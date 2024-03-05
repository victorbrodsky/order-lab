<?php

use App\Kernel;
//use Symfony\Component\Debug\Debug;
//use Symfony\Component\ErrorHandler\Debug;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Dotenv\Dotenv;

//use Symfony\Component\Dotenv\Dotenv;
////exit(__DIR__.'/../.env');
//$dotenv = new Dotenv();
//$dotenv->load(__DIR__.'/../.env');

$env = 'dev';
$_SERVER['APP_DEBUG'] = true;
$_SERVER['APP_ENV'] = $env;

//require dirname(__DIR__).'/config/bootstrap.php';
//require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    echo "APP_PREFIX_URL=".$context['APP_PREFIX_URL']."<br>";
    $_SERVER['APP_PREFIX_URL'] = $context['APP_PREFIX_URL'];
    putenv('APP_PREFIX_URL='.$context['APP_PREFIX_URL']);
    //exit( 'APP_ENV='.$context['APP_ENV'].', debug='.$context['APP_DEBUG'] );
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    //return new Kernel('dev', true);
};
