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

class Bvb_Grid_Deploy_Table extends Bvb_Grid_Data implements
Bvb_Grid_Deploy_Interface
{

    const OUTPUT = 'table';

    /**
     * Hold definitions from configurations
     * @var array
     */
    public $deploy = array();

    /**
     * Information about the template
     *
     * @var array|empty
     */

    public $templateInfo;

    /**
     * Check if the message has already been set
     *
     * @var bool
     */
    protected $messageOk;

    /**
     * Se o formulário foi submetido com sucesso
     *
     * @var bool
     */
    protected $formSuccess = 0;

    /**
     * If the form has been submited
     *
     * @var bool
     */
    protected $formPost = 0;

    /**
     * Form values
     */
    protected $_formValues = array();

    /**
     * Form error messages
     *
     * @var unknown_type
     */
    protected $_formMessages = array();

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
     * Message after form submission
     *
     * @var string
     */
    public $message;

    /**
     * Template data
     *
     * @var array
     */
    public $template;

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
    public $imagesUrl;

    /**
     * If we are allowed to add records to the database if we
     * show two tables (the form and the grid) or just one
     *
     * @var bool
     */
    protected $double_tables = 0;

    /**
     * Set if form vaidation failed
     *
     * @var bool
     */
    protected $_failedValidation;

    /**
     * Url param with the information about removing records
     *
     * @var string
     */
    protected $_comm;

    /**
     * Template
     *
     * @var object
     */
    public $temp;

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
     * @var Zend_View_Interface
     */
    protected $_view;


    /**
     *
     * @var Zend_Form
     */
    protected $_form;


    /**
     * If the form is based on a model or set by the user
     * @var bool
     */
    protected $_formHasModel = false;

    /**
     * Show or not show the form
     * @var bool
     */
    protected $_noForm = 0;

    /**
     * To let the user know if a form will be displayed or not
     * @var bool
     */
    protected $_showsForm = false;


    /**
     * To let a user know if the grid will be displayed or not
     * @var unknown_type
     */
    protected $_showsGrid = false;


    /**
     * To edit, add, or delete records, a user must be authenticated, so we instanciate
     * it here.
     *
     * @param array $data
     */
    function __construct ($options)
    {

        $this->_setRemoveHiddenFields(true);

        parent::__construct($options);

        $this->addTemplateDir('Bvb/Grid/Template/Table', 'Bvb_Grid_Template_Table', 'table');
    }

    /**
     * @param string $var
     * @param string $value
     */

    function __set ($var, $value)
    {
        parent::__set($var, $value);
    }

    /**
     * Fetch the field type from the DB
     *
     * @param string $type
     * @param string $table
     * @return string
     */
    protected function _getFieldType ($type, $table)
    {

        $fields = $this->_getDescribeTable($table);

        return $fields[$type]['DATA_TYPE'];

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

        if (isset($this->info['add']['allow']) && $this->info['add']['allow'] == 1) {
            $this->allowAdd = 1;
        }

        if (isset($this->info['delete']['allow']) && $this->info['delete']['allow'] == 1) {
            $this->allowDelete = 1;
        }

        if (isset($this->info['edit']['allow']) && $this->info['edit']['allow'] == 1) {
            $this->allowEdit = 1;
        }

        // IF a user can edit or delete data we must instanciate the crypt classe.
        // This is an extra-security step.
        if ($this->allowEdit == 1 || $this->allowDelete) {
            $dec = isset($this->ctrlParams['comm' . $this->_gridId]) ? $this->ctrlParams['comm' . $this->_gridId] : '';
            $this->_comm = $dec;
        }

        /**
         * Remove if there is something to remove
         */
        if ($this->allowDelete) {
            self::_deleteRecord($dec);

        }


        if ($this->allowAdd == 1 || $this->allowEdit == 1) {
            $opComm = isset($this->ctrlParams['comm' . $this->_gridId]) ? $this->ctrlParams['comm' . $this->_gridId] : '';
            $op_query = self::_convertComm($opComm);


            $mode = isset($this->ctrlParams['edit' . $this->_gridId]) ? 'edit' : 'add';

            $queryUrl = $this->_getPkFromUrl();


            if (! Zend_Controller_Front::getInstance()->getRequest()->isPost()) {
                if ($mode == 'edit') {
                    $r = $this->_form->getModel()->fetchRow($queryUrl);

                    if ($r === null) {
                        $this->message = $this->__('Record Not Found');
                        $this->_noForm = 1;
                    } else {
                        $r = $r->toArray();

                        $info = $this->_form->getModel()->info();

                        foreach ($r as $key => $value) {
                            $isField = $this->_form->getElement($key);
                            if (isset($isField)) {
                                if (substr($info['metadata'][$key]['DATA_TYPE'], 0, 4) == 'set(') {
                                    $value = explode(',', $value);
                                }
                                $this->_form->getElement($key)->setValue($value);
                            }
                        }
                    }
                }
            }
        }

        //Check if the request method is POST
        if (Zend_Controller_Front::getInstance()->getRequest()->isPost() && Zend_Controller_Front::getInstance()->getRequest()->getPost('_form_edit' . $this->_gridId) == 1) {

            if ($this->_form->isValid($_POST)) {

                $post = array();

                foreach ($this->_form->getElements() as $el) {
                    $post[$el->getName()] = is_array($el->getValue()) ? implode(',', $el->getValue()) : $el->getValue();
                }

                unset($post['form_submit' . $this->_gridId]);
                unset($post['_form_edit' . $this->_gridId]);
                unset($post['form_reset' . $this->_gridId]);

                $param = Zend_Controller_Front::getInstance()->getRequest();

                if ($this->_formHasModel) {
                    $modelInfo = $this->_form->getModel()->info();
                }

                // Process data
                if ($mode == 'add') {

                    try {

                        if (null !== $this->_callbackBeforeInsert) {
                            call_user_func_array($this->_callbackBeforeInsert, $post);
                        }

                        if ($this->_formHasModel) {
                            $this->_form->getModel()->insert($post);
                        } else {
                            $this->_getDb()->insert($this->data['table'], $post);
                        }

                        if (null !== $this->_callbackAfterInsert) {
                            call_user_func_array($this->_callbackAfterInsert, $post);
                        }

                        $this->message = $this->__('Record saved');
                        $this->messageOk = true;
                        $this->_comm = false;
                        $this->_noForm = 1;

                    }
                    catch (Zend_Exception $e) {
                        $this->messageOk = FALSE;
                        $this->message = $this->__('Error saving record =>') . $e->getMessage();
                        $this->formSuccess = 0;
                        $this->formPost = 1;
                        $this->_noForm = 0;
                    }

                }

                // Process data
                if ($mode == 'edit') {

                    $where = isset($this->info['edit']['where']) ? " AND " . $this->info['edit']['where'] : '';

                    try {

                        if (null !== $this->_callbackBeforeUpdate) {
                            call_user_func_array($this->_callbackBeforeUpdate, $post);
                        }

                        if ($this->_formHasModel) {
                            $this->_form->getModel()->update($post, $queryUrl . $where);
                        } else {
                            $this->_getDb()->update($this->data['table'], $post, $queryUrl . $where);
                        }

                        if (null !== $this->_callbackAfterUpdate) {
                            call_user_func_array($this->_callbackAfterUpdate, $post);
                        }

                        $this->message = $this->__('Record saved');
                        $this->messageOk = true;

                        $this->_comm = false;
                        $this->_noForm = 1;


                        unset($this->ctrlParams['comm' . $this->_gridId]);
                        unset($this->ctrlParams['edit' . $this->_gridId]);

                    }
                    catch (Zend_Exception $e) {
                        $this->messageOk = FALSE;
                        $this->message = $this->__('Error updating record =>') . $e->getMessage();
                        $this->formSuccess = 0;
                        $this->formPost = 1;
                        $this->_noForm = 0;
                    }


                }

                if ($this->cache['use'] == 1) {
                    $this->cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->cache['tag']));
                }
                $this->formSuccess = 1;
                $this->formPost = 1;

                foreach ($post as $key => $value) {
                    unset($this->ctrlParams[$key]);
                }

                unset($this->ctrlParams['form_submit' . $this->_gridId]);
                unset($this->ctrlParams['_form_edit' . $this->_gridId]);

            } else {

                $this->message = $this->__('Validation failed');
                $this->messageOk = false;
                $this->formSuccess = 0;
                $this->formPost = 1;
                $this->_noForm = 0;
                $post = null;
            }

        }

    }

    /**
     * Return the fields to be used in a specific table
     *
     * @param array $fields
     * @param string $sufix
     * @return array
     */
    protected function _getFieldsToTable (array $fields, $sufix)
    {

        $final = array();

        $sufix = rtrim($sufix, '_') . '_';

        foreach ($fields as $key => $field) {
            if (substr($key, 0, strlen($sufix)) == $sufix) {
                $final[substr($key, strlen($sufix))] = $field;
            }
        }

        return $final;
    }


    /**
     * Remove the record from the table
     * Don't forget to see if the user as set an "extra" WHERE.
     *
     * @param string $sql
     * @param string $user
     * @return string
     */
    protected function _deleteRecord ($sql)
    {

        @$param = explode(";", $sql);

        foreach ($param as $value) {
            $dec = explode(":", $value);
            @$final[$dec[0]] = $dec[1];
        }

        if (@$final['mode'] != 'delete') {
            return 0;
        }

        $pkArray = parent::_getPrimaryKey();
        $id = $this->_getDb()->quoteIdentifier($pkArray[0]);

        if (isset($this->info['delete']['where'])) {

            $where = " AND " . $this->info['delete']['where'];
        } else {
            $where = '';
        }

        try {

            $pkParentArray = $this->_getPrimaryKey();
            $pkParent = $pkParentArray[0];

            if (is_array($this->info['delete']['cascadeDelete'])) {
                foreach ($this->info['delete']['cascadeDelete'] as $value) {

                    $operand = isset($value['operand']) ? $value['operand'] : '=';
                    $parentField = isset($value['parentField']) ? $value['parentField'] : $pkParent;

                    if ($parentField != $pkParent && ! is_array($pkParentArray)) {

                        $select = $this->_getDb()->select();
                        $select->from($this->data['table'], array('total' => $parentField));
                        $select->where($this->_getPkFromUrl(true));
                        $final = $select->query(Zend_Db::FETCH_ASSOC);
                        $result = $final->fetchAll();

                        $finalValue = $result[0];
                    } else {
                        $finalValue = $final['id'];
                    }

                    $resultDelete = $this->_getDb()->delete($value['table'], $this->_getDb()->quoteIdentifier($value['childField']) . $operand . $this->_getDb()->quote($finalValue));

                }
            }

            if (null !== $this->_callbackBeforeDelete) {
                call_user_func_array($this->_callbackBeforeDelete, $this->_getPkFromUrl(false) . $where);
            }

            if ($this->_formHasModel) {
                $resultDelete = $this->_form->getModel()->delete($this->_getPkFromUrl(false) . $where);
            } else {
                $resultDelete = $this->_getDb()->delete($this->data['table'], $this->_getPkFromUrl(false) . $where);
            }

            if ($resultDelete == 1) {
                if (null !== $this->_callbackAfterDelete) {
                    call_user_func_array($this->_callbackAfterDelete, $this->_getPkFromUrl(false) . $where);
                }
            }

            $this->messageOk = true;
            $this->message = $this->__('Record deleted');

        }
        catch (Zend_Exception $e) {
            $this->messageOk = FALSE;
            $this->message = $this->__('Error deleting record =>') . $e->getMessage();
        }

        unset($this->ctrlParams['comm' . $this->_gridId]);

        if ($this->cache['use'] == 1) {
            $this->cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->cache['tag']));
        }

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

        if ($this->_adapter == 'db') {
            if (($this->getInfo('double_tables') == 0 && @$this->ctrlParams['add' . $this->_gridId] != 1 && @$this->ctrlParams['edit' . $this->_gridId] != 1) && $this->_getPrimaryKey() && @$this->info['add']['allow'] == 1 && @$this->info['add']['button'] == 1 && @$this->info['add']['no_button'] != 1) {

                $final = "<div class=\"addRecord\" ><a href=\"$url/add" . $this->_gridId . "/1\">" . $this->__('Add Record') . "</a></div>";
            }
        }

        //Template start
        $final .= $this->temp['table']->globalStart();

        /**
         * We must check if there is a filter set or an order, to show the extra th on top
         */


        if (isset($this->ctrlParams['filters' . $this->_gridId]) || isset($this->ctrlParams['order' . $this->_gridId])) {

            $url = $this->getUrl('filters', 'nofilters');
            $url2 = $this->getUrl('order');
            $url3 = $this->getUrl(array('filters', 'order', 'nofilters'));

            if (is_array($this->_defaultFilters)) {
                $url .= '/nofilters/1';
                $url3 .= '/nofilters/1';
            }

            $this->temp['table']->hasExtraRow = 1;


            //Filters and order
            if (isset($this->ctrlParams['filters' . $this->_gridId]) and isset($this->ctrlParams['order' . $this->_gridId])) {
                if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {

                    $final1 = "<a href=\"javascript:gridAjax('{$this->info['ajax']}','" . $url . "')\">" . $this->__('Remove Filters') . "</a> | <a href=\"javascript:gridAjax('{$this->info['ajax']}','" . $url2 . "')\">" . $this->__('Remove Order') . "</a> | <a href=\"javascript:gridAjax('{$this->info['ajax']}','" . $url3 . "')\">" . $this->__('Remove Filters &amp; Order') . "</a>";

                } else {
                    $final1 = "<a href=\"$url\">" . $this->__('Remove Filters') . "</a> | <a href=\"$url2\">" . $this->__('Remove Order') . "</a> | <a href=\"$url3\">" . $this->__('Remove Filters &amp; Order') . "</a>";
                }

            //Only filters
            } elseif (isset($this->ctrlParams['filters' . $this->_gridId]) && ! isset($this->ctrlParams['order' . $this->_gridId])) {
                if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {

                    $final1 = "<a href=\"javascript:gridAjax('{$this->info['ajax']}','" . $url . "') \">" . $this->__('Remove Filters') . "</a>";

                } else {
                    $final1 = "<a href=\"$url\">" . $this->__('Remove Filters') . "</a>";
                }

            //Only order
            } elseif (! isset($this->ctrlParams['filters' . $this->_gridId]) && isset($this->ctrlParams['order' . $this->_gridId])) {

                if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {

                    $final1 = "<a href=\"javascript:gridAjax('{$this->info['ajax']}','" . $url2 . "') \">" . $this->__('Remove Order') . "</a>";

                } else {
                    $final1 = "<a href=\"$url2\">" . $this->__('Remove Order') . "</a>";
                }
            }

            //Replace values
            $final .= str_replace("{{value}}", $final1, $this->temp['table']->extra());

        //close cycle
        }

        return $final;
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
        if (! is_array($filters)) {
            $this->temp['table']->hasFilters = 0;
            return '';
        }

        //Start the template
        $grid = $this->temp['table']->filtersStart();

        foreach ($filters as $filter) {

            //Check extra fields
            if ($filter['type'] == 'extraField' && $filter['position'] == 'left') {
                //Replace values
                $filterValue = isset($filter['value']) ? $filter['value'] : '';

                $grid .= str_replace('{{value}}', $filterValue . '&nbsp;', $this->temp['table']->filtersLoop());
            }

            $hRowField = isset($this->info['hRow']['field']) ? $this->info['hRow']['field'] : '';

            //Check if we have an horizontal row
            if ((@$filter['field'] != $hRowField && isset($this->info['hRow']['title'])) || ! isset($this->info['hRow']['title'])) {

                if ($filter['type'] == 'field') {
                    //Replace values
                    $grid .= str_replace('{{value}}', $this->_formatField($filter['field']), $this->temp['table']->filtersLoop());
                }
            }

            //Check extra fields from the right
            if ($filter['type'] == 'extraField' && $filter['position'] == 'right') {
                @ $grid .= str_replace('{{value}}', $filter['value'], $this->temp['table']->filtersLoop());
            }

        }

        //Close template
        $grid .= $this->temp['table']->filtersEnd();

        return $grid;
    }

    /**
     * Buil Table titles.
     *
     * @param array $titles
     * @return string
     */
    protected function _buildTitlesTable ($titles)
    {

        //We must now the field that is being ordered. So we can grab the image
        $order = @array_keys($this->order);
        $order2 = @array_keys(array_flip($this->order));

        //The field that is being ordered
        $orderField = $order[0];

        //The oposite order
        $order = strtolower($order2[0]);

        //Lets get the images for defining the order
        $images = $this->temp['table']->images($this->imagesUrl);

        //Iniciate titles template
        $grid = $this->temp['table']->titlesStart();

        if ($orderField === null) {
            if ($this->_getAdapter() == "db") {
                //Lets get the default order using in the query (Zend_Db)
                $queryOrder = $this->_select->getPart('order');
            } else {
                $queryOrder = null;
            }
            if (is_array($queryOrder)) {
                $finalQueryOrder = array();
                foreach ($queryOrder as $value) {

                    if (strpos($value[1], '.' === false)) {
                        $finalQueryOrder = array($value[0], $this->data['table'] . '.' . $value[1]);
                    } else {
                        $finalQueryOrder = $value;
                    }

                    $order = strtolower($value[1]) == 'asc' ? 'desc' : 'asc';
                    $orderField = $finalQueryOrder[0];

                    break;
                }
            }
        }

        foreach ($titles as $title) {

            $imgFinal = '';

            if (isset($title['field']) && $title['field'] == $orderField) {
                $imgFinal = $images[$order];
            }

            //deal with extra field and template
            if ($title['type'] == 'extraField' && $title['position'] == 'left') {
                $grid .= str_replace('{{value}}', $title['value'], $this->temp['table']->titlesLoop());
            }

            $hRowTitle = isset($this->info['hRow']['field']) ? $this->info['hRow']['field'] : '';

            if ((@$title['field'] != $hRowTitle && isset($this->info['hRow']['title'])) || ! isset($this->info['hRow']['title'])) {

                if ($title['type'] == 'field') {

                    $noOrder = isset($this->info['noOrder']) ? $this->info['noOrder'] : '';

                    if ($noOrder == 1) {

                        //user set the noOrder(1) method
                        $grid .= str_replace('{{value}}', $this->__($title['value']), $this->temp['table']->titlesLoop());

                    } else {

                        if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {


                            $link1 = "<a  href=\"javascript:gridAjax('{$this->info['ajax']}','{$title['simpleUrl']}/order{$this->_gridId}/{$title['field']}_DESC')\">{$images['desc']}</a>";
                            $link2 = "<a  href=\"javascript:gridAjax('{$this->info['ajax']}','{$title['simpleUrl']}/order{$this->_gridId}/{$title['field']}_ASC')\">{$images['asc']}</a>";

                            if ($orderField == $title['field'] && $order == 'asc') {
                                $link1 = '';
                            }

                            if ($orderField == $title['field'] && $order == 'desc') {
                                $link2 = '';
                            }

                            $grid .= str_replace('{{value}}', $link2 . $title['value'] . $link1, $this->temp['table']->titlesLoop());

                        } else {
                            //Replace values in the template
                            if (! array_key_exists('url', $title)) {
                                $grid .= str_replace('{{value}}', $title['value'], $this->temp['table']->titlesLoop());
                            } else {

                                $link1 = "<a  href='" . $title['simpleUrl'] . "/order{$this->_gridId}/{$title['field']}_DESC'>{$images['desc']}</a>";
                                $link2 = "<a  href='" . $title['simpleUrl'] . "/order{$this->_gridId}/{$title['field']}_ASC'>{$images['asc']}</a>";

                                if ($orderField == $title['field'] && $order == 'asc') {
                                    $link1 = '';
                                }

                                if ($orderField == $title['field'] && $order == 'desc') {
                                    $link2 = '';
                                }

                                $grid .= str_replace('{{value}}', $link2 . $title['value'] . $link1, $this->temp['table']->titlesLoop());
                            }
                        }
                    }
                }
            }

            //Deal with extra fields
            if ($title['type'] == 'extraField' && $title['position'] == 'right') {
                $grid .= str_replace('{{value}}', $title['value'], $this->temp['table']->titlesLoop());
            }

        }

        //End template
        $grid .= $this->temp['table']->titlesEnd();

        return $grid;

    }

    /**
     * Convert url  params
     *
     * @return array
     */
    protected function _convertComm ()
    {

        $t = explode(";", $this->_comm);

        foreach ($t as $value) {
            $value = explode(":", $value);
            @$final[$value[0]] = $value[1];
        }

        return $final;
    }

    /**
     * Get the list of primary keys from the URL
     *
     * @return string
     */
    protected function _getPkFromUrl ($array = false)
    {

        if (! isset($this->ctrlParams['comm' . $this->_gridId])) {
            return false;
        }

        $param = $this->ctrlParams['comm' . $this->_gridId];
        $explode = explode(';', $param);
        $param = end($explode);
        $param = substr($param, 1, - 1);

        $paramF = explode('-', $param);
        $param = '';

        $returnArray = array();
        foreach ($paramF as $value) {
            $f = explode(':', $value);
            $field_explode = explode('.', $f[0]);
            $field = end($field_explode);

            $param .= " AND  " . $this->_getDb()->quoteIdentifier($field) . '=' . $this->_getDb()->quote($f[1]);

            $returnArray[$f[0]] = $f[1];
        }

        $param = substr($param, 4);

        return $array != FALSE ? $returnArray : $param;

    }


    /**
     * Buil the table
     *
     * @param array $grids | db results
     * @return unknown
     */
    protected function _buildGridTable ($grids)
    {

        $i = 0;
        $grid = '';

        //We have an extra td for the text to remove filters and order
        if (isset($this->ctrlParams['filters' . $this->_gridId]) || isset($this->ctrlParams['order' . $this->_gridId])) {
            $i ++;
        }

        if (isset($this->info['hRow']['title']) && $this->_totalRecords > 0) {

            $bar = $grids;

            $hbar = trim($this->info['hRow']['field']);

            $p = 0;

            foreach ($grids[0] as $value) {
                if ($value['field'] == $hbar) {
                    $hRowIndex = $p;
                }

                $p ++;
            }
            $aa = 0;
        }

        $aa = 0;
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

                if (isset($tia['field'])) {
                    $fi[] = $tia['value'];
                }
            }

            if (count($fi) != count($search)) {
                $diff = count($fi) > count($search) ? count($fi) - count($search) : count($search) - count($fi);

                if (count($search) > count($fi) && $diff == 1) {
                    //Remove first element if a id_
                    array_shift($search);
                }
            }

            if ($search[0] == 'D' || $search[0] == 'E' || $search[0] == 'V') {
                unset($search[0]);
            }

            if (isset($search[1]) && $search[1] == 'E') {
                unset($search[1]);
            }

            $search = $this->_reset_keys($search);

            $finalFields = array_combine($search, $fi);

            //horizontal row
            if (isset($this->info['hRow']['title'])) {

                if ($bar[$aa][$hRowIndex]['value'] != @$bar[$aa - 1][$hRowIndex]['value']) {
                    $i ++;

                    $grid .= str_replace(array("{{value}}", "{{class}}"), array($bar[$aa][$hRowIndex]['value'], @$value['class']), $this->temp['table']->hRow($finalFields));
                }
            }

            $i ++;

            //loop tr
            $grid .= $this->temp['table']->loopStart($finalFields);

            $set = 0;
            foreach ($value as $final) {

                $finalField = isset($final['field']) ? $final['field'] : '';
                $finalHrow = isset($this->info['hRow']['field']) ? $this->info['hRow']['field'] : '';

                if (($finalField != $finalHrow && isset($this->info['hRow']['title'])) || ! isset($this->info['hRow']['title'])) {

                    $set ++;

                    $grid .= str_replace(array("{{value}}", "{{class}}", "{{style}}"), array($final['value'], $final['class'], $final['style']), $this->temp['table']->loopLoop($finalFields));

                }
            }

            $set = null;
            $grid .= $this->temp['table']->loopEnd($finalFields);

            @$aa ++;
        }

        if ($this->_totalRecords == 0) {
            $grid = str_replace("{{value}}", $this->__('No records found'), $this->temp['table']->noResults());
        }

        return $grid;

    }

    /**
     * Biuild the table that handles the query result from sql expressions
     *
     * @param array $sql
     * @return unknown
     */
    protected function _buildSqlexpTable ($sql)
    {

        $grid = '';
        if (is_array($sql)) {
            $grid .= $this->temp['table']->sqlExpStart();

            foreach ($sql as $exp) {
                if ($exp['field'] != @$this->info['hRow']['field']) {
                    $grid .= str_replace(array("{{value}}", '{{class}}'), array($exp['value'], $exp['class']), $this->temp['table']->sqlExpLoop());
                }
            }
            $grid .= $this->temp['table']->sqlExpEnd();

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

        $f = '';

        $url = $this->getUrl(array('start'));

        $actual = (int) isset($this->ctrlParams['start' . $this->_gridId]) ? $this->ctrlParams['start' . $this->_gridId] : 0;

        $ppagina = $this->pagination;
        $result2 = '';

        $pa = $actual == 0 ? 1 : ceil($actual / $ppagina) + 1;

        // Calculate the number of pages
        if ($this->pagination > 0) {
            $npaginas = ceil($this->_totalRecords / $ppagina);
            $actual = floor($actual / $ppagina) + 1;
        } else {
            $npaginas = 0;
            $actual = 0;
        }

        if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"javascript:gridAjax('{$this->info['ajax']}','$url/star{$this->_gridId}t/0')\">1</a>";
        } else {
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"$url/start{$this->_gridId}/0\">1</a>";

        }

        $pag .= ($actual > 5) ? " ... " : "  ";

        if ($npaginas > 5) {
            $in = min(max(1, $actual - 4), $npaginas - 5);
            $fin = max(min($npaginas, $actual + 4), 6);

            for ($i = $in + 1; $i < $fin; $i ++) {
                if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=javascript:gridAjax('{$this->info['ajax']}','$url/start{$this->_gridId}/" . (($i - 1) * $ppagina) . "')> $i </a>";
                } else {
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href='$url/start{$this->_gridId}/" . (($i - 1) * $ppagina) . "'> $i </a>";
                }

            }

            $pag .= ($fin < $npaginas) ? " ... " : "  ";
        } else {

            for ($i = 2; $i < $npaginas; $i ++) {
                if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {

                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=\"javascript:gridAjax('{$this->info['ajax']}','" . $url . "/start{$this->_gridId}/" . (($i - 1) * $ppagina) . "')\">$i</a> ";

                } else {

                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=\"" . $url . "/start{$this->_gridId}/" . (($i - 1) * $ppagina) . "\">$i</a> ";

                }

            }
        }

        if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {
            $pag .= ($actual == $npaginas) ? "<strong>" . $npaginas . "</strong>" : " <a href=\"javascript:gridAjax('{$this->info['ajax']}','$url/start{$this->_gridId}/" . (($npaginas - 1) * $ppagina) . "')\">$npaginas</a> ";

        } else {
            $pag .= ($actual == $npaginas) ? "<strong>" . $npaginas . "</strong>" : " <a href=\"$url/start{$this->_gridId}/" . (($npaginas - 1) * $ppagina) . "\">$npaginas</a> ";

        }

        if ($actual != 1) {

            if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {
                $pag = " <a href=\"javascript:gridAjax('{$this->info['ajax']}','$url/start{$this->_gridId}/0')\">" . $this->__('First') . "</a>&nbsp;&nbsp;<a href=\"javascript:agridAjax('{$this->info['ajax']}','$url/start/" . (($actual - 2) * $ppagina) . "')\">" . $this->__('Previous') . "</a>&nbsp;&nbsp;" . $pag;

            } else {

                $pag = " <a href=\"$url/start/0\">" . $this->__('First') . "</a>&nbsp;&nbsp;<a href=\"$url/start{$this->_gridId}/" . (($actual - 2) * $ppagina) . "\">" . $this->__('Previous') . "</a>&nbsp;&nbsp;" . $pag;
            }

        }

        if ($actual != $npaginas) {
            if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {

                $pag .= "&nbsp;&nbsp;<a href=\"javascript:gridAjax('{$this->info['ajax']}','$url/start{$this->_gridId}/" . ($actual * $ppagina) . "')\">" . $this->__('Next') . "</a> <a href=\"javascript:gridAjax('{$this->info['ajax']}','$url/start{$this->_gridId}/" . (($npaginas - 1) * $ppagina) . "')\">" . $this->__('Last') . "&nbsp;&nbsp;</a>";
            } else {

                $pag .= "&nbsp;&nbsp;<a href=\"$url/start{$this->_gridId}/" . ($actual * $ppagina) . "\">" . $this->__('Next') . "</a>&nbsp;&nbsp;<a href=\"$url/start{$this->_gridId}/" . (($npaginas - 1) * $ppagina) . "\">" . $this->__('Last') . "</a>";
            }

        }

        if ($npaginas > 1 && isset($this->info['limit']) && (int) @$this->info['limit'] == 0) {

            if ($npaginas < 100) {
                // Buil the select form element
                if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {
                    $f = "<select id=\"idf\" onchange=\"javascript:gridAjax('{$this->info['ajax']}','{$url}/start{$this->_gridId}/'+this.value)\">";
                } else {
                    $f = "<select id=\"idf\" onchange=\"window.location='{$url}/start{$this->_gridId}/'+this.value\">";
                }

                for ($i = 1; $i <= $npaginas; $i ++) {
                    $f .= "<option ";
                    if ($pa == $i) {
                        $f .= " selected ";
                    }
                    $f .= " value=\"" . (($i - 1) * $ppagina) . "\">$i</option>\n";
                }
                $f .= "</select>";

            } else {
                #$f ='<input type="text" size="3" style="width:40px !important;">';
                $f = '';
            }

        }

        if ($npaginas > 1 || count($this->export) > 0) {

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

            $images = $this->temp['table']->images($this->imagesUrl);

            $exp = '';


            foreach ($this->getExports() as $export) {

                $export['newWindow'] = isset($export['newWindow']) ? $export['newWindow'] : true;
                $class = isset($export['cssClass']) ? 'class="' . $export['cssClass'] . '"' : '';

                $blank = $export['newWindow'] == false ? '' : "target='_blank'";

                if (isset($this->imagesUrl)) {
                    $export['img'] = $this->imagesUrl . $export['caption'] . '.gif';

                }

                if (isset($export['img'])) {
                    $exp .= "<a title='{$export['caption'] }' $class $blank href='$url/_exportTo{$this->_gridId}/{$export['caption']}'><img alt='{$export['caption']}' src='{$export ['img']}' border='0'></a>";
                } else {
                    $exp .= "<a title='{$export['caption'] }'  $class $blank href='$url/_exportTo{$this->_gridId}/{$export['caption']}'>" . $export['caption'] . "</a>";
                }
            }

            if (isset($this->info['limit']) && (int) @$this->info['limit'] > 0) {
                $result2 = str_replace(array('{{export}}', '{{pagination}}', '{{numberRecords}}'), array($exp, '', (int) $this->info['limit']), $this->temp['table']->pagination());

            } elseif ($npaginas > 1 && count($this->export) > 0) {

                if ($this->pagination == 0) {
                    $pag = '';
                    $f = '';
                }

                $result2 = str_replace(array('{{export}}', '{{pagination}}', '{{numberRecords}}'), array($exp, $pag, $registoActual . ' ' . $this->__('to') . ' ' . $registoFinal . ' ' . $this->__('of') . '  ' . $this->_totalRecords), $this->temp['table']->pagination());

            } elseif ($npaginas < 2 && count($this->export) > 0) {

                if ($this->pagination == 0) {
                    $pag = '';
                    $f = '';
                }
                $result2 .= str_replace(array('{{export}}', '{{pagination}}', '{{numberRecords}}'), array($exp, '', $this->_totalRecords), $this->temp['table']->pagination());

            } elseif (count($this->export) == 0) {

                if ($this->pagination == 0) {
                    $pag = '';
                    $f = '';
                }
                $result2 .= str_replace(array('{{export}}', '{{pagination}}', '{{numberRecords}}'), array('', $pag, $this->_totalRecords), $this->temp['table']->pagination());

            }

        } else {
            return '';
        }

        return $result2;
    }

    /**
     * Remeve the auto-increment field from the array. If a field is auto-increment,
     * we won't let the user insert data on the field
     *
     * @param array $fields
     * @param string $table
     * @return array
     */
    protected function _removeAutoIncrement ($fields, $table)
    {

        $table = $this->_getDescribeTable($table);

        foreach ($table as $value) {

            if ($value['IDENTITY'] == true) {
                $table_fields = $value['COLUMN_NAME'];
            }
        }

        if (array_key_exists($table_fields, $fields)) {
            unset($fields->$table_fields);
        }

        return $fields;
    }


    /**
     * Here we go....
     *
     * @return string
     */
    function deploy ()
    {


        $url = $this->getUrl('comm');

        $this->_view = $this->getView();
        if ($this->_adapter == 'db') {
            //Process form, if necessary, before query
            self::_processForm();
        }

        parent::deploy();

        if (! $this->temp['table'] instanceof Bvb_Grid_Template_Table_Table) {
            $this->setTemplate('table', 'table', $this->_templateParams);
        } else {
            $this->setTemplate($this->temp['table']->options['name'], 'table', $this->_templateParams);
        }


        // The extra fields, they are not part of database table.
        // Usefull for adding links (a least for me :D )
        $grid = $this->_printScript();

        $images = $this->temp['table']->images($this->imagesUrl);

        if ($this->allowDelete == 1 || $this->allowEdit == 1 || (is_array($this->_detailColumns) && $this->_isDetail == false)) {
            $pkUrl = $this->_getPrimaryKey();
            $urlFinal = '';

            if (is_array($pkUrl)) {
                foreach ($pkUrl as $value) {
                    $urlFinal .= $value . ':{{' . $value . '}}-';
                }
            }

            $urlFinal = trim($urlFinal, '-');

        }

        if ($this->allowEdit == 1) {
            if (! is_array($this->extra_fields)) {
                $this->extra_fields = array();
            }
            // Remove the unnecessary URL params
            #$removeParams = array ('filters', 'add' );
            $removeParams = array('filters', 'add', 'edit', 'comm');

            foreach (array_keys($this->info['edit']['fields']) as $key) {
                array_push($removeParams, $key);
            }
            $url = $this->getUrl($removeParams);

            if ($this->allowEdit == 1 && isset($this->info['ajax']) && $this->info['ajax'] !== false) {
                $urlEdit = $this->_baseUrl . '/' . str_replace("/gridmod" . $this->_gridId . "/ajax", "", $url);
            } else {
                $urlEdit = $url;
            }

            array_unshift($this->extra_fields, array('position' => 'left', 'name' => 'E', 'decorator' => "<a href=\"$urlEdit/edit" . $this->_gridId . "/1/comm" . $this->_gridId . "/" . "mode:edit;[" . $urlFinal . "]\" > " . $images['edit'] . "</a>", 'edit' => true));

        }




        if ($this->allowDelete) {
            if (! is_array($this->extra_fields)) {
                $this->extra_fields = array();
            }

            array_unshift($this->extra_fields, array('position' => 'left', 'name' => 'D', 'decorator' => "<a href=\"#\" onclick=\"_" . $this->_gridId . "confirmDel('" . $this->__('Are you sure?') . "','$url/comm" . $this->_gridId . "/" . "mode:delete;[" . $urlFinal . "]');\" > " . $images['delete'] . "</a>", 'delete' => true));
        }

     if (is_array($this->_detailColumns) && $this->_isDetail == false) {
            if (! is_array($this->extra_fields)) {
                $this->extra_fields = array();
            }

            $removeParams = array('filters', 'add', 'edit', 'comm');
            $url = $this->getUrl($removeParams);

            array_unshift($this->extra_fields, array('position' => 'left', 'name' => 'V', 'decorator' => "<a href=\"$url/gridDetail" . $this->_gridId . "/1/comm" . $this->_gridId . "/" . "mode:view;[" . $urlFinal . "]\" > " . $images['detail'] . "</a>", 'detail' => true));

        }

        if (strlen($this->message) > 0) {
            $grid .= str_replace("{{value}}", $this->message, $this->temp['table']->formMessage($this->messageOk));
        }

        if (((isset($this->ctrlParams['edit' . $this->_gridId]) && $this->ctrlParams['edit' . $this->_gridId] == 1) || (isset($this->ctrlParams['add' . $this->_gridId]) && $this->ctrlParams['add' . $this->_gridId] == 1) || (isset($this->ctrlParams['double_tables' . $this->_gridId]) && $this->ctrlParams['double_tables' . $this->_gridId] == 1))) {

            if (($this->allowAdd == 1 || $this->allowEdit == 1) && $this->_noForm == 0) {

                // Remove the unnecessary URL params
                $removeParams = array('filters', 'add');

                foreach (array_keys($this->info['edit']['fields']) as $key) {
                    array_push($removeParams, $key);
                }

                $url = $this->getUrl($removeParams);

                $grid .= $this->_form;

                $this->_showsForm = true;

            }
        }

        if ( ((! isset($this->ctrlParams['edit' . $this->_gridId]) || $this->ctrlParams['edit' . $this->_gridId] != 1) && (! isset($this->ctrlParams['add' . $this->_gridId]) || $this->ctrlParams['add' . $this->_gridId] != 1)) || $this->_noForm == 1) {

            if ($this->_isDetail == true) {

                $columns = parent::_buildGrid();

                $grid = $this->temp['table']->globalStart();

                foreach ($columns[0] as $value)
                {
                    if(!isset($value['field']))
                    {
                        continue;
                    }

                    if(isset($this->data['fields'][$value['field']]['title']))
                    {
                        $value['field'] = $this->data['fields'][$value['field']]['title'];
                    }else{
                        $value['field'] = ucwords(str_replace('_',' ',$value['field']));
                    }

                    $grid .= str_replace(array('{{field}}','{{value}}'),array($value['field'],$value['value']),$this->temp['table']->detail());
                }

                $grid .= str_replace('{{url}}',$this->getUrl(array('gridDetail','comm')),$this->temp['table']->detailEnd());
                $grid .= $this->temp['table']->globalEnd();

            } else {

                $grid .= self::_buildHeader();
                $grid .= self::_buildTitlesTable(parent::_buildTitles());
                $grid .= self::_buildFiltersTable(parent::_buildFilters());
                $grid .= self::_buildGridTable(parent::_buildGrid());
                $grid .= self::_buildSqlexpTable(parent::_buildSqlExp());
                $grid .= self::_pagination();
            }

            $this->_showsGrid = true;
        }

        $grid .= $this->temp['table']->globalEnd();

        $gridId = $this->_gridId;

        if (isset($this->ctrlParams['gridmod' . $this->_gridId]) && $this->ctrlParams['gridmod' . $this->_gridId] == 'ajax' && $this->info['ajax'] !== false) {

            echo $grid;
            die();
            return '';
        }
        if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {
            $gridId = $this->info['ajax'];
        }

        $grid = "<div id='{$gridId}'>" . $grid . "</div>";

        $this->_deploymentContent = $grid;
        return $this;

    }

    function __toString ()
    {
        if (is_null($this->_deploymentContent)) {
            die('You must explicity call the deploy() method before printing the object');
            # self::deploy();
        }

        return $this->_deploymentContent;
    }

    protected function _printScript ()
    {

        if ($this->getInfo('ajax') !== false) {
            $useAjax = 1;
        } else {
            $useAjax = 0;
        }

        $script = "";

        if ($this->allowDelete == 1) {

            $script .= " function _" . $this->_gridId . "confirmDel(msg, url)
        {
            if(confirm(msg))
            {
            ";
            if ($useAjax == 1) {
                $script .= "window.location = '" . $this->_baseUrl . "/'+url.replace('/gridmod" . $this->_gridId . "/ajax','');";
            } else {
                $script .= "window.location = url;";
            }

            $script .= "

            }else{
                return false;
            }
        }";

        }
        if ($useAjax == 1) {
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
            texto=xmlhttp.responseText;
            document.getElementById(ponto).innerHTML=texto;
        }else{

        }
    }
    xmlhttp.send(null);
}
";
        }

        if (! isset($this->info['noFilters']) || $this->info['noFilters'] != 0) {

            $script .= "function _" . $this->_gridId . "gridChangeFilters(fields,url,Ajax)
{
    var Ajax = \"1\";
    var fieldsArray = fields.split(\",\");
    var filtro = new Array;

    for (var i = 0; i < fieldsArray.length -1; i++)
    {
        value = document.getElementById(fieldsArray[i]).value;\n";
            $script .= " value = value.replace(/[\"]/,''); ";
            $script .= " value = value.replace(/[\\\]/,''); ";
            $script .= " fieldsArray[i] = fieldsArray[i].replace(/filter_" . $this->_gridId . "/,'filter_'); ";
            $script .= "\nfiltro[i] = '\"'+encodeURIComponent(fieldsArray[i])+'\":\"'+encodeURIComponent(value)+'\"';
    }

    filtro = \"{\"+filtro+\"}\";
    ";

            if ($useAjax == 1) {
                $script .= "gridAjax('{$this->info['ajax']}',url+'/filters{$this->_gridId}/'+filtro);";
            } else {
                $script .= "window.location=url+'/filters{$this->_gridId}/'+filtro;";
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
     *@var Bvb_Grid_Form
     * @return unknown
     */
    function addForm ($form)
    {

        if (is_null($form->getModel()) && count($form->getElements()) == 0) {
            if (is_null($this->_model)) {
                throw new Bvb_Grid_Exception('Please set the model to use');
            }

            if ($this->getDbServerName() != 'mysql') {
                throw new Bvb_Grid_Exception('At this moment only models using MySQL can be used for scaffolding');
            }
            $form->setModel($this->_model);

            $this->_formHasModel = true;

            foreach ($form->getElements() as $key => $value) {

                if (isset($this->data['fields'][$key]['title'])) {
                    $value->setLabel($this->data['fields'][$key]['title']);
                }

                if (isset($this->data['fields'][$key]['tooltipField'])) {
                    $value->setDescription($this->data['fields'][$key]['tooltipField']);
                }
            }

        } elseif (count($form->getElements()) > 0) {

            foreach ($form->getElements() as $key => $value) {
                $value->setDecorators($form->elementDecorators);
            }
        }


        $form->setDecorators($form->formDecorator);

        $form->addElement('submit', 'form_submit' . $this->_gridId, array('label' => 'Submit', 'class' => 'submit', 'decorators' => $form->buttonHidden));
        $form->addElement('hidden', '_form_edit' . $this->_gridId, array('value' => 1, 'decorators' => $form->buttonHidden));

        $url = $this->getUrl(array_merge(array('add', 'edit', 'comm', 'form_reset'), array_keys($form->getElements())));

        $form->addElement('button', 'form_reset' . $this->_gridId, array('onclick' => "window.location='$url'", 'label' => 'Cancel', 'class' => 'reset', 'decorators' => $form->buttonHidden));
        $form->addDisplayGroup(array('form_submit' . $this->_gridId, 'form_reset' . $this->_gridId), 'buttons', array('decorators' => $form->groupDecorators));

        $form->setAction($this->getUrl(array_keys($form->getElements())));

        $this->_form = $form;

        $form = $this->_object2array($form);

        $fieldsGet = $form['fields'];
        $fields = array();

        if (isset($form['options']['callbackBeforeDelete'])) {
            $this->_callbackBeforeDelete = $form['options']['callbackBeforeDelete'];
        }

        if (isset($form['options']['callbackBeforeInsert'])) {
            $this->_callbackBeforeInsert = $form['options']['callbackBeforeInsert'];
        }

        if (isset($form['options']['callbackBeforeUpdate'])) {
            $this->_callbackBeforeUpdate = $form['options']['callbackBeforeUpdate'];
        }

        if (isset($form['options']['callbackAfterDelete'])) {
            $this->_callbackAfterDelete = $form['options']['callbackAfterDelete'];
        }

        if (isset($form['options']['callbackAfterInsert'])) {
            $this->_callbackAfterInsert = $form['options']['callbackAfterInsert'];
        }

        if (isset($form['options']['callbackAfterUpdate'])) {
            $this->_callbackAfterUpdate = $form['options']['callbackAfterUpdate'];
        }

        if (is_array($fieldsGet)) {
            foreach ($fieldsGet as $value) {
                $fields[$value['options']['field']] = $value['options'];
            }
        }


        $options = $form['options'];

        $this->info['double_tables'] = isset($options['double_tables']) ? $options['double_tables'] : '';

        if (isset($options['delete'])) {
            if ($options['delete'] == 1) {
                $this->delete = array('allow' => 1);
                if (isset($options['onDeleteAddWhere'])) {
                    $this->info['delete']['where'] = $options['onDeleteAddWhere'];
                }
            }
        }

        @$this->info['delete']['cascadeDelete'] = $form['cascadeDelete'];

        if ($options['add'] == 1 && $options['add'] == 1) {
            if (! isset($options['button'])) {
                $options['button'] = 0;
            }

            $this->add = array('allow' => 1, 'button' => $options['button'], 'fields' => $fields, 'force' => @$options['onAddForce']);
        }

        if (isset($options['edit']) && $options['edit'] == 1) {
            $this->edit = array('allow' => 1, 'fields' => $fields, 'force' => @$options['onEditForce']);
        }
        if (isset($options['onUpdateAddWhere'])) {
            $this->info['edit']['where'] = $options['onUpdateAddWhere'];
        }
        return $this;
    }

    /**
     * Field type on the filters area. If the field type is enum, build the options
     * Also, we first need to check if the user has defined values to presente.
     * If set, this values override the others
     *
     * @param string $campo
     * @param string $valor
     * @return string
     */
    protected function _formatField ($campo)
    {

        $valor = $campo;

        if (isset($this->data['fields'][$valor]['search']) && $this->data['fields'][$valor]['search'] == false) {
            return '';
        }

        if ($this->_getAdapter() == 'db') {
            //check if we need to load  fields for filters
            if (isset($this->filters[$valor]['distinct']) && is_array($this->filters[$valor]['distinct']) && isset($this->filters[$valor]['distinct']['field'])) {

                $distinctField = $this->filters[$valor]['distinct']['field'];
                $distinctValue = $this->filters[$valor]['distinct']['name'];

                $distinct = clone $this->_select;

                $distinct->reset(Zend_Db_Select::COLUMNS);
                $distinct->reset(Zend_Db_Select::ORDER);
                $distinct->reset(Zend_Db_Select::LIMIT_COUNT);
                $distinct->reset(Zend_Db_Select::LIMIT_OFFSET);

                $distinct->columns(array('field' => new Zend_Db_Expr("DISTINCT({$this->data['fields'][$distinctField]['field']})")));
                $distinct->columns(array('value' => $this->data['fields'][$distinctValue]['field']));
                $distinct->order(' value ASC');

                $result = $distinct->query(Zend_Db::FETCH_ASSOC);

                $final = $result->fetchAll();

                $final = $this->_convertResultSetToArrayKeys($final);

                $this->filters[$valor]['values'] = $final;
            }

        }

        if ($this->_adapter == 'array' && @in_array('distinct', $this->filters[$valor])) {
            $this->filters[$valor]['values'] = $this->_builFilterFromArray($campo);
        }

        //Remove unwanted url params
        $url = $this->getUrl(array('filters', 'start', 'comm', '_exportTo'));

        $fieldsSemAsFinal = $this->data['fields'];

        if (isset($fieldsSemAsFinal[$campo]['searchField'])) {
            $nkey = $fieldsSemAsFinal[$campo]['searchField'];
            @$this->_filtersValues[$campo] = $this->_filtersValues[$nkey];
        }

        if ($this->_getAdapter() == 'db') {
            $tAlias = explode('.', $this->data['fields'][$campo]['field']);
            $tableName = $this->_tablesList[reset($tAlias)]['tableName'];
            $table = $this->_getDescribeTable($tableName);
        }

        @$tipo = $table[$campo];
        $tipo = $tipo['DATA_TYPE'];
        $help_javascript = '';

        if (substr($tipo, 0, 4) == 'enum') {
            $enum = str_replace(array('(', ')'), array('', ''), $tipo);
            $tipo = 'enum';
        }

        $i = 0;
        foreach (array_keys($this->filters) as $value) {

            if (! isset($this->data['fields'][$value]['search'])) {
                $this->data['fields'][$value]['search'] = true;
            }

            $hRow = isset($this->data['fields'][$value]['hRow']) ? $this->data['fields'][$value]['hRow'] : '';

            if ($this->_displayField($value) && $hRow != 1 && $this->data['fields'][$value]['search'] != false) {
                $help_javascript .= "filter_" . $this->_gridId . $value . ",";
            }
        }

        $help_javascript = str_replace(".", "bvbdot", $help_javascript);
        $attr['onChange'] = "_" . $this->_gridId . "gridChangeFilters('$help_javascript','$url');";

        $opcoes = array();
        if (isset($this->filters[$campo])) {
            $opcoes = $this->filters[$campo];
        }

        if (isset($opcoes['style']) && strlen($opcoes['style']) > 1) {
            $attr['style'] = $opcoes['style'];
        } else {
            $attr['style'] = " width:95% ";
        }

        $attr['id'] = "filter_" . $this->_gridId . $campo;

        $selected = null;
        if (isset($opcoes['values']) && is_array($opcoes['values'])) {

            $tipo = 'invalid';
            $values = array();
            $values[''] = '--' . $this->__('All') . '--';

            $avalor = $opcoes['values'];

            foreach ($avalor as $key => $value) {
                if (isset($this->_filtersValues[$campo]) && $this->_filtersValues[$campo] == $key) {
                    $selected = $key;
                }

                $values[$key] = $value;
            }

            $valor = $this->_view->formSelect($campo, $selected, $attr, $values);

        }

        switch ($tipo) {
            case 'invalid':
                break;
            case 'enum':
                $values = array();
                $values[''] = '--' . $this->__('All') . '--';
                $avalor = explode(",", substr($enum, 4));

                foreach ($avalor as $value) {
                    $value = substr($value, 1);
                    $value = substr($value, 0, - 1);

                    if (isset($this->_filtersValues[$campo]) && $this->_filtersValues[$campo] == $value) {
                        $selected = $value;
                    }
                    $values[$value] = $value;
                }

                $valor = $this->_view->formSelect($campo, $selected, $attr, $values);

                break;
            default:
                $valor = $this->_view->formText($campo, @$this->_filtersValues[$campo], $attr);
                break;
        }

        return $valor;
    }


    /**
     * Set view object
     *
     * @param Zend_View_Interface $view view object to use
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function setView (Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Retrieve view object
     *
     * If none registered, attempts to pull from ViewRenderer.
     *
     * @return Zend_View_Interface|null
     */
    public function getView ()
    {
        if (null === $this->_view) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->setView($viewRenderer->view);
        }

        return $this->_view;
    }

    /**
     * Let the user know waht will be displayed.
     * @param $option (grid|form)
     * @return array|bool
     */
    public function willShow ($option = 'null')
    {

        if (null !== $option && in_array($option, array('grid', 'form'))) {

            if ($option == 'form')
                return $this->_showsForm;
            else
                return $this->_showsGrid;

        }

        $return = array();
        if ($this->_showsGrid == true) {
            $return[] = 'grid';
        }

        if ($this->_showsForm == true) {
            $return[] = 'form';
        }


        return $return;

    }

}

