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
//https://github.com/symfony/symfony-standard/blob/3.4/web/app.php

use Symfony\Component\HttpFoundation\Request;

//Testing permission
//umask(0000);

// Report all errors except E_NOTICE
//error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
//error_reporting(0);
//ini_set('display_errors', 0);

require __DIR__.'/../vendor/autoload.php';
if (PHP_VERSION_ID < 70000) {
    include_once __DIR__.'/../var/bootstrap.php.cache';
}

$kernel = new AppKernel('prod', false);

//mvds - trick php into thinking it is running in HTTPS and let the script run for 5 min max
//if statement to enable https based on the connection_channel container's parameter
//Now we can initialize the container by boot(). This boot() method exists in the handle(), however it will be skipped and will not be run twice.
if(0) {
    $kernel->boot();
    $container = $kernel->getContainer();
    $connectionChannel = $container->getParameter('connection_channel');
    echo "connectionChannel=".$connectionChannel."<br>";
    if ($connectionChannel == "https") {
        exit('https on!');
        $_SERVER['HTTPS'] = 'on';
    }
}

if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}

//Optional cache
if(0) {
    // When using the HTTP Cache to improve application performance, the application
    // kernel is wrapped by the AppCache class to activate the built-in reverse proxy.
    // See https://symfony.com/doc/current/book/http_cache.html#symfony-reverse-proxy
    $kernel = new AppCache($kernel);
    // When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
    Request::enableHttpMethodParameterOverride();
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);

$response->send();
$kernel->terminate($request, $response);
