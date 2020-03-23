<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

//include_once('setparameters_function.php');
//include_once('setparameters_util.php');

//function getDBParameter( $row, $originalParam, $name ) {
////    if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ) {
////        //keep it for MSSQL
////    } else {
////        $name = strtolower($name);
////    }
//
//    //1) try as it is
//    if( array_key_exists($name, $row) ) {
//        //echo "1 parameter=".$row[$name]."<br>";
//        return trim($row[$name]);
//    }
//
//    //2) try with lowercase for postgresql
//    $name = strtolower($name);
//    if( array_key_exists($name, $row) ) {
//        //echo "2 parameter=".$row[$name]."<br>";
//        return trim($row[$name]);
//    }
//
//    return $originalParam;
//}
//
//function isWindows() {
//    if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ) {
//        //Windows
//        return true;
//    }
//    return false;
//}

if( ! function_exists('getDBParameter') ) {
    function getDBParameter( $row, $originalParam, $name ) {
        //1) try as it is
        if( array_key_exists($name, $row) ) {
            //echo "1 parameter=".$row[$name]."<br>";
            return trim($row[$name]);
        }

        //2) try with lowercase for postgresql
        $name = strtolower($name);
        if( array_key_exists($name, $row) ) {
            //echo "2 parameter=".$row[$name]."<br>";
            return trim($row[$name]);
        }

        return $originalParam;
    }
}
if( ! function_exists('isWindows') ) {
    function isWindows() {
        if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ) {
            //Windows
            return true;
        }
        return false;
    }
}

//$dtz = $this->container->getParameter('default_time_zone');
//echo "dtz=".$dtz."<br>";

$host = $container->getParameter('database_host');
$driver = $container->getParameter('database_driver');
$dbname = $container->getParameter('database_name');
$user = $container->getParameter('database_user');
$password = $container->getParameter('database_password');

$connection_channel = $container->getParameter('connection_channel');
if( !$connection_channel ) {
    $connection_channel = 'http';
}

//echo "driver=".$driver."<br>";
//echo "host=".$host."<br>";
//echo "dbname=".$dbname."<br>";
//echo "user=".$user."<br>";
//echo "password=".$password."<br>";

$config = new \Doctrine\DBAL\Configuration();

$connectionParams = array(
    'dbname' => $dbname,
    'user' => $user,
    'password' => $password,
    'host' => $host,
    'driver' => $driver,
    //'port' => 3306
);

//upload paths can't be NULL
$employeesuploadpath = "directory/documents";
$employeesavataruploadpath = "directory/avatars";
$container->setParameter('employees.avataruploadpath',$employeesavataruploadpath);
$container->setParameter('employees.uploadpath',$employeesuploadpath);
//scan
$scanuploadpath = "scan-order/documents";
$container->setParameter('scan.uploadpath',$scanuploadpath);
//fellapp
$fellappuploadpath = "fellapp";
$container->setParameter('fellapp.uploadpath',$fellappuploadpath);
//vacreq
$vacrequploadpath = "vacreq";
$container->setParameter('vacreq.uploadpath',$vacrequploadpath);
//transres
$transresuploadpath = "transres";
$container->setParameter('transres.uploadpath',$transresuploadpath);
//calllog
$callloguploadpath = "calllog";
$container->setParameter('calllog.uploadpath',$callloguploadpath);
//crn
$crnuploadpath = "crn";
$container->setParameter('crn.uploadpath',$crnuploadpath);

//exit("1");
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
//exit("2");

//testing
//$connected = $conn->connect();
//echo "connected=".$connected."<br>";
//echo "conn name=".$conn->getName()."<br>"; // connection 1

$table = 'user_siteParameters';

$schemaManager = $conn->getSchemaManager();
//exit("3");

if( $conn && $schemaManager->tablesExist(array($table)) == true ) {

    //exit("connected!");
    //echo("table true<br>");

    $sql = "SELECT * FROM ".$table;
    $params = $conn->query($sql); // Simple, but has several drawbacks

    //var_dump($params);
    //echo "count=".count($params)."<br>";
    //var_dump($params->fetch());

    ////////// condition to continue for empty and not empty DB ////////////
    $continue = true;
    if($params) {
    } else {
        $continue = false;
        //exit('!params');
    }
//    if(is_array($params)) {
//    } else {
//        //it is not array even if DB exists
//        $continue = false;
//        exit('!is_array($params)');
//    }
    if( $continue && (is_object($params) || is_array($params)) ) {
    } else {
        $continue = false;
        //exit('!is_object($params)');
    }
//    if( $continue && (is_object($params) || is_array($params)) && count($params) >= 1 ) {
//    } else {
//        $continue = false;
//        //exit('!count($params) >= 1)');
//    }
    $row = $params->fetch();
    if( $continue && (is_object($row) || is_array($row)) && count($row) == 0 ) {
        $continue = false;
        //exit('!count($row) == 0');
    }
    ////////// condition to continue for empty and not empty DB ////////////

    //exit('111');

    //if( $params && is_array($params) && count($params) >= 1 ) {
    if( $continue ) {

        //exit('111');

//        $aDLDAPServerAddress = null;
//        $aDLDAPServerPort = null;
//        $aDLDAPServerOu = null;
//        $aDLDAPServerAccountUserName = null;
//        $aDLDAPServerAccountPassword = null;
//        $ldapExePath = null;
//        $ldapExeFilename = null;

        $smtpServerAddress = null;
        $defaultSiteEmail = null;
        $institution_url = null;
        $institution_name = null;
        $subinstitution_url = null;
        $subinstitution_name = null;
        $department_url = null;
        $department_name = null;
        $showcopyrightonfooter = false;

        //third party software html to pdf
        $wkhtmltopdfpath = null;
        //set default Third-Party Software Dependencies for Linux used in container
        if( !isWindows() ) {
            //$wkhtmltopdfpath = "/usr/bin/xvfb-run /usr/bin/wkhtmltopdf";
            $wkhtmltopdfpath = "/usr/bin/xvfb-run wkhtmltopdf";
        }

        //titles
        $mainhome_title = null;
        $listmanager_title = null;
        $eventlog_title = null;
        $sitesettings_title = null;
        $contentabout_page = null;  //not used: now contentabout_page is getting from DB directly on the about page
        //$underlogin_msg_user = null;
        //$underlogin_msg_scan = null;

        //maintenance
//        $maintenance = null;
//        $maintenanceenddate = null;
//        $maintenanceloginmsg = null;
//        $maintenancelogoutmsg = null;

        //Symfony DB
        $database_host = null;
        $database_port = null;
        $database_name = null;
        $database_user = null;
        $database_password = null;

        //pacsvendor DB
        $database_host_pacsvendor = null;
        $database_port_pacsvendor = null;
        $database_name_pacsvendor = null;
        $database_user_pacsvendor = null;
        $database_password_pacsvendor = null;

        //set path to binary for knp_snappy
        //$knp_snappy_path = $_SERVER['DOCUMENT_ROOT']."/order/scanorder/Scanorders2/src/App/UserdirectoryBundle/Util/wkhtmltopdf/bin/";
        //$knp_snappy_path = str_replace("/","\\\\",$knp_snappy_path);
        //"\"C:\\Program Files (x86)\\pacsvendor\\pacsname\\htdocs\\order\\scanorder\\Scanorders2\\src\\App\\UserdirectoryBundle\\Util\\wkhtmltopdf\\bin\\wkhtmltopdf.exe\""
        //$knp_snappy_path_pdf = '"\"'.$knp_snappy_path.'wkhtmltopdf.exe'.'\""';
        //$knp_snappy_path_image = '"\"'.$knp_snappy_path.'wkhtmltoimage.exe'.'\""';
        //$container->setParameter('knp_snappy.pdf.binary',$knp_snappy_path_pdf);
        //$container->setParameter('knp_snappy.image.binary',$knp_snappy_path_image);
        //echo "knp_snappy.pdf.binary=".$container->getParameter('knp_snappy.pdf.binary')."<br>";

        //while( $row = $params->fetch() ) { //we have only 1 row of siteParameters
        if( $row ) {

            //print_r($row);
            //exit('111');

//            if( array_key_exists('aDLDAPServerAddress', $row) )
//                $aDLDAPServerAddress = $row['aDLDAPServerAddress'];
//            if( array_key_exists('aDLDAPServerPort', $row) )
//                $aDLDAPServerPort = $row['aDLDAPServerPort'];
//            if( array_key_exists('aDLDAPServerOu', $row) )
//                $aDLDAPServerOu = $row['aDLDAPServerOu'];
//            if( array_key_exists('aDLDAPServerAccountUserName', $row) )
//                $aDLDAPServerAccountUserName = $row['aDLDAPServerAccountUserName'];
//            if( array_key_exists('aDLDAPServerAccountPassword', $row) )
//                $aDLDAPServerAccountPassword = $row['aDLDAPServerAccountPassword'];
//            if (array_key_exists('ldapExePath', $row)) {
//                $ldapExePath = $row['ldapExePath'];
//            }
//            if (array_key_exists('ldapExeFilename', $row)) {
//                $ldapExeFilename = $row['ldapExeFilename'];
//            }

            //if( array_key_exists('smtpServerAddress', $row) ) {
//            if( $param = getDBParameter($row,'wkhtmltopdfpath') ) {
//                $smtpServerAddress = $param;
//            }
            $smtpServerAddress = getDBParameter($row,$smtpServerAddress,'wkhtmltopdfpath');

            //if( array_key_exists('siteEmail', $row) )
//            if( $param = getDBParameter($row,'siteEmail') ) {
//                $defaultSiteEmail = $param;
//            }
            $defaultSiteEmail = getDBParameter($row,$defaultSiteEmail,'siteEmail');

            //if( array_key_exists('institutionurl', $row) )
//            if( $param = getDBParameter($row,'institutionurl') ) {
//                $institution_url = $param;
//            }
            $institution_url = getDBParameter($row,$institution_url,'institutionurl');

            //if( array_key_exists('institutionname', $row) )
//            if( $param = getDBParameter($row,'institutionname') ) {
//                $institution_name = $param;
//            }
            $institution_name = getDBParameter($row,$institution_name,'institutionname');

            //if( array_key_exists('subinstitutionurl', $row) )
//            if( $param = getDBParameter($row,'subinstitutionurl') ) {
//                $subinstitution_url = $param;
//            }
            $subinstitution_url = getDBParameter($row,$subinstitution_url,'subinstitutionurl');

            //if( array_key_exists('subinstitutionname', $row) )
//            if( $param = getDBParameter($row,'subinstitutionname') ) {
//                $subinstitution_name = $param;
//            }
            $subinstitution_name = getDBParameter($row,$subinstitution_name,'subinstitutionname');

            //if( array_key_exists('departmenturl', $row) )
            //if( $param = getDBParameter($row,$department_url,'departmenturl') ) {
            //    $department_url = $param;
            //}
            $department_url = getDBParameter($row,$department_url,'departmenturl');

//            if( array_key_exists('departmentname', $row) )
//                $department_name = $row['departmentname'];
            $department_name = getDBParameter($row,$department_name,'departmentname');

//            if( array_key_exists('showCopyrightOnFooter', $row) )
//                $showcopyrightonfooter = $row['showCopyrightOnFooter'];
            $showcopyrightonfooter = getDBParameter($row,$showcopyrightonfooter,'showCopyrightOnFooter');

            //third party software html to pdf
            //echo "EOF wkhtmltopdfpath=".getDBParameter($row,'wkhtmltopdfpath')."<br>";
            //echo "EOF wkhtmltopdfpathLinux=".getDBParameter($row,'wkhtmltopdfpathLinux')."<br>";
            if( isWindows() ) {
                //Windows
//                if( $param = getDBParameter($row,'wkhtmltopdfpath') ) {
//                    $wkhtmltopdfpath = $param;
//                }
                $wkhtmltopdfpath = getDBParameter($row,$wkhtmltopdfpath,'wkhtmltopdfpath');
            } else {
                //Linux
//                if( $param = getDBParameter($row,'wkhtmltopdfpathLinux') ) {
//                    $wkhtmltopdfpath = $param;
//                }
                $wkhtmltopdfpath = getDBParameter($row,$wkhtmltopdfpath,'wkhtmltopdfpathLinux');
            }

            //employees
            //$employeesuploadpath = $row['employeesuploadpath'];
            $employeesuploadpath = getDBParameter($row,$employeesuploadpath,'employeesuploadpath');

            //$employeesavataruploadpath = $row['avataruploadpath'];
            $employeesavataruploadpath = getDBParameter($row,$employeesavataruploadpath,'avataruploadpath');

            //scan
            //$scanuploadpath = $row['scanuploadpath'];
            $scanuploadpath = getDBParameter($row,$scanuploadpath,'scanuploadpath');

            //fellapp
//            if (array_key_exists('fellappuploadpath', $row)) {
//                $fellappuploadpath = $row['fellappuploadpath'];
//            }
            $fellappuploadpath = getDBParameter($row,$fellappuploadpath,'fellappuploadpath');

            //vacreq
//            if (array_key_exists('vacrequploadpath', $row)) {
//                $vacrequploadpath = $row['vacrequploadpath'];
//            }
            $vacrequploadpath = getDBParameter($row,$vacrequploadpath,'vacrequploadpath');

            //transres
//            if (array_key_exists('transresuploadpath', $row)) {
//                $transresuploadpath = $row['transresuploadpath'];
//            }
            $transresuploadpath = getDBParameter($row,$transresuploadpath,'transresuploadpath');

            $callloguploadpath = getDBParameter($row,$callloguploadpath,'callloguploadpath');

            $crnuploadpath = getDBParameter($row,$crnuploadpath,'crnuploadpath');

            //titles
//            if( array_key_exists('mainHomeTitle', $row) )
//                $mainhome_title = $row['mainHomeTitle'];
            $mainhome_title = getDBParameter($row,$mainhome_title,'mainHomeTitle');

//            if( array_key_exists('listManagerTitle', $row) )
//                $listmanager_title = $row['listManagerTitle'];
            $listmanager_title = getDBParameter($row,$listmanager_title,'listManagerTitle');

//            if( array_key_exists('eventLogTitle', $row) )
//                $eventlog_title = $row['eventLogTitle'];
            $eventlog_title = getDBParameter($row,$eventlog_title,'eventLogTitle');

//            if( array_key_exists('siteSettingsTitle', $row) )
//                $sitesettings_title = $row['siteSettingsTitle'];
            $sitesettings_title = getDBParameter($row,$sitesettings_title,'siteSettingsTitle');

//            if( array_key_exists('contentAboutPage', $row) )
//                $contentabout_page = $row['contentAboutPage'];
            $contentabout_page = getDBParameter($row,$contentabout_page,'contentAboutPage');

            //$underlogin_msg_user = $row['underLoginMsgUser'];
            //$underlogin_msg_scan = $row['underLoginMsgScan'];
            //echo "mainhome_title=".$mainhome_title."<br>";

//            $maintenance = $row['maintenance'];
//            $maintenanceenddate = $row['maintenanceenddate'];
//            $maintenanceloginmsg = $row['maintenanceloginmsg'];
//            $maintenancelogoutmsg = $row['maintenancelogoutmsg'];
            //echo "department_url=".$department_url."<br>";

            //Symfony DB
//            if( array_key_exists('dbServerAddress', $row) )
//                $database_host = $row['dbServerAddress'];
            $database_host = getDBParameter($row,$database_host,'dbServerAddress');

//            if( array_key_exists('dbServerPort', $row) )
//                $database_port = $row['dbServerPort'];
            $database_port = getDBParameter($row,$database_port,'dbServerPort');

//            if( array_key_exists('dbDatabaseName', $row) )
//                $database_name = $row['dbDatabaseName'];
            $database_name = getDBParameter($row,$database_name,'dbDatabaseName');

//            if( array_key_exists('dbServerAccountUserName', $row) )
//                $database_user = $row['dbServerAccountUserName'];
            $database_user = getDBParameter($row,$database_user,'dbServerAccountUserName');

//            if( array_key_exists('dbServerAccountPassword', $row) )
//                $database_password = $row['dbServerAccountPassword'];
            $database_password = getDBParameter($row,$database_password,'dbServerAccountPassword');

            //pacsvendor DB
//            if( array_key_exists('pacsvendorSlideManagerDBServerAddress', $row) )
//                $database_host_pacsvendor = $row['pacsvendorSlideManagerDBServerAddress'];
            $database_host_pacsvendor = getDBParameter($row,$database_host_pacsvendor,'pacsvendorSlideManagerDBServerAddress');

//            if( array_key_exists('pacsvendorSlideManagerDBServerPort', $row) )
//                $database_port_pacsvendor = $row['pacsvendorSlideManagerDBServerPort'];
            $database_port_pacsvendor = getDBParameter($row,$database_port_pacsvendor,'pacsvendorSlideManagerDBServerPort');

//            if( array_key_exists('pacsvendorSlideManagerDBName', $row) )
//                $database_name_pacsvendor = $row['pacsvendorSlideManagerDBName'];
            $database_name_pacsvendor = getDBParameter($row,$database_name_pacsvendor,'pacsvendorSlideManagerDBName');

//            if( array_key_exists('pacsvendorSlideManagerDBUserName', $row) )
//                $database_user_pacsvendor = $row['pacsvendorSlideManagerDBUserName'];
            $database_user_pacsvendor = getDBParameter($row,$database_user_pacsvendor,'pacsvendorSlideManagerDBUserName');

//            if( array_key_exists('pacsvendorSlideManagerDBPassword', $row) )
//                $database_password_pacsvendor = $row['pacsvendorSlideManagerDBPassword'];
            $database_password_pacsvendor = getDBParameter($row,$database_password_pacsvendor,'pacsvendorSlideManagerDBPassword');

            $connection_channel = getDBParameter($row,$connection_channel,'connectionChannel');
            //echo "connection_channel=[".$connection_channel."]\n";

        }//while

        $container->setParameter('connection_channel',$connection_channel);

        $container->setParameter('mailer_host',$smtpServerAddress);
        $container->setParameter('default_system_email',$defaultSiteEmail);

        //footer params
        $container->setParameter('institution_url',$institution_url);
        $container->setParameter('institution_name',$institution_name);
        $container->setParameter('subinstitution_url',$subinstitution_url);
        $container->setParameter('subinstitution_name',$subinstitution_name);
        $container->setParameter('department_url',$department_url);
        $container->setParameter('department_name',$department_name);
        $container->setParameter('showcopyrightonfooter',$showcopyrightonfooter);

        //third party software html to pdf
        //echo "set wkhtmltopdfpath=$wkhtmltopdfpath<br>";
        //$container->setParameter('wkhtmltopdfpath','"'.$wkhtmltopdfpath.'"');
        $container->setParameter('wkhtmltopdfpath',$wkhtmltopdfpath);

        //uploads
        $container->setParameter('employees.avataruploadpath',$employeesavataruploadpath);
        $container->setParameter('employees.uploadpath',$employeesuploadpath);
        $container->setParameter('scan.uploadpath',$scanuploadpath);
        if( $fellappuploadpath )
            $container->setParameter('fellapp.uploadpath',$fellappuploadpath);
        if( $vacrequploadpath )
            $container->setParameter('vacreq.uploadpath',$vacrequploadpath);
        if( $transresuploadpath )
            $container->setParameter('transres.uploadpath',$transresuploadpath);
        if( $callloguploadpath )
            $container->setParameter('calllog.uploadpath',$callloguploadpath);
        if( $crnuploadpath )
            $container->setParameter('crn.uploadpath',$crnuploadpath);

        //titles
        $mainhome_title = str_replace("%","%%",$mainhome_title);
        $container->setParameter('mainhome_title',$mainhome_title);
        $listmanager_title = str_replace("%","%%",$listmanager_title);
        $container->setParameter('listmanager_title',$listmanager_title);
        $eventlog_title = str_replace("%","%%",$eventlog_title);
        $container->setParameter('eventlog_title',$eventlog_title);
        $sitesettings_title = str_replace("%","%%",$sitesettings_title);
        $container->setParameter('sitesettings_title',$sitesettings_title);

        //The percent sign inside a parameter or argument, as part of the string, must be escaped with another percent sign: % -> %%
        $contentabout_page = str_replace("%","%%",$contentabout_page);
        $container->setParameter('contentabout_page',$contentabout_page);

        //ldap
//        if( $aDLDAPServerAddress )
//            $container->setParameter('ldaphost',$aDLDAPServerAddress);
//        if( $aDLDAPServerPort )
//            $container->setParameter('ldapport',$aDLDAPServerPort);
//        if( $aDLDAPServerAccountUserName )
//            $container->setParameter('ldapusername',$aDLDAPServerAccountUserName);
//        if( $aDLDAPServerAccountPassword )
//            $container->setParameter('ldappassword',$aDLDAPServerAccountPassword);
//        if( $aDLDAPServerOu )
//            $container->setParameter('ldapou',$aDLDAPServerOu);
//        if( $ldapExePath )
//            $container->setParameter('ldapexepath',$ldapExePath);
//        if( $ldapExeFilename )
//            $container->setParameter('ldapexefilename',$ldapExeFilename);

        //maintenance
//        $container->setParameter('maintenance',$maintenance);
//        $container->setParameter('maintenanceenddate',$maintenanceenddate);
//        $container->setParameter('maintenanceloginmsg',$maintenanceloginmsg);
//        $container->setParameter('maintenancelogoutmsg',$maintenancelogoutmsg);
        //echo "maint=".$this->container->getParameter('maintenance')."<br>";
        //echo "department_url=".$department_url."<br>";
        //echo "container department_url=".$this->container->getParameter('department_url')."<br>";

        //TODO: assign a new parameters for DB does not work
        //Symfony DB
//        echo "database_host=[".$database_host."]<br>";
//        echo "database_port=[".$database_port."]<br>";
//        echo "database_name=[".$database_name."]<br>";
//        echo "database_user=[".$database_user."]<br>";
//        echo "database_password=[".$database_password."]<br>";

//        if( $database_host )
//            $container->setParameter('database_host',trim($database_host));
//        if( $database_port )
//            $container->setParameter('database_port',trim($database_port));
//        if( $database_name )
//            $container->setParameter('database_name',trim($database_name));
//        if( $database_user )
//            $container->setParameter('database_user',trim($database_user));
//        if( $database_password )
//            $container->setParameter('database_password',$database_password);

    } else {
        //var_dump($params);
        //exit("params are not valid<br>");
    }//if param


} else {
    //exit("table false<br>");
    //echo("table false<br>");
} //if


