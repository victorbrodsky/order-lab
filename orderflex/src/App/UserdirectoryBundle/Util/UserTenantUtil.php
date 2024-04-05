<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 3/29/2024
 * Time: 12:05 PM
 */

namespace App\UserdirectoryBundle\Util;


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
        $userSecUtil = $this->container->get('user_security_utility');
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

        $userServiceUtil = $this->container->get('user_service_utility');

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

    //Best option is to have shell script to create and modify config files and to run restart
    public function processDBTenants( $tenantManager ) {

        $logger = $this->container->get('logger');
        $userUtil = $this->container->get('user_utility');
        $session = $userUtil->getSession(); //$this->container->get('session');

        $updateHaproxy = false;
        $updateHttpd = false;
        $resultArr = array();
        $resultArr['haproxy-error'] = null;
        $resultArr['httpd-error'] = null;

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
            if( $tenantId != 'tenantapptest' ) {
                continue;
            }

            $haproxyConfig = $this->getHaproxyConfig();

            //Enable/Disable => haproxy
            $tenantDataArr = array();
            $tenantDataArr['existedTenantIds'][] = $tenantId;
            $tenantDataArr = $this->getTenantDataFromHaproxy($tenantDataArr);
            //dump($tenantDataArr);
            //exit('111');

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
                        } else {
                            $enabledStr = "disabled";
                            if( $tenant->getEnabled() ) {
                                $enabledStr = "enabled";
                            }
                            $session->getFlashBag()->add(
                                'note',
                                "Tenant $tenantId has been $enabledStr in haproxy config"
                            );
                            $logger->notice(
                                "Update haproxy config for tenant ".$tenantId.", updated to ".$enabledStr
                            );
                            $updateHaproxy = true;
                        }
                        break;
                    }
                }
            }

            //update URL slug or tenant's port: modify files: haproxy and $tenantId-httpd.conf
            $tenantDbUrl = $tenant->getUrlSlug();
            $tenantDbUrlTrim = trim(trim($tenantDbUrl,'/'));
            $tenantServerUrlTrim = trim(trim($tenantDataArr[$tenantId]['url'],'/'));

            $tenantDbPort = $tenant->getTenantPort();
            $tenantDbPortTrim = trim($tenantDbPort);
            $tenantServerPortTrim = trim($tenantDataArr[$tenantId]['port']);

            $logger->notice("compare url: ".$tenantDbUrlTrim."?=".$tenantServerUrlTrim);
            $logger->notice("compare port: ".$tenantDbPortTrim."?=".$tenantServerPortTrim);

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
                            } else {
                                $session->getFlashBag()->add(
                                    'note',
                                    "Tenant $tenantId url has been updated in haproxy config from"
                                    ."[".$tenantServerUrlTrim."]"
                                    ." to [".$tenantDbUrlTrim."]"
                                );
                                $logger->notice(
                                    "Update haproxy config for tenant ".$tenantId.", update URL from "
                                    ."[".$tenantServerUrlTrim."]"
                                    ." to [".$tenantDbUrlTrim."]"
                                );
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
                            } else {
                                $session->getFlashBag()->add(
                                    'note',
                                    "Tenant $tenantId port has been updated in haproxy config from"
                                    ."[".$tenantServerPortTrim."]"
                                    ." to [".$tenantDbPortTrim."]"
                                );
                                $logger->notice(
                                    "Update haproxy config for tenant ".$tenantId.", update port from "
                                    ."[".$tenantServerPortTrim."]"
                                    ." to [".$tenantDbPortTrim."]"
                                );
                                $updateHaproxy = true;
                            }
                            break;
                        }
                    }//foreach tenant's haproxy
                }


                //URL: change URL in httpd config file
                $httpdConfig = $this->getTenantHttpd($tenantId);
                echo "httpdConfig=[$httpdConfig]<br>";
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
                            } else {
                                $msg = "Tenant's $tenantId url has been updated in httpd from "
                                    . $tenantServerUrlTrim . " to " . $tenantDbUrlTrim;
                                echo "msg=" . $msg . "<br>";
                                $session->getFlashBag()->add(
                                    'note',
                                    $msg
                                );
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
                            } else {
                                echo "processDBTenants: $tenantId: status=" . $res['status'] . "<br>";
                                $session->getFlashBag()->add(
                                    'note',
                                    "Tenant's $tenantId port has been updated in httpd from "
                                    . "[" . $tenantServerPortTrim . "] to [" . $tenantDbPortTrim . "]"
                                );
                                $updateHttpd = true;
                            }
                            $logger->notice(
                                "Update httpd config for tenant " . $tenantId . ", update port from "
                                . "[" . $tenantServerPortTrim . "]"
                                . " to [" . $tenantDbPortTrim . "]"
                            );
                            $updateThisHttpd = true;
                        } else {
                            echo "processDBTenants: httpdConfig for $tenantId: config does not have port=" . $tenantServerPortTrim . "<br>";
                        }
                    }

                    if( $updateThisHttpd === true ) {
                        $logger->notice("Restart httpd service for tenant ".$tenantId);
                        $session->getFlashBag()->add(
                            'notice',
                            "Restart httpd service for tenant ".$tenantId
                        );
                        $this->restartTenantHttpd($tenantId);
                    }

                }//if $httpdConfig

            }//if url changes


        }//foreach

        if( $updateHttpd === true && $updateHaproxy === true ) {
            $logger->notice("Restart haproxy service");
            $session->getFlashBag()->add(
                'notice',
                "Restart haproxy service"
            );
            $this->restartHaproxy();
        }

        if( $updateHttpd === false ) {
            if( $resultArr['httpd-error'] == null ) {
                $msg = "The Apache HTTPD configuration has not been restarted because no differences".
                    " have been detected between the database and server configurations.";
            } else {
                $msg = "The Apache HTTPD configuration has not been restarted due to an error.";
            }

            $session->getFlashBag()->add(
                'warning',
                $msg
            );
        }

        if( $updateHaproxy === false ) {
            if( $resultArr['haproxy-error'] == null ) {
                $msg = "The HAProxy configuration has not been restarted because no differences".
                " have been detected between the database and server configurations.";
            } else {
                $msg = "The HAProxy configuration has not been restarted due to an error.";
            }
            $session->getFlashBag()->add(
                'warning',
                $msg
            );
        }

       //exit('111');
        return $resultArr;
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

    public function runProcessShell($script, $output=true) {
        echo "runProcessShell: script=[$script]<br>";
        $process = Process::fromShellCommandline($script);
        $process->setOptions(['create_new_console' => true]);
        $process->start();
        return null;


        $process->setTimeout(1800); //sec; 1800 sec => 30 min

        if( $output === false ) {
            $process->disableOutput();
            $process->run();
            return null;
        }

        $logger = $this->container->get('logger');
        //$process->run();

        // wait a few seconds for the process to be ready
        //sleep(5);

        if (!$process->isSuccessful()) {
            $logger->notice("runProcessShell: failed, script=$script");
            throw new ProcessFailedException($process);
        } else {
            $logger->notice("runProcessShell: successfull, script=$script");
        }
        $output = $process->getOutput();
        $logger->notice("runProcessShell: finish script=$script, output: ".$output);

        // wait a few seconds for the process to be ready
        //sleep(5);

        return $output;
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

        while ($process->isRunning()) {
            // waiting for process to finish
        }

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

}