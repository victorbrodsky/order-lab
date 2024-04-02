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

#[Route(path: '/settings')]
class MultiTenancyController extends OrderAbstractController
{

    //, methods: ['GET', 'POST'] TenantManager $tenantManager=null
    #[Route(path: '/tenant-manager/configure', name: 'employees_tenancy_manager_configure')]
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

        $tenantRole = $this->getParameter('tenant_role');
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
        $user = $this->getUser();
        $userServiceUtil = $this->container->get('user_service_utility');
        $userTenantUtil = $this->container->get('user_tenant_utility');

        $tenantManager = $userTenantUtil->getSingleTenantManager($createIfEmpty = true);
        //echo "tenantManager ID=".$tenantManager->getId()."<br>";

        $cycle = "edit";

        $originalTenants = array();
        foreach( $tenantManager->getTenants() as $tenant ) {
            $originalTenants[] = $tenant;
            $tenant->setMatchSystem("Database");
        }

        //get available tenants based on haproxy config (/etc/haproxy/haproxy.cfg) and httpd (/etc/httpd/conf/tenantname-httpd.conf)
        //homepagemanager-httpd.conf, tenantmanager-httpd.conf, tenantappdemo-httpd.conf, tenantapptest-httpd.conf, tenantapp1-httpd.conf, tenantapp2-httpd.conf
        $tenantDataArr = $userTenantUtil->getTenants();

        if( $tenantDataArr['error'] ) {
            foreach($tenantDataArr['error'] as $error ) {
//                $this->addFlash(
//                    'warning',
//                    $error
//                );
            }
            if( count($tenantDataArr['error']) > 0 ) {
                $this->addFlash(
                    'warning',
                    implode("<br>",$tenantDataArr['error'])
                );
            }
        }

        $tenantBaseUrlArr = array();

        $baseUrl = $request->getScheme() . '://' . $request->getHttpHost();
        //$tenantBaseUrlArr[] = '<a href="'.$baseUrl.'">'.$baseUrl.'</a> ';

        if( $tenantDataArr['existedTenantIds'] ) {
            $orderInList = 0;
            foreach ($tenantDataArr['existedTenantIds'] as $tenantId) {
                if( $tenantId ) {
                    $tenantData = $tenantDataArr[$tenantId];
                    $enabled = $tenantData['enabled'];
                    $enabledStr = "Disabled";
                    if ($enabled) {
                        $enabledStr = "Enabled";
                    }

                    $url = null;
                    if( isset($tenantData['url']) ) {
                        $url = $tenantData['url'];
                    }
                    //remove leading '/' if not a single '/'
                    if( $url != '/' ) {
                        $url = ltrim($url, '/');
                    }
//                    $this->addFlash(
//                        'notice',
//                        "Tenant ID=" . $tenantId . "; " . $enabledStr . "; url=" . $url
//                    );

                    if( $url ) {
                        if( $url == '/' ) {
                            $tenantBaseUrl = $baseUrl;
                        } else {
                            $tenantBaseUrl = $baseUrl . '/' . $url;
                        }

                        $tenantBaseUrl = '<a href="' . $tenantBaseUrl . '" target="_blank">' . $tenantBaseUrl . '</a> ';
                        if( !$enabled ) {
                            $tenantBaseUrl = $tenantBaseUrl . " ($enabledStr)";
                        }
                        $tenantBaseUrlArr[] = $tenantBaseUrl;
                    }

                    //Add tenants to the tenant's section
                    //1) check if tenant from the file system exists in DB
                    $tenantDb = $em->getRepository(TenantList::class)->findOneByName($tenantId);
                    if( $tenantDb ) {
                        //tenant already exists in DB => don't add
                        $tenantDb->setMatchSystem("Database");
                    } else {
                        //add tenant to DB and, therefore, this form
                        $orderInList = $orderInList + 10;
                        $newTenant = new TenantList($user);
                        //$em->persist($newTenant);
                        $tenantManager->addTenant($newTenant);

                        $newTenant->setMatchSystem("File system");
                        $newTenant->setName($tenantId);
                        $newTenant->setOrderinlist($orderInList);
                        $newTenant->setEnabled($enabled);
                        $newTenant->setShowOnHomepage(false);

                        //URL
                        //If url should corresponds to the list of URL,
                        // then we don't have any match for url '/' corresponding
                        // 'https://view.online' homepagemanager 127.0.0.1:8081
                        //Therefore, use field tenant's 'urlSlug' field
                        $newTenant->setUrlSlug($url);

                        //Port (get it from haproxy or corresponding httpd)
                        if( isset($tenantData['port']) ) {
                            $newTenant->setTenantPort($tenantData['port']);
                        }

                        if( isset($tenantData['databaseName']) ) {
                            $newTenant->setDatabaseName($tenantData['databaseName']);
                        }

                        //Host (get it from corresponding parameters.yml 'localhost': order-lab-$tenantId/orderflex/config)
                        if( isset($tenantData['databaseHost']) ) {
                            $newTenant->setDatabaseHost($tenantData['databaseHost']);
                        }

                        //DB user (get it from corresponding parameters.yml)
                        if( isset($tenantData['databaseUser']) ) {
                            $newTenant->setDatabaseUser($tenantData['databaseUser']);
                        }

                        //DB password (get it from corresponding parameters.yml)
                        if( isset($tenantData['databasePassword']) ) {
                            $newTenant->setDatabasePassword($tenantData['databasePassword']);
                        }
                    }
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
        $form = $this->createForm(TenantManagerType::class, $tenantManager, array(
            'form_custom_value' => $params,
        ));
        $form->handleRequest($request);

        //echo "1 tenant count=".count($tenantManager->getTenants())."<br>";
        //foreach($tenantManager->getTenants() as $tenant) {
        //    echo "tenant=$tenant <br>";
        //}

        if( $form->isSubmitted() && $form->isValid() ) {

            //exit("tenantManagerConfigureAction: form is valid");

            //$res = null;
            //$res = $userTenantUtil->processDBTenants($tenantManager);
            //dump($res);
            //exit('111');
//            if( $res ) {
//                $haproxyError = $res['haproxy-error'];
//                if ($haproxyError) {
//                    //echo "$tenantId: haproxyError=$haproxyError<br>";
//                    $this->addFlash(
//                        'warning',
//                        $haproxyError
//                    );
//                }
//
//                if ($res['httpd-error']) {
//                    foreach ($res['httpd-error'] as $tenantId => $errorMessage) {
//                        //$httpdError = "$tenantId: errorMessage=$errorMessage";
//                        //echo "$httpdError<br>";
//                        $this->addFlash(
//                            'warning',
//                            "Tenant $tenantId: " . $errorMessage
//                        );
//                    }
//                }
//            }
            //exit('111');

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

            //echo "2 tenant count=".count($tenantManager->getTenants())."<br>";
            //foreach($tenantManager->getTenants() as $tenant) {
            //    echo "tenant=$tenant <br>";
            //}
            //exit("tenantManagerConfigureAction: submitted");

            $em->getRepository(Document::class)->processDocuments($tenantManager,"logo");

            $em->flush();

            $this->addFlash(
                'notice',
                "Tenancy configuration have been updated."
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
            //return $this->redirect($this->generateUrl('employees_tenancy_manager_configure'));
            return $this->redirect( $this->generateUrl('main_common_home') );
        }

        return array(
            'tenantManager' => $tenantManager,
            'tenantBaseUrlArr' => $tenantBaseUrlArr,
            'title' => "Tenancy Configuration",
            'form' => $form->createView(),
            'cycle' => $cycle
        );
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

    #[Route(path: '/tenant-manager/update-server-config', name: 'employees_tenancy_manager_update_server_config', methods: ['GET', 'POST'])]
    #[Template('AppUserdirectoryBundle/MultiTenancy/tenancy-management.html.twig')]
    public function syncTenantsUpdateServerConfigAction( Request $request, KernelInterface $kernel )
    {
        $tenantRole = $this->getParameter('tenant_role');
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

        $tenantManager = $userTenantUtil->getSingleTenantManager($createIfEmpty = true);
        $res = $userTenantUtil->processDBTenants($tenantManager);

        //dump($res);
        //exit('111');

        if( $res ) {
            $haproxyError = $res['haproxy-error'];
            if ($haproxyError) {
                //echo "$tenantId: haproxyError=$haproxyError<br>";
                $this->addFlash(
                    'warning',
                    $haproxyError
                );
            }

            if ($res['httpd-error']) {
                foreach ($res['httpd-error'] as $tenantId => $errorMessage) {
                    //$httpdError = "$tenantId: errorMessage=$errorMessage";
                    //echo "$httpdError<br>";
                    $this->addFlash(
                        'warning',
                        "Tenant $tenantId: " . $errorMessage
                    );
                }
            }
        }

        //return $this->redirect($this->generateUrl('employees_tenancy_manager_configure'));
        return $this->redirect( $this->generateUrl('main_common_home') );
    }


    #[Route(path: '/tenancy-management', name: 'employees_tenancy_management', methods: ['GET', 'POST'])]
    #[Template('AppUserdirectoryBundle/MultiTenancy/tenancy-management.html.twig')]
    public function tenancyManagementAction( Request $request, KernelInterface $kernel )
    {
        //exit("tenancyManagementAction");
        $tenantRole = $this->getParameter('tenant_role');
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
        $tenantRole = $this->getParameter('tenant_role');
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
        $tenantRole = $this->getParameter('tenant_role');
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
        $tenantRole = $this->getParameter('tenant_role');
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
}