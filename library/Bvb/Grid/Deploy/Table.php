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
 * @category  Table
 * @package   Bvb_Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id$
 * @link      http://zfdatagrid.com
 */
class Bvb_Grid_Deploy_Table extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface {

    /**
     * @var mixed
     */
    protected $_deployOptions = null;
    /**
     * Information about the template
     *
     * @var array|empty
     */
    protected $_templateInfo;
    /**
     * Deploy options
     *
     * @var string
     */
    protected $options = array();
    /**
     * The output type
     *
     * @var string
     */
    protected $output = 'table';
    /**
     * Images url for export
     *
     * @var string
     */
    protected $_imagesUrl;
    /**
     * Contains result of deploy() function.
     *
     * @var string
     */
    protected $_deploymentContent = null;
    /**
     * String containg the inputs ids for fitlers
     *
     * @var array
     */
    protected $_javaScriptHelper = array('js' => '', 'url' => '');
    /**
     *
     * @var Zend_Form
     */
    protected $_form;
    /**
     *
     * @var Bvb_Grid_Form
     */
    protected $_bvbForm;
    /**
     * The table where crud operations
     * should be performed.
     * by default the table is fetched from the quaery
     * but the user can set other manually
     *
     * @var mixed
     */
    protected $_crudTable;
    /**
     * Options for CRUD operations
     *
     * @var array
     */
    protected $_crudOptions = array();
    /**
     * If data should be saved or not into the source
     *
     * @var array
     */
    protected $_crudTableOptions = array('add' => 1, 'edit' => 1, 'delete' => 1);
    /**
     *
     * @var Zend_Session_Abstract
     */
    protected $_gridSession = null;
    /**
     * Whether to use or not key events for filters
     *
     * @var bool
     */
    protected $_useKeyEventsOnFilters = false;
    /**
     * An array with all the parts that can be rendered
     * even
     *
     * @var array
     */
    protected $_render = array();
    /**
     * An array with all parts that will be rendered
     *
     * @var array
     */
    protected $_renderDeploy = array();
    /**
     * Definitions from form
     * May contain data being edited, what operation is beiing performed
     *
     * @var array
     */
    protected $_formSettings = array();
    /**
     * If the user should be redirected to a confirmation page
     * before a record being deleted or if there should be a popup
     *
     * @var bool
     */
    protected $_deleteConfirmationPage = false;
    /**
     * Shows allways all arrows in all fields
     * or only when a fiel is sorted
     *
     * @var bool
     */
    protected $_alwaysShowOrderArrows = true;
    /**
     * If we should show order images when sorting results
     *
     * @var $_showOrderImages string
     */
    protected $_showOrderImages = true;
    /**
     * Wheter to show or not the detail column
     *
     * @var bool
     */
    protected $_showDetailColumn = true;
    /**
     * The record to place the grid on loading
     *
     * @var array
     */
    protected $_recordPage = array();
    /**
     * Table title when viewing record details.
     *
     * @var string
     */
    protected $_detailViewTitle = 'Record Details';
    /**
     * Return label when viewing when in detail view
     *
     * @var string
     */
    protected $_detailViewReturnLabel = 'Return';
    /**
     * Columns positions
     *
     * @var left|right
     */
    protected $_crudColumnsPosition = 'left';

    /**
     * Class construct
     *
     * @param array $options Config options to apply
     */
    public function __construct (array $options = array())
    {
        $this->_setRemoveHiddenFields(true);

        parent::__construct($options);

        if (isset($this->_options['grid']['id'])) {
            $this->setGridId($this->_options['grid']['id']);
        }

        $this->_gridSession = new Zend_Session_Namespace('Bvb_Grid_' . $this->getGridId());
        $this->addTemplateDir('Bvb/Grid/Template', 'Bvb_Grid_Template', 'table');

        if ($this->getParam('add') || $this->getParam('edit')) {

            if ($this->getParam('add')) {
                $this->_willShow[] = 'form';
                $this->_willShow[] = 'formAdd';
            }
            if ($this->getParam('edit')) {
                $this->_willShow[] = 'form';
                $this->_willShow[] = 'formEdit';
            }
        } else {
            if ($this->getParam('detail') ||  $this->getParam('delete') ){
                $this->_willShow[] = 'detail';
            } else {
                $this->_willShow[] = 'listing';
            }
        }
    }

    /**
     * Buils form values
     *
     * @return void
     */
    protected function _buildFormValues()
    {

        if (!isset($this->_data['schema'])) {
            $this->_data['schema'] = '';
        }

        if ($this->_allowAdd == 1 || $this->_allowEdit == 1) {

            $mode = $this->getParam('edit') ? 'edit' : 'add';

            $queryUrl = $this->getIdentifierColumnsFromUrl();

            if (!$this->getRequest()->isPost()
                || ($this->getParam('zfmassedit')
                && $this->getRequest()->isPost())
            ) {

                foreach ($this->_form->getSubForms() as $key => $form) {
                    foreach (array_keys($form->getElements()) as $element) {
                        if ($this->_gridSession->noErrors !== true) {
                            if (isset($this->_gridSession->errors[$key][$element])) {
                                $form->getElement($element)->setErrors($this->_gridSession->errors[$key][$element]);
                            }
                        }
                        if (isset($this->_gridSession->post[$key][$element])) {
                            $form->getElement($element)->setValue($this->_gridSession->post[$key][$element]);
                        }
                    }
                }

                if ($mode == 'edit') {

                    $conditions = array();
                    if ($this->getParam('postMassIds')) {
                        $ids = explode(',', $this->getParam('postMassIds'));
                        $pkParentArray = $this->getSource()
                            ->getIdentifierColumns($this->_data['table'], $this->_data['schema']);

                        $a = 1;
                        foreach ($ids as $value) {
                            if (strpos($value, '-')) {
                                $allIds = explode('-', $value);
                                $i = 0;
                                foreach ($allIds as $fIds) {
                                    $conditions[$a][$pkParentArray[$i]] = $fIds;
                                    $i++;
                                    $a++;
                                }
                            } else {
                                $conditions[$a][$pkParentArray[0]] = $value;
                                $a++;
                            }
                        }
                    } else {
                        $conditions[1] = $this->getIdentifierColumnsFromUrl();
                    }

                    for ($i = 1; $i <= count($conditions); $i++) {
                        $r = $this->getSource()->getRecord($this->_crudTable, $conditions[$i]);

                        if (($r === false || (is_array($r) && count($r)==0) ) && count($conditions) == 1) {
                            $this->_gridSession->message = $this->__('Record Not Found');
                            $this->_gridSession->_noForm = 1;
                            $this->_gridSession->correct = 1;
                            $this->_redirect($this->getUrl(array('comm', 'delete', 'detail', 'edit')));
                        }

                        if (is_array($r)) {
                            foreach ($r as $key => $value) {
                                $pk = explode('.', key($conditions[$i]));
                                if ($key == end($pk)) {
                                    $this->getForm($i)->getElement('ZFPK')->setValue(implode('-',$conditions[$i]));
                                }

                                $isField = $this->getForm($i)->getElement($key);

                                if (isset($isField)) {

                                    if (isset($this->_data['fields'][$key])) {
                                        $fieldType = $this->getSource()
                                            ->getFieldType($this->_data['fields'][$key]['field']);
                                    } else {
                                        $fieldType = 'text';
                                    }

                                    if (isset($this->_gridSession->post) && is_array($this->_gridSession->post)) {
                                        if (isset($this->_gridSession->post[$i][$key])) {
                                            $this->getForm($i)
                                                ->getElement($key)
                                                ->setValue($this->_gridSession->post[$i][$key]);
                                        }
                                    } else {
                                            $this->getForm($i)->getElement($key)->setValue($value);
                                    }
                                }
                            }
                        }
                    }
                }

                $this->emitEvent('crud.set_values', array('form' => $this->getForm()));
            }
        }
    }

    /**
     * Process all information forms related
     * First we check for permissions to add, edit, delete
     * And then the request->isPost. If true we process the data
     *
     * @return Bvb_Grid_Deploy_Table
     */
    protected function _processForm()
    {
        if (!$this->getSource()->hasCrud()) {
            return false;
        }
        if ($this->getInfo("add,allow") == 1) {
            $this->_allowAdd = 1;
        }

        if ($this->getInfo("delete,allow") == 1) {
            $this->_allowDelete = 1;
        }

        if ($this->getInfo("edit,allow") == 1) {
            $this->_allowEdit = 1;
        }

        /**
         * Remove if there is something to remove
         */
        if (($this->_allowDelete == 1 && ( $this->getParam('delete') ||  $this->getParam('zfmassremove')))
            && !$this->getParam('detail')) {
            self::_deleteRecord();
        }

        $mode = $this->getParam('edit') ? 'edit' : 'add';


        //Check if the request method is POST
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('zfg_form_edit' . $this->getGridId()) == 1) {

            foreach ($this->_form->getSubForms() as $key => $form) {
                if (isset($_POST[$key]['ZFIGNORE']) && $_POST[$key]['ZFIGNORE'] == 1) {
                    $this->_form->removeSubForm($key);
                }
            }

            if (count($this->_form->getSubForms()) == 0) {
                $this->_redirect($this->getUrl(array('zfg_csrf', 'add', 'zfg_form_edit', 'form_submit')));
            }

            if ($this->_form->isValid($_POST)) {
                $post = array();

                foreach ($this->_form->getSubForms() as $key => $value) {
                    foreach ($value->getElements() as $el) {
                        if($el->getIgnore())
                                continue;

                        $fieldValue = $el->getValue();

                        $post[$key][$el->getName()] = is_array($fieldValue) ? implode(',', $fieldValue) :$fieldValue;
                    }

                    unset($post[$key]['ZFIGNORE']);
                }

                $addNew = false;

                if (isset($_POST['saveAndAdd' . $this->getGridId()])) {
                    $this->_gridSession->noErrors = true;
                    $addNew = true;
                }

                unset($post['form_submit' . $this->getGridId()]);
                unset($post['zfg_form_edit' . $this->getGridId()]);
                unset($post['form_reset' . $this->getGridId()]);
                unset($post['zfg_csrf' . $this->getGridId()]);
                unset($post['saveAndAdd' . $this->getGridId()]);

                // Process data
                if ($mode == 'add') {

                    $this->getSource()->beginTransaction();
                    try {
                        foreach ($this->_form->getSubForms() as $key => $value) {
                            if ($this->_crud->getUseVerticalInputs() === false && $key == 0) {
                                continue;
                            }


                            //Let's see if the field is nullable and empty
                            //If so, we need to remove it from the array
                            $tableFields = $this->getSource()->getDescribeTable($this->_crudTable);

                            foreach (array_keys($post[$key]) as $field) {

                                if (!isset($tableFields[$field]))
                                    continue;

                                if ($tableFields[$field]['NULLABLE'] == 1 && strlen($post[$key][$field]) == 0) {
                                    $post[$key][$field] = new Zend_Db_Expr("NULL");
                                }
                            }

                            if (count($post[$key]) == 0) {
                                throw new Bvb_Grid_Exception($this->__('No values to insert'));
                            }


                            $post[$key] = array_merge($post[$key], $this->_crudOptions['addForce']);

                            $this->emitEvent('crud.before_insert', array('table' => &$this->_crudTable,
                                'connectionId' => $this->getSource()->getConnectionId(),
                                                  'values' => &$post[$key]));

                            if ($this->_crudTableOptions['add'] == true) {
                                $insertId = $this->getSource()->insert($this->_crudTable, $post[$key]);
                            }else{
                                $insertId = '';
                            }


                            $this->emitEvent('crud.after_insert', array('table' => &$this->_crudTable,
                                'connectionId' => $this->getSource()->getConnectionId(),
                                                  'values' => &$post[$key],
                                                  'insertId' => $insertId));

                            unset($this->_gridSession->post[$key]);
                        }

                        $this->getSource()->commit();

                        $this->_gridSession->message = $this->__('Record saved');
                        $this->_gridSession->messageOk = true;

                        if (isset($post['saveAndAdd' . $this->getGridId()])) {
                            $this->_gridSession->_noForm = 0;
                        } else {
                            $this->_gridSession->_noForm = 1;
                        }

                        $this->_gridSession->correct = 1;

                        $this->_removeFormParams(array('add' . $this->getGridId() => '1'));

                        if ($addNew === true) {
                            $finalUrl = '/add/1';
                        } else {
                            $finalUrl = '';
                        }

                        $this->_redirect($this->getUrl() . $finalUrl);
                    } catch (Exception $e) {
                        $this->_gridSession->messageOk = false;
                        $this->_gridSession->message = $this->__('Error saving record: ') . $e->getMessage();
                        $this->_gridSession->formSuccess = 0;
                        $this->_gridSession->formPost = 1;
                        $this->_gridSession->_noForm = 0;
                        $this->_gridSession->correct = 0;

                        $this->getSource()->rollBack();
                        $this->_removeFormParams();
                        $this->_redirect($this->getUrl());
                    }
                }

                // Process data
                if ($mode == 'edit') {
                    try {

                        $this->getSource()->beginTransaction();
                        foreach ($this->_form->getSubForms() as $key => $value) {
                            if ($this->_crud->getUseVerticalInputs() === false && $key == 0) {
                                continue;
                            }

                            $pks = $this->getSource()
                                ->getIdentifierColumns($this->_data['table'], $this->_data['schema']);

                            if (isset($post[$key]['ZFPK'])) {
                                if (strpos($post[$key]['ZFPK'], '-')) {
                                    $allIds = explode('-', $post[$key]['ZFPK']);
                                    $i = 0;
                                    foreach ($allIds as $fIds) {
                                        $condition[$pks[$i]] = $fIds;
                                        $i++;
                                    }
                                } else {
                                    $condition[$pks[0]] = $post[$key]['ZFPK'];
                                }

                                $queryUrl = $condition;

                                unset($post[$key]['ZFPK']);
                            }

                            $post[$key] = array_merge($post[$key], $this->_crudOptions['editForce']);
                            $queryUrl = array_merge($queryUrl, $this->_crudOptions['editAddCondition']);


                            $oldValues = $this->getSource()->getRecord($this->_crudTable, $condition);

                            $this->emitEvent('crud.before_update', array('table' => &$this->_crudTable,
                                'connectionId' => $this->getSource()->getConnectionId(),
                                                'newValues' => &$post[$key],
                                                'oldValues' => &$oldValues,
                                                'condition' => &$condition));

                            if (count($post[$key]) == 0) {
                                throw new Bvb_Grid_Exception($this->__('No values to update'));
                            }

                            if ($this->_crudTableOptions['edit'] == true) {
                                $this->getSource()->update($this->_crudTable, $post[$key], $queryUrl);
                            }


                            $newValues = $this->getSource()->getRecord($this->_crudTable, $condition);

                            $this->emitEvent('crud.after_update', array('table' => &$this->_crudTable,
                                'connectionId' => $this->getSource()->getConnectionId(),
                                                'newValues' => &$newValues,
                                                'oldValues' => &$oldValues,
                                                'condition' => &$condition));
                        }

                        $this->getSource()->commit();

                        $this->_gridSession->message = $this->__('Record saved');
                        $this->_gridSession->messageOk = true;
                        $this->_gridSession->_noForm = 1;
                        $this->_gridSession->correct = 1;

                        unset($this->_gridSession->post);

                        $this->_removeFormParams(
                            array('edit' . $this->getGridId() => '', '
                                  zfmassedit' => '')
                        );

                        $this->_redirect($this->getUrl());
                    } catch (Exception $e) {
                        $this->_gridSession->messageOk = false;
                        $this->_gridSession->message = $this->__('Error updating record: ') . $e->getMessage();
                        $this->_gridSession->formSuccess = 0;
                        $this->_gridSession->formPost = 1;
                        $this->_gridSession->_noForm = 0;
                        $this->_gridSession->correct = 0;

                        $this->getSource()->rollBack();

                        $this->_removeFormParams();
                        $this->_redirect($this->getUrl());
                    }
                }
            } else {
                $this->_gridSession->post = $_POST;

                foreach ($this->_form->getSubForms() as $key => $value) {
                    $this->_gridSession->errors[$key] = $value->getMessages();
                }

                $this->_gridSession->message = $this->__('Validation failed');
                $this->_gridSession->messageOk = false;
                $this->_gridSession->formSuccess = 0;
                $this->_gridSession->formPost = 1;
                $this->_gridSession->_noForm = 0;
                $this->_gridSession->correct = 0;
                $this->_removeFormParams();
            }
        }
    }

    /**
     * Remove unneeded form inputs
     *
     * @param array $extra Array of extra params to remove
     *
     * @return bool
     */
    protected function _removeFormParams($extra = array())
    {

        $post = (array) array_flip(array_keys($this->_form->getSubForms()));

        $extra = array_merge(
            $extra,
            array('massActionsAll_'.$this->getGridId() => '',
                  'postMassIds'.$this->getGridId() => '',
                  'send_'.$this->getGridId() => '',
                  'gridAction_'.$this->getGridId() => '')
        );

        if (count($extra) > 0) {
            foreach ($extra as $key => $value) {
                $this->clearParam($key);
            }
        }

        if ($this->getRequest()->isPost()) {
            foreach ($_POST as $key => $value) {
                $this->clearParam($key);
            }
        }

        foreach ($post as $key => $value) {
            $this->clearParam($key);
        }

        $this->clearParam('saveAndAdd' . $this->getGridId());
        $this->clearParam('form_submit' . $this->getGridId());
        $this->clearParam('zfg_form_edit' . $this->getGridId());
        $this->clearParam('zfg_csrf' . $this->getGridId());

        return true;
    }

    /**
     * Remove the record from the table
     *
     * @param string $sql Params to delete record
     *
     * @return string
     *
     */
    protected function _deleteRecord()
    {
        if ($this->getParam('postMassIds') && $this->getParam('zfmassremove') == 1) {
            //ID's to remove
            $ids = explode(',', $this->getParam('postMassIds'));

            //Lets get PK'/**
            $pkParentArray = $this->getSource()->getIdentifierColumns($this->_data['table']);
            foreach ($ids as $value) {

                $this->getSource()->beginTransaction();

                $condition = array();

                if (strpos($value, '-')) {
                    $allIds = explode('-', $value);
                    $i = 0;
                    foreach ($allIds as $fIds) {
                        $condition[$pkParentArray[$i]] = $fIds;
                        $i++;
                    }
                } else {
                    $condition[$pkParentArray[0]] = $value;
                }

                try {

                        $condition = array_merge($condition, $this->_crudOptions['deleteAddCondition']);

                        $values = $this->getSource()->getRecord($this->_crudTable, $condition);

                    $this->emitEvent('crud.before_delete', array('table' => &$this->_crudTable,
                        'connectionId' => $this->getSource()->getConnectionId(),
                                              'condition' => &$condition,
                                              'values' => &$values));

                        if ($this->_crudTableOptions['delete'] == true) {
                            $resultDelete = $this->getSource()->delete($this->_crudTable, $condition);
                        }

                    $this->emitEvent('crud.after_delete', array('table' => &$this->_crudTable,
                        'connectionId' => $this->getSource()->getConnectionId(),
                                              'condition' => &$condition,
                                              'values' => &$values));

                    $this->getSource()->commit();

                } catch (Exception $e) {
                    $this->getSource()->rollBack();
                    $this->_gridSession->correct = 1;
                    $this->_gridSession->messageOk = false;
                    $this->_gridSession->message = $this->__('Error deleting record: ') . $e->getMessage();
                }
            }

            $this->_gridSession->messageOk = true;
            $this->_gridSession->message = $this->__('Record deleted');
            $this->_gridSession->correct = 1;

            $this->_removeFormParams($_POST);

            $this->_redirect($this->getUrl(array( 'zfmassremove', 'postMassIds')));
        }

        $condition = $this->getIdentifierColumnsFromUrl();

        if (count($condition) == 0) {
            return false;
        }

        $this->getSource()->beginTransaction();
        try {


            $condition = array_merge($condition, $this->_crudOptions['deleteAddCondition']);
            $values = $this->getSource()->getRecord($this->_crudTable, $condition);

            $this->emitEvent('crud.before_delete', array('table' => &$this->_crudTable,
                'connectionId' => $this->getSource()->getConnectionId(),
                                  'condition' => &$condition,
                                  'values' => &$values));

            if ($this->_crudTableOptions['delete'] == true) {
                $resultDelete = $this->getSource()->delete($this->_crudTable, $condition);
            }

            $this->emitEvent('crud.after_delete', array('table' => &$this->_crudTable,
                'connectionId' => $this->getSource()->getConnectionId(),
                                  'condition' => &$condition,
                                  'values' => &$values));

            $this->_gridSession->messageOk = true;
            $this->_gridSession->message = $this->__('Record deleted');
            $this->_gridSession->correct = 1;

            $this->getSource()->commit();

            $this->_redirect($this->getUrl('delete'));
        } catch (Exception $e) {
            $this->getSource()->rollBack();
            $this->_gridSession->correct = 1;
            $this->_gridSession->messageOk = false;
            $this->_gridSession->message = $this->__('Error deleting record: ') . $e->getMessage();
        }


        return true;
    }

    /**
     * Build the first line of the table (Not the TH)
     *
     * @return string
     */
    protected function _buildHeader()
    {
        $url = array( 'edit', 'filters', 'order');

        $final = '';
        $final1 = '';

        $this->_actionsUrls['add'] = $this->getUrl($url,array('add'=>'record'));

        if ($this->getSource()->hasCrud()) {
            if (($this->getInfo('doubleTables') == 0 && $this->_allowAdd == 1)
                && $this->getSource()->getIdentifierColumns($this->_data['table'])
                && $this->_allowAddButton == 1
                && !$this->getParam('add')
                && !$this->getParam('edit')
            ) {
                $addButton = "<button class='addRecord' onclick=\"window.location='"
                        . $this->_actionsUrls['add'] . "';\">"
                        . $this->__($this->getBvbForm()->getAddButtonLabel()) . "</button>";
            } else {
                $addButton = '';
            }
        } else {
            $addButton = '';
        }

        /**
         * We must check if there is a filter set or an order, to show the extra th on top
         */
        if (count($this->_filters) > 0) {
            $url = $this->getUrl(array('filters', 'noFilters'));
            $url2 = $this->getUrl(array('order', 'noOrder'));
            $url3 = $this->getUrl(array('filters', 'order', 'noFilters', 'noOrder'));

            if ((is_array($this->_defaultFilters) || $this->_paramsInSession === true)
                && !$this->getParam('noFilters')
            ) {
                $url = $this->getUrl(array('filters', 'noFilters'), array('noFilters' => 1));
                $url3 = $this->getUrl(array('filters', 'noFilters'), array('noFilters' => 1));
            }

            if (is_array($this->getSource()->getSelectOrder())) {

                $url3 = $this->getUrl(array('filters', 'noFilters'), array('noOrder' => 1));
                $url3 = $this->getUrl(array('filters', 'noFilters'), array('noOrder' => 1));
            }

            $this->_temp['table']->hasExtraRow = 1;

            //Filters and order
            if ($this->getParam('order') && !$this->getParam('noOrder') && count($this->_filtersValues) > 0) {
                    $final1 = "<button id='remove_filters' onclick=\"window.location='$url'\">"
                        . $this->__('Clear Filters')
                            . "</button><button id='remove_order' onclick=\"window.location='$url2'\">"
                        . $this->__('Clear Order')
                            . "</button><button id='remove_filters_order' onclick=\"window.location='$url3'\">"
                        . $this->__('Clear Filters and Order') . "</button>";
                //Only filters
            } elseif ((!$this->getParam('order') || $this->getParam('noOrder')) && count($this->_filtersValues) > 0) {
                    $final1 = "<button id='remove_filters' onclick=\"window.location='$url'\">"
                        . $this->__('Clear Filters') . "</button>";

                //Only order
            } elseif (count($this->_filtersValues) == 0
                && ($this->getParam('order')
                && !$this->getParam('noOrder')
                && $this->getInfo('noOrder') != 1)
            ) {
                    $final1 = "<button id='remove_order' onclick=\"window.location='$url2'\">"
                        . $this->__('Clear Order') . "</button>";
            }

            //Replace values
            if (($this->getInfo('noFilters') != 1 || $this->_allowAdd == 1)
                && !$this->getParam('add') && !$this->getParam('edit')
            ) {

                if (strlen($final1) > 5 || $this->getUseKeyEventsOnFilters() == false) {
                    if ($this->getUseKeyEventsOnFilters() === false && $this->getInfo('noFilters') != 1) {
                        $final1 .= "<button id='apply_filters' onclick=\"_" . $this->getGridId()
                                . "gridChangeFilters(1)\">"
                                . $this->__('Apply Filter') . "</button>";
                    }

                    if ($this->_allowAdd == true)
                        $final1 .= $addButton;

                    $this->_render['addButton'] = $addButton;

                    $this->_render['extra'] = $this->_temp['table']->extra($final1);
                    $this->_renderDeploy['extra'] =  $this->_temp['table']->extra($final1);
                }
            }
        }

        return;
    }

    /**
     * Build filters
     * We receive the information from an array
     *
     * @param array $filters Filters Params
     *
     * @return string
     *
     */
    protected function _buildFiltersTable($filters)
    {

        if (!is_array($filters)) {
            $filters = array();
        }

        //There are no filters.
        if (count($filters) == 0 && count($this->_externalFilters) == 0) {
            $this->_temp['table']->hasFilters = 0;
            return '';
        }

        //Start the template
        $grid = $this->_temp['table']->filtersStart();


        foreach ($filters as $filter) {
            // check if this goes on a new row
            if (isset($filter['newrow']) && $filter['newrow']) {
                break; // only filters from the first row can be displayed
            }

            // compute colowspan stuff
            $colspan = isset($filter['colspan']) && $filter['colspan'] !== null ? $filter['colspan'] : null;
            if ($colspan == "*") {
                $colspan = $this->_colspan;
            } else if ($colspan < 0) {
                $colspan = $this->_colspan + $colspan;
            }
            $colspan = $colspan !== null ?  $colspan : '';

            //Check extra fields
            if ($filter['type'] == 'extraField' && $filter['position'] == 'left') {
                //Replace values
                $filterValue = isset($filter['value']) ? $filter['value'] : '';

                $grid .=$this->_temp['table']->filtersLoop($filterValue,$colspan);
            }

            $hRowField = $this->getInfo("hRow,field") ? $this->getInfo("hRow,field") : '';

            //Check if we have an horizontal row
            if ((isset($filter['field']) && $filter['field'] != $hRowField && $this->getInfo('hRow', 'title'))
                || !$this->getInfo('hRow', 'title')
            ) {

                if ($filter['type'] == 'field') {
                    //Replace values
                    $grid .= $this->_temp['table']->filtersLoop($this->_formatField($filter['field']),$colspan);
                }
            }

            //Check extra fields from the right
            if ($filter['type'] == 'extraField' && $filter['position'] == 'right') {
                $filter['value'] = isset($filter['value']) ? $filter['value'] : '';
                $grid .= $this->_temp['table']->filtersLoop($filter['value'],$colspan);
            }
        }


        if (count($filters) == 0) {

            //Remove unwanted url params
            $url = $this->getUrl(array('filters', 'start',  '_exportTo', 'noFilters'));

            $helpJavascript = '';
            if (count($this->_externalFilters) > 0) {
                foreach (array_keys($this->_externalFilters) as $fil) {
                    $helpJavascript .= $fil . ',';
                }
            }
            $this->_javaScriptHelper = array('js' => $helpJavascript, 'url' => $url);
        }



        //Close template
        $grid .= $this->_temp['table']->filtersEnd();

        return $grid;
    }

    /**
     * Build Table titles.
     *
     * @param array $titles Titles Params
     *
     * @return string
     *
     */
    protected function _buildTitlesTable($titles, $header = false)
    {
        $orderField = null;

        if($this->getInfo('hRow') && $header==true)
        {
            return;
        }

        if (is_array($this->_order)) {
            //We must now the field that is being ordered. So we can grab the image
            $order = array_keys($this->_order);
            $order2 = array_keys(array_flip($this->_order));

            //The field that is being ordered
            $orderField = $order[0];

            //The opposite order
            $order = strtolower($order2[0]);
        }

        //Lets get the images for defining the order
        $images = $this->_temp['table']->images($this->getImagesUrl());

        //Initiate titles template
        $grid = $this->_temp['table']->titlesStart();

        if ($orderField === null) {
            //Lets get the default order using in the query (Zend_Db)
            $queryOrder = $this->getSource()->getSelectOrder();

            if (count($queryOrder) > 0) {
                $order = strtolower($queryOrder[1]) == 'asc' ? 'desc' : 'asc';
                $orderField = $queryOrder[0];
            }
        }

        if ($this->getParam('noOrder')) {
            $orderField = null;
        }

        foreach ($titles as $title) {


            // check if this goes on a new row
            if (isset($title['newrow']) && $title['newrow']) {
                break; // only titles from the first row can be displayed
            }

            // compute colowspan stuff
            $colspan = isset($title['colspan']) && $title['colspan'] !== null ? $title['colspan'] : null;
            if ($colspan == "*") {
                $colspan = $this->_colspan;
            } else if ($colspan < 0) {
                $colspan = $this->_colspan + $colspan;
            }
            $colspan = $colspan !== null ?  $colspan : '';


            //deal with extra field and template
            if ($title['type'] == 'extraField' && $title['position'] == 'left') {
                $grid .= $this->_temp['table']->titlesLoop($title['value'],$colspan);
            }

            $hRowTitle = $this->getInfo("hRow,field") ? $this->getInfo("hRow,field") : '';

            if ((isset($title['field']) && $title['field'] != $hRowTitle && $this->getInfo("hRow,title"))
                || !$this->getInfo("hRow,title")
            ) {
                if ($title['type'] == 'field') {
                    $hrefTitle = '';

                    $noOrder = $this->getInfo("noOrder") ? $this->getInfo("noOrder") : '';

                    if ($noOrder == 1) {

                        //user set the noOrder(1) method
                        $grid .= $this->_temp['table']->titlesLoop($this->__($title['value']),$colspan);
                    } else {
                        if (!isset($this->_data['fields'][$title['field']]['order'])) {
                            $this->_data['fields'][$title['field']]['order'] = true;
                        }

                        if ($this->getAlwaysShowOrderArrows() === false && $this->getShowOrderImages() === true) {
                            $imgF = explode('_', $this->getParam('order'));
                            $checkOrder = str_replace('_' . end($imgF), '', $this->getParam('order'));

                            if (in_array(strtolower(end($imgF)), array('asc', 'desc'))
                                && $checkOrder == $title['field']
                            ) {
                                $imgFinal = $images[strtolower(end($imgF))];
                            } else {
                                $imgFinal = '';
                            }
                        }

                        if ($this->getShowOrderImages() === false) {
                            $imgFinal = '';
                        }

                        $spanClass = '';

                        if ($orderField == $title['field']) {
                            $spanClass = ' class="selected" ';
                        }


                            //Replace values in the template
                            if (!array_key_exists('url', $title)) {

                                $grid .= $this->_temp['table']->titlesLoop($title['value'],$colspan);
                            } else {

                                if ($this->getAlwaysShowOrderArrows() === true
                                    && $this->getShowOrderImages() == true
                                ) {
                                    $url1 = $this->getUrl(array('order', 'start', 'comm', 'noOrder'),
                                                          array('order'=>$title['field'].'_DESC'));

                                    $url2 = $this->getUrl(array('order', 'start', 'comm', 'noOrder'),
                                                          array('order'=>$title['field'].'_ASC'));

                                    $link1 = "<a  href='$url1'>{$images['desc']}</a>";
                                    $link2 = "<a  href='$url2'>{$images['asc']}</a>";

                                    if (($orderField == $title['field'] && $order == 'asc')
                                        || $this->_data['fields'][$title['field']]['order'] == 0
                                    ) {
                                        $link1 = '';
                                    }

                                    if (($orderField == $title['field'] && $order == 'desc')
                                        || $this->_data['fields'][$title['field']]['order'] == 0
                                    ) {
                                        $link2 = '';
                                    }

                                    $grid .= $this->_temp['table']->titlesLoop($link2 . $title['value'] . $link1,
                                                                               $colspan);
                            } else {
                                    if ($this->getShowOrderImages() == false) {
                                        $hrefTitle = '';
                                        if (substr($title['url'], - 4) == '_ASC') {
                                            $hrefTitle = $this->__('Sort ASC') . ' ' . $title['value'];
                                        } elseif (substr($title['url'], - 5) == '_DESC') {
                                            $hrefTitle = $this->__('Sort DESC') . ' ' . $title['value'];
                                        }
                                    }

                                    if ( $this->_data['fields'][$title['field']]['order'] == 0
                                    ) {
                                        $grid .= $this->_temp['table']->titlesLoop($title['value'] . $imgFinal,$colspan);
                                    }else{
                                        $grid .= $this->_temp['table']->titlesLoop("<a title='$hrefTitle' href='"
                                              . $title['url'] . "'><span $spanClass>". $title['value'] . $imgFinal
                                              . "</span></a>",$colspan);
                                    }

                                }
                        }
                    }
                }
            }

            //Deal with extra fields
            if ($title['type'] == 'extraField' && $title['position'] == 'right') {
                $grid .= $this->_temp['table']->titlesLoop($title['value'],$colspan);
            }
        }

        //End template
        $grid .= $this->_temp['table']->titlesEnd();

        return $grid;
    }

    /**
     * Build the table
     *
     * @param array $grids | db results
     *
     * @return string
     *
     */
    protected function _buildGridTable($grids)
    {
        $sqlExp = $this->getInfo('sqlexp');
        $i = 0;
        $grid = '';

        //We have an extra td for the text to remove filters and order
        if ($this->getParam('filters') || $this->getParam('order')) {
            $i++;
        }

        if ($this->getInfo("hRow,title") && $this->_totalRecords > 0) {

            $gridData = $grids;
            $hbar = trim($this->getInfo("hRow,field"));
            $p = 0;

            foreach ($grids[0] as $value) {
                if (isset($value['field']) && $value['field'] == $hbar) {
                    $hRowIndex = $p;
                }
                $p++;
            }
            $gridDataIndex = 0;
        }

        $gridDataIndex = 0;
        $class = 0;
        $fi = array();
        foreach ($grids as $value) {

            unset($fi);
            // decorators
            $search = $this->_finalFields;
            foreach ($search as $key => $final) {
                if ($final['type'] == 'extraField') {
                    unset($search[$key]);
                }
            }

            $search = array_keys($search);

            foreach ($value as $tia) {
                if (isset($tia['field']) && $tia['type']=='field') {
                    $fi[] = $tia['value'];
                }
            }

            if ($this->getSource()->hasCrud()) {

                if (isset($search[0]) && ($search[0] === 'D' || $search[0] === 'E' || $search[0] === 'V')) {
                    unset($search[0]);
                }

                if (isset($search[1]) && ($search[1] === 'D' || $search[1] === 'E')) {
                    unset($search[1]);
                }

                if (isset($search[2]) && ($search[2] === 'D' || $search[2] === 'E')) {
                    unset($search[2]);
                }
            } else {
                if (isset($search[0]) && $search[0] === 'V') {
                    unset($search[0]);
                }
            }

            $search = array_values($search);

            $finalFields = array_combine($search, $fi);

            //horizontal row
            if ($this->getInfo("hRow,title")) {
                $col = $this->getInfo("hRow");
                $firstRow = false;

                if (!isset($gridData[$gridDataIndex - 1][$hRowIndex])) {
                    $gridData[$gridDataIndex - 1][$hRowIndex]['value'] = '';
                    $firstRow = true;
                }

                if ($gridData[$gridDataIndex][$hRowIndex]['value'] != $gridData[$gridDataIndex - 1][$hRowIndex]['value']) {
                    $i++;

                    if (isset($gridData[$gridDataIndex - 1]) && $firstRow !== true) {
                        $sqlExpValue = $gridData[$gridDataIndex - 1][$hRowIndex]['value'];
                        $colName = $col['field'];
                        foreach ($sqlExp as $sInfo => $sValue) {
                            if (isset($sValue['field'][$gridData[$gridDataIndex - 1][$hRowIndex]['field']])) {
                                $colName = reset($sValue['field']);
                                $nField = $sValue['field'][$gridData[$gridDataIndex - 1][$hRowIndex]['field']];
                                $sqlExpValue = $this->_result[$gridDataIndex - 1][$nField];
                            }
                        }
                        $grid .= $this->_buildSqlexpTable(
                                        $this->_buildSqlExp(array($colName => $sqlExpValue))
                        );
                    }

                    $grid .= $this->_temp['table']->hRow($gridData[$gridDataIndex][$hRowIndex]['value']);


                    $grid .= $this->_buildTitlesTable(parent::_buildTitles());

                }
            }

            $i++;

            //loop tr
            $rowclass = isset($this->_classRowConditionResult[$class]) ? $this->_classRowConditionResult[$class] : '';
            $grid .= $this->_temp['table']->loopStart($rowclass, '');

            $set = 0;
            foreach ($value as $final) {

                $finalField = isset($final['field']) ? $final['field'] : '';
                $finalHrow = $this->getInfo("hRow,field") ? $this->getInfo("hRow,field") : '';


                // check if this goes on a new row
                if (isset($final['newrow']) && $final['newrow']) {

                    $grid .= $this->_temp['table']->loopEnd();

                    if (is_array($final['newrow'])) {

                        if (isset($final['newrow']['class'])) {
                            $subRow = $final['newrow']['class'] . ' subrow ' . $rowclass ;
                        } else {
                            $subRow = $rowclass . ' subrow';
                        }



                        $grid .= $this->_temp['table']->loopStart(
                                        $subRow, isset($final['newrow']['style']) ? $final['newrow']['style'] : ''
                        );
                    } else {

                        $grid .= $this->_temp['table']->loopStart($rowclass . ' subrow', '');
                    }
                }


                if (($finalField != $finalHrow && $this->getInfo("hRow,title")) || !$this->getInfo("hRow,title")) {
                    $set++;

                    // compute rowspan/colowspan stuff

                    if (isset($final['rowspan']) && strlen($final['rowspan']) > 0) {
                        $rowspan = "rowspan='" . $final['rowspan'] . "'" ;
                    } else {
                        $rowspan =  '';
                    }

                    if (isset($final['colspan']) && strlen($final['colspan']) > 0) {
                        $colspan = $final['colspan'];
                    } else {
                        $colspan = null;
                    }


                    if ($colspan == "*") {
                        $colspan = $this->_colspan;
                    } else if ($colspan < 0) {
                        $colspan = $this->_colspan + $colspan;
                    }

                    $colspan = $colspan !== null ?  $colspan : '';
                    $classLoop = strlen($final['class']) > 1 ?   $final['class']  : '';
                    $styleLoop = strlen($final['style']) > 1 ?   $final['style']  : '';

                    $grid .=  $this->_temp['table']->loopLoop($final['value'],$classLoop,$styleLoop,$rowspan,$colspan);
                }
            }

            if ($this->getInfo("hRow,title") && $this->_totalRecords > 0) {

                if (($gridDataIndex + 1) == $this->getTotalRecords()) {

                    $sqlExpValue = $gridData[$gridDataIndex - 1][$hRowIndex]['value'];
                    $colName = $col['field'];

                    foreach ($sqlExp as $sInfo => $sValue) {
                        if (isset($sValue['field'][$gridData[$gridDataIndex - 1][$hRowIndex]['field']])) {
                            $colName = reset($sValue['field']);
                            $nField = $sValue['field'][$gridData[$gridDataIndex - 1][$hRowIndex]['field']];
                            $sqlExpValue = $this->_result[$gridDataIndex - 1][$nField];
                        }
                }

                    $grid .= $this->_buildSqlexpTable($this->_buildSqlExp(array($colName => $sqlExpValue)));
                }
            }

            $set = null;
            $grid .= $this->_temp['table']->loopEnd();

            @$gridDataIndex++;
            $class++;
        }

        if ($this->_totalRecords == 0) {
            $grid = $this->_temp['table']->noResults($this->__('No records found'));
        }

        return $grid;
    }

    /**
     * Build the table that handles the query result from sql expressions
     *
     * @param array $sql Params to build SQL exp
     *
     * @return string
     *
     */
    protected function _buildSqlexpTable($sql)
    {

        $grid = '';
        if (is_array($sql)) {
            $grid .= $this->_temp['table']->sqlExpStart();

            foreach ($sql as $exp) {
                if (!$this->getInfo("hRow,field") || $exp['field'] != $this->getInfo("hRow,field")) {
                    $grid .= $this->_temp['table']->sqlExpLoop($exp['value'],$exp['class']);
                }
            }
            $grid .= $this->_temp['table']->sqlExpEnd();
        } else {
            return false;
        }

        return $grid;
    }

    /**
     * Change form elements order to match query order
     *
     * @return void
     */
    private function _orderFormElements()
    {
        $hasCustomOrder = false;

        foreach ($this->_form->getSubForms() as $form) {
            foreach ($form->getElements() as $key => $element) {
                if (is_numeric($element->getOrder())) {
                    $hasCustomOrder = true;
                    break;
                }
            }
        }

        if ($hasCustomOrder === true) {
            return;
        }

        $fieldsOrder = array_flip($this->_fields);

        foreach ($this->_form->getSubForms() as $form) {
            $i = 100;
            foreach ($form->getElements() as $key => $element) {
                $fieldOrder = isset($fieldsOrder[$key]) ? $fieldsOrder[$key] : $i++;
                $element->setOrder($fieldOrder);
            }
        }
    }

    /**
     * Build pagination
     *
     * @return string
     */
    protected function _buildPagination()
    {
        $pageSelect = '';

        if (count($this->_paginationInterval) > 0 && $this->getTotalRecords() > 0) {
            if (!array_key_exists($this->_recordsPerPage, $this->_paginationInterval) ) {
                $this->_paginationOptions[0] = $this->__('Select');
            }
            ksort($this->_paginationInterval);

            foreach ($this->_paginationInterval as $key => $value) {
                $this->_paginationOptions[$key] = $this->__($value);
            }

            $url = $this->getUrl('perPage');

            $menuPerPage = ' | ' . $this->__('Show') . ' '
                         . $this->getView()->formSelect(
                            'perPage' . $this->getGridId(), $this->getParam('perPage', $this->_recordsPerPage), array('onChange' => "window.location='$url/perPage"
                         . $this->getGridId() . "/'+this.value;"), $this->_paginationInterval
                           )
                         . ' ' . $this->__('items');
        } else {
            $menuPerPage = '';
        }

        $url = $this->getUrl(array('start'));

        $actual = (int) $this->getParam('start');

        $ppagina = (int) $this->getParam('perPage');
        if ($ppagina == 0) {
            $ppagina = $this->_recordsPerPage;
        }
        $result2 = '';

        $pa = $actual == 0 ? 1 : ceil($actual / $ppagina) + 1;

        // Calculate the number of pages
        if ($this->_recordsPerPage > 0) {
            $npaginas = ceil($this->_totalRecords / $ppagina);
            $actual = floor($actual / $ppagina) + 1;
        } else {
            $npaginas = 0;
            $actual = 0;
        }

        if (1 == $actual) {
            $pag = "<strong> 1 </strong>";
        } else {
            $pag = "<a href=\"$url/start{$this->getGridId()}/0\">1</a>";
        }

        $pag .= ( $actual > 5) ? " ... " : "  ";

        if ($npaginas > 5) {

            $in = min(max(1, $actual - 4), $npaginas - 5);
            $fin = max(min($npaginas, $actual + 4), 6);

            for ($i = $in + 1; $i < $fin; $i++) {

                if ($i == $actual) {
                    $pag .= "<strong> $i </strong>";
                } else {
                    $pag .=  " <a href='$url/start{$this->getGridId()}/" . (($i - 1) * $ppagina) . "'> $i </a>";
                }
            }

            $pag .= ( $fin < $npaginas) ? " ... " : "  ";

        } else {

            for ($i = 2; $i < $npaginas; $i++) {

                if ($i == $actual) {
                    $pag .= "<strong> $i </strong>";
                } else {
                    $pag .= " <a href=\"" . $url . "/start{$this->getGridId()}/"
                         . (($i - 1) * $ppagina) . "\">$i</a> ";
                }
            }
        }


        if ($actual == $npaginas) {
            $pag .= "<strong>" . $npaginas . "</strong>" ;
        } else {
            $pag .= " <a href=\"$url/start{$this->getGridId()}/"
                  . (($npaginas - 1) * $ppagina) . "\">$npaginas</a> ";
        }


        if ($actual != 1) {
            $pag = " <a href=\"$url/start/0\">" . $this->__('First')
                 . "</a>&nbsp;&nbsp;<a href=\"$url/start{$this->getGridId()}/" . (($actual - 2) * $ppagina)
                 . "\">" . $this->__('Previous') . "</a>&nbsp;&nbsp;" . $pag;
        }

        if ($actual != $npaginas) {

            $pag .= "&nbsp;&nbsp;<a href=\"$url/start{$this->getGridId()}/" . ($actual * $ppagina) . "\">"
                  . $this->__('Next') . "</a>&nbsp;&nbsp;<a href=\"$url/start{$this->getGridId()}/"
                  . (($npaginas - 1) * $ppagina) . "\">" . $this->__('Last') . "</a>";
        }

        if ($npaginas > 1 && $this->getInfo("limit") == 0) {

            if ($npaginas <= 100) {

                $pageSelectOptions = array();

                for ($i = 1; $i <= $npaginas; $i++) {
                    $pageSelectOptions[(($i - 1) * $ppagina)] = $i;
                }

                $pageSelect = $this->getView()->formSelect(
                                'idf' . $this->getGridId(), ($pa - 1) * $this->getRecordsPerPage(), array('onChange' => "window.location='{$url}/start{$this->getGridId()}/'+this.value"), $pageSelectOptions
                );
            } else {
                $pageSelect = $this->getView()->formText(
                                'idf', $pa, array('style' => 'width:30px !important; ',
                           'onChange' => "window.location='{$url}/start{$this->getGridId()}/'+(this.value - 1)*"
                                       . $this->getRecordsPerPage())
                );
            }

            $pageSelect = ' | ' . $this->__('Page') . ':' . $pageSelect;
        }

        if ($npaginas > 1 || count($this->_export) > 0) {
            //get actual record
            if ($actual <= 1) {
                $registoActual = 1;
                $registoFinal = $this->_totalRecords > $ppagina ? $ppagina : $this->_totalRecords;
            } else {
                $registoActual = $actual * $ppagina - $ppagina;

                if ($actual * $ppagina > $this->_totalRecords) {
                    $registoFinal = $this->_totalRecords;
                } else {
                    $registoFinal = $actual * $ppagina;
                }
            }

            $images = $this->_temp['table']->images($this->getImagesUrl());

            $url1 = $url = $this->getUrl(array('start'));

            $this->_render['export'] = $this->_temp['table']->export(
                $this->getExports(),
                $this->getImagesUrl(),
                $url1,
                $this->getGridId()
            );

            if ((int) $this->getInfo("limit") > 0) {

                $result2 = $this->_temp['table']->pagination('',
                                                             (int) $this->getInfo("limit"),
                                                             $menuPerPage,
                                                             $pageSelect);
            } elseif ($npaginas > 1 && count($this->_export) > 0) {
                if ($this->_recordsPerPage == 0) {
                    $pag = '';
                    $pageSelect = '';
                }

                $result2 =
                    $this->_temp['table']->pagination(' | ' . $pag,
                          $registoActual . ' ' . $this->__('to') . ' '
                            . $registoFinal . ' ' . $this->__('of') . '  '
                            . $this->_totalRecords,
                          $menuPerPage,
                          $pageSelect);

            } elseif ($npaginas < 2 && count($this->_export) > 0) {

                if ($this->_recordsPerPage == 0) {
                    $pag = '';
                    $pageSelect = '';
                }

                $result2 .=  $this->_temp['table']->pagination('',
                          $this->_totalRecords,
                          $menuPerPage,
                          $pageSelect);

            } elseif (count($this->_export) == 0) {
                if ($this->_recordsPerPage == 0) {
                    $pag = '';
                    $pageSelect = '';
                }
                $result2 .= $this->_temp['table']->pagination(' | ' . $pag,
                          $this->_totalRecords,
                          $menuPerPage,
                          $pageSelect);
            }
        } else {
            return '';
        }

        return $result2;
    }

    /**
     * Here we go....
     *
     * @return string
     */
    public function deploy()
    {
        if ($this->getSource() === null) {
            throw new Bvb_Grid_Exception('Please Specify your source');
        }

        if ($this->getRequest()->isPost()
            && $this->getRequest()->getPost('postMassIds'.$this->getGridId())
        ) {
            $this->_redirect($this->getUrl(array(
                           'zfmassedit',
                           'send_',
                           'gridAction_',
                           'massActionsAll_')));
            die();
        }

        if ($this->_allowDelete == 1 || $this->_allowEdit == 1 || $this->_allowAdd == 1) {
            $this->setAjax(false);
        }

        $this->_view = $this->getView();

        $this->_placePageAtRecord();

        if (isset($this->_ctrlParams['_zfgid']) && $this->_ctrlParams['_zfgid'] != $this->getGridId())
            return;

        parent::deploy();


        $this->_applyConfigOptions(array());

        $this->_processForm();

        if (!$this->_temp['table'] instanceof Bvb_Grid_Template_Table) {
            $this->setTemplate('table', 'table', $this->_templateParams);
        } else {
            $this->setTemplate($this->_temp['table']->options['name'], 'table', $this->_templateParams);
        }

        $images = $this->_temp['table']->images($this->getImagesUrl());

        if ($this->_allowDelete == 1
            || $this->_allowEdit == 1
            || (is_array($this->_detailColumns))
        ) {
            $pkUrl = $this->getSource()->getIdentifierColumns($this->_data['table']);
            $urlFinal = '';

            $failPk = false;
            $pkUrl2 = $pkUrl;
            foreach ($pkUrl as $key => $value) {
                foreach ($this->getFields(true) as $field) {
                    if ($field['field'] == $value) {
                        unset($pkUrl2[$key]);
                        break 2;
                    }
                }

                // throw new Bvb_Grid_Exception("You don't have your primary key in your query.
                // So it's not possible to perform CRUD operations.
                // Change your select object to include your Primary Key: " . implode(';', $pkUrl2));
            }

            foreach ($pkUrl as $value) {
                if (strpos($value, '.') !== false) {
                    $urlFinal .= '{{' . substr($value, strpos($value, '.') + 1) . '}}-';
                } else {
                    $urlFinal .= '{{' . $value . '}}-';
                }
            }

            $urlFinal = trim($urlFinal, '-');
        }


        $removeParams = array('add', 'edit');

        $url = $this->getUrl($removeParams);
        if ($this->_allowEdit == 1 && is_object($this->_crud) && $this->_crud->getBulkEdit() !== true) {


            $urlEdit = $url;

            $this->_actionsUrls['edit'] = "$urlEdit/edit" . $this->getGridId()
                                        . "/" . $urlFinal;

            if ($this->_crud->getEditColumn() !== false)
            {
                $this->addExtraColumn(array(
                    'position' => $this->getCrudColumnsPosition(),
                    'name' => 'E',
                    'decorator' => "<a href=\"" . $this->_actionsUrls['edit'] . "\" > " . $images['edit'] . "</a>",
                    'edit' => true,
                    'order' => -2,
                ));

            }
        }

        if ($this->_allowDelete && is_object($this->_crud) && $this->_crud->getBulkDelete() !== true) {

            if ($this->_deleteConfirmationPage == true) {
                $this->_actionsUrls['delete'] = "$url/delete" . $this->getGridId() . "/$urlFinal"
                                              . "/detail" . $this->getGridId()
                                              . "/1";

                if ($this->_crud->getDeleteColumn() !== false)
                {
                    $this->addExtraColumn(array(
                            'position' => $this->getCrudColumnsPosition(),
                            'name' => 'D',
                            'class' => 'gridDeleteColumn',
                            'decorator' => "<a href=\"" . $this->_actionsUrls['delete'] . "\" > "
                                             . $images['delete'] . "</a>",
                            'delete' => true,
                            'order' => -3,
                        ));

                }
            } else {
                $this->_actionsUrls['delete'] = "$url/delete/" . $urlFinal;

                if ($this->_crud->getDeleteColumn() !== false)
                {
                    $this->addExtraColumn(array(
                            'position' => $this->getCrudColumnsPosition(),
                            'name' => 'D',
                            'class' => 'gridDeleteColumn',
                            'decorator' => "<a href=\"#\" onclick=\"_" . $this->getGridId() . "confirmDel('"
                                        . $this->__('Are you sure?') . "','"
                                        . $this->_actionsUrls['delete'] . "');\" > " . $images['delete'] . "</a>",
                            'delete' => true,
                            'order' => -3,
                        ));
                }
            }
        }

        if (is_array($this->_detailColumns) && $this->_isDetail == false) {

            $removeParams = array('add', 'edit');
            $url = $this->getUrl($removeParams);

            $this->_actionsUrls['detail'] = "$url/detail" . $this->getGridId(). "/" . $urlFinal ;

            if ($this->_showDetailColumn === true)
            {
                $this->addExtraColumn(array(
                        'position' => $this->getCrudColumnsPosition(),
                        'name' => 'V',
                        'class' => 'gridDetailColumn',
                        'decorator' => "<a href=\"" . $this->_actionsUrls['detail'] . "\" >" . $images['detail'] . "</a>",
                        'detail' => true,
                        'order' => -1,
                    ));
            }
        }

        if ($this->_allowAdd == 0 && $this->_allowDelete == 0 && $this->_allowEdit == 0) {
            $this->_gridSession->unsetAll();
        }

        if (!in_array('add' . $this->getGridId(), array_keys($this->getParams()))
            && !in_array('edit' . $this->getGridId(), array_keys($this->getParams()))
        ) {
            if ($this->_gridSession->correct === null || $this->_gridSession->correct === 0) {
                $this->_gridSession->unsetAll();
            }
        }

        if (strlen($this->_gridSession->message) > 0) {
            $this->_render['message'] =
                $this->_temp['table']->formMessage($this->_gridSession->messageOk,$this->_gridSession->message);

            $this->_renderDeploy['message'] = $this->_render['message'];
        }

        if (($this->getParam('edit') && $this->_allowEdit==1)
            || ($this->getParam('add')  && $this->_allowAdd==1) || $this->getInfo("doubleTables") == 1) {

            if ($this->_allowAdd == 1 || $this->_allowEdit == 1) {

                // Remove the unnecessary URL params
                $removeParams = array('filters', 'add');

                $url = $this->getUrl($removeParams);

                $this->_orderFormElements();

                $this->_renderDeploy['form'] = $this->_form->render();
                $this->_render['form'] = $this->_form->render();

                $this->_showsForm = true;
            }
        }

        $showsForm = $this->getWillShow();

        if ((isset($showsForm['form']) && $showsForm['form'] == 1 && $this->getInfo("doubleTables") == 1)
            || !isset($showsForm['form'])
        ) {
            $this->_render['start'] = $this->_temp['table']->globalStart();
            $this->_renderDeploy['start'] = $this->_render['start'];
        }

        if (((!$this->getParam('edit') )
            && !$this->getParam('add') )
            || $this->getInfo("doubleTables") == 1
        ) {

            if ($this->_isDetail == true
                || ($this->_deleteConfirmationPage == true && $this->getParam('delete') )
            ) {
                $columnsTemp = $this->getSource()->fetchDetail($this->getIdentifierColumnsFromUrl());

                $columns = array();
                foreach ($this->_fields as $orderValue) {
                    $columns[$orderValue] = $columnsTemp[$orderValue];
                }

                $this->_render['detail'] = $this->_temp['table']->globalStart();

                if (count($this->_detailColumns) > 0) {
                    $columns = array_intersect_key( $columns , array_flip($this->_detailColumns) );
                }

                foreach ($columns as $field => $options) {
                    $this->updateColumn($field,array('hidden'=>false));
                }

                $result = array($columns);

                $result = parent::_buildGrid($result);


                $this->_render['detail'] .= $this->_temp['table']->startDetail($this->getDetailViewTitle());
                foreach ($result[0] as $value) {

                    if(!isset($value['field']))
                        continue;

                    if($value['type']=='extraField'
                       && !in_array($value['field'],$this->_detailColumns))
                            continue;

                    $field = $value['field'];

                    if (isset($value['field'])
                        && isset($this->_data['fields'][$value['field']]['title'])) {
                        $field = $this->__($this->_data['fields'][$value['field']]['title']);
                    } else {
                        $field = $this->__(ucwords(str_replace('_', ' ',$field)));
                    }

                    $this->_render['detail'] .= $this->_temp['table']->detail($field, $value['value']);
                }

                if ($this->getParam('delete')) {
                    $localCancel = $this->getUrl(array( 'detail', 'delete'));

                    $localDelete = $this->getUrl(array('delete', 'detail'))
                                 . "/delete" . $this->getGridId() . "/"
                                 . str_replace("view", 'delete', $this->getParam('delete'));

                    $buttonRemove = $this->getView()->formButton(
                                    'delRecordGrid', $this->__('Remove Record'), array('onclick' => "window.location='$localDelete'")
                    );

                    $buttonCancel = $this->getView()->formButton(
                                    'delRecordGrid', $this->__('Cancel'), array('onclick' => "window.location='$localCancel'")
                    );

                    $this->_render['detail'] .= $this->_temp['table']->detailDelete($buttonRemove . ' ' . $buttonCancel);
                } else {
                    $this->_render['detail'] .= $this->_temp['table']->detailEnd($this->getUrl(array('detail')), $this->__($this->getDetailViewReturnLabel()));
                }

                $this->_render['detail'] .= $this->_temp['table']->globalEnd();

                $this->_renderDeploy['detail'] = $this->_render['detail'];
            } else {
                $this->_buildGridRender();
            }

        } else {
            $this->_render['start'] = $this->_temp['table']->globalStart();
            $this->_buildGridRender(false);
            $this->_render['end'] = $this->_temp['table']->globalEnd();
        }

        if ((isset($showsForm['form']) && $showsForm['form'] == 1 && $this->getInfo("doubleTables") == 1)
            || !isset($showsForm['form'])
        ) {
            $this->_render['end'] = $this->_temp['table']->globalEnd();
            $this->_renderDeploy['end'] = $this->_render['end'];
        }

        //Build JS
        $this->_printScript();

        $gridId = $this->getGridId();
        if (strlen($gridId) == 0) {
            $gridId = 'grid';
        }

        if (($this->getParam('gridmod') == 'ajax' && $this->getInfo("ajax") !== false )
            || $this->getRequest()->isXmlHttpRequest()) {
            $layout = Zend_Layout::getMvcInstance();
            if ($layout instanceof Zend_Layout) {
                $layout->disableLayout();
            }

            $response = Zend_Controller_Front::getInstance()->getResponse();
            $response->clearBody();
            $response->setBody(implode($this->_renderDeploy))
                ->sendHeaders()
                ->sendResponse();
            die();
        }

        if ($this->getInfo("ajax") !== false) {
            $gridId = $this->getInfo("ajax");
        }

        $grid = "<div id='{$gridId}'>" . implode($this->_renderDeploy) . "</div>";

        if ($this->_gridSession->correct == 1) {
            $this->_gridSession->unsetAll();
        }

        $this->_deploymentContent = $grid;
        return $this;
    }

    /**
     * Combines all parts from the output
     * To deploy or to render()
     *
     * @param bool $deploy IF this is the deploy and not a render part
     *
     * @return void
     */
    private function _buildGridRender($deploy = true)
    {
        $bHeader  = $this->_buildExtraRows('beforeHeader')
                  . $this->_buildHeader()
                  . $this->_buildExtraRows('afterHeader');

        $bTitles  = $this->_buildExtraRows('beforeTitles')
                  . $this->_buildMassActions()
                  . $this->_buildTitlesTable(parent::_buildTitles(),true)
                  . $this->_buildExtraRows('afterTitles');

        $bFilters = $this->_buildExtraRows('beforeFilters')
                  . $this->_buildFiltersTable(parent::_buildFilters())
                  . $this->_buildExtraRows('afterFilters');

        $bGrid    = $this->_buildGridTable(parent::_buildGrid());

        if (!$this->getInfo("hRow,title")) {
            $bSqlExp = $this->_buildExtraRows('beforeSqlExpTable')
                     . $this->_buildSqlexpTable(parent::_buildSqlExp())
                     . $this->_buildExtraRows('afterSqlExpTable');
        } else {
            $bSqlExp = '';
        }

        $bPagination = $this->_buildExtraRows('beforePagination')
                     . $this->_buildPagination()
                     . $this->_buildExtraRows('afterPagination');

        if ($deploy == true) {
            $this->_renderDeploy['header'] = $bHeader;
            $this->_renderDeploy['titles'] = $bTitles;
            $this->_renderDeploy['filters'] = $bFilters;
            $this->_renderDeploy['grid'] = $bGrid;
            $this->_renderDeploy['sqlExp'] = $bSqlExp;
            $this->_renderDeploy['pagination'] = $bPagination;
        }

        $this->_render['header'] = $bHeader;
        $this->_render['titles'] = $bTitles;
        $this->_render['filters'] = $bFilters;
        $this->_render['grid'] = $bGrid;
        $this->_render['sqlExp'] = $bSqlExp;
        $this->_render['pagination'] = $bPagination;
    }

    /**
     * Render parts of the grid
     *
     * @param string $part         Which part to render
     * @param bool   $appendGlobal Id we should append the global <table>
     *
     * @return string
     */
    public function render($part, $appendGlobal = false)
    {
        $result = '';

        if ($part == 'start' && $this->getInfo('ajax') !== false) {
            $result .= "<div id='" . $this->getInfo('ajax') . "'>";
        }

        if ($appendGlobal === true) {
            $result .= $this->_render['start'];
        }

        if (isset($this->_render[$part])) {
            $result .= $this->_render[$part];
        }

        if ($appendGlobal === true) {
            $result .= $this->_render['end'];
        }

        if ($part == 'end' && $this->getInfo('ajax') !== false) {
            $result .= "</div>";
        }

        return $result;
    }

    /**
     * Return string
     *
     * @return string
     */
    public function __toString()
    {
        if (is_null($this->_deploymentContent)) {
            die('You must explicitly call the deploy() method before printing the object');
        }
        return $this->_deploymentContent;
    }

    /**
     * Builds all JS necessary to run the grid (filters, mass actions, paga selection, ...)
     *
     * @return void
     */
    protected function _printScript()
    {
        $script = "";

        if ($this->getMassActions()->hasMassActions()) {
            $script .= " var confirmMessages_" . $this->getGridId() . " = new Array();" . PHP_EOL;

            foreach ($this->getMassActions()->getMassActionsOptions() as $value) {
                if (isset($value['confirm']) && strlen($value['confirm'])>0) {
                    $script .= " confirmMessages_" . $this->getGridId() . "['{$value['url']}']='{$value['confirm']}';";
                    $script .= PHP_EOL;
                }
            }
            $script .= "

var recordsSelected_" . $this->getGridId() . " = 0;

var postMassIds_" . $this->getGridId() . " = new Array();

function convertArrayToInput_" . $this->getGridId() . "()
{
    if(postMassIds_" . $this->getGridId() . ".length==0)
    {
        tempArray_" . $this->getGridId() . " = new Array();

            var campos = document.getElementsByTagName('input');

            for (i=0; i < campos.length; i++)
            {
                if (campos[i].type == 'checkbox'
                    && campos[i].id == 'massCheckBox_" . $this->getGridId() . "'
                    && campos[i].checked==true
                ) {
                            tempArray_" . $this->getGridId() . ".push(campos[i].value);
                }
            }

        recordsSelected_" . $this->getGridId() . " = tempArray_" . $this->getGridId() . ".length;
        updateRecords_" . $this->getGridId() . "();
        postMassIds_" . $this->getGridId() . " = tempArray_" . $this->getGridId() . ";

        if(tempArray_" . $this->getGridId() . ".length ==0)
        {
            alert('" . $this->__('No records selected') . "');
            return false;
        }
    }

    var input_" . $this->getGridId() . " = document.getElementById('gridAction_" . $this->getGridId() . "').value;

    for(var i in confirmMessages_" . $this->getGridId() . ")
    {
       if(i == input_" . $this->getGridId() . " &&
           !confirm(confirmMessages_" . $this->getGridId() . "[input_" . $this->getGridId() . "])
       ) {
        return false;
       }
    }

    document.forms.massActions_" . $this->getGridId() . ".action = input_" . $this->getGridId() . ";

    document.getElementById('postMassIds" . $this->getGridId() . "').value = postMassIds_" . $this->getGridId() . "
        .join('" . $this->getMassActions()->getRecordSeparator() . "');
}

function updateRecords_" . $this->getGridId() . "()
{
     document.getElementById('massSelected_" . $this->getGridId() . "').innerHTML =
         recordsSelected_" . $this->getGridId() . ";
}

function observeCheckBox_" . $this->getGridId() . "(box)
{
    if(box.checked == true)
    {
        if(postMassIds_" . $this->getGridId() . "[box.value] != 'undefined')
        {
            postMassIds_" . $this->getGridId() . ".push(box.value);
        }
        recordsSelected_" . $this->getGridId() . "++;
    }else{
         if(postMassIds_" . $this->getGridId() . "[box.value] != 'undefined')
        {
            for(var i=0; i< postMassIds_" . $this->getGridId() . ".length;i++ )
                 {
                    if( postMassIds_" . $this->getGridId() . "[i]==box.value)
                         postMassIds_" . $this->getGridId() . ".splice(i,1);
                  }
            recordsSelected_" . $this->getGridId() . "--;
        }
    }
    updateRecords_" . $this->getGridId() . "();
}

function checkAll_" . $this->getGridId() . "(field,total,all)
    {
       var tempArray_" . $this->getGridId() . " = new Array();

       var campos = document.getElementsByTagName('input');

        for (i=0; i < campos.length; i++)
        {
            if (campos[i].type == 'checkbox' && campos[i].id == 'massCheckBox_" . $this->getGridId() . "')
            {
                tempArray_" . $this->getGridId() . ".push(campos[i].value);
                campos[i].checked = true;
            }
        }

        if(all ==1)
        {
            postMassIds_" . $this->getGridId() . " =
                document.getElementById('massActionsAll_" . $this->getGridId() . "').value.split(',');
        }else{
            postMassIds_" . $this->getGridId() . " = tempArray_" . $this->getGridId() . ";
        }

         recordsSelected_" . $this->getGridId() . " = total;
         updateRecords_" . $this->getGridId() . "();
    }

function uncheckAll_" . $this->getGridId() . "(field)
{
    var campos = document.getElementsByTagName('input');

    for (i=0; i < campos.length; i++)
    {
        if (campos[i].type == 'checkbox' && campos[i].id == 'massCheckBox_" . $this->getGridId() . "')
        {
            campos[i].checked = false;
        }
    }

    recordsSelected_" . $this->getGridId() . " = 0;

    postMassIds_" . $this->getGridId() . " = new Array();

    updateRecords_" . $this->getGridId() . "();
}
" . PHP_EOL;
        }

        if ($this->_allowDelete == 1) {

            $script .= "function _" . $this->getGridId() . "confirmDel(msg, url)
        {
            if(confirm(msg))
            {
               window.location = url;
            }else{
                return false;
            }
        }\n\n";
        }

        if ((!$this->getInfo("noFilters") || $this->getInfo("noFilters") != 1 && !$this->_isDetail)
            || count($this->_externalFilters) > 0
        ) {
            $script .= "


function encodeString(str)
    {
        if(document.defaultCharset)
        {
            encoding = document.defaultCharset;
        }else{
            encoding = document.characterSet
        }

        encoding = encoding.toLowerCase();

        if(encoding.indexOf('iso-8859')!=-1)
        {
            str = escape(str);
        }else{
            str = encodeURIComponent(str);
        }

        return str;
    }

function _" . $this->getGridId() . "gridChangeFilters(event)
    {
        if(typeof(event)=='undefined')
        {
            event = 1;
        }

        if(event!= 1 && event.keyCode != 13)
        {
          return false;
        }

        var fields = '{$this->_javaScriptHelper['js']}';
        var url = '{$this->_javaScriptHelper['url']}';

        var fieldsArray = fields.split(\",\");
        var filtro = new Array();

        for (var i = 0; i < fieldsArray.length -1; i++)
            {

                if (!document.getElementById(fieldsArray[i])){
                    continue;
                }

                if(document.getElementById(fieldsArray[i]).type=='checkbox'
                   && document.getElementById(fieldsArray[i]).checked ==false)
                {
                    value = '';
                }else{
                    value = document.getElementById(fieldsArray[i]).value;
                }

                if(typeof(value) == 'undefined')
                {
                    value = dijit.byId(fieldsArray[i]).attr( 'value' );
                }

                if ( value.length > 0 )
                {
                    name = document.getElementById(fieldsArray[i]).name;
                    if ( name == '' || typeof(name) == 'undefined' ) {
                        name = dijit.byId(fieldsArray[i]).get('name');
                     }

                    value = value.replace(/^\s+|\s+$/g,'').replace(/\//,'');
                    filtro += encodeString(name) + '/'+encodeString(value)+'/';
        	}

            }

            window.location=url+'/'+filtro;
     }";
        }
        $this->getView()->headScript()->appendScript($script);

        return;
    }

    /**
     * Sets the form instance to be used
     *
     * @param Bvb_Grid_Form $crud Bvb_Grid_Form Instance
     *
     * @return Bvb_Grid_Deploy_Table
     *
     */
    public function setForm(Bvb_Grid_Form $crud)
    {

        $this->emitEvent('crud.set_form', array('form'=>$crud));

        $this->_bvbForm = $crud;

        $this->setAjax(false);

        $oldElements = $crud->getElements();
        $crud->clearElements();

        $displayGroupElements = array();
        if (count($crud->getForm()->getDisplayGroups()) > 0) {
            foreach ($crud->getForm()->getDisplayGroups() as $group) {
                $displayGroupElements = array_merge($displayGroupElements, $group->getElements());
            }
        }

        $formElements = $this->getSource()->buildForm($crud->getInputsType());

        if ($crud->getUsePreDefinedFormElements() == true) {
            $formElements = array();
        }

        $this->emitEvent('crud.elements_loaded', array('elements'=>&$form));


        if ($this->getParam('add')) {
            $formsCount = $crud->getBulkAdd() > 0 ? $crud->getBulkAdd() : 1;
        } elseif ($this->getParam('edit')) {

            $totalMassIds = explode(',', $this->getParam('postMassIds'));
            $totalMassIds = count($totalMassIds);

            $formsCount = $totalMassIds > 0 ? $totalMassIds : 1;
        } else {
            $formsCount = 1;
        }

        if ($crud->getBulkDelete() == true) {
            $this->getMassActions()->addMassActions(
                array(
                    array('url' => $this->getUrl() . '/zfmassremove' . $this->getGridId() . '/1/',
                          'caption' => $this->__('Remove Selected Records'),
                          'confirm' => $this->__('Are you sure?')
                    )
                )
            );
        }

        if ($crud->getBulkEdit() == true) {

            $editMassUrl = $this->getUrl() . '/zfmassedit' . $this->getGridId() . '/1/edit' . $this->getGridId() . '/1';
            $this->getMassActions()->addMassActions(
                array(
                    array('url' => $editMassUrl,
                          'caption' => $this->__('Edit Selected Records')
                    )
                )
            );
        }

        $this->_crud = $crud;

        $arr = array();

        if ($crud->getUseVerticalInputs() === false) {
            $arr[0] = new Zend_Form_SubForm($formElements);

            if ($formsCount > 1)
                $arr[0]->addElement('checkbox', 'ZFIGNORE', array('label' => $this->__('Ignore'), 'order' => 0));
                $arr[0]->setElementDecorators($crud->getSubformElementTitle());

            if ($crud->getUseDecorators() === true) {

                if ($crud->getUseVerticalInputs()) {
                    $verticalInputs = $crud->getSubFormDecorator();
                } else {
                    $verticalInputs = $crud->getSubFormDecoratorVertical();
                }

                $arr[0]->setDecorators($verticalInputs);
            }

            $crud->getForm()->addSubForm($arr[0], 0);

            foreach ($crud->getForm()->getSubForm(0)->getElements() as $value) {
                $value->clearValidators();
                $value->setRequired(false);
            }
        }

        for ($i = 1; $i <= $formsCount; $i++) {
            $arr[$i] = new Zend_Form_SubForm($formElements);

            if ($formsCount > 1)
                $arr[$i]->addElement(
                        'checkbox', 'ZFIGNORE', array('label' => $this->__('Ignore record'), 'order' => 0)
                );

            if ($crud->getUseDecorators() === true) {

                if ($crud->getUseVerticalInputs()) {
                    $verticalInputs = $crud->getSubFormDecorator();
                } else {
                    $verticalInputs = $crud->getSubFormDecoratorVertical();
                }

                $arr[$i]->setDecorators($verticalInputs);
            }

            if ($this->getParam('edit')) {
                $arr[$i]->addElement('hidden', 'ZFPK', array('decorators' => $crud->getButtonHiddenDecorator()));
            }


            if ($this->getParam('add')) {
                $fieldsDisallowed = array_merge($crud->getDisallowedFields(), array_keys($crud->getOnAddForce()));
               $crud->setDisallowedFields($fieldsDisallowed);
            }
            if ($this->getParam('edit')) {
                $fieldsDisallowed = array_merge($crud->getDisallowedFields(), array_keys($crud->getOnEditForce()));
                $crud->setDisallowedFields($fieldsDisallowed);
            }

            $crud->getForm()->addSubForm($arr[$i], $i);

            $form = $crud->getForm()->getSubForm($i);

            foreach ($oldElements as $key => $value) {
                if ($value->helper == 'formHidden') {
                    $value->setDecorators($crud->getButtonHiddenDecorator());
                }

                $form->addElement($value);
            }

            if (count($form->getElements()) > 0) {
                foreach ($form->getElements() as $key => $value) {
                    if ($value->helper == 'formHidden') {
                        continue;
                    }

                    if ($crud->getUseDecorators() === true) {

                        if ($crud->getUseVerticalInputs()) {
                            $decorator = $crud->getSubformElementDecorator();
                        } else {
                            $decorator = $crud->getSubformElementDecoratorVertical();
                        }

                        $value->setDecorators($decorator);
                    }
                }
            }

            if ($crud->getFieldsBasedOnQuery() == 1) {
                $finalFieldsForm = array();
                $fieldsToForm = $this->getFields(true);

                foreach ($fieldsToForm as $key => $value) {
                    $field = substr($value['field'], strpos($value['field'], '.') + 1);
                    $finalFieldsForm[] = $field;
                }
                foreach ($form->getElements() as $key => $value) {
                    if ($key == 'ZFIGNORE' || $key == 'ZFPK')
                        continue;

                    if (!in_array($key, $finalFieldsForm)) {
                        $form->removeElement($key);
                    }
                }
            }

            if (count($crud->getAllowedFields()) > 0) {
                foreach ($form->getElements() as $key => $value) {
                    if ($key == 'ZFIGNORE' || $key == 'ZFPK')
                        continue;

                    if (!in_array($key, $crud->getAllowedFields())) {
                        $form->removeElement($key);
                    }
                }
            }

            if (count($crud->getDisallowedFields()) > 0) {
                foreach ($form->getElements() as $key => $value) {
                    if ($key == 'ZFIGNORE' || $key == 'ZFPK')
                        continue;

                    if (in_array($key, $crud->getDisallowedFields())) {
                        $form->removeElement($key);
                    }
                }
            }

            foreach ($this->_data['fields'] as $key => $title) {
                if ($form->getElement($key) && strlen($form->getElement($key)->getLabel())==0 ) {
                    $form->getElement($key)->setLabel($title['title']);
                }
            }

            if (count($form->getElements()) == 0) {
                throw new Bvb_Grid_Exception($this->__("Your form does not have any fields"));
            }

            if (count($displayGroupElements) > 0) {
                foreach ($displayGroupElements as $key => $value) {
                    $form->removeElement($key);
                }
            }

            foreach ($form->getElements() as $element) {
                if ($element->helper == 'formFile') {
                    if ($crud->getUseDecorators() === true)
                        $element->setDecorators($crud->getFileDecorator());
                }
                if ($element->helper == 'formHidden') {
                    $element->setLabel('');
                }
            }


            if ($formsCount == 1 && $crud->getFormTitle() && $crud->getUseVerticalInputs()) {
                $form->addElement('text','infoTextHeader',array('ignore'=>true,
                                               'label'=>$crud->getFormTitle(),
                                               'required'=>false,
                                               'order'=>-1,
                                               'decorators'=>array(
                        array('Label'),
                        array(array('data2' => 'HtmlTag'), array('tag' => 'th', 'colspan' => '2')),
                        array(array('row' => 'HtmlTag'), array('tag' => 'thead'), array('tag' => 'tr'))
                    )));
            }
        }

        if ($crud->getUseVerticalInputs() === false) {
            foreach ($crud->getForm()->getSubForm(0)->getElements() as $key => $value) {
                if (!in_array($key, array_keys($crud->getForm()->getSubForm(1)->getElements()))) {
                    $crud->getForm()->getSubForm(0)->removeElement($key);
                }
            }
        }

        if (count($crud->getForm()->getDisplayGroups()) > 0) {
            foreach ($crud->getForm()->getDisplayGroups() as $group) {
                $group->setDecorators($crud->getDisplayGroupsDecorator());
            }
        }

        if ($crud->getUseDecorators() === true) {
            $crud->getForm()->setDecorators($crud->getFormDecorator());
        } else {
            $crud->getForm()->setDecorators($crud->getFormDecoratorSimple());
        }

        $crud->getForm()->setMethod('post');

        if (isset($crud->options['saveAndAddButton'])
            && $crud->options['saveAndAddButton'] == true
            && !$this->getParam('edit')
        ) {
            $crud->getForm()->addElement(
                'submit',
                'saveAndAdd' . $this->getGridId(),
                array('label' => $this->__('Save And New'),
                      'class' => 'submit',
                      'decorators' => $crud->getButtonHiddenDecorator())
            );
        }

        $crud->getForm()->addElement(
            'submit',
            'form_submit' . $this->getGridId(),
            array('label' => $this->__('Save'),
                  'class' => 'submit',
                  'decorators' => $crud->getButtonHiddenDecorator())
        );

        $crud->getForm()->addElement(
            'hidden',
            'zfg_form_edit' . $this->getGridId(),
            array('value' => 1,
                  'decorators' => $crud->getButtonHiddenDecorator())
        );

        if ($crud->getUseCSRF() == 1) {
            $crud->addElement(
                'hash',
                'zfg_csrf' . $this->getGridId(),
                array('salt' => 'unique',
                    'decorators' => $crud->getButtonHiddenDecorator())
            );
        }

        $url = $this->getUrl(
            array_merge(
                array('add',
                      'postMassIds',
                      'zfmassedit',
                      'edit',
                      'form_reset'),
                array_keys($crud->getForm()->getElements())
            )
        );

        $crud->getForm()->addElement(
            'button',
            'form_reset' . $this->getGridId(),
            array('onclick' => "window.location='$url'",
                'label' => $this->__('Cancel'),
                'class' => 'reset',
                'decorators' => $crud->getButtonHiddenDecorator())
        );


        $crud->getForm()->addDisplayGroup(
            array('zfg_csrf' . $this->getGridId(),
                  'zfg_form_edit' . $this->getGridId(),
                  'form_submit' . $this->getGridId(),
                  'saveAndAdd' . $this->getGridId(),
                  'form_reset' . $this->getGridId()
                  ),
            'buttons', array('decorators' => $crud->getSubformGroupDecorator())
        );

        $crud->setAction($this->getUrl(array_keys($crud->getForm()->getElements())));

        $this->emitEvent('crud.form_built', array('form'=>$crud));

        $this->_crudOptions['addForce'] = $crud->getOnAddForce();
        $this->_crudOptions['editForce'] = $crud->getOnEditForce();
        $this->_crudOptions['editAddCondition'] = $crud->getOnEditAddCondition();
        $this->_crudOptions['deleteAddCondition'] = $crud->getOnDeleteAddCondition();

        $this->_form = $crud->getForm();
        $this->_bvbForm = $crud;


        $crud = $this->_object2array($crud);

        $options = $crud['options'];

        if (isset($options['table']) && is_string($options['table'])) {
            $this->_crudTable = $options['table'];
        }

        if (isset($options['isPerformCrudAllowed']) && $options['isPerformCrudAllowed'] == 0) {
            $this->_crudTableOptions['add'] = 0;
            $this->_crudTableOptions['edit'] = 0;
            $this->_crudTableOptions['delete'] = 0;
        } else {
            $this->_crudTableOptions['add'] = 1;
            $this->_crudTableOptions['edit'] = 1;
            $this->_crudTableOptions['delete'] = 1;
        }

        if (isset($options['isPerformCrudAllowedForAddition'])
            && $options['isPerformCrudAllowedForAddition'] == 1
        ) {
            $this->_crudTableOptions['add'] = 1;
        } elseif (isset($options['isPerformCrudAllowedForAddition'])
            && $options['isPerformCrudAllowedForAddition'] == 0
        ) {
            $this->_crudTableOptions['add'] = 0;
        }

        if (isset($options['isPerformCrudAllowedForEdition'])
            && $options['isPerformCrudAllowedForEdition'] == 1
        ) {
            $this->_crudTableOptions['edit'] = 1;
        } elseif (isset($options['isPerformCrudAllowedForEdition'])
            && $options['isPerformCrudAllowedForEdition'] == 0
        ) {
            $this->_crudTableOptions['edit'] = 0;
        }

        if (isset($options['isPerformCrudAllowedForDeletion'])
            && $options['isPerformCrudAllowedForDeletion'] == 1
        ) {
            $this->_crudTableOptions['delete'] = 1;
        } elseif (isset($options['isPerformCrudAllowedForDeletion'])
            && $options['isPerformCrudAllowedForDeletion'] == 0
        ) {
            $this->_crudTableOptions['delete'] = 0;
        }

        $this->_info['doubleTables'] = $this->getInfo("doubleTables");

        if (isset($options['delete'])) {
            if ($options['delete'] == 1) {
                $this->_allowDelete = true;
                if (isset($options['onDeleteAddWhere'])) {
                    $this->_info['delete']['where'] = $options['onDeleteAddWhere'];
                }
            }
        }

        if (isset($options['add']) && $options['add'] == 1) {
            if (!isset($options['addButton'])) {
                $options['addButton'] = 0;
            }
            $this->_allowAdd = true;
            $this->_allowAddButton = $options['addButton'];
        }

        if (isset($options['edit']) && $options['edit'] == 1) {
            $this->_allowEdit = true;
        }

        $this->_buildFormValues();
        return $this;
    }

    /**
     * Field type on the filters area. If the field type is enum, build the options
     * Also, we first need to check if the user has defined values to present.
     * If set, this values override the others
     *
     * @param string $field Field Name
     *
     * @return string
     *
     */
    protected function _formatField($field)
    {
        $renderLoaded = false;
        $allFieldsIds = $this->getAllFieldsIds();

        if (!isset($this->_filters[$field])) {
            $this->_filters[$field] = array();
        }

        if (is_array($this->_filters[$field])
            && isset($this->_filters[$field]['render'])
        ) {
            $render = $this->loadFilterRender($this->_filters[$field]['render']);
            $render->setView($this->getView());
            $renderLoaded = true;
        }

        if (isset($this->_data['fields'][$field]['search'])
            && $this->_data['fields'][$field]['search'] == false
        ) {
            return '';
        }

        //check if we need to load  fields for filters
        if (isset($this->_filters[$field]['distinct'])
            && is_array($this->_filters[$field]['distinct'])
            && isset($this->_filters[$field]['distinct']['field'])
        ) {
            $distinctField = $this->_filters[$field]['distinct']['field'];
            $distinctValue = $this->_filters[$field]['distinct']['name'];

            if (isset($this->_filters[$field]['distinct']['order'])) {
                $distinctOrder = $this->_filters[$field]['distinct']['order'];
            } else {
                $distinctOrder = 'name ASC';
            }

            $dir = stripos($distinctOrder, ' asc') !== false ? 'ASC' : 'DESC';
            $sort = stripos($distinctOrder, 'name') !== false ? 'value' : 'field';

            if (isset($this->_data['fields'][$distinctField]['field'])) {
                $distinctField = $this->_data['fields'][$distinctField]['field'];
            }
            if (isset($this->_data['fields'][$distinctValue]['field'])) {
                $distinctValue = $this->_data['fields'][$distinctValue]['field'];
            }

            $final = $this->getSource()->getDistinctValuesForFilters(
                $distinctField,
                $distinctValue, $sort . ' ' . $dir
            );

            $this->_filters[$field]['values'] = $final;
        }

        if (isset($this->_filters[$field]['table'])
            && is_array($this->_filters[$field]['table'])
            && isset($this->_filters[$field]['table']['field'])
        ) {
            $tableField = $this->_filters[$field]['table']['field'];
            $tableValue = $this->_filters[$field]['table']['name'];
            $tableName = $this->_filters[$field]['table']['table'];

            if (isset($this->_filters[$field]['table']['order'])) {
                $tableOrder = $this->_filters[$field]['table']['order'] ;
            } else {
                $tableOrder = 'name ASC';
            }



            $dir = stripos($tableOrder, ' asc') !== false ? 'ASC' : 'DESC';
            $sort = stripos($tableOrder, 'name') !== false ? 'value' : 'field';

            $final = $this->getSource()
                ->getValuesForFiltersFromTable($tableName, $tableField, $tableValue, $sort . ' ' . $dir);

            $this->_filters[$field]['values'] = $final;
        }

        //Remove unwanted url params
        $url = $this->getUrl(array('filters', 'start',  '_exportTo', 'noFilters'));

        $fieldsSemAsFinal = $this->_data['fields'];

        if (isset($fieldsSemAsFinal[$field]['searchField'])) {
            $nkey = $fieldsSemAsFinal[$field]['searchField'];
            @$this->_filtersValues[$field] = $this->_filtersValues[$nkey];
        }

        $helpJavascript = '';

        $i = 0;
        foreach (array_keys($this->_filters) as $value) {

            if (!isset($this->_data['fields'][$value]['search'])) {
                $this->_data['fields'][$value]['search'] = true;
            }

            $hRow = isset($this->_data['fields'][$value]['hRow']) ? $this->_data['fields'][$value]['hRow'] : '';

            if ($this->_displayField($value) && $hRow != 1 && $this->_data['fields'][$value]['search'] != false) {

                if (isset($allFieldsIds[$value]) && is_array($allFieldsIds[$value])) {
                    foreach ($allFieldsIds[$value] as $newId) {
                        $helpJavascript .= "filter_" . $value . $this->getGridId() . "_" . $newId . ',';
                    }
                } else {
                    $helpJavascript .= "filter_" . $value . $this->getGridId() . ",";
                }
            }
        }

        if (count($this->_externalFilters) > 0) {
            foreach (array_keys($this->_externalFilters) as $fil) {
                $helpJavascript .= $fil . ',';
            }
        }


        $this->_javaScriptHelper = array('js' => $helpJavascript, 'url' => $url);

        if ($this->getUseKeyEventsOnFilters() === true) {
            $attr['onChange'] = "_" . $this->getGridId() . "gridChangeFilters(1);";
        }
        $attr['onKeyUp'] = "_" . $this->getGridId() . "gridChangeFilters(event);";

        $opcoes = $this->_filters[$field];

        if (is_array($opcoes) && isset($opcoes['style'])) {
            $attr['style'] = $opcoes['style'];
        }

        if (is_array($opcoes) && isset($opcoes['class'])) {
            $attr['class'] = $opcoes['class'];
        }

        if (isset($this->_filters[$field])) {
            $opcoes = $this->_filters[$field];
        }

        $attr['id'] = "filter_" . $field . $this->getGridId();

        $selected = null;

        if (isset($this->_filters[$field]['values']) && is_array($this->_filters[$field]['values'])) {
            $hasValues = $this->_filters[$field]['values'];
        } else {
            $hasValues = $this->getSource()
                ->getFilterValuesBasedOnFieldDefinition($this->_data['fields'][$field]['field']);
        }

        if (is_array($hasValues)) {
            $opcoes = array();
            $tipo = 'text';
            $opcoes['values'] = $hasValues;
        } else {
            $tipo = 'text';
        }

        if (isset($opcoes['values']) && is_array($opcoes['values'])) {
            $tipo = 'invalid';
            $values = array();
            $values[''] = '--' . $this->__('All') . '--';

            $avalor = $opcoes['values'];

            if (isset($this->_data['fields'][$field]['translate'])
                && $this->_data['fields'][$field]['translate'] == 1
            ) {
                $avalor = array_map(array($this, '__'), $avalor);
            }

            foreach ($avalor as $key => $value) {
                if (isset($this->_filtersValues[$field]) && $this->_filtersValues[$field] == $key) {
                    $selected = $key;
                }

                $values[$key] = $value;
            }

            if ($renderLoaded === false) {
                $render = $this->loadFilterRender('Select');
                $render->setView($this->getView());
                $renderLoaded = true;
            }

            $render->setValues($values);
            $render->setDefaultValue(isset($this->_filtersValues[$field]) ? $this->_filtersValues[$field] : '');
        }

        if ($tipo != 'invalid') {
            if ($renderLoaded === false) {
                $render = $this->loadFilterRender('Text');
                $render->setView($this->getView());
                $renderLoaded = true;
            }

            $render->setDefaultValue(isset($this->_filtersValues[$field]) ? $this->_filtersValues[$field] : '');
        }

        if (isset($this->_filtersValues[$field]) && is_array($this->_filtersValues[$field])) {
            foreach ($this->_filtersValues[$field] as $key => $value) {
                $render->setDefaultValue($value, $key);
            }
        }

        $render->setFieldName($field);
        $render->setAttributes($attr);
        $render->setTranslator($this->getTranslator());

        return $render->render();
    }

    /**
     * Returns all fields ids
     *
     * @return array
     */
    public function getAllFieldsIds()
    {
        $fields = array();
        foreach ($this->_filters as $key => $filter) {
            if (is_array($filter) && isset($filter['render'])) {
                $render = $this->loadFilterRender($filter['render']);
                $fields[$key] = $render->getChilds();
            } else {
                $fields[$key] = $key;
                $render = false;
            }
        }

        return $fields;
    }

    /**
     * Apply config options
     *
     * @param array $options   Array of options to apply
     * @param bool  $firstCall If this is the first time this metod is being called
     *
     * @return void
     */
    protected function _applyConfigOptions($options, $firstCall = false)
    {
        $this->_deployOptions = $options;

        if (isset($this->_deployOptions['templateDir'])) {
            $this->_deployOptions['templateDir'] = (array) $this->_deployOptions['templateDir'];

            foreach ($this->_deployOptions['templateDir'] as $templates) {
                $temp = $templates;
                $temp = str_replace('_', '/', $temp);
                $this->addTemplateDir($temp, $templates, 'table');
            }
        }

        if ($firstCall === true && isset($this->_options['extra'])) {
            if (isset($this->_options['extra']['row']) && is_array($this->_options['extra']['row'])) {
                $rows = new Bvb_Grid_Extra_Rows();
                foreach ($this->_options['extra']['row'] as $key => $value) {
                    $value['name'] = $key;
                    $rows->addRow($value['position'], array($value));
                }
                $this->addExtraRows($rows);
            }
            if (isset($this->_options['extra']['column']) && is_array($this->_options['extra']['column'])) {

                $columns = array();
                foreach ($this->_options['extra']['column'] as $key => $value) {

                    $columns[] = new Bvb_Grid_Extra_Column($key, $value);
                }
                $this->addExtraColumns($columns);
            }
        }

        if (isset($this->_deployOptions['imagesUrl'])) {
            $this->setImagesUrl($this->_deployOptions['imagesUrl']);
        }

        if (isset($this->_deployOptions['template'])) {
            $this->setTemplate($this->_deployOptions['template'], 'table');
        }
    }

    /**
     * Returns form or subform instance
     *
     * @param mixed $subForm Subform name to return, Numeric value
     *
     * @return Bvb_Grid_Form
     */
    public function getForm($subForm = null)
    {
        if (!is_null($subForm))
            return $this->_form->getSubForm($subForm);

        return $this->_form;
    }

    /**
     * Adds a row class based on a condition
     *
     * @param string $condition Condition to apply
     * @param string $class     CSS class to apply if match
     * @param string $else      CSS class to apply if no match
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function addClassRowCondition($condition, $class, $else = '')
    {
        $this->_classRowCondition[] = array('condition' => $condition,
                                            'class' => $class,
                                            'else' => $else);
        return $this;
    }

    /**
     * Adds a cell class based on a condition
     *
     * @param string $column    Column Name
     * @param string $condition Condition to apply
     * @param string $class     CSS class to apply if match
     * @param string $else      CSS class to apply if no match
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function addClassCellCondition($column, $condition, $class, $else = '')
    {
        $this->_classCellCondition[$column][] = array('condition' => $condition,
                                                      'class' => $class,
                                                      'else' => $else);
        return $this;
    }

    /**
     * Returns current clss conditions for a given field
     *
     * @param string $column Field to obtain condition
     * @return mixed
     */
    public function getClassCellCondition($column)
    {
        return isset($this->_classCellCondition[$column])?$this->_classCellCondition[$column]:false;
    }

    /**
     * Sets a row class based on a condition
     *
     * @param string $condition Condition to apply
     * @param string $class     CSS class to apply if match
     * @param string $else      CSS class to apply if no match
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function setClassRowCondition($condition, $class, $else = '')
    {
        $this->clearClassRowConditions();
        $this->addClassRowCondition($condition, $class, $else);
        return $this;
    }

    /**
     * Returns current row class conditions
     *
     * @return array
     */
    public function getClassRowCondition()
    {
        return $this->_classRowCondition;
    }

    /**
     * Clears all row conditions
     *
     * @return Bvb_Grid_Deploy_Table
     *
     */
    public function clearClassRowConditions()
    {
        $this->_classRowCondition = array();
        return $this;
    }

    /**
     * Clears a given class cell condition
     *
     * @param string $cell Table cell to clear condition
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function clearClassCellConditions($cell)
    {
        $this->_classCellCondition[$cell] = array();
        return $this;
    }

    /**
     * Clears all class cels conditions
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function clearClassCellsConditions()
    {
        $this->_classCellCondition = array();
        return $this;
    }

    /**
     * Set a cell class based on a condition
     *
     * @param string $column    Column Name
     * @param string $condition Condition to apply
     * @param string $class     CSS class if match
     * @param string $else      CSS class to aply if not match
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function setClassCellCondition($column, $condition, $class, $else='')
    {
        $this->clearClassRowConditions();
        $this->_classCellCondition[$column][] = array('condition' => $condition,
                                                      'class' => $class,
                                                      'else' => $else);
        return $this;
    }

    /**
     * Build extra rows
     *
     * @param string $position Row position to build
     *
     * @return mixed
     */
    protected function _buildExtraRows($position)
    {
        if (count($this->_extraRows) == 0) {
            return false;
        }

        $result = '';
        foreach ($this->_extraRows as $key => $value) {
//            if (count($this->_getExtraFields('left')) > 0) {
//                $result .= " <td colspan='" . count($this->_getExtraFields('left')) . "'></td>";
//            }
//
//            if (count($this->_getExtraFields('right')) > 0) {
//                $result .= " <td colspan='" . count($this->_getExtraFields('left')) . "'></td>";
//            }

            if ($value['position'] != $position)
                continue;

            foreach ($value['values'] as $final) {
                $colspan = isset($final['colspan']) ? "colspan='" . $final['colspan'] . "'" : '';
                $class = isset($final['class']) ? "class='" . $final['class'] . "'" : '';
                if (!isset($final['content'])) {
                    $final['content'] = '';
                }

                $result .= "<td $colspan $class >{$final['content']}</td>";
            }

            $result .= '</tr>';
        }

        return ($result != '') ? $result : false;
    }

    /**
     * Defines the default classes to be used on odd and even td
     *
     * @param string $odd  CSS class to show on odd rows
     * @param string $even CSS class to show on even rows
     *
     * @return Bvb_Grid_Deploy_Table
     *
     */
    public function setRowAltClasses($odd, $even = '')
    {
        $this->_cssClasses = array('odd' => $odd, 'even' => $even);
        return $this;
    }

    /**
     * Returns current classes for odd and even rows
     *
     * @return array
     */
    public function getRowAltClasses()
    {
        return $this->_cssClasses;
    }

    /**
     * So user can know what is going to be done
     *
     * @return void
     */
    public function buildFormDefinitions()
    {
        if ($this->getParam('add')) {
            $this->_formSettings['mode'] = 'add';
            $this->_formSettings['action'] = $this->getForm()->getAction();
        }

        if ($this->getParam('edit')) {
            $this->_formSettings['mode'] = 'edit';
            $this->_formSettings['id'] = $this->getIdentifierColumnsFromUrl();
            $this->_formSettings['row'] = $this->getSource()->fetchDetail($this->getIdentifierColumnsFromUrl());
            $this->_formSettings['action'] = $this->getForm()->getAction();
        }

        if ($this->getParam('delete')) {
            $this->_formSettings['mode'] = 'delete';
            $this->_formSettings['id'] = $this->getIdentifierColumnsFromUrl();
            $this->_formSettings['row'] = $this->getSource()->fetchDetail($this->getIdentifierColumnsFromUrl());
            $this->_formSettings['action'] = $this->getForm()->getAction();
        }
    }

    /**
     * Return actions from the form
     *
     * @return array
     */
    public function getFormSettings()
    {
        $this->buildFormDefinitions();
        return $this->_formSettings;
    }

    /**
     * Show a confirmation page instead a alert window
     *
     * @param bool $status Set to true to show a pdelete confirmation page
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function setDeleteConfirmationPage($status)
    {
        $this->_deleteConfirmationPage = (bool) $status;
        return $this;
    }

    /**
     * Defines Images location
     *
     * @param string $url The relative url where images to be used in table are located
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function setImagesUrl($url)
    {
        if (!is_string($url)) {
            throw new Bvb_Grid_Exception('String expected, ' . gettype($url) . ' provided');
        }
        $this->_imagesUrl = $url;
        return $this;
    }

    /**
     * Returns the actual URL images location
     *
     * @return string
     */
    public function getImagesUrl()
    {
        return $this->_imagesUrl;
    }

    /**
     * Always show arrows on all fields or show only when a field
     * is sorted
     *
     * @param bool $status Set to true to always show order images, instead of only the field is sorted
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function setAlwaysShowOrderArrows($status)
    {
        $this->_alwaysShowOrderArrows = (bool) $status;
        return $this;
    }

    /**
     * Returns true if we should always show order arrows
     *
     * @return bool
     */
    public function getAlwaysShowOrderArrows()
    {
        return $this->_alwaysShowOrderArrows;
    }

    /**
     * Returns any erros from form validation
     *
     * @return mixed
     */
    public function getFormErrorMessages()
    {
        return isset($this->_gridSession->errors) ? $this->_gridSession->errors : false;
    }

    /**
     * If we should use onclick, and onkeyup instead a button over the filters
     *
     * @param bool $flag Set to true to use onchange events
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function setUseKeyEventsOnFilters($flag)
    {
        $this->_useKeyEventsOnFilters = (bool) $flag;
        return $this;
    }

    /**
     * Returns true if we should use the onchange event
     * when filtering results
     *
     * @return bool
     */
    public function getUseKeyEventsOnFilters()
    {
        return $this->_useKeyEventsOnFilters;
    }

    /**
     * Sets if we should show ordering images next to columns titles
     *
     * @param bool $status Set to true to show order images
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function setShowOrderImages($status = true)
    {
        $this->_showOrderImages = (bool) $status;
        return $this;
    }

    /**
     * Returns true if we should show the order images next to column titles
     *
     * @return bool
     */
    public function getShowOrderImages()
    {
        return $this->_showOrderImages;
    }

    /**
     * Builds Mass Actions
     *
     * @return string
     */
    protected function _buildMassActions()
    {
        if (!$this->getMassActions()->hasMassActions())
            return false;

        $pk = $this->_getMassActionsDecorator();


        $left = new Bvb_Grid_Extra_Column();

        $decorator = "<input type='checkbox' onclick='observeCheckBox_" . $this->getGridId() . "(this)' "
                   . "id='massCheckBox_" . $this->getGridId() . "' value='" . $pk . "' />";

        $left->position('left')
            ->title('')
            ->name('ZFG_MASS_ACTIONS')
            ->decorator($decorator);

        $this->addExtraColumns($left);

        $select = array();
        foreach ($this->getMassActions()->getMassActionsOptions() as $value) {
            $select[$value['url']] = $value['caption'];
        }


        $formSubmitOptions = array_merge(array('onClick' => "return convertArrayToInput_" . $this->getGridId() . "()"),
                $this->getMassActions()->getSumitAttributes());


        $formSelect = $this->getView()->formSelect("gridAction_" . $this->getGridId(), null, array(), $select);
        $formSubmit = $this->getView()->formSubmit(
            "send_" . $this->getGridId(),
            $this->__('Submit'),
            $formSubmitOptions
        );

        if ($this->getRecordsPerPage() < $this->getTotalRecords()) {
            $currentRecords = $this->getRecordsPerPage();
        } else {
            $currentRecords = $this->getTotalRecords();
        }

        $ids = $this->getSource()->getMassActionsIds(
                $this->_data['table'],
                $this->getMassActions()->getFields(),
                $this->getMassActions()->getMultipleFieldsSeparator());


        $cssClasses = $this->getTemplateParams();

        if (!isset($cssClasses['cssClass'])) {
            $cssClasses['cssClass']='';
        }

        $cssClasses = $cssClasses['cssClass'];
        $cssClasses['massActions'] = isset($cssClasses['massActions']) ? " class='{$cssClasses['massActions']}'" : '';
        $cssClasses['massSelect'] = isset($cssClasses['massSelect']) ? " class='{$cssClasses['massSelect']}'" : '';


        $return = "<tr><td " . $cssClasses['massActions'] . " colspan=" . $this->_colspan . ">"
                . "<form style=\"padding:0;margin:0;\" method=\"post\" action=\"\" "
                . " id=\"massActions_{$this->getGridId()}\" name=\"massActions_{$this->getGridId()}\">"
                . $this->getView()->formHidden('massActionsAll_' . $this->getGridId(), $ids)
                . $this->getView()->formHidden('postMassIds' . $this->getGridId(), '')
                . "<span " . $cssClasses['massSelect'] . ">"
                . "<a href='#' onclick='checkAll_" . $this->getGridId() . ""
                . "(document.massActions_" . $this->getGridId() . ".gridMassActions_" . $this->getGridId() . ","
                . "{$this->getTotalRecords()},1);return false;'>" . $this->__('Select All') . "</a> | "
                . "<a href='#' onclick='checkAll_" . $this->getGridId() . ""
                . "(document.massActions_" . $this->getGridId() . ".gridMassActions_" . $this->getGridId() . ","
                . "{$currentRecords},0);return false;'>" . $this->__('Select Visible') . "</a> | "
                . "<a href='#' onclick='uncheckAll_" . $this->getGridId() . ""
                . "(document.massActions_" . $this->getGridId() . ".gridMassActions_" . $this->getGridId() . ",0); "
                . "return false;'>" . $this->__('Unselect All') . "</a> | <strong>"
                . "<span id='massSelected_" . $this->getGridId() . "'>0</span></strong> "
                . $this->__('items selected') . "</span> " . $this->__('Actions') . ": $formSelect $formSubmit"
                . "</form></td></tr>";

        return $return;
    }

    /**
     * If we should show the detail column
     *
     * @param bool $flag Show or not detail column
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function setShowDetailColumn($flag)
    {
        $this->_showDetailColumn = (bool) $flag;
        return $this;
    }

    /**
     * This options enables you to start the grid at a specified record.
     * Grid will look for the specified value and will position the grid at the correspondent page
     *
     * @param String $recordId Column identifier value to place the initial page at
     * @param string $rowClass Css class to apply at specified row
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function placePageAtRecord($recordId, $rowClass = '')
    {
        $this->_recordPage = array('id' => $recordId, 'class' => $rowClass);
        return $this;
    }

    /**
     * Returns the current option for place page at record
     *
     * @return mixed
     */
    public function getPlacePageAtRecord()
    {
        return $this->_recordPage;
    }

    /**
     * Builds the place page at record option
     *
     * @return Bvb_Grid_Deploy_Table
     */
    protected function _placePageAtRecord()
    {
        if ($this->getParam('start') !== false
            || $this->getParam('order')
            || $this->getParam('noOrder')
            || !isset($this->_recordPage['id'])
        ) {
            return;
        }

        if (count(array_intersect(array_flip($this->getParams()), $this->getFields())) > 0) {
            return;
        }

        $pk = $this->getSource()->getIdentifierColumns($this->_data['table']);
        $fieldAlias = $this->getFieldAlias($pk[0]);

        $r = $this->getSource()->execute();
        $totalRecords = count($r);
        $i = 1;
        foreach ($r as $record) {
            if ($record[$fieldAlias] == $this->_recordPage['id']) {
                unset($r);
                break;
            }
            $i++;
        }


        $page = floor($i / $this->getRecordsPerPage());
        $start = $this->getRecordsPerPage() * $page;

        if ($start > $totalRecords) {
            $start = 0;
        }

        $this->setParam('start', $start);
        $this->addClassRowCondition("'{{{$fieldAlias}}}' == '{$this->_recordPage['id']}'", $this->_recordPage['class']);

        return $this;
    }

    /**
     * Sets info about ajax.
     *
     * @param bool|string $id Ajax id, or false to not use ajax
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function setAjax($id)
    {
        $this->_info['ajax'] = $id;
        if (!$this->getGridId()) {
            $this->setGridId($id);
        }

        return $this;
    }

    /**
     * Checks if ajax is in use
     *
     * @return bool
     */
    public function getAjax()
    {
        return isset($this->_info['ajax']) ? $this->_info['ajax'] : false;
    }

    /**
     * Returns current CRUD table
     *
     * @return string
     */
    public function getCrudTable()
    {
        return $this->_crudTable;
    }

    /**
     * Returns current Bvb_Grid_Form instance
     *
     * @return Bvb_Grid_Form
     */
    public function getBvbForm()
    {
        return $this->_bvbForm;
    }

    /**
     * Set's the table title when viewing record details
     *
     * @param string $title
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function setDetailViewTitle($title)
    {
        $this->_detailViewTitle = $title;
        return $this;
    }

    /**
     * Returns current table title when viewing record details
     *
     * @return Bvb_Grid_Deploy_Table
     */
    public function getDetailViewTitle()
    {
        return $this->_detailViewTitle;
    }

    /**
     * Defines the label for the link to return to list view when in detail view
     *
     * @param strin $label
     * @return Bvb_Grid_Deploy_Table
     */
    public function setDetailViewReturnLabel($label)
    {
        $this->_detailViewReturnLabel = $label;
        return $this;
    }

    /**
     * Returns current label when in detail view to return to list view
     *
     * @return string
     */
    public function getDetailViewReturnLabel()
    {
        return $this->_detailViewReturnLabel;
    }


    /**
     * Sets crud columns positions (delete, edit)
     *
     * @param string $positon
     * @return Bvb_Grid
     */
    public function setCrudColumnsPosition($positon = 'left')
    {
        if ($positon != 'left' && $positon != 'right') {
            throw new Bvb_Grid_Exception('Please use left or right');
        }

        $this->_crudColumnsPosition = $positon;
        return $this;
    }

    /**
     * Returns current position for crud aux columns
     *
     * @return string
     */
    public function getCrudColumnsPosition()
    {
        return $this->_crudColumnsPosition;
    }

    /**
     * In some cases we don't need to execute costly query on data source on grid deployment
     *
     * @return bool
     */
    protected function _deployNeedsData()
    {
        return in_array('listing', $this->getWillShow()) || $this->getInfo("doubleTables") == 1;
    }


    /**
     * Let the user know what will be displayed.
     *
     * @return array
     */
    public function getWillShow()
    {
        return $this->_willShow;
    }
}
