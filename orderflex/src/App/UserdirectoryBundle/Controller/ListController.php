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

namespace App\UserdirectoryBundle\Controller;



use App\UserdirectoryBundle\Entity\Document; //process.py script: replaced namespace by ::class: added use line for classname=Document


use App\UserdirectoryBundle\Entity\PlatformListManagerRootList; //process.py script: replaced namespace by ::class: added use line for classname=PlatformListManagerRootList
use App\OrderformBundle\Controller\ScanListController;
use App\TranslationalResearchBundle\Entity\VisualInfo;
use App\UserdirectoryBundle\Entity\CompositeNodeInterface;
use App\UserdirectoryBundle\Entity\Permission;
use App\UserdirectoryBundle\Entity\UsernameType;
use App\UserdirectoryBundle\Form\ListFilterType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use App\UserdirectoryBundle\Form\GenericListType;
//use App\UserdirectoryBundle\Util\ErrorHelperUser as ErrorHelper;
/**
 * Common list controller
 */
#[Route(path: '/admin')]
class ListController extends OrderAbstractController
{

    protected $sitename = "employees";
    protected $postPath = null;

    //Method({"GET","POST"}) Method("GET") //TODO: why method GET does not work for handleRequest https://symfony.com/doc/current/form/action_method.html
    /**
     * Lists all entities.
     *
     * //Platform List Manager Root List
     *
     *
     *
     *
     *
     *
     */
    #[Route(path: '/list-manager/', name: 'platformlistmanager-list', methods: ['GET'])]
    #[Route(path: '/list/source-systems/', name: 'sourcesystems-list', methods: ['GET'])]
    #[Route(path: '/list/roles/', name: 'role-list', methods: ['GET'])]
    #[Route(path: '/list/institutions/', name: 'institutions-list', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/states/', name: 'states-list', methods: ['GET'])]
    #[Route(path: '/list/countries/', name: 'countries-list', methods: ['GET'])]
    #[Route(path: '/list/board-certifications/', name: 'boardcertifications-list', methods: ['GET'])]
    #[Route(path: '/list/employment-termination-reasons/', name: 'employmentterminations-list', methods: ['GET'])]
    #[Route(path: '/list/event-log-event-types/', name: 'loggereventtypes-list', methods: ['GET'])]
    #[Route(path: '/list/primary-public-user-id-types/', name: 'usernametypes-list', methods: ['GET'])]
    #[Route(path: '/list/identifier-types/', name: 'identifiers-list', methods: ['GET'])]
    #[Route(path: '/list/residency-tracks/', name: 'residencytracks-list', methods: ['GET'])]
    #[Route(path: '/list/fellowship-types/', name: 'fellowshiptypes-list', methods: ['GET'])]
    #[Route(path: '/list/location-types/', name: 'locationtypes-list', methods: ['GET'])]
    #[Route(path: '/list/equipment/', name: 'equipments-list', methods: ['GET'])]
    #[Route(path: '/list/equipment-types/', name: 'equipmenttypes-list', methods: ['GET'])]
    #[Route(path: '/list/location-privacy-types/', name: 'locationprivacy-list', methods: ['GET'])]
    #[Route(path: '/list/role-attributes/', name: 'roleattributes-list', methods: ['GET'])]
    #[Route(path: '/list/buidlings/', name: 'buildings-list', methods: ['GET'])]
    #[Route(path: '/list/rooms/', name: 'rooms-list', methods: ['GET'])]
    #[Route(path: '/list/suites/', name: 'suites-list', methods: ['GET'])]
    #[Route(path: '/list/floors/', name: 'floors-list', methods: ['GET'])]
    #[Route(path: '/list/grants/', name: 'grants-list', methods: ['GET'])]
    #[Route(path: '/list/mailboxes/', name: 'mailboxes-list', methods: ['GET'])]
    #[Route(path: '/list/percent-effort/', name: 'efforts-list', methods: ['GET'])]
    #[Route(path: '/list/administrative-titles/', name: 'admintitles-list', methods: ['GET'])]
    #[Route(path: '/list/academic-appointment-titles/', name: 'apptitles-list', methods: ['GET'])]
    #[Route(path: '/list/training-completion-reasons/', name: 'completionreasons-list', methods: ['GET'])]
    #[Route(path: '/list/training-degrees/', name: 'trainingdegrees-list', methods: ['GET'])]
    #[Route(path: '/list/training-majors/', name: 'trainingmajors-list', methods: ['GET'])]
    #[Route(path: '/list/training-minors/', name: 'trainingminors-list', methods: ['GET'])]
    #[Route(path: '/list/training-honors/', name: 'traininghonors-list', methods: ['GET'])]
    #[Route(path: '/list/fellowship-titles/', name: 'fellowshiptitles-list', methods: ['GET'])]
    #[Route(path: '/list/residency-specialties/', name: 'residencyspecialtys-list', methods: ['GET'])]
    #[Route(path: '/list/fellowship-subspecialties/', name: 'fellowshipsubspecialtys-list', methods: ['GET'])]
    #[Route(path: '/list/institution-types/', name: 'institutiontypes-list', methods: ['GET'])]
    #[Route(path: '/list/document-types/', name: 'documenttypes-list', methods: ['GET'])]
    #[Route(path: '/list/medical-titles/', name: 'medicaltitles-list', methods: ['GET'])]
    #[Route(path: '/list/medical-specialties/', name: 'medicalspecialties-list', methods: ['GET'])]
    #[Route(path: '/list/employment-types/', name: 'employmenttypes-list', methods: ['GET'])]
    #[Route(path: '/list/grant-source-organizations/', name: 'sourceorganizations-list', methods: ['GET'])]
    #[Route(path: '/list/languages/', name: 'languages-list', methods: ['GET'])]
    #[Route(path: '/list/locales/', name: 'locales-list', methods: ['GET'])]
    #[Route(path: '/list/ranks-of-importance/', name: 'importances-list', methods: ['GET'])]
    #[Route(path: '/list/authorship-roles/', name: 'authorshiproles-list', methods: ['GET'])]
    #[Route(path: '/list/lecture-venues/', name: 'organizations-list', methods: ['GET'])]
    #[Route(path: '/list/cities/', name: 'cities-list', methods: ['GET'])]
    #[Route(path: '/list/link-types/', name: 'linktypes-list', methods: ['GET'])]
    #[Route(path: '/list/sexes/', name: 'sexes-list', methods: ['GET'])]
    #[Route(path: '/list/position-types/', name: 'positiontypes-list', methods: ['GET'])]
    #[Route(path: '/list/organizational-group-types/', name: 'organizationalgrouptypes-list', methods: ['GET'])]
    #[Route(path: '/list/profile-comment-group-types/', name: 'commentgrouptypes-list', methods: ['GET'])]
    #[Route(path: '/list/comment-types/', name: 'commenttypes-list', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/user-wrappers/', name: 'userwrappers-list', methods: ['GET'])]
    #[Route(path: '/list/spot-purposes/', name: 'spotpurposes-list', methods: ['GET'])]
    #[Route(path: '/list/medical-license-statuses/', name: 'medicalstatuses-list', methods: ['GET'])]
    #[Route(path: '/list/certifying-board-organizations/', name: 'certifyingboardorganizations-list', methods: ['GET'])]
    #[Route(path: '/list/training-types/', name: 'trainingtypes-list', methods: ['GET'])]
    #[Route(path: '/list/job-titles/', name: 'joblists-list', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-statuses/', name: 'fellappstatuses-list', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-ranks/', name: 'fellappranks-list', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-language-proficiencies/', name: 'fellapplanguageproficiency-list', methods: ['GET'])]
    #[Route(path: '/list/collaboration-types/', name: 'collaborationtypes-list', methods: ['GET'])]
    #[Route(path: '/list/permissions/', name: 'permission-list', methods: ['GET'])]
    #[Route(path: '/list/permission-objects/', name: 'permissionobject-list', methods: ['GET'])]
    #[Route(path: '/list/permission-actions/', name: 'permissionaction-list', methods: ['GET'])]
    #[Route(path: '/list/sites/', name: 'sites-list', methods: ['GET'])]
    #[Route(path: '/list/event-object-types/', name: 'eventobjecttypes-list', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-types/', name: 'vacreqrequesttypes-list', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-floating-texts/', name: 'vacreqfloatingtexts-list', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-floating-types/', name: 'vacreqfloatingtypes-list', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-approval-types/', name: 'vacreqapprovaltypes-list', methods: ['GET'])]
    #[Route(path: '/list/healthcare-provider-specialties/', name: 'healthcareproviderspecialty-list', methods: ['GET'])]
    #[Route(path: '/list/healthcare-provider-initial-communications/', name: 'healthcareprovidercommunication-list', methods: ['GET'])]
    #[Route(path: '/list/object-types/', name: 'objecttypes-list', methods: ['GET'])]
    #[Route(path: '/list/form-nodes/', name: 'formnodes-list', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/object-type-texts/', name: 'objecttypetexts-list', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/blood-product-transfusions/', name: 'bloodproducttransfusions-list', methods: ['GET'])]
    #[Route(path: '/list/transfusion-reaction-types/', name: 'transfusionreactiontypes-list', methods: ['GET'])]
    #[Route(path: '/list/object-type-strings/', name: 'objecttypestrings-list', methods: ['GET'])]
    #[Route(path: '/list/object-type-dropdowns/', name: 'objecttypedropdowns-list', methods: ['GET'])]
    #[Route(path: '/list/blood-types/', name: 'bloodtypes-list', methods: ['GET'])]
    #[Route(path: '/list/additional-communications/', name: 'additionalcommunications-list', methods: ['GET'])]
    #[Route(path: '/list/transfusion-antibody-screen-results/', name: 'transfusionantibodyscreenresults-list', methods: ['GET'])]
    #[Route(path: '/list/transfusion-crossmatch-results/', name: 'transfusioncrossmatchresults-list', methods: ['GET'])]
    #[Route(path: '/list/transfusion-dat-results/', name: 'transfusiondatresults-list', methods: ['GET'])]
    #[Route(path: '/list/transfusion-hemolysis-check-results/', name: 'transfusionhemolysischeckresults-list', methods: ['GET'])]
    #[Route(path: '/list/object-type-datetimes/', name: 'objecttypedatetimes-list', methods: ['GET'])]
    #[Route(path: '/list/complex-platelet-summary-antibodies/', name: 'complexplateletsummaryantibodies-list', methods: ['GET'])]
    #[Route(path: '/list/cci-unit-platelet-count-default-values/', name: 'cciunitplateletcountdefaultvalues-list', methods: ['GET'])]
    #[Route(path: '/list/cci-platelet-type-transfused/', name: 'cciplatelettypetransfuseds-list', methods: ['GET'])]
    #[Route(path: '/list/platelet-transfusion-product-receiving/', name: 'platelettransfusionproductreceivings-list', methods: ['GET'])]
    #[Route(path: '/list/transfusion-product-status/', name: 'transfusionproductstatus-list', methods: ['GET'])]
    #[Route(path: '/list/week-days/', name: 'weekdays-list', methods: ['GET'])]
    #[Route(path: '/list/months/', name: 'months-list', methods: ['GET'])]
    #[Route(path: '/list/clerical-errors/', name: 'clericalerrors-list', methods: ['GET'])]
    #[Route(path: '/list/lab-result-names/', name: 'labresultnames-list', methods: ['GET'])]
    #[Route(path: '/list/lab-result-units-measures/', name: 'labresultunitsmeasures-list', methods: ['GET'])]
    #[Route(path: '/list/lab-result-flags/', name: 'labresultflags-list', methods: ['GET'])]
    #[Route(path: '/list/pathology-result-signatories/', name: 'pathologyresultsignatories-list', methods: ['GET'])]
    #[Route(path: '/list/object-type-checkboxes/', name: 'objecttypecheckboxs-list', methods: ['GET'])]
    #[Route(path: '/list/object-type-radio-buttons/', name: 'objecttyperadiobuttons-list', methods: ['GET'])]
    #[Route(path: '/list/life-forms/', name: 'lifeforms-list', methods: ['GET'])]
    #[Route(path: '/list/position-track-types/', name: 'positiontracktypes-list', methods: ['GET'])]
    #[Route(path: '/list/translational-research-project-specialties-orig/', name: 'transresprojectspecialties-list-orig', methods: ['GET'])]
    #[Route(path: '/list/translational-research-project-types/', name: 'transresprojecttypes-list', methods: ['GET'])]
    #[Route(path: '/list/translational-research-request-category-types/', name: 'transresrequestcategorytypes-list', methods: ['GET'])]
    #[Route(path: '/list/translational-irb-approval-types/', name: 'transresirbapprovaltypes-list', methods: ['GET'])]
    #[Route(path: '/list/translational-business-purposes/', name: 'transresbusinesspurposes-list', methods: ['GET'])]
    #[Route(path: '/list/translational-work-queue-types/', name: 'workqueuetypes-list', methods: ['GET'])]
    #[Route(path: '/list/translational-orderable-status/', name: 'orderablestatus-list', methods: ['GET'])]
    #[Route(path: '/list/antibodies/', name: 'antibodies-list', methods: ['GET'])]
    #[Route(path: '/list/custom000/', name: 'custom000-list', methods: ['GET'])]
    #[Route(path: '/list/custom001/', name: 'custom001-list', methods: ['GET'])]
    #[Route(path: '/list/custom002/', name: 'custom002-list', methods: ['GET'])]
    #[Route(path: '/list/custom003/', name: 'custom003-list', methods: ['GET'])]
    #[Route(path: '/list/custom004/', name: 'custom004-list', methods: ['GET'])]
    #[Route(path: '/list/custom005/', name: 'custom005-list', methods: ['GET'])]
    #[Route(path: '/list/custom006/', name: 'custom006-list', methods: ['GET'])]
    #[Route(path: '/list/custom007/', name: 'custom007-list', methods: ['GET'])]
    #[Route(path: '/list/custom008/', name: 'custom008-list', methods: ['GET'])]
    #[Route(path: '/list/custom009/', name: 'custom009-list', methods: ['GET'])]
    #[Route(path: '/list/custom010/', name: 'custom010-list', methods: ['GET'])]
    #[Route(path: '/list/custom011/', name: 'custom011-list', methods: ['GET'])]
    #[Route(path: '/list/custom012/', name: 'custom012-list', methods: ['GET'])]
    #[Route(path: '/list/custom013/', name: 'custom013-list', methods: ['GET'])]
    #[Route(path: '/list/custom014/', name: 'custom014-list', methods: ['GET'])]
    #[Route(path: '/list/custom015/', name: 'custom015-list', methods: ['GET'])]
    #[Route(path: '/list/custom016/', name: 'custom016-list', methods: ['GET'])]
    #[Route(path: '/list/custom017/', name: 'custom017-list', methods: ['GET'])]
    #[Route(path: '/list/custom018/', name: 'custom018-list', methods: ['GET'])]
    #[Route(path: '/list/custom019/', name: 'custom019-list', methods: ['GET'])]
    #[Route(path: '/list/custom020/', name: 'custom020-list', methods: ['GET'])]
    #[Route(path: '/list/custom021/', name: 'custom021-list', methods: ['GET'])]
    #[Route(path: '/list/custom022/', name: 'custom022-list', methods: ['GET'])]
    #[Route(path: '/list/custom023/', name: 'custom023-list', methods: ['GET'])]
    #[Route(path: '/list/custom024/', name: 'custom024-list', methods: ['GET'])]
    #[Route(path: '/list/custom025/', name: 'custom025-list', methods: ['GET'])]
    #[Route(path: '/list/custom026/', name: 'custom026-list', methods: ['GET'])]
    #[Route(path: '/list/custom027/', name: 'custom027-list', methods: ['GET'])]
    #[Route(path: '/list/custom028/', name: 'custom028-list', methods: ['GET'])]
    #[Route(path: '/list/custom029/', name: 'custom029-list', methods: ['GET'])]
    #[Route(path: '/list/custom030/', name: 'custom030-list', methods: ['GET'])]
    #[Route(path: '/list/custom031/', name: 'custom031-list', methods: ['GET'])]
    #[Route(path: '/list/custom032/', name: 'custom032-list', methods: ['GET'])]
    #[Route(path: '/list/custom033/', name: 'custom033-list', methods: ['GET'])]
    #[Route(path: '/list/custom034/', name: 'custom034-list', methods: ['GET'])]
    #[Route(path: '/list/custom035/', name: 'custom035-list', methods: ['GET'])]
    #[Route(path: '/list/custom036/', name: 'custom036-list', methods: ['GET'])]
    #[Route(path: '/list/custom037/', name: 'custom037-list', methods: ['GET'])]
    #[Route(path: '/list/custom038/', name: 'custom038-list', methods: ['GET'])]
    #[Route(path: '/list/custom039/', name: 'custom039-list', methods: ['GET'])]
    #[Route(path: '/list/custom040/', name: 'custom040-list', methods: ['GET'])]
    #[Route(path: '/list/custom041/', name: 'custom041-list', methods: ['GET'])]
    #[Route(path: '/list/custom042/', name: 'custom042-list', methods: ['GET'])]
    #[Route(path: '/list/custom043/', name: 'custom043-list', methods: ['GET'])]
    #[Route(path: '/list/custom044/', name: 'custom044-list', methods: ['GET'])]
    #[Route(path: '/list/custom045/', name: 'custom045-list', methods: ['GET'])]
    #[Route(path: '/list/custom046/', name: 'custom046-list', methods: ['GET'])]
    #[Route(path: '/list/custom047/', name: 'custom047-list', methods: ['GET'])]
    #[Route(path: '/list/custom048/', name: 'custom048-list', methods: ['GET'])]
    #[Route(path: '/list/custom049/', name: 'custom049-list', methods: ['GET'])]
    #[Route(path: '/list/custom050/', name: 'custom050-list', methods: ['GET'])]
    #[Route(path: '/list/custom051/', name: 'custom051-list', methods: ['GET'])]
    #[Route(path: '/list/custom052/', name: 'custom052-list', methods: ['GET'])]
    #[Route(path: '/list/custom053/', name: 'custom053-list', methods: ['GET'])]
    #[Route(path: '/list/custom054/', name: 'custom054-list', methods: ['GET'])]
    #[Route(path: '/list/custom055/', name: 'custom055-list', methods: ['GET'])]
    #[Route(path: '/list/custom056/', name: 'custom056-list', methods: ['GET'])]
    #[Route(path: '/list/custom057/', name: 'custom057-list', methods: ['GET'])]
    #[Route(path: '/list/custom058/', name: 'custom058-list', methods: ['GET'])]
    #[Route(path: '/list/custom059/', name: 'custom059-list', methods: ['GET'])]
    #[Route(path: '/list/custom060/', name: 'custom060-list', methods: ['GET'])]
    #[Route(path: '/list/custom061/', name: 'custom061-list', methods: ['GET'])]
    #[Route(path: '/list/custom062/', name: 'custom062-list', methods: ['GET'])]
    #[Route(path: '/list/custom063/', name: 'custom063-list', methods: ['GET'])]
    #[Route(path: '/list/custom064/', name: 'custom064-list', methods: ['GET'])]
    #[Route(path: '/list/custom065/', name: 'custom065-list', methods: ['GET'])]
    #[Route(path: '/list/custom066/', name: 'custom066-list', methods: ['GET'])]
    #[Route(path: '/list/custom067/', name: 'custom067-list', methods: ['GET'])]
    #[Route(path: '/list/custom068/', name: 'custom068-list', methods: ['GET'])]
    #[Route(path: '/list/custom069/', name: 'custom069-list', methods: ['GET'])]
    #[Route(path: '/list/custom070/', name: 'custom070-list', methods: ['GET'])]
    #[Route(path: '/list/custom071/', name: 'custom071-list', methods: ['GET'])]
    #[Route(path: '/list/custom072/', name: 'custom072-list', methods: ['GET'])]
    #[Route(path: '/list/custom073/', name: 'custom073-list', methods: ['GET'])]
    #[Route(path: '/list/custom074/', name: 'custom074-list', methods: ['GET'])]
    #[Route(path: '/list/custom075/', name: 'custom075-list', methods: ['GET'])]
    #[Route(path: '/list/custom076/', name: 'custom076-list', methods: ['GET'])]
    #[Route(path: '/list/custom077/', name: 'custom077-list', methods: ['GET'])]
    #[Route(path: '/list/custom078/', name: 'custom078-list', methods: ['GET'])]
    #[Route(path: '/list/custom079/', name: 'custom079-list', methods: ['GET'])]
    #[Route(path: '/list/custom080/', name: 'custom080-list', methods: ['GET'])]
    #[Route(path: '/list/custom081/', name: 'custom081-list', methods: ['GET'])]
    #[Route(path: '/list/custom082/', name: 'custom082-list', methods: ['GET'])]
    #[Route(path: '/list/custom083/', name: 'custom083-list', methods: ['GET'])]
    #[Route(path: '/list/custom084/', name: 'custom084-list', methods: ['GET'])]
    #[Route(path: '/list/custom085/', name: 'custom085-list', methods: ['GET'])]
    #[Route(path: '/list/custom086/', name: 'custom086-list', methods: ['GET'])]
    #[Route(path: '/list/custom087/', name: 'custom087-list', methods: ['GET'])]
    #[Route(path: '/list/custom088/', name: 'custom088-list', methods: ['GET'])]
    #[Route(path: '/list/custom089/', name: 'custom089-list', methods: ['GET'])]
    #[Route(path: '/list/custom090/', name: 'custom090-list', methods: ['GET'])]
    #[Route(path: '/list/custom091/', name: 'custom091-list', methods: ['GET'])]
    #[Route(path: '/list/custom092/', name: 'custom092-list', methods: ['GET'])]
    #[Route(path: '/list/custom093/', name: 'custom093-list', methods: ['GET'])]
    #[Route(path: '/list/custom094/', name: 'custom094-list', methods: ['GET'])]
    #[Route(path: '/list/custom095/', name: 'custom095-list', methods: ['GET'])]
    #[Route(path: '/list/custom096/', name: 'custom096-list', methods: ['GET'])]
    #[Route(path: '/list/custom097/', name: 'custom097-list', methods: ['GET'])]
    #[Route(path: '/list/custom098/', name: 'custom098-list', methods: ['GET'])]
    #[Route(path: '/list/custom099/', name: 'custom099-list', methods: ['GET'])]
    #[Route(path: '/list/translational-tissue-processing-services/', name: 'transrestissueprocessingservices-list', methods: ['GET'])]
    #[Route(path: '/list/translational-other-requested-services/', name: 'transresotherrequestedservices-list', methods: ['GET'])]
    #[Route(path: '/list/translational-collaboration-labs/', name: 'transrescolllabs-list', methods: ['GET'])]
    #[Route(path: '/list/translational-collaboration-divs/', name: 'transrescolldivs-list', methods: ['GET'])]
    #[Route(path: '/list/translational-irb-approval-status/', name: 'transresirbstatus-list', methods: ['GET'])]
    #[Route(path: '/list/translational-requester-group/', name: 'transresrequestergroup-list', methods: ['GET'])]
    #[Route(path: '/list/transrescomptypes/', name: 'transrescomptypes-list', methods: ['GET'])]
    #[Route(path: '/list/visa-status/', name: 'visastatus-list', methods: ['GET'])]
    #[Route(path: '/list/resappstatuses/', name: 'resappstatuses-list', methods: ['GET'])]
    #[Route(path: '/list/resappranks/', name: 'resappranks-list', methods: ['GET'])]
    #[Route(path: '/list/resapplanguageproficiency/', name: 'resapplanguageproficiency-list', methods: ['GET'])]
    #[Route(path: '/list/resappfitforprogram/', name: 'resappfitforprogram-list', methods: ['GET'])]
    #[Route(path: '/list/resappvisastatus/', name: 'resappvisastatus-list', methods: ['GET'])]
    #[Route(path: '/list/postsoph/', name: 'postsoph-list', methods: ['GET'])]
    #[Route(path: '/list/resappapplyingresidencytrack/', name: 'resappapplyingresidencytrack-list', methods: ['GET'])]
    #[Route(path: '/list/resapplearnarealist/', name: 'resapplearnarealist-list', methods: ['GET'])]
    #[Route(path: '/list/resappspecificindividuallist/', name: 'resappspecificindividuallist-list', methods: ['GET'])]
    #[Route(path: '/list/viewmodes/', name: 'viewmodes-list', methods: ['GET'])]
    #[Route(path: '/list/transrespricetypes/', name: 'transrespricetypes-list', methods: ['GET'])]
    #[Route(path: '/list/charttypes/', name: 'charttypes-list', methods: ['GET'])]
    #[Route(path: '/list/charttopics/', name: 'charttopics-list', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/chartfilters/', name: 'chartfilters-list', methods: ['GET'])]
    #[Route(path: '/list/charts/', name: 'charts-list', methods: ['GET'])]
    #[Route(path: '/list/chartdatasources/', name: 'chartdatasources-list', methods: ['GET'])]
    #[Route(path: '/list/chartupdatefrequencies/', name: 'chartupdatefrequencies-list', methods: ['GET'])]
    #[Route(path: '/list/chartvisualizations/', name: 'chartvisualizations-list', methods: ['GET'])]
    #[Route(path: '/list/vacreqholidays/', name: 'vacreqholidays-list', methods: ['GET'])]
    #[Route(path: '/list/vacreqobservedholidays/', name: 'vacreqobservedholidays-list', methods: ['GET'])]
    #[Route(path: '/list/authusergroup/', name: 'authusergroup-list', methods: ['GET'])]
    #[Route(path: '/list/authservernetwork/', name: 'authservernetwork-list', methods: ['GET'])]
    #[Route(path: '/list/authpartnerserver/', name: 'authpartnerserver-list', methods: ['GET'])]
    #[Route(path: '/list/tenanturls/', name: 'tenanturls-list', methods: ['GET'])]
    #[Route(path: '/list/antibodycategorytag/', name: 'antibodycategorytag-list', methods: ['GET'])]
    #[Route(path: '/list/transferstatus/', name: 'transferstatus-list', methods: ['GET'])]
    #[Route(path: '/list/interfacetransfers/', name: 'interfacetransfers-list', methods: ['GET'])]
    #[Route(path: '/list/antibodylabs/', name: 'antibodylabs-list', methods: ['GET'])]
    #[Route(path: '/list/antibodypanels/', name: 'antibodypanels-list', methods: ['GET'])]
    #[Route(path: '/list/samlconfig/', name: 'samlconfig-list', methods: ['GET'])]
    #[Route(path: '/list/globalfellowshipspecialty/', name: 'globalfellowshipspecialty-list', methods: ['GET'])]
    #[Route(path: '/list/trainingeligibility/', name: 'trainingeligibility-list', methods: ['GET'])]
    #[Route(path: '/list/dutiescapability/', name: 'dutiescapability-list', methods: ['GET'])]
    #[Route(path: '/list/phdfield/', name: 'phdfield-list', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/ListForm/index.html.twig')]
    public function indexAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        return $this->getList($request);
    }
    public function getList($request, $limit=50) {

        $routeName = $request->get('_route');

        //get object name: stain-list => stain
        $pieces = explode("-", $routeName);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";

        $mapper = $this->classListMapper($pathbase,$request);
        //echo "bundleName=".$mapper['bundleName']."<br>";
        //echo "className=".$mapper['className']."<br>";

        //$repository = $this->getDoctrine()->getRepository($mapper['bundleName'].':'.$mapper['className']);
        $repository = $this->getDoctrine()->getRepository($mapper['fullClassName']);

        $dql =  $repository->createQueryBuilder("ent");
        $dql->select('ent');
        $dql->groupBy('ent');

        $dql->leftJoin("ent.creator", "creator");
        $dql->leftJoin("ent.updatedby", "updatedby");

        $dql->addGroupBy('creator.username');
        $dql->addGroupBy('updatedby.username');

        $entityClass = $mapper['fullClassName'];   //"App\\OrderformBundle\\Entity\\".$mapper['className'];
        $className = $mapper['className'];

        //synonyms and original
        $dql->leftJoin("ent.synonyms", "synonyms");
        $dql->addGroupBy('synonyms.name');
        $dql->leftJoin("ent.original", "original");
        $dql->addGroupBy('original.name');

        $useWalker = false;
        //$useWalker = true;

        //$dql->leftJoin("ent.objectType", "objectType");
        if( method_exists($entityClass,'getObjectType') ) {
            $dql->leftJoin("ent.objectType", "objectType");
            $dql->addGroupBy('objectType.name');
        }

//        if( method_exists($entityClass,'getResearchlab') ) {
//            $dql->leftJoin("ent.researchlab", "researchlab");
//            $dql->leftJoin("researchlab.user", "user");
//            $dql->addSelect('COUNT(user) AS HIDDEN usercount');
//        }

        if( method_exists($entityClass,'getParent') ) {
            $dql->leftJoin("ent.parent", "parent");
            $dql->addGroupBy('parent.name');
        }

        if( method_exists($entityClass,'getOrganizationalGroupType') ) {
            $dql->leftJoin("ent.organizationalGroupType", "organizationalGroupType");
            $dql->addGroupBy('organizationalGroupType.name');
        }

        if( method_exists($entityClass,'getRoles') ) {
            $dql->leftJoin("ent.roles", "roles");
            $dql->addGroupBy('roles.name');
        }

        if( method_exists($entityClass,'getAttributes') ) {
            $dql->leftJoin("ent.attributes", "attributes");
            $dql->addGroupBy('attributes');
        }

        if( method_exists($entityClass,'getPermissionObjectList') ) {
            $dql->leftJoin("ent.permissionObjectList", "permissionObjectList");
            $dql->addGroupBy('permissionObjectList');
        }
        if( method_exists($entityClass,'getPermissionActionList') ) {
            $dql->leftJoin("ent.permissionActionList", "permissionActionList");
            $dql->addGroupBy('permissionActionList');
        }

        if( method_exists($entityClass,'getInstitution') ) {
            $dql->leftJoin("ent.institution", "institution");
            $dql->addGroupBy('institution');
        }

        if( method_exists($entityClass,'getInstitutions') ) {
            $dql->leftJoin("ent.institutions", "institutions");
            $dql->addGroupBy('institutions');
            $useWalker = true;
        }

        if( method_exists($entityClass,'getCollaborationType') ) {
            $dql->leftJoin("ent.collaborationType", "collaborationType");
            $dql->addGroupBy('collaborationType');
        }

        if( method_exists($entityClass,'getSites') ) {
            $dql->leftJoin("ent.sites", "sites");
            $dql->addGroupBy('sites.name');
            $useWalker = true;
        }

        if( method_exists($entityClass,'getFellowshipSubspecialty') ) {
            $dql->leftJoin("ent.fellowshipSubspecialty", "fellowshipSubspecialty");
            $dql->addGroupBy('fellowshipSubspecialty.name');
        }

        if( method_exists($entityClass,'getWorkQueues') ) {
            //echo "getWorkQueues <br>";
            $dql->leftJoin("ent.workQueues", "workQueues");
            $dql->addGroupBy('workQueues');
            $useWalker = true;
        }

        if( 0 && method_exists($entityClass,'getProjectSpecialties') ) {
            //exit('123');
            $useWalker = true;
            $dql->leftJoin("ent.projectSpecialties", "projectSpecialties");
            //$dql->addGroupBy('ent.projectSpecialties');
            //$dql->addGroupBy('projectSpecialties');
            $dql->addGroupBy('projectSpecialties.name'); //This causes 201 matching items in RequestCategoryTypeList, however it has only 67 items
            //$dql->addGroupBy('projectSpecialties.id');
        }

//        if( method_exists($entityClass,'getPatients') ) {
//            $dql->leftJoin("ent.patients", "patients");
//            //$dql->addGroupBy('patients.name');
//        }

        //$dql->orderBy("ent.createdate","DESC");
		
		//Pass sorting parameters directly to query; Somehow, knp_paginator does not sort correctly according to sorting parameters
        $postData = $request->query->all();
        if (isset($postData['sort'])) {
            //$dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
            //$dql->orderBy("ent.createdate","DESC");
            $dql->orderBy($postData['sort'], $postData['direction']);
        } else {
            //$dql = $dql . " ORDER BY ent.orderinlist ASC";
            $dql->orderBy("ent.orderinlist", "ASC");
        }

        $dqlParameters = array();

        $params = array("className" => $mapper['className']);
        $filterform = $this->createForm(ListFilterType::class, null, array(
            //'action' => $this->generateUrl($routeName),
            'form_custom_value'=>$params,
            'method' => 'GET',
        ));
        //$filterform->submit($request);
        $filterform->handleRequest($request);
        $search = $filterform['search']->getData();
        //echo "search=".$search."<br>";
        //$search = $request->request->get('filter')['search'];
        //$search = $request->query->get('search');
        //echo "2search=".$search."<br>";

        $filterTypes = null;
        if( isset($filterform['type']) ) {
            $filterTypes = $filterform['type']->getData();
        }

        if( $search ) {
            $searchStr = "";

            if( is_numeric($search) ) {
                //echo "int <br>";
                $searchInt = intval($search);
                $searchStr = "ent.id = :searchInt OR";
                $dqlParameters['searchInt'] = $searchInt;
            }

            $searchStr = $searchStr."
                LOWER(ent.name) LIKE LOWER(:search) 
                OR LOWER(ent.abbreviation) LIKE LOWER(:search) 
                OR LOWER(ent.shortname) LIKE LOWER(:search) 
                OR LOWER(ent.description) LIKE LOWER(:search)
                OR LOWER(ent.entityName) LIKE LOWER(:search)
                ";

//            //search location: phone, building, room
//            if( method_exists($entityClass,'getPhone') ) {
//                $searchStr = $searchStr . " OR ent.phone LIKE :search";
//            }
//            if( method_exists($entityClass,'getBuilding') ) {
//                $dql->leftJoin("ent.building", "building");
//                $searchStr = $searchStr . " OR building.name LIKE :search";
//            }
//            if( method_exists($entityClass,'getRoom') ) {
//                $dql->leftJoin("ent.room", "room");
//                $searchStr = $searchStr . " OR room.name LIKE :search";
//            }

            if (method_exists($entityClass, 'listName')) {
                $searchStr = $searchStr . " OR LOWER(ent.listName) LIKE LOWER(:search)";
            }

            if (method_exists($entityClass, 'getSection')) {
                $searchStr = $searchStr . " OR LOWER(ent.section) LIKE LOWER(:search)";
            }

            if (method_exists($entityClass, 'getProductId')) {
                $searchStr = $searchStr . " OR LOWER(ent.productId) LIKE LOWER(:search)";
            }

            if (method_exists($entityClass, 'getFeeUnit')) {
                $searchStr = $searchStr . " OR LOWER(ent.feeUnit) LIKE LOWER(:search)";
            }

            if (method_exists($entityClass, 'getFee')) {
                $searchStr = $searchStr . " OR LOWER(ent.fee) LIKE LOWER(:search)";
            }

            //AntibodyList
            //if( method_exists($entityClass, 'getDatasheet') ) {
            if( $className == 'AntibodyList' ) {
                $dql->leftJoin("ent.categoryTags", "categoryTags");
                $dql->addGroupBy('categoryTags');
                
                $searchStr = $searchStr . " OR LOWER(ent.category) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(categoryTags.name) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.altname) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.company) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.catalog) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.lot) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.igconcentration) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.clone) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.host) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.reactivity) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.control) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.protocol) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.retrieval) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.dilution) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.storage) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.comment) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.comment1) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.comment2) LIKE LOWER(:search)";
                $searchStr = $searchStr . " OR LOWER(ent.datasheet) LIKE LOWER(:search)";
            }

            $dql->andWhere($searchStr);
            $dqlParameters['search'] = '%'.$search.'%';
        }

        if( $filterTypes && count($filterTypes) > 0 ) {
            $dql->andWhere("ent.type IN (:filterTypes)");
            $dqlParameters['filterTypes'] = $filterTypes;
        }

        //echo "dql=".$dql."<br>";

        //$em = $this->getDoctrine()->getManager();
        $limit = 50;

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters( $dqlParameters );
        }

        //TODO: check why showing 201 matching fees when only 67 is in DB
        if( $useWalker ) {
            $walker = array('wrap-queries'=>true);
        } else {
            $walker = array();
        }

        $paginator = $this->container->get('knp_paginator');
        $entities = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            $limit                          /*limit per page*/
            ,$walker//,array('wrap-queries'=>true)   //this cause sorting impossible, but without it "site" sorting does not work (mssql: "There is no component aliased by [sites] in the given Query" )
            //,array('distinct'=>true)
            //,array('defaultSortFieldName' => 'ent.orderinlist', 'defaultSortDirection' => 'asc')
            //,array('defaultSortFieldName' => 'ent.orderinlist', 'defaultSortDirection' => 'asc', 'wrap-queries'=>true)
        );
        //echo "list count=".count($entities)."<br>";
        //echo "getTotalItemCount=".$entities->getTotalItemCount()."<br>";
        //exit();

        ///////////// check if show "create a new entity" link //////////////
        $createNew = true;
        $reflectionClass = new \ReflectionClass($mapper['fullClassName']);
        $compositeReflection = new \ReflectionClass("App\\UserdirectoryBundle\\Entity\\CompositeNodeInterface");
        if( $reflectionClass->isSubclassOf($compositeReflection) ) {
            $createNew = false;
            //echo "dont show create new link";
        } else {
            //echo "show create new link";
        }
        ///////////// EOF check if show "create a new entity" link //////////////

        //echo "pathbase=".$pathbase."<br>";
        //echo "routeName=".$routeName."<br>";
        //exit('111');

        return array(
            'entities' => $entities,
            'displayName' => $mapper['displayName'],
            'linkToListId' => $mapper['linkToListId'],
            'pathbase' => $pathbase,
            'withCreateNewEntityLink' => $createNew,
            'filterform' => $filterform->createView(),
            'routename' => $routeName,
            'sitename' => $this->sitename,
            'cycle' => 'show'
        );
    }
    public function getList_Test($request, $limit=50) {

        $routeName = $request->get('_route');

        //get object name: stain-list => stain
        $pieces = explode("-", $routeName);
        $pathbase = $pieces[0];

        $mapper = $this->classListMapper($pathbase,$request);
        //echo "bundleName=".$mapper['bundleName']."<br>";
        //echo "className=".$mapper['className']."<br>";

        //$repository = $this->getDoctrine()->getRepository($mapper['bundleName'].':'.$mapper['className']);
        $repository = $this->getDoctrine()->getRepository($mapper['fullClassName']);

        $dql =  $repository->createQueryBuilder("ent");
        $dql->select('ent');
        //$dql->groupBy('ent');

        //$dql->leftJoin("ent.creator", "creator");
        //$dql->leftJoin("ent.updatedby", "updatedby");

        //$dql->addGroupBy('creator.username');
        //$dql->addGroupBy('updatedby.username');

        //$entityClass = $mapper['fullClassName'];   //"App\\OrderformBundle\\Entity\\".$mapper['className'];

        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
		$postData = $request->query->all();
        //dump($postData);
		if( isset($postData['sort']) ) {
            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
            //echo 'sort='.$postData['sort'] . ", direction=" . $postData['direction']."<br>";
        }

        //$dqlParameters = array();

//        $params = array("className" => $mapper['className']);
//        $filterform = $this->createForm(ListFilterType::class, null, array(
//            //'action' => $this->generateUrl($routeName),
//            'form_custom_value'=>$params,
//            'method' => 'GET',
//        ));
//        //$filterform->submit($request);
//        $filterform->handleRequest($request);
//        $search = $filterform['search']->getData();

        //echo "dql=".$dql."<br>";

        $em = $this->getDoctrine()->getManager();
        $limit = 50;

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

//        if( count($dqlParameters) > 0 ) {
//            $query->setParameters( $dqlParameters );
//        }

        $paginator = $this->container->get('knp_paginator');
        $entities = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            $limit                          /*limit per page*/
            //,array('wrap-queries'=>true)   //this cause sorting impossible, but without it "site" sorting does not work (mssql: "There is no component aliased by [sites] in the given Query" )
            //,array('distinct'=>true)
            //,array('defaultSortFieldName' => 'ent.orderinlist', 'defaultSortDirection' => 'asc')
            ,array('defaultSortFieldName' => 'ent.orderinlist', 'defaultSortDirection' => 'asc', 'wrap-queries'=>true)
        );
        //echo "list count=".count($entities)."<br>";
        //exit();

        ///////////// check if show "create a new entity" link //////////////
        $createNew = true;
        $reflectionClass = new \ReflectionClass($mapper['fullClassName']);
        $compositeReflection = new \ReflectionClass("App\\UserdirectoryBundle\\Entity\\CompositeNodeInterface");
        if( $reflectionClass->isSubclassOf($compositeReflection) ) {
            $createNew = false;
            //echo "dont show create new link";
        } else {
            //echo "show create new link";
        }
        ///////////// EOF check if show "create a new entity" link //////////////

        return array(
            'entities' => $entities,
            'displayName' => $mapper['displayName'],
            'linkToListId' => $mapper['linkToListId'],
            'pathbase' => $pathbase,
            'withCreateNewEntityLink' => $createNew,
            //'filterform' => $filterform->createView(),
            'routename' => $routeName,
            'sitename' => $this->sitename,
            'cycle' => 'show'
        );
    }

    #[Route(path: '/download-list-excel', name: 'user_download_list_excel')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function downloadListExcelAction( Request $request ) {
        //$ids = $request->request->get('ids');
        //echo "ids=".$ids."<br>";
        //exit('111');

        $search = $request->get('search');
        $linkToListId = $request->get('linkToListId');
        $pathbase = $request->get('pathbase');
        //echo "linkToListId=$linkToListId, search=$search, pathbase=$pathbase <br>";
        //dump($search);

        //dump($request);
        //exit('111');

        $mapper = $this->classListMapper($pathbase,$request);
        //echo "repository=".$mapper['bundleName'].':'.$mapper['className']."<br>";

        //$repository = $this->getDoctrine()->getRepository($mapper['bundleName'].':'.$mapper['className']);
        $repository = $this->getDoctrine()->getRepository($mapper['fullClassName']);

        $entityClass = $mapper['fullClassName'];

        $userServiceUtil = $this->container->get('user_service_utility');
        
        $fileName = "list_ID_$linkToListId".".xlsx";

        $userServiceUtil->createtListExcelSpout( $repository, $entityClass, $search, $fileName );

        exit();
    }

    /**
     * Creates a new entity.
     *
     *
     *
     *
     *
     *
     *
     *
     */
    #[Route(path: '/list/list-manager/', name: 'platformlistmanager_create', methods: ['POST'])]
    #[Route(path: '/list/source-systems/', name: 'sourcesystems_create', methods: ['POST'])]
    #[Route(path: '/list/roles/', name: 'role_create', methods: ['POST'])]
    #[Route(path: '/list/institutions/', name: 'institutions_create', methods: ['POST'])]
    #[Route(path: '/list/states/', name: 'states_create', methods: ['POST'])]
    #[Route(path: '/list/countries/', name: 'countries_create', methods: ['POST'])]
    #[Route(path: '/list/board-certifications/', name: 'boardcertifications_create', methods: ['POST'])]
    #[Route(path: '/list/employment-termination-reasons/', name: 'employmentterminations_create', methods: ['POST'])]
    #[Route(path: '/list/event-log-event-types/', name: 'loggereventtypes_create', methods: ['POST'])]
    #[Route(path: '/list/primary-public-user-id-types/', name: 'usernametypes_create', methods: ['POST'])]
    #[Route(path: '/list/identifier-types/', name: 'identifiers_create', methods: ['POST'])]
    #[Route(path: '/list/residency-tracks/', name: 'residencytracks_create', methods: ['POST'])]
    #[Route(path: '/list/fellowship-types/', name: 'fellowshiptypes_create', methods: ['POST'])]
    #[Route(path: '/list/location-types/', name: 'locationtypes_create', methods: ['POST'])]
    #[Route(path: '/list/equipment/', name: 'equipments_create', methods: ['POST'])]
    #[Route(path: '/list/equipment-types/', name: 'equipmenttypes_create', methods: ['POST'])]
    #[Route(path: '/list/location-privacy-types/', name: 'locationprivacy_create', methods: ['POST'])]
    #[Route(path: '/list/role-attributes/', name: 'roleattributes_create', methods: ['POST'])]
    #[Route(path: '/list/buidlings/', name: 'buildings_create', methods: ['POST'])]
    #[Route(path: '/list/rooms/', name: 'rooms_create', methods: ['POST'])]
    #[Route(path: '/list/suites/', name: 'suites_create', methods: ['POST'])]
    #[Route(path: '/list/floors/', name: 'floors_create', methods: ['POST'])]
    #[Route(path: '/list/grants/', name: 'grants_create', methods: ['POST'])]
    #[Route(path: '/list/mailboxes/', name: 'mailboxes_create', methods: ['POST'])]
    #[Route(path: '/list/percent-effort/', name: 'efforts_create', methods: ['POST'])]
    #[Route(path: '/list/administrative-titles/', name: 'admintitles_create', methods: ['POST'])]
    #[Route(path: '/list/academic-appointment-titles/', name: 'apptitles_create', methods: ['POST'])]
    #[Route(path: '/list/training-completion-reasons/', name: 'completionreasons_create', methods: ['POST'])]
    #[Route(path: '/list/training-degrees/', name: 'trainingdegrees_create', methods: ['POST'])]
    #[Route(path: '/list/training-majors/', name: 'trainingmajors_create', methods: ['POST'])]
    #[Route(path: '/list/training-minors/', name: 'trainingminors_create', methods: ['POST'])]
    #[Route(path: '/list/training-honors/', name: 'traininghonors_create', methods: ['POST'])]
    #[Route(path: '/list/fellowship-titles/', name: 'fellowshiptitles_create', methods: ['POST'])]
    #[Route(path: '/list/residency-specialties/', name: 'residencyspecialtys_create', methods: ['POST'])]
    #[Route(path: '/list/fellowship-subspecialties/', name: 'fellowshipsubspecialtys_create', methods: ['POST'])]
    #[Route(path: '/list/institution-types/', name: 'institutiontypes_create', methods: ['POST'])]
    #[Route(path: '/list/document-types/', name: 'documenttypes_create', methods: ['POST'])]
    #[Route(path: '/list/medical-titles/', name: 'medicaltitles_create', methods: ['POST'])]
    #[Route(path: '/list/medical-specialties/', name: 'medicalspecialties_create', methods: ['POST'])]
    #[Route(path: '/list/employment-types/', name: 'employmenttypes_create', methods: ['POST'])]
    #[Route(path: '/list/grant-source-organizations/', name: 'sourceorganizations_create', methods: ['POST'])]
    #[Route(path: '/list/languages/', name: 'languages_create', methods: ['POST'])]
    #[Route(path: '/list/locales/', name: 'locales_create', methods: ['POST'])]
    #[Route(path: '/list/ranks-of-importance/', name: 'importances_create', methods: ['POST'])]
    #[Route(path: '/list/authorship-roles/', name: 'authorshiproles_create', methods: ['POST'])]
    #[Route(path: '/list/lecture-venues/', name: 'organizations_create', methods: ['POST'])]
    #[Route(path: '/list/cities/', name: 'cities_create', methods: ['POST'])]
    #[Route(path: '/list/link-types/', name: 'linktypes_create', methods: ['POST'])]
    #[Route(path: '/list/sexes/', name: 'sexes_create', methods: ['POST'])]
    #[Route(path: '/list/position-types/', name: 'positiontypes_create', methods: ['POST'])]
    #[Route(path: '/list/organizational-group-types/', name: 'organizationalgrouptypes_create', methods: ['POST'])]
    #[Route(path: '/list/profile-comment-group-types/', name: 'commentgrouptypes_create', methods: ['POST'])]
    #[Route(path: '/list/comment-types/', name: 'commenttypes_createt', methods: ['POST'])]
    #[Route(path: '/list/user-wrappers/', name: 'userwrappers_create', methods: ['POST'])]
    #[Route(path: '/list/spot-purposes/', name: 'spotpurposes_create', methods: ['POST'])]
    #[Route(path: '/list/medical-license-statuses/', name: 'medicalstatuses_create', methods: ['POST'])]
    #[Route(path: '/list/certifying-board-organizations/', name: 'certifyingboardorganizations_create', methods: ['POST'])]
    #[Route(path: '/list/training-types/', name: 'trainingtypes_create', methods: ['POST'])]
    #[Route(path: '/list/job-titles/', name: 'joblists_create', methods: ['POST'])]
    #[Route(path: '/list/fellowship-application-statuses/', name: 'fellappstatuses_create', methods: ['POST'])]
    #[Route(path: '/list/fellowship-application-ranks/', name: 'fellappranks_create', methods: ['POST'])]
    #[Route(path: '/list/fellowship-application-language-proficiencies/', name: 'fellapplanguageproficiency_create', methods: ['POST'])]
    #[Route(path: '/list/collaboration-types/', name: 'collaborationtypes_create', methods: ['POST'])]
    #[Route(path: '/list/permissions/', name: 'permission_create', methods: ['POST'])]
    #[Route(path: '/list/permission-objects/', name: 'permissionobject_create', methods: ['POST'])]
    #[Route(path: '/list/permission-actions/', name: 'permissionaction_create', methods: ['POST'])]
    #[Route(path: '/list/sites/', name: 'sites_create', methods: ['POST'])]
    #[Route(path: '/list/event-object-types/', name: 'eventobjecttypes_create', methods: ['POST'])]
    #[Route(path: '/list/time-away-request-types/', name: 'vacreqrequesttypes_create', methods: ['POST'])]
    #[Route(path: '/list/time-away-request-floating-texts/', name: 'vacreqfloatingtexts_create', methods: ['POST'])]
    #[Route(path: '/list/time-away-request-floating-types/', name: 'vacreqfloatingtypes_create', methods: ['POST'])]
    #[Route(path: '/list/time-away-request-approval-types/', name: 'vacreqapprovaltypes_create', methods: ['POST'])]
    #[Route(path: '/list/healthcare-provider-specialties/', name: 'healthcareproviderspecialty_create', methods: ['POST'])]
    #[Route(path: '/list/healthcare-provider-initial-communications/', name: 'healthcareprovidercommunication_create', methods: ['POST'])]
    #[Route(path: '/list/object-types/', name: 'objecttypes_create', methods: ['POST'])]
    #[Route(path: '/list/form-nodes/', name: 'formnodes_create', methods: ['POST'], options: ['expose' => true])]
    #[Route(path: '/list/object-type-texts/', name: 'objecttypetexts_create', methods: ['POST'], options: ['expose' => true])]
    #[Route(path: '/list/blood-product-transfusions/', name: 'bloodproducttransfusions_create', methods: ['POST'])]
    #[Route(path: '/list/transfusion-reaction-types/', name: 'transfusionreactiontypes_create', methods: ['POST'])]
    #[Route(path: '/list/object-type-strings/', name: 'objecttypestrings_create', methods: ['POST'])]
    #[Route(path: '/list/object-type-dropdowns/', name: 'objecttypedropdowns_create', methods: ['POST'])]
    #[Route(path: '/list/blood-types/', name: 'bloodtypes_create', methods: ['POST'])]
    #[Route(path: '/list/additional-communications/', name: 'additionalcommunications_create', methods: ['POST'])]
    #[Route(path: '/list/transfusion-antibody-screen-results/', name: 'transfusionantibodyscreenresults_create', methods: ['POST'])]
    #[Route(path: '/list/transfusion-crossmatch-results/', name: 'transfusioncrossmatchresults_create', methods: ['POST'])]
    #[Route(path: '/list/transfusion-dat-results/', name: 'transfusiondatresults_create', methods: ['POST'])]
    #[Route(path: '/list/transfusion-hemolysis-check-results/', name: 'transfusionhemolysischeckresults_create', methods: ['POST'])]
    #[Route(path: '/list/object-type-datetimes/', name: 'objecttypedatetimes_create', methods: ['POST'])]
    #[Route(path: '/list/complex-platelet-summary-antibodies/', name: 'complexplateletsummaryantibodies_create', methods: ['POST'])]
    #[Route(path: '/list/cci-unit-platelet-count-default-values/', name: 'cciunitplateletcountdefaultvalues_create', methods: ['POST'])]
    #[Route(path: '/list/cci-platelet-type-transfused/', name: 'cciplatelettypetransfuseds_create', methods: ['POST'])]
    #[Route(path: '/list/platelet-transfusion-product-receiving/', name: 'platelettransfusionproductreceivings_create', methods: ['POST'])]
    #[Route(path: '/list/transfusion-product-status/', name: 'transfusionproductstatus_create', methods: ['POST'])]
    #[Route(path: '/list/week-days/', name: 'weekdays_create', methods: ['POST'])]
    #[Route(path: '/list/months/', name: 'months_create', methods: ['POST'])]
    #[Route(path: '/list/clerical-errors/', name: 'clericalerrors_create', methods: ['POST'])]
    #[Route(path: '/list/lab-result-names/', name: 'labresultnames_create', methods: ['POST'])]
    #[Route(path: '/list/lab-result-units-measures/', name: 'labresultunitsmeasures_create', methods: ['POST'])]
    #[Route(path: '/list/lab-result-flags/', name: 'labresultflags_create', methods: ['POST'])]
    #[Route(path: '/list/pathology-result-signatories/', name: 'pathologyresultsignatories_create', methods: ['POST'])]
    #[Route(path: '/list/object-type-checkboxes/', name: 'objecttypecheckboxs_create', methods: ['POST'])]
    #[Route(path: '/list/object-type-radio-buttons/', name: 'objecttyperadiobuttons_create', methods: ['POST'])]
    #[Route(path: '/list/life-forms/', name: 'lifeforms_create', methods: ['POST'])]
    #[Route(path: '/list/position-track-types/', name: 'positiontracktypes_create', methods: ['POST'])]
    #[Route(path: '/list/translational-research-project-specialties-orig/', name: 'transresprojectspecialties_create_orig', methods: ['POST'])]
    #[Route(path: '/list/translational-research-project-types/', name: 'transresprojecttypes_create', methods: ['POST'])]
    #[Route(path: '/list/translational-research-request-category-types/', name: 'transresrequestcategorytypes_create', methods: ['POST'])]
    #[Route(path: '/list/translational-irb-approval-types/', name: 'transresirbapprovaltypes_create', methods: ['POST'])]
    #[Route(path: '/list/translational-business-purposes/', name: 'transresbusinesspurposes_create', methods: ['POST'])]
    #[Route(path: '/list/translational-work-queue-types/', name: 'workqueuetypes_create', methods: ['POST'])]
    #[Route(path: '/list/translational-orderable-status/', name: 'orderablestatus_create', methods: ['POST'])]
    #[Route(path: '/list/antibodies/', name: 'antibodies_create', methods: ['POST'])]
    #[Route(path: '/list/custom000/', name: 'custom000_create', methods: ['POST'])]
    #[Route(path: '/list/custom001/', name: 'custom001_create', methods: ['POST'])]
    #[Route(path: '/list/custom002/', name: 'custom002_create', methods: ['POST'])]
    #[Route(path: '/list/custom003/', name: 'custom003_create', methods: ['POST'])]
    #[Route(path: '/list/custom004/', name: 'custom004_create', methods: ['POST'])]
    #[Route(path: '/list/custom005/', name: 'custom005_create', methods: ['POST'])]
    #[Route(path: '/list/custom006/', name: 'custom006_create', methods: ['POST'])]
    #[Route(path: '/list/custom007/', name: 'custom007_create', methods: ['POST'])]
    #[Route(path: '/list/custom008/', name: 'custom008_create', methods: ['POST'])]
    #[Route(path: '/list/custom009/', name: 'custom009_create', methods: ['POST'])]
    #[Route(path: '/list/custom010/', name: 'custom010_create', methods: ['POST'])]
    #[Route(path: '/list/custom011/', name: 'custom011_create', methods: ['POST'])]
    #[Route(path: '/list/custom012/', name: 'custom012_create', methods: ['POST'])]
    #[Route(path: '/list/custom013/', name: 'custom013_create', methods: ['POST'])]
    #[Route(path: '/list/custom014/', name: 'custom014_create', methods: ['POST'])]
    #[Route(path: '/list/custom015/', name: 'custom015_create', methods: ['POST'])]
    #[Route(path: '/list/custom016/', name: 'custom016_create', methods: ['POST'])]
    #[Route(path: '/list/custom017/', name: 'custom017_create', methods: ['POST'])]
    #[Route(path: '/list/custom018/', name: 'custom018_create', methods: ['POST'])]
    #[Route(path: '/list/custom019/', name: 'custom019_create', methods: ['POST'])]
    #[Route(path: '/list/custom020/', name: 'custom020_create', methods: ['POST'])]
    #[Route(path: '/list/custom021/', name: 'custom021_create', methods: ['POST'])]
    #[Route(path: '/list/custom022/', name: 'custom022_create', methods: ['POST'])]
    #[Route(path: '/list/custom023/', name: 'custom023_create', methods: ['POST'])]
    #[Route(path: '/list/custom024/', name: 'custom024_create', methods: ['POST'])]
    #[Route(path: '/list/custom025/', name: 'custom025_create', methods: ['POST'])]
    #[Route(path: '/list/custom026/', name: 'custom026_create', methods: ['POST'])]
    #[Route(path: '/list/custom027/', name: 'custom027_create', methods: ['POST'])]
    #[Route(path: '/list/custom028/', name: 'custom028_create', methods: ['POST'])]
    #[Route(path: '/list/custom029/', name: 'custom029_create', methods: ['POST'])]
    #[Route(path: '/list/custom030/', name: 'custom030_create', methods: ['POST'])]
    #[Route(path: '/list/custom031/', name: 'custom031_create', methods: ['POST'])]
    #[Route(path: '/list/custom032/', name: 'custom032_create', methods: ['POST'])]
    #[Route(path: '/list/custom033/', name: 'custom033_create', methods: ['POST'])]
    #[Route(path: '/list/custom034/', name: 'custom034_create', methods: ['POST'])]
    #[Route(path: '/list/custom035/', name: 'custom035_create', methods: ['POST'])]
    #[Route(path: '/list/custom036/', name: 'custom036_create', methods: ['POST'])]
    #[Route(path: '/list/custom037/', name: 'custom037_create', methods: ['POST'])]
    #[Route(path: '/list/custom038/', name: 'custom038_create', methods: ['POST'])]
    #[Route(path: '/list/custom039/', name: 'custom039_create', methods: ['POST'])]
    #[Route(path: '/list/custom040/', name: 'custom040_create', methods: ['POST'])]
    #[Route(path: '/list/custom041/', name: 'custom041_create', methods: ['POST'])]
    #[Route(path: '/list/custom042/', name: 'custom042_create', methods: ['POST'])]
    #[Route(path: '/list/custom043/', name: 'custom043_create', methods: ['POST'])]
    #[Route(path: '/list/custom044/', name: 'custom044_create', methods: ['POST'])]
    #[Route(path: '/list/custom045/', name: 'custom045_create', methods: ['POST'])]
    #[Route(path: '/list/custom046/', name: 'custom046_create', methods: ['POST'])]
    #[Route(path: '/list/custom047/', name: 'custom047_create', methods: ['POST'])]
    #[Route(path: '/list/custom048/', name: 'custom048_create', methods: ['POST'])]
    #[Route(path: '/list/custom049/', name: 'custom049_create', methods: ['POST'])]
    #[Route(path: '/list/custom050/', name: 'custom050_create', methods: ['POST'])]
    #[Route(path: '/list/custom051/', name: 'custom051_create', methods: ['POST'])]
    #[Route(path: '/list/custom052/', name: 'custom052_create', methods: ['POST'])]
    #[Route(path: '/list/custom053/', name: 'custom053_create', methods: ['POST'])]
    #[Route(path: '/list/custom054/', name: 'custom054_create', methods: ['POST'])]
    #[Route(path: '/list/custom055/', name: 'custom055_create', methods: ['POST'])]
    #[Route(path: '/list/custom056/', name: 'custom056_create', methods: ['POST'])]
    #[Route(path: '/list/custom057/', name: 'custom057_create', methods: ['POST'])]
    #[Route(path: '/list/custom058/', name: 'custom058_create', methods: ['POST'])]
    #[Route(path: '/list/custom059/', name: 'custom059_create', methods: ['POST'])]
    #[Route(path: '/list/custom060/', name: 'custom060_create', methods: ['POST'])]
    #[Route(path: '/list/custom061/', name: 'custom061_create', methods: ['POST'])]
    #[Route(path: '/list/custom062/', name: 'custom062_create', methods: ['POST'])]
    #[Route(path: '/list/custom063/', name: 'custom063_create', methods: ['POST'])]
    #[Route(path: '/list/custom064/', name: 'custom064_create', methods: ['POST'])]
    #[Route(path: '/list/custom065/', name: 'custom065_create', methods: ['POST'])]
    #[Route(path: '/list/custom066/', name: 'custom066_create', methods: ['POST'])]
    #[Route(path: '/list/custom067/', name: 'custom067_create', methods: ['POST'])]
    #[Route(path: '/list/custom068/', name: 'custom068_create', methods: ['POST'])]
    #[Route(path: '/list/custom069/', name: 'custom069_create', methods: ['POST'])]
    #[Route(path: '/list/custom070/', name: 'custom070_create', methods: ['POST'])]
    #[Route(path: '/list/custom071/', name: 'custom071_create', methods: ['POST'])]
    #[Route(path: '/list/custom072/', name: 'custom072_create', methods: ['POST'])]
    #[Route(path: '/list/custom073/', name: 'custom073_create', methods: ['POST'])]
    #[Route(path: '/list/custom074/', name: 'custom074_create', methods: ['POST'])]
    #[Route(path: '/list/custom075/', name: 'custom075_create', methods: ['POST'])]
    #[Route(path: '/list/custom076/', name: 'custom076_create', methods: ['POST'])]
    #[Route(path: '/list/custom077/', name: 'custom077_create', methods: ['POST'])]
    #[Route(path: '/list/custom078/', name: 'custom078_create', methods: ['POST'])]
    #[Route(path: '/list/custom079/', name: 'custom079_create', methods: ['POST'])]
    #[Route(path: '/list/custom080/', name: 'custom080_create', methods: ['POST'])]
    #[Route(path: '/list/custom081/', name: 'custom081_create', methods: ['POST'])]
    #[Route(path: '/list/custom082/', name: 'custom082_create', methods: ['POST'])]
    #[Route(path: '/list/custom083/', name: 'custom083_create', methods: ['POST'])]
    #[Route(path: '/list/custom084/', name: 'custom084_create', methods: ['POST'])]
    #[Route(path: '/list/custom085/', name: 'custom085_create', methods: ['POST'])]
    #[Route(path: '/list/custom086/', name: 'custom086_create', methods: ['POST'])]
    #[Route(path: '/list/custom087/', name: 'custom087_create', methods: ['POST'])]
    #[Route(path: '/list/custom088/', name: 'custom088_create', methods: ['POST'])]
    #[Route(path: '/list/custom089/', name: 'custom089_create', methods: ['POST'])]
    #[Route(path: '/list/custom090/', name: 'custom090_create', methods: ['POST'])]
    #[Route(path: '/list/custom091/', name: 'custom091_create', methods: ['POST'])]
    #[Route(path: '/list/custom092/', name: 'custom092_create', methods: ['POST'])]
    #[Route(path: '/list/custom093/', name: 'custom093_create', methods: ['POST'])]
    #[Route(path: '/list/custom094/', name: 'custom094_create', methods: ['POST'])]
    #[Route(path: '/list/custom095/', name: 'custom095_create', methods: ['POST'])]
    #[Route(path: '/list/custom096/', name: 'custom096_create', methods: ['POST'])]
    #[Route(path: '/list/custom097/', name: 'custom097_create', methods: ['POST'])]
    #[Route(path: '/list/custom098/', name: 'custom098_create', methods: ['POST'])]
    #[Route(path: '/list/custom099/', name: 'custom099_create', methods: ['POST'])]
    #[Route(path: '/list/translational-tissue-processing-services/', name: 'transrestissueprocessingservices_create', methods: ['POST'])]
    #[Route(path: '/list/translational-other-requested-services/', name: 'transresotherrequestedservices_create', methods: ['POST'])]
    #[Route(path: '/list/translational-collaboration-labs/', name: 'transrescolllabs_create', methods: ['POST'])]
    #[Route(path: '/list/translational-collaboration-divs/', name: 'transrescolldivs_create', methods: ['POST'])]
    #[Route(path: '/list/translational-irb-approval-status/', name: 'transresirbstatus_create', methods: ['POST'])]
    #[Route(path: '/list/translational-requester-group/', name: 'transresrequestergroup_create', methods: ['POST'])]
    #[Route(path: '/list/transrescomptypes/', name: 'transrescomptypes_create', methods: ['POST'])]
    #[Route(path: '/list/visastatus/', name: 'visastatus_create', methods: ['POST'])]
    #[Route(path: '/list/resappstatuses/', name: 'resappstatuses_create', methods: ['POST'])]
    #[Route(path: '/list/resappranks/', name: 'resappranks_create', methods: ['POST'])]
    #[Route(path: '/list/resapplanguageproficiency/', name: 'resapplanguageproficiency_create', methods: ['POST'])]
    #[Route(path: '/list/resappfitforprogram/', name: 'resappfitforprogram_create', methods: ['POST'])]
    #[Route(path: '/list/resappvisastatus/', name: 'resappvisastatus_create', methods: ['POST'])]
    #[Route(path: '/list/postsoph/', name: 'postsoph_create', methods: ['POST'])]
    #[Route(path: '/list/resappapplyingresidencytrack/', name: 'resappapplyingresidencytrack_create', methods: ['POST'])]
    #[Route(path: '/list/resapplearnarealist/', name: 'resapplearnarealist_create', methods: ['POST'])]
    #[Route(path: '/list/resappspecificindividuallist/', name: 'resappspecificindividuallist_create', methods: ['POST'])]
    #[Route(path: '/list/viewmodes/', name: 'viewmodes_create', methods: ['POST'])]
    #[Route(path: '/list/transrespricetypes/', name: 'transrespricetypes_create', methods: ['POST'])]
    #[Route(path: '/list/charttypes/', name: 'charttypes_create', methods: ['POST'], options: ['expose' => true])]
    #[Route(path: '/list/charttopics/', name: 'charttopics_create', methods: ['POST'], options: ['expose' => true])]
    #[Route(path: '/list/chartfilters/', name: 'chartfilters_create', methods: ['POST'])]
    #[Route(path: '/list/charts/', name: 'charts_create', methods: ['POST'])]
    #[Route(path: '/list/chartdatasources/', name: 'chartdatasources_create', methods: ['POST'])]
    #[Route(path: '/list/chartupdatefrequencies/', name: 'chartupdatefrequencies_create', methods: ['POST'])]
    #[Route(path: '/list/chartvisualizations/', name: 'chartvisualizations_create', methods: ['POST'])]
    #[Route(path: '/list/vacreqholidays/', name: 'vacreqholidays_create', methods: ['POST'])]
    #[Route(path: '/list/vacreqobservedholidays/', name: 'vacreqobservedholidays_create', methods: ['POST'])]
    #[Route(path: '/list/authusergroup/', name: 'authusergroup_create', methods: ['POST'])]
    #[Route(path: '/list/authservernetwork/', name: 'authservernetwork_create', methods: ['POST'])]
    #[Route(path: '/list/authpartnerserver/', name: 'authpartnerserver_create', methods: ['POST'])]
    #[Route(path: '/list/tenanturls/', name: 'tenanturls_create', methods: ['POST'], options: ['expose' => true])]
    #[Route(path: '/list/antibodycategorytag/', name: 'antibodycategorytag_create', methods: ['POST'])]
    #[Route(path: '/list/transferstatus/', name: 'transferstatus_create', methods: ['POST'])]
    #[Route(path: '/list/interfacetransfers/', name: 'interfacetransfers_create', methods: ['POST'])]
    #[Route(path: '/list/antibodylabs/', name: 'antibodylabs_create', methods: ['POST'])]
    #[Route(path: '/list/antibodypanels/', name: 'antibodypanels_create', methods: ['POST'])]
    #[Route(path: '/list/samlconfig/', name: 'samlconfig_create', methods: ['POST'])]
    #[Route(path: '/list/globalfellowshipspecialty/', name: 'globalfellowshipspecialty_create', methods: ['POST'])]
    #[Route(path: '/list/trainingeligibility/', name: 'trainingeligibility_create', methods: ['POST'])]
    #[Route(path: '/list/dutiescapability/', name: 'dutiescapability_create', methods: ['POST'])]
    #[Route(path: '/list/phdfield/', name: 'phdfield_create', methods: ['POST'])]
    #[Template('AppUserdirectoryBundle/ListForm/new.html.twig')]
    public function createAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        return $this->createList($request);
    }
    public function createList( $request ) {

        $routeName = $request->get('_route');

        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $mapper= $this->classListMapper($pathbase,$request);

        $entityClass = $mapper['fullClassName'];    //"App\\OrderformBundle\\Entity\\".$mapper['className'];

        $entity = new $entityClass();

        $form = $this->createCreateForm($entity,$mapper,$pathbase,'new');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            //the date from the form does not contain time, so set createdate with date and time.
            $entity->setCreatedate(new \DateTime());

            $user = $this->getUser();
            $entity->setCreator($user);

            if( $entity instanceof UsernameType ) {
                $entity->setEmptyAbbreviation();
            }

            if( method_exists($entity, "getDocuments") ) {
                $em->getRepository(Document::class)->processDocuments($entity, "document");
            }

            if( method_exists($entity, "getVisualInfos") ) {
                foreach( $entity->getVisualInfos() as $visualInfo) {
                    $em->getRepository(Document::class)->processDocuments( $visualInfo, "document" );
                }
            }
            
            $em->persist($entity);
            $em->flush();
            
            $this->postProcessList($entity);

            //EventLog
            $userSecUtil = $this->container->get('user_security_utility');
            $newName = "Unknown";
            if( method_exists($entity,"getName") ) {
                $newName = $entity->getName();
            }
            $event = "New list '".$newName."' created by $user";
            $userSecUtil->createUserEditEvent($this->getParameter('employees.sitename'),$event,$user,$entity,$request,'List Created');
            
            return $this->redirect($this->generateUrl($pathbase.'_show'.$this->postPath, array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase,
            'sitename' => $this->sitename,
            'cycle' => 'new'
        );
    }

    public function postProcessList($entity) {
        return NULL;
    }


    /**
    * Creates a form to create an entity.
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm($entity,$mapper,$pathbase,$cycle=null)
    {
        $options = array();

        if( $cycle ) {
            $options['cycle'] = $cycle;
        }

        //use $timezone = $user->getTimezone(); ?
        $user = $this->getUser();
        $options['user'] = $user;
        $options['entity'] = $entity;
        $options['em'] = $this->getDoctrine()->getManager();
        //$options['SecurityAuthChecker'] = $this->container->get('security.authorization_checker');

        //exit("this->postPath=".$this->postPath);
        //$path = $pathbase.'_create'.$this->postPath;
        $actionUrl = $this->generateUrl($pathbase.'_create'.$this->postPath);
        //exit("path=".$path."; actionUrl=".$actionUrl);

        $form = $this->createForm(GenericListType::class, $entity, array(
            'action' => $actionUrl,
            //'method' => 'POST',
            'data_class' => $mapper['fullClassName'],
            'form_custom_value' => $options,
            'form_custom_value_mapper' => $mapper
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create','attr'=>array('class'=>'btn btn-warning')));

        return $form;
    }

    /**
     * Displays a form to create a new entity.
     *
     *
     *
     *
     *
     *
     *
     *
     */
    #[Route(path: '/list/list-manager/new', name: 'platformlistmanager_new')]
    #[Route(path: '/list/source-systems/new', name: 'sourcesystems_new', methods: ['GET'])]
    #[Route(path: '/list/roles/new', name: 'role_new', methods: ['GET'])]
    #[Route(path: '/list/institutions/new', name: 'institutions_new', methods: ['GET'])]
    #[Route(path: '/list/states/new', name: 'states_new', methods: ['GET'])]
    #[Route(path: '/list/countries/new', name: 'countries_new', methods: ['GET'])]
    #[Route(path: '/list/board-certifications/new', name: 'boardcertifications_new', methods: ['GET'])]
    #[Route(path: '/list/employment-termination-reasons/new', name: 'employmentterminations_new', methods: ['GET'])]
    #[Route(path: '/list/event-log-event-types/new', name: 'loggereventtypes_new', methods: ['GET'])]
    #[Route(path: '/list/primary-public-user-id-types/new', name: 'usernametypes_new', methods: ['GET'])]
    #[Route(path: '/list/identifier-types/new', name: 'identifiers_new', methods: ['GET'])]
    #[Route(path: '/list/residency-tracks/new', name: 'residencytracks_new', methods: ['GET'])]
    #[Route(path: '/list/fellowship-types/new', name: 'fellowshiptypes_new', methods: ['GET'])]
    #[Route(path: '/list/location-types/new', name: 'locationtypes_new', methods: ['GET'])]
    #[Route(path: '/list/equipment/new', name: 'equipments_new', methods: ['GET'])]
    #[Route(path: '/list/equipment-types/new', name: 'equipmenttypes_new', methods: ['GET'])]
    #[Route(path: '/list/location-privacy-types/new', name: 'locationprivacy_new', methods: ['GET'])]
    #[Route(path: '/list/role-attributes/new', name: 'roleattributes_new', methods: ['GET'])]
    #[Route(path: '/list/buidlings/new', name: 'buildings_new', methods: ['GET'])]
    #[Route(path: '/list/rooms/new', name: 'rooms_new', methods: ['GET'])]
    #[Route(path: '/list/suites/new', name: 'suites_new', methods: ['GET'])]
    #[Route(path: '/list/floors/new', name: 'floors_new', methods: ['GET'])]
    #[Route(path: '/list/grants/new', name: 'grants_new', methods: ['GET'])]
    #[Route(path: '/list/mailboxes/new', name: 'mailboxes_new', methods: ['GET'])]
    #[Route(path: '/list/percent-effort/new', name: 'efforts_new', methods: ['GET'])]
    #[Route(path: '/list/administrative-titles/new', name: 'admintitles_new', methods: ['GET'])]
    #[Route(path: '/list/academic-appointment-titles/new', name: 'apptitles_new', methods: ['GET'])]
    #[Route(path: '/list/training-completion-reasons/new', name: 'completionreasons_new', methods: ['GET'])]
    #[Route(path: '/list/training-degrees/new', name: 'trainingdegrees_new', methods: ['GET'])]
    #[Route(path: '/list/training-majors/new', name: 'trainingmajors_new', methods: ['GET'])]
    #[Route(path: '/list/training-minors/new', name: 'trainingminors_new', methods: ['GET'])]
    #[Route(path: '/list/training-honors/new', name: 'traininghonors_new', methods: ['GET'])]
    #[Route(path: '/list/fellowship-titles/new', name: 'fellowshiptitles_new', methods: ['GET'])]
    #[Route(path: '/list/residency-specialties/new', name: 'residencyspecialtys_new', methods: ['GET'])]
    #[Route(path: '/list/fellowship-subspecialties/new', name: 'fellowshipsubspecialtys_new', methods: ['GET'])]
    #[Route(path: '/list/institution-types/new', name: 'institutiontypes_new', methods: ['GET'])]
    #[Route(path: '/list/document-types/new', name: 'documenttypes_new', methods: ['GET'])]
    #[Route(path: '/list/medical-titles/new', name: 'medicaltitles_new', methods: ['GET'])]
    #[Route(path: '/list/medical-specialties/new', name: 'medicalspecialties_new', methods: ['GET'])]
    #[Route(path: '/list/employment-types/new', name: 'employmenttypes_new', methods: ['GET'])]
    #[Route(path: '/list/grant-source-organizations/new', name: 'sourceorganizations_new', methods: ['GET'])]
    #[Route(path: '/list/languages/new', name: 'languages_new', methods: ['GET'])]
    #[Route(path: '/list/locales/new', name: 'locales_new', methods: ['GET'])]
    #[Route(path: '/list/ranks-of-importance/new', name: 'importances_new', methods: ['GET'])]
    #[Route(path: '/list/authorship-roles/new', name: 'authorshiproles_new', methods: ['GET'])]
    #[Route(path: '/list/lecture-venues/new', name: 'organizations_new', methods: ['GET'])]
    #[Route(path: '/list/cities/new', name: 'cities_new', methods: ['GET'])]
    #[Route(path: '/list/link-types/new', name: 'linktypes_new', methods: ['GET'])]
    #[Route(path: '/list/sexes/new', name: 'sexes_new', methods: ['GET'])]
    #[Route(path: '/list/position-types/new', name: 'positiontypes_new', methods: ['GET'])]
    #[Route(path: '/list/organizational-group-types/new', name: 'organizationalgrouptypes_new', methods: ['GET'])]
    #[Route(path: '/list/profile-comment-group-types/new', name: 'commentgrouptypes_new', methods: ['GET'])]
    #[Route(path: '/list/comment-types/new', name: 'commenttypes_new', methods: ['GET'])]
    #[Route(path: '/list/user-wrappers/new', name: 'userwrappers_new', methods: ['GET'])]
    #[Route(path: '/list/spot-purposes/new', name: 'spotpurposes_new', methods: ['GET'])]
    #[Route(path: '/list/medical-license-statuses/new', name: 'medicalstatuses_new', methods: ['GET'])]
    #[Route(path: '/list/certifying-board-organizations/new', name: 'certifyingboardorganizations_new', methods: ['GET'])]
    #[Route(path: '/list/training-types/new', name: 'trainingtypes_new', methods: ['GET'])]
    #[Route(path: '/list/job-titles/new', name: 'joblists_new', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-statuses/new', name: 'fellappstatuses_new', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-ranks/new', name: 'fellappranks_new', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-language-proficiencies/new', name: 'fellapplanguageproficiency_new', methods: ['GET'])]
    #[Route(path: '/list/collaboration-types/new', name: 'collaborationtypes_new', methods: ['GET'])]
    #[Route(path: '/list/permissions/new', name: 'permission_new', methods: ['GET'])]
    #[Route(path: '/list/permission-objects/new', name: 'permissionobject_new', methods: ['GET'])]
    #[Route(path: '/list/permission-actions/new', name: 'permissionaction_new', methods: ['GET'])]
    #[Route(path: '/list/sites/new', name: 'sites_new', methods: ['GET'])]
    #[Route(path: '/list/event-object-types/new', name: 'eventobjecttypes_new', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-types/new', name: 'vacreqrequesttypes_new', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-floating-texts/new', name: 'vacreqfloatingtexts_new', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-floating-types/new', name: 'vacreqfloatingtypes_new', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-approval-types/new', name: 'vacreqapprovaltypes_new', methods: ['GET'])]
    #[Route(path: '/list/healthcare-provider-specialties/new', name: 'healthcareproviderspecialty_new', methods: ['GET'])]
    #[Route(path: '/list/healthcare-provider-initial-communications/new', name: 'healthcareprovidercommunication_new', methods: ['GET'])]
    #[Route(path: '/list/object-types/new', name: 'objecttypes_new', methods: ['GET'])]
    #[Route(path: '/list/form-nodes/new', name: 'formnodes_new', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/object-type-texts/new', name: 'objecttypetexts_new', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/blood-product-transfusions/new', name: 'bloodproducttransfusions_new', methods: ['GET'])]
    #[Route(path: '/list/transfusion-reaction-types/new', name: 'transfusionreactiontypes_new', methods: ['GET'])]
    #[Route(path: '/list/object-type-strings/new', name: 'objecttypestrings_new', methods: ['GET'])]
    #[Route(path: '/list/object-type-dropdowns/new', name: 'objecttypedropdowns_new', methods: ['GET'])]
    #[Route(path: '/list/blood-types/new', name: 'bloodtypes_new', methods: ['GET'])]
    #[Route(path: '/list/additional-communications/new', name: 'additionalcommunications_new', methods: ['GET'])]
    #[Route(path: '/list/transfusion-antibody-screen-results/new', name: 'transfusionantibodyscreenresults_new', methods: ['GET'])]
    #[Route(path: '/list/transfusion-crossmatch-results/new', name: 'transfusioncrossmatchresults_new', methods: ['GET'])]
    #[Route(path: '/list/transfusion-dat-results/new', name: 'transfusiondatresults_new', methods: ['GET'])]
    #[Route(path: '/list/transfusion-hemolysis-check-results/new', name: 'transfusionhemolysischeckresults_new', methods: ['GET'])]
    #[Route(path: '/list/object-type-datetimes/new', name: 'objecttypedatetimes_new', methods: ['GET'])]
    #[Route(path: '/list/complex-platelet-summary-antibodies/new', name: 'complexplateletsummaryantibodies_new', methods: ['GET'])]
    #[Route(path: '/list/cci-unit-platelet-count-default-values/new', name: 'cciunitplateletcountdefaultvalues_new', methods: ['GET'])]
    #[Route(path: '/list/cci-platelet-type-transfused/new', name: 'cciplatelettypetransfuseds_new', methods: ['GET'])]
    #[Route(path: '/list/platelet-transfusion-product-receiving/new', name: 'platelettransfusionproductreceivings_new', methods: ['GET'])]
    #[Route(path: '/list/transfusion-product-status/new', name: 'transfusionproductstatus_new', methods: ['GET'])]
    #[Route(path: '/list/week-days/new', name: 'weekdays_new', methods: ['GET'])]
    #[Route(path: '/list/months/new', name: 'months_new', methods: ['GET'])]
    #[Route(path: '/list/clerical-errors/new', name: 'clericalerrors_new', methods: ['GET'])]
    #[Route(path: '/list/lab-result-names/new', name: 'labresultnames_new', methods: ['GET'])]
    #[Route(path: '/list/lab-result-units-measures/new', name: 'labresultunitsmeasures_new', methods: ['GET'])]
    #[Route(path: '/list/lab-result-flags/new', name: 'labresultflags_new', methods: ['GET'])]
    #[Route(path: '/list/pathology-result-signatories/new', name: 'pathologyresultsignatories_new', methods: ['GET'])]
    #[Route(path: '/list/object-type-checkboxes/new', name: 'objecttypecheckboxs_new', methods: ['GET'])]
    #[Route(path: '/list/object-type-radio-buttons/new', name: 'objecttyperadiobuttons_new', methods: ['GET'])]
    #[Route(path: '/list/life-forms/new', name: 'lifeforms_new', methods: ['GET'])]
    #[Route(path: '/list/position-track-types/new', name: 'positiontracktypes_new', methods: ['GET'])]
    #[Route(path: '/list/translational-research-project-specialties-orig/new', name: 'transresprojectspecialties_new_orig', methods: ['GET'])]
    #[Route(path: '/list/translational-research-project-types/new', name: 'transresprojecttypes_new', methods: ['GET'])]
    #[Route(path: '/list/translational-research-request-category-types/new', name: 'transresrequestcategorytypes_new', methods: ['GET'])]
    #[Route(path: '/list/translational-irb-approval-types/new', name: 'transresirbapprovaltypes_new', methods: ['GET'])]
    #[Route(path: '/list/translational-business-purposes/new', name: 'transresbusinesspurposes_new', methods: ['GET'])]
    #[Route(path: '/list/translational-work-queue-types/new', name: 'workqueuetypes_new', methods: ['GET'])]
    #[Route(path: '/list/antibodies/new', name: 'antibodies_new', methods: ['GET'])]
    #[Route(path: '/list/translational-orderable-status/new', name: 'orderablestatus_new', methods: ['GET'])]
    #[Route(path: '/list/custom000/new', name: 'custom000_new', methods: ['GET'])]
    #[Route(path: '/list/custom001/new', name: 'custom001_new', methods: ['GET'])]
    #[Route(path: '/list/custom002/new', name: 'custom002_new', methods: ['GET'])]
    #[Route(path: '/list/custom003/new', name: 'custom003_new', methods: ['GET'])]
    #[Route(path: '/list/custom004/new', name: 'custom004_new', methods: ['GET'])]
    #[Route(path: '/list/custom005/new', name: 'custom005_new', methods: ['GET'])]
    #[Route(path: '/list/custom006/new', name: 'custom006_new', methods: ['GET'])]
    #[Route(path: '/list/custom007/new', name: 'custom007_new', methods: ['GET'])]
    #[Route(path: '/list/custom008/new', name: 'custom008_new', methods: ['GET'])]
    #[Route(path: '/list/custom009/new', name: 'custom009_new', methods: ['GET'])]
    #[Route(path: '/list/custom010/new', name: 'custom010_new', methods: ['GET'])]
    #[Route(path: '/list/custom011/new', name: 'custom011_new', methods: ['GET'])]
    #[Route(path: '/list/custom012/new', name: 'custom012_new', methods: ['GET'])]
    #[Route(path: '/list/custom013/new', name: 'custom013_new', methods: ['GET'])]
    #[Route(path: '/list/custom014/new', name: 'custom014_new', methods: ['GET'])]
    #[Route(path: '/list/custom015/new', name: 'custom015_new', methods: ['GET'])]
    #[Route(path: '/list/custom016/new', name: 'custom016_new', methods: ['GET'])]
    #[Route(path: '/list/custom017/new', name: 'custom017_new', methods: ['GET'])]
    #[Route(path: '/list/custom018/new', name: 'custom018_new', methods: ['GET'])]
    #[Route(path: '/list/custom019/new', name: 'custom019_new', methods: ['GET'])]
    #[Route(path: '/list/custom020/new', name: 'custom020_new', methods: ['GET'])]
    #[Route(path: '/list/custom021/new', name: 'custom021_new', methods: ['GET'])]
    #[Route(path: '/list/custom022/new', name: 'custom022_new', methods: ['GET'])]
    #[Route(path: '/list/custom023/new', name: 'custom023_new', methods: ['GET'])]
    #[Route(path: '/list/custom024/new', name: 'custom024_new', methods: ['GET'])]
    #[Route(path: '/list/custom025/new', name: 'custom025_new', methods: ['GET'])]
    #[Route(path: '/list/custom026/new', name: 'custom026_new', methods: ['GET'])]
    #[Route(path: '/list/custom027/new', name: 'custom027_new', methods: ['GET'])]
    #[Route(path: '/list/custom028/new', name: 'custom028_new', methods: ['GET'])]
    #[Route(path: '/list/custom029/new', name: 'custom029_new', methods: ['GET'])]
    #[Route(path: '/list/custom030/new', name: 'custom030_new', methods: ['GET'])]
    #[Route(path: '/list/custom031/new', name: 'custom031_new', methods: ['GET'])]
    #[Route(path: '/list/custom032/new', name: 'custom032_new', methods: ['GET'])]
    #[Route(path: '/list/custom033/new', name: 'custom033_new', methods: ['GET'])]
    #[Route(path: '/list/custom034/new', name: 'custom034_new', methods: ['GET'])]
    #[Route(path: '/list/custom035/new', name: 'custom035_new', methods: ['GET'])]
    #[Route(path: '/list/custom036/new', name: 'custom036_new', methods: ['GET'])]
    #[Route(path: '/list/custom037/new', name: 'custom037_new', methods: ['GET'])]
    #[Route(path: '/list/custom038/new', name: 'custom038_new', methods: ['GET'])]
    #[Route(path: '/list/custom039/new', name: 'custom039_new', methods: ['GET'])]
    #[Route(path: '/list/custom040/new', name: 'custom040_new', methods: ['GET'])]
    #[Route(path: '/list/custom041/new', name: 'custom041_new', methods: ['GET'])]
    #[Route(path: '/list/custom042/new', name: 'custom042_new', methods: ['GET'])]
    #[Route(path: '/list/custom043/new', name: 'custom043_new', methods: ['GET'])]
    #[Route(path: '/list/custom044/new', name: 'custom044_new', methods: ['GET'])]
    #[Route(path: '/list/custom045/new', name: 'custom045_new', methods: ['GET'])]
    #[Route(path: '/list/custom046/new', name: 'custom046_new', methods: ['GET'])]
    #[Route(path: '/list/custom047/new', name: 'custom047_new', methods: ['GET'])]
    #[Route(path: '/list/custom048/new', name: 'custom048_new', methods: ['GET'])]
    #[Route(path: '/list/custom049/new', name: 'custom049_new', methods: ['GET'])]
    #[Route(path: '/list/custom050/new', name: 'custom050_new', methods: ['GET'])]
    #[Route(path: '/list/custom051/new', name: 'custom051_new', methods: ['GET'])]
    #[Route(path: '/list/custom052/new', name: 'custom052_new', methods: ['GET'])]
    #[Route(path: '/list/custom053/new', name: 'custom053_new', methods: ['GET'])]
    #[Route(path: '/list/custom054/new', name: 'custom054_new', methods: ['GET'])]
    #[Route(path: '/list/custom055/new', name: 'custom055_new', methods: ['GET'])]
    #[Route(path: '/list/custom056/new', name: 'custom056_new', methods: ['GET'])]
    #[Route(path: '/list/custom057/new', name: 'custom057_new', methods: ['GET'])]
    #[Route(path: '/list/custom058/new', name: 'custom058_new', methods: ['GET'])]
    #[Route(path: '/list/custom059/new', name: 'custom059_new', methods: ['GET'])]
    #[Route(path: '/list/custom060/new', name: 'custom060_new', methods: ['GET'])]
    #[Route(path: '/list/custom061/new', name: 'custom061_new', methods: ['GET'])]
    #[Route(path: '/list/custom062/new', name: 'custom062_new', methods: ['GET'])]
    #[Route(path: '/list/custom063/new', name: 'custom063_new', methods: ['GET'])]
    #[Route(path: '/list/custom064/new', name: 'custom064_new', methods: ['GET'])]
    #[Route(path: '/list/custom065/new', name: 'custom065_new', methods: ['GET'])]
    #[Route(path: '/list/custom066/new', name: 'custom066_new', methods: ['GET'])]
    #[Route(path: '/list/custom067/new', name: 'custom067_new', methods: ['GET'])]
    #[Route(path: '/list/custom068/new', name: 'custom068_new', methods: ['GET'])]
    #[Route(path: '/list/custom069/new', name: 'custom069_new', methods: ['GET'])]
    #[Route(path: '/list/custom070/new', name: 'custom070_new', methods: ['GET'])]
    #[Route(path: '/list/custom071/new', name: 'custom071_new', methods: ['GET'])]
    #[Route(path: '/list/custom072/new', name: 'custom072_new', methods: ['GET'])]
    #[Route(path: '/list/custom073/new', name: 'custom073_new', methods: ['GET'])]
    #[Route(path: '/list/custom074/new', name: 'custom074_new', methods: ['GET'])]
    #[Route(path: '/list/custom075/new', name: 'custom075_new', methods: ['GET'])]
    #[Route(path: '/list/custom076/new', name: 'custom076_new', methods: ['GET'])]
    #[Route(path: '/list/custom077/new', name: 'custom077_new', methods: ['GET'])]
    #[Route(path: '/list/custom078/new', name: 'custom078_new', methods: ['GET'])]
    #[Route(path: '/list/custom079/new', name: 'custom079_new', methods: ['GET'])]
    #[Route(path: '/list/custom080/new', name: 'custom080_new', methods: ['GET'])]
    #[Route(path: '/list/custom081/new', name: 'custom081_new', methods: ['GET'])]
    #[Route(path: '/list/custom082/new', name: 'custom082_new', methods: ['GET'])]
    #[Route(path: '/list/custom083/new', name: 'custom083_new', methods: ['GET'])]
    #[Route(path: '/list/custom084/new', name: 'custom084_new', methods: ['GET'])]
    #[Route(path: '/list/custom085/new', name: 'custom085_new', methods: ['GET'])]
    #[Route(path: '/list/custom086/new', name: 'custom086_new', methods: ['GET'])]
    #[Route(path: '/list/custom087/new', name: 'custom087_new', methods: ['GET'])]
    #[Route(path: '/list/custom088/new', name: 'custom088_new', methods: ['GET'])]
    #[Route(path: '/list/custom089/new', name: 'custom089_new', methods: ['GET'])]
    #[Route(path: '/list/custom090/new', name: 'custom090_new', methods: ['GET'])]
    #[Route(path: '/list/custom091/new', name: 'custom091_new', methods: ['GET'])]
    #[Route(path: '/list/custom092/new', name: 'custom092_new', methods: ['GET'])]
    #[Route(path: '/list/custom093/new', name: 'custom093_new', methods: ['GET'])]
    #[Route(path: '/list/custom094/new', name: 'custom094_new', methods: ['GET'])]
    #[Route(path: '/list/custom095/new', name: 'custom095_new', methods: ['GET'])]
    #[Route(path: '/list/custom096/new', name: 'custom096_new', methods: ['GET'])]
    #[Route(path: '/list/custom097/new', name: 'custom097_new', methods: ['GET'])]
    #[Route(path: '/list/custom098/new', name: 'custom098_new', methods: ['GET'])]
    #[Route(path: '/list/custom099/new', name: 'custom099_new', methods: ['GET'])]
    #[Route(path: '/list/translational-tissue-processing-services/new', name: 'transrestissueprocessingservices_new', methods: ['GET'])]
    #[Route(path: '/list/translational-other-requested-services/new', name: 'transresotherrequestedservices_new', methods: ['GET'])]
    #[Route(path: '/list/translational-collaboration-labs/new', name: 'transrescolllabs_new', methods: ['GET'])]
    #[Route(path: '/list/translational-collaboration-divs/new', name: 'transrescolldivs_new', methods: ['GET'])]
    #[Route(path: '/list/translational-irb-approval-status/new', name: 'transresirbstatus_new', methods: ['GET'])]
    #[Route(path: '/list/translational-requester-group/new', name: 'transresrequestergroup_new', methods: ['GET'])]
    #[Route(path: '/list/transrescomptypes/new', name: 'transrescomptypes_new', methods: ['GET'])]
    #[Route(path: '/list/visastatus/new', name: 'visastatus_new', methods: ['GET'])]
    #[Route(path: '/list/resappstatuses/new', name: 'resappstatuses_new', methods: ['GET'])]
    #[Route(path: '/list/resappranks/new', name: 'resappranks_new', methods: ['GET'])]
    #[Route(path: '/list/resapplanguageproficiency/new', name: 'resapplanguageproficiency_new', methods: ['GET'])]
    #[Route(path: '/list/resappfitforprogram/new', name: 'resappfitforprogram_new', methods: ['GET'])]
    #[Route(path: '/list/resappvisastatus/new', name: 'resappvisastatus_new', methods: ['GET'])]
    #[Route(path: '/list/postsoph/new', name: 'postsoph_new', methods: ['GET'])]
    #[Route(path: '/list/resappapplyingresidencytrack/new', name: 'resappapplyingresidencytrack_new', methods: ['GET'])]
    #[Route(path: '/list/resapplearnarealist/new', name: 'resapplearnarealist_new', methods: ['GET'])]
    #[Route(path: '/list/resappspecificindividuallist/new', name: 'resappspecificindividuallist_new', methods: ['GET'])]
    #[Route(path: '/list/viewmodes/new', name: 'viewmodes_new', methods: ['GET'])]
    #[Route(path: '/list/transrespricetypes/new', name: 'transrespricetypes_new', methods: ['GET'])]
    #[Route(path: '/list/charttypes/new', name: 'charttypes_new', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/charttopics/new', name: 'charttopics_new', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/chartfilters/new', name: 'chartfilters_new', methods: ['GET'])]
    #[Route(path: '/list/charts/new', name: 'charts_new', methods: ['GET'])]
    #[Route(path: '/list/chartdatasources/new', name: 'chartdatasources_new', methods: ['GET'])]
    #[Route(path: '/list/chartupdatefrequencies/new', name: 'chartupdatefrequencies_new', methods: ['GET'])]
    #[Route(path: '/list/chartvisualizations/new', name: 'chartvisualizations_new', methods: ['GET'])]
    #[Route(path: '/list/vacreqholidays/new', name: 'vacreqholidays_new', methods: ['GET'])]
    #[Route(path: '/list/vacreqobservedholidays/new', name: 'vacreqobservedholidays_new', methods: ['GET'])]
    #[Route(path: '/list/authusergroup/new', name: 'authusergroup_new', methods: ['GET'])]
    #[Route(path: '/list/authservernetwork/new', name: 'authservernetwork_new', methods: ['GET'])]
    #[Route(path: '/list/authpartnerserver/new', name: 'authpartnerserver_new', methods: ['GET'])]
    #[Route(path: '/list/tenanturls/new', name: 'tenanturls_new', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/antibodycategorytag/new', name: 'antibodycategorytag_new', methods: ['GET'])]
    #[Route(path: '/list/transferstatus/new', name: 'transferstatus_new', methods: ['GET'])]
    #[Route(path: '/list/interfacetransfers/new', name: 'interfacetransfers_new', methods: ['GET'])]
    #[Route(path: '/list/antibodylabs/new', name: 'antibodylabs_new', methods: ['GET'])]
    #[Route(path: '/list/antibodypanels/new', name: 'antibodypanels_new', methods: ['GET'])]
    #[Route(path: '/list/samlconfig/new', name: 'samlconfig_new', methods: ['GET'])]
    #[Route(path: '/list/globalfellowshipspecialty/new', name: 'globalfellowshipspecialty_new', methods: ['GET'])]
    #[Route(path: '/list/trainingeligibility/new', name: 'trainingeligibility_new', methods: ['GET'])]
    #[Route(path: '/list/dutiescapability/new', name: 'dutiescapability_new', methods: ['GET'])]
    #[Route(path: '/list/phdfield/new', name: 'phdfield_new', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/ListForm/new.html.twig')]
    public function newAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        return $this->newList($request);
    }
    public function newList( $request, $pid=null ) {

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";

        if( $routeName == 'antibodies_new' ) {
            return $this->redirect( $this->generateUrl('translationalresearch_antibody_new') );
        }

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase,$request);

        $entityClass = $mapper['fullClassName'];    //"App\\OrderformBundle\\Entity\\".$mapper['className'];

        $entity = new $entityClass();

        $user = $this->getUser();
        $entity->setCreatedate(new \DateTime());
        $entity->setType('user-added');
        $entity->setCreator($user);

        if( $pid ) {
            //echo "pid=".$pid."<br>";
            $parentNMapper = $this->getParentName($mapper['className']);
            //$parent = $em->getRepository($parentNMapper['bundleName'].':'.$parentNMapper['className'])->find($pid);
            $parent = $em->getRepository($parentNMapper['fullClassName'])->find($pid);
            $entity->setParent($parent);
        }

        //get max orderinlist + 10
        //$query = $em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM '.$mapper['bundleName'].':'.$mapper['className'].' c');
        $query = $em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM '.$mapper['fullClassName'].' c');

        $nextorder = $query->getSingleResult()['maxorderinlist']+10;
        $entity->setOrderinlist($nextorder);

        //add visual info
        //$this->addVisualInfo($entity); //new

        $form = $this->createCreateForm($entity,$mapper,$pathbase,'new');

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase,
            'sitename' => $this->sitename,
            'cycle' => 'new'
        );
    }

    /**
     * Finds and displays a entity.
     *
     *
     *
     *
     *
     *
     *
     */
    #[Route(path: '/list-manager/{id}', name: 'platformlistmanager_show', methods: ['GET'])]
    #[Route(path: '/list/source-systems/{id}', name: 'sourcesystems_show', methods: ['GET'])]
    #[Route(path: '/list/roles/{id}', name: 'role_show', methods: ['GET'])]
    #[Route(path: '/list/institutions/{id}', name: 'institutions_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/states/{id}', name: 'states_show', methods: ['GET'])]
    #[Route(path: '/list/countries/{id}', name: 'countries_show', methods: ['GET'])]
    #[Route(path: '/list/board-certifications/{id}', name: 'boardcertifications_show', methods: ['GET'])]
    #[Route(path: '/list/employment-termination-reasons/{id}', name: 'employmentterminations_show', methods: ['GET'])]
    #[Route(path: '/list/event-log-event-types/{id}', name: 'loggereventtypes_show', methods: ['GET'])]
    #[Route(path: '/list/primary-public-user-id-types/{id}', name: 'usernametypes_show', methods: ['GET'])]
    #[Route(path: '/list/identifier-types/{id}', name: 'identifiers_show', methods: ['GET'])]
    #[Route(path: '/list/residency-tracks/{id}', name: 'residencytracks_show', methods: ['GET'])]
    #[Route(path: '/list/fellowship-types/{id}', name: 'fellowshiptypes_show', methods: ['GET'])]
    #[Route(path: '/list/location-types/{id}', name: 'locationtypes_show', methods: ['GET'])]
    #[Route(path: '/list/equipment/{id}', name: 'equipments_show', methods: ['GET'])]
    #[Route(path: '/list/equipment-types/{id}', name: 'equipmenttypes_show', methods: ['GET'])]
    #[Route(path: '/list/location-privacy-types/{id}', name: 'locationprivacy_show', methods: ['GET'])]
    #[Route(path: '/list/role-attributes/{id}', name: 'roleattributes_show', methods: ['GET'])]
    #[Route(path: '/list/buidlings/{id}', name: 'buildings_show', methods: ['GET'])]
    #[Route(path: '/list/rooms/{id}', name: 'rooms_show', methods: ['GET'])]
    #[Route(path: '/list/suites/{id}', name: 'suites_show', methods: ['GET'])]
    #[Route(path: '/list/floors/{id}', name: 'floors_show', methods: ['GET'])]
    #[Route(path: '/list/grants/{id}', name: 'grants_show', methods: ['GET'])]
    #[Route(path: '/list/mailboxes/{id}', name: 'mailboxes_show', methods: ['GET'])]
    #[Route(path: '/list/percent-effort/{id}', name: 'efforts_show', methods: ['GET'])]
    #[Route(path: '/list/administrative-titles/{id}', name: 'admintitles_show', methods: ['GET'])]
    #[Route(path: '/list/academic-appointment-titles/{id}', name: 'apptitles_show', methods: ['GET'])]
    #[Route(path: '/list/training-completion-reasons/{id}', name: 'completionreasons_show', methods: ['GET'])]
    #[Route(path: '/list/training-degrees/{id}', name: 'trainingdegrees_show', methods: ['GET'])]
    #[Route(path: '/list/training-majors/{id}', name: 'trainingmajors_show', methods: ['GET'])]
    #[Route(path: '/list/training-minors/{id}', name: 'trainingminors_show', methods: ['GET'])]
    #[Route(path: '/list/training-honors/{id}', name: 'traininghonors_show', methods: ['GET'])]
    #[Route(path: '/list/fellowship-titles/{id}', name: 'fellowshiptitles_show', methods: ['GET'])]
    #[Route(path: '/list/residency-specialties/{id}', name: 'residencyspecialtys_show', methods: ['GET'])]
    #[Route(path: '/list/fellowship-subspecialties/{id}', name: 'fellowshipsubspecialtys_show', methods: ['GET'])]
    #[Route(path: '/list/institution-types/{id}', name: 'institutiontypes_show', methods: ['GET'])]
    #[Route(path: '/list/document-types/{id}', name: 'documenttypes_show', methods: ['GET'])]
    #[Route(path: '/list/medical-titles/{id}', name: 'medicaltitles_show', methods: ['GET'])]
    #[Route(path: '/list/medical-specialties/{id}', name: 'medicalspecialties_show', methods: ['GET'])]
    #[Route(path: '/list/employment-types/{id}', name: 'employmenttypes_show', methods: ['GET'])]
    #[Route(path: '/list/grant-source-organizations/{id}', name: 'sourceorganizations_show', methods: ['GET'])]
    #[Route(path: '/list/languages/{id}', name: 'languages_show', methods: ['GET'])]
    #[Route(path: '/list/locales/{id}', name: 'locales_show', methods: ['GET'])]
    #[Route(path: '/list/ranks-of-importance/{id}', name: 'importances_show', methods: ['GET'])]
    #[Route(path: '/list/authorship-roles/{id}', name: 'authorshiproles_show', methods: ['GET'])]
    #[Route(path: '/list/lecture-venues/{id}', name: 'organizations_show', methods: ['GET'])]
    #[Route(path: '/list/cities/{id}', name: 'cities_show', methods: ['GET'])]
    #[Route(path: '/list/link-types/{id}', name: 'linktypes_show', methods: ['GET'])]
    #[Route(path: '/list/sexes/{id}', name: 'sexes_show', methods: ['GET'])]
    #[Route(path: '/list/position-types/{id}', name: 'positiontypes_show', methods: ['GET'])]
    #[Route(path: '/list/organizational-group-types/{id}', name: 'organizationalgrouptypes_show', methods: ['GET'])]
    #[Route(path: '/list/profile-comment-group-types/{id}', name: 'commentgrouptypes_show', methods: ['GET'])]
    #[Route(path: '/list/comment-types/{id}', name: 'commenttypes_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/user-wrappers/{id}', name: 'userwrappers_show', methods: ['GET'])]
    #[Route(path: '/list/spot-purposes/{id}', name: 'spotpurposes_show', methods: ['GET'])]
    #[Route(path: '/list/medical-license-statuses/{id}', name: 'medicalstatuses_show', methods: ['GET'])]
    #[Route(path: '/list/certifying-board-organizations/{id}', name: 'certifyingboardorganizations_show', methods: ['GET'])]
    #[Route(path: '/list/training-types/{id}', name: 'trainingtypes_show', methods: ['GET'])]
    #[Route(path: '/list/job-titles/{id}', name: 'joblists_show', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-statuses/{id}', name: 'fellappstatuses_show', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-ranks/{id}', name: 'fellappranks_show', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-language-proficiencies/{id}', name: 'fellapplanguageproficiency_show', methods: ['GET'])]
    #[Route(path: '/list/collaboration-types/{id}', name: 'collaborationtypes_show', methods: ['GET'])]
    #[Route(path: '/list/permissions/{id}', name: 'permission_show', methods: ['GET'])]
    #[Route(path: '/list/permission-objects/{id}', name: 'permissionobject_show', methods: ['GET'])]
    #[Route(path: '/list/permission-actions/{id}', name: 'permissionaction_show', methods: ['GET'])]
    #[Route(path: '/list/sites/{id}', name: 'sites_show', methods: ['GET'])]
    #[Route(path: '/list/event-object-types/{id}', name: 'eventobjecttypes_show', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-types/{id}', name: 'vacreqrequesttypes_show', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-floating-texts/{id}', name: 'vacreqfloatingtexts_show', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-floating-types/{id}', name: 'vacreqfloatingtypes_show', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-approval-types/{id}', name: 'vacreqapprovaltypes_show', methods: ['GET'])]
    #[Route(path: '/list/healthcare-provider-specialties/{id}', name: 'healthcareproviderspecialty_show', methods: ['GET'])]
    #[Route(path: '/list/healthcare-provider-initial-communications/{id}', name: 'healthcareprovidercommunication_show', methods: ['GET'])]
    #[Route(path: '/list/object-types/{id}', name: 'objecttypes_show', methods: ['GET'])]
    #[Route(path: '/list/form-nodes/{id}', name: 'formnodes_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/object-type-texts/{id}', name: 'objecttypetexts_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/blood-product-transfusions/{id}', name: 'bloodproducttransfusions_show', methods: ['GET'])]
    #[Route(path: '/list/transfusion-reaction-types/{id}', name: 'transfusionreactiontypes_show', methods: ['GET'])]
    #[Route(path: '/list/object-type-strings/{id}', name: 'objecttypestrings_show', methods: ['GET'])]
    #[Route(path: '/list/object-type-dropdowns/{id}', name: 'objecttypedropdowns_show', methods: ['GET'])]
    #[Route(path: '/list/blood-types/{id}', name: 'bloodtypes_show', methods: ['GET'])]
    #[Route(path: '/list/additional-communications/{id}', name: 'additionalcommunications_show', methods: ['GET'])]
    #[Route(path: '/list/transfusion-antibody-screen-results/{id}', name: 'transfusionantibodyscreenresults_show', methods: ['GET'])]
    #[Route(path: '/list/transfusion-crossmatch-results/{id}', name: 'transfusioncrossmatchresults_show', methods: ['GET'])]
    #[Route(path: '/list/transfusion-dat-results/{id}', name: 'transfusiondatresults_show', methods: ['GET'])]
    #[Route(path: '/list/transfusion-hemolysis-check-results/{id}', name: 'transfusionhemolysischeckresults_show', methods: ['GET'])]
    #[Route(path: '/list/object-type-datetimes/{id}', name: 'objecttypedatetimes_show', methods: ['GET'])]
    #[Route(path: '/list/complex-platelet-summary-antibodies/{id}', name: 'complexplateletsummaryantibodies_show', methods: ['GET'])]
    #[Route(path: '/list/cci-unit-platelet-count-default-values/{id}', name: 'cciunitplateletcountdefaultvalues_show', methods: ['GET'])]
    #[Route(path: '/list/cci-platelet-type-transfused/{id}', name: 'cciplatelettypetransfuseds_show', methods: ['GET'])]
    #[Route(path: '/list/platelet-transfusion-product-receiving/{id}', name: 'platelettransfusionproductreceivings_show', methods: ['GET'])]
    #[Route(path: '/list/transfusion-product-status/{id}', name: 'transfusionproductstatus_show', methods: ['GET'])]
    #[Route(path: '/list/week-days/{id}', name: 'weekdays_show', methods: ['GET'])]
    #[Route(path: '/list/months/{id}', name: 'months_show', methods: ['GET'])]
    #[Route(path: '/list/clerical-errors/{id}', name: 'clericalerrors_show', methods: ['GET'])]
    #[Route(path: '/list/lab-result-names/{id}', name: 'labresultnames_show', methods: ['GET'])]
    #[Route(path: '/list/lab-result-units-measures/{id}', name: 'labresultunitsmeasures_show', methods: ['GET'])]
    #[Route(path: '/list/lab-result-flags/{id}', name: 'labresultflags_show', methods: ['GET'])]
    #[Route(path: '/list/pathology-result-signatories/{id}', name: 'pathologyresultsignatories_show', methods: ['GET'])]
    #[Route(path: '/list/object-type-checkboxes/{id}', name: 'objecttypecheckboxs_show', methods: ['GET'])]
    #[Route(path: '/list/object-type-radio-buttons/{id}', name: 'objecttyperadiobuttons_show', methods: ['GET'])]
    #[Route(path: '/list/life-forms/{id}', name: 'lifeforms_show', methods: ['GET'])]
    #[Route(path: '/list/position-track-types/{id}', name: 'positiontracktypes_show', methods: ['GET'])]
    #[Route(path: '/list/translational-research-project-specialties_orig/{id}', name: 'transresprojectspecialties_show_orig', methods: ['GET'])]
    #[Route(path: '/list/translational-research-project-types/{id}', name: 'transresprojecttypes_show', methods: ['GET'])]
    #[Route(path: '/list/translational-research-request-category-types/{id}', name: 'transresrequestcategorytypes_show', methods: ['GET'])]
    #[Route(path: '/list/translational-irb-approval-types/{id}', name: 'transresirbapprovaltypes_show', methods: ['GET'])]
    #[Route(path: '/list/translational-business-purposes/{id}', name: 'transresbusinesspurposes_show', methods: ['GET'])]
    #[Route(path: '/list/translational-work-queue-types/{id}', name: 'workqueuetypes_show', methods: ['GET'])]
    #[Route(path: '/list/translational-orderable-status/{id}', name: 'orderablestatus_show', methods: ['GET'])]
    #[Route(path: '/list/antibodies/{id}', name: 'antibodies_show', methods: ['GET'])]
    #[Route(path: '/list/custom000/{id}', name: 'custom000_show', methods: ['GET'])]
    #[Route(path: '/list/custom001/{id}', name: 'custom001_show', methods: ['GET'])]
    #[Route(path: '/list/custom002/{id}', name: 'custom002_show', methods: ['GET'])]
    #[Route(path: '/list/custom003/{id}', name: 'custom003_show', methods: ['GET'])]
    #[Route(path: '/list/custom004/{id}', name: 'custom004_show', methods: ['GET'])]
    #[Route(path: '/list/custom005/{id}', name: 'custom005_show', methods: ['GET'])]
    #[Route(path: '/list/custom006/{id}', name: 'custom006_show', methods: ['GET'])]
    #[Route(path: '/list/custom007/{id}', name: 'custom007_show', methods: ['GET'])]
    #[Route(path: '/list/custom008/{id}', name: 'custom008_show', methods: ['GET'])]
    #[Route(path: '/list/custom009/{id}', name: 'custom009_show', methods: ['GET'])]
    #[Route(path: '/list/custom010/{id}', name: 'custom010_show', methods: ['GET'])]
    #[Route(path: '/list/custom011/{id}', name: 'custom011_show', methods: ['GET'])]
    #[Route(path: '/list/custom012/{id}', name: 'custom012_show', methods: ['GET'])]
    #[Route(path: '/list/custom013/{id}', name: 'custom013_show', methods: ['GET'])]
    #[Route(path: '/list/custom014/{id}', name: 'custom014_show', methods: ['GET'])]
    #[Route(path: '/list/custom015/{id}', name: 'custom015_show', methods: ['GET'])]
    #[Route(path: '/list/custom016/{id}', name: 'custom016_show', methods: ['GET'])]
    #[Route(path: '/list/custom017/{id}', name: 'custom017_show', methods: ['GET'])]
    #[Route(path: '/list/custom018/{id}', name: 'custom018_show', methods: ['GET'])]
    #[Route(path: '/list/custom019/{id}', name: 'custom019_show', methods: ['GET'])]
    #[Route(path: '/list/custom020/{id}', name: 'custom020_show', methods: ['GET'])]
    #[Route(path: '/list/custom021/{id}', name: 'custom021_show', methods: ['GET'])]
    #[Route(path: '/list/custom022/{id}', name: 'custom022_show', methods: ['GET'])]
    #[Route(path: '/list/custom023/{id}', name: 'custom023_show', methods: ['GET'])]
    #[Route(path: '/list/custom024/{id}', name: 'custom024_show', methods: ['GET'])]
    #[Route(path: '/list/custom025/{id}', name: 'custom025_show', methods: ['GET'])]
    #[Route(path: '/list/custom026/{id}', name: 'custom026_show', methods: ['GET'])]
    #[Route(path: '/list/custom027/{id}', name: 'custom027_show', methods: ['GET'])]
    #[Route(path: '/list/custom028/{id}', name: 'custom028_show', methods: ['GET'])]
    #[Route(path: '/list/custom029/{id}', name: 'custom029_show', methods: ['GET'])]
    #[Route(path: '/list/custom030/{id}', name: 'custom030_show', methods: ['GET'])]
    #[Route(path: '/list/custom031/{id}', name: 'custom031_show', methods: ['GET'])]
    #[Route(path: '/list/custom032/{id}', name: 'custom032_show', methods: ['GET'])]
    #[Route(path: '/list/custom033/{id}', name: 'custom033_show', methods: ['GET'])]
    #[Route(path: '/list/custom034/{id}', name: 'custom034_show', methods: ['GET'])]
    #[Route(path: '/list/custom035/{id}', name: 'custom035_show', methods: ['GET'])]
    #[Route(path: '/list/custom036/{id}', name: 'custom036_show', methods: ['GET'])]
    #[Route(path: '/list/custom037/{id}', name: 'custom037_show', methods: ['GET'])]
    #[Route(path: '/list/custom038/{id}', name: 'custom038_show', methods: ['GET'])]
    #[Route(path: '/list/custom039/{id}', name: 'custom039_show', methods: ['GET'])]
    #[Route(path: '/list/custom040/{id}', name: 'custom040_show', methods: ['GET'])]
    #[Route(path: '/list/custom041/{id}', name: 'custom041_show', methods: ['GET'])]
    #[Route(path: '/list/custom042/{id}', name: 'custom042_show', methods: ['GET'])]
    #[Route(path: '/list/custom043/{id}', name: 'custom043_show', methods: ['GET'])]
    #[Route(path: '/list/custom044/{id}', name: 'custom044_show', methods: ['GET'])]
    #[Route(path: '/list/custom045/{id}', name: 'custom045_show', methods: ['GET'])]
    #[Route(path: '/list/custom046/{id}', name: 'custom046_show', methods: ['GET'])]
    #[Route(path: '/list/custom047/{id}', name: 'custom047_show', methods: ['GET'])]
    #[Route(path: '/list/custom048/{id}', name: 'custom048_show', methods: ['GET'])]
    #[Route(path: '/list/custom049/{id}', name: 'custom049_show', methods: ['GET'])]
    #[Route(path: '/list/custom050/{id}', name: 'custom050_show', methods: ['GET'])]
    #[Route(path: '/list/custom051/{id}', name: 'custom051_show', methods: ['GET'])]
    #[Route(path: '/list/custom052/{id}', name: 'custom052_show', methods: ['GET'])]
    #[Route(path: '/list/custom053/{id}', name: 'custom053_show', methods: ['GET'])]
    #[Route(path: '/list/custom054/{id}', name: 'custom054_show', methods: ['GET'])]
    #[Route(path: '/list/custom055/{id}', name: 'custom055_show', methods: ['GET'])]
    #[Route(path: '/list/custom056/{id}', name: 'custom056_show', methods: ['GET'])]
    #[Route(path: '/list/custom057/{id}', name: 'custom057_show', methods: ['GET'])]
    #[Route(path: '/list/custom058/{id}', name: 'custom058_show', methods: ['GET'])]
    #[Route(path: '/list/custom059/{id}', name: 'custom059_show', methods: ['GET'])]
    #[Route(path: '/list/custom060/{id}', name: 'custom060_show', methods: ['GET'])]
    #[Route(path: '/list/custom061/{id}', name: 'custom061_show', methods: ['GET'])]
    #[Route(path: '/list/custom062/{id}', name: 'custom062_show', methods: ['GET'])]
    #[Route(path: '/list/custom063/{id}', name: 'custom063_show', methods: ['GET'])]
    #[Route(path: '/list/custom064/{id}', name: 'custom064_show', methods: ['GET'])]
    #[Route(path: '/list/custom065/{id}', name: 'custom065_show', methods: ['GET'])]
    #[Route(path: '/list/custom066/{id}', name: 'custom066_show', methods: ['GET'])]
    #[Route(path: '/list/custom067/{id}', name: 'custom067_show', methods: ['GET'])]
    #[Route(path: '/list/custom068/{id}', name: 'custom068_show', methods: ['GET'])]
    #[Route(path: '/list/custom069/{id}', name: 'custom069_show', methods: ['GET'])]
    #[Route(path: '/list/custom070/{id}', name: 'custom070_show', methods: ['GET'])]
    #[Route(path: '/list/custom071/{id}', name: 'custom071_show', methods: ['GET'])]
    #[Route(path: '/list/custom072/{id}', name: 'custom072_show', methods: ['GET'])]
    #[Route(path: '/list/custom073/{id}', name: 'custom073_show', methods: ['GET'])]
    #[Route(path: '/list/custom074/{id}', name: 'custom074_show', methods: ['GET'])]
    #[Route(path: '/list/custom075/{id}', name: 'custom075_show', methods: ['GET'])]
    #[Route(path: '/list/custom076/{id}', name: 'custom076_show', methods: ['GET'])]
    #[Route(path: '/list/custom077/{id}', name: 'custom077_show', methods: ['GET'])]
    #[Route(path: '/list/custom078/{id}', name: 'custom078_show', methods: ['GET'])]
    #[Route(path: '/list/custom079/{id}', name: 'custom079_show', methods: ['GET'])]
    #[Route(path: '/list/custom080/{id}', name: 'custom080_show', methods: ['GET'])]
    #[Route(path: '/list/custom081/{id}', name: 'custom081_show', methods: ['GET'])]
    #[Route(path: '/list/custom082/{id}', name: 'custom082_show', methods: ['GET'])]
    #[Route(path: '/list/custom083/{id}', name: 'custom083_show', methods: ['GET'])]
    #[Route(path: '/list/custom084/{id}', name: 'custom084_show', methods: ['GET'])]
    #[Route(path: '/list/custom085/{id}', name: 'custom085_show', methods: ['GET'])]
    #[Route(path: '/list/custom086/{id}', name: 'custom086_show', methods: ['GET'])]
    #[Route(path: '/list/custom087/{id}', name: 'custom087_show', methods: ['GET'])]
    #[Route(path: '/list/custom088/{id}', name: 'custom088_show', methods: ['GET'])]
    #[Route(path: '/list/custom089/{id}', name: 'custom089_show', methods: ['GET'])]
    #[Route(path: '/list/custom090/{id}', name: 'custom090_show', methods: ['GET'])]
    #[Route(path: '/list/custom091/{id}', name: 'custom091_show', methods: ['GET'])]
    #[Route(path: '/list/custom092/{id}', name: 'custom092_show', methods: ['GET'])]
    #[Route(path: '/list/custom093/{id}', name: 'custom093_show', methods: ['GET'])]
    #[Route(path: '/list/custom094/{id}', name: 'custom094_show', methods: ['GET'])]
    #[Route(path: '/list/custom095/{id}', name: 'custom095_show', methods: ['GET'])]
    #[Route(path: '/list/custom096/{id}', name: 'custom096_show', methods: ['GET'])]
    #[Route(path: '/list/custom097/{id}', name: 'custom097_show', methods: ['GET'])]
    #[Route(path: '/list/custom098/{id}', name: 'custom098_show', methods: ['GET'])]
    #[Route(path: '/list/custom099/{id}', name: 'custom099_show', methods: ['GET'])]
    #[Route(path: '/list/translational-tissue-processing-services/{id}', name: 'transrestissueprocessingservices_show', methods: ['GET'])]
    #[Route(path: '/list/translational-other-requested-services/{id}', name: 'transresotherrequestedservices_show', methods: ['GET'])]
    #[Route(path: '/list/translational-collaboration-labs/{id}', name: 'transrescolllabs_show', methods: ['GET'])]
    #[Route(path: '/list/translational-collaboration-divs/{id}', name: 'transrescolldivs_show', methods: ['GET'])]
    #[Route(path: '/list/translational-irb-approval-status/{id}', name: 'transresirbstatus_show', methods: ['GET'])]
    #[Route(path: '/list/translational-requester-group/{id}', name: 'transresrequestergroup_show', methods: ['GET'])]
    #[Route(path: '/list/transrescomptypes/{id}', name: 'transrescomptypes_show', methods: ['GET'])]
    #[Route(path: '/list/visastatus/{id}', name: 'visastatus_show', methods: ['GET'])]
    #[Route(path: '/list/resappstatuses/{id}', name: 'resappstatuses_show', methods: ['GET'])]
    #[Route(path: '/list/resappranks/{id}', name: 'resappranks_show', methods: ['GET'])]
    #[Route(path: '/list/resapplanguageproficiency/{id}', name: 'resapplanguageproficiency_show', methods: ['GET'])]
    #[Route(path: '/list/resappfitforprogram/{id}', name: 'resappfitforprogram_show', methods: ['GET'])]
    #[Route(path: '/list/resappvisastatus/{id}', name: 'resappvisastatus_show', methods: ['GET'])]
    #[Route(path: '/list/postsoph/{id}', name: 'postsoph_show', methods: ['GET'])]
    #[Route(path: '/list/resappapplyingresidencytrack/{id}', name: 'resappapplyingresidencytrack_show', methods: ['GET'])]
    #[Route(path: '/list/resapplearnarealist/{id}', name: 'resapplearnarealist_show', methods: ['GET'])]
    #[Route(path: '/list/resappspecificindividuallist/{id}', name: 'resappspecificindividuallist_show', methods: ['GET'])]
    #[Route(path: '/list/viewmodes/{id}', name: 'viewmodes_show', methods: ['GET'])]
    #[Route(path: '/list/transrespricetypes/{id}', name: 'transrespricetypes_show', methods: ['GET'])]
    #[Route(path: '/list/charttypes/{id}', name: 'charttypes_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/charttopics/{id}', name: 'charttopics_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/chartfilters/{id}', name: 'chartfilters_show', methods: ['GET'])]
    #[Route(path: '/list/charts/{id}', name: 'charts_show', methods: ['GET'])]
    #[Route(path: '/list/chartdatasources/{id}', name: 'chartdatasources_show', methods: ['GET'])]
    #[Route(path: '/list/chartupdatefrequencies/{id}', name: 'chartupdatefrequencies_show', methods: ['GET'])]
    #[Route(path: '/list/chartvisualizations/{id}', name: 'chartvisualizations_show', methods: ['GET'])]
    #[Route(path: '/list/vacreqholidays/{id}', name: 'vacreqholidays_show', methods: ['GET'])]
    #[Route(path: '/list/vacreqobservedholidays/{id}', name: 'vacreqobservedholidays_show', methods: ['GET'])]
    #[Route(path: '/list/authusergroup/{id}', name: 'authusergroup_show', methods: ['GET'])]
    #[Route(path: '/list/authservernetwork/{id}', name: 'authservernetwork_show', methods: ['GET'])]
    #[Route(path: '/list/authpartnerserver/{id}', name: 'authpartnerserver_show', methods: ['GET'])]
    #[Route(path: '/list/tenanturls/{id}', name: 'tenanturls_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/antibodycategorytag/{id}', name: 'antibodycategorytag_show', methods: ['GET'])]
    #[Route(path: '/list/transferstatus/{id}', name: 'transferstatus_show', methods: ['GET'])]
    #[Route(path: '/list/interfacetransfers/{id}', name: 'interfacetransfers_show', methods: ['GET'])]
    #[Route(path: '/list/antibodylabs/{id}', name: 'antibodylabs_show', methods: ['GET'])]
    #[Route(path: '/list/antibodypanels/{id}', name: 'antibodypanels_show', methods: ['GET'])]
    #[Route(path: '/list/samlconfig/{id}', name: 'samlconfig_show', methods: ['GET'])]
    #[Route(path: '/list/globalfellowshipspecialty/{id}', name: 'globalfellowshipspecialty_show', methods: ['GET'])]
    #[Route(path: '/list/trainingeligibility/{id}', name: 'trainingeligibility_show', methods: ['GET'])]
    #[Route(path: '/list/dutiescapability/{id}', name: 'dutiescapability_show', methods: ['GET'])]
    #[Route(path: '/list/phdfield/{id}', name: 'phdfield_show', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/ListForm/show.html.twig')]
    public function showAction(Request $request,$id)
    {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $showEditBtn = false;
        if( $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $showEditBtn = true;
        }

        return $this->showList($request,$id,$showEditBtn);
    }
    public function showList( $request, $id, $showEditBtn=false ) {

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";
        //exit('show');
        //exit("showEditBtn=".$showEditBtn);

        $em = $this->getDoctrine()->getManager();

        $mapper = $this->classListMapper($pathbase,$request);

        //$entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);
        $entity = $em->getRepository($mapper['fullClassName'])->find($id);

        //echo "entity ID=".$entity->getId()."; name=".$entity->getName()."<br>";
        $form = $this->createEditForm($entity,$mapper,$pathbase,'edit',true);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
        }

        //$deleteForm = $this->createDeleteForm($id,$pathbase);

        return array(
            'entity' => $entity,
            'edit_form' => $form->createView(),
            'delete_form' => null,  //$deleteForm->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase,
            'sitename' => $this->sitename,
            'showEditBtn' => $showEditBtn,
            'cycle' => 'show'
        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     *
     *
     *
     *
     *
     *
     */
    #[Route(path: '/list-manager/{id}/edit', name: 'platformlistmanager_edit', methods: ['GET'])]
    #[Route(path: '/list/source-systems/{id}/edit', name: 'sourcesystems_edit', methods: ['GET'])]
    #[Route(path: '/list/roles/{id}/edit', name: 'role_edit', methods: ['GET'])]
    #[Route(path: '/list/institutions/{id}/edit', name: 'institutions_edit', methods: ['GET'])]
    #[Route(path: '/list/states/{id}/edit', name: 'states_edit', methods: ['GET'])]
    #[Route(path: '/list/countries/{id}/edit', name: 'countries_edit', methods: ['GET'])]
    #[Route(path: '/list/board-certifications/{id}/edit', name: 'boardcertifications_edit', methods: ['GET'])]
    #[Route(path: '/list/employment-termination-reasons/{id}/edit', name: 'employmentterminations_edit', methods: ['GET'])]
    #[Route(path: '/list/event-log-event-types/{id}/edit', name: 'loggereventtypes_edit', methods: ['GET'])]
    #[Route(path: '/list/primary-public-user-id-types/{id}/edit', name: 'usernametypes_edit', methods: ['GET'])]
    #[Route(path: '/list/identifier-types/{id}/edit', name: 'identifiers_edit', methods: ['GET'])]
    #[Route(path: '/list/residency-tracks/{id}/edit', name: 'residencytracks_edit', methods: ['GET'])]
    #[Route(path: '/list/fellowship-types/{id}/edit', name: 'fellowshiptypes_edit', methods: ['GET'])]
    #[Route(path: '/list/location-types/{id}/edit', name: 'locationtypes_edit', methods: ['GET'])]
    #[Route(path: '/list/equipment/{id}/edit', name: 'equipments_edit', methods: ['GET'])]
    #[Route(path: '/list/equipment-types/{id}/edit', name: 'equipmenttypes_edit', methods: ['GET'])]
    #[Route(path: '/list/location-privacy-types/{id}/edit', name: 'locationprivacy_edit', methods: ['GET'])]
    #[Route(path: '/list/role-attributes/{id}/edit', name: 'roleattributes_edit', methods: ['GET'])]
    #[Route(path: '/list/buidlings/{id}/edit', name: 'buildings_edit', methods: ['GET'])]
    #[Route(path: '/list/rooms/{id}/edit', name: 'rooms_edit', methods: ['GET'])]
    #[Route(path: '/list/suites/{id}/edit', name: 'suites_edit', methods: ['GET'])]
    #[Route(path: '/list/floors/{id}/edit', name: 'floors_edit', methods: ['GET'])]
    #[Route(path: '/list/grants/{id}/edit', name: 'grants_edit', methods: ['GET'])]
    #[Route(path: '/list/mailboxes/{id}/edit', name: 'mailboxes_edit', methods: ['GET'])]
    #[Route(path: '/list/percent-effort/{id}/edit', name: 'efforts_edit', methods: ['GET'])]
    #[Route(path: '/list/administrative-titles/{id}/edit', name: 'admintitles_edit', methods: ['GET'])]
    #[Route(path: '/list/academic-appointment-titles/{id}/edit', name: 'apptitles_edit', methods: ['GET'])]
    #[Route(path: '/list/training-completion-reasons/{id}/edit', name: 'completionreasons_edit', methods: ['GET'])]
    #[Route(path: '/list/training-degrees/{id}/edit', name: 'trainingdegrees_edit', methods: ['GET'])]
    #[Route(path: '/list/training-majors/{id}/edit', name: 'trainingmajors_edit', methods: ['GET'])]
    #[Route(path: '/list/training-minors/{id}/edit', name: 'trainingminors_edit', methods: ['GET'])]
    #[Route(path: '/list/training-honors/{id}/edit', name: 'traininghonors_edit', methods: ['GET'])]
    #[Route(path: '/list/fellowship-titles/{id}/edit', name: 'fellowshiptitles_edit', methods: ['GET'])]
    #[Route(path: '/list/residency-specialties/{id}/edit', name: 'residencyspecialtys_edit', methods: ['GET'])]
    #[Route(path: '/list/fellowship-subspecialties/{id}/edit', name: 'fellowshipsubspecialtys_edit', methods: ['GET'])]
    #[Route(path: '/list/institution-types/{id}/edit', name: 'institutiontypes_edit', methods: ['GET'])]
    #[Route(path: '/list/document-types/{id}/edit', name: 'documenttypes_edit', methods: ['GET'])]
    #[Route(path: '/list/medical-titles/{id}/edit', name: 'medicaltitles_edit', methods: ['GET'])]
    #[Route(path: '/list/medical-specialties/{id}/edit', name: 'medicalspecialties_edit', methods: ['GET'])]
    #[Route(path: '/list/employment-types/{id}/edit', name: 'employmenttypes_edit', methods: ['GET'])]
    #[Route(path: '/list/grant-source-organizations/{id}/edit', name: 'sourceorganizations_edit', methods: ['GET'])]
    #[Route(path: '/list/languages/{id}/edit', name: 'languages_edit', methods: ['GET'])]
    #[Route(path: '/list/locales/{id}/edit', name: 'locales_edit', methods: ['GET'])]
    #[Route(path: '/list/ranks-of-importance/{id}/edit', name: 'importances_edit', methods: ['GET'])]
    #[Route(path: '/list/authorship-roles/{id}/edit', name: 'authorshiproles_edit', methods: ['GET'])]
    #[Route(path: '/list/lecture-venues/{id}/edit', name: 'organizations_edit', methods: ['GET'])]
    #[Route(path: '/list/cities/{id}/edit', name: 'cities_edit', methods: ['GET'])]
    #[Route(path: '/list/link-types/{id}/edit', name: 'linktypes_edit', methods: ['GET'])]
    #[Route(path: '/list/sexes/{id}/edit', name: 'sexes_edit', methods: ['GET'])]
    #[Route(path: '/list/position-types/{id}/edit', name: 'positiontypes_edit', methods: ['GET'])]
    #[Route(path: '/list/organizational-group-types/{id}/edit', name: 'organizationalgrouptypes_edit', methods: ['GET'])]
    #[Route(path: '/list/profile-comment-group-types/{id}/edit', name: 'commentgrouptypes_edit', methods: ['GET'])]
    #[Route(path: '/list/comment-types/{id}/edit', name: 'commenttypes_edit', methods: ['GET'])]
    #[Route(path: '/list/user-wrappers/{id}/edit', name: 'userwrappers_edit', methods: ['GET'])]
    #[Route(path: '/list/spot-purposes/{id}/edit', name: 'spotpurposes_edit', methods: ['GET'])]
    #[Route(path: '/list/medical-license-statuses/{id}/edit', name: 'medicalstatuses_edit', methods: ['GET'])]
    #[Route(path: '/list/certifying-board-organizations/{id}/edit', name: 'certifyingboardorganizations_edit', methods: ['GET'])]
    #[Route(path: '/list/training-types/{id}/edit', name: 'trainingtypes_edit', methods: ['GET'])]
    #[Route(path: '/list/job-titles/{id}/edit', name: 'joblists_edit', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-statuses/{id}/edit', name: 'fellappstatuses_edit', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-ranks/{id}/edit', name: 'fellappranks_edit', methods: ['GET'])]
    #[Route(path: '/list/fellowship-application-language-proficiencies/{id}/edit', name: 'fellapplanguageproficiency_edit', methods: ['GET'])]
    #[Route(path: '/list/collaboration-types/{id}/edit', name: 'collaborationtypes_edit', methods: ['GET'])]
    #[Route(path: '/list/permissions/{id}/edit', name: 'permission_edit', methods: ['GET'])]
    #[Route(path: '/list/permission-objects/{id}/edit', name: 'permissionobject_edit', methods: ['GET'])]
    #[Route(path: '/list/permission-actions/{id}/edit', name: 'permissionaction_edit', methods: ['GET'])]
    #[Route(path: '/list/sites/{id}/edit', name: 'sites_edit', methods: ['GET'])]
    #[Route(path: '/list/event-object-types/{id}/edit', name: 'eventobjecttypes_edit', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-types/{id}/edit', name: 'vacreqrequesttypes_edit', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-floating-texts/{id}/edit', name: 'vacreqfloatingtexts_edit', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-floating-types/{id}/edit', name: 'vacreqfloatingtypes_edit', methods: ['GET'])]
    #[Route(path: '/list/time-away-request-approval-types/{id}/edit', name: 'vacreqapprovaltypes_edit', methods: ['GET'])]
    #[Route(path: '/list/healthcare-provider-specialties/{id}/edit', name: 'healthcareproviderspecialty_edit', methods: ['GET'])]
    #[Route(path: '/list/healthcare-provider-initial-communications/{id}/edit', name: 'healthcareprovidercommunication_edit', methods: ['GET'])]
    #[Route(path: '/list/object-types/{id}/edit', name: 'objecttypes_edit', methods: ['GET'])]
    #[Route(path: '/list/form-nodes/{id}/edit', name: 'formnodes_edit', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/object-type-texts/{id}/edit', name: 'objecttypetexts_edit', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/blood-product-transfusions/{id}/edit', name: 'bloodproducttransfusions_edit', methods: ['GET'])]
    #[Route(path: '/list/transfusion-reaction-types/{id}/edit', name: 'transfusionreactiontypes_edit', methods: ['GET'])]
    #[Route(path: '/list/object-type-strings/{id}/edit', name: 'objecttypestrings_edit', methods: ['GET'])]
    #[Route(path: '/list/object-type-dropdowns/{id}/edit', name: 'objecttypedropdowns_edit', methods: ['GET'])]
    #[Route(path: '/list/blood-types/{id}/edit', name: 'bloodtypes_edit', methods: ['GET'])]
    #[Route(path: '/list/additional-communications/{id}/edit', name: 'additionalcommunications_edit', methods: ['GET'])]
    #[Route(path: '/list/transfusion-antibody-screen-results/{id}/edit', name: 'transfusionantibodyscreenresults_edit', methods: ['GET'])]
    #[Route(path: '/list/transfusion-crossmatch-results/{id}/edit', name: 'transfusioncrossmatchresults_edit', methods: ['GET'])]
    #[Route(path: '/list/transfusion-dat-results/{id}/edit', name: 'transfusiondatresults_edit', methods: ['GET'])]
    #[Route(path: '/list/transfusion-hemolysis-check-results/{id}/edit', name: 'transfusionhemolysischeckresults_edit', methods: ['GET'])]
    #[Route(path: '/list/object-type-datetimes/{id}/edit', name: 'objecttypedatetimes_edit', methods: ['GET'])]
    #[Route(path: '/list/complex-platelet-summary-antibodies/{id}/edit', name: 'complexplateletsummaryantibodies_edit', methods: ['GET'])]
    #[Route(path: '/list/cci-unit-platelet-count-default-values/{id}/edit', name: 'cciunitplateletcountdefaultvalues_edit', methods: ['GET'])]
    #[Route(path: '/list/cci-platelet-type-transfused/{id}/edit', name: 'cciplatelettypetransfuseds_edit', methods: ['GET'])]
    #[Route(path: '/list/platelet-transfusion-product-receiving/{id}/edit', name: 'platelettransfusionproductreceivings_edit', methods: ['GET'])]
    #[Route(path: '/list/transfusion-product-status/{id}/edit', name: 'transfusionproductstatus_edit', methods: ['GET'])]
    #[Route(path: '/list/week-days/{id}/edit', name: 'weekdays_edit', methods: ['GET'])]
    #[Route(path: '/list/months/{id}/edit', name: 'months_edit', methods: ['GET'])]
    #[Route(path: '/list/clerical-errors/{id}/edit', name: 'clericalerrors_edit', methods: ['GET'])]
    #[Route(path: '/list/lab-result-names/{id}/edit', name: 'labresultnames_edit', methods: ['GET'])]
    #[Route(path: '/list/lab-result-units-measures/{id}/edit', name: 'labresultunitsmeasures_edit', methods: ['GET'])]
    #[Route(path: '/list/lab-result-flags/{id}/edit', name: 'labresultflags_edit', methods: ['GET'])]
    #[Route(path: '/list/pathology-result-signatories/{id}/edit', name: 'pathologyresultsignatories_edit', methods: ['GET'])]
    #[Route(path: '/list/object-type-checkboxes/{id}/edit', name: 'objecttypecheckboxs_edit', methods: ['GET'])]
    #[Route(path: '/list/object-type-radio-buttons/{id}/edit', name: 'objecttyperadiobuttons_edit', methods: ['GET'])]
    #[Route(path: '/list/life-forms/{id}/edit', name: 'lifeforms_edit', methods: ['GET'])]
    #[Route(path: '/list/position-track-types/{id}/edit', name: 'positiontracktypes_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-research-project-specialties-orig/{id}/edit', name: 'transresprojectspecialties_edit-orig', methods: ['GET'])]
    #[Route(path: '/list/translational-research-project-types/{id}/edit', name: 'transresprojecttypes_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-research-request-category-types/{id}/edit', name: 'transresrequestcategorytypes_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-irb-approval-types/{id}/edit', name: 'transresirbapprovaltypes_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-business-purposes/{id}/edit', name: 'transresbusinesspurposes_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-work-queue-types/{id}/edit', name: 'workqueuetypes_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-orderable-status/{id}/edit', name: 'orderablestatus_edit', methods: ['GET'])]
    #[Route(path: '/list/antibodies/{id}/edit', name: 'antibodies_edit', methods: ['GET'])]
    #[Route(path: '/list/custom000/{id}/edit', name: 'custom000_edit', methods: ['GET'])]
    #[Route(path: '/list/custom001/{id}/edit', name: 'custom001_edit', methods: ['GET'])]
    #[Route(path: '/list/custom002/{id}/edit', name: 'custom002_edit', methods: ['GET'])]
    #[Route(path: '/list/custom003/{id}/edit', name: 'custom003_edit', methods: ['GET'])]
    #[Route(path: '/list/custom004/{id}/edit', name: 'custom004_edit', methods: ['GET'])]
    #[Route(path: '/list/custom005/{id}/edit', name: 'custom005_edit', methods: ['GET'])]
    #[Route(path: '/list/custom006/{id}/edit', name: 'custom006_edit', methods: ['GET'])]
    #[Route(path: '/list/custom007/{id}/edit', name: 'custom007_edit', methods: ['GET'])]
    #[Route(path: '/list/custom008/{id}/edit', name: 'custom008_edit', methods: ['GET'])]
    #[Route(path: '/list/custom009/{id}/edit', name: 'custom009_edit', methods: ['GET'])]
    #[Route(path: '/list/custom010/{id}/edit', name: 'custom010_edit', methods: ['GET'])]
    #[Route(path: '/list/custom011/{id}/edit', name: 'custom011_edit', methods: ['GET'])]
    #[Route(path: '/list/custom012/{id}/edit', name: 'custom012_edit', methods: ['GET'])]
    #[Route(path: '/list/custom013/{id}/edit', name: 'custom013_edit', methods: ['GET'])]
    #[Route(path: '/list/custom014/{id}/edit', name: 'custom014_edit', methods: ['GET'])]
    #[Route(path: '/list/custom015/{id}/edit', name: 'custom015_edit', methods: ['GET'])]
    #[Route(path: '/list/custom016/{id}/edit', name: 'custom016_edit', methods: ['GET'])]
    #[Route(path: '/list/custom017/{id}/edit', name: 'custom017_edit', methods: ['GET'])]
    #[Route(path: '/list/custom018/{id}/edit', name: 'custom018_edit', methods: ['GET'])]
    #[Route(path: '/list/custom019/{id}/edit', name: 'custom019_edit', methods: ['GET'])]
    #[Route(path: '/list/custom020/{id}/edit', name: 'custom020_edit', methods: ['GET'])]
    #[Route(path: '/list/custom021/{id}/edit', name: 'custom021_edit', methods: ['GET'])]
    #[Route(path: '/list/custom022/{id}/edit', name: 'custom022_edit', methods: ['GET'])]
    #[Route(path: '/list/custom023/{id}/edit', name: 'custom023_edit', methods: ['GET'])]
    #[Route(path: '/list/custom024/{id}/edit', name: 'custom024_edit', methods: ['GET'])]
    #[Route(path: '/list/custom025/{id}/edit', name: 'custom025_edit', methods: ['GET'])]
    #[Route(path: '/list/custom026/{id}/edit', name: 'custom026_edit', methods: ['GET'])]
    #[Route(path: '/list/custom027/{id}/edit', name: 'custom027_edit', methods: ['GET'])]
    #[Route(path: '/list/custom028/{id}/edit', name: 'custom028_edit', methods: ['GET'])]
    #[Route(path: '/list/custom029/{id}/edit', name: 'custom029_edit', methods: ['GET'])]
    #[Route(path: '/list/custom030/{id}/edit', name: 'custom030_edit', methods: ['GET'])]
    #[Route(path: '/list/custom031/{id}/edit', name: 'custom031_edit', methods: ['GET'])]
    #[Route(path: '/list/custom032/{id}/edit', name: 'custom032_edit', methods: ['GET'])]
    #[Route(path: '/list/custom033/{id}/edit', name: 'custom033_edit', methods: ['GET'])]
    #[Route(path: '/list/custom034/{id}/edit', name: 'custom034_edit', methods: ['GET'])]
    #[Route(path: '/list/custom035/{id}/edit', name: 'custom035_edit', methods: ['GET'])]
    #[Route(path: '/list/custom036/{id}/edit', name: 'custom036_edit', methods: ['GET'])]
    #[Route(path: '/list/custom037/{id}/edit', name: 'custom037_edit', methods: ['GET'])]
    #[Route(path: '/list/custom038/{id}/edit', name: 'custom038_edit', methods: ['GET'])]
    #[Route(path: '/list/custom039/{id}/edit', name: 'custom039_edit', methods: ['GET'])]
    #[Route(path: '/list/custom040/{id}/edit', name: 'custom040_edit', methods: ['GET'])]
    #[Route(path: '/list/custom041/{id}/edit', name: 'custom041_edit', methods: ['GET'])]
    #[Route(path: '/list/custom042/{id}/edit', name: 'custom042_edit', methods: ['GET'])]
    #[Route(path: '/list/custom043/{id}/edit', name: 'custom043_edit', methods: ['GET'])]
    #[Route(path: '/list/custom044/{id}/edit', name: 'custom044_edit', methods: ['GET'])]
    #[Route(path: '/list/custom045/{id}/edit', name: 'custom045_edit', methods: ['GET'])]
    #[Route(path: '/list/custom046/{id}/edit', name: 'custom046_edit', methods: ['GET'])]
    #[Route(path: '/list/custom047/{id}/edit', name: 'custom047_edit', methods: ['GET'])]
    #[Route(path: '/list/custom048/{id}/edit', name: 'custom048_edit', methods: ['GET'])]
    #[Route(path: '/list/custom049/{id}/edit', name: 'custom049_edit', methods: ['GET'])]
    #[Route(path: '/list/custom050/{id}/edit', name: 'custom050_edit', methods: ['GET'])]
    #[Route(path: '/list/custom051/{id}/edit', name: 'custom051_edit', methods: ['GET'])]
    #[Route(path: '/list/custom052/{id}/edit', name: 'custom052_edit', methods: ['GET'])]
    #[Route(path: '/list/custom053/{id}/edit', name: 'custom053_edit', methods: ['GET'])]
    #[Route(path: '/list/custom054/{id}/edit', name: 'custom054_edit', methods: ['GET'])]
    #[Route(path: '/list/custom055/{id}/edit', name: 'custom055_edit', methods: ['GET'])]
    #[Route(path: '/list/custom056/{id}/edit', name: 'custom056_edit', methods: ['GET'])]
    #[Route(path: '/list/custom057/{id}/edit', name: 'custom057_edit', methods: ['GET'])]
    #[Route(path: '/list/custom058/{id}/edit', name: 'custom058_edit', methods: ['GET'])]
    #[Route(path: '/list/custom059/{id}/edit', name: 'custom059_edit', methods: ['GET'])]
    #[Route(path: '/list/custom060/{id}/edit', name: 'custom060_edit', methods: ['GET'])]
    #[Route(path: '/list/custom061/{id}/edit', name: 'custom061_edit', methods: ['GET'])]
    #[Route(path: '/list/custom062/{id}/edit', name: 'custom062_edit', methods: ['GET'])]
    #[Route(path: '/list/custom063/{id}/edit', name: 'custom063_edit', methods: ['GET'])]
    #[Route(path: '/list/custom064/{id}/edit', name: 'custom064_edit', methods: ['GET'])]
    #[Route(path: '/list/custom065/{id}/edit', name: 'custom065_edit', methods: ['GET'])]
    #[Route(path: '/list/custom066/{id}/edit', name: 'custom066_edit', methods: ['GET'])]
    #[Route(path: '/list/custom067/{id}/edit', name: 'custom067_edit', methods: ['GET'])]
    #[Route(path: '/list/custom068/{id}/edit', name: 'custom068_edit', methods: ['GET'])]
    #[Route(path: '/list/custom069/{id}/edit', name: 'custom069_edit', methods: ['GET'])]
    #[Route(path: '/list/custom070/{id}/edit', name: 'custom070_edit', methods: ['GET'])]
    #[Route(path: '/list/custom071/{id}/edit', name: 'custom071_edit', methods: ['GET'])]
    #[Route(path: '/list/custom072/{id}/edit', name: 'custom072_edit', methods: ['GET'])]
    #[Route(path: '/list/custom073/{id}/edit', name: 'custom073_edit', methods: ['GET'])]
    #[Route(path: '/list/custom074/{id}/edit', name: 'custom074_edit', methods: ['GET'])]
    #[Route(path: '/list/custom075/{id}/edit', name: 'custom075_edit', methods: ['GET'])]
    #[Route(path: '/list/custom076/{id}/edit', name: 'custom076_edit', methods: ['GET'])]
    #[Route(path: '/list/custom077/{id}/edit', name: 'custom077_edit', methods: ['GET'])]
    #[Route(path: '/list/custom078/{id}/edit', name: 'custom078_edit', methods: ['GET'])]
    #[Route(path: '/list/custom079/{id}/edit', name: 'custom079_edit', methods: ['GET'])]
    #[Route(path: '/list/custom080/{id}/edit', name: 'custom080_edit', methods: ['GET'])]
    #[Route(path: '/list/custom081/{id}/edit', name: 'custom081_edit', methods: ['GET'])]
    #[Route(path: '/list/custom082/{id}/edit', name: 'custom082_edit', methods: ['GET'])]
    #[Route(path: '/list/custom083/{id}/edit', name: 'custom083_edit', methods: ['GET'])]
    #[Route(path: '/list/custom084/{id}/edit', name: 'custom084_edit', methods: ['GET'])]
    #[Route(path: '/list/custom085/{id}/edit', name: 'custom085_edit', methods: ['GET'])]
    #[Route(path: '/list/custom086/{id}/edit', name: 'custom086_edit', methods: ['GET'])]
    #[Route(path: '/list/custom087/{id}/edit', name: 'custom087_edit', methods: ['GET'])]
    #[Route(path: '/list/custom088/{id}/edit', name: 'custom088_edit', methods: ['GET'])]
    #[Route(path: '/list/custom089/{id}/edit', name: 'custom089_edit', methods: ['GET'])]
    #[Route(path: '/list/custom090/{id}/edit', name: 'custom090_edit', methods: ['GET'])]
    #[Route(path: '/list/custom091/{id}/edit', name: 'custom091_edit', methods: ['GET'])]
    #[Route(path: '/list/custom092/{id}/edit', name: 'custom092_edit', methods: ['GET'])]
    #[Route(path: '/list/custom093/{id}/edit', name: 'custom093_edit', methods: ['GET'])]
    #[Route(path: '/list/custom094/{id}/edit', name: 'custom094_edit', methods: ['GET'])]
    #[Route(path: '/list/custom095/{id}/edit', name: 'custom095_edit', methods: ['GET'])]
    #[Route(path: '/list/custom096/{id}/edit', name: 'custom096_edit', methods: ['GET'])]
    #[Route(path: '/list/custom097/{id}/edit', name: 'custom097_edit', methods: ['GET'])]
    #[Route(path: '/list/custom098/{id}/edit', name: 'custom098_edit', methods: ['GET'])]
    #[Route(path: '/list/custom099/{id}/edit', name: 'custom099_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-tissue-processing-services/{id}/edit', name: 'transrestissueprocessingservices_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-other-requested-services/{id}/edit', name: 'transresotherrequestedservices_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-collaboration-labs/{id}/edit', name: 'transrescolllabs_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-collaboration-divs/{id}/edit', name: 'transrescolldivs_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-irb-approval-status/{id}/edit', name: 'transresirbstatus_edit', methods: ['GET'])]
    #[Route(path: '/list/translational-requester-group/{id}/edit', name: 'transresrequestergroup_edit', methods: ['GET'])]
    #[Route(path: '/list/transrescomptypes/{id}/edit', name: 'transrescomptypes_edit', methods: ['GET'])]
    #[Route(path: '/list/visastatus/{id}/edit', name: 'visastatus_edit', methods: ['GET'])]
    #[Route(path: '/list/resappstatuses/{id}/edit', name: 'resappstatuses_edit', methods: ['GET'])]
    #[Route(path: '/list/resappranks/{id}/edit', name: 'resappranks_edit', methods: ['GET'])]
    #[Route(path: '/list/resapplanguageproficiency/{id}/edit', name: 'resapplanguageproficiency_edit', methods: ['GET'])]
    #[Route(path: '/list/resappfitforprogram/{id}/edit', name: 'resappfitforprogram_edit', methods: ['GET'])]
    #[Route(path: '/list/resappvisastatus/{id}/edit', name: 'resappvisastatus_edit', methods: ['GET'])]
    #[Route(path: '/list/postsoph/{id}/edit', name: 'postsoph_edit', methods: ['GET'])]
    #[Route(path: '/list/resappapplyingresidencytrack/{id}/edit', name: 'resappapplyingresidencytrack_edit', methods: ['GET'])]
    #[Route(path: '/list/resapplearnarealist/{id}/edit', name: 'resapplearnarealist_edit', methods: ['GET'])]
    #[Route(path: '/list/resappspecificindividuallist/{id}/edit', name: 'resappspecificindividuallist_edit', methods: ['GET'])]
    #[Route(path: '/list/viewmodes/{id}/edit', name: 'viewmodes_edit', methods: ['GET'])]
    #[Route(path: '/list/transrespricetypes/{id}/edit', name: 'transrespricetypes_edit', methods: ['GET'])]
    #[Route(path: '/list/charttypes/{id}/edit', name: 'charttypes_edit', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/charttopics/{id}/edit', name: 'charttopics_edit', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/chartfilters/{id}/edit', name: 'chartfilters_edit', methods: ['GET'])]
    #[Route(path: '/list/charts/{id}/edit', name: 'charts_edit', methods: ['GET'])]
    #[Route(path: '/list/chartdatasources/{id}/edit', name: 'chartdatasources_edit', methods: ['GET'])]
    #[Route(path: '/list/chartupdatefrequencies/{id}/edit', name: 'chartupdatefrequencies_edit', methods: ['GET'])]
    #[Route(path: '/list/chartvisualizations/{id}/edit', name: 'chartvisualizations_edit', methods: ['GET'])]
    #[Route(path: '/list/vacreqholidays/{id}/edit', name: 'vacreqholidays_edit', methods: ['GET'])]
    #[Route(path: '/list/vacreqobservedholidays/{id}/edit', name: 'vacreqobservedholidays_edit', methods: ['GET'])]
    #[Route(path: '/list/authusergroup/{id}/edit', name: 'authusergroup_edit', methods: ['GET'])]
    #[Route(path: '/list/authservernetwork/{id}/edit', name: 'authservernetwork_edit', methods: ['GET'])]
    #[Route(path: '/list/authpartnerserver/{id}/edit', name: 'authpartnerserver_edit', methods: ['GET'])]
    #[Route(path: '/list/tenanturls/{id}/edit', name: 'tenanturls_edit', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/list/antibodycategorytag/{id}/edit', name: 'antibodycategorytag_edit', methods: ['GET'])]
    #[Route(path: '/list/transferstatus/{id}/edit', name: 'transferstatus_edit', methods: ['GET'])]
    #[Route(path: '/list/interfacetransfers/{id}/edit', name: 'interfacetransfers_edit', methods: ['GET'])]
    #[Route(path: '/list/antibodylabs/{id}/edit', name: 'antibodylabs_edit', methods: ['GET'])]
    #[Route(path: '/list/antibodypanels/{id}/edit', name: 'antibodypanels_edit', methods: ['GET'])]
    #[Route(path: '/list/samlconfig/{id}/edit', name: 'samlconfig_edit', methods: ['GET'])]
    #[Route(path: '/list/globalfellowshipspecialty/{id}/edit', name: 'globalfellowshipspecialty_edit', methods: ['GET'])]
    #[Route(path: '/list/trainingeligibility/{id}/edit', name: 'trainingeligibility_edit', methods: ['GET'])]
    #[Route(path: '/list/dutiescapability/{id}/edit', name: 'dutiescapability_edit', methods: ['GET'])]
    #[Route(path: '/list/phdfield/{id}/edit', name: 'phdfield_edit', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/ListForm/edit.html.twig')]
    public function editAction(Request $request,$id)
    {
        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        return $this->editList($request,$id);
    }
    public function editList( $request, $id ) {

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        if( $routeName == 'antibodies_edit' ) {
            return $this->redirect( $this->generateUrl('translationalresearch_antibody_edit', array('id'=>$id)) );
        }

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase,$request);

        //$entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);
        $entity = $em->getRepository($mapper['fullClassName'])->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
        }

        //add permissions
        //$this->addPermissions($entity);

        //add visual info
        //$this->addVisualInfo($entity); //edit

        $editForm = $this->createEditForm($entity,$mapper,$pathbase,'edit',false);
        $deleteForm = $this->createDeleteForm($id,$pathbase);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase,
            'sitename' => $this->sitename,
            'cycle' => 'edit'
        );
    }

    private function addPermissions($entity) {
        if( method_exists($entity,'getPermissions') ) {
            //echo "add permission for ".$entity."<br>";
            $permission = new Permission();
            $entity->addPermission($permission);
        }
    }

    private function addVisualInfo($entity) {
        if( method_exists($entity,'getVisualInfos') ) {
            if( count($entity->getVisualInfos()) == 0 ) {
                $item = new VisualInfo();
                $entity->addVisualInfo($item);
            }
        }
    }

    /**
    * Creates a form to edit an entity.
    * @param $entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm($entity,$mapper,$pathbase,$cycle,$disabled=false)
    {

        $options = array();

        $options['id'] = $entity->getId();

        if( $cycle ) {
            $options['cycle'] = $cycle;
        }

        //use $timezone = $user->getTimezone(); ?
        $user = $this->getUser();
        $options['user'] = $user;
        $options['entity'] = $entity;
        $options['em'] = $this->getDoctrine()->getManager();
        //$options['SecurityAuthChecker'] = $this->container->get('security.authorization_checker');

        $form = $this->createForm(GenericListType::class, $entity, array(
            'action' => $this->generateUrl($pathbase.'_show'.$this->postPath, array('id' => $entity->getId())),
            'method' => 'PUT',
            'disabled' => $disabled,
            'data_class' => $mapper['fullClassName'],
            'form_custom_value' => $options,
            'form_custom_value_mapper' => $mapper
        ));

        if( !$disabled ) {
            $form->add('submit', SubmitType::class, array('label' => 'Update', 'attr'=>array('class'=>'btn btn-warning')));
        }

        return $form;
    }
    /**
     * Edits an existing entity.
     *
     *
     *
     *
     *
     *
     *
     *
     */
    #[Route(path: '/list-manager/{id}', name: 'platformlistmanager_update', methods: ['PUT'])]
    #[Route(path: '/list/source-systems/{id}', name: 'sourcesystems_update', methods: ['PUT'])]
    #[Route(path: '/list/roles/{id}', name: 'role_update', methods: ['PUT'])]
    #[Route(path: '/list/institutions/{id}', name: 'institutions_update', methods: ['PUT'])]
    #[Route(path: '/list/states/{id}', name: 'states_update', methods: ['PUT'])]
    #[Route(path: '/list/countries/{id}', name: 'countries_update', methods: ['PUT'])]
    #[Route(path: '/list/board-certifications/{id}', name: 'boardcertifications_update', methods: ['PUT'])]
    #[Route(path: '/list/employment-termination-reasons/{id}', name: 'employmentterminations_update', methods: ['PUT'])]
    #[Route(path: '/list/event-log-event-types/{id}', name: 'loggereventtypes_update', methods: ['PUT'])]
    #[Route(path: '/list/primary-public-user-id-types/{id}', name: 'usernametypes_update', methods: ['PUT'])]
    #[Route(path: '/list/identifier-types/{id}', name: 'identifiers_update', methods: ['PUT'])]
    #[Route(path: '/list/residency-tracks/{id}', name: 'residencytracks_update', methods: ['PUT'])]
    #[Route(path: '/list/fellowship-types/{id}', name: 'fellowshiptypes_update', methods: ['PUT'])]
    #[Route(path: '/list/location-types/{id}', name: 'locationtypes_update', methods: ['PUT'])]
    #[Route(path: '/list/equipment/{id}', name: 'equipments_update', methods: ['PUT'])]
    #[Route(path: '/list/equipment-types/{id}', name: 'equipmenttypes_update', methods: ['PUT'])]
    #[Route(path: '/list/location-privacy-types/{id}', name: 'locationprivacy_update', methods: ['PUT'])]
    #[Route(path: '/list/role-attributes/{id}', name: 'roleattributes_update', methods: ['PUT'])]
    #[Route(path: '/list/buidlings/{id}', name: 'buildings_update', methods: ['PUT'])]
    #[Route(path: '/list/rooms/{id}', name: 'rooms_update', methods: ['PUT'])]
    #[Route(path: '/list/suites/{id}', name: 'suites_update', methods: ['PUT'])]
    #[Route(path: '/list/floors/{id}', name: 'floors_update', methods: ['PUT'])]
    #[Route(path: '/list/grants/{id}', name: 'grants_update', methods: ['PUT'])]
    #[Route(path: '/list/mailboxes/{id}', name: 'mailboxes_update', methods: ['PUT'])]
    #[Route(path: '/list/percent-effort/{id}', name: 'efforts_update', methods: ['PUT'])]
    #[Route(path: '/list/administrative-titles/{id}', name: 'admintitles_update', methods: ['PUT'])]
    #[Route(path: '/list/academic-appointment-titles/{id}', name: 'apptitles_update', methods: ['PUT'])]
    #[Route(path: '/list/training-completion-reasons/{id}', name: 'completionreasons_update', methods: ['PUT'])]
    #[Route(path: '/list/training-degrees/{id}', name: 'trainingdegrees_update', methods: ['PUT'])]
    #[Route(path: '/list/training-majors/{id}', name: 'trainingmajors_update', methods: ['PUT'])]
    #[Route(path: '/list/training-minors/{id}', name: 'trainingminors_update', methods: ['PUT'])]
    #[Route(path: '/list/training-honors/{id}', name: 'traininghonors_update', methods: ['PUT'])]
    #[Route(path: '/list/fellowship-titles/{id}', name: 'fellowshiptitles_update', methods: ['PUT'])]
    #[Route(path: '/list/residency-specialties/{id}', name: 'residencyspecialtys_update', methods: ['PUT'])]
    #[Route(path: '/list/fellowship-subspecialties/{id}', name: 'fellowshipsubspecialtys_update', methods: ['PUT'])]
    #[Route(path: '/list/institution-types/{id}', name: 'institutiontypes_update', methods: ['PUT'])]
    #[Route(path: '/list/document-types/{id}', name: 'documenttypes_update', methods: ['PUT'])]
    #[Route(path: '/list/medical-titles/{id}', name: 'medicaltitles_update', methods: ['PUT'])]
    #[Route(path: '/list/medical-specialties/{id}', name: 'medicalspecialties_update', methods: ['PUT'])]
    #[Route(path: '/list/employment-types/{id}', name: 'employmenttypes_update', methods: ['PUT'])]
    #[Route(path: '/list/grant-source-organizations/{id}', name: 'sourceorganizations_update', methods: ['PUT'])]
    #[Route(path: '/list/languages/{id}', name: 'languages_update', methods: ['PUT'])]
    #[Route(path: '/list/locales/{id}', name: 'locales_update', methods: ['PUT'])]
    #[Route(path: '/list/ranks-of-importance/{id}', name: 'importances_update', methods: ['PUT'])]
    #[Route(path: '/list/authorship-roles/{id}', name: 'authorshiproles_update', methods: ['PUT'])]
    #[Route(path: '/list/lecture-venues/{id}', name: 'organizations_update', methods: ['PUT'])]
    #[Route(path: '/list/cities/{id}', name: 'cities_update', methods: ['PUT'])]
    #[Route(path: '/list/link-types/{id}', name: 'linktypes_update', methods: ['PUT'])]
    #[Route(path: '/list/sexes/{id}', name: 'sexes_update', methods: ['PUT'])]
    #[Route(path: '/list/position-types/{id}', name: 'positiontypes_update', methods: ['PUT'])]
    #[Route(path: '/list/organizational-group-types/{id}', name: 'organizationalgrouptypes_update', methods: ['PUT'])]
    #[Route(path: '/list/profile-comment-group-types/{id}', name: 'commentgrouptypes_update', methods: ['PUT'])]
    #[Route(path: '/list/comment-types/{id}', name: 'commenttypes_update', methods: ['PUT'])]
    #[Route(path: '/list/user-wrappers/{id}', name: 'userwrappers_update', methods: ['PUT'])]
    #[Route(path: '/list/spot-purposes/{id}', name: 'spotpurposes_update', methods: ['PUT'])]
    #[Route(path: '/list/medical-license-statuses/{id}', name: 'medicalstatuses_update', methods: ['PUT'])]
    #[Route(path: '/list/certifying-board-organizations/{id}', name: 'certifyingboardorganizations_update', methods: ['PUT'])]
    #[Route(path: '/list/training-types/{id}', name: 'trainingtypes_update', methods: ['PUT'])]
    #[Route(path: '/list/job-titles/{id}', name: 'joblists_update', methods: ['PUT'])]
    #[Route(path: '/list/fellowship-application-statuses/{id}', name: 'fellappstatuses_update', methods: ['PUT'])]
    #[Route(path: '/list/fellowship-application-ranks/{id}', name: 'fellappranks_update', methods: ['PUT'])]
    #[Route(path: '/list/fellowship-application-language-proficiencies/{id}', name: 'fellapplanguageproficiency_update', methods: ['PUT'])]
    #[Route(path: '/list/collaboration-types/{id}', name: 'collaborationtypes_update', methods: ['PUT'])]
    #[Route(path: '/list/permissions/{id}', name: 'permission_update', methods: ['PUT'])]
    #[Route(path: '/list/permission-objects/{id}', name: 'permissionobject_update', methods: ['PUT'])]
    #[Route(path: '/list/permission-actions/{id}', name: 'permissionaction_update', methods: ['PUT'])]
    #[Route(path: '/list/sites/{id}', name: 'sites_update', methods: ['PUT'])]
    #[Route(path: '/list/event-object-types/{id}', name: 'eventobjecttypes_update', methods: ['PUT'])]
    #[Route(path: '/list/time-away-request-types/{id}', name: 'vacreqrequesttypes_update', methods: ['PUT'])]
    #[Route(path: '/list/time-away-request-floating-texts/{id}', name: 'vacreqfloatingtexts_update', methods: ['PUT'])]
    #[Route(path: '/list/time-away-request-floating-types/{id}', name: 'vacreqfloatingtypes_update', methods: ['PUT'])]
    #[Route(path: '/list/time-away-request-approval-types/{id}', name: 'vacreqapprovaltypes_update', methods: ['PUT'])]
    #[Route(path: '/list/healthcare-provider-specialties/{id}', name: 'healthcareproviderspecialty_update', methods: ['PUT'])]
    #[Route(path: '/list/healthcare-provider-initial-communications/{id}', name: 'healthcareprovidercommunication_update', methods: ['PUT'])]
    #[Route(path: '/list/object-types/{id}', name: 'objecttypes_update', methods: ['PUT'])]
    #[Route(path: '/list/form-nodes/{id}', name: 'formnodes_update', methods: ['PUT'], options: ['expose' => true])]
    #[Route(path: '/list/object-type-texts/{id}', name: 'objecttypetexts_update', methods: ['PUT'], options: ['expose' => true])]
    #[Route(path: '/list/blood-product-transfusions/{id}', name: 'bloodproducttransfusions_update', methods: ['PUT'])]
    #[Route(path: '/list/transfusion-reaction-types/{id}', name: 'transfusionreactiontypes_update', methods: ['PUT'])]
    #[Route(path: '/list/object-type-strings/{id}', name: 'objecttypestrings_update', methods: ['PUT'])]
    #[Route(path: '/list/object-type-dropdowns/{id}', name: 'objecttypedropdowns_update', methods: ['PUT'])]
    #[Route(path: '/list/blood-types/{id}', name: 'bloodtypes_update', methods: ['PUT'])]
    #[Route(path: '/list/additional-communications/{id}', name: 'additionalcommunications_update', methods: ['PUT'])]
    #[Route(path: '/list/transfusion-antibody-screen-results/{id}', name: 'transfusionantibodyscreenresults_update', methods: ['PUT'])]
    #[Route(path: '/list/transfusion-crossmatch-results/{id}', name: 'transfusioncrossmatchresults_update', methods: ['PUT'])]
    #[Route(path: '/list/transfusion-dat-results/{id}', name: 'transfusiondatresults_update', methods: ['PUT'])]
    #[Route(path: '/list/transfusion-hemolysis-check-results/{id}', name: 'transfusionhemolysischeckresults_update', methods: ['PUT'])]
    #[Route(path: '/list/object-type-datetimes/{id}', name: 'objecttypedatetimes_update', methods: ['PUT'])]
    #[Route(path: '/list/complex-platelet-summary-antibodies/{id}', name: 'complexplateletsummaryantibodies_update', methods: ['PUT'])]
    #[Route(path: '/list/cci-unit-platelet-count-default-values/{id}', name: 'cciunitplateletcountdefaultvalues_update', methods: ['PUT'])]
    #[Route(path: '/list/cci-platelet-type-transfused/{id}', name: 'cciplatelettypetransfuseds_update', methods: ['PUT'])]
    #[Route(path: '/list/platelet-transfusion-product-receiving/{id}', name: 'platelettransfusionproductreceivings_update', methods: ['PUT'])]
    #[Route(path: '/list/transfusion-product-status/{id}', name: 'transfusionproductstatus_update', methods: ['PUT'])]
    #[Route(path: '/list/week-days/{id}', name: 'weekdays_update', methods: ['PUT'])]
    #[Route(path: '/list/months/{id}', name: 'months_update', methods: ['PUT'])]
    #[Route(path: '/list/clerical-errors/{id}', name: 'clericalerrors_update', methods: ['PUT'])]
    #[Route(path: '/list/lab-result-names/{id}', name: 'labresultnames_update', methods: ['PUT'])]
    #[Route(path: '/list/lab-result-units-measures/{id}', name: 'labresultunitsmeasures_update', methods: ['PUT'])]
    #[Route(path: '/list/lab-result-flags/{id}', name: 'labresultflags_update', methods: ['PUT'])]
    #[Route(path: '/list/pathology-result-signatories/{id}', name: 'pathologyresultsignatories_update', methods: ['PUT'])]
    #[Route(path: '/list/object-type-checkboxes/{id}', name: 'objecttypecheckboxs_update', methods: ['PUT'])]
    #[Route(path: '/list/object-type-radio-buttons/{id}', name: 'objecttyperadiobuttons_update', methods: ['PUT'])]
    #[Route(path: '/list/life-forms/{id}', name: 'lifeforms_update', methods: ['PUT'])]
    #[Route(path: '/list/position-track-types/{id}', name: 'positiontracktypes_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-research-project-specialties-orig/{id}', name: 'transresprojectspecialties_update_orig', methods: ['PUT'])]
    #[Route(path: '/list/translational-research-project-types/{id}', name: 'transresprojecttypes_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-research-request-category-types/{id}', name: 'transresrequestcategorytypes_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-irb-approval-types/{id}', name: 'transresirbapprovaltypes_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-business-purposes/{id}', name: 'transresbusinesspurposes_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-work-queue-types/{id}', name: 'workqueuetypes_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-orderable-status/{id}', name: 'orderablestatus_update', methods: ['PUT'])]
    #[Route(path: '/list/antibodies/{id}', name: 'antibodies_update', methods: ['PUT'])]
    #[Route(path: '/list/custom000/{id}', name: 'custom000_update', methods: ['PUT'])]
    #[Route(path: '/list/custom001/{id}', name: 'custom001_update', methods: ['PUT'])]
    #[Route(path: '/list/custom002/{id}', name: 'custom002_update', methods: ['PUT'])]
    #[Route(path: '/list/custom003/{id}', name: 'custom003_update', methods: ['PUT'])]
    #[Route(path: '/list/custom004/{id}', name: 'custom004_update', methods: ['PUT'])]
    #[Route(path: '/list/custom005/{id}', name: 'custom005_update', methods: ['PUT'])]
    #[Route(path: '/list/custom006/{id}', name: 'custom006_update', methods: ['PUT'])]
    #[Route(path: '/list/custom007/{id}', name: 'custom007_update', methods: ['PUT'])]
    #[Route(path: '/list/custom008/{id}', name: 'custom008_update', methods: ['PUT'])]
    #[Route(path: '/list/custom009/{id}', name: 'custom009_update', methods: ['PUT'])]
    #[Route(path: '/list/custom010/{id}', name: 'custom010_update', methods: ['PUT'])]
    #[Route(path: '/list/custom011/{id}', name: 'custom011_update', methods: ['PUT'])]
    #[Route(path: '/list/custom012/{id}', name: 'custom012_update', methods: ['PUT'])]
    #[Route(path: '/list/custom013/{id}', name: 'custom013_update', methods: ['PUT'])]
    #[Route(path: '/list/custom014/{id}', name: 'custom014_update', methods: ['PUT'])]
    #[Route(path: '/list/custom015/{id}', name: 'custom015_update', methods: ['PUT'])]
    #[Route(path: '/list/custom016/{id}', name: 'custom016_update', methods: ['PUT'])]
    #[Route(path: '/list/custom017/{id}', name: 'custom017_update', methods: ['PUT'])]
    #[Route(path: '/list/custom018/{id}', name: 'custom018_update', methods: ['PUT'])]
    #[Route(path: '/list/custom019/{id}', name: 'custom019_update', methods: ['PUT'])]
    #[Route(path: '/list/custom020/{id}', name: 'custom020_update', methods: ['PUT'])]
    #[Route(path: '/list/custom021/{id}', name: 'custom021_update', methods: ['PUT'])]
    #[Route(path: '/list/custom022/{id}', name: 'custom022_update', methods: ['PUT'])]
    #[Route(path: '/list/custom023/{id}', name: 'custom023_update', methods: ['PUT'])]
    #[Route(path: '/list/custom024/{id}', name: 'custom024_update', methods: ['PUT'])]
    #[Route(path: '/list/custom025/{id}', name: 'custom025_update', methods: ['PUT'])]
    #[Route(path: '/list/custom026/{id}', name: 'custom026_update', methods: ['PUT'])]
    #[Route(path: '/list/custom027/{id}', name: 'custom027_update', methods: ['PUT'])]
    #[Route(path: '/list/custom028/{id}', name: 'custom028_update', methods: ['PUT'])]
    #[Route(path: '/list/custom029/{id}', name: 'custom029_update', methods: ['PUT'])]
    #[Route(path: '/list/custom030/{id}', name: 'custom030_update', methods: ['PUT'])]
    #[Route(path: '/list/custom031/{id}', name: 'custom031_update', methods: ['PUT'])]
    #[Route(path: '/list/custom032/{id}', name: 'custom032_update', methods: ['PUT'])]
    #[Route(path: '/list/custom033/{id}', name: 'custom033_update', methods: ['PUT'])]
    #[Route(path: '/list/custom034/{id}', name: 'custom034_update', methods: ['PUT'])]
    #[Route(path: '/list/custom035/{id}', name: 'custom035_update', methods: ['PUT'])]
    #[Route(path: '/list/custom036/{id}', name: 'custom036_update', methods: ['PUT'])]
    #[Route(path: '/list/custom037/{id}', name: 'custom037_update', methods: ['PUT'])]
    #[Route(path: '/list/custom038/{id}', name: 'custom038_update', methods: ['PUT'])]
    #[Route(path: '/list/custom039/{id}', name: 'custom039_update', methods: ['PUT'])]
    #[Route(path: '/list/custom040/{id}', name: 'custom040_update', methods: ['PUT'])]
    #[Route(path: '/list/custom041/{id}', name: 'custom041_update', methods: ['PUT'])]
    #[Route(path: '/list/custom042/{id}', name: 'custom042_update', methods: ['PUT'])]
    #[Route(path: '/list/custom043/{id}', name: 'custom043_update', methods: ['PUT'])]
    #[Route(path: '/list/custom044/{id}', name: 'custom044_update', methods: ['PUT'])]
    #[Route(path: '/list/custom045/{id}', name: 'custom045_update', methods: ['PUT'])]
    #[Route(path: '/list/custom046/{id}', name: 'custom046_update', methods: ['PUT'])]
    #[Route(path: '/list/custom047/{id}', name: 'custom047_update', methods: ['PUT'])]
    #[Route(path: '/list/custom048/{id}', name: 'custom048_update', methods: ['PUT'])]
    #[Route(path: '/list/custom049/{id}', name: 'custom049_update', methods: ['PUT'])]
    #[Route(path: '/list/custom050/{id}', name: 'custom050_update', methods: ['PUT'])]
    #[Route(path: '/list/custom051/{id}', name: 'custom051_update', methods: ['PUT'])]
    #[Route(path: '/list/custom052/{id}', name: 'custom052_update', methods: ['PUT'])]
    #[Route(path: '/list/custom053/{id}', name: 'custom053_update', methods: ['PUT'])]
    #[Route(path: '/list/custom054/{id}', name: 'custom054_update', methods: ['PUT'])]
    #[Route(path: '/list/custom055/{id}', name: 'custom055_update', methods: ['PUT'])]
    #[Route(path: '/list/custom056/{id}', name: 'custom056_update', methods: ['PUT'])]
    #[Route(path: '/list/custom057/{id}', name: 'custom057_update', methods: ['PUT'])]
    #[Route(path: '/list/custom058/{id}', name: 'custom058_update', methods: ['PUT'])]
    #[Route(path: '/list/custom059/{id}', name: 'custom059_update', methods: ['PUT'])]
    #[Route(path: '/list/custom060/{id}', name: 'custom060_update', methods: ['PUT'])]
    #[Route(path: '/list/custom061/{id}', name: 'custom061_update', methods: ['PUT'])]
    #[Route(path: '/list/custom062/{id}', name: 'custom062_update', methods: ['PUT'])]
    #[Route(path: '/list/custom063/{id}', name: 'custom063_update', methods: ['PUT'])]
    #[Route(path: '/list/custom064/{id}', name: 'custom064_update', methods: ['PUT'])]
    #[Route(path: '/list/custom065/{id}', name: 'custom065_update', methods: ['PUT'])]
    #[Route(path: '/list/custom066/{id}', name: 'custom066_update', methods: ['PUT'])]
    #[Route(path: '/list/custom067/{id}', name: 'custom067_update', methods: ['PUT'])]
    #[Route(path: '/list/custom068/{id}', name: 'custom068_update', methods: ['PUT'])]
    #[Route(path: '/list/custom069/{id}', name: 'custom069_update', methods: ['PUT'])]
    #[Route(path: '/list/custom070/{id}', name: 'custom070_update', methods: ['PUT'])]
    #[Route(path: '/list/custom071/{id}', name: 'custom071_update', methods: ['PUT'])]
    #[Route(path: '/list/custom072/{id}', name: 'custom072_update', methods: ['PUT'])]
    #[Route(path: '/list/custom073/{id}', name: 'custom073_update', methods: ['PUT'])]
    #[Route(path: '/list/custom074/{id}', name: 'custom074_update', methods: ['PUT'])]
    #[Route(path: '/list/custom075/{id}', name: 'custom075_update', methods: ['PUT'])]
    #[Route(path: '/list/custom076/{id}', name: 'custom076_update', methods: ['PUT'])]
    #[Route(path: '/list/custom077/{id}', name: 'custom077_update', methods: ['PUT'])]
    #[Route(path: '/list/custom078/{id}', name: 'custom078_update', methods: ['PUT'])]
    #[Route(path: '/list/custom079/{id}', name: 'custom079_update', methods: ['PUT'])]
    #[Route(path: '/list/custom080/{id}', name: 'custom080_update', methods: ['PUT'])]
    #[Route(path: '/list/custom081/{id}', name: 'custom081_update', methods: ['PUT'])]
    #[Route(path: '/list/custom082/{id}', name: 'custom082_update', methods: ['PUT'])]
    #[Route(path: '/list/custom083/{id}', name: 'custom083_update', methods: ['PUT'])]
    #[Route(path: '/list/custom084/{id}', name: 'custom084_update', methods: ['PUT'])]
    #[Route(path: '/list/custom085/{id}', name: 'custom085_update', methods: ['PUT'])]
    #[Route(path: '/list/custom086/{id}', name: 'custom086_update', methods: ['PUT'])]
    #[Route(path: '/list/custom087/{id}', name: 'custom087_update', methods: ['PUT'])]
    #[Route(path: '/list/custom088/{id}', name: 'custom088_update', methods: ['PUT'])]
    #[Route(path: '/list/custom089/{id}', name: 'custom089_update', methods: ['PUT'])]
    #[Route(path: '/list/custom090/{id}', name: 'custom090_update', methods: ['PUT'])]
    #[Route(path: '/list/custom091/{id}', name: 'custom091_update', methods: ['PUT'])]
    #[Route(path: '/list/custom092/{id}', name: 'custom092_update', methods: ['PUT'])]
    #[Route(path: '/list/custom093/{id}', name: 'custom093_update', methods: ['PUT'])]
    #[Route(path: '/list/custom094/{id}', name: 'custom094_update', methods: ['PUT'])]
    #[Route(path: '/list/custom095/{id}', name: 'custom095_update', methods: ['PUT'])]
    #[Route(path: '/list/custom096/{id}', name: 'custom096_update', methods: ['PUT'])]
    #[Route(path: '/list/custom097/{id}', name: 'custom097_update', methods: ['PUT'])]
    #[Route(path: '/list/custom098/{id}', name: 'custom098_update', methods: ['PUT'])]
    #[Route(path: '/list/custom099/{id}', name: 'custom099_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-tissue-processing-services/{id}', name: 'transrestissueprocessingservices_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-other-requested-services/{id}', name: 'transresotherrequestedservices_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-collaboration-labs/{id}', name: 'transrescolllabs_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-collaboration-divs/{id}', name: 'transrescolldivs_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-irb-approval-status/{id}', name: 'transresirbstatus_update', methods: ['PUT'])]
    #[Route(path: '/list/translational-requester-group/{id}', name: 'transresrequestergroup_update', methods: ['PUT'])]
    #[Route(path: '/list/transrescomptypes/{id}', name: 'transrescomptypes_update', methods: ['PUT'])]
    #[Route(path: '/list/visastatus/{id}', name: 'visastatus_update', methods: ['PUT'])]
    #[Route(path: '/list/resappstatuses/{id}', name: 'resappstatuses_update', methods: ['PUT'])]
    #[Route(path: '/list/resappranks/{id}', name: 'resappranks_update', methods: ['PUT'])]
    #[Route(path: '/list/resapplanguageproficiency/{id}', name: 'resapplanguageproficiency_update', methods: ['PUT'])]
    #[Route(path: '/list/resappfitforprogram/{id}', name: 'resappfitforprogram_update', methods: ['PUT'])]
    #[Route(path: '/list/resappvisastatus/{id}', name: 'resappvisastatus_update', methods: ['PUT'])]
    #[Route(path: '/list/postsoph/{id}', name: 'postsoph_update', methods: ['PUT'])]
    #[Route(path: '/list/resappapplyingresidencytrack/{id}', name: 'resappapplyingresidencytrack_update', methods: ['PUT'])]
    #[Route(path: '/list/resapplearnarealist/{id}', name: 'resapplearnarealist_update', methods: ['PUT'])]
    #[Route(path: '/list/resappspecificindividuallist/{id}', name: 'resappspecificindividuallist_update', methods: ['PUT'])]
    #[Route(path: '/list/viewmodes/{id}', name: 'viewmodes_update', methods: ['PUT'])]
    #[Route(path: '/list/transrespricetypes/{id}', name: 'transrespricetypes_update', methods: ['PUT'])]
    #[Route(path: '/list/charttypes/{id}', name: 'charttypes_update', methods: ['PUT'], options: ['expose' => true])]
    #[Route(path: '/list/charttopics/{id}', name: 'charttopics_update', methods: ['PUT'], options: ['expose' => true])]
    #[Route(path: '/list/chartfilters/{id}', name: 'chartfilters_update', methods: ['PUT'])]
    #[Route(path: '/list/charts/{id}', name: 'charts_update', methods: ['PUT'])]
    #[Route(path: '/list/chartdatasources/{id}', name: 'chartdatasources_update', methods: ['PUT'])]
    #[Route(path: '/list/chartupdatefrequencies/{id}', name: 'chartupdatefrequencies_update', methods: ['PUT'])]
    #[Route(path: '/list/chartvisualizations/{id}', name: 'chartvisualizations_update', methods: ['PUT'])]
    #[Route(path: '/list/vacreqholidays/{id}', name: 'vacreqholidays_update', methods: ['PUT'])]
    #[Route(path: '/list/vacreqobservedholidays/{id}', name: 'vacreqobservedholidays_update', methods: ['PUT'])]
    #[Route(path: '/list/authusergroup/{id}', name: 'authusergroup_update', methods: ['PUT'])]
    #[Route(path: '/list/authservernetwork/{id}', name: 'authservernetwork_update', methods: ['PUT'])]
    #[Route(path: '/list/authpartnerserver/{id}', name: 'authpartnerserver_update', methods: ['PUT'])]
    #[Route(path: '/list/tenanturls/{id}', name: 'tenanturls_update', methods: ['PUT'], options: ['expose' => true])]
    #[Route(path: '/list/antibodycategorytag/{id}', name: 'antibodycategorytag_update', methods: ['PUT'])]
    #[Route(path: '/list/transferstatus/{id}', name: 'transferstatus_update', methods: ['PUT'])]
    #[Route(path: '/list/interfacetransfers/{id}', name: 'interfacetransfers_update', methods: ['PUT'])]
    #[Route(path: '/list/antibodylabs/{id}', name: 'antibodylabs_update', methods: ['PUT'])]
    #[Route(path: '/list/antibodypanels/{id}', name: 'antibodypanels_update', methods: ['PUT'])]
    #[Route(path: '/list/samlconfig/{id}', name: 'samlconfig_update', methods: ['PUT'])]
    #[Route(path: '/list/globalfellowshipspecialty/{id}', name: 'globalfellowshipspecialty_update', methods: ['PUT'])]
    #[Route(path: '/list/trainingeligibility/{id}', name: 'trainingeligibility_update', methods: ['PUT'])]
    #[Route(path: '/list/dutiescapability/{id}', name: 'dutiescapability_update', methods: ['PUT'])]
    #[Route(path: '/list/phdfield/{id}', name: 'phdfield_update', methods: ['PUT'])]
    #[Template('AppUserdirectoryBundle/ListForm/edit.html.twig')]
    public function updateAction(Request $request, $id)
    {
        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        return $this->updateList($request, $id);
    }
    public function updateList( $request, $id ) {

        //exit("Update list");

        $userSecUtil = $this->container->get('user_security_utility');

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase,$request);

        //$entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);
        $entity = $em->getRepository($mapper['fullClassName'])->find($id);

        //save array of synonyms
        $beforeformSynonyms = clone $entity->getSynonyms();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
        }

        $originalName = NULL;
        if( method_exists($entity,'getName') ) {
            $originalName = $entity->getName();
        }
        $originalType = NULL;
        if( method_exists($entity,'getType') ) {
            $originalType = $entity->getType();
        }
        $originalDescription = NULL;
        if( method_exists($entity,'getDescription') ) {
            $originalDescription = $entity->getDescription();
        }

        //remove permissions: original permissions. Used for roles
        if( method_exists($entity,'getPermissions') ) {
            $originalPermissions = array();
            foreach( $entity->getPermissions() as $permission ) {
                $originalPermissions[] = $permission;
            }
        }

        if( method_exists($entity,'getVisualInfos') ) {
            $originalVisualInfos = array();
            foreach( $entity->getVisualInfos() as $visualInfo ) {
                $originalVisualInfos[] = $visualInfo;
            }
        }

        if( method_exists($entity,'getPrices') ) {
            $originalPrices = array();
            foreach( $entity->getPrices() as $price ) {
                $originalPrices[] = $price;
            }
        }

        if( method_exists($entity,'getHostedGroupHolders') ) {
            $originalHostedGroupHolders = array();
            foreach( $entity->getHostedGroupHolders() as $hostedGroupHolder ) {
                $originalHostedGroupHolders[] = $hostedGroupHolder;
            }
        }

        //Antibody specific attributes
        $originalEssentialAttributes = NULL;
        if( method_exists($entity,'getEssentialAttributes') ) {
            $originalEssentialAttributes = $entity->getEssentialAttributes();
        }

        $deleteForm = $this->createDeleteForm($id,$pathbase);
        $editForm = $this->createEditForm($entity,$mapper,$pathbase,'edit_put_list');
        $editForm->handleRequest($request);

        if( $editForm->isValid() ) {

            //make sure to keep creator and creation date from original entity, according to the requirements (Issue#250):
            //For "Creation Date", "Creator" these variables should not be modifiable via the form even if the user unlocks these fields in the browser.
            //$originalEntity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);
            $originalEntity = $em->getRepository($mapper['fullClassName'])->find($id);

            $entity->setCreator($originalEntity->getCreator());
            $entity->setCreatedate($originalEntity->getCreatedate());

            $user = $this->getUser();
            $entity->setUpdatedby($user);
            //$entity->setUpdatedon(new \DateTime());
            $entity->setUpdateAuthorRoles($user->getRoles());

            //take care of self-referencing: remove
            if( count($beforeformSynonyms) > count($entity->getSynonyms()) ) {
                foreach( $beforeformSynonyms as $syn ) {
                    $syn->setOriginal(NULL);
                }
            }

            //take care of self-referencing: add
            foreach( $entity->getSynonyms() as $syn ) {
                $syn->setOriginal($entity);
            }

            /////////// remove permissions. Used for roles ///////////
            if( method_exists($entity,'getPermissions') ) {

                /////////////// Process Removed Collections ///////////////
                $removedCollections = array();

                $removedInfo = $this->removeCollection($originalPermissions,$entity->getPermissions(),$entity);
                if( $removedInfo ) {
                    $removedCollections[] = $removedInfo;
                }
                /////////////// EOF Process Removed Collections ///////////////

                /////////////// Add event log on edit (edit or add collection) ///////////////
                /////////////// Must run before removeCollection() function which flash DB. When DB is flashed getEntityChangeSet() will not work ///////////////
                $changedInfoArr = $this->setEventLogChanges($entity);
                //exit('1');
                //set Edit event log for removed collection and changed fields or added collection
                if( count($changedInfoArr) > 0 || count($removedCollections) > 0 ) {
                    $event = "Permission of the Role ".$entity->getId()." has been changed by ".$user.":"."<br>";
                    $event = $event . implode("<br>", $changedInfoArr);
                    $event = $event . "<br>" . implode("<br>", $removedCollections);
                    //$userSecUtil = $this->container->get('user_security_utility');
                    //echo "event=".$event."<br>";
                    //print_r($removedCollections);
                    //exit();
                    $userSecUtil->createUserEditEvent($this->getParameter('employees.sitename'),$event,$user,$entity,$request,'Role Permission Updated');
                }
                //exit();

//                foreach( $originalPermissions as $originalPermission ) {
//                    if( false === $entity->getPermissions()->contains($originalPermission) ) {
//                        // remove the Task from the Tag
//                        $entity->removePermission($originalPermission);
//
//                        // if it was a many-to-one relationship, remove the relationship like this
//                        $originalPermission->setRole(null);
//
//                        $em->persist($originalPermission);
//
//                        // if you wanted to delete the Tag entirely, you can also do that
//                        $em->remove($originalPermission);
//                    }
//                }
            }
            /////////// EOF remove permissions. Used for roles ///////////

            /////////// remove visual infos. Used for antibody ///////////
            if( method_exists($entity,'getVisualInfos') ) {

                /////////////// Process Removed Collections ///////////////
                $removedVisualInfoCollections = array();

                $removedInfo = $this->removeVisualInfoCollection($originalVisualInfos,$entity->getVisualInfos(),$entity);
                if( $removedInfo ) {
                    $removedVisualInfoCollections[] = $removedInfo;
                }
                /////////////// EOF Process Removed Collections ///////////////

                /////////////// Add event log on edit (edit or add collection) ///////////////
                /////////////// Must run before removeVisualInfoCollection function which flash DB. When DB is flashed getEntityChangeSet() will not work ///////////////
                if(0) {
                    //This caused doctrine problems to persist the VisualInfos->documents
                    $changedInfoArr = $this->setEventLogChanges($entity);
                    //exit('1');
                    //set Edit event log for removed collection and changed fields or added collection
                    if (count($changedInfoArr) > 0 || count($removedVisualInfoCollections) > 0) {
                        $event = "Visual Infos of the Antibody " . $entity->getId() . " has been changed by " . $user . ":" . "<br>";
                        $event = $event . implode("<br>", $changedInfoArr);
                        $event = $event . "<br>" . implode("<br>", $removedVisualInfoCollections);
                        //$userSecUtil = $this->container->get('user_security_utility');
                        //echo "event=".$event."<br>";
                        //print_r($removedCollections);
                        //exit();
                        $userSecUtil->createUserEditEvent($this->getParameter('employees.sitename'), $event, $user, $entity, $request, 'Antibody Visual Info Updated');
                    }
                }
                //exit();
            }
            /////////// EOF remove visual infos. Used for antibody ///////////


            /////////// remove prices. Used for RequestCategoryTypeList ///////////
            if( method_exists($entity,'getPrices') ) {

                /////////////// Process Removed Collections ///////////////
                $removedPriceCollections = array();

                $removedInfo = $this->removePriceCollection($originalPrices,$entity->getPrices(),$entity);
                if( $removedInfo ) {
                    $removedPriceCollections[] = $removedInfo;
                }
                /////////////// EOF Process Removed Collections ///////////////

                /////////////// Add event log on edit (edit or add collection) ///////////////
                /////////////// Must run before removePriceCollection() function which flash DB. When DB is flashed getEntityChangeSet() will not work ///////////////
                if(0) {
                    //This caused doctrine problems to persist the Prices->documents
                    $changedInfoArr = $this->setEventLogChanges($entity);
                    //exit('1');
                    //set Edit event log for removed collection and changed fields or added collection
                    if (count($changedInfoArr) > 0 || count($removedPriceCollections) > 0) {
                        $event = "Price the RequestCategoryTypeList " . $entity->getId() . " has been changed by " . $user . ":" . "<br>";
                        $event = $event . implode("<br>", $changedInfoArr);
                        $event = $event . "<br>" . implode("<br>", $removedPriceCollections);
                        //$userSecUtil = $this->container->get('user_security_utility');
                        //echo "event=".$event."<br>";
                        //print_r($removedCollections);
                        //exit();
                        $userSecUtil->createUserEditEvent($this->getParameter('employees.sitename'), $event, $user, $entity, $request, 'RequestCategoryTypeList Price Updated');
                    }
                }
                //exit();
            }
            /////////// EOF remove prices. Used for RequestCategoryTypeList ///////////

//            if( method_exists($entity,'getHostedGroupHolders') ) {
//                $originalHostedGroupHolders = array();
//                foreach( $entity->getHostedGroupHolders() as $hostedGroupHolder ) {
//                    $originalHostedGroupHolders[] = $hostedGroupHolder;
//                }
//            }
            /////////// remove prices. Used for RequestCategoryTypeList ///////////
            if( method_exists($entity,'getHostedGroupHolders') ) {

                /////////////// Process Removed Collections ///////////////
                $removedHostedGroupHolderCollections = array();

                $removedInfo = $this->removeHostedGroupHolderCollection($originalHostedGroupHolders,$entity->getHostedGroupHolders(),$entity);
                if( $removedInfo ) {
                    $removedHostedGroupHolderCollections[] = $removedInfo;
                }
                /////////////// EOF Process Removed Collections ///////////////

                /////////////// Add event log on edit (edit or add collection) ///////////////
                /////////////// Must run before removeHostedGroupHolderCollection() function which flash DB. When DB is flashed getEntityChangeSet() will not work ///////////////
                if(0) {
                    //This caused doctrine problems to persist the Prices->documents
                    $changedInfoArr = $this->setEventLogChanges($entity);
                    //exit('1');
                    //set Edit event log for removed collection and changed fields or added collection
                    if (count($changedInfoArr) > 0 || count($removedHostedGroupHolderCollections) > 0) {
                        $event = "HostedGroupHolder " . $entity->getId() . " has been changed by " . $user . ":" . "<br>";
                        $event = $event . implode("<br>", $changedInfoArr);
                        $event = $event . "<br>" . implode("<br>", $removedHostedGroupHolderCollections);
                        //$userSecUtil = $this->container->get('user_security_utility');
                        //echo "event=".$event."<br>";
                        //print_r($removedCollections);
                        //exit();
                        $userSecUtil->createUserEditEvent($this->getParameter('employees.sitename'), $event, $user, $entity, $request, 'AuthServerNetworkList HostedGroupHolder Updated');
                    }
                }
                //exit();
            }
            /////////// EOF remove prices. Used for RequestCategoryTypeList ///////////


            if( $entity instanceof UsernameType ) {
                $entity->setEmptyAbbreviation();
            }
            
            //increments the version (current +1)
            $currentVersion = $entity->getVersion();
            if( $currentVersion === NULL ) {
                $currentVersion = 1;
            }
            $newVersion = $currentVersion + 1;
            $entity->setVersion($newVersion);

            if( method_exists($entity, "getDocuments") ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
                $em->getRepository(Document::class)->processDocuments($entity, "document");
            }

            if( method_exists($entity, "getVisualInfos") ) {
                foreach( $entity->getVisualInfos() as $visualInfo) {
                    //echo "<br><br>getVisualInfos ID=".$visualInfo->getId().": <br>";
                    $em->getRepository(Document::class)->processDocuments( $visualInfo, "document" );
                }
                //exit('exit visualinfo');
            }

            $em->flush();

            $newName = "Unknown";
            if( method_exists($entity,"getName") ) {
                $newName = $entity->getName();
            }
            $newType = "Unknown";
            if( method_exists($entity,"getType") ) {
                $newType = $entity->getType();
            }
            $newDescription = "Unknown";
            if( method_exists($entity,'getDescription') ) {
                $newDescription = $entity->getDescription();
            }

            $updatedInfo = "";
            if( $newName != $originalName ) {
                $updatedInfo = " original name=$originalName, new name=$newName";
            }
            if( $newType != $originalType ) {
                if( $updatedInfo ) {
                    $updatedInfo = $updatedInfo . ";";
                }
                $updatedInfo = $updatedInfo . " original type=$originalType, new type=$newType";
            }
            if( $newDescription != $originalDescription ) {
                if( $updatedInfo ) {
                    $updatedInfo = $updatedInfo . ";";
                }
                $updatedInfo = $updatedInfo . " original description=$originalDescription, new description=$newDescription";
            }

            if( method_exists($entity,'getEssentialAttributes') ) {
                $newEssentialAttributes = $entity->getEssentialAttributes();
                if( $newEssentialAttributes != $originalEssentialAttributes ) {
                    $updatedInfo = $updatedInfo . "<br> original EssentialAttributes=$originalEssentialAttributes; <br> new EssentialAttributes=$newEssentialAttributes";
                }
            }
            
            if( $updatedInfo ) {
                $updatedInfo = ": ".$updatedInfo;
            }

            //EventLog
            $event = "List '".$newName."' updated by $user" . $updatedInfo;
            $userSecUtil->createUserEditEvent($this->getParameter('employees.sitename'),$event,$user,$entity,$request,'List Updated');

            return $this->redirect($this->generateUrl($pathbase.'_show'.$this->postPath, array('id' => $id)));
        } else {
            //exit("Form is invalid");
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase,
            'sitename' => $this->sitename,
            'cycle' => 'edit'
        );
    }


    ////////////////////////////////////
    public function setEventLogChanges($entity) {

        $em = $this->getDoctrine()->getManager();

        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener

        $eventArr = array();

        //log simple fields
        $changeset = $uow->getEntityChangeSet($entity);
        $eventArr = $this->addChangesToEventLog( $eventArr, $changeset );

        if( method_exists($entity,'getPermissions') ) {
            //permission list
            foreach ($entity->getPermissions() as $subentity) {
                //echo "subentity=".$subentity."<br>";
                $changeset = $uow->getEntityChangeSet($subentity);
                $text = "(" . "permission " . $this->getEntityId($subentity) . ")";
                //print_r($changeset);
                $eventArr = $this->addChangesToEventLog($eventArr, $changeset, $text);

                //add current object state
                $eventArr[] = "Final state: " . $subentity;
            }
        }

        return $eventArr;
    }
    public function removeCollection($originalArr,$currentArr,$entity) {
        $em = $this->getDoctrine()->getManager();
        $removeArr = array();

        foreach( $originalArr as $element ) {
            if( false === $currentArr->contains($element) ) {
                $removeArr[] = "<strong>"."Removed: ".$element." ".$this->getEntityId($element)."</strong>";
                $entity->removePermission($element);
                $element->setRole(NULL);
                $em->persist($element);
                $em->remove($element);
            }
        } //foreach

        return implode("<br>", $removeArr);
    }
    public function removeVisualInfoCollection($originalArr,$currentArr,$entity) {
        $em = $this->getDoctrine()->getManager();
        $removeArr = array();

        foreach( $originalArr as $element ) {
            if( false === $currentArr->contains($element) ) {
                $removeArr[] = "<strong>"."Removed: ".$element." ".$this->getEntityId($element)."</strong>";
                $entity->removeVisualInfo($element);
                $element->setAntibody(NULL);
                $em->persist($element);
                $em->remove($element);
            }
        } //foreach

        return implode("<br>", $removeArr);
    }
    public function removePriceCollection($originalArr,$currentArr,$entity) {
        $em = $this->getDoctrine()->getManager();
        $removeArr = array();

        foreach( $originalArr as $element ) {
            if( false === $currentArr->contains($element) ) {
                $removeArr[] = "<strong>"."Removed: ".$element." ".$this->getEntityId($element)."</strong>";
                $entity->removePrice($element);
                $element->setRequestCategoryType(NULL);
                $em->persist($element);
                $em->remove($element);
            }
        } //foreach

        return implode("<br>", $removeArr);
    }
    public function removeHostedGroupHolderCollection($originalArr,$currentArr,$entity) {
        $em = $this->getDoctrine()->getManager();
        $removeArr = array();

        foreach( $originalArr as $element ) {
            if( false === $currentArr->contains($element) ) {
                $removeArr[] = "<strong>"."Removed: ".$element." ".$this->getEntityId($element)."</strong>";
                $entity->removeHostedGroupHolder($element);
                $element->setServerNetwork(NULL);
                $em->persist($element);
                $em->remove($element);
            }
        } //foreach

        return implode("<br>", $removeArr);
    }
    public function addChangesToEventLog( $eventArr, $changeset, $text="" ) {

        $changeArr = array();

        //process $changeset: author, subjectuser, oldvalue, newvalue
        foreach( $changeset as $key => $value ) {
            if( $value[0] != $value[1] ) {

                if( is_object($key) ) {
                    //if $key is object then skip it, because we don't want to have non-informative record such as: credentials(stateLicense New): old value=, new value=Credentials
                    continue;
                }

                $field = $key;

                $oldValue = $value[0];
                $newValue = $value[1];

                if( $oldValue instanceof \DateTime ) {
                    $oldValue = $this->convertDateTimeToStr($value[0]);
                }
                if( $newValue instanceof \DateTime ) {
                    $newValue = $this->convertDateTimeToStr($value[1]);
                }

                if( is_array($oldValue) ) {
                    $oldValue = implode(",",$oldValue);
                }
                if( is_array($newValue) ) {
                    $newValue = implode(",",$newValue);
                }

                $event = "<strong>".$field.$text."</strong>".": "."old value=".$oldValue.", new value=".$newValue;
                //echo "event=".$event."<br>";
                //exit();

                $changeArr[] = $event;
            }
        }

        if( count($changeArr) > 0 ) {
            $eventArr[] = implode("<br>", $changeArr);
        }

        return $eventArr;

    }

    public function convertDateTimeToStr($datetime) {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        $dateStr = $transformer->transform($datetime);
        return $dateStr;
    }
    public function getEntityId($entity) {
        if( $entity->getId() ) {
            return "ID=".$entity->getId();
        }
        return "New";
    }
    ////////////////////////////////////








    //////////////////////// Tree //////////////////////////////
//    /**
//     * Displays a form to create a new entity with parent.
//     *
//     * @Route("/department/new/parent/{pid}", name="departments_new_with_parent")
//     * @Route("/division/new/parent/{pid}", name="divisions_new_with_parent")
//     * @Route("/service/new/parent/{pid}", name="services_new_with_parent")
//     * @Route("/service/new/parent/{pid}", name="services_new_with_parent")
//     * @Template("AppUserdirectoryBundle/ListForm/new.html.twig")
//     */
//    public function newNodeWithParentAction(Request $request,$pid)
//    {
//        return $this->newList($request,$pid);
//    }

    public function getParentName( $className ) {

        //echo "className=".$className."<br>";

        switch( $className ) {
//            case "Department":
//                $parentClassName = "Institution";
//                break;
//            case "Division":
//                $parentClassName = "Department";
//                break;
//            case "Service":
//                $parentClassName = "Division";
//                break;
            case "FellowshipSubspecialty":
                $parentClassName = "ResidencySpecialty";
                break;
            default:
                //$parentClassName = null;
                return null;
        }

        $res = array();
        $res['className'] = $parentClassName;
        $res['fullClassName'] = "App\\UserdirectoryBundle\\Entity\\".$className;
        $res['bundleName'] = "UserdirectoryBundle";

        return $res;
    }
    //////////////////////// EOF tree //////////////////////////////











    public function classListMapper( $route, $request ) {

        $labels = null;

        $bundleName = "UserdirectoryBundle";

        //regular lists
        if (strpos((string)$route, "-") !== false) {
            //sites-list
            $pieces = explode("-", $route);
            $route = $pieces[0];
        }
        if (strpos((string)$route, "_") !== false) {
            //sites_show
            $pieces = explode("_", $route);
            $route = $pieces[0];
        }
        //echo "route search=".$route."<br>";

        switch( $route ) {

            case "platformlistmanager":
                $className = "PlatformListManagerRootList";
                $displayName = "Platform List Manager Root List";
                break;
            case "sourcesystems":
                $className = "SourceSystemList";
                $displayName = "Systems";
                break;
            case "role":
                $className = "Roles";
                $displayName = "Roles";
                //$labels = array('description'=>'Explanation of Capabilities:');
                break;
            case "institutions":
                $className = "Institution";
                $displayName = "Institutions";
                break;
            case "states":
                $className = "States";
                $displayName = "States";
                break;
            case "countries":
                $className = "Countries";
                $displayName = "Countries";
                break;
            case "boardcertifications":
                $className = "BoardCertifiedSpecialties";
                $displayName = "Pathology Board Certified Specialties";
                break;
            case "employmenttypes":
                $className = "EmploymentType";
                $displayName = "Employment Types";
                break;
            case "employmentterminations":
                $className = "EmploymentTerminationType";
                $displayName = "Employment Types of Termination";
                break;
            case "loggereventtypes":
                $className = "EventTypeList";
                $displayName = "Event Log Types";
                break;
            case "viewmodes":
                $className = "ViewModeList";
                $displayName = "View Mode List";
                break;
            case "usernametypes":
                $className = "UsernameType";
                $displayName = "Primary Public User ID Types";
                break;
            case "identifiers":
                $className = "IdentifierTypeList";
                $displayName = "Identifier Types";
                break;
            case "residencytracks":
                $className = "ResidencyTrackList";
                $displayName = "Residency Tracks";
                break;
            case "fellowshiptypes":
                $className = "FellowshipTypeList";
                $displayName = "Fellowship Types";
                break;
//            case "researchlabs":
//                $className = "ResearchLab";
//                $displayName = "Research Labs";
//                break;
            case "locationtypes":
                $className = "LocationTypeList";
                $displayName = "Location Types";
                break;
            case "equipments":
                $className = "Equipment";
                $displayName = "Equipment";
                break;
            case "equipmenttypes":
                $className = "EquipmentType";
                $displayName = "Equipment Types";
                break;
            case "locationprivacy":
                $className = "LocationPrivacyList";
                $displayName = "Location Privacy Types";
                break;
            case "roleattributes":
                $className = "RoleAttributeList";
                $displayName = "Role Attributes";
                //$labels = array('description'=>'Explanation of Capabilities:');
                break;
            case "buildings":
                $className = "BuildingList";
                $displayName = "Buildings";
                break;
            case "rooms":
                $className = "RoomList";
                $displayName = "Rooms";
                break;
            case "suites":
                $className = "SuiteList";
                $displayName = "Suites";
                break;
            case "floors":
                $className = "FloorList";
                $displayName = "Floors";
                break;
            case "grants":
                $className = "Grant";
                $displayName = "Grants";
                break;
            case "mailboxes":
                $className = "MailboxList";
                $displayName = "Mailboxes";
                break;
            case "efforts":
                $className = "EffortList";
                $displayName = "Percent Efforts";
                break;
            case "admintitles":
                $className = "AdminTitleList";
                $displayName = "Administrative Titles";
                break;
            case "apptitles":
                $className = "AppTitleList";
                $displayName = "Academic Appointment Titles";
                break;
            case "completionreasons":
                $className = "CompletionReasonList";
                $displayName = "Training Completion Reasons";
                break;
            case "trainingdegrees":
                $className = "TrainingDegreeList";
                $displayName = "Training Degrees";
                break;
            case "trainingmajors":
                $className = "MajorTrainingList";
                $displayName = "Training Majors";
                break;
            case "trainingminors":
                $className = "MinorTrainingList";
                $displayName = "Training Minors";
                break;
            case "traininghonors":
                $className = "HonorTrainingList";
                $displayName = "Training Honors";
                break;
            case "fellowshiptitles":
                $className = "FellowshipTitleList";
                $displayName = "Professional Fellowship Titles";
                break;
            case "residencyspecialtys":
                $className = "ResidencySpecialty";
                $displayName = "Residency Specialties";
                break;
            case "fellowshipsubspecialtys":
                $className = "FellowshipSubspecialty";
                $displayName = "Fellowship Subspecialties";
                break;
            case "institutiontypes":
                $className = "InstitutionType";
                $displayName = "Institution Types";
                break;
            case "documenttypes":
                $className = "DocumentTypeList";
                $displayName = "Document Types";
                break;
            case "medicaltitles":
                $className = "MedicalTitleList";
                $displayName = "Medical Titles";
                break;
            case "medicalspecialties":
                $className = "MedicalSpecialties";
                $displayName = "Medical Specialties";
                break;
            case "sourceorganizations":
                $className = "SourceOrganization";
                $displayName = "Grant Source Organizations (Sponsors)";
                break;
            case "languages":
                $className = "LanguageList";
                $displayName = "Languages";
                break;
            case "locales":
                $className = "LocaleList";
                $displayName = "Locales";
                break;
            case "importances":
                $className = "ImportanceList";
                $displayName = "Ranks of Importance";
                break;
            case "authorshiproles":
                $className = "AuthorshipRoles";
                $displayName = "Authorship Roles";
                break;
            case "organizations":
                $className = "OrganizationList";
                $displayName = "Lecture Venues";
                break;
            case "cities":
                $className = "CityList";
                $displayName = "City";
                break;
            case "linktypes":
                $className = "LinkTypeList";
                $displayName = "Link Types";
                break;
            case "sexes":
                $className = "SexList";
                $displayName = "Genders";
                break;
            case "positiontypes":
                $className = "PositionTypeList";
                $displayName = "Position Types";
                break;
            case "organizationalgrouptypes":
                $className = "OrganizationalGroupType";
                $displayName = "Organizational Group Types";
                break;
            case "commenttypes":
                $className = "CommentTypeList";
                $displayName = "Comment Types";
                break;
            case "commentgrouptypes":
                $className = "CommentGroupType";
                $displayName = "Profile Comment Group Types";
                break;
            case "userwrappers":
                $className = "UserWrapper";
                $displayName = "User Wrappers";
                break;
            case "spotpurposes":
                $className = "SpotPurpose";
                $displayName = "Spot Purposes";
                break;
            case "medicalstatuses":
                $className = "MedicalLicenseStatus";
                $displayName = "Medical License Statuses";
                break;
            case "certifyingboardorganizations":
                $className = "CertifyingBoardOrganization";
                $displayName = "Certifying Board Organizations";
                break;
            case "trainingtypes":
                $className = "TrainingTypeList";
                $displayName = "Training Types";
                break;
            case "joblists":
                $className = "JobTitleList";
                $displayName = "Job or Experience Title";
                break;
            case "fellappstatuses":
                $className = "FellAppStatus";
                $displayName = "Fellowship Application Statuses";
                $bundleName = "FellAppBundle";
                break;
            case "globalfellowshipspecialty":
                $className = "GlobalFellowshipSpecialty";
                $displayName = "Global Fellowship Specialty";
                $bundleName = "FellAppBundle";
                break;
            case "fellappranks":
                $className = "FellAppRank";
                $displayName = "Fellowship Application Score";
                $bundleName = "FellAppBundle";
                break;
            case "fellapplanguageproficiency":
                $className = "LanguageProficiency";
                $displayName = "Fellowship Application Language Proficiencies";
                $bundleName = "FellAppBundle";
                break;

            case "resappstatuses":
                $className = "ResAppStatus";
                $displayName = "Residency Application Statuses";
                $bundleName = "ResAppBundle";
                break;
            case "resappranks":
                $className = "ResAppRank";
                $displayName = "Residency Application Score";
                $bundleName = "ResAppBundle";
                break;
            case "resapplanguageproficiency":
                $className = "LanguageProficiency";
                $displayName = "Residency Application Language Proficiencies";
                $bundleName = "ResAppBundle";
                break;
            case "resappfitforprogram":
                $className = "ResAppFitForProgram";
                $displayName = "Residency Application Fit for Program";
                $bundleName = "ResAppBundle";
                break;
            case "resappvisastatus":
                $className = "VisaStatus";
                $displayName = "Residency Visa Status";
                $bundleName = "ResAppBundle";
                break;
            case "postsoph":
                $className = "PostSophList";
                $displayName = "Post Soph List";
                $bundleName = "ResAppBundle";
                break;

            case "resappapplyingresidencytrack":
                $className = "ApplyingResidencyTrack";
                $displayName = "Applying Residency Track";
                $bundleName = "ResAppBundle";
                break;
            case "resapplearnarealist":
                $className = "LearnAreaList";
                $displayName = "Learn Area List";
                $bundleName = "ResAppBundle";
                break;
            case "resappspecificindividuallist":
                $className = "SpecificIndividualList";
                $displayName = "Specific Individuals Meet List";
                $bundleName = "ResAppBundle";
                break;

//            case "collaborations":
//                $className = "Collaboration";
//                $displayName = "Collaborations";
//                break;
            case "collaborationtypes":
                $className = "CollaborationTypeList";
                $displayName = "Collaboration Types";
                break;
            case "permission":
                $className = "PermissionList";
                $displayName = "Permissions List";
                break;
            case "permissionobject":
                $className = "PermissionObjectList";
                $displayName = "Permission Objects List";
                break;
            case "permissionaction":
                $className = "PermissionActionList";
                $displayName = "Permission Actions List";
                break;
            case "sites":
                $className = "SiteList";
                $displayName = "Sites List";
                break;
            case "eventobjecttypes":
                $className = "EventObjectTypeList";
                $displayName = "Event Log Object Types";
                break;
            case "vacreqrequesttypes":
                $className = "VacReqRequestTypeList";
                $displayName = "Business/Vacation Request Types";
                $bundleName = "VacReqBundle";
                break;
            case "vacreqfloatingtexts":
                $className = "VacReqFloatingTextList";
                $displayName = "Vacation Request Floating Text List";
                $bundleName = "VacReqBundle";
                break;
            case "vacreqfloatingtypes":
                $className = "VacReqFloatingTypeList";
                $displayName = "Vacation Request Floating Type List";
                $bundleName = "VacReqBundle";
                break;
            case "vacreqapprovaltypes":
                $className = "VacReqApprovalTypeList";
                $displayName = "Vacation Request Approval Type List";
                $bundleName = "VacReqBundle";
                break;

            case "vacreqholidays":
                $className = "VacReqHolidayList";
                $displayName = "Vacation Request Holidays List";
                $bundleName = "VacReqBundle";
                break;
            case "vacreqobservedholidays":
                $className = "VacReqObservedHolidayList";
                $displayName = "Vacation Request Observed Holidays List";
                $bundleName = "VacReqBundle";
                break;

            case "authusergroup":
                $className = "AuthUserGroupList";
                $displayName = "Dual Authentication User Group List";
                break;
            case "authservernetwork":
                $className = "AuthServerNetworkList";
                $displayName = "Dual Authentication Server Network Accessibility and Role";
                break;
            case "authpartnerserver":
                $className = "AuthPartnerServerList";
                $displayName = "Dual Authentication Tandem Partner Server URL";
                break;
//            case "hostedusergroups":
//                $className = "HostedUserGroupList";
//                $displayName = "Hosted User Groups";
//                break;
            case "tenanturls":
                $className = "TenantUrlList";
                $displayName = "Tenant Urls";
                break;

            case "healthcareproviderspecialty":
                $className = "HealthcareProviderSpecialtiesList";
                $displayName = "Healthcare Provider Specialties";
                break;
            case "healthcareprovidercommunication":
                $className = "HealthcareProviderCommunicationList";
                $displayName = "Healthcare Provider Initial Communications";
                break;
            case "objecttypes":
                $className = "ObjectTypeList";
                $displayName = "Object Types";
                break;
            case "formnodes":
                $className = "FormNode";
                $displayName = "Form Nodes";
                break;
            case "objecttypetexts":
                $className = "ObjectTypeText";
                $displayName = "Object Type Text";
                break;
            case "bloodproducttransfusions":
                $className = "BloodProductTransfusedList";
                $displayName = "Blood Product Transfused List";
                break;
            case "transfusionreactiontypes":
                $className = "TransfusionReactionTypeList";
                $displayName = "Transfusion Reaction Type";
                break;
            case "objecttypestrings":
                $className = "ObjectTypeString";
                $displayName = "Object Type String";
                break;
            case "objecttypedropdowns":
                $className = "ObjectTypeDropdown";
                $displayName = "Object Type Dropdown";
                break;
            case "bloodtypes":
                $className = "BloodTypeList";
                $displayName = "Blood Type List";
                break;
            case "additionalcommunications":
                $className = "AdditionalCommunicationList";
                $displayName = "Additional Communication List";
                break;
            case "transfusionantibodyscreenresults":
                $className = "TransfusionAntibodyScreenResultsList";
                $displayName = "Transfusion Antibody Screen Results List";
                break;
            case "transfusioncrossmatchresults":
                $className = "TransfusionCrossmatchResultsList";
                $displayName = "Transfusion Crossmatch Results List";
                break;
            case "transfusiondatresults":
                $className = "TransfusionDATResultsList";
                $displayName = "Transfusion DAT Results List";
                break;
            case "transfusionhemolysischeckresults":
                $className = "TransfusionHemolysisCheckResultsList";
                $displayName = "Transfusion Hemolysis Check Results List";
                break;
            case "objecttypedatetimes":
                $className = "ObjectTypeDateTime";
                $displayName = "Object Type DateTime";
                break;
            case "complexplateletsummaryantibodies":
                $className = "ComplexPlateletSummaryAntibodiesList";
                $displayName = "Complex Platelet Summary Antibodies List";
                break;
            case "cciunitplateletcountdefaultvalues":
                $className = "CCIUnitPlateletCountDefaultValueList";
                $displayName = "CCI Unit Platelet Count Default Value List";
                break;
            case "cciplatelettypetransfuseds":
                $className = "CCIPlateletTypeTransfusedList";
                $displayName = "CCI Platelet Type Transfused List";
                break;
            case "platelettransfusionproductreceivings":
                $className = "PlateletTransfusionProductReceivingList";
                $displayName = "Platelet Transfusion Product Receiving List";
                break;
            case "transfusionproductstatus":
                $className = "TransfusionProductStatusList";
                $displayName = "Transfusion Product Status List";
                break;
            case "weekdays":
                $className = "WeekDaysList";
                $displayName = "Days of the Week List";
                break;
            case "months":
                $className = "MonthsList";
                $displayName = "Months List";
                break;
            case "clericalerrors":
                $className = "ClericalErrorList";
                $displayName = "Clerical Error List";
                break;
            case "labresultnames":
                $className = "LabResultNameList";
                $displayName = "Lab Result Names";
                break;
            case "labresultunitsmeasures":
                $className = "LabResultUnitsMeasureList";
                $displayName = "Lab Result Units of Measure List";
                break;
            case "labresultflags":
                $className = "LabResultFlagList";
                $displayName = "Lab Result Flag List";
                break;
            case "pathologyresultsignatories":
                $className = "PathologyResultSignatoriesList";
                $displayName = "Pathology Result Signatories List";
                break;
            case "objecttypecheckboxs":
                $className = "ObjectTypeCheckbox";
                $displayName = "Object Type Checkbox";
                break;
            case "objecttyperadiobuttons":
                $className = "ObjectTypeRadioButton";
                $displayName = "Object Type Radio Button";
                break;
            case "lifeforms":
                $className = "LifeFormList";
                $displayName = "Life Form";
                break;
            case "positiontracktypes":
                $className = "PositionTrackTypeList";
                $displayName = "Position Track Type List";
                break;
            case "transresprojectspecialties":
                $className = "SpecialtyList";
                $displayName = "Translational Research Project Specialty List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transrespricetypes":
                $className = "PriceTypeList";
                $displayName = "Translational Research Price Type List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transresprojecttypes":
                $className = "ProjectTypeList";
                $displayName = "Translational Research Project Type List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transresrequestcategorytypes":
            case "translationalresearchfeesschedule":
                $className = "RequestCategoryTypeList";
                $displayName = "Translational Research Request Products/Services (Fee Schedule) List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transresirbapprovaltypes":
                $className = "IrbApprovalTypeList";
                $displayName = "Translational Research Irb Approval Type List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transrestissueprocessingservices":
                $className = "TissueProcessingServiceList";
                $displayName = "Translational Research Tissue Processing Service List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transresotherrequestedservices":
                $className = "OtherRequestedServiceList";
                $displayName = "Translational Research Other Requested Service List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transresbusinesspurposes":
                $className = "BusinessPurposeList";
                $displayName = "Translational Research Work Request Business Purposes";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "antibodies":
                $className = "AntibodyList";
                $displayName = "Antibody List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transrescolllabs":
                $className = "CollLabList";
                $displayName = "Translational Research Collaboration Laboratory List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transrescolldivs":
                $className = "CollDivList";
                $displayName = "Translational Research Collaboration Division List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transresirbstatus":
                $className = "IrbStatusList";
                $displayName = "Translational Research Irb Approval Status List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transresrequestergroup":
                $className = "RequesterGroupList";
                $displayName = "Translational Research Requester Group List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transrescomptypes":
                $className = "CompCategoryList";
                $displayName = "Translational Research Computational Categories List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "antibodycategorytag":
                $className = "AntibodyCategoryTagList";
                $displayName = "Translational Research Antibody Category Tag List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "antibodylabs":
                $className = "AntibodyLabList";
                $displayName = "Translational Research Antibody Lab List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "antibodypanels":
                $className = "AntibodyPanelList";
                $displayName = "Translational Research Antibody Panel List";
                $bundleName = "TranslationalResearchBundle";
                break;

            case "workqueuetypes":
                $className = "WorkQueueList";
                $displayName = "Work Queue Type List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "orderablestatus":
                $className = "OrderableStatusList";
                $displayName = "Orderable Status List";
                $bundleName = "TranslationalResearchBundle";
                break;

            case "visastatus":
                $className = "VisaStatus";
                $displayName = "Visa Status";
                $bundleName = "FellAppBundle";
                break;
//            case "crnentrytags":
//                $className = "CrnEntryTagsList";
//                $displayName = "Crn Entry Tags List";
//                $bundleName = "CrnBundle";
//                break;
            //FellApp form nodes
            case "trainingeligibility":
                $className = "TrainingEligibilityList";
                $displayName = "Training Eligibility List";
                $bundleName = "FellAppBundle";
                break;
            case "dutiescapability":
                $className = "DutiesCapabilityList";
                $displayName = "Duties Capability List";
                $bundleName = "FellAppBundle";
                break;
            case "phdfield":
                $className = "PhdFieldList";
                $displayName = "Phd Field List";
                $bundleName = "FellAppBundle";
                break;


            //Dashboards (7 lists)
            case "charttypes":
                $className = "ChartTypeList";
                $displayName = "Chart Type List";
                $bundleName = "DashboardBundle";
                break;
            case "charttopics":
                $className = "TopicList";
                $displayName = "Chart Topic List";
                $bundleName = "DashboardBundle";
                break;
            case "chartfilters":
                $className = "FilterList";
                $displayName = "Chart Filter List";
                $bundleName = "DashboardBundle";
                break;
            case "charts":
                $className = "ChartList";
                $displayName = "Chart Type List";
                $bundleName = "DashboardBundle";
                break;
            case "chartdatasources":
                $className = "DataSourceList";
                $displayName = "Chart Data Source List";
                $bundleName = "DashboardBundle";
                break;
            case "chartupdatefrequencies":
                $className = "UpdateFrequencyList";
                $displayName = "Chart Update Frequency List";
                $bundleName = "DashboardBundle";
                break;
            case "chartvisualizations":
                $className = "VisualizationList";
                $displayName = "Chart Visualization List";
                $bundleName = "DashboardBundle";
                break;

            case "transferstatus":
                $className = "TransferStatusList";
                $displayName = "Transfer Status List";
                break;
            case "interfacetransfers":
                $className = "InterfaceTransferList";
                $displayName = "Interface Transfer List";
                break;

            case "samlconfig":
                $className = "SamlConfig";
                $displayName = "Saml Configuration List";
                $bundleName = "Saml";
                break;


            case "custom000":
                $className = "Custom000List";
                $displayName = "Custom000 List";
                break;
            case "custom001":
                $className = "Custom001List";
                $displayName = "Custom001 List";
                break;
            case "custom002":
                $className = "Custom002List";
                $displayName = "Custom002 List";
                break;
            case "custom003":
                $className = "Custom003List";
                $displayName = "Custom003 List";
                break;
            case "custom004":
                $className = "Custom004List";
                $displayName = "Custom004 List";
                break;
            case "custom005":
                $className = "Custom005List";
                $displayName = "Custom005 List";
                break;
            case "custom006":
                $className = "Custom006List";
                $displayName = "Custom006 List";
                break;
            case "custom007":
                $className = "Custom007List";
                $displayName = "Custom007 List";
                break;
            case "custom008":
                $className = "Custom008List";
                $displayName = "Custom008 List";
                break;
            case "custom009":
                $className = "Custom009List";
                $displayName = "Custom009 List";
                break;
            case "custom010":
                $className = "Custom010List";
                $displayName = "Custom010 List";
                break;
            case "custom011":
                $className = "Custom011List";
                $displayName = "Custom011 List";
                break;
            case "custom012":
                $className = "Custom012List";
                $displayName = "Custom012 List";
                break;
            case "custom013":
                $className = "Custom013List";
                $displayName = "Custom013 List";
                break;
            case "custom014":
                $className = "Custom014List";
                $displayName = "Custom014 List";
                break;
            case "custom015":
                $className = "Custom015List";
                $displayName = "Custom015 List";
                break;
            case "custom016":
                $className = "Custom016List";
                $displayName = "Custom016 List";
                break;
            case "custom017":
                $className = "Custom017List";
                $displayName = "Custom017 List";
                break;
            case "custom018":
                $className = "Custom018List";
                $displayName = "Custom018 List";
                break;
            case "custom019":
                $className = "Custom019List";
                $displayName = "Custom019 List";
                break;
            case "custom020":
                $className = "Custom020List";
                $displayName = "Custom020 List";
                break;
            case "custom021":
                $className = "Custom021List";
                $displayName = "Custom021 List";
                break;
            case "custom022":
                $className = "Custom022List";
                $displayName = "Custom022 List";
                break;
            case "custom023":
                $className = "Custom023List";
                $displayName = "Custom023 List";
                break;
            case "custom024":
                $className = "Custom024List";
                $displayName = "Custom024 List";
                break;
            case "custom025":
                $className = "Custom025List";
                $displayName = "Custom025 List";
                break;
            case "custom026":
                $className = "Custom026List";
                $displayName = "Custom026 List";
                break;
            case "custom027":
                $className = "Custom027List";
                $displayName = "Custom027 List";
                break;
            case "custom028":
                $className = "Custom028List";
                $displayName = "Custom028 List";
                break;
            case "custom029":
                $className = "Custom029List";
                $displayName = "Custom029 List";
                break;
            case "custom030":
                $className = "Custom030List";
                $displayName = "Custom030 List";
                break;
            case "custom031":
                $className = "Custom031List";
                $displayName = "Custom031 List";
                break;
            case "custom032":
                $className = "Custom032List";
                $displayName = "Custom0032 List";
                break;
            case "custom033":
                $className = "Custom033List";
                $displayName = "Custom033 List";
                break;
            case "custom034":
                $className = "Custom034List";
                $displayName = "Custom034 List";
                break;
            case "custom035":
                $className = "Custom035List";
                $displayName = "Custom035 List";
                break;
            case "custom036":
                $className = "Custom036List";
                $displayName = "Custom036 List";
                break;
            case "custom037":
                $className = "Custom037List";
                $displayName = "Custom037 List";
                break;
            case "custom038":
                $className = "Custom038List";
                $displayName = "Custom038 List";
                break;
            case "custom039":
                $className = "Custom039List";
                $displayName = "Custom039 List";
                break;
            case "custom040":
                $className = "Custom040List";
                $displayName = "Custom040 List";
                break;
            case "custom041":
                $className = "Custom041List";
                $displayName = "Custom041 List";
                break;
            case "custom042":
                $className = "Custom042List";
                $displayName = "Custom042 List";
                break;
            case "custom043":
                $className = "Custom043List";
                $displayName = "Custom043 List";
                break;
            case "custom044":
                $className = "Custom044List";
                $displayName = "Custom044 List";
                break;
            case "custom045":
                $className = "Custom045List";
                $displayName = "Custom045 List";
                break;
            case "custom046":
                $className = "Custom046List";
                $displayName = "Custom046 List";
                break;
            case "custom047":
                $className = "Custom047List";
                $displayName = "Custom047 List";
                break;
            case "custom048":
                $className = "Custom048List";
                $displayName = "Custom048 List";
                break;
            case "custom049":
                $className = "Custom049List";
                $displayName = "Custom049 List";
                break;
            case "custom050":
                $className = "Custom050List";
                $displayName = "Custom050 List";
                break;
            case "custom051":
                $className = "Custom051List";
                $displayName = "Custom051 List";
                break;
            case "custom052":
                $className = "Custom052List";
                $displayName = "Custom052 List";
                break;
            case "custom053":
                $className = "Custom053List";
                $displayName = "Custom053 List";
                break;
            case "custom054":
                $className = "Custom054List";
                $displayName = "Custom054 List";
                break;
            case "custom055":
                $className = "Custom055List";
                $displayName = "Custom055 List";
                break;
            case "custom056":
                $className = "Custom056List";
                $displayName = "Custom056 List";
                break;
            case "custom057":
                $className = "Custom057List";
                $displayName = "Custom057 List";
                break;
            case "custom058":
                $className = "Custom058List";
                $displayName = "Custom058 List";
                break;
            case "custom059":
                $className = "Custom059List";
                $displayName = "Custom059 List";
                break;
            case "custom060":
                $className = "Custom060List";
                $displayName = "Custom060 List";
                break;
            case "custom061":
                $className = "Custom061List";
                $displayName = "Custom061 List";
                break;
            case "custom062":
                $className = "Custom062List";
                $displayName = "Custom062 List";
                break;
            case "custom063":
                $className = "Custom063List";
                $displayName = "Custom063 List";
                break;
            case "custom064":
                $className = "Custom064List";
                $displayName = "Custom064 List";
                break;
            case "custom065":
                $className = "Custom065List";
                $displayName = "Custom065 List";
                break;
            case "custom066":
                $className = "Custom066List";
                $displayName = "Custom066 List";
                break;
            case "custom067":
                $className = "Custom067List";
                $displayName = "Custom067 List";
                break;
            case "custom068":
                $className = "Custom068List";
                $displayName = "Custom068 List";
                break;
            case "custom069":
                $className = "Custom069List";
                $displayName = "Custom069 List";
                break;
            case "custom070":
                $className = "Custom070List";
                $displayName = "Custom070 List";
                break;
            case "custom071":
                $className = "Custom071List";
                $displayName = "Custom071 List";
                break;
            case "custom072":
                $className = "Custom072List";
                $displayName = "Custom072 List";
                break;
            case "custom073":
                $className = "Custom073List";
                $displayName = "Custom073 List";
                break;
            case "custom074":
                $className = "Custom074List";
                $displayName = "Custom074 List";
                break;
            case "custom075":
                $className = "Custom075List";
                $displayName = "Custom075 List";
                break;
            case "custom076":
                $className = "Custom076List";
                $displayName = "Custom076 List";
                break;
            case "custom077":
                $className = "Custom077List";
                $displayName = "Custom077 List";
                break;
            case "custom078":
                $className = "Custom078List";
                $displayName = "Custom078 List";
                break;
            case "custom079":
                $className = "Custom079List";
                $displayName = "Custom079 List";
                break;
            case "custom080":
                $className = "Custom080List";
                $displayName = "Custom080 List";
                break;
            case "custom081":
                $className = "Custom081List";
                $displayName = "Custom081 List";
                break;
            case "custom082":
                $className = "Custom082List";
                $displayName = "Custom082 List";
                break;
            case "custom083":
                $className = "Custom083List";
                $displayName = "Custom083 List";
                break;
            case "custom084":
                $className = "Custom084List";
                $displayName = "Custom084 List";
                break;
            case "custom085":
                $className = "Custom085List";
                $displayName = "Custom085 List";
                break;
            case "custom086":
                $className = "Custom086List";
                $displayName = "Custom086 List";
                break;
            case "custom087":
                $className = "Custom087List";
                $displayName = "Custom087 List";
                break;
            case "custom088":
                $className = "Custom088List";
                $displayName = "Custom088 List";
                break;
            case "custom089":
                $className = "Custom089List";
                $displayName = "Custom089 List";
                break;
            case "custom090":
                $className = "Custom090List";
                $displayName = "Custom090 List";
                break;
            case "custom091":
                $className = "Custom091List";
                $displayName = "Custom091 List";
                break;
            case "custom092":
                $className = "Custom092List";
                $displayName = "Custom092 List";
                break;
            case "custom093":
                $className = "Custom093List";
                $displayName = "Custom093 List";
                break;
            case "custom094":
                $className = "Custom094List";
                $displayName = "Custom094 List";
                break;
            case "custom095":
                $className = "Custom095List";
                $displayName = "Custom095 List";
                break;
            case "custom096":
                $className = "Custom096List";
                $displayName = "Custom096 List";
                break;
            case "custom097":
                $className = "Custom097List";
                $displayName = "Custom097 List";
                break;
            case "custom098":
                $className = "Custom098List";
                $displayName = "Custom098 List";
                break;
            case "custom099":
                $className = "Custom099List";
                $displayName = "Custom099 List";
                break;

//            case "employees_locations":
//                $className = "Location";
//                $displayName = "Locations";
//                break;

//            case "messagetypeclassifiers":
//                $className = "MessageTypeClassifiers";
//                $displayName = "Message Type Classifiers";
//                $bundleName = "OrderformBundle";
//                break;

            default:
                $className = null;
                $displayName = null;
                $labels = null;
        }

        if( !$className ) {
            //try ScanListController->classListMapper
            $scanListController = new ScanListController();
            $mapper = $scanListController->classListMapper($route,$request);
            $className = $mapper['className'];
            $bundleName = $mapper['bundleName'];
            $displayName = $mapper['displayName'];
            $bundleName = str_replace("App","",$bundleName);
        }

        //echo $route.": className=".$className.", displayName=".$displayName."<br>";

        $res = array();
        $res['className'] = $className;
        $res['fullClassName'] = "App\\".$bundleName."\\Entity\\".$className;
        $res['entityNamespace'] = "App\\".$bundleName."\\Entity";
        $res['bundleName'] = $bundleName;
        $res['displayName'] = $displayName . ", class: [" . $className . "]";
        //$res['labels'] = $labels;

        //check parent name
        $parentMapper = $this->getParentName($className);
        if( $parentMapper ) {
            $res['parentClassName'] = $parentMapper['className'];
        }

        //get linkId
        $res['linkToListId'] = null;
        if( $className ) {
            //$routeName = $request->get('_route');
            $em = $this->getDoctrine()->getManager();
            //$rootList = $em->getRepository('AppUserdirectoryBundle:PlatformListManagerRootList')->findOneByListId($listId);
            //$rootList = $em->getRepository('AppUserdirectoryBundle:PlatformListManagerRootList')->findOneByListRootName($routeName);
            $rootList = $em->getRepository(PlatformListManagerRootList::class)->findOneByListName($className);
//            if( !$rootList ) {
//                throw $this->createNotFoundException('Unable to find PlatformListManagerRootList by listName=' . $className);
//            }
            if( $rootList ) {
                $linkToListId = $rootList->getLinkToListId();
                //echo "linkToListId=$linkToListId<br>";
                if ($linkToListId) {
                    $res['linkToListId'] = $linkToListId;
                }
            } else {
                $res['linkToListId'] = null;
            }

        }

        return $res;
    }



    /////////////////// DELETE IS NOT USED /////////////////////////
    /**
     * Deletes a entity.
     *
     *
     *
     *
     *
     *
     *
     *
     */
    #[Route(path: '/list-manager/{id}', name: 'platformlistmanager_delete', methods: ['DELETE'])]
    #[Route(path: '/list/source-systems/{id}', name: 'sourcesystems_delete', methods: ['DELETE'])]
    #[Route(path: '/list/roles/{id}', name: 'role_delete', methods: ['DELETE'])]
    #[Route(path: '/list/institutions/{id}', name: 'institutions_delete', methods: ['DELETE'])]
    #[Route(path: '/list/states/{id}', name: 'states_delete', methods: ['DELETE'])]
    #[Route(path: '/list/countries/{id}', name: 'countries_delete', methods: ['DELETE'])]
    #[Route(path: '/list/board-certifications/{id}', name: 'boardcertifications_delete', methods: ['DELETE'])]
    #[Route(path: '/list/employment-termination-reasons/{id}', name: 'employmentterminations_delete', methods: ['DELETE'])]
    #[Route(path: '/list/event-log-event-types/{id}', name: 'loggereventtypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/primary-public-user-id-types/{id}', name: 'usernametypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/identifier-types/{id}', name: 'identifiers_delete', methods: ['DELETE'])]
    #[Route(path: '/list/residency-tracks/{id}', name: 'residencytracks_delete', methods: ['DELETE'])]
    #[Route(path: '/list/fellowship-types/{id}', name: 'fellowshiptypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/location-types/{id}', name: 'locationtypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/equipment/{id}', name: 'equipments_delete', methods: ['DELETE'])]
    #[Route(path: '/list/equipment-types/{id}', name: 'equipmenttypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/location-privacy-types/{id}', name: 'locationprivacy_delete', methods: ['DELETE'])]
    #[Route(path: '/list/role-attributes/{id}', name: 'roleattributes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/buidlings/{id}', name: 'buildings_delete', methods: ['DELETE'])]
    #[Route(path: '/list/rooms/{id}', name: 'rooms_delete', methods: ['DELETE'])]
    #[Route(path: '/list/suites/{id}', name: 'suites_delete', methods: ['DELETE'])]
    #[Route(path: '/list/floors/{id}', name: 'floors_delete', methods: ['DELETE'])]
    #[Route(path: '/list/grants/{id}', name: 'grants_delete', methods: ['DELETE'])]
    #[Route(path: '/list/mailboxes/{id}', name: 'mailboxes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/percent-effort/{id}', name: 'efforts_delete', methods: ['DELETE'])]
    #[Route(path: '/list/administrative-titles/{id}', name: 'admintitles_delete', methods: ['DELETE'])]
    #[Route(path: '/list/academic-appointment-titles/{id}', name: 'apptitles_delete', methods: ['DELETE'])]
    #[Route(path: '/list/training-completion-reasons/{id}', name: 'completionreasons_delete', methods: ['DELETE'])]
    #[Route(path: '/list/training-degrees/{id}', name: 'trainingdegrees_delete', methods: ['DELETE'])]
    #[Route(path: '/list/training-majors/{id}', name: 'trainingmajors_delete', methods: ['DELETE'])]
    #[Route(path: '/list/training-minors/{id}', name: 'trainingminors_delete', methods: ['DELETE'])]
    #[Route(path: '/list/training-honors/{id}', name: 'traininghonors_delete', methods: ['DELETE'])]
    #[Route(path: '/list/fellowship-titles/{id}', name: 'fellowshiptitles_delete', methods: ['DELETE'])]
    #[Route(path: '/list/residency-specialties/{id}', name: 'residencyspecialtys_delete', methods: ['DELETE'])]
    #[Route(path: '/list/fellowship-subspecialties/{id}', name: 'fellowshipsubspecialtys_delete', methods: ['DELETE'])]
    #[Route(path: '/list/institution-types/{id}', name: 'institutiontypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/document-types/{id}', name: 'documenttypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/medical-titles/{id}', name: 'medicaltitles_delete', methods: ['DELETE'])]
    #[Route(path: '/list/medical-specialties/{id}', name: 'medicalspecialties_delete', methods: ['DELETE'])]
    #[Route(path: '/list/employment-types/{id}', name: 'employmenttypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/grant-source-organizations/{id}', name: 'sourceorganizations_delete', methods: ['DELETE'])]
    #[Route(path: '/list/languages/{id}', name: 'languages_delete', methods: ['DELETE'])]
    #[Route(path: '/list/locales/{id}', name: 'locales_delete', methods: ['DELETE'])]
    #[Route(path: '/list/ranks-of-importance/{id}', name: 'importances_delete', methods: ['DELETE'])]
    #[Route(path: '/list/authorship-roles/{id}', name: 'authorshiproles_delete', methods: ['DELETE'])]
    #[Route(path: '/list/lecture-venues/{id}', name: 'organizations_delete', methods: ['DELETE'])]
    #[Route(path: '/list/cities/{id}', name: 'cities_delete', methods: ['DELETE'])]
    #[Route(path: '/list/link-types/{id}', name: 'linktypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/sexes/{id}', name: 'sexes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/position-types/{id}', name: 'positiontypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/organizational-group-types/{id}', name: 'organizationalgrouptypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/profile-comment-group-types/{id}', name: 'commentgrouptypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/comment-types/{id}', name: 'commenttypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/user-wrappers/{id}', name: 'userwrappers_delete', methods: ['DELETE'])]
    #[Route(path: '/list/spot-purposes/{id}', name: 'spotpurposes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/medical-license-statuses/{id}', name: 'medicalstatuses_delete', methods: ['DELETE'])]
    #[Route(path: '/list/certifying-board-organizations/{id}', name: 'certifyingboardorganizations_delete', methods: ['DELETE'])]
    #[Route(path: '/list/training-types/{id}', name: 'trainingtypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/job-titles/{id}', name: 'joblists_delete', methods: ['DELETE'])]
    #[Route(path: '/list/fellowship-application-statuses/{id}', name: 'fellappstatuses_delete', methods: ['DELETE'])]
    #[Route(path: '/list/fellowship-application-ranks/{id}', name: 'fellappranks_delete', methods: ['DELETE'])]
    #[Route(path: '/list/fellowship-application-language-proficiencies/{id}', name: 'fellapplanguageproficiency_delete', methods: ['DELETE'])]
    #[Route(path: '/list/collaboration-types/{id}', name: 'collaborationtypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/permissions/{id}', name: 'permission_delete', methods: ['DELETE'])]
    #[Route(path: '/list/permission-objects/{id}', name: 'permissionobject_delete', methods: ['DELETE'])]
    #[Route(path: '/list/permission-actions/{id}', name: 'permissionaction_delete', methods: ['DELETE'])]
    #[Route(path: '/list/sites/{id}', name: 'sites_delete', methods: ['DELETE'])]
    #[Route(path: '/list/event-object-types/{id}', name: 'eventobjecttypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/time-away-request-types/{id}', name: 'vacreqrequesttypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/time-away-request-floating-texts/{id}', name: 'vacreqfloatingtexts_delete', methods: ['DELETE'])]
    #[Route(path: '/list/time-away-request-floating-types/{id}', name: 'vacreqfloatingtypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/time-away-request-approval-types/{id}', name: 'vacreqapprovaltypes_delete', methods: ['GET'])]
    #[Route(path: '/list/healthcare-provider-specialties/{id}', name: 'healthcareproviderspecialty_delete', methods: ['DELETE'])]
    #[Route(path: '/list/healthcare-provider-initial-communications/{id}', name: 'healthcareprovidercommunication_delete', methods: ['DELETE'])]
    #[Route(path: '/list/object-types/{id}', name: 'objecttypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/form-nodes/{id}', name: 'formnodes_delete', methods: ['DELETE'], options: ['expose' => true])]
    #[Route(path: '/list/object-type-texts/{id}', name: 'objecttypetexts_delete', methods: ['DELETE'], options: ['expose' => true])]
    #[Route(path: '/list/blood-product-transfusions/{id}', name: 'bloodproducttransfusions_delete', methods: ['DELETE'])]
    #[Route(path: '/list/transfusion-reaction-types/{id}', name: 'transfusionreactiontypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/object-type-strings/{id}', name: 'objecttypestrings_delete', methods: ['DELETE'])]
    #[Route(path: '/list/object-type-dropdowns/{id}', name: 'objecttypedropdowns_delete', methods: ['DELETE'])]
    #[Route(path: '/list/blood-types/{id}', name: 'bloodtypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/additional-communications/{id}', name: 'additionalcommunications_delete', methods: ['DELETE'])]
    #[Route(path: '/list/transfusion-antibody-screen-results/{id}', name: 'transfusionantibodyscreenresults_delete', methods: ['DELETE'])]
    #[Route(path: '/list/transfusion-crossmatch-results/{id}', name: 'transfusioncrossmatchresults_delete', methods: ['DELETE'])]
    #[Route(path: '/list/transfusion-dat-results/{id}', name: 'transfusiondatresults_delete', methods: ['DELETE'])]
    #[Route(path: '/list/transfusion-hemolysis-check-results/{id}', name: 'transfusionhemolysischeckresults_delete', methods: ['DELETE'])]
    #[Route(path: '/list/object-type-datetimes/{id}', name: 'objecttypedatetimes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/complex-platelet-summary-antibodies/{id}', name: 'complexplateletsummaryantibodies_delete', methods: ['DELETE'])]
    #[Route(path: '/list/cci-unit-platelet-count-default-values/{id}', name: 'cciunitplateletcountdefaultvalues_delete', methods: ['DELETE'])]
    #[Route(path: '/list/cci-platelet-type-transfused/{id}', name: 'cciplatelettypetransfuseds_delete', methods: ['DELETE'])]
    #[Route(path: '/list/platelet-transfusion-product-receiving/{id}', name: 'platelettransfusionproductreceivings_delete', methods: ['DELETE'])]
    #[Route(path: '/list/transfusion-product-status/{id}', name: 'transfusionproductstatus_delete', methods: ['DELETE'])]
    #[Route(path: '/list/week-days/{id}', name: 'weekdays_delete', methods: ['DELETE'])]
    #[Route(path: '/list/months/{id}', name: 'months_delete', methods: ['DELETE'])]
    #[Route(path: '/list/clerical-errors/{id}', name: 'clericalerrors_delete', methods: ['DELETE'])]
    #[Route(path: '/list/lab-result-names/{id}', name: 'labresultnames_delete', methods: ['DELETE'])]
    #[Route(path: '/list/lab-result-units-measures/{id}', name: 'labresultunitsmeasures_delete', methods: ['DELETE'])]
    #[Route(path: '/list/lab-result-flags/{id}', name: 'labresultflags_delete', methods: ['DELETE'])]
    #[Route(path: '/list/pathology-result-signatories/{id}', name: 'pathologyresultsignatories_delete', methods: ['DELETE'])]
    #[Route(path: '/list/object-type-checkboxes/{id}', name: 'objecttypecheckboxs_delete', methods: ['DELETE'])]
    #[Route(path: '/list/object-type-radio-buttons/{id}', name: 'objecttyperadiobuttons_delete', methods: ['DELETE'])]
    #[Route(path: '/list/life-forms/{id}', name: 'lifeforms_delete', methods: ['DELETE'])]
    #[Route(path: '/list/position-track-types/{id}', name: 'positiontracktypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-research-project-specialties/{id}', name: 'transresprojectspecialties_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-research-project-types/{id}', name: 'transresprojecttypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-research-request-category-types/{id}', name: 'transresrequestcategorytypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-irb-approval-types/{id}', name: 'transresirbapprovaltypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-business-purposes/{id}', name: 'transresbusinesspurposes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-work-queue-types/{id}', name: 'workqueuetypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-orderable-status/{id}', name: 'orderablestatus_delete', methods: ['DELETE'])]
    #[Route(path: '/list/antibodies/{id}', name: 'antibodies_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom000/{id}', name: 'custom000_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom001/{id}', name: 'custom001_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom002/{id}', name: 'custom002_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom003/{id}', name: 'custom003_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom004/{id}', name: 'custom004_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom005/{id}', name: 'custom005_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom006/{id}', name: 'custom006_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom007/{id}', name: 'custom007_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom008/{id}', name: 'custom008_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom009/{id}', name: 'custom009_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom010/{id}', name: 'custom010_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom011/{id}', name: 'custom011_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom012/{id}', name: 'custom012_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom013/{id}', name: 'custom013_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom014/{id}', name: 'custom014_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom015/{id}', name: 'custom015_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom016/{id}', name: 'custom016_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom017/{id}', name: 'custom017_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom018/{id}', name: 'custom018_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom019/{id}', name: 'custom019_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom020/{id}', name: 'custom020_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom021/{id}', name: 'custom021_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom022/{id}', name: 'custom022_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom023/{id}', name: 'custom023_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom024/{id}', name: 'custom024_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom025/{id}', name: 'custom025_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom026/{id}', name: 'custom026_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom027/{id}', name: 'custom027_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom028/{id}', name: 'custom028_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom029/{id}', name: 'custom029_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom030/{id}', name: 'custom030_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom031/{id}', name: 'custom031_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom032/{id}', name: 'custom032_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom033/{id}', name: 'custom033_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom034/{id}', name: 'custom034_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom035/{id}', name: 'custom035_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom036/{id}', name: 'custom036_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom037/{id}', name: 'custom037_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom038/{id}', name: 'custom038_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom039/{id}', name: 'custom039_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom040/{id}', name: 'custom040_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom041/{id}', name: 'custom041_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom042/{id}', name: 'custom042_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom043/{id}', name: 'custom043_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom044/{id}', name: 'custom044_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom045/{id}', name: 'custom045_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom046/{id}', name: 'custom046_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom047/{id}', name: 'custom047_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom048/{id}', name: 'custom048_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom049/{id}', name: 'custom049_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom050/{id}', name: 'custom050_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom051/{id}', name: 'custom051_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom052/{id}', name: 'custom052_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom053/{id}', name: 'custom053_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom054/{id}', name: 'custom054_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom055/{id}', name: 'custom055_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom056/{id}', name: 'custom056_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom057/{id}', name: 'custom057_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom058/{id}', name: 'custom058_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom059/{id}', name: 'custom059_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom060/{id}', name: 'custom060_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom061/{id}', name: 'custom061_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom062/{id}', name: 'custom062_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom063/{id}', name: 'custom063_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom064/{id}', name: 'custom064_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom065/{id}', name: 'custom065_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom066/{id}', name: 'custom066_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom067/{id}', name: 'custom067_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom068/{id}', name: 'custom068_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom069/{id}', name: 'custom069_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom070/{id}', name: 'custom070_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom071/{id}', name: 'custom071_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom072/{id}', name: 'custom072_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom073/{id}', name: 'custom073_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom074/{id}', name: 'custom074_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom075/{id}', name: 'custom075_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom076/{id}', name: 'custom076_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom077/{id}', name: 'custom077_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom078/{id}', name: 'custom078_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom079/{id}', name: 'custom079_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom080/{id}', name: 'custom080_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom081/{id}', name: 'custom081_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom082/{id}', name: 'custom082_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom083/{id}', name: 'custom083_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom084/{id}', name: 'custom084_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom085/{id}', name: 'custom085_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom086/{id}', name: 'custom086_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom087/{id}', name: 'custom087_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom088/{id}', name: 'custom088_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom089/{id}', name: 'custom089_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom090/{id}', name: 'custom090_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom091/{id}', name: 'custom091_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom092/{id}', name: 'custom092_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom093/{id}', name: 'custom093_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom094/{id}', name: 'custom094_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom095/{id}', name: 'custom095_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom096/{id}', name: 'custom096_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom097/{id}', name: 'custom097_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom098/{id}', name: 'custom098_delete', methods: ['DELETE'])]
    #[Route(path: '/list/custom099/{id}', name: 'custom099_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-tissue-processing-services/{id}', name: 'transrestissueprocessingservices_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-other-requested-services/{id}', name: 'transresotherrequestedservices_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-collaboration-labs/{id}', name: 'transrescolllabs_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-collaboration-divs/{id}', name: 'transrescolldivs_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-irb-approval-status/{id}', name: 'transresirbstatus_delete', methods: ['DELETE'])]
    #[Route(path: '/list/translational-requester-group/{id}', name: 'transresrequestergroup_delete', methods: ['DELETE'])]
    #[Route(path: '/list/transrescomptypes/{id}', name: 'transrescomptypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/visastatus/{id}', name: 'visastatus_delete', methods: ['DELETE'])]
    #[Route(path: '/list/resappstatuses/{id}', name: 'resappstatuses_delete', methods: ['DELETE'])]
    #[Route(path: '/list/resappranks/{id}', name: 'resappranks_delete', methods: ['DELETE'])]
    #[Route(path: '/list/resapplanguageproficiency/{id}', name: 'resapplanguageproficiency_delete', methods: ['DELETE'])]
    #[Route(path: '/list/resappfitforprogram/{id}', name: 'resappfitforprogram_delete', methods: ['DELETE'])]
    #[Route(path: '/list/resappvisastatus/{id}', name: 'resappvisastatus_delete', methods: ['DELETE'])]
    #[Route(path: '/list/postsoph/{id}', name: 'postsoph_delete', methods: ['DELETE'])]
    #[Route(path: '/list/resappapplyingresidencytrack/{id}', name: 'resappapplyingresidencytrack_delete', methods: ['DELETE'])]
    #[Route(path: '/list/resapplearnarealist/{id}', name: 'resapplearnarealist_delete', methods: ['DELETE'])]
    #[Route(path: '/list/resappspecificindividuallist/{id}', name: 'resappspecificindividuallist_delete', methods: ['DELETE'])]
    #[Route(path: '/list/viewmodes/{id}', name: 'viewmodes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/transrespricetypes/{id}', name: 'transrespricetypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/charttypes/{id}', name: 'charttypes_delete', methods: ['DELETE'])]
    #[Route(path: '/list/charttopics/{id}', name: 'charttopics_delete', methods: ['DELETE'])]
    #[Route(path: '/list/chartfilters/{id}', name: 'chartfilters_delete', methods: ['DELETE'])]
    #[Route(path: '/list/charts/{id}', name: 'charts_delete', methods: ['DELETE'])]
    #[Route(path: '/list/chartdatasources/{id}', name: 'chartdatasources_delete', methods: ['DELETE'])]
    #[Route(path: '/list/chartupdatefrequencies/{id}', name: 'chartupdatefrequencies_delete', methods: ['DELETE'])]
    #[Route(path: '/list/chartvisualizations/{id}', name: 'chartvisualizations_delete', methods: ['DELETE'])]
    #[Route(path: '/list/vacreqholidays/{id}', name: 'vacreqholidays_delete', methods: ['DELETE'])]
    #[Route(path: '/list/vacreqobservedholidays/{id}', name: 'vacreqobservedholidays_delete', methods: ['DELETE'])]
    #[Route(path: '/list/authusergroup/{id}', name: 'authusergroup_delete', methods: ['DELETE'])]
    #[Route(path: '/list/authservernetwork/{id}', name: 'authservernetwork_delete', methods: ['DELETE'])]
    #[Route(path: '/list/authpartnerserver/{id}', name: 'authpartnerserver_delete', methods: ['DELETE'])]
    #[Route(path: '/list/tenanturls/{id}', name: 'tenanturls_delete', methods: ['DELETE'])]
    #[Route(path: '/list/antibodycategorytag/{id}', name: 'antibodycategorytag_delete', methods: ['DELETE'])]
    #[Route(path: '/list/transferstatus/{id}', name: 'transferstatus_delete', methods: ['DELETE'])]
    #[Route(path: '/list/interfacetransfers/{id}', name: 'interfacetransfers_delete', methods: ['DELETE'])]
    #[Route(path: '/list/antibodylabs/{id}', name: 'antibodylabs_delete', methods: ['DELETE'])]
    #[Route(path: '/list/antibodypanels/{id}', name: 'antibodypanels_delete', methods: ['DELETE'])]
    #[Route(path: '/list/samlconfig/{id}', name: 'samlconfig_delete', methods: ['DELETE'])]
    #[Route(path: '/list/globalfellowshipspecialty/{id}', name: 'globalfellowshipspecialty_delete', methods: ['DELETE'])]
    #[Route(path: '/list/trainingeligibility/{id}', name: 'trainingeligibility_delete', methods: ['DELETE'])]
    #[Route(path: '/list/dutiescapability/{id}', name: 'dutiescapability_delete', methods: ['DELETE'])]
    #[Route(path: '/list/phdfield/{id}', name: 'phdfield_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, $id)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        //return $this->deleteList($request, $id);
    }

    public function deleteList($request, $id) {

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $mapper= $this->classListMapper($pathbase,$routeName);

        $form = $this->createDeleteForm($id,$pathbase);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            //$entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);
            $entity = $em->getRepository($mapper['fullClassName'])->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
            }

            $em->remove($entity);
            $em->flush();
        } else {
            //
        }

        return $this->redirect($this->generateUrl($pathbase));
    }

    /**
     * Creates a form to delete a entity by id.
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createDeleteForm($id,$pathbase)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($pathbase.'_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete','attr'=>array('class'=>'btn btn-danger')))
            ->getForm()
        ;
    }

    //http://127.0.0.1/directory/admin/list/edit-by-listname/FellowshipSubspecialty
    #[Route(path: '/list/edit-by-listname/{listName}', name: 'employees_edit_by_listname', methods: ['GET'])]
    public function editByListnameAction(Request $request, $listName)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $url = $userServiceUtil->getSiteParamListUrl($listName);
        if( !$url ) {
            throw $this->createNotFoundException('Unable to find url by listName='.$listName);
        }
        //echo 'url='.$url."<br>";

        $logger = $this->container->get('logger');
        $logger->notice("url for $listName=$url");

        return $this->redirect($url);
    }

    /////////////////// DELETE IS NOT USED /////////////////////////
    /**
     * Platform List Manager Root List
     * /order/list-manager/list?id=70 => show Roles list (assuming that listName=Roles == listId=70)
     *
     *
     */
    #[Route(path: '/list-manager/id/{listId}', name: 'platform_list_manager', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/ListForm/platform_list_manager.html.twig')]
    public function platformListManagerAction(Request $request, $listId)
    {

        $em = $this->getDoctrine()->getManager();
        //$rootList = $em->getRepository('AppUserdirectoryBundle:PlatformListManagerRootList')->findOneByListId($listId);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PlatformListManagerRootList'] by [PlatformListManagerRootList::class]
        $rootList = $em->getRepository(PlatformListManagerRootList::class)->findOneByLinkToListId($listId);
        if( !$rootList ) {
            throw $this->createNotFoundException('Unable to find PlatformListManagerRootList by linkToListId='.$listId);
        }

        //$listName = $rootList->getListName();
        $listRootName = $rootList->getListRootName(); //roles-list

        if( $listRootName ) {
            //return $this->redirect( $this->generateUrl($listRootName) );
            //echo "listRootName=".$listRootName."<br>";

            $request->attributes->set('_route',$listRootName);

            if( strpos((string)$listRootName, "_pathaction") === false ) {
                //echo "ListController::indexAction, listRootName=$listRootName<br>";
                return $this->forward('App\UserdirectoryBundle\Controller\ListController::indexAction', array('request' => $request));
            } else {
                //echo "ComplexListController::indexAction, listRootName=$listRootName<br>";
                return $this->forward('App\UserdirectoryBundle\Controller\ComplexListController::indexAction', array('request' => $request));
            }

        }

        return array(
            'routename' => $listRootName,
            'displayName' => 'Platform List Manager Root List with List ID #'.$listId
        );
    }

    /**
     * Platform Element Manager Root Element
     * /order/directory/admin/list/sites/1 it goes to /order/directory/admin/list-manager/id/10/1 use role_show
     */
    #[Route(path: '/list-manager/id/{linkToListId}/{entityId}', name: 'platform_list_manager_element', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/ListForm/platform_list_manager.html.twig')]
    public function platformElementManagerRootElementAction( Request $request, $linkToListId, $entityId ) {

        $em = $this->getDoctrine()->getManager();
        //$rootList = $em->getRepository('AppUserdirectoryBundle:PlatformListManagerRootList')->findOneByListId($listId);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PlatformListManagerRootList'] by [PlatformListManagerRootList::class]
        $rootList = $em->getRepository(PlatformListManagerRootList::class)->findOneByLinkToListId($linkToListId);
        if( !$rootList ) {
            throw $this->createNotFoundException('Unable to find PlatformListManagerRootList by linkToListId='.$linkToListId);
        }

        //$listName = $rootList->getListName();
        $listRootName = $rootList->getListRootName(); //roles-list

        if( $listRootName ) {
            //return $this->redirect( $this->generateUrl($listRootName) );

            //$request->attributes->set('_route',$listRootName);
            //return $this->showList($request,$entityId);

            $pieces = explode("-", $listRootName);
            $pathbase = $pieces[0];
            //echo '2 pathbase='.$pathbase.'<br>';
            $newRootName = $pathbase."_show";

            $request->attributes->set('_route',$newRootName);

            //exit('1');
            return $this->forward('App\UserdirectoryBundle\Controller\ListController::showAction', array('request' => $request, 'id' => $entityId));
        }

        //exit('2');
        return array(
            'routename' => $listRootName,
            'displayName' => 'Platform List Manager Root List with List ID #'.$linkToListId
        );
    }

    #[Route(path: '/change-list-element-type/{type}/{entityId}/{pathbase}/{postpath}', name: 'platform_list_manager_element_change_type', methods: ['GET'])]
    public function changeTypeAction( Request $request, $type, $entityId, $pathbase, $postpath=null ) {

        $additionalSitename = null;

        //exit("pathbase=".$pathbase);
        if( $pathbase == "translationalresearchfeesschedule" || $pathbase == "antibodies" || $pathbase == "transresrequestcategorytypes") {
            $additionalSitename = $this->getParameter('translationalresearch.sitename');
            if (
                false === $this->isGranted('ROLE_TRANSRES_ADMIN') &&
                false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
            ) {
                return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
            }
        } elseif( $pathbase == "visastatus" ) {
            $additionalSitename = $this->getParameter('fellapp.sitename');
            if( false === $this->isGranted('ROLE_FELLAPP_ADMIN') ) {
                return $this->redirect($this->generateUrl($this->getParameter('fellapp.sitename') . '-nopermission'));
            }
        } elseif( $pathbase == "resappvisastatus" ) {
            $additionalSitename = $this->getParameter('resapp.sitename');
            if( false === $this->isGranted('ROLE_RESAPP_ADMIN') ) {
                return $this->redirect($this->generateUrl($this->getParameter('resapp.sitename') . '-nopermission'));
            }
        } else {
            if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
                return $this->redirect($this->generateUrl($this->getParameter('employees.sitename').'-nopermission'));
            }
        }

        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->getUser();

        //echo "data: $pathbase, $entityId, $type <br>";

        $mapper = $this->classListMapper($pathbase,$request);
        //echo "bundleName=".$mapper['bundleName']."<br>";
        //echo "className=".$mapper['className']."<br>";
        //$entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($entityId);
        $entity = $em->getRepository($mapper['fullClassName'])->find($entityId);

        //echo "entity=".$entity."<br>";

        if( $type ) {
            $entity->setType($type);
            //$em->flush($entity);
            $em->flush();

            $event = "The type of the list entry '" . $entity . "' has been changed to '" . $type . "'";

            $this->addFlash(
                'notice',
                $event
            );

            //$userSecUtil = $this->container->get('user_security_utility');
            //echo "event=".$event."<br>";
            //print_r($removedCollections);
            //exit();
            $userSecUtil->createUserEditEvent($this->getParameter('employees.sitename'),$event,$user,$entity,$request,'List Updated');

            if( $additionalSitename ) {
                $userSecUtil->createUserEditEvent($additionalSitename,$event,$user,$entity,$request,'List Updated');
            }
        }

        //exit();
        return $this->redirect( $this->generateUrl($pathbase.'-list'.$postpath) );
    }
    
}
