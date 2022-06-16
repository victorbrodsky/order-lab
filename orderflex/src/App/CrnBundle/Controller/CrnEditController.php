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

namespace App\CrnBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use App\OrderformBundle\Entity\EncounterAttendingPhysician;
use App\OrderformBundle\Entity\EncounterReferringProvider;
use App\UserdirectoryBundle\Entity\ModifierInfo;
use App\UserdirectoryBundle\Entity\Spot;
use App\UserdirectoryBundle\Entity\Tracker;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class CrnEditController extends CrnEntryController
{

    /**
     * @Route("/delete/{messageOid}/{messageVersion}", name="crn_delete", methods={"GET"})
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function deleteMessageAction(Request $request, $messageOid, $messageVersion)
    {
        if( false == $this->isGranted("ROLE_CRN_USER") ){
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        //$userServiceUtil = $this->container->get('user_service_utility');
        //$user = $this->getUser();
        //$securityUtil = $this->container->get('user_security_utility');
        //$userSecUtil = $this->container->get('user_security_utility');
        //$orderUtil = $this->container->get('scanorder_utility');
        //$crnUtil = $this->container->get('crn_util');
        $em = $this->getDoctrine()->getManager();

        $message = $em->getRepository('AppOrderformBundle:Message')->findByOidAndVersion($messageOid,$messageVersion);
        if( !$message ) {
            throw new \Exception( "Message is not found by oid ".$messageOid." and version ".$messageVersion );
        }

        $msg = $this->deleteMessage( $message, "delete link", $request );

        $this->addFlash(
            'pnotify',
            $msg
        );

        return $this->redirect($this->generateUrl('crn_home'));
    }


    /**
     * @Route("/un-delete/{messageOid}/{messageVersion}", name="crn_undelete", methods={"GET"})
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function unDeleteMessageAction(Request $request, $messageOid, $messageVersion)
    {
        if( false == $this->isGranted("ROLE_CRN_USER") ){
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $userSecUtil = $this->container->get('user_security_utility');

        $message = $em->getRepository('AppOrderformBundle:Message')->findByOidAndVersion($messageOid,$messageVersion);
        if( !$message ) {
            throw new \Exception( "Message is not found by oid ".$messageOid." and version ".$messageVersion );
        }

        $messageStatusPrior = $message->getMessageStatusPrior();

        if( !$messageStatusPrior ) {
            $this->addFlash(
                'notice',
                'Prior entry status is undefined, therefore, no modification has been performed.'
            );
            return $this->redirect($this->generateUrl('crn_home'));
        }

        $message->setMessageStatus($messageStatusPrior);

        $em->flush($message);

        //Entry 123 for PatientFirstName PatientLastName (DOB: MM/DD/YYYY) submitted on
        // [submitted timestamp in MM/DD/YYYY HH:MM 24HR format] by SubmitterFirstName SubmitterLastName, MD successfully
        // un-deleted and status set to [name of status]
        $patientInfoStr = $message->getPatientNameMrnInfo();
        if( $patientInfoStr ) {
            $patientInfoStr = "for ".$patientInfoStr;
        }
        $msg = "Message Entry ID#".$message->getMessageOidVersion()." $patientInfoStr submitted on ".$userServiceUtil->getSubmitterInfo($message).
            " successfully un-deleted and status set to ".$messageStatusPrior;
//        $this->addFlash(
//            'notice',
//            $msg
//        );
        $this->addFlash(
            'pnotify',
            $msg
        );

        $eventType = "Critical Result Notification Entry Undeleted";
        $userSecUtil->createUserEditEvent($this->getParameter('crn.sitename'), $msg, $user, $message, $request, $eventType);

        return $this->redirect($this->generateUrl('crn_home'));
    }

    public function deleteMessage( $message, $actionStr, $request, $testing=false ) {
        $em = $this->getDoctrine()->getManager();
        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->getUser();

        if( $message->getMessageStatus()->getName()."" != "Deleted" ) {
            $message->setMessageStatusPrior($message->getMessageStatus());
        }

        $messageStatus = $em->getRepository('AppOrderformBundle:MessageStatusList')->findOneByName("Deleted");
        if( !$messageStatus ) {
            throw new \Exception( "Message Status is not found by name '"."Deleted"."'" );
        }

        $message->setMessageStatus($messageStatus);

        if( !$testing ) {
            $em->flush($message);
        }

        //"Entry 123 for PatientFirstName PatientLastName (DOB: MM/DD/YYYY) submitted on
        // [submitted timestamp in MM/DD/YYYY HH:MM 24HR format] by SubmitterFirstName SubmitterLastName, MD successfully deleted
        $patientInfoStr = $message->getPatientNameMrnInfo();
        if( $patientInfoStr ) {
            $patientInfoStr = "for ".$patientInfoStr;
        }
        $msg = "Message Entry ID#".$message->getMessageOidVersion()." $patientInfoStr submitted on ".$userServiceUtil->getSubmitterInfo($message)." successfully deleted by ".$actionStr;

        //testing
        //$msg = $msg . " DB ID#".$message->getID();

        //Event Log
        $eventType = "Critical Result Notification Entry Deleted";
        $userSecUtil->createUserEditEvent($this->getParameter('crn.sitename'), $msg, $user, $message, $request, $eventType);

        return $msg;
    }


    /**
     * Get Critical Result Notification Entry Message Edit page
     * @Route("/entry/edit/{messageOid}/{messageVersion}", name="crn_crnentry_edit", methods={"GET"})
     * @Route("/entry/amend/{messageOid}/{messageVersion}", name="crn_crnentry_amend", methods={"GET"})
     * @Route("/entry/edit-latest-encounter/{messageOid}/{messageVersion}", name="crn_crnentry_edit_latest_encounter", methods={"GET"})
     * @Route("/entry/amend-latest-encounter/{messageOid}/{messageVersion}", name="crn_crnentry_amend_latest_encounter", methods={"GET"})
     * @Template("AppCrnBundle/Crn/crn-entry-edit.html.twig")
     */
    public function getCrnEntryAction(Request $request, $messageOid, $messageVersion=null)
    {

        if (false == $this->isGranted('ROLE_CRN_USER')) {
            return $this->redirect($this->generateUrl('crn-nopermission'));
        }

        //ini_set('memory_limit', '5120M');
        //ini_set('memory_limit', '-1');

        $userSecUtil = $this->container->get('user_security_utility');
        $crnUtil = $this->container->get('crn_util');
        $securityUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //$title = "Critical Result Notification";
        $formtype = "crn-entry";

        $route = $request->get('_route');
        if( strpos((string)$route, "crn_crnentry_edit") !== false ) {
            $cycle = "edit";
        }
        if( strpos((string)$route, "crn_crnentry_amend") !== false ) {
            $cycle = "amend";
        }

        //$patientId = trim((string)$request->get('patientId'));
        //$nowStr = trim((string)$request->get('nowStr'));
        //echo "patientId=".$patientId."<br>";
        //echo "nowStr=".$nowStr."<br>";
        //$messageId = 142; //154; //testing

        if( !is_numeric($messageVersion) || !$messageVersion ) {
            $messageLatest = $em->getRepository('AppOrderformBundle:Message')->findByOidAndVersion($messageOid);

            if( !$messageLatest && !$messageVersion ) {
                //handle case with th real DB id: http://localhost/order/crn-book/entry/view/267
                $messageLatest = $em->getRepository('AppOrderformBundle:Message')->find($messageOid);
            }

            if( $messageLatest ) {
                return $this->redirect($this->generateUrl($route, array(
                    'messageOid' => $messageLatest->getOid(),
                    'messageVersion' => $messageLatest->getVersion()
                )));
            }

            throw new \Exception( "Latest Message is not found by oid ".$messageOid );
        }

        $message = $em->getRepository('AppOrderformBundle:Message')->findByOidAndVersion($messageOid,$messageVersion);
        if( !$message ) {
            throw new \Exception( "Message is not found by oid ".$messageOid." and version ".$messageVersion );
        }

        //Replace encounter with the latest encounter.
        //Used replaced encounter for latest url only to show message's encounter, not patient's encounter!.
        if( strpos((string)$route, "_latest_encounter") !== false ) {
            $encounter = $message->getEncounter()->first();
            if( !$crnUtil->isLatestEncounterVersion($encounter) ) {
                $latestEncounter = $em->getRepository('AppOrderformBundle:Encounter')->findLatestVersionEncounter($encounter);
                if( $latestEncounter ) {
                    //echo "Original id=".$encounter->getId()."; version=".$encounter->getVersion()." => latestEncounter: id=".$latestEncounter->getId()."; version=".$latestEncounter->getVersion()."<br>";
                    //clear encounter
                    $message->clearEncounter();
                    //add encounter to the message
                    $message->addEncounter($latestEncounter);
                }
            }
        }

        //Testing (show): Check and copy attachments
//        $documents = $message->getCrnEntryMessage()->getDocuments();
//        echo "documents count=".count($documents)."<br>";
//        foreach ($documents as $document) {
//            echo "document: ID=".$document->getId()."; Size==".$document->getSizeStr()."; abspath=".$document->getAbsoluteUploadFullPath()."<br>";
//        }
        //exit('111');

        $messageInfo = "Entry ID ".$message->getMessageOidVersion()." submitted on ".$userServiceUtil->getSubmitterInfo($message); // . " | Critical Result Notification";
        //echo "messageInfo=".$messageInfo."<br>";
        //exit('1');
        if (count($message->getPatient()) > 0 ) {
            $mrnRes = $message->getPatient()->first()->obtainStatusField('mrn', "valid");
            //$mrntype = $mrnRes->getKeytype()->getId();
            if( $mrnRes->getKeytype() ) {
                $mrntype = $mrnRes->getKeytype()->getId();
            } else {
                $mrntype = NULL;
            }
            $mrn = $mrnRes->getField();

            //LastName, FirstName, MiddleName | MRN Type: MRN | DOB: MM/DD/YY |
            // Entry ID XXX submitted on MM/DD/YYYY at HH:MM by SubmitterFirstName SubmitterLastName, MD | Critical Result Notification
            $title = $message->getPatient()->first()->obtainPatientInfoTitle('valid',null,false);

            $messageAccessions = $message->getAccession();
            if( count($messageAccessions) > 0 ) {
                $messageAccession = $messageAccessions[0];
                $messageAccessionStr = $messageAccession->obtainFullValidKeyName();
                if( $messageAccessionStr ) {
                    $title = $title . " | " . $messageAccessionStr; // /entry/edit
                }
            }

            //edit: get message's encounter location
            $messageEncounters = $message->getEncounterLocationInfos();
            if( $messageEncounters ) {
                $title = $title . " | " . $messageEncounters;
            }

            $title = $title . " | ".$messageInfo;

        } else {
            $mrntype = null;
            $mrn = null;

            $title = $messageInfo;
        }

        ////////////////// add missing encounter fields //////////////////
        $system = $securityUtil->getDefaultSourceSystem($this->getParameter('crn.sitename'));

//        $existingEncounter = null;
//        foreach( $message->getEncounter() as $encounter ) {
//            //echo "encounter ID=".$encounter->getId()."; status=".$encounter->getStatus()."<br>";
//            //if( !$encounter->getId() ) {
//                if( $encounter->getStatus() == 'valid' ) {
//                    $existingEncounter = $encounter;
//                    break;
//                }
//            //}
//        }

        //message should have only one attached encounter
        if( count($message->getEncounter()) > 1 ) {
            throw new \Exception('Message must have only one attached encounter. Number of attached encounters '.count($message->getEncounter()));
        }
        $existingEncounter = $message->getEncounter()->first();
        //echo "existingEncounter=".$existingEncounter->getId()."<br>";

        //ReferringProvider
        if( count($existingEncounter->getReferringProviders()) == 0 ) {
            $encounterReferringProvider = new EncounterReferringProvider('valid', $user, $system);
            $existingEncounter->addReferringProvider($encounterReferringProvider);
        }
        //AttendingPhysician
        if( count($existingEncounter->getAttendingPhysicians()) == 0 ) {
            $encounterAttendingPhysician = new EncounterAttendingPhysician('valid', $user, $system);
            $existingEncounter->addAttendingPhysician($encounterAttendingPhysician);
        }
        //Location: entity.tracker.spots
//        if( !$existingEncounter->getTracker() ) {
//            $tracker = new Tracker();
//            $existingEncounter->setTracker($tracker);
//        }
//        if( count($existingEncounter->getTracker()->getSpots()) == 0 ) {
//            $spotEntity = new Spot($user,$system);
//            $existingEncounter->getTracker()->addSpot($spotEntity);
//        }
        if( !$existingEncounter->getTracker() ) {
//            $withdummyfields = true;
//            //$locationTypePrimary = null;
//            $encounterLocationType = $em->getRepository('AppUserdirectoryBundle:LocationTypeList')->findOneByName("Encounter Location");
//            if (!$encounterLocationType) {
//                throw new \Exception('Location type is not found by name Encounter Location');
//            }
//            $locationName = null;   //""; //"Encounter's Location";
//            $spotEntity = null;
//            $removable = 0;
            //$existingEncounter->addContactinfoByTypeAndName($user, $system, $encounterLocationType, $locationName, $spotEntity, $withdummyfields, $em, $removable);
            $existingEncounter = $crnUtil->addDefaultLocation($existingEncounter,$user,$system);
        }
        ////////////////// EOF add missing encounter fields //////////////////

        //echo "patients=".count($message->getPatient())."<br>";
        $form = $this->createCrnEntryForm($message,$mrntype,$mrn,$cycle);

        $complexPatientStr = null;
        //find record in the "Pathology Crnl Complex Patients" list by message object entityName, entityId
//        $mapper = array(
//            'prefix' => "App",
//            'bundleName' => "CrnBundle",
//            'className' => "CrnComplexPatients",
//        );
//        $listRecord = $userSecUtil->getListByNameAndObject( $message, $mapper );
//        if( $listRecord ) {
//            //Patient was added to the "xxxxxxxx" list via this entry.
//            $complexPatientStr = "Patient was added to the Pathology Crn Complex Patients list ID# ".$listRecord->getId()." via this entry:<br>".$listRecord->getName()."";
//        }
        //echo "complexStr=".$complexPatientStr."<br>";

        $class = new \ReflectionClass($message);
        $className = $class->getShortName();          //ObjectTypeText
        $classNamespace = $class->getNamespaceName(); //App\UserdirectoryBundle\Entity

        //top message category id
        //TODO: get buy default values in site settings
        $formnodeTopHolderId = null;
        //$categoryStr = "Critical Result Notification";
        //$messageCategory = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($categoryStr);
        $messageCategory = $crnUtil->getDefaultMessageCategory();
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        //View Previous Version(s)
        $allMessages = $em->getRepository('AppOrderformBundle:Message')->findAllMessagesByOid($messageOid); //$messageVersion=null => all messages ordered by latest version first

        //find current (latest) message status
        $latestMessageStatus = null;
        $latestMessageLabel = null;
        $latestMessage = $em->getRepository('AppOrderformBundle:Message')->findLatestMessageByOid(null,$allMessages);
        $latestNextMessageVersion = intval($latestMessage->getVersion()) + 1;
        //echo "latestNextMessageVersion=".$latestNextMessageVersion."<br>";
        if( $latestMessage && intval($messageVersion) != intval($latestMessage->getVersion()) ) {
            $latestMessageStatus = $latestMessage->getMessageStatus()->getName()."";
            //"Current Status of the Current Version of this message (Current Version is X, Displaying Version Y):"
            $latestMessageLabel = "Current Status of the Current Version of this message (Current Version is $messageVersion, New Displaying Version ".$latestNextMessageVersion."):";
        }
        //echo "messageLabel=".$latestMessageLabel."<br>";

        $latestEntryUrl = $this->generateUrl(
            $route,
            array('messageOid'=>$message->getOid(), 'messageVersion'=>'latest'),
            UrlGeneratorInterface::ABSOLUTE_URL // This guy right here
        );

        $maxEncounterVersion = $em->getRepository('AppOrderformBundle:Encounter')->getMaxEncounterVersion($existingEncounter);
        $latestNextEncounterVersion = intval($maxEncounterVersion) + 1;

        //Event Log - User accessing “Edit Entry” page should be added to the event log as an event for that object/note (Event Type “Entry Edit Accessed”)
        //$userSecUtil = $this->container->get('user_security_utility');
        //$user = $this->getUser();
        $eventType = "Critical Result Notification Entry Edit Accessed";
        $eventStr = "Critical Result Notification Entry ID#".$message->getMessageOidVersion()." has been viewed on the edit page by ".$user;
        $userSecUtil->createUserEditEvent($this->getParameter('crn.sitename'), $eventStr, $user, $message, $request, $eventType); //View Critical Result Notification Entry


        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'formtype' => $formtype,
            'triggerSearch' => 0,
            'mrn' => $mrn,
            'mrntype' => $mrntype,
            'message' => $message,
            'complexPatientStr' => $complexPatientStr,
            //'encounterid' => $encounterid
            'entityNamespace' => $classNamespace,
            'entityName' => $className,
            'entityId' => $message->getId(),
            'sitename' => $this->getParameter('crn.sitename'),
            'titleheadroom' => $title,
            'formnodeTopHolderId' => $formnodeTopHolderId,
            'currentMessageStatus' => $latestMessageStatus,
            'currentMessageLabel' => $latestMessageLabel,
            'allMessages' => $allMessages,
            'currentMessageVersion' => $latestNextMessageVersion,
            'currentEncounterVersion' => $latestNextEncounterVersion,
            'latestEntryUrl' => $latestEntryUrl
        );
    }



    /**
     * Save/Update Critical Result Notification Entry
     * @Route("/entry/update/{messageId}/{cycle}", name="crn_update_entry", methods={"POST"}, options={"expose"=true})
     * @Template("AppCrnBundle/Crn/crn-entry-edit.html.twig")
     */
    public function updateEntryAction(Request $request, $messageId, $cycle)
    {
        if( false == $this->isGranted("ROLE_CRN_USER") ){
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        //exit('update entry');

        $user = $this->getUser();
        $securityUtil = $this->container->get('user_security_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $orderUtil = $this->container->get('scanorder_utility');
        $crnUtil = $this->container->get('crn_util');
        $em = $this->getDoctrine()->getManager();

        $testing = false;
        //$testing = true;

        //check if user has at least one institution
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('crn_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('crn_home') );
        }

        $originalMessage = $em->getRepository('AppOrderformBundle:Message')->find($messageId);
        if (!$originalMessage) {
            throw new \Exception('Original Message not found by ID ' . $messageId);
        }

        $mrn = null;
        $mrntype = null;

        $title = "Update Entry";

        $system = $securityUtil->getDefaultSourceSystem($this->getParameter('crn.sitename'));
        $status = 'valid';
        $cycleForm = 'new';
        $formtype = 'crn-entry';

        $message = $this->createCrnEntryMessage($user,$permittedInstitutions,$system);

        // Create an ArrayCollection of the current Task objects in the database
        $originalTasks = new ArrayCollection();
        foreach($originalMessage->getCrnEntryMessage()->getCrnTasks() as $task) {
            $originalTasks->add($task);
        }
//        foreach($originalTasks as $task) {
//            echo "Original task=".$task."<br>";
//        }

//        //Testing (Save/Update Critical Result Notification Entry): Check and copy attachments
//        $documents = $message->getCrnEntryMessage()->getDocuments();
//        echo "1documents count=".count($documents)."<br>";
//        foreach ($documents as $document) {
//            echo "1document: ID=".$document->getId()."; Size==".$document->getSizeStr()."; abspath=".$document->getAbsoluteUploadFullPath()."<br>";
//        }
        //exit('111');

        $form = $this->createCrnEntryForm($message,$mrntype,$mrn,$cycleForm);

        $form->handleRequest($request);

        if( $form->isSubmitted() ) {

            //echo "message id=".$message->getId()."<br>";

            $msg = "No Case found. No action has been performed.";
            $institution = $userSecUtil->getCurrentUserInstitution($user);

            $patient = null;
            $patients = $message->getPatient();
            if( count($patients) > 0 ) {
                $patient = $patients->first();
                //echo "patient id=".$patient->getId()."<br>";
            }

            //For edit page we have only one encounter with patient info => use first encounter.
            //Another dummy encounter used to search patient does not exists on the edit page.
            if( count($message->getEncounter()) == 1 ) {
                $newEncounter = $message->getEncounter()->first();
            } else {
                throw new \Exception('Edit/Amend message must contain only one encounter. Encounters count=' . count($message->getEncounter()));
            }

//            //testing
//            if( $patient ) {
//                echo "###patient encounter counter=".count($patient->getEncounter())."<br>";
//                $patientEncounter = $patient->getEncounter()->first();
//                echo "### patient encounter ID=" . $patientEncounter->getId() . "<br>";
//                echo "### encounter message count=" . count($patientEncounter->getMessage()) . "<br>";
//            }
//            echo "###message encounter counter=".count($message->getEncounter())."<br>";
//            if( count($message->getEncounter()) > 0 ) {
//                $messageEncounter = $message->getEncounter()->first();
//                echo "### message encounter ID=" . $messageEncounter->getId() . "<br>";
//            }

            //Testing (Save/Update Critical Result Notification Entry): Check and copy attachments
//            $documents = $message->getCrnEntryMessage()->getDocuments();
//            echo "1documents count=".count($documents)."<br>";
//            foreach ($documents as $document) {
//                echo "1document: ID=".$document->getId()."; Size==".$document->getSizeStr()."; abspath=".$document->getAbsoluteUploadFullPath()."<br>";
//            }
            //exit('222');

            //////////// Find and Add document by ID. The documents will be shared between original and amended crn entries. //////////////
            //$newDocuments = array();
            $crnEntryMessage = $message->getCrnEntryMessage();
            $documents = $crnEntryMessage->getDocuments();
            foreach ($documents as $document) {
                //$crnEntryMessage->removeDocument($document);
                //echo "2document: ID=".$document->getId()."; Size==".$document->getSizeStr()."; abspath=".$document->getAbsoluteUploadFullPath()."<br>";
                $documentId = $document->getId();
                if( $documentId ) {
                    $documentEntity = $em->getRepository('AppUserdirectoryBundle:Document')->find($documentId);
                    //echo "documentEntity: ID=".$documentEntity->getId()."; Size=".$documentEntity->getSizeStr()."; abspath=".$documentEntity->getAbsoluteUploadFullPath()."<br>";
                    if( $documentEntity ) {
                        //Create a new document (clone)
                        $newDocument = $crnUtil->createCopyDocument($documentEntity,$crnEntryMessage);
                        //echo "newDocument: ID=".$newDocument->getId()."; Size=".$newDocument->getSizeStr()."; abspath=".$newDocument->getAbsoluteUploadFullPath()."<br>";
                        //$newDocuments[] = $newDocument;
                        $crnEntryMessage->removeDocument($document);
                        $crnEntryMessage->addDocument($newDocument);
                    }
                }
            }

            //Testing (Save/Update Critical Result Notification Entry): Check and copy attachments
//            $documents = $crnEntryMessage->getDocuments();
//            echo "2documents count=".count($documents)."<br>";
//            foreach ($documents as $document) {
//                echo "2document: ID=".$document->getId()."; Size=".$document->getSizeStr()."; abspath=".$document->getAbsoluteUploadFullPath()."<br>";
//            }
            //exit('222');

//            if(0) {
//                //Remove all existing documents
//                $documents = $crnEntryMessage->getDocuments();
//                foreach ($documents as $document) {
//                    echo "remove document: ID=" . $document->getId() . "; Size=" . $document->getSizeStr() . "; abspath=" . $document->getAbsoluteUploadFullPath() . "<br>";
//                    $crnEntryMessage->removeDocument($document);
//                }
//                //$documents = $crnEntryMessage->getDocuments();
//                //echo "after removal documents count=".count($documents)."<br>";
//
//                //Add all new documents
//                foreach ($newDocuments as $newDocument) {
//                    echo "add newDocument: ID=" . $newDocument->getId() . "; Size=" . $newDocument->getSizeStr() . "; abspath=" . $newDocument->getAbsoluteUploadFullPath() . "<br>";
//                    $crnEntryMessage->addDocument($newDocument);
//                }
//                //$documents = $crnEntryMessage->getDocuments();
//                //echo "after adding documents count=".count($documents)."<br>";
//            }
            //////////// EOF Find and Add document by ID. The documents will be shared between original and amended crn entries. //////////////

            //process Attached Documents
            //$em->getRepository('AppUserdirectoryBundle:Document')->processDocuments($message->getCrnEntryMessage()); //save update

//            $documents = $crnEntryMessage->getDocuments();
//            echo "3documents count=".count($documents)."<br>";
//            foreach ($documents as $document) {
//                echo "3document: ID=".$document->getId()."; Size=".$document->getSizeStr()."; abspath=".$document->getAbsoluteUploadFullPath()."<br>";
//            }
            //exit('333');

            //process Task sections
            $taskUpdateStr = $crnUtil->processCrnTask($message,$originalTasks);
            //echo "taskUpdateStr=".$taskUpdateStr."<br>";
            //exit('111');

            //set system source and user's default institution
            if( $newEncounter ) {

                $newEncounter->setSource($system);
                $newEncounter->setInstitution($institution);

                //assign generated encounter number ID
                //$key = $newEncounter->obtainAllKeyfield()->first();
                //echo $newEncounter->getId().": key=".$key."<br>";
                //$em->getRepository('AppOrderformBundle:Encounter')->setEncounterKey($key, $newEncounter, $user);

                //increment encounter version for current encounter (keep status valid for all encounters, use version instead of status)
                $crnUtil->incrementVersionEncounterFamily($newEncounter);

                //Remove tracker if spots/location is empty
                $tracker = $newEncounter->getTracker();
                if( $tracker ) {
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
                }
                //exit();

//                if( $patient && $dummyEncounter ) {
//                    //$dummyEncounter must be removed from the patient
//                    $patient->removeEncounter($dummyEncounter);
//                }

                //prevent creating a new location every time: if location id is provided => find location in DB and replace it with tracker->spot->location
                $crnUtil->processTrackerLocation($newEncounter);

                //process EncounterReferringProvider: set Specialty, Phone and Email for a new userWrapper (getReferringProviders)
                $crnUtil->processReferringProviders($newEncounter,$system);


                //clear encounter
                $message->clearEncounter();
                //add encounter to the message
                $message->addEncounter($newEncounter);

                //set message status from the form's name="messageStatus" field
                $data = $request->request->all();
                $buttonStatusObj = null;
                $buttonStatusForm = $data['messageStatusJs'];
                //echo "buttonStatusForm=".$buttonStatusForm."<br>";
                if( $buttonStatusForm ) {
                    $buttonStatusObj = $em->getRepository('AppOrderformBundle:MessageStatusList')->findOneByName($buttonStatusForm);
                    if( $buttonStatusObj ) {

                        //if "Signed" set signed User, datetime, roles by signeeInfo
                        if( $buttonStatusObj->getName()."" == "Signed" ) {
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
                        if( $buttonStatusObj->getName()."" == "Deleted" ) {
                            //echo "deleted <br>";
                            $editorInfo = new ModifierInfo($user);
                            $message->addEditorInfo($editorInfo);
                        }

                        if( $buttonStatusObj->getName()."" == "Draft" ) {
                            echo "add editor: draft <br>";
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
                $crnUtil->setFormVersions($message,$cycle);

                /////////////////////// Set edited message info /////////////////////
                //set OID from original message
                $message->setOid($originalMessage->getOid());
                //increment version: latest message + 1
                $latestMessage = $em->getRepository('AppOrderformBundle:Message')->findLatestMessageByOid($originalMessage->getOid());
                $incrementedVersion = intval($latestMessage->getVersion()) + 1;
                //echo "incrementedVersion=".$incrementedVersion."<br>";
                $message->setVersion($incrementedVersion);

                if( $buttonStatusObj ) {
                    echo "set message status to " . $buttonStatusObj . "<br>";
                    //determine the new message status
                    $newMessageStatusObj = $crnUtil->getNewMessageStatus($latestMessage->getMessageStatus(), $buttonStatusObj, $originalMessage->getOid());
                    $message->setMessageStatus($newMessageStatusObj);
                }

                //delete original message
                $this->deleteMessage( $originalMessage, $cycle." action", $request, $testing );
                /////////////////////// EOF Set edited message info /////////////////////

                if( $patient && $patient->getId() ) {
                    //CASE 1
                    echo "case 1: patient exists: create a new encounter to DB and add it to the existing patient <br>";
                    //get a new encounter without id $newEncounter
                    //                foreach( $encounter->getReferringProviders() as $referringProvider ) {
                    //                    echo "encounter referringProvider phone=".$referringProvider->getReferringProviderPhone()."<br>";
                    //                }

                    $patient = $em->getRepository('AppOrderformBundle:Patient')->find($patient->getId());
                    $message->clearPatient();
                    $message->addPatient($patient);

                    //reset institution from the patient
                    $newEncounter->setInstitution($patient->getInstitution());

                    $patient->addEncounter($newEncounter);

                    $crnUtil->updatePatientInfoFromEncounter($patient,$newEncounter,$user,$system );

                    if( !$testing ) {
                        $em->persist($newEncounter);
                        $em->persist($message);
                        $em->flush();
                    }

                    //add patient to the complex patient list specified by patientListTitle if the option addPatientToList is checked.
                    //do it after message is in DB and has ID
                    $crnUtil->addToPatientLists($patient,$message,$testing);

                    //add Accession to the Accession list specified by accessionListTitle if the option addAccessionToList is checked.
                    //do it after message is in DB and has ID
                    $crnUtil->addToCrnAccessionLists($message,$testing);

                    //New Encounter (ID#" . $newEncounter->getId() . ")
                    $msg = " is created with Encounter number " . $newEncounter->obtainEncounterNumber() . " for the Patient with ID #" . $patient->getId();

                } else {
                    //CASE 2
                    echo "case 2: patient does not exists: create a new encounter to DB <br>";
                    //app_CrnBundle_patienttype[encounter][0][referringProviders][0][referringProviderPhone]

                    $newEncounter->setPatient(null);

                    //remove empty patient from message
                    if( $patient ) {
                        $message->removePatient($patient);
                    }

                    //exit('Exit Case 2');
                    if( !$testing ) {
                        $em->persist($newEncounter);
                        //$em->flush($newEncounter); //testing

                        $em->persist($message);
                        //$em->flush($message); //testing
                        $em->flush();
                    }

                    //New Encounter (ID#" . $newEncounter->getId() . ")
                    $msg = " is created with Encounter number " . $newEncounter->obtainEncounterNumber();
                }

                //set encounter as message's input
                //$message->addInputObject($newEncounter);
                //$em->persist($message);
                //$em->flush($message);

                //process form nodes
                $formNodeUtil = $this->container->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request,$message->getMessageCategory(),$message,$testing); //testing
                //exit('after formnode');

                $crnUtil->deleteAllOtherMessagesByOid($message,$cycle,$testing);

//                /////////////////////// Set edited message info /////////////////////
//                //set OID from original message
//                $message->setOid($originalMessage->getOid());
//                //increment version: latest message + 1
//                $latestMessage = $em->getRepository('AppOrderformBundle:Message')->findLatestMessageByOid($messageId);
//                $incrementedVersion = intval($latestMessage->getVersion()) + 1;
//                echo "incrementedVersion=".$incrementedVersion."<br>";
//                $message->setVersion($incrementedVersion);
//
//                if( !$testing ) {
//                    $em->persist($message);
//                    $em->flush($message);
//                }
//                //delete original message
//                //$this->deleteMessage( $originalMessage, $cycle." action", $request );
//                /////////////////////// EOF Set edited message info /////////////////////

                //log search action
                $logger = $this->container->get('logger');
                $logger->notice("before check msg=".$msg);
                if( $msg ) {

                    if( $cycle == "edit" ) {
                        $msg = "Updated Message Entry ID#".$originalMessage->getMessageOidVersion() . " (new version " . $incrementedVersion .") ". $msg;
                        $eventType = "Critical Result Notification Entry Edited";
                    }
                    if( $cycle == "amend" ) {
                        $msg = "Amended Message Entry ID#".$originalMessage->getMessageOidVersion() . " (new version " . $incrementedVersion .") ". $msg;
                        $eventType = "Critical Result Notification Entry Amended";
                    }

                    $eventStr = $crnUtil->getEventLogDescription($message,$patient,$newEncounter);
                    //exit('eventStr='.$eventStr);

                    if( $taskUpdateStr ) {
                        $eventStr = $eventStr . "<br><br>" . $taskUpdateStr;
                        $msg = $msg . "<br><br>" . $taskUpdateStr;
                    }

                    if( !$testing ) {
                        $userSecUtil->createUserEditEvent($this->getParameter('crn.sitename'), $eventStr, $user, $message, $request, $eventType);
                        $logger->notice("createUserEditEvent=".$msg);
                    }
                }

                if( !$testing ) {
                    //send an email to the Preferred Email of the "Attending:"
                    $crnUtil->sendConfirmationEmail($message, $patient, $newEncounter);
                    $logger->notice("sendConfirmationEmail");
                }

            }//if $newEncounter

            //process Attached Documents
            //$em->getRepository('AppUserdirectoryBundle:Document')->processDocuments($message->getCrnEntryMessage()); //save update

            //TODO: save Critical Result Notification Entry short info to setShortInfo($shortInfo)
            //$crnUtil->updateMessageShortInfo($message);

            if( $testing ) {
                echo "<br><br>message ID=" . $message->getId() . "; OID=". $message->getOid() . "<br>";
                foreach ($message->getPatient() as $patient) {
                    echo "patient id=" . $patient->getId() . "<br>";
                }
                foreach ($message->getEncounter() as $encounter) {
                    echo "encounter id=" . $encounter->getId() . "<br>";
                }
            }

            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->addFlash(
                'notice',
                $msg
            );

            //echo "return messageId=".$message->getId()."<br>";
            //exit('1');

            //return $this->redirect( $this->generateUrl('crn_crnentry') );
            if( $message->getId() ) {
                return $this->redirect($this->generateUrl('crn_crnentry_view', array('messageOid'=>$message->getOid(),'messageVersion'=>$message->getVersion())));
            } else {
                return $this->redirect($this->generateUrl('crn_home'));
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
    }

    /**
     * Check if a new message/encounter version already exists for provided message/entry family.
     * @Route("/entry/check-message-version", name="crn-check-message-version", methods={"GET"}, options={"expose"=true})
     */
    public function checkMessageVersionAction(Request $request)
    {
        if (false == $this->isGranted("ROLE_CRN_USER")) {
            return $this->redirect($this->generateUrl('crn-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $crnUtil = $this->container->get('crn_util');

        $messageId = trim((string)$request->get('messageId'));
        $latestNextMessageVersion = trim((string)$request->get('latestNextMessageVersion')); //next message version, that message will have after submit
        $latestNextEncounterVersion = trim((string)$request->get('latestNextEncounterVersion'));
        //echo "latestNextMessageVersion=$latestNextMessageVersion<br>";

        $encounter = null;
        $encounterVersionOk = true;
        $result = "Not OK";

        $message = $em->getRepository('AppOrderformBundle:Message')->find($messageId);
        if( !$message ) {
            throw new \Exception( "Message is not found by id ".$messageId );
        }

        $messageVersionOk = $crnUtil->isMessageVersionMatch($message,$latestNextMessageVersion);
        //echo "messageVersionOk=$messageVersionOk<br>";

        if( count($message->getEncounter()) > 0 ) {
            $encounter = $message->getEncounter()->first();
        }

        if( $encounter ) {
            //echo "encounter exists: id=".$encounter->getId()."<br>";
            $encounterVersionOk = $crnUtil->isEncounterVersionMatch($encounter,$latestNextEncounterVersion);
        }
        //echo "encounterVersionOk=$encounterVersionOk<br>";

        if( $messageVersionOk && $encounterVersionOk ) {
            $result = "OK";
            //echo "result OK!";
        } else {
            //not ok
        }
        //exit("res=".$result);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($result));
        return $response;
    }

}
