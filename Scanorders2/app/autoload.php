<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

//ini_set('memory_limit', '128M');

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

return $loader;
