<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

if (!file_exists(__DIR__ . '/.env')) {
    echo "No .env file found in " . __DIR__ . "\n";
    exit(1);
}

(new Dotenv())->loadEnv(__DIR__ . '/.env');

$env = $_SERVER['APP_ENV'] ?? 'dev';
$debug = filter_var($_SERVER['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

$kernel = new Kernel($env, $debug);
$kernel->boot();

$connection = $kernel->getContainer()->get('doctrine.dbal.default_connection');
$sqlFile = __DIR__ . '/src/Migrations/setval_identity_columns.sql';

if (!file_exists($sqlFile)) {
    echo "SQL file not found: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
$connection->executeStatement($sql);

echo "Setval SQL executed successfully.\n";
