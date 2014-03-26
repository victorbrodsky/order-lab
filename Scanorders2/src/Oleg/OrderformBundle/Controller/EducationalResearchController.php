<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\Educational;
use Oleg\OrderformBundle\Form\EducationalType;
use Oleg\OrderformBundle\Entity\Research;
use Oleg\OrderformBundle\Form\ResearchType;

/**
 * Educational and Research controller.
 */
class EducationalResearchController extends Controller {

    /**
     * Finds and displays a entity.
     *
     * @Route("/educational/{id}", name="educational_show")
     * @Route("/research/{id}", name="research_show")
     * @Method("GET")
     */
    public function showAction($id)
    {
        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //echo "routeName=".$routeName; //mrntype

        $pieces = explode("_", $routeName);
        $type = $pieces[0];
        //echo "type=".$type."<br>";

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$type.' entity.');
        }

        $editForm = $this->createEditForm($entity,'show');
        //$deleteForm = $this->createDeleteForm($id);

//        return array(
//            'entity'      => $entity,
//            'edit_form'   => $editForm->createView(),
//            'cicle' => 'show'
//            //'delete_form' => $deleteForm->createView(),
//        );

        return $this->render('OlegOrderformBundle:'.$type.':edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'cicle' => 'show'
        ));
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/educational/{id}/edit", name="educational_edit")
     * @Route("/research/{id}/edit", name="research_edit")
     * @Method("GET")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //echo "routeName=".$routeName; //mrntype

        $pieces = explode("_", $routeName);
        $type = $pieces[0];
        //echo "type=".$type."<br>";
        //exit();

        $entity = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$type.' entity.');
        }

        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

//        return array(
//            'entity'      => $entity,
//            'edit_form'   => $editForm->createView(),
//            //'delete_form' => $deleteForm->createView(),
//        );

        return $this->render('OlegOrderformBundle:'.$type.':edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'cicle' => 'show'
        ));
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/educational/{id}", name="educational_update")
     * @Route("/research/{id}", name="research_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $type = $pieces[0];

        $entity = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$type.' entity.');
        }

        $editForm = $this->createEditForm($entity);

        $editForm->bind($request);

        if ($editForm->isValid()) {
            //exit("form is valid!");

            $em->persist($entity);
            $em->flush();

            $orderid = $entity->getOrderinfo()->getId();
            //return $this->redirect($this->generateUrl($type.'_show', array('id' => $id)));
            return $this->redirect($this->generateUrl('scan-order-data-review-full', array('id' => $orderid)));
        } else {
            //exit("form is not valid ???");
        }

        return $this->render('OlegOrderformBundle:'.$type.':edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'cicle' => 'show'
        ));

    }

    private function createEditForm($entity, $cicle = null) {

        $params = array('type'=>'SingleObject');
        $disable = false;

        if( $cicle == 'show' ) {
            $disable = true;
        }

        if( $entity instanceof Educational ) {
            $typeform = new EducationalType($params);
            $type = "educational";
        }

        if( $entity instanceof Research ) {
            $typeform = new ResearchType($params);
            $type = "research";
        }

        $form = $this->createForm( $typeform, $entity, array(
            'action' => $this->generateUrl($type.'_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'disabled' => $disable
        ));

        if( $cicle != 'show' ) {
            $form->add('submit', 'submit', array('label' => 'Update', 'attr' => array('class' => 'btn btn-primary')));
        }

        return $form;
    }
}
