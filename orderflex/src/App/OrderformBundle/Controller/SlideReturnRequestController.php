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

namespace App\OrderformBundle\Controller;

use App\OrderformBundle\Entity\Endpoint;
use App\OrderformBundle\Entity\Message;
use App\OrderformBundle\Form\MessageType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use App\OrderformBundle\Form\SlideReturnRequestType;
use App\OrderformBundle\Form\FilterSlideReturnRequestType;
use App\OrderformBundle\Entity\SlideReturnRequest;
use App\OrderformBundle\Security\Util\SecurityUtil;
use App\OrderformBundle\Entity\History;
use App\OrderformBundle\Entity\SlideText;
use App\UserdirectoryBundle\Util\UserUtil;


/**
 * Scan controller.
 */
class SlideReturnRequestController extends Controller
{

    /**
     * Creates a new Request Slide Return with just slide names such as Accession Number
     *
     * @Route("/slide-return-request/new", name="slide-return-request-table")
     * @Method("GET")
     * @Template("AppOrderformBundle/SlideReturnRequest/create-table.html.twig")
     */
    public function newRequestSlideReturnTableAction(Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();

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

        $params = array( 'type'=>'table', 'cycle'=>'new');
        $slideReturnRequest  = new SlideReturnRequest();

        $form = $this->constractSlideRequestForm($slideReturnRequest,$params,null);

        return array(
            'form' => $form->createView(),
            'cycle' => 'new'
        );
    }

    /**
     * @Route("/slide-return-request/submit", name="slide-return-request-table-submit")
     * @Method("POST")
     * @Template("AppOrderformBundle/SlideReturnRequest/create-table.html.twig")
     */
    public function submitRequestSlideReturnTableAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $slideReturnRequest = new SlideReturnRequest();
        $slideReturnRequest->setStatus('active');

        $params = array( 'type'=>'table', 'cycle'=>'create');
        $form = $this->constractSlideRequestForm($slideReturnRequest,$params,null);

        $form->handleRequest($request);

        //echo $form->getErrors()."<br>";
        //exit("controller exit");

        if( $form->isValid() ) {
            //echo "form is valid !!! <br>";

            //////////////// process handsontable rows ////////////////
            //$datajson = $form->get('slideReturnRequest')->get('datalocker')->getData();
            $datajson = $form->get('datalocker')->getData();

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

                $accessionTypeStr = $this->getValueByHeaderName("Accession Type",$row,$headers);
                $accessionStr = $this->getValueByHeaderName("Accession Number",$row,$headers);
                $partStr = $this->getValueByHeaderName("Part",$row,$headers);
                $blockStr = $this->getValueByHeaderName("Block",$row,$headers);

                $slideText = new SlideText();
                $slideText->setMrntype( $this->getValueByHeaderName('MRN Type',$row,$headers) );
                $slideText->setMrn( $this->getValueByHeaderName("Patient's MRN",$row,$headers) );
                $slideText->setPatientlastname( $this->getValueByHeaderName("Patient's Last Name",$row,$headers) );
                $slideText->setPatientfirstname( $this->getValueByHeaderName("Patient's First Name",$row,$headers) );
                $slideText->setPatientmiddlename( $this->getValueByHeaderName("Patient's Middle Name",$row,$headers) );
                $slideText->setAccessiontype( $accessionTypeStr );
                $slideText->setAccession( $accessionStr );
                $slideText->setPart( $partStr );
                $slideText->setBlock( $blockStr );
                $slideText->setStain( $this->getValueByHeaderName("Stain",$row,$headers) );

                $slideReturnRequest->addSlidetext($slideText);

                //set this slide as order input
                $institution = $slideReturnRequest->getMessage()->getInstitution()->getId();


                if( $slideReturnRequest->getReturnoption() ) {
                    $slides = $em->getRepository('AppOrderformBundle:Slide')->findSlidesByInstAccession($institution,$accessionTypeStr,$accessionStr);
                } else {
                    $slides = $em->getRepository('AppOrderformBundle:Slide')->findSlidesByInstAccessionPartBlock($institution,$accessionTypeStr,$accessionStr,$partStr,$blockStr);
                }

                //echo "slides count=".count($slides)."<br>";
                //exit('1');

                foreach( $slides as $slide ) {
                    $slideReturnRequest->getMessage()->addInputObject($slide);
                }

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
     * @Template("AppOrderformBundle/SlideReturnRequest/create.html.twig")
     */
    public function indexAction( $id ) {

        //check if the user has permission to view this order
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $slideReturnRequest  = new SlideReturnRequest();

        $params = array( 'cycle'=>'new');
        $form = $this->constractSlideRequestForm($slideReturnRequest,$params,$id);

        return array(
            'form' => $form->createView(),  //SlideReturnRequest form
            'cycle' => $params['cycle']
        );
    }

    /**
     * Creates a new SlideReturnRequest. id - order oid
     *
     * @Route("/slide-return-request/{id}", name="slide-return-request_create", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("AppOrderformBundle/SlideReturnRequest/create.html.twig")
     */
    public function createSlideReturnRequestAction(Request $request, $id)
    {

        $slideReturnRequest  = new SlideReturnRequest();
        $slideReturnRequest->setStatus('active');

        $params = array( 'cycle'=>'create');
        $form = $this->constractSlideRequestForm($slideReturnRequest,$params,null);

        $form->handleRequest($request);

//        echo $form->getErrors()."<br>";
        //exit("controller exit");

        if( $form->isValid() ) {
            //echo "form is valid !!! <br>";

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();

            //add message with specified id to associated objects to message
            $associationMessage = $em->getRepository('AppOrderformBundle:Message')->findOneByOid($id);
            $slideReturnRequest->getMessage()->addAssociation($associationMessage);

            $slides = $associationMessage->getSlide();
            //echo "slide request slide count=".count($slides)."<br>";

            //replace form's slide with DB slide => this way new slide will not be created
            foreach( $slides as $slide ) {

                //add slide to slide return request order (however, we have these slides in $associationMessage)
                $slideReturnRequest->getMessage()->removeSlide($slide);
                $slideDb =  $em->getRepository('AppOrderformBundle:Slide')->findOneById($slide->getId());
                $slideReturnRequest->getMessage()->addSlide($slideDb);

                //set this slide as order input
                $slideReturnRequest->getMessage()->addInputObject($slide);
            }

            //exit('1');

            if( $slides && count($slides) > 0 ) {
                $em->persist($slideReturnRequest);
                $em->flush();
            }

            //record history
            $message = $em->getRepository('AppOrderformBundle:Message')->findOneByOid($id);
            $history = new History();
            $history->setMessage($message);
            $history->setCurrentid($message->getOid());
            $history->setCurrentstatus($message->getStatus());
            $history->setProvider($user);
            $history->setRoles($user->getRoles());

            $notemsg = 'Slide Return Request has been made for '.count($slides) . ' slide(s):<br>'.implode("<br>",$slideReturnRequest->getSlideDescription($user));
            $history->setNote($notemsg);

            $eventtype = $em->getRepository('AppOrderformBundle:ProgressCommentsEventTypeList')->findOneByName('Initial Slide Return Request Submission');
            $history->setEventtype($eventtype);

            $em->persist($history);
            $em->flush();

            return $this->redirect($this->generateUrl('my-slide-return-requests'));

        } else {
            //exit("form is not valid ??? <br>");
            //echo "form is not valid ??? <br>";
            throw new \Exception( 'Form was altered' );
        }

        //return $this->redirect($this->generateUrl('slide-return-request_create',array('id'=>$id)));
    }

    public function constractSlideRequestForm($slideReturnRequest,$params,$scanorderId) {

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        if( $scanorderId ) {

            $message = $em->getRepository('AppOrderformBundle:Message')->findOneByOid($scanorderId);

            if( !$message ) {
                throw $this->createNotFoundException('Unable to find Message (Scan Order) entity with id='.$scanorderId);
            }

            $securityUtil = $this->get('order_security_utility');
            if( $message && !$securityUtil->isUserAllowOrderActions($message, $user, array('show')) ) {
                return $this->redirect( $this->generateUrl('scan-nopermission') );
            }
        } else {
            $message = new Message();

            //set category
            $category = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName("Slide Return Request");
            $message->setMessageCategory($category);

            //set destination
            $destination = new Endpoint();
            $message->addDestination($destination);
        }

//        echo "slide count=".count($message->getSlide())."<br>";
//        foreach( $message->getSlide() as $slide ) {
//            echo $slide->getId()."<br>";
//        }

        //assign message
        $slideReturnRequest->setMessage($message);

        $slideReturnRequest->getMessage()->setProvider($user);
        $orderUtil = $this->get('scanorder_utility');
        $orderUtil->setLastOrderWithProxyuser($user,$slideReturnRequest->getMessage());

        $securityUtil = $this->get('order_security_utility');
        $permittedInst = $securityUtil->getUserPermittedInstitutions($user);

        $permittedInst = $orderUtil->getAllScopeInstitutions($permittedInst,$message);

        $orderUtil = $this->get('scanorder_utility');

        $params['em'] = $this->getDoctrine()->getManager();
        $params['user'] = $user;
        $params['institutions'] = $permittedInst;
        $params['destinationLocation'] = $orderUtil->getOrderReturnLocations($message);


        $form = $this->createForm(SlideReturnRequestType::class, $slideReturnRequest, array('form_custom_value'=>$params));

        return $form;
    }






    /**
     * Lists all Slides requested for return for Admin.
     *
     * @Route("/incoming-slide-return-requests", name="incoming-slide-return-requests")
     * @Method("GET")
     * @Template("AppOrderformBundle/SlideReturnRequest/index.html.twig")
     */
    public function allRequestedSlidesAction( Request $request ) {

        $filterForm = $this->createForm(FilterSlideReturnRequestType::class);

        //$filterForm->handleRequest($request);
        $filterForm->submit($request); //use bind. handleRequest does not work with GET

        $filter = $filterForm->get('filter')->getData();
        //echo "filter = ".$filter."<br>";

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('AppOrderformBundle:SlideReturnRequest');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list, COUNT(slides) as slidecount');

        $dql->leftJoin('list.message','message');
        $dql->leftJoin("message.slide", "slides");
        $dql->leftJoin('message.provider','provider');
        $dql->leftJoin('message.institution','institution');
        $dql->leftJoin('message.destinations','destinations');
        $dql->leftJoin("destinations.location", "destinationslocation");
        $dql->leftJoin('message.associations','associations');
        $dql->leftJoin("message.proxyuser", "proxyuserWrapper");
        $dql->leftJoin("proxyuserWrapper.user", "proxyuser");

        $dql->groupBy('list');
        $dql->addGroupBy('provider');
        $dql->addGroupBy('proxyuser');
        $dql->addGroupBy('message');
        $dql->addGroupBy('institution');
        $dql->addGroupBy('associations');
        $dql->addGroupBy('destinationslocation');

		$postData = $request->query->all();
		
		if( !isset($postData['sort']) ) { 
			$dql->orderBy('message.orderdate','DESC');
		}

        $setParameter = false;
        $criteriastr = "";
        if( $filter == '' || $filter == 'all' ) {
            //no where filter: show all
        } else {
            $criteriastr = "list.status = :status";
            $setParameter = true;
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /////////// institution ///////////
        $criteriastr = $this->addSlideReturnRequestInstitutionQueryCriterion($user,$criteriastr);
        /////////// EOF institution ///////////

        $dql->where($criteriastr);
		
		//pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters       
//		if( isset($postData['sort']) ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }
		
        //echo "dql=".$dql;

        $limit = 30;
        $query = $em->createQuery($dql);

        if( $setParameter ) {
            $query->setParameter('status',$filter);
        }				

        $paginator  = $this->get('knp_paginator');
        $sliderequests = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
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
     * @Template("AppOrderformBundle/SlideReturnRequest/index.html.twig")
     */
    public function userRequestedSlidesAction( Request $request ) {

        $filterForm = $this->createForm(FilterSlideReturnRequestType::class);

        //$filterForm->handleRequest($request);
        $filterForm->submit($request); //use bind. handleRequest does not work with GET

        $filter = $filterForm->get('filter')->getData();
        //echo "filter = ".$filter."<br>";

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $repository = $this->getDoctrine()->getRepository('AppOrderformBundle:SlideReturnRequest');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list, COUNT(slides) as slidecount');

        $dql->groupBy('list');
        $dql->addGroupBy('provider');
        $dql->addGroupBy('proxyuser');
        $dql->addGroupBy('message');
        $dql->addGroupBy('institution');
        $dql->addGroupBy('destinationslocation');
        $dql->addGroupBy('associations');

        $dql->leftJoin('list.message','message');
        $dql->leftJoin("message.slide", "slides");
        $dql->innerJoin('message.provider','provider');
        $dql->innerJoin('message.institution','institution');
        $dql->leftJoin('message.destinations', 'destinations');
        $dql->leftJoin('destinations.location', 'destinationslocation');
        $dql->leftJoin('message.associations','associations');
        $dql->leftJoin("message.proxyuser", "proxyuserWrapper");
        $dql->leftJoin("proxyuserWrapper.user", "proxyuser");

		$postData = $request->query->all();
		
		if( !isset($postData['sort']) ) { 
			$dql->orderBy('message.orderdate','DESC');
		}

        $setParameter = false;
        if( $filter == '' || $filter == 'all' ) {
            $dql->where("provider=".$user->getId());
        } else {
            $dql->where("provider=".$user->getId()." AND list.status = :status");
            $setParameter = true;
        }

		//pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters       
//		if( isset($postData['sort']) ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }
		
        //echo "dql=".$dql."<br>";

        $limit = 30;
        $query = $em->createQuery($dql);

        if( $setParameter ) {
            $query->setParameter('status',$filter);
        }				

        $paginator  = $this->get('knp_paginator');
        $sliderequests = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
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
     * @Template("AppOrderformBundle/SlideReturnRequest/index.html.twig")
     */
    public function statusAction( Request $request, $id, $status )
    {

        $url = $request->headers->get('referer');
        if( strpos($url,'my-slide-return-requests') ) {
            if( false === $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') ) {
                return $this->redirect( $this->generateUrl('scan-nopermission') );
            }
        } else {
            if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
                return $this->redirect( $this->generateUrl('scan-nopermission') );
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppOrderformBundle:SlideReturnRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SlideReturnRequest entity.');
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $message = $entity->getMessage();
        $securityUtil = $this->get('order_security_utility');
        if( $message && !$securityUtil->hasUserPermission($message,$user,array("Union"),array("changestatus")) ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $entity->setStatus($status);
        $em->persist($entity);
        $em->flush();


        //record history
        $message = $entity->getMessage();
        if( $message ) {

            $slides = $entity->getSlide();
            $history = new History();
            $history->setMessage($message);
            $history->setCurrentid($message->getOid());
            $history->setCurrentstatus($message->getStatus());
            $history->setProvider($user);
            $history->setRoles($user->getRoles());
            $notemsg = 'Status Changed to "'.ucfirst($status).'" for Slide Return Request ' .
                        $entity->getId() . ' for '.count($slides) . ' slide(s):<br>'.
                        implode("<br>", $entity->getSlideDescription($user));
            $history->setNote($notemsg);
            $eventtype = $em->getRepository('AppOrderformBundle:ProgressCommentsEventTypeList')->findOneByName('Slide Return Request Status Changed');
            $history->setEventtype($eventtype);
            $em->persist($history);
            $em->flush();

        } //if message


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
//            $slideReturnRequest = $em->getRepository('AppOrderformBundle:SlideReturnRequest')->find($id);
//
//            if( !$slideReturnRequest ) {
//                throw $this->createNotFoundException('Unable to find SlideReturnRequest entity.');
//            }
//
//            $user = $this->get('security.token_storage')->getToken()->getUser();
//            $slideReturnRequest->addComment($text_value, $user);
//
//            //echo "ok";
//            $em->persist($slideReturnRequest);
//            $em->flush();
//
//
//            //record history
//            $user = $this->get('security.token_storage')->getToken()->getUser();
//            $message = $slideReturnRequest->getMessage();
//            $slides = $slideReturnRequest->getSlide();
//            $history = new History();
//            $history->setEventtype('Slide Return Request Comment Added');
//            $history->setMessage($message);
//            $history->setCurrentid($message->getOid());
//            $history->setCurrentstatus($message->getStatus());
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


    //@Template("AppOrderformBundle/SlideReturnRequest/index.html.twig")
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
            $user = $this->get('security.token_storage')->getToken()->getUser();

            $slideReturnRequest = $em->getRepository('AppOrderformBundle:SlideReturnRequest')->find($id);

            $slideReturnRequest->addComment($text_value, $user);

            //echo "ok";
            $em->persist($slideReturnRequest);
            $em->flush();

            //record history
            $message = $slideReturnRequest->getMessage();
            if( $message ) {

                $user = $this->get('security.token_storage')->getToken()->getUser();
                $slides = $slideReturnRequest->getSlide();
                $history = new History();
                $history->setMessage($message);
                $history->setCurrentid($message->getOid());
                $history->setCurrentstatus($message->getStatus());
                $history->setProvider($user);
                $history->setRoles($user->getRoles());

                $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y \a\t G:i');
                $dateStr = $transformer->transform(new \DateTime());
                $commentFull = $user . " on " . $dateStr. ": " . $text_value;
                $notemsg = 'Comment added to Slide Return Request '.$id.' for '.count($slides) .
                            ' slide(s):<br>'.implode("<br>", $slideReturnRequest->getSlideDescription($user));
                $history->setNote($notemsg."<br>".$commentFull);

                $eventtype = $em->getRepository('AppOrderformBundle:ProgressCommentsEventTypeList')->findOneByName('Slide Return Request Comment Added');
                $history->setEventtype($eventtype);

                $em->persist($history);
                $em->flush();

            } //if message

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }

    
}
