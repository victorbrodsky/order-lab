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
        //$logger->notice("DatabaseConnectionFactory multitenancy=".$multitenancy."; dbName=".$params['dbname']);
        //return parent::createConnection($params, $config, $eventManager, $mappingTypes); //testing

//        if( !$params['dbname'] ) {
//            dump($params);
//            exit('111');
//        }

        //////// Use '/system/' as a system site ////////
//        if(0) {
//            $systemdb = $this->container->getParameter('systemdb');
//            //echo "systemdb=".$systemdb."<br>";
//            if( $systemdb ) {
//                $uri = null;
//                $request = $this->requestStack->getCurrentRequest();
//                if( $request ) {
//                    $uri = $request->getUri();
//                }
//                //echo "uri=".$uri."<br>";
//                if( str_contains($uri,'/system/') ) {
//                    $params = array();
//                    $params['driver'] = $this->container->getParameter('database_driver_systemdb');
//                    $params['host'] = $this->container->getParameter('database_host_systemdb');
//                    $params['port'] = $this->container->getParameter('database_port_systemdb');
//                    $params['dbname'] = $this->container->getParameter('database_name_systemdb');
//                    $params['user'] = $this->container->getParameter('database_user_systemdb');
//                    $params['password'] = $this->container->getParameter('database_password_systemdb');
//                    //echo "<br>SystemDB: dBName=".$params['dbname']."<br>";
//                    return parent::createConnection($params, $config, $eventManager, $mappingTypes);
//                }
//            }
//            //dump($params);
//            //exit('111');
//        }
        //////// EOF Use '/system/' as a system site ////////

        if( $multitenancy == 'singletenancy' ) {
            //echo "singletenancy dBName=".$params['dbname']."<br>";
            $logger->notice("DatabaseConnectionFactory: exit (singletenancy) multitenancy=[".$multitenancy."]; dbName=[".$params['dbname']."]");
            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $request = $this->requestStack->getCurrentRequest();

        //Check if session set and $session->get('locale') exists => use locale to get connection parameters
        if( $request ) {
            if( $request->hasSession() ) {
                $session = $request->getSession();
                if ($session) {
                    //dump($session);
                    //exit('111');
                    if ($session->has('locale')) {
                        $locale = $session->get('locale');
                        if ($locale) {
                            $params = $userServiceUtil->getConnectionParams($locale);
                            $logger->notice("DatabaseConnectionFactory: exit(use locale) multitenancy=[" . $multitenancy . "]; dbName=[" . $params['dbname'] . "]");
                            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
                        }
                    }
                }
            }
        }

        $uri = null;
        if( $request ) {
            $uri = $request->getUri();
        }
        $logger->notice("DatabaseConnectionFactory: uri=".$uri);

        //$urlArray = parse_url($uri);
        //dump($urlArray);
        //exit('111');

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

        $found = false;
        foreach($multilocalesUrlArr as $multilocalesUrl) {
            $logger->notice("DatabaseConnectionFactory: foreach multilocalesUrl=[".$multilocalesUrl."]");
            //uri=http://127.0.0.1/system/directory/admin/populate-country-city-list-with-default-values
            //foreach multilocalesUrl=[system]
            //foreach multilocalesUrl=[default]

            //get the first level of url and break loop
            //$multilocalesUrl = 'c/lmh/pathology'
            if( $multilocalesUrl != 'default' ) {
                if ($uri && str_contains($uri, "/" . $multilocalesUrl)) {
                    //connect to the appropriate DB
                    $params = $userServiceUtil->getConnectionParams($multilocalesUrl);
                    //$dbName = 'Tenant2';
                    //$params['dbname'] = $dbName;
                    $found = true;
                    break;
                } else {
                    //don't change default dbname
                }
            }
        }

        //if match not found, for example, uri=http://127.0.0.1/directory/, then set to default connection
        if( !$found ) {
            $params = $userServiceUtil->getConnectionParams('default');
        }

        $logger->notice("DatabaseConnectionFactory: exit multitenancy=[".$multitenancy."]; dbName=[".$params['dbname']."]");
        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
    }

}