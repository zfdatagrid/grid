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
 * @version    $Id$
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

class Bvb_Grid_Form extends Zend_Form
{

    public $options;

    public $fields;

    public $cascadeDelete;

    protected $_model;

    public $elementDecorators = array(
                                    'ViewHelper',
                                    'Errors',
                                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                                    array(array('label' => 'Label'), array('tag' => 'td')),
                                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    public $buttonHidden = array('ViewHelper');

    function __call ($name, $args)
    {
        if (substr(strtolower($name), 0, 3) == 'set') {
            $name = substr($name, 3);
            $name[0] = strtolower($name[0]);
            $this->options[$name] = $args[0];

            return $this;
        }

        parent::__call($name, $args);

    }

    function setCallbackBeforeDelete ($callback)
    {

        if (! is_callable($callback)) {
            throw new Exception($callback . ' not callable');
        }
        $this->options['callbackBeforeDelete'] = $callback;

        return $this;
    }

    function setCallbackBeforeUpdate ($callback)
    {

        if (! is_callable($callback)) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackBeforeUpdate'] = $callback;

        return $this;
    }

    function setCallbackBeforeInsert ($callback)
    {

        if (! is_callable($callback)) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackBeforeInsert'] = $callback;

        return $this;
    }

    function setCallbackAfterDelete ($callback)
    {

        if (! is_callable($callback)) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterDelete'] = $callback;

        return $this;
    }

    function setCallbackAfterUpdate ($callback)
    {

        if (! is_callable($callback)) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterUpdate'] = $callback;

        return $this;
    }

    function setCallbackAfterInsert ($callback)
    {

        if (! is_callable($callback)) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterInsert'] = $callback;

        return $this;
    }

    function onDeleteCascade ($options)
    {
        $this->cascadeDelete[] = $options;
        return $this;

    }


    /**
     *
     * @param Zend_Db_Table_Abstract $model
     */
    public function setModel ($model)
    {
        $this->_model = $model;

        $final = array();

        $info = $model->info();
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

                    $form['elements'][$column] = array('select', array('decorators' => $this->elementDecorators, 'multiOptions' => $final['values'][$column], 'label' => $label));

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

                $form['elements'][$column] = array('select', array('decorators' => $this->elementDecorators, 'multiOptions' => $options, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'label' => $label));

                continue;
            }

            if (stripos($detail['DATA_TYPE'], 'set') !== false) {
                preg_match_all('/\'(.*?)\'/', $detail['DATA_TYPE'], $result);

                $options = array();
                foreach ($result[1] as $match) {
                    $options[$match] = ucfirst($match);
                }

                $form['elements'][$column] = array('multiCheckbox', array('decorators' => $this->elementDecorators, 'multiOptions' => $options, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'label' => $label));

                continue;
            }

            switch ($detail['DATA_TYPE']) {

                case 'varchar':
                case 'char':
                    $length = $detail['LENGTH'];
                    $form['elements'][$column] = array('text', array('decorators' => $this->elementDecorators, 'validators' => array(array('stringLength', false, array(0, $length))), 'size' => 40, 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'value' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : "")));
                    break;
                case 'date':
                    $form['elements'][$column] = array('text', array('decorators' => $this->elementDecorators, 'validators' => array(array('Date')), 'size' => 10, 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'value' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : "")));
                    break;
                case 'datetime':
                    $form['elements'][$column] = array('text', array('decorators' => $this->elementDecorators, 'validators' => array(array(new Zend_Validate_Date('Y-m-d H:i:s'))), 'size' => 19, 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'value' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : "")));
                    break;

                case 'text':
                case 'mediumtext':
                case 'longtext':
                case 'smalltext':
                    $form['elements'][$column] = array('textarea', array('decorators' => $this->elementDecorators, 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'filters' => array('StripTags')));
                    break;

                case 'int':
                case 'bigint':
                case 'mediumint':
                case 'smallint':
                case 'tinyint':
                    $defaultIsZero = (! is_null($detail['DEFAULT']) && $detail['DEFAULT'] == "0") ? true : false;
                    $form['elements'][$column] = array('text', array('decorators' => $this->elementDecorators, 'validators' => array('Digits'), 'label' => $label, 'size' => 10, 'required' => ($defaultIsZero == false && $detail['NULLABLE'] == 1) ? false : true, 'value' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : "")));
                    break;

                case 'float':
                case 'decimal':
                case 'double':
                    $form['elements'][$column] = array('text', array('decorators' => $this->elementDecorators, 'validators' => array('Float'), 'size' => 10, 'label' => $label, 'required' => ($detail['NULLABLE'] == 1) ? false : true, 'value' => (! is_null($detail['DEFAULT']) ? $detail['DEFAULT'] : "")));
                    break;

                default:
                    break;
            }
        }

        $this->setDecorators(array('FormElements', array('HtmlTag', array('tag' => 'table', 'style' => 'width:98%')), 'Form'));


        $this->setOptions($form);


        return $this;
    }

    /**
     * @var Zend_From
     */
    function getModel ()
    {
        return $this->_model;
    }

    function addColumns ()
    {
        $columns = func_get_args();
        $final = array();
        if (is_array($columns[0])) {
            $columns = $columns[0];
        }
        foreach ($columns as $value) {
            if ($value instanceof Bvb_Grid_Form_Column) {
                array_push($final, $value);
            }
        }
        $this->fields = $final;
        return $this;
    }

}