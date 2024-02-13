<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 2/12/2024
 * Time: 5:16 PM
 */

declare(strict_types=1);

namespace App\SystemBundle\DynamicConnection;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;


class DynamicConnectionWrapper extends Connection #implements DynamicConnection
{
    public function __construct(
        array $params,
        Driver $driver,
        ?Configuration $config = null,
        ?EventManager $eventManager = null
    ) {
        //exit('111');
        parent::__construct($params, $driver, $config, $eventManager);
    }

//    public function __construct(
//        array $params,
//        Driver $driver,
//            Configuration $config = null,
//            EventManager $eventManager = null
//        )
//    {
//        //exit('111');
//        parent::__construct($params, $driver, $config, $eventManager);
//    }

    public function reinitialize(array $params): void
    {
        if ($this->isConnected()) {
            $this->close();
        }

        $params = array_merge($this->getParams(), $params);
        parent::__construct($params, $this->_driver, $this->_config, $this->_eventManager);
    }
}

