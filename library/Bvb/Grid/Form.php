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

class Bvb_Grid_Form
{

    /**
     *
     * @var Zend_Form
     */
    protected $_form;

    /**
     * input types (text, select, etc, etc) for every form input
     *
     * @var array
     */
    protected $_inputsType = array();

    /**
     * Form options
     *
     * @var array
     */
    public $options;

    /**
     * Form fields
     * @var array
     */
    public $fields;

    /**
     * If the form to be showed is built with fields from query
     * @var bool
     */
    protected $_fieldsBasedOnQuery = false;

    /**
     * Decorators for subform Groups
     * @var array
     */
    protected $_subformGroupDecorator = array('FormElements', array('HtmlTag', array('tag' => 'td', 'colspan' => '90', 'class' => 'buttons')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    /**
     * Decorators for subform elements
     * @var array
     */
    protected $_subformElementDecorator = array('ViewHelper', 'Description', 'Errors', array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')), array(array('label' => 'Label'), array('tag' => 'td', 'class' => 'elementTitle')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    /**
     * Decorators for subform Element Titles
     * @var array
     */
    protected $_subformElementTitle = array(array('Label', array('tag' => 'th')));

    /**
     * Decorators for subform vertical inputs elements
     * @var array
     */
    protected $_subformElementDecoratorVertical = array('ViewHelper', 'Description', 'Errors', array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')));

    /**
     * Decorators for subform file decorators
     * @var array
     */
    protected $_fileDecorator = array('File', 'Description', 'Errors', array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')), array(array('label' => 'Label'), array('tag' => 'td')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    /**
     * Decorators for subform hidden elements
     * @var array
     */
    protected $_buttonHiddenDecorator = array('ViewHelper');

    /**
     * Decorators for form
     * @var array
     */
    protected $_formDecorator = array('FormElements', array('HtmlTag', array('tag' => 'table', 'class' => 'formTable')), 'Form');

    /**
     * Decorators for Form
     * @var array
     */
    protected $_formDecoratorSimple = array('FormElements', 'Form');

    /**
     * Decorators for Groups
     * @var array
     */
    protected $_displayGroupsDecorator = array();

    /**
     * Decorators for subform
     * @var array
     */
    protected $_subFormDecorator = array('FormElements', array('HtmlTag', array('tag' => 'table', 'class' => 'borders subForm')));

    /**
     * Decorators for vertical subform
     * @var array
     */
    protected $_subFormDecoratorVertical = array('FormElements', array('HtmlTag', array('tag' => 'tr')));

    /**
     * Number of subform to show when performing bulk aditions
     * @var int
     */
    protected $_bulkAdd = 1;

    /**
     * Allow bulk Deletion
     * @var bool
     */
    protected $_bulkDelete = false;

    /**
     * Allow bulk Edition
     * @var bool
     */
    protected $_bulkEdit = false;

    /**
     * use vertical inputs, instead horizontal
     * @var bool
     */
    protected $_useVerticalInputs = true;

    /**
     * Use CSRF element to prevent atacks
     * @var bool
     */
    protected $_useCSRF = true;

    /**
     * Use decorators. If false it will assume you have your own in your form class
     * @var bool
     */
    protected $_useDecorators = true;

    /**
     * Fields allowed to be processd
     * @var array
     */
    protected $_allowedFields = array();

    /**
     * Fields not allowed to be processed
     * @var array
     */
    protected $_disallowedFields = array();

    /**
     * When adding force some input to have the value specified
     * @var array
     */
    protected $_onAddForce = array();

    /**
     * When editing force some inputs to have the value specified
     * @var array
     */
    protected $_onEditForce = array();

    /**
     * When editing, add extra condition to the existing ones
     * @var array
     */
    protected $_onEditAddCondition = array();

    /**
     * When deleting records, add extra condition to the existing ones
     * @var array
     */
    protected $_onDeleteAddCondition = array();

    /**
     * If we should show the delete column
     * @var bool
     */
    protected $_deleteColumn = true;

    /**
     * If we should show the edit column
     * @var bool
     */
    protected $_editColumn = true;


    /**
     * Instantiates a new form, using Zend_Form
     * by default or any provided by the user
     *
     * @param Zend_Form $formClass   Class to be instantiated. Zend_Form or any implementation
     * @param array     $formOptions Options to be passed to the form
     *
     * @return void
     */
    public function __construct ($formClass = 'Zend_Form', $formOptions = array())
    {
        $this->_form = new $formClass($formOptions);
    }


    /**
     * Returns the current form or subform if a number is specified
     *
     * @param int $subForm If set, the subForm number to be returned. (Probably 1)
     *
     * @return Zend_Form
     */
    public function getForm ($subForm = null)
    {
        if ( ! is_null($subForm) ) return $this->_form
            ->getSubForm($subForm);

        return $this->_form;
    }


    /**
     * sets the protected properties of this class, of the method does not exists
     * in the instantiated class (Zend_Form by default)
     *
     * @param string $name The method name or class proporty
     * @param mixed  $args Arguments to be passed
     *
     * @return Bvb_Grid_Form
     */
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


    /**
     * Defines the callback to be called before deleting a record
     *
     * @param mixed $callback A valid callback
     *
     * @return Bvb_Grid_Form
     */
    public function setCallbackBeforeDelete ($callback)
    {
        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }
        $this->options['callbackBeforeDelete'] = $callback;

        return $this;
    }


    /**
     * Defines the callback to be called before updating a record
     *
     * @param mixed $callback A valid callback
     *
     * @return Bvb_Grid_Form
     */
    public function setCallbackBeforeUpdate ($callback)
    {
        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackBeforeUpdate'] = $callback;

        return $this;
    }


    /**
     * Defines the callback to be called before inserting a record
     *
     * @param mixed $callback A valid callback
     *
     * @return Bvb_Grid_Form
     */
    public function setCallbackBeforeInsert ($callback)
    {
        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackBeforeInsert'] = $callback;

        return $this;
    }


    /**
     * Defines the callback to be called after deleting a record
     *
     * @param mixed $callback A valid callback
     *
     * @return Bvb_Grid_Form
     */
    public function setCallbackAfterDelete ($callback)
    {
        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterDelete'] = $callback;

        return $this;
    }


    /**
     * Defines the callback to be called after updating a record
     *
     * @param mixed $callback A valid callback
     *
     * @return Bvb_Grid_Form
     */
    public function setCallbackAfterUpdate ($callback)
    {
        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterUpdate'] = $callback;

        return $this;
    }


    /**
     * Defines the callback to be called after inserting a record
     *
     * @param mixed $callback A valid callback
     *
     * @return Bvb_Grid_Form
     */
    public function setCallbackAfterInsert ($callback)
    {
        if ( ! is_callable($callback) ) {
            throw new Exception($callback . ' not callable');
        }

        $this->options['callbackAfterInsert'] = $callback;

        return $this;
    }
}