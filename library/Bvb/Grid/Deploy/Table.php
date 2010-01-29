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

class Bvb_Grid_Deploy_Table extends Bvb_Grid_Data implements Bvb_Grid_Deploy_Interface
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
     * [PT] Se o formulário foi submetido com sucesso
     *
     * @var bool
     */
    protected $formSuccess = 0;

    /**
     * [PT] If the form has been submited
     *
     * @var bool
     */
    protected $formPost = 0;

    /**
     * [PT] Form values
     */
    protected $_formValues = array();

    /**
     * [PT] Form error messages
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
     * To edit, add, or delete records, a user must be authenticated, so we instanciate
     * it here.
     *
     * @param array $data
     */
    function __construct ($options)
    {

        $this->_setRemoveHiddenFields(true);

        parent::__construct($options);

        // Add Zend_Validate and Zend_Filter to the form element
        $this->addElementDir('Zend/Filter', 'Zend_Filter', 'filter');
        $this->addElementDir('Zend/Validate', 'Zend_Validate', 'validator');

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
            $dec = isset($this->ctrlParams['comm']) ? $this->ctrlParams['comm'] : '';
            $this->_comm = $dec;
        }

        /**
         * Remove if there is something to remove
         */
        if ($this->allowDelete) {
            self::_deleteRecord($dec);

        }

        //Check if the request method is POST
        if (Zend_Controller_Front::getInstance()->getRequest()->isPost() && Zend_Controller_Front::getInstance()->getRequest()->getPost('_form_edit') == 1) {

            $param = Zend_Controller_Front::getInstance()->getRequest();

            $opComm = isset($this->ctrlParams['comm']) ? $this->ctrlParams['comm'] : '';
            $op_query = self::_convertComm($opComm);

            $get_mode = isset($op_query['mode']) ? $op_query['mode'] : '';
            $mode = $get_mode == 'edit' ? 'edit' : 'add';

            // We must know what fields to get with getPost(). We only gonna get the fieds
            // That belong to the database table. We must ensure we process the right data.
            // So we also must verify if have been defined the fields to process
            if (is_array($this->info[$mode]['fields'])) {
                $fields = array();

                foreach ($this->info[$mode]['fields'] as $key => $value) {
                    $fields[$key] = $key;
                }

            } else {
                $fields = parent::_getFields($mode, $this->data['table']);
            }

            $queryUrl = $this->_getPkFromUrl();

            // Apply filter and validators. Firtst we apply the filters
            foreach ($fields as $value) {

                $this->_formValues[$value] = $param->getPost($value);

                $fieldType = $this->_getFieldType($value, $this->data['table']);

                if (substr($fieldType, 0, 3) != 'set') {

                    $result = $this->_applyFilters($param->getPost($value), $value, $mode);

                    $result = $this->_validate($result, $value, $mode);

                } else {

                    $possibleValuesForSetField = explode(",", str_replace(array('(', ')', '\'', 'set'), array('', '', '', ''), $fieldType));

                    if (is_array($param->getPost($value))) {

                        $finalValue = array_intersect($possibleValuesForSetField, $param->getPost($value));
                    } else {
                        $finalValue = null;
                    }

                    if (count($finalValue) > 0) {
                        $result = implode(',', $finalValue);
                    } else {
                        $result = '';
                    }
                }

                $final[$value] = $result;

            }

            // If pass validation
            if ($this->_failedValidation !== true) {

                // Check ig the user has defined "force" fields. If so we need to merge them
                // With the ones we get from the form process
                $force = $this->info[$mode]['force'];
                if (is_array($force)) {
                    $final_values = array_merge($final, $force);

                } else {
                    $final_values = $final;
                }

                $pk2 = parent::_getPrimaryKey();

                foreach ($pk2 as $value) {
                    unset($final_values[$value]);
                }

                //Deal with readonly and disabled attributes.
                //Also check for security issues
                foreach (array_keys($final_values) as $key) {

                    if (isset($this->info['add']['fields'][$key]['attributes']['disabled'])) {
                        unset($final_values[$key]);
                    }

                    if ($mode == 'add') {

                        if (isset($this->info['add']['fields'][$key]['attributes']['readonly'])) {
                            $final_values[$key] = '';
                        }

                    }

                    if ($mode == 'edit') {

                        if (isset($this->info['add']['fields'][$key]['attributes']['readonly'])) {
                            unset($final_values[$key]);
                        }
                    }
                }

                // Process data
                if ($mode == 'add' && is_array($final_values)) {

                    try {

                        if (null !== $this->_callbackBeforeInsert) {
                            call_user_func_array($this->_callbackBeforeInsert, $final_values);
                        }
                        $this->_db->insert($this->data['table'], $final_values);

                        if (null !== $this->_callbackAfterInsert) {
                            call_user_func_array($this->_callbackAfterInsert, $final_values);
                        }

                        $this->message = $this->__('Record saved');
                        $this->messageOk = true;

                    }
                    catch (Zend_Exception $e) {
                        $this->messageOk = FALSE;
                        $this->message = $this->__('Error saving record =>') . $e->getMessage();
                    }

                }

                // Process data
                if ($mode == 'edit' && is_array($final_values)) {

                    $where = isset($this->info['edit']['where']) ? " AND " . $this->info['edit']['where'] : '';

                    try {

                        if (null !== $this->_callbackBeforeUpdate) {
                            call_user_func_array($this->_callbackBeforeUpdate, $final_values);
                        }

                        $this->_db->update($this->data['table'], $final_values, $queryUrl . $where);

                        if (null !== $this->_callbackAfterUpdate) {
                            call_user_func_array($this->_callbackAfterUpdate, $final_values);
                        }

                        $this->message = $this->__('Record saved');
                        $this->messageOk = true;

                    }
                    catch (Zend_Exception $e) {
                        $this->messageOk = FALSE;
                        $this->message = $this->__('Error updating record =>') . $e->getMessage();
                    }

                    //No need to show the form
                    $this->_editNoForm = 1;

                    unset($this->ctrlParams['comm']);
                    unset($this->ctrlParams['edit']);

                }

                if ($this->cache['use'] == 1) {
                    $this->cache['instance']->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->cache['tag']));
                }
                $this->formSuccess = 1;

            } else {

                $this->message = $this->__('Validation failed');
                $this->messageOk = false;
                $this->formSuccess = 0;
                $this->formPost = 1;

                $final_values = null;

            }

            // Unset all params so we can have a more ckean URl when calling $this->getUrl
            if (is_array($final_values)) {
                foreach ($final_values as $key => $value) {
                    unset($this->ctrlParams[$key]);
                }
            }
        }

        if ($this->formSuccess == 1) {
            foreach ($this->ctrlParams as $key => $value) {
                if ($key != 'module' && $key != 'controller' && $key != 'action') {
                    unset($this->ctrlParams[$key]);
                }
            }

            $this->_comm = false;
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
     * Apply filter susing the Zend Framework set.
     *
     * @param string $value
     * @param string $field
     * @param string $mode
     * @return string
     */
    protected function _applyFilters ($value, $field, $mode)
    {

        $filters = isset($this->info[$mode]['fields'][$field]['filters']) ? $this->info[$mode]['fields'][$field]['filters'] : '';

        if (is_array($filters)) {
            //It has filters to apply. Get dirs...
            foreach ($filters as $func) {
                $class = $this->_elements['filter']->load($func);
                $t = new $class();
                $value = $t->filter($value);
            }
        }

        return $value;
    }

    /**
     * Validate fields using the set on he Zend Framework
     *
     * @param string $value
     * @param string $field
     * @param string $mode
     * @return string
     */
    protected function _validate ($value, $field, $mode = 'edit')
    {

        //Array with allowed values
        $values = isset($this->info[$mode]['fields'][$field]['values']) ? $this->info[$mode]['fields'][$field]['values'] : '';

        //Array of validators
        $validators = isset($this->info[$mode]['fields'][$field]['validators']) ? $this->info[$mode]['fields'][$field]['validators'] : '';

        //Check if the value is in the allowed values array
        if (is_array($values) && $mode == 'edit') {

            if (! in_array($value, $values) && ! array_key_exists($value, $values)) {
                $this->_failedValidation = true;
                return false;
            }

        } elseif (is_array($validators)) {

            foreach ($validators as $key => $func) {

                if (is_array($validators[$key])) {
                    $func = $key;
                }

                $class = $this->_elements['validator']->load($func);

                // If an array, means the Validator receives arguments
                if (is_array($validators[$key])) {
                    // If an array, means the Validator receives arguments
                    $refObj = new ReflectionClass($class);
                    $t = $refObj->newInstanceArgs($validators[$key]);
                    $return = $t->isValid($value);

                    if ($return === false) {
                        $this->_failedValidation = true;
                        foreach ($t->getMessages() as $messageId => $message) {
                            $this->_formMessages[$field][] = array($messageId => $message);
                        }
                        return false;
                    }

                } else {

                    $t = new $class();
                    $return = $t->isValid($value);

                    if ($return === false) {
                        $this->_failedValidation = true;
                        foreach ($t->getMessages() as $messageId => $message) {
                            $this->_formMessages[$field][] = array($messageId => $message);
                        }
                        return false;
                    }
                }

            }

        }

        return $value;

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
        $id = $this->_db->quoteIdentifier($pkArray[0]);

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

                        $select = $this->_db->select();
                        $select->from($this->data['table'], array('total' => $parentField));
                        $select->where($this->_getPkFromUrl(true));
                        $final = $select->query(Zend_Db::FETCH_ASSOC);
                        $result = $final->fetchAll();

                        $finalValue = $result[0];
                    } else {
                        $finalValue = $final['id'];
                    }

                    $resultDelete = $this->_db->delete($value['table'], $this->_db->quoteIdentifier($value['childField']) . $operand . $this->_db->quote($finalValue));

                }
            }

            if (null !== $this->_callbackBeforeDelete) {
                call_user_func_array($this->_callbackBeforeDelete, $this->_getPkFromUrl(false) . $where);
            }

            $resultDelete = $this->_db->delete($this->data['table'], $this->_getPkFromUrl(false) . $where);

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

        unset($this->ctrlParams['comm']);

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

        $url = parent::_getUrl(array('comm', 'edit', 'filters', 'order'));

        $final = '';

        if ($this->_adapter == 'db') {
            if (($this->getInfo('double_tables') == 0 && @$this->ctrlParams['add'] != 1 && @$this->ctrlParams['edit'] != 1) && $this->_getPrimaryKey() && @$this->info['add']['allow'] == 1 && @$this->info['add']['button'] == 1 && @$this->info['add']['no_button'] != 1) {

                $final = "<div class=\"addRecord\" ><a href=\"$url/add/1\">" . $this->__('Add Record') . "</a></div>";
            }
        }

        //Template start
        $final .= $this->temp['table']->globalStart();

        /**
         * We must check if there is a filter set or an order, to show the extra th on top
         */


        if (isset($this->ctrlParams['filters' . $this->_id]) || isset($this->ctrlParams['order' . $this->_id])) {

            $url = $this->_getUrl('filters', 'nofilters');
            $url2 = $this->_getUrl('order');
            $url3 = $this->_getUrl(array('filters', 'order', 'nofilters'));

            if (is_array($this->_defaultFilters)) {
                $url .= '/nofilters/1';
                $url3 .= '/nofilters/1';
            }

            $this->temp['table']->hasExtraRow = 1;


            //Filters and order
            if (isset($this->ctrlParams['filters' . $this->_id]) and isset($this->ctrlParams['order' . $this->_id])) {
                if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {

                    $final1 = "<a href=\"javascript:gridAjax('{$this->info['ajax']}','" . $url . "')\">" . $this->__('Remove Filters') . "</a> | <a href=\"javascript:gridAjax('{$this->info['ajax']}','" . $url2 . "')\">" . $this->__('Remove Order') . "</a> | <a href=\"javascript:gridAjax('{$this->info['ajax']}','" . $url3 . "')\">" . $this->__('Remove Filters &amp; Order') . "</a>";

                } else {
                    $final1 = "<a href=\"$url\">" . $this->__('Remove Filters') . "</a> | <a href=\"$url2\">" . $this->__('Remove Order') . "</a> | <a href=\"$url3\">" . $this->__('Remove Filters &amp; Order') . "</a>";
                }

            //Only filters
            } elseif (isset($this->ctrlParams['filters' . $this->_id]) && ! isset($this->ctrlParams['order' . $this->_id])) {
                if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {

                    $final1 = "<a href=\"javascript:gridAjax('{$this->info['ajax']}','" . $url . "') \">" . $this->__('Remove Filters') . "</a>";

                } else {
                    $final1 = "<a href=\"$url\">" . $this->__('Remove Filters') . "</a>";
                }

            //Only order
            } elseif (! isset($this->ctrlParams['filters' . $this->_id]) && isset($this->ctrlParams['order' . $this->_id])) {

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
                            $grid .= str_replace('{{value}}', "<a href=\"javascript:gridAjax('{$this->info['ajax']}','" . @$title['url'] . "') \">" . $title['value'] . $imgFinal . "</a>", $this->temp['table']->titlesLoop());

                        } else {
                            //Replace values in the template
                            if (! array_key_exists('url', $title)) {
                                $grid .= str_replace('{{value}}', $title['value'], $this->temp['table']->titlesLoop());
                            } else {
                                $link1 = "<a  href='" . $title['simpleUrl'] . "/order{$this->_id}/{$title['field']}_DESC'>{$images['desc']}</a>";
                                $link2 = "<a  href='" . $title['simpleUrl'] . "/order{$this->_id}/{$title['field']}_ASC'>{$images['asc']}</a>";

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
     * Build the form elements for the edit or add action
     * This is different from the filters
     *
     * @param string $field | The database field that we are processing
     * @param string $inicial_value | the inicial field value
     * @param srint $mod edit|add
     * @param string $fieldValue | This saves the fields values in case o failed validation
     * @return string
     */
    protected function _buildFormElement ($field, $inicial_value = '', $mod = 'edit', $fieldValue = '')
    {

        $view = $this->_view->view;

        $fieldRaw = $field;
        //If not editing, remove the initial value, otherwise it will assume the fields names
        if ($mod != 'edit') {
            $field = $inicial_value;
            if ($this->formSuccess == 0) {
                $inicial_value = $fieldValue;
            } else {
                $inicial_value = '';
            }
        }

        //Get table desc to known to field type
        $table = parent::_getDescribeTable($this->data['table'], $fieldRaw);

        $field = explode('.', $field);
        $field = end($field);

        @$tipo = $table[$field];

        $tipo = $tipo['DATA_TYPE'];

        if (substr($tipo, 0, 4) == 'enum') {
            $enum = str_replace(array('(', ')'), array('', ''), $tipo);
            $tipo = 'enum';
        }

        //Let's get the possible values for the set Type
        if (substr($tipo, 0, 3) == 'set') {
            $set = str_replace(array('(', ')', '\'', 'set'), array('', '', '', ''), $tipo);
            $tipo = 'set';
        }

        @$options = $this->info[$mod]['fields'][$field];

        $selected = null;

        //If the field as options
        $attr = array();

        if (isset($options['attributes']['type'])) {
            $tipo = $options['attributes']['type'];
        }

        if (! is_array(@$options['attributes'])) {
            $options['attributes'] = array();

            if (! in_array('style', @$options['attributes'])) {
                $attr['style'] = 'width:95%';
            }
        } else {

            if (! array_key_exists('style', @$options['attributes'])) {
                $attr['style'] = 'width:95%';
            }
        }

        if (@is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key => $value) {
                $attr[$key] = $value;
            }
        }

        //User wants to specify the values
        if (isset($options['values'])) {

            if (is_array($options['values'])) {

                //Declare as invalid to skip the swith
                $tipo = 'invalid';
                $avalor = $options['values'];

                foreach ($avalor as $key => $value) {

                    //check for select value
                    if ($mod == 'edit') {
                        $selected = $inicial_value == $key ? $inicial_value : "";
                    } elseif (key_exists('value', $options)) {
                        $selected = $options['value'] == $key ? $options['value'] : "";
                    } else {
                        $selected = null;
                    }

                    $values[$key] = $value;
                }

                $valor = $view->formSelect($fieldRaw, $selected, $attr, $avalor);

            }
        }

        switch ($tipo) {

            case 'invalid':
                break;
            case 'set':

                //Build options based on set from database, if not defined by the user
                $avalor = explode(",", $set);
                $setValues = explode(',', $inicial_value);
                $valor = $view->formMultiCheckbox($fieldRaw, $setValues, $attr, $avalor);

                break;
            case 'enum':

                //Build options based on enum from database, if not defined by the user
                $avalor = explode(",", substr($enum, 4));
                $values = array();

                foreach ($avalor as $value) {
                    if ($value == "'" . $inicial_value . "'") {
                        $selected = $inicial_value;
                    }
                    $value = substr($value, 1);
                    $value = substr($value, 0, - 1);
                    $values[$value] = $value;
                }
                $valor = $view->formSelect($fieldRaw, $selected, $attr, $values);

                break;
            case 'text':
            case 'textarea':
                $valor = $view->formTextarea($fieldRaw, $view->escape($inicial_value), $attr);
                break;
            case 'password':
                $valor = $view->formPassword($fieldRaw, $view->escape($inicial_value), $attr);
                break;
            default:
                $valor = $view->formText($fieldRaw, $view->escape($inicial_value), $attr);

                break;
        }

        return $valor;

    }

    /**
     * Get the list of primary keys from the URL
     *
     * @return string
     */
    protected function _getPkFromUrl ($array = false)
    {

        if (! isset($this->ctrlParams['comm'])) {
            return false;
        }

        $param = $this->ctrlParams['comm'];
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

            $param .= " AND  " . $this->_db->quoteIdentifier($field) . '=' . $this->_db->quote($f[1]);

            $returnArray[$f[0]] = $f[1];
        }

        $param = substr($param, 4);

        return $array != FALSE ? $returnArray : $param;

    }

    /**
     * The table to show when editing or adding records
     *
     * @return string
     */
    protected function _gridForm ()
    {

        $view = $this->_view->view;

        // Remove the unnecessary URL params
        $url = parent::_getUrl(array('comm', 'edit', 'add'));

        $button_name = $this->__('Add');

        // Get the comm param, and "decode" it
        $final = self::_convertComm();

        $fields = $this->_fields;

        if (is_array(@$this->info['add']['fields'])) {

            foreach ($this->info['add']['fields'] as $value) {
                $fields_to[$value['field']] = $value['field'];
            }

            $fields = $fields_to;
            $mod = 'add';

        }

        $form_hidden = $view->formButton('cancel', $this->__('Cancel'), array('onClick' => $view->escape("window.location='$url'")));
        $form_hidden .= $view->formHidden('_form_edit', 1);

        #$fields = parent::consolidateFields ( $fields, $this->data ['table'] );
        if (count($fields) == 0) {
            throw new Bvb_Grid_Exception('Upsss. It seams there was an error while intersecting your fields with the table fields. Please make sure you allow the fields you are defining...');
        }

        $grid = $this->temp['table']->formStart();

        if (isset($final['mode'])) {

            if ($final['mode'] == 'edit' && ! $this->_editNoForm) {

                $select = clone $this->_select;

                foreach ($this->_getPkFromUrl(true) as $key => $value) {
                    $select->where("$key = ?", $value);
                }

                $select->reset(Zend_Db_Select::COLUMNS);
                $select->reset(Zend_Db_Select::LIMIT_COUNT);
                $select->reset(Zend_Db_Select::LIMIT_OFFSET);

                $select->columns(array_keys($this->info['edit']['fields']));

                $stmt = $select->query();
                $result = $stmt->fetchAll();

                $fields = array();

                foreach ($result[0] as $key => $value) {
                    $fields[$key] = $value;
                }

                $button_name = $this->__('Edit');

                $mod = 'edit';

                #$form_hidden = " <input type=\"button\"  onclick=\"window.location='$url'\" value=\"" . $this->__ ( 'Cancel' ) . "\"><input type=\"hidden\" name=\"_form_edit\" value=\"1\">";
                $fields = self::_removeAutoIncrement($fields, $this->data['table']);

            }
        }

        $titles = $this->_fields;

        if (is_array($this->info[$mod]['fields'])) {
            unset($titles);
            foreach ($this->info[$mod]['fields'] as $key => $value) {
                $titles[] = $key;
            }
        }

        #$titles = parent::consolidateFields ( $titles, $this->data ['table'] );
        $grid .= $this->temp['table']->formHeader();

        $i = 0;

        foreach ($fields as $key => $value) {

            $grid .= $this->temp['table']->formStart();

            $finalV = '';
            if (isset($this->_formMessages[$titles[$i]])) {
                if (is_array($this->_formMessages[$titles[$i]])) {
                    foreach ($this->_formMessages[$titles[$i]] as $formS) {
                        $finalV .= '<br />' . implode('<br />', $formS);
                    }
                    $finalV = '<span style="color:red;">' . $finalV . '</span>';
                }
            } else {
                $finalV = '';
            }

            $fieldValue = isset($this->_formValues[$value]) ? $this->_formValues[$value] : '';
            $fieldDescription = isset($this->info['add']['fields'][$titles[$i]]['description']) ? $this->info['add']['fields'][$titles[$i]]['description'] : '';

            $fieldTitle = isset($this->info['add']['fields'][$titles[$i]]['title']) ? $this->info['add']['fields'][$titles[$i]]['title'] : '';

            $grid .= str_replace("{{value}}", $this->__($fieldTitle) . '<br><em>' . $this->__($fieldDescription) . '</em>', $this->temp['table']->formLeft());

            $grid .= str_replace("{{value}}", self::_buildFormElement($key, $value, $mod, $fieldValue) . $finalV, $this->temp['table']->formRight());

            $grid .= $this->temp['table']->formEnd();

            $i ++;
        }

        $grid .= $this->temp['table']->formStart();
        $grid .= str_replace("{{value}}", $view->formSubmit('submitForm', $button_name) . $form_hidden . "", $this->temp['table']->formButtons());
        $grid .= $this->temp['table']->formEnd();

        return $grid;

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
        if (isset($this->ctrlParams['filters' . $this->_id]) || isset($this->ctrlParams['order' . $this->_id])) {
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

            if ($search[0] == 'D' || $search[0] == 'E') {
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

                    $grid .= str_replace(array("{{value}}", "{{class}}"), array($final['value'], $final['class']), $this->temp['table']->loopLoop($finalFields));

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

        $url = parent::_getUrl(array('start'));

        $actual = (int) isset($this->ctrlParams['start' . $this->_id]) ? $this->ctrlParams['start' . $this->_id] : 0;

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
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"javascript:gridAjax('{$this->info['ajax']}','$url/star{$this->_id}t/0')\">1</a>";
        } else {
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"$url/start{$this->_id}/0\">1</a>";

        }

        $pag .= ($actual > 5) ? " ... " : "  ";

        if ($npaginas > 5) {
            $in = min(max(1, $actual - 4), $npaginas - 5);
            $fin = max(min($npaginas, $actual + 4), 6);

            for ($i = $in + 1; $i < $fin; $i ++) {
                if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=javascript:gridAjax('{$this->info['ajax']}','$url/start{$this->_id}/" . (($i - 1) * $ppagina) . "')> $i </a>";
                } else {
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href='$url/start{$this->_id}/" . (($i - 1) * $ppagina) . "'> $i </a>";
                }

            }

            $pag .= ($fin < $npaginas) ? " ... " : "  ";
        } else {

            for ($i = 2; $i < $npaginas; $i ++) {
                if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {

                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=\"javascript:gridAjax('{$this->info['ajax']}','" . $url . "/start{$this->_id}/" . (($i - 1) * $ppagina) . "')\">$i</a> ";

                } else {

                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=\"" . $url . "/start{$this->_id}/" . (($i - 1) * $ppagina) . "\">$i</a> ";

                }

            }
        }

        if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {
            $pag .= ($actual == $npaginas) ? "<strong>" . $npaginas . "</strong>" : " <a href=\"javascript:gridAjax('{$this->info['ajax']}','$url/start{$this->_id}/" . (($npaginas - 1) * $ppagina) . "')\">$npaginas</a> ";

        } else {
            $pag .= ($actual == $npaginas) ? "<strong>" . $npaginas . "</strong>" : " <a href=\"$url/start{$this->_id}/" . (($npaginas - 1) * $ppagina) . "\">$npaginas</a> ";

        }

        if ($actual != 1) {

            if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {
                $pag = " <a href=\"javascript:gridAjax('{$this->info['ajax']}','$url/start{$this->_id}/0')\">" . $this->__('First') . "</a>&nbsp;&nbsp;<a href=\"javascript:agridAjax('{$this->info['ajax']}','$url/start/" . (($actual - 2) * $ppagina) . "')\">" . $this->__('Previous') . "</a>&nbsp;&nbsp;" . $pag;

            } else {

                $pag = " <a href=\"$url/start/0\">" . $this->__('First') . "</a>&nbsp;&nbsp;<a href=\"$url/start{$this->_id}/" . (($actual - 2) * $ppagina) . "\">" . $this->__('Previous') . "</a>&nbsp;&nbsp;" . $pag;
            }

        }

        if ($actual != $npaginas) {
            if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {

                $pag .= "&nbsp;&nbsp;<a href=\"javascript:gridAjax('{$this->info['ajax']}','$url/start{$this->_id}/" . ($actual * $ppagina) . "')\">" . $this->__('Next') . "</a> <a href=\"javascript:gridAjax('{$this->info['ajax']}','$url/start{$this->_id}/" . (($npaginas - 1) * $ppagina) . "')\">" . $this->__('Last') . "&nbsp;&nbsp;</a>";
            } else {

                $pag .= "&nbsp;&nbsp;<a href=\"$url/start{$this->_id}/" . ($actual * $ppagina) . "\">" . $this->__('Next') . "</a>&nbsp;&nbsp;<a href=\"$url/start{$this->_id}/" . (($npaginas - 1) * $ppagina) . "\">" . $this->__('Last') . "</a>";
            }

        }

        if ($npaginas > 1 && isset($this->info['limit']) && (int) @$this->info['limit'] == 0) {

            if ($npaginas < 100) {
                // Buil the select form element
                if (isset($this->info['ajax']) && $this->info['ajax'] !== false) {
                    $f = "<select id=\"idf\" onchange=\"javascript:gridAjax('{$this->info['ajax']}','{$url}/start{$this->_id}/'+this.value)\">";
                } else {
                    $f = "<select id=\"idf\" onchange=\"window.location='{$url}/start{$this->_id}/'+this.value\">";
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

                if (isset($export['img'])) {
                    $exp .= "<a $class $blank href='$url/_exportTo{$this->_id}/{$export['caption']}'><img src='{$export ['img']}' border='0'></a>";
                } else {
                    $exp .= "<a $class $blank href='$url/_exportTo{$this->_id}/{$export['caption']}'>" . $export['caption'] . "</a>";
                }

            }

            if (isset($this->info['limit']) && (int) @$this->info['limit'] > 0) {
                $result2 = str_replace(array('{{export}}', '{{pagination}}', '{{pageSelect}}', '{{numberRecords}}'), array($exp, '', '', (int) $this->info['limit']), $this->temp['table']->pagination());

            } elseif ($npaginas > 1 && count($this->export) > 0) {

                if ($this->pagination == 0) {
                    $pag = '';
                    $f = '';
                }

                $result2 = str_replace(array('{{export}}', '{{pagination}}', '{{pageSelect}}', '{{numberRecords}}'), array($exp, $pag, $f, $registoActual . ' ' . $this->__('to') . ' ' . $registoFinal . ' ' . $this->__('of') . '  ' . $this->_totalRecords), $this->temp['table']->pagination());

            } elseif ($npaginas < 2 && count($this->export) > 0) {

                if ($this->pagination == 0) {
                    $pag = '';
                    $f = '';
                }
                $result2 .= str_replace(array('{{export}}', '{{pagination}}', '{{pageSelect}}', '{{numberRecords}}'), array($exp, '', '', $this->_totalRecords), $this->temp['table']->pagination());

            } elseif (count($this->export) == 0) {

                if ($this->pagination == 0) {
                    $pag = '';
                    $f = '';
                }
                $result2 .= str_replace(array('{{export}}', '{{pagination}}', '{{pageSelect}}', '{{numberRecords}}'), array('', $pag, $f, $this->_totalRecords), $this->temp['table']->pagination());

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

        $url = parent::_getUrl('comm');

        $this->_view = $this->getView();
        if ($this->_adapter == 'db') {
            //Process form, if necessary, before query
            self::_processForm();
        }

        parent::deploy();

        if (! $this->temp['table'] instanceof Bvb_Grid_Template_Table_Table) {
            $this->setTemplate('table', 'table');
        }

        //colspan to apply
        #  $this->_colspan();

        // The extra fields, they are not part of database table.
        // Usefull for adding links (a least for me :D )
        $grid = $this->_printScript();

        $images = $this->temp['table']->images($this->imagesUrl);

        if ($this->allowDelete == 1 || $this->allowEdit == 1) {
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
            $url = parent::_getUrl($removeParams);

            if ($this->allowEdit == 1 && isset($this->info['ajax']) && $this->info['ajax'] !== false) {
                $urlEdit = $this->_baseUrl . '/' . str_replace("/gridmod/ajax", "", $url);
            } else {
                $urlEdit = $url;
            }

            array_unshift($this->extra_fields, array('position' => 'left', 'name' => 'E', 'decorator' => "<a href=\"$urlEdit/edit/1/comm/" . "mode:edit;[" . $urlFinal . "]\" > " . $images['edit'] . "</a>", 'edit' => true));

        }

        if ($this->allowDelete) {
            if (! is_array($this->extra_fields)) {
                $this->extra_fields = array();
            }

            array_unshift($this->extra_fields, array('position' => 'left', 'name' => 'D', 'decorator' => "<a href=\"#\" onclick=\"confirmDel('" . $this->__('Are you sure?') . "','$url/comm/" . "mode:delete;[" . $urlFinal . "]');\" > " . $images['delete'] . "</a>", 'delete' => true));
        }

        if (strlen($this->message) > 0) {
            $grid .= str_replace("{{value}}", $this->message, $this->temp['table']->formMessage($this->messageOk));
        }

        if (((isset($this->ctrlParams['edit']) && $this->ctrlParams['edit'] == 1) || @$this->ctrlParams['add'] == 1 || @$this->info['double_tables'] == 1) || ($this->formPost == 1 && $this->formSuccess == 0)) {

            if (($this->allowAdd == 1 && $this->_editNoForm != 1) || ($this->allowEdit == 1 && strlen($this->_comm) > 1)) {

                // Remove the unnecessary URL params
                $removeParams = array('filters', 'add');

                foreach (array_keys($this->info['edit']['fields']) as $key) {
                    array_push($removeParams, $key);
                }

                $url = parent::_getUrl($removeParams);

                $grid .= "<form method=\"post\" action=\"$url\">" . $this->temp['table']->formGlobal() . $this->_gridForm() . "</form><br><br>";

            }
        }

        $grid .= $this->_view->formHidden('inputId');

        if ((isset($this->info['double_tables']) && $this->info['double_tables'] == 1) || (@$this->ctrlParams['edit'] != 1 && @$this->ctrlParams['add'] != 1)) {

            if (($this->formPost == 1 && $this->formSuccess == 1) || $this->formPost == 0) {

                $grid .= self::_buildHeader();
                $grid .= self::_buildTitlesTable(parent::_buildTitles());
                $grid .= self::_buildFiltersTable(parent::_buildFilters());
                $grid .= self::_buildGridTable(parent::_buildGrid());
                $grid .= self::_buildSqlexpTable(parent::_buildSqlExp());
                $grid .= self::_pagination();

            }
        }
        $grid .= $this->temp['table']->globalEnd();

        if (isset($this->ctrlParams['gridmod']) && $this->ctrlParams['gridmod'] == 'ajax' && $this->info['ajax'] !== false) {

            echo $grid;
            die();
            return '';
        }


        $this->_deploymentContent = $grid;
        return $this;

    }

    function __toString ()
    {
        if (is_null($this->_deploymentContent)) {
            self::deploy();
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

        $script = "<script language=\"javascript\" type=\"text/javascript\">

        function confirmDel(msg, url)
        {
            if(confirm(msg))
            {
            ";
        if ($useAjax == 1) {
            $script .= "window.location = '" . $this->_baseUrl . "/'+url.replace('/gridmod/ajax','');";
        } else {
            $script .= "window.location = url;";
        }

        $script .= "

            }else{
                return false;
            }
        }


function gridAjax(ponto,url) {

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


function gridChangeFilters(fields,url,Ajax)
{
    var Ajax = \"1\";
    var fieldsArray = fields.split(\",\");
    var filtro = new Array;

    for (var i = 0; i < fieldsArray.length -1; i++)
    {
        filtro[i] = '\"'+encodeURIComponent(fieldsArray[i])+'\":\"'+encodeURIComponent(document.getElementById(fieldsArray[i]).value)+'\"';
    }

    filtro = \"{\"+filtro+\"}\";
    ";

        if ($useAjax == 1) {
            $script .= "gridAjax('{$this->info['ajax']}',url+'/filters{$this->_id}/'+filtro);";
        } else {
            $script .= "window.location=url+'/filters{$this->_id}/'+filtro;";
        }

        $script .= "
    }
        </script>";

        return $script;
    }

    /**
     *
     * @return unknown
     */
    function addForm ($form)
    {

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

        $checkFields = array_keys($fields);

        foreach ($checkFields as $field) {
            $explode = explode('.', $this->data['fields'][$field]['field']);
            if (reset($explode) != $this->data['tableAlias']) {
                throw new Bvb_Grid_Exception('You can only add/update fields from your main table');
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
            $this->add = array('allow' => 1, 'button' => $options['button'], 'fields' => $fields, 'force' => @$options['onAddForce']);
        }

        if (isset($options['edit']) && $options['edit'] == 1) {
            $this->edit = array('allow' => 1, 'button' => $options['button'], 'fields' => $fields, 'force' => @$options['onEditForce']);
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
                $this->filters[$valor]['distinct']['field'] = @$this->filters[$valor]['distinct']['field'];
                $this->filters[$valor]['distinct']['name'] = @$this->filters[$valor]['distinct']['name'];

                $distinct = clone $this->_select;

                $distinct->reset(Zend_Db_Select::COLUMNS);
                $distinct->reset(Zend_Db_Select::ORDER);
                $distinct->reset(Zend_Db_Select::LIMIT_COUNT);
                $distinct->reset(Zend_Db_Select::LIMIT_OFFSET);

                $distinct->columns(array('field' => new Zend_Db_Expr("DISTINCT({$this->filters[$valor]['distinct']['field']})")));
                $distinct->columns(array('value' => $this->filters[$valor]['distinct']['name']));
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
        $url = $this->_getUrl(array('filters', 'start', 'comm', '_exportTo'));

        $fieldsSemAsFinal = $this->data['fields'];

        if (isset($fieldsSemAsFinal[$campo]['searchField'])) {
            $nkey = $fieldsSemAsFinal[$campo]['searchField'];
            @$this->_filtersValues[$campo] = $this->_filtersValues[$nkey];
        }

        if ($this->_adapter == 'db') {

            $tAlias = explode('.', $this->data['fields'][$campo]['field']);
            $tableName = $this->_tablesList[reset($tAlias)]['tableName'];
            $table = $this->_getDescribeTable($this->data['table']);
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
                $help_javascript .= "filter_" . $value . ",";
            }
        }

        $help_javascript = str_replace(".", "bvbdot", $help_javascript);
        $attr['onChange'] = "gridChangeFilters('$help_javascript','$url');";

        $opcoes = array();
        if (isset($this->filters[$campo])) {
            $opcoes = $this->filters[$campo];
        }

        if (isset($opcoes['style']) && strlen($opcoes['style']) > 1) {
            $attr['style'] = $opcoes['style'];
        } else {
            $attr['style'] = " width:95% ";
        }

        $attr['id'] = "filter_" . $campo;

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

                $values[$this->_view->escape($key)] = $this->_view->escape($value);
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
                    $values[$this->_view->escape($value)] = $this->_view->escape($value);
                }

                $valor = $this->_view->formSelect($campo, $selected, $attr, $values);

                break;
            default:
                $valor = $this->_view->formText($campo, $this->_view->escape(@$this->_filtersValues[$campo]), $attr);
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
    public function setView(Zend_View_Interface $view = null)
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
    public function getView()
    {
        if (null === $this->_view) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->setView($viewRenderer->view);
        }

        return $this->_view;
    }
}

