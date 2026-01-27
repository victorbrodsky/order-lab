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

require 'base.php';
//require 'multitenancy.php';

echo "*** siteparameters.php: Runing siteparameters.php ***\n"; //testing

//Testing
putenv('APP_ENV=dev');
putenv('APP_DEBUG=1');

$useDb = true;
//$useDb = false; //use when new fields are added to the "SiteParameters" entity
//TODO: why getSiteSettingParameter (or getSingleSiteSettingParameter) is called when clear cache
//TODO: PdfUtil calls getSiteSettingParameter in construct!

$conn = null;

///////// Connect DB //////////
if( $useDb ) {
    //$container->setParameter('systemdb',false);

    $config = new \Doctrine\DBAL\Configuration();
    $config->setSchemaManagerFactory(new \Doctrine\DBAL\Schema\DefaultSchemaManagerFactory());

    /////// EOF Check if system DB exists ///////
    if (!$conn) {
        //system DB does not exists => use default DB
        $driver = $container->getParameter('database_driver');
        $host = $container->getParameter('database_host');
        $port = $container->getParameter('database_port');
        $dbname = $container->getParameter('database_name');
        $user = $container->getParameter('database_user');
        $password = $container->getParameter('database_password');
        $connectionParams = array(
            'driver' => $driver,
            'host' => $host,
            'port' => $port,
            'dbname' => $dbname,
            'user' => $user,
            'password' => $password
        );
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

        if ($conn) {
            $dbname = null;
            try {
                $dbname = $conn->getDatabase();
            } catch (Exception $e) {
                //exit('NO');
                echo "<br>*** siteparameters.php: Failed to connect to default Database. DB has not been created yet, use default site settings. ***\n\r" . "<br>";
                //echo "<br>*** siteparameters.php: Ignore the following error message ***\n\r" . $e->getMessage() . "<br>";
                $conn = null;
            }
        } else {
            echo "<br>*** siteparameters.php: default DB conn is null ***\n\r <br>";
            $conn = null;
        }

    //    echo "isConnected=".$conn->isConnected()."<br>";
    //    if( !$conn ) {
    //    //if( !$conn->isConnected() ) {
    //        //$conn = null;
    //        echo "<br>*** Warning: No connection to defaulat DB!!! ***\n<br>";
    //    } else {
    //        echo "<br>*** Connection to defaulat DB!!! ***\n<br>";
    //    }
    }
} //if $useDb
///////// EOF Connect DB //////////

$connection_channel = $container->getParameter('connection_channel');
if( !$connection_channel ) {
    $connection_channel = 'http';
}
echo "*** siteparameters.php: Initial connection_channel=[".$connection_channel."] ***\n"; //testing

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

$container->setParameter('mailer_dsn', "null://null"); //disable for testing

//Set firewall_context_name to 'scan_auth' or scan_auth_tenantrole if tenant_role exists
if( $container->hasParameter('tenant_role') && $container->getParameter('tenant_role') ) {
    $container->setParameter('firewall_context_name', "scan_auth_".$container->getParameter('tenant_role'));
} else {
    $container->setParameter('firewall_context_name', "scan_auth");
}


if( $conn ) {
    echo "*** siteparameters.php: Connection to DB established. DB name=[".$conn->getDatabase()."] ***\n";

    $table = 'user_siteparameters';

    //$schemaManager = $conn->getSchemaManager();
    $schemaManager = $conn->createSchemaManager();
    //exit("3");

    if ($conn && $schemaManager->tablesExist(array($table)) == true) {

        //exit("connected!");
        //echo("table true<br>");

        $sql = "SELECT * FROM " . $table;
        $params = $conn->executeQuery($sql); // Simple, but has several drawbacks

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

        //$row = $params->fetch();
        $row = $params->fetchAllAssociative();
        //dump($row);
        //exit('111');
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

            //JWT keys for SAML
            $secret_key = null;
            $public_key = null;
            $pass_phrase = null;

            if( $row && is_array($row) ) {

                //print_r($row);
                //exit('111');

                $smtpServerAddress = getDBParameter($row, $smtpServerAddress, 'smtpServerAddress');
                $defaultSiteEmail = getDBParameter($row, $defaultSiteEmail, 'siteEmail');
                $institution_url = getDBParameter($row, $institution_url, 'institutionurl');
                $institution_name = getDBParameter($row, $institution_name, 'institutionname');
                $subinstitution_url = getDBParameter($row, $subinstitution_url, 'subinstitutionurl');
                $subinstitution_name = getDBParameter($row, $subinstitution_name, 'subinstitutionname');
                $department_url = getDBParameter($row, $department_url, 'departmenturl');
                $department_name = getDBParameter($row, $department_name, 'departmentname');
                $showcopyrightonfooter = getDBParameter($row, $showcopyrightonfooter, 'showCopyrightOnFooter');

                //exit('$defaultSiteEmail='.$defaultSiteEmail);exit('111');
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

                //$secret_key = null;
                //$public_key = null;
                //$pass_phrase = null;

                //$connection_channel = 'http'; //testing
                echo "*** siteparameters.php: site settings connection_channel=[".$connection_channel."] ***\n"; //testing
            }//if $row

            //echo "connection_channel=[".$connection_channel."]\n"; //testing
            $container->setParameter('connection_channel', $connection_channel);

            //echo "mailer_host=[".$smtpServerAddress."]\n"; //testing
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

            //echo "set institution_url=[$institution_url]<br>";

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

            //Set default container parameters for multitenancy
            //setRequiredMultitenancyByDB($container, $conn, $row);

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

echo "*** siteparameters.php: APP_ENV=".$_SERVER['APP_ENV'] . ", APP_DEBUG=" . $_SERVER['APP_DEBUG']." ***\n";

#printSettings($container);

