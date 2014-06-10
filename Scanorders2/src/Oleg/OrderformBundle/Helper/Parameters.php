<?php

namespace Oleg\OrderformBundle\Helper;

use Doctrine\ORM\EntityManager;

class Parameters {

    private $em;
    private $container;

    public function __construct(EntityManager $em, $container = null)
    {
        $this->em = $em;
        $this->container = $container;

        $this->setParameters();

        //exit("Test");
    }


    public function setParameters() {

        $container = $this->container;

        $entities = $this->em->getRepository('OlegOrderformBundle:SiteParameters')->findAll();

        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }

        $entity = $entities[0];

        $aDLDAPServerAddress = $entity->getaDLDAPServerAddress();
        $aDLDAPServerOu = $entity->getaDLDAPServerOu();
        $aDLDAPServerAccountUserName = $entity->getaDLDAPServerAccountUserName();
        $aDLDAPServerAccountPassword = $entity->getaDLDAPServerAccountPassword();

        echo "aDLDAPServerAddress=".$aDLDAPServerAddress."<br>";
        exit();

        $container->loadFromExtension('fr3d_ldap', array(
            'driver' => array(
                'host'   => $aDLDAPServerAddress,               //'a.wcmc-ad.net',
                'username'   => $aDLDAPServerAccountUserName,   //'svc_aperio_spectrum@a.wcmc-ad.net',
                'password'     => $aDLDAPServerAccountPassword, //'Aperi0,123',
                'accountDomainName' => $aDLDAPServerOu,         //'a.wcmc-ad.net',
            ),
            'user' => array(
                'baseDn'   => 'dc=a,dc=wcmc-ad,dc=net'
            )
        ));

        //$this->container->setParameter('database_user', "symfony2");

        //TODO: get parameters from DB

        //$container->setParameter('test1.test','testpar');
        //$test = $container->getParameter('test1.test');
        //echo "test=".$test."<br>";

        //var_dump( $container->getParameterBag()  );

        //$ldaphost = $container->getParameter('fr3d_ldap.driver.host');
        //echo("ldaphost=".$ldaphost."<br>");

    }



}
