<?php

use App\Kernel;
//use Symfony\Component\Debug\Debug;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

//require dirname(__DIR__).'/config/bootstrap.php';
require_once dirname(__DIR__).'/vendor/autoload.php';

Debug::enable();

$kernel = new Kernel('dev',true);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
