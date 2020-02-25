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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 8/30/2016
 * Time: 12:19 PM
 */

namespace App\CallLogBundle\Controller;


use App\CallLogBundle\Entity\SinglePatient;
use App\CallLogBundle\Form\CalllogListPreviousEntriesFilterType;
use App\CallLogBundle\Form\CalllogPatientType;
use App\CallLogBundle\Form\CalllogSinglePatientType;
use App\OrderformBundle\Entity\Encounter;
use App\OrderformBundle\Entity\Patient;
use App\OrderformBundle\Entity\PatientDob;
use App\OrderformBundle\Entity\PatientFirstName;
use App\OrderformBundle\Entity\PatientLastName;
use App\OrderformBundle\Entity\PatientMiddleName;
use App\OrderformBundle\Entity\PatientMrn;
use App\OrderformBundle\Entity\PatientSex;
use App\OrderformBundle\Entity\PatientSuffix;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\OrderformBundle\Controller\PatientController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


///**
// * CallLog Patient controller.
// *
// * @Route("/patient")
// */
class CallLogPatientController extends PatientController {

    /**
     * Finds and displays a Patient entity.
     *
     * @Route("/patient/info/{id}", name="calllog_patient_show", options={"expose"=true})
     * @Method("GET")
     * @Template("AppOrderformBundle/Patient/new.html.twig")
     */
    public function showAction( Request $request, $id )
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        ini_set('memory_limit', '5120M');

        $showtreedepth = 2;

        $params = array(
            'sitename' => $this->getParameter('calllog.sitename'),
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'editpath' => 'calllog_patient_edit',
            'show-tree-depth' => $showtreedepth
        );

        return $this->showPatient($request,$id,$params);
    }

    /**
     * Displays a form to view an existing Patient entity by mrn.
     * Test 'show-tree-depth': http://localhost/order/call-log-book/patient/view-patient-record?mrn=testmrn-1&mrntype=16&show-tree-depth=2
     *
     * @Route("/patient/view-patient-record", name="calllog_patient_view_by_mrn", options={"expose"=true})
     * @Method("GET")
     * @Template("AppOrderformBundle/Patient/new.html.twig")
     */
    public function viewPatientByMrnAction( Request $request )
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //ini_set('memory_limit', '5120M');
        //ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userSecUtil = $this->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $mrntype = trim($request->get('mrntype'));
        $mrn = trim($request->get('mrn'));
        $showtreedepth = trim($request->get('show-tree-depth'));

        $extra = array();
        $extra["keytype"] = $mrntype;
        $validity = array('valid','reserved');
        $single = false;

        $institution = $userSecUtil->getCurrentUserInstitution($user);
        $institutions = array();
        $institutions[] = $institution->getId();

        $patients = $em->getRepository('AppOrderformBundle:Patient')->findOneByIdJoinedToField($institutions,$mrn,"Patient","mrn",$validity,$single,$extra);

        if( count($patients) > 1 ) {
            $patient = null;
            $patientArr = array();
            foreach( $patients as $thisPatient ) {
                if( $thisPatient->obtainValidKeyfield() ) {
                    //we should return a single result, but we got multiple entity, so return the first valid key one.
                    $patient = $thisPatient;
                }
                $patientArr[] = $patient->obtainPatientInfoSimple();
            }
            if( !$patient ) {
                $patient = $patients[0];
            }
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                'Multiple patients found with mrn ' . $mrn . ". Displayed is the first patient with a valid mrn. Found " . count($patients) . " patients: <hr>" . implode("<hr>",$patientArr)
            );
        }

        if( count($patients) == 1 ) {
            $patient = $patients[0];
        }

        if( !$patient || !$patient->getId() ) {
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                'No patient found with mrn ' . $mrn
            );
            return $this->redirect($this->generateUrl('calllog_home'));
        }


        if( !$showtreedepth ) {
            $showtreedepth = 2;
        }
        //echo "showtreedepth=".$showtreedepth."<br>";

        $params = array(
            'sitename' => $this->getParameter('calllog.sitename'),
            'datastructure' => 'datastructure-patient',
            //'datastructure' => 'datastructure', //images are shown only if the 'datastructure' parameters is set to 'datastructure'
            'tracker' => 'tracker',
            'editpath' => 'calllog_patient_edit',
            'show-tree-depth' => $showtreedepth
        );

        return $this->showPatient($request,$patient->getId(),$params);
    }


    /**
     * Displays a form to edit an existing Patient entity by id.
     *
     * @Route("/patient/{id}/edit", name="calllog_patient_edit", options={"expose"=true})
     * @Method("GET")
     * @Template("AppOrderformBundle/Patient/new.html.twig")
     */
    public function editAction( Request $request, $id )
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $showtreedepth = 2;

        $params = array(
            'sitename' => $this->getParameter('calllog.sitename'),
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'updatepath' => 'calllog_patient_update',
            'showPlus' => 'showPlus',
            'show-tree-depth' => $showtreedepth
        );

        $formResArr = $this->editPatient($request,$id,$params);

        $formResArr['title'] = $formResArr['title'] . " | Call Log Book";

        return $formResArr;
    }

    /**
     * Displays a form to edit an existing Patient entity by mrn.
     *
     * ////Route("/patient/edit-by-mrn/{mrn}/{mrntype}", name="calllog_patient_edit_by_mrn", options={"expose"=true})
     *
     * @Route("/patient/edit-patient-record", name="calllog_patient_edit_by_mrn", options={"expose"=true})
     * @Method("GET")
     * @Template("AppOrderformBundle/Patient/new.html.twig")
     */
    public function editPatientByMrnAction( Request $request )
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '5120M');
        //ini_set('memory_limit', '-1');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userSecUtil = $this->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $mrntype = trim($request->get('mrntype'));
        $mrn = trim($request->get('mrn'));
        $showtreedepth = trim($request->get('show-tree-depth'));

        $extra = array();
        $extra["keytype"] = $mrntype;
        $validity = array('valid','reserved');
        $single = false;

        //$institution = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName("All Institutions");
        //$institution = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName("Weill Cornell Medical College");
        //$institution = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName("New York Presbyterian Hospital");
        $institution = $userSecUtil->getCurrentUserInstitution($user);
        $institutions = array();
        $institutions[] = $institution->getId();

        $patients = $em->getRepository('AppOrderformBundle:Patient')->findOneByIdJoinedToField($institutions,$mrn,"Patient","mrn",$validity,$single,$extra);
        //echo "found patient=".$entity."<br>";
        //exit("edit patient by mrn $mrn $mrntype");
        //$patients = $em->getRepository('AppOrderformBundle:Patient')->findAll(); //testing

        if( count($patients) > 1 ) {
            $patient = null;
            $patientArr = array();
            foreach( $patients as $thisPatient ) {
                if( $thisPatient->obtainValidKeyfield() ) {
                    //we should return a single result, but we got multiple entity, so return the first valid key one.
                    $patient = $thisPatient;
                }
                $patientArr[] = $patient->obtainPatientInfoSimple();
            }
            if( !$patient ) {
                $patient = $patients[0];
            }
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                'Multiple patients found with mrn ' . $mrn . ". Displayed is the first patient with a valid mrn. Found " . count($patients) . " patients: <hr>" . implode("<hr>",$patientArr)
            );
        }

        if( count($patients) == 1 ) {
            $patient = $patients[0];
        }

        if( !$patient || !$patient->getId() ) {
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                'No patient found with mrn ' . $mrn
            );
            return $this->redirect($this->generateUrl('calllog_home'));
        }

//        $this->get('session')->getFlashBag()->add(
//            'pnotify',
//            'Ok!'
//        );

        if( !$showtreedepth ) {
            $showtreedepth = 2;
        }
        //echo "showtreedepth=".$showtreedepth."<br>";

        $params = array(
            'sitename' => $this->getParameter('calllog.sitename'),
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'updatepath' => 'calllog_patient_update',
            'showPlus' => 'showPlus',
            'show-tree-depth' => $showtreedepth
        );

        return $this->editPatient($request,$patient->getId(),$params);
    }

    /**
     * Edits an existing Patient entity.
     *
     * @Route("/patient/{id}/edit", name="calllog_patient_update", options={"expose"=true})
     * @Method("POST")
     * @Template("AppOrderformBundle/Patient/new.html.twig")
     */
    public function updateAction( Request $request, $id )
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $params = array(
            'sitename' => $this->getParameter('calllog.sitename'),
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'updatepath' => 'calllog_patient_update',
            'showpath' => 'calllog_patient_show',
        );

        return $this->updatePatient($request,$id,$params);  //$datastructure,$showpath,$updatepath);
    }

    /**
     * Displays a form to edit patient info only (not encounters)
     *
     * @Route("/patient-demographics/{id}", name="calllog_single_patient_view")
     * @Template("AppCallLogBundle/DataQuality/single-patient-edit.html.twig")
     * @Method("GET")
     */
    public function patientSingleViewAction(Request $request, Patient $patient)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $cycle = "show";

        $singlePatient = new SinglePatient();

        $form = $this->createPatientSingleForm($patient,$singlePatient,$cycle);

        //Encounter list
        $messages = $this->getEncounterInfos($patient);

        return array(
            'patient' => $patient,
            'messages' => $messages,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Patient Demographics",
        );
    }
    /**
     * Displays a form to edit patient info only (not encounters)
     *
     * @Route("/patient-demographics/edit/{id}", name="calllog_single_patient_edit")
     * @Template("AppCallLogBundle/DataQuality/single-patient-edit.html.twig")
     * @Method({"GET", "POST"})
     */
    public function patientSingleEditAction(Request $request, Patient $patient)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->get('user_security_utility');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $cycle = "edit";
        $invalidStatus = 'invalid';

        //$mrnEntity = $patient->obtainValidField('mrn');
        //$mrnNumber = $mrnEntity->getField();
        //$mrnTypeId = $mrnEntity->getKeytype()->getId();

        $singlePatient = new SinglePatient();

        $editForm = $this->createPatientSingleForm($patient,$singlePatient,$cycle);

        $editForm->handleRequest($request);

        if( $editForm->isSubmitted() ) {
            //make sure mrn and mnr type are not empty
            if( !$singlePatient->getKeytype() ) {
                $editForm['keytype']->addError(new FormError("MRN Type can not be empty"));
            }
            if( !$singlePatient->getMrn() ) {
                $editForm['mrn']->addError(new FormError("MRN can not be empty"));
            }
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $newMrntype = $singlePatient->getKeytype();
            $newMrnNumber = $singlePatient->getMrn();
            $newDob = $singlePatient->getDob();
            $newLastname = $singlePatient->getLastname();
            $newFirstname = $singlePatient->getFirstname();
            $newMiddlename = $singlePatient->getMiddlename();
            $newSuffix = $singlePatient->getSuffix();
            $newGender = $singlePatient->getGender();
            $phone = $singlePatient->getPhone();
            $email = $singlePatient->getEmail();

            $mrntypeObject = null;
            $createNewMrn = false;

//            echo "new mrntype=".$newMrntype."<br>";
//            echo "new MrnNumber=".$newMrnNumber."<br>";
//            echo "new gender=".$newGender."<br>";
//            echo "new Lastname=".$newLastname."<br>";
//            echo "new Firstname=".$newFirstname."<br>";
//            echo "new dob=".$newDob->format('Y-m-d')."<br>";
//            exit('1');

            ///////////////// Create new object if a new value exists and is not equal to the original /////////////////////
            if( $newMrntype || $newMrnNumber ) {
                $mrnEntity = $patient->obtainValidField('mrn');
                if( $mrnEntity ) {
                    $mrnNumber = $mrnEntity->getField();
                    if( $mrnEntity->getKeytype() ) {
                        $mrnTypeId = $mrnEntity->getKeytype()->getId();
                    } else {
                        $mrnTypeId = null;
                    }
                } else {
                    $mrnNumber = null;
                    $mrnTypeId = null;
                }
                //$newMrnObject = new PatientMrn('valid',$user,null);
                //check mrn type
                if( $newMrntype ) {
                    if( $newMrntype != $mrnTypeId ) {
                        $mrntypeObject = $em->getRepository('AppOrderformBundle:MrnType')->find($newMrntype);
                        if ($mrntypeObject) {
                            //$newMrnObject->setKeytype($mrntypeObject);
                            $createNewMrn = true;
                        }
                    }
                }
                //check mrn number
                if( $newMrnNumber ) {
                    if( $newMrnNumber != $mrnNumber ) {
                        //$newMrnObject->setField($newMrnNumber);
                        $createNewMrn = true;
                    }
                }

                if( $createNewMrn ) {
                    //echo "create new mrn <br>";
                    $patient->setStatusAllFields($patient->getMrn(), $invalidStatus);
                    $newMrnObject = new PatientMrn('valid',$user,null);
                    if( $mrntypeObject ) {
                        $newMrnObject->setKeytype($mrntypeObject);
                    }
                    if( $newMrnNumber ) {
                        $newMrnObject->setField($newMrnNumber);
                    }
                    $patient->addMrn($newMrnObject);
                }
            }

            if( $newGender ) {
                $sex = $patient->obtainValidField('sex');
                $sexStr = null;
                if( $sex && $sex->getField() ) {
                    //echo "current gender=".$sex->getField()->getId()."<br>";
                    $sexStr = $sex->getField()->getId();
                }
                //echo "Sex: ".$sexStr." ?= ".$newGender."<br>";
                if( $sexStr != $newGender ) {
                    //echo "create new sex <br>";
                    $patient->setStatusAllFields($patient->getSex(), $invalidStatus);
                    $newSexObject = new PatientSex('valid', $user, null);
                    $sexObject = $em->getRepository('AppUserdirectoryBundle:SexList')->find($newGender);
                    $newSexObject->setField($sexObject);
                    $patient->addSex($newSexObject, true);
                }
            }

            if( $newDob ) {
                $dob = $patient->obtainValidField('dob'); //object data
                $dobStr = null;
                if( $dob && $dob->getField() ) {
                    $dobStr = $dob->getField()->format('m/d/Y');
                }
                $newDobStr = $newDob->format('m/d/Y');
                //echo "$dobStr ?= $newDobStr<br>";
                if( $dobStr != $newDobStr ) {
                    //echo "create new dob <br>";
                    $patient->setStatusAllFields($patient->getDob(), $invalidStatus);
                    $newDobObject = new PatientDob('valid', $user, null);
                    $newDobObject->setField($newDob);
                    $patient->addDob($newDobObject);
                }
            }

            if( $newLastname ) {
                $lastname = $patient->obtainValidField('lastname');
                if( $lastname != $newLastname ) {
                    //echo "create new lastname <br>";
                    $patient->setStatusAllFields($patient->getLastname(), $invalidStatus);
                    $newLastnameObject = new PatientLastName('valid', $user, null);
                    $newLastnameObject->setField($newLastname);
                    $patient->addLastname($newLastnameObject, true);
                }
            }

            if( $newFirstname ) {
                $firstname = $patient->obtainValidField('firstname');
                if( $firstname != $newFirstname ) {
                    //echo "create new firstname <br>";
                    $patient->setStatusAllFields($patient->getFirstname(), $invalidStatus);
                    $newFirstnameObject = new PatientFirstName('valid', $user, null);
                    $newFirstnameObject->setField($newFirstname);
                    $patient->addFirstname($newFirstnameObject, true);
                }
            }

            if( $newMiddlename ) {
                $middlename = $patient->obtainValidField('middlename');
                if( $middlename != $newMiddlename ) {
                    //echo "create new middlename <br>";
                    $patient->setStatusAllFields($patient->getMiddlename(), $invalidStatus);
                    $newMiddlenameObject = new PatientMiddleName('valid', $user, null);
                    $newMiddlenameObject->setField($newMiddlename);
                    $patient->addMiddlename($newMiddlenameObject, true);
                }
            }

            if( $newSuffix ) {
                $suffix = $patient->obtainValidField('suffix');
                if( $suffix != $newSuffix ) {
                    //echo "create new suffix <br>";
                    $patient->setStatusAllFields($patient->getSuffix(), $invalidStatus);
                    $newSuffixObject = new PatientSuffix('valid', $user, null);
                    $newSuffixObject->setField($newSuffix);
                    $patient->addSuffix($newSuffixObject, true);
                }
            }

            if( $phone ) {
                $patient->setPhone($phone);
            }
            if( $email ) {
                $patient->setEmail($email);
            }
            ///////////////// EOF Create new object if a new value exists and is not equal to the original /////////////////////

            //exit("Update Patient");

            $em->flush();

            //////////////// TODO: update all associated messages patient info for CSV export ////////////////
            //$formNodeUtil = $this->get('user_formnode_utility');
            $em = $this->getDoctrine()->getManager();

            $repository = $em->getRepository('AppOrderformBundle:Message');

            $dql =  $repository->createQueryBuilder("message");
            $dql->select('message');
            $dql->leftJoin("message.patient","patient");

            $dql->where("patient.id = ".$patient->getId());

            $query = $em->createQuery($dql);

            $messages = $query->getResult();
            //echo "Messages count=".count($messages)."<br>";

            foreach( $messages as $message ) {
                //$res = $formNodeUtil->updateFieldsCache($message);
                //if( !$res) {
                //    exit("Error updating patient cache");
                //}

                //////////// Patient Info //////////////////
                //make sure update this info when patient info is updated via "Edit Patient Demographics"
                $populated = false;
                $patientNames = array();
                $mrns = array();
                foreach ($message->getPatient() as $patient) {
                    $patientNames[] = $patient->getFullPatientName(false);
                    $mrns[] = $patient->obtainFullValidKeyName();
                }
                //Patient Name
                $patientNameStr = implode("\n", $patientNames);
                if( $patientNameStr ) {
                    $message->setPatientNameCache($patientNameStr);
                    $populated = true;
                }
                //MRN
                $mrnsStr = implode("\n", $mrns);
                if( $mrnsStr ) {
                    $message->setPatientMrnCache($mrnsStr);
                    $populated = true;
                }
                //////////// EOF Patient Info //////////////////

                if( $populated ) {
                    $em->flush($message);
                }
            }
            //////////////// EOF update all associated messages patient info for CSV export ////////////////

            ///////// Event Log /////////////////
            $eventType = 'Patient Demographics Updated';
            $event = "Patient with ID " . $patient->getId() . " has been updated by " . $user;
            $changeSetStr = $patient->obtainChangeObjectStr();
            $eventStr = $event . "<br>Changes:<br>".$changeSetStr;

            $userSecUtil->createUserEditEvent($this->getParameter('calllog.sitename'), $eventStr, $user, $patient, $request, $eventType);
            ///////// EOF Event Log /////////////////

            //return $this->redirectToRoute('calllog_patient_view_by_mrn', array('mrn' => $mrnNumber, 'mrntype' => $mrnTypeId, 'show-tree-depth' => 2));
            return $this->redirectToRoute('calllog_single_patient_view',array('id'=>$patient->getId()));
        }

        //Encounter list
        $messages = $this->getEncounterInfos($patient);

        return array(
            'patient' => $patient,
            //'mrnNumber' => $mrnNumber,
            //'mrntype' => $mrnTypeId,
            'messages' => $messages,
            'form' => $editForm->createView(),
            'cycle' => $cycle,
            'title' => "Edit Patient Demographics",
        );
    }
    public function createPatientSingleForm($patient,$singlePatient,$cycle) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        //$calllogUtil = $this->get('calllog_util');

        //echo "Patient=".$patient->getId()."<br>";

        //pre-populate single patient object

        $mrn = $patient->obtainValidField('mrn');
        if( $mrn ) {
            $keytype = $mrn->getKeytype(); //MrnType entity
            if( $keytype ) {
                //echo "keytype=".$keytype->getId().": ".$keytype->getName()."<br>";
                $singlePatient->setKeytype($keytype->getId());
            } else {
                //echo "empty keytype <br>";
                //$singlePatient->setKeytype(NULL);
            }
            $mrnNumber = $mrn->getField();
            //echo "mrnNumber=$mrnNumber<br>";
            if ($mrnNumber) {
                $singlePatient->setMrn($mrnNumber);
            }
        }

        $lastname = $patient->obtainValidField('lastname');
        if( $lastname ) {
            $singlePatient->setLastname($lastname);
        }

        $firstname = $patient->obtainValidField('firstname');
        //echo "firstname=$firstname<br>";
        if( $firstname ) {
            $singlePatient->setFirstname($firstname);
        }

        $middlename = $patient->obtainValidField('middlename');
        if( $middlename ) {
            $singlePatient->setMiddlename($middlename);
        }

        $suffix = $patient->obtainValidField('suffix');
        if( $suffix ) {
            $singlePatient->setSuffix($suffix);
        }

        $dob = $patient->obtainValidField('dob');
        //echo "dob=".$dob->getId()."<br>";
        if( $dob ) {
            $singlePatient->setDob($dob->getField());
        }

        $sex = $patient->obtainValidField('sex');
        if( $sex ) {
            $sexObject = $sex->getField(); //App\UserdirectoryBundle\Entity\SexList
            //echo "sexObject=".$sexObject->getId().": ".$sexObject->getName()."<br>";
            $singlePatient->setGender($sexObject->getId());
        }

        $phone = $patient->getPhone();
        if( $phone ) {
            $singlePatient->setPhone($phone);
        }
        $email = $patient->getEmail();
        if( $email ) {
            $singlePatient->setEmail($email);
        }


        //get mrntypes
        $mrntypeChoices = array();
        $mrntypeChoicesArr = $em->getRepository('AppOrderformBundle:MrnType')->findBy(
            array(
                'type'=>array('default','user-added')
            ),
            array('orderinlist' => 'ASC')
        );
        foreach( $mrntypeChoicesArr as $thisMrnType ) {
            $mrntypeChoices[$thisMrnType->getName()] = $thisMrnType->getId();
        }

        //get genders
        $genderChoices = array();
        $genderChoicesArr = $em->getRepository('AppUserdirectoryBundle:SexList')->findBy(array('type'=>array('default','user-added')));
        foreach( $genderChoicesArr as $thisGender ) {
            $genderChoices[$thisGender->getName()] = $thisGender->getId();
        }

        $params = array(
            'keytypes' => $mrntypeChoices,
            'genders' => $genderChoices,
            'update' => false
        );

        if( $cycle == "show" ) {
            $disabled = true;
        }
        if( $cycle == "edit" ) {
            $disabled = false;
            $params['update'] = true;
        }

        $form = $this->createForm(CalllogSinglePatientType::class, $singlePatient, array(
            'form_custom_value' => $params,
            'disabled' => $disabled
        ));

        return $form;
    }
    public function getEncounterInfos($patient) {
        //Encounter list
        if(0) {
            $encounterInfoArr = array();
            foreach ($patient->getEncounter() as $encounter) {
                $encounterNumber = $encounter->obtainEncounterNumber();
                $encounterInfoArr[$encounterNumber] = $encounter->obtainFullObjectName();
            }
            $encounterInfo = "<b>Encounter(s)</b>:<br>" . implode("<br>", $encounterInfoArr);
            return $encounterInfo;
        }

        //perform search
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppOrderformBundle:Message');
        $dql = $repository->createQueryBuilder('message');
        $dql->leftJoin("message.patient","patient");
        $dql->leftJoin("message.messageStatus","messageStatus");
//        $dql->leftJoin("patient.mrn","mrn");
//        $dql->leftJoin("patient.lastname","lastname");
//        $dql->leftJoin("patient.firstname","firstname");
//        $dql->leftJoin("message.encounter","encounter");
//        $dql->leftJoin("message.calllogEntryMessage","calllogEntryMessage");

        $dql->where("patient.id = ".$patient->getId()." AND messageStatus.name != 'Deleted'");
        $query = $em->createQuery($dql);
        //$query->setMaxResults(10);

        $messages = $query->getResult();
        //echo "messages=".count($messages)."<br>";
        //exit();

        return $messages;
    }


    /**
     * Complex Patient List
     * @Route("/patient-list/{listid}/{listname}", name="calllog_complex_patient_list")
     * @Template("AppCallLogBundle/PatientList/complex-patient-list.html.twig")
     */
    public function complexPatientListAction(Request $request, $listid, $listname)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $securityUtil = $this->get('user_security_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //$listname
        $listnameArr = explode('-',$listname);
        $listname = implode(' ',$listnameArr);
        $listname = ucwords($listname);
        //echo "list: name=$listname; id=$listid <br>";

        //get list name by $listname, convert it to the first char as Upper case and use it to find the list in DB
        //for now use the mock page complex-patient-list.html.twig

        //get list by id
        //$patientList = $em->getRepository('AppOrderformBundle:PatientListHierarchy')->find($listid);
        //$patients = $patientList->getChildren();

        $patientGroup = $em->getRepository('AppOrderformBundle:PatientListHierarchyGroupType')->findOneByName('Patient');

        $parameters = array();

        $repository = $em->getRepository('AppOrderformBundle:PatientListHierarchy');
        $dql = $repository->createQueryBuilder("list");

        $dql->leftJoin("list.patient", "patient");
        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");
        $dql->leftJoin("patient.mrn", "mrn");

        $dql->where("list.parent = :parentId AND list.organizationalGroupType = :patientGroup");
        $parameters['parentId'] = $listid;
        $parameters['patientGroup'] = $patientGroup->getId();

        $dql->andWhere("list.type = 'user-added' OR list.type = 'default'");

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        //echo "sql=".$query->getSql()."<br>";

        $limit = 30;
        $paginator  = $this->get('knp_paginator');
        $patients = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            //$request->query->getInt('page', 1),
            $limit,      /*limit per page*/
            array(
                'defaultSortFieldName' => 'patient.id',
                'defaultSortDirection' => 'DESC',
                'wrap-queries'=>true
            )
        );
        //$patients = $query->getResult();

        //echo "patients=".count($patients)."<br>";

        $patientListHierarchyObject = $em->getRepository('AppUserdirectoryBundle:PlatformListManagerRootList')->findOneByName('Patient List Hierarchy');

        //create patient form for "Add Patient" section
        $status = 'invalid';
        $system = $securityUtil->getDefaultSourceSystem($this->getParameter('calllog.sitename'));
        $newPatient = new Patient(true,$status,$user,$system);
        $newEncounter = new Encounter(true,'dummy',$user,$system);
        $newPatient->addEncounter($newEncounter);
        $patientForm = $this->createPatientForm($newPatient);

        //src/App/CallLogBundle/Resources/views/PatientList/complex-patient-list.html.twig
        return array(
            'patientListId' => $listid,
            'patientNodes' => $patients,
            'title' => $listname,   //"Complex Patient List",
            'platformListManagerRootListId' => $patientListHierarchyObject->getId(),
            'patientForm' => $patientForm->createView(),
            'cycle' => 'new',
            'formtype' => 'add-patient-to-list',
            'mrn' => null,
            'mrntype' => null
        );
    }


    /**
     * Listing patients whose notes have been updated in the last 96 hours (4 days)
     *
     * @Route("/recent-patients", name="calllog_recent_patients")
     * @Template("AppCallLogBundle/PatientList/recent-patients.html.twig")
     */
    public function recentPatientsAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $securityUtil = $this->get('user_security_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //listing patients whose notes have been updated in the last 96 hours

        $parameters = array();

        $repository = $em->getRepository('AppOrderformBundle:Patient');
        $dql = $repository->createQueryBuilder("patient");

        $dql->leftJoin("patient.message", "message");
        $dql->leftJoin("message.editorInfos", "editorInfos");
        $dql->leftJoin("message.calllogEntryMessage", "calllogEntryMessage");
        $dql->leftJoin("calllogEntryMessage.calllogTasks", "calllogTasks");

        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");
        $dql->leftJoin("patient.mrn", "mrn");

        //$dql->where("list.parent = :parentId AND list.organizationalGroupType = :patientGroup");
        //$parameters['parentId'] = $listid;
        //$parameters['patientGroup'] = $patientGroup->getId();

        $dql->where("calllogEntryMessage.id IS NOT NULL");
        //$dql->andWhere("message.orderdate >= :hours96Ago OR editorInfos.modifiedOn >= :hours96Ago OR calllogTasks.statusUpdatedDate >= :hours96Ago");

        $andWhere = "message.orderdate >= :hours96Ago OR editorInfos.modifiedOn >= :hours96Ago OR calllogTasks.statusUpdatedDate >= :hours96Ago";
        //$andWhere = "message.orderdate >= :hours96Ago";
        $dql->andWhere($andWhere);

        $hours96Ago = new \DateTime();
        $hours96Ago->modify('-96 hours');
        //$hours96Ago->modify('-5 hours');
        //$parameters['hours96Ago'] = $hours96Ago->format('Y-m-d');
        $parameters['hours96Ago'] = $hours96Ago;

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        //echo "sql=".$query->getSql()."<br>";

        $limit = 30;
        $paginator  = $this->get('knp_paginator');
        $patients = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            //$request->query->getInt('page', 1),
            $limit,      /*limit per page*/
            array(
                'defaultSortFieldName' => 'patient.id',
                'defaultSortDirection' => 'DESC',
                'wrap-queries'=>true
            )
        );
        //$patients = $query->getResult();

        //echo "patients=".count($patients)."<br>";

        //create patient form for "Add Patient" section
//        $status = 'invalid';
//        $system = $securityUtil->getDefaultSourceSystem($this->getParameter('calllog.sitename'));
//        $newPatient = new Patient(true,$status,$user,$system);
//        $newEncounter = new Encounter(true,'dummy',$user,$system);
//        $newPatient->addEncounter($newEncounter);
//        $patientForm = $this->createPatientForm($newPatient);

        //src/App/CallLogBundle/Resources/views/PatientList/complex-patient-list.html.twig
        return array(
            'patients' => $patients,
            'title' => "Recent Patients (96 hours)",
        );
    }


    /**
     * @Route("/patient/remove-patient-from-list/{patientId}/{patientListId}", name="calllog_remove_patient_from_list")
     */
    public function removePatientFromListAction(Request $request, $patientId, $patientListId) {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $patientList = $em->getRepository('AppOrderformBundle:PatientListHierarchy')->find($patientListId);
        if( !$patientList ) {
            throw new \Exception( "PatientListHierarchy not found by id $patientListId" );
        }

        //remove patient from the list
        $repository = $em->getRepository('AppOrderformBundle:PatientListHierarchy');
        $dql = $repository->createQueryBuilder("list");

        $dql->leftJoin("list.patient", "patient");

        $dql->where("patient = :patientId");
        $parameters['patientId'] = $patientId;

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        $patients = $query->getResult();

        $msgArr = array();
        foreach( $patients as $patientNode ) {
            $patientNode->setType('disabled');
            //TODO: remove this patient from all CalllogEntryMessage (addPatientToList, patientList): find all message with this patient where addPatientToList is true and set to false?
            $msgArr[] = $patientNode->getPatient()->obtainPatientInfoTitle();
        }
        $em->flush();

        $msg = implode('<br>',$msgArr);
        if( $msg ) {
            $msg = "Removed patient:<br>" . $msg;
        }

        $this->get('session')->getFlashBag()->add(
            'pnotify',
            $msg
        );

        $listName = $patientList->getName()."";
        $listNameLowerCase = str_replace(" ","-",$listName);
        $listNameLowerCase = strtolower($listNameLowerCase);

        return $this->redirect($this->generateUrl('calllog_complex_patient_list',array('listname'=>$listNameLowerCase,'listid'=>$patientListId)));
    }



    /**
     * @Route("/patient/add-patient-to-list/{patientListId}/{patientId}", name="calllog_add_patient_to_list")
     * @Route("/patient/add-patient-to-list-ajax/{patientListId}/{patientId}", name="calllog_add_patient_to_list_ajax", options={"expose"=true})
     *
     * @Template("AppCallLogBundle/PatientList/complex-patient-list.html.twig")
     */
    public function addPatientToListAction(Request $request, $patientListId, $patientId) {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $calllogUtil = $this->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

        $patientList = $em->getRepository('AppOrderformBundle:PatientListHierarchy')->find($patientListId);
        if( !$patientList ) {
            throw new \Exception( "PatientListHierarchy not found by id $patientListId" );
        }

        //add patient from the list
        $patient = $em->getRepository('AppOrderformBundle:Patient')->find($patientId);
        if( !$patient ) {
            throw new \Exception( "Patient not found by id $patientId" );
        }

        //exit("before adding patient");
        $newListElement = $calllogUtil->addPatientToPatientList($patient,$patientList);

        if( $newListElement ) {
            //Patient added to the Pathology Call Complex Patients list
            $msg = "Patient " . $newListElement->getPatient()->obtainPatientInfoTitle() . " has been added to the " . $patientList->getName() . " list";
            $pnotify = 'pnotify';
        } else {
            $msg = "Patient " . $patient->obtainPatientInfoTitle() . " HAS NOT BEEN ADDED to the " . $patientList->getName() . " list. Probably, this patient already exists in this list.";
            $pnotify = 'pnotify-error';
        }

        $this->get('session')->getFlashBag()->add(
            $pnotify,
            $msg
        );

        //return OK
        if( $request->get('_route') == "calllog_add_patient_to_list_ajax" ) {
            $res = "OK";
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($res));
            return $response;
        }

        $listName = $patientList->getName()."";
        $listNameLowerCase = str_replace(" ","-",$listName);
        $listNameLowerCase = strtolower($listNameLowerCase);

        return $this->redirect($this->generateUrl('calllog_complex_patient_list',array('listname'=>$listNameLowerCase,'listid'=>$patientListId)));
    }


    //calllog-list-previous-entries
    /**
     * @Route("/patient/list-previous-entries/", name="calllog-list-previous-entries", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function listPatientPreviousEntriesAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $calllogUtil = $this->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

        $title = "Previous Entries";
        $template = null;
        $filterMessageCategory = null;

        $messageId = $request->query->get('messageid');

        $patientid = $request->query->get('patientid');
        //echo "patientid=".$patientid."<br>";

        $patient = $em->getRepository('AppOrderformBundle:Patient')->find($patientid);
        if( !$patient ) {
            throw new \Exception( "Patient not found by id $patientid" );
        }

        //get linked patients
        $mergedPatients = $calllogUtil->getAllMergedPatients( array($patient) );

        //get master patient If the entered patient is linked to another
        if( count($mergedPatients) > 1 ) {
            $masterPatient = $calllogUtil->getMasterRecordPatients($mergedPatients);
            if ($masterPatient) {
                if ($masterPatient->getId() != $patientid) {
                    //not master record
                    //"Previous Entries for FirstNameOfMasterRecord LastNameOfMasterRecord (DOB: DateOfBirthOfMasterRecord, MRNTypeOfMasterRecord: MRNofMasterRecord)
                    $title = "Previous entries for all patients linked with the master patient record of ".$masterPatient->obtainPatientInfoSimple();
                }
            }
        }

        //get patient ids
        $patientIdArr = array();
        foreach( $mergedPatients as $mergedPatient ) {
            $patientIdArr[] = $mergedPatient->getId();
        }
        if( count($patientIdArr) > 0 ) {
            $patientIds = implode(",", $patientIdArr);
        } else {
            throw new \Exception( "Patient array does not have any patients. count=".count($patientIdArr) );
        }

        $messageCategoryId = $request->query->get('type');
        //if ( strval($messageCategoryId) != strval(intval($messageCategoryId)) ) {
            //echo "Your variable is not an integer";
            //$messageCategoryId = null;
        //} else {
            //$filterMessageCategory = $em->getRepository('AppOrderformBundle:MessageCategory')->find($messageCategoryId);
            //echo "filter=".$filterMessageCategory."<br>";
        //}
        if( !$messageCategoryId || $messageCategoryId == "null" || $messageCategoryId == "undefined" ) {
            $messageCategoryId = null;
        }

        //echo "patientid=".$patientid."<br>";
        //echo "messageCategory=".$messageCategory."<br>";

        $testing = $request->query->get('testing');

        //$showUserArr = $this->showUser($userid,$this->getParameter('employees.sitename'),false);
        //$template = $this->render('AppUserdirectoryBundle/Profile/edit_user_only.html.twig',$showUserArr)->getContent();

        //child nodes of "Pathology Call Log Entry"
        //$messageCategoriePathCall = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName("Pathology Call Log Entry");
        $messageCategoriePathCall = $calllogUtil->getDefaultMessageCategory();
        $messageCategories = array();
        if( $messageCategoriePathCall ) {
            //$messageCategories = $messageCategoriePathCall->printTreeSelectList();
            //#51: Show them in the same way as the "Message Type" dropdown menu on the homepage shows its values.
            $messageCategories = $messageCategoriePathCall->printTreeSelectListIncludingThis(true,array("default","user-added"));
        }
        //print_r($messageCategories);

        $filterform = null;
        if(0) {
            $params = array(
                'messageCategory' => $messageCategoryId,
                'messageCategories' => $messageCategories //for previous entries page
            );
            $filterform = $this->createForm(CalllogListPreviousEntriesFilterType::class, null, array(
                'method' => 'GET',
                'form_custom_value' => $params
            ));
            //$filterform->submit($request);
            $filterform->handleRequest($request);

            //$messageCategoryId = $filterform['messageCategory']->getData();
            //echo "messageCategoryId=".$messageCategoryId."<br>";
        }

        //////////////// find messages ////////////////
        //$this->testSelectMessagesWithMaxVersion($patientid);

        $queryParameters = array();
        $repository = $em->getRepository('AppOrderformBundle:Message');
        $dql = $repository->createQueryBuilder('message');
        $dql->select('message');

        //$dql->select('message, MAX(message.version) AS HIDDEN max_version');
        //$dql->groupBy('message.oid');
        //$dql->addGroupBy('message.version');

        $dql->leftJoin("message.messageStatus","messageStatus");
        $dql->leftJoin("message.messageCategory","messageCategory");
        $dql->leftJoin("message.provider","provider");
        $dql->leftJoin("message.patient","patient");
        $dql->leftJoin("message.editorInfos","editorInfos");

        $dql->leftJoin("message.signeeInfo","signeeInfo");
        $dql->leftJoin("signeeInfo.modifiedBy","signee");

        $dql->leftJoin("message.encounter","encounter");
        $dql->leftJoin("encounter.referringProviders","referringProviders");
        $dql->leftJoin("referringProviders.field","referringProviderWrapper");
        $dql->leftJoin("encounter.attendingPhysicians","attendingPhysicians");
        $dql->leftJoin("attendingPhysicians.field","attendingPhysicianWrapper");

        $dql->orderBy("message.orderdate","DESC");
        $dql->addOrderBy("editorInfos.modifiedOn","DESC");

        //$dql->where("patient.id = :patientId");
        //$queryParameters['patientId'] = $patientid;

        $dql->where('patient.id IN (:patientIds)');
        $queryParameters['patientIds'] = $patientIds;

        //$dql->andWhere("(SELECT messages, MAX(messages.version) AS maxversion FROM AppOrderformBundle:Message WHERE messages.id=message.id)");

        //We can use the fact that latest version messages have status not "Deleted"
        $dql->andWhere("messageStatus.name != :deletedMessageStatus");
        $queryParameters['deletedMessageStatus'] = "Deleted";

        if( $messageCategoryId ) {
            $dql->andWhere("messageCategory.name=:messageCategoryId");
            $queryParameters['messageCategoryId'] = $messageCategoryId;
        }

        //TODO: Show only the most recent version for each message (if a message has been edited/amended 5 times, show only the message with message version "6").

        //TODO: 7- If the entered patient is linked to another AND is NOT the master patient record,
        // change the title of the accordion to
        // "Previous Entries for FirstNameOfMasterRecord LastNameOfMasterRecord (DOB: DateOfBirthOfMasterRecord, MRNTypeOfMasterRecord: MRNofMasterRecord).
        // Clicking "Re-enter patient" in the Patient Info accordion should re-set the title of the accordion to "Previous Entries" (remove the patient name/info).

        $query = $em->createQuery($dql);
        $query->setParameters($queryParameters);

        $limit = 10;
        //$query->setMaxResults($limit);

        //echo "query=".$query->getSql()."<br>";

//        $paginator  = $this->get('knp_paginator');
//        $messages = $paginator->paginate(
//            $query,
//            $this->get('request')->query->get('page', 1), /*page number*/
//            //$request->query->getInt('page', 1),
//            $limit      /*limit per page*/
//        );

        $messages = $query->getResult();

        //echo "messages count=".count($messages)."<br>";
        //foreach( $messages as $message ) {
        //    echo "Message=".$message->getMessageOidVersion()."<br>";
        //}
        //exit('testing');
        //////////////// find messages ////////////////
        //do not show section if none previous messages
        if( count($messages) == 0 ) {
            $json = json_encode(null);
            $response = new Response($json);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        //do not show if 1 result with the same message id (if there is only itself as the previous note)
        if( count($messages) == 1 ) {
            $singleMessage = $messages[0];
            if( $messageId && $singleMessage->getId() == $messageId ) {
                $json = json_encode(null);
                $response = new Response($json);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }

        if( count($messages) > $limit ) {
            $mrnRes = $patient->obtainStatusField('mrn', "valid");
            $mrntype = $mrnRes->getKeytype()->getId();
            $mrn = $mrnRes->getField();
            $linkUrl = $this->generateUrl(
                "calllog_home",
                array(
                    'filter[mrntype]'=>$mrntype,
                    'filter[search]'=>$mrn,
                    'filter[messageStatus]'=>"All except deleted",
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $showAllMsg = "showing the last $limit entries, click here to view all";
            $href = '<a href="'.$linkUrl.'" target="_blank">'.$showAllMsg.'</a>';
            $title = $title . " (" . $href . ")";
        }

        $params = array(
            'filterform' =>  ($filterform ? $filterform->createView() : null), //$filterform->createView(),
            'route_path' => $request->get('_route'),
            'messages' => $messages,
            'title' => $title,
            'limit' => $limit,
            'messageid' => $messageId
            //'testing' => true
        );
        $htmlPage = $this->render('AppCallLogBundle/PatientList/patient_entries.html.twig',$params);

        //testing
        if( $testing ) {
            return $htmlPage;
        }

        $template = $htmlPage->getContent();

        $json = json_encode($template);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    //NOT USED
    public function testSelectMessagesWithMaxVersion( $patientid ) {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppOrderformBundle:Message');
        $query = $repository->createQueryBuilder('s');
        $query->select('s, MAX(s.version)');
        $query->leftJoin("s.patient","patient");
        $query->where('patient.id = :patient')->setParameter('patient', $patientid);
        $query->groupBy('s');
        //$query->addGroupBy('s.version');
        //$query->setMaxResults($limit);
        $query->orderBy('s.oid', 'ASC');

        $messagesComplex = $query->getQuery()->getResult();
        //print_r($messagesComplex);
        echo "messagesComplex count=".count($messagesComplex)."<br>";

        $messages = $messagesComplex['s'];
        echo "messages=".$messages."<br>";
        echo "messages count=".count($messages)."<br>";

        foreach( $messages as $message ) {
            echo "Message=".$message->getMessageOidVersion()."<br>";
        }
        exit('testing');
    }
    //NOT USED
    public function testSelectMessagesWithMaxVersion_OLD($patientid) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('
            SELECT message, message.version AS HIDDEN
            FROM AppOrderformBundle:Message message
            INNER JOIN message.patient patient'.
            ' LEFT OUTER JOIN AppOrderformBundle:Message b ON message.id = b.id AND message.version < b.version'.
            ' WHERE patient.id = :patient
            ORDER BY message.oid ASC'
        )->setParameter('patient', $patientid);

        echo "query=".$query->getSql()."<br>";

        $messages = $query->getResult();

        echo "messages count=".count($messages)."<br>";

        foreach( $messages as $message ) {
            echo "Message=".$message->getMessageOidVersion()."<br>";
        }

        exit("testing");
    }

    /**
     * @Route("/patient/list-previous-tasks/", name="calllog-list-previous-tasks", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function listPatientPreviousTasksAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $calllogUtil = $this->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

        $title = "Outstanding/Pending To Do Tasks";
        $template = null;
        $filterMessageCategory = null;

        $messageId = $request->query->get('messageid');

        $patientid = $request->query->get('patientid');
        //echo "patientid=".$patientid."<br>";

        $cycle = $request->query->get('cycle');

        $patient = $em->getRepository('AppOrderformBundle:Patient')->find($patientid);
        if( !$patient ) {
            throw new \Exception( "Patient not found by id $patientid" );
        }

        //get linked patients
        $mergedPatients = $calllogUtil->getAllMergedPatients( array($patient) );

        //get master patient If the entered patient is linked to another
        if( count($mergedPatients) > 1 ) {
            $masterPatient = $calllogUtil->getMasterRecordPatients($mergedPatients);
            if ($masterPatient) {
                if ($masterPatient->getId() != $patientid) {
                    //not master record
                    //"Previous Entries for FirstNameOfMasterRecord LastNameOfMasterRecord (DOB: DateOfBirthOfMasterRecord, MRNTypeOfMasterRecord: MRNofMasterRecord)
                    $title = "Previous entries for all patients linked with the master patient record of ".$masterPatient->obtainPatientInfoSimple();
                }
            }
        }

        //get patient ids
        $patientIdArr = array();
        foreach( $mergedPatients as $mergedPatient ) {
            $patientIdArr[] = $mergedPatient->getId();
        }
        if( count($patientIdArr) > 0 ) {
            $patientIds = implode(",", $patientIdArr);
        } else {
            throw new \Exception( "Patient array does not have any patients. count=".count($patientIdArr) );
        }

        $messageCategoryId = $request->query->get('type');
        //if ( strval($messageCategoryId) != strval(intval($messageCategoryId)) ) {
        //echo "Your variable is not an integer";
        //$messageCategoryId = null;
        //} else {
        //$filterMessageCategory = $em->getRepository('AppOrderformBundle:MessageCategory')->find($messageCategoryId);
        //echo "filter=".$filterMessageCategory."<br>";
        //}
        if( !$messageCategoryId || $messageCategoryId == "null" || $messageCategoryId == "undefined" ) {
            $messageCategoryId = null;
        }

        //echo "patientid=".$patientid."<br>";
        //echo "messageCategory=".$messageCategoryId."<br>";

        $testing = $request->query->get('testing');

        //$showUserArr = $this->showUser($userid,$this->getParameter('employees.sitename'),false);
        //$template = $this->render('AppUserdirectoryBundle/Profile/edit_user_only.html.twig',$showUserArr)->getContent();

        //child nodes of "Pathology Call Log Entry"
        //$messageCategoriePathCall = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName("Pathology Call Log Entry");
        $messageCategoriePathCall = $calllogUtil->getDefaultMessageCategory();
        $messageCategories = array();
        if( $messageCategoriePathCall ) {
            //$messageCategories = $messageCategoriePathCall->printTreeSelectList();
            //#51: Show them in the same way as the "Message Type" dropdown menu on the homepage shows its values.
            $messageCategories = $messageCategoriePathCall->printTreeSelectListIncludingThis(true,array("default","user-added"));
        }
        //print_r($messageCategories);

        $filterform = null;

        //////////////// find previous pending tasks ////////////////

        $queryParameters = array();
        $repository = $em->getRepository('AppOrderformBundle:CalllogTask');
        $dql = $repository->createQueryBuilder('task');
        $dql->select('task');

        //$dql->select('message, MAX(message.version) AS HIDDEN max_version');
        //$dql->groupBy('message.oid');
        //$dql->addGroupBy('message.version');

        $dql->leftJoin("task.calllogEntryMessage","calllogEntryMessage");
        $dql->leftJoin("calllogEntryMessage.message","message");
        $dql->leftJoin("message.patient","patient");
        $dql->leftJoin("message.messageStatus","messageStatus");

        $dql->orderBy("task.createdDate","DESC");

        //echo "patientIds=$patientIds <br>";
        $dql->where('patient.id IN (:patientIds)');
        $queryParameters['patientIds'] = $patientIds;

        //We can use the fact that latest version messages have status not "Deleted"
        $dql->andWhere("task.status IS NULL OR task.status = false");

        //We can use the fact that latest version messages have status not "Deleted"
        $dql->andWhere("messageStatus.name != :deletedMessageStatus");
        $queryParameters['deletedMessageStatus'] = "Deleted";
        //$dql->andWhere("messageStatus.name != :deletedMessageStatus AND messageStatus.name != :draftMessageStatus");
        //$queryParameters['deletedMessageStatus'] = "Deleted";
        //$queryParameters['draftMessageStatus'] = "Draft";

        if( $messageCategoryId ) {
            $dql->andWhere("messageCategory.name=:messageCategoryId");
            $queryParameters['messageCategoryId'] = $messageCategoryId;
        }

        $query = $em->createQuery($dql);
        $query->setParameters($queryParameters);

        //$limit = 10;

        $tasks = $query->getResult();
        //echo "tasks count=".count($tasks)."<br>";
        //exit('testing');

        //////////////// find messages ////////////////
        //do not show section if none previous messages
        if( count($tasks) == 0 ) {
            $json = json_encode(null);
            $response = new Response($json);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        //do not show if 1 result with the same message id (if there is only itself as the previous note)
        if( count($tasks) == 1 ) {
            $singleMessage = $tasks[0];
            if( $messageId && $singleMessage->getId() == $messageId ) {
                $json = json_encode(null);
                $response = new Response($json);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }

//        if( count($tasks) > $limit ) {
//            $mrnRes = $patient->obtainStatusField('mrn', "valid");
//            $mrntype = $mrnRes->getKeytype()->getId();
//            $mrn = $mrnRes->getField();
//            $linkUrl = $this->generateUrl(
//                "calllog_home",
//                array(
//                    'filter[mrntype]'=>$mrntype,
//                    'filter[search]'=>$mrn,
//                    'filter[messageStatus]'=>"All except deleted",
//                ),
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
//            $showAllMsg = "showing outstanding To Do tasks, click here to view all";
//            $href = '<a href="'.$linkUrl.'" target="_blank">'.$showAllMsg.'</a>';
//            $title = $title . " (" . $href . ")";
//        }

            $showAllMsg = "showing ".count($tasks)." outstanding To Do tasks";
            $title = $title . " (" . $showAllMsg . ")";

        $params = array(
            'filterform' =>  null,
            'route_path' => $request->get('_route'),
            'tasks' => $tasks,
            'title' => $title,
            'cycle' => $cycle,
            //'limit' => $limit,
            'messageid' => $messageId
            //'testing' => true
        );
        $htmlPage = $this->render('AppCallLogBundle/PatientList/patient_tasks.html.twig',$params);

        //testing
        if( $testing ) {
            return $htmlPage;
        }
        //exit('testing');

        $template = $htmlPage->getContent();

        $json = json_encode($template);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    public function createPatientForm($patient, $mrntype=null, $mrn=null) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $calllogUtil = $this->get('calllog_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $sitename = $this->getParameter('calllog.sitename');

        if( !$mrntype ) {
            //$mrntype = 1;
            $defaultMrnType = $calllogUtil->getDefaultMrnType();
            $mrntype = $defaultMrnType->getId();
        }

        $userTimeZone = $userSecUtil->getSiteSettingParameter('timezone',$sitename);

        $params = array(
            'cycle' => 'new',
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            //'alias' => true
            'type' => null,
            'mrntype' => intval($mrntype),
            'mrn' => $mrn,
            'formtype' => 'call-entry',
            'complexLocation' => false,
            'alias' => false,
            'timezoneDefault' => $userTimeZone,
        );

        $form = $this->createForm(CalllogPatientType::class, $patient, array(
            'form_custom_value' => $params,
            'form_custom_value_entity' => $patient
        ));

        return $form;
    }


    /**
     * Get previous encounters for a given patient. Use previous entries (listPatientPreviousEntriesAction) html result?
     *
     * @Route("/patient/get-previous-encounters", name="calllog-get-previous-encounters", options={"expose"=true})
     * @Method("GET")
     */
    public function getPreviousEncountersAction(Request $request)
    {
        if (false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_USER")) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $calllogUtil = $this->get('calllog_util');

        $patientId = trim($request->get('patientId'));
        //echo "patientId=$patientId<br>";

        $patient = $em->getRepository('AppOrderformBundle:Patient')->find($patientId);
        if( !$patient ) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode(NULL));
            return $response;
        }

        //$result = array();
        $resultEncounters = $calllogUtil->getPreviousEncounterByPatient($patient);
//        foreach($encounters as $encounter) {
//            //$result[] = array("id"=>$encounter->getId(), "number"=>$encounter->obtainEncounterNumberOnlyAndDate(), "snapshot"=>$snapshot);
//            $result[$encounter->getId()] = $encounter->obtainEncounterNumberOnlyAndDate();
//            //$result[$encounter->getId()] = $encounter->getId();
//        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($resultEncounters));
        return $response;
    }

    /**
     * Get previous encounter info by id
     *
     * @Route("/patient/get-encounter-by-id", name="calllog-get-encounter-by-id", options={"expose"=true})
     * @Method("GET")
     */
    public function getEncounterByIdAction(Request $request)
    {
        if (false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_USER")) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        //$calllogUtil = $this->get('calllog_util');

        $encounterId = trim($request->get('encounterId'));
        //echo "encounterId=$encounterId<br>";

        $encounter = $em->getRepository('AppOrderformBundle:Encounter')->find($encounterId);
        if( !$encounter ) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode(NULL));
            return $response;
        }

        //$result = array();
        //$result['number'] = $encounter->obtainEncounterNumberOnlyAndDate();
        //$result['date'] = $encounter->getCreationdate()->format("m/d/Y H:i:s");

//        foreach($encounter->getTracker()->getSpots() as $spot) {
//            $currentLocation = $spot->getCurrentLocation();
//            $room = $currentLocation->getRoom();
//            //foreach($currentLocation->getRoom() as $room) {
//                echo "room=".$room."<br>";
//            //}
//        }
        //exit('111');

        //get encounter html page and send it to the calllog page
        //////////////////
        $params = array(
            'encounter' => $encounter
            //'filterform' =>  ($filterform ? $filterform->createView() : null), //$filterform->createView(),
            //'route_path' => $request->get('_route'),
            //'messages' => $messages,
            //'title' => $title,
            //'limit' => $limit,
            //'messageid' => $messageId
            //'testing' => true
        );
        $htmlPage = $this->render('AppCallLogBundle/PatientList/encounter_show.html.twig',$params);

        //testing
        //$response = new Response($htmlPage);
        //return $response;

        //testing
        //$testing = true;
        //$testing = false;
        //if( $testing ) {
            //return $htmlPage;
        //}

        //$template = $result; //testing
        $template = $htmlPage->getContent();

        $json = json_encode($template);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
        /////////////////

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($result));
        return $response;
    }

}