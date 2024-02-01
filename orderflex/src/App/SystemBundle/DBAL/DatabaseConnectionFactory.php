<?php
/**
 * Created by PhpStorm.
 * User: Oleg Ivanov oli2002
 * Date: 12/20/2023
 * Time: 12:28 PM
 */

namespace App\SystemBundle\DBAL;

use App\UserdirectoryBundle\Entity\HostedGroupHolder;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

//Credit to TvC
//https://stackoverflow.com/questions/15108732/symfony2-dynamic-db-connection-early-override-of-doctrine-service

class DatabaseConnectionFactory extends ConnectionFactory
{

    private $requestStack;
    private $container;
    private $em;

    //private $multitenancy;
    //private $wrappedConnectionFactory;

    public function __construct( RequestStack $requestStack, ContainerInterface $container, EntityManagerInterface $em )
    {
        $this->requestStack = $requestStack;
        $this->container = $container;
        $this->em = $em;
        //$this->multitenancy = $multitenancy;
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
        //echo "createConnection: multitenancy=".$multitenancy."<br>";
        //echo "DatabaseConnectionFactory multitenancy=".$multitenancy."<br>";
        $logger->notice("DatabaseConnectionFactory multitenancy=".$multitenancy."; dbName=".$params['dbname']);
        //return parent::createConnection($params, $config, $eventManager, $mappingTypes); //testing

        //Do not use '/system/' as a system site. Use '/c/' as a system url.
        if(0) {
            $systemdb = $this->container->getParameter('systemdb');
            //echo "systemdb=".$systemdb."<br>";
            if( $systemdb ) {
                $uri = null;
                $request = $this->requestStack->getCurrentRequest();
                if( $request ) {
                    $uri = $request->getUri();
                }
                //echo "uri=".$uri."<br>";
                if( str_contains($uri,'/system/') ) {
                    $params = array();
                    $params['driver'] = $this->container->getParameter('database_driver_systemdb');
                    $params['host'] = $this->container->getParameter('database_host_systemdb');
                    $params['port'] = $this->container->getParameter('database_port_systemdb');
                    $params['dbname'] = $this->container->getParameter('database_name_systemdb');
                    $params['user'] = $this->container->getParameter('database_user_systemdb');
                    $params['password'] = $this->container->getParameter('database_password_systemdb');
                    //echo "<br>SystemDB: dBName=".$params['dbname']."<br>";
                    return parent::createConnection($params, $config, $eventManager, $mappingTypes);
                }
            }
            //dump($params);
            //exit('111');
        }

        if( $multitenancy == 'singletenancy' ) {
            //echo "singletenancy dBName=".$params['dbname']."<br>";
            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
        }

        $userServiceUtil = $this->container->get('user_service_utility');

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

        $multilocales = $this->container->getParameter('multilocales');
        //echo "createConnection: multilocales=$multilocales <br>";
        //$multilocales = $this->container->getParameter('multilocales-urls'); //main|c/wcm/pathology|c/lmh/pathology
        $multilocalesUrlArr = explode("|", $multilocales);

        foreach($multilocalesUrlArr as $multilocalesUrl) {
            //$multilocalesUrl = 'c/lmh/pathology'
            if( $uri && str_contains($uri, $multilocalesUrl) ) {
                //connect to the system DB
                //$params = $this->getConnectionParams($multilocalesUrl);
                $params = $userServiceUtil->getConnectionParams($multilocalesUrl);
                //$dbName = 'Tenant2';
                //$params['dbname'] = $dbName;
            } else {
                //don't change default dbname
            }
        }

//        if( $uri && str_contains($uri, 'c/lmh/pathology') ) {
//            $dbName = 'Tenant2';
//            $params['dbname'] = $dbName;
//        } else {
//            //don't change default dbname
//        }

        //dump($params);
        //exit('111');

        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
        //return $this->wrappedConnectionFactory->createConnection($params, $config, $eventManager, $mappingTypes);
    }

}