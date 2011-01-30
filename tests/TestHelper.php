<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TestHelper.php 23522 2010-12-16 20:33:22Z andries $
 */
/*
 * Include PHPUnit dependencies
 */
@require_once 'PHPUnit/Framework.php';
@require_once 'PHPUnit/Autoload.php'; // >= of PHPUnit 3.5.5

/*
 * Set error reporting to the level to which Zend Framework code must comply.
 */
error_reporting(E_ALL | E_STRICT);

/*
 * Determine the root, library, and tests directories of the framework
 * distribution.
 */
$zfRoot = realpath(dirname(dirname(__FILE__)));
$zfCoreLibrary = "$zfRoot/library";
$zfCoreTests = "$zfRoot/tests";
/*
 * Prepend the Zend Framework library/ and tests/ directories to the
 * include_path. This allows the tests to run out of the box and helps prevent
 * loading other copies of the framework code and tests that would supersede
 * this copy.
 */
$path = array(
    $zfCoreLibrary,
    $zfCoreTests,
    get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));

require_once 'Zend/Loader/Autoloader.php';

$autoloader = Zend_Loader_Autoloader::getInstance ();
$autoloader->setFallbackAutoloader ( true );
$autoloader->suppressNotFoundWarnings ( true );

define('APPLICATION_PATH', $zfRoot.'/application');

$c = Zend_Controller_Front::getInstance();
$c->setRequest(new Zend_Controller_Request_Http());
$c->setResponse(new Zend_Controller_Response_Cli());

unset($zfRoot, $zfCoreLibrary, $zfCoreTests, $path);