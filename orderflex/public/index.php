<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

//$env = 'dev';
//$_SERVER['APP_DEBUG'] = true;
//$_SERVER['APP_ENV'] = $env;
//putenv('APP_ENV='.$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $env);

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {

    //exit( 'APP_ENV='.$context['APP_ENV'].', debug='.$context['APP_DEBUG'] );
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    //exit( 'debug='.$context['APP_DEBUG'] );

//    Debug::enable();
//    $kernel = new Kernel('dev', true);
//    $request = Request::createFromGlobals();
//    $response = $kernel->handle($request);
//    $response->send();
//    $kernel->terminate($request, $response);
//    return $kernel;
};
