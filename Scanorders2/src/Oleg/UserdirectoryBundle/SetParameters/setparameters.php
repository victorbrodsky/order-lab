<?php

//include_once('setparameters_function.php');

//$dtz = $this->container->getParameter('default_time_zone');
//echo "dtz=".$dtz."<br>";

$host = $container->getParameter('database_host');
$driver = $container->getParameter('database_driver');
$dbname = $container->getParameter('database_name');
$user = $container->getParameter('database_user');
$password = $container->getParameter('database_password');
//echo "host=".$host."<br>";

//setLDAPParametersFromDB_ScanOrder($container,$host,$driver,$dbname,$user,$password);

$config = new \Doctrine\DBAL\Configuration();

$connectionParams = array(
    'dbname' => $dbname,
    'user' => $user,
    'password' => $password,
    'host' => $host,
    'driver' => $driver,
);

//upload paths can't be NULL
$employeesuploadpath = "directory/Documents";
$scanuploadpath = "scan-order/Documents";
$employeesavataruploadpath = "directory/Avatars";
$container->setParameter('employees.avataruploadpath',$employeesavataruploadpath);
$container->setParameter('employees.uploadpath',$employeesuploadpath);
$container->setParameter('scan.uploadpath',$scanuploadpath);

$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$table = 'user_siteParameters';

$schemaManager = $conn->getSchemaManager();

if( $conn && $schemaManager->tablesExist(array($table)) == true ) {
    //echo("table true<br>");

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
        $defaultSiteEmail = null;
        $institution_url = null;
        $institution_name = null;
        $department_url = null;
        $department_name = null;

        //maintenance
//        $maintenance = null;
//        $maintenanceenddate = null;
//        $maintenanceloginmsg = null;
//        $maintenancelogoutmsg = null;

        while( $row = $params->fetch() ) {

            $aDLDAPServerAddress = $row['aDLDAPServerAddress'];
            $aDLDAPServerOu = $row['aDLDAPServerOu'];
            $aDLDAPServerAccountUserName = $row['aDLDAPServerAccountUserName'];
            $aDLDAPServerAccountPassword = $row['aDLDAPServerAccountPassword'];
            $smtpServerAddress = $row['smtpServerAddress'];

            $defaultSiteEmail = $row['siteEmail'];

            $institution_url = $row['institutionurl'];
            $institution_name = $row['institutionname'];
            $department_url = $row['departmenturl'];
            $department_name = $row['departmentname'];

            $employeesuploadpath = $row['employeesuploadpath'];
            $scanuploadpath = $row['scanuploadpath'];
            $employeesavataruploadpath = $row['avataruploadpath'];

//            $maintenance = $row['maintenance'];
//            $maintenanceenddate = $row['maintenanceenddate'];
//            $maintenanceloginmsg = $row['maintenanceloginmsg'];
//            $maintenancelogoutmsg = $row['maintenancelogoutmsg'];
            //echo "department_url=".$department_url."<br>";
        }

        //echo "aDLDAPServerAddress=".$aDLDAPServerAddress."<br>";
        //exit("aDLDAPServerAddress=".$aDLDAPServerAddress);

        if( $aDLDAPServerAddress && $aDLDAPServerAddress != "" ) {

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
        } else {
            //exit(" aDLDAPServerAddress is empty ");
        }

        $container->setParameter('default_system_email',$defaultSiteEmail);

        //footer params
        $container->setParameter('institution_url',$institution_url);
        $container->setParameter('institution_name',$institution_name);
        $container->setParameter('department_url',$department_url);
        $container->setParameter('department_name',$department_name);

        //uploads
        $container->setParameter('employees.avataruploadpath',$employeesavataruploadpath);
        $container->setParameter('employees.uploadpath',$employeesuploadpath);
        $container->setParameter('scan.uploadpath',$scanuploadpath);

        //maintenance
//        $container->setParameter('maintenance',$maintenance);
//        $container->setParameter('maintenanceenddate',$maintenanceenddate);
//        $container->setParameter('maintenanceloginmsg',$maintenanceloginmsg);
//        $container->setParameter('maintenancelogoutmsg',$maintenancelogoutmsg);
        //echo "maint=".$this->container->getParameter('maintenance')."<br>";
        //echo "department_url=".$department_url."<br>";
        //echo "container department_url=".$this->container->getParameter('department_url')."<br>";

    }//if param


} else {
    //exit("table false<br>");
    //echo("table false<br>");
}
