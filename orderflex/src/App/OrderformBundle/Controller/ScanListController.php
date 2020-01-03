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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use App\OrderformBundle\Helper\ErrorHelper;

use App\UserdirectoryBundle\Controller\ListController;

/**
 * Common list controller
 * @Route("/admin/list")
 */
class ScanListController extends ListController
{

    /**
     * @Route("/stains-spreadsheet/", name="stain-list-excel")
     * @Method("GET")
     * @Template()
     */
    public function downloadStainExcelAction(Request $request)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        $listArr = $this->getList($request,1000000);


        $listExcelHtml = $this->container->get('templating')->render('AppOrderformBundle:ListForm:list-excel.html.twig',
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

    /**
     * @Route("/stains-update-full-title/", name="stain_update_fulltitle")
     * @Method("GET")
     * @Template()
     */
    public function updateFullTitleListAction(Request $request)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $pathbase = $pieces[0];
        //echo "pathbase=".$pathbase."<br>";
        //exit();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppOrderformBundle:StainList')->findAll();

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

        $this->get('session')->getFlashBag()->add(
            'notice',
            "Stain's Full Title updated: " . $count
        );

        return $this->redirect($this->generateUrl($pathbase.'-list'));
    }


//* @Route("/principal-investigators/", name="principalinvestigators-list")
//* @Route("/course-directors/", name="coursedirectors-list")
//     * @Route("/system-account-request-types/", name="systemaccountrequesttypes-list")


    /**
     * Lists all entities.
     *
     * @Route("/research-project-titles/", name="researchprojecttitles-list", options={"expose"=true})
     * @Route("/research-project-group-types/", name="researchprojectgrouptype-list")
     * @Route("/educational-course-titles/", name="educationalcoursetitles-list", options={"expose"=true})
     * @Route("/educational-course-group-types/", name="educationalcoursegrouptypes-list")
     * @Route("/mrn-types/", name="mrntype-list")
     * @Route("/accession-types/", name="accessiontype-list")
     * @Route("/encounter-number-types/", name="encountertype-list")
     * @Route("/procedure-number-types/", name="proceduretype-list")
     * @Route("/stains/", name="stain-list")
     * @Route("/organs/", name="organ-list")
     * @Route("/encounter-types/", name="encounter-list")
     * @Route("/procedure-types/", name="procedure-list")
     * @Route("/pathology-services/", name="pathservice-list")
     * @Route("/slide-types/", name="slidetype-list")
     * @Route("/message-categories/", name="messagecategorys-list", options={"expose"=true})
     * @Route("/statuses/", name="status-list")
     * @Route("/scan-order-delivery-options/", name="orderdelivery-list")
     * @Route("/region-to-scan-options/", name="regiontoscan-list")
     * @Route("/scan-order-processor-comments/", name="processorcomment-list")
     * @Route("/account-numbers/", name="accounts-list")
     * @Route("/urgency-types/", name="urgency-list")
     * @Route("/progress-and-comments-event-types/", name="progresscommentseventtypes-list")
     * @Route("/event-log-event-types/", name="scanloggereventtypes-list")
     * @Route("/races/", name="races-list")
     * @Route("/report-types/", name="reporttype-list")
     * @Route("/instructions-for-embedder/", name="instruction-list")
     * @Route("/patient-types/", name="patienttype-list")
     * @Route("/magnifications/", name="magnifications-list")
     * @Route("/image-analysis-algorithms/", name="imageanalysisalgorithm-list")
     * @Route("/disease-types/", name="diseasetypes-list")
     * @Route("/disease-origins/", name="diseaseorigins-list")
     * @Route("/laboratory-test-id-types/", name="labtesttype-list")
     * @Route("/part-titles/", name="parttitle-list")
     * @Route("/message-type-classifiers/", name="messagetypeclassifiers-list")
     * @Route("/amendment-reasons/", name="amendmentreasons-list")
     * @Route("/pathology-call-complex-patients/", name="pathologycallcomplexpatients-list")
     * @Route("/patient-list-hierarchy/", name="patientlisthierarchys-list")
     * @Route("/patient-list-hierarchy-group-types/", name="patientlisthierarchygrouptype-list")
     * @Route("/encounter-statuses/", name="encounterstatuses-list")
     * @Route("/patient-record-statuses/", name="patientrecordstatuses-list")
     * @Route("/message-statuses/", name="messagestatuses-list")
     * @Route("/encounter-info-types/", name="encounterinfotypes-list")
     * @Route("/suggested-message-categories/", name="suggestedmessagecategorys-list")
     * @Route("/calllog-entry-tags/", name="calllogentrytags-list")
     * @Route("/calllog-attachment-types/", name="calllogattachmenttypes-list")
     * @Route("/calllog-task-types/", name="calllogtasktypes-list")
     *
     *
     * @Method("GET")
     * @Template("AppOrderformBundle:ListForm:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->getList($request);
    }

    /**
     * Creates a new entity.
     *
     * @Route("/research-project-titles/", name="researchprojecttitles_create")
     * @Route("/research-project-group-types/", name="researchprojectgrouptype_create")
     * @Route("/educational-course-titles/", name="educationalcoursetitles_create")
     * @Route("/educational-course-group-types/", name="educationalcoursegrouptypes_create")
     * @Route("/mrn-types/", name="mrntype_create")
     * @Route("/accession-types/", name="accessiontype_create")
     * @Route("/encounter-number-types/", name="encountertype_create")
     * @Route("/procedure-number-types/", name="proceduretype_create")
     * @Route("/stains/", name="stain_create")
     * @Route("/organs/", name="organ_create")
     * @Route("/encounter-types/", name="encounter_create")
     * @Route("/procedure-types/", name="procedure_create")
     * @Route("/pathology-services/", name="pathservice_create")
     * @Route("/slide-types/", name="slidetype_create")
     * @Route("/message-categories/", name="messagecategorys_create")
     * @Route("/statuses/", name="status_create")
     * @Route("/scan-order-delivery-options/", name="orderdelivery_create")
     * @Route("/region-to-scan-options/", name="regiontoscan_create")
     * @Route("/scan-order-processor-comments/", name="processorcomment_create")
     * @Route("/account-numbers/", name="accounts_create")
     * @Route("/urgency-types/", name="urgency_create")
     * @Route("/progress-and-comments-event-types/", name="progresscommentseventtypes_create")
     * @Route("/event-log-event-types/", name="scanloggereventtypes_create")
     * @Route("/races/", name="races_create")
     * @Route("/report-types/", name="reporttype_create")
     * @Route("/instructions-for-embedder/", name="instruction_create")
     * @Route("/patient-types/", name="patienttype_create")
     * @Route("/magnifications/", name="magnifications_create")
     * @Route("/image-analysis-algorithms/", name="imageanalysisalgorithm_create")
     * @Route("/disease-types/", name="diseasetypes_create")
     * @Route("/disease-origins/", name="diseaseorigins_create")
     * @Route("/laboratory-test-id-types/", name="labtesttype_create")
     * @Route("/part-titles/", name="parttitle_create")
     * @Route("/message-type-classifiers/", name="messagetypeclassifiers_create")
     * @Route("/amendment-reasons/", name="amendmentreasons_create")
     * @Route("/pathology-call-complex-patients/", name="pathologycallcomplexpatients_create")
     * @Route("/patient-list-hierarchy/", name="patientlisthierarchys_create")
     * @Route("/patient-list-hierarchy-group-types/", name="patientlisthierarchygrouptype_create")
     * @Route("/encounter-statuses/", name="encounterstatuses_create")
     * @Route("/patient-record-statuses/", name="patientrecordstatuses_create")
     * @Route("/message-statuses/", name="messagestatuses_create")
     * @Route("/encounter-info-types/", name="encounterinfotypes_create")
     * @Route("/suggested-message-categories/", name="suggestedmessagecategorys_create")
     * @Route("/calllog-entry-tags/", name="calllogentrytags_create")
     * @Route("/calllog-attachment-types/", name="calllogattachmenttypes_create")
     * @Route("/calllog-task-types/", name="calllogtasktypes_create")
     *
     * @Method("POST")
     * @Template("AppOrderformBundle:ListForm:new.html.twig")
     */
    public function createAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->createList($request);
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/research-project-titles/new", name="researchprojecttitles_new")
     * @Route("/research-project-group-types/new", name="researchprojectgrouptype_new")
     * @Route("/educational-course-titles/new", name="educationalcoursetitles_new")
     * @Route("/educational-course-group-types/new", name="educationalcoursegrouptypes_new")
     * @Route("/mrn-types/new", name="mrntype_new")
     * @Route("/accession-types/new", name="accessiontype_new")
     * @Route("/encounter-number-types/new", name="encountertype_new")
     * @Route("/procedure-number-types/new", name="proceduretype_new")
     * @Route("/stains/new", name="stain_new")
     * @Route("/organs/new", name="organ_new")
     * @Route("/encounter-types/new", name="encounter_new")
     * @Route("/procedure-types/new", name="procedure_new")
     * @Route("/pathology-services/new", name="pathservice_new")
     * @Route("/slide-types/new", name="slidetype_new")
     * @Route("/message-categories/new", name="messagecategorys_new")
     * @Route("/statuses/new", name="status_new")
     * @Route("/scan-order-delivery-options/new", name="orderdelivery_new")
     * @Route("/region-to-scan-options/new", name="regiontoscan_new")
     * @Route("/scan-order-processor-comments/new", name="processorcomment_new")
     * @Route("/account-numbers/new", name="accounts_new")
     * @Route("/urgency-types/new", name="urgency_new")
     * @Route("/progress-and-comments-event-types/new", name="progresscommentseventtypes_new")
     * @Route("/event-log-event-types/new", name="scanloggereventtypes_new")
     * @Route("/races/new", name="races_new")
     * @Route("/report-types/new", name="reporttype_new")
     * @Route("/instructions-for-embedder/new", name="instruction_new")
     * @Route("/patient-types/new", name="patienttype_new")
     * @Route("/magnifications/new", name="magnifications_new")
     * @Route("/image-analysis-algorithms/new", name="imageanalysisalgorithm_new")
     * @Route("/disease-types/new", name="diseasetypes_new")
     * @Route("/disease-origins/new", name="diseaseorigins_new")
     * @Route("/laboratory-test-id-types/new", name="labtesttype_new")
     * @Route("/part-titles/new", name="parttitle_new")
     * @Route("/message-type-classifiers/new", name="messagetypeclassifiers_new")
     * @Route("/amendment-reasons/new", name="amendmentreasons_new")
     * @Route("/pathology-call-complex-patients/new", name="pathologycallcomplexpatients_new")
     * @Route("/patient-list-hierarchy/new", name="patientlisthierarchys_new")
     * @Route("/patient-list-hierarchy-group-types/new", name="patientlisthierarchygrouptype_new")
     * @Route("/encounter-statuses/new", name="encounterstatuses_new")
     * @Route("/patient-record-statuses/new", name="patientrecordstatuses_new")
     * @Route("/message-statuses/new", name="messagestatuses_new")
     * @Route("/encounter-info-types/new", name="encounterinfotypes_new")
     * @Route("/suggested-message-categories/new", name="suggestedmessagecategorys_new")
     * @Route("/calllog-entry-tags/new", name="calllogentrytags_new")
     * @Route("/calllog-attachment-types/new", name="calllogattachmenttypes_new")
     * @Route("/calllog-task-types/new", name="calllogtasktypes_new")
     *
     * @Method("GET")
     * @Template("AppOrderformBundle:ListForm:new.html.twig")
     */
    public function newAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->newList($request);
    }

    /**
     * Finds and displays a entity.
     *
     * @Route("/research-project-titles/{id}", name="researchprojecttitles_show", options={"expose"=true})
     * @Route("/research-project-group-types/{id}", name="researchprojectgrouptype_show")
     * @Route("/educational-course-titles/{id}", name="educationalcoursetitles_show", options={"expose"=true})
     * @Route("/educational-course-group-types/{id}", name="educationalcoursegrouptypes_show")
     * @Route("/mrn-types/{id}", name="mrntype_show")
     * @Route("/accession-types/{id}", name="accessiontype_show")
     * @Route("/encounter-number-types/{id}", name="encountertype_show")
     * @Route("/procedure-number-types/{id}", name="proceduretype_show")
     * @Route("/stains/{id}", name="stain_show")
     * @Route("/organs/{id}", name="organ_show")
     * @Route("/encounter-types/{id}", name="encounter_show")
     * @Route("/procedure-types/{id}", name="procedure_show")
     * @Route("/pathology-services/{id}", name="pathservice_show")
     * @Route("/slide-types/{id}", name="slidetype_show")
     * @Route("/message-categories/{id}", name="messagecategorys_show", options={"expose"=true})
     * @Route("/statuses/{id}", name="status_show")
     * @Route("/scan-order-delivery-options/{id}", name="orderdelivery_show")
     * @Route("/region-to-scan-options/{id}", name="regiontoscan_show")
     * @Route("/scan-order-processor-comments/{id}", name="processorcomment_show")
     * @Route("/account-numbers/{id}", name="accounts_show")
     * @Route("/urgency-types/{id}", name="urgency_show")
     * @Route("/progress-and-comments-event-types/{id}", name="progresscommentseventtypes_show")
     * @Route("/event-log-event-types/{id}", name="scanloggereventtypes_show")
     * @Route("/races/{id}", name="races_show")
     * @Route("/report-types/{id}", name="reporttype_show")
     * @Route("/instructions-for-embedder/{id}", name="instruction_show")
     * @Route("/patient-types/{id}", name="patienttype_show")
     * @Route("/magnifications/{id}", name="magnifications_show")
     * @Route("/image-analysis-algorithms/{id}", name="imageanalysisalgorithm_show")
     * @Route("/disease-types/{id}", name="diseasetypes_show")
     * @Route("/disease-origins/{id}", name="diseaseorigins_show")
     * @Route("/laboratory-test-id-types/{id}", name="labtesttype_show")
     * @Route("/part-titles/{id}", name="parttitle_show")
     * @Route("/message-type-classifiers/{id}", name="messagetypeclassifiers_show")
     * @Route("/amendment-reasons/{id}", name="amendmentreasons_show")
     * @Route("/pathology-call-complex-patients/{id}", name="pathologycallcomplexpatients_show")
     * @Route("/patient-list-hierarchy/{id}", name="patientlisthierarchys_show", options={"expose"=true})
     * @Route("/patient-list-hierarchy-group-types/{id}", name="patientlisthierarchygrouptype_show")
     * @Route("/encounter-statuses/{id}", name="encounterstatuses_show")
     * @Route("/patient-record-statuses/{id}", name="patientrecordstatuses_show")
     * @Route("/message-statuses/{id}", name="messagestatuses_show")
     * @Route("/encounter-info-types/{id}", name="encounterinfotypes_show")
     * @Route("/suggested-message-categories/{id}", name="suggestedmessagecategorys_show")
     * @Route("/calllog-entry-tags/{id}", name="calllogentrytags_show")
     * @Route("/calllog-attachment-types/{id}", name="calllogattachmenttypes_show")
     * @Route("/calllog-task-types/{id}", name="calllogtasktypes_show")
     *
     * @Method("GET")
     * @Template("AppOrderformBundle:ListForm:show.html.twig")
     */
    public function showAction(Request $request,$id)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->showList($request,$id,true);
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/research-project-titles/{id}/edit", name="researchprojecttitles_edit")
     * @Route("/research-project-group-types/{id}/edit", name="researchprojectgrouptype_edit")
     * @Route("/educational-course-titles/{id}/edit", name="educationalcoursetitles_edit")
     * @Route("/educational-course-group-types/{id}/edit", name="educationalcoursegrouptypes_edit")
     * @Route("/mrn-types/{id}/edit", name="mrntype_edit")
     * @Route("/accession-types/{id}/edit", name="accessiontype_edit")
     * @Route("/encounter-number-types/{id}/edit", name="encountertype_edit")
     * @Route("/procedure-number-types/{id}/edit", name="proceduretype_edit")
     * @Route("/stains/{id}/edit", name="stain_edit")
     * @Route("/organs/{id}/edit", name="organ_edit")
     * @Route("/encounter-types/{id}/edit", name="encounter_edit")
     * @Route("/procedure-types/{id}/edit", name="procedure_edit")
     * @Route("/pathology-services/{id}/edit", name="pathservice_edit")
     * @Route("/slide-types/{id}/edit", name="slidetype_edit")
     * @Route("/message-categories/{id}/edit", name="messagecategorys_edit")
     * @Route("/statuses/{id}/edit", name="status_edit")
     * @Route("/scan-order-delivery-options/{id}/edit", name="orderdelivery_edit")
     * @Route("/region-to-scan-options/{id}/edit", name="regiontoscan_edit")
     * @Route("/scan-order-processor-comments/{id}/edit", name="processorcomment_edit")
     * @Route("/account-numbers/{id}/edit", name="accounts_edit")
     * @Route("/urgency-types/{id}/edit", name="urgency_edit")
     * @Route("/progress-and-comments-event-types/{id}/edit", name="progresscommentseventtypes_edit")
     * @Route("/event-log-event-types/{id}/edit", name="scanloggereventtypes_edit")
     * @Route("/races/{id}/edit", name="races_edit")
     * @Route("/report-types/{id}/edit", name="reporttype_edit")
     * @Route("/instructions-for-embedder/{id}/edit", name="instruction_edit")
     * @Route("/patient-types/{id}/edit", name="patienttype_edit")
     * @Route("/magnifications/{id}/edit", name="magnifications_edit")
     * @Route("/image-analysis-algorithms/{id}/edit", name="imageanalysisalgorithm_edit")
     * @Route("/disease-types/{id}/edit", name="diseasetypes_edit")
     * @Route("/disease-origins/{id}/edit", name="diseaseorigins_edit")
     * @Route("/laboratory-test-id-types/{id}/edit", name="labtesttype_edit")
     * @Route("/part-titles/{id}/edit", name="parttitle_edit")
     * @Route("/message-type-classifiers/{id}/edit", name="messagetypeclassifiers_edit")
     * @Route("/amendment-reasons/{id}/edit", name="amendmentreasons_edit")
     * @Route("/pathology-call-complex-patients/{id}/edit", name="pathologycallcomplexpatients_edit")
     * @Route("/patient-list-hierarchy/{id}/edit", name="patientlisthierarchys_edit")
     * @Route("/patient-list-hierarchy-group-types/{id}/edit", name="patientlisthierarchygrouptype_edit")
     * @Route("/encounter-statuses/{id}/edit", name="encounterstatuses_edit")
     * @Route("/patient-record-statuses/{id}/edit", name="patientrecordstatuses_edit")
     * @Route("/message-statuses/{id}/edit", name="messagestatuses_edit")
     * @Route("/encounter-info-types/{id}/edit", name="encounterinfotypes_edit")
     * @Route("/suggested-message-categories/{id}/edit", name="suggestedmessagecategorys_edit")
     * @Route("/calllog-entry-tags/{id}/edit", name="calllogentrytags_edit")
     * @Route("/calllog-attachment-types/{id}/edit", name="calllogattachmenttypes_edit")
     * @Route("/calllog-task-types/{id}/edit", name="calllogtasktypes_edit")
     *
     * @Method("GET")
     * @Template("AppOrderformBundle:ListForm:edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->editList($request,$id);
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/research-project-titles/{id}", name="researchprojecttitles_update")
     * @Route("/research-project-group-types/{id}", name="researchprojectgrouptype_update")
     * @Route("/educational-course-titles/{id}", name="educationalcoursetitles_update")
     * @Route("/educational-course-group-types/{id}", name="educationalcoursegrouptypes_update")
     * @Route("/mrn-types/{id}", name="mrntype_update")
     * @Route("/accession-types/{id}", name="accessiontype_update")
     * @Route("/encounter-number-types/{id}", name="encountertype_update")
     * @Route("/procedure-number-types/{id}", name="proceduretype_update")
     * @Route("/stains/{id}", name="stain_update")
     * @Route("/organs/{id}", name="organ_update")
     * @Route("/encounter-types/{id}", name="encounter_update")
     * @Route("/procedure-types/{id}", name="procedure_update")
     * @Route("/pathology-services/{id}", name="pathservice_update")
     * @Route("/slide-types/{id}", name="slidetype_update")
     * @Route("/message-categories/{id}", name="messagecategorys_update")
     * @Route("/statuses/{id}", name="status_update")
     * @Route("/scan-order-delivery-options/{id}", name="orderdelivery_update")
     * @Route("/region-to-scan-options/{id}", name="regiontoscan_update")
     * @Route("/scan-order-processor-comments/{id}", name="processorcomment_update")
     * @Route("/account-numbers/{id}", name="accounts_update")
     * @Route("/urgency-types/{id}", name="urgency_update")
     * @Route("/progress-and-comments-event-types/{id}", name="progresscommentseventtypes_update")
     * @Route("/event-log-event-types/{id}", name="scanloggereventtypes_update")
     * @Route("/races/{id}", name="races_update")
     * @Route("/report-types/{id}", name="reporttype_update")
     * @Route("/instructions-for-embedder/{id}", name="instruction_update")
     * @Route("/patient-types/{id}", name="patienttype_update")
     * @Route("/magnifications/{id}", name="magnifications_update")
     * @Route("/image-analysis-algorithms/{id}", name="imageanalysisalgorithm_update")
     * @Route("/disease-types/{id}", name="diseasetypes_update")
     * @Route("/disease-origins/{id}", name="diseaseorigins_update")
     * @Route("/laboratory-test-id-types/{id}", name="labtesttype_update")
     * @Route("/part-titles/{id}", name="parttitle_update")
     * @Route("/message-type-classifiers/{id}", name="messagetypeclassifiers_update")
     * @Route("/amendment-reasons/{id}", name="amendmentreasons_update")
     * @Route("/pathology-call-complex-patients/{id}", name="pathologycallcomplexpatients_update")
     * @Route("/patient-list-hierarchy/{id}", name="patientlisthierarchys_update")
     * @Route("/patient-list-hierarchy-group-types/{id}", name="patientlisthierarchygrouptype_update")
     * @Route("/encounter-statuses/{id}", name="encounterstatuses_update")
     * @Route("/patient-record-statuses/{id}", name="patientrecordstatuses_update")
     * @Route("/message-statuses/{id}", name="messagestatuses_update")
     * @Route("/encounter-info-types/{id}", name="encounterinfotypes_update")
     * @Route("/suggested-message-categories/{id}", name="suggestedmessagecategorys_update")
     * @Route("/calllog-entry-tags/{id}", name="calllogentrytags_update")
     * @Route("/calllog-attachment-types/{id}", name="calllogattachmenttypes_update")
     * @Route("/calllog-task-types/{id}", name="calllogtasktypes_update")
     *
     * @Method("PUT")
     * @Template("AppOrderformBundle:ListForm:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->updateList($request, $id);
    }


    public function classListMapper( $route, $request ) {

        $classPath = "App\\OrderformBundle\\Entity\\";
        $bundleName = "AppOrderformBundle";

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
            $bundleName = "AppUserdirectoryBundle";
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
            $bundleName = "AppCallLogBundle";
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
        case "calllogentrytags":
            $className = "CalllogEntryTagsList";
            $displayName = "Call Log Entry Tags List";
            break;
        case "calllogattachmenttypes":
            $className = "CalllogAttachmentTypeList";
            $displayName = "Call Log Attachment Type List";
            break;
        case "calllogtasktypes":
            $className = "CalllogTaskTypeList";
            $displayName = "Call Log Task Type List";
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
        $res['displayName'] = $displayName;
        $res['linkToListId'] = null;

        return $res;
    }

    /////////////////// DELETE IS NOT USED /////////////////////////
    /**
     * Deletes a entity.
     *
     * @Route("/research-project-titles/{id}", name="researchprojecttitles_delete")
     * @Route("/research-project-group-types/{id}", name="researchprojectgrouptype_delete")
     * @Route("/educational-course-titles/{id}", name="educationalcoursetitles_delete")
     * @Route("/educational-course-group-types/{id}", name="educationalcoursegrouptypes_delete")
     * @Route("/mrn-types/{id}", name="mrntype_delete")
     * @Route("/accession-types/{id}", name="accessiontype_delete")
     * @Route("/encounter-number-types/{id}", name="encountertype_delete")
     * @Route("/procedure-number-types/{id}", name="proceduretype_delete")
     * @Route("/stains/{id}", name="stain_delete")
     * @Route("/organs/{id}", name="organ_delete")
     * @Route("/encounter-types/{id}", name="encounter_delete")
     * @Route("/procedure-types/{id}", name="procedure_delete")
     * @Route("/pathology-services/{id}", name="pathservice_delete")
     * @Route("/slide-types/{id}", name="slidetype_delete")
     * @Route("/message-categories/{id}", name="messagecategorys_delete")
     * @Route("/statuses/{id}", name="status_delete")
     * @Route("/scan-order-delivery-options/{id}", name="orderdelivery_delete")
     * @Route("/region-to-scan-options/{id}", name="regiontoscan_delete")
     * @Route("/scan-order-processor-comments/{id}", name="processorcomment_delete")
     * @Route("/account-numbers/{id}", name="accounts_delete")
     * @Route("/urgency-types/{id}", name="urgency_delete")
     * @Route("/progress-and-comments-event-types/{id}", name="progresscommentseventtypes_delete")
     * @Route("/event-log-event-types/{id}", name="scanloggereventtypes_delete")
     * @Route("/races/{id}", name="races_delete")
     * @Route("/report-types/{id}", name="reporttype_delete")
     * @Route("/instructions-for-embedder/{id}", name="instruction_delete")
     * @Route("/patient-types/{id}", name="patienttype_delete")
     * @Route("/magnifications/{id}", name="magnifications_delete")
     * @Route("/image-analysis-algorithms/{id}", name="imageanalysisalgorithm_delete")
     * @Route("/disease-types/{id}", name="diseasetypes_delete")
     * @Route("/disease-origins/{id}", name="diseaseorigins_delete")
     * @Route("/laboratory-test-id-types/{id}", name="labtesttype_delete")
     * @Route("/part-titles/{id}", name="parttitle_delete")
     * @Route("/message-type-classifiers/{id}", name="messagetypeclassifiers_delete")
     * @Route("/amendment-reasons/{id}", name="amendmentreasons_delete")
     * @Route("/pathology-call-complex-patients/{id}", name="pathologycallcomplexpatients_delete")
     * @Route("/patient-list-hierarchy/{id}", name="patientlisthierarchys_delete")
     * @Route("/patient-list-hierarchy-group-types/{id}", name="patientlisthierarchygrouptype_delete")
     * @Route("/encounter-statuses/{id}", name="encounterstatuses_delete")
     * @Route("/patient-record-statuses/{id}", name="patientrecordstatuses_delete")
     * @Route("/message-statuses/{id}", name="messagestatuses_delete")
     * @Route("/encounter-info-types/{id}", name="encounterinfotypes_delete")
     * @Route("/suggested-message-categories/{id}", name="suggestedmessagecategorys_delete")
     * @Route("/calllog-entry-tags/{id}", name="calllogentrytags_delete")
     * @Route("/calllog-attachment-types/{id}", name="calllogattachmenttypes_delete")
     * @Route("/calllog-task-types/{id}", name="calllogtasktypes_delete")
     *
     *
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        //return $this->deleteList($request, $id);
    }
    /////////////////// DELETE IS NOT USED /////////////////////////

}
