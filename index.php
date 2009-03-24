<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('max_execution_time',0);

date_default_timezone_set ( 'Europe/Lisbon' );
set_include_path ( '.' . PATH_SEPARATOR . './library/'
.'.' . PATH_SEPARATOR . './application/'
. PATH_SEPARATOR . get_include_path () );

include "Zend/Loader.php";
Zend_Loader::registerAutoload ();


$view = new Zend_View();
$view->setScriptPath('app/skins/bvb/views/');
$view->doctype('XHTML1_TRANSITIONAL');
$view->setEncoding('UTF-8');

// Load Config
$config = new Zend_Config_Ini ( './application/config.ini', 'general' );
Zend_Registry::set ( 'config', $config );

// Database
$db = Zend_Db::factory ( $config->db->adapter, $config->db->config->toArray () );
Zend_Db_Table::setDefaultAdapter ( $db );
#$db->getConnection ()->exec ( "SET NAMES utf8" );
$db->setFetchMode ( Zend_Db::FETCH_OBJ );
$db->setProfiler(true);
Zend_Registry::set ( 'db', $db );

//Cache Options
$frontendOptions = array('lifetime' => 7200,'automatic_serialization' => true);
$backendOptions = array('cache_dir' => './data/cache/');
$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
Zend_Registry::set('cache',$cache);


//Locale
$locale = new Zend_Locale ( 'en_US' );
Zend_Registry::set ( 'locale', $locale );


$frontController = Zend_Controller_Front::getInstance();
$frontController->throwExceptions(true);
$frontController->setControllerDirectory('./application/controllers');
$frontController->setDefaultControllerName('site');
$frontController->registerPlugin(new Bvb_Controller_Plugin_Profiler());

$frontController->dispatch ();