<?php
class siteController extends Zend_Controller_Action {


    function __call($name,$var)
    {
        $this->_redirect('default/site/basic',array('exit'=>1));
    }


    /**
     * TEST FUNCTION
     * DO NOT USE
     *
     */
    function teste2Action()
    {
        include "models/Grid.php";

        $t = new Grid();
        $lista = Bvb::Grid('table');
        $lista->selectFromDbTable($t);
        $lista->setTemplate('select');
        $this->view->pages = $lista->deploy();
        $this->render ('index') ;
    }

    function groupAction()
    {
        $grid = Bvb::Grid('table');
        $grid->from('crud')
        ->addColumn('id')
        ->addColumn('firstname')
        ->addColumn('lastname',array('title'=>'Last name (Grouped)'))
        ->addColumn('age',array('sqlexp'=>'avg','title'=>'Age Average','format'=>'number','class'=>'center'))
        ->groupby('lastname')
        ->noFilters(1)
        ->setTemplate('select');

        $this->view->pages = $grid->deploy();
        $this->render ('index') ;
    }


    function codeAction()
    {
        $this->render('code');
    }


    function init()
    {
        $conf = Bvb::get('config');
        $this->view->url = $conf->site->url;
    }

    function indexAction()
    {
        $this->_forward('basic');
    }

    function filtersAction () {

        $lista = Bvb::Grid('table');

        $lista->from ( 'Country c INNER JOIN City ct ON c.Capital=ct.ID ')
        ->table (array('c'=>'Country','ct'=>'City'))
        ->setPagination(15)
        ->addColumn('c.name',array('title'=>'Country (Capital)','class'=>'hideInput','decorator'=>'{{c.name}} <em>({{ct.Name}})</em>'))
        ->addColumn('ct.Name',array('title'=>'Capital','hide'=>1))
        ->addColumn('c.continent',array('title'=>'Continent'))
        ->addColumn('c.Population',array('title'=>'Population','class'=>'width_80','eval'=>"number_format('{{c.Population}}');"))
        ->addColumn('c.LifeExpectancy',array('title'=>'Life E.','class'=>'width_50'))
        ->addColumn('c.GovernmentForm',array('title'=>'Government Form' ))
        ->addColumn('c.HeadOfState',array('title'=>'Head Of State','searchType'=>'='))
        ->sqlexp(array('c.LifeExpectancy'=>'AVG','c.Population'=>'SUM'));



        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('firstname')
        ->addFilter('c.name', array('distinct'=>array('field'=>'c.name','name'=>'c.name')))
        ->addFilter('ct.Name' , array('distinct'=>array('field'=>'ct.Name','name'=>'ct.Name')))
        ->addFilter('c.continent', array('distinct'=>array('field'=>'c.continent','name'=>'c.continent')))
        ->addFilter('c.LifeExpectancy', array('distinct'=>array('field'=>'c.LifeExpectancy','name'=>'c.LifeExpectancy')))
        ->addFilter('c.GovernmentForm', array('distinct'=>array('field'=>'c.GovernmentForm','name'=>'c.GovernmentForm')))
        ->addFilter('c.HeadOfState')
        ->addFilter('c.Population');

        $lista->addFilters($filters);


        $this->view->pages = $lista->deploy();
        $this->render ('index') ;
    }

    /*
    function exportAction () {


    $lista = Bvb::Grid('table');
    $lista->from = 'Country c INNER JOIN City ct ON c.Capital=ct.ID ';
    $lista->table = array('c'=>'Country','ct'=>'City');
    $lista->data['pagination']['per_page'] = 15;

    $lista->addColumn('c.name',array('title'=>'Country (Capital)','class'=>'hideInput','decorator'=>'{{c.name}} <em>({{ct.Name}})</em>'));
    $lista->addColumn('ct.Name',array('title'=>'Capital','hide'=>1));
    $lista->addColumn('c.continent',array('title'=>'Continent'));
    $lista->addColumn('c.Population',array('title'=>'Population','class'=>'width_80','eval'=>"number_format('{{c.Population}}');"));
    $lista->addColumn('c.LifeExpectancy',array('title'=>'Life E.','class'=>'width_50'));
    $lista->addColumn('c.GovernmentForm',array('title'=>'Government Form' ));

    $lista->sqlexp = array('c.LifeExpectancy'=>'AVG','c.Population'=>'SUM');

    $lista->filters  =array(
    'c.name' => array('distinct'=>array('field'=>'c.name','name'=>'c.name')),
    'ct.Name' => array('distinct'=>array('field'=>'ct.Name','name'=>'ct.Name')),
    'c.continent' => array('distinct'=>array('field'=>'c.continent','name'=>'c.continent')),
    'c.LifeExpectancy' => array('distinct'=>array('field'=>'c.LifeExpectancy','name'=>'c.LifeExpectancy')),
    'c.GovernmentForm' => array('distinct'=>array('field'=>'c.GovernmentForm','name'=>'c.GovernmentForm')),
    'c.Population'=>array(),
    );

    $lista->export = array('print','excel','pdf','word');

    $this->view->pages = $lista->deploy();
    $this->view->action = 'export';
    $this->render ('index') ;
    }
    */
    function joinsAction () {


        $lista = Bvb::Grid('table');
        $lista->from  ('Country c INNER JOIN City ct ON c.Capital=ct.ID ')
        ->table ( array('c'=>'Country','ct'=>'City'))
        ->order('c.Continent')
        ->limit(50);

        $lista->addColumn('c.Name',array('title'=>'Country (Capital)','class'=>'hideInput','decorator'=>'{{c.Name}} <em>({{ct.Name}})</em>'))
        ->addColumn('ct.Name',array('title'=>'Capital','hide'=>1))
        ->addColumn('c.Continent',array('title'=>'Continent','class'=>'width_120'))
        ->addColumn('c.Population',array('title'=>'Population','class'=>'width_80','format'=>array('number',array('dias'=>1))))
        ->addColumn('c.LifeExpectancy',array('title'=>'Life E.','class'=>'width_50'))
        ->addColumn('c.GovernmentForm',array('title'=>'Government Form' ))
        ->addColumn('c.HeadOfState',array('title'=>'Head Of State'));


        $this->view->pages = $lista->deploy();
        $this->render ('index') ;
    }

    function extraAction () {


        $lista = Bvb::Grid('table');
        $lista->from ('Country c INNER JOIN City ct ON c.Capital=ct.ID ')
        ->table (array('c'=>'Country','ct'=>'City'))
        ->noFilters(1);

        $lista->addColumn('c.name',array('title'=>'Country (Capital)','class'=>'hideInput','decorator'=>'{{c.name}} <em>({{ct.Name}})</em>'));
        $lista->addColumn('ct.Name',array('title'=>'Capital','hide'=>1));
        $lista->addColumn('c.continent',array('title'=>'Continent'));
        $lista->addColumn('c.Population',array('title'=>'Population','class'=>'width_80','eval'=>"number_format('{{c.Population}}');"));
        $lista->addColumn('c.LifeExpectancy',array('title'=>'Life E.','class'=>'width_50'));
        $lista->addColumn('c.GovernmentForm',array('title'=>'Government Form' ));
        $lista->addColumn('c.HeadOfState',array('title'=>'Head Of State', 'hide'=>1));

       

        $extra = new Bvb_Grid_ExtraColumns();
        $extra->position('right')
        ->name('Right')
        ->decorator("<input class='input_p'type='text' value=\"{{c.LifeExpectancy}}\" size=\"3\" name='number[]'>");

        $esquerda = new Bvb_Grid_ExtraColumns();
        $esquerda->position('left')
        ->name('Left')
        ->decorator("<input  type='checkbox' name='number[]'>");

        $lista->addExtraColumns($extra,$esquerda);



        $this->view->pages = $lista->deploy();
        $this->render ('index') ;
    }



    function crudAction () {
        $db = Bvb::get('db');


        $lista = Bvb::Grid('table');
        $lista->from('crud')
        ->order('id DESC ');

        $paises = $db->fetchCol("SELECT DISTINCT(Name) FROM Country ORDER BY Name ASC ");
        $language = $db->fetchCol("SELECT DISTINCT(Language) FROM CountryLanguage ORDER BY Language ASC");
        $age = range(0,75);

        $lista->addColumn('id',array('title'=>'ID','hide'=>1));
        $lista->addColumn('firstname',array('title'=>'First Name'));
        $lista->addColumn('lastname',array('title'=>'Last Name'));
        $lista->addColumn('title',array('title'=>'Title'));
        $lista->addColumn('age',array('title'=>'Age'));
        $lista->addColumn('language',array('title'=>'Language'));
        $lista->addColumn('date_added',array('title'=>'Added','format'=>'date'));
        $lista->addColumn('country',array('title'=>'Country'));


        $form = new Bvb_Grid_Form();
        $form->add(1)
        ->edit(1)
        ->button(1)
        ->user(1)
        ->force(array('date_added'=>date('Y-m-d H:i:s')));


        $fAdd = new Bvb_Grid_Form_Column('firstname');
        $fAdd->title('First name')
        ->validators(array('EmailAddress','Int'))
        ->description('Insert you email address')
        ->filters(array('StripTags'));

        $lastName = new Bvb_Grid_Form_Column('lastname');
        $lastName->title('Last name')
        ->values($paises);


        $lang = new Bvb_Grid_Form_Column('language');
        $lang->title('Language')
        ->description('Your language')
        ->values($language);

        $form->addColumns($fAdd,$lastName,$lang);

        $lista->addForm($form);

        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('firstname')
        ->addFilter('lastname')
        ->addFilter('age',array('distinct'=>array('name'=>'age','field'=>'age')))
        ->addFilter('country',array('distinct'=>array('name'=>'country','field'=>'country')))
        ->addFilter('language',array('distinct'=>array('name'=>'language','field'=>'language')))
        ->addFilter('title',array('distinct'=>array('name'=>'title','field'=>'title')));

        $lista->addFilters($filters);


        $this->view->pages = $lista->deploy();
        $this->render ('index') ;
    }


    /**
     * TESTE FUNCTION
     * DO NOT USE
     *
     */
    function selectAction () {
        $lista = Bvb::Grid('table');

        $db = Bvb::get('db');

        $select = $db->select()
        ->from(array('p' => 'products'),
        array('product_id', 'product_name'));

        $lista->queryFromDbSelect($select);

        $this->view->pages = $lista->deploy();
        $this->view->action = 'basic';
        $this->render ('index') ;
    }



    function basicAction () {

        $lista = Bvb::Grid('table');
        $lista->from ('City');
        $this->view->pages = $lista->deploy();
        $this->render ('index') ;
    }

    function templateAction () {

        $lista = Bvb::Grid('table');
        $lista->noFilters(1)
        ->from('City')
        ->setTemplate('select','table');

        $this->view->pages = $lista->deploy();
        $this->render ('index') ;
    }


    function hrowAction () {

        $lista = Bvb::Grid('table');
        $lista->from ('Country c INNER JOIN City ct ON c.Capital=ct.ID ')
        ->table(array('c'=>'Country','ct'=>'City'))
        ->order (' c.Continent, c.Name');
        #->noFilters(1);
        #->noOrder(1);

        $lista->setPagination(100);

        $lista->addColumn('c.Name AS cap',array('title'=>'Country (Capital)','decorator'=>'{{c.Name}} <em>({{ct.Name}})</em>'));
        $lista->addColumn('ct.Name',array('title'=>'Capital','hide'=>1));
        $lista->addColumn('c.Continent',array('title'=>'Continent','hRow'=>1));
        $lista->addColumn('c.Population',array('title'=>'Population','class'=>'width_80'));
        $lista->addColumn('c.LifeExpectancy',array('title'=>'Life E.','class'=>'width_50'));
        $lista->addColumn('c.GovernmentForm',array('title'=>'Government Form' ));
        $lista->addColumn('c.HeadOfState',array('title'=>'Head Of State'));


        $this->view->pages = $lista->deploy();
        $this->render ('index') ;
    }

    function columnAction () {

        $lista = Bvb::Grid('table');
        $lista->from ('Country c INNER JOIN City ct ON c.Capital=ct.ID ')
        ->table(array('c'=>'Country','ct'=>'City'))
        ->order (' c.Continent, c.Name')
        ->setPagination(20);
        #->noFilters(1);
        #->noOrder(1);

        

        $cap = new Bvb_Grid_Column('c.Name AS cap');
        $cap->title('Country (Capital)')
        ->decorator('{{c.Name}} <em>({{ct.Name}})</em>');

        $name = new Bvb_Grid_Column('ct.Name');
        $name->title('Capital')
        ->hide(1);

        $continent = new Bvb_Grid_Column('c.Continent');
        $continent->title('Continent');

        $population = new Bvb_Grid_Column('c.Population');
        $population->title('Population')
        ->class('width_80');

        $lifeExpectation = new Bvb_Grid_Column('c.LifeExpectancy');
        $lifeExpectation->title('Life E.')
        ->class('width_50');

        $governmentForm = new Bvb_Grid_Column('c.GovernmentForm');
        $governmentForm->title('Government Form');

        $headState = new Bvb_Grid_Column('c.HeadOfState');
        $headState->title('Head Of State');

        $lista->addColumns($cap,$name,$continent,$population,$lifeExpectation,$governmentForm,$headState);

        
        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('c.Name',array('distinct'=>array('field'=>'c.Name AS cap','name'=>'c.Name AS cap')))
        ->addFilter('ct.Name' , array('distinct'=>array('field'=>'ct.Name','name'=>'ct.Name')))
        ->addFilter('c.Continent', array('distinct'=>array('field'=>'c.Continent','name'=>'c.Continent')))
        ->addFilter('c.LifeExpectancy', array('distinct'=>array('field'=>'c.LifeExpectancy','name'=>'c.LifeExpectancy')))
        ->addFilter('c.GovernmentForm', array('distinct'=>array('field'=>'c.GovernmentForm','name'=>'c.GovernmentForm')))
        ->addFilter('c.HeadOfState')
        ->addFilter('c.Population');

        $lista->addFilters($filters);
        
        $this->view->pages = $lista->deploy();
        $this->render ('index') ;
    }
}
