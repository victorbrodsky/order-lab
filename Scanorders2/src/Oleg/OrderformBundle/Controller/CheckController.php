<?php

namespace Oleg\OrderformBundle\Controller;

use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\PatientMrn;
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

    public function getArrayFieldJson( $fields, $childrenArr = null ) {

        //echo "fields count=".count($fields)."  ";
        $fieldJson = array();
        foreach( $fields as $field ) {

            //echo "field=".$field." ";

            $providerStr = "";
            //echo "provider count=".count($field->getProvider()).", provider name=".$field->getProvider()->getUsername().", ";

            $provider = $field->getProvider();
            if( $provider ) {
                if( $provider->getDisplayName() != "" ) {
                    $providerStr = $providerStr." ".$provider->getDisplayName();
                } else {
                    $providerStr = $providerStr." ".$provider->getUsername();
                }
            }

            //echo "providerStr=".$providerStr.", ";

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($field->getCreationdate());

            $hist = array();
            $hist['id'] = $field->getId();
            $hist['text'] = $field."";
            $hist['provider'] = $providerStr;
            $hist['date'] = $dateStr;
            $hist['validity'] = $field->getValidity();

            if( $childrenArr ) {
                foreach( $childrenArr as $child ) {
                    $getMethod = "get".$child;
                    //echo "getMethod=".$getMethod."<br>";
                    $childValue = $field->$getMethod()."";
                    //echo "childValue=".$childValue."<br>";
                    $hist[$child] = $childValue;
                }
            }

            $fieldJson[] = $hist;

        }

        return $fieldJson;
    }


    /**
     * @Route("/patient", name="get-patientdata")
     * @Method("GET")   //TODO: use POST?
     */
    public function getPatientAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->get('request');
        $key = $request->get('key');

        //$em = $this->getDoctrine()->getManager();
        //$entity = $em->getRepository('OlegOrderformBundle:Patient')->findOneByMrn($mrn);
        $entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Patient')->findOneByIdJoinedToField($key,"Patient","mrn",true);   //findOneByIdJoinedToMrn($mrn);

        if( $entity ) {

            $element = array(
                //'inmrn'=>$mrn,
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

    /**
     * Get next available MRN from DB
     * @Route("/patientmrn", name="create-mrn")
     * @Method("GET")
     */
    public function createPatientAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        //$entity = $em->getRepository('OlegOrderformBundle:Patient')->createPatient();
        $entity = $em->getRepository('OlegOrderformBundle:Patient')->createElement(null,null,"Patient","mrn");
        //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

//        $entity = new Patient();
//        $mrn = new PatientMrn(1);
//        $mrn->setField("NOMRNPROVIDED-0000000003");
//        $entity->addMrn($mrn);

        $element = array(
            'id'=>$entity->getId(),
            'mrn'=>$this->getArrayFieldJson($entity->getMrn()),
            'name'=>$this->getArrayFieldJson($entity->getName()),
            'sex'=>$this->getArrayFieldJson($entity->getSex()),
            'dob'=>$this->getArrayFieldJson($entity->getDob()),
            'age'=>$this->getArrayFieldJson($entity->getAge()),
            'clinicalHistory'=>$this->getArrayFieldJson($entity->getClinicalHistory())
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    /**
     * @Route("/mrn/check/{key}", name="delete-mrn")
     * @Method("DELETE")
     */
    public function deleteMrnAction($key) {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('OlegOrderformBundle:Patient')->deleteIfReserved( $key,"Patient","mrn" );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }



    /************************ ACCESSION *************************/
    /**
     * Get next available MRN from DB
     * @Route("/accession", name="get-accession")
     * @Method("GET")
     */
    public function getAccessionAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->get('request');
        $key = $request->get('key');

        $entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Accession')->findOneByIdJoinedToField($key,"Accession","accession",true);

        //$procedure = $this->getDoctrine()->getRepository('OlegOrderformBundle:Procedure')->findOneByAccession($entity);

        if( $entity ) {

            //find patient mrn
            //$patient = $this->getDoctrine()->getRepository('OlegOrderformBundle:Patient')->findOneByIdJoinedToField($key,"Patient","mrn",true);
            $patient = $entity->getProcedure()->getPatient();

            if( $patient ) {
                $parentKey = $this->getDoctrine()->getRepository('OlegOrderformBundle:Patient')->getValidField( $patient->getMrn() );
            } else {
                $parentKey = null;
            }

            $element = array(
                'id'=>$entity->getId(),
                'parent'=>$parentKey."",
                'procedure'=>$this->getArrayFieldJson($entity->getProcedure()->getName()),
                'accession'=>$this->getArrayFieldJson($entity->getAccession()),
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
     * Get next available Accession from DB
     * @Route("/accessionaccession", name="create-accession")
     * @Method("GET")
     */
    public function createAccessionAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OlegOrderformBundle:Accession')->createElement(null,null,"Accession","accession");
        //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

        $element = array(
            'id'=>$entity->getId(),
            'accession'=>$this->getArrayFieldJson($entity->getAccession()),
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    /**
     * @Route("/accession/check/{key}", name="delete-accession")
     * @Method("DELETE")
     */
    public function deleteAccessionAction($key) {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('OlegOrderformBundle:Accession')->deleteIfReserved( $key,"Accession","accession" );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }


    /************************ PART *************************/
    /**
     * Get Part from DB if existed
     * @Route("/part", name="get-part")
     * @Method("GET")
     */
    public function getPartAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->get('request');
        $key = $request->get('key');
        $accession = $request->get('parent'); //need accession number to check if part exists in DB
        //echo "key=".$key."   ";

        //$entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Part')->findOneByIdJoinedToField($key,"Part","partname",true);
        $entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Part')->findOnePartByJoinedToField( $accession, $key );

        //echo "count=".count($entity)."<br>";
        //echo "partname=".$entity->getPartname()->first()."<br>";

        if( $entity ) {

            $element = array(
                'id'=>$entity->getId(),
                'partname'=>$this->getArrayFieldJson($entity->getPartname()),
                'sourceOrgan'=>$this->getArrayFieldJson($entity->getSourceOrgan()),
                'description'=>$this->getArrayFieldJson($entity->getDescription()),
                'disident'=>$this->getArrayFieldJson($entity->getDisident()),
                'paper'=>$this->getArrayFieldJson($entity->getPaper()),
                'diffDisident'=>$this->getArrayFieldJson($entity->getDiffDisident()),
                'diseaseType'=>$this->getArrayFieldJson( $entity->getDiseaseType(), array("origin","primaryorgan") )
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
     * Get next available Part from DB by giving Accession number
     * @Route("/partpartname", name="create-part")
     * @Method("GET")
     */
    public function createPartAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->get('request');
        $accession = $request->get('key');

        //echo "accession=(".$accession.")   ";

        $em = $this->getDoctrine()->getManager();
        $part = $em->getRepository('OlegOrderformBundle:Part')->createPartByAccession($accession);
        //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

        //echo "partname=".$part->getPartname()."  ";

        if( $part ) {
            //$validPartname = $em->getRepository('OlegOrderformBundle:Part')->getValidField($part->getPartname());
            $element = array(
                'id'=>$part->getId(),
                'partname'=>$this->getArrayFieldJson($part->getPartname())
            );
        } else {
            $element = null;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    /**
     * @Route("/partname/check/{key}", name="delete-part")
     * @Method("DELETE")
     */
    public function deletePartAction($key) {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('OlegOrderformBundle:Part')->deleteIfReserved( $key,"Part","partname" );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }


    /************************ BLOCK *************************/
    /**
     * Get BLOCK from DB if existed
     * @Route("/block", name="get-block")
     * @Method("GET")
     */
    public function getBlockAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->get('request');
        $key = $request->get('key');
        $accession = $request->get('parent'); //need accession number to check if part exists in DB
        $partname = $request->get('parent2'); //need accession number to check if part exists in DB
        //echo "key=".$key."   ";

        //$entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Part')->findOneByIdJoinedToField($key,"Part","partname",true);
        $entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Block')->findOneBlockByJoinedToField( $accession, $partname, $key );

        //echo "count=".count($entity)."<br>";
        //echo "partname=".$entity->getPartname()->first()."<br>";

        if( $entity ) {

            $element = array(
                'id'=>$entity->getId(),
                'blockname'=>$this->getArrayFieldJson($entity->getBlockname()),
                'sectionsource'=>$this->getArrayFieldJson($entity->getSectionsource()),
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
     * Get next available Block from DB by giving Accession number and Part name
     * @Route("/blockblockname", name="create-block")
     * @Method("GET")
     */
    public function createBlockAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->get('request');
        $accession = $request->get('key');
        $partname = $request->get('key2');

        //echo "accession=(".$accession.")   ";

        $em = $this->getDoctrine()->getManager();
        $block = $em->getRepository('OlegOrderformBundle:Block')->createBlockByPartnameAccession($accession,$partname);
        //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

        //echo "partname=".$part->getPartname()."  ";

        if( $block ) {
            //$validPartname = $em->getRepository('OlegOrderformBundle:Part')->getValidField($part->getPartname());
            $element = array(
                'id'=>$block->getId(),
                'blockname'=>$this->getArrayFieldJson($block->getBlockname())
            );
        } else {
            $element = null;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    /**
     * @Route("/blockname/check/{key}", name="delete-block")
     * @Method("DELETE")
     */
    public function deleteBlockAction($key) {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('OlegOrderformBundle:Part')->deleteIfReserved( $key,"Block","blockname" );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }

}
