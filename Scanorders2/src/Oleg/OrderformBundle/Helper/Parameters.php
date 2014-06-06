<?php

//include_once('/path/to/drupal/sites/default/settings.php');

//$this->container->setParameter('database_user', "symfony2");

//TODO: get parameters from DB

//LDAP (ignored ?)
$this->container->setParameter('fr3d_ldap.driver.host', "a.wcmc-ad.net");
//$this->container->setParameter('fr3d_ldap.driver.username', "svc_aperio_spectrum@a.wcmc-ad.net");
//$this->container->setParameter('fr3d_ldap.driver.password', "Aperi0,123");
//$this->container->setParameter('fr3d_ldap.driver.accountDomainName', "");
$this->container->setParameter('fr3d_ldap.user.baseDn', "dc=a,dc=wcmc-ad,dc=net");




?>
