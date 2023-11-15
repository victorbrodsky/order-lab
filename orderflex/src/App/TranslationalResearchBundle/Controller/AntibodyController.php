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

namespace App\TranslationalResearchBundle\Controller;

use App\TranslationalResearchBundle\Entity\AntibodyList;
use App\TranslationalResearchBundle\Entity\VisualInfo;
use App\TranslationalResearchBundle\Form\AntibodyFilterType;
use App\TranslationalResearchBundle\Form\AntibodyType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AntibodyController extends OrderAbstractController
{
    //Custom Antibody list
    #[Route(path: '/antibodies/', name: 'translationalresearch_antibodies', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Antibody/antibodies.html.twig')]
    public function indexAntibodiesAction(Request $request)
    {
        if(
            false == $this->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN') &&
            false === $this->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }
        
        $listArr = $this->getList($request);
        $listArr['title'] = "Antibodies";
        $listArr['postPath'] = "_translationalresearch";

        return $listArr;
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
        $filterform = $this->createForm(AntibodyFilterType::class, null, array(
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

            //echo "searchStr=$searchStr <br>";
            $dql->andWhere($searchStr);
            $dqlParameters['search'] = '%'.$search.'%';
        }

        if( $filterTypes && count($filterTypes) > 0 ) {
            $dql->andWhere("ent.type IN (:filterTypes)");
            $dqlParameters['filterTypes'] = $filterTypes;
        }

        //echo "dql=".$dql."<br>";

        $em = $this->getDoctrine()->getManager();
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
            //'linkToListId' => $mapper['linkToListId'],
            'pathbase' => $pathbase,
            'withCreateNewEntityLink' => $createNew,
            'filterform' => $filterform->createView(),
            'routename' => $routeName,
            //'sitename' => $this->sitename,
            'cycle' => 'show'
        );
    }

    #[Route(path: '/antibody/new', name: 'translationalresearch_antibody_new', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Antibody/new.html.twig')]
    public function newAction(Request $request)
    {
        if(
            false == $this->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$user = $this->getUser();
        $cycle = "new";

//        $antibody = new AntibodyList($user);
//
//        $antibody->setCreatedate(new \DateTime());
//        $antibody->setType('user-added');
//        $antibody->setCreator($user);
//
//        $fullClassName = "App\\"."TranslationalResearchBundle"."\\Entity\\"."AntibodyList";
//        $query = $em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM '.$fullClassName.' c');
//        $nextorder = $query->getSingleResult()['maxorderinlist']+10;
//        $antibody->setOrderinlist($nextorder);
//
//        //Add default VisualInfo (we know the three types of uploads ahead of time):
//        //Region of Interest Image(s) [Up to 10 images, up to 10MB each]
//        //Whole Slide Image(s) [Up to 2 images, up to 2GB each]
//        $visualInfos = $antibody->getVisualInfos();
//        if( count($visualInfos) == 0 ) {
//            $visualInfo = new VisualInfo($user);
//            $visualInfo->setUploadedType('Region Of Interest');
//            $antibody->addVisualInfo($visualInfo);
//
//            $visualInfo = new VisualInfo($user);
//            $visualInfo->setUploadedType('Whole Slide Image');
//            $antibody->addVisualInfo($visualInfo);
//        }
        $antibody = $this->createEditAntibody();

        $form = $this->createAntibodyForm($antibody,$cycle); //new

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            exit('antibody new');

            $msg = "Create new antibody";

            $this->addFlash(
                'notice',
                $msg
            );

            return $this->redirectToRoute('translationalresearch_antibody_show', array('id' => $antibody->getId()));
        }//$form->isSubmitted()

        return array(
            'antibody' => $antibody,
            'form' => $form->createView(),
            'title' => "New Antibody",
            'cycle' => $cycle
        );
    }


    #[Route(path: '/antibody/edit/{id}', name: 'translationalresearch_antibody_edit', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Antibody/new.html.twig')]
    public function editAction(Request $request, AntibodyList $antibody)
    {
        if(
            false == $this->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "edit";

        $antibody = $this->createEditAntibody($antibody);

        $form = $this->createAntibodyForm($antibody,$cycle); //new

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            exit('antibody edit');

            $msg = "Create new antibody";

            $this->addFlash(
                'notice',
                $msg
            );

            return $this->redirectToRoute('translationalresearch_antibody_show', array('id' => $antibody->getId()));
        }//$form->isSubmitted()

        return array(
            'antibody' => $antibody,
            'form' => $form->createView(),
            'title' => "New Antibody",
            'cycle' => $cycle
        );
    }

    #[Route(path: '/antibody/show/{id}', name: 'translationalresearch_antibody_show', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Antibody/new.html.twig')]
    public function showAction(Request $request, AntibodyList $antibody)
    {
        if( false == $this->isGranted('ROLE_TRANSRES_USER') ) { //ROLE_TRANSRES_REQUESTER
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $em = $this->getDoctrine()->getManager();

        $cycle = "show";

        $form = $this->createAntibodyForm($antibody, $cycle); //show

        return array(
            'antibody' => $antibody,
            'form' => $form->createView(),
            'title' => "New Antibody",
            'cycle' => $cycle
        );
    }


    public function createAntibodyForm( $antibody, $cycle ) {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'antibody' => $antibody,
        );


        $mapper = $this->classListMapper();
        $params['mapper'] = $mapper;

        $disabled = true;

        if( $cycle == "new" ) {
            $disabled = false;
        }

        if( $cycle == "show" ) {
            $disabled = true;
        }

        if( $cycle == "edit" ) {
            $disabled = false;
        }

        if( $cycle == "download" ) {
            $disabled = true;
        }

        $form = $this->createForm(AntibodyType::class, $antibody, array(
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));

        return $form;
    }

    public function createEditAntibody( $antibody=null, $user=null ) {

        if( !$user ) {
            $user = $this->getUser();
        }

        if( !$antibody ) {
            $antibody = new AntibodyList($user);

            $antibody->setCreatedate(new \DateTime());
            $antibody->setType('user-added');
            $antibody->setCreator($user);

            $fullClassName = "App\\" . "TranslationalResearchBundle" . "\\Entity\\" . "AntibodyList";
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery('SELECT MAX(c.orderinlist) as maxorderinlist FROM ' . $fullClassName . ' c');
            $nextorder = $query->getSingleResult()['maxorderinlist'] + 10;
            $antibody->setOrderinlist($nextorder);
        }

        //Add default VisualInfo (we know the three types of uploads ahead of time):
        //Region of Interest Image(s) [Up to 10 images, up to 10MB each]
        //Whole Slide Image(s) [Up to 2 images, up to 2GB each]
        $visualInfos = $antibody->getVisualInfos();
        $visualInfoROI = false;
        $visualInfoWSI = false;
        foreach($visualInfos as $visualInfo) {
            if( $visualInfo->getUploadedType() == 'Region Of Interest' ) {
                $visualInfoROI = true;
            }
            if( $visualInfo->getUploadedType() == 'Whole Slide Image' ) {
                $visualInfoWSI = true;
            }
        }

        if( !$visualInfoROI ) {
            $visualInfo = new VisualInfo($user);
            $visualInfo->setUploadedType('Region Of Interest');
            $antibody->addVisualInfo($visualInfo);
        }

        if( !$visualInfoWSI ) {
            $visualInfo = new VisualInfo($user);
            $visualInfo->setUploadedType('Whole Slide Image');
            $antibody->addVisualInfo($visualInfo);
        }

//        if( count($visualInfos) == 0 ) {
//            $visualInfo = new VisualInfo($user);
//            $visualInfo->setUploadedType('Region Of Interest');
//            $antibody->addVisualInfo($visualInfo);
//
//            $visualInfo = new VisualInfo($user);
//            $visualInfo->setUploadedType('Whole Slide Image');
//            $antibody->addVisualInfo($visualInfo);
//        }

        return $antibody;
    }

    public function classListMapper() {

        $bundleName = "TranslationalResearchBundle";
        $className = "AntibodyList";
        $displayName = "Antibody List";

        $mapper = array();
        $mapper['className'] = $className;
        $mapper['fullClassName'] = "App\\".$bundleName."\\Entity\\".$className;
        $mapper['entityNamespace'] = "App\\".$bundleName."\\Entity";
        $mapper['bundleName'] = $bundleName;
        $mapper['displayName'] = $displayName . ", class: [" . $className . "]";
        return $mapper;
    }
}
