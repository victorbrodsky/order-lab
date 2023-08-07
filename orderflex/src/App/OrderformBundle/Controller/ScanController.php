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



use App\OrderformBundle\Entity\Accession; //process.py script: replaced namespace by ::class: added use line for classname=Accession


use App\OrderformBundle\Entity\Part; //process.py script: replaced namespace by ::class: added use line for classname=Part


use App\OrderformBundle\Entity\Block; //process.py script: replaced namespace by ::class: added use line for classname=Block
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;

use App\OrderformBundle\Entity\Imaging;
use App\OrderformBundle\Entity\Slide;
use App\OrderformBundle\Form\ImagingType;
use App\OrderformBundle\Helper\FormHelper;

/**
 * Scan controller.
 */
#[Route(path: '/scan')]
class ScanController extends OrderAbstractController
{

    /**
     * Lists all Scan entities.
     */
    #[Route(path: '/', name: 'scan', methods: ['GET'])]
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Imaging'] by [Imaging::class]
        $entities = $em->getRepository(Imaging::class)->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Scan entity.
     */
    #[Route(path: '/', name: 'scan_create', methods: ['POST'])]
    #[Template('AppOrderformBundle/Imaging/new_orig.html.twig')]
    public function createAction(Request $request)
    {
        $entity  = new Imaging();
        $form = $this->createForm(ImagingType::class, $entity);
        $form->submit($request);

        $mag = $form["mag"]->getData();
        echo "mag=".$mag."<br>";
        
        $errors = $this->getErrorMessages($form);
        print_r($errors);            
        
        if( $form->isValid() ) {
            
            $em = $this->getDoctrine()->getManager();
            
            $entity->setStatus("submitted");
            
            //TODO: i.e. if part's field is updated then add options to detect and update it.
            //get Accession, Part and Block. Create if they are not exist, or return them if they are exist.
            //process accession. If not exists - create and return new object, if exists - return object          
            $accession = $entity->getSlide()->getAccession();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Accession'] by [Accession::class]
            $accession = $em->getRepository(Accession::class)->processAccession( $accession );                         
            $entity->getSlide()->setAccession($accession);          
            
            $part = $entity->getSlide()->getPart();
            $part->setAccession($accession);
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Part'] by [Part::class]
            $part = $em->getRepository(Part::class)->processPart( $part ); 
            $entity->getSlide()->setPart($part);         
            
            $block = $entity->getSlide()->getBlock();
            $block->setAccession($accession);
            $block->setPart($part);
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Block'] by [Block::class]
            $block = $em->getRepository(Block::class)->processBlock( $block );                         
            $entity->getSlide()->setBlock($block);    
                    
            $em->persist($entity);
            $em->flush();

            return $this->redirect( $this->generateUrl('scan') );
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Scan entity.
     */
    #[Route(path: '/new', name: 'scan_new', methods: ['GET'])]
    public function newAction()
    {
        $helper = new FormHelper();
        $entity = new Imaging();
        
        //$slide= new Slide(); 
        //$entity->setSlide($slide);
                
        $entity->setMag( key($helper->getMags()) );       
        
        $form   = $this->createForm(ImagingType::class, $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Scan entity.
     */
    #[Route(path: '/{id}', name: 'scan_show', methods: ['GET'])]
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Imaging'] by [Imaging::class]
        $entity = $em->getRepository(Imaging::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Imaging entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Imaging entity.
     */
    #[Route(path: '/{id}/edit', name: 'scan_edit', methods: ['GET'])]
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Imaging'] by [Imaging::class]
        $entity = $em->getRepository(Imaging::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Imaging entity.');
        }

        $editForm = $this->createForm(ImagingType::class, $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Imaging entity.
     */
    #[Route(path: '/{id}', name: 'scan_update', methods: ['PUT'])]
    #[Template('AppOrderformBundle/Imaging/edit.html.twig')]
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Imaging'] by [Imaging::class]
        $entity = $em->getRepository(Imaging::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Imaging entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(ImagingType::class, $entity);
        $editForm->submit($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('scan_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Imaging entity.
     */
    #[Route(path: '/{id}', name: 'scan_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Imaging'] by [Imaging::class]
            $entity = $em->getRepository(Imaging::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Imaging entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('scan'));
    }

    /**
     * Creates a form to delete a Imaging entity by id.
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
    
    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = array();

        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                /**
                 * @var \Symfony\Component\Form\Form $child
                 */
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        } else {
            /**
             * @var \Symfony\Component\Form\FormError $error
             */
            foreach ($form->getErrors() as $key => $error) {
                $errors[] = $error->getMessage();
            }
        }

        return $errors;
    }
    
}
