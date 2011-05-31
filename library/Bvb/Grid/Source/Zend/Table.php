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

class Bvb_Grid_Source_Zend_Table extends Bvb_Grid_Source_Zend_Select
{

    private $_model;

    /**
     *
     * @var array
     */
    protected $_relationMap = array();

    /**
     * Returns current model
     *
     * @return Zend_Db_Table
     */

    public function getModel ()
    {
        return $this->_model;
    }

    /**
     * Returns current relationmap
     *
     * @return array
     */

    public function getRelationMap()
    {
        return $this->_relationMap;
    }


    /**
     * Builds form input types
     *
     * @param array $inputsType Elements input types
     *
     * @return array
     */
    public function buildForm ($inputsType = array())
    {
        $info = $this->getModel()->info();
        $cols = $info['metadata'];
        $form = $this->buildFormElements($cols, $info, $inputsType, $this->_relationMap);
        return $form;
    }


    /**
     * Creating a query using a Model.
     *
     * @param Zend_Db_Table_Abstract $model
     * @param array                  $relationmap Relation map for joins
     *
     * @return $this
     */
    public function __construct (Zend_Db_Table_Abstract $model,array $relationMap = array())
    {
        $this->_model = $model;
        $this->_relationMap = $relationMap;
        $info = $model->info();
        $select = $model->select();

        $map = $info['referenceMap'];

        $map = array_merge_recursive($map,$this->_relationMap);
        
        $this->_relationMap = $map;

        if ( is_array($map) && count($map) > 0 ) {

            $select->setIntegrityCheck(false);

            /**
            $columnsToRemove = array();
            foreach ( $map as $sel ) {
                if ( is_array($sel['columns']) ) {
                    $columnsToRemove = array_merge($columnsToRemove, $sel['columns']);
                } else {
                    $columnsToRemove[] = $sel['columns'];
                }
            }

            $columnsMainTable = array_diff($info['cols'], $columnsToRemove);*/
            $columnsMainTable = $info['cols'];
            $select->from($info['name'], $columnsMainTable, $info['schema']);

            $tAlias = array($info['name'] => 1);
            $this->_setJoins($info['name'], $map, $select, $tAlias);

        }else{
            $select->from($info['name'], $info['cols'], $info['schema']);
        }

        parent::__construct($select);

        return $this;
    }


    private function _setJoins ($tName, array $map, &$select, array &$tAlias = array())
    {
        foreach ( $map as $sel ) {

            $class = new $sel['refTableClass']();
            $info = $class->info();

            if ( ! isset($tAlias[$info['name']]) ) {
                $tAlias[$info['name']] = 0;
            }

            $alias = $tAlias[$info['name']] > 0 ? '_' . $tAlias[$info['name']] : null;

            if ( is_array($sel['columns']) ) {

                if ( ! is_array($sel['refColumns']) || (count($sel['columns']) != count($sel['refColumns'])) ) {
                    throw new Bvb_Grid_Exception('Mapping of ' . $sel['refTableClass'] . ' is wrong: columns and refColumns must have same type. In case of arrays, they must have same length.');
                }

                if ( ! array_key_exists('refBvbColumns', $sel) ) {
                    $cols = null;
                } else {
                    if ( ! is_array($sel['refBvbColumns']) ) {
                        $cols = array($sel['columns'][0] => $sel['refBvbColumns']);
                    } else {
                        $cols = $sel['refBvbColumns'];
                    }
                }

                $tFields = array_combine($sel['columns'], $sel['refColumns']);

                $join = null;
                foreach ( $tFields as $key => $value ) {
                    if ( ! is_null($join) ) {
                        $join .= ' AND ';
                    }
                    $join .= $info['name'] . $alias . '.' . $value . ' = ' . $tName . '.' . $key;
                }
                $select->joinLeft(array($info['name'] . $alias => $info['name']), $join, $cols, $info['schema']);
                $tAlias[$info['name']] ++;

            } else {
                if ( is_array($sel['refColumns']) ) {
                    throw new Bvb_Grid_Exception('Mapping of ' . $sel['refTableClass'] . ' is wrong: columns and refColumns must have same type.');
                }

                if ( array_key_exists('refBvbColumns', $sel) ) {
                    if ( is_array($sel['refBvbColumns']) ) {
                        $cols = $sel['refBvbColumns'];
                    } else {
                        $cols = array_combine((array) $sel['columns'], (array) $sel['refBvbColumns']);
                    }
                } else {
                    $cols = null;
                }
                $select->joinLeft(array($info['name'] . $alias => $info['name']), $info['name'] . $alias . '.' . array_shift($info['primary']) . ' = ' . $tName . '.' . $sel['columns'], $cols, $info['schema']);

            }

            $tAlias[$info['name']] ++;

            if ( ! array_key_exists('refBvbFollow', $sel) ) {
                $sel['refBvbFollow'] = false;
            }

            if ( is_array($info['referenceMap']) && count($info['referenceMap']) > 0 && $sel['refBvbFollow'] ) {
                $this->_setJoins($info['name'], $info['referenceMap'], $select, $tAlias);
            }
        }
    }


    public function getRecord ($table, array $condition)
    {


        if ( $this->_cache['enable'] == 1 ) {
            $hash = 'Bvb_Grid_Model' . md5($this->buildWhereCondition($condition));
            if ( ! $result = $this->_cache['instance']->load($hash) ) {
                $result = call_user_func_array(array($this->getModel(),'find'), $condition);
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {

            $result = call_user_func_array(array($this->getModel(),'find'), $condition);
        }


        if($result->current() === null)
        {
            return array();
        }
        
        return $result->current()->toArray();
    }


    /**
     * Executes the current query and returns an associative array of results
     *
     * @return array
     */
    public function execute()
    {

        $this->_prepareExecute();


        if ($this->_cache['enable'] == 1) {
            $hash = 'Bvb_Grid' . md5($this->_select->__toString());
            if (!$result = $this->_cache['instance']->load($hash)) {
                $result = $this->getModel()->fetchAll($this->_select);
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {

            $result = $this->getModel()->fetchAll($this->_select);

            if ($this->_server == 'mysql') {
                $this->_totalRecords = $this->_select->getAdapter()->fetchOne('select FOUND_ROWS()');
            }
        }

        return $result;
    }



    public function fetchDetail ( array $where)
    {
        if ( $this->_cache['enable'] == 1 ) {
            $hash = 'Bvb_Grid_Model' . md5($this->buildWhereCondition($where));
            if ( ! $result = $this->_cache['instance']->load($hash) ) {
                $result = call_user_func_array(array($this->getModel(),'find'), $where);
                $this->_cache['instance']->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $result = call_user_func_array(array($this->getModel(),'find'), $where);
        }

        if ( $result === null ) {
            return false;
        }
        
        $result = $result->toArray();

        
        if (isset($result[0]['ZFG_GHOST'])) {
            unset($result[0]['ZFG_GHOST']);
        }
        
        return $result[0];
    }


    public function delete ($table, array $condition)
    {
        if ( $this->_cache['enable'] == 1 ) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }
        
        $result = call_user_func_array(array($this->getModel(),'find'), $condition);

         if($result->current() === null)
        {
            return array();
        }
        
        $return = $result->current()->delete();
        
        return $return;
        
    }


    public function update ($table, array $post, array $condition)
    {
        if ( $this->_cache['enable'] == 1 ) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }

        $result = call_user_func_array(array($this->getModel(),'find'), $condition);

        $return = $result->current()->setFromArray($post)->save();
        
        return $return;
    }


    public function insert ($table, array $post)
    {
        if ( $this->_cache['enable'] == 1 ) {
            $this->_cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }
        
        $return = $this->getModel()->createRow($post)->save();

        return $return;

    }


    /**
     * Get the primary table key
     * This is important because we only allow edit, add or remove records
     * From tables that have on primary key
     *
     * @return array
     */
    public function getIdentifierColumns ($table)
    {
        $info = $this->_model->info();
        
        $keys = array();
        foreach ( $info['primary'] as $pk ) {
            $keys[] = $info['metadata'][$pk]['TABLE_NAME'] . '.' . $pk;
        }

        return $keys;
    }

}