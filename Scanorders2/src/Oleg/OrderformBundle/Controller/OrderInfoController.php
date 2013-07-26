<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Form\OrderInfoType;
use Oleg\OrderformBundle\Entity\Scan;
use Oleg\OrderformBundle\Form\ScanType;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Form\SlideType;
use Oleg\OrderformBundle\Helper\FormHelper;

/**
 * OrderInfo controller.
 *
 * @Route("/orderinfo")
 */
class OrderInfoController extends Controller {

    /**
     * Lists all OrderInfo entities.
     *
     * @Route("/test", name="test")
     * @Method("GET")
     * @Template()
     */
    public function testAction() {
    
//        $scan = new Scan();
//        $scan->setMag('20X');
//
//        $order = new OrderInfo();
//        $order->setStatus('test');
//        $order->setPriority('test priority');
//        $order->setSlideDelivery('test priority');
//        $order->setReturnSlide('test ret');
//        $order->setProvider('test prov');
//        // relate this product(scan) to the category(order)
//        $scan->setOrderInfo($order);
//
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($order);
//        $em->persist($scan);
//        $em->flush();

//        echo 'Created scan id: '.$scan->getId().' and order id: '.$order->getId();
        
        $scan2 = $this->getDoctrine()
        ->getRepository('OlegOrderformBundle:Scan')
        ->find(5);

        $order_status = $scan2->getOrderinfo()->getStatus();
        echo "order status=".$order_status."<br>";
        
        $order2 = $this->getDoctrine()
        ->getRepository('OlegOrderformBundle:OrderInfo')
        ->find(6);

        $scans = $order2->getScan();
        
        foreach( $scans as $scan3 ) {
            echo "scan mag=".$scan3->getMag()."<br>";
        }
        
        exit();
//        return new Response(
//            'Created product id: '.$product->getId().' and category id: '.$category->getId()
//        );
        
    }
    /**
     * Lists all OrderInfo entities.
     *
     * @Route("/", name="orderinfo")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        
        //findAll();
        $entities = $em->getRepository('OlegOrderformBundle:OrderInfo')->
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
     * Creates a new OrderInfo entity.
     *
     * @Route("/", name="orderinfo_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:OrderInfo:new.html.twig")
     */
    public function createAction(Request $request)
    {
        //echo "orderinfo createAction";
        $entity  = new OrderInfo();
        $form = $this->createForm(new OrderInfoType(), $entity);
        $form->bind($request);
        
        $scan_entity = new Scan();
        $scan_form = $this->createForm(new ScanType(), $scan_entity);
        $scan_form->bind($request);

//        $slide_entity = new Slide();
//        $slide_form = $this->createForm(new SlideType(), $slide_entity);
//        $slide_form->bind($request);
        
        //$barcode = $slide_entity->getBarcode();
        //echo "slide barcode=".$barcode;
        
        if( $form->isValid() && $scan_form->isValid() ) {
            $em = $this->getDoctrine()->getManager();                  
                      
            $entity->setStatus("submitted");            
                      
            $scan_entity->setStatus("submitted");
            $scan_entity->setOrderinfo($entity);                 
            
            $em->persist($entity);       
            $em->persist($scan_entity);           
            
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'You successfully submit a scan request! Confirmation email sent!'
            );
            
            return $this->redirect( $this->generateUrl('orderinfo') );
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new OrderInfo entity.
     *
     * @Route("/new", name="orderinfo_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $helper = new FormHelper();
        
        $entity = new OrderInfo();      
        $form   = $this->createForm(new OrderInfoType(), $entity);

        $scan_entity = new Scan();
        //$scan_entity->setMag( key($helper->getMags()) );
        //$entity->addScan($scan_entity);
        $form_scan   = $this->createForm(new ScanType(), $scan_entity);
        
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'form_scan' => $form_scan->createView(),
        );
    }

    /**
     * Finds and displays a OrderInfo entity.
     *
     * @Route("/{id}", name="orderinfo_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing OrderInfo entity.
     *
     * @Route("/{id}/edit", name="orderinfo_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        $editForm = $this->createForm(new OrderInfoType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing OrderInfo entity.
     *
     * @Route("/{id}", name="orderinfo_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:OrderInfo:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new OrderInfoType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('orderinfo_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a OrderInfo entity.
     *
     * @Route("/{id}", name="orderinfo_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OrderInfo entity.');
            }
            
//            $scan_entities = $em->getRepository('OlegOrderformBundle:Scan')->
//                    findBy(array('orderinfo_id'=>$id));
            
//            $scan_entities = $em->getRepository('OlegOrderformBundle:Scan')->findBy(
//                array('orderinfo' => $id)            
//            );
            $entity->removeAllChildren();          
            
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('orderinfo'));
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
}
