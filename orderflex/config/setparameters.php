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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$useDb = true;
//$useDb = false; //use when new fields are added to the "SiteParameters" entity
//exit('start user_siteparameters');

//echo "*** Run siteparameters.php ***\n"; //testing

if( $useDb ) {

    if (!function_exists('getDBParameter')) {
        function getDBParameter($row, $originalParam, $name)
        {
            //1) try as it is
            if (array_key_exists($name, $row)) {
                //echo "1 parameter=".$row[$name]."<br>";
                return trim((string)$row[$name]);
            }

            //2) try with lowercase for postgresql
            $name = strtolower($name);
            if (array_key_exists($name, $row)) {
                //echo "2 parameter=".$row[$name]."<br>";
                return trim((string)$row[$name]);
            }

            return $originalParam;
        }
    }
    if (!function_exists('isWindows')) {
        function isWindows()
        {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                //Windows
                return true;
            }
            return false;
        }
    }
}

//$dtz = $this->container->getParameter('default_time_zone');
//echo "dtz=".$dtz."<br>";

echo "*** siteparameters.php: Runing siteparameters.php ***\n"; //testing

$host = $container->getParameter('database_host');
$driver = $container->getParameter('database_driver');
$dbname = $container->getParameter('database_name');
$user = $container->getParameter('database_user');
$password = $container->getParameter('database_password');

$connection_channel = $container->getParameter('connection_channel');
if( !$connection_channel ) {
    $connection_channel = 'http';
}
//echo "*** siteparameters.php: Initial connection_channel=[".$connection_channel."] ***\n"; //testing

//echo "driver=".$driver."<br>";
//echo "host=".$host."<br>";
//echo "dbname=".$dbname."<br>";
//echo "user=".$user."<br>";
//echo "password=".$password."<br>";

//upload paths can't be NULL
$tenantprefix = '';
$container->setParameter('tenantprefix', $tenantprefix);

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
//resapp
$resappuploadpath = "resapp";
$container->setParameter('resapp.uploadpath',$resappuploadpath);
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
//dashboard
$dashboarduploadpath = "dashboard";
$container->setParameter('dashboard.uploadpath',$dashboarduploadpath);

$container->setParameter('mailer_dsn', "null://null");


$config = new \Doctrine\DBAL\Configuration();
$config->setSchemaManagerFactory(new \Doctrine\DBAL\Schema\DefaultSchemaManagerFactory());

$connectionParams = array(
    'dbname' => $dbname,
    'user' => $user,
    'password' => $password,
    'host' => $host,
    'driver' => $driver,
    //'port' => 3306
);

//exit("1");
if( $useDb ) {
    $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
} else {
    $conn = NULL;
}
//exit("2");

//testing
//$connected = $conn->connect();
//echo "connected=".$connected."<br>";
//echo "conn name=".$conn->getName()."<br>"; // connection 1

if( $conn ) {

    echo "*** siteparameters.php: Connection to DB established ***\n";

    //$table = 'user_siteParameters';
    $table = 'user_siteparameters';

    //$schemaManager = $conn->getSchemaManager();
    $schemaManager = $conn->createSchemaManager();
    //exit("3");

    if ($conn && $schemaManager->tablesExist(array($table)) == true) {

        //exit("connected!");
        //echo("table true<br>");

        $sql = "SELECT * FROM " . $table;
        $params = $conn->query($sql); // Simple, but has several drawbacks

        //var_dump($params);
        //echo "count=".count($params)."<br>";
        //var_dump($params->fetch());

        ////////// condition to continue for empty and not empty DB ////////////
        $continue = true;
        if ($params) {
        } else {
            $continue = false;
            //exit('!params');
        }

        if ($continue && (is_object($params) || is_array($params))) {
        } else {
            $continue = false;
            //exit('!is_object($params)');
        }

        $row = $params->fetch();
        if ($continue && (is_object($row) || is_array($row)) && count($row) == 0) {
            $continue = false;
            //exit('!count($row) == 0');
        }
        ////////// condition to continue for empty and not empty DB ////////////

        //exit('111');

        //if( $params && is_array($params) && count($params) >= 1 ) {
        if ($continue) {

            echo "*** siteparameters.php: DB is not empty. Overwrite container's parameters ***\n";
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
            if (!isWindows()) {
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

            if(0) {
                $mailer_host = NULL;
                $mailer_password = NULL;
                $mailer_user = NULL;
                $mailer_port = NULL;
            }

            if( $row && is_array($row) ) {

                //print_r($row);
                //exit('111');

                $smtpServerAddress = getDBParameter($row, $smtpServerAddress, 'wkhtmltopdfpath');
                $defaultSiteEmail = getDBParameter($row, $defaultSiteEmail, 'siteEmail');
                $institution_url = getDBParameter($row, $institution_url, 'institutionurl');
                $institution_name = getDBParameter($row, $institution_name, 'institutionname');
                $subinstitution_url = getDBParameter($row, $subinstitution_url, 'subinstitutionurl');
                $subinstitution_name = getDBParameter($row, $subinstitution_name, 'subinstitutionname');
                $department_url = getDBParameter($row, $department_url, 'departmenturl');
                $department_name = getDBParameter($row, $department_name, 'departmentname');
                $showcopyrightonfooter = getDBParameter($row, $showcopyrightonfooter, 'showCopyrightOnFooter');

                //third party software html to pdf
                //echo "EOF wkhtmltopdfpath=".getDBParameter($row,'wkhtmltopdfpath')."<br>";
                //echo "EOF wkhtmltopdfpathLinux=".getDBParameter($row,'wkhtmltopdfpathLinux')."<br>";
                if (isWindows()) {
                    //Windows
                    $wkhtmltopdfpath = getDBParameter($row, $wkhtmltopdfpath, 'wkhtmltopdfpath');
                } else {
                    //Linux
                    $wkhtmltopdfpath = getDBParameter($row, $wkhtmltopdfpath, 'wkhtmltopdfpathLinux');
                }

                //employees
                $employeesuploadpath = getDBParameter($row, $employeesuploadpath, 'employeesuploadpath');
                $employeesavataruploadpath = getDBParameter($row, $employeesavataruploadpath, 'avataruploadpath');

                //scan
                $scanuploadpath = getDBParameter($row, $scanuploadpath, 'scanuploadpath');

                //fellapp
                $fellappuploadpath = getDBParameter($row, $fellappuploadpath, 'fellappuploadpath');

                //resapp
                $resappuploadpath = getDBParameter($row, $resappuploadpath, 'resappuploadpath');

                //vacreq
                $vacrequploadpath = getDBParameter($row, $vacrequploadpath, 'vacrequploadpath');

                //transres
                $transresuploadpath = getDBParameter($row, $transresuploadpath, 'transresuploadpath');

                $callloguploadpath = getDBParameter($row, $callloguploadpath, 'callloguploadpath');

                $crnuploadpath = getDBParameter($row, $crnuploadpath, 'crnuploadpath');

                $dashboarduploadpath = getDBParameter($row, $dashboarduploadpath, 'dashboarduploadpath');

                //titles
                $mainhome_title = getDBParameter($row, $mainhome_title, 'mainHomeTitle');
                $listmanager_title = getDBParameter($row, $listmanager_title, 'listManagerTitle');
                $eventlog_title = getDBParameter($row, $eventlog_title, 'eventLogTitle');
                $sitesettings_title = getDBParameter($row, $sitesettings_title, 'siteSettingsTitle');
                $contentabout_page = getDBParameter($row, $contentabout_page, 'contentAboutPage');

                //Symfony DB
                $database_host = getDBParameter($row, $database_host, 'dbServerAddress');
                $database_port = getDBParameter($row, $database_port, 'dbServerPort');
                $database_name = getDBParameter($row, $database_name, 'dbDatabaseName');
                $database_user = getDBParameter($row, $database_user, 'dbServerAccountUserName');
                $database_password = getDBParameter($row, $database_password, 'dbServerAccountPassword');

                //pacsvendor DB
                $database_host_pacsvendor = getDBParameter($row, $database_host_pacsvendor, 'pacsvendorSlideManagerDBServerAddress');
                $database_port_pacsvendor = getDBParameter($row, $database_port_pacsvendor, 'pacsvendorSlideManagerDBServerPort');
                $database_name_pacsvendor = getDBParameter($row, $database_name_pacsvendor, 'pacsvendorSlideManagerDBName');
                $database_user_pacsvendor = getDBParameter($row, $database_user_pacsvendor, 'pacsvendorSlideManagerDBUserName');
                $database_password_pacsvendor = getDBParameter($row, $database_password_pacsvendor, 'pacsvendorSlideManagerDBPassword');

                $connection_channel = getDBParameter($row, $connection_channel, 'connectionChannel');
                //$connection_channel = 'http'; //testing
                //echo "*** siteparameters.php: connection_channel=[".$connection_channel."] ***\n"; //testing
            }//if $row

            //echo "connection_channel=[".$connection_channel."]\n"; //testing
            $container->setParameter('connection_channel', $connection_channel);

            $container->setParameter('mailer_host', $smtpServerAddress);
            $container->setParameter('default_system_email', $defaultSiteEmail);

            //footer params
            $container->setParameter('institution_url', $institution_url);
            $container->setParameter('institution_name', $institution_name);
            $container->setParameter('subinstitution_url', $subinstitution_url);
            $container->setParameter('subinstitution_name', $subinstitution_name);
            $container->setParameter('department_url', $department_url);
            $container->setParameter('department_name', $department_name);
            $container->setParameter('showcopyrightonfooter', $showcopyrightonfooter);

            //third party software html to pdf
            //echo "set wkhtmltopdfpath=$wkhtmltopdfpath<br>";
            //$container->setParameter('wkhtmltopdfpath','"'.$wkhtmltopdfpath.'"');
            $container->setParameter('wkhtmltopdfpath', $wkhtmltopdfpath);

            //uploads
            $container->setParameter('employees.avataruploadpath', $employeesavataruploadpath);
            $container->setParameter('employees.uploadpath', $employeesuploadpath);
            $container->setParameter('scan.uploadpath', $scanuploadpath);
            if ($fellappuploadpath)
                $container->setParameter('fellapp.uploadpath', $fellappuploadpath);
            if ($resappuploadpath)
                $container->setParameter('resapp.uploadpath', $resappuploadpath);
            if ($vacrequploadpath)
                $container->setParameter('vacreq.uploadpath', $vacrequploadpath);
            if ($transresuploadpath)
                $container->setParameter('transres.uploadpath', $transresuploadpath);
            if ($callloguploadpath)
                $container->setParameter('calllog.uploadpath', $callloguploadpath);
            if ($crnuploadpath)
                $container->setParameter('crn.uploadpath', $crnuploadpath);
            if ($dashboarduploadpath) {
                $container->setParameter('dashboard.uploadpath', $dashboarduploadpath);
            }

            //titles
            if( $mainhome_title) {
                $mainhome_title = str_replace("%", "%%", $mainhome_title);
                $container->setParameter('mainhome_title', $mainhome_title);
            }

            if( $listmanager_title) {
                $listmanager_title = str_replace("%", "%%", $listmanager_title);
                $container->setParameter('listmanager_title', $listmanager_title);
            }

            if( $eventlog_title ) {
                $eventlog_title = str_replace("%", "%%", $eventlog_title);
                $container->setParameter('eventlog_title', $eventlog_title);
            }

            if( $sitesettings_title ) {
                $sitesettings_title = str_replace("%", "%%", $sitesettings_title);
                $container->setParameter('sitesettings_title', $sitesettings_title);
            }

            //The percent sign inside a parameter or argument, as part of the string, must be escaped with another percent sign: % -> %%
            if( $contentabout_page ) {
                $contentabout_page = str_replace("%", "%%", $contentabout_page);
                $container->setParameter('contentabout_page', $contentabout_page);
            }

            ////////////// Dynamically change url prefix /////////////
            //Test multi tenancy
            //1) IF "Server Role and Network Access:" = "Internet (Hub)”
            //2) Then: get url prefix from HostedUserGroupList (parent/child1/child2 ...) or "Tandem Partner Server URL" (authPartnerServer) or (?)
            //3) set tenantid $tenantprefix = authPartnerServer

            //////// tenantprefix is used in base.html.twig to set hidden id="tenantprefix" ////////
            //////// and then is used in getCommonBaseUrl. ////////
            //////// It is not need if locale is used ////////
            //importat to have closing '/' to form url correctly /%multitenancy_prefix%deidentifier => /c/wcm/pathology/deidentifier
            //$tenantprefix = 'c/wcm/pathology/';
            //$tenantprefix = 'c/lmh/pathology/';
            $tenantprefix = ''; //default prefix as it was in the original configuration
            $container->setParameter('tenantprefix', $tenantprefix);
            //////// EOF tenantprefix is used in base.html.twig to set hidden id="tenantprefix" ////////

            //defaultlocale is used in translation.yaml to set default translation for main home page with '/'
            //$defaultLocale = 'main';
            $defaultLocale = '';
            $container->setParameter('defaultlocale', $defaultLocale);

            $multilocales = '';
            $container->setParameter('multilocales', $multilocales);
            $container->setParameter('localedel', "");

            //$locales = "c/wcm/pathology|c/lmh/pathology";
            //$container->setParameter('locales', $locales);

            //$default_locale = $container->getParameter('framework.default_locale');
            //echo "default_locale=" . $default_locale . "<br>";
            //$default_locale = $container->getParameter('framework.translator.default_locale');
            //echo "default_locale=" . $default_locale . "<br>";
            $multitenancy = 'singletenancy';
            //$multitenancy = 'multitenancy';
            //Get DB: from AuthServerNetworkList if 'Internet (Hub)'
            $authServerNetwork = null;
            $authServerNetwork = getDBParameter($row, $authServerNetwork, 'authservernetwork_id');
            if( $authServerNetwork ) {
                //dump($authServerNetwork);
                //dump($row);
                echo "authServerNetwork=".$authServerNetwork."\n";
                $table = 'user_authservernetworklist';
                $authServerNetworkSql = "SELECT * FROM " . $table . " WHERE id=$authServerNetwork";
                $authServerNetworkParams = $conn->query($authServerNetworkSql); // Simple, but has several drawbacks
                $authServerNetworkRow = $authServerNetworkParams->fetch();
                //dump($authServerNetworkRow);
                //echo "authServerNetworkRow=" . count($authServerNetworkRow) . "<br>";
                if( count($authServerNetworkRow) > 0 ) {
                    //dump($authServerNetworkRow);
                    $authServerNetworkName = $authServerNetworkRow['name'];
                    echo "authServerNetworkName=" . $authServerNetworkName . "\n";
                }
                //exit('111');
                if( $authServerNetworkName == 'Internet (Hub)' ) {
                    $multitenancy = 'multitenancy';
                    $container->setParameter('defaultlocale', 'main');

                    //TODO: get from DB
                    $multilocales = 'main|c/wcm/pathology|c/lmh/pathology';
                    $container->setParameter('multilocales', $multilocales);

                    $container->setParameter('locdel', "/"); //locale delimeter '/'
                    //$container->setParameter('seclocales', $multilocales."(%localedel%)");
//                    //Load security's access_control yaml for multitatncy
//                    $configDirectory = '../config/custom';
//                    $locator = new FileLocator($configDirectory);
//                    $loader = new YamlFileLoader($container, $locator);
//                    $loader->load('security_access_control.yml');
                }
            }
            echo "multitenancy=" . $multitenancy . "\n";
            $container->setParameter('multitenancy', $multitenancy);

            //$container->get('router')->getContext()->setParameter('tenantprefix', $tenantprefix);
            //$router->getContext()->setParameter('tenantprefix', $tenantprefix);
            ////////////// EOF Dynamically change url prefix /////////////

        } else {
            echo "*** siteparameters.php: DB is empty. Do not overwrite container's parameters ***\n";
            //var_dump($params);
            //exit("params are not valid<br>");
        }//if param


    } else {
        //exit("table false<br>");
        //echo("table false<br>");
    } //if $conn && $schemaManager

}//if $conn
else {
    echo "*** siteparameters.php: No connection to DB ***\n";
}


