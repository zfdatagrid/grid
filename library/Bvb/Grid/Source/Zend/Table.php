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


    public function getModel ()
    {
        return $this->_model;
    }


    public function buildForm ($fields = array(), $inputsType = array())
    {
        $info = $this->getModel()
            ->info();
        $cols = $info['metadata'];
        $form = $this->buildFormElements($cols, $info, $inputsType);
        return $form;
    }


    /**
     * Creating a query using a Model.
     * @param Zend_Db_Table_Abstract $model
     * @return $this
     */
    public function __construct (Zend_Db_Table_Abstract $model)
    {
        $this->_model = $model;
        $info = $model->info();

        $select = new Zend_Db_Select($model->getAdapter());

        $map = $info['referenceMap'];

        if ( is_array($map) && count($map) > 0 ) {
            $columnsToRemove = array();

            foreach ( $map as $sel ) {
                if ( is_array($sel['columns']) ) {
                    $columnsToRemove = array_merge($columnsToRemove, $sel['columns']);
                } else {
                    $columnsToRemove[] = $sel['columns'];
                }
            }

            $columnsMainTable = array_diff($info['cols'], $columnsToRemove);

            $select->from($info['name'], $columnsMainTable, $info['schema']);

            $tAlias = array();

            foreach ( $map as $sel ) {
                $newClass = new $sel['refTableClass']();
                $infoNewClass = $newClass->info();

                if ( ! isset($tAlias[$infoNewClass['name']]) ) {
                    $tAlias[$infoNewClass['name']] = 0;
                }

                $alias = $tAlias[$infoNewClass['name']] > 0 ? '_' . $tAlias[$infoNewClass['name']] : '';

                if ( is_array($sel['columns']) ) {
                    $cols = array_combine($sel['columns'], $sel['refColumns']);

                    foreach ( $sel['columns'] as $key => $value ) {
                        $alias = $tAlias[$infoNewClass['name']] > 0 ? '_' . $tAlias[$infoNewClass['name']] : '';

                        $select->joinLeft(array($infoNewClass['name'] . $alias => $infoNewClass['name']), $infoNewClass['name'] . $alias . '.' . reset($infoNewClass['primary']) . ' = ' . $info['name'] . '.' . $sel['columns'][$key], $cols);
                        $tAlias[$infoNewClass['name']] ++;
                    }
                } else {
                    $cols = array($sel['columns'] => $sel['refColumns']);
                    $select->joinLeft(array($infoNewClass['name'] . $alias => $infoNewClass['name']), $infoNewClass['name'] . $alias . '.' . array_shift($infoNewClass['primary']) . ' = ' . $info['name'] . '.' . $sel['columns'], $cols);
                }

                $tAlias[$infoNewClass['name']] ++;
            }
        } else {
            $select->from($info['name'], '*', $info['schema']); 
        }

        parent::__construct($select);

        return $this;
    }


    public function getRecord ($table, array $condition)
    {

        if ( $this->_cache['use'] == 1 ) {
            $hash = 'Bvb_Grid_Model' . md5($this->buildWhereCondition($condition));
            if ( ! $result = $this->_cache['instance']
                ->load($hash) ) {
                $result = $this->getModel()
                    ->fetchRow($this->buildWhereCondition($condition));
                $this->_cache['instance']
                    ->save($result, $hash, array($this->_cache['tag']));
            }
        } else {
            $result = $this->getModel()
                ->fetchRow($this->buildWhereCondition($condition));
        }

        if ( $result === null ) {
            return false;
        }

        return $result->toArray();
    }


    public function delete ($table, array $condition)
    {
        if ( $this->_cache['use'] == 1 ) {
            $this->_cache['instance']
                ->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }
        return $this->getModel()
            ->delete($this->buildWhereCondition($condition));
    }


    public function update ($table, array $post, array $condition)
    {
        if ( $this->_cache['use'] == 1 ) {
            $this->_cache['instance']
                ->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }
        return $this->getModel()
            ->update($post, $this->buildWhereCondition($condition));
    }


    public function insert ($table, array $post)
    {
        if ( $this->_cache['use'] == 1 ) {
            $this->_cache['instance']
                ->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->_cache['tag']));
        }
        return $this->getModel()
            ->insert($post);
    }


    /**
     * Get the primary table key
     * This is important because we only allow edit, add or remove records
     * From tables that have on primary key
     *
     * @return array
     */
    public function getPrimaryKey ($table)
    {
        $info = $this->_model
            ->info();

        $keys = array();
        foreach ( $info['primary'] as $pk ) {
            $keys[] = $info['metadata'][$pk]['TABLE_NAME'] . '.' . $pk;
        }

        return $keys;
    }
}