<?php

namespace Oleg\CallLogBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Oleg\CallLogBundle\Form\CalllogMessageType;
use Oleg\CallLogBundle\Form\PatientType;
use Oleg\OrderformBundle\Entity\Encounter;
use Oleg\OrderformBundle\Entity\EncounterAttendingPhysician;
use Oleg\OrderformBundle\Entity\EncounterPatfirstname;
use Oleg\OrderformBundle\Entity\EncounterPatlastname;
use Oleg\OrderformBundle\Entity\EncounterPatmiddlename;
use Oleg\OrderformBundle\Entity\EncounterPatsex;
use Oleg\OrderformBundle\Entity\EncounterPatsuffix;
use Oleg\OrderformBundle\Entity\EncounterReferringProvider;
use Oleg\OrderformBundle\Entity\EncounterReferringProviderSpecialty;
use Oleg\OrderformBundle\Entity\Endpoint;
use Oleg\OrderformBundle\Entity\Message;
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
        $orderUtil = $this->get('scanorder_utility');
        $em = $this->getDoctrine()->getManager();

        $mrn = trim($request->get('mrn'));
        $mrntype = trim($request->get('mrn-type'));


        //check if user has at least one institution
        $securityUtil = $this->get('order_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('calllog_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('calllog_home') );
        }

        $title = "New Entry";

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $cycle = 'new';
        $formtype = 'call-entry';

        $institution = $userSecUtil->getCurrentUserInstitution($user);

        //create patient
        $patient = new Patient(true,'valid',$user,$system);
        $patient->setInstitution($institution);

        //set patient record status "Active"
        $patientActiveStatus = $em->getRepository('OlegOrderformBundle:PatientRecordStatusList')->findOneByName("Active");
        if( $patientActiveStatus ) {
            $patient->setPatientRecordStatus($patientActiveStatus);
        }

        //create invalid encounter #1 just to display fields in "Patient Info"
        $encounter1 = new Encounter(true,'invalid',$user,$system);

        //create encounter #2 to display in "Encounter Info" -> "Update Patient Info"
        $encounter2 = new Encounter(true,'valid',$user,$system);
        $encounter2->setInstitution($institution);
        //ReferringProvider
        $encounterReferringProvider = new EncounterReferringProvider('valid',$user,$system);
        $encounter2->addReferringProvider($encounterReferringProvider);
        //AttendingPhysician
        $encounterAttendingPhysician = new EncounterAttendingPhysician('valid',$user,$system);
        $encounter2->addAttendingPhysician($encounterAttendingPhysician);

        //set encounter generated id
        $key = $encounter2->obtainAllKeyfield()->first();
        $encounter2 = $em->getRepository('OlegOrderformBundle:Encounter')->setEncounterKey($key, $encounter2, $user);

        //set encounter date and time
        $date = $encounter2->getDate()->first();
        $userTimeZone = $user->getPreferences()->getTimezone();
        $nowDate = new \DateTime( "now", new \DateTimeZone($userTimeZone)  );
        $date->setField( $nowDate );
        $date->setTime( $nowDate );

        //set encounter status "Open"
        $encounterOpenStatus = $em->getRepository('OlegOrderformBundle:EncounterStatusList')->findOneByName("Open");
        if( $encounterOpenStatus ) {
            $encounter2->setEncounterStatus($encounterOpenStatus);
        }

        //set encounter info type to "Call to Pathology"
        $encounterInfoType = $em->getRepository('OlegOrderformBundle:EncounterInfoTypeList')->findOneByName("Call to Pathology");
        if( $encounterInfoType ) {
            if( count($encounter2->getEncounterInfoTypes()) > 0 ) {
                $encounter2->getEncounterInfoTypes()->first()->setField($encounterInfoType);
            }
        }

        //testing
        //echo "next key=".$calllogUtil->getNextEncounterGeneratedId()."<br>";
        //$calllogUtil->checkNextEncounterGeneratedId();
        //testing
        //$userFormNodeUtil = $this->get('user_formnode_utility');
        //$formNodeTest = $em->getRepository('OlegUserdirectoryBundle:FormNode')->findOneByName("Blood Product Transfused");
        //$values = $userFormNodeUtil->getDropdownValue($formNodeTest);
        //print_r($values);
        //exit('1');

        //create a new spot and add it to the encounter's tracker
        $withdummyfields = true;
        //$locationTypePrimary = null;
        $encounterLocationType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Encounter Location");
        if( !$encounterLocationType ) {
            throw new \Exception( 'Location type is not found by name Encounter Location' );
        }
        $locationName = null;   //""; //"Encounter's Location";
        $spotEntity = null;
        $removable = 0;
        $encounter2->addContactinfoByTypeAndName($user,$system,$encounterLocationType,$locationName,$spotEntity,$withdummyfields,$em,$removable);

        //add encounter to patient
        $patient->addEncounter($encounter1);
        $patient->addEncounter($encounter2);


        ///////////// Message //////////////
//        $message = new Message();
//        $message->setPurpose("For Internal Use by WCMC Department of Pathology for Call Log Book");
//        $message->setProvider($user);
//
//        //set Source object
//        $source = new Endpoint();
//        $source->setSystem($system);
//        $message->addSource($source);
//
//        //set order category
//        $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName("Pathology Call Log Entry");
//        if( !$messageCategory ) {
//            throw new \Exception( "Location type is not found by name 'Pathology Call Log Entry'" );
//        }
//        $message->setMessageCategory($messageCategory);
//
//        //set Institutional PHI Scope
//        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,$message);
//        $message->setInstitution($permittedInstitutions->first());
        $message = $this->createCalllogEntryMessage($user,$permittedInstitutions,$system);

        //add patient
        $message->addPatient($patient);
        //add encounter
        $message->addEncounter($encounter2);
        ///////////// EOF Message //////////////


        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle);

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
     * Save/Update Call Log Entry
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
        $orderUtil = $this->get('scanorder_utility');
        $calllogUtil = $this->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

        $testing = false;
        $testing = true;

        //check if user has at least one institution
        $securityUtil = $this->get('order_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('calllog_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('calllog_home') );
        }

//        $mrn = trim($request->get('mrn'));
//        $mrntype = trim($request->get('mrn-type'));
        $mrn = null;
        $mrntype = null;

        $title = "Update Entry";

        $system = $securityUtil->getDefaultSourceSystem($this->container->getParameter('calllog.sitename'));
        $status = 'valid';
        $cycle = 'new';
        $formtype = 'call-entry';

        //$patient = new Patient();
        $message = $this->createCalllogEntryMessage($user,$permittedInstitutions,$system);

        //add patient
        //$message->addPatient($patient);

        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle);

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

//        if( $form->isSubmitted() ) {
//            echo "form is submitted <br>";
//        }
//        if( $form->isValid() ) {
//            echo "form is valid <br>";
//        }

        //if( $form->isSubmitted() && $form->isValid() ) {
        if( $form->isSubmitted() ) {

            //$data = $form->getData();
//            $data = $request->request->all();
//            print "<pre>";
//            print_r($data);
//            print "</pre>";
//            $unmappedField = $data["formnode-4"];
//            echo "<br>unmappedField=".$unmappedField."<br>";
//            //print_r($request->get("form"));
//            //exit('form is valid');

            $msg = "No Case found";
            $institution = $userSecUtil->getCurrentUserInstitution($user);

            $patients = $message->getPatient();
            if( count($patients) != 1 ) {
                throw new \Exception( "Message must have only one patient. Patient count= ".count($patients)."'" );
            }
            $patient = $patients->first();
            //echo "patient id=".$patient->getId()."<br>";

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

                //TODO: just keep timezone as DB field and show it in the encounter Date
                //re-set encounter date according to the unmapped timezone
//                $encounterDateObject = $newEncounter->getDate()->first();
//                $encounterDate = $encounterDateObject->getField();
//                echo "date1=".$encounterDate->format('Y-m-d H:i')."<br>";
//                $encounterDateTimezone = $encounterDateObject->getTimezone();
//                echo "encounterDateTimezone=$encounterDateTimezone <br>";
//                $encounterDate = $encounterDate->setTimezone(new \DateTimeZone($encounterDateTimezone));
//                echo "date2=".$encounterDate->format('Y-m-d H:i')."<br>";
//                $encounterDateObject->setField($encounterDate);
//                //exit('1');

                //TODO: Update Patient Info from $newEncounter (?):
                // The values typed into these fields should be recorded as "valid".
                // If the user types in the Date of Birth, it should be added to the "Patient" hierarchy level
                // of the selected patient as a "valid" value and the previous "valid" value should be marked "invalid" on the server side.
                //Use unmapped encounter's "patientDob" to update patient's DOB

                if( $patientInfoEncounter ) {
                    //TODO: copy all non-empty values from the $patientInfoEncounter to the $newEncounter ?

                    //If the user types in the Date of Birth, it should be added to
                    // the "Patient" hierarchy level of the selected patient as a "valid" value
                    // and the previous "valid" value should be marked "invalid" on the server side.
                    //Use unmapped encounter's "patientDob" to update patient's DOB
                    //$patientDob = $form['patientDob']->getData();
                    //echo "patientDob=$patientDob <br>";

                    //$patientInfoEncounter must be removed from the patient
                    $patient->removeEncounter($patientInfoEncounter);
                }

                //prevent creating a new location every time: if location id is provided => find location in DB and replace it with tracker->spot->location
                $calllogUtil->processTrackerLocation($newEncounter);
                //exit('after location');

                //testing: process form nodes
                //$formNodeUtil = $this->get('user_formnode_utility');
                //$formNodeUtil->processFormNodes($request,$message->getMessageCategory(),$message);
                //exit('after formnode');


                //clear encounter
                $message->clearEncounter();
                //add encounter to the message
                $message->addEncounter($newEncounter);

                //set message status from the form's name="messageStatus" field
                $data = $request->request->all();
                $messageStatusForm = $data['messageStatusJs'];
                //echo "messageStatusForm=".$messageStatusForm."<br>";
                if( $messageStatusForm ) {
                    $messageStatusObj = $em->getRepository('OlegOrderformBundle:MessageStatusList')->findOneByName($messageStatusForm);
                    if( $messageStatusObj ) {
                        //echo "set message status to ".$messageStatusObj."<br>";
                        $message->setMessageStatus($messageStatusObj);
                    }
                }

                if( $patient->getId() ) {
                    //CASE 1
                    echo "case 1: patient exists: create a new encounter to DB and add it to the existing patient <br>";
                    //get a new encounter without id $newEncounter
    //                foreach( $encounter->getReferringProviders() as $referringProvider ) {
    //                    echo "encounter referringProvider phone=".$referringProvider->getReferringProviderPhone()."<br>";
    //                }

                    $patient = $em->getRepository('OlegOrderformBundle:Patient')->find($patient->getId());
                    $message->clearPatient();
                    $message->addPatient($patient);

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

                    //add patient to the complex patient list specified by patientListTitle if the option addPatientToList is checked.
                    $addPatientToList = $form["addPatientToList"]->getData();
                    if( $addPatientToList ) {
                        $patientList = $form["patientListTitle"]->getData();
                        echo "add patient to the patient list: ".$patientList->getName().": id=".$patientList->getId()."<br>";
                        if( $patientList ) {
                            $entityNamespace = $patientList->getEntityNamespace();
                            $entityName = $patientList->getEntityName();
                            if( $entityNamespace && $entityName ) {
                                //check if the patient does not exists in this list
                                $entityNamespaceArr = explode("\\", $entityNamespace);
                                $bundleName = $entityNamespaceArr[0] . $entityNamespaceArr[1];
                                $patientListDb = $em->getRepository($bundleName.':'.$entityName)->findOneByPatient($patient);
                                if( !$patientListDb ) {
                                    //create a new record in the list (i.e. PathologyCallComplexPatients)
                                    $listClassName = $entityNamespace . "\\" . $entityName;
                                    $newListElement = new $listClassName();
                                    $patientDescription = "Patient ID# " . $patient->getId() . ": " . $patient->obtainPatientInfoTitle();
                                    $patientName = "Patient ID# ".$patient->getId();
                                    $count = null;
                                    $userSecUtil->setDefaultList($newListElement,$count,$user,$patientName);
                                    $newListElement->setPatient($patient);
                                    $newListElement->setDescription($patientDescription);
                                    $newListElement->setObject($message);
                                    $em->persist($newListElement);
                                }
                            }
                        }
                    }

                    if(0) { //testing
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

                    //echo "patient count=".count($message->getPatient())."<br>";
                    //echo "patient=".$message->getPatient()->first()->obtainPatientInfoTitle()."<br>";
                    //echo $name."<br>";
                    //exit('1');

                    //exit('Exit Case 1');
                    //$em->persist($patient);
                    if( !$testing ) {
                        $em->persist($newEncounter);
                        $em->persist($message);
                        $em->flush(); //testing
                    }

                    $msg = "New Encounter (ID#" . $newEncounter->getId() . ") is created with number " . $newEncounter->obtainEncounterNumber() . " for the Patient with ID #" . $patient->getId();

                } else {
                    //CASE 2
                    echo "case 2: patient does not exists: create a new encounter to DB <br>";
                    //oleg_calllogbundle_patienttype[encounter][0][referringProviders][0][referringProviderPhone]

                    $newEncounter->setPatient(null);

                    //remove empty patient from message
                    $message->removePatient($patient);

                    //exit('Exit Case 2');
                    if( !$testing ) {
                        $em->persist($newEncounter);
                        $em->flush($newEncounter); //testing

                        $em->persist($message);
                        $em->flush($message); //testing
                    }

                    $msg = "New Encounter (ID#" . $newEncounter->getId() . ") is created with number " . $newEncounter->obtainEncounterNumber();
                }

                //set encounter as message's input
                //$message->addInputObject($newEncounter);
                //$em->persist($message);
                //$em->flush($message);

                //process form nodes
                $formNodeUtil = $this->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request,$message->getMessageCategory(),$message,$testing); //testing
                //exit('after formnode');


                //log search action
                if( $msg ) {
                    $eventType = "New Call Log Book Entry Submitted";
//                    $event = "New Call Log with ID# ".$message->getId()." has been created by ".$user." ";
//                    //$event = "";
//                    //PatientLastName, Patient FirstName (DOB: MM/DD/YY, [Gender], [MRN Type(short name)]: [MRN])
//                    if( $patient->getId() ) {
//                        $event = $event . $patient->obtainPatientInfoSimple();
//                    }
//                    // at [EncounterLocation'sName] / [EncounterLocation'sPhoneNumber]
//                    $encounterLocation = $newEncounter->obtainLocationInfo();
//                    if( $encounterLocation ) {
//                        $event = $event . " at " . $encounterLocation;
//                    }
//                    // referred by [ReferringProvider] ([Specialty], [Phone Number]/[ReferringProviderEmail])
//                    $referringProviderInfo = $newEncounter->obtainReferringProviderInfo();
//                    if( $referringProviderInfo ) {
//                        $event = $event . " referred by " . $referringProviderInfo;
//                    }
//                    // for [MessageType:Service] / [MessageType:Issue]
//                    $messageCategoryInfo = $message->getMessageCategoryString();
//                    if( $messageCategoryInfo ) {
//                        $event = $event . " for " . $messageCategoryInfo;
//                    }

                    $event = $calllogUtil->getEventLogDescription($message,$patient,$newEncounter);
                    //exit('event='.$event);

                    //$event = $event . " submitted by " . $user;

                    if( !$testing ) {
                        $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $event, $user, $message, $request, $eventType);
                    }
                }

            }//if $newEncounter


            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

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

    public function createCalllogEntryForm($message, $mrntype=null, $mrn=null, $cycle) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        //$patient = $message->getPatient()->first();

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

        if( $cycle == 'show' ) {
            $disabled = true;
        } else {
            $disabled = false;
        }

        //$timezones
        $userTimeZone = $user->getPreferences()->getTimezone();

        $params = array(
            'cycle' => $cycle,  //'new',
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            'type' => null,
            'mrntype' => intval($mrntype),
            'mrn' => $mrn,
            'formtype' => 'call-entry',
            'complexLocation' => false,
            'alias' => true,
            'timezoneDefault' => $userTimeZone
        );

        $form = $this->createForm(
            new CalllogMessageType($params, $message),
            $message,
            array(
                'disabled' => $disabled
            )
        );

        return $form;
    }

    public function createCalllogEntryMessage($user,$permittedInstitutions,$system) {
        $em = $this->getDoctrine()->getManager();
        $orderUtil = $this->get('scanorder_utility');

        $message = new Message();
        $message->setPurpose("For Internal Use by WCMC Department of Pathology for Call Log Book");
        $message->setProvider($user);
        $message->setVersion('1');

        //set Source object
        $source = new Endpoint();
        $source->setSystem($system);
        $message->addSource($source);

        //set order category
        $categoryStr = "Pathology Call Log Entry";
        //$categoryStr = "Nesting Test"; //testing
        $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
        if( !$messageCategory ) {
            throw new \Exception( "Location type is not found by name '".$categoryStr."'" );
        }
        $message->setMessageCategory($messageCategory);

        //set Institutional PHI Scope
        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,$message);
        $message->setInstitution($permittedInstitutions->first());

        //set message status "Draft"
        $messageStatus = $em->getRepository('OlegOrderformBundle:MessageStatusList')->findOneByName("Draft");
        if( $messageStatus ) {
            $message->setMessageStatus($messageStatus);
        }

        //add patient
        //$message->addPatient($patient);

        return $message;
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
        $searchedArr = array();

        //$currentUrl = trim($request->get('currentUrl'));
        //echo "currentUrl=".$currentUrl."<br>";

        $formtype = trim($request->get('formtype'));

        $patientsData = $this->searchPatient( $request, true );
        $patients = $patientsData['patients'];
        $searchedStr = $patientsData['searchStr'];
        $searchedArr[] = "(Searched for ".$searchedStr.")";
        //echo "patients=".count($patients)."<br>";

        if( count($patients) == 0 ) {
            $params = array();
            $mrntype = trim($request->get('mrntype'));
            $mrn = trim($request->get('mrn'));
            $params['mrntype'] = $mrntype;
            $params['mrn'] = $mrn;
            $patientsDataStrict = $this->searchPatient( $request, true, $params );
            $patientsStrict = $patientsDataStrict['patients'];
            //$searchedStrStrict = $patientsDataStrict['searchStr'];
            foreach( $patientsStrict as $patientStrict ) {
                $mrnRes = $patientStrict->obtainStatusField('mrn', "valid");
                $mrntypeStrict = $mrnRes->getKeytype();
                $mrnStrict = $mrnRes->getField();
                //MRN 001 of MRN type NYH MRN appears to belong to a patient with a last name of LLL, first name of FFFF, and a MM/DD/YYYY date of birth.
                $patientInfoStrict = $patientStrict->obtainPatientInfoShort();
                $searchedArr[] = "<br>MRN $mrnStrict of MRN type $mrntypeStrict appears to belong to a patient $patientInfoStrict";
            }
        }

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

        $resData = array();
        $resData['patients'] = $patientsArr;
        $resData['searchStr'] = implode("; ",$searchedArr);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($resData));
        return $response;
    }

    public function searchPatient( $request, $evenlog=false, $params=null ) {

        $mrntype = trim($request->get('mrntype'));
        $mrn = trim($request->get('mrn'));
        $dob = trim($request->get('dob'));
        $lastname = trim($request->get('lastname'));
        $firstname = trim($request->get('firstname'));
        //print_r($allgets);
        //echo "mrn=".$mrn."<br>";

        $exactMatch = true;
        $matchAnd = true;

        if( $params ) {
            $mrntype = ( array_key_exists('mrntype', $params) ? $params['mrntype'] : null);
            $mrn = ( array_key_exists('mrn', $params) ? $params['mrn'] : null);
            $dob = ( array_key_exists('dob', $params) ? $params['dob'] : null);
            $lastname = ( array_key_exists('lastname', $params) ? $params['lastname'] : null);
            $firstname = ( array_key_exists('firstname', $params) ? $params['firstname'] : null);
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
        $dql->leftJoin("encounter.patfirstname", "encounterFirstname");

        //$dql->where("mrn.status = :statusValid");

        $where = false;
        $searchBy = "unknown parameters";
        $searchArr = array();

        if( $mrntype ) {
            $calllogUtil = $this->get('calllog_util');
            $mrntype = $calllogUtil->convertAutoGeneratedMrntype($mrntype,true);
        }

        //mrn
        if( $mrntype && $mrn ) {

            //$calllogUtil = $this->get('calllog_util');
            //$mrntype = $calllogUtil->convertAutoGeneratedMrntype($mrntype);
            //echo "mrntype=".$mrntype."<br>";
            //echo "mrn=".$mrn."<br>";

            $dql->andWhere("mrn.keytype = :keytype");
            $parameters['keytype'] = $mrntype->getId();

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
            $searchArr[] = "MRN Type: ".$mrntype."; MRN: ".$mrn;
        }

        //DOB
        if( $dob && ($where == false || $matchAnd == true) ) {
            //echo "dob=".$dob."<br>";
            $searchArr[] = "DOB: " . $dob;
            $dobDateTime = \DateTime::createFromFormat('m/d/Y', $dob)->format('Y-m-d');
            //echo "dob=".$dob." => ".$dobDateTime."<br>";
            $dql->andWhere("dob.status = :statusValid OR dob.status = :statusAlias");
            $dql->andWhere("dob.field = :dob");
            $parameters['dob'] = $dobDateTime;
            $where = true;
        }

//        //Last Name Only
//        if( $lastname && !$firstname && ($where == false || $matchAnd == true) ) {
//
//            //$lastname = "Doe";
//            echo "lastname=".$lastname."<br>";
//            $searchArr[] = "Last Name: " . $lastname;
//
//            $statusStr = "(lastname.status = :statusValid OR lastname.status = :statusAlias)";
//            $statusEncounterStr = "(encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias)";
//
//            if ($exactMatch) {
//                ////$dql->andWhere("lastname.field = :lastname OR encounterLastname.field = :lastname");
//                $dql->andWhere("(lastname.field = :lastname AND $statusStr) OR (encounterLastname.field = :lastname AND $statusEncounterStr)");
//                $parameters['lastname'] = $lastname;
//            } else {
//                $dql->andWhere("lastname.field LIKE :lastname OR encounterLastname.field LIKE :lastname");
//            }
//
//            //$dql->andWhere("lastname.status = :statusValid OR lastname.status = :statusAlias");
//            //$dql->andWhere("encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias");
//
//            $parameters['statusValid'] = 'valid';
//            $parameters['statusAlias'] = 'alias';
//
//            $where = true;
//        }

//        //First Name Only
//        if( $firstname && !$lastname && ($where == false || $matchAnd == true) ) {
//
//            //$firstname = "Linda";
//            echo "firstname=".$firstname."<br>";
//            $searchArr[] = "First Name: " . $firstname;
//
//            $statusStr = "(firstname.status = :statusValid OR firstname.status = :statusAlias)";
//            $statusEncounterStr = "(encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias)";
//
//            if( $exactMatch ) {
//                ////$dql->andWhere("firstname.field = :firstname OR encounterFirstname.field = :firstname");
//                $dql->andWhere("(firstname.field = :firstname AND $statusStr) OR (encounterFirstname.field = :firstname AND $statusEncounterStr)");
//                $parameters['firstname'] = $firstname;
//            } else {
//                $dql->andWhere("firstname.field LIKE :firstname OR encounterFirstname.field LIKE :firstname");
//                $parameters['firstname'] = '%' . $firstname . '%';
//            }
//
//            $dql->andWhere("firstname.status = :statusValid OR firstname.status = :statusAlias");
//            $dql->andWhere("encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias");
//            $parameters['statusValid'] = 'valid';
//            $parameters['statusAlias'] = 'alias';
//
//            $where = true;
//        }

        //Last Name AND DOB
        if( ($lastname || $firstname) && ($where == false || $matchAnd == true) ) {

            //$lastname = "Doe";
            //echo "1 lastname=".$lastname."<br>";
            //echo "1 firstname=".$firstname."<br>";

            $searchCriterionArr = array();

            if( $lastname ) {
                $searchArr[] = "Last Name: " . $lastname;

                $statusStr = "(lastname.status = :statusValid OR lastname.status = :statusAlias)";
                $statusEncounterStr = "(encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias)";

                $searchCriterionArr[] = "(lastname.field = :lastname AND $statusStr) OR (encounterLastname.field = :lastname AND $statusEncounterStr)";

                $parameters['lastname'] = $lastname;

                //status
                $dql->andWhere("lastname.status = :statusValid OR lastname.status = :statusAlias");
                $dql->andWhere("encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias");
                $parameters['statusValid'] = 'valid';
                $parameters['statusAlias'] = 'alias';

                $where = true;
            }

            if( $firstname ) {
                $searchArr[] = "First Name: " . $firstname;

                $statusStr = "(firstname.status = :statusValid OR firstname.status = :statusAlias)";
                $statusEncounterStr = "(encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias)";

                $searchCriterionArr[] = "(firstname.field = :firstname AND $statusStr) OR (encounterFirstname.field = :firstname AND $statusEncounterStr)";

                $parameters['firstname'] = $firstname;

                //status
                $dql->andWhere("firstname.status = :statusValid OR firstname.status = :statusAlias");
                $dql->andWhere("encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias");
                $parameters['statusValid'] = 'valid';
                $parameters['statusAlias'] = 'alias';

                $where = true;
            }

            $searchCriterionStr = implode(" OR ",$searchCriterionArr);
            $dql->andWhere($searchCriterionStr);
        }

//        //Last Name AND DOB
//        if( $lastname && $dob && ($where == false || $matchAnd == true) ) {
//            $dobDateTime = \DateTime::createFromFormat('m/d/Y', $dob)->format('Y-m-d');
//            //echo "dob=".$dob." => ".$dobDateTime."<br>";
////            $dql->andWhere("dob.field = :dob AND (lastname.field = :lastname OR encounterLastname.field = :lastname)");
////            $parameters['lastname'] = $lastname;
////            $parameters['dob'] = $dobDateTime;
//
//            $dql->andWhere("dob.field = :dob");
//            $parameters['dob'] = $dobDateTime;
//
//            if ($exactMatch) {
//                $dql->andWhere("lastname.field = :lastname OR encounterLastname.field = :lastname");
//                $parameters['lastname'] = $lastname;
//            } else {
//                $dql->andWhere("lastname.field LIKE :lastname OR encounterLastname.field LIKE :lastname");
//                $parameters['lastname'] = '%' . $lastname . '%';
//            }
//
//            $dql->andWhere("dob.status = :statusValid OR dob.status = :statusAlias");
//            $dql->andWhere("lastname.status = :statusValid OR lastname.status = :statusAlias");
//            $dql->andWhere("encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias");
//            $parameters['statusValid'] = 'valid';
//            $parameters['statusAlias'] = 'alias';
//
//            $searchArr[] = "DOB: " . $dob . ", Last Name: " . $lastname;
//
//            if ($firstname) {
//                $dql->andWhere("firstname.field = :firstname OR encounterFirstname.field = :firstname");
//                $dql->andWhere("encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias");
//                $parameters['firstname'] = $firstname;
//
//                $searchArr[] = ", First Name: " . $firstname;
//            }
//
//            $where = true;
//        }

//        //Last Name only
//        if( $lastname && ($where == false || $matchAnd == true) ) {
//            echo "lastname=".$lastname."<br>";
//            if( $exactMatch ) {
//                $dql->andWhere("lastname.field = :lastname OR encounterLastname.field = :lastname");
//                $parameters['lastname'] = $lastname;
//            } else {
//                $dql->andWhere("lastname.field LIKE :lastname OR encounterLastname.field LIKE :lastname");
//                $parameters['lastname'] = '%' . $lastname . '%';
//            }
//
//            $dql->andWhere("lastname.status = :statusValid OR lastname.status = :statusAlias");
//            $dql->andWhere("encounterLastname.status = :statusValid OR encounterLastname.status = :statusAlias");
//            $parameters['statusValid'] = 'valid';
//            $parameters['statusAlias'] = 'alias';
//
//            $searchArr[] = "Last Name: ".$lastname;
//
//            if( $firstname ) {
//                $dql->andWhere("encounterFirstname.status = :statusValid OR encounterFirstname.status = :statusAlias");
//
//                if( $exactMatch ) {
//                    $dql->andWhere("firstname.field = :firstname OR encounterFirstname.field = :firstname");
//                    $parameters['firstname'] = $firstname;
//                } else {
//                    $dql->andWhere("firstname.field LIKE :firstname OR encounterFirstname.field LIKE :firstname");
//                    $parameters['firstname'] = '%' . $firstname . '%';
//                }
//
//                $searchArr[] = ", First Name: ".$firstname;
//            }
//
//            $where = true;
//        }


//        //firstname, Last Name AND DOB
//        if( $lastname && $firstname && $dob ) {
//            $dql->andWhere("lastname.field = :lastname AND firstname.field = :firstname");
//            $parameters['lastname'] = $lastname;
//            $parameters['firstname'] = $firstname;
//            $where = true;
//        }

        if( count($searchArr) > 0 ) {
            $searchBy = implode("; ",$searchArr);
        }

        if( $where ) {

            $query = $em->createQuery($dql);
            $query->setParameters($parameters);
            //echo "sql=".$query->getSql()."<br>";
            $patients = $query->getResult();

            //testing
//            echo "<br>";
//            foreach( $patients as $patient ) {
//                echo "ID=".$patient->getId()."<br>";
//            }
//            //exit('patients count='.count($patients));

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

        $res = array();
        $res['patients'] = $patients;
        $res['searchStr'] = $searchBy;

        return $res;
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

        //testing
        //exit("createPatientAction");
        //$res['output'] = "OK";
        //$response->setContent(json_encode($res));
        //return $response;

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
//        $params = array();
//        $params['mrntype'] = $mrntype;
//        $params['mrn'] = $mrn;
//        $patientsData = $this->searchPatient( $request, false, $params );
        $patientsData = $this->searchPatient( $request );
        $patients = $patientsData['patients'];

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

        //set source
        $patient->setSource($sourcesystem);

        //set patient record status "Active"
        $patientActiveStatus = $em->getRepository('OlegOrderformBundle:PatientRecordStatusList')->findOneByName("Active");
        if( $patientActiveStatus ) {
            $patient->setPatientRecordStatus($patientActiveStatus);
        }

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

    /**
     * Get Patient Titles
     * @Route("/patient/title/", name="calllog_get_patient_title", options={"expose"=true})
     * @Method("GET")
     */
    public function getPatientTitleAction(Request $request) {

        if (false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $patientId = trim($request->get('patientId'));
        $nowStr = trim($request->get('nowStr'));
        //echo "patientId=".$patientId."<br>";
        //echo "nowStr=".$nowStr."<br>";

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $nowDate = new \DateTime($nowStr);

        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository('OlegOrderformBundle:Patient')->find($patientId);
        if( !$patient ) {
            $response->setContent(json_encode("ERROR"));
            return $response;
        }

        $patientTitle = $patient->obtainPatientInfoTitle('valid',$nowDate);
        if( !$patientTitle ) {
            $response->setContent(json_encode("ERROR"));
            return $response;
        }

        $response->setContent(json_encode($patientTitle));
        return $response;
    }


    /**
     * Get Call Log Entry Message
     * @Route("/entry/view/{messageId}", name="calllog_callentry_view")
     * @Method("GET")
     * @Template("OlegCallLogBundle:CallLog:call-entry-view.html.twig")
     */
    public function getCallLogEntryAction(Request $request, $messageId)
    {

        if (false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $userSecUtil = $this->get('user_security_utility');

        $cycle = "show";
        $title = "Call Log Entry";
        $formtype = "call-entry";

        //$patientId = trim($request->get('patientId'));
        //$nowStr = trim($request->get('nowStr'));
        //echo "patientId=".$patientId."<br>";
        //echo "nowStr=".$nowStr."<br>";

        $em = $this->getDoctrine()->getManager();
        $message = $em->getRepository('OlegOrderformBundle:Message')->find($messageId);
        if (!$message) {
            throw new \Exception('Message has not found by ID ' . $messageId);
        }

        if (count($message->getPatient()) > 0 ) {
            $mrnRes = $message->getPatient()->first()->obtainStatusField('mrn', "valid");
            $mrntype = $mrnRes->getKeytype()->getId();
            $mrn = $mrnRes->getField();
        } else {
            $mrntype = null;
            $mrn = null;
        }

        //echo "patients=".count($message->getPatient())."<br>";
        $form = $this->createCalllogEntryForm($message,$mrntype,$mrn,$cycle);

        $complexPatientStr = null;
        //find record in the "Pathology Call Complex Patients" list by message object entityName, entityId
        $mapper = array(
            'prefix' => "Oleg",
            'bundleName' => "CallLogBundle",
            'className' => "PathologyCallComplexPatients",
        );
        $listRecord = $userSecUtil->getListByNameAndObject( $message, $mapper );
        if( $listRecord ) {
            //Patient was added to the "xxxxxxxx" list via this entry.
            $complexPatientStr = "Patient was added to the Pathology Call Complex Patients list ID# ".$listRecord->getId()." via this entry:<br>".$listRecord->getName()."";
        }
        //echo "complexStr=".$complexPatientStr."<br>";

        $class = new \ReflectionClass($message);
        $className = $class->getShortName();          //ObjectTypeText
        $classNamespace = $class->getNamespaceName(); //Oleg\UserdirectoryBundle\Entity

        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title . " ID# " . $message->getId(),
            'formtype' => $formtype,
            'triggerSearch' => 0,
            'mrn' => $mrn,
            'mrntype' => $mrntype,
            'message' => $message,
            'complexPatientStr' => $complexPatientStr,
            //'encounterid' => $encounterid
            'entityNamespace' => $classNamespace,
            'entityName' => $className,
            'entityId' => $message->getId(),
        );
    }

}
