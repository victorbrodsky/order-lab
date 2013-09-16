<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
            FROM OlegOrderformBundle:StainList stain'
        );
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
            FROM OlegOrderformBundle:ProcedureList proc'
        );

        $empty = array("id"=>0,"text"=>"");
        $output = $query->getResult();
        array_unshift($output, $empty);

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
            FROM OlegOrderformBundle:OrganList proc'
        );

        $empty = array("id"=>0,"text"=>"");
        $output = $query->getResult();
        array_unshift($output, $empty);

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
    
}
