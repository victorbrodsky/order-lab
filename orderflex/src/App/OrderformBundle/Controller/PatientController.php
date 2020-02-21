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

use App\OrderformBundle\Entity\ExternalId;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\OrderformBundle\Entity\ImageAnalysisAlgorithmList;
use App\OrderformBundle\Entity\ImageAnalysisOrder;
use App\OrderformBundle\Entity\ProcedureOrder;
use App\OrderformBundle\Entity\ReportBlock;
use App\UserdirectoryBundle\Entity\InstitutionWrapper;
use App\UserdirectoryBundle\Entity\Link;
use App\UserdirectoryBundle\Form\DataTransformer\UserWrapperTransformer;
use App\OrderformBundle\Helper\ErrorHelper;

use App\OrderformBundle\Entity\Patient;
use App\OrderformBundle\Entity\Encounter;
use App\OrderformBundle\Form\PatientType;
use App\OrderformBundle\Entity\Procedure;
use App\OrderformBundle\Entity\Accession;
use App\OrderformBundle\Entity\Part;
use App\OrderformBundle\Entity\Block;
use App\OrderformBundle\Entity\Slide;

use App\OrderformBundle\Entity\LabOrder;

use App\OrderformBundle\Entity\AccessionAccession;
use App\OrderformBundle\Entity\BlockOrder;
use App\OrderformBundle\Entity\EncounterDate;
use App\OrderformBundle\Entity\EncounterPatage;
use App\OrderformBundle\Entity\Endpoint;
use App\OrderformBundle\Entity\Instruction;
use App\OrderformBundle\Entity\Message;
use App\OrderformBundle\Entity\PatientClinicalHistory;
use App\OrderformBundle\Entity\PatientDob;
use App\OrderformBundle\Entity\PatientFirstName;
use App\OrderformBundle\Entity\PatientLastName;
use App\OrderformBundle\Entity\PatientMiddleName;
use App\OrderformBundle\Entity\PatientMrn;
use App\OrderformBundle\Entity\PatientSex;
use App\OrderformBundle\Entity\Report;
use App\OrderformBundle\Entity\Imaging;
use App\OrderformBundle\Entity\ScanOrder;
use App\OrderformBundle\Entity\SlideOrder;
use App\OrderformBundle\Entity\StainOrder;
use App\OrderformBundle\Form\DataTransformer\AccessionTypeTransformer;
use App\OrderformBundle\Form\DataTransformer\MrnTypeTransformer;

use App\UserdirectoryBundle\Entity\AttachmentContainer;
use App\UserdirectoryBundle\Entity\DocumentContainer;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\Institution;
use App\UserdirectoryBundle\Entity\UserWrapper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Patient controller.
 *
 * @Route("/patient")
 */
class PatientController extends AbstractController
{


    /**
     * Lists all Patient entities.
     *
     * @Route("/", name="scan-patient-list")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $searchUtil = $this->get('search_utility');
        $object = 'patient';
        $params = array('request'=>$request,'object'=>$object);
        $res = $searchUtil->searchAction($params);
        $entities = $res[$object];

        return $this->render('AppOrderformBundle/Patient/index.html.twig', array(
            'patiententities' => $entities,
        ));
    }

    /**
     * New Patient.
     *
     * @Route("/data-structure", name="scan-patient-new")
     * @Method("GET")
     * @Template("AppOrderformBundle/Patient/new.html.twig")
     */
    public function newPatientAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if user has at least one institution
        $securityUtil = $this->get('user_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil = $this->get('scanorder_utility');
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil = $this->get('scanorder_utility');
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }

        $thisparams = array(
            'objectNumber' => 2,
            'dropzoneImageNumber' => 1,
            'pacsvendorImageNumber' => 1,
            'withorders' => true,
            'testpatient' => true,
            'accession.attachmentContainer' => 1,
            'part.attachmentContainer' => 1,
            'block.attachmentContainer' => 1
        );


        $patient = $this->createPatientDatastructure($thisparams);
        //$patient = $res['patient'];

        $disabled = true;
        //$disabled = false; //testing

        $params = array(
            'type' => 'multy',
            'cycle' => 'new',
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            'sitename' => $this->container->getParameter('scan.sitename'),
            'datastructure' => 'datastructure', //'datastructure-patient'
        );

        //message fields
        $params['endpoint.system'] = true;
        $params['message.orderdate'] = false;   //true;
        $params['message.provider'] = true;
        $params['message.proxyuser'] = true;
        $params['message.externalIds'] = true;
        $params['message.idnumber'] = false;
        $params['message.sources'] = false;
        $params['message.destinations'] = false;
        $params['message.inputs'] = false;
        $params['message.outputs'] = false;

        $labels = array(
            'proxyuser' => 'Signing Provider(s):',
        );
        $params['labels'] = $labels;

        //specific orders
//        $params['message.laborder'] = true;
//        $params['message.report'] = true;
//        $params['message.blockorder'] = true;
//        $params['message.slideorder'] = true;
//        $params['message.stainorder'] = true;

        $form = $this->createForm(PatientType::class, $patient, array(
                'form_custom_value' => $params,
                'form_custom_value_entity' => $patient,
                'disabled' => $disabled
        ));

        return array(
            'entity' => $patient,
            'form' => $form->createView(),
            'formtype' => 'Patient Data Structure',
            'type' => 'show',
            'cycle' => 'new',
            'datastructure' => 'datastructure',
            'sitename' => $this->container->getParameter('scan.sitename')
        );
    }


    /**
     * Finds and displays a Patient entity.
     *
     * @Route("/{id}", name="scan-patient-show")
     * @Route("/info/{id}", name="scan-patient-info-show")
     * @Method("GET")
     * @Template("AppOrderformBundle/Patient/new.html.twig")
     */
    public function showAction( Request $request, $id )
    {

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        ) {
            return $this->redirect($this->generateUrl('scan-nopermission'));
        }


        $route = $request->get('_route');
        if( $route == "scan-patient-show" ) {
            $datastructure = 'datastructure';
        } else {
            $datastructure = 'datastructure-patient';
        }

        $parameters = array(
            'sitename' => $this->container->getParameter('scan.sitename'),
            'datastructure' => $datastructure,
            'tracker' => 'tracker',
            'editpath' => 'scan-patient-edit'
        );

        return $this->showPatient($request,$id,$parameters);
    }
    public function showPatient( $request, $id, $parameters ) {

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = $em->getRepository('AppOrderformBundle:Patient')->find($id);

//        $encounter = $entity->getEncounter()->first();
//        $procedure = $encounter->getProcedure()->first();
//        $accession = $procedure->getAccession()->first();
//        $parts = $accession->getPart();
//        foreach( $parts as $part ) {
//            foreach( $part->getDiseaseType() as $diseaseType ) {
//                $part->removeDiseaseType($diseaseType);
//            }
//        }

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Patient entity.');
        }

        //echo "fullname=".$entity->getFullPatientName(false)."<br>";
        //exit('1');

        $securityUtil = $this->get('user_security_utility');
        if( $entity && !$securityUtil->hasUserPermission($entity,$user,array("Union","Intersection"),array("show")) ) {
            //exit("showPatient: no permission to show patient");
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        //testing: Error: Out of memory ([currently] allocated 288620544) (tried to allocate [additional] 16777216 bytes)
        //currently= 288620544 bytes = 288,620 Kbytes = 288 Mbytes
        //additional= 16777216 bytes = 16,777 Kbytes = 16 Mbytes
        //459,538,432 bytes = 459,538 Kbytes = 459 Mbytes
//        if( $parameters['datastructure'] == 'datastructure' ) {
//            //echo "increase memory_limit <br>";
//            //ini_set('memory_limit', '-1'); //dangerous!
//            ini_set('memory_limit', '3072M');
//        } else {
//            //echo "no increase memory_limit <br>";
//            ini_set('memory_limit', '3072M');
//        }
        ini_set('memory_limit', '5120M');

//        $showtreedepth = true; //show all levels
//        if( array_key_exists('show-tree-depth',$parameters) ) {
//            $showtreedepth = intval($parameters['show-tree-depth']);
//        }
//        echo "showPatient: show-tree-depth=".$parameters['show-tree-depth']."<br>";
        //echo "showPatient: datastructure=".$parameters['datastructure']."<br>";

        //NOTE: if X=8, show only the first 8 levels (patient + encounter + procedure + accession + part + block + slide + image)
        //image is an 'attachmentContainer' field in AccessionType, PartType, BlockType ans 'scan' field in SlideType
        //BUT images are shown only if the 'datastructure' parameters is set to 'datastructure'.

        $params = array(
            'type' => 'multy',
            'cycle' => "show",
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            'sitename' => $parameters['sitename'],
            'datastructure' => $parameters['datastructure'],
            'tracker' => $parameters['tracker'],
            'show-tree-depth' => $parameters['show-tree-depth']
        );

        //message fields
        $params['endpoint.system'] = true;
        $params['message.orderdate'] = false; //true;
        $params['message.provider'] = true;
        $params['message.proxyuser'] = true;
        $params['message.externalIds'] = false;
        $params['message.idnumber'] = false;
        $params['message.sources'] = false;
        $params['message.destinations'] = false;
        $params['message.inputs'] = false;
        $params['message.outputs'] = false;

        $labels = array(
            'proxyuser' => 'Signing Provider(s):',
        );
        $params['labels'] = $labels;

        //$time_pre = microtime(true);

        $form = $this->createForm(PatientType::class,$entity,array(
            'form_custom_value' => $params,
            'form_custom_value_entity' => $entity,
            'disabled' => true
        ));

        //$time_post = microtime(true);
        //$exec_time = $time_post - $time_pre;
        //exit('form created: exec_time='.round($exec_time));
        //echo 'form created: exec_time='.round($exec_time)."<br>";
        //phpinfo();

        //LastName, FirstName, MiddleName | MRN Type: MRN | DOB: MM/DD/YY
        $title = $entity->obtainPatientInfoTitle('valid',null,false);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'formtype' => 'Patient Data Structure',
            'type' => 'show',
            'cycle' => 'show',
            'datastructure' => $parameters['datastructure'],
            'tracker' => $parameters['tracker'],
            'sitename' => $parameters['sitename'],
            'editpath' => $parameters['editpath'],
            'title' => $title,
            'titleheadroom' => $title
        );
    }

    /**
     * Displays a form to edit an existing Patient entity.
     *
     * @Route("/{id}/edit", name="scan-patient-edit")
     * @Method("GET")
     * @Template("AppOrderformBundle/Patient/new.html.twig")
     */
    public function editAction( Request $request, $id )
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        ) {
            return $this->redirect($this->generateUrl('scan-nopermission'));
        }

        $parameters = array(
            'sitename' => $this->container->getParameter('scan.sitename'),
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'updatepath' => 'scan_patient_update',
            'showPlus' => 'showPlus'
        );

        return $this->editPatient($request,$id,$parameters);
    }
    public function editPatient( $request, $id, $parameters ) {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppOrderformBundle:Patient')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Patient entity.');
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $securityUtil = $this->get('user_security_utility');
        if( $entity && !$securityUtil->hasUserPermission($entity,$user,array("Union"),array("edit")) ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $system = $securityUtil->getDefaultSourceSystem();

        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '5120M');

        //add tracker if does not exists
        if( !$entity->getTracker() ) {
            //$entity->addContactinfoByTypeAndName($user, $system);

            //$patientSpotPurpose = $em->getRepository('AppUserdirectoryBundle:SpotPurpose')->findOneByName("Initial Patient Encounter - Address Entry");
            //$spotEntityPatient = $em->getRepository('AppUserdirectoryBundle:Spot')->findOneBySpotPurpose($patientSpotPurpose);
            //$locationTypePrimary = $em->getRepository('AppUserdirectoryBundle:LocationTypeList')->findOneByName("Patient's Primary Contact Information");

            $locationTypePrimary = null;
            $spotEntityPatient = null;
            $withdummyfields = false; //true;

            $entity->addContactinfoByTypeAndName($user,$system,$locationTypePrimary,"Patient's Current Location",$spotEntityPatient,$withdummyfields,$em);

            //echo "spots=".count($entity->getTracker()->getSpots())."<br>";
        }

        if( count($entity->getEncounter()) == 0 ) {
            //exit("no encounter");
            $encounter = new Encounter(true,'valid',$user,$system);
            $encounter->setProvider($user);
            $entity->addEncounter($encounter); //add new encounter to patient
        }


        //////////////// params ////////////////
        $params = array(
            'type' => 'multy',
            'cycle' => "edit",
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            'sitename' => $parameters['sitename'],
            'datastructure' => $parameters['datastructure'],
            'tracker' => $parameters['tracker'],
            'show-tree-depth' => $parameters['show-tree-depth']
        );

        $params['endpoint.system'] = true;
        $params['message.orderdate'] = false; //true;
        $params['message.provider'] = true;
        $params['message.proxyuser'] = true;
        $params['message.externalIds'] = false;
        $params['message.idnumber'] = false;
        $params['message.sources'] = false;
        $params['message.destinations'] = false;
        $params['message.inputs'] = false;
        $params['message.outputs'] = false;

        $labels = array(
            'proxyuser' => 'Signing Provider(s):',
        );
        $params['labels'] = $labels;
        //////////////// EOF params ////////////////

        $form = $this->createForm(PatientType::class,$entity,array(
            'form_custom_value' => $params,
            'form_custom_value_entity' => $entity,
        ));


        //$deleteForm = $this->createDeleteForm($id);

//        return array(
//            'entity'      => $entity,
//            'edit_form'   => $form->createView(),
//            //'delete_form' => $deleteForm->createView(),
//        );

        //LastName, FirstName, MiddleName | MRN Type: MRN | DOB: MM/DD/YY
        $title = $entity->obtainPatientInfoTitle('valid',null,false);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'formtype' => 'Update Patient Data Structure',
            'type' => 'edit',//'edit' 'show'
            'cycle' => 'edit',
            'datastructure' => $parameters['datastructure'],
            'tracker' => $parameters['tracker'],
            'updatepath' => $parameters['updatepath'],
            'sitename' => $parameters['sitename'],
            'showPlus' => $parameters['showPlus'],
            'title' => $title, //formtype
            'titleheadroom' => $title
        );
    }

    /**
     * Edits an existing Patient entity.
     *
     * @Route("/{id}/edit", name="scan_patient_update")
     * @Method("POST")
     * @Template("AppOrderformBundle/Patient/new.html.twig")
     */
    public function updateAction( Request $request, $id )
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        ) {
            return $this->redirect($this->generateUrl('scan-nopermission'));
        }

        $parameters = array(
            'sitename' => $this->container->getParameter('scan.sitename'),
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'updatepath' => 'scan_patient_update',
            'showpath' => 'scan-patient-info-show'
        );

        return $this->updatePatient($request,$id,$parameters);
    }
    public function updatePatient( $request, $id, $parameters ) {   //$datastructure, $showpath, $updatepath) {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppOrderformBundle:Patient')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Patient entity.');
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $securityUtil = $this->get('user_security_utility');
        if ($entity && !$securityUtil->hasUserPermission($entity, $user, array("Union"), array("edit"))) {
            return $this->redirect($this->generateUrl('scan-nopermission'));
        }

        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '5120M');

        //assign temp source and user for updated array fields
        $tempSource = $securityUtil->getDefaultSourceSystem($parameters['sitename']);
        $entity->setTempSource($tempSource);
        $entity->setTempUser($user);

        //exit("2 Form scan_patient_update");

        //$deleteForm = $this->createDeleteForm($id);
        //////////////// params ////////////////
        $params = array(
            'type' => 'multy',
            'cycle' => "edit",
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            'sitename' => $parameters['sitename'],
            'datastructure' => $parameters['datastructure'],
            'tracker' => $parameters['tracker']
        );

        $params['endpoint.system'] = true;
        $params['message.orderdate'] = false; //true;
        $params['message.provider'] = true;
        $params['message.proxyuser'] = true;
        $params['message.externalIds'] = false;
        $params['message.idnumber'] = false;
        $params['message.sources'] = false;
        $params['message.destinations'] = false;
        $params['message.inputs'] = false;
        $params['message.outputs'] = false;

        $labels = array(
            'proxyuser' => 'Signing Provider(s):',
        );
        $params['labels'] = $labels;
        //////////////// EOF params ////////////////
        $form = $this->createForm(PatientType::class,$entity,array(
            'form_custom_value' => $params,
            'form_custom_value_entity' => $entity,
        ));

        //exit("3 Form scan_patient_update");

        //$editForm->submit($request);
        $form->handleRequest($request);

        //exit("4 Form scan_patient_update");

        if (0) {
            $errorHelper = new ErrorHelper();
            $errors = $errorHelper->getErrorMessages($form);
            echo "<br>form errors:<br>";
            print_r($errors);

            echo "loc errors:<br>";
            print_r($form->getErrors());
            //echo "<br>loc string errors:<br>";
            //print_r($form->getErrorsAsString());
            //echo "<br>";
            exit();
        }


        if( $form->isValid() ) {

            //set patient's name if does not exists
            //$em->getRepository('AppOrderformBundle:Patient')->copyCommonEncountersFieldsToPatient($entity,$user,$parameters['sitename']);
            //echo "<br><br>";
            //foreach( $entity->getLastname() as $lastname ) {
            //    echo $lastname->getStatus()." ID#".$lastname->getId().": lastname=".$lastname."<br>";
            //}
            //exit('1');
            //set provider, source, status='valid'. All other fields - 'invalid'. This is done by form listener

//            echo "<br><br>";
//            foreach( $entity->getDob() as $dob ) {
//                //echo "Controller: provider=".$dob->getProvider()."<br>";
//                //echo "Controller: provider id=".$dob->getProvider()->getId()."<br>";
//                //echo "Controller: source id=".$dob->getSource()->getId().": ".$dob->getSource()."<br>";
//                echo "Controller: parentId=".$dob->getParent()->getId()."; dob id=".$dob->getId()."; dob=".$dob."; status=".$dob->getStatus()."; provider =(ID#".$dob->getProvider()->getId().")".$dob->getProvider()."<br>";
//            }

            //get patient's changes
            $changeSetStr = $entity->obtainChangeObjectStr();

//            echo "changeSetStr:<br>";
//            echo $changeSetStr;
//            exit('1');

            //we might have newly added not persisted encounter without ID
            foreach( $entity->getEncounter() as $encounter ) {
                //echo "ID=".$encounter->getId()."; creationdate=".$encounter->getCreationDate()."<br>";
                if( !$encounter->getCreationdate() ) {
                    $em->persist($encounter);
                }
            }

            //exit("Form is valid");
            $em->persist($entity);
            $em->flush();

            //DO IT AFTER UPDATE DB: set patient's common fields (names, suffix and gender) for the latest modified encounter.
            // The latest encounter fields will be copy to the patient object. They can come from different encounters
            $em->getRepository('AppOrderformBundle:Patient')->copyCommonLatestEncounterFieldsToPatient($entity,$user,$parameters['sitename']);
            $em->persist($entity); //entity is a patient object
            $em->flush();

            if( $changeSetStr ) {
                $userSecUtil = $this->container->get('user_security_utility');
                //$user = $em->getRepository('AppUserdirectoryBundle:User')->find($user->getId());
                $event = "Patient with ID " . $entity->getId() . " has been updated by " . $user;
                $event .= ". Changes:<br>".$changeSetStr;
                $userSecUtil->createUserEditEvent($parameters['sitename'], $event, $user, $entity, $request, 'Patient Updated');
            }
            //exit('event='.$event);

            return $this->redirect($this->generateUrl($parameters['showpath'], array('id' => $id)));
        }
        //exit("Form is not valid");

        $this->get('session')->getFlashBag()->add(
            'warning',
            'Form is invalid.'
        );

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'formtype' => 'Update Patient Data Structure',
            'type' => 'show',
            'cycle' => 'edit',
            'datastructure' => $parameters['datastructure'],
            'tracker' => $parameters['tracker'],
            'updatepath' => $parameters['updatepath'],
            'sitename' => $parameters['sitename']
        );
    }


    /**
     * Creates a form to delete a Patient entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm()
        ;
    }






    //create Test Patient
    /**
     * @Route("/data-structure/new-test-patient", name="scan_testpatient_new")
     * @Method("GET")
     * @Template("AppOrderformBundle/Patient/new.html.twig")
     */
    public function newTestPatientAction() {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $securityUtil = $this->get('user_security_utility');
        $status = 'valid';
        $system = $securityUtil->getDefaultSourceSystem();
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if user has at least one institution
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil = $this->get('scanorder_utility');
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil = $this->get('scanorder_utility');
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }

        ///////////////////// prepare test patient /////////////////////
        $thisparams = array(
            'objectNumber' => 2,                //2 - generate 2 encounters (Note: it takes 40 sec (17 sec to make form on server) to render patient with 2 objects; it takes 15 sec for 1 object)
            'dropzoneImageNumber' => 1,
            'pacsvendorImageNumber' => 1,
            'withorders' => true,               //add orders to correspondent entities
            'persist' => true,                 //persist each patient hierarchy object (not used)
            'flush' => true,                    //flush after each object creation
            'testpatient' => true,              //populate patient hierarchy with default data
            'accession.attachmentContainer' => 1,
            'part.attachmentContainer' => 1,
            'block.attachmentContainer' => 1
        );
        $patient = $this->createPatientDatastructure($thisparams);
        //$patient = $res['patient'];
        //$slides = $res['slides'];
        ///////////////////// EOF prepare test patient /////////////////////

        //echo "messages=".count($patient->getMessage())."<br>";

        ///////////////////// populate patient with mrn, mrntype, name etc. /////////////////////
        $mrntypeStr = 'Test Patient MRN';
        $testpatients = $em->getRepository('AppOrderformBundle:Patient')->findByMrntypeString($mrntypeStr);
        $testpatientmrnIndex = count($testpatients)+1;

        //mrn
        $mrntypeTransformer = new MrnTypeTransformer($em,$user);
        $mrntype = $mrntypeTransformer->reverseTransform($mrntypeStr);
        //echo "mrntype id=".$mrntype->getId()."<br>";
        //$patientMrn = new PatientMrn($status,$user,$system);
        $patientMrn = $patient->getMrn()->first();
        $patientMrn->setKeytype($mrntype);
        $patientMrn->setField('testmrn-'.$testpatientmrnIndex);
        $patient->addMrn($patientMrn);

        //lastname
        $patientLastname = new PatientLastName($status,$user,$system);
        $patientLastname->setField('TestLastname');
        $patient->addLastname($patientLastname);
        //firstname
        $patientFirstname = new PatientFirstName($status,$user,$system);
        $patientFirstname->setField('TestFirstname');
        $patient->addFirstname($patientFirstname);
        //middlename
        $patientMiddlename = new PatientMiddleName($status,$user,$system);
        $patientMiddlename->setField('TestMiddlename');
        $patient->addMiddlename($patientMiddlename);

        //sex
        $patientSex = new PatientSex($status,$user,$system);
        $sex = $em->getRepository('AppUserdirectoryBundle:SexList')->findOneByName('Female');
        $patientSex->setField($sex);
        $patient->addSex($patientSex);

        //dob
        //$patientDob = new PatientDob($status,$user,$system);
        $patientDob = $patient->getDob()->first();
        $patientDob->setField( new \DateTime('01/30/1915') );
        $patient->addDob($patientDob);

        //clinical history
        //$patientClinHist = new PatientClinicalHistory($status,$user,$system);
        $patientClinHist = $patient->getClinicalHistory()->first();
        $patientClinHist->setField('Test Clinical History');
        $patient->addClinicalHistory($patientClinHist);
        ///////////////////// EOF populate patient with mrn, mrntype, name etc. /////////////////////


        ///////////////////// populate accession with accession number, accession type, etc. /////////////////////
        $accessiontypeStr = 'Test Accession Number';

        //accession
        $accessiontypeTransformer = new AccessionTypeTransformer($em,$user);
        $accessiontype = $accessiontypeTransformer->reverseTransform($accessiontypeStr);
        //echo "accessiontype id=".$accessiontype->getId()."<br>";

        $encounterCount = 0;
        foreach( $patient->getEncounter() as $encounter ) {

            //set encounter age
            //$encounterAge = new EncounterPatage($status,$user,$system);
            //$encounterAge->setField($patient->calculateAgeInt());
            //$encounter->addPatage($encounterAge);

            //set encounter date
            //$encounterdate = new EncounterDate($status,$user,$system);
            //$encounter->addDate($encounterdate);

            $accession = $encounter->getProcedure()->first()->getAccession()->first();
            //echo $accession;

            $testaccessions = $em->getRepository('AppOrderformBundle:Accession')->findByAccessiontypeString($accessiontypeStr);
            $testaccessionIndex = count($testaccessions)+$encounterCount;

            //$accessionNumber = new AccessionAccession($status,$user,$system);
            $accessionNumber = $accession->getAccession()->first();
            $accessionNumber->setKeytype($accessiontype);
            $accessionNumber->setField('testaccession-'.$testaccessionIndex);
            $accession->addAccession($accessionNumber);

            $encounterCount++;

            //block staintype

            //testing!!! add document to autopsy image
//            $accessionAttachmentContainer = $accession->getAttachmentContainer();
//            $accessionDocContainer = $accessionAttachmentContainer->getdocumentContainers()->first();
//            //add document to DocumentContainer
//            $uniqueName = 'testimage_5522979c2e736.jpg';
//            $autopsydocument = $em->getRepository('AppUserdirectoryBundle:Document')->findOneByUniquename($uniqueName);
//            $accessionDocContainer->addDocument($autopsydocument);

        }
        ///////////////////// EOF populate accession with accession number, accession type, etc. /////////////////////

        $MultiSlideScanOrder = $patient->getMessage()->first();
        //echo "multi-scan message count=".count($messageMultiSlideScanOrder)."<br>";

        //create scan order first; patient hierarchy will be created as well.
        $MultiSlideScanOrder = $em->getRepository('AppOrderformBundle:Message')->processMessageEntity( $MultiSlideScanOrder, $user, null, $this->get('router'), $this->container );

        if( $patient->getId() ) {
            return $this->redirect( $this->generateUrl('scan-patient-show',array('id'=>$patient->getId())) );
        } else {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Failed to create a test patient'
            );
            return $this->redirect( $this->generateUrl('scan-patient-list') );
        }

    }

//    public function linkMessagesInPatient( $patient ) {
//
//        $em = $this->getDoctrine()->getManager();
//
//        $messages = $patient->getMessage();
//        //echo "<br><br>link Messages InPatient messages=".count($messages)."<br>";
//
//        foreach( $messages as $message ) {
//
//            //echo "<br>";
//            //echo $message." => 1 inputs count=".count($message->getInputs())."<br>";
//
//            foreach( $message->getInputs() as $input ) {
//                //echo "1 input=".$input."<br>";
//                if( !$input->getEntityId() ) {
//                    $className = $input->getEntityName();
//                    //echo "className=".$className."<br>";
//                    $getMethod = 'get'.$className;  //getImaging()
//                    $objects = $message->$getMethod();
//                    foreach( $objects as $object ) {
//                        if( !$input->getEntityId() ) {
//                            //echo "className=".$className.": set input object=".$object;
//                            $input->setObject($object);
//                        }
//                    }
//                }
//                //echo "2 input=".$input."<br>";
//            }
//
//            foreach( $message->getOutputs() as $output ) {
//                //echo "1 output=".$output."<br>";
//                if( !$output->getEntityId() ) {
//                    $className = $output->getEntityName();
//                    //echo "className=".$className."<br>";
//                    $getMethod = 'get'.$className;
//                    $objects = $message->$getMethod();
//                    foreach( $objects as $object ) {
//                        if( !$output->getEntityId() ) {
//                            //echo "className=".$className." set output object=".$object."<br>";
//                            $output->setObject($object);
//                        }
//                    }
//                }
//                //echo "2 output=".$output."<br>";
//            }
//            //echo $message." => 2 inputs count=".count($message->getInputs())."<br>";
//
//            $em->persist($message);
//        }
//
//        //exit('1');
//        $em->flush();
//    }


    public function linkMessageObject( $message, $object, $objectType='input', $addObject=true, $forceAddObject=true ) {

        //echo "<br>";
        //echo "addObject=".$addObject."<br>";
        //echo "link message with category=".$message->getMessageCategory()->getName()."<br>";
        //foreach( $message->getInputs() as $input ) {
        //    echo "input=".$input->getFullName()."<br>";
        //}

        //add message to object
        $object->addMessage($message);

        //add object to message
        $class = new \ReflectionClass($object);
        $className = $class->getShortName();
        $addMethod = 'add'.$className;
        $message->$addMethod($object);

        //set object as message input
        if( $addObject ) {
            if( $forceAddObject || $object->getId() ) {
                if( $objectType == 'input' ) {
                    //echo "add object $className as input, entityId=".$object->getId()."<br>";
                    $message->addInputObject($object);
                }
                if( $objectType == 'output' ) {
                    //echo "add object $className as output <br>";
                    $message->addOutputObject($object);
                }
            }
        }
    }



    public function createPatientDatastructure( $params ) {

        if( array_key_exists('withfields', $params) ) {
            $withfields = $params['withfields'];
        } else {
            $withfields = true;
        }

        if( array_key_exists('persist', $params) ) {
            $persist = $params['persist'];
        } else {
            $persist = false;
        }

        if( array_key_exists('objectNumber', $params) ) {
            $objectNumber = $params['objectNumber'];
        } else {
            $objectNumber = 1;
        }

        if( array_key_exists('dropzoneImageNumber', $params) ) {
            $dropzoneImageNumber = $params['dropzoneImageNumber'];
        } else {
            $dropzoneImageNumber = 0;
        }

        if( array_key_exists('pacsvendorImageNumber', $params) ) {
            $pacsvendorImageNumber = $params['pacsvendorImageNumber'];
        } else {
            $pacsvendorImageNumber = 0;
        }

        if( array_key_exists('withorders', $params) ) {
            $withOrders = $params['withorders'];
        } else {
            $withOrders = false;
        }

        if( array_key_exists('withscanorder', $params) ) {
            $withscanorder = $params['withscanorder'];
        } else {
            $withscanorder = true;
        }

        if( array_key_exists('accession.attachmentContainer', $params) ) {
            $attachmentContainerAccessionNumber = $params['accession.attachmentContainer'];
        } else {
            $attachmentContainerAccessionNumber = 0;
        }

        if( array_key_exists('part.attachmentContainer', $params) ) {
            $attachmentContainerPartNumber = $params['part.attachmentContainer'];
        } else {
            $attachmentContainerPartNumber = 0;
        }

        if( array_key_exists('block.attachmentContainer', $params) ) {
            $attachmentContainerBlockNumber = $params['block.attachmentContainer'];
        } else {
            $attachmentContainerBlockNumber = 0;
        }

        if( array_key_exists('testpatient', $params) ) {
            $testpatient = $params['testpatient'];
        } else {
            $testpatient = false;
        }

        if( array_key_exists('flush', $params) ) {
            $flush = $params['flush'];
        } else {
            $flush = false;
        }

        $em = $this->getDoctrine()->getManager();
        $securityUtil = $this->get('user_security_utility');

        $system = $securityUtil->getDefaultSourceSystem();
        $status = 'valid';
        $user = $this->get('security.token_storage')->getToken()->getUser();


        //////////////////////////// get lists ////////////////////////////////////
        $uniqueName = 'testimage_5522979c2e736.jpg';
        $document = $em->getRepository('AppUserdirectoryBundle:Document')->findOneByUniquename($uniqueName);
        //echo "document=".$document."<br>";

        if( !$document ) {
            $document = new Document($user);
            $document->setCleanOriginalname('testimage.jpg');
            $document->setUniquename($uniqueName);
            //$dir = 'Uploaded/scan-order/documents';
            //scan.uploadpath
            $dir = 'Uploaded/'.$this->container->getParameter('scan.uploadpath');
            $document->setUploadDirectory($dir);
            $filename = $dir."/".$uniqueName;
            if( file_exists($filename) ) {
                $imagesize = filesize($filename);
                //echo "The imagesize=$imagesize<br>";
                $document->setSize($imagesize);
            } else {
                //copy file to

                $originalFile = __DIR__."/../../UserdirectoryBundle/Util/".$uniqueName;
                if( !file_exists($originalFile) ) {
                    throw new \Exception( 'There is no original file '.$originalFile );
                }
                if( !file_exists($dir) ) {
                    // 0700 - Read and write, execute for owner, nothing for everybody else
                    mkdir($dir, 0700, true);
                    chmod($dir, 0700);
                    //throw new \Exception( 'There is no dir '.$dir );
                }
                if( !copy($originalFile,$filename) ) {
                    throw new \Exception( 'Copy Failed: the file '.$filename.' does not exist. Please copy this file to public/'.$dir );
                }

            }

            if( !file_exists($filename) ) {
                throw new \Exception( 'The file '.$filename.' does not exist. Please copy this file to public/'.$dir );
            }
        }

        $staintype = $em->getRepository('AppOrderformBundle:StainList')->find(1);
        $organList = $em->getRepository('AppOrderformBundle:OrganList')->findOneByName('Breast');
        $slidetype = $em->getRepository('AppOrderformBundle:SlideType')->findOneByName('Frozen Section');

        $sourceSystemName = 'PACS on C.MED.CORNELL.EDU';
        $sourceSystemPacsvendor = $em->getRepository('AppUserdirectoryBundle:SourceSystemList')->findOneByName($sourceSystemName);

        $maginification = $em->getRepository('AppOrderformBundle:Magnification')->findOneByName('20X');

        $neoplasticType = $em->getRepository('AppOrderformBundle:DiseaseTypeList')->findOneByName('Neoplastic');
        $metastaticOrigin = $em->getRepository('AppOrderformBundle:DiseaseOriginList')->findOneByName('Metastatic');

        //Input: Slide Id from c.med: 42814
        $slideId = 42814;
        //////////////////////////// EOF get lists ////////////////////////////////////

        $patient = new Patient($withfields,$status,$user,$system);
        $patient->addExtraFields($status,$user,$system);

        //add two contactinfo: "Test Patient's Primary Residence" and "Test Patient's Secondary Residence"
        $patientSpotPurpose = $em->getRepository('AppUserdirectoryBundle:SpotPurpose')->findOneByName("Initial Patient Encounter - Address Entry");
        $spotEntityPatient = $em->getRepository('AppUserdirectoryBundle:Spot')->findOneBySpotPurpose($patientSpotPurpose);
        $locationTypePrimary = $em->getRepository('AppUserdirectoryBundle:LocationTypeList')->findOneByName("Patient's Primary Contact Information");
        $patient->addContactinfoByTypeAndName($user,$system,$locationTypePrimary,"Test Patient's Primary Residence",$spotEntityPatient,true,$em);
        $locationType = $em->getRepository('AppUserdirectoryBundle:LocationTypeList')->findOneByName("Patient's Contact Information");
        $patient->addContactinfoByTypeAndName($user,$system,$locationType,"Test Patient's Secondary Residence",$spotEntityPatient,true,$em);

        if( $withscanorder ) {
            $MultiSlideScanOrder = $this->createSpecificMessage("Multi-Slide Scan Order");
            $patient->addMessage($MultiSlideScanOrder);
        }

        if( $persist ) {
            $em->persist($patient);
        }

        //$slideArr = array();

        for( $countObject = 0; $countObject < $objectNumber; $countObject++ ) {

            $encounter = new Encounter($withfields,$status,$user,$system);
            $encounter->addExtraFields($status,$user,$system);
            //$encounter->setStatus($status."_".$countObject);
            $patient->addEncounter($encounter);

            if( $persist ) {
                $em->persist($encounter);
            }

            if( $testpatient ) {
                $encounter->getDate()->first()->setField(new \DateTime());
            }

            $procedure = new Procedure($withfields,$status,$user,$system);
            $procedure->addExtraFields($status,$user,$system);
            $encounter->addProcedure($procedure);

            if( $persist ) {
                $em->persist($procedure);
            }

            if( $testpatient ) {
                $procedure->getDate()->first()->setField(new \DateTime());
            }

            $accession = new Accession($withfields,$status,$user,$system);
            $accession->addExtraFields($status,$user,$system);
            $procedure->addAccession($accession);

            if( $persist ) {
                $em->persist($accession);
            }

            if( $testpatient ) {
                $accession->getAccessionDate()->first()->setField(new \DateTime());
            }

            $part = new Part($withfields,$status,$user,$system);
            //$part->addExtraFields($status,$user,$system);
            $accession->addPart($part);

            if( $persist ) {
                $em->persist($part);
            }

            if( $testpatient ) {
                $partname = $part->obtainValidField('partname');
                $partname->setField('A');
                $sourceOrgan = $part->obtainValidField('sourceOrgan');

                $sourceOrgan->setField($organList);

                //set the "Type of Disease" in Part to "Neoplastic" and "Metastatic" to show the child and grandchild questions.
                $typeDisease = $part->obtainValidField('diseaseType');
                //echo "DiseaseType count=".count($typeDisease->getDiseaseTypes())."<br>";
                //echo "DiseaseOrigin count=".count($typeDisease->getDiseaseOrigins())."<br>";
                //exit('1');
                $typeDisease->addDiseaseType($neoplasticType);
                $typeDisease->addDiseaseOrigin($metastaticOrigin);
                $typeDisease->setPrimaryOrgan($organList);
            }

            $block = new Block($withfields,$status,$user,$system);

            //set specialStains to null
            $blockSpecialstain = $block->getSpecialStains()->first();
            $blockSpecialstain->setStaintype($staintype);
            //$blockSpecialstain->setField('stain ' . $staintype);
            //echo "specialStain field=".$blockSpecialstain->getField()."<br>";
            //echo "specialStain staintype=".$blockSpecialstain->getStaintype()."<br>";

            $part->addBlock($block);

            if( $persist ) {
                $em->persist($block);
            }

            if( $testpatient ) {
                $blockname = $block->obtainValidField('blockname');
                $blockname->setField('1');
                $sectionsource = $block->obtainValidField('sectionsource');
                $sectionsource->setField('Test Section Source');
            }

//            $em = $this->getDoctrine()->getManager();
//            $Staintype = $em->getRepository('AppOrderformBundle:StainList')->find(1);
//            $block->getSpecialStains()->first()->setStaintype($Staintype);
//            echo $block;
//            echo "staintype=".$block->getSpecialStains()->first()->getStaintype()->getId()."<br>";


            $slide = new Slide($withfields,'valid',$user,$system); //Slides are always valid by default
            $slide->clearScan();
            //$slide->addExtraFields($status,$user,$system);
            $block->addSlide($slide);

            if( $persist ) {
                $em->persist($slide);
            }

            if( $testpatient ) {
                //set stain

                $slide->getStain()->first()->setField($staintype);

                //set slide title
                $slide->setTitle('Test Slide ' . $countObject);

                //set slide type
                $slide->setSlidetype($slidetype);
            }

            //$slideArr[] = $slide;

            //add scan (Imaging) to a slide
            if( $dropzoneImageNumber > 0 || $pacsvendorImageNumber > 0 ) {
                $slide->clearScan();
            }

            //attach one existing dropzone image
            for( $countImage = 0; $countImage < $dropzoneImageNumber; $countImage++ ) {
                $dropzoneImage = new Imaging('valid',$user,$system);

                if( $testpatient ) {
                    $dropzoneImage->setMagnification($maginification);

                    //set imageid
                    $dropzoneImage->setImageId('testimage_id_'.$countImage);
                    $docContainer = $dropzoneImage->getDocumentContainer();

                    if( !$docContainer ) {
                        $docContainer = new DocumentContainer($user);
                        $dropzoneImage->setDocumentContainer($docContainer);
                    }

                    //set Document Container
                    $docContainer->setTitle('Test Image');
                    $docContainer->addDocument($document);
                } //if testpatient

                //add scan to slide
                $slide->addScan($dropzoneImage);

                if( $persist ) {
                    $em->persist($dropzoneImage);
                }
            }

            //attach one existing pacsvendor image http://c.med.cornell.edu/EditRecord.php?TableName=Slide&Ids[]=42814,
            //image ID:73660
            //image/pacsvendor/73660
            for( $countImage = 0; $countImage < $pacsvendorImageNumber; $countImage++ ) {
                $pacsvendorImage = new Imaging('valid',$user,$sourceSystemPacsvendor);

                if( $testpatient ) {
                    $pacsvendorImage->setMagnification($maginification);

                    //Input: Slide Id from c.med: 42814
                    //$slideId = 42814;
                    $pacsvendorImage->setImageId($slideId);

                    //get document container
                    $docContainer = $pacsvendorImage->getDocumentContainer();
                    if( !$docContainer ) {
                        $docContainer = new DocumentContainer($user);
                        $pacsvendorImage->setDocumentContainer($docContainer);
                    }

                    $this->setDocumentContainerWithLinks($docContainer,$slideId,$user);

                } //if testpatient

                //add Image to Slide
                $slide->addScan($pacsvendorImage);

                if( $persist ) {
                    $em->persist($pacsvendorImage);
                }
            }

            //Accession: add n autopsy fields: add n documentContainers to attachmentContainer
            if( $attachmentContainerAccessionNumber > 0 ) {
                $attachmentContainerAccession = $accession->getAttachmentContainer();
                if( !$attachmentContainerAccession ) {
                    $attachmentContainerAccession = new AttachmentContainer();
                    $accession->setAttachmentContainer($attachmentContainerAccession);
                }
                for( $i=0; $i<$attachmentContainerAccessionNumber; $i++) {
                    $docContainer = new DocumentContainer($user);
                    //drop zone
                    if( $countObject == 0 ) {
                        $docContainer->setTitle('Test Image');
                        $docContainer->addDocument($document);
                    }
                    //link
                    if( $countObject == 1 ) {
                        $docContainer = $this->setDocumentContainerWithLinks($docContainer,$slideId,$user);
                    }
                    $attachmentContainerAccession->addDocumentContainer( $docContainer );
                }
            }

            //Part: add n gross image fields: add n documentContainers to attachmentContainer
            if( $attachmentContainerPartNumber > 0 ) {
                $attachmentContainerPart = $part->getAttachmentContainer();
                if( !$attachmentContainerPart ) {
                    $attachmentContainerPart = new AttachmentContainer();
                    $part->setAttachmentContainer($attachmentContainerPart);
                }
                for( $i=0; $i<$attachmentContainerPartNumber; $i++) {
                    $attachmentContainerPart->addDocumentContainer( new DocumentContainer($user) );
                }
            }

            //Block: add n gross image fields: add n documentContainers to attachmentContainer
            if( $attachmentContainerBlockNumber > 0 ) {
                $attachmentContainerBlock = $block->getAttachmentContainer();
                if( !$attachmentContainerBlock ) {
                    $attachmentContainerBlock = new AttachmentContainer();
                    $block->setAttachmentContainer($attachmentContainerBlock);
                }
                for( $i=0; $i<$attachmentContainerBlockNumber; $i++) {
                    $attachmentContainerBlock->addDocumentContainer( new DocumentContainer($user) );
                }
            }

            if( $flush ) {
                $em->flush();
            }

            /////////////////////// testing: create specific messages ///////////////////////
            if( $withOrders ) {

                if( $withscanorder ) {
                    //Multi-Slide Scan Order
                    $this->addSpecificMessage($MultiSlideScanOrder,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                    $this->linkMessageObject( $MultiSlideScanOrder, $slide, 'input' );
                }

                //Encounter Note
                $EncounterNote = $this->createSpecificMessage("Encounter Note");
                $this->addSpecificMessage($EncounterNote,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $EncounterNote, $encounter, 'input' );

                //Procedure Order
                $ProcedureOrder = $this->createSpecificMessage("Procedure Order");
                $this->addSpecificMessage($ProcedureOrder,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $ProcedureOrder, $encounter, 'input' );
                $this->linkMessageObject( $ProcedureOrder, $procedure, 'output' );

                //Procedure Note
                $ProcedureNote = $this->createSpecificMessage("Procedure Note");
                $this->addSpecificMessage($ProcedureNote,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $ProcedureNote, $procedure, 'input' );

                //Lab Order Requisition
                $LabOrderRequisition = $this->createSpecificMessage("Lab Order Requisition");
                $this->addSpecificMessage($LabOrderRequisition,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $LabOrderRequisition, $accession, 'output' );
                $this->linkMessageObject( $LabOrderRequisition, $procedure, 'input' );

                //Lab Report
                $Report = $this->createSpecificMessage("Lab Report");
                $this->addSpecificMessage($Report,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $Report, $accession, 'input' );
                $this->linkMessageObject( $Report, $procedure, 'output' );

                //set "Order" as source for "Report"
                $LabOrderRequisition->addAssociation($Report);
                $Report->addBackAssociation($LabOrderRequisition);

                //Embed Block Order
                $EmbedBlockOrder = $this->createSpecificMessage("Embed Block Order");
                $this->addSpecificMessage($EmbedBlockOrder,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $EmbedBlockOrder, $part, 'input' );
                $this->linkMessageObject( $EmbedBlockOrder, $block, 'output' );

                //Block Report
                $BlockReport = $this->createSpecificMessage("Block Report");
                $this->addSpecificMessage($BlockReport,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $BlockReport, $block, 'input' );
                $this->linkMessageObject( $BlockReport, $part, 'output' );

                //set "Embed Block Order" as source for "Block Report"
                $EmbedBlockOrder->addAssociation($BlockReport);
                $BlockReport->addBackAssociation($EmbedBlockOrder);

                //Autopsy Images
                $AutopsyImages = $this->createSpecificMessage("Autopsy Images");
                $this->addSpecificMessage($AutopsyImages,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $AutopsyImages, $accession, 'input' );

                //Gross Images
                $GrossImages = $this->createSpecificMessage("Gross Images");
                $this->addSpecificMessage($GrossImages,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $GrossImages, $part, 'input' );

                //Block Images
                $BlockImages = $this->createSpecificMessage("Block Images");
                $this->addSpecificMessage($BlockImages,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $BlockImages, $block, 'input' );

                //Outside Report to Part
                $OutsideReportPart = $this->createSpecificMessage("Outside Report");
                $this->addSpecificMessage($OutsideReportPart,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $OutsideReportPart, $part, 'input' );

                //Outside Report to Accession
                $OutsideReportAccession = $this->createSpecificMessage("Outside Report");
                $this->addSpecificMessage($OutsideReportAccession,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $OutsideReportAccession, $accession, 'input' );

                //Slide Order
                $SlideOrder = $this->createSpecificMessage("Slide Order");
                $this->addSpecificMessage($SlideOrder,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $SlideOrder, $block, 'input' );

                //Slide Report
                $SlideReport = $this->createSpecificMessage("Slide Report");
                $this->addSpecificMessage($SlideReport,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $SlideReport, $slide, 'input' );
                $this->linkMessageObject( $SlideReport, $block, 'output' );

                //set "Order" as source for "Report"
                $SlideOrder->addAssociation($SlideReport);
                $SlideReport->addBackAssociation($SlideOrder);

                //Stain Slide Order
                $StainSlideOrder = $this->createSpecificMessage("Stain Slide Order");
                $this->addSpecificMessage($StainSlideOrder,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $StainSlideOrder, $slide, 'input' );
                $this->linkMessageObject( $StainSlideOrder, $slide, 'output' );

                //Stain Report
                $StainReport = $this->createSpecificMessage("Stain Report");
                $this->addSpecificMessage($StainReport,$patient,$encounter,$procedure,$accession,$part,$block,$slide);
                $this->linkMessageObject( $StainReport, $slide, 'input' );

                //set "Order" as source for "Report"
                $StainSlideOrder->addAssociation($StainReport);
                $StainReport->addBackAssociation($StainSlideOrder);



                foreach( $slide->getScan() as $scan ) {
                    //Scan Report
                    $ScanReport = $this->createSpecificMessage("Scan Report");
                    $this->addSpecificMessage($ScanReport,$patient,$encounter,$procedure,$accession,$part,$block,$slide,$scan);
                    $this->linkMessageObject( $ScanReport, $scan, 'input' );
                    $this->linkMessageObject( $ScanReport, $slide, 'output' );
                    //set "Multi-Scan Order" as source for "Scan Report"
                    $MultiSlideScanOrder->addAssociation($ScanReport);
                    $ScanReport->addBackAssociation($MultiSlideScanOrder);

                    //Image Analysis Order
                    $ImageAnalysisOrder = $this->createSpecificMessage("Image Analysis Order");
                    $this->addSpecificMessage($ImageAnalysisOrder,$patient,$encounter,$procedure,$accession,$part,$block,$slide,$scan);
                    $this->linkMessageObject( $ImageAnalysisOrder, $scan, 'input' );

                    //Image Analysis Report
                    $AnalysisReport = $this->createSpecificMessage("Image Analysis Report");
                    $this->addSpecificMessage($AnalysisReport,$patient,$encounter,$procedure,$accession,$part,$block,$slide,$scan);
                    $this->linkMessageObject( $AnalysisReport, $scan, 'input' );

                    if( $withscanorder ) {
                        $this->linkMessageObject( $MultiSlideScanOrder, $scan, 'output' );
                    }

                    //set "Image Analysis Order" as source for "Image Analysis Report"
                    $ImageAnalysisOrder->addAssociation($AnalysisReport);
                    $AnalysisReport->addBackAssociation($ImageAnalysisOrder);
                }

            }
            /////////////////////// EOF specific messages ///////////////////////

        } //for $objectNumber

        ///////////// Referral Order /////////////
        if( $withOrders ) {
            $ReferralOrder = $this->createSpecificMessage("Referral Order");
            $encounters = $patient->getEncounter();
            if( count($encounters) > 0 ) {
                $encounterFirst = $encounters[0];
                //$encounterFirst->setId(1);
                //echo $encounterFirst->getStatus()."<br>";
                $this->addSpecificMessage($ReferralOrder,$patient,$encounterFirst,null,null,null,null,null);
                $this->linkMessageObject( $ReferralOrder, $encounterFirst, 'input' );
            }
            if( count($encounters) > 1 ) {
                $encounterSecond = $encounters[1];
                //$encounterSecond->setId(2);
                //echo $encounterSecond->getStatus()."<br>";
                $this->addSpecificMessage($ReferralOrder,$patient,$encounterSecond,null,null,null,null,null);
                $this->linkMessageObject( $ReferralOrder, $encounterSecond, 'output' );
            }
        }
        ///////////// EOF Referral Order /////////////

        if( $flush ) {
            $em->flush();
        }

//        $res = array(
//            'patient' => $patient,
//            'slides' => $slideArr
//        );

        return $patient;
    }

    public function addSpecificMessage($message,$patient,$encounter,$procedure,$accession,$part,$block,$slide,$scan=null) {

        if( $patient )
            $message->addPatient($patient);
        if( $encounter )
            $message->addEncounter($encounter);
        if( $procedure )
            $message->addProcedure($procedure);
        if( $accession )
            $message->addAccession($accession);
        if( $part )
            $message->addPart($part);
        if( $block )
            $message->addBlock($block);
        if( $slide )
            $message->addSlide($slide);

        if( $scan ) {
            $message->addImaging($scan);
        } else {
            if( $slide ) {
                foreach( $slide->getChildren() as $imaging ) {
                    $message->addImaging($imaging);
                }
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($message);
    }


    public function createSpecificMessage( $messageCategoryStr ) {

        $em = $this->getDoctrine()->getManager();
        $securityUtil = $this->get('user_security_utility');

        $userSecurity = $this->get('security.token_storage')->getToken()->getUser();
        $user = $em->getRepository('AppUserdirectoryBundle:User')->find($userSecurity->getId());

        $system = $securityUtil->getDefaultSourceSystem();

        //set scan order
        $message = new Message();
        //$scanOrder = new ScanOrder();
        //$scanOrder->setMessage($message);

        //set provider
        $message->setProvider($user);

        //add 2 proxyusers as Signing Provider(s)
        $this->addTwoSigningProviders($message);

        //set Source object
        $source = new Endpoint();
        $source->setSystem($system);
        $message->addSource($source);

        //set Destination object
        $destination = new Endpoint();
        $message->addDestination($destination);

        //add one ExternalId object
        //echo "add external Id object $messageCategoryStr <br>";
        $externalId = new ExternalId();
        $externalId->setExternalId('External ID 123');
        $externalId->setSourceSystem($system);
        $message->addExternalId($externalId);
        $message->addExternalId($externalId);

        //type
        $category = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($messageCategoryStr);
        //echo "category=".$category."<br>";
        if( !$category ) {
            throw $this->createNotFoundException('Unable to find MessageCategory bt name "' . $messageCategoryStr . '"');
        }
        $message->setMessageCategory($category);

        //set the default institution; check if user has at least one institution
        $orderUtil = $this->get('scanorder_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        if( !$userSiteSettings ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        if( count($permittedInstitutions) == 0 ) {
            $orderUtil->setWarningMessageNoInstitution($user);
            return $this->redirect( $this->generateUrl('scan_home') );
        }
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        $message->setInstitution($permittedInstitutions->first());


        //set default department and division
//        $defaultsDepDiv = $securityUtil->getDefaultDepartmentDivision($entity,$userSiteSettings);
//        $department = $defaultsDepDiv['department'];
//        $division = $defaultsDepDiv['division'];

        //set message status
        $orderStatus = $em->getRepository('AppOrderformBundle:Status')->findOneByName('Submitted');
        $message->setStatus($orderStatus);


        /////////////////// set specific message //////////////////////////////
        //add attachment with 1 documentContainer
        $attachmentContainerPart = $message->getAttachmentContainer();
        if( !$attachmentContainerPart ) {
            $attachmentContainerPart = new AttachmentContainer();
            $message->setAttachmentContainer($attachmentContainerPart);
        }
        for( $i = 0; $i < 1; $i++ ) {
            $attachmentContainerPart->addDocumentContainer( new DocumentContainer($user) );
        }


        //add this object to message and input
        //$object->addMessage($message);

        //set this object as order input
//        if( $addObjectToMessage ) {
//            $message->addInputObject($object);
//        }

        if( $messageCategoryStr == "Procedure Order" ) {
            $procedureorder = new ProcedureOrder();
            $procedureorder->setMessage($message);
            $message->setProcedureorder($procedureorder);

            //add 2 proxyusers
        }

        if( $messageCategoryStr == "Lab Order" || $messageCategoryStr == "Lab Order Requisition" ) {
            $laborder = new LabOrder();
            $laborder->setMessage($message);
            $message->setLaborder($laborder);

            //show Report Recipient(s)
            $UserWrapperTransformer = new UserWrapperTransformer($em, $this->container);
            $UserWrappers = $UserWrapperTransformer->reverseTransform($user."");
            $message->addReportRecipient($UserWrappers[0]);

            //add a field for the "Recipient Organization" with a title "Laboratory:"
            $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName("Molecular diagnostics");
            $organizationRecipient = new InstitutionWrapper();
            $organizationRecipient->setInstitution($institution);
            $message->addOrganizationRecipient($organizationRecipient);
        }

        if(
            $messageCategoryStr == "Report" ||
            $messageCategoryStr == "Lab Report" ||
            $messageCategoryStr == "Image Analysis Report" ||
            $messageCategoryStr == "Outside Report" ||
            $messageCategoryStr == "Slide Report" ||
            $messageCategoryStr == "Stain Report" ||
            $messageCategoryStr == "Scan Report"
        ) {

            $report = new Report();
            $report->setMessage($message);
            $message->setReport($report);

            //add 2 proxyusers

            //show Report Recipient(s)
            $UserWrapperTransformer = new UserWrapperTransformer($em, $this->container);
            $UserWrappers = $UserWrapperTransformer->reverseTransform($user."");
            $message->addReportRecipient($UserWrappers[0]);
        }

        if( $messageCategoryStr == "Block Report" ) {

            $report = new ReportBlock();
            $report->setMessage($message);
            $message->setReportBlock($report);

            //add 2 proxyusers
        }

        if( $messageCategoryStr == "Image Analysis Order" ) {
            $imageAnalysisOrder = new ImageAnalysisOrder();
            $imageAnalysisOrder->setMessage($message);
            $message->setImageAnalysisOrder($imageAnalysisOrder);

            $instruction = new Instruction($user);
            $imageAnalysisOrder->setInstruction($instruction);

            //Image Analysis Software: Indica HALO (destination(endpoint) -> SourceSystemList "Indica HALO")
            $indicaHALOSystem = $em->getRepository('AppUserdirectoryBundle:SourceSystemList')->findOneByName('Indica HALO');
            $destination->setSystem($indicaHALOSystem);

            //Image Analysis Algorithm:
            $imageAnalysisAlgorithm = $em->getRepository('AppOrderformBundle:ImageAnalysisAlgorithmList')->findOneByName('Break-Apart & Fusion FISH');
            $imageAnalysisOrder->setImageAnalysisAlgorithm($imageAnalysisAlgorithm);

            //Message Source: source(endpoint) -> SourceSystemList "ScanOrder": already set as default system
            //$scanOrderSystem = $em->getRepository('AppUserdirectoryBundle:SourceSystemList')->findOneByName('ScanOrder');
            //$source->setSystem($scanOrderSystem);

        }

        if( $messageCategoryStr == "Embed Block Order" ) {
            $blockorder = new BlockOrder();
            $blockorder->setMessage($message);
            $message->setBlockorder($blockorder);

            //$instruction = new Instruction($user);
            //$blockorder->setInstruction($instruction);

            //$em->persist($message);
        }

        if( $messageCategoryStr == "Slide Order" ) {
            $slideorder = new SlideOrder();
            $slideorder->setMessage($message);
            $message->setSlideorder($slideorder);


            $instruction = new Instruction($user);
            $slideorder->setInstruction($instruction);

            //$em->persist($message);
        }

        if( $messageCategoryStr == "Stain Slide Order" ) {
            $stainorder = new StainOrder();
            $stainorder->setMessage($message);
            $message->setStainorder($stainorder);

            $instruction = new Instruction($user);
            $stainorder->setInstruction($instruction);

            //$em->persist($message);
        }

        if( $messageCategoryStr == "Multi-Slide Scan Order" ) {
            $scanorder = new ScanOrder();
            $scanorder->setMessage($message);
            $message->setScanorder($scanorder);
        }

        if( $messageCategoryStr == "Referral Order" ) {

            //show Order Recipient(s) with title Refer To Individual
            $UserWrapperTransformer = new UserWrapperTransformer($em, $this->container);
            $UserWrappers = $UserWrapperTransformer->reverseTransform($user."");
            $message->addOrderRecipient($UserWrappers[0]);

            //add a field for the "Recipient Organization" with a title "Refer to Organization:"
            $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName("New York Hospital");
            $organizationRecipient = new InstitutionWrapper();
            $organizationRecipient->setInstitution($institution);
            $message->addOrganizationRecipient($organizationRecipient);
        }

        /////////////////// EOF set specific message //////////////////////////////




        //echo $message;
        //echo "message institution=".$message->getInstitution()->getName()."<br>";
        //echo "message accessions count=".count($message->getAccession())."<br>";

        return $message;
    }


    public function addTwoSigningProviders($message) {
        $em = $this->getDoctrine()->getManager();

        $userSecurity = $this->get('security.token_storage')->getToken()->getUser();
        //$user = $em->getRepository('AppUserdirectoryBundle:User')->find($userSecurity->getId());
        $user = $em->getReference('AppUserdirectoryBundle:User',$userSecurity->getId());

        //add 2 proxyusers
        $UserWrapperTransformer = new UserWrapperTransformer($em, $this->container);

        //add first proxyuser
        $UserWrappers = $UserWrapperTransformer->reverseTransform($user."");
        $message->addProxyuser($UserWrappers[0]);

        //add second proxyuser
        $userSystem = $em->getRepository('AppUserdirectoryBundle:User')->find(1);
        $UserWrappers = $UserWrapperTransformer->reverseTransform($userSystem."");
        $message->addProxyuser($UserWrappers[0]);
    }


    public function setDocumentContainerWithLinks( $docContainer, $slideId, $user ) {

        $em = $this->getDoctrine()->getManager();

        $sourceSystemName = 'PACS on C.MED.CORNELL.EDU';
        $sourceSystemPacsvendor = $em->getRepository('AppUserdirectoryBundle:SourceSystemList')->findOneByName($sourceSystemName);
        $sourceSystemPacsvendorClean = $sourceSystemPacsvendor->getName();

        $linkTypeWebScope = $em->getRepository('AppUserdirectoryBundle:LinkTypeList')->findOneByName("Via WebScope");
        $linkTypeWebScopeClean = $linkTypeWebScope->getName();

        $linkTypeImageScope = $em->getRepository('AppUserdirectoryBundle:LinkTypeList')->findOneByName("Via ImageScope");
        $linkTypeImageScopeClean = $linkTypeImageScope->getName();

        $linkTypeThumbnail = $em->getRepository('AppUserdirectoryBundle:LinkTypeList')->findOneByName("Thumbnail");
        $linkTypeThumbnailClean = $linkTypeThumbnail->getName();

        $linkTypeLabel = $em->getRepository('AppUserdirectoryBundle:LinkTypeList')->findOneByName("Label");
        $linkTypeLabelClean = $linkTypeLabel->getName();

        $linkTypeDownload = $em->getRepository('AppUserdirectoryBundle:LinkTypeList')->findOneByName("Download");
        $linkTypeDownloadClean = $linkTypeDownload->getName();

        //$docContainer->setTitle('Image from ' . $sourceSystemPacsvendor);
        $docContainer->setTitle('Sample Test Whole Slide Image');

        $router = $this->container->get('router');

        //add link Via WebScope
        //use http://c.med.cornell.edu/imageserver/@@D5a3Yrn7dI2BGAKr0BEOxigCkxFErp2QJNfGJrBmWo68tr-locAr0Q==/@73660/view.apml
        $linklink = $router->generate('scan_image_viewer',array('system'=>$sourceSystemPacsvendorClean,'type'=>$linkTypeWebScopeClean,'tablename'=>'Slide','imageid'=>$slideId),UrlGeneratorInterface::ABSOLUTE_URL);
        $link = new Link($user);
        $link->setLinktype($linkTypeWebScope);
        $link->setLink($linklink);
        $docContainer->addLink($link);

        //add link Via ImageScope
        //use sis file containing url to image from pacsvendor DB \\win-vtbcq31qg86\images\1376592217_1368_3005ER.svs
        $linklink = $router->generate('scan_image_viewer',array('system'=>$sourceSystemPacsvendorClean,'type'=>$linkTypeImageScopeClean,'tablename'=>'Slide','imageid'=>$slideId),UrlGeneratorInterface::ABSOLUTE_URL);
        $link = new Link($user);
        $link->setLinktype($linkTypeImageScope);
        $link->setLink($linklink);
        $docContainer->addLink($link);

        //add Thumbnail
        $linklink = $router->generate('scan_image_viewer',array('system'=>$sourceSystemPacsvendorClean,'type'=>$linkTypeThumbnailClean,'tablename'=>'Slide','imageid'=>$slideId),UrlGeneratorInterface::ABSOLUTE_URL);
        $link = new Link($user);
        $link->setLinktype($linkTypeThumbnail);
        $link->setLink($linklink);
        $docContainer->addLink($link);

        //add Label
        $linklink = $router->generate('scan_image_viewer',array('system'=>$sourceSystemPacsvendorClean,'type'=>$linkTypeLabelClean,'tablename'=>'Slide','imageid'=>$slideId),UrlGeneratorInterface::ABSOLUTE_URL);
        $link = new Link($user);
        $link->setLinktype($linkTypeLabel);
        $link->setLink($linklink);
        $docContainer->addLink($link);

        //add download
        $linklink = $router->generate('scan_image_viewer',array('system'=>$sourceSystemPacsvendorClean,'type'=>$linkTypeDownloadClean,'tablename'=>'Slide','imageid'=>$slideId),UrlGeneratorInterface::ABSOLUTE_URL);
        $link = new Link($user);
        $link->setLinktype($linkTypeDownload);
        $link->setLink($linklink);
        $docContainer->addLink($link);

        return $docContainer;
    }

}
