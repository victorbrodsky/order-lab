<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Form\BlockType;

use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Form\BlockSlideType;
use Oleg\OrderformBundle\Helper\ErrorHelper;

/**
 * Block controller.
 *
 * @Route("/block")
 */
class BlockController extends Controller
{

    /**
     * Lists all Block entities.
     *
     * @Route("/", name="block")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:Block')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Block entity.
     *
     * @Route("/", name="block_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:Block:new_orig.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Block();
        $form = $this->createForm(new BlockType(), $entity);
        $form->bind($request);

        $errorHelper = new ErrorHelper();
        $errors = $errorHelper->getErrorMessages($form);
        print_r($errors);            
        
        if( $form->isValid() ) {
            
            echo "form is valid <br>";
            
//            $slide = $entity->getSlide()->getPart();
//            $slide->setAccession($accession);
//            $part = $em->getRepository('OlegOrderformBundle:Part')->processPart( $part ); 
//            $scan_entity->getSlide()->setPart($part);         
            
            $slide = $entity->getSlide();                      
            
            $em = $this->getDoctrine()->getManager();
                       
            $em->persist($entity);
            $em->flush();
        
            $this->get('session')->getFlashBag()->add(
                'notice',
                'You successfully submit a scan request! Confirmation email sent!'
            );
            
            return $this->redirect( $this->generateUrl('block') );
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Block entity.
     *
     * @Route("/new", name="block_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Block();
        
            
        // dummy code - this is here just so that the Task has some tags
        // otherwise, this isn't an interesting example
//        $slide1 = new Slide();
//        $slide1->setBarcode('slide1');
//        $entity->getSlide()->add($slide1);
//        $slide2 = new Slide();
//        $slide2->setBarcode('tag2');
//        $entity->getSlide()->add($slide2);
        // end dummy code
   
        
        $form   = $this->createForm(new BlockType(), $entity);
//        $form   = $this->createForm(new BlockType(), $entity);
        
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Block entity.
     *
     * @Route("/{id}", name="block_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Block')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Block entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Block entity.
     *
     * @Route("/{id}/edit", name="block_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Block')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Block entity.');
        }

        $editForm = $this->createForm(new BlockType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Block entity.
     *
     * @Route("/{id}", name="block_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Block:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Block')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Block entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new BlockType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('block_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Block entity.
     *
     * @Route("/{id}", name="block_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:Block')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Block entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('block'));
    }

    /**
     * Creates a form to delete a Block entity by id.
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
