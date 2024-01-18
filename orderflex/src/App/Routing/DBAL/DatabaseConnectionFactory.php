<?php
/**
 * Created by PhpStorm.
 * User: Oleg Ivanov oli2002
 * Date: 12/20/2023
 * Time: 12:28 PM
 */

namespace App\Routing\DBAL;

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

        $multilocales = $this->container->getParameter('multilocales-urls'); //main|c/wcm/pathology|c/lmh/pathology
        $multilocalesUrlArr = explode("|", $multilocales);

        foreach($multilocalesUrlArr as $multilocalesUrl) {
            //$multilocalesUrl = 'c/lmh/pathology'
            if( $uri && str_contains($uri, $multilocalesUrl) ) {
                $strArray = explode('/',$multilocalesUrl);
                $urlSlug = end($strArray); //last element
                //echo "lastElement=$lastElement <br>";
                //Find HostedGroupHolder by name=$lastElement
                //$entities = $em->getRepository(HostedGroupHolder::class)->findOneByHostedUserGroup();
                //TODO: connect to the system DB

                if(0) {
                    $repository = $this->em->getRepository(HostedGroupHolder::class);
                    $dql = $repository->createQueryBuilder("holder");
                    $dql->leftJoin('holder.hostedUserGroup', 'hostedUserGroup');
                    $dql->andWhere("hostedUserGroup.urlSlug = :urlSlug");
                    $queryParameters['urlSlug'] = $urlSlug;
                    $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
                    $query->setParameters($queryParameters);
                    $query->setMaxResults(1);
                    $hostedGroupHolder = $query->getOneOrNullResult();

                    if ($hostedGroupHolder) {
                        $dbHost = $hostedGroupHolder->getDatabaseHost();
                        $dbPort = $hostedGroupHolder->getDatabasePort();
                        $dbName = $hostedGroupHolder->getDatabaseName();
                        $dbUser = $hostedGroupHolder->getDatabaseUser();
                        $dbPassword = $hostedGroupHolder->getDatabasePassword();
                        $dbEnabled = $hostedGroupHolder->getEnabled();

                        if ($dbEnabled === true && $dbName && $dbUser && $dbPassword) {
                            if (!$dbHost) {
                                $dbHost = 'localhost';
                            }
                            if (!$dbPort) {
                                $dbPort = '5432';
                            }

                            //$params['host'] = $dbHost;
                            //$params['port'] = $dbPort;
                            $params['dbname'] = $dbName;
                            //$params['user'] = $dbName;
                            //$params['password'] = $dbName;
                            //$params['driver'] = $dbDriver;
                        }
                    }
                }//if 0
                $params = $this->getConnectionParams($multilocalesUrl);

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

        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
        //return $this->wrappedConnectionFactory->createConnection($params, $config, $eventManager, $mappingTypes);
    }

    public function getConnectionParams( $urlSlug ) {

        $params = array();
        $params['host'] = $this->container->getParameter($urlSlug.'-databaseHost');
        $params['port'] = $this->container->getParameter($urlSlug.'-databasePort');
        $params['dbname'] = $this->container->getParameter($urlSlug.'-databaseName');
        $params['user'] = $this->container->getParameter($urlSlug.'-databaseUser');
        $params['password'] = $this->container->getParameter($urlSlug.'-databasePassword');
        $params['driver'] = $this->container->getParameter('database_driver');

        return $params;
        
//        $host = $this->container->getParameter('database_host');
//        $driver = $this->container->getParameter('database_driver');
//        $dbname = $this->container->getParameter('database_name');
//        $user = $this->container->getParameter('database_user');
//        $password = $this->container->getParameter('database_password');
//
//        $connectionParams = array(
//            'dbname' => $dbname,
//            'user' => $user,
//            'password' => $password,
//            'host' => $host,
//            'driver' => $driver,
//            //'port' => 3306
//        );
//
//        $config = new \Doctrine\DBAL\Configuration();
//        $config->setSchemaManagerFactory(new \Doctrine\DBAL\Schema\DefaultSchemaManagerFactory());
//
//        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
//
//        if( $conn ) {
//
//            //get ID of the urlSlug
//            $hostedGroupHolderSql = "SELECT * FROM " . 'user_hostedusergrouplist' .
//                " WHERE urlSlug=$urlSlug AND enabled=TRUE";
//            $hostedGroupHolder = $conn->executeQuery($hostedGroupHolderSql);
//            $hostedGroupHolderRows = $hostedGroupHolder->fetchAllAssociative();
//
//            $hostedGroupHolderSql = "SELECT * FROM " . 'user_hostedgroupholder' .
//                " WHERE urlSlug=$urlSlug AND enabled=TRUE";
//            $hostedGroupHolder = $conn->executeQuery($hostedGroupHolderSql);
//            $hostedGroupHolderRows = $hostedGroupHolder->fetchAllAssociative();
//        }
    }

}