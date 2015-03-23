<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Form\OrderInfoType;
use Oleg\OrderformBundle\Entity\Imaging;
use Oleg\OrderformBundle\Form\ImagingType;

use Oleg\OrderformBundle\Helper\FormHelper;
use Oleg\OrderformBundle\Entity\Block;
//use Oleg\OrderformBundle\Form\BlockType;

use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Form\PartType;

use Oleg\OrderformBundle\Entity\Patient;

/**
 * OrderInfo controller.
 *
 * @Route("/orderinfo")
 */
class OrderInfoController extends Controller {

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
     * @Template("OlegOrderformBundle:OrderInfo:new_orig.html.twig")
     */
    public function createAction(Request $request)
    {       
        $entity  = new OrderInfo();
        $form = $this->createForm(new OrderInfoType(), $entity);
        $form->bind($request);
          
//        it works!
//        $part_entity  = new Part();
//        $part_form = $this->createForm(new PartType(), $part_entity);
//        $part_form->bind($request);
//        echo "entity provider=".$entity->getProvider()."<br>";
//        echo "part name=".$part_entity->getName()." part description".$part_entity->getDescription()."<br>";
//        exit();
        
        if( $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();                  
                      
            $entity->setStatus("submitted");            
                      
//            foreach( $entity->getPatient() as $patient ) {
//                echo "patient mrn=".$patient->getMrn()."<br>";           
//            }          
            //exit();
            
            //$em->persist($part_entity);
            $em->persist($entity);                               
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
     * @Template("OlegOrderformBundle:OrderInfo:new_orig.html.twig")
     */
    public function newAction()
    {         
        $entity = new OrderInfo();      
        
        //sample data
//        $patient1 = new Patient();
//        $patient1->setMrn('mrn1');
//        $entity->addPatient($patient1);
//        $patient2 = new Patient();
//        $patient2->setMrn('mrn2');
//        $entity->addPatient($patient2);
        
        $form   = $this->createForm(new OrderInfoType(), $entity);
        
        return array(
            'entity' => $entity,
            'form' => $form->createView(),           
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
            
            //$entity->removeAllChildren();          
            
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
