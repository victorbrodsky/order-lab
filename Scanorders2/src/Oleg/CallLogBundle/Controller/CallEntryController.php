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

use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
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
use Oleg\OrderformBundle\Form\DataTransformer\MrnTypeTransformer;
use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\UserdirectoryBundle\Entity\ModifierInfo;
use Oleg\UserdirectoryBundle\Entity\Spot;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


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
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //testing
        //$userServiceUtil = $this->get('user_service_utility');
        //$results = $userServiceUtil->getFuzzyTest();
        //exit("<br>exit");


        $em = $this->getDoctrine()->getManager();
        $calllogUtil = $this->get('calllog_util');
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
        $res = $this->getCalllogEntryFilter($request);

        if( $res['redirect'] ) {
            return $res['redirect'];
        }

        $query = $res['query'];
        $filterform = $res['filterform'];
        $advancedFilter = $res['advancedFilter'];

        $paginator  = $this->get('knp_paginator');
        $messages = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            //$request->query->getInt('page', 1),
            $limit,      /*limit per page*/
            array('wrap-queries'=>true)
        );
        //echo "messages count=".count($messages)."<br>";

        //all messages will show only form fields for this message category node
//        $categoryStr = "Pathology Call Log Entry";
//        $messageCategoryInfoNode = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
//        if( !$messageCategoryInfoNode ) {
//            throw new \Exception( "MessageCategory type is not found by name '".$categoryStr."'" );
//        }

        $eventObjectType = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->findOneByName("Message");
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
            $title = $title . " (".$messages->getTotalItemCount()." matching entries)";
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
            'shownavbarfilter' => false
            //'navbarfilterform' => $navbarfilterform->createView()
            //'sitename' => $this->container->getParameter('calllog.sitename')
            //'calllogsearch' => $calllogsearch,
            //'calllogsearchtype' => $calllogsearchtype,
        );
    }

    public function getCalllogEntryFilter(Request $request, $limit=null)
    {

        $em = $this->getDoctrine()->getManager();
        $calllogUtil = $this->get('calllog_util');
        $userServiceUtil = $this->get('user_service_utility');
        //$userSecUtil = $this->get('user_security_utility');
        //$sitename = $this->container->getParameter('calllog.sitename');

        //$route = $request->get('_route');
        //$title = "Call Case List";

        //$alerts = false;
        //if( $route == "calllog_alerts" ) {
        //    $alerts = true;
        //    $title = $title . " (Alerts)";
        //}

        //$messageStatuses
        $messageStatuses = $em->getRepository('OlegOrderformBundle:MessageStatusList')->findBy(array('type'=>array('default','user-added')));
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

        //child nodes of "Pathology Call Log Entry"
        //$messageCategoryParent = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName("Encounter Note");
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

        //$messageCategoriePathCall = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName("Pathology Call Log Entry");
        //$node1 = array('id'=>1,'text'=>'node1');
        //$node2 = array('id'=>2,'text'=>'node2');
        //$messageCategories = array($node1,$node2);

        //use site setting
//        $defaultMrnType = $userSecUtil->getSiteSettingParameter('keytypemrn',$sitename);
//        //echo "defaultMrnType=".$defaultMrnType."; ID=".$defaultMrnType->getId()."<br>";
//        if( !$defaultMrnType ) {
//            $defaultMrnType = $em->getRepository('OlegOrderformBundle:MrnType')->findOneByName("New York Hospital MRN");
//        }
        $defaultMrnType = $calllogUtil->getDefaultMrnType();
        //$defaultMrnTypeId = null;
        if( $defaultMrnType ) {
            $defaultMrnTypeId = $defaultMrnType->getId();
        }
        //echo "defaultMrnTypeId=".$defaultMrnTypeId."<br>";

        //get mrntypes ($mrntypeChoices)
        $mrntypeChoices = array();
        $mrntypeChoicesArr = $em->getRepository('OlegOrderformBundle:MrnType')->findBy(array('type'=>array('default','user-added')));
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
            'method'=>'GET',
            'form_custom_value'=>$navbarParams
        ));
        $navbarfilterform->handleRequest($request);
        $calllogsearchtype = $navbarfilterform['searchtype']->getData();
        $calllogsearch = $navbarfilterform['search']->getData();
        //$metaphone = $navbarfilterform['metaphone']->getData();
        //echo "navbar: calllogsearchtype=".$calllogsearchtype."; calllogsearch=".$calllogsearch."<br>";
        if( $calllogsearchtype == 'MRN or Last Name' ) {
            $searchFilter = $calllogsearch;
            //$mrntypeFilter = $defaultMrnTypeId;
        }
        //if( $calllogsearchtype == 'NYH MRN' ) {
        if( $calllogsearchtype == $defaultMrnType ) {
            $searchFilter = $calllogsearch;
        }
        if( $calllogsearchtype == 'Entry full text' ) {
            $entryBodySearchFilter = $calllogsearch;
        }
        if( $calllogsearchtype == 'Last Name' ) {
            $searchFilter = $calllogsearch;
        }
        if( $calllogsearchtype == 'Last Name similar to' ) {
            $searchFilter = $calllogsearch;
            $metaphone = true;
        }
        if( $calllogsearchtype == 'Message Type' ) {
            $messageCategoryTypeId = $calllogUtil->getMessageTypeByString($calllogsearch,$messageCategories,$messageCategorieDefaultIdStr);
            //echo "navbar messageCategoryTypeId=".$messageCategoryTypeId."<br>";
            $messageCategory = $messageCategoryTypeId; //Other_59 => Other: Chemistry: Pathology Call Log Entry
        }
        //echo "navbar: searchFilter=".$searchFilter."; entryBodySearchFilter=".$entryBodySearchFilter."<br>";
        ///////////////// EOF search in navbar /////////////////

        $params = array(
            'messageStatuses' => $messageStatusesChoice,
            'messageCategories' => $messageCategories, //for home to list all entries page
            //'messageCategoryDefault' => $messageCategoriePathCall->getId(),
            //'mrntype' => $defaultMrnTypeId,
            'mrntypeChoices' => $mrntypeChoices,
            'mrntypeDefault' => $defaultMrnTypeId,
            'referringProviders' => $referringProviders,
            'search' => $searchFilter,
            'entryBodySearch' => $entryBodySearchFilter,
            'messageCategoryType' => $messageCategoryTypeId,
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
        $specialtyFilter = $filterform['referringProviderSpecialty']->getData();
        $patientListTitleFilter = $filterform['patientListTitle']->getData();
        $attendingFilter = $filterform['attending']->getData();
        $entryTags = $filterform['entryTags']->getData();

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
        $repository = $em->getRepository('OlegOrderformBundle:Message');
        $dql = $repository->createQueryBuilder('message');
        $dql->leftJoin("message.patient","patient");
        $dql->leftJoin("patient.mrn","mrn");
        $dql->leftJoin("patient.lastname","lastname");
        $dql->leftJoin("patient.firstname","firstname");
        $dql->leftJoin("message.encounter","encounter");
        $dql->leftJoin("message.calllogEntryMessage","calllogEntryMessage");

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

        //testing
        //$dql->leftJoin( 'OlegOrderformBundle:Message', 'message2', 'WITH', 'message.oid = message2.oid AND message.version > message2.version' );
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
                    if( strpos($searchFilter, ',') === false ) {
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
                                $latentLastname = trim($latentLastname);
                                $latentFirstname = trim($latentFirstname);
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
                $mergeMrn = $searchFilter;

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
            $entryBodySearchStr = "SELECT s FROM OlegUserdirectoryBundle:ObjectTypeText s WHERE " .
                "(message.id = CAST(s.entityId AS ".$castAs.") AND s.entityName='Message' AND LOWER(s.value) LIKE LOWER(:entryBodySearch))";
            $dql->andWhere("EXISTS (" . $entryBodySearchStr . ")");
            $queryParameters['entryBodySearch'] = "%" . $entryBodySearchFilter . "%";

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
//                $messageCategoryEntity = $em->getRepository('OlegOrderformBundle:MessageCategory')->find($calllogsearch);
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

            $thisPatient = $em->getRepository('OlegOrderformBundle:Patient')->findByValidMrnAndMrntype($mergeMrn,$mergeMrnKeytypeId);
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

        //$limit = 10;
        $query = $em->createQuery($dql);
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

//        $paginator  = $this->get('knp_paginator');
//        $messages = $paginator->paginate(
//            $query,
//            $this->get('request')->query->get('page', 1), /*page number*/
//            //$request->query->getInt('page', 1),
//            $limit      /*limit per page*/
//        );
//        //echo "messages count=".count($messages)."<br>";
//
//        //all messages will show only form fields for this message category node
////        $categoryStr = "Pathology Call Log Entry";
////        $messageCategoryInfoNode = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
////        if( !$messageCategoryInfoNode ) {
////            throw new \Exception( "MessageCategory type is not found by name '".$categoryStr."'" );
////        }
//
//        $eventObjectType = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->findOneByName("Message");
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
//            //'sitename' => $this->container->getParameter('calllog.sitename')
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
     * Call Entry New Page
     * http://localhost/order/call-log-book/entry/new?mrn-type=4&mrn=3
     *
     * @Route("/entry/new", name="calllog_callentry")
     * @Template("OlegCallLogBundle:CallLog:call-entry.html.twig")
     */
    public function callEntryAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //1) search box: MRN,Name...

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $calllogUtil = $this->get('calllog_util');
        $userSecUtil = $this->get('user_security_utility');
        $orderUtil = $this->get('scanorder_utility');
        $em = $this->getDoctrine()->getManager();
        //$sitename = $this->container->getParameter('calllog.sitename');

        $mrn = trim($request->get('mrn'));
        $mrntype = trim($request->get('mrntype'));
        $encounterNumber = trim($request->get('encounter-number'));
        $encounterTypeId = trim($request->get('encounter-type'));
        //$encounterVersion = trim($request->get('encounter-version'));
        $messageTypeId = trim($request->get('message-type'));

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
        $permittedInstitutions = $orderUtil->getAndAddAtleastOneInstitutionPHI($user,$this->get('session'));
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('calllog_home') );
        }


        $title = "New Entry";
        $titleheadroom = null;

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
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
        $patientActiveStatus = $em->getRepository('OlegOrderformBundle:PatientRecordStatusList')->findOneByName("Active");
        if( $patientActiveStatus ) {
            $patient->setPatientRecordStatus($patientActiveStatus);
        }

        //create dummy encounter #1 just to display fields in "Patient Info"
        $encounter1 = new Encounter(true,'dummy',$user,$system);
        $encounter1->setProvider($user);
        $patient->addEncounter($encounter1); //add new encounter to patient

//        $encounter2 = $em->getRepository('OlegOrderformBundle:Encounter')->findOneEncounterByNumberAndType($encounterTypeId,$encounterNumber);
//
//        //check whether patient MRN supplied in the URL corresponds to the supplied encounter number.
//        // If it does not, show the normal /entry/new page but with the notification "
//        // Encounter "1111" of type "blah" is not with patient whose MRN of type "whatever" is "1111"
//        if( $mrn && $mrntype && $encounter2 ) {
//            if( !$em->getRepository('OlegOrderformBundle:Encounter')->isPatientEncounterMatch($mrn,$mrntype,$encounter2) ) {
//
//                $mrntypeStr = "";
//                $mrntypeEntity = $em->getRepository('OlegOrderformBundle:MrnType')->find($mrntype);
//                if( $mrntypeEntity ) {
//                    $mrntypeStr = $mrntypeEntity->getName()."";
//                }
//
//                $encounterMsg = "Encounter $encounterNumber of type ".$encounter2->obtainEncounterNumber()." is not with patient whose MRN of type $mrntypeStr is $mrn";
//                $this->get('session')->getFlashBag()->add(
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
//            $withdummyfields = true;
//            //$locationTypePrimary = null;
//            $encounterLocationType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Encounter Location");
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
        ///////////// EOF Message //////////////

        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle,$readonlyEncounter); //entry/new

        //$encounterid = $calllogUtil->getNextEncounterGeneratedId();

        $formnodetrigger = 1;
        if( $messageTypeId ) {
            $formnodetrigger = 0; //build formnodes from top to bottom
        }

        //top message category id
        $formnodeTopHolderId = null;
        $messageCategory = $calllogUtil->getDefaultMessageCategory();
        //$categoryStr = "Pathology Call Log Entry";
        //$messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment != 'live' ) {
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                "THIS IS A TEST SERVER. USE ONLY FOR TESTING !!!"
            );
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
     * Save Call Log Entry
     * @Route("/entry/save", name="calllog_save_entry", options={"expose"=true})
     * @Template("OlegCallLogBundle:CallLog:call-entry.html.twig")
     * @Method("POST")
     */
    public function saveEntryAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        //exit('save entry');
        //case 1: patient exists: create a new encounter to DB and add it to the existing patient
        //add patient id field to the form (id="oleg_calllogbundle_patienttype_id") or use class="calllog-patient-id" input field.
        //case 2: patient does not exists: create a new encounter to DB

        $user = $this->get('security.token_storage')->getToken()->getUser();
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
//        $mrntype = trim($request->get('mrntype'));
        $mrn = null;
        $mrntype = null;

        $title = "Save Entry";

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $status = 'valid';
        $cycle = 'new';
        $formtype = 'call-entry';

        $message = $this->createCalllogEntryMessage($user,$permittedInstitutions,$system); //save

        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle); ///entry/save

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

                ////////////// processing new encounter ///////////////////
                $newEncounter->setSource($system);
                $newEncounter->setInstitution($institution);
                $newEncounter->setVersion(1);

                //assign generated encounter number ID
                $key = $newEncounter->obtainAllKeyfield()->first();
                //echo "key=".$key."<br>"; //TODO: test - why key count($newEncounter->obtainAllKeyfield()) == 0 after deprecated removed? because disabled!?
                //exit('1');
                if( !$key ) {
                    //$newKeys = $newEncounter->createKeyField();
                    //if( count($newKeys) > 0 ) {
                    //    $key = $newKeys->first();
                    //} else {
                    //    throw new \Exception( "CallLog save new Entry Action: Encounter does not have any keys." );
                    //}
                    throw new \Exception( "CallLog save new Entry Action: Encounter does not have a key." );
                }
                $em->getRepository('OlegOrderformBundle:Encounter')->setEncounterKey($key, $newEncounter, $user);

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
                $calllogUtil->processReferringProviders($newEncounter,$system);
                ////////////// EOF processing new encounter ///////////////////

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

                    //CASE 1
                    echo "case 1: patient exists in this Call Entry form: create a new encounter to DB and add it to the existing patient <br>";
                    //get a new encounter without id $newEncounter
    //                foreach( $encounter->getReferringProviders() as $referringProvider ) {
    //                    echo "encounter referringProvider phone=".$referringProvider->getReferringProviderPhone()."<br>";
    //                }

                    $patient = $em->getRepository('OlegOrderformBundle:Patient')->find($patient->getId());
                    $message->clearPatient();
                    $message->addPatient($patient);

                    //backup patient to message
                    $calllogUtil->copyPatientBackupToMessage($message,$patient);

                    /////////// processing new encounter ///////////
                    //reset institution from the patient
                    $newEncounter->setInstitution($patient->getInstitution());

                    $patient->addEncounter($newEncounter);

                    //update patient's last name, first name, middle name, dob, sex, ...
                    $calllogUtil->updatePatientInfoFromEncounter($patient, $newEncounter, $user, $system);
                    /////////// EOF processing new encounter ///////////

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
                        //new encounter
                        $em->persist($newEncounter);
                        $em->persist($message);
                        $em->flush();
                    }


                    //add patient to the complex patient list specified by patientListTitle if the option addPatientToList is checked.
                    //do it after message is in DB and has ID
                    $calllogUtil->addToPatientLists($patient,$message,$testing);

                    $msg = "New Encounter (ID#" . $newEncounter->getId() . ") is created with number " . $newEncounter->obtainEncounterNumber() . " for the Patient with ID #" . $patient->getId();

                } else {
                    //CASE 2
                    echo "case 2: patient does not exists in this Call Entry form: create a new encounter to DB <br>";
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
                //////////////////// EOF Processing ////////////////////////

                //set encounter as message's input
                //$message->addInputObject($newEncounter);
                //$em->persist($message);
                //$em->flush($message);

                //process form nodes: process each form field and record it to DB
                $formNodeUtil = $this->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request,$message->getMessageCategory(),$message,$testing); //testing
                //exit('after formnode');

                $calllogUtil->deleteAllOtherMessagesByOid($message,$cycle,$testing);

                //log search action
                if( $msg ) {
                    $eventType = "New Call Log Book Entry Submitted";

                    $eventStr = $calllogUtil->getEventLogDescription($message,$patient,$newEncounter);
                    //exit('eventStr='.$eventStr);

                    //$eventStr = $eventStr . " submitted by " . $user;

                    if( !$testing ) {
                        $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $eventStr, $user, $message, $request, $eventType);
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

            $this->get('session')->getFlashBag()->add(
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

    public function createCalllogEntryForm($message, $mrntype=null, $mrn=null, $cycle, $readonlyEncounter=false) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $calllogUtil = $this->get('calllog_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $sitename = $this->container->getParameter('calllog.sitename');

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
            //$mrntype = 1;
//            $defaultMrnType = $userSecUtil->getSiteSettingParameter('keytypemrn',$sitename);
//            //echo "defaultMrnType=".$defaultMrnType.$defaultMrnType."<br>";
//            if( !$defaultMrnType ) {
//                $defaultMrnType = $em->getRepository('OlegOrderformBundle:MrnType')->findOneByName("New York Hospital MRN");
//            }
            $defaultMrnType = $calllogUtil->getDefaultMrnType();
            $mrntype = $defaultMrnType->getId();
        }

        if( $cycle == 'show' ) {
            $disabled = true;
        } else {
            $disabled = false;
        }

        //$timezones
        //$userTimeZone = $user->getPreferences()->getTimezone();
        $userTimeZone = $userSecUtil->getSiteSettingParameter('timezone',$sitename);

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
            'referringProviders-readonly' => false,
            'readonlyLocationType' => true //lock the "Location Type" field (with the default "Encounter Location" value in it)
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
        $orderUtil = $this->get('scanorder_utility');
        $calllogUtil = $this->get('calllog_util');

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
            $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->find($messageCategoryId);
        } else {
            //$categoryStr = "Pathology Call Log Entry";
            //$categoryStr = "Nesting Test"; //testing
            //$messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
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
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
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
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $calllogUtil = $this->get('calllog_util');
        $searchedArr = array();

        //$currentUrl = trim($request->get('currentUrl'));
        //echo "currentUrl=".$currentUrl."<br>";

        $formtype = trim($request->get('formtype'));

        //$patientsData = $this->searchPatient( $request, true, null, false ); //testing
        $patientsData = $this->searchPatient( $request, true);

        $allowCreateNewPatient = true;
        $patients = $patientsData['patients'];
        $searchedStr = $patientsData['searchStr'];
        $searchedArr[] = "(Searched for ".$searchedStr.")";
        //echo "patients=".count($patients)."<br>";

        if( count($patients) == 0 ) {
            //search again, but only by mrn
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
                $allowCreateNewPatient = false;
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

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($resData));
        return $response;
    }

    //search patients: used by JS when search for patient in the new entry page (calllog_search_patient)
    // and to verify before creating patient if already exists (calllog_create_patient)
    public function searchPatient( $request, $evenlog=false, $params=null, $turnOffMetaphone=false ) {

        $userServiceUtil = $this->get('user_service_utility');

        $mrntype = trim($request->get('mrntype')); //ID of mrn type
        $mrn = trim($request->get('mrn'));
        $dob = trim($request->get('dob'));
        $lastname = trim($request->get('lastname'));
        $firstname = trim($request->get('firstname'));
        $metaphone = trim($request->get('metaphone'));
        //print_r($allgets);
        //echo "metaphone=".$metaphone."<br>";
        //exit('1');

        $exactMatch = true;
        $matchAnd = true;

        if( $params ) {
            $mrntype = ( array_key_exists('mrntype', $params) ? $params['mrntype'] : null);
            $mrn = ( array_key_exists('mrn', $params) ? $params['mrn'] : null);
            $dob = ( array_key_exists('dob', $params) ? $params['dob'] : null);
            $lastname = ( array_key_exists('lastname', $params) ? $params['lastname'] : null);
            $firstname = ( array_key_exists('firstname', $params) ? $params['firstname'] : null);
            $metaphone = ( array_key_exists('metaphone', $params) ? $params['metaphone'] : null);
        }

        //echo "mrntype=".$mrntype."<br>";
        //echo "mrn=".$mrn."<br>";

        if( $turnOffMetaphone ) {
            $metaphone = null;
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

        //echo "mrntype=".$mrntype."<br>";
        //echo "mrn=".$mrn."<br>";

        //mrn
        if( $mrntype && $mrn ) {

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

        //$lastname = null;
        //$firstname = null;
        //Last Name AND First Name
        if( ($lastname || $firstname) && ($where == false || $matchAnd == true) ) {
            //$lastname = "Doe";
            //echo "1 lastname=".$lastname."<br>";
            //echo "1 firstname=".$firstname."<br>";

            $searchCriterionArr = array();

            //only last name
            if( $lastname && !$firstname ) {
                $searchArr[] = "Last Name: " . $lastname;

                $statusStr = "(lastname.status = :statusValid OR lastname.status = :statusAlias)";

                if( $metaphone ) {
                    $lastnameCriterion = $userServiceUtil->getMetaphoneStrLike("lastname.field","lastname.fieldMetaphone",$lastname,$parameters);
                    if( $lastnameCriterion ) {
                        $searchCriterionArr[] = $lastnameCriterion . " AND " . $statusStr;

                        $parameters['statusValid'] = 'valid';
                        $parameters['statusAlias'] = 'alias';

                        $where = true;
                    }
                } else {
                    //exact search
                    $searchCriterionArr[] = "LOWER(lastname.field) LIKE LOWER(:lastname) AND $statusStr";
                    $parameters['lastname'] = "%".$lastname."%";
                    $parameters['statusValid'] = 'valid';
                    $parameters['statusAlias'] = 'alias';
                    $where = true;
                }
                //$statusStr = "(lastname.status = :statusValid OR lastname.status = :statusAlias)";
                ////$statusEncounterStr = "(encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias)";
                ////$searchCriterionArr[] = "(lastname.field = :lastname AND $statusStr) OR (encounterLastname.field = :lastname AND $statusEncounterStr)";

                //$searchCriterionArr[] = "lastname.field = :lastname AND $statusStr";
                //$parameters['lastname'] = $lastname;
                //$searchCriterionArr[] = "lastname.field LIKE :lastname AND $statusStr";
                //$parameters['lastname'] = '%'.$lastname.'%';

                //status
                //$dql->andWhere("lastname.status = :statusValid OR lastname.status = :statusAlias");
                //$dql->andWhere("encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias");
                //$parameters['statusValid'] = 'valid';
                //$parameters['statusAlias'] = 'alias';
                //$where = true;
            }

            //only first name
            if( $firstname && !$lastname ) {
                $searchArr[] = "First Name: " . $firstname;

                $statusStr = "(firstname.status = :statusValid OR firstname.status = :statusAlias)";

                if( $metaphone ) {
                    $firstnameCriterion = $userServiceUtil->getMetaphoneStrLike("firstname.field","firstname.fieldMetaphone",$firstname,$parameters);
                    if( $firstnameCriterion ) {
                        $searchCriterionArr[] = $firstnameCriterion . " AND " . $statusStr;

                        $parameters['statusValid'] = 'valid';
                        $parameters['statusAlias'] = 'alias';

                        $where = true;
                    }
                } else {
                    //exact search
                    $searchCriterionArr[] = "LOWER(firstname.field) LIKE LOWER(:firstname) AND $statusStr";
                    $parameters['firstname'] = "%".$firstname."%";
                    $parameters['statusValid'] = 'valid';
                    $parameters['statusAlias'] = 'alias';
                    $where = true;
                }

                //$statusStr = "(firstname.status = :statusValid OR firstname.status = :statusAlias)";
                ////$statusEncounterStr = "(encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias)";
                ////$searchCriterionArr[] = "(firstname.field = :firstname AND $statusStr) OR (encounterFirstname.field = :firstname AND $statusEncounterStr)";
                //$searchCriterionArr[] = "firstname.field = :firstname AND $statusStr";
                //$parameters['firstname'] = $firstname;

                //status
                //$dql->andWhere("firstname.status = :statusValid OR firstname.status = :statusAlias");
                //$dql->andWhere("encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias");
                //$parameters['statusValid'] = 'valid';
                //$parameters['statusAlias'] = 'alias';
                //$where = true;
            }

            if( $firstname && $lastname ) {
                $searchArr[] = "Last Name: " . $lastname;
                $searchArr[] = "First Name: " . $firstname;

                if( $metaphone ) {

                    $lastnameStatusStr = "(lastname.status = :statusValid OR lastname.status = :statusAlias)";
                    $lastnameCriterion = $userServiceUtil->getMetaphoneStrLike("lastname.field","lastname.fieldMetaphone",$lastname,$parameters,"lastname");
                    if ($lastnameCriterion) {
                        $searchCriterionArr[] = $lastnameCriterion . " AND " . $lastnameStatusStr;
                        //$searchCriterionArr[] = $lastnameCriterion;

                        $parameters['statusValid'] = 'valid';
                        $parameters['statusAlias'] = 'alias';

                        $where = true;
                    }

                    $firstnameStatusStr = "(firstname.status = :statusValid OR firstname.status = :statusAlias)";
                    $firstnameCriterion = $userServiceUtil->getMetaphoneStrLike("firstname.field","firstname.fieldMetaphone",$firstname,$parameters,"firstname");
                    if ($firstnameCriterion) {
                        $searchCriterionArr[] = $firstnameCriterion . " AND " . $firstnameStatusStr;
                        //$searchCriterionArr[] = $firstnameCriterion;

                        $parameters['statusValid'] = 'valid';
                        $parameters['statusAlias'] = 'alias';

                        $where = true;
                    }

                } else {

                    //exact search
                    //last name: status
                    $statusStrLastname = "(lastname.status = :statusValid OR lastname.status = :statusAlias)";
                    //$searchCriterionArr[] = "lastname.field LIKE :lastname AND $statusStr";
                    //$parameters['lastname'] = '%'.$lastname.'%';
                    $searchCriterionArr[] = "lastname.field = :lastname AND $statusStrLastname";
                    $parameters['lastname'] = $lastname;

                    //first name: status
                    $statusStrFirstname = "(firstname.status = :statusValid OR firstname.status = :statusAlias)";
                    //$searchCriterionArr[] = "firstname.field LIKE :firstname AND $statusStr";
                    //$parameters['firstname'] = '%'.$firstname.'%';
                    $searchCriterionArr[] = "firstname.field = :firstname AND $statusStrFirstname";
                    $parameters['firstname'] = $firstname;

                    $parameters['statusValid'] = 'valid';
                    $parameters['statusAlias'] = 'alias';
                    $where = true;

                }//if

                //testing
                if(0) {
                    echo "metaphone=".$metaphone."<br>";
                    echo "<pre>";
                    print_r($searchCriterionArr);
                    echo "</pre>";
                    echo "parameters:"."<br><pre>";
                    print_r($parameters);
                    echo "</pre>";
                    exit();
                }
            }

            if( count($searchCriterionArr) > 0 ) {
                //" OR " or " AND "
                $searchCriterionStr = implode(" AND ", $searchCriterionArr);
                $dql->andWhere($searchCriterionStr);
            }
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
            $patients = $query->getResult();

            //testing
            //echo "sql=".$query->getSql()."<br>";
            //echo "parameters:"."<br><pre>";
            //print_r($query->getParameters());
            //exit();
            //echo "</pre>";
//            echo "<br>";
//            foreach( $patients as $patient ) {
//                echo "ID=".$patient->getId().": ".$patient->getFullPatientName()."<br>";
//                echo "patient=".$patient."<br>";
//            }
//            exit('patients count='.count($patients));

            //log search action
            if( $evenlog ) {
                if( count($patients) == 0 ) {
                    $patientEntities = null;
                } else {
                    $patientEntities = $patients;
                }
                $user = $this->get('security.token_storage')->getToken()->getUser();
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
        //exit('Finished.');

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

        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $securityUtil = $this->get('order_security_utility');
        $userSecUtil = $this->get('user_security_utility');
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
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER')) {
            //return $this->redirect($this->generateUrl('calllog-nopermission'));
            $res['patients'] = null;
            $res['output'] = "You don't have a permission to create a new patient record";
            $response->setContent(json_encode($res));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $calllogUtil = $this->get('calllog_util');

        $mrn = trim($request->get('mrn'));
        $mrntype = trim($request->get('mrntype')); //ID
        $dob = trim($request->get('dob'));
        $lastname = trim($request->get('lastname'));
        $firstname = trim($request->get('firstname'));
        $middlename = trim($request->get('middlename'));
        $suffix = trim($request->get('suffix'));
        $sex = trim($request->get('sex'));
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
        //check only by mrn: pass params with only mrn and mrntype
        $mrnParams = array();
        $mrnParams['mrntype'] = $mrntype;
        $mrnParams['mrn'] = $mrn;
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

        //echo "mrn=".$fieldValue."<br>";
        //echo "keytype=".$keytype." (".$keytypeEntity.")<br>";
        //exit("1");

        $extra = array();
        $extra["keytype"] = $keytype; //must be keytype ID

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

        ////0 should be maintained and not deleted out when the patient is registered
        //if(0) {
            if( $mrn ) {    //mrn with leading zeros
                $mrnClean = ltrim($mrn, '0');
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
            $sexObj = $em->getRepository('OlegUserdirectoryBundle:SexList')->findOneById( $sex );

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
     * Get Patient Titles according to a new encounter date specified by nowStr
     * @Route("/patient/title/", name="calllog_get_patient_title", options={"expose"=true})
     * @Method("GET")
     */
    public function getPatientTitleAction(Request $request) {

        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $patientId = trim($request->get('patientId'));
        $nowStr = trim($request->get('nowStr'));
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
     * TODO: make messageVersion can be null and find by messageOid only by the most recent version
     * @Route("/entry/view/{messageOid}/{messageVersion}", name="calllog_callentry_view")
     * @Route("/entry/view-latest-encounter/{messageOid}/{messageVersion}", name="calllog_callentry_view_latest_encounter")
     * @Method("GET")
     * @Template("OlegCallLogBundle:CallLog:call-entry-view.html.twig")
     */
    public function getCallLogEntryAction(Request $request, $messageOid, $messageVersion=null)
    {

        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        //$userSecUtil = $this->get('user_security_utility');
        $userServiceUtil = $this->get('user_service_utility');
        $calllogUtil = $this->get('calllog_util');
        $route = $request->get('_route');

        $pathPostfix = "";
        $cycle = "show";
        //$title = "Call Log Entry";
        $formtype = "call-entry";

        $formbased = false;
        //$formbased = true;

        //$patientId = trim($request->get('patientId'));
        //$nowStr = trim($request->get('nowStr'));
        //echo "patientId=".$patientId."<br>";
        //echo "nowStr=".$nowStr."<br>";
        //$messageId = 142; //154; //testing

        $em = $this->getDoctrine()->getManager();

        if( !is_numeric($messageVersion) || !$messageVersion ) {
            $messageLatest = $em->getRepository('OlegOrderformBundle:Message')->findByOidAndVersion($messageOid);

            if( !$messageLatest && !$messageVersion ) {
                //handle case with th real DB id: http://localhost/order/call-log-book/entry/view/267
                $messageLatest = $em->getRepository('OlegOrderformBundle:Message')->find($messageOid);
            }

            if( $messageLatest ) {
                return $this->redirect($this->generateUrl('calllog_callentry_view', array(
                    'messageOid' => $messageLatest->getOid(),
                    'messageVersion' => $messageLatest->getVersion()
                )));
            }

            throw new \Exception( "Latest Message is not found by oid ".$messageOid );
        }

        $message = $em->getRepository('OlegOrderformBundle:Message')->findByOidAndVersion($messageOid,$messageVersion);
        if( !$message ) {
            throw new \Exception( "Message is not found by oid ".$messageOid." and version ".$messageVersion );
        }

        //testing
        //$this->get('user_formnode_utility')->updateFieldsCache($message);
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
        //$mesInfo = $this->get('user_formnode_utility')->getFormNodeHolderShortInfo($message,$message->getMessageCategory(),1,"");
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
                $latestEncounter = $em->getRepository('OlegOrderformBundle:Encounter')->findLatestVersionEncounter($encounter);
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
            $mrntype = $mrnRes->getKeytype()->getId();
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

            $titleBody = $titleBody . " | ".$messageInfo;

        } else {
            $patient = null;
            $mrntype = null;
            $mrn = null;
            $patientId = null;

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

        //top message category id
        $formnodeTopHolderId = null;
        //$categoryStr = "Pathology Call Log Entry";
        //$messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
        $messageCategory = $calllogUtil->getDefaultMessageCategory();
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        $eventObjectType = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->findOneByName("Message");
        if( $eventObjectType ) {
            $eventObjectTypeId = $eventObjectType->getId();
        } else {
            $eventObjectTypeId = null;
        }

        //View Previous Version(s)
        $allMessages = $em->getRepository('OlegOrderformBundle:Message')->findAllMessagesByOid($messageOid);

        //previous entries similar to calllog-list-previous-entries: get it in the view by ajax

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
                'sitename' => $this->container->getParameter('calllog.sitename'),
                'titleheadroom' => $titleBody,
                'formnodeTopHolderId' => $formnodeTopHolderId,
                'eventObjectTypeId' => $eventObjectTypeId,
                'allMessages' => $allMessages,
                'pathPostfix' => $pathPostfix,
                'formbased' => $formbased
            );
        } else {
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
                'sitename' => $this->container->getParameter('calllog.sitename'),
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

    /**
     * @Route("/single-export-csv/{messageOid}/{messageVersion}", name="calllog_single_export_csv")
     * @Template("OlegCallLogBundle:Export:call-entry-export-csv.html.twig")
     */
    public function exportSingleCsvAction(Request $request, $messageOid, $messageVersion=null)
    {
        if (false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_ADMIN")) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if( !is_numeric($messageVersion) || !$messageVersion ) {
            $messageLatest = $em->getRepository('OlegOrderformBundle:Message')->findByOidAndVersion($messageOid);

            if( !$messageLatest && !$messageVersion ) {
                //handle case with th real DB id: http://localhost/order/call-log-book/entry/view/267
                $messageLatest = $em->getRepository('OlegOrderformBundle:Message')->find($messageOid);
            }

            if( $messageLatest ) {
                return $this->redirect($this->generateUrl('calllog_callentry_view', array(
                    'messageOid' => $messageLatest->getOid(),
                    'messageVersion' => $messageLatest->getVersion()
                )));
            }

            throw new \Exception( "Latest Message is not found by oid ".$messageOid );
        }

        $message = $em->getRepository('OlegOrderformBundle:Message')->findByOidAndVersion($messageOid,$messageVersion);
        if( !$message ) {
            throw new \Exception( "Message is not found by oid ".$messageOid." and version ".$messageVersion );
        }

        //testing
        //$this->get('user_formnode_utility')->updateFieldsCache($message);

        $fileName = "Call-Log-Entry-ID" . $message->getOid();

        $fileName = str_replace(".","-",$fileName);

        $ext = "XLSX";
        $ext = "CSV";

        $this->createCalllogListExcelSpout(array($message),$fileName,$user,$ext);

        exit();
        //exit('single-export-csv');
    }


    /**
     * @Route("/export_csv/", name="calllog_export_csv")
     * @Route("/export_csv/all/", name="calllog_export_csv_all")
     * @Template("OlegCallLogBundle:Export:call-entry-export-csv.html.twig")
     */
    public function exportCsvAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        set_time_limit(600); //600 seconds => 10 mins

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userSecUtil = $this->get('user_security_utility');
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
            $res = $this->getCalllogEntryFilter($request, $limit);

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
//        $repository = $em->getRepository('OlegOrderformBundle:Message');
//        $dql = $repository->createQueryBuilder("message");
//        //$dql->select('message.id');
//        $dql->select('message');
//        $query = $em->createQuery($dql);
//        $query->setMaxResults(1);
//        $entries = $query->getResult();
        //foreach($entries as $message) {
            //echo "encounters=".count($message->getEncounter())."<br>";
        //}
//        echo "query=".$query->getSql()."<br>";
        //return array('filename'=>'111','title'=>'222');

        if( count($entries) == 0 ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                "No entries found for exporting."
            );
            return $this->redirect( $this->generateUrl('calllog_home') );
        }

        //An entry should be added to the Event Log, Titled "Call Log Book data exported".
        $eventType = "Call Log Book data exported";
        $eventDesc = "Call Log Book data exported on ".date('m/d/Y H:i')." by ".$user.". Exported entries count is ".count($entries);
        $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $eventDesc, $user, $entries, $request, $eventType);

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
            return $this->render('OlegCallLogBundle:Export:call-entry-export-csv.html.twig', array(
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

        $formNodeUtil = $this->get('user_formnode_utility');

        $ea = new Spreadsheet(); // ea is short for Excel Application

        $ea->getProperties()
            ->setCreator($author."")
            ->setTitle('Call Log Book data')
            ->setLastModifiedBy($author."")
            ->setDescription('Call Log Book data list in Excel format')
            ->setSubject('PHP Excel manipulation')
            ->setKeywords('excel php office phpexcel')
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
        $ews->setCellValue('F1', 'Referring Provider');
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


            //Location and Referring Provider
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

            //Referring Provider
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
            $row = $row + 1;
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

            $originalRow = $row;
            $numItems = count($snapshotArrChunks);
            $i = 0;
            foreach( $snapshotArrChunks as $snapshotArrChunk ) {

                //$snapshot = implode("\n",$snapshotArrChunk);
                //$objRichText = new \PHPExcel_RichText();
                $objRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                foreach( $snapshotArrChunk as $snapshotRow ) {
//                    $snapshotRow = "snapshotRow=$snapshotRow<br>";
                    if( strpos($snapshotRow, "[###excel_section_flag###]") === false ) {
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

//                if( strpos($snapshot, '[Form Section]') !== false ) {
//                    $ews->getStyle($aRow)->getFont()->setItalic(true);
//                }

                if( ++$i < $numItems ) {
                    $row = $row + 1;
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
            $row = $row + 1;

        }//foreach



        // Auto size columns for each worksheet
        //\PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        foreach ($ea->getWorksheetIterator() as $worksheet) {

            $ea->setActiveSheetIndex($ea->getIndex($worksheet));

            $sheet = $ea->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            /** @var PHPExcel_Cell $cell */
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

        $formNodeUtil = $this->get('user_formnode_utility');
        $em = $this->getDoctrine()->getManager();

        $useCache = TRUE; //default. Always use cache for export
//        $userSecUtil = $this->container->get('user_security_utility');
//        $sitename = $this->container->getParameter('calllog.sitename');
//        $useCache = $userSecUtil->getSiteSettingParameter('useCache',$sitename);
//        if( !$useCache ) {
//            $useCache = TRUE; //default
//        }

        if( $ext == "XLSX" ) {
            $fileName = $fileName . ".xlsx";
            $writer = WriterFactory::create(Type::XLSX);
        } else {
            $fileName = $fileName . ".csv";
            $writer = WriterFactory::create(Type::CSV);
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

        $writer->addRowWithStyle(
            [
                'ID',                   //0 - A
                'Last Modified',        //1 - B
                'Patient Name',         //2 - C
                'MRN',                  //3 - D
                'Location',             //4 - E
                'Referring Provider',   //5 - F
                'Call Issue',           //6 - G
                'Author'                //7 - H
            ],
            $headerStyle
        );

        //$entryIds = array();
        $count = 0;
        $row = 2;
        foreach( $entryIds as $message ) {
        //foreach( $entryIds as $entryId ) {

            $count++;

            $data = array();

            //$message = $em->getRepository('OlegOrderformBundle:Message')->find($entryId);

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

                //Location and Referring Provider
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

                //Referring Provider
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

            $writer->addRowWithStyle($data,$rowStyle);

            //////// subsection with message snapshot info ////////
            if(0) {
                $row = $row + 1;
                $trclassname = "";

                if( $table=true ) {
                    $snapshotRow = $formNodeUtil->getFormNodeHolderShortInfo($message, $message->getMessageCategory(), true, $trclassname);
                    $data = array();
                    $data[0] = $snapshotRow;
                    $writer->addRowWithStyle($data, $rowStyle);
                } else {
                    $snapshotArr = $formNodeUtil->getFormNodeHolderShortInfo($message, $message->getMessageCategory(), false, $trclassname);

                    //divide results by chunks of 21 rows in order to fit them in the excel row max height
                    $snapshotArrChunks = array_chunk($snapshotArr, 21);

                    $originalRow = $row;
                    $numItems = count($snapshotArrChunks);
                    $i = 0;
                    foreach ($snapshotArrChunks as $snapshotArrChunk) {

                        //$objRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                        foreach ($snapshotArrChunk as $snapshotRow) {
                            if (strpos($snapshotRow, "[###excel_section_flag###]") === false) {
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
                        $writer->addRowWithStyle($data, $rowStyle);

//                if( strpos($snapshot, '[Form Section]') !== false ) {
//                    $ews->getStyle($aRow)->getFont()->setItalic(true);
//                }

                        if (++$i < $numItems) {
                            $row = $row + 1;
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
                        $writer->addRowWithStyle($data, $rowStyle);
                    }
                } else {
                    $data = array();
                    $data[0] = $formnodesCacheStr;

                    //Entry in XML
                    $data[1] = $formnodesCache;

                    $writer->addRowWithStyle($data, $rowStyle);
                }
            }
            //////// EOF subsection with message snapshot info ////////

            //increment row index
            $row = $row + 1;

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
}
