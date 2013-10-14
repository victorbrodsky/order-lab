<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oleg\OrderformBundle\Form\PatientType;

/**
 * OrderInfo controller.
 *
 * @Route("/check")
 * @Template("OlegOrderformBundle:Patient:edit_single.html.twig")
 */
class CheckController extends Controller {
      
    /**
     * @Route("/patient", name="get-patient")
     * @Method("GET")
     */
    public function getPatientAction() {

        $request = $this->get('request');
        $id   = $request->get('mrn');
        $id = "NOMRNPROVIDED-0000000001";

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OlegOrderformBundle:Patient')->findOneByMrn($id);
        $entity->setSpecimen(new \Doctrine\Common\Collections\ArrayCollection());

        $form   = $this->createForm(new PatientType(), $entity);

//        $delete_form = $this->createFormBuilder(array('id' => $id))
//            ->add('id', 'hidden')
//            ->getForm();

        return array(
            //'entity'   => $entity,
            'edit_form'   => $form->createView(),
            //'delete_form' => $delete_form->createView(),
        );

    }


    /**
     * @Route("/patient2", name="get-patient2")
     * @Method("GET")
     */
    public function getAction() {

        $out_json = array(
            'status' => "OK",
            'template' => $this->getPatientAction()
        );

        return new \Symfony\Component\HttpFoundation\Response(json_encode($out_json));
    }


}
