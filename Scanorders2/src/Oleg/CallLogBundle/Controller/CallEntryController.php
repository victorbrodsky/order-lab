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

namespace Oleg\CallLogBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Oleg\CallLogBundle\Form\CalllogFilterType;
use Oleg\CallLogBundle\Form\CalllogMessageType;
use Oleg\CallLogBundle\Form\CalllogNavbarFilterType;
use Oleg\OrderformBundle\Entity\CalllogEntryMessage;
use Oleg\OrderformBundle\Entity\Encounter;
use Oleg\OrderformBundle\Entity\EncounterAttendingPhysician;
use Oleg\OrderformBundle\Entity\EncounterPatfirstname;
use Oleg\OrderformBundle\Entity\EncounterPatlastname;
use Oleg\OrderformBundle\Entity\EncounterPatmiddlename;
use Oleg\OrderformBundle\Entity\EncounterPatsex;
use Oleg\OrderformBundle\Entity\EncounterPatsuffix;
use Oleg\OrderformBundle\Entity\EncounterReferringProvider;
use Oleg\OrderformBundle\Entity\EncounterReferringProviderSpecialty;
use Oleg\OrderformBundle\Entity\Endpoint;
use Oleg\OrderformBundle\Entity\Message;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\PatientDob;
use Oleg\OrderformBundle\Entity\PatientFirstName;
use Oleg\OrderformBundle\Entity\PatientLastName;
use Oleg\OrderformBundle\Entity\PatientMiddleName;
use Oleg\OrderformBundle\Entity\PatientMrn;
use Oleg\OrderformBundle\Entity\PatientSex;
use Oleg\OrderformBundle\Entity\PatientSuffix;
use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\UserdirectoryBundle\Entity\ModifierInfo;
use Oleg\UserdirectoryBundle\Entity\Spot;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CallEntryController extends Controller
{


    /**
     * Case List Page
     * @Route("/", name="calllog_home")
     *
     * Alerts: filtered case list
     * @Route("/alerts/", name="calllog_alerts")
     *
     * @Template("OlegCallLogBundle:CallLog:home.html.twig")
     */
    public function homeAction(Request $request)
    {
        if( false == $this->get('security.context')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $calllogUtil = $this->get('calllog_util');
        $route = $request->get('_route');
        $title = "Call Case List";
        $alerts = false;

        if( $request->get('_route') == "calllog_alerts" ) {
            $alerts = true;
            $title = $title . " (Alerts)";
        }

        //$messageStatuses
        $messageStatuses = $em->getRepository('OlegOrderformBundle:MessageStatusList')->findBy(array('type'=>array('default','user-added')));
        $messageStatusesChoice = array();
        foreach( $messageStatuses as $messageStatuse ) {
            $messageStatusesChoice[$messageStatuse->getId()] = $messageStatuse."";
        }
        //add: "All except deleted" [this should show all except "Deleted"], "All", "All Signed Non-Drafts" [this should show both "Signed" and "Signed, Amended"]
        //"All Drafts" [this should show these four "Draft", "Post-signature Draft", "Post-amendment Draft", "Post-deletion draft"].
        //"All post-signature drafts" [this should show these two "Post-signature Draft", "Post-amendment Draft"].
        $messageStatusesChoice["All except deleted"] = "All except deleted";
        $messageStatusesChoice["All"] = "All";
        $messageStatusesChoice["All Signed Non-Drafts"] = "All Signed Non-Drafts";
        $messageStatusesChoice["All Drafts"] = "All Drafts";
        $messageStatusesChoice["All post-signature drafts"] = "All post-signature drafts";

        //child nodes of "Pathology Call Log Entry"
        //$messageCategoryParent = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName("Encounter Note");
        $messageCategoriePathCall = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName("Pathology Call Log Entry");
        $messageCategories = array();
        if( $messageCategoriePathCall ) {
            $messageCategories = $messageCategoriePathCall->printTreeSelectListIncludingThis();
        }
        //$messageCategoriePathCall = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName("Pathology Call Log Entry");
        //$node1 = array('id'=>1,'text'=>'node1');
        //$node2 = array('id'=>2,'text'=>'node2');
        //$messageCategories = array($node1,$node2);

        $defaultMrnType = $em->getRepository('OlegOrderformBundle:MrnType')->findOneByName("New York Hospital MRN");

        $referringProviders = $calllogUtil->getReferringProviders();

        $params = array(
            'messageStatuses' => $messageStatusesChoice,
            'messageCategories' => $messageCategories,
            //'messageCategoryDefault' => $messageCategoriePathCall->getId(),
            'mrntype' => $defaultMrnType->getId(),
            'referringProviders' => $referringProviders
        );
        $filterform = $this->createForm(new CalllogFilterType($params), null);
        $filterform->bind($request);
        $messageStatusFilter = $filterform['messageStatus']->getData();
        $mrntypeFilter = $filterform['mrntype']->getData();
        $searchFilter = $filterform['search']->getData();
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $messageCategory = $filterform['messageCategory']->getData();
        $authorFilter = $filterform['author']->getData();
        $referringProviderFilter = $filterform['referringProvider']->getData();
        $encounterLocationFilter = $filterform['encounterLocation']->getData();
        $specialtyFilter = $filterform['referringProviderSpecialty']->getData();
        $patientListTitleFilter = $filterform['patientListTitle']->getData();
        $entryBodySearchFilter = $filterform['entryBodySearch']->getData();
        $attendingFilter = $filterform['attending']->getData();

        ///////////////// search in navbar /////////////////
        $navbarSearchTypes = array(
            'MRN or Last Name' => 'MRN or Last Name',
            'MRN' => 'MRN',
            'Patient Last Name' => 'Patient Last Name',
            'Message Type' => 'Message Type',
            'Entry full text' => 'Entry full text'
        );
        $params['navbarSearchTypes'] = $navbarSearchTypes;
        $navbarfilterform = $this->createForm(new CalllogNavbarFilterType($params), null);
        $navbarfilterform->bind($request);
        $calllogsearchtype = $navbarfilterform['searchtype']->getData();
        $calllogsearch = $navbarfilterform['search']->getData();
        //echo "calllogsearchtype=".$calllogsearchtype."; calllogsearch=".$calllogsearch."<br>";
        //exit('0');
        if( $calllogsearchtype == 'MRN or Last Name' ) {
            $searchFilter = $calllogsearch;
        }
        if( $calllogsearchtype == 'Entry full text' ) {
            $entryBodySearchFilter = $calllogsearch;
        }
        ///////////////// EOF search in navbar /////////////////

        if( $this->isFilterEmpty($filterform) && !$calllogsearch ) {
            return $this->redirect( $this->generateUrl('calllog_home',
                array(
                    'filter[messageStatus]'=>"All except deleted",
                    'filter[messageCategory]'=>$messageCategoriePathCall->getName().""
                )
            ) );
        }

        //perform search
        $repository = $em->getRepository('OlegOrderformBundle:Message');
        $dql = $repository->createQueryBuilder('message');
        $dql->leftJoin("message.patient","patient");
        $dql->leftJoin("patient.mrn","mrn");
        $dql->leftJoin("patient.lastname","lastname");
        $dql->leftJoin("message.encounter","encounter");

        $dql->leftJoin("encounter.referringProviders","referringProviders");
        $dql->leftJoin("referringProviders.field","referringProviderWrapper");

        $dql->leftJoin("encounter.tracker","tracker");
        $dql->leftJoin("tracker.spots","spots");
        $dql->leftJoin("spots.currentLocation","currentLocation");

        $dql->leftJoin("message.editorInfos","editorInfos");

        $dql->leftJoin("message.signeeInfo","signeeInfo");
        $dql->leftJoin("signeeInfo.modifiedBy","author");
        $dql->leftJoin("author.infos","authorInfos");

        $dql->leftJoin("message.messageCategory","messageCategory");
        //$dql->where("institution.id = ".$pathology->getId());
        $dql->orderBy("message.orderdate","DESC");
        $dql->addOrderBy("editorInfos.modifiedOn","DESC");

        //filter
        $advancedFilter = 0;
        //$defaultAdvancedFilter = false;
        $queryParameters = array();

        //use editorInfos or orderdate
//        if( $startDate || $endDate ) {
//            echo "startDate=" . $startDate->format('Y-m-d') . "<br>";
//            $dql->andWhere('message.orderdate BETWEEN :startDate and :endDate');
//            $startDateStr = "";
//            if( $startDate ) {
//                $startDateStr = $startDate->format('Y-m-d H:i:s');
//            }
//            $queryParameters['startDate'] = $startDateStr;
//            $endDateStr = "";
//            if( $endDate ) {
//                $endDateStr = $endDate->format('Y-m-d H:i:s');
//            }
//            $queryParameters['endDate'] = $endDateStr;
//        }
        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d') . "<br>";
            $dql->andWhere('message.orderdate >= :startDate');
            $queryParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
        }
        if( $endDate ) {
            //echo "endDate=" . $endDate->format('Y-m-d') . "<br>";
            $dql->andWhere('message.orderdate <= :endDate');
            $queryParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
        }

        if( $messageCategory ) {
            $messageCategoryEntity = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($messageCategory);
            if( $messageCategoryEntity ) {
                $selectOrder = false;
                $nodeChildSelectStr = $messageCategoryEntity->selectNodesUnderParentNode($messageCategoryEntity, "messageCategory",$selectOrder);
                $dql->andWhere($nodeChildSelectStr);
            }
        }

        if( $searchFilter ) {
            if ( strval($searchFilter) != strval(intval($searchFilter)) ) {
                //echo "string $searchFilter<br>";
                //$dql->andWhere("mrn.field LIKE :search OR lastname.field LIKE :search OR message.messageTitle LIKE :search OR authorInfos.displayName LIKE :search OR messageCategory.name LIKE :search");
                $dql->andWhere("lastname.field LIKE :search");
                $queryParameters['search'] = "%".$searchFilter."%";
            } else {
                //echo "integer $searchFilter<br>";
                $dql->andWhere("mrn.field = :search");
                $queryParameters['search'] = $searchFilter;

                if( $mrntypeFilter ) {
                    $dql->andWhere("mrn.keytype = :keytype");
                    $queryParameters['keytype'] = $mrntypeFilter;
                }
            }
        }

        //This single filter should work in the "OR" mode for these three fields: Submitter, Signee, Editor
        //Don't use: Referring Provider - encounter->referringProviders[]->field(userWrapper)->user(User)
        //encounter-provider (User)
        //Don't use: message-provider (User)
        //message-signeeInfo(ModifierInfo)-modifiedBy(User)
        //message-editorInfos(ModifierInfo)-modifiedBy(User)
        // (meaning if the selected user shows up in any of these three fields of the message/entry, show this message/entry.)
        if( $authorFilter ) {
            $authorStr = "encounter.provider=:author OR signeeInfo.modifiedBy=:author OR editorInfos.modifiedBy=:author";
            $dql->andWhere($authorStr);
            $queryParameters['author'] = $authorFilter;
            $advancedFilter++;
        }

        if( $attendingFilter ) {
            //messagetype_patient_0_encounter_1_attendingPhysicians_0_field
            $dql->leftJoin("encounter.attendingPhysicians","attendingPhysicians");
            $dql->leftJoin("attendingPhysicians.field","attendingPhysicianWrapper");
            $attendingStr = "attendingPhysicianWrapper.user=:attendingPhysician";
            $dql->andWhere($attendingStr);
            $queryParameters['attendingPhysician'] = $attendingFilter;
            $advancedFilter++;
        }

        if( $referringProviderFilter ) {
            if ( strval($referringProviderFilter) != strval(intval($referringProviderFilter)) ) {
                //echo "string (wrapper name)=[$referringProviderFilter]<br>";
                $referringProviderStr = "referringProviderWrapper.name=:referringProvider";
                $dql->andWhere($referringProviderStr);
                $queryParameters['referringProvider'] = $referringProviderFilter;
            } else {
                //echo "integer (user id)=[$referringProviderFilter]<br>";
                $referringProviderStr = "referringProviderWrapper.user=:referringProvider";
                $dql->andWhere($referringProviderStr);
                $queryParameters['referringProvider'] = $referringProviderFilter;
            }

            $advancedFilter++;
        }

        //encounter_1_referringProviders_0_referringProviderSpecialty
        if( $specialtyFilter ) {
            $specialtyStr = "referringProviders.referringProviderSpecialty=:referringProviderSpecialty";
            $dql->andWhere($specialtyStr);
            $queryParameters['referringProviderSpecialty'] = $specialtyFilter;
            $advancedFilter++;
        }

        //encounter_1_tracker_spots_0_currentLocation
        if( $encounterLocationFilter ) {
            $encounterLocationStr = "currentLocation=:encounterLocation";
            $dql->andWhere($encounterLocationStr);
            $queryParameters['encounterLocation'] = $encounterLocationFilter;
            $advancedFilter++;
        }
        //messageStatus
        //$messageStatusFilter = $filterform['messageStatus']->getData();
//        if( !$messageStatusFilter ) {
//            $messageStatusFilter = "All except deleted";
//        }
        if( $messageStatusFilter ) {
            $advancedFilter++;
            if ( strval($messageStatusFilter) != strval(intval($messageStatusFilter)) ) {
                //echo "string=[$messageStatusFilter]<br>";
                $messageStatusStr = null;
                $dql->leftJoin("message.messageStatus","messageStatus");
                // "All except deleted" [this should show all except "Deleted"],
                if( $messageStatusFilter == "All except deleted" ) {
                    $messageStatusStr = "messageStatus.name != :deletedMessageStatus";
                    $queryParameters['deletedMessageStatus'] = "Deleted";
                    //$defaultAdvancedFilter = true;
                    //if( $advancedFilter === false ) {
                        //$advancedFilter = false;
                    //}
                    $advancedFilter--;
                }
                // "All"
                if( $messageStatusFilter == "All" ) {
                }
                // "All Signed Non-Drafts" [this should show both "Signed" and "Signed, Amended"]
                if( $messageStatusFilter == "All Signed Non-Drafts" ) {
                    $messageStatusStr = "messageStatus.name = :signedMessageStatus OR messageStatus.name = :signedAmendedMessageStatus";
                    $queryParameters['signedMessageStatus'] = "Signed";
                    $queryParameters['signedAmendedMessageStatus'] = "Signed, Amended";
                }
                // "All Drafts" [this should show these four "Draft", "Post-signature Draft", "Post-amendment Draft", "Post-deletion draft"].
                if( $messageStatusFilter == "All Drafts" ) {
                    $messageStatusStr = "messageStatus.name = :DraftMessageStatus OR messageStatus.name = :PostSignatureDraftMessageStatus";
                    $queryParameters['DraftMessageStatus'] = "Draft";
                    $queryParameters['PostSignatureDraftMessageStatus'] = "Post-signature Draft";
                    $messageStatusStr = $messageStatusStr . " OR messageStatus.name = :PostAmendmentDraftMessageStatus OR messageStatus.name = :PostDeletionDraftMessageStatus";
                    $queryParameters['PostAmendmentDraftMessageStatus'] = "Post-amendment Draft";
                    $queryParameters['PostDeletionDraftMessageStatus'] = "Post-deletion Draft";
                }
                // "All post-signature drafts" [this should show these two "Post-signature Draft", "Post-amendment Draft"].
                if( $messageStatusStr == "All post-signature drafts" ) {
                    $messageStatusStr = "messageStatus.name = :PostSignatureDraftMessageStatus OR messageStatus.name = :PostAmendmentDraftMessageStatus";
                    $queryParameters['PostSignatureDraftMessageStatus'] = "Post-signature Draft";
                    $queryParameters['PostAmendmentDraftMessageStatus'] = "Post-amendment Draft";
                }
                if( $messageStatusStr ) {
                    //echo "string: $messageStatusStr<br>";
                    $dql->andWhere($messageStatusStr);
                }
            } else {
                //echo "integer=$messageStatusFilter<br>";
                $messageStatusStr = "message.messageStatus=:messageStatus";
                $dql->andWhere($messageStatusStr);
                $queryParameters['messageStatus'] = $messageStatusFilter;
            }
        } else {
            //
        }

        //patientListTitle: Selecting the list should filter the shown entries/messages to only those that belong to patients currently on this list.
        if( $patientListTitleFilter ) {
            $dql->leftJoin("message.calllogEntryMessage","calllogEntryMessage");
            $dql->leftJoin("calllogEntryMessage.patientList","patientList");
            //show message if the message's patient has been removed from the patient list (disabled)?
            $patientListEntityStr = "patientList=:patientList";
            $dql->andWhere($patientListEntityStr);
            $queryParameters['patientList'] = $patientListTitleFilter;

            $advancedFilter++;
        }

        //"Entry Body": The value entered in this field should be searched for in the "History/Findings" and "Impression/Outcome" fields
        // (with an "OR" - a match in either one should list the entry).
        if( $entryBodySearchFilter ) {
            //find ObjectTypeText with value=$entryBodySearchFilter AND entityName="Message"
            $entryBodySearchStr = "SELECT s FROM OlegUserdirectoryBundle:ObjectTypeText s WHERE ".
              "(message.id = s.entityId AND s.entityName='Message' AND s.value LIKE :entryBodySearch)";
            $dql->andWhere("EXISTS (".$entryBodySearchStr.")");
            $queryParameters['entryBodySearch'] = "%".$entryBodySearchFilter."%";
            $advancedFilter++;
        }

        ///////////////// search in navbar /////////////////
        if( $calllogsearchtype && $calllogsearch ) {
            if( $calllogsearchtype == 'MRN or Last Name' ) {
                //use regular filter by replacing an appropriate filter string
            }
            if( $calllogsearchtype == 'MRN' ) {
                $dql->andWhere("mrn.field = :search");
                $queryParameters['search'] = $calllogsearch;
            }
            if( $calllogsearchtype == 'Patient Last Name' ) {
                $dql->andWhere("lastname.field LIKE :search");
                $queryParameters['search'] = "%".$calllogsearch."%";
            }
            if( $calllogsearchtype == 'Message Type' ) {
                $messageCategoryEntity = $em->getRepository('OlegOrderformBundle:MessageCategory')->find($calllogsearch);
                if( $messageCategoryEntity ) {
                    $nodeChildSelectStr = $messageCategoryEntity->selectNodesUnderParentNode($messageCategoryEntity, "messageCategory",$selectOrder);
                    $dql->andWhere($nodeChildSelectStr);
                } else {
                    $dql->andWhere("1=0");
                }
            }
            if( $calllogsearchtype == 'Entry full text' ) {
                //use regular filter by replacing an appropriate filter string
            }
            //exit("1 [$calllogsearchtype] : [$calllogsearch]");
        }
        //exit('2');
        ///////////////// EOF search in navbar /////////////////


        //$query = $em->createQuery($dql);
        //$messages = $query->getResult();

        $limit = 10;
        $query = $em->createQuery($dql);
        $query->setParameters($queryParameters);

        //echo "query=".$query->getSql()."<br>";

        $paginator  = $this->get('knp_paginator');
        $messages = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            //$request->query->getInt('page', 1),
            $limit      /*limit per page*/
        );
        //echo "messages count=".count($messages)."<br>";

        //all messages will show only form fields for this message category node
//        $categoryStr = "Pathology Call Log Entry";
//        $messageCategoryInfoNode = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
//        if( !$messageCategoryInfoNode ) {
//            throw new \Exception( "MessageCategory type is not found by name '".$categoryStr."'" );
//        }

        $eventObjectType = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->findOneByName("Message");

        $defaultPatientListId = null;
        $defaultPatientList = $calllogUtil->getDefaultPatientList();
        if( $defaultPatientList ) {
            $defaultPatientListId = $defaultPatientList->getId();
        }

        return array(
            'messages' => $messages,
            'alerts' => $alerts,
            'title' => $title,
            'filterform' => $filterform->createView(),
            'route_path' => $route,
            'advancedFilter' => $advancedFilter,
            //'messageCategoryInfoNode' => $messageCategoryInfoNode, //all messages will show only form fields for this message category node
            'eventObjectTypeId' => $eventObjectType->getId(),
            'patientListId' => $defaultPatientListId,
            'shownavbarfilter' => false
            //'navbarfilterform' => $navbarfilterform->createView()
            //'sitename' => $this->container->getParameter('calllog.sitename')
            //'calllogsearch' => $calllogsearch,
            //'calllogsearchtype' => $calllogsearchtype,
        );

    }
    public function isFilterEmpty($filterform) {
        //print_r($filterform->getData());
        foreach( $filterform->getData() as $key=>$value ) {
            if( $value ) {
                return false;
            }
        }
        return true;
    }


    /**
     * Call Entry
     * http://localhost/order/call-log-book/entry/new?mrn-type=4&mrn=3
     * @Route("/entry/new", name="calllog_callentry")
     * @Template("OlegCallLogBundle:CallLog:call-entry.html.twig")
     */
    public function callEntryAction(Request $request)
    {
        if( false == $this->get('security.context')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //1) search box: MRN,Name...

        $user = $this->get('security.context')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $calllogUtil = $this->get('calllog_util');
        $userSecUtil = $this->get('user_security_utility');
        $orderUtil = $this->get('scanorder_utility');
        $em = $this->getDoctrine()->getManager();

        $mrn = trim($request->get('mrn'));
        $mrntype = trim($request->get('mrn-type'));
        $encounterNumber = trim($request->get('encounter'));
        $encounterTypeId = trim($request->get('encounter-type'));
        $messageTypeId = trim($request->get('message-type'));

        //check if user has at least one institution
        $securityUtil = $this->get('order_security_utility');
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

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $cycle = 'new';
        $formtype = 'call-entry';

        $institution = $userSecUtil->getCurrentUserInstitution($user);

        //create patient
        $patient = new Patient(true,'valid',$user,$system);
        $patient->setInstitution($institution);

        //set patient record status "Active"
        $patientActiveStatus = $em->getRepository('OlegOrderformBundle:PatientRecordStatusList')->findOneByName("Active");
        if( $patientActiveStatus ) {
            $patient->setPatientRecordStatus($patientActiveStatus);
        }

        //create invalid encounter #1 just to display fields in "Patient Info"
        $encounter1 = new Encounter(true,'invalid',$user,$system);
        $encounter1->setProvider($user);
        $patient->addEncounter($encounter1); //add new encounter to patient

        $readonlyEncounter = true;
        $encounter2 = $em->getRepository('OlegOrderformBundle:Encounter')->findOneEncounterByNumberAndType($encounterTypeId,$encounterNumber);
        //echo "Found encounter=".$encounter2."<br>";

        //check whether patient MRN supplied in the URL corresponds to the supplied encounter number.
        // If it does not, show the normal /entry/new page but with the notification "
        // Encounter "1111" of type "blah" is not with patient whose MRN of type "whatever" is "1111"
        if( $mrn && $mrntype && $encounter2 ) {
            if( !$em->getRepository('OlegOrderformBundle:Encounter')->isPatientEncounterMatch($mrn,$mrntype,$encounter2) ) {

                $mrntypeStr = "";
                $mrntypeEntity = $em->getRepository('OlegOrderformBundle:MrnType')->find($mrntype);
                if( $mrntypeEntity ) {
                    $mrntypeStr = $mrntypeEntity->getName()."";
                }

                $encounterMsg = "Encounter $encounterNumber of type ".$encounter2->obtainEncounterNumber()." is not with patient whose MRN of type $mrntypeStr is $mrn";
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    $encounterMsg
                );

                $encounter2 = null;
            }
        }

        if( !$encounter2 ) {
            //echo "Create new encounter <br>";
            //create encounter #2 to display in "Encounter Info" -> "Update Patient Info"
            $encounter2 = new Encounter(true, 'valid', $user, $system);
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
            $encounter2 = $em->getRepository('OlegOrderformBundle:Encounter')->setEncounterKey($key, $encounter2, $user);

            //set encounter date and time
            $date = $encounter2->getDate()->first();
            $userTimeZone = $user->getPreferences()->getTimezone();
            $nowDate = new \DateTime("now", new \DateTimeZone($userTimeZone));
            $date->setField($nowDate);
            $date->setTime($nowDate);

            //set encounter status "Open"
            $encounterOpenStatus = $em->getRepository('OlegOrderformBundle:EncounterStatusList')->findOneByName("Open");
            if ($encounterOpenStatus) {
                $encounter2->setEncounterStatus($encounterOpenStatus);
            }

            //set encounter info type to "Call to Pathology"
            $encounterInfoType = $em->getRepository('OlegOrderformBundle:EncounterInfoTypeList')->findOneByName("Call to Pathology");
            if ($encounterInfoType) {
                if (count($encounter2->getEncounterInfoTypes()) > 0) {
                    $encounter2->getEncounterInfoTypes()->first()->setField($encounterInfoType);
                }
            }

            //testing
            //echo "next key=".$calllogUtil->getNextEncounterGeneratedId()."<br>";
            //$calllogUtil->checkNextEncounterGeneratedId();
            //testing
            //$userFormNodeUtil = $this->get('user_formnode_utility');
            //$formNodeTest = $em->getRepository('OlegUserdirectoryBundle:FormNode')->findOneByName("Blood Product Transfused");
            //$values = $userFormNodeUtil->getDropdownValue($formNodeTest);
            //print_r($values);
            //exit('1');

            //create a new spot and add it to the encounter's tracker
            $withdummyfields = true;
            //$locationTypePrimary = null;
            $encounterLocationType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Encounter Location");
            if (!$encounterLocationType) {
                throw new \Exception('Location type is not found by name Encounter Location');
            }
            $locationName = null;   //""; //"Encounter's Location";
            $spotEntity = null;
            $removable = 0;
            $encounter2->addContactinfoByTypeAndName($user, $system, $encounterLocationType, $locationName, $spotEntity, $withdummyfields, $em, $removable);
            $readonlyEncounter = false;
        }

        //add new encounter to patient
        $patient->addEncounter($encounter2);

        $message = $this->createCalllogEntryMessage($user,$permittedInstitutions,$system,$messageTypeId);

        //set patient list
        $patientList = $calllogUtil->getDefaultPatientList();
        //echo "patientList ID=".$patientList->getId()."<br>";
        $message->getCalllogEntryMessage()->setPatientList($patientList);

        //add patient
        $message->addPatient($patient);
        //add encounter
        $message->addEncounter($encounter2);
        ///////////// EOF Message //////////////

        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle,$readonlyEncounter);

        //$encounterid = $calllogUtil->getNextEncounterGeneratedId();

        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'formtype' => $formtype,
            'triggerSearch' => 0,
            'mrn' => $mrn,
            'mrntype' => $mrntype,
            'titleheadroom' => null
            //'readonlyEncounter' => $readonlyEncounters
            //'encounterid' => $encounterid
        );
    }

    /**
     * Save/Update Call Log Entry
     * @Route("/entry/update", name="calllog_update_entry", options={"expose"=true})
     * @Template("OlegCallLogBundle:CallLog:call-entry.html.twig")
     * @Method("POST")
     */
    public function updateEntryAction(Request $request)
    {
        if( false == $this->get('security.context')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //exit('update entry');
        //case 1: patient exists: create a new encounter to DB and add it to the existing patient
        //add patient id field to the form (id="oleg_calllogbundle_patienttype_id") or use class="calllog-patient-id" input field.
        //case 2: patient does not exists: create a new encounter to DB

        $user = $this->get('security.context')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $userSecUtil = $this->get('user_security_utility');
        $orderUtil = $this->get('scanorder_utility');
        $calllogUtil = $this->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

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

//        $mrn = trim($request->get('mrn'));
//        $mrntype = trim($request->get('mrn-type'));
        $mrn = null;
        $mrntype = null;

        $title = "Update Entry";

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $status = 'valid';
        $cycle = 'new';
        $formtype = 'call-entry';

        $message = $this->createCalllogEntryMessage($user,$permittedInstitutions,$system);

        //add patient
        //$message->addPatient($patient);

        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle);

        $form->handleRequest($request);

//        echo "loc errors:<br>";
//        print_r($form->getErrors());
//        echo "<br>loc string errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";

//        $errorHelper = new ErrorHelper();
//        $errors = $errorHelper->getErrorMessages($form);
//        echo "<br>form errors:<br>";
//        print_r($errors);
        //exit();

        //oleg_calllogbundle_patienttype[id]
        //$formPatientId = $form["id"]->getData();
        //echo "1: formPatientId=".$formPatientId."<br>";

        //oleg_calllogbundle_patienttype[encounter][0][patfirstname][0][field]
        //$formPatientDob = $form["dob"][0]->getData();
        //echo "1: formPatientDob=".$formPatientDob."<br>";
        //print_r($formPatientDob);

        //$referringProviderPhone = $form["encounter"][0]['referringProviders'][0]['referringProviderPhone']->getData();
        //$formPatientId = $request->request->get('patient');
        //$formPatientId = $form["id"]->getData();
        //echo "1: form referringProviderPhone=".$referringProviderPhone."<br>";
        //$data = $form->getData();
        //print_r($data);

//        if( $form->isSubmitted() ) {
//            echo "form is submitted <br>";
//        }
//        if( $form->isValid() ) {
//            echo "form is valid <br>";
//        }

        //if( $form->isSubmitted() && $form->isValid() ) {
        if( $form->isSubmitted() ) {

            //$data = $form->getData();
//            $data = $request->request->all();
//            print "<pre>";
//            print_r($data);
//            print "</pre>";
//            $unmappedField = $data["formnode-4"];
//            echo "<br>unmappedField=".$unmappedField."<br>";
//            //print_r($request->get("form"));
//            //exit('form is valid');

            $msg = "No Case found. No action has been performed.";
            $institution = $userSecUtil->getCurrentUserInstitution($user);

            $patients = $message->getPatient();
            if( count($patients) != 1 ) {
                throw new \Exception( "Message must have only one patient. Patient count= ".count($patients)."'" );
            }
            $patient = $patients->first();
            //echo "patient id=".$patient->getId()."<br>";

            $patientInfoEncounter = null;
            $newEncounter = null;
            //get a new encounter without id
            foreach( $patient->getEncounter() as $encounter ) {
                //echo "encounter ID=".$encounter->getId()."; status=".$encounter->getStatus()."<br>";
                if( !$encounter->getId() ) {
                    if( $encounter->getStatus() == 'valid' ) {
                        $newEncounter = $encounter;
                    }
                    if( $encounter->getStatus() == 'invalid' ) {
                        //this encounter is served only to find the patient:
                        //copy all non-empty values from the $patientInfoEncounter to the $newEncounter
                        //it must be removed from the patient
                        $patientInfoEncounter = $encounter;
                    }
                }
            }

            //set system source and user's default institution
            if( $newEncounter ) {

                $newEncounter->setSource($system);
                $newEncounter->setInstitution($institution);

                //assign generated encounter number ID
                $key = $newEncounter->obtainAllKeyfield()->first();
                $em->getRepository('OlegOrderformBundle:Encounter')->setEncounterKey($key, $newEncounter, $user);

                //Remove tracker if spots/location is empty
                $tracker = $newEncounter->getTracker();
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
                //exit();

                //TODO: just keep timezone as DB field and show it in the encounter Date
                //re-set encounter date according to the unmapped timezone
//                $encounterDateObject = $newEncounter->getDate()->first();
//                $encounterDate = $encounterDateObject->getField();
//                echo "date1=".$encounterDate->format('Y-m-d H:i')."<br>";
//                $encounterDateTimezone = $encounterDateObject->getTimezone();
//                echo "encounterDateTimezone=$encounterDateTimezone <br>";
//                $encounterDate = $encounterDate->setTimezone(new \DateTimeZone($encounterDateTimezone));
//                echo "date2=".$encounterDate->format('Y-m-d H:i')."<br>";
//                $encounterDateObject->setField($encounterDate);
//                //exit('1');

                //TODO: Update Patient Info from $newEncounter (?):
                // The values typed into these fields should be recorded as "valid".
                // If the user types in the Date of Birth, it should be added to the "Patient" hierarchy level
                // of the selected patient as a "valid" value and the previous "valid" value should be marked "invalid" on the server side.
                //Use unmapped encounter's "patientDob" to update patient's DOB

                if( $patientInfoEncounter ) {
                    //TODO: copy all non-empty values from the $patientInfoEncounter to the $newEncounter ?

                    //If the user types in the Date of Birth, it should be added to
                    // the "Patient" hierarchy level of the selected patient as a "valid" value
                    // and the previous "valid" value should be marked "invalid" on the server side.
                    //Use unmapped encounter's "patientDob" to update patient's DOB
                    //$patientDob = $form['patientDob']->getData();
                    //echo "patientDob=$patientDob <br>";

                    //$patientInfoEncounter must be removed from the patient
                    $patient->removeEncounter($patientInfoEncounter);
                }

                //prevent creating a new location every time: if location id is provided => find location in DB and replace it with tracker->spot->location
                $calllogUtil->processTrackerLocation($newEncounter);
                //exit('after location');

                //TODO: process EncounterReferringProvider: set Specialty, Phone and Email for a new userWrapper (getReferringProviders)
                $calllogUtil->processReferringProviders($newEncounter,$system);

                //testing: process form nodes
                //$formNodeUtil = $this->get('user_formnode_utility');
                //$formNodeUtil->processFormNodes($request,$message->getMessageCategory(),$message);
                //exit('after formnode');


                //clear encounter
                $message->clearEncounter();
                //add encounter to the message
                $message->addEncounter($newEncounter);

                //set message status from the form's name="messageStatus" field
                $data = $request->request->all();
                $messageStatusForm = $data['messageStatusJs'];
                //echo "messageStatusForm=".$messageStatusForm."<br>";
                if( $messageStatusForm ) {
                    $messageStatusObj = $em->getRepository('OlegOrderformBundle:MessageStatusList')->findOneByName($messageStatusForm);
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

                    }
                }


                if( $message->getMessageCategory() ) {

                    //message title setMessageTitle: show the title of the form (not the message type) here, not just its ID
                    $messageTitle = $message->getMessageTitleStr();
                    $message->setMessageTitle($messageTitle);
                }

                //On the server side write in the "Versions" of the associated forms into this "Form Version" field in the same order as the Form titles+IDs
                $calllogUtil->setFormVersions($message);


                ///////// Issue 51(6): check if patient info is entered but Find Patient is not pressed  ///////
                //Don't use since it's not possible to restore original entry
//                if( $patient && !$patient->getId() ) {
//                    //$patient
//                    $patientParams = array();
//                    //oleg_calllogformbundle_messagetype[patient][0][mrn][0][field]
//                    $data = $request->request->all();
//                    $mrn1 = $data['oleg_calllogformbundle_messagetype[patient][0][mrn][0][field]'];
//                    $mrn2 = $request->query->get('oleg_calllogformbundle_messagetype[patient][0][mrn][0][field]');
//                    echo "mrn1=".$mrn1."; mrn2=".$mrn2."<br>";
//                    //$mrntype = $data['oleg_calllogformbundle_messagetype[patient][0][mrn][0][keytype]'];
//                    //echo "mrn:".$mrn."; ".$mrntype."<br>";
////                    $mrnObject = $patient->getMrn()->first();
////                    echo "mrn count=".count($patient->getMrn())."<br>";
////                    if( $mrn ) {
////                        $mrntype = $mrnObject->getKeytype();
////                        $mrn = $mrnObject->getField();
////                        $params['mrntype'] = $mrntype;
////                        $params['mrn'] = $mrn;
////                        echo "mrn:".$mrn."; ".$mrntype."<br>";
////                    }
////                    $dob = $patient->getDob()->first();
////                    $params['dob'] = $dob;
//                    //$patientInfoEncounter
//                    $lastname = $patientInfoEncounter->getPatlastname()->first();
//                    $firstname = $patientInfoEncounter->getPatfirstname()->first();
//                    $params['lastname'] = $lastname;
//                    $params['firstname'] = $firstname;
//
//                    $patientsData = $this->searchPatient($request, false, $patientParams); //submit new entry
//                    $patients = $patientsData['patients'];
//                    echo "found patients=".count($patients)."<br>";
//
////                    return array(
////                        //'entity' => $entity,
////                        'form' => $form->createView(),
////                        'cycle' => $cycle,
////                        'title' => $title,
////                        'formtype' => $formtype,
////                        'triggerSearch' => 0,
////                        'mrn' => $mrn,
////                        'mrntype' => $mrntype
////                    );
//                }
                ///////// EOF Issue 51(6): check if patient info is entered but Find Patient is not pressed ///////


                if( $patient->getId() ) {
                    //CASE 1
                    echo "case 1: patient exists: create a new encounter to DB and add it to the existing patient <br>";
                    //get a new encounter without id $newEncounter
    //                foreach( $encounter->getReferringProviders() as $referringProvider ) {
    //                    echo "encounter referringProvider phone=".$referringProvider->getReferringProviderPhone()."<br>";
    //                }

                    $patient = $em->getRepository('OlegOrderformBundle:Patient')->find($patient->getId());
                    $message->clearPatient();
                    $message->addPatient($patient);

                    //reset institution from the patient
                    $newEncounter->setInstitution($patient->getInstitution());

                    $patient->addEncounter($newEncounter);

                    //add new DOB (if exists) to the Patient
                    //Use unmapped encounter's "patientDob" to update patient's DOB
//                    if( $newEncounter->getPatientDob() ) {
//                        //invalidate all other patient's DOB
//                        $validDOBs = $patient->obtainStatusFieldArray("dob","valid");
//                        foreach( $validDOBs as $validDOB) {
//                            $validDOB->setStatus("invalid");
//                        }
//
//                        $patientDob = $newEncounter->getPatientDob();
//                        //echo "encounter patientDob=" . $patientDob->format('Y-m-d') . "<br>";
//                        $newPatientDob = new PatientDob($status,$user,$system);
//                        $newPatientDob->setField($patientDob);
//                        $patient->addDob($newPatientDob);
//                        //echo "patient patientDob=" . $newPatientDob . "<br>";
//                    }
                    $calllogUtil->updatePatientInfoFromEncounter($patient,$newEncounter,$user,$system );

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
                        $em->persist($newEncounter);
                        $em->persist($message);
                        $em->flush(); //testing
                    }


                    //add patient to the complex patient list specified by patientListTitle if the option addPatientToList is checked.
                    //do it after message is in DB and has ID
                    $calllogUtil->addToPatientList($patient,$message,$testing);

                    $msg = "New Encounter (ID#" . $newEncounter->getId() . ") is created with number " . $newEncounter->obtainEncounterNumber() . " for the Patient with ID #" . $patient->getId();

                } else {
                    //CASE 2
                    echo "case 2: patient does not exists: create a new encounter to DB <br>";
                    //oleg_calllogbundle_patienttype[encounter][0][referringProviders][0][referringProviderPhone]

                    $newEncounter->setPatient(null);

                    //remove empty patient from message
                    $message->removePatient($patient);

                    //exit('Exit Case 2');
                    if( !$testing ) {
                        $em->persist($newEncounter);
                        $em->flush($newEncounter); //testing

                        $em->persist($message);
                        $em->flush($message); //testing
                    }

                    $msg = "New Encounter (ID#" . $newEncounter->getId() . ") is created with number " . $newEncounter->obtainEncounterNumber();
                }

                //set encounter as message's input
                //$message->addInputObject($newEncounter);
                //$em->persist($message);
                //$em->flush($message);

                //process form nodes
                $formNodeUtil = $this->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request,$message->getMessageCategory(),$message,$testing); //testing
                //exit('after formnode');


                //log search action
                if( $msg ) {
                    $eventType = "New Call Log Book Entry Submitted";

                    $event = $calllogUtil->getEventLogDescription($message,$patient,$newEncounter);
                    //exit('event='.$event);

                    //$event = $event . " submitted by " . $user;

                    if( !$testing ) {
                        $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $event, $user, $message, $request, $eventType);
                    }
                }

                if( !$testing ) {
                    //send an email to the Preferred Email of the "Attending:"
                    $calllogUtil->sendConfirmationEmail($message, $patient, $newEncounter);
                }

            }//if $newEncounter


            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            //echo "return messageId=".$message->getId()."<br>";
            //exit('1');

            //return $this->redirect( $this->generateUrl('calllog_callentry') );
            if( $message->getId() ) {
                return $this->redirect($this->generateUrl('calllog_callentry_view', array('messageId' => $message->getId())));
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
    }

    public function createCalllogEntryForm($message, $mrntype=null, $mrn=null, $cycle, $readonlyEncounter=false) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        //$patient = $message->getPatient()->first();

        ////////////////////////
//        $query = $em->createQueryBuilder()
//            ->from('OlegOrderformBundle:MrnType', 'list')
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
            $mrntype = 1;
        }

        if( $cycle == 'show' ) {
            $disabled = true;
        } else {
            $disabled = false;
        }

        //$timezones
        $userTimeZone = $user->getPreferences()->getTimezone();

        $params = array(
            'cycle' => $cycle,  //'new',
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            'type' => null,
            'mrntype' => intval($mrntype),
            'mrn' => $mrn,
            'formtype' => 'call-entry',
            'complexLocation' => false,
            'alias' => true,
            'timezoneDefault' => $userTimeZone,
            'readonlyEncounter' => $readonlyEncounter,
            'attendingPhysicians-readonly' => false,
            'referringProviders-readonly' => false
        );

        $form = $this->createForm(
            new CalllogMessageType($params, $message),
            $message,
            array(
                'disabled' => $disabled
            )
        );

        return $form;
    }

    public function createCalllogEntryMessage($user,$permittedInstitutions,$system,$messageCategoryId=null) {
        $em = $this->getDoctrine()->getManager();
        $orderUtil = $this->get('scanorder_utility');

        $message = new Message();
        $message->setPurpose("For Internal Use by WCMC Department of Pathology for Call Log Book");
        $message->setProvider($user);
        $message->setVersion('1');

        //set Source object
        $source = new Endpoint();
        $source->setSystem($system);
        $message->addSource($source);

        //set order category
        if( $messageCategoryId ) {
            $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->find($messageCategoryId);
        } else {
            $categoryStr = "Pathology Call Log Entry";
            //$categoryStr = "Nesting Test"; //testing
            $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
        }
        if( !$messageCategory ) {
            throw new \Exception( "Location type is not found by name '".$categoryStr."'" );
        }
        $message->setMessageCategory($messageCategory);

        //set Institutional PHI Scope
        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,$message);
        $message->setInstitution($permittedInstitutions->first());

        //set message status "Draft"
        $messageStatus = $em->getRepository('OlegOrderformBundle:MessageStatusList')->findOneByName("Draft");
        if( $messageStatus ) {
            $message->setMessageStatus($messageStatus);
        }

        $calllogEntryMessage = $message->getCalllogEntryMessage();
        if (!$calllogEntryMessage) {
            $calllogEntryMessage = new CalllogEntryMessage();
            $message->setCalllogEntryMessage($calllogEntryMessage);
        }

        //add patient
        //$message->addPatient($patient);

        return $message;
    }

    /**
     * NOT USED (search is displayed in the home page)
     * Search Call Entry
     * @Route("/callentry/search", name="calllog_search_callentry")
     * @Method("GET")
     * @Template()
     */
    public function searchCallEntryAction(Request $request)
    {
        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $entities = null;

        $allgets = $request->query->all();
        //$patientid = trim( $request->get('patientid') );
        //print_r($allgets);
        //echo "<br>";

        $searchtype = null;
        $search = null;

        foreach( $allgets as $thiskey => $thisvalue ) {
            $searchtype = $thiskey;
            $search = $thisvalue;
            break;
        }

        $searchtype = str_replace("_"," ",$searchtype);

        //$searchtype = trim( $request->get('searchtype') );
        //$search = trim( $request->get('search') );
        //echo "searchtype=".$searchtype."<br>";
        //echo "search=".$search."<br>";

        if( $searchtype != "" && $search != "" ) {

//            $searchUtil = $this->get('search_utility');
//            $object = 'patient';
//            $params = array('request'=>$request,'object'=>$object,'searchtype'=>$searchtype,'search'=>$search,'exactmatch'=>false);
//            $res = $searchUtil->searchAction($params);
//            $entities = $res[$object];
            $entities = null;
        }


        //echo "entities count=".count($entities)."<br>";

        return $this->render('OlegCallLogBundle:CallLog:home.html.twig', array(
            'patientsearch' => $search,
            'patientsearchtype' => $searchtype,
            'patiententities' => $entities,
        ));
    }

    /**
     * Search Patient
     * @Route("/patient/search", name="calllog_search_patient", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function patientSearchAction(Request $request)
    {
        if (false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $calllogUtil = $this->get('calllog_util');
        $searchedArr = array();

        //$currentUrl = trim($request->get('currentUrl'));
        //echo "currentUrl=".$currentUrl."<br>";

        $formtype = trim($request->get('formtype'));

        $patientsData = $this->searchPatient( $request, true );
        $patients = $patientsData['patients'];
        $searchedStr = $patientsData['searchStr'];
        $searchedArr[] = "(Searched for ".$searchedStr.")";
        //echo "patients=".count($patients)."<br>";

        if( count($patients) == 0 ) {
            $params = array();
            $mrntype = trim($request->get('mrntype'));
            $mrn = trim($request->get('mrn'));
            $params['mrntype'] = $mrntype;
            $params['mrn'] = $mrn;
            $patientsDataStrict = $this->searchPatient( $request, true, $params );
            $patientsStrict = $patientsDataStrict['patients'];
            //$searchedStrStrict = $patientsDataStrict['searchStr'];
            foreach( $patientsStrict as $patientStrict ) {
                $mrnRes = $patientStrict->obtainStatusField('mrn', "valid");
                $mrntypeStrict = $mrnRes->getKeytype();
                $mrnStrict = $mrnRes->getField();
                //MRN 001 of MRN type NYH MRN appears to belong to a patient with a last name of LLL, first name of FFFF, and a MM/DD/YYYY date of birth.
                $patientInfoStrict = $patientStrict->obtainPatientInfoShort();
                $searchedArr[] = "<br>MRN $mrnStrict of MRN type $mrntypeStrict appears to belong to a patient $patientInfoStrict";
            }
        }

        $patientsArr = array(); //return json data
        //$status = 'valid';
        //$fieldnameArr = array('patlastname','patfirstname','patmiddlename','patsuffix','patsex');

        foreach( $patients as $patient ) {

            $patientId = $patient->getId();
            //echo "<br>found patient=".$patient->getId()."<br>";

            //add all merged patients to the master patient
            $mergedPatients = $calllogUtil->getAllMergedPatients( array($patient) );
            //echo "mergedPatients count=" . count($mergedPatients) . "<br>";

//            foreach( $mergedPatients as $mergedPatient ) {
//                echo "merged Patient=" . $mergedPatient->getId() . "<br>";
//
//                if( $mergedPatient->isMasterMergeRecord() ) {
//                    $masterPatientId = $mergedPatient->getId();
//                    echo "master=" . $masterPatientId . "<br>";
//
//                }
//
//            }//foreach $mergedPatient
//            //exit('1');

            $masterPatient = $calllogUtil->getMasterRecordPatients($mergedPatients);

            if( $masterPatient ) {

                $patientId = $masterPatient->getId();
                $masterPatientId = $masterPatient->getId();
                //echo "###masterPatientId=" . $masterPatientId . "<br>";

                $patientInfo = array();

                $mergedPatientsInfo = array();
                $mergedPatientsInfo[$masterPatientId] = array();

                foreach( $mergedPatients as $mergedPatient ) {
                    //echo "merged Patient=" . $mergedPatient->getId() . "<br>";

                    //first iteration: first create master record $patientInfo
                    if( $masterPatientId == $mergedPatient->getId() ) {
                        $patientInfo = $calllogUtil->getJsonEncodedPatient($mergedPatient);
                        continue;
                    }

                    //other iterations: add as merged patients to $patientInfo
                    $mergedPatientsInfo[$masterPatientId]['patientInfo'][$mergedPatient->getId()] = $calllogUtil->getJsonEncodedPatient($mergedPatient);
                    $mergedPatientsInfo[$masterPatientId]['patientInfo'][$mergedPatient->getId()]['masterPatientId'] = $masterPatientId;

                    //$mergedPatientsInfo[$masterPatientId]['mergeInfo'][$mergedPatient->getId()] = $mergedPatient->obtainMergeInfo();

                }//foreach $mergedPatient

                $patientInfo['masterPatientId'] = $masterPatientId;
                $patientInfo['mergedPatientsInfo'] = $mergedPatientsInfo;

//                //set Master Patient
//                $masterPatientIdOut = null;
//                if ($mergedPatient->isMasterMergeRecord() && $formtype == "call-entry") {
//                    $masterPatientIdOut = $masterPatientId;
//                }
//                $patientInfo['masterPatientId'] = $masterPatientIdOut;

            } else {
                //just display this patient
                $patientInfo = $calllogUtil->getJsonEncodedPatient($patient);
                //$patientsArr[] = $patientInfo;
            }

            $patientsArr[$patientId] = $patientInfo;
        }
        //exit('exit search patient');

        $resData = array();
        $resData['patients'] = $patientsArr;
        $resData['searchStr'] = implode("; ",$searchedArr);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($resData));
        return $response;
    }

    public function searchPatient( $request, $evenlog=false, $params=null ) {

        $mrntype = trim($request->get('mrntype'));
        $mrn = trim($request->get('mrn'));
        $dob = trim($request->get('dob'));
        $lastname = trim($request->get('lastname'));
        $firstname = trim($request->get('firstname'));
        //print_r($allgets);
        //echo "mrn=".$mrn."<br>";

        $exactMatch = true;
        $matchAnd = true;

        if( $params ) {
            $mrntype = ( array_key_exists('mrntype', $params) ? $params['mrntype'] : null);
            $mrn = ( array_key_exists('mrn', $params) ? $params['mrn'] : null);
            $dob = ( array_key_exists('dob', $params) ? $params['dob'] : null);
            $lastname = ( array_key_exists('lastname', $params) ? $params['lastname'] : null);
            $firstname = ( array_key_exists('firstname', $params) ? $params['firstname'] : null);
        }

        $em = $this->getDoctrine()->getManager();

        $parameters = array();

        $repository = $em->getRepository('OlegOrderformBundle:Patient');
        $dql = $repository->createQueryBuilder("patient");
        $dql->leftJoin("patient.mrn", "mrn");
        $dql->leftJoin("patient.dob", "dob");
        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");
        $dql->leftJoin("patient.encounter", "encounter");
        $dql->leftJoin("encounter.patlastname", "encounterLastname");
        $dql->leftJoin("encounter.patfirstname", "encounterFirstname");

        //$dql->where("mrn.status = :statusValid");

        $where = false;
        $searchBy = "unknown parameters";
        $searchArr = array();

        if( $mrntype ) {
            $calllogUtil = $this->get('calllog_util');
            $mrntype = $calllogUtil->convertAutoGeneratedMrntype($mrntype,true);
        }

        //mrn
        if( $mrntype && $mrn ) {

            //$calllogUtil = $this->get('calllog_util');
            //$mrntype = $calllogUtil->convertAutoGeneratedMrntype($mrntype);
            //echo "mrntype=".$mrntype."<br>";
            //echo "mrn=".$mrn."<br>";

            $dql->andWhere("mrn.keytype = :keytype");
            $parameters['keytype'] = $mrntype->getId();

            if( $exactMatch ) {
                $mrnClean = ltrim($mrn, '0');
                //echo "mrn: ".$mrn."?=".$mrnClean."<br>";
                if( $mrn === $mrnClean ) {
                    //echo "equal <br>";
                    $dql->andWhere("mrn.field = :mrn");
                    $parameters['mrn'] = $mrn;
                } else {
                    //echo "not equal <br>";
                    $dql->andWhere("mrn.field = :mrn OR mrn.field = :mrnClean");
                    $parameters['mrn'] = $mrn;
                    $parameters['mrnClean'] = $mrnClean;
                }

            } else {
                $dql->andWhere("mrn.field LIKE :mrn");
                $parameters['mrn'] = '%' . $mrn . '%';
            }

            $dql->andWhere("mrn.status = :statusValid OR mrn.status = :statusAlias");
            $parameters['statusValid'] = 'valid';
            $parameters['statusAlias'] = 'alias';

            $where = true;
            $searchArr[] = "MRN Type: ".$mrntype."; MRN: ".$mrn;
        }

        //DOB
        if( $dob && ($where == false || $matchAnd == true) ) {
            //echo "dob=".$dob."<br>";
            $searchArr[] = "DOB: " . $dob;
            $dobDateTime = \DateTime::createFromFormat('m/d/Y', $dob)->format('Y-m-d');
            //echo "dob=".$dob." => ".$dobDateTime."<br>";
            $dql->andWhere("dob.status = :statusValid OR dob.status = :statusAlias");
            $dql->andWhere("dob.field = :dob");
            $parameters['dob'] = $dobDateTime;
            $where = true;
        }

//        //Last Name Only
//        if( $lastname && !$firstname && ($where == false || $matchAnd == true) ) {
//
//            //$lastname = "Doe";
//            echo "lastname=".$lastname."<br>";
//            $searchArr[] = "Last Name: " . $lastname;
//
//            $statusStr = "(lastname.status = :statusValid OR lastname.status = :statusAlias)";
//            $statusEncounterStr = "(encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias)";
//
//            if ($exactMatch) {
//                ////$dql->andWhere("lastname.field = :lastname OR encounterLastname.field = :lastname");
//                $dql->andWhere("(lastname.field = :lastname AND $statusStr) OR (encounterLastname.field = :lastname AND $statusEncounterStr)");
//                $parameters['lastname'] = $lastname;
//            } else {
//                $dql->andWhere("lastname.field LIKE :lastname OR encounterLastname.field LIKE :lastname");
//            }
//
//            //$dql->andWhere("lastname.status = :statusValid OR lastname.status = :statusAlias");
//            //$dql->andWhere("encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias");
//
//            $parameters['statusValid'] = 'valid';
//            $parameters['statusAlias'] = 'alias';
//
//            $where = true;
//        }

//        //First Name Only
//        if( $firstname && !$lastname && ($where == false || $matchAnd == true) ) {
//
//            //$firstname = "Linda";
//            echo "firstname=".$firstname."<br>";
//            $searchArr[] = "First Name: " . $firstname;
//
//            $statusStr = "(firstname.status = :statusValid OR firstname.status = :statusAlias)";
//            $statusEncounterStr = "(encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias)";
//
//            if( $exactMatch ) {
//                ////$dql->andWhere("firstname.field = :firstname OR encounterFirstname.field = :firstname");
//                $dql->andWhere("(firstname.field = :firstname AND $statusStr) OR (encounterFirstname.field = :firstname AND $statusEncounterStr)");
//                $parameters['firstname'] = $firstname;
//            } else {
//                $dql->andWhere("firstname.field LIKE :firstname OR encounterFirstname.field LIKE :firstname");
//                $parameters['firstname'] = '%' . $firstname . '%';
//            }
//
//            $dql->andWhere("firstname.status = :statusValid OR firstname.status = :statusAlias");
//            $dql->andWhere("encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias");
//            $parameters['statusValid'] = 'valid';
//            $parameters['statusAlias'] = 'alias';
//
//            $where = true;
//        }

        //Last Name AND DOB
        if( ($lastname || $firstname) && ($where == false || $matchAnd == true) ) {

            //$lastname = "Doe";
            //echo "1 lastname=".$lastname."<br>";
            //echo "1 firstname=".$firstname."<br>";

            $searchCriterionArr = array();

            if( $lastname ) {
                $searchArr[] = "Last Name: " . $lastname;

                $statusStr = "(lastname.status = :statusValid OR lastname.status = :statusAlias)";
                $statusEncounterStr = "(encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias)";

                $searchCriterionArr[] = "(lastname.field = :lastname AND $statusStr) OR (encounterLastname.field = :lastname AND $statusEncounterStr)";

                $parameters['lastname'] = $lastname;

                //status
                $dql->andWhere("lastname.status = :statusValid OR lastname.status = :statusAlias");
                $dql->andWhere("encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias");
                $parameters['statusValid'] = 'valid';
                $parameters['statusAlias'] = 'alias';

                $where = true;
            }

            if( $firstname ) {
                $searchArr[] = "First Name: " . $firstname;

                $statusStr = "(firstname.status = :statusValid OR firstname.status = :statusAlias)";
                $statusEncounterStr = "(encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias)";

                $searchCriterionArr[] = "(firstname.field = :firstname AND $statusStr) OR (encounterFirstname.field = :firstname AND $statusEncounterStr)";

                $parameters['firstname'] = $firstname;

                //status
                $dql->andWhere("firstname.status = :statusValid OR firstname.status = :statusAlias");
                $dql->andWhere("encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias");
                $parameters['statusValid'] = 'valid';
                $parameters['statusAlias'] = 'alias';

                $where = true;
            }

            $searchCriterionStr = implode(" OR ",$searchCriterionArr);
            $dql->andWhere($searchCriterionStr);
        }

//        //Last Name AND DOB
//        if( $lastname && $dob && ($where == false || $matchAnd == true) ) {
//            $dobDateTime = \DateTime::createFromFormat('m/d/Y', $dob)->format('Y-m-d');
//            //echo "dob=".$dob." => ".$dobDateTime."<br>";
////            $dql->andWhere("dob.field = :dob AND (lastname.field = :lastname OR encounterLastname.field = :lastname)");
////            $parameters['lastname'] = $lastname;
////            $parameters['dob'] = $dobDateTime;
//
//            $dql->andWhere("dob.field = :dob");
//            $parameters['dob'] = $dobDateTime;
//
//            if ($exactMatch) {
//                $dql->andWhere("lastname.field = :lastname OR encounterLastname.field = :lastname");
//                $parameters['lastname'] = $lastname;
//            } else {
//                $dql->andWhere("lastname.field LIKE :lastname OR encounterLastname.field LIKE :lastname");
//                $parameters['lastname'] = '%' . $lastname . '%';
//            }
//
//            $dql->andWhere("dob.status = :statusValid OR dob.status = :statusAlias");
//            $dql->andWhere("lastname.status = :statusValid OR lastname.status = :statusAlias");
//            $dql->andWhere("encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias");
//            $parameters['statusValid'] = 'valid';
//            $parameters['statusAlias'] = 'alias';
//
//            $searchArr[] = "DOB: " . $dob . ", Last Name: " . $lastname;
//
//            if ($firstname) {
//                $dql->andWhere("firstname.field = :firstname OR encounterFirstname.field = :firstname");
//                $dql->andWhere("encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias");
//                $parameters['firstname'] = $firstname;
//
//                $searchArr[] = ", First Name: " . $firstname;
//            }
//
//            $where = true;
//        }

//        //Last Name only
//        if( $lastname && ($where == false || $matchAnd == true) ) {
//            echo "lastname=".$lastname."<br>";
//            if( $exactMatch ) {
//                $dql->andWhere("lastname.field = :lastname OR encounterLastname.field = :lastname");
//                $parameters['lastname'] = $lastname;
//            } else {
//                $dql->andWhere("lastname.field LIKE :lastname OR encounterLastname.field LIKE :lastname");
//                $parameters['lastname'] = '%' . $lastname . '%';
//            }
//
//            $dql->andWhere("lastname.status = :statusValid OR lastname.status = :statusAlias");
//            $dql->andWhere("encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias");
//            $parameters['statusValid'] = 'valid';
//            $parameters['statusAlias'] = 'alias';
//
//            $searchArr[] = "Last Name: ".$lastname;
//
//            if( $firstname ) {
//                $dql->andWhere("encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias");
//
//                if( $exactMatch ) {
//                    $dql->andWhere("firstname.field = :firstname OR encounterFirstname.field = :firstname");
//                    $parameters['firstname'] = $firstname;
//                } else {
//                    $dql->andWhere("firstname.field LIKE :firstname OR encounterFirstname.field LIKE :firstname");
//                    $parameters['firstname'] = '%' . $firstname . '%';
//                }
//
//                $searchArr[] = ", First Name: ".$firstname;
//            }
//
//            $where = true;
//        }


//        //firstname, Last Name AND DOB
//        if( $lastname && $firstname && $dob ) {
//            $dql->andWhere("lastname.field = :lastname AND firstname.field = :firstname");
//            $parameters['lastname'] = $lastname;
//            $parameters['firstname'] = $firstname;
//            $where = true;
//        }

        if( count($searchArr) > 0 ) {
            $searchBy = implode("; ",$searchArr);
        }

        if( $where ) {

            $query = $em->createQuery($dql);
            $query->setParameters($parameters);
            //echo "sql=".$query->getSql()."<br>";
            $patients = $query->getResult();

            //testing
//            echo "<br>";
//            foreach( $patients as $patient ) {
//                echo "ID=".$patient->getId()."<br>";
//            }
//            //exit('patients count='.count($patients));

            //log search action
            if( $evenlog ) {
                if( count($patients) == 0 ) {
                    $patientEntities = null;
                } else {
                    $patientEntities = $patients;
                }
                $user = $this->get('security.context')->getToken()->getUser();
                $userSecUtil = $this->container->get('user_security_utility');
                $eventType = "Patient Searched";
                $event = "Patient searched by ".$searchBy;
                $event = $event . "; found ".count($patients)." patient(s).";
                $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'),$event,$user,$patientEntities,$request,$eventType);
            }

        } else {
            $patients = array();
        }

        //search for merged
        //$calllogUtil = $this->get('calllog_util');
        //$patients = $calllogUtil->getAllMergedPatients( $patients );

        $res = array();
        $res['patients'] = $patients;
        $res['searchStr'] = $searchBy;

        return $res;
    }


    /**
     * Create a new Patient
     * @Route("/patient/create", name="calllog_create_patient", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function createPatientAction(Request $request)
    {

        if( false == $this->get('security.context')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $securityUtil = $this->get('order_security_utility');
        $userSecUtil = $this->get('user_security_utility');
        $res = array();
        $output = 'OK';
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        //testing
        //exit("createPatientAction");
        //$res['output'] = "OK";
        //$response->setContent(json_encode($res));
        //return $response;

        //TODO: The server should DOUBLECHECK that the user has a role with a permission of "Create Patient Record"
        if (false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER')) {
            //return $this->redirect($this->generateUrl('calllog-nopermission'));
            $res['patients'] = null;
            $res['output'] = "You don't have a permission to create a new patient record";
            $response->setContent(json_encode($res));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $calllogUtil = $this->get('calllog_util');

        $mrn = trim($request->get('mrn'));
        $mrntype = trim($request->get('mrntype'));
        $dob = trim($request->get('dob'));
        $lastname = trim($request->get('lastname'));
        $firstname = trim($request->get('firstname'));
        $middlename = trim($request->get('middlename'));
        $suffix = trim($request->get('suffix'));
        $sex = trim($request->get('sex'));
        //print_r($allgets);
        //echo "mrn=".$mrn."<br>";
        //echo "mrntype=".$mrntype."<br>";

        if( $mrntype ) {
            $errorMsg = 'Mrn Type not found by "' . $mrntype . '"';
            $calllogUtil = $this->get('calllog_util');
            $mrntype = $calllogUtil->convertAutoGeneratedMrntype($mrntype);
            if( ! $mrntype ) {
                $res['patients'] = null;
                $res['output'] = $errorMsg;
                $response->setContent(json_encode($res));
                return $response;
            }
        }

        //TODO: set institution
//        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
//        $institution = $userSiteSettings->getDefaultInstitution();
//        //echo "1 inst=".$institution."<br>";
//        if( !$institution ) {
//            $institutions = $securityUtil->getUserPermittedInstitutions($user);
//            //echo "count inst=".count($institutions)."<br>";
//            if (count($institutions) > 0) {
//                $institution = $institutions[0];
//            }
//            //echo "2 inst=".$institution."<br>";
//        }
//        if (!$institution) {
//            $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
//        }
        $institution = $userSecUtil->getCurrentUserInstitution($user);
        //echo "3 inst=".$institution."<br>";
        //exit('1');

        //get correct mrn type
        if( $mrntype && $mrn ) {
            $keytype = $mrntype;
        } else {
            $keytypeEntity = $this->getDoctrine()->getRepository('OlegOrderformBundle:MrnType')->findOneByName("Auto-generated MRN");
            $keytype = $keytypeEntity->getId() . ""; //id of "New York Hospital MRN" in DB
        }

        //first check if the patient already exists
//        $params = array();
//        $params['mrntype'] = $mrntype;
//        $params['mrn'] = $mrn;
//        $patientsData = $this->searchPatient( $request, false, $params );
        $patientsData = $this->searchPatient( $request );
        $patients = $patientsData['patients'];

        if( count($patients) > 0 ) {
            $output = "Can not create a new Patient. The patient with specified parameters already exists:<br>";

            if( $mrntype ) {
                $output .= "MRN Type:".$keytypeEntity."<br>";
            }
            if( $mrn )
                $output .= "MRN:".$mrn."<br>";
            if( $lastname )
                $output .= "Last Name:".$lastname."<br>";
            if( $firstname )
                $output .= "First Name:".$firstname."<br>";
            if( $dob )
                $output .= "DOB:".$dob."<br>";

            $res['patients'] = null;
            $res['output'] = $output;
            $response->setContent(json_encode($res));
            return $response;
        }

        //testing
        if(0) {
            $patient = $em->getRepository('OlegOrderformBundle:Patient')->find(32);
            $patientsArr = array(); //return json data
            $patientInfo = $calllogUtil->getJsonEncodedPatient($patient);
            $patientsArr[$patient->getId()] = $patientInfo;
            $res['patients'] = $patientsArr;
            $res['output'] = $output;
            $response->setContent(json_encode($res));
            return $response;
        }

        //Create a new Patient
        $sourcesystem = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $status = 'valid';

        //$patient = new Patient(false,$status,$user,$sourcesystem);
        //$patient->setInstitution($institution);

        //create a new patient
        if( $mrn ) {
            $fieldValue = $mrn;
        } else {
            $fieldValue = null;
        }

//        if( $mrntype ) {
//            $keytype = $mrntype;
//        } else {
//            $keytypeEntity = $this->getDoctrine()->getRepository('OlegOrderformBundle:MrnType')->findOneByName("Auto-generated MRN");
//            $keytype = $keytypeEntity->getId() . ""; //id of "New York Hospital MRN" in DB
//        }

        //echo "mrn=".$fieldValue."<br>";
        //echo "keytype=".$keytype." (".$keytypeEntity.")<br>";
        //exit("1");

        $extra = array();
        $extra["keytype"] = $keytype;

        //echo "keytype=".$keytype."<br>";
        //exit();


        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository('OlegOrderformBundle:Patient')->createElement(
            $institution,
            $status,            //status
            $user,              //provider
            "Patient",          //$className
            "mrn",              //$fieldName
            null,               //$parent
            $fieldValue,        //$fieldValue
            $extra,             //$extra
            false               //$withfields
        );

        $mrnRes = $patient->obtainStatusField('mrn', $status);
        $createdWithArr = array();
        $createdWithArr[] = "MRN Type: ".$mrnRes->getKeytype()->getName();
        $createdWithArr[] = "MRN: ".$mrnRes->getField();

        //set source
        $patient->setSource($sourcesystem);

        //set patient record status "Active"
        $patientActiveStatus = $em->getRepository('OlegOrderformBundle:PatientRecordStatusList')->findOneByName("Active");
        if( $patientActiveStatus ) {
            $patient->setPatientRecordStatus($patientActiveStatus);
        }

        //mrn with leading zeros
        if( 0 && $mrn ) {
            $mrnClean = ltrim($mrn, '0');
            //echo "mrn: ".$mrn."?=".$mrnClean."<br>";
            if ($mrn !== $mrnClean) {
                //create additional valid patient MRN: "00123456" and "123456".
                $mrnCleanObject = new PatientMrn($status,$user,$sourcesystem);
                $mrnCleanObject->setKeytype($mrnRes->getKeytype());
                $mrnCleanObject->setField($mrnClean);
                $patient->addMrn($mrnCleanObject);
                $createdWithArr[] = "Clean MRN: ".$mrnClean;
            }
        }

        //$patient->addDob( new PatientDob($status,$user,$sourcesystem) );
        if( $dob ) {
            $dobDateTime = \DateTime::createFromFormat('m/d/Y', $dob);
            $PatientDob = new PatientDob($status, $user, $sourcesystem);
            $PatientDob->setField($dobDateTime);
            $patient->addDob($PatientDob);
            $createdWithArr[] = "DOB: " . $dob;
        }

        //create an encounter for this new patient with the First Name, Last Name, Middle Name, Suffix, and sex (if any)
        $encounter = new Encounter(false,$status,$user,$sourcesystem);
        $encounter->setInstitution($institution);

        if( $lastname ) {
            $EncounterPatlastname = new EncounterPatlastname($status, $user, $sourcesystem);
            $EncounterPatlastname->setField($lastname);
            $encounter->addPatlastname($EncounterPatlastname);

            $PatientLastname = new PatientLastName($status,$user,$sourcesystem);
            $PatientLastname->setField($lastname);
            $patient->addLastname( $PatientLastname );

            $createdWithArr[] = "Last Name: " . $lastname;
        }

        if( $firstname ) {
            $EncounterPatfirstname = new EncounterPatfirstname($status, $user, $sourcesystem);
            $EncounterPatfirstname->setField($firstname);
            $encounter->addPatfirstname($EncounterPatfirstname);

            $PatientFirstname = new PatientFirstName($status,$user,$sourcesystem);
            $PatientFirstname->setField($firstname);
            $patient->addFirstname( $PatientFirstname );

            $createdWithArr[] = "First Name: " . $firstname;
        }

        if( $middlename ) {
            $EncounterPatmiddlename = new EncounterPatmiddlename($status, $user, $sourcesystem);
            $EncounterPatmiddlename->setField($middlename);
            $encounter->addPatmiddlename($EncounterPatmiddlename);

            $PatientMiddlename = new PatientMiddleName($status,$user,$sourcesystem);
            $PatientMiddlename->setField($middlename);
            $patient->addMiddlename( $PatientMiddlename );

            $createdWithArr[] = "Middle Name: " . $middlename;
        }

        if( $suffix ) {
            $EncounterPatsuffix = new EncounterPatsuffix($status, $user, $sourcesystem);
            $EncounterPatsuffix->setField($suffix);
            $encounter->addPatsuffix($EncounterPatsuffix);

            $PatientSuffix = new PatientSuffix($status,$user,$sourcesystem);
            $PatientSuffix->setField($suffix);
            $patient->addSuffix( $PatientSuffix );

            $createdWithArr[] = "Suffix: " . $suffix;
        }

        if( $sex ) {
            //echo "sex=".$sex."<br>";
            $sexObj = $em->getRepository('OlegUserdirectoryBundle:SexList')->findOneById( $sex );
            $EncounterPatsex = new EncounterPatsex($status, $user, $sourcesystem);
            $EncounterPatsex->setField($sexObj);
            $encounter->addPatsex($EncounterPatsex);

            $PatientSex = new PatientSex($status,$user,$sourcesystem);
            $PatientSex->setField($sexObj);
            $patient->addSex( $PatientSex );

            $createdWithArr[] = "Gender: " . $sexObj;
        }

        $patient->addEncounter($encounter);

        $em->persist($patient);
        $em->persist($encounter);
        $em->flush();

        //convert patient to json
        $patientsArr = array(); //return json data
        $patientInfo = $calllogUtil->getJsonEncodedPatient($patient);
        $patientsArr[$patient->getId()] = $patientInfo;
        $res['patients'] = $patientsArr;
        $res['output'] = $output;

        $eventType = "Patient Created";
        $event = "New Patient has been created:<br>" . implode("<br>", $createdWithArr);

        //log patient creation action
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $event, $user, $patient, $request, $eventType);

        $response->setContent(json_encode($res));
        return $response;
    }


//    public function getCurrentUserInstitution($user)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $securityUtil = $this->get('order_security_utility');
//
//        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
//        $institution = $userSiteSettings->getDefaultInstitution();
//        //echo "1 inst=".$institution."<br>";
//        if (!$institution) {
//            $institutions = $securityUtil->getUserPermittedInstitutions($user);
//            //echo "count inst=".count($institutions)."<br>";
//            if (count($institutions) > 0) {
//                $institution = $institutions[0];
//            }
//        //echo "2 inst=".$institution."<br>";
//        }
//        if (!$institution) {
//            $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
//        }
//
//        return $institution;
//    }

    /**
     * Get Patient Titles
     * @Route("/patient/title/", name="calllog_get_patient_title", options={"expose"=true})
     * @Method("GET")
     */
    public function getPatientTitleAction(Request $request) {

        if (false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $patientId = trim($request->get('patientId'));
        $nowStr = trim($request->get('nowStr'));
        //echo "patientId=".$patientId."<br>";
        //echo "nowStr=".$nowStr."<br>";

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $nowDate = new \DateTime($nowStr);

        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository('OlegOrderformBundle:Patient')->find($patientId);
        if( !$patient ) {
            $response->setContent(json_encode("ERROR"));
            return $response;
        }

        $patientTitle = $patient->obtainPatientInfoTitle('valid',$nowDate);
        if( !$patientTitle ) {
            $response->setContent(json_encode("ERROR"));
            return $response;
        }

        $response->setContent(json_encode($patientTitle));
        return $response;
    }


    /**
     * Get Call Log Entry Message
     * @Route("/entry/view/{messageId}", name="calllog_callentry_view")
     * @Method("GET")
     * @Template("OlegCallLogBundle:CallLog:call-entry-view.html.twig")
     */
    public function getCallLogEntryAction(Request $request, $messageId)
    {

        if (false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $userSecUtil = $this->get('user_security_utility');

        $cycle = "show";
        //$title = "Call Log Entry";
        $formtype = "call-entry";

        //$patientId = trim($request->get('patientId'));
        //$nowStr = trim($request->get('nowStr'));
        //echo "patientId=".$patientId."<br>";
        //echo "nowStr=".$nowStr."<br>";
        //$messageId = 142; //154; //testing

        $em = $this->getDoctrine()->getManager();
        $message = $em->getRepository('OlegOrderformBundle:Message')->find($messageId);
        if (!$message) {
            throw new \Exception('Message has not found by ID ' . $messageId);
        }

        //testing dob: dob before 1901 causes php error
//        $testPatient = $message->getPatient()[0];
//        foreach( $testPatient->getDob() as $dob ) {
//            if( $dob ) {
//                echo $dob->getId().": dob=" . $dob . "<br>";
//                //echo "dob=" . $dob->format('m-d-Y') . "<br>";
//            } else {
//                echo "dob is null <br>";
//            }
//        }
        //testing
        //$mesInfo = $this->get('user_formnode_utility')->getFormNodeHolderShortInfo($message,$message->getMessageCategory(),1,"");
        //echo "mesInfo=".$mesInfo."<br>";

        $messageInfo = "Entry ID ".$message->getId()." submitted on ".$message->getSubmitterInfo(); // . " | Call Log Book";
        if (count($message->getPatient()) > 0 ) {
            $mrnRes = $message->getPatient()->first()->obtainStatusField('mrn', "valid");
            $mrntype = $mrnRes->getKeytype()->getId();
            $mrn = $mrnRes->getField();

            //LastName, FirstName, MiddleName | MRN Type: MRN | DOB: MM/DD/YY |
            // Entry ID XXX submitted on MM/DD/YYYY at HH:MM by SubmitterFirstName SubmitterLastName, MD | Call Log Book
            $title = $message->getPatient()->first()->obtainPatientInfoTitle('valid',null,false);
            $title = $title . " | ".$messageInfo;

        } else {
            $mrntype = null;
            $mrn = null;

            $title = $messageInfo;
        }

        //echo "patients=".count($message->getPatient())."<br>";
        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle);

        $complexPatientStr = null;
        //find record in the "Pathology Call Complex Patients" list by message object entityName, entityId
//        $mapper = array(
//            'prefix' => "Oleg",
//            'bundleName' => "CallLogBundle",
//            'className' => "PathologyCallComplexPatients",
//        );
//        $listRecord = $userSecUtil->getListByNameAndObject( $message, $mapper );
//        if( $listRecord ) {
//            //Patient was added to the "xxxxxxxx" list via this entry.
//            $complexPatientStr = "Patient was added to the Pathology Call Complex Patients list ID# ".$listRecord->getId()." via this entry:<br>".$listRecord->getName()."";
//        }
        //echo "complexStr=".$complexPatientStr."<br>";

        $class = new \ReflectionClass($message);
        $className = $class->getShortName();          //ObjectTypeText
        $classNamespace = $class->getNamespaceName(); //Oleg\UserdirectoryBundle\Entity

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
            'sitename' => $this->container->getParameter('calllog.sitename'),
            'titleheadroom' => $title
        );
    }

}
