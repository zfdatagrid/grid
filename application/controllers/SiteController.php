<?php

/**
 * @author mascker
 */

include 'application/models/Model.php';


function people ($id, $value, $select)
{
    $select->where('Population>?', $value);
}


function filterContinent ($id, $value, $select)
{
    $select->where('Continent=?', $value);
}

class SiteController extends Zend_Controller_Action
{

    private $_db;

    function teste()
    {
       echo "<pre>";

       print_r(func_get_args());

       echo '<br><br><br><br><br>';
       die();
    }

    /**
     * If a action don't exist, just redirect to the basic
     *
     * @param string $name
     * @param array $var
     */
    public function __call ($name, $var)
    {
        $this->_redirect('default/site/basic', array('exit' => 1));
        return false;
    }


    /**
     * I think this is needed for something. can't remember
     *
     */
    public function init ()
    {
        $this->view->url = Zend_Registry::get('config')->site->url;
        $this->view->action = $this->getRequest()->getActionName();
        header('Content-Type: text/html; charset=ISO-8859-1');
        $this->_db = Zend_Registry::get('db');
        Bvb_Grid_Deploy_Ofc::$url = Zend_Registry::get('config')->site->url;
    }


    /**
     * Same as __call
     *
     */
    public function indexAction ()
    {
        $this->_forward('basic');
    }


    public function formAction ()
    {
        $grid = $this->grid();
        $grid->query($this->_db->select()->from('form'));
        $form = new Bvb_Grid_Form();
        $form->setBulkAdd(2)->setAdd(true)->setEdit(true)->setBulkDelete(true)->setBulkEdit(true)->setDelete(true)->setAddButton(true);
        $grid->setForm($form);
        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    public function form1Action ()
    {
        $grid = $this->grid();
        $grid->query($this->_db->select()->from('form'));
        $form = new Bvb_Grid_Form();
        $form->setBulkAdd(5)->setAdd(true)->setEdit(true)->setBulkDelete(true)->setBulkEdit(true)->setDelete(true)->setAddButton(true);
        $form->setUseVerticalInputs(false);
        $grid->setForm($form);
        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    /**
     * Show the source code for this controller
     *
     */
    public function codeAction ()
    {
        $this->render('code');
    }


    /**
     * Simplify the datagrid creation process
     * @return Bvb_Grid_Deploy_Table
     */
    public function grid ($id = '')
    {
        $view = new Zend_View();
        $view->setEncoding('ISO-8859-1');
        $config = new Zend_Config_Ini('./application/grids/grid.ini', 'production');
        $grid = Bvb_Grid::factory('Table', $config, $id);
        $grid->setEscapeOutput(false);
        $grid->setExport(array( 'csv','excel'));
        $grid->setView($view);
        #$grid->saveParamsInSession(true);
        #$grid->setCache(array('use' => array('form'=>false,'db'=>false), 'instance' => Zend_Registry::get('cache'), 'tag' => 'grid'));
        return $grid;
    }


    /**
     * A simple usage of advanced filters. Every time you change a filter, the system automatically
     *runs a query to the others filters, making sure they don't allow you to filter for a record that is not in the database
     */
    public function filtersAction ()
    {

        $grid = $this->grid();

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($this->_db->select()->from('Country', array('Name', 'Continent', 'Population', 'LifeExpectancy', 'GovernmentForm', 'HeadOfState'))));

        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('Name', array('distinct' => array('field' => 'Name', 'name' => 'Name', 'order' => 'field desc')));
        $filters->addFilter('Continent', array('distinct' => array('field' => 'Continent', 'name' => 'Continent')));
        $filters->addFilter('LifeExpectancy', array('render' => 'number', 'distinct' => array('field' => 'LifeExpectancy', 'name' => 'LifeExpectancy')));
        $filters->addFilter('GovernmentForm', array('distinct' => array('field' => 'GovernmentForm', 'name' => 'GovernmentForm')));
        $filters->addFilter('HeadOfState');
        $filters->addFilter('Population', array('render' => 'number'));


        $grid->addFilters($filters);


        $grid->addExternalFilter('new_filter', 'people');
        $grid->addExternalFilter('filter_country', 'filterContinent');

        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    public function dateAction ()
    {

        $grid = $this->grid();

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($this->_db->select()->from('bugs', array('bug_status', 'status', 'date', 'time'))));

        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('date', array('render' => 'date'));

        $grid->addFilters($filters);

        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    /**
     * Adding extra columns to a datagrid. They can be at left or right.
     * Also notice that you can use fields values to populate the fields by surrounding the field name with {{}}
     *
     */
    public function extraAction ()
    {

        $grid = $this->grid();

        $select = $this->_db->select()->from(array('c' => 'Country'), array('country' => 'Name', 'Continent', 'Population', 'GovernmentForm', 'HeadOfState'))->join(array('ct' => 'City'), 'c.Capital = ct.ID', array('city' => 'Name'));

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $grid->updateColumn('country', array('title' => 'Country (Capital)', 'class' => 'hideInput', 'decorator' => '{{country}} <em>({{city}})</em>'));
        $grid->updateColumn('city', array('title' => 'Capital', 'hidden' => 1));
        $grid->updateColumn('Continent', array('title' => 'Continent'));
        $grid->updateColumn('Population', array('title' => 'Population', 'class' => 'width_80'));
        $grid->updateColumn('LifeExpectancy', array('title' => 'Life E.', 'class' => 'width_50'));
        $grid->updateColumn('GovernmentForm', array('title' => 'Government Form'));
        $grid->updateColumn('HeadOfState', array('title' => 'Head Of State', 'hidden' => 1));

        $grid->setPdfGridColumns(array('Continent','Population'));


        $right = new Bvb_Grid_Extra_Column();
        $right->position('right')->name('Right')->decorator("<input class='input_p'type='text' value=\"{{Population}}\" size=\"3\" name='number[]'>");

        $left = new Bvb_Grid_Extra_Column();
        $left->position('left')->name('Left')->decorator("<input  type='checkbox' name='number[]'>");

        $grid->addExtraColumns($right, $left);


        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    public function csvAction ()
    {

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Csv('media/files/grid.csv'));
        $grid->setSqlExp(array('Population' => array('functions' => array('SUM'), 'value' => 'Population')));

        $form = new Bvb_Grid_Form();
        #$form->setIsPerformCrudAllowed(false);
        $form->setAdd(1)->setEdit(1)->setDelete(1)->setAddButton(1);
        #$form->addElement('text','my');



        $grid->setForm($form);

        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    public function jsonAction ()
    {

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Json('media/files/json.json', 'rows'));
        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    public function feedAction ()
    {

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Xml('http://zfdatagrid.com/feed/', 'channel,item'));

        $grid->setRecordsPerPage(10);

        $grid->updateColumn('title', array('decorator' => '<a href="{{link}}">{{title}}</a>', 'style' => 'width:200px;'));
        $grid->updateColumn('pubDate', array('class' => 'width_200'));

        $grid->setGridColumns(array('title', 'comments', 'pubDate'));

        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    /**
     * The 'most' basic example.
     */
    public function openAction ()
    {
        $grid = $this->grid();


        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    /**
     * The 'most' basic example.
     */
    public function basicAction ()
    {
        $grid = $this->grid();
        $select = $this->_db->select()->from('Country', array('Name', 'Continent', 'Population', 'LocalName', 'GovernmentForm'));
        #$grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->query($select);

        #$grid->getSelect()->columns(array('calc'=>" CONCAT(Name,'')"));
        #$grid->setSqlExp(array('calc' => array('functions' => array('LENGTH'))));
        $grid->setUseKeyEventsOnFilters(true);

        #$grid->setShowOrderImages(false);

        #$grid->setShowFiltersInExport(array('User'=>'Barcelos'));
        #$grid->setDefaultFiltersValues(array('Continent'=>'Europe'));
        #$grid->setAlwaysShowOrderArrows(false);
        #$grid->setDefaultFiltersValues(array('Population'=>'12'));

        #$grid->setDeployOptions(array('title'=>'My Custom Title','subtitle'=>date('Y-m-d')));

        #$grid->saveParamsInSession(true);

        #$grid->placePageAtRecord('PRT','green');
        #$grid->updateColumn('Name',array('searchType'=>'sqlExp','searchSqlExp'=>'Name !={{value}} '));


        $script = "$(document).ready(function() {";
        foreach($grid->getVisibleFields() as $name)
        {
            $script .= "$(\"input#filter_$name\").autocomplete({focus: function(event, ui) {document.getElementById('filter_$name').value = ui.item.value }, source: '{$grid->getAutoCompleteUrlForFilter($name)}'});\n";
        }
        $script .= "});";
        $grid->getView()->headScript()->appendScript($script);

        $this->view->pages = $grid->deploy();


        $this->render('index');
    }


    public function referenceAction ()
    {

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Zend_Table(new Products()));

        $form = new Bvb_Grid_Form();
        $form->setAdd(true)->setEdit(true)->setDelete(true);
        $grid->setForm($form);

        $this->view->pages = $grid->deploy();

        $this->render('index');
    }


    public function multiAction ()
    {
        $grid = $this->grid('a');
        $select = $this->_db->select()->from('Country', array('Name', 'Continent', 'Population', 'LocalName', 'GovernmentForm'));
        $grid->query($select);
        $this->view->pages = $grid->deploy();

        $grid2 = $this->grid('b');
        $select2 = $this->_db->select()->from('Country', array('Name', 'Continent', 'Population', 'GovernmentForm'));
        $grid2->query($select2);
        $grid2->setTemplate('outside', 'table');
        $grid2->setRecordsPerPage(10);
        $this->view->pages2 = $grid2->deploy();

        $this->render('index');
    }


    public function detailAction ()
    {

        $grid = $this->grid();
        $select = $this->_db->select()->from('Country');
        $grid->query($select);

        $grid->setDetailColumns();
        $grid->setTableGridColumns(array('Name', 'Continent', 'Population', 'LocalName', 'GovernmentForm'));

        $this->view->pages = $grid->deploy();

        $this->render('index');
    }


    public function columnsAction ()
    {

        $grid = $this->grid();
        $select = $this->_db->select()->from('Country', array('Name', 'Continent', 'Population', 'LocalName', 'GovernmentForm'));
        #$grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->query($select);


        $rows = new Bvb_Grid_Extra_Rows();
        $rows->addRow('beforeHeader', array('', array('colspan' => 1, 'class' => 'myclass', 'content' => 'my content'), array('colspan' => 2, 'class' => 'myotherclass', 'content' => 'some '), array('colspan' => 1, 'class' => 'myclass', 'content' => 'flowers:) ')));
        $rows->addRow('beforePagination', array(array('colspan' => 5, 'content' => "This is an extra row added before pagination")));
        $grid->addExtraRows($rows);


        $this->view->pages = $grid->deploy();

        $this->render('index');
    }


    public function conditionalAction ()
    {

        $grid = $this->grid();
        $select = $this->_db->select()->from('Country');
        $grid->query($select);

        $grid->setClassCellCondition('Population', "'{{Population}}' > 200000", "red", 'green');
        #$grid->setClassRowCondition("'{{Population}}' > 20000", "green", 'red');


        $grid->setRecordsPerPage(15);
        $grid->setPaginationInterval(array(10 => 10, 20 => 20, 50 => 50, 100 => 100));

        $grid->setTableGridColumns(array('Name', 'Continent', 'Population', 'LocalName', 'GovernmentForm'));

        $grid->setSqlExp(array('Population' => array('functions' => array('SUM'))));

        $this->view->pages = $grid->deploy();


        $script = "$(document).ready(function() {";
        foreach($grid->getVisibleFields() as $name)
        {
            $script .= "$(\"input#filter_$name\").autocomplete({focus: function(event, ui) {document.getElementById('filter_$name').value = ui.item.value }, source: '{$grid->getAutoCompleteUrlForFilter($name)}'});\n";
        }
        $script .= "});";
        $grid->getView()->headScript()->appendScript($script);

        $this->render('index');
    }


    public function massAction ()
    {

        if ( $this->getRequest()->isPost() ) {
            echo "<pre>";
            print_r($this->_getAllParams());
            die();
        }

        $grid = $this->grid();
        $select = $this->_db->select()->from('Country');
        $grid->query($select);


        $grid->setMassActions(array(array('url' => $grid->getUrl(), 'caption' => 'Remove (Nothing will happen)', 'confirm' => 'Are you sure?'), array('url' => $grid->getUrl() . '/nothing/happens', 'caption' => 'Some other action', 'confirm' => 'Another confirmation message?')));


        $grid->setRecordsPerPage(15);
        $grid->setPaginationInterval(array(10 => 10, 20 => 20, 50 => 50, 100 => 100));
        $grid->setTableGridColumns(array('Name', 'Continent', 'Population', 'LocalName', 'GovernmentForm'));
        $grid->setSqlExp(array('Population' => array('functions' => array('SUM'))));

        $this->view->pages = $grid->deploy();

        $this->render('index');
    }

    public function arrayAction ()
    {
        $grid = $this->grid();
        $array = array(array('Alex', '12', 'M'), array('David', '1', 'M'), array('David', '2', 'M'), array('David', '3', 'M'), array('Richard', '3', 'M'), array('Lucas', '3', 'M'));

        $source = new Bvb_Grid_Source_Array($array, array('name', 'age', 'sex'));
        $source->setPrimaryKey(array('age'));

         $grid->setDetailColumns(array('age','name'));

        $grid->setSource($source);

        #$grid->setMassActions(array(array('url' => $grid->getUrl(), 'caption' => 'Remove (Nothing will happen)', 'confirm' => 'Are you sure?'), array('url' => $grid->getUrl() . '/nothing/happens', 'caption' => 'Some other action', 'confirm' => 'Another confirmation message?')));

        $this->view->pages = $grid->deploy();

        $this->render('index');
    }


    public function ajaxAction ()
    {
        $grid = $this->grid();
        $select = $this->_db->select()->from('Country');
        $grid->query($select);
        $grid->setAjax('ajax_grid');
        $grid->setTableGridColumns(array('Name', 'Continent', 'Population', 'LocalName', 'GovernmentForm'));
        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    public function joinsAction ()
    {

        $grid = $this->grid();
        $select = $this->_db->select()->from(array('c' => 'Country'), array('country' => 'Name', 'Code', 'Continent', 'Population', 'GovernmentForm', 'HeadOfState'))->join(array('ct' => 'City'), 'c.Capital = ct.ID', array('city' => 'Name'));
        $grid->query($select);

        $form = new Bvb_Grid_Form();
        $form->setAdd(1)->setEdit(1)->setDelete(1)->setAddButton(1);
        $form->setFieldsBasedOnQuery(true);
        $grid->setForm($form);


        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    public function unionAction ()
    {

        $grid = $this->grid();

        $sql1 = $this->_db->select()->from('Country', array('Continent', 'Code'))->limit(12);
        $sql2 = $this->_db->select()->from('City', array('Name', 'District'))->limit(12);

        $select = $this->_db->select()->union(array($sql1, $sql2))->order('Name DESC');

        $t = $select->query()->fetchAll();

        echo "<pre>";
        print_r($t);
        die();


        $grid->query($select);

        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    /**
     * Using a model
     */
    public function crudAction ()
    {
        $grid = $this->grid();
        $grid->query(new Bugs());
        $grid->setColumnsHidden(array('bug_id', 'time', 'verified_by','next'));

        $form = new Bvb_Grid_Form('My_Form');

        $form->setAdd(true)->setEdit(true)->setDelete(true)->setAddButton(true)->setSaveAndAddButton(true);



        #$grid->setDetailColumns();

        $grid->setForm($form);


        $grid->setDeleteConfirmationPage(true);
        $this->view->pages = $grid->deploy();

        $this->render('index');
    }


    /**
     * This demonstrates how easy it is for us to use our own templates (Check the grid function at the page top)
     *
     */
    public function templateAction ()
    {
        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($this->_db->select()->from('City')));
        $grid->setNoFilters(1)->setRecordsPerPage(14)->setTemplate('outside', 'table');
        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    /**
     * This example allow you to create an horizontal row, for every distinct value from a field
     *
     */
    public function hrowAction ()
    {

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($this->_db->select()->from('Country', array('Name', 'Continent', 'Population', 'LifeExpectancy', 'GovernmentForm', 'HeadOfState'))->limit(1000)));
        $grid->setNoFilters(1);
        $grid->setNoOrder(1);

        $grid->setRecordsPerPage(1200);

        $grid->updateColumn('Name', array('title' => 'Country'));
        $grid->updateColumn('Continent', array('title' => 'Continent', 'hRow' => 1));
        $grid->updateColumn('Population', array('title' => 'Population', 'class' => 'width_80'));
        $grid->updateColumn('LifeExpectancy', array('title' => 'Life E.', 'class' => 'width_50', 'decorator' => '<b>{{LifeExpectancy}}</b>'));
        $grid->updateColumn('GovernmentForm', array('title' => 'Government Form'));
        $grid->updateColumn('HeadOfState', array('title' => 'Head Of State'));

        $grid->setSqlExp(array('Population' => array('functions' => array('SUM'))));


        $extra = new Bvb_Grid_Extra_Column();
        $extra->position('right')->name('Right')->decorator("<input class='input_p'type='text' value=\"{{Population}}\" size=\"3\" name='number[]'>");

        $esquerda = new Bvb_Grid_Extra_Column();
        $esquerda->position('left')->name('Left')->decorator("<input  type='checkbox' name='number[]'>");

        $grid->addExtraColumns($extra, $esquerda);


        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    public function ofcAction ()
    {

        $this->view->graphs = $allowedGraphs = array('line', 'bar', 'bar_glass', 'bar_3d', 'bar_filled', 'pie', 'mixed');

        $type = $this->_getParam('type');


        if ( ! in_array($type, $allowedGraphs) ) {
            $type = 'bar_glass';
        }

        $this->getRequest()->setParam('_exportTo', 'ofc');

        $grid = $this->grid();
        $grid->setExport(array('ofc'));


        if ( $type == 'pie' ) {
            $grid->addValues('Population', array('set_colours' => array('#000000', '#999999', '#BBBBBB', '#FFFFFF')));
        } elseif ( $type == 'mixed' ) {
            $grid->addValues('GNP', array('set_colour' => '#FF0000', 'set_key' => 'Gross National Product', 'chartType' => 'Bar_Glass'));
            $grid->addValues('SurfaceArea', array('set_colour' => '#00FF00', 'set_key' => 'Surface', 'chartType' => 'line'));
        } else {
            $grid->addValues('GNP', array('set_colour' => '#00FF00', 'set_key' => 'Gross National Product'));
            $grid->addValues('SurfaceArea', array('set_colour' => '#0000FF', 'set_key' => 'Surface'));
        }

        $grid->setXLabels('Name');

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($this->_db->select()->from('Country', array('Population', 'Name', 'GNP', 'SurfaceArea'))->where('Continent=?', 'Europe')->where('Population>?', 5000000)->where(new Zend_Db_Expr('length(Name)<10'))->order(new Zend_Db_Expr('RAND()'))->limit(10)));

        $grid->setChartType($type);

        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    /**
     * If you don't like to work with array when adding columns, you can work by dereferencing objects
     *
     */
    public function columnAction ()
    {

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($this->_db->select()->from(array('c' => 'Country'), array('Country' => 'Name', 'Continent', 'Population', 'GovernmentForm', 'HeadOfState'))->join(array('ct' => 'City'), 'c.Capital = ct.ID', array('Capital' => 'Name'))));
        $grid->setRecordsPerPage(15);

        $cap = new Bvb_Grid_Column('Country');
        $cap->title('Country (Capital)')->class('width_150')->decorator('{{Country}} <em>({{Capital}})</em>');

        $name = new Bvb_Grid_Column('Name');
        $name->title('Capital')->hide(1);

        $continent = new Bvb_Grid_Column('Continent');
        $continent->title('Continent');

        $population = new Bvb_Grid_Column('Population');
        $population->title('Population')->class('width_80');

        $governmentForm = new Bvb_Grid_Column('GovernmentForm');
        $governmentForm->title('Government Form');

        $headState = new Bvb_Grid_Column('HeadOfState');
        $headState->title('Head Of State');

        $grid->updateColumns($cap, $name, $continent, $population, $governmentForm, $headState);

        $this->view->pages = $grid->deploy();
        $this->render('index');
    }

}