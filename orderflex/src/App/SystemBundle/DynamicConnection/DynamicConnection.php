<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 2/12/2024
 * Time: 5:26 PM
 */

declare(strict_types=1);

namespace App\SystemBundle\DynamicConnection;

use Doctrine\DBAL\Driver\Connection;

interface DynamicConnection extends Connection
{
    public function reinitialize(array $params): void;
}
