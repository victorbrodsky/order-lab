<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

//use Oleg\OrderformBundle\Entity\OrderInfo;
//use Oleg\OrderformBundle\Form\OrderInfoType;
use Oleg\OrderformBundle\Form\FilterType;
use Oleg\OrderformBundle\Entity\Document;
use Oleg\OrderformBundle\Helper\OrderUtil;


//ScanOrder joins OrderInfo + Scan
/**
 * OrderInfo controller.
 *
 * @Route("/")
 */
class ScanOrderController extends Controller {

    /**
     * Lists all OrderInfo entities.
     *
     * @Route("/", name="scan-order-home")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Default:home.html.twig")
     */
    public function indexAction( Request $request ) {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        $unprocessed = $this->getUnprocessedOrders();

        $sliderequests = $this->getUnprocessedSlideRequests();

        //check for active user requests
        $accountreqs = $this->getActiveAccountReq();

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        return array(
            'unprocessed' => $unprocessed,
            'sliderequests' => $sliderequests,
            'accountreqs' => count($accountreqs),
            'accessreqs' => count($accessreqs)
        );
    }

    /**
     * Lists all OrderInfo entities.
     *
     * @Route("/my-scan-orders", name="my-scan-orders")
     * @Route("/incoming-scan-orders", name="incoming-scan-orders")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ScanOrder:index.html.twig")
     */
    public function orderListAction( Request $request ) {

        $em = $this->getDoctrine()->getManager();

        $routeName = $request->get('_route');
        //echo "routeName=".$routeName."<br>";

        if( $routeName == "incoming-scan-orders" && false === $this->get('security.context')->isGranted('ROLE_PROCESSOR')) {
            return $this->redirect( $this->generateUrl('my-scan-orders') );
        }

        //by user
        $user = $this->get('security.context')->getToken()->getUser();
        //echo "user=".$user;
        if( !is_object($user) ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        if( $routeName == "incoming-scan-orders" ) {
            $services = $this->getServiceFilter();
            $commentFlag = 'admin';
        } else {
            $services = null;
            $commentFlag = null;
        }

//        $adminemail = $this->container->getParameter('scanorder.adminemail');
//        echo "adminemail=".$adminemail."<br>";
//        exit();
        //throw new \Exception( 'Test' );
        //http://knpbundles.com/craue/CraueConfigBundle
        //$this->get('craue_config')->set('ldap_driver_host', 'a.wcmc-ad.net');

        //create filters
        $form = $this->createForm(new FilterType( $this->getFilter($routeName), $user, $services ), null);
        $form->bind($request);  //use bind instead of handleRequest. handleRequest does not get filter data

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo');
        $dql =  $repository->createQueryBuilder("orderinfo");
        $dql->select('orderinfo, COUNT(slides) as slidecount');

        $dql->groupBy('orderinfo');
        $dql->addGroupBy('status.name');
        $dql->addGroupBy('formtype.name');
        $dql->addGroupBy('provider.username');

        //$dql->having("( (COUNT(orderinfo) > 1) AND (COUNT(status.name) > 1) AND (COUNT(formtype.name) > 1) AND (COUNT(provider.username) > 1) )");
        //$dql->having("( COUNT(orderinfo) > 1 )");

        $dql->innerJoin("orderinfo.slide", "slides");
        $dql->innerJoin("orderinfo.provider", "provider");
        $dql->innerJoin("orderinfo.type", "formtype");

        $dql->leftJoin("orderinfo.history", "history"); //history might not exist, so use leftJoin
        $dql->leftJoin("orderinfo.proxyuser", "proxyuser");

        $search = $form->get('search')->getData();
        $filter = $form->get('filter')->getData();
        $service = $form->get('service')->getData();

        //service
        //echo "<br>service=".$service."<br>";
        //exit();

        $criteriastr = "";

        //***************** Pathology Service filetr ***************************//
        $showprovider = 'false';
        $showproxyuser = 'false';

        //***************** Status filetr ***************************//
        $dql->innerJoin("orderinfo.status", "status");
        //echo "status filter = ".$filter."<br>";
        if( $filter && is_numeric($filter) && $filter > 0 ) {
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }
            $criteriastr .= " status.id=" . $filter;
        }

        //filter special cases
        if( $filter && is_string($filter) && $filter != "All" ) {

            //echo "filter=".$filter;
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }

            switch( $filter ) {

                case "With New Comments":
                    $orderUtil = new OrderUtil($em);
                    $newCommentsCriteriaStr = "( " . $orderUtil->getCommentsCriteriaStr($this->get('security.context'),'new_comments',$commentFlag) . " ) ";
                    $criteriastr .= $newCommentsCriteriaStr;
                    break;
                case "With Comments":
                    $orderUtil = new OrderUtil($em);
                    $newCommentsCriteriaStr = "( " . $orderUtil->getCommentsCriteriaStr($this->get('security.context'),'all_comments',null,$commentFlag) . " ) ";
                    $criteriastr .= $newCommentsCriteriaStr;
                    break;
                case "All Filled":
                    $criteriastr .= " status.name LIKE '%Filled%'";
                    break;
                case "All Filled & Returned":
                    $criteriastr .= " status.name LIKE '%Filled%' AND status.name LIKE '%Returned%'";
                    break;
                case "All Filled & Not Returned":
                    $criteriastr .= " status.name LIKE '%Filled%' AND status.name NOT LIKE '%Returned%'";
                    break;
                case "All Not Filled":
                    $criteriastr .= " status.name NOT LIKE '%Filled%' AND status.name NOT LIKE '%Not Submitted%'";
                    break;
                case "All On Hold":
                    $criteriastr .= " status.name LIKE '%On Hold%'";
                    break;
                case "All Canceled":
                    $criteriastr .= " status.name = 'Canceled by Submitter' OR status.name = 'Canceled by Processor'";
                    break;
                case "All Submitted & Amended":
                    $criteriastr .= " status.name = 'Submitted' OR status.name = 'Amended'";
                    break;
                case "All Stat":
                    $criteriastr .= " orderinfo.priority = 'Stat'";
                    break;
                case "Stat & Not Filled":
                    $criteriastr .= " orderinfo.priority = 'Stat' AND status.name NOT LIKE '%Filled%'";
                    break;
                case "Stat & Filled":
                    $criteriastr .= " orderinfo.priority = 'Stat' AND status.name LIKE '%Filled%'";
                    break;
                case "No Course Director Link":
                    $dql->innerJoin("orderinfo.educational", "educational");
                    $dql->innerJoin("educational.directorWrappers", "directorWrappers");
                    $dql->innerJoin("directorWrappers.director", "director");
                    $criteriastr .= " director.director IS NULL AND status.name != 'Superseded'";
                    break;
                case "No Principal Investigator Link":
                    $dql->innerJoin("orderinfo.research", "research");
                    $dql->innerJoin("research.principalWrappers", "principalWrappers");
                    $dql->innerJoin("principalWrappers.principal", "principal");
                    $criteriastr .= " principal.principal IS NULL AND status.name != 'Superseded'";
                    break;
                default:
                    ;
            }

        }
        //***************** END of Status filetr ***************************//

        //***************** Superseded filter ***************************//
        if( false === $this->get('security.context')->isGranted('ROLE_PROCESSOR') ) {
            //$superseded_status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Superseded');
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }
            $criteriastr .= " status.name != 'Superseded'";
        }
        //***************** END of Superseded filetr ***************************//


        //***************** Search filetr ***************************//
        if( $search && $search != "" ) {
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }
            $dql->leftJoin("orderinfo.accession", "accessionobj");
            $dql->leftJoin("accessionobj.accession", "accession");
            $criteriastr .= "accession.field LIKE '%" . $search . "%'";

            //patient name
            $dql->leftJoin("orderinfo.patient", "patientobj");
            $dql->leftJoin("patientobj.name", "name");
            $criteriastr .= "OR name.field LIKE '%" . $search . "%'";

            //part Gross Description
            $dql->leftJoin("orderinfo.part", "partobj");
            $dql->leftJoin("partobj.description", "description");
            $criteriastr .= "OR description.field LIKE '%" . $search . "%'";
            
        }
        //***************** END of Search filetr ***************************//

        //***************** User filter ***************************//
        if( $routeName == "my-scan-orders" ) {

            $crituser = "";

            //echo $routeName.": service=".$service."<br>";
            //select only orders where this user is author or proxy user, except "Where I am the Course Director" and "Where I am the Principal Investigator" cases.
            if( $service == "" || $service == "My Orders" ) {

                //show only my order and the orders where I'm a proxy
                //Orders I Personally Placed and Where I am the Ordering Provider: $service == "My Orders"

                $crituser .= "( provider.id=".$user->getId();

                //***************** Proxy User Orders *************************//
                $crituser .= " OR proxyuser.id=".$user->getId();
                //***************** END of Proxy User Orders *************************//

                $crituser .= " )";


                //***************** Pathology service filter: show all orders with chosen pathology service matched with current user's service *****************//
                $allservices = $this->allServiceFilter( $service, $routeName, $user, $crituser );
                if( $allservices != "" ) {
                    $showprovider = 'true';
                    $crituser .= $allservices;
                }
                //***************** EOF: Pathology service filter: show all orders with chosen pathology service matched with current user's service *****************//
            }

            //show all for ROLE_DIVISION_CHIEF: remove all user's restriction
            if( $this->get('security.context')->isGranted('ROLE_DIVISION_CHIEF') ) {
                //echo "ROLE_DIVISION_CHIEF";
                $crituser = "";
            }

            if( $service == "Orders I Personally Placed" ) {
                //echo "Orders I Personally Placed <br>";
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                $crituser .= "provider.id=".$user->getId();
            }
            if( $service == "Where I am the Ordering Provider" ) {
                //echo "Where I am the Ordering Provider <br>";
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                //***************** Proxy User Orders *************************//
                $crituser .= "proxyuser.id=".$user->getId();
                //***************** END of Proxy User Orders *************************//
            }
            if( $service == "Where I am the Course Director" ) {
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                $dql->innerJoin("orderinfo.educational", "educational");
                $dql->innerJoin("educational.directorWrappers", "directorWrappers");
                $dql->innerJoin("directorWrappers.director", "director");
                $crituser .= "director.director=".$user->getId();
            }
            if( $service == "Where I am the Principal Investigator" ) {
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                $dql->innerJoin("orderinfo.research", "research");
                $dql->innerJoin("research.principalWrappers", "principalWrappers");
                $dql->innerJoin("principalWrappers.principal", "principal");
                $crituser .= "principal.principal=".$user->getId();
            }
            if( $service == "Where I am the Amendment Author" ) {
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                $crituser .= "history.provider=".$user->getId()." AND history.eventtype='Amended Order Submission'";
            }

            //"All ".$service->getName()." Orders"; => $service is service's id
            if( is_int($service) ) {
                //echo "service=".$service."<br>";
                $showprovider = 'true';
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
                $crituser .= "orderinfo.pathologyService=".$service;
            }

            if( $criteriastr != "" && $crituser != "" ) {
                $criteriastr = $criteriastr." AND ".$crituser;
            } else {
                $criteriastr .= $crituser;
            }

        }
        //***************** END of User filetr ***************************//

        if( $routeName == "incoming-scan-orders" ) {
            //echo "admin index filter <br>";
            //***************** Data Review filter ***************************//
//            "No Course Director Link" => "No Course Director Link",
//            "No Principal Investigator Link" => "No Principal Investigator Link"
            //***************** End of Service filter ***************************//

            //filter by service
            $critservice = "";
            if( is_int($service) ) {
                //echo "service=".$service."<br>";
                $showproxyuser = 'true';
                $critservice = "orderinfo.pathologyService=".$service;
            }

            if( $criteriastr != "" && $critservice != "" ) {
                $criteriastr = $criteriastr." AND ".$critservice;
            } else {
                $criteriastr .= $critservice;
            }
        }

        //echo "<br>criteriastr=".$criteriastr."<br>";
        
        if( $criteriastr != "" ) {
            $dql->where($criteriastr);
        }

        $params = $this->get('request_stack')->getCurrentRequest()->query->all();
        $sort = $this->get('request_stack')->getCurrentRequest()->query->get('sort');
        
        if( $routeName == "my-scan-orders" ) {
            if( $params == null || count($params) == 0 ) {
                $dql->orderBy("orderinfo.orderdate","DESC");
            }
            if( $sort != 'orderinfo.oid' ) {
                $dql->orderBy("orderinfo.orderdate","DESC");
            }
        }
               
        if( $routeName == "incoming-scan-orders" ) {
            if( $sort == '' ) {
                $dql->orderBy("orderinfo.priority","DESC");
                $dql->addOrderBy("orderinfo.scandeadline","ASC");
                $dql->addOrderBy("orderinfo.orderdate","DESC");
            }
        }
        
        //echo "dql=".$dql;
        
        $limit = 50;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        //check for active user requests
        $accountreqs = $this->getActiveAccountReq();

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        $processorComments = $em->getRepository('OlegOrderformBundle:ProcessorComments')->findAll();

        //echo "<br>pagination count=".count($pagination)."<br>";
        //exit();

        return array(
            'form' => $form->createView(),
            'showprovider' => $showprovider,
            'showproxyuser' => $showproxyuser,
            'pagination' => $pagination,
            'accountreqs' => $accountreqs,
            'accessreqs' => $accessreqs,
            'routename' => $routeName,
            'comments' => $processorComments
        );
    }


    //requirements={"id" = "\d+"}
    /**
     * Deletes a OrderInfo entity.
     *
     * @Route("/scan-order/{id}/delete", name="scanorder_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {

        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OrderInfo entity.');
            }

//            $scan_entities = $em->getRepository('OlegOrderformBundle:Scan')->
//                    findBy(array('scanorder_id'=>$id));

//            $scan_entities = $em->getRepository('OlegOrderformBundle:Scan')->findBy(
//                array('scanorder' => $id)
//            );
            $entity->removeAllChildren();

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('scanorder'));
    }

    /**
     * Change status of orderinfo
     *
     * @Route("/scan-order/{id}/status/{status}/", name="scanorder_status")
     * @Method("GET")
     * @Template()
     */
    public function statusAction(Request $request, $id, $status) {

        if( false === $this->get('security.context')->isGranted('ROLE_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_EXTERNAL_SUBMITTER')
        ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }
        
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        $orderUtil = new OrderUtil($em);

        //make uppercase: cancel, sumbit, un-cancel (Un-Cancel)
        //$status = str_replace("-"," ",$status);
        $status = ucwords($status);
        //$status = str_replace(" ","-",$status);

        $res = $orderUtil->changeStatus($id, $status, $user, $this->get('router'));

        if( $res['result'] == 'conflict' ) {   //redirect to amend
            return $this->redirect( $this->generateUrl( 'order_amend', array('id' => $res['oid']) ) );
        }

        $this->get('session')->getFlashBag()->add('status-changed',$res['message']);


        $referer_url = $request->headers->get('referer');
        //$referer_url = 'my-scan-orders';
        //return $this->redirect($this->generateUrl($previouspath));
        return new RedirectResponse($referer_url);
    }

    /**
     * Creates a form to delete a OrderInfo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    
    /**   
     * @Route("/thanks", name="thanks")
     * 
     * @Template("OlegOrderformBundle:ScanOrder:thanks.html.twig")
     */
    public function thanksAction( $oid = '' )
    {    
        
        return $this->render('OlegOrderformBundle:ScanOrder:thanks.html.twig',
            array(
                'oid' => $oid
            ));
    }

    public function getFilter($routeName) {
        $em = $this->getDoctrine()->getManager();

//        if( $this->get('security.context')->isGranted('ROLE_PROCESSOR') ) {
        if( $routeName == "incoming-scan-orders" ) {
            $statuses = $em->getRepository('OlegOrderformBundle:Status')->findAll();
        } else {
            $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:Status');
            $dql = $repository->createQueryBuilder("status");
            //$dql->where('status.action IS NOT NULL');
            $dql->where("status.name != 'Superseded'");
            $statuses = $dql->getQuery()->getResult();
        }

        //add special cases statuses
        $specials = array(
            "All" => "All Statuses",
            "All Not Filled" => "All Not Filled",
            "All On Hold" => "All On Hold",
            "All Stat" => "All Stat",
            //All Canceled here
            "All Submitted & Amended" => "All Submitted & Amended",
            "All Filled" => "All Filled",
            "All Filled & Not Returned" => "All Filled & Not Returned",
            "All Filled & Returned" => "All Filled & Returned",
            "With New Comments" => "With New Comments",
            "With Comments" => "With Comments",
            "Stat & Not Filled" => "Stat & Not Filled",
            "Stat & Filled" => "Stat & Filled"
        );

        $filterType = array();
        foreach( $specials as $key => $value ) {
            $filterType[$key] = $value;
            if( $value == "All Stat" ) {
                $filterType["All Canceled"] = "All Canceled";   //add after Not Submitted
            }
        }

        //add statuses from DB
        foreach( $statuses as $status ) {
            //echo "type: id=".$status->getId().", name=".$status->getName()."<br>";
            $filterType[$status->getId()] = $status->getName();
        }

        //add Data Review
        if( $routeName == "incoming-scan-orders" ) {
            $dataReviews = array(
                "No Course Director Link" => "No Course Director Link",
                "No Principal Investigator Link" => "No Principal Investigator Link"
            );

            foreach( $dataReviews as $key => $value ) {
                $filterType[$key] = $value;
            }
        }

        return $filterType;
    }
    
    
    public function getServiceFilter() {
        $em = $this->getDoctrine()->getManager();

        if( $this->get('security.context')->isGranted('ROLE_PROCESSOR') ) {
            $statuses = $em->getRepository('OlegOrderformBundle:PathServiceList')->findAll();
        } 

        //add special cases
        $specials = array(
            "All" => "All Services",          
        );

        $filterType = array();
        foreach( $specials as $key => $value ) {
            $filterType[$key] = $value;
        }

        //add statuses
        foreach( $statuses as $status ) {
            //echo "type: id=".$status->getId().", name=".$status->getName()."<br>";
            $filterType[$status->getId()] = $status->getName();           
        }

        return $filterType;
    }

    //Pathology Service filter
    public function allServiceFilter( $service, $routeName, $user, $criterions ) {

        $criteriastr = "";
        $em = $this->getDoctrine()->getManager();

        if( $this->get('security.context')->isGranted('ROLE_DIVISION_CHIEF') ) {
            return $criteriastr;
        }

        //for "My Orders" get all user services and chief services
        if( $routeName == "my-scan-orders" ) {

            $services = array();
            $userServices = $user->getPathologyServices();

            if( $this->get('security.context')->isGranted('ROLE_SERVICE_CHIEF') ) {
                $chiefServices = $user->getChiefservices();
                if( $userServices && count($userServices)>0 ) {
                    $services = array_merge($userServices, $chiefServices);
                } else {
                    $services = $chiefServices;
                }
            }

            foreach( $services as $service ) {
                if( $service && $service != "" ) {
                    if( $criteriastr != "" ) {
                        $criteriastr .= " OR ";
                    }
                    $criteriastr .= " orderinfo.pathologyService=".$service->getId();
                }
            }//foreach

        }

        //for "Incoming Orders" select only chosen service
        if( $routeName == "incoming-scan-orders" ) {

            if( is_numeric($service)  ) {

                $pathService = $em->getRepository('OlegOrderformBundle:PathServiceList')->find($service);

                if( !$pathService ) {
                    throw new \Exception( 'Unable to find Service '.$service );
                }

                $criteriastr = " orderinfo.pathologyService=".$pathService->getId();

            }

        }

        if( $criterions != "" ) {
            if( $criteriastr != "" ) {
                $criteriastr = " OR (" . $criteriastr . ") ";
            }
        } else {
            $criteriastr = " (" . $criteriastr . ") ";
        }

        return $criteriastr;
    }


    /**
     * Finds and displays a unprocessed orders.
     */
    public function getUnprocessedOrders()
    {
        $unprocessed = 0;
        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo');
        $dql =  $repository->createQueryBuilder("orderinfo");
        $dql->innerJoin("orderinfo.status", "status");
        $dql->where("status.name NOT LIKE '%Filled%' AND status.name NOT LIKE '%Not Submitted%'");
        $query = $em->createQuery($dql);
        $unprocessedOrders = $query->getResult();

        if( $unprocessedOrders && count($unprocessedOrders) > 0 ) {
            $unprocessed = count($unprocessedOrders);
        }

        return $unprocessed;
    }


    /**
     * Finds and displays a unprocessed (active) Slide Return Requests.
     */
    public function getUnprocessedSlideRequests()
    {
        $unprocessed = 0;
        $em = $this->getDoctrine()->getManager();

        $slideReturnRequest = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->findByStatus('active');

        if( $slideReturnRequest && count($slideReturnRequest) > 0 ) {
            $unprocessed = count($slideReturnRequest);
        }

        return $unprocessed;
    }

    //check for active user requests
    public function getActiveAccountReq() {
        $em = $this->getDoctrine()->getManager();
        $accountreqs = array();
        if( $this->get('security.context')->isGranted('ROLE_PROCESSOR') ) {
            $accountreqs = $em->getRepository('OlegOrderformBundle:UserRequest')->findByStatus("active");
        }
        return $accountreqs;
    }

    //check for active access requests
    public function getActiveAccessReq() {
        $em = $this->getDoctrine()->getManager();
        $accessreqs = array();
        if( $this->get('security.context')->isGranted('ROLE_PROCESSOR') ) {
            $accessreqs = $em->getRepository('OlegOrderformBundle:User')->findByAppliedforaccess('active');
        }
        return $accessreqs;
    }

}
