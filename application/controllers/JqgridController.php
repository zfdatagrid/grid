<?php
class JqgridController extends Zend_Controller_Action
{
    function init()
    {
        $this->db = Zend_Registry::get ( 'db' );
        // turn on profiler
        $profiler = new Zend_Db_Profiler_Firebug('DB Queries');
        $profiler->setEnabled(true);
        $this->db->setProfiler($profiler);
        // enable debug
        Bvb_Grid_Deploy_JqGrid::$debug = true;
        // enable JQuery - should be part of bootstrap
        ZendX_JQuery::enableView($this->view);
        // set url to jqGrid library
        if (@isset(Zend_Registry::get ( 'config' )->site->jqGridUrl)) {
            Bvb_Grid_Deploy_JqGrid::$defaultJqGridLibPath = Zend_Registry::get ( 'config' )->site->jqGridUrl;
        }

        $this->_config = new Zend_Config_Ini('./application/grids/grid.ini','production');
    }
    /**
     * Show the source code for this controller
     *
     */
    function codeAction()
    {
    }
    function doAction()
    {
        print_r($this->getRequest()->getParams());
        die();
    }
    function indexAction()
    {
        // construct JqGrid and let it configure
        $grid1 = new Bvb_Grid_Deploy_JqGrid($this->_config);
        $this->configG1($grid1, $this->_getParam('onlyFromPolynesia', 'false')==='true');

       $grid1->setGridColumns(array('Name', 'Continent', 'Population', 'LocalName', 'GovernmentForm'));

        // pass grids to view and deploy() them there
        $this->view->g1 = $grid1->deploy();
    }


    function exportAction()
    {
        // construct JqGrid and let it configure
        $grid1 = Bvb_Grid::factory(
            'Bvb_Grid_Deploy_JqGrid',$this->_config ,'', // this is the defualt grid class used to render on page
            array(
                'csv'=>array($this, 'configG1PostCsv') // do post config for Csv export
            )
        );
        $this->configG1($grid1, $this->_getParam('onlyFromPolynesia', 'false')==='true');
        // pass grids to view and deploy() them there
        $this->view->g1 = $grid1->deploy();

        $this->render('index');
    }
    /**
     * This will run if we will export to Csv before deploy() and ajax() functions
     */
    public function configG1PostCsv($grid)
    {
        // we don't want this column in export
        $grid->updateColumn('_action', array('hide'=>true));
    }

    public function g1ActionBar($id) {
        $helper = new Zend_View_Helper_Url();
        $actions = array(
            array('href'=>$helper->url(array('action'=>'do', 'what'=>'view', 'id'=>$id)), 'caption'=>'View', 'class'=>'{view}'),
            array('href'=>$helper->url(array('action'=>'do', 'what'=>'edit', 'id'=>$id)), 'caption'=>'Edit', 'class'=>'{edit} fixedClass'),
            array('href'=>$helper->url(array('action'=>'do', 'what'=>'delete', 'id'=>$id)), 'caption'=>'Delete', 'class'=>'{delete}'),
            array('onclick'=>new Zend_Json_Expr('alert("you clicked on ID: "+jQuery(this).closest("tr").attr("id"));'), 'caption'=>'Alert Me')
        );
        return Bvb_Grid_Deploy_JqGrid::formatterActionBar($actions);
    }

    function configG1($grid, $onlyFromPolynesia = false)
    {
        ////////////////// 1. define select
        $select = $this->db->select()
            ->from('City')
            ->order('Name')
            ->columns(array('IsBig'=>new Zend_Db_Expr('IF(City.Population>500000,1,0)')))
            ->columns(array('_action'=>'ID'))
        ;
        if ($onlyFromPolynesia) {
            $select
                ->join('Country', 'Country.Code=City.CountryCode', array('Region'))
                ->where('Country.Region=?', "Polynesia")
            ;
        }
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        ////////////////// 2. update column options
        ////////////////// see Bvb documentation
        ////////////////// and for jqg array see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:colmodel_options
        $grid->updateColumn('ID', array(
            'title'=>'#ID',
            'hide'=>true,
        ));
        $grid->updateColumn('_action', array(
            'search'=>false, // this will disable search on this field
            'order'=>1,
            'title'=>'Action',
            'width'=>100,
            'class'=>'bvb_action bvb_first',
            'callback'=>array(
                'function'=>array($this,'g1ActionBar'),
                'params'=>array('{{ID}}')
            ),
            'jqg'=>array('fixed'=>true, 'search'=>false)
        ));
        $grid->updateColumn('Name', array(
            'title'=>'City name',
            'width'=>260
        ));
        $grid->updateColumn('CountryCode',
            array(
                'title'=>'Country code',
                'searchType'=>"="
            )
        );
        $grid->updateColumn('District', array(
            'title'=>'District (ucase)',
            'callback'=>array(
                'function'=>create_function('$text', 'return strtoupper($text);'),
                'params'=>array('{{District}}')
            ),
        ));
        $grid->updateColumn('Population', array(
            'align'=>'right',
            'jqg' => array(
                'formatter'=>'integer'
            )
        ));
        $grid->updateColumn('IsBig', array(
            'width'=>40,
            'title' => 'Is Big City',
            'align'=>'center',
            'jqg' => array(
                'formatter'=>'checkbox',
                'stype'=>'select',
                'searchoptions'=>array(/*'defaultValue'=>'1', */'value'=>array(null=>'All', 0=>'No', 1=>'Yes'))
            )
        ));

        ////////////////// 3. configure Bvb grid behaviour
        //$grid->noFilters(1);
        //$grid->noOrder(1);
        $grid->setDefaultFilters(array('IsBig'=>'1'));

        ////////////////// 4. configure jqGrid options
        ////////////////// for setJqgOptions see http://www.trirand.com/jqgridwiki/doku.php?id=wiki:options
        ////////////////// see also other Bvb_Grid_Deploy_JqGrid::setJqg*() and Bvb_Grid_Deploy_JqGrid::jqg*() methods
        $grid->setExport(array(
            // define parameters for csv export, see Bvb_Grid::getExports
            'csv'=>array('caption'=>'Csv'))
        );
        $grid->setJqgParams(array(
            'caption' => 'jqGrid Example',
            'forceFit'=>true,
            'viewrecords'=>false, // show/hide record count right bottom in navigation bar
            'rowList'=> array(10, 15, 50), // show row number per page control in navigation bar
            'altRows'=>true, // rows will alternate color
        ));
        $grid->setJqgParam('viewrecords', true); // yet another way to set jqGrid property viewrecords
        $grid->jqgViewrecords = true; // yet another way to set jqGrid property viewrecords

        $grid->setBvbParams(array(
            'id'=>'ID'
        ));
        $grid->setBvbParam('id', 'ID'); // another way to set own Bvb_Grid_Deploy_JqGrid parameter
        $grid->bvbId = 'ID'; // yet another way to set own Bvb_Grid_Deploy_JqGrid parameter

        $grid->bvbOnInit = 'console.log("this message will not be logged because of call to bvbClearOnInit().");';
        $grid->bvbClearOnInit();
        $grid->bvbSetOnInit('console.log("jqGrid initiated ! If data are remote they are not loaded at this point.");');

        //$grid->bvbFirstDataAsLocal = false; // how will grid receive first data ?

        ////////////////// 5. set ajax ID and process response if requested
        $grid->ajax(get_class($grid));
    }
}