<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oleg\OrderformBundle\Helper\FormHelper;

//TODO: optimise by removing foreach loops

/**
 * OrderInfo controller.
 *
 * @Route("/util")
 */
class ScanUtilController extends Controller {
      
    /**
     * @Route("/stain", name="get-stain")
     * @Method("GET")
     */
    public function getStainsAction() {
        
        $em = $this->getDoctrine()->getManager();
        //$addwhere = "";

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        //echo "opt=".$opt."<br>";

//        if( $this->get('security.context')->isGranted('ROLE_SCANORDER_DIVISION_CHIEF') ||
//            $this->get('security.context')->isGranted('ROLE_SCANORDER_SERVICE_CHIEF')
//        ) {
//            $addwhere = " OR list.type = 'user-added' ";
//        }

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:StainList', 'list')
            ->select("list.id as id, list.name as text")
            //->select("list")
            //->where("list.type = 'default' OR list.creator = ".$user." ".$addwhere)
            ->groupBy("list.id")
            ->addGroupBy("list.orderinlist")
            ->addGroupBy("list.name")
            ->orderBy("list.orderinlist","ASC"); //ASC DESC

        if( $opt ) {
            $user = $this->get('security.context')->getToken()->getUser();
            $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);
        }

        //echo "query=".$query." ";

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
            ->from('OlegOrderformBundle:ProcedureList', 'list')
            ->select("list.id as id, list.name as text")
            //->where("list.creator = ".$user)
            ->orderBy("list.orderinlist","ASC");

        if( $opt ) {
            $user = $this->get('security.context')->getToken()->getUser();
            $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);
        }

        //echo "query=".$query." ";

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
            ->from('OlegOrderformBundle:OrganList', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        if( $opt ) {
            $user = $this->get('security.context')->getToken()->getUser();
            $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);
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

        $user = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('OlegOrderformBundle:RegionToScan')->findByType('default');

        //////////////////////////////////// 1) get all default list ////////////////////////////////////
        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:RegionToScan', 'list')
            ->select("list.name")
            ->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user)
            ->groupBy('list')
            ->orderBy("list.orderinlist","ASC");
        $entities = $query->getQuery()->getResult();
        //////////////////////////////////// END OF 1 ///////////////////////////////////////////


        //////////////// 2) create addwhere to does not select scanregion elements with the same name as in list names //////////////////////
        $addwhere = "";
        $count = 1;
        $parametersArr = array();
        foreach( $entities as $entity ) {
            $arr[] = $entity["name"];
            $parametersArr['text'.$count] = $entity["name"];
            $addwhere = $addwhere . "scan.scanregion != :text".$count;
            if( count($entities) > $count ) {
                $addwhere = $addwhere . " AND ";
            }
            $count++;
        }

        if( $addwhere != "" ) {
            $addwhere = " AND (" . $addwhere . ")";
        }

        //echo "addwhere=".$addwhere." \n ";
        //////////////////////////////////// END OF 2 ///////////////////////////////////////////

//        //add custom added values
//        //TODO: add custom values, added by ordering provider
//        $user = $this->get('security.context')->getToken()->getUser();
//        $entities = $this->getDoctrine()->getRepository('OlegOrderformBundle:Scan')->findByProvider($user);
//        foreach( $entities as $entity ) {
//            $arr[] = $entity->getScanregion();
//        }

        //////////////// 3) add custom added values by order id (if id is set) //////////////////////
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
        //////////////////////////////////// END OF 3 ///////////////////////////////////////////


        //////////////// 4) add custom added values from all my orders //////////////////////
        $parametersArr['user'] = $user;

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:OrderInfo', 'list')
            ->select("scan.scanregion")
            ->innerJoin("list.slide","slide")
            ->innerJoin("slide.scan","scan")
            ->innerJoin("scan.provider","provider")
            ->groupBy('scan')
            ->addGroupBy('scan.scanregion')
            ->where( "provider = :user ".$addwhere )
            ->setParameters( $parametersArr );

        //echo "query=".$query." \n ";

        $myOrders = $query->getQuery()->getResult();

        foreach( $myOrders as $scanreg ) {
            //echo $scanreg['scanregion']." => ";
            $arr[] = $scanreg['scanregion'];
        }
        //////////////////////////////////// END OF 4 ///////////////////////////////////////////
        
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

        $user = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('OlegOrderformBundle:SlideDelivery')->findByType('default');

        //////////////////////////////////// 1) get all default list ////////////////////////////////////
        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:SlideDelivery', 'list')
            ->select("list.name")
            ->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user)
            ->groupBy('list')
            ->orderBy("list.orderinlist","ASC");
        $entities = $query->getQuery()->getResult();
        //////////////////////////////////// END OF 1 ///////////////////////////////////////////


        //////////////// 2) create addwhere to does not select scanregion elements with the same name as in list names //////////////////////
        $addwhere = "";
        $count = 1;
        $parametersArr = array();
        foreach( $entities as $entity ) {
            $arr[] = $entity["name"];
            $parametersArr['text'.$count] = $entity["name"];
            $addwhere = $addwhere . "list.slideDelivery != :text".$count;
            if( count($entities) > $count ) {
                $addwhere = $addwhere . " AND ";
            }
            $count++;
        }

        if( $addwhere != "" ) {
            $addwhere = " AND (" . $addwhere . ")";
        }

        //echo "addwhere=".$addwhere." \n ";
        //////////////////////////////////// END OF 2 ///////////////////////////////////////////

        //////////////// 3) add custom added values by order id (if id is set) //////////////////////
        $request = $this->get('request');
        $id = trim( $request->get('opt') );
        if( $id ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);
            if( $orderinfo ) {
                $arr[] = $orderinfo->getSlideDelivery();
            }
        }
        //////////////////////////////////// END OF 3 ///////////////////////////////////////////

        //////////////// 4) add custom added values from all my orders //////////////////////
        $parametersArr['user'] = $user;

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:OrderInfo', 'list')
            ->select("list.slideDelivery")
            ->innerJoin("list.provider","provider")
            ->groupBy('list.slideDelivery')
            ->where( "provider = :user ".$addwhere )
            ->setParameters( $parametersArr );

        //echo "query=".$query." \n ";

        $myOrders = $query->getQuery()->getResult();

        foreach( $myOrders as $scanreg ) {
            //echo $scanreg['scanregion']." => ";
            $arr[] = $scanreg['slideDelivery'];
        }
        //////////////////////////////////// END OF 4 ///////////////////////////////////////////

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
    public function getReturnSlideAction(Request $request) {

        $arr = array();

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        //$entities = $em->getRepository('OlegOrderformBundle:ReturnSlideTo')->findByType('default');

        //////////////////////////////////// 1) get all default list ////////////////////////////////////
        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:ReturnSlideTo', 'list')
            ->select("list.name")
            ->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user)
            ->groupBy('list')
            ->orderBy("list.orderinlist","ASC");
        $entities = $query->getQuery()->getResult();
        //////////////////////////////////// END OF 1 ///////////////////////////////////////////

        //////////////// 2) create addwhere to does not select scanregion elements with the same name as in list names //////////////////////
        $addwhere = "";
        $count = 1;
        $parametersArr = array();
        foreach( $entities as $entity ) {
            $arr[] = $entity["name"];
            $parametersArr['text'.$count] = $entity["name"];
            $addwhere = $addwhere . "list.returnSlide != :text".$count;
            if( count($entities) > $count ) {
                $addwhere = $addwhere . " AND ";
            }
            $count++;
        }

        if( $addwhere != "" ) {
            $addwhere = " AND (" . $addwhere . ")";
        }

        //echo "addwhere=".$addwhere." \n ";
        //////////////////////////////////// END OF 2 ///////////////////////////////////////////

        //////////////// 3) add custom added values by order id (if id is set) //////////////////////
        $request = $this->get('request');
        $id = trim( $request->get('opt') );
        if( $id ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);
            if( $orderinfo ) {
                $arr[] = $orderinfo->getReturnSlide();
            }
        }
        //////////////////////////////////// END OF 3 ///////////////////////////////////////////

        //////////////// 4) add custom added values from all my orders //////////////////////
        $parametersArr['user'] = $user;

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:OrderInfo', 'list')
            ->select("list.returnSlide")
            ->innerJoin("list.provider","provider")
            ->groupBy('list.returnSlide')
            ->where( "provider = :user ".$addwhere )
            ->setParameters( $parametersArr );

        //echo "query=".$query." \n ";

        $myOrders = $query->getQuery()->getResult();

        foreach( $myOrders as $scanreg ) {
            //echo $scanreg['scanregion']." => ";
            $arr[] = $scanreg['returnSlide'];
        }
        //////////////////////////////////// END OF 4 ///////////////////////////////////////////

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

//    //TODO: test it according to new Service!
//    /**
//     * @Route("/userpathservice", name="get-userpathservice")
//     * @Method("POST")
//     */
//    public function getUserPathServiceAction() {
//
//        $output = array();
//
//        $em = $this->getDoctrine()->getManager();
//
//        $request = $this->get('request');
//        $username   = $request->get('username');
//        //echo "username=".$username."<br>";
//
//        $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername(trim($username));
//        //echo $user;
//
//        //$user = $em->getRepository('OlegUserdirectoryBundle:User')->find(15);
//        if( $user ) {
//            //echo "user found!";
//            $services = $user->getServices();
//            //echo "count=".count($services);
//
//            //$count=0;
//            foreach( $services as $service) {
//                $temp = array('id'=>$service->getId(), 'text'=>$service->getName());
//                $output[] = $temp;
//                //$count++;
//            }
//
//        } else {
//            //echo "no user found!";
//        }
//
//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode($output));
//        return $response;
//    }


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

        //echo "opt=".$opt."<br>";

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:AccessionType', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        if( $type == "single" ) {
            if( $opt ) {
                $query->where("list.type = :type OR list.type = :typetma OR ( list.type = 'user-added' AND list.creator = :user)");    //->setParameter('type', 'default')->setParameter('typetma', 'TMA');
                $query->setParameters( array('type' => 'default', 'typetma' => 'TMA', 'user' => $user) );
            }
        } else {
            if( $opt ) {
                $query->where("list.type = :type AND list.type != :typetma OR ( list.type = 'user-added' AND list.creator = :user)");   //->setParameter('type', 'default')->setParameter('typetma', 'TMA');
                $query->setParameters( array('type' => 'default', 'typetma' => 'TMA', 'user' => $user) );
            } else {
                $query->where('list.type != :type')->setParameter('type', 'TMA');
            }
        }

        //echo "query=".$query."<br>";

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/mrntype", name="get-mrntype")
     * @Method("GET")
     */
    public function getMrnTypeAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );
        $type = trim( $request->get('type') );

        //echo "opt=".$opt."<br>";

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:MrnType', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        if( $opt ) {
            $query->where("list.type = :type OR ( list.type = 'user-added' AND list.creator = :user)");
            $query->setParameters( array('type' => 'default', 'user' => $user) );
        }

        //echo "query=".$query."<br>";

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * @Route("/slidetype", name="get-slidetype")
     * @Method("GET")
     */
    public function getSlideTypesAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:SlideType', 'list')
            ->select("list.name as text")
            ->where("list.type='default'")
            ->orderBy("list.orderinlist","ASC");

        //echo "query=".$query."<br>";

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }



    /**
     * @Route("/projecttitle", name="get-projecttitle")
     * @Method("GET")
     */
    public function getProjectTitleAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:ProjectTitleList', 'list')
            ->select("list.name as id, list.name as text")
            //->where("list.type = 'default'")
            ->orderBy("list.orderinlist","ASC");

//        if( $opt ) {
            $user = $this->get('security.context')->getToken()->getUser();
            $query->where("list.type = :type OR ( list.type = 'user-added' AND list.creator = :user)");
            $query->setParameters( array('type' => 'default', 'user' => $user) );
//        }

        //echo "query=".$query."<br \>";

        $output = $query->getQuery()->getResult();

        //add old name. The name might be changed by admin, so check and add if not existed, the original name eneterd by a user when order was created
        if( $opt ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($opt);
            if( $orderinfo->getResearch() ) {
                $strEneterd = $orderinfo->getResearch()->getProjectTitleStr();
                $element = array('id'=>$strEneterd, 'text'=>$strEneterd);
                if( !$this->in_complex_array($element,$output) ) {
                    $output[] = $element;
                }
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/settitle", name="get-settitle")
     * @Method("GET")
     */
    public function getSetTitleAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') ); //projectTitle name
        $orderoid = trim( $request->get('orderoid') );
        //echo 'opt='.$opt.' => ';

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:SetTitleList', 'list')
            ->select("list.name as id, list.name as text")
            ->leftJoin("list.projectTitle","parent")
            ->where("parent.name = :pname AND list.type = :type")
            ->orderBy("list.orderinlist","ASC")
            ->setParameters( array(
                'pname' => $opt,
                'type' => 'default'
            ));

        //echo "query=".$query."<br>";
        $output = $query->getQuery()->getResult();

        //add old name. The name might be changed by admin, so check and add if not existed, the original name eneterd by a user when order was created
        if( $orderoid ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($orderoid);
            if( $orderinfo->getResearch() ) {
                $strEneterd = $orderinfo->getResearch()->getSetTitleStr();
                $element = array('id'=>$strEneterd, 'text'=>$strEneterd);
                if( !$this->in_complex_array($element,$output) ) {
                    $output[] = $element;
                }
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * @Route("/coursetitle", name="get-coursetitle")
     * @Method("GET")
     */
    public function getCourseTitleAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );
        //$type = trim( $request->get('type') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:CourseTitleList', 'list')
            ->select("list.name as id, list.name as text")
            ->where("list.type = 'default'")
            ->orderBy("list.orderinlist","ASC");

//        if( $opt ) {
            $user = $this->get('security.context')->getToken()->getUser();
            $query->where("list.type = :type OR ( list.type = 'user-added' AND list.creator = :user)");
            $query->setParameters( array('type' => 'default', 'user' => $user) );
//        }

        //echo "query=".$query."<br>";

        $output = $query->getQuery()->getResult();
        //$output = array();

        //add old name. The name might be changed by admin, so check and add if not existed, the original name eneterd by a user when order was created
        if( $opt ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($opt);
            if( $strEneterd = $orderinfo->getEducational() ) {
                $strEneterd = $orderinfo->getEducational()->getCourseTitleStr();
                $element = array('id'=>$strEneterd, 'text'=>$strEneterd);
                if( !$this->in_complex_array($element,$output) ) {
                    $output[] = $element;
                }
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/lessontitle", name="get-lessontitle")
     * @Method("GET")
     */
    public function getLessonTitleAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') ); //parent id: courseTitle id
        $orderoid = trim( $request->get('orderoid') );
        //echo 'opt='.$opt.' => ';

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:LessonTitleList', 'list')
            ->select("list.name as id, list.name as text")
            ->leftJoin("list.courseTitle","parent")
            ->where("parent.name = :pname AND list.type = :type")
            ->orderBy("list.orderinlist","ASC")
            ->setParameters( array(
                'pname' => $opt,
                'type' => 'default'
            ));

        //echo "query=".$query."<br>";

        $output = $query->getQuery()->getResult();

        //add old name. The name might be changed by admin, so check and add if not existed, the original name eneterd by a user when order was created
        if( $orderoid ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($orderoid);
            if( $orderinfo->getEducational() ) {
                $strEneterd = $orderinfo->getEducational()->getLessonTitleStr();
                $element = array('id'=>$strEneterd, 'text'=>$strEneterd);
                if( !$this->in_complex_array($element,$output) ) {
                    $output[] = $element;
                }
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/optionalusereducational", name="get-optionalusereducational")
     * @Route("/optionaluserresearch", name="get-optionaluserresearch")
     * @Method("GET")
     */
    public function getOptionalUserAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') ); //parent name: courseTitle name
        $routeName = $request->get('_route');

        if( $routeName == "get-optionalusereducational" ) {
            $role = "ROLE_SCANORDER_COURSE_DIRECTOR";
            $className = 'DirectorList';
            $pname = 'courses';
        }
        if( $routeName == "get-optionaluserresearch" ) {
            $role = "ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR";
            $className = 'PIList';
            $pname = 'projectTitles';
        }

        if(0) {
            echo "opt=".$opt." => ";
            $project = $this->getDoctrine()->getRepository('OlegOrderformBundle:CourseTitleList')->findOneById($opt);
            $pis = $project->getDirectors();
            echo "countpis=".count($pis)." => ";
            foreach( $project->getDirectors() as $pi ) {
                echo "pi name=".$pi->getName()." | ";
            }
        }

        //1) add PIList with parent name = $opt
        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:'.$className, 'list')
            ->select("list.name as id, list.name as text")
            ->leftJoin("list.".$pname,"parents")
            ->where("parents.name = :pname AND (list.type = :type OR list.type = :type2)")
            ->orderBy("list.orderinlist","ASC")
            ->setParameters( array(
                'pname' => $opt,
                'type' => 'default',
                'type2' => 'user-added'
            ));

        $output = $query->getQuery()->getResult();

        //var_dump($output);

        //2) add users with ROLE_SCANORDER_COURSE_DIRECTOR and ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR
        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:User', 'list')
            //->select("list.id as id, list.username as text")
            ->select("list")
            ->where("list.roles LIKE :role")
            ->orderBy("list.id","ASC")
            ->setParameter('role', '%"' . $role . '"%');

        $users = $query->getQuery()->getResult();

        foreach( $users as $user ) {
            $element = array('id'=>$user."", 'text'=>$user."");
            if( !$this->in_complex_array($user."",$output) ) {
                $output[] = $element;
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;

    }



//    /**
//     * @Route("/department", name="get-department")
//     * @Method("GET")
//     */
//    public function getDepartmentAction() {
//
//        $whereServicesList = "";
//
//        $em = $this->getDoctrine()->getManager();
//
//        $request = $this->get('request');
//        $opt = trim( $request->get('opt') );
//
//        $query = $em->createQueryBuilder()
//            ->from('OlegUserdirectoryBundle:Department', 'list')
//            ->select("list.id as id, list.name as text")
//            ->orderBy("list.orderinlist","ASC");
//
//        $user = $this->get('security.context')->getToken()->getUser();
//
//        $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);
//
//        $output = $query->getQuery()->getResult();
//
//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode($output));
//        return $response;
//    }

//    /**
//     * @Route("/institution", name="scan_get_institution")
//     * @Method("GET")
//     */
//    public function getInstitutionAction() {
//
//        $whereServicesList = "";
//
//        $em = $this->getDoctrine()->getManager();
//
//        $request = $this->get('request');
//        $opt = trim( $request->get('opt') );
//
//        $query = $em->createQueryBuilder()
//            ->from('OlegUserdirectoryBundle:Institution', 'list')
//            ->select("list.id as id, list.name as text")
//            ->orderBy("list.orderinlist","ASC");
//
//        $user = $this->get('security.context')->getToken()->getUser();
//
//        $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);
//
//        $output = $query->getQuery()->getResult();
//
//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode($output));
//        return $response;
//    }


    /**
     * @Route("/account", name="get-account")
     * @Method("GET")
     */
    public function getAccountAction() {

        $whereServicesList = "";

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Account', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        if( $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            //$query->where("list.type = 'user-added' AND list.creator = :user")->setParameter('user',$user);
        } else {
            $query->where("list.type = 'user-added' AND list.creator = :user")->setParameter('user',$user);
        }

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * @Route("/urgency", name="get-urgency")
     * @Method("GET")
     */
    public function getUrgencyAction() {


        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Urgency', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);

        $urgencies = $query->getQuery()->getResult();

        $output = array();
        foreach( $urgencies as $urgency ) {
            //echo "urgency=".$urgency->getName()." ";
            //var_dump($urgency);
            $element = array('id'=>$urgency['text']."", 'text'=>$urgency['text']."");
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    //TODO: test it according to new Service!
    /**
     * @Route("/scan-service", name="get-service")
     * @Method("GET")
     */
    public function getServiceAction() {

        $whereServicesList = "";

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Service', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        if( $opt == 'default' ) {
            if( $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ) {
                $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);
            } else {
                $query->where('list.type = :type ')->setParameter('type', 'default');
            }
        } else {
            //find user's services to include them in the list
            $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneById($opt);
            $getServices = $user->getServices();	//TODO: user's or allowed services?

            foreach( $getServices as $serviceId ) {
                $whereServicesList = $whereServicesList . " OR list.id=".$serviceId->getId();
            }
            //$query->where('list.type = :type OR list.creator = :user_id ' . $whereServicesList)->setParameter('type', 'default')->setParameter('user_id', $opt);
            $query->where("list.type = :type OR ( list.type = 'user-added' AND list.creator = :user_id) ".$whereServicesList)->setParameter('type', 'default')->setParameter('user_id', $opt);
        }

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }



    //search if $needle exists in array $products
    public function in_complex_array($needle,$products,$indexstr='text') {
        foreach( $products as $product ) {
            if ( $product[$indexstr] === $needle ) {
                return true;
            }
        }
        return false;
    }

}
