<?php

/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 12/8/2023
 * Time: 6:11 PM
 */

//https://stackoverflow.com/questions/65902878/dynamic-doctrine-database-connection
//Good: https://stackoverflow.com/questions/53151669/symfony-change-database-dynamically

//declare(strict_types=1);

namespace App\Routing\DBAL;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;

final class MultiDbConnectionWrapper extends Connection
{

    public function __construct(array $params, Driver $driver, $config, $eventManager)
    {
        //$request = $eventManager->getRequest();
        //$session = $request->getSession();
        //$params['dbname'] = 'ScanOrder2';
        //dump($config);
        //exit('1');
//        if(!$this->isConnected()){
//            // Create default config and event manager if none given (case in command line)
//            if (!$config) {
//                $config = new Configuration();
//            }
//            if (!$eventManager) {
//                $eventManager = new EventManager();
//            }
//
//            $refEventManager = new \ReflectionObject($eventManager);
//            $refContainer = $refEventManager->getProperty('container');
//            $refContainer->setAccessible('public'); //We have to change it for a moment
//
//            /*
//             * @var \Symfony\Component\DependencyInjection\ContainerInterface $container
//             */
//            $container = $refContainer->getValue($eventManager);
//            //dump($container);
//            //exit('111');
//
//            /*
//             * @var Symfony\Component\HttpFoundation\Request
//             */
//            $request = $container->get('request_stack')->getCurrentRequest();
//
//            if ($request != null && $request->attributes->has('_company')) {
//                $params['dbname'] .= $request->attributes->get('_company');
//            }
//
//            $refContainer->setAccessible('private'); //We put in private again
//            parent::__construct($params, $driver, $config, $eventManager);
//        }
        if( !$this->isConnected() ) {
            $params['dbname'] = 'Tenant2';
            //exit("dbname=".$params['dbname']);
            parent::__construct($params, $driver, $config, $eventManager);
        }

        //parent::__construct($params, $driver, $config, $eventManager);
    }

    public function selectDatabase(string $dbName): void
    {
        if( $this->isConnected() ) {
            $this->close();
        }

        $params = $this->getParams();
        $params['dbname'] = $dbName;
        //exit('switch DB to '.$dbName);
        parent::__construct($params, $this->_driver, $this->_config, $this->_eventManager);
    }
}
