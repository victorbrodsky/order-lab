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
use Oleg\OrderformBundle\Entity\ClinicalHistory;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * OrderInfo controller.
 *
 * @Route("/check")
 * @Template("OlegOrderformBundle:Patient:edit_single.html.twig")
 */
class CheckController extends Controller {

//    /**
//     * @Route("/patientfull", name="get-patient")
//     * @Method("GET")
//     */
//    public function getPatientAction() {
//
//        $request = $this->get('request');
//        $id   = $request->get('mrn');
//        $id = "NOMRNPROVIDED-0000000001";
//
//        $em = $this->getDoctrine()->getManager();
//        $entity = $em->getRepository('OlegOrderformBundle:Patient')->findOneByMrn($id);
//        $entity->setSpecimen(new \Doctrine\Common\Collections\ArrayCollection());
//
//        $form   = $this->createForm(new PatientType(), $entity);
//
////        $delete_form = $this->createFormBuilder(array('id' => $id))
////            ->add('id', 'hidden')
////            ->getForm();
//
//        return array(
//            //'entity'   => $entity,
//            'edit_form'   => $form->createView(),
//            //'delete_form' => $delete_form->createView(),
//        );
//
//    }


    /**
     * @Route("/patient", name="get-patientdata")
     * @Method("GET")
     */
    public function getAction() {

        $request = $this->get('request');
        $mrn = $request->get('mrn');

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OlegOrderformBundle:Patient')->findOneByMrn($mrn);

        if( $entity ) {
            //$hist = new ClinicalHistory();
            //$hist->setClinicalHistory("new history");
            //$entity->addClinicalHistory($hist);

            $clinHistories = $entity->getClinicalHistory();

            $clinHistoriesJson = array();
            foreach( $clinHistories as $clinHist ) {

                $providerStr = "";
                if( count($clinHist->getProvider()) > 0 ) {
                    foreach( $clinHist->getProvider() as $provider ) {
                        if( $provider->getDisplayName() != "" ) {
                            $providerStr = $providerStr." ".$provider->getDisplayName();
                        } else {
                            $providerStr = $providerStr." ".$provider->getUsername();
                        }
                    }
                } else {
                    $providerStr = "unknown";
                }

                $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
                $dateStr = $transformer->transform($clinHist->getCreationdate());

                $hist = array();
                $hist['id'] = $clinHist->getId();
                $hist['text'] = $clinHist->getClinicalHistory();
                $hist['provider'] = $providerStr;
                $hist['date'] = $dateStr;
                $clinHistoriesJson[] = $hist;

            }

            $element = array(
                'inmrn'=>$mrn,
                'id'=>$entity->getId(),
                'mrn'=>$entity->getMrn(),
                'name'=>$entity->getName(),
                'sex'=>$entity->getSex(),
                'dob'=>$entity->getDob(),
                'age'=>$entity->getAge(),
                'clinicalHistory'=>$clinHistoriesJson
            );
        } else {
            $element = array();
        }



        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    /**
     * Get next available MRN from DB
     * @Route("/mrn", name="get-mrn")
     * @Method("GET")
     */
    public function getMrnAction() {

        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository('OlegOrderformBundle:Patient')->createPatient();

        $element = array(
            'mrn'=>$patient->getMrn()
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    /**
     * Get next available MRN from DB
     * @Route("/accession", name="get-accession")
     * @Method("GET")
     */
    public function getAccessionAction() {

        //$em = $this->getDoctrine()->getManager();
        //$mrn = $em->getRepository('OlegOrderformBundle:Patient')->getNextMrn();

        $element = array(
            'accession'=>""
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }


}
