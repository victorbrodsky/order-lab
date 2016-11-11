<?php

namespace Oleg\VacReqBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\Roles;
use Oleg\UserdirectoryBundle\Form\SimpleUserType;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\VacReqBundle\Entity\VacReqCarryOver;
use Oleg\VacReqBundle\Entity\VacReqRequest;
use Oleg\VacReqBundle\Entity\VacReqSettings;
use Oleg\VacReqBundle\Entity\VacReqUserCarryOver;
use Oleg\VacReqBundle\Form\VacReqEmailusersType;
use Oleg\VacReqBundle\Form\VacReqGroupType;
use Oleg\VacReqBundle\Form\VacReqRequestType;
use Oleg\VacReqBundle\Form\VacReqUserCarryOverType;
use Oleg\VacReqBundle\Form\VacReqUserComboboxType;
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
     * @Route("/groups/", name="vacreq_approvers")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:approvers-list.html.twig")
     */
    public function myRequestsAction(Request $request)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $vacreqUtil = $this->get('vacreq_util');
        $user = $this->get('security.context')->getToken()->getUser();
        //$em = $this->getDoctrine()->getManager();

        //list all organizational group (institution)
//        $roles = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesByObjectAction("VacReqRequest", "changestatus");
//        $organizationalInstitutions = array();
//        foreach( $roles as $role ) {
//            $organizationalInstitutions[] = $role->getInstitution();
//        }

        //get submitter groups
        $groupParams = array('asObject'=>true);
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
        $groupParams['exceptPermissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
        $groupParams['statusArr'] = array('default','user-added');
        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);
        //echo "organizationalInstitutions=".count($organizationalInstitutions)."<br>";

        //get carryover approver groups
        $carryOverRequestGroups = array();
        if( $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
            $carryOverGroupParams = array('asObject'=>true);
            $carryOverGroupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
            $carryOverRequestGroups = $vacreqUtil->getGroupsByPermission($user,$carryOverGroupParams);
        }
        //echo "carryOverRequestGroups=".count($carryOverRequestGroups)."<br>";


        return array(
            'organizationalInstitutions' => $organizationalInstitutions,
            'carryOverRequestGroups' => $carryOverRequestGroups
        );
    }



    /**
     * Display a collapse for a given carry over request group submitters
     *
     * @Route("/carry-over-request-group/{groupId}", name="vacreq_carry_over_request_group_list")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:carry-over-request-group-list.html.twig")
     */
    public function carryOverRequestGroupAction(Request $request, $groupId)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //find role approvers by institution
        $approvers = array();
        $roleApprovers = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUPERVISOR', $groupId);
        $roleApprover = $roleApprovers[0];
        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
            $approvers = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleApprover->getName(),"infos.lastName",true);
        }
        //echo "approvers=".count($approvers)."<br>";

        //find role submitters by institution
//        $submitters = array();
//        $roleSubmitters = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUBMITTER', $groupId);
//        $roleSubmitter = $roleSubmitters[0];
//        //echo "roleSubmitter=".$roleSubmitter."<br>";
//        if( $roleSubmitter ) {
//            $submitters = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleSubmitter->getName(),"infos.lastName",true);
//        }s
        $submitters = $em->getRepository('OlegUserdirectoryBundle:User')->findUsersBySitePermissionObjectActionInstitution("vacreq","VacReqRequest","create",$groupId,true);

        $organizationalGroupInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($groupId);

        //vacreq_util
        $vacreqUtil = $this->get('vacreq_util');
        $settings = $vacreqUtil->getSettingsByInstitution($groupId);

        return array(
            'approvers' => $approvers,
            'submitters' => $submitters,
            'organizationalGroupId' => $groupId,
            'organizationalGroupName' => $organizationalGroupInstitution."",
            'settings' => $settings
        );
    }



    /**
     * Display a collapse for a given organizational institution
     *
     * @Route("/organizational-institutions/{institutionId}", name="vacreq_orginst_list")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-list.html.twig")
     */
    public function organizationalInstitutionAction(Request $request, $institutionId)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
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
            $approvers = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleApprover->getName(),"infos.lastName",true);
        }
        //echo "approvers=".count($approvers)."<br>";

        //find role submitters by institution
        $submitters = array();
        $roleSubmitters = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUBMITTER', $institutionId);
        $roleSubmitter = $roleSubmitters[0];
        //echo "roleSubmitter=".$roleSubmitter."<br>";
        if( $roleSubmitter ) {
            $submitters = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleSubmitter->getName(),"infos.lastName",true);
        }

        $organizationalGroupInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($institutionId);

        //vacreq_util
        $vacreqUtil = $this->get('vacreq_util');
        $settings = $vacreqUtil->getSettingsByInstitution($institutionId);

        return array(
            'approvers' => $approvers,
            'submitters' => $submitters,
            'organizationalGroupId' => $institutionId,
            'organizationalGroupName' => $organizationalGroupInstitution."",
            'settings' => $settings
        );
    }



    /**
     * General management page for a given organizational institution
     *
     * @Route("/manage-group/{institutionId}", name="vacreq_orginst_management")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-management.html.twig")
     */
    public function orgInstManagementAction(Request $request, $institutionId)
    {

        if(
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => institutionId=".$institutionId."<br>";

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        //find role approvers by institution
        $approvers = array();
        $roleApprovers = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_APPROVER', $institutionId);
        $roleApprover = $roleApprovers[0];
        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
            $approvers = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleApprover->getName(),"infos.lastName",true);
        }
        //echo "approvers=".count($approvers)."<br>";

        $vacreqUtil = $this->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $institutionId) == false ) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //find role submitters by institution
        $submitters = array();
        $roleSubmitters = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUBMITTER', $institutionId);
        $roleSubmitter = $roleSubmitters[0];
        //echo "roleSubmitter=".$roleSubmitter."<br>";
        if( $roleSubmitter ) {
            $submitters = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleSubmitter->getName(),"infos.lastName",true);
        }

        $organizationalGroupInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($institutionId);

        $roleApproverId = null;
        if( $roleApprover ) {
            //echo "roleApprover=".$roleApprover."<br>";
            $roleApproverId = $roleApprover->getId();
        }

        $roleSubmitterId = null;
        if( $roleSubmitter ) {
            $roleSubmitterId = $roleSubmitter->getId();
        }

        //echo "approverRoleId=".$roleApproverId."<br>";

        return array(
            'approvers' => $approvers,
            'approverRoleId' => $roleApproverId,
            'submitters' => $submitters,
            'submitterRoleId' => $roleSubmitterId,
            'organizationalGroupId' => $institutionId,
            'organizationalGroupName' => $organizationalGroupInstitution.""
        );
    }



    /**
     * A particular management page for a given organizational institution and user to show a well to update the role or remove a user
     *
     * @Route("/organizational-institution-user-management/{userid}/{instid}/{roleId}", name="vacreq_orginst_user_management")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-user-management.html.twig")
     */
    public function userManagementAction(Request $request, $userid, $instid, $roleId )
    {

        if(
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$userid."<br>";

        $em = $this->getDoctrine()->getManager();

        //check if logged in user has approver role for $instid
        $vacreqUtil = $this->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

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
        //$securityUtil = $this->get('order_security_utility');
        //$rolesArr = $securityUtil->getSiteRolesKeyValue('vacreq');

        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findById($roleId);
        $rolesArr = array();
        foreach( $roles as $role ) {
            $rolesArr[$role->getName()] = $role->getAlias();
        }

        $params = array('roles'=>$rolesArr);

        $form = $this->createForm(
            new VacReqUserType($params),
            $subjectUser,
            array(
                'method' => "POST",
                //'action' => $action
            )
        );

        return array(
            'form' => $form->createView(),
            'entity' => $subjectUser,
            'institution' => $organizationalGroupInstitution,
            'roleId' => $roleId
        );
    }


    /**
     * Update for a userManagementAction page
     *
     * @Route("/organizational-institution-user-update/{userid}/{instid}/{roleIds}", name="vacreq_orginst_user_update", options={"expose"=true})
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-user-management.html.twig")
     */
    public function userManagementUpdateAction(Request $request, $userid, $instid, $roleIds )
    {

        if(
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        //check if logged in user has approver role for $instid
        $vacreqUtil = $this->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

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
            $eventType = "Business/Vacation Group Updated"; //"User record updated";
            $event = $organizationalGroupInstitution.": Roles of ".$subjectUser . " has been changed. Original roles:".implode(",",$originalUserSiteRoles)."; New roles:".implode(",",$newUserSiteRoles);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$organizationalGroupInstitution,$request,$eventType);

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

        if(
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        //check if logged in user has approver role for $instid
        $vacreqUtil = $this->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

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
            $eventType = "Business/Vacation Group Updated";
            $event = $organizationalGroupInstitution.": User ".$subjectUser." has been removed as ".$role->getAlias();
            $userSecUtil = $this->container->get('user_security_utility');
            //$userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$organizationalGroupInstitution,$request,$eventType);
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$subjectUser,$request,$eventType);

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

        if(
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //check if logged in user has approver role for $instid
        $vacreqUtil = $this->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false ) {
            exit('no permission');
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->find($roleId);

        if( !$role ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Role by id='.$roleId);
        }

        if(0) {
            //new simple user form: user type, user id
            $params = array(
                'cycle' => 'create',
                'readonly' => false,
                //'path' => 'vacreq_orginst_add_action_user'
            );
            $form = $this->createForm(new SimpleUserType($params));
        }else {
            $params = array(
                'btnName' => $btnName
            );
            $form = $this->createForm(new VacReqUserComboboxType($params));
        }

        return array(
            'form' => $form->createView(),
            'btnName' => $btnName,
            'roleId' => $roleId,
            'instid' => $instid
        );
    }

    /**
     * @Route("/organizational-institution-user-add-action/{instid}/{roleId}", name="vacreq_orginst_add_action_user")
     * @Method({"GET","POST"})
     */
    public function addRoleToUserAction(Request $request, $instid, $roleId )
    {

        if(
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->find($roleId);
        if( !$role ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Role by id='.$roleId);
        }

        $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }


        $params = array(
            'cycle' => 'create',
            'readonly' => false,
        );
        $form = $this->createForm(new VacReqUserComboboxType($params));

        $form->handleRequest($request);

        //$users = $form['users']->getData();
        $users = $form->get('users')->getData();

        //$usersArr = array();

        foreach( $users as $thisUser ) {
            //echo "user=".$user."<br>";
            $thisUser->addRole($role);
            $em->persist($thisUser);
            //$usersArr = $thisUser;
        }

        //$users = $request->query->get('users');
        //$users = trim($users);

        $em->flush();

        $globalEventArr = array();
        $globalEventArr[] = $institution.": the following users have been added:";

        foreach( $users as $userObject ) {

            //$subjectUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($userObject->getId());
            $globalEventArr[] = $userObject."";
            $event = $institution . ": user has been added as " . $role->getAlias() . ": " . $userObject;
            $eventType = "Business/Vacation Group Updated";

            //Event Log
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'), $event, $user, $userObject, $request, $eventType);
        }
        //exit();

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            implode(" ",$globalEventArr)
        );

        return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
    }



    /**
     * @Route("/add-group", name="vacreq_group_add")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-add.html.twig")
     */
    public function addGroupAction(Request $request )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

//        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->find($roleId);
//
//        if( !$role ) {
//            throw $this->createNotFoundException('Unable to find Vacation Request Role by id='.$roleId);
//        }

        //new simple user form: user type, user id
        $params = array(
            'em' => $em,
            'cycle' => 'create',
            'readonly' => false,
            //'path' => 'vacreq_orginst_add_action_user'
        );
        $form = $this->createForm(new VacReqGroupType($params));

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $userSecUtil = $this->container->get('user_security_utility');
            $site = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation('vacreq');

            //add group
            //$instid = null;
            $institution = $form["institution"]->getData();

            $instid = $institution->getId();
            //exit('instid='.$instid);

            $count = 0;

            //get ROLE NAME: Pathology Informatics => PATHOLOGYINFORMATCS
            $roleNameBase = str_replace(" ","",$institution->getName());
            $roleNameBase = strtoupper($roleNameBase);

            //create approver role
            $roleName = "ROLE_VACREQ_APPROVER_".$roleNameBase;
            $approverRole = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($roleName);
            if( !$approverRole ) {
                $approverRole = new Roles();
                $approverRole = $userSecUtil->setDefaultList($approverRole, null, $user, $roleName);
                $approverRole->setLevel(50);
                $approverRole->setAlias('Vacation Request Approver for the ' . $institution->getName());
                $approverRole->setDescription('Can search and approve vacation requests for specified service');
                $approverRole->addSite($site);
                $approverRole->setInstitution($institution);
                $userSecUtil->checkAndAddPermissionToRole($approverRole, "Approve a Vacation Request", "VacReqRequest", "changestatus");

                $em->persist($approverRole);
                $em->flush($approverRole);

                $count++;
            } else {
                $approverType = $approverRole->getType();
                if( $approverType != 'default' && $approverType != 'user-added' ) {
                    $approverRole->setType('default');
                    $em->persist($approverRole);
                    $em->flush($approverRole);
                    $count++;
                }
            }

            //create submitter role
            $roleName = "ROLE_VACREQ_SUBMITTER_".$roleNameBase;
            $submitterRole = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($roleName);
            if( !$submitterRole ) {
                $submitterRole = new Roles();
                $submitterRole = $userSecUtil->setDefaultList($submitterRole, null, $user, $roleName);
                $submitterRole->setLevel(30);
                $submitterRole->setAlias('Vacation Request Approver for the ' . $institution->getName());
                $submitterRole->setDescription('Can search and approve vacation requests for specified service');
                $submitterRole->addSite($site);
                $submitterRole->setInstitution($institution);
                $userSecUtil->checkAndAddPermissionToRole($submitterRole, "Submit a Vacation Request", "VacReqRequest", "create");

                $em->persist($submitterRole);
                $em->flush($submitterRole);

                $count++;
            } else {
                $submitterType = $submitterRole->getType();
                if( $submitterType != 'default' && $submitterType != 'user-added' ) {
                    $submitterRole->setType('default');
                    $em->persist($submitterRole);
                    $em->flush($submitterRole);
                    $count++;
                }
            }

            if( $count > 0 ) {
                //Event Log
                $event = "New Business/Vacation Group " . $roleNameBase . " has been created for " . $institution->getName();
                $userSecUtil = $this->container->get('user_security_utility');
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'), $event, $user, $institution, $request, 'Business/Vacation Group Created');

                //Flash
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $event
                );
            }

            return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
        }

            return array(
            'form' => $form->createView(),
            //'roleId' => $roleId,
            //'instid' => $instid
        );
    }


    /**
     * It should ONLY remove/strip all of THIS GROUP's roles from all users.
     * Do not delete the roles themselves and do not delete the organizational group from the Institution tree.
     * //TODO: Why is this "Group" still shown? How do I delete it in order for it not to show on this page? https://bitbucket.org/weillcornellpathology/call-logbook-plan/issues/32/fixes-for-gina
     *
     * @Route("/organizational-institution-remove/{instid}", name="vacreq_group_remove")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-user-add.html.twig")
     */
    public function removeGroupAction(Request $request, $instid )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //exit('not implemented');

        $removedRoles = array();

        $removedRoles[] = $this->removeVacReqGroupByInstitution($instid,"ROLE_VACREQ_APPROVER_");
        $removedRoles[] = $this->removeVacReqGroupByInstitution($instid,"ROLE_VACREQ_SUBMITTER_");

        if( count($removedRoles) > 0 ) {
            //Event Log
            $event = "Business/Vacation Group " . $institution . " has been removed by removing roles:".implode(", ",$removedRoles);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'), $event, $user, $institution, $request, 'Business/Vacation Role Removed');

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );
        }

        return $this->redirectToRoute('vacreq_approvers');
    }

    //remove/strip all of THIS GROUP's roles from all users.
    //Do not delete the roles themselves and do not delete the organizational group from the Institution tree.
    public function removeVacReqGroupByInstitution($instid,$rolePartialName) {
        $em = $this->getDoctrine()->getManager();

        $roleName = null;

        //1) find approver roles with institution
        $role = null;
        $roles = $em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName("vacreq",$rolePartialName,$instid);
        if( count($roles)>0 ) {
            $role = $roles[0];
            $roleName = $role->getName();
        }

        //1a) set ROLE_VACREQ_SUBMITTER_ role status disabled
        if( $rolePartialName == "ROLE_VACREQ_SUBMITTER_" ) {
            foreach ($roles as $thisRole) {
                $thisRole->setType('disabled');
            }
        }

        //2) remove approver role from all users
        if( $role ) {
            $users = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleName,"infos.lastName",true);
            foreach( $users as $user ) {
                $user->removeRole($roleName);
            }

            //Do not delete the roles themselves and do not delete the organizational group from the Institution tree.
            //$em->remove($role);

            $em->flush();
        }

        return $roleName;
    }

//    public function processUserAuthorization( $entity, $originalOtherRoles ) {
//
//        //$em = $this->getDoctrine()->getManager();
//
//        ///////////////// update roles /////////////////
//        //add original roles not associated with this site
//        foreach( $originalOtherRoles as $role ) {
//            $entity->addRole($role);
//        }
//
//        //$em->persist($entity);
//        //$em->flush($entity);
//        ///////////////// EOF update roles /////////////////
//    }


    /**
     * @Route("/organizational-institution-emailusers/{instid}", name="vacreq_orginst_emailusers")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-emailusers.html.twig")
     */
    public function emailUsersAction(Request $request, $instid)
    {

        $em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.context')->getToken()->getUser();

        //vacreq_util
        $vacreqUtil = $this->get('vacreq_util');
        $entity = $vacreqUtil->getSettingsByInstitution($instid);

        $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        if( !$entity ) {
            $entity = new VacReqSettings($institution);
        }

        $params = array();

        $form = $this->createForm(
            new VacReqEmailusersType($params),
            $entity,
            array(
                'method' => "POST",
                //'action' => $action
            )
        );

//        $form->handleRequest($request);
//
//        if( $form->isSubmitted() && $form->isValid() ) {
//
//            foreach( $entity->getEmailUsers() as $emailUser ) {
//                echo "emailUser=".$emailUser."<br>";
//            }
//            exit();
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($entity);
//            $em->flush();
//
//            return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
//        }
//        exit('form not valid');

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'organizationalGroupName' => $institution."",
            'organizationalGroupId' => $instid,
        );
    }
    /**
     * @Route("/organizational-institution-emailusers-update/{instid}/{users}", name="vacreq_orginst_emailusers_update", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function emailUsersUpdateAction(Request $request, $instid, $users)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

//        $keytype = $request->query->get('keytype');
//        $keytype = trim($keytype);

        $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //vacreq_util
        $vacreqUtil = $this->get('vacreq_util');
        $entity = $vacreqUtil->getSettingsByInstitution($instid);

        if( !$entity ) {
            $entity = new VacReqSettings($institution);
        }

        $res = $vacreqUtil->settingsAddRemoveUsers( $entity, $users );

//        foreach( explode(",",$users) as $emailUserStr ) {
//
//            echo "emailUserStr=".$emailUserStr."<br>";
//            $emailUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($emailUserStr);
//            $entity->addEmailUser($emailUser);

        if( $res ) {

            $originalUsers = $res['originalUsers'];
            $newUsers = $res['newUsers'];

            $em->persist($entity);
            $em->flush();

            //Event Log
            $event = "Email users has been updated for Business/Vacation Group " . $institution .
                "; Original email users=".implode(",",$originalUsers).
                "; New email users=".implode(",",$newUsers);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'), $event, $user, $institution, $request, 'Business/Vacation Group Updated');

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );

        }

//        }//foreach


        return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));

    }



    //My Group vacreq_mygroup
    /**
     * @Route("/my-group/", name="vacreq_mygroup")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Group:mygroup.html.twig")
     */
    public function myGroupAction(Request $request)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //$em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        //vacreq_util
        $vacreqUtil = $this->get('vacreq_util');

        //find groups for logged in user
        //$params = array('asObject'=>true,'roleSubStrArr'=>array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR'));
        //$groups = $vacreqUtil->getVacReqOrganizationalInstitutions($user,$params);  //"business-vacation",true);
        $groupParams = array('asObject'=>true);
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
        $groupParams['exceptPermissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
        $groupParams['statusArr'] = array('default','user-added');
        $groups = $vacreqUtil->getGroupsByPermission($user,$groupParams);
        //echo "groups=".count($groups)."<br>";

        //accrued days up to this month calculated by vacationAccruedDaysPerMonth
        $accruedDays = $vacreqUtil->getAccruedDaysUpToThisMonth();

        //Current Academic Year
//        $currentYear = new \DateTime();
//        $currentYear = $currentYear->format('Y');
//        $previousYear = $currentYear - 1;
//        $yearRange = $previousYear."-".$currentYear;
        $yearRange = $vacreqUtil->getCurrentAcademicYearRange();

        return array(
            'groups' => $groups,
            'accruedDays' => $accruedDays,
            'yearRange' => $yearRange,
            //'entity' => $entity,
            //'form' => $form->createView(),
            //'organizationalGroupName' => $institution."",
            //'organizationalGroupId' => $instid,
        );
    }

    /**
     * @Route("/my-single-group/{groupId}", name="vacreq_mysinglegroup")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Group:my-single-group.html.twig")
     */
    public function mySingleGroupAction(Request $request, $groupId)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo "groupId=".$groupId."<br>";
        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->get('vacreq_util');

        //find role submitters by institution
        //$submitters = $vacreqUtil->getSubmittersFromSubmittedRequestsByGroup($groupId);
        $submitters = $vacreqUtil->getUsersByGroupId($groupId,"ROLE_VACREQ_SUBMITTER");

        $group = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($groupId);

        $yearRanges = array();
        //Current Academic Year
        //$currentYear = new \DateTime();
        //$currentYear = $currentYear->format('Y');
        //$previousYear = $currentYear - 1;
        //$yearRanges[] = $previousYear."-".$currentYear;
        //$yearRange = $vacreqUtil->getCurrentAcademicYearRange();
        $yearRanges[] = $vacreqUtil->getCurrentAcademicYearRange();

        //Current Academic Year - 1
        //$currentYear = $currentYear - 1;
        //$previousYear = $currentYear - 1;
        //$yearRanges[] = $previousYear."-".$currentYear;
        $yearRanges[] = $vacreqUtil->getPreviousAcademicYearRange();

        //Current Academic Year - 2
        //$currentYear = $currentYear - 1;
        //$previousYear = $currentYear - 1;
        //$yearRanges[] = $previousYear."-".$currentYear;
        $yearRanges[] = $vacreqUtil->getPreviousAcademicYearRange(1);

        $yearRangesColor = array('#c1e2b3','#d0e9c6','#dff0d8');

        return array(
            'groupId' => $groupId,
            'submitters' => $submitters,
            'groupName' => $group."",
            'yearRanges' => $yearRanges,
            'yearRangesColor' => $yearRangesColor,
            'totalAllocatedDays' => $vacreqUtil->getTotalAccruedDays()
        );
    }



}
