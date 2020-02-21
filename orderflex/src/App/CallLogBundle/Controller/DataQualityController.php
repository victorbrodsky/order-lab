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

namespace App\CallLogBundle\Controller;

use App\CallLogBundle\Form\CalllogPatientType;
use App\CallLogBundle\Util\CallLogUtil;
use App\OrderformBundle\Entity\Encounter;
use App\OrderformBundle\Entity\EncounterPatfirstname;
use App\OrderformBundle\Entity\EncounterPatlastname;
use App\OrderformBundle\Entity\EncounterPatmiddlename;
use App\OrderformBundle\Entity\EncounterPatsex;
use App\OrderformBundle\Entity\EncounterPatsuffix;
use App\OrderformBundle\Entity\MrnType;
use App\OrderformBundle\Entity\Patient;
use App\OrderformBundle\Entity\PatientDob;
use App\OrderformBundle\Entity\PatientFirstName;
use App\OrderformBundle\Entity\PatientLastName;
use App\OrderformBundle\Entity\PatientMiddleName;
use App\OrderformBundle\Entity\PatientMrn;
use App\OrderformBundle\Entity\PatientSex;
use App\OrderformBundle\Entity\PatientSuffix;
use App\UserdirectoryBundle\Util\UserSecurityUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DataQualityController extends CallEntryController
{

//    protected $calllogUtil;
//    public function __construct( CallLogUtil $calllogUtil ) {
//        $this->calllogUtil = $calllogUtil;
//        //$this->userServiceUtil = $this->get('user_service_utility');//user_service_utility;
//    }

    /**
     * @Route("/merge-patient-records", name="calllog_merge_patient_records", options={"expose"=true})
     * @Template("AppCallLogBundle/DataQuality/merge-records.html.twig")
     */
    public function mergePatientAction(Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $securityUtil = $this->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();

        $title = "Merge Patient Records";

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $status = 'valid';
        $cycle = 'new';

        $patient1 = new Patient(true,$status,$user,$system);

        $triggerSearch = 0;
        $mrntype = trim($request->get('mrntype'));
        $mrnid = trim($request->get('mrn'));
        if( $mrntype && $mrnid ) {
            $mrnPatient1 = $patient1->obtainStatusField('mrn', $status);
            $mrnPatient1->setKeytype($mrntype);
            $mrnPatient1->setField($mrnid);
            $triggerSearch = 1;
        }
        //echo "triggerSearch=".$triggerSearch."<br>";

        $encounter1 = new Encounter(true,'dummy',$user,$system);
        $patient1->addEncounter($encounter1);
        $form1 = $this->createPatientForm($patient1,$mrntype,$mrnid);


        $patient2 = new Patient(true,$status,$user,$system);
        $encounter2 = new Encounter(true,'dummy',$user,$system);
        $patient2->addEncounter($encounter2);
        $form2 = $this->createPatientForm($patient2); //,$mrntype,$mrnid


        return array(
            //'entity' => $entity,
            'form1' => $form1->createView(),
            'form2' => $form2->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'triggerSearch' => $triggerSearch,
            'mrntype' => $mrntype,
            'mrn' => $mrnid
        );
    }

    /**
     * @Route("/merge-patient-records-ajax", name="calllog_merge_patient_records_ajax", options={"expose"=true})
     */
    public function mergePatientAjaxAction(Request $request, CallLogUtil $calllogUtil)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$securityUtil = $this->get('user_security_utility');
        $calllogUtil = $this->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;
        $em = $this->getDoctrine()->getManager();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $id1 = trim($request->get('id1'));
        $id2 = trim($request->get('id2'));
        $masterMergeRecordId = trim($request->get('masterMergeRecordId'));
        //echo "id1=$id1; id2=$id2 <br>";
        //exit('exit');

        $msg = "";
        //$res = null;
        $merged = false;
        $error = false;
        $patient1 = null;
        $patient2 = null;
        $patientsArr = array();
        $status = 'valid';

        if( $id1 ) {
            $patient1 = $this->getDoctrine()->getRepository('AppOrderformBundle:Patient')->find($id1);
            if( !$patient1 ) {
                $msg .= "Patient 1 not found by id=".$id1;
                $error = true;
            }
            //$res = $patient1->getId();
            $patientsArr[] = $patient1;
        } else {
            $msg .= "Patient 1 id is invalid";
        }

        if( $id2 ) {
            $patient2 = $this->getDoctrine()->getRepository('AppOrderformBundle:Patient')->find($id2);
            if( !$patient2 ) {
                $msg .= "Patient 2 not found by id=".$id2;
                $error = true;
            }
            //$res = $patient2->getId();
            $patientsArr[] = $patient2;
        } else {
            $msg .= "Patient 2 id is invalid";
        }

        //testing
//        foreach( $patientsArr as $patient ) {
//            foreach( $patient->getMrn() as $mrn ) {
//                $msg .= $patient->getId().": init MRNID=".$mrn->getID()." mrn=".$mrn->obtainOptimalName()."; status=".$mrn->getStatus()."<br>";
//            }
//        }


        if( !$error && $patient1 && $patient2 ) {
            $mergedMrn1 = $patient1->obtainMergeMrn($status);
            $mergedMrn2 = $patient2->obtainMergeMrn($status);

            //a) If neither of the patients has an MRN of type="Merge ID"
            //Add the generated MRN to both patients with an MRN Type of "Merge ID"
            //MergeID: auto-generate unique, but prepend a prefix "MERGE" (ID MERGE123456)
            if( !$mergedMrn1 && !$mergedMrn2 ) {
                //$msg .= 'Case (a): neither of the patients has an MRN of type="Merge ID"<br>';

                $merged = true;
                $autoGeneratedMergeMrn = $calllogUtil->autoGenerateMergeMrn($patient1);

                $patRes = $calllogUtil->addGenerateMergeMrnToPatient($patient1,$autoGeneratedMergeMrn,$user);
                if( !($patRes instanceof Patient) ) {
                    $msg .= $patRes."<br>";
                    $error = true;
                }

                $patRes = $calllogUtil->addGenerateMergeMrnToPatient($patient2,$autoGeneratedMergeMrn,$user);
                if( !($patRes instanceof Patient) ) {
                    $msg .= $patRes."<br>";
                    $error = true;
                }
            }

            //b) If one of the patients has one MRN of type = "Merge ID",
            // copy that MRN with the type of "Merge ID" to the second patient as a new MRN.
            //(c) If one of the patients has more than one (two, three, etc) MRNs of type= "Merge ID",
            // copy the MRN with the oldest timestamp of the ones available with the type of "Merge ID" to the second patient as a new MRN
            if( (!$mergedMrn1 && $mergedMrn2) || ($mergedMrn1 && !$mergedMrn2) ) {
                //$msg .= 'Case (b,c): one of the patients has one MRN of type = "Merge ID".<br>';

                if( $mergedMrn1 ) {
                    $msg .= " Patient with ID ".$id1." has Merged MRN. "."<br>";

                    $newMrn = $calllogUtil->createPatientMergeMrn($user,$patient2,$mergedMrn1->getField());
                    if( $newMrn instanceof PatientMrn ) {
                        $merged = true;
                        //$newMrn->setField($mergedMrn1->getField());
                        //$patient2->addMrn($newMrn);
                    } else {
                        $msg .= $newMrn."<br>";
                    }
                }

                if( $mergedMrn2 ) {
                    $msg .= " Patient with ID ".$id2." has Merged MRN. "."<br>";

                    $newMrn = $calllogUtil->createPatientMergeMrn($user,$patient1,$mergedMrn2->getField());
                    if( $newMrn instanceof PatientMrn ) {
                        $merged = true;
                        //$newMrn->setField($mergedMrn2->getField());
                        //$patient1->addMrn($newMrn);
                    } else {
                        $msg .= $newMrn."<br>";
                    }
                }

            }

            //If both patients have at least one MRN of type = "Merge ID"
            if( $mergedMrn1 && $mergedMrn2 ) {
                //$msg .= 'Case (d,e,f): If both patients have at least one MRN of type = "Merge ID". ';

                //(d) If both patients have (only) one MRN of type = "Merge ID" each and they are equal to each other
                if( !$error && !$merged ) {
                    if ($patient1->hasOnlyOneMergeMrn($status) && $patient2->hasOnlyOneMergeMrn($status)) {
                        if ($mergedMrn1->getField() == $mergedMrn2->getField()) {
                            //"Patient Records have already been merged by FirstNameOfAuthorOfMRN LastNameofAuthorOfMRN on
                            // DateOfMergeIDAdditionToPatientOne / DateOfMergeIDAdditionToPatientTwo via Merge ID [MergeID-MRN]
                            $msg .= "Patient Records have already been merged by " . $calllogUtil->obtainSameMergeMrnInfoStr($mergedMrn1, $mergedMrn2)."<br>";
                            $error = true;
                        } else {
                            //If not equal, copy the MRN with the oldest (earliest) timestamp of the ones available from one
                            // patient with the type of "Merge ID" to the second patient as a new MRN
                            $newMrn = $calllogUtil->copyOldestMrnToSecondPatient($user, $patient1, $mergedMrn1, $patient2, $mergedMrn2);
                            if ($newMrn instanceof PatientMrn) {
                                $merged = true;
                            } else {
                                $msg .= $newMrn."<br>";
                            }
                        }
                    }//(d)
                }

                //(e) (one has 1 and the other 3), the Merge ID of one is equal to any of the Merge IDs of another
                //(f) (one has 4 and the other 3), any of the Merge IDs of one is equal to any of the Merge IDs of another
                //(e,f): check if MRNs have overlapped (the same) MRN ID.
                if( !$error && !$merged ) {
                    if( $calllogUtil->hasSameID($patient1, $patient2) ) {
                        $msg .= "Patient Records have already been merged by " . $calllogUtil->obtainSameMergeMrnInfoStr($mergedMrn1, $mergedMrn2)."<br>";
                        $error = true;
                    } else {
                        //If not equal, copy the MRN with the oldest timestamp of the ones available from
                        // one patient with the type of "Merge ID" to the second patient as a new MRN
                        $newMrn = $calllogUtil->copyOldestMrnToSecondPatient($user, $patient1, $mergedMrn1, $patient2, $mergedMrn2);
                        if( $newMrn instanceof PatientMrn ) {
                            $merged = true;
                        } else {
                            $msg .= $newMrn."<br>";
                        }
                    }
                }//(e,f)

            }

            if( !$error && $merged ) {

                //merge: set master patient
                $ids = $calllogUtil->setMasterPatientRecord($patientsArr,$masterMergeRecordId,$user);

                $em->flush();

                //testing
                $patientInfoArr = array();
                foreach( $ids as $patientId ) {
                    $thisPatient = $this->getDoctrine()->getRepository('AppOrderformBundle:Patient')->find($patientId);
                    //foreach( $patient->getMrn() as $mrn ) {
                        //$msg .= $patient->getId().": after MRNID=".$mrn->getID()." mrn=".$mrn->obtainOptimalName()."; status=".$mrn->getStatus()."<br>";
                    //}
                    $thisPatientInfo = $thisPatient->getFullPatientName() . "[ID# " . $patientId . "]";
                    if( $masterMergeRecordId == $patientId ) {
                        $thisPatientInfo = $thisPatientInfo . " (Master Patient)";
                    }
                    $patientInfoArr[$patientId] = $thisPatientInfo;
                }

                //"You have successfully merged patient records: Master Patient ID #."
                $msg .= "You have successfully merged patient records:<br>".implode("<br>",$patientInfoArr);
            }

            if( !$error && !$merged ) {
                $msg .= "No merged cases found."."<br>";
            }

            //$result['res'] = 'OK';
        }


        //get master record
        $masterMergeRecordPatient = null;
        if( $masterMergeRecordId == $id1 ) {
            $masterMergeRecordPatient = $patient1;
        }
        if( $masterMergeRecordId == $id2 ) {
            $masterMergeRecordPatient = $patient2;
        }
        //event log
        $userSecUtil = $this->container->get('user_security_utility');
        $eventType = "Merged Patient";
        $event = "Merged patients with ID#" . $id1 . " and ID# " . $id2 .":"."<br>";
        $event = $event . $msg;
        $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $event, $user, $masterMergeRecordPatient, $request, $eventType);


        $result = array();
        $result['error'] = $error;
        $result['msg'] = $msg;

        $response->setContent(json_encode($result));
        return $response;
    }






    /**
     * @Route("/un-merge-patient-records", name="calllog_unmerge_patient_records", options={"expose"=true})
     * @Route("/set-master-patient-record", name="calllog_set_master_patient_record", options={"expose"=true})
     *
     * @Template("AppCallLogBundle/DataQuality/un-merge-records.html.twig")
     */
    public function unmergePatientAction(Request $request)
    //public function unmergePatientAction(Request $request, UserSecurityUtil $securityUtil)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $securityUtil = $this->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $status = 'valid';
        $cycle = 'new';

        $route = $request->get('_route');

        if( $route == "calllog_unmerge_patient_records" ) {
            $title = "Un-merge Patient Records";
            $formtype = 'unmerge';
        } else {
            $title = "Set Master Patient Record";
            $formtype = 'set-master-record';
        }

        $patient1 = new Patient(true,$status,$user,$system);

        $triggerSearch = 0;
        $mrntype = trim($request->get('mrntype'));
        $mrnid = trim($request->get('mrn'));
        if( $mrntype && $mrnid ) {
            $mrnPatient1 = $patient1->obtainStatusField('mrn', $status);
            $mrnPatient1->setKeytype($mrntype);
            $mrnPatient1->setField($mrnid);
            $triggerSearch = 1;
        }

        $encounter1 = new Encounter(true,'dummy',$user,$system);
        $patient1->addEncounter($encounter1);
        $form1 = $this->createPatientForm($patient1,$mrntype,$mrnid);

//        $patient2 = new Patient(true,$status,$user,$system);
//        $encounter2 = new Encounter(true,$status,$user,$system);
//        $patient2->addEncounter($encounter2);
//        $form2 = $this->createPatientForm($patient2);

        return array(
            //'entity' => $entity,
            'form1' => $form1->createView(),
            //'form2' => $form2->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'formtype' => $formtype,
            'triggerSearch' => $triggerSearch,
            'mrntype' => $mrntype,
            'mrn' => $mrnid
        );
    }



    /**
     * @Route("/set-master-patient-record-ajax", name="calllog_set_master_patient_record_ajax", options={"expose"=true})
     */
    public function setMasterPatientAjaxAction(Request $request, CallLogUtil $calllogUtil)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$securityUtil = $this->get('user_security_utility');
        //$calllogUtil = $this->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;
        $em = $this->getDoctrine()->getManager();

        //$system = $securityUtil->getDefaultSourceSystem(); //'scanorder';
        //$status = 'valid';
        //$cycle = 'new';

        $error = false;
        $msg = "";

        $patientId = trim($request->get('masterId'));
        //echo "patientId=".$patientId."<br>";

        //set master patient
        if( $patientId ) {
            $patientObject = $this->getDoctrine()->getRepository('AppOrderformBundle:Patient')->find($patientId);
            $patients = $calllogUtil->getAllMergedPatients(array($patientObject));
            $ids = $calllogUtil->setMasterPatientRecord($patients, $patientId, $user);
            $em->flush();
            $msg .= "Patient with ID $patientId has been set as a Master Record Patient; Patients affected ids=".implode(", ",$ids);
        } else {
            $error = true;
            $msg .= "Patient ID is not provided; patientId=".$patientId;
        }

        $result = array();
        $result['error'] = $error;
        $result['msg'] = $msg;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($result));
        return $response;
    }

    /**
     * @Route("/unmerge-patient-records-ajax", name="calllog_unmerge_patient_records_ajax", options={"expose"=true})
     */
    public function unmergePatientAjaxAction(Request $request, CallLogUtil $calllogUtil)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$securityUtil = $this->get('user_security_utility');
        //$calllogUtil = $this->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;
        $em = $this->getDoctrine()->getManager();

        //$system = $securityUtil->getDefaultSourceSystem(); //'scanorder';
        //$status = 'valid';
        //$cycle = 'new';

        $error = false;
        $msg = "";

        $masterId = trim($request->get('masterId'));
        $patientIds = trim($request->get('patientIds'));
        //echo "masterId=".$masterId."<br>";
        //echo "patientIds=".$patientIds."<br>";
        //exit('1');

        $patientIdsArr = explode(",",$patientIds);

        $unmergedPatients = array();

        //1) get all patients as $unmergedPatients array
        foreach( $patientIdsArr as $patientId ) {

            //$patientIdStrArr = explode("-mergeid-",$patientIdStr);
            //$patientId = $patientIdStrArr[0];
            //$patientMergeId = $patientIdStrArr[1];
            //echo "patientId=".$patientId."<br>";
            //continue;

            //find patient object
            $patient = $this->getDoctrine()->getRepository('AppOrderformBundle:Patient')->find($patientId);
            if( !$patient ) {
                $error = true;
                $msg .= ' Patient not found by ID# '.$patientId.'<br>';
                break;
            }

            $unmergedPatients[] = $patient;

        }//foreach

        //2) check and change (if required) the masterRecord
        $processMasterRes = $calllogUtil->processMasterRecordPatients($unmergedPatients,$masterId,$user);
        if( $processMasterRes['error'] ) {
            $error = true;
        }
        $msg .= $processMasterRes['msg']."<br>";

        //3) process each un-merged patient
        foreach( $unmergedPatients as $unmergedPatient ) {
            // A) if only one merged patient exists with this mergeId (except this patient) => orphan
            // B) if multiple patients found (except this patient) => copy all merged IDs to the master patient in the chain
            $processUnmergePatientRes = $calllogUtil->processUnmergedPatient($unmergedPatient,$masterId,$user);
            if( $processUnmergePatientRes['error'] ) {
                $error = true;
            } else {
                $em->persist($unmergedPatient);
                //$em->persist($mergeMrn);
                $em->flush(); //testing
            }
            $msg .= "Successfully Unmerged Patient ID# ".$unmergedPatient->getId()." ".$unmergedPatient->getFullPatientName() . ";<br>" .
                $processUnmergePatientRes['msg'];
        }

        if( count($unmergedPatients) > 0 ) {
            $userSecUtil = $this->container->get('user_security_utility');
            $eventType = "Un-Merged Patient";
            $event = "Un-Merged " . count($unmergedPatients) . " Patient(s) with a master patient " . $masterId.":";
            $event = $event . $msg;
            $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $event, $user, $unmergedPatients, $request, $eventType);
        }

        $result = array();
        $result['error'] = $error;
        $result['msg'] = $msg;
        //exit('exit:'.$msg);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($result));
        return $response;
    }

    /**
     * TODO: test if the found patient correctly shown: New Entry -> found multiple patient -> Edit patient record menu action => mrn and mrntype are not set correctly
     * TODO: http://localhost/order/call-log-book/find-and-edit-patient-record?mrntype=13&mrn=NOMRNPROVIDED-0000000000010
     * Form to find patient and select. When patient is found and select clicked, patient/{id}/edit page is opened with patient edit form.
     * This form also used in new entry page, when "Edit patient record" action menu, for a specific patient, is clicked.
     *
     * @Route("/find-and-edit-patient-record", name="calllog_find_and_edit_patient_record", options={"expose"=true})
     * @Template("AppCallLogBundle/DataQuality/edit-patient-record.html.twig")
     */
    public function findAndEditPatientAction(Request $request) {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $securityUtil = $this->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $status = 'valid';
        $cycle = 'new';

        $title = "Edit Patient Info";
        $formtype = 'edit-patient';

        $patient1 = new Patient(true,$status,$user,$system);

        $triggerSearch = 0;
        $mrntype = trim($request->get('mrntype'));
        $mrnid = trim($request->get('mrn'));
        if( $mrntype && $mrnid ) {
            $mrnPatient1 = $patient1->obtainStatusField('mrn', $status);
            $mrnPatient1->setKeytype($mrntype);
            $mrnPatient1->setField($mrnid);
            $triggerSearch = 1;

            //redirect to calllog_patient_edit_by_mrn
            return $this->redirect( $this->generateUrl('calllog_patient_edit_by_mrn',array('mrntype'=>$mrntype,'mrn'=>$mrnid,'show-tree-depth'=>2)) );

        }
        //echo "mrn=".$mrntype.";".$mrnid."<br>";

        $encounter1 = new Encounter(true,'dummy',$user,$system);
        $patient1->addEncounter($encounter1);
        $form1 = $this->createPatientForm($patient1,$mrntype,$mrnid); //edit-patient-record

        return array(
            'form1' => $form1->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'formtype' => $formtype,
            'triggerSearch' => $triggerSearch,
            'mrntype' => $mrntype,
            'mrn' => $mrnid
        );
    }

    /**
     * TODO: Under construction. Not Used
     * @Route("/edit-patient-record-ajax", name="calllog_edit_patient_record_ajax", options={"expose"=true})
     */
    public function editPatientAjaxAction(Request $request)
    {

        $result = array();
        $result['error'] = false;
        $result['msg'] = "";


        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $patientId = trim($request->get('patientId'));
        $mrn = trim($request->get('mrn'));
        $mrntype = trim($request->get('mrntype'));
        $dob = trim($request->get('dob'));
        $lastname = trim($request->get('lastname'));
        $firstname = trim($request->get('firstname'));
        $middlename = trim($request->get('middlename'));
        $suffix = trim($request->get('suffix'));
        $sex = trim($request->get('sex'));
        $phone = trim($request->get('phone'));
        $email = trim($request->get('email'));
        //print_r($allgets);
        echo "patientId=".$patientId."; mrn=".$mrn."<br>";


        $result['error'] = true;
        $result['msg'] = "Under construction.";

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($result));
        return $response;
    }

    /**
     * @Route("/merge-patient-records-todel", name="calllog_merge_patient_records_todel", options={"expose"=true})
     * @Template("AppCallLogBundle/DataQuality/merge-records.html.twig")
     */
    public function addNewPatientToListAction_TODEL(Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $securityUtil = $this->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $title = "Merge Patient Records";

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $status = 'valid';
        $cycle = 'new';

        $patient1 = new Patient(true,$status,$user,$system);

        $triggerSearch = 0;
        $mrntype = trim($request->get('mrntype'));
        $mrnid = trim($request->get('mrn'));
        if( $mrntype && $mrnid ) {
            $mrnPatient1 = $patient1->obtainStatusField('mrn', $status);
            $mrnPatient1->setKeytype($mrntype);
            $mrnPatient1->setField($mrnid);
            $triggerSearch = 1;
        }
        //echo "triggerSearch=".$triggerSearch."<br>";

        $encounter1 = new Encounter(true,'dummy',$user,$system);
        $patient1->addEncounter($encounter1);
        $form1 = $this->createPatientForm($patient1,$mrntype,$mrnid);


        $patient2 = new Patient(true,$status,$user,$system);
        $encounter2 = new Encounter(true,'dummy',$user,$system);
        $patient2->addEncounter($encounter2);
        $form2 = $this->createPatientForm($patient2,$mrntype,$mrnid);


        return array(
            //'entity' => $entity,
            'form1' => $form1->createView(),
            'form2' => $form2->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'triggerSearch' => $triggerSearch,
            'mrntype' => $mrntype,
            'mrn' => $mrnid
        );
    }


    public function createPatientForm($patient, $mrntype=null, $mrn=null) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $calllogUtil = $this->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;
        $userSecUtil = $this->container->get('user_security_utility');
        $sitename = $this->container->getParameter('calllog.sitename');

        ////////////////////////
//        $query = $em->createQueryBuilder()
//            ->from('AppOrderformBundle:MrnType', 'list')
//            ->select("list.id as id, list.name as text")
//            ->orderBy("list.orderinlist","ASC");
//        $query->where("list.type = :type OR ( list.type = 'user-added' AND list.name != :autogen)");
//        $query->setParameters( array('type' => 'default','autogen' => 'Auto-generated MRN') );
//        //echo "query=".$query."<br>";
//
//        $mrntypes = $query->getQuery()->getResult();
//        foreach( $mrntypes as $mrntype ) {
//            echo "mrntype=".$mrntype['id'].":".$mrntype['text']."<br>";
//        }
        ///////////////////////

        if( !$mrntype ) {
            //$mrntype = 1;
            $defaultMrnType = $calllogUtil->getDefaultMrnType();
            if( $defaultMrnType ) {
                $mrntype = $defaultMrnType->getId();
                $mrntype = intval($mrntype);
            }
        }

        $userTimeZone = $userSecUtil->getSiteSettingParameter('timezone',$sitename);

        $params = array(
            'cycle' => 'new',
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            //'alias' => true
            'type' => null,
            'mrntype' => $mrntype,
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
     * @Route("/calllog_update_task/{taskId}/{status}", name="calllog_update_task", options={"expose"=true})
     */
    public function updateTaskAction(Request $request, $taskId, $status)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$calllogUtil = $this->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

        $error = false;
        $msg = "";

        //$status = null;//testing
        //exit("status=".$status);

        if( $taskId && $status ) {
            $task = $this->getDoctrine()->getRepository('AppOrderformBundle:CalllogTask')->find($taskId);
            //echo "$task=".$task."<br>";

            //Convert status to boolean
            if( $status == "completed" ) {
                $statusBoolean = true;
            }
            elseif( $status == "pending" ) {
                $statusBoolean = false;
            } else {
                //error
                $result = array();
                $result['error'] = $error;
                $result['msg'] = "Status is invalid: '".$status."'";

                $response = new Response();
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent(json_encode($result));
                return $response;
            }

            $task->setStatus($statusBoolean);
            $task->setStatusUpdatedBy($user);
            $task->setStatusUpdatedDate(new \DateTime());

            $em->flush($task);

            $patient = NULL;
            $patientStr = NULL;
            $calllogEntryMessage = $task->getCalllogEntryMessage();
            $message = $calllogEntryMessage->getMessage();
            $patients = $message->getPatient();
            if( count($patients) > 0 ) {
                $patient = $message->getPatient()->first();
            }

            if( $patient ) {
                $patientStr = " and patient " . $patient->getFullPatientName();
            }

            $msg .= "Task associated with Call Log entry ID#".$message->getOid().$patientStr." has been updated: status is set to '".$status."'".":";

            $msg = $msg . "<br>" .  $task->getTaskFullInfo();

            //EventLog
            $userSecUtil = $this->container->get('user_security_utility');
            $eventType = "Task Updated";
            $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $msg, $user, $message, $request, $eventType);

        } else {
            $error = true;
            $msg .= "Task has not been updated: task id or status are not provided.";
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        $result = array();
        $result['error'] = $error;
        $result['msg'] = $msg;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($result));
        return $response;
    }


}
