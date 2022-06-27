<?php

use App\Kernel;
//use Symfony\Component\Debug\Debug;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;


$env = 'dev';
$_SERVER['APP_DEBUG'] = true;
$_SERVER['APP_ENV'] = $env;
//putenv('APP_ENV='.$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $env);

//require dirname(__DIR__).'/config/bootstrap.php';
//require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    //exit( 'APP_ENV='.$context['APP_ENV'].', debug='.$context['APP_DEBUG'] );
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
