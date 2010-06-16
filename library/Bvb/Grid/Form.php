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

class Bvb_Grid_Form
{

    protected $_form;

    protected $_inputsType = array();

    public $options;

    public $fields;

    protected $_fieldsBasedOnQuery = false;


    protected $_groupDecorator = array('FormElements', array('HtmlTag', array('tag' => 'td', 'colspan' => '2', 'class' => 'buttons')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    protected $_subformGroupDecorator = array('FormElements', array('HtmlTag', array('tag' => 'td', 'colspan' => '90', 'class' => 'buttons')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    protected $_elementDecorator = array('ViewHelper', 'Description', 'Errors', array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')), array(array('label' => 'Label'), array('tag' => 'td')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    protected $_subformElementDecorator = array('ViewHelper', 'Description', 'Errors', array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')), array(array('label' => 'Label'), array('tag' => 'td', 'class' => 'elementTitle')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    protected $_subformElementTitle = array(array('Label', array('tag' => 'th')));

    protected $_subformElementDecoratorVertical = array('ViewHelper', 'Description', 'Errors', array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')));

    protected $_fileDecorator = array('File', 'Description', 'Errors', array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')), array(array('label' => 'Label'), array('tag' => 'td')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    protected $_buttonHiddenDecorator = array('ViewHelper');

    protected $_formDecorator = array('FormElements', array('HtmlTag', array('tag' => 'table', 'style' => 'width:99%')), 'Form');

    protected $_subFormDecorator = array('FormElements', array('HtmlTag', array('tag' => 'table', 'style' => 'margin-bottom:5px; width:100%', 'class' => 'borders')));

    protected $_subFormDecoratorVertical = array('FormElements', array('HtmlTag', array('tag' => 'tr')));

    protected $_bulkAdd = 1;

    protected $_bulkDelete = false;

    protected $_bulkEdit = false;

    protected $_useVerticalInputs = true;


    protected $_allowedFields = array();

    protected $_disallowedFields = array();

    protected $_onAddForce = array();

    protected $_onEditForce = array();

    protected $_onEditAddCondition = array();

    protected $_onDeleteAddCondition = array();


    public function getForm ($subForm = null)
    {
        if ( ! is_null($subForm) ) return $this->_form->getSubForm($subForm);

        return $this->_form;
    }


    public function __call ($name, $args)
    {
        if ( method_exists($this->getForm(), $name) ) {
            return call_user_func_array(array($this->getForm(), $name), $args);
        }

        if ( substr(strtolower($name), 0, 3) == 'set' ) {
            $name = substr($name, 3);
            $name[0] = strtolower($name[0]);

            $decorator = '_' . $name;
            if ( isset($this->$decorator) && gettype($this->$decorator) == gettype($args[0]) ) {
                $this->$decorator = $args[0];
                return $this;
            }

            $this->options[$name] = $args[0];
            return $this;
        }

        if ( substr(strtolower($name), 0, 3) == 'get' ) {
            $name = substr($name, 3);
            $name[0] = strtolower($name[0]);

            $decorator = '_' . $name;

            if ( isset($this->$decorator) ) {
                return $this->$decorator;
            }

            return $this;
        }

    }


    public function __construct ($formClass = 'Zend_Form', $formOptions = array())
    {
        $this->_form = new $formClass($formOptions);
    }


    public function setCallbackBeforeDelete ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }
        $this->options['callbackBeforeDelete'] = $callback;

        return $this;
    }


    public function setCallbackBeforeUpdate ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackBeforeUpdate'] = $callback;

        return $this;
    }


    public function setCallbackBeforeInsert ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackBeforeInsert'] = $callback;

        return $this;
    }


    public function setCallbackAfterDelete ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterDelete'] = $callback;

        return $this;
    }


    public function setCallbackAfterUpdate ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterUpdate'] = $callback;

        return $this;
    }


    public function setCallbackAfterInsert ($callback)
    {

        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterInsert'] = $callback;

        return $this;
    }

}