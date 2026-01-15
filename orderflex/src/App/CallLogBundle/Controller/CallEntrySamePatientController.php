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



use App\OrderformBundle\Entity\MrnType; //process.py script: replaced namespace by ::class: added use line for classname=MrnType


use App\OrderformBundle\Entity\EncounterStatusList; //process.py script: replaced namespace by ::class: added use line for classname=EncounterStatusList


use App\OrderformBundle\Entity\EncounterInfoTypeList; //process.py script: replaced namespace by ::class: added use line for classname=EncounterInfoTypeList


use App\UserdirectoryBundle\Entity\Document; //process.py script: replaced namespace by ::class: added use line for classname=Document


use App\OrderformBundle\Entity\MessageStatusList; //process.py script: replaced namespace by ::class: added use line for classname=MessageStatusList
use Doctrine\Common\Collections\ArrayCollection;
use App\CallLogBundle\Form\CalllogFilterType;
use App\CallLogBundle\Form\CalllogMessageType;
use App\CallLogBundle\Form\CalllogNavbarFilterType;
use App\OrderformBundle\Entity\CalllogEntryMessage;
use App\OrderformBundle\Entity\Encounter;
use App\OrderformBundle\Entity\EncounterAttendingPhysician;
use App\OrderformBundle\Entity\EncounterPatfirstname;
use App\OrderformBundle\Entity\EncounterPatlastname;
use App\OrderformBundle\Entity\EncounterPatmiddlename;
use App\OrderformBundle\Entity\EncounterPatsex;
use App\OrderformBundle\Entity\EncounterPatsuffix;
use App\OrderformBundle\Entity\EncounterReferringProvider;
use App\OrderformBundle\Entity\Endpoint;
use App\OrderformBundle\Entity\Message;
use App\OrderformBundle\Entity\Patient;
use App\OrderformBundle\Entity\PatientDob;
use App\OrderformBundle\Entity\PatientFirstName;
use App\OrderformBundle\Entity\PatientLastName;
use App\OrderformBundle\Entity\PatientMiddleName;
use App\OrderformBundle\Entity\PatientMrn;
use App\OrderformBundle\Entity\PatientSex;
use App\OrderformBundle\Entity\PatientSuffix;
use App\OrderformBundle\Helper\ErrorHelper;
use App\UserdirectoryBundle\Entity\ModifierInfo;
use App\UserdirectoryBundle\Entity\Spot;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CallEntrySamePatientController extends CallEntryController
{

    /**
     * Call Entry New Page Same Patient
     */
    #[Route(path: '/entry/same-patient/new', name: 'calllog_callentry_same_patient')]
    #[Template('AppCallLogBundle/CallLog/call-entry-same-patient.html.twig')]
    public function callEntrySamePatientAction(Request $request)
    {
        if( false == $this->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //1) search box: MRN,Name...

        $user = $this->getUser();
        $securityUtil = $this->container->get('user_security_utility');
        $calllogUtil = $this->container->get('calllog_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $orderUtil = $this->container->get('scanorder_utility');
        $em = $this->getDoctrine()->getManager();

        $mrn = trim((string)$request->get('mrn'));
        $mrntype = trim((string)$request->get('mrntype'));
        $encounterNumber = trim((string)$request->get('encounter-number'));
        $encounterTypeId = trim((string)$request->get('encounter-type'));
        //$encounterVersion = trim((string)$request->get('encounter-version'));
        $messageTypeId = trim((string)$request->get('message-type'));
        //echo "mrntype=".$mrntype."<br>";

        //check if user has at least one institution
        //$securityUtil = $this->container->get('user_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('calllog_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('calllog_home') );
        }

        $title = "New Entry";

        $system = $securityUtil->getDefaultSourceSystem($this->getParameter('calllog.sitename'));
        $cycle = 'new';
        $formtype = 'call-entry';
        $readonlyPatient = false;
        $readonlyEncounter = false; //Same Encounter
        $patient = null;

        $institution = $userSecUtil->getCurrentUserInstitution($user);

        if( $mrntype && $mrn ) {
            $extra = array();
            $extra["keytype"] = $mrntype;

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
            $patient = $em->getRepository(Patient::class)->createElement(
                $institution,
                'valid',            //status
                $user,              //provider
                "Patient",          //$className
                "mrn",              //$fieldName
                null,               //$parent
                $mrn,        //$fieldValue
                $extra,             //$extra
                false               //$withfields
            );
            $patientTitle = $patient->obtainPatientInfoTitle('valid',null,false);
            $titleheadroom = $patientTitle;

            if( $messageTypeId ) {
                $title = "Add Entry (New Encounter, Same Type) to " . $patientTitle;
            } else {
                $title = "Add Entry (New Encounter) to " . $patientTitle;
            }

            $readonlyPatient = true;
        }

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
        $encounter2 = $em->getRepository(Encounter::class)->findOneEncounterByNumberAndType($encounterTypeId,$encounterNumber);
        //echo "Found encounter=".$encounter2->getId()."; version=".$encounter2->getVersion()."<br>";
        //exit();

        //check whether patient MRN supplied in the URL corresponds to the supplied encounter number.
        // If it does not, show the normal /entry/new page but with the notification "
        // Encounter "1111" of type "blah" is not with patient whose MRN of type "whatever" is "1111"
        if( $mrn && $mrntype && $encounter2 ) {

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
            if( !$em->getRepository(Encounter::class)->isPatientEncounterMatch($mrn,$mrntype,$encounter2) ) {

                $mrntypeStr = "";
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
                $mrntypeEntity = $em->getRepository(MrnType::class)->find($mrntype);
                if( $mrntypeEntity ) {
                    $mrntypeStr = $mrntypeEntity->getName()."";
                }

                $encounterMsg = "Encounter $encounterNumber of type ".$encounter2->obtainEncounterNumber()." is not with patient whose MRN of type $mrntypeStr is $mrn";
                $this->addFlash(
                    'warning',
                    $encounterMsg
                );

                $encounter2 = null;
            } else {
                if( $messageTypeId ) {
                    $title = "Add Entry (Same Encounter & Type) to " . $patientTitle;
                } else {
                    $title = "Add Entry (Same Encounter) to " . $patientTitle;
                }

                $readonlyEncounter = true;
            }
        }

        if( !$encounter2 ) {
            //echo "Create new encounter <br>";
            //create encounter #2 to display in "Encounter Info" -> "Update Patient Info"
            $encounter2 = new Encounter(true, 'valid', $user, $system);
            $encounter2->setVersion(1);
            $encounter2->setInstitution($institution);
            //ReferringProvider
            $encounterReferringProvider = new EncounterReferringProvider('valid', $user, $system);
            $encounter2->addReferringProvider($encounterReferringProvider);
            //AttendingPhysician
            $encounterAttendingPhysician = new EncounterAttendingPhysician('valid', $user, $system);
            $encounter2->addAttendingPhysician($encounterAttendingPhysician);

            $encounter2->setProvider($user);

            //set encounter generated id
            $key = $encounter2->obtainAllKeyfield()->first();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
            $encounter2 = $em->getRepository(Encounter::class)->setEncounterKey($key, $encounter2, $user);

            //set encounter date and time
            $date = $encounter2->getDate()->first();
            $userTimeZone = $user->getPreferences()->getTimezone();
            $nowDate = new \DateTime("now", new \DateTimeZone($userTimeZone));
            $date->setField($nowDate);
            $date->setTime($nowDate);

            //set encounter status "Open"
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:EncounterStatusList'] by [EncounterStatusList::class]
            $encounterOpenStatus = $em->getRepository(EncounterStatusList::class)->findOneByName("Open");
            if ($encounterOpenStatus) {
                $encounter2->setEncounterStatus($encounterOpenStatus);
            }

            //set encounter info type to "Call to Pathology"
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:EncounterInfoTypeList'] by [EncounterInfoTypeList::class]
            $encounterInfoType = $em->getRepository(EncounterInfoTypeList::class)->findOneByName("Call to Pathology");
            if ($encounterInfoType) {
                if (count($encounter2->getEncounterInfoTypes()) > 0) {
                    $encounter2->getEncounterInfoTypes()->first()->setField($encounterInfoType);
                }
            }

            //testing
            //echo "next key=".$calllogUtil->getNextEncounterGeneratedId()."<br>";
            //$calllogUtil->checkNextEncounterGeneratedId();
            //testing
            //$userFormNodeUtil = $this->container->get('user_formnode_utility');
            //$formNodeTest = $em->getRepository('AppUserdirectoryBundle:FormNode')->findOneByName("Blood Product Transfused");
            //$values = $userFormNodeUtil->getDropdownValue($formNodeTest);
            //print_r($values);
            //exit('1');

            //create a new spot and add it to the encounter's tracker
//            $withdummyfields = true;
//            //$locationTypePrimary = null;
//            $encounterLocationType = $em->getRepository('AppUserdirectoryBundle:LocationTypeList')->findOneByName("Encounter Location");
//            if (!$encounterLocationType) {
//                throw new \Exception('Location type is not found by name Encounter Location');
//            }
//            $locationName = null;   //""; //"Encounter's Location";
//            $spotEntity = null;
//            $removable = 0;
//            $encounter2->addContactinfoByTypeAndName($user, $system, $encounterLocationType, $locationName, $spotEntity, $withdummyfields, $em, $removable);
            $encounter2 = $calllogUtil->addDefaultLocation($encounter2,$user,$system);
        }//!$encounter2

        $message = $this->createCalllogEntryMessage($user,$permittedInstitutions,$system,$messageTypeId); //new

        //set patient list
        $patientList = $calllogUtil->getDefaultPatientList();
        //echo "patientList ID=".$patientList->getId()."<br>";
        $message->getCalllogEntryMessage()->addPatientList($patientList);

        //add patient
        $message->addPatient($patient);
        //add encounter
        $message->addEncounter($encounter2);

        //set default accession list
        $scanorderUtil = $this->container->get('scanorder_utility');
        $accessionList = $scanorderUtil->getDefaultAccessionList();
        $message->addAccessionList($accessionList);

        //add calllog task
        //$task = new CalllogTask($user);
        //$message->getCalllogEntryMessage()->addCalllogTask($task);
        ///////////// EOF Message //////////////

        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle,$readonlyEncounter); //entry/new

        //$encounterid = $calllogUtil->getNextEncounterGeneratedId();

        $formnodetrigger = 1;
        if( $messageTypeId ) {
            $formnodetrigger = 0; //build formnodes from top to bottom
        }

        //top message category id
        $formnodeTopHolderId = null;
        //$categoryStr = "Pathology Call Log Entry";
        //$messageCategory = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($categoryStr);
        $messageCategory = $calllogUtil->getDefaultMessageCategory();
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'formtype' => $formtype,
            'triggerSearch' => 0,
            'mrn' => $mrn,
            'mrntype' => $mrntype,
            'titleheadroom' => $titleheadroom,
            'formnodetrigger' => $formnodetrigger,
            'formnodeTopHolderId' => $formnodeTopHolderId,
            'readonlyPatient' => $readonlyPatient,
            'readonlyEncounter' => $readonlyEncounter
            //'encounterid' => $encounterid
        );
    }

    /**
     * Save Call Log Entry Same Patient
     */
    #[Route(path: '/entry/same-patient/save', name: 'calllog_save_entry_same_patient', methods: ['POST'], options: ['expose' => true])]
    #[Template('AppCallLogBundle/CallLog/call-entry-same-patient.html.twig')]
    public function saveEntrySamePatientAction(Request $request)
    {
        if( false == $this->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //exit('save entry (same patient)');
        //case 1: patient exists: create a new encounter to DB and add it to the existing patient
        //add patient id field to the form (id="oleg_calllogbundle_patienttype_id") or use class="calllog-patient-id" input field.
        //case 2: patient does not exists: create a new encounter to DB

        $user = $this->getUser();
        $securityUtil = $this->container->get('user_security_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $orderUtil = $this->container->get('scanorder_utility');
        $calllogUtil = $this->container->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

        //$readonlyPatient = trim((string)$request->get('readonlyPatient'));
        //$readonlyEncounter = trim((string)$request->get('readonlyEncounter'));

        $testing = false;
        //$testing = true;

        //check if user has at least one institution
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('calllog_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('calllog_home') );
        }

//        $mrn = trim((string)$request->get('mrn'));
//        $mrntype = trim((string)$request->get('mrntype'));
        $mrn = null;
        $mrntype = null;

        $title = "Save Entry";

        $system = $securityUtil->getDefaultSourceSystem($this->getParameter('calllog.sitename'));
        $status = 'valid';
        $cycle = 'new';
        $formtype = 'call-entry';

        $message = $this->createCalllogEntryMessage($user,$permittedInstitutions,$system);

        // Create an ArrayCollection of the current Task objects in the database
        $originalTasks = new ArrayCollection();
        foreach($message->getCalllogEntryMessage()->getCalllogTasks() as $task) {
            $originalTasks->add($task);
        }

        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle); ///entry/save

        $form->handleRequest($request);

        if( $form->isSubmitted() ) {

            $msg = "No Case found. No action has been performed.";
            $institution = $userSecUtil->getCurrentUserInstitution($user);

            $patients = $message->getPatient();
            if( count($patients) != 1 ) {
                throw new \Exception( "Message must have only one patient. Patient count= ".count($patients)."'" );
            }
            $patient = $patients->first();

            //it should work for mysql, mssql, but in postgres DB's id is already pre-genarated even when object is in the pre-persisting stage with "new" (new Patient)
            if( $patient->getId() ) {
                //get existing patient from DB to prevent creating a new one
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
                $patient = $em->getRepository(Patient::class)->find($patient->getId());
                if( $patient ) {
                    $existingPatientDB = true;
                }
            } else {
                $existingPatientDB = false;
            }

            echo "message id=".$message->getId()."<br>";
            echo "patient id=".$patient->getId()."<br>";

            $patientInfoEncounter = null;
            $newEncounter = null;
            //get a new encounter without id
            foreach( $message->getEncounter() as $encounter ) {
                echo "encounter ID=".$encounter->getId()."; status=".$encounter->getStatus()."<br>";
                if( $encounter->getStatus() == 'valid' ) {
                    $newEncounter = $encounter;
                }
            }

            //it should work for mysql, mssql, but in postgres DB's id is already pre-genarated even when object is in the pre-persisting stage with "new" (new Encounter)
            $existingEncounterDB = false;
            if( $newEncounter->getId() ) {
                //get existing encounter from DB to prevent creating a new one
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
                $encounterDB = $em->getRepository(Encounter::class)->find($newEncounter->getId());
                if( $encounterDB ) {
                    $existingEncounterDB = $encounterDB;
                    $newEncounter = $encounterDB;
                }
            }

            //process Task sections
            $taskUpdateStr = $calllogUtil->processCalllogTask($message,$originalTasks); //Save New Call Log Entry

            //process Attached Documents
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
            $em->getRepository(Document::class)->processDocuments($message->getCalllogEntryMessage()); //Save Call Log Entry Same Patient

            //set system source and user's default institution
            if( $newEncounter ) {

                if( !$existingEncounterDB ) {
                    ////////////// processing new encounter ///////////////////
                    $newEncounter->setSource($system);
                    $newEncounter->setInstitution($institution);
                    $newEncounter->setVersion(1);

                    //assign generated encounter number ID
                    $key = $newEncounter->obtainAllKeyfield()->first();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
                    $em->getRepository(Encounter::class)->setEncounterKey($key, $newEncounter, $user);

                    //Remove tracker if spots/location is empty
                    $tracker = $newEncounter->getTracker();
                    if ($tracker) {
                        $tracker->removeEmptySpots();
                        if ($tracker->isEmpty()) {
                            //echo "Tracker is empty! <br>";
                            $newEncounter->setTracker(null);
                        } else {
                            //echo "Tracker is not empty! <br>";
                            //check if location name is not empty
                            if ($newEncounter->getTracker()) {
                                $currentLocation = $newEncounter->getTracker()->getSpots()->first()->getCurrentLocation();
                                if (!$currentLocation->getName()) {
                                    $currentLocation->setName('');
                                }
                                if (!$currentLocation->getCreator()) {
                                    $currentLocation->setCreator($user);
                                }
                            }
                        }
                    }//$tracker

                    //prevent creating a new location every time: if location id is provided => find location in DB and replace it with tracker->spot->location
                    $calllogUtil->processTrackerLocation($newEncounter);

                    //process EncounterReferringProvider: set Specialty, Phone and Email for a new userWrapper (getReferringProviders)
                    $calllogUtil->processReferringProviders($newEncounter, $system);
                    ////////////// EOF processing new encounter ///////////////////
                }

                //clear encounter
                $message->clearEncounter();
                //add encounter to the message
                $message->addEncounter($newEncounter);

                //set message status from the form's name="messageStatus" field
                $data = $request->request->all();
                $messageStatusForm = $data['messageStatusJs'];
                //echo "messageStatusForm=".$messageStatusForm."<br>";
                if( $messageStatusForm ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageStatusList'] by [MessageStatusList::class]
                    $messageStatusObj = $em->getRepository(MessageStatusList::class)->findOneByName($messageStatusForm);
                    if( $messageStatusObj ) {
                        //echo "set message status to ".$messageStatusObj."<br>";
                        $message->setMessageStatus($messageStatusObj);

                        //if "Signed" set signed User, datetime, roles by signeeInfo
                        if( $messageStatusObj->getName()."" == "Signed" ) {
                            if ($message->getSigneeInfo()) {
                                //echo "signee exist <br>";
                                $signeeInfo = $message->getSigneeInfo();
                                $signeeInfo->setInfo($user);
                            } else {
                                //echo "signee does exist <br>";
                                $message->setSigneeInfo(new ModifierInfo($user));
                            }
                        }

                        //if "Deleted" set signed User, datetime, roles by signeeInfo
                        if( $messageStatusObj->getName()."" == "Deleted" ) {
                            //echo "deleted <br>";
                            $editorInfo = new ModifierInfo($user);
                            $message->addEditorInfo($editorInfo);
                        }

                        if( $messageStatusObj->getName()."" == "Draft" ) {
                            //echo "add editor: draft <br>";
                            $editorInfo = new ModifierInfo($user);
                            $message->addEditorInfo($editorInfo);
                        }

                    }
                }


                if( $message->getMessageCategory() ) {

                    //message title setMessageTitle: show the title of the form (not the message type) here, not just its ID
                    $messageTitle = $message->getMessageTitleStr();
                    $message->setMessageTitle($messageTitle);
                }

                //On the server side write in the "Versions" of the associated forms into this "Form Version" field in the same order as the Form titles+IDs
                $calllogUtil->setFormVersions($message,$cycle);

                //////////////////// Processing ////////////////////////
                if( $existingPatientDB ) {

                    //get existing patient from DB to prevent creating a new one
                    //$patient = $em->getRepository('AppOrderformBundle:Patient')->find($patient->getId());

                    if( $existingEncounterDB ) {
                        //CASE 1A
                        echo "case 1A: same exists, same encounter <br>";
                    } else {
                        //CASE 1B
                        echo "case 1B: same patient, create a new encounter to DB and add it to the existing patient <br>";
                        /////////// processing new encounter ///////////
                        //reset institution from the patient
                        $newEncounter->setInstitution($patient->getInstitution());

                        //add encounter to patient
                        $patient->addEncounter($newEncounter);

                        //update patient's last name, first name, middle name, dob, sex, ...
                        $calllogUtil->updatePatientInfoFromEncounter($patient, $newEncounter, $user, $system);
                        /////////// EOF processing new encounter ///////////
                    }

                    //add patient to message
                    $message->clearPatient();
                    $message->addPatient($patient);

                    if(0) { //testing
                        echo "encounter count=" . count($patient->getEncounter()) . "<br>";
                        foreach ($patient->getEncounter() as $encounter) {
                            echo "<br>encounter ID=" . $encounter->getId() . "<br>";
                            echo "encounter Date=" . $encounter->getDate()->first() . "<br>";
                            echo "encounter Last Name=" . $encounter->getPatlastname()->first() . "<br>";
                            echo "encounter First Name=" . $encounter->getPatfirstname()->first() . "<br>";
                            echo "encounter Middle Name=" . $encounter->getPatmiddlename()->first() . "<br>";
                            echo "encounter Suffix=" . $encounter->getPatsuffix()->first() . "<br>";
                            echo "encounter Gender=" . $encounter->getPatsex()->first() . "<br>";

                            if( $encounter->getTracker() ) {
                                echo "encounter Location=" . $encounter->getTracker()->getSpots()->first()->getCurrentLocation()->getName() . "<br>";
                            }
                        }
                    }

                    //echo "patient count=".count($message->getPatient())."<br>";
                    //echo "patient=".$message->getPatient()->first()->obtainPatientInfoTitle()."<br>";
                    //echo $name."<br>";
                    //exit('1');

                    //exit('Exit Case 1');
                    //$em->persist($patient);
                    if( !$testing ) {
                        if( !$existingEncounterDB ) {
                            $em->persist($newEncounter);
                        }
                        $em->persist($message);
                        $em->flush();
                    }

                    //add patient to the complex patient list specified by patientListTitle if the option addPatientToList is checked.
                    //do it after message is in DB and has ID
                    $calllogUtil->addToPatientLists($patient,$message,$testing);

                    //add Accession to the Accession list specified by accessionListTitle if the option addAccessionToList is checked.
                    //do it after message is in DB and has ID
                    $calllogUtil->addToCalllogAccessionLists($message,$testing);
                    
                    if( $existingEncounterDB ) {
                        //CASE 1A
                        $msg = "Call Log Entry has been created: Same Encounter (ID#" . $newEncounter->getId() . ") with number " . $newEncounter->obtainEncounterNumber() . " for the Patient with ID #" . $patient->getId();
                    } else {
                        //CASE 1B
                        $msg = "Call Log Entry has been created: New Encounter (ID#" . $newEncounter->getId() . ") is created with number " . $newEncounter->obtainEncounterNumber() . " for the Patient with ID #" . $patient->getId();
                    }

                } else {
                    //CASE 2
                    echo "case 2: patient does not exists: create a new encounter to DB <br>";
                    //app_calllogbundle_patienttype[encounter][0][referringProviders][0][referringProviderPhone]

                    throw new \Exception( "For this controller patient must exists. encounterId=".$newEncounter->getId() );
                }
                //////////////////// EOF Processing ////////////////////////

                //process form nodes
                $formNodeUtil = $this->container->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request,$message->getMessageCategory(),$message,$testing); //same-patient/save
                //exit('after formnode');


                //log search action
                if( $msg ) {
                    $eventType = "New Call Log Book Entry Submitted";

                    $eventStr = $calllogUtil->getEventLogDescription($message,$patient,$newEncounter);
                    //exit('eventStr='.$eventStr);

                    //$eventStr = $eventStr . " submitted by " . $user;

                    if( $taskUpdateStr ) {
                        $eventStr = $eventStr . "<br><br>" . $taskUpdateStr;
                        $msg = $msg . "<br><br>" . $taskUpdateStr;
                    }

                    if( !$testing ) {
                        $userSecUtil->createUserEditEvent($this->getParameter('calllog.sitename'), $eventStr, $user, $message, $request, $eventType);
                    }
                }

                if( !$testing ) {
                    //send an email to the Preferred Email of the "Attending:"
                    $calllogUtil->sendConfirmationEmail($message, $patient, $newEncounter);
                }

            }//if $newEncounter

            if( $testing ) {
                echo "<br><br>message id=" . $message->getId() . "<br>";
                foreach ($message->getPatient() as $patient) {
                    echo "patient id=" . $patient->getId() . "<br>";
                }
                foreach ($message->getEncounter() as $encounter) {
                    echo "encounter id=" . $encounter->getId() . "<br>";
                }

                exit('form is submitted and finished, msg='.$msg);
            }

            $this->addFlash(
                'notice',
                $msg
            );

            //echo "return messageId=".$message->getId()."<br>";
            //exit('1');

            //return $this->redirect( $this->generateUrl('calllog_callentry') );
            if( $message->getId() ) {
                return $this->redirect($this->generateUrl('calllog_callentry_view', array('messageOid'=>$message->getOid(),'messageVersion'=>$message->getVersion())));
            } else {
                return $this->redirect($this->generateUrl('calllog_home'));
            }
        }
        //exit('form is not submitted');

        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'formtype' => $formtype,
            'triggerSearch' => 0,
            'mrn' => $mrn,
            'mrntype' => $mrntype
        );
    }//save


}
