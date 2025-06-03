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
use App\TranslationalResearchBundle\Entity\AntibodyPanelList;
use App\TranslationalResearchBundle\Entity\VisualInfo;
use App\TranslationalResearchBundle\Form\AntibodyFilterType;
use App\TranslationalResearchBundle\Form\AntibodyType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Response;
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

        //echo "indexAntibodiesAction <br>";

        $listArr = $this->getList($request); //list
        //$listArr['title'] = "Antibodies";
        $listArr['postPath'] = "_translationalresearch";

        return $listArr;
    }
    public function getList($request, $onlyPublic=false, $limit=50) {

        $transresUtil = $this->container->get('transres_util');
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
        //$className = $mapper['className'];

        //synonyms and original
        $dql->leftJoin("ent.synonyms", "synonyms");
        $dql->addGroupBy('synonyms.name');
        $dql->leftJoin("ent.original", "original");
        $dql->addGroupBy('original.name');
        $dql->leftJoin("ent.categoryTags", "categoryTags");
        $dql->addGroupBy('categoryTags.name');
        $dql->leftJoin("ent.antibodyLabs", "antibodyLabs");
        //$dql->addGroupBy('antibodyLabs.name');
        $dql->addGroupBy('antibodyLabs.abbreviation');
        $dql->leftJoin("ent.antibodyPanels", "antibodyPanels");
        $dql->addGroupBy('antibodyPanels.name');
        $dql->leftJoin("ent.associates", "associates");
        $dql->addGroupBy('associates.name');

        //$useWalker = false;
        $useWalker = true;

        $advancedFilter = 0;

        //$dql->leftJoin("ent.objectType", "objectType");
        if( method_exists($entityClass,'getObjectType') ) {
            $dql->leftJoin("ent.objectType", "objectType");
            $dql->addGroupBy('objectType.name');
        }

        //Pass sorting parameters directly to query; Somehow, knp_paginator does not sort correctly according to sorting parameters
        $postData = $request->query->all();
        if (isset($postData['sort'])) {
            $dql->orderBy($postData['sort'], $postData['direction']);
        } else {
            $dql->orderBy("ent.orderinlist", "ASC");
        }

        $dqlParameters = array();

        $publicFormPage = false;
        if( $onlyPublic ) {
            $publicFormPage = true;
        }
        //echo "1 publicFormPage=".$publicFormPage."<br>";
        $params = array(
            "className" => $mapper['className'],
            "publicFormPage" => $publicFormPage
        );
        $filterform = $this->createForm(AntibodyFilterType::class, null, array(
            //'action' => $this->generateUrl($routeName),
            'form_custom_value'=>$params,
            'method' => 'GET',
        ));


        $name = null;
        $description = null;
        $categorytags = null;
        $antibodylabs = null;
        $antibodypanels = null;
        //$public = null;
        //secondary filter
        $clone = null;
        $host = null;
        $reactivity = null;
        $company = null;
        $catalog = null;
        $control = null;
        $protocol = null;
        $retrieval = null;
        $dilution = null;
        $comment = null;
        $hasDocument = null;
        $hasVisualInfo = null;


        //$filterform->submit($request);
        $filterform->handleRequest($request);

        $search = $filterform['search']->getData();

        //if( $publicFormPage === false ) {
            $name = $filterform['name']->getData();
            $description = $filterform['description']->getData();
            $categorytags = $filterform['categorytags']->getData();
            $antibodylabs = $filterform['antibodylabs']->getData();
            $antibodypanels = $filterform['antibodypanels']->getData();
            //$public = $filterform['public']->getData();
            //secondary filter
            $clone = $filterform['clone']->getData();
            $host = $filterform['host']->getData();
            $reactivity = $filterform['reactivity']->getData();
            $company = $filterform['company']->getData();

            $catalog = $filterform['catalog']->getData();
            $control = $filterform['control']->getData();
            $protocol = $filterform['protocol']->getData();
            $retrieval = $filterform['retrieval']->getData();
            $dilution = $filterform['dilution']->getData();
            $comment = $filterform['comment']->getData();

            $hasDocument = $filterform['document']->getData();
            $hasVisualInfo = $filterform['visual']->getData();
            //$hasRoi = $filterform['hasRoi']->getData();
            //$hasWsi = $filterform['hasWsi']->getData();
        //}

        //echo "search=".$search."<br>";
        //$search = $request->request->get('filter')['search'];
        //$search = $request->query->get('search');
        //echo "2search=".$search."<br>";
        //dump($filterform);
        //echo "control=".$control."<br>";
        //exit('111');

        $public = "public";
        if( isset($filterform['public']) ) {
            $public = $filterform['public']->getData();
        }
        //overwrite $public to show only public antibodies
        if( $onlyPublic ) {
            $public = "public";
        }

        $filterTypes = null;
        if( isset($filterform['type']) ) {
            $filterTypes = $filterform['type']->getData();
        }

        if( $publicFormPage === true ) {
            //set type to
//            'filter[public]' => 'Public',
//                    'filter[type][0]' => 'default',
//                    'filter[type][1]' => 'user-added',
            $filterTypes = array('default','user-added');
        }

        if( $search ) {
            //echo "search=".$search."<br>";
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

            //AntibodyList
            //$dql->leftJoin("ent.categoryTags", "categoryTags");
            //$dql->addGroupBy('categoryTags');

            $searchStr = $searchStr . " OR LOWER(ent.category) LIKE LOWER(:search)";
            $searchStr = $searchStr . " OR LOWER(categoryTags.name) LIKE LOWER(:search)";
            $searchStr = $searchStr . " OR LOWER(antibodyLabs.name) LIKE LOWER(:search)";
            $searchStr = $searchStr . " OR LOWER(antibodyLabs.abbreviation) LIKE LOWER(:search)";
            $searchStr = $searchStr . " OR LOWER(antibodyPanels.name) LIKE LOWER(:search)";
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

            //echo "searchStr=$searchStr <br>";
            $dql->andWhere($searchStr);
            $dqlParameters['search'] = '%'.$search.'%';
        }

        if( $filterTypes && count($filterTypes) > 0 ) {
            //echo "types=".count($filterTypes)."<br>";
            $dql->andWhere("ent.type IN (:filterTypes)");
            $dqlParameters['filterTypes'] = $filterTypes;
        }

        if( $name ) {
            //echo "name=".$name."<br>";
            $dql->andWhere("LOWER(ent.name) LIKE LOWER(:name)");
            $dqlParameters['name'] = '%'.$name.'%';
        }

        if( $description ) {
            //echo "description=".$description."<br>";
            $dql->andWhere("LOWER(ent.description) LIKE LOWER(:description)");
            $dqlParameters['description'] = '%'.$description.'%';
        }

        if( $categorytags && count($categorytags) > 0 ) {
            $dql->andWhere("categoryTags.id IN (:categoryTags)");
            $dqlParameters['categoryTags'] = $categorytags;
        }

        if( $public ) {
            //echo "public=".$public."<br>";
            if( strtolower($public) == 'public' ) {
                $dql->andWhere("ent.openToPublic = TRUE");
            } else {
                $dql->andWhere("ent.openToPublic IS NULL OR ent.openToPublic = FALSE");
            }
        }

        //Secondary filter
        if( $clone ) {
            $dql->andWhere("LOWER(ent.clone) LIKE LOWER(:clone)");
            $dqlParameters['clone'] = '%'.$clone.'%';
            $advancedFilter++;
        }
        if( $host ) {
            $dql->andWhere("LOWER(ent.host) LIKE LOWER(:host)");
            $dqlParameters['host'] = '%'.$host.'%';
            $advancedFilter++;
        }
        if( $reactivity ) {
            $dql->andWhere("LOWER(ent.reactivity) LIKE LOWER(:reactivity)");
            $dqlParameters['reactivity'] = '%'.$reactivity.'%';
            $advancedFilter++;
        }
        if( $company ) {
            $dql->andWhere("LOWER(ent.company) LIKE LOWER(:company)");
            $dqlParameters['company'] = '%'.$company.'%';
            $advancedFilter++;
        }
        if( $catalog ) {
            $dql->andWhere("LOWER(ent.catalog) LIKE LOWER(:catalog)");
            $dqlParameters['catalog'] = '%'.$catalog.'%';
            $advancedFilter++;
        }
        if( $control ) {
            $dql->andWhere("LOWER(ent.control) LIKE LOWER(:control)");
            $dqlParameters['control'] = '%'.$control.'%';
            $advancedFilter++;
        }
        if( $protocol ) {
            $dql->andWhere("LOWER(ent.protocol) LIKE LOWER(:protocol)");
            $dqlParameters['protocol'] = '%'.$protocol.'%';
            $advancedFilter++;
        }
        if( $retrieval ) {
            $dql->andWhere("LOWER(ent.retrieval) LIKE LOWER(:retrieval)");
            $dqlParameters['retrieval'] = '%'.$retrieval.'%';
            $advancedFilter++;
        }
        if( $dilution ) {
            $dql->andWhere("LOWER(ent.dilution) LIKE LOWER(:dilution)");
            $dqlParameters['dilution'] = '%'.$dilution.'%';
            $advancedFilter++;
        }
        if( $comment ) {
            $dql->andWhere("LOWER(ent.comment) LIKE LOWER(:comment)");
            $dqlParameters['comment'] = '%'.$comment.'%';
            $advancedFilter++;
        }

        if( $hasDocument ) {
            //$dql->andWhere("ent.documents IS NOT NULL");
            $dql->leftJoin("ent.documents", "documents");
            $dql->andWhere("documents IS NOT NULL");
            $advancedFilter++;
        }
        if( $hasVisualInfo ) {
            $dql->leftJoin("ent.visualInfos", "visualInfos");
            $dql->leftJoin("visualInfos.documents", "visualInfosDocuments");
            $dql->andWhere("visualInfosDocuments IS NOT NULL");
            $advancedFilter++;
        }

//        echo "antibodylabs=".$antibodylabs."<br>";
        //echo "antibodylabs=".count($antibodylabs)."<br>";
//        foreach($antibodylabs as $antibodylab) {
//            echo "antibodylab=".$antibodylab."<br>";
//        }
//        exit('111');
        if( $antibodylabs && count($antibodylabs) > 0 ) {
            $dql->andWhere("antibodyLabs.id IN (:antibodyLabs)");
            $dqlParameters['antibodyLabs'] = $antibodylabs;
            $advancedFilter++;
        }

        if( $antibodypanels && count($antibodypanels) > 0 ) {
            $dql->andWhere("antibodyPanels.id IN (:antibodyPanels)");
            $dqlParameters['antibodyPanels'] = $antibodypanels;
            $advancedFilter++;
        }

        //echo "dql=".$dql."<br>";

        //$em = $this->getDoctrine()->getManager();
        //$limit = 50;

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters( $dqlParameters );
        }

        $totalAntibodies = $query->getResult();
        $totalAntibodiesCount = count($totalAntibodies);

        if( $useWalker ) {
            $walker = array('wrap-queries'=>true);
        } else {
            $walker = array();
        }

        $paginator = $this->container->get('knp_paginator');
        $entities = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            $limit,                          /*limit per page*/
            $walker
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

        $matchingAntibodyIdsArr = $transresUtil->getAntibodyIdsArrByDqlParameters($dql,$dqlParameters);
        //$dql, $dqlParameters
        $allGlobalAntibodys = $transresUtil->getTotalAntibodyCount();
        //echo "matching=".count($matchingAntibodyIdsArr).", allGlobalAntibodys=$allGlobalAntibodys"."<br>";
        $title = "Antibodies";
        $title = $title . " (Matching " . count($matchingAntibodyIdsArr) . ", Total " . $allGlobalAntibodys . ")";

        //echo "pathbase=".$pathbase."<br>";
        //echo "routeName=".$routeName."<br>";
        //exit('111');

        return array(
            'entities' => $entities,
            'displayName' => $mapper['displayName'],
            //'linkToListId' => $mapper['linkToListId'],
            'pathbase' => $pathbase,
            'withCreateNewEntityLink' => $createNew,
            'filterFormObject' => $filterform,
            'filterform' => $filterform->createView(),
            'routename' => $routeName,
            //'sitename' => $this->sitename,
            'cycle' => 'show',
            'advancedFilter' => $advancedFilter,
            'matchingAntibodyIdsArr' => $matchingAntibodyIdsArr,
            'title' => $title,
            'limit' => $limit,
            'totalAntibodiesCount' => $totalAntibodiesCount
        );
    }

    /**
     * Download multiple filtered projects
     */
    #[Route(path: '/download-antibody-spreadsheet-post', methods: ['POST'], name: 'translationalresearch_download_antibody_spreadsheet')]
    public function downloadApplicantListExcelPostAction(Request $request) {

        if (false == $this->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$ids = $request->query->get('projectids');
        $ids = $request->request->get('ids');
        //exit("ids=".$ids);

        $limit = null;
        //exit("ids=".$ids);
        //exit("limit=".$limit);

        if( $ids ) {
            if( is_array($ids) && count($ids) == 0 ) {
                exit("No Antibodies to Export to spreadsheet");
            }
        }

        if( !$ids ) {
            exit("No Antibodies to Export to spreadsheet");
        }

        $transresUtil = $this->container->get('transres_util');

        //[YEAR] [WCMC (top level of actual institution)] [FELLOWSHIP-TYPE] Fellowship Candidate Data generated on [DATE] at [TIME] EST.xls
        //$fileName = "Projects ".date('m/d/Y H:i').".xlsx";
        $fileName = "Antibodies-".date('m-d-Y').".xlsx";

        $antibodyIdsArr = explode(',', $ids);

        //Spout uses less memory
        $transresUtil->createAntibodyExcelSpout($antibodyIdsArr,$fileName,$limit);
        //header('Content-Disposition: attachment;filename="'.$fileName.'"');
        exit();
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

        $antibody = $this->createEditAntibody();

        $form = $this->createAntibodyForm($antibody,$cycle); //new

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //exit('antibody new');

            $em->getRepository(Document::class)->processDocuments($antibody, "document");

            $removedInfo = $this->removeEmptyVisualInfo($antibody);

            $em->persist($antibody);
            $em->flush();

            $msg = "Created new antibody ".$antibody; //."; ".$removedInfo;

            $this->addFlash(
                'notice',
                $msg
            );

            return $this->redirectToRoute('translationalresearch_antibody_show', array('id' => $antibody->getId()));
        }//$form->isSubmitted()

        return array(
            'antibody' => $antibody,
            'form' => $form->createView(),
            'title' => "Create New Antibody",
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

        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');

        $cycle = "edit";

        $antibody = $this->createEditAntibody($antibody);

        $originalVisualInfos = array();
        foreach( $antibody->getVisualInfos() as $visualInfo ) {
            $originalVisualInfos[] = $visualInfo;
        }

        $originalName = NULL;
        if( method_exists($antibody,'getName') ) {
            $originalName = $antibody->getName();
        }
        $originalType = NULL;
        if( method_exists($antibody,'getType') ) {
            $originalType = $antibody->getType();
        }

        $form = $this->createAntibodyForm($antibody,$cycle); //new

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //exit('antibody edit /antibody/edit/');

            $user = $this->getUser();
            $em->getRepository(Document::class)->processDocuments($antibody, "document");

            $removedInfo1 = $this->removeEmptyVisualInfo($antibody);
            $removedInfo2 = $this->removeVisualInfoCollection($originalVisualInfos,$antibody->getVisualInfos(),$antibody);

            $em->flush();

            $newName = "Unknown";
            if( method_exists($antibody,"getName") ) {
                $newName = $antibody->getName();
            }
            $newType = "Unknown";
            if( method_exists($antibody,"getType") ) {
                $newType = $antibody->getType();
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
            if( $updatedInfo ) {
                $updatedInfo = ": ".$updatedInfo;
            }

            $msg = "Updated antibody ".$antibody; //."; ".$removedInfo1."; ".$removedInfo2;

            $this->addFlash(
                'notice',
                $msg."; ".$updatedInfo
            );

            //EventLog
            $event = "Antibody '".$antibody."' updated by $user" . $updatedInfo;
            $userSecUtil->createUserEditEvent($this->getParameter('employees.sitename'),$event,$user,$antibody,$request,'List Updated');

            return $this->redirectToRoute('translationalresearch_antibody_show', array('id' => $antibody->getId()));
        }//$form->isSubmitted()

        return array(
            'antibody' => $antibody,
            'form' => $form->createView(),
            'title' => "Edit Antibody ".$antibody,
            'cycle' => $cycle
        );
    }

    #[Route(path: '/antibody/show/{id}', name: 'translationalresearch_antibody_show', methods: ['GET'], options: ['expose' => true])]
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
            'title' => "Show Antibody ".$antibody,
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

    public function removeEmptyVisualInfo( $antibody ) {
        $em = $this->getDoctrine()->getManager();
        $removeArr = array();
        $visualInfos = $antibody->getVisualInfos();
        //echo "visualInfos=".count($visualInfos)."<br>";
        foreach($visualInfos as $visualInfo) {
            if( $visualInfo->isEmpty() ) {
                //echo "visualInfo is empty<br>";
                $removeArr[] = "<strong>"."Removed empty: ".$visualInfo." ".$this->getEntityId($visualInfo)."</strong>";
                $antibody->removeVisualInfo($visualInfo);
                $visualInfo->setAntibody(NULL);
                $em->persist($visualInfo);
                $em->remove($visualInfo);
//                $this->addFlash(
//                    'notice',
//                    "Removed empty Visual Info"
//                );
            } else {
                //echo "visualInfo is not empty<br>";
//                $this->addFlash(
//                    'notice',
//                    "visualInfo is not empty"
//                );
                $em->getRepository(Document::class)->processDocuments( $visualInfo, "document" );
            }
        }

        //$visualInfos = $antibody->getVisualInfos();
        //echo "visualInfos=".count($visualInfos)."<br>";
        //exit('111');

        return implode("<br>", $removeArr);;
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
    public function getEntityId($entity) {
        if( $entity->getId() ) {
            return "ID=".$entity->getId();
        }
        return "New";
    }

    #[Route(path: '/change-antibody-type/{type}/{entityId}', name: 'translationalresearch_change_antibody_type', methods: ['GET'])]
    public function changeAntibodyTypeAction( Request $request, $type, $entityId ) {
        
        if (
            false === $this->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->getUser();

        $entity = $em->getRepository(AntibodyList::class)->find($entityId);

        //echo "entity=".$entity."<br>";

        if( $type ) {
            $entity->setType($type);
            //$em->flush($entity);
            $em->flush();

            $event = "Type of the antibody with ID ".$entity->getId()." has been changed to '" . $type . "'";

            $this->addFlash(
                'notice',
                $event
            );

            $userSecUtil->createUserEditEvent($this->getParameter('employees.sitename'),$event,$user,$entity,$request,'List Updated');
        }

        //exit();
        return $this->redirect( $this->generateUrl('translationalresearch_antibodies') );
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

    #[Route(path: '/public/misi-antibody-panels', name: 'translationalresearch_misi_antibody_panels', methods: ['GET'])]
    public function indexPublicMisiAntibodyPanelsAction(Request $request)
    {
        $projectDir = $this->container->get('kernel')->getProjectDir();

        //orderflex\src\App\TranslationalResearchBundle\Util\MISI_Antibody_Panels.pdf
        $originalname = 'MISI_Antibody_Panels.pdf';
        $folderPath = $projectDir.
            DIRECTORY_SEPARATOR."src".
            DIRECTORY_SEPARATOR."App".
            DIRECTORY_SEPARATOR."TranslationalResearchBundle".
            DIRECTORY_SEPARATOR."Util"
        ;
        $abspath = $folderPath . DIRECTORY_SEPARATOR . $originalname;

        $size = filesize($abspath);

        if( $abspath || $originalname || $size ) {
            //echo "abspath=".$abspath."<br>";
            //echo "originalname=".$originalname."<br>";
            //echo "$abspath: size=".$size."<br>";
            $viewType = NULL;
            $downloader = new LargeFileDownloader();
            $downloader->downloadLargeFile($abspath, $originalname, $size, true, "view", $viewType);
        } else {
            exit ("File $originalname is not available");
        }
        exit;
    }

    //Classical approach using html table
    #[Route(path: '/public/published-antibodies', name: 'translationalresearch_antibodies_public', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Antibody/antibodies_public_table.html.twig')]
    public function indexPublicAntibodiesAction(Request $request)
    {
        $filterType = trim((string)$request->get('public'));

        $filterPublic = null;
        $all = $request->query->all();
        if( isset($all['filter']) && isset($all['filter']['public']) ) {
            $filterPublic = $all['filter']['public'];
        }
        //dump($filterPublic);
        //exit();

//        if( $filterPublic === null || strtolower($filterPublic) != 'public' ) {
//            return $this->redirectToRoute(
//                'translationalresearch_antibodies_public',
//                array(
//                    'filter[public]' => 'Public',
//                    'filter[type][0]' => 'default',
//                    'filter[type][1]' => 'user-added',
//                )
//            );
//        }

        //$request->request->set('public', 'Public');
        //$all['filter']['public'] = 'Public';
        //$request->query->replace($all);
        //dump($request);
        //exit();

        $limit = 50;
        $onlyPublic = true;
        $listArr = $this->getList($request,$onlyPublic,$limit);
        //$listArr['title'] = "Antibodies";
        $listArr['postPath'] = "_translationalresearch";
        //$listArr['title'] = "Public ".$listArr['title'];

        $filterFormObject = $listArr['filterFormObject'];

        //'antibodypanels'
        $antibodyLabs = $filterFormObject['antibodylabs']->getData();

        $antibodyLabsStr = "";
        if( $antibodyLabs ) {
            //echo "antibodyLabs count=".count($antibodyLabs)."<br>";
            foreach ($antibodyLabs as $antibodyLab) {
                $antibodyLabsStr = $antibodyLabsStr . $antibodyLab->getName();
            }
            if( $antibodyLabsStr ) {
                $antibodyLabsStr = $antibodyLabsStr . " ";
            }
        }

        $listArr['title'] = "Published ".$antibodyLabsStr.$listArr['title'];

        return $listArr;
    }

    #[Route(path: '/public/published-antibodies-card', name: 'translationalresearch_antibodies_public_react', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Antibody/antibodies_public_react.html.twig')]
    public function indexPublicAntibodiesReactAction(Request $request)
    {
        if(0) {
            $transresUtil = $this->container->get('transres_util');
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(AntibodyList::class);
            $dql = $repository->createQueryBuilder("ent");
            $dql->select('ent');
            //$dql->orderBy("antibody.orderinlist","DESC");
            $dql->where("ent.type = :typedef OR ent.type = :typeadd");
            $dql->andWhere("ent.openToPublic = TRUE");

            //$matchingAntibodyIdsArr = $transresUtil->getAntibodyIdsArrByDqlParameters($dql,array());
            $allGlobalAntibodys = $transresUtil->getTotalAntibodyCount();
            //echo "matching=".count($matchingAntibodyIdsArr).", allGlobalAntibodys=$allGlobalAntibodys"."<br>";
            $title = "Public Antibodies";
            $title = $title . " (Total " . $allGlobalAntibodys . ")";

            return array(
                'cycle' => 'show',
                'title' => $title //"Public Antibodies";
            );
        }

        $limit = 50;
        $onlyPublic = true;
        //$onlyPublic = false;
        $listArr = $this->getList($request,$onlyPublic,$limit); //list react main page
        //$listArr['title'] = "Antibodies";
        //$listArr['postPath'] = "_translationalresearch";
        //echo "entities=".count($listArr['entities'])."<br>";

        $filterFormObject = $listArr['filterFormObject'];

        //'antibodypanels'
        $antibodyLabs = $filterFormObject['antibodylabs']->getData();

        $antibodyLabsStr = "";
        if( $antibodyLabs ) {
            //echo "antibodyLabs count=".count($antibodyLabs)."<br>";
            foreach ($antibodyLabs as $antibodyLab) {
                $antibodyLabsStr = $antibodyLabsStr . $antibodyLab->getName();
            }
            if( $antibodyLabsStr ) {
                $antibodyLabsStr = $antibodyLabsStr . " ";
            }
        }

        $listArr['title'] = "Published ".$antibodyLabsStr.$listArr['title'];

        $control = $filterFormObject['control']->getData();
        $tags = array(
            //array("control","All"),
            array("control","Breast cancer"),
            array("control","Colon cancer"),
            array("control","Lung cancer"),
            array("control","Bone marrow"),
        );
        $listArr['tags'] = $tags;
        $listArr['selectedTag'] = $control;

        return $listArr;
    }

    //schemes: ['http','https'],
    #[Route(path: '/public/antibodies/api', name: 'translationalresearch_antibodies_api', options: ['expose' => true])]
    public function getAntibodiesApiAction( Request $request ) {
        //For the sorting function, I would recommend to use built in pre-sorting
        // so we put the antibodies with pictures, and control slides available for purchase,
        // pathologist referred, etc in the front.

        //pre-set list type
        //$typeArr = array('type'=>array('default','user-added'));
        //$request->query->set('filter', $typeArr);

        //get original filter
        $filter = $request->get('filter');

        //add types ('default','user-added') to the existing filter
        $filter['type'] = array('default','user-added');

        $request->query->set('filter',$filter);

        //dump($request);
        //exit('111');

        $limit = 20; //20
        $onlyPublic = true;

        //dump($request);
        //exit('111');

        //Correct  : http://127.0.0.1/translational-research/public/antibodies/api?page=1&antibodylabs[]=2
        //Currently: http://127.0.0.1/translational-research/public/antibodies/api?page=1&filter[antibodylabs][]=2

        $listArr = $this->getList($request,$onlyPublic,$limit); //api

        $antibodies = $listArr['entities'];
        $totalAntibodiesCount = $listArr['totalAntibodiesCount'];
        //echo "react antibodies=".count($antibodies)."<br>";
        //exit('111');
        //echo "totalAntibodiesCount=".$totalAntibodiesCount."<br>";
        //$limit = $listArr['limit'];
        //echo "limit=".$limit."<br>";

        //Public Antibody List fields:
        //ID
        //Name
        //Description
        //Category Tags
        //Public
        //Company
        //Clone
        //Host
        //Reactivity
        //Storage
        //Associated Antibodies
        //$count = 0;
        $jsonArray = array();
        foreach($antibodies as $antibody) {
            //$count++;
            $jsonArray[] = $antibody->toJson(); //$count
        }

        $totalCount = 0;
        $totalPages = 0;
        if( count($antibodies) > 0 ) {
            //$totalCount = $antibodies->getTotalItemCount();
            $totalCount = $totalAntibodiesCount;
            //echo "totalCount=$totalCount <br>";
            $totalPages = ceil($totalCount / $limit);
        }

        $results = array(
            'results' => $jsonArray,
            'totalPages'   => $totalPages,
            'totalProducts' => $totalCount
        );

        //return new JsonResponse($results);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(200);
        //$response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setContent(json_encode($results));

        return $response;
    }

    #[Route(path: '/public-antibody/{id}', name: 'translationalresearch_antibody_show_react', methods: ['GET'], options: ['expose' => true])]
    #[Template('AppTranslationalResearchBundle/Antibody/new_react.html.twig')]
    public function showReactAction(Request $request, AntibodyList $antibody)
    {
        //$transresUtil = $this->container->get('transres_util');
        //$em = $this->getDoctrine()->getManager();

        $cycle = "show";

        $form = $this->createAntibodyForm($antibody, $cycle); //show

        $jsonArray = array();
        $jsonArray[] = $antibody->toJson();

        return array(
            'antibody' => $antibody,
            'jsonArray' => $jsonArray,
            'form' => $form->createView(),
            'title' => "Show Antibody ".$antibody,
            'cycle' => $cycle
        );
    }

    #[Route(path: '/public/antibody/api/{id}', name: 'translationalresearch_antibody_public_api', methods: ['GET'], options: ['expose' => true])]
    public function showPublicAction(Request $request, AntibodyList $antibody)
    {
        //$transresUtil = $this->container->get('transres_util');
        //$em = $this->getDoctrine()->getManager();

        $jsonArray = array();
        $jsonArray[] = $antibody->toJson();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(200);
        //$response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setContent(json_encode($jsonArray));

        return $response;
    }

    #[Route(path: '/public/download-antibody-spreadsheet-post', methods: ['POST'], name: 'translationalresearch_public_download_antibody_spreadsheet')]
    public function downloadPublicApplicantListExcelPostAction(Request $request) {

        //$ids = $request->query->get('projectids');
        $ids = $request->request->get('ids');
        //exit("ids=".$ids);

        $limit = null;
        //exit("ids=".$ids);
        //exit("limit=".$limit);

        if( $ids ) {
            if( is_array($ids) && count($ids) == 0 ) {
                exit("No Antibodies to Export to spreadsheet");
            }
        }

        if( !$ids ) {
            exit("No Antibodies to Export to spreadsheet");
        }

        $transresUtil = $this->container->get('transres_util');

        //[YEAR] [WCMC (top level of actual institution)] [FELLOWSHIP-TYPE] Fellowship Candidate Data generated on [DATE] at [TIME] EST.xls
        //$fileName = "Projects ".date('m/d/Y H:i').".xlsx";
        $fileName = "Antibodies-".date('m-d-Y').".xlsx";

        $antibodyIdsArr = explode(',', $ids);

        //Spout uses less memory
        $transresUtil->createAntibodyExcelSpout($antibodyIdsArr,$fileName,$limit,$onlyPublic=true);
        //header('Content-Disposition: attachment;filename="'.$fileName.'"');
        exit();
    }

    #[Route(path: '/public/download-antibody-pdf-post', methods: ['POST'], name: 'translationalresearch_public_download_antibody_pdf')]
    public function downloadPublicApplicantListPdfPostAction(Request $request) {

        //$ids = $request->query->get('projectids');
        $ids = $request->request->get('ids');
        //exit("ids=".$ids);

        $limit = null;
        //exit("ids=".$ids);
        //exit("limit=".$limit);

        if( $ids ) {
            if( is_array($ids) && count($ids) == 0 ) {
                exit("No Antibodies to Export to PDF");
            }
        }

        if( !$ids ) {
            exit("No Antibodies to Export to PDF");
        }

        $transresUtil = $this->container->get('transres_util');

        //[YEAR] [WCMC (top level of actual institution)] [FELLOWSHIP-TYPE] Fellowship Candidate Data generated on [DATE] at [TIME] EST.xls
        //$fileName = "Projects ".date('m/d/Y H:i').".xlsx";
        $fileName = "Antibodies-".date('m-d-Y').".pdf";

        $antibodyIdsArr = explode(',', $ids);

        //Spout uses less memory
        $transresUtil->createAntibodyPdf($antibodyIdsArr,$fileName,$limit,$onlyPublic=true);
        //header('Content-Disposition: attachment;filename="'.$fileName.'"');
        exit();
    }

    #[Route(path: '/public/antibodies/group-by-panel/{labs}', methods: ['GET'], name: 'translationalresearch_antibodies_group_by_panel')]
    #[Template('AppTranslationalResearchBundle/Antibody/antibodies_group_by_panel.html.twig')]
    public function groupByPanelAction(Request $request, $labs=NULL) {
        $transresUtil = $this->container->get('transres_util');

        //get all panels
        $res = $transresUtil->getTransResAntibodyPanels($labs);

        $panels = $res['panels'];
        //$labsStr = $res['labsStr'];
        $labsArr = $res['labsArr'];
        $labsStr = implode(', ',$labsArr);

        return array(
            'panels' => $panels,
            'title' => $labsStr . " Antibodies - Grouped by Panel",
            //'cycle' => $cycle
        );
    }

    #[Route(path: '/public/antibody/panel/{panelId}', methods: ['GET'], name: 'translationalresearch_antibody_panel')]
    #[Template('AppTranslationalResearchBundle/Antibody/panel.html.twig')]
    public function showPanelAction(Request $request, $panelId) {

        $transresUtil = $this->container->get('transres_util');


        $em = $this->getDoctrine()->getManager();
        $panel = $em->getRepository(AntibodyPanelList::class)->find($panelId);

        $panelName = $panel->getName();

        //get all panels
        $antibodies = $transresUtil->getAntibodiesByPanel($panel);


        return array(
            'antibodies' => $antibodies,
            'panel' => $panel,
            'title' => "Panel ".$panelName,
            //'cycle' => $cycle
        );
    }
}
