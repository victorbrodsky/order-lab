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

        $params = array(
            'type' => 'multy',
            'cycle' => 'new',
            'user' => $user,
            'datastructure' => 'datastructure'
        );

        $form = $this->createForm( new PatientType($params,$patient), $patient, array('disabled' => false) );

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
