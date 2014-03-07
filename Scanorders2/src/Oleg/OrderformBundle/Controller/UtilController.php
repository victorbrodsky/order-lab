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

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        //echo "opt=".$opt."<br>";

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:StainList', 'stain')
            ->select("stain.id as id, stain.name as text")
            ->orderBy("stain.orderinlist","ASC"); //ASC DESC

        if( $opt ) {
            $query->where('stain.type = :type')->setParameter('type', 'default');
        }

        $output = $query->getQuery()->getResult();

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

//        $query = $em->createQuery(
//            'SELECT proc.id as id, proc.name as text
//            FROM OlegOrderformBundle:ProcedureList proc WHERE proc.type = :type'
//        )->setParameter('type', 'default');
//
//        //$empty = array("id"=>0,"text"=>"");
//        $output = $query->getResult();
//        //array_unshift($output, $empty);

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:ProcedureList', 'e')
            ->select("e.id as id, e.name as text")
            ->orderBy("e.orderinlist","ASC");

        if( $opt ) {
            $query->where('e.type = :type')->setParameter('type', 'default');
        }

        $output = $query->getQuery()->getResult();

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

//        $query = $em->createQuery(
//            'SELECT proc.id as id, proc.name as text
//            FROM OlegOrderformBundle:OrganList proc WHERE proc.type = :type'
//        )->setParameter('type', 'default');
//
//        //$empty = array("id"=>0,"text"=>"");
//        $output = $query->getResult();
//        //array_unshift($output, $empty);

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:OrganList', 'e')
            ->select("e.id as id, e.name as text")
            ->orderBy("e.orderinlist","ASC");

        if( $opt ) {
            $query->where('e.type = :type')->setParameter('type', 'default');
        }

        $output = $query->getQuery()->getResult();

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

        $whereServicesList = "";

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:PathServiceList', 'e')
            ->select("e.id as id, e.name as text")
            ->orderBy("e.orderinlist","ASC");

        if( $opt == 'default' ) {
            $query->where('e.type = :type')->setParameter('type', 'default');
        } else {
            //find user's pathservices to include them in the list
            $user = $em->getRepository('OlegOrderformBundle:User')->findOneById($opt);
            $getPathologyServices = $user->getPathologyServices();

            foreach( $getPathologyServices as $serviceId ) {
                $whereServicesList = $whereServicesList . " OR e.id=".$serviceId->getId();
            }
            $query->where('e.type = :type OR e.creator = :user_id ' . $whereServicesList)->setParameter('type', 'default')->setParameter('user_id', $opt);
        }

        $output = $query->getQuery()->getResult();

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
//        $query = $em->createQuery(
//            'SELECT obj.name FROM OlegOrderformBundle:RegionToScan obj'
//        );
//        $res = $query->getResult();

        $arr = array();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:RegionToScan')->findAll();

        foreach( $entities as $entity ) {
            $arr[] = $entity."";
        }

//        //add custom added values
//        //TODO: add custom values, added by ordering provider
//        $user = $this->get('security.context')->getToken()->getUser();
//        $entities = $this->getDoctrine()->getRepository('OlegOrderformBundle:Scan')->findByProvider($user);
//        foreach( $entities as $entity ) {
//            $arr[] = $entity->getScanregion();
//        }

        //add custom added values by order id
        $request = $this->get('request');
        $id = trim( $request->get('opt') );
        if( $id ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);
            if( $orderinfo ) {
                $slides = $orderinfo->getSlide();
                foreach( $slides as $slide ) {
                    $arr[] = $slide->getScan()->first()->getScanregion();
                }
            }
        }
        
        $output = array();
        
        //$count = 0;
        foreach( $arr as $region ) {
            $element = array('id'=>$region, 'text'=>$region);
            $output[] = $element;          
            //$count++;
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

        $arr = array();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:SlideDelivery')->findAll();

        foreach( $entities as $entity ) {
            $arr[] = $entity."";
        }

        //add custom added values by order id
        $request = $this->get('request');
        $id = trim( $request->get('opt') );
        if( $id ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);
            if( $orderinfo ) {
                $arr[] = $orderinfo->getSlideDelivery();
            }
        }

        $output = array();
        
        //$count = 0;
        foreach( $arr as $region ) {
            $element = array('id'=>$region, 'text'=>$region);
            $output[] = $element;          
            //$count++;
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

        $arr = array();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:ReturnSlideTo')->findAll();

        foreach( $entities as $entity ) {
            $arr[] = $entity."";
        }

        //add custom added values by order id
        $request = $this->get('request');
        $id = trim( $request->get('opt') );
        if( $id ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);
            if( $orderinfo ) {
                $arr[] = $orderinfo->getReturnSlide();
            }
        }

        $output = array();
        
        //$count = 0;
        foreach( $arr as $region ) {
            $element = array('id'=>$region, 'text'=>$region);
            $output[] = $element;          
            //$count++;
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

        //add custom added values by order id
        $request = $this->get('request');
        $id = trim( $request->get('opt') );
        if( $id ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);
            if( $orderinfo ) {
                $parts = $orderinfo->getPart();
                foreach( $parts as $part ) {
                    foreach( $part->getPartname() as $partname ) {
                        $arr[] = $partname."";
                    }
                }
            }
        }

        $output = array();

        foreach( $arr as $var ) {
            $element = array('id'=>$var."", 'text'=>$var.""); 
            $output[] = $element;
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

        //add custom added values by order id
        $request = $this->get('request');
        $id = trim( $request->get('opt') );
        if( $id ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);
            if( $orderinfo ) {
                $blocks = $orderinfo->getBlock();
                foreach( $blocks as $block ) {
                    foreach( $block->getBlockname() as $blockname ) {
                        $arr[] = $blockname."";
                    }
                }
            }
        }

        $output = array();

        foreach( $arr as $var ) {
            $element = array('id'=>$var."", 'text'=>$var.""); 
            $output[] = $element;
        }
        
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/accessiontype", name="get-accessiontype")
     * @Method("GET")
     */
    public function getAccessionTypeAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );
        $type = trim( $request->get('type') );

//        $defSelect = "";
//        if( $type == "multi" ) {
//            $defSelect = "type.type = 'default'";
//        }

        //echo "opt=".$opt."<br>";

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:AccessionType', 'type')
            ->select("type.id as id, type.name as text")
            ->orderBy("type.orderinlist","ASC");
            //->where($defSelect);

//        if( $opt ) {
//            $query->where('type.type = :type')->setParameter('type', 'default');
//        }

        if( $type == "single" ) {
            if( $opt ) {
                $query->where('type.type = :type OR type.type = :typetma');    //->setParameter('type', 'default')->setParameter('typetma', 'TMA');
                $query->setParameters(array('type' => 'default', 'typetma' => 'TMA'));
            }
        } else {
            if( $opt ) {
                $query->where('type.type = :type AND type.type != :typetma');   //->setParameter('type', 'default')->setParameter('typetma', 'TMA');
                $query->setParameters(array('type' => 'default', 'typetma' => 'TMA'));
            } else {
                $query->where('type.type != :type')->setParameter('type', 'TMA');
            }
        }

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


}
