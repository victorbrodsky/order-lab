<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

ini_set('memory_limit', '256M'); //128M

ini_set('max_execution_time', 180);  //in sec

/**
 * @var $loader ClassLoader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$loader->add( 'FR3D', __DIR__.'/../vendor/bundles' );

//autoload Aperio authentication
$loader->add( 'Aperio_' , __DIR__.'/../vendor/aperio/lib' );

$loader->add(
    'Knp\\Component', __DIR__.'/../vendor/knp-components/src',
    'Knp\\Bundle', __DIR__.'/../vendor/bundles'
);

$loader->add( 'PHPExcel' , __DIR__.'/../vendor/phpexcel/Classes' );

return $loader;
