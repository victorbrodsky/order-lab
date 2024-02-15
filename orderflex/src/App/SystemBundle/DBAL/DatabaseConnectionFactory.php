<?php
/**
 * Created by PhpStorm.
 * User: Oleg Ivanov oli2002
 * Date: 12/20/2023
 * Time: 12:28 PM
 */

namespace App\SystemBundle\DBAL;

use App\SystemBundle\DynamicConnection\DoctrineMultidatabaseConnection;
use App\UserdirectoryBundle\Entity\HostedGroupHolder;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

//Credit to TvC
//https://stackoverflow.com/questions/15108732/symfony2-dynamic-db-connection-early-override-of-doctrine-service

class DatabaseConnectionFactory extends ConnectionFactory
{

    private $requestStack;
    private $container;
    private $em;
    private $security;

    //private $multitenancy;
    //private $wrappedConnectionFactory;

    public function __construct( RequestStack $requestStack, ContainerInterface $container, EntityManagerInterface $em, Security $security=null )
    {
        $this->requestStack = $requestStack;
        $this->container = $container;
        $this->em = $em;
        $this->security = $security;
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
        //dump($params);
        //exit('DatabaseConnectionFactory createConnection');
        //exit('DatabaseConnectionFactory');
        $logger = $this->container->get('logger');
        $multitenancy = $this->container->getParameter('multitenancy');

        $params['wrapperClass'] = DoctrineMultidatabaseConnection::class;

        if( $multitenancy == 'singletenancy' ) {
            //echo "singletenancy dBName=".$params['dbname']."<br>";
            $logger->notice("DatabaseConnectionFactory: exit (singletenancy) multitenancy=[".$multitenancy."]; dbName=[".$params['dbname']."]");
            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $request = $this->requestStack->getCurrentRequest();
        //$session = $this->requestStack->getSession();
        //dump($request);
        //exit(222);
        //dump($params);
        //exit('1 DatabaseConnectionFactory createConnection');

        //Check if session set and $session->get('locale') exists => use locale to get connection parameters
        if( $request ) {

            $requestLocale = $request->attributes->get('_locale');
            //echo "requestLocale=".$requestLocale."<br>";
            //exit('DatabaseConnectionFactory _locale');

            //if( isset($params['dbname']) ) {
                //dump($params);
                //exit('1 DatabaseConnectionFactory createConnection');
            //}
            //dump($params);
            //exit('1 DatabaseConnectionFactory createConnection');
            //echo "1 params=".$params['dbname']."<br>";

//            if( $this->security->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//                $session = $request->getSession();
//            } else {
//                $session = null;
//            }

            //if(1) {
            //if( $session ) {
            if( $request->hasSession() ) {
                //echo "createConnection: after has Session params=".$params['dbname']."<br>";
                //dump($params);
                //exit('2 DatabaseConnectionFactory createConnection');

                $session = $request->getSession();
                //$params['wrapperClass'] = DoctrineMultidatabaseConnection::class;
                if ($session) {
                    //dump($session);
                    //dump($params);
                    //exit('2 DatabaseConnectionFactory session');
                    //exit('createConnection 1');
                    //Create new DB
                    //$session->set('create-custom-db', null);
                    //$session->remove('create-custom-db');

                    if( 0 && $session->has('create-custom-db') ) {
                        dump($params);
                        $createDbName = $session->get('create-custom-db');
                        //exit('createConnection: create-custom-db: createDbName='.$createDbName);
                        if( $createDbName ) {
                            $params['dbname'] = $createDbName;
                            $params['wrapperClass'] = DoctrineMultidatabaseConnection::class;
                            dump($params);
                            //exit('createConnection 1');
                            //exit('wrapperClass='.$wrapperClass);
                            $logger->notice("DatabaseConnectionFactory: exit(use create-custom-db) multitenancy=[" . $multitenancy . "]; dbName=[" . $params['dbname'] . "]");
                            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
                        }
                        //exit('createConnection 2');
                    }

                    if( $session->has('locale') ) {
                        //$locale = $request->attributes->get('_locale');
                        $locale = $session->get('locale');
                        if( $locale ) {
                            //exit('$locale='.$locale);
                            $params = $userServiceUtil->getConnectionParams($locale);
                            $logger->notice("DatabaseConnectionFactory: exit(use locale=".$locale.") multitenancy=[" . $multitenancy . "]; dbName=[" . $params['dbname'] . "]");
                            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
                        } else {
                            $logger->notice("DatabaseConnectionFactory: 'locale' is null");
                        }
                    } else {
                        $logger->notice("DatabaseConnectionFactory: session does not have 'locale'");
                    }
                }
            }
            elseif( $requestLocale ) {
                $params = $userServiceUtil->getConnectionParams($requestLocale);
                $logger->notice("DatabaseConnectionFactory: exit(use requestLocale=".$requestLocale.") multitenancy=[" . $multitenancy . "]; dbName=[" . $params['dbname'] . "]");
                return parent::createConnection($params, $config, $eventManager, $mappingTypes);
            }
            else {
                $logger->notice("DatabaseConnectionFactory: request does not have a session or requestLocale");
            }
        }

        $uri = null;
        if( $request ) {
            $uri = $request->getUri();
        }
        $logger->notice("DatabaseConnectionFactory: uri=[".$uri."]");

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

        $logger->notice("DatabaseConnectionFactory: eof exit multitenancy=[".$multitenancy."]; dbName=[".$params['dbname']."]");
        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
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
    public function createConnection_ORIG(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = []
    )
    {
        //dump($params);
        //exit('DatabaseConnectionFactory createConnection');
        //exit('DatabaseConnectionFactory');
        $logger = $this->container->get('logger');
        $multitenancy = $this->container->getParameter('multitenancy');
        //echo "createConnection: multitenancy=".$multitenancy."<br>";
        //echo "DatabaseConnectionFactory multitenancy=".$multitenancy."<br>";
        //$logger->notice("DatabaseConnectionFactory multitenancy=".$multitenancy."; dbName=".$params['dbname']);
        //$params['dbname'] = 'ScanOrderSystem2';
        //return parent::createConnection($params, $config, $eventManager, $mappingTypes); //testing
        //exit('111');

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
        //$session = $this->requestStack->getSession();
        //dump($request);
        //exit(222);
        //dump($params);
        //exit('1 DatabaseConnectionFactory createConnection');

        //Check if session set and $session->get('locale') exists => use locale to get connection parameters
        if( $request ) {

            $requestLocale = $request->attributes->get('_locale');
            //echo "requestLocale=".$requestLocale."<br>";
            //exit('DatabaseConnectionFactory _locale');

            //dump($params);
            //exit('1 DatabaseConnectionFactory createConnection');
            //echo "params=".$params['dbname']."<br>";
            //if(1) {
            //if( $this->security->isGranted('IS_AUTHENTICATED_FULLY') ) {
            if( $request->hasSession() ) {
                //echo "after has Session params=".$params['dbname']."<br>";
                //dump($params);
                //exit('2 DatabaseConnectionFactory createConnection');

                $session = $request->getSession();
                //$params['wrapperClass'] = DoctrineMultidatabaseConnection::class;
                if ($session) {
                    //dump($session);
                    //dump($params);
                    //exit('2 DatabaseConnectionFactory session');
                    //exit('createConnection 1');
                    //Create new DB
                    //$session->set('create-custom-db', null);
                    //$session->remove('create-custom-db');

                    if( $session->has('create-custom-db') ) {

                        $createDbName = $session->get('create-custom-db');
                        exit('createConnection createDbName='.$createDbName);
                        if( $createDbName ) {
                            $params['dbname'] = $createDbName;
                            $params['wrapperClass'] = DoctrineMultidatabaseConnection::class;
                            //dump($params);
                            //exit('createConnection 1');
                            //exit('wrapperClass='.$wrapperClass);
                            $logger->notice("DatabaseConnectionFactory: exit(use create-custom-db) multitenancy=[" . $multitenancy . "]; dbName=[" . $params['dbname'] . "]");
                            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
                        }
                    }

                    if( $session->has('locale') ) {
                        //$locale = $request->attributes->get('_locale');
                        $locale = $session->get('locale');
                        if( $locale ) {
                            //exit('$locale='.$locale);
                            $params = $userServiceUtil->getConnectionParams($locale);
                            $logger->notice("DatabaseConnectionFactory: exit(use locale=".$locale.") multitenancy=[" . $multitenancy . "]; dbName=[" . $params['dbname'] . "]");
                            return parent::createConnection($params, $config, $eventManager, $mappingTypes);
                        } else {
                            $logger->notice("DatabaseConnectionFactory: 'locale' is null");
                        }
                    } else {
                        $logger->notice("DatabaseConnectionFactory: session does not have 'locale'");
                    }
                }
            }
            elseif( $requestLocale ) {
                $params = $userServiceUtil->getConnectionParams($requestLocale);
                $logger->notice("DatabaseConnectionFactory: exit(use requestLocale=".$requestLocale.") multitenancy=[" . $multitenancy . "]; dbName=[" . $params['dbname'] . "]");
                return parent::createConnection($params, $config, $eventManager, $mappingTypes);
            }
            else {
                $logger->notice("DatabaseConnectionFactory: request does not have a session or requestLocale");
            }
        }

        $uri = null;
        if( $request ) {
            $uri = $request->getUri();
        }
        $logger->notice("DatabaseConnectionFactory: uri=[".$uri."]");

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

        $logger->notice("DatabaseConnectionFactory: eof exit multitenancy=[".$multitenancy."]; dbName=[".$params['dbname']."]");
        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
    }

    //TODO: check if DB connection is correct?

}