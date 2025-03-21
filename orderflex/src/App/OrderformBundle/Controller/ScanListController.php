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

namespace App\OrderformBundle\Controller;



use App\OrderformBundle\Entity\StainList; //process.py script: replaced namespace by ::class: added use line for classname=StainList
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use App\OrderformBundle\Helper\ErrorHelper;

use App\UserdirectoryBundle\Controller\ListController;

/**
 * Common list controller
 */
#[Route(path: '/admin/list')]
class ScanListController extends ListController
{

    #[Route(path: '/stains-spreadsheet/', name: 'stain-list-excel', methods: ['GET'])]
    public function downloadStainExcelAction(Request $request)
    {

        if( false === $this->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        $listArr = $this->getList($request,1000000);


        //$listExcelHtml = $this->container->get('templating')->render('AppOrderformBundle/ListForm/list-excel.html.twig',
        //    $listArr
        //);
        $listExcelHtml = $this->container->get('twig')->render('AppOrderformBundle/ListForm/list-excel.html.twig',
            $listArr
        );

        //generate file name
        $fileName = $listArr['displayName'].".xls";
        $fileName = preg_replace('!\s+!', '-', $fileName);

        //echo "count=".count($listArr['entities'])."<br>";
        //exit('1');

        return new Response(
            $listExcelHtml,
            200,
            array(
                'Content-Type'          => 'application/vnd.ms-excel',
                //'Content-Type'          => 'application/msexcel',
                'Content-Disposition'   => 'attachment; filename="'.$fileName.'"'
            )
        );
    }

    #[Route(path: '/stains-update-full-title/', name: 'stain_update_fulltitle', methods: ['GET'])]
    public function updateFullTitleListAction(Request $request)
    {

        if( false === $this->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";
        //exit();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:StainList'] by [StainList::class]
        $entities = $em->getRepository(StainList::class)->findAll();

        //echo "count=".count($entities)."<br>";
        //exit();

        $batchSize = 20;
        $count = 0;

        foreach( $entities as $entity ) {
            $entity->createFullTitle();

            $em->flush();

//            $em->persist($entity);
//            if( ($count % $batchSize) === 0 ) {
//                $em->flush();
//                $em->clear(); // Detaches all objects from Doctrine!
//            }

            $count++;
        }

        //$em->flush(); // Persist objects that did not make up an entire batch
        $em->clear();

        $this->addFlash(
            'notice',
            "Stain's Full Title updated: " . $count
        );

        return $this->redirect($this->generateUrl($pathbase.'-list'));
    }


//* @Route("/principal-investigators/", name="principalinvestigators-list")
    //* @Route("/course-directors/", name="coursedirectors-list")
    //     * @Route("/system-account-request-types/", name="systemaccountrequesttypes-list")
    //@Route("/calllog-entry-tags/", name="calllogentrytags-list", methods={"GET"})
    /**
     * Lists all entities.
     *
     *
     *
     */
    #[Route(path: '/research-project-titles/', name: 'researchprojecttitles-list', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/research-project-group-types/', name: 'researchprojectgrouptype-list', methods: ['GET'])]
    #[Route(path: '/educational-course-titles/', name: 'educationalcoursetitles-list', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/educational-course-group-types/', name: 'educationalcoursegrouptypes-list', methods: ['GET'])]
    #[Route(path: '/mrn-types/', name: 'mrntype-list', methods: ['GET'])]
    #[Route(path: '/accession-types/', name: 'accessiontype-list', methods: ['GET'])]
    #[Route(path: '/encounter-number-types/', name: 'encountertype-list', methods: ['GET'])]
    #[Route(path: '/procedure-number-types/', name: 'proceduretype-list', methods: ['GET'])]
    #[Route(path: '/stains/', name: 'stain-list', methods: ['GET'])]
    #[Route(path: '/organs/', name: 'organ-list', methods: ['GET'])]
    #[Route(path: '/encounter-types/', name: 'encounter-list', methods: ['GET'])]
    #[Route(path: '/procedure-types/', name: 'procedure-list', methods: ['GET'])]
    #[Route(path: '/pathology-services/', name: 'pathservice-list', methods: ['GET'])]
    #[Route(path: '/slide-types/', name: 'slidetype-list', methods: ['GET'])]
    #[Route(path: '/message-categories/', name: 'messagecategorys-list', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/statuses/', name: 'status-list', methods: ['GET'])]
    #[Route(path: '/scan-order-delivery-options/', name: 'orderdelivery-list', methods: ['GET'])]
    #[Route(path: '/region-to-scan-options/', name: 'regiontoscan-list', methods: ['GET'])]
    #[Route(path: '/scan-order-processor-comments/', name: 'processorcomment-list', methods: ['GET'])]
    #[Route(path: '/account-numbers/', name: 'accounts-list', methods: ['GET'])]
    #[Route(path: '/urgency-types/', name: 'urgency-list', methods: ['GET'])]
    #[Route(path: '/progress-and-comments-event-types/', name: 'progresscommentseventtypes-list', methods: ['GET'])]
    #[Route(path: '/event-log-event-types/', name: 'scanloggereventtypes-list', methods: ['GET'])]
    #[Route(path: '/races/', name: 'races-list', methods: ['GET'])]
    #[Route(path: '/report-types/', name: 'reporttype-list', methods: ['GET'])]
    #[Route(path: '/instructions-for-embedder/', name: 'instruction-list', methods: ['GET'])]
    #[Route(path: '/patient-types/', name: 'patienttype-list', methods: ['GET'])]
    #[Route(path: '/magnifications/', name: 'magnifications-list', methods: ['GET'])]
    #[Route(path: '/image-analysis-algorithms/', name: 'imageanalysisalgorithm-list', methods: ['GET'])]
    #[Route(path: '/disease-types/', name: 'diseasetypes-list', methods: ['GET'])]
    #[Route(path: '/disease-origins/', name: 'diseaseorigins-list', methods: ['GET'])]
    #[Route(path: '/laboratory-test-id-types/', name: 'labtesttype-list', methods: ['GET'])]
    #[Route(path: '/part-titles/', name: 'parttitle-list', methods: ['GET'])]
    #[Route(path: '/message-type-classifiers/', name: 'messagetypeclassifiers-list', methods: ['GET'])]
    #[Route(path: '/amendment-reasons/', name: 'amendmentreasons-list', methods: ['GET'])]
    #[Route(path: '/pathology-call-complex-patients/', name: 'pathologycallcomplexpatients-list', methods: ['GET'])]
    #[Route(path: '/patient-list-hierarchy/', name: 'patientlisthierarchys-list', methods: ['GET'])]
    #[Route(path: '/patient-list-hierarchy-group-types/', name: 'patientlisthierarchygrouptype-list', methods: ['GET'])]
    #[Route(path: '/encounter-statuses/', name: 'encounterstatuses-list', methods: ['GET'])]
    #[Route(path: '/patient-record-statuses/', name: 'patientrecordstatuses-list', methods: ['GET'])]
    #[Route(path: '/message-statuses/', name: 'messagestatuses-list', methods: ['GET'])]
    #[Route(path: '/encounter-info-types/', name: 'encounterinfotypes-list', methods: ['GET'])]
    #[Route(path: '/suggested-message-categories/', name: 'suggestedmessagecategorys-list', methods: ['GET'])]
    #[Route(path: '/calllog-attachment-types/', name: 'calllogattachmenttypes-list', methods: ['GET'])]
    #[Route(path: '/calllog-task-types/', name: 'calllogtasktypes-list', methods: ['GET'])]
    #[Route(path: '/message-tag-types/', name: 'messagetagtypes-list', methods: ['GET'])]
    #[Route(path: '/message-tags/', name: 'messagetags-list', methods: ['GET'])]
    #[Route(path: '/accession-list-hierarchys/', name: 'accessionlisthierarchys-list', methods: ['GET'])]
    #[Route(path: '/accession-list-hierarchy-group-type/', name: 'accessionlisthierarchygrouptype-list', methods: ['GET'])]
    #[Route(path: '/accession-list-types/', name: 'accessionlisttype-list', methods: ['GET'])]
    #[Template('AppOrderformBundle/ListForm/index.html.twig')]
    public function indexAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->getList($request);
    }

    /**
     * Creates a new entity.
     *
     *
     */
    #[Route(path: '/research-project-titles/', name: 'researchprojecttitles_create', methods: ['POST'])]
    #[Route(path: '/research-project-group-types/', name: 'researchprojectgrouptype_create', methods: ['POST'])]
    #[Route(path: '/educational-course-titles/', name: 'educationalcoursetitles_create', methods: ['POST'])]
    #[Route(path: '/educational-course-group-types/', name: 'educationalcoursegrouptypes_create', methods: ['POST'])]
    #[Route(path: '/mrn-types/', name: 'mrntype_create', methods: ['POST'])]
    #[Route(path: '/accession-types/', name: 'accessiontype_create', methods: ['POST'])]
    #[Route(path: '/encounter-number-types/', name: 'encountertype_create', methods: ['POST'])]
    #[Route(path: '/procedure-number-types/', name: 'proceduretype_create', methods: ['POST'])]
    #[Route(path: '/stains/', name: 'stain_create', methods: ['POST'])]
    #[Route(path: '/organs/', name: 'organ_create', methods: ['POST'])]
    #[Route(path: '/encounter-types/', name: 'encounter_create', methods: ['POST'])]
    #[Route(path: '/procedure-types/', name: 'procedure_create', methods: ['POST'])]
    #[Route(path: '/pathology-services/', name: 'pathservice_create', methods: ['POST'])]
    #[Route(path: '/slide-types/', name: 'slidetype_create', methods: ['POST'])]
    #[Route(path: '/message-categories/', name: 'messagecategorys_create', methods: ['POST'])]
    #[Route(path: '/statuses/', name: 'status_create', methods: ['POST'])]
    #[Route(path: '/scan-order-delivery-options/', name: 'orderdelivery_create', methods: ['POST'])]
    #[Route(path: '/region-to-scan-options/', name: 'regiontoscan_create', methods: ['POST'])]
    #[Route(path: '/scan-order-processor-comments/', name: 'processorcomment_create', methods: ['POST'])]
    #[Route(path: '/account-numbers/', name: 'accounts_create', methods: ['POST'])]
    #[Route(path: '/urgency-types/', name: 'urgency_create', methods: ['POST'])]
    #[Route(path: '/progress-and-comments-event-types/', name: 'progresscommentseventtypes_create', methods: ['POST'])]
    #[Route(path: '/event-log-event-types/', name: 'scanloggereventtypes_create', methods: ['POST'])]
    #[Route(path: '/races/', name: 'races_create', methods: ['POST'])]
    #[Route(path: '/report-types/', name: 'reporttype_create', methods: ['POST'])]
    #[Route(path: '/instructions-for-embedder/', name: 'instruction_create', methods: ['POST'])]
    #[Route(path: '/patient-types/', name: 'patienttype_create', methods: ['POST'])]
    #[Route(path: '/magnifications/', name: 'magnifications_create', methods: ['POST'])]
    #[Route(path: '/image-analysis-algorithms/', name: 'imageanalysisalgorithm_create', methods: ['POST'])]
    #[Route(path: '/disease-types/', name: 'diseasetypes_create', methods: ['POST'])]
    #[Route(path: '/disease-origins/', name: 'diseaseorigins_create', methods: ['POST'])]
    #[Route(path: '/laboratory-test-id-types/', name: 'labtesttype_create', methods: ['POST'])]
    #[Route(path: '/part-titles/', name: 'parttitle_create', methods: ['POST'])]
    #[Route(path: '/message-type-classifiers/', name: 'messagetypeclassifiers_create', methods: ['POST'])]
    #[Route(path: '/amendment-reasons/', name: 'amendmentreasons_create', methods: ['POST'])]
    #[Route(path: '/pathology-call-complex-patients/', name: 'pathologycallcomplexpatients_create', methods: ['POST'])]
    #[Route(path: '/patient-list-hierarchy/', name: 'patientlisthierarchys_create', methods: ['POST'])]
    #[Route(path: '/patient-list-hierarchy-group-types/', name: 'patientlisthierarchygrouptype_create', methods: ['POST'])]
    #[Route(path: '/encounter-statuses/', name: 'encounterstatuses_create', methods: ['POST'])]
    #[Route(path: '/patient-record-statuses/', name: 'patientrecordstatuses_create', methods: ['POST'])]
    #[Route(path: '/message-statuses/', name: 'messagestatuses_create', methods: ['POST'])]
    #[Route(path: '/encounter-info-types/', name: 'encounterinfotypes_create', methods: ['POST'])]
    #[Route(path: '/suggested-message-categories/', name: 'suggestedmessagecategorys_create', methods: ['POST'])]
    #[Route(path: '/calllog-attachment-types/', name: 'calllogattachmenttypes_create', methods: ['POST'])]
    #[Route(path: '/calllog-task-types/', name: 'calllogtasktypes_create', methods: ['POST'])]
    #[Route(path: '/message-tag-types/', name: 'messagetagtypes_create', methods: ['POST'])]
    #[Route(path: '/message-tags/', name: 'messagetags_create', methods: ['POST'])]
    #[Route(path: '/accession-list-hierarchys/', name: 'accessionlisthierarchys_create', methods: ['POST'])]
    #[Route(path: '/accession-list-hierarchy-group-type/', name: 'accessionlisthierarchygrouptype_create', methods: ['POST'])]
    #[Route(path: '/accession-list-types/', name: 'accessionlisttype_create', methods: ['POST'])]
    #[Template('AppOrderformBundle/ListForm/new.html.twig')]
    public function createAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->createList($request);
    }

    /**
     * Displays a form to create a new entity.
     *
     *
     */
    #[Route(path: '/research-project-titles/new', name: 'researchprojecttitles_new', methods: ['GET'])]
    #[Route(path: '/research-project-group-types/new', name: 'researchprojectgrouptype_new', methods: ['GET'])]
    #[Route(path: '/educational-course-titles/new', name: 'educationalcoursetitles_new', methods: ['GET'])]
    #[Route(path: '/educational-course-group-types/new', name: 'educationalcoursegrouptypes_new', methods: ['GET'])]
    #[Route(path: '/mrn-types/new', name: 'mrntype_new', methods: ['GET'])]
    #[Route(path: '/accession-types/new', name: 'accessiontype_new', methods: ['GET'])]
    #[Route(path: '/encounter-number-types/new', name: 'encountertype_new', methods: ['GET'])]
    #[Route(path: '/procedure-number-types/new', name: 'proceduretype_new', methods: ['GET'])]
    #[Route(path: '/stains/new', name: 'stain_new', methods: ['GET'])]
    #[Route(path: '/organs/new', name: 'organ_new', methods: ['GET'])]
    #[Route(path: '/encounter-types/new', name: 'encounter_new', methods: ['GET'])]
    #[Route(path: '/procedure-types/new', name: 'procedure_new', methods: ['GET'])]
    #[Route(path: '/pathology-services/new', name: 'pathservice_new', methods: ['GET'])]
    #[Route(path: '/slide-types/new', name: 'slidetype_new', methods: ['GET'])]
    #[Route(path: '/message-categories/new', name: 'messagecategorys_new', methods: ['GET'])]
    #[Route(path: '/statuses/new', name: 'status_new', methods: ['GET'])]
    #[Route(path: '/scan-order-delivery-options/new', name: 'orderdelivery_new', methods: ['GET'])]
    #[Route(path: '/region-to-scan-options/new', name: 'regiontoscan_new', methods: ['GET'])]
    #[Route(path: '/scan-order-processor-comments/new', name: 'processorcomment_new', methods: ['GET'])]
    #[Route(path: '/account-numbers/new', name: 'accounts_new', methods: ['GET'])]
    #[Route(path: '/urgency-types/new', name: 'urgency_new', methods: ['GET'])]
    #[Route(path: '/progress-and-comments-event-types/new', name: 'progresscommentseventtypes_new', methods: ['GET'])]
    #[Route(path: '/event-log-event-types/new', name: 'scanloggereventtypes_new', methods: ['GET'])]
    #[Route(path: '/races/new', name: 'races_new', methods: ['GET'])]
    #[Route(path: '/report-types/new', name: 'reporttype_new', methods: ['GET'])]
    #[Route(path: '/instructions-for-embedder/new', name: 'instruction_new', methods: ['GET'])]
    #[Route(path: '/patient-types/new', name: 'patienttype_new', methods: ['GET'])]
    #[Route(path: '/magnifications/new', name: 'magnifications_new', methods: ['GET'])]
    #[Route(path: '/image-analysis-algorithms/new', name: 'imageanalysisalgorithm_new', methods: ['GET'])]
    #[Route(path: '/disease-types/new', name: 'diseasetypes_new', methods: ['GET'])]
    #[Route(path: '/disease-origins/new', name: 'diseaseorigins_new', methods: ['GET'])]
    #[Route(path: '/laboratory-test-id-types/new', name: 'labtesttype_new', methods: ['GET'])]
    #[Route(path: '/part-titles/new', name: 'parttitle_new', methods: ['GET'])]
    #[Route(path: '/message-type-classifiers/new', name: 'messagetypeclassifiers_new', methods: ['GET'])]
    #[Route(path: '/amendment-reasons/new', name: 'amendmentreasons_new', methods: ['GET'])]
    #[Route(path: '/pathology-call-complex-patients/new', name: 'pathologycallcomplexpatients_new', methods: ['GET'])]
    #[Route(path: '/patient-list-hierarchy/new', name: 'patientlisthierarchys_new', methods: ['GET'])]
    #[Route(path: '/patient-list-hierarchy-group-types/new', name: 'patientlisthierarchygrouptype_new', methods: ['GET'])]
    #[Route(path: '/encounter-statuses/new', name: 'encounterstatuses_new', methods: ['GET'])]
    #[Route(path: '/patient-record-statuses/new', name: 'patientrecordstatuses_new', methods: ['GET'])]
    #[Route(path: '/message-statuses/new', name: 'messagestatuses_new', methods: ['GET'])]
    #[Route(path: '/encounter-info-types/new', name: 'encounterinfotypes_new', methods: ['GET'])]
    #[Route(path: '/suggested-message-categories/new', name: 'suggestedmessagecategorys_new', methods: ['GET'])]
    #[Route(path: '/calllog-attachment-types/new', name: 'calllogattachmenttypes_new', methods: ['GET'])]
    #[Route(path: '/calllog-task-types/new', name: 'calllogtasktypes_new', methods: ['GET'])]
    #[Route(path: '/message-tag-types/new', name: 'messagetagtypes_new', methods: ['GET'])]
    #[Route(path: '/message-tags/new', name: 'messagetags_new', methods: ['GET'])]
    #[Route(path: '/accession-list-hierarchys/new', name: 'accessionlisthierarchys_new', methods: ['GET'])]
    #[Route(path: '/accession-list-hierarchy-group-type/new', name: 'accessionlisthierarchygrouptype_new', methods: ['GET'])]
    #[Route(path: '/accession-list-types/new', name: 'accessionlisttype_new', methods: ['GET'])]
    #[Template('AppOrderformBundle/ListForm/new.html.twig')]
    public function newAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->newList($request);
    }

    /**
     * Finds and displays a entity.
     *
     *
     */
    #[Route(path: '/research-project-titles/{id}', name: 'researchprojecttitles_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/research-project-group-types/{id}', name: 'researchprojectgrouptype_show', methods: ['GET'])]
    #[Route(path: '/educational-course-titles/{id}', name: 'educationalcoursetitles_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/educational-course-group-types/{id}', name: 'educationalcoursegrouptypes_show', methods: ['GET'])]
    #[Route(path: '/mrn-types/{id}', name: 'mrntype_show', methods: ['GET'])]
    #[Route(path: '/accession-types/{id}', name: 'accessiontype_show', methods: ['GET'])]
    #[Route(path: '/encounter-number-types/{id}', name: 'encountertype_show', methods: ['GET'])]
    #[Route(path: '/procedure-number-types/{id}', name: 'proceduretype_show', methods: ['GET'])]
    #[Route(path: '/stains/{id}', name: 'stain_show', methods: ['GET'])]
    #[Route(path: '/organs/{id}', name: 'organ_show', methods: ['GET'])]
    #[Route(path: '/encounter-types/{id}', name: 'encounter_show', methods: ['GET'])]
    #[Route(path: '/procedure-types/{id}', name: 'procedure_show', methods: ['GET'])]
    #[Route(path: '/pathology-services/{id}', name: 'pathservice_show', methods: ['GET'])]
    #[Route(path: '/slide-types/{id}', name: 'slidetype_show', methods: ['GET'])]
    #[Route(path: '/message-categories/{id}', name: 'messagecategorys_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/statuses/{id}', name: 'status_show', methods: ['GET'])]
    #[Route(path: '/scan-order-delivery-options/{id}', name: 'orderdelivery_show', methods: ['GET'])]
    #[Route(path: '/region-to-scan-options/{id}', name: 'regiontoscan_show', methods: ['GET'])]
    #[Route(path: '/scan-order-processor-comments/{id}', name: 'processorcomment_show', methods: ['GET'])]
    #[Route(path: '/account-numbers/{id}', name: 'accounts_show', methods: ['GET'])]
    #[Route(path: '/urgency-types/{id}', name: 'urgency_show', methods: ['GET'])]
    #[Route(path: '/progress-and-comments-event-types/{id}', name: 'progresscommentseventtypes_show', methods: ['GET'])]
    #[Route(path: '/event-log-event-types/{id}', name: 'scanloggereventtypes_show', methods: ['GET'])]
    #[Route(path: '/races/{id}', name: 'races_show', methods: ['GET'])]
    #[Route(path: '/report-types/{id}', name: 'reporttype_show', methods: ['GET'])]
    #[Route(path: '/instructions-for-embedder/{id}', name: 'instruction_show', methods: ['GET'])]
    #[Route(path: '/patient-types/{id}', name: 'patienttype_show', methods: ['GET'])]
    #[Route(path: '/magnifications/{id}', name: 'magnifications_show', methods: ['GET'])]
    #[Route(path: '/image-analysis-algorithms/{id}', name: 'imageanalysisalgorithm_show', methods: ['GET'])]
    #[Route(path: '/disease-types/{id}', name: 'diseasetypes_show', methods: ['GET'])]
    #[Route(path: '/disease-origins/{id}', name: 'diseaseorigins_show', methods: ['GET'])]
    #[Route(path: '/laboratory-test-id-types/{id}', name: 'labtesttype_show', methods: ['GET'])]
    #[Route(path: '/part-titles/{id}', name: 'parttitle_show', methods: ['GET'])]
    #[Route(path: '/message-type-classifiers/{id}', name: 'messagetypeclassifiers_show', methods: ['GET'])]
    #[Route(path: '/amendment-reasons/{id}', name: 'amendmentreasons_show', methods: ['GET'])]
    #[Route(path: '/pathology-call-complex-patients/{id}', name: 'pathologycallcomplexpatients_show', methods: ['GET'])]
    #[Route(path: '/patient-list-hierarchy/{id}', name: 'patientlisthierarchys_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/patient-list-hierarchy-group-types/{id}', name: 'patientlisthierarchygrouptype_show', methods: ['GET'])]
    #[Route(path: '/encounter-statuses/{id}', name: 'encounterstatuses_show', methods: ['GET'])]
    #[Route(path: '/patient-record-statuses/{id}', name: 'patientrecordstatuses_show', methods: ['GET'])]
    #[Route(path: '/message-statuses/{id}', name: 'messagestatuses_show', methods: ['GET'])]
    #[Route(path: '/encounter-info-types/{id}', name: 'encounterinfotypes_show', methods: ['GET'])]
    #[Route(path: '/suggested-message-categories/{id}', name: 'suggestedmessagecategorys_show', methods: ['GET'])]
    #[Route(path: '/calllog-attachment-types/{id}', name: 'calllogattachmenttypes_show', methods: ['GET'])]
    #[Route(path: '/calllog-task-types/{id}', name: 'calllogtasktypes_show', methods: ['GET'])]
    #[Route(path: '/message-tag-types/{id}', name: 'messagetagtypes_show', methods: ['GET'])]
    #[Route(path: '/message-tags/{id}', name: 'messagetags_show', methods: ['GET'])]
    #[Route(path: '/accession-list-hierarchys/{id}', name: 'accessionlisthierarchys_show', methods: ['GET'], options: ['expose' => true])]
    #[Route(path: '/accession-list-hierarchy-group-type/{id}', name: 'accessionlisthierarchygrouptype_show', methods: ['GET'])]
    #[Route(path: '/accession-list-types/{id}', name: 'accessionlisttype_show', methods: ['GET'])]
    #[Template('AppOrderformBundle/ListForm/show.html.twig')]
    public function showAction(Request $request,$id)
    {
        if( false === $this->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->showList($request,$id,true);
    }

    /**
     * Displays a form to edit an existing entity.
     *
     *
     */
    #[Route(path: '/research-project-titles/{id}/edit', name: 'researchprojecttitles_edit', methods: ['GET'])]
    #[Route(path: '/research-project-group-types/{id}/edit', name: 'researchprojectgrouptype_edit', methods: ['GET'])]
    #[Route(path: '/educational-course-titles/{id}/edit', name: 'educationalcoursetitles_edit', methods: ['GET'])]
    #[Route(path: '/educational-course-group-types/{id}/edit', name: 'educationalcoursegrouptypes_edit', methods: ['GET'])]
    #[Route(path: '/mrn-types/{id}/edit', name: 'mrntype_edit', methods: ['GET'])]
    #[Route(path: '/accession-types/{id}/edit', name: 'accessiontype_edit', methods: ['GET'])]
    #[Route(path: '/encounter-number-types/{id}/edit', name: 'encountertype_edit', methods: ['GET'])]
    #[Route(path: '/procedure-number-types/{id}/edit', name: 'proceduretype_edit', methods: ['GET'])]
    #[Route(path: '/stains/{id}/edit', name: 'stain_edit', methods: ['GET'])]
    #[Route(path: '/organs/{id}/edit', name: 'organ_edit', methods: ['GET'])]
    #[Route(path: '/encounter-types/{id}/edit', name: 'encounter_edit', methods: ['GET'])]
    #[Route(path: '/procedure-types/{id}/edit', name: 'procedure_edit', methods: ['GET'])]
    #[Route(path: '/pathology-services/{id}/edit', name: 'pathservice_edit', methods: ['GET'])]
    #[Route(path: '/slide-types/{id}/edit', name: 'slidetype_edit', methods: ['GET'])]
    #[Route(path: '/message-categories/{id}/edit', name: 'messagecategorys_edit', methods: ['GET'])]
    #[Route(path: '/statuses/{id}/edit', name: 'status_edit', methods: ['GET'])]
    #[Route(path: '/scan-order-delivery-options/{id}/edit', name: 'orderdelivery_edit', methods: ['GET'])]
    #[Route(path: '/region-to-scan-options/{id}/edit', name: 'regiontoscan_edit', methods: ['GET'])]
    #[Route(path: '/scan-order-processor-comments/{id}/edit', name: 'processorcomment_edit', methods: ['GET'])]
    #[Route(path: '/account-numbers/{id}/edit', name: 'accounts_edit', methods: ['GET'])]
    #[Route(path: '/urgency-types/{id}/edit', name: 'urgency_edit', methods: ['GET'])]
    #[Route(path: '/progress-and-comments-event-types/{id}/edit', name: 'progresscommentseventtypes_edit', methods: ['GET'])]
    #[Route(path: '/event-log-event-types/{id}/edit', name: 'scanloggereventtypes_edit', methods: ['GET'])]
    #[Route(path: '/races/{id}/edit', name: 'races_edit', methods: ['GET'])]
    #[Route(path: '/report-types/{id}/edit', name: 'reporttype_edit', methods: ['GET'])]
    #[Route(path: '/instructions-for-embedder/{id}/edit', name: 'instruction_edit', methods: ['GET'])]
    #[Route(path: '/patient-types/{id}/edit', name: 'patienttype_edit', methods: ['GET'])]
    #[Route(path: '/magnifications/{id}/edit', name: 'magnifications_edit', methods: ['GET'])]
    #[Route(path: '/image-analysis-algorithms/{id}/edit', name: 'imageanalysisalgorithm_edit', methods: ['GET'])]
    #[Route(path: '/disease-types/{id}/edit', name: 'diseasetypes_edit', methods: ['GET'])]
    #[Route(path: '/disease-origins/{id}/edit', name: 'diseaseorigins_edit', methods: ['GET'])]
    #[Route(path: '/laboratory-test-id-types/{id}/edit', name: 'labtesttype_edit', methods: ['GET'])]
    #[Route(path: '/part-titles/{id}/edit', name: 'parttitle_edit', methods: ['GET'])]
    #[Route(path: '/message-type-classifiers/{id}/edit', name: 'messagetypeclassifiers_edit', methods: ['GET'])]
    #[Route(path: '/amendment-reasons/{id}/edit', name: 'amendmentreasons_edit', methods: ['GET'])]
    #[Route(path: '/pathology-call-complex-patients/{id}/edit', name: 'pathologycallcomplexpatients_edit', methods: ['GET'])]
    #[Route(path: '/patient-list-hierarchy/{id}/edit', name: 'patientlisthierarchys_edit', methods: ['GET'])]
    #[Route(path: '/patient-list-hierarchy-group-types/{id}/edit', name: 'patientlisthierarchygrouptype_edit', methods: ['GET'])]
    #[Route(path: '/encounter-statuses/{id}/edit', name: 'encounterstatuses_edit', methods: ['GET'])]
    #[Route(path: '/patient-record-statuses/{id}/edit', name: 'patientrecordstatuses_edit', methods: ['GET'])]
    #[Route(path: '/message-statuses/{id}/edit', name: 'messagestatuses_edit', methods: ['GET'])]
    #[Route(path: '/encounter-info-types/{id}/edit', name: 'encounterinfotypes_edit', methods: ['GET'])]
    #[Route(path: '/suggested-message-categories/{id}/edit', name: 'suggestedmessagecategorys_edit', methods: ['GET'])]
    #[Route(path: '/calllog-attachment-types/{id}/edit', name: 'calllogattachmenttypes_edit', methods: ['GET'])]
    #[Route(path: '/calllog-task-types/{id}/edit', name: 'calllogtasktypes_edit', methods: ['GET'])]
    #[Route(path: '/message-tag-types/{id}/edit', name: 'messagetagtypes_edit', methods: ['GET'])]
    #[Route(path: '/message-tags/{id}/edit', name: 'messagetags_edit', methods: ['GET'])]
    #[Route(path: '/accession-list-hierarchys/{id}/edit', name: 'accessionlisthierarchys_edit', methods: ['GET'])]
    #[Route(path: '/accession-list-hierarchy-group-type/{id}/edit', name: 'accessionlisthierarchygrouptype_edit', methods: ['GET'])]
    #[Route(path: '/accession-list-types/{id}/edit', name: 'accessionlisttype_edit', methods: ['GET'])]
    #[Template('AppOrderformBundle/ListForm/edit.html.twig')]
    public function editAction(Request $request,$id)
    {
        if( false === $this->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->editList($request,$id);
    }

    /**
     * Edits an existing entity.
     *
     *
     */
    #[Route(path: '/research-project-titles/{id}', name: 'researchprojecttitles_update', methods: ['PUT'])]
    #[Route(path: '/research-project-group-types/{id}', name: 'researchprojectgrouptype_update', methods: ['PUT'])]
    #[Route(path: '/educational-course-titles/{id}', name: 'educationalcoursetitles_update', methods: ['PUT'])]
    #[Route(path: '/educational-course-group-types/{id}', name: 'educationalcoursegrouptypes_update', methods: ['PUT'])]
    #[Route(path: '/mrn-types/{id}', name: 'mrntype_update', methods: ['PUT'])]
    #[Route(path: '/accession-types/{id}', name: 'accessiontype_update', methods: ['PUT'])]
    #[Route(path: '/encounter-number-types/{id}', name: 'encountertype_update', methods: ['PUT'])]
    #[Route(path: '/procedure-number-types/{id}', name: 'proceduretype_update', methods: ['PUT'])]
    #[Route(path: '/stains/{id}', name: 'stain_update', methods: ['PUT'])]
    #[Route(path: '/organs/{id}', name: 'organ_update', methods: ['PUT'])]
    #[Route(path: '/encounter-types/{id}', name: 'encounter_update', methods: ['PUT'])]
    #[Route(path: '/procedure-types/{id}', name: 'procedure_update', methods: ['PUT'])]
    #[Route(path: '/pathology-services/{id}', name: 'pathservice_update', methods: ['PUT'])]
    #[Route(path: '/slide-types/{id}', name: 'slidetype_update', methods: ['PUT'])]
    #[Route(path: '/message-categories/{id}', name: 'messagecategorys_update', methods: ['PUT'])]
    #[Route(path: '/statuses/{id}', name: 'status_update', methods: ['PUT'])]
    #[Route(path: '/scan-order-delivery-options/{id}', name: 'orderdelivery_update', methods: ['PUT'])]
    #[Route(path: '/region-to-scan-options/{id}', name: 'regiontoscan_update', methods: ['PUT'])]
    #[Route(path: '/scan-order-processor-comments/{id}', name: 'processorcomment_update', methods: ['PUT'])]
    #[Route(path: '/account-numbers/{id}', name: 'accounts_update', methods: ['PUT'])]
    #[Route(path: '/urgency-types/{id}', name: 'urgency_update', methods: ['PUT'])]
    #[Route(path: '/progress-and-comments-event-types/{id}', name: 'progresscommentseventtypes_update', methods: ['PUT'])]
    #[Route(path: '/event-log-event-types/{id}', name: 'scanloggereventtypes_update', methods: ['PUT'])]
    #[Route(path: '/races/{id}', name: 'races_update', methods: ['PUT'])]
    #[Route(path: '/report-types/{id}', name: 'reporttype_update', methods: ['PUT'])]
    #[Route(path: '/instructions-for-embedder/{id}', name: 'instruction_update', methods: ['PUT'])]
    #[Route(path: '/patient-types/{id}', name: 'patienttype_update', methods: ['PUT'])]
    #[Route(path: '/magnifications/{id}', name: 'magnifications_update', methods: ['PUT'])]
    #[Route(path: '/image-analysis-algorithms/{id}', name: 'imageanalysisalgorithm_update', methods: ['PUT'])]
    #[Route(path: '/disease-types/{id}', name: 'diseasetypes_update', methods: ['PUT'])]
    #[Route(path: '/disease-origins/{id}', name: 'diseaseorigins_update', methods: ['PUT'])]
    #[Route(path: '/laboratory-test-id-types/{id}', name: 'labtesttype_update', methods: ['PUT'])]
    #[Route(path: '/part-titles/{id}', name: 'parttitle_update', methods: ['PUT'])]
    #[Route(path: '/message-type-classifiers/{id}', name: 'messagetypeclassifiers_update', methods: ['PUT'])]
    #[Route(path: '/amendment-reasons/{id}', name: 'amendmentreasons_update', methods: ['PUT'])]
    #[Route(path: '/pathology-call-complex-patients/{id}', name: 'pathologycallcomplexpatients_update', methods: ['PUT'])]
    #[Route(path: '/patient-list-hierarchy/{id}', name: 'patientlisthierarchys_update', methods: ['PUT'])]
    #[Route(path: '/patient-list-hierarchy-group-types/{id}', name: 'patientlisthierarchygrouptype_update', methods: ['PUT'])]
    #[Route(path: '/encounter-statuses/{id}', name: 'encounterstatuses_update', methods: ['PUT'])]
    #[Route(path: '/patient-record-statuses/{id}', name: 'patientrecordstatuses_update', methods: ['PUT'])]
    #[Route(path: '/message-statuses/{id}', name: 'messagestatuses_update', methods: ['PUT'])]
    #[Route(path: '/encounter-info-types/{id}', name: 'encounterinfotypes_update', methods: ['PUT'])]
    #[Route(path: '/suggested-message-categories/{id}', name: 'suggestedmessagecategorys_update', methods: ['PUT'])]
    #[Route(path: '/calllog-attachment-types/{id}', name: 'calllogattachmenttypes_update', methods: ['PUT'])]
    #[Route(path: '/calllog-task-types/{id}', name: 'calllogtasktypes_update', methods: ['PUT'])]
    #[Route(path: '/message-tag-types/{id}', name: 'messagetagtypes_update', methods: ['PUT'])]
    #[Route(path: '/message-tags/{id}', name: 'messagetags_update', methods: ['PUT'])]
    #[Route(path: '/accession-list-hierarchys/{id}', name: 'accessionlisthierarchys_update', methods: ['PUT'])]
    #[Route(path: '/accession-list-hierarchy-group-type/{id}', name: 'accessionlisthierarchygrouptype_update', methods: ['PUT'])]
    #[Route(path: '/accession-list-types/{id}', name: 'accessionlisttype_update', methods: ['PUT'])]
    #[Template('AppOrderformBundle/ListForm/edit.html.twig')]
    public function updateAction(Request $request, $id)
    {
        if( false === $this->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->updateList($request, $id);
    }


    public function classListMapper( $route, $request ) {

        $classPath = "App\\OrderformBundle\\Entity\\";
        $bundleName = "OrderformBundle";

        switch( $route ) {

        case "researchprojecttitles":
            $className = "ProjectTitleTree";
            $displayName = "Project Titles";
            break;
        case "researchprojectgrouptype":
            $className = "ResearchGroupType";
            $displayName = "Research Project Group Types";
            break;
        case "educationalcoursetitles":
            $className = "CourseTitleTree";
            $displayName = "Course Titles";
            break;
        case "educationalcoursegrouptypes":
            $className = "CourseGroupType";
            $displayName = "Educational Course Group Types";
            break;
        case "mrntype":
            $className = "MrnType";
            $displayName = "MRN Types";
            break;
        case "accessiontype":
            $className = "AccessionType";
            $displayName = "Accession Types";
            break;
        case "encountertype":
            $className = "EncounterType";
            $displayName = "Encounter Number Types";
            break;
        case "proceduretype":
            $className = "ProcedureType";
            $displayName = "Procedure Number Types";
            break;
        case "stain":
            $className = "StainList";
            $displayName = "Stains";
            break;
        case "organ":
            $className = "OrganList";
            $displayName = "Organs";
            break;
        case "encounter":
            $className = "EncounterList";
            $displayName = "Encounter Types";
            break;
        case "procedure":
            $className = "ProcedureList";
            $displayName = "Procedure Types";
            break;
        case "slidetype":
            $className = "SlideType";
            $displayName = "Slide Types";
            break;
        case "messagecategorys":
            $className = "MessageCategory";
            $displayName = "Message categories";
            break;
        case "status":
            $className = "Status";
            $displayName = "Statuses";
            break;
        case "orderdelivery":
            $className = "OrderDelivery";
            $displayName = "Scan Order Delivery Options";
            break;
        case "regiontoscan":
            $className = "RegionToScan";
            $displayName = '"Region To Scan" Options';
            break;
        case "processorcomment":
            $className = "ProcessorComments";
            $displayName = "Scan Order Processor Comments";
            break;
        case "accounts":
            $className = "Account";
            $displayName = "Account Numbers";
            break;
        case "urgency":
            $className = "Urgency";
            $displayName = "Urgency Types";
            break;
        case "progresscommentseventtypes":
            $className = "ProgressCommentsEventTypeList";
            $displayName = "Progress and Comments Event Types";
            break;
        case "scanloggereventtypes":
            $className = "EventTypeList";
            $displayName = "Event Log's Event Types";
            $classPath = "App\\UserdirectoryBundle\\Entity\\";
            $bundleName = "UserdirectoryBundle";
            break;
        case "races":
            $className = "RaceList";
            $displayName = "Races";
            break;
        case "reporttype":
            $className = "ReportType";
            $displayName = "Report Types";
            break;
        case "instruction":
            $className = "EmbedderInstructionList";
            $displayName = "Instructions for Embedder";
            break;
        case "patienttype":
            $className = "PatientTypeList";
            $displayName = "Patient Types";
            break;
        case "magnifications":
            $className = "Magnification";
            $displayName = "Magnifications";
            break;
        case "imageanalysisalgorithm":
            $className = "ImageAnalysisAlgorithmList";
            $displayName = "Image Analysis Algorithms";
            break;
        case "diseasetypes":
            $className = "DiseaseTypeList";
            $displayName = "Disease Types";
            break;
        case "diseaseorigins":
            $className = "DiseaseOriginList";
            $displayName = "Disease Origins";
            break;
        case "labtesttype":
            $className = "LabTestType";
            $displayName = "Laboratory Test ID Types";
            break;
        case "parttitle":
            $className = "ParttitleList";
            $displayName = "Part Titles";
            break;
//        case "systemaccountrequesttypes":
//            $className = "SystemAccountRequestType";
//            $displayName = "System Account Request Types";
//            break;
        case "messagetypeclassifiers":
            $className = "MessageTypeClassifiers";
            $displayName = "Message Type Classifiers";
            break;
        case "amendmentreasons":
            $className = "AmendmentReasonList";
            $displayName = "Amendment Reasons";
            break;
        case "patientlisthierarchys":
            $className = "PatientListHierarchy";
            $displayName = "Patient List Hierarchy";
            break;
        case "pathologycallcomplexpatients":
            $className = "PathologyCallComplexPatients";
            $displayName = "Pathology Call Complex Patients";
            $classPath = "App\\CallLogBundle\\Entity\\";
            $bundleName = "CallLogBundle";
            break;
        case "patientlisthierarchygrouptype":
            $className = "PatientListHierarchyGroupType";
            $displayName = "Patient List Hierarchy Group Types";
            break;
        case "encounterstatuses":
            $className = "EncounterStatusList";
            $displayName = "Encounter Statuses";
            break;
        case "patientrecordstatuses":
            $className = "PatientRecordStatusList";
            $displayName = "Patient Record Statuses";
            break;
        case "messagestatuses":
            $className = "MessageStatusList";
            $displayName = "Message Statuses";
            break;
//        case "encounterinfotype":
//            $className = "EncounterInfoType";
//            $displayName = "Encounter Info Types";
//            break;
        case "encounterinfotypes":
            $className = "EncounterInfoTypeList";
            $displayName = "Encounter Info Type List";
            break;
        case "suggestedmessagecategorys":
            $className = "SuggestedMessageCategoriesList";
            $displayName = "Suggested Message Categories List";
            break;
//        case "calllogentrytags":
//            $className = "CalllogEntryTagsList";
//            $displayName = "Call Log Entry Tags List";
//            break;
        case "calllogattachmenttypes":
            $className = "CalllogAttachmentTypeList";
            $displayName = "Call Log Attachment Type List";
            break;
        case "calllogtasktypes":
            $className = "CalllogTaskTypeList";
            $displayName = "Call Log Task Type List";
            break;

        case "messagetagtypes":
            $className = "MessageTagTypesList";
            $displayName = "Message Tag Types List";
            break;
        case "messagetags":
            $className = "MessageTagsList";
            $displayName = "Message Tags List";
            break;

        case "accessionlisthierarchys":
            $className = "AccessionListHierarchy";
            $displayName = "Accession List Hierarchy";
            break;
        case "accessionlisthierarchygrouptype":
            $className = "AccessionListHierarchyGroupType";
            $displayName = "Accession List Hierarchy Group Type";
            break;
        case "accessionlisttype":
            $className = "AccessionListType";
            $displayName = "Accession List Type";
            break;

        default:
            $className = null;
            $displayName = null;
        }

        //echo "className=".$className.", displayName=".$displayName."<br>";

        $res = array();
        $res['className'] = $className;
        $res['fullClassName'] = $classPath.$className;
        $res['bundleName'] = $bundleName;
        $res['entityNamespace'] = "App\\".$bundleName."\\Entity";
        $res['displayName'] = $displayName;
        $res['linkToListId'] = null;

        return $res;
    }

    /////////////////// DELETE IS NOT USED /////////////////////////
    /**
     * Deletes a entity.
     *
     *
     */
    #[Route(path: '/research-project-titles/{id}', name: 'researchprojecttitles_delete', methods: ['DELETE'])]
    #[Route(path: '/research-project-group-types/{id}', name: 'researchprojectgrouptype_delete', methods: ['DELETE'])]
    #[Route(path: '/educational-course-titles/{id}', name: 'educationalcoursetitles_delete', methods: ['DELETE'])]
    #[Route(path: '/educational-course-group-types/{id}', name: 'educationalcoursegrouptypes_delete', methods: ['DELETE'])]
    #[Route(path: '/mrn-types/{id}', name: 'mrntype_delete', methods: ['DELETE'])]
    #[Route(path: '/accession-types/{id}', name: 'accessiontype_delete', methods: ['DELETE'])]
    #[Route(path: '/encounter-number-types/{id}', name: 'encountertype_delete', methods: ['DELETE'])]
    #[Route(path: '/procedure-number-types/{id}', name: 'proceduretype_delete', methods: ['DELETE'])]
    #[Route(path: '/stains/{id}', name: 'stain_delete', methods: ['DELETE'])]
    #[Route(path: '/organs/{id}', name: 'organ_delete', methods: ['DELETE'])]
    #[Route(path: '/encounter-types/{id}', name: 'encounter_delete', methods: ['DELETE'])]
    #[Route(path: '/procedure-types/{id}', name: 'procedure_delete', methods: ['DELETE'])]
    #[Route(path: '/pathology-services/{id}', name: 'pathservice_delete', methods: ['DELETE'])]
    #[Route(path: '/slide-types/{id}', name: 'slidetype_delete', methods: ['DELETE'])]
    #[Route(path: '/message-categories/{id}', name: 'messagecategorys_delete', methods: ['DELETE'])]
    #[Route(path: '/statuses/{id}', name: 'status_delete', methods: ['DELETE'])]
    #[Route(path: '/scan-order-delivery-options/{id}', name: 'orderdelivery_delete', methods: ['DELETE'])]
    #[Route(path: '/region-to-scan-options/{id}', name: 'regiontoscan_delete', methods: ['DELETE'])]
    #[Route(path: '/scan-order-processor-comments/{id}', name: 'processorcomment_delete', methods: ['DELETE'])]
    #[Route(path: '/account-numbers/{id}', name: 'accounts_delete', methods: ['DELETE'])]
    #[Route(path: '/urgency-types/{id}', name: 'urgency_delete', methods: ['DELETE'])]
    #[Route(path: '/progress-and-comments-event-types/{id}', name: 'progresscommentseventtypes_delete', methods: ['DELETE'])]
    #[Route(path: '/event-log-event-types/{id}', name: 'scanloggereventtypes_delete', methods: ['DELETE'])]
    #[Route(path: '/races/{id}', name: 'races_delete', methods: ['DELETE'])]
    #[Route(path: '/report-types/{id}', name: 'reporttype_delete', methods: ['DELETE'])]
    #[Route(path: '/instructions-for-embedder/{id}', name: 'instruction_delete', methods: ['DELETE'])]
    #[Route(path: '/patient-types/{id}', name: 'patienttype_delete', methods: ['DELETE'])]
    #[Route(path: '/magnifications/{id}', name: 'magnifications_delete', methods: ['DELETE'])]
    #[Route(path: '/image-analysis-algorithms/{id}', name: 'imageanalysisalgorithm_delete', methods: ['DELETE'])]
    #[Route(path: '/disease-types/{id}', name: 'diseasetypes_delete', methods: ['DELETE'])]
    #[Route(path: '/disease-origins/{id}', name: 'diseaseorigins_delete', methods: ['DELETE'])]
    #[Route(path: '/laboratory-test-id-types/{id}', name: 'labtesttype_delete', methods: ['DELETE'])]
    #[Route(path: '/part-titles/{id}', name: 'parttitle_delete', methods: ['DELETE'])]
    #[Route(path: '/message-type-classifiers/{id}', name: 'messagetypeclassifiers_delete', methods: ['DELETE'])]
    #[Route(path: '/amendment-reasons/{id}', name: 'amendmentreasons_delete', methods: ['DELETE'])]
    #[Route(path: '/pathology-call-complex-patients/{id}', name: 'pathologycallcomplexpatients_delete', methods: ['DELETE'])]
    #[Route(path: '/patient-list-hierarchy/{id}', name: 'patientlisthierarchys_delete', methods: ['DELETE'])]
    #[Route(path: '/patient-list-hierarchy-group-types/{id}', name: 'patientlisthierarchygrouptype_delete', methods: ['DELETE'])]
    #[Route(path: '/encounter-statuses/{id}', name: 'encounterstatuses_delete', methods: ['DELETE'])]
    #[Route(path: '/patient-record-statuses/{id}', name: 'patientrecordstatuses_delete', methods: ['DELETE'])]
    #[Route(path: '/message-statuses/{id}', name: 'messagestatuses_delete', methods: ['DELETE'])]
    #[Route(path: '/encounter-info-types/{id}', name: 'encounterinfotypes_delete', methods: ['DELETE'])]
    #[Route(path: '/suggested-message-categories/{id}', name: 'suggestedmessagecategorys_delete', methods: ['DELETE'])]
    #[Route(path: '/calllog-attachment-types/{id}', name: 'calllogattachmenttypes_delete', methods: ['DELETE'])]
    #[Route(path: '/calllog-task-types/{id}', name: 'calllogtasktypes_delete', methods: ['DELETE'])]
    #[Route(path: '/message-tag-types/{id}', name: 'messagetagtypes_delete', methods: ['DELETE'])]
    #[Route(path: '/message-tags/{id}', name: 'messagetags_delete', methods: ['DELETE'])]
    #[Route(path: '/accession-list-hierarchys/{id}', name: 'accessionlisthierarchys_delete', methods: ['DELETE'])]
    #[Route(path: '/accession-list-hierarchy-group-type/{id}', name: 'accessionlisthierarchygrouptype_delete', methods: ['DELETE'])]
    #[Route(path: '/accession-list-types/{id}', name: 'accessionlisttype_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, $id)
    {
        return $this->redirect( $this->generateUrl($this->getParameter('scan.sitename').'-order-nopermission') );
        //return $this->deleteList($request, $id);
    }
    /////////////////// DELETE IS NOT USED /////////////////////////

}
