<?php

namespace Oleg\OrderformBundle\Helper;

class Parameters {

    private $em;
    private $sc;
    private $serviceContainer;

    public function __construct(ObjectManager $em, SecurityContext $sc, $serviceContainer = null)
    {
        $this->em = $em;
        $this->sc = $sc;
        $this->serviceContainer = $serviceContainer;

        $this->setParameters();

        //exit("Test");
    }


    public function setParameters() {

        $container = $this->sc;

        //$this->container->setParameter('database_user', "symfony2");

        //TODO: get parameters from DB

        $container->setParameter('test1.test','testpar');
        $test = $container->getParameter('test1.test');
        //echo "test=".$test."<br>";

        //var_dump( $container->getParameterBag()  );

        //$ldaphost = $container->getParameter('fr3d_ldap.driver.host');
        //echo("ldaphost=".$ldaphost."<br>");


        //LDAP (ignored ?)
        $container->setParameter('fr3d_ldap.driver.host', "a.wcmc-ad.net");
        //$this->container->setParameter('fr3d_ldap.driver.username', "svc_aperio_spectrum@a.wcmc-ad.net");
        //$this->container->setParameter('fr3d_ldap.driver.password', "Aperi0,123");
        //$this->container->setParameter('fr3d_ldap.driver.accountDomainName', "");
        //$this->container->setParameter('fr3d_ldap.user.baseDn', "dc=a,dc=wcmc-ad,dc=net");

    }



}
