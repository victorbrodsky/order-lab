<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
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
