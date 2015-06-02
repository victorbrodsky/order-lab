<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\Message;
use Oleg\OrderformBundle\Form\MessageType;
use Oleg\OrderformBundle\Entity\Imaging;
use Oleg\OrderformBundle\Form\ImagingType;
//use Oleg\OrderformBundle\Entity\Slide;
//use Oleg\OrderformBundle\Form\SlideType;
use Oleg\OrderformBundle\Helper\FormHelper;
use Oleg\OrderformBundle\Entity\Block;
//use Oleg\OrderformBundle\Form\BlockType;

/**
 * Message controller.
 *
 * @Route("/message")
 */
class MessageController extends Controller {

    /**
     * Lists all Message entities.
     *
     * @Route("/test", name="test")
     * @Method("GET")
     * @Template()
     */
    public function testAction() {
    
//        $scan = new Imaging();
//        $scan->setMag('20X');
//
//        $order = new Message();
//        $order->setStatus('test');
//        $order->setPriority('test priority');
//        $order->setSlideDelivery('test priority');
//        $order->setReturnSlide('test ret');
//        $order->setProvider('test prov');
//        // relate this product(scan) to the category(order)
//        $scan->setMessage($order);
//
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($order);
//        $em->persist($scan);
//        $em->flush();

//        echo 'Created scan id: '.$scan->getId().' and order id: '.$order->getId();
        
        $scan2 = $this->getDoctrine()
        ->getRepository('OlegOrderformBundle:Imaging')
        ->findAll();

        $order_status = $scan2[0]->getMessage()->getStatus();
        echo "order status=".$order_status."<br>";
        
        $order2 = $this->getDoctrine()
        ->getRepository('OlegOrderformBundle:Message')
        ->findAll();

        $scans = $order2[0]->getScan();
        
        foreach( $scans as $scan3 ) {
            echo "scan mag=".$scan3->getMag()."<br>";
        }
        
        
        $block = new Block();
        $slides = $block->getSlide();
        echo "count of slides=".count($slides)."<br>";
        foreach( $slides as $slide ) {
            echo "slide barcode = ".$slide->getBarcode()."<br>";
        }
        
        //exit();
//        return new Response(
//            'Created product id: '.$product->getId().' and category id: '.$category->getId()
//        );
        
    }
    /**
     * Lists all Message entities.
     *
     * @Route("/", name="message")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        
        //findAll();
        $entities = $em->getRepository('OlegOrderformBundle:Message')->
                    findBy(array(), array('orderdate'=>'desc')); 
        
//        echo "count=".count($entities);     
//        $entity = $entities[0];
//        echo "<br>entity id=".$entity->getId();
//        $scans = $entity->getScan();
//        $scan = $scans[0];
//        echo "scan mag=".$scan->getMag();
        
        return array(
            'entities' => $entities,          
        );
    }
    
    /**
     * Creates a new Message entity.
     *
     * @Route("/", name="message_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:Message:new_orig.html.twig")
     */
    public function createAction(Request $request)
    {
        //echo "message createAction";
        $entity  = new Message();
        $form = $this->createForm(new MessageType(), $entity);
        $form->bind($request);
        
        $scan_entity = new Imaging();
        $scan_form = $this->createForm(new ImagingType(), $scan_entity);
        $scan_form->bind($request);
        
        if( $form->isValid() && $scan_form->isValid() ) {
            $em = $this->getDoctrine()->getManager();                  
                      
            $entity->setStatus("submitted");            
                      
            $scan_entity->setStatus("submitted");
            $scan_entity->setMessage($entity);
            
            //get Accession, Part and Block. Create if they are not exist, or return them if they are exist.
            //process accession. If not exists - create and return new object, if exists - return object          
            $accession = $scan_entity->getSlide()->getAccession();
            $accession = $em->getRepository('OlegOrderformBundle:Accession')->processAccession( $accession );                         
            $scan_entity->getSlide()->setAccession($accession);          
            
            $part = $scan_entity->getSlide()->getPart();
            $part->setAccession($accession);
            $part = $em->getRepository('OlegOrderformBundle:Part')->processPart( $part ); 
            $scan_entity->getSlide()->setPart($part);         
            
            $block = $scan_entity->getSlide()->getBlock();
            $block->setAccession($accession);
            $block->setPart($part);
            $block = $em->getRepository('OlegOrderformBundle:Block')->processBlock( $block );                         
            $scan_entity->getSlide()->setBlock($block);        

            //TODO: i.e. if part's field is updated then add options to detect and update it.
            
            $em->persist($entity);       
            $em->persist($scan_entity);           
            
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'You successfully submit a scan request! Confirmation email sent!'
            );
            
            return $this->redirect( $this->generateUrl('message') );
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'form_scan'   => $scan_form->createView(),
        );
    }

    /**
     * Displays a form to create a new Message entity.
     *
     * @Route("/new", name="message_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Message:new_orig.html.twig")
     */
    public function newAction()
    {         
        $entity = new Message();
        $form   = $this->createForm(new MessageType(), $entity);

        $scan_entity = new Imaging();      
        $form_scan   = $this->createForm(new ImagingType(), $scan_entity);
        
        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'form_scan' => $form_scan->createView(),
        );
    }

    /**
     * Finds and displays a Message entity.
     *
     * @Route("/{id}", name="message_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Message')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Message entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Message entity.
     *
     * @Route("/{id}/edit", name="message_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Message')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Message entity.');
        }

        $editForm = $this->createForm(new MessageType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Message entity.
     *
     * @Route("/{id}", name="message_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Message:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Message')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Message entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new MessageType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('message_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Message entity.
     *
     * @Route("/{id}", name="message_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:Message')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Message entity.');
            }
            
//            $scan_entities = $em->getRepository('OlegOrderformBundle:Imaging')->
//                    findBy(array('message_id'=>$id));
            
//            $scan_entities = $em->getRepository('OlegOrderformBundle:Imaging')->findBy(
//                array('message' => $id)
//            );
            $entity->removeAllChildren();          
            
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('message'));
    }

    /**
     * Creates a form to delete a Message entity by id.
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
