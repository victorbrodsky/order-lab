<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Type\SlideType;

/**
 * @Route("/order")
 */
class SlideController extends Controller {
    
    /**
     * Lists all Slide entities.
     *
     * @Route("/", name="order")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        //$em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('OlegOrderformBundle:OrderInfo')->findAll();
    
        $em = $this->getDoctrine()->getManager();
        //$slides = $em->getRepository('OlegOrderformBundle:Slide')->findAllOrderedByName(); 
        $slides = $em->getRepository('OlegOrderformBundle:Slide')->findAll();
        
        $accession = null;
        $orderinfo = null;
        
        return $this->render('OlegOrderformBundle:Slide:index.html.twig',
            array(
                'slides' => $slides,
                'accession' => $accession,
                'orderinfo' => $orderinfo
            ));
        
    }
    
    /**
     * Displays a form to edit an existing Slide entity.
     *
     * @Route("/{id}/edit", name="order_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction( $id ) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Slide')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Slide entity.');
        }

        $editForm = $this->createForm(new SlideType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Edits an existing Slide entity.
     *
     * @Route("/{id}", name="order_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Slide:edit.html.twig")
     */
    public function updateAction( Request $request, $id )
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Slide')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Slide entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new SlideType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('order_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Deletes a Slide entity.
     *
     * @Route("/{id}", name="order_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:Slide')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Slide entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('order'));
    }
    
    
    /**
     * Displays form to add a Slide.
     *
     * @Route("/new", name="order_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction(){//Request $request) {
        
        $entity  = new Slide();
        $form = $this->createForm(new SlideType(), $entity);
        //$form->bind($request);
        

//        if ($request->getMethod() == 'POST') {
//            $form->bind($request);
//            if ($form->isValid()) {            
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
//                $response = $this->forward('OlegOrderformBundle:Slide:verify', array(
//                    'form'  => $form              
//                ));
//            }           
//        }                    

        return $this->render('OlegOrderformBundle:Slide:new.html.twig',
            array(
                'form' => $form->createView()
            ));
    }
    
    /**
     * Creates a new OrderInfo entity.
     *
     * @Route("/", name="order_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:Slide:new.html.twig")
     */
    public function createAction(Request $request) {
        $entity  = new Slide();             
        $form = $this->createForm(new SlideType(), $entity);                      
        $form->bind($request);    
            
        if ($form->isValid()) {
//            echo "stain=".$entity->getStain();
//            echo ", mag=".$entity->getMag();
//            echo "<br>";         
//            exit();
                      
            $em = $this->getDoctrine()->getManager();
            
            //process accession. If not exists - create and return new object, if exists - return object
            $accession_number = $form["accession"]->getData();
            $accession = $em->getRepository('OlegOrderformBundle:Accession')->processAccession( $accession_number );                         
            $entity->setAccession($accession);
            $em->persist($entity);             
            
            $em->flush();
            
            return $this->redirect($this->generateUrl('order_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    
    //TODO: remove it?
    //Display order information for modification and verification. 
    //Display all fields
    /**
     * @Route("/verify/")
     * @Method({"POST"})
     */
    public function verifyAction( $form ) {
        
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
        
        return $this->render('OlegOrderformBundle:Slide:add.html.twig',
            array(
                'form' => $form->createView(),
                'verify' => true
            ));
    }
    
    
    /**
     * Finds and displays a Slide entity.
     *
     * @Route("/{id}", name="order_show", requirements={"id" = "\d+"}, defaults={"id" = 1})
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Slide')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Slide entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Creates a form to delete a Slide entity by id.
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
    
}

?>
