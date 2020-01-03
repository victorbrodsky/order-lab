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
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\LabelType;
use App\UserdirectoryBundle\Security\Util\UserSecurityUtil;
use App\UserdirectoryBundle\Util\UserUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class HomeController extends Controller {

    public function mainCommonHomeAction() {

        $userSecUtil = $this->get('user_security_utility');

        $width = "300";
        $height = "80";

        $platformLogoPath = null;
        $platformLogos = $userSecUtil->getSiteSettingParameter('platformLogos');
        if( count($platformLogos) > 0 ) {
            $platformLogo = $platformLogos->first();
            $platformLogoPath = $platformLogo->getAbsoluteUploadFullPath();
        }

        return $this->render('AppUserdirectoryBundle:Default:main-common-home.html.twig',
            array(
                'platformLogo' => $platformLogoPath,
                'width' => $width,
                'height' => $height
            )
        );
    }

    /**
     * @Route("/maintanencemode", name="main_maintenance")
     */
    public function maintanenceModeAction() {

        //exit('maint controller');

        $em = $this->getDoctrine()->getManager();
        $params = $roles = $em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();

        if( count($params) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        $param = $params[0];

        //$maintenanceLoginMsg = $param->getMaintenanceloginmsg();
        //$maintenance = $param->getMaintenance();
        //echo "maintenance=".$maintenance."<br>";

        return $this->render('AppUserdirectoryBundle:Default:maintenance.html.twig',
            array(
                'param' => $param
            )
        );
    }

    /**
     * @Route("/under-construction", name="under_construction")
     */
    public function underConstructionAction() {
        return $this->render('AppUserdirectoryBundle:Default:under_construction.html.twig');
    }


 
//    /**
//     * @Route("/admin/list-manager/", name="platformlistmanager-list")
//     */
//    public function listManagerAction() {
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            //exit('no access');
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }
//        return $this->getList($request);
//    }


    /**
     * Not used: use http://localhost/order/directory/admin/first-time-login-generation-init/ for the first time user generation login
     * @Route("/first-time-user-generation-init/", name="first-time-user-generation-init")
     */
    public function firstTimeUserGenerationAction() {
        exit("not used");
//        return $this->render('AppUserdirectoryBundle:Default:under_construction.html.twig');

        //exit("firstTimeUserGenerationAction");

        $em = $this->getDoctrine()->getManager();

        $default_time_zone = null;
        $usernamePrefix = "local-user";
        //$username = "oli2002";
        //$user = $this->em->getRepository('AppUserdirectoryBundle:User')->findOneByUsername( $username."_@_". $usernamePrefix);


        $userSecUtil = new UserSecurityUtil($em,null,null,null);
        $systemuser = $userSecUtil->findSystemUser();

        //$this->generateSitenameList($systemuser);

        if( !$systemuser ) {

            $usetUtil = new UserUtil();
            $usetUtil->generateUsernameTypes($em);
            //$userkeytype = $em->getRepository('AppUserdirectoryBundle:UsernameType')->findOneByAbbreviation("local-user");

            $userSecUtil = $this->container->get('user_security_utility');
            $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);

            $systemuser = $usetUtil->createSystemUser($em, $userkeytype, $default_time_zone);
            $this->generateSitenameList($systemuser);

            //set unique username
            $usernameUnique = $systemuser->createUniqueUsername();
            $systemuser->setUsername($usernameUnique);
            $systemuser->setUsernameCanonical($usernameUnique);

            //$systemuser->setUsername("system_@_local-user");
            //$systemuser->setUsernameCanonical("system_@_local-user");

            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($systemuser, "systemuserpass");

            $systemuser->setPassword($encoded);
            $systemuser->setLocked(false);

            $em->persist($systemuser);
            $em->flush();

            exit("system user created");
        }

        if( !$systemuser->getPassword() ) {
            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($systemuser, "systemuserpass");
            $systemuser->setPassword($encoded);
            $em->persist($systemuser);
            $em->flush();
        }

        exit("system user is already existed");
    }
    public function generateSitenameList($systemuser) {

        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->get('user_security_utility');

        $elements = array(
            'directory' => 'employees',
            'scan' => 'scan',
            'fellowship-applications' => 'fellapp',
            'deidentifier' => 'deidentifier',
            'vacation-request' => 'vacreq',
            'call-log-book' => 'calllog',
            'translational-research' => 'translationalresearch'
        );


        //$username = $this->get('security.token_storage')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name => $abbreviation ) {

            $entity = $em->getRepository('AppUserdirectoryBundle:SiteList')->findOneByName($name);
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
