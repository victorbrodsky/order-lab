<?php

namespace Oleg\UserdirectoryBundle\FellAppController;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


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

        echo "fellapp home <br>";

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


    /**
     * Show home page
     *
     * @Route("/", name="fellapp_import")
     */
    public function importAction() {

        echo "fellapp import <br>";


        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    

}
