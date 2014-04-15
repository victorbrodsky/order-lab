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
class UtilController extends Controller {
      
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

//        if( $this->get('security.context')->isGranted('ROLE_DIVISION_CHIEF') ||
//            $this->get('security.context')->isGranted('ROLE_SERVICE_CHIEF')
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
     * @Route("/pathservice", name="get-pathservice")
     * @Method("GET")
     */
    public function getPathServiceAction() {

        $whereServicesList = "";

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:PathServiceList', 'list')
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
            //find user's pathservices to include them in the list
            $user = $em->getRepository('OlegOrderformBundle:User')->findOneById($opt);
            $getPathologyServices = $user->getPathologyServices();

            foreach( $getPathologyServices as $serviceId ) {
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
     * @Route("/optionalusereducational", name="get-optionalusereducational")
     * @Route("/optionaluserresearch", name="get-optionaluserresearch")
     * @Method("GET")
     */
    public function getOptionalUserAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') ); //current user id

        $routeName = $request->get('_route');

        $where = "";
        if( $routeName == "get-optionalusereducational" ) {
            $role = "ROLE_COURSE_DIRECTOR";
        }
        if( $routeName == "get-optionaluserresearch" ) {
            $role = "ROLE_PRINCIPAL_INVESTIGATOR";
        }

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:User', 'list')
            //->select("list.id as id, list.username as text")
            ->select("list")
            ->where("list.roles LIKE :role")
            ->orderBy("list.id","ASC")
            ->setParameter('role', '%"' . $role . '"%');

        $users = $query->getQuery()->getResult();

        $output = array();
        foreach( $users as $user ) {
            $element = array('id'=>$user."", 'text'=>$user."");
            $output[] = $element;
        }

        //attach this user course directors and principal investigators string from educationa and research entities
        if( !$opt || $opt == "" ) {
            $opt = $this->get('security.context')->getToken()->getUser()->getId();
        }

        //$orderinfos = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findByProvider($opt);

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:OrderInfo', 'orderinfo')
            ->innerJoin("orderinfo.provider", "provider")
            ->select("orderinfo")
            ->where("provider.id=:userid")
            ->setParameter("userid",$opt);

        $orderinfos = $query->getQuery()->getResult();

        //echo "order count=".count($orderinfos)."<br>";

        foreach( $orderinfos as $orderinfo ) {

            if( $orderinfo->getEducational() ) {
                $dirstr = $orderinfo->getEducational()->getDirectorstr();
                if( $dirstr && $dirstr != "" ) {
                    $element = array('id'=>$dirstr, 'text'=>$dirstr);
                    if( !$this->in_complex_array($dirstr, $output) ) {
                        $output[] = $element;
                    }
                }
            }


            if( $orderinfo->getResearch() ) {
                $princstr = $orderinfo->getResearch()->getPrincipalstr();
                if( $princstr && $princstr != "" ) {
                    $element = array('id'=>$princstr, 'text'=>$princstr);
                    if( !$this->in_complex_array($princstr, $output) ) {
                        $output[] = $element;
                    }
                }
            }

        }

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
//        $type = trim( $request->get('type') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:ProjectTitleList', 'list')
            ->select("list.id as id, list.name as text")
            //->where("list.type = 'default'")
            ->orderBy("list.orderinlist","ASC");

        if( $opt ) {
            $user = $this->get('security.context')->getToken()->getUser();
            $query->where("list.type = :type OR ( list.type = 'user-added' AND list.creator = :user)");
            $query->setParameters( array('type' => 'default', 'user' => $user) );
        }

        //echo "query=".$query."<br \>";

        $output = $query->getQuery()->getResult();
        //$output = array();

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
        $opt = trim( $request->get('opt') ); //projectTitle id

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:SetTitleList', 'list')
            ->select("list.id as id, list.name as text")
            ->leftJoin("list.projectTitle","parent")
            ->where("parent.id = :pid AND list.type = :type")
            ->orderBy("list.orderinlist","ASC")
            ->setParameters( array(
                'pid' => $opt,
                'type' => 'default'
            ));

        //echo "query=".$query."<br>";
        $output = $query->getQuery()->getResult();

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
            ->select("list.id as id, list.name as text")
            ->where("list.type = 'default'")
            ->orderBy("list.orderinlist","ASC");

        if( $opt ) {
            $user = $this->get('security.context')->getToken()->getUser();
            $query->where("list.type = :type OR ( list.type = 'user-added' AND list.creator = :user)");
            $query->setParameters( array('type' => 'default', 'user' => $user) );
        }

        //echo "query=".$query."<br>";

        $output = $query->getQuery()->getResult();
        //$output = array();

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

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:LessonTitleList', 'list')
            ->select("list.id as id, list.name as text")
            ->leftJoin("list.courseTitle","parent")
            ->where("parent.id = :pid AND list.type = :type")
            ->orderBy("list.orderinlist","ASC")
            ->setParameters( array(
                'pid' => $opt,
                'type' => 'default'
            ));

        //echo "query=".$query."<br>";

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }



    //search if $needle exists in array $products
    public function in_complex_array($needle,$products) {
        foreach( $products as $product ) {
            if ( $product['id'] === $needle ) {
                return true;
            }
        }
        return false;
    }

}
