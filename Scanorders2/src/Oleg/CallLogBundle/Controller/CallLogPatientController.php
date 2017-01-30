<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 8/30/2016
 * Time: 12:19 PM
 */

namespace Oleg\CallLogBundle\Controller;


use Oleg\CallLogBundle\Form\CalllogListPreviousEntriesFilterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Controller\PatientController;
use Symfony\Component\HttpFoundation\Response;


/**
 * CallLog Patient controller.
 *
 * @Route("/patient")
 */
class CallLogPatientController extends PatientController {

    /**
     * Finds and displays a Patient entity.
     *
     * @Route("/info/{id}", name="calllog_patient_show", options={"expose"=true})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function showAction( Request $request, $id )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $params = array(
            'sitename' => $this->container->getParameter('calllog.sitename'),
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'editpath' => 'calllog_patient_edit'
        );

        return $this->showPatient($request,$id,$params);
    }


    /**
     * Displays a form to edit an existing Patient entity.
     *
     * @Route("/{id}/edit", name="calllog_patient_edit", options={"expose"=true})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function editAction( Request $request, $id )
    {
        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }


        $params = array(
            'sitename' => $this->container->getParameter('calllog.sitename'),
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'updatepath' => 'calllog_patient_update',
            'showPlus' => 'showPlus'
        );

        $formResArr = $this->editPatient($request,$id,$params);

        $formResArr['title'] = $formResArr['title'] . " | Call Log Book";

        return $formResArr;
    }

    /**
     * Displays a form to edit an existing Patient entity.
     *
     * @Route("/edit-by-mrn/{mrn}/{mrntype}", name="calllog_patient_edit_by_mrn", options={"expose"=true})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function editPatientByMrnAction( Request $request, $mrn, $mrntype )
    {
        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $userSecUtil = $this->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $extra = array();
        $extra["keytype"] = $mrntype;
        $validity = array('valid','reserved');
        $single = false;

        //$institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName("All Institutions");
        //$institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName("Weill Cornell Medical College");
        //$institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName("New York-Presbyterian Hospital");
        $institution = $userSecUtil->getCurrentUserInstitution($user);
        $institutions = array();
        $institutions[] = $institution->getId();

        $patients = $em->getRepository('OlegOrderformBundle:Patient')->findOneByIdJoinedToField($institutions,$mrn,"Patient","mrn",$validity,$single,$extra);
        //echo "found patient=".$entity."<br>";
        //exit("edit patient by mrn $mrn $mrntype");
        //$patients = $em->getRepository('OlegOrderformBundle:Patient')->findAll(); //testing

        if( count($patients) > 1 ) {
            $patient = null;
            $patientArr = array();
            foreach( $patients as $thisPatient ) {
                if( $thisPatient->obtainValidKeyfield() ) {
                    //we should return a single result, but we got multiple entity, so return the first valid key one.
                    $patient = $thisPatient;
                }
                $patientArr[] = $patient->obtainPatientInfoSimple();
            }
            if( !$patient ) {
                $patient = $patients[0];
            }
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                'Multiple patients found with mrn ' . $mrn . ". Displayed is the first patient with a valid mrn. Found " . count($patients) . " patients: <hr>" . implode("<hr>",$patientArr)
            );
        }

        if( count($patients) == 1 ) {
            $patient = $patients[0];
        }

        if( !$patient || !$patient->getId() ) {
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                'No patient found with mrn ' . $mrn
            );
            return $this->redirect($this->generateUrl('calllog_home'));
        }

//        $this->get('session')->getFlashBag()->add(
//            'pnotify',
//            'Ok!'
//        );

        $params = array(
            'sitename' => $this->container->getParameter('calllog.sitename'),
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'updatepath' => 'calllog_patient_update',
            'showPlus' => 'showPlus'
        );

        return $this->editPatient($request,$patient->getId(),$params);
    }

    /**
     * Edits an existing Patient entity.
     *
     * @Route("/{id}/edit", name="calllog_patient_update", options={"expose"=true})
     * @Method("POST")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function updateAction( Request $request, $id )
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        ) {
            return $this->redirect($this->generateUrl('scan-nopermission'));
        }

        $params = array(
            'sitename' => $this->container->getParameter('calllog.sitename'),
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'updatepath' => 'calllog_patient_update',
            'showpath' => 'calllog_patient_show',
        );

        return $this->updatePatient($request,$id,$params);  //$datastructure,$showpath,$updatepath);
    }


    /**
     * Complex Patient List
     * @Route("/patient-list/{listid}/{listname}", name="calllog_complex_patient_list")
     * @Template("OlegCallLogBundle:PatientList:complex-patient-list.html.twig")
     */
    public function complexPatientListAction(Request $request, $listid, $listname)
    {
        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$listname
        $listnameArr = explode('-',$listname);
        $listname = implode(' ',$listnameArr);
        $listname = ucwords($listname);
        //echo "list: name=$listname; id=$listid <br>";

        //get list name by $listname, convert it to the first char as Upper case and use it to find the list in DB
        //for now use the mock page complex-patient-list.html.twig

        //get list by id
        //$patientList = $em->getRepository('OlegOrderformBundle:PatientListHierarchy')->find($listid);
        //$patients = $patientList->getChildren();

        $patientGroup = $em->getRepository('OlegOrderformBundle:PatientListHierarchyGroupType')->findOneByName('Patient');

        $parameters = array();

        $repository = $em->getRepository('OlegOrderformBundle:PatientListHierarchy');
        $dql = $repository->createQueryBuilder("list");

        $dql->leftJoin("list.patient", "patient");
        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");
        $dql->leftJoin("patient.mrn", "mrn");

        $dql->where("list.parent = :parentId AND list.organizationalGroupType = :patientGroup");
        $parameters['parentId'] = $listid;
        $parameters['patientGroup'] = $patientGroup->getId();

        $dql->andWhere("list.type = 'user-added' OR list.type = 'default'");

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        //echo "sql=".$query->getSql()."<br>";

        $limit = 30;
        $paginator  = $this->get('knp_paginator');
        $patients = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            //$request->query->getInt('page', 1),
            $limit      /*limit per page*/
        );
        //$patients = $query->getResult();

        //echo "patients=".count($patients)."<br>";

        $patientListHierarchyObject = $em->getRepository('OlegUserdirectoryBundle:PlatformListManagerRootList')->findOneByName('Patient List Hierarchy');

        //src/Oleg/CallLogBundle/Resources/views/PatientList/complex-patient-list.html.twig
        return array(
            'patientListId' => $listid,
            'patientNodes' => $patients,
            'title' => $listname,   //"Complex Patient List",
            'platformListManagerRootListId' => $patientListHierarchyObject->getId()
        );
    }

    /**
     * @Route("/remove-patient-from-list/{patientId}/{patientListId}", name="calllog_remove_patient_from_list")
     */
    public function removePatientFromListAction(Request $request, $patientId, $patientListId) {
        if (false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $patientList = $em->getRepository('OlegOrderformBundle:PatientListHierarchy')->find($patientListId);
        if( !$patientList ) {
            throw new \Exception( "PatientListHierarchy not found by id $patientListId" );
        }

        //remove patient from the list
        $repository = $em->getRepository('OlegOrderformBundle:PatientListHierarchy');
        $dql = $repository->createQueryBuilder("list");

        $dql->leftJoin("list.patient", "patient");

        $dql->where("patient = :patientId");
        $parameters['patientId'] = $patientId;

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        $patients = $query->getResult();

        $msgArr = array();
        foreach( $patients as $patientNode ) {
            $patientNode->setType('disabled');
            //TODO: remove this patient from all CalllogEntryMessage (addPatientToList, patientList): find all message with this patient where addPatientToList is true and set to false?
            $msgArr[] = $patientNode->getPatient()->obtainPatientInfoTitle();
        }
        $em->flush();

        $msg = implode('<br>',$msgArr);
        if( $msg ) {
            $msg = "Removed patient:<br>" . $msg;
        }

        $this->get('session')->getFlashBag()->add(
            'pnotify',
            $msg
        );

        $listName = $patientList->getName()."";
        $listNameLowerCase = str_replace(" ","-",$listName);
        $listNameLowerCase = strtolower($listNameLowerCase);

        return $this->redirect($this->generateUrl('calllog_complex_patient_list',array('listname'=>$listNameLowerCase,'listid'=>$patientListId)));
    }



    /**
     * @Route("/add-patient-to-list/{patientListId}/{patientId}", name="calllog_add_patient_to_list")
     * @Template("OlegCallLogBundle:PatientList:complex-patient-list.html.twig")
     */
    public function addPatientToListAction(Request $request, $patientListId, $patientId) {
        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $calllogUtil = $this->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

        $patientList = $em->getRepository('OlegOrderformBundle:PatientListHierarchy')->find($patientListId);
        if( !$patientList ) {
            throw new \Exception( "PatientListHierarchy not found by id $patientListId" );
        }

        //add patient from the list
        $patient = $em->getRepository('OlegOrderformBundle:Patient')->find($patientId);
        if( !$patient ) {
            throw new \Exception( "Patient not found by id $patientId" );
        }

        $newListElement = $calllogUtil->addPatientToPatientList($patient,$patientList);

        //Patient added to the Pathology Call Complex Patients list
        $msg = "Patient " . $newListElement->getPatient()->obtainPatientInfoTitle() . " added to the ".$patientList->getName()." list";

        $this->get('session')->getFlashBag()->add(
            'pnotify',
            $msg
        );

        $listName = $patientList->getName()."";
        $listNameLowerCase = str_replace(" ","-",$listName);
        $listNameLowerCase = strtolower($listNameLowerCase);

        return $this->redirect($this->generateUrl('calllog_complex_patient_list',array('listname'=>$listNameLowerCase,'listid'=>$patientListId)));
    }


    //calllog-list-previous-entries
    /**
     * @Route("/list-previous-entries/", name="calllog-list-previous-entries", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function showOnlyAjaxUserAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USER') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $calllogUtil = $this->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

        $template = null;
        $filterMessageCategory = null;

        $patientid = $request->query->get('patientid');

        $messageCategoryId = $request->query->get('type');
        //if ( strval($messageCategoryId) != strval(intval($messageCategoryId)) ) {
            //echo "Your variable is not an integer";
            //$messageCategoryId = null;
        //} else {
            //$filterMessageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->find($messageCategoryId);
            //echo "filter=".$filterMessageCategory."<br>";
        //}
        if( !$messageCategoryId || $messageCategoryId == "null" || $messageCategoryId == "undefined" ) {
            $messageCategoryId = null;
        }

        //echo "patientid=".$patientid."<br>";
        //echo "messageCategory=".$messageCategory."<br>";

        $testing = $request->query->get('testing');

        //$showUserArr = $this->showUser($userid,$this->container->getParameter('employees.sitename'),false);
        //$template = $this->render('OlegUserdirectoryBundle:Profile:edit_user_only.html.twig',$showUserArr)->getContent();

        //child nodes of "Pathology Call Log Entry"
        $messageCategoriePathCall = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName("Pathology Call Log Entry");
        $messageCategories = array();
        if( $messageCategoriePathCall ) {
            $messageCategories = $messageCategoriePathCall->printTreeSelectList();
        }
        //print_r($messageCategories);

        $params = array(
            'messageCategory' => $messageCategoryId,
            'messageCategories' => $messageCategories
        );
        $filterform = $this->createForm(new CalllogListPreviousEntriesFilterType($params), null);
        $filterform->bind($request);

        //////////////// find messages ////////////////
        $queryParameters = array();
        $repository = $em->getRepository('OlegOrderformBundle:Message');
        $dql = $repository->createQueryBuilder('message');

        $dql->leftJoin("message.messageStatus","messageStatus");
        $dql->leftJoin("message.messageCategory","messageCategory");
        $dql->leftJoin("message.provider","provider");
        $dql->leftJoin("message.patient","patient");
        $dql->leftJoin("message.editorInfos","editorInfos");

        $dql->leftJoin("message.signeeInfo","signeeInfo");
        $dql->leftJoin("signeeInfo.modifiedBy","signee");

        $dql->leftJoin("message.encounter","encounter");
        $dql->leftJoin("encounter.referringProviders","referringProviders");
        $dql->leftJoin("referringProviders.field","referringProviderWrapper");
        $dql->leftJoin("encounter.attendingPhysicians","attendingPhysicians");
        $dql->leftJoin("attendingPhysicians.field","attendingPhysicianWrapper");

        $dql->orderBy("message.orderdate","DESC");
        $dql->addOrderBy("editorInfos.modifiedOn","DESC");

        $dql->where("patient=:patientId");
        $queryParameters['patientId'] = $patientid;

        if( $messageCategoryId ) {
            $dql->andWhere("messageCategory.name=:messageCategoryId");
            $queryParameters['messageCategoryId'] = $messageCategoryId;
        }

        //TODO: Show only the most recent version for each message (if a message has been edited/amended 5 times, show only the message with message version "6").

        //TODO: 7- If the entered patient is linked to another AND is NOT the master patient record,
        // change the title of the accordion to
        // "Previous Entries for FirstNameOfMasterRecord LastNameOfMasterRecord (DOB: DateOfBirthOfMasterRecord, MRNTypeOfMasterRecord: MRNofMasterRecord).
        // Clicking "Re-enter patient" in the Patient Info accordion should re-set the title of the accordion to "Previous Entries" (remove the patient name/info).

        $limit = 1000;
        $query = $em->createQuery($dql);
        $query->setParameters($queryParameters);

        //echo "query=".$query->getSql()."<br>";

//        $paginator  = $this->get('knp_paginator');
//        $messages = $paginator->paginate(
//            $query,
//            $this->get('request')->query->get('page', 1), /*page number*/
//            //$request->query->getInt('page', 1),
//            $limit      /*limit per page*/
//        );

        $messages = $query->getResult();
        //////////////// find messages ////////////////

        $params = array(
            'filterform' => $filterform->createView(),
            'route_path' => $request->get('_route'),
            'messages' => $messages,
            'title' => "Previous Entries"
            //'testing' => true
        );
        $htmlPage = $this->render('OlegCallLogBundle:PatientList:patient_entries.html.twig',$params);

        //testing
        if( $testing ) {
            return $htmlPage;
        }

        $template = $htmlPage->getContent();

        $json = json_encode($template);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}