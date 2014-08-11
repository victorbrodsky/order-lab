<?php

namespace Oleg\OrderformBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class HomeController extends Controller {

    public function mainCommonHomeAction() {
        return $this->render('OlegOrderformBundle:Default:main-common-home.html.twig');
    }

    /**
     * @Route("/maintanencemode", name="maintenance_scanorder")
     */
    public function maintanenceModeAction() {

        //return $this->render('OlegOrderformBundle:Default:maintenance.html.twig');

        $maintenanceUntil = "";

        $em = $this->getDoctrine()->getManager();
        $params = $roles = $em->getRepository('OlegOrderformBundle:SiteParameters')->findAll();

        if( count($params) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        if( count($params) == 1 ) {
            $param = $params[0];
            $maintenanceLoginMsg = $param->getMaintenanceloginmsg();
        }



        return $this->render('OlegOrderformBundle:Default:maintenance.html.twig',
            array(
                'param'=>$param
            )
        );
    }

}
