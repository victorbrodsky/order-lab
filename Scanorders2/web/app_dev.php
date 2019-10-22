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
// development environment ('dev'). See:
//   * https://symfony.com/doc/current/cookbook/configuration/front_controllers_and_kernel.html
//   * https://symfony.com/doc/current/cookbook/configuration/environments.html

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

//mvds - trick php into thinking it is running in HTTPS and let the script run for 5 min max
//$_SERVER['HTTPS'] = 'on';
//if( $_SERVER['SERVER_NAME'] == "c.med.cornell.edu" || $_SERVER['SERVER_NAME'] == "collage.med.cornell.edu" ) {
//    $_SERVER['HTTPS'] = 'on';
//}

// If you don't want to setup permissions the proper way, just uncomment the
// following PHP line. See:
// https://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

//Testing permission
//umask(0000);

// This check prevents access to debug front controllers that are deployed by
// accident to production servers. Feel free to remove this, extend it, or make
// something more sophisticated.
echo "REMOTE_ADDR=".$_SERVER['REMOTE_ADDR']."<br>";
echo "Web user=";
print posix_getpwuid(posix_geteuid())['name'];
echo "<br>";
//echo "HTTPDUSER=".$HTTPDUSER."<br>";
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', 'fe80::1', '::1', '140.251.6.82', '142.93.180.104'], true) || PHP_SAPI === 'cli-server')
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';
Debug::enable();

$kernel = new AppKernel('dev', true);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}

//mvds - trick php into thinking it is running in HTTPS and let the script run for 5 min max
//if statement to enable https based on the connection_channel container's parameter
//Now we can initialize the container by boot(). This boot() method exists in the handle(), however it will be skipped and will not be run twice.
$kernel->boot();
$container = $kernel->getContainer();
$connectionChannel = $container->getParameter('connection_channel');
//echo "connectionChannel=".$connectionChannel."<br>";
if( $connectionChannel == "https" ) {
    $_SERVER['HTTPS'] = 'on';
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
