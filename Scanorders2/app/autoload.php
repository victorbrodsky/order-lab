<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

//ini_set('memory_limit', '3072M');   //2048M 1024M 512M 128M
//ini_set('max_execution_time', 360);  //in sec

// Report all errors except E_NOTICE
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

/**
 * @var $loader ClassLoader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

//autoload Aperio authentication
$loader->add( 'Aperio_' , __DIR__.'/../vendor/aperio/lib' );

$loader->add(
    'Knp\\Component', __DIR__.'/../vendor/knp-components/src',
    'Knp\\Bundle', __DIR__.'/../vendor/bundles'
);

$loader->add( 'PHPExcel' , __DIR__.'/../vendor/phpexcel/Classes' );

return $loader;
