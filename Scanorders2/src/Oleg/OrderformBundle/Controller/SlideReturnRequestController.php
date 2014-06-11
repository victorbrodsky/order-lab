<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Form\SlideReturnRequestType;
use Oleg\OrderformBundle\Entity\SlideReturnRequest;


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

        $em = $this->getDoctrine()->getManager();

        $orderinfo = $em->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($id);

        if (!$orderinfo) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity with id='.$id);
        }

        $slideReturnRequest  = new SlideReturnRequest();

        $user = $this->get('security.context')->getToken()->getUser();
        $slideReturnRequest->setProvider($user);

        $params = array('orderid'=>$id);
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

        $slideReturnRequest->setStatus('valid');

        $params = array('orderid'=>$id);
        $form = $this->createForm(new SlideReturnRequestType($params,$slideReturnRequest), $slideReturnRequest);

        $form->handleRequest($request);

        if( $form->isValid() ) {
            echo "form is valid !!! <br>";

            //$data = $form->getData();
            //var_dump($data['slide']);

            //$slides = $form["slide"]->getData();

            $slides = $slideReturnRequest->getSlide();
            var_dump($slides);

            foreach( $slides as $slide ) {
                echo "slide=".$slide->getId()."<br>";
            }

            exit();

            //get Slides from checkboxes
            //$slide
            //$slideReturnRequest->addSlide( $slide );

            $em->persist($slideReturnRequest);
            $em->flush();
        } else {
            echo "form is not valid ??? <br>";
        }


        return array(
            //'orderinfo' => $orderinfo,
            //'form' => $form
        );

    }

    
}
