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
        if (false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        //$allgets = $request->query->all();;
//        $mrn = trim($request->get('mrn'));
//        $mrntype = trim($request->get('mrntype'));
//        $dob = trim($request->get('dob'));
//        $lastname = trim($request->get('lastname'));
//        $firstname = trim($request->get('firstname'));
//        //print_r($allgets);
//        //echo "mrn=".$mrn."<br>";

//        $em = $this->getDoctrine()->getManager();
//
//        $parameters = array('status' => 'valid');
//
//        $repository = $em->getRepository('OlegOrderformBundle:Patient');
//        $dql = $repository->createQueryBuilder("patient");
//        $dql->leftJoin("patient.mrn", "mrn");
//        $dql->leftJoin("patient.dob", "dob");
//        $dql->leftJoin("patient.lastname", "lastname");
//        $dql->leftJoin("patient.firstname", "firstname");
//
//        $dql->where("mrn.status = :status");
//
//        $where = false;
//
//        //mrn
//        if( $mrntype && $mrn ) {
//            $dql->andWhere("mrn.keytype = :keytype AND mrn.field = :mrn");
//            $parameters['keytype'] = $mrntype;
//            $parameters['mrn'] = $mrn;
//            $where = true;
//        }
//
//        //Last Name AND DOB
//        if( $where == false && $lastname && $dob ) {
//            $dobDateTime = \DateTime::createFromFormat('m/d/Y', $dob)->format('Y-m-d');
//            //echo "dob=".$dob." => ".$dobDateTime."<br>";
//            $dql->andWhere("lastname.field = :lastname AND dob.field = :dob");
//            $parameters['lastname'] = $lastname;
//            $parameters['dob'] = $dobDateTime;
//            $where = true;
//        }
//
////        //firstname, Last Name AND DOB
////        if( $lastname && $firstname && $dob ) {
////            $dql->andWhere("lastname.field = :lastname AND firstname.field = :firstname");
////            $parameters['lastname'] = $lastname;
////            $parameters['firstname'] = $firstname;
////            $where = true;
////        }
//
//        if( $where ) {
//            $query = $em->createQuery($dql);
//            $query->setParameters($parameters);
//            //echo "sql=".$query->getSql()."<br>";
//            $patients = $query->getResult();
//        } else {
//            $patients = array();
//        }

        $patients = $this->searchPatient( $request );
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
                'mrntypestr' => $mrnRes->getKeytype()->getName(),
                'mrn' => $mrnRes->getField(),
                'dob' => $dobRes."",
                'lastname' => $lastNameRes->getField(),
                'firstname' => $firstNameRes->getField(),
                'middlename' => $middleNameRes->getField(),
                'suffix' => $suffixRes->getField(),
                'sex' => $sexRes->getId(),
                'sexstr' => $sexRes."",
            );
            $patientsArr[] = $patientInfo;

        }//foreach

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($patientsArr));
        return $response;
    }

    public function searchPatient( $request, $params=null ) {

        $mrn = trim($request->get('mrn'));
        $mrntype = trim($request->get('mrntype'));
        $dob = trim($request->get('dob'));
        $lastname = trim($request->get('lastname'));
        $firstname = trim($request->get('firstname'));
        //print_r($allgets);
        //echo "mrn=".$mrn."<br>";

        if( $params ) {
            $mrntype = ( array_key_exists('mrntype', $params) ? $params['mrntype'] : null);
            $mrn = ( array_key_exists('mrn', $params) ? $params['mrn'] : null);
            $dob = ( array_key_exists('dob', $params) ? $params['dob'] : null);
            $lastname = ( array_key_exists('lastname', $params) ? $params['lastname'] : null);
            $firstname = ( array_key_exists('firstname', $params) ? $params['firstname'] : null);
        }

        $em = $this->getDoctrine()->getManager();

        $parameters = array('status' => 'valid');

        $repository = $em->getRepository('OlegOrderformBundle:Patient');
        $dql = $repository->createQueryBuilder("patient");
        $dql->leftJoin("patient.mrn", "mrn");
        $dql->leftJoin("patient.dob", "dob");
        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");

        $dql->where("mrn.status = :status");

        $where = false;

        //mrn
        if( $mrntype && $mrn ) {
            $dql->andWhere("mrn.keytype = :keytype AND mrn.field = :mrn");
            $parameters['keytype'] = $mrntype;
            $parameters['mrn'] = $mrn;
            $where = true;
        }

        //Last Name AND DOB
        if( $where == false && $lastname && $dob ) {
            $dobDateTime = \DateTime::createFromFormat('m/d/Y', $dob)->format('Y-m-d');
            //echo "dob=".$dob." => ".$dobDateTime."<br>";
            $dql->andWhere("lastname.field = :lastname AND dob.field = :dob");
            $parameters['lastname'] = $lastname;
            $parameters['dob'] = $dobDateTime;
            $where = true;
        }

//        //firstname, Last Name AND DOB
//        if( $lastname && $firstname && $dob ) {
//            $dql->andWhere("lastname.field = :lastname AND firstname.field = :firstname");
//            $parameters['lastname'] = $lastname;
//            $parameters['firstname'] = $firstname;
//            $where = true;
//        }

        if( $where ) {
            $query = $em->createQuery($dql);
            $query->setParameters($parameters);
            //echo "sql=".$query->getSql()."<br>";
            $patients = $query->getResult();
        } else {
            $patients = array();
        }

        return $patients;
    }


    /**
     * Create a new Patient
     * @Route("/patient/create", name="calllog_create_patient", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function createPatientAction(Request $request)
    {
        //TODO: The server should DOUBLECHECK that the user has a role with a permission of "Create Patient Record"
        if (false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $mrn = trim($request->get('mrn'));
        $mrntype = trim($request->get('mrntype'));
        $dob = trim($request->get('dob'));
        $lastname = trim($request->get('lastname'));
        $firstname = trim($request->get('firstname'));
        $middlename = trim($request->get('middlename'));
        $suffix = trim($request->get('suffix'));
        $sex = trim($request->get('sex'));
        //print_r($allgets);
        //echo "mrn=".$mrn."<br>";

        $output = 'OK';
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        //first check if the patient already exists
        $patients = $this->searchPatient( $request );
        if( count($patients) > 0 ) {
            $output = "Can not create a new Patient. The patient with specified parameters already exists:<br>";

            if( $mrntype ) {
                $mrntypeObj = $em->getRepository('OlegOrderformBundle:MrnType')->findOneById( $mrntype );
                $output .= "MRN Type:".$mrntypeObj."<br>";
            }
            if( $mrn )
                $output .= "MRN:".$mrn."<br>";
            if( $lastname )
                $output .= "Last Name:".$lastname."<br>";
            //if( $firstname )
            //    $output .= "First Name:".$firstname."<br>";
            if( $dob )
                $output .= "DOB:".$dob."<br>";

            $response->setContent(json_encode($output));
            return $response;
        }

        //Create a new Patient
        $user = $this->get('security.context')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $sourcesystem = $securityUtil->getDefaultSourceSystem(); //'scanorder';
        $status = 'valid';

        $patient = new Patient(false,$status,$user,$sourcesystem);
        $patient->addMrn( new PatientMrn($status,$user,$sourcesystem) );
        $patient->addDob( new PatientDob($status,$user,$sourcesystem) );

        $encounter = new Encounter(false,$status,$user,$sourcesystem);
        $patient->addEncounter($encounter);


        $response->setContent(json_encode($output));
        return $response;
    }
}
