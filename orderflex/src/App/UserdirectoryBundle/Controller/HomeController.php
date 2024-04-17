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

namespace App\UserdirectoryBundle\Controller;


use App\UserdirectoryBundle\Entity\SiteList;
use App\UserdirectoryBundle\Entity\SiteParameters;
use App\UserdirectoryBundle\Entity\TenantList;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\LabelType;
use App\UserdirectoryBundle\Util\UserSecurityUtil;
use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Attribute\Template;


class HomeController extends OrderAbstractController {

    public function mainCommonHomeAction(Request $request) {

        //homepagemanager show a different multi-tenant home page
        $tenantManagerName = 'homepagemanager';
        $tenantRole = $this->getParameter('tenant_role');
        if( $tenantRole == $tenantManagerName ) {
            return $this->multiTenancyHomePage($request);
        }

        //$userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        $width = "300";
        $height = "80";

        $platformLogoPath = null;
        $platformLogos = $userSecUtil->getSiteSettingParameter('platformLogos');
        if( is_array($platformLogos) && count($platformLogos) > 0 ) {
            $platformLogo = $platformLogos->first();
            $platformLogoPath = $platformLogo->getAbsoluteUploadFullPath();
        }

//        return $this->render('AppUserdirectoryBundle/Default/main-common-home.html.twig',
//            array(
//                'platformLogo' => $platformLogoPath,
//                'width' => $width,
//                'height' => $height
//            )
//        );
        //path to twig is relative to templates/ folder
        //Replace ':' to '/'    sf3: 'AppUserdirectoryBundle/Default/main-common-home.html.twig'
        //                 sf4_flex: 'AppUserdirectoryBundle/Default/main-common-home.html.twig'
        //Move AppUserdirectoryBundle/Resources/views to templates AppUserdirectoryBundle
        return $this->render('AppUserdirectoryBundle/Default/main-common-home.html.twig',
            array(
                'platformLogo' => $platformLogoPath,
                'width' => $width,
                'height' => $height
            )
        );
    }

    //Header Image : [DropZone field allowing upload of 1 image]
    //Greeting Text : [free text form field, multi-line, accepts HTML, with default value: “Welcome to the View! The following organizations are hosted on this platform:”]
    //[List of hosted tenants, each one shown as a clickable link]
    //Main text [free text form field, multi-line, accepts HTML, with default value: “Please visit the site of the organization of interest to see the available applications.”]
    //Footer [free text form field, multi-line, accepts HTML, with default value: “[Home | <a href=”/about-us”>About Us</a> | Follow Us]”
    //
    //Add /about-us “About Us” (Multitenant Platform) page config and URL as well (only accessible on the Homepage Manager instance):
    //About Us Page Header: [DropZone field allowing upload of 1 image]
    //About Us Page Text: [free text form field, multi-line, accepts HTML, with default value: “This website hosts data for organizations using the Order platform.”]
    //About Us Page Footer: [free text form field, multi-line, accepts HTML, with default value: “[<a href=”/”>Home</a> | About Us | Follow Us]
    public function multiTenancyHomePage(Request $request) {

        $userTenantUtil = $this->container->get('user_tenant_utility');

        $title = "Multi-tenancy home page";

        $width = "300";
        $height = "80";

        $tenantManager = $userTenantUtil->getSingleTenantManager($createIfEmpty = true);
        $platformLogoPath = null;
        $platformLogos = $tenantManager->getLogos();
        echo "1 platformLogos=".count($platformLogos)."<br>";
        if( is_array($platformLogos) && count($platformLogos) > 0 ) {
            $platformLogo = $platformLogos->first();
            echo "2 platformLogos=".count($platformLogos)."<br>";
            echo "platformLogo=".$platformLogo."<br>";
            $platformLogoPath = $platformLogo->getAbsoluteUploadFullPath();
        }
        echo "platformLogoPath=$platformLogoPath<br>";

        $tenants = array();
        $tenantBaseUrlArr = array();
        $baseUrl = $request->getScheme() . '://' . $request->getHttpHost();
        $tenants = $userTenantUtil->getTenantsFromTenantManager();

        $tenantManagerName = 'tenantmanager';
        $tenantManagerUrl = null;
        foreach ($tenants as $tenant) {
            if( $tenant['name'] === $tenantManagerName ) {
                $tenantManagerUrl = $tenant['urlslug'];
                break;
            }
        }

        foreach ($tenants as $tenantArr) {
            //$tenant as array
            if($tenantArr) {
                $tenant = new TenantList();
                $tenant->setDatabaseHost($tenantArr['databasehost']);
                $tenant->setDatabaseName($tenantArr['databasename']);
                $tenant->setDatabaseUser($tenantArr['databaseuser']);
                $tenant->setDatabasePassword($tenantArr['databasepassword']);
                $tenant->setUrlslug($tenantArr['urlslug']);
                $tenant->setEnabled($tenantArr['enabled']);

                $url = $tenant->getUrlslug();
                echo "url=".$url."<br>";

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
        echo 'tenantBaseUrlArr count='.count($tenantBaseUrlArr)."<br>";

        $greetingText = $tenantManager->getGreeting();
        $mainText = $tenantManager->getMaintext();
        $footer = $tenantManager->getFooter();

        return $this->render('AppUserdirectoryBundle/MultiTenancy/multi-tenancy-home.html.twig',
            array(
                'title' => $title,
                'tenantBaseUrlArr' => $tenantBaseUrlArr,
                'platformLogo' => $platformLogoPath,
                'greetingText' => $greetingText,
                'mainText' => $mainText,
                'footer' => $footer,
                'width' => $width,
                'height' => $height
            )
        );
    }

    #[Route(path: '/maintanencemode', name: 'main_maintenance')]
    public function maintanenceModeAction() {

        //exit('maint controller');

        $em = $this->getDoctrine()->getManager();
        $params = $roles = $em->getRepository(SiteParameters::class)->findAll();

        if( count($params) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        $param = $params[0];

        //$maintenanceLoginMsg = $param->getMaintenanceloginmsg();
        //$maintenance = $param->getMaintenance();
        //echo "maintenance=".$maintenance."<br>";

        return $this->render('AppUserdirectoryBundle/Default/maintenance.html.twig',
            array(
                'param' => $param
            )
        );
    }

    #[Route(path: '/under-construction', name: 'under_construction')]
    public function underConstructionAction() {
        return $this->render('AppUserdirectoryBundle/Default/under_construction.html.twig');
    }


 
//    /**
    //     * @Route("/admin/list-manager/", name="platformlistmanager-list")
    //     */
    //    public function listManagerAction() {
    //        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
    //            //exit('no access');
    //            return $this->redirect( $this->generateUrl('employees-nopermission') );
    //        }
    //        return $this->getList($request);
    //    }
    /**
     * Not used: use http://localhost/order/directory/admin/first-time-login-generation-init/ for the first time user generation login
     */
    #[Route(path: '/first-time-user-generation-init/', name: 'first-time-user-generation-init')]
    public function firstTimeUserGenerationAction() {
        exit("not used");
//        return $this->render('AppUserdirectoryBundle/Default/under_construction.html.twig');

        //exit("firstTimeUserGenerationAction");

        $em = $this->getDoctrine()->getManager();

        $default_time_zone = null;
        $usernamePrefix = "local-user";
        //$username = "oli2002";
        //$user = $this->em->getRepository(User::class)->findOneByUsername( $username."_@_". $usernamePrefix);


        //$userSecUtil = new UserSecurityUtil($em,null);
        $userSecUtil = $this->container->get('user_security_utility');
        $systemuser = $userSecUtil->findSystemUser();

        //$this->generateSitenameList($systemuser);

        if( !$systemuser ) {

            //$usetUtil = new UserUtil();
            $userUtil = $this->container->get('user_utility');
            $userUtil->generateUsernameTypes();
            //$userkeytype = $em->getRepository('AppUserdirectoryBundle:UsernameType')->findOneByAbbreviation("local-user");

            $userSecUtil = $this->container->get('user_security_utility');
            $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);

            $systemuser = $userUtil->createSystemUser($userkeytype, $default_time_zone);
            $this->generateSitenameList($systemuser);

            //set unique username
            $usernameUnique = $systemuser->createUniqueUsername();
            $systemuser->setUsername($usernameUnique);
            $systemuser->setUsernameCanonical($usernameUnique);

            //$systemuser->setUsername("system_@_local-user");
            //$systemuser->setUsernameCanonical("system_@_local-user");

            //$encoder = $this->container->get('security.password_encoder');
            //$encoded = $encoder->encodePassword($systemuser, "systemuserpass");
            $authUtil = $this->container->get('authenticator_utility');
            $encoded = $authUtil->getEncodedPassword($systemuser, "systemuserpass");

            $systemuser->setPassword($encoded);
            $systemuser->setLocked(false);

            $em->persist($systemuser);
            $em->flush();

            exit("system user created");
        }

        if( !$systemuser->getPassword() ) {
            //$encoder = $this->container->get('security.password_encoder');
            //$encoded = $encoder->encodePassword($systemuser, "systemuserpass");
            $authUtil = $this->container->get('authenticator_utility');
            $encoded = $authUtil->getEncodedPassword($systemuser, "systemuserpass");
            $systemuser->setPassword($encoded);
            $em->persist($systemuser);
            $em->flush();
        }

        exit("system user is already existed");
    }
    public function generateSitenameList($systemuser) {

        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');

        $elements = array(
            'directory' => 'employees',
            'scan' => 'scan',
            'fellowship-applications' => 'fellapp',
            'residency-applications' => 'resapp',
            'deidentifier' => 'deidentifier',
            'time-away-request' => 'vacreq',
            'call-log-book' => 'calllog',
            'critical-result-notifications' => 'crn',
            'translational-research' => 'translationalresearch',
            'dashboards' => 'dashboard'
        );


        //$username = $this->getUser();

        $count = 10;
        foreach( $elements as $name => $abbreviation ) {

            $entity = $em->getRepository(SiteList::class)->findOneByName($name);
            if( $entity ) {
                continue;
            }

            $entity = new SiteList();
            $userSecUtil->setDefaultList($entity,$count,$systemuser,$name);

            $entity->setAbbreviation($abbreviation);

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }


}
