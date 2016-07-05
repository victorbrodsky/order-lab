<?php

namespace Oleg\UserdirectoryBundle\Controller;


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

}
