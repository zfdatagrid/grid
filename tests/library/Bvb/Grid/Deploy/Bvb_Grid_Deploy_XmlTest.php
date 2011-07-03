<?php

class Bvb_Grid_Deploy_XmlTest extends Bvb_GridTestHelper {

    public function setUp()
    {

        parent::setUp();
        $this->grid = Bvb_Grid::factory('xml');
        $this->grid->setParam('module', 'default');
        $this->grid->setParam('controller', 'site');
        $this->grid->setView(new Zend_View(array()));
        $this->grid->setExport(array('xml'));
    }

    public function testSaveFile()
    {
        $this->grid->setDeployOption('name', 'barcelos');
        $this->grid->setDeployOption('save', '1');
        $this->grid->setDeployOption('dir', $this->_temp);

        $this->grid->setSource(new Bvb_Grid_Source_Zend_Table(new Bugs()));
        $this->grid->deploy();

        $this->assertTrue(file_exists($this->_temp . 'barcelos.xml'));
        unlink($this->_temp . 'barcelos.xml');
    }

    public function testNotSaveFile()
    {
        $this->grid->setDeployOption('name', 'barcelos');
        $this->grid->setDeployOption('save', '0');
        $this->grid->setDeployOption('display', '1');
        $this->grid->setDeployOption('dir', $this->_temp);

        $this->grid->setSource(new Bvb_Grid_Source_Zend_Table(new Bugs()));
        $this->grid->deploy();

        $this->assertFalse(file_exists($this->_temp . 'barcelos.xml'));
    }

    public function testDisplayAndSave()
    {
        $this->grid->setDeployOption('name', 'barcelos');
        $this->grid->setDeployOption('save', '1');
        $this->grid->setDeployOption('display', '1');
        $this->grid->setDeployOption('dir', $this->_temp);

        $this->grid->setSource(new Bvb_Grid_Source_Zend_Table(new Bugs()));
        $this->grid->deploy();
        die();

        $this->assertTrue(file_exists($this->_temp . 'barcelos.xml'));
        unlink($this->_temp . 'barcelos.xml');
    }

}
