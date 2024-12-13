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

    public function findByDomain( $domain ) {

        if( !$domain ) {
            return NULL;
        }

        $config = NULL;

        $query = $this->_em->createQueryBuilder()
            ->from(SamlConfig::class, 'config')
            ->select("config")
            ->where('config.client = :client AND config.type IN (:type)')
            ->orderBy("config.id","ASC")
            ->setParameters( array(
                'client' => $domain,
                'type' => array('default','user-added'),
            ))
        ;

        $configs = $query->getQuery()->getResult();

        if( count($configs) > 0 ) {
            $config = $configs[0];
        }

        return $config;
    }

    public function findByClient( $clientEmail ) {
        if( !$clientEmail ) {
            return NULL;
        }

        //exit('findByClient: $clientEmail='.$clientEmail);
        $client = NULL;
        $config = NULL;

        $domainArr = explode('@', $clientEmail);
        if( count($domainArr) > 1 ) {
            $client = $domainArr[1];
        }

        if( !$client ) {
            return NULL;
        }

        //return $this->findByDomain($client);

        $query = $this->_em->createQueryBuilder()
            ->from(SamlConfig::class, 'config')
            ->select("config")
            ->where('config.client = :client AND config.type IN (:type)')
            ->orderBy("config.id","ASC")
            ->setParameters( array(
                'client' => $client,
                'type' => array('default','user-added'),
            ))
        ;

        $configs = $query->getQuery()->getResult();

        if( count($configs) > 0 ) {
            $config = $configs[0];
        }

        return $config;
    }

//    //$client - email
//    public function findByClientSimple(string $client)
//    {
//        //$client = 'oli2002@med.cornell.edu' => $client = 'med.cornell.edu'
//        $domain = explode('@', $client);
//        $client = $domain[1];
//        //exit('client='.$client);
//
//        $config = $this->findOneBy(['client' => $client]);
//
//        //if( !$config ) {
//        //    $config = $this->findAnyOne();
//        //}
//
//        return $config;
//    }
//    public function findByDomainSimple(string $domain)
//    {
//        //$client = 'oli2002@med.cornell.edu' => $client = 'med.cornell.edu'
//        //$domain = explode('@', $client);
//        //$client = $domain[1];
//        //exit('client='.$client);
//
//        $config = $this->findOneBy(['client' => $domain]);
//
//        //if( !$config ) {
//        //    $config = $this->findAnyOne();
//        //}
//
//        return $config;
//    }




    public function findAnyOne() {
        $configs = $this->_em->getRepository(SamlConfig::class)->findAll();
        if( count($configs) > 0 ) {
            return $configs[0];
        }
        return NULL;
    }

    public function findAnyEnabledOne() {
        $config = NULL;

        $query = $this->_em->createQueryBuilder()
            ->from(SamlConfig::class, 'config')
            ->select("config")
            ->where('config.type IN (:type)')
            ->orderBy("config.id","ASC")
            ->setParameters( array(
                'type' => array('default','user-added'),
            ))
        ;

        $configs = $query->getQuery()->getResult();
        //echo "configs=".count($configs)."<br>";

        if( count($configs) > 0 ) {
            $config = $configs[0];
        }

        return $config;
    }


//    public function findOneBy(array $criteria, ?array $orderBy = null)
//    {
//        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);
//
//        return $persister->load($criteria, null, null, [], null, 1, $orderBy);
//    }
}
