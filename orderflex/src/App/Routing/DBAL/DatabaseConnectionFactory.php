<?php
/**
 * Created by PhpStorm.
 * User: Oleg Ivanov oli2002
 * Date: 12/20/2023
 * Time: 12:28 PM
 */

namespace App\Routing\DBAL;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

//Credit to TvC
//https://stackoverflow.com/questions/15108732/symfony2-dynamic-db-connection-early-override-of-doctrine-service

class DatabaseConnectionFactory extends ConnectionFactory
{

    private $requestStack;
    //private $multitenancy;
    private $container;
    //private $wrappedConnectionFactory;

    public function __construct(
        $requestStack,
        //$multitenancy,
        ContainerInterface $container
        //$wrappedConnectionFactory
    )
    {
        $this->requestStack = $requestStack;
        //$this->multitenancy = $multitenancy;
        $this->container = $container;
        //$this->wrappedConnectionFactory = $wrappedConnectionFactory;
    }


    /** App\Factory\Authentication\DatabaseConnectionFactory
     * @param array              $params
     * @param Configuration|null $config
     * @param EventManager|null  $eventManager
     * @param array              $mappingTypes
     *
     * @throws \DomainException
     *
     * @return mixed
     */
    public function createConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = []
    )
    {
        //exit('DatabaseConnectionFactory');
        $logger = $this->container->get('logger');
        $multitenancy = $this->container->getParameter('multitenancy');
        //echo "DatabaseConnectionFactory multitenancy=".$multitenancy."<br>";
        $logger->notice("DatabaseConnectionFactory multitenancy=".$multitenancy);
        if( $multitenancy == 'singletenancy' ) {
            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
        }

        $uri = null;
        $request = $this->requestStack->getCurrentRequest();

        if( $request ) {
            $uri = $request->getUri();
        }
        //echo "uri=".$uri."<br>";
        //dump($params);
        //exit('111');

//        if( !$uri ) {
//            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
//        }

        if( $uri && str_contains($uri, 'c/lmh/pathology') ) {
            $dbName = 'Tenant2';
            $params['dbname'] = $dbName;
        } else {
            //don't change default dbname
        }

        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
        //return $this->wrappedConnectionFactory->createConnection($params, $config, $eventManager, $mappingTypes);
    }

}