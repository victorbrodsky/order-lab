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
     * @Route("/patientfull", name="get-patient")
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
     * @Route("/patient", name="get-patientdata")
     * @Method("GET")
     */
    public function getAction() {

        $request = $this->get('request');
        $mrn = $request->get('mrn');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OlegOrderformBundle:Patient')->findOneByMrn($mrn);

        $clinHist = $entity->getClinicalHistory();
        if( count($clinHist) == 0 ) {
            $clinHistStr = "";
            }
        if( count($clinHist) == 1 ) {
            $clinHistStr = $entity->getClinicalHistory()[0]->getClinicalHistory();
        }
        if( count($clinHist) > 1 ) {
            $clinHistStr = "Multi Cilinical History: Not Supported";
        }

        $element = array(
            'inmrn'=>$mrn,
            'id'=>$entity->getId(),
            'mrn'=>$entity->getMrn(),
            'name'=>$entity->getName(),
            'sex'=>$entity->getSex(),
            'dob'=>$entity->getDob(),
            'age'=>$entity->getAge(),
            'clinicalHistory'=>$clinHistStr
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }


}
