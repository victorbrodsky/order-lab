<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 3/20/2024
 * Time: 2:15 PM
 */

namespace App\UserdirectoryBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\AuthServerNetworkList;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\TenantList;
use App\UserdirectoryBundle\Entity\TenantManager;
use App\UserdirectoryBundle\Form\TenancyManagementType;
use App\UserdirectoryBundle\Form\TenantManagerType;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


//TenantManager - entity contains the list of the tenants as a ListOfHostedTenants (TenantList)
//Keep the list of tenants in TenantList. It's very similar to HostedGroupHolder,
// however 'TenantList' name is much clear than 'HostedGroupHolder'.
// We will not need HostedGroupHolder, plus the the purpose of the AuthServerNetworkList object is not clear.
// TenantManager has many tenant (TenantList). Each tenant has tenantUrl (url slug as TenantUrlList) and other tenant's parameters (name, db name, etc.)

//The homepage of the 'TenantManager' has:
// * Header Image : [DropZone field allowing upload of 1 image]
// * Greeting Text : [free text form field, multi-line, accepts HTML, with default value:
//  “Welcome to the View! The following organizations are hosted on this platform:”]
// * ListOfHostedTenants as a List of hosted tenants, each one shown as a clickable link
// * Main text [free text form field, multi-line, accepts HTML, with default value: “Please log in to manage the tenants on this platform.”]
// * Footer [free text form field, multi-line, accepts HTML, with default value: “[Home | <a href=”/about-us”>About Us</a> | Follow Us]”

//Once the user log into the Tenant Manager instance, to manage tenants “/tenant-manager/configure”:
//Each tenant (ListOfHostedTenants) you have the following attributes:
// * Name (that is what would be shown on the homepage list)
// * URL Slug (that will be used to construct the link to the tenant homepage - /c/wcm/pathology , etc)
// * Show on Homepage? (Yes/No, Boolean) if set to “No” do not show on the main homepage list
// * Parent (Hierarchical): Since the list of tenants is hierarchical it should stay hierarchical:
//      the root tenant is /c , /c/wcm is an entry for the institution with parent of /c ,
//      /c/wcm/pathology and /c/wcm/psychiatry have /c/wcm as parent, etc
// * Active and accessible via Web GUI? (Yes/No, Boolean) – this is separate from showing it on the homepage
//      or not – a tenant can be active and accessible via web gui but not shown on the homepage (like the test and demo sites),
//      or it can be set to be inaccessible – meaning even direct navigation to those
//      URLs /c/some-org/some-client-who-left would not let users access the site)
// * Database file name:””
// * Path to the database file: “”
// * Database password: “”
// * Platform Administrator Account User Name: “”
// * Tenant Institution Title: [free text]
// * Tenant Department Title: [free text]
// * Billing Tenant Administrator Contact Name: [free text]
// * Billing Tenant Administrator Contact Email: [free text]
// * Operational Tenant Administrator Contact Name: [free text]
// * Operational Tenant Administrator Contact Email: [free text]

//Each tenant should have the following four buttons:
// * “Create Tenant Database and Activate for Use” button when a completely new tenant is added without any additional actions
// * “Inactivate tenant and make it inaccessible via Web GUI” (this should not delete the database)
// * “Activate this previously inactivated tenant and make it accessible via Web GUI” (self-explanatory)
// * “Put Tenant Site in Maintenance Mode” (this is to kick out all users from the tenant and prevent all logins
//      except Platform Administrator – this will be useful if it is hacked).
// * “Reset Platform Administrator Account Password for this tenant” (Call your password generation function
//      you already have and show a dialog with a new password saying “Password for the platform administrator account “Administrator” for tenant “Tenant Name” accessible at “/c/link/here” has been reset to “NewPassword”.)

//Make sure php-fpm is started:	sudo systemctl start php-fpm
//php-fpm.service: A process of this unit has been killed by the OOM killer.
//sudo systemctl restart haproxy
//sudo systemctl restart php-fpm
//sudo systemctl start httpd"$1"

#[Route(path: '/settings')]
class MultiTenancyController extends OrderAbstractController
{

    //, methods: ['GET', 'POST'] TenantManager $tenantManager=null
    #[Route(path: '/tenant-manager/configure/', name: 'employees_tenancy_manager_configure')]
    #[Route(path: '/tenant-manager/configure/edit', name: 'employees_tenancy_manager_configure_edit')]
    #[Template('AppUserdirectoryBundle/MultiTenancy/tenant-manager-config.html.twig')]
    public function tenantManagerConfigureAction(Request $request)
    {
        //First show tenancy home page settings (TenantManager)
        //The homepage of the 'TenantManager' has:
        // * Header Image : [DropZone field allowing upload of 1 image]
        // * Greeting Text : [free text form field, multi-line, accepts HTML, with default value:
        //  “Welcome to the View! The following organizations are hosted on this platform:”]
        // * ListOfHostedTenants as a List of hosted tenants, each one shown as a clickable link
        // * Main text [free text form field, multi-line, accepts HTML, with default value: “Please log in to manage the tenants on this platform.”]
        // * Footer [free text form field, multi-line, accepts HTML, with default value: “[Home | <a href=”/about-us”>About Us</a> | Follow Us]”

        $userTenantUtil = $this->container->get('user_tenant_utility');

        $tenantRole = $userTenantUtil->getTenantRole();
        $tenantManagerName = 'tenantmanager';
        if( $tenantRole != $tenantManagerName ) {
            if( !$tenantRole ) {
                $tenantRole = 'undefined';
            }
            $this->addFlash(
                'warning',
                "Tenancy settings is accessible only from tenant manager system. Current system is $tenantRole"
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        //$userServiceUtil = $this->container->get('user_service_utility');

        $tenantManager = $userTenantUtil->getSingleTenantManager($createIfEmpty = true);
        //echo "tenantManager ID=".$tenantManager->getId()."<br>";

        $cycle = "show";
        $disabled = true;

        $routeName = $request->get('_route');
        if( $routeName == 'employees_tenancy_manager_configure_edit' ) {
            $cycle = "edit";
            $disabled = false;
        }
        //echo "cycle=".$cycle."<br>";
        //exit('111');

        $tenantManagerUrl = null;
        foreach ($tenantManager->getTenants() as $tenant) {
            if ($tenant) {
                if( $tenant->getName() === $tenantManagerName ) {
                    $tenantManagerUrl = $tenant->getUrlSlug();
                    break;
                }
            }
        }
        //echo "tenantManagerUrl=".$tenantManagerUrl."<br>";

        //check if tenant initialized, if not replace the url with
        // directory/admin/first-time-login-generation-init
        $tenantBaseUrlArr = array();

        //replace $request->getScheme() with getRealScheme($request)
        $userUtil = $this->container->get('user_utility');
        $scheme = $userUtil->getRealScheme($request);

        $baseUrl = $scheme . '://' . $request->getHttpHost();
        foreach ($tenantManager->getTenants() as $tenant) {
            if($tenant) {
                $url = $tenant->getUrlSlug();

                if ($url) {
                    if ($url == '/') {
                        $tenantBaseUrl = $baseUrl;
                    } else {
                        $tenantBaseUrl = $baseUrl . '/' . $url;
                    }

                    $tenantBaseUrl = '<a href="' . $tenantBaseUrl . '" target="_blank">' . $tenantBaseUrl . '</a> ';

                    $enabled = $tenant->getEnabled();
                    if( !$enabled ) {
                        $tenantBaseUrl = $tenantBaseUrl . " (Disabled)";
                    }

                    //isTenantInitialized
                    if( $userTenantUtil->isTenantInitialized($tenant) === false ) {
                        $initializeUrl = $userTenantUtil->getInitUrl($tenant,$tenantManagerUrl);
                        $tenantBaseUrl = $tenantBaseUrl . " (".$initializeUrl.")";
                    }

                    $tenantBaseUrlArr[] = $tenantBaseUrl;
                }
            }
        }


        $originalTenants = array();
        foreach ($tenantManager->getTenants() as $tenant) {
            $originalTenants[] = $tenant;
        }

        //echo "0 tenant count=".count($tenantManager->getTenants())."<br>";
        //foreach($tenantManager->getTenants() as $tenant) {
        //    echo "tenant=$tenant <br>";
        //}

        $params = array(
            //'cycle'=>"edit",
            //'em'=>$em,
        );
        $params['user'] = $user;
        $params['cycle'] = $cycle;
        $params['tenantRole'] = $tenantRole;
        $form = $this->createForm(TenantManagerType::class, $tenantManager, array(
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));
        $form->handleRequest($request);

        //echo "1 tenant count=".count($tenantManager->getTenants())."<br>";
        //foreach($tenantManager->getTenants() as $tenant) {
        //    echo "tenant=$tenant <br>";
        //}

        if( $form->isSubmitted() && $form->isValid() ) {

            //exit("tenantManagerConfigureAction: form is valid");

            $removedTenantCollections = array();
            $removedInfo = $this->removeTenantCollection($originalTenants,$tenantManager->getTenants(),$tenantManager);
            if( $removedInfo ) {
                $removedTenantCollections[] = $removedInfo;
                //echo "Remove tenant: ".$removedInfo."<br>";
                $this->addFlash(
                    'notice',
                    "Tenant has been removed from Database: ".$removedInfo
                );
            }

            //$em->getRepository(Document::class)->processDocuments($tenantManager,"logo");

            $em->flush();

            $this->addFlash(
                'notice',
                "Tenancy configuration have been updated."
            );

            //exit('111');
            return $this->redirect($this->generateUrl('employees_tenancy_manager_configure'));
            //return $this->redirect( $this->generateUrl('main_common_home') );
        }

        return array(
            'tenantManager' => $tenantManager,
            'tenantBaseUrlArr' => $tenantBaseUrlArr,
            'title' => "Tenancy Configuration",
            'form' => $form->createView(),
            'cycle' => $cycle
        );
    }

    //This straightforward approach to use processDBTenants causes Gateway timeout
    #[Route(path: '/tenant-manager/update-server-config', name: 'employees_tenancy_manager_update_server_config', methods: ['GET', 'POST'])]
    #[Template('AppUserdirectoryBundle/MultiTenancy/tenancy-management.html.twig')]
    public function syncTenantsUpdateServerConfigAction( Request $request )
    {

        $userTenantUtil = $this->container->get('user_tenant_utility');

        $tenantRole = $userTenantUtil->getTenantRole();
        if( $tenantRole != 'tenantmanager' ) {
            if( !$tenantRole ) {
                $tenantRole = 'undefined';
            }
            $this->addFlash(
                'warning',
                "Tenancy settings is accessible only from tenant manager system. Current system is $tenantRole"
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //$logger = $this->container->get('logger');
        //$logger->notice("syncTenantsUpdateServerConfigAction: tenantRole=$tenantRole");

        $tenantManager = $userTenantUtil->getSingleTenantManager($createIfEmpty = true);
        //$logger->notice("syncTenantsUpdateServerConfigAction: tenants count=" . count($tenantManager->getTenants()));

        set_time_limit(1800); //1800 seconds => 30 mins

        //Update server configuration files
        $res = $userTenantUtil->processDBTenants($tenantManager);

        if(0) {
            //$resultTenantArr['haproxy-message']['error']
            if (isset($res['haproxy-message']['error'])) {
                $this->addFlash(
                    'warning',
                    implode("<br>", $res['haproxy-message']['error'])
                );
            }
            //$resultTenantArr['haproxy-message']['success']
            if (isset($res['haproxy-message']['success'])) {
                $this->addFlash(
                    'notice',
                    implode("<br>", $res['haproxy-message']['success'])
                );
            }

            //$resultTenantArr['httpd-message']
            if (isset($res['httpd-message']['success'])) {
                $this->addFlash(
                    'notice',
                    implode("<br>", $res['httpd-message']['success'])
                );
            }
            if (isset($res['httpd-message']['error'])) {
                $this->addFlash(
                    'warning',
                    implode("<br>", $res['httpd-message']['error'])
                );
            }

            //dump($res);
            //exit('exit processDBTenants');

            //$resultTenantArr[$tenantId]['message']['error']
            //$resultTenantArr[$tenantId]['message']['success']
            foreach ($res as $tenantId => $tenantInfoArr) {
                if (isset($tenantInfoArr['message']['success'])) {
                    $this->addFlash(
                        'notice',
                        implode("<br>", $tenantInfoArr['message']['success'])
                    );
                }
                if (isset($tenantInfoArr['message']['error'])) {
                    $this->addFlash(
                        'warning',
                        implode("<br>", $tenantInfoArr['error']['success'])
                    );
                }
            }
        }

        $this->flashSessionResults($res);

        return $this->redirect($this->generateUrl('employees_tenancy_manager_configure'));
    }

    //Use async javascript approach to use processDBTenants
    #[Route(path: '/tenant-manager/update-server-config-ajax', name: 'employees_tenancy_manager_update_server_config_ajax', methods: ['GET'], options: ['expose' => true])]
    public function updateServerConfigAjaxAction(Request $request) {

        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $execTime = 1800; //sec 30 min
        ini_set('max_execution_time', $execTime);

        $result = "no testing";

        //$testFile = trim((string)$request->get('testFile'));

        $userTenantUtil = $this->container->get('user_tenant_utility');
        $tenantManager = $userTenantUtil->getSingleTenantManager($createIfEmpty = true);

        //set_time_limit(1800); //1800 seconds => 30 mins

        //Update server configuration files
        $res = $userTenantUtil->processDBTenants($tenantManager);

        $this->flashSessionResults($res);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;


        ///////////////// NOT USED BELOW /////////////////
        ///// Show result from buffer /////
        $projectRoot = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex

        $createNewTenantScript = $projectRoot.'/../utils/executables/create-new-tenant.sh';
        $createNewTenantScript = realpath($createNewTenantScript);
        $createNewTenantLog = $projectRoot."/var/log/create_$tenantId.log";

        $tenantId = 'newtenant';
        $tenant = $em->getRepository(TenantList::class)->findOneByName($tenantId);
        $url = $tenant->getUrlSlug();
        $port = $tenant->getTenantPort();

        $createCmd = 'sudo /bin/bash '.$createNewTenantScript.' -t '.$tenantId.' -p '.$port.' -u '.$url." > $createNewTenantLog";

        //$commandArr = array($testCmd,$testFilePath);

        $userUtil = $this->container->get('user_utility');
        //$scheme = $userUtil->getScheme();
        $scheme = $userUtil->getRealScheme();
        $envArr = array();
        //exit("scheme=$scheme");
        if( $scheme ) {
            if( strtolower($scheme) == 'http' ) {
                //echo "HTTP";
                $envArr = array('HTTP' => 1);
            } else {
                //echo "HTTPS";
                //$httpsChannel = true;
            }
        }

        $commandArr = explode(" ", $createCmd);
        $logDir = $this->container->get('kernel')->getProjectDir();
        $process = new Process($commandArr,$logDir,$envArr,null,$execTime);

        //$process = Process::fromShellCommandline($createCmd);

        $process->setTimeout(1800); //sec; 1800 sec => 30 min
        //$process->setOptions(['create_new_console' => true]);

        try {
            //$process->mustRun();
            $process->run();
            $buffer = $process->getOutput();
            $buffer = '<code><pre>'.$buffer.'</pre></code>';
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($buffer));
            return $response;
        } catch (ProcessFailedException $exception) {
            $buffer = $exception->getMessage();
            $buffer = '<code><pre>'.$buffer.'</pre></code>';
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($buffer));
            return $response;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($result));
        return $response;
    }

    public function flashSessionResults( $res ) {
        //$resultTenantArr['haproxy-message']['error']
        if( isset($res['haproxy-message']['error']) ) {
            $this->addFlash(
                'warning',
                implode("<br>", $res['haproxy-message']['error'])
            );
        }
        //$resultTenantArr['haproxy-message']['success']
        if( isset($res['haproxy-message']['success']) ) {
            $this->addFlash(
                'notice',
                implode("<br>", $res['haproxy-message']['success'])
            );
        }

        //$resultTenantArr['httpd-message']
        if( isset($res['httpd-message']['success']) ) {
            $this->addFlash(
                'notice',
                implode("<br>", $res['httpd-message']['success'])
            );
        }
        if( isset($res['httpd-message']['error']) ) {
            $this->addFlash(
                'warning',
                implode("<br>", $res['httpd-message']['error'])
            );
        }

        //dump($res);
        //exit('exit processDBTenants');

        //$resultTenantArr[$tenantId]['message']['error']
        //$resultTenantArr[$tenantId]['message']['success']
        foreach($res as $tenantId => $tenantInfoArr) {
            if( isset($tenantInfoArr['message']['success']) ) {
                $this->addFlash(
                    'notice',
                    implode("<br>", $tenantInfoArr['message']['success'])
                );
            }
            if( isset($tenantInfoArr['message']['error']) ) {
                $this->addFlash(
                    'warning',
                    implode("<br>", $tenantInfoArr['error']['success'])
                );
            }
        }
    }

    #[Route(path: '/tenant-manager/update-db-config', name: 'employees_tenancy_manager_update_db_config', methods: ['GET', 'POST'])]
    #[Template('AppUserdirectoryBundle/MultiTenancy/tenancy-management.html.twig')]
    public function syncTenantsUpdateDBConfigAction( Request $request )
    {
        $userTenantUtil = $this->container->get('user_tenant_utility');
        $tenantRole = $userTenantUtil->getTenantRole();
        if( $tenantRole != 'tenantmanager' ) {
            if( !$tenantRole ) {
                $tenantRole = 'undefined';
            }
            $this->addFlash(
                'warning',
                "Tenancy settings is accessible only from tenant manager system. Current system is $tenantRole"
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $tenantManager = $userTenantUtil->getSingleTenantManager($createIfEmpty = true);

        //get available tenants based on haproxy config (/etc/haproxy/haproxy.cfg) and httpd (/etc/httpd/conf/tenantname-httpd.conf)
        //homepagemanager-httpd.conf, tenantmanager-httpd.conf, tenantappdemo-httpd.conf, tenantapptest-httpd.conf, tenantapp1-httpd.conf, tenantapp2-httpd.conf
        $tenantDataArr = $userTenantUtil->getTenants();
        //dump($tenantDataArr);
        //exit('111');

        if( $tenantDataArr['error'] ) {
            if( count($tenantDataArr['error']) > 0 ) {
                $tenantsCount = 0;
                if( $tenantDataArr['existedTenantIds'] ) {
                    $tenantsCount = count($tenantDataArr['existedTenantIds']);
                }
                $this->addFlash(
                    'warning',
                    implode("<br>",$tenantDataArr['error']).
                    ", Tenants in the server config: ".$tenantsCount
                );
            }
        }

        //$tenantBaseUrlArr = array();
        //$baseUrl = $request->getScheme() . '://' . $request->getHttpHost();
        //$tenantBaseUrlArr[] = '<a href="'.$baseUrl.'">'.$baseUrl.'</a> ';

        if( $tenantDataArr['existedTenantIds'] ) {
            $orderInList = 0;
            foreach ($tenantDataArr['existedTenantIds'] as $tenantId) {
                if( $tenantId ) {
                    $tenantData = $tenantDataArr[$tenantId];
                    //dump($tenantData);
                    //echo "tenant=$tenantId: port=[".$tenantData['port']."]<br>";
                    //exit('111');

                    $enabled = $tenantData['enabled'];
                    $enabledStr = "Disabled";
                    if ($enabled) {
                        $enabledStr = "Enabled";
                    }

                    $url = null;
                    if (isset($tenantData['url'])) {
                        $url = $tenantData['url'];
                    }
                    //remove leading '/' if not a single '/'
                    if ($url != '/') {
                        $url = ltrim($url, '/');
                    }

                    //Add/Update tenants
                    //1) check if tenant from the file system exists in DB
                    $tenantDb = $em->getRepository(TenantList::class)->findOneByName($tenantId);

                    if (!$tenantDb) {
                        $tenantDb = new TenantList();
                        $tenantManager->addTenant($tenantDb);

                        $orderInList = $orderInList + 10;
                        $tenantDb->setMatchSystem("File system");
                        $tenantDb->setName($tenantId);
                        $tenantDb->setOrderinlist($orderInList);
                        $tenantDb->setEnabled($enabled);
                        $tenantDb->setShowOnHomepage(true);
                    }

                    //URL
                    //If url should corresponds to the list of URL,
                    // then we don't have any match for url '/' corresponding
                    // 'https://view.online' homepagemanager 127.0.0.1:8081
                    //Therefore, use field tenant's 'urlSlug' field
                    $tenantDb->setUrlSlug($url);

                    //Port (get it from haproxy or corresponding httpd)
                    //echo "tenant=$tenantId: port=[".$tenantData['port']."]<br>";
                    if (isset($tenantData['port'])) {
                        //$tenantPort = strval($tenantData['port']);
                        //echo "set port for tenant=$tenantId: port=[".$tenantPort."]<br>";
                        $tenantDb->setTenantPort($tenantData['port']);
                    }
                    //echo "tenant port DB=".$tenantDb->getTenantPort()."<br>";
                    //exit('111');

                    if (isset($tenantData['databaseName'])) {
                        $tenantDb->setDatabaseName($tenantData['databaseName']);
                    }

                    //Host (get it from corresponding parameters.yml 'localhost': order-lab-$tenantId/orderflex/config)
                    if (isset($tenantData['databaseHost'])) {
                        $tenantDb->setDatabaseHost($tenantData['databaseHost']);
                    }

                    //DB user (get it from corresponding parameters.yml)
                    if (isset($tenantData['databaseUser'])) {
                        $tenantDb->setDatabaseUser($tenantData['databaseUser']);
                    }

                    //DB password (get it from corresponding parameters.yml)
                    if (isset($tenantData['databasePassword'])) {
                        $tenantDb->setDatabasePassword($tenantData['databasePassword']);
                    }

                    $this->addFlash(
                        'notice',
                        "Tenant ".$tenantId." has been updated in DB"
                    );

                }//if( $tenantId ) {
            }//foreach

            $em->flush();

        }//if( $tenantDataArr['existedTenantIds'] )

        return $this->redirect( $this->generateUrl('employees_tenancy_manager_configure') );
    }

    public function removeTenantCollection($originalArr,$currentArr,$entity) {
        $em = $this->getDoctrine()->getManager();
        $removeArr = array();

        foreach( $originalArr as $element ) {
            if( false === $currentArr->contains($element) ) {
                $removeArr[] = "<strong>"."Removed tenant: ".$element." ".$this->getEntityId($element)."</strong>";
                $entity->removeTenant($element);
                //$element->setTenantManager(NULL);
                $em->persist($element);
                $em->remove($element);
            }
        } //foreach

        return implode("<br>", $removeArr);
    }
    public function getEntityId($entity) {
        if( $entity->getId() ) {
            return "ID=".$entity->getId();
        }
        return "New";
    }

    //////////// OLD ////////////////////////
    #[Route(path: '/tenancy-management', name: 'employees_tenancy_management', methods: ['GET', 'POST'])]
    #[Template('AppUserdirectoryBundle/MultiTenancy/tenancy-management.html.twig')]
    public function tenancyManagementAction( Request $request, KernelInterface $kernel )
    {
        //exit("tenancyManagementAction");
        $userTenantUtil = $this->container->get('user_tenant_utility');
        $tenantRole = $userTenantUtil->getTenantRole();
        if( $tenantRole != 'tenantmanager' ) {
            if( !$tenantRole ) {
                $tenantRole = 'undefined';
            }
            $this->addFlash(
                'warning',
                "Tenancy settings is accessible only from tenant manager system. Current system is $tenantRole"
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //ROLE_PLATFORM_DEPUTY_ADMIN or ROLE_SUPER_DEPUTY_ADMIN
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $this->addFlash(
                'warning',
                "Tenancy settings is accessible only by ROLE_SUPER_DEPUTY_ADMIN."
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //only if local is system
//        $locale = $request->getLocale();
//        //exit('$locale='.$locale);
//        if( $locale != "system" ) {
//            $this->addFlash(
//                'warning',
//                "Tenancy settings is accessible only for system database. Please relogin to /system"
//            );
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }

        //$user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        //$userSecUtil = $this->container->get('user_security_utility');
        //$siteParam = $userSecUtil->getSingleSiteSettingsParam();
        $userServiceUtil = $this->container->get('user_service_utility');
        $siteParam = $userServiceUtil->getSingleSiteSettingParameter();

        if( !$siteParam ) {
            throw $this->createNotFoundException('Unable to find SiteParameters entity.');
        }

        $title = "Tenancy Management";

        //find AuthServerNetworkList by name "Internet (Hub)" => show hostedGroupHolders (authservernetwork_edit)
        $authServerNetwork = $em->getRepository(AuthServerNetworkList::class)->findOneByName('Internet (Hub)');
        $authServerNetworkId = null;
        if( $authServerNetwork ) {
            $authServerNetworkId = $authServerNetwork->getId();
        }

        $params = array(
            //'cycle'=>"edit",
            //'em'=>$em,
        );

        $form = $this->createForm(TenancyManagementType::class, $siteParam, array(
            'form_custom_value' => $params,
        ));

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            exit("tenancyManagementAction: form is valid");

            $em->flush();

            $this->addFlash(
                'notice',
                "Tenancy settings have been updated."
            );

//            //runDeployScript
//            $userServiceUtil = $this->container->get('user_service_utility');
//            //$userServiceUtil->runDeployScript(false,false,true);
//            $output = $userServiceUtil->clearCacheInstallAssets($kernel);
//            $this->addFlash(
//                'notice',
//                "Container rebuilded, cache cleared, assets dumped. Output=".$output
//            );

            //exit('111');
            return $this->redirect($this->generateUrl('employees_tenancy_management'));
        }

        return array(
            'entity' => $siteParam,
            'title' => $title,
            'form' => $form->createView(),
            'authServerNetworkId' => $authServerNetworkId,
        );
    }

    #[Route(path: '/tenancy-management-update', name: 'employees_tenancy_management_update', methods: ['GET', 'POST'])]
    #[Template('AppSystemBundle/tenancy-management.html.twig')]
    public function updateTenancyManagementAction( Request $request, KernelInterface $kernel )
    {
        exit('updateTenancyManagementAction');
        $userTenantUtil = $this->container->get('user_tenant_utility');
        $tenantRole = $userTenantUtil->getTenantRole();
        if( $tenantRole != 'tenantmanager' ) {
            $this->addFlash(
                'warning',
                "Tenancy settings is accessible only from the tenant manager system."
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //ROLE_PLATFORM_DEPUTY_ADMIN or ROLE_SUPER_DEPUTY_ADMIN
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $this->addFlash(
                'warning',
                "Tenancy settings is accessible only by ROLE_SUPER_DEPUTY_ADMIN."
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        $em = $this->getDoctrine()->getManager();
        $authServerNetwork = $em->getRepository(AuthServerNetworkList::class)->findOneByName('Internet (Hub)');
        $authServerNetworkId = null;
        if( $authServerNetwork ) {
            $authServerNetworkId = $authServerNetwork->getId();
        }

        //Scan order instances

//        //Create DB if not exists
//        $output = null;
//        //https://carlos-compains.medium.com/multi-database-doctrine-symfony-based-project-0c1e175b64bf
//        $output = $userServiceUtil->checkAndCreateNewDBs($request,$authServerNetwork,$kernel);
//        $this->addFlash(
//            'notice',
//            "New DBs verified and created if not existed.<br> Output:<br>".$output
//        );
//
//        //runDeployScript
//        if(1) {
//            //$userServiceUtil->runDeployScript(false,false,true);
//            $output = $userServiceUtil->clearCacheInstallAssets($kernel);
//            $this->addFlash(
//                'notice',
//                "Container rebuilded, cache cleared, assets dumped. Output=" . $output
//            );
//        }

        return $this->redirect($this->generateUrl('employees_tenancy_management'));
    }











//////////////// BELOW IS THE OLD IMPLEMENTATION, NOT USED /////////////////
    #[Route(path: '/tenancy-management_orig', name: 'employees_tenancy_management_orig', methods: ['GET', 'POST'])]
    #[Template('AppSystemBundle/tenancy-management.html.twig')]
    public function tenancyManagementOrigAction( Request $request, KernelInterface $kernel )
    {
        $userTenantUtil = $this->container->get('user_tenant_utility');
        $tenantRole = $userTenantUtil->getTenantRole();
        if( $tenantRole != 'tenantmanager' ) {
            if( !$tenantRole ) {
                $tenantRole = 'undefined';
            }
            $this->addFlash(
                'warning',
                "Tenancy settings is accessible only from tenant manager system. Current system is $tenantRole"
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //ROLE_PLATFORM_DEPUTY_ADMIN or ROLE_SUPER_DEPUTY_ADMIN
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $this->addFlash(
                'warning',
                "Tenancy settings is accessible only by ROLE_SUPER_DEPUTY_ADMIN."
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //only if local is system
//        $locale = $request->getLocale();
//        //exit('$locale='.$locale);
//        if( $locale != "system" ) {
//            $this->addFlash(
//                'warning',
//                "Tenancy settings is accessible only for system database. Please relogin to /system"
//            );
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }

        $em = $this->getDoctrine()->getManager();
        //$userSecUtil = $this->container->get('user_security_utility');
        //$siteParam = $userSecUtil->getSingleSiteSettingsParam();
        $userServiceUtil = $this->container->get('user_service_utility');
        $siteParam = $userServiceUtil->getSingleSiteSettingParameter();

        if( !$siteParam ) {
            throw $this->createNotFoundException('Unable to find SiteParameters entity.');
        }

        $title = "Tenancy Management";

        //find AuthServerNetworkList by name "Internet (Hub)" => show hostedGroupHolders (authservernetwork_edit)
        $authServerNetwork = $em->getRepository(AuthServerNetworkList::class)->findOneByName('Internet (Hub)');
        $authServerNetworkId = null;
        if( $authServerNetwork ) {
            $authServerNetworkId = $authServerNetwork->getId();
        }

        $params = array(
            //'cycle'=>"edit",
            //'em'=>$em,
        );

        $form = $this->createForm(TenancyManagementType::class, $siteParam, array(
            'form_custom_value' => $params,
        ));

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            //exit("form is valid");

            $em->flush();

            $this->addFlash(
                'notice',
                "Tenancy settings have been updated."
            );

//            //runDeployScript
//            $userServiceUtil = $this->container->get('user_service_utility');
//            //$userServiceUtil->runDeployScript(false,false,true);
//            $output = $userServiceUtil->clearCacheInstallAssets($kernel);
//            $this->addFlash(
//                'notice',
//                "Container rebuilded, cache cleared, assets dumped. Output=".$output
//            );

            //exit('111');
            return $this->redirect($this->generateUrl('employees_tenancy_management'));
        }

        return array(
            'entity' => $siteParam,
            'title' => $title,
            'form' => $form->createView(),
            'authServerNetworkId' => $authServerNetworkId,
        );
    }

    #[Route(path: '/tenancy-management-update-orig', name: 'employees_tenancy_management_update_orig', methods: ['GET', 'POST'])]
    #[Template('AppSystemBundle/tenancy-management.html.twig')]
    public function updateTenancyManagementOrigAction( Request $request, KernelInterface $kernel )
    {
        $userTenantUtil = $this->container->get('user_tenant_utility');
        $tenantRole = $userTenantUtil->getTenantRole();
        if( $tenantRole != 'tenantmanager' ) {
            $this->addFlash(
                'warning',
                "Tenancy settings is accessible only from the tenant manager system."
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //ROLE_PLATFORM_DEPUTY_ADMIN or ROLE_SUPER_DEPUTY_ADMIN
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $this->addFlash(
                'warning',
                "Tenancy settings is accessible only by ROLE_SUPER_DEPUTY_ADMIN."
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        $em = $this->getDoctrine()->getManager();
        $authServerNetwork = $em->getRepository(AuthServerNetworkList::class)->findOneByName('Internet (Hub)');
        $authServerNetworkId = null;
        if( $authServerNetwork ) {
            $authServerNetworkId = $authServerNetwork->getId();
        }

        //Create DB if not exists
        $output = null;
        //https://carlos-compains.medium.com/multi-database-doctrine-symfony-based-project-0c1e175b64bf
        $output = $userServiceUtil->checkAndCreateNewDBs($request,$authServerNetwork,$kernel);
        $this->addFlash(
            'notice',
            "New DBs verified and created if not existed.<br> Output:<br>".$output
        );

        //runDeployScript
        if(1) {
            //$userServiceUtil->runDeployScript(false,false,true);
            $output = $userServiceUtil->clearCacheInstallAssets($kernel);
            $this->addFlash(
                'notice',
                "Container rebuilded, cache cleared, assets dumped. Output=" . $output
            );
        }

        return $this->redirect($this->generateUrl('employees_tenancy_management'));
    }

    //Homepage
    #[Route(path: '/homepage-manager/configure/', name: 'employees_homepage_manager_configure')]
    #[Route(path: '/homepage-manager/configure/edit', name: 'employees_homepage_manager_configure_edit')]
    #[Template('AppUserdirectoryBundle/MultiTenancy/homepage-manager-config.html.twig')]
    public function homepageConfigureAction(Request $request)
    {
        //First show tenancy home page settings (TenantManager)
        //The homepage of the 'TenantManager' has:
        // * Header Image : [DropZone field allowing upload of 1 image]
        // * Greeting Text : [free text form field, multi-line, accepts HTML, with default value:
        //  “Welcome to the View! The following organizations are hosted on this platform:”]
        // * ListOfHostedTenants as a List of hosted tenants, each one shown as a clickable link
        // * Main text [free text form field, multi-line, accepts HTML, with default value: “Please log in to manage the tenants on this platform.”]
        // * Footer [free text form field, multi-line, accepts HTML, with default value: “[Home | <a href=”/about-us”>About Us</a> | Follow Us]”

        $userTenantUtil = $this->container->get('user_tenant_utility');

        $tenantManagerName = 'homepagemanager';
        $tenantRole = $userTenantUtil->getTenantRole();
        if( $tenantRole != $tenantManagerName ) {
            if( !$tenantRole ) {
                $tenantRole = 'undefined';
            }
            $this->addFlash(
                'warning',
                "Home page manager settings is accessible only from home manager system. Current system is $tenantRole"
            );
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $userServiceUtil = $this->container->get('user_service_utility');

        $tenantManager = $userTenantUtil->getSingleTenantManager($createIfEmpty = true);
        //echo "tenantManager ID=".$tenantManager->getId()."<br>";

        $cycle = "show";
        $disabled = true;

        $routeName = $request->get('_route');
        if( $routeName == 'employees_homepage_manager_configure_edit' ) {
            $cycle = "edit";
            $disabled = false;
        }
        //echo "cycle=".$cycle."<br>";
        //exit('111');

        $tenantManagerUrl = null;
        foreach ($tenantManager->getTenants() as $tenant) {
            if ($tenant) {
                if( $tenant->getName() === $tenantManagerName ) {
                    $tenantManagerUrl = $tenant->getUrlSlug();
                    break;
                }
            }
        }
        //echo "tenantManagerUrl=".$tenantManagerUrl."<br>";

        //check if tenant initialized, if not replace the url with
        // directory/admin/first-time-login-generation-init
        $tenantBaseUrlArr = array();

        //replace $request->getScheme() with getRealScheme($request)
        $userUtil = $this->container->get('user_utility');
        $scheme = $userUtil->getRealScheme($request);

        $baseUrl = $scheme . '://' . $request->getHttpHost();
        foreach ($tenantManager->getTenants() as $tenant) {
            if($tenant) {
                $url = $tenant->getUrlSlug();

                if ($url) {
                    if ($url == '/') {
                        $tenantBaseUrl = $baseUrl;
                    } else {
                        $tenantBaseUrl = $baseUrl . '/' . $url;
                    }

                    $tenantBaseUrl = '<a href="' . $tenantBaseUrl . '" target="_blank">' . $tenantBaseUrl . '</a> ';

                    $enabled = $tenant->getEnabled();
                    if( !$enabled ) {
                        $tenantBaseUrl = $tenantBaseUrl . " (Disabled)";
                    }

                    //isTenantInitialized
                    if( $userTenantUtil->isTenantInitialized($tenant) === false ) {
                        $initializeUrl = $userTenantUtil->getInitUrl($tenant,$tenantManagerUrl);
                        $tenantBaseUrl = $tenantBaseUrl . " (".$initializeUrl.")";
                    }

                    $tenantBaseUrlArr[] = $tenantBaseUrl;
                }
            }
        }

        //echo "0 tenant count=".count($tenantManager->getTenants())."<br>";
        //foreach($tenantManager->getTenants() as $tenant) {
        //    echo "tenant=$tenant <br>";
        //}

        $params = array(
            //'cycle'=>"edit",
            //'em'=>$em,
        );
        $params['user'] = $user;
        $params['cycle'] = $cycle;
        $params['tenantRole'] = $tenantRole;
        $form = $this->createForm(TenantManagerType::class, $tenantManager, array(
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));
        $form->handleRequest($request);

        //echo "1 tenant count=".count($tenantManager->getTenants())."<br>";
        //foreach($tenantManager->getTenants() as $tenant) {
        //    echo "tenant=$tenant <br>";
        //}

        if( $form->isSubmitted() && $form->isValid() ) {

            //exit("HomePageManagerConfigureAction: form is valid");

            $em->getRepository(Document::class)->processDocuments($tenantManager,"logo");
            $em->getRepository(Document::class)->processDocuments($tenantManager,"aboutusLogo");
            $em->getRepository(Document::class)->processDocuments($tenantManager,"highResLogo");

            $em->flush();

            $this->addFlash(
                'notice',
                "Homepage manager configuration have been updated."
            );

            return $this->redirect($this->generateUrl('employees_homepage_manager_configure'));
        }

        return array(
            'tenantManager' => $tenantManager,
            'tenantBaseUrlArr' => $tenantBaseUrlArr,
            'title' => "Tenancy Configuration",
            'form' => $form->createView(),
            'cycle' => $cycle
        );
    }

}