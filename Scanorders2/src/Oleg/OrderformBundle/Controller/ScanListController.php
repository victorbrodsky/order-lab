<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\OrderformBundle\Helper\ErrorHelper;

use Oleg\UserdirectoryBundle\Controller\ListController;

/**
 * Common list controller
 * @Route("/admin/list")
 */
class ScanListController extends ListController
{

    /**
     * Lists all entities.
     *
     * @Route("/research-project-titles/", name="researchprojecttitles-list")
     * @Route("/research-set-titles/", name="researchsettitles-list")
     * @Route("/educational-course-titles/", name="educationalcoursetitles-list")
     * @Route("/educational-lesson-titles/", name="educationallessontitles-list")
     * @Route("/principal-investigators/", name="principalinvestigators-list")
     * @Route("/course-directors/", name="coursedirectors-list")
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
     * @Route("/message-categories/", name="messagecategorys-list")
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
     * @Route("/sex/", name="sexes-list")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->getList($request);
    }

    /**
     * Creates a new entity.
     *
     * @Route("/research-project-titles/", name="researchprojecttitles_create")
     * @Route("/research-set-titles/", name="researchsettitles_create")
     * @Route("/educational-course-titles/", name="educationalcoursetitles_create")
     * @Route("/educational-lesson-titles/", name="educationallessontitles_create")
     * @Route("/principal-investigators/", name="principalinvestigators_create")
     * @Route("/course-directors/", name="coursedirectors_create")
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
     * @Route("/sex/", name="sexes_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:ListForm:new.html.twig")
     */
    public function createAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->createList($request);
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/research-project-titles/new", name="researchprojecttitles_new")
     * @Route("/research-set-titles/new", name="researchsettitles_new")
     * @Route("/educational-course-titles/new", name="educationalcoursetitles_new")
     * @Route("/educational-lesson-titles/new", name="educationallessontitles_new")
     * @Route("/principal-investigators/new", name="principalinvestigators_new")
     * @Route("/course-directors/new", name="coursedirectors_new")
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
     * @Route("/sex/new", name="sexes_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:new.html.twig")
     */
    public function newAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->newList($request);
    }

    /**
     * Finds and displays a entity.
     *
     * @Route("/research-project-titles/{id}", name="researchprojecttitles_show")
     * @Route("/research-set-titles/{id}", name="researchsettitles_show")
     * @Route("/educational-course-titles/{id}", name="educationalcoursetitles_show")
     * @Route("/educational-lesson-titles/{id}", name="educationallessontitles_show")
     * @Route("/principal-investigators/{id}", name="principalinvestigators_show")
     * @Route("/course-directors/{id}", name="coursedirectors_show")
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
     * @Route("/message-categories/{id}", name="messagecategorys_show")
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
     * @Route("/sex/{id}", name="sexes_show")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:show.html.twig")
     */
    public function showAction(Request $request,$id)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->showList($request,$id);
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/research-project-titles/{id}/edit", name="researchprojecttitles_edit")
     * @Route("/research-set-titles/{id}/edit", name="researchsettitles_edit")
     * @Route("/educational-course-titles/{id}/edit", name="educationalcoursetitles_edit")
     * @Route("/educational-lesson-titles/{id}/edit", name="educationallessontitles_edit")
     * @Route("/principal-investigators/{id}/edit", name="principalinvestigators_edit")
     * @Route("/course-directors/{id}/edit", name="coursedirectors_edit")
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
     * @Route("/sex/{id}/edit", name="sexes_edit")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->editList($request,$id);
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/research-project-titles/{id}", name="researchprojecttitles_update")
     * @Route("/research-set-titles/{id}", name="researchsettitles_update")
     * @Route("/educational-course-titles/{id}", name="educationalcoursetitles_update")
     * @Route("/educational-lesson-titles/{id}", name="educationallessontitles_update")
     * @Route("/principal-investigators/{id}", name="principalinvestigators_update")
     * @Route("/course-directors/{id}", name="coursedirectors_update")
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
     * @Route("/sex/{id}", name="sexes_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:ListForm:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->updateList($request, $id);
    }


    public function classListMapper( $route ) {

        $classPath = "Oleg\\OrderformBundle\\Entity\\";
        $bundleName = "OlegOrderformBundle";

        switch( $route ) {

        case "researchprojecttitles":
            $className = "projecttitlelist";
            $displayName = "Project Titles";
            break;
        case "researchsettitles":
            $className = "settitlelist";
            $displayName = "Set Titles";
            break;
        case "educationalcoursetitles":
            $className = "CourseTitleList";
            $displayName = "Course Titles";
            break;
        case "educationallessontitles":
            $className = "LessonTitleList";
            $displayName = "Lesson Titles";
            break;
        case "principalinvestigators":
            $className = "PIList";
            $displayName = "Principal Investigators";
            break;
        case "coursedirectors":
            $className = "DirectorList";
            $displayName = "Course Directors";
            break;
        case "mrntype":
            $className = "mrntype";
            $displayName = "MRN Types";
            break;
        case "accessiontype":
            $className = "accessiontype";
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
            $className = "stainlist";
            $displayName = "Stains";
            break;
        case "organ":
            $className = "organlist";
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
            $className = "slidetype";
            $displayName = "Slide Types";
            break;
        case "messagecategorys":
            $className = "MessageCategory";
            $displayName = "Message categories";
            break;
        case "status":
            $className = "status";
            $displayName = "Statuses";
            break;
        case "orderdelivery":
            $className = "OrderDelivery";
            $displayName = "Scan Order Delivery Options";
            break;
        case "regiontoscan":
            $className = "regiontoscan";
            $displayName = '"Region To Scan" Options';
            break;
        case "processorcomment":
            $className = "processorcomments";
            $displayName = "Processor Comments";
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
            $displayName = "Event Log Types";
            $classPath = "Oleg\\UserdirectoryBundle\\Entity\\";
            $bundleName = "OlegUserdirectoryBundle";
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
            $className = "InstructionList";
            $displayName = "Instructions for Embedder";
            break;
        case "patienttype":
            $className = "PatientTypeList";
            $displayName = "Patient Types";
            break;
        case "sexes":
            $className = "SexList";
            $displayName = "Sex";
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

        return $res;
    }

    /////////////////// DELETE IS NOT USED /////////////////////////
    /**
     * Deletes a entity.
     *
     * @Route("/research-project-titles/{id}", name="researchprojecttitles_delete")
     * @Route("/research-set-titles/{id}", name="researchsettitles_delete")
     * @Route("/educational-course-titles/{id}", name="educationalcoursetitles_delete")
     * @Route("/educational-lesson-titles/{id}", name="educationallessontitles_delete")
     * @Route("/principal-investigators/{id}", name="principalinvestigators_delete")
     * @Route("/course-directors/{id}", name="coursedirectors_delete")
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
     * @Route("/sex/{id}", name="sexes_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        //return $this->deleteList($request, $id);
    }
    /////////////////// DELETE IS NOT USED /////////////////////////

}
