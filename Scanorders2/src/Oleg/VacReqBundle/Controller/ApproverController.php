<?php

namespace Oleg\VacReqBundle\Controller;

use Oleg\UserdirectoryBundle\Form\SimpleUserType;
use Oleg\VacReqBundle\Entity\VacReqRequest;
use Oleg\VacReqBundle\Form\VacReqRequestType;
use Oleg\VacReqBundle\Form\VacReqUserType;
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
            'approverRoleId' => $roleApprover->getId(),
            'submitters' => $submitters,
            'submitterRoleId' => $roleSubmitter->getId(),
            'organizationalGroupId' => $institutionId,
            'organizationalGroupName' => $organizationalGroupInstitution.""
        );
    }



    /**
     * @Route("/organizational-institution-user-management/{userid}/{instid}/{roleId}", name="vacreq_orginst_user_management")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-user-management.html.twig")
     */
    public function userManagementAction(Request $request, $userid, $instid, $roleId )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') || false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();

        //$user = $this->get('security.context')->getToken()->getUser();
        $subjectUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($userid);

        if( !$subjectUser ) {
            throw $this->createNotFoundException('Unable to find Vacation Request user by id='.$userid);
        }

        $organizationalGroupInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($instid);

        if( !$organizationalGroupInstitution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //Original Roles not associated with this site
        //$securityUtil = $this->get('order_security_utility');
        //$originalOtherRoles = $securityUtil->getUserRolesBySite( $subjectUser, 'vacreq', false );

        //Roles
        $securityUtil = $this->get('order_security_utility');
        $rolesArr = $securityUtil->getSiteRolesKeyValue('vacreq');

        $params = array('roles'=>$rolesArr);

        $form = $this->createForm(
            new VacReqUserType($params),
            $subjectUser,
            array(
                'method' => "POST",
                //'action' => $action
            )
        );

//        $form->handleRequest($request);

        //$formRoles = $request->request->get('roles');
        //$formRoles = $form->getData();
        //$formRoles = $form["roles"]->getData();
        //print_r($formRoles);

//        foreach( $subjectUser->getRoles() as $userRole ) {
//            echo "0 userRole=".$userRole."<br>";
//        }
//        echo "<br>";
        //exit('formRoles='.$formRoles);

//        if( $form->isSubmitted() && $form->isValid() ) {
//
//            foreach( $subjectUser->getRoles() as $userRole ) {
//                echo "0 userRole=".$userRole."<br>";
//            }
//            echo "<br>";
//
//            $this->processUserAuthorization( $subjectUser, $originalOtherRoles );
//
//            foreach( $subjectUser->getRoles() as $userRole ) {
//                echo "1 userRole=".$userRole."<br>";
//            }
//            exit('submitted');
//
//            $em->persist($subjectUser);
//            $em->flush();
//
//            return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
//        }
        //exit('not submitted');

        return array(
            'form' => $form->createView(),
            'entity' => $subjectUser,
            'institution' => $organizationalGroupInstitution,
            'roleId' => $roleId
        );
    }


    /**
     * @Route("/organizational-institution-user-update/{userid}/{instid}/{roleIds}", name="vacreq_orginst_user_update", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-user-management.html.twig")
     */
    public function userManagementUpdateAction(Request $request, $userid, $instid, $roleIds )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') || false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $subjectUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($userid);

        if( !$subjectUser ) {
            throw $this->createNotFoundException('Unable to find Vacation Request user by id='.$userid);
        }

        $organizationalGroupInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($instid);

        if( !$organizationalGroupInstitution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //echo "roleIds=".$roleIds."<br>";

        if( $roleIds || $roleIds == '0' ) {
            $roleArr = explode(',',$roleIds);
        } else {
            $roleArr = array();
        }

        $securityUtil = $this->get('order_security_utility');
        $res = $securityUtil->addOnlySiteRoles($subjectUser,$roleArr,'vacreq');

        if( $res ) {

            $originalUserSiteRoles = $res['originalUserSiteRoles'];
            $newUserSiteRoles = $res['newUserSiteRoles'];

            $em->persist($subjectUser);
            $em->flush();

            //Event Log
            $event = "Roles of ".$subjectUser . " has been changed. Original roles:".implode(",",$originalUserSiteRoles)."; New roles:".implode(",",$newUserSiteRoles);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$subjectUser,$request,'User record updated');

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );

        }

        //return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
        exit('ok');
    }


    /**
     * @Route("/organizational-institution-user-remove/{userid}/{instid}/{roleId}", name="vacreq_orginst_user_remove")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-user-management.html.twig")
     */
    public function removeUserAction(Request $request, $userid, $instid, $roleId )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') || false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $subjectUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($userid);

        if( !$subjectUser ) {
            throw $this->createNotFoundException('Unable to find Vacation Request user by id='.$userid);
        }

        $organizationalGroupInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($instid);

        if( !$organizationalGroupInstitution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //get role by roletype
        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->find( $roleId );
        if( $role ) {
//            echo "subjectUser=".$subjectUser."<br>";
//            echo "role=".$role."<br><br>";
//            foreach( $subjectUser->getRoles() as $userRole ) {
//                echo "0 userRole=".$userRole."<br>";
//            }
//            echo "<br>";

            //remove role from user
            $subjectUser->removeRole($role->getName());

//            foreach( $subjectUser->getRoles() as $userRole ) {
//                echo "1 userRole=".$userRole."<br>";
//            }
            //exit('1');

            $em->persist($subjectUser);
            $em->flush();

            //Event Log
            $event = "User ".$subjectUser." has been removed as ".$role->getAlias();
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$subjectUser,$request,'User record updated');

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );

        }

        return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
    }

    /**
     * @Route("/organizational-institution-user-add/{instid}/{roleId}/{btnName}", name="vacreq_orginst_add_user")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-user-add.html.twig")
     */
    public function addUserAction(Request $request, $instid, $roleId, $btnName )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') || false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();

        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->find($roleId);

        if( !$role ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Role by id='.$roleId);
        }

        //new simple user form: user type, user id
        $params = array(
            'cycle' => 'create',
            'readonly' => false,
            //'path' => 'vacreq_orginst_add_action_user'
        );
        $form = $this->createForm(new SimpleUserType($params));

        return array(
            'form' => $form->createView(),
            'btnName' => $btnName,
            'roleId' => $roleId,
            'instid' => $instid
        );
    }

    /**
     * @Route("/organizational-institution-user-add-action/{instid}/{roleId}", name="vacreq_orginst_add_action_user")
     * @Method({"GET"})
     */
    public function addRoleToUserAction(Request $request, $instid, $roleId )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') || false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->find($roleId);

        if( !$role ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Role by id='.$roleId);
        }


        $keytype = $request->query->get('keytype');
        $keytype = trim($keytype);

        $primaryPublicUserId = $request->query->get('primaryPublicUserId');
        $primaryPublicUserId = trim($primaryPublicUserId);

        //exit('primaryPublicUserId='.$primaryPublicUserId);

        //find user in DB
        $users = $em->getRepository('OlegUserdirectoryBundle:User')->findBy(array('keytype'=>$keytype,'primaryPublicUserId'=>$primaryPublicUserId));

        if( count($users) > 1 ) {
            throw $this->createNotFoundException('Unable to find a Single User. Found users ' . count($users) );
        }

        if( count($users) == 1 ) {
            $subjectUser = $users[0];
        }

        if( count($users) == 0 ) {
            $keytypeObj = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->find($keytype);
            $this->get('session')->getFlashBag()->set(
                'notice',
                'User ' . $primaryPublicUserId . ' (' . $keytypeObj . ')' . ' not found.'." ".
                "Please use the 'Create New User' form to add a new user."
            );
            return $this->redirect( $this->generateUrl("employees_new_user",array("user-type"=>$keytype,"user-name"=>$primaryPublicUserId)) );
        }

        $subjectUser->addRole($role);

        //exit('added '.$role.' to '.$subjectUser);

        $em->persist($subjectUser);
        $em->flush();

        //Event Log
        $event = "User ".$subjectUser." has been added as ".$role->getAlias();
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$subjectUser,$request,'User record updated');

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            $event
        );

        return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
    }


    public function processUserAuthorization( $entity, $originalOtherRoles ) {

        //$em = $this->getDoctrine()->getManager();

        ///////////////// update roles /////////////////
        //add original roles not associated with this site
        foreach( $originalOtherRoles as $role ) {
            $entity->addRole($role);
        }

        //$em->persist($entity);
        //$em->flush($entity);
        ///////////////// EOF update roles /////////////////
    }

}
