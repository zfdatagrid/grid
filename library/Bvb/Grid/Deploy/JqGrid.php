<?php
/**
 * ZFDatagrid
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
 * @package   Bvb_Grid
 * @copyright Copyright (c) XTmotion Limited (http://www.xtmotion.co.uk/)
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id$
 * @author    k2s (Martin Minka) <martin.minka@gmail.com>
 */

/** Zend_Json */
require_once 'Zend/Json.php';

/** Zend_Controller_Front */
require_once 'Zend/Controller/Front.php';

/**
 * Deploy class to render data with jqGrid library
 *
 * @package   Bvb_Grid
 * @author    k2s (Martin Minka) <martin.minka@gmail.com>
 * @copyright Copyright (c) 2010-2011 XTmotion Limited (http://www.xtmotion.co.uk/)
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id$
 */
class Bvb_Grid_Deploy_JqGrid extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{
    /**
     * URL path to place where JqGrid library resides
     *
     * @var string
     */
    public static $defaultJqGridLibPath = "public/scripts/jqgrid";

    /**
     * Code of locale file to use
     *
     * @var string
     */
    public static $defaultJqgI18n = "en";

    /**
     * Remember if we are already initialized
     *
     * @var boolean
     */
    protected $_jqInitialized = false;

    /**
     * URL path to place where JqGrid library resides
     *
     * @var string
     */
    public static $defaultActionClasses = array(
        '{edit}'   => 'ui-icon ui-icon-pencil',
        '{delete}' => 'ui-icon ui-icon-trash',
        '{view}'   => 'ui-icon ui-icon-search',
        '{add}'    => 'ui-icon ui-icon-plus',
    );

    /**
     * Track if setAjax() function was called
     *
     * @var boolean
     */
    private $_ajaxFuncCalled = false;

    /**
     * Trac if deploy() function was called
     *
     * @var boolean
     */
    private $_deployFuncCalled = false;

    /**
     * Default options for JqGrid
     *
     * @var array
     */
    protected $_jqgDefaultParams = array(
        'mtype' => 'POST', // GET will not work because of our parsing
        'height' => 'auto',
        'autowidth' => true,
        'rownumbers' => true,
        'gridview' => true,
        'multiselect' => false,
        'viewrecords' => true,
        'imgpath' => "themes/basic/images",
        'caption' => '',
        'loadError' => 'function(xhr,st,err) { if (xhr.status!=200) {alert(xhr.statusText);} }',
    );

    /**
     * Default options for columns
     *
     * @var array
     */
    protected $_jqgColDefaultParams = array(
        'title' => false
    );

    /**
     * Options defined for jqGrid object
     *
     * @see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:options
     *
     * @var array
     */
    private $_jqgParams = array();

    /**
     * Bvb_Grid_Deploy_JqGrid own options to apply
     *
     * @var array
     */
    private $_bvbParams = array();

    /**
     * @var bool
     */
    protected $_dataWithColNames = false;

    /**
     * Options to apply by navGrid
     * TODO maybe not needed
     * @var array
     */
    private $_navGridParams = array();

    // TODO rename this property ? It is Bvb related
    private $_jqgOnInit = array();

    /**
     * List of commands to execute after the jqGrid object is initiated
     *
     * @var array
     */
    private $_postCommands = array();

    /**
     * List of custon buttons to be shown on navigation bar
     *
     * @var array
     */
    private $_navButtons = array();

    /**
     * Constructor
     *
     * @param array $options configuration options
     */
    public function __construct (array $options = array())
    {
        $this->initLogger();

        parent::__construct($options);

        // TODO fix for property with same name in Bvb_Grid
        $this->_view = null;

        // prepare request parameters sent by jqGrid
        $this->clearParams();
        $this->convertRequestParams();
    }

    /**
     * Call this in controller (before any output) to dispatch Ajax requests.
     *
     * @param string $id ID to recognize the request from multiple tables ajax request will be ignored if FALSE
     *
     * @return void
     */
    public function setAjax($id='')
    {
        $this->setId($id);

        // apply additional configuration
        $this->runConfigCallbacks();

        // track that this function was called
        $this->_ajaxFuncCalled = true;
        // if request is Ajax we should only return data
        if (false!==$id && $this->isAjaxRequest() && isset($_GET['q']) && $id===$_GET['q']) {
            // prepare data
            parent::deploy();
            // set data in JSON format
            $response = Zend_Controller_Front::getInstance()->getResponse();
            if (!self::$debug) {
                $response->setHeader('Content-Type', 'application/json');
            }
            $response->setBody($this->renderPartData());
            // send logged messages to FirePHP
            Zend_Wildfire_Channel_HttpHeaders::getInstance()->flush();
            // send the response now and end request processing
            $response->sendResponse();
            exit;
        }
    }

    /**
     * Deprecated, use setAjax()
     *
     * @param string $id id
     *
     * @return void
     */
    public function ajax($id='')
    {
        trigger_error("Bvb_Grid_Deploy_JqGrid::ajax() is deprecated, use setAjax() instead. Function will be removed in later versions.", E_USER_DEPRECATED);
        $this->setAjax($id);
    }

    /**
     * Set jQuery Grid options (merging with old options)
     *
     * @param array $options set JqGrid options (@see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:options)
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function setJqgParams(array $options)
    {
        // TODO bad name, use the same as in ZendX_Jquery
        // TODO also dangerouse that it will call set functions for general Bvb class
        $this->_jqgParams = array(); //$this->_jqgDefaultParams;

        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'jqgSet' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            } else {
                $this->_jqgParams[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Set value to one parameter from jqGrid domain
     *
     * @param string $var   name of property to set with value
     * @param mixed  $value value
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function setJqgParam($var, $value)
    {
        $this->_jqgParams[$var] = $value;
        return $this;
    }

    /**
     * Return value of parameter from jqGrid domain
     *
     * @param string $var     variable name
     * @param mixed  $default value to return if option is not set
     *
     * @return mixed
     */
    public function getJqgParam($var, $default = null)
    {
        return isset($this->_jqgParams[$var]) ? $this->_jqgParams[$var] : $default;
    }

    /**
     * Set Bvb_Grid_Deploy_JqGrid own options (merging with old options)
     *
     * @param array $options set Bvb_Grid_Deploy_JqGrid
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function setBvbParams(array $options)
    {
        // TODO bad name, use the same as in ZendX_Jquery
        // TODO also dangerouse that it will call set functions for general Bvb class
        $this->_bvbParams = array(); //$this->_jqgDefaultParams;

        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'bvbSet' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            } else {
                $this->_bvbParams[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Set value to one parameter from Bvb_Grid_Deploy_JqGrid domain
     *
     * @param string $var   name of property to set with value
     * @param mixed  $value value
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function setBvbParam($var, $value)
    {
        $this->_bvbParams[$var] = $value;
        return $this;
    }

    /**
     * @param bool $addColumnNames
     * @return void
     */
    public function setBvbDataWithColNames($addColumnNames)
    {
        $this->_dataWithColNames = $addColumnNames;
    }

    public function getBvbDataWithColNames()
    {
        return $this->_dataWithColNames;
    }


    /**
     * Return value of parameter from Bvb_Grid_Deploy_JqGrid domain
     *
     * @param string $var     variable name
     * @param mixed  $default value to return if option is not set
     *
     * @return mixed
     */
    public function getBvbParam($var, $default = null)
    {
        return isset($this->_bvbParams[$var]) ? $this->_bvbParams[$var] : $default;
    }

    /**
     * Will add passed javascript code inside anonymouse function.
     *
     * Following variables are accessible in that function:
     * this  - jqgrid DOM object
     * grid  - jqGrid object
     *
     * @param string $javaScript javascript will be included into funcion
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function bvbSetOnInit($javaScript)
    {
        $this->_jqgOnInit[] = $javaScript;
        return $this;
    }

    /**
     * Removes all javascript code added by calls to setJqgOnInit()
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function bvbClearOnInit()
    {
        // TODO __call function in Bvb_Grid should ignore such call
        $this->_jqgOnInit = array();
        return $this;
    }

    /**
     * Add export action buttons to grid
     *
     * @param array $exports names of deploy classes
     *
     * @return void
     */
    protected function addExportButtons(array $exports)
    {
        $myUrl = 'x';
        foreach ($exports as $export=>$options) {
            $url = isset($options['url']) ?  $options['url'] : $myUrl;
            $newWindow = isset($options['newWindow']) ?  $options['newWindow'] : true;
            $this->jqgAddNavButton(
                array( // /public/images/csv.gif
                    'caption' => $options['caption'],
                    'buttonicon' => isset($options['cssClass']) ? $options['cssClass'] : "ui-icon-extlink",
                    'onClickButton' => isset($options['onClick'])
                        ? new Zend_Json_Expr($options['onClick'])
                        // TODO following JS function should be added as universal function if at least one exp. button
                        : new Zend_Json_Expr($this->getExportButtonJs($url, $newWindow, $export)),
                    'position' => "last"
                )
            );
        }
        return $this;
    }

    /**
     * Create javascript adding export button to grid navBar
     *
     * @param string  $url       url to action which supports generation of export
     * @param boolean $newWindow should the export be opened as new window
     * @param string  $exportTo  Bvb deploy class name used to generate export
     *
     * @return void
     */
    protected function getExportButtonJs($url, $newWindow, $exportTo)
    {

        $cmd1 = $this->cmd("getGridParam", "url");
        $cmd2 = $this->cmd("getGridParam", "postData");
        // TODO does not support sending of search/sort parameters yet
        $getUrl = <<<JS
var url = $cmd1;
var data = $cmd2;
var sdata = [];
for(var p in data)
    sdata.push(p + "=" + encodeURIComponent(data[p]));
url = url + "&_exportFrom=jqGrid&_exportTo=$exportTo&" + sdata.join("&");
JS;
        if ($newWindow) {
            return
<<<JS
function() {
    $getUrl
    newwindow = window.open(url);
    if (window.focus) {
        newwindow.focus();
    }
    return false;
}
JS;
        } else {
            return
<<<JS
function() {
    $getUrl
    location.href = url;
}
JS;
        }
    }

    /**
     * Build grid. Will output HTML definition for grid and add js/css to view.
     *
     * Use __toString() function to receive the result and place it in view where you want to display the grid.
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function deploy()
    {
        // check if setAjax() function was called
        if (!$this->_ajaxFuncCalled) {
            $this->log("setAjax() function was not called before deploy()", Zend_Log::WARN);
        }

        // it would not work correctly if deploy() is called more times
        if ($this->_deployFuncCalled) {
            return $this;
        }

        // prepare internal Bvb data
        parent::deploy();
        // prepare access to view
        $view = $this->getView();
        // defines ID property of html tags related to this jqGrid
        $id = $this->getId();

        // initialize jQuery
        $this->jqInit();
        // prepare options used to build jqGrid element
        $this->prepareOptions();
        // build definition of columns, which will manipulate _options
        $this->_jqgParams['colModel'] = $this->jqgGetColumnModel();
        // build final JavaScript code and return HTML code to display
        $this->jqAddOnLoad($this->renderPartJavascript());
        $this->_deploymentContent = $this->renderPartHtml();
        $this->_deployFuncCalled = true;

        return $this;
    }

    /**
     * Return javascript part of grid
     *
     * @return string
     */
    public function renderPartJavascript()
    {
        // ! this should be the last commands (it is not chainable anymore)
        foreach ($this->_navButtons as $btn) {
            $this->_postCommands[] = sprintf(
                'jqGrid("navButtonAdd", "#%s", %s)',
                $this->jqgGetIdPager(),
                self::encodeJson($btn)
            );
        }

        if ($this->_dataWithColNames) {
            $this->_jqgParams['jsonReader'] = array("repeatitems"=>false);
        }

        if (!$this->getBvbParam('firstDataAsLocal', true)) {
            // first data will be loaded via ajax call
            $data = array();
            $this->_jqgParams['datatype'] = "json";
        } else {
            // set first data without ajax request
            $data = $this->renderPartData();
            $this->_jqgParams['datatype'] = "local";
            $this->_postCommands[] = 'jqGrid("setGridParam", {datatype:"json"})';
            $this->_postCommands[] = 'jqGrid()[0].addJSONData('.$data.')';
        }
        // combine the post commands into JavaScrip string
        if (count($this->_postCommands)) {
            $postCommands = '.' . implode("\n.", $this->_postCommands);
        }
        // convert options to javascript
        $options = self::encodeJson($this->_jqgParams);
        // build javascript text
        $idtable = $this->jqgGetIdTable();
        $idpager = $this->jqgGetIdPager();
        $js = <<<EOF
jQuery("#$idtable").jqGrid(
$options
)
$postCommands
;
EOF;
        // add users javascript code (something like ready event)
        if (count($this->_jqgOnInit)>0) {
            $cmds = implode(PHP_EOL, $this->_jqgOnInit);
            $js .= PHP_EOL . <<<JS
jQuery("#$idtable").each(function () {
    var grid = jQuery(this).jqGrid();
    $cmds
});
JS;
        }
        return $js;
    }

    /**
     * Return html part of grid
     *
     * @return string
     */
    public function renderPartHtml()
    {
        $idtable = $this->jqgGetIdTable();
        $idpager = $this->jqgGetIdPager();
        $html = <<<HTML
<table id="$idtable">
    <tr><td></td></tr>
</table>
<div id="$idpager"></div>
HTML;
        return $html;
    }

    /**
     * Return data in JSON format
     *
     * @return string
     */
    public function renderPartData()
    {
        // clarify the values
        $page = $this->getParam('page'); // get the requested page
        $limit = $this->getRecordsPerPage(); // get how many rows we want to have into the grid
        $count =  $this->_totalRecords;
        // decide if we should pass PK as ID to each row
        $passPk = false;
        if (isset($this->_bvbParams['id']) && count($this->_result)>0) {
            $pkName = $this->_bvbParams['id'];
            if (isset($this->_result[0][$pkName])) {
                // only if that field exists
                $passPk = true;
            } else {
                $this->log(
                    "field '$pkName' defined as jqg>reader>id option does not exists in result set",
                    Zend_Log::WARN
                );
            }
        }

        // build rows
        $data = new stdClass();
        $data->rows = array();
        foreach (parent::_buildGrid() as $i=>$row) {
            $dataRow = new stdClass();
            // collect data for cells
            $d = array();
            foreach ( $row as $key=>$val ) {
                if ($this->_dataWithColNames) {
                    // add column names to data
                    $d[$val['field']] = $val['value'];
                } else {
                    // faster but you can't change order of columns
                    $d[] = $val['value'];
                }
            }
            if ($passPk) {
                // set PK to row
                // TODO works only if _buildGrid() results are in same order as $this->_result
                $dataRow->id = $this->_result[$i][$pkName];
            }
            if ($this->_dataWithColNames) {
                $data->rows[] = $d;
            } else {
                $dataRow->cell = $d;
                $data->rows[] = $dataRow;
            }
        }
        // set some other information
        if ($limit>0) {
            $totalPages = ceil($count/$limit);
        } else {
            $totalPages = 0;
        }
        $data->page = $page;
        $data->total = $totalPages;
        $data->records = $count;

        return Zend_Json::encode($data);
    }

    /**
     * Consolidate all settings to know how to render the grid
     *
     * Options are set on grid level by:
     * 1. javascript options passed to jqGrid (?)
     * 2. special Bvb_Grid_Deploy_JqGrid options (jqg array)
     * 3. standard Bvb settings
     *
     * Options are set on column level by:
     * 1. javascript options passed to columns (?)
     * 2. special Bvb_Grid_Deploy_JqGrid options (jqg array)
     * 3. standard Bvb settings
     * 4. formaters (?)
     *
     * @return void
     */
    public function prepareOptions()
    {
        $id = $this->getId();
        // build URL where to receive data from
        $url = $this->getView()->serverUrl(true) . "?q=$id";

        // initialize table with default options
        $this->_jqgParams += $this->_jqgDefaultParams;
        // prepare navigation
        $this->_postCommands[] = sprintf(
            "jqGrid('navGrid', '#%s',{edit:false,add:false,del:false,search:false,view:true})",
            $this->jqgGetIdPager()
        );

        // override with options defined on Bvb_Grid level
        $this->_jqgParams['url'] = isset($this->_jqgParams['url']) ? (empty($this->_jqgParams['url']) ? $url : $this->_jqgParams['url']) : $url;
        $this->_jqgParams['pager'] = new Zend_Json_Expr(sprintf("'#%s'", $this->jqgGetIdPager()));
        $this->_jqgParams['rowNum'] = isset($this->_jqgParams['rowNum'])
            ? (empty($this->_jqgParams['rowNum']) ? $this->_recordsPerPage : $this->_jqgParams['rowNum'])
            : $this->_recordsPerPage;
        $this->_jqgParams['rowList'] = isset($this->_jqgParams['rowList']) ? $this->_jqgParams['rowList'] : $this->_paginationInterval;

        if (!$this->getInfo('noFilters', false)) {
            // add filter toolbar to grid - if not set $grid->noFilters(1);
            $this->_postCommands[] = 'jqGrid("filterToolbar")';
            $this->jqgAddNavButton(
                array(
                    'caption' => $this->__("Toggle Search"),
                    'title' => $this->__("Toggle Search Toolbar"),
                    'buttonicon' => 'ui-icon-pin-s',
                    'onClickButton' => new Zend_Json_Expr("function(){ jQuery(this)[0].toggleToolbar(); }")
                )
            );
        }

        if ($this->getInfo('noOrder', false)) {
            // dissable sorting on columns - if set $grid->noOrder(1);
            $this->_jqgParams['viewsortcols'] = array(false,'vertical',false);
        }

        if ($this->getMassActions()->hasMassActions()) {
            // add multiselect
            $this->_jqgParams['multiselect'] = true;
            $this->_jqgParams['multiboxonly'] = false;
            $this->_jqgParams['multikey'] = "ctrlKey";

            // add actions
            // TODO render combobox and button combination
            foreach ($this->getMassActions()->getMassActionsOptions() as $options) {
                $this->jqgAddNavButton(
                    array(
                        'caption' => $options['caption'],
                        'buttonicon' => isset($options['cssClass']) ? $options['cssClass'] : "ui-icon-circle-triangle-e ",
                        'onClickButton' => new Zend_Json_Expr($this->getJsFuncMassAction($options)),
                        'position' => "last"
                    )
                );
            }
        }
        // add export buttons
        $this->addExportButtons($this->getExports());
    }

    /**
     * Return javascript call to post to given mass action
     *
     * @param array          $massAction  mass action definition to execute on selected rows
     * @param boolean|string $useOnSubmit true will add hander defined on action, false will add nothing, string will replace action setting
     *
     * @return string
     */
    public function getJsFuncMassAction($massAction, $useOnSubmit=true)
    {
        $onsubmit = "";

        // decide what fields to submit for given action
        if (isset($massAction['fields'])) {
            if (is_string($massAction['fields'])) {
                $fields = array($massAction['fields']);
            } else {
                $fields = $massAction['fields'];
            }
        } else {
            $fields = $this->getMassActions()->getFields();
        }
        $selectedValuesJs = $this->getJsFuncSelectedValues($fields);

        if (true===$useOnSubmit) {
            // build confirmation if requested
            if (isset($massAction['confirm'])) {
                $onsubmit = 'if(!confirm("'.preg_replace("/\r?\n/", "\\n", addslashes($massAction['confirm'])).'")) return false;';
            }

            // add onsubmit handler provided by
            if (isset($massAction['onsubmit'])) {
                $onsubmit .= 'if(!function(count,data) {'.$massAction['onsubmit'].'}(data.count, data.postMassIds)) return false;';
            }
        } elseif (false!==$useOnSubmit) {
            $onsubmit .= 'if(!function(count,data) {'.$useOnSubmit.'}(data.count, data.postMassIds)) return false;';
        }

        // render function
        // TODO this could be more general function added with jQuery() helper and called with parameters
        return <<<JS
function(){
    var data=$selectedValuesJs();
    {$onsubmit}
    if (data.count==0) {
        return false;
    }
    var id = 1;
    while ($('#_jqpost'+id).size()>0) {
        id++;
    }
    id = '_jqpost'+id;
    $('body').append('<form id="'+id+'" method="post" action="{$massAction['url']}"><input type="hidden" name="postMassIds"/></form>');
    $('#'+id+' :input').val(JSON.stringify(data.postMassIds));
    $('#'+id).submit();
}
JS;
    }


    /**
     * Helper to generate JS to get column data of selected rows
     *
     * TODO empty $colNames should cause to receive data from all columns
     *
     * @param array|string $colNames list of column names you want collect
     *
     * @return string javascript function which returns data when executed
     */
    public function getJsFuncSelectedValues($colNames)
    {
        if (is_string($colNames)) {
            $colNames = array($colNames);
        }
        // build dynamic code
        $init = $assign = "";
        foreach ($colNames as $col) {
            $init .= "values.$col = [];";
            $assign .= "var v=grid.jqGrid('getCell', ids[i], '$col');values.$col.push(v);";
        }
        // build JS
        $js = <<<JS
function () {
    var grid = jQuery('#{$this->jqgGetIdTable()}');
    var ids = grid.jqGrid("getGridParam","selarrrow");
    var values = new Object;
    var ret = new Object;
    ret.count = ids.length;
    $init
    if (ret.count>0) {
        for (var i=0, il=ids.length; i<il;i++) {
            $assign
        }
    }
    ret.postMassIds = values;
    return ret;
}
JS;
        return $js;
    }

    /**
     * Encode Json that may include javascript expressions.
     *
     * Take care of using the Zend_Json_Encoder to alleviate problems with the json_encode
     * magic key mechanism as of now.
     *
     * @param mixed $value value to encode
     *
     * @see Zend_Json::encode
     *
     * @return mixed
     */
    public static function encodeJson($value)
    {
        return Zend_Json::encode($value, false, array('enableJsonExprFinder' => true));
    }

    /**
     * Loads jQuery related libraries needed to display jqGrid.
     *
     * ZendX_Jquery is used as default, but this could be overriden.
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function jqInit()
    {
        if ($this->_jqInitialized) {
            // this should run only once for all grids in this request
            return $this;
        }
        $jqgridLibPath = $this->getJqGridLibPath();
        $this->getView()->jQuery()
            ->enable()
            ->uiEnable()
            ->addStylesheet($jqgridLibPath . "/css/ui.jqgrid.css")
            ->addJavascriptFile($jqgridLibPath . '/js/i18n/grid.locale-' . $this->getJqgI18n() . '.js')
            // TODO enable following lines when ZendX_Jquery will support it
            //->addJavascriptBetweenFiles($this->getJqgPreloadConfig())
            ->addJavascriptFile($jqgridLibPath . '/js/jquery.jqGrid.min.js');
        ;

        // remember that we are initialized
        $this->_jqInitialized = true;

        return $this;
    }

    /**
     * Return language code
     *
     * @return string
     */
    public function getJqgI18n()
    {
        return $this->getJqgParam('i18n', self::$defaultJqgI18n);
    }

    /**
     * Return URL where jqGrid library is located (it has js and css folders under it).
     *
     * @return string
     */
    public function getJqGridLibPath()
    {
        return self::$defaultJqGridLibPath;
    }

    /**
     * Add JavaScript code to be executed when jQuery ready event
     *
     * ZendX_Jquery is used as default, but this could be overriden.
     *
     * @param string $js javascipt code to add
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function jqAddOnLoad($js)
    {
        $this->getView()->jQuery()->addOnLoad($js);
        return $this;
    }

    /**
     * Return Javascript which will configure jqGrid before it will be loaded.
     *
     * This code should be added between grid.locale-*.js and  jquery.jqGrid.min.js file.
     *
     * @return string
     */
    public function getJqgPreloadConfig()
    {
        // TODO settings should be configurable
        // TODO is there benefit to add: \njQuery.jgrid.no_legacy_api = true;
        return "jQuery.jgrid.useJSON = true;";
    }

    /**
     * Add action button to navigation bar
     *
     * @param array $button options for JqGrid custom button
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function jqgAddNavButton($button)
    {
        $this->_navButtons[] = $button;
        return $this;
    }

    /**
     * Return colModel property for jqGrid
     *
     * @return array
     */
    public function jqgGetColumnModel()
    {
        $model = array();

        //BVB grid options
        $skipOptions = array(
            'title',     // handled in parent::_buildTitles()
            'hidde',      // handled in parent::_buildTitles()
            'sqlexp',
            'hRow',
            'eval',
            'callback',
            'searchType',
            'format',
            'field',
            'jqg' // we handle this separately
        );

        $defaultFilters = array_flip(is_null($this->_defaultFilters) ? array() : $this->_defaultFilters);

        $titles = $this->_buildTitles();
        $fields = $this->_data['fields'];

        foreach ($titles as $key=>$title) {
            // basic options
            $options = array_merge(
                $this->_jqgColDefaultParams,
                array("name" => isset($title['name'])?$title['name']:'', "label" => $title['value'])
            );

            if ($title['type']=='extraField') {
                $options['search']= false;
            }

            // add defined options
            if (isset($fields[$key])) {
                if (isset($fields[$key]['class'])) {
                    // convert Bvb class option to jqGrid classes option
                    // TODO maybe move to prepareOptions()
                    if (!isset($fields[$key]['jqg'])) {
                        $fields[$key]['jqg'] = array();
                    }
                    $fields[$key]['jqg']['classes'] = $fields[$key]['class'];
                    unset($fields[$key]['class']);
                }
                foreach ($fields[$key] as $name=>$value) {
                    if ( in_array($name, $skipOptions)) {
                        continue ;
                    }
                    // standard Bvb property which is not excluded will be passed to jqGrid colModel
                    //$this->log("not skipped option: ".$name);
                    $options[$name] = $value;
                }
                if (isset($fields[$key]['jqg'])) {
                    // we apply jqg options after all other options
                    // see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:colmodel_options
                    foreach ($fields[$key]['jqg'] as $name=>$value) {
                        $options[$name] = $value;
                    }
                }
            } else {
                $this->log("why there is no key $key in fields ?");
            }
            // overide default filters if setDefaultFilers() was used
            if (isset($defaultFilters[$key])) {
                // this does not work with bvbFirstDataAsLocal = false,
                // because jQuery().trigger("refreshGrid") will reset filters
                if (isset($options['searchoptions'])) {
                    $options['searchoptions']['defaultValue'] = $defaultFilters[$key];
                } else {
                    $options['searchoptions'] = array('defaultValue'=>$defaultFilters[$key]);
                }
            }

            // fix hidden field
            if (isset($options['hidden'])) {
                $options['hidden'] = (bool) $options['hidden'];
            } else {
                unset($options['hidden']);
            }

            // add field to model
            $model[] = $options;
        }

        return $model;
    }

    /**
     * Return ID for pager HTML element
     *
     * @return string
     */
    public function jqgGetIdPager()
    {
        return "jqg_pager_" . $this->getId();
    }

    /**
     * Return ID for pager HTML element
     *
     * @return string
     */
    public function jqgGetIdTable()
    {
        return "jqg_" . $this->getId();
    }

    /**
     * Add command to chain. See http://www.trirand.com/jqgridwiki/doku.php?id=wiki:methods.
     *
     * @param string $command jqGrid command
     *                        there could be any number of additional parameters
     *
     * @return JqGridCommand
     */
    public function cmd($command)
    {
        $cmd = new JqGridCommand($this);
        $args = func_get_args();
        call_user_func_array(array($cmd, 'cmd'), $args);
        return $cmd;
    }

    // Following functions could go to Bvb_Grid
    /**
     * Contains result of deploy() function.
     *
     * @var string
     */
    protected $_deploymentContent = null;

    /**
     * Return result of deploy().
     *
     * string|boolean FALSE if deploy() was not called before
     *
     * @return string
     */
    public function __toString()
    {
        if (is_null($this->_deploymentContent)) {
            $this->log("You should call deploy() before ", Zend_Log::WARN);
            // TODO !!! maybe we should simply call deploy() here
            // TODO enable this line after DataGrid will be fixed
            // return false;
            return parent::__toString();
        } else {
            return $this->_deploymentContent;
        }
    }

    /**
     * Ajax ID
     * @var string
     */
    protected $_id = 0;

    /**
     * @var mixed
     */
    protected $_logger = null;

    /**
     * Set to true if you want to debug this class
     *
     * @var bool
     */
    public static $debug = false;

    /**
     * Use to detect if we should return plain JSON data or full table definition
     *
     * @return boolean
     */
    protected function isAjaxRequest()
    {
        return $this->getRequest()->isXmlHttpRequest()
            || $this->getParam('_search');
    }

    /**
     * Return value used to build HTML element ID attributes
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set value used to build HTML element ID attributes
     *
     * @param string $id text to apply as part of jqGrid HTML element IDs
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Set column containing id value of the row
     *
     * @param string $id name of the column
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    public function bvbSetId($id)
    {
        return $this->setId($id);
    }

    /**
     * Get column containing id value of the row
     *
     * @return string
     */
    public function bvbGetId()
    {
        return $this->getId();
    }

    /**
     * Create Zend_Log object used to debug Bvb classes
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    protected function initLogger()
    {
        if (self::$debug) {
            // send messages to FirePHP
            $writter = new Zend_Log_Writer_Firebug();
        } else {
            // we need to have at least dummy instance of Zend_Log
            $writter = new Zend_Log_Writer_Null();
        }
        $this->_logger = new Zend_Log($writter);
        return $this;
    }

    /**
     * Log message. Per default the message will be sent to FirePHP.
     *
     * @param string $message  message to log
     * @param int    $priority one of Zend_Log constances, Zend_Log::DEBUG is default
     *
     * @return Bvb_Grid_Deploy_JqGrid
     */
    protected function log($message, $priority = 7)
    {
        $this->_logger->log($message, $priority);
        return $this;
    }

    /**
     * Handle parameters send from frontend.
     *
     * They could contain:
     * - number of rows to be shown on page
     * - first row to show on page
     * - sort order
     * - search filters
     *
     * @param array $params parameters to conver, request parameters will be used of not set
     *                      this array should always contain Zend parameters module, controller, action
     *
     * @return void
     */
    protected function convertRequestParams($params=null)
    {
        if (is_null($params)) {
            $params = $this->getRequest()->getParams();
        }

        // we try to convert jqGrid request to be Bvb ctrlParms compatible

        // add Zend parameters
        $this->setParam('module', $params['module']);
        $this->setParam('controller', $params['controller']);

        if (isset($params['action'])) {
            $this->setParam('action', $params['action']);
        }

        // number of rows to be shown on page, could be changed in jqGrid
        if (isset($params['rows'])) {
            $this->setParam('perPage', $params['rows']);
            $this->_recordsPerPage = $params['rows'];
        }

        // first row to display
        if (isset($params['page'])) {
            $page = $params['page'];
        } else {
            $page = 1;
        }
        $this->setParam('page', $page);
        $this->setParam('start', $this->_recordsPerPage * ($page-1));

        // sort order
        $sidx = isset($params['sidx']) ? $params['sidx'] : "";
        $sord = isset($params['sord']) ? $params['sord'] : "asc";
        if ($sidx!=="") {
            $this->setParam('order', $sidx . '_' . strtoupper($sord));
        }

        // filters
        // TODO it would be great to have some methods to define more complicated filters
        if (isset($params['_search']) && $params['_search']) {
            if (isset($params['filters'])) {
                // TODO Advanced searching
                // see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:advanced_searching&s[]=multiplesearch
                // http://www.ong.agimondo.it/extras/jq/search_adv.php
            } elseif (isset($params['searchField'])) {
                // TODO Single searching format
                // see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:singe_searching&s[]=multiplesearch
            } else {
                // Toolbar Searching
                // see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:toolbar_searching&s[]=searchoptions
                $flts = new stdClass();
                $filteredFields = array_diff_key(
                    $params,
                    array_flip(
                        array('q', 'nd', 'rows', 'page', 'sidx', 'sord', '_search', 'module', 'controller', 'action')
                    )
                );
                foreach ($filteredFields as $filter=>$val) {
                    $flts->$filter = $val;
                    $this->setParam($filter, $val);
                }
            }
        }
    }
    /**
     * Function to format action links
     *
     * @param mixed $actions definition of links to action
     *                       all parameters will be added as HTML attributes to link except:
     *                       caption: will be placed between <a><span>$caption</span></a>
     *                       class: predefined variables {edit},{delete},{view} will be replaced with jQuery UI classes
     *                       img: could be array and will be extracted as HTML attributes to <img> tag
     *
     * @return string
     */
    public static function formatterActionBar($actions)
    {
        // TODO this should be Bvb_Formatter class and it should not be static there
        //$actionClasses = $this->getActionClasses();
        $actionClasses = self::$defaultActionClasses;
        $html = "";
        foreach ($actions as $a) {
            $htmlAtts = array();
            if (isset($a['class'])) {
                // support predefined CSS classes
                $class = trim(
                    str_replace(
                        array_keys($actionClasses),
                        $actionClasses,
                        $a['class']
                    )
                );
                if (!empty($class)) {
                    $a['class'] = $class;
                }
            }

            if (isset($a['caption'])) {
                // handle caption
                $caption = $a['caption'];
                unset($a['caption']);
            }

            if (isset($a['img'])) {
                // TODO if we pass link to image to show instead of text
            } else {
                // will show text or icon if CSS class is styled
                if (!isset($a['style'])) {
                    $a['style'] = "float:left;";
                }
                $htmlAtts = self::htmlAttribs($a);
                $html .= "<a $htmlAtts><span>$caption</span></a>";
            }
        }
        return $html;
    }

    /**
     * Converts an associative array to a string of tag attributes.
     *
     * This function is clone from Zend_View_Helper_HtmlElement
     *
     * @param array $attribs From this array, each key-value pair is
     *                       converted to an attribute name and value.
     *
     * @return string The XHTML for the attributes.
     */
    public static function htmlAttribs($attribs)
    {
        $view = new Zend_View();
        $xhtml = '';
        foreach ((array) $attribs as $key => $val) {
            $key = $view->escape($key);

            if (('on' == substr($key, 0, 2)) || ('constraints' == $key)) {
                // Don't escape event attributes; _do_ substitute double quotes with singles
                if (!is_scalar($val)) {
                    // non-scalar data should be cast to JSON first
                    include_once 'Zend/Json.php';
                    $val = self::encodeJson($val);
                }
                $val = preg_replace('/"([^"]*)":/', '$1:', $val);
            } else {
                if (is_array($val)) {
                    $val = implode(' ', $val);
                }
                $val = $view->escape($val);
            }

            if (strpos($val, '"') !== false) {
                $xhtml .= " $key='$val'";
            } else {
                $xhtml .= " $key=\"$val\"";
            }
        }
        return $xhtml;
    }

    /**
     * Configuration magic
     *
     * @param string $var   name of property to set with value
     * @param mixed  $value value
     *
     * @return void
     */
    public function __set($var, $value)
    {
        // check what domain parameter it is
        $domain = substr($var, 0, 3);
        if (strlen($var)>3 && $var[3]===strtoupper($var[3]) && 0===strcmp($domain, strtolower($domain))) {
            // it is valid domain
            $variableName = substr($var, 3);
            $setterName = $domain."Set".$variableName;
            if (method_exists($this, $setterName)) {
                // there is dedicated setter function for this option
                $this->$setterName($value);
                return;
            }

            $variableName[0] = strtolower($variableName[0]);
            $setterName = "set".ucfirst($domain)."Param";
            if (method_exists($this, $setterName)) {
                // there is setter function for this domain
                $this->$setterName($variableName, $value);
                return;
            }
        }
        // not a domain property
        parent::__set($var, $value);
    }
    /**
     * Returns the number of records to show per page
     *
     * Is overriden to return always the number of data requested by jqGrid ajax request.
     *
     * @return integer
     */
    public function getRecordsPerPage ()
    {
        $perPage = $this->getParam('perPage', false);
        if (false===$perPage) {
            return parent::getRecordsPerPage();
        }
        return $perPage;
    }

    /**
     * Will return actual version of this file
     *
     * @return string
     */
    public static function getVersion()
    {
        return '$Rev$';
    }
    // TODO __get()
}

class JqGridCommand
{
    protected $_cmds = array(0=>array());
    protected $_cmdsStack = 0;
    protected $_grid;

    /**
     * Constructor
     *
     * @param Bvb_Grid_Deploy_JqGrid $grid grid object
     */
    public function __construct($grid)
    {
        $this->_grid = $grid;
    }

    /**
     * Build javascript from all commands
     *
     * @return string
     */
    public function __toString()
    {
        $stacks = array();
        foreach ($this->_cmds as $stack) {
            if (count($stack)>0) {
                $stacks[] = "jQuery('#" . $this->_grid->jqgGetIdTable() . "')." . implode('.', $stack) . ";";
            }
        }

        if (count($stacks)>0) {
            return implode("\n", $stacks);
        }
    }

    /**
     * Add command to chain.
     *
     * @param string $command jqGrid command there could be any number of additional parameters
     *
     * @link http://www.trirand.com/jqgridwiki/doku.php?id=wiki:methods
     *
     * @return JqGridCommand
     */
    public function cmd($command)
    {
        $params = func_get_args();
        // remove command from parameter list
        array_shift($params);
        // encode parameters
        $tmp = array();
        foreach ($params as $param) {
            $tmp[] = $this->_grid->encodeJson($param);
        }
        $params = implode(",", $tmp);
        // add parameter to stack
        switch ($command) {
            case "trigger":
                // does not seam to work in new API, maybe it will change in future
                $this->_cmds[$this->_cmdsStack][] = "trigger($params)";
                break;
            case 'setPostData':
            case 'appendPostData':
            case 'setPostDataItem':
            case 'removePostDataItem':
                // fix non chainable jqGrid methods
                $this->_cmds[$this->_cmdsStack] = array('jqGrid("' . $command . '",' . $params . ')');
                $this->_cmdsStack++;
                break;
            default:
                $this->_cmds[$this->_cmdsStack][] = 'jqGrid("' . $command . '",' . $params . ')';
        }
        // let us be chainable
        return $this;
    }
}