<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 3/29/2024
 * Time: 12:05 PM
 */

namespace App\UserdirectoryBundle\Util;


use App\UserdirectoryBundle\Entity\TenantList;
use App\UserdirectoryBundle\Entity\TenantManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


class UserTenantUtil
{

    protected $em;
    protected $doctrine;
    protected $security;
    protected $container;
    protected $m3;

    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        ContainerInterface $container,
        ManagerRegistry $doctrine
    ) {
        $this->em = $em;
        $this->doctrine = $doctrine;
        $this->security = $security;
        $this->container = $container;
    }

    public function getTenantRole() {
        if( !$this->container->hasParameter('tenant_role') ) {
            return null;
        }
        return $this->container->getParameter('tenant_role');
    }


    public function getSingleTenantManager( $createIfEmpty=false ) {
        $logger = $this->container->get('logger');

        $tenantManager = null;
        $tenantManagers = $this->em->getRepository(TenantManager::class)->findAll();

        if( count($tenantManagers) == 1 ) {
            return $tenantManagers[0];
        }

        //make sure sitesettings is initialized
        if( count($tenantManagers) == 0 ) {
            $logger->notice("getSingleTenantManager: TenantManager count=".count($tenantManagers)."; createIfEmpty=".$createIfEmpty);
            if( $createIfEmpty ) {
                $tenantManager = $this->generateTenantManager();
                return $tenantManager;
            }
        }

        if( count($tenantManagers) != 1 ) {
            if( $createIfEmpty ) {
                throw new \Exception(
                    'getSingleTenantManager: Must have only one tenant manager object. Found '.
                    count($tenantManagers).' object(s)'."; createIfEmpty=".$createIfEmpty
                );
            } else {
                return null;
            }
        }

        return null;
    }
    public function generateTenantManager()
    {

        $logger = $this->container->get('logger');
        //$userSecUtil = $this->container->get('user_security_utility');
        $em = $this->em;

        $tenantManagers = $em->getRepository(TenantManager::class)->findAll();

        if (count($tenantManagers) > 0) {
            $logger->notice("Exit generateTenantManager: TenantManager has been already generated.");
            return $tenantManagers[0];
        }

        $user = $this->security->getUser();
        $tenantManager = new TenantManager($user);

        $tenantManager->setGreeting("Welcome to the View! The following organizations are hosted on this platform:");
        $tenantManager->setMaintext("Please log in to manage the tenants on this platform.");
        //$tenantManager->setFooter();

        $em->persist($tenantManager);
        $em->flush();

        $logger->notice("Finished generateTenantManager");

        return $tenantManager;
    }


    //get available tenants based on haproxy config (/etc/haproxy/haproxy.cfg) and httpd (/etc/httpd/conf/tenantname-httpd.conf)
    //tenant's httpd: homepagemanager-httpd.conf, tenantmanager-httpd.conf, tenantappdemo-httpd.conf, tenantapptest-httpd.conf,
    // tenantapp1-httpd.conf, tenantapp2-httpd.conf
    public function getTenants() {
        $tenantDataArr = array();
        $tenantDataArr['error'] = null;
        $tenantDataArr['existedTenantIds'] = null;

        //$tenants = array('homepagemanager', 'tenantmanager', 'tenantappdemo', 'tenantapptest');
        //testing
        if(0) {
            //$tenantDataArr['existedTenantIds'][] = 'tenantmanager';
            //$tenantDataArr['existedTenantIds'][] = 'homepagemanager';
            //$tenantDataArr['existedTenantIds'][] = 'tenantapp2';
            $tenantDataArr['existedTenantIds'][] = 'tenantappdemo';
        }

        ////// 1) Check if tenant's htppd exists and get tenant list as array //////
        $tenantDataArr = $this->getTenantDataFromHttpd($tenantDataArr);

        ////// 2) read haproxy (check if tenant is enabled) //////
        $tenantDataArr = $this->getTenantDataFromHaproxy($tenantDataArr);

        ////// 3) read corresponding parameters.yml //////
        $tenantDataArr = $this->getTenantDataFromParameters($tenantDataArr);

        //dump($tenantDataArr);
        //exit('111');

        return $tenantDataArr;
    }
    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
    function getTextByStartEnd($text, $startStr, $endStr) {
        //echo "getTextByStartEnd: startStr=[$startStr]; endStr=[$endStr] <br>";
        //$startStr = '###START-FRONTEND';
        //$endStr = '###END-FRONTEND';
        //Get part of the text $matches by $startStr and $endStr
        $pattern = '/('.$startStr.')(?:.|[\n\r])+(?='.$endStr.')/';
        preg_match($pattern, $text, $matches);
        if( !isset($matches[0]) ) {
            //echo "getTextByStartEnd: text does not have $startStr and $endStr";
            //$errorMsg = "File does not have $startStr and $endStr";
            return array();
        }

        $frontendTenantsArray = explode("\n", trim($matches[0]));
        return $frontendTenantsArray;
    }

    ////// 1) Check if tenant's htppd exists and get tenant list as array //////
    public function getTenantDataFromHttpd( $tenantDataArr ) {
        //tenant's httpd: homepagemanager-httpd.conf, tenantmanager-httpd.conf, tenantappdemo-httpd.conf, tenantapptest-httpd.conf,
        // tenantapp1-httpd.conf, tenantapp2-httpd.conf in /etc/httpd/conf/tenantname-httpd.conf
        $httpdPath = '/etc/httpd/conf/';

        if( file_exists($httpdPath) ) {
            //echo "The httpd directory $httpdPath exists";
            //$files = scandir($path);
            $tenantDataArr['existedTenantIds'] = null;
            $httpdFiles = array_diff(scandir($httpdPath), array('.', '..')); //remove . and .. from the returned array from scandir
            //dump($files);
            //exit('111');
            foreach($httpdFiles as $httpdFile) {
                if( str_contains($httpdFile, '-httpd.conf') ) {
                    //echo "file=[".$httpdFile."]<br>"; //tenantapp2-httpd.conf
                    //use tenantapp2 to get match between fronend tenantapp2_url and tenantapp2-httpd.conf
                    $tenantId = null;
                    $tenantIdArr = explode('-', $httpdFile);
                    if( count($tenantIdArr) == 2 ) {
                        $tenantId = $tenantIdArr[0];
                    }
                    $tenantDataArr['existedTenantIds'][] = $tenantId;
                    //$tenantDataArr['tenants']['tenantId'] = $tenantId;
                }
            }
        } else {
            //echo "The httpd directory $httpdPath does not exist";
            $tenantDataArr['error'][] = "The httpd configuration directory $httpdPath does not exist";
            return $tenantDataArr;
        }

        //dump($tenantDataArr);
        //exit('111');
        return $tenantDataArr;
    }

    ////// 2) read haproxy (check if tenant is enabled) //////
    public function getTenantDataFromHaproxy( $tenantDataArr ) {

        if( $tenantDataArr['existedTenantIds'] && isset($tenantDataArr['existedTenantIds']) ) {
            //ok
        } else {
            $tenantDataArr['error'][] = "getTenantDataFromHaproxy: Tenants are not found";
            return $tenantDataArr;
        }

        //$logger = $this->container->get('logger');
        //$logger->notice( "getTenantDataFromHaproxy: tenantDataArr=".$tenantDataArr['existedTenantIds'][0] );

        //$userServiceUtil = $this->container->get('user_service_utility');
//        $haproxyConfig = '/etc/haproxy/haproxy_testing.cfg';
//
//        if( $userServiceUtil->isWindows() ) {
//            //testing with packer's default haproxy config
//            $projectRoot = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
//            $haproxyConfig = $projectRoot.'/../packer/haproxy.cfg';
//        }
//
//        if( file_exists($haproxyConfig) ) {
//            //echo "The file $haproxyConfig exists";
//        } else {
//            //echo "The file $haproxyConfig does not exist";
//            $tenantDataArr['error'][] = "HAproxy configuration file $haproxyConfig does not exist";
//            return $tenantDataArr;
//        }

        $haproxyConfig = $this->getHaproxyConfig();
        //echo "haproxyConfig file=".$haproxyConfig."<br>";

        //get all tenants between: ###START-CUSTOM-TENANTS and ###END-CUSTOM-TENANTS
        $originalText = file_get_contents($haproxyConfig);

        $frontendTenantsArray = $this->getTextByStartEnd($originalText,'###START-FRONTEND','###END-FRONTEND');
        //dump($finalArray);
        //exit('111');
        //Result:
//        0 => "###START-CUSTOM-TENANTS "
//        1 => "\tacl tenantapp3_url path_beg -i /c/wcm/333"
//        2 => "    use_backend tenantapp3_backend if tenant_app3_url"
//        3 => "\t"
//        4 => "\tacl tenantapp4url path_beg -i /c/wcm/444"
//        5 => "    use_backend tenantapp4backend if tenant_app4_url"

        //Get url '/c/wcm/333', enabled
        foreach($tenantDataArr['existedTenantIds'] as $tenantId) {
            $tenantDataArr[$tenantId]['enabled'] = true;
            foreach($frontendTenantsArray as $frontendTenantLine) {
                if( str_contains($frontendTenantLine, ' '.$tenantId.'_url') ) {
                    //echo "frontendTenantLine=$frontendTenantLine <br>";
                    $tenantUrlArr = explode('path_beg -i', $frontendTenantLine);
                    if( count($tenantUrlArr) > 1 ) {
                        $tenantUrl = end($tenantUrlArr); //=>' /c/wcm/333'
                        if ($tenantUrl) {
                            $tenantUrl = trim($tenantUrl);
                            //echo "tenantUrl=[".$tenantUrl."]<br>";
                            $tenantDataArr[$tenantId]['url'] = $tenantUrl;
                        }
                    }

                    if( str_contains($frontendTenantLine, '#') ) {
                        $tenantDataArr[$tenantId]['enabled'] = false;
//                        foreach ($tenantDataArr['existedTenantIds'] as $existedTenantId) {
//                            if ($existedTenantId == $tenantId) {
//                                $tenantDataArr[$tenantId]['enabled'] = true;
//                            }
//                        }
                    }
                }

                //if: use_backend tenantapp1_backend if homepagemanager_url
                //then: primaryTenant
                $tenantDataArr[$tenantId]['primaryTenant'] = false;
                //check for: 'use_backend tenantapp1_backend if homepagemanager_url'
                //$logger->notice("getTenantDataFromHaproxy: frontendTenantLine=$frontendTenantLine");
                if( str_contains($frontendTenantLine, 'use_backend '.$tenantId.'_backend if homepagemanager_url') ) {
                    $tenantDataArr[$tenantId]['primaryTenant'] = true;
                    //$logger->notice("getTenantDataFromHaproxy: primaryTenant=[$tenantId], frontendTenantLine=$frontendTenantLine"." => primaryTenant=".$tenantDataArr[$tenantId]['primaryTenant']);
                }

            } //foreach $frontendTenantsArray
        } //foreach $tenantDataArr['existedTenantIds']

        //Get port front backend between ###START-BACKEND and ###END-BACKEND
        //backend homepagemanager_backend
        //server homepagemanager_server *:8081 check
        $backendTenantsArray = $this->getTextByStartEnd($originalText,'###START-BACKEND','###END-BACKEND');
        foreach($tenantDataArr['existedTenantIds'] as $tenantId) {
            foreach($backendTenantsArray as $backendTenantLine) {
                if( str_contains($backendTenantLine, ' '.$tenantId.'_server') ) {
                    //echo "backendTenantLine=$backendTenantLine <br>"; // server tenantmanager_server *:8082 check
                    $tenantPort = $this->get_string_between($backendTenantLine,$tenantId.'_server'," check"); //=>*:8081
                    $tenantPort = trim($tenantPort);
                    //echo "1 tenantPort=[$tenantPort] <br>";
                    $tenantPort = str_replace('*:','',$tenantPort); //=>8081
                    //echo "2 tenantPort=[$tenantPort] <br>";
                    $tenantDataArr[$tenantId]['port'] = $tenantPort;
                }
            } //foreach $backendTenantsArray
            //$logger->notice("getTenantDataFromHaproxy: tenant=[$tenantId], before exit, primaryTenant=[".$tenantDataArr[$tenantId]['primaryTenant']."]");
        } //foreach $tenantDataArr['existedTenantIds']

        return $tenantDataArr;
    }
    public function getHaproxyConfig() {
        $userServiceUtil = $this->container->get('user_service_utility');
        $haproxyConfig = '/etc/haproxy/haproxy.cfg';

        if( $userServiceUtil->isWindows() ) {
            //testing with packer's default haproxy config
            $projectRoot = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
            $haproxyConfig = $projectRoot.'/../packer/haproxy_testing.cfg';
        }

        if( file_exists($haproxyConfig) ) {
            //echo "The file $haproxyConfig exists";
        } else {
            //echo "The file $haproxyConfig does not exist";
            //$tenantDataArr['error'][] = "HAproxy configuration file $haproxyConfig does not exist";
            //return $tenantDataArr;
            return null;
        }
        return $haproxyConfig;
    }

    ////// 3) read corresponding parameters.yml //////
    public function getTenantDataFromParameters( $tenantDataArr ) {

        if( $tenantDataArr['existedTenantIds'] && isset($tenantDataArr['existedTenantIds']) ) {
            //ok
        } else {
            $tenantDataArr['error'][] = "getTenantDataFromParameters: Tenants are not found";
            return $tenantDataArr;
        }

        $projectRoot = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
        $orderHolderFolder = $projectRoot.'/../../';
        $orderFolders = array_diff(scandir($orderHolderFolder), array('.', '..')); //remove . and .. from the returned array from scandir
        foreach($orderFolders as $orderFolder) {
            //echo "orderFolder=$orderFolder <br>";
            if( str_contains($orderFolder, 'order-lab-') ) {
                foreach($tenantDataArr['existedTenantIds'] as $tenantId) {
                    $orderPath = $orderHolderFolder.DIRECTORY_SEPARATOR.'order-lab-'.$tenantId.DIRECTORY_SEPARATOR; //order-lab-tenantapp2
                    $orderParameterPath = $orderPath . DIRECTORY_SEPARATOR.'orderflex'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'parameters.yml';
                    if( file_exists($orderParameterPath) ) {
                        //echo "orderParameterPath=$orderParameterPath <br>";
                        $originalText = file_get_contents($orderParameterPath);
                        //echo "originalText=$originalText <br>";

                        $tenantDataArr[$tenantId]['databaseHost'] = 'Unknown';
                        $tenantDataArr[$tenantId]['databaseName'] = 'Unknown';
                        $tenantDataArr[$tenantId]['databaseUser'] = 'Unknown';
                        $tenantDataArr[$tenantId]['databasePassword'] = 'Unknown';

                        $parametersLines = $this->getTextByStartEnd($originalText,'parameters:','');
                        foreach($parametersLines as $parametersLine) {
                            //echo "parametersLine=$parametersLine <br>";
                            if( str_contains($parametersLine, 'database_host:') && !str_contains($parametersLine, '#') ) {
                                $dbHost = str_replace('database_host:','',$parametersLine);
                                //echo "dbHost=$dbHost <br>";
                                $dbHost = trim($dbHost);
                                $tenantDataArr[$tenantId]['databaseHost'] = $dbHost;
                                //exit('111');
                            }
                            if( str_contains($parametersLine, 'database_name:') && !str_contains($parametersLine, '#') ) {
                                //echo "database_name=$parametersLine <br>";
                                $dbName = str_replace('database_name:','',$parametersLine);
                                $dbName = trim($dbName);
                                $tenantDataArr[$tenantId]['databaseName'] = $dbName;
                            }
                            if( str_contains($parametersLine, 'database_user:') && !str_contains($parametersLine, '#') ) {
                                $dbUser = str_replace('database_user:','',$parametersLine);
                                $dbUser = trim($dbUser);
                                $tenantDataArr[$tenantId]['databaseUser'] = $dbUser;
                            }
                            if( str_contains($parametersLine, 'database_password:') && !str_contains($parametersLine, '#') ) {
                                $dbPass = str_replace('database_password:','',$parametersLine);
                                $dbPass = trim($dbPass);
                                $tenantDataArr[$tenantId]['databasePassword'] = $dbPass;
                            }
                        }
                        //dump($dbHost);
                        //exit('111');

                    } else {

                    }
                }
            }
        }
        //dump($tenantDataArr);
        //exit('111');
        return $tenantDataArr;
    }

    //Update server data according to DB data (DB -> server)
    //Best option is to have shell script to create and modify config files and to run restart
    public function processDBTenants( $tenantManager ) {

        $logger = $this->container->get('logger');
        $userUtil = $this->container->get('user_utility');
        $session = $userUtil->getSession(); //$this->container->get('session');

        $updateHaproxy = false;
        $updateHttpd = false;
        $resultArr = array();
        $resultArr['haproxy-error'] = null;
        $resultArr['haproxy-ok'] = null;
        $resultArr['httpd-error'] = null;
        $resultTenantArr = array();
        $resultTenantArr['haproxy-message'] = null;
        $resultTenantArr['httpd-message'] = null;
        $createNewTenants = array();

        //testing
        if(0) {
            $logger = $this->container->get('logger');
            $logger->notice("start restartHaproxy " . date('h:i:s'));
            $output = $this->restartHaproxy();
            $logger->notice("end restartHaproxy " . date('h:i:s') . ", output=" . $output);
            //$this->restartTenantHttpd();
            return $output;
        }


        foreach( $tenantManager->getTenants() as $tenant ) {
            //echo "tenant=".$tenant."; url=".$tenant->getUrlSlug()."<br>";

            $tenantId = $tenant->getName();

            //testing
//            if(
//                $tenantId != 'newtenant'
//                //$tenantId != 'tenantapptest'
//            ) { //newtenant tenantapptest
//                continue;
//            }
            
            $haproxyConfig = $this->getHaproxyConfig();

            //Enable/Disable => haproxy
            $tenantDataArr = array();
            $tenantDataArr['existedTenantIds'][] = $tenantId;
            $tenantDataArr = $this->getTenantDataFromHaproxy($tenantDataArr);

            //TODO: update HaProxy config with primaryTenant (assign route '/' to this tenant)
            $httpdConfig = $this->getTenantHttpd($tenantId);
            echo "httpdConfig=[$httpdConfig]<br>";

            //dump($tenantDataArr);
            //exit('111');

            //$tenantDataArr: for existing tenant, $tenantDataArr should have url and port (set and not null)
            //$httpdConfig: for existing tenant should be not null
            if( $httpdConfig && isset($tenantDataArr[$tenantId]['url']) && isset($tenantDataArr[$tenantId]['port']) ) {
                //tenant exists
                $logger->notice("processDBTenants: tenant $tenantId exists");
            } else {
                //create new tenant
                $logger->notice("processDBTenants: create new tenant $tenantId");
                $msgCreateNewTenant = $this->createNewTenant($tenant);
                $resultTenantArr[$tenantId]['message']['success'][] = $msgCreateNewTenant;
                //$createNewTenants[] = $tenantId;
                continue;
            }

            echo "enable: ".$tenant->getEnabled()."?=".$tenantDataArr[$tenantId]['enabled']."<br>";
            $logger->notice("compare url: "."enable: [".$tenant->getEnabled()."]?=[".$tenantDataArr[$tenantId]['enabled']."]");
            if( $tenant->getEnabled() != $tenantDataArr[$tenantId]['enabled'] ) {
                echo "Change enable <br>";
                $originalText = file_get_contents($haproxyConfig);
                //Disable or enabled according to DB value $tenant->getEnabled():
                //comment out line frontend->'use_backend tenantappdemo_backend if tenantappdemo_url'
                $frontendTenantsArray = $this->getTextByStartEnd($originalText,'###START-FRONTEND','###END-FRONTEND');
                foreach($frontendTenantsArray as $frontendTenantLine) {
                    $lineIdentifier = 'use_backend ' . $tenantId . '_backend';
                    $logger->notice("str_contains: lineIdentifier=[$lineIdentifier]");
                    if (str_contains($frontendTenantLine,$lineIdentifier)) {
                        $logger->notice("YES str_contains: lineIdentifier=[$lineIdentifier]");
                        $res = $this->changeLineInFile($haproxyConfig,$lineIdentifier,'#',$tenant->getEnabled());
                        $logger->notice("changeLineInFile: status=[".$res['status']."]; message=".$res['message']);
                        if( $res['status'] == 'error' ) {
                            $resultArr['haproxy-error'] = $res['message'];
                            $resultTenantArr['haproxy-message']['error'][] = $res['message'];
                        } else {
                            $enabledStr = "disabled";
                            if( $tenant->getEnabled() ) {
                                $enabledStr = "enabled";
                            }
                            $msg = "Tenant $tenantId has been $enabledStr in haproxy config";
//                            $session->getFlashBag()->add(
//                                'note',
//                                "Tenant $tenantId has been $enabledStr in haproxy config"
//                            );
                            $logger->notice(
                                $msg
                                //"Update haproxy config for tenant ".$tenantId.", updated to ".$enabledStr
                            );
                            $resultArr['haproxy-ok'] = $resultArr['haproxy-ok'] . "; " . $msg;
                            $resultTenantArr['haproxy-message']['success'][] = $msg;
                            $updateHaproxy = true;
                        }
                        break;
                    }
                }
            }

            //Overwrite homepage '/' by one of the tenant
            $tempMsg = "PrimaryTenant Processing: [".$tenantId."]: primaryTenant?: db[".$tenant->getPrimaryTenant()."]?=server[".$tenantDataArr[$tenantId]['primaryTenant']."]";
            echo $tempMsg."<br>";
            $logger->notice(
                $tempMsg
            );
            if( $tenant != 'homepagemanager' && $tenant->getPrimaryTenant() != $tenantDataArr[$tenantId]['primaryTenant'] ) {
                echo "Change primaryTenant '/' in HaProxy <br>";
                $originalText = file_get_contents($haproxyConfig);

                //Replace: use_backend homepagemanager_backend if homepagemanager_url
                //With: use_backend tenantapp1_backend if homepagemanager_url

                //Replace: use_backend homepagemanager_backend if homepagemanager_url
                //With: #use_backend homepagemanager_backend if homepagemanager_url
                //Add below: use_backend $tenantId_backend if homepagemanager_url

                $frontendTenantsArray = $this->getTextByStartEnd($originalText,'###START-FRONTEND','###END-FRONTEND');
                foreach($frontendTenantsArray as $frontendTenantLine) {

                    if( !$tenant->getPrimaryTenant() ) {
                        // Not PrimaryTenant
                        $homepagemanagerLine = "use_backend homepagemanager_backend if homepagemanager_url";
                        $lineIdentifier = 'use_backend ' . $tenantId . '_backend if homepagemanager_url';
                        //$logger->notice("str_contains: lineIdentifier=[$lineIdentifier]");
                        //&& !str_contains($frontendTenantLine,'#')
                        if( str_contains($frontendTenantLine,$lineIdentifier) ) {
                            //remove tenant: 'use_backend $tenantId_backend if homepagemanager_url'
                            //$originalLine = "use_backend homepagemanager_backend if homepagemanager_url";
                            $originalLine = ''; //'#'.$originalLine;
                            $logger->notice("Tenant [$tenant] is not primary => remove [".$lineIdentifier."]");
                            $logger->notice("YES str_contains: lineIdentifier=[$lineIdentifier]");

                            $homepagemanagerOldStr = '#use_backend homepagemanager_backend if homepagemanager_url';
                            $homepagemanagerNewStr = 'use_backend homepagemanager_backend if homepagemanager_url';
                            $res = $this->replaceAllInFile($haproxyConfig, $homepagemanagerOldStr, $homepagemanagerNewStr);
                            if ($res['status'] == 'error') {
                                $resultArr['haproxy-error'] = $res['message'];
                                $resultTenantArr['haproxy-message']['error'][] = $res['message'];
                            } else {
                                $msg = "PrimaryTenant $tenantId has been updated in haproxy config from"
                                    ."[".$homepagemanagerOldStr."]"
                                    ." to [".$homepagemanagerNewStr."]";
                                $resultTenantArr['haproxy-message']['success'][] = $msg;
                            }

                            $res = $this->replaceAllInFile($haproxyConfig, $lineIdentifier, $originalLine);
                            //$res = $this->changeLineInFile($haproxyConfig,$lineIdentifier,'#',true);
                            $logger->notice("replaceAllInFile: status=[".$res['status']."]; message=".$res['message']);
                            if ($res['status'] == 'error') {
                                $resultArr['haproxy-error'] = $res['message'];
                                $resultTenantArr['haproxy-message']['error'][] = $res['message'];
                                $logger->notice(
                                    "PrimaryTenant Error: ".$res['message']
                                );
                            } else {
                                $msg = "PrimaryTenant $tenantId has been updated in haproxy config from"
                                    ."[".$lineIdentifier."]"
                                    ." to [".$originalLine."]";
                                $logger->notice(
                                    $msg
                                );
                                $resultArr['haproxy-ok'] = $resultArr['haproxy-ok'] . "; " . $msg;
                                $resultTenantArr['haproxy-message']['success'][] = $msg;
                                $updateHaproxy = true;
                            }
                            break;
                        }
                    } else {
                        // PrimaryTenant
                        $lineIdentifier = "use_backend homepagemanager_backend if homepagemanager_url";
                        //$logger->notice("str_contains: lineIdentifier=[$lineIdentifier]");
                        if( str_contains($frontendTenantLine,$lineIdentifier) ) {
                            //replace tenant's homepage with original homepage
                            $newLine =
                                '#'.$lineIdentifier.
                                PHP_EOL.
                                'use_backend ' . $tenantId . '_backend if homepagemanager_url';
                            $logger->notice("Tenant [$tenant] is primary => remove homepagemanager [".$lineIdentifier."]");
                            $logger->notice("YES str_contains: lineIdentifier=[$lineIdentifier]");
                            $res = $this->replaceAllInFile($haproxyConfig, $lineIdentifier, $newLine);
                            $logger->notice("replaceAllInFile: status=[".$res['status']."]; message=".$res['message']);
                            if ($res['status'] == 'error') {
                                $resultArr['haproxy-error'] = $res['message'];
                                $resultTenantArr['haproxy-message']['error'][] = $res['message'];
                                $logger->notice(
                                    "PrimaryTenant Error: ".$res['message']
                                );
                            } else {
                                $msg = "PrimaryTenant $tenantId has been updated in haproxy config from"
                                    ."[".$lineIdentifier."]"
                                    ." to [".$newLine."]";
                                $logger->notice(
                                    $msg
                                );
                                $resultArr['haproxy-ok'] = $resultArr['haproxy-ok'] . "; " . $msg;
                                $resultTenantArr['haproxy-message']['success'][] = $msg;
                                $updateHaproxy = true;
                            }
                            break;
                        }
                    }
                } //foreach
            } //if $tenant->getPrimaryTenant()

            //update URL slug or tenant's port: modify files: haproxy and $tenantId-httpd.conf
            $tenantDbUrl = $tenant->getUrlSlug();
            $tenantDbUrlTrim = trim(trim($tenantDbUrl,'/'));
            $tenantServerUrlTrim = trim(trim($tenantDataArr[$tenantId]['url'],'/'));

            $tenantDbPort = $tenant->getTenantPort();
            $tenantDbPortTrim = trim($tenantDbPort);
            $tenantServerPortTrim = trim($tenantDataArr[$tenantId]['port']);

            $tenantDbPrimaryTenant = $tenant->getPrimaryTenant();
            $tenantDbPrimaryTenantTrim = trim($tenantDbPrimaryTenant);
            $tenantServerPrimaryTenantTrim = trim($tenantDataArr[$tenantId]['primaryTenant']);

            $logger->notice("compare url: ".$tenantDbUrlTrim."?=".$tenantServerUrlTrim);
            $logger->notice("compare port: ".$tenantDbPortTrim."?=".$tenantServerPortTrim);
            $logger->notice("compare primaryTenant: ".$tenantDbPrimaryTenantTrim."?=".$tenantServerPrimaryTenantTrim);

            if( $tenantDbUrlTrim != $tenantServerUrlTrim || $tenantDbPortTrim != $tenantServerPortTrim ) {

                $originalText = file_get_contents($haproxyConfig);

                //URL: modify URL in haproxy: 'acl tenantappdemo_url path_beg -i /c/demo-institution/demo-department'
                if( $tenantDbUrlTrim != $tenantServerUrlTrim ) {
                    $frontendTenantsArray = $this->getTextByStartEnd($originalText, '###START-FRONTEND', '###END-FRONTEND');
                    foreach($frontendTenantsArray as $frontendTenantLine) {
                        if (str_contains($frontendTenantLine, ' ' . $tenantId . '_url')) {
                            $res = $this->replaceAllInFile($haproxyConfig,$tenantServerUrlTrim,$tenantDbUrlTrim);
                            //$resultArr[$tenantId]['haproxy-url'] = $res;
                            if ($res['status'] == 'error') {
                                $resultArr['haproxy-error'] = $res['message'];
                                $resultTenantArr['haproxy-message']['error'][] = $res['message'];
                            } else {
                                $msg = "URL for tenant $tenantId has been updated in haproxy config from"
                                    ."[".$tenantServerUrlTrim."]"
                                    ." to [".$tenantDbUrlTrim."]";
//                                $session->getFlashBag()->add(
//                                    'note',
//                                    $msg
//                                );
                                $logger->notice(
                                    $msg
                                );
                                $resultArr['haproxy-ok'] = $resultArr['haproxy-ok'] . "; " . $msg;
                                $resultTenantArr['haproxy-message']['success'][] = $msg;
                                $updateHaproxy = true;
                            }
                            break;
                        }
                    }//foreach tenant's haproxy
                }


                //PORT: modify port in haproxy: 'server tenantmanager_server *:8082 check'
                if( $tenantDbPortTrim != $tenantServerPortTrim ) {
                    $backendTenantsArray = $this->getTextByStartEnd($originalText, '###START-BACKEND', '###END-BACKEND');
                    foreach($backendTenantsArray as $backendTenantLine) {
                        //modify 'server tenantmanager_server *:8082 check'
                        if (str_contains($backendTenantLine, 'server ' . $tenantId . '_server')) {
                            $res = $this->replaceAllInFile($haproxyConfig, $tenantServerPortTrim, $tenantDbPortTrim);
                            if ($res['status'] == 'error') {
                                $resultArr['haproxy-error'] = $res['message'];
                                $resultTenantArr['error-haproxy-message']['error'][] = $res['message'];
                            } else {
                                $msg = "Port for tenant $tenantId has been updated in haproxy config from"
                                    ."[".$tenantServerPortTrim."]"
                                    ." to [".$tenantDbPortTrim."]";
//                                $session->getFlashBag()->add(
//                                    'note',
//                                    $msg
//                                );
                                $logger->notice(
                                    $msg
                                );
                                $resultArr['haproxy-ok'] = $resultArr['haproxy-ok'] . "; " . $msg;
                                $resultTenantArr['haproxy-message']['success'][] = $msg;
                                $updateHaproxy = true;
                            }
                            break;
                        }
                    }//foreach tenant's haproxy
                }


                //URL: change URL in httpd config file
                //$httpdConfig = $this->getTenantHttpd($tenantId);
                //echo "httpdConfig=[$httpdConfig]<br>";
                if( $httpdConfig ) {
                    $httpdOriginalText = file_get_contents($httpdConfig);
                    //dump($httpdOriginalText);
                    $updateThisHttpd = false;

                    //$tenantServerUrlTrim $tenantUrl = trim($tenantDataArr[$tenantId]['url'],'/');

                    //modify URL in httpd
                    if( $tenantDbUrlTrim != $tenantServerUrlTrim ) {
                        if (str_contains($httpdOriginalText, $tenantServerUrlTrim)) {
                            $res = $this->replaceAllInFile($httpdConfig, $tenantServerUrlTrim, $tenantDbUrlTrim);
                            if ($res['status'] == 'error') {
                                echo "processDBTenants: $tenantId: error=>message=" . $res['message'] . "<br>";
                                $resultArr['httpd-error'][$tenantId] = $res['message'];
                                $resultTenantArr[$tenantId]['message']['error'][] = $res['message'];
                            } else {
                                $msg = "Tenant's $tenantId url has been updated in httpd from "
                                    . $tenantServerUrlTrim . " to " . $tenantDbUrlTrim;
                                echo "msg=" . $msg . "<br>";
//                                $session->getFlashBag()->add(
//                                    'note',
//                                    $msg
//                                );
                                $resultTenantArr[$tenantId]['message']['success'][] = $msg;
                                $updateHttpd = true;
                            }
                            $logger->notice(
                                "Update httpd config for tenant " . $tenantId . ", update URL from "
                                . "[" . $tenantServerUrlTrim . "]"
                                . " to [" . $tenantDbUrlTrim . "]"
                            );
                            $updateThisHttpd = true;
                        } else {
                            echo "processDBTenants: httpdConfig for $tenantId: config does not have url=" .
                                $tenantServerUrlTrim . "; tenantDbUrl=$tenantDbUrlTrim" . "<br>";
                        }
                    }

                    //modify port in httpd
                    if( $tenantDbPortTrim != $tenantServerPortTrim ) {
                        if (str_contains($httpdOriginalText, $tenantServerPortTrim)) {
                            $res = $this->replaceAllInFile($httpdConfig, $tenantServerPortTrim, $tenantDbPortTrim);
                            if ($res['status'] == 'error') {
                                echo "processDBTenants: $tenantId: status=" . $res['status'] . "; message=" . $res['message'] . "<br>";
                                $resultArr['httpd-error'][$tenantId] = $res['message'];
                                $resultTenantArr[$tenantId]['message']['error'][] = $res['message'];
                                $msg = $res['message'];
                            } else {
                                echo "processDBTenants: $tenantId: status=" . $res['status'] . "<br>";
                                $msg = "Port for tenant $tenantId has been updated in httpd from "
                                . "[" . $tenantServerPortTrim . "] to [" . $tenantDbPortTrim . "]";
//                                $session->getFlashBag()->add(
//                                    'note',
//                                    "Tenant's $tenantId port has been updated in httpd from "
//                                    . "[" . $tenantServerPortTrim . "] to [" . $tenantDbPortTrim . "]"
//                                );
                                $resultTenantArr[$tenantId]['message']['success'][] = $msg;
                                $updateHttpd = true;
                            }
                            $logger->notice(
                                $msg
                            );
                            $updateThisHttpd = true;
                        } else {
                            echo "processDBTenants: httpdConfig for $tenantId: config does not have port=" . $tenantServerPortTrim . "<br>";
                        }
                    }

                    if( $updateThisHttpd === true ) {
                        $msg = "Httpd httpd service for tenant ".$tenantId." has been restarted.";
                        $logger->notice($msg);
//                        $session->getFlashBag()->add(
//                            'notice',
//                            "Restart httpd service for tenant ".$tenantId
//                        );
                        $resultTenantArr[$tenantId]['message']['success'][] = $msg;
                        $this->restartTenantHttpd($tenantId);
                    }

                }//if $httpdConfig

            }//if url changes


        }//foreach tenant

        if( $updateHttpd === true || $updateHaproxy === true ) {
            $msg = "Restart haproxy service";
            $logger->notice($msg);
//            $session->getFlashBag()->add(
//                'notice',
//                "Restart haproxy service"
//            );
            $resultArr['haproxy-ok'] = $resultArr['haproxy-ok'] . "; HAProxy service has been restarted.";
            $resultTenantArr['haproxy-message']['success'][] = $msg;
            $resHaproxy = $this->restartHaproxy();
            $logger->notice('haproxy-message='.$resHaproxy);
        }

        if( $updateHttpd === false ) {
            if( $resultArr['httpd-error'] == null ) {
                $msg = "The Apache HTTPD configuration has not been restarted, as no differences".
                    " were detected between the database and server configurations.";
            } else {
                $msg = "The Apache HTTPD configuration has not been restarted due to an error.";
            }

            $resultTenantArr['httpd-message']['error'][] = $msg;

//            $session->getFlashBag()->add(
//                'warning',
//                $msg
//            );
        }

        if( $updateHaproxy === false ) {
            if( $resultArr['haproxy-error'] == null ) {
                $msg = "The HAProxy configuration has not been restarted, as no differences".
                    " were detected between the database and server configurations.";
            } else {
                $msg = "The HAProxy configuration has not been restarted due to an error.";
            }
//            $session->getFlashBag()->add(
//                'warning',
//                $msg
//            );
            $resultTenantArr['haproxy-message']['error'][] = $msg;
        }

        //create new tenants
        //foreach($createNewTenants as $createNewTenant) {
        //    $msgCreateNewTenant = $this->createNewTenant($tenant);
        //}

       //exit('111');
        return $resultTenantArr; //$resultArr;
    }

    public function getTenantHttpd( $tenantId ) {
        $httpdPath = '/etc/httpd/conf/';
        // /etc/httpd/conf/tenantappdemo-httpd.conf
        $httpdFile = $httpdPath.$tenantId.'-httpd.conf';
        if( file_exists($httpdFile) ) {
            return $httpdFile;
        }
        return null;
    }

    public function createNewTenant($tenant) {
        //create new httpd file, add tenant to haproxy
        // order-lab/utils/executables/create-new-tenant.sh
        $logger = $this->container->get('logger');

        $tenantId = $tenant->getName();

        if( $tenant->getEnabled() !== true ) {
            $logger->notice("createNewTenant: Do not create new tenant $tenantId; tenant is not enabled.");
            return "createNewTenant: Do not create new tenant $tenantId; tenant is not enabled.";
        }

        $projectRoot = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex

        //don't create if already exists, if folder order-lab-$tenantId exists
        $tenantOrderFolder = $projectRoot."/../../order-lab-$tenantId";
        if( file_exists($tenantOrderFolder)===TRUE ) {
            $tenantCreateMsg = "A new tenant $tenantId has not been created because it already exists.";
            $logger->notice("createNewTenant: ".$tenantCreateMsg);
            return $tenantCreateMsg;
        }

        $tenant = $this->em->getRepository(TenantList::class)->findOneByName($tenantId);
        $url = $tenant->getUrlSlug();
        $port = $tenant->getTenantPort();
        $logger->notice("createNewTenant: create-new-tenant.sh: tenant=[$tenant], url=[$url], port=[$port]");

        $createNewTenantScript = $projectRoot.'/../utils/executables/create-new-tenant.sh';
        $createNewTenantScript = realpath($createNewTenantScript);

        if( file_exists($createNewTenantScript) === false ) {
            $logger->notice("createNewTenant: file does not exist: [$createNewTenantScript]");
        }

        $createNewTenantLog = $projectRoot."/var/log/create_$tenantId.log";
        $logger->notice("createNewTenant: createNewTenantLog=[$createNewTenantLog]");

        if(1) {
            $createCmd = 'sudo /bin/bash ' . $createNewTenantScript . ' -t ' . $tenantId . ' -p ' . $port . ' -u ' . $url . " > $createNewTenantLog";
            $logger->notice("createNewTenant: create new tenant, createCmd=[$createCmd]");
            //create-new-tenant.sh -t newtenant -p 8087 -u newtenant
            //$output = $this->runProcessShell($createCmd);
            $output = $this->runProcessSyncShell($createCmd);
            //exit('end runProcessShell, output='.$output);

            //$output = $this->runProcessWait($createCmd);
        }

//        $commandArr = array(
//            //'sudo',
//            '/bin/bash',
//            $createNewTenantScript,
//            '-t',
//            $tenantId,
//            '-p',
//            $port,
//            '-u',
//            $url,
//            '>',
//            $createNewTenantLog
//        );
//        $output = $this->runProcess_new($commandArr);

        return "A new tenant $tenantId has been created. ".$output."; HAProxy has been restarted.";
    }

    public function fileReplaceContent($path, $oldContent, $newContent)
    {
        $str = file_get_contents($path);
        $str = str_replace($oldContent, $newContent, $str);
        file_put_contents($path, $str);
    }
    /**
     * Replaces a string in a file
     *
     * @param string $FilePath
     * @param string $OldText text to be replaced
     * @param string $NewText new text
     * @return array $Result status (success | error) & message (file exist, file permissions)
     */
    function replaceAllInFile($FilePath, $OldText, $NewText)
    {
        $Result = array('status' => 'error', 'message' => '');
        if(file_exists($FilePath)===TRUE)
        {
            if(is_writeable($FilePath))
            {
                try
                {
                    $FileContent = file_get_contents($FilePath);
                    $FileContent = str_replace($OldText, $NewText, $FileContent);
                    if(file_put_contents($FilePath, $FileContent) > 0)
                    {
                        $Result["status"] = 'success';
                    }
                    else
                    {
                        $Result["message"] = 'Error while writing file';
                    }
                }
                catch(Exception $e)
                {
                    $Result["message"] = 'Error : '.$e;
                }
            }
            else
            {
                $Result["message"] = 'File '.$FilePath.' is not writable !';
            }
        }
        else
        {
            $Result["message"] = 'File '.$FilePath.' does not exist !';
        }
        return $Result;
    }

    //https://stackoverflow.com/questions/29182924/overwrite-a-specific-line-in-a-text-file-with-php
    public function changeLineInFile( $filePath, $lineIdentifier, $appendStr, $enable ) {
        //echo "filePath=".$filePath."<br>";
        //echo "lineIdentifier=".$lineIdentifier."<br>";
        //echo "appendStr=".$appendStr."<br>";

        $result = array('status' => 'error', 'message' => '');

        if(file_exists($filePath)===TRUE) {
            if (is_writeable($filePath)) {

                $content = file($filePath); // reads an array of lines
                //dump($content);
                //exit('111');

                foreach ($content as $key => $value) {
                    if (str_contains($value, $lineIdentifier)) {
                        $newValue = null;
                        if (!$enable) {
                            //Disable line
                            $newValue = $appendStr . $value;
                            //echo "append $appendStr line=[".$newValue."]<br>";
                        }
                        if ($enable) {
                            //Enable line
                            $newValue = str_replace($appendStr, '', $value);
                            //echo "remove $appendStr line=[".$newValue."]<br>";
                        }
                        //echo "line=[".$value."]?=[".$newValue."]<br>";
                        if ($value != $newValue) {
                            //echo "new line=".$newValue."<br>";
                            $content[$key] = $newValue;
                        }

                        //echo "new line=".$content[$key]."<br>";
                        break;
                    }
                }
                $allContent = implode("", $content);

                try
                {
                    if(file_put_contents($filePath, $allContent) > 0)
                    {
                        $result["status"] = 'success';
                    }
                    else
                    {
                        $result["message"] = 'Error while writing file';
                    }
                }
                catch(Exception $e)
                {
                    $result["message"] = 'Error : '.$e;
                }

            } else {
                $result["message"] = 'File '.$filePath.' is not writable !';
            }//if is_writeable
        } else {
            $result["message"] = 'File '.$filePath.' does not exist !';
        }//if file_exists

        //dump($allContent);
        //exit('111');
        return $result;
    }

    //TODO: run in background as cron?
    public function restartHaproxy() {
        //https://stackoverflow.com/questions/8532304/execute-root-commands-via-php
        //create haproxy-restart.sh
        //chown root /usr/local/bin/order-lab-tenantmanager/utils/executables/haproxy-restart.sh
        //chmod u=rwx,go=xr /usr/local/bin/order-lab-tenantmanager/utils/executables/haproxy-restart.sh
        //create haproxywrapper.c
        //https://askubuntu.com/questions/155791/how-do-i-sudo-a-command-in-a-script-without-being-asked-for-a-password
        //sudo systemctl restart haproxy
        //$output = shell_exec('/bin/sh /usr/local/bin/order-lab-tenantmanager/orderflex/php_root');
        //echo "<pre>$output</pre>";

//        $commandArr = array(
//            //'/usr/bin/systemctl restart haproxy',
//            '/usr/bin/systemctl',
//            'restart',
//            'haproxy'
//        );

        //Use the 'sudo visudo' command to edit the /etc/sudoers file
        //apache ALL=(ALL:ALL) NOPASSWD:/usr/local/bin/order-lab-tenantmanager/utils/executables/haproxy-restart.sh

        // /bin/su -s /bin/bash -c "/usr/local/bin/order-lab-tenantmanager/utils/executables/haproxy-restart.sh" apache
        //Permission denied:
        $projectRoot = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
        $haproxyRestartScript = $projectRoot.'/../utils/executables/haproxy-restart.sh';
        $haproxyRestartScript = realpath($haproxyRestartScript);

        //run: order-lab/utils/executables/haproxy-restart.sh
        //$commandArr = array(
        //    'sudo',
        //    '/bin/bash',
        //    $haproxyRestartScript
        //);
        //$this->runProcess($commandArr);

        //$this->runProcessShell("bash " . $projectRoot . DIRECTORY_SEPARATOR . "deploy.sh");

        $output = $this->runProcessShell('sudo /bin/bash '.$haproxyRestartScript);
        //exit('end runProcessShell, output='.$output);
        return $output;
    }

    public function restartTenantHttpd( $tenantId ) {
        //sudo systemctl restart haproxy
        //$output = shell_exec('sudo systemctl restart httpd'.$tenantId);
        //echo "<pre>$output</pre>";

//        $commandArr = array(
//            //'sudo systemctl restart httpd'.$tenantId,
//            '/usr/bin/systemctl',
//            'restart',
//            'httpd'.$tenantId
//        );
//        $this->runProcess($commandArr);

        $projectRoot = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
        $haproxyRestartScript = $projectRoot.'/../utils/executables/httpd-restart.sh';
        $haproxyRestartScript = realpath($haproxyRestartScript);
        $output = $this->runProcessShell('sudo /bin/bash '.$haproxyRestartScript.' -t '.$tenantId);
        //exit('end runProcessShell, output='.$output);
        return $output;
    }

    public function runProcess($commandArr) {
        $logger = $this->container->get('logger');
        $process = new Process($commandArr);
        $process->setTimeout(3600); //sec; 3600 sec => 60 min
        $process->setIdleTimeout(1800); //1800 sec => 30 min
        $process->run();

        if (!$process->isSuccessful()) {
            $logger->notice("runProcess: failed");
            throw new ProcessFailedException($process);
        } else {
            //echo "process successfull <br>";
            //$logger->notice("runProcess: successfull");
        }
        $output = $process->getOutput();
        $logger->notice("runProcess: output: ".$output);

        echo $output;
        return $output;
    }

    public function runProcess_new($commandArr) {
        $logger = $this->container->get('logger');
        $process = new Process($commandArr);
        $process->setTimeout(3600); //sec; 3600 sec => 60 min
        $process->setIdleTimeout(1800); //1800 sec => 30 min
        $process->setOptions(['create_new_console' => true]);

        $process->start();
        //return null;

//        if (!$process->isSuccessful()) {
//            $logger->notice("runProcess: failed");
//            throw new ProcessFailedException($process);
//        } else {
//            //echo "process successfull <br>";
//            //$logger->notice("runProcess: successfull");
//        }
        $output = $process->getOutput();
        $logger->notice("runProcess: output: ".$output);

        echo $output;
        return $output;
    }

    //Run process asynchronously in new console
    public function runProcessShell($script, $output=true)
    {
        echo "runProcessShell: script=[$script]<br>";
        $process = Process::fromShellCommandline($script);
        $process->setOptions(['create_new_console' => true]);
        $process->start();
        return null;
    }

    public function runProcessSyncShell($script, $output=true) {
        echo "runProcessSyncShell: script=[$script]<br>";
        $process = Process::fromShellCommandline($script);
        $process->setTimeout(3600); //sec; 3600 sec => 60 min
        $process->setIdleTimeout(1800); //1800 sec => 30 min
        //$process->setOptions(['create_new_console' => true]);

        //Was able to generate vendor
        try {
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $exception) {
            return $exception->getMessage();
        }

        //$process->start();

        return null;


//        $process->setTimeout(1800); //sec; 1800 sec => 30 min
//
//        if( $output === false ) {
//            $process->disableOutput();
//            $process->run();
//            return null;
//        }
//
//        $logger = $this->container->get('logger');
//        $logger->notice("runProcessShell: Start script=$script");
//
//        $process->run();
//
//        // wait a few seconds for the process to be ready
//        //sleep(5);
//
//        if (!$process->isSuccessful()) {
//            $logger->notice("runProcessShell: failed, script=$script");
//            throw new ProcessFailedException($process);
//        } else {
//            $logger->notice("runProcessShell: successfull, script=$script");
//        }
//        $output = $process->getOutput();
//        $logger->notice("runProcessShell: finish script=$script, output: ".$output);
//
//        // wait a few seconds for the process to be ready
//        //sleep(5);
//
//        return $output;
    }

    public function runProcessWait($script) {
        $logger = $this->container->get('logger');
        $process = Process::fromShellCommandline($script);
        //$process = new Process([$script]);
        //$process->disableOutput();
        //$process->setTimeout(1800); //sec; 1800 sec => 30 min
        $process->start();
        $process->wait();
        //return null;

//        while ($process->isRunning()) {
//            // waiting for process to finish
//        }

        if (!$process->isSuccessful()) {
            $logger->notice("runProcessWait: failed");
            throw new ProcessFailedException($process);
        } else {
            $logger->notice("runProcessWait: successfull");
        }
        $output = $process->getOutput();
        $logger->notice("runProcessWait: output: ".$output);
        return $output;
    }

    public function isTenantInitialized( $tenant ) {
        //return true; //testing
        $logger = $this->container->get('logger');
        $initialized = false;

        if( !$tenant ) {
            $logger->notice("isTenantInitialized: tenant is null");
            return $initialized;
        }

        //check if tenant's DB has users
        $conn = $this->getConnectionTenantDB($tenant);

        if( !$conn ) {
            $logger->notice("isTenantInitialized: connection is null for tenant DB=".$tenant->getDatabaseName());
            return $initialized;
        }

        $userSql = "SELECT * FROM " . 'user_fosuser';
        $userQuery = $conn->executeQuery($userSql);
        $userRows = $userQuery->fetchAllAssociative();
        //dump($userRows);
        //exit();
        //$id = $hostedGroupRows[0]['id'];
        $logger->notice("isTenantInitialized: found rows count=".count($userRows));
        if( count($userRows) > 0 ) {
            $initialized = true;
        }

        return $initialized;
    }

    public function getTenantsFromTenantManager( $tenantManagerName = 'tenantmanager' ) {
        $tenantDataArr['existedTenantIds'][] = $tenantManagerName;
        $tenantDataArr = $this->getTenantDataFromParameters($tenantDataArr);

        //dump($tenantDataArr);
        //exit('111');

        if( !isset($tenantDataArr[$tenantManagerName]) ) {
            return array();
        }

        $host = $tenantDataArr[$tenantManagerName]['databaseHost'];
        $dbname = $tenantDataArr[$tenantManagerName]['databaseName'];
        $user = $tenantDataArr[$tenantManagerName]['databaseUser'];
        $password = $tenantDataArr[$tenantManagerName]['databasePassword'];
        //echo "dbname=$dbname<br>";

        //create temporary tenant object
        $tenant = new TenantList();
        $tenant->setDatabaseHost($host);
        $tenant->setDatabaseName($dbname);
        $tenant->setDatabaseUser($user);
        $tenant->setDatabasePassword($password);

        //check if tenant's DB has users
        $conn = $this->getConnectionTenantDB($tenant);

        if( !$conn ) {
            return array();
        }

        //$tenantManagerSql = "SELECT * FROM " . 'user_tenantmanager';

        $tenantsSql = "SELECT * FROM " . 'user_tenantlist' . " WHERE tenantmanager_id=1";

        $tenantsQuery = $conn->executeQuery($tenantsSql);
        $tenantsRows = $tenantsQuery->fetchAllAssociative();
        //dump($tenantsRows);
        //exit('get Tenants From Tenant Manager');
        //$tenant = $hostedGroupRows[0]['id'];

        //destroy temporary $tenant
        unset($tenant);

        return $tenantsRows;
    }

    public function getTenantBaseUrls( $request ) {
        $tenantManagerName = 'tenantmanager';
        $tenantBaseUrlArr = array();
        $baseUrl = $request->getScheme() . '://' . $request->getHttpHost();
        $tenants = $this->getTenantsFromTenantManager($tenantManagerName); //TODO: make sure tenant is coming from tenant manager

//        $tenantManagerName = 'tenantmanager';
//        $tenantManagerUrl = null;
//        foreach ($tenants as $tenant) {
//            if( $tenant['name'] === $tenantManagerName ) {
//                $tenantManagerUrl = $tenant['urlslug'];
//                break;
//            }
//        }

        $currentTenantArr = $this->getCurrentTenantArr($request);

        foreach ($tenants as $tenantArr) {
            //$tenant as array
            if($tenantArr) {

                if( $currentTenantArr['urlslug'] == $tenantArr['urlslug'] ) {
                    //skip the current tenant in the list of available tenants
                    continue;
                }

                //create temporary tenant object
                $tenant = new TenantList();
                $tenant->setDatabaseHost($tenantArr['databasehost']);
                $tenant->setDatabaseName($tenantArr['databasename']);
                $tenant->setDatabaseUser($tenantArr['databaseuser']);
                $tenant->setDatabasePassword($tenantArr['databasepassword']);
                $tenant->setUrlslug($tenantArr['urlslug']);
                $tenant->setEnabled($tenantArr['enabled']);
                $tenant->setShowOnHomepage($tenantArr['showonhomepage']);
                $tenant->setInstitutionTitle($tenantArr['institutiontitle']);

                $showOnHomepage = $tenant->getShowOnHomepage();
                if( $showOnHomepage !== true ) {
                    continue;
                }

                $databasename = $tenant->getDatabaseName();
                $url = $tenant->getUrlslug();
                $instTitle = $tenant->getInstitutionTitle();
                //echo $databasename.": url=".$url."<br>";
                //echo $databasename.": instTitle=".$instTitle."<br>";

                if ($url) {
                    if ($url == '/') {
                        $tenantBaseUrl = $baseUrl;
                    } else {
                        $tenantBaseUrl = $baseUrl . '/' . $url;
                    }

                    if( !$instTitle ) {
                        $instTitle = $tenantBaseUrl;
                    }

                    //
                    //$tenantBaseUrl = '<a href="' . $tenantBaseUrl . '" target="_blank">' . $tenantBaseUrl . '</a> ';
                    $tenantBaseUrl = '<a href="' . $tenantBaseUrl . '" target="_blank">' . $instTitle . '</a> ';

                    $enabled = $tenant->getEnabled();
                    if( !$enabled ) {
                        $tenantBaseUrl = $tenantBaseUrl . " (Disabled)";
                    }

                    //isTenantInitialized
                    if( $this->isTenantInitialized($tenant) === false ) {
                        //$initializeUrl = $userTenantUtil->getInitUrl($tenant,$tenantManagerUrl);
                        $tenantBaseUrl = $tenantBaseUrl . " ("."not initialized".")";
                    }

                    $tenantBaseUrlArr[] = $tenantBaseUrl;
                }

                //destroy temporary $tenant
                unset($tenant);
            }
        }
        //echo 'tenantBaseUrlArr count='.count($tenantBaseUrlArr)."<br>";
        //dump($tenants);
        //exit('multiTenancyHomePage: get Tenants');

        return $tenantBaseUrlArr;
    }

    //check if current tenant is marked as a primaryTenant in tenantmanager's DB
    public function isPrimaryTenant( $request ) {

        $currentTenantArr = $this->getCurrentTenantArr($request);

        //dump($currentTenantArr);

        if( !$currentTenantArr || count($currentTenantArr) == 0 ) {
            return FALSE;
        }

        $primarytenant = $currentTenantArr['primarytenant'];

        return $primarytenant;
    }

    //TODO: check newtenantt init: http://143.198.22.81:8089/ - ok, http://143.198.22.81/newtenantt - not ok
    public function getInitUrl( $tenant, $tenantManagerUrl ) {
        //first-time-login-generation-init
        $url = $this->container->get('router')->generate('first-time-login-generation-init');

        //replace baseUrl with the tenant's baseUrl
        $tenantUrl = $tenant->getUrlSlug();
        $tenantUrl = trim($tenantUrl,'/');
        //replace 'tenant-manager'.'/directory' with $tenantUrl.'/directory'
        //$url = str_replace($tenantManagerUrl.'/directory',$tenantUrl.'/directory',$url);
        $url = str_replace($tenantManagerUrl,$tenantUrl,$url);
        //echo '$tenantManagerUrl='.$tenantManagerUrl.'; $url='.$url.'; $tenantUrl='.$tenantUrl.'<br>';
        //exit('111');
        //url=directory/admin/first-time-login-generation-init

        $href = " <a href=".$url." target='_blank'>Initialize Tenant</a> ";
        return $href;
    }

    public function getConnectionTenantDB( $tenant ) {

        $config = new \Doctrine\DBAL\Configuration();
        $config->setSchemaManagerFactory(new \Doctrine\DBAL\Schema\DefaultSchemaManagerFactory());

        $driver = $this->container->getParameter('database_driver');
        //$host = $container->getParameter('database_host');
        $port = $this->container->getParameter('database_port');
        //$dbname = $container->getParameter('database_name');
        //$user = $container->getParameter('database_user');
        //$password = $container->getParameter('database_password');

        $host = $tenant->getDatabaseHost();
        $dbname = $tenant->getDatabaseName();
        $user = $tenant->getDatabaseUser();
        $password = $tenant->getDatabasePassword();

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
            try {
                $conn->getDatabase();
            } catch (\Exception $e) {
                //exit('NO');
                //echo "getConnectionTenantDB: Failed to connect to system DB. Use the default DB; " . $e->getMessage() . "<br>";
                $conn = null;
            }
        }

        return $conn;
    }

    //Get current tenant data based on the current url based on request.
    public function getCurrentTenantArr( $request ) {
        $tenantManagerName = 'tenantmanager';
        $tenants = $this->getTenantsFromTenantManager($tenantManagerName);
        //echo "tenants=".count($tenants)."<br>";

        //$host = $request->getHost();
        //echo "host=$host <br>"; //view.online

        $currentFullUri = $request->getUri();
        //echo "currentFullUri=$currentFullUri <br>"; //http://view.online/c/wcm/pathology/saml/login/oli2002@med.cornell.edu

        $currentFullUriPath = parse_url($currentFullUri,PHP_URL_PATH); // '/tenant-manager/' or '/c/wcm/pathology/' or '/'
        //echo "currentFullUriPath=$currentFullUriPath <br>";

        foreach ($tenants as $tenantArr) {
            //$tenant as array
            if($tenantArr) {
                $urlslug = $tenantArr['urlslug'];
                //echo "urlslug=$urlslug <br>"; //c/wcm/pathology

                $primarytenant = $tenantArr['primarytenant'];
                //echo "primarytenant=$primarytenant <br>";
                //For primarytenant, the $currentFullUriPath will always be '/' on the main home page
                if( $primarytenant ) {
                    if( $currentFullUriPath == '/' ) {
                        return $tenantArr;
                    }
                }

                if( $urlslug != '/' ) {
                    if ($urlslug && $currentFullUri && str_contains($currentFullUri, $urlslug)) {
                        return $tenantArr;
                    }
                }
            }
        }
        return array();

//        $tenant->setDatabaseHost($tenantArr['databasehost']);
//        $tenant->setDatabaseName($tenantArr['databasename']);
//        $tenant->setDatabaseUser($tenantArr['databaseuser']);
//        $tenant->setDatabasePassword($tenantArr['databasepassword']);
//        $tenant->setUrlslug($tenantArr['urlslug']);
//        $tenant->setEnabled($tenantArr['enabled']);
//        $tenant->setShowOnHomepage($tenantArr['showonhomepage']);
//        $tenant->setInstitutionTitle($tenantArr['institutiontitle']);
    }

}