<?php

use App\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

//$_SERVER['APP_ENV'] = 'dev';
//$_SERVER['APP_DEBUG'] = 1;

require dirname(__DIR__).'/config/bootstrap.php';


//if ($_SERVER['APP_DEBUG']) {
    //exit('111');
    umask(0000);

    Debug::enable();
//}

//if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
//    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
//}

//if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
//    Request::setTrustedHosts([$trustedHosts]);
//}

//if (
//        isset($_SERVER['HTTP_CLIENT_IP'])
//        || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
//        || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', 'fe80::1', '::1'], true) || PHP_SAPI === 'cli-server')
//) {
//    header('HTTP/1.0 403 Forbidden');
//    exit('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
//}

//$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel = new Kernel('dev',true);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
