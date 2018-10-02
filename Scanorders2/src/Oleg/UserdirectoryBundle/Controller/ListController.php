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
     * @Route("/list/translational-business-purposes/", name="transresbusinesspurposes-list")
     * @Route("/list/antibodies/", name="antibodies-list")
     * @Route("/list/custom000/", name="custom000-list")
     * @Route("/list/custom001/", name="custom001-list")
     * @Route("/list/custom002/", name="custom002-list")
     * @Route("/list/custom003/", name="custom003-list")
     * @Route("/list/custom004/", name="custom004-list")
     * @Route("/list/custom005/", name="custom005-list")
     * @Route("/list/custom006/", name="custom006-list")
     * @Route("/list/custom007/", name="custom007-list")
     * @Route("/list/custom008/", name="custom008-list")
     * @Route("/list/custom009/", name="custom009-list")
     * @Route("/list/custom010/", name="custom010-list")
     * @Route("/list/custom011/", name="custom011-list")
     * @Route("/list/custom012/", name="custom012-list")
     * @Route("/list/custom013/", name="custom013-list")
     * @Route("/list/custom014/", name="custom014-list")
     * @Route("/list/custom015/", name="custom015-list")
     * @Route("/list/custom016/", name="custom016-list")
     * @Route("/list/custom017/", name="custom017-list")
     * @Route("/list/custom018/", name="custom018-list")
     * @Route("/list/custom019/", name="custom019-list")
     * @Route("/list/custom020/", name="custom020-list")
     * @Route("/list/custom021/", name="custom021-list")
     * @Route("/list/custom022/", name="custom022-list")
     * @Route("/list/custom023/", name="custom023-list")
     * @Route("/list/custom024/", name="custom024-list")
     * @Route("/list/custom025/", name="custom025-list")
     * @Route("/list/custom026/", name="custom026-list")
     * @Route("/list/custom027/", name="custom027-list")
     * @Route("/list/custom028/", name="custom028-list")
     * @Route("/list/custom029/", name="custom029-list")
     * @Route("/list/custom030/", name="custom030-list")
     * @Route("/list/custom031/", name="custom031-list")
     * @Route("/list/custom032/", name="custom032-list")
     * @Route("/list/custom033/", name="custom033-list")
     * @Route("/list/custom034/", name="custom034-list")
     * @Route("/list/custom035/", name="custom035-list")
     * @Route("/list/custom036/", name="custom036-list")
     * @Route("/list/custom037/", name="custom037-list")
     * @Route("/list/custom038/", name="custom038-list")
     * @Route("/list/custom039/", name="custom039-list")
     * @Route("/list/custom040/", name="custom040-list")
     * @Route("/list/custom041/", name="custom041-list")
     * @Route("/list/custom042/", name="custom042-list")
     * @Route("/list/custom043/", name="custom043-list")
     * @Route("/list/custom044/", name="custom044-list")
     * @Route("/list/custom045/", name="custom045-list")
     * @Route("/list/custom046/", name="custom046-list")
     * @Route("/list/custom047/", name="custom047-list")
     * @Route("/list/custom048/", name="custom048-list")
     * @Route("/list/custom049/", name="custom049-list")
     * @Route("/list/custom050/", name="custom050-list")
     * @Route("/list/custom051/", name="custom051-list")
     * @Route("/list/custom052/", name="custom052-list")
     * @Route("/list/custom053/", name="custom053-list")
     * @Route("/list/custom054/", name="custom054-list")
     * @Route("/list/custom055/", name="custom055-list")
     * @Route("/list/custom056/", name="custom056-list")
     * @Route("/list/custom057/", name="custom057-list")
     * @Route("/list/custom058/", name="custom058-list")
     * @Route("/list/custom059/", name="custom059-list")
     * @Route("/list/custom060/", name="custom060-list")
     * @Route("/list/custom061/", name="custom061-list")
     * @Route("/list/custom062/", name="custom062-list")
     * @Route("/list/custom063/", name="custom063-list")
     * @Route("/list/custom064/", name="custom064-list")
     * @Route("/list/custom065/", name="custom065-list")
     * @Route("/list/custom066/", name="custom066-list")
     * @Route("/list/custom067/", name="custom067-list")
     * @Route("/list/custom068/", name="custom068-list")
     * @Route("/list/custom069/", name="custom069-list")
     * @Route("/list/custom070/", name="custom070-list")
     * @Route("/list/custom071/", name="custom071-list")
     * @Route("/list/custom072/", name="custom072-list")
     * @Route("/list/custom073/", name="custom073-list")
     * @Route("/list/custom074/", name="custom074-list")
     * @Route("/list/custom075/", name="custom075-list")
     * @Route("/list/custom076/", name="custom076-list")
     * @Route("/list/custom077/", name="custom077-list")
     * @Route("/list/custom078/", name="custom078-list")
     * @Route("/list/custom079/", name="custom079-list")
     * @Route("/list/custom080/", name="custom080-list")
     * @Route("/list/custom081/", name="custom081-list")
     * @Route("/list/custom082/", name="custom082-list")
     * @Route("/list/custom083/", name="custom083-list")
     * @Route("/list/custom084/", name="custom084-list")
     * @Route("/list/custom085/", name="custom085-list")
     * @Route("/list/custom086/", name="custom086-list")
     * @Route("/list/custom087/", name="custom087-list")
     * @Route("/list/custom088/", name="custom088-list")
     * @Route("/list/custom089/", name="custom089-list")
     * @Route("/list/custom090/", name="custom090-list")
     * @Route("/list/custom091/", name="custom091-list")
     * @Route("/list/custom092/", name="custom092-list")
     * @Route("/list/custom093/", name="custom093-list")
     * @Route("/list/custom094/", name="custom094-list")
     * @Route("/list/custom095/", name="custom095-list")
     * @Route("/list/custom096/", name="custom096-list")
     * @Route("/list/custom097/", name="custom097-list")
     * @Route("/list/custom098/", name="custom098-list")
     * @Route("/list/custom099/", name="custom099-list")
     * @Route("/list/translational-tissue-processing-services/", name="transrestissueprocessingservices-list")
     * @Route("/list/translational-other-requested-services/", name="transresotherrequestedservices-list")
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
     * @Route("/list/translational-business-purposes/", name="transresbusinesspurposes_create")
     * @Route("/list/antibodies/", name="antibodies_create")
     * @Route("/list/custom000/", name="custom000_create")
     * @Route("/list/custom001/", name="custom001_create")
     * @Route("/list/custom002/", name="custom002_create")
     * @Route("/list/custom003/", name="custom003_create")
     * @Route("/list/custom004/", name="custom004_create")
     * @Route("/list/custom005/", name="custom005_create")
     * @Route("/list/custom006/", name="custom006_create")
     * @Route("/list/custom007/", name="custom007_create")
     * @Route("/list/custom008/", name="custom008_create")
     * @Route("/list/custom009/", name="custom009_create")
     * @Route("/list/custom010/", name="custom010_create")
     * @Route("/list/custom011/", name="custom011_create")
     * @Route("/list/custom012/", name="custom012_create")
     * @Route("/list/custom013/", name="custom013_create")
     * @Route("/list/custom014/", name="custom014_create")
     * @Route("/list/custom015/", name="custom015_create")
     * @Route("/list/custom016/", name="custom016_create")
     * @Route("/list/custom017/", name="custom017_create")
     * @Route("/list/custom018/", name="custom018_create")
     * @Route("/list/custom019/", name="custom019_create")
     * @Route("/list/custom020/", name="custom020_create")
     * @Route("/list/custom021/", name="custom021_create")
     * @Route("/list/custom022/", name="custom022_create")
     * @Route("/list/custom023/", name="custom023_create")
     * @Route("/list/custom024/", name="custom024_create")
     * @Route("/list/custom025/", name="custom025_create")
     * @Route("/list/custom026/", name="custom026_create")
     * @Route("/list/custom027/", name="custom027_create")
     * @Route("/list/custom028/", name="custom028_create")
     * @Route("/list/custom029/", name="custom029_create")
     * @Route("/list/custom030/", name="custom030_create")
     * @Route("/list/custom031/", name="custom031_create")
     * @Route("/list/custom032/", name="custom032_create")
     * @Route("/list/custom033/", name="custom033_create")
     * @Route("/list/custom034/", name="custom034_create")
     * @Route("/list/custom035/", name="custom035_create")
     * @Route("/list/custom036/", name="custom036_create")
     * @Route("/list/custom037/", name="custom037_create")
     * @Route("/list/custom038/", name="custom038_create")
     * @Route("/list/custom039/", name="custom039_create")
     * @Route("/list/custom040/", name="custom040_create")
     * @Route("/list/custom041/", name="custom041_create")
     * @Route("/list/custom042/", name="custom042_create")
     * @Route("/list/custom043/", name="custom043_create")
     * @Route("/list/custom044/", name="custom044_create")
     * @Route("/list/custom045/", name="custom045_create")
     * @Route("/list/custom046/", name="custom046_create")
     * @Route("/list/custom047/", name="custom047_create")
     * @Route("/list/custom048/", name="custom048_create")
     * @Route("/list/custom049/", name="custom049_create")
     * @Route("/list/custom050/", name="custom050_create")
     * @Route("/list/custom051/", name="custom051_create")
     * @Route("/list/custom052/", name="custom052_create")
     * @Route("/list/custom053/", name="custom053_create")
     * @Route("/list/custom054/", name="custom054_create")
     * @Route("/list/custom055/", name="custom055_create")
     * @Route("/list/custom056/", name="custom056_create")
     * @Route("/list/custom057/", name="custom057_create")
     * @Route("/list/custom058/", name="custom058_create")
     * @Route("/list/custom059/", name="custom059_create")
     * @Route("/list/custom060/", name="custom060_create")
     * @Route("/list/custom061/", name="custom061_create")
     * @Route("/list/custom062/", name="custom062_create")
     * @Route("/list/custom063/", name="custom063_create")
     * @Route("/list/custom064/", name="custom064_create")
     * @Route("/list/custom065/", name="custom065_create")
     * @Route("/list/custom066/", name="custom066_create")
     * @Route("/list/custom067/", name="custom067_create")
     * @Route("/list/custom068/", name="custom068_create")
     * @Route("/list/custom069/", name="custom069_create")
     * @Route("/list/custom070/", name="custom070_create")
     * @Route("/list/custom071/", name="custom071_create")
     * @Route("/list/custom072/", name="custom072_create")
     * @Route("/list/custom073/", name="custom073_create")
     * @Route("/list/custom074/", name="custom074_create")
     * @Route("/list/custom075/", name="custom075_create")
     * @Route("/list/custom076/", name="custom076_create")
     * @Route("/list/custom077/", name="custom077_create")
     * @Route("/list/custom078/", name="custom078_create")
     * @Route("/list/custom079/", name="custom079_create")
     * @Route("/list/custom080/", name="custom080_create")
     * @Route("/list/custom081/", name="custom081_create")
     * @Route("/list/custom082/", name="custom082_create")
     * @Route("/list/custom083/", name="custom083_create")
     * @Route("/list/custom084/", name="custom084_create")
     * @Route("/list/custom085/", name="custom085_create")
     * @Route("/list/custom086/", name="custom086_create")
     * @Route("/list/custom087/", name="custom087_create")
     * @Route("/list/custom088/", name="custom088_create")
     * @Route("/list/custom089/", name="custom089_create")
     * @Route("/list/custom090/", name="custom090_create")
     * @Route("/list/custom091/", name="custom091_create")
     * @Route("/list/custom092/", name="custom092_create")
     * @Route("/list/custom093/", name="custom093_create")
     * @Route("/list/custom094/", name="custom094_create")
     * @Route("/list/custom095/", name="custom095_create")
     * @Route("/list/custom096/", name="custom096_create")
     * @Route("/list/custom097/", name="custom097_create")
     * @Route("/list/custom098/", name="custom098_create")
     * @Route("/list/custom099/", name="custom099_create")
     * @Route("/list/translational-tissue-processing-services/", name="transrestissueprocessingservices_create")
     * @Route("/list/translational-other-requested-services/", name="transresotherrequestedservices_create")
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
     * @Route("/list/translational-business-purposes/new", name="transresbusinesspurposes_new")
     * @Route("/list/antibodies/new", name="antibodies_new")
     * @Route("/list/custom000/new", name="custom000_new")
     * @Route("/list/custom001/new", name="custom001_new")
     * @Route("/list/custom002/new", name="custom002_new")
     * @Route("/list/custom003/new", name="custom003_new")
     * @Route("/list/custom004/new", name="custom004_new")
     * @Route("/list/custom005/new", name="custom005_new")
     * @Route("/list/custom006/new", name="custom006_new")
     * @Route("/list/custom007/new", name="custom007_new")
     * @Route("/list/custom008/new", name="custom008_new")
     * @Route("/list/custom009/new", name="custom009_new")
     * @Route("/list/custom010/new", name="custom010_new")
     * @Route("/list/custom011/new", name="custom011_new")
     * @Route("/list/custom012/new", name="custom012_new")
     * @Route("/list/custom013/new", name="custom013_new")
     * @Route("/list/custom014/new", name="custom014_new")
     * @Route("/list/custom015/new", name="custom015_new")
     * @Route("/list/custom016/new", name="custom016_new")
     * @Route("/list/custom017/new", name="custom017_new")
     * @Route("/list/custom018/new", name="custom018_new")
     * @Route("/list/custom019/new", name="custom019_new")
     * @Route("/list/custom020/new", name="custom020_new")
     * @Route("/list/custom021/new", name="custom021_new")
     * @Route("/list/custom022/new", name="custom022_new")
     * @Route("/list/custom023/new", name="custom023_new")
     * @Route("/list/custom024/new", name="custom024_new")
     * @Route("/list/custom025/new", name="custom025_new")
     * @Route("/list/custom026/new", name="custom026_new")
     * @Route("/list/custom027/new", name="custom027_new")
     * @Route("/list/custom028/new", name="custom028_new")
     * @Route("/list/custom029/new", name="custom029_new")
     * @Route("/list/custom030/new", name="custom030_new")
     * @Route("/list/custom031/new", name="custom031_new")
     * @Route("/list/custom032/new", name="custom032_new")
     * @Route("/list/custom033/new", name="custom033_new")
     * @Route("/list/custom034/new", name="custom034_new")
     * @Route("/list/custom035/new", name="custom035_new")
     * @Route("/list/custom036/new", name="custom036_new")
     * @Route("/list/custom037/new", name="custom037_new")
     * @Route("/list/custom038/new", name="custom038_new")
     * @Route("/list/custom039/new", name="custom039_new")
     * @Route("/list/custom040/new", name="custom040_new")
     * @Route("/list/custom041/new", name="custom041_new")
     * @Route("/list/custom042/new", name="custom042_new")
     * @Route("/list/custom043/new", name="custom043_new")
     * @Route("/list/custom044/new", name="custom044_new")
     * @Route("/list/custom045/new", name="custom045_new")
     * @Route("/list/custom046/new", name="custom046_new")
     * @Route("/list/custom047/new", name="custom047_new")
     * @Route("/list/custom048/new", name="custom048_new")
     * @Route("/list/custom049/new", name="custom049_new")
     * @Route("/list/custom050/new", name="custom050_new")
     * @Route("/list/custom051/new", name="custom051_new")
     * @Route("/list/custom052/new", name="custom052_new")
     * @Route("/list/custom053/new", name="custom053_new")
     * @Route("/list/custom054/new", name="custom054_new")
     * @Route("/list/custom055/new", name="custom055_new")
     * @Route("/list/custom056/new", name="custom056_new")
     * @Route("/list/custom057/new", name="custom057_new")
     * @Route("/list/custom058/new", name="custom058_new")
     * @Route("/list/custom059/new", name="custom059_new")
     * @Route("/list/custom060/new", name="custom060_new")
     * @Route("/list/custom061/new", name="custom061_new")
     * @Route("/list/custom062/new", name="custom062_new")
     * @Route("/list/custom063/new", name="custom063_new")
     * @Route("/list/custom064/new", name="custom064_new")
     * @Route("/list/custom065/new", name="custom065_new")
     * @Route("/list/custom066/new", name="custom066_new")
     * @Route("/list/custom067/new", name="custom067_new")
     * @Route("/list/custom068/new", name="custom068_new")
     * @Route("/list/custom069/new", name="custom069_new")
     * @Route("/list/custom070/new", name="custom070_new")
     * @Route("/list/custom071/new", name="custom071_new")
     * @Route("/list/custom072/new", name="custom072_new")
     * @Route("/list/custom073/new", name="custom073_new")
     * @Route("/list/custom074/new", name="custom074_new")
     * @Route("/list/custom075/new", name="custom075_new")
     * @Route("/list/custom076/new", name="custom076_new")
     * @Route("/list/custom077/new", name="custom077_new")
     * @Route("/list/custom078/new", name="custom078_new")
     * @Route("/list/custom079/new", name="custom079_new")
     * @Route("/list/custom080/new", name="custom080_new")
     * @Route("/list/custom081/new", name="custom081_new")
     * @Route("/list/custom082/new", name="custom082_new")
     * @Route("/list/custom083/new", name="custom083_new")
     * @Route("/list/custom084/new", name="custom084_new")
     * @Route("/list/custom085/new", name="custom085_new")
     * @Route("/list/custom086/new", name="custom086_new")
     * @Route("/list/custom087/new", name="custom087_new")
     * @Route("/list/custom088/new", name="custom088_new")
     * @Route("/list/custom089/new", name="custom089_new")
     * @Route("/list/custom090/new", name="custom090_new")
     * @Route("/list/custom091/new", name="custom091_new")
     * @Route("/list/custom092/new", name="custom092_new")
     * @Route("/list/custom093/new", name="custom093_new")
     * @Route("/list/custom094/new", name="custom094_new")
     * @Route("/list/custom095/new", name="custom095_new")
     * @Route("/list/custom096/new", name="custom096_new")
     * @Route("/list/custom097/new", name="custom097_new")
     * @Route("/list/custom098/new", name="custom098_new")
     * @Route("/list/custom099/new", name="custom099_new")
     * @Route("/list/translational-tissue-processing-services/new", name="transrestissueprocessingservices_new")
     * @Route("/list/translational-other-requested-services/new", name="transresotherrequestedservices_new")
     *
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
     * @Route("/list/translational-business-purposes/{id}", name="transresbusinesspurposes_show")
     * @Route("/list/antibodies/{id}", name="antibodies_show")
     * @Route("/list/custom000/{id}", name="custom000_show")
     * @Route("/list/custom001/{id}", name="custom001_show")
     * @Route("/list/custom002/{id}", name="custom002_show")
     * @Route("/list/custom003/{id}", name="custom003_show")
     * @Route("/list/custom004/{id}", name="custom004_show")
     * @Route("/list/custom005/{id}", name="custom005_show")
     * @Route("/list/custom006/{id}", name="custom006_show")
     * @Route("/list/custom007/{id}", name="custom007_show")
     * @Route("/list/custom008/{id}", name="custom008_show")
     * @Route("/list/custom009/{id}", name="custom009_show")
     * @Route("/list/custom010/{id}", name="custom010_show")
     * @Route("/list/custom011/{id}", name="custom011_show")
     * @Route("/list/custom012/{id}", name="custom012_show")
     * @Route("/list/custom013/{id}", name="custom013_show")
     * @Route("/list/custom014/{id}", name="custom014_show")
     * @Route("/list/custom015/{id}", name="custom015_show")
     * @Route("/list/custom016/{id}", name="custom016_show")
     * @Route("/list/custom017/{id}", name="custom017_show")
     * @Route("/list/custom018/{id}", name="custom018_show")
     * @Route("/list/custom019/{id}", name="custom019_show")
     * @Route("/list/custom020/{id}", name="custom020_show")
     * @Route("/list/custom021/{id}", name="custom021_show")
     * @Route("/list/custom022/{id}", name="custom022_show")
     * @Route("/list/custom023/{id}", name="custom023_show")
     * @Route("/list/custom024/{id}", name="custom024_show")
     * @Route("/list/custom025/{id}", name="custom025_show")
     * @Route("/list/custom026/{id}", name="custom026_show")
     * @Route("/list/custom027/{id}", name="custom027_show")
     * @Route("/list/custom028/{id}", name="custom028_show")
     * @Route("/list/custom029/{id}", name="custom029_show")
     * @Route("/list/custom030/{id}", name="custom030_show")
     * @Route("/list/custom031/{id}", name="custom031_show")
     * @Route("/list/custom032/{id}", name="custom032_show")
     * @Route("/list/custom033/{id}", name="custom033_show")
     * @Route("/list/custom034/{id}", name="custom034_show")
     * @Route("/list/custom035/{id}", name="custom035_show")
     * @Route("/list/custom036/{id}", name="custom036_show")
     * @Route("/list/custom037/{id}", name="custom037_show")
     * @Route("/list/custom038/{id}", name="custom038_show")
     * @Route("/list/custom039/{id}", name="custom039_show")
     * @Route("/list/custom040/{id}", name="custom040_show")
     * @Route("/list/custom041/{id}", name="custom041_show")
     * @Route("/list/custom042/{id}", name="custom042_show")
     * @Route("/list/custom043/{id}", name="custom043_show")
     * @Route("/list/custom044/{id}", name="custom044_show")
     * @Route("/list/custom045/{id}", name="custom045_show")
     * @Route("/list/custom046/{id}", name="custom046_show")
     * @Route("/list/custom047/{id}", name="custom047_show")
     * @Route("/list/custom048/{id}", name="custom048_show")
     * @Route("/list/custom049/{id}", name="custom049_show")
     * @Route("/list/custom050/{id}", name="custom050_show")
     * @Route("/list/custom051/{id}", name="custom051_show")
     * @Route("/list/custom052/{id}", name="custom052_show")
     * @Route("/list/custom053/{id}", name="custom053_show")
     * @Route("/list/custom054/{id}", name="custom054_show")
     * @Route("/list/custom055/{id}", name="custom055_show")
     * @Route("/list/custom056/{id}", name="custom056_show")
     * @Route("/list/custom057/{id}", name="custom057_show")
     * @Route("/list/custom058/{id}", name="custom058_show")
     * @Route("/list/custom059/{id}", name="custom059_show")
     * @Route("/list/custom060/{id}", name="custom060_show")
     * @Route("/list/custom061/{id}", name="custom061_show")
     * @Route("/list/custom062/{id}", name="custom062_show")
     * @Route("/list/custom063/{id}", name="custom063_show")
     * @Route("/list/custom064/{id}", name="custom064_show")
     * @Route("/list/custom065/{id}", name="custom065_show")
     * @Route("/list/custom066/{id}", name="custom066_show")
     * @Route("/list/custom067/{id}", name="custom067_show")
     * @Route("/list/custom068/{id}", name="custom068_show")
     * @Route("/list/custom069/{id}", name="custom069_show")
     * @Route("/list/custom070/{id}", name="custom070_show")
     * @Route("/list/custom071/{id}", name="custom071_show")
     * @Route("/list/custom072/{id}", name="custom072_show")
     * @Route("/list/custom073/{id}", name="custom073_show")
     * @Route("/list/custom074/{id}", name="custom074_show")
     * @Route("/list/custom075/{id}", name="custom075_show")
     * @Route("/list/custom076/{id}", name="custom076_show")
     * @Route("/list/custom077/{id}", name="custom077_show")
     * @Route("/list/custom078/{id}", name="custom078_show")
     * @Route("/list/custom079/{id}", name="custom079_show")
     * @Route("/list/custom080/{id}", name="custom080_show")
     * @Route("/list/custom081/{id}", name="custom081_show")
     * @Route("/list/custom082/{id}", name="custom082_show")
     * @Route("/list/custom083/{id}", name="custom083_show")
     * @Route("/list/custom084/{id}", name="custom084_show")
     * @Route("/list/custom085/{id}", name="custom085_show")
     * @Route("/list/custom086/{id}", name="custom086_show")
     * @Route("/list/custom087/{id}", name="custom087_show")
     * @Route("/list/custom088/{id}", name="custom088_show")
     * @Route("/list/custom089/{id}", name="custom089_show")
     * @Route("/list/custom090/{id}", name="custom090_show")
     * @Route("/list/custom091/{id}", name="custom091_show")
     * @Route("/list/custom092/{id}", name="custom092_show")
     * @Route("/list/custom093/{id}", name="custom093_show")
     * @Route("/list/custom094/{id}", name="custom094_show")
     * @Route("/list/custom095/{id}", name="custom095_show")
     * @Route("/list/custom096/{id}", name="custom096_show")
     * @Route("/list/custom097/{id}", name="custom097_show")
     * @Route("/list/custom098/{id}", name="custom098_show")
     * @Route("/list/custom099/{id}", name="custom099_show")
     * @Route("/list/translational-tissue-processing-services/{id}", name="transrestissueprocessingservices_show")
     * @Route("/list/translational-other-requested-services/{id}", name="transresotherrequestedservices_show")
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:show.html.twig")
     */
    public function showAction(Request $request,$id)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        $showEditBtn = false;
        if( $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
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
            'sitename' => $this->sitename,
            'showEditBtn' => $showEditBtn
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
     * @Route("/list/translational-business-purposes/{id}/edit", name="transresbusinesspurposes_edit")
     * @Route("/list/antibodies/{id}/edit", name="antibodies_edit")
     * @Route("/list/custom000/{id}/edit", name="custom000_edit")
     * @Route("/list/custom001/{id}/edit", name="custom001_edit")
     * @Route("/list/custom002/{id}/edit", name="custom002_edit")
     * @Route("/list/custom003/{id}/edit", name="custom003_edit")
     * @Route("/list/custom004/{id}/edit", name="custom004_edit")
     * @Route("/list/custom005/{id}/edit", name="custom005_edit")
     * @Route("/list/custom006/{id}/edit", name="custom006_edit")
     * @Route("/list/custom007/{id}/edit", name="custom007_edit")
     * @Route("/list/custom008/{id}/edit", name="custom008_edit")
     * @Route("/list/custom009/{id}/edit", name="custom009_edit")
     * @Route("/list/custom010/{id}/edit", name="custom010_edit")
     * @Route("/list/custom011/{id}/edit", name="custom011_edit")
     * @Route("/list/custom012/{id}/edit", name="custom012_edit")
     * @Route("/list/custom013/{id}/edit", name="custom013_edit")
     * @Route("/list/custom014/{id}/edit", name="custom014_edit")
     * @Route("/list/custom015/{id}/edit", name="custom015_edit")
     * @Route("/list/custom016/{id}/edit", name="custom016_edit")
     * @Route("/list/custom017/{id}/edit", name="custom017_edit")
     * @Route("/list/custom018/{id}/edit", name="custom018_edit")
     * @Route("/list/custom019/{id}/edit", name="custom019_edit")
     * @Route("/list/custom020/{id}/edit", name="custom020_edit")
     * @Route("/list/custom021/{id}/edit", name="custom021_edit")
     * @Route("/list/custom022/{id}/edit", name="custom022_edit")
     * @Route("/list/custom023/{id}/edit", name="custom023_edit")
     * @Route("/list/custom024/{id}/edit", name="custom024_edit")
     * @Route("/list/custom025/{id}/edit", name="custom025_edit")
     * @Route("/list/custom026/{id}/edit", name="custom026_edit")
     * @Route("/list/custom027/{id}/edit", name="custom027_edit")
     * @Route("/list/custom028/{id}/edit", name="custom028_edit")
     * @Route("/list/custom029/{id}/edit", name="custom029_edit")
     * @Route("/list/custom030/{id}/edit", name="custom030_edit")
     * @Route("/list/custom031/{id}/edit", name="custom031_edit")
     * @Route("/list/custom032/{id}/edit", name="custom032_edit")
     * @Route("/list/custom033/{id}/edit", name="custom033_edit")
     * @Route("/list/custom034/{id}/edit", name="custom034_edit")
     * @Route("/list/custom035/{id}/edit", name="custom035_edit")
     * @Route("/list/custom036/{id}/edit", name="custom036_edit")
     * @Route("/list/custom037/{id}/edit", name="custom037_edit")
     * @Route("/list/custom038/{id}/edit", name="custom038_edit")
     * @Route("/list/custom039/{id}/edit", name="custom039_edit")
     * @Route("/list/custom040/{id}/edit", name="custom040_edit")
     * @Route("/list/custom041/{id}/edit", name="custom041_edit")
     * @Route("/list/custom042/{id}/edit", name="custom042_edit")
     * @Route("/list/custom043/{id}/edit", name="custom043_edit")
     * @Route("/list/custom044/{id}/edit", name="custom044_edit")
     * @Route("/list/custom045/{id}/edit", name="custom045_edit")
     * @Route("/list/custom046/{id}/edit", name="custom046_edit")
     * @Route("/list/custom047/{id}/edit", name="custom047_edit")
     * @Route("/list/custom048/{id}/edit", name="custom048_edit")
     * @Route("/list/custom049/{id}/edit", name="custom049_edit")
     * @Route("/list/custom050/{id}/edit", name="custom050_edit")
     * @Route("/list/custom051/{id}/edit", name="custom051_edit")
     * @Route("/list/custom052/{id}/edit", name="custom052_edit")
     * @Route("/list/custom053/{id}/edit", name="custom053_edit")
     * @Route("/list/custom054/{id}/edit", name="custom054_edit")
     * @Route("/list/custom055/{id}/edit", name="custom055_edit")
     * @Route("/list/custom056/{id}/edit", name="custom056_edit")
     * @Route("/list/custom057/{id}/edit", name="custom057_edit")
     * @Route("/list/custom058/{id}/edit", name="custom058_edit")
     * @Route("/list/custom059/{id}/edit", name="custom059_edit")
     * @Route("/list/custom060/{id}/edit", name="custom060_edit")
     * @Route("/list/custom061/{id}/edit", name="custom061_edit")
     * @Route("/list/custom062/{id}/edit", name="custom062_edit")
     * @Route("/list/custom063/{id}/edit", name="custom063_edit")
     * @Route("/list/custom064/{id}/edit", name="custom064_edit")
     * @Route("/list/custom065/{id}/edit", name="custom065_edit")
     * @Route("/list/custom066/{id}/edit", name="custom066_edit")
     * @Route("/list/custom067/{id}/edit", name="custom067_edit")
     * @Route("/list/custom068/{id}/edit", name="custom068_edit")
     * @Route("/list/custom069/{id}/edit", name="custom069_edit")
     * @Route("/list/custom070/{id}/edit", name="custom070_edit")
     * @Route("/list/custom071/{id}/edit", name="custom071_edit")
     * @Route("/list/custom072/{id}/edit", name="custom072_edit")
     * @Route("/list/custom073/{id}/edit", name="custom073_edit")
     * @Route("/list/custom074/{id}/edit", name="custom074_edit")
     * @Route("/list/custom075/{id}/edit", name="custom075_edit")
     * @Route("/list/custom076/{id}/edit", name="custom076_edit")
     * @Route("/list/custom077/{id}/edit", name="custom077_edit")
     * @Route("/list/custom078/{id}/edit", name="custom078_edit")
     * @Route("/list/custom079/{id}/edit", name="custom079_edit")
     * @Route("/list/custom080/{id}/edit", name="custom080_edit")
     * @Route("/list/custom081/{id}/edit", name="custom081_edit")
     * @Route("/list/custom082/{id}/edit", name="custom082_edit")
     * @Route("/list/custom083/{id}/edit", name="custom083_edit")
     * @Route("/list/custom084/{id}/edit", name="custom084_edit")
     * @Route("/list/custom085/{id}/edit", name="custom085_edit")
     * @Route("/list/custom086/{id}/edit", name="custom086_edit")
     * @Route("/list/custom087/{id}/edit", name="custom087_edit")
     * @Route("/list/custom088/{id}/edit", name="custom088_edit")
     * @Route("/list/custom089/{id}/edit", name="custom089_edit")
     * @Route("/list/custom090/{id}/edit", name="custom090_edit")
     * @Route("/list/custom091/{id}/edit", name="custom091_edit")
     * @Route("/list/custom092/{id}/edit", name="custom092_edit")
     * @Route("/list/custom093/{id}/edit", name="custom093_edit")
     * @Route("/list/custom094/{id}/edit", name="custom094_edit")
     * @Route("/list/custom095/{id}/edit", name="custom095_edit")
     * @Route("/list/custom096/{id}/edit", name="custom096_edit")
     * @Route("/list/custom097/{id}/edit", name="custom097_edit")
     * @Route("/list/custom098/{id}/edit", name="custom098_edit")
     * @Route("/list/custom099/{id}/edit", name="custom099_edit")
     * @Route("/list/translational-tissue-processing-services/{id}/edit", name="transrestissueprocessingservices_edit")
     * @Route("/list/translational-other-requested-services/{id}/edit", name="transresotherrequestedservices_edit")
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
     * @Route("/list/translational-business-purposes/{id}", name="transresbusinesspurposes_update")
     * @Route("/list/antibodies/{id}", name="antibodies_update")
     * @Route("/list/custom000/{id}", name="custom000_update")
     * @Route("/list/custom001/{id}", name="custom001_update")
     * @Route("/list/custom002/{id}", name="custom002_update")
     * @Route("/list/custom003/{id}", name="custom003_update")
     * @Route("/list/custom004/{id}", name="custom004_update")
     * @Route("/list/custom005/{id}", name="custom005_update")
     * @Route("/list/custom006/{id}", name="custom006_update")
     * @Route("/list/custom007/{id}", name="custom007_update")
     * @Route("/list/custom008/{id}", name="custom008_update")
     * @Route("/list/custom009/{id}", name="custom009_update")
     * @Route("/list/custom010/{id}", name="custom010_update")
     * @Route("/list/custom011/{id}", name="custom011_update")
     * @Route("/list/custom012/{id}", name="custom012_update")
     * @Route("/list/custom013/{id}", name="custom013_update")
     * @Route("/list/custom014/{id}", name="custom014_update")
     * @Route("/list/custom015/{id}", name="custom015_update")
     * @Route("/list/custom016/{id}", name="custom016_update")
     * @Route("/list/custom017/{id}", name="custom017_update")
     * @Route("/list/custom018/{id}", name="custom018_update")
     * @Route("/list/custom019/{id}", name="custom019_update")
     * @Route("/list/custom020/{id}", name="custom020_update")
     * @Route("/list/custom021/{id}", name="custom021_update")
     * @Route("/list/custom022/{id}", name="custom022_update")
     * @Route("/list/custom023/{id}", name="custom023_update")
     * @Route("/list/custom024/{id}", name="custom024_update")
     * @Route("/list/custom025/{id}", name="custom025_update")
     * @Route("/list/custom026/{id}", name="custom026_update")
     * @Route("/list/custom027/{id}", name="custom027_update")
     * @Route("/list/custom028/{id}", name="custom028_update")
     * @Route("/list/custom029/{id}", name="custom029_update")
     * @Route("/list/custom030/{id}", name="custom030_update")
     * @Route("/list/custom031/{id}", name="custom031_update")
     * @Route("/list/custom032/{id}", name="custom032_update")
     * @Route("/list/custom033/{id}", name="custom033_update")
     * @Route("/list/custom034/{id}", name="custom034_update")
     * @Route("/list/custom035/{id}", name="custom035_update")
     * @Route("/list/custom036/{id}", name="custom036_update")
     * @Route("/list/custom037/{id}", name="custom037_update")
     * @Route("/list/custom038/{id}", name="custom038_update")
     * @Route("/list/custom039/{id}", name="custom039_update")
     * @Route("/list/custom040/{id}", name="custom040_update")
     * @Route("/list/custom041/{id}", name="custom041_update")
     * @Route("/list/custom042/{id}", name="custom042_update")
     * @Route("/list/custom043/{id}", name="custom043_update")
     * @Route("/list/custom044/{id}", name="custom044_update")
     * @Route("/list/custom045/{id}", name="custom045_update")
     * @Route("/list/custom046/{id}", name="custom046_update")
     * @Route("/list/custom047/{id}", name="custom047_update")
     * @Route("/list/custom048/{id}", name="custom048_update")
     * @Route("/list/custom049/{id}", name="custom049_update")
     * @Route("/list/custom050/{id}", name="custom050_update")
     * @Route("/list/custom051/{id}", name="custom051_update")
     * @Route("/list/custom052/{id}", name="custom052_update")
     * @Route("/list/custom053/{id}", name="custom053_update")
     * @Route("/list/custom054/{id}", name="custom054_update")
     * @Route("/list/custom055/{id}", name="custom055_update")
     * @Route("/list/custom056/{id}", name="custom056_update")
     * @Route("/list/custom057/{id}", name="custom057_update")
     * @Route("/list/custom058/{id}", name="custom058_update")
     * @Route("/list/custom059/{id}", name="custom059_update")
     * @Route("/list/custom060/{id}", name="custom060_update")
     * @Route("/list/custom061/{id}", name="custom061_update")
     * @Route("/list/custom062/{id}", name="custom062_update")
     * @Route("/list/custom063/{id}", name="custom063_update")
     * @Route("/list/custom064/{id}", name="custom064_update")
     * @Route("/list/custom065/{id}", name="custom065_update")
     * @Route("/list/custom066/{id}", name="custom066_update")
     * @Route("/list/custom067/{id}", name="custom067_update")
     * @Route("/list/custom068/{id}", name="custom068_update")
     * @Route("/list/custom069/{id}", name="custom069_update")
     * @Route("/list/custom070/{id}", name="custom070_update")
     * @Route("/list/custom071/{id}", name="custom071_update")
     * @Route("/list/custom072/{id}", name="custom072_update")
     * @Route("/list/custom073/{id}", name="custom073_update")
     * @Route("/list/custom074/{id}", name="custom074_update")
     * @Route("/list/custom075/{id}", name="custom075_update")
     * @Route("/list/custom076/{id}", name="custom076_update")
     * @Route("/list/custom077/{id}", name="custom077_update")
     * @Route("/list/custom078/{id}", name="custom078_update")
     * @Route("/list/custom079/{id}", name="custom079_update")
     * @Route("/list/custom080/{id}", name="custom080_update")
     * @Route("/list/custom081/{id}", name="custom081_update")
     * @Route("/list/custom082/{id}", name="custom082_update")
     * @Route("/list/custom083/{id}", name="custom083_update")
     * @Route("/list/custom084/{id}", name="custom084_update")
     * @Route("/list/custom085/{id}", name="custom085_update")
     * @Route("/list/custom086/{id}", name="custom086_update")
     * @Route("/list/custom087/{id}", name="custom087_update")
     * @Route("/list/custom088/{id}", name="custom088_update")
     * @Route("/list/custom089/{id}", name="custom089_update")
     * @Route("/list/custom090/{id}", name="custom090_update")
     * @Route("/list/custom091/{id}", name="custom091_update")
     * @Route("/list/custom092/{id}", name="custom092_update")
     * @Route("/list/custom093/{id}", name="custom093_update")
     * @Route("/list/custom094/{id}", name="custom094_update")
     * @Route("/list/custom095/{id}", name="custom095_update")
     * @Route("/list/custom096/{id}", name="custom096_update")
     * @Route("/list/custom097/{id}", name="custom097_update")
     * @Route("/list/custom098/{id}", name="custom098_update")
     * @Route("/list/custom099/{id}", name="custom099_update")
     * @Route("/list/translational-tissue-processing-services/{id}", name="transrestissueprocessingservices_update")
     * @Route("/list/translational-other-requested-services/{id}", name="transresotherrequestedservices_update")
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

        $userSecUtil = $this->get('user_security_utility');

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
                    //$userSecUtil = $this->get('user_security_utility');
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

            //EventLog
            $event = "List Updated by $user";
            $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'),$event,$user,$entity,$request,'List Updated');

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
                echo "event=".$event."<br>";
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
     * @Route("/list/translational-business-purposes/{id}", name="transresbusinesspurposes_delete")
     * @Route("/list/antibodies/{id}", name="antibodies_delete")
     * @Route("/list/custom000/{id}", name="custom000_delete")
     * @Route("/list/custom001/{id}", name="custom001_delete")
     * @Route("/list/custom002/{id}", name="custom002_delete")
     * @Route("/list/custom003/{id}", name="custom003_delete")
     * @Route("/list/custom004/{id}", name="custom004_delete")
     * @Route("/list/custom005/{id}", name="custom005_delete")
     * @Route("/list/custom006/{id}", name="custom006_delete")
     * @Route("/list/custom007/{id}", name="custom007_delete")
     * @Route("/list/custom008/{id}", name="custom008_delete")
     * @Route("/list/custom009/{id}", name="custom009_delete")
     * @Route("/list/custom010/{id}", name="custom010_delete")
     * @Route("/list/custom011/{id}", name="custom011_delete")
     * @Route("/list/custom012/{id}", name="custom012_delete")
     * @Route("/list/custom013/{id}", name="custom013_delete")
     * @Route("/list/custom014/{id}", name="custom014_delete")
     * @Route("/list/custom015/{id}", name="custom015_delete")
     * @Route("/list/custom016/{id}", name="custom016_delete")
     * @Route("/list/custom017/{id}", name="custom017_delete")
     * @Route("/list/custom018/{id}", name="custom018_delete")
     * @Route("/list/custom019/{id}", name="custom019_delete")
     * @Route("/list/custom020/{id}", name="custom020_delete")
     * @Route("/list/custom021/{id}", name="custom021_delete")
     * @Route("/list/custom022/{id}", name="custom022_delete")
     * @Route("/list/custom023/{id}", name="custom023_delete")
     * @Route("/list/custom024/{id}", name="custom024_delete")
     * @Route("/list/custom025/{id}", name="custom025_delete")
     * @Route("/list/custom026/{id}", name="custom026_delete")
     * @Route("/list/custom027/{id}", name="custom027_delete")
     * @Route("/list/custom028/{id}", name="custom028_delete")
     * @Route("/list/custom029/{id}", name="custom029_delete")
     * @Route("/list/custom030/{id}", name="custom030_delete")
     * @Route("/list/custom031/{id}", name="custom031_delete")
     * @Route("/list/custom032/{id}", name="custom032_delete")
     * @Route("/list/custom033/{id}", name="custom033_delete")
     * @Route("/list/custom034/{id}", name="custom034_delete")
     * @Route("/list/custom035/{id}", name="custom035_delete")
     * @Route("/list/custom036/{id}", name="custom036_delete")
     * @Route("/list/custom037/{id}", name="custom037_delete")
     * @Route("/list/custom038/{id}", name="custom038_delete")
     * @Route("/list/custom039/{id}", name="custom039_delete")
     * @Route("/list/custom040/{id}", name="custom040_delete")
     * @Route("/list/custom041/{id}", name="custom041_delete")
     * @Route("/list/custom042/{id}", name="custom042_delete")
     * @Route("/list/custom043/{id}", name="custom043_delete")
     * @Route("/list/custom044/{id}", name="custom044_delete")
     * @Route("/list/custom045/{id}", name="custom045_delete")
     * @Route("/list/custom046/{id}", name="custom046_delete")
     * @Route("/list/custom047/{id}", name="custom047_delete")
     * @Route("/list/custom048/{id}", name="custom048_delete")
     * @Route("/list/custom049/{id}", name="custom049_delete")
     * @Route("/list/custom050/{id}", name="custom050_delete")
     * @Route("/list/custom051/{id}", name="custom051_delete")
     * @Route("/list/custom052/{id}", name="custom052_delete")
     * @Route("/list/custom053/{id}", name="custom053_delete")
     * @Route("/list/custom054/{id}", name="custom054_delete")
     * @Route("/list/custom055/{id}", name="custom055_delete")
     * @Route("/list/custom056/{id}", name="custom056_delete")
     * @Route("/list/custom057/{id}", name="custom057_delete")
     * @Route("/list/custom058/{id}", name="custom058_delete")
     * @Route("/list/custom059/{id}", name="custom059_delete")
     * @Route("/list/custom060/{id}", name="custom060_delete")
     * @Route("/list/custom061/{id}", name="custom061_delete")
     * @Route("/list/custom062/{id}", name="custom062_delete")
     * @Route("/list/custom063/{id}", name="custom063_delete")
     * @Route("/list/custom064/{id}", name="custom064_delete")
     * @Route("/list/custom065/{id}", name="custom065_delete")
     * @Route("/list/custom066/{id}", name="custom066_delete")
     * @Route("/list/custom067/{id}", name="custom067_delete")
     * @Route("/list/custom068/{id}", name="custom068_delete")
     * @Route("/list/custom069/{id}", name="custom069_delete")
     * @Route("/list/custom070/{id}", name="custom070_delete")
     * @Route("/list/custom071/{id}", name="custom071_delete")
     * @Route("/list/custom072/{id}", name="custom072_delete")
     * @Route("/list/custom073/{id}", name="custom073_delete")
     * @Route("/list/custom074/{id}", name="custom074_delete")
     * @Route("/list/custom075/{id}", name="custom075_delete")
     * @Route("/list/custom076/{id}", name="custom076_delete")
     * @Route("/list/custom077/{id}", name="custom077_delete")
     * @Route("/list/custom078/{id}", name="custom078_delete")
     * @Route("/list/custom079/{id}", name="custom079_delete")
     * @Route("/list/custom080/{id}", name="custom080_delete")
     * @Route("/list/custom081/{id}", name="custom081_delete")
     * @Route("/list/custom082/{id}", name="custom082_delete")
     * @Route("/list/custom083/{id}", name="custom083_delete")
     * @Route("/list/custom084/{id}", name="custom084_delete")
     * @Route("/list/custom085/{id}", name="custom085_delete")
     * @Route("/list/custom086/{id}", name="custom086_delete")
     * @Route("/list/custom087/{id}", name="custom087_delete")
     * @Route("/list/custom088/{id}", name="custom088_delete")
     * @Route("/list/custom089/{id}", name="custom089_delete")
     * @Route("/list/custom090/{id}", name="custom090_delete")
     * @Route("/list/custom091/{id}", name="custom091_delete")
     * @Route("/list/custom092/{id}", name="custom092_delete")
     * @Route("/list/custom093/{id}", name="custom093_delete")
     * @Route("/list/custom094/{id}", name="custom094_delete")
     * @Route("/list/custom095/{id}", name="custom095_delete")
     * @Route("/list/custom096/{id}", name="custom096_delete")
     * @Route("/list/custom097/{id}", name="custom097_delete")
     * @Route("/list/custom098/{id}", name="custom098_delete")
     * @Route("/list/custom099/{id}", name="custom099_delete")
     * @Route("/list/translational-tissue-processing-services/{id}", name="transrestissueprocessingservices_delete")
     * @Route("/list/translational-other-requested-services/{id}", name="transresotherrequestedservices_delete")
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
