<?php

class Accounts extends Zend_Db_Table_Abstract
{
    protected $_name = 'accounts';
    protected $_dependentTables = array('Bugs');
}

class Products extends Zend_Db_Table_Abstract
{
    protected $_name = 'products';
    protected $_dependentTables = array('BugsProducts');
}


class Bugs extends Zend_Db_Table_Abstract
{
    protected $_name = 'bugs';
    protected $_dependentTables = array('BugsProducts');

    protected $_referenceMap = array(

    'Reporter' => array(
    'columns' => 'reported_by',
    'refTableClass' => 'Accounts',
    'refColumns' => 'account_name')
    ,
    'Engineer' => array(
    'columns' => 'assigned_to',
    'refTableClass' => 'Accounts',
    'refColumns' => 'account_name')
    ,
    'Verifier' => array(
    'columns' => array('verified_by'),
    'refTableClass' => 'Accounts',
    'refColumns' => array('account_name'))
    )    ;

}



class BugsProducts extends Zend_Db_Table_Abstract
{
    protected $_name = 'bugs_products';
    protected $_referenceMap = array(
    'Bug' => array(
    'columns' => array('bug_id'),
    'refTableClass' => 'Bugs',
    'refColumns' => array('bug_id'))
    ,
    'Product' => array(
    'columns' => array('product_id'),
    'refTableClass' => 'Products',
    'refColumns' => array('product_id')));
}