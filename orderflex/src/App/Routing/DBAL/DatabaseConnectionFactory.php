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

class DatabaseConnectionFactory
{

    public $wrappedConnectionFactory;

    public function __construct( $wrappedConnectionFactory )
    {
        $this->wrappedConnectionFactory = $wrappedConnectionFactory;
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
    public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null, array $mappingTypes = [])
    {
        $params['url'] = $this->databaseConnectionUrlService->getDatabaseConnectionUrlForApiUser($this->apiUser, $params['url'] );

        return $this->wrappedConnectionFactory->createConnection($params, $config, $eventManager, $mappingTypes);
    }

}