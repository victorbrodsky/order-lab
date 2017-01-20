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
            'datastructure' => 'datastructure-patient',
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
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'updatepath' => 'calllog_patient_update',
            'showPlus' => 'showPlus'
        );

        return $this->editPatient($request,$id,$params);
    }

    /**
     * Displays a form to edit an existing Patient entity.
     *
     * @Route("/edit-by-mrn/{mrn}/{mrntype}", name="calllog_patient_edit_by_mrn", options={"expose"=true})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Patient:new.html.twig")
     */
    public function editPatientByMrnAction( Request $request, $mrn, $mrntype )
    {
        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $userSecUtil = $this->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $extra = array();
        $extra["keytype"] = $mrntype;
        $validity = array('valid','reserved');
        $single = false;

        //$institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName("All Institutions");
        //$institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName("Weill Cornell Medical College");
        //$institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName("New York-Presbyterian Hospital");
        $institution = $userSecUtil->getCurrentUserInstitution($user);
        $institutions = array();
        $institutions[] = $institution->getId();

        $patients = $em->getRepository('OlegOrderformBundle:Patient')->findOneByIdJoinedToField($institutions,$mrn,"Patient","mrn",$validity,$single,$extra);
        //echo "found patient=".$entity."<br>";
        //exit("edit patient by mrn $mrn $mrntype");
        $patients = $em->getRepository('OlegOrderformBundle:Patient')->findAll(); //testing

        if( count($patients) > 1 ) {
            $patient = null;
            $patientArr = array();
            foreach( $patients as $thisPatient ) {
                if( $thisPatient->obtainValidKeyfield() ) {
                    //we should return a single result, but we got multiple entity, so return the first valid key one.
                    $patient = $thisPatient;
                }
                $patientArr[] = $patient->obtainPatientInfoSimple();
            }
            if( !$patient ) {
                $patient = $patients[0];
            }
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                'Multiple patients found with mrn ' . $mrn . ". Displayed is the first patient with a valid mrn. Found " . count($patients) . " patients: <hr>" . implode("<hr>",$patientArr)
            );
        }

        if( count($patients) == 1 ) {
            $patient = $patients[0];
        }

        if( !$patient || !$patient->getId() ) {
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                'No patient found with mrn ' . $mrn
            );
            return $this->redirect($this->generateUrl('calllog_home'));
        }

        $params = array(
            'sitename' => $this->container->getParameter('calllog.sitename'),
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'updatepath' => 'calllog_patient_update',
            'showPlus' => 'showPlus'
        );

        return $this->editPatient($request,$patient->getId(),$params);
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
            'datastructure' => 'datastructure-patient',
            'tracker' => 'tracker',
            'updatepath' => 'calllog_patient_update',
            'showpath' => 'calllog_patient_show',
        );

        return $this->updatePatient($request,$id,$params);  //$datastructure,$showpath,$updatepath);
    }

}