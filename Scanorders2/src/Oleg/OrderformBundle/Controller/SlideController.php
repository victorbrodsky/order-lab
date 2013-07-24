<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Form\SlideType;

use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Form\PartType;

use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Form\BlockType;

use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Helper as Helper;

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
        
        //get part, block by accession
        //$part = null;
        //$block = null;
        
        return $this->render('OlegOrderformBundle:Slide:index.html.twig',
            array(
                'slides' => $slides,
                //'part' => $part,
                //'block' => $block
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

            return $this->redirect($this->generateUrl('order_show', array('id' => $id)));        
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
        
        $helper = new Helper\FormHelper(); 
        
        $entity = new Slide();          
        //initialize default values in the form
        $entity->setMag( key($helper->getMags()) );
        $entity->setStain( key($helper->getStains()) );
        $orderInfo = new OrderInfo();
        $orderInfo->setPriority( key($helper->getPriority()) );
        $orderInfo->setSlideDelivery( key($helper->getSlideDelivery()) );
        $orderInfo->setReturnSlide( key($helper->getReturnSlide()) );
        $entity->setOrderinfo($orderInfo);
                         
        //$order_form = $this->createForm(new OrderInfoType(), new OrderInfo());
        $block_entity = new Block();
        $block_entity->setName(1);
        $entity->setBlock($block_entity);
        
        $part_entity = new Part();
        $part_entity->setName(0);
        $entity->setPart($part_entity);
        
        //$part_form = $this->createForm(new PartType(), $part_entity); 
        //$block_form = $this->createForm(new BlockType(), $block_entity); 
        
        $form = $this->createForm(new SlideType(), $entity); 
        
        return $this->render('OlegOrderformBundle:Slide:new.html.twig',
            array(
                'form' => $form->createView(),
                //'part_form' => $part_form->createView(),
                //'block_form' => $block_form->createView()
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
        
        //$part_entity  = new Part();
        //$part_form = $this->createForm(new PartType(), $part_entity);
        //$part_form->bind($request);
        
        //$block_entity  = new Block();
        //$block_form = $this->createForm(new BlockType(), $block_entity);
        //$block_form->bind($request); 
        
        //$accession_number_form = $form["accession"]->getData();
        //$part_form["accession"]->setData($accession_number_form);
        //$block_form["accession"]->setData($accession_number_form);
        
        if( $form->isValid() ) { //&& $part_form->isValid() && $block_form->isValid() ) {
//            echo "stain=".$entity->getStain();
//            echo ", mag=".$entity->getMag();
//            echo "<br>";         
//            exit();
                          
            $em = $this->getDoctrine()->getManager();
            
            /*
             * Process accession number. 
             * If not exists - create and return new accession object, 
             * if exists - return existing accession object.
             * However, unique Accession nubmer is combination of Accession + Part + Block (i.e. "S12-99998 B1")
             */
//            $accession_number = $form["accession"]->getData();
//            $accession = $em->getRepository('OlegOrderformBundle:Accession')->processAccession( $accession_number );                         
//            $entity->setAccession($accession);
            
            $accession_number = $entity->getAccession();          
            //$part_entity->setAccession($accession_number);
            //$block_entity->setAccession($accession_number);
            
            $entity->getOrderinfo()->setStatus("Submitted");
            
            $em->persist($entity);  
            //$em->persist($part_entity);
            //$em->persist($block_entity);
            
            $em->flush();
            
            return $this->redirect(
                    $this->generateUrl('order' 
                            //array(
                            //    'id' => $entity->getId()                              
                            //)
                    ));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),         
        );
    }
      
    /**
     * Finds and displays a Slide entity. //Option defaults={"id" = 1} does not work: it does order/1 => order/
     *
     * @Route("/{id}", name="order_show", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function showAction($id) {
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Slide')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Slide entity.');
        }

        $deleteForm = $this->createDeleteForm( $id );

        ////
        $form = $this->createForm( new SlideType(), $entity, array('disabled' => true) );
        ///
        
        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'form'   => $form->createView(),
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
