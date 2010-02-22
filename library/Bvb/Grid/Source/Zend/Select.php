<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id: Column.php 492 2010-01-26 17:08:02Z pao.fresco $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class Bvb_Grid_Source_Zend_Select implements Bvb_Grid_Source_Interface
{

    protected $_select;

    protected $_server;

    protected $_describeTables;


    function __construct (Zend_Db_Select $select)
    {

        $this->_select = $select;
        $this->init($this->_select);
        return $this;
    }


    /**
     * Define the query using Zend_Db_Select instance
     *
     * @param Zend_Db_Select $select
     * @return $this
     */
    public function init (Zend_Db_Select $select)
    {
        $this->_setDb($select->getAdapter());
        $adapter = get_class($select->getAdapter());
        $adapter = str_replace("Zend_Db_Adapter_", "", $adapter);

        if (stripos($adapter, 'mysql') !== false) {
            $this->_server = 'mysql';
        } else {
            $adapter = str_replace('Pdo_', '', $adapter);
            $this->_server = strtolower($adapter);
        }

        return $this;
    }


    /**
     * Set db
     * @param Zend_Db_Adapter_Abstract $db
     */
    protected function _setDb (Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
        return $this;
    }


    function hasCrud ()
    {
        return true;
    }


    function getRecord ($table, array $condition)
    {

        $select = new Zend_Db_Select($this->_getDb());
        $select->from($table);

        foreach ($condition as $field => $value) {
            $select->where($field . '=?', $value);
        }

        $final = $select->query(Zend_Db::FETCH_ASSOC);

        return $final->fetchAll();
    }


    /**
     * Build the fields based on Zend_Db_Select
     * @param $fields
     * @param $tables
     */
    function buildFields ()
    {

        $fields = $this->_select->getPart(Zend_Db_Select::COLUMNS);
        $tables = $this->_select->getPart(Zend_Db_Select::FROM);

        $returnFields = array();

        foreach ($fields as $field => $value) {

            /**
             * Select all fields from the table
             */
            if ($value[1] == '*') {

                if (array_key_exists($value[0], $tables)) {
                    $tableFields = $this->getDescribeTable($tables[$value[0]]['tableName']);
                }
                $tableFields = array_keys($tableFields);

                foreach ($tableFields as $field) {
                    $title = ucwords(str_replace('_', ' ', $field));
                    $returnFields[$field] = array('title' => $title, 'field' => $value[0] . '.' . $field);
                }

            } else {

                $explode = explode('.', $value[1]);
                $title = ucwords(str_replace("_", ' ', end($explode)));

                if (is_object($value[1])) {
                    $title = ucwords(str_replace('_', ' ', $value[2]));
                    $returnFields[$value[2]] = array('title' => $title, 'field' => $value[0] . '.' . $value[2]);
                } elseif (strlen($value[2]) > 0) {
                    $title = ucwords(str_replace('_', ' ', $value[2]));
                    $returnFields[$value[2]] = array('title' => $title, 'field' => $value[0] . '.' . $value[1]);
                } else {
                    $title = ucwords(str_replace('_', ' ', $value[1]));
                    $returnFields[$value[1]] = array('title' => $title, 'field' => $value[0] . '.' . $value[1]);
                }

            }
        }

        return $returnFields;

        $this->_allFieldsAdded = true;
        if (count($this->_updateColumnQueue) > 0) {
            foreach ($this->_updateColumnQueue as $field => $options) {
                if (! array_key_exists($field, $this->data['fields'])) continue;
                $this->updateColumn($field, $options);
            }
            $this->_updateColumnQueue = array();
        }

        return $this;
    }


    /**
     * Get table description and then save it to a array.
     *
     * @param array|string $table
     * @return array
     */
    function getDescribeTable ($table)
    {
        if (! isset($this->_describeTables[$table]) || ! is_array($this->_describeTables[$table])) {

            $describe = $this->_getDb()->describeTable($table);
            $this->_describeTables[$table] = $describe;
        }

        return $this->_describeTables[$table];
    }


    function _getDb ()
    {
        return $this->_db;
    }


    function execute ()
    {
        $final = $this->_select->query(Zend_Db::FETCH_ASSOC);
        return $final->fetchAll();
    }


    function fetchDetail (array $where)
    {
        foreach ($where as $field => $value) {
            $this->_select->where($field . '=?', $value);
        }

        $this->_select->reset(Zend_Db_Select::LIMIT_COUNT);
        $this->_select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $final = $this->_select->query(Zend_Db::FETCH_ASSOC);

        return $final->fetchAll();
    }


    /**
     * Count the rows total without the limit
     *
     * @return void
     */
    function getTotalRecords ()
    {
        $selectCount = clone $this->_select;
        $selectCount->reset(Zend_Db_Select::COLUMNS);
        $selectCount->reset(Zend_Db_Select::LIMIT_OFFSET);
        $selectCount->reset(Zend_Db_Select::LIMIT_COUNT);
        $selectCount->reset(Zend_Db_Select::ORDER);

        $selectCount->columns(new Zend_Db_Expr('COUNT(*) AS TOTAL '));

        $final = $selectCount->query(Zend_Db::FETCH_ASSOC);

        return $final->fetchColumn();

    }


    function getTableList ()
    {
        return $this->_select->getPart(Zend_Db_Select::FROM);
    }


    function getFilterValuesBasedOnFieldDefinition ($field)
    {
        $tableList = $this->getTableList();

        $explode = explode('.', $field);
        $tableName = reset($explode);
        $field = end($explode);

        if (array_key_exists($tableName, $tableList)) {
            $tableName = $tableList[$tableName]['tableName'];
        }

        $table = $this->getDescribeTable($tableName);

        $type = $table[$field]['DATA_TYPE'];

        $return = 'text';

        if (substr($type, 0, 4) == 'enum') {
            preg_match_all('/\'(.*?)\'/', $type, $result);

            $return = array_combine($result[1], $result[1]);
        }


        return $return;
    }


    function getFieldType ($field)
    {
        $tableList = $this->getTableList();

        $explode = explode('.', $field);
        $tableName = reset($explode);
        $field = end($explode);

        if (array_key_exists($tableName, $tableList)) {
            $tableName = $tableList[$tableName]['tableName'];
        }

        $table = $this->getDescribeTable($tableName);
        $type = $table[$field]['DATA_TYPE'];

        if (substr($type, 0, 3) == 'set') {
            return 'set';
        }

        return $type;
    }


    function getMainTable ()
    {
        $return = array();

        $from = $this->_select->getPart(Zend_Db_Select::FROM);

        foreach ($from as $key => $tables) {

            if ($tables['joinType'] == 'from' || count($from) == 1) {
                $return['table'] = $tables['tableName'];
                break;
            }
        }

        if (count($return) == 0) {
            $table = reset($from);
            $return['table'] = $table['tableName'];
        }

        return $return;
    }


    function buildQueryOrder ($field, $order, $reset = false)
    {
        foreach ($this->_select->getPart(Zend_Db_Select::COLUMNS) as $col) {
            if (($col[0] . '.' . $col[2] == $field) && is_object($col[1])) {
                $field = $col[2];
            }
        }

        if ($reset === true) {
            $this->_select->reset('order');
        }

        $this->_select->order($field . ' ' . $order);
        return $this;

    }


    function buildQueryLimit ($start, $offset)
    {
        $this->_select->limit($start, $offset);
    }


    function getSelectObject ()
    {
        return $this->_select;
    }


    function getSelectOrder ()
    {

        $result = $this->_select->getPart(Zend_Db_Select::ORDER);

        if (count($result) == 0) {
            return array();
        }

        return $result[0];
    }


    function getDistinctValuesForFilters ($field, $value)
    {

        $distinct = clone $this->_select;

        $distinct->reset(Zend_Db_Select::COLUMNS);
        $distinct->reset(Zend_Db_Select::ORDER);
        $distinct->reset(Zend_Db_Select::LIMIT_COUNT);
        $distinct->reset(Zend_Db_Select::LIMIT_OFFSET);

        $distinct->columns(array('field' => new Zend_Db_Expr("DISTINCT({$field})")));
        $distinct->columns(array('value' => $value));
        $distinct->order(' value ASC');

        $result = $distinct->query(Zend_Db::FETCH_ASSOC);

        $result = $result->fetchAll();

        $final = array();

        foreach ($result as $value) {
            $final[$value['field']] = $value['value'];
        }

        return $final;
    }


    function getSqlExp (array $value)
    {

        if (is_array($value)) {
            $valor = '';
            foreach ($value['functions'] as $final) {
                $valor .= $final . '(';
            }
            $valor .= $value['value'] . str_repeat(')', count($value['functions']));
        } else {
            $valor = "$value(" . $value['value'] . ")";
        }

        $select = clone $this->_select;
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::GROUP);
        $select->columns(new Zend_Db_Expr($valor . ' AS TOTAL'));

        $final = $select->query(Zend_Db::FETCH_ASSOC);
        return $final->fetchColumn();
    }


    function getColumns ()
    {
        return $this->_select->getPart('columns');
    }


    function addFullTextSearch ($filter, $field)
    {

        $full = $field['search'];

        if (! isset($full['indexes'])) {
            $indexes = $field['field'];
        } elseif (is_array($full['indexes'])) {
            $indexes = implode(',', array_values($full['indexes']));
        } elseif (is_string($full['indexes'])) {
            $indexes = $full['indexes'];
        }

        $extra = isset($full['extra']) ? $full['extra'] : 'boolean';

        if (! in_array($extra, array('boolean', 'queryExpansion', false))) {
            throw new Bvb_Grid_Exception('Unrecognized value in extra key');
        }

        if ($extra == 'boolean') {
            $extra = 'IN BOOLEAN MODE';
        } elseif ($extra == 'queryExpansion') {
            $extra = ' WITH QUERY EXPANSION ';
        } else {
            $extra = '';
        }

        if ($extra == 'IN BOOLEAN MODE') {
            $filter = preg_replace("/\s+/", " +", $this->_getDb()->quote(' ' . $filter));
        } else {
            $filter = $this->_getDb()->quote($filter);
        }

        $this->_select->where(new Zend_Db_Expr("MATCH ($indexes) AGAINST ($filter $extra) "));
        return;
    }


    function addCondition ($filter, $op, $completeField)
    {

        $explode = explode('.', $completeField['field']);
        $field = end($explode);

        $columns = $this->getColumns();

        foreach ($columns as $value) {
            if ($field == $value[2]) {
                if (is_object($value[1])) {
                    $field = $value[1]->__toString();
                } else {
                    $field = $value[0] . '.' . $value[1];
                }
                break;
            } elseif ($field == $value[0]) {
                $field = $value[0] . '.' . $value[1];
                break;
            }
        }

        if (strpos($field, '.') === false) {
            $field = $completeField['field'];
        }

        switch ($op) {
            case 'equal':
            case '=':
                $this->_select->where($field . ' = ?', $filter);
                break;
            case 'REGEX':
                $this->_select->where(new Zend_Db_Expr($this->_getDb()->quoteIdentifier($field) . " REGEXP " . $this->_getDb()->quote($filter)));
                break;
            case 'rlike':
                $this->_select->where(new Zend_Db_Expr($this->_getDb()->quoteIdentifier($field) . " LIKE " . $this->_getDb()->quote($filter . "%")));
                break;
            case 'llike':
                $this->_select->where(new Zend_Db_Expr($this->_getDb()->quoteIdentifier($field) . " LIKE " . $this->_getDb()->quote("%" . $filter)));
                break;
            case '>=':
                $this->_select->where($field . " >= ?", $filter);
                break;
            case '>':
                $this->_select->where($field . " > ?", $filter);
                break;
            case '<>':
            case '!=':
                $this->_select->where($field . " <> ?", $filter);
                break;
            case '<=':
                $this->_select->where($field . " <= ?", $filter);
                break;
            case '<':
                $this->_select->where($field . " < ?", $filter);
                break;
            case 'range':

                $start = substr($filter, 0, strpos($filter, '<>'));
                $end = substr($filter, strpos($filter, '<>') + 2);
                $this->_select->where($field . " between " . $this->_getDb()->quote($start) . " and " . $this->_getDb()->quote($end));
                break;
            case 'like':
            default:
                $this->_select->where(new Zend_Db_Expr($this->_getDb()->quoteIdentifier($field) . " LIKE " . $this->_getDb()->quote("%" . $filter . "%")));
                break;
        }

    }


    /**
     * Returns server name (mysql|pgsql|etc)
     */
    function getSourceName ()
    {
        return $this->_server;
    }


    function insert ($table, array $post)
    {
        return $this->_getDb()->insert($table, $post);
    }


    function update ($table, array $post, array $condition)
    {
        return $this->_getDb()->update($table, $post, $this->buildWhereCondition($condition));
    }


    function delete ($table, array $condition)
    {
        return $this->_getDb()->delete($table, $this->buildWhereCondition($condition));
    }


    function buildWhereCondition (array $condition)
    {
        $where = '';
        foreach ($condition as $field => $value) {
            $where .= 'AND ' . $this->_getDb()->quoteIdentifier($field) . ' = ' . $this->_getDb()->quote($value) . ' ';
        }
        return substr($where, 3);

    }
}