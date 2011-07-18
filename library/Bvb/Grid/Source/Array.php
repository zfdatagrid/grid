<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package   Bvb_Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id$
 * @link      http://zfdatagrid.com
 */
class Bvb_Grid_Source_Array implements Bvb_Grid_Source_SourceInterface {

    /**
     * Fields list
     *
     * @var array
     */
    protected $_fields = array();
    /**
     * Raw result
     *
     * @var array
     */
    protected $_rawResult = array();
    /**
     * Current Offset
     *
     * @var int
     */
    protected $_offset;
    /**
     * Current Result start
     *
     * @var int
     */
    protected $_start;
    /**
     * Total Records Foun
     *
     * @var int
     */
    protected $_totalRecords = 0;
    /**
     * Source nama
     *
     * @var string
     */
    protected $_sourceName;
    /**
     * Cache Instance
     *
     * @var mixed
     */
    protected $_cache;
    /**
     * Primary Key
     *
     * @var string|null
     */
    protected $_primaryKey = null;

    public function __construct(array $array, $titles = null)
    {
        if (count($array) > 0) {
            $min = min(array_keys($array));
            if ($titles === null || count($titles) != count($array[$min])) {
                $this->_fields = array_keys($array[$min]);
            } else {
                $this->_fields = $titles;
                foreach ($array as $key => $value) {
                    $array[$key] = array_combine($titles, $value);
                }
            }
        } elseif ($titles !== null) {
            $this->_fields = $titles;
        }

        if (count($this->_fields) == 0) {
            $this->_fields = array('Default');
        }

        $this->_rawResult = $array;
        $this->_sourceName = 'array';
    }

    public function quoteValue($value)
    {
        return $value;
    }

    /**
     * Resets query order
     *
     * @return bool
     */
    public function resetOrder()
    {
        return true;
    }

    /**
     * Resets result limit
     *
     * @return bool
     */
    public function resetLimit()
    {
        return true;
    }

    /**
     * Returns source name
     *
     * @return string
     */
    public function getSourceName()
    {
        return $this->_sourceName;
    }

    /**
     * Builds fields information.
     *
     * @return array
     */
    public function buildFields()
    {
        $fields = $this->_fields;

        $final = array();

        foreach ($fields as $value) {
            if ($value == '_zfgId') {
                $final[$value] = array('title' => ucfirst(str_replace('_', ' ', $value)),
                    'field' => $value, 'remove' => 1);
            } else {
                $final[$value] = array('title' => ucfirst(str_replace('_', ' ', $value)),
                    'field' => $value);
            }
        }

        return $final;
    }

    /**
     * Builds query limit
     *
     * @param int $offset
     * @param int $start
     *
     * @return bool
     */
    public function buildQueryLimit($offset, $start)
    {
        $this->_offset = $offset;
        $this->_start = ($this->_totalRecords != 0) ? 0 : $start;
        return true;
    }

    /**
     * Adds a condition to filter
     *
     * @param string $filter
     * @param string $op
     * @param array  $completeField
     *
     * @return bool
     */
    public function addCondition($filter, $op, $completeField)
    {

        $explode = explode('.', $completeField['field']);
        $field = end($explode);

        foreach ($this->_rawResult as $key => $result) {
            foreach ($result as $fieldKey => $fieldValue) {
                if (strlen($filter) > 0 && $fieldKey == $field) {
                    if (!$this->_applySearchType($fieldValue, $filter, $op)) {
                        unset($this->_rawResult[$key]);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Apply the search to a give field when the adaptar is an array
     *
     * @param type $fieldValue
     * @param type $valueNeeded
     * @param type $op
     *
     * @return bool
     */
    protected function _applySearchType($fieldValue, $valueNeeded, $op)
    {
        switch ($op) {
            case 'equal':
            case '=':
                if ($valueNeeded == $fieldValue)
                    return true;
                break;
            case 'REGEXP':
                if (preg_match($valueNeeded, $fieldValue))
                    return true;
                break;
            case 'rlike':
                if (substr($fieldValue, 0, strlen($valueNeeded)) == $valueNeeded)
                    return true;
                break;
            case 'llike':
                if (substr($fieldValue, - strlen($valueNeeded)) == $valueNeeded)
                    return true;
                break;
            case '>=':
                if ($fieldValue >= $valueNeeded)
                    return true;
                break;
            case '>':
                if ($fieldValue > $valueNeeded)
                    return true;
                break;
            case '<>':
            case '!=':
                if ($fieldValue != $valueNeeded)
                    return true;
                break;
            case '<=':
                if ($fieldValue <= $valueNeeded)
                    return true;
                break;
            case '<':
                if ($fieldValue < $valueNeeded)
                    return true;
                break;
            default:
                $enc = stripos((string) $fieldValue, $valueNeeded);
                if ($enc !== false) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * returns query order
     */
    public function getSelectOrder()
    {
        return array();
    }

    /**
     * Describe table
     *
     * @return bool
     */
    public function getDescribeTable()
    {
        return false;
    }

    /**
     * Returns field options based on field type
     * Not applicable to array sources
     *
     * @param string $field
     *
     * @return string
     */
    public function getFilterValuesBasedOnFieldDefinition($field)
    {
        return 'text';
    }

    /**
     * Returns records count
     *
     * @return int
     */
    public function getTotalRecords()
    {
        return $this->_totalRecords;
    }

    /**
     * Executes query
     *
     * @return array
     */
    public function execute()
    {
        if ((int) $this->_totalRecords === 0) {
            $this->setTotalRecords(count($this->_rawResult));
        }

        if ($this->_offset == 0) {
            return array_slice($this->_rawResult, $this->_start);
        }

        return array_slice($this->_rawResult, $this->_start, $this->_offset);
    }

    /**
     * Returns main table name
     *
     * @return bool
     */
    public function getMainTable()
    {
        return true;
    }

    /**
     * Returns table list
     *
     * @return array
     */
    public function getTableList()
    {
        return array();
    }

    /**
     * Builds query order
     *
     * @param string $field
     * @param string $order
     * @param bool   $reset
     */
    public function buildQueryOrder($field, $order, $reset = false)
    {
        if (strtolower($order) == 'desc') {
            $sort = SORT_DESC;
        } else {
            $sort = SORT_ASC;
        }

        // Obtain a list of columns
        foreach ($this->_rawResult as $key => $row) {
            if (isset($row[$field])) {
                $result[$key] = $row[$field];
            } else {
                $result[$key] = '';
            }
        }

        array_multisort($result, $sort, $this->_rawResult);

        unset($result);
    }

    /**
     * Converts an object to an array
     *
     * @param object $data
     *
     * @return array
     */
    protected function _object2array($data)
    {
        if (!is_object($data) && !is_array($data))
            return $data;

        if (is_object($data))
            $data = get_object_vars($data);

        return array_map(array($this, '_object2array'), $data);
    }

    /**
     * Builds sql expressions
     *
     * @param array $value
     * @param array $where
     *
     * @return scalar
     */
    public function getSqlExp(array $value, $where = array())
    {
        if (is_array($value['functions'])) {
            $i = 0;
            foreach ($value['functions'] as $final) {
                if ($i == 0) {
                    $valor = $this->_applySqlExpToArray($value['value'], $final, null, $where);
                } else {
                    $valor = $this->_applySqlExpToArray($value['value'], $final, $valor, $where);
                }

                $i++;
            }
        } else {
            $valor = $this->_applySqlExpToArray($value['value'], $value['functions'], null, $where);
        }

        return $valor;
    }

    /**
     * Applies the SQL EXP options to an array
     *
     * @param $field
     * @param $operation
     * @param $option
     */
    protected function _applySqlExpToArray($field, $operation, $value = null, $where = array())
    {
        $field = trim($field);
        $array = array();

        if (null === $value) {
            foreach ($this->_rawResult as $value) {
                if ((count($where) > 0 && $value[key($where)] == reset($where)) || count($where) == 0) {
                    $array[] = $value[$field];
                }
            }
        } else {
            $array = array($value);
        }

        $operation = trim(strtolower($operation));

        switch ($operation) {
            case 'product':
                return array_product($array);
                break;
            case 'sum':
                return array_sum($array);
                break;
            case 'count':
                return count($array);
                break;
            case 'min':
                sort($array);
                return array_shift($array);
                break;
            case 'max':
                sort($array);
                return array_pop($array);
                break;
            case 'avg':
                return round(array_sum($array) / count($array));
                break;
            default:
                throw new Bvb_Grid_Exception('Operation not found');
                break;
        }
    }

    /**
     * Returns distincts values from a field
     *
     * @param string $field
     * @param string $fieldValue
     * @param string $order
     *
     * @return array
     */
    public function getDistinctValuesForFilters($field, $fieldValue, $order = 'name ASC')
    {
        $filter = array();
        foreach ($this->_rawResult as $value) {
            $filter[$value[$field]] = $value[$fieldValue];
        }

        return array_unique($filter);
    }

    /**
     * Returns record details
     *
     * @param array $where
     *
     * @return array
     */
    public function fetchDetail(array $where)
    {
        if (count($where) > 1) {
            throw new Bvb_Grid_Exception('At this moment only arrays with one primary key are supported');
        }

        $field = key($where);
        $valueToSearch = reset($where);

        foreach ($this->_rawResult as $key => $value) {
            if ($value[$field] == $valueToSearch)
                return $value;
        }
    }

    /**
     * Returns field tyoe
     *
     * @param string $field
     */
    public function getFieldType($field)
    {

    }

    /**
     * Returns select object
     */
    public function getSelectObject()
    {

    }

    /**
     * Adds fulltext search
     *
     * @param string $filter
     * @param string $field
     */
    public function addFullTextSearch($filter, $field)
    {

    }

    /**
     * Inserts a new record
     *
     * @param string $table
     * @param array $post
     */
    public function insert($table, array $post)
    {

    }

    /**
     * Updates a record
     *
     * @param string $table
     * @param array  $post
     * @param array  $condition
     */
    public function update($table, array $post, array $condition)
    {

    }

    /**
     * Deletes a record
     *
     * @param string $table
     * @param array  $condition
     */
    public function delete($table, array $condition)
    {

    }

    /**
     * Returns cache instance
     *
     * @return mixed
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Defines cache instance
     *
     * @param Zend_Cache $cache
     */
    public function setCache($cache)
    {
        if (!is_array($cache) && !$this->_cache) {
            $cache = array('enable' => 0);
        }

        $this->_cache = $cache;
    }

    /**
     * Builds form fields
     *
     * @param array $inputsType
     *
     * @return array
     */
    public function buildForm($inputsType = array())
    {
        $form = array();

        foreach ($this->_fields as $elements) {
            if ($elements == '_zfgId')
                continue;

            $label = ucwords(str_replace('_', ' ', $elements));
            $type = isset($inputsType[$elements]) ? $inputsType[$elements] : 'text';
            $form['elements'][$elements] = array($type, array('size' => 10, 'label' => $label));
        }

        return $form;
    }

    /**
     * Returns columns identifiers for result set
     *
     * @param string $table
     *
     * @return mixed
     */
    public function getIdentifierColumns($table)
    {
        if (in_array('_zfgId', $this->_fields)) {
            return array('_zfgId');
        }

        if (is_array($this->_primaryKey)) {
            return $this->_primaryKey;
        }
        return false;
    }

    /**
     * Returns mass actions id's
     *
     * @param string $table
     * @param array  $fields
     * @param string $separator
     *
     * @return type
     */
    public function getMassActionsIds($table, $fields, $separator = '-')
    {
        if (!$pk = $this->getIdentifierColumns()) {
            throw new Bvb_Grid_Exception('No primary key found');
        }

        return $this->getIdentifierColumns($table);
    }

    /**
     * Defines primary key
     *
     * @param array $pk
     * @return Bvb_Grid_Source_Array
     */
    public function setPrimaryKey(array $pk)
    {
        $this->_primaryKey = $pk;
        return $this;
    }

    /**
     * Builds filters values from table
     *
     * @param type $table
     * @param type $field
     * @param type $fieldValue
     * @param type $order
     */
    public function getValuesForFiltersFromTable($table, $field, $fieldValue, $order = 'name ASC')
    {

        throw new Exception('Not possible');
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

    /**
     * Returns a JSON encoded array of options to be used by auto-complete operations
     *
     * @var string $term       Term to search
     * @var string $field      Field to search
     * @var string $specialKey Key used by user to improve search (>, <>, *, etc, etc)
     * @var string $output     Output format. Default json
     *
     * @return json
     *
     */
    public function getAutoCompleteForFilter($term, $field, $specialKey='', $output = 'json')
    {
        throw new Exception('Not possible');
    }

    /**
     * If this adapter supports crud operations
     *
     * @return bool
     */
    public function hasCrud()
    {
        return true;
    }

    /**
     * Returns record details
     *
     * @param string $table
     * @param array  $condition
     *
     * @return mixed
     */
    public function getRecord($table, array $condition)
    {
        $key = key($condition);
        $value = reset($condition);
        foreach ($this->_rawResult as $id => $result) {
            if (isset($result[$key]) && $result[$key] == $value) {
                return $result;
            }
        }
        return false;
    }

    /**
     * Starts a new transaction
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return false;
    }

    /**
     * Commits active transaction
     *
     * @return bool
     */
    public function commit()
    {
        return false;
    }

    /**
     * Rollbacks current trasaction
     *
     * @return bool
     */
    public function rollBack()
    {
        return false;
    }

    /**
     * Returns current connection ID
     *
     * @return type
     */
    public function getConnectionId()
    {
        return 0;
    }

}