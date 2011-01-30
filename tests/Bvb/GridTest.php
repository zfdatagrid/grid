<?php

class Bvb_GridTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    protected $grid;
    protected $controller;
    protected $db;

    public function setUp()
    {
        // Assign and instantiate in one step:
        $this->bootstrap = new Zend_Application(
                'general',
                APPLICATION_PATH . '/config.ini'
        );

        $this->controller = Zend_Controller_Front::getInstance();
        $this->db = $this->bootstrap->getBootstrap()->getPluginResource('db')->getDbAdapter();

        $this->controller->setControllerDirectory(APPLICATION_PATH . '/application/controllers');
        $this->controller->setDefaultModule('defualt');
        $this->controller->setDefaultControllerName('site');


        $this->grid = Bvb_Grid::factory('Table');
        $this->grid->setParam('module', 'default');
        $this->grid->setParam('controller', 'site');
        $this->grid->setView(new Zend_View(array()));

        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($this->db->select()->from('unit')));

        parent::setUp();
    }

    public function deployGrid($select = null)
    {
        if ($select === null) {
            $select = $this->db->select()->from('unit');
        }

        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);

        return $grid;
    }

    public function testLoad()
    {
        $this->assertTrue($this->grid instanceof Bvb_Grid);
    }

    public function testDefaultConfig()
    {
        $this->assertEquals(count(Bvb_Grid::getDefaultConfig()), 0);
    }

    public function testParams()
    {
        $this->assertFalse($this->grid->getParam('empty'));
        $this->grid->setParam('empty', 1);
        $this->assertEquals($this->grid->getParam('empty'), 1);
        $this->assertEquals(count($this->grid->removeAllParams()->getAllParams()), 0);
        $this->assertEquals($this->grid->addParam('test', 'value')->getParam('test'), 'value');
        $this->assertEquals($this->grid->removeParam('test')->getParam('test'), false);
        $this->assertEquals($this->grid->addParams(array('1', '2', '3'))->getAllParams(), array('1', '2', '3'));
    }

    public function testExport()
    {
        $this->assertEquals($this->grid->setExport(array())->getExport(), array());
        $this->assertEquals(count($this->grid->addExport('csv', array())), 1);
    }

    public function testPaginationRelated()
    {
        $this->assertEquals($this->grid->getRecordsPerPage(), 15); //Default is 15
        $this->assertEquals($this->grid->setRecordsPerPage(12)->getRecordsPerPage(), 12);
        $this->assertInternalType('array',$this->grid->getPaginationInterval());
        $this->assertEquals(count($this->grid->getPaginationInterval()),0);
        $this->assertEquals($this->grid->setPaginationInterval(array(10 => 10))->getPaginationInterval(), array('10' => 10));
    }

    public function testHiddenFields()
    {
        $this->grid->updateColumn('test', array('hidden' => true));
        $this->assertFalse($this->grid->getField('non-existing'));
        $this->grid->updateColumn('Name', array('hidden' => true));
        $field = $this->grid->getField('Name');
        $this->assertTrue($field['hidden']);
        $this->assertInternalType('array', $field);
    }

   public function testCharEncoding()
   {
       $this->grid->setcharEncoding('UTF8');
       $this->assertEquals($this->grid->getCharEncoding(),'UTF8');
   }

   public function testAddObjectColumns()
   {
       $name = new Bvb_Grid_Column('Name');
       $name->title('teste');
       $this->grid->updateColumns($name);
       $this->assertInternalType('array',$this->grid->getField('Name'));
   }

}
