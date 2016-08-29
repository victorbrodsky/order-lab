<?php

namespace Oleg\CallLogBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Oleg\CallLogBundle\Form\PatientType;
use Oleg\OrderformBundle\Entity\Encounter;
use Oleg\OrderformBundle\Entity\EncounterPatfirstname;
use Oleg\OrderformBundle\Entity\EncounterPatlastname;
use Oleg\OrderformBundle\Entity\EncounterPatmiddlename;
use Oleg\OrderformBundle\Entity\EncounterPatsex;
use Oleg\OrderformBundle\Entity\EncounterPatsuffix;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\PatientDob;
use Oleg\OrderformBundle\Entity\PatientFirstName;
use Oleg\OrderformBundle\Entity\PatientLastName;
use Oleg\OrderformBundle\Entity\PatientMiddleName;
use Oleg\OrderformBundle\Entity\PatientMrn;
use Oleg\OrderformBundle\Entity\PatientSex;
use Oleg\OrderformBundle\Entity\PatientSuffix;
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
        $formtype = 'call-entry';

        $patient = new Patient(true,$status,$user,$system);

        $encounter = new Encounter(true,$status,$user,$system);
        $patient->addEncounter($encounter);


        $form = $this->createPatientForm($patient);

        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'formtype' => $formtype
        );
    }

    public function createPatientForm($patient, $formparams=null) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $params = array(
            'cycle' => 'new',
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            //'alias' => true
            'type' => null
        );

        if( $formparams ) {
            $form = $this->createForm(new PatientType($params, $patient), $patient, $formparams);
        } else {
            $form = $this->createForm(new PatientType($params, $patient), $patient);
        }

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
    public function patientSearchAction(Request $request)
    {
        if (false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $calllogUtil = $this->get('calllog_util');

        //$currentUrl = trim($request->get('currentUrl'));
        //echo "currentUrl=".$currentUrl."<br>";

        $formtype = trim($request->get('formtype'));

        $patients = $this->searchPatient( $request, true );
        //echo "patients=".count($patients)."<br>";

        $patientsArr = array(); //return json data
        //$status = 'valid';
        //$fieldnameArr = array('patlastname','patfirstname','patmiddlename','patsuffix','patsex');

        foreach( $patients as $patient ) {

            $patientId = $patient->getId();
            //echo "<br>found patient=".$patient->getId()."<br>";

            //add all merged patients to the master patient
            $mergedPatients = $calllogUtil->getAllMergedPatients( array($patient) );
            //echo "mergedPatients count=" . count($mergedPatients) . "<br>";

//            foreach( $mergedPatients as $mergedPatient ) {
//                echo "merged Patient=" . $mergedPatient->getId() . "<br>";
//
//                if( $mergedPatient->isMasterMergeRecord() ) {
//                    $masterPatientId = $mergedPatient->getId();
//                    echo "master=" . $masterPatientId . "<br>";
//
//                }
//
//            }//foreach $mergedPatient
//            exit('1');

            $masterPatient = $calllogUtil->getMasterRecordPatients($mergedPatients);

            if( $masterPatient ) {

                $patientId = $masterPatient->getId();
                $masterPatientId = $masterPatient->getId();
                //echo "###masterPatientId=" . $masterPatientId . "<br>";

                $patientInfo = array();

                $mergedPatientsInfo = array();
                $mergedPatientsInfo[$masterPatientId] = array();

                foreach( $mergedPatients as $mergedPatient ) {
                    //echo "merged Patient=" . $mergedPatient->getId() . "<br>";

                    //first iteration: first create master record $patientInfo
                    if( $masterPatientId == $mergedPatient->getId() ) {
                        $patientInfo = $calllogUtil->getJsonEncodedPatient($mergedPatient);
                        continue;
                    }

                    //other iterations: add as merged patients to $patientInfo
                    $mergedPatientsInfo[$masterPatientId]['patientInfo'][$mergedPatient->getId()] = $calllogUtil->getJsonEncodedPatient($mergedPatient);
                    $mergedPatientsInfo[$masterPatientId]['patientInfo'][$mergedPatient->getId()]['masterPatientId'] = $masterPatientId;

                    //$mergedPatientsInfo[$masterPatientId]['mergeInfo'][$mergedPatient->getId()] = $mergedPatient->obtainMergeInfo();

                }//foreach $mergedPatient

                $patientInfo['masterPatientId'] = $masterPatientId;
                $patientInfo['mergedPatientsInfo'] = $mergedPatientsInfo;

//                //set Master Patient
//                $masterPatientIdOut = null;
//                if ($mergedPatient->isMasterMergeRecord() && $formtype == "call-entry") {
//                    $masterPatientIdOut = $masterPatientId;
//                }
//                $patientInfo['masterPatientId'] = $masterPatientIdOut;

            } else {
                //just display this patient
                $patientInfo = $calllogUtil->getJsonEncodedPatient($patient);
                //$patientsArr[] = $patientInfo;
            }

            $patientsArr[$patientId] = $patientInfo;
        }
        //exit('1');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($patientsArr));
        return $response;
    }

    public function searchPatient( $request, $evenlog=false, $params=null ) {

        $mrn = trim($request->get('mrn'));
        $mrntype = trim($request->get('mrntype'));
        $dob = trim($request->get('dob'));
        $lastname = trim($request->get('lastname'));
        $firstname = trim($request->get('firstname'));
        //print_r($allgets);
        //echo "mrn=".$mrn."<br>";

        $exactMatch = true;

        if( $params ) {
            $mrntype = ( array_key_exists('mrntype', $params) ? $params['mrntype'] : null);
            $mrn = ( array_key_exists('mrn', $params) ? $params['mrn'] : null);
            $dob = ( array_key_exists('dob', $params) ? $params['dob'] : null);
            $lastname = ( array_key_exists('lastname', $params) ? $params['lastname'] : null);
            //$firstname = ( array_key_exists('firstname', $params) ? $params['firstname'] : null);
        }

        $em = $this->getDoctrine()->getManager();

        $parameters = array();

        $repository = $em->getRepository('OlegOrderformBundle:Patient');
        $dql = $repository->createQueryBuilder("patient");
        $dql->leftJoin("patient.mrn", "mrn");
        $dql->leftJoin("patient.dob", "dob");
        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");
        $dql->leftJoin("patient.encounter", "encounter");
        $dql->leftJoin("encounter.patlastname", "encounterLastname");
        $dql->leftJoin("encounter.patfirstname", "encounterFirsttname");

        //$dql->where("mrn.status = :statusValid");

        $where = false;
        $searchBy = "unknown parameters";

        //mrn
        if( $mrntype && $mrn ) {
            $dql->andWhere("mrn.keytype = :keytype");
            $parameters['keytype'] = $mrntype;

            if( $exactMatch ) {
                $dql->andWhere("mrn.field = :mrn");
                $parameters['mrn'] = $mrn;
            } else {
                $dql->andWhere("mrn.field LIKE :mrn");
                $parameters['mrn'] = '%' . $mrn . '%';
            }

            $dql->andWhere("mrn.status = :statusValid OR mrn.status = :statusAlias");
            $parameters['statusValid'] = 'valid';
            $parameters['statusAlias'] = 'alias';

            $where = true;
            $searchBy = "mrntype=".$mrntype." and mrn=".$mrn;
        }

        //Last Name AND DOB
        if( $where == false && $lastname && $dob ) {
            $dobDateTime = \DateTime::createFromFormat('m/d/Y', $dob)->format('Y-m-d');
            //echo "dob=".$dob." => ".$dobDateTime."<br>";
//            $dql->andWhere("dob.field = :dob AND (lastname.field = :lastname OR encounterLastname.field = :lastname)");
//            $parameters['lastname'] = $lastname;
//            $parameters['dob'] = $dobDateTime;

            $dql->andWhere("dob.field = :dob");
            $parameters['dob'] = $dobDateTime;

            if( $exactMatch ) {
                $dql->andWhere("lastname.field = :lastname OR encounterLastname.field = :lastname");
                $parameters['lastname'] = $lastname;
            } else {
                $dql->andWhere("lastname.field LIKE :lastname OR encounterLastname.field LIKE :lastname");
                $parameters['lastname'] = '%' . $lastname . '%';
            }

            $dql->andWhere("dob.status = :statusValid OR dob.status = :statusAlias");
            $dql->andWhere("lastname.status = :statusValid OR lastname.status = :statusAlias");
            $dql->andWhere("encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias");
            $parameters['statusValid'] = 'valid';
            $parameters['statusAlias'] = 'alias';

            $searchBy = "dob=".$dob." and lastname=".$lastname;

            if( $firstname ) {
                $dql->andWhere("firstname.field = :firstname OR encounterFirsttname.field = :firstname");
                $dql->andWhere("encounterFirsttname.status = :statusValid OR encounterFirsttname.status = :statusAlias");
                $parameters['firstname'] = $firstname;

                $searchBy = " and firstname=".$firstname;
            }

            $where = true;
        }

        //Last Name only
        if( $where == false && $lastname ) {
            if( $exactMatch ) {
                $dql->andWhere("lastname.field = :lastname OR encounterLastname.field = :lastname");
                $parameters['lastname'] = $lastname;
            } else {
                $dql->andWhere("lastname.field LIKE :lastname OR encounterLastname.field LIKE :lastname");
                $parameters['lastname'] = '%' . $lastname . '%';
            }

            $dql->andWhere("lastname.status = :statusValid OR lastname.status = :statusAlias");
            $dql->andWhere("encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias");
            $parameters['statusValid'] = 'valid';
            $parameters['statusAlias'] = 'alias';

            $searchBy = "lastname=".$lastname;

            if( $firstname ) {
                $dql->andWhere("encounterFirsttname.status = :statusValid OR encounterFirsttname.status = :statusAlias");

                if( $exactMatch ) {
                    $dql->andWhere("firstname.field = :firstname OR encounterFirsttname.field = :firstname");
                    $parameters['firstname'] = $firstname;
                } else {
                    $dql->andWhere("firstname.field LIKE :firstname OR encounterFirsttname.field LIKE :firstname");
                    $parameters['firstname'] = '%' . $firstname . '%';
                }

                $searchBy = " and firstname=".$firstname;
            }

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

            //log search action
            if( $evenlog ) {
                if( count($patients) == 0 ) {
                    $patientEntities = null;
                } else {
                    $patientEntities = $patients;
                }
                $user = $this->get('security.context')->getToken()->getUser();
                $userSecUtil = $this->container->get('user_security_utility');
                $eventType = "Patient Searched";
                $event = "Patient searched by ".$searchBy;
                $event = $event . "; found ".count($patients)." patient(s).";
                $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'),$event,$user,$patientEntities,$request,$eventType);
            }

        } else {
            $patients = array();
        }

        //search for merged
        $calllogUtil = $this->get('calllog_util');

        //$patients = $calllogUtil->getAllMergedPatients( $patients );

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
        $user = $this->get('security.context')->getToken()->getUser();

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

        //TODO: set institution
        $securityUtil = $this->get('order_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        $institution = $userSiteSettings->getDefaultInstitution();

        if( $mrntype ) {
            $mrntypeObj = $em->getRepository('OlegOrderformBundle:MrnType')->findOneById( $mrntype );
        } else {
            $mrntypeObj = null;
        }

        //first check if the patient already exists
        $patients = $this->searchPatient( $request );
        if( count($patients) > 0 ) {
            $output = "Can not create a new Patient. The patient with specified parameters already exists:<br>";

            if( $mrntype ) {
                $output .= "MRN Type:".$mrntypeObj."<br>";
            }
            if( $mrn )
                $output .= "MRN:".$mrn."<br>";
            if( $lastname )
                $output .= "Last Name:".$lastname."<br>";
            if( $firstname )
                $output .= "First Name:".$firstname."<br>";
            if( $dob )
                $output .= "DOB:".$dob."<br>";

            $response->setContent(json_encode($output));
            return $response;
        }

        //Create a new Patient
        $securityUtil = $this->get('order_security_utility');
        $sourcesystem = $securityUtil->getDefaultSourceSystem(); //'scanorder';
        $status = 'valid';

        //$patient = new Patient(false,$status,$user,$sourcesystem);
        //$patient->setInstitution($institution);

        //create a new patient
        if( $mrn ) {
            $fieldValue = $mrn;
        } else {
            $fieldValue = null;
        }

        if( $mrntype ) {
            $keytype = $mrntype;
        } else {
            $keytypeEntity = $this->getDoctrine()->getRepository('OlegOrderformBundle:MrnType')->findOneByName("Auto-generated MRN");
            $keytype = $keytypeEntity->getId() . ""; //id of "New York Hospital MRN" in DB
        }

        $extra = array();
        $extra["keytype"] = $keytype;

        //echo "keytype=".$keytype."<br>";
        //exit();

        $createdWithArr = array();
        $createdWithArr[] = "MRN Type: ".$mrntypeObj;
        $createdWithArr[] = "MRN: ".$mrn;

        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository('OlegOrderformBundle:Patient')->createElement(
            $institution,
            $status,            //status
            $user,              //provider
            "Patient",          //$className
            "mrn",              //$fieldName
            null,               //$parent
            $fieldValue,        //$fieldValue
            $extra,             //$extra
            false               //$withfields
        );


        $patient->addDob( new PatientDob($status,$user,$sourcesystem) );
        if( $dob ) {
            $dobDateTime = \DateTime::createFromFormat('m/d/Y', $dob);
            $PatientDob = new PatientDob($status, $user, $sourcesystem);
            $PatientDob->setField($dobDateTime);
            $patient->addDob($PatientDob);
            $createdWithArr[] = "DOB: " . $dob;
        }

        //create an encounter for this new patient with the First Name, Last Name, Middle Name, Suffix, and sex (if any)
        $encounter = new Encounter(false,$status,$user,$sourcesystem);
        $encounter->setInstitution($institution);

        if( $lastname ) {
            $EncounterPatlastname = new EncounterPatlastname($status, $user, $sourcesystem);
            $EncounterPatlastname->setField($lastname);
            $encounter->addPatlastname($EncounterPatlastname);

            $PatientLastname = new PatientLastName($status,$user,$sourcesystem);
            $PatientLastname->setField($lastname);
            $patient->addLastname( $PatientLastname );

            $createdWithArr[] = "Last Name: " . $lastname;
        }

        if( $firstname ) {
            $EncounterPatfirstname = new EncounterPatfirstname($status, $user, $sourcesystem);
            $EncounterPatfirstname->setField($firstname);
            $encounter->addPatfirstname($EncounterPatfirstname);

            $PatientFirstname = new PatientFirstName($status,$user,$sourcesystem);
            $PatientFirstname->setField($firstname);
            $patient->addFirstname( $PatientFirstname );

            $createdWithArr[] = "First Name: " . $firstname;
        }

        if( $middlename ) {
            $EncounterPatmiddlename = new EncounterPatmiddlename($status, $user, $sourcesystem);
            $EncounterPatmiddlename->setField($middlename);
            $encounter->addPatmiddlename($EncounterPatmiddlename);

            $PatientMiddlename = new PatientMiddleName($status,$user,$sourcesystem);
            $PatientMiddlename->setField($middlename);
            $patient->addMiddlename( $PatientMiddlename );

            $createdWithArr[] = "Middle Name: " . $middlename;
        }

        if( $suffix ) {
            $EncounterPatsuffix = new EncounterPatsuffix($status, $user, $sourcesystem);
            $EncounterPatsuffix->setField($suffix);
            $encounter->addPatsuffix($EncounterPatsuffix);

            $PatientSuffix = new PatientSuffix($status,$user,$sourcesystem);
            $PatientSuffix->setField($suffix);
            $patient->addSuffix( $PatientSuffix );

            $createdWithArr[] = "Suffix: " . $suffix;
        }

        if( $sex ) {
            //echo "sex=".$sex."<br>";
            $sexObj = $em->getRepository('OlegUserdirectoryBundle:SexList')->findOneById( $sex );
            $EncounterPatsex = new EncounterPatsex($status, $user, $sourcesystem);
            $EncounterPatsex->setField($sexObj);
            $encounter->addPatsex($EncounterPatsex);

            $PatientSex = new PatientSex($status,$user,$sourcesystem);
            $PatientSex->setField($sexObj);
            $patient->addSex( $PatientSex );

            $createdWithArr[] = "Sex: " . $sexObj;
        }

        $patient->addEncounter($encounter);

        $em->persist($patient);
        $em->persist($encounter);
        $em->flush();

        //log patient creation action
        $userSecUtil = $this->container->get('user_security_utility');
        $eventType = "Patient Created";
        $event = "New Patient has been created:<br>".implode("<br>",$createdWithArr);
        $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'),$event,$user,$patient,$request,$eventType);


        $response->setContent(json_encode($output));
        return $response;
    }
}
