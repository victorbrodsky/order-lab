<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Oleg\OrderformBundle\Controller;

use Oleg\UserdirectoryBundle\Controller\UtilController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oleg\OrderformBundle\Helper\FormHelper;

//TODO: optimise by removing foreach loops

/**
 * Message controller.
 *
 * @Route("/util")
 */
class ScanUtilController extends UtilController {

    /**
     * @Route("/common/generic/{name}", name="scan_get_generic_select2")
     * @Method("GET")
     */
    public function getGenericAction( Request $request, $name ) {

        return $this->getGenericList($request,$name);
    }

    public function getClassBundleByName($name) {

        $bundleName = "OrderformBundle";

        switch( $name ) {

            case "parttitle":
                $className = "ParttitleList";
                break;
            case "labtesttype":
                $className = "LabTestType";
                break;
            case "amendmentReason":
                $className = "AmendmentReasonList";
                break;
            case "embedderinstruction":
                $className = "EmbedderInstructionList";
                break;

            default:
                $className = null;
        }

        $res = array(
            'className' => $className,
            'bundleName' => $bundleName
        );

        return $res;
    }





////////////////// we can convert almost all functions below to use getGenericAction method by using js getComboboxGeneric(null,'embedderinstruction',_embedderinstruction,false,'','scan');

    /**
     * @Route("/stain", name="get-stain")
     * @Method("GET")
     */
    public function getStainsAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        //$addwhere = "";

        $opt = trim( $request->get('opt') );

        //echo "opt=".$opt."<br>";

//        if( $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_DIVISION_CHIEF') ||
//            $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SERVICE_CHIEF')
//        ) {
//            $addwhere = " OR list.type = 'user-added' ";
//        }

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:StainList', 'list')
            ->select("list.id as id, list.fulltitle as text")
            ->leftJoin("list.original","original")
            ->where("original.id IS NULL")
            ->groupBy("list")
//            ->groupBy("list.id")
//            ->addGroupBy("list.orderinlist")
//            ->addGroupBy("list.fulltitle")
            ->orderBy("list.orderinlist","ASC"); //ASC DESC

        if( $opt ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $query->andWhere("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);
        }

        //echo "query=".$query." ";

        //$output = $query->getQuery()->getResult('StainHydrator');
        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * Displays a form to create a new Message + Scan entities.
     * @Route("/procedure", name="get-procedure")
     * @Method("GET")
     */
    public function getProcedureAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

//        $query = $em->createQuery(
//            'SELECT proc.id as id, proc.name as text
//            FROM OlegOrderformBundle:ProcedureList proc WHERE proc.type = :type'
//        )->setParameter('type', 'default');
//
//        //$empty = array("id"=>0,"text"=>"");
//        $output = $query->getResult();
//        //array_unshift($output, $empty);

        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:ProcedureList', 'list')
            ->select("list.id as id, list.name as text")
            //->where("list.creator = ".$user)
            ->orderBy("list.orderinlist","ASC");

        if( $opt ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
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
    public function getOrgansAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

//        $query = $em->createQuery(
//            'SELECT proc.id as id, proc.name as text
//            FROM OlegOrderformBundle:OrganList proc WHERE proc.type = :type'
//        )->setParameter('type', 'default');
//
//        //$empty = array("id"=>0,"text"=>"");
//        $output = $query->getResult();
//        //array_unshift($output, $empty);

        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:OrganList', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        if( $opt ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);
        }

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * Displays a form to create a new Message + Scan entities.
     * @Route("/scanregion", name="get-scanregion")
     * @Method("GET")
     */
    public function getScanRegionAction(Request $request) {

//        $em = $this->getDoctrine()->getManager();
//        $query = $em->createQuery(
//            'SELECT obj.name FROM OlegOrderformBundle:RegionToScan obj'
//        );
//        $res = $query->getResult();

        $arr = array();

        $user = $this->get('security.token_storage')->getToken()->getUser();

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
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $entities = $this->getDoctrine()->getRepository('OlegOrderformBundle:Imaging')->findByProvider($user);
//        foreach( $entities as $entity ) {
//            $arr[] = $entity->getScanregion();
//        }

        //////////////// 3) add custom added values by order id (if id is set) //////////////////////
        $id = trim( $request->get('opt') );

        if( $id && $id != "undefined" ) {
            $message = $this->getDoctrine()->getRepository('OlegOrderformBundle:Message')->findOneByOid($id);
            if( $message ) {
                $slides = $message->getSlide();
                foreach( $slides as $slide ) {
                    $arr[] = $slide->getScan()->first()->getScanregion();
                }
            }
        }
        //////////////////////////////////// END OF 3 ///////////////////////////////////////////


        //////////////// 4) add custom added values from all my orders //////////////////////
        $parametersArr['user'] = $user;

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Message', 'list')
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
     * Displays a form to create a new Message + Scan entities.
     * @Route("/delivery", name="get-orderdelivery")
     * @Method("GET")
     */
    public function getOrderDeliveryAction(Request $request) {

        $arr = array();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('OlegOrderformBundle:OrderDelivery')->findByType('default');

        //////////////////////////////////// 1) get all default list ////////////////////////////////////
        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:OrderDelivery', 'list')
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
            $addwhere = $addwhere . "scanorder.delivery != :text".$count;
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
        $id = trim( $request->get('opt') );

        if( $id && $id != "undefined" ) {
            $message = $this->getDoctrine()->getRepository('OlegOrderformBundle:Message')->findOneByOid($id);
            if( $message ) {
                $arr[] = $message->getScanorder()->getDelivery();
            }
        }
        //////////////////////////////////// END OF 3 ///////////////////////////////////////////

        //////////////// 4) add custom added values from all my orders //////////////////////
        $parametersArr['user'] = $user;

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Message', 'list')
            ->select("scanorder.delivery")
            ->innerJoin("list.provider","provider")
            ->innerJoin("list.scanorder","scanorder")
            ->groupBy('scanorder.delivery')
            ->where( "provider = :user ".$addwhere )
            ->setParameters( $parametersArr );

        //echo "query=".$query." \n ";

        $myOrders = $query->getQuery()->getResult();

        foreach( $myOrders as $scanreg ) {
            //echo $scanreg['scanregion']." => ";
            $arr[] = $scanreg['delivery'];
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
     * @Route("/partname", name="get-partname")
     * @Method("GET")
     */
    public function getPartnameAction(Request $request) {

        $formHelper = new FormHelper();
        $arr = $formHelper->getPart();

        //add custom added values by order id
        $id = trim( $request->get('opt') );

        if( $id && $id != "undefined" ) {
            $message = $this->getDoctrine()->getRepository('OlegOrderformBundle:Message')->findOneByOid($id);
            if( $message ) {
                $parts = $message->getPart();
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
    public function getBlocknameAction(Request $request) {

        $formHelper = new FormHelper();
        $arr = $formHelper->getBlock();

        //add custom added values by order id
        $id = trim( $request->get('opt') );

        if( $id && $id != "undefined" ) {
            $message = $this->getDoctrine()->getRepository('OlegOrderformBundle:Message')->findOneByOid($id);
            if( $message ) {
                $blocks = $message->getBlock();
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
    public function getAccessionTypeAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $opt = trim( $request->get('opt') );
        $type = trim( $request->get('type') );

        //echo "opt=".$opt."<br>";

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:AccessionType', 'list')
            ->select("list.id as id, list.name as text, list.abbreviation as abbreviation")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.token_storage')->getToken()->getUser();

        if( $type == "single" ) {
            if( $opt && $opt != "undefined" ) {
                $query->where("list.type = :type OR list.type = :typetma OR ( list.type = 'user-added' AND list.creator = :user)");    //->setParameter('type', 'default')->setParameter('typetma', 'TMA');
                $query->setParameters( array('type' => 'default', 'typetma' => 'TMA', 'user' => $user) );
            }
        } else {
            if( $opt && $opt != "undefined" ) {
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
    public function getMrnTypeAction(Request $request) {

        $simple = false;
        $em = $this->getDoctrine()->getManager();

        $opt = trim( $request->get('opt') );
        $type = trim( $request->get('type') );
        $exception = trim( $request->get('exception') );

        //echo "opt=".$opt."<br>";

        $query = $em->createQueryBuilder()->from('OlegOrderformBundle:MrnType', 'list');
            //->select("list.id as id, list.name as text")
            //->select("list")

        if( $simple ) {
            $query->select("list.id as id, list.name as text");
        } else {
            $query->select("list");
        }

        $query->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.token_storage')->getToken()->getUser();

        if( $opt && $opt != "undefined" ) {
            $query->where("list.type = :type OR ( list.type = 'user-added' AND list.creator = :user)");
            $query->setParameters( array('type' => 'default', 'user' => $user) );
        }

        if( $exception == "autogenerated" ) {
            $query->andWhere("list.name != 'Auto-generated MRN'");
        }
        if( $exception == "existingautogenerated" ) {
            $query->andWhere("list.name != 'Existing Auto-generated MRN'");
        }

        //echo "query=".$query."<br>";

        if( $simple ) {
            $output = $query->getQuery()->getResult();
        } else {
            $mrntypes = $query->getQuery()->getResult();

            $output = array();
            foreach ($mrntypes as $mrntype) {
                $output[] = array('id' => $mrntype->getId(), 'text' => $mrntype->getOptimalName());
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * @Route("/slidetype", name="get-slidetype")
     * @Method("GET")
     */
    public function getSlideTypesAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:SlideType', 'list')
            ->select("list.name as text")
            ->where("list.type='default' OR list.type='user-added' OR list.type='TMA'")
            ->orderBy("list.orderinlist","ASC");

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
    public function getOptionalUserAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $holderId = trim( $request->get('opt') ); //associated object ProjectTitleTree id
        $routeName = $request->get('_route');

        if( $routeName == "get-optionalusereducational" ) {
            $role = "ROLE_SCANORDER_COURSE_DIRECTOR";
            $prefix = 'Oleg';
            $bundleName = 'OrderformBundle';
            $className = 'CourseTitleTree';
        }
        if( $routeName == "get-optionaluserresearch" ) {
            $role = "ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR";
            $prefix = 'Oleg';
            $bundleName = 'OrderformBundle';
            $className = 'ProjectTitleTree';
        }

        //1) ProjectTitleTree id => get research => get principalWrappers
        if( $holderId && $holderId != "undefined" ) {
            $query = $em->createQueryBuilder()
                ->from($prefix.$bundleName.':'.$className, 'list')
                //->select("userWrappers.id as id, CONCAT(userWrappers.name,CONCAT(' - ',userWrappersUserInfos.displayName)) as text")
                ->select("userWrappers.id as id, (CASE WHEN userWrappersUser.id IS NULL THEN userWrappers.name ELSE userWrappers.name+' - '+userWrappersUserInfos.displayName END) as text")
                //->select("userWrappers.id as id, userWrappers.name as text")
                ->leftJoin("list.userWrappers","userWrappers")
                ->leftJoin("userWrappers.user","userWrappersUser")
                ->leftJoin("userWrappersUser.infos","userWrappersUserInfos")

                ->where("list.id = :holderId AND (userWrappers.type = :type OR userWrappers.type = :type2)")
                ->orderBy("list.orderinlist","ASC")
                ->setParameters( array(
                    'holderId' => $holderId,
                    'type' => 'default',
                    'type2' => 'user-added'
                ));

            //echo "query=".$query."<br>";

            $output = $query->getQuery()->getResult();
        } else {
            $output = array();
        }

        //var_dump($output);

        //2) add users with ROLE_SCANORDER_COURSE_DIRECTOR and ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR
        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:User', 'list')
            //->select("list.id as id, list.username as text")
            ->select("list")
            ->where("list.roles LIKE :role")
            //->andWhere("list.testingAccount = 0 OR list.testingAccount IS NULL")
            ->orderBy("list.id","ASC")
            ->setParameter('role', '%"' . $role . '"%');

        $users = $query->getQuery()->getResult();

        foreach( $users as $user ) {
            $element = array('id'=>$user->getPrimaryPublicUserId()."", 'text'=>$user."");
            if( !$this->in_complex_array($user."",$output) ) {
                //echo "add user id=".$user->getId()."\n";
                $output[] = $element;
            }
        }

        //echo "\nfinal output:";
        //var_dump($output);
        //echo "\n";

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;

    }



    /**
     * @Route("/account", name="get-account")
     * @Method("GET")
     */
    public function getAccountAction(Request $request) {

        $whereServicesList = "";

        $em = $this->getDoctrine()->getManager();

        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Account', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.token_storage')->getToken()->getUser();

        if( $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
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
    public function getUrgencyAction(Request $request) {


        $em = $this->getDoctrine()->getManager();

        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Urgency', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.token_storage')->getToken()->getUser();

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

    /**
     * @Route("/returnlocation", name="scan_get_returnlocation")
     * @Method("GET")
     */
    public function getReturnLocationAction(Request $request) {

        $providerid = trim( $request->get('providerid') );
        $proxyid = trim( $request->get('proxyid') );

        if( $providerid == 'undefined' ) {
            $providerid = null;
        }

        if( $proxyid == 'undefined' ) {
            $proxyid = null;
        }

        //get default returnLocation option
        $orderUtil = $this->get('scanorder_utility');
        $returnLocations = $orderUtil->getOrderReturnLocations(null,$providerid,$proxyid);
        $preferredLocations = $returnLocations['preferred_choices'];

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Location', 'list')
            ->select("list")
            ->orderBy("user.username","ASC")
            ->addOrderBy("list.name","ASC");

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        //Exclude from the list locations of type "Patient Contact Information", "Medical Office", and "Inpatient location".
        $andWhere = "locationTypes.name IS NULL OR ".
            "(" .
                "locationTypes.name !='Patient Contact Information' AND ".
                "locationTypes.name !='Medical Office' AND ".
                "locationTypes.name !='Inpatient location' AND ".
                "locationTypes.name !='Employee Home'" .
            ")";

        $query->leftJoin("list.locationTypes", "locationTypes");
        $query->leftJoin("list.user", "user");
        $query->andWhere($andWhere);

        //exclude system user:  "user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'"; //"user.email != '-1'"
        $query->andWhere("user.id IS NULL OR (user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system')");

        //exclude preferred locations (they will be added later)
        $prefLocs = "";
        foreach( $preferredLocations as $loc ) {
            if( $prefLocs != "" ) {
                $prefLocs = $prefLocs . " AND ";
            }
            $prefLocs = $prefLocs . " list.id != " .$loc->getId();
        }
        //echo "prefLocs=".$prefLocs."<br>";
        if( $prefLocs ) {
            $query->andWhere($prefLocs);
        }

        //do not show (exclude) all locations that are tied to a user who has no current employment periods (all of whose employment periods have an end date)
        $curdate = date("Y-m-d", time());
        $query->leftJoin("user.employmentStatus", "employmentStatus");
        $currentusers = "employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."'";
        $query->andWhere($currentusers);

        //echo "query=".$query." | ";

        $locations = $query->getQuery()->getResult();
        //echo "loc count=".count($locations)."<br>";

        $output = array();

        foreach( $preferredLocations as $location ) {
            $element = array('id'=>$location->getId(), 'text'=>$location->getNameFull());
            $output[] = $element;
        }

        foreach( $locations as $location ) {
            $element = array('id'=>$location->getId(), 'text'=>$location->getNameFull());
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }




    /**
     * TODO: optimize this function. Error: Out of memory: https://c.med.cornell.edu/order/scan/util/common/encounterReferringProvider?cycle=new&sitename=call-log-book
     *
     * Get all users and user wrappers combined
     * @Route("/common/proxyuser", name="scan_get_proxyuser")
     * @Method("GET")
     */
    public function getProxyusersAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $loggedUser = $this->get('security.token_storage')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $cycle = $request->query->get('cycle');

        $output = array();

        ///////////// 1) get all real users /////////////
        if(0) {
            $query = $em->createQueryBuilder()
                ->from('OlegUserdirectoryBundle:User', 'list')
                ->select("list")
                //->groupBy('list.id')
                ->leftJoin("list.infos", "infos")
                ->leftJoin("list.employmentStatus", "employmentStatus")
                ->leftJoin("employmentStatus.employmentType", "employmentType")
                ->where("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                ->andWhere("(list.testingAccount = false OR list.testingAccount IS NULL)")
                ->andWhere("(list.keytype IS NOT NULL AND list.primaryPublicUserId != 'system')")
                ->orderBy("infos.displayName", "ASC");

            $users = $query->getQuery()->getResult();
            //echo "users count=".count($users)."<br>";

            foreach ($users as $user) {
                $element = array('id' => $user."", 'text' => $user . "");
                //$element = array('id' => $user->getUsername()."", 'text' => $user . "");
                //$element = array('id' => $user->getId(), 'text' => $user . "");
                //if( !$this->in_complex_array($user."",$output,'text') ) {
                    $output[] = $element;
                //}
            }
        }
        if(1) {
            $query = $em->createQueryBuilder()
                ->from('OlegUserdirectoryBundle:User', 'list')
                ->select("infos.displayName as id, infos.displayName as text")
                //->groupBy('list.id')
                ->leftJoin("list.infos", "infos")
                ->leftJoin("list.employmentStatus", "employmentStatus")
                ->leftJoin("employmentStatus.employmentType", "employmentType")
                ->where("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                ->andWhere("(list.testingAccount = false OR list.testingAccount IS NULL)")
                ->andWhere("(list.keytype IS NOT NULL AND list.primaryPublicUserId != 'system')")
                ->andWhere("infos.displayName IS NOT NULL")
                ->groupBy("list")
                ->orderBy("infos.displayName", "ASC")
            ;

            $output = $query->getQuery()->getResult();
            //echo "users count=".count($output)."<br>";
//            foreach ($outputs as $user) {
//                //echo "user=".$user."<br>";
//                //print_r($user);
//                $element = array('id' => $user['id'], 'text' => $user['text']);
//                //$element = array('id' => $user->getUsername()."", 'text' => $user . "");
//                //$element = array('id' => $user->getId(), 'text' => $user . "");
//                //if( !$this->in_complex_array($user."",$output,'text') ) {
//                $output[] = $element;
//                //}
//            }
            //exit('111');
        }
        //print_r($output);
        //exit('111');
        ///////////// EOF 1) get all real users /////////////


        $sourceSystem = $securityUtil->getDefaultSourceSystemByRequest($request);

        ///////////// 2) default user wrappers for this source ///////////////
        ///////////// 3) user-added user wrappers created by logged in user for this source ///////////////
        if(1) {
            $query = $em->createQueryBuilder()
                ->from('OlegUserdirectoryBundle:UserWrapper', 'list')
                ->select("list")
                ->leftJoin("list.user", "user")
                ->leftJoin("user.infos", "infos")
                ->leftJoin("list.creator", "creator")
                ->leftJoin("list.userWrapperSource", "userWrapperSource")
                ->orderBy("infos.displayName", "ASC");

            //default OR user-added user wrappers created by logged in user
            //$query->andWhere("list.type=:default");
            //echo "cycle=".$cycle."<br>";
            if( $cycle != "show" && $cycle != "edit" && $cycle != "amend" ) {
                $query->where("list.type = :typedef OR (list.type = :typeadd AND creator.id=:loggedUser)")->setParameters(
                    array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                        'loggedUser' => $loggedUser->getId()
                    )
                );
            }

            if( $sourceSystem ) {
                //echo "sourceSystem: id=".$sourceSystem->getId()."; ".$sourceSystem."<br>";
                $query->andWhere("userWrapperSource.id IS NULL OR userWrapperSource.id=" . $sourceSystem->getId());
            }

            //echo "query=".$query." <br><br>";
            //exit();

            $userWrappers = $query->getQuery()->getResult();
            foreach ($userWrappers as $userWrapper) {
//                if( $userWrapper->getUser() ) {
//                    $thisId = $userWrapper->getUser()->getUSername();
//                } else {
//                    $thisId = $userWrapper->getId();
//                }
                $thisId = $userWrapper->getId();
                $element = array(
                    'id' => $thisId,
                    'text' => $userWrapper . ""
                    //'text' => $userWrapper . "" . " [wrapper ID#".$thisId."]" //testing //TODO: fix user wrapper for edit/amend
                );

//                if( $cycle == "show" || $cycle == "edit" || $cycle == "amend" ) {
//                    $output[] = $element;
//                } else {
//                    if( !$this->in_complex_array($userWrapper . "", $output, 'id') ) {
//                        $output[] = $element;
//                    }
//                }

                if( !$this->in_complex_array($userWrapper . "", $output, 'id') ) {
                    $output[] = $element;
                }

            }

            //print_r($output);
            //exit('1');
        }
        ///////////// EOF 2) 3) user wrappers for this source ///////////////


        ///////////// 2) get all wrapper users /////////////
//        $query = $em->createQueryBuilder()
//            ->from('OlegUserdirectoryBundle:UserWrapper', 'list')
//            ->select("list")
//            ->leftJoin("list.user", "user")
//            ->leftJoin("user.infos", "infos")
//            //->leftJoin("user.employmentStatus", "employmentStatus")
//            //->leftJoin("employmentStatus.employmentType", "employmentType")
//            //->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
//            //->andWhere("user.testingAccount = 0 OR user.testingAccount IS NULL")
//            //->select("list.id as id, infos.displayName as text")
//            ->orderBy("infos.displayName","ASC");
//        $userWrappers = $query->getQuery()->getResult();
//        foreach( $userWrappers as $userWrapper ) {
//            $element = array(
//                'id'        => $userWrapper->getId(),
//                'text'      => $userWrapper.""
//            );
//            if( !$this->in_complex_array($userWrapper."",$output) ) {
//                $output[] = $element;
//            }
//        }
        ///////////// EOF get all wrapper users /////////////

        //$output = array_merge($users,$output);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * Get all users and user wrappers combined
     * @Route("/common/encounterReferringProvider", name="scan_get_encounterReferringProvider")
     * @Method("GET")
     */
    public function getEncounterReferringProvidersAction(Request $request) {
        //echo "get encounterReferringProvider<br>";
        return $this->getProxyusersAction($request);
    }

    /**
     * Get all users and user wrappers combined
     * @Route("/common/encounterAttendingPhysician", name="scan_get_encounterAttendingPhysician")
     * @Method("GET")
     */
    public function getEncounterAttendingPhysiciansAction(Request $request) {
        //echo "get encounterReferringProvider<br>";
        return $this->getProxyusersAction($request);
    }

    /**
     * @Route("/common/get-encounter-referring-provider/", name="scan_get_encounterreferringprovider", options={"expose"=true})
     * @Method("GET")
     */
    public function getEncounterReferringProviderByNameAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $providerId = trim($request->get('providerId'));
        //echo "providerId=".$providerId."<br>";

        $output = array();
        $objectId = null;
        $specialty = null;
        $phone = null;
        $email = null;

        //echo 'compare: '.strval($providerId).' ?= '.strval(intval($providerId))."<br>";
        if( strval($providerId) == strval(intval($providerId)) ) {
            //echo "Case1: providerId is integer=$providerId => providerID is wrapperId => find EncounterReferringProvider by field<br>";
            $provider = $em->getRepository('OlegOrderformBundle:EncounterReferringProvider')->findOneByField($providerId);

            if( $provider ) {
                //echo "provider=".$provider."<br>";
                $userWrapper = $provider->getField();

                if( $userWrapper ) {
                    $objectId = $userWrapper->getId();
                }

                //priority is on EncounterReferringProvider's object
                if( $provider->getReferringProviderSpecialty() ) {
                    $specialty = $provider->getReferringProviderSpecialty()->getId();
                } else {
                    if( $userWrapper ) {
                        $specialty = $userWrapper->getUserWrapperSpecialty();
                    }
                }

                if( $provider->getReferringProviderSpecialty() ) {
                    $phone = $provider->getReferringProviderPhone();
                } else {
                    if( $userWrapper ) {
                        $phone = $userWrapper->getUserWrapperPhone();
                    }
                }

                if( $provider->getReferringProviderSpecialty() ) {
                    $email = $provider->getReferringProviderEmail();
                } else {
                    if( $userWrapper ) {
                        $email = $userWrapper->getUserWrapperEmail();
                    }
                }
            }

        } else {
            //echo "Case2: providerId is string=$providerId<br>";

            if( strpos($providerId, '_@_') !== false ) {
                //cwid_@_ldap-user
                $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername($providerId);
            } else {
                //$user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername($providerId);
                $userSecUtil = $this->get('user_security_utility');
                $user = $userSecUtil->getUserByUserstr($providerId);
            }

            if( $user ) {
                $objectId = $user->getUsername();
                $phone = $user->getPreferredPhone();
                $email = $user->getEmail();
            }

        }

        $output['id'] = $objectId;
        $output['referringProviderSpecialty'] = $specialty;
        $output['referringProviderPhone'] = $phone;
        $output['referringProviderEmail'] = $email;

//        print "<pre>";
//        print_r($output);
//        print "</pre><br>";
//        exit('1');

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
