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



use App\OrderformBundle\Entity\MessageStatusList; //process.py script: replaced namespace by ::class: added use line for classname=MessageStatusList


use App\OrderformBundle\Entity\MrnType; //process.py script: replaced namespace by ::class: added use line for classname=MrnType


use App\OrderformBundle\Entity\CalllogAttachmentTypeList; //process.py script: replaced namespace by ::class: added use line for classname=CalllogAttachmentTypeList


use App\OrderformBundle\Entity\PatientListHierarchy; //process.py script: replaced namespace by ::class: added use line for classname=PatientListHierarchy


use App\OrderformBundle\Entity\PatientRecordStatusList; //process.py script: replaced namespace by ::class: added use line for classname=PatientRecordStatusList


use App\OrderformBundle\Entity\EncounterStatusList; //process.py script: replaced namespace by ::class: added use line for classname=EncounterStatusList


use App\OrderformBundle\Entity\EncounterInfoTypeList; //process.py script: replaced namespace by ::class: added use line for classname=EncounterInfoTypeList


use App\UserdirectoryBundle\Entity\Document; //process.py script: replaced namespace by ::class: added use line for classname=Document


use App\OrderformBundle\Entity\MessageCategory; //process.py script: replaced namespace by ::class: added use line for classname=MessageCategory


use App\UserdirectoryBundle\Entity\SexList; //process.py script: replaced namespace by ::class: added use line for classname=SexList



use App\CallLogBundle\Util\CallLogUtil;
use App\OrderformBundle\Entity\Accession;
use App\OrderformBundle\Entity\AccessionAccession;
use App\OrderformBundle\Entity\AccessionAccessionDate;
use App\OrderformBundle\Entity\Procedure;
use App\UserdirectoryBundle\Entity\EventObjectTypeList;
//use App\UserdirectoryBundle\Util\UserServiceUtil;
use Doctrine\Common\Collections\ArrayCollection;
use App\CallLogBundle\Form\CalllogFilterType;
use App\CallLogBundle\Form\CalllogMessageType;
use App\CallLogBundle\Form\CalllogNavbarFilterType;
use App\OrderformBundle\Entity\CalllogEntryMessage;
use App\OrderformBundle\Entity\CalllogTask;
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
use App\OrderformBundle\Form\DataTransformer\MrnTypeTransformer;
use App\OrderformBundle\Helper\ErrorHelper;
use App\UserdirectoryBundle\Entity\ModifierInfo;
use App\UserdirectoryBundle\Entity\Spot;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

//use Box\Spout\Writer\Style\Border;
//use Box\Spout\Writer\Style\BorderBuilder;
//use Box\Spout\Writer\Style\Color;
//use Box\Spout\Writer\Style\StyleBuilder;
//use Box\Spout\Common\Type;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

//TODO: implement WYSIWYG editor to textarea-reach fields
// https://github.com/summernote/summernote
// https://packagist.org/packages/helios-ag/fm-summernote-bundle
//helios-ag/fm-summernote-bundle

class CallEntryController extends OrderAbstractController
{

//    protected $calllogUtil;
    //    protected $userServiceUtil;
    ////    protected $paginator;
    //    public function __construct( CallLogUtil $calllogUtil, UserServiceUtil $userServiceUtil ) {
    //        $this->calllogUtil = $calllogUtil;
    //        $this->userServiceUtil = $userServiceUtil;
    ////        $this->paginator = $paginator;
    //    }
    /**
     * Case List Page
     *
     * Search by navbar
     *
     * Alerts: filtered case list
     *
     */
    //    public function homeAction(Request $request, PaginatorInterface $paginator, CallLogUtil $calllogUtil)
    #[Route(path: '/', name: 'calllog_home', methods: ['GET'])]
    #[Route(path: '/navbarsearch/', name: 'calllog_home_navbarsearch', methods: ['GET'])]
    #[Route(path: '/alerts/', name: 'calllog_alerts', methods: ['GET'])]
    #[Template('AppCallLogBundle/CallLog/home.html.twig')]
    public function homeAction(Request $request)
    {
        if( false == $this->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //testing
        //$userServiceUtil = $this->container->get('user_service_utility');
        //$results = $userServiceUtil->getFuzzyTest();
        //exit("<br>exit");

        //$calllogUtil = $this->container->get('user_service_utility');
        //echo $calllogUtil->getInstalledSoftware()."<br>";

        $em = $this->getDoctrine()->getManager();
        $calllogUtil = $this->container->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;
        $route = $request->get('_route');
        $title = "Call Case List";
        $alerts = false;
        $limit = 10;

        //testing
//        $toid = 272;
//        if( $calllogUtil->hadMessageStatus($toid) ) {
//            echo $toid.": signed<br>";
//        } else {
//            echo $toid.":unsigned<br>";
//        }
//        $toid = 252;
//        if( $calllogUtil->hadMessageStatus($toid) ) {
//            echo $toid.": signed<br>";
//        } else {
//            echo $toid.":unsigned<br>";
//        }
        //exit();

        if( $route == "calllog_alerts" ) {
            $alerts = true;
            $title = $title . " (Alerts)";
        }

        //create a filter and perform search
        $res = $this->getCalllogEntryFilter($request); //Home page

        if( $res['redirect'] ) {
            return $res['redirect'];
        }

        $query = $res['query'];
        $filterform = $res['filterform'];
        $advancedFilter = $res['advancedFilter'];
        //echo "advancedFilter=".$advancedFilter."<br>";

        //Sort Default by ID
//        $paginationParams = array(
//            //'defaultSortFieldName' => 'calllogEntryMessage.oid',
//            'defaultSortFieldName' => 'message.oid',
//            'defaultSortDirection' => 'DESC',
//            'wrap-queries' => true
//        );
        $paginationParams = array('wrap-queries'=>true);

        $paginator  = $this->container->get('knp_paginator');
        $messages = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            //$request->query->getInt('page', 1),
            $limit,      /*limit per page*/
            $paginationParams   //array('wrap-queries'=>true)
        );
        //echo "messages count=".count($messages)."<br>";

        //all messages will show only form fields for this message category node
//        $categoryStr = "Pathology Call Log Entry";
//        $messageCategoryInfoNode = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($categoryStr);
//        if( !$messageCategoryInfoNode ) {
//            throw new \Exception( "MessageCategory type is not found by name '".$categoryStr."'" );
//        }

        $eventObjectType = $em->getRepository(EventObjectTypeList::class)->findOneByName("Message");
        if( $eventObjectType ) {
            $eventObjectTypeId = $eventObjectType->getId();
        } else {
            $eventObjectTypeId = null;
        }

        $defaultPatientListId = null;
        $defaultPatientList = $calllogUtil->getDefaultPatientList();
        if( $defaultPatientList ) {
            $defaultPatientListId = $defaultPatientList->getId();
        }

        if( $messages && count($messages)>0 ) {
            $postfixTitle = "matching entries";
            if( count($messages) == 1 ) {
                $postfixTitle = "matching entry";
            }
            $title = $title . " (".$messages->getTotalItemCount()." ".$postfixTitle.")";
        }
        
        return array(
            'messages' => $messages,
            'alerts' => $alerts,
            'title' => $title,
            'filterform' => $filterform->createView(),
            'route_path' => $route,
            'advancedFilter' => $advancedFilter,
            //'messageCategoryInfoNode' => $messageCategoryInfoNode, //all messages will show only form fields for this message category node
            'eventObjectTypeId' => $eventObjectTypeId,
            'patientListId' => $defaultPatientListId,
            'shownavbarfilter' => false,
            //'navbarfilterform' => $navbarfilterform->createView()
            //'sitename' => $this->getParameter('calllog.sitename')
            //'calllogsearch' => $calllogsearch,
            //'calllogsearchtype' => $calllogsearchtype,
        );
    }

    public function getCalllogEntryFilter(Request $request, $limit=null)
    {

        $em = $this->getDoctrine()->getManager();
        $calllogUtil = $this->container->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;
        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $sitename = $this->getParameter('calllog.sitename');

        $route = $request->get('_route');
        //exit("route=".$route);
        //$title = "Call Case List";

        //$messageStatuses
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageStatusList'] by [MessageStatusList::class]
        $messageStatuses = $em->getRepository(MessageStatusList::class)->findBy(array('type'=>array('default','user-added')));
        $messageStatusesChoice = array();
        foreach( $messageStatuses as $messageStatuse ) {
            //$messageStatusesChoice[$messageStatuse->getId()] = $messageStatuse."";
            $messageStatusesChoice[$messageStatuse.""] = $messageStatuse->getId();
        }
        //add: "All except deleted" [this should show all except "Deleted"], "All", "All Signed Non-Drafts" [this should show both "Signed" and "Signed, Amended"]
        //"All Drafts" [this should show these four "Draft", "Post-signature Draft", "Post-amendment Draft", "Post-deletion draft"].
        //"All post-signature drafts" [this should show these two "Post-signature Draft", "Post-amendment Draft"].
        $messageStatusesChoice["All except deleted"] = "All except deleted";
        $messageStatusesChoice["All"] = "All";
        $messageStatusesChoice["All Signed Non-Drafts"] = "All Signed Non-Drafts";
        $messageStatusesChoice["All Drafts"] = "All Drafts";
        $messageStatusesChoice["All post-signature drafts"] = "All post-signature drafts";

        $searchFilter = null;
        $entryBodySearchFilter = null;
        $messageCategory = null;
        $messageCategoryTypeId = null;
        $messageCategoryEntity = null;
        $messageCategorieDefaultIdStr = null;
        $defaultMrnTypeId = null;
        $metaphone = false;
        $patientPhone = null;
        $patientEmail = null;
        $sortBy = null;
        $attachmentType = null;

        $referringProviderCommunicationFilter = null;
        $accessionTypeFilter = null;
        $accessionNumberFilter = null;

        //4 Tasks
        $task = null;
        $taskType = null;
        $taskUpdatedBy = null;
        $taskAddedBy = null;


        //child nodes of "Pathology Call Log Entry"
        //$messageCategoryParent = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName("Encounter Note");
        $messageCategoriePathCall = $calllogUtil->getDefaultMessageCategory();

        $messageCategories = array();
        if( $messageCategoriePathCall ) {
            $messageCategorieDefaultIdStr = $messageCategoriePathCall->getName()."_".$messageCategoriePathCall->getId();
            
            $messageCategories = $messageCategoriePathCall->printTreeSelectListIncludingThis(true,array("default","user-added"));

            /////////// sort alphabetically //////////////
            $sort = true;
            //$sort = false;
            if( $sort ) {
                $messageCategoriesValue = array();
//                foreach ($messageCategories as $key => $row) {
//                    //echo "row:".$row."<br>";
//                    $messageCategoriesValue[$key] = $row;
//                }
                foreach ($messageCategories as $row => $key) {
                    $messageCategoriesValue[$row] = $key;
                }
                array_multisort($messageCategoriesValue, SORT_ASC, $messageCategories);
            }
            /////////// EOF sort alphabetically //////////////
        }

        //testing
        //print_r($messageCategories);

        //$messageCategoriePathCall = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName("Pathology Call Log Entry");
        //$node1 = array('id'=>1,'text'=>'node1');
        //$node2 = array('id'=>2,'text'=>'node2');
        //$messageCategories = array($node1,$node2);

        //use site setting
//        $defaultMrnType = $userSecUtil->getSiteSettingParameter('keytypemrn',$sitename);
//        //echo "defaultMrnType=".$defaultMrnType."; ID=".$defaultMrnType->getId()."<br>";
//        if( !$defaultMrnType ) {
//            $defaultMrnType = $em->getRepository('AppOrderformBundle:MrnType')->findOneByName("New York Hospital MRN");
//        }
        $defaultMrnType = $calllogUtil->getDefaultMrnType();
        //$defaultMrnTypeId = null;
        if( $defaultMrnType ) {
            $defaultMrnTypeId = $defaultMrnType->getId();
        }
        //echo "defaultMrnTypeId=".$defaultMrnTypeId.", ".$defaultMrnType->getName()."<br>";

        //get mrntypes ($mrntypeChoices)
        $mrntypeChoices = array();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
        $mrntypeChoicesArr = $em->getRepository(MrnType::class)->findBy(
            array(
                'type'=>array('default','user-added')
            ),
            array(
                'orderinlist' => 'ASC'
            )
        );
        foreach( $mrntypeChoicesArr as $thisMrnType ) {
            $mrntypeChoices[$thisMrnType->getName()] = $thisMrnType->getId();
        }

        $referringProviders = $calllogUtil->getReferringProvidersWithUserWrappers();

        ///////////////// search in navbar /////////////////
        $navbarParams = array();
        $navbarParams['navbarSearchTypes'] = $calllogUtil->getNavbarSearchTypes();
        $navbarParams['container'] = $this->container;
        $navbarfilterform = $this->createForm(CalllogNavbarFilterType::class, null, array(
            //'action' => $this->generateUrl('calllog_home'),
            'method' => 'GET',
            'form_custom_value' => $navbarParams
        ));
        $navbarfilterform->handleRequest($request);
        $calllogsearchtype = $navbarfilterform['searchtype']->getData();
        $calllogsearch = $navbarfilterform['search']->getData();
        //$messageStatusFilter = "All except deleted";
        //$messageStatusFilter = 1;
        //$metaphone = $navbarfilterform['metaphone']->getData();
        //echo "navbar: calllogsearchtype=".$calllogsearchtype."; calllogsearch=".$calllogsearch."<br>";

        $redirectParams = array(
            'filter[messageStatus]' => "All except deleted",
            'filter[mrntype]' => $defaultMrnTypeId //$defaultMrnType->getName()
        );
        if ($calllogsearchtype == 'MRN or Last Name') {
            $searchFilter = $calllogsearch;
            //$mrntypeFilter = $defaultMrnTypeId;
            $redirectParams['filter[search]'] = $calllogsearch;
        }
        //if( $calllogsearchtype == 'NYH MRN' ) {
        if ($calllogsearchtype == $defaultMrnType) {
            $searchFilter = $calllogsearch;
            $redirectParams['filter[search]'] = $calllogsearch;
        }
        if ($calllogsearchtype == 'Entry full text') {
            $entryBodySearchFilter = $calllogsearch;
            $redirectParams['filter[entryBodySearch]'] = $calllogsearch;
        }
        if ($calllogsearchtype == 'Last Name') {
            $searchFilter = $calllogsearch;
            $redirectParams['filter[search]'] = $calllogsearch;
        }
        if ($calllogsearchtype == 'Last Name similar to') {
            $searchFilter = $calllogsearch;
            $metaphone = true;
            $redirectParams['filter[search]'] = $calllogsearch;
            $redirectParams['filter[metaphone]'] = $metaphone;
        }
        if ($calllogsearchtype == 'Message Type') {
            $messageCategoryTypeId = $calllogUtil->getMessageTypeByString($calllogsearch, $messageCategories, $messageCategorieDefaultIdStr);
            //echo "navbar messageCategoryTypeId=".$messageCategoryTypeId."<br>";
            $messageCategory = $messageCategoryTypeId; //Other_59 => Other: Chemistry: Pathology Call Log Entry
            $redirectParams['filter[messageCategory]'] = $messageCategoryTypeId;
        }
        //echo "navbar: searchFilter=".$searchFilter."; entryBodySearchFilter=".$entryBodySearchFilter."<br>";

        if( $route == "calllog_home_navbarsearch" ) {
            $redirect = $this->redirect($this->generateUrl('calllog_home',
                $redirectParams
            ));
            return array('redirect' => $redirect);
        }
        ///////////////// EOF search in navbar /////////////////

        $tasks = array(
            "With or without tasks" =>      "with-without-tasks",
            "With Tasks" =>                 "with-tasks",
            "With Any Outstanding Tasks" => "with-outstanding-tasks",
            "With Any Completed Tasks" =>   "with-completed-tasks",
            "Without Tasks" =>              "without-tasks"
        );

        ////////// attachmentTypes //////////
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:CalllogAttachmentTypeList'] by [CalllogAttachmentTypeList::class]
        $attachmentTypes = $em->getRepository(CalllogAttachmentTypeList::class)->findBy(array('type'=>array('default','user-added')));
        $attachmentTypesChoice = array();
        //add: "With attachments", "Without attachments"
        $attachmentTypesChoice["With attachments"] = "With attachments";
        $attachmentTypesChoice["Without attachments"] = "Without attachments";
        foreach( $attachmentTypes as $attachmentType ) {
            $attachmentTypesChoice[$attachmentType.""] = $attachmentType->getId();
        }
        ////////// EOF attachmentTypes //////////

        //Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader::getIdValue(): Argument #1 ($object) must be of type ?object, int given
        //https://stackoverflow.com/questions/62649386/argument-1-passed-to-symfony-bridge-doctrine-form-choicelist-idreadergetidvalu
        //https://ourcodeworld.com/articles/read/1388/how-to-solve-symfony-5-exception-argument-1-passed-to-symfonybridgedoctrineformchoicelistidreader-getidvalue-must-be-an-object-or-null-string-given
        //$defaultCommunication = $em->getRepository('AppUserdirectoryBundle:HealthcareProviderCommunicationList')->findOneByName("Inbound");
        $defaultCommunication = $userSecUtil->getSiteSettingParameter('defaultInitialCommunication',$sitename);
//        if( $defaultCommunication ) {
//            $defaultCommunication = $defaultCommunication->getId();
//        }

//        $referringProviderCommunications = $em->getRepository('AppUserdirectoryBundle:HealthcareProviderCommunicationList')->findBy(array('type'=>array('default','user-added')));
//        $referringProviderCommunicationChoices = array();
//        foreach( $referringProviderCommunications as $referringProviderCommunication ) {
//            echo "referringProviderCommunication=".$referringProviderCommunication.", id=".$referringProviderCommunication->getId()."<br>";
//            $referringProviderCommunicationChoices[$referringProviderCommunication->getName()] = $referringProviderCommunication->getId();
//        }
        //$defaultAccessionType = $userSecUtil->getSiteSettingParameter('defaultAccessionType',$sitename);

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:PatientListHierarchy'] by [PatientListHierarchy::class]
        $parentPatientList = $em->getRepository(PatientListHierarchy::class)->findOneByName("Pathology Call Log Book Lists");
        if( $parentPatientList ) {
            $parentPatientListId = $parentPatientList->getId();
        } else {
            $parentPatientListId = null;
        }

        $params = array(
            'messageStatuses' => $messageStatusesChoice,
            'messageCategories' => $messageCategories, //for home to list all entries page
            //'messageCategoryDefault' => $messageCategoriePathCall->getId(),
            //'mrntype' => $defaultMrnTypeId,
            'mrntypeChoices' => $mrntypeChoices,
            'mrntypeDefault' => $defaultMrnTypeId,
            'referringProviders' => $referringProviders,
            'search' => $searchFilter,
            //'messageStatus' => $messageStatusFilter,
            'entryBodySearch' => $entryBodySearchFilter,
            'messageCategoryType' => $messageCategoryTypeId,
            'tasks' => $tasks,
            'attachmentTypesChoice' => $attachmentTypesChoice,
            'defaultCommunication' => $defaultCommunication,
            'parentPatientListId' => $parentPatientListId,
            //'referringProviderCommunicationChoices' => $referringProviderCommunicationChoices,
            //'defaultAccessionType' => $defaultAccessionType,
            'metaphone' => $metaphone
        );

        $filterform = $this->createForm(CalllogFilterType::class, null, array(
            'method'=>'GET',
            'form_custom_value'=>$params
        ));

        $filterform->handleRequest($request);

        $messageStatusFilter = $filterform['messageStatus']->getData();
        $mrntypeFilter = $filterform['mrntype']->getData();
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $authorFilter = $filterform['author']->getData();
        $referringProviderFilter = $filterform['referringProvider']->getData();
        $encounterLocationFilter = $filterform['encounterLocation']->getData();
        $institutionFilter = $filterform['institution']->getData();
        $specialtyFilter = $filterform['referringProviderSpecialty']->getData();
        $patientListTitleFilter = $filterform['patientListTitle']->getData();
        $attendingFilter = $filterform['attending']->getData();
        $entryTags = $filterform['entryTags']->getData();
        $patientPhone = $filterform['patientPhone']->getData();
        $patientEmail = $filterform['patientEmail']->getData();
        $sortBy = $filterform['sortBy']->getData();
        $task = $filterform['task']->getData();
        $taskType = $filterform['taskType']->getData();
        $taskUpdatedBy = $filterform['taskUpdatedBy']->getData();
        $taskAddedBy = $filterform['taskAddedBy']->getData();
        $attachmentType = $filterform['attachmentType']->getData();
        $messageId = $filterform['id']->getData();

        $initialCommunicationFilter = $filterform['initialCommunication']->getData();
        $accessionTypeFilter = $filterform['accessionType']->getData();
        $accessionNumberFilter = $filterform['accessionNumber']->getData();

        if( !$searchFilter ) {
            $searchFilter = $filterform['search']->getData();
        }
        if( !$entryBodySearchFilter ) {
            $entryBodySearchFilter = $filterform['entryBodySearch']->getData();
        }
        if( !$messageCategory ) {
            $messageCategory = $filterform['messageCategory']->getData();
        }
        if( $messageCategory ) {
            $messageCategoryEntity = $calllogUtil->getMessageCategoryEntityByIdStr($messageCategory);
        }
//        if( !$messageStatusFilter ) {
//            $messageStatusFilter = $filterform['messageStatus']->getData();
//        }

        //if( $filterform->has('metaphone') ) {
        if( !$metaphone ) {
            $metaphone = $filterform['metaphone']->getData();
            //echo "has metaphone<br>";
        }
        //echo "metaphone=".$metaphone."<br>";

        //redirect if filter is empty
        if( $this->isFilterEmpty($filterform) ) {
            //echo "calllogsearch isFilterEmpty true; calllogsearch=$calllogsearch <br>";
            if( !$calllogsearch ) {
                $redirect = $this->redirect($this->generateUrl('calllog_home',
                    array(
                        'filter[messageStatus]' => "All except deleted",
                        'filter[messageCategory]' => $messageCategorieDefaultIdStr,    //$messageCategoriePathCall->getName()."_".$messageCategoriePathCall->getId()
                        'filter[mrntype]' => $defaultMrnTypeId,
                        //'filter[metaphone]'=>false
                    )
                ));
                return array('redirect' => $redirect);
            }
        }

        //perform search
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $repository = $em->getRepository(Message::class);
        $dql = $repository->createQueryBuilder('message');
        $dql->leftJoin("message.patient","patient");
        $dql->leftJoin("patient.mrn","mrn");
        $dql->leftJoin("patient.lastname","lastname");
        $dql->leftJoin("patient.firstname","firstname");
        $dql->leftJoin("message.encounter","encounter");
        $dql->leftJoin("message.calllogEntryMessage","calllogEntryMessage");
        $dql->leftJoin("calllogEntryMessage.calllogTasks","calllogTasks");

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

        $dql->leftJoin("message.accession", "accession");
        $dql->leftJoin("accession.accession", "accessionaccession");
        
        //$dql->where("institution.id = ".$pathology->getId());

        //sortBy
        //$dql->orderBy("message.orderdate","DESC");
        //$dql->addOrderBy("editorInfos.modifiedOn","DESC");

        //$sortBy
        //'Sort by date of entry creation, latest first' => 'sort-by-creation-date', (default)
        //'Sort by date of latest edit, latest first' => 'sort-by-latest-edit-date'
        //echo "sortBy=$sortBy <br>";
        //exit('111');
        if( $sortBy ) {
            if( $sortBy == "sort-by-creation-date" ) {
                //Sort by date of entry creation, latest first (default)
                //list ordered by submission date of the first version of the entries
                //Creation date correlates with the original message's ID (oid).
                $dql->orderBy("message.oid","DESC");
            }
            if( $sortBy == "sort-by-latest-edit-date" ) {
                //Sort by date of latest edit, latest first
                //Latest edit date correlates with the current message's ID (oid). Also, message's orderdate can be used simultaneously.
                //$dql->orderBy("message.id","DESC");
                $dql->orderBy("message.orderdate","DESC");
            }
        } else {
            $dql->orderBy("message.oid","DESC");
        }

        $dql->addOrderBy("editorInfos.modifiedOn","DESC");

        //filter only CallLog messages
        $dql->andWhere("calllogEntryMessage IS NOT NULL");

        //testing
        //$dql->leftJoin( 'AppOrderformBundle:Message', 'message2', 'WITH', 'message.oid = message2.oid AND message.version > message2.version' );
        //$dql->groupBy("message.oid");

        //filter
        $mergeMrn = null;

        $advancedFilter = 0;
        //$defaultAdvancedFilter = false;
        $queryParameters = array();

        if( $metaphone ) {
            $advancedFilter++;
        }

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
            $endDate->modify('+1 day');
            //echo "endDate=" . $endDate->format('Y-m-d') . "<br>";
            $dql->andWhere('message.orderdate <= :endDate');
            $queryParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
        }



        if( $messageCategoryEntity ) {
            //echo "search messageCategory=".$messageCategory."<br>";
            //echo "search under messageCategoryEntity=".$messageCategoryEntity."<br>";
            $selectOrder = false;
            $nodeChildSelectStr = $messageCategoryEntity->selectNodesUnderParentNode($messageCategoryEntity, "messageCategory",$selectOrder);
            $dql->andWhere($nodeChildSelectStr);
        }

        //echo "searchFilter=$searchFilter; mrntypeFilter=".$mrntypeFilter."<br>";
        if( $searchFilter ) {
            //echo "searchFilter=$searchFilter; mrntypeFilter=".$mrntypeFilter."<br>";
            if ( strval($searchFilter) != strval(intval($searchFilter)) ) {
                //echo "lastname.field string: $searchFilter<br>";
                ////$dql->andWhere("mrn.field LIKE :search OR lastname.field LIKE :search OR message.messageTitle LIKE :search OR authorInfos.displayName LIKE :search OR messageCategory.name LIKE :search");
                if( $metaphone ) {
                    $userServiceUtil->getMetaphoneLike("lastname.field","lastname.fieldMetaphone",$searchFilter,$dql,$queryParameters);
                    $dql->andWhere("lastname.status='valid'");
                } else {
                    //search can be both: lastname or mrn number
                    //$dql->andWhere("lastname.field LIKE :search");
                    //$queryParameters['search'] = "%".$searchFilter."%";
                    if( strpos((string)$searchFilter, ',') === false ) {
                        //echo "no commas in search <br>";
                        $lastnameOrMrn = "LOWER(lastname.field) LIKE LOWER(:search) OR (mrn.field = :searchMrn AND mrn.keytype = :keytype)";
                        //$lastnameOrMrn = "lastname.field LIKE :search OR (mrn.field = :searchMrn)";
                        $queryParameters['search'] = "%" . $searchFilter . "%";
                        $queryParameters['searchMrn'] = $searchFilter;
                        $queryParameters['keytype'] = $mrntypeFilter; //->getId()?
                        $dql->andWhere($lastnameOrMrn);
                        $dql->andWhere("lastname.status='valid'");
                        $mergeMrn = $searchFilter;
                    } else {
                        //If a comma is present, treat the string to the left of the comma as the Beginning of a last name
                        // and the string to the right of the comma (if any non-space characters are present) as the Beginning of a last name.
                        //echo "comma exists in search<br>";
                        $namesArr = explode(",",$searchFilter);
                        if( count($namesArr) == 2 ) {
                            $latentLastname = $namesArr[0];
                            $latentFirstname = $namesArr[1];
                            //echo "0: [$latentLastname] [$latentFirstname]<br>";
                            if( $latentLastname && $latentFirstname ) {
                                $latentLastname = trim((string)$latentLastname);
                                $latentFirstname = trim((string)$latentFirstname);
                                //echo "1: [$latentLastname] [$latentFirstname]<br>";
                                $lastnameOrMrn = "(LOWER(lastname.field) LIKE LOWER(:searchLastname) AND LOWER(firstname.field) LIKE LOWER(:searchFirstname)) OR (mrn.field = :searchMrn AND mrn.keytype = :keytype)";
                                $queryParameters['searchLastname'] = "%" . $latentLastname . "%";
                                $queryParameters['searchFirstname'] = "%" . $latentFirstname . "%";
                                $queryParameters['searchMrn'] = $searchFilter;
                                $queryParameters['keytype'] = $mrntypeFilter; //->getId()?
                                $dql->andWhere($lastnameOrMrn);
                                $dql->andWhere("lastname.status='valid'");
                                $dql->andWhere("firstname.status='valid'");
                                $mergeMrn = $searchFilter;
                            }
                            if( $latentLastname && !$latentFirstname ) {
                                //echo "2: [$latentLastname]<br>";
                                $lastnameOrMrn = "(LOWER(lastname.field) LIKE LOWER(:searchLastname)) OR (mrn.field = :searchMrn AND mrn.keytype = :keytype)";
                                $queryParameters['searchLastname'] = "%" . $latentLastname . "%";
                                $queryParameters['searchMrn'] = $searchFilter;
                                $queryParameters['keytype'] = $mrntypeFilter; //->getId()?
                                $dql->andWhere($lastnameOrMrn);
                                $dql->andWhere("lastname.status='valid'");
                                $mergeMrn = $searchFilter;
                            }
                        }
                    }
                    //echo "keytype=".$queryParameters['keytype']."<br>";
                }
            } else {
                //echo "integer $searchFilter<br>";
                $dql->andWhere("mrn.field = :search");
                $queryParameters['search'] = $searchFilter;

                //$dql->andWhere("mrn.field LIKE :search");
                //$queryParameters['search'] = "%".$searchFilter."%";

                $mergeMrn = $searchFilter;

                if( $mrntypeFilter ) {
                    $dql->andWhere("mrn.keytype = :keytype");
                    $queryParameters['keytype'] = $mrntypeFilter;
                }
            }
        }//if searchFilter

        //filter only by mrn type even if the search parameter is empty
//        if( $mrntypeFilter ) {
//            $dql->andWhere("mrn.keytype = :keytype");
//            $queryParameters['keytype'] = $mrntypeFilter;
//        }

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

        if( $entryTags ) {
            $entryTagsArr = array();
            foreach( $entryTags as $entryTag ) {
                //echo "entryTag=".$entryTag->getId()."<br>";
                $entryTagsArr[] = $entryTag->getId();
            }
            //echo "entryTagsArr=".count($entryTagsArr)."<br>";
            if( count($entryTagsArr) > 0 ) {
                $dql->leftJoin("calllogEntryMessage.entryTags", "entryTags");
                $dql->andWhere("entryTags.id IN (:entryTags)");
                $queryParameters['entryTags'] = $entryTagsArr;  //implode(",", $entryTagsArr);
                $advancedFilter++;
            }
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

        if( $institutionFilter ) {
            //echo "inst id=".$institutionFilter->getId()."<br>";
            $dql->leftJoin("currentLocation.institution","currentLocationInstitution");
            //$dql->andWhere("currentLocationInstitution.id=".$institutionFilter->getId());
            $dql->andWhere("currentLocationInstitution.id=:currentLocationInstitutionId");
            $queryParameters['currentLocationInstitutionId'] = $institutionFilter->getId();
            $advancedFilter++;
        }

        //messageStatus
        //$messageStatusFilter = $filterform['messageStatus']->getData();
//        if( !$messageStatusFilter ) {
//            $messageStatusFilter = "All except deleted";
//        }
        if( $messageStatusFilter ) {
            $advancedFilter++;
            //echo "string=[$messageStatusFilter]<br>";
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
            $dql->leftJoin("calllogEntryMessage.patientLists","patientList");
            //show message if the message's patient has been removed from the patient list (disabled)?
            $patientListEntityStr = "patientList=:patientList";
            $dql->andWhere($patientListEntityStr);
            $queryParameters['patientList'] = $patientListTitleFilter;

            $advancedFilter++;
        }

        //"Entry Body": The value entered in this field should be searched for in the "History/Findings" and "Impression/Outcome" fields
        // (with an "OR" - a match in either one should list the entry).
        if( $entryBodySearchFilter ) {
            //echo "entryBodySearchFilter=".$entryBodySearchFilter."<br>";
            //UNSIGNED is not defined in SQL server version used in pacsvendor => use INTEGER
            //use custom CastFunction
            $castAs = "INTEGER";
            if( $this->getParameter('database_driver') == 'pdo_mysql' ) {
                $castAs = "UNSIGNED";
            }
            $fromEntity = "App\\UserdirectoryBundle\\Entity\\ObjectTypeText"; //"AppUserdirectoryBundle:ObjectTypeText";
            $entryBodySearchStr =
                //"SELECT s FROM AppUserdirectoryBundle:ObjectTypeText s WHERE " .
                "SELECT s FROM $fromEntity s WHERE " .
                "(message.id = CAST(s.entityId AS ".$castAs.") AND s.entityName='Message' AND " .
                "( (LOWER(s.value) LIKE LOWER(:entryBodySearch)) OR (LOWER(s.secondaryValue) LIKE LOWER(:entryBodySearch))  )" .
                ")";
            $dql->andWhere("EXISTS (" . $entryBodySearchStr . ")");
            $queryParameters['entryBodySearch'] = "%" . $entryBodySearchFilter . "%";

            $advancedFilter++;
        }

        if( $patientPhone ) {
            $phoneCanonical = $calllogUtil->obtainPhoneCanonical($patientPhone);
            $dql->andWhere("patient.phoneCanonical LIKE :patientPhone");
            $queryParameters['patientPhone'] = "%" . $phoneCanonical . "%";
            $advancedFilter++;
        }
        if( $patientEmail ) {
            $emailCanonical = strtolower($patientEmail);
            $dql->andWhere("patient.emailCanonical LIKE :patientEmail");
            $queryParameters['patientEmail'] = "%" . $emailCanonical . "%";
            $advancedFilter++;
        }

        if( $attachmentType ) {
            $dql->leftJoin("calllogEntryMessage.documents","documents");
            if( $attachmentType == "With attachments" ) {
                //$dql->andWhere("calllogEntryMessage.calllogAttachmentType IS NOT NULL");
                //$dql->leftJoin("calllogEntryMessage.documents","documents");
                $dql->andWhere("documents.id IS NOT NULL");
            } elseif( $attachmentType == "Without attachments" ) {
//                $dql->andWhere("calllogEntryMessage.calllogAttachmentType IS NULL");
                //$dql->leftJoin("calllogEntryMessage.documents","documents");
                $dql->andWhere("documents.id IS NULL");
            } else {
                $dql->andWhere("documents.id IS NOT NULL");
                $dql->andWhere("calllogEntryMessage.calllogAttachmentType = :calllogAttachmentTypeId");
                $queryParameters['calllogAttachmentTypeId'] = $attachmentType;
            }
            $advancedFilter++;
        }

        if( $messageId ) {
            $versionId = NULL;
            if (strpos((string)$messageId, '.') !== false) {
                //OID has '.'
                $messageIdArr = explode('.',$messageId);
                if( count($messageIdArr) == 2 ) {
                    $messageId = $messageIdArr[0];
                    $versionId = $messageIdArr[1];
                } elseif( count($messageIdArr) > 2 ) {
                    $messageId = $messageIdArr[0];
                }
            }

            //oid and version - must be integer
            if ( filter_var($messageId, FILTER_VALIDATE_INT) !== false ) {
                //$messageId is integer
                if( $messageId && $versionId && filter_var($versionId, FILTER_VALIDATE_INT) !== false ) {
                    //$versionId is integer
                    $dql->andWhere("message.oid = :messageOid AND message.version = :versionId");
                    $queryParameters['messageOid'] = $messageId;
                    $queryParameters['versionId'] = $versionId;
                } elseif ($messageId) {
                    $dql->andWhere("message.oid = :messageOid");
                    $queryParameters['messageOid'] = $messageId;
                }
            }
            $advancedFilter++;
        }

        //echo "initialCommunicationFilter=$initialCommunicationFilter <br>";
        if( $initialCommunicationFilter ) {
            $dql->andWhere("referringProviders.referringProviderCommunication = :referringProviderCommunicationId");
            $queryParameters['referringProviderCommunicationId'] = $initialCommunicationFilter; //$initialCommunicationFilter->getId();
            $advancedFilter++;
        }

        if ($accessionTypeFilter) {
            $dql->andWhere("accessionaccession.keytype = :accessionKeytypeId");
            $queryParameters['accessionKeytypeId'] = $accessionTypeFilter->getId();
            $advancedFilter++;
        }
        if ($accessionNumberFilter) {
            $dql->andWhere("LOWER(accessionaccession.field) LIKE LOWER(:accessionNumber)");
            $queryParameters['accessionNumber'] = "%" . $accessionNumberFilter . "%";
            $advancedFilter++;
        }

//        $tasks = array(
//            "With or without tasks" =>      "with-without-tasks",
//            "With Tasks" =>                 "with-tasks",
//            "With Any Outstanding Tasks" => "with-outstanding-tasks",
//            "With Any Completed Tasks" =>   "with-completed-tasks",
//            "Without Tasks" =>              "without-tasks"
//        );
        //echo "task=".$task."<br>";
        if( $task == "with-without-tasks" ) {
//            $dql->andWhere("calllogEntryMessage.task IS NOT NULL");
            $advancedFilter++;
        }
        if( $task == "with-tasks" ) {
            $dql->andWhere("calllogTasks.id IS NOT NULL");
            $advancedFilter++;
        }
        if( $task == "with-outstanding-tasks" ) {
            $dql->andWhere("calllogTasks.status = false");
            $advancedFilter++;
        }
        if( $task == "with-completed-tasks" ) {
            $dql->andWhere("calllogTasks.status = true");
            $advancedFilter++;
        }
        if( $task == "without-tasks" ) {
            $dql->andWhere("calllogTasks.id IS NULL");
            $advancedFilter++;
        }

        //taskType
        if( $taskType ) {
            $dql->leftJoin("calllogTasks.calllogTaskType","calllogTaskType");
            $dql->andWhere("calllogTaskType.id = :taskTypeId");
            $queryParameters['taskTypeId'] = $taskType->getId();
            $advancedFilter++;
        }

        //taskUpdatedBy statusUpdatedBy
        if( $taskUpdatedBy ) {
            $dql->leftJoin("calllogTasks.statusUpdatedBy","statusUpdatedBy");
            $dql->andWhere("statusUpdatedBy.id = :statusUpdatedById");
            $queryParameters['statusUpdatedById'] = $taskUpdatedBy->getId();
            $advancedFilter++;
        }

        if( $taskAddedBy ) {
            $dql->leftJoin("calllogTasks.createdBy","createdBy");
            $dql->andWhere("createdBy.id = :createdById");
            $queryParameters['createdById'] = $taskAddedBy->getId();
            $advancedFilter++;
        }

        ///////////////// search in navbar /////////////////
        if( $calllogsearchtype && $calllogsearch ) {
            if( $calllogsearchtype == 'MRN or Last Name' ) {
                //use regular filter by replacing an appropriate filter string
            }
            if( $calllogsearchtype == $defaultMrnType ) {
                $dql->andWhere("mrn.field = :search");
                $queryParameters['search'] = $calllogsearch;
                //add AND type MRN Type="NYH MRN"
                $dql->andWhere("mrn.keytype = :keytype");
                $queryParameters['keytype'] = $defaultMrnType;  //$defaultMrnType->getId();
                $mergeMrn = $calllogsearch;
            }
            if( $calllogsearchtype == 'Last Name' || $calllogsearchtype == 'Last Name similar to' ) {
                if( $metaphone ) {
                    $userServiceUtil->getMetaphoneLike("lastname.field", "lastname.fieldMetaphone", $calllogsearch, $dql, $queryParameters);
                } else {
                    $dql->andWhere("lastname.status='valid'");
                    $dql->andWhere("LOWER(lastname.field) LIKE LOWER(:search)");
                    $queryParameters['search'] = "%".$calllogsearch."%";
                }
            }
//            if( $calllogsearchtype == 'Message Type' ) {
//                $messageCategoryEntity = $em->getRepository('AppOrderformBundle:MessageCategory')->find($calllogsearch);
//                if( $messageCategoryEntity ) {
//                    $nodeChildSelectStr = $messageCategoryEntity->selectNodesUnderParentNode($messageCategoryEntity, "messageCategory",$selectOrder);
//                    $dql->andWhere($nodeChildSelectStr);
//                } else {
//                    $dql->andWhere("1=0");
//                }
//            }
            if( $calllogsearchtype == 'Entry full text' ) {
                //use regular filter by replacing an appropriate filter string
            }
            //exit("1 [$calllogsearchtype] : [$calllogsearch]");
        }
        //exit('2');
        ///////////////// EOF search in navbar /////////////////

        //check potential merged MRN
        if( $mergeMrn ) {
            $mergeMrnKeytype = null;
            $mergedPatients = array();
            $mergedPatientIds = array();

            if( isset($queryParameters['keytype']) ) {
                $mergeMrnKeytype = $queryParameters['keytype'];
            }

            //if ( strval($mergeMrnKeytype) != strval(intval($mergeMrnKeytype)) ) {
            if( is_object($mergeMrnKeytype) ) {
                //string
                $mergeMrnKeytypeId = $mergeMrnKeytype->getId();
            } else {
                //integer
                $mergeMrnKeytypeId = $mergeMrnKeytype;
            }
            //$mergeMrnKeytypeId = $mergeMrnKeytype->getId();
            //echo "mergeMrnKeytypeId=".$mergeMrnKeytypeId."<br>";

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
            $thisPatient = $em->getRepository(Patient::class)->findByValidMrnAndMrntype($mergeMrn,$mergeMrnKeytypeId);
            if( $thisPatient ) {
                //echo "thisPatient=".$thisPatient."<br>";
                $mergedPatients = $calllogUtil->getAllMergedPatients(array($thisPatient));
                //echo "mergedPatients=".count($mergedPatients)."<br>";
            }

            foreach ($mergedPatients as $mergedPatient) {
                if( $thisPatient->getId() != $mergedPatient->getId() ) {
                    //echo "mergedPatient=" . $mergedPatient->getId() . "<br>";
                    $mergedPatientIds[] = $mergedPatient->getId();
                }
            }
            //echo "mergedPatient count=" . count($mergedPatientIds) . "<br>";
            if( count($mergedPatientIds) > 0 ) {
                $dql->orWhere("patient.id IN (:mergePatientIds)");
                $queryParameters['mergePatientIds'] = $mergedPatientIds;
            }
        }

        //$sortBy
        //'Sort by date of entry creation, latest first' => 'sort-by-creation-date', (default)
        //'Sort by date of latest edit, latest first' => 'sort-by-latest-edit-date'
//        echo "sortBy=$sortBy <br>";
//        //exit('111');
//        if( $sortBy ) {
//            if( $sortBy == "sort-by-creation-date" ) {
//                //default: do nothing. Sort by ID in paginator
//            }
//            if( $sortBy == "sort-by-latest-edit-date" ) {
//
//            }
//        }

        //$dql->orderBy("message.id","DESC");

        //$limit = 10;
        //$query = $dql->getQuery(); //$query = $em->createQuery($dql);
        $query = $dql->getQuery();
        $query->setParameters($queryParameters);

        //$logger = $this->container->get('logger');
        //$logger->notice("setMaxResults limit=".$limit);
        if( $limit ) {
            $query->setMaxResults($limit);
        }

        //echo "query=".$query->getSql()."<br>";
        //$messages = $query->getResult();
        //echo "messages count=".count($messages)."<br>";

        $res = array(
            'query' => $query,
            'filterform' => $filterform,
            'advancedFilter' => $advancedFilter,
            'redirect' => null
        );

        return $res;

//        $paginator  = $this->container->get('knp_paginator');
//        $messages = $paginator->paginate(
//            $query,
//            $this->container->get('request')->query->get('page', 1), /*page number*/
//            //$request->query->getInt('page', 1),
//            $limit      /*limit per page*/
//        );
//        //echo "messages count=".count($messages)."<br>";
//
//        //all messages will show only form fields for this message category node
////        $categoryStr = "Pathology Call Log Entry";
////        $messageCategoryInfoNode = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($categoryStr);
////        if( !$messageCategoryInfoNode ) {
////            throw new \Exception( "MessageCategory type is not found by name '".$categoryStr."'" );
////        }
//
//        $eventObjectType = $em->getRepository(EventObjectTypeList::class)->findOneByName("Message");
//        if( $eventObjectType ) {
//            $eventObjectTypeId = $eventObjectType->getId();
//        } else {
//            $eventObjectTypeId = null;
//        }
//
//        $defaultPatientListId = null;
//        $defaultPatientList = $calllogUtil->getDefaultPatientList();
//        if( $defaultPatientList ) {
//            $defaultPatientListId = $defaultPatientList->getId();
//        }
//
//        return array(
//            'messages' => $messages,
//            'alerts' => $alerts,
//            'title' => $title,
//            'filterform' => $filterform->createView(),
//            'route_path' => $route,
//            'advancedFilter' => $advancedFilter,
//            //'messageCategoryInfoNode' => $messageCategoryInfoNode, //all messages will show only form fields for this message category node
//            'eventObjectTypeId' => $eventObjectTypeId,
//            'patientListId' => $defaultPatientListId,
//            'shownavbarfilter' => false
//            //'navbarfilterform' => $navbarfilterform->createView()
//            //'sitename' => $this->getParameter('calllog.sitename')
//            //'calllogsearch' => $calllogsearch,
//            //'calllogsearchtype' => $calllogsearchtype,
//        );

    }
    public function isFilterEmpty($filterform) {
        $data = $filterform->getData();
        if( !$data ) {
            return true;
        }
//        echo "<pre>";
//        print_r($data);
//        echo "</pre>";
        foreach( $data as $key=>$value ) {
            if( $value ) {
                //echo $key.": value=".$value."<br>";
                if( is_array($value) || $value instanceof ArrayCollection ) {
                    //echo $key.": value is array=".$value."<br>";
                    foreach($value as $thisValue ) {
                        if( $thisValue ) {
                            //echo $key.": filterform not empty: thisValue=".$thisValue."<br>";
                            return false;
                        }
                    }
                } else {
                    //echo $key.": filterform not empty: value=".$value."<br>";
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * <li><a href="{{ path(calllog_sitename~'_tasks_todo') }}">To Do</a></li>
     * <li><a href="{{ path(calllog_sitename~'_tasks_i_added') }}">Tasks I Added</a></li>
     * <li><a href="{{ path(calllog_sitename~'_tasks_i_updated') }}">Tasks I Updated</a></li>
     */
    #[Route(path: '/tasks/to-do', name: 'calllog_tasks_todo')]
    #[Route(path: '/tasks/i-added', name: 'calllog_tasks_i_added')]
    #[Route(path: '/tasks/i-updated', name: 'calllog_tasks_i_updated')]
    public function listTasksAction(Request $request)
    {
        if (false == $this->isGranted("ROLE_CALLLOG_USER")) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $user = $this->getUser();
        $routename = $request->get('_route');

        if( $routename == "calllog_tasks_todo" ) {
            return $this->redirectToRoute('calllog_home',
                array(
                    'filter[messageStatus]' => "All except deleted",
                    'filter[task]' => "with-outstanding-tasks"
                )
            );
        }

        if( $routename == "calllog_tasks_i_added" ) {
            return $this->redirectToRoute('calllog_home',
                array(
                    'filter[messageStatus]' => "All except deleted",
                    'filter[taskAddedBy]' => $user->getId()
                )
            );
        }

        if( $routename == "calllog_tasks_i_updated" ) {
            return $this->redirectToRoute('calllog_home',
                array(
                    'filter[messageStatus]' => "All except deleted",
                    'filter[taskUpdatedBy]' => $user->getId()
                )
            );
        }

    }


    /**
     * Call Entry New Page
     * http://localhost/order/call-log-book/entry/new?mrn-type=4&mrn=3
     */
    #[Route(path: '/entry/new', name: 'calllog_callentry')]
    #[Template('AppCallLogBundle/CallLog/call-entry.html.twig')]
    public function callEntryAction(Request $request)
    {
        if( false == $this->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //1) search box: MRN,Name...

        $user = $this->getUser();
        $securityUtil = $this->container->get('user_security_utility');
        $calllogUtil = $this->container->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;
        $userSecUtil = $this->container->get('user_security_utility');
        $orderUtil = $this->container->get('scanorder_utility');
        $em = $this->getDoctrine()->getManager();
        $sitename = $this->getParameter('calllog.sitename');

        $mrn = trim((string)$request->get('mrn'));
        $mrntype = trim((string)$request->get('mrntype'));
        $encounterNumber = trim((string)$request->get('encounter-number'));
        $encounterTypeId = trim((string)$request->get('encounter-type'));
        //$encounterVersion = trim((string)$request->get('encounter-version'));
        $messageTypeId = trim((string)$request->get('message-type'));

        //check if user has at least one institution
//        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
//        if( !$userSiteSettings ) {
//            $orderUtil->setWarningMessageNoInstitution($user);
//            return $this->redirect( $this->generateUrl('calllog_home') );
//        }
//        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
//        if( count($permittedInstitutions) == 0 ) {
//            $orderUtil->setWarningMessageNoInstitution($user);
//            return $this->redirect( $this->generateUrl('calllog_home') );
//        }
        //$permittedInstitutions = $orderUtil->getAndAddAtleastOneInstitutionPHI($user,$this->container->get('session'));
        $permittedInstitutions = $orderUtil->getAndAddAtleastOneInstitutionPHI($user,$request->getSession());
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('calllog_home') );
        }


        $title = "New Entry";
        $titleheadroom = null;

        $system = $securityUtil->getDefaultSourceSystem($this->getParameter('calllog.sitename'));
        $cycle = 'new';
        $formtype = 'call-entry';
        $readonlyPatient = false;
        $readonlyEncounter = false;
        $patient = null;
        $encounter1 = null;
        $encounter2 = null;

        //redirect logic for Same Patient, Same/New Encounter
        if( ($mrntype && $mrn) || ($encounterTypeId && $encounterNumber) ) {
            return $this->redirect($this->generateUrl('calllog_callentry_same_patient', array(
                'mrn'=>$mrn,
                'mrntype'=>$mrntype,
                'encounter-number'=>$encounterNumber,
                'encounter-type'=>$encounterTypeId,
                'message-type'=>$messageTypeId
            )));
        }

        $institution = $userSecUtil->getCurrentUserInstitution($user);

        //create patient
        $patient = new Patient(true, 'valid', $user, $system);
        $patient->setInstitution($institution);

        //set patient record status "Active"
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:PatientRecordStatusList'] by [PatientRecordStatusList::class]
        $patientActiveStatus = $em->getRepository(PatientRecordStatusList::class)->findOneByName("Active");
        if( $patientActiveStatus ) {
            $patient->setPatientRecordStatus($patientActiveStatus);
        }

        //create dummy encounter #1 just to display fields in "Patient Info"
        $encounter1 = new Encounter(true,'dummy',$user,$system);
        $encounter1->setProvider($user);
        $patient->addEncounter($encounter1); //add new encounter to patient

        //this will create a new patient and encounter
        //$em->persist($patient);
        //$em->persist($encounter1);

//        $encounter2 = $em->getRepository('AppOrderformBundle:Encounter')->findOneEncounterByNumberAndType($encounterTypeId,$encounterNumber);
//
//        //check whether patient MRN supplied in the URL corresponds to the supplied encounter number.
//        // If it does not, show the normal /entry/new page but with the notification "
//        // Encounter "1111" of type "blah" is not with patient whose MRN of type "whatever" is "1111"
//        if( $mrn && $mrntype && $encounter2 ) {
//            if( !$em->getRepository('AppOrderformBundle:Encounter')->isPatientEncounterMatch($mrn,$mrntype,$encounter2) ) {
//
//                $mrntypeStr = "";
//                $mrntypeEntity = $em->getRepository('AppOrderformBundle:MrnType')->find($mrntype);
//                if( $mrntypeEntity ) {
//                    $mrntypeStr = $mrntypeEntity->getName()."";
//                }
//
//                $encounterMsg = "Encounter $encounterNumber of type ".$encounter2->obtainEncounterNumber()." is not with patient whose MRN of type $mrntypeStr is $mrn";
//                $this->addFlash(
//                    'warning',
//                    $encounterMsg
//                );
//
//                $encounter2 = null;
//            }
//        }

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

            //TODO: encounter drop down should be:
            //[Autogenerated new ID, selected by default]
            //[Previous ID (MM/DD/YYYY)]
            //[Older Previous ID (MM/DD/YYYY)]



            //set encounter date and time
            $date = $encounter2->getDate()->first();
            $userTimeZone = $user->getPreferences()->getTimezone();
            if( !$userTimeZone ) {
                $userTimeZone = "America/New_York";
            }
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

            //set Initial Communication to "Inbound" ($encounter2->referringProviders->referringProviderCommunication->referringProviderCommunication)
            $referingProvider = null;
            $referingProviders = $encounter2->getReferringProviders();
            if( count($referingProviders) > 0 ) {
                $referingProvider = $referingProviders[0];
            } else {
                //__construct( $status = 'valid', $provider = null, $source = null )
                //$encounterReferringProvider = new EncounterReferringProvider('valid',$user,$system);
                //$encounter2->addReferringProvider($encounterReferringProvider);
            }
            if( $referingProvider ) {
                //$defaultCommunication = $em->getRepository('AppUserdirectoryBundle:HealthcareProviderCommunicationList')->findOneByName("Inbound");
                $defaultCommunication = $userSecUtil->getSiteSettingParameter('defaultInitialCommunication',$sitename);
                if( $defaultCommunication ) {
                    $referingProvider->setReferringProviderCommunication($defaultCommunication);
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
            //$encounter2->addContactinfoByTypeAndName($user, $system, $encounterLocationType, $locationName, $spotEntity, $withdummyfields, $em, $removable);
            $encounter2 = $calllogUtil->addDefaultLocation($encounter2,$user,$system);
        }//!$encounter2

        //add new encounter to patient
        $patient->addEncounter($encounter2);

        $message = $this->createCalllogEntryMessage($user,$permittedInstitutions,$system,$messageTypeId); //new

        //set patient list
        $patientList = $calllogUtil->getDefaultPatientList();
        //echo "patientList ID=".$patientList->getId().": ".$patientList."<br>";
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

        //testing
//        $calllogEntryMessage = $message->getCalllogEntryMessage();
//        $tasks = $calllogEntryMessage->getCalllogTasks();
//        echo "tasks count=".count($tasks)."<br>";

        $showPreviousEncounters = true;
        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle,$readonlyEncounter,$showPreviousEncounters); //entry/new

        //testing
//        $calllogEntryMessage = $message->getCalllogEntryMessage();
//        $tasks = $calllogEntryMessage->getCalllogTasks();
//        echo "tasks count=".count($tasks)."<br>";

        //$encounterid = $calllogUtil->getNextEncounterGeneratedId();

        $formnodetrigger = 1;
        if( $messageTypeId ) {
            $formnodetrigger = 0; //build formnodes from top to bottom
        }

        //top message category id
        $formnodeTopHolderId = null;
        $messageCategory = $calllogUtil->getDefaultMessageCategory();
        //$categoryStr = "Pathology Call Log Entry";
        //$messageCategory = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($categoryStr);
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment != 'live' ) {
            $this->addFlash(
                'pnotify-error',
                "THIS IS A TEST SERVER. USE ONLY FOR TESTING !!!"
            );
        }

        $calllogViewMode = $calllogUtil->getDefaultViewMode();

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
            'readonlyEncounter' => $readonlyEncounter,
            'showPreviousEncounters' => $showPreviousEncounters,
            'calllogViewMode' => $calllogViewMode
            //'encounterid' => $encounterid
        );
    }

    /**
     * Save Call Log Entry
     */
    #[Route(path: '/entry/save', name: 'calllog_save_entry', methods: ['POST'], options: ['expose' => true])]
    #[Template('AppCallLogBundle/CallLog/call-entry.html.twig')]
    public function saveEntryAction(Request $request)
    {
        if( false == $this->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //exit('save entry');
        //case 1: patient exists: create a new encounter to DB and add it to the existing patient
        //add patient id field to the form (id="oleg_calllogbundle_patienttype_id") or use class="calllog-patient-id" input field.
        //case 2: patient does not exists: create a new encounter to DB

        $user = $this->getUser();
        $securityUtil = $this->container->get('user_security_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $orderUtil = $this->container->get('scanorder_utility');
        $calllogUtil = $this->container->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;
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

//        $mrn = trim((string)$request->get('mrn'));
//        $mrntype = trim((string)$request->get('mrntype'));
        $mrn = null;
        $mrntype = null;
        $accession = null;

        $title = "Save Entry";

        $system = $securityUtil->getDefaultSourceSystem($this->getParameter('calllog.sitename'));
        $status = 'valid';
        $cycle = 'new';
        $formtype = 'call-entry';

        $message = $this->createCalllogEntryMessage($user,$permittedInstitutions,$system); //save

        // Create an ArrayCollection of the current Task objects in the database
        $originalTasks = new ArrayCollection();
        foreach($message->getCalllogEntryMessage()->getCalllogTasks() as $task) {
            $originalTasks->add($task);
        }

        $showPreviousEncounters = true;
        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle,false,$showPreviousEncounters); ///entry/save

        $form->handleRequest($request);


        //testing
//        foreach($message->getPatient() as $pat ) {
//            foreach($pat->getEncounter() as $enc) {
//                echo "keys=".count($enc->obtainKeyField())."<br>";
//                foreach($enc->obtainKeyField() as $key) {
//                    echo "key=".$key."<br>";
//                }
//            }
//        }
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

        //app_calllogbundle_patienttype[id]
        //$formPatientId = $form["id"]->getData();
        //echo "1: formPatientId=".$formPatientId."<br>";

        //app_calllogbundle_patienttype[encounter][0][patfirstname][0][field]
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

            //testing task
//            $tasks = $message->getCalllogEntryMessage()->getCalllogTasks();
//            foreach ($tasks as $task) {
//                echo "Task: created=".$task->getCreatedBy()."<br>";
//            }
//            $crnEntryMessage = $message->getCrnEntryMessage();
//            echo "crnEntryMessage=".$crnEntryMessage->getId()."<br>";
//            exit('111');



            $msg = "No Case found. No action has been performed.";
            $institution = $userSecUtil->getCurrentUserInstitution($user);

            $patients = $message->getPatient();
            if( count($patients) != 1 ) {
                throw new \Exception( "Message must have only one patient. Patient count= ".count($patients)."'" );
            }
            $patient = $patients->first();

            //it should work for mysql, mssql, but in postgres DB's id is already pre-genarated even when object is in the pre-persisting stage with "new" (new Patient)
            if( $patient->getId() ) {
                $existingPatientDB = true;
            } else {
                $existingPatientDB = false;
            }

            //echo "message id=".$message->getId()."<br>";
            //echo "patient id=".$patient->getId()."<br>";

            //if $previousEncounterId set => use this encounter, $previousEncounterId is null => don't change anything and use existing message encounter
            //$previousEncounter = null;
            $previousEncounterId = $form->get("previousEncounterId")->getData();
            //echo "previousEncounterId=".$previousEncounterId."<br>";
            //$previousEncounterId = 99999999; //testing

            //Check if $previousEncounter is really exists, however it's not supposed to happen
            if( $previousEncounterId ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
                $checkPreviousEncounter = $em->getRepository(Encounter::class)->find($previousEncounterId);
                if( !$checkPreviousEncounter ) {
                    //Recovery for previous encounter is not found by ID
                    $logger = $this->container->get('logger');
                    $previousEncounterErrorMsg = "Previous encounter is not found by ID=".$previousEncounterId . ". Create a new encounter. Current user is ".$user;
                    $logger->warning($previousEncounterErrorMsg);
                    $emailUtil = $this->container->get('user_mailer_utility');
                    $siteEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
                    //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
                    $emailUtil->sendEmail($siteEmail,"Previous encounter is not found by ID",$previousEncounterErrorMsg);

                    //set $previousEncounterId to null. This will create a new Encounter
                    $previousEncounterId = null;
                }
            }

//            $previousEncounters = $form->get("previousEncounters")->getData();
//            echo "previousEncounters=".$previousEncounters."<br>";
//            echo "previousEncounters count=".count($previousEncounters)."<br>";
//            foreach($previousEncounters as $previousEncounter) {
//                echo "previousEncounter=".$previousEncounter->obtainEncounterNumberOnlyAndDate()."<br>";
//            }

            //exit('111');

            //if accession number exists => create new Accession and link it to Message and Patient (if exists)
            //add accession for patient info section
            if(1) {
                $accessionType = null;
                $accessionNumber = null;
                if( $form->has('accessionType') ) {
                    $accessionType = $form['accessionType']->getData();
                }
                if( $form->has('accessionNumber') ) {
                    $accessionNumber = $form['accessionNumber']->getData();
                }

                //echo "accession: typeName=".$accessionType.", typeID=".$accessionType->getId().", number=".$accessionNumber."<br>";
                //exit('before adding accession');
                if( $accessionType && $accessionNumber ) {
                    //exit('before adding accession');
//                    $status = 'valid';
//                    $sourcesystem = $securityUtil->getDefaultSourceSystem($this->getParameter('calllog.sitename'));
//                    $accession = new Accession(false, $status, $user, $sourcesystem); //$withfields=false, $status='invalid', $provider=null, $source=null
//                    $accessionAccession = new AccessionAccession($status, $user, $sourcesystem);
//                    //add accession type
//                    $accessionAccession->setKeytype($accessionType);
//                    //add accession number
//                    $accessionAccession->setField($accessionNumber);
//                    $accessionDate = new AccessionAccessionDate($status, $user, $sourcesystem);
//                    $accession->addAccession($accessionAccession);
//                    $accession->addAccessionDate($accessionDate);

                    //$accessionExistingRes = $calllogUtil->getPatientsByAccessions($request,$accessionNumber,$accessionType); // /entry/save
                    //$accessionExistingOutput = $accessionExistingRes['output'];
                    //$accessionExistingPatients = $accessionExistingRes['patients'];
//                    if( count($accessionExistingPatients) > 0 ) {
//                        foreach($accessionExistingPatients as $accessionExistingPatient) {
//                            //compare patients
//                        }
//                        throw new \Exception("Can not add a new Accession number $accessionNumber ($accessionType) to this Patient with ID #".$patient->getId().": ".$accessionExistingOutput);
//                    }

                    $accessionParams = array();
                    $accessionParams['accessiontype'] = $accessionType;
                    $accessionParams['accessionnumber'] = $accessionNumber;
                    $patientsDataStrict = $calllogUtil->searchPatientByAccession($request, $accessionParams, false);
                    $patientsStrict = $patientsDataStrict['patients'];
                    if (array_key_exists("accessionFound", $patientsDataStrict)) {
                        $accessionFound = $patientsDataStrict['accessionFound'];
                    } else {
                        $accessionFound = false;
                    }
                    //$accessionFound but (count(patients) == 0) => found patient by accession does not match entered patient's info
                    if( $accessionFound && count($patientsStrict) == 0 ) {
                        throw new \Exception(
                            "Found patient by accession does not match entered patient's info. ".
                            "Can not add a new Accession number $accessionNumber ($accessionType) to this Patient: ".
                            $patient->obtainPatientInfoTitle('valid',null,false)
                        );
                    }

                    $accession = $calllogUtil->createNewOrFindExistingAccession($accessionNumber,$accessionType,$user); // /entry/save
                    $em->persist($accession);
                    $message->addAccession($accession);

                    //$accessions = $message->getAccession();
                    //echo "accessions [$accessionNumber ($accessionType)] count=".count($accessions)."<br>";
                }
            }
            //exit('after adding accession');

            $patientInfoDummyEncounter = null;
            $newEncounter = null;
            //get a new encounter without id
            foreach( $patient->getEncounter() as $encounter ) {
                //echo "encounter ID=".$encounter->getId()."; status=".$encounter->getStatus()."<br>";
                //if( !$encounter->getId() ) {
                    if( $encounter->getStatus() == 'valid' ) {
                        $newEncounter = $encounter;
                    }
                    if( $encounter->getStatus() == 'dummy' ) {
                        //this encounter is served only to find the patient:
                        //it must be removed from the patient
                        $patientInfoDummyEncounter = $encounter;
                    }
                //}
            }

            //process Task sections
//            // remove the relationship between the CalllogEntryMessage and the Task
//            foreach($originalTasks as $task) {
//                if( false === $message->getCalllogEntryMessage()->getCalllogTasks()->contains($task) ) {
//                    // remove the Task from the Tag
//                    $message->getCalllogEntryMessage()->getCalllogTasks()->removeElement($task);
//                    // if it was a many-to-one relationship, remove the relationship like this
//                    $task->setCalllogEntryMessage(null);
//                    $em->persist($task);
//                    // if you wanted to delete the Tag entirely, you can also do that
//                    $em->remove($task);
//                }
//            }
            //process Task sections
            $taskUpdateStr = $calllogUtil->processCalllogTask($message,$originalTasks); //Save New Call Log Entry

            //process Attached Documents (here this function works, but entityId is NULL - still it's OK)
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
            $em->getRepository(Document::class)->processDocuments($message->getCalllogEntryMessage()); //Save new entry

            //exit('$newEncounter='.$newEncounter);

            //set system source and user's default institution
            if( $newEncounter ) {

                //Update Patient Info from $newEncounter:
                // The values typed into these fields should be recorded as "valid".
                // If the user types in the Date of Birth, it should be added to the "Patient" hierarchy level
                // of the selected patient as a "valid" value and the previous "valid" value should be marked "invalid" on the server side.
                //Use unmapped encounter's "patientDob" to update patient's DOB
                if( $patientInfoDummyEncounter && $patient ) {
                    //dummy $patientInfoDummyEncounter must be removed from the patient
                    $patient->removeEncounter($patientInfoDummyEncounter);
                }

                if( $previousEncounterId ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
                    $newEncounter = $em->getRepository(Encounter::class)->find($previousEncounterId);
                    if( !$newEncounter ) {
                        $previousEncounterErrorMsg = "Previous encounter is not found by ID=" . $previousEncounterId . " Current user is " . $user;
                        throw new \Exception($previousEncounterErrorMsg);
                    }
                } else {
                    ////////////// processing new encounter ///////////////////
                    //exit('processing new encounter');
                    $newEncounter->setSource($system);
                    $newEncounter->setInstitution($institution);
                    $newEncounter->setVersion(1);

                    //assign generated encounter number ID
                    $key = $newEncounter->obtainAllKeyfield()->first();
                    //echo "key=".$key."<br>"; //TODO: test - why key count($newEncounter->obtainAllKeyfield()) == 0 after deprecated removed? because disabled!?
                    //exit('1');
                    if (!$key) {
                        //$newKeys = $newEncounter->createKeyField();
                        //if( count($newKeys) > 0 ) {
                        //    $key = $newKeys->first();
                        //} else {
                        //    throw new \Exception( "CallLog save new Entry Action: Encounter does not have any keys." );
                        //}
                        throw new \Exception("CallLog save new Entry Action: Encounter does not have a key.");
                    }
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
                    //exit("eof processTrackerLocation");

                    //process EncounterReferringProvider: set Specialty, Phone and Email for a new userWrapper (getReferringProviders)
                    $calllogUtil->processReferringProviders($newEncounter, $system);
                    ////////////// EOF processing new encounter ///////////////////

                }//if( !$previousEncounterId )

                //Use $newEncounter
                //backup encounter to message
                $calllogUtil->copyEncounterBackupToMessage($message,$newEncounter);

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
                $calllogUtil->setFormVersions($message,$cycle); //saveEntryAction

                //////////////////// Processing ////////////////////////
                if( $existingPatientDB ) {

                    //CASE 1
                    echo "case 1: patient exists in this Call Entry form: create a new encounter to DB and add it to the existing patient <br>";
                    //get a new encounter without id $newEncounter
    //                foreach( $encounter->getReferringProviders() as $referringProvider ) {
    //                    echo "encounter referringProvider phone=".$referringProvider->getReferringProviderPhone()."<br>";
    //                }

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
                    $patient = $em->getRepository(Patient::class)->find($patient->getId());
                    $message->clearPatient();
                    $message->addPatient($patient);

                    //backup patient to message
                    $calllogUtil->copyPatientBackupToMessage($message,$patient);

                    if( !$previousEncounterId ) {
                        /////////// processing new encounter ///////////
                        //echo "processing new encounter<br>";
                        //reset institution from the patient
                        $newEncounter->setInstitution($patient->getInstitution());

                        $patient->addEncounter($newEncounter);

                        //update patient's last name, first name, middle name, dob, sex, ...
                        $calllogUtil->updatePatientInfoFromEncounter($patient, $newEncounter, $user, $system);
                        /////////// EOF processing new encounter ///////////
                    }

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

                    //Add accession if exists to the patient if exists (via $newEncounter)
                    if( $accession && $patient ) {
                        //Create new Procedure
                        $sourcesystem = $securityUtil->getDefaultSourceSystem($this->getParameter('calllog.sitename'));
                        $procedure = new Procedure(false, 'valid', $user, $sourcesystem);
                        $em->persist($procedure);
                        $newEncounter->addProcedure($procedure);
                        $procedure->addAccession($accession);
                    }

                    //exit('Exit Case 1');
                    //$em->persist($patient);
                    if( !$testing ) {
                        if( !$previousEncounterId ) {
                            //echo "persist new encounter<br>";
                            //new encounter
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
                    
                    if( $previousEncounterId ) {
                        $msg = "Previous Encounter (ID#" . $newEncounter->getId() . ") has been used with number " . $newEncounter->obtainEncounterNumber() . " for the Patient with ID #" . $patient->getId();
                    } else {
                        $msg = "New Encounter (ID#" . $newEncounter->getId() . ") is created with number " . $newEncounter->obtainEncounterNumber() . " for the Patient with ID #" . $patient->getId();
                    }

                } else {
                    //CASE 2
                    echo "case 2: patient does not exists in this Call Entry form: create a new encounter to DB <br>";
                    //app_calllogbundle_patienttype[encounter][0][referringProviders][0][referringProviderPhone]

                    $newEncounter->setPatient(null);

                    //remove empty patient from message
                    $message->removePatient($patient);

                    //exit('Exit Case 2');
                    if( !$testing ) {
                        $em->persist($newEncounter);
                        //$em->flush($newEncounter); //testing
                        $em->persist($message);
                        //$em->flush($message); //testing

                        $em->flush(); //testing
                    }

                    $msg = "New Encounter (ID#" . $newEncounter->getId() . ") is created with number " . $newEncounter->obtainEncounterNumber();
                }
                //////////////////// EOF Processing ////////////////////////


                //set encounter as message's input
                //$message->addInputObject($newEncounter);
                //$em->persist($message);
                //$em->flush($message);

                //process form nodes: process each form field and record it to DB
                $formNodeUtil = $this->container->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request,$message->getMessageCategory(),$message,$testing); //save
                //exit('after formnode');

                $calllogUtil->deleteAllOtherMessagesByOid($message,$cycle,$testing);

                //log search action
                if( $msg ) {
                    $eventType = "New Call Log Book Entry Submitted";

                    $eventStr = $calllogUtil->getEventLogDescription($message,$patient,$newEncounter);
                    //exit('eventStr='.$eventStr);

                    if( $taskUpdateStr ) {
                        $eventStr = $eventStr . "<br><br>" . $taskUpdateStr;
                        $msg = $msg . "<br><br>" . $taskUpdateStr;
                    }

                    //$eventStr = $eventStr . " submitted by " . $user;

                    if( !$testing ) {
                        $userSecUtil->createUserEditEvent($this->getParameter('calllog.sitename'), $eventStr, $user, $message, $request, $eventType); //Save Call Log Entry
                    }
                }

                if( !$testing ) {
                    //send an email to the Preferred Email of the "Attending:"
                    $calllogUtil->sendConfirmationEmail($message, $patient, $newEncounter);
                }

            }//if $newEncounter
            
            //TODO: save call log entry short info to setShortInfo($shortInfo)
            //$calllogUtil->updateMessageShortInfo($message);

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
            'mrntype' => $mrntype,
            'showPreviousEncounters' => $showPreviousEncounters
        );
    }//save

    public function createCalllogEntryForm($message, $mrntype=null, $mrn=null, $cycle='show', $readonlyEncounter=false, $showPreviousEncounters=false) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $calllogUtil = $this->container->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;
        $userSecUtil = $this->container->get('user_security_utility');
        $sitename = $this->getParameter('calllog.sitename');

        //$patient = $message->getPatient()->first();

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
//            $defaultMrnType = $userSecUtil->getSiteSettingParameter('keytypemrn',$sitename);
//            //echo "defaultMrnType=".$defaultMrnType.$defaultMrnType."<br>";
//            if( !$defaultMrnType ) {
//                $defaultMrnType = $em->getRepository('AppOrderformBundle:MrnType')->findOneByName("New York Hospital MRN");
//            }
            $defaultMrnType = $calllogUtil->getDefaultMrnType();
            $mrntype = $defaultMrnType->getId();
        }

        //accessiontype
        //$accessiontypes = $calllogUtil->getAccessionTypes();
        //accessionnumber

        if( $cycle == 'show' ) {
            $disabled = true;
        } else {
            $disabled = false;
        }

        //$timezones
        //$userTimeZone = $user->getPreferences()->getTimezone();
        $userTimeZone = $userSecUtil->getSiteSettingParameter('timezone',$sitename);

        $defaultInstitution = NULL;
        //$defaultInstitution = $userSecUtil->getSiteSettingParameter('institution',$sitename);

        $previousEncounters = NULL;
        if( $showPreviousEncounters ) {
            $previousEncounters = $calllogUtil->getPreviousEncounterByMessage($message);
            //$previousEncounters = array("Encounter 1"=>"Encounter 1", "Encounter 2"=>"Encounter 2", "Encounter 3"=>"Encounter 3");
        }

        $enableDocumentUpload = $userSecUtil->getSiteSettingParameter('enableDocumentUpload',$sitename);

        $defaultAccessionType = $userSecUtil->getSiteSettingParameter('defaultAccessionType',$sitename);
        $showAccession = $userSecUtil->getSiteSettingParameter('showAccession',$sitename);

        $defaultTagTypeId = NULL;
        $defaultTagType = $userSecUtil->getSiteSettingParameter('defaultTagType',$sitename);
        if( $defaultTagType ) {
            $defaultTagTypeId = $defaultTagType->getId();
        }

        $params = array(
            'cycle' => $cycle,  //'new',
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            'type' => null,
            'mrntype' => intval($mrntype),
            'mrn' => $mrn,
            'defaultInstitution' => $defaultInstitution,
            'formtype' => 'call-entry',
            'complexLocation' => false,
            'alias' => true,
            'institution' => true,
            'timezoneDefault' => $userTimeZone,
            'readonlyEncounter' => $readonlyEncounter,
            'previousEncounters' => $previousEncounters,
            'attendingPhysicians-readonly' => false,
            'referringProviders-readonly' => false,
            'readonlyLocationType' => true, //lock the "Location Type" field (with the default "Encounter Location" value in it)
            'enableDocumentUpload' => $enableDocumentUpload,
            'defaultAccessionType' => $defaultAccessionType,
            'showAccession' => $showAccession,
            'defaultTagType' => $defaultTagTypeId
            //'user_security_utility' => $this->container->get('user_security_utility')
            //'accessiontypes' => $accessiontypes
        );

        $form = $this->createForm(
            CalllogMessageType::class,
            $message,
            array(
                'form_custom_value' => $params,
                'form_custom_value_entity' => $message,
                'disabled' => $disabled
            )
        );

        return $form;
    }

    public function createCalllogEntryMessage($user,$permittedInstitutions,$system,$messageCategoryId=null) {
        $em = $this->getDoctrine()->getManager();
        $orderUtil = $this->container->get('scanorder_utility');
        $calllogUtil = $this->container->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;

        $message = new Message();
        $message->setPurpose("For Internal Use by the Department of Pathology for Call Log Book");
        $message->setProvider($user);
        $message->setVersion(1);

        //set Source object
        $source = new Endpoint();
        $source->setSystem($system);
        $message->addSource($source);

        //set order category
        if( $messageCategoryId ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageCategory'] by [MessageCategory::class]
            $messageCategory = $em->getRepository(MessageCategory::class)->find($messageCategoryId);
        } else {
            //$categoryStr = "Pathology Call Log Entry";
            //$categoryStr = "Nesting Test"; //testing
            //$messageCategory = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($categoryStr);
            $messageCategory = $calllogUtil->getDefaultMessageCategory();
        }
        if( !$messageCategory ) {
            throw new \Exception( "Default Message category is not found." );
        }
        $message->setMessageCategory($messageCategory);

        //set Institutional PHI Scope
        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,$message);
        $message->setInstitution($permittedInstitutions->first());

        //set message status "Draft"
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageStatusList'] by [MessageStatusList::class]
        $messageStatus = $em->getRepository(MessageStatusList::class)->findOneByName("Draft");
        if( $messageStatus ) {
            $message->setMessageStatus($messageStatus);
        }

        $calllogEntryMessage = $message->getCalllogEntryMessage();
        if (!$calllogEntryMessage) {
            $calllogEntryMessage = new CalllogEntryMessage();
            $message->setCalllogEntryMessage($calllogEntryMessage);
        }

        //add calllog task
        if( count($calllogEntryMessage->getCalllogTasks()) == 0 ) {
            $task = new CalllogTask($user);
            $calllogEntryMessage->addCalllogTask($task);
        }

        //add patient
        //$message->addPatient($patient);

        //add accession for patient info section
//        if(0) {
//            $accession = new Accession();
//            $status = 'invalid';
//            $provider = null;
//            $source = null;
//            $accession->addAccession(new AccessionAccession($status, $provider, $source));
//            $message->addAccession($accession);
//            //$accessions = $message->getAccession();
//            //echo "accessions count=".count($accessions)."<br>";
//        }

        return $message;
    }

    /**
     * NOT USED (search is displayed in the home page)
     * Search Call Entry
     */
    #[Route(path: '/callentry/search', name: 'calllog_search_callentry', methods: ['GET'])]
    public function searchCallEntryAction(Request $request)
    {
        if( false == $this->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $entities = null;

        $allgets = $request->query->all();
        //$patientid = trim((string)$request->get('patientid') );
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

        //$searchtype = trim((string)$request->get('searchtype') );
        //$search = trim((string)$request->get('search') );
        //echo "searchtype=".$searchtype."<br>";
        //echo "search=".$search."<br>";

        if( $searchtype != "" && $search != "" ) {

//            $searchUtil = $this->container->get('search_utility');
//            $object = 'patient';
//            $params = array('request'=>$request,'object'=>$object,'searchtype'=>$searchtype,'search'=>$search,'exactmatch'=>false);
//            $res = $searchUtil->searchAction($params);
//            $entities = $res[$object];
            $entities = null;
        }


        //echo "entities count=".count($entities)."<br>";

        return $this->render('AppCallLogBundle/CallLog/home.html.twig', array(
            'patientsearch' => $search,
            'patientsearchtype' => $searchtype,
            'patiententities' => $entities,
        ));
    }

    /**
     * Search Patient
     */
    #[Route(path: '/patient/search', name: 'calllog_search_patient', methods: ['GET'], options: ['expose' => true])]
    public function patientSearchAction(Request $request)
    {
        if (false == $this->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $calllogUtil = $this->container->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;
        $searchedArr = array();

        //$currentUrl = trim((string)$request->get('currentUrl'));
        //echo "currentUrl=".$currentUrl."<br>";

        //$formtype = trim((string)$request->get('formtype'));

        //$patientsData = $this->searchPatient( $request, true, null, false ); //testing
        $patientsData = $this->searchPatient( $request, true);

        $allowCreateNewPatient = true;
        $patients = $patientsData['patients'];
        $searchedStr = $patientsData['searchStr'];
        $searchedArr[] = "(Searched for ".$searchedStr.")";
        //echo "patients=".count($patients)."<br>";

        if( array_key_exists("accessionFound",$patientsData) ) {
            $accessionFound = $patientsData['accessionFound'];
        } else {
            $accessionFound = false;
        }

        if( count($patients) == 0 ) {
            //search again, but only by mrn
            $params = array();
            $mrntype = trim((string)$request->get('mrntype'));
            $mrn = trim((string)$request->get('mrn'));
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
                $allowCreateNewPatient = false;
            }
        }

        if( count($patients) == 0 ) {
            //search again, but only by accession
            $params = array();
            $accessiontype = trim((string)$request->get('accessiontype'));
            $accessionnumber = trim((string)$request->get('accessionnumber'));
            if( $accessionnumber && $accessiontype ) {
                $params['accessiontype'] = $accessiontype;
                $params['accessionnumber'] = $accessionnumber;
                $patientsDataStrict = $this->searchPatient($request, true, $params);
                $patientsStrict = $patientsDataStrict['patients'];

                if( array_key_exists("accessionFound",$patientsDataStrict) ) {
                    $accessionFound = $patientsDataStrict['accessionFound'];
                } else {
                    $accessionFound = false;
                }

                //$searchedStrStrict = $patientsDataStrict['searchStr'];
                if( $accessionFound ) {
                    foreach ($patientsStrict as $patientStrict) {
                        //$accessionRes = $patientStrict->obtainStatusField('accession', "valid");
                        //$accessiontypeStrict = $accessionRes->getKeytype();
                        //$accessionStrict = $accessionRes->getField();
                        //Accession 001 of Accession type NYH Accession appears to belong to a patient with a last name of LLL, first name of FFFF, and a MM/DD/YYYY date of birth.
                        $patientInfoStrict = $patientStrict->obtainPatientInfoShort();
                        $searchedArr[] = "<br>Accession $accessionnumber of Accession type $accessiontype appears to belong to a patient $patientInfoStrict";
                        $allowCreateNewPatient = false;
                    }
                }
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
        $resData['allowCreateNewPatient'] = $allowCreateNewPatient;
        $resData['accessionFound'] = $accessionFound;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($resData));
        return $response;
    }

    public function searchPatient( $request, $evenlog=false, $params=null, $turnOffMetaphone=false ) {
        //$userServiceUtil = $this->container->get('user_service_utility');
        $calllogUtil = $this->container->get('calllog_util');

//        if( $params ) {
//            //echo "params true<br>";
//            $mrntype = ( array_key_exists('mrntype', $params) ? $params['mrntype'] : null);
//            $mrn = ( array_key_exists('mrn', $params) ? $params['mrn'] : null);
//            $accessionnumber = ( array_key_exists('accessionnumber', $params) ? $params['accessionnumber'] : null);
//            $accessiontype = ( array_key_exists('accessiontype', $params) ? $params['accessiontype'] : null);
//            $dob = ( array_key_exists('dob', $params) ? $params['dob'] : null);
//            $lastname = ( array_key_exists('lastname', $params) ? $params['lastname'] : null);
//            $firstname = ( array_key_exists('firstname', $params) ? $params['firstname'] : null);
//            $phone = ( array_key_exists('phone', $params) ? $params['phone'] : null);
//            $email = ( array_key_exists('email', $params) ? $params['email'] : null);
//            $metaphone = ( array_key_exists('metaphone', $params) ? $params['metaphone'] : null);
//        }

        if( !$params ) {
//            $mrntype = trim((string)$request->get('mrntype')); //ID of mrn type
//            $mrn = trim((string)$request->get('mrn'));
//            $accessionnumber = trim((string)$request->get('accessionnumber'));
//            $accessiontype = trim((string)$request->get('accessiontype'));
//            $dob = trim((string)$request->get('dob'));
//            $lastname = trim((string)$request->get('lastname'));
//            $firstname = trim((string)$request->get('firstname'));
//            $phone = trim((string)$request->get('phone'));
//            $email = trim((string)$request->get('email'));
//            $metaphone = trim((string)$request->get('metaphone'));

            $params = array(
                'mrntype' => trim((string)$request->get('mrntype')),
                'mrn' => trim((string)$request->get('mrn')),
                'accessionnumber' => trim((string)$request->get('accessionnumber')),
                'accessiontype' => trim((string)$request->get('accessiontype')),
                'dob' => trim((string)$request->get('dob')),
                'lastname' => trim((string)$request->get('lastname')),
                'firstname' => trim((string)$request->get('firstname')),
                'phone' => trim((string)$request->get('phone')),
                'email' => trim((string)$request->get('email')),
                'metaphone' => trim((string)$request->get('metaphone'))
            );
        }

        //echo "phone=".$phone.", email=".$email."<br>";
        //print_r($allgets);
        //echo "metaphone=".$metaphone."<br>";
        //exit('1');

        $accessionnumber = ( array_key_exists('accessionnumber', $params) ? $params['accessionnumber'] : null);
        $accessiontype = ( array_key_exists('accessiontype', $params) ? $params['accessiontype'] : null);

        //accession (If anything was entered into the Accession Number field, ignore content of all other fields)
        if( $accessionnumber && $accessiontype ) {
            return $calllogUtil->searchPatientByAccession($request,$params,$evenlog,$turnOffMetaphone);
        } else {
            return $calllogUtil->searchPatientByMrn($request,$params,$evenlog,$turnOffMetaphone);
        }

    }

    


    /**
     * Create a new Patient
     */
    #[Route(path: '/patient/create', name: 'calllog_create_patient', methods: ['GET'], options: ['expose' => true])]
    public function createPatientAction(Request $request)
    {

        if( false == $this->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $securityUtil = $this->container->get('user_security_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $withEncounter = false;
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
        if (false == $this->isGranted('ROLE_CALLLOG_USER')) {
            //return $this->redirect($this->generateUrl('calllog-nopermission'));
            $res['patients'] = null;
            $res['output'] = "You don't have a permission to create a new patient record";
            $response->setContent(json_encode($res));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $calllogUtil = $this->container->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;

        $mrn = trim((string)$request->get('mrn'));
        $mrntype = trim((string)$request->get('mrntype')); //ID
        $dob = trim((string)$request->get('dob'));
        $lastname = trim((string)$request->get('lastname'));
        $firstname = trim((string)$request->get('firstname'));
        $middlename = trim((string)$request->get('middlename'));
        $suffix = trim((string)$request->get('suffix'));
        $sex = trim((string)$request->get('sex'));
        $phone = trim((string)$request->get('phone'));
        $email = trim((string)$request->get('email'));
        $accessionnumber = trim((string)$request->get('accessionnumber'));
        $accessiontype = trim((string)$request->get('accessiontype'));
        //print_r($allgets);
        //echo "mrn=".$mrn."<br>";
        //echo "mrntype=".$mrntype."<br>";
        //exit();

        $mrnTypeError = true;
        if( $mrntype ) {
            if( strval($mrntype) != strval(intval($mrntype)) ) {
                //not integer
                $mrntypeTransformer = new MrnTypeTransformer($em,$user);
                $withFlush = true;
                $mrntypeNew = $mrntypeTransformer->reverseTransform($mrntype,$withFlush);
                if( $mrntypeNew ) {
                    $mrntype = $mrntypeNew->getId();
                    $mrnTypeError = false;
                }
            }
            $keytypeEntity = $calllogUtil->convertAutoGeneratedMrntype($mrntype);
            if( $keytypeEntity ) {
                $mrntype = $keytypeEntity->getId(); //now its is ID
                $mrnTypeError = false;
            }
//            else {
//                $errorMsg = 'Mrn Type not found by keytype ID# "' . $mrntype . '"';
//                $res['patients'] = null;
//                $res['output'] = $errorMsg;
//                $response->setContent(json_encode($res));
//                return $response;
//            }
        }
        if( $mrnTypeError ) {
            $errorMsg = 'Mrn Type not found by keytype ID# "' . $mrntype . '"';
            $res['patients'] = null;
            $res['output'] = $errorMsg;
            $response->setContent(json_encode($res));
            return $response;
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
//            $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
//        }
        $institution = $userSecUtil->getCurrentUserInstitution($user);
        //echo "3 inst=".$institution."<br>";
        //exit('1');

        //get correct mrn type
        if( $mrntype && $mrn ) {
            $keytype = $mrntype;
        } else {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
            $keytypeEntity = $this->getDoctrine()->getRepository(MrnType::class)->findOneByName("Auto-generated MRN");
            $keytype = $keytypeEntity->getId() . ""; //id of "New York Hospital MRN" in DB
        }

        //first check if the patient already exists
        //check only by mrn: pass params with only mrn and mrntype
        $mrnParams = array();
        $mrnParams['mrntype'] = $mrntype;
        $mrnParams['mrn'] = $mrn;
        //searchPatient( $request, $evenlog=false, $params=null, $turnOffMetaphone=false )
        $patientsDataStrict = $this->searchPatient( $request, true, $mrnParams );
        $patientsStrict = $patientsDataStrict['patients'];
        if( count($patientsStrict) > 0 ) {
            $output = "Can not create a new Patient. The patient with specified MRN already exists:<br>";
            if( $mrntype ) {
                $output .= "MRN Type: ".$keytypeEntity."<br>";
            }
            if( $mrn ) {
                $output .= "MRN: " . $mrn . "<br>";
            }

            $searchedArr = array();
            foreach( $patientsStrict as $patientStrict ) {
                $mrnRes = $patientStrict->obtainStatusField('mrn', "valid");
                $mrntypeStrict = $mrnRes->getKeytype();
                $mrnStrict = $mrnRes->getField();
                //MRN 001 of MRN type NYH MRN appears to belong to a patient with a last name of LLL, first name of FFFF, and a MM/DD/YYYY date of birth.
                $patientInfoStrict = $patientStrict->obtainPatientInfoShort();
                $searchedArr[] = "<br>MRN $mrnStrict of MRN type $mrntypeStrict appears to belong to a patient $patientInfoStrict";
            }
            if( count($searchedArr) > 0 ) {
                $output .= implode("<br>",$searchedArr);
            }

            $res['patients'] = null;
            $res['output'] = $output;
            $response->setContent(json_encode($res));
            return $response;
        }

        //search by the rest of the parameters
        $turnOffMetaphone = true;
        //searchPatient( $request, $evenlog=false, $params=null, $turnOffMetaphone=false )
        $patientsData = $this->searchPatient($request,false,null,$turnOffMetaphone);
        $patients = $patientsData['patients'];

        if( count($patients) > 0 ) {
            $output = "Can not create a new Patient. The patient with specified parameters already exists:<br>";

            if( $mrntype ) {
                $output .= "MRN Type: ".$keytypeEntity."<br>";
            }
            if( $mrn ) {
                $output .= "MRN: " . $mrn . "<br>";
            }
            if( $lastname ) {
                $output .= "Last Name: " . $lastname . "<br>";
            }
            if( $firstname ) {
                $output .= "First Name: " . $firstname . "<br>";
            }
            if( $dob ) {
                $output .= "DOB: " . $dob . "<br>";
            }
            if( $phone ) {
                $output .= "Phone: " . $phone . "<br>";
            }
            if( $email ) {
                $output .= "E-Mail: " . $email . "<br>";
            }

            $res['patients'] = null;
            $res['output'] = $output;
            $response->setContent(json_encode($res));
            return $response;
        }

        //search by Accession
        if( $accessionnumber && $accessiontype ) {

//            $accessionParams = array();
//            $accessionParams['accessiontype'] = $accessiontype;
//            $accessionParams['accessionnumber'] = $accessionnumber;
//            $patientsDataStrict = $this->searchPatient($request, true, $accessionParams);
//            $patientsStrict = $patientsDataStrict['patients'];
//
//            if (array_key_exists("accessionFound", $patientsDataStrict)) {
//                $accessionFound = $patientsDataStrict['accessionFound'];
//            } else {
//                $accessionFound = false;
//            }
//
//            //$searchedStrStrict = $patientsDataStrict['searchStr'];
//            if( $accessionFound ) {
//
//                $searchedArr = array();
//
//                foreach( $patientsStrict as $patientStrict ) {
//                    //Accession 001 of Accession type NYH Accession appears to belong to a patient with a last name of LLL, first name of FFFF, and a MM/DD/YYYY date of birth.
//                    $patientInfoStrict = $patientStrict->obtainPatientInfoShort();
//                    $searchedArr[] = "<br>Accession $accessionnumber of Accession type $accessiontype appears to belong to a patient $patientInfoStrict";
//                }
//
//                if( count($patientsStrict) > 0 ) {
//                    $output = "Can not create a new Patient. The patient with specified Accession already exists:<br>";
//                    if( $accessiontype ) {
//                        $output .= "Accession Type: ".$accessiontype."<br>";
//                    }
//                    if( $accessionnumber ) {
//                        $output .= "Accession: " . $accessionnumber . "<br>";
//                    }
//
//                    if( count($searchedArr) > 0 ) {
//                        $output .= implode("<br>",$searchedArr);
//                    }
//
//                    $res['patients'] = null;
//                    $res['output'] = $output;
//                    $response->setContent(json_encode($res));
//                    return $response;
//                }
//
//            }//if( $accessionFound )

            $accessionExistingRes = $calllogUtil->getPatientsByAccessions($request,$accessionnumber,$accessiontype); // /patient/create
            $accessionExistingOutput = $accessionExistingRes['output'];
            $accessionExistingPatients = $accessionExistingRes['patients'];
            if( $accessionExistingOutput && count($accessionExistingPatients) > 0 ) {
                $res['patients'] = null;
                $res['output'] = $accessionExistingOutput;
                $response->setContent(json_encode($res));
                return $response;
            }

            //Just in case check again DB just by accession number/type. Accession must not exist in DB.
            $accession = $calllogUtil->findExistingAccession($accessionnumber,$accessiontype);
            if( $accession ) {
                $res['patients'] = null;
                $res['output'] = "Can not create a new patient with existing accession number. Accession $accessionnumber ($accessiontype) already exists in DB and belongs to another patient.";
                $response->setContent(json_encode($res));
                return $response;
            }

        }//if( $accessionnumber && $accessiontype )

        //testing
        if(0) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
            $patient = $em->getRepository(Patient::class)->find(32);
            $patientsArr = array(); //return json data
            $patientInfo = $calllogUtil->getJsonEncodedPatient($patient);
            $patientsArr[$patient->getId()] = $patientInfo;
            $res['patients'] = $patientsArr;
            $res['output'] = $output;
            $response->setContent(json_encode($res));
            return $response;
        }

        //Create a new Patient
        $sourcesystem = $securityUtil->getDefaultSourceSystem($this->getParameter('calllog.sitename'));
        $status = 'valid';

        //$patient = new Patient(false,$status,$user,$sourcesystem);
        //$patient->setInstitution($institution);

        //create a new patient
        if( $mrn ) {
            $fieldValue = $mrn;
        } else {
            $fieldValue = null;
        }

        //echo "mrn=".$fieldValue."<br>";
        //echo "keytype=".$keytype." (".$keytypeEntity.")<br>";
        //exit("1");

        $extra = array();
        $extra["keytype"] = $keytype; //must be keytype ID

        //echo "keytype=".$keytype."<br>";
        //exit();


        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $patient = $em->getRepository(Patient::class)->createElement(
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
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:PatientRecordStatusList'] by [PatientRecordStatusList::class]
        $patientActiveStatus = $em->getRepository(PatientRecordStatusList::class)->findOneByName("Active");
        if( $patientActiveStatus ) {
            $patient->setPatientRecordStatus($patientActiveStatus);
        }

        ////0 should be maintained and not deleted out when the patient is registered
        //if(0) {
            if( $mrn ) {    //mrn with leading zeros
                $mrnClean = ltrim((string)$mrn, '0');
                //echo "mrn: ".$mrn."?=".$mrnClean."<br>";
                if ($mrn !== $mrnClean) {
                    //create additional valid patient MRN: "00123456" and "123456".
                    $mrnCleanObject = new PatientMrn($status, $user, $sourcesystem);
                    $mrnCleanObject->setKeytype($mrnRes->getKeytype());
                    $mrnCleanObject->setField($mrnClean);
                    $patient->addMrn($mrnCleanObject);
                    $createdWithArr[] = "Clean MRN: " . $mrnClean;
                }
            }
        //}

        //$patient->addDob( new PatientDob($status,$user,$sourcesystem) );
        if( $dob ) {
            $dobDateTime = \DateTime::createFromFormat('m/d/Y', $dob);
            $PatientDob = new PatientDob($status, $user, $sourcesystem);
            $PatientDob->setField($dobDateTime);
            $patient->addDob($PatientDob);
            $createdWithArr[] = "DOB: " . $dob;
        }

        if( $accessiontype && $accessionnumber ) {
            //create accession and add it to this new patient
            $accession = $calllogUtil->createNewAccession($accessionnumber,$accessiontype,$user); // /patient/create
            if( $accession ) {
                $createdWithArr[] = "Accession Number: " . $accessionnumber . " (" . $accessiontype . ")";
                $em->persist($accession);
                //Patient->Encounter->Procedure->Accession
                //Create new Encounter
                $encounter = new Encounter(false, $status, $user, $sourcesystem);
                $em->persist($encounter);
                $patient->addEncounter($encounter);
                //Create new Procedure
                $procedure = new Procedure(false, $status, $user, $sourcesystem);
                $em->persist($procedure);
                $encounter->addProcedure($procedure);
                $procedure->addAccession($accession);
            }
        }

        if( $withEncounter ) {
            //create an encounter for this new patient with the First Name, Last Name, Middle Name, Suffix, and sex (if any)
            $encounter = new Encounter(false, $status, $user, $sourcesystem);
            $encounter->setInstitution($institution);
        }

        if( $lastname ) {
            if( $withEncounter ) {
                $EncounterPatlastname = new EncounterPatlastname($status, $user, $sourcesystem);
                $EncounterPatlastname->setField($lastname);
                $encounter->addPatlastname($EncounterPatlastname);
            }

            $PatientLastname = new PatientLastName($status,$user,$sourcesystem);
            $PatientLastname->setField($lastname);
            $patient->addLastname( $PatientLastname );

            $createdWithArr[] = "Last Name: " . $lastname;
        }

        if( $firstname ) {
            if( $withEncounter ) {
                $EncounterPatfirstname = new EncounterPatfirstname($status, $user, $sourcesystem);
                $EncounterPatfirstname->setField($firstname);
                $encounter->addPatfirstname($EncounterPatfirstname);
            }

            $PatientFirstname = new PatientFirstName($status,$user,$sourcesystem);
            $PatientFirstname->setField($firstname);
            $patient->addFirstname( $PatientFirstname );

            $createdWithArr[] = "First Name: " . $firstname;
        }

        if( $middlename ) {
            if( $withEncounter ) {
                $EncounterPatmiddlename = new EncounterPatmiddlename($status, $user, $sourcesystem);
                $EncounterPatmiddlename->setField($middlename);
                $encounter->addPatmiddlename($EncounterPatmiddlename);
            }

            $PatientMiddlename = new PatientMiddleName($status,$user,$sourcesystem);
            $PatientMiddlename->setField($middlename);
            $patient->addMiddlename( $PatientMiddlename );

            $createdWithArr[] = "Middle Name: " . $middlename;
        }

        if( $suffix ) {
            if( $withEncounter ) {
                $EncounterPatsuffix = new EncounterPatsuffix($status, $user, $sourcesystem);
                $EncounterPatsuffix->setField($suffix);
                $encounter->addPatsuffix($EncounterPatsuffix);
            }

            $PatientSuffix = new PatientSuffix($status,$user,$sourcesystem);
            $PatientSuffix->setField($suffix);
            $patient->addSuffix( $PatientSuffix );

            $createdWithArr[] = "Suffix: " . $suffix;
        }

        if( $sex ) {
            //echo "sex=".$sex."<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SexList'] by [SexList::class]
            $sexObj = $em->getRepository(SexList::class)->findOneById( $sex );

            if( $withEncounter ) {
                $EncounterPatsex = new EncounterPatsex($status, $user, $sourcesystem);
                $EncounterPatsex->setField($sexObj);
                $encounter->addPatsex($EncounterPatsex);
            }

            $PatientSex = new PatientSex($status,$user,$sourcesystem);
            $PatientSex->setField($sexObj);
            $patient->addSex( $PatientSex );

            $createdWithArr[] = "Gender: " . $sexObj;
        }

        if( $phone ) {
            $patient->setPhone($phone);
        }

        if( $email ) {
            $patient->setEmail($email);
        }

        if( $withEncounter ) {
            $patient->addEncounter($encounter);
            $em->persist($encounter);
        }

        $em->persist($patient);
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
        $userSecUtil->createUserEditEvent($this->getParameter('calllog.sitename'), $event, $user, $patient, $request, $eventType); //Create a new Patient

        $response->setContent(json_encode($res));
        return $response;
    }


//    public function getCurrentUserInstitution($user)
    //    {
    //        $em = $this->getDoctrine()->getManager();
    //        $securityUtil = $this->container->get('user_security_utility');
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
    //            $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
    //        }
    //
    //        return $institution;
    //    }
    /**
     * Get Patient Titles according to a new encounter date specified by nowStr
     */
    #[Route(path: '/patient/title/', name: 'calllog_get_patient_title', methods: ['GET'], options: ['expose' => true])]
    public function getPatientTitleAction(Request $request) {

        if (false == $this->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $patientId = trim((string)$request->get('patientId'));
        $nowStr = trim((string)$request->get('nowStr'));
        //echo "patientId=".$patientId."<br>";
        //echo "nowStr=".$nowStr."<br>";

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        //check if $nowStr is a valid date
        if( \DateTime::createFromFormat('m/d/Y', $nowStr) !== FALSE ) {
            // it's a date
            $nowDate = new \DateTime($nowStr);
        } else {
            $nowDate = null;
        }

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
        $patient = $em->getRepository(Patient::class)->find($patientId);
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
     * TODO: make messageVersion can be null and find by messageOid only by the most recent version
     */
    #[Route(path: '/entry/view/{messageOid}/{messageVersion}', name: 'calllog_callentry_view', methods: ['GET'])]
    #[Route(path: '/entry/view-latest-encounter/{messageOid}/{messageVersion}', name: 'calllog_callentry_view_latest_encounter', methods: ['GET'])]
    #[Template('AppCallLogBundle/CallLog/call-entry-view.html.twig')]
    public function getCallLogEntryAction(Request $request, $messageOid, $messageVersion=null)
    {

        if (false == $this->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        //$userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');
        $calllogUtil = $this->container->get('calllog_util');
        //$calllogUtil = $this->calllogUtil;
        $route = $request->get('_route');

        $pathPostfix = "";
        $cycle = "show";
        //$title = "Call Log Entry";
        $formtype = "call-entry";

        $formbased = false;
        //$formbased = true;

        //$patientId = trim((string)$request->get('patientId'));
        //$nowStr = trim((string)$request->get('nowStr'));
        //echo "patientId=".$patientId."<br>";
        //echo "nowStr=".$nowStr."<br>";
        //$messageId = 142; //154; //testing

        $em = $this->getDoctrine()->getManager();

        if( !is_numeric($messageVersion) || !$messageVersion ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
            $messageLatest = $em->getRepository(Message::class)->findByOidAndVersion($messageOid);

            if( !$messageLatest && !$messageVersion ) {
                //handle case with th real DB id: http://localhost/order/call-log-book/entry/view/267
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
                $messageLatest = $em->getRepository(Message::class)->find($messageOid);
            }

//            if( $messageLatest ) {
//                exit("Message found, ID=".$messageLatest->getId());
//            } else {
//                exit("Message not found");
//            }

            $latestMessageVersion = NULL;
            if( $messageLatest ) {
                //if message version is NULL we can try to recover by set version to 1 and save the message
                if( !$messageLatest->getVersion() ) {
                    //exit("Set version to 1 <br>");
                    $messageLatest->setVersion(1);
                    $em->flush();
                }
                $latestMessageVersion = $messageLatest->getVersion();
                if( $messageLatest->getOid() && $latestMessageVersion ) {
                    return $this->redirect($this->generateUrl('calllog_callentry_view', array(
                        'messageOid' => $messageLatest->getOid(),
                        'messageVersion' => $latestMessageVersion
                    )));
                }
            }

            throw new \Exception( "Latest Message is not found by oid=".$messageOid." or by version=$latestMessageVersion" );
        }

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $message = $em->getRepository(Message::class)->findByOidAndVersion($messageOid,$messageVersion);
        if( !$message ) {
            throw new \Exception( "Message is not found by oid ".$messageOid." and version ".$messageVersion );
        }

        //testing
        //$this->container->get('user_formnode_utility')->updateFieldsCache($message);
        //exit('pre-update entry');

        //testing dob: dob before 1901 causes php error
//        $testPatient = $message->getPatient()[0];
//        echo "patientName=".implode(",",$testPatient->patientName("valid"))."<br>";
//        echo "getFullPatientName=".$testPatient->getFullPatientName()."<br>";
//        echo "obtainPatientInfoTitle=".$testPatient->obtainPatientInfoTitle()."<br>";
//        echo "obtainPatientInfoShort=".$testPatient->obtainPatientInfoShort()."<br>";
//        foreach( $testPatient->getDob() as $dob ) {
//            if( $dob ) {
//                echo $dob->getId().": dob=" . $dob . "<br>";
//                //echo "dob=" . $dob->format('m-d-Y') . "<br>";
//            } else {
//                echo "dob is null <br>";
//            }
//        }
        //testing
        //$mesInfo = $this->container->get('user_formnode_utility')->getFormNodeHolderShortInfo($message,$message->getMessageCategory(),1,"");
        //echo "mesInfo=".$mesInfo."<br>";
        //$tz = $message->getOrderdate()->getTimezone();
        //echo "tz=".$tz->getName()."<br>";
        //exit('1');

        $encounter = $message->getEncounter()->first();

        //Replace encounter with the latest encounter.
        //Used replaced encounter for latest url only to show message's encounter, not patient's encounter!.
        if( $route == "calllog_callentry_view_latest_encounter" ) {
            $pathPostfix = "_latest_encounter";
            //$encounter = $message->getEncounter()->first();
            if( !$calllogUtil->isLatestEncounterVersion($encounter) ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Encounter'] by [Encounter::class]
                $latestEncounter = $em->getRepository(Encounter::class)->findLatestVersionEncounter($encounter);
                if( $latestEncounter ) {
                    //echo "Original id=".$encounter->getId()."; version=".$encounter->getVersion()." => latestEncounter: id=".$latestEncounter->getId()."; version=".$latestEncounter->getVersion()."<br>";
                    //clear encounter
                    $message->clearEncounter();
                    //add encounter to the message
                    $message->addEncounter($latestEncounter);
                }
            }
        }

        $messageInfo = "Entry ID ".$message->getMessageOidVersion()." submitted on ".$userServiceUtil->getSubmitterInfo($message); // . " | Call Log Book";
        //echo "messageInfo=".$messageInfo."<br>";
        //exit('1');
        if (count($message->getPatient()) > 0 ) {
            $patient = $message->getPatient()->first();
            $mrnRes = $patient->obtainStatusField('mrn', "valid");
            if( $mrnRes->getKeytype() ) {
                $mrntype = $mrnRes->getKeytype()->getId();
            } else {
                $mrntype = NULL;
            }
            $mrn = $mrnRes->getField();
            $patientId = $patient->getId();

            //LastName, FirstName, MiddleName | MRN Type: MRN | DOB: MM/DD/YY |
            // Entry ID XXX submitted on MM/DD/YYYY at HH:MM by SubmitterFirstName SubmitterLastName, MD | Call Log Book
            $title = $patient->obtainPatientInfoTitle('valid',null,false);

            //The beginning potion of the title ("LastName, FirstName | DOB: 09/22/1955 | 71 y.o. | NYH MRN: 12345678")
            //should be a link to the homepage with the filters set to this patient's MRN
            $linkUrl = $this->generateUrl(
                "calllog_home",
                array(
                    'filter[mrntype]'=>$mrntype,
                    'filter[search]'=>$mrn,
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $titleBody = '<a href="'.$linkUrl.'" target="_blank">'.$title.'</a>';

            //Accession
            $messageAccessions = $message->getAccession();
            if( count($messageAccessions) > 0 ) {
                $messageAccession = $messageAccessions[0];
                $messageAccessionArr = $messageAccession->obtainFullValidKeyNameArr();
                $messageAccessionStr = $messageAccessionArr['keyStr'];
                $accessionType = $messageAccessionArr['keytype'];
                if( $accessionType ) {
                    $accessionTypeId = $accessionType->getId();
                } else {
                    $accessionTypeId = null;
                }
                $accessionNumber = $messageAccessionArr['field'];
                if( $messageAccessionStr ) {
                    $linkAccessionUrl = $this->generateUrl(
                        "calllog_home",
                        array(
                            'filter[accessionType]'=>$accessionTypeId,
                            'filter[accessionNumber]'=>$accessionNumber,
                        ),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $messageAccessionStr = '<a href="'.$linkAccessionUrl.'" target="_blank">'.$messageAccessionStr.'</a>';
                    $titleBody = $titleBody . " | " . $messageAccessionStr; // /entry/view
                }
            }

            //view: get message's encounter location
            $messageEncounters = $message->getEncounterLocationInfos();
            if( $messageEncounters ) {
                $titleBody = $titleBody . " | " . $messageEncounters;
            }

            $titleBody = $titleBody . " | ".$messageInfo;

        } else {
            $patient = null;
            $mrntype = null;
            $mrn = null;
            $patientId = null;
            $titleBody = null;

            $title = $messageInfo;
        }

        //testing
//        foreach( $message->getEncounter() as $thisEncounter ) {
//            echo "thisEncounter: id=".$thisEncounter->getId()."; version=".$thisEncounter->getVersion()."<br>";
//        }

        if( $formbased ) {
            $form = $this->createCalllogEntryForm($message, $mrntype, $mrn, $cycle); //view
        }

        $complexPatientStr = null;
        //find record in the "Pathology Call Complex Patients" list by message object entityName, entityId
//        $mapper = array(
//            'prefix' => "App",
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
        $classNamespace = $class->getNamespaceName(); //App\UserdirectoryBundle\Entity

        //top message category id
        $formnodeTopHolderId = null;
        //$categoryStr = "Pathology Call Log Entry";
        //$messageCategory = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($categoryStr);
        $messageCategory = $calllogUtil->getDefaultMessageCategory();
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        $eventObjectType = $em->getRepository(EventObjectTypeList::class)->findOneByName("Message");
        if( $eventObjectType ) {
            $eventObjectTypeId = $eventObjectType->getId();
        } else {
            $eventObjectTypeId = null;
        }

        //View Previous Version(s)
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $allMessages = $em->getRepository(Message::class)->findAllMessagesByOid($messageOid);

        //previous entries similar to calllog-list-previous-entries: get it in the view by ajax

        //Event Log - User accessing “Show Entry” page should be added to the event log as an event for that object/note (Event Type “Entry Viewed”)
        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->getUser();
        $eventType = "Call Log Book Entry Viewed";
        $eventStr = "Call Log Book Entry ID#".$message->getMessageOidVersion()." has been viewed by ".$user;
        $userSecUtil->createUserEditEvent($this->getParameter('calllog.sitename'), $eventStr, $user, $message, $request, $eventType); //View Call Log Entry

        if( $formbased ) {
            return array(
                //'entity' => $entity,
                'form' => $form->createView(),
                'cycle' => $cycle,
                'title' => $title,
                'titleBody' => $titleBody,
                'formtype' => $formtype,
                'triggerSearch' => 0,
                'mrn' => $mrn,
                'mrntype' => $mrntype,
                'patientId' => $patientId,
                'message' => $message,
                'complexPatientStr' => $complexPatientStr,
                //'encounterid' => $encounterid
                'entityNamespace' => $classNamespace,
                'entityName' => $className,
                'entityId' => $message->getId(),
                'sitename' => $this->getParameter('calllog.sitename'),
                'titleheadroom' => $titleBody,
                'formnodeTopHolderId' => $formnodeTopHolderId,
                'eventObjectTypeId' => $eventObjectTypeId,
                'allMessages' => $allMessages,
                'pathPostfix' => $pathPostfix,
                'formbased' => $formbased
            );
        } else {

            //echo "encounter=".$encounter->getId();

            return array(
                'cycle' => $cycle,
                'title' => $title,
                'titleBody' => $titleBody,
                'formtype' => $formtype,
                'triggerSearch' => 0,
                'mrn' => $mrn,
                'mrntype' => $mrntype,
                'patientId' => $patientId,
                'message' => $message,
                'complexPatientStr' => $complexPatientStr,
                'entityNamespace' => $classNamespace,
                'entityName' => $className,
                'entityId' => $message->getId(),
                'sitename' => $this->getParameter('calllog.sitename'),
                'titleheadroom' => $titleBody,
                'formnodeTopHolderId' => $formnodeTopHolderId,
                'eventObjectTypeId' => $eventObjectTypeId,
                'allMessages' => $allMessages,
                'pathPostfix' => $pathPostfix,
                /////// formbased=false /////////
                'formbased' => $formbased,
                'patient' => $patient,
                'encounter' => $encounter,
                'status' => 'Submitted'
            );
        }
    }

    #[Route(path: '/single-export-csv/{messageOid}/{messageVersion}', name: 'calllog_single_export_csv')]
    #[Template('AppCallLogBundle/Export/call-entry-export-csv.html.twig')]
    public function exportSingleCsvAction(Request $request, $messageOid, $messageVersion=null)
    {
        if (false == $this->isGranted("ROLE_CALLLOG_ADMIN")) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if( !is_numeric($messageVersion) || !$messageVersion ) {
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
            $messageLatest = $em->getRepository(Message::class)->findByOidAndVersion($messageOid);

            if( !$messageLatest && !$messageVersion ) {
                //handle case with th real DB id: http://localhost/order/call-log-book/entry/view/267
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
                $messageLatest = $em->getRepository(Message::class)->find($messageOid);
            }

            if( $messageLatest ) {
                return $this->redirect($this->generateUrl('calllog_callentry_view', array(
                    'messageOid' => $messageLatest->getOid(),
                    'messageVersion' => $messageLatest->getVersion()
                )));
            }

            throw new \Exception( "Latest Message is not found by oid ".$messageOid );
        }

        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Message'] by [Message::class]
        $message = $em->getRepository(Message::class)->findByOidAndVersion($messageOid,$messageVersion);
        if( !$message ) {
            throw new \Exception( "Message is not found by oid ".$messageOid." and version ".$messageVersion );
        }

        //testing
        //$this->container->get('user_formnode_utility')->updateFieldsCache($message);

        $fileName = "Call-Log-Entry-ID" . $message->getOid();

        $fileName = str_replace(".","-",$fileName);

        $ext = "XLSX";
        $ext = "CSV";

        $this->createCalllogListExcelSpout(array($message),$fileName,$user,$ext);

        exit();
        //exit('single-export-csv');
    }


    #[Route(path: '/export_csv/', name: 'calllog_export_csv')]
    #[Route(path: '/export_csv/all/', name: 'calllog_export_csv_all')]
    #[Template('AppCallLogBundle/Export/call-entry-export-csv.html.twig')]
    public function exportCsvAction(Request $request)
    {
        if( false == $this->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        set_time_limit(600); //600 seconds => 10 mins

        $user = $this->getUser();
        $userSecUtil = $this->container->get('user_security_utility');
        //$logger = $this->container->get('logger');

        //$all = $request->get('all');
        //echo "all=".$all."<br>";

        $routename = $request->get('_route');

        if( $routename == "calllog_export_csv" ) {
            $limit = 500;
            //$limit = 1;
        } else {
            set_time_limit(600); //6 min
            //ini_set('memory_limit', '30720M'); //30GB
            //ini_set('memory_limit', '-1');
            $limit = null;
        }

        if(1) {
            $res = $this->getCalllogEntryFilter($request, $limit); //Export CSV

            if ($res['redirect']) {
                //exit('redirect to home page');
                return $res['redirect'];
            }

            $query = $res['query'];

            $entries = $query->getResult();
            //echo "number of entries=".count($entries)."<br>";
            //exit('111');
            //$logger->notice("exportCsvAction: number of entries=".count($entries));
        }

        //testing
//        $em = $this->getDoctrine()->getManager();
//        $repository = $em->getRepository('AppOrderformBundle:Message');
//        $dql = $repository->createQueryBuilder("message");
//        //$dql->select('message.id');
//        $dql->select('message');
//        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
//        $query->setMaxResults(1);
//        $entries = $query->getResult();
        //foreach($entries as $message) {
            //echo "encounters=".count($message->getEncounter())."<br>";
        //}
//        echo "query=".$query->getSql()."<br>";
        //return array('filename'=>'111','title'=>'222');

        if( count($entries) == 0 ) {
            $this->addFlash(
                'notice',
                "No entries found for exporting."
            );
            return $this->redirect( $this->generateUrl('calllog_home') );
        }

        //An entry should be added to the Event Log, Titled "Call Log Book data exported".
        $eventType = "Call Log Book data exported";
        $eventDesc = "Call Log Book data exported on ".date('m/d/Y H:i')." by ".$user.". Exported entries count is ".count($entries);
        $userSecUtil->createUserEditEvent($this->getParameter('calllog.sitename'), $eventDesc, $user, $entries, $request, $eventType); //exportCsvAction

        //filename: The title of the file should be "Call-Log-Book-Entries-exported-on-[Timestamp]-by-[Logged-In-User-FirstName-LastName-(cwid)].csv .
        $userName = $user."";//->getUsernameOptimal();
        $userName = str_replace(",", "-", $userName);
        //$userName = str_replace("--", "-", $userName);
        //exit("userName=".$userName);

        $fileName = "Call-Log-Book-Entries-exported-on-".date('m/d/Y')."-".date('H:i')."-by-".$userName;//.".csv";//".xlsx";
        //$fileName = $fileName . ".xlsx";
        $fileName = str_replace("  ", " ", $fileName);
        $fileName = str_replace(" ", "-", $fileName);
        $fileName = str_replace("--", "-", $fileName);
        $fileName = str_replace("--", "-", $fileName);
        //exit($fileName);

        //testing: render in html with excel header
        if(0) {
            return $this->render('AppCallLogBundle/Export/call-entry-export-csv.html.twig', array(
                'messages' => $entries,
                'title' => "Call Log Book data",
                'filename' => $fileName
            ));
        }

        //Testing Spout
        if(1) {
            $ext = "XLSX";
            $ext = "CSV";
//            $entryIds = array();
//            foreach ($entries as $thisEntry) {
//                $entryIds[] = $thisEntry->getId();
//            }
//            $this->createCalllogListExcelSpout($entryIds, $fileName, $user);
            $this->createCalllogListExcelSpout($entries,$fileName,$user,$ext);
            //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            //header('Content-Disposition: attachment;filename="'.$fileName.'"');
            exit();
        }

        if(0) {
            $excelBlob = $this->createCalllogListExcel($entries, $user);

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelBlob, 'Xlsx');
            //ob_end_clean();
            //$writer->setIncludeCharts(true);

            $fileName = $fileName . ".xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');

            // Write file to the browser
            $writer->save('php://output');

            exit();
        }
    }

    //Not used. Use Spout instead.
    public function createCalllogListExcel($entries,$author) {

        $formNodeUtil = $this->container->get('user_formnode_utility');

        $ea = new Spreadsheet(); // ea is short for Excel Application

        $ea->getProperties()
            ->setCreator($author."")
            ->setTitle('Call Log Book data')
            ->setLastModifiedBy($author."")
            ->setDescription('Call Log Book data list in spreadsheet format')
            ->setSubject('PHP spreadsheet manipulation')
            ->setKeywords('spreadsheet php office')
            ->setCategory('programming')
        ;

        $title = 'Call Log Book data';
        $ews = $ea->getSheet(0);
        $ews->setTitle($title);

        //align all cells to left
//        $style = array(
//            'alignment' => array(
//                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
//            )
//        );
        $style = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrap' => true
            ),
            'font'  => array(
                'size'  => 10,
                'name'  => 'Calibri'
            )
        );
        //$ews->getDefaultStyle()->applyFromArray($style);
        $ews->getParent()->getDefaultStyle()->applyFromArray($style);

//        //set width (from original excel file to make printable)
//        $ews->getColumnDimension('A')->setWidth(22.18);
//        $ews->getColumnDimension('B')->setWidth(24.36);   //20.36
//        $ews->getColumnDimension('C')->setWidth(24.36);   //18.36
//        $ews->getColumnDimension('D')->setWidth(14.18);   //8.18
//        $ews->getColumnDimension('E')->setWidth(24.64);   //21.64
//        $ews->getColumnDimension('F')->setWidth(24.64);   //21.64
//        $ews->getColumnDimension('G')->setWidth(24.64);   //21.64
//        $ews->getColumnDimension('H')->setWidth(24.64);   //21.64

//        //marging
//        $ews->getPageMargins()->setTop(1);
//        $ews->getPageMargins()->setRight(0.25); //0.75
//        $ews->getPageMargins()->setLeft(0);
//        $ews->getPageMargins()->setBottom(1);

        //set title
        $ews->getHeaderFooter()->setOddHeader('&C&H'.$title);

//        //set footer (The code for "left" is &L)
//        $ews->getHeaderFooter()->setOddFooter('&L'.$footer);
//        $ews->getHeaderFooter()->setEvenFooter('&L'.$footer);

        $ews->setCellValue('A1', 'ID'); // Sets cell 'a1' to value 'ID
        $ews->setCellValue('B1', 'Last Modified');
        $ews->setCellValue('C1', 'Patient Name');
        $ews->setCellValue('D1', 'MRN');
        $ews->setCellValue('E1', 'Location');
        $ews->setCellValue('F1', 'Healthcare Provider');
        $ews->setCellValue('G1', 'Call Issue');
        $ews->setCellValue('H1', 'Author');

        //set bold
        $ews->getStyle("A1:H1")->getFont()->setBold(true);

        $row = 2;
        foreach( $entries as $message ) {

            //ID
            $ews->setCellValue('A'.$row, $message->getMessageOidVersion());

            //Last Modified
            $lastModified = null;
            if( $message->getVersion() > 1 ) {
                if (count($message->getEditorInfos()) > 0) {
                    $modifiedOnDate = $message->getEditorInfos()[0]->getModifiedOn();
                    $lastModified = $modifiedOnDate->format('m/d/Y') . " at " . $modifiedOnDate->format('H:i:s');
                } else {
                    $modifiedOnDate = $message->getOrderdate();
                    $lastModified = $modifiedOnDate->format('m/d/Y') . " at " . $modifiedOnDate->format('H:i:s');
                }
            } else {
                $modifiedOnDate = $message->getOrderdate();
                $lastModified = $modifiedOnDate->format('m/d/Y') . " at " . $modifiedOnDate->format('H:i:s');
            }
            $ews->setCellValue('B'.$row, $lastModified);

            //Patient
            $patientNames = array();
            $mrns = array();
            foreach( $message->getPatient() as $patient ) {
                $patientNames[] = $patient->getFullPatientName(false);
                $mrns[] = $patient->obtainFullValidKeyName();
            }

            //Patient Name
            $patientNameStr = implode("\n",$patientNames);
            $ews->setCellValue('C'.$row, $patientNameStr);

            //MRN
            $mrnsStr = implode("\n",$mrns);
            $ews->setCellValue('D'.$row, $mrnsStr);


            //Location and Healthcare Provider
            $locationArr = array();
            $refProviderArr = array();
            foreach( $message->getEncounter() as $encounter ) {
                $locationArr[] = $encounter->obtainLocationInfo();
                foreach( $encounter->getReferringProviders() as $refProvider ) {
                    if( $refProvider->getField() ) {
                        $refProviderArr[] = $refProvider->getField()->getFullName();
                    }
                }
            }

            //Location
            $locationStr = implode("\n",$locationArr);
            $ews->setCellValue('E'.$row, $locationStr);

            //Healthcare Provider
            $refProviderStr = implode("\n",$refProviderArr);
            $ews->setCellValue('F'.$row, $refProviderStr);

            //Call Issue
            $callIssue = $message->getMessageCategory()->getNodeNameWithParents();
            $ews->setCellValue('G'.$row, $callIssue);

            //Author
            $author = null;
            if( $message->getMessageStatus() && $message->getMessageStatus()->getName() == "Draft" ) {
                $provider = $message->getProvider();
                if( $provider ) {
                    $author = $provider->getUsernameOptimal();
                }
            } else {
                $signeeInfo = $message->getSigneeInfo();
                if( $signeeInfo && $signeeInfo->getModifiedBy() ) {
                    $author = $signeeInfo->getModifiedBy()->getUsernameOptimal();
                }
            }
            $ews->setCellValue('H'.$row, $author);

            //////// subsection with message snapshot info ////////
            $rowCount = $rowCount + 1;
            $trclassname = "";
            $snapshotArr = $formNodeUtil->getFormNodeHolderShortInfo($message,$message->getMessageCategory(),false,$trclassname);
            //$snapshotArr = $formNodeUtil->getFormNodeHolderShortInfo($message,$message->getMessageCategory(),true,$trclassname);

            //divide results by chunks of 21 rows in order to fit them in the excel row max height
//            echo "snapshotArr count=".count($snapshotArr)."<br>";
//            print "<pre>";
//            print_r($snapshotArr);
//            print "</pre><br>";
            $snapshotArrChunks = array_chunk($snapshotArr, 21);
//            echo "snapshotArrChunks count=".count($snapshotArrChunks)."<br>";

            //$originalRow = $row;
            $numItems = count($snapshotArrChunks);
            $i = 0;
            foreach( $snapshotArrChunks as $snapshotArrChunk ) {

                //$snapshot = implode("\n",$snapshotArrChunk);
                //$objRichText = new \PHPExcel_RichText();
                $objRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                foreach( $snapshotArrChunk as $snapshotRow ) {
//                    $snapshotRow = "snapshotRow=$snapshotRow<br>";
                    if( strpos((string)$snapshotRow, "[###excel_section_flag###]") === false ) {
                        $objRichText->createText($snapshotRow."\n");
                    } else {
                        $snapshotRow = str_replace("[###excel_section_flag###]","",$snapshotRow);
                        $objItalic = $objRichText->createTextRun($snapshotRow."\n");
                        $objItalic->getFont()->setItalic(true);
                    }
                }
                //exit('$snapshot='.$snapshotArr);
                $aRow = 'A' . $row;
                //$ews->setCellValue($aRow, "".$snapshot);
                $ews->setCellValue($aRow, $objRichText);

//                if( strpos((string)$snapshot, '[Form Section]') !== false ) {
//                    $ews->getStyle($aRow)->getFont()->setItalic(true);
//                }

                if( ++$i < $numItems ) {
                    $rowCount = $rowCount + 1;
                }
            }
            //$aRowMerged = 'A' . $originalRow . ':' . 'A' . $row; //merge is not working
            //$ews->mergeCells($aRowMerged);
//            exit('1');

//            $snapshot = implode("\n",$snapshotArr);
//            //exit('$snapshot='.$snapshotArr);
//            $aRow = 'A' . $row;
//
//            //$aRowMerged = 'A' . $row . ':' . 'B' . $row;
//            $nrow = $row + 1;
//            $aRowMerged = 'A' . $row . ':' . 'A' . $nrow;
//            $row = $row + 1;
//            $ews->mergeCells($aRowMerged);
//
//            $ews->setCellValue($aRow, "".$snapshot."\n");
            //$ews->getStyle($aRow)->getAlignment()->setWrapText(true);
            //////// EOF subsection with message snapshot info ////////

            //increment row index
            $rowCount = $rowCount + 1;

        }//foreach



        // Auto size columns for each worksheet
        //\PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        foreach ($ea->getWorksheetIterator() as $worksheet) {

            $ea->setActiveSheetIndex($ea->getIndex($worksheet));

            $sheet = $ea->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
            //$sheet->getDefaultRowDimension()->setRowHeight(-1);
            //$sheet->getStyle('A')->getAlignment()->setWrapText(true);
        }


        return $ea;
    }

    public function createCalllogListExcelSpout($entryIds,$fileName,$author,$ext="XLSX") {

        set_time_limit(600); //6 min

        $formNodeUtil = $this->container->get('user_formnode_utility');
        $em = $this->getDoctrine()->getManager();

        $useCache = TRUE; //default. Always use cache for export
//        $userSecUtil = $this->container->get('user_security_utility');
//        $sitename = $this->getParameter('calllog.sitename');
//        $useCache = $userSecUtil->getSiteSettingParameter('useCache',$sitename);
//        if( !$useCache ) {
//            $useCache = TRUE; //default
//        }

        if( $ext == "XLSX" ) {
            $fileName = $fileName . ".xlsx";
            //$writer = WriterFactory::create(Type::XLSX);
            $writer = WriterEntityFactory::createXLSXWriter();
        } else {
            $fileName = $fileName . ".csv";
            //$writer = WriterFactory::create(Type::CSV);
            $writer = WriterEntityFactory::createCSVWriter();
        }
        $writer->openToBrowser($fileName);

        //$title = 'Call Log Book data';

        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            //->setFontItalic()
            ->setFontSize(12)
            ->setFontColor(Color::BLACK)
            ->setShouldWrapText()
            ->setBackgroundColor(Color::toARGB("E0E0E0"))
            ->build();

//        $rowStyle = (new StyleBuilder())
//            ->setFontSize(10)
//            ->setShouldWrapText()
//            ->build();

        $border = (new BorderBuilder())
            ->setBorderBottom(Color::GREEN, Border::WIDTH_THIN, Border::STYLE_DASHED)
            ->build();
        $rowStyle = (new StyleBuilder())
            //->setFontBold()
            //->setFontItalic()
            //->setFontSize(12)
            //->setFontColor(Color::BLACK)
            ->setShouldWrapText()
            //->setBackgroundColor(Color::toARGB("EBF1DE"))
            ->setBorder($border)
            ->build();

//        $writer->addRowWithStyle(
//            [
//                'ID',                   //0 - A
//                'Last Modified',        //1 - B
//                'Patient Name',         //2 - C
//                'MRN',                  //3 - D
//                'Location',             //4 - E
//                'Healthcare Provider',   //5 - F
//                'Call Issue',           //6 - G
//                'Author'                //7 - H
//            ],
//            $headerStyle
//        );
        $spoutRow = WriterEntityFactory::createRowFromArray(
            [
                'ID',                   //0 - A
                'Last Modified',        //1 - B
                'Patient Name',         //2 - C
                'MRN',                  //3 - D
                'Location',             //4 - E
                'Healthcare Provider',   //5 - F
                'Call Issue',           //6 - G
                'Author'                //7 - H
            ],
            $headerStyle
        );
        $writer->addRow($spoutRow);

        //$entryIds = array();
        $count = 0;
        $rowCount = 2;
        foreach( $entryIds as $message ) {
        //foreach( $entryIds as $entryId ) {

            $count++;

            $data = array();

            //$message = $em->getRepository('AppOrderformBundle:Message')->find($entryId);

            //ID
            //$ews->setCellValue('A'.$row, $message->getMessageOidVersion());
            $messageOid = $message->getMessageOidVersion();
            $data[0] = $messageOid;

            //Last Modified
            if(1) {//testing Last Modified
                $lastModified = null;
                if ($message->getVersion() > 1) {
                    $editorInfos = $message->getEditorInfos();
                    if (count($editorInfos) > 0) {
                        $modifiedOnDate = $editorInfos[0]->getModifiedOn();
                        $lastModified = $modifiedOnDate->format('m/d/Y') . " at " . $modifiedOnDate->format('H:i:s');
                    } else {
                        $modifiedOnDate = $message->getOrderdate();
                        $lastModified = $modifiedOnDate->format('m/d/Y') . " at " . $modifiedOnDate->format('H:i:s');
                    }
                } else {
                    $modifiedOnDate = $message->getOrderdate();
                    $lastModified = $modifiedOnDate->format('m/d/Y') . " at " . $modifiedOnDate->format('H:i:s');
                }
                //$ews->setCellValue('B'.$row, $lastModified);
                $data[1] = $lastModified;

                $this->print_mem("$count : $messageOid Last Modified");

                $editorInfos = NULL;
                $modifiedOnDate = NULL;
                $lastModified = NULL;
                gc_collect_cycles();
            }

            if(1) {//testing patient

                //Patient
                if( $useCache ) {
                    $patientNameStr = $message->getPatientNameCache();
                    $mrnsStr = $message->getPatientMrnCache();
                } else {
                    $patientNameStr = null;
                    $mrnsStr = null;
                }

                if( !$patientNameStr || !$mrnsStr ) {
                    $patientNames = array();
                    $mrns = array();
                    foreach ($message->getPatient() as $patient) {
                        $patientNames[] = $patient->getFullPatientName(false);
                        $mrns[] = $patient->obtainFullValidKeyName();
                        //$patient = NULL;
                        //gc_collect_cycles();
                    }
                    //Patient Name
                    $patientNameStr = implode("\n", $patientNames);
                    //MRN
                    $mrnsStr = implode("\n", $mrns);
                }

                $data[2] = $patientNameStr;
                $data[3] = $mrnsStr;

                $this->print_mem("$count : $messageOid Patient");

//                $patientNames = NULL;
//                $patientNameStr = NULL;
//                $mrnsStr = NULL;
//                $mrns = NULL;
//                gc_collect_cycles();
            }

            if(0) {//testing encounter

                //Location and Healthcare Provider
                $locationArr = array();
                $refProviderArr = array();
                foreach ($message->getEncounter() as $encounter) {
                    $locationArr[] = $encounter->obtainLocationInfo();
                    foreach ($encounter->getReferringProviders() as $refProvider) {
                        if ($refProvider->getField()) {
                            $refProviderArr[] = $refProvider->getField()->getFullName();
                        }
                        $refProvider = NULL;
                    }
                    $encounter = NULL;
                }

                //Location
                $locationStr = implode("\n", $locationArr);
                //$ews->setCellValue('E'.$row, $locationStr);
                $data[4] = $locationStr;

                //Healthcare Provider
                $refProviderStr = implode("\n", $refProviderArr);
                //$ews->setCellValue('F'.$row, $refProviderStr);
                $data[5] = $refProviderStr;

                $this->print_mem("$count : $messageOid Encounter");
            } else {
                $data[4] = NULL;
                $data[5] = NULL;
            }//testing

            //Call Issue
            $callIssue = $message->getMessageCategory()->getNodeNameWithParents(); //another object
            //$ews->setCellValue('G'.$row, $callIssue);
            $data[6] = $callIssue;

            //Author
            if(1) { //testing author
                $author = null;

                if ($message->getMessageStatus() && $message->getMessageStatus()->getName() == "Draft") {
                    $provider = $message->getProvider();
                    if ($provider) {
                        $author = $provider->getUsernameOptimal();
                    } else {
                        $author = "Unknown Author";
                    }
                } else {
                    $signeeInfo = $message->getSigneeInfo();
                    if ($signeeInfo && $signeeInfo->getModifiedBy()) {
                        $author = $signeeInfo->getModifiedBy()->getUsernameOptimal();
                    } else {
                        $author = "Unknown Author";
                    }
                }

//                $provider = $message->getProvider();
//                if( $provider ) {
//                    $author = $provider->getUsernameOptimal();
//                } else {
//                    $author = "Unknown Author";
//                }

                //$ews->setCellValue('H'.$row, $author);
                $data[7] = $author;
            }

            //$writer->addRowWithStyle($data,$rowStyle);
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $rowStyle);
            $writer->addRow($spoutRow);

            //////// subsection with message snapshot info ////////
            if(0) {
                $rowCount = $rowCount + 1;
                $trclassname = "";

                if( $table=true ) {
                    $snapshotRow = $formNodeUtil->getFormNodeHolderShortInfo($message, $message->getMessageCategory(), true, $trclassname);
                    $data = array();
                    $data[0] = $snapshotRow;
                    //$writer->addRowWithStyle($data, $rowStyle);
                    $spoutRow = WriterEntityFactory::createRowFromArray($data, $rowStyle);
                    $writer->addRow($spoutRow);
                } else {
                    $snapshotArr = $formNodeUtil->getFormNodeHolderShortInfo($message, $message->getMessageCategory(), false, $trclassname);

                    //divide results by chunks of 21 rows in order to fit them in the excel row max height
                    $snapshotArrChunks = array_chunk($snapshotArr, 21);

                    //$originalRow = $row;
                    $numItems = count($snapshotArrChunks);
                    $i = 0;
                    foreach ($snapshotArrChunks as $snapshotArrChunk) {

                        //$objRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                        foreach ($snapshotArrChunk as $snapshotRow) {
                            if (strpos((string)$snapshotRow, "[###excel_section_flag###]") === false) {
                                //$objRichText->createText($snapshotRow."\n");
                            } else {
                                $snapshotRow = str_replace("[###excel_section_flag###]", "", $snapshotRow);
                                //$objItalic = $objRichText->createTextRun($snapshotRow."\n");
                                //$objItalic->getFont()->setItalic(true);
                            }
                        }
                        //$aRow = 'A' . $row;
                        //$ews->setCellValue($aRow, $objRichText);
                        $data = array();
                        $data[0] = $snapshotRow;
                        //$writer->addRowWithStyle($data, $rowStyle);
                        $spoutRow = WriterEntityFactory::createRowFromArray($data, $rowStyle);
                        $writer->addRow($spoutRow);

//                if( strpos((string)$snapshot, '[Form Section]') !== false ) {
//                    $ews->getStyle($aRow)->getFont()->setItalic(true);
//                }

                        if (++$i < $numItems) {
                            $rowCount = $rowCount + 1;
                        }
                    }
                }
            } else {

                if( $useCache ) {
                    $formnodesCache = $message->getFormnodesCache();
                }
                //$formnodesCache = "<formnode>"."<section>"."</section>"."</formnode>"; //testing

                if( !$formnodesCache ) {
                    $trclassname = "";
                    $table = FALSE;
                    $formnodesCache = $formNodeUtil->getFormNodeHolderShortInfo($message,$message->getMessageCategory(),$table,$trclassname);
                    //exit("use direct value");
                }

                if( !$formnodesCache ) {
                    $formnodesCache = "Error getting entry information in XML format!";
                }

                //convert XML to text
                $table = FALSE;
                $showLabelForce = TRUE;
                $withValue = TRUE;
                $formnodesCacheStr = $formNodeUtil->xmlToTable($formnodesCache,$table,$showLabelForce,$withValue);

                if( !$formnodesCacheStr ) {
                    $formnodesCacheStr = "Error converting entry information XML to text!";
                }

                //$useChunks = TRUE;
                $useChunks = FALSE;

                if( $useChunks ) {
                    //divide results by chunks of 21 rows in order to fit them in the excel row max height
                    $snapshotArrChunks = array_chunk($formnodesCacheStr, 21);

                    foreach( $snapshotArrChunks as $snapshotChunkRow ) {
                        $data = array();
                        $data[0] = $snapshotChunkRow;
                        //$writer->addRowWithStyle($data, $rowStyle);
                        $spoutRow = WriterEntityFactory::createRowFromArray($data, $rowStyle);
                        $writer->addRow($spoutRow);
                    }
                } else {
                    $data = array();
                    $data[0] = $formnodesCacheStr;

                    //Entry in XML
                    $data[1] = $formnodesCache;

                    //$writer->addRowWithStyle($data, $rowStyle);
                    $spoutRow = WriterEntityFactory::createRowFromArray($data, $rowStyle);
                    $writer->addRow($spoutRow);
                }
            }
            //////// EOF subsection with message snapshot info ////////

            //increment row index
            $rowCount = $rowCount + 1;

            $message = null;
            $em->clear();
            gc_collect_cycles();

        }//foreach $entryId


        // Auto size columns for each worksheet

        $writer->close();
    }

    function print_mem($description='The script is now using') {
        return null;

        /* Currently used memory */
        $mem_usage = memory_get_usage();

        /* Peak memory usage */
        $mem_peak = memory_get_peak_usage();

        $msg = $description.': <strong>' . round($mem_usage / (1024*1000)) . 'MB</strong> of memory.<br>';
        $msg = $msg . 'Peak usage: <strong>' . round($mem_peak / (1024*1000)) . 'MB</strong> of memory.<br><br>';

        $logger = $this->container->get('logger');
        $logger->notice($msg);
    }

//    public function obtainPhoneCanonical($phone) {
//        //echo "original phone=".$phoneCanonical."<br>";
//        $phoneCanonical = str_replace(' ', '', $phone); // Replaces all spaces with hyphens.
//        $phoneCanonical = preg_replace('/[^0-9]/', '', $phoneCanonical); // Removes special chars.
//        //exit("phoneCanonical=".$phoneCanonical);
//        return $phoneCanonical;
//    }
}
