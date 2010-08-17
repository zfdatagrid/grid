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
 * @category  Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id$
 * @link      http://zfdatagrid.com
 */

abstract class Bvb_Grid
{
    const VERSION = '$Rev$';

    /**
     * Char encoding
     *
     * @var string
     */
    protected $_charEncoding = 'UTF-8';

    /**
     * DBRM server name
     * @var string
     */
    private $_server = null;

    /**
     * Fields order
     *
     * @var unknown_type
     */
    private $_fieldsOrder;

    /**
     * The path where we can find the library
     * Usually is lib or library
     *
     * @var unknown_type
     */
    protected $_libraryDir = 'library';

    /**
     * classes location
     *
     * @var array
     */
    //TODO set template classes from config file
    protected $_template = array();

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
    protected $_recordsPerPage = 15;

    /**
     * Number of results per page
     *
     * @var int
     */
    protected $_paginationOptions = array();

    /**
     * Type of export available
     *
     * @var array
     */
    protected $_export = array('pdf', 'word', 'wordx', 'excel', 'print', 'xml', 'csv', 'ods', 'odt', 'json');

    #protected $_export = array('pdf', 'word', 'wordx', 'excel', 'print', 'xml', 'csv', 'ods', 'odt', 'json','ofc');

    /**
     * All info that is not directly related to the database
     */
    protected $_info = array();

    /**
     * URL to prefix in case of routes
     * @var unknown_type
     */
    protected $_routeUrl = false;

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
     * Filters list
     *
     * @var array
     */
    protected $_filters;

    /**
     * Filters Render
     * @var
     */
    protected $_filtersRenders;

    /**
     * @var array
     */
    protected $_externalFilters = array();

    /**
     * Filters values inserted by the user
     *
     * @var array
     */
    protected $_filtersValues;

    /**
     * All information database related
     *
     * @var array
     */
    protected $_data = array();

    /**
     * URL params
     *
     * @var string
     */
    protected $_ctrlParams = array();

    /**
     * Extra fields array
     *
     * @var array
     */
    protected $_extraFields = array();

    /**
     * Final fields list (after all procedures).
     *
     * @var unknown_type
     */
    protected $_finalFields;

    /**
     *Use cache or not.
     * @var bool
     */
    protected $_cache = false;

    /**
     * The field to set order by, if we have a horizontal row
     *
     * @var string
     */
    private $_fieldHorizontalRow;

    /**
     * Template instance
     *
     * @var unknown_type
     */
    protected $_temp;

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
     * Default filters to be applied
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
     * Functions to be applied on every fields before display
     * @var unknown_type
     */
    protected $_escapeFunction = 'htmlspecialchars';

    /**
     * Grid Options.
     * They can be
     * @var array
     */
    protected $_options = array();

    /**
     * Id used for multiples instances on the same page
     *
     * @var string
     */
    protected $_gridId;

    /**
     * Colspan for table
     * @var int
     */
    protected $_colspan;

    /**
     * User defined INFO for templates
     * @var array
     */
    protected $_templateParams = array();

    /**
     * To let a user know if the grid will be displayed or not
     * @var unknown_type
     */
    protected $_showsGrid = false;

    /**
     * Array of fields that should appear on detail view
     * @var unknown_type
     */
    protected $_gridColumns = null;

    /**
     * Array of columns that should appear on detail view
     * @var unknown_type
     */
    protected $_detailColumns = null;

    /**
     * If we are on detail or grid view
     * @var unknown_type
     */
    protected $_isDetail = false;

    /**
     * @var Zend_View_Interface
     */
    protected $_view;

    /**
     * Information from FORM
     * @var object
     */
    protected $_crud = null;

    /**
     *
     * @var Bvb_Grid_Source_Interface
     */
    private $_source = null;

    /**
     * Last name from deploy class (table|pdf|csv|etc...)
     * @var unknown_type
     */
    protected $_deployName = null;

    /**
     * What is being done with this request
     * @var unknown_type
     */
    protected $_willShow = array();

    /**
     * Print class based on conditions
     * @var array
     */
    protected $_classRowCondition = array();

    /**
     * Result to apply to every <tr> based on condition
     * @var $_classRowConditionResult array
     */
    protected $_classRowConditionResult = array();

    /**
     * Condition to apply a CSS class to a table cell <td>
     * @var unknown_type
     */
    protected $_classCellCondition = array();

    /**
     * Order setted by adapter
     * @var unknown_type
     */
    protected $_order;

    /**
     * custom translate instance
     * @var Zend_Translate
     */
    protected $_translator;

    /**
     *
     * @var bool
     */
    protected $_showFiltersInExport = false;

    /**
     * If whe should save filters in session
     * @var bool
     */
    protected $_paramsInSession = false;

    /**
     * Session Params Zend_Session
     * @var unknown_type
     */
    protected $_sessionParams = false;

    /**
     * Hold definitions from configurations
     * @var array
     */
    protected  $_deploy = array();

    /**
     * IF user has defined mass actions operations
     * @var bool
     */
    protected $_hasMassActions = false;

    protected $_massActions = false;

    /**
     * Columns that should be return when submiting the form
     * @var array
     */
    protected $_massActionsFields = array();

    /**
     * Backwards compatibility
     * @param $object
     * @return Bvb_Grid
     */
    public function query($object)
    {
        if ($object instanceof Zend_Db_Select) {
            $this->setSource(new Bvb_Grid_Source_Zend_Select($object));
        } elseif ($object instanceof Zend_Db_Table_Abstract) {
            $this->setSource(new Bvb_Grid_Source_Zend_Table($object));
        } else {
            throw new Bvb_Grid_Exception('Please use the setSource() method instead');
        }

        return $this;
    }

    /**
     * Sets the source to be used
     *
     * Bvb_Grid_Source_*
     *
     * @param Bvb_Grid_Source_SourceInterface $source
     * @return Bvb_Grid
     */
    public function setSource(Bvb_Grid_Source_SourceInterface $source)
    {
        $this->_source = $source;

        $this->getSource()
            ->setCache($this->getCache());

        $tables = $this->getSource()
            ->getMainTable();

        $this->_data['table'] = $tables['table'];
        $this->_crudTable = $this->_data['table'];

        $fields = $this->getSource()
            ->buildFields();

        foreach ($fields as $key => $field) {
            $this->updateColumn($key, $field);
        }

        $this->_allFieldsAdded = true;
        //Apply options to the fields
        $this->_applyOptionsToFields();

        return $this;
    }

    /**
     * The path where we can find the library
     * Usually is lib or library
     * @param $dir
     * @return Bvb_Grid
     */
    public function setLibraryDir($dir)
    {
        $this->_libraryDir = $dir;
        return $this;
    }

    /**
     * Returns the actual library path
     */
    public function getLibraryDir()
    {
        return $this->_libraryDir;
    }

    /**
     * Sets grid cache
     * @param bool|array $cache
     */
    public function setCache($cache)
    {
        if ($cache == false || (is_array($cache) && isset($cache['use']) && $cache['use'] == 0)) {
            $this->_cache = array('use' => 0);
            return $this;
        }

        if (is_array($cache) && isset($cache['use']) && isset($cache['instance']) && isset($cache['tag'])) {
            $this->_cache = $cache;
            return $this;
        }

        return false;
    }

    /**
     * Returns actual cache params
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Returns the actual source object
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Get db instance
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getDb()
    {
        return $this->_db;
    }

    /**
     * Defines a custom Translator
     * @param Zend_Translate $translator
     */
    public function setTranslator(Zend_Translate $translator)
    {
        Bvb_Grid_Translator::getInstance()->setTranslator($translator);
        return $this;
    }

    /**
     * The __construct function receives the db adapter. All information related to the
     * URL is also processed here
     *
     * @param array $data
     */
    public function __construct($options)
    {
        if (!$this instanceof Bvb_Grid_Deploy_DeployInterface) {
            throw new Bvb_Grid_Exception(get_class($this) . ' needs to implment Bvb_Grid_Deploy_DeployInterface');
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            throw new Bvb_Grid_Exception('options must be an instance from Zend_Config or an array');
        }

        $this->_options = $options;

        //Get the controller params and baseurl to use with filters
        $this->setParams(Zend_Controller_Front::getInstance()->getRequest()
            ->getParams());
        $this->_baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

        foreach (array('massActionsAll_', 'gridAction_', 'send_') as $value) {
            $this->removeParam($value);
        }

        foreach (Zend_Controller_Front::getInstance()->getRequest()
            ->getParams() as $key => $value) {
            if (is_array($value)) {
                $this->removeParam($key);
            }
        }

        /**
         * plugins loaders
         */
        $this->_formatter = new Zend_Loader_PluginLoader();

        //Templates loading
        if (is_array($this->_export)) {
            foreach ($this->_export as $key => $temp) {
                if (is_array($temp)) {
                    $export = $key;
                } else {
                    $export = $temp;
                }
                $this->_templates[$export] = new Zend_Loader_PluginLoader(array());
            }
        }

        // Add the formatter fir for fields content
        $this->addFormatterDir('Bvb/Grid/Formatter', 'Bvb_Grid_Formatter');

        $deploy = explode('_', get_class($this));
        $this->_deployName = strtolower(end($deploy));

        $renderDir = ucfirst($this->_deployName);

        $this->_filtersRenders = new Zend_Loader_PluginLoader();
        $this->addFiltersRenderDir('Bvb/Grid/Filters/Render/' . $renderDir, 'Bvb_Grid_Filters_Render_' . $renderDir);
    }

    public function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }

    /**
     * Set view object
     *
     * @param Zend_View_Interface $view view object to use
     *
     * @return Bvb_Grid
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

    /**
     * Sets the functions to be used to apply to each value
     * before display
     * @param array $functions
     */
    public function setDefaultEscapeFunction($functions)
    {
        $this->_escapeFunction = $functions;
        return $this;
    }

    /**
     * Returns the active escape functions
     */
    public function getDefaultEscapeFunction()
    {
        return $this->_escapeFunction;
    }

    /**
     * Character encoding
     *
     * @param string $encoding
     * @return unknown
     */
    public function setcharEncoding($encoding)
    {
        $this->_charEncoding = $encoding;
        return $this;
    }

    /**
     * Returns the actual encoding
     */
    public function getCharEncoding()
    {
        return $this->_charEncoding;
    }

    /**
     * The translator
     *
     * @param string $message
     * @return string
     */
    protected function __($message)
    {
        if (strlen($message) == 0) {
            return $message;
        }

        if ($this->getTranslator()) {
            return $this->getTranslator()
                ->translate($message);
        }

        return $message;
    }

    public function getTranslator()
    {
        return Bvb_Grid_Translator::getInstance()->getTranslator();
    }

    /**
     * Check if a string is available
     * @param unknown_type $message
     */
    protected function is__($message)
    {
        return Bvb_Grid_Translator::getInstance()->isTranslated($message);
    }

    /**
     * Use the overload function so we can return an object
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function __call($name, $value)
    {
        if (substr(strtolower($name), 0, 6) == 'source') {

            $meth = substr($name, 6);
            $meth[0] = strtolower($meth[0]);

            if (is_object($this->getSource()) && method_exists($this->getSource(), $meth)) {
                $this->getSource()
                    ->$meth();
                return $this;
            }
        }

        $class = $this->_deployName;

        if ($name == 'set' . ucfirst($class) . 'GridColumns') {
            $this->setGridColumns($value[0]);
            return $this;
        }

        if ($name == 'set' . ucfirst($class) . 'DetailColumns') {
            $this->setDetailColumns($value[0]);
            return $this;
        }

        if (substr(strtolower($name), 0, strlen($class) + 3) == 'set' . $class) {
            $name = substr($name, strlen($class) + 3);
            $name[0] = strtolower($name[0]);
            $this->_deploy[$name] = $value[0];
            return $this;
        }

        if (substr(strtolower($name), 0, 3) == 'set') {
            $name = substr($name, 3);

            if (!isset($value[0])) {
                $value[0] = null;
            }
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
    public function __set($var, $value)
    {
        $var[0] = strtolower($var[0]);
        $this->_info[$var] = $value;
        return $this;
    }

    /**
     * Update data from a column
     *
     * @param string $field
     * @param array $options
     * @return self
     */
    public function updateColumn($field, $options = array())
    {
        if (null == $this->getSource() || ($this->_allFieldsAdded == true && !array_key_exists($field, $this->_data['fields']))) {
            /**
             * Add to the queue and call it from the getFieldsFromQuery() method
             * @var $_updateColumnQueue Bvb_Grid
             */
            if (isset($this->_updateColumnQueue[$field])) {
                $this->_updateColumnQueue[$field] = array_merge($this->_updateColumnQueue[$field], $options);
            } else {
                $this->_updateColumnQueue[$field] = $options;
            }

            return $this;
        }

        if ($this->_allFieldsAdded == false) {
            $this->_data['fields'][$field] = $options;
        } elseif (array_key_exists($field, $this->_data['fields'])) {

            if (isset($options['hRow']) && $options['hRow'] == 1) {
                $this->_fieldHorizontalRow = $field;
                $this->_info['hRow'] = array('field' => $field, 'title' => $options['title']);
            }

            $this->_data['fields'][$field] = array_merge($this->_data['fields'][$field], $options);
        }

        return $this;
    }

    /**
     * Set option hidden=1 on several columns
     * @param $columns
     */
    public function setColumnsHidden(array $columns)
    {
        foreach ($columns as $column) {
            $this->updateColumn($column, array('hidden' => 1));
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
    public function addFormatterDir($dir, $prefix)
    {
        $this->_formatter
            ->addPrefixPath(trim($prefix, "_"), trim($dir, "/") . '/');
        return $this;
    }

    /**
     * Format a field
     *
     * @param  $value
     * @param  $formatter
     * @return formatted value
     */
    protected function _applyFormat($value, $formatter)
    {
        if (is_array($formatter)) {
            $result = reset($formatter);
            if (!isset($formatter[1])) {
                $formatter[1] = array();
            }

            $options = (array) $formatter[1];
        } else {
            $result = $formatter;
            $options = array();
        }

        $class = $this->_formatter
            ->load($result);

        $t = new $class($options);

        if (!$t instanceof Bvb_Grid_Formatter_FormatterInterface) {
            throw new Bvb_Grid_Exception("$class must implement the Bvb_Grid_Formatter_FormatterInterface");
        }

        return $t->format($value);
    }

    /**
     * Number of records to show per page
     * @param $number
     */
    public function setPaginationInterval(array $pagination)
    {
        $this->_paginationOptions = $pagination;
        return $this;
    }

    /**
     * Number of records to show per page
     * @param $number
     */
    public function setNumberRecordsPerPage($number = 15)
    {
        trigger_error("setNumberRecordsPerPage() is deprecated, use setRecordsPerPage() instead. Function will be removed in later versions.", E_USER_DEPRECATED);
        $this->setRecordsPerPage($number);
        return $this;
    }

    /**
     * Number of records to show per page
     * @param $number
     */
    public function setRecordsPerPage($number = 15)
    {
        $this->_recordsPerPage = (int) $number;
        return $this;
    }

    /**
     * Default values for filters.
     * This will be applied before displaying. However the user can still remove them.
     * @param $filters
     */
    public function setDefaultFiltersValues(array $filters)
    {
        $this->_defaultFilters = $filters;
        return $this;
    }

    /**
     * Get filters values
     *
     * @return void
     */
    protected function _buildFiltersValues()
    {
        //Build an array to know filters values
        $filtersValues = array();
        $fields = $this->getFields();

        $filters = array();
        foreach ($this->_ctrlParams as $key => $value) {
            if (stripos($key, '[')) {
                $name = explode('[', $key);

                if (in_array($name[0] . $this->getGridId(), $fields)) {
                    $filters[$name[0] . $this->getGridId()][substr($name[1], 0, - 1)] = $value;
                }
            } else {
                if (in_array($key, $fields)) {
                    $filters[$key] = $value;
                } elseif (in_array(substr($key, 0, - strlen($this->getGridId())), $fields)) {
                    if ($this->getGridId() != '' && substr($key, - strlen($this->getGridId())) == $this->getGridId()) {
                        $key = substr($key, 0, - strlen($this->getGridId()));
                    }

                    $filters[$key] = $value;
                }
            }
        }

        if (count($filters) > 0) {
            foreach ($filters as $key => $value) {
                if (is_array($value)) {
                    $this->setParam($key, $value);
                }
            }

            $fieldsRaw = $this->_data['fields'];

            foreach ($filters as $key => $filter) {
                if (!is_array($filter) && (strlen($filter) == 0 || !in_array($key, $this->_fields))) {
                    unset($filters[$key]);
                } elseif (!is_array($filter)) {
                    if (isset($fieldsRaw[$key]['searchField'])) {
                        $key = $fieldsRaw[$key]['searchField'];
                    }

                    $oldFilter = $filter;
                    if (isset($this->_filters[$key]['transform']) && is_callable($this->_filters[$key]['transform'])) {
                        $filter = call_user_func($this->_filters[$key]['transform'], $filter);
                    }

                    if (isset($this->_filters[$key]['callback']) && is_array($this->_filters[$key]['callback'])) {
                        if (!is_callable($this->_filters[$key]['callback']['function'])) {
                            throw new Bvb_Grid_Exception($this->_filters[$key]['callback']['function'] . ' is not callable');
                        }

                        if (!isset($this->_filters[$key]['callback']['params']) || !is_array($this->_filters[$key]['callback']['params'])) {
                            $this->_filters[$key]['callback']['params'] = array();
                        }

                        $this->_filters[$key]['callback']['params'] = array_merge($this->_filters[$key]['callback']['params'], array('field' => $key, 'value' => $filter, 'select' => $this->getSource()
                            ->getSelectObject()));

                        $result = call_user_func($this->_filters[$key]['callback']['function'], $this->_filters[$key]['callback']['params']);

                    } elseif (isset($this->_data['fields'][$key]['search']) && is_array($this->_data['fields'][$key]['search']) && $this->_data['fields'][$key]['search']['fulltext'] == true) {
                        $this->getSource()
                            ->addFullTextSearch($filter, $this->_data['fields'][$key]);
                    } else {
                        $op = $this->getFilterOp($key, $filter);

                        $this->getSource()
                            ->addCondition($op['filter'], $op['op'], $this->_data['fields'][$key]);
                    }

                    $filtersValues[$key] = $oldFilter;
                }

                if (is_array($filter)) {
                    $render = $this->loadFilterRender($this->_filters[$key]['render']);

                    $render->setFieldName($key);

                    if ($render->hasConditions()) {
                        $cond = $render->getConditions();
                        $render->setSelect($this->getSource()
                            ->getSelectObject());

                        foreach ($filter as $nkey => $value) {
                            if (strlen($value) > 0) {
                                $oldValue = $value;
                                $value = $render->normalize($value, $nkey);
                                $this->getSource()
                                    ->addCondition($value, $cond[$nkey], $this->_data['fields'][$key]);
                                $filtersValues[$key][$nkey] = $oldValue;
                            }
                        }
                    } else {
                        $render->buildQuery($filter);
                    }
                }
            }
        }

        $this->_filtersValues = $filtersValues;

        $this->_applyExternalFilters();

        if (count($this->_filtersValues) > 0 && $this->_paramsInSession === true) {
            $this->_sessionParams->filters = $this->_filtersValues;
        }

        return $this;
    }

    protected function _applyExternalFilters()
    {
        if (count($this->_externalFilters) == 0)
            return false;

        foreach ($this->_externalFilters as $id => $callback) {
            if ($this->getParam($id))
                call_user_func_array($callback, array($id, $this->getParam($id), $this->getSelect()));

            if ($this->getParam($id))
                $this->_filtersValues[$id] = $this->getParam($id);
        }
    }

    /**
     * Returns the operand to be used in filters
     * This value comes from the user input
     * but can be override
     * @param $field
     * @param $filter
     */
    public function getFilterOp($field, $filter)
    {
        if (!isset($this->_data['fields'][$field]['searchType'])) {
            $this->_data['fields'][$field]['searchType'] = 'like';
        }

        $op = strtolower($this->_data['fields'][$field]['searchType']);

        if (substr(strtoupper($filter), 0, 2) == 'R:') {
            $op = 'REGEX';
            $filter = substr($filter, 2);
        } elseif (strpos($filter, '<>') !== false && substr($filter, 0, 2) != '<>') {
            $op = 'range';
        } elseif (substr($filter, 0, 1) == '=') {
            $op = '=';
            $filter = substr($filter, 1);
        } elseif (substr($filter, 0, 2) == '>=') {
            $op = '>=';
            $filter = substr($filter, 2);
        } elseif ($filter[0] == '>') {
            $op = '>';
            $filter = substr($filter, 1);
        } elseif (substr($filter, 0, 2) == '<=') {
            $op = '<=';
            $filter = substr($filter, 2);
        } elseif (substr($filter, 0, 2) == '<>' || substr($filter, 0, 2) == '!=') {
            $op = '<>';
            $filter = substr($filter, 2);
        } elseif ($filter[0] == '<') {
            $op = '<';
            $filter = substr($filter, 1);
        } elseif ($filter[0] == '*' and substr($filter, - 1) == '*') {
            $op = 'like';
            $filter = substr($filter, 1, - 1);
        } elseif ($filter[0] == '*' and substr($filter, - 1) != '*') {
            $op = 'llike';
            $filter = substr($filter, 1);
        } elseif ($filter[0] != '*' and substr($filter, - 1) == '*') {
            $op = 'rlike';
            $filter = substr($filter, 0, - 1);
        } elseif (stripos($filter, ',') !== false) {
            $op = 'IN';
        }

        if (isset($this->_data['fields']['searchTypeFixed']) && $this->_data['fields']['searchTypeFixed'] === true && $op != $this->_data['fields']['searchType']) {
            $op = $this->_data['fields']['searchType'];
        }

        return array('op' => $op, 'filter' => $filter);
    }

    /**
     * Build query.
     *
     * @return string
     */
    protected function _buildQueryOrderAndLimit()
    {
        $start = (int) $this->getParam('start');
        $order = $this->getParam('order');
        $order1 = explode("_", $order);
        $orderf = strtoupper(end($order1));

        if ($this->_paramsInSession === true) {
            if ($this->getParam('start') === false) {
                $start = (int) $this->_sessionParams->start;
                $this->setParam('start' . $this->getGridId(), $start);
            } else {
                $this->_sessionParams->start = $start;
            }
        }

        if ($orderf == 'DESC' || $orderf == 'ASC' || ($this->_paramsInSession === true && is_array($this->_sessionParams->order))) {
            array_pop($order1);
            $order_field = implode("_", $order1);

            #$this->getSource()->buildQueryOrder($order_field, $orderf);

            if ($this->_paramsInSession === true) {
                if ($this->getParam('noOrder')) {
                    $this->_sessionParams->order = null;
                }

                if (is_array($this->_sessionParams->order) && !$this->getParam('order')) {
                    $order_field = $this->_sessionParams->order['field'];
                    $orderf = $this->_sessionParams->order['order'];
                    $this->setParam('order' . $this->getGridId(), $order_field . '_' . $orderf);
                }
            }

            if (in_array($order_field, $this->_fieldsOrder)) {
                $this->getSource()
                    ->buildQueryOrder($order_field, $orderf, true);

                if ($this->_paramsInSession === true) {
                    $this->_sessionParams->order = array('field' => $order_field, 'order' => $orderf);
                }
            }

        }

        if (strlen($this->_fieldHorizontalRow) > 0) {
            $this->getSource()
                ->buildQueryOrder($this->_fieldHorizontalRow, 'ASC', true);
        }

        if (false === $this->_forceLimit) {
            $this->getSource()
                ->buildQueryLimit($this->getResultsPerPage(), $start);
        }

        return true;
    }

    /**
     * Returns the number of records to show per page
     */
    public function getResultsPerPage()
    {
        $perPage = (int) $this->getParam('perPage', 0);

        if ($this->_paramsInSession === true && $this->getParam('perPage') === false) {
            $perPage = (int) $this->_sessionParams->perPage;
            $this->setParam('perPage' . $this->getGridId(), $perPage);
        }

        if ($perPage > 0 && array_key_exists($perPage, $this->_paginationOptions)) {
            if ($this->_paramsInSession === true) {
                $this->_sessionParams->perPage = $perPage;
            }
            return $perPage;
        } else {
            if ($this->_paramsInSession === true) {
                $this->_sessionParams->perPage = $this->_recordsPerPage;
            }
            return $this->_recordsPerPage;
        }
    }

    /**
     * Returns the url, without the param(s) specified
     *
     * @param array|string $situation
     * @return string
     */
    public function getUrl($situation = '', $allowAjax = true)
    {
        $situation = (array) $situation;

        //this array the a list of params that name changes
        //based on grid id. The id is prepended to the name
        $paramsGet = array('perPage', 'order', 'start', 'filters', 'noFilters', '_exportTo', 'add', 'edit', 'noOrder', 'comm', 'gridDetail', 'gridRemove');

        $params = $this->getAllParams();

        if (in_array('filters', $situation)) {
            $fields = array_merge($this->getFields(), array_keys($this->_externalFilters));

            foreach ($fields as $field) {
                if (isset($params[$field . $this->getGridId()])) {
                    unset($params[$field . $this->getGridId()]);
                }
            }

            foreach ($params as $key => $value) {
                if (stripos($key, '[')) {
                    $fl = explode('[', $key);
                    if (in_array(rtrim($fl[0], $this->getGridId()), $fields)) {
                        unset($params[rtrim($fl[0], $this->getGridId()) . '[' . $fl[1]]);
                    }
                }
            }
        }

        foreach ($situation as $value) {
            if (in_array($value, $paramsGet)) {
                $value = $value . $this->getGridId();
            }
            unset($params[$value]);
        }

        $params_clean = $params;
        unset($params_clean['controller']);
        unset($params_clean['module']);
        unset($params_clean['action']);
        unset($params_clean['gridmod']);

        if (is_array($this->_filters)) {
            foreach ($this->_filters as $key => $value) {
                if (is_array($key) && isset($key['render'])) {
                    unset($params_clean[$key]);
                }
            }
        }

        $url = '';
        foreach ($params_clean as $key => $param) {
            if (is_array($param) || ($key == 'perPage' && $value == 0) || ($key == 'start' && $value == 0)) {
                continue;
            }

            $url .= "/" . $this->getView()
                ->escape($key) . "/" . $this->getView()
                ->escape($param);
        }

        $action = '';
        if (isset($params['action'])) {
            $action = "/" . $params['action'];
        }

        if ($this->getRouteUrl() !== false) {
            $finalUrl = $this->getRouteUrl();
        } else {
            $finalUrl = $params['module'] . "/" . $params['controller'] . $action;
        }

        // Remove the action e controller keys, they are not necessary (in fact they aren't part of url)
        if (array_key_exists('ajax', $this->_info) && $this->getInfo('ajax') !== false && $allowAjax == true) {
            return $finalUrl . $url . "/gridmod/ajax";
        } else {
            return $this->_baseUrl . "/" . $finalUrl . $url;
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
    public function getInfo($param, $default = false)
    {
        if (isset($this->_info[$param])) {
            return $this->_info[$param];
        } elseif (strpos($param, ',')) {
            $params = explode(',', $param);
            $param = array_map('trim', $params);

            $final = $this->_info;

            foreach ($params as $check) {
                if (!isset($final[$check])) {
                    return $default;
                }
                $final = $final[$check];
            }

            return $final;
        }

        return $default;
    }

    /**
     * Build Filters. If defined put the values
     * Also check if the user wants to hide a field
     *
     * @return string
     */
    protected function _buildFilters()
    {
        $return = array();
        if ($this->getInfo('noFilters') == 1) {
            return false;
        }

        $class = isset($this->_template['classes']['filter']) ? $this->_template['classes']['filter'] : '';

        $data = $this->_fields;

        foreach ($this->_extraFields as $key => $value) {
            if ($value['position'] == 'left') {
                $return[$key] = array('type' => 'extraField', 'class' => $class, 'position' => 'left');
            }
        }

        for ($i = 0; $i < count($data); $i++) {
            $nf = $this->_fields[$i];

            if (!isset($this->_data['fields'][$nf]['search'])) {
                $this->_data['fields'][$nf]['search'] = true;
            }

            if ($this->_displayField($nf)) {
                if (is_array($this->_filters) && array_key_exists($data[$i], $this->_filters) && $this->_data['fields'][$nf]['search'] != false) {
                    $return[] = array('type' => 'field', 'class' => $class, 'value' => isset($this->_filtersValues[$data[$i]]) ? $this->_filtersValues[$data[$i]] : '', 'field' => $data[$i]);
                } else {
                    $return[] = array('type' => 'field', 'class' => $class, 'field' => $data[$i]);
                }
            }
        }

        foreach ($this->_extraFields as $key => $value) {
            if ($value['position'] == 'right') {
                $return[$key] = array('type' => 'extraField', 'class' => $class, 'position' => 'right');
            }
        }

        return $return;
    }

    /**
     * Checks if a field should be displayed or is setted as 'remove'
     * @param string $field
     * @return bool
     */
    protected function _displayField($field)
    {
        if (!isset($this->_data['fields'][$field]['remove'])) {
            $this->_data['fields'][$field]['remove'] = false;
        }
        if (!isset($this->_data['fields'][$field]['hidden'])) {
            $this->_data['fields'][$field]['hidden'] = false;
        }

        if ($this->_data['fields'][$field]['remove'] == 0 && (($this->_data['fields'][$field]['hidden'] == 0) || ($this->_data['fields'][$field]['hidden'] == 1 && $this->_removeHiddenFields !== true))) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param array $fields
     * @return array
     */
    protected function _prepareReplace($fields)
    {
        return array_map(create_function('$value', 'return "{{{$value}}}";'), $fields);
    }

    /**
     * Build the titles with the order links (if wanted)
     *
     * @return string
     */
    protected function _buildTitles()
    {
        $return = array();
        $url = $this->getUrl(array('order', 'start', 'comm', 'noOrder'));

        foreach ($this->_extraFields as $key => $value) {
            if ($value['position'] == 'left') {
                $title = $return[$key] = array('type' => 'extraField', 'value' => $this->__(isset($value['title']) ? $value['title'] : $value['name']), 'position' => 'left');
            }
        }

        $titles = $this->_fields;

        if (!$this->getParam('noOrder')) {
            $selectOrder = $this->getSource()
                ->getSelectOrder();

            if (count($selectOrder) == 1) {
                $this->setParam('order' . $this->getGridId(), $selectOrder[0] . '_' . strtoupper($selectOrder[1]));
            }
        }

        for ($i = 0; $i < count($this->_fields); $i++) {
            if ($this->getParam('order')) {
                $explode = explode('_', $this->getParam('order'));
                $name = str_replace('_' . end($explode), '', $this->getParam('order'));
                $this->_order[$name] = strtoupper(end($explode)) == 'ASC' ? 'DESC' : 'ASC';
            }

            $fieldsToOrder = $this->_resetKeys($this->_data['fields']);

            if (isset($fieldsToOrder[$i]['orderField']) && strlen($fieldsToOrder[$i]['orderField']) > 0) {
                $orderFinal = $fieldsToOrder[$i]['orderField'];
            } else {
                $orderFinal = $titles[$i];
            }

            if (is_array($this->_order)) {
                $order = $orderFinal == key($this->_order) ? $this->_order[$orderFinal] : 'ASC';
            } else {
                $order = 'ASC';
            }

            if ($this->_displayField($titles[$i])) {
                $noOrder = $this->getInfo('noOrder') ? $this->getInfo('noOrder') : '';

                if ($noOrder == 1) {
                    $return[$titles[$i]] = array('type' => 'field', 'name' => $titles[$i], 'field' => $titles[$i], 'value' => ($this->is__($titles[$i])) ? $this->__($titles[$i]) : $this->__($this->_titles[$titles[$i]]));
                } else {
                    $return[$titles[$i]] = array('type' => 'field', 'name' => $titles[$i], 'field' => $orderFinal, 'simpleUrl' => $url, 'url' => "$url/order{$this->getGridId()}/{$orderFinal}_$order", 'value' => ($this->is__($titles[$i])) === true ? $this->__($titles[$i]) : $this->__($this->_titles[$titles[$i]]));
                }
            }
        }

        foreach ($this->_extraFields as $key => $value) {
            if ($value['position'] == 'right') {
                $return[$key] = array('type' => 'extraField', 'value' => $this->__(isset($value['title']) ? $value['title'] : $value['name']), 'position' => 'right');
            }
        }

        $this->_finalFields = $return;

        return $return;
    }

    /**
     * Replaces {{field}} for the actual field value
     * @param  $item
     * @param  $key
     * @param  $text
     */
    protected function _replaceSpecialTags(&$item, $key, $text)
    {
        $item = str_replace($text['find'], $text['replace'], $item);
    }

    /**
     * Applies the format option to a field
     * @param $new_value
     * @param $value
     * @param $search
     * @param $replace
     */
    protected function _applyFieldFormat($new_value, $value, $search, $replace)
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
    protected function _applyFieldCallback($new_value, $value, $search, $replace)
    {
        if (!is_callable($value['function'])) {
            throw new Bvb_Grid_Exception($value['function'] . ' not callable');
        }

        if (isset($value['params']) && is_array($value['params'])) {
            $toReplace = $value['params'];
            $toReplaceArray = array();
            $toReplaceObj = array();

            foreach ($toReplace as $key => $rep) {
                if (is_scalar($rep) || is_array($rep)) {
                    $toReplaceArray[$key] = $rep;
                } else {
                    $toReplaceObj[$key] = $rep;
                }
            }
        } else {
            return call_user_func($value['function']);
        }

        if (is_array($toReplace)) {
            array_walk_recursive($toReplaceArray, array($this, '_replaceSpecialTags'), array('find' => $search, 'replace' => $replace));
        }

        for ($i = 0; $i <= count($toReplace); $i ++) {
            if (isset($toReplaceArray[$i])) {
                $toReplace[$i] = $toReplaceArray[$i];
            } elseif (isset($toReplaceObj[$i])) {
                $toReplace[$i] = $toReplaceObj[$i];
            }
        }

        return call_user_func_array($value['function'], $toReplace);
    }

    /**
     * Applies the decorator to a fields
     * @param unknown_type $find
     * @param unknown_type $replace
     * @param unknown_type $value
     */
    protected function _applyFieldDecorator($find, $replace, $value)
    {
        return str_replace($find, $replace, $value);
    }

    /**
     * Applies escape functions to a field
     * @param  $value
     */
    protected function _applyFieldEscape($value)
    {
        if ($this->_escapeFunction === false) {
            return $value;
        }

        if (!is_callable($this->_escapeFunction)) {
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
    private function _escapeField($field, $new_value)
    {
        if (!isset($this->_data['fields'][$field]['escape'])) {
            $this->_data['fields'][$field]['escape'] = 1;
        }

        if (($this->_data['fields'][$field]['escape'] ? 1 : 0) == 0) {
            return $new_value;
        }

        if ($this->_data['fields'][$field]['escape'] == 1) {
            return $this->_applyFieldEscape($new_value);
        }

        if (!is_callable($this->_data['fields'][$field]['escape'])) {
            throw new Bvb_Grid_Exception($this->_data['fields'][$field]['escape'] . ' not callable');
        }

        return call_user_func($this->_data['fields'][$field]['escape'], $new_value);
    }

    /**
     * Applies the view helper to the field
     * @param  $new_value
     * @param  $value
     * @param  $search
     * @param  $replace
     */
    protected function _applyFieldHelper($new_value, $value, $search, $replace)
    {
        if (is_array($value)) {
            array_walk_recursive($value, array($this, '_replaceSpecialTags'), array('find' => $search, 'replace' => $replace));
        }

        $name = $value['name'];
        $t = $this->getView()
            ->getHelper($name);
        $re = new ReflectionMethod($t, $name);

        if (isset($value['params']) && is_array($value['params'])) {
            $new_value = $re->invokeArgs($t, $value['params']);
        } else {
            $new_value = $re->invoke($t);
        }

        return $new_value;
    }

    /**
     * The loop for the results.
     * Check the extra-fields,
     *
     * @return string
     */
    protected function _buildGrid()
    {
        $return = array();

        $search = $this->_prepareReplace($this->_fields);

        $fields = $this->_fields;

        $i = 0;

        $classConditional = array();
        foreach ($this->_result as $dados) {
            $outputToReplace = array();
            foreach ($fields as $field) {
                $outputToReplace[$field] = $dados[$field];
            }

            if ($this->_deployName == 'table') {
                $this->_classRowConditionResult[$i] = '';
                if (isset($this->_classRowCondition[0]) && is_array($this->_classRowCondition[0])) {
                    foreach ($this->_classRowCondition as $key => $value) {
                        $cond = str_replace($search, $outputToReplace, $value['condition']);
                        $final = call_user_func(create_function('', "if($cond){return true;}else{return false;}"));
                        $this->_classRowConditionResult[$i] .= $final == true ? $value['class'] . ' ' : $value['else'] . ' ';
                    }
                }

                $this->_classRowConditionResult[$i] .= ($i % 2) ? $this->_cssClasses['even'] : $this->_cssClasses['odd'];

                if (count($this->_classCellCondition) > 0) {
                    foreach ($this->_classCellCondition as $key => $value) {
                        $classConditional[$key] = '';
                        foreach ($value as $condFinal) {
                            $cond = str_replace($search, $outputToReplace, $condFinal['condition']);
                            $final = call_user_func(create_function('', "if($cond){return true;}else{return false;}"));
                            $classConditional[$key] .= $final == true ? $condFinal['class'] . ' ' : $condFinal['else'] . ' ';
                        }
                    }
                }
            }

            /**
             *Deal with extrafield from the left
             */
            foreach ($this->_getExtraFields('left') as $value) {
                $value['class'] = !isset($value['class']) ? '' : $value['class'];
                $value['style'] = !isset($value['style']) ? '' : $value['style'];

                $new_value = '';

                if (isset($value['format'])) {
                    $new_value = $this->_applyFieldFormat($new_value, $value['format'], $search, $outputToReplace);
                }

                if (isset($value['callback']['function'])) {
                    $new_value = $this->_applyFieldCallback($new_value, $value['callback'], $search, $outputToReplace);
                }

                if (isset($value['helper'])) {
                    $new_value = $this->_applyFieldHelper($new_value, $value['helper'], $search, $outputToReplace);
                }

                if (isset($value['decorator'])) {
                    $new_value = $this->_applyFieldDecorator($search, $outputToReplace, $value['decorator']);
                }

                $return[$i][] = array('class' => $value['class'], 'value' => $new_value, 'style' => $value['style']);
            }

            /**
             * Deal with the grid itself
             */
            foreach ($fields as $field) {
                $new_value = $this->_escapeField($field, $dados[$field]);

                if (isset($this->_data['fields'][$field]['callback']['function'])) {
                    $new_value = $this->_applyFieldCallback($new_value, $this->_data['fields'][$field]['callback'], $search, $outputToReplace);
                    $outputToReplace[$field] = $new_value;
                }

                if (isset($this->_data['fields'][$field]['format'])) {
                    $new_value = $this->_applyFieldFormat($new_value, $this->_data['fields'][$field]['format'], $search, $outputToReplace);
                    $outputToReplace[$field] = $new_value;
                }

                if (isset($this->_data['fields'][$field]['helper'])) {
                    $new_value = $this->_applyFieldHelper($new_value, $this->_data['fields'][$field]['helper'], $search, $outputToReplace);
                    $outputToReplace[$field] = $new_value;
                }

                if (isset($this->_data['fields'][$field]['decorator'])) {
                    $new_value = $this->_applyFieldDecorator($search, $outputToReplace, $this->_data['fields'][$field]['decorator']);
                }

                if ($this->_displayField($field)) {
                    if (isset($this->_data['fields'][$field]['translate']) && $this->_data['fields'][$field]['translate'] == true) {
                        $new_value = $this->__($new_value);
                    }

                    $style = !isset($this->_data['fields'][$field]['style']) ? '' : $this->_data['fields'][$field]['style'];
                    $fieldClass = isset($this->_data['fields'][$field]['class']) ? $this->_data['fields'][$field]['class'] : '';
                    $finalClassConditional = isset($classConditional[$field]) ? $classConditional[$field] : '';

                    $return[$i][] = array('class' => $fieldClass . ' ' . $finalClassConditional, 'value' => $new_value, 'field' => $field, 'style' => $style);
                }
            }

            /**
             * Deal with extra fields from the right
             */

            //Reset the value. This is an extra field.
            $new_value = null;
            foreach ($this->_getExtraFields('right') as $value) {
                $value['class'] = !isset($value['class']) ? '' : $value['class'];
                $value['style'] = !isset($value['style']) ? '' : $value['style'];

                if (isset($value['callback']['function'])) {
                    $new_value = $this->_applyFieldCallback($new_value, $value['callback'], $search, $outputToReplace);
                }

                if (isset($value['format'])) {
                    $new_value = $this->_applyFieldFormat($new_value, $value['format'], $search, $outputToReplace);
                }

                if (isset($value['helper'])) {
                    $new_value = $this->_applyFieldHelper($new_value, $value['helper'], $search, $outputToReplace);
                }

                if (isset($value['decorator'])) {
                    $new_value = $this->_applyFieldDecorator($search, $outputToReplace, $value['decorator']);
                }

                $return[$i][] = array('class' => $value['class'], 'value' => $new_value, 'style' => $value['style']);
            }
            $i++;
        }

        return $return;
    }

    /**
     * Get the extra fields for a give position
     *
     * @param string $position
     * @return array
     */
    protected function _getExtraFields($position = 'left')
    {
        if (!is_array($this->_extraFields)) {
            return array();
        }

        $final = array();

        foreach ($this->_extraFields as $value) {
            if ($value['position'] == $position) {
                $final[] = $value;
            }
        }

        return $final;
    }

    /**
     * Reset keys indexes
     * @param unknown_type $array
     * @return unknown
     */
    protected function _resetKeys(array $array)
    {
        return array_values($array);
    }

    /**
     * Apply SQL Functions
     */
    protected function _buildSqlExp($where = array())
    {
        $final = $this->getInfo('sqlexp') ? $this->getInfo('sqlexp') : '';

        if (!is_array($final)) {
            return false;
        }

        $result = array();
        foreach ($final as $key => $value) {
            if (!array_key_exists($key, $this->_data['fields']))
                continue;

            if (!isset($value['value'])) {
                $value['value'] = $key;
            }

            $resultExp = $this->getSource()
                ->getSqlExp($value, $where);

            if (!isset($value['format']) && isset($this->_data['fields'][$key]['format'])) {
                $resultExp = $this->_applyFormat($resultExp, $this->_data['fields'][$key]['format']);
            } elseif (isset($value['format']) && strlen(isset($value['format'])) > 2 && false !== $value['format']) {
                $resultExp = $this->_applyFormat($resultExp, $value['format']);
            }

            $result[$key] = $resultExp;
        }

        if (!$result)
            return array();

        $return = array();
        foreach ($this->_finalFields as $key => $value) {
            $class    = $this->getInfo("sqlexp,$key,class") ? ' ' . $this->getInfo("sqlexp,$key,class") : '';
            $value    = (array_key_exists($key, $result)) ? $result[$key] : '';
            $return[] = array('class' => $class, 'value' => $value, 'field' => $key);
        }
        return $return;
    }

    /**
     * Make sure the fields exists on the database, if not remove them from the array
     *
     * @param array $fields
     */
    protected function _validateFields(array $fields)
    {
        $hidden = array();
        $show = array();
        foreach ($fields as $key => $value) {
            if (!isset($value['order']) || $value['order'] == 1) {
                if (isset($value['orderField'])) {
                    $orderFields[$key] = $value['orderField'];
                } else {
                    $orderFields[$key] = $key;
                }
            }

            if (isset($value['title'])) {
                $titulos[$key] = $value['title'];
            } else {
                $titulos[$key] = ucwords(str_replace('_', ' ', $key));
            }

            if (isset($this->_data['fields'][$key]['hidden']) && $this->_data['fields'][$key]['hidden'] == 1) {
                $hidden[$key] = $key;
            } else {
                $show[$key] = $key;
            }
        }

        $fields_final = array();
        $lastIndex = 1;
        $norder = 0;
        foreach ($show as $key => $value) {
            $value = $this->_data['fields'][$value];

            if (isset($value['position']) && (!isset($value['hidden']) || $value['hidden'] == 0)) {
                if ($value['position'] == 'last') {
                    $fields_final[($lastIndex + 100)] = $key;
                } elseif ($value['position'] == 'first') {
                    $fields_final[($lastIndex - 100)] = $key;
                } else {
                    if ($value['position'] == 'next') {
                        $norder = $lastIndex + 1;
                    } else {
                        $norder = (int) $value['position'];
                    }

                    if (array_key_exists($norder, $fields_final)) {
                        for ($i = count($fields_final); $i >= $norder; $i --) {
                            $fields_final[($i + 1)] = $fields_final[$i];
                        }
                        $fields_final[$norder] = $key;
                    }

                    $fields_final[$norder] = $key;
                }

            } elseif (!isset($value['hidden']) || $value['hidden'] == 0) {
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

        $fields_final = $this->_resetKeys($fields_final);

        //Put the hidden fields on the end of the array
        foreach ($hidden as $value) {
            $fields_final[] = $value;
        }

        $this->_fields = $fields_final;
        $this->_titles = $titulos;
        $this->_fieldsOrder = $orderFields;
    }

    /**
     * Make sure the filters exists, they are the name from the table field.
     * If not, remove them from the array
     * If we get an empty array, we then create a new one with all the fields specified
     * in $this->_fields method
     *
     * @param string $filters
     */
    protected function _validateFilters()
    {
        if ($this->getInfo("noFilters") == 1) {
            return false;
        }

        $filters = null;

        if (is_array($this->_filters)) {
            return $this->_filters;
        } else {
            $filters = array_combine($this->_fields, $this->_fields);
        }

        return $filters;
    }

    public function hasFilters()
    {
        if (count(array_intersect_key(array_combine($this->getFields(), $this->getFields()), $this->_ctrlParams)) > 0)
            return true;

        return false;
    }

    /**
     * Build user defined filters
     */
    protected function _buildDefaultFiltersValues()
    {
        if ($this->_paramsInSession === true) {
            if ($this->getParam('noFilters')) {
                $this->_sessionParams->filters = null;
            }
        }

        if ((is_array($this->_defaultFilters) || $this->_paramsInSession === true) && !$this->hasFilters() && !$this->getParam('noFilters')) {
            foreach ($this->_data['fields'] as $key => $value) {
                if (!$this->_displayField($key)) {
                    continue;
                }

                if ($this->_paramsInSession === true) {
                    if (isset($this->_sessionParams->filters[$key])) {
                        if (is_array($this->_sessionParams->filters[$key])) {
                            foreach ($this->_sessionParams->filters[$key] as $skey => $svalue) {
                                if (!isset($this->_ctrlParams[$key . $this->getGridId() . '[' . $skey . ']'])) {
                                    $this->_ctrlParams[$key . $this->getGridId() . '[' . $skey . ']'] = $svalue;
                                }
                            }
                        } else {
                            $this->_ctrlParams[$key . $this->getGridId()] = $this->_sessionParams->filters[$key];
                        }
                        continue;
                    }
                }

                if (is_array($this->_defaultFilters) && array_key_exists($key, $this->_defaultFilters)) {
                    $this->_ctrlParams[$key] = $this->_defaultFilters[$key];
                }
            }
        }

        return $this;
    }

    /**
     * Done. Send the grid to the user
     *
     * @return string
     */
    public function deploy()
    {
        if ($this->getSource() === null) {
            throw new Bvb_Grid_Exception('Please specify your source');
        }

        if ($this->_paramsInSession === true) {
            $this->_sessionParams = new Zend_Session_Namespace('ZFDG_FILTERS' . $this->getGridId(true));
        }

        //Disable ajax for CRUD operations
        if (!is_null($this->_crud)) {
            $this->setAjax(false);
        }

        //Add columns in queue
        foreach ($this->_updateColumnQueue as $field => $options) {
            $this->updateColumn($field, $options);
        }

        // apply additional configuration
        $this->_runConfigCallbacks();

        if ($this->getParam('gridDetail') == 1 && $this->_deployName == 'table' && (is_array($this->_detailColumns) || $this->getParam('gridRemove'))) {
            $this->_isDetail = true;
        }

        if ($this->_isDetail === true && is_array($this->_detailColumns)) {
            if (count($this->_detailColumns) > 0) {
                $finalColumns = array_intersect($this->_detailColumns, array_keys($this->_data['fields']));

                foreach ($this->_data['fields'] as $key => $value) {
                    if (!in_array($key, $finalColumns)) {
                        $this->updateColumn($key, array('remove' => 1));
                    }
                }
            }
        }

        if ($this->_isDetail === false && is_array($this->_gridColumns)) {
            $finalColumns = array_intersect($this->_gridColumns, array_keys($this->_data['fields']));
            foreach ($this->_data['fields'] as $key => $value) {
                if (!in_array($key, $finalColumns)) {
                    $this->updateColumn($key, array('remove' => 1));
                }
            }

            foreach (array_keys($this->_extraFields) as $value) {
                if ($value == 'ZFG_MASS_ACTIONS') continue;

                if (!in_array($value, $this->_gridColumns)) {
                    unset($this->_extraFields[$value]);
                }
            }
        }

        if ($this->_isDetail == true) {
            $result = $this->getSource()
                ->fetchDetail($this->getPkFromUrl());
            if ($result == false) {
                $this->_gridSession->message = $this->__('Record Not Found');
                $this->_gridSession->_noForm = 1;
                $this->_gridSession->correct = 1;
                $this->_redirect($this->getUrl(array('comm', 'gridDetail', 'gridRemove')));
            }
        }

        if (count($this->getSource()->getSelectOrder()) == 1 && !$this->getParam('order')) {
            $norder = $this->getSource()->getSelectOrder();

            if (!$norder instanceof Zend_Db_Expr) {
                $this->setParam('order' . $this->getGridId(), $norder[0] . '_' . strtoupper($norder[1]));
            }
        }

        $this->_buildDefaultFiltersValues();

        // Validate table fields, make sure they exist...
        $this->_validateFields($this->_data['fields']);

        // Filters. Not required that every field as filter.
        $this->_filters = $this->_validateFilters($this->_filters);

        $this->_buildFiltersValues();

        if ($this->_isDetail == false) {
            $this->_buildQueryOrderAndLimit();
        }

        if ($this->getParam('noOrder') == 1) {
            $this->getSource()
                ->resetOrder();
        }

        $result = $this->getSource()
            ->execute();

        if ($this->_forceLimit === false) {
            $resultCount = $this->getSource()
                ->getTotalRecords();
        } else {
            $resultCount = $this->_forceLimit;
            if (count($result) < $resultCount) {
                $resultCount = count($result);
            }
        }

        //Total records found
        $this->_totalRecords = $resultCount;

        //The result
        $this->_result = $result;

        $this->_colspan();
        return $this;
    }

    /**
     * Get details about a column
     *
     * @param string $column
     * @return null|array
     */
    protected function _getColumn($column)
    {
        return isset($this->_data['fields'][$column]) ? $this->_data['fields'][$column] : null;
    }

    /**
     *Convert Object to Array
     * @param object $object
     * @return array
     */
    protected function _object2array($data)
    {
        if (!is_object($data) && !is_array($data))
            return $data;

        if (is_object($data))
            $data = get_object_vars($data);

        return array_map(array($this, '_object2array'), $data);
    }

    /**
     * set template locations
     *
     * @param string $path
     * @param string $prefix
     * @return unknown
     */
    public function addTemplateDir($dir, $prefix, $type)
    {
        if (!isset($this->_templates[$type])) {
            $this->_templates[$type] = new Zend_Loader_PluginLoader();
        }

        $this->_templates[$type]
            ->addPrefixPath(trim($prefix, "_"), trim($dir, "/") . '/', $type);
        return $this;
    }

    /**
     * Define the template to be used
     *
     * @param string $template
     * @return unknown
     */
    public function setTemplate($template, $output = 'table', $options = array())
    {
        $tmp = $options;
        $options['userDefined'] = $tmp;

        $class = $this->_templates[$output]
            ->load($template, $output);

        if (isset($this->_options['template'][$output][$template])) {
            $tpOptions = array_merge($this->_options['template'][$output][$template], $options);
        } else {
            $tpOptions = $options;
        }

        $tpInfo = array('colspan' => $this->_colspan, 'charEncoding' => $this->getCharEncoding(), 'name' => $template, 'dir' => $this->_templates[$output]
            ->getClassPath($template, $output), 'class' => $this->_templates[$output]
            ->getClassName($template, $output));

        $this->_temp[$output] = new $class();

        $this->_temp[$output]->options = array_merge($tpInfo, $tpOptions);

        return $this->_temp[$output];
    }

    /**
     * Add multiple columns at once
     *
     */
    public function updateColumns()
    {
        $fields = func_get_args();

        foreach ($fields as $value) {

            if (!$value instanceof Bvb_Grid_Column) {
                throw new Bvb_Grid_Exception('Instance of Bvb_Grid_Column must be provided');
            }

            foreach ($value as $field) {
                $finalField = $field['field'];
                unset($field['field']);
                $this->updateColumn($finalField, $field);

            }
        }

        return;
    }

    /**
     * Calculate colspan for pagination and top
     *
     * @return int
     */
    protected function _colspan()
    {
        $totalFields = count($this->_fields);

        foreach ($this->_data['fields'] as $value) {
            if (isset($value['remove']) && $value['remove'] == 1) {
                $totalFields --;
            } elseif (isset($value['hidden']) && $value['hidden'] == 1 && $this->_removeHiddenFields === true) {
                $totalFields --;
            }

            if (isset($value['hRow']) && $value['hRow'] == 1) {
                $totalFields --;
            }
        }

        if ($this->getInfo("delete,allow") == 1) {
            $totalFields ++;
        }

        if ($this->getInfo("edit,allow") == 1) {
            $totalFields ++;
        }

        if (is_array($this->_detailColumns) && $this->_isDetail == false) {
            $totalFields ++;
        }

        $colspan = $totalFields + count($this->_extraFields);

        $this->_colspan = $colspan;

        return $colspan;
    }

    /**
     * Returns a field and is options
     * @param $field
     */
    public function getField($field)
    {
        return isset($this->_data['fields'][$field]) ? $this->_data['fields'][$field] : false;
    }

    /**
     *Return fields list.
     *Optional param returns also fields options
     * @param $returnOptions
     */
    public function getFields($returnOptions = false)
    {
        if (false !== $returnOptions) {
            return $this->_data['fields'];
        }

        return array_keys($this->_data['fields']);
    }

    /**
     * Add filters
     */
    public function addFilters($filters)
    {
        $filtersObj = $filters;

        $filters = $this->_object2array($filters);
        $filters = $filters['_filters'];

        foreach ($filtersObj->_filters as $key => $value) {
            if (isset($filters[$key]['callback'])) {
                $filters[$key]['callback'] = $value['callback'];
            }
            if (isset($filters[$key]['transform'])) {
                $filters[$key]['transform'] = $value['transform'];
            }
        }

        $this->_filters = $filters;

        foreach ($filters as $key => $filter) {
            if (isset($filter['searchType'])) {
                $this->updateColumn($key, array('searchType' => $filter['searchType']));
            }
        }

        $unspecifiedFields = array_diff($this->getFields(), array_keys($this->_filters));

        foreach ($unspecifiedFields as $value) {
            $this->updateColumn($value, array('search' => false));
        }

        return $this;
    }

    /**
     * Add extra columns
     *
     * @return unknown
     */
    public function addExtraColumns()
    {
        $extra_fields = func_get_args();

        if (is_array($this->_extraFields)) {
            $final = $this->_extraFields;
        } else {
            $final = array();
        }

        foreach ($extra_fields as $value) {
            if (!$value instanceof Bvb_Grid_Extra_Column) {
                throw new Bvb_Grid_Exception($value . ' must be na instance of Bvb_Grid_Extra_Column');
            }

            if (!isset($value->_field['name']) || !is_string($value->_field['name'])) {
                throw new Bvb_Grid_Exception('You need to define the column name');
            }

            if (isset($value->_field['title']) && !is_string($value->_field['title'])) {
                throw new Bvb_Grid_Exception('title option must be a string');
            }

            $final[$value->_field['name']] = $value->_field;

        }

        $this->_extraFields = $final;
        return $this;
    }

    /**
     * Returns the grid version
     * @return string
     */
    public static function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Return number records found
     */
    public function getTotalRecords()
    {
        return (int) $this->_totalRecords;
    }

    /**
     * Automates export functionality
     *
     * @param array|array of array $classCallbacks key should be lowercase, functions to call once before deploy() and ajax() functions
     * @param array|boolean $requestData request parameters will bu used if FALSE
     */
    public static function factory($defaultClass, $options = array(), $id = '', $classCallbacks = array(), $requestData = false)
    {
        if (!is_string($id)) {
            $id = "";
        }

        if (strpos($defaultClass, '_') === false) {
            $defaultClass = 'Bvb_Grid_Deploy_' . ucfirst(strtolower($defaultClass));
        }

        if (false === $requestData) {
            $requestData = Zend_Controller_Front::getInstance()->getRequest()
                ->getParams();
        }

        if (!isset($requestData['_exportTo' . $id])) {
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
            $grid->setGridId($id);
        }

        return $grid;
    }

    /**
     * Runs callbacks
     * @return
     */
    protected function _runConfigCallbacks()
    {
        if (!is_array($this->_configCallbacks)) {
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
    public function getExports()
    {
        $res = array();
        foreach ($this->_export as $name => $defs) {
            if (!is_array($defs)) {
                // only export name is passed, we need to get default option
                $name = $defs;
                $className = "Bvb_Grid_Deploy_" . ucfirst($name); // TODO support user defined classes

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
     * This is useful if the deploy class has no intention of using hidden fields
     * @param bool $value
     * @return $this
     */
    protected function _setRemoveHiddenFields($value)
    {
        $this->_removeHiddenFields = (bool) $value;
        return $this;
    }

    /**
     * Adds more options to the grid
     * @param $options
     */
    public function updateOptions($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            throw new Bvb_Grid_Exception('options must be an instance from Zend_Config or an array');
        }

        $this->_options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * Defines options to the grid
     * @param $options
     */
    public function setOptions($options)
    {
        $this->_options = array_merge($options, $this->_options);
        return $this;
    }

    /**
     * Apply the options to the fields
     */
    protected function _applyOptionsToFields()
    {
        if (isset($this->_options['fields']) && is_array($this->_options['fields'])) {
            foreach ($this->_options['fields'] as $field => $options) {
                if (isset($options['format']['function'])) {
                    if (!isset($options['format']['params'])) {
                        $options['format']['params'] = array();
                    }
                    $options['format'] = array($options['format']['function'], $options['format']['params']);
                }

                if (isset($options['callback'])) {
                    if (!isset($options['callback']['params'])) {
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
            if (method_exists($this, '_applyConfigOptions')) {
                $this->_applyConfigOptions($this->_options['deploy'][$name], true);
            } else {
                $this->_deploy = $this->_options['deploy'][$name];
            }
        }

        if (isset($this->_options['template'][$name]) && is_array($this->_options['template'][$name])) {
            $this->addTemplateParams($this->_options['template'][$name]);
        }

        if (isset($this->_options['grid']['formatter'])) {
            $this->_options['grid']['formatter'] = (array) $this->_options['grid']['formatter'];

            foreach ($this->_options['grid']['formatter'] as $formatter) {
                $temp = $formatter;
                $temp = str_replace('_', '/', $temp);
                $this->addFormatterDir($temp, $formatter);
            }
        }

        if (isset($this->_options['grid']['recordsPerPage'])) {
            $this->setRecordsPerPage($this->_options['grid']['recordsPerPage']);
        }
    }

    /**
     * Sets the grid id, to allow multiples instances per page
     * @param $id
     */
    public function setGridId($id)
    {
        $this->_gridId = trim(preg_replace("/[^a-zA-Z0-9_]/", '_', $id), '_');
        return $this;
    }

    /**
     * Returns the current id.
     * ""=>emty string is a valid value
     */
    public function getGridId($forceId = false)
    {
        if ($forceId === true && strlen($this->_gridId) == 0) {
            return $this->getRequest()
                ->getActionName() . '_' . $this->getRequest()
                ->getControllerName() . '_' . $this->getRequest()
                ->getModuleName();
        }
        return $this->_gridId;
    }

    /**
     *Set user defined params for templates.
     * @param array $options
     * @return unknown
     */
    public function setTemplateParams(array $options)
    {
        $this->_templateParams = $options;
        return $this;
    }

    /**
     * Set user defined params for templates.
     * @param $name
     * @param $value
     */
    public function addTemplateParam($name, $value)
    {
        $this->_templateParams[$name] = $value;
        return $this;
    }

    /**
     * Adds user defined params for templates.
     * @param array $options
     * @return $this
     */
    public function addTemplateParams(array $options)
    {
        $this->_templateParams = array_merge($this->_templateParams, $options);
        return $this;
    }

    /**
     * Returns template info defined by the user
     */
    public function getTemplateParams()
    {
        return $this->_templateParams;
    }

    /**
     * Reset options for column
     * @param string $column
     * @return self
     */
    public function resetColumn($column)
    {
        $support = array();
        $support[] = $this->_data['fields']['title'];
        $support[] = $this->_data['fields']['field'];
        $this->updateColumn($column, $support);
        return $this;
    }

    /**
     * Reset options for several columns
     * @param $columns
     */
    public function resetColumns(array $columns)
    {
        foreach ($columns as $column) {
            $this->resetColumn($column);
        }

        return $this;
    }

    /**
     * Defines which columns will be available to user
     * @param $columns
     */
    public function setGridColumns(array $columns)
    {
        $this->_gridColumns = $columns;
        return $this;
    }

    /**
     * Adds more columns to be showed
     * @param $columns
     */
    public function addGridColumns(array $columns)
    {
        $this->_gridColumns = array_merge($this->_gridColumns, $columns);
        return $this;
    }

    /**
     * Defines which columns will be available on detail view
     * @param $columns
     */
    public function setDetailColumns($columns = array())
    {
        $this->_detailColumns = $columns;
        return $this;
    }

    /**
     * Adds more columns that will be available on detail view
     * @param $columns
     */
    public function addDetailColumns(array $columns)
    {
        $this->_detailColumns = array_merge($this->_detailColumns, $columns);
        return $this;
    }

    /**
     * Get the list of primary keys from the URL
     *
     * @return string
     */
    public function getPkFromUrl()
    {
        if (!$this->getParam('comm')) {
            return array();
        }

        $param = $this->getParam('comm');
        $explode = explode(';', $param);
        $param = end($explode);
        $param = substr($param, 1, - 1);

        $paramF = explode('-', $param);
        $param = '';

        $returnArray = array();
        foreach ($paramF as $value) {
            $f = explode(':', $value);
            $returnArray[$f[0]] = $f[1];
        }
        return $returnArray;
    }

    /**
     * Let the user know what will be displayed.
     * @param $option (grid|form)
     * @return array|bool
     */
    public function willShow()
    {
        return $this->_willShow;
    }

    /**
     * Get a param from the $this->_ctrlParams appending the grid id
     * @param $param
     * @param $default
     */
    public function getParam($param, $default = false)
    {
        return isset($this->_ctrlParams[$param . $this->getGridId()]) ? $this->_ctrlParams[$param . $this->getGridId()] : $default;
    }

    /**
     * Returns all params received from Zend_Controller
     */
    public function getAllParams()
    {
        return $this->_ctrlParams;
    }

    /**
     * Redirects a user to a give URL and exits
     * @param string $url
     * @param int $code
     */
    protected function _redirect($url, $code = 302)
    {
        $response = Zend_Controller_Front::getInstance()->getResponse();
        $response->setRedirect($url, $code);
        $response->sendResponse();
        die();
    }

    /**
     * Set a param to be used by controller.
     *
     * @param $param
     * @param $value
     */
    public function setParam($param, $value)
    {
        $this->_ctrlParams[$param] = $value;
        return $this;
    }

    /**
     * Remove a param
     * @param $param
     */
    public function removeParam($param)
    {
        unset($this->_ctrlParams[$param]);
        return $this;
    }

    /**
     * Unsets all params received from controller
     */
    public function removeAllParams()
    {
        $this->_ctrlParams = array();
        return $this;
    }

    /**
     * Defines a new set of params
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->_ctrlParams = $params;
        return $this;
    }

    /**
     * Defines which export options are available
     * Ex: array('word','pdf');
     * @param array $export
     * @return Bvb_Grid
     */
    public function setExport(array $export)
    {
        $this->_export = $export;
        return $this;
    }

    /**
     * Returns the currently setted export methods
     * @return array
     */
    public function getExport()
    {
        return $this->_export;
    }

    /**
     * Defines SQL expressions
     * @param array $exp
     * @return Bvb_Grid
     */
    public function setSqlExp(array $exp)
    {
        $this->_info['sqlexp'] = $exp;
        return $this;
    }

    public function setRouteUrl($url)
    {
        $this->_routeUrl = (string) ltrim($url, '/');
        return $this;
    }

    public function getRouteUrl()
    {
        return $this->_routeUrl;
    }

    /**
     * @param $render
     */
    public function loadFilterRender($render)
    {
        if (is_array($render)) {
            $toRender = key($render);
        } else {
            $toRender = $render;
        }

        $classname = $this->_filtersRenders
            ->load(ucfirst($toRender));
        $class = new $classname();

        if (!$class instanceof Bvb_Grid_Filters_Render_RenderInterface) {
            throw new Bvb_Grid_Exception("$classname must implement Bvb_Grid_Filters_Render_RenderInterface");
        }

        if (is_array($render)) {
            $re = new ReflectionMethod($class, '__construct');
            $new_value = $re->invokeArgs($class, $render[$toRender]);
        }

        return $class;
    }

    public function addFiltersRenderDir($dir, $prefix)
    {
        $this->_filtersRenders
            ->addPrefixPath(trim($prefix, "_"), trim($dir, "/") . '/');
        return $this;
    }

    public function isExport()
    {
        return in_array($this->getParam('_exportTo'), $this->_export);
    }

    /**
     * @return Bvb_Grid_Source_Zend_Select
     */
    public function getSelect()
    {
        return $this->getSource()
            ->getSelectObject();
    }

    public function addExternalFilter($fieldId, $callback)
    {
        if (!is_callable($callback)) {
            throw new Bvb_Grid_Exception($callback . ' not callable');
        }

        $this->_externalFilters[$fieldId] = $callback;

        return $this;
    }

    public function clearExternalFilters()
    {
        $this->_externalFilters = array();
        return $this;
    }

    public function removeExternalFilter($fieldId)
    {
        if (isset($this->_externalFilters[$fieldId])) {
            unset($this->_externalFilters[$fieldId]);
        }

        return $this;
    }

    public function setShowFiltersInExport($show)
    {
        $this->_showFiltersInExport = $show;

        return $this;
    }

    /**
     * Whetever to save or not in session filters and order
     * This is based on gridId, if not provided, action_controller_module
     * @param bool $status
     */
    public function saveParamsInSession($status)
    {
        $this->_paramsInSession = (bool) $status;
        return $this;
    }

    /**
     * Defines options for deployment
     * @param array $options
     * @return $this
     */
    public function setDeployOptions(array $options)
    {
        foreach ($options as $option => $value) {
            $this->setDeployOption($option, $value);
        }
        return $this;
    }

    /**
     * Defines option for deployment
     * @param string $option
     * @param string $value
     * @return $this
     */
    public function setDeployOption($option, $value)
    {
        $this->_deploy[$option] = $value;
        return $this;
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getDeployOption($name, $default = null)
    {
        return (array_key_exists($name, $this->_deploy)) ? $this->_deploy[$name] : $default;
    }

    /**
     *Reset Deploy Options
     */
    public function clearDeployOptions()
    {
        $this->_deploy = array();
        return $this;
    }

    /**
     * retrieve deploy options
     */
    public function getDeployOptions()
    {
        return $this->_deploy;
    }

    public function hasMassActions()
    {
        return $this->_hasMassActions;
    }

    public function getMassActionsOptions()
    {
        if (!$this->_hasMassActions) {
            return array();
        }

        return (array) $this->_massActions;
    }

    public function setMassActions(array $options,array $fields)
    {
        $this->_hasMassActions = true;
        $this->_massActions = $options;
        $this->_massActionsFields = $fields;

        foreach ($options as $value) {
            if (!isset($value['url']) || !isset($value['caption'])) {
                throw new Bvb_Grid_Exception('Options url and caption are required for each action');
            }
        }

        if (count($fields)==0 &&  count($this->getSource()
            ->getPrimaryKey($this->_data['table'])) == 0) {
            throw new Bvb_Grid_Exception('No primary key defined in table. Mass actions not available');
        }

        $pk = '';
        foreach ($this->getSource()
            ->getPrimaryKey($this->_data['table']) as $value) {
            $aux = explode('.', $value);
            $pk .= end($aux) . '-';
        }

        return rtrim($pk, '-');
    }

    /**
     * Adds a new mass action option
     * @param $options
     */
    public function addMassActions(array $options, $fields = null)
    {
        if ($this->_hasMassActions !== true) {
            return $this->setMassActions($options, (array) $fields);
        }

        foreach ($options as $value) {
            if (!isset($value['url']) || !isset($value['caption'])) {
                throw new Bvb_Grid_Exception('Options url and caption are required for each action');
            }
        }

        $this->_massActions = array_merge($options, $this->_massActions);

        if (is_array($fields)) {
            $this->_massActionsFields = $fields;
        }

        return $this;
    }

    public function checkExportRights()
    {
        if (!in_array($this->_deployName, $this->_export) && !array_key_exists($this->_deployName, $this->_export)) {
            throw new Bvb_Grid_Exception($this->__("You don't have permission to export the results to this format"));
        }
    }

    /**
     * Build an array based on the given key name (why this never made it to PHP core I'll never know).
     *
     * @see http://www.php.net/manual/en/function.array-map.php#96269
     * @param array $array
     * @param string $key
     * @return array
     */
    public function arrayPluck($array, $key = 'value')
    {
        if (!is_array($array))
            return array();

        $result = array();
        foreach ($array as $row) {
            if (array_key_exists($key, $row))
                $result[] = $row[$key];
        }

        return $result;
    }
}