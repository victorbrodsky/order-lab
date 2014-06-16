<?php

namespace Oleg\OrderformBundle\Controller;

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


/**
 * Scan controller.
 */
class SlideReturnRequestController extends Controller
{


    /**
     * Lists all Slides for this order with oid=$id.
     *
     * @Route("/slide-return-request/{id}", name="slide-return-request", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:create.html.twig")
     */
    public function indexAction( $id ) {

        //check if the user has permission to view this order
        if( false === $this->get('security.context')->isGranted('ROLE_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_ORDERING_PROVIDER') &&
            false === $this->get('security.context')->isGranted('ROLE_EXTERNAL_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_EXTERNAL_ORDERING_PROVIDER')
        ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $secUtil = new SecurityUtil($em,$this->get('security.context'),$this->get('session') );
        if( !$secUtil->isCurrentUserAllow($id) ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $orderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

        if (!$orderinfo) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity with id='.$id);
        }

        $slideReturnRequest  = new SlideReturnRequest();

        $user = $this->get('security.context')->getToken()->getUser();
        $slideReturnRequest->setProvider($user);
        $slideReturnRequest->setProxyuser($user);

        $params = array('user'=>$user);
        $form = $this->createForm(new SlideReturnRequestType($params,$slideReturnRequest), $slideReturnRequest);

        return array(
            'orderinfo' => $orderinfo,
            'form' => $form->createView(),
            'cicle' => 'new'
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
        $slideReturnRequest->setProvider($user);

        $slideReturnRequest->setStatus('active');

        $formtype = $em->getRepository('OlegOrderformBundle:FormType')->findOneByName("Slide Return Request");
        $slideReturnRequest->setType($formtype);

        $params = array('user'=>$user);
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
            $history->setEventtype('Initial Slide Return Request Submission');
            $history->setOrderinfo($orderinfo);
            $history->setCurrentid($orderinfo->getOid());
            $history->setCurrentstatus($orderinfo->getStatus());
            $history->setProvider($user);
            $history->setRoles($user->getRoles());
            $notemsg = 'Slide Return Request has been made for '.count($slides) . ' slide(s):<br>'.implode("<br>",$slideReturnRequest->getSlideDescription($user));
            $history->setNote($notemsg);
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

        $filterForm = $this->createForm(new FilterSlideReturnRequestType('all'));

        //$filterForm->handleRequest($request);
        $filterForm->bind($request); //use bind. handleRequest does not work with GET

        $filter = $filterForm->get('filter')->getData();
        //echo "filter = ".$filter."<br>";

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:SlideReturnRequest');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list, COUNT(slides) as slidecount');

        $dql->groupBy('list');
        $dql->addGroupBy('provider.username');
        $dql->addGroupBy('proxyuser.username');
        $dql->addGroupBy('orderinfo.id');

        $dql->innerJoin("list.slide", "slides");
        $dql->innerJoin('list.provider','provider');
        $dql->innerJoin('list.orderinfo','orderinfo');

        $dql->leftJoin('list.proxyuser','proxyuser');

        $dql->orderBy('list.orderdate','DESC');

        $setParameter = false;
        if( $filter == '' || $filter == 'all' ) {
            //no where filter: show all
        } else {
            $dql->where("list.status = :status");
            $setParameter = true;
        }

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
            'route' => $request->get('_route')
        );
    }


    /**
     * Lists user's Slides requested for return.
     *
     * @Route("/my-slide-return-requests", name="my-slide-return-requests")
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index.html.twig")
     */
    public function userRequestedSlidesAction( Request $request ) {

        $filterForm = $this->createForm(new FilterSlideReturnRequestType('all'));

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
        $dql->addGroupBy('orderinfo.id');

        $dql->innerJoin("list.slide", "slides");
        $dql->innerJoin('list.provider','provider');
        $dql->innerJoin('list.orderinfo','orderinfo');

        $dql->leftJoin('list.proxyuser','proxyuser');

        $dql->orderBy('list.orderdate','DESC');

        $setParameter = false;
        if( $filter == '' || $filter == 'all' ) {
            $dql->where("provider=".$user->getId());
        } else {
            $dql->where("provider=".$user->getId()." AND list.status = :status");
            $setParameter = true;
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
            'route' => $request->get('_route')
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
            if( false === $this->get('security.context')->isGranted('ROLE_PROCESSOR') ) {
                return $this->redirect( $this->generateUrl('scan-order-nopermission') );
            }
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SlideReturnRequest entity.');
        }

        $entity->setStatus($status);
        $em->persist($entity);
        $em->flush();


        //record history
        $user = $this->get('security.context')->getToken()->getUser();
        $orderinfo = $entity->getOrderinfo();
        $slides = $entity->getSlide();
        $history = new History();
        $history->setEventtype('Slide Return Request Status Changed');
        $history->setOrderinfo($orderinfo);
        $history->setCurrentid($orderinfo->getOid());
        $history->setCurrentstatus($orderinfo->getStatus());
        $history->setProvider($user);
        $history->setRoles($user->getRoles());
        $notemsg = 'Status Changed to "'.ucfirst($status).'" for Slide Return Request ' . $entity->getId() . ' for '.count($slides) . ' slide(s):<br>'.implode("<br>", $entity->getSlideDescription($user));
        $history->setNote($notemsg);
        $em->persist($history);
        $em->flush();


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
            $user = $this->get('security.context')->getToken()->getUser();
            $orderinfo = $slideReturnRequest->getOrderinfo();
            $slides = $slideReturnRequest->getSlide();
            $history = new History();
            $history->setEventtype('Slide Return Request Comment Added');
            $history->setOrderinfo($orderinfo);
            $history->setCurrentid($orderinfo->getOid());
            $history->setCurrentstatus($orderinfo->getStatus());
            $history->setProvider($user);
            $history->setRoles($user->getRoles());

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y \a\t G:ia');
            $dateStr = $transformer->transform(new \DateTime());
            $commentFull = $user . " on " . $dateStr. ": " . $text_value;
            $notemsg = 'Comment added to Slide Return Request '.$id.' for '.count($slides) . ' slide(s):<br>'.implode("<br>", $slideReturnRequest->getSlideDescription($user));
            $history->setNote($notemsg."<br>".$commentFull);

            $em->persist($history);
            $em->flush();

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }

    
}
