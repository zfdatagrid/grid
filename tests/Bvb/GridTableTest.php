<?php

class GridTableTest extends Bvb_GridTest
{

    public function testCountResults()
    {
        $this->deployGrid();
        $this->assertQueryCount("/div/table/tr", 19);
    }

    public function test16Results()
    {
        $select = $this->db->select()->from('unit')->limit(16);
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setRecordsPerPage(16);
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertQueryCount("/div/table/tr", 20);
    }

    public function testNoOrder()
    {
        $select = $this->db->select()->from('unit')->limit(16);
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setNoOrder(1);
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertNotQuery('/div/table/tr[1]/th[2]/a[2]/img');
    }

    public function testNoFilters()
    {
        $select = $this->db->select()->from('unit')->limit(16);
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setNoFilters(true);
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertQueryCount("/div/table/tr", 17);
    }

    public function testLeftExtraColumn()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $left = new Bvb_Grid_Extra_Column();
        $left->title('Left')->decorator('test')->name('test')->position('left');
        $this->grid->addExtraColumns($left);

        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertQueryContentContains("/div/table/tr[2]/th[1]", 'Left');
    }

    public function testRightExtraColumn()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $right = new Bvb_Grid_Extra_Column();
        $right->title('Right')->decorator('test')->name('test')->position('right');
        $this->grid->addExtraColumns($right);

        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertQueryContentContains("/div/table/tr[2]/th[16]", 'Right');
    }

    public function testExtraRows()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $rows = new Bvb_Grid_Extra_Rows();
        $rows->addRow('beforeHeader', array(array('colspan' => 15, 'class' => 'myclass', 'content' => 'my content')));
        $rows->addRow('beforePagination', array(array('colspan' => 9, 'content' => "This is an extra row added before pagination")));
        $this->grid->addExtraRows($rows);

        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertQueryContentContains("td", 'my content');
        $this->assertQueryContentContains("td", 'This is an extra row added before pagination');
    }

    public function testShowButtonToApplyFilters()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setUseKeyEventsOnFilters(false);
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertQuery("/div/table/tr[1]/td/div/button");
    }

    public function testApplyFiltersWithKeyEvents()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setUseKeyEventsOnFilters(true);
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertNotQuery("/div/table/tr[1]/td/div/button");
    }

    public function testDetailView()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setDetailColumns();
        $this->grid->setParam('gridDetail', 1);
        $this->grid->setParam('com', 'mode:view;[Code:AFG]');

        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertQueryContentContains("td", 'Afghanistan');
    }

    public function testDisplaySaveAndAddButton()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setParam('add', 1);

        $form = new Bvb_Grid_Form();
        $form->setView(new Zend_View());
        $form->setAdd(true)->setEdit(true)->setDelete(true)->setSaveAndAddButton(true);

        $this->grid->setForm($form);

        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertQuery("form");
        $this->assertQuery("[@id='saveAndAdd']");
    }

    public function testPlaceAtRecordPage()
    {
        $this->grid->placePageAtRecord('PRT', 'green');
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);

        $this->assertInternalType('array', $this->grid->getPlacePageAtRecord());
        $this->assertQueryContentContains('td', 'Portugal');
        $this->assertNotQueryContentContains('td', 'Brazil');
        $this->assertEquals($this->grid->getParam('start'),150);
    }

    public function testDisplayAddForm()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setParam('add', 1);

        $form = new Bvb_Grid_Form();
        $form->setView(new Zend_View());
        $form->setAdd(true)->setEdit(true)->setDelete(true);
        $this->grid->setForm($form);
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertQuery("form");
        $this->assertQuery("[@id='1-Name']");
    }

    public function testDisplayAddFormWithoutPermissions()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setParam('add', 1);
        $form = new Bvb_Grid_Form();
        $form->setView(new Zend_View());
        $form->setAdd(false)->setEdit(true)->setDelete(true);
        $this->grid->setForm($form);
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertNotQuery("form");
    }

    public function testDisplayEditForm()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setParam('edit', 1);
        $this->grid->setParam('comm','mode:edit;[Name:Portugal]');

        $form = new Bvb_Grid_Form();
        $form->setView(new Zend_View());
        $form->setAdd(true)->setEdit(true)->setDelete(true);

        $this->grid->setForm($form);

        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertQuery("form");
        $this->assertQuery("[@id='1-Name']");
    }

    public function testDisplayEditFormWithNoPermissions()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setParam('edit', 1);
        $this->grid->setParam('comm','mode:edit;[Name:Portugal]');

        $form = new Bvb_Grid_Form();
        $form->setView(new Zend_View());
        $form->setAdd(true)->setEdit(false)->setDelete(true);
        $this->grid->setForm($form);
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertNotQuery("form");
    }


    public function testGetForm()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $form = new Bvb_Grid_Form();
        $form->setView(new Zend_View());
        $form->setAdd(true)->setEdit(true)->setDelete(false);
        $this->grid->setForm($form);

        $this->assertTrue($this->grid->getForm() instanceof Zend_Form);
    }


    public function testDisplayDeleteWithNoPermissions()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setParam('gridDetail', 1);
        $this->grid->setParam('gridRemove', 1);
        $this->grid->setParam('comm','mode:delete;[Name:Afghanistan]');

        $form = new Bvb_Grid_Form();
        $form->setView(new Zend_View());
        $form->setAdd(true)->setEdit(true)->setDelete(false);
        $this->grid->setForm($form);

        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);
        $this->assertQueryContentContains('td', 'Afghanistan');
    }

    public function testRowConditions()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setClassRowCondition("'{{Population}}' > 20000", "green", 'red');
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);

        $this->assertQueryContentRegex(".green", "/68000/si");
        $this->assertQueryContentRegex(".red", "/8000/si");
    }

    public function testRowConditionsStatus()
    {
        $this->grid->setClassRowCondition("'{{Population}}' > 20000", "green", 'red');

        $this->assertInternalType('array',$this->grid->getClassRowCondition());

    }

    public function testCellConditions()
    {

        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setClassCellCondition('Population', "'{{Population}}' > 200000", "red", 'green');
        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);

        $this->assertQueryContentRegex(".green", "/68000/si");
        $this->assertQueryContentRegex(".red", "/22720000/si");

    }

    public function testCellConditionsStatus()
    {
        $this->grid->setClassCellCondition('Population', "'{{Population}}' > 200000", "red", 'green');

        $this->assertFalse($this->grid->getClassCellCondition('nonExistingField'));
        $this->assertInternalType('array',$this->grid->getClassCellCondition('Population'));
    }

    public function testAjax()
    {
        $this->assertFalse($this->grid->getAjax());
        $this->grid->setAjax('id');
        $this->assertInternalType('string',$this->grid->getAjax());
    }

    public function testSqlxpressions()
    {
        $select = $this->db->select()->from('unit');
        $this->grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $this->grid->setSqlExp(array('Population' => array('functions' => array('SUM'))));

        $grid = $this->grid->deploy();
        $this->controller->getResponse()->setBody($grid);

        $this->assertQueryContentContains('td','6062668450');
    }

    public function testRowAltClasses()
    {
        $this->grid->setRowAltClasses(array('odd'=>'red','even'=>'green'));

        $this->assertInternalType('array',$this->grid->getRowAltClasses());
    }

    public function testImagesUrl()
    {
        $url = '/something/new';

        $this->grid->setImagesUrl($url);

        $this->assertEquals($this->grid->getImagesUrl(),$url);
    }
}