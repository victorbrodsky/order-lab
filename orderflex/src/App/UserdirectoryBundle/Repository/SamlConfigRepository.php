<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 10/15/2024
 * Time: 11:24 AM
 */

namespace App\UserdirectoryBundle\Repository;



//use App\Entity\SamlConfig;
use App\UserdirectoryBundle\Entity\SamlConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SamlConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SamlConfig::class);
    }

    public function findByClient(string $client): ?SamlConfig
    {
        return $this->findOneBy(['client' => $client]);
    }

//    public function findOneBy(array $criteria, ?array $orderBy = null)
//    {
//        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);
//
//        return $persister->load($criteria, null, null, [], null, 1, $orderBy);
//    }
}
