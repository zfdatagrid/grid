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
 * @package    Mascker_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    0.1  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */


class Bvb_Grid_DataGrid {

    
    /**
     * Var that holds the Zend_Db_Select object when 
     * using the method queryFromZendDbSelect
     *
     * @var Zend_Db_Select
     */
    private $_selectZendDb = false;

    /**
     * The query object containg the total records from Zend_Db_Select
     *
     * @var Zend_Db_Select
     */
    private $_selectCount = false;

    /**
     * The query object from Zend_Db_Select
     *
     * @var Zend_Db_Select
     */
    private $_select = false;

    /**
     * Bool to check if the query has already been executed
     *
     * @var unknown_type
     */
    private $_searchPerformedInArray = false;

    /**
     * What kind of source do we have 
     *
     * @var string
     */
    protected $_adapter = 'db';

    
    /**
     * Array containing all data
     *
     * @var unknown_type
     */
    public $arrayData = array ();

    
    /**
     * Apply or not the htmlspecialchars function to output
     *
     * @var unknown_type
     */
    public $escapeOutput = true;

    /**
     * Fields order
     *
     * @var unknown_type
     */
    private $_fieldsOrder;

    
    /**
     * If we are using a sorce that is a  URL
     *
     * @var unknown_type
     */
    private $sourceIsExternal = 0;

    
    /**
     * The path where we can find the library 
     * Usally is lib or library
     *
     * @var unknown_type
     */
    public $libraryDir = 'library';

    
    /**
     * Cache var
     *
     * @var unknown_type
     */
    public static $_cache;

    /**
     * classes location
     *
     * @var array
     */
    protected $template = array ();

    /**
     * templates type to be used
     *
     * @var unknown_type
     */
    protected $_templates;

    /**
     * dir and prefix list to be used when formatting fields 
     *
     * @var unknown_type
     */
    protected $_formatter;

    
    /**
     * Number of results per page
     *
     * @var int
     */
    protected $pagination = 15;

    
    /**
     * Type of exportation available
     *
     * @var array
     */
    public $export = array ('pdf', 'word', 'wordx', 'excel', 'print', 'xml', 'csv', 'ods', 'odt' );

    /**
     *  All info that is not directly related to the database
     */
    public $info = array ();

    /**
     *  Save the result of the describeTables
     */
    protected $_describeTables = array ();

    /**
     *  Registry for PK
     */
    protected $_getPrimaryKey = array ();

    /**
     *  Where part from query
     */
    protected $_queryWhere = false;

    /**
     *  DB Adapter
     *
     * @var object
     */
    protected $_db;

    /**
     *  Baseurl
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     *  Array containing the query result from table(s)
     *
     * @var array
     */
    protected $_result;

    /**
     * Total records from db query
     *
     * @var int
     */
    protected $_totalRecords;

    /**
     *  Array containing field titles
     *
     * @var array
     */
    protected $_titles;

    /**
     *  Array containing table(s) fields
     *
     * @var array
     */
    protected $_fields = array ();

    /**
     *  Where initially defined by user
     *
     * @var string
     */
    protected $_where;

    /**
     * Filters list
     *
     * @var array
     */
    public $filters;

    /**
     *  Filters values inserted by the user
     *
     * @var array
     */
    protected $_filtersValues;

    /**
     *  All information databse related
     *
     * @var array
     */
    public $data = array ();

    /**
     *  Params list
     *
     * @var array
     */
    public $params = array ();

    /**
     *  URL params
     *
     * @var string
     */
    public $ctrlParams;

    /**
     *  Extra fields array
     *
     * @var array
     */
    public $extra_fields;

    /**
     * Final fields list (after all procedures).
     * 
     *
     * @var unknown_type
     */
    protected $_finalFields;

    /**
     * colspan to apply
     *
     * @var unknown_type
     */
    public $_colspan;

    /**
     * Number of hidden fields
     * used to calculate the colspan
     *
     * @var int
     */
    public $totalHiddenFields;

    /**
     * Check if everything was analyzed
     *
     * @var unknown_type
     */
    private $consolidated = 0;

    /**
     *Use cache or not.
     * @var bool
     */
    public $cache = false;

    /**
     * Dir list where to find the validators and filters for CRUD 
     * operations
     *
     * @var array
     */
    protected $_elements = array ();

    /**
     * Elements types allowed in forms
     *
     * @var array
     */
    private $_elementsAllowed = array ('filter', 'validator' );

    /**
     * The field to set order by, if we have a horizontal row
     *
     * @var string
     */
    private $fieldHorizontalRow;

    /**
     *  Template instance
     *
     * @var unknown_type
     */
    protected $temp;

    /**
     * Instanciated templates classes
     *
     * @var unknown_type
     */
    public $activeTemplates = array ();

    
    /**
     * If the query has group by
     */
    private $hasGroup = 0;

    
    /**
     * Result untouched
     *
     * @var array
     */
    private $_resultRaw;

    
    /**
     * When using multiple grids in the same page we can only
     * use url params for one grid.
     * 
     *  
     * This should be fixed for v 1.0
     *
     * @var bool
     */
    protected $_isPrimaryGrid = true;


    
    /**
     *  The __construct function receives the db adapter. All information related to the
     *  URL is also processed here
     * 
     * @var $db = Zend_Db_Adapter_Abstract
     *
     * @param array $data
     */
    function __construct($db) {

        
        //Iniciate adapter
        $this->_db = $db;
        $this->_db->setFetchMode ( Zend_Db::FETCH_OBJ );
        

        //Instanciate the Zend_Db_Select object
        $this->_select = $this->_db->select ();
        
        //Get the controller params and baseurl to use with filters
        $this->ctrlParams = Zend_Controller_Front::getInstance ()->getRequest ()->getParams ();
        $this->_baseUrl = Zend_Controller_Front::getInstance ()->getBaseUrl ();
        
        /**
         * plugins loaders
         */
        $this->_formatter = new Zend_Loader_PluginLoader ( );
        $this->_elements ['filter'] = new Zend_Loader_PluginLoader ( );
        $this->_elements ['validator'] = new Zend_Loader_PluginLoader ( );
        

        //Templates loading
        if (is_array ( $this->export )) {
            foreach ( $this->export as $temp ) {
                $this->_templates [$temp] = new Zend_Loader_PluginLoader ( array () );
            }
        
        }
        

        // Add Zend_Validate and Zend_Filter to the form element
        $this->addElementDir ( 'Zend/Filter', 'Zend_Filter', 'filter' );
        $this->addElementDir ( 'Zend/Validate', 'Zend_Validate', 'validator' );
        

        // Add the formatter fir for fields content
        $this->addFormatterDir ( 'Bvb/Grid/Formatter', 'Bvb_Grid_Formatter' );
        

        // Add the templates dir's
        $this->addTemplateDir ( 'Bvb/Grid/Template/Table', 'Bvb_Grid_Template_Table', 'table' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Pdf', 'Bvb_Grid_Template_Pdf', 'pdf' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Print', 'Bvb_Grid_Template_Print', 'print' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Word', 'Bvb_Grid_Template_Word', 'word' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Wordx', 'Bvb_Grid_Template_Wordx', 'wordx' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Csv', 'Bvb_Grid_Template_Csv', 'csv' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Xml', 'Bvb_Grid_Template_Xml', 'xml' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Ods', 'Bvb_Grid_Template_Ods', 'ods' );
        $this->addTemplateDir ( 'Bvb/Grid/Template/Odt', 'Bvb_Grid_Template_Odt', 'odt' );
    
    }


    /**
     * If set to false, then this grid won't care about any 
     * get vars. This is needed if we want to use more than one 
     * grid per page
     *
     * @param bool $value
     */
    public function setPrimaryGrid($value) {

        $this->_isPrimaryGrid = $value;
    }


    /**
     * Define the adapter to use
     *
     * @param string $adapter
     */
    function setAdapter($adapter) {

        $this->_adapter = strtolower ( $adapter ) != 'db' ? 'array' : 'db';
        
        return $this;
    }


    
    /**
     * Enter data using a csv file
     *
     * @param string $file
     * @param string $field
     * @param string $separator
     * @return unknown
     */
    function setDataFromCsv($file, $field = null, $separator = ',') {

        
        $this->_adapter = 'array';
        

        if ($this->cache ['use'] == 1) {
            $cache = $this->cache ['instance'];
            
            if (! $final = $cache->load ( md5 ( 'array' . $file ) )) {
                

                $row = 0;
                $handle = fopen ( $file, "r" );
                while ( ($data = fgetcsv ( $handle, 1000, $separator )) !== FALSE ) {
                    $num = count ( $data );
                    
                    if (null != $field) {
                        for($c = 0; $c < $num; $c ++) {
                            $final [$row] [$field [$c]] = $data [$c];
                        }
                    } else {
                        if ($row == 0) {
                            for($c = 0; $c < $num; $c ++) {
                                $field [] = $data [$c];
                            }
                        
                        } else {
                            for($c = 0; $c < $num; $c ++) {
                                $final [$row - 1] [$field [$c]] = $data [$c];
                            }
                        }
                    }
                    
                    $row ++;
                }
                
                fclose ( $handle );
                
                $cache->save ( $final, md5 ( 'array' . $file ), array ($this->cache ['tag'] ) );
                $cache->save ( $field, md5 ( 'field' . $file ), array ($this->cache ['tag'] ) );
            
            } else {
                $final = $cache->load ( md5 ( 'array' . $file ) );
                $field = $cache->load ( md5 ( 'field' . $file ) );
            }
        
        } else {
            
            $row = 0;
            $handle = fopen ( $file, "r" );
            while ( ($data = fgetcsv ( $handle, 1000, $separator )) !== FALSE ) {
                $num = count ( $data );
                

                if (null != $field) {
                    
                    for($c = 0; $c < $num; $c ++) {
                        $final [$row] [$field [$c]] = $data [$c];
                    }
                

                } else {
                    if ($row == 0) {
                        for($c = 0; $c < $num; $c ++) {
                            $field [] = $data [$c];
                        }
                    
                    } else {
                        for($c = 0; $c < $num; $c ++) {
                            $final [$row - 1] [$field [$c]] = $data [$c];
                        }
                    }
                }
                
                $row ++;
            }
            
            fclose ( $handle );
        }
        

        $this->addArrayColumns ( $field );
        $this->addArrayData ( $final );
        
        return $this;
    
    }


    
    /**
     * Set the data using a XML file
     *
     * @param string $url
     * @param bool $loop
     * @param bool $columns
     */
    function setDataFromXml($url, $loop = null, $columns = null, $type = 'file') {

        $this->_adapter = 'array';
        
        if ($this->cache ['use'] == 1) {
            $cache = $this->cache ['instance'];
            
            if (! $xml = $cache->load ( md5 ( $url ) )) {
                
                if ($type != 'string') {
                    $xml = simplexml_load_file ( $url );
                } else {
                    $xml = simplexml_load_string ( $url );
                }
                $xml = $this->object2array ( $xml );
                $cache->save ( $xml, md5 ( $url ), array ($this->cache ['tag'] ) );
            } else {
                $xml = $cache->load ( md5 ( $url ) );
            }
        } else {
            
            if ($type != 'string') {
                $xml = simplexml_load_file ( $url );
            } else {
                $xml = simplexml_load_string ( $url );
            }
            $xml = $this->object2array ( $xml );
        }
        

        $cols = explode ( ',', $loop );
        if (is_array ( $cols )) {
            foreach ( $cols as $value ) {
                $xml = $xml [$value];
            }
        }
        

        //Remove possible arrays
        for($i = 0; $i < count ( $xml ); $i ++) {
            foreach ( $xml [$i] as $key => $final ) {
                if (! is_string ( $final )) {
                    unset ( $xml [$i] [$key] );
                }
            }
        }
        

        if (is_array ( $columns )) {
            foreach ( $columns as $value ) {
                $columns = $columns [$value];
            }
        } else {
            $columns = array_keys ( $xml [0] );
        }
        
        $this->addArrayColumns ( $columns );
        $this->addArrayData ( $xml );
        
        return $this;
    
    }


    
    /**
     * Set the data using a JSON formatted value
     *
     * @param string $array
     * @param bool $file
     * @param bool $loop
     * @param bool $columns
     */
    function setDataFromJson($array, $file = false, $loop = null, $columns = null) {

        
        $this->_adapter = 'array';
        
        if (true === $file) {
            
            if ($this->cache ['use'] == 1) {
                $cache = $this->cache ['instance'];
                
                if (! $result = $cache->load ( md5 ( $array ) )) {
                    $result = file_get_contents ( $array );
                    
                    $cache->save ( $result, md5 ( $array ), array ($this->cache ['tag'] ) );
                } else {
                    $result = $cache->load ( md5 ( $array ) );
                }
            } else {
                $result = file_get_contents ( $array );
            }
        

        } else {
            $result = $array;
        }
        
        $xml = Zend_Json::decode ( $result, true );
        
        $cols = explode ( ',', $loop );
        if (is_array ( $cols )) {
            foreach ( $cols as $value ) {
                $xml = $xml [$value];
            }
        }
        

        //Remove possible arrays
        for($i = 0; $i < count ( $xml ); $i ++) {
            foreach ( $xml [$i] as $key => $final ) {
                if (! is_string ( $final )) {
                    unset ( $xml [$i] [$key] );
                }
            }
        }
        
        if (is_array ( $columns )) {
            foreach ( $columns as $value ) {
                if (is_string ( $value ))
                    $columns = $columns [$value];
            }
        } else {
            $columns = array_keys ( $xml [0] );
        }
        

        $this->addArrayColumns ( $columns );
        $this->addArrayData ( $xml );
        
        return $this;
    
    }


    
    /**
     * Set the data using an array
     *
     * @param array $array
     */
    function setDataFromArray($array) {

        
        $this->_adapter = 'array';
        
        $this->addArrayColumns ( array_keys ( $array [0] ) );
        $this->addArrayData ( $array );
        
        return $this;
    
    }


    
    /**
     *  The translator
     *
     * @param string $message
     * @return string
     */
    function __($message) {

        
        if (Zend_Registry::isRegistered ( 'Zend_Translate' )) {
            $message = Zend_Registry::get ( 'Zend_Translate' )->translate ( $message );
        }
        return $message;
    }


    
    /**
     *  Use the overload function so we can return an object to  make possibler
     *  the use of 
     * $grid->from('barcelos')
     *             ->noFilters(1)->
     *             ->noOrder(1);
     * @param string $name
     * @param string $value
     * @return unknown
     */
    function __call($name, $value) {

        
        if (substr ( strtolower ( $name ), 0, 3 ) == 'set') {
            $name = substr ( $name, 3 );
        }
        $this->__set ( $name, $value [0] );
        return $this;
    }


    
    /**
     * @param string $var
     * @param string $value
     */
    function __set($var, $value) {

        
        // The data variavel contains options related to the query,
        // because of thatm they need to go to a separate Array
        $data = array ('from', 'order', 'where', 'primaryKey', 'table', 'fields', 'hide' );
        if (in_array ( $var, $data )) {
            
            if ($var == 'from' && ! strpos ( " ", trim ( $value ) )) {
                $this->data ['from'] = trim ( $value );
                $this->data ['table'] = trim ( $value );
                $this->_adapter = 'db';
            
            } else {
                $this->data [$var] = $value;
            }
        } else {
            $this->info [$var] = $value;
        }
    }


    
    /**
     * Define which allowed types of exportation 
     *
     * @param array $var
     * @return $this
     */
    function export(array $var) {

        $this->export = $var;
        return $this;
    }


    
    /**
     * Get the table name using the field name.
     * This happens when we are using joins, and the field
     * has a table sufix.
     *
     * @param string $field
     * @return string
     */
    function getTableNameFromField($field) {

        $tableAb = reset ( explode ( '.', $field ) );
        
        return $this->data ['table'] [$tableAb];
    }


    
    /**
     *  Get table description and then save it to a array.
     *
     * @param array|string $table
     * @return array
     */
    function getDescribeTable($table, $field = '') {

        
        if (strpos ( $field, '.' )) {
            $tableAb = reset ( explode ( '.', $field ) );
            
            $table = $this->data ['table'] [$tableAb];
        
        }
        
        if (is_array ( $table )) {
            
            $table = $this->data ['table'] [reset ( explode ( '.', $this->info ['crud'] ['primaryKey'] ) )];
        
        }
        
        if (! isset ( $this->_describeTables [$table] ) || ! @is_array ( $this->_describeTables [$table] )) {
            
            if ($this->cache ['use'] == 1) {
                
                $cache = $this->cache ['instance'];
                

                if (! $describe = $cache->load ( md5 ( 'describe' . $table ) )) {
                    $describe = $this->_db->describeTable ( $table );
                    $cache->save ( $describe, md5 ( 'describe' . $table ), array ($this->cache ['tag'] ) );
                
                } else {
                    $describe = $cache->load ( md5 ( 'describe' . $table ) );
                }
            

            } else {
                
                $describe = $this->_db->describeTable ( $table );
            }
            

            $this->_describeTables [$table] = $describe;
        }
        

        return $this->_describeTables [$table];
    }


    
    /**
     * Add a new coolumn to the list, if not there yet
     * 
     * We must also check if one of the parameters is an horizontal row.
     * It it is, we mus define it on the $this->info array
     *
     * @param string $field
     * @param array $options
     * @return unknown
     */
    function addColumn($field, $options = array()) {

        $this->updateColumn ( $field, $options );
        
        return $this;
    }


    /**
     * Update data from a column
     *
     * @param string $field
     * @param array $options
     * @return self
     */
    
    function updateColumn($field, $options = array()) {

        
        if (isset ( $this->data ['fields'] ) && is_array ( $this->data ['fields'] )) {
            
            if (! array_key_exists ( $field, $this->data ['fields'] )) {
                
                $this->data ['fields'] [$field] = $options;
                
                if (isset ( $options ['hRow'] )) {
                    if ($options ['hRow'] == 1) {
                        $this->fieldHorizontalRow = $field;
                        $this->info ['hRow'] = array ('field' => $field, 'title' => $options ['title'] );
                    }
                }
                
                if (isset ( $options ['groupBy'] )) {
                    $this->info ['groupby'] = $field;
                }
            
            } else {
                
                $this->data ['fields'] [$field] = array_merge_recursive ( $this->data ['fields'] [$field], $options );
            
            }
        

        } else {
            $this->data ['fields'] [$field] = $options;
        }
        
        return $this;
    }


    
    /**
     * Add a new dir to look for when formating a field
     * 
     * @param string $dir
     * @param string $prefix
     * @return $this
     */
    function addFormatterDir($dir, $prefix) {

        
        $this->_formatter->addPrefixPath ( trim ( $prefix, "_" ), trim ( $dir, "/" ) . '/' );
        return $this;
    }


    
    /**
     * Add new elements form dir.
     * TRhey can be filters os validators
     *
     * @param string $dir
     * @param string $prefix
     * @param string $type
     * @return $this
     */
    function addElementDir($dir, $prefix, $type = 'filter') {

        
        if (! in_array ( strtolower ( $type ), $this->_elementsAllowed )) {
            throw new Exception ( 'Type not recognized' );
        }
        
        $this->_elements [$type]->addPrefixPath ( trim ( $prefix, "_" ), trim ( $dir, "/" ) . '/' );
        
        return $this;
    }


    
    /**
     * Format a field 
     *
     * @param unknown_type $value
     * @param unknown_type $formatter
     * @return unknown
     */
    function applyFormat($value, $formatter) {

        
        if (is_array ( $formatter )) {
            $result = $formatter [0];
            $options = $formatter [1];
        } else {
            $result = $formatter;
            $options = null;
        }
        
        $class = $this->_formatter->load ( $result );
        
        $t = new $class ( $options );
        $return = $t->format ( $value );
        

        return $return;
    }


    
    /**
     *  All information related with database.
     * Filters, extra fields, etc, etc
     * @param string $data
     * 
     * */
    function setData($data) {

        $this->data = $data ['data'];
        $this->info = $data;
        if (! is_array ( $this->data ['table'] )) {
            $this->data ['table'] = $this->data ['from'];
        }
    }


    
    /**
     * Create a grid using XML
     * 
     * ALPHA!!!!!!!
     *
     */
    function setGridFromXml($file) {

        $t2 = '';
        $file = rtrim ( $file, ".xml" ) . ".xml";
        $xml = $this->object2array ( simplexml_load_file ( $file ) );
        

        if (isset ( $xml ['data'] ['where'] ) && strlen ( $xml ['data'] ['where'] ) > 0) {
            $final = $xml ['data'] ['where'];
            preg_match_all ( "/{eval}(.*?){\/eval}/", $final, $t );
            $t2 = $t;
            $i = 0;
            foreach ( $t2 [1] as $value ) {
                $h = eval ( "return " . $value . ";" );
                $final = str_replace ( $t [0] [$i], $h, $final );
                $i ++;
            }
            $xml ['data'] ['where'] = $final;
        }
        
        foreach ( $xml ['data'] ['fields'] as $key => $final ) {
            if (isset ( $final ['@attributes'] ) && is_array ( $final ['@attributes'] )) {
                unset ( $xml ['data'] ['fields'] [$key] );
                $xml ['data'] ['fields'] [$key . " AS " . $final ['@attributes'] ['as']] = $final;
            }
        }
        
        self::setData ( $xml );
    
    }


    
    /**
     *  The allowed fields from a table
     *
     * @param string $mode
     * @param string $table
     * @return string
     */
    function getFields($mode = 'edit', $table) {

        
        $get = $this->info [$mode] ['fields'];
        if (! is_array ( $get )) {
            $get = $this->getTableFields ( $table );
        }
        return $get;
    }


    
    /**
     *  Get table fields
     *
     * @param string $table
     * @return string
     */
    function getTableFields($table) {

        
        $table = $this->getDescribeTable ( $table );
        foreach ( array_keys ( $table ) as $key ) {
            $val [$key] = $key;
        }
        return $val;
    }


    
    /**
     * pagination definition
     *
     */
    function setPagination($number = 15) {

        
        $this->pagination = ( int ) $number;
        return $this;
    }


    
    /**
     *  Calculate colspan for pagination and top
     *
     * @return int
     */
    function colspan() {

        
        $totalFields = count ( $this->_fields );
        $a = 0;
        $i = 0;
        foreach ( $this->data ['fields'] as $value ) {
            if (isset ( $value ['hide'] )) {
                if ($value ['hide'] == 1) {
                    $i ++;
                }
            }
            if (isset ( $value ['hRow'] )) {
                if ($value ['hRow'] == 1) {
                    $totalFields --;
                }
            }
        }
        
        $totalFields = $totalFields - $i;
        if (isset ( $this->info ['delete'] ['allow'] ) && $this->info ['delete'] ['allow'] == 1) {
            $a ++;
        }
        
        if (@$this->info ['edit'] ['allow'] == 1) {
            $a ++;
        }
        
        $totalFields = $totalFields + $a;
        $colspan = $totalFields + count ( $this->extra_fields );
        
        if (@is_object ( $this->temp [$this->output] )) {
            $this->temp [$this->output]->colSpan = $colspan;
        }
        return $colspan;
        #return count ( $this->_fields ) - $this->totalHiddenFields + count($this->extra_fields);
    }


    
    /**
     *  Apply quoteidentifier to the table fields
     *
     * @return string
     */
    function buildSelectFields($values) {

        
        if ($this->sourceIsExternal == 1) {
            return implode ( ', ', $values );
        }
        

        foreach ( $values as $value ) {
            

            if (isset ( $this->data ['fields'] [$value] ['sqlexp'] )) {
                $sqlExp = trim ( $this->data ['fields'] [$value] ['sqlexp'] );
                
                if (stripos ( $sqlExp, " AS " )) {
                    $fields [] = $sqlExp;
                } else {
                    $fields [] = $sqlExp . ' AS ' . str_replace ( '.', '', $value );
                }
            

            } else {
                
                if (stripos ( $value, ' AS ' )) {
                    $asFinal = substr ( $value, stripos ( $value, ' as' ) + 4 );
                    $asValue = substr ( $value, 0, stripos ( $value, ' as' ) );
                    
                    $fields [] = $asValue . ' AS ' . $asFinal;
                
                } elseif (strpos ( $value, "." )) {
                    $ini = substr ( $value, 0, (strpos ( $value, "." )) );
                    $fields [] = $ini . substr ( $value, strpos ( $value, "." ) );
                } else {
                    $fields [] = $value;
                }
            }
        }
        

        return implode ( ', ', $fields );
    }


    
    /**
     * Searchj type to be used in filters
     * By default its LIKE
     *
     * @param unknown_type $filtro
     * @param unknown_type $key
     * @param string $key
     * @return unknown
     */
    function buildSearchType($filtro, $key, $field) {

        
        $fieldsSemAsFinal = $this->removeAsFromFields ();
        if (@$fieldsSemAsFinal [$key] ['searchType'] != "") {
            $op = @$fieldsSemAsFinal [$key] ['searchType'];
        }
        $op = @strtolower ( $op );
        
        if (substr ( $filtro, 0,2 )=='>=') {
            $op = '>=';
            $filtro = substr ( $filtro, 2 );
        }elseif ($filtro [0] == '>') {
            $op = '>';
            $filtro = substr ( $filtro, 1 );
        }  elseif (substr ( $filtro, 0,2 ) == '<=') {
            $op = '<=';
            $filtro = substr ( $filtro, 2 );
        } elseif ($filtro [0] == '<') {
            $op = '<';
            $filtro = substr ( $filtro, 1 );
        } elseif ($filtro [0] == '*' and substr ( $filtro, - 1 ) == '*') {
            $op = 'like';
            $filtro = substr ( $filtro, 1, - 1 );
        } elseif ($filtro [0] == '*' and substr ( $filtro, - 1 ) != '*') {
            $op = 'llike';
            $filtro = substr ( $filtro, 1 );
        } elseif ($filtro [0] != '*' and substr ( $filtro, - 1 ) == '*') {
            $op = 'rlike';
            $filtro = substr ( $filtro, 0, - 1 );
        }

        switch ($op) {
            case 'equal' :
            case '=' :
                $this->_select->where ( $field . ' = ?', $filtro );
                break;
            case 'rlike' :
                $this->_select->where ( new Zend_Db_Expr ( $field . " LIKE " . $this->_db->quote ( $filtro . "%" ) ) );
                break;
            case 'llike' :
                $this->_select->where ( new Zend_Db_Expr ( $field . " LIKE " . $this->_db->quote ( "%" . $filtro ) ) );
                break;
            case '>=' :
                $this->_select->where ( new Zend_Db_Expr ( $field . " >= " . $this->_db->quote ( $filtro ) ) );
                break;
            case '>' :
                $this->_select->where ( new Zend_Db_Expr ( $field . " > " . $this->_db->quote ( $filtro ) ) );
                break;
            case '<>' :
            case '!=' :
                $this->_select->where ( new Zend_Db_Expr ( $field . " <> " . $this->_db->quote ( $filtro ) ) );
                break;
            case '<=' :
                $this->_select->where ( new Zend_Db_Expr ( $field . " <= " . $this->_db->quote ( $filtro ) ) );
                break;
            case '<' :
                $this->_select->where ( new Zend_Db_Expr ( $field . " < " . $this->_db->quote ( $filtro ) ) );
                break;
            case 'like' :
            default :
                $this->_select->where ( new Zend_Db_Expr ( $field . " LIKE " . $this->_db->quote ( "%" . $filtro . "%" ) ) );
                break;
        }
    

    }


    
    /**
     *  Build the query WHERE
     *
     * @return void
     */
    function buildQueryWhere() {

        
        if ($this->_queryWhere) {
            return;
        }
        if (strlen ( trim ( $this->_where ) ) > 1) {
            $this->_select->where ( $this->_where );
        }
        
        //Vamos criar a aray para sabermos o valor dos filtro
        $valor_filters = array ();
        $filters = @urldecode ( $this->ctrlParams ['filters'] );
        $filters = str_replace ( "filter_", "", $filters );
        $filters = Zend_Json::decode ( $filters );
        $fieldsSemAsFinal = $this->removeAsFromFields ();
        

        if (is_array ( $filters )) {
            foreach ( $filters as $key => $filtro ) {
                $key = str_replace ( "bvbdot", ".", $key );
                if (strlen ( $filtro ) == 0 || ! in_array ( $key, $this->map_array ( $this->_fields, 'replace_AS' ) )) {
                    unset ( $filters [$key] );
                } else {
                    $oldKey = $key;
                    if (@$fieldsSemAsFinal [$key] ['searchField'] != "") {
                        $key = $this->replaceAsString ( $fieldsSemAsFinal [$key] ['searchField'] );
                    }
                    if (@array_key_exists ( 'sqlexp', $this->data ['fields'] [$key] )) {
                        $this->buildSearchType ( $filtro, $oldKey, $key );
                    } else {
                        $this->buildSearchType ( $filtro, $oldKey, $key );
                        $valor_filters [$key] = $filtro;
                    }
                }
            }
        }
        
        $this->_filtersValues = $valor_filters;
        
        return;
    }


    
    /**
     *  Build query. only LIMIT and ORDER
     *
     * @return string
     */
    function buildQuery() {

        
        @$inicio = ( int ) $this->ctrlParams ['start'];
        $order = @$this->ctrlParams ['order'];
        $order1 = explode ( "_", $order );
        $orderf = strtoupper ( end ( $order1 ) );
        

        if ($orderf != 'DESC' and $orderf != 'ASC') {
            
            $orderf = 'ASC';
            $order_field = $order;
            $query_order = $order_field . " $orderf ";
            $this->_select->order ( $query_order );
        } else {
            array_pop ( $order1 );
            $order_field = implode ( "_", $order1 );
            $query_order = $order_field . " $orderf ";
            $this->_select->order ( $query_order );
        }
        

        $this->order [$order_field] = $orderf == 'ASC' ? 'DESC' : 'ASC';
        

        if (! in_array ( $order_field, $this->map_array ( $this->_fieldsOrder, 'replace_AS' ) )) {
            $this->_select->reset ( Zend_Db_Select::ORDER );
            $query_order = '';
            if (@strlen ( $this->data ['order'] ) > 0) {
                $this->_select->order ( array_map ( 'trim', explode ( ',', $this->data ['order'] ) ) );
            }
        }
        

        $query_order = '';
        
        if (strlen ( $this->fieldHorizontalRow ) > 0) {
            
            $split = $this->fieldHorizontalRow;
            if (strlen ( $query_order ) > 4) {
                $query_order = $split . ' ASC ';
            } else {
                $query_order = $this->fieldHorizontalRow . ' ASC ';
            }
            
            $this->_select->order ( $query_order );
        }
        

        if (isset ( $this->info ['groupby'] )) {
            $this->_select->group ( $this->info ['groupby'] );
        }
        

        if (isset ( $this->info ['having'] )) {
            if (is_array ( $this->info ['having'] )) {
                
                if (isset ( $this->info ['having'] ['agregate'] )) {
                    $myCond = $this->info ['having'] ['agregate'] . "(" . $this->info ['having'] ['field'] . ")";
                } else {
                    $myCond = $this->info ['having'] ['field'];
                }
                $this->_select->having ( $myCond . "  " . $this->info ['having'] ['operand'] . " " . $this->info ['having'] ['value'] );
            }
        }
        

        if (isset ( $this->info ['limit'] ) && (strlen ( $this->info ['limit'] ) > 0 || @is_array ( $this->info ['limit'] ))) {
            if (is_array ( $this->info ['limit'] )) {
                $this->_select->limit ( $this->info ['limit'] [1], $this->info ['limit'] [0] );
            } else {
                $this->_select->limit ( $this->info ['limit'] );
            }
        } elseif ($this->pagination > 0) {
            $this->_select->limit ( $this->pagination, $inicio );
        } else {
        }
        

        return true;
    }


    
    /**
     *  Returns the url, without the param(s) specified 
     *
     * @param array|string $situation
     * @return string
     */
    function getUrl($situation = '') {

        
        $url = '';
        $params = $this->ctrlParams;
        if (is_array ( $situation )) {
            foreach ( $situation as $value ) {
                unset ( $params [$value] );
            }
        } else {
            unset ( $params [$situation] );
        }
        

        if (count ( $this->params ) > 0) {
            //User as defined its own params (probably using routes)
            $myParams = array ('comm', 'order', 'filters', 'add', 'edit' );
            $newParams = $this->params;
            foreach ( $myParams as $value ) {
                if (strlen ( $params [$value] ) > 0) {
                    $newParams [$value] = $params [$value];
                }
            }
            $params = $newParams;
        }
        

        $params_clean = $params;
        unset ( $params_clean ['controller'] );
        unset ( $params_clean ['module'] );
        unset ( $params_clean ['action'] );
        


        foreach ( $params_clean as $key => $param ) {
            // Apply the urldecode function to the filtros param, because its  JSON
            if ($key == 'filters') {
                $url .= "/" . trim ( $key ) . "/" . trim ( htmlspecialchars ( urlencode ( $param ), ENT_QUOTES ) );
            } else {
                @$url .= "/" . trim ( $key ) . "/" . trim ( htmlspecialchars ( $param, ENT_QUOTES ) );
            }
        }
        
        if (strlen ( $params ['action'] ) > 0) {
            $action = "/" . $params ['action'];
        }
        

        // Remove the action e controller keys, they are not necessary (in fact they aren't part of url)
        if (array_key_exists ( 'ajax', $this->info )) {
            return $params ['module'] . "/" . $params ['controller'] . $action . $url . "/modo/ajax";
        } else {
            return $this->_baseUrl . "/" . $params ['module'] . "/" . $params ['controller'] . $action . $url;
        }
    }


    /**
     * Check if a var exist 
     *
     * @param string $param
     * @return bool | $param
     */
    function getInfo($param) {

        if (isset ( $this->info [$param] )) {
            return $this->info [$param];
        } else {
            return false;
        }
    
    }


    /**
     *
     *  Build Filters. If defined put the values
     *  Also check if the user wants to hide a field
     *  
     * 
     * @return string
     */
    function buildFilters() {

        
        $return = array ();
        if (isset ( $this->info ['noFilters'] )) {
            return false;
        }
        

        $data = $this->map_array ( $this->_fields, 'replace_AS' );
        $tcampos = count ( $data );
        
        for($i = 0; $i < count ( $this->extra_fields ); $i ++) {
            if ($this->extra_fields [$i] ['position'] == 'left') {
                $return [] = array ('type' => 'extraField', 'class' => $this->template ['classes'] ['filter'], 'position' => 'left' );
            }
        }
        

        for($i = 0; $i < $tcampos; $i ++) {
            if (! isset ( $this->data ['fields'] [$this->_fields [$i]] ['hide'] )) {
                if (@array_key_exists ( $data [$i], $this->filters )) {
                    if (isset ( $this->filters [$data [$i]] ['decorator'] ) && is_array ( $this->filters [$data [$i]] )) {
                        $return [] = array ('type' => 'field', 'value' => $this->filters [$data [$i]] ['decorator'], 'field' => $data [$i] );
                    } else {
                        $return [] = array ('type' => 'field', 'class' => $this->template ['classes'] ['filter'], 'value' => self::formatField ( $data [$i], $data [$i] ), 'field' => $data [$i] );
                    }
                } else {
                    $return [] = array ('type' => 'field', 'class' => $this->template ['classes'] ['filter'], 'field' => $data [$i] );
                }
            }
        }
        

        for($i = 0; $i < count ( $this->extra_fields ); $i ++) {
            if ($this->extra_fields [$i] ['position'] == 'right') {
                $return [] = array ('type' => 'extraField', 'class' => $this->template ['classes'] ['filter'], 'position' => 'right' );
            }
        }
        

        return $return;
    }


    
    /**
     *  Consolidate the fields that are in the array with the one on the table
     *
     * @param array $fields
     * @param string $table
     * @return array
     */
    function consolidateFields($fields, $table) {

        
        $table = $this->_db->describeTable ( $table );
        

        foreach ( $table as $value ) {
            if ($value ['IDENTITY'] === false) {
                $table_fields [] = $value ['COLUMN_NAME'];
            }
        }
        
        foreach ( $fields as $key => $value ) {
            if (! in_array ( $value, $table_fields )) {
                unset ( $fields [$key] );
            }
        }
        //Reset keys
        foreach ( $fields as $value ) {
            $fields_final [] = $value;
        }
        return $fields_final;
    }


    
    /**
     * Apply various functions to arrays
     * @param unknown_type $campos
     * @param unknown_type $callback
     * @return unknown
     */
    function map_array($campos, $callback) {

        
        if (! is_array ( $campos ))
            return FALSE;
        

        $ncampos = array ();
        foreach ( $campos as $key => $value ) {
            if (is_array ( $value ))
                return FALSE;
            

            if (strlen ( $value ) == 0) {
                
                $ncampos [] = stripos ( $key, ' AS ' ) ? substr ( $key, 0, stripos ( $key, ' AS ' ) ) : $key;
            } else {
                $ncampos [] = stripos ( $value, ' AS ' ) ? substr ( $value, 0, stripos ( $value, ' AS ' ) ) : $value;
                ;
            }
        
        }
        
        $campos = $ncampos;
        unset ( $ncampos );
        $ncampos = array ();
        switch ($callback) {
            case 'prepare_replace' :
                foreach ( $campos as $value ) {
                    $ncampos [] = "{{" . $value . "}}";
                }
                break;
            case 'replace_AS' :
                $ncampos = $campos;
                break;
            case 'prepare_output' :
                foreach ( $campos as $value ) {
                    $ncampos [] = htmlspecialchars ( $value );
                }
                break;
            default :
                break;
        }
        

        return $ncampos;
    }


    
    /**
     *  Build the titles with the order links (if wanted)
     *
     * @return string
     */
    function buildTitles() {

        
        $return = array ();
        $url = $this->getUrl ( array ('order', 'start', 'comm' ) );
        $tcampos = count ( $this->_fields );
        

        for($i = 0; $i < count ( $this->extra_fields ); $i ++) {
            if ($this->extra_fields [$i] ['position'] == 'left') {
                $return [$this->extra_fields [$i] ['name']] = array ('type' => 'extraField', 'value' => $this->extra_fields [$i] ['name'], 'position' => 'left' );
            }
        }
        
        $titles = $this->map_array ( $this->_fields, 'replace_AS' );
        
        $novaData = array ();
        
        if (is_array ( $this->data ['fields'] )) {
            foreach ( $this->data ['fields'] as $key => $value ) {
                $nkey = stripos ( $key, ' AS ' ) ? substr ( $key, 0, stripos ( $key, ' AS ' ) ) : $key;
                $novaData [$nkey] = $value;
            }
        }
        
        $links = $this->_fields;
        

        for($i = 0; $i < $tcampos; $i ++) {
            if (isset ( $this->ctrlParams ['order'] )) {
                $this->order [reset ( explode ( '_', $this->ctrlParams ['order'] ) )] = strtoupper ( end ( explode ( '_', $this->ctrlParams ['order'] ) ) ) == 'ASC' ? 'DESC' : 'ASC';
            }
            $fieldsToOrder = $this->reset_keys ( $this->data ['fields'] );
            

            if (isset ( $fieldsToOrder [$i] ['orderField'] ) && strlen ( $fieldsToOrder [$i] ['orderField'] ) > 0) {
                $orderFinal = $fieldsToOrder [$i] ['orderField'];
            } else {
                $orderFinal = $titles [$i];
            }
            
            $order = $orderFinal == @key ( $this->order ) ? $this->order [$orderFinal] : 'ASC';
            

            if (! isset ( $novaData [$titles [$i]] ['hide'] )) {
                
                if ($titles [$i] == @key ( $this->order )) {
                    if ($order == 'ASC') {
                        $order_img = 'desc';
                    } else {
                        $order_img = 'asc';
                    }
                    $img = @$this->template ['images'] [$order_img];
                
                } else {
                    $img = "";
                }
                

                $noOrder = isset ( $this->info ['noOrder'] ) ? $this->info ['noOrder'] : '';
                

                if ($noOrder == 1) {
                    $return [$titles [$i]] = array ('type' => 'field', 'name' => $links [$i], 'field' => $links [$i], 'value' => $this->_titles [$links [$i]] );
                } else {
                    $return [$titles [$i]] = array ('type' => 'field', 'name' => $titles [$i], 'field' => $orderFinal, 'url' => "$url/order/{$orderFinal}_$order", 'img' => $img, 'value' => $this->_titles [$links [$i]] );
                }
            }
        }
        

        for($i = 0; $i < count ( $this->extra_fields ); $i ++) {
            if ($this->extra_fields [$i] ['position'] == 'right') {
                $return [$this->extra_fields [$i] ['name']] = array ('type' => 'extraField', 'value' => $this->extra_fields [$i] ['name'], 'position' => 'right' );
            }
        }
        
        $this->_finalFields = $return;
        
        return $return;
    }


    
    /**
     * Add the columns using an array
     */
    function addArrayColumns(array $columns) {

        
        $filter = array ();
        
        if ($this->_adapter != 'array')
            return false;
        
        foreach ( $columns as $value ) {
            if (is_array ( $value )) {
                $this->addArrayColumns ( $value );
            } else {
                $this->addColumn ( $value );
                $filter [$value] = $value;
            }
        }
        
        $this->filters = $filter;
        
        return true;
    }


    
    /**
     * Add the records using an array
     */
    function addArrayData($data) {

        
        if ($this->_adapter != 'array')
            return false;
        

        $this->_result = $data;
        $this->_resultRaw = $data;
        return $this;
    }


    
    /**
     * remove the word 'as' from fields
     *
     * @return unknown
     */
    function removeAsFromFields() {

        
        $fieldsSemAs = $this->data ['fields'];
        if (is_array ( $fieldsSemAs )) {
            foreach ( $fieldsSemAs as $key => $value ) {
                if (strpos ( $key, ' ' ) === false) {
                    $fieldsSemAsFinal [$key] = $value;
                } else {
                    $fieldsSemAsFinal [substr ( $key, 0, strpos ( $key, ' ' ) )] = $value;
                }
            }
        }
        return $fieldsSemAsFinal;
    }


    
    /**
     *Replace dots to avoid JS error
     * @param string $string
     * @return unknown
     */
    function replaceDots($string) {

        
        return str_replace ( ".", "bvbdot", $string );
    }


    
    /**
     * Replace As *.* from queries
     *
     * @param unknown_type $string
     * @return unknown
     */
    function replaceAsString($string) {

        
        return stripos ( $string, ' AS ' ) ? substr ( $string, 0, stripos ( $string, ' AS ' ) ) : $string;
    }


    
    /**
     * Create the filters when using the array adapter
     */
    function builFilterFromArray($field) {

        
        $filter = array ();
        foreach ( $this->_resultRaw as $value ) {
            $filter [$value [$field]] = $value [$field];
        }
        
        return array_unique ( $filter );
    }


    /**
     * Similar to fetchPairs
     *
     * @param array $array
     * @return array
     */
    function convertResultSetToArrayKeys($array) {

        $final = array ();
        
        foreach ( $array as $value ) {
            $final [$value->field] = $value->value;
        }
        
        return $final;
    
    }


    /**
     *  Field type on the filters area. If the field type is enum, build the options
     *  Also, we first need to check if the user has defined values to presente.
     *  If set, this values override the others
     *
     * @param string $campo
     * @param string $valor
     * @return string
     */
    function formatField($campo, $valor, $options = array()) {

        
        if ($this->_adapter == 'db') {
            //check if we need to load  fields for filters
            if (@is_array ( $this->filters [$valor] ['distinct'] )) {
                $this->filters [$valor] ['distinct'] ['field'] = @$this->replaceAsString ( $this->filters [$valor] ['distinct'] ['field'] );
                $this->filters [$valor] ['distinct'] ['name'] = @$this->replaceAsString ( $this->filters [$valor] ['distinct'] ['name'] );
                
                $distinct = clone $this->_select;
                
                $distinct->reset ( Zend_Db_Select::COLUMNS );
                $distinct->reset ( Zend_Db_Select::ORDER );
                $distinct->reset ( Zend_Db_Select::LIMIT_COUNT );
                $distinct->reset ( Zend_Db_Select::LIMIT_OFFSET );
                
                $distinct->columns ( array ('field' => new Zend_Db_Expr ( "DISTINCT({$this->filters[$valor]['distinct']['field']})" ) ) );
                $distinct->columns ( array ('value' => $this->filters [$valor] ['distinct'] ['name'] ) );
                $distinct->order ( $this->filters [$valor] ['distinct'] ['name'] . ' ASC' );
                $result = $distinct->query ();
                
                $final = $result->fetchAll ();
                
                $final = $this->convertResultSetToArrayKeys ( $final );
                

                $this->filters [$valor] ['values'] = $final;
            }
        
        }
        
        if ($this->_adapter == 'array' && @in_array ( 'distinct', $this->filters [$valor] )) {
            
            $this->filters [$valor] ['values'] = $this->builFilterFromArray ( $campo );
        }
        
        //Remove unwanted url params
        $url = urlencode ( $this->getUrl ( array ('filters', 'start', 'comm' ) ) );
        


        $fieldsSemAsFinal = $this->removeAsFromFields ();
        if (isset ( $fieldsSemAsFinal [$campo] ['searchField'] )) {
            $nkey = $this->replaceAsString ( $fieldsSemAsFinal [$campo] ['searchField'] );
            @$this->_filtersValues [$campo] = $this->_filtersValues [$nkey];
        }
        
        if ($this->_adapter == 'db') {
            if (! is_array ( $this->data ['table'] )) {
                $table = $this->getDescribeTable ( $this->data ['table'] );
            } else {
                $ini = substr ( $campo, 0, (strpos ( $campo, "." )) );
                $table = $this->getDescribeTable ( $this->data ['table'] [$ini] );
            }
        }
        

        if (strpos ( $campo, "." )) {
            $campo_simples = substr ( $campo, strpos ( $campo, "." ) + 1 );
        } else {
            $campo_simples = $campo;
        }
        @$tipo = $table [$campo_simples];
        $tipo = $tipo ['DATA_TYPE'];
        $help_javascript = '';
        

        if (substr ( $tipo, 0, 4 ) == 'enum') {
            $enum = str_replace ( array ('(', ')' ), array ('', '' ), $tipo );
            $tipo = 'enum';
        }
        

        foreach ( array_keys ( $this->filters ) as $value ) {
            

            $hRow = isset ( $this->data ['fields'] [$value] ['hRow'] ) ? $this->data ['fields'] [$value] ['hRow'] : '';
            if (! isset ( $this->data ['fields'] [$value] ['hide'] ) && $hRow != 1) {
                $help_javascript .= "filter_" . $value . ",";
            }
        }
        

        if (@$options ['noFilters'] != 1) {
            $help_javascript = str_replace ( ".", "bvbdot", $help_javascript );
            $onchange = "onchange=\"changeFilters('$help_javascript','$url');\"";
        }
        $opcoes = $this->filters [$campo];
        
        if (strlen ( @$opcoes ['style'] ) > 1) {
            $opt = " style=\"{$opcoes['style']}\"  ";
        } else {
            $opt = " style=\"width:95%\"  ";
        }
        

        if (@is_array ( $opcoes ['values'] )) {
            
            $tipo = 'invalid';
            $avalor = $opcoes ['values'];
            $valor = "<select name=\"$campo\" $opt $onchange id=\"filter_" . $this->replaceDots ( $campo ) . "\"  >";
            $valor .= "<option value=\"\">--" . $this->__ ( 'All' ) . "--</option>";
            foreach ( $avalor as $key => $value ) {
                $selected = isset ( $this->_filtersValues [$campo] ) && $this->_filtersValues [$campo] == $key ? "selected" : "";
                
                $valor .= "<option value=\"" . stripslashes ( $key ) . "\" $selected >" . stripslashes ( $value ) . "</option>";
            }
            $valor .= "</select>";
        }
        

        switch ($tipo) {
            case 'invalid' :
                break;
            case 'enum' :
                $avalor = explode ( ",", substr ( $enum, 4 ) );
                $valor = "<select  id=\"filter_" . str_replace ( ".", "bvbdot", $campo ) . "\" $opt $onchange name=\"\">";
                $valor .= "<option value=\"\">--" . $this->__ ( 'All' ) . "--</option>";
                foreach ( $avalor as $value ) {
                    $value = substr ( $value, 1 );
                    $value = substr ( $value, 0, - 1 );
                    $selected = @$this->_filtersValues [$campo] == $value ? "selected" : "";
                    $valor .= "<option value=\"$value\" $selected >" . ucfirst ( $value ) . "</option>";
                }
                $valor .= "</select>";
                break;
            default :
                $valor = "<input type=\"text\" $onchange id=\"filter_" . @str_replace ( ".", "bvbdot", $campo ) . "\"   class=\"input_p\" value=\"" . @$this->_filtersValues [$campo] . "\" $opt>";
                break;
        }
        
        return $valor;
    }


    
    /**
     * @param unknown_type $campos
     * @return unknown
     */
    function replace_AS($campos) {

        
        return trim ( stripos ( $campos, ' AS ' ) ? substr ( $campos, 0, stripos ( $campos, ' AS ' ) ) : $campos );
    }


    
    /**
     * Escape the output
     */
    function escapeOutput($escape) {

        
        $this->escapeOutput = ( bool ) $escape;
        
        return $this;
    }


    
    /**
     *  The loop for the results.
     *  Check the extra-fields,
     *
     * @return string
     */
    function buildGrid() {

        
        $return = array ();
        

        $extra_fields = $this->extra_fields;
        

        $search = $this->map_array ( $this->_fields, 'prepare_replace' );
        
        foreach ( $this->_fields as $field ) {
            $fields_duble [] = $field;
            if (strpos ( $field, "." )) {
                $fields [] = substr ( $field, strpos ( $field, "." ) + 1 );
            } else {
                $fields [] = $field;
            }
        }
        

        $i = 0;
        

        foreach ( $this->_result as $dados ) {
            
            /**
             *Deal with extrafield from the left
             */
            if (is_array ( $extra_fields )) {
                foreach ( $extra_fields as $value ) {
                    if ($value ['position'] == 'left') {
                        $fi = get_object_vars ( $dados );
                        $new_value = str_replace ( $search, $fi, $value ['decorator'] );
                        if (isset ( $value ['eval'] )) {
                            $evalf = str_replace ( $search, $fi, $value ['eval'] );
                            $new_value = eval ( 'return ' . $evalf );
                        }
                        
                        if (isset ( $value ['format'] )) {
                            $new_value = $this->applyFormat ( $new_value, $value ['format'], $value ['format'] );
                        }
                        $return [$i] [] = @array ('class' => $class . ' ' . $value ['class'], 'value' => $new_value );
                    }
                }
            }
            /**
             * Deal with the grid itself
             */
            $is = 0;
            $integralFields = array_keys ( $this->removeAsFromFields () );
            
            foreach ( $fields as $campos ) {
                
                $campos = stripos ( $campos, ' AS ' ) ? substr ( $campos, stripos ( $campos, ' AS ' ) + 3 ) : $campos;
                $campos = trim ( $campos );
                
                if (isset ( $this->data ['fields'] [$fields_duble [$is]] ['eval'] )) {
                    $finalDados = is_object ( $dados ) ? get_object_vars ( $dados ) : $dados;
                    
                    $evalf = str_replace ( $search, $this->reset_keys ( $this->map_array ( $finalDados, 'prepare_output' ) ), $this->data ['fields'] [$fields_duble [$is]] ['eval'] );
                    $new_value = eval ( 'return ' . $evalf . ';' );
                
                } else {
                    if ($this->_adapter == 'db') {
                        $final = $this->object2array ( $dados );
                    } else {
                        $final = $dados;
                    }
                    
                    if (isset ( $final [$campos] ) && ! is_array ( $final [$campos] )) {
                        $new_value = $final [$campos];
                    }
                }
                

                if ($this->escapeOutput === true) {
                    $new_value = htmlspecialchars ( $new_value );
                }
                
                //[PT]Aplicar o formato da célula
                if (isset ( $this->data ['fields'] [$fields_duble [$is]] ['format'] )) {
                    
                    $new_value = $this->applyFormat ( $new_value, $this->data ['fields'] [$fields_duble [$is]] ['format'], $this->data ['fields'] [$fields_duble [$is]] ['format'] [1] );
                }
                

                if (isset ( $this->data ['fields'] [$fields_duble [$is]] ['decorator'] )) {
                    
                    $finalDados = is_object ( $dados ) ? get_object_vars ( $dados ) : $dados;
                    $new_value = str_replace ( $search, $this->reset_keys ( $this->map_array ( $finalDados, 'prepare_output' ) ), $this->data ['fields'] [$fields_duble [$is]] ['decorator'] );
                }
                

                if (! isset ( $this->data ['fields'] [$fields_duble [$is]] ['hide'] )) {
                    $fieldClass = isset ( $this->data ['fields'] [$fields_duble [$is]] ['class'] ) ? $this->data ['fields'] [$fields_duble [$is]] ['class'] : '';
                    $class = isset ( $class ) ? $class : '';
                    $return [$i] [] = @array ('class' => $class . " " . $fieldClass, 'value' => stripslashes ( $new_value ), 'field' => $integralFields [$is] );
                }
                

                $is ++;
            

            }
            /**
             * Deal with extra fields from the right
             */
            if (is_array ( $extra_fields )) {
                foreach ( $extra_fields as $value ) {
                    if ($value ['position'] == 'right') {
                        $fi = get_object_vars ( $dados );
                        $new_value = str_replace ( $search, $fi, $value ['decorator'] );
                        if (isset ( $value ['eval'] )) {
                            $evalf = str_replace ( $search, $fi, $value ['eval'] );
                            $new_value = eval ( 'return ' . trim ( $evalf, ';' ) );
                        }
                        
                        if (isset ( $value ['format'] )) {
                            $new_value = $this->applyFormat ( $new_value, $value ['format'], $value ['format'] );
                        }
                        
                        $finalClass = isset ( $value ['class'] ) ? $value ['class'] : '';
                        $class = isset ( $class ) ? $class : '';
                        $return [$i] [] = array ('class' => $class . ' ' . $finalClass, 'value' => $new_value );
                    }
                }
            }
            $i ++;
        }
        return $return;
    }


    
    /**
     *Reset keys
     * @param unknown_type $array
     * @return unknown
     */
    function reset_keys($array) {

        
        if (! is_array ( $array ))
            return FALSE;
        

        $novo_array = array ();
        $i = 0;
        foreach ( $array as $value ) {
            $novo_array [$i] = $value;
            $i ++;
        }
        return $novo_array;
    }


    
    function applySqlExpToArray($field, $operation, $option = 0) {

        foreach ( $this->_resultRaw as $value ) {
            
            $array [] = $value [$field];
        
        }
        
        $operation = trim(strtolower($operation)); 
        
        switch ($operation) {
            case 'product' :
                return array_product ( $array );
                break;
            case 'sum' :
                return array_sum ( $array );
                break;
            case 'count' :
                return count ( $array );
                break;
            case 'min' :
                sort ( $array );
                return array_shift ( $array );
                break;
            case 'max' :
                sort ( $array );
                return array_pop ( $array );
                break;
            case 'avg' :
                $option = ( int ) $option;
                return round ( array_sum ( $array ) / count ( $array ), $option );
                break;
            default :
                throw new Exception ( 'Operation not found' );
                break;
        }
    }


    /**
     * Apply SQL Functions
     *
     */
    function buildSqlExp() {

        $exp = isset ( $this->info ['sqlexp'] ) ? $this->info ['sqlexp'] : '';
        
        if (! is_array ( $exp )) {
            return false;
        }
        
        $final = $exp;
        

        if ($this->_adapter == 'array') {
            
            foreach ( $final as $key => $value ) {
            
                    $result[$key] = $this->applySqlExpToArray($key,$value);

            }
            
            
        } else {
            


            foreach ( $final as $key => $value ) {
                

                if (is_array ( $value )) {
                    $valor = '';
                    foreach ( $value as $final ) {
                        $valor .= $final . '(';
                    }
                    
                    $valor .= $key . str_repeat ( ')', count ( $value ) );
                } else {
                    $valor = "$value($key)";
                }
                
                $select = clone $this->_select;
                
                $select->reset ( Zend_Db_Select::COLUMNS );
                $select->reset ( Zend_Db_Select::ORDER );
                $select->reset ( Zend_Db_Select::LIMIT_COUNT );
                $select->reset ( Zend_Db_Select::LIMIT_OFFSET );
                
                $select->columns ( new Zend_Db_Expr ( $valor . ' AS TOTAL' ) );
                
                $final = $select->query ();
                
                $result1 = $final->fetchAll ();
                
                $result [$key] = $result1 [0]->TOTAL;
            
            }
        
        }
        
        if (is_array ( $result )) {
            $return = array ();
            foreach ( $this->_finalFields as $key => $value ) {
                if (array_key_exists ( $key, $result )) {
                    $class = isset ( $this->template ['classes'] ['sqlexp'] ) ? $this->template ['classes'] ['sqlexp'] : '';
                    $return [] = array ('class' => $class, 'value' => round ( $result [$key], 1 ), 'field' => $key );
                } else {
                    $class = isset ( $this->template ['classes'] ['sqlexp'] ) ? $this->template ['classes'] ['sqlexp'] : '';
                    $return [] = array ('class' => $class, 'value' => '', 'field' => $key );
                }
            }
        }
        return $return;
    }


    
    /**
     *  Make sure the fields exists on the database, if not remove them from the array
     *
     * @param array $fields
     */
    function validateFields($fields) {

        
        if (is_array ( $fields )) {
            $hide = 0;
            $fields_final = array ();
            $i = 0;
            
            foreach ( $fields as $key => $value ) {
                

                //A parte da order
                if (isset ( $value ['orderField'] )) {
                    $orderFields [$key] = $value ['orderField'];
                } else {
                    $orderFields [$key] = $key;
                }
                
                if (isset ( $value ['title'] )) {
                    
                    $titulos [$key] = $value ['title'];
                } else {
                    $titulos [$key] = ucfirst ( $key );
                }
                

                if (isset ( $value ['order'] )) {
                    if (@$value ['order'] > - 1) {
                        $fields_final [( int ) $value ['order']] = $key;
                    }
                } else {
                    $fields_final [$i] = $key;
                }
                

                if (isset ( $value ['hide'] )) {
                    if ($value ['hide'] == 1) {
                        $hide ++;
                    }
                }
                $i ++;
            }
            

            ksort ( $fields_final );
            $fields_final = $this->reset_keys ( $fields_final );
        


        }
        


        //remove unwanted fields for dislpay
        $naoMostrar = array_flip ( $fields_final );
        

        foreach ( $naoMostrar as $key => $field ) {
            if (isset ( $this->data ['hide'] ) && in_array ( $key, $this->data ['hide'] )) {
                unset ( $naoMostrar [$key] );
                unset ( $orderFields [$key] );
                unset ( $titulos [$key] );
                unset ( $this->data ['fields'] [$key] );
            
            }
        }
        
        $fields_final = array_values ( array_flip ( $naoMostrar ) );
        


        $this->totalHiddenFields = $hide;
        $this->_fields = $fields_final;
        $this->_titles = $titulos;
        $this->_fieldsOrder = $orderFields;
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

        
        if (isset ( $this->info ['noFilters'] ) && $this->info ['noFilters']) {
            return false;
        }
        
        $titulos = null;
        
        if (is_array ( $filters )) {
            return $filters;
        } elseif ($this->_adapter == 'db') {
            //Não forneceu dados, temos que ir buscá-los todos às tabelas
            if (is_array ( $this->data ['table'] )) {
                foreach ( $this->data ['table'] as $key => $value ) {
                    $tab = $this->getDescribeTable ( $value );
                    foreach ( $tab as $list ) {
                        $titulos [$key . "." . $list ['COLUMN_NAME']] = ucfirst ( $list ['COLUMN_NAME'] );
                    }
                }
            } else {
                $tab = $this->getDescribeTable ( $this->data ['table'] );
                foreach ( $tab as $list ) {
                    $titulos [$list ['COLUMN_NAME']] = ucfirst ( $list ['COLUMN_NAME'] );
                }
            }
        }
        
        if (@is_array ( $this->data ['hide'] )) {
            
            foreach ( $this->data ['hide'] as $value ) {
                if (array_key_exists ( $value, $titulos )) {
                    unset ( $titulos [$value] );
                }
            }
        } else {
            if (@is_array ( $titulos )) {
                foreach ( $titulos as $key => $value ) {
                    if (! in_array ( $key, $this->map_array ( $this->_fields, 'replace_AS' ) )) {
                        unset ( $titulos [$key] );
                    }
                }
            }
        }
        

        return $titulos;
    }


    
    /**
     *  Get the primary table key
     *  This is important because we only allow edit, add or remove records
     *  From tables that have on primary key
     *
     * @return string
     */
    function getPrimaryKey($newTable = null) {

        
        $table = $this->data ['table'];
        
        if ($this->_crudJoin) {
            
            $table = $this->data ['table'] [reset ( explode ( '.', $this->info ['crud'] ['primaryKey'] ) )];
        
        }
        
        if (null !== $newTable) {
            $table = $newTable;
        }
        

        if (is_array ( $table )) {
            return false;
        }
        
        if (isset ( $this->_getPrimaryKey [$table] )) {
            return $this->_getPrimaryKey [$table];
        }
        

        if (isset ( $this->data ['primaryKey'] )) {
            $this->_getPrimaryKey [$table] = $this->data ['primaryKey'];
            return $this->_getPrimaryKey [$table];
        }
        

        $param = $this->getDescribeTable ( $table );
        foreach ( $param as $value ) {
            if ($value ['PRIMARY'] === true) {
                $primary_key [] = $value ['COLUMN_NAME'];
                $field = $value ['COLUMN_NAME'];
            }
        }
        
        if ($this->_crudJoin) {
            $field = reset ( explode ( '.', $this->info ['crud'] ['primaryKey'] ) ) . '.' . $field;
        }
        
        $this->_getPrimaryKey [$table] = $primary_key;
        
        return $this->_getPrimaryKey [$table];
    }


    
    /**
     * Confirm that all fields are in the query.
     * Check for auto filters
     *
     * @return true
     */
    function consolidateQuery() {

        
        $this->consolidated = 1;
        $cFields = @$this->data ['fields'];
        
        if (! is_array ( $cFields )) {
            
            if (is_array ( $this->data ['table'] )) {
                
                foreach ( $this->data ['table'] as $key => $table ) {
                    
                    $tableFinal = array_keys ( $this->getDescribeTable ( $table ) );
                    
                    foreach ( $tableFinal as $field ) {
                        $this->addColumn ( $key . '.' . $field );
                    }
                }
            

            } else {
                
                $table = array_keys ( $this->getDescribeTable ( $this->data ['table'] ) );
            }
            
            foreach ( $table as $field ) {
                $this->addColumn ( $field );
            }
        }
        

        if (! @is_array ( $this->filters ) && @is_array ( $this->data ['filters'] )) {
            $this->filters = $this->data ['filters'];
        }
        


        //If a search is performed in a field that isn't displayed,
        //If so we add the field and hide it
        if (is_array ( $cFields )) {
            foreach ( $cFields as $value ) {
                if (@$value ['searchField'] != "") {
                    if (! in_array ( $value ['searchField'], $this->data ['fields'] )) {
                        $this->addColumn ( $value ['searchField'], array ('title' => 'Barcelos', 'hide' => 1 ) );
                    }
                }
            }
        }
        

        //Make sure the fields we need to execute the distinct query for filter exist
        //If not, we add them and define the hide param as 1
        if (is_array ( $this->filters )) {
            
            foreach ( $this->filters as $value ) {
                

                if (is_array ( $value ) && isset ( $value ['distinct'] ['field'] ) && isset ( $value ['distinct'] ['name'] )) {
                    
                    if (! array_key_exists ( $value ['distinct'] ['field'], $this->data ['fields'] )) {
                        $this->addColumn ( $value ['distinct'] ['field'] . ' AS f' . md5 ( $value ['distinct'] ['field'] ), array ('title' => 'Barcelos', 'hide' => 1 ) );
                    }
                    
                    if (! array_key_exists ( $value ['distinct'] ['name'], $this->data ['fields'] ) && $value ['distinct'] ['name'] != $value ['distinct'] ['field']) {
                        $this->addColumn ( $value ['distinct'] ['name'] . ' AS f' . md5 ( $value ['distinct'] ['name'] ), array ('title' => 'Barcelos', 'hide' => 1 ) );
                    }
                    $this->data ['fields'] [$value ['distinct'] ['name']] ['searchField'] = $value ['distinct'] ['field'];
                }
            
            }
        

        }
        

        // The extra fields, they are not part of database table.
        // Usefull for adding links (a least for me :D )
        if (@is_array ( $this->info ['extra_fields'] )) {
            if (! is_array ( $this->extra_fields )) {
                $this->extra_fields = $this->info ['extra_fields'];
            } else {
                $this->extra_fields = array_merge ( $this->extra_fields, $this->info ['extra_fields'] );
            }
        }
        

        // Validate table fields, make sure they exist...
        $this->validateFields ( $this->data ['fields'] );
        

        // Filters. Not required that every field as filter.
        // Make sute they exists on the table
        $this->filters = self::validateFilters ( $this->filters );
        

        //[PT]O colspan a ser aplicado em tabelas
        $this->_colspan = $this->colspan ();
        return true;
    }


    
    /**
     * Build Query
     *
     * @return unknown
     */
    function getQuery() {

        
        if ($this->consolidated == 0) {
            $this->consolidateQuery ();
        }
        
        $this->buildQuery ();
        
        $this->buildSelectQuery ();
        
        return true;
    }


    /**
     * Build the select fields, from and if necessary the joins part
     *
     * @return void
     */
    function buildSelectQuery() {

        if ($this->_selectZendDb === true)
            return;
        
        $select_fields = $this->buildSelectFields ( $this->_fields );
        
        $from = trim ( $this->data ['from'] );
        
        /**
         * This menas that the user set an alias for the table withou the 'as'
         * ->from('Country c')
         * instead of
         * ->from('County as c')
         */
        if (substr_count ( $from, ' ' ) < 2) {
            if (strpos ( $from, ' ' ) !== false) {
                $table = array_map ( 'trim', explode ( ' ', $from ) );
                
                $this->_select->from ( array ($table [1] => $table [0] ) );
            } else {
                
                $this->_select->from ( $from );
            }
            
            $this->buildColumns ();
            
            /**
             * No joins
             */
            return;
        }
        
        $a = '';
        preg_match ( "/(.*?)(inner\sjoin?|left\sjoin?|rigth\sjoin?|full\sjoin?|join|cross\sjoin?|natural\sjoin?).* /mi", $from, $a );
        
        $fromTable = trim ( $a [1] );
        
        /**
         * reset the join part.
         * Shouldn't be set already, just in case...
         */
        $this->_select->reset ( Zend_Db_Select::FROM );
        


        /**
         * We culd simplify this using the preg_split. 
         * But it is much faster to use the strpos.
         * Wating on feedback...
         */
        if (strpos ( $fromTable, ' as ' ) !== false) {
            
            $final = array_map ( 'trim', explode ( 'as', $fromTable ) );
            
            $this->_select->from ( array ($final [1] => $final [0] ), array_map ( 'trim', explode ( ',', $select_fields ) ) );
        
        } elseif (strpos ( $fromTable, ' AS ' ) !== false) {
            
            $final = array_map ( 'trim', explode ( 'AS', $fromTable ) );
            
            $this->_select->from ( array ($final [1] => $final [0] ), array_map ( 'trim', explode ( ',', $select_fields ) ) );
        
        } elseif (strpos ( $fromTable, ' ' ) !== false) {
            
            $final = array_map ( 'trim', explode ( ' ', $fromTable ) );
            
            $this->_select->from ( array ($final [1] => $final [0] ), array_map ( 'trim', explode ( ',', $select_fields ) ) );
        
        } else {
            
            $this->_select->from ( $fromTable, array_map ( 'trim', explode ( ',', $select_fields ) ) );
        
        }
        

        $from = str_replace ( $fromTable, '', $from );
        $t = '';
        
        preg_match_all ( "/(inner\sjoin?|left\sjoin?|rigth\sjoin?|join|cross\sjoin?|natural\sjoin?)(.*?)(on)\s+(.*?=.*?\s?\w.+)/i", $from, $t );
        
        for($i = 0; $i < count ( $t [1] ); $i ++) {
            
            $table = $this->getArrayForDbSelect ( $t [2] [$i] );
            
            switch (trim ( strtoupper ( $t [1] [$i] ) )) {
                case 'INNER JOIN' :
                case 'JOIN' :
                    $this->_select->join ( $table, $t [4] [$i], array () );
                    break;
                case 'LEFT JOIN' :
                    $this->_select->joinLeft ( $table, $t [4] [$i], array () );
                    break;
                case 'RIGHT JOIN' :
                    $this->_select->joinRight ( $table, $t [4] [$i], array () );
                    break;
                case 'FULL JOIN' :
                    $this->_select->joinFull ( $table, $t [4] [$i], array () );
                    break;
                case 'CROSS JOIN' :
                    $this->_select->joinCross ( $table, array () );
                    break;
                case 'NATURAL JOIN' :
                    $this->_select->joinNatural ( $table, array () );
                    break;
                
                default :
                    break;
            
            }
        }
        


        return;
    }


    /**
     * Build fields if necessary
     *
     * @return void
     */
    function buildColumns() {

        
        if ($this->_selectZendDb === true) {
            return;
        }
        
        //Lets add the columns
        if (count ( $this->data ['fields'] ) != count ( $this->_select->getPart ( Zend_Db_Select::COLUMNS ) )) {
            

            //Reset all columns already set
            $this->_select->reset ( Zend_Db_Select::COLUMNS );
            
            $this->_fields = false;
            $this->_titles = false;
            


            foreach ( array_keys ( $this->data ['fields'] ) as $field ) {
                $finalField = $this->getArrayForDbSelect ( $field );
                

                if (is_array ( $finalField )) {
                    
                    $this->_fields [] = key ( $finalField );
                    $this->_titles [key ( $finalField )] = $this->data ['fields'] [$field] ['title'];
                    $this->_select->columns ( $finalField );
                } else {
                    
                    $this->_select->columns ( $finalField );
                    
                    $this->_fields [] = $finalField;
                    $this->_titles [$finalField] = isset ( $this->data ['fields'] [$field] ['title'] ) ? $this->data ['fields'] [$field] ['title'] : ucfirst ( $finalField );
                }
            
            }
        

        }
        
        return;
    
    }


    /**
     * If a field or table as an alias,
     * then convert it to and array key=>value
     * to be set on Zend_Db_Select
     *
     * @param string $string
     * @return array
     */
    function getArrayForDbSelect($string) {

        if (strpos ( $string, ' AS ' )) {
            
            $final1 = array_map ( 'trim', explode ( ' AS ', $string ) );
            
            $final [$final1 [1]] = $final1 [0];
        
        } elseif (strpos ( $string, ' as ' )) {
            
            $final1 = array_map ( 'trim', explode ( ' as ', $string ) );
            
            $final [$final1 [1]] = $final1 [0];
        
        } elseif (strpos ( $string, ' ' )) {
            
            $final1 = array_map ( 'trim', explode ( ' ', $string ) );
            
            $final [$final1 [1]] = $final1 [0];
        
        } else {
            
            $final = trim ( $string );
        
        }
        
        return $final;
    }


    /**
     * Count the rows total without the limit
     *
     * @return void
     */
    function getQueryCount() {

        if ($this->consolidated == 0) {
            $this->consolidateQuery ();
        }
        
        // Get the WHERE condition and apply from now on...
        $this->buildQueryWhere ();
        
        $this->_selectCount = clone $this->_select;
        
        $this->_selectCount->reset ( Zend_Db_Select::COLUMNS );
        $this->_selectCount->reset ( Zend_Db_Select::LIMIT_OFFSET );
        $this->_selectCount->reset ( Zend_Db_Select::LIMIT_COUNT );
        
        $this->_selectCount->columns ( new Zend_Db_Expr ( 'COUNT(*) AS TOTAL ' ) );
        
        return;
    }


    /**
     *  Done. Send the grid to the user
     *
     * @return string
     */
    function deploy() {

        if (FALSE === $this->_isPrimaryGrid) {
            $myParams = array ('comm', 'order', 'filters', 'add', 'edit' );
            
            foreach ( $myParams as $key ) {
                unset ( $this->ctrlParams [$key] );
            }
        
        }
        

        if ($this->consolidated == 0) {
            $this->consolidateQuery ();
        }
        

        if ($this->_adapter == 'db') {
            

            $this->getQuery ();
            $this->getQueryCount ();
            
            $this->buildColumns ();
            

            if ($this->cache ['use'] == 1) {
                
                $cache = $this->cache ['instance'];
                
                if (! $result = $cache->load ( md5 ( $this->_select->__toString () ) )) {
                    

                    $stmt = $this->_db->query ( $this->_select );
                    $result = $stmt->fetchAll ();
                    
                    $stmt = $this->_db->query ( $this->_selectCount );
                    $resultCount = $stmt->fetchAll ();
                    $resultCount = $resultCount [0]->TOTAL;
                    
                    $cache->save ( $result, md5 ( $this->_select->__toString () ), array ($this->cache ['tag'] ) );
                    $cache->save ( $resultCount, md5 ( $this->_selectCount->__toString () ), array ($this->cache ['tag'] ) );
                
                } else {
                    $result = $cache->load ( md5 ( $this->_select->__toString () ) );
                    $resultCount = $cache->load ( md5 ( $this->_selectCount->__toString () ) );
                }
            

            } else {
                
                $stmt = $this->_db->query ( $this->_select );
                $result = $stmt->fetchAll ();
                
                $selectZendDb = clone $this->_select;
                $selectZendDb->reset ( Zend_Db_Select::LIMIT_COUNT );
                $selectZendDb->reset ( Zend_Db_Select::LIMIT_OFFSET );
                $selectZendDb->reset ( Zend_Db_Select::COLUMNS );
                $selectZendDb->columns ( array ('TOTAL' => new Zend_Db_Expr ( "COUNT(*)" ) ) );
                
                $stmt = $selectZendDb->query ();
                
                $resultZendDb = $stmt->fetchAll ();
                
                $resultCount = $resultZendDb [0]->TOTAL;
            
            }
            


            //Total records found
            $this->_totalRecords = $resultCount;
            
            //The result
            $this->_result = $result;
        

        } else {
            

            $filters = Zend_Json::decode ( @$this->ctrlParams ['filters'] );
            if (is_array ( $filters )) {
                
                foreach ( $filters as $key => $filter ) {
                    $key = end ( explode ( '_', $key ) );
                    $filterValue [$key] = $filter;
                }
                
                $filters = $filterValue;
                

                $find = $this->findInArray ( $filters );
                
                $this->_filtersValues = $filterValue;
                
                if (count ( $find ) > 0) {
                    $this->_result = $find;
                
                } elseif ($this->_searchPerformedInArray === true) {
                    $this->_result = array ();
                }
            

            }
            

            if (isset ( $this->ctrlParams ['order'] ) || strlen ( @$this->data ['order'] ) > 3) {
                
                if (strlen ( $this->data ['order'] ) > 3 && ! isset ( $this->ctrlParams ['order'] )) {
                    
                    $order = reset ( explode ( ' ', $this->data ['order'] ) );
                    $orderType = end ( explode ( ' ', $this->data ['order'] ) );
                    if (strtoupper ( $orderType ) != 'ASC' && strtoupper ( $orderType ) != 'DESC') {
                        $orderType = 'ASC';
                    }
                    

                    $orderType = strtoupper ( $orderType ) == 'ASC' ? SORT_ASC : SORT_DESC;
                
                } else {
                    $order = reset ( explode ( '_', $this->ctrlParams ['order'] ) );
                    $orderType = end ( explode ( '_', $this->ctrlParams ['order'] ) );
                    
                    $orderType = strtoupper ( $orderType ) == 'ASC' ? SORT_ASC : SORT_DESC;
                }
                

                // Obtain a list of columns
                foreach ( $this->_result as $key => $row ) {
                    $result [$key] = $row [$order];
                }
                

                @array_multisort ( $result, $orderType, $this->_result );
            
            }
            


            if (@strlen ( $this->info ['limit'] ) > 0 || @is_array ( $this->info ['limit'] )) {
                if (is_array ( $this->info ['limit'] )) {
                    $this->_totalRecords = $this->info ['limit'] [1];
                    $result = array_slice ( $this->_result, $this->info ['limit'] [0], $this->info ['limit'] [1] );
                } else {
                    $this->_totalRecords = $this->info ['limit'];
                    $result = array_slice ( $this->_result, 0, $this->info ['limit'] );
                }
            
            } elseif ($this->pagination == 0) {
                $this->_totalRecords = count ( $this->_result );
                $result = $this->_result;
            
            } else {
                $this->_totalRecords = count ( $this->_result );
                $result = array_slice ( $this->_result, ( int ) @$this->ctrlParams ['start'] < $this->_totalRecords ? ( int ) @$this->ctrlParams ['start'] : 0, $this->pagination );
            
            }
            
            $this->_result = $result;
        }
        

        return;
    }


    /**
     * Return the query to be executed
     *
     * @return Zend_Db_Select
     */
    function __toString() {

        return $this->_select->__toString ();
    }


    /**
     * Get details about a column
     *
     * @param string $column
     * @return null|array
     */
    function getColumn($column) {

        return isset ( $this->data ['fields'] [$column] ) ? $this->data ['fields'] [$column] : null;
    
    }


    
    /**
     * Search function for array adapters
     */
    function findInArray($filters) {

        
        $filtersNumber = 0;
        foreach ( $filters as $value ) {
            if (strlen ( $value ) > 0) {
                $filtersNumber ++;
            }
        }
        
        $this->_searchPerformedInArray = true;
        
        $find = array ();
        
        foreach ( $this->_result as $result ) {
            
            $i = 0;
            
            foreach ( $filters as $filterKey => $filterValue ) {
                foreach ( $result as $fieldKey => $fieldValue ) {
                    if (strlen ( $filterValue ) > 0 && $fieldKey == $filterKey) {
                        
                        if ($this->applySearchTypeToArray ( $fieldValue, $filterValue, $filterKey )) {
                            $i ++;
                        }
                    }
                }
            }
            

            if ($i == $filtersNumber) {
                $find [] = $result;
            }
        
        }
        

        return $find;
    
    }


    
    /**
     * Apply the search to a give field when the adaptar is an array
     */
    function applySearchTypeToArray($final, $search, $key) {

        $enc = stripos ( ( string ) $final, $search );
        
        if (@$this->data ['fields'] [$key] ['searchType'] != "") {
            $filtro = $this->data ['fields'] [$key] ['searchType'];
        }
        $filtro = @strtolower ( $filtro );
        

        switch ($filtro) {
            case 'equal' :
            case '=' :
                if ($search == $final)
                    return true;
                break;
            case 'rlike' :
                if (substr ( $final, 0, strlen ( $search ) ) == $search)
                    return true;
                break;
            case 'llike' :
                if (substr ( $final, - strlen ( $search ) ) == $search)
                    return true;
                break;
            case '>=' :
                if ($final >= $search)
                    return true;
                break;
            case '>' :
                if ($final > $search)
                    return true;
                break;
            case '<>' :
            case '!=' :
                if ($final != $search)
                    return true;
                break;
            case '<=' :
                if ($final <= $search)
                    return true;
                break;
            case '<' :
                if ($final < $search)
                    return true;
                break;
            default :
                $enc = stripos ( ( string ) $final, $search );
                if ($enc !== false) {
                    return true;
                }
                break;
        }
        
        return false;
    
    }


    
    /**
     * Remove a column
     */
    function removeColumn($column) {

        unset ( $this->data ['fields'] [$column] );
        unset ( $this->filters [$column] );
        return $this;
    }


    
    /**
     * Remove an array of columns
     */
    function removeColumns(array $columns) {

        
        foreach ( $columns as $column ) {
            $this->removeColumn ( $column );
        }
        
        return $this;
    }


    
    /**
     * Reset a column to clear all customizations 
     */
    function resetColumn($column) {

        $this->removeColumn ( $column );
        $this->addColumn ( $column );
    }


    
    /**
     *Convert Object to Array
     * @param object $object
     * @return array
     */
    function object2array($object) {

        
        $return = NULL;
        if (is_array ( $object )) {
            foreach ( $object as $key => $value )
                $return [$key] = self::object2array ( $value );
        } else {
            $var = get_object_vars ( $object );
            if ($var) {
                foreach ( $var as $key => $value )
                    $return [$key] = self::object2array ( $value );
            } else {
                return strval ( $object );
            }
        }
        return $return;
    }


    
    /**
     * set template locations
     *
     * @param string $path
     * @param string $prefix
     * @return unknown
     */
    function addTemplateDir($dir, $prefix, $type) {

        
        if (! isset ( $this->_templates [$type] )) {
            $this->_templates [$type] = new Zend_Loader_PluginLoader ( );
        }
        
        $this->_templates [$type]->addPrefixPath ( trim ( $prefix, "_" ), trim ( $dir, "/" ) . '/', $type );
        return $this;
    }


    
    /**
     * Define the template to be used
     *
     * @param string $template
     * @return unknown
     */
    function setTemplate($template, $output = 'table', $options = array()) {

        $class = $this->_templates [$output]->load ( $template, $output );
        
        $this->temp [$output] = new $class ( $options );
        $this->activeTemplates [] = $output;
        
        $this->temp [$output]->templateInfo = array ('name' => $template, 'dir' => $this->_templates [$output]->getClassPath ( $template, $output ), 'class' => $this->_templates [$output]->getClassName ( $template, $output ), 'options' => $options );
        
        return $this->temp [$output];
    
    }


    
    /**
     * Add multiple columns at once
     *
     */
    function addColumns() {

        $fields = func_get_args ();
        
        foreach ( $fields as $value ) {
            
            if ($value instanceof Bvb_Grid_Column) {
                
                $value = $this->object2array ( $value );
                foreach ( $value as $field ) {
                    
                    $finalField = $field ['field'];
                    unset ( $field ['field'] );
                    $this->addColumn ( $finalField, $field );
                
                }
            }
        }
    }


    
    /**
     * Add filters
     *
     */
    function addFilters($filters) {

        
        $filters = $this->object2array ( $filters );
        $this->filters = $filters ['_filters'];
    }


    
    /**
     * Add extra columns
     *
     * @return unknown
     */
    function addExtraColumns() {

        
        $extra_fields = func_get_args ();
        $final = array ();
        foreach ( $extra_fields as $value ) {
            if ($value instanceof Bvb_Grid_ExtraColumns) {
                $value = $this->object2array ( $value );
                array_push ( $final, $value ['_field'] );
            }
        }
        $this->extra_fields = $final;
        return $this;
    }


    
    /**
     * @deprecated 
     * Create the grid using a Zend_Db_Select Object
     */
    function queryFromZendDbSelect(Zend_Db_Select $select) {

        return $this->query ( $select );
    }


    /**
     * Define the query using Zend_Db_Select instance
     *
     * @param Zend_Db_Select $select
     * @return bool
     */
    function query(Zend_Db_Select $select) {

        $this->_selectZendDb = true;
        
        $this->_select = $select;
        
        $fields = $this->_select->getPart ( Zend_Db_Select::COLUMNS );
        
        foreach ( $fields as $value ) {
            
            $title = ucwords ( str_replace ( "_", ' ', end ( explode ( '.', $value [1] ) ) ) );
            
            if (strlen ( $value [2] ) > 0) {
                $title = $value [2];
                
                $this->addColumn ( $value [1] . ' as ' . $value [2], array ('title' => $title ) );
            } else {
                
                $this->addColumn ( $value [1], array ('title' => $title ) );
            }
        }
        
        $from = $this->_select->getPart ( Zend_Db_Select::FROM );
        

        if (count ( $from ) == 1) {
            
            if (key ( $from ) != $from [key ( $from )] ['tableName']) {
                $this->data ['from'] = $from [key ( $from )] ['tableName'] . ' as ' . key ( $from );
            } else {
                $this->data ['from'] = $from [key ( $from )] ['tableName'];
            }
            
            $this->data ['table'] = $this->data ['from'];
        }
        

        return true;
    }

}

  