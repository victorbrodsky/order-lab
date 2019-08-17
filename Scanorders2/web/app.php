<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// This is the front controller used when executing the application in the
// production environment ('prod'). See:
//   * https://symfony.com/doc/current/cookbook/configuration/front_controllers_and_kernel.html
//   * https://symfony.com/doc/current/cookbook/configuration/environments.html

use Symfony\Component\HttpFoundation\Request;

//ini_set('memory_limit', '2048M');
//ini_set('max_execution_time', 180);  //in sec

// Report all errors except E_NOTICE
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
//error_reporting(0);
//ini_set('display_errors', 0);

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../vendor/autoload.php';
if (PHP_VERSION_ID < 70000) {
    include_once __DIR__.'/../app/bootstrap.php.cache';
    //$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
}

// Change 'sf' by something unique to this app to prevent
// conflicts with other applications running in the same server
//$loader = new ApcClassLoader('sf', $loader);
//$loader->register(true);


// If your web server provides APC support for PHP applications, uncomment these
// lines to use APC for class autoloading. This can improve application performance
// very significantly. See https://symfony.com/doc/current/components/class_loader/cache_class_loader.html#apcclassloader

// NOTE: The first argument of ApcClassLoader() is the prefix used to prevent
// cache key conflicts. In a real Symfony application, make sure to change
// it to a value that it's unique in the web server. A common practice is to use
// the domain name associated to the Symfony application (e.g. 'example_com').

// $apcLoader = new Symfony\Component\ClassLoader\ApcClassLoader(sha1(__FILE__), $loader);
// $loader->unregister();
// $apcLoader->register(true);

$kernel = new AppKernel('prod', false);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}

// When using the HTTP Cache to improve application performance, the application
// kernel is wrapped by the AppCache class to activate the built-in reverse proxy.
// See https://symfony.com/doc/current/book/http_cache.html#symfony-reverse-proxy
$kernel = new AppCache($kernel);

// If you use HTTP Cache and your application relies on the _method request parameter
// to get the intended HTTP method, uncomment this line.
// See https://symfony.com/doc/current/reference/configuration/framework.html#http-method-override
Request::enableHttpMethodParameterOverride();

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
