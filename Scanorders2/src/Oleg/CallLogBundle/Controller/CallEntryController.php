<?php

namespace Oleg\CallLogBundle\Controller;

use Oleg\CallLogBundle\Form\PatientType;
use Oleg\OrderformBundle\Entity\Encounter;
use Oleg\OrderformBundle\Entity\Patient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CallEntryController extends Controller
{

    /**
     * Case List Page
     * @Route("/", name="calllog_home")
     *
     * Alerts: filtered case list
     * @Route("/alerts/", name="calllog_alerts")
     *
     * @Template("OlegCallLogBundle:CallLog:home.html.twig")
     */
    public function homeAction(Request $request)
    {

        $title = "Call Case List";
        $alerts = false;

        if( $request->get('_route') == "calllog_alerts" ) {
            $alerts = true;
            $title = $title . " (Alerts)";
        }

        return array(
            //'entity' => $entity,
            //'form' => $form->createView(),
            //'cycle' => $cycle,
            'alerts' => $alerts,
            'title' => $title,
        );

    }



    /**
     * Call Entry
     * @Route("/entry/new", name="calllog_callentry")
     * @Template("OlegCallLogBundle:CallLog:call-entry.html.twig")
     */
    public function callEntryAction(Request $request)
    {
        //1) search box: MRN,Name...

        $user = $this->get('security.context')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $em = $this->getDoctrine()->getManager();

        $title = "Call Entry";

        $system = $securityUtil->getDefaultSourceSystem(); //'scanorder';
        $status = 'valid';
        $cycle = 'new';

        $patient = new Patient(true,$status,$user,$system);

        $encounter = new Encounter(true,$status,$user,$system);
        $patient->addEncounter($encounter);


        $form = $this->createPatientForm($patient);

        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
        );
    }

    public function createPatientForm($patient) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $params = array(
            'cycle' => 'new',
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
        );

        $form = $this->createForm(new PatientType($params,$patient), $patient);

        return $form;
    }

    /**
     * Search Call Entry
     * @Route("/callentry/search", name="calllog_search_callentry")
     * @Method("GET")
     * @Template()
     */
    public function searchCallEntryAction(Request $request)
    {
        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $entities = null;

        $allgets = $request->query->all();;
        //$patientid = trim( $request->get('patientid') );
        //print_r($allgets);
        //echo "<br>";

        $searchtype = null;
        $search = null;

        foreach( $allgets as $thiskey => $thisvalue ) {
            $searchtype = $thiskey;
            $search = $thisvalue;
            break;
        }

        $searchtype = str_replace("_"," ",$searchtype);

        //$searchtype = trim( $request->get('searchtype') );
        //$search = trim( $request->get('search') );
        //echo "searchtype=".$searchtype."<br>";
        //echo "search=".$search."<br>";

        if( $searchtype != "" && $search != "" ) {

//            $searchUtil = $this->get('search_utility');
//            $object = 'patient';
//            $params = array('request'=>$request,'object'=>$object,'searchtype'=>$searchtype,'search'=>$search,'exactmatch'=>false);
//            $res = $searchUtil->searchAction($params);
//            $entities = $res[$object];
            $entities = null;
        }


        //echo "entities count=".count($entities)."<br>";

        return $this->render('OlegCallLogBundle:CallLog:home.html.twig', array(
            'patientsearch' => $search,
            'patientsearchtype' => $searchtype,
            'patiententities' => $entities,
        ));
    }

    /**
     * Search Patient
     * @Route("/patient/search", name="calllog_search_patient", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function searchPatientAction(Request $request)
    {
        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $entities = null;

        //$allgets = $request->query->all();;
        $mrn = trim( $request->get('mrn') );
        $mrntype = trim( $request->get('mrntype') );
        $dob = trim( $request->get('dob') );
        $lastname = trim( $request->get('lastname') );
        $firstname = trim( $request->get('firstname') );
        //print_r($allgets);
        //echo "mrn=".$mrn."<br>";

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Patient', 'patient')
            ->select("patient")
            ->leftJoin("patient.mrn", "mrn")
            ->leftJoin("patient.dob", "dob")
            ->leftJoin("patient.lastname", "lastname")
            ->leftJoin("patient.firstname", "firstname")
            ->where("mrn.keytype = :keytype AND mrn.field = :mrn AND mrn.status = :status")
            ->setParameters( array('keytype'=>$mrntype, 'mrn'=>$mrn, 'status'=>'valid') )
            ->getQuery();

        $patients = $query->getResult();
        //echo "patients=".count($patients)."<br>";

        $patientsArr = array();
        $status = 'valid';

        foreach( $patients as $patient ) {

//            //to get a single field only use obtainStatusField
//            //obtainStatusFieldArray - get array of fields
//            $mrnArr = $patient->obtainStatusFieldArray('mrn', $status);
//            $dobArr = $patient->obtainStatusFieldArray('dob', $status);
//            $firstNameArr = $patient->obtainStatusFieldArray('firstname', $status);
//            $middleNameArr = $patient->obtainStatusFieldArray('middlename', $status);
//            $lastNameArr = $patient->obtainStatusFieldArray('lastname', $status);
//            $suffixArr = $patient->obtainStatusFieldArray('suffix', $status);
//            $sexArr = $patient->obtainStatusFieldArray('sex', $status);
//
//            if( count($mrnArr) > 0 && $mrnArr[0] ) {
//                $mrntypeRes = $mrnArr[0]->getKeytype()->getId();
//                $mrnRes = $mrnArr[0]->getField();
//            }
//
//            if( count($dobArr) > 0 && $dobArr[0] ) {
//                $dobRes = $dobArr[0]."";
//            }
//
//            if( count($firstNameArr) > 0 && $firstNameArr[0] ) {
//                $firstnameRes = $firstNameArr[0]->getField();
//            }
//
//            if( count($lastNameArr) > 0 && $lastNameArr[0] ) {
//                $lastnameRes = $lastNameArr[0]->getField();
//            }
//
//            if( count($middleNameArr) > 0 && $middleNameArr[0] ) {
//                $middlenameRes = $middleNameArr[0]->getField();
//            }
//
//            if( count($suffixArr) > 0 && $suffixArr[0] ) {
//                $suffixRes = $suffixArr[0]->getField();
//            }
//
//            if( count($sexArr) > 0 && $sexArr[0] ) {
//                $sexRes = $sexArr[0]->getId();
//            }

//            if( $dobRes ) {
//                $patientInfo = array(
//                    'id' => $patient->getId(),
//                    'mrntype' => $mrntypeRes,
//                    'mrn' => $mrnRes,
//                    'dob' => $dobRes,
//                    'lastname' => $lastnameRes,
//                    'firstname' => $firstnameRes,
//                    'middlename' => $middlenameRes,
//                    'suffix' => $suffixRes,
//                    'sex' => $sexRes,
//                );
//                $output[] = $patientInfo;
//            }//if

            //to get a single field only use obtainStatusField
            $mrnRes = $patient->obtainStatusField('mrn', $status);
            $dobRes = $patient->obtainStatusField('dob', $status);
            $firstNameRes = $patient->obtainStatusField('firstname', $status);
            $middleNameRes = $patient->obtainStatusField('middlename', $status);
            $lastNameRes = $patient->obtainStatusField('lastname', $status);
            $suffixRes = $patient->obtainStatusField('suffix', $status);
            $sexRes = $patient->obtainStatusField('sex', $status);

            $patientInfo = array(
                'id' => $patient->getId(),
                'mrntype' => $mrnRes->getKeytype()->getId(),
                'mrn' => $mrnRes->getField(),
                'dob' => $dobRes."",
                'lastname' => $lastNameRes->getField(),
                'firstname' => $firstNameRes->getField(),
                'middlename' => $middleNameRes->getField(),
                'suffix' => $suffixRes->getField(),
                'sex' => $sexRes->getId(),
            );
            $patientsArr[] = $patientInfo;

        }//foreach

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($patientsArr));
        return $response;
    }

}
