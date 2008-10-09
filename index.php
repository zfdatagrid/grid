<?php
error_reporting(E_ALL ^E_NOTICE);
ini_set('date.timezone','Europe/Lisbon');
ini_set('max_execution_time',0);

date_default_timezone_set ( 'Europe/Lisbon' );
set_include_path ( '.' . PATH_SEPARATOR . './library/'
.'.' . PATH_SEPARATOR . './application/'
. PATH_SEPARATOR . get_include_path () );

include "Zend/Loader.php";
Zend_Loader::registerAutoload ();

include ("Bvb/Bvb.php");
#teste 

$view = new Zend_View();
$view->setScriptPath('app/skins/bvb/views/');
$view->doctype('XHTML1_TRANSITIONAL');
$view->setEncoding('UTF-8');

// Iniciar a sessão
Zend_Session::start ();
$sessao = new Zend_Session_Namespace ( 'petala_azul' );
$sessao->setExpirationSeconds ( 3600 );
Zend_Registry::set ( 'session', $sessao );

// Carregar a configuração
$config = new Zend_Config_Ini ( './application/config.ini', 'general' );
Bvb::reg ( 'config', $config );

// base de dados
$db = Zend_Db::factory ( $config->db->adapter, $config->db->config->toArray () );
Zend_Db_Table::setDefaultAdapter ( $db );
$db->getConnection ()->exec ( "SET NAMES utf8" );
$db->setFetchMode ( Zend_Db::FETCH_OBJ );
Zend_Registry::set ( 'db', $db );

//Opções cache
$frontendOptions = array('lifetime' => 7200,'automatic_serialization' => true);
$backendOptions = array('cache_dir' => './data/cache/');
$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
Zend_Registry::set('cache',$cache);


//Iniciar definições regionais
$locale = new Zend_Locale ( 'en_US' );
Bvb::reg ( 'locale', $locale );
Bvb::construir_lingua($locale);


$frontController = Zend_Controller_Front::getInstance();
$frontController->throwExceptions(true);
$frontController->setControllerDirectory('./application/controllers');
$frontController->setDefaultControllerName('site');

$frontController->dispatch ();