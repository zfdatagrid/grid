<?php

/**
 * Doctrine2 Controller.
 *
 * In order to use this controller, php >= 5.3 and Doctrine2 must be installed.
 * 
 * See http://www.doctrine-project.org/projects/orm/2.0/download/2.0.3 for more information.
 */
class Doctrine2Controller extends Zend_Controller_Action
{
    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * I think this is needed for something. can't remember
     *
     */
    public function init()
    {
        $this->view->url = Zend_Registry::get('config')->site->url;
        $this->view->action = $this->getRequest()->getActionName();
        header('Content-Type: text/html; charset=ISO-8859-1');
        Bvb_Grid_Deploy_Ofc::$url = Zend_Registry::get('config')->site->url;
        $this->em = $this->_initDoctrine2();
    }

    public function indexAction()
    {
        $em = $this->em;
        $grid = $this->grid();
        $qb = $em->getRepository('\My\Entity\Country')->createQueryBuilder('c');
        $qb->select('c.code', 'c.name', 'c.continent', 'c.population', 'c.localName', 'c.governmentForm')->orderBy('c.code', 'ASC');

        $grid->setUseKeyEventsOnFilters(true);

        $source = new Bvb_Grid_Source_Doctrine2($qb, $em);

        $grid->setSource($source);
        $grid->setColumnsHidden(array('code'));

        $script = "$(document).ready(function() {";
        foreach($grid->getVisibleFields() as $name) {
            $script .= "$(\"input#filter_$name\").autocomplete({focus: function(event, ui) {document.getElementById('filter_$name').value = ui.item.value }, source: '{$grid->getAutoCompleteUrlForFilter($name)}'});\n";
        }
        $script .= "});";
        $grid->getView()->headScript()->appendScript($script);

        $this->view->pages = $grid->deploy();

        $this->renderScript('site/index.phtml');
    }

    public function formAction()
    {
        $em = $this->em;
        $grid = $this->grid();
        $qb = $em->getRepository('\My\Entity\Bug')->createQueryBuilder('c');

        $source = new Bvb_Grid_Source_Doctrine2($qb, $em);

        $grid->setSource($source);

        $form = new Bvb_Grid_Form();
        $form->setBulkAdd(1)->setAdd(true)->setEdit(true)->setBulkDelete(true)->setBulkEdit(true)->setDelete(true)->setAddButton(true);
        $grid->setForm($form);

        $this->view->pages = $grid->deploy();

        $this->renderScript('site/index.phtml');
    }

    /**
     * Simplify the datagrid creation process
     * @return Bvb_Grid_Deploy_Table
     */
    public function grid($id = '')
    {
        $view = new Zend_View();
        $view->setEncoding('ISO-8859-1');
        $config = new Zend_Config_Ini('./application/grids/grid.ini', 'production');
        $grid = Bvb_Grid::factory('Table', $config, $id);
        $grid->setEscapeOutput(false);
        $grid->setExport(array('csv', 'excel', 'pdf'));
        $grid->setView($view);
        #$grid->saveParamsInSession(true);
        #$grid->setCache(array('enable' => array('form'=>false,'db'=>false), 'instance' => Zend_Registry::get('cache'), 'tag' => 'grid'));
        return $grid;
    }

    /**
     * init doctrine connection
     */
    private function _initDoctrine2()
    {
        require 'Doctrine/Common/ClassLoader.php';
        $classLoader = new \Doctrine\Common\ClassLoader('Doctrine');
        $classLoader->register(); // register on SPL autoload stack

        $config = new Doctrine\ORM\Configuration();
        $cache = new \Doctrine\Common\Cache\ArrayCache;

        $config->setMetadataCacheImpl($cache);
        $driverImpl = $config->newDefaultAnnotationDriver(APPLICATION_PATH . '/library/My/Entity');
        $config->setMetadataDriverImpl($driverImpl);
        $config->setQueryCacheImpl($cache);
        $config->setProxyDir(APPLICATION_PATH . '/library/My/Entity/Proxies');
        $config->setProxyNamespace('My\Entity\Proxies');

        $config->setAutoGenerateProxyClasses(true);
        $dbConfig = new Zend_Config_Ini(APPLICATION_PATH . '/application/config.ini', 'general');
        $dbConfig = $dbConfig->db->config;


        $connectionOptions = array(
            'driver' => 'pdo_mysql',
            'host' => $dbConfig->host,
            'dbname' => $dbConfig->dbname,
            'user' => $dbConfig->username,
            'password' => $dbConfig->password,
        );

        $em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config);

        return $em;
    }

}
