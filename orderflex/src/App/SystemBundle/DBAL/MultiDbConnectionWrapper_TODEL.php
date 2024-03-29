<?php

/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 12/8/2023
 * Time: 6:11 PM
 */

//https://stackoverflow.com/questions/65902878/dynamic-doctrine-database-connection
//Good: https://stackoverflow.com/questions/53151669/symfony-change-database-dynamically

//decorator:
//https://stackoverflow.com/questions/15108732/symfony2-dynamic-db-connection-early-override-of-doctrine-service

//declare(strict_types=1);

namespace App\Routing\DBAL;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Symfony\Component\HttpFoundation\RequestStack;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\KernelInterface;

//use Doctrine\DBAL\Driver\PDO\PgSQL\Driver;

//Problem: how to get RequestStack?
//NOT USED
final class MultiDbConnectionWrapper extends Connection
{

    //public function __construct(array $params, Driver $driver, Configuration $config, EventManager $eventManager, #[Autowire('@request_stack')])
    public function __construct(
        array $params,
        Driver $driver,
        Configuration $config,
        EventManager $eventManager
    )
    {
        //$request = $eventManager->getRequest();
        //$session = $request->getSession();
        //$params['dbname'] = 'ScanOrder2';
        //dump($config);
        //dump($params);
        //exit('1');

        $enableMulti = false;
        //$enableMulti = true;

        if( $enableMulti && !$this->isConnected() ) {
            // Create default config and event manager if none given (case in command line)
            if (!$config) {
                $config = new Configuration();
            }
            if (!$eventManager) {
                $eventManager = new EventManager();
            }

            $refEventManager = new \ReflectionObject($eventManager);
            $refContainer = $refEventManager->getProperty('container');
            $refContainer->setAccessible('public'); //We have to change it for a moment

            /*
             * @var \Symfony\Component\DependencyInjection\ContainerInterface $container
             */
            $container = $refContainer->getValue($eventManager);

            $container = $kernel->getContainer();

            //$userSecUtil = $container->get('user_security_utility');
            //$userSecUtil->switchDb();

            //$userSecUtil = $container->get('user_security_utility');
            //dump($eventManager);
            //dump($params);
            //dump($config);
            //dump($container);
            //exit('111');

            //Problem: how to get RequestStack?
            /*
             * @var Symfony\Component\HttpFoundation\Request
             */
            $request = $container->get('request_stack')->getCurrentRequest();

            //if( $request != null && $request->attributes->has('_company') ) {
            //    $params['dbname'] .= $request->attributes->get('_company');
            //}

            //$session = $request->getSession();
            //$sessionLocale = $session->get('locale');
            //echo "sessionLocale=".$sessionLocale."<br>";
            //exit('1');

            $refContainer->setAccessible('private'); //We put in private again
            parent::__construct($params, $driver, $config, $eventManager);
        }

        if( 0 && !$this->isConnected() ) {
            $params['dbname'] = 'Tenant2';
            //exit("dbname=".$params['dbname']);
            parent::__construct($params, $driver, $config, $eventManager);
        }

        if( 1 && $enableMulti == false ) {
            //single tenancy
            parent::__construct($params, $driver, $config, $eventManager);
        }
    }


    public function selectDatabase(string $dbName): void
    {
        if ($this->isConnected()) {
            $this->close();
        }
        $params = $this->getParams();
        $params['dbname'] = $dbName;
        parent::__construct($params, $this->_driver, $this->_config, $this->_eventManager);
    }
    
    public function createConnectionToMyFavouriteDatabase( RequestStack $requestStack, Connection $connection ) {
        //
    }

}
