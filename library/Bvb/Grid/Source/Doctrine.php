<?php

class Bvb_Grid_Source_Doctrine implements Bvb_Grid_Source_Interface
{
    protected $_query;
    
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
            $name = get_class($q);
            $q = Doctrine_Query::create()->from($name);
        }
        
        if (!$q instanceof Doctrine_Query) {
            require_once 'Bvb/Grid/Source/Doctrine/Exception.php';
            throw new Bvb_Grid_Source_Doctrine_Exception(
                "Please provide only an instance of Doctrine_Query "
                 . "or a valid Doctrine_Record instance"
            );
        }
        
        $this->_query = $q;
        $this->_setFromParts();
        $this->_setSelectParts();
    }
    
    public function hasCrud()
    {
        return true;
    }
    
    /**
     * Returns the "main" table
     * the one after select * FROM {MAIN_TABLE}
     *
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
     */
    public function buildFields()
    {
        $this->_setSelectParts();
        return $this->_queryParts['select'];
    }
    
    /**
     * Use the supplied Doctrine_Query to find its primary ID
     * 
     * TODO : Implement usage of $table param
     */
    public function getPrimaryKey($table = null)
    {
        $return = array();
        $table = Doctrine::getTable($this->_queryParts['from']['tableModel']);
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
        
        $results = $query->fetchOne(array(), Doctrine::HYDRATE_SCALAR);
        
        die(Zend_Debug::dump($results, null, false));
    }

    /**
     * Should return the database server name or source name
     *
     * Ex: mysql, pgsql, array, xml
     */
    public function getSourceName()
    {
        return strtolower($this->_query->getConnection()->getDriverName());
    }

    /**
     * Runs the query and returns the result as a associative array
     */
    public function execute()
    {
        $newArray = array();
        $newQuery = clone $this->_query;
        $results = $newQuery->execute(array(), Doctrine::HYDRATE_SCALAR);
        
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
                    
                    foreach ($this->_queryParts['select'] as $alias => $select) {
                        if (implode('.', $parts) == $select['field'] || $parts[1] == $alias) {
                            $temp[$alias] = $val;
                        }
                    }
                }
                
                $newArray[] = $temp;
            }
        }
        //die(Zend_Debug::dump($newArray, null, false));
        return $newArray;
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
     * @return int
     */
    public function getTotalRecords()
    {
        return (int) $this->_query->count();
    }

    /**
     * Ex: array('c'=>array('tableName'=>'Country'));
     * where c is the table alias. If the table as no alias,
     * c should be the table name
     * 
     * @return array
     */
    function getTableList ()
    {
        die('getTableList');
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
     */
    public function getFilterValuesBasedOnFieldDefinition($field)
    {
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
     */

    public function getFieldType($field)
    {
        die('getFieldType');
        
        $this->_queryExecuted->getRoot()->getTypeOfColumn('continent');
    }

    /**
     *
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
     */
    public function buildQueryLimit($start, $offset)
    {
        $this->_query->limit($start)->offset($offset);
        
        return $this;
    }

    /**
     * Returns the select object
     */
    function getSelectObject ()
    {
        die('getSelectObject');
    }

    /**
     * returns the selected order
     * that was defined by the user in the query entered
     * and not the one generated by the system
     *
     *If empty a empty array must be returned.
     *
     *Else the array must be like this:
     *
     *Array
     * (
     * [0] => field
     * [1] => ORDER (ASC|DESC)
     * )
     *
     * @return array
     */
    public function getSelectOrder()
    {
        $newOrderBys = array();
        $orderBy = $this->_query->getDqlPart('orderby');
        
        if (!empty($orderBy)) {
            foreach ($orderBy as $anOrderby) {
                $orderBys = explode(',', $anOrderby);
                
                foreach ($orderBys as $order) {
                    $parts = explode(' ', trim($order));
                    if (strtolower($parts[1]) != 'desc' && strtolower($parts[1]) != 'asc') {
                        $parts[1] = '';
                    }
                    $newOrderBys[] = $parts;
                }
            }
        }
        
        return $newOrderBys;
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
    function getDistinctValuesForFilters ($field, $value)
    {
        die('getDistinctValuesForFilters');
    }

    /**
     *
     *Perform a sqlexp
     *
     *$value =  array ('functions' => array ('AVG'), 'value' => 'Population' );
     *
     *Should be converted to
     *SELECT AVG(Population) FROM *
     *
     *$value =  array ('functions' => array ('SUM','AVG'), 'value' => 'Population' );
     *
     *Should be converted to
     *SELECT SUM(AVG(Population)) FROM *
     *
     * @param array $value
     */
    function getSqlExp (array $value)
    {
        die('getSqlExp');
    }

    /**
     * Adds a fulltext search instead of a addcondition method
     *
     *$field has an index search
     *$field['search'] = array('extra'=>'boolean|queryExpansion','indexes'=>'string|array');
     *
     *if no indexes provided, use the field name
     *
     *boolean =>  IN BOOLEAN MODE
     *queryExpansion =>  WITH QUERY EXPANSION
     *
     * @param $filter
     * @param $field
     */
    function addFullTextSearch ($filter, $field)
    {
        die('addFullTextSearch');
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
     */
    public function addCondition($filter, $op, $completeField)
    {
        $field = $completeField['field'];
        
        switch (strtolower($op)) {
            case 'equal':
            case '=':
                $this->_query->addWhere($field . ' = ?', $filter);
                break;
            case 'regex':
                $this->_query->addWhere($field . " REGEXP ?", $filter);
                break;
            case 'rlike':
                $this->_query->addWhere($field . " LIKE ?", $filter . "%");
                break;
            case 'llike':
                $this->_query->addWhere($field . " LIKE ?", "%" . $filter);
                break;
            case '>=':
                $this->_query->addWhere($field . " >= ?", $filter);
                break;
            case '>':
                $this->_query->addWhere($field . " > ?", $filter);
                break;
            case '<>':
            case '!=':
                $this->_query->addWhere($field . " <> ?", $filter);
                break;
            case '<=':
                $this->_query->addWhere($field . " <= ?", $filter);
                break;
            case '<':
                $this->_query->addWhere($field . " < ?", $filter);
                break;
            case 'in':
                $filter = explode(',', $filter);
                $this->_query->whereIn($field, $filter);
                break;
            case 'range':
                $start = substr($filter, 0, strpos($filter, '<>'));
                $end = substr($filter, strpos($filter, '<>') + 2);
                $this->_query->addWhere($field . " between ? and ?", array($start, $end));
                break;
            case 'like':
            default:
                $this->_query->addWhere($field . " LIKE ?", "%" . $filter . "%");
                break;
        }
        
        return $this;
    }

    /**
     * Insert an array of key=>values in the specified table
     *
     * @param string $table
     * @param array $post
     */
    public function insert($table, array $post)
    {
        $tableModel = $this->_getModelFromTable($table);
        $id = Doctrine::getTable($tableModel)->getIdentifier();
        
        /**
         * @var Doctrine_Record
         */
        $model = new $tableModel;
        $model->fromArray($post);
        $success = $model->trySave();
        
        return (!is_array($id)) ? $model->$id : $success;
    }

    /**
     *Update values in a table using the $condition clause
     *
     *The condition clause is a $field=>$value array
     *that should be escaped by YOU (if your class doesn't do that for you)
     * and usinf the AND operand
     *
     *Ex: array('user_id'=>'1','id_site'=>'12');
     *
     *Raw SQL: * WHERE user_id='1' AND id_site='12'
     *
     * @param string $table
     * @param array $post
     * @param array $condition
     */
    function update ($table, array $post, array $condition)
    {
        die('update');
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
     */
    function delete ($table, array $condition)
    {
        die('delete');
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
     * Cache handler.
     * 
     * TODO: Research what 'cache' does, might just need to look at the 
     *       bool and see if we need to set Doctrine Cache or not
     */
    function setCache($cache)
    {
        //die(Zend_Debug::dump($cache));
        //die('setCache');
    }

    /**
     * Build the form based on a Model or query
     */
    public function buildForm()
    {
        $table = $this->_queryParts['from']['tableModel'];
        $columns = Doctrine::getTable($table)->getColumns();
        //die(Zend_Debug::dump($columns, null, false));
        return $this->buildFormElements($columns);
    }
    
    public function buildFormElements($cols, $info = array())
    {
        $form = array();
        
        foreach ($cols as $column => $detail) {
            
            if ($detail['primary']) {
                continue;
            }
            
            $label = ucwords(str_replace('_', ' ', $column));
            
            switch ($detail['type']) {
                case 'enum':
                    $form['elements'][$column] = array('select', array('multiOptions' => $detail['values'], 'required' => ($detail['notnull'] == 1) ? false : true, 'label' => $label));
                    break;
                
                case 'string':
                case 'varchar':
                case 'char':
                    $length = $detail['length'];
                    $form['elements'][$column] = array('text', array('validators' => array(array('stringLength', false, array(0, $length))), 'size' => 40, 'label' => $label, 'required' => ($detail['notnull'] == 1) ? false : true, 'value' => (! is_null($detail['default']) ? $detail['default'] : "")));
                    break;
                case 'date':
                    $form['elements'][$column] = array('text', array('validators' => array(array('Date')), 'size' => 10, 'label' => $label, 'required' => ($detail['notnull'] == 1) ? false : true, 'value' => (! is_null($detail['default']) ? $detail['default'] : "")));
                    break;
                case 'datetime':
                case 'timestamp':
                    $form['elements'][$column] = array('text', array('validators' => array(array(new Zend_Validate_Date('Y-m-d H:i:s'))), 'size' => 19, 'label' => $label, 'required' => ($detail['notnull'] == 1) ? false : true, 'value' => (! is_null($detail['default']) ? $detail['default'] : "")));
                    break;
                
                case 'text':
                case 'mediumtext':
                case 'longtext':
                case 'smalltext':
                    $form['elements'][$column] = array('textarea', array('label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'filters' => array('StripTags')));
                    break;
                
                case 'integer':
                case 'int':
                case 'bigint':
                case 'mediumint':
                case 'smallint':
                case 'tinyint':
                    $isZero = (! is_null($detail['default']) && $detail['default'] == "0") ? true : false;
                    $form['elements'][$column] = array('text', array('validators' => array('Digits'), 'label' => $label, 'size' => 10, 'required' => ($isZero == false && $detail['notnull'] == 1) ? false : true, 'value' => (! is_null($detail['default']) ? $detail['default'] : "")));
                    break;

                case 'float':
                case 'decimal':
                case 'double':
                    $form['elements'][$column] = array('text', array('validators' => array('Float'), 'size' => 10, 'label' => $label, 'required' => ($detail['notnull'] == 1) ? false : true, 'value' => (! is_null($detail['default']) ? $detail['default'] : "")));
                    break;

                default:
                    break;
            }
        }
        //die(Zend_Debug::dump($form, null, false));
        return $form;
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
                list($fieldName, $fieldAlias) = explode(' ', trim($field));
                
                if (empty($fieldAlias)) {
                    $fieldAlias = str_replace('.', '_', $fieldName);
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
                    $this->_queryParts = array_merge($this->_queryParts, $this->_explodeFrom($field));
                } else {
                    $join = explode('JOIN', $field);
                    $join = array_map('trim', $join);
                    
                    $joinType = strtolower($join[0]);
                    
                    $this->_queryParts = array_merge($this->_queryParts, $this->_explodeJoin($join[1], $joinType));
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
        $this->_setFromParts();
        
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
            $addColumn = (!empty($fromAlias)) ? $fromAlias . '.' . $fromColumn . ' AS ' . $fromColumn : $fromColumn;
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
        
        list($table, $alias) = explode(' ', $from);
        
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
        
        $tableModel = Doctrine::getTable('Model_Country')->getRelation('City')->getClass();
        
        $return['join'][strtolower($joinType)][] = array(
            'alias'      => $alias,
            'tableModel' => $tableModel,
            'tableAlias' => $tableAlias,
            'joinOn'     => $joinOn
        );
        
        return $return;
    }
    
    private function _removeAs($subject)
    {
        return str_replace(array(' AS', ' As', ' aS', ' as'), array('', '', '', ''), $subject);
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
}