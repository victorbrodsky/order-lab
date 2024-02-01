<?php

/**
 * Created by PhpStorm.
 * User: Oleg Ivanov oli2002
 * Date: 01/24/2024
 * Time: 12:28 PM
 */

require 'base.php';

function initRequiredMultitenancy( $container )
{
    echo "<br>### initRequiredMultitenancy ### <br>";
    /////////// 'tenantprefix' ///////////
    //////// tenantprefix is used by twig.yaml, the it is used in base.html.twig to set hidden id="tenantprefix" ////////
    //////// and then is used in getCommonBaseUrl. ////////
    //////// It is not need if locale is used ////////
    ////importat to have closing '/' to form url correctly /%multitenancy_prefix%deidentifier => /c/wcm/pathology/deidentifier
    ////$tenantprefix = 'c/wcm/pathology/';
    ////$tenantprefix = 'c/lmh/pathology/';
    $tenantprefix = ''; //default prefix as it was in the original configuration
    $container->setParameter('tenantprefix', $tenantprefix);
    /////////// EOF 'tenantprefix' ///////////

    //defaultlocale is used in translation.yaml to set default translation for main home page with '/'
    //$defaultLocale = 'main';
    $defaultLocale = '';
    $container->setParameter('defaultlocale', $defaultLocale);

    //'multilocales' and 'locdel' are used in firewalls.yml and in security_access_control.yml
    $multilocales = '';
    //$multilocales = 'en';
    $container->setParameter('multilocales', $multilocales);
    $container->setParameter('locdel', '');

    //set default 'multitenancy' - used by the DatabaseConnectionFactory
    //On the home page 'http://127.0.0.1/index_dev.php/' the default value is used which connect to the default DB
    //When on the specific tenant's website (i.e. http://127.0.0.1/index_dev.php/c/wcm/pathology),
    // the DB is chosen according to the updated value 'multitenancy' which is set by ParametersCompilerPass
    $multitenancy = 'singletenancy'; //Used by CustomTenancyLoader and DatabaseConnectionFactory
    //$multitenancy = 'multitenancy'; //testing
    $container->setParameter('multitenancy', $multitenancy);

    $container->setParameter('multilocales-urls', '');

    //Always have default and system
    //$container->setParameter('locale_prefixes', "default: '',system: 'system'");
}


//1) IF "Server Role and Network Access:" = "Internet (Hub)”
//2) Then: get url prefix from HostedUserGroupList (parent/child1/child2 ...) or "Tandem Partner Server URL" (authPartnerServer) or (?)
//3) set tenantid $tenantprefix = authPartnerServer
function setRequiredMultitenancyByDB( $container, $conn, $row )
{
    echo "<br>### setRequiredMultitenancyByDB ### <br>";

    $systemdb = $container->getParameter('systemdb');
    echo "<br>### setRequiredMultitenancyByDB: systemdb=".$systemdb." ### <br>";
    if( $systemdb == false ) {
        echo "<br>### setRequiredMultitenancyByDB: system DB does not exists => single-tenancy ### <br>";
        return;
    }
    
//    //Enable system DB
//    //$table = 'user_siteParameters';
//    $table = 'user_siteparameters';
//    $schemaManager = $conn->createSchemaManager();
//    //exit("3");
//    if ($conn && $schemaManager->tablesExist(array($table)) == true) {
//        //SiteParameters entity exists => Do nothing
//        echo "<br>### SiteParameters entity exists => Do nothing ### <br>";
//    } else {
//        echo "<br>### Enable multi-tenancy with 'system' ### <br>";
//        //Enable multi-tenancy with 'system' to be able to
//        //run /order/system/admin/first-time-login-generation-init/
//        //and manage System DB
//        $multitenancy = 'multitenancy'; //Used by CustomTenancyLoader
//        $container->setParameter('multitenancy', $multitenancy);
//
//        //$container->setParameter('defaultlocale', 'main');
//        $container->setParameter('defaultlocale', 'system');
//        $container->setParameter('locdel', '/'); //locale delimeter '/'
//
//        //$multilocales = implode('|', $tenantUrlArr);
//        //echo "\n<br> setRequiredMultitenancyByDB: " . "multilocales=$multilocales <br>";
//        //$container->setParameter('multilocales', 'main|'.$multilocales);
//        //$container->setParameter('multilocales', 'system|' . $multilocales);
//        //$container->setParameter('multilocales-urls', $multilocales);
//
//        $container->setParameter('multilocales', 'system');
//        $container->setParameter('multilocales-urls', 'system');
//    }

//    //Set system db connection
//    $tenantUrl = "system";
//    $container->setParameter($tenantUrl . "-id", null);
//    $container->setParameter($tenantUrl . "-databaseDriver", $container->getParameter('database_driver_systemdb'));
//    $container->setParameter($tenantUrl . "-databaseHost", $container->getParameter('database_host_systemdb'));
//    $container->setParameter($tenantUrl . "-databasePort", $container->getParameter('database_port_systemdb'));
//    $container->setParameter($tenantUrl . "-databaseName", $container->getParameter('database_name_systemdb'));
//    $container->setParameter($tenantUrl . "-databaseUser", $container->getParameter('database_user_systemdb'));
//    $container->setParameter($tenantUrl . "-databasePassword", $container->getParameter('database_password_systemdb'));

    $authServerNetworkId = getDBParameter($row, null, 'authservernetwork_id');
    if ($authServerNetworkId) {
        //dump($authServerNetworkId);
        //dump($row);
        echo "authServerNetworkId=" . $authServerNetworkId . "\n";
        $table = 'user_authservernetworklist';
        $authServerNetworkSql = "SELECT * FROM " . $table . " WHERE id=$authServerNetworkId";
        $authServerNetworkParams = $conn->executeQuery($authServerNetworkSql); // Simple, but has several drawbacks
        $authServerNetworkRow = $authServerNetworkParams->fetchAllAssociative(); //fetch();
        //dump($authServerNetworkRow);
        //exit('111');
        //echo "authServerNetworkRow=" . count($authServerNetworkRow) . "<br>";
        if (count($authServerNetworkRow) > 0) {
            //$authServerNetworkName = $authServerNetworkRow[0]['name'];
            $authServerNetworkName = getDBParameter($authServerNetworkRow, null, 'name');
            echo "authServerNetworkName=" . $authServerNetworkName . "\n";
        }

        if ($authServerNetworkName == 'Internet (Hub)') {
//                    $multitenancy = 'multitenancy'; //USed by CustomTenancyLoader
//                    $container->setParameter('multitenancy', $multitenancy);
//
//                    $container->setParameter('defaultlocale', 'main');
//                    $container->setParameter('locdel', '/'); //locale delimeter '/'

            //TODO: get from DB. Use $authServerNetworkId to get these from AuthServerNetworkList
            //TODO: make sure ParametersCompilerPass is working
            //$multilocales = 'main|c/wcm/pathology|c/lmh/pathology';
            //$container->setParameter('multilocales', $multilocales);

            $table = 'user_hostedgroupholder';
            $hostedGroupHolderSql = "SELECT * FROM " . $table .
                " WHERE servernetwork_id=$authServerNetworkId"
                . " AND enabled=TRUE"
                . " ORDER BY orderinlist ASC" //lower on top
            ;
            $hostedGroupHolders = $conn->executeQuery($hostedGroupHolderSql);
            $hostedGroupHolderRows = $hostedGroupHolders->fetchAllAssociative(); //fetch();

            if (1) {
                $tenantUrlArr = array();
                foreach ($hostedGroupHolderRows as $hostedGroupHolderRow) {
                    //dump($hostedGroupHolderRow);
                    //$hostedUserGroupId = getDBParameter($hostedGroupHolderRow, null, 'hostedusergroup_id');
                    $hostedUserGroupId = $hostedGroupHolderRow['hostedusergroup_id'];
                    echo "\n<br>" . "hostedUserGroupId=$hostedUserGroupId";
                    //Get parent's abbreviation
//                            $hostedGroupSql = "SELECT * FROM " . 'user_hostedusergrouplist' .
//                                " WHERE id=$hostedUserGroupId ORDER BY orderinlist ASC";
//                            $hostedGroup = $conn->executeQuery($hostedGroupSql);
//                            $hostedGroupRows = $hostedGroup->fetchAllAssociative(); //fetch();

                    $tenantUrl = getNestedTreeBreadCrumb($hostedUserGroupId, $conn);
                    echo "\n<br>" . "tenantUrl=$tenantUrl";
                    $tenantUrlArr[] = $tenantUrl;

                    //Set id of this hosted user group
                    $container->setParameter($tenantUrl . "-id", $hostedGroupHolderRow['id']);
                    //$container->setParameter($tenantUrl . "-databaseDriver", $container->getParameter('database_driver_systemdb'));
                    $container->setParameter($tenantUrl . "-databaseHost", $hostedGroupHolderRow['databasehost']);
                    $container->setParameter($tenantUrl . "-databasePort", $hostedGroupHolderRow['databaseport']);
                    $container->setParameter($tenantUrl . "-databaseName", $hostedGroupHolderRow['databasename']);
                    $container->setParameter($tenantUrl . "-databaseUser", $hostedGroupHolderRow['databaseuser']);
                    $container->setParameter($tenantUrl . "-databasePassword", $hostedGroupHolderRow['databasepassword']);
                }
                if (count($tenantUrlArr) > 0) {
                    $multitenancy = 'multitenancy'; //Used by CustomTenancyLoader
                    $container->setParameter('multitenancy', $multitenancy);

                    //$container->setParameter('defaultlocale', 'main');
                    $container->setParameter('defaultlocale', 'system');
                    $container->setParameter('locdel', '/'); //locale delimeter '/'

                    $multilocales = implode('|', $tenantUrlArr);
                    echo "\n<br> setRequiredMultitenancyByDB: " . "multilocales=$multilocales <br>";
                    //$container->setParameter('multilocales', 'main|'.$multilocales);
                    $container->setParameter('multilocales', 'system|' . $multilocales);
                    $container->setParameter('multilocales-urls', $multilocales);
                }
                //exit("\n<br>".'user_hostedgroupholder');
            }

            //$container->setParameter('seclocales', $multilocales."(%localedel%)");
//                    //Load security's access_control yaml for multitatncy
//                    $configDirectory = '../config/custom';
//                    $locator = new FileLocator($configDirectory);
//                    $loader = new YamlFileLoader($container, $locator);
//                    $loader->load('security_access_control.yml');
        }
    }
}


function checkAndEnableSystemDB( $container, $conn )
{
    echo "<br>### checkAndEnableSystemDB ### <br>";

    $systemdb = $container->getParameter('systemdb');
    echo "<br>### setRequiredMultitenancyByDB: systemdb=" . $systemdb . " ### <br>";
    if ($systemdb == false) {
        echo "<br>### setRequiredMultitenancyByDB: system DB does not exists => single-tenancy ### <br>";
        return;
    }

    //Enable system DB
    //$table = 'user_siteParameters';
    $table = 'user_siteparameters';
    $schemaManager = $conn->createSchemaManager();
    //exit("3");
    if ($conn && $schemaManager->tablesExist(array($table)) == true) {
        //SiteParameters entity exists => Do nothing
        echo "<br>### SiteParameters entity exists => Check 'initialConfigurationCompleted' ### <br>";

        $enableSystem = false;

        $siteparameters = "SELECT * FROM " . $table;
        $hostedGroupHolders = $conn->executeQuery($siteparameters);
        $siteparametersRows = $hostedGroupHolders->fetchAllAssociative(); //fetch();
        //dump($siteparametersRows);
        //exit('111');
        if( count($siteparametersRows) > 0 ) {
            $siteparametersRow = $siteparametersRows[0];

            if( isset($siteparametersRow['initialconfigurationcompleted']) ) {
                $initialConfigurationCompleted = $siteparametersRow['initialconfigurationcompleted'];
                echo "<br>### initialConfigurationCompleted=$initialConfigurationCompleted ### <br>";
                if( !$initialConfigurationCompleted ) {
                    $enableSystem = true;
                }
            } else {
                echo "<br>### ".$table." does not have initialConfigurationCompleted  ### <br>";
                $enableSystem = true;
            }
        }
        echo "<br>### enableSystem=[$enableSystem]  ### <br>";
        if( $enableSystem ) {
            echo "<br>### Enable multi-tenancy with 'system' ### <br>";
            //Enable multi-tenancy with 'system' to be able to
            //run /order/system/admin/first-time-login-generation-init/
            //and manage System DB
            $multitenancy = 'multitenancy'; //Used by CustomTenancyLoader
            $container->setParameter('multitenancy', $multitenancy);

            //$container->setParameter('defaultlocale', 'main');
            $container->setParameter('defaultlocale', 'system');
            $container->setParameter('locdel', '/'); //locale delimeter '/'

            //$multilocales = implode('|', $tenantUrlArr);
            //echo "\n<br> setRequiredMultitenancyByDB: " . "multilocales=$multilocales <br>";
            //$container->setParameter('multilocales', 'main|'.$multilocales);
            //$container->setParameter('multilocales', 'system|' . $multilocales);
            //$container->setParameter('multilocales-urls', $multilocales);

            $container->setParameter('multilocales', 'system');
            $container->setParameter('multilocales-urls', 'system');
        }

        if( 1 ) {
            echo "<br>### TEST Enable multi-tenancy with 'system' ### <br>";
            //Enable multi-tenancy with 'system' to be able to
            //access /system/ and manage System DB
            $multitenancy = 'multitenancy'; //Used by CustomTenancyLoader
            $container->setParameter('multitenancy', $multitenancy);

            $container->setParameter('defaultlocale', 'default');
            $container->setParameter('locdel', '/'); //locale delimeter '/'

            $container->setParameter('multilocales', 'system|default');
            $container->setParameter('multilocales-urls', 'system|default');

            //$container->setParameter('locale_prefixes', "default: '',system: 'system'");

            //Set id of this hosted user group
            $tenantUrl = 'default';
            $container->setParameter($tenantUrl . "-id", null);
            //$container->setParameter($tenantUrl . "-databaseDriver", $container->getParameter('database_driver'));
            $container->setParameter($tenantUrl . "-databaseHost", $container->getParameter('database_host'));
            $container->setParameter($tenantUrl . "-databasePort", $container->getParameter('database_port'));
            $container->setParameter($tenantUrl . "-databaseName", $container->getParameter('database_name'));
            $container->setParameter($tenantUrl . "-databaseUser", $container->getParameter('database_user'));
            $container->setParameter($tenantUrl . "-databasePassword", $container->getParameter('database_password'));
        }

    } else {
        echo "<br>### SiteParameters entity exists => Do nothing ### <br>";
    }
}









