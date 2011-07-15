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
    protected $_subformGroupDecorator = array('FormElements',
                                              array('HtmlTag',
                                                    array('tag' => 'td',
                                                          'colspan' => '90',
                                                          'class' => 'buttons')),
                                              array(array('row' => 'HtmlTag'),
                                                    array('tag' => 'tr')));
    /**
     * Decorators for subform elements
     * @var array
     */
    protected $_subformElementDecorator = array('ViewHelper',
                                                'Description',
                                                'Errors',
                                                array(array('data' => 'HtmlTag'),
                                                      array('tag' => 'td', 'class' => 'element')),
                                                array(array('label' => 'Label'),
                                                      array('tag' => 'td', 'class' => 'elementTitle')),
                                                array(array('row' => 'HtmlTag'),
                                                      array('tag' => 'tr')));
    /**
     * Decorators for subform Element Titles
     * @var array
     */
    protected $_subformElementTitle = array(array('Label', array('tag' => 'th')));
    /**
     * Decorators for subform vertical inputs elements
     * @var array
     */
    protected $_subformElementDecoratorVertical = array('ViewHelper',
                                                        'Description',
                                                        'Errors',
                                                        array(array('data' => 'HtmlTag'),
                                                              array('tag' => 'td', 'class' => 'element')));
    /**
     * Decorators for subform file decorators
     * @var array
     */
    protected $_fileDecorator = array('File',
                                      'Description',
                                      'Errors',
                                      array(array('data' => 'HtmlTag'),
                                            array('tag' => 'td', 'class' => 'element')),
                                      array(array('label' => 'Label'),
                                            array('tag' => 'td')),
                                      array(array('row' => 'HtmlTag'),
                                            array('tag' => 'tr')));
    /**
     * Decorators for subform hidden elements
     * @var array
     */
    protected $_buttonHiddenDecorator = array('ViewHelper');
    /**
     * Decorators for form
     * @var array
     */
    protected $_formDecorator = array('FormElements','Form');
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
    protected $_subFormDecorator = array('FormElements',
                                         array('HtmlTag',
                                               array('tag' => 'table',
                                                     'class' => 'borders subForm')));
    /**
     * Decorators for vertical subform
     * @var array
     */
    protected $_subFormDecoratorVertical = array('FormElements',
                                                 array('HtmlTag',
                                                       array('tag' => 'tr')));
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
     * Text to be showed on top of add/edit form
     *
     * @var mixed
     */
    protected $_formTitle = false;
    /**
     * Add button label
     *
     * @var string
     */
    protected $_addButtonLabel = 'Add Record';

    /**
     * If we should load only form elements that where defined
     * in user supplied form
     *
     * @var bool
     */
    protected $_usePreDefinedFormElements = false;

    /**
     * Instantiates a new form, using Zend_Form
     * by default or any provided by the user
     *
     * @param Zend_Form $formClass   Class to be instantiated. Zend_Form or any implementation
     * @param array     $formOptions Options to be passed to the form
     *
     * @return void
     */
    public function __construct($formClass = 'Zend_Form', $formOptions = array())
    {
        if($formClass instanceof Zend_Form)
        {
            $this->_form = $formClass;
        }else{
            $this->_form = new $formClass($formOptions);
        }
    }

    /**
     * Returns the current form or subform if a number is specified
     *
     * @param int $subForm If set, the subForm number to be returned. (Probably 1)
     *
     * @return Zend_Form
     */
    public function getForm($subForm = null)
    {
        if (!is_null($subForm))
            return $this->_form
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
    public function __call($name, $args)
    {
        if (method_exists($this->getForm(), $name)) {
            return call_user_func_array(array($this->getForm(), $name), $args);
        }

        if (substr(strtolower($name), 0, 3) == 'set') {
            $name = substr($name, 3);
            $name[0] = strtolower($name[0]);

            $this->options[$name] = $args[0];
            return $this;
        }
    }

    /**
     * @param Zend_Form $_form
     */
    public function setForm($_form)
    {
        $this->_form = $_form;
        return $this;
    }

    /**
     * @return the $_inputsType
     */
    public function getInputsType()
    {
        return $this->_inputsType;
    }

    /**
     * @param array $_inputsType
     */
    public function setInputsType($_inputsType)
    {
        $this->_inputsType = $_inputsType;
        return $this;
    }

    /**
     * @return the $options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return the $fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return the $_fieldsBasedOnQuery
     */
    public function getFieldsBasedOnQuery()
    {
        return $this->_fieldsBasedOnQuery;
    }

    /**
     * @param bool $_fieldsBasedOnQuery
     */
    public function setFieldsBasedOnQuery($_fieldsBasedOnQuery)
    {
        $this->_fieldsBasedOnQuery = $_fieldsBasedOnQuery;
        return $this;
    }

    /**
     * @return the $_subformGroupDecorator
     */
    public function getSubformGroupDecorator()
    {
        return $this->_subformGroupDecorator;
    }

    /**
     * @param array $_subformGroupDecorator
     */
    public function setSubformGroupDecorator($_subformGroupDecorator)
    {
        $this->_subformGroupDecorator = $_subformGroupDecorator;
        return $this;
    }

    /**
     * @return the $_subformElementDecorator
     */
    public function getSubformElementDecorator()
    {
        return $this->_subformElementDecorator;
    }

    /**
     * @param array $_subformElementDecorator
     */
    public function setSubformElementDecorator($_subformElementDecorator)
    {
        $this->_subformElementDecorator = $_subformElementDecorator;
        return $this;
    }

    /**
     * @return the $_subformElementTitle
     */
    public function getSubformElementTitle()
    {
        return $this->_subformElementTitle;
    }

    /**
     * @param array $_subformElementTitle
     */
    public function setSubformElementTitle($_subformElementTitle)
    {
        $this->_subformElementTitle = $_subformElementTitle;
        return $this;
    }

    /**
     * @return the $_subformElementDecoratorVertical
     */
    public function getSubformElementDecoratorVertical()
    {
        return $this->_subformElementDecoratorVertical;
    }

    /**
     * @param array $_subformElementDecoratorVertical
     */
    public function setSubformElementDecoratorVertical(
    $_subformElementDecoratorVertical)
    {
        $this->_subformElementDecoratorVertical = $_subformElementDecoratorVertical;
        return $this;
    }

    /**
     * @return the $_fileDecorator
     */
    public function getFileDecorator()
    {
        return $this->_fileDecorator;
    }

    /**
     * @param array $_fileDecorator
     */
    public function setFileDecorator($_fileDecorator)
    {
        $this->_fileDecorator = $_fileDecorator;
        return $this;
    }

    /**
     * @return the $_buttonHiddenDecorator
     */
    public function getButtonHiddenDecorator()
    {
        return $this->_buttonHiddenDecorator;
    }

    /**
     * @param array $_buttonHiddenDecorator
     */
    public function setButtonHiddenDecorator($_buttonHiddenDecorator)
    {
        $this->_buttonHiddenDecorator = $_buttonHiddenDecorator;
        return $this;
    }

    /**
     * @return the $_formDecorator
     */
    public function getFormDecorator()
    {
        return $this->_formDecorator;
    }

    /**
     * @param array $_formDecorator
     */
    public function setFormDecorator($_formDecorator)
    {
        $this->_formDecorator = $_formDecorator;
        return $this;
    }

    /**
     * @return the $_formDecoratorSimple
     */
    public function getFormDecoratorSimple()
    {
        return $this->_formDecoratorSimple;
    }

    /**
     * @param array $_formDecoratorSimple
     */
    public function setFormDecoratorSimple($_formDecoratorSimple)
    {
        $this->_formDecoratorSimple = $_formDecoratorSimple;
        return $this;
    }

    /**
     * @return the $_displayGroupsDecorator
     */
    public function getDisplayGroupsDecorator()
    {
        return $this->_displayGroupsDecorator;
    }

    /**
     * @param array $_displayGroupsDecorator
     */
    public function setDisplayGroupsDecorator($_displayGroupsDecorator)
    {
        $this->_displayGroupsDecorator = $_displayGroupsDecorator;
        return $this;
    }

    /**
     * @return the $_subFormDecorator
     */
    public function getSubFormDecorator()
    {
        return $this->_subFormDecorator;
    }

    /**
     * @param array $_subFormDecorator
     */
    public function setSubFormDecorator($_subFormDecorator)
    {
        $this->_subFormDecorator = $_subFormDecorator;
        return $this;
    }

    /**
     * @return the $_subFormDecoratorVertical
     */
    public function getSubFormDecoratorVertical()
    {
        return $this->_subFormDecoratorVertical;
    }

    /**
     * @param array $_subFormDecoratorVertical
     */
    public function setSubFormDecoratorVertical($_subFormDecoratorVertical)
    {
        $this->_subFormDecoratorVertical = $_subFormDecoratorVertical;
        return $this;
    }

    /**
     * @return the $_bulkAdd
     */
    public function getBulkAdd()
    {
        return $this->_bulkAdd;
    }

    /**
     * @param int $_bulkAdd
     */
    public function setBulkAdd($_bulkAdd)
    {
        $this->_bulkAdd = $_bulkAdd;
        return $this;
    }

    /**
     * @return the $_bulkDelete
     */
    public function getBulkDelete()
    {
        return $this->_bulkDelete;
    }

    /**
     * @param bool $_bulkDelete
     */
    public function setBulkDelete($_bulkDelete)
    {
        $this->_bulkDelete = $_bulkDelete;
        return $this;
    }

    /**
     * @return the $_bulkEdit
     */
    public function getBulkEdit()
    {
        return $this->_bulkEdit;
    }

    /**
     * @param bool $_bulkEdit
     */
    public function setBulkEdit($_bulkEdit)
    {
        $this->_bulkEdit = $_bulkEdit;
        return $this;
    }

    /**
     * @return the $_useVerticalInputs
     */
    public function getUseVerticalInputs()
    {
        return $this->_useVerticalInputs;
    }

    /**
     * @param bool $_useVerticalInputs
     */
    public function setUseVerticalInputs($_useVerticalInputs)
    {
        $this->_useVerticalInputs = $_useVerticalInputs;
        return $this;
    }

    /**
     * @return the $_useCSRF
     */
    public function getUseCSRF()
    {
        return $this->_useCSRF;
    }

    /**
     * @param bool $_useCSRF
     */
    public function setUseCSRF($_useCSRF)
    {
        $this->_useCSRF = $_useCSRF;
        return $this;
    }

    /**
     * @return the $_useDecorators
     */
    public function getUseDecorators()
    {
        return $this->_useDecorators;
    }

    /**
     * @param bool $_useDecorators
     */
    public function setUseDecorators($_useDecorators)
    {
        $this->_useDecorators = $_useDecorators;
        return $this;
    }

    /**
     * @return the $_allowedFields
     */
    public function getAllowedFields()
    {
        return $this->_allowedFields;
    }

    /**
     * @param array $_allowedFields
     */
    public function setAllowedFields($_allowedFields)
    {
        $this->_allowedFields = $_allowedFields;
        return $this;
    }

    /**
     * @return the $_disallowedFields
     */
    public function getDisallowedFields()
    {
        return $this->_disallowedFields;
    }

    /**
     * @param array $_disallowedFields
     */
    public function setDisallowedFields($_disallowedFields)
    {
        $this->_disallowedFields = $_disallowedFields;
        return $this;
    }

    /**
     * Adds one more field to the set not allowed to be managed
     * @param string  $field
     * @return Bvb_Grid_Form
     */
    public function addAllowedField($field)
    {
        $this->_allowedFields[] = $field;
        return $this;
    }

    /**
     * Adds another set of fields not allowed to be managed
     * @param array $fields
     * @return Bvb_Grid_Form
     */
    public function addAllowedFields(array $fields)
    {
        $this->_allowedFields = array_merge($fields, $this->_allowedFields);
        return $this;
    }

    /**
     * Adds one more field to the set not allowed to be managed
     * @param string  $field
     * @return Bvb_Grid_Form
     */
    public function addDisallowedField($field)
    {
        $this->_disallowedFields[] = $field;
        return $this;
    }

    /**
     * Adds another set of fields not allowed to be managed
     * @param array $fields
     * @return Bvb_Grid_Form
     */
    public function addDisallowedFields(array $fields)
    {
        $this->_disallowedFields = array_merge($fields, $this->_disallowedFields);
        return $this;
    }

    /**
     * @return the $_onAddForce
     */
    public function getOnAddForce()
    {
        return $this->_onAddForce;
    }

    /**
     * @param array $_onAddForce
     */
    public function setOnAddForce($_onAddForce)
    {
        $this->_onAddForce = $_onAddForce;
        return $this;
    }

    /**
     * @return the $_onEditForce
     */
    public function getOnEditForce()
    {
        return $this->_onEditForce;
    }

    /**
     * @param array $_onEditForce
     */
    public function setOnEditForce($_onEditForce)
    {
        $this->_onEditForce = $_onEditForce;
        return $this;
    }

    /**
     * @return the $_onEditAddCondition
     */
    public function getOnEditAddCondition()
    {
        return $this->_onEditAddCondition;
    }

    /**
     * @param array $_onEditAddCondition
     */
    public function setOnEditAddCondition($_onEditAddCondition)
    {
        $this->_onEditAddCondition = $_onEditAddCondition;
        return $this;
    }

    /**
     * @return the $_onDeleteAddCondition
     */
    public function getOnDeleteAddCondition()
    {
        return $this->_onDeleteAddCondition;
    }

    /**
     * @param array $_onDeleteAddCondition
     */
    public function setOnDeleteAddCondition($_onDeleteAddCondition)
    {
        $this->_onDeleteAddCondition = $_onDeleteAddCondition;
        return $this;
    }

    /**
     * @return the $_deleteColumn
     */
    public function getDeleteColumn()
    {
        return $this->_deleteColumn;
    }

    /**
     * @param bool $_deleteColumn
     */
    public function setDeleteColumn($_deleteColumn)
    {
        $this->_deleteColumn = $_deleteColumn;
        return $this;
    }

    /**
     * @return the $_editColumn
     */
    public function getEditColumn()
    {
        return $this->_editColumn;
    }

    /**
     * @param bool $_editColumn
     */
    public function setEditColumn($_editColumn)
    {
        $this->_editColumn = $_editColumn;
        return $this;
    }

    /**
     * If we should use a table thead with some text to describe form action
     *
     * @param string $label
     * @return Bvb_Grid_Form
     */
    public function setFormTitle($label)
    {
        $this->_formTitle = $label;
        return $this;
    }

    /**
     * If we should use a for header to describe form action
     *
     * @return mixed
     */
    public function getFormTitle()
    {
        return $this->_formTitle;
    }

    /**
     * returns current label for add button
     *
     * @return string
     */
    public function getAddButtonLabel()
    {
        return $this->_addButtonLabel;
    }

    /**
     * Defines a new label for the add record button
     * @param string $label
     * @return Bvb_Grid_Form
     */
    public function setAddButtonLabel($label)
    {
        $this->_addButtonLabel = (string) $label;
        return $this;
    }

    /**
     * Defined if we should display all form elements or only the ones
     * definef in user supplied form
     *
     * @param bool $use
     * @return Bvb_Grid_Form
     */
    public function setUsePreDefinedFormElements($value)
    {
        $this->_usePreDefinedFormElements = (bool) $value;
        return $this;
    }

    /**
     * Returns current option if we should display all form elements
     * or only the ones defined in user supplied form
     *
     * @return bool
     */
    public function getUsePreDefinedFormElements()
    {
        return $this->_usePreDefinedFormElements;
    }

}