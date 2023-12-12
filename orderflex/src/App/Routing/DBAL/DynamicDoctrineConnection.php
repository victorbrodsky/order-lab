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
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Exception;

//NOT USED
class DynamicDoctrineConnection
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct()
    {
        exit('__construct');
    }

    /**
     * Sets the DB Name prefix to use when selecting the database to connect to
     *
     * @param  Connection       $connection
     * @return SiteDbConnection $this
     */
    public function setDoctrineConnection(Connection $connection)
    {
        exit('setDoctrineConnection');
        $this->connection = $connection;

        return $this;
    }

    public function setUpAppConnection()
    {
        exit('setUpAppConnection');
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