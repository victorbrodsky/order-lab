<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Form\SlideReturnRequestType;
use Oleg\OrderformBundle\Form\FilterSlideReturnRequestType;
use Oleg\OrderformBundle\Entity\SlideReturnRequest;
use Oleg\OrderformBundle\Security\Util\SecurityUtil;
use Oleg\OrderformBundle\Entity\History;


/**
 * Scan controller.
 *
 * @Route("/slide-return-request")
 */
class SlideReturnRequestController extends Controller
{


    /**
     * Lists all Slides for this order with oid=$id.
     *
     * @Route("/{id}", name="slide-return-request", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index.html.twig")
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
     * @Route("/{id}", name="slide-return-request_create", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index.html.twig")
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

            return $this->redirect($this->generateUrl('my-slide-requests'));

        } else {
            //exit("form is not valid ??? <br>");
            //echo "form is not valid ??? <br>";
            throw new \Exception( 'Form was altered' );
        }

//        return array(
//            //'orderinfo' => $orderinfo,
//            //'form' => $form
//        );

    }



    /**
     * Lists all Slides requested for return.
     *
     * @Route("/all-slide-requests", name="all-slides-requested-for-return")
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index_all.html.twig")
     */
    public function allRequestedSlidesAction( Request $request ) {

        $filterForm = $this->createForm(new FilterSlideReturnRequestType('active'));

        //$filterForm->handleRequest($request);
        $filterForm->bind($request); //use bind. handleRequest does not work with GET

        $filter = $filterForm->get('filter')->getData();
        //echo "filter = ".$filter."<br>";

        $em = $this->getDoctrine()->getManager();

        if( $filter == '' || $filter == 'all' ) {
            $sliderequests = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->findBy(array(),array('orderdate' => 'DESC'));
        } else {
            $sliderequests = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->findBy(array('status' => $filter),array('orderdate' => 'DESC'));
        }

        return array(
            'sliderequests' => $sliderequests,
            'filter' => $filterForm->createView(),
            'route' => $request->get('_route')
        );
    }


    /**
     * Lists user's Slides requested for return.
     *
     * @Route("/my-slide-requests", name="my-slide-requests")
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index_all.html.twig")
     */
    public function userRequestedSlidesAction( Request $request ) {

        $filterForm = $this->createForm(new FilterSlideReturnRequestType('all'));

        //$filterForm->handleRequest($request);
        $filterForm->bind($request); //use bind. handleRequest does not work with GET

        $filter = $filterForm->get('filter')->getData();
        //echo "filter = ".$filter."<br>";

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        if( $filter == '' || $filter == 'all' ) {
            $sliderequests = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->findBy(
                array('provider' => $user),
                array('orderdate' => 'DESC')
            );
        } else {
            $sliderequests = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->findBy(
                array('status' => $filter, 'provider' => $user),
                array('orderdate' => 'DESC')
            );
        }

        return array(
            'sliderequests' => $sliderequests,
            'filter' => $filterForm->createView(),
            'route' => $request->get('_route')
        );
    }


    /**
     * Change status
     * @Route("/sliderequest-status/{id}/{status}/status", name="sliderequest_status", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index_all.html.twig")
     */
    public function statusAction( Request $request, $id, $status )
    {

        $url = $request->headers->get('referer');
        if( strpos($url,'my-slide-requests') ) {
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
        $notemsg = 'Status Changed to '.ucfirst($status).' for Slide Return Request for '.count($slides) . ' slide(s):<br>'.implode("<br>", $entity->getSlideDescription($user));
        $history->setNote($notemsg);
        $em->persist($history);
        $em->flush();

        $url = $request->headers->get('referer');
        if( strpos($url,'my-slide-requests') ) {
            return $this->redirect( $this->generateUrl( 'my-slide-requests',array('filter_search_box[filter]'=>'all') ) );
        } else {
            return $this->redirect( $this->generateUrl( 'all-slides-requested-for-return',array('filter_search_box[filter]'=>'active') ) );
        }

    }


    /**
     * Change status
     * @Route("/comment/create", name="sliderequest_status_comment")
     * @Method("POST")
     * @Template("OlegOrderformBundle:SlideReturnRequest:comment.html.twig")
     */
    public function statusWithCommentAction( Request $request ) {

        $text_value = $request->request->get('text');
        $id = $request->request->get('id');

        //echo "id=".$id.", text_value=".$text_value."<br>";

        $res = 1;

        if( $text_value == "" ) {
            $res = 'Comment was not provided';
        } else {

            $em = $this->getDoctrine()->getManager();
            $slideReturnRequest = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->find($id);

            if( !$slideReturnRequest ) {
                throw $this->createNotFoundException('Unable to find SlideReturnRequest entity.');
            }

            $user = $this->get('security.context')->getToken()->getUser();
            $slideReturnRequest->addComment($text_value, $user);

            //echo "ok";
            $em->persist($slideReturnRequest);
            $em->flush();
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;

    }

    
}
