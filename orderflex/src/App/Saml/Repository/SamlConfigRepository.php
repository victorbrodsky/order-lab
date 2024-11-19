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

        $config = $this->findOneBy(['client' => $client]);

        if( !$config ) {
            $config = $this->findAnyOne();
        }

        return $config;
    }

    public function findByDomain(string $domain): ?SamlConfig
    {
        //$client = 'oli2002@med.cornell.edu' => $client = 'med.cornell.edu'
        //$domain = explode('@', $client);
        //$client = $domain[1];
        //exit('client='.$client);

        $config = $this->findOneBy(['client' => $domain]);

        if( !$config ) {
            $config = $this->findAnyOne();
        }

        return $config;
    }

    public function findAnyOne() {
        $configs = $this->_em->getRepository(SamlConfig::class)->findAll();
        if( count($configs) > 0 ) {
            return $configs[0];
        }
        return NULL;
    }

//    public function findOneBy(array $criteria, ?array $orderBy = null)
//    {
//        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);
//
//        return $persister->load($criteria, null, null, [], null, 1, $orderBy);
//    }
}
