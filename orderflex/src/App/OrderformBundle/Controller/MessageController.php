<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\OrderformBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\OrderformBundle\Entity\Message;
use App\OrderformBundle\Form\MessageType;
use App\OrderformBundle\Entity\Imaging;
use App\OrderformBundle\Form\ImagingType;

use App\OrderformBundle\Helper\FormHelper;
use App\OrderformBundle\Entity\Block;
//use App\OrderformBundle\Form\BlockType;

use App\OrderformBundle\Entity\Part;
use App\OrderformBundle\Form\PartType;

use App\OrderformBundle\Entity\Patient;

/**
 * Message controller.
 *
 * @Route("/message")
 */
class MessageController extends OrderAbstractController {

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
        $entities = $em->getRepository('AppOrderformBundle:Message')->
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
     * Finds and displays a Message entity.
     *
     * @Route("/{id}", name="scan_message_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppOrderformBundle:Message')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Message entity.');
        }

        //redirect if this is a scan order
        $messageCategory = $entity->getMessageCategory()->getName()."";
        if(
            $messageCategory == "Multi-Slide Scan Order" ||
            $messageCategory == "One-Slide Scan Order" ||
            $messageCategory == "Table-View Scan Order"
        )
        {
            //exit('message_show');
            return $this->redirect($this->generateUrl('multy_show',array('id'=>$id)));
        }

        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'formtype' => $entity->getMessageCategory()->getName()."",
            'cycle' => 'show',
            'entity' => $entity,
            //'delete_form' => $deleteForm->createView(),
        );
    }







    
    /**
     * Creates a new Message entity.
     *
     * @Route("/", name="message_create")
     * @Method("POST")
     * @Template("AppOrderformBundle/Message/new_orig.html.twig")
     */
    public function createAction(Request $request)
    {       
        $entity  = new Message();
        $form = $this->createForm(MessageType::class, $entity);
        $form->submit($request);
          
//        it works!
//        $part_entity  = new Part();
//        $part_form = $this->createForm(new PartType(), $part_entity);
//        $part_form->submit($request);
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
     * @Template("AppOrderformBundle/Message/new_orig.html.twig")
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
        
        $form   = $this->createForm(MessageType::class, $entity);
        
        return array(
            'entity' => $entity,
            'form' => $form->createView(),           
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

        $entity = $em->getRepository('AppOrderformBundle:Message')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Message entity.');
        }

        $editForm = $this->createForm(MessageType::class, $entity);
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
     * @Template("AppOrderformBundle/Message/edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppOrderformBundle:Message')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Message entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(MessageType::class, $entity);
        $editForm->submit($request);

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
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppOrderformBundle:Message')->find($id);

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
            ->add('id', HiddenType::class)
            ->getForm()
        ;
    }
}
