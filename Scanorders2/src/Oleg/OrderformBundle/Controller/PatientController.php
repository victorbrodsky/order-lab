<?php

namespace Oleg\OrderformBundle\Controller;


use Oleg\OrderformBundle\Entity\BlockOrder;
use Oleg\OrderformBundle\Entity\EmbedBlockOrder;
use Oleg\OrderformBundle\Entity\Endpoint;
use Oleg\OrderformBundle\Entity\InstructionList;
use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Entity\Report;
use Oleg\OrderformBundle\Entity\RequisitionForm;
use Oleg\OrderformBundle\Entity\SlideOrder;
use Oleg\OrderformBundle\Entity\StainOrder;
use Oleg\UserdirectoryBundle\Entity\Document;
use Oleg\UserdirectoryBundle\Entity\Institution;
use Oleg\UserdirectoryBundle\Entity\UserWrapper;
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
use Oleg\UserdirectoryBundle\Entity\DocumentContainer;

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
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:Patient')->findAll();

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
        $em = $this->getDoctrine()->getManager();
        $securityUtil = $this->get('order_security_utility');

        $system = $securityUtil->getDefaultSourceSystem(); //'scanorder';
        $status = 'valid';
        $user = $this->get('security.context')->getToken()->getUser();

        $patient = new Patient(true,$status,$user,$system);
        $patient->addExtraFields($status,$user,$system);

        $encounter = new Encounter(true,$status,$user,$system);
        $encounter->addExtraFields($status,$user,$system);
        $patient->addEncounter($encounter);

        $procedure = new Procedure(true,$status,$user,$system);
        $procedure->addExtraFields($status,$user,$system);
        $encounter->addProcedure($procedure);

        $accession = new Accession(true,$status,$user,$system);
        $accession->addExtraFields($status,$user,$system);
        $procedure->addAccession($accession);

        $part = new Part(true,$status,$user,$system);
        //$part->addExtraFields($status,$user,$system);
        $accession->addPart($part);

        $block = new Block(true,$status,$user,$system);
        //$block->addExtraFields($status,$user,$system);
        $part->addBlock($block);

        $slide = new Slide(true,'valid',$user,$system); //Slides are always valid by default
        //$slide->addExtraFields($status,$user,$system);
        $block->addSlide($slide);


        $disabled = true;
        //$disabled = false;

        $params = array(
            'type' => 'multy',
            'cycle' => 'new',
            'user' => $user,
            'datastructure' => 'datastructure'
        );

        /////////////////////// testing: create specific messages ///////////////////////
        $params['sources'] = true;
        $params['system'] = true;
        $params['orderdate'] = true;
        $params['provider'] = true;

        $this->createAndAddSpecificMessage($slide,"Lab Order");
        $params['message.laborder'] = true;
        $params['idnumber'] = true;

        $this->createAndAddSpecificMessage($slide,"Report");
        $params['message.report'] = true;

        $this->createAndAddSpecificMessage($slide,"Block Order");
        $params['message.blockorder'] = true;

        $this->createAndAddSpecificMessage($slide,"Slide Order");
        $params['message.slideorder'] = true;

        $this->createAndAddSpecificMessage($slide,"Stain Order");
        $params['message.stainorder'] = true;
        /////////////////////// EOF create lab order ///////////////////////

        $form = $this->createForm( new PatientType($params,$patient), $patient, array('disabled' => $disabled) );

        return array(
            'entity' => $patient,
            'form' => $form->createView(),
            'formtype' => 'Patient Data Structure',
            'type' => 'show',
            'datastructure' => 'datastructure'
        );
    }

    public function createAndAddSpecificMessage($object,$messageTypeStr) {

        $em = $this->getDoctrine()->getManager();
        $message = new OrderInfo();

        $user = $this->get('security.context')->getToken()->getUser();
        $message->setProvider($user);

//        $message->setIdnumber($messageTypeStr.' id number');

        $category = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($messageTypeStr);
        $message->setMessageCategory($category);

        $source = new Endpoint();
        $message->addSource($source);

        $destination = new Endpoint();
        $message->addDestination($destination);


        //add slide to message and input
        $message->addSlide($object);
        $object->addOrderinfo($message);
        //set this slide as order input
        $message->addInputObject($object);


        if( $messageTypeStr == "Lab Order" ) {

            $laborder = new LabOrder();
            $laborder->setOrderinfo($message);
            $message->setLaborder($laborder);

            $reqForm = new RequisitionForm();
            $documentContainer = new DocumentContainer();
            //$documentContainer->addDocument(new Document());
            $reqForm->setDocumentContainer($documentContainer);
            $laborder->addRequisitionForm($reqForm);

        }

        if( $messageTypeStr == "Report" ) {

            $report = new Report();
            $report->setOrderinfo($message);
            $message->setReport($report);

            $documentContainer = new DocumentContainer();
            $report->setDocumentContainer($documentContainer);

            $signingPathologist = new UserWrapper();
            $report->addSigningPathologist($signingPathologist);

            $consultedPathologist = new UserWrapper();
            $report->addConsultedPathologist($consultedPathologist);

        }

        if( $messageTypeStr == "Block Order" ) {
            $blockorder = new BlockOrder();
            $blockorder->setOrderinfo($message);
            $message->setBlockorder($blockorder);

            $documentContainer = new DocumentContainer();
            $blockorder->setDocumentContainer($documentContainer);

            $instruction = new InstructionList();
            $blockorder->setInstruction($instruction);
        }

        if( $messageTypeStr == "Slide Order" ) {
            $slideorder = new SlideOrder();
            $slideorder->setOrderinfo($message);
            $message->setSlideorder($slideorder);


            $instruction = new InstructionList();
            $slideorder->setInstruction($instruction);
        }

        if( $messageTypeStr == "Stain Order" ) {
            $stainorder = new StainOrder();
            $stainorder->setOrderinfo($message);
            $message->setStainorder($stainorder);

            $instruction = new InstructionList();
            $stainorder->setInstruction($instruction);
        }

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
}
