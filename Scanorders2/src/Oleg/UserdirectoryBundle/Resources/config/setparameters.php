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

        //titles
        $mainhome_title = null;
        $listmanager_title = null;
        $eventlog_title = null;
        $sitesettings_title = null;
        $contentabout_page = null;
        //$underlogin_msg_user = null;
        //$underlogin_msg_scan = null;

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


            //titles
            $mainhome_title = $row['mainHomeTitle'];
            $listmanager_title = $row['listManagerTitle'];
            $eventlog_title = $row['eventLogTitle'];
            $sitesettings_title = $row['siteSettingsTitle'];
            $contentabout_page = $row['contentAboutPage'];
            //$underlogin_msg_user = $row['underLoginMsgUser'];
            //$underlogin_msg_scan = $row['underLoginMsgScan'];
            //echo "mainhome_title=".$mainhome_title."<br>";

//            $maintenance = $row['maintenance'];
//            $maintenanceenddate = $row['maintenanceenddate'];
//            $maintenanceloginmsg = $row['maintenanceloginmsg'];
//            $maintenancelogoutmsg = $row['maintenancelogoutmsg'];
            //echo "department_url=".$department_url."<br>";
        }

        //echo "aDLDAPServerAddress=".$aDLDAPServerAddress."<br>";
        //exit("aDLDAPServerAddress=".$aDLDAPServerAddress);

        //testing
//        $aDLDAPServerAddress = null;
//
//        if( $aDLDAPServerAddress && $aDLDAPServerAddress != "" ) {
//
//            //get baseDn from $aDLDAPServerOu or $aDLDAPServerAddress: a.wcmc-ad.net => dc=a,dc=wcmc-ad,dc=net
//            $pieces = explode(".", $aDLDAPServerOu);
//            $baseDn = "dc=".$pieces[0].",dc=".$pieces[1].",dc=".$pieces[2];
//            //echo "baseDn=".$baseDn."<br>";
//
//            //set fr3d_ldap
//            $container->loadFromExtension('fr3d_ldap', array(
//                'driver' => array(
//                    'host'   => $aDLDAPServerAddress,
//                    'username'   => $aDLDAPServerAccountUserName,
//                    'password'     => $aDLDAPServerAccountPassword,
//                    'accountDomainName' => $aDLDAPServerOu,         //'a.wcmc-ad.net',
//                ),
//                'user' => array(
//                    'baseDn'   => $baseDn                           //'dc=a,dc=wcmc-ad,dc=net'
//                )
//            ));
//
//            //set $smtpServerAddress
//            $container->setParameter('mailer_host',$smtpServerAddress);
//        } else {
//            //exit(" aDLDAPServerAddress is empty ");
//        }

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

        //titles
        $container->setParameter('mainhome_title',$mainhome_title);
        $container->setParameter('listmanager_title',$listmanager_title);
        $container->setParameter('eventlog_title',$eventlog_title);
        $container->setParameter('sitesettings_title',$sitesettings_title);
        $container->setParameter('contentabout_page',$contentabout_page);

        //ldap
        $container->setParameter('ldaphost',$aDLDAPServerAddress);
        $container->setParameter('ldapusername',$aDLDAPServerAccountUserName);
        $container->setParameter('ldappassword',$aDLDAPServerAccountPassword);

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
