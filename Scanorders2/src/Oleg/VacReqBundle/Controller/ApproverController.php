<?php

namespace Oleg\VacReqBundle\Controller;

use Oleg\VacReqBundle\Entity\VacReqRequest;
use Oleg\VacReqBundle\Form\VacReqRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//vacreq site

class ApproverController extends Controller
{

    /**
     * Creates a new VacReqRequest entity.
     *
     * @Route("/approvers/", name="vacreq_approvers")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:approvers-list.html.twig")
     */
    public function myRequestsAction(Request $request)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //list all organizational group (institution)
        $roles = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesByObjectAction("VacReqRequest", "changestatus");

        $organizationalInstitutions = array();
        foreach( $roles as $role ) {
            $organizationalInstitutions[] = $role->getInstitution();
        }

        return array(
            'organizationalInstitutions' => $organizationalInstitutions,
        );
    }



    /**
     * @Route("/organizational-institutions/{institutionId}", name="vacreq_orginst_list")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-list.html.twig")
     */
    public function organizationalInstitutionAction(Request $request, $institutionId)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') || false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => institutionId=".$institutionId."<br>";

        $em = $this->getDoctrine()->getManager();

        //find role approvers by institution
        $approvers = array();
        $roleApprovers = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_APPROVER', $institutionId);
        $roleApprover = $roleApprovers[0];
        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
            $approvers = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleApprover->getName());
        }
        //echo "approvers=".count($approvers)."<br>";

        //find role submitters by institution
        $submitters = array();
        $roleSubmitters = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUBMITTER', $institutionId);
        $roleSubmitter = $roleSubmitters[0];
        //echo "roleSubmitter=".$roleSubmitter."<br>";
        if( $roleSubmitter ) {
            $submitters = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleSubmitter->getName());
        }

        $organizationalGroupInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($institutionId);

        return array(
            'approvers' => $approvers,
            'submitters' => $submitters,
            'organizationalGroupId' => $institutionId,
            'organizationalGroupName' => $organizationalGroupInstitution.""
        );
    }



    /**
     * Creates a new VacReqRequest entity.
     *
     * @Route("/organizational-institution-management/{institutionId}", name="vacreq_orginst_management")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-management.html.twig")
     */
    public function orgInstManagementAction(Request $request, $institutionId)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') || false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => institutionId=".$institutionId."<br>";

        $em = $this->getDoctrine()->getManager();

        //$user = $this->get('security.context')->getToken()->getUser();

        //find role approvers by institution
        $approvers = array();
        $roleApprovers = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_APPROVER', $institutionId);
        $roleApprover = $roleApprovers[0];
        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
            $approvers = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleApprover->getName());
        }
        //echo "approvers=".count($approvers)."<br>";

        //find role submitters by institution
        $submitters = array();
        $roleSubmitters = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUBMITTER', $institutionId);
        $roleSubmitter = $roleSubmitters[0];
        //echo "roleSubmitter=".$roleSubmitter."<br>";
        if( $roleSubmitter ) {
            $submitters = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleSubmitter->getName());
        }

        $organizationalGroupInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($institutionId);

        return array(
            'approvers' => $approvers,
            'submitters' => $submitters,
            'organizationalGroupId' => $institutionId,
            'organizationalGroupName' => $organizationalGroupInstitution.""
        );
    }

}
