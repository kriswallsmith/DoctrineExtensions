<?php
/**
 * This is bootstrap for phpUnit unit tests,
 * make sure that your doctrine library structure looks like:
 * /Doctrine
 *      /ORM
 *      /DBAL
 *      /Common
 * 
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package DoctrineExtensions.Translatable
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

if (!defined('DOCTRINE_LIBRARY_PATH') || !strlen(DOCTRINE_LIBRARY_PATH)) {
	die('path to doctrine library must be defined in phpunit.xml configuration');
}

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(DOCTRINE_LIBRARY_PATH),
    get_include_path(),
)));

!defined('DS') && define('DS', DIRECTORY_SEPARATOR);
!defined('TESTS_PATH') && define('TESTS_PATH', __DIR__);

$classLoaderFile = DOCTRINE_LIBRARY_PATH . DS . 'Doctrine/Common/ClassLoader.php';
if (!file_exists($classLoaderFile)) {
	die('cannot find doctrine classloader, check the library path');
}
require_once $classLoaderFile;
$classLoader = new Doctrine\Common\ClassLoader('Doctrine');
$classLoader->register();
      
$classLoader = new Doctrine\Common\ClassLoader('DoctrineExtensions', __DIR__ . '/../lib');
$classLoader->register();