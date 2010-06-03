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

class Bvb_Grid_Deploy_Table extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{

    const OUTPUT = 'table';

    /**
     * Hold definitions from configurations
     * @var array
     */
    public $deploy = array();


    protected $_deployOptions = null;

    /**
     * Information about the template
     *
     * @var array|empty
     */

    public $templateInfo;

    /**
     * If the form has been submitted
     *
     * @var bool
     */
    protected $formPost = 0;

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
     * Permission to add records
     *
     * @var array
     */
    private $allowAdd = null;

    /**
     * Permission to edit records
     *
     * @var array
     */
    private $allowEdit = null;

    /**
     * Permission to delete records
     *
     * @var array
     */
    private $allowDelete = null;

    /**
     * Override the form presentation
     *
     * @var bool
     */
    protected $_editNoForm;

    /**
     * Images url for export
     *
     * @var string
     */
    protected $_imagesUrl;

    /**
     * If we are allowed to add records to the database if we
     * show two tables (the form and the grid) or just one
     *
     * @var bool
     */
    protected $double_tables = 0;

    /**
     * Set if form validation failed
     *
     * @var bool
     */
    protected $_failedValidation;


    /**
     * Callback to be called after crud operation update
     * @var unknown_type
     */
    protected $_callbackAfterUpdate = null;

    /**
     * Callback to be called after crud operation delete
     * @var unknown_type
     */
    protected $_callbackAfterDelete = null;

    /**
     * Callback to be called after crud operation insert
     * @var unknown_type
     */
    protected $_callbackAfterInsert = null;

    /**
     * Callback to be called Before crud operation update
     * @var unknown_type
     */
    protected $_callbackBeforeUpdate = null;

    /**
     * Callback to be called Before crud operation delete
     * @var unknown_type
     */
    protected $_callbackBeforeDelete = null;

    /**
     * Callback to be called Before crud operation insert
     * @var unknown_type
     */
    protected $_callbackBeforeInsert = null;

    /**
     * Contains result of deploy() function.
     *
     * @var string
     */
    protected $_deploymentContent = null;


    /**
     * String containg the inputs ids for fitlers
     * @var array
     */
    protected $_javaScriptHelper = array();

    /**
     * Url param with the information about removing records
     *
     * @var string
     */
    protected $_comm;

    /**
     * IF user has defined mass actions operations
     * @var bool
     */
    protected $_hasMassActions = false;


    protected $_massActions = false;

    /**
     *
     * @var Zend_Form
     */
    protected $_form;

    /**
     * The table where crud operations
     * should be performed.
     * by default the table is fetched from the quaery
     * but the user can set other manually
     * @var unknown_type
     */
    protected $_crudTable;


    /**
     * Options for CRUD operations
     * @var unknown_type
     */
    protected $_crudOptions = array();


    /**
     * If data should be saved or not into the source
     * @var unknown_type
     */
    protected $_crudTableOptions = array('add' => 1, 'edit' => 1, 'delete' => 1);

    /**
     *
     * @var Zend_Session_Abstract
     */
    protected $_gridSession = null;


    /**
     * Whether to use or not key events for filters
     * @var unknown_type
     */
    protected $_useKeyEventsOnFilters = false;

    /**
     * Extra Rows
     * @var unknown_typearray
     */
    protected $_extraRows = array();

    /**
     * An array with all the parts that can be rendered
     * even
     * @var unknown_type
     */
    protected $_render = array();

    /**
     * An array with all parts that will be rendered
     * @var array
     */
    protected $_renderDeploy = array();


    /**
     * CSS classes to be used
     * @var array
     */
    protected $_cssClasses = array('odd' => 'alt', 'even' => '');

    /**
     * Definitions from form
     * May contain data being edited, what operation is beiing performed
     * @var array
     */
    protected $_formSettings = array();

    /**
     * If the user should be redirected to a confirmation page
     * before a record being deleted or if there should be a popup
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
     * To edit, add, or delete records, a user must be authenticated, so we instantiate
     * it here.
     *
     * @param array $data
     */
    public function __construct ($options)
    {
        $this->_setRemoveHiddenFields(true);

        parent::__construct($options);

        if ( isset($this->_options['grid']['id']) ) {
            $this->setGridId($this->_options['grid']['id']);
        }

        $this->_gridSession = new Zend_Session_Namespace('Bvb_Grid_' . $this->getGridId());
        $this->addTemplateDir('Bvb/Grid/Template/Table', 'Bvb_Grid_Template_Table', 'table');

        if($this->getRequest()->isPost() && $this->getRequest()->getPost('postMassIds'))
        {
            $this->_redirect($this->getUrl());
            die();
        }

    }



    /**
     *
     * Process all information forms related
     * First we check for permissions to add, edit, delete
     * And then the request->isPost. If true we process the data
     *
     */

    protected function _processForm ()
    {

        if ( ! $this->getSource()->hasCrud() ) {
            return false;
        }

        if ( $this->getInfo("add,allow") == 1 ) {
            $this->allowAdd = 1;
        }

        if ( $this->getInfo("delete,allow") == 1 ) {
            $this->allowDelete = 1;
        }

        if ( $this->getInfo("edit,allow") == 1 ) {
            $this->allowEdit = 1;
        }

        if ( $this->allowEdit == 1 || $this->allowDelete == 1 ) {
            $dec = $this->getParam('comm');
            $this->_comm = $dec;
        }

        /**
         * Remove if there is something to remove
         */
        if ( $this->allowDelete == 1 ) {
            self::_deleteRecord($dec);
        }

        if ( $this->allowAdd == 1 || $this->allowEdit == 1 ) {
            $opComm = $this->getParam('comm');

            $mode = $this->getParam('edit') ? 'edit' : 'add';

            $queryUrl = $this->getPkFromUrl();


            if ( ! $this->getRequest()->isPost()  || ($this->getParam('zfmassedit') && $this->getRequest()->isPost() ) ) {

                foreach ( $this->_form->getSubForms() as $key => $form ) {

                    foreach ( array_keys($form->getElements()) as $element ) {

                        if ( $this->_gridSession->noErrors !== true ) {
                            if ( isset($this->_gridSession->errors[$key][$element]) ) {
                                $form->getElement($element)->setErrors($this->_gridSession->errors[$key][$element]);
                            }
                        }
                        if ( isset($this->_gridSession->post[$key][$element]) ) {
                            $form->getElement($element)->setValue($this->_gridSession->post[$key][$element]);
                        }
                    }
                }


                if ( $this->getParam('add') == 1 ) {
                    $this->_willShow['form'] = true;
                    $this->_willShow['formAdd'] = true;
                }

                if ( $mode == 'edit' ) {

                    $this->_willShow['form'] = true;
                    $this->_willShow['formEdit'] = true;
                    $this->_willShow['formEditId'] = $this->getPkFromUrl();

                    if ( $this->getParam('postMassIds') ) {

                        $ids = explode(',', $this->getParam('postMassIds'));
                        $pkParentArray = $this->getSource()->getPrimaryKey($this->_data['table']);

                        $a = 1;
                        foreach ( $ids as $value ) {

                            if ( strpos($value, '-') ) {

                                $allIds = explode('-', $value);
                                $i = 0;
                                foreach ( $allIds as $fIds ) {
                                    $conditions[$a][$pkParentArray[$i]] = $fIds;
                                    $i ++;
                                    $a ++;
                                }

                            } else {
                                $conditions[$a][$pkParentArray[0]] = $value;
                                $a++;
                            }
                        }

                    } else {
                        $conditions[1] = $this->getPkFromUrl();
                    }



                    for ( $i = 1; $i <= count($conditions); $i ++ ) {


                        $r = $this->getSource()->getRecord($this->_crudTable, $conditions[$i]);

                        if ( $r === false && count($conditions)==1 ) {
                            $this->_gridSession->message = $this->__('Record Not Found');
                            $this->_gridSession->_noForm = 1;
                            $this->_gridSession->correct = 1;
                            $this->_redirect($this->getUrl(array('comm', 'gridRemove', 'gridDetail', 'edit')));
                        }


                        if ( is_array($r) ) {

                            foreach ( $r as $key => $value ) {

                                $pk = explode('.', key($conditions[$i]));
                                if ( $key == end($pk) ) {
                                    $this->getForm($i)->getElement('ZFPK')->setValue($value);
                                }

                                $isField = $this->getForm($i)->getElement($key);

                                if ( isset($isField) ) {


                                    if ( isset($this->_data['fields'][$key]) ) {
                                        $fieldType = $this->getSource()->getFieldType($this->_data['fields'][$key]['field']);
                                    } else {
                                        $fieldType = 'text';
                                    }

                                    if ( isset($this->_gridSession->post) && is_array($this->_gridSession->post) ) {
                                        if ( isset($this->_gridSession->post[$i][$key]) ) {
                                            $this->getForm($i)->getElement($key)->setValue($this->_gridSession->post[$i][$key]);
                                        }
                                    } else {
                                        $this->getForm($i)->getElement($key)->setValue($value);
                                    }


                                }
                            }
                        }
                    }
                }
            }
        }



        //Check if the request method is POST
        if ( $this->getRequest()->isPost()
            && $this->getRequest()->getPost('zfg_form_edit' . $this->getGridId()) == 1 ) {

            foreach ( $this->_form->getSubForms() as $key => $form ) {
                if (isset( $_POST[$key]['ZFIGNORE']) && $_POST[$key]['ZFIGNORE'] == 1 ) {
                    $this->_form->removeSubForm($key);
                }
            }

            if(count($this->_form->getSubForms())==0)
            {
                $this->_redirect($this->getUrl(array('zfg_csrf','add','zfg_form_edit','form_submit')));
            }

            if ( $this->_form->isValid($_POST) ) {


                $post = array();

                foreach ( $this->_form->getSubForms() as $key => $value ) {


                    foreach ( $value->getElements() as $el ) {
                        $post[$key][$el->getName()] = is_array($el->getValue()) ? implode(',', $el->getValue()) : $el->getValue();
                    }

                    $addNew = false;

                    if ( isset($post['saveAndAdd' . $this->getGridId()]) ) {
                        $this->_gridSession->noErrors = true;
                        $addNew = true;
                    }


                    unset($post[$key]['ZFIGNORE']);
                }


                unset($post['form_submit' . $this->getGridId()]);
                unset($post['zfg_form_edit' . $this->getGridId()]);
                unset($post['form_reset' . $this->getGridId()]);
                unset($post['zfg_csrf' . $this->getGridId()]);
                unset($post['saveAndAdd' . $this->getGridId()]);

                $param = Zend_Controller_Front::getInstance()->getRequest();

                // Process data
                if ( $mode == 'add' ) {

                    try {

                        foreach ( $this->_form->getSubForms() as $key => $value ) {

                            if($this->_crud->getUseVerticalInputs()===false && $key==0)
                            {
                                continue;
                            }


                            $sendCall = array(&$post[$key], $this->getSource());

                            if ( null !== $this->_callbackBeforeInsert ) {
                                call_user_func_array($this->_callbackBeforeInsert, $sendCall);
                            }


                            if ( $this->_crudTableOptions['add'] == true ) {
                                $post[$key] = array_merge($post[$key], $this->_crudOptions['addForce']);
                                $sendCall[] = $this->getSource()->insert($this->_crudTable, $post[$key]);
                            }


                            if ( null !== $this->_callbackAfterInsert ) {
                                call_user_func_array($this->_callbackAfterInsert, $sendCall);
                            }


                            unset($this->_gridSession->post[$key]);
                        }

                        $this->_gridSession->message = $this->__('Record saved');
                        $this->_gridSession->messageOk = true;

                        if ( isset($post['saveAndAdd' . $this->getGridId()]) ) {
                            $this->_gridSession->_noForm = 0;
                        } else {
                            $this->_gridSession->_noForm = 1;
                        }

                        $this->_gridSession->correct = 1;


                        $this->_removeFormParams( array('add' . $this->getGridId()=>'1'));

                        if ( $addNew === true ) {
                            $finalUrl = '/add' . $this->getGridId() . '/1';
                        } else {
                            $finalUrl = '';
                        }

                        $this->_redirect($this->getUrl() . $finalUrl);

                        die();

                    }
                    catch (Zend_Exception $e) {
                        $this->_gridSession->messageOk = FALSE;
                        $this->_gridSession->message = $this->__('Error saving record: ') . $e->getMessage();
                        $this->_gridSession->formSuccess = 0;
                        $this->_gridSession->formPost = 1;
                        $this->_gridSession->_noForm = 0;
                        $this->_gridSession->correct = 0;

                        $this->_removeFormParams();
                        $this->_redirect($this->getUrl());
                    }

                }

                // Process data
                if ( $mode == 'edit' ) {

                    try {

                        foreach ( $this->_form->getSubForms() as $key => $value ) {

                            if($this->_crud->getUseVerticalInputs()===false && $key==0)
                            {
                                continue;
                            }


                            $sendCall = array(&$post[$key], $this->getSource());

                            $pks = $this->getSource()->getPrimaryKey($this->_data['table']);

                            if ( isset($post[$key]['ZFPK']) ) {

                                if ( strpos($post[$key]['ZFPK'], '-') ) {

                                    $allIds = explode('-', $post[$key]['ZFPK']);
                                    $i = 0;
                                    foreach ( $allIds as $fIds ) {
                                        $condition[$pks[$i]] = $fIds;
                                        $i ++;
                                    }

                                } else {
                                    $condition[$pks[0]] = $post[$key]['ZFPK'];
                                }

                                $queryUrl = $condition;

                                unset($post[$key]['ZFPK']);
                            }


                            if ( null !== $this->_callbackBeforeUpdate ) {
                                call_user_func_array($this->_callbackBeforeUpdate, $sendCall);
                            }

                            if ( $this->_crudTableOptions['edit'] == true ) {
                                $post[$key] = array_merge($post[$key], $this->_crudOptions['editForce']);
                                $queryUrl = array_merge($queryUrl, $this->_crudOptions['editAddCondition']);
                                $this->getSource()->update($this->_crudTable, $post[$key], $queryUrl);
                            }


                            if ( null !== $this->_callbackAfterUpdate ) {
                                call_user_func_array($this->_callbackAfterUpdate, $sendCall);
                            }
                        }

                        $this->_gridSession->message = $this->__('Record saved');
                        $this->_gridSession->messageOk = true;

                        $this->_gridSession->_noForm = 1;

                        $this->_gridSession->correct = 1;

                        unset($this->_gridSession->post);

                        $this->_removeFormParams(array('comm' . $this->getGridId()=>'', 'edit' . $this->getGridId()=>'','zfmassedit'=>''));

                        $this->_redirect($this->getUrl());

                    }
                    catch (Zend_Exception $e) {
                        $this->_gridSession->messageOk = FALSE;
                        $this->_gridSession->message = $this->__('Error updating record: ') . $e->getMessage();
                        $this->_gridSession->formSuccess = 0;
                        $this->_gridSession->formPost = 1;
                        $this->_gridSession->_noForm = 0;
                        $this->_gridSession->correct = 0;

                        $this->_removeFormParams();
                        $this->_redirect($this->getUrl());
                    }
                }

            } else {

                $this->_gridSession->post = $_POST;

                foreach ( $this->_form->getSubForms() as $key => $value ) {
                    $this->_gridSession->errors[$key] = $value->getMessages();
                }

                $this->_gridSession->message = $this->__('Validation failed');
                $this->_gridSession->messageOk = false;
                $this->_gridSession->formSuccess = 0;
                $this->_gridSession->formPost = 1;
                $this->_gridSession->_noForm = 0;
                $this->_gridSession->correct = 0;
                $this->_removeFormParams();

                #$this->_redirect($this->getUrl());
            }

        }

    }


    /**
     * Remove unneeded form inputs
     * @param  $post
     * @param  $extra
     */
    protected function _removeFormParams ( $extra = array())
    {

        $post = (array) array_flip(array_keys($this->_form->getSubForms()));

        $extra = array_merge($extra,array('massActionsAll_'=>'','postMassIds'=>'','send_'=>'','gridAction_'=>''));

        if ( count($extra) > 0 ) {
             foreach ( $extra as $key=>$value ) {
                $this->removeParam($key);
            }
        }

        if($this->getRequest()->isPost())
        {
             foreach ( $_POST as $key => $value ) {
                $this->removeParam($key);
            }
        }

        foreach ( $post as $key => $value ) {
            $this->removeParam($key);
        }

        $this->removeParam('saveAndAdd' . $this->getGridId());
        $this->removeParam('form_submit' . $this->getGridId());
        $this->removeParam('zfg_form_edit' . $this->getGridId());
        $this->removeParam('zfg_csrf' . $this->getGridId());


        return true;
    }


    /**
     * Remove the record from the table
     *
     * @param string $sql
     * @param string $user
     * @return string
     */
    protected function _deleteRecord ($sql)
    {

        if ( $this->getParam('postMassIds') && $this->getParam('zfmassremove' . $this->getGridId()) == 1) {

            //ID's to remove
            $ids = explode(',', $this->getParam('postMassIds'));

            //Lets get PK'/**
            $pkParentArray = $this->getSource()->getPrimaryKey($this->_data['table']);
            foreach ( $ids as $value ) {

                $condition = array();

                if ( strpos($value, '-') ) {

                    $allIds = explode('-', $value);
                    $i = 0;
                    foreach ( $allIds as $fIds ) {
                        $condition[$pkParentArray[$i]] = $fIds;
                        $i ++;
                    }

                } else {
                    $condition[$pkParentArray[0]] = $value;
                }

                try {

                    $sendCall = array(&$condition, $this->getSource());

                    if ( null !== $this->_callbackBeforeDelete ) {
                        call_user_func_array($this->_callbackBeforeDelete, $sendCall);
                    }


                    if ( $this->_crudTableOptions['delete'] == true ) {
                        $condition = array_merge($condition, $this->_crudOptions['deleteAddCondition']);
                        $resultDelete = $this->getSource()->delete($this->_crudTable, $condition);
                    }

                    if ( $resultDelete == 1 ) {
                        if ( null !== $this->_callbackAfterDelete ) {
                            call_user_func_array($this->_callbackAfterDelete, $sendCall);
                        }
                    }


                } catch (Exception $e) {
                    $this->_gridSession->correct = 1;
                    $this->_gridSession->messageOk = FALSE;
                    $this->_gridSession->message = $this->__('Error deleting record: ') . $e->getMessage();
                }

            }

            $this->_gridSession->messageOk = true;
            $this->_gridSession->message = $this->__('Record deleted');
            $this->_gridSession->correct = 1;

            $this->_removeFormParams($_POST);

            $this->_redirect($this->getUrl(array('comm','zfmassremove','postMassIds')));

        } else {


            if ( strpos($sql, ';') === false ) {
                return false;
            }

            $param = explode(";", $sql);

            foreach ( $param as $value ) {
                $dec = explode(":", $value);
                $final[$dec[0]] = $dec[1];
            }

            if ( $final['mode'] != 'delete' ) {
                return 0;
            }


            $condition = $this->getPkFromUrl();
        }


        try {

            $sendCall = array(&$condition, $this->getSource());

            if ( null !== $this->_callbackBeforeDelete ) {
                call_user_func_array($this->_callbackBeforeDelete, $sendCall);
            }

            if ( $this->_crudTableOptions['delete'] == true ) {

                $condition = array_merge($condition, $this->_crudOptions['deleteAddCondition']);
                $resultDelete = $this->getSource()->delete($this->_crudTable, $condition);
            }

            if ( $resultDelete == 1 ) {
                if ( null !== $this->_callbackAfterDelete ) {
                    call_user_func_array($this->_callbackAfterDelete, $sendCall);
                }
            }

            $this->_gridSession->messageOk = true;
            $this->_gridSession->message = $this->__('Record deleted');
            $this->_gridSession->correct = 1;

            $this->_redirect($this->getUrl('comm'));

        }
        catch (Exception $e) {
            $this->_gridSession->correct = 1;
            $this->_gridSession->messageOk = FALSE;
            $this->_gridSession->message = $this->__('Error deleting record: ') . $e->getMessage();
        }

        $this->removeParam('comm' . $this->getGridId());

        return true;
    }


    /**
     * Build the first line of the table (Not the TH )
     *
     * @return string
     */
    protected function _buildHeader ()
    {

        $url = $this->getUrl(array('comm', 'edit', 'filters', 'order'));

        $final = '';
        $final1 = '';

        if ( $this->getSource()->hasCrud() ) {
            $this->_render['addButton'] = "<div class=\"addRecord\" ><a href=\"$url/add" . $this->getGridId() . "/1\">" . $this->__('Add Record') . "</a></div>";
            if ( ($this->getInfo('doubleTables') == 0 && $this->getParam('add') != 1 && $this->getParam('edit') != 1) && $this->getSource()->getPrimaryKey($this->_data['table']) && $this->getInfo('add,allow') == 1 && $this->getInfo('add,button') == 1 && $this->getInfo('add,noButton') != 1 ) {
                $this->_renderDeploy['addButton'] = $this->_render['addButton'];
            }
        }

        /**
         * We must check if there is a filter set or an order, to show the extra th on top
         */


        if ( count($this->_filters)>0 && ($this->getInfo('noOrder')!=1 && $this->getInfo('noFilters')!=1 ) ) {

            $url = $this->getUrl('filters', 'nofilters');
            $url2 = $this->getUrl(array('order', 'noOrder'));
            $url3 = $this->getUrl(array('filters', 'order', 'noFilters', 'noOrder'));

            if ( is_array($this->_defaultFilters) ) {
                $url .= '/nofilters'.$this->getGridId().'/1';
                $url3 .= '/nofilters'.$this->getGridId().'/1';
            }

            if ( is_array($this->getSource()->getSelectOrder()) ) {

                $url3 .= '/noOrder'.$this->getGridId().'/1';
                $url2 .= '/noOrder'.$this->getGridId().'/1';
            }

            $this->_temp['table']->hasExtraRow = 1;

            //Filters and order
            if ( $this->getParam('order') && ! $this->getParam('noOrder') && count($this->_filtersValues)>0) {
                if ( $this->getInfo("ajax") !== false ) {

                    $final1 = "<button href=\"gridAjax('{$this->getInfo("ajax")}','" . $url . "')\">" . $this->__('Remove Filters') . "</button><button onclick=\"gridAjax('{$this->getInfo("ajax")}','" . $url2 . "')\">" . $this->__('Remove Order') . "</button><button onclick=\"gridAjax('{$this->_info['ajax']}','" . $url3 . "')\">" . $this->__('Remove Filters and Order') . "</button>";

                } else {
                    $final1 = "<button onclick=\"window.location='$url'\">" . $this->__('Remove Filters') . "</button><button onclick=\"window.location='$url2'\">" . $this->__('Remove Order') . "</button><button onclick=\"window.location='$url3'\">" . $this->__('Remove Filters and Order') . "</button>";
                }
                //Only filters
            } elseif (  (! $this->getParam('order') || $this->getParam('noOrder')) && count($this->_filtersValues)>0 ) {

                if ( $this->getInfo("ajax") !== false ) {

                    $final1 = "<button onclick=\"gridAjax('{$this->getInfo("ajax")}','" . $url . "') \">" . $this->__('Remove Filters') . "</button>";

                } else {
                    $final1 = "<button onclick=\"window.location='$url'\">" . $this->__('Remove Filters') . "</button>";
                }

            //Only order
            } elseif ( count($this->_filtersValues)==0 && ($this->getParam('order') && ! $this->getParam('noOrder') && $this->getInfo('noOrder') != 1) ) {

                if ( $this->getInfo("ajax") !== false ) {
                    $final1 = "<button onclick=\"gridAjax('{$this->getInfo("ajax")}','" . $url2 . "') \">" . $this->__('Remove Order') . "</button>";
                } else {
                    $final1 = "<button onclick=\"window.location='$url2'\">" . $this->__('Remove Order') . "</button>";
                }
            }

            //Replace values
            if (  ( $this->getParam('noFilters') != 1 && $this->getInfo('noOrder') != 1) && ($this->getParam('add')!=1 && $this->getParam('edit')!=1) ) {


                if ( strlen($final1) > 5 || $this->getUseKeyEventsOnFilters() ==false ) {

                    if ( $this->getUseKeyEventsOnFilters() === false ) {
                        $final1 .= "<button onclick=\"" . $this->getGridId() . "gridChangeFilters(1)\">" . $this->__('Apply Filter') . "</button>";
                    }

                    $this->_render['extra'] = str_replace("{{value}}", $final1, $this->_temp['table']->extra());
                    $this->_renderDeploy['extra'] = str_replace("{{value}}", $final1, $this->_temp['table']->extra());

                }


            }


        //close cycle
        }

        return;
    }


    /**
     *
     * Build filters.
     *
     * We receive the information from an array
     * @param array $filters
     * @return unknown
     */
    protected function _buildFiltersTable ($filters)
    {

        //There are no filters.
        if ( ! is_array($filters) ) {
            $this->_temp['table']->hasFilters = 0;
            return '';
        }

        //Start the template
        $grid = $this->_temp['table']->filtersStart();

        foreach ( $filters as $filter ) {

            //Check extra fields
            if ( $filter['type'] == 'extraField' && $filter['position'] == 'left' ) {
                //Replace values
                $filterValue = isset($filter['value']) ? $filter['value'] : '';

                $grid .= str_replace('{{value}}', $filterValue . '&nbsp;', $this->_temp['table']->filtersLoop());
            }

            $hRowField = $this->getInfo("hRow,field") ? $this->getInfo("hRow,field") : '';

            //Check if we have an horizontal row
            if ( (isset($filter['field']) && $filter['field'] != $hRowField && $this->getInfo('hRow', 'title')) || ! $this->getInfo('hRow', 'title') ) {

                if ( $filter['type'] == 'field' ) {
                    //Replace values
                    $grid .= str_replace('{{value}}', $this->_formatField($filter['field']), $this->_temp['table']->filtersLoop());
                }
            }

            //Check extra fields from the right
            if ( $filter['type'] == 'extraField' && $filter['position'] == 'right' ) {
                $filter['value'] = isset($filter['value'])?$filter['value']:'';
                 $grid .= str_replace('{{value}}', $filter['value'], $this->_temp['table']->filtersLoop());
            }

        }

        //Close template
        $grid .= $this->_temp['table']->filtersEnd();

        return $grid;
    }


    /**
     * Build Table titles.
     *
     * @param array $titles
     * @return string
     */
    protected function _buildTitlesTable ($titles)
    {
        $orderField = null;

        if ( is_array($this->_order) ) {
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

        if ( $orderField === null ) {
            //Lets get the default order using in the query (Zend_Db)
            $queryOrder = $this->getSource()->getSelectOrder();

            if ( count($queryOrder) > 0 ) {
                $order = strtolower($queryOrder[1]) == 'asc' ? 'desc' : 'asc';
                $orderField = $queryOrder[0];
            }
        }

        if ( $this->getParam('noOrder') ) {
            $orderField = null;
        }

        foreach ( $titles as $title ) {


            //deal with extra field and template
            if ( $title['type'] == 'extraField' && $title['position'] == 'left' ) {
                $grid .= str_replace('{{value}}', $title['value'], $this->_temp['table']->titlesLoop());
            }

            $hRowTitle = $this->getInfo("hRow,field") ? $this->getInfo("hRow,field") : '';

            if ( (isset($title['field']) && $title['field'] != $hRowTitle && $this->getInfo("hRow,title")) || ! $this->getInfo("hRow,title") ) {

                if ( $title['type'] == 'field' ) {


                    $noOrder = $this->getInfo("noOrder") ? $this->getInfo("noOrder") : '';

                    if ( $noOrder == 1 ) {

                        //user set the noOrder(1) method
                        $grid .= str_replace('{{value}}', $this->__($title['value']), $this->_temp['table']->titlesLoop());

                    } else {

                        if ( ! isset($this->_data['fields'][$title['field']]['order']) ) {
                            $this->_data['fields'][$title['field']]['order'] = true;
                        }

                        if ( $this->getAlwaysShowOrderArrows() === false ) {
                            $imgF = explode('_', $this->getParam('order'));
                            $checkOrder = str_replace('_' . end($imgF), '', $this->getParam('order'));

                            if ( in_array(strtolower(end($imgF)), array('asc', 'desc')) && $checkOrder == $title['field'] ) {
                                $imgFinal = $images[strtolower(end($imgF))];
                            } else {
                                $imgFinal = '';
                            }
                        }

                        if ( $this->getInfo("ajax") !== false ) {


                            if ( $this->getAlwaysShowOrderArrows() === true ) {
                                $link1 = "<a  href=\"javascript:gridAjax('{$this->getInfo("ajax")}','{$title['simpleUrl']}/order{$this->getGridId()}/{$title['field']}_DESC')\">{$images['desc']}</a>";
                                $link2 = "<a  href=\"javascript:gridAjax('{$this->getInfo("ajax")}','{$title['simpleUrl']}/order{$this->getGridId()}/{$title['field']}_ASC')\">{$images['asc']}</a>";

                                if ( ($orderField == $title['field'] && $order == 'asc') || $this->_data['fields'][$title['field']]['order'] == 0 ) {
                                    $link1 = '';
                                }

                                if ( ($orderField == $title['field'] && $order == 'desc') || $this->_data['fields'][$title['field']]['order'] == 0 ) {
                                    $link2 = '';
                                }

                                $grid .= str_replace('{{value}}', $link2 . $title['value'] . $link1, $this->_temp['table']->titlesLoop());
                            } else {
                                $grid .= str_replace('{{value}}', "<a href=\"javascript:gridAjax('{$this->getInfo('ajax')}','" . $title['url'] . "') \">" . $title['value'] . $imgFinal . "</a>", $this->_temp['table']->titlesLoop());

                            }

                        } else {
                            //Replace values in the template
                            if ( ! array_key_exists('url', $title) ) {
                                $grid .= str_replace('{{value}}', $title['value'], $this->_temp['table']->titlesLoop());
                            } else {

                                if ( $this->getAlwaysShowOrderArrows() === true ) {

                                    $link1 = "<a  href='" . $title['simpleUrl'] . "/order{$this->getGridId()}/{$title['field']}_DESC'>{$images['desc']}</a>";
                                    $link2 = "<a  href='" . $title['simpleUrl'] . "/order{$this->getGridId()}/{$title['field']}_ASC'>{$images['asc']}</a>";

                                    if ( ($orderField == $title['field'] && $order == 'asc') || $this->_data['fields'][$title['field']]['order'] == 0 ) {
                                        $link1 = '';
                                    }

                                    if ( ($orderField == $title['field'] && $order == 'desc') || $this->_data['fields'][$title['field']]['order'] == 0 ) {
                                        $link2 = '';
                                    }

                                    $grid .= str_replace('{{value}}', $link2 . $title['value'] . $link1, $this->_temp['table']->titlesLoop());

                                } else {

                                    $grid .= str_replace('{{value}}', "<a href='" . $title['url'] . "'>" . $title['value'] . $imgFinal . "</a>", $this->_temp['table']->titlesLoop());

                                }


                            }
                        }
                    }
                }
            }

            //Deal with extra fields
            if ( $title['type'] == 'extraField' && $title['position'] == 'right' ) {
                $grid .= str_replace('{{value}}', $title['value'], $this->_temp['table']->titlesLoop());
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
     * @return unknown
     */
    protected function _buildGridTable ($grids)
    {

        $i = 0;
        $grid = '';

        //We have an extra td for the text to remove filters and order
        if ( $this->getParam('filters') || $this->getParam('order') ) {
            $i ++;
        }

        if ( $this->getInfo("hRow,title") && $this->_totalRecords > 0 ) {

            $bar = $grids;
            $hbar = trim($this->getInfo("hRow,field"));
            $p = 0;

            foreach ( $grids[0] as $value ) {
                if ( isset($value['field']) && $value['field'] == $hbar ) {
                    $hRowIndex = $p;
                }
                $p ++;
            }
            $aa = 0;
        }

        $aa = 0;
        $class = 0;
        $fi = array();
        foreach ( $grids as $value ) {

            unset($fi);
            // decorators
            $search = $this->_finalFields;
            foreach ( $search as $key => $final ) {
                if ( $final['type'] == 'extraField' ) {
                    unset($search[$key]);
                }
            }

            $search = array_keys($search);

            foreach ( $value as $tia ) {

                if ( isset($tia['field']) ) {
                    $fi[] = $tia['value'];
                }
            }


            if ( $this->getSource()->hasCrud() ) {

                if ( isset($search[0]) && ($search[0] === 'D' || $search[0] === 'E' || $search[0] === 'V') ) {
                    unset($search[0]);
                }

                if ( isset($search[1]) && ($search[1] === 'D' || $search[1] === 'E') ) {
                    unset($search[1]);
                }

                if ( isset($search[2]) && ($search[2] === 'D' || $search[2] === 'E') ) {
                    unset($search[2]);
                }
            } else {
                if ( isset($search[0]) && $search[0] === 'V' ) {
                    unset($search[0]);
                }
            }

            $search = $this->_resetKeys($search);


            $finalFields = array_combine($search, $fi);

            //horizontal row
            if ( $this->getInfo("hRow,title") ) {


                $col = $this->getInfo("hRow");
                $firstRow = false;

                if(! isset($bar[$aa - 1][$hRowIndex]))
                {
                     $bar[$aa - 1][$hRowIndex]['value'] = '';
                     $firstRow = true;
                }

                if ( $bar[$aa][$hRowIndex]['value'] != $bar[$aa - 1][$hRowIndex]['value'] ) {
                    $i ++;

                    if ( isset($bar[$aa - 1]) && $firstRow!==true ) {
                        $grid .= $this->_buildSqlexpTable($this->_buildSqlExp(array($col['field'] => $bar[$aa - 1][$hRowIndex]['value'])));
                    }

                    $grid .= str_replace(array("{{value}}", "{{class}}"), array($bar[$aa][$hRowIndex]['value'], isset($value['class'])?$value['class']:''), $this->_temp['table']->hRow($finalFields));
                }


            }

            $i ++;

            //loop tr
            $grid .= $this->_temp['table']->loopStart(isset($this->_classRowConditionResult[$class]) ? $this->_classRowConditionResult[$class] : '');

            $set = 0;
            foreach ( $value as $final ) {

                $finalField = isset($final['field']) ? $final['field'] : '';
                $finalHrow = $this->getInfo("hRow,field") ? $this->getInfo("hRow,field") : '';

                if ( ($finalField != $finalHrow && $this->getInfo("hRow,title")) || ! $this->getInfo("hRow,title") ) {

                    $set ++;

                    $grid .= str_replace(array("{{value}}", "{{class}}", "{{style}}"), array($final['value'], $final['class'], $final['style']), $this->_temp['table']->loopLoop($finalFields));

                }
            }

            if ( $this->getInfo("hRow,title") && $this->_totalRecords > 0 ) {
                if ( ($aa + 1) == $this->getTotalRecords() ) {
                    $grid .= $this->_buildSqlexpTable($this->_buildSqlExp(array($col['field'] => $bar[$aa][$hRowIndex]['value'])));
                }
            }

            $set = null;
            $grid .= $this->_temp['table']->loopEnd($finalFields);

            @$aa ++;
            $class ++;
        }

        if ( $this->_totalRecords == 0 ) {
            $grid = str_replace("{{value}}", $this->__('No records found'), $this->_temp['table']->noResults());
        }

        return $grid;

    }


    /**
     * Build the table that handles the query result from sql expressions
     *
     * @param array $sql
     * @return unknown
     */
    protected function _buildSqlexpTable ($sql)
    {

        $grid = '';
        if ( is_array($sql) ) {
            $grid .= $this->_temp['table']->sqlExpStart();

            foreach ( $sql as $exp ) {
                if ( ! $this->getInfo("hRow,field") || $exp['field'] != $this->getInfo("hRow,field") ) {
                    $grid .= str_replace(array("{{value}}", '{{class}}'), array($exp['value'], $exp['class']), $this->_temp['table']->sqlExpLoop());
                }
            }
            $grid .= $this->_temp['table']->sqlExpEnd();

        } else {
            return false;
        }

        return $grid;

    }


    /**
     * Build pagination
     *
     * @return string
     */
    protected function _pagination ()
    {

        $pageSelect = '';
        if ( count($this->_paginationOptions) > 0 && $this->getTotalRecords() > 0 ) {
            if ( ! array_key_exists($this->_pagination, $this->_paginationOptions) && ! $this->getParam('perPage') ) {
                $this->_paginationOptions[0] = $this->__('Select');
            }
            ksort($this->_paginationOptions);

            foreach ( $this->_paginationOptions as $key => $value ) {
                $this->_paginationOptions[$key] = $this->__($value);
            }

            $url = $this->getUrl('perPage');
            $menuPerPage = ' | ' . $this->__('Show') . ' ' . $this->getView()->formSelect('perPage', $this->getParam('perPage', $this->_pagination), array('onChange' => "window.location='$url/perPage" . $this->getGridId() . "/'+this.value;"), $this->_paginationOptions) . ' ' . $this->__('items');
        } else {
            $menuPerPage = '';
        }

        $url = $this->getUrl(array('start'));

        $actual = (int) $this->getParam('start');

        $ppagina = $this->getParam('perPage', $this->_pagination);
        $result2 = '';

        $pa = $actual == 0 ? 1 : ceil($actual / $ppagina) + 1;

        // Calculate the number of pages
        if ( $this->_pagination > 0 ) {
            $npaginas = ceil($this->_totalRecords / $ppagina);
            $actual = floor($actual / $ppagina) + 1;
        } else {
            $npaginas = 0;
            $actual = 0;
        }

        if ( $this->getInfo("ajax") !== false ) {
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','$url/star{$this->getGridId()}t/0')\">1</a>";
        } else {
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"$url/start{$this->getGridId()}/0\">1</a>";

        }

        $pag .= ($actual > 5) ? " ... " : "  ";

        if ( $npaginas > 5 ) {
            $in = min(max(1, $actual - 4), $npaginas - 5);
            $fin = max(min($npaginas, $actual + 4), 6);

            for ( $i = $in + 1; $i < $fin; $i ++ ) {
                if ( $this->getInfo("ajax") !== false ) {
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=javascript:gridAjax('{$this->getInfo("ajax")}','$url/start{$this->getGridId()}/" . (($i - 1) * $ppagina) . "')> $i </a>";
                } else {
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href='$url/start{$this->getGridId()}/" . (($i - 1) * $ppagina) . "'> $i </a>";
                }

            }

            $pag .= ($fin < $npaginas) ? " ... " : "  ";
        } else {

            for ( $i = 2; $i < $npaginas; $i ++ ) {
                if ( $this->getInfo("ajax") !== false ) {

                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','" . $url . "/start{$this->getGridId()}/" . (($i - 1) * $ppagina) . "')\">$i</a> ";

                } else {

                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=\"" . $url . "/start{$this->getGridId()}/" . (($i - 1) * $ppagina) . "\">$i</a> ";

                }

            }
        }

        if ( $this->getInfo("ajax") !== false ) {
            $pag .= ($actual == $npaginas) ? "<strong>" . $npaginas . "</strong>" : " <a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','$url/start{$this->getGridId()}/" . (($npaginas - 1) * $ppagina) . "')\">$npaginas</a> ";

        } else {
            $pag .= ($actual == $npaginas) ? "<strong>" . $npaginas . "</strong>" : " <a href=\"$url/start{$this->getGridId()}/" . (($npaginas - 1) * $ppagina) . "\">$npaginas</a> ";

        }

        if ( $actual != 1 ) {

            if ( $this->getInfo("ajax") !== false ) {
                $pag = " <a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','$url/start{$this->getGridId()}/0')\">" . $this->__('First') . "</a>&nbsp;&nbsp;<a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','$url/start/" . (($actual - 2) * $ppagina) . "')\">" . $this->__('Previous') . "</a>&nbsp;&nbsp;" . $pag;

            } else {

                $pag = " <a href=\"$url/start/0\">" . $this->__('First') . "</a>&nbsp;&nbsp;<a href=\"$url/start{$this->getGridId()}/" . (($actual - 2) * $ppagina) . "\">" . $this->__('Previous') . "</a>&nbsp;&nbsp;" . $pag;
            }

        }

        if ( $actual != $npaginas ) {
            if ( $this->getInfo("ajax") !== false ) {

                $pag .= "&nbsp;&nbsp;<a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','$url/start{$this->getGridId()}/" . ($actual * $ppagina) . "')\">" . $this->__('Next') . "</a> <a href=\"javascript:gridAjax('{$this->getInfo("ajax")}','$url/start{$this->getGridId()}/" . (($npaginas - 1) * $ppagina) . "')\">" . $this->__('Last') . "&nbsp;&nbsp;</a>";
            } else {

                $pag .= "&nbsp;&nbsp;<a href=\"$url/start{$this->getGridId()}/" . ($actual * $ppagina) . "\">" . $this->__('Next') . "</a>&nbsp;&nbsp;<a href=\"$url/start{$this->getGridId()}/" . (($npaginas - 1) * $ppagina) . "\">" . $this->__('Last') . "</a>";
            }

        }

        if ( $npaginas > 1 && $this->getInfo("limit") == 0 ) {

            if ( $npaginas <= 100 ) {

                $pageSelectOptions = array();
                for ( $i = 1; $i <= $npaginas; $i ++ ) {
                    $pageSelectOptions[(($i - 1) * $ppagina)] = $i;
                }

                // Buil the select form element
                if ( $this->getInfo("ajax") !== false ) {
                    $pageSelect = $this->getView()->formSelect('idf' . $this->getGridId(), ($pa - 1) * $this->getResultsPerPage(), array('onChange' => "javascript:gridAjax('{$this->getInfo("ajax")}','{$url}/start{$this->getGridId()}/'+this.value)"), $pageSelectOptions);
                } else {
                    $pageSelect = $this->getView()->formSelect('idf' . $this->getGridId(), ($pa - 1) * $this->getResultsPerPage(), array('onChange' => "window.location='{$url}/start{$this->getGridId()}/'+this.value"), $pageSelectOptions);
                }

            } else {
                $pageSelect = $this->getView()->formText('idf', $pa, array('style' => 'width:30px !important; ', 'onChange' => "window.location='{$url}/start{$this->getGridId()}/'+(this.value - 1)*" . $this->getResultsPerPage()));
            }

            $pageSelect = ' | ' . $this->__('Page') . ':' . $pageSelect;

        }

        if ( $npaginas > 1 || count($this->_export) > 0 ) {

            //get actual record
            if ( $actual <= 1 ) {
                $registoActual = 1;
                $registoFinal = $this->_totalRecords > $ppagina ? $ppagina : $this->_totalRecords;
            } else {
                $registoActual = $actual * $ppagina - $ppagina;

                if ( $actual * $ppagina > $this->_totalRecords ) {
                    $registoFinal = $this->_totalRecords;
                } else {
                    $registoFinal = $actual * $ppagina;
                }

            }

            $images = $this->_temp['table']->images($this->getImagesUrl());

            $url1 = $url = $this->getUrl(array('start'), false);

            $this->_render['export'] = $this->_temp['table']->export($this->getExports(), $this->getImagesUrl(), $url1, $this->getGridId());


            if ( (int) $this->getInfo("limit") > 0 ) {
                $result2 = str_replace(array('{{pagination}}', '{{numberRecords}}', '{{perPage}}', '{{pageSelect}}'), array('', (int) $this->getInfo("limit"), $menuPerPage, $pageSelect), $this->_temp['table']->pagination());

            } elseif ( $npaginas > 1 && count($this->_export) > 0 ) {

                if ( $this->_pagination == 0 ) {
                    $pag = '';
                    $pageSelect = '';
                }

                $result2 = str_replace(array('{{pagination}}', '{{numberRecords}}', '{{perPage}}', '{{pageSelect}}'), array(' | ' . $pag, $registoActual . ' ' . $this->__('to') . ' ' . $registoFinal . ' ' . $this->__('of') . '  ' . $this->_totalRecords, $menuPerPage, $pageSelect), $this->_temp['table']->pagination());

            } elseif ( $npaginas < 2 && count($this->_export) > 0 ) {

                if ( $this->_pagination == 0 ) {
                    $pag = '';
                    $pageSelect = '';
                }
                $result2 .= str_replace(array('{{pagination}}', '{{numberRecords}}', '{{perPage}}', '{{pageSelect}}'), array('', $this->_totalRecords, $menuPerPage, $pageSelect), $this->_temp['table']->pagination());

            } elseif ( count($this->_export) == 0 ) {

                if ( $this->_pagination == 0 ) {
                    $pag = '';
                    $pageSelect = '';
                }
                $result2 .= str_replace(array('{{pagination}}', '{{numberRecords}}', '{{perPage}}', '{{pageSelect}}'), array(' | ' . $pag, $this->_totalRecords, $menuPerPage, $pageSelect), $this->_temp['table']->pagination());

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
    public function deploy ()
    {

        if ( $this->getSource() === null ) {
            throw new Bvb_Grid_Exception('Please Specify your source');
        }


        if ( $this->allowDelete == 1 || $this->allowEdit == 1 || $this->allowAdd == 1 ) {
            $this->setAjax(false);
        }

        $this->_view = $this->getView();


        parent::deploy();


        $this->_applyConfigOptions(array(), true);

        if ( ! $this->_temp['table'] instanceof Bvb_Grid_Template_Table_Table ) {
            $this->setTemplate('table', 'table', $this->_templateParams);
        } else {
            $this->setTemplate($this->_temp['table']->options['name'], 'table', $this->_templateParams);
        }


        $images = $this->_temp['table']->images($this->getImagesUrl());


        if ( $this->allowDelete == 1 || $this->allowEdit == 1 || (is_array($this->_detailColumns) && $this->_isDetail == false) ) {

            $pkUrl = $this->getSource()->getPrimaryKey($this->_data['table']);
            $urlFinal = '';

            $failPk = false;
            $pkUrl2 = $pkUrl;
            foreach ( $pkUrl as $key => $value ) {
                foreach ( $this->getFields(true) as $field ) {
                    if ( $field['field'] == $value ) {
                        unset($pkUrl2[$key]);
                        break 2;
                    }
                }

                throw new Bvb_Grid_Exception("You don't have your primary key in your query.
                So it's not possible to perform CRUD operations. Change your select object to include your Primary Key: " . implode(';', $pkUrl2));
            }


            foreach ( $pkUrl as $value ) {
                if ( strpos($value, '.') !== false ) {
                    $urlFinal .= $value . ':{{' . substr($value, strpos($value, '.') + 1) . '}}-';
                } else {
                    $urlFinal .= $value . ':{{' . $value . '}}-';
                }
            }

            $urlFinal = trim($urlFinal, '-');

        }

        if ( $this->allowEdit == 1  && is_object($this->_crud) && $this->_crud->getBulkEdit()!==true ) {
            if ( ! is_array($this->_extraFields) ) {
                $this->_extraFields = array();
            }

            $removeParams = array('add', 'edit', 'comm');

            $url = $this->getUrl($removeParams);

            if ( $this->allowEdit == 1 && $this->getInfo("ajax") !== false ) {
                $urlEdit = $this->_baseUrl . '/' . str_replace("/gridmod" . $this->getGridId() . "/ajax", "", $url);
            } else {
                $urlEdit = $url;
            }

            array_unshift($this->_extraFields, array('position' => 'left', 'name' => 'E', 'decorator' => "<a href=\"$urlEdit/edit" . $this->getGridId() . "/1/comm" . $this->getGridId() . "/" . "mode:edit;[" . $urlFinal . "]\" > " . $images['edit'] . "</a>", 'edit' => true));

        }


        if ( $this->allowDelete && is_object($this->_crud) && $this->_crud->getBulkDelete()!==true ) {
            if ( ! is_array($this->_extraFields) ) {
                $this->_extraFields = array();
            }


            if ( $this->_deleteConfirmationPage == true ) {
                array_unshift($this->_extraFields, array('position' => 'left', 'name' => 'D', 'decorator' => "<a href=\"$url/comm" . $this->getGridId() . "/" . "mode:view;[" . $urlFinal . "]/gridDetail" . $this->getGridId() . "/1/gridRemove" . $this->getGridId() . "/1\" > " . $images['delete'] . "</a>", 'delete' => true));
            } else {
                array_unshift($this->_extraFields, array('position' => 'left', 'name' => 'D', 'decorator' => "<a href=\"#\" onclick=\"_" . $this->getGridId() . "confirmDel('" . $this->__('Are you sure?') . "','$url/comm" . $this->getGridId() . "/" . "mode:delete;[" . $urlFinal . "]');\" > " . $images['delete'] . "</a>", 'delete' => true));
            }

        }


        if ( is_array($this->_detailColumns) && $this->_isDetail == false ) {
            if ( ! is_array($this->_extraFields) ) {
                $this->_extraFields = array();
            }

            $removeParams = array('add', 'edit', 'comm');
            $url = $this->getUrl($removeParams, false);

            array_unshift($this->_extraFields, array('position' => 'left', 'name' => 'V', 'decorator' => "<a href=\"$url/gridDetail" . $this->getGridId() . "/1/comm" . $this->getGridId() . "/" . "mode:view;[" . $urlFinal . "]/\" >" . $images['detail'] . "</a>", 'detail' => true));
        }


        if ( $this->allowAdd == 0 && $this->allowDelete == 0 && $this->allowEdit == 0 ) {
            $this->_gridSession->unsetAll();
        }

        if ( ! in_array('add' . $this->getGridId(), array_keys($this->getAllParams())) && ! in_array('edit' . $this->getGridId(), array_keys($this->getAllParams())) ) {

            if ( $this->_gridSession->correct === NULL || $this->_gridSession->correct === 0 ) {
                $this->_gridSession->unsetAll();
            }
        }

        if ( strlen($this->_gridSession->message) > 0 ) {
            $this->_render['message'] = str_replace("{{value}}", $this->_gridSession->message, $this->_temp['table']->formMessage($this->_gridSession->messageOk));
            $this->_renderDeploy['message'] = $this->_render['message'];
        }


        #$this->_render['form'] = $this->_form->render();
        if ( (($this->getParam('edit') == 1) || $this->getParam('add') == 1) || $this->getInfo("doubleTables") == 1 ) {

            if ( $this->allowAdd == 1 || $this->allowEdit == 1  ) {

                // Remove the unnecessary URL params
                $removeParams = array('filters', 'add');

                $url = $this->getUrl($removeParams);

                $this->_renderDeploy['form'] = $this->_form->render();
                $this->_render['form'] = $this->_form->render();

                $this->_showsForm = true;
            }
        }


        $showsForm = $this->willShow();


        if ( (isset($showsForm['form']) && $showsForm['form'] == 1 && $this->getInfo("doubleTables") == 1) || ! isset($showsForm['form']) ) {
            $this->_render['start'] = $this->_temp['table']->globalStart();
            $this->_renderDeploy['start'] = $this->_render['start'];
        }

        if ( ((! $this->getParam('edit') || $this->getParam('edit') != 1) && (! $this->getParam('add') || $this->getParam('add') != 1))  || $this->getInfo("doubleTables") == 1 ) {

            if ( $this->_isDetail == true || ($this->_deleteConfirmationPage == true && $this->getParam('gridRemove') == 1) ) {

                $columns = parent::_buildGrid();

                $this->_willShow['detail'] = true;
                $this->_willShow['detailId'] = $this->getPkFromUrl();

                $this->_render['detail'] = $this->_temp['table']->globalStart();

                foreach ( $columns[0] as $value ) {
                    if ( ! isset($value['field']) ) {
                        continue;
                    }

                    if ( isset($this->_data['fields'][$value['field']]['title']) ) {
                        $value['field'] = $this->__($this->_data['fields'][$value['field']]['title']);
                    } else {
                        $value['field'] = $this->__(ucwords(str_replace('_', ' ', $value['field'])));
                    }

                    $this->_render['detail'] .= str_replace(array('{{field}}', '{{value}}'), array($value['field'], $value['value']), $this->_temp['table']->detail());
                }

                if ( $this->getParam('gridRemove') == 1 ) {

                    $localCancel = $this->getUrl(array('comm', 'gridDetail', 'gridRemove'));

                    $localDelete = $this->getUrl(array('gridRemove', 'gridDetail', 'comm'));
                    $localDelete .= "/comm" . $this->getGridId() . "/" . str_replace("view", 'delete', $this->getParam('comm'));

                    $buttonRemove = $this->getView()->formButton('delRecordGrid', $this->__('Remove Record'), array('onclick' => "window.location='$localDelete'"));
                    $buttonCancel = $this->getView()->formButton('delRecordGrid', $this->__('Cancel'), array('onclick' => "window.location='$localCancel'"));

                    $this->_render['detail'] .= str_replace('{{button}}', $buttonRemove . ' ' . $buttonCancel, $this->_temp['table']->detailDelete());
                } else {
                    $this->_render['detail'] .= str_replace(array('{{url}}', '{{return}}'), array($this->getUrl(array('gridDetail', 'comm'), false), $this->__('Return')), $this->_temp['table']->detailEnd());
                }

                $this->_render['detail'] .= $this->_temp['table']->globalEnd();

                $this->_renderDeploy['detail'] = $this->_render['detail'];

            } else {
                $this->_willShow['grid'] = true;
                $this->_buildGridRender();
            }

            $this->_showsGrid = true;
        } else {
            $this->_render['start'] = $this->_temp['table']->globalStart();
            $this->_buildGridRender(false);
            $this->_render['end'] = $this->_temp['table']->globalEnd();
        }


        if ( (isset($showsForm['form']) && $showsForm['form'] == 1 && $this->getInfo("doubleTables") == 1) || ! isset($showsForm['form']) ) {
            $this->_render['end'] = $this->_temp['table']->globalEnd();
            $this->_renderDeploy['end'] = $this->_render['end'];
        }


        //Build JS
        $this->_printScript();

        $gridId = $this->getGridId();

        if ( $this->getParam('gridmod') == 'ajax' && $this->getInfo("ajax") !== false ) {

            $layout = Zend_Layout::getMvcInstance();
            if ( $layout instanceof Zend_Layout ) {
                $layout->disableLayout();
            }

            $response = Zend_Controller_Front::getInstance()->getResponse();
            $response->clearBody();
            $response->setBody(implode($this->_renderDeploy));
            $response->sendResponse();
            die();
        }

        if ( $this->getInfo("ajax") !== false ) {
            $gridId = $this->getInfo("ajax");
        }

        $grid = "<div id='{$gridId}'>" . implode($this->_renderDeploy) . "</div>";

        if ( $this->_gridSession->correct == 1 ) {
            $this->_gridSession->unsetAll();
        }

        $this->_deploymentContent = $grid;
        return $this;
    }


    /**
     * Combines all parts from the output
     * To deploy or to render()
     * @param unknown_type $deploy
     */
    private function _buildGridRender ($deploy = true)
    {
        $bHeader = $this->_buildExtraRows('beforeHeader');
        $bHeader .= $this->_buildHeader();
        $bHeader .= $this->_buildExtraRows('afterHeader');
        $bTitles = $this->_buildExtraRows('beforeTitles');
        $bTitles .= $this->_buildMassActions();
        $bTitles .= $this->_buildTitlesTable(parent::_buildTitles());
        $bTitles .= $this->_buildExtraRows('afterTitles');
        $bFilters = $this->_buildExtraRows('beforeFilters');
        $bFilters .= $this->_buildFiltersTable(parent::_buildFilters());
        $bFilters .= $this->_buildExtraRows('afterFilters');
        $bGrid = $this->_buildGridTable(parent::_buildGrid());

        if ( ! $this->getInfo("hRow,title") ) {
            $bSqlExp = $this->_buildExtraRows('beforeSqlExpTable');
            $bSqlExp .= $this->_buildSqlexpTable(parent::_buildSqlExp());
            $bSqlExp .= $this->_buildExtraRows('afterSqlExpTable');
        } else {
            $bSqlExp = '';
        }


        $bPagination = $this->_buildExtraRows('beforePagination');
        $bPagination .= $this->_pagination();
        $bPagination .= $this->_buildExtraRows('afterPagination');

        if ( $deploy == true ) {
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
     * @param $part
     * @param $appendGlobal
     */
    public function render ($part, $appendGlobal = false)
    {
        $result = '';

        if($part=='start' && $this->getInfo('ajax')!==false)
        {
            $result .= "<div id='".$this->getInfo('ajax')."'>";
        }


        if ( $appendGlobal === true ) {
            $result .= $this->_render['start'];
        }

        if ( isset($this->_render[$part]) ) {
            $result .= $this->_render[$part];
        }

        if ( $appendGlobal === true ) {
            $result .= $this->_render['end'];
        }

        if($part=='end' && $this->getInfo('ajax')!==false)
        {
            $result .= "</div>";
        }

        return $result;
    }


    public function __toString ()
    {
        if ( is_null($this->_deploymentContent) ) {
            die('You must explicitly call the deploy() method before printing the object');
            # self::deploy();
        }
        return $this->_deploymentContent;
    }

    protected function _printScript ()
    {

        if ( $this->getInfo('ajax') !== false ) {
            $useAjax = 1;
        } else {
            $useAjax = 0;
        }

        $script = "";


        if($this->hasMassActions())
        {

 $script .=" var confirmMessages_".$this->getGridId()." = new Array();".PHP_EOL;

          foreach ($this->getMassActionsOptions() as $value)
          {
              if(isset($value['confirm']))
              {
                  $script .=" confirmMessages_".$this->getGridId()."['{$value['url']}'] = '{$value['confirm']}';".PHP_EOL;
              }
          }
$script .= "

var recordsSelected_".$this->getGridId()." = 0;

var postMassIds_".$this->getGridId()." = new Array();

function convertArrayToInput_".$this->getGridId()."()
{

    if(postMassIds_".$this->getGridId().".length==0)
    {
          tempArray_".$this->getGridId()." = new Array();


           var campos = document.getElementsByTagName('input');

            for (i=0; i < campos.length; i++)
            {
                if (campos[i].type == 'checkbox' && campos[i].id == 'massCheckBox_".$this->getGridId()."' && campos[i].checked==true)
                        {
                            tempArray_".$this->getGridId().".push(campos[i].value);
                }
            }

         recordsSelected_".$this->getGridId()." = tempArray_".$this->getGridId().".length;
         updateRecords_".$this->getGridId()."();
         postMassIds_".$this->getGridId()." = tempArray_".$this->getGridId().";

         if(tempArray_".$this->getGridId().".length ==0)
         {
            alert('".$this->__('No records selected')."');
            return false;
        }
    }

    var input_".$this->getGridId()." = document.getElementById('gridAction_".$this->getGridId()."').value;

    for(var i in confirmMessages_".$this->getGridId().")
    {
       if(i == input_".$this->getGridId()." && !confirm(confirmMessages_".$this->getGridId()."[input_".$this->getGridId()."]))
       {
        return false;
       }
    }

    document.forms.massActions_".$this->getGridId().".action = input_".$this->getGridId().";

    document.getElementById('postMassIds').value = postMassIds_".$this->getGridId().".join(',');

}

function updateRecords_".$this->getGridId()."()
{
     document.getElementById('massSelected_".$this->getGridId()."').innerHTML = recordsSelected_".$this->getGridId().";
}

function observeCheckBox_".$this->getGridId()."(box)
{
    if(box.checked == true)
    {
        if(postMassIds_".$this->getGridId().".indexOf(box.value)== -1)
            {
                postMassIds_".$this->getGridId().".push(box.value);
            }
        recordsSelected_".$this->getGridId()."++;
    }else{

        if(postMassIds_".$this->getGridId().".indexOf(box.value)!= -1)
            {
                postMassIds_".$this->getGridId().".splice(postMassIds_".$this->getGridId().".indexOf(box.value),1);
                recordsSelected_".$this->getGridId()."--;
            }
    }
    updateRecords_".$this->getGridId()."();
}

function checkAll_".$this->getGridId()."(field,total,all)
    {
       var tempArray_".$this->getGridId()." = new Array();

       var campos = document.getElementsByTagName('input');

        for (i=0; i < campos.length; i++)
        {
            if (campos[i].type == 'checkbox' && campos[i].id == 'massCheckBox_".$this->getGridId()."')
                    {
                        tempArray_".$this->getGridId().".push(campos[i].value);
                        campos[i].checked = true;
                    }
        }

        if(all ==1)
            {
                postMassIds_".$this->getGridId()." = document.getElementById('massActionsAll_".$this->getGridId()."').value.split(',');
            }else{
                postMassIds_".$this->getGridId()." = tempArray_".$this->getGridId().";
            }

         recordsSelected_".$this->getGridId()." = total;
         updateRecords_".$this->getGridId()."();
    }

function uncheckAll_".$this->getGridId()."(field)
{
      var campos = document.getElementsByTagName('input');

        for (i=0; i < campos.length; i++)
        {
            if (campos[i].type == 'checkbox' && campos[i].id == 'massCheckBox_".$this->getGridId()."')
            {
                campos[i].checked = false;
            }
        }

    recordsSelected_".$this->getGridId()." = 0;

    postMassIds_".$this->getGridId()." = new Array();

    updateRecords_".$this->getGridId()."();
}
".PHP_EOL;

        }

        if ( $this->allowDelete == 1 ) {

$script .= "function _" . $this->getGridId() . "confirmDel(msg, url)
        {
            if(confirm(msg))
            {
            ";
            if ( $useAjax == 1 ) {
                $script .= "    window.location = '" . $this->_baseUrl . "/'+url.replace('/gridmod" . $this->getGridId() . "/ajax','');";
            } else {
                $script .= "    window.location = url;";
            }

            $script .= "
            }else{
                return false;
            }
        }\n\n";

        }
if ( $useAjax == 1 ) {
    $script .= "function gridAjax(ponto,url) {

    var xmlhttp;
    try
    {
        xmlhttp=new XMLHttpRequest();
    }
    catch (e)
    {
        try
        {
            xmlhttp=new ActiveXObject(\"Msxml2.XMLHTTP\");
        }
        catch (e)
        {
            try
            {
                xmlhttp=new ActiveXObject(\"Microsoft.XMLHTTP\");
            }
            catch (e)
            {
                alert(\"Your browser does not suppor AJAX!\");
                return false;
            }
        }
    }
    xmlhttp.open(\"GET\", '" . $this->_baseUrl . "/'+url,true);

    xmlhttp.onreadystatechange=function () {

        if (xmlhttp.readyState==4) {
            document.getElementById(ponto).innerHTML=xmlhttp.responseText;
        }else{
            document.getElementById(ponto).innerHTML= '<div style=\"width:'+(document.getElementById('".$this->getInfo('ajax')."').offsetWidth - 2)+';height:'+(document.getElementById('".$this->getInfo('ajax')."').offsetHeight - 2)+'\" class=\"gridLoading\"></div>';
        }
    }
    xmlhttp.send(null);
}
".PHP_EOL;
        }

if ( ! $this->getInfo("noFilters") || $this->getInfo("noFilters") != 1 ) {
$script .= "
function urlencode(str) {
    return escape(str).replace(/\+/g,'%2B').replace(/%20/g, '+').replace(/\*/g, '%2A').replace(/\//g, '%2F').replace(/@/g, '%40');
}

function " . $this->getGridId() . "gridChangeFilters(event)
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
        var values = new Array();

        for (var i = 0; i < fieldsArray.length -1; i++)
        {

        if(document.getElementById(fieldsArray[i]).type=='checkbox' && document.getElementById(fieldsArray[i]).checked ==false)
        {
            value = '';
        }else{
            value = document.getElementById(fieldsArray[i]).value;
        }
         ".PHP_EOL;

$script .= "
        if(value.length>0)
            {";

                $script .= "         value = value.replace(/^\s+|\s+$/g,'');".PHP_EOL;
                $script .= "         value = value.replace(/\//,'');".PHP_EOL;
                $script .= "         filtro += urlencode(document.getElementById(fieldsArray[i]).name)+'".$this->getGridId()."/'+urlencode(value)+'/';

                values.push(value);
            }

            if(document.getElementById(fieldsArray[i]).type == 'select-one')
            {
                values.push(value);
            }
        }

        if(values.length==0)
        {
            alert('".$this->__('No Filters to Apply')."');
            return false;
        }

    ".PHP_EOL;

            if ( $useAjax == 1 ) {
                $script .= "        gridAjax('{$this->getInfo("ajax")}',url+'/'+filtro);";
            } else {
                $script .= "        window.location=url+'/'+filtro;".PHP_EOL;
            }
        }
        $script .= "
    }
        ";

        $this->getView()->headScript()->appendScript($script);

        return;
    }


    /**
     *
     * @var Bvb_Grid_Form
     * @return unknown
     */
    public function setForm ($crud)
    {

        $oldElements = $crud->getElements();


        $formElements = $this->getSource()->buildForm($this->_data['fields']);

        if ( $this->getParam('add') ) {
            $formsCount = $crud->getBulkAdd() > 0 ? $crud->getBulkAdd() : 1;
        } elseif ( $this->getParam('edit') ) {
            $formsCount = count(explode(',',$this->getParam('postMassIds')))> 0 ? count(explode(',',$this->getParam('postMassIds'))) : 1;
        }else{
            $formsCount = 1;
        }

        if ( $crud->getBulkDelete() == true ) {
            $this->addMassActions(array(array('url' => $this->getUrl() . '/zfmassremove' . $this->getGridId() . '/1/', 'caption' => 'Remove Selected Records', 'confirm' => 'Are you sure?')));
        }

        if ( $crud->getBulkEdit() == true ) {
            $this->addMassActions(array(array('url' => $this->getUrl() . '/zfmassedit' . $this->getGridId() . '/1/edit'.$this->getGridId().'/1', 'caption' => 'Edit Selected Records')));
        }

        $this->_crud = $crud;

        $arr = array();

         //////
        if ( $crud->getUseVerticalInputs() ===false ) {
            $arr[0] = new Zend_Form_SubForm($formElements);

            if($formsCount>1)
            $arr[0]->addElement('checkbox','ZFIGNORE',array('label'=>$this->__('Ignore'),'order'=>0));
            $arr[0]->setElementDecorators($crud->getSubformElementTitle());
            $arr[0]->setDecorators($crud->getUseVerticalInputs() ? $crud->getSubFormDecorator() : $crud->getSubFormDecoratorVertical());
            $crud->getForm()->addSubForm($arr[0], 0);

            foreach ($crud->getForm()->getSubForm(0)->getElements() as $value)
            {
                $value->clearValidators();
                $value->setRequired(false);
            }
        }
        /////

        for ( $i = 1; $i <= $formsCount; $i ++ ) {

            $arr[$i] = new Zend_Form_SubForm($formElements);


            if($formsCount>1)
            $arr[$i]->addElement('checkbox','ZFIGNORE',array('label'=>$this->__('Ignore record'),'order'=>0));

            #$arr[$i]->setElementDecorators($crud->getUseVerticalInputs() ? $crud->getSubformElementDecorator() : $crud->getSubformElementDecoratorVertical());
            $arr[$i]->setDecorators($crud->getUseVerticalInputs() ? $crud->getSubFormDecorator() : $crud->getSubFormDecoratorVertical());


            if($this->getParam('edit'))
            {
               $arr[$i]->addElement('hidden','ZFPK',array('decorators' => $crud->getButtonHiddenDecorator()));
            }

            $crud->getForm()->addSubForm($arr[$i], $i);

            ////////////////
            ////////////////
            ////////////////
            ////////////////

            $form = $crud->getForm()->getSubForm($i);


            foreach ( $oldElements as $key => $value ) {
                $form->addElement($value);
            }

            if ( count($form->getElements()) > 0 ) {
                foreach ( $form->getElements() as $key => $value ) {
                    if($value->helper =='formHidden'){
                       continue;
                    }
                    $value->setDecorators($crud->getUseVerticalInputs() ? $crud->getSubformElementDecorator() : $crud->getSubformElementDecoratorVertical());
                }
            }

            if ( $crud->getFieldsBasedOnQuery() == 1 ) {

                $finalFieldsForm = array();
                $fieldsToForm = $this->getFields(true);

                foreach ( $fieldsToForm as $key => $value ) {
                    $field = substr($value['field'], strpos($value['field'], '.') + 1);
                    $finalFieldsForm[] = $field;
                }
                foreach ( $form->getElements() as $key => $value ) {

                    if ( ! in_array($key, $finalFieldsForm) ) {
                        $form->removeElement($key);
                    }
                }
            }

            if ( count($crud->getAllowedFields()) > 0 ) {

                foreach ( $form->getElements() as $key => $value ) {
                    if ( ! in_array($key, $crud->getAllowedFields()) ) {
                        $form->removeElement($key);
                    }
                }
            }

            if ( count($crud->getDisallowedFields()) > 0 ) {

                foreach ( $form->getElements() as $key => $value ) {
                    if ( in_array($key, $crud->getDisallowedFields()) ) {
                       $form->removeElement($key);
                    }
                }
            }

            foreach ( $this->_data['fields'] as $key => $title ) {

                if ( $form->getElement($key) ) {
                    $form->getElement($key)->setLabel($title['title']);
                }
            }


            if ( count($form->getElements()) == 0 ) {
                throw new Bvb_Grid_Exception($this->__("Your form does not have any fields"));
            }


            foreach ( $form->getElements() as $element ) {
                if ( $element->helper == 'formFile' ) {
                    $element->setDecorators($crud->getFileDecorator());
                }
            }

            ////////////////
            ////////////////
            ////////////////
        }


        if ( $crud->getUseVerticalInputs() === false ) {
            foreach ( $crud->getForm()->getSubForm(0)->getElements() as $key => $value ) {
                if ( ! in_array($key, array_keys($crud->getForm()->getSubForm(1)->getElements())) ) {
                    $crud->getForm()->getSubForm(0)->removeElement($key);
                }
            }
        }

        $crud->getForm()->setDecorators($crud->getFormDecorator());
        $crud->getForm()->setMethod('post');

        $crud->getForm()->addElement('submit', 'form_submit' . $this->getGridId(), array('label' => $this->__('Save'), 'class' => 'submit', 'decorators' => $crud->getButtonHiddenDecorator()));
        $crud->getForm()->addElement('hidden', 'zfg_form_edit' . $this->getGridId(), array('value' => 1, 'decorators' => $crud->getButtonHiddenDecorator()));
        $crud->addElement('hash', 'zfg_csrf' . $this->getGridId(), array('salt' => 'unique', 'decorators' => $crud->getButtonHiddenDecorator()));

        $url = $this->getUrl(array_merge(array('add','postMassIds','zfmassedit', 'edit', 'comm', 'form_reset'), array_keys($crud->getForm()->getElements())));

        $crud->getForm()->addElement('button', 'form_reset' . $this->getGridId(), array('onclick' => "window.location='$url'", 'label' => $this->__('Cancel'), 'class' => 'reset', 'decorators' => $crud->getButtonHiddenDecorator()));
        $crud->getForm()->addDisplayGroup(array('zfg_csrf' . $this->getGridId(), 'zfg_form_edit' . $this->getGridId(), 'form_submit' . $this->getGridId(),'saveAndAdd' . $this->getGridId(), 'form_reset' . $this->getGridId()), 'buttons', array('decorators' => $crud->getSubformGroupDecorator()));

        $crud->setAction($this->getUrl(array_keys($crud->getForm()->getElements())));






        $this->_crudOptions['addForce'] = $crud->getOnAddForce();
        $this->_crudOptions['editForce'] = $crud->getOnEditForce();
        $this->_crudOptions['editAddCondition'] = $crud->getOnEditAddCondition();
        $this->_crudOptions['deleteAddCondition'] = $crud->getOnDeleteAddCondition();

        $this->_form = $crud->getForm();

        if ( isset($crud->options['callbackBeforeDelete']) ) {
            $this->_callbackBeforeDelete = $crud->options['callbackBeforeDelete'];
        }

        if ( isset($crud->options['callbackBeforeInsert']) ) {
            $this->_callbackBeforeInsert = $crud->options['callbackBeforeInsert'];
        }

        if ( isset($crud->options['callbackBeforeUpdate']) ) {
            $this->_callbackBeforeUpdate = $crud->options['callbackBeforeUpdate'];
        }

        if ( isset($crud->options['callbackAfterDelete']) ) {
            $this->_callbackAfterDelete = $crud->options['callbackAfterDelete'];
        }

        if ( isset($crud->options['callbackAfterInsert']) ) {
            $this->_callbackAfterInsert = $crud->options['callbackAfterInsert'];
        }

        if ( isset($crud->options['callbackAfterUpdate']) ) {
            $this->_callbackAfterUpdate = $crud->options['callbackAfterUpdate'];
        }

        $crud = $this->_object2array($crud);


        $options = $crud['options'];


        if ( isset($options['table']) && is_string($options['table']) ) {
            $this->_crudTable = $options['table'];
        }

        if ( isset($options['isPerformCrudAllowed']) && $options['isPerformCrudAllowed'] == 0 ) {
            $this->_crudTableOptions['add'] = 0;
            $this->_crudTableOptions['edit'] = 0;
            $this->_crudTableOptions['delete'] = 0;
        } else {
            $this->_crudTableOptions['add'] = 1;
            $this->_crudTableOptions['edit'] = 1;
            $this->_crudTableOptions['delete'] = 1;
        }

        if ( isset($options['isPerformCrudAllowedForAddition']) && $options['isPerformCrudAllowedForAddition'] == 1 ) {
            $this->_crudTableOptions['add'] = 1;
        } elseif ( isset($options['isPerformCrudAllowedForAddition']) && $options['isPerformCrudAllowedForAddition'] == 0 ) {
            $this->_crudTableOptions['add'] = 0;
        }

        if ( isset($options['isPerformCrudAllowedForEdition']) && $options['isPerformCrudAllowedForEdition'] == 1 ) {
            $this->_crudTableOptions['edit'] = 1;
        } elseif ( isset($options['isPerformCrudAllowedForEdition']) && $options['isPerformCrudAllowedForEdition'] == 0 ) {
            $this->_crudTableOptions['edit'] = 0;
        }

        if ( isset($options['isPerformCrudAllowedForDeletion']) && $options['isPerformCrudAllowedForDeletion'] == 1 ) {
            $this->_crudTableOptions['delete'] = 1;
        } elseif ( isset($options['isPerformCrudAllowedForDeletion']) && $options['isPerformCrudAllowedForDeletion'] == 0 ) {
            $this->_crudTableOptions['delete'] = 0;
        }


        $this->_info['doubleTables'] = $this->getInfo("doubleTables");

        if ( isset($options['delete']) ) {
            if ( $options['delete'] == 1 ) {
                $this->delete = array('allow' => 1);
                if ( isset($options['onDeleteAddWhere']) ) {
                    $this->_info['delete']['where'] = $options['onDeleteAddWhere'];
                }
            }
        }

        if ( isset($options['add']) && $options['add'] == 1 ) {
            if ( ! isset($options['addButton']) ) {
                $options['addButton'] = 0;
            }
            $this->add = array('allow' => 1, 'button' => $options['addButton']);
        }

        if ( isset($options['edit']) && $options['edit'] == 1 ) {
            $this->edit = array('allow' => 1);
        }

        $this->_processForm();
        return $this;
    }



    /**
     * Field type on the filters area. If the field type is enum, build the options
     * Also, we first need to check if the user has defined values to present.
     * If set, this values override the others
     *
     * @param string $campo
     * @param string $valor
     * @return string
     */
    protected function _formatField ($campo)
    {

        $renderLoaded = false;
        $allFieldsIds = $this->getAllFieldsIds();

        if (isset($this->_filters[$campo]) && is_array($this->_filters[$campo]) && isset($this->_filters[$campo]['render']) ) {

            $render = $this->loadFilterRender($this->_filters[$campo]['render']);
            $render->setView($this->getView());
            $renderLoaded = true;
        }


        $valor = $campo;

        if ( isset($this->_data['fields'][$valor]['search']) && $this->_data['fields'][$valor]['search'] == false ) {
            return '';
        }

        //check if we need to load  fields for filters
        if ( isset($this->_filters[$valor]['distinct']) && is_array($this->_filters[$valor]['distinct']) && isset($this->_filters[$valor]['distinct']['field']) ) {

            $distinctField = $this->_filters[$valor]['distinct']['field'];
            $distinctValue = $this->_filters[$valor]['distinct']['name'];
            $distinctOrder = isset($this->_filters[$valor]['distinct']['order']) ? $this->_filters[$valor]['distinct']['order'] : 'name ASC';


            $dir = stripos($distinctOrder, ' asc') !== false ? 'ASC' : 'DESC';
            $sort = stripos($distinctOrder, 'name') !== false ? 'value' : 'field';

            if ( isset($this->_data['fields'][$distinctField]['field']) ) {
                $distinctField = $this->_data['fields'][$distinctField]['field'];
            }
            if ( isset($this->_data['fields'][$distinctValue]['field']) ) {
                $distinctValue = $this->_data['fields'][$distinctValue]['field'];
            }

            $final = $this->getSource()->getDistinctValuesForFilters($distinctField, $distinctValue, $sort . ' ' . $dir);


            $this->_filters[$valor]['values'] = $final;
        }


        //Remove unwanted url params
        $url = $this->getUrl(array('filters', 'start', 'comm', '_exportTo'));

        $fieldsSemAsFinal = $this->_data['fields'];

        if ( isset($fieldsSemAsFinal[$campo]['searchField']) ) {
            $nkey = $fieldsSemAsFinal[$campo]['searchField'];
            @$this->_filtersValues[$campo] = $this->_filtersValues[$nkey];
        }


        $help_javascript = '';

        $i = 0;
        foreach ( array_keys($this->_filters) as $value ) {

            if ( ! isset($this->_data['fields'][$value]['search']) ) {
                $this->_data['fields'][$value]['search'] = true;
            }

            $hRow = isset($this->_data['fields'][$value]['hRow']) ? $this->_data['fields'][$value]['hRow'] : '';

            if ( $this->_displayField($value) && $hRow != 1 && $this->_data['fields'][$value]['search'] != false ) {

                if ( is_array($allFieldsIds[$value]) ) {
                    foreach ( $allFieldsIds[$value] as $newId ) {
                        $help_javascript .= "filter_" . $this->getGridId() . $value . "_" . $newId . ',';
                    }
                } else {
                    $help_javascript .= "filter_" . $this->getGridId() . $value . ",";
                }
            }
        }

        if(count($this->_externalFilters)>0)
        {
            foreach (array_keys($this->_externalFilters) as $fil)
            {

                $help_javascript .= $fil.',';
            }
        }

        $this->_javaScriptHelper = array('js'=>$help_javascript,'url'=>$url);

        if ( $this->getUseKeyEventsOnFilters() === true ) {
            $attr['onChange'] =  $this->getGridId() . "gridChangeFilters(1);";
        }
            $attr['onKeyUp'] =  $this->getGridId() . "gridChangeFilters(event);";

        $opcoes = array();


        if ( isset($opcoes['style']) ) {
            $attr['style'] = $opcoes['style'];
        } else {
            $attr['style'] = " width:95% ";
        }

        if ( isset($opcoes['class']) ) {
            $attr['class'] = $opcoes['class'];
        }

        if ( isset($this->_filters[$campo]) ) {
            $opcoes = $this->_filters[$campo];
        }

        $attr['id'] = "filter_" . $this->getGridId() . $campo;

        $selected = null;

        if ( isset($this->_filters[$valor]['values']) && is_array($this->_filters[$valor]['values']) ) {
            $hasValues = false;
        } else {
            $hasValues = $this->getSource()->getFilterValuesBasedOnFieldDefinition($this->_data['fields'][$campo]['field']);
        }

        if ( is_array($hasValues) ) {
            $opcoes = array();
            $tipo = 'text';
            $opcoes['values'] = $hasValues;
        } else {
            $tipo = 'text';
        }

        if ( isset($opcoes['values']) && is_array($opcoes['values']) ) {

            $tipo = 'invalid';
            $values = array();
            $values[''] = '--' . $this->__('All') . '--';

            $avalor = $opcoes['values'];

            if ( isset($this->_data['fields'][$valor]['translate']) && $this->_data['fields'][$valor]['translate'] == 1 ) {
                $avalor = array_map(array($this, '__'), $avalor);
            }

            foreach ( $avalor as $key => $value ) {
                if ( isset($this->_filtersValues[$campo]) && $this->_filtersValues[$campo] == $key ) {
                    $selected = $key;
                }

                $values[$key] = $value;
            }

            if($renderLoaded===false)
            {
                $render = $this->loadFilterRender('Select');
                $render->setView($this->getView());
                $renderLoaded = true;
            }

            $render->setValues($values);
            $render->setDefaultValue(isset($this->_filtersValues[$campo]) ? $this->_filtersValues[$campo] : '');
        }

        if ( $tipo != 'invalid' ) {

            if ( $renderLoaded === false ) {
                $render = $this->loadFilterRender('Text');
                $render->setView($this->getView());
                $renderLoaded = true;
            }

            $render->setDefaultValue(isset($this->_filtersValues[$campo]) ? $this->_filtersValues[$campo] : '');
        }

        if (isset($this->_filtersValues[$campo]) && is_array($this->_filtersValues[$campo]) ) {

            foreach ( $this->_filtersValues[$campo] as $key => $value ) {
                $render->setDefaultValue($value, $key);
            }
        }

        $render->setFieldName($valor);
        $render->setAttributes($attr);
        $render->setTranslator($this->getTranslator());

        return $render->render();
    }


    function getAllFieldsIds ()
    {
        $fields = array();
        foreach ( $this->_filters as $key => $filter ) {

            if (is_array($filter) && isset($filter['render']) ) {

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
     * @param $options
     */
    protected function _applyConfigOptions ($options)
    {

        $this->_deployOptions = $options;

        if ( isset($this->_deployOptions['templateDir']) ) {

            $this->_deployOptions['templateDir'] = (array) $this->_deployOptions['templateDir'];

            foreach ( $this->_deployOptions['templateDir'] as $templates ) {
                $temp = $templates;
                $temp = str_replace('_', '/', $temp);
                $this->addTemplateDir($temp, $templates, 'table');
            }
        }


        if ( isset($this->_deployOptions['imagesUrl']) ) {
            $this->setImagesUrl($this->_deployOptions['imagesUrl']);
        }

        if ( isset($this->_deployOptions['template']) ) {
            $this->setTemplate($this->_deployOptions['template'], 'table');
        }

        return true;
    }


    /**
     * Returns form instance
     */
    public function getForm ($subForm = null)
    {
        if(!is_null($subForm))
        return $this->_form->getSubForm($subForm);

        return $this->_form;
    }


    /**
     * Adds a row class based on a condition
     * @param $column
     * @param $condition
     * @param $class
     */
    public function addClassRowCondition ($column, $condition, $class)
    {
        $this->_classRowCondition[$column][] = array('condition' => $condition, 'class' => $class);
        return $this;
    }


    /**
     * Adds a cell class based on a condition
     * @param $column
     * @param $condition
     * @param $class
     */
    public function addClassCellCondition ($column, $condition, $class, $else = '')
    {
        $this->_classCellCondition[$column][] = array('condition' => $condition, 'class' => $class, 'else' => $else);
        return $this;
    }


    /**
     * Sets a row class based on a condition
     * @param $column
     * @param $condition
     * @param $class
     */
    public function setClassRowCondition ($condition, $class, $else = '')
    {
        $this->_classRowCondition = array();
        $this->_classRowCondition[] = array('condition' => $condition, 'class' => $class, 'else' => $else);
        return $this;
    }


    /**
     * Set a cell class based on a condition
     * @param $column
     * @param $condition
     * @param $class
     */
    public function setClassCellCondition ($column, $condition, $class, $else)
    {
        $this->_classCellCondition = array();
        $this->_classCellCondition[$column][] = array('condition' => $condition, 'class' => $class, 'else' => $else);
        return $this;
    }


    /**
     * Adds extra rows to the grid.
     * @param Bvb_Grid_Extra_Rows $rows
     */
    public function addExtraRows (Bvb_Grid_Extra_Rows $rows)
    {
        $rows = $this->_object2array($rows);
        $this->_extraRows = $rows['_rows'];

        return $this;
    }


    /**
     * Build extra rows
     * @param $position
     */
    protected function _buildExtraRows ($position)
    {

        if ( count($this->_extraRows) == 0 ) {

            return false;
        }

        $start = '<tr>';
        $middle = '';
        $end = '';
        $hasReturn = false;

        if ( count($this->_getExtraFields('left')) > 0 ) {
            $start .= " <td colspan='" . count($this->_getExtraFields('left')) . "'></td>";
        }

        if ( count($this->_getExtraFields('right')) > 0 ) {
            $end .= " <td colspan='" . count($this->_getExtraFields('left')) . "'></td>";
        }

        foreach ( $this->_extraRows as $key => $value ) {

            if ( $value['position'] != $position ) continue;

            foreach ( $value['values'] as $final ) {
                $colspan = isset($final['colspan']) ? "colspan='" . $final['colspan'] . "'" : '';
                $class = isset($final['class']) ? "class='" . $final['class'] . "'" : '';
                if ( ! isset($final['content']) ) {
                    $final['content'] = '';
                }

                $middle .= "<td $colspan $class >{$final['content']}</td>";

                $hasReturn = true;
            }
        }

        if ( $hasReturn === false ) {
            return false;
        }

        $end .= '</tr>';

        return $start . $middle . $end;

    }


    /**
     * Defines the default classes to be used on odd and even td
     * @param string $odd
     * @param string $even
     */
    public function setRowAltClasses ($odd, $even = '')
    {
        $this->_cssClasses = array('odd' => $odd, 'even' => $even);
        return $this;
    }


    /**
     * So user can know what is going to be done
     */
    public function buildFormDefinitions ()
    {

        if ( $this->getParam('add') == 1 ) {
            $this->_formSettings['mode'] = 'add';
            $this->_formSettings['action'] = $this->getForm()->getAction();
        }

        if ( $this->getParam('edit') == 1 ) {
            $this->_formSettings['mode'] = 'edit';
            $this->_formSettings['id'] = $this->getPkFromUrl();
            $this->_formSettings['row'] = $this->getSource()->fetchDetail($this->getPkFromUrl());
            $this->_formSettings['action'] = $this->getForm()->getAction();
        }

        if ( $this->getParam('delete') == 1 ) {
            $this->_formSettings['mode'] = 'delete';
            $this->_formSettings['id'] = $this->getPkFromUrl();
            $this->_formSettings['row'] = $this->getSource()->fetchDetail($this->getPkFromUrl());
            $this->_formSettings['action'] = $this->getForm()->getAction();
        }

    }


    /**
     * Return actions from the form
     */
    public function getFormSettings ()
    {

        $this->buildFormDefinitions();
        return $this->_formSettings;
    }


    /**
     * Show a confirmation page instead a alert window
     * @param $status
     */
    public function setDeleteConfirmationPage ($status)
    {
        $this->_deleteConfirmationPage = (bool) $status;
        return $this;
    }


    /**
     * Defines Images location
     * @param $url
     */
    public function setImagesUrl ($url)
    {
        if ( ! is_string($url) ) {
            throw new Bvb_Grid_Exception('String expected, ' . gettype($url) . ' provided');
        }
        $this->_imagesUrl = $url;
        return $this;
    }


    /**
     * Returns the actual URL images location
     */
    public function getImagesUrl ()
    {
        return $this->_imagesUrl;
    }


    /**
     *
     * Always show arrows on all fields or show only when a field
     * is sorted
     *
     * @param bool $status
     * @return Bvb_Grid_Deploy_Table
     */
    public function setAlwaysShowOrderArrows ($status)
    {
        $this->_alwaysShowOrderArrows = (bool) $status;
        return $this;
    }


    public function getAlwaysShowOrderArrows ()
    {
        return $this->_alwaysShowOrderArrows;
    }


    public function hasMassActions ()
    {
        return $this->_hasMassActions;
    }


    public function getMassActionsOptions ()
    {
        if ( ! $this->_hasMassActions ) {
            return array();
        }

        return (array) $this->_massActions;
    }


    protected function _buildMassActions ()
    {
        if ( ! $this->hasMassActions() ) return false;


        $select = array();
        foreach ( $this->getMassActionsOptions() as $value ) {
            $select[$value['url']] = $value['caption'];
        }

        $formSelect = $this->getView()->formSelect("gridAction_".$this->getGridId(), null, array(), $select);
        $formSubmit = $this->getView()->formSubmit("send_".$this->getGridId(),$this->__('Submit'),array('onClick'=>"return convertArrayToInput_".$this->getGridId()."()"));

        if($this->getResultsPerPage()<$this->getTotalRecords())
        {
            $currentRecords = $this->getResultsPerPage();
        }else{
            $currentRecords = $this->getTotalRecords();
        }

        $ids = $this->getSource()->getMassActionsIds($this->_data['table']);

        $return = "<tr><td class='massActions' colspan=" . $this->_colspan . ">";
        $return .= '<form style="padding:0;margin:0;" method="post" action="" id="massActions_' . $this->getGridId() . '" name="massActions_' . $this->getGridId() . '">';
        $return .= $this->getView()->formHidden('massActionsAll_' . $this->getGridId(), $ids);
        $return .= $this->getView()->formHidden('postMassIds', '');


        $return .= "<span class='massSelect'><a href='#' onclick='checkAll_" . $this->getGridId() . "(document.massActions_" . $this->getGridId() . ".gridMassActions_" . $this->getGridId() . ",{$this->getTotalRecords()},1);return false;'>" . $this->__('Select All') . "</a> | <a href='#' onclick='checkAll_" . $this->getGridId() . "(document.massActions_" . $this->getGridId() . ".gridMassActions_" . $this->getGridId() . ",{$currentRecords},0);return false;'>" . $this->__('Select Visible') . "</a> | <a href='#' onclick='uncheckAll_" . $this->getGridId() . "(document.massActions_" . $this->getGridId() . ".gridMassActions_" . $this->getGridId() . ",0); return false;'>" . $this->__('Unselect All') . "</a> | <strong><span id='massSelected_" . $this->getGridId() . "'>0</span></strong> " . $this->__('items selected') . "</span> " . $this->__('Actions') . ": $formSelect $formSubmit</form></td></tr>";

        return $return;
    }


    function setMassAction(array $options)
    {

        $this->_hasMassActions = true;
        $this->_massActions = $options;

        foreach ($options as $value)
        {
            if(!isset($value['url']) || !isset($value['caption']))
            {
                throw new Bvb_Grid_Exception('Options url and caption are required for each action');
            }
        }

        if(count($this->getSource()->getPrimaryKey($this->_data['table']))==0)
        {
            throw new Bvb_Grid_Exception('No primary key defined in table. Mass actions not available');
        }

        $pk = '';
        foreach ($this->getSource()->getPrimaryKey($this->_data['table']) as $value)
        {
            $aux = explode('.',$value);
            $pk .= end($aux).'-';
        }

        $pk = rtrim($pk,'-');


        $left = new Bvb_Grid_Extra_Column();
        $left->position('left')->name('')->decorator("<input type='checkbox' onclick='observeCheckBox_".$this->getGridId()."(this)' name='gridMassActions_".$this->getGridId()."' id='massCheckBox_".$this->getGridId()."' value='{{{$pk}}}' >");

        $this->addExtraColumns( $left);

    }


    function addMassActions (array $options)
    {
        if ( $this->_hasMassActions !== true ) {
            return $this->setMassAction($options);
        }


        foreach ( $options as $value ) {
            if ( ! isset($value['url']) || ! isset($value['caption']) ) {
                throw new Bvb_Grid_Exception('Options url and caption are required for each action');
            }
        }

        $this->_massActions = array_merge($options, $this->_massActions);

        return $this;
    }

    /**
     * Returns any erros from form validation
     */
    public function getFormErrorMessages()
    {
        return isset($this->_gridSession->errors)?$this->_gridSession->errors:false;
    }

    /**
     * If we should use onclick, and onkeyup instead a button over the filters
     * @param $flag
     */
    public function setUseKeyEventsOnFilters( $flag)
    {
        $this->_useKeyEventsOnFilters = (bool) $flag;
        return $this;
    }

    public function getUseKeyEventsOnFilters()
    {
        return $this->_useKeyEventsOnFilters;
    }

}
