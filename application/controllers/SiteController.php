<?php



class SiteController extends Zend_Controller_Action
{


    function __call($name, $var)
    {

        $this->_redirect ( 'default/site/basic', array ('exit' => 1 ) );
    }



    function grid()
    {

        $export = $this->getRequest ()->getParam ( 'export' );
        
        $db = Zend_Registry::get ( 'db' );
        
        switch ($export)
        {
            case 'excel' :
                $grid = new Bvb_Grid_Deploy_Excel ( $db, 'Grid Example', 'media/temp' );
                #$grid->cache = array('use'=>0,'instance'=>Zend_Registry::get('cache'),'tag'=>'grid');
                break;
            case 'word' :
                $grid = new Bvb_Grid_Deploy_Word ( $db, 'Grid Example', 'media/temp' );
                #$grid->cache = array('use'=>0,'instance'=>Zend_Registry::get('cache'),'tag'=>'grid');
                break;
            case 'wordx' :
                $grid = new Bvb_Grid_Deploy_Wordx ( $db, 'Grid Example', 'media/temp' );
                #$grid->cache = array('use'=>0,'instance'=>Zend_Registry::get('cache'),'tag'=>'grid');
                break;
            case 'pdf' :
                $grid = new Bvb_Grid_Deploy_Pdf ( $db, 'Grid Example', 'media/temp' );
                #$grid->cache = array('use'=>0,'instance'=>Zend_Registry::get('cache'),'tag'=>'grid');
                break;
            case 'print' :
                $grid = new Bvb_Grid_Deploy_Print ( $db, 'Grid Example' );
                #$grid->cache = array('use'=>0,'instance'=>Zend_Registry::get('cache'),'tag'=>'grid');
                break;
            default :
                $grid = new Bvb_Grid_Deploy_Table ( $db );
                $grid->addTemplateDir ( 'My/Template/Table', 'My_Template_Table', 'table' );
                $grid->addElementDir ( 'My/Validate', 'My_Validate', 'validator' );
                $grid->addElementDir ( 'My/Filter', 'My_Filter', 'filter' );
                $grid->addFormatterDir ( 'My/Formatter', 'My_Formatter' );
                $grid->imagesUrl = $this->getRequest ()->getBaseUrl () . '/public/images/';
                $grid->cache = array ('use' => 0, 'instance' => Zend_Registry::get ( 'cache' ), 'tag' => 'grid' );
                
                break;
        }
        
        return $grid;
    }


    function imagesAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'images' )
        ->addColumn ( 'url', array ('decorator' => '<a href="{{url}}"><img src="{{url}}" border="0"></a>', 'title' => 'Katie Melua - Image Galerie' ) )
        ->noOrder ( 1 )
        ->setPagination ( 10000 )
        ->setTemplate ( 'images' );
        
        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    /**
     * TEST FUNCTION
     * DO NOT USE
     *
     */
    function teste2Action()
    {

        include "models/Grid.php";
        
        $t = new Grid ( );
        $grid = $this->grid ( 'table' );
        $grid->selectFromDbTable ( $t );
        $grid->setTemplate ( 'select' );
        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    function groupAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'crud' )->addColumn ( 'id' )->addColumn ( 'firstname' )->addColumn ( 'lastname', array ('title' => 'Last name (Grouped)' ) )->addColumn ( 'age', array ('sqlexp' => 'avg', 'title' => 'Age Average', 'format' => 'currency', 'class' => 'center' ) )->groupby ( 'lastname' )->noFilters ( 1 )->setTemplate ( 'select' );
        
        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    function codeAction()
    {

        $this->render ( 'code' );
    }


    function init()
    {

        $this->view->url = Zend_Registry::get ( 'config' )->site->url;
    }


    function indexAction()
    {

        $this->_forward ( 'basic' );
    }


    function filtersAction()
    {

        $grid = $this->grid ( 'table' );
        
        $grid->setPagination ( 15 );
        
        $grid->from ( 'Country ' )->addColumn ( 'Name', array ('title' => 'Country', 'class' => 'width_200' ) )->addColumn ( 'continent', array ('title' => 'Continent' ) )->addColumn ( 'Population', array ('title' => 'Population', 'class' => 'width_80', 'eval' => "number_format('{{Population}}');" ) )->addColumn ( 'LifeExpectancy', array ('title' => 'Life E.', 'class' => 'width_50' ) )->addColumn ( 'GovernmentForm', array ('title' => 'Government Form', 'searchType' => '=' ) )->addColumn ( 'HeadOfState', array ('title' => 'Head Of State', 'searchType' => '=' ) )->sqlexp ( array ('LifeExpectancy' => 'AVG', 'Population' => 'SUM' ) );
        

        $filters = new Bvb_Grid_Filters ( );
        $filters->addFilter ( 'Name', array ('distinct' => array ('field' => 'Name', 'name' => 'Name' ) ) )->addFilter ( 'continent', array ('distinct' => array ('field' => 'continent', 'name' => 'continent' ) ) )->addFilter ( 'LifeExpectancy', array ('distinct' => array ('field' => 'LifeExpectancy', 'name' => 'LifeExpectancy' ) ) )->addFilter ( 'GovernmentForm', array ('distinct' => array ('field' => 'GovernmentForm', 'name' => 'GovernmentForm' ) ) )->addFilter ( 'HeadOfState' )->addFilter ( 'Population' );
        
        $grid->addFilters ( $filters );
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    function joinsAction()
    {

        
        $grid = $this->grid ( 'table' );
        $grid->from ( 'Country c INNER JOIN City ct ON c.Capital=ct.ID ' )->table ( array ('c' => 'Country', 'ct' => 'City' ) )->order ( 'c.Continent' )->limit ( 10 );
        
        $grid->addColumn ( 'c.Name', array ('title' => 'Country (District)', 'class' => 'hideInput', 'decorator' => '{{c.Name}} <em>({{ct.District}})</em>' ) )->addColumn ( 'ct.District', array ('title' => 'District', 'hide' => 1 ) )->addColumn ( 'c.Continent', array ('title' => 'Continent', 'class' => 'width_120' ) )->addColumn ( 'c.Population', array ('title' => 'Population', 'class' => 'width_80', 'format' => array ('number', array ('dias' => 1 ) ) ) )->addColumn ( 'c.LifeExpectancy', array ('title' => 'Life E.', 'class' => 'width_50' ) )->addColumn ( 'c.GovernmentForm', array ('title' => 'Government Form' ) )->addColumn ( 'c.HeadOfState', array ('title' => 'Head Of State' ) );
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    function extraAction()
    {

        
        $grid = $this->grid ( 'table' );
        $grid->from ( 'Country c INNER JOIN City ct ON c.Capital=ct.ID ' )->table ( array ('c' => 'Country', 'ct' => 'City' ) )->noFilters ( 1 );
        
        $grid->addColumn ( 'c.name', array ('title' => 'Country (Capital)', 'class' => 'hideInput', 'decorator' => '{{c.name}} <em>({{ct.Name}})</em>' ) );
        $grid->addColumn ( 'ct.Name', array ('title' => 'Capital', 'hide' => 1 ) );
        $grid->addColumn ( 'c.continent', array ('title' => 'Continent' ) );
        $grid->addColumn ( 'c.Population', array ('title' => 'Population', 'class' => 'width_80', 'eval' => "number_format('{{c.Population}}');" ) );
        $grid->addColumn ( 'c.LifeExpectancy', array ('title' => 'Life E.', 'class' => 'width_50' ) );
        $grid->addColumn ( 'c.GovernmentForm', array ('title' => 'Government Form' ) );
        $grid->addColumn ( 'c.HeadOfState', array ('title' => 'Head Of State', 'hide' => 1 ) );
        

        $extra = new Bvb_Grid_ExtraColumns ( );
        $extra->position ( 'right' )->name ( 'Right' )->decorator ( "<input class='input_p'type='text' value=\"{{c.LifeExpectancy}}\" size=\"3\" name='number[]'>" );
        
        $esquerda = new Bvb_Grid_ExtraColumns ( );
        $esquerda->position ( 'left' )->name ( 'Left' )->decorator ( "<input  type='checkbox' name='number[]'>" );
        
        $grid->addExtraColumns ( $extra, $esquerda );
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    function crudAction()
    {

        $db = Zend_Registry::get ( 'db' );
        

        $grid = $this->grid ( 'table' );
        $grid->from ( 'crud' )->order ( 'id DESC ' );
        
        $paises = $db->fetchCol ( "SELECT DISTINCT(Name) FROM Country ORDER BY Name ASC " );
        $language = $db->fetchCol ( "SELECT DISTINCT(Language) FROM CountryLanguage ORDER BY Language ASC" );
        
        $grid->addColumn ( 'id', array ('title' => 'ID', 'hide' => 1 ) );
        $grid->addColumn ( 'firstname', array ('title' => 'First Name' ) );
        $grid->addColumn ( 'lastname', array ('title' => 'Last Name' ) );
        $grid->addColumn ( 'title', array ('title' => 'Title' ) );
        $grid->addColumn ( 'age', array ('title' => 'Age' ) );
        $grid->addColumn ( 'language', array ('title' => 'Language' ) );
        $grid->addColumn ( 'date_added', array ('title' => 'Added', 'format' => array ('date', 'en_US' ) ) );
        $grid->addColumn ( 'country', array ('title' => 'Country' ) );
        

        $form = new Bvb_Grid_Form ( );
        $form->add ( 1 )->button ( 1 )->delete ( 1 )->onAddForce ( array ('date_added' => date ( 'Y-m-d H:i:s' ) ) )->onEditForce ( array ('date_added' => date ( 'Y-m-d H:i:s' ) ) );
        

        #->onDeleteCascade(array('table'=>'teste','parentField'=>'age','childField'=>'op','operand'=>'='))
        


        $fAdd = new Bvb_Grid_Form_Column ( 'firstname' );
        $fAdd->title ( 'First name' )->validators ( array ('EmailAddress' ) )->description ( 'Insert you email address' );
        
        $lastName = new Bvb_Grid_Form_Column ( 'lastname' );
        $lastName->title ( 'Last name' );
        
        $country = new Bvb_Grid_Form_Column ( 'country' );
        $country->title ( 'Country' )->description ( 'Choose your Country' )->values ( $paises );
        

        $lang = new Bvb_Grid_Form_Column ( 'language' );
        $lang->title ( 'Language' )->description ( 'Your language' )->values ( $language );
        
        $form->addColumns ( $fAdd, $lastName, $lang, $country );
        
        $grid->addForm ( $form );
        
        $filters = new Bvb_Grid_Filters ( );
        $filters->addFilter ( 'firstname' )->addFilter ( 'lastname' )->addFilter ( 'age', array ('distinct' => array ('name' => 'age', 'field' => 'age' ) ) )->addFilter ( 'country', array ('distinct' => array ('name' => 'country', 'field' => 'country' ) ) )->addFilter ( 'language', array ('distinct' => array ('name' => 'language', 'field' => 'language' ) ) )->addFilter ( 'title', array ('distinct' => array ('name' => 'title', 'field' => 'title' ) ) );
        
        $grid->addFilters ( $filters );
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    /**
     * TESTE FUNCTION
     * DO NOT USE
     *
     */
    function selectAction()
    {

        $grid = $this->grid ( 'table' );
        
        $db = Zend_Registry::get ( 'db' );
        
        $select = $db->select ()->from ( array ('p' => 'products' ), array ('product_id', 'product_name' ) );
        
        $grid->queryFromDbSelect ( $select );
        
        $this->view->pages = $grid->deploy ();
        $this->view->action = 'basic';
        $this->render ( 'index' );
    }


    function basicAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'City' );
        
        $pdf = array ('logo' => 'public/images/logo.png', 'baseUrl' => '/grid/', 'title' => 'DataGrid Zend Framework', 'subtitle' => 'Easy and powerfull - (Demo document)', 'footer' => 'Downloaded from: http://www.petala-azul.com ', 'size' => 'a4', #letter || a4
'orientation' => '', #landscape || ''
'page' => 'Page N.' );
        

        $grid->setTemplate ( 'print', 'print', $pdf );
        $grid->setTemplate ( 'pdf', 'pdf', $pdf );
        $grid->setTemplate ( 'word', 'word', $pdf );
        $grid->setTemplate ( 'wordx', 'wordx', $pdf );
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    function templateAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->noFilters ( 1 )->from ( 'City' )->setTemplate ( 'outside', 'table' );
        
        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    function hrowAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'Country c INNER JOIN City ct ON c.Capital=ct.ID ' )->table ( array ('c' => 'Country', 'ct' => 'City' ) )->order ( ' c.Continent, c.Name' );
        #->noFilters(1);
        #->noOrder(1);
        


        $grid->setPagination ( 12 );
        
        $grid->addColumn ( 'c.Name AS cap', array ('title' => 'Country (Capital)', 'decorator' => '{{c.Name}} <em>({{ct.Name}})</em>' ) );
        $grid->addColumn ( 'ct.Name', array ('title' => 'Capital', 'hide' => 1 ) );
        $grid->addColumn ( 'c.Continent', array ('title' => 'Continent', 'hRow' => 1 ) );
        $grid->addColumn ( 'c.Population', array ('title' => 'Population', 'class' => 'width_80' ) );
        $grid->addColumn ( 'c.LifeExpectancy', array ('title' => 'Life E.', 'class' => 'width_50' ) );
        $grid->addColumn ( 'c.GovernmentForm', array ('title' => 'Government Form' ) );
        $grid->addColumn ( 'c.HeadOfState', array ('title' => 'Head Of State' ) );
        

        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }


    function columnAction()
    {

        $grid = $this->grid ( 'table' );
        $grid->from ( 'Country c INNER JOIN City ct ON c.Capital=ct.ID ' )->table ( array ('c' => 'Country', 'ct' => 'City' ) )->order ( ' c.Continent, c.Name' )->setPagination ( 20 );
        #->noFilters(1);
        #->noOrder(1);
        


        $cap = new Bvb_Grid_Column ( 'c.Name AS cap' );
        $cap->title ( 'Country (Capital)' )->decorator ( '{{c.Name}} <em>({{ct.Name}})</em>' );
        
        $name = new Bvb_Grid_Column ( 'ct.Name' );
        $name->title ( 'Capital' )->hide ( 1 );
        
        $continent = new Bvb_Grid_Column ( 'c.Continent' );
        $continent->title ( 'Continent' );
        
        $population = new Bvb_Grid_Column ( 'c.Population' );
        $population->title ( 'Population' )->class ( 'width_80' );
        
        $lifeExpectation = new Bvb_Grid_Column ( 'c.LifeExpectancy' );
        $lifeExpectation->title ( 'Life E.' )->class ( 'width_50' );
        
        $governmentForm = new Bvb_Grid_Column ( 'c.GovernmentForm' );
        $governmentForm->title ( 'Government Form' );
        
        $headState = new Bvb_Grid_Column ( 'c.HeadOfState' );
        $headState->title ( 'Head Of State' );
        
        $grid->addColumns ( $cap, $name, $continent, $population, $lifeExpectation, $governmentForm, $headState );
        

        $filters = new Bvb_Grid_Filters ( );
        $filters->addFilter ( 'c.Name', array ('distinct' => array ('field' => 'c.Name AS cap', 'name' => 'c.Name AS cap' ) ) )->addFilter ( 'ct.Name', array ('distinct' => array ('field' => 'ct.Name', 'name' => 'ct.Name' ) ) )->addFilter ( 'c.Continent', array ('distinct' => array ('field' => 'c.Continent', 'name' => 'c.Continent' ) ) )->addFilter ( 'c.LifeExpectancy', array ('distinct' => array ('field' => 'c.LifeExpectancy', 'name' => 'c.LifeExpectancy' ) ) )->addFilter ( 'c.GovernmentForm', array ('distinct' => array ('field' => 'c.GovernmentForm', 'name' => 'c.GovernmentForm' ) ) )->addFilter ( 'c.HeadOfState' )->addFilter ( 'c.Population' );
        
        $grid->addFilters ( $filters );
        
        $this->view->pages = $grid->deploy ();
        $this->render ( 'index' );
    }
}
