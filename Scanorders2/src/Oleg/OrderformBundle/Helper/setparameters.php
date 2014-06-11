<?php

//include_once('/path/to/drupal/sites/default/settings.php');


//$em = $container->loadFromExtension('doctrine')->getEntityManager();
//$em = $this->container->get('doctrine.orm.entity_manager');

//$doctrine = $container->loadFromExtension('doctrine');
//var_dump($doctrine);

//$setparameters = $container->get('scanorder.setparameters');


//$dtz = $this->container->getParameter('default_time_zone');
//echo "dtz=".$dtz."<br>";

//read: http://symfony.com/doc/current/components/config/definition.html

//working ok!
//$container->loadFromExtension('fr3d_ldap', array(
//    'driver' => array(
//        'host'   => 'a.wcmc-ad.net',
//        'username'   => 'svc_aperio_spectrum@a.wcmc-ad.net',
//        'password'     => 'Aperi0,123',
//        'accountDomainName' => 'a.wcmc-ad.net',
//    ),
//    'user' => array(
//        'baseDn'   => 'dc=a,dc=wcmc-ad,dc=net'
//    )
//));

//$fr3d = $container->loadFromExtension('fr3d_ldap');
//var_dump($fr3d['driver']);


//$doctrine = $container->loadFromExtension('doctrine',array(
//    'dbal' => array(
//        'host'
//    )
//));
//var_dump($doctrine);

//var_dump($doctrine->dbal);

//echo "dbname=".$doctrine->host."<br>";

$host = $container->getParameter('database_host');
$driver = $container->getParameter('database_driver');
$dbname = $container->getParameter('database_name');
$user = $container->getParameter('database_user');
$password = $container->getParameter('database_password');
//echo "host=".$host."<br>";

setLDAPParametersFromDB_ScanOrder($container,$host,$driver,$dbname,$user,$password);


    function checkParameterTable_ScanOrder($table, $conn) {
        $schemaManager = $conn->getSchemaManager();
        if( $schemaManager->tablesExist(array($table)) == true ) {
            return true;
        } else {
            return false;
        }
//        try {
//            $sql = "DESC ".$table;
//            //echo "sql=".$sql."<br>";
//            $conn->query("DESC ".$table);
//        }
//        catch (Exception $e) {
//            //echo "Exception=".$e."<br>";
//            return false;
//        }
//        return true;
    }

    function setLDAPParametersFromDB_ScanOrder($container,$host,$driver,$dbname,$user,$password) {

        if( !$host || $host == "" ) {
            //exit("host is not defined, host=" . $host);
            return;
        }

        $config = new \Doctrine\DBAL\Configuration();

        $connectionParams = array(
            'dbname' => $dbname,
            'user' => $user,
            'password' => $password,
            'host' => $host,
            'driver' => $driver,
        );

        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

        $table = 'siteParameters';

        if( !checkParameterTable_ScanOrder($table, $conn) ) {
            //exit($table." does not exists");
            return;
        }

        if( $conn ) {

            $sql = "SELECT * FROM ".$table;
            $params = $conn->query($sql); // Simple, but has several drawbacks

            //var_dump($params);
            //echo "count=".count($params)."<br>";

            if( $params && count($params) >= 1 ) {

                $aDLDAPServerAddress = null;
                $aDLDAPServerOu = null;
                $aDLDAPServerAccountUserName = null;
                $aDLDAPServerAccountPassword = null;
                $baseDn = null;
                $smtpServerAddress = null;

                while( $row = $params->fetch() ) {
                    $aDLDAPServerAddress = $row['aDLDAPServerAddress'];
                    $aDLDAPServerOu = $row['aDLDAPServerOu'];
                    $aDLDAPServerAccountUserName = $row['aDLDAPServerAccountUserName'];
                    $aDLDAPServerAccountPassword = $row['aDLDAPServerAccountPassword'];
                    $smtpServerAddress = $row['smtpServerAddress'];
                    //echo $aDLDAPServerAddress."<br>";
                }

                //get baseDn from $aDLDAPServerOu or $aDLDAPServerAddress: a.wcmc-ad.net => dc=a,dc=wcmc-ad,dc=net
                $pieces = explode(".", $aDLDAPServerOu);
                $baseDn = "dc=".$pieces[0].",dc=".$pieces[1].",dc=".$pieces[2];
                //echo "baseDn=".$baseDn."<br>";

                //set fr3d_ldap
                $container->loadFromExtension('fr3d_ldap', array(
                    'driver' => array(
                        'host'   => $aDLDAPServerAddress,               //'a.wcmc-ad.net',
                        'username'   => $aDLDAPServerAccountUserName,   //'svc_aperio_spectrum@a.wcmc-ad.net',
                        'password'     => $aDLDAPServerAccountPassword, //'Aperi0,123',
                        'accountDomainName' => $aDLDAPServerOu,         //'a.wcmc-ad.net',
                    ),
                    'user' => array(
                        'baseDn'   => $baseDn                           //'dc=a,dc=wcmc-ad,dc=net'
                    )
                ));

                //set $smtpServerAddress
                $container->setParameter('mailer_host',$smtpServerAddress);

            }//if param
        }//if conn
    }//function




