<?php

namespace Oleg\OrderformBundle\Controller;

use Oleg\OrderformBundle\Entity\Endpoint;
use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Form\OrderInfoType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\OrderformBundle\Form\SlideReturnRequestType;
use Oleg\OrderformBundle\Form\FilterSlideReturnRequestType;
use Oleg\OrderformBundle\Entity\SlideReturnRequest;
use Oleg\OrderformBundle\Security\Util\SecurityUtil;
use Oleg\OrderformBundle\Entity\History;
use Oleg\OrderformBundle\Entity\SlideText;
use Oleg\UserdirectoryBundle\Util\UserUtil;


/**
 * Scan controller.
 */
class SlideReturnRequestController extends Controller
{

    /**
     * Creates a new Request Slide Return with just slide names such as Accession Number
     *
     * @Route("/slide-return-request", name="slide-return-request-table")
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:create-table.html.twig")
     */
    public function newRequestSlideReturnTableAction(Request $request)
    {

        $user = $this->get('security.context')->getToken()->getUser();

        $orderUtil = $this->get('scanorder_utility');

        //check if user has at least one institution
        $securityUtil = $this->get('order_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }

        $orderinfo = new OrderInfo();

        //set destination
        $destination = new Endpoint();
        $orderinfo->addDestination($destination);

        $slideReturnRequest  = new SlideReturnRequest();

        //$orderinfo->setSlideReturnRequest($slideReturnRequest);
        $slideReturnRequest->setOrderinfo($orderinfo);

        $slideReturnRequest->getOrderinfo()->setProvider($user);
        $slideReturnRequest->getOrderinfo()->setProxyuser($user);
        $slideReturnRequest->getOrderinfo()->setReturnoption(true);

        $securityUtil = $this->get('order_security_utility');
        $permittedInst = $securityUtil->getUserPermittedInstitutions($user);

//        $params = array(
//            'user'=>$user,
//            'type'=>'table',
//            'institutions'=>$permittedInst,
//            'cycle' => 'new',
//            'destinationLocation'=>$orderUtil->getOrderReturnLocations($orderinfo)
//        );
//        $form = $this->createForm(new SlideReturnRequestType($params,$slideReturnRequest), $slideReturnRequest);

        $params = array(
            'type'=>'table',
            'cycle'=>'new',
            'institutions'=>$permittedInst,
            'user'=>$user,
            'destinationLocation'=>$orderUtil->getOrderReturnLocations($orderinfo),
            'datastructure'=>null
        );
        $form   = $this->createForm( new OrderInfoType($params, $orderinfo), $orderinfo );

        return array(
            'form' => $form->createView(),
            'cycle' => 'new'
        );
    }

    /**
     * @Route("/slide-return-request/submit", name="slide-return-request-table-submit")
     * @Method("POST")
     * @Template("OlegOrderformBundle:SlideReturnRequest:create-table.html.twig")
     */
    public function submitRequestSlideReturnTableAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $formtype = $em->getRepository('OlegOrderformBundle:FormType')->findOneByName("Slide Return Request");
        $orderUtil = $this->get('scanorder_utility');

        $orderinfo = new OrderInfo();
        $slideReturnRequest  = new SlideReturnRequest();
        $orderinfo->setSlideReturnRequest($slideReturnRequest);

        $slideReturnRequest->getOrderinfo()->setProvider($user);

        $slideReturnRequest->setStatus('active');

        $slideReturnRequest->getOrderinfo()->setType($formtype);

        $securityUtil = $this->get('order_security_utility');
        $permittedInst = $securityUtil->getUserPermittedInstitutions($user);

        //$params = array('user'=>$user,'type'=>'table', 'institutions'=>$permittedInst);
        //$form = $this->createForm(new SlideReturnRequestType($params,$slideReturnRequest), $slideReturnRequest);

        $params = array(
            'type'=>'table',
            'cycle'=>'create',
            'institutions'=>$permittedInst,
            'user'=>$user,
            'destinationLocation'=>$orderUtil->getOrderReturnLocations($orderinfo),
            'datastructure'=>null
        );
        $form   = $this->createForm( new OrderInfoType($params, $orderinfo), $orderinfo );

        $form->handleRequest($request);

        //echo "<br>errors:<br>";
        //print_r($form->getErrors());
        //var_dump($form->getErrors());die;
        //echo "<br>errors:<br>";
        //print_r($form->getErrorsAsString());
        //echo "<br>";
        //exit("controller exit");

        if( $form->isValid() ) {
            //echo "form is valid !!! <br>";

            //////////////// process handsontable rows ////////////////
            $datajson = $form->get('slideReturnRequest')->get('datalocker')->getData();

            $data = json_decode($datajson, true);
            //var_dump($data);

            if( $data == null ) {
                throw new \Exception( 'Table order data is null.' );
            }

            $rowCount = 0;
            $headers = array_shift($data);

            foreach( $data as $row ) {
                //var_dump($row);
                //echo "<br>";

                $accValue = $this->getValueByHeaderName('Accession Number',$row,$headers);
                //echo "accValue=".$accValue."<br>";

                if( !$accValue || $accValue == '' ) {
                    continue;   //skip row if accession number is empty
                }

                $slideText = new SlideText();
                $slideText->setMrntype( $this->getValueByHeaderName('MRN Type',$row,$headers) );
                $slideText->setMrn( $this->getValueByHeaderName("Patient's MRN",$row,$headers) );
                $slideText->setPatientlastname( $this->getValueByHeaderName("Patient's Last Name",$row,$headers) );
                $slideText->setPatientfirstname( $this->getValueByHeaderName("Patient's First Name",$row,$headers) );
                $slideText->setPatientmiddlename( $this->getValueByHeaderName("Patient's Middle Name",$row,$headers) );
                $slideText->setAccessiontype( $this->getValueByHeaderName("Accession Type",$row,$headers) );
                $slideText->setAccession( $this->getValueByHeaderName("Accession Number",$row,$headers) );
                $slideText->setPart( $this->getValueByHeaderName("Part",$row,$headers) );
                $slideText->setBlock( $this->getValueByHeaderName("Block",$row,$headers) );
                $slideText->setStain( $this->getValueByHeaderName("Stain",$row,$headers) );

                $slideReturnRequest->addSlidetext($slideText);

                //echo $rowCount.": accType=".$row[0].", acc=".$row[1]." \n ";
                $rowCount++;

            }//foreach row


            if( $rowCount > 0 ) {
                $em->persist($slideReturnRequest);
                $em->flush();
            }

            //exit();

            return $this->redirect($this->generateUrl('my-slide-return-requests'));

        } else {
            //exit("form is not valid ??? <br>");
            //echo "form is not valid ??? <br>";
            throw new \Exception( 'Form was altered' );
        }

    }

    public function getValueByHeaderName($header, $row, $headers) {
        $key = array_search($header, $headers);
        return $row[$key];
    }

    /**
     * Lists all Slides for this order with oid=$id.
     *
     * @Route("/slide-return-request/{id}", name="slide-return-request", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:create.html.twig")
     */
    public function indexAction( $id ) {

        //check if the user has permission to view this order
        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $orderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

        if( !$orderinfo ) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity with id='.$id);
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $securityUtil = $this->get('order_security_utility');
        if( $orderinfo && !$securityUtil->isUserAllowOrderActions($orderinfo, $user, array('show')) ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        //TODO: test it
        //$orderinfo = new OrderInfo();
        $slideReturnRequest  = new SlideReturnRequest();
        $orderinfo->setSlideReturnRequest($slideReturnRequest);

        $slideReturnRequest->getOrderinfo()->setProvider($user);
        $slideReturnRequest->getOrderinfo()->setProxyuser($user);

        $securityUtil = $this->get('order_security_utility');
        $permittedInst = $securityUtil->getUserPermittedInstitutions($user);

        $orderUtil = $this->get('scanorder_utility');

        $params = array(
            'user'=>$user,
            'institutions'=>$permittedInst,
            'cycle' => 'new',
            'destinationLocation'=>$orderUtil->getOrderReturnLocations($orderinfo)
        );
        $form = $this->createForm(new SlideReturnRequestType($params,$slideReturnRequest), $slideReturnRequest);

        return array(
            'orderinfo' => $orderinfo,
            'form' => $form->createView(),  //SlideReturnRequest form
            'cycle' => 'new'
        );
    }

    /**
     * Creates a new SlideReturnRequest. id - order oid
     *
     * @Route("/slide-return-request/{id}", name="slide-return-request_create", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("OlegOrderformBundle:SlideReturnRequest:create.html.twig")
     */
    public function createSlideReturnRequestAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();

        $slideReturnRequest  = new SlideReturnRequest();

        $user = $this->get('security.context')->getToken()->getUser();
        $slideReturnRequest->getOrderinfo()->setProvider($user);

        $slideReturnRequest->setStatus('active');

        $formtype = $em->getRepository('OlegOrderformBundle:FormType')->findOneByName("Slide Return Request");
        $slideReturnRequest->getOrderinfo()->setType($formtype);

        $securityUtil = $this->get('order_security_utility');
        $permittedInst = $securityUtil->getUserPermittedInstitutions($user);

        $params = array('user'=>$user, 'institutions'=>$permittedInst);
        $form = $this->createForm(new SlideReturnRequestType($params,$slideReturnRequest), $slideReturnRequest);

        $form->handleRequest($request);

        if( $form->isValid() ) {
            //echo "form is valid !!! <br>";

            $orderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);
            $slideReturnRequest->setOrderinfo($orderinfo);

            $slides = $slideReturnRequest->getSlide();

            //replace form's slide with DB slide => this way new slide will not be created
            $fullStainArr = array();
            foreach( $slides as $slide ) {
                //echo "slide=".$slide->getId()."<br>";
                $slideReturnRequest->removeSlide($slide);
                $slideDb =  $em->getRepository('OlegOrderformBundle:Slide')->findOneById($slide->getId());
                $slideReturnRequest->addSlide($slideDb);

                //stains string
                $stainArr = array();
                foreach( $slideDb->getStain() as $stain ) {
                    $stainArr[] = $stain."";
                }
                //echo "implode1:".implode(",", $stainArr)."<br>";
                $fullStainArr[] = implode(",", $stainArr);
            }

            if( $slides && count($slides) > 0 ) {
                $em->persist($slideReturnRequest);
                $em->flush();
            }

            //record history
            $orderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);
            $history = new History();
            $history->setOrderinfo($orderinfo);
            $history->setCurrentid($orderinfo->getOid());
            $history->setCurrentstatus($orderinfo->getStatus());
            $history->setProvider($user);
            $history->setRoles($user->getRoles());

            $notemsg = 'Slide Return Request has been made for '.count($slides) . ' slide(s):<br>'.implode("<br>",$slideReturnRequest->getSlideDescription($user));
            $history->setNote($notemsg);

            $eventtype = $em->getRepository('OlegOrderformBundle:ProgressCommentsEventTypeList')->findOneByName('Initial Slide Return Request Submission');
            $history->setEventtype($eventtype);

            $em->persist($history);
            $em->flush();

            return $this->redirect($this->generateUrl('my-slide-return-requests'));

        } else {
            //exit("form is not valid ??? <br>");
            //echo "form is not valid ??? <br>";
            throw new \Exception( 'Form was altered' );
        }

    }



    /**
     * Lists all Slides requested for return for Admin.
     *
     * @Route("/incoming-slide-return-requests", name="incoming-slide-return-requests")
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index.html.twig")
     */
    public function allRequestedSlidesAction( Request $request ) {

        $filterForm = $this->createForm(new FilterSlideReturnRequestType());

        //$filterForm->handleRequest($request);
        $filterForm->bind($request); //use bind. handleRequest does not work with GET

        $filter = $filterForm->get('filter')->getData();
        //echo "filter = ".$filter."<br>";

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:SlideReturnRequest');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list, COUNT(slides) as slidecount');

        $dql->leftJoin("list.slide", "slides");
        $dql->leftJoin('list.provider','provider');
        $dql->leftJoin('list.orderinfo','orderinfo');
        $dql->leftJoin('list.institution','institution');
        $dql->leftJoin('list.proxyuser','proxyuser');
        $dql->leftJoin("list.returnLocation", "returnLocation");

        $dql->groupBy('list');
        $dql->addGroupBy('provider.username');
        $dql->addGroupBy('proxyuser.username');
        $dql->addGroupBy('orderinfo.id');
        $dql->addGroupBy('institution.name');
        $dql->addGroupBy('returnLocation.name');

		$postData = $request->query->all();
		
		if( !isset($postData['sort']) ) { 
			$dql->orderBy('list.orderdate','DESC');
		}

        $setParameter = false;
        $criteriastr = "";
        if( $filter == '' || $filter == 'all' ) {
            //no where filter: show all
        } else {
            $criteriastr = "list.status = :status";
            $setParameter = true;
        }

        $user = $this->get('security.context')->getToken()->getUser();

        /////////// institution ///////////
        $criteriastr = $this->addSlideReturnRequestInstitutionQueryCriterion($user,$criteriastr);
        /////////// EOF institution ///////////

        $dql->where($criteriastr);
		
		//pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters       
		if( isset($postData['sort']) ) {    			
            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
        }
		
        //echo "dql=".$dql;

        $limit = 30;
        $query = $em->createQuery($dql);

        if( $setParameter ) {
            $query->setParameter('status',$filter);
        }				

        $paginator  = $this->get('knp_paginator');
        $sliderequests = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        return array(
            'sliderequests' => $sliderequests,
            'filter' => $filterForm->createView(),
            'route' => $request->get('_route'),
            'routename' => $request->get('_route')
        );
    }

    //local function used only in Slide Return Request Controller
    public function getSlideReturnRequestInstitutionQueryCriterion($user) {
        $securityUtil = $this->container->get('order_security_utility');
        $institutions = $securityUtil->getUserPermittedInstitutions($user);
        $instStr = "";
        foreach( $institutions as $inst ) {
            if( $instStr != "" ) {
                $instStr = $instStr . " OR ";
            }
            $instStr = $instStr . 'institution='.$inst->getId();
        }
        if( $instStr == "" ) {
            $instStr = "1=0";
        }
        return $instStr;
    }
    public function addSlideReturnRequestInstitutionQueryCriterion($user,$criteriastr) {
        $instStr = $this->getSlideReturnRequestInstitutionQueryCriterion($user);
        if( $instStr != "" ) {
            if( $criteriastr != "" ) {
                $criteriastr = $criteriastr . " AND (" . $instStr . ") ";
            } else {
                $criteriastr = $criteriastr . " (" . $instStr . ") ";
            }
        }
        return $criteriastr;
    }


    /**
     * Lists user's Slides requested for return.
     *
     * @Route("/my-slide-return-requests", name="my-slide-return-requests")
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index.html.twig")
     */
    public function userRequestedSlidesAction( Request $request ) {

        $filterForm = $this->createForm(new FilterSlideReturnRequestType());

        //$filterForm->handleRequest($request);
        $filterForm->bind($request); //use bind. handleRequest does not work with GET

        $filter = $filterForm->get('filter')->getData();
        //echo "filter = ".$filter."<br>";

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:SlideReturnRequest');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list, COUNT(slides) as slidecount');

        $dql->groupBy('list');
        $dql->addGroupBy('provider.username');
        $dql->addGroupBy('proxyuser.username');
        $dql->addGroupBy('orderinfo');
        $dql->addGroupBy('institution.name');
        $dql->addGroupBy('destinationslocation');

        $dql->leftJoin("list.slide", "slides");
        $dql->leftJoin('list.orderinfo','orderinfo');
        $dql->innerJoin('orderinfo.provider','provider');
        $dql->innerJoin('orderinfo.institution','institution');
        $dql->leftJoin('orderinfo.proxyuser','proxyuser');
        $dql->leftJoin("orderinfo.destinations", "destinations");
        $dql->leftJoin("destinations.location", "destinationslocation");

		$postData = $request->query->all();
		
		if( !isset($postData['sort']) ) { 
			$dql->orderBy('orderinfo.orderdate','DESC');
		}

        $setParameter = false;
        if( $filter == '' || $filter == 'all' ) {
            $dql->where("provider=".$user->getId());
        } else {
            $dql->where("provider=".$user->getId()." AND list.status = :status");
            $setParameter = true;
        }

		//pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters       
		if( isset($postData['sort']) ) {    			
            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
        }
		
        //echo "dql=".$dql."<br>";

        $limit = 30;
        $query = $em->createQuery($dql);

        if( $setParameter ) {
            $query->setParameter('status',$filter);
        }				

        $paginator  = $this->get('knp_paginator');
        $sliderequests = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        return array(
            'sliderequests' => $sliderequests,
            'filter' => $filterForm->createView(),
            'routename' => $request->get('_route')
        );
    }


    /**
     * Change status
     * @Route("/slide-return-request/{id}/{status}/status", name="sliderequest_status", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index.html.twig")
     */
    public function statusAction( Request $request, $id, $status )
    {

        $url = $request->headers->get('referer');
        if( strpos($url,'my-slide-return-requests') ) {
            if( false === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ) {
                return $this->redirect( $this->generateUrl('scan-order-nopermission') );
            }
        } else {
            if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
                return $this->redirect( $this->generateUrl('scan-order-nopermission') );
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SlideReturnRequest entity.');
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $orderinfo = $entity->getOrderInfo();
        $securityUtil = $this->get('order_security_utility');
        if( $orderinfo && !$securityUtil->hasUserPermission($orderinfo,$user) ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $entity->setStatus($status);
        $em->persist($entity);
        $em->flush();


        //record history
        $orderinfo = $entity->getOrderinfo();
        if( $orderinfo ) {

            $slides = $entity->getSlide();
            $history = new History();
            $history->setOrderinfo($orderinfo);
            $history->setCurrentid($orderinfo->getOid());
            $history->setCurrentstatus($orderinfo->getStatus());
            $history->setProvider($user);
            $history->setRoles($user->getRoles());
            $notemsg = 'Status Changed to "'.ucfirst($status).'" for Slide Return Request ' . $entity->getId() . ' for '.count($slides) . ' slide(s):<br>'.implode("<br>", $entity->getSlideDescription($user));
            $history->setNote($notemsg);
            $eventtype = $em->getRepository('OlegOrderformBundle:ProgressCommentsEventTypeList')->findOneByName('Slide Return Request Status Changed');
            $history->setEventtype($eventtype);
            $em->persist($history);
            $em->flush();

        } //if orderinfo


        $filter = $request->query->get('filter');
        if( $filter && $filter != "" ) {
            $paramUrl = array('filter_search_box[filter]'=>$filter);
        } else {
            $paramUrl = array();
        }

        $url = $request->headers->get('referer');
        if( strpos($url,'my-slide-return-requests') ) {
            return $this->redirect( $this->generateUrl( 'my-slide-return-requests',$paramUrl ) );
        } else {
            return $this->redirect( $this->generateUrl( 'incoming-slide-return-requests',$paramUrl ) );
        }

    }



//    /**
//     * Add comment
//     * @Route("/slide-return-request/status-changed-comment/create", name="sliderequest_status_comment")
//     * @Method("POST")
//     */
//    public function statusWithCommentAction( Request $request ) {
//
//        $text_value = $request->request->get('text');
//        $id = $request->request->get('id');
//
//        //echo "id=".$id.", text_value=".$text_value."<br>";
//
//        $res = 1;
//
//        if( $text_value == "" ) {
//            $res = 'Comment was not provided';
//        } else {
//
//            $em = $this->getDoctrine()->getManager();
//            $slideReturnRequest = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->find($id);
//
//            if( !$slideReturnRequest ) {
//                throw $this->createNotFoundException('Unable to find SlideReturnRequest entity.');
//            }
//
//            $user = $this->get('security.context')->getToken()->getUser();
//            $slideReturnRequest->addComment($text_value, $user);
//
//            //echo "ok";
//            $em->persist($slideReturnRequest);
//            $em->flush();
//
//
//            //record history
//            $user = $this->get('security.context')->getToken()->getUser();
//            $orderinfo = $slideReturnRequest->getOrderinfo();
//            $slides = $slideReturnRequest->getSlide();
//            $history = new History();
//            $history->setEventtype('Slide Return Request Comment Added');
//            $history->setOrderinfo($orderinfo);
//            $history->setCurrentid($orderinfo->getOid());
//            $history->setCurrentstatus($orderinfo->getStatus());
//            $history->setProvider($user);
//            $history->setRoles($user->getRoles());
//            $notemsg = 'Comment added to Slide Return Request '.$id.' for '.count($slides) . ' slide(s):<br>'.implode("<br>", $slideReturnRequest->getSlideDescription($user));
//            $history->setNote($notemsg);
//            $em->persist($history);
//            $em->flush();
//
//        }
//
//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode($res));
//        return $response;
//
//    }


    //@Template("OlegOrderformBundle:SlideReturnRequest:index.html.twig")
    /**
     * @Route("/slide-return-request/comment/create", name="slide-return-request-comment-create")
     * @Method("POST")
     */
    public function createSlideReturnRequestCommentAction(Request $request)
    {

        $text_value = $request->request->get('text');
        $id = $request->request->get('id');
        //echo "id=".$id.", text_value=".$text_value."<br>";

        $res = 1;

        if( $text_value == "" ) {
            $res = 'Comment was not provided';
        } else {

            $em = $this->getDoctrine()->getManager();
            $user = $this->get('security.context')->getToken()->getUser();

            $slideReturnRequest = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->find($id);

            $slideReturnRequest->addComment($text_value, $user);

            //echo "ok";
            $em->persist($slideReturnRequest);
            $em->flush();

            //record history
            $orderinfo = $slideReturnRequest->getOrderinfo();
            if( $orderinfo ) {

                $user = $this->get('security.context')->getToken()->getUser();
                $slides = $slideReturnRequest->getSlide();
                $history = new History();
                $history->setOrderinfo($orderinfo);
                $history->setCurrentid($orderinfo->getOid());
                $history->setCurrentstatus($orderinfo->getStatus());
                $history->setProvider($user);
                $history->setRoles($user->getRoles());

                $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y \a\t G:i');
                $dateStr = $transformer->transform(new \DateTime());
                $commentFull = $user . " on " . $dateStr. ": " . $text_value;
                $notemsg = 'Comment added to Slide Return Request '.$id.' for '.count($slides) . ' slide(s):<br>'.implode("<br>", $slideReturnRequest->getSlideDescription($user));
                $history->setNote($notemsg."<br>".$commentFull);

                $eventtype = $em->getRepository('OlegOrderformBundle:ProgressCommentsEventTypeList')->findOneByName('Slide Return Request Comment Added');
                $history->setEventtype($eventtype);

                $em->persist($history);
                $em->flush();

            } //if orderinfo

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }

    
}
