<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Pre-set session save path before PHPUnit emits any output, so NativeFileSessionHandler
// won't call ini_set() with headers already sent during tests.
$sessionSavePath = dirname(__DIR__) . '/var/sessions/test';
if (!is_dir($sessionSavePath)) {
    @mkdir($sessionSavePath, 0777, true);
}
ini_set('session.save_handler', 'files');
ini_set('session.save_path', $sessionSavePath);

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    //(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.test');
}
