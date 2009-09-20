<?php



/**
 * Mascker
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
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    0.4  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */


class Bvb_Grid_Deploy_Table extends Bvb_Grid_DataGrid {

    /**
     * Information about the template
     *
     * @var array|empty
     */
    
    public $templateInfo;

    
    /**
     * A bool value to check if there is a form when perfoming crud with joins
     *
     * @var bool
     */
    protected $_crudJoin = false;

    
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
    protected $_formValues = array ();

    /**
     * [PT] Form error messages
     *
     * @var unknown_type
     */
    protected $_formMessages = array ();

    /**
     * The output type
     *
     * @var string
     */
    protected $output = 'table';

    /**
     *  Permission to add records
     *
     * @var array
     */
    private $allowAdd = null;

    /**
     *  Permission to edit records
     *
     * @var array
     */
    private $allowEdit = null;

    /**
     *  Permission to delete records
     *
     * @var array
     */
    private $allowDelete = null;

    /**
     *  Message after form submission
     *
     * @var string
     */
    public $message;

    /**
     *  Template data
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
     *  Set if form vaidation failed
     *
     * @var bool
     */
    protected $_failedValidation;

    /**
     *  Url param with the information about removing records
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
     *  The __construct function receives the db adapter. All information related to the
     *  URL is also processed here
     *  To edit, add, or delete records, a user must be authenticated, so we instanciate 
     *  it here. Remember to use the method write when autenticating a user, so we can know 
     *  if its logged or not
     *
     * @param array $data
     */
    function __construct($db) {

        parent::__construct ( $db );
        
        $this->setTemplate ( 'table', 'table' );
    
    }


    
    /**
     * @param string $var
     * @param string $value
     */
    
    function __set($var, $value) {

        parent::__set ( $var, $value );
    }


    
    /**
     * Fetch the field type from the DB
     *
     * @param string $type
     * @param string $table
     * @return string
     */
    function getFieldType($type, $table) {

        
        if ($this->_crudJoin) {
            
            $explode = explode ( '_', $type );
            

            $table = array_shift ( explode ( '_', $type ) );
            $table = $this->data ['table'] [$table];
            
            unset ( $explode [0] );
            
            $type = implode ( '_', $explode );
        
        }
        
        $fields = $this->getDescribeTable ( $table );
        
        return $fields [$type] ['DATA_TYPE'];
    
    }


    
    /**
     * 
     *  Process all information forms related
     *  First we check for permissions to add, edit, delete
     *  And then the request->isPost. If true we process the data
     *
     */
    
    protected function processForm() {

        
        if (isset ( $this->info ['add'] ['allow'] ) && $this->info ['add'] ['allow'] == 1) {
            $this->allowAdd = 1;
        }
        
        if (isset ( $this->info ['delete'] ['allow'] ) && $this->info ['delete'] ['allow'] == 1) {
            $this->allowDelete = 1;
        }
        
        if (isset ( $this->info ['edit'] ['allow'] ) && $this->info ['edit'] ['allow'] == 1) {
            $this->allowEdit = 1;
        }
        
        // IF a user can edit or delete data we must instanciate the crypt classe.
        // This is an extra-security step.
        



        if ($this->allowEdit == 1 || $this->allowDelete) {
            
            $dec = isset ( $this->ctrlParams ['comm'] ) ? $this->ctrlParams ['comm'] : '';
            $this->_comm = $dec;
        }
        

        /**
         *  emove if there is something to remove
         */
        if ($this->allowDelete) {
            self::deleteRecord ( $dec );
        
        }
        

        //Check if the request method is POST
        if (Zend_Controller_Front::getInstance ()->getRequest ()->isPost ()) {
            
            $param = Zend_Controller_Front::getInstance ()->getRequest ();
            
            $opComm = isset ( $this->ctrlParams ['comm'] ) ? $this->ctrlParams ['comm'] : '';
            $op_query = self::convertComm ( $opComm );
            
            $get_mode = isset ( $op_query ['mode'] ) ? $op_query ['mode'] : '';
            $mode = $get_mode == 'edit' ? 'edit' : 'add';
            

            // We must know what fields to get with getPost(). We only gonna get the fieds
            // That belong to the database table. We must ensure we process the right data.
            // So we also must verify if have been defined the fields to process
            if (is_array ( $this->info [$mode] ['fields'] )) {
                $fields = array ();
                
                foreach ( $this->info [$mode] ['fields'] as $key => $value ) {
                    $fields [$key] = $key;
                }
            
            } else {
                $fields = parent::getFields ( $mode, $this->data ['table'] );
            }
            

            if ($this->_crudJoin) {
                
                //Array containg all tables used in this form
                $tables = array ();
                $tablesFields = array ();
                
                foreach ( $fields as $name ) {
                    $abv = reset ( explode ( '.', $name ) );
                    array_push ( $tables, $this->data ['table'] [$abv] );
                }
                
                $tables = array_unique ( $tables );
                

                //associate fields to tables
                foreach ( $fields as $name ) {
                    $abv = reset ( explode ( '.', $name ) );
                    
                    $fieldName = substr ( $name, strpos ( $name, '.' ) + 1 );
                    
                    $tablesFields [$this->data ['table'] [$abv]] [] = $fieldName;
                
                }
            
            }
            

            $queryUrl = $this->getPkFromUrl ();
            
            // Apply filter and validators. Firtst we apply the filters
            foreach ( $fields as $value ) {
                

                $value = preg_replace ( "/\./", '_', $value, 1 );
                

                $this->_formValues [$value] = $param->getPost ( $value );
                

                $fieldType = $this->getFieldType ( $value, $this->data ['from'] );
                

                if (substr ( $fieldType, 0, 3 ) != 'set') {
                    
                    $result = self::applyFilters ( $param->getPost ( $value ), $value, $mode );
                    
                    $result = self::Validate ( $result, $value, $mode );
                

                } else {
                    
                    $possibleValuesForSetField = explode ( ",", str_replace ( array ('(', ')', '\'', 'set' ), array ('', '', '', '' ), $fieldType ) );
                    

                    if (is_array ( $param->getPost ( $value ) )) {
                        
                        $finalValue = array_intersect ( $possibleValuesForSetField, $param->getPost ( $value ) );
                    } else {
                        $finalValue = null;
                    }
                    
                    if (count ( $finalValue ) > 0) {
                        $result = implode ( ',', $finalValue );
                    } else {
                        $result = '';
                    }
                }
                
                $final [$value] = $result;
            

            }
            
            // If pass validation
            if ($this->_failedValidation !== true) {
                
                // Check ig the user has defined "force" fields. If so we need to merge them
                // With the ones we get from the form process
                $force = $this->info [$mode] ['force'];
                if (is_array ( $force )) {
                    $final_values = array_merge ( $final, $force );
                
                } else {
                    $final_values = $final;
                }
                
                $pk2 = parent::getPrimaryKey ();
                
                foreach ( $pk2 as $value ) {
                    unset ( $final_values [$value] );
                }
                
                //Deal with readonly and disabled attributes. 
                //Also check for security issues
                foreach ( array_keys ( $final_values ) as $key ) {
                    
                    if (isset ( $this->info ['add'] ['fields'] [$key] ['attributes'] ['disabled'] )) {
                        unset ( $final_values [$key] );
                    }
                    

                    if ($mode == 'add') {
                        
                        if (isset ( $this->info ['add'] ['fields'] [$key] ['attributes'] ['readonly'] )) {
                            $final_values [$key] = '';
                        }
                    
                    }
                    
                    if ($mode == 'edit') {
                        
                        if (isset ( $this->info ['add'] ['fields'] [$key] ['attributes'] ['readonly'] )) {
                            unset ( $final_values [$key] );
                        }
                    }
                }
                

                // Process data
                if ($mode == 'add' && is_array ( $final_values )) {
                    
                    try {
                        if ($this->_crudJoin) {
                            
                            foreach ( $tablesFields as $key => $fieldName ) {
                                
                                $dataToInsert = $this->getFieldsToTable ( $final_values, array_search ( $key, $this->data ['table'] ) );
                                
                                $this->_db->insert ( $key, $dataToInsert );
                            }
                        
                        } else {
                            
                            $this->_db->insert ( $this->data ['table'], $final_values );
                        }
                        

                        $this->message = $this->__ ( 'Record saved' );
                        $this->messageOk = true;
                    

                    } catch ( Zend_Exception $e ) {
                        $this->messageOk = FALSE;
                        $this->message = $this->__ ( 'Error saving record =>' ) . $e->getMessage ();
                    }
                
                }
                
                // Process data
                if ($mode == 'edit' && is_array ( $final_values )) {
                    
                    $where = isset ( $this->info ['edit'] ['where'] ) ? " AND " . $this->info ['edit'] ['where'] : '';
                    
                    try {
                        
                        if ($this->_crudJoin) {
                            

                            $tableAbv = substr ( $pk2 [0], 0, strpos ( $pk2 [0], '.' ) );
                            
                            $valuesForUpdate = array ();
                            
                            foreach ( $final_values as $key => $value ) {
                                
                                if (substr ( $key, 0, strpos ( $key, '_' ) ) == $tableAbv) {
                                    
                                    $valuesForUpdate [substr ( $key, strpos ( $key, '_' ) + 1 )] = $value;
                                
                                }
                            
                            }
                            
                            $pk = substr ( $pk2 [0], strpos ( $pk2 [0], '.' ) + 1 );
                            

                            $this->_db->update ( $this->data ['table'] [$tableAbv], $valuesForUpdate, " $pk=" . $this->_db->quote ( $op_query ['id'] ) . " $where " );
                        

                        } else {
                            
                            $this->_db->update ( $this->data ['table'], $final_values, $queryUrl . $where );
                        
                        }
                        

                        $this->message = $this->__ ( 'Record saved' );
                        $this->messageOk = true;
                    
                    } catch ( Zend_Exception $e ) {
                        $this->messageOk = FALSE;
                        $this->message = $this->__ ( 'Error updating record =>' ) . $e->getMessage ();
                    }
                    

                    //No need to show the form
                    $this->_editNoForm = 1;
                    
                    unset ( $this->ctrlParams ['comm'] );
                    unset ( $this->ctrlParams ['edit'] );
                
                }
                
                if ($this->cache ['use'] == 1) {
                    $this->cache ['instance']->clean ( Zend_Cache::CLEANING_MODE_MATCHING_TAG, array ($this->cache ['tag'] ) );
                }
                $this->formSuccess = 1;
            
            } else {
                
                $this->message = $this->__ ( 'Validation failed' );
                $this->messageOk = false;
                $this->formSuccess = 0;
                $this->formPost = 1;
                
                $final_values = null;
            
            }
            
            // Unset all params so we can have a more ckean URl when calling $this->getUrl
            if (is_array ( $final_values )) {
                foreach ( $final_values as $key => $value ) {
                    unset ( $this->ctrlParams [$key] );
                }
            }
        }
        
        if ($this->formSuccess == 1) {
            foreach ( $this->ctrlParams as $key => $value ) {
                if ($key != 'module' && $key != 'controller' && $key != 'action') {
                    unset ( $this->ctrlParams [$key] );
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
    function getFieldsToTable(array $fields, $sufix) {

        
        $final = array ();
        
        $sufix = rtrim ( $sufix, '_' ) . '_';
        
        foreach ( $fields as $key => $field ) {
            if (substr ( $key, 0, strlen ( $sufix ) ) == $sufix) {
                $final [substr ( $key, strlen ( $sufix ) )] = $field;
            }
        }
        

        return $final;
    }


    
    /**
     *  Apply filter susing the Zend Framework set.
     *
     * @param string $value
     * @param string $field
     * @param string $mode
     * @return string
     */
    function applyFilters($value, $field, $mode) {

        
        if ($this->_crudJoin) {
            $field = preg_replace ( "/_/", '.', $field, 1 );
        }
        
        $filters = isset ( $this->info [$mode] ['fields'] [$field] ['filters'] ) ? $this->info [$mode] ['fields'] [$field] ['filters'] : '';
        
        if (is_array ( $filters )) {
            //It has filters to apply. Get dirs...
            foreach ( $filters as $func ) {
                $class = $this->_elements ['filter']->load ( $func );
                $t = new $class ( );
                $value = $t->filter ( $value );
            }
        }
        
        return $value;
    }


    
    /**
     *  Validate fields using the set on he Zend Framework
     *
     * @param string $value
     * @param string $field
     * @param string $mode
     * @return string
     */
    function Validate($value, $field, $mode = 'edit') {

        
        if ($this->_crudJoin) {
            $field = preg_replace ( "/_/", '.', $field, 1 );
        }
        
        //Array with allowed values
        $values = isset ( $this->info [$mode] ['fields'] [$field] ['values'] ) ? $this->info [$mode] ['fields'] [$field] ['values'] : '';
        
        //Array of validators
        $validators = isset ( $this->info [$mode] ['fields'] [$field] ['validators'] ) ? $this->info [$mode] ['fields'] [$field] ['validators'] : '';
        

        //Check if the value is in the allowed values array
        if (is_array ( $values ) && $mode == 'edit') {
            
            if (! in_array ( $value, $values ) && ! array_key_exists ( $value, $values )) {
                $this->_failedValidation = true;
                return false;
            }
        
        } elseif (is_array ( $validators )) {
            

            foreach ( $validators as $key => $func ) {
                
                if (is_array ( $validators [$key] )) {
                    $func = $key;
                }
                

                $class = $this->_elements ['validator']->load ( $func );
                


                // If an array, means the Validator receives arguments
                if (is_array ( $validators [$key] )) {
                    
                    // If an array, means the Validator receives arguments
                    



                    $refObj = new ReflectionClass ( $class );
                    $t = $refObj->newInstanceArgs ( $validators [$key] );
                    

                    $return = $t->isValid ( $value );
                    
                    if ($return === false) {
                        $this->_failedValidation = true;
                        foreach ( $t->getMessages () as $messageId => $message ) {
                            $this->_formMessages [$field] [] = array ($messageId => $message );
                        }
                        return false;
                    }
                
                } else {
                    
                    $t = new $class ( );
                    $return = $t->isValid ( $value );
                    
                    if ($return === false) {
                        $this->_failedValidation = true;
                        foreach ( $t->getMessages () as $messageId => $message ) {
                            $this->_formMessages [$field] [] = array ($messageId => $message );
                        }
                        return false;
                    }
                }
            
            }
        

        }
        
        return $value;
    
    }


    
    /**
     *  Remove the record from the table
     *  Don't forget to see if the user as set an "extra" WHERE.
     * 
     * @param string $sql
     * @param string $user
     * @return string
     */
    function deleteRecord($sql) {

        
        @$param = explode ( ";", $sql );
        
        foreach ( $param as $value ) {
            $dec = explode ( ":", $value );
            @$final [$dec [0]] = $dec [1];
        }
        
        if (@$final ['mode'] != 'delete') {
            return 0;
        }
        

        $pkArray = parent::getPrimaryKey ();
        $id = $this->_db->quoteIdentifier ( $pkArray [0] );
        

        if (isset ( $this->info ['delete'] ['where'] )) {
            
            $where = " AND " . $this->info ['delete'] ['where'];
        } else {
            $where = '';
        }
        

        try {
            
            $pkParentArray = $this->getPrimaryKey ();
            $pkParent = $pkParentArray [0];
            
            if (is_array ( $this->info ['delete'] ['cascadeDelete'] )) {
                foreach ( $this->info ['delete'] ['cascadeDelete'] as $value ) {
                    
                    $operand = isset ( $value ['operand'] ) ? $value ['operand'] : '=';
                    $parentField = isset ( $value ['parentField'] ) ? $value ['parentField'] : $pkParent;
                    
                    if ($parentField != $pkParent && ! is_array ( $pkParentArray )) {
                        
                        $select = $this->_db->select ();
                        $select->from ( $this->data ['table'], array ('total' => $parentField ) );
                        $select->where ( $this->getPkFromUrl ( true ) );
                        $final = $select->query ();
                        $result = $final->fetchAll ();
                        
                        $finalValue = $result [0];
                    } else {
                        $finalValue = $final ['id'];
                    }
                    

                    $this->_db->delete ( $value ['table'], $this->_db->quoteIdentifier ( $value ['childField'] ) . $operand . $this->_db->quote ( $finalValue ) );
                }
            }
            
            if ($this->_crudJoin) {
                $id = substr ( $id, strpos ( $id, '.' ) + 1 );
            }
            
            $this->_db->delete ( $this->getMainTableName (), $this->getPkFromUrl ( false ) . $where );
            

            $this->messageOk = true;
            $this->message = $this->__ ( 'Record deleted' );
        
        } catch ( Zend_Exception $e ) {
            $this->messageOk = FALSE;
            $this->message = $this->__ ( 'Error deleting record =>' ) . $e->getMessage ();
        }
        
        unset ( $this->ctrlParams ['comm'] );
        
        if ($this->cache ['use'] == 1) {
            $this->cache ['instance']->clean ( Zend_Cache::CLEANING_MODE_MATCHING_TAG, array ($this->cache ['tag'] ) );
        }
        
        return true;
    
    }


    
    /**
     * Get the main table name.
     * This ius used to get the table when using crud operations with joins.
     * 
     * Otherwise the defined table will be fetched
     *
     * @return string
     */
    function getMainTableName() {

        
        if (is_array ( $this->data ['table'] )) {
            return $this->data ['table'] [reset ( explode ( '.', $this->info ['crud'] ['primaryKey'] ) )];
        } else {
            return $this->data ['table'];
        }
    
    }


    
    /**
     *  Field type on the filters area. If the field type is enum, build the options
     *  Also, we first need to check if the user has defined values.
     *  If set, this values override the others
     *
     * @param string $campo
     * @param string $valor
     * @return string
     */
    
    function formatField($campo, $valor, $options = array()) {

        
        $url = parent::getUrl ( array ('filters', 'start', 'comm' ) );
        
        if (! is_array ( $this->data ['table'] )) {
            $table = parent::getDescribeTable ( $this->data ['table'] );
        } else {
            
            $ini = substr ( $campo, 0, (strpos ( $campo, "." )) );
            $table = parent::getDescribeTable ( $this->data ['table'] [$ini] );
        }
        
        $tipo = $table [$campo];
        
        $tipo = $tipo ['DATA_TYPE'];
        
        if (substr ( $tipo, 0, 4 ) == 'enum') {
            $enum = str_replace ( array ('(', ')' ), array ('', '' ), $tipo );
            $tipo = 'enum';
        }
        

        foreach ( array_keys ( $this->filters ) as $value ) {
            
            if (! $this->data ['fields'] [$value] ['hide']) {
                $help_javascript .= "filter_" . $value . ",";
            }
        }
        
        if ($options ['noFilters'] != 1) {
            $onchange = "onchange=\"gridChangeFilters('$help_javascript','$url');\" id=\"filter_{$campo}\"";
        }
        
        $opcoes = $this->filters [$campo];
        
        if ($opcoes ['style']) {
            $opt = " style=\"{$opcoes['style']}\"  ";
        } else {
            $opt = " style=\"width:95%\"  ";
        }
        
        if (is_array ( $opcoes ['valores'] )) {
            $tipo = 'invalid';
            $avalor = $opcoes ['valores'];
            
            $valor = "<select name=\"$campo\" $opt $onchange  >";
            $valor .= "<option value=\"\">--" . $this->__ ( 'All' ) . "--</option>";
            
            foreach ( $avalor as $value ) {
                
                $selected = $this->_filtersValues [$campo] == $value ['value'] ? "selected" : "";
                $valor .= "<option value=\"{$value['value']}\" $selected >{$value['name']}</option>";
            }
            
            $valor .= "</select>";
        }
        
        switch ($tipo) {
            
            case 'invalid' :
                break;
            case 'enum' :
                
                $avalor = explode ( ",", substr ( $enum, 4 ) );
                $valor = "<select  id=\"filter_{$campo}\" $opt $onchange name=\"\">";
                $valor .= "<option value=\"\">--" . $this->__ ( 'All' ) . "--</option>";
                
                foreach ( $avalor as $value ) {
                    
                    $value = substr ( $value, 1 );
                    $value = substr ( $value, 0, - 1 );
                    $selected = $this->_filtersValues [$campo] == $value ? "selected" : "";
                    $valor .= "<option value=\"$value\" $selected >" . ucfirst ( $value ) . "</option>";
                
                }
                
                $valor .= "</select>";
                
                break;
            
            default :
                
                $valor = "<input type=\"text\" $onchange id=\"filter_{$campo}\"   class=\"input_p\" value=\"" . $this->_filtersValues [$campo] . "\" $opt>";
                
                break;
        }
        
        return $valor;
    }


    
    /**
     *  Build the first line of the table (Not the TH )
     *
     * @return string
     */
    function buildHeader() {

        
        $url = parent::getUrl ( array ('comm', 'edit', 'filters', 'order' ) );
        
        $final = '';
        
        if ($this->_adapter == 'db') {
            if (($this->getInfo ( 'double_tables' ) == 0 && @$this->ctrlParams ['add'] != 1 && @$this->ctrlParams ['edit'] != 1) && $this->getPrimaryKey () && @$this->info ['add'] ['allow'] == 1 && @$this->info ['add'] ['button'] == 1 && @$this->info ['add'] ['no_button'] != 1) {
                
                $final = "<div class=\"addRecord\" ><a href=\"$url/add/1\">" . $this->__ ( 'Add Record' ) . "</a></div>";
            }
        }
        
        //Template start
        $final .= $this->temp ['table']->globalStart ();
        
        /**
         * We must check if there is a filter set or an order, to show the extra th on top
         */
        
        if (isset ( $this->ctrlParams ['filters'] ) || isset ( $this->ctrlParams ['order'] )) {
            
            $url = $this->getUrl ( 'filters' );
            $url2 = $this->getUrl ( 'order' );
            $url3 = $this->getUrl ( array ('filters', 'order' ) );
            
            $this->temp ['table']->hasExtraRow = 1;
            


            //Filters and order
            if (isset ( $this->ctrlParams ['filters'] ) and isset ( $this->ctrlParams ['order'] )) {
                if (isset ( $this->info ['ajax'] ) && $this->info ['ajax'] === true) {
                    
                    $final1 = "<a href=\"javascript:gridAjax('{$this->info['ajaxId']}','" . $url . "')\">" . $this->__ ( 'Remove Filters' ) . "</a> | <a href=\"javascript:gridAjax('{$this->info['ajaxId']}','" . $url2 . "')\">" . $this->__ ( 'Remove Order' ) . "</a> | <a href=\"javascript:gridAjax('{$this->info['ajaxId']}','" . $url3 . "')\">" . $this->__ ( 'Remove Filters &amp; Order' ) . "</a>";
                
                } else {
                    $final1 = "<a href=\"$url\">" . $this->__ ( 'Remove Filters' ) . "</a> | <a href=\"$url2\">" . $this->__ ( 'Remove Order' ) . "</a> | <a href=\"$url3\">" . $this->__ ( 'Remove Filters &amp; Order' ) . "</a>";
                }
                

            //Only filters
            } elseif (isset ( $this->ctrlParams ['filters'] ) && ! isset ( $this->ctrlParams ['order'] )) {
                if (isset ( $this->info ['ajax'] ) && $this->info ['ajax'] === true) {
                    
                    $final1 = "<a href=\"javascript:gridAjax('{$this->info['ajaxId']}','" . $url . "') \">" . $this->__ ( 'Remove Filters' ) . "</a>";
                
                } else {
                    $final1 = "<a href=\"$url\">" . $this->__ ( 'Remove Filters' ) . "</a>";
                }
                
            //Only order
            } elseif (! isset ( $this->ctrlParams ['filters'] ) && isset ( $this->ctrlParams ['order'] )) {
                
                if (isset ( $this->info ['ajax'] ) && $this->info ['ajax'] === true) {
                    
                    $final1 = "<a href=\"javascript:gridAjax('{$this->info['ajaxId']}','" . $url2 . "') \">" . $this->__ ( 'Remove Order' ) . "</a>";
                
                } else {
                    $final1 = "<a href=\"$url2\">" . $this->__ ( 'Remove Order' ) . "</a>";
                }
            }
            
            //Replace values
            $final .= str_replace ( "{{value}}", $final1, $this->temp ['table']->extra () );
            
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
    function buildFiltersTable($filters) {

        
        //There are no filters. 
        if (! is_array ( $filters )) {
            $this->temp ['table']->hasFilters = 0;
            return '';
        }
        
        //Start the template
        $grid = $this->temp ['table']->filtersStart ();
        
        foreach ( $filters as $filter ) {
            
            //Check extra fields
            if ($filter ['type'] == 'extraField' && $filter ['position'] == 'left') {
                
                //Replace values
                $filterValue = isset ( $filter ['value'] ) ? $filter ['value'] : '';
                
                $grid .= str_replace ( '{{value}}', $filterValue . '&nbsp;', $this->temp ['table']->filtersLoop () );
            }
            

            $hRowField = isset ( $this->info ['hRow'] ['field'] ) ? $this->info ['hRow'] ['field'] : '';
            
            //Check if we have an horizontal row
            if ((@$filter ['field'] != $hRowField && isset ( $this->info ['hRow'] ['title'] )) || ! isset ( $this->info ['hRow'] ['title'] )) {
                
                if ($filter ['type'] == 'field') {
                    
                    $filterValue = isset ( $filter ['value'] ) ? $filter ['value'] : '';
                    
                    $newValue = strlen ( urldecode ( $filterValue ) ) > 0 ? urldecode ( $filter ['value'] ) : "&nbsp;";
                    
                    //Replace values
                    $grid .= str_replace ( '{{value}}', $newValue, $this->temp ['table']->filtersLoop () );
                
                }
            }
            

            //Check extra fields from the right
            if ($filter ['type'] == 'extraField' && $filter ['position'] == 'right') {
                @ $grid .= str_replace ( '{{value}}', $filter ['value'], $this->temp ['table']->filtersLoop () );
            }
        
        }
        
        //Close template 
        $grid .= $this->temp ['table']->filtersEnd ();
        
        return $grid;
    }


    
    /**
     * Buil Table titles.
     *
     * @param array $titles
     * @return string
     */
    function buildTitltesTable($titles) {

        
        //We must now the field that is being ordered. So we can grab the image
        $order = @array_keys ( $this->order );
        $order2 = @array_keys ( array_flip ( $this->order ) );
        
        //The field that is being ordered
        $orderField = $order [0];
        
        //The oposite order
        $order = strtolower ( $order2 [0] );
        
        //Lets get the images for defining the order
        $images = $this->temp ['table']->images ( $this->imagesUrl );
        
        //Iniciate titles template
        $grid = $this->temp ['table']->titlesStart ();
        
        foreach ( $titles as $title ) {
            
            if (isset ( $title ['field'] )) {
                if ($title ['field'] == $orderField) {
                    $imgFinal = $images [$order];
                } else {
                    $imgFinal = '';
                }
            } else {
                $imgFinal = '';
            }
            
            //deal with extra field and template
            if ($title ['type'] == 'extraField' && $title ['position'] == 'left') {
                $grid .= str_replace ( '{{value}}', $title ['value'], $this->temp ['table']->titlesLoop () );
            }
            

            $hRowTitle = isset ( $this->info ['hRow'] ['field'] ) ? $this->info ['hRow'] ['field'] : '';
            
            if ((@$title ['field'] != $hRowTitle && isset ( $this->info ['hRow'] ['title'] )) || ! isset ( $this->info ['hRow'] ['title'] )) {
                
                if ($title ['type'] == 'field') {
                    
                    $noOrder = isset ( $this->info ['noOrder'] ) ? $this->info ['noOrder'] : '';
                    
                    if ($noOrder == 1) {
                        
                        //user set the noOrder(1) method
                        $grid .= str_replace ( '{{value}}', $this->__ ( $title ['value'] ), $this->temp ['table']->titlesLoop () );
                    
                    } else {
                        
                        if (isset ( $this->info ['ajax'] ) && $this->info ['ajax'] === true) {
                            $grid .= str_replace ( '{{value}}', "<a href=\"javascript:gridAjax('{$this->info['ajaxId']}','" . $title ['url'] . "') \">" . $title ['value'] . $imgFinal . "</a>", $this->temp ['table']->titlesLoop () );
                        
                        } else {
                            //Replace values in the template
                            
                            if(!array_key_exists('url',$title))
                            {
                                $grid .= str_replace ( '{{value}}', $title ['value'] , $this->temp ['table']->titlesLoop () );
                        
                            }else{
                                $grid .= str_replace ( '{{value}}', "<a href='" . $title ['url'] . "'>" . $title ['value'] . $imgFinal . "</a>", $this->temp ['table']->titlesLoop () );
                        
                            }
                            
                        }
                    }
                }
            
            }
            
            //Deal with extra fields
            if ($title ['type'] == 'extraField' && $title ['position'] == 'right') {
                $grid .= str_replace ( '{{value}}', $title ['value'], $this->temp ['table']->titlesLoop () );
            }
        
        }
        
        //End template
        $grid .= $this->temp ['table']->titlesEnd ();
        
        return $grid;
    
    }


    
    /**
     *  Convert url  params
     *
     * @return array
     */
    function convertComm() {

        
        $t = explode ( ";", $this->_comm );
        
        foreach ( $t as $value ) {
            $value = explode ( ":", $value );
            @$final [$value [0]] = $value [1];
        }
        
        return $final;
    }


    
    /**
     *  Build the form elements for the edit or add action
     *  This is different from the filters
     *
     * @param string $field | The database field that we are processing
     * @param string $inicial_value | the inicial field value
     * @param srint $mod edit|add
     * @param string $fieldValue | This saves the fields values in case o failed validation
     * @return string
     */
    function buildFormElement($field, $inicial_value = '', $mod = 'edit', $fieldValue = '') {

        
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
        $table = parent::getDescribeTable ( $this->data ['table'], $fieldRaw );
        

        $field = end ( explode ( '.', $field ) );
        
        @$tipo = $table [$field];
        

        $tipo = $tipo ['DATA_TYPE'];
        
        if (substr ( $tipo, 0, 4 ) == 'enum') {
            $enum = str_replace ( array ('(', ')' ), array ('', '' ), $tipo );
            $tipo = 'enum';
        }
        

        //Let's get the possible values for the set Type
        if (substr ( $tipo, 0, 3 ) == 'set') {
            $set = str_replace ( array ('(', ')', '\'', 'set' ), array ('', '', '', '' ), $tipo );
            $tipo = 'set';
        }
        

        @$options = $this->info [$mod] ['fields'] [$field];
        
        //If the field as options
        $attr = '';
        
        if (isset ( $options ['attributes'] ['type'] )) {
            $tipo = $options ['attributes'] ['type'];
        }
        
        if (! is_array ( @$options ['attributes'] )) {
            $options ['attributes'] = array ();
            
            if (! in_array ( 'style', @$options ['attributes'] )) {
                $options ['attributes'] ['style'] = 'width:95%';
            }
        } else {
            
            if (! array_key_exists ( 'style', @$options ['attributes'] )) {
                $options ['attributes'] ['style'] = 'width:95%';
            }
        }
        

        if (@is_array ( $options ['attributes'] )) {
            foreach ( $options ['attributes'] as $key => $value ) {
                $attr .= " $key=\"$value\" ";
            }
        }
        
        //User wants to specify the values 
        if (isset ( $options ['values'] )) {
            

            if (is_array ( $options ['values'] )) {
                
                //Declare as invalid to skip the swith
                $tipo = 'invalid';
                $avalor = $options ['values'];
                
                $valor = "<select name=\"$fieldRaw\"   $attr >";
                
                foreach ( $avalor as $key => $value ) {
                    
                    //check for select value
                    if ($mod == 'edit') {
                        $selected = $inicial_value == $value ? "selected" : "";
                    } else {
                        $selected = null;
                    }
                    $valor .= "<option value=\"{$key}\" $selected >" . ucfirst ( $value ) . "</option>";
                }
                
                $valor .= "</select>";
            }
        }
        
        switch ($tipo) {
            
            case 'invalid' :
                break;
            case 'set' :
                
                //Build options based on set from database, if not defined by the user
                $avalor = explode ( ",", $set );
                
                $setValues = explode ( ',', $inicial_value );
                
                $size = count ( $avalor ) > 7 ? 7 : count ( $avalor );
                
                $valor = "<select multiple=\"multiple\"  size=\"$size\" name=\"{$fieldRaw}[]\" $attr  >";
                foreach ( $avalor as $value ) {
                    
                    $selected = in_array ( $value, $setValues ) ? 'selected="selected"' : '';
                    
                    $valor .= "<option value=\"$value\" $selected >" . ucfirst ( $value ) . "</option>";
                
                }
                
                $valor .= "</select>";
                break;
            case 'enum' :
                
                //Build options based on enum from database, if not defined by the user
                $avalor = explode ( ",", substr ( $enum, 4 ) );
                
                $valor = "<select  name=\"$fieldRaw\" $attr  >";
                foreach ( $avalor as $value ) {
                    $selected = $value == "'" . $inicial_value . "'" ? "selected" : "";
                    $value = substr ( $value, 1 );
                    $value = substr ( $value, 0, - 1 );
                    $valor .= "<option value=\"$value\" $selected >" . ucfirst ( $value ) . "</option>";
                
                }
                
                $valor .= "</select>";
                
                break;
            case 'text' :
            case 'textarea' :
                $valor = "<textarea  name=\"{$fieldRaw}\"   $attr>" . stripslashes ( $inicial_value ) . "</textarea>";
                break;
            case 'password' :
                $valor = "<input  type=\"password\"  name=\"{$fieldRaw}\"   value=\"" . stripslashes ( $inicial_value ) . "\" $attr>";
                
                break;
            default :
                $valor = "<input  type=\"text\"  name=\"{$fieldRaw}\"   value=\"" . stripslashes ( $inicial_value ) . "\" $attr>";
                
                break;
        }
        
        return $valor;
    
    }


    
    /**
     * When we query the db, we don't get the field prefix (c.days, c.hours returns days and hours)
     * We need to make the output match the fields names defined by the user
     *
     */
    function convertOutputNamesFromSqlToUserDefined($fields) {

        
        $fields = parent::object2array ( $fields );
        
        $originalFields = $this->data ['fields'];
        

        $temp = array ();
        foreach ( array_keys ( $originalFields ) as $key ) {
            
            if (strpos ( ' AS ', $key )) {
                $return = substr ( $key, strpos ( $key, ' AS ' ) + 1 );
            } else {
                $return = substr ( $key, strpos ( $key, '.' ) + 1 );
            }
            
            if (key_exists ( $return, $fields )) {
                
                $temp [$key] = $fields [$return];
            }
        }
        
        return $temp;
    
    }


    
    /**
     * Build the select fields when performing crus operations.
     * 
     * We need to grab the tables primary keys.
     *
     */
    protected function buildSelectFieldsForUpdate() {

        
        if (@is_array ( $this->info ['edit'] ['fields'] )) {
            foreach ( $this->info ['edit'] ['fields'] as $value ) {
                $fields_to [] = $value ['field'];
            }
            
            $select_fields = parent::buildSelectFields ( $fields_to );
        
        } else {
            $select_fields = " * ";
        }
        
        return $select_fields;
    
    }


    /**
     * Get the list of primary keys from the URL
     *
     * @return string
     */
    function getPkFromUrl($array = false) {

        
        if (! isset ( $this->ctrlParams ['comm'] )) {
            return false;
        }
        
        $param = $this->ctrlParams ['comm'];
        $param = end ( explode ( ';', $param ) );
        $param = substr ( $param, 1, - 1 );
        
        $paramF = explode ( '-', $param );
        $param = '';
        

        $returnArray = array ();
        foreach ( $paramF as $value ) {
            $f = explode ( ':', $value );
            
            $param .= " AND  " . $this->_db->quoteIdentifier ( $f [0] ) . '=' . $this->_db->quote ( $f [1] );
            
            $returnArray [$f [0]] = $f [1];
        }
        
        $param = substr ( $param, 4 );
        
        return $array != FALSE ? $returnArray : $param;
    
    }


    /**
     *  The table to show when editing or adding records
     *
     * @return string
     */
    function gridForm() {

        
        // Remove the unnecessary URL params
        $url = parent::getUrl ( array ('comm', 'edit', 'add' ) );
        
        $button_name = $this->__ ( 'Add' );
        
        // Get the comm param, and "decode" it
        $final = self::convertComm ();
        

        $select_fields = $this->buildSelectFieldsForUpdate ();
        
        $fields = $this->_fields;
        
        if (is_array ( @$this->info ['add'] ['fields'] )) {
            unset ( $fields_to );
            
            foreach ( $this->info ['add'] ['fields'] as $value ) {
                
                $fields_to [$value ['field']] = $value ['field'];
            
            }
            
            $fields = $fields_to;
            $mod = 'add';
        
        }
        $form_hidden = " <input type=\"button\"    onclick=\"window.location='$url'\" value=\"" . $this->__ ( 'Cancel' ) . "\"><input type=\"hidden\" name=\"_form_edit\" value=\"1\">";
        
        #$fields = parent::consolidateFields ( $fields, $this->data ['table'] );
        



        if (count ( $fields ) == 0) {
            throw new Exception ( 'Upsss. It seams there was an error while intersecting your fields with the table fields. Please make sure you allow the fields you are defining...' );
        }
        
        $grid = $this->temp ['table']->formStart ();
        
        if (isset ( $final ['mode'] )) {
            

            if ($final ['mode'] == 'edit' && ! $this->_editNoForm) {
                
                $select = $this->_db->select ();
                $select->from ( $this->data ['from'], array_map ( 'trim', explode ( ',', $select_fields ) ) );
                

                foreach ( $this->getPkFromUrl ( true ) as $key => $value ) {
                    $select->where ( "$key = ?", $value );
                }
                
                $stmt = $select->query ();
                $result = $stmt->fetchAll ();
                
                $fields = array ();
                
                foreach ( $result [0] as $key => $value ) {
                    $fields [$key] = $value;
                }
                
                if ($this->_crudJoin) {
                    $fields = $this->convertOutputNamesFromSqlToUserDefined ( $fields );
                }
                
                $button_name = $this->__ ( 'Edit' );
                
                $mod = 'edit';
                
                $form_hidden = " <input type=\"button\"  onclick=\"window.location='$url'\" value=\"" . $this->__ ( 'Cancel' ) . "\"><input type=\"hidden\" name=\"_form_edit\" value=\"1\">";
                
                $fields = self::removeAutoIncrement ( $fields, $this->data ['table'] );
            
            }
        }
        
        $titles = $this->_fields;
        

        if (is_array ( $this->info [$mod] ['fields'] )) {
            unset ( $titles );
            foreach ( $this->info [$mod] ['fields'] as $key => $value ) {
                $titles [] = $key;
            }
        }
        
        #$titles = parent::consolidateFields ( $titles, $this->data ['table'] );
        



        $grid .= $this->temp ['table']->formHeader ();
        
        $i = 0;
        

        foreach ( $fields as $key => $value ) {
            

            $grid .= $this->temp ['table']->formStart ();
            
            $finalV = '';
            if (isset ( $this->_formMessages [$titles [$i]] )) {
                
                if (is_array ( $this->_formMessages [$titles [$i]] )) {
                    
                    foreach ( $this->_formMessages [$titles [$i]] as $formS ) {
                        $finalV .= '<br />' . implode ( '<br />', $formS );
                    }
                    
                    $finalV = '<span style="color:red;">' . $finalV . '</span>';
                }
            
            } else {
                $finalV = '';
            }
            

            $fieldValue = isset ( $this->_formValues [$value] ) ? $this->_formValues [$value] : '';
            $fieldDescription = isset ( $this->info ['add'] ['fields'] [$titles [$i]] ['description'] ) ? $this->info ['add'] ['fields'] [$titles [$i]] ['description'] : '';
            
            $fieldTitle = isset ( $this->info ['add'] ['fields'] [$titles [$i]] ['title'] ) ? $this->info ['add'] ['fields'] [$titles [$i]] ['title'] : '';
            
            $grid .= str_replace ( "{{value}}", $this->__ ( $fieldTitle ) . '<br><em>' . $this->__ ( $fieldDescription ) . '</em>', $this->temp ['table']->formLeft () );
            
            $grid .= str_replace ( "{{value}}", self::buildFormElement ( $key, $value, $mod, $fieldValue ) . $finalV, $this->temp ['table']->formRight () );
            
            $grid .= $this->temp ['table']->formEnd ();
            
            $i ++;
        }
        

        $grid .= $this->temp ['table']->formStart ();
        $grid .= str_replace ( "{{value}}", "<input type=\"submit\"  value=\"" . $button_name . "\"> " . $form_hidden . "", $this->temp ['table']->formButtons () );
        $grid .= $this->temp ['table']->formEnd ();
        
        return $grid;
    
    }


    
    /**
     * Buil the table
     *
     * @param array $grids | db results
     * @return unknown
     */
    function buildGridTable($grids) {

        
        $i = 0;
        $grid = '';
        
        //We have an extra td for the text to remove filters and order
        if (isset ( $this->ctrlParams ['filters'] ) || isset ( $this->ctrlParams ['order'] )) {
            $i ++;
        }
        
        if (isset ( $this->info ['hRow'] ['title'] )) {
            
            $bar = $grids;
            
            $hbar = trim ( $this->info ['hRow'] ['field'] );
            
            $p = 0;
            
            foreach ( $grids [0] as $value ) {
                if ($value ['field'] == $hbar) {
                    $hRowIndex = $p;
                }
                
                $p ++;
            }
            $aa = 0;
        }
        
        $aa = 0;
        foreach ( $grids as $value ) {
            

            // decorators
            $search = $this->_finalFields;
            
            foreach ( $search as $key => $final ) {
                if ($final ['type'] == 'extraField') {
                    unset ( $search [$key] );
                
                }
            }
            
            $search = array_keys ( $search );
            
            unset ( $fi );
            foreach ( $value as $tia ) {
                
                if (isset ( $tia ['field'] )) {
                    $fi [] = $tia ['value'];
                }
            }
            
            if (count ( $fi ) != count ( $search )) {
                $diff = count ( $fi ) > count ( $search ) ? count ( $fi ) - count ( $search ) : count ( $search ) - count ( $fi );
                
                if (count ( $search ) > count ( $fi ) && $diff == 1) {
                    //Remove first element if a id_
                    array_shift ( $search );
                }
            }
            

            if ($search [0] == 'D' || $search [0] == 'E') {
                unset ( $search [0] );
            }
            
            if ($search [1] == 'E') {
                unset ( $search [1] );
            }
            
            $search = $this->reset_keys ( $search );
            

            $finalFields = array_combine ( $search, $fi );
            
            //horizontal row
            if (isset ( $this->info ['hRow'] ['title'] )) {
                
                if ($bar [$aa] [$hRowIndex] ['value'] != @$bar [$aa - 1] [$hRowIndex] ['value']) {
                    $i ++;
                    
                    $grid .= str_replace ( array ("{{value}}", "{{class}}" ), array ($bar [$aa] [$hRowIndex] ['value'], @$value ['class'] ), $this->temp ['table']->hRow ( $finalFields ) );
                }
            }
            
            $i ++;
            
            //loop tr
            $grid .= $this->temp ['table']->loopStart ( $finalFields );
            

            $set = 0;
            foreach ( $value as $final ) {
                
                $finalField = isset ( $final ['field'] ) ? $final ['field'] : '';
                $finalHrow = isset ( $this->info ['hRow'] ['field'] ) ? $this->info ['hRow'] ['field'] : '';
                
                if (($finalField != $finalHrow && isset ( $this->info ['hRow'] ['title'] )) || ! isset ( $this->info ['hRow'] ['title'] )) {
                    
                    $set ++;
                    
                    $grid .= str_replace ( array ("{{value}}", "{{class}}" ), array ($final ['value'], $final ['class'] ), $this->temp ['table']->loopLoop ( $finalFields ) );
                
                }
            }
            
            $set = null;
            $grid .= $this->temp ['table']->loopEnd ( $finalFields );
            
            @$aa ++;
        }
        
        if ($this->_totalRecords == 0) {
            $grid = str_replace ( "{{value}}", $this->__ ( 'No records found' ), $this->temp ['table']->noResults () );
        }
        
        return $grid;
    
    }


    
    /**
     * Biuild the table that handles the query result from sql expressions
     *
     * @param array $sql
     * @return unknown
     */
    function buildSqlexpTable($sql) {

        
        $grid = '';
        if (is_array ( $sql )) {
            $grid .= $this->temp ['table']->sqlExpStart ();
            
            foreach ( $sql as $exp ) {
                if ($exp ['field'] != @$this->info ['hRow'] ['field']) {
                    $grid .= str_replace ( "{{value}}", $exp ['value'], $this->temp ['table']->sqlExpLoop () );
                }
            }
            $grid .= $this->temp ['table']->sqlExpEnd ();
        
        } else {
            return false;
        }
        
        return $grid;
    
    }


    
    /**
     *  Build pagination
     *
     * @return string
     */
    function pagination() {

        $f = '';
        

        $url = parent::getUrl ( array ('start' ) );
        
        $actual = ( int ) isset ( $this->ctrlParams ['start'] ) ? $this->ctrlParams ['start'] : 0;
        
        $ppagina = $this->pagination;
        $result2 = '';
        
        $pa = $actual == 0 ? 1 : ceil ( $actual / $ppagina ) + 1;
        
        // Calculate the number of pages
        if ($this->pagination > 0) {
            $npaginas = ceil ( $this->_totalRecords / $ppagina );
            $actual = floor ( $actual / $ppagina ) + 1;
        } else {
            $npaginas = 0;
            $actual = 0;
        }
        

        if (isset ( $this->info ['ajax'] ) && $this->info ['ajax'] === true) {
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"javascript:gridAjax('{$this->info['ajaxId']}','$url/start/0')\">1</a>";
        } else {
            $pag = ($actual == 1) ? '<strong>1</strong>' : "<a href=\"$url/start/0\">1</a>";
        
        }
        

        if ($npaginas > 5) {
            $in = min ( max ( 1, $actual - 4 ), $npaginas - 5 );
            $fin = max ( min ( $npaginas, $actual + 4 ), 6 );
            
            for($i = $in + 1; $i < $fin; $i ++) {
                if (isset ( $this->info ['ajax'] ) && $this->info ['ajax'] === true) {
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=javascript:gridAjax('{$this->info['ajaxId']}','$url/start/" . (($i - 1) * $ppagina) . "')> $i </a>";
                } else {
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href='$url/start/" . (($i - 1) * $ppagina) . "'> $i </a>";
                }
            
            }
            
            $pag .= ($fin < $npaginas) ? " ... " : "  ";
        } else {
            
            for($i = 2; $i < $npaginas; $i ++) {
                if (isset ( $this->info ['ajax'] ) && $this->info ['ajax'] === true) {
                    
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=\"javascript:gridAjax('{$this->info['ajaxId']}','" . $url . "/start/" . (($i - 1) * $ppagina) . "')\">$i</a> ";
                
                } else {
                    
                    $pag .= ($i == $actual) ? "<strong> $i </strong>" : " <a href=\"" . $url . "/start/" . (($i - 1) * $ppagina) . "\">$i</a> ";
                
                }
            
            }
        }
        
        if (isset ( $this->info ['ajax'] ) && $this->info ['ajax'] === true) {
            $pag .= ($actual == $npaginas) ? "<strong>" . $npaginas . "</strong>" : " <a href=\"javascript:gridAjax('{$this->info['ajaxId']}','$url/start/" . (($npaginas - 1) * $ppagina) . "')\">$npaginas</a> ";
        
        } else {
            $pag .= ($actual == $npaginas) ? "<strong>" . $npaginas . "</strong>" : " <a href=\"$url/start/" . (($npaginas - 1) * $ppagina) . "\">$npaginas</a> ";
        
        }
        
        if ($actual != 1) {
            
            if (isset ( $this->info ['ajax'] ) && $this->info ['ajax'] === true) {
                $pag = " <a href=\"javascript:gridAjax('{$this->info['ajaxId']}','$url/start/0')\">" . $this->__ ( 'First' ) . "</a>&nbsp;&nbsp;<a href=\"javascript:agridAjax('{$this->info['ajaxId']}','$url/start/" . (($actual - 2) * $ppagina) . "')\">" . $this->__ ( 'Previous' ) . "</a>&nbsp;&nbsp;" . $pag;
            
            } else {
                
                $pag = " <a href=\"$url/start/0\">" . $this->__ ( 'First' ) . "</a>&nbsp;&nbsp;<a href=\"$url/start/" . (($actual - 2) * $ppagina) . "\">" . $this->__ ( 'Previous' ) . "</a>&nbsp;&nbsp;" . $pag;
            }
        
        }
        
        if ($actual != $npaginas) {
            if (isset ( $this->info ['ajax'] ) && $this->info ['ajax'] === true) {
                
                $pag .= "&nbsp;&nbsp;<a href=\"javascript:gridAjax('{$this->info['ajaxId']}','$url/start/" . ($actual * $ppagina) . "')\">" . $this->__ ( 'Next' ) . "</a> <a href=\"javascript:gridAjax('{$this->info['ajaxId']}','$url/start/" . (($npaginas - 1) * $ppagina) . "')\">" . $this->__ ( 'Last' ) . "&nbsp;&nbsp;</a>";
            } else {
                
                $pag .= "&nbsp;&nbsp;<a href=\"$url/start/" . ($actual * $ppagina) . "\">" . $this->__ ( 'Next' ) . "</a>&nbsp;&nbsp;<a href=\"$url/start/" . (($npaginas - 1) * $ppagina) . "\">" . $this->__ ( 'Last' ) . "</a>";
            }
        
        }
        
        if ($npaginas > 1 && isset ( $this->info ['limit'] ) && ( int ) @$this->info ['limit'] == 0) {
            

            if ($npaginas < 100) {
                // Buil the select form element
                if (isset ( $this->info ['ajax'] ) && $this->info ['ajax'] === true) {
                    $f = "<select id=\"idf\" onchange=\"javascript:gridAjax('{$this->info['ajaxId']}','{$url}/start/'+this.value)\">";
                } else {
                    $f = "<select id=\"idf\" onchange=\"window.location='{$url}/start/'+this.value\">";
                }
                
                for($i = 1; $i <= $npaginas; $i ++) {
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
        
        if ($npaginas > 1 || count ( $this->export ) > 0) {
            

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
            
            $images = $this->temp ['table']->images ( $this->imagesUrl );
            
            $exp = '';
            

            foreach ( $this->export as $export ) {
                $exp .= "<a target='_blank' href='$url/export/{$export}'>" . $images [$export] . "</a>";
            }
            
            if (isset ( $this->info ['limit'] ) && ( int ) @$this->info ['limit'] > 0) {
                $result2 = str_replace ( array ('{{export}}', '{{pagination}}', '{{pageSelect}}', '{{numberRecords}}' ), array ($exp, '', '', ( int ) $this->info ['limit'] ), $this->temp ['table']->pagination () );
            
            } elseif ($npaginas > 1 && count ( $this->export ) > 0) {
                
                if ($this->pagination == 0) {
                    $pag = '';
                    $f = '';
                }
                
                $result2 = str_replace ( array ('{{export}}', '{{pagination}}', '{{pageSelect}}', '{{numberRecords}}' ), array ($exp, $pag, $f, $registoActual . ' ' . $this->__ ( 'to' ) . ' ' . $registoFinal . ' ' . $this->__ ( 'of' ) . '  ' . $this->_totalRecords ), $this->temp ['table']->pagination () );
            
            } elseif ($npaginas < 2 && count ( $this->export ) > 0) {
                
                if ($this->pagination == 0) {
                    $pag = '';
                    $f = '';
                }
                $result2 .= str_replace ( array ('{{export}}', '{{pagination}}', '{{pageSelect}}', '{{numberRecords}}' ), array ($exp, '', '', $this->_totalRecords ), $this->temp ['table']->pagination () );
            
            } elseif (count ( $this->export ) == 0) {
                
                if ($this->pagination == 0) {
                    $pag = '';
                    $f = '';
                }
                $result2 .= str_replace ( array ('{{export}}', '{{pagination}}', '{{pageSelect}}', '{{numberRecords}}' ), array ('', $pag, $f, $this->_totalRecords ), $this->temp ['table']->pagination () );
            
            }
        
        } else {
            return '';
        }
        
        return $result2;
    }


    
    /**
     *  Remeve the auto-increment field from the array. If a field is auto-increment,
     *  we won't let the user insert data on the field
     *
     * @param array $fields
     * @param string $table
     * @return array
     */
    function removeAutoIncrement($fields, $table) {

        
        if ($this->_crudJoin) {
            
            $table = $this->data ['table'] [reset ( explode ( '.', $this->info ['crud'] ['primaryKey'] ) )];
        
        }
        

        $table = $this->getDescribeTable ( $table );
        
        foreach ( $table as $value ) {
            
            if ($value ['IDENTITY'] == true) {
                $table_fields = $value ['COLUMN_NAME'];
            }
        }
        
        if (array_key_exists ( $table_fields, $fields )) {
            unset ( $fields->$table_fields );
        }
        

        return $fields;
    }


    
    /**
     *  Make sure the filters exists, they are the name from the table field.
     *  If not, remove them from the array
     *  If we get an empty array, we then creat a new one with all the fields specifieds
     *  in $this->_fields method
     *
     * @param string $filters
     */
    
    function validateFilters($filters) {

        
        if ($this->info ['noFilters']) {
            return false;
        }
        
        if (is_array ( $filters )) {
            
            return $filters;
        
        } else {
            
            //o fields given. Fetch all
            if (is_array ( $this->data ['table'] )) {
                
                foreach ( $this->data ['table'] as $key => $value ) {
                    
                    $tab = parent::getDescribeTable ( $value );
                    
                    foreach ( $tab as $list ) {
                        $titulos [$key . "." . $list ['COLUMN_NAME']] = ucfirst ( $list ['COLUMN_NAME'] );
                    }
                }
            
            } else {
                
                $tab = parent::getDescribeTable ( $this->data ['table'] );
                
                foreach ( $tab as $list ) {
                    $titulos [$list ['COLUMN_NAME']] = ucfirst ( $list ['COLUMN_NAME'] );
                }
            }
        
        }
        
        if (is_array ( $this->data ['hide'] )) {
            foreach ( $this->data ['hide'] as $value ) {
                if (! in_array ( $value, $titulos )) {
                    unset ( $titulos [$value] );
                }
            }
        } else {
            
            foreach ( $titulos as $key => $value ) {
                
                if (! in_array ( $key, $this->_fields )) {
                    unset ( $titulos [$key] );
                }
            
            }
        
        }
        
        return $titulos;
    
    }


    
    /**
     * Here we go....
     *
     * @return string
     */
    function deploy() {

        
        if (isset($this->info ['ajax']) && $this->info ['ajax'] == true && ! isset ( $this->info ['ajaxId'] )) {
            throw new Exception ( 'You can not use ajax without specifying a id to load the content. Please use $grid->ajaxId()' );
        }
        

        $url = parent::getUrl ( 'comm' );
        

        if ($this->_adapter == 'db') {
            //Process form, if necessary, before query
            self::processForm ();
        }
        
        parent::deploy ();
        

        if (! $this->temp ['table'] instanceof Bvb_Grid_Template_Table_Table) {
            $this->setTemplate ( 'table', 'table' );
        }
        

        // The extra fields, they are not part of database table.
        // Usefull for adding links (a least for me :D )
        $grid = $this->printScript();
        
        $images = $this->temp ['table']->images ( $this->imagesUrl );
        
        
        if($this->allowDelete==1 || $this->allowEdit==1)
        {
        	 $pkUrl = $this->getPrimaryKey ();
            $urlFinal = '';
            foreach ( $pkUrl as $value ) {
                $urlFinal .= $value . ':{{' . $value . '}}-';
            }
            
            $urlFinal = trim ( $urlFinal, '-' );
            
        }

        if ($this->allowEdit == 1) {
            if (! is_array ( $this->extra_fields )) {
                $this->extra_fields = array ();
            }
            // Remove the unnecessary URL params
            #$removeParams = array ('filters', 'add' );
            $removeParams = array ('filters', 'add', 'edit', 'comm' );
            

            foreach ( array_keys ( $this->info ['add'] ['fields'] ) as $key ) {
                array_push ( $removeParams, $key );
            }
            $url = parent::getUrl ( $removeParams );
            
           

            array_unshift ( $this->extra_fields, array ('position' => 'left', 'name' => 'E', 'decorator' => "<a href=\"$url/edit/1/comm/" . "mode:edit;[" . $urlFinal . "]\" > " . $images ['edit'] . "</a>", 'edit' => true ) );
        
        }
        
        if ($this->allowDelete) {
            if (! is_array ( $this->extra_fields )) {
                $this->extra_fields = array ();
            }
            
            array_unshift ( $this->extra_fields, array ('position' => 'left', 'name' => 'D', 'decorator' => "<a href=\"#\" onclick=\"confirmDel('" . $this->__ ( 'Are you sure?' ) . "','$url/comm/" . "mode:delete;[" . $urlFinal . "]');\" > " . $images ['delete'] . "</a>", 'delete' => true ) );
        }
        
        if (strlen ( $this->message ) > 0) {
            $grid .= str_replace ( "{{value}}", $this->message, $this->temp ['table']->formMessage ( $this->messageOk ) );
        }
        
        if (((isset ( $this->ctrlParams ['edit'] ) && $this->ctrlParams ['edit'] == 1) || @$this->ctrlParams ['add'] == 1 || @$this->info ['double_tables'] == 1) || ($this->formPost == 1 && $this->formSuccess == 0)) {
            
            if (($this->allowAdd == 1 && $this->_editNoForm != 1) || ($this->allowEdit == 1 && strlen ( $this->_comm ) > 1)) {
                

                // Remove the unnecessary URL params
                



                $removeParams = array ('filters', 'add' );
                
                foreach ( array_keys ( $this->info ['add'] ['fields'] ) as $key ) {
                    array_push ( $removeParams, $key );
                }
                
                $url = parent::getUrl ( $removeParams );
                
                $grid .= "<form method=\"post\" action=\"$url\">" . $this->temp ['table']->formGlobal () . self::gridForm () . "</form><br><br>";
            
            }
        }
        
        $grid .= "<input type=\"hidden\" name=\"inputId\" id=\"inputId\">";
        
        if ((isset ( $this->info ['double_tables'] ) && $this->info ['double_tables'] == 1) || (@$this->ctrlParams ['edit'] != 1 && @$this->ctrlParams ['add'] != 1)) {
            
            if (($this->formPost == 1 && $this->formSuccess == 1) || $this->formPost == 0) {
                
                $grid .= self::buildHeader ();
                $grid .= self::buildTitltesTable ( parent::buildTitles () );
                $grid .= self::buildFiltersTable ( parent::buildFilters () );
                $grid .= self::buildGridTable ( parent::buildGrid () );
                $grid .= self::buildSqlexpTable ( parent::buildSqlExp () );
                $grid .= self::pagination ();
            
            }
        }
        $grid .= $this->temp ['table']->globalEnd ();
        

        if (isset ( $this->ctrlParams ['gridmod'] ) && $this->ctrlParams ['gridmod'] == 'ajax' && $this->info ['ajax'] == true) {

            echo $grid;
            die();
            return '';
        }
        
        return $grid;
    
    }


    
    function printScript() {
        
        
        if(isset($this->info ['ajax']) && $this->info['ajax']==1)
        {
            $useAjax = 1;
        }else {
            $useAjax  =0;
        }

        $script = "<script language=\"javascript\" type=\"text/javascript\">
        
function confirmDel(msg, url)
{

    if(confirm(msg))
    {
        window.location = url;
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
    xmlhttp.open(\"GET\", '".$this->_baseUrl."/'+url,true);

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
        filtro[i] = '\"'+escape(fieldsArray[i])+'\":\"'+escape(document.getElementById(fieldsArray[i]).value)+'\"';
    }

    filtro = \"{\"+filtro+\"}\";
    ";
        
        if($useAjax ==1)
        {
            $script .= "gridAjax('{$this->info['ajaxId']}',url+'/filters/'+filtro);";
        }else{
            $script .= "window.location=url+'/filters/'+filtro;";
        }
        

        $script .="
    } 
        </script>";
        
        return $script;
    }


    /**
     *
     * @return unknown
     */
    function addForm($form) {

        
        $form = $this->object2array ( $form );
        
        $fieldsGet = $form ['fields'];
        $fields = array ();
        
        if (is_array ( $fieldsGet )) {
            foreach ( $fieldsGet as $value ) {
                $fields [$value ['options'] ['field']] = $value ['options'];
            }
        }
        

        $options = $form ['options'];
        

        if (is_array ( $this->data ['table'] )) {
            $this->_crudJoin = true;
        }
        
        @$this->info ['crud'] = array ('primaryKey' => $options ['primaryKey'], 'relations' => $options ['relations'] );
        

        $this->info ['double_tables'] = isset ( $options ['double_tables'] ) ? $options ['double_tables'] : '';
        
        if (isset ( $options ['delete'] )) {
            if ($options ['delete'] == 1) {
                $this->delete = array ('allow' => 1 );
                
                if (isset ( $options ['onDeleteAddWhere'] )) {
                    $this->info ['delete'] ['where'] = $options ['onDeleteAddWhere'];
                }
            }
        }
        
        @$this->info ['delete'] ['cascadeDelete'] = $form ['cascadeDelete'];
        
        if ($options ['add'] == 1) {
            $this->add = array ('allow' => 1, 'button' => $options ['button'], 'fields' => $fields, 'force' => @$options ['onAddForce'] );
        }
        
        if (isset ( $options ['edit'] )) {
            if ($options ['edit'] == 1) {
                @$this->edit = array ('allow' => 1, 'button' => $options ['button'], 'fields' => $fields, 'force' => $options ['onEditForce'] );
            }
        }
        if (isset ( $options ['onUpdateAddWhere'] )) {
            $this->info ['edit'] ['where'] = $options ['onUpdateAddWhere'];
        }
        return $this;
    }

}

