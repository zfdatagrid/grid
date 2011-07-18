<?php

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);

date_default_timezone_set('Europe/Lisbon');
set_include_path('.' . PATH_SEPARATOR . './library/'
        . '.' . PATH_SEPARATOR . './application/'
        . PATH_SEPARATOR . get_include_path());

include "Zend/Loader/Autoloader.php";

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('ZFDebug');
$autoloader->registerNamespace('Bvb');
$autoloader->registerNamespace('My');
$autoloader->registerNamespace('OFC');
$autoloader->registerNamespace('Zendx');
#$autoloader->suppressNotFoundWarnings(false);
#$autoloader->setFallbackAutoloader(true);


define('APPLICATION_PATH', getcwd());

Zend_Session::start();

// Load Config
$config = new Zend_Config_Ini('./application/config.ini', 'general');
Zend_Registry::set('config', $config);
//Cache Options
$frontendOptions = array('lifetime' => 7200, 'automatic_serialization' => true);
$backendOptions = array('cache_dir' => './data/cache/');
$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
Zend_Registry::set('cache', $cache);

// Database
$db = Zend_Db::factory($config->db->adapter, $config->db->config->toArray());
Zend_Db_Table::setDefaultAdapter($db);
Zend_Db_Table::setDefaultMetadataCache($cache);
#$db->getConnection ()->exec ( "SET NAMES utf8" );
$db->setFetchMode(Zend_Db::FETCH_OBJ);
$db->setProfiler(true);
Zend_Registry::set('db', $db);


/*
  //Locale
  $locale = new Zend_Locale('en_US');
  Zend_Registry::set('locale', $locale);
  $english = array(
  'Name_of' => 'Barcelos',
  'message2' => 'message2',
  'message3' => 'message3');

  $german = array(
  'Fmessage1' => 'Nachricht1',
  'message2' => 'Nachricht2',
  'message3' => 'Nachricht3');

  $translate = new Zend_Translate('array', $english, 'en');

  Zend_Registry::set('Zend_Translate',$translate);
 */


$frontController = Zend_Controller_Front::getInstance();
$frontController->throwExceptions(true);
$frontController->setControllerDirectory('./application/controllers');
$frontController->setDefaultControllerName('site');

// Leave 'Database' options empty to rely on Zend_Db_Table default adapter
$options = array(
    'jquery_path' => 'http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js',
    'plugins' => array('Variables',
        'Html',
        'Database' => array('adapter' => array('standard' => $db)),
        'File' => array('base_path' => '/Library/WebServer/Documents/'),
        'Memory',
        'Time',
        'Cache' => array('backend' => $cache->getBackend()),
        'Exception')
);

$debug = new ZFDebug_Controller_Plugin_Debug($options);

#$frontController->registerPlugin($debug);
$route = new Zend_Controller_Router_Route(
                'route/*',
                array('controller' => 'site', 'action' => 'crud')
);

$router = $frontController->getRouter();
$router->addRoute('login', $route);
$frontController->dispatch();