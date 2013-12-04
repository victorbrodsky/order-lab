<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oleg\OrderformBundle\Helper\FormHelper;

/**
 * OrderInfo controller.
 *
 * @Route("/util")
 */
class UtilController extends Controller {
      
    /**
     * @Route("/stain", name="get-stain")
     * @Method("GET")
     */
    public function getStainsAction() {
        
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT stain.id as id, stain.name as text
            FROM OlegOrderformBundle:StainList stain WHERE stain.type = :type'
        )->setParameter('type', 'default');

        $output = $query->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/staintype", name="get-staintype")
     * @Method("GET")
     */
    public function getStainTypeAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT stain.id as id, stain.name as text
            FROM OlegOrderformBundle:StainList stain WHERE stain.type = :type'
        )->setParameter('type', 'default');

        $output = $query->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     * @Route("/procedure", name="get-procedure")
     * @Method("GET")
     */
    public function getProcedureAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT proc.id as id, proc.name as text
            FROM OlegOrderformBundle:ProcedureList proc WHERE proc.type = :type'
        )->setParameter('type', 'default');

        //$empty = array("id"=>0,"text"=>"");
        $output = $query->getResult();
        //array_unshift($output, $empty);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }
    
    /**
     * @Route("/organ", name="get-organ")
     * @Method("GET")
     */
    public function getOrgansAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT proc.id as id, proc.name as text
            FROM OlegOrderformBundle:OrganList proc WHERE proc.type = :type'
        )->setParameter('type', 'default');

        //$empty = array("id"=>0,"text"=>"");
        $output = $query->getResult();
        //array_unshift($output, $empty);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/pathservice", name="get-pathservice")
     * @Method("GET")
     */
    public function getPathServiceAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT proc.id as id, proc.name as text
            FROM OlegOrderformBundle:PathServiceList proc WHERE proc.type = :type'
        )->setParameter('type', 'default');

        //$empty = array("id"=>0,"text"=>"");
        $output = $query->getResult();
        //array_unshift($output, $empty);

        //echo "count=".count($output)."<br>";
        //print_r($output);

//        $res = array();
//        foreach( $output as $out ) {
//            echo $out['text'];
////            if( trim($out['text']) == trim("Gynecologic Pathology / Perinatal Pathology / Autopsy") ) {
////                $res[] = array("id"=>$out['id'],"text"=>$out['text'], "selected"=>true);
////            } else {
////                $res[] = array("id"=>$out['id'],"text"=>$out['text']);
////            }
//            array_push($res,array("id"=>$out['id'],"text"=>$out['text']));
//        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     * @Route("/scanregion", name="get-scanregion")
     * @Method("GET")
     */
    public function getScanRegionAction() {

//        $em = $this->getDoctrine()->getManager();
//
//        $query = $em->createQuery(
//            'SELECT proc.id as id, proc.name as text
//            FROM OlegOrderformBundle:ProcedureList proc'
//        );
//        $output = $query->getResult();
        
        $formHelper = new FormHelper();
        $arr = $formHelper->getScanRegion();
        
        $output = array();
        
        $count = 0;
        foreach( $arr as $region ) {
            $element = array('id'=>$count, 'text'=>$region); 
            $output[] = $element;          
            $count++;
        }
        

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }
    
    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     * @Route("/delivery", name="get-slidedelivery")
     * @Method("GET")
     */
    public function getSlideDeliveryAction() {

//        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
//            return $this->render('OlegOrderformBundle:Security:login.html.twig');
//        }

        $formHelper = new FormHelper();
        $arr = $formHelper->getSlideDelivery();
        
        $output = array();
        
        $count = 0;
        foreach( $arr as $region ) {
            $element = array('id'=>$count, 'text'=>$region); 
            $output[] = $element;          
            $count++;
        }
        
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     * @Route("/return", name="get-returnslide")
     * @Method("GET")
     */
    public function getReturnSlideAction() {

//        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
//            return $this->render('OlegOrderformBundle:Security:login.html.twig');
//        }

        $formHelper = new FormHelper();
        $arr = $formHelper->getReturnSlide();
        
        $output = array();
        
        $count = 0;
        foreach( $arr as $region ) {
            $element = array('id'=>$count, 'text'=>$region); 
            $output[] = $element;          
            $count++;
        }
        
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * @Route("/userpathservice", name="get-userpathservice")
     * @Method("POST")
     */
    public function getUserPathServiceAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $output = array();

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $username   = $request->get('username');
        //echo "username=".$username."<br>";

        $user = $em->getRepository('OlegOrderformBundle:User')->findOneByUsername(trim($username));
        //echo $user;

        //$user = $em->getRepository('OlegOrderformBundle:User')->find(15);
        if( $user ) {
            //echo "user found!";
            $services = $user->getPathologyServices();
            //echo "count=".count($services);

            //$count=0;
            foreach( $services as $service) {
                $temp = array('id'=>$service->getId(), 'text'=>$service->getName());
                $output[] = $temp;
                //$count++;
            }

        } else {
            //echo "no user found!";
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * @Route("/partname", name="get-partname")
     * @Method("GET")
     */
    public function getPartnameAction() {

        $formHelper = new FormHelper();
        $arr = $formHelper->getPart();
        
        $output = array();
        
        $count = 0;
        foreach( $arr as $var ) {
            $element = array('id'=>$var."", 'text'=>$var.""); 
            $output[] = $element;          
            $count++;
        }
        
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/blockname", name="get-blockname")
     * @Method("GET")
     */
    public function getBlocknameAction() {

        $formHelper = new FormHelper();
        $arr = $formHelper->getBlock();
        
        $output = array();
        
        $count = 0;
        foreach( $arr as $var ) {
            $element = array('id'=>$var."", 'text'=>$var.""); 
            $output[] = $element;          
            $count++;
        }
        
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }
  
}
