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

use App\OrderformBundle\Entity\Patient;
use App\OrderformBundle\Entity\PatientMrn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\OrderformBundle\Form\PatientType;
use App\OrderformBundle\Entity\ClinicalHistory;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use App\UserdirectoryBundle\Util\UserUtil;

/**
 * Message controller.
 *
 * @Route("/check")
 * @Template("AppOrderformBundle/Patient/edit_single.html.twig")
 */
class CheckController extends AbstractController {

    public function getArrayFieldJson( $fields, $childrenArr = null ) {

        //echo "fields count=".count($fields)."  ";
        $fieldJson = array();

        foreach( $fields as $field ) {

            //echo "field=".$field." ";

            if( $field == null ) {
                $fieldJson[] = null;
                continue;
            }

            $provider = $field->getProvider();
            $providerStr = $provider->getUserNameStr();

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

                    if( $child == "keytype" ) {
                        $childValue = $field->$getMethod()->getId();
                        //echo "childValue=".$childValue."<br>";
                        $hist[$child] = $childValue;
                        $hist['keytypename'] = $field->$getMethod()."";
                    } else
                    if( $child == "staintype" ) {
                        $childValue = $field->$getMethod()->getId();
                        $hist[$child] = $childValue;
                    } else
                    if( $child == "documents" ) {
                            $childs = $field->$getMethod();
                            $children = array();
                            foreach( $childs as $onechild ) {
                                $childArr = array();
                                $childArr["id"] = $onechild->getId();
                                $childArr["uniquename"] = $onechild->getUniquename();
                                $childArr["originalname"] = $onechild->getOriginalnameClean();
                                $childArr["size"] = $onechild->getSize();
                                $childArr["url"] = $onechild->getAbsoluteUploadFullPath();
                                $children[] = $childArr;
                            }
                            $hist[$child] = $children;
                    } else
                    if( $child == "diseaseorigins" || $child == "diseasetypes" ) {
                        $childs = $field->$getMethod();
                        $children = array();
                        foreach( $childs as $onechild ) {
                            $childArr = array();
                            $childArr["id"] = $onechild->getId();
                            $childArr["name"] = $onechild->getName()."";
                            $children[] = $childArr;
                        }
                        $hist[$child] = $children;
                    } else
                    {
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
     * @Route("/patient/check", name="get-patientdata")
     * @Method("GET")   //TODO: use POST?
     */
    public function getPatientAction(Request $request) {

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $key = trim( $request->get('key') );
        $keytype = trim( $request->get('extra') );
        $inst = trim( $request->get('inst') );

        $originalKeytype = $keytype;
        
        $em = $this->getDoctrine()->getManager();
        $keytype = $em->getRepository('AppOrderformBundle:Patient')->getCorrectKeytypeId($keytype,$user);

        $extra = array();
        $extra["keytype"] = $keytype;

        //echo "key=".$key.", keytype=".$keytype.", inst=".$inst." ";

        $validity = array('valid','reserved');

        $institutions = array();
        $institutions[] = $inst;

        $entity = $em->getRepository('AppOrderformBundle:Patient')->findOneByIdJoinedToField($institutions,$key,"Patient","mrn",$validity,true,$extra);   //findOneByIdJoinedToMrn($mrn);

        $element = array();
        
        //$security_content = $this->get('security.context');
        //$user = $this->get('security.token_storage')->getToken()->getUser();
        //$userUtil = new UserUtil();
        $securityUtil = $this->get('user_security_utility');
        if( $entity && !$securityUtil->hasUserPermission($entity,$user,array("Union","Intersection"),array("show")) ) {
            //echo "no permission ";
            $entity = null;
            //$entity->filterArrayFields($user,true);
        }

        if( $entity && $entity->obtainExistingFields(true) == 0 ) { //if all fields are empty make entity = null
            $entity = null;
        }

        if( !is_numeric ( $originalKeytype ) ) {
            $originalKeytype = $keytype;
        }
        
        $originalKeytype = $em->getRepository('AppOrderformBundle:MrnType')->findOneById($originalKeytype);
        if( $originalKeytype == "Existing Auto-generated MRN" && !$entity ) {
            $entity = null;               
            $element = -2;
        }
        
        if( $entity ) {

            $element = array(
                //'inmrn'=>$mrn,
                'id'=>$entity->getId(),
                'mrn'=>$this->getArrayFieldJson($entity->getMrn(),array('keytype')),
                'fullname'=>$entity->getFullPatientName(),
                'sex'=>$this->getArrayFieldJson( array($entity->obtainValidField('sex')) ),
                'dob'=>$this->getArrayFieldJson( array($entity->obtainValidField('dob')) ), //$this->getArrayFieldJson($entity->getDob()),
                'age'=>$entity->calculateAge(),
                'clinicalHistory'=>$this->getArrayFieldJson($entity->obtainAllValidNotEmptyClinicalHistories()),
                'fullObjectName'=>$entity->obtainFullObjectName()
            );
        } 

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    /**
     * Create new element with status RESERVED
     * @Route("/patient/generate", name="create-mrn")
     * @Method("GET")
     */
    public function createPatientAction(Request $request) {

//        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
//            return $this->render('AppOrderformBundle/Security/login.html.twig');
//        }

        $inst = trim( $request->get('inst') );

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $keytypeEntity = $this->getDoctrine()->getRepository('AppOrderformBundle:MrnType')->findOneByName("Auto-generated MRN");
        $keytype = $keytypeEntity->getId().""; //id of "New York Hospital MRN" in DB

        $extra = array();
        $extra["keytype"] = $keytype;

        //echo "keytype=".$keytype."<br>";
        //exit();

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppOrderformBundle:Patient')->createElement(
            $inst,
            null,       //status
            $user,      //provider
            "Patient",  //$className
            "mrn",      //$fieldName
            null,       //$parent
            null,       //$fieldValue
            $extra,     //$extra
            false        //$withfields
        );
        
        $element = array(
            'id'=>$entity->getId(),
            'mrn'=>$this->getArrayFieldJson($entity->getMrn(),array('keytype')),
            'dob'=>$this->getArrayFieldJson($entity->getDob()),
            'clinicalHistory'=>$this->getArrayFieldJson($entity->getClinicalHistory())
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    /**
     * TODO: test on collage. DELETE might not work with a new php version and ajax calls (symfony bug?)?
     * @Route("/patient/delete/{key}", name="delete-mrn-keytype")
     * @Method({"POST", "DELETE"})
     */
    public function deleteMrnAction( Request $request ) {
        //echo "deleteMrnAction key=".$key."<br>";
        //exit('delete finish');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $key = trim( $request->get('key') );
        $keytype = trim( $request->get('extra') );
        //echo "keytype=$keytype<br>";

        $inst = trim( $request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;

        $em = $this->getDoctrine()->getManager();
        $keytype = $em->getRepository('AppOrderformBundle:Patient')->getCorrectKeytypeId($keytype,$user);

        $extra = array();
        $extra["keytype"] = $keytype;

        $res = $em->getRepository('AppOrderformBundle:Patient')->deleteIfReserved( $institutions, $key,"Patient","mrn",$extra );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }



    /************************ ACCESSION *************************/
    /**
     * Find accession by #
     * @Route("/accession/check", name="get-accession")
     * @Method("GET")
     */
    public function getAccessionAction(Request $request) {

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $key = trim( $request->get('key') );
        $keytype = trim( $request->get('extra') );  //id or string of accession type
        $inst = trim( $request->get('inst') );
        
        $originalKeytype = $keytype;

        $em = $this->getDoctrine()->getManager();

        $keytype = $em->getRepository('AppOrderformBundle:Accession')->getCorrectKeytypeId($keytype,$user);
        //echo "keytype2=".$keytype.", key=".$key." => ";

        $extra = array();
        $extra["keytype"] = $keytype;

        $validity = array('valid','reserved');

        $institutions = array();
        $institutions[] = $inst;

        $entity = $em->getRepository('AppOrderformBundle:Accession')->findOneByIdJoinedToField($institutions,$key,"Accession","accession",$validity,true,$extra);

        $element = array();

        $securityUtil = $this->get('user_security_utility');
        if( $entity && !$securityUtil->hasUserPermission($entity,$user,array("Union","Intersection"),array("show")) ) {
            $entity = null;
        }

        if( $entity && $entity->obtainExistingFields(true) == 0 && $entity->getParent()->obtainExistingFields(true) == 0 ) { //if all fields are empty make entity = null
            $entity = null;
        }

        if( !is_numeric ( $originalKeytype ) ) {
            $originalKeytype = $keytype;
        }

        $originalKeytype = $em->getRepository('AppOrderformBundle:AccessionType')->findOneById($originalKeytype);
        if( $originalKeytype == "Existing Auto-generated Accession Number" && !$entity ) {
            $entity = null;               
            $element = -2;
        }

        //echo "entity=".$entity."<br>";

        if( $entity ) { //$entity - Accession

            $parentKey = null;
            $encounter = null;

            $procedure = $entity->getProcedure();

            if( $procedure ) {
                $encounter = $procedure->getEncounter();
            }

            if( $procedure && $encounter ) {

                $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');

                //find patient mrn
                $patient = $encounter->getPatient();
                //if( !$permission ) {
                //    $patient->filterArrayFields($user,true);
                //}

                if( $patient ) {
                    $parentKey = $patient->obtainValidKeyfield();
                    //$parentDob = $patient->obtainValidDob();
                    $parentDob = $patient->obtainValidField('dob');
                }

                //echo "parentKey=".$parentKey."<br>";

                if( $patient && $parentKey ) {
                    $parentKey = $patient->obtainValidKeyfield();
                    $dateStr = $transformer->transform($parentKey->getCreationdate());
                    $mrnstring = 'MRN '.$parentKey.' ['.$parentKey->getKeytype().'] (as submitted by '.$parentKey->getProvider().' on '. $dateStr.')';
                    $extraid = $parentKey->getKeytype()->getId()."";
                    $mrnkeytype = $em->getRepository('AppOrderformBundle:MrnType')->findOneById($extraid);
                    if( $mrnkeytype == "Auto-generated MRN" ) {
                        //set to "Existing Auto-generated MRN" in order to correct set select2 to "Existing Auto-generated MRN"
                        $newkeytype = $em->getRepository('AppOrderformBundle:MrnType')->findOneByName("Existing Auto-generated MRN");
                        $extraid = $newkeytype->getId()."";
                    }
                    $messageString = "Order ".$patient->getMessage()->first()->getId()." submitted on ".$transformer->transform($patient->getMessage()->first()->getOrderdate()). " by ". $patient->getMessage()->first()->getProvider();
                }

                //procedure's fields: Procedure Type (Name)
                $procedureName = $procedure->getName();
                //$procedureNumber = $procedure->getNumber();

                //encounter's fields: date, suffix, lastname, firstname, middlename, sex, age, history
                //$encounterName = $encounter->getName();
                $encounterDate = $encounter->getDate();
                $patSuffix = $encounter->getPatsuffix();
                $patLastName = $encounter->getPatlastname();
                $patFirstName = $encounter->getPatfirstname();
                $patMiddleName = $encounter->getPatmiddlename();
                $patSex = $encounter->getPatsex();
                $patAge = $encounter->getPatage();
                $patHist = $encounter->getPathistory();

            }//if $entity->getProcedure()

            if( !$parentKey ) {
                $parentKey = null;
                $mrnstring = "";
                $extraid = "";
                $parentDob = "";
                $messageString = "";
                //$encounterName = array();
                $procedureName = array();

                $encounterDate = array();
                $patSuffix = array();
                $patLastName = array();
                $patFirstName = array();
                $patMiddleName = array();
                $patSex = array();
                $patAge = array();
                $patHist = array();
            }

            //echo "mrnstring=".$mrnstring." ";

            $element = array(
                'id'=>$entity->getId(),
                'parent'=>$parentKey."",
                'extraid'=>$extraid,
                'parentdob'=>$parentDob."",
                'mrnstring'=>$mrnstring,
                'message'=>$messageString,

                //procedure data
                'procedure'=>$this->getArrayFieldJson($procedureName),

                //encounter data
                'date'=>$this->getArrayFieldJson($encounterDate),
                'patsuffix'=>$this->getArrayFieldJson($patSuffix,array('alias')),
                'patlastname'=>$this->getArrayFieldJson($patLastName,array('alias')),
                'patfirstname'=>$this->getArrayFieldJson($patFirstName,array('alias')),
                'patmiddlename'=>$this->getArrayFieldJson($patMiddleName,array('alias')),
                'patsex'=>$this->getArrayFieldJson($patSex),
                'patage'=>$this->getArrayFieldJson($patAge),
                'pathistory'=>$this->getArrayFieldJson($patHist),

                //accession data
                'accession'=>$this->getArrayFieldJson($entity->getAccession(),array('keytype')),
                'accessionDate'=>$this->getArrayFieldJson($entity->getAccessionDate()),

                'fullObjectName'=>$entity->obtainFullObjectName()
            );
        } 

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    /**
     * Get next available Accession from DB
     * @Route("/accession/generate", name="create-accession")
     * @Method("GET")
     */
    public function createAccessionAction(Request $request) {

//        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
//            return $this->render('AppOrderformBundle/Security/login.html.twig');
//        }

        $inst = trim( $request->get('inst') );

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        //always use Auto-generated Accession Number keytype to generate the new key
        $typeEntity = $em->getRepository('AppOrderformBundle:AccessionType')->findOneByName("Auto-generated Accession Number");
        $keytype = $typeEntity->getId().""; //id of "New York Hospital MRN" in DB

        $extra = array();
        $extra["keytype"] = $keytype;

        //$status, $provider, $className, $fieldName, $parent = null, $fieldValue = null, $extra = null, $withfields = true, $flush=true
        $entity = $em->getRepository('AppOrderformBundle:Accession')->createElement(
            $inst,
            null,           //status
            $user,          //provider
            "Accession",    //$className
            "accession",    //$fieldName
            null,           //$parent
            null,           //$fieldValue
            $extra,         //$extra
            false           //$withfields
        );
        //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

        $element = array(
            'id'=>$entity->getId(),
            'accession'=>$this->getArrayFieldJson($entity->getAccession(),array('keytype')),
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    /**
     * @Route("/accession/delete/{key}", name="delete-accession")
     * @Method({"POST", "DELETE"})
     */
    public function deleteAccessionAction(Request $request) {

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $key = trim( $request->get('key') );
        $keytype = trim( $request->get('extra') );
        //echo "key=".$key.",keytype=".$keytype." | ";

        $inst = trim( $request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;

        $em = $this->getDoctrine()->getManager();

        $keytype = $em->getRepository('AppOrderformBundle:Accession')->getCorrectKeytypeId($keytype,$user);

        $extra = array();
        $extra["keytype"] = $keytype;

        $res = $em->getRepository('AppOrderformBundle:Accession')->deleteIfReserved( $institutions, $key,"Accession","accession",$extra );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }


    /************************ PART *************************/
    /**
     * Get Part from DB if existed
     * @Route("/part/check", name="get-part")
     * @Method("GET")
     */
    public function getPartAction(Request $request) {

        $key = trim( $request->get('key') );
        $accession = trim( $request->get('parentkey') ); //need accession number to check if part exists in DB
        $keytype = trim( $request->get('parentextra') );
        //echo "key=".$key."   ";

        $inst = trim( $request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;

        $validity = array('valid','reserved');

        $entity = $this->getDoctrine()->getRepository('AppOrderformBundle:Part')->findOnePartByJoinedToField( $institutions, $accession, $keytype, $key, $validity );

        //echo "count=".count($entity)."<br>";
        //echo "partname=".$entity->getPartname()->first()."<br>";

        $element = array();

        if( $entity && strpos($key,$entity->obtainNoprovidedKeyPrefix().'-') !== false ) {
            $entity = null;
            $element = -2;
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $securityUtil = $this->get('user_security_utility');
        if( !$securityUtil->hasUserPermission($entity,$user,array("Union","Intersection"),array("show")) ) {
            $entity = null;
        }

        if( $entity && $entity->obtainExistingFields(true) == 0 ) { //if all fields are empty make entity = null
            $entity = null;
        }

        if( $entity ) {

            $element = array(
                'id'=>$entity->getId(),
                'partname'=>$this->getArrayFieldJson($entity->getPartname()),
                'sourceOrgan'=>$this->getArrayFieldJson($entity->getSourceOrgan()),
                'parttitle'=>$this->getArrayFieldJson($entity->getParttitle()),
                'description'=>$this->getArrayFieldJson($entity->getDescription()),
                'disident'=>$this->getArrayFieldJson($entity->getDisident()),
                'paper'=>$this->getArrayFieldJson($entity->getPaper(), array("documents")),
                'diffDisident'=>$this->getArrayFieldJson($entity->getDiffDisident()),
                'diseaseType'=>$this->getArrayFieldJson( $entity->getDiseaseType(), array("diseasetypes","diseaseorigins","primaryorgan") ),
                'fullObjectName'=>$entity->obtainFullObjectName()
            );
        } 

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($element));
        return $response;
    }

    /**
     * Get next available Part from DB by giving Accession number
     * @Route("/part/generate", name="create-part")
     * @Method("GET")
     */
    public function createPartAction(Request $request) {

        $accession = trim( $request->get('parentkey') );
        $keytype = trim( $request->get('parentextra') );
        $inst = trim( $request->get('inst') );
        //echo "accession=(".$accession.")   ";

        if( $accession && $accession != ""  ) {

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();
            $part = $em->getRepository('AppOrderformBundle:Part')->createPartByAccession($inst,$accession,$keytype,$user);
            //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

            if( $part ) {
                $user = $this->get('security.token_storage')->getToken()->getUser();
                $part->getPartname()->first()->setProvider($user);
                //$validPartname = $em->getRepository('AppOrderformBundle:Part')->obtainValidField($part->getPartname());
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
     * @Route("/part/delete/{key}", name="delete-part")
     * @Method({"POST", "DELETE"})
     */
    public function deletePartAction(Request $request) {

        $key = trim( $request->get('key') );
        $accession = trim( $request->get('parentkey') );
        $keytype = trim( $request->get('parentextra') );

        $extra = array();
        $extra["accession"] = $accession;
        $extra["keytype"] = $keytype;

        $inst = trim( $request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;

        //echo "key=".$key." , accession=".$accession.", keytype=".$keytype."   ";

        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('AppOrderformBundle:Part')->deleteIfReserved( $institutions, $key,"Part","partname", $extra );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }


    /************************ BLOCK *************************/
    /**
     * Get BLOCK from DB if existed
     * @Route("/block/check", name="get-block")
     * @Method("GET")
     */
    public function getBlockAction(Request $request) {

        $key = trim($request->get('key'));
        $partname = trim($request->get('parentkey')); //need partname to check if part exists in DB
        $accession = trim($request->get('grandparentkey')); //need accession number to check if part exists in DB 
        $keytype = trim($request->get('grandparentextra'));    
        //echo "key=".$key."   ";

        $inst = trim( $request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;
        $validity = array('valid','reserved');

        if( $accession != "" && $partname != "" ) {
            $entity = $this->getDoctrine()->getRepository('AppOrderformBundle:Block')->findOneBlockByJoinedToField( $institutions, $accession, $keytype, $partname, $key, $validity );

            //echo "count=".count($entity)."<br>";
            //echo "partname=".$entity->getPartname()->first()."<br>";
            
            $element = array();

            if( $entity && strpos($key,$entity->obtainNoprovidedKeyPrefix().'-') !== false ) {
                $entity = null;
                $element = -2;
            }

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $securityUtil = $this->get('user_security_utility');
            if( !$securityUtil->hasUserPermission($entity,$user,array("Union","Intersection"),array("show")) ) {
                $entity = null;
            }

            if( $entity && $entity->obtainExistingFields(true) == 0 ) { //if all fields are empty make entity = null
                $entity = null;
            }

            if( $entity ) {

                $element = array(
                    'id'=>$entity->getId(),
                    'blockname'=>$this->getArrayFieldJson($entity->getBlockname()),
                    'sectionsource'=>$this->getArrayFieldJson($entity->getSectionsource()),
                    'specialStains'=>$this->getArrayFieldJson( $entity->getSpecialStains(), array("field","staintype") ),
                    'fullObjectName'=>$entity->obtainFullObjectName()
                );
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
     * @Route("/block/generate", name="create-block")
     * @Method("GET")
     */
    public function createBlockAction(Request $request) {
        $partname = trim($request->get('parentkey'));
        $accession = trim($request->get('grandparentkey')); //need accession number to check if part exists in DB 
        $keytype = trim( $request->get('grandparentextra') );
        $inst = trim( $request->get('inst') );
        //echo "accession=(".$accession.")   ";

        if( $accession != "" && $partname != "" ) {

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();
            $block = $em->getRepository('AppOrderformBundle:Block')->createBlockByPartnameAccession($inst,$accession,$keytype,$partname,$user);
            //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $block->getBlockname()->first()->setProvider($user);

            //echo "partname=".$part->getPartname()."  ";

            if( $block ) {
                //$validPartname = $em->getRepository('AppOrderformBundle:Part')->obtainValidField($part->getPartname());
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
     * @Route("/block/delete/{key}", name="delete-block")
     * @Method({"POST", "DELETE"})
     */
    public function deleteBlockAction(Request $request) {

        $key = trim($request->get('key'));            
        $partname = trim($request->get('parentkey'));
        $accession = trim($request->get('grandparentkey'));
        $keytype = trim( $request->get('grandparentextra') );

        $inst = trim( $request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;

        $extra = array();
        $extra["accession"] = $accession;
        $extra["keytype"] = $keytype;
        $extra["partname"] = $partname;

        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('AppOrderformBundle:Block')->deleteIfReserved( $institutions, $key,"Block","blockname", $extra );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }


    //get keytype ids by keytype string for handsontable
    /**
     * @Route("/accession/keytype/{keytype}", name="get-accession-keytypeid")
     * @Route("/patient/keytype/{keytype}", name="get-patient-keytypeid")
     * @Method("GET")
     */
    public function getKeytypeIdAction(Request $request, $keytype) {

        $em = $this->getDoctrine()->getManager();

        if( $request->get('_route') == "get-accession-keytypeid" ) {
            $keytypeEntity = $em->getRepository('AppOrderformBundle:AccessionType')->findOneByName($keytype);
        } else
        if( $request->get('_route') == "get-patient-keytypeid" ) {
            $keytypeEntity = $em->getRepository('AppOrderformBundle:MrnType')->findOneByName($keytype);
        } else {
            $keytypeEntity = null;
        }

        if( $keytypeEntity ) {
            $keytype = $keytypeEntity->getId();
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($keytype));
        return $response;
    }

//    /**
//     * @Route("/userrole", name="get-user-role")
//     * @Method("POST")
//     */
//    public function getUserRoleAction(Request $request) {
//
//        $external = 'true';
//
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//
//        if( !$user->hasRole('ROLE_SCANORDER_EXTERNAL_SUBMITTER') && !$user->hasRole('ROLE_SCANORDER_EXTERNAL_ORDERING_PROVIDER') ) {
//            $external = 'not_external_role';
//        }
//
//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode($external));
//        return $response;
//    }


}
