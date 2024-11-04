<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 10/15/2024
 * Time: 11:24 AM
 */

namespace App\Saml\Repository;



//use App\Entity\SamlConfig;
use App\Saml\Entity\SamlConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SamlConfigRepository extends EntityRepository //ServiceEntityRepository
{
//    public function __construct(ManagerRegistry $registry)
//    {
//        parent::__construct($registry, SamlConfig::class);
//    }

    public function findByClient(string $client): ?SamlConfig
    {
        //$client = 'oli2002@med.cornell.edu' => $client = 'med.cornell.edu'
        $domain = explode('@', $client);
        $client = $domain[1];
        //exit('client='.$client);

        return $this->findOneBy(['client' => $client]);
    }

//    public function findOneBy(array $criteria, ?array $orderBy = null)
//    {
//        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);
//
//        return $persister->load($criteria, null, null, [], null, 1, $orderBy);
//    }
}
