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

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->get('request');
        $mrn = $request->get('mrn');

        $em = $this->getDoctrine()->getManager();
        //$entity = $em->getRepository('OlegOrderformBundle:Patient')->findOneByMrn($mrn);

//        $mrnEntity = $em->getRepository('OlegOrderformBundle:PatientMrn')->findOneByField($mrn);
//        if( $mrnEntity ) {
//            $entity = $mrnEntity->getPatient();
//        } else {
//            $entity = null;
//        }

//        $mrnEntity = $this->getDoctrine()->getRepository('OlegOrderformBundle:PatientMrn')->findOneByIdJoinedToPatient($mrn);
//        $entity = null;
//        if( $mrnEntity ) {
//            echo "count patients=".count($mrnEntity->getPatient())."<br>";
//            foreach( $mrnEntity->getPatient() as $patient ) {
//                foreach( $patient->getName() as $name ) {
//                    echo "Name=".$name->getField()."<br>";
//                }
//                $entity = $patient;
//            }
//        } else {
//            echo "no result";
//        }

        $entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Patient')->findOneByIdJoinedToMrn($mrn);

//        echo "count names=".count($entity->getName()).", ";
//
//        foreach( $entity->getName() as $name ) {
//            echo "Name=".$name->getField().", ";
//        }
//        echo "Name=".$entity->getName()->first().", ";

        if( $entity ) {
            //$hist = new ClinicalHistory();
            //$hist->setClinicalHistory("new history");
            //$entity->addClinicalHistory($hist);

//            $clinHistories = $entity->getClinicalHistory();
//            $clinHistoriesJson = array();
//            foreach( $clinHistories as $clinHist ) {
//
//                $providerStr = "";
//                if( count($clinHist->getProvider()) > 0 ) {
//                    foreach( $clinHist->getProvider() as $provider ) {
//                        if( $provider->getDisplayName() != "" ) {
//                            $providerStr = $providerStr." ".$provider->getDisplayName();
//                        } else {
//                            $providerStr = $providerStr." ".$provider->getUsername();
//                        }
//                    }
//                } else {
//                    $providerStr = "unknown";
//                }
//
//                $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
//                $dateStr = $transformer->transform($clinHist->getCreationdate());
//
//                $hist = array();
//                $hist['id'] = $clinHist->getId();
//                $hist['text'] = $clinHist.""; //getClinicalHistory();
//                $hist['provider'] = $providerStr;
//                $hist['date'] = $dateStr;
//                $clinHistoriesJson[] = $hist;
//
//            }

            $element = array(
                'inmrn'=>$mrn,
                'id'=>$entity->getId(),
                'mrn'=>$this->getArrayFieldJson($entity->getMrn()),
                'name'=>$this->getArrayFieldJson($entity->getName()),
                'sex'=>$this->getArrayFieldJson($entity->getSex()),
                'dob'=>$this->getArrayFieldJson($entity->getDob()),
                'age'=>$this->getArrayFieldJson($entity->getAge()),
                'clinicalHistory'=>$this->getArrayFieldJson($entity->getClinicalHistory())
            );
        } else {
            $element = array();
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    public function getArrayFieldJson( $fields ) {

        $fieldJson = array();
        foreach( $fields as $field ) {

            //echo $field."<br>";

            $providerStr = "";
            //echo "provider count=".count($field->getProvider()).", provider name=".$field->getProvider()->getUsername().", ";

            $provider = $field->getProvider();
            if( $provider->getDisplayName() != "" ) {
                $providerStr = $providerStr." ".$provider->getDisplayName();
            } else {
                $providerStr = $providerStr." ".$provider->getUsername();
            }

            //echo "providerStr=".$providerStr.", ";

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($field->getCreationdate());

            $hist = array();
            $hist['id'] = $field->getId();
            $hist['text'] = $field."";
            $hist['provider'] = $providerStr;
            $hist['date'] = $dateStr;
            $fieldJson[] = $hist;

        }

        return $fieldJson;
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
