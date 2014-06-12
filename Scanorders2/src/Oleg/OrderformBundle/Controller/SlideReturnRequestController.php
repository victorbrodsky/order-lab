<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Form\SlideReturnRequestType;
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

        $params = array();
        $form = $this->createForm(new SlideReturnRequestType($params,$slideReturnRequest), $slideReturnRequest);

        return array(
            'orderinfo' => $orderinfo,
            'form' => $form->createView(),
            'cicle' => 'new'
        );
    }

    /**
     * Creates a new SlideReturnRequest.
     *
     * @Route("/{id}", name="singleorder_create", requirements={"id" = "\d+"})
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

        //exit("before form");

        $params = array();
        $form = $this->createForm(new SlideReturnRequestType($params,$slideReturnRequest), $slideReturnRequest);

        $form->handleRequest($request);

        if( $form->isValid() ) {
            //echo "form is valid !!! <br>";

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
            $history->setEventtype('Status Changed');
            $history->setOrderinfo($orderinfo);
            $history->setCurrentid($orderinfo->getOid());
            $history->setCurrentstatus($orderinfo->getStatus());
            $history->setProvider($user);
            $history->setRoles($user->getRoles());
            $notemsg = 'Slide Return Request has been made for '.count($slides) . ' slide(s) with stain(s):'.implode(";", $fullStainArr);
            $history->setNote($notemsg);
            $em->persist($history);
            $em->flush();

            return $this->redirect($this->generateUrl('all-slides-requested-for-return'));

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
     * @Route("/all-slides-requested-for-return", name="all-slides-requested-for-return")
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index_all.html.twig")
     */
    public function allRequestedSlidesAction( Request $request ) {

        $filterForm = $this->createFormBuilder()
            ->add('filter', 'choice',
                array(
                    //'mapped' => false,
                    'label' => false,
                    'attr' => array('class' => 'combobox combobox-width'),
                    'choices' => array('active' => 'Active', 'returned' => 'Returned', 'all' => 'All')
                )
            )
            ->getForm();

        //$filterForm->handleRequest($request);
        $filterForm->bind($request); //use bind. handleRequest does not work with GET

        $filter = $filterForm->get('filter')->getData();
        //echo "filter = ".$filter."<br>";

        $em = $this->getDoctrine()->getManager();

        if( $filter == 'all' ) {
            $sliderequests = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->findAll();
        } else {
            $sliderequests = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->findByStatus($filter);
        }

        return array(
            'sliderequests' => $sliderequests,
            'filter' => $filterForm->createView()
        );
    }


    /**
     * @Route("/sliderequest-status/{id}/{status}/status", name="sliderequest_status", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:SlideReturnRequest:index_all.html.twig")
     */
    public function statusAction($id, $status)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:SlideReturnRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SlideReturnRequest entity.');
        }

        $entity->setStatus($status);
        $em->persist($entity);
        $em->flush();


        return $this->redirect($this->generateUrl('all-slides-requested-for-return'));

    }

    
}
