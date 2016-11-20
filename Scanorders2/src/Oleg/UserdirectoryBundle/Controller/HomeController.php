<?php

namespace Oleg\UserdirectoryBundle\Controller;


use Oleg\UserdirectoryBundle\Entity\SiteList;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Security\Util\UserSecurityUtil;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class HomeController extends Controller {

    public function mainCommonHomeAction() {
        return $this->render('OlegUserdirectoryBundle:Default:main-common-home.html.twig');
    }

    /**
     * @Route("/maintanencemode", name="main_maintenance")
     */
    public function maintanenceModeAction() {

        //exit('maint controller');

        $em = $this->getDoctrine()->getManager();
        $params = $roles = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( count($params) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        $param = $params[0];

        //$maintenanceLoginMsg = $param->getMaintenanceloginmsg();
        //$maintenance = $param->getMaintenance();
        //echo "maintenance=".$maintenance."<br>";

        return $this->render('OlegUserdirectoryBundle:Default:maintenance.html.twig',
            array(
                'param' => $param
            )
        );
    }

    /**
     * @Route("/under-construction", name="under_construction")
     */
    public function underConstructionAction() {
        return $this->render('OlegUserdirectoryBundle:Default:under_construction.html.twig');
    }



//    /**
//     * @Route("/admin/list-manager/", name="platformlistmanager-list")
//     */
//    public function listManagerAction() {
//        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            //exit('no access');
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }
//        return $this->getList($request);
//    }


    /**
     * @Route("/first-time-user-generation-init/", name="first-time-user-generation-init")
     */
    public function firstTimeUserGenerationAction() {
//        return $this->render('OlegUserdirectoryBundle:Default:under_construction.html.twig');

        //exit("firstTimeUserGenerationAction");

        $em = $this->getDoctrine()->getManager();

        $default_time_zone = null;
        $usernamePrefix = "local-user";
        //$username = "oli2002";
        //$user = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername( $username."_@_". $usernamePrefix);


        $userSecUtil = new UserSecurityUtil($em,null,null);
        $systemuser = $userSecUtil->findSystemUser();

        //$this->generateSitenameList($systemuser);

        if( !$systemuser ) {

            $userSecUtil = $this->container->get('user_security_utility');
            $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);

            $usetUtil = new UserUtil();
            $usetUtil->generateUsernameTypes($em);
            //$userkeytype = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findOneByAbbreviation("local-user");

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
            'call-log-book' => 'calllog'
        );


        //$username = $this->get('security.context')->getToken()->getUser();

        $count = 10;
        foreach( $elements as $name => $abbreviation ) {

            $entity = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByName($name);
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
