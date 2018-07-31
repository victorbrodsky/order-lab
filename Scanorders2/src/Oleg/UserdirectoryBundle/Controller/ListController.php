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

namespace Oleg\UserdirectoryBundle\Controller;

use Oleg\OrderformBundle\Controller\ScanListController;
use Oleg\UserdirectoryBundle\Entity\CompositeNodeInterface;
use Oleg\UserdirectoryBundle\Entity\Permission;
use Oleg\UserdirectoryBundle\Entity\UsernameType;
use Oleg\UserdirectoryBundle\Form\ListFilterType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\UserdirectoryBundle\Form\GenericListType;
use Oleg\UserdirectoryBundle\Util\ErrorHelper;


/**
 * Common list controller
 * @Route("/admin")
 */
class ListController extends Controller
{

    protected $sitename = "employees";
    protected $postPath = null;

    //@Method({"GET","POST"}) @Method("GET") //TODO: why method GET does not work for handleRequest https://symfony.com/doc/current/form/action_method.html
    /**
     * Lists all entities.
     *
     * //Platform List Manager Root List
     * @Route("/list-manager/", name="platformlistmanager-list")
     * @Route("/list/source-systems/", name="sourcesystems-list")
     * @Route("/list/roles/", name="role-list")
     * @Route("/list/institutions/", name="institutions-list", options={"expose"=true})
     * @Route("/list/states/", name="states-list")
     * @Route("/list/countries/", name="countries-list")
     * @Route("/list/board-certifications/", name="boardcertifications-list")
     * @Route("/list/employment-termination-reasons/", name="employmentterminations-list")
     * @Route("/list/event-log-event-types/", name="loggereventtypes-list")
     * @Route("/list/primary-public-user-id-types/", name="usernametypes-list")
     * @Route("/list/identifier-types/", name="identifiers-list")
     * @Route("/list/residency-tracks/", name="residencytracks-list")
     * @Route("/list/fellowship-types/", name="fellowshiptypes-list")
//     * @Route("/list/research-labs/", name="researchlabs-list")
     * @Route("/list/location-types/", name="locationtypes-list")
     * @Route("/list/equipment/", name="equipments-list")
     * @Route("/list/equipment-types/", name="equipmenttypes-list")
     * @Route("/list/location-privacy-types/", name="locationprivacy-list")
     * @Route("/list/role-attributes/", name="roleattributes-list")
     * @Route("/list/buidlings/", name="buildings-list")
     * @Route("/list/rooms/", name="rooms-list")
     * @Route("/list/suites/", name="suites-list")
     * @Route("/list/floors/", name="floors-list")
     * @Route("/list/mailboxes/", name="mailboxes-list")
     * @Route("/list/percent-effort/", name="efforts-list")
     * @Route("/list/administrative-titles/", name="admintitles-list")
     * @Route("/list/academic-appointment-titles/", name="apptitles-list")
     * @Route("/list/training-completion-reasons/", name="completionreasons-list")
     * @Route("/list/training-degrees/", name="trainingdegrees-list")
     * @Route("/list/training-majors/", name="trainingmajors-list")
     * @Route("/list/training-minors/", name="trainingminors-list")
     * @Route("/list/training-honors/", name="traininghonors-list")
     * @Route("/list/fellowship-titles/", name="fellowshiptitles-list")
     * @Route("/list/residency-specialties/", name="residencyspecialtys-list")
     * @Route("/list/fellowship-subspecialties/", name="fellowshipsubspecialtys-list")
     * @Route("/list/institution-types/", name="institutiontypes-list")
     * @Route("/list/document-types/", name="documenttypes-list")
     * @Route("/list/medical-titles/", name="medicaltitles-list")
     * @Route("/list/medical-specialties/", name="medicalspecialties-list")
     * @Route("/list/employment-types/", name="employmenttypes-list")
     * @Route("/list/grant-source-organizations/", name="sourceorganizations-list")
     * @Route("/list/languages/", name="languages-list")
     * @Route("/list/locales/", name="locales-list")
     * @Route("/list/ranks-of-importance/", name="importances-list")
     * @Route("/list/authorship-roles/", name="authorshiproles-list")
     * @Route("/list/lecture-venues/", name="organizations-list")
     * @Route("/list/cities/", name="cities-list")
     * @Route("/list/link-types/", name="linktypes-list")
     * @Route("/list/sexes/", name="sexes-list")
     * @Route("/list/position-types/", name="positiontypes-list")
     * @Route("/list/organizational-group-types/", name="organizationalgrouptypes-list")
     * @Route("/list/profile-comment-group-types/", name="commentgrouptypes-list")
     * @Route("/list/comment-types/", name="commenttypes-list", options={"expose"=true})
     * @Route("/list/user-wrappers/", name="userwrappers-list")
     * @Route("/list/spot-purposes/", name="spotpurposes-list")
     * @Route("/list/medical-license-statuses/", name="medicalstatuses-list")
     * @Route("/list/certifying-board-organizations/", name="certifyingboardorganizations-list")
     * @Route("/list/training-types/", name="trainingtypes-list")
     * @Route("/list/job-titles/", name="joblists-list")
     * @Route("/list/fellowship-application-statuses/", name="fellappstatuses-list")
     * @Route("/list/fellowship-application-ranks/", name="fellappranks-list")
     * @Route("/list/fellowship-application-language-proficiencies/", name="fellapplanguageproficiency-list")
//     * @Route("/list/collaborations/", name="collaborations-list")
     * @Route("/list/collaboration-types/", name="collaborationtypes-list")
     * @Route("/list/permissions/", name="permission-list")
     * @Route("/list/permission-objects/", name="permissionobject-list")
     * @Route("/list/permission-actions/", name="permissionaction-list")
     * @Route("/list/sites/", name="sites-list")
     * @Route("/list/event-object-types/", name="eventobjecttypes-list")
     * @Route("/list/vacation-request-types/", name="vacreqrequesttypes-list")
     * @Route("/list/healthcare-provider-specialties/", name="healthcareproviderspecialty-list")
     * @Route("/list/object-types/", name="objecttypes-list")
     * @Route("/list/form-nodes/", name="formnodes-list", options={"expose"=true})
     * @Route("/list/object-type-texts/", name="objecttypetexts-list", options={"expose"=true})
     * @Route("/list/blood-product-transfusions/", name="bloodproducttransfusions-list")
     * @Route("/list/transfusion-reaction-types/", name="transfusionreactiontypes-list")
     * @Route("/list/object-type-strings/", name="objecttypestrings-list")
     * @Route("/list/object-type-dropdowns/", name="objecttypedropdowns-list")
     * @Route("/list/blood-types/", name="bloodtypes-list")
     * @Route("/list/transfusion-antibody-screen-results/", name="transfusionantibodyscreenresults-list")
     * @Route("/list/transfusion-crossmatch-results/", name="transfusioncrossmatchresults-list")
     * @Route("/list/transfusion-dat-results/", name="transfusiondatresults-list")
     * @Route("/list/transfusion-hemolysis-check-results/", name="transfusionhemolysischeckresults-list")
     * @Route("/list/object-type-datetimes/", name="objecttypedatetimes-list")
     * @Route("/list/complex-platelet-summary-antibodies/", name="complexplateletsummaryantibodies-list")
     * @Route("/list/cci-unit-platelet-count-default-values/", name="cciunitplateletcountdefaultvalues-list")
     * @Route("/list/cci-platelet-type-transfused/", name="cciplatelettypetransfuseds-list")
     * @Route("/list/platelet-transfusion-product-receiving/", name="platelettransfusionproductreceivings-list")
     * @Route("/list/transfusion-product-status/", name="transfusionproductstatus-list")
     * @Route("/list/week-days/", name="weekdays-list")
     * @Route("/list/months/", name="months-list")
     * @Route("/list/clerical-errors/", name="clericalerrors-list")
     * @Route("/list/lab-result-names/", name="labresultnames-list")
     * @Route("/list/lab-result-units-measures/", name="labresultunitsmeasures-list")
     * @Route("/list/lab-result-flags/", name="labresultflags-list")
     * @Route("/list/pathology-result-signatories/", name="pathologyresultsignatories-list")
     * @Route("/list/object-type-checkboxes/", name="objecttypecheckboxs-list")
     * @Route("/list/object-type-radio-buttons/", name="objecttyperadiobuttons-list")
     * @Route("/list/life-forms/", name="lifeforms-list")
     * @Route("/list/position-track-types/", name="positiontracktypes-list")
     * @Route("/list/translational-research-project-specialties/", name="transresprojectspecialties-list")
     * @Route("/list/translational-research-project-types/", name="transresprojecttypes-list")
     * @Route("/list/translational-research-request-category-types/", name="transresrequestcategorytypes-list")
     * @Route("/list/translational-irb-approval-types/", name="transresirbapprovaltypes-list")
     * @Route("/list/antibodies/", name="antibodies-list")
     *
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->getList($request);
    }
    public function getList($request, $limit=50) {

        $routeName = $request->get('_route');

        //get object name: stain-list => stain
        $pieces = explode("-", $routeName);
        $pathbase = $pieces[0];

        $mapper = $this->classListMapper($pathbase,$request);
        //echo "bundleName=".$mapper['bundleName']."<br>";
        //echo "className=".$mapper['className']."<br>";

        $repository = $this->getDoctrine()->getRepository($mapper['bundleName'].':'.$mapper['className']);
        $dql =  $repository->createQueryBuilder("ent");
        $dql->select('ent');
        $dql->groupBy('ent');

        $dql->leftJoin("ent.creator", "creator");
        $dql->leftJoin("ent.updatedby", "updatedby");

        $dql->addGroupBy('creator.username');
        $dql->addGroupBy('updatedby.username');

        $entityClass = $mapper['fullClassName'];   //"Oleg\\OrderformBundle\\Entity\\".$mapper['className'];

        //synonyms and original
        $dql->leftJoin("ent.synonyms", "synonyms");
        $dql->addGroupBy('synonyms.name');
        $dql->leftJoin("ent.original", "original");
        $dql->addGroupBy('original.name');

        //$dql->leftJoin("ent.objectType", "objectType");

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
        }

        if( method_exists($entityClass,'getCollaborationType') ) {
            $dql->leftJoin("ent.collaborationType", "collaborationType");
            $dql->addGroupBy('collaborationType');
        }

        if( method_exists($entityClass,'getSites') ) {
            $dql->leftJoin("ent.sites", "sites");
            $dql->addGroupBy('sites.name');
        }

        if( method_exists($entityClass,'getFellowshipSubspecialty') ) {
            $dql->leftJoin("ent.fellowshipSubspecialty", "fellowshipSubspecialty");
            $dql->addGroupBy('fellowshipSubspecialty.name');
        }

//        if( method_exists($entityClass,'getPatients') ) {
//            $dql->leftJoin("ent.patients", "patients");
//            //$dql->addGroupBy('patients.name');
//        }


        //$dql->orderBy("ent.createdate","DESC");
		
		//pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters       		
//		$postData = $request->query->all();
//		if( isset($postData['sort']) ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }

        $dqlParameters = array();
        $filterform = $this->createForm(ListFilterType::class, null, array(
            //'action' => $this->generateUrl($routeName),
            'method' => 'GET',
        ));
        //$filterform->submit($request);
        $filterform->handleRequest($request);
        $search = $filterform['search']->getData();
        //echo "search=".$search."<br>";
        //$search = $request->request->get('filter')['search'];
        //$search = $request->query->get('search');
        //echo "2search=".$search."<br>";

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
            if( method_exists($entityClass, 'getDatasheet') ) {
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

        //echo "dql=".$dql."<br>";

        $em = $this->getDoctrine()->getManager();
        $limit = 50;

        $query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters( $dqlParameters );
        }

        $paginator = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            $limit                          /*limit per page*/
            //,array('wrap-queries'=>true)   //this cause sorting impossible, but without it "site" sorting does not work (mssql: "There is no component aliased by [sites] in the given Query" )
            //,array('distinct'=>true)
            ,array('defaultSortFieldName' => 'ent.orderinlist', 'defaultSortDirection' => 'asc', 'wrap-queries'=>true)
        );
        //echo "list count=".count($entities)."<br>";
        //exit();

        ///////////// check if show "create a new entity" link //////////////
        $createNew = true;
        $reflectionClass = new \ReflectionClass($mapper['fullClassName']);
        $compositeReflection = new \ReflectionClass("Oleg\\UserdirectoryBundle\\Entity\\CompositeNodeInterface");
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
            'filterform' => $filterform->createView(),
            'routename' => $routeName,
            'sitename' => $this->sitename,
        );
    }

    /**
     * Creates a new entity.
     *
     * @Route("/list/list-manager/", name="platformlistmanager_create")
     * @Route("/list/source-systems/", name="sourcesystems_create")
     * @Route("/list/roles/", name="role_create")
     * @Route("/list/institutions/", name="institutions_create")
     * @Route("/list/states/", name="states_create")
     * @Route("/list/countries/", name="countries_create")
     * @Route("/list/board-certifications/", name="boardcertifications_create")
     * @Route("/list/employment-termination-reasons/", name="employmentterminations_create")
     * @Route("/list/event-log-event-types/", name="loggereventtypes_create")
     * @Route("/list/primary-public-user-id-types/", name="usernametypes_create")
     * @Route("/list/identifier-types/", name="identifiers_create")
     * @Route("/list/residency-tracks/", name="residencytracks_create")
     * @Route("/list/fellowship-types/", name="fellowshiptypes_create")
//     * @Route("/list/research-labs/", name="researchlabs_create")
     * @Route("/list/location-types/", name="locationtypes_create")
     * @Route("/list/equipment/", name="equipments_create")
     * @Route("/list/equipment-types/", name="equipmenttypes_create")
     * @Route("/list/location-privacy-types/", name="locationprivacy_create")
     * @Route("/list/role-attributes/", name="roleattributes_create")
     * @Route("/list/buidlings/", name="buildings_create")
     * @Route("/list/rooms/", name="rooms_create")
     * @Route("/list/suites/", name="suites_create")
     * @Route("/list/floors/", name="floors_create")
     * @Route("/list/mailboxes/", name="mailboxes_create")
     * @Route("/list/percent-effort/", name="efforts_create")
     * @Route("/list/administrative-titles/", name="admintitles_create")
     * @Route("/list/academic-appointment-titles/", name="apptitles_create")
     * @Route("/list/training-completion-reasons/", name="completionreasons_create")
     * @Route("/list/training-degrees/", name="trainingdegrees_create")
     * @Route("/list/training-majors/", name="trainingmajors_create")
     * @Route("/list/training-minors/", name="trainingminors_create")
     * @Route("/list/training-honors/", name="traininghonors_create")
     * @Route("/list/fellowship-titles/", name="fellowshiptitles_create")
     * @Route("/list/residency-specialties/", name="residencyspecialtys_create")
     * @Route("/list/fellowship-subspecialties/", name="fellowshipsubspecialtys_create")
     * @Route("/list/institution-types/", name="institutiontypes_create")
     * @Route("/list/document-types/", name="documenttypes_create")
     * @Route("/list/medical-titles/", name="medicaltitles_create")
     * @Route("/list/medical-specialties/", name="medicalspecialties_create")
     * @Route("/list/employment-types/", name="employmenttypes_create")
     * @Route("/list/grant-source-organizations/", name="sourceorganizations_create")
     * @Route("/list/languages/", name="languages_create")
     * @Route("/list/locales/", name="locales_create")
     * @Route("/list/ranks-of-importance/", name="importances_create")
     * @Route("/list/authorship-roles/", name="authorshiproles_create")
     * @Route("/list/lecture-venues/", name="organizations_create")
     * @Route("/list/cities/", name="cities_create")
     * @Route("/list/link-types/", name="linktypes_create")
     * @Route("/list/sexes/", name="sexes_create")
     * @Route("/list/position-types/", name="positiontypes_create")
     * @Route("/list/organizational-group-types/", name="organizationalgrouptypes_create")
     * @Route("/list/profile-comment-group-types/", name="commentgrouptypes_create")
     * @Route("/list/comment-types/", name="commenttypes_createt")
     * @Route("/list/user-wrappers/", name="userwrappers_create")
     * @Route("/list/spot-purposes/", name="spotpurposes_create")
     * @Route("/list/medical-license-statuses/", name="medicalstatuses_create")
     * @Route("/list/certifying-board-organizations/", name="certifyingboardorganizations_create")
     * @Route("/list/training-types/", name="trainingtypes_create")
     * @Route("/list/job-titles/", name="joblists_create")
     * @Route("/list/fellowship-application-statuses/", name="fellappstatuses_create")
     * @Route("/list/fellowship-application-ranks/", name="fellappranks_create")
     * @Route("/list/fellowship-application-language-proficiencies/", name="fellapplanguageproficiency_create")
//     * @Route("/list/collaborations/", name="collaborations_create")
     * @Route("/list/collaboration-types/", name="collaborationtypes_create")
     * @Route("/list/permissions/", name="permission_create")
     * @Route("/list/permission-objects/", name="permissionobject_create")
     * @Route("/list/permission-actions/", name="permissionaction_create")
     * @Route("/list/sites/", name="sites_create")
     * @Route("/list/event-object-types/", name="eventobjecttypes_create")
     * @Route("/list/vacation-request-types/", name="vacreqrequesttypes_create")
     * @Route("/list/healthcare-provider-specialties/", name="healthcareproviderspecialty_create")
     * @Route("/list/object-types/", name="objecttypes_create")
     * @Route("/list/form-nodes/", name="formnodes_create", options={"expose"=true})
     * @Route("/list/object-type-texts/", name="objecttypetexts_create", options={"expose"=true})
     * @Route("/list/blood-product-transfusions/", name="bloodproducttransfusions_create")
     * @Route("/list/transfusion-reaction-types/", name="transfusionreactiontypes_create")
     * @Route("/list/object-type-strings/", name="objecttypestrings_create")
     * @Route("/list/object-type-dropdowns/", name="objecttypedropdowns_create")
     * @Route("/list/blood-types/", name="bloodtypes_create")
     * @Route("/list/transfusion-antibody-screen-results/", name="transfusionantibodyscreenresults_create")
     * @Route("/list/transfusion-crossmatch-results/", name="transfusioncrossmatchresults_create")
     * @Route("/list/transfusion-dat-results/", name="transfusiondatresults_create")
     * @Route("/list/transfusion-hemolysis-check-results/", name="transfusionhemolysischeckresults_create")
     * @Route("/list/object-type-datetimes/", name="objecttypedatetimes_create")
     * @Route("/list/complex-platelet-summary-antibodies/", name="complexplateletsummaryantibodies_create")
     * @Route("/list/cci-unit-platelet-count-default-values/", name="cciunitplateletcountdefaultvalues_create")
     * @Route("/list/cci-platelet-type-transfused/", name="cciplatelettypetransfuseds_create")
     * @Route("/list/platelet-transfusion-product-receiving/", name="platelettransfusionproductreceivings_create")
     * @Route("/list/transfusion-product-status/", name="transfusionproductstatus_create")
     * @Route("/list/week-days/", name="weekdays_create")
     * @Route("/list/months/", name="months_create")
     * @Route("/list/clerical-errors/", name="clericalerrors_create")
     * @Route("/list/lab-result-names/", name="labresultnames_create")
     * @Route("/list/lab-result-units-measures/", name="labresultunitsmeasures_create")
     * @Route("/list/lab-result-flags/", name="labresultflags_create")
     * @Route("/list/pathology-result-signatories/", name="pathologyresultsignatories_create")
     * @Route("/list/object-type-checkboxes/", name="objecttypecheckboxs_create")
     * @Route("/list/object-type-radio-buttons/", name="objecttyperadiobuttons_create")
     * @Route("/list/life-forms/", name="lifeforms_create")
     * @Route("/list/position-track-types/", name="positiontracktypes_create")
     * @Route("/list/translational-research-project-specialties/", name="transresprojectspecialties_create")
     * @Route("/list/translational-research-project-types/", name="transresprojecttypes_create")
     * @Route("/list/translational-research-request-category-types/", name="transresrequestcategorytypes_create")
     * @Route("/list/translational-irb-approval-types/", name="transresirbapprovaltypes_create")
     * @Route("/list/antibodies/", name="antibodies_create")
     *
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:ListForm:new.html.twig")
     */
    public function createAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->createList($request);
    }
    public function createList( $request ) {

        $routeName = $request->get('_route');

        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $mapper= $this->classListMapper($pathbase,$request);

        $entityClass = $mapper['fullClassName'];    //"Oleg\\OrderformBundle\\Entity\\".$mapper['className'];

        $entity = new $entityClass();

        $form = $this->createCreateForm($entity,$mapper,$pathbase,'new');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            //the date from the form does not contain time, so set createdate with date and time.
            $entity->setCreatedate(new \DateTime());

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $entity->setCreator($user);

            if( $entity instanceof UsernameType ) {
                $entity->setEmptyAbbreviation();
            }

            if( method_exists($entity, "getDocuments") ) {
                $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($entity, "document");
            }

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($pathbase.'_show'.$this->postPath, array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase,
            'sitename' => $this->sitename
        );
    }


    /**
    * Creates a form to create an entity.
    * @param $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm($entity,$mapper,$pathbase,$cycle=null)
    {
        $options = array();

        if( $cycle ) {
            $options['cycle'] = $cycle;
        }

        //use $timezone = $user->getTimezone(); ?
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $options['user'] = $user;
        $options['entity'] = $entity;
        $options['em'] = $this->getDoctrine()->getManager();

        $form = $this->createForm(GenericListType::class, $entity, array(
            'action' => $this->generateUrl($pathbase.'_create'.$this->postPath),
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
     * @Route("/list/list-manager/new", name="platformlistmanager_new")
     * @Route("/list/source-systems/new", name="sourcesystems_new")
     * @Route("/list/roles/new", name="role_new")
     * @Route("/list/institutions/new", name="institutions_new")
     * @Route("/list/states/new", name="states_new")
     * @Route("/list/countries/new", name="countries_new")
     * @Route("/list/board-certifications/new", name="boardcertifications_new")
     * @Route("/list/employment-termination-reasons/new", name="employmentterminations_new")
     * @Route("/list/event-log-event-types/new", name="loggereventtypes_new")
     * @Route("/list/primary-public-user-id-types/new", name="usernametypes_new")
     * @Route("/list/identifier-types/new", name="identifiers_new")
     * @Route("/list/residency-tracks/new", name="residencytracks_new")
     * @Route("/list/fellowship-types/new", name="fellowshiptypes_new")
//     * @Route("/list/research-labs/new", name="researchlabs_new")
     * @Route("/list/location-types/new", name="locationtypes_new")
     * @Route("/list/equipment/new", name="equipments_new")
     * @Route("/list/equipment-types/new", name="equipmenttypes_new")
     * @Route("/list/location-privacy-types/new", name="locationprivacy_new")
     * @Route("/list/role-attributes/new", name="roleattributes_new")
     * @Route("/list/buidlings/new", name="buildings_new")
     * @Route("/list/rooms/new", name="rooms_new")
     * @Route("/list/suites/new", name="suites_new")
     * @Route("/list/floors/new", name="floors_new")
     * @Route("/list/mailboxes/new", name="mailboxes_new")
     * @Route("/list/percent-effort/new", name="efforts_new")
     * @Route("/list/administrative-titles/new", name="admintitles_new")
     * @Route("/list/academic-appointment-titles/new", name="apptitles_new")
     * @Route("/list/training-completion-reasons/new", name="completionreasons_new")
     * @Route("/list/training-degrees/new", name="trainingdegrees_new")
     * @Route("/list/training-majors/new", name="trainingmajors_new")
     * @Route("/list/training-minors/new", name="trainingminors_new")
     * @Route("/list/training-honors/new", name="traininghonors_new")
     * @Route("/list/fellowship-titles/new", name="fellowshiptitles_new")
     * @Route("/list/residency-specialties/new", name="residencyspecialtys_new")
     * @Route("/list/fellowship-subspecialties/new", name="fellowshipsubspecialtys_new")
     * @Route("/list/institution-types/new", name="institutiontypes_new")
     * @Route("/list/document-types/new", name="documenttypes_new")
     * @Route("/list/medical-titles/new", name="medicaltitles_new")
     * @Route("/list/medical-specialties/new", name="medicalspecialties_new")
     * @Route("/list/employment-types/new", name="employmenttypes_new")
     * @Route("/list/grant-source-organizations/new", name="sourceorganizations_new")
     * @Route("/list/languages/new", name="languages_new")
     * @Route("/list/locales/new", name="locales_new")
     * @Route("/list/ranks-of-importance/new", name="importances_new")
     * @Route("/list/authorship-roles/new", name="authorshiproles_new")
     * @Route("/list/lecture-venues/new", name="organizations_new")
     * @Route("/list/cities/new", name="cities_new")
     * @Route("/list/link-types/new", name="linktypes_new")
     * @Route("/list/sexes/new", name="sexes_new")
     * @Route("/list/position-types/new", name="positiontypes_new")
     * @Route("/list/organizational-group-types/new", name="organizationalgrouptypes_new")
     * @Route("/list/profile-comment-group-types/new", name="commentgrouptypes_new")
     * @Route("/list/comment-types/new", name="commenttypes_new")
     * @Route("/list/user-wrappers/new", name="userwrappers_new")
     * @Route("/list/spot-purposes/new", name="spotpurposes_new")
     * @Route("/list/medical-license-statuses/new", name="medicalstatuses_new")
     * @Route("/list/certifying-board-organizations/new", name="certifyingboardorganizations_new")
     * @Route("/list/training-types/new", name="trainingtypes_new")
     * @Route("/list/job-titles/new", name="joblists_new")
     * @Route("/list/fellowship-application-statuses/new", name="fellappstatuses_new")
     * @Route("/list/fellowship-application-ranks/new", name="fellappranks_new")
     * @Route("/list/fellowship-application-language-proficiencies/new", name="fellapplanguageproficiency_new")
//     * @Route("/list/collaborations/new", name="collaborations_new")
     * @Route("/list/collaboration-types/new", name="collaborationtypes_new")
     * @Route("/list/permissions/new", name="permission_new")
     * @Route("/list/permission-objects/new", name="permissionobject_new")
     * @Route("/list/permission-actions/new", name="permissionaction_new")
     * @Route("/list/sites/new", name="sites_new")
     * @Route("/list/event-object-types/new", name="eventobjecttypes_new")
     * @Route("/list/vacation-request-types/new", name="vacreqrequesttypes_new")
     * @Route("/list/healthcare-provider-specialties/new", name="healthcareproviderspecialty_new")
     * @Route("/list/object-types/new", name="objecttypes_new")
     * @Route("/list/form-nodes/new", name="formnodes_new", options={"expose"=true})
     * @Route("/list/object-type-texts/new", name="objecttypetexts_new", options={"expose"=true})
     * @Route("/list/blood-product-transfusions/new", name="bloodproducttransfusions_new")
     * @Route("/list/transfusion-reaction-types/new", name="transfusionreactiontypes_new")
     * @Route("/list/object-type-strings/new", name="objecttypestrings_new")
     * @Route("/list/object-type-dropdowns/new", name="objecttypedropdowns_new")
     * @Route("/list/blood-types/new", name="bloodtypes_new")
     * @Route("/list/transfusion-antibody-screen-results/new", name="transfusionantibodyscreenresults_new")
     * @Route("/list/transfusion-crossmatch-results/new", name="transfusioncrossmatchresults_new")
     * @Route("/list/transfusion-dat-results/new", name="transfusiondatresults_new")
     * @Route("/list/transfusion-hemolysis-check-results/new", name="transfusionhemolysischeckresults_new")
     * @Route("/list/object-type-datetimes/new", name="objecttypedatetimes_new")
     * @Route("/list/complex-platelet-summary-antibodies/new", name="complexplateletsummaryantibodies_new")
     * @Route("/list/cci-unit-platelet-count-default-values/new", name="cciunitplateletcountdefaultvalues_new")
     * @Route("/list/cci-platelet-type-transfused/new", name="cciplatelettypetransfuseds_new")
     * @Route("/list/platelet-transfusion-product-receiving/new", name="platelettransfusionproductreceivings_new")
     * @Route("/list/transfusion-product-status/new", name="transfusionproductstatus_new")
     * @Route("/list/week-days/new", name="weekdays_new")
     * @Route("/list/months/new", name="months_new")
     * @Route("/list/clerical-errors/new", name="clericalerrors_new")
     * @Route("/list/lab-result-names/new", name="labresultnames_new")
     * @Route("/list/lab-result-units-measures/new", name="labresultunitsmeasures_new")
     * @Route("/list/lab-result-flags/new", name="labresultflags_new")
     * @Route("/list/pathology-result-signatories/new", name="pathologyresultsignatories_new")
     * @Route("/list/object-type-checkboxes/new", name="objecttypecheckboxs_new")
     * @Route("/list/object-type-radio-buttons/new", name="objecttyperadiobuttons_new")
     * @Route("/list/life-forms/new", name="lifeforms_new")
     * @Route("/list/position-track-types/new", name="positiontracktypes_new")
     * @Route("/list/translational-research-project-specialties/new", name="transresprojectspecialties_new")
     * @Route("/list/translational-research-project-types/new", name="transresprojecttypes_new")
     * @Route("/list/translational-research-request-category-types/new", name="transresrequestcategorytypes_new")
     * @Route("/list/translational-irb-approval-types/new", name="transresirbapprovaltypes_new")
     * @Route("/list/antibodies/new", name="antibodies_new")
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:new.html.twig")
     */
    public function newAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->newList($request);
    }
    public function newList( $request, $pid=null ) {

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase,$request);

        $entityClass = $mapper['fullClassName'];    //"Oleg\\OrderformBundle\\Entity\\".$mapper['className'];

        $entity = new $entityClass();

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $entity->setCreatedate(new \DateTime());
        $entity->setType('user-added');
        $entity->setCreator($user);

        if( $pid ) {
            //echo "pid=".$pid."<br>";
            $parentNMapper = $this->getParentName($mapper['className']);
            $parent = $em->getRepository($parentNMapper['bundleName'].':'.$parentNMapper['className'])->find($pid);
            $entity->setParent($parent);
        }

        //get max orderinlist + 10
        $query = $em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM '.$mapper['bundleName'].':'.$mapper['className'].' c');
        $nextorder = $query->getSingleResult()['maxorderinlist']+10;
        $entity->setOrderinlist($nextorder);

        $form   = $this->createCreateForm($entity,$mapper,$pathbase,'new');

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase,
            'sitename' => $this->sitename
        );
    }

    /**
     * Finds and displays a entity.
     *
     * @Route("/list-manager/{id}", name="platformlistmanager_show")
     * @Route("/list/source-systems/{id}", name="sourcesystems_show")
     * @Route("/list/roles/{id}", name="role_show")
     * @Route("/list/institutions/{id}", name="institutions_show", options={"expose"=true})
     * @Route("/list/states/{id}", name="states_show")
     * @Route("/list/countries/{id}", name="countries_show")
     * @Route("/list/board-certifications/{id}", name="boardcertifications_show")
     * @Route("/list/employment-termination-reasons/{id}", name="employmentterminations_show")
     * @Route("/list/event-log-event-types/{id}", name="loggereventtypes_show")
     * @Route("/list/primary-public-user-id-types/{id}", name="usernametypes_show")
     * @Route("/list/identifier-types/{id}", name="identifiers_show")
     * @Route("/list/residency-tracks/{id}", name="residencytracks_show")
     * @Route("/list/fellowship-types/{id}", name="fellowshiptypes_show")
//     * @Route("/list/research-labs/{id}", name="researchlabs_show")
     * @Route("/list/location-types/{id}", name="locationtypes_show")
     * @Route("/list/equipment/{id}", name="equipments_show")
     * @Route("/list/equipment-types/{id}", name="equipmenttypes_show")
     * @Route("/list/location-privacy-types/{id}", name="locationprivacy_show")
     * @Route("/list/role-attributes/{id}", name="roleattributes_show")
     * @Route("/list/buidlings/{id}", name="buildings_show")
     * @Route("/list/rooms/{id}", name="rooms_show")
     * @Route("/list/suites/{id}", name="suites_show")
     * @Route("/list/floors/{id}", name="floors_show")
     * @Route("/list/mailboxes/{id}", name="mailboxes_show")
     * @Route("/list/percent-effort/{id}", name="efforts_show")
     * @Route("/list/administrative-titles/{id}", name="admintitles_show")
     * @Route("/list/academic-appointment-titles/{id}", name="apptitles_show")
     * @Route("/list/training-completion-reasons/{id}", name="completionreasons_show")
     * @Route("/list/training-degrees/{id}", name="trainingdegrees_show")
     * @Route("/list/training-majors/{id}", name="trainingmajors_show")
     * @Route("/list/training-minors/{id}", name="trainingminors_show")
     * @Route("/list/training-honors/{id}", name="traininghonors_show")
     * @Route("/list/fellowship-titles/{id}", name="fellowshiptitles_show")
     * @Route("/list/residency-specialties/{id}", name="residencyspecialtys_show")
     * @Route("/list/fellowship-subspecialties/{id}", name="fellowshipsubspecialtys_show")
     * @Route("/list/institution-types/{id}", name="institutiontypes_show")
     * @Route("/list/document-types/{id}", name="documenttypes_show")
     * @Route("/list/medical-titles/{id}", name="medicaltitles_show")
     * @Route("/list/medical-specialties/{id}", name="medicalspecialties_show")
     * @Route("/list/employment-types/{id}", name="employmenttypes_show")
     * @Route("/list/grant-source-organizations/{id}", name="sourceorganizations_show")
     * @Route("/list/languages/{id}", name="languages_show")
     * @Route("/list/locales/{id}", name="locales_show")
     * @Route("/list/ranks-of-importance/{id}", name="importances_show")
     * @Route("/list/authorship-roles/{id}", name="authorshiproles_show")
     * @Route("/list/lecture-venues/{id}", name="organizations_show")
     * @Route("/list/cities/{id}", name="cities_show")
     * @Route("/list/link-types/{id}", name="linktypes_show")
     * @Route("/list/sexes/{id}", name="sexes_show")
     * @Route("/list/position-types/{id}", name="positiontypes_show")
     * @Route("/list/organizational-group-types/{id}", name="organizationalgrouptypes_show")
     * @Route("/list/profile-comment-group-types/{id}", name="commentgrouptypes_show")
     * @Route("/list/comment-types/{id}", name="commenttypes_show", options={"expose"=true})
     * @Route("/list/user-wrappers/{id}", name="userwrappers_show")
     * @Route("/list/spot-purposes/{id}", name="spotpurposes_show")
     * @Route("/list/medical-license-statuses/{id}", name="medicalstatuses_show")
     * @Route("/list/certifying-board-organizations/{id}", name="certifyingboardorganizations_show")
     * @Route("/list/training-types/{id}", name="trainingtypes_show")
     * @Route("/list/job-titles/{id}", name="joblists_show")
     * @Route("/list/fellowship-application-statuses/{id}", name="fellappstatuses_show")
     * @Route("/list/fellowship-application-ranks/{id}", name="fellappranks_show")
     * @Route("/list/fellowship-application-language-proficiencies/{id}", name="fellapplanguageproficiency_show")
//     * @Route("/list/collaborations/{id}", name="collaborations_show")
     * @Route("/list/collaboration-types/{id}", name="collaborationtypes_show")
     * @Route("/list/permissions/{id}", name="permission_show")
     * @Route("/list/permission-objects/{id}", name="permissionobject_show")
     * @Route("/list/permission-actions/{id}", name="permissionaction_show")
     * @Route("/list/sites/{id}", name="sites_show")
     * @Route("/list/event-object-types/{id}", name="eventobjecttypes_show")
     * @Route("/list/vacation-request-types/{id}", name="vacreqrequesttypes_show")
     * @Route("/list/healthcare-provider-specialties/{id}", name="healthcareproviderspecialty_show")
     * @Route("/list/object-types/{id}", name="objecttypes_show")
     * @Route("/list/form-nodes/{id}", name="formnodes_show", options={"expose"=true})
     * @Route("/list/object-type-texts/{id}", name="objecttypetexts_show", options={"expose"=true})
     * @Route("/list/blood-product-transfusions/{id}", name="bloodproducttransfusions_show")
     * @Route("/list/transfusion-reaction-types/{id}", name="transfusionreactiontypes_show")
     * @Route("/list/object-type-strings/{id}", name="objecttypestrings_show")
     * @Route("/list/object-type-dropdowns/{id}", name="objecttypedropdowns_show")
     * @Route("/list/blood-types/{id}", name="bloodtypes_show")
     * @Route("/list/transfusion-antibody-screen-results/{id}", name="transfusionantibodyscreenresults_show")
     * @Route("/list/transfusion-crossmatch-results/{id}", name="transfusioncrossmatchresults_show")
     * @Route("/list/transfusion-dat-results/{id}", name="transfusiondatresults_show")
     * @Route("/list/transfusion-hemolysis-check-results/{id}", name="transfusionhemolysischeckresults_show")
     * @Route("/list/object-type-datetimes/{id}", name="objecttypedatetimes_show")
     * @Route("/list/complex-platelet-summary-antibodies/{id}", name="complexplateletsummaryantibodies_show")
     * @Route("/list/cci-unit-platelet-count-default-values/{id}", name="cciunitplateletcountdefaultvalues_show")
     * @Route("/list/cci-platelet-type-transfused/{id}", name="cciplatelettypetransfuseds_show")
     * @Route("/list/platelet-transfusion-product-receiving/{id}", name="platelettransfusionproductreceivings_show")
     * @Route("/list/transfusion-product-status/{id}", name="transfusionproductstatus_show")
     * @Route("/list/week-days/{id}", name="weekdays_show")
     * @Route("/list/months/{id}", name="months_show")
     * @Route("/list/clerical-errors/{id}", name="clericalerrors_show")
     * @Route("/list/lab-result-names/{id}", name="labresultnames_show")
     * @Route("/list/lab-result-units-measures/{id}", name="labresultunitsmeasures_show")
     * @Route("/list/lab-result-flags/{id}", name="labresultflags_show")
     * @Route("/list/pathology-result-signatories/{id}", name="pathologyresultsignatories_show")
     * @Route("/list/object-type-checkboxes/{id}", name="objecttypecheckboxs_show")
     * @Route("/list/object-type-radio-buttons/{id}", name="objecttyperadiobuttons_show")
     * @Route("/list/life-forms/{id}", name="lifeforms_show")
     * @Route("/list/position-track-types/{id}", name="positiontracktypes_show")
     * @Route("/list/translational-research-project-specialties/{id}", name="transresprojectspecialties_show")
     * @Route("/list/translational-research-project-types/{id}", name="transresprojecttypes_show")
     * @Route("/list/translational-research-request-category-types/{id}", name="transresrequestcategorytypes_show")
     * @Route("/list/translational-irb-approval-types/{id}", name="transresirbapprovaltypes_show")
     * @Route("/list/antibodies/{id}", name="antibodies_show")
     *
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:show.html.twig")
     */
    public function showAction(Request $request,$id)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->showList($request,$id);
    }
    public function showList( $request, $id ) {

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";
        //exit('show');

        $em = $this->getDoctrine()->getManager();

        $mapper = $this->classListMapper($pathbase,$request);

        $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);
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
            'sitename' => $this->sitename
        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/list-manager/{id}/edit", name="platformlistmanager_edit")
     * @Route("/list/source-systems/{id}/edit", name="sourcesystems_edit")
     * @Route("/list/roles/{id}/edit", name="role_edit")
     * @Route("/list/institutions/{id}/edit", name="institutions_edit")
     * @Route("/list/states/{id}/edit", name="states_edit")
     * @Route("/list/countries/{id}/edit", name="countries_edit")
     * @Route("/list/board-certifications/{id}/edit", name="boardcertifications_edit")
     * @Route("/list/employment-termination-reasons/{id}/edit", name="employmentterminations_edit")
     * @Route("/list/event-log-event-types/{id}/edit", name="loggereventtypes_edit")
     * @Route("/list/primary-public-user-id-types/{id}/edit", name="usernametypes_edit")
     * @Route("/list/identifier-types/{id}/edit", name="identifiers_edit")
     * @Route("/list/residency-tracks/{id}/edit", name="residencytracks_edit")
     * @Route("/list/fellowship-types/{id}/edit", name="fellowshiptypes_edit")
//     * @Route("/list/research-labs/{id}/edit", name="researchlabs_edit")
     * @Route("/list/location-types/{id}/edit", name="locationtypes_edit")
     * @Route("/list/equipment/{id}/edit", name="equipments_edit")
     * @Route("/list/equipment-types/{id}/edit", name="equipmenttypes_edit")
     * @Route("/list/location-privacy-types/{id}/edit", name="locationprivacy_edit")
     * @Route("/list/role-attributes/{id}/edit", name="roleattributes_edit")
     * @Route("/list/buidlings/{id}/edit", name="buildings_edit")
     * @Route("/list/rooms/{id}/edit", name="rooms_edit")
     * @Route("/list/suites/{id}/edit", name="suites_edit")
     * @Route("/list/floors/{id}/edit", name="floors_edit")
     * @Route("/list/mailboxes/{id}/edit", name="mailboxes_edit")
     * @Route("/list/percent-effort/{id}/edit", name="efforts_edit")
     * @Route("/list/administrative-titles/{id}/edit", name="admintitles_edit")
     * @Route("/list/academic-appointment-titles/{id}/edit", name="apptitles_edit")
     * @Route("/list/training-completion-reasons/{id}/edit", name="completionreasons_edit")
     * @Route("/list/training-degrees/{id}/edit", name="trainingdegrees_edit")
     * @Route("/list/training-majors/{id}/edit", name="trainingmajors_edit")
     * @Route("/list/training-minors/{id}/edit", name="trainingminors_edit")
     * @Route("/list/training-honors/{id}/edit", name="traininghonors_edit")
     * @Route("/list/fellowship-titles/{id}/edit", name="fellowshiptitles_edit")
     * @Route("/list/residency-specialties/{id}/edit", name="residencyspecialtys_edit")
     * @Route("/list/fellowship-subspecialties/{id}/edit", name="fellowshipsubspecialtys_edit")
     * @Route("/list/institution-types/{id}/edit", name="institutiontypes_edit")
     * @Route("/list/document-types/{id}/edit", name="documenttypes_edit")
     * @Route("/list/medical-titles/{id}/edit", name="medicaltitles_edit")
     * @Route("/list/medical-specialties/{id}/edit", name="medicalspecialties_edit")
     * @Route("/list/employment-types/{id}/edit", name="employmenttypes_edit")
     * @Route("/list/grant-source-organizations/{id}/edit", name="sourceorganizations_edit")
     * @Route("/list/languages/{id}/edit", name="languages_edit")
     * @Route("/list/locales/{id}/edit", name="locales_edit")
     * @Route("/list/ranks-of-importance/{id}/edit", name="importances_edit")
     * @Route("/list/authorship-roles/{id}/edit", name="authorshiproles_edit")
     * @Route("/list/lecture-venues/{id}/edit", name="organizations_edit")
     * @Route("/list/cities/{id}/edit", name="cities_edit")
     * @Route("/list/link-types/{id}/edit", name="linktypes_edit")
     * @Route("/list/sexes/{id}/edit", name="sexes_edit")
     * @Route("/list/position-types/{id}/edit", name="positiontypes_edit")
     * @Route("/list/organizational-group-types/{id}/edit", name="organizationalgrouptypes_edit")
     * @Route("/list/profile-comment-group-types/{id}/edit", name="commentgrouptypes_edit")
     * @Route("/list/comment-types/{id}/edit", name="commenttypes_edit")
     * @Route("/list/user-wrappers/{id}/edit", name="userwrappers_edit")
     * @Route("/list/spot-purposes/{id}/edit", name="spotpurposes_edit")
     * @Route("/list/medical-license-statuses/{id}/edit", name="medicalstatuses_edit")
     * @Route("/list/certifying-board-organizations/{id}/edit", name="certifyingboardorganizations_edit")
     * @Route("/list/training-types/{id}/edit", name="trainingtypes_edit")
     * @Route("/list/job-titles/{id}/edit", name="joblists_edit")
     * @Route("/list/fellowship-application-statuses/{id}/edit", name="fellappstatuses_edit")
     * @Route("/list/fellowship-application-ranks/{id}/edit", name="fellappranks_edit")
     * @Route("/list/fellowship-application-language-proficiencies/{id}/edit", name="fellapplanguageproficiency_edit")
//     * @Route("/list/collaborations/{id}/edit", name="collaborations_edit")
     * @Route("/list/collaboration-types/{id}/edit", name="collaborationtypes_edit")
     * @Route("/list/permissions/{id}/edit", name="permission_edit")
     * @Route("/list/permission-objects/{id}/edit", name="permissionobject_edit")
     * @Route("/list/permission-actions/{id}/edit", name="permissionaction_edit")
     * @Route("/list/sites/{id}/edit", name="sites_edit")
     * @Route("/list/event-object-types/{id}/edit", name="eventobjecttypes_edit")
     * @Route("/list/vacation-request-types/{id}/edit", name="vacreqrequesttypes_edit")
     * @Route("/list/healthcare-provider-specialties/{id}/edit", name="healthcareproviderspecialty_edit")
     * @Route("/list/object-types/{id}/edit", name="objecttypes_edit")
     * @Route("/list/form-nodes/{id}/edit", name="formnodes_edit", options={"expose"=true})
     * @Route("/list/object-type-texts/{id}/edit", name="objecttypetexts_edit", options={"expose"=true})
     * @Route("/list/blood-product-transfusions/{id}/edit", name="bloodproducttransfusions_edit")
     * @Route("/list/transfusion-reaction-types/{id}/edit", name="transfusionreactiontypes_edit")
     * @Route("/list/object-type-strings/{id}/edit", name="objecttypestrings_edit")
     * @Route("/list/object-type-dropdowns/{id}/edit", name="objecttypedropdowns_edit")
     * @Route("/list/blood-types/{id}/edit", name="bloodtypes_edit")
     * @Route("/list/transfusion-antibody-screen-results/{id}/edit", name="transfusionantibodyscreenresults_edit")
     * @Route("/list/transfusion-crossmatch-results/{id}/edit", name="transfusioncrossmatchresults_edit")
     * @Route("/list/transfusion-dat-results/{id}/edit", name="transfusiondatresults_edit")
     * @Route("/list/transfusion-hemolysis-check-results/{id}/edit", name="transfusionhemolysischeckresults_edit")
     * @Route("/list/object-type-datetimes/{id}/edit", name="objecttypedatetimes_edit")
     * @Route("/list/complex-platelet-summary-antibodies/{id}/edit", name="complexplateletsummaryantibodies_edit")
     * @Route("/list/cci-unit-platelet-count-default-values/{id}/edit", name="cciunitplateletcountdefaultvalues_edit")
     * @Route("/list/cci-platelet-type-transfused/{id}/edit", name="cciplatelettypetransfuseds_edit")
     * @Route("/list/platelet-transfusion-product-receiving/{id}/edit", name="platelettransfusionproductreceivings_edit")
     * @Route("/list/transfusion-product-status/{id}/edit", name="transfusionproductstatus_edit")
     * @Route("/list/week-days/{id}/edit", name="weekdays_edit")
     * @Route("/list/months/{id}/edit", name="months_edit")
     * @Route("/list/clerical-errors/{id}/edit", name="clericalerrors_edit")
     * @Route("/list/lab-result-names/{id}/edit", name="labresultnames_edit")
     * @Route("/list/lab-result-units-measures/{id}/edit", name="labresultunitsmeasures_edit")
     * @Route("/list/lab-result-flags/{id}/edit", name="labresultflags_edit")
     * @Route("/list/pathology-result-signatories/{id}/edit", name="pathologyresultsignatories_edit")
     * @Route("/list/object-type-checkboxes/{id}/edit", name="objecttypecheckboxs_edit")
     * @Route("/list/object-type-radio-buttons/{id}/edit", name="objecttyperadiobuttons_edit")
     * @Route("/list/life-forms/{id}/edit", name="lifeforms_edit")
     * @Route("/list/position-track-types/{id}/edit", name="positiontracktypes_edit")
     * @Route("/list/translational-research-project-specialties/{id}/edit", name="transresprojectspecialties_edit")
     * @Route("/list/translational-research-project-types/{id}/edit", name="transresprojecttypes_edit")
     * @Route("/list/translational-research-request-category-types/{id}/edit", name="transresrequestcategorytypes_edit")
     * @Route("/list/translational-irb-approval-types/{id}/edit", name="transresirbapprovaltypes_edit")
     * @Route("/list/antibodies/{id}/edit", name="antibodies_edit")
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->editList($request,$id);
    }
    public function editList( $request, $id ) {

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase,$request);

        $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
        }

        //add permissions
        //$this->addPermissions($entity);

        $editForm = $this->createEditForm($entity,$mapper,$pathbase,'edit',false);
        $deleteForm = $this->createDeleteForm($id,$pathbase);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase,
            'sitename' => $this->sitename
        );
    }

    private function addPermissions($entity) {
        if( method_exists($entity,'getPermissions') ) {
            //echo "add permission for ".$entity."<br>";
            $permission = new Permission();
            $entity->addPermission($permission);
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
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $options['user'] = $user;
        $options['entity'] = $entity;
        $options['em'] = $this->getDoctrine()->getManager();

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
     * @Route("/list-manager/{id}", name="platformlistmanager_update")
     * @Route("/list/source-systems/{id}", name="sourcesystems_update")
     * @Route("/list/roles/{id}", name="role_update")
     * @Route("/list/institutions/{id}", name="institutions_update")
     * @Route("/list/states/{id}", name="states_update")
     * @Route("/list/countries/{id}", name="countries_update")
     * @Route("/list/board-certifications/{id}", name="boardcertifications_update")
     * @Route("/list/employment-termination-reasons/{id}", name="employmentterminations_update")
     * @Route("/list/event-log-event-types/{id}", name="loggereventtypes_update")
     * @Route("/list/primary-public-user-id-types/{id}", name="usernametypes_update")
     * @Route("/list/identifier-types/{id}", name="identifiers_update")
     * @Route("/list/residency-tracks/{id}", name="residencytracks_update")
     * @Route("/list/fellowship-types/{id}", name="fellowshiptypes_update")
//     * @Route("/list/research-labs/{id}", name="researchlabs_update")
     * @Route("/list/location-types/{id}", name="locationtypes_update")
     * @Route("/list/equipment/{id}", name="equipments_update")
     * @Route("/list/equipment-types/{id}", name="equipmenttypes_update")
     * @Route("/list/location-privacy-types/{id}", name="locationprivacy_update")
     * @Route("/list/role-attributes/{id}", name="roleattributes_update")
     * @Route("/list/buidlings/{id}", name="buildings_update")
     * @Route("/list/rooms/{id}", name="rooms_update")
     * @Route("/list/suites/{id}", name="suites_update")
     * @Route("/list/floors/{id}", name="floors_update")
     * @Route("/list/mailboxes/{id}", name="mailboxes_update")
     * @Route("/list/percent-effort/{id}", name="efforts_update")
     * @Route("/list/administrative-titles/{id}", name="admintitles_update")
     * @Route("/list/academic-appointment-titles/{id}", name="apptitles_update")
     * @Route("/list/training-completion-reasons/{id}", name="completionreasons_update")
     * @Route("/list/training-degrees/{id}", name="trainingdegrees_update")
     * @Route("/list/training-majors/{id}", name="trainingmajors_update")
     * @Route("/list/training-minors/{id}", name="trainingminors_update")
     * @Route("/list/training-honors/{id}", name="traininghonors_update")
     * @Route("/list/fellowship-titles/{id}", name="fellowshiptitles_update")
     * @Route("/list/residency-specialties/{id}", name="residencyspecialtys_update")
     * @Route("/list/fellowship-subspecialties/{id}", name="fellowshipsubspecialtys_update")
     * @Route("/list/institution-types/{id}", name="institutiontypes_update")
     * @Route("/list/document-types/{id}", name="documenttypes_update")
     * @Route("/list/medical-titles/{id}", name="medicaltitles_update")
     * @Route("/list/medical-specialties/{id}", name="medicalspecialties_update")
     * @Route("/list/employment-types/{id}", name="employmenttypes_update")
     * @Route("/list/grant-source-organizations/{id}", name="sourceorganizations_update")
     * @Route("/list/languages/{id}", name="languages_update")
     * @Route("/list/locales/{id}", name="locales_update")
     * @Route("/list/ranks-of-importance/{id}", name="importances_update")
     * @Route("/list/authorship-roles/{id}", name="authorshiproles_update")
     * @Route("/list/lecture-venues/{id}", name="organizations_update")
     * @Route("/list/cities/{id}", name="cities_update")
     * @Route("/list/link-types/{id}", name="linktypes_update")
     * @Route("/list/sexes/{id}", name="sexes_update")
     * @Route("/list/position-types/{id}", name="positiontypes_update")
     * @Route("/list/organizational-group-types/{id}", name="organizationalgrouptypes_update")
     * @Route("/list/profile-comment-group-types/{id}", name="commentgrouptypes_update")
     * @Route("/list/comment-types/{id}", name="commenttypes_update")
     * @Route("/list/user-wrappers/{id}", name="userwrappers_update")
     * @Route("/list/spot-purposes/{id}", name="spotpurposes_update")
     * @Route("/list/medical-license-statuses/{id}", name="medicalstatuses_update")
     * @Route("/list/certifying-board-organizations/{id}", name="certifyingboardorganizations_update")
     * @Route("/list/training-types/{id}", name="trainingtypes_update")
     * @Route("/list/job-titles/{id}", name="joblists_update")
     * @Route("/list/fellowship-application-statuses/{id}", name="fellappstatuses_update")
     * @Route("/list/fellowship-application-ranks/{id}", name="fellappranks_update")
     * @Route("/list/fellowship-application-language-proficiencies/{id}", name="fellapplanguageproficiency_update")
//     * @Route("/list/collaborations/{id}", name="collaborations_update")
     * @Route("/list/collaboration-types/{id}", name="collaborationtypes_update")
     * @Route("/list/permissions/{id}", name="permission_update")
     * @Route("/list/permission-objects/{id}", name="permissionobject_update")
     * @Route("/list/permission-actions/{id}", name="permissionaction_update")
     * @Route("/list/sites/{id}", name="sites_update")
     * @Route("/list/event-object-types/{id}", name="eventobjecttypes_update")
     * @Route("/list/vacation-request-types/{id}", name="vacreqrequesttypes_update")
     * @Route("/list/healthcare-provider-specialties/{id}", name="healthcareproviderspecialty_update")
     * @Route("/list/object-types/{id}", name="objecttypes_update")
     * @Route("/list/form-nodes/{id}", name="formnodes_update", options={"expose"=true})
     * @Route("/list/object-type-texts/{id}", name="objecttypetexts_update", options={"expose"=true})
     * @Route("/list/blood-product-transfusions/{id}", name="bloodproducttransfusions_update")
     * @Route("/list/transfusion-reaction-types/{id}", name="transfusionreactiontypes_update")
     * @Route("/list/object-type-strings/{id}", name="objecttypestrings_update")
     * @Route("/list/object-type-dropdowns/{id}", name="objecttypedropdowns_update")
     * @Route("/list/blood-types/{id}", name="bloodtypes_update")
     * @Route("/list/transfusion-antibody-screen-results/{id}", name="transfusionantibodyscreenresults_update")
     * @Route("/list/transfusion-crossmatch-results/{id}", name="transfusioncrossmatchresults_update")
     * @Route("/list/transfusion-dat-results/{id}", name="transfusiondatresults_update")
     * @Route("/list/transfusion-hemolysis-check-results/{id}", name="transfusionhemolysischeckresults_update")
     * @Route("/list/object-type-datetimes/{id}", name="objecttypedatetimes_update")
     * @Route("/list/complex-platelet-summary-antibodies/{id}", name="complexplateletsummaryantibodies_update")
     * @Route("/list/cci-unit-platelet-count-default-values/{id}", name="cciunitplateletcountdefaultvalues_update")
     * @Route("/list/cci-platelet-type-transfused/{id}", name="cciplatelettypetransfuseds_update")
     * @Route("/list/platelet-transfusion-product-receiving/{id}", name="platelettransfusionproductreceivings_update")
     * @Route("/list/transfusion-product-status/{id}", name="transfusionproductstatus_update")
     * @Route("/list/week-days/{id}", name="weekdays_update")
     * @Route("/list/months/{id}", name="months_update")
     * @Route("/list/clerical-errors/{id}", name="clericalerrors_update")
     * @Route("/list/lab-result-names/{id}", name="labresultnames_update")
     * @Route("/list/lab-result-units-measures/{id}", name="labresultunitsmeasures_update")
     * @Route("/list/lab-result-flags/{id}", name="labresultflags_update")
     * @Route("/list/pathology-result-signatories/{id}", name="pathologyresultsignatories_update")
     * @Route("/list/object-type-checkboxes/{id}", name="objecttypecheckboxs_update")
     * @Route("/list/object-type-radio-buttons/{id}", name="objecttyperadiobuttons_update")
     * @Route("/list/life-forms/{id}", name="lifeforms_update")
     * @Route("/list/position-track-types/{id}", name="positiontracktypes_update")
     * @Route("/list/translational-research-project-specialties/{id}", name="transresprojectspecialties_update")
     * @Route("/list/translational-research-project-types/{id}", name="transresprojecttypes_update")
     * @Route("/list/translational-research-request-category-types/{id}", name="transresrequestcategorytypes_update")
     * @Route("/list/translational-irb-approval-types/{id}", name="transresirbapprovaltypes_update")
     * @Route("/list/antibodies/{id}", name="antibodies_update")
     *
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:ListForm:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->updateList($request, $id);
    }
    public function updateList( $request, $id ) {

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];

        $em = $this->getDoctrine()->getManager();

        $mapper= $this->classListMapper($pathbase,$request);

        $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);

        //save array of synonyms
        $beforeformSynonyms = clone $entity->getSynonyms();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$mapper['fullClassName'].' entity.');
        }

        //remove permissions: original permissions. Used for roles
        if( method_exists($entity,'getPermissions') ) {
            $originalPermissions = array();
            foreach( $entity->getPermissions() as $permission ) {
                $originalPermissions[] = $permission;
            }
        }

        $deleteForm = $this->createDeleteForm($id,$pathbase);
        $editForm = $this->createEditForm($entity,$mapper,$pathbase,'edit_put_list');
        $editForm->handleRequest($request);

        if( $editForm->isValid() ) {

            //make sure to keep creator and creation date from original entity, according to the requirements (Issue#250):
            //For "Creation Date", "Creator" these variables should not be modifiable via the form even if the user unlocks these fields in the browser.
            $originalEntity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);
            $entity->setCreator($originalEntity->getCreator());
            $entity->setCreatedate($originalEntity->getCreatedate());

            $user = $this->get('security.token_storage')->getToken()->getUser();
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
                    $userSecUtil = $this->get('user_security_utility');
                    //echo "event=".$event."<br>";
                    //print_r($removedCollections);
                    //exit();
                    $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'),$event,$user,$entity,$request,'Role Permission Updated');
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
                $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($entity, "document");
            }

            $em->flush();

            return $this->redirect($this->generateUrl($pathbase.'_show'.$this->postPath, array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'displayName' => $mapper['displayName'],
            'pathbase' => $pathbase,
            'sitename' => $this->sitename
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

        //permission list
        foreach( $entity->getPermissions() as $subentity ) {
            //echo "subentity=".$subentity."<br>";
            $changeset = $uow->getEntityChangeSet($subentity);
            $text = "("."permission ".$this->getEntityId($subentity).")";
            //print_r($changeset);
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );

            //add current object state
            $eventArr[] = "Final state: " . $subentity;
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
//     * @Method("GET")
//     * @Template("OlegUserdirectoryBundle:ListForm:new.html.twig")
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
        $res['fullClassName'] = "Oleg\\UserdirectoryBundle\\Entity\\".$className;
        $res['bundleName'] = "OlegUserdirectoryBundle";

        return $res;
    }
    //////////////////////// EOF tree //////////////////////////////











    public function classListMapper( $route, $request ) {

        $labels = null;

        $bundleName = "UserdirectoryBundle";

        //regular lists
        if (strpos($route, "-") !== false) {
            //sites-list
            $pieces = explode("-", $route);
            $route = $pieces[0];
        }
        if (strpos($route, "_") !== false) {
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
            case "fellappranks":
                $className = "FellAppRank";
                $displayName = "Fellowship Application Ranks";
                $bundleName = "FellAppBundle";
                break;
            case "fellapplanguageproficiency":
                $className = "LanguageProficiency";
                $displayName = "Fellowship Application Language Proficiencies";
                $bundleName = "FellAppBundle";
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
            case "healthcareproviderspecialty":
                $className = "HealthcareProviderSpecialtiesList";
                $displayName = "Healthcare Provider Specialties";
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
            case "transresprojecttypes":
                $className = "ProjectTypeList";
                $displayName = "Translational Research Project Type List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transresrequestcategorytypes":
                $className = "RequestCategoryTypeList";
                $displayName = "Translational Research Request Category Type List";
                $bundleName = "TranslationalResearchBundle";
                break;
            case "transresirbapprovaltypes":
                $className = "IrbApprovalTypeList";
                $displayName = "Translational Research Irb Approval Type List";
                $bundleName = "TranslationalResearchBundle";
                break;

            case "antibodies":
                $className = "AntibodyList";
                $displayName = "Antibody List";
                $bundleName = "TranslationalResearchBundle";
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
            $bundleName = str_replace("Oleg","",$bundleName);
        }

        //echo $route.": className=".$className.", displayName=".$displayName."<br>";

        $res = array();
        $res['className'] = $className;
        $res['fullClassName'] = "Oleg\\".$bundleName."\\Entity\\".$className;
        $res['bundleName'] = "Oleg".$bundleName;
        $res['displayName'] = $displayName;
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
            //$rootList = $em->getRepository('OlegUserdirectoryBundle:PlatformListManagerRootList')->findOneByListId($listId);
            //$rootList = $em->getRepository('OlegUserdirectoryBundle:PlatformListManagerRootList')->findOneByListRootName($routeName);
            $rootList = $em->getRepository('OlegUserdirectoryBundle:PlatformListManagerRootList')->findOneByListName($className);
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
     * @Route("/list-manager/{id}", name="platformlistmanager_delete")
     * @Route("/list/source-systems/{id}", name="sourcesystems_delete")
     * @Route("/list/roles/{id}", name="role_delete")
     * @Route("/list/institutions/{id}", name="institutions_delete")
     * @Route("/list/states/{id}", name="states_delete")
     * @Route("/list/countries/{id}", name="countries_delete")
     * @Route("/list/board-certifications/{id}", name="boardcertifications_delete")
     * @Route("/list/employment-termination-reasons/{id}", name="employmentterminations_delete")
     * @Route("/list/event-log-event-types/{id}", name="loggereventtypes_delete")
     * @Route("/list/primary-public-user-id-types/{id}", name="usernametypes_delete")
     * @Route("/list/identifier-types/{id}", name="identifiers_delete")
     * @Route("/list/residency-tracks/{id}", name="residencytracks_delete")
     * @Route("/list/fellowship-types/{id}", name="fellowshiptypes_delete")
//     * @Route("/list/research-labs/{id}", name="researchlabs_delete")
     * @Route("/list/location-types/{id}", name="locationtypes_delete")
     * @Route("/list/equipment/{id}", name="equipments_delete")
     * @Route("/list/equipment-types/{id}", name="equipmenttypes_delete")
     * @Route("/list/location-privacy-types/{id}", name="locationprivacy_delete")
     * @Route("/list/role-attributes/{id}", name="roleattributes_delete")
     * @Route("/list/buidlings/{id}", name="buildings_delete")
     * @Route("/list/rooms/{id}", name="rooms_delete")
     * @Route("/list/suites/{id}", name="suites_delete")
     * @Route("/list/floors/{id}", name="floors_delete")
     * @Route("/list/mailboxes/{id}", name="mailboxes_delete")
     * @Route("/list/percent-effort/{id}", name="efforts_delete")
     * @Route("/list/administrative-titles/{id}", name="admintitles_delete")
     * @Route("/list/academic-appointment-titles/{id}", name="apptitles_delete")
     * @Route("/list/training-completion-reasons/{id}", name="completionreasons_delete")
     * @Route("/list/training-degrees/{id}", name="trainingdegrees_delete")
     * @Route("/list/training-majors/{id}", name="trainingmajors_delete")
     * @Route("/list/training-minors/{id}", name="trainingminors_delete")
     * @Route("/list/training-honors/{id}", name="traininghonors_delete")
     * @Route("/list/fellowship-titles/{id}", name="fellowshiptitles_delete")
     * @Route("/list/residency-specialties/{id}", name="residencyspecialtys_delete")
     * @Route("/list/fellowship-subspecialties/{id}", name="fellowshipsubspecialtys_delete")
     * @Route("/list/institution-types/{id}", name="institutiontypes_delete")
     * @Route("/list/document-types/{id}", name="documenttypes_delete")
     * @Route("/list/medical-titles/{id}", name="medicaltitles_delete")
     * @Route("/list/medical-specialties/{id}", name="medicalspecialties_delete")
     * @Route("/list/employment-types/{id}", name="employmenttypes_delete")
     * @Route("/list/grant-source-organizations/{id}", name="sourceorganizations_delete")
     * @Route("/list/languages/{id}", name="languages_delete")
     * @Route("/list/locales/{id}", name="locales_delete")
     * @Route("/list/ranks-of-importance/{id}", name="importances_delete")
     * @Route("/list/authorship-roles/{id}", name="authorshiproles_delete")
     * @Route("/list/lecture-venues/{id}", name="organizations_delete")
     * @Route("/list/cities/{id}", name="cities_delete")
     * @Route("/list/link-types/{id}", name="linktypes_delete")
     * @Route("/list/sexes/{id}", name="sexes_delete")
     * @Route("/list/position-types/{id}", name="positiontypes_delete")
     * @Route("/list/organizational-group-types/{id}", name="organizationalgrouptypes_delete")
     * @Route("/list/profile-comment-group-types/{id}", name="commentgrouptypes_delete")
     * @Route("/list/comment-types/{id}", name="commenttypes_delete")
     * @Route("/list/user-wrappers/{id}", name="userwrappers_delete")
     * @Route("/list/spot-purposes/{id}", name="spotpurposes_delete")
     * @Route("/list/medical-license-statuses/{id}", name="medicalstatuses_delete")
     * @Route("/list/certifying-board-organizations/{id}", name="certifyingboardorganizations_delete")
     * @Route("/list/training-types/{id}", name="trainingtypes_delete")
     * @Route("/list/job-titles/{id}", name="joblists_delete")
     * @Route("/list/fellowship-application-statuses/{id}", name="fellappstatuses_delete")
     * @Route("/list/fellowship-application-ranks/{id}", name="fellappranks_delete")
     * @Route("/list/fellowship-application-language-proficiencies/{id}", name="fellapplanguageproficiency_delete")
//     * @Route("/list/collaborations/{id}", name="collaborations_delete")
     * @Route("/list/collaboration-types/{id}", name="collaborationtypes_delete")
     * @Route("/list/permissions/{id}", name="permission_delete")
     * @Route("/list/permission-objects/{id}", name="permissionobject_delete")
     * @Route("/list/permission-actions/{id}", name="permissionaction_delete")
     * @Route("/list/sites/{id}", name="sites_delete")
     * @Route("/list/event-object-types/{id}", name="eventobjecttypes_delete")
     * @Route("/list/vacation-request-types/{id}", name="vacreqrequesttypes_delete")
     * @Route("/list/healthcare-provider-specialties/{id}", name="healthcareproviderspecialty_delete")
     * @Route("/list/object-types/{id}", name="objecttypes_delete")
     * @Route("/list/form-nodes/{id}", name="formnodes_delete", options={"expose"=true})
     * @Route("/list/object-type-texts/{id}", name="objecttypetexts_delete", options={"expose"=true})
     * @Route("/list/blood-product-transfusions/{id}", name="bloodproducttransfusions_delete")
     * @Route("/list/transfusion-reaction-types/{id}", name="transfusionreactiontypes_delete")
     * @Route("/list/object-type-strings/{id}", name="objecttypestrings_delete")
     * @Route("/list/object-type-dropdowns/{id}", name="objecttypedropdowns_delete")
     * @Route("/list/blood-types/{id}", name="bloodtypes_delete")
     * @Route("/list/transfusion-antibody-screen-results/{id}", name="transfusionantibodyscreenresults_delete")
     * @Route("/list/transfusion-crossmatch-results/{id}", name="transfusioncrossmatchresults_delete")
     * @Route("/list/transfusion-dat-results/{id}", name="transfusiondatresults_delete")
     * @Route("/list/transfusion-hemolysis-check-results/{id}", name="transfusionhemolysischeckresults_delete")
     * @Route("/list/object-type-datetimes/{id}", name="objecttypedatetimes_delete")
     * @Route("/list/complex-platelet-summary-antibodies/{id}", name="complexplateletsummaryantibodies_delete")
     * @Route("/list/cci-unit-platelet-count-default-values/{id}", name="cciunitplateletcountdefaultvalues_delete")
     * @Route("/list/cci-platelet-type-transfused/{id}", name="cciplatelettypetransfuseds_delete")
     * @Route("/list/platelet-transfusion-product-receiving/{id}", name="platelettransfusionproductreceivings_delete")
     * @Route("/list/transfusion-product-status/{id}", name="transfusionproductstatus_delete")
     * @Route("/list/week-days/{id}", name="weekdays_delete")
     * @Route("/list/months/{id}", name="months_delete")
     * @Route("/list/clerical-errors/{id}", name="clericalerrors_delete")
     * @Route("/list/lab-result-names/{id}", name="labresultnames_delete")
     * @Route("/list/lab-result-units-measures/{id}", name="labresultunitsmeasures_delete")
     * @Route("/list/lab-result-flags/{id}", name="labresultflags_delete")
     * @Route("/list/pathology-result-signatories/{id}", name="pathologyresultsignatories_delete")
     * @Route("/list/object-type-checkboxes/{id}", name="objecttypecheckboxs_delete")
     * @Route("/list/object-type-radio-buttons/{id}", name="objecttyperadiobuttons_delete")
     * @Route("/list/life-forms/{id}", name="lifeforms_delete")
     * @Route("/list/position-track-types/{id}", name="positiontracktypes_delete")
     * @Route("/list/translational-research-project-specialties/{id}", name="transresprojectspecialties_delete")
     * @Route("/list/translational-research-project-types/{id}", name="transresprojecttypes_delete")
     * @Route("/list/translational-research-request-category-types/{id}", name="transresrequestcategorytypes_delete")
     * @Route("/list/translational-irb-approval-types/{id}", name="transresirbapprovaltypes_delete")
     * @Route("/list/antibodies/{id}", name="antibodies_delete")
     *
     *
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {

        return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
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
            $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);

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
    /////////////////// DELETE IS NOT USED /////////////////////////



    /**
     * Platform List Manager Root List
     * /order/list-manager/list?id=70 => show Roles list (assuming that listName=Roles == listId=70)
     *
     * @Route("/list-manager/id/{listId}", name="platform_list_manager")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:platform_list_manager.html.twig")
     */
    public function platformListManagerAction(Request $request, $listId)
    {

        $em = $this->getDoctrine()->getManager();
        //$rootList = $em->getRepository('OlegUserdirectoryBundle:PlatformListManagerRootList')->findOneByListId($listId);
        $rootList = $em->getRepository('OlegUserdirectoryBundle:PlatformListManagerRootList')->findOneByLinkToListId($listId);
        if( !$rootList ) {
            throw $this->createNotFoundException('Unable to find PlatformListManagerRootList by linkToListId='.$listId);
        }

        //$listName = $rootList->getListName();
        $listRootName = $rootList->getListRootName(); //roles-list

        if( $listRootName ) {
            //return $this->redirect( $this->generateUrl($listRootName) );
            //echo "listRootName=".$listRootName."<br>";

            $request->attributes->set('_route',$listRootName);

            if( strpos($listRootName, "_pathaction") === false ) {
                return $this->forward('OlegUserdirectoryBundle:List:index', array('request' => $request));
            } else {
                return $this->forward('OlegUserdirectoryBundle:ComplexList:index', array('request' => $request));
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
     *
     * @Route("/list-manager/id/{linkToListId}/{entityId}", name="platform_list_manager_element")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:platform_list_manager.html.twig")
     */
    public function platformElementManagerRootElementAction( Request $request, $linkToListId, $entityId ) {

        $em = $this->getDoctrine()->getManager();
        //$rootList = $em->getRepository('OlegUserdirectoryBundle:PlatformListManagerRootList')->findOneByListId($listId);
        $rootList = $em->getRepository('OlegUserdirectoryBundle:PlatformListManagerRootList')->findOneByLinkToListId($linkToListId);
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
            $newRootName = $pathbase."_show";

            $request->attributes->set('_route',$newRootName);

            //exit('1');
            return $this->forward('OlegUserdirectoryBundle:List:show', array('request' => $request, 'id' => $entityId));
        }

        //exit('2');
        return array(
            'routename' => $listRootName,
            'displayName' => 'Platform List Manager Root List with List ID #'.$linkToListId
        );
    }

}
