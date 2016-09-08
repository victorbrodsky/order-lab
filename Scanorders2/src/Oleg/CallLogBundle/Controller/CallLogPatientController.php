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
     * @Route("/info/{id}", name="calllog_patient_show", options={"expose"=true})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function showAction( Request $request, $id )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $params = array(
            'sitename' => $this->container->getParameter('calllog.sitename'),
            'datastructure' => '',
            'tracker' => 'tracker',
            'editpath' => 'calllog_patient_edit'
        );

        return $this->showPatient($request,$id,$params);
    }


    /**
     * Displays a form to edit an existing Patient entity.
     *
     * @Route("/{id}/edit", name="calllog_patient_edit", options={"expose"=true})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function editAction( Request $request, $id )
    {
        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }


        $params = array(
            'sitename' => $this->container->getParameter('calllog.sitename'),
            'datastructure' => '',
            'tracker' => 'tracker',
            'updatepath' => 'calllog_patient_update',
            'showPlus' => 'showPlus'
        );

        return $this->editPatient($request,$id,$params);
    }

    /**
     * Edits an existing Patient entity.
     *
     * @Route("/{id}/edit", name="calllog_patient_update", options={"expose"=true})
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

        $params = array(
            'sitename' => $this->container->getParameter('calllog.sitename'),
            'datastructure' => '',
            'tracker' => 'tracker',
            'updatepath' => 'calllog_patient_update',
            'showpath' => 'calllog_patient_show'
        );

        return $this->updatePatient($request,$id,$params);  //$datastructure,$showpath,$updatepath);
    }

}