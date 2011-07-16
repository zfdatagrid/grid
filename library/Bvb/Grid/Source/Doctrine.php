<?php

/**
 * Provides you the ability to use Doctrine as a source
 * with the Grid.
 *
 * @package   Bvb_Grid
 * @author James Solomon <labs@clickbooth.com>
 */

class Bvb_Grid_Source_Doctrine
    extends Bvb_Grid_Source_Db_DbAbstract
        implements Bvb_Grid_Source_SourceInterface
{

    protected $_totalRecords;

    /**
     * Stores the supplied Doctrine_Query
     *
     * @var Doctrine_Query
     */
    protected $_query;

    /**
     * Stores the parsed out DQL
     *
     * @var array
     */
    protected $_queryParts = array(
        'select' => array(),
        'from'   => array(),
        'join'   => array()
    );

    /**
     * Intialize the Doctrine_Query. We will parse out
     * all the provided DQL or start a Doctrine_Query
     * if an instance of Doctrine_Record is provided
     *
     * @param mixed $q
     */
    public function __construct($q)
    {
        if ($q instanceof Doctrine_Record) {
            $q = get_class($q);
        }

        if (is_string($q)) {
            $q = Doctrine_Query::create()->from($q);
        }

        if (!$q instanceof Doctrine_Query) {
            require_once 'Bvb/Grid/Source/Doctrine/Exception.php';
            throw new Bvb_Grid_Source_Doctrine_Exception(
                "Please provide only an instance of Doctrine_Query "
                 . "or a valid Doctrine_Record instance"
            );
        }

        $this->_query = $q;
        $this->_setFromParts(); // called here
        $this->_setSelectParts();
    }

    /**
     * Simple method for the Grid to determine if this
     * Source can handle CRUD
     *
     * @return boolean
     */
    public function hasCrud()
    {
        return true;
    }

    /**
     * Returns the "main" table
     * the one after select * FROM {MAIN_TABLE}
     *
     * @return array
     */
    public function getMainTable()
    {
        $table = Doctrine::getTable($this->_queryParts['from']['tableModel']);
        return array('table' => $table->getTableName());
    }

    /**
     * builds a key=>value array
     *
     * they must have two options
     * title and field
     * field is used to perform queries.
     * Must have table name or table alias as a prefix
     * ex: user.id | country.population
     *
     * The key for this array is the output field
     * If raw sql is somehting like
     *
     * select name as alias, country from users
     *
     * the return array must be like this:
     *
     * array('alias'=>array('title'=>'alias','field'=>'users.name'));
     *
     * its not bad idea to apply this to fields titles
     * $title = ucwords(str_replace('_',' ',$title));
     *
     * @return array
     */
    public function buildFields()
    {
        $this->_setSelectParts();
        return $this->_queryParts['select'];
    }

    /**
     * Use the supplied Doctrine_Query to find its primary ID
     *
     * @return array Primary Keys for specified table
     */
    public function getIdentifierColumns($table = null)
    {
        $return = array();
        $table = Doctrine::getTable($this->_getModelFromTable($table));
        $alias = $this->_queryParts['from']['alias'];

        //Get the Primary Key(s) for provided table
        $ids = $table->getIdentifierColumnNames();

        //Append the alias to each Primary key field
        foreach ($ids as $id) {
            $return[] = (!empty($alias)) ? $alias . '.' . $id : $id;
        }

        return $return;
    }

    /**
     * Gets a unique record as a associative array
     *
     * @param $table
     * @param $condition
     * @return array
     */
    public function getRecord($table, array $condition)
    {
        $tableModel = $this->_getModelFromTable($table);

        $query = Doctrine_Query::create()->from($tableModel);

        foreach ($condition as $field => $value) {
            $query->addWhere($field . ' = ?', $value);
        }

        $results = $query->fetchArray(array(), Doctrine::HYDRATE_SCALAR);

        $newResults = $this->_cleanQueryResults($results);

        if (count($newResults) == 1) {
            return $newResults[0];
        }

        return $newResults;
    }

    /**
     * Should return the database server name or source name
     *
     * Ex: mysql, pgsql, array, xml
     *
     * @return string
     */
    public function getSourceName()
    {
        return strtolower($this->_query->getConnection()->getDriverName());
    }

    /**
     * Runs the query and returns the result as a associative array
     *
     * @return array
     */
    public function execute()
    {
        $newQuery = clone $this->_query;

        $results = $newQuery->execute(array(), Doctrine::HYDRATE_SCALAR);

        return $this->_cleanQueryResults($results);
    }

    /**
     * Get a record detail based the current query
     *
     * <code>
     * $where = array(
     *     array('columnName' => 'searchValue')
     * )
     * </code>
     *
     * @param array $where
     * @return array
     */
    public function fetchDetail(array $where)
    {
        /**
         * Remove these since we are trying to retrieve
         * a specific row
         */
        $this->_query->removeDqlQueryPart('limit')
                     ->removeDqlQueryPart('offset');
        foreach ($where as $column => $value) {
            $this->_query->addWhere($column . ' = ?', $value);
        }

        return $this->execute();
    }

    /**
     * Return the total of records
     *
     * @return integer
     */
    public function getTotalRecords()
    {
        if($this->_totalRecords>0)
            return $this->_totalRecords;


        return (int) $this->_query->count();
    }

    /**
     * Ex: array('c'=>array('tableName'=>'Country'));
     * where c is the table alias. If the table as no alias,
     * c should be the table name
     *
     * TODO : Find where this is used, and test
     *
     * @return array
     */
    public function getTableList()
    {
        $return = array();

        $fromAlias = (empty($this->_queryParts['from']['alias'])) ? $this->_queryParts['from']['tableName'] : $this->_queryParts['from']['alias'];
        $fromName = $this->_queryParts['from']['tableName'];
        $return[$fromAlias] = array('tableName' => $fromName);

        foreach ($this->_queryParts['join'] as $joinTypes) {
            foreach ($joinTypes as $join) {
                $return[$join['alias']] = array('tableName' => $join['tableAlias']);
            }
        }

        return $return;
    }

    /**
     * Return possible filters values based on field definion
     * This is mostly used for enum fields where the possibile
     * values are extracted
     *
     * Ex: enum('Yes','No','Empty');
     *
     * should return
     *
     * array('Yes'=>'Yes','No'=>'No','Empty'=>'Empty');
     *
     * @param $field
     * @return string
     */
    public function getFilterValuesBasedOnFieldDefinition($field)
    {
        if (strpos($field, '(') !== false) {
            return 'text';
        }

        $table = $this->_getModelFromColumn($field);

        $tableClass = Doctrine::getTable($table);

        if (strpos($field, '.') !== false) {
            list($alias, $column) = explode('.', $field);
        } else {
            $column = $field;
        }

        $definition = $tableClass->getDefinitionOf($column);

        if ($definition['type'] == 'enum') {
            foreach ($definition['values'] as $val) {
                $return[$val] = $val;
            }

            return $return;
        }

        return 'text';
    }

    /**
     * Return te field type
     * char, varchar, int
     *
     * Note: If the field is enum or set,
     * the value returned must be set or enum,
     * and not the full definition
     *
     * @param string $field
     * @return string|null
     */
    public function getFieldType($field)
    {
        if (!isset($this->_queryParts['select'][$field]['field']))
            return null;

        if (strpos($this->_queryParts['select'][$field]['field'], '.') !== false) {
            list($alias, $fieldName) = explode('.', $this->_queryParts['select'][$field]['field']);
        } else {
            $fieldName = $field;
        }

        $tableModel = $this->_getModelFromColumn($field);

        return Doctrine::getTable($tableModel)->getTypeOfColumn($fieldName);
    }

    /**
     * Build the order part from the query.
     *
     * The first arg is the field to be ordered and the $order
     * arg is the correspondent order (ASC|DESC)
     *
     * If the $reset is set to true, all previous order should be removed
     *
     * @param string $field
     * @param string $order
     * @param bool $reset
     * @return Bvb_Grid_Source_Doctrine
     */
    public function buildQueryOrder($field, $order, $reset = false)
    {
        if ($reset) {
            $this->_query->removeDqlQueryPart('orderby');
        }

        $this->_query->addOrderBy($field . ' ' . $order);

        return $this;
    }

    /**
     * Build the query limit clause
     *
     * @param $start
     * @param $offset
     * @return Bvb_Grid_Source_Doctrine
     */
    public function buildQueryLimit($start, $offset)
    {
        $this->_query->limit($start)->offset($offset);

        return $this;
    }

    /**
     * Returns the select object
     *
     * @return Doctrine_Query
     */
    public function getSelectObject()
    {
        return $this->_query;
    }

    /**
     * Returns the selected order
     * that was defined by the user in the query entered
     * and not the one generated by the system
     *
     * If empty an empty array must be returned.
     *
     * Else the array must be like this:
     *
     * <code>
     * $return = array(
     *     0 => field
     *     1 => (ASC|DESC)
     * );
     * </code>
     *
     * @return array
     */
    public function getSelectOrder()
    {
        $orderBy = $this->_query->getDqlPart('orderby');

        if (empty($orderBy))
            return array();

        $result = array();
        foreach ($orderBy as $anOrderby) {
            $orderBys = explode(',', $anOrderby);

            foreach ($orderBys as $order) {
                $parts = explode(' ', trim($order));
                if (isset($parts[1]) && !in_array(strtolower($parts[1]), array('desc', 'asc'))) {
                    $parts[1] = '';
                }
                $result[] = implode(' ', $parts);
            }
        }

        /**
         * FIX : #215
         *
         * This function seems to be needed to only return the
         * first order, even if more than one is present
         *
         * @see Bvb_Grid_Source_Zend_Select::getSelectOrder()
         */
        return $result[0];
    }

    /**
     * Should preform a query based on the provided by the user
     * select the two fields and return an array $field=>$value
     * as result
     *
     * ex: SELECT $field, $value FROM *
     * array('1'=>'Something','2'=>'Number','3'=>'history')....;
     *
     * @param string $field
     * @param string $value
     * @return array
     */
    public function getDistinctValuesForFilters($field, $fieldValue, $order = 'name ASC')
    {
        $return = array();

        $newQuery = clone $this->_query;

        $distinct = new Doctrine_Expression("DISTINCT($field)");

        $newQuery->removeDqlQueryPart('select')
                 ->removeDqlQueryPart('orderby')
                 ->removeDqlQueryPart('limit')
                 ->removeDqlQueryPart('offset')
                 ->select("$distinct AS field, $fieldValue AS value")
                 ->orderBy('value ASC');

        //Only using Scalar here, b/c of an aparent bug with Doctrine::HYDRATE_ARRAY
        $results = $newQuery->execute(array(), Doctrine::HYDRATE_SCALAR);
        $cleanResults = array();

        foreach ($results as $result) {
            $temp = array();

            foreach ($result as $column => $value) {
                $pos = strpos($column, '_');
                $field = substr($column, ++$pos);
                $temp[$field] = $value;
            }

            $cleanResults[] = $temp;
        }

        foreach ($cleanResults as $value) {
            $return[$value['field']] = $value['value'];
        }

        return $return;
    }

    /**
     * Perform a sqlexp
     *
     * $value =  array ('functions' => array ('AVG'), 'value' => 'Population' );
     *
     * Should be converted to
     * SELECT AVG(Population) FROM *
     *
     * $value =  array ('functions' => array ('SUM','AVG'), 'value' => 'Population' );
     *
     * Should be converted to
     * SELECT SUM(AVG(Population)) FROM *
     *
     * @param array $value
     */
    public function getSqlExp(array $value, $where = array())
    {
        $return = array();

        $newQuery = clone $this->_query;

        foreach (array_reverse($value['functions']) as $key => $func) {
            if ($key == 0) {
                $exp = $func . '(' . $value['value'] . ')';
            } else {
                $exp = $func . '(' . $exp . ')';
            }
        }

        $exp = new Doctrine_Expression($exp);

        $newQuery->removeDqlQueryPart('select')
                 ->removeDqlQueryPart('orderby')
                 ->removeDqlQueryPart('limit')
                 ->removeDqlQueryPart('offset')
                 ->select("$exp AS total");

        return $newQuery->fetchOne(array(), Doctrine::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Adds a fulltext search instead of a addcondition method
     *
     * $field has an index search
     * $field['search'] = array('extra'=>'boolean|queryExpansion','indexes'=>'string|array');
     *
     * if no indexes provided, use the field name
     *
     * boolean =>  IN BOOLEAN MODE
     * queryExpansion =>  WITH QUERY EXPANSION
     *
     * @param $filter
     * @param $field
     */
    public function addFullTextSearch($filter, $field)
    {
        throw new Bvb_Grid_Source_Doctrine_Exception("Fulltext searching is currently not supported by the Doctrine Source");
    }

    /**
     * Adds a new condition to the current query
     * $filter is the value to be filtered
     * $op is the opreand to be used: =,>=, like, llike,REGEX,
     * $completeField. use the index $completField['field'] to
     * specify the field, to avoid ambiguous
     *
     * @param $filter
     * @param $op
     * @param $completeField
     * @return Bvb_Grid_Source_Doctrine
     */
    public function addCondition($filter, $op, $completeField)
    {

        $field = $completeField['field'];

        /**
         * FIX : #218
         * We need to make sure to use HAVING when there is function
         * in the select, as you cannot use these selected fields in the
         * WHERE clause, and all others will use the WHERE clause
         */
        $func = 'addWhere';
        if (strpos($field, '(') !== false) {
            $func = 'addHaving';
        }

        switch (strtolower($op)) {
            case 'sqlexp':
                $this->_query->$func($filter);
                break;
            case 'isnull':
                $this->_query->$func($field.' IS NULL ');
                break;
            case 'isnnotull':
                $this->_query->$func($field.' IS NOT NULL ');
                break;
            case 'empty':
                $this->_query->$func($field . " =''");
                break;
            case 'equal':
            case '=':
                $this->_query->$func($field . ' = ?', $filter);
                break;
            case 'regex':
                $this->_query->$func($field . " REGEXP ?", $filter);
                break;
            case 'rlike':
                $this->_query->$func($field . " LIKE ?", $filter . "%");
                break;
            case 'llike':
                $this->_query->$func($field . " LIKE ?", "%" . $filter);
                break;
            case '>=':
                $this->_query->$func($field . " >= ?", $filter);
                break;
            case '>':
                $this->_query->$func($field . " > ?", $filter);
                break;
            case '<>':
            case '!=':
                $this->_query->$func($field . " <> ?", $filter);
                break;
            case '<=':
                $this->_query->$func($field . " <= ?", $filter);
                break;
            case '<':
                $this->_query->$func($field . " < ?", $filter);
                break;
            case 'in':
                $filter = explode(',', $filter);
                $this->_query->whereIn($field, $filter);
                break;
            case '&':
            case 'and':
            case 'AND':
            case 'flag':
            case 'FLAG':
                $this->_query->$func($field . " & ? <> 0", $filter);
                break;
            case 'range':
                $start = substr($filter, 0, strpos($filter, '<>'));
                $end = substr($filter, strpos($filter, '<>') + 2);
                $this->_query->$func($field . " between ? and ?", array($start, $end));
                break;
            case '||':
                $this->_query->orWhere($field . " LIKE ?", "%" . $filter . "%");
                break;case 'like':
            default:
                $this->_query->$func($field . " LIKE ?", "%" . $filter . "%");
                break;
        }

        return $this;
    }

    /**
     * Insert an array of key=>values in the specified table
     *
     * @param string $table
     * @param array $post
     * @return boolean
     */
    public function insert($table, array $post)
    {

        $tableModel = $this->_getModelFromTable($table);

        /**
         * @var Doctrine_Record
         */
        $model = new $tableModel;
        $model->fromArray($post);

        $return = $model->trySave();

        return $return;
    }

    /**
     * Update values in a table using the $condition clause
     *
     * The condition clause is a $field=>$value array
     * that should be escaped by YOU (if your class doesn't do that for you)
     * and usinf the AND operand
     *
     * Ex: array('user_id'=>'1','id_site'=>'12');
     *
     * Raw SQL: * WHERE user_id='1' AND id_site='12'
     *
     * @param string $table
     * @param array $post
     * @param array $condition
     * @return integer Of Affected rows
     */
    public function update($table, array $post, array $condition)
    {

        $tableModel = $this->_getModelFromTable($table);

        $query = Doctrine_Query::create()->update($tableModel);

        foreach ($post as $field => $value) {
            $query->set($field, '?', $value);
        }

        foreach ($condition as $field => $value) {
            $query->addWhere($field . ' = ?', $value);
        }

       return  $query->execute();

    }

    /**
     * Delete a record from a table
     *
     * The condition clause is a $field=>$value array
     * that should be escaped by YOU (if your class doesn't do that for you)
     * and usinf the AND operand
     *
     * Ex: array('user_id'=>'1','id_site'=>'12');
     * Raw SQL: * WHERE user_id='1' AND id_site='12'
     *
     * @param string $table
     * @param array $condition
     * @return integer Of Affected rows
     */
    public function delete($table, array $condition)
    {


        $tableModel = $this->_getModelFromTable($table);

        $query = Doctrine_Query::create()->delete($tableModel);

        foreach ($condition as $field => $value) {
            $query->addWhere($field . ' = ?', $value);
        }

        return $query->execute();

    }


    /**
     * @todo Change to Doctrine Native function
     * @see Bvb_Grid_Source_SourceInterface::quoteValue()
     */
    public function quoteValue ($value)
    {
        return mysql_real_escape_string($value);
    }


    /**
     * Removes any order in query
     *
     * @return Bvb_Grid_Source_Doctrine
     */
    public function resetOrder()
    {
        $this->_query->removeDqlQueryPart('orderby');
        return $this;
    }

    /**
     * Removes any limit in query
     *
     * @return Bvb_Grid_Source_Doctrine
     */
    public function resetLimit()
    {
        $this->_query->removeDqlQueryPart('limit');
        $this->_query->removeDqlQueryPart('offset');
        return $this;
    }

    /**
     * Cache handler.
     *
     * TODO: Research what 'cache' does, might just need to look at the
     *       bool and see if we need to set Doctrine Cache or not
     */
    public function setCache($cache)
    {
        //die(Zend_Debug::dump($cache));
        //die('setCache');
    }

    /**
     * Build the form based on a Model or query
     *
     * @return array
     */
    public function buildForm($inputsType = array())
    {
        $table = $this->_queryParts['from']['tableModel'];
        $columns = Doctrine::getTable($table)->getColumns();

        return $this->buildFormElements($columns, array(), $inputsType);
    }

    /**
     * Will build out an array of form elements,
     * based on the column type and return the array
     * to be used when loading the Bvb_Grid_Form
     *
     * @param array $cols
     * @param array $info
     * @return array
     */
    public function buildFormElements(array $cols, $info = array(), $inputsType= array())
    {
        $form = array();

        foreach ($cols as $column => $detail) {

            if (isset($detail['primary']) && $detail['primary']) {
                continue;
            }

            $label = ucwords(str_replace('_', ' ', $column));

            switch ($detail['type']) {
                case 'enum':
                    $form['elements'][$column] = array('select', array('multiOptions' => $detail['values'], 'required' => (array_key_exists('notnull', $detail)) ? true : false, 'label' => $label));
                    break;

                case 'string':
                case 'varchar':
                case 'char':
                    $length = $detail['length'];
                    $form['elements'][$column] = array('text', array('validators' => array(array('stringLength', false, array(0, $length))), 'size' => 40, 'label' => $label, 'required' =>  (array_key_exists('notnull', $detail)) ? true : false, 'value' => (! empty($detail['default']) ? $detail['default'] : "")));
                    break;
                case 'date':
                    $form['elements'][$column] = array('text', array('validators' => array(array('Date')), 'size' => 10, 'label' => $label, 'required' =>  (array_key_exists('notnull', $detail)) ? true : false, 'value' => (! empty($detail['default']) ? $detail['default'] : "")));
                    break;
                case 'time':
                    $form['elements'][$column] = array('text', array('validators' => array(array(new Zend_Validate_Date('H:i:s'))), 'size' => 19, 'label' => $label, 'required' => (array_key_exists('notnull', $detail)) ? true : false, 'value' => (! empty($detail['default']) ? $detail['default'] : "")));
                    break;
                case 'datetime':
                case 'timestamp':
                    $form['elements'][$column] = array('text', array('validators' => array(array(new Zend_Validate_Date('Y-m-d H:i:s'))), 'size' => 19, 'label' => $label, 'required' =>  (array_key_exists('notnull', $detail)) ? true : false, 'value' => (! empty($detail['default']) ? $detail['default'] : "")));
                    break;

                case 'text':
                case 'mediumtext':
                case 'longtext':
                case 'smalltext':
                    $form['elements'][$column] = array('textarea', array('label' => $label, 'required' =>  (array_key_exists('notnull', $detail)) ? true : false, 'filters' => array('StripTags')));
                    break;

                case 'integer':
                case 'int':
                case 'bigint':
                case 'mediumint':
                case 'smallint':
                case 'tinyint':
                    $isZero = (! empty($detail['default']) && $detail['default'] == "0") ? true : false;
                    $form['elements'][$column] = array('text', array('validators' => array('Digits'), 'label' => $label, 'size' => 10, 'required' => ($isZero == false && (array_key_exists('notnull', $detail))) ? false : true, 'value' => (! empty($detail['default']) ? $detail['default'] : "")));
                    break;

                case 'float':
                case 'decimal':
                case 'double':
                    $form['elements'][$column] = array('text', array('validators' => array('Float'), 'size' => 10, 'label' => $label, 'required' =>  (array_key_exists('notnull', $detail)) ? true : false, 'value' => (! empty($detail['default']) ? $detail['default'] : "")));
                    break;

                default:
                    break;
            }
        }

        foreach ( $inputsType as $field => $type ) {
            $form['elements'][$field][0] = strtolower($type);
        }

        return $form;
    }

    /**
     * Used within this class to clean the hydrated result
     * to be something more Grid friendly
     *
     * @param array $results
     * @return array Cleaned results
     */
    protected function _cleanQueryResults($results)
    {
        $newArray = array();

        if (empty($this->_queryParts['from']['alias'])) {
            foreach ($results as $rows) {
                $temp = array();
                foreach ($rows as $col => $val) {
                    $name = str_replace($this->_queryParts['from']['tableModel'] . '_', '', $col);
                    $temp[$name] = $val;
                }

                $newArray[] = $temp;
            }
        } else {
            foreach ($results as $rows) {
                $temp = array();

                foreach ($rows as $col => $val) {
                    $parts = explode('_', $col, 2);
                    $field = implode('.', $parts);

                    foreach ($this->_queryParts['select'] as $alias => $select) {
                        if ($field == $select['field'] || $parts[1] == $alias) {
                            $temp[$alias] = $val;
                        }
                    }
                }

                $newArray[] = $temp;
            }
        }

        return $newArray;
    }

    /**
     * Used to parse out the SELECT pieces of the DQL
     * and place it in the $_queryParts array for use
     * in many other places
     *
     * @return Bvb_Grid_Source_Doctrine
     */
    protected function _setSelectParts()
    {
        $return = array();

        $selects = $this->_query->getDqlPart('select');

        if (empty($selects)) {
            $this->_findAndSetSelect();
            $selects = $this->_query->getDqlPart('select');
        }

        //Remove all 'as' instances
        $selects = $this->_removeAs($selects);

        foreach ($selects as $select) {
            $fields = explode(',', $select);
            $fields = array_map('trim', $fields);

            foreach ($fields as $field) {
                $fieldName = trim($field);
                $fieldAlias = null;

                if (strpos($field, ' ') !== false) {
                    // since our field expression may contain spaces, assume the last space marks the alias
                    $parts      = explode(' ', $field);
                    $fieldAlias = array_pop($parts);
                    $fieldName  = implode(' ', $parts);
                }

                if (empty($fieldAlias)) {
                    $pos = strpos($fieldName, '.');
                    $fieldAlias = substr($fieldName, ++$pos);
                }

                $return[$fieldAlias] = array(
                    'title' => ucwords(str_replace('_', ' ', $fieldAlias)),
                    'field' => $fieldName
                );
            }
        }

        $this->_queryParts['select'] = $return;
        return $this;
    }

    /**
     * Used to parse out the FROM and JOIN pieces of the DQL
     * and place it in the $_queryParts array for use
     * in many other places
     *
     * @return Bvb_Grid_Source_Doctrine
     */
    protected function _setFromParts()
    {
        $return = array();

        //Remove all 'as' instances
        $froms = $this->_removeAs($this->_query->getDqlPart('from'));

        foreach ($froms as $from) {
            $fields = explode(',', $from);
            $fields = array_map('trim', $fields);

            foreach ($fields as $field) {
                if (strpos(strtoupper($field), 'JOIN') === false) {
                    $this->_queryParts = array_merge_recursive($this->_queryParts, $this->_explodeFrom($field));
                } else {
                    $join = explode('JOIN', $field);
                    $join = array_map('trim', $join);

                    $joinType = strtolower($join[0]);

                    $this->_queryParts = array_merge_recursive($this->_queryParts, $this->_explodeJoin($join[1], $joinType));
                }
            }
        }

        return $this;
    }

    /**
     * Used to set SELECT values to the DQL when
     * no SELECT is provided.  We will just add
     * ALL columns for all tables given.
     *
     * NOTE: Since no SELECT was provided, to access these
     * from within the Bvb_Grid_Data class, you will need to
     * use the table alias + "_" + column name
     *
     * <code>
     * $grid->setGridColumns(array('co_code', 'co_name', 'co_continent', 'ci_name'));
     * </code>
     *
     * @return void
     */
    protected function _findAndSetSelect()
    {
        $return = array();

        //Make sure we have the FROM set
        // $this->_setFromParts(); // second call, if commented works

        $fromTableModel = $this->_queryParts['from']['tableModel'];
        $fromClass = Doctrine::getTable($fromTableModel);
        $fromColumns = array_keys($fromClass->getColumns());
        $fromAlias = $this->_queryParts['from']['alias'];

        foreach ($fromColumns as $fromColumn) {
            /**
             * Do this check here because a DQL with no JOIN,
             * does not need a table alias
             *
             * @var string
             */
            $addColumn = (!empty($fromAlias)) ? $fromAlias . '.' . $fromColumn : $fromColumn;
            $this->_query->addSelect($addColumn);
        }

        $joins = $this->_queryParts['join'];

        if (!empty($joins)) {
            foreach ($joins as $joinType) {
                foreach ($joinType as $join) {
                    $joinClass = Doctrine::getTable($join['tableModel']);
                    $joinColumns = array_keys($joinClass->getColumns());

                    foreach ($joinColumns as $joinColumn) {
                        $this->_query->addSelect($join['alias'] . '.' . $joinColumn);
                    }
                }
            }
        }
    }

    /**
     * Take a DQL SELECT string and parse it into
     * a usable array
     *
     * <code>
     * $return = array(
     *     'from' => array(
     *         'alias' => 'c',
     *         'table' => 'country'
     *     )
     * )
     * </code>
     *
     * @param string $from A DQL SELECT statement
     * @return array
     */
    private function _explodeFrom($from)
    {
        if (!is_string($from)) {
            require_once 'Bvb/Grid/Source/Doctrine/Exception.php';
            throw new Bvb_Grid_Source_Doctrine_Exception('Provided param needs to be a string only');
        }

        $return = array();
        $table = $from;
        $alias = null;

        if (count(explode(' ', $from)) > 1) {
            list($table, $alias) = explode(' ', $from);
        }

        if (strpos($table, '.') !== false) {
            $return = $this->_explodeJoin($from, 'left');
        } else {
            $return['from'] = array(
                'alias' => $alias,
                'tableModel' => $table,
                'tableName' => Doctrine::getTable($table)->getTableName()
            );
        }

        return $return;
    }

    /**
     * Take a DQL JOIN string and parse it into
     * a usable array
     *
     * <code>
     * $return = array(
     *     'join' => array(
     *         'left' => array(
     *             array(
     *                 'alias' => 'ci'
     *                 'table' => 'Model_City' //Doctrine Table name
     *                 'tableAlias' => 'City' //What is used in the JOIN statement
     *                 'joinOn' => 'c'
     *             )
     *         )
     *     )
     * )
     * </code>
     *
     * @param string $join
     * @param string $joinType The type of join - LEFT, RIGHT, etc
     */
    private function _explodeJoin($join, $joinType)
    {
        $return = array();

        list($table, $alias) = explode(' ', $join);
        list($joinOn, $tableAlias) = explode('.', $table);
        $mainTable = $this->_getModelFromAlias($joinOn);

        $tableModel = Doctrine::getTable($mainTable)->getRelation($tableAlias)->getClass();

        $return['join'][strtolower($joinType)][] = array(
            'alias'      => $alias,
            'tableModel' => $tableModel,
            'tableAlias' => $tableAlias,
            'joinOn'     => $joinOn
        );

        return $return;
    }

    /**
     * Simple utility for removing "as" from the DQL
     *
     * @param string $subject
     * @return string No-As DQL expression
     */
    private function _removeAs($subject)
    {
        return str_ireplace(' as', '', $subject);
    }

    /**
     * Find the table for which a column belongs
     *
     * @param string $column
     * @return string Name of the table used
     */
    private function _getModelFromColumn($column)
    {
        if (!is_string($column)) {
            $type = gettype($column);
            require_once 'Bvb/Grid/Source/Doctrine/Exception.php';
            throw new Bvb_Grid_Source_Doctrine_Exception('The $column param needs to be a string, ' . $type . ' provided');
        }

        if (empty($this->_queryParts['from']['alias'])) {
            return $this->_queryParts['from']['tableModel'];
        }

        list($alias, $field) = explode('.', $column);

        return $this->_getModelFromAlias($alias);
    }

    /**
     * Find the table/model based on the table alias provided
     *
     * @param string $alias
     * @return string
     */
    private function _getModelFromAlias($alias)
    {
        if (!is_string($alias)) {
            $type = gettype($alias);
            require_once 'Bvb/Grid/Source/Doctrine/Exception.php';
            throw new Bvb_Grid_Source_Doctrine_Exception('The $alias param needs to be a string, ' . $type . ' provided');
        }

        if ($this->_queryParts['from']['alias'] == $alias) {
            return $this->_queryParts['from']['tableModel'];
        }

        foreach ($this->_queryParts['join'] as $joins) {
            foreach ($joins as $join) {
                if ($join['alias'] == $alias) {
                    return $join['tableModel'];
                }
            }
        }
    }

    /**
     * Find the table/model based on the table alias provided
     *
     * @param string $table Name of table to find model from
     * @return string Name of the model associated with the provided table
     */
    private function _getModelFromTable($table)
    {
        if (!is_string($table)) {
            $type = gettype($table);
            require_once 'Bvb/Grid/Source/Doctrine/Exception.php';
            throw new Bvb_Grid_Source_Doctrine_Exception('The $table param needs to be a string, ' . $type . ' provided');
        }

        if ($this->_queryParts['from']['tableName'] == $table) {
            return $this->_queryParts['from']['tableModel'];
        }

        foreach ($this->_queryParts['join'] as $joins) {
            foreach ($joins as $join) {
                if ($join['tableAlias'] == $table) {
                    return $join['tableModel'];
                }
            }
        }
    }


    /**
     * @todo Implement
     * @see library/Bvb/Grid/Source/Bvb_Grid_Source_SourceInterface::getMassActionsIds()
     */
    public function getMassActionsIds ($table, $fields, $separator = '-')
    {
        $q= clone $this->_query;
        $alias = $this->_queryParts['from']['alias'];

        $q->removeDqlQueryPart('limit');
        $q->removeDqlQueryPart('offset');
        $q->removeDqlQueryPart('orderby');

        $q->removeSqlQueryPart('limit');
        $q->removeSqlQueryPart('offset');
        $q->removeSqlQueryPart('orderby');

        if ( count($fields) == 0 ) {
            $pks = Doctrine::getTable($this->_getModelFromTable($table))->getIdentifier();
        } else {
            $pks = $fields;
        }

        if ($pks && !is_array($pks))
        {
            $pks= array($pks);
        }

        if ( count($pks) > 1 ) {
            $concat = '';
            foreach ( $pks as $conc ) {
                $concat .= $alias.'.'.$conc . " ,'$separator' ,";
            }
            $concat = rtrim($concat, "'-' ,");
            $q->select('CONCAT('.$concat.', "_") as ids');
        } else {
            $concat = $pks[0];
            $q->select($alias.'.'.$concat.' as ids');
        }

        $result= $q->getConnection()->fetchAssoc($q->getSqlQuery(), $q->getFlattenedParams());

        $return = array();
        foreach ( $result as $value ) {
            $return[] = current($value);
        }

        return implode(',', $return);
    }


    /**
     * @todo Implement
     * @see library/Bvb/Grid/Source/Bvb_Grid_Source_SourceInterface::getValuesForFiltersFromTable()
     */
    public function getValuesForFiltersFromTable($table, $field, $fieldValue, $order = 'name ASC')
    {
        throw new Exception('Not yet Implemented');
    }

    /**
     * @todo Implement
     * @see library/Bvb/Grid/Source/Bvb_Grid_Source_SourceInterface::getAutoCompleteForFilter()
     */
    public function getAutoCompleteForFilter( $term, $field, $specialKey='', $output = 'json')
    {
        throw new Exception('Not yet Implemented');
    }

    /**
     * Defines total records
     *
     * @param int $total Total records found
     */

    public function setTotalRecords($total)
    {
        $this->_totalRecords = (int) $total;
    }


    public function beginTransaction()
    {
       return false;
    }

    public function commit()
    {
        return false;
    }

    public function rollBack()
    {
        return false;
    }

    public function getConnectionId()
    {
      return 0;
    }
}
