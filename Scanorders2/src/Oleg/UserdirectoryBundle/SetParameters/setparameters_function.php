<?php

function checkParametersTable_ScanOrder_2($table, $conn) {
        try {
            $sql = "DESC ".$table;
            //echo "sql=".$sql."<br>";
            $conn->query($sql);
        }
        catch (Exception $e) {
            //echo "Exception=".$e."<br>";
            return false;
        }
        return true;
}

function checkParametersTable_ScanOrder($table, $conn) {
    $schemaManager = $conn->getSchemaManager();
    if( $schemaManager->tablesExist(array($table)) == true ) {
        //exit("table true<br>");
        //echo("table true<br>");
        return true;
    } else {
        //exit("table false<br>");
        //echo("table false<br>");
        return false;
    }
}

function setLDAPParametersFromDB_ScanOrder($container,$host,$driver,$dbname,$user,$password) {

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

    if( !checkParametersTable_ScanOrder($table, $conn) ) {
        //exit($table." does not exists");
        return;
    }

    //exit("before checking conn");

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

            //echo $aDLDAPServerAddress."<br>";

            if( !$aDLDAPServerAddress || $aDLDAPServerAddress == "" ) {
                return;
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
