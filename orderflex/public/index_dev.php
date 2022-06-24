<?php

use App\Kernel;
//use Symfony\Component\Debug\Debug;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

//require dirname(__DIR__).'/config/bootstrap.php';
//require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

Debug::enable();

//return function (array $context) {
//    return new Kernel('dev',true);
//};

$kernel = new Kernel('dev',true);
//return $kernel;

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
