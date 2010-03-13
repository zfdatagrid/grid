<?php

error_reporting(-1);
ini_set('display_errors',1);
ini_set('max_execution_time',0);

date_default_timezone_set ( 'Europe/Lisbon' );
set_include_path ( '.' . PATH_SEPARATOR . './library/'
.'.' . PATH_SEPARATOR . './application/'
. PATH_SEPARATOR . get_include_path () );


function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
        die();
        break;

    default:
        echo "Unknown error type: [$errno] $errstr<br />\n";
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}



// set to the user defined error handler
set_error_handler("myErrorHandler");

include "Zend/Loader/Autoloader.php";

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Bvb_');
$autoloader->registerNamespace('My_');
$autoloader->suppressNotFoundWarnings(false);
$autoloader->setFallbackAutoloader(true);

Zend_Session::start();

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


// Leave 'Database' options empty to rely on Zend_Db_Table default adapter

$options = array(
    'jquery_path' => 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js',
    'plugins' => array('Variables',
                       'Html',
                       'Database' => array('adapter' => array('standard' => $db)),
                       'File' => array('base_path' => '/Library/WebServer/Documents/'),
                       'Memory',
                       'Time',
                       'Registry',
                       #'Cache' => array('backend' => $cache->getBackend()),
                       'Exception')
);

$debug = new ZFDebug_Controller_Plugin_Debug($options);

$frontController = Zend_Controller_Front::getInstance();
$frontController->registerPlugin($debug);



$frontController->dispatch ();