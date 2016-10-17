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
use Oleg\OrderformBundle\Entity\EncounterReferringProvider;
use Oleg\OrderformBundle\Entity\EncounterReferringProviderSpecialty;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\PatientDob;
use Oleg\OrderformBundle\Entity\PatientFirstName;
use Oleg\OrderformBundle\Entity\PatientLastName;
use Oleg\OrderformBundle\Entity\PatientMiddleName;
use Oleg\OrderformBundle\Entity\PatientMrn;
use Oleg\OrderformBundle\Entity\PatientSex;
use Oleg\OrderformBundle\Entity\PatientSuffix;
use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\UserdirectoryBundle\Entity\Spot;
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
     * http://localhost/order/call-log-book/entry/new?mrn-type=4&mrn=3
     * @Route("/entry/new", name="calllog_callentry")
     * @Template("OlegCallLogBundle:CallLog:call-entry.html.twig")
     */
    public function callEntryAction(Request $request)
    {
        //1) search box: MRN,Name...

        $user = $this->get('security.context')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $calllogUtil = $this->get('calllog_util');
        $userSecUtil = $this->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $mrn = trim($request->get('mrn'));
        $mrntype = trim($request->get('mrn-type'));

        $title = "New Entry";

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $status = 'valid';
        $cycle = 'new';
        $formtype = 'call-entry';

        $institution = $userSecUtil->getCurrentUserInstitution($user);

        //create patient
        $patient = new Patient(true,$status,$user,$system);
        $patient->setInstitution($institution);

        //create invalid encounter #1 just to display in "Patient Info"
        $encounter1 = new Encounter(true,'invalid',$user,$system);

        //create encounter #2 to display in "Encounter Info"
        $encounter2 = new Encounter(true,$status,$user,$system);
        $encounter2->setInstitution($institution);
        $encounterReferringProvider = new EncounterReferringProvider($status,$user,$system);
        $encounter2->addReferringProvider($encounterReferringProvider);

        //set encounter generated id
        $key = $encounter2->obtainAllKeyfield()->first();
        $encounter2 = $em->getRepository('OlegOrderformBundle:Encounter')->setEncounterKey($key, $encounter2, $user);

        //set encounter date and time
        $date = $encounter2->getDate()->first();
        $userTimeZone = $user->getPreferences()->getTimezone();
        $nowDate = new \DateTime( "now", new \DateTimeZone($userTimeZone)  );
        $date->setField( $nowDate );
        $date->setTime( $nowDate );

        //testing
        //echo "next key=".$calllogUtil->getNextEncounterGeneratedId()."<br>";
        //$calllogUtil->checkNextEncounterGeneratedId();
        //exit('1');

        //create a new spot and add it to the encounter's tracker
        $withdummyfields = true;
        //$locationTypePrimary = null;
        $encounterLocationType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Encounter Location");
        if( !$encounterLocationType ) {
            throw new \Exception( 'Location type is not found by name Encounter Location' );
        }
        $locationName = ""; //"Encounter's Location";
        $spotEntity = null;
        $removable = 0;
        $encounter2->addContactinfoByTypeAndName($user,$system,$encounterLocationType,$locationName,$spotEntity,$withdummyfields,$em,$removable);
//        if( $encounter->getTracker() ) {
//            echo "spot count=".count($encounter->getTracker()->getSpots())."<br>";
//        }

        //add encounter to patient
        $patient->addEncounter($encounter1);
        $patient->addEncounter($encounter2);


        $form = $this->createPatientForm($patient,$mrntype,$mrn);

        //$encounterid = $calllogUtil->getNextEncounterGeneratedId();

        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'formtype' => $formtype,
            'triggerSearch' => 0,
            'mrn' => $mrn,
            'mrntype' => $mrntype,
            //'encounterid' => $encounterid
        );
    }

    /**
     * Update Patient
     * @Route("/patient/update", name="calllog_update_patient", options={"expose"=true})
     * @Template("OlegCallLogBundle:CallLog:call-entry.html.twig")
     * @Method("POST")
     */
    public function updatePatientAction(Request $request)
    {
        //exit('update patient');
        //case 1: patient exists: create a new encounter to DB and add it to the existing patient
        //add patient id field to the form (id="oleg_calllogbundle_patienttype_id") or use class="calllog-patient-id" input field.
        //case 2: patient does not exists: create a new encounter to DB

        $user = $this->get('security.context')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $userSecUtil = $this->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

//        $mrn = trim($request->get('mrn'));
//        $mrntype = trim($request->get('mrn-type'));
        $mrn = null;
        $mrntype = null;

        $title = "Update Entry";

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $status = 'valid';
        $cycle = 'new';
        $formtype = 'call-entry';

        $patient = new Patient();

        $form = $this->createPatientForm($patient,$mrntype,$mrn);

        $form->handleRequest($request);

//        echo "loc errors:<br>";
//        print_r($form->getErrors());
//        echo "<br>loc string errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";

//        $errorHelper = new ErrorHelper();
//        $errors = $errorHelper->getErrorMessages($form);
//        echo "<br>form errors:<br>";
//        print_r($errors);
        //exit();

        //oleg_calllogbundle_patienttype[id]
        //$formPatientId = $form["id"]->getData();
        //echo "1: formPatientId=".$formPatientId."<br>";

        //oleg_calllogbundle_patienttype[encounter][0][patfirstname][0][field]
        //$formPatientDob = $form["dob"][0]->getData();
        //echo "1: formPatientDob=".$formPatientDob."<br>";
        //print_r($formPatientDob);

        //$referringProviderPhone = $form["encounter"][0]['referringProviders'][0]['referringProviderPhone']->getData();
        //$formPatientId = $request->request->get('patient');
        //$formPatientId = $form["id"]->getData();
        //echo "1: form referringProviderPhone=".$referringProviderPhone."<br>";
        //$data = $form->getData();
        //print_r($data);

        if( $form->isSubmitted() ) {
            echo "form is submitted <br>";
        }
        if( $form->isValid() ) {
            echo "form is valid <br>";
        }

        //if( $form->isSubmitted() && $form->isValid() ) {
        if( $form->isSubmitted() ) {
            //exit('form is valid');

            $msg = "No Case found";
            $institution = $userSecUtil->getCurrentUserInstitution($user);

            echo "patient id=".$patient->getId()."<br>";

            $patientInfoEncounter = null;
            $newEncounter = null;
            //get a new encounter without id
            foreach( $patient->getEncounter() as $encounter ) {
                echo "encounter ID=".$encounter->getId()."; status=".$encounter->getStatus()."<br>";
                if( !$encounter->getId() ) {
                    if( $encounter->getStatus() == 'valid' ) {
                        $newEncounter = $encounter;
                    }
                    if( $encounter->getStatus() == 'invalid' ) {
                        //this encounter is served only to find the patient:
                        //copy all non-empty values from the $patientInfoEncounter to the $newEncounter
                        //it must be removed from the patient
                        $patientInfoEncounter = $encounter;
                    }
                }
            }

            //set system source and user's default institution
            if( $newEncounter ) {

                $newEncounter->setSource($system);
                $newEncounter->setInstitution($institution);

                //assign generated encounter number ID
                $key = $newEncounter->obtainAllKeyfield()->first();
                $em->getRepository('OlegOrderformBundle:Encounter')->setEncounterKey($key, $newEncounter, $user);

                //Remove tracker if spots/location is empty
                $tracker = $newEncounter->getTracker();
                $tracker->removeEmptySpots();
                if( $tracker->isEmpty() ) {
                    //echo "Tracker is empty! <br>";
                    $newEncounter->setTracker(null);
                } else {
                    //echo "Tracker is not empty! <br>";
                    //check if location name is not empty
                    if( $newEncounter->getTracker() ) {
                        $currentLocation = $newEncounter->getTracker()->getSpots()->first()->getCurrentLocation();
                        if( !$currentLocation->getName() ) {
                            $currentLocation->setName('');
                        }
                        if( !$currentLocation->getCreator() ) {
                            $currentLocation->setCreator($user);
                        }
                    }
                }
                //exit();

                //TODO: Update Patient Info from $newEncounter:
                // The values typed into these fields should be recorded as "valid".
                // If the user types in the Date of Birth, it should be added to the "Patient" hierarchy level
                // of the selected patient as a "valid" value and the previous "valid" value should be marked "invalid" on the server side.
                //Use unmapped encounter's "patientDob" to update patient's DOB

                if( $patientInfoEncounter ) {
                    //TODO: copy all non-empty values from the $patientInfoEncounter to the $newEncounter?

                    //If the user types in the Date of Birth, it should be added to
                    // the "Patient" hierarchy level of the selected patient as a "valid" value
                    // and the previous "valid" value should be marked "invalid" on the server side.
                    //Use unmapped encounter's "patientDob" to update patient's DOB
                    //$patientDob = $form['patientDob']->getData();
                    //echo "patientDob=$patientDob <br>";

                    //$patientInfoEncounter must be removed from the patient
                    $patient->removeEncounter($patientInfoEncounter);
                }

                if( $patient->getId() ) {
                    //CASE 1
                    echo "case 1: patient exists: create a new encounter to DB and add it to the existing patient <br>";
                    //get a new encounter without id $newEncounter
    //                foreach( $encounter->getReferringProviders() as $referringProvider ) {
    //                    echo "encounter referringProvider phone=".$referringProvider->getReferringProviderPhone()."<br>";
    //                }

                    $patient = $em->getRepository('OlegOrderformBundle:Patient')->find($patient->getId());

                    //reset institution from the patient
                    $newEncounter->setInstitution($patient->getInstitution());

                    $patient->addEncounter($newEncounter);

                    //add new DOB (if exists) to the Patient
                    //Use unmapped encounter's "patientDob" to update patient's DOB
                    if( $newEncounter->getPatientDob() ) {
                        //invalidate all other patient's DOB
                        $validDOBs = $patient->obtainStatusFieldArray("dob","valid");
                        foreach( $validDOBs as $validDOB) {
                            $validDOB->setStatus("invalid");
                        }

                        $patientDob = $newEncounter->getPatientDob();
                        //echo "encounter patientDob=" . $patientDob->format('Y-m-d') . "<br>";
                        $newPatientDob = new PatientDob($status,$user,$system);
                        $newPatientDob->setField($patientDob);
                        $patient->addDob($newPatientDob);
                        //echo "patient patientDob=" . $newPatientDob . "<br>";
                    }

                    if(1) {
                        echo "encounter count=" . count($patient->getEncounter()) . "<br>";
                        foreach ($patient->getEncounter() as $encounter) {
                            echo "<br>encounter ID=" . $encounter->getId() . "<br>";
                            echo "encounter Date=" . $encounter->getDate()->first() . "<br>";
                            echo "encounter Last Name=" . $encounter->getPatlastname()->first() . "<br>";
                            echo "encounter First Name=" . $encounter->getPatfirstname()->first() . "<br>";
                            echo "encounter Middle Name=" . $encounter->getPatmiddlename()->first() . "<br>";
                            echo "encounter Suffix=" . $encounter->getPatsuffix()->first() . "<br>";
                            echo "encounter Gender=" . $encounter->getPatsex()->first() . "<br>";

                            if( $encounter->getTracker() ) {
                                echo "encounter Location=" . $encounter->getTracker()->getSpots()->first()->getCurrentLocation()->getName() . "<br>";
                            }
                        }
                    }

                    //exit('Exit Case 1');
                    //$em->persist($patient);
                    $em->persist($newEncounter);
                    $em->flush();

                    $msg = "New Encounter (ID#" . $newEncounter->getId() . ") is created with number " . $newEncounter->obtainEncounterNumber() . " for the Patient with ID #" . $patient->getId();
                    //}

                } else {
                    //CASE 2
                    echo "case 2: patient does not exists: create a new encounter to DB <br>";
                    //oleg_calllogbundle_patienttype[encounter][0][referringProviders][0][referringProviderPhone]

                    $newEncounter->setPatient(null);

                    //exit('Exit Case 2');
                    $em->persist($newEncounter);
                    $em->flush($newEncounter);

                    $msg = "New Encounter (ID#" . $newEncounter->getId() . ") is created with number " . $newEncounter->obtainEncounterNumber();

                }


            }//if $newEncounter

            //exit('form is submitted and finished, msg='.$msg);

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            return $this->redirect( $this->generateUrl('calllog_callentry') );
        }
        //exit('form is not submitted');

        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'formtype' => $formtype,
            'triggerSearch' => 0,
            'mrn' => $mrn,
            'mrntype' => $mrntype
        );
    }

    public function createPatientForm($patient, $mrntype=null, $mrn=null, $formparams=null) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        ////////////////////////
//        $query = $em->createQueryBuilder()
//            ->from('OlegOrderformBundle:MrnType', 'list')
//            ->select("list.id as id, list.name as text")
//            ->orderBy("list.orderinlist","ASC");
//        $query->where("list.type = :type OR ( list.type = 'user-added' AND list.name != :autogen)");
//        $query->setParameters( array('type' => 'default','autogen' => 'Auto-generated MRN') );
//        //echo "query=".$query."<br>";
//
//        $mrntypes = $query->getQuery()->getResult();
//        foreach( $mrntypes as $mrntype ) {
//            echo "mrntype=".$mrntype['id'].":".$mrntype['text']."<br>";
//        }
        ///////////////////////

        if( !$mrntype ) {
            $mrntype = 1;
        }

        $params = array(
            'cycle' => 'new',
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            //'alias' => true
            'type' => null,
            'mrntype' => intval($mrntype),
            'mrn' => $mrn,
            'formtype' => 'call-entry',
            'complexLocation' => false,
            'alias' => false
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
//            //exit('1');

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
        //exit('exit search patient');

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

        if( $mrntype ) {
            $calllogUtil = $this->get('calllog_util');
            $mrntype = $calllogUtil->convertAutoGeneratedMrntype($mrntype);
        }

        //mrn
        if( $mrntype && $mrn ) {

            //$calllogUtil = $this->get('calllog_util');
            //$mrntype = $calllogUtil->convertAutoGeneratedMrntype($mrntype);
            //echo "mrntype=".$mrntype."<br>";

            $dql->andWhere("mrn.keytype = :keytype");
            $parameters['keytype'] = $mrntype;

            if( $exactMatch ) {
                $mrnClean = ltrim($mrn, '0');
                //echo "mrn: ".$mrn."?=".$mrnClean."<br>";
                if( $mrn === $mrnClean ) {
                    //echo "equal <br>";
                    $dql->andWhere("mrn.field = :mrn");
                    $parameters['mrn'] = $mrn;
                } else {
                    //echo "not equal <br>";
                    $dql->andWhere("mrn.field = :mrn OR mrn.field = :mrnClean");
                    $parameters['mrn'] = $mrn;
                    $parameters['mrnClean'] = $mrnClean;
                }

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
        //$calllogUtil = $this->get('calllog_util');
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

        $securityUtil = $this->get('order_security_utility');
        $userSecUtil = $this->get('user_security_utility');
        $res = array();
        $output = 'OK';
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        //TODO: The server should DOUBLECHECK that the user has a role with a permission of "Create Patient Record"
        if (false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER')) {
            //return $this->redirect($this->generateUrl('calllog-nopermission'));
            $res['patients'] = null;
            $res['output'] = "You don't have a permission to create a new patient record";
            $response->setContent(json_encode($res));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $calllogUtil = $this->get('calllog_util');

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
        //echo "mrntype=".$mrntype."<br>";

        if( $mrntype ) {
            $errorMsg = 'Mrn Type not found by "' . $mrntype . '"';
            $calllogUtil = $this->get('calllog_util');
            $mrntype = $calllogUtil->convertAutoGeneratedMrntype($mrntype);
            if( ! $mrntype ) {
                $res['patients'] = null;
                $res['output'] = $errorMsg;
                $response->setContent(json_encode($res));
                return $response;
            }
        }

        //TODO: set institution
//        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
//        $institution = $userSiteSettings->getDefaultInstitution();
//        //echo "1 inst=".$institution."<br>";
//        if( !$institution ) {
//            $institutions = $securityUtil->getUserPermittedInstitutions($user);
//            //echo "count inst=".count($institutions)."<br>";
//            if (count($institutions) > 0) {
//                $institution = $institutions[0];
//            }
//            //echo "2 inst=".$institution."<br>";
//        }
//        if (!$institution) {
//            $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
//        }
        $institution = $userSecUtil->getCurrentUserInstitution($user);
        //echo "3 inst=".$institution."<br>";
        //exit('1');

        //get correct mrn type
        if( $mrntype && $mrn ) {
            $keytype = $mrntype;
        } else {
            $keytypeEntity = $this->getDoctrine()->getRepository('OlegOrderformBundle:MrnType')->findOneByName("Auto-generated MRN");
            $keytype = $keytypeEntity->getId() . ""; //id of "New York Hospital MRN" in DB
        }

        //first check if the patient already exists
        $patients = $this->searchPatient( $request );
        if( count($patients) > 0 ) {
            $output = "Can not create a new Patient. The patient with specified parameters already exists:<br>";

            if( $mrntype ) {
                $output .= "MRN Type:".$keytypeEntity."<br>";
            }
            if( $mrn )
                $output .= "MRN:".$mrn."<br>";
            if( $lastname )
                $output .= "Last Name:".$lastname."<br>";
            if( $firstname )
                $output .= "First Name:".$firstname."<br>";
            if( $dob )
                $output .= "DOB:".$dob."<br>";

            $res['patients'] = null;
            $res['output'] = $output;
            $response->setContent(json_encode($res));
            return $response;
        }

        //testing
        if(0) {
            $patient = $em->getRepository('OlegOrderformBundle:Patient')->find(32);
            $patientsArr = array(); //return json data
            $patientInfo = $calllogUtil->getJsonEncodedPatient($patient);
            $patientsArr[$patient->getId()] = $patientInfo;
            $res['patients'] = $patientsArr;
            $res['output'] = $output;
            $response->setContent(json_encode($res));
            return $response;
        }

        //Create a new Patient
        $sourcesystem = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $status = 'valid';

        //$patient = new Patient(false,$status,$user,$sourcesystem);
        //$patient->setInstitution($institution);

        //create a new patient
        if( $mrn ) {
            $fieldValue = $mrn;
        } else {
            $fieldValue = null;
        }

//        if( $mrntype ) {
//            $keytype = $mrntype;
//        } else {
//            $keytypeEntity = $this->getDoctrine()->getRepository('OlegOrderformBundle:MrnType')->findOneByName("Auto-generated MRN");
//            $keytype = $keytypeEntity->getId() . ""; //id of "New York Hospital MRN" in DB
//        }

        //echo "mrn=".$fieldValue."<br>";
        //echo "keytype=".$keytype." (".$keytypeEntity.")<br>";
        //exit("1");

        $extra = array();
        $extra["keytype"] = $keytype;

        //echo "keytype=".$keytype."<br>";
        //exit();


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

        $mrnRes = $patient->obtainStatusField('mrn', $status);
        $createdWithArr = array();
        $createdWithArr[] = "MRN Type: ".$mrnRes->getKeytype()->getName();
        $createdWithArr[] = "MRN: ".$mrnRes->getField();

        //mrn with leading zeros
        if( 0 && $mrn ) {
            $mrnClean = ltrim($mrn, '0');
            //echo "mrn: ".$mrn."?=".$mrnClean."<br>";
            if ($mrn !== $mrnClean) {
                //create additional valid patient MRN: "00123456" and "123456".
                $mrnCleanObject = new PatientMrn($status,$user,$sourcesystem);
                $mrnCleanObject->setKeytype($mrnRes->getKeytype());
                $mrnCleanObject->setField($mrnClean);
                $patient->addMrn($mrnCleanObject);
                $createdWithArr[] = "Clean MRN: ".$mrnClean;
            }
        }

        //$patient->addDob( new PatientDob($status,$user,$sourcesystem) );
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

            $createdWithArr[] = "Gender: " . $sexObj;
        }

        $patient->addEncounter($encounter);

        $em->persist($patient);
        $em->persist($encounter);
        $em->flush();

        //convert patient to json
        $patientsArr = array(); //return json data
        $patientInfo = $calllogUtil->getJsonEncodedPatient($patient);
        $patientsArr[$patient->getId()] = $patientInfo;
        $res['patients'] = $patientsArr;
        $res['output'] = $output;

        $eventType = "Patient Created";
        $event = "New Patient has been created:<br>" . implode("<br>", $createdWithArr);

        //log patient creation action
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $event, $user, $patient, $request, $eventType);

        $response->setContent(json_encode($res));
        return $response;
    }


//    public function getCurrentUserInstitution($user)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $securityUtil = $this->get('order_security_utility');
//
//        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
//        $institution = $userSiteSettings->getDefaultInstitution();
//        //echo "1 inst=".$institution."<br>";
//        if (!$institution) {
//            $institutions = $securityUtil->getUserPermittedInstitutions($user);
//            //echo "count inst=".count($institutions)."<br>";
//            if (count($institutions) > 0) {
//                $institution = $institutions[0];
//            }
//        //echo "2 inst=".$institution."<br>";
//        }
//        if (!$institution) {
//            $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
//        }
//
//        return $institution;
//    }
}
