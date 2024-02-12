<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 2/12/2024
 * Time: 5:57 PM
 */

declare(strict_types=1);

namespace App\SystemBundle\DynamicConnection\Exception;

//use DynamicConnection\DynamicConnection;
use App\SystemBundle\DynamicConnection\DynamicConnection;
use Exception;

class ConnectionTypeException extends Exception
{
    public function __construct()
    {
        parent::__construct(sprintf(
            'Wrong connection type. Instance of %s expected.',
            DynamicConnection::class
        ));
    }
}
