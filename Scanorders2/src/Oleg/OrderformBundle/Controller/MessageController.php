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

use Oleg\OrderformBundle\Helper\FormHelper;
use Oleg\OrderformBundle\Entity\Block;
//use Oleg\OrderformBundle\Form\BlockType;

use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Form\PartType;

use Oleg\OrderformBundle\Entity\Patient;

/**
 * Message controller.
 *
 * @Route("/message")
 */
class MessageController extends Controller {

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
        $entity  = new Message();
        $form = $this->createForm(new MessageType(), $entity);
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
            
            return $this->redirect( $this->generateUrl('message') );
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),           
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
        
        //sample data
//        $patient1 = new Patient();
//        $patient1->setMrn('mrn1');
//        $entity->addPatient($patient1);
//        $patient2 = new Patient();
//        $patient2->setMrn('mrn2');
//        $entity->addPatient($patient2);
        
        $form   = $this->createForm(new MessageType(), $entity);
        
        return array(
            'entity' => $entity,
            'form' => $form->createView(),           
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
            
            //$entity->removeAllChildren();          
            
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
