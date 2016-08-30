<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 8/30/2016
 * Time: 12:19 PM
 */

namespace Oleg\CallLogBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Controller\PatientController;


/**
 * CallLog Patient controller.
 *
 * @Route("/patient")
 */
class CallLogPatientController extends PatientController {

    /**
     * Finds and displays a Patient entity.
     *
     * @Route("/{id}", name="calllog_patient_show")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function showAction( Request $request, $id )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $datastructure = '';
        return $this->showPatient($request,$id,$datastructure);
    }


    /**
     * Displays a form to edit an existing Patient entity.
     *
     * @Route("/{id}/edit", name="calllog_patient_edit")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function editAction( Request $request, $id )
    {
        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $updatepath = 'calllog_patient_update';
        $datastructure = '';
        return $this->editPatient($request,$id,$datastructure,$updatepath);
    }

    /**
     * Edits an existing Patient entity.
     *
     * @Route("/{id}/edit", name="calllog_patient_update")
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

        $datastructure = 'datastructure';
        $showpath = 'calllog_patient_show';
        $updatepath = 'calllog_patient_update';
        return $this->updatePatient($request,$id,$datastructure,$showpath,$updatepath);
    }

}