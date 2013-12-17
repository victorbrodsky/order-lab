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
            $hist['validity'] = $field->getStatus();

            if( $childrenArr ) {
                foreach( $childrenArr as $child ) {
                    $getMethod = "get".$child;
                    //echo "getMethod=".$getMethod."<br>";

                    if( $child == "mrntype" ) {
                        $childValue = $field->$getMethod()->getId();
                        //echo "childValue=".$childValue."<br>";
                        $hist[$child] = $childValue;
                    } else {
                        $childValue = $field->$getMethod()."";
                        //echo "childValue=".$childValue."<br>";
                        $hist[$child] = $childValue;
                    }


                }
            }

            $fieldJson[] = $hist;

        }

        return $fieldJson;
    }


    /**
     * Find an element in DB
     * @Route("/patient", name="get-patientdata")
     * @Method("GET")   //TODO: use POST?
     */
    public function getPatientAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->get('request');
        $key = $request->get('key');
        $mrntype = $request->get('extra');
        $extra = array();
        $extra["mrntype"] = $mrntype;
        //echo "key=".$key.", mrntype=".$mrntype."; ";

        //TODO: select2 is not set correctly by clean in checkForm.js? So, mrntype is ""
//        if( $mrntype == "" ) {
//            $entityTemp = $this->getDoctrine()->getRepository('OlegOrderformBundle:MrnType')->findOneByName("New York Hospital MRN");   //get default value
//            $mrntype = $entityTemp->getId(); //id of "New York Hospital MRN" in DB
//        }
        //echo "key=".$key.", mrntype=".$mrntype."; ";

        //$em = $this->getDoctrine()->getManager();
        //$entity = $em->getRepository('OlegOrderformBundle:Patient')->findOneByMrn($mrn);
        $entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Patient')->findOneByIdJoinedToField($key,"Patient","mrn",true,true,$extra);   //findOneByIdJoinedToMrn($mrn);
        //$entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Patient')->findOnePatientByIdJoinedToField($key,$mrntype,true);   //findOneByIdJoinedToMrn($mrn);

        if( $entity ) {

            $element = array(
                //'inmrn'=>$mrn,
                'id'=>$entity->getId(),
                'mrn'=>$this->getArrayFieldJson($entity->getMrn(),array('mrntype')),
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
     * Create new element with status RESERVED
     * @Route("/patientmrn", name="create-mrn")
     * @Method("GET")
     */
    public function createPatientAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $request = $this->get('request');
        $mrntype = $request->get('key');

        $extra = array();
        $extra["mrntype"] = $mrntype;

        //echo "mrntype=".$mrntype."<br>";
        //TODO: select2 is not set correctly by clean in checkForm.js? So, mrntype is ""
//        if( $mrntype == "" ) {
//            $entityTemp = $this->getDoctrine()->getRepository('OlegOrderformBundle:MrnType')->findOneByName("New York Hospital MRN");   //get default value
//            $mrntype = $entityTemp->getId().""; //id of "New York Hospital MRN" in DB
//        }
        //echo "mrntype=".$mrntype."<br>";

        $em = $this->getDoctrine()->getManager();
        //$entity = $em->getRepository('OlegOrderformBundle:Patient')->createPatient();
        $entity = $em->getRepository('OlegOrderformBundle:Patient')->createElement(null,$user,"Patient","mrn",null,null,$extra);
        //$entity = $em->getRepository('OlegOrderformBundle:Patient')->createPatient(null, $user, $mrntype );
        //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

        //$entity->getValidKeyfield()->setMrntype($mrntype);
        //echo "mrntype name = ".$entity->getMrn()->first()->getMrntype()." ";

        $element = array(
            'id'=>$entity->getId(),
            'mrn'=>$this->getArrayFieldJson($entity->getMrn(),array('mrntype')),
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
     * @Route("/mrn/check/{key}", name="delete-mrn-mrntype")
     * @Method("DELETE")
     */
    public function deleteMrnAction( Request $request ) {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

//        $arr = explode("_", $keymrntype);
//        $key = $arr[0];
//        $mrntype = $arr[1];

        $key = $request->get('key');
        $mrntype = $request->get('extra');

        $extra = array();
        $extra["mrntype"] = $mrntype;

        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('OlegOrderformBundle:Patient')->deleteIfReserved( $key,"Patient","mrn",$extra );
        //$res = $em->getRepository('OlegOrderformBundle:Patient')->deletePatientIfReserved( $key, $mrntype );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }



    /************************ ACCESSION *************************/
    /**
     * Find accession by #
     * @Route("/accession", name="get-accession")
     * @Method("GET")
     */
    public function getAccessionAction() {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->get('request');
        $key = $request->get('key');

        $entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Accession')->findOneByIdJoinedToField($key,"Accession","accession",true, true);

        //$procedure = $this->getDoctrine()->getRepository('OlegOrderformBundle:Procedure')->findOneByAccession($entity);

        if( $entity ) {

            //find patient mrn
            $patient = $entity->getProcedure()->getPatient();

            if( $patient ) {
                $parentKey = $patient->getValidKeyfield();
                $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
                $dateStr = $transformer->transform($parentKey->getCreationdate());
                $mrnstring = 'MRN '.$parentKey.', '.$parentKey->getMrntype().' (as submitted by '.$parentKey->getProvider().' on '. $dateStr.')';
                $extraid = $parentKey->getMrntype()->getId()."";
                $orderinfoString = "Order #".$patient->getOrderinfo()->first()->getId()." submitted on ".$transformer->transform($patient->getOrderinfo()->first()->getOrderdate()). " by ". $patient->getOrderinfo()->first()->getProvider()->first();
            } else {
                $parentKey = null;
                $mrnstring = "";
                $extraid = "";
                $orderinfoString = "";
            }

            //echo "mrnstring=".$mrnstring." ";

            $element = array(
                'id'=>$entity->getId(),
                'parent'=>$parentKey."",
                'extraid'=>$extraid,
                'mrnstring'=>$mrnstring,
                'orderinfo'=>$orderinfoString,
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

        $user = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OlegOrderformBundle:Accession')->createElement(null,$user,"Accession","accession");
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

        $entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Part')->findOnePartByJoinedToField( $accession, $key, true );

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

        if( $accession && $accession != ""  ) {

            $em = $this->getDoctrine()->getManager();
            $part = $em->getRepository('OlegOrderformBundle:Part')->createPartByAccession($accession);
            //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

            if( $part ) {
                $user = $this->get('security.context')->getToken()->getUser();
                $part->getPartname()->first()->setProvider($user);
                //$validPartname = $em->getRepository('OlegOrderformBundle:Part')->getValidField($part->getPartname());
                $element = array(
                    'id'=>$part->getId(),
                    'partname'=>$this->getArrayFieldJson($part->getPartname())
                );
            } else {
                $element = null;
            }

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
    public function deletePartAction(Request $request) {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $key = $request->get('key');
        $accession = $request->get('accession');

        $extra = array();
        $extra["accession"] = $accession;

        //echo "key=".$key." , accession=".$accession."   ";

        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('OlegOrderformBundle:Part')->deleteIfReserved( $key,"Part","partname", $extra );

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

        if( $accession != "" && $partname != "" ) {
            $entity = $this->getDoctrine()->getRepository('OlegOrderformBundle:Block')->findOneBlockByJoinedToField( $accession, $partname, $key, true );

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

        if( $accession != "" && $partname != "" ) {

            $em = $this->getDoctrine()->getManager();
            $block = $em->getRepository('OlegOrderformBundle:Block')->createBlockByPartnameAccession($accession,$partname);
            //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

            $user = $this->get('security.context')->getToken()->getUser();
            $block->getBlockname()->first()->setProvider($user);

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
    public function deleteBlockAction(Request $request) {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $key = $request->get('key');
        $accession = $request->get('accession');
        $partname = $request->get('partname');

        $extra = array();
        $extra["accession"] = $accession;
        $extra["partname"] = $partname;

        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('OlegOrderformBundle:Block')->deleteIfReserved( $key,"Block","blockname", $extra );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }

}
