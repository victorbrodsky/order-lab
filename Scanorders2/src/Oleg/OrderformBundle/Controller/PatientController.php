<?php

namespace Oleg\OrderformBundle\Controller;



use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\Encounter;
use Oleg\OrderformBundle\Form\PatientType;
use Oleg\OrderformBundle\Entity\Procedure;
use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Entity\Slide;

use Oleg\OrderformBundle\Entity\LabOrder;

use Oleg\OrderformBundle\Entity\AccessionAccession;
use Oleg\OrderformBundle\Entity\BlockOrder;
use Oleg\OrderformBundle\Entity\EmbedBlockOrder;
use Oleg\OrderformBundle\Entity\EncounterDate;
use Oleg\OrderformBundle\Entity\EncounterPatage;
use Oleg\OrderformBundle\Entity\Endpoint;
use Oleg\OrderformBundle\Entity\InstructionList;
use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Entity\PatientClinicalHistory;
use Oleg\OrderformBundle\Entity\PatientDob;
use Oleg\OrderformBundle\Entity\PatientFirstName;
use Oleg\OrderformBundle\Entity\PatientLastName;
use Oleg\OrderformBundle\Entity\PatientMiddleName;
use Oleg\OrderformBundle\Entity\PatientMrn;
use Oleg\OrderformBundle\Entity\PatientSex;
use Oleg\OrderformBundle\Entity\Report;
use Oleg\OrderformBundle\Entity\RequisitionForm;
use Oleg\OrderformBundle\Entity\Imaging;
use Oleg\OrderformBundle\Entity\ScanOrder;
use Oleg\OrderformBundle\Entity\SlideOrder;
use Oleg\OrderformBundle\Entity\StainOrder;
use Oleg\OrderformBundle\Form\DataTransformer\AccessionTypeTransformer;
use Oleg\OrderformBundle\Form\DataTransformer\MrnTypeTransformer;

use Oleg\UserdirectoryBundle\Entity\AttachmentContainer;
use Oleg\UserdirectoryBundle\Entity\DocumentContainer;
use Oleg\UserdirectoryBundle\Entity\Document;
use Oleg\UserdirectoryBundle\Entity\Institution;
use Oleg\UserdirectoryBundle\Entity\UserWrapper;

/**
 * Patient controller.
 *
 * @Route("/patient")
 */
class PatientController extends Controller
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
        //found 19
        //$em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('OlegOrderformBundle:Patient')->findAll();

        $searchUtil = $this->get('search_utility');
        $object = 'patient';
        $params = array('request'=>$request,'object'=>$object);
        $res = $searchUtil->searchAction($params);
        $entities = $res[$object];

        return array(
            'entities' => $entities,
        );
    }

    /**
     * New Patient.
     *
     * @Route("/datastructure", name="scan-patient-new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function newPatientAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        //check if user has at least one institution
        $securityUtil = $this->get('order_security_utility');
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


        ///////////////////// prepare messages /////////////////////
        $messages = array();

        $messageStainOrder = $this->createSpecificMessage("Block Order");
        $messages[] = $messageStainOrder;

        $messageStainOrder = $this->createSpecificMessage("Slide Order");
        $messages[] = $messageStainOrder;

        $messageLabOrder = $this->createSpecificMessage("Lab Order");
        $messages[] = $messageLabOrder;

        $messageReportOrder = $this->createSpecificMessage("Report");
        $messages[] = $messageReportOrder;

        $messageStainOrder = $this->createSpecificMessage("Stain Order");
        $messages[] = $messageStainOrder;

        $messageMultiSlideScanOrder = $this->createSpecificMessage("Multi-Slide Scan Order");
        $messages[] = $messageMultiSlideScanOrder;
        ///////////////////// EOF prepare messages /////////////////////


        $thisparams = array(
            'objectNumber' => 1,
            'aperioImageNumber' => 1,
            'withorders' => true,
            'accession.attachmentContainer' => 1,
            'part.attachmentContainer' => 1,
            'specificmessages' => $messages
        );
        $patient = $this->createPatientDatastructure($thisparams);

        $disabled = true;
        //$disabled = false;

        $params = array(
            'type' => 'multy',
            'cycle' => 'new',
            'user' => $user,
            'datastructure' => 'datastructure'
        );

        //message fields
        $params['endpoint.system'] = true;
        $params['message.orderdate'] = true;
        $params['message.provider'] = true;
        $params['message.proxyuser'] = true;
        $params['message.idnumber'] = false;
        $params['message.sources'] = false;
        $params['message.destinations'] = false;
        $params['message.inputs'] = false;
        $params['message.outputs'] = false;

        //specific orders
//        $params['message.laborder'] = true;
//        $params['message.report'] = true;
//        $params['message.blockorder'] = true;
//        $params['message.slideorder'] = true;
//        $params['message.stainorder'] = true;

        $form = $this->createForm( new PatientType($params,$patient), $patient, array('disabled' => $disabled) );

        return array(
            'entity' => $patient,
            'form' => $form->createView(),
            'formtype' => 'Patient Data Structure',
            'type' => 'show',
            'datastructure' => 'datastructure'
        );
    }


    /**
     * Finds and displays a Patient entity.
     *
     * @Route("/{id}", name="scan-patient-show")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function showAction($id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ORDERING_PROVIDER')
        ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $entity = $em->getRepository('OlegOrderformBundle:Patient')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Patient entity.');
        }

        $params = array(
            'type' => 'multy',
            'cycle' => "show",
            'user' => $user,
            'datastructure' => 'datastructure'
        );

        //message fields
        $params['endpoint.system'] = true;
        $params['message.orderdate'] = true;
        $params['message.provider'] = true;
        $params['message.proxyuser'] = true;
        $params['message.idnumber'] = false;
        $params['message.sources'] = false;
        $params['message.destinations'] = false;
        $params['message.inputs'] = false;
        $params['message.outputs'] = false;

        $form = $this->createForm( new PatientType($params,$entity), $entity, array('disabled' => true) );

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'formtype' => 'Patient Data Structure',
            'type' => 'show',
            'datastructure' => 'datastructure'
        );
    }

    /**
     * Displays a form to edit an existing Patient entity.
     *
     * @Route("/{id}/edit", name="scan-patient-edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Patient')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Patient entity.');
        }

        $editForm = $this->createForm(new PatientType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Patient entity.
     *
     * @Route("/{id}", name="patient_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Patient:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Patient')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Patient entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new PatientType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('patient_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
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
            ->add('id', 'hidden')
            ->getForm()
        ;
    }






    //create Test Patient
    /**
     * @Route("/datastructure/new-test-patient", name="scan_testpatient_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function newTestPatientAction() {

        $securityUtil = $this->get('order_security_utility');
        $status = 'valid';
        $system = $securityUtil->getDefaultSourceSystem();
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

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

        ///////////////////// prepare messages /////////////////////
        $messages = array();

//        $messageStainOrder = $this->createSpecificMessage("Block Order");
//        $messages[] = $messageStainOrder;
//
//        $messageStainOrder = $this->createSpecificMessage("Slide Order");
//        $messages[] = $messageStainOrder;
//
//        $messageLabOrder = $this->createSpecificMessage("Lab Order");
//        $messages[] = $messageLabOrder;
//
//        $messageReportOrder = $this->createSpecificMessage("Report");
//        $messages[] = $messageReportOrder;
//
//        $messageStainOrder = $this->createSpecificMessage("Stain Order");
//        $messages[] = $messageStainOrder;

        $messageMultiSlideScanOrder = $this->createSpecificMessage("Multi-Slide Scan Order");
        $messages[] = $messageMultiSlideScanOrder;
        ///////////////////// EOF prepare messages /////////////////////


        ///////////////////// prepare test patient /////////////////////
        $thisparams = array(
            'objectNumber' => 1,
            'aperioImageNumber' => 1,
            //'withorders' => false,               //add orders to correspondent entities
            'persist' => false,                 //persist each patient hierarchy object (not used)
            //'specificmessages' => $messages,    //add specific messages to each patient hierarchy
            'testpatient' => true,              //populate patient hierarchy with default data
            //'accession.attachmentContainer' => 1 //testing!!!
        );
        $patient = $this->createPatientDatastructure($thisparams);
        ///////////////////// EOF prepare test patient /////////////////////

        //add messages to the patient
//        foreach( $messages as $message ) {
//            $patient->addOrderinfo($message);
//            $message->addPatient($patient);
//        }

        //echo "messages=".count($patient->getOrderinfo())."<br>";

        ///////////////////// populate patient with mrn, mrntype, name etc. /////////////////////
        $mrntypeStr = 'Test Patient MRN';
        $testpatients = $em->getRepository('OlegOrderformBundle:Patient')->findByMrntypeString($mrntypeStr);
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
        $sex = $em->getRepository('OlegOrderformBundle:SexList')->findOneByName('Female');
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

            $testaccessions = $em->getRepository('OlegOrderformBundle:Accession')->findByAccessiontypeString($accessiontypeStr);
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
//            $autopsydocument = $em->getRepository('OlegUserdirectoryBundle:Document')->findOneByUniquename($uniqueName);
//            $accessionDocContainer->addDocument($autopsydocument);

        }
        ///////////////////// EOF populate accession with accession number, accession type, etc. /////////////////////

        //add messages to the patient
        foreach( $messages as $message ) {
            $patient->addOrderinfo($message);
            $message->addPatient($patient);
        }

        //create scan order first; patient hierarchy will be created as well.
        $messageMultiSlideScanOrder = $em->getRepository('OlegOrderformBundle:OrderInfo')->processOrderInfoEntity( $messageMultiSlideScanOrder, $user, null, $this->get('router'), $this->container );

        ///////////////////// prepare messages /////////////////////
        $messages = array();

        $messageStainOrder = $this->createSpecificMessage("Block Order");
        $messages[] = $messageStainOrder;

        $messageStainOrder = $this->createSpecificMessage("Slide Order");
        $messages[] = $messageStainOrder;

        $messageLabOrder = $this->createSpecificMessage("Lab Order");
        $messages[] = $messageLabOrder;

        $messageReportOrder = $this->createSpecificMessage("Report");
        $messages[] = $messageReportOrder;

        $messageStainOrder = $this->createSpecificMessage("Stain Order");
        $messages[] = $messageStainOrder;

        //$messageMultiSlideScanOrder = $this->createSpecificMessage("Multi-Slide Scan Order");
        //$messages[] = $messageMultiSlideScanOrder;
        ///////////////////// EOF prepare messages /////////////////////

//        foreach( $messages as $message ) {
//
//            $patient->addOrderinfo($message);
//            $message->addPatient($patient);
//
//            $message = $em->getRepository('OlegOrderformBundle:OrderInfo')->processOrderInfoEntity( $message, $user, null, $this->get('router'), $this->container );
//            echo '"<br><br>Created message id='.$message->getId().', cat='.$message->getMessageCategory()."<br><br>";
//            //continue;
//        }

        //now patient hierarchy exists in DB => re-set message inputs and outputs (GeneralEntity) with existing object ID from DB
        $patientDb = $em->getRepository('OlegOrderformBundle:Patient')->find($patient->getId());
        echo "patientDb=".$patientDb."<br>";
        $this->linkMessagesPatient($messages,$patientDb);
        //exit('1');

        //$em->persist($patient);
        //$em->flush();

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


    public function linkMessagesPatient( $messages, $patient ) {

        $em = $this->getDoctrine()->getManager();

        foreach( $messages as $message ) {

            $this->recursiveHierarchyLinkMessageObject($message,$patient);

            $em->persist($message);
        }

        $em->flush();
    }

    public function recursiveHierarchyLinkMessageObject( $message, $entity ) {

        $children = $entity->getChildren();

        if( !$children ) {
            return;
        }

        foreach( $entity->getChildren() as $child ) {

            $this->linkSingleMessageObject( $message, $child );

            if( $child ) {

                $this->recursiveHierarchyLinkMessageObject( $message, $child );

            }

        }

    }

    public function linkSingleMessageObject( $message, $object ) {

        $inputPairs = array(
            "Slide" => array("Lab Order","Report","Stain Order","Multi-Slide Scan Order"),
            "Block" => array("Slide Order"),
            "Part" => array("Block Order")
        );

        $class = new \ReflectionClass($object);
        $className = $class->getShortName();

        if( !array_key_exists($className, $inputPairs) ) {
            return;
        }

        $messageCategories = $inputPairs[$className];
        $thisMessageCategory = $message->getMessageCategory()->getName()."";

        $addObjectAsInput = false;
        if( in_array($thisMessageCategory,$messageCategories) ) {
            $addObjectAsInput = true;
        }

        $this->linkMessageObject($message,$object,$addObjectAsInput);

    }


    public function linkMessageObject( $message, $object, $addObjectAsInput=true, $forceAddObjectAsInput=false ) {

//        echo "<br><br>";
//        echo "addObjectAsInput=".$addObjectAsInput."<br>";
//        echo "link message with category=".$message->getMessageCategory()->getName()."<br>";
//        foreach( $message->getInputs() as $input ) {
//            echo "input=".$input->getFullName()."<br>";
//        }

        //add message to object
        $object->addOrderinfo($message);

        //add object to message
        $class = new \ReflectionClass($object);
        $className = $class->getShortName();
        $addMethod = 'add'.$className;
        $message->$addMethod($object);

        //set object as message input
        if( $addObjectAsInput ) {
            if( $forceAddObjectAsInput || $object->getId() ) {
                $message->addInputObject($object);
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

        if( array_key_exists('specificmessages', $params) ) {
            $specificmessages = $params['specificmessages'];
        } else {
            $specificmessages = false;
        }

        if( array_key_exists('objectNumber', $params) ) {
            $objectNumber = $params['objectNumber'];
        } else {
            $objectNumber = 1;
        }

        if( array_key_exists('aperioImageNumber', $params) ) {
            $aperioImageNumber = $params['aperioImageNumber'];
        } else {
            $aperioImageNumber = 0;
        }

        if( array_key_exists('withorders', $params) ) {
            $withOrders = $params['withorders'];
        } else {
            $withOrders = false;
        }

//        if( array_key_exists('scanorder', $params) ) {
//            $scanorderType = $params['scanorder'];
//        } else {
//            $scanorderType = false;
//        }

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

        if( array_key_exists('testpatient', $params) ) {
            $testpatient = $params['testpatient'];
        } else {
            $testpatient = false;
        }

        $em = $this->getDoctrine()->getManager();
        $securityUtil = $this->get('order_security_utility');

        $system = $securityUtil->getDefaultSourceSystem();
        $status = 'valid';
        $user = $this->get('security.context')->getToken()->getUser();

        $patient = new Patient($withfields,$status,$user,$system);
        $patient->addExtraFields($status,$user,$system);

        if( $persist ) {
            $em->persist($patient);
        }

        if( $specificmessages ) {
            foreach( $specificmessages as $specificmessage ) {
                //echo "adding patient to message with category=".$specificmessage->getMessageCategory()."<br>";
                $specificmessage->addPatient($patient);
            }
        }

        for( $count = 0; $count < $objectNumber; $count++ ) {

            $encounter = new Encounter($withfields,$status,$user,$system);
            $encounter->addExtraFields($status,$user,$system);
            $patient->addEncounter($encounter);

            if( $persist ) {
                $em->persist($encounter);
            }

            if( $specificmessages ) {
                foreach( $specificmessages as $specificmessage ) {
                    $specificmessage->addEncounter($encounter);
                }
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

            if( $specificmessages ) {
                foreach( $specificmessages as $specificmessage ) {
                    $specificmessage->addProcedure($procedure);
                }
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

            if( $specificmessages ) {
                foreach( $specificmessages as $specificmessage ) {
                    $specificmessage->addAccession($accession);
                }
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

            if( $specificmessages ) {
                foreach( $specificmessages as $specificmessage ) {
                    $specificmessage->addPart($part);
                }
            }

            if( $testpatient ) {
                $partname = $part->obtainValidField('partname');
                $partname->setField('A');
                $sourceOrgan = $part->obtainValidField('sourceOrgan');
                $organList = $em->getRepository('OlegOrderformBundle:OrganList')->find(1);
                $sourceOrgan->setField($organList);

                //set the "Type of Disease" in Part to "Neoplastic" and "Metastatic" to show the child and grandchild questions.
                $typeDisease = $part->obtainValidField('diseaseType');
                $typeDisease->setField('Neoplastic');
                $typeDisease->setOrigin('Metastatic');
                $typeDisease->setPrimaryOrgan($organList);
            }

            $block = new Block($withfields,$status,$user,$system);

            //set specialStains to null
            $blockSpecialstain = $block->getSpecialStains()->first();
            $staintype = $em->getRepository('OlegOrderformBundle:StainList')->find(1);
            $blockSpecialstain->setStaintype($staintype);
            //$blockSpecialstain->setField('stain ' . $staintype);
            //echo "specialStain field=".$blockSpecialstain->getField()."<br>";
            //echo "specialStain staintype=".$blockSpecialstain->getStaintype()."<br>";

            $part->addBlock($block);

            if( $persist ) {
                $em->persist($block);
            }

            if( $specificmessages ) {
                foreach( $specificmessages as $specificmessage ) {
                    $specificmessage->addBlock($block);
                }
            }

            if( $testpatient ) {
                $blockname = $block->obtainValidField('blockname');
                $blockname->setField('1');
                $sectionsource = $block->obtainValidField('sectionsource');
                $sectionsource->setField('Test Section Source');
            }

//            $em = $this->getDoctrine()->getManager();
//            $Staintype = $em->getRepository('OlegOrderformBundle:StainList')->find(1);
//            $block->getSpecialStains()->first()->setStaintype($Staintype);
//            echo $block;
//            echo "staintype=".$block->getSpecialStains()->first()->getStaintype()->getId()."<br>";


            $slide = new Slide($withfields,'valid',$user,$system); //Slides are always valid by default
            //$slide->addExtraFields($status,$user,$system);
            $block->addSlide($slide);

            if( $persist ) {
                $em->persist($slide);
            }

            if( $specificmessages ) {
                foreach( $specificmessages as $specificmessage ) {
                    $specificmessage->addSlide($slide);
                }
            }

            if( $testpatient ) {
                //set stain
                $slidestain = $em->getRepository('OlegOrderformBundle:StainList')->find(1);
                $slide->getStain()->first()->setField($slidestain);

                //set slide title
                $slide->setTitle('Test Slide ' . $count);

                //set slide type
                $slidetype = $em->getRepository('OlegOrderformBundle:SlideType')->findOneByName('Frozen Section');
                $slide->setSlidetype($slidetype);
            }

            //add scan (Imaging) to a slide
            if( $objectNumber > 0 ) {
                $slide->clearScan();
            }
            for( $countImage = 0; $countImage < $objectNumber; $countImage++ ) {
                $scanimage = new Imaging('valid',$user,$system);

                if( $testpatient ) {
                    $scanimage->setField('20X');

                    //set imageid
                    $scanimage->setImageId('testimage_id_'.$countImage);
                    $docContainer = $scanimage->getDocumentContainer();

                    if( !$docContainer ) {
                        $docContainer = new DocumentContainer($user);
                        $scanimage->setDocumentContainer($docContainer);
                    }

                    $docContainer->setTitle('Test Image');

                    //set image
                    //testimage_5522979c2e736.jpg
                    //$uniqueName = uniqid('testimage_').".jpg";
                    $uniqueName = 'testimage_5522979c2e736.jpg';
                    //echo "uniqueName=".$uniqueName."<br>";
                    //exit();

                    //add document to DocumentContainer
                    $document = $em->getRepository('OlegUserdirectoryBundle:Document')->findOneByUniquename($uniqueName);
                    //echo "document=".$document."<br>";

                    if( !$document ) {
                        $document = new Document($user);
                        $document->setOriginalname('testimage.jpg');
                        $document->setUniquename($uniqueName);
                        $dir = 'Uploaded/scan-order/documents';
                        $document->setUploadDirectory($dir);
                        $filename = $dir."/".$uniqueName;
                        if( file_exists($filename) ) {
                            $imagesize = filesize($filename);
                            //echo "The imagesize=$imagesize<br>";
                            $document->setSize($imagesize);
                        } else {
                            //echo "The file $filename does not exist<br>";
                            $this->get('session')->getFlashBag()->add(
                                'notice',
                                'The file'.$filename.' does not exist. Please copy this file to web/'.$dir
                            );
                            return $this->redirect( $this->generateUrl('scan-patient-list') );
                            //throw new \Exception( 'The file'.$filename.' does not exist' );
                        }
                    }

                    $docContainer->addDocument($document);
                } //if testpatient

                //add scan to slide
                $slide->addScan($scanimage);
            }

            //attach one existing aperio image http://c.med.cornell.edu/EditRecord.php?TableName=Slide&Ids[]=42814,
            //image ID:73660
            //image/aperio/73660
            for( $countImage = 0; $countImage < $aperioImageNumber; $countImage++ ) {
                $scanimage = new Imaging('valid',$user,$system);

                if( 0 && $testpatient ) {
                    $scanimage->setField('20X');

                    //set imageid
                    $scanimage->setImageId('testimage_id_'.$countImage);
                    $docContainer = $scanimage->getDocumentContainer();

                    if( !$docContainer ) {
                        $docContainer = new DocumentContainer($user);
                        $scanimage->setDocumentContainer($docContainer);
                    }

                    $docContainer->setTitle('Aperio Image');

                    //set image
                    //testimage_5522979c2e736.jpg
                    //$uniqueName = uniqid('testimage_').".jpg";
                    $uniqueName = 'testimage_5522979c2e736.jpg';
                    //echo "uniqueName=".$uniqueName."<br>";
                    //exit();

                    $docContainer->addDocument($document);
                } //if testpatient

                //add scan to slide
                $slide->addScan($scanimage);
            }

            //Accession: add n autopsy fields: add n documentContainers to attachmentContainer
            if( $attachmentContainerAccessionNumber > 0 ) {
                $attachmentContainerAccession = $accession->getAttachmentContainer();
                if( !$attachmentContainerAccession ) {
                    $attachmentContainerAccession = new AttachmentContainer();
                    $accession->setAttachmentContainer($attachmentContainerAccession);
                }
                for( $i=0; $i<$attachmentContainerAccessionNumber; $i++) {
                    $attachmentContainerAccession->addDocumentContainer( new DocumentContainer($user) );
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

            /////////////////////// testing: create specific messages ///////////////////////
            if( $withOrders ) {

                $this->addSpecificMessage($specificmessages,$slide,"Lab Order",true);

                //$this->addSpecificMessage($part,"Report");
                $this->addSpecificMessage($specificmessages,$slide,"Report",true);

                $this->addSpecificMessage($specificmessages,$part,"Block Order",true);

                $this->addSpecificMessage($specificmessages,$block,"Slide Order",true);

                $this->addSpecificMessage($specificmessages,$slide,"Stain Order",true);

                $this->addSpecificMessage($specificmessages,$slide,"Multi-Slide Scan Order",true);

            }

//            if( $scanorderType && $scanorderType != "" ) {
//                exit('with scanorder type???');
//                $message = $this->addSpecificMessage($slide,$scanorderType,false);
//            }

            /////////////////////// EOF specific messages ///////////////////////

        } //for $objectNumber

        return $patient;
    }

    public function addSpecificMessage( $messages, $object, $messageTypeStr, $addObjectToMessage=true ) {

        if( $messages == null ) {
            return;
        }

        $forceAddObjectAsInput = true;

        foreach( $messages as $message) {

            if( $message->getMessageCategory()->getName()."" == $messageTypeStr ) {

                $this->linkMessageObject($message,$object,$addObjectToMessage,$forceAddObjectAsInput);

            }

        }

    }


    public function createSpecificMessage( $messageCategoryStr ) {

        $em = $this->getDoctrine()->getManager();
        $securityUtil = $this->get('order_security_utility');

        $userSecurity = $this->get('security.context')->getToken()->getUser();
        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($userSecurity->getId());

        $system = $securityUtil->getDefaultSourceSystem();

        //set scan order
        $message = new OrderInfo();
        //$scanOrder = new ScanOrder();
        //$scanOrder->setOrderinfo($message);

        //set provider
        $message->setProvider($user);

        //set Source object
        $source = new Endpoint();
        $source->setSystem($system);
        $message->addSource($source);

        //set Destination object
        $destination = new Endpoint();
        $message->addDestination($destination);

        //type
        $category = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($messageCategoryStr);
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
        $orderStatus = $em->getRepository('OlegOrderformBundle:Status')->findOneByName('Submitted');
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
        //$object->addOrderinfo($message);

        //set this object as order input
//        if( $addObjectToMessage ) {
//            $message->addInputObject($object);
//        }


        if( $messageCategoryStr == "Lab Order" ) {

            $laborder = new LabOrder();
            $laborder->setOrderinfo($message);
            $message->setLaborder($laborder);

            //$em->persist($message);
        }

        if( $messageCategoryStr == "Report" ) {

            $report = new Report();
            $report->setOrderinfo($message);
            $message->setReport($report);

            $signingPathologist = new UserWrapper();
            $report->addSigningPathologist($signingPathologist);

            $consultedPathologist = new UserWrapper();
            $report->addConsultedPathologist($consultedPathologist);

            //$em->persist($message);
        }

        if( $messageCategoryStr == "Block Order" ) {
            $blockorder = new BlockOrder();
            $blockorder->setOrderinfo($message);
            $message->setBlockorder($blockorder);

            $instruction = new InstructionList($user);
            $blockorder->setInstruction($instruction);

            //$em->persist($message);
        }

        if( $messageCategoryStr == "Slide Order" ) {
            $slideorder = new SlideOrder();
            $slideorder->setOrderinfo($message);
            $message->setSlideorder($slideorder);


            $instruction = new InstructionList($user);
            $slideorder->setInstruction($instruction);

            //$em->persist($message);
        }

        if( $messageCategoryStr == "Stain Order" ) {
            $stainorder = new StainOrder();
            $stainorder->setOrderinfo($message);
            $message->setStainorder($stainorder);

            $instruction = new InstructionList($user);
            $stainorder->setInstruction($instruction);

            //$em->persist($message);
        }

        if( $messageCategoryStr == "Multi-Slide Scan Order" ) {
            $scanorder = new ScanOrder();
            $scanorder->setOrderinfo($message);
            $message->setScanorder($scanorder);
        }
        /////////////////// EOF set specific message //////////////////////////////




        //echo $message;
        //echo "message institution=".$message->getInstitution()->getName()."<br>";
        //echo "message accessions count=".count($message->getAccession())."<br>";

        return $message;
    }

}
