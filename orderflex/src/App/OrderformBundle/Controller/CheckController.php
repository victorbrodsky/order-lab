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



use App\OrderformBundle\Entity\AccessionType;
use App\OrderformBundle\Entity\MrnType; //process.py script: replaced namespace by ::class: added use line for classname=MrnType


use App\OrderformBundle\Entity\Accession; //process.py script: replaced namespace by ::class: added use line for classname=Accession


use App\OrderformBundle\Entity\Part; //process.py script: replaced namespace by ::class: added use line for classname=Part


use App\OrderformBundle\Entity\Block; //process.py script: replaced namespace by ::class: added use line for classname=Block
use App\OrderformBundle\Entity\Patient;
use App\OrderformBundle\Entity\PatientMrn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\OrderformBundle\Form\PatientType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use App\UserdirectoryBundle\Util\UserUtil;

/**
 * Message controller.
 */
#[Route(path: '/check')]
#[Template('AppOrderformBundle/Patient/edit_single.html.twig')]
class CheckController extends OrderAbstractController {

    public function getArrayFieldJson( $fields, $childrenArr = null ) {

        $userServiceUtil = $this->container->get('user_service_utility');
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
                                //$childArr["url"] = $onechild->getAbsoluteUploadFullPath();
                                $childArr["url"] = $userServiceUtil->getDocumentAbsoluteUrl($onechild);
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
     */
    #[Route(path: '/patient/check', name: 'get-patientdata', methods: ['GET'])]
    public function getPatientAction(Request $request) {

        $user = $this->getUser();

        $key = trim((string)$request->get('key') );
        $keytype = trim((string)$request->get('extra') );
        $inst = trim((string)$request->get('inst') );

        $originalKeytype = $keytype;
        
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $keytype = $em->getRepository(Patient::class)->getCorrectKeytypeId($keytype,$user);

        $extra = array();
        $extra["keytype"] = $keytype;

        //echo "key=".$key.", keytype=".$keytype.", inst=".$inst." ";

        $validity = array('valid','reserved');

        $institutions = array();
        $institutions[] = $inst;

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $entity = $em->getRepository(Patient::class)->findOneByIdJoinedToField($institutions,$key,"Patient","mrn",$validity,true,$extra);   //findOneByIdJoinedToMrn($mrn);

        $element = array();
        
        //$security_content = $this->container->get('security.context');
        //$user = $this->getUser();
        //$userUtil = new UserUtil();
        $securityUtil = $this->container->get('user_security_utility');
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
        
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
        $originalKeytype = $em->getRepository(MrnType::class)->findOneById($originalKeytype);
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
     */
    #[Route(path: '/patient/generate', name: 'create-mrn', methods: ['GET'])]
    public function createPatientAction(Request $request) {

//        if (false === $this->isGranted('ROLE_USER')) {
//            return $this->render('AppOrderformBundle/Security/login.html.twig');
//        }

        $inst = trim((string)$request->get('inst') );

        $user = $this->getUser();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
        $keytypeEntity = $this->getDoctrine()->getRepository(MrnType::class)->findOneByName("Auto-generated MRN");
        $keytype = $keytypeEntity->getId().""; //id of "New York Hospital MRN" in DB

        $extra = array();
        $extra["keytype"] = $keytype;

        //echo "keytype=".$keytype."<br>";
        //exit();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $entity = $em->getRepository(Patient::class)->createElement(
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
     */
    #[Route(path: '/patient/delete/{key}', name: 'delete-mrn-keytype', methods: ['POST', 'DELETE'])]
    public function deleteMrnAction( Request $request ) {
        //echo "deleteMrnAction key=".$key."<br>";
        //exit('delete finish');

        $user = $this->getUser();

        $key = trim((string)$request->get('key') );
        $keytype = trim((string)$request->get('extra') );
        //echo "keytype=$keytype<br>";

        $inst = trim((string)$request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $keytype = $em->getRepository(Patient::class)->getCorrectKeytypeId($keytype,$user);

        $extra = array();
        $extra["keytype"] = $keytype;

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $res = $em->getRepository(Patient::class)->deleteIfReserved( $institutions, $key,"Patient","mrn",$extra );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }



    /************************ ACCESSION *************************/
    /**
     * Find accession by #
     */
    #[Route(path: '/accession/check', name: 'get-accession', methods: ['GET'])]
    public function getAccessionAction(Request $request) {

        $user = $this->getUser();

        $key = trim((string)$request->get('key') );
        $keytype = trim((string)$request->get('extra') );  //id or string of accession type
        $inst = trim((string)$request->get('inst') );
        
        $originalKeytype = $keytype;

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Accession'] by [Accession::class]
        $keytype = $em->getRepository(Accession::class)->getCorrectKeytypeId($keytype,$user);
        //echo "keytype2=".$keytype.", key=".$key." => ";

        $extra = array();
        $extra["keytype"] = $keytype;

        $validity = array('valid','reserved');

        $institutions = array();
        $institutions[] = $inst;

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Accession'] by [Accession::class]
        $entity = $em->getRepository(Accession::class)->findOneByIdJoinedToField($institutions,$key,"Accession","accession",$validity,true,$extra);

        $element = array();

        $securityUtil = $this->container->get('user_security_utility');
        if( $entity && !$securityUtil->hasUserPermission($entity,$user,array("Union","Intersection"),array("show")) ) {
            $entity = null;
        }

        if( $entity && $entity->obtainExistingFields(true) == 0 && $entity->getParent()->obtainExistingFields(true) == 0 ) { //if all fields are empty make entity = null
            $entity = null;
        }

        if( !is_numeric ( $originalKeytype ) ) {
            $originalKeytype = $keytype;
        }

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
        $originalKeytype = $em->getRepository(AccessionType::class)->findOneById($originalKeytype);
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
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
                    $mrnkeytype = $em->getRepository(MrnType::class)->findOneById($extraid);
                    if( $mrnkeytype == "Auto-generated MRN" ) {
                        //set to "Existing Auto-generated MRN" in order to correct set select2 to "Existing Auto-generated MRN"
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
                        $newkeytype = $em->getRepository(MrnType::class)->findOneByName("Existing Auto-generated MRN");
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
     */
    #[Route(path: '/accession/generate', name: 'create-accession', methods: ['GET'])]
    public function createAccessionAction(Request $request) {

//        if (false === $this->isGranted('ROLE_USER')) {
//            return $this->render('AppOrderformBundle/Security/login.html.twig');
//        }

        $inst = trim((string)$request->get('inst') );

        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        //always use Auto-generated Accession Number keytype to generate the new key
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
        $typeEntity = $em->getRepository(AccessionType::class)->findOneByName("Auto-generated Accession Number");
        $keytype = $typeEntity->getId().""; //id of "New York Hospital MRN" in DB

        $extra = array();
        $extra["keytype"] = $keytype;

        //$status, $provider, $className, $fieldName, $parent = null, $fieldValue = null, $extra = null, $withfields = true, $flush=true
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Accession'] by [Accession::class]
        $entity = $em->getRepository(Accession::class)->createElement(
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

    #[Route(path: '/accession/delete/{key}', name: 'delete-accession', methods: ['POST', 'DELETE'])]
    public function deleteAccessionAction(Request $request) {

        $user = $this->getUser();

        $key = trim((string)$request->get('key') );
        $keytype = trim((string)$request->get('extra') );
        //echo "key=".$key.",keytype=".$keytype." | ";

        $inst = trim((string)$request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Accession'] by [Accession::class]
        $keytype = $em->getRepository(Accession::class)->getCorrectKeytypeId($keytype,$user);

        $extra = array();
        $extra["keytype"] = $keytype;

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Accession'] by [Accession::class]
        $res = $em->getRepository(Accession::class)->deleteIfReserved( $institutions, $key,"Accession","accession",$extra );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }


    /************************ PART *************************/
    /**
     * Get Part from DB if existed
     */
    #[Route(path: '/part/check', name: 'get-part', methods: ['GET'])]
    public function getPartAction(Request $request) {

        $key = trim((string)$request->get('key') );
        $accession = trim((string)$request->get('parentkey') ); //need accession number to check if part exists in DB
        $keytype = trim((string)$request->get('parentextra') );
        //echo "key=".$key."   ";

        $inst = trim((string)$request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;

        $validity = array('valid','reserved');

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Part'] by [Part::class]
        $entity = $this->getDoctrine()->getRepository(Part::class)->findOnePartByJoinedToField( $institutions, $accession, $keytype, $key, $validity );

        //echo "count=".count($entity)."<br>";
        //echo "partname=".$entity->getPartname()->first()."<br>";

        $element = array();

        if( $entity && strpos((string)$key,$entity->obtainNoprovidedKeyPrefix().'-') !== false ) {
            $entity = null;
            $element = -2;
        }

        $user = $this->getUser();
        $securityUtil = $this->container->get('user_security_utility');
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
     */
    #[Route(path: '/part/generate', name: 'create-part', methods: ['GET'])]
    public function createPartAction(Request $request) {

        $accession = trim((string)$request->get('parentkey') );
        $keytype = trim((string)$request->get('parentextra') );
        $inst = trim((string)$request->get('inst') );
        //echo "accession=(".$accession.")   ";

        if( $accession && $accession != ""  ) {

            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Part'] by [Part::class]
            $part = $em->getRepository(Part::class)->createPartByAccession($inst,$accession,$keytype,$user);
            //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

            if( $part ) {
                $user = $this->getUser();
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

    #[Route(path: '/part/delete/{key}', name: 'delete-part', methods: ['POST', 'DELETE'])]
    public function deletePartAction(Request $request) {

        $key = trim((string)$request->get('key') );
        $accession = trim((string)$request->get('parentkey') );
        $keytype = trim((string)$request->get('parentextra') );

        $extra = array();
        $extra["accession"] = $accession;
        $extra["keytype"] = $keytype;

        $inst = trim((string)$request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;

        //echo "key=".$key." , accession=".$accession.", keytype=".$keytype."   ";

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Part'] by [Part::class]
        $res = $em->getRepository(Part::class)->deleteIfReserved( $institutions, $key,"Part","partname", $extra );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }


    /************************ BLOCK *************************/
    /**
     * Get BLOCK from DB if existed
     */
    #[Route(path: '/block/check', name: 'get-block', methods: ['GET'])]
    public function getBlockAction(Request $request) {

        $key = trim((string)$request->get('key'));
        $partname = trim((string)$request->get('parentkey')); //need partname to check if part exists in DB
        $accession = trim((string)$request->get('grandparentkey')); //need accession number to check if part exists in DB 
        $keytype = trim((string)$request->get('grandparentextra'));    
        //echo "key=".$key."   ";

        $inst = trim((string)$request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;
        $validity = array('valid','reserved');

        if( $accession != "" && $partname != "" ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Block'] by [Block::class]
            $entity = $this->getDoctrine()->getRepository(Block::class)->findOneBlockByJoinedToField( $institutions, $accession, $keytype, $partname, $key, $validity );

            //echo "count=".count($entity)."<br>";
            //echo "partname=".$entity->getPartname()->first()."<br>";
            
            $element = array();

            if( $entity && strpos((string)$key,$entity->obtainNoprovidedKeyPrefix().'-') !== false ) {
                $entity = null;
                $element = -2;
            }

            $user = $this->getUser();
            $securityUtil = $this->container->get('user_security_utility');
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
     */
    #[Route(path: '/block/generate', name: 'create-block', methods: ['GET'])]
    public function createBlockAction(Request $request) {
        $partname = trim((string)$request->get('parentkey'));
        $accession = trim((string)$request->get('grandparentkey')); //need accession number to check if part exists in DB 
        $keytype = trim((string)$request->get('grandparentextra') );
        $inst = trim((string)$request->get('inst') );
        //echo "accession=(".$accession.")   ";

        if( $accession != "" && $partname != "" ) {

            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Block'] by [Block::class]
            $block = $em->getRepository(Block::class)->createBlockByPartnameAccession($inst,$accession,$keytype,$partname,$user);
            //echo "len=".count($entity->getMrn()).",mrn=".$entity->getMrn()->last()." ";

            $user = $this->getUser();
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

    #[Route(path: '/block/delete/{key}', name: 'delete-block', methods: ['POST', 'DELETE'])]
    public function deleteBlockAction(Request $request) {

        $key = trim((string)$request->get('key'));            
        $partname = trim((string)$request->get('parentkey'));
        $accession = trim((string)$request->get('grandparentkey'));
        $keytype = trim((string)$request->get('grandparentextra') );

        $inst = trim((string)$request->get('inst') );
        $institutions = array();
        $institutions[] = $inst;

        $extra = array();
        $extra["accession"] = $accession;
        $extra["keytype"] = $keytype;
        $extra["partname"] = $partname;

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Block'] by [Block::class]
        $res = $em->getRepository(Block::class)->deleteIfReserved( $institutions, $key,"Block","blockname", $extra );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }


    //get keytype ids by keytype string for handsontable
    #[Route(path: '/accession/keytype/{keytype}', name: 'get-accession-keytypeid', methods: ['GET'])]
    #[Route(path: '/patient/keytype/{keytype}', name: 'get-patient-keytypeid', methods: ['GET'])]
    public function getKeytypeIdAction(Request $request, $keytype) {

        $em = $this->getDoctrine()->getManager();

        if( $request->get('_route') == "get-accession-keytypeid" ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
            $keytypeEntity = $em->getRepository(AccessionType::class)->findOneByName($keytype);
        } else
        if( $request->get('_route') == "get-patient-keytypeid" ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
            $keytypeEntity = $em->getRepository(MrnType::class)->findOneByName($keytype);
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
//     * @Route("/userrole", name="get-user-role", methods={"POST"})
//     */
//    public function getUserRoleAction(Request $request) {
//
//        $external = 'true';
//
//        $user = $this->getUser();
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
