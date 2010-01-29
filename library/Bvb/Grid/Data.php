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
 * @copyright  Copyright (c) Pétala Azul (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id$
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

class Bvb_Grid_Data
{

    const VERSION = "0.6 alpha";

    /**
     * Char encoding
     *
     * @var string
     */

    public $charEncoding = 'UTF-8';

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
     * @return Zend_Db_Select
     */
    protected $_select = false;

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
    public $arrayData = array();

    /**
     * Fields order
     *
     * @var unknown_type
     */
    private $_fieldsOrder;

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
    protected $template = array();

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
    public $export = array('pdf', 'word', 'wordx', 'excel', 'print', 'xml', 'csv', 'ods', 'odt', 'json');

    /**
     * All info that is not directly related to the database
     */
    public $info = array();

    /**
     * Save the result of the describeTables
     */
    protected $_describeTables = array();

    /**
     * Registry for PK
     */
    protected $_primaryKey = array();

    /**
     * Where part from query
     */
    protected $_queryWhere = false;

    /**
     * DB Adapter
     *
     * @var Zend_Db_Select
     * @return Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * Baseurl
     *
     * @var string
     */
    protected $_baseUrl;

    /**
     * Array containing the query result from table(s)
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
     * Array containing field titles
     *
     * @var array
     */
    protected $_titles;

    /**
     * Array containing table(s) fields
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * Where initially defined by user
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
     * Filters values inserted by the user
     *
     * @var array
     */
    protected $_filtersValues;

    /**
     * All information databse related
     *
     * @var array
     */
    protected $data = array();

    /**
     * Params list
     *
     * @var array
     */
    public $params = array();

    /**
     * URL params
     *
     * @var string
     */
    public $ctrlParams;

    /**
     * Extra fields array
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
    protected $_elements = array();

    /**
     * Elements types allowed in forms
     *
     * @var array
     */
    private $_elementsAllowed = array('filter', 'validator');

    /**
     * The field to set order by, if we have a horizontal row
     *
     * @var string
     */
    private $fieldHorizontalRow;

    /**
     * Template instance
     *
     * @var unknown_type
     */
    protected $temp;

    /**
     * Instanciated templates classes
     *
     * @var unknown_type
     */
    public $activeTemplates = array();

    /**
     * Result untouched
     *
     * @var array
     */
    private $_resultRaw;

    /**
     * Check if all columns have been added by ->query()
     * @var bool
     */
    private $_allFieldsAdded = false;

    /**
     * If the user manually sets the query limit
     * @var int|bool
     */
    protected $_forceLimit = false;

    /**
     * Default filters to be applyed
     * @var array
     * @return array
     */
    protected $_defaultFilters;

    /**
     * Instead throwing an exception,
     * we queue the field list and call this in
     * getFieldsFromQuery()
     * @var array
     */
    protected $_updateColumnQueue = array();

    /**
     * List of callback functions to apply
     * on grid deploy and ajax
     * @var $_configCallbacks
     */
    protected $_configCallbacks = array();

    /**
     * Treat hidden fields as 'remove'
     * @var bool
     */
    protected $_removeHiddenFields = false;

    /**
     * Functions to be aplied on every field sbefore dislpay
     * @var unknown_type
     */
    protected $_escapeFunction =  'htmlspecialchars';

    /**
     * array of used tables
     * @var array
     */
    protected $_tablesList = null;


    /**
     * Grid Options.
     * They can be
     * @var array
     */
    protected $_options = array();


    /**
     * Id used for multiples insatnces onde the same page
     *
     * @var string
     */
    protected $_id;

    /**
     * Colspan for table
     * @var int
     */
    protected $_colspan;

    /**
     * The __construct function receives the db adapter. All information related to the
     * URL is also processed here
     *
     * @param array $data
     */
    function __construct ($options)
    {

        if (! $this instanceof Bvb_Grid_Deploy_Interface) {
            throw new Bvb_Grid_Exception(get_class($this) . ' needs to implment the Bvb_Grid_Deploy_Interface');
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (! is_array($options)) {
            throw new Bvb_Grid_Exception('options must be an instance from Zend_Config or an array');
        }

        $this->_options = $options;

        //Get the controller params and baseurl to use with filters
        $this->ctrlParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        $this->_baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

        /**
         * plugins loaders
         */
        $this->_formatter = new Zend_Loader_PluginLoader();
        $this->_elements['filter'] = new Zend_Loader_PluginLoader();
        $this->_elements['validator'] = new Zend_Loader_PluginLoader();

        //Templates loading
        if (is_array($this->export)) {
            foreach ($this->export as $temp) {
                $this->_templates[$temp] = new Zend_Loader_PluginLoader(array());
            }
        }

        // Add the formatter fir for fields content
        $this->addFormatterDir('Bvb/Grid/Formatter', 'Bvb_Grid_Formatter');


        //Apply options to the fields
        $this->_applyOptionsToFields();
    }


    /**
     * Sets the functions to be used to apply th each value
     * before fisplay
     * @param array $functions
     */
    public function setDefaultEscapeFunction ( $functions)
    {
        $this->_escapeFunction = $functions;
        return $this;
    }

    /**
     * Returns the activa escape functions
     */
    public function getDefaultEscapeFunction ()
    {
        return $this->_escapeFunction;
    }

    /**
     * Character encoding
     *
     * @param string $encoding
     * @return unknown
     */
    public function setcharEncoding ($encoding)
    {
        $this->charEncoding = $encoding;
        return $this;
    }

    /**
     * Returns de char encoding
     *
     * @return string
     */
    public function getCharEncoding ()
    {
        return $this->charEncoding;
    }

    /**
     * Define the adapter to use
     *
     * @param string $adapter
     */
    private function _setAdapter ($adapter)
    {
        $this->_adapter = strtolower($adapter) != 'db' ? 'array' : 'db';
        return $this;
    }

    /**
     * Get the current adapter
     * @return (array|db)
     */
    protected function _getAdapter ()
    {
        return $this->_adapter;
    }

    /**
     * Enter data using a csv file
     *
     * @param string $file
     * @param string $field
     * @param string $separator
     * @return unknown
     */
    public function setDataFromCsv ($file, $field = null, $separator = ',')
    {

        $this->_setAdapter('array');

        if ($this->cache['use'] == 1) {
            $cache = $this->cache['instance'];

            if (! $final = $cache->load(md5('array' . $file))) {

                $row = 0;
                $handle = fopen($file, "r");
                while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
                    $num = count($data);

                    if (null != $field) {
                        for ($c = 0; $c < $num; $c ++) {
                            $final[$row][$field[$c]] = $data[$c];
                        }
                    } else {
                        if ($row == 0) {
                            for ($c = 0; $c < $num; $c ++) {
                                $field[] = $data[$c];
                            }

                        } else {
                            for ($c = 0; $c < $num; $c ++) {
                                $final[$row - 1][$field[$c]] = $data[$c];
                            }
                        }
                    }

                    $row ++;
                }

                fclose($handle);

                $cache->save($final, md5('array' . $file), array($this->cache['tag']));
                $cache->save($field, md5('field' . $file), array($this->cache['tag']));

            } else {
                $final = $cache->load(md5('array' . $file));
                $field = $cache->load(md5('field' . $file));
            }

        } else {

            $row = 0;
            $handle = fopen($file, "r");
            while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
                $num = count($data);

                if (null != $field) {

                    for ($c = 0; $c < $num; $c ++) {
                        $final[$row][$field[$c]] = $data[$c];
                    }

                } else {
                    if ($row == 0) {
                        for ($c = 0; $c < $num; $c ++) {
                            $field[] = $data[$c];
                        }

                    } else {
                        for ($c = 0; $c < $num; $c ++) {
                            $final[$row - 1][$field[$c]] = $data[$c];
                        }
                    }
                }

                $row ++;
            }

            fclose($handle);
        }

        $this->addArrayColumns($field);
        $this->addArrayData($final);

        return $this;

    }

    /**
     * Set the data using a XML file
     *
     * @param string $url
     * @param bool $loop
     * @param bool $columns
     */
    public function setDataFromXml ($url, $loop = null, $columns = null)
    {

        $this->_setAdapter('array');

        if ($this->cache['use'] == 1) {
            $cache = $this->cache['instance'];

            if (! $xml = $cache->load(md5($url))) {

                if (strstr($url, '<?xml')) {
                    $xml = simplexml_load_string($url);
                } else {
                    $xml = simplexml_load_file($url);
                }
                $xml = $this->_object2array($xml);
                $cache->save($xml, md5($url), array($this->cache['tag']));
            } else {
                $xml = $cache->load(md5($url));
            }
        } else {

            if (strstr($url, '<?xml')) {
                $xml = simplexml_load_string($url);
            } else {
                $xml = simplexml_load_file($url);
            }
            $xml = $this->_object2array($xml);
        }

        $cols = explode(',', $loop);
        if (is_array($cols)) {
            foreach ($cols as $value) {
                $xml = $xml[$value];
            }
        }

        //Remove possible arrays
        for ($i = 0; $i < count($xml); $i ++) {
            foreach ($xml[$i] as $key => $final) {
                if (! is_string($final)) {
                    unset($xml[$i][$key]);
                }
            }
        }

        if (is_array($columns)) {
            foreach ($columns as $value) {
                $columns = $columns[$value];
            }
        } else {
            $columns = array_keys($xml[0]);
        }

        $this->addArrayColumns($columns);
        $this->addArrayData($xml);

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
    public function setDataFromJson ($array, $file = false, $loop = null, $columns = null)
    {

        $this->_setAdapter('array');

        if (true === $file) {

            if ($this->cache['use'] == 1) {
                $cache = $this->cache['instance'];

                if (! $result = $cache->load(md5($array))) {
                    $result = file_get_contents($array);

                    $cache->save($result, md5($array), array($this->cache['tag']));
                } else {
                    $result = $cache->load(md5($array));
                }
            } else {
                $result = file_get_contents($array);
            }

        } else {
            $result = $array;
        }

        $xml = Zend_Json::decode($result, true);

        $cols = explode(',', $loop);
        if (is_array($cols)) {
            foreach ($cols as $value) {
                $xml = $xml[$value];
            }
        }

        //Remove possible arrays
        for ($i = 0; $i < count($xml); $i ++) {
            foreach ($xml[$i] as $key => $final) {
                if (! is_string($final)) {
                    unset($xml[$i][$key]);
                }
            }
        }

        if (is_array($columns)) {
            foreach ($columns as $value) {
                if (is_string($value)) $columns = $columns[$value];
            }
        } else {
            $columns = array_keys($xml[0]);
        }

        $this->addArrayColumns($columns);
        $this->addArrayData($xml);

        return $this;

    }

    /**
     * Set the data using an array
     *
     * @param array $array
     */
    public function setDataFromArray ($array)
    {

        $this->_setAdapter('array');

        $this->addArrayColumns(array_keys($array[0]));
        $this->addArrayData($array);

        return $this;

    }

    /**
     * The translator
     *
     * @param string $message
     * @return string
     */
    protected function __ ($message)
    {

        if (Zend_Registry::isRegistered('Zend_Translate')) {
            $message = Zend_Registry::get('Zend_Translate')->translate($message);
        }
        return $message;
    }

    /**
     * Use the overload function so we can return an object
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function __call ($name, $value)
    {

        $deploy = explode('_', get_class($this));
        $class = strtolower(end($deploy));

        if (substr(strtolower($name), 0, strlen($class) + 3) == 'set' . $class) {
            $name = substr($name, strlen($class) + 3);
            $name[0] = strtolower($name[0]);
            $this->deploy[$name] = $value[0];
            return;
        }

        if (substr(strtolower($name), 0, 3) == 'set') {
            $name = substr($name, 3);
            $this->__set($name, $value[0]);
        } else {
            throw new Bvb_Grid_Exception("call to unknown function $name");
        }

        return $this;
    }


    /**
     * @param string $var
     * @param string $value
     */
    public function __set ($var, $value)
    {
        $var[0] = strtolower($var[0]);
        $this->info[$var] = $value;
        return $this;
    }

    /**
     * Update data from a column
     *
     * @param string $field
     * @param array $options
     * @return self
     */

    public function updateColumn ($field, $options = array())
    {

        if (! isset($this->data['table']) && $this->_selectZendDb == false && $this->_getAdapter() == 'db') {
            /**
             * Add to the queue and call it from the getFieldsFromQuery() method
             * @var $_updateColumnQueue Bvb_Grid_DataGrid
             */
            $this->_updateColumnQueue[$field] = $options;
            return true;

        }

        if ($this->_allFieldsAdded == false) {

            $this->data['fields'][$field] = $options;

        } elseif (array_key_exists($field, $this->data['fields'])) {

            if (isset($options['hRow']) && $options['hRow'] == 1) {
                $this->fieldHorizontalRow = $field;
                $this->info['hRow'] = array('field' => $field, 'title' => $options['title']);
            }

            $this->data['fields'][$field] = array_merge($this->data['fields'][$field], $options);

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
    public function addFormatterDir ($dir, $prefix)
    {

        $this->_formatter->addPrefixPath(trim($prefix, "_"), trim($dir, "/") . '/');
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
    public function addElementDir ($dir, $prefix, $type = 'filter')
    {

        if (! in_array(strtolower($type), $this->_elementsAllowed)) {
            throw new Bvb_Grid_Exception('Type not recognized');
        }

        $this->_elements[$type]->addPrefixPath(trim($prefix, "_"), trim($dir, "/") . '/');

        return $this;
    }

    /**
     * Format a field
     *
     * @param unknown_type $value
     * @param unknown_type $formatter
     * @return unknown
     */
    protected function _applyFormat ($value, $formatter)
    {

        if (is_array($formatter)) {
            $result = $formatter[0];
            $options = $formatter[1];
        } else {
            $result = $formatter;
            $options = null;
        }

        $class = $this->_formatter->load($result);

        $t = new $class($options);
        $return = $t->format($value);

        return $return;
    }

    /**
     * The allowed fields from a table
     *
     * @param string $mode
     * @param string $table
     * @return string
     */
    protected function _getFields ($mode, $table)
    {

        $get = $this->info[$mode]['fields'];
        if (! is_array($get)) {
            $get = $this->_getTableFields($table);
        }
        return $get;
    }

    /**
     * Get table fields
     *
     * @param string $table
     * @return string
     */
    protected function _getTableFields ($table)
    {

        $table = $this->_getDescribeTable($table);
        foreach (array_keys($table) as $key) {
            $val[$key] = $key;
        }
        return $val;
    }

    /**
     * pagination definition
     *
     */
    public function setPagination ($number = 15)
    {
        $this->pagination = (int) $number;
        return $this;
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
    protected function _buildSearchType ($filtro, $key, $field)
    {

        $columns = $this->_select->getPart('columns');

        foreach ($columns as $value) {
            if ($key == $value[2]) {
                if (is_object($value[1])) {
                    $field = $value[1]->__toString();
                } else {
                    $field = $value[0] . '.' . $value[1];
                }
                break;
            }

        }
        if (isset($this->data['fields'][$key]['search']) and is_array($this->data['fields'][$key]['search']) && $this->data['fields'][$key]['search']['fulltext'] == true) {

            $full = $this->data['fields'][$key]['search'];

            if (! isset($full['indexes'])) {
                $indexes = $this->data['fields'][$key]['field'];
            } elseif (is_array($full['indexes'])) {
                $indexes = implode(',', array_values($full['indexes']));
            } elseif (is_string($full['indexes'])) {
                $indexes = $full['indexes'];
            }

            $extra = isset($full['extra']) ? $full['extra'] : 'boolean';

            if (! in_array($extra, array('boolean', 'queryExpansion', false))) {
                throw new Bvb_Grid_Exception('Unrecognized value in extra key');
            }

            if ($extra == 'boolean') {
                $extra = 'IN BOOLEAN MODE';
            } elseif ($extra == 'queryExpansion') {
                $extra = ' WITH QUERY EXPANSION ';
            } else {
                $extra = '';
            }

            if ($extra == 'IN BOOLEAN MODE') {
                $filtro = preg_replace("/\s+/", " +", $this->_db->quote(' ' . $filtro));
            } else {
                $filtro = $this->_db->quote($filtro);
            }

            $this->_select->where(new Zend_Db_Expr("MATCH ($indexes) AGAINST ($filtro $extra) "));
            return;
        }

        if (! isset($this->data['fields'][$key]['searchType'])) {
            $this->data['fields'][$key]['searchType'] = 'like';
        }

        $op = strtolower($this->data['fields'][$key]['searchType']);

        if (strpos($filtro, '<>') !== false && substr($filtro, 0, 2) != '<>') {
            $op = 'range';
        } elseif (substr($filtro, 0, 1) == '=') {
            $op = '=';
            $filtro = substr($filtro, 1);
        } elseif (substr($filtro, 0, 2) == '>=') {
            $op = '>=';
            $filtro = substr($filtro, 2);
        } elseif ($filtro[0] == '>') {
            $op = '>';
            $filtro = substr($filtro, 1);
        } elseif (substr($filtro, 0, 2) == '<=') {
            $op = '<=';
            $filtro = substr($filtro, 2);
        } elseif (substr($filtro, 0, 2) == '<>' || substr($filtro, 0, 2) == '!=') {
            $op = '<>';
            $filtro = substr($filtro, 2);
        } elseif ($filtro[0] == '<') {
            $op = '<';
            $filtro = substr($filtro, 1);
        } elseif ($filtro[0] == '*' and substr($filtro, - 1) == '*') {
            $op = 'like';
            $filtro = substr($filtro, 1, - 1);
        } elseif ($filtro[0] == '*' and substr($filtro, - 1) != '*') {
            $op = 'llike';
            $filtro = substr($filtro, 1);
        } elseif ($filtro[0] != '*' and substr($filtro, - 1) == '*') {
            $op = 'rlike';
            $filtro = substr($filtro, 0, - 1);
        }

        if (isset($this->data['fields'][$key]['searchTypeFixed']) && $this->data['fields'][$key]['searchTypeFixed'] === true && $op != $this->data['fields'][$key]['searchType']) {
            $op = $this->data['fields'][$key]['searchType'];
        }

        switch ($op) {
            case 'equal':
            case '=':
                $this->_select->where($field . ' = ?', $filtro);
                break;
            case 'rlike':
                $this->_select->where(new Zend_Db_Expr($field . " LIKE " . $this->_db->quote($filtro . "%")));
                break;
            case 'llike':
                $this->_select->where(new Zend_Db_Expr($field . " LIKE " . $this->_db->quote("%" . $filtro)));
                break;
            case '>=':
                $this->_select->where(new Zend_Db_Expr($field . " >= " . $this->_db->quote($filtro)));
                break;
            case '>':
                $this->_select->where(new Zend_Db_Expr($field . " > " . $this->_db->quote($filtro)));
                break;
            case '<>':
            case '!=':
                $this->_select->where(new Zend_Db_Expr($field . " <> " . $this->_db->quote($filtro)));
                break;
            case '<=':
                $this->_select->where(new Zend_Db_Expr($field . " <= " . $this->_db->quote($filtro)));
                break;
            case '<':
                $this->_select->where(new Zend_Db_Expr($field . " < " . $this->_db->quote($filtro)));
                break;
            case 'range':

                $end = substr($filtro, 0, strpos($filtro, '<>'));
                $start = substr($filtro, strpos($filtro, '<>') + 2);
                $this->_select->where(new Zend_Db_Expr($field . " < " . $this->_db->quote($start)));
                $this->_select->where(new Zend_Db_Expr($field . " > " . $this->_db->quote($end)));
                break;
            case 'like':
            default:
                $this->_select->where(new Zend_Db_Expr($field . " LIKE " . $this->_db->quote("%" . $filtro . "%")));
                break;
        }

    }

    public function setDefaultFilters (array $filters)
    {
        $this->_defaultFilters = array_flip($filters);
        return $this;

    }

    /**
     * Build the query WHERE
     *
     * @return void
     */
    protected function _buildFiltersValues ()
    {

        if ($this->_queryWhere) {
            return;
        }

        //Build an array to know filters values
        $valor_filters = array();
        $filters = @urldecode($this->ctrlParams['filters' . $this->_id]);
        $filters = str_replace("filter_", "", $filters);
        $filters = Zend_Json::decode($filters);

        $fieldsSemAsFinal = $this->data['fields'];

        if (is_array($filters)) {
            foreach ($filters as $key => $filtro) {
                $key = str_replace("bvbdot", ".", $key);

                if (strlen($filtro) == 0 || ! in_array($key, $this->_fields)) {
                    unset($filters[$key]);
                } else {
                    $oldKey = $key;
                    if (@$fieldsSemAsFinal[$key]['searchField'] != "") {
                        $key = $fieldsSemAsFinal[$key]['searchField'];
                    }

                    $this->_buildSearchType($filtro, $oldKey, $key);
                    $valor_filters[$key] = $filtro;
                }

            }
        }

        $this->_filtersValues = $valor_filters;

        return $this;
    }

    /**
     * Build query.
     *
     * @return string
     */
    protected function _buildQuery ()
    {

        @$inicio = (int) $this->ctrlParams['start' . $this->_id];
        $order = @$this->ctrlParams['order' . $this->_id];
        $order1 = explode("_", $order);
        $orderf = strtoupper(end($order1));

        if ($orderf == 'DESC' || $orderf == 'ASC') {
            array_pop($order1);
            $order_field = implode("_", $order1);
            $query_order = $order_field . " $orderf ";

            foreach ($this->_select->getPart(Zend_Db_Select::COLUMNS) as $col) {
                if (($col[0] . '.' . $col[2] == $order_field) and is_object($col[1])) {
                    $query_order = $col[2] . " $orderf ";
                }
            }

            if (in_array($order_field, $this->_fieldsOrder)) {
                $this->_select->reset('order');
                $this->_select->order($query_order);
            }
        }

        $query_order = '';

        if (strlen($this->fieldHorizontalRow) > 0) {

            $split = $this->fieldHorizontalRow;
            if (strlen($query_order) > 4) {
                $query_order = $split . ' ASC ';
            } else {
                $query_order = $this->fieldHorizontalRow . ' ASC ';
            }

            $this->_select->order($query_order);
        }


        if (false === $this->_forceLimit) {
            $this->_select->limit($this->pagination, $inicio);
        }

        return true;
    }

    /**
     * Returns the url, without the param(s) specified
     *
     * @param array|string $situation
     * @return string
     */
    protected function _getUrl ($situation = '')
    {

        $paramsGet = array('order', 'start', 'filters', 'noFilters', '_exportTo');

        $url = '';
        $params = $this->ctrlParams;


        if (is_array($situation)) {
            foreach ($situation as $value) {
                if (in_array($value, $paramsGet)) {
                    $value = $value . $this->_id;
                }
                unset($params[$value]);
            }

        } else {
            if (in_array($situation, $paramsGet)) {
                $situation = $situation . $this->_id;
            }
            unset($params[$situation]);
        }

        if (count($this->params) > 0) {
            //User as defined its own params (probably using routes)
            $myParams = array('comm', 'order', 'filters', 'add', 'edit', '_exportTo');
            $newParams = $this->params;
            foreach ($myParams as $value) {
                if (strlen($params[$value]) > 0) {
                    $newParams[$value] = $params[$value];
                }
            }
            $params = $newParams;
        }

        $params_clean = $params;
        unset($params_clean['controller']);
        unset($params_clean['module']);
        unset($params_clean['action']);
        unset($params_clean['gridmod']);

        foreach ($params_clean as $key => $param) {
            // Apply the urldecode function to the filtros param, because its  JSON
            if ($key == 'filters' . $this->_id) {
                $url .= "/" . trim($key) . "/" . trim(htmlspecialchars(urlencode($param), ENT_QUOTES));
            } else {
                @$url .= "/" . trim($key) . "/" . trim(htmlspecialchars($param, ENT_QUOTES));
            }
        }

        if (strlen($params['action']) > 0) {
            $action = "/" . $params['action'];
        }

        if (Zend_Controller_Front::getInstance()->getDefaultModule() != $params['module']) {
            $urlPrefix = $params['module'] . "/";
        } else {
            $urlPrefix = '';
        }

        // Remove the action e controller keys, they are not necessary (in fact they aren't part of url)
        if (array_key_exists('ajax', $this->info) && $this->info['ajax'] !== false) {
            return $urlPrefix . $params['controller'] . $action . $url . "/gridmod/ajax";
        } else {
            return $this->_baseUrl . "/" . $urlPrefix . $params['controller'] . $action . $url;
        }
    }

    /**
     * Return variable stored in info. Return default if value is not stored.
     *
     * @param string $param
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getInfo ($param, $default = false)
    {
        if (isset($this->info[$param])) {
            return $this->info[$param];
        } else {
            return $default;
        }
    }

    /**
     *
     * Build Filters. If defined put the values
     * Also check if the user wants to hide a field
     *
     *
     * @return string
     */
    protected function _buildFilters ()
    {

        $return = array();
        if (isset($this->info['noFilters'])) {
            return false;
        }

        $data = $this->_fields;

        $tcampos = count($data);

        for ($i = 0; $i < count($this->extra_fields); $i ++) {
            if ($this->extra_fields[$i]['position'] == 'left') {
                $return[] = array('type' => 'extraField', 'class' => $this->template['classes']['filter'], 'position' => 'left');
            }
        }

        for ($i = 0; $i < $tcampos; $i ++) {

            $nf = $this->_fields[$i];

            if (! isset($this->data['fields'][$nf]['search'])) {
                $this->data['fields'][$nf]['search'] = true;
            }

            if ($this->_displayField($nf)) {

                if (! isset($this->data['fields'][$nf]['tooltipFilter'])) {
                    $this->data['fields'][$nf]['tooltipFilter'] = '';
                }

                if (@array_key_exists($data[$i], $this->filters) && $this->data['fields'][$nf]['search'] != false) {
                    $return[] = array('type' => 'field', 'tooltip' => $this->data['fields'][$nf]['tooltipFilter'], 'class' => $this->template['classes']['filter'], 'value' => isset($this->_filtersValues[$data[$i]]) ? $this->_filtersValues[$data[$i]] : '', 'field' => $data[$i]);
                } else {
                    $return[] = array('type' => 'field', 'tooltip' => $this->data['fields'][$nf]['tooltipFilter'], 'class' => $this->template['classes']['filter'], 'field' => $data[$i]);
                }
            }
        }

        for ($i = 0; $i < count($this->extra_fields); $i ++) {
            if ($this->extra_fields[$i]['position'] == 'right') {
                $return[] = array('type' => 'extraField', 'class' => $this->template['classes']['filter'], 'position' => 'right');
            }
        }

        return $return;
    }

    /**
     *
     * @param string $field
     * @return bool
     */
    protected function _displayField ($field)
    {

        if (! isset($this->data['fields'][$field]['remove'])) {
            $this->data['fields'][$field]['remove'] = false;
        }
        if (! isset($this->data['fields'][$field]['hidden'])) {
            $this->data['fields'][$field]['hidden'] = false;
        }

        if ($this->data['fields'][$field]['remove'] == 0 && (($this->data['fields'][$field]['hidden'] == 0) || ($this->data['fields'][$field]['hidden'] == 1 && $this->_removeHiddenFields !== true))) {

            return true;
        }

        return false;

    }

    /**
     *
     * @param array $fields
     * @return array
     */
    protected function _prepareReplace ($fields)
    {
        return array_map(create_function('$value', 'return "{{{$value}}}";'), $fields);
    }


    /**
     * Build the titles with the order links (if wanted)
     *
     * @return string
     */
    protected function _buildTitles ()
    {

        $return = array();
        $url = $this->_getUrl(array('order', 'start', 'comm'));

        $tcampos = count($this->_fields);

        for ($i = 0; $i < count($this->extra_fields); $i ++) {
            if ($this->extra_fields[$i]['position'] == 'left') {
                $return[$this->extra_fields[$i]['name']] = array('type' => 'extraField', 'value' => $this->extra_fields[$i]['name'], 'position' => 'left');
            }
        }

        $titles = $this->_fields;

        $novaData = array();

        if (is_array($this->data['fields'])) {
            foreach ($this->data['fields'] as $key => $value) {
                $nkey = stripos($key, ' AS ') ? substr($key, 0, stripos($key, ' AS ')) : $key;
                $novaData[$nkey] = $value;
            }
        }

        $links = $this->_fields;


        $selectOrder = $this->_select->getPart(Zend_Db_Select::ORDER);

        if (count($selectOrder) == 1) {
            $this->ctrlParams['order' . $this->_id] = $selectOrder[0][0] . '_' . strtoupper($selectOrder[0][1]);
        }

        for ($i = 0; $i < $tcampos; $i ++) {
            if (isset($this->ctrlParams['order' . $this->_id])) {
                $explode = explode('_', $this->ctrlParams['order' . $this->_id]);
                $name = str_replace('_' . end($explode), '', $this->ctrlParams['order' . $this->_id]);
                $this->order[$name] = strtoupper(end($explode)) == 'ASC' ? 'DESC' : 'ASC';
            }

            $fieldsToOrder = $this->_reset_keys($this->data['fields']);

            if (isset($fieldsToOrder[$i]['orderField']) && strlen($fieldsToOrder[$i]['orderField']) > 0) {
                $orderFinal = $fieldsToOrder[$i]['orderField'];
            } else {
                $orderFinal = $titles[$i];
            }

            $order = $orderFinal == @key($this->order) ? $this->order[$orderFinal] : 'ASC';

            if ($this->_displayField($titles[$i])) {

                $noOrder = isset($this->info['noOrder']) ? $this->info['noOrder'] : '';

                if (! isset($this->data['fields'][$titles[$i]]['tooltipTitle'])) {
                    $this->data['fields'][$titles[$i]]['tooltipTitle'] = '';
                }

                if ($noOrder == 1) {
                    $return[$titles[$i]] = array('type' => 'field', 'tooltip' => $this->data['fields'][$titles[$i]]['tooltipTitle'], 'name' => $links[$i], 'field' => $links[$i], 'value' => $this->_titles[$links[$i]]);
                } else {
                    $return[$titles[$i]] = array('type' => 'field', 'tooltip' => $this->data['fields'][$titles[$i]]['tooltipTitle'], 'name' => $titles[$i], 'field' => $orderFinal, 'simpleUrl' => $url, 'url' => "$url/order$this->_id/{$orderFinal}_$order", 'value' => $this->_titles[$links[$i]]);
                }
            }
        }

        for ($i = 0; $i < count($this->extra_fields); $i ++) {
            if ($this->extra_fields[$i]['position'] == 'right') {
                $return[$this->extra_fields[$i]['name']] = array('type' => 'extraField', 'value' => $this->extra_fields[$i]['name'], 'position' => 'right');
            }
        }

        $this->_finalFields = $return;

        return $return;
    }


    /**
     * Add the columns using an array
     *
     * @param array $columns
     * @return bool
     */
    public function addArrayColumns (array $columns)
    {

        $filter = array();

        if ($this->_getAdapter() != 'array') return false;

        foreach ($columns as $value) {
            if (is_array($value)) {
                $this->addArrayColumns($value);
            } else {
                $this->updateColumn($value);
                $filter[$value] = $value;
            }
        }

        $this->filters = $filter;

        return true;
    }

    /**
     * Add the records using an array
     */
    public function addArrayData ($data)
    {

        if ($this->_adapter != 'array') return false;

        $this->_result = $data;
        $this->_resultRaw = $data;
        return $this;
    }

    /**
     *
     * @param string $field
     * @return array
     */
    protected function _builFilterFromArray ($field)
    {

        $filter = array();
        foreach ($this->_resultRaw as $value) {
            $filter[$value[$field]] = $value[$field];
        }

        return array_unique($filter);
    }

    /**
     * Similar to fetchPairs
     *
     * @param array $array
     * @return array
     */
    protected function _convertResultSetToArrayKeys ($array)
    {

        $final = array();

        foreach ($array as $value) {
            $final[$value['field']] = $value['value'];
        }

        return $final;

    }


    protected function _replaceSpecialTags (&$item, $key, $text)
    {
        $item = str_replace($text['find'], $text['replace'], $item);
    }

    /**
     * Aplies the format option to a field
     * @param $new_value
     * @param $value
     * @param $search
     * @param $replace
     */
    protected function _applyFieldFormat ($new_value, $value, $search, $replace)
    {

        if (is_array($value)) {
            array_walk_recursive($value, array($this, '_replaceSpecialTags'), array('find' => $search, 'replace' => $replace));
        }

        return $this->_applyFormat($new_value, $value);
    }

    /**
     * Applies the callback option to a field
     * @param unknown_type $new_value
     * @param unknown_type $value
     * @param unknown_type $search
     * @param unknown_type $replace
     */
    protected function _applyFieldCallback ($new_value, $value, $search, $replace)
    {

        if (! is_callable($value['function'])) {
            throw new Bvb_Grid_Exception($value['function'] . ' not callable');
        }

        if (isset($value['params'])) {
            $toReplace = $value['params'];
        } else {
            $toReplace = array();
        }

        if (is_array($toReplace)) {
            array_walk_recursive($toReplace, array($this, '_replaceSpecialTags'), array('find' => $search, 'replace' => $replace));
        }

        $new_value = call_user_func_array($value['function'], $toReplace);

        return $new_value;
    }

    /**
     * Aplies the decorator to a fields
     * @param unknown_type $find
     * @param unknown_type $replace
     * @param unknown_type $value
     */
    protected function _applyFieldDecorator ($find, $replace, $value)
    {
        return str_replace($find, $replace, $value);
    }

    /**
     * Applies escape functions to a field
     * @param  $value
     */
    protected function _applyFieldEscape ($value)
    {

        if ($this->_escapeFunction === false) {
            return $value;
        }


        if (! is_callable($this->_escapeFunction)) {
            throw new Bvb_Grid_Exception($this->_escapeFunction . ' not callable');
        }

        $value = call_user_func($this->_escapeFunction, $value);
        return $value;

    }



    /**
     * Apply escape functions to column
     * @param string $field
     * @param string $new_value
     * @return mixed
     */
    private function _escapeField ($field, $new_value)
    {

        if (! isset($this->data['fields'][$field]['escape'])) {
            $this->data['fields'][$field]['escape'] = 1;
        }

        if (($this->data['fields'][$field]['escape'] ? 1 : 0) == 0) {
            return $new_value;
        }

        if ($this->data['fields'][$field]['escape'] == 1) {
            return $this->_applyFieldEscape($new_value);
        }

        if (! is_callable($this->data['fields'][$field]['escape'])) {
            throw new Bvb_Grid_Exception($this->data['fields'][$field]['escape'] . ' not callable');
        }

        return call_user_func($this->data['fields'][$field]['escape'], $new_value);

    }

    /**
     * The loop for the results.
     * Check the extra-fields,
     *
     * @return string
     */
    protected function _buildGrid ()
    {

        $return = array();

        $search = $this->_prepareReplace($this->_fields);

        $fields = $this->_fields;

        $i = 0;

        foreach ($this->_result as $dados) {

            /**
             *Deal with extrafield from the left
             */
            foreach ($this->_getExtraFields('left') as $value) {

                $value['class'] = ! isset($value['class']) ? '' : $value['class'];

                if (isset($value['format'])) {
                    $new_value = $this->_applyFieldFormat($new_value, $value['format'], $search, $dados);
                }

                if (isset($value['callback']['function'])) {
                    $new_value = $this->_applyFieldCallback($new_value, $value['callback'], $search, $dados);
                }

                if (isset($value['decorator'])) {
                    $new_value = $this->_applyFieldDecorator($search, $dados, $value['decorator']);
                }

                $return[$i][] = array('class' => $value['class'], 'value' => $new_value);
            }
            /**
             * Deal with the grid itself
             */
            $is = 0;
            foreach ($fields as $campos) {

                $outputToReplace = array();
                foreach ($fields as $value) {
                    $outputToReplace[] = $dados[$value];
                }

                $new_value = $dados[$fields[$is]];


                $new_value = $this->_escapeField($fields[$is],$new_value);

                if (isset($this->data['fields'][$fields[$is]]['callback']['function'])) {
                    $new_value = $this->_applyFieldCallback($new_value, $this->data['fields'][$fields[$is]]['callback'], $search, $outputToReplace);
                }

                if (isset($this->data['fields'][$fields[$is]]['format'])) {
                    $new_value = $this->_applyFieldFormat($new_value, $this->data['fields'][$fields[$is]]['format'], $search, $outputToReplace);
                }

                if (isset($this->data['fields'][$fields[$is]]['decorator'])) {
                    $new_value = $this->_applyFieldDecorator($search, $outputToReplace, $this->data['fields'][$fields[$is]]['decorator']);
                }

                if ($this->_displayField($fields[$is])) {
                    $fieldClass = isset($this->data['fields'][$fields[$is]]['class']) ? $this->data['fields'][$fields[$is]]['class'] : '';
                    $return[$i][] = @array('class' => $fieldClass, 'value' => $new_value, 'field' => $this->_fields[$is]);
                }

                $is ++;

            }

            /**
             * Deal with extra fields from the right
             */

            //Reset the value. This is an extra field.
            $new_value = null;
            foreach ($this->_getExtraFields('right') as $value) {

                $value['class'] = ! isset($value['class']) ? '' : $value['class'];

                if (isset($value['callback']['function'])) {
                    $new_value = $this->_applyFieldCallback($new_value, $value['callback'], $search, $dados);
                }

                if (isset($value['format'])) {
                    $new_value = $this->_applyFieldFormat($new_value, $value['format'], $search, $dados);
                }

                if (isset($value['decorator'])) {
                    $new_value = $this->_applyFieldDecorator($search, $dados, $value['decorator']);
                }

                $return[$i][] = array('class' => $value['class'], 'value' => $new_value);
            }
            $i ++;
        }

        return $return;
    }

    /**
     * Get the extra fields for a give position
     *
     * @param string $position
     * @return array
     */
    protected function _getExtraFields ($position = 'left')
    {

        if (! is_array($this->extra_fields)) {
            return array();
        }

        $final = array();

        foreach ($this->extra_fields as $value) {
            if ($value['position'] == $position) {
                $final[] = $value;
            }
        }

        return $final;

    }

    /**
     *Reset keys
     * @param unknown_type $array
     * @return unknown
     */
    protected function _reset_keys ($array)
    {

        if (! is_array($array)) return FALSE;

        $novo_array = array();
        $i = 0;
        foreach ($array as $value) {
            $novo_array[$i] = $value;
            $i ++;
        }
        return $novo_array;
    }

    /**
     * Applies the SQL EXP options to an array
     * @param $field
     * @param $operation
     * @param $option
     */
    protected function _applySqlExpToArray ($field, $operation, $option = 0)
    {

        foreach ($this->_resultRaw as $value) {

            $array[] = $value[$field];

        }

        $operation = trim(strtolower($operation));

        switch ($operation) {
            case 'product':
                return array_product($array);
                break;
            case 'sum':
                return array_sum($array);
                break;
            case 'count':
                return count($array);
                break;
            case 'min':
                sort($array);
                return array_shift($array);
                break;
            case 'max':
                sort($array);
                return array_pop($array);
                break;
            case 'avg':
                $option = (int) $option;
                return round(array_sum($array) / count($array), $option);
                break;
            default:
                throw new Bvb_Grid_Exception('Operation not found');
                break;
        }
    }

    /**
     * Apply SQL Functions
     *
     */
    protected function _buildSqlExp ()
    {

        $return = false;

        $final = isset($this->info['sqlexp']) ? $this->info['sqlexp'] : '';

        if (! is_array($final)) {
            return false;
        }

        if ($this->_adapter == 'array') {

            foreach ($final as $key => $value) {

                $result[$key] = $this->_applySqlExpToArray($key, $value);

            }

        } else {

            foreach ($final as $key => $value) {

                if (! array_key_exists($key, $this->data['fields'])) continue;

                if (is_array($value)) {
                    $valor = '';
                    foreach ($value['functions'] as $final) {
                        $valor .= $final . '(';
                    }

                    $valor .= $value['value'] . str_repeat(')', count($value['functions']));
                } else {
                    $valor = "$value(" . $value['value'] . ")";
                }

                $select = clone $this->_select;

                $select->reset(Zend_Db_Select::COLUMNS);
                $select->reset(Zend_Db_Select::ORDER);
                $select->reset(Zend_Db_Select::LIMIT_COUNT);
                $select->reset(Zend_Db_Select::LIMIT_OFFSET);
                $select->reset(Zend_Db_Select::GROUP);

                $select->columns(new Zend_Db_Expr($valor . ' AS TOTAL'));

                $final = $select->query(Zend_Db::FETCH_ASSOC);

                $result1 = $final->fetchAll();

                if (isset($value['format'])) {
                    $finalResult = $this->_applyFormat($result1[0]['TOTAL'], $value['format']);
                } else {
                    $finalResult = $result1[0]['TOTAL'];
                }

                $result[$key] = $finalResult;

            }

        }

        if (isset($result) && is_array($result)) {
            $return = array();
            foreach ($this->_finalFields as $key => $value) {
                if (array_key_exists($key, $result)) {
                    $class = isset($this->template['classes']['sqlexp']) ? $this->template['classes']['sqlexp'] : '';
                    $class .= isset($this->info['sqlexp'][$key]['class']) ? ' ' . $this->info['sqlexp'][$key]['class'] : '';
                    $return[] = array('class' => $class, 'value' => $result[$key], 'field' => $key);
                } else {
                    $class = isset($this->template['classes']['sqlexp']) ? $this->template['classes']['sqlexp'] : '';
                    $class .= isset($this->info['sqlexp'][$key]['class']) ? ' ' . $this->info['sqlexp'][$key]['class'] : '';
                    $return[] = array('class' => $class, 'value' => '', 'field' => $key);
                }
            }
        }
        return $return;
    }

    /**
     * Make sure the fields exists on the database, if not remove them from the array
     *
     * @param array $fields
     */
    protected function _validateFields ($fields)
    {

        if (is_array($fields)) {
            $hide = 0;
            $fields_final = array();
            $lastIndex = 1;

            foreach ($fields as $key => $value) {

                //A parte da order
                if (isset($value['orderField'])) {
                    $orderFields[$key] = $value['orderField'];
                } else {
                    $orderFields[$key] = $key;
                }

                if (isset($value['title'])) {
                    $titulos[$key] = $value['title'];
                } else {
                    $titulos[$key] = ucfirst($key);
                }

                if (isset($value['order']) && $value['order'] >= 0) {

                    if ($value['order'] == 'last') {
                        $fields_final[($lastIndex + 100)] = $key;
                    } elseif ($value['order'] == 'first') {
                        $fields_final[($lastIndex - 10)] = $key;
                    } elseif ($value['order'] == 'next') {
                        $norder++;
                        $fields_final[$norder] = $key;
                    } else {

                        $norder = (int) $value['order'];

                        $var = $value['order'];

                        while (true) {
                            if (array_key_exists($var, $fields_final)) {
                                $fields_final[$var + 1] = $fields_final[$var];
                                $var = $var + 2;
                            } else {
                                break;
                            }
                        }

                        $fields_final[$norder] = $key;

                    }
                } else {

                    while (true) {
                        if (array_key_exists($lastIndex, $fields_final)) {
                            $lastIndex ++;
                        } else {
                            break;
                        }
                    }

                    $fields_final[$lastIndex] = $key;
                }

            }



            ksort($fields_final);

            $fields_final = $this->_reset_keys($fields_final);
        }


        $this->_fields = $fields_final;
        $this->_titles = $titulos;
        $this->_fieldsOrder = $orderFields;
    }

    /**
     * Make sure the filters exists, they are the name from the table field.
     * If not, remove them from the array
     * If we get an empty array, we then creat a new one with all the fields specifieds
     * in $this->_fields method
     *
     * @param string $filters
     */
    protected function _validateFilters ()
    {

        if (isset($this->info['noFilters']) && $this->info['noFilters'] == 1) {
            return false;
        }

        $filters = null;

        if (is_array($this->filters)) {
            return $this->filters;
        } elseif ($this->_adapter == 'db') {
            $filters = array_combine($this->_fields, $this->_fields);
        }

        return $filters;
    }

    /**
     * Get the primary table key
     * This is important because we only allow edit, add or remove records
     * From tables that have on primary key
     *
     * @return string
     */
    protected function _getPrimaryKey ($table = null)
    {

        if (null === $table) {
            $table = $this->data['table'];
        }

        if (isset($this->_primaryKey[$table])) {
            return $this->_primaryKey[$table];
        }

        $pk = $this->_getDescribeTable($table);

        $keys = array();

        foreach ($pk as $pkk => $primary) {
            if ($primary['PRIMARY'] == 1) {
                $keys[] = $pkk;
            }
        }

        $this->_primaryKey[$table] = $keys;

        return $this->_primaryKey[$table];
    }

    /**
     * Count the rows total without the limit
     *
     * @return void
     */
    protected function _builQueryCount ()
    {

        $this->_selectCount = clone $this->_select;

        $this->_selectCount->reset(Zend_Db_Select::COLUMNS);
        $this->_selectCount->reset(Zend_Db_Select::LIMIT_OFFSET);
        $this->_selectCount->reset(Zend_Db_Select::LIMIT_COUNT);
        $this->_selectCount->reset(Zend_Db_Select::ORDER);

        $this->_selectCount->columns(new Zend_Db_Expr('COUNT(*) AS TOTAL '));

        return;
    }

    /**
     * Build user defined filters
     */
    protected function _buildDefaultFilters ()
    {

        if (is_array($this->_defaultFilters) && ! isset($this->ctrlParams['filters' . $this->_id]) && ! isset($this->ctrlParams['nofilters'])) {
            $df = array();
            foreach ($this->data['fields'] as $key => $value) {

                if (! $this->_displayField($key)) {
                    continue;
                }

                if (array_key_exists($key, array_flip($this->_defaultFilters))) {
                    $df['filter_' . $key] = array_search($key, $this->_defaultFilters);
                } else {
                    $df['filter_' . $key] = '';
                }

            }

            $defaultFilters = $df;

            $this->ctrlParams['filters' . $this->_id] = Zend_Json_Encoder::encode($defaultFilters);
        }

        return $this;
    }

    /**
     * Done. Send the grid to the user
     *
     * @return string
     */
    public function deploy ()
    {
        // apply additional configuration
        $this->_runConfigCallbacks();

        if ($this->_selectZendDb !== true && $this->_getAdapter() == 'db') {
            throw new Bvb_Grid_Exception('You must specify the query object using a Zend_Db_Select instance');
        }

        $this->_buildDefaultFilters();

        // Validate table fields, make sure they exist...
        $this->_validateFields($this->data['fields']);

        // Filters. Not required that every field as filter.
        $this->filters = $this->_validateFilters($this->filters);


        $this->_buildFiltersValues();

        if ($this->_adapter == 'db') {

            $this->_buildQuery();
            $this->_builQueryCount();

            if ($this->cache['use'] == 1) {
                $cache = $this->cache['instance'];

                if (! $result = $cache->load(md5($this->_select->__toString()))) {

                    $stmt = $this->_select->query(Zend_Db::FETCH_ASSOC);
                    ;
                    $result = $stmt->fetchAll();

                    if ($this->_forceLimit === false) {

                        $selectZendDb = clone $this->_select;
                        if ($this->_forceLimit == false) {
                            $selectZendDb->reset(Zend_Db_Select::LIMIT_COUNT);
                            $selectZendDb->reset(Zend_Db_Select::LIMIT_OFFSET);
                        }
                        $selectZendDb->reset(Zend_Db_Select::GROUP);
                        $selectZendDb->reset(Zend_Db_Select::COLUMNS);
                        $selectZendDb->reset(Zend_Db_Select::ORDER);
                        $selectZendDb->columns(array('TOTAL' => new Zend_Db_Expr("COUNT(*)")));

                        $stmt = $selectZendDb->query(Zend_Db::FETCH_ASSOC);

                        $resultZendDb = $stmt->fetchAll();

                        if (count($resultZendDb) == 1) {
                            $resultCount = $resultZendDb[0]['TOTAL'];
                        } else {
                            $resultCount = count($resultZendDb);
                        }

                    } else {

                        $resultCount = $this->_forceLimit;

                        if (count($result) < $resultCount) {
                            $resultCount = count($result);
                        }
                    }

                    $cache->save($result, md5($this->_select->__toString()), array($this->cache['tag']));
                    $cache->save($resultCount, md5($this->_selectCount->__toString()), array($this->cache['tag']));

                } else {
                    $result = $cache->load(md5($this->_select->__toString()));
                    $resultCount = $cache->load(md5($this->_selectCount->__toString()));
                }

            } else {

                $stmt = $this->_select->query(Zend_Db::FETCH_ASSOC);

                $result = $stmt->fetchAll();

                if ($this->_forceLimit === false) {

                    $selectZendDb = clone $this->_select;
                    if ($this->_forceLimit == false) {
                        $selectZendDb->reset(Zend_Db_Select::LIMIT_COUNT);
                        $selectZendDb->reset(Zend_Db_Select::LIMIT_OFFSET);
                    }
                    $selectZendDb->reset(Zend_Db_Select::GROUP);
                    $selectZendDb->reset(Zend_Db_Select::COLUMNS);
                    $selectZendDb->reset(Zend_Db_Select::ORDER);
                    $selectZendDb->columns(array('TOTAL' => new Zend_Db_Expr("COUNT(*)")));

                    $stmt = $selectZendDb->query(Zend_Db::FETCH_ASSOC);

                    $resultZendDb = $stmt->fetchAll();

                    if (count($resultZendDb) == 1) {
                        $resultCount = $resultZendDb[0]['TOTAL'];
                    } else {
                        $resultCount = count($resultZendDb);
                    }

                } else {

                    $resultCount = $this->_forceLimit;

                    if (count($result) < $resultCount) {
                        $resultCount = count($result);
                    }
                }
            }

            //Total records found
            $this->_totalRecords = $resultCount;

            //The result
            $this->_result = $result;

        } else {

            $filters = Zend_Json::decode(@$this->ctrlParams['filters' . $this->_id]);
            if (is_array($filters)) {

                foreach ($filters as $key => $filter) {
                    $explode = explode('_', $key);
                    $key = end($explode);
                    $filterValue[$key] = $filter;
                }

                $filters = $filterValue;

                $find = $this->_findInArray($filters);

                $this->_filtersValues = $filterValue;

                if (count($find) > 0) {
                    $this->_result = $find;

                } elseif ($this->_searchPerformedInArray === true) {
                    $this->_result = array();
                }

            }

            if (isset($this->ctrlParams['order']) || strlen(@$this->data['order']) > 3) {

                if (strlen($this->data['order']) > 3 && ! isset($this->ctrlParams['order'])) {

                    $explode = explode(' ', $this->data['order']);

                    $order = reset($explode);
                    $orderType = end($explode);
                    if (strtoupper($orderType) != 'ASC' && strtoupper($orderType) != 'DESC') {
                        $orderType = 'ASC';
                    }

                    $orderType = strtoupper($orderType) == 'ASC' ? SORT_ASC : SORT_DESC;

                } else {

                    $explode = explode('_', $this->ctrlParams['order']);
                    $order = reset($explode);
                    $orderType = end($explode);

                    $orderType = strtoupper($orderType) == 'ASC' ? SORT_ASC : SORT_DESC;
                }

                // Obtain a list of columns
                foreach ($this->_result as $key => $row) {
                    $result[$key] = $row[$order];
                }

                @array_multisort($result, $orderType, $this->_result);

            }

            if ($this->pagination == 0) {
                $this->_totalRecords = count($this->_result);
                $result = $this->_result;

            } else {
                $this->_totalRecords = count($this->_result);
                $result = array_slice($this->_result, (int) @$this->ctrlParams['start' . $this->_id] < $this->_totalRecords ? (int) @$this->ctrlParams['start' . $this->_id] : 0, $this->pagination);
            }

            $this->_result = $result;

        }

        $this->_colspan();
        return $this;
    }

    /**
     * Get details about a column
     *
     * @param string $column
     * @return null|array
     */
    protected function _getColumn ($column)
    {

        return isset($this->data['fields'][$column]) ? $this->data['fields'][$column] : null;

    }

    /**
     * Search function for array adapters
     */
    protected function _findInArray ($filters)
    {

        $filtersNumber = 0;
        foreach ($filters as $value) {
            if (strlen($value) > 0) {
                $filtersNumber ++;
            }
        }

        $this->_searchPerformedInArray = true;

        $find = array();

        foreach ($this->_result as $result) {

            $i = 0;

            foreach ($filters as $filterKey => $filterValue) {
                foreach ($result as $fieldKey => $fieldValue) {
                    if (strlen($filterValue) > 0 && $fieldKey == $filterKey) {

                        if ($this->_applySearchTypeToArray($fieldValue, $filterValue, $filterKey)) {
                            $i ++;
                        }
                    }
                }
            }

            if ($i == $filtersNumber) {
                $find[] = $result;
            }

        }

        return $find;

    }

    /**
     * Apply the search to a give field when the adaptar is an array
     */
    protected function _applySearchTypeToArray ($final, $filtro, $key)
    {

        if (! isset($this->data['fields'][$key]['searchType'])) {
            $this->data['fields'][$key]['searchType'] = 'like';
        }

        $op = strtolower($this->data['fields'][$key]['searchType']);

        if (substr($filtro, 0, 1) == '=') {
            $op = '=';
            $filtro = substr($filtro, 1);
        } elseif (substr($filtro, 0, 2) == '>=') {
            $op = '>=';
            $filtro = substr($filtro, 2);
        } elseif ($filtro[0] == '>') {
            $op = '>';
            $filtro = substr($filtro, 1);
        } elseif (substr($filtro, 0, 2) == '<=') {
            $op = '<=';
            $filtro = substr($filtro, 2);
        } elseif (substr($filtro, 0, 2) == '<>' || substr($filtro, 0, 2) == '!=') {
            $op = '<>';
            $filtro = substr($filtro, 2);
        } elseif ($filtro[0] == '<') {
            $op = '<';
            $filtro = substr($filtro, 1);
        } elseif ($filtro[0] == '*' and substr($filtro, - 1) == '*') {
            $op = 'like';
            $filtro = substr($filtro, 1, - 1);
        } elseif ($filtro[0] == '*' and substr($filtro, - 1) != '*') {
            $op = 'llike';
            $filtro = substr($filtro, 1);
        } elseif ($filtro[0] != '*' and substr($filtro, - 1) == '*') {
            $op = 'rlike';
            $filtro = substr($filtro, 0, - 1);
        }

        if (isset($this->data['fields'][$key]['searchTypeFixed']) && $this->data['fields'][$key]['searchTypeFixed'] === true && $op != $this->data['fields'][$key]['searchType']) {
            $op = $this->data['fields'][$key]['searchType'];
        }

        switch ($op) {
            case 'equal':
            case '=':
                if ($filtro == $final) return true;
                break;
            case 'rlike':
                if (substr($final, 0, strlen($filtro)) == $filtro) return true;
                break;
            case 'llike':
                if (substr($final, - strlen($filtro)) == $filtro) return true;
                break;
            case '>=':
                if ($final >= $filtro) return true;
                break;
            case '>':
                if ($final > $filtro) return true;
                break;
            case '<>':
            case '!=':
                if ($final != $filtro) return true;
                break;
            case '<=':
                if ($final <= $filtro) return true;
                break;
            case '<':
                if ($final < $filtro) return true;
                break;
            default:
                $enc = stripos((string) $final, $filtro);
                if ($enc !== false) {
                    return true;
                }
                break;
        }

        return false;

    }

    /**
     *Convert Object to Array
     * @param object $object
     * @return array
     */
    protected function _object2array ($data)
    {

        if (! is_object($data) && ! is_array($data)) return $data;

        if (is_object($data)) $data = get_object_vars($data);

        return array_map(array($this, '_object2array'), $data);

    }

    /**
     * set template locations
     *
     * @param string $path
     * @param string $prefix
     * @return unknown
     */
    public function addTemplateDir ($dir, $prefix, $type)
    {

        if (! isset($this->_templates[$type])) {
            $this->_templates[$type] = new Zend_Loader_PluginLoader();
        }

        $this->_templates[$type]->addPrefixPath(trim($prefix, "_"), trim($dir, "/") . '/', $type);
        return $this;
    }

    /**
     * Define the template to be used
     *
     * @param string $template
     * @return unknown
     */
    public function setTemplate ($template, $output = 'table')
    {

        $class = $this->_templates[$output]->load($template, $output);

        if (isset($this->_options['template'][$output][$template])) {
            $tpOptions = $this->_options['template'][$output][$template];
        } else {
            $tpOptions = array();
        }


        $tpInfo = array('colspan' => $this->_colspan, 'charEncoding' => $this->charEncoding, 'name' => $template, 'dir' => $this->_templates[$output]->getClassPath($template, $output), 'class' => $this->_templates[$output]->getClassName($template, $output));

        $this->temp[$output] = new $class();
        $this->activeTemplates[] = $output;

        $this->temp[$output]->options = array_merge($tpInfo, $tpOptions);

        return $this->temp[$output];

    }

    /**
     * Add multiple columns at once
     *
     */
    public function updateColumns ()
    {

        $fields = func_get_args();

        foreach ($fields as $value) {

            if ($value instanceof Bvb_Grid_Column) {

                $value = $this->_object2array($value);
                foreach ($value as $field) {

                    $finalField = $field['field'];
                    unset($field['field']);
                    $this->updateColumn($finalField, $field);

                }
            }
        }
    }



    /**
     * Calculate colspan for pagination and top
     *
     * @return int
     */
    protected function _colspan ()
    {

        $totalFields = count($this->_fields);
        $a = 0;
        $i = 0;
        foreach ($this->data['fields'] as $value) {
            if (isset($value['remove']) && $value['remove'] == 1) {
                $i ++;
            } elseif (isset($value['hidden']) && $value['hidden'] == 1 && $this->_removeHiddenFields === true) {
                $i ++;
            }

            if (isset($value['hRow']) && $value['hRow'] == 1) {
                $totalFields --;
            }
        }

        $totalFields = $totalFields - $i;
        if (isset($this->info['delete']['allow']) && $this->info['delete']['allow'] == 1) {
            $a ++;
        }

        if (isset($this->info['edit']['allow']) && $this->info['edit']['allow'] == 1) {
            $a ++;
        }

        $totalFields = $totalFields + $a;
        $colspan = $totalFields + count($this->extra_fields);

        # if (isset($this->temp[$this->output]) && is_object($this->temp[$this->output])) {
        #$this->temp[$this->output]->colSpan = $colspan;
        #}


        $this->_colspan = $colspan;

        return $colspan;
    }


    /**
     * Add filters
     *
     */
    public function addFilters ($filters)
    {

        $filters = $this->_object2array($filters);
        $filters = $filters['_filters'];
        $this->filters = $filters;

    }

    /**
     * Add extra columns
     *
     * @return unknown
     */
    public function addExtraColumns ()
    {

        $extra_fields = func_get_args();

        if (is_array($this->extra_fields)) {
            $final = $this->extra_fields;
        } else {
            $final = array();
        }

        foreach ($extra_fields as $value) {
            if ($value instanceof Bvb_Grid_ExtraColumns) {
                $value = $this->_object2array($value);
                array_push($final, $value['_field']);
            }
        }
        $this->extra_fields = $final;
        return $this;
    }

    /**
     * Get table description and then save it to a array.
     *
     * @param array|string $table
     * @return array
     */
    protected function _getDescribeTable ($table)
    {

        if ($this->_getAdapter() != 'db') {
            return false;
        }

        if (! isset($this->_describeTables[$table]) || ! @is_array($this->_describeTables[$table])) {

            if ($this->cache['use'] == 1) {
                $cache = $this->cache['instance'];
                if (! $describe = $cache->load(md5('describe' . $table))) {
                    $describe = $this->_db->describeTable($table);
                    $cache->save($describe, md5('describe' . $table), array($this->cache['tag']));
                } else {
                    $describe = $cache->load(md5('describe' . $table));
                }
            } else {
                $describe = $this->_db->describeTable($table);
            }

            $this->_describeTables[$table] = $describe;
        }

        return $this->_describeTables[$table];
    }

    /**
     * Build the fields based on Zend_Db_Select
     * @param $fields
     * @param $tables
     */
    protected function _getFieldsFromQuery (array $fields, array $tables)
    {

        foreach ($fields as $key => $value) {

            /**
             * Select all fields from the table
             */
            if ($value[1] == '*') {

                if (array_key_exists($value[0], $tables)) {
                    $tableFields = $this->_getDescribeTable($tables[$value[0]]['tableName']);
                }

                $tableFields = array_keys($tableFields);

                foreach ($tableFields as $field) {
                    $title = ucfirst($field);
                    $this->updateColumn($field, array('title' => $title, 'field' => $value[0] . '.' . $field));
                }

            } else {

                $explode = explode('.', $value[1]);
                $title = ucwords(str_replace("_", ' ', end($explode)));

                if (is_object($value[1])) {
                    $title = $value[2];
                    $this->updateColumn($value[2], array('title' => $title, 'field' => $value[0] . '.' . $value[2]));
                } elseif (strlen($value[2]) > 0) {
                    $title = $value[2];
                    $this->updateColumn($value[2], array('title' => $title, 'field' => $value[0] . '.' . $value[1]));
                } else {
                    $title = ucfirst($value[1]);
                    $this->updateColumn($value[1], array('title' => $title, 'field' => $value[0] . '.' . $value[1]));
                }

            }
        }

        $this->_allFieldsAdded = true;

        if (count($this->_updateColumnQueue) > 0) {
            foreach ($this->_updateColumnQueue as $field => $options) {

                if (! array_key_exists($field, $this->data['fields'])) continue;

                $this->updateColumn($field, $options);
            }

            $this->_updateColumnQueue = array();
        }

        return $this;
    }

    /**
     * Define the query using Zend_Db_Select instance
     *
     * @param Zend_Db_Select $select
     * @return $this
     */
    public function query (Zend_Db_Select $select)
    {

        $this->_db = $select->getAdapter();
        $this->_setAdapter('db');

        //Instanciate the Zend_Db_Select object
        $this->_select = $this->_db->select();

        $this->_selectZendDb = true;

        $this->_select = $select;

        $this->_tablesList = $this->_select->getPart(Zend_Db_Select::FROM);

        $this->_getFieldsFromQuery($this->_select->getPart(Zend_Db_Select::COLUMNS), $this->_select->getPart(Zend_Db_Select::FROM));

        $from = $this->_select->getPart(Zend_Db_Select::FROM);

        if ($this->_select->getPart(Zend_Db_Select::LIMIT_COUNT) > 0) {
            $this->_forceLimit = $this->_select->getPart(Zend_Db_Select::LIMIT_COUNT);
            $this->setPagination($this->_forceLimit);
        }

        foreach ($from as $key => $tables) {

            if ($tables['joinType'] == 'from') {
                $this->data['table'] = $tables['tableName'];
                $this->data['tableAlias'] = $key;
                break;
            }
        }

        return $this;
    }

    /**
     * Returns the grid version
     * @return string
     */
    public function getVersion ()
    {
        return self::VERSION;
    }

    /**
     * Return number records found
     */
    public function getTotalRecords ()
    {
        return (int) $this->_totalRecords;
    }

    /**
     * Return the query object
     */
    public function getSelectObject ()
    {
        return $this->_select;
    }

    /**
     * Automates export functionality
     *
     * @param array|array of array $classCallbacks key should be lowercase, functions to call once before deploy() and ajax() functions
     * @param array|boolean $requestData request parameters will bu used if FALSE
     */
    public static function factory ($defaultClass, $options = array(), $id = '', $classCallbacks = array(), $requestData = false)
    {

        if (! is_string($id)) {
            $id = "";
        }

        if (false === $requestData) {
            $requestData = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        }

        if (! isset($requestData['_exportTo' . $id])) {

            // return instance of the main Bvb object, because this is not and export request
            $grid = new $defaultClass($options);
            $lClass = $defaultClass;
        } else {
            $lClass = strtolower($requestData['_exportTo' . $id]);
            // support translating of parameters specifig for the export initiator class
            if (isset($requestData['_exportFrom'])) {
                // TODO support translating of parameters specifig for the export initiator class
                $requestData = $requestData;
            }

            // now we need to find and load the right Bvb deploy class
            $className = "Bvb_Grid_Deploy_" . ucfirst($requestData['_exportTo' . $id]); // TODO support user defined classes
            if (Zend_Loader_Autoloader::autoload($className)) {
                $grid = new $className($options);
            } else {
                $grid = new $defaultClass($options);
                $lClass = $defaultClass;
            }
        }

        // add the powerfull configuration callback function
        if (isset($classCallbacks[$lClass])) {
            $grid->_configCallbacks = $classCallbacks[$lClass];
        }

        if (is_string($id)) {
            $grid->_setId($id);
        }

        return $grid;
    }

    /**
     *
     * @return
     */
    protected function _runConfigCallbacks ()
    {
        if (! is_array($this->_configCallbacks)) {
            call_user_func($this->_configCallbacks, $this);
        } elseif (count($this->_configCallbacks) == 0) {
            // no callback
            return;
        } elseif (count($this->_configCallbacks) > 1 && is_array($this->_configCallbacks[0])) {
            die("multi");
            // TODO maybe fix
            // ordered list of callback functions defined
            foreach ($this->_configCallbacks as $func) {

            }
            break;
        } else {
            // only one callback function defined
            call_user_func($this->_configCallbacks, $this);
        }
        // run it only once
        $this->_configCallbacks = array();
    }
    /**
     * Build list of exports with options
     *
     * Options:
     * caption   - mandatory
     * img       - (default null)
     * cssClass   - (default ui-icon-extlink)
     * newWindow - (default true)
     * url       - (default actual url)
     * onClick   - (default null)
     * _class    - (reserved, used internaly)
     */
    public function getExports ()
    {
        $res = array();
        foreach ($this->export as $name => $defs) {
            if (! is_array($defs)) {
                // only export name is passed, we need to get default option
                $name = $defs;
                $className = "Bvb_Grid_Deploy_" . $name; // TODO support user defined classes
                if (Zend_Loader_Autoloader::autoload($className) && method_exists($className, 'getExportDefaults')) {
                    // learn the defualt values
                    $defs = call_user_func(array($className, "getExportDefaults"));
                } else {
                    // there are no defaults, we need at least some caption
                    $defs = array('caption' => $name);
                }
                $defs['_class'] = $className;
            }
            $res[$name] = $defs;
        }

        return $res;
    }

    /**
     * This is usefull if the deploy clas has no intention of using hidden fields
     * @param bool $value
     * @return $this
     */
    protected function _setRemoveHiddenFields ($value)
    {

        $this->_removeHiddenFields = (bool) $value;
        return $this;

    }

    /**
     *
     * @param $options
     */
    public function updateOptions ($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (! is_array($options)) {
            throw new Bvb_Grid_Exception('options must be an instance from Zend_Config or an array');
        }

        $this->_options = array_merge($this->options, $options);
        return $this;
    }


    /**
     *
     * @param $options
     */
    public function setOptions ($options)
    {
        $this->_options = array_merge($options, $this->_options);
        return $this;
    }


    /**
     * Apply the options to the fields
     */
    protected function _applyOptionsToFields ()
    {
        if (isset($this->_options['fields']) && is_array($this->_options['fields'])) {
            foreach ($this->_options['fields'] as $field => $options) {

                if (isset($options['format']['function'])) {
                    if (! isset($options['format']['params'])) {
                        $options['format']['params'] = array();
                    }
                    $options['format'] = array($options['format']['function'], $options['format']['params']);
                }

                if (isset($options['callback'])) {

                    if (! isset($options['callback']['params'])) {
                        $options['callback']['params'] = array();
                    }

                    if (isset($options['callback']['function']) && isset($options['callback']['class'])) {
                        $options['callback'] = array('function' => array($options['callback']['class'], $options['callback']['function']), 'params' => $options['callback']['params']);
                    } else {
                        $options['callback'] = array('function' => $options['callback']['function'], 'params' => $options['callback']['params']);
                    }

                }

                $this->updateColumn($field, $options);

            }
        }

        $deploy = explode('_', get_class($this));
        $name = strtolower(end($deploy));

        if (isset($this->_options['deploy'][$name]) && is_array($this->_options['deploy'][$name])) {
            $this->deploy = $this->_options['deploy'][$name];
        }

    }


    /**
     * Sets the grid id, to allow multiples instances per page
     * @param $id
     */
    protected function _setId ($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Returns the current id.
     * ""=>emty string is a valid value
     */
    public function getId ()
    {
        return $this->_id;
    }

    /**
     * Define if use order or not
     * @param $value
     */
    function setNoOrder ($value)
    {
        $this->info['noOrder'] = (bool) $value;
        return $this;
    }

    /**
     * Define if use Filters or Not
     * @param (bool) $value
     */
    function setNoFilters ($value)
    {
        $this->info['noFilters'] = (bool) $value;
        return $this;
    }
}

