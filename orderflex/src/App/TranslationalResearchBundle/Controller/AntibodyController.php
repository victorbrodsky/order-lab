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
use App\UserdirectoryBundle\Entity\Document;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AntibodyController extends OrderAbstractController
{
    //Custom Antibody list
    #[Route(path: '/antibodies/', name: 'translationalresearch_antibodies', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Antibody/antibodies_v2.html.twig')]
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
        //$listArr['title'] = "Antibodies";
        $listArr['postPath'] = "_translationalresearch";

        return $listArr;
    }
    public function getList($request, $publicPage=false, $limit=50) {

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
        $className = $mapper['className'];

        //synonyms and original
        $dql->leftJoin("ent.synonyms", "synonyms");
        $dql->addGroupBy('synonyms.name');
        $dql->leftJoin("ent.original", "original");
        $dql->addGroupBy('original.name');
        $dql->leftJoin("ent.categoryTags", "categoryTags");
        $dql->addGroupBy('categoryTags.name');
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

        //$publicPage = true;
        //echo "1 publicPage=".$publicPage."<br>";
        $params = array(
            "className" => $mapper['className'],
            "publicPage" => $publicPage
        );
        $filterform = $this->createForm(AntibodyFilterType::class, null, array(
            //'action' => $this->generateUrl($routeName),
            'form_custom_value'=>$params,
            'method' => 'GET',
        ));


        $name = null;
        $description = null;
        $categorytags = null;
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

        if( $publicPage === false ) {
            $name = $filterform['name']->getData();
            $description = $filterform['description']->getData();
            $categorytags = $filterform['categorytags']->getData();
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
        }

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

        $filterTypes = null;
        if( isset($filterform['type']) ) {
            $filterTypes = $filterform['type']->getData();
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

        //echo "dql=".$dql."<br>";

        $em = $this->getDoctrine()->getManager();
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

        $cycle = "edit";

        $antibody = $this->createEditAntibody($antibody);

        $originalVisualInfos = array();
        foreach( $antibody->getVisualInfos() as $visualInfo ) {
            $originalVisualInfos[] = $visualInfo;
        }

        $form = $this->createAntibodyForm($antibody,$cycle); //new

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //exit('antibody edit');

            $em->getRepository(Document::class)->processDocuments($antibody, "document");

            $removedInfo1 = $this->removeEmptyVisualInfo($antibody);
            $removedInfo2 = $this->removeVisualInfoCollection($originalVisualInfos,$antibody->getVisualInfos(),$antibody);

            $em->flush();

            $msg = "Updated antibody ".$antibody; //."; ".$removedInfo1."; ".$removedInfo2;

            $this->addFlash(
                'notice',
                $msg
            );

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


    //Classical approach using html
    #[Route(path: '/antibodies/public/orig/', name: 'translationalresearch_antibodies_public', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Antibody/antibodies_public_orig.html.twig')]
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

        if( $filterPublic === null || strtolower($filterPublic) != 'public' ) {
            return $this->redirectToRoute(
                'translationalresearch_antibodies_public',
                array(
                    'filter[public]' => 'Public',
                    'filter[type][0]' => 'default',
                    'filter[type][1]' => 'user-added',
                )
            );
        }

        //$request->request->set('public', 'Public');
        //$all['filter']['public'] = 'Public';
        //$request->query->replace($all);
        //dump($request);
        //exit();

        $listArr = $this->getList($request);
        //$listArr['title'] = "Antibodies";
        $listArr['postPath'] = "_translationalresearch";
        $listArr['title'] = "Public ".$listArr['title'];

        return $listArr;
    }

    #[Route(path: '/antibodies/public/', name: 'translationalresearch_antibodies_public_react', methods: ['GET'])]
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

        //exit('indexPublicAntibodiesReactAction');
//        $filterType = trim((string)$request->get('public'));
//
//        $filterPublic = null;
//        $all = $request->query->all();
//        if( isset($all['filter']) && isset($all['filter']['public']) ) {
//            $filterPublic = $all['filter']['public'];
//        }
//        //dump($filterPublic);
//        //exit();
//
//        if( $filterPublic === null || strtolower($filterPublic) != 'public' ) {
//            return $this->redirectToRoute(
//                'translationalresearch_antibodies_public_react',
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

        $limit = 20;
        $publicPage = true;
        $listArr = $this->getList($request,$publicPage,$limit);
        //$listArr['title'] = "Antibodies";
        //$listArr['postPath'] = "_translationalresearch";
        $listArr['title'] = "Public ".$listArr['title'];

        $tags = array(
            array("control","Breast cancer"),
            array("control","Colon cancer"),
            array("control","Lung cancer"),
            array("control","Bone marrow"),
        );
        $listArr['tags'] = $tags;

        return $listArr;
    }

    //Tags"
    //http://127.0.0.1/index_dev.php/translational-research/antibodies/?filter%5Bsearch%5D=&filter%5Bname%5D=&filter%5Bdescription%5D=&filter%5Bpublic%5D=&filter%5Btype%5D%5B%5D=default&filter%5Btype%5D%5B%5D=user-added&filter%5Bclone%5D=&filter%5Bhost%5D=&filter%5Breactivity%5D=&filter%5Bcompany%5D=&filter%5Bcatalog%5D=&filter%5Bcontrol%5D=breast+cancer&filter%5Bprotocol%5D=&filter%5Bretrieval%5D=&filter%5Bdilution%5D=&filter%5Bcomment%5D=&filter%5Bdocument%5D=&filter%5Bvisual%5D=

    #[Route(path: '/antibodies/public/tag/{type}/{tag}', name: 'translationalresearch_antibody_filter_tag', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Antibody/antibodies_public_react.html.twig')]
    public function indexPublicAntibodiesReactFilterTagAction(Request $request)
    {
        $all = $request->query->all();
        dump($all);
        if( isset($all['filter']) && isset($all['filter']['public']) ) {
            $filterPublic = $all['filter']['public'];
        }
        dump($filterPublic);
        exit();
    }

    #[Route(path: '/antibodies/api', name: 'translationalresearch_antibodies_api', options: ['expose' => true])]
    public function getAntibodiesApiAction( Request $request ) {

        if(0) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(AntibodyList::class);
            $dql = $repository->createQueryBuilder("antibody");
            $dql->select('antibody');
            //$dql->orderBy("antibody.orderinlist","DESC");
            $dql->where("antibody.type = :typedef OR antibody.type = :typeadd");
            $dql->andWhere("antibody.openToPublic = TRUE");

            $dqlParameters = array();
            $dqlParameters["typedef"] = 'default';
            $dqlParameters["typeadd"] = 'user-added';

            $query = $dql->getQuery(); //$query = $em->createQuery($dql);
            //$query->setMaxResults(60);

            if (count($dqlParameters) > 0) {
                $query->setParameters($dqlParameters);
            }

            $limit = 20;

            $paginationParams = array(
                'defaultSortFieldName' => 'antibody.orderinlist',
                'defaultSortDirection' => 'ASC',
                'wrap-queries' => true
            );

            $page = $request->query->get('page', 1);

            $paginator = $this->container->get('knp_paginator');
            $antibodies = $paginator->paginate(
                $query,
                $page,   /*page number*/
                $limit,                            /*limit per page*/
                $paginationParams
            );
        }

        $limit = 20;
        $publicPage = true;
        $listArr = $this->getList($request,$publicPage,$limit);
        //$listArr = $this->getList($request);
        $antibodies = $listArr['entities'];
        $totalAntibodiesCount = $listArr['totalAntibodiesCount'];
        //echo "antibodies=".count($antibodies)."<br>";
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
        $count = 0;
        $jsonArray = array();
        foreach($antibodies as $antibody) {
            $count++;
            //$documentImageUrl = null;
            //$documentUrls = array();
            $imageData = array();
            foreach( $antibody->getDocuments() as $document ) {
                //$documentUrlsHtml = $document->getAbsoluteUploadFullPath();
                //$documentUrlsHtml = '<img src="'.$documentUrlsHtml.'" className="card-img-top" alt="Hollywood Sign on The Hill" />';
                //$documentUrls[] = $document->getAbsoluteUploadFullPath();
                //$documentImageUrl = $document->getAbsoluteUploadFullPath();
                $imageData[] = array(
                    'key' => 'document-'.$document->getId(),
                    'label' => $antibody->getName(),
                    'url' => $document->getAbsoluteUploadFullPath()
                );
            }

            foreach( $antibody->getVisualInfos() as $visualInfo ) {
                $visualInfoROI = false;
                $visualInfoWSI = false;
                $uploadedType = $visualInfo->getUploadedType();

//                if( $uploadedType == 'Region Of Interest' ) {
//                    $visualInfoROI = true;
//                }
//                if( $uploadedType == 'Whole Slide Image' ) {
//                    $visualInfoWSI = true;
//                }

                //$comment
                //$catalog
                //documents

                if( $uploadedType ) {
                    $uploadedType = $uploadedType . ": ";
                }

                foreach( $visualInfo->getDocuments() as $visualInfoDocument ) {
                    $path = $visualInfoDocument->getAbsoluteUploadFullPath();
                    if( $path ) {
                        $imageData[] = array(
                            'key' => 'visualinfo-'.$visualInfoDocument->getId(),
                            'label' => $uploadedType.$visualInfo->getComment(),
                            'url' => $path,
                            'comment' => $visualInfo->getComment(),
                            'catalog' => $visualInfo->getCatalog()
                        );
                    }
                }
            }

            $disableDatasheet = false;
            $datasheet = $antibody->getDatasheet();
            if( !$datasheet || $datasheet == '' ) {
                $disableDatasheet = true;
            }
            //$disableDatasheet = true;

            if(0) {
                $jsonArray[] = array(
                    'id' => $antibody->getId(),
                    'name' => ($antibody->getName()) ? $antibody->getName() : '', //$antibody->getName(),
                    'description' => ($antibody->getDescription()) ? $antibody->getDescription() : '',
                    'categorytags' => ($antibody->getCategoryTagsStr()) ? $antibody->getCategoryTagsStr() : '', //$antibody->getCategoryTagsStr(),
                    'public' => ($antibody->getOpenToPublic()) ? $antibody->getOpenToPublic() : '', //$antibody->getOpenToPublic(),
                    'company' => ($antibody->getCompany()) ? $antibody->getCompany() : '', //$antibody->getCompany(),
                    'clone' => ($antibody->getClone()) ? $antibody->getClone() : '', //$antibody->getClone(),
                    'host' => ($antibody->getHost()) ? $antibody->getHost() : '', //$antibody->getHost(),
                    'reactivity' => ($antibody->getReactivity()) ? $antibody->getReactivity() : '', //$antibody->getReactivity(),
                    'storage' => ($antibody->getStorage()) ? $antibody->getStorage() : '', //$antibody->getStorage(),
                    //'documents' => $documentUrls //$antibody->getDocuments()
                    'documents' => $imageData //$antibody->getDocuments()
                    //'unitPrice'     => $antibody->getUnitPrice(),
                    //'Catalog'       => $antibody->getCatalog(),
                );
            } else {
                $jsonArray[] = array(
                    'id' => ($antibody->getId()) ? $antibody->getId() : $count."-key",
                    'name' => ($antibody->getName()) ? $antibody->getName() : '', //$antibody->getName(),
                    'publictext' => $antibody->getPublicText(),
                    'documents' => $imageData, //$documentUrls, //$antibody->getDocuments()
                    'datasheet' => $datasheet,
                    'disableDatasheet' => $disableDatasheet
                    //'image' => $documentImageUrl //$antibody->getDocuments()
                );
            }
        }

        $totalCount = 0;
        $totalPages = 0;
        if( count($antibodies) > 0 ) {
            $totalCount = $antibodies->getTotalItemCount();
            //echo "totalCount=$totalCount <br>";
            $totalPages = ceil($totalCount / $limit);
        }

        //$paginationData = $antibodies->getPaginationData();
        //$indexTitle = " (".$paginationData['firstItemNumber']."-". $paginationData['lastItemNumber'];
        //exit($indexTitle);

//        $info = array(
//            'seed' => "abc",
//            'results' => $limit,
//            'page' => $page,
//            'version' => 1
//        );

        $results = array(
            'results' => $jsonArray,
            //'products' => $jsonArray,
            //'info'    => $info,
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

}
