<?php

/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 12/8/2023
 * Time: 6:11 PM
 */

//https://carlos-compains.medium.com/multi-database-doctrine-symfony-based-project-0c1e175b64bf
//https://github.com/compains/multi-database-symfony-based-project/blob/main/src/Connection/DoctrineMultidatabaseConnection.php

namespace App\SystemBundle\DynamicConnection;

use App\SystemBundle\DynamicConnection\DynamicConnection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

//implements DynamicConnection
class DoctrineMultidatabaseConnection extends Connection
{

//    public function __construct(
//        array $params,
//        Driver $driver,
//        Configuration $config,
//        EventManager $eventManager
//    )
//    {
//        //exit('222');
//        parent::__construct($params,$driver,$config,$eventManager);
//    }

    public function changeDatabase($connectionParams): bool {
        $params = $this->getParams();

        //dump($connectionParams);
        //dump($params);
        //exit('111');

        if(
            $params['dbname'] != $connectionParams['dbname'] ||
            $params['host'] != $connectionParams['host']
        ) {
            if ($this->isConnected()) {
                $this->close();
            }
            //$params['dbname'] = $dbName;
            //$params['wrapperClass'] = DoctrineMultidatabaseConnection::class;

            parent::__construct(
                $connectionParams,
                $this->_driver,
                $this->_config,
                $this->_eventManager
            );
            return true;
        }
        return false;
    }

    public function changeDatabaseByName(string $dbName): bool {
        $params = $this->getParams();
        if ($params['dbname'] != $dbName) {
            if ($this->isConnected()) {
                $this->close();
            }
            //$params['url'] = "pgsql://" . $params['user'] . ":" . $params['password'] . "@" . $params['host'] . ":" . $params['port'] . "/" . $dbName;
            //$params['dbname'] = $dbName;

            $params['dbname'] = $dbName;
            $params['wrapperClass'] = DoctrineMultidatabaseConnection::class;

            parent::__construct(
                $params,
                $this->_driver,
                $this->_config,
                $this->_eventManager
            );
            return true;
        }
        return false;
    }

    public function getDatabases(string $prefix = 'app_') {
        $dbs = $this->fetchAllAssociative('show databases;');
        $res = [];
        foreach ($dbs as $key => $dbName) {
            if (strpos($dbName['Database'], $prefix) === 0) {
                $res[] = $dbName['Database'];
            }
        }
        return $res;
    }
    

}
