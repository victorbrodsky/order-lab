<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Oleg\OrderformBundle\Entity as Entity;
use Oleg\OrderformBundle\Form as Form;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/order")
 */
class SlideController extends Controller
{
    /**
     * By default, displays form to add a Slide.
     * If form has been posted, validates and adds Slide to database.
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    /**
     * @Route("/")
     * @Template()
     */
    public function addAction() {
        $slide = new Entity\Slide();
        $form = $this->get('form.factory')->create(new Form\AddSlideForm(), $slide);
        $request = $this->get('request');

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {            
//                $em = $this->getDoctrine()->getManager();              
//                $em->persist($slide);
//                $em->flush();
                
//                $this->get('session')->getFlashBag()->add(
//                    'notice',
//                    'You have successfully added slide# '.$slide->getAccession().' to the scan order!'
//                );
                //Note for generateUrl oleg_orderform_slide_add: 
                //A route defined with the @Route annotation is given a default name 
                //composed of the bundle name, the controller name and the action name.
                //return $this->redirect( $this->generateUrl('oleg_orderform_slide_verify') );
                $response = $this->forward('OlegOrderformBundle:Slide:verify', array(
                    'form'  => $form              
                ));
            }           
        }                    

        return $this->render('OlegOrderformBundle:Forms:add.html.twig',
            array(
                'form' => $form->createView()
            ));
    }
    
    //Show order information for modification and verification. 
    //Show all fields
    /**
     * @Route("/verify/")
     * @Method({"POST"})
     */
    public function verifyAction($form) {
        
        $request = $this->get('request');
        
        $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Please verify/correct your order information'
                );
        
        $form->bind($request);
        if ($form->isValid()) { 
            $this->get('session')->getFlashBag()->add(
                    'notice',
                    'You have successfully added slide# '.$slide->getAccession().' to the scan order!'
                );
        }
        
        return $this->render('OlegOrderformBundle:Forms:add.html.twig',
            array(
                'form' => $form->createView(),
                'verify' => true
            ));
    }
    
}

?>
