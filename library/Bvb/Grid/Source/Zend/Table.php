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


class Bvb_Grid_Source_Zend_Table extends Bvb_Grid_Source_Zend_Select
{

    private $_model;


    function getModel ()
    {
        return $this->_model;
    }


    function buildForm ($decorators)
    {
        $final = array();

        $info = $this->getModel()->info();
        $cols = $info['metadata'];
        $form = array();

        foreach ($cols as $column => $detail) {

            $label = ucwords(str_replace('_', ' ', $column));

            $next = false;

            if ($detail['PRIMARY'] == 1) {
                continue;
            }

            if (count($info['referenceMap']) > 0) {

                foreach ($info['referenceMap'] as $dep) {

                    if (is_array($dep['columns']) && in_array($column, $dep['columns'])) {
                        $refColumn = $dep['refColumns'][array_search($column, $dep['columns'])];
                    } elseif (is_string($dep['columns']) && $column == $dep['columns']) {
                        $refColumn = $dep['refColumns'];
                    } else {
                        continue;
                    }

                    $t = new $dep['refTableClass']();

                    $in = $t->info();

                    if ((count($in['cols']) == 1 && count($in['primary']) == 0) || count($in['primary']) > 1) {
                        throw new Exception('Columns:' . count($in['cols']) . ' Keys:' . count($in['primary']));
                        # break;
                    }

                    if (count($in['primary']) == 1) {
                        $field1 = array_shift($in['primary']);
                        $field2 = $refColumn;
                    }

                    $final['values'][$column] = array();
                    $r = $t->fetchAll()->toArray();

                    if ($detail['NULLABLE'] == 1) {
                        $final['values'][$column][""] = "-- Empty --";
                    }

                    foreach ($r as $field) {
                        $final['values'][$column][$field[$field1]] = $field[$field2];
                    }

                    $form['elements'][$column] = array('select', array('decorators' => $decorators->elementDecorators, 'multiOptions' => $final['values'][$column], 'label' => $label));

                    $next = true;

                }

            }

            if ($next === true) {
                continue;
            }

            if (stripos($detail['DATA_TYPE'], 'enum') !== false) {
                preg_match_all('/\'(.*?)\'/', $detail['DATA_TYPE'], $result);

                $options = array();
                foreach ($result[1] as $match) {
                    $options[$match] = ucfirst($match);
                }

                $form['elements'][$column] = array('select', array('decorators' => $decorators->elementDecorators, 'multiOptions' => $options, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'label' => $label));

                continue;
            }

            if (stripos($detail['DATA_TYPE'], 'set') !== false) {
                preg_match_all('/\'(.*?)\'/', $detail['DATA_TYPE'], $result);

                $options = array();
                foreach ($result[1] as $match) {
                    $options[$match] = ucfirst($match);
                }

                $form['elements'][$column] = array('multiCheckbox', array('decorators' => $decorators->elementDecorators, 'multiOptions' => $options, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'label' => $label));

                continue;
            }

            switch ($detail['DATA_TYPE']) {

                case 'varchar':
                case 'char':
                    $length = $detail['LENGTH'];
                    $form['elements'][$column] = array('text', array('decorators' => $decorators->elementDecorators, 'validators' => array(array('stringLength', false, array(0, $length))), 'size' => 40, 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'value' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : "")));
                    break;
                case 'date':
                    $form['elements'][$column] = array('text', array('decorators' => $decorators->elementDecorators, 'validators' => array(array('Date')), 'size' => 10, 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'value' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : "")));
                    break;
                case 'datetime':
                case 'timestamp':
                    $form['elements'][$column] = array('text', array('decorators' => $decorators->elementDecorators, 'validators' => array(array(new Zend_Validate_Date('Y-m-d H:i:s'))), 'size' => 19, 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'value' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : "")));
                    break;

                case 'text':
                case 'mediumtext':
                case 'longtext':
                case 'smalltext':
                    $form['elements'][$column] = array('textarea', array('decorators' => $decorators->elementDecorators, 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'filters' => array('StripTags')));
                    break;

                case 'int':
                case 'bigint':
                case 'mediumint':
                case 'smallint':
                case 'tinyint':
                    $defaultIsZero = (! is_null($detail['DEFAULT']) && $detail['DEFAULT'] == "0") ? true : false;
                    $form['elements'][$column] = array('text', array('decorators' => $decorators->elementDecorators, 'validators' => array('Digits'), 'label' => $label, 'size' => 10, 'required' => ($defaultIsZero == false && $detail['NULLABLE'] == 1) ? false : true, 'value' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : "")));
                    break;

                case 'float':
                case 'decimal':
                case 'double':
                    $form['elements'][$column] = array('text', array('decorators' => $decorators->elementDecorators, 'validators' => array('Float'), 'size' => 10, 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'value' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : "")));
                    break;

                default:
                    break;
            }
        }

        return $form;
    }


    /**
     * Creating a query using a Model.
     * @param Zend_Db_Table_Abstract $model
     * @return $this
     */
    function __construct (Zend_Db_Table_Abstract $model)
    {
        $this->_model = $model;
        $info = $model->info();

        $select = new Zend_Db_Select($model->getAdapter());

        $map = $info['referenceMap'];

        if (is_array($map) && count($map) > 0) {
            $columnsToRemove = array();

            foreach ($map as $sel) {
                if (is_array($sel['columns'])) {
                    $columnsToRemove = array_merge($columnsToRemove, $sel['columns']);
                } else {
                    $columnsToRemove[] = $sel['columns'];
                }
            }

            $columnsMainTable = array_diff($info['cols'], $columnsToRemove);

            $select->from($info['name'], $columnsMainTable);

            $i = 0;
            foreach ($map as $sel) {

                if ($i > 0) {
                    $alias = '_' . $i;
                } else {
                    $alias = '';
                }

                $newClass = new $sel['refTableClass']();
                $infoNewClass = $newClass->info();

                if (is_array($sel['columns'])) {
                    $cols = array_combine($sel['columns'], $sel['refColumns']);

                    foreach ($sel['columns'] as $key => $value) {

                        if ($i > 0) {
                            $alias = '_' . $i;
                        } else {
                            $alias = '';
                        }
                        $select->joinLeft(array($infoNewClass['name'] . $alias => $infoNewClass['name']), $infoNewClass['name'] . $alias . '.' . array_shift($infoNewClass['primary']) . ' = ' . $info['name'] . '.' . $sel['columns'][$key], $cols);
                        $i ++;
                    }

                } else {
                    $cols = array($sel['columns'] => $sel['refColumns']);

                    $select->joinLeft(array($infoNewClass['name'] . $alias => $infoNewClass['name']), $infoNewClass['name'] . $alias . '.' . array_shift($infoNewClass['primary']) . ' = ' . $info['name'] . '.' . $sel['columns'], $cols);
                }

                $i ++;
            }
        } else {
            $select->from($info['name']);
        }

        parent::__construct($select);

        return $this;
    }


    function getRecord ($table, array $condition)
    {
        $final = $this->getModel()->fetchRow($condition);
        return $final->toArray();
    }


    function delete ($table, array $condition)
    {
        return $this->getModel()->delete($this->buildWhereCondition($condition));
    }


    function update ($table, array $post, array $condition)
    {
        return $this->getModel()->update($post, $this->buildWhereCondition($condition));
    }


    function insert ($table, array $post)
    {
        return $this->getModel()->insert($post);
    }

}