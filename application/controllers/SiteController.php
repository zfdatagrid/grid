<?php

/**
 * @author mascker
 */

include 'application/models/Model.php';

class SiteController extends Zend_Controller_Action
{

    private $_db;


    /**
     * If a action don't exist, just redirect to the basic
     *
     * @param string $name
     * @param array $var
     */
    function __call ($name, $var)
    {
        $this->_redirect('default/site/basic', array('exit' => 1));
        return false;
    }


    /**
     * I think this is needed for something. can't remember
     *
     */
    function init ()
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
    function indexAction ()
    {
        $this->_forward('basic');
    }


    /**
     * Show the source code for this controller
     *
     */
    function codeAction ()
    {
        $this->render('code');
    }


    /**
     * Simplify the datagrid creation process
     * @return Bvb_Grid_Deploy_Table
     */
    function grid ($id = '')
    {
        $config = new Zend_Config_Ini('./application/grids/grid.ini', 'production');
        $grid = Bvb_Grid_Data::factory('Bvb_Grid_Deploy_Table', $config, $id);
        $grid->setEscapeOutput(false);
        #$grid->setCache(array('use' => array('form'=>false,'db'=>false), 'instance' => Zend_Registry::get('cache'), 'tag' => 'grid'));
        return $grid;
    }


    /**
     * A simple usage of advanced filters. Every time you change a filter, the system automatically
     *runs a query to the others filters, making sure they don't allow you to filter for a record that is not in the database
     */
    function filtersAction ()
    {

        $grid = $this->grid();

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($this->_db->select()->from('Country', array('Name', 'Continent', 'Population', 'LifeExpectancy', 'GovernmentForm', 'HeadOfState'))));

        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('Name', array('distinct' => array('field' => 'Name', 'name' => 'Name')));
        $filters->addFilter('Continent', array('distinct' => array('field' => 'Continent', 'name' => 'Continent')));
        $filters->addFilter('LifeExpectancy', array('distinct' => array('field' => 'LifeExpectancy', 'name' => 'LifeExpectancy')));
        $filters->addFilter('GovernmentForm', array('distinct' => array('field' => 'GovernmentForm', 'name' => 'GovernmentForm')));
        $filters->addFilter('HeadOfState');
        $filters->addFilter('Population');

        $grid->addFilters($filters);

        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    /**
     * Adding extra columns to a datagrid. They can be at left or right.
     * Also notice that you can use fields values to populate the fields by surrounding the field name with {{}}
     *
     */
    function extraAction ()
    {

        $grid = $this->grid();

        $select = $this->_db->select()->from(array('c' => 'Country'), array('country' => 'Name', 'Continent', 'Population', 'GovernmentForm', 'HeadOfState'))->join(array('ct' => 'City'), 'c.Capital = ct.ID', array('city' => 'Name'));

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $grid->updateColumn('country', array('title' => 'Country (Capital)', 'class' => 'hideInput', 'decorator' => '{{country}} <em>({{city}})</em>'));
        $grid->updateColumn('city', array('title' => 'Capital', 'hide' => 1));
        $grid->updateColumn('Continent', array('title' => 'Continent'));
        $grid->updateColumn('Population', array('title' => 'Population', 'class' => 'width_80'));
        $grid->updateColumn('LifeExpectancy', array('title' => 'Life E.', 'class' => 'width_50'));
        $grid->updateColumn('GovernmentForm', array('title' => 'Government Form'));
        $grid->updateColumn('HeadOfState', array('title' => 'Head Of State', 'hide' => 1));

        $extra = new Bvb_Grid_Extra_Columns();
        $extra->position('right')->name('Right')->decorator("<input class='input_p'type='text' value=\"{{Population}}\" size=\"3\" name='number[]'>");

        $esquerda = new Bvb_Grid_Extra_Columns();
        $esquerda->position('left')->name('Left')->decorator("<input  type='checkbox' name='number[]'>");

        $grid->addExtraColumns($extra, $esquerda);


        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    function arrayAction ()
    {
        $array = array(array('Marcel', '12', 'M'), array('Katty', '34', 'F'), array('Richard', '87', 'M'), array('Dany', '33', 'F'));

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Array($array, array('nome', 'idade', 'sexo')));
        $this->view->pages = $grid->deploy();
        $this->render('index');

    }


    function csvAction ()
    {

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Csv('media/files/grid.csv'));
        $grid->sqlexp = array('Population' => array('functions' => array('SUM'), 'value' => 'Population'));

        $form = new Bvb_Grid_Form();
        #$form->setIsPerformCrudAllowed(false);
        $form->setAdd(1)->setEdit(1)->setDelete(1)->setAddButton(1);
        #$form->addElement('text','my');



        $grid->setForm($form);


        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    function jsonAction ()
    {

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Json('media/files/json.json', 'rows'));
        /*
        $grid->sqlexp = array ('Population' => array ('functions' => array ('MIN','AVG'), 'value' => 'Population' ) );

         $filters = new Bvb_Grid_Filters();
        $filters->addFilter('ID', array('distinct' => array('field' => 'ID', 'name' => 'ID')))
        ->addFilter('CountryCode', array('distinct' => array('field' => 'CountryCode', 'name' => 'CountryCode')))
        ->addFilter('Population');

        $grid->addFilters($filters);

*/
        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    function feedAction ()
    {
        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Xml('http://zfdatagrid.com/feed/', 'channel,item'));

        $grid->setPagination(10);

        $grid->updateColumn('title', array('decorator' => '<a href="{{link}}">{{title}}</a>', 'style' => 'width:200px;'));
        $grid->updateColumn('pubDate', array('class' => 'width_200'));

        $grid->setGridColumns(array('title', 'comments', 'pubDate'));

        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    /**
     * The 'most' basic example.
     */
    function basicAction ()
    {
        $grid = $this->grid();
        $select = $this->_db->select()->from('Country');
        #$grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->query($select);

        #$grid->setClassCellCondition('Population',"'{{Population}}' > 200000","red",'green');
        #$grid->addClassCellCondition('Population',"'{{Population}}' < 200000","green");
        #$grid->setClassRowCondition("'{{Population}}' > 20000","green",'orange');


        $grid->setDetailColumns();
        $grid->setGridColumns(array('Name', 'Continent', 'Population', 'LocalName', 'GovernmentForm'));

        #$grid->updateColumn('Name',array('helper'=>array('name'=>'formText','params'=>array('[{{ID}}]','{{Name}}'))));
        $grid->setSqlExp( array ('Population' => array ('functions' => array ('SUM' )) ));


        $this->view->pages = $grid->deploy();

        $this->render('index');
    }


    /**
     * The 'most' basic example.
     */
    function excelAction ()
    {
        $grid = $this->grid();

        $grid->setSource(new Bvb_Grid_Source_PHPExcel_Reader_Excel2007(getcwd() . '/1.xlsx', 'Sheet1'));
        $this->view->pages = $grid->deploy();
        $this->render('index');

    }


    function joinAction ()
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


    /**
     * Using a model
     */
    function crudAction ()
    {
        $grid = $this->grid('barcelos_');
        #$grid->setSource(new Bvb_Grid_Source_Zend_Table(new Bugs()));
        $grid->query(new Bugs());
        $grid->setColumnsHidden(array('bug_id', 'next', 'time', 'verified_by'));

        $form = new Bvb_Grid_Form();
        $form->setAdd(1)->setEdit(1)->setDelete(1)->setAddButton(1);

        #$form->setAllowedFields(array('times','nexst'));
        #$form->setDisallowedFields(array('time','next'));
        #$form->setFieldsBasedOnQuery(false);


        #$form->setIsPerformCrudAllowed(false);
        //$form->setIsPerformCrudAllowedForAddition(true);
        //$form->setIsPerformCrudAllowedForEdition(true);
        //$form->setIsPerformCrudAllowedForDeletion(false);
        //$grid->setDeleteConfirmationPage(true);

        $grid->setForm($form);

        $grid->setDeleteConfirmationPage(true);
        $this->view->pages = $grid->deploy();

        $this->render('index');
    }


    /**
     * This demonstrates how easy it is for us to use our own templates (Check the grid function at the page top)
     *
     */
    function templateAction ()
    {

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($this->_db->select()->from('City')));
        $grid->setNoFilters(1)->setPagination(14)->setTemplate('outside', 'table');
        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    /**
     * This example allow you to create an horizontal row, for every distinct value from a field
     *
     */
    function hrowAction ()
    {

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($this->_db->select()->from('Country', array('Name', 'Continent', 'Population', 'LifeExpectancy', 'GovernmentForm', 'HeadOfState'))));
        $grid->setNoFilters(1);
        $grid->setNoOrder(1);

        $grid->setPagination(1200);

        $grid->updateColumn('Name', array('title' => 'Country'));
        $grid->updateColumn('Continent', array('title' => 'Continent', 'hRow' => 1));
        $grid->updateColumn('Population', array('title' => 'Population', 'class' => 'width_80'));
        $grid->updateColumn('LifeExpectancy', array('title' => 'Life E.', 'class' => 'width_50', 'decorator' => '<b>{{LifeExpectancy}}</b>'));
        $grid->updateColumn('GovernmentForm', array('title' => 'Government Form'));
        $grid->updateColumn('HeadOfState', array('title' => 'Head Of State'));

        $extra = new Bvb_Grid_ExtraColumns();
        $extra->position('right')->name('Right')->decorator("<input class='input_p'type='text' value=\"{{Population}}\" size=\"3\" name='number[]'>");

        $esquerda = new Bvb_Grid_ExtraColumns();
        $esquerda->position('left')->name('Left')->decorator("<input  type='checkbox' name='number[]'>");

        $grid->addExtraColumns($extra, $esquerda);


        $this->view->pages = $grid->deploy();
        $this->render('index');
    }


    /**
     * If you don't like to work with array when adding columns, you can work by dereferencing objects
     *
     */
    function columnAction ()
    {

        $grid = $this->grid();
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($this->_db->select()->from(array('c' => 'Country'), array('Country' => 'Name', 'Continent', 'Population', 'GovernmentForm', 'HeadOfState'))->join(array('ct' => 'City'), 'c.Capital = ct.ID', array('Capital' => 'Name'))));
        $grid->setPagination(15);

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



    function ofcAction ()
    {

        $this->view->graphs = $allowedGraphs = array('line', 'bar', 'bar_glass', 'bar_3d', 'bar_filled', 'pie', 'mixed');

        $type = $this->_getParam('type');

        if ( ! in_array($type, $allowedGraphs) ) {
            $type = 'bar_glass';
        }

        $this->getRequest()->setParam('_exportTo', 'ofc');

        $grid = $this->grid();
        $grid->setChartType($type);

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

        $this->view->pages = $grid->deploy();
        $this->render('index');
    }

}