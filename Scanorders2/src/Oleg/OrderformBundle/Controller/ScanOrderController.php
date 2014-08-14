<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\OrderformBundle\Form\FilterType;
use Oleg\OrderformBundle\Entity\Document;
use Oleg\OrderformBundle\Helper\OrderUtil;
use Oleg\OrderformBundle\Entity\Logger;


//ScanOrder joins OrderInfo + Scan
/**
 * OrderInfo controller.
 *
 * @Route("/")
 */
class ScanOrderController extends Controller {

    protected $limit = 50;

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

        if( $routeName == "incoming-scan-orders" && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR')) {
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

        $search = $form->get('search')->getData();
        $filter = $form->get('filter')->getData();
        $service = $form->get('service')->getData();
        $page = $request->get('page');

        //service
        //echo "<br>service=".$service."<br>";
        //exit();

        $increaseMaxExecTime = false;

        if( $search != "" ) {
            return $this->createComplexSearchPage( $form, $routeName, $service, $filter, $search, $page );
        }

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo');

        $withSearch = true;
        $res = $this->getDQL( $repository, $service, $filter, $search, $routeName, $this->get('security.context'), $withSearch );
        $dql = $res['dql'];
        $criteriastr = $res['criteriastr'];
        $showprovider = $res['showprovider'];
        $showproxyuser = $res['showproxyuser'];

        if( $criteriastr != "" ) {
            $dql->where($criteriastr);
        }

        $params = $this->get('request_stack')->getCurrentRequest()->query->all();
        $sort = $this->get('request_stack')->getCurrentRequest()->query->get('sort');

        //echo "sort=".$sort.", page=".$page."<br>";

        if( $routeName == "my-scan-orders" ) {
            if( $sort == '' ) {
                if( $params == null || count($params) == 0 ) {
                    $dql->orderBy("orderinfo.orderdate","DESC");
                }
                if( $sort != 'orderinfo.oid' ) {
                    $dql->orderBy("orderinfo.orderdate","DESC");
                }
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

        if( $increaseMaxExecTime ) {
            $max_exec_time = ini_get('max_execution_time');
            ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        }


        $limit = $this->limit;

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

        if( $increaseMaxExecTime ) {
            ini_set('max_execution_time', $max_exec_time); //set back to the original value
        }

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

        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN')) {
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

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
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

        if( $res['result'] == 'nopermission' ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $this->get('session')->getFlashBag()->add('status-changed',$res['message']);

        $referer_url = $request->headers->get('referer');

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

//        if( $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
        if( $routeName == "incoming-scan-orders" ) {
            $statuses = $em->getRepository('OlegOrderformBundle:Status')->findAll();
        } else {
            $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:Status');
            $dql = $repository->createQueryBuilder("status");
            //$dql->where('status.action IS NOT NULL');
            $dql->where("status.name != 'Superseded'");
            $statuses = $dql->getQuery()->getResult();
        }

        $filterType = array();

        //add at the top
        if( $routeName == "incoming-scan-orders" ) {
            $dataReviews = array(
                "All Statuses (except Not Submitted)" => "All Statuses (except Not Submitted)",
            );

            foreach( $dataReviews as $key => $value ) {
                $filterType[$key] = $value;
            }
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

        if( $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
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

        if( $this->get('security.context')->isGranted('ROLE_SCANORDER_DIVISION_CHIEF') ) {
            return $criteriastr;
        }

        //for "My Orders" get all user services and chief services
        if( $routeName == "my-scan-orders" ) {

            $services = array();
            $userServices = $user->getDivision();

            if( $this->get('security.context')->isGranted('ROLE_SCANORDER_SERVICE_CHIEF') ) {
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

        /////////// institution ///////////
        $user = $this->get('security.context')->getToken()->getUser();
        $instStr = "";
        foreach( $user->getInstitution() as $inst ) {
            if( $instStr != "" ) {
                $instStr = $instStr . " OR ";
            }
            $instStr = $instStr . 'orderinfo.institution='.$inst->getId();
        }
        if( $instStr == "" ) {
            $instStr = "1=0";
        }
        if( $instStr != "" ) {
            $instStr = " AND (" . $instStr . ") ";
        }
        //echo "instStr=".$instStr."<br>";
        /////////// EOF institution ///////////

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo');
        $dql =  $repository->createQueryBuilder("orderinfo");
        $dql->innerJoin("orderinfo.status", "status");
        $dql->where("status.name NOT LIKE '%Filled%' AND status.name NOT LIKE '%Not Submitted%'" . $instStr);
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

        //$slideReturnRequest = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->findByStatus('active');

        /////////// institution ///////////
        $user = $this->get('security.context')->getToken()->getUser();
        $instStr = "";
        foreach( $user->getInstitution() as $inst ) {
            if( $instStr != "" ) {
                $instStr = $instStr . " OR ";
            }
            $instStr = $instStr . 'orderinfo.institution='.$inst->getId();
        }
        if( $instStr == "" ) {
            $instStr = "1=0";
        }
        if( $instStr != "" ) {
            $instStr = " AND (" . $instStr . ") ";
        }
        //echo "instStr=".$instStr."<br>";
        /////////// EOF institution ///////////

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:SlideReturnRequest');
        $dql =  $repository->createQueryBuilder("req");
        $dql->innerJoin("req.orderinfo", "orderinfo");
        $dql->where("req.status='active'" . $instStr);
        //echo "dql=".$dql;
        $query = $em->createQuery($dql);
        $slideReturnRequest = $query->getResult();

        if( $slideReturnRequest && count($slideReturnRequest) > 0 ) {
            $unprocessed = count($slideReturnRequest);
        }

        return $unprocessed;
    }

    //check for active user requests
    public function getActiveAccountReq() {
        $em = $this->getDoctrine()->getManager();
        $accountreqs = array();
        if( $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            $accountreqs = $em->getRepository('OlegOrderformBundle:UserRequest')->findByStatus("active");
        }
        return $accountreqs;
    }

    //check for active access requests
    public function getActiveAccessReq() {
        $em = $this->getDoctrine()->getManager();
        $accessreqs = array();
        if( $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            $accessreqs = $em->getRepository('OlegOrderformBundle:User')->findByAppliedforaccess('active');
        }
        return $accessreqs;
    }


    public function createComplexSearchPage( $form, $routeName, $service, $filter, $search, $page ) {

        $searchObjects = [
            'orderinfo.oid',
            'educational.courseTitleStr',
            'educational.lessonTitleStr',
            'research.projectTitleStr',
            'research.setTitleStr',
            'provider',
            'proxyuser',
            'directorUser',
            'principalUser',
            'accession',
            'patient.mrn',
            'patient.name',
            'part.disident',
            'part.diffDisident',
            'scan.note',
            'pathistory.field',
            'procedureType.name',
            'sectionsource.field',
            'description.field',
            'slides.microscopicdescr',
            'diseaseType.field',
            'StainList.name',
            'specialStains.field',
            'clinicalHistory.field'
        ];

        return $this->render('OlegOrderformBundle:ScanOrder:index-search.html.twig', array(
            'form' => $form->createView(),
//            'showprovider' => $showprovider,
//            'showproxyuser' => $showproxyuser,
//            'pagination' => $pagination,
//            'accountreqs' => $accountreqs,
//            'accessreqs' => $accessreqs,
            'routename' => $routeName,
//            'comments' => $processorComments
            'service' => $service,
            'filter' => $filter,
            'search' => $search,
            'page' => $page,
            'searchObjects' => $searchObjects
        ));
    }

    /**
     * Find accession by #
     * @Route("/scanorder-complex-search", name="scanorder-complex-search")
     * @Method("POST")
     */
    public function getSearchViewAjaxAction( Request $request ) {

        $routename   = $request->get('routename');
        $service   = $request->get('service');
        $filter   = $request->get('filter');
        $search   = $request->get('search');
        $searchObject   = $request->get('searchobject');
        $page   = $request->get('page');

        //echo "routename=".$routename.", search=".$search.", searchObject=".$searchObject."<br>";

        return $this->getSearchViewAction( $routename, $service, $filter, $search, $searchObject, $page );
    }

    //render the search result a single search objects
    public function getSearchViewAction( $routeName, $service, $filter, $search, $searchObject, $page ) {
        $viewArr = $this->getSearchViewArray( $routeName, $service, $filter, $search, $searchObject, $page );

        //////// record to EventLog ////////
        if( !$page || $page == "" ) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->get('security.context')->getToken()->getUser();
            $roles = $user->getRoles();
            $request = $this->get('request');

            $count = count($viewArr['pagination']);
            if( $count == $viewArr['limit'] ) {
                $count = $count . "+";
            }

            $logger = new Logger();
            $logger->setUser($user);
            $logger->setRoles($roles);
            $logger->setUsername($user."");
            $logger->setIp($request->getClientIp());
            $logger->setUseragent($_SERVER['HTTP_USER_AGENT']);
            $logger->setWidth($request->get('display_width'));
            $logger->setHeight($request->get('display_height'));
            $logger->setEvent( 'Search for "' . $search . '" in ' . $viewArr['searchObjectName'] . '. ' . $count . ' results found.' );

            $em->persist($logger);
            $em->flush();
        }
        //////// EOF EventLog ////////

        return $this->render('OlegOrderformBundle:ScanOrder:one-search-result.html.twig', $viewArr);
    }


    //render the search results for all search objects
    public function getSearchAllViewAction( $routeName, $service, $filter, $search, $searchObjects, $page ) {

        $renderedViewArr = array();

        $resArr = array();

        foreach( $searchObjects as $searchObject ) {
            $viewArr = $this->getSearchViewArray( $routeName, $service, $filter, $search, $searchObject, $page );

            //$renderedView = $this->render('OlegOrderformBundle:ScanOrder:one-search-result.html.twig', $viewArr);
            $renderedView = $this->renderView('OlegOrderformBundle:ScanOrder:one-search-result.html.twig', $viewArr);

            $renderedViewArr[] = $renderedView;
            $resArr[] = 'Search for "' . $viewArr['search'] . '" in ' . $viewArr['searchObjectName'] . '. ' . count($viewArr['pagination']) . ' results found.';
        }


        //////// record to EventLog ////////
        if( !$page || $page == "" ) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->get('security.context')->getToken()->getUser();
            $roles = $user->getRoles();
            $request = $this->get('request');

            $count = count($viewArr['pagination']);
            if( $count == $viewArr['limit'] ) {
                $count = $count . "+";
            }

            $logger = new Logger();
            $logger->setUser($user);
            $logger->setRoles($roles);
            $logger->setUsername($user."");
            $logger->setIp($request->getClientIp());
            $logger->setUseragent($_SERVER['HTTP_USER_AGENT']);
            $logger->setWidth($request->get('display_width'));
            $logger->setHeight($request->get('display_height'));
            //$logger->setEvent( 'Search for "' . $search . '" in ' . $viewArr['searchObjectName'] . '. ' . $count . ' results found.' );
            $logger->setEvent( implode("<br>",$resArr) );

            $em->persist($logger);
            $em->flush();
        }
        //////// EOF EventLog ////////

        return $this->render('OlegOrderformBundle:ScanOrder:all-search-result.html.twig', array('views'=>$renderedViewArr));
    }

    public function getSearchViewArray( $routeName, $service, $filter, $search, $searchObject, $page ) {

        //***************** Search filetr ***************************//
        if( $search == "" ) {
            $viewArr = array(
                'pagination' => array(),
            );
            return $viewArr;
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo');

        $withSearch = false;
        $res = $this->getDQL( $repository, $service, $filter, $search, $routeName, $this->get('security.context'), $withSearch );

        $dql = $res['dql'];
        $criteriastrOrig = $res['criteriastr'];
        $showprovider = $res['showprovider'];
        $showproxyuser = $res['showproxyuser'];


        //Start making a search string
        $criteriastr = "";

        $searchStr = " LIKE '%" . $search . "%'";

        switch( $searchObject ) {
            case 'orderinfo.oid':
                //orderinfo oid
                //if( is_numeric($search) ) {
                $criteriastr .= "orderinfo.oid".$searchStr;
                //}
                $searchObjectName = "Order ID";
                break;
            case 'educational.courseTitleStr':
                //educational
                $criteriastr .= "educational.courseTitleStr".$searchStr;
                $searchObjectName = "Course Title";
                break;
            case 'educational.lessonTitleStr':
                //educational
                $criteriastr .= "educational.lessonTitleStr".$searchStr;
                $searchObjectName = "Lesson Title";
                break;
            case 'research.projectTitleStr':
                $criteriastr .= "research.projectTitleStr".$searchStr;
                $searchObjectName = "Research Project Title";
                break;
            case 'research.setTitleStr':
                //educational
                $criteriastr .= "research.setTitleStr".$searchStr;
                $searchObjectName = "Research Set Title";
                break;
            case 'provider':
                $criteriastr .= "provider.username".$searchStr;
                $criteriastr .= " OR provider.displayName".$searchStr;
                $searchObjectName = "Submitter";
                break;
            case 'proxyuser':
                $criteriastr .= "proxyuser.username".$searchStr;
                $criteriastr .= " OR proxyuser.displayName".$searchStr;
                $searchObjectName = "Ordering Provider";
                break;
            case 'directorUser':
                $dql->leftJoin("director.director", "directorUser");
                $criteriastr .= "directorUser.username".$searchStr;
                $criteriastr .= "OR directorUser.displayName".$searchStr;
                $searchObjectName = "Course Director";
                break;
            case 'principalUser':
                $dql->leftJoin("principal.principal", "principalUser");
                $criteriastr .= "principalUser.username".$searchStr;
                $criteriastr .= " OR principalUser.displayName".$searchStr;
                $searchObjectName = "Principal Investigator";
                break;
            case 'accession':
                $dql->leftJoin("orderinfo.accession", "accessionObj");
                $dql->leftJoin("accessionObj.accession", "accession");
                $criteriastr .= "accession.field".$searchStr;
                $searchObjectName = "Accession Number";
                break;
            case 'patient.mrn':
                $dql->leftJoin("orderinfo.patient", "patient");
                $dql->leftJoin("patient.mrn", "mrn");
                $criteriastr .= "mrn.field".$searchStr;
                $searchObjectName = "MRN";
                break;
            case 'patient.name':
                $dql->leftJoin("orderinfo.patient", "patient");
                $dql->leftJoin("patient.lastname", "lastname");
                $dql->leftJoin("patient.firstname", "firstname");
                $dql->leftJoin("patient.middlename", "middlename");
                $criteriastr .= "lastname.field".$searchStr;
                $criteriastr .= "OR firstname.field".$searchStr;
                $criteriastr .= "OR middlename.field".$searchStr;
                $searchObjectName = "Patient Name";
                break;
            case 'part.disident':
                $dql->leftJoin("orderinfo.part", "part");
                $dql->leftJoin("part.disident", "disident");
                $criteriastr .= "disident.field".$searchStr;
                $searchObjectName = "Diagnosis";
                break;
            case 'part.diffDisident':
                $dql->leftJoin("orderinfo.part", "part");
                $dql->leftJoin("part.diffDisident", "diffDisident");
                $criteriastr .= "diffDisident.field".$searchStr;
                $searchObjectName = "Differential Diagnoses";
                break;
            case 'scan.note':
                $dql->leftJoin("slides.scan", "scan");
                $criteriastr .= "scan.note".$searchStr;
                $searchObjectName = "Reason for Scan/Note";
                break;
            case 'pathistory.field':
                $dql->innerJoin("orderinfo.procedure", "procedure");
                $dql->leftJoin("procedure.pathistory", "pathistory");
                $criteriastr .= "pathistory.field".$searchStr;
                $searchObjectName = "Clinical History";
                break;
            case 'procedureType.name':
                $dql->innerJoin("orderinfo.procedure", "procedure");
                $dql->leftJoin("procedure.name", "procedureName");
                $dql->leftJoin("procedureName.field", "procedureType");
                $criteriastr .= "procedureType.name".$searchStr;
                $searchObjectName = "Procedure Type";
                break;
            case 'sectionsource.field':
                $dql->leftJoin("orderinfo.block", "block");
                $dql->leftJoin("block.sectionsource", "sectionsource");
                $criteriastr .= "sectionsource.field".$searchStr;
                $searchObjectName = "Source Organ";
                break;
            case 'description.field':
                //part Gross Description
                $dql->leftJoin("orderinfo.part", "part");
                $dql->leftJoin("part.description", "description");
                $criteriastr .= "description.field".$searchStr;
                $searchObjectName = "Gross Description";
                break;
            case 'slides.microscopicdescr':
                $criteriastr .= "slides.microscopicdescr".$searchStr;
                $searchObjectName = "Microscopic Description";
                break;
            case 'diseaseType.field':
                $dql->leftJoin("orderinfo.part", "part");
                $dql->leftJoin("part.diseaseType", "diseaseType");
                $criteriastr .= "diseaseType.field".$searchStr;
                $searchObjectName = "Disease Type";
                break;
            case 'StainList.name':
                $dql->innerJoin("slides.stain", "stain");
                $dql->leftJoin("stain.field", "StainList");
                $criteriastr .= "StainList.name".$searchStr;
                $searchObjectName = "Stain Name";
                break;
            case 'specialStains.field':
                //Special Stain Results (both stain name and the result field)
                $dql->leftJoin("orderinfo.block", "block");
                $dql->leftJoin("block.specialStains", "specialStains");
                $dql->leftJoin("specialStains.staintype", "specialStainsStainList");
                $criteriastr .= "specialStainsStainList.name".$searchStr;
                $criteriastr .= " OR specialStains.field".$searchStr;
                $searchObjectName = "Special Stain Results";
                break;
            case 'clinicalHistory.field':
                //Clinical Summary
                $dql->leftJoin("orderinfo.patient", "patient");
                $dql->leftJoin("patient.clinicalHistory", "clinicalHistory");
                $criteriastr .= "clinicalHistory.field".$searchStr;
                $searchObjectName = "Clinical Summary";
                break;
            default:
                $searchObjectName = "";
                //echo "searchObject is not found = ".$searchObject."<br>";
        }

        //$criteriastr .= " ) ";

        $increaseMaxExecTime = true;

        if( $criteriastr != "" ) {

            if( $criteriastrOrig != "" ) {
                $criteriastrOrig = $criteriastrOrig . " AND ( " . $criteriastr . " ) ";
            } else {
                $criteriastrOrig = $criteriastr;
            }

            $dql->where($criteriastrOrig);
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

        if( $increaseMaxExecTime ) {
            $max_exec_time = ini_get('max_execution_time');
            ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        }


        $limit = $this->limit;

        if( !$page && $page == "" ) {
            $page = 1;
        }

        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', $page), /*page number*/
            $limit/*limit per page*/
        );

        //check for active user requests
        $accountreqs = $this->getActiveAccountReq();

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        $processorComments = $em->getRepository('OlegOrderformBundle:ProcessorComments')->findAll();

        if( $increaseMaxExecTime ) {
            ini_set('max_execution_time', $max_exec_time); //set back to the original value
        }

//        if( $searchObject == "accession" ) {
//            //echo "dql=".$dql."<br>";
//
//        }
        //echo $searchObjectName.": count=".count($pagination)."<br>";

        $viewArr = array(
            'showprovider' => $showprovider,
            'showproxyuser' => $showproxyuser,
            'pagination' => $pagination,
            'accountreqs' => $accountreqs,
            'accessreqs' => $accessreqs,
            'routename' => $routeName,
            'comments' => $processorComments,
            'searchObjectName' => $searchObjectName,
            'search' => $search,
            'limit' => $limit
        );

//        return $this->render('OlegOrderformBundle:ScanOrder:one-search-result.html.twig', array(
//            //'form' => $form->createView(),
//            'showprovider' => $showprovider,
//            'showproxyuser' => $showproxyuser,
//            'pagination' => $pagination,
//            'accountreqs' => $accountreqs,
//            'accessreqs' => $accessreqs,
//            'routename' => $routeName,
//            'comments' => $processorComments,
//            'searchObjectName' => $searchObjectName,
//            'search' => $search
//        ));

        return $viewArr;
    }


    public function getDQL( $repository, $service, $filter, $search, $routeName, $securityContext, $withSearch = false ) {

        $em = $this->getDoctrine()->getManager();

        $user = $securityContext->getToken()->getUser();

        if( $routeName == "incoming-scan-orders" ) {
            $commentFlag = 'admin';
        } else {
            $commentFlag = null;
        }

        $dql = $repository->createQueryBuilder("orderinfo");

        $dql->innerJoin("orderinfo.slide", "slides");

        $dql->select('orderinfo, COUNT(slides.id) AS slidecount');

        $dql->groupBy('orderinfo');
        $dql->addGroupBy('status.name');
        $dql->addGroupBy('formtype.name');
        $dql->addGroupBy('provider.username');

        //$dql->having("( (COUNT(orderinfo) > 1) AND (COUNT(status.name) > 1) AND (COUNT(formtype.name) > 1) AND (COUNT(provider.username) > 1) )");
        //$dql->having("( COUNT(orderinfo) > 1 )");

        $dql->innerJoin("orderinfo.provider", "provider");
        $dql->innerJoin("orderinfo.type", "formtype");

        $dql->leftJoin("orderinfo.history", "history"); //history might not exist, so use leftJoin
        $dql->leftJoin("orderinfo.proxyuser", "proxyuser");

        $dql->leftJoin("orderinfo.educational", "educational");
        $dql->leftJoin("educational.directorWrappers", "directorWrappers");
        $dql->leftJoin("directorWrappers.director", "director");

        $dql->leftJoin("orderinfo.research", "research");
        $dql->leftJoin("research.principalWrappers", "principalWrappers");
        $dql->leftJoin("principalWrappers.principal", "principal");

        $dql->innerJoin("orderinfo.status", "status");

        //$increaseMaxExecTime = false;

        $criteriastr = "";

        //***************** Pathology Service filetr ***************************//
        $showprovider = 'false';
        $showproxyuser = 'false';

        //***************** Status filetr ***************************//
        //echo "status filter = ".$filter."<br>";
        if( $filter && is_numeric($filter) && $filter > 0 ) {

            //echo "numeric filter=".$filter;
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }
            $criteriastr .= " status.id=" . $filter;
        }

        //filter special cases
        if( is_string($filter) || $filter == "" ) {

            //echo "string filter=".$filter;

            $filterStr = "";
            switch( $filter ) {

                case "With New Comments":
                    $orderUtil = new OrderUtil($em);
                    $newCommentsCriteriaStr = "( " . $orderUtil->getCommentsCriteriaStr($securityContext,'new_comments',$commentFlag) . " ) ";
                    $filterStr = $newCommentsCriteriaStr;
                    break;
                case "With Comments":
                    $orderUtil = new OrderUtil($em);
                    $newCommentsCriteriaStr = "( " . $orderUtil->getCommentsCriteriaStr($securityContext,'all_comments',null,$commentFlag) . " ) ";
                    $filterStr = $newCommentsCriteriaStr;
                    break;
                case "All":
                    break;
                case "":
                    if( $routeName == "incoming-scan-orders" ) {
                        $filterStr = " status.name != 'Not Submitted'";
                    }
                    break;
                case "All Statuses (except Not Submitted)":
                    $filterStr = " status.name != 'Not Submitted' AND status.name != 'Superseded'";
                    break;
                case "All Filled":
                    $filterStr = " status.name LIKE '%Filled%'";
                    break;
                case "All Filled & Returned":
                    $filterStr = " status.name LIKE '%Filled%' AND status.name LIKE '%Returned%'";
                    break;
                case "All Filled & Not Returned":
                    $filterStr = " status.name LIKE '%Filled%' AND status.name NOT LIKE '%Returned%'";
                    break;
                case "All Not Filled":
                    $filterStr = " status.name NOT LIKE '%Filled%' AND status.name NOT LIKE '%Canceled%' AND status.name != 'Not Submitted' AND status.name != 'Superseded' ";
                    break;
                case "All On Hold":
                    $filterStr = " status.name LIKE '%On Hold%'";
                    break;
                case "All Canceled":
                    $filterStr = " status.name = 'Canceled by Submitter' OR status.name = 'Canceled by Processor'";
                    break;
                case "All Submitted & Amended":
                    $filterStr = " status.name = 'Submitted' OR status.name = 'Amended'";
                    break;
                case "All Stat":
                    $filterStr = " orderinfo.priority = 'Stat'";
                    break;
                case "Stat & Not Filled":
                    $filterStr = " orderinfo.priority = 'Stat' AND status.name NOT LIKE '%Filled%'";
                    break;
                case "Stat & Filled":
                    $filterStr = " orderinfo.priority = 'Stat' AND status.name LIKE '%Filled%'";
                    break;
                case "No Course Director Link":
                    $filterStr = " director.director IS NULL AND status.name != 'Superseded'";
                    break;
                case "No Principal Investigator Link":
                    $filterStr = " principal.principal IS NULL AND status.name != 'Superseded'";
                    break;
                default:
                    ;
            }

            if( $filterStr != "" ) {
                $filterStr = " (". $filterStr .") ";
            }

            if( $criteriastr != "" ) {
                $criteriastr .= " AND ". $filterStr ." ";
            } else {
                $criteriastr .= $filterStr;
            }

        }
        //***************** END of Status filetr ***************************//

        //***************** Superseded filter ***************************//
        if( false === $securityContext->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            //$superseded_status = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Superseded');
            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }
            $criteriastr .= " status.name != 'Superseded'";
        }
        //***************** END of Superseded filetr ***************************//

        //***************** User filter ***************************//
        if( $routeName == "my-scan-orders" ) {

            $crituser = "";

            //echo $routeName.": service=".$service."<br>";
            //select only orders where this user is author or proxy user, except "Where I am the Course Director" and "Where I am the Principal Investigator" cases.
            if( $service == "" || $service == "My Orders" ) {

                //show only my order and the orders where I'm a proxy
                //Where I am the Submitter and Where I am the Ordering Provider: $service == "My Orders"

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

            //show all for ROLE_SCANORDER_DIVISION_CHIEF: remove all user's restriction
            if( $securityContext->isGranted('ROLE_SCANORDER_DIVISION_CHIEF') ) {
                //echo "ROLE_SCANORDER_DIVISION_CHIEF";
                $crituser = "";
            }

            if( $service == "Where I am the Submitter" ) {
                //echo "Where I am the Submitter <br>";
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
                $crituser .= "director.director=".$user->getId();
            }
            if( $service == "Where I am the Principal Investigator" ) {
                if( $crituser != "" ) {
                    $crituser .= " AND ";
                }
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


        //***************** Search filetr ***************************//
        if( $withSearch && $search != "" ) {

            if( $criteriastr != "" ) {
                $criteriastr .= " AND ";
            }

            $criteriastr .= " ( ";

            $searchStr = " LIKE '%" . $search . "%'";

            if( is_numeric($search) ) {
                $criteriastr .= "orderinfo.oid=".$search;
                $criteriastr .= " OR ";
            }

            //educational
            $criteriastr .= " educational.courseTitleStr".$searchStr;
            $criteriastr .= " OR educational.lessonTitleStr".$searchStr;

            //Course Director
            $dql->leftJoin("director.director", "directorUser");
            $criteriastr .= " OR directorUser.username".$searchStr;
            $criteriastr .= " OR directorUser.displayName".$searchStr;

            //reasearch
            $criteriastr .= " OR research.projectTitleStr".$searchStr;
            $criteriastr .= " OR research.setTitleStr".$searchStr;

            //Principal Investigator
            $dql->leftJoin("principal.principal", "principalUser");
            $criteriastr .= " OR principalUser.username".$searchStr;
            $criteriastr .= " OR principalUser.displayName".$searchStr;

            //Submitter
            $criteriastr .= " OR provider.username".$searchStr;
            $criteriastr .= " OR provider.displayName".$searchStr;

            //Ordering Provider
            $criteriastr .= " OR proxyuser.username".$searchStr;
            $criteriastr .= " OR proxyuser.displayName".$searchStr;

            //accession
            $dql->leftJoin("orderinfo.accession", "accessionObj");
            $dql->leftJoin("accessionObj.accession", "accession");
            $criteriastr .= " OR accession.field".$searchStr;

            //MRN
            $dql->leftJoin("orderinfo.patient", "patient");
            $dql->leftJoin("patient.mrn", "mrn");
            $criteriastr .= " OR mrn.field".$searchStr;

            //patient last name
            $dql->leftJoin("patient.lastname", "lastname");
            $criteriastr .= " OR lastname.field".$searchStr;

            //patient first name
            $dql->leftJoin("patient.firstname", "firstname");
            $criteriastr .= " OR firstname.field".$searchStr;

            //Diagnosis
            $dql->leftJoin("orderinfo.part", "part");
            $dql->leftJoin("part.disident", "disident");
            $criteriastr .= " OR disident.field".$searchStr;

            //Differential Diagnoses
            $dql->leftJoin("part.diffDisident", "diffDisident");
            $criteriastr .= " OR diffDisident.field".$searchStr;

            //Reason for Scan/Note
            $dql->leftJoin("slides.scan", "scan");
            $criteriastr .= " OR scan.note".$searchStr;

            //Clinical History
            $dql->innerJoin("orderinfo.procedure", "procedure");
            $dql->leftJoin("procedure.pathistory", "pathistory");
            $criteriastr .= " OR pathistory.field".$searchStr;

            //Procedure Type
            $dql->leftJoin("procedure.name", "procedureName");
            $dql->leftJoin("procedureName.field", "procedureType");
            $criteriastr .= " OR procedureType.name".$searchStr;

            //Source Organ
            $dql->leftJoin("orderinfo.block", "block");
            $dql->leftJoin("block.sectionsource", "sectionsource");
            $criteriastr .= " OR sectionsource.field".$searchStr;

            //part Gross Description
            $dql->leftJoin("part.description", "description");
            $criteriastr .= " OR description.field".$searchStr;

            //Microscopic Description
            $criteriastr .= " OR slides.microscopicdescr".$searchStr;

            //Disease Type [Neoplastic, non-neoplastic, metastatic]
            $dql->leftJoin("part.diseaseType", "diseaseType");
            $criteriastr .= " OR diseaseType.field".$searchStr;

            //Stain Name
            $dql->innerJoin("slides.stain", "stain");
            $dql->leftJoin("stain.field", "StainList");
            $criteriastr .= " OR StainList.name".$searchStr;

            //Special Stain Results (both stain name and the result field)
            $dql->leftJoin("block.specialStains", "specialStains");
            $dql->leftJoin("specialStains.staintype", "specialStainsStainList");
            $criteriastr .= " OR specialStainsStainList.name".$searchStr;
            $criteriastr .= " OR specialStains.field".$searchStr;

            //Clinical Summary
            $dql->leftJoin("patient.clinicalHistory", "clinicalHistory");
            $criteriastr .= " OR clinicalHistory.field".$searchStr;

            $criteriastr .= " ) ";

            //$increaseMaxExecTime = true;
        }
        //***************** END of Search filetr ***************************//

        /////////// institution ///////////
        $instStr = "";
        foreach( $user->getInstitution() as $inst ) {
            if( $instStr != "" ) {
                $instStr = $instStr . " OR ";
            }
            $instStr = $instStr . 'orderinfo.institution='.$inst->getId();
        }
        if( $instStr == "" ) {
            $instStr = "1=0";
        }
        if( $criteriastr != "" ) {
            $criteriastr = $criteriastr . " AND (" . $instStr . ") ";
        } else {
            $criteriastr = $criteriastr . " (" . $instStr . ") ";
        }
        /////////// EOF institution ///////////

        //echo "<br>criteriastr=".$criteriastr."<br>";

        $res = array();
        $res['dql'] = $dql;
        $res['criteriastr'] = $criteriastr;
        $res['showprovider'] = $showprovider;
        $res['showproxyuser'] = $showproxyuser;


        return $res;
    }

}
