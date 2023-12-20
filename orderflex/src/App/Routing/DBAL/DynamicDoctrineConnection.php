<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 12/12/2023
 * Time: 3:51 PM
 */

//https://stackoverflow.com/questions/15108732/symfony2-dynamic-db-connection-early-override-of-doctrine-service

namespace App\Routing\DBAL;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Exception;

//NOT USED
class DynamicDoctrineConnection
{
    /**
     * @var Connection
     */
    private $connection;
    private $requestStack;
    private $defaultConnection;
    private $doctrine;


    public function __construct(RequestStack $requestStack, Connection $defaultConnection, Registry $doctrine)
    {
        //exit('DynamicDoctrineConnection');
        $this->requestStack      = $requestStack;
        $this->defaultConnection = $defaultConnection;
        $this->doctrine          = $doctrine;
    }

    public function onKernelRequest()
    {
        $request = $this->requestStack->getCurrentRequest();
        //dump($request);
        //exit('111');

        $session = $request->getSession();
        //$sessionLocale = $session->get('locale');
        //echo 'sessionLocale='.$sessionLocale."<br>";
        $uri = $request->getUri();
        echo 'uri='.$uri."<br>";
        //exit('111');

        if( str_contains($uri, 'c/lmh/pathology') ) {
            //echo "The string 'lazy' was found in the string\n";
            $dbName = 'Tenant2';
            $this->switchDb($dbName);
        }

//        if( $sessionLocale == 'c/lmh/pathology' ) {
//            $dbName = 'Tenant2';
//            $this->switchDb($dbName);
//        }

//            //$this->defaultConnection->close();
//
//            $connection = $this->defaultConnection;
//            $params = $this->defaultConnection->getParams();
//
//            if ($connection->isConnected()) {
//                 $connection->close();
//            }
//
//            $params['dbname'] = $dbName;
//
//            $connection->__construct(
//                $params, $connection->getDriver(), $connection->getConfiguration(),
//                $connection->getEventManager()
//            );
//
//            try {
//                $connection->connect();
//            } catch (Exception $e) {
//                // log and handle exception
//            }

//            $reflectionConn = new \ReflectionObject($this->defaultConnection);
//            dump($reflectionConn);
//            exit('111');
//            $reflectionParams = $reflectionConn->getProperty('_params');
//            $reflectionParams->setAccessible(true);
//
//            $params = $reflectionParams->getValue($this->defaultConnection);
//            $params['dbname'] = $dbName;
//
//            $reflectionParams->setValue($this->defaultConnection, $params);
//            $reflectionParams->setAccessible(false);
//
//            $this->doctrine->resetEntityManager('default');
//        }


//        if ($this->request->attributes->has('appId')) {
//
//            //$dbName = 'Acme_App_' . $this->request->attributes->get('appId');
//            $request = $this->requestStack->getCurrentRequest();
//
//            dump($request);
//            exit('111');
//
//            $this->defaultConnection->close();
//
//            $reflectionConn = new \ReflectionObject($this->defaultConnection);
//            $reflectionParams = $reflectionConn->getProperty('_params');
//            $reflectionParams->setAccessible(true);
//
//            $params = $reflectionParams->getValue($this->defaultConnection);
//            $params['dbname'] = $dbName;
//
//            $reflectionParams->setValue($this->defaultConnection, $params);
//            $reflectionParams->setAccessible(false);
//
//            $this->doctrine->resetEntityManager('default');
//        }
    }

    public function switchDb($dbName)
    {

        //exit('switchDb to '.$dbName);
        $this->defaultConnection->close();

        $connection = $this->defaultConnection;
        $params = $this->defaultConnection->getParams();

        if ($connection->isConnected()) {
            $connection->close();
        }

        $params['dbname'] = $dbName;

        $connection->__construct(
            $params, $connection->getDriver(), $connection->getConfiguration(),
            $connection->getEventManager()
        );

        try {
            $connection->connect();
        } catch (Exception $e) {
            // log and handle exception
        }
    }

    /**
     * Sets the DB Name prefix to use when selecting the database to connect to
     *
     * @param  Connection       $connection
     * @return SiteDbConnection $this
     */
    public function setDoctrineConnection(Connection $connection)
    {
        //exit('setDoctrineConnection');
        $this->connection = $connection;

        return $this;
    }

    public function setUpAppConnection()
    {
        //exit('setUpAppConnection');
        if ($this->request->attributes->has('appId')) {
            $connection = $this->connection;
            $params     = $this->connection->getParams();

            // we also check if the current connection needs to be closed based on various things
            // have left that part in for information here
            // $appId changed from that in the connection?
            // if ($connection->isConnected()) {
            //     $connection->close();
            // }

            // Set default DB connection using appId
            //$params['host']   = $someHost;
            $params['dbname'] = 'Acme_App'.$this->request->attributes->get('appId');

            // Set up the parameters for the parent
            $connection->__construct(
                $params, $connection->getDriver(), $connection->getConfiguration(),
                $connection->getEventManager()
            );

            try {
                $connection->connect();
            } catch (Exception $e) {
                // log and handle exception
            }
        }

        return $this;
    }
}