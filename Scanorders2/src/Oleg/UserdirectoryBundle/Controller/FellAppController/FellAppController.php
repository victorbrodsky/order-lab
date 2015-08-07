<?php

namespace Oleg\UserdirectoryBundle\Controller\FellAppController;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

///**
// * @Route("/fellowship-applications")
// */
class FellAppController extends Controller {

    /**
     * Show home page
     *
     * @Route("/", name="fellapp_home")
     * @Template("OlegUserdirectoryBundle:FellApp:home.html.twig")
     */
    public function indexAction() {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        $em = $this->getDoctrine()->getManager();

        //$fellApps = $em->getRepository('OlegUserdirectoryBundle:FellowshipApplication')->findAll();
        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:FellowshipApplication');
        $dql =  $repository->createQueryBuilder("ent");
        $dql->select('ent');
        $dql->groupBy('ent');
        //$dql->leftJoin("ent.creator", "creator");

        $limit = 100;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $fellApps = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );


        return array(
            'entities' => $fellApps,
        );
    }



//    /**
//     * @Route("/fellowship-applications/login", name="fellapp_login")
//     *
//     * @Method("GET")
//     * @Template()
//     */
//    public function loginAction( Request $request ) {
//
//        $routename = $request->get('_route');
//        echo "routename=".$routename."<br>";
//
//        if( $routename == "employees_login" ) {
//            $sitename = $this->container->getParameter('employees.sitename');
//        }
//        if( $routename == "fellapp_login" ) {
//            $sitename = $this->container->getParameter('fellapp.sitename');
//        }
//
//        //$sitename = $this->container->getParameter('employees.sitename');
//        $formArr = $this->loginPage($sitename);
//
//        if( $formArr == null ) {
//            return $this->redirect( $this->generateUrl('main_common_home') );
//            //return $this->redirect( $this->generateUrl($sitename.'_home') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//        $usernametypes = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findBy(
//            array(
//                'type' => array('default', 'user-added'),
//                'abbreviation' => array('wcmc-cwid')
//            ),
//            array('orderinlist' => 'ASC')
//        );
//
//        if( count($usernametypes) == 0 ) {
//            $usernametypes = array();
//            $option = array('abbreviation'=>'wcmc-cwid', 'name'=>'WCMC CWID');
//            $usernametypes[] = $option;
//        }
//
//        $formArr['usernametypes'] = $usernametypes;
//
//        return $this->render(
//            'OlegUserdirectoryBundle:Security:login.html.twig',
//            $formArr
//        );
//    }

}
