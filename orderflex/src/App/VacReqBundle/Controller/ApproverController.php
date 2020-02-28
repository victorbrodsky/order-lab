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

namespace App\VacReqBundle\Controller;

use App\VacReqBundle\Util\VacReqUtil;
use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Entity\Roles;
use App\UserdirectoryBundle\Form\SimpleUserType;
use App\UserdirectoryBundle\Util\UserUtil;
use App\VacReqBundle\Entity\VacReqCarryOver;
use App\VacReqBundle\Entity\VacReqRequest;
use App\VacReqBundle\Entity\VacReqSettings;
use App\VacReqBundle\Entity\VacReqUserCarryOver;
use App\VacReqBundle\Form\VacReqEmailusersType;
use App\VacReqBundle\Form\VacReqGroupType;
use App\VacReqBundle\Form\VacReqRequestType;
use App\VacReqBundle\Form\VacReqUserCarryOverType;
use App\VacReqBundle\Form\VacReqUserComboboxType;
use App\VacReqBundle\Form\VacReqUserType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//vacreq site

class ApproverController extends OrderAbstractController
{
    protected $vacreqUtil;

    public function __construct( VacReqUtil $vacreqUtil ) {
        $this->vacreqUtil = $vacreqUtil;
        //parent::setContainer(null);
    }

    /**
     * @Route("/groups/", name="vacreq_approvers", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Approver/approvers-list.html.twig")
     */
    //public function myRequestsAction(Request $request, VacReqUtil $vacreqUtil)
    public function myRequestsAction(Request $request)
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //$vacreqUtil = $this->get('vacreq_util');
        $vacreqUtil = $this->vacreqUtil;
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$em = $this->getDoctrine()->getManager();

        //list all organizational group (institution)
//        $roles = $em->getRepository('AppUserdirectoryBundle:User')->findRolesByObjectAction("VacReqRequest", "changestatus");
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
        if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
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
     * @Route("/carry-over-request-group/{groupId}", name="vacreq_carry_over_request_group_list", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Approver/carry-over-request-group-list.html.twig")
     */
    public function carryOverRequestGroupAction(Request $request, $groupId)
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //find role approvers by institution
        $approvers = array();
        $roleApprovers = $em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUPERVISOR', $groupId);
        $roleApprover = $roleApprovers[0];
        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
            $approvers = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleApprover->getName(),"infos.lastName",true);
        }
        //echo "approvers=".count($approvers)."<br>";

        //find role submitters by institution
//        $submitters = array();
//        $roleSubmitters = $em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUBMITTER', $groupId);
//        $roleSubmitter = $roleSubmitters[0];
//        //echo "roleSubmitter=".$roleSubmitter."<br>";
//        if( $roleSubmitter ) {
//            $submitters = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleSubmitter->getName(),"infos.lastName",true);
//        }s
        $submitters = $em->getRepository('AppUserdirectoryBundle:User')->findUsersBySitePermissionObjectActionInstitution("vacreq","VacReqRequest","create",$groupId,true);

        $organizationalGroupInstitution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($groupId);

        //vacreq_util
        //$vacreqUtil = $this->get('vacreq_util');
        $vacreqUtil = $this->vacreqUtil;
        //$vacreqUtil = $this->container->get('vacreq_util');
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
     * @Route("/organizational-institutions/{institutionId}", name="vacreq_orginst_list", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Approver/orginst-list.html.twig")
     */
    public function organizationalInstitutionAction(Request $request, $institutionId)
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => institutionId=".$institutionId."<br>";

        $em = $this->getDoctrine()->getManager();

        //find role approvers by institution
        $approvers = array();
        $roleApprovers = $em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_APPROVER', $institutionId);
        $roleApprover = $roleApprovers[0];
        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
            $approvers = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleApprover->getName(),"infos.lastName",true);
        }
        //echo "approvers=".count($approvers)."<br>";

        //find role submitters by institution
        $submitters = array();
        $roleSubmitters = $em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUBMITTER', $institutionId);
        $roleSubmitter = $roleSubmitters[0];
        //echo "roleSubmitter=".$roleSubmitter."<br>";
        if( $roleSubmitter ) {
            $submitters = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleSubmitter->getName(),"infos.lastName",true);
        }

        $organizationalGroupInstitution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($institutionId);

        //vacreq_util
        //$vacreqUtil = $this->get('vacreq_util');
        $vacreqUtil = $this->vacreqUtil;
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
     * @Route("/manage-group/{institutionId}", name="vacreq_orginst_management", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Approver/orginst-management.html.twig")
     */
    public function orgInstManagementAction(Request $request, $institutionId)
    {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => institutionId=".$institutionId."<br>";

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        //find role approvers by institution
        $approvers = array();
        $roleApprovers = $em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_APPROVER', $institutionId);
        $roleApprover = $roleApprovers[0];
        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
            $approvers = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleApprover->getName(),"infos.lastName",true);
        }
        //echo "approvers=".count($approvers)."<br>";

        //$vacreqUtil = $this->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $this->vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $institutionId) == false ) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //find role submitters by institution
        $submitters = array();
        $roleSubmitters = $em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUBMITTER', $institutionId);
        $roleSubmitter = $roleSubmitters[0];
        //echo "roleSubmitter=".$roleSubmitter."<br>";
        if( $roleSubmitter ) {
            $submitters = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleSubmitter->getName(),"infos.lastName",true);
        }

        $organizationalGroupInstitution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($institutionId);

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
     * @Route("/organizational-institution-user-management/{userid}/{instid}/{roleId}", name="vacreq_orginst_user_management", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Approver/orginst-user-management.html.twig")
     */
    public function userManagementAction(Request $request, $userid, $instid, $roleId )
    {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$userid."<br>";

        $em = $this->getDoctrine()->getManager();

        //check if logged in user has approver role for $instid
        //$vacreqUtil = $this->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $this->vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $subjectUser = $em->getRepository('AppUserdirectoryBundle:User')->find($userid);

        if( !$subjectUser ) {
            throw $this->createNotFoundException('Unable to find Vacation Request user by id='.$userid);
        }

        $organizationalGroupInstitution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($instid);

        if( !$organizationalGroupInstitution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //Original Roles not associated with this site
        //$securityUtil = $this->get('user_security_utility');
        //$originalOtherRoles = $securityUtil->getUserRolesBySite( $subjectUser, 'vacreq', false );

        //Roles
        //$securityUtil = $this->get('user_security_utility');
        //$rolesArr = $securityUtil->getSiteRolesKeyValue('vacreq');

        $roles = $em->getRepository('AppUserdirectoryBundle:Roles')->findById($roleId);
        $rolesArr = array();
        foreach( $roles as $role ) {
            //$rolesArr[$role->getName()] = $role->getAlias();
            $rolesArr[$role->getAlias()] = $role->getName(); //flipped
        }

        $params = array('roles'=>$rolesArr);

        $form = $this->createForm(
            VacReqUserType::class,
            $subjectUser,
            array(
                'form_custom_value' => $params,
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
     * Don't use it: We don't need to update the roles from the Group Management page. We need only add or remove user.
     *
     * @Route("/organizational-institution-user-update/{userid}/{instid}/{roleIds}", name="vacreq_orginst_user_update", methods={"GET", "POST"}, options={"expose"=true})
     * @Template("AppVacReqBundle/Approver/orginst-user-management.html.twig")
     */
    public function userManagementUpdateAction(Request $request, $userid, $instid, $roleIds )
    {
        exit("We don't need to update the roles from the Group Management page. We need only add or remove user.");

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if logged in user has approver role for $instid
        //$vacreqUtil = $this->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $this->vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $subjectUser = $em->getRepository('AppUserdirectoryBundle:User')->find($userid);
        if( !$subjectUser ) {
            throw $this->createNotFoundException('Unable to find Vacation Request user by id='.$userid);
        }

        $organizationalGroupInstitution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($instid);
        if( !$organizationalGroupInstitution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //echo "roleIds=".$roleIds."<br>";

        if( $roleIds || $roleIds == '0' ) {
            $roleArr = explode(',',$roleIds);
        } else {
            $roleArr = array();
        }

        $securityUtil = $this->get('user_security_utility');
        $res = $securityUtil->addOnlySiteRoles($subjectUser,$roleArr,'vacreq');

        if( $res ) {

            $originalUserSiteRoles = $res['originalUserSiteRoles'];
            $newUserSiteRoles = $res['newUserSiteRoles'];

            //testing
//            $event = $organizationalGroupInstitution.": Roles of ".$subjectUser . " has been changed. Original roles: ".implode(", ",$originalUserSiteRoles).";<br> New roles:".implode(", ",$newUserSiteRoles);
//            echo "event=".$event."<br>";
//            exit('update');

            $em->persist($subjectUser);
            $em->flush();

            //Event Log
            $eventType = "Business/Vacation Group Updated"; //"User record updated";
            $event = $organizationalGroupInstitution.": Roles of ".$subjectUser . " has been changed. Original roles: ".implode(", ",$originalUserSiteRoles).";<br> New roles:".implode(", ",$newUserSiteRoles);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'),$event,$user,$organizationalGroupInstitution,$request,$eventType);

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
     * @Route("/organizational-institution-user-remove/{userid}/{instid}/{roleId}", name="vacreq_orginst_user_remove", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Approver/orginst-user-management.html.twig")
     */
    public function removeUserAction(Request $request, $userid, $instid, $roleId )
    {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if logged in user has approver role for $instid
        //$vacreqUtil = $this->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $this->vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $subjectUser = $em->getRepository('AppUserdirectoryBundle:User')->find($userid);
        if( !$subjectUser ) {
            throw $this->createNotFoundException('Unable to find Vacation Request user by id='.$userid);
        }

        $organizationalGroupInstitution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($instid);
        if( !$organizationalGroupInstitution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //get role by roletype
        $role = $em->getRepository('AppUserdirectoryBundle:Roles')->find( $roleId );
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
            //$userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'),$event,$user,$organizationalGroupInstitution,$request,$eventType);
            $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'),$event,$user,$subjectUser,$request,$eventType);

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );

        }

        return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
    }

    /**
     * @Route("/organizational-institution-user-add/{instid}/{roleId}/{btnName}", name="vacreq_orginst_add_user", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Approver/orginst-user-add.html.twig")
     */
    public function addUserAction(Request $request, $instid, $roleId, $btnName )
    {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //check if logged in user has approver role for $instid
        //$vacreqUtil = $this->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $this->vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false ) {
            exit('no permission');
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $role = $em->getRepository('AppUserdirectoryBundle:Roles')->find($roleId);

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
            $form = $this->createForm(SimpleUserType::class,null,array('form_custom_value'=>$params));
        }else {
            $params = array(
                'btnName' => $btnName
            );
            $form = $this->createForm(VacReqUserComboboxType::class,null,array('form_custom_value'=>$params));
        }

        return array(
            'form' => $form->createView(),
            'btnName' => $btnName,
            'roleId' => $roleId,
            'instid' => $instid
        );
    }

    /**
     * @Route("/organizational-institution-user-add-action/{instid}/{roleId}", name="vacreq_orginst_add_action_user", methods={"GET", "POST"})
     */
    public function addRoleToUserAction(Request $request, $instid, $roleId )
    {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $role = $em->getRepository('AppUserdirectoryBundle:Roles')->find($roleId);
        if( !$role ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Role by id='.$roleId);
        }

        $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }


        $params = array(
            'cycle' => 'create',
            'readonly' => false,
        );
        $form = $this->createForm(VacReqUserComboboxType::class,null,array('form_custom_value'=>$params));

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

            //$subjectUser = $em->getRepository('AppUserdirectoryBundle:User')->find($userObject->getId());
            $globalEventArr[] = $userObject."";
            $event = $institution . ": user has been added as " . $role->getAlias() . ": " . $userObject;
            $eventType = "Business/Vacation Group Updated";

            //Event Log
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $event, $user, $userObject, $request, $eventType);
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
     * @Route("/add-group", name="vacreq_group_add", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Approver/orginst-add.html.twig")
     */
    public function addGroupAction(Request $request )
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

//        $role = $em->getRepository('AppUserdirectoryBundle:Roles')->find($roleId);
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
        $form = $this->createForm(VacReqGroupType::class, null, array('form_custom_value'=>$params));

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $userSecUtil = $this->container->get('user_security_utility');
            $site = $em->getRepository('AppUserdirectoryBundle:SiteList')->findOneByAbbreviation('vacreq');

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
            $approverRole = $em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($roleName);
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
            $submitterRole = $em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($roleName);
            if( !$submitterRole ) {
                $submitterRole = new Roles();
                $submitterRole = $userSecUtil->setDefaultList($submitterRole, null, $user, $roleName);
                $submitterRole->setLevel(30);
                $submitterRole->setAlias('Vacation Request Submitter for the ' . $institution->getName());
                $submitterRole->setDescription('Can search and create vacation requests for specified service');
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
                $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $event, $user, $institution, $request, 'Business/Vacation Group Created');

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
     * @Route("/organizational-institution-remove/{instid}", name="vacreq_group_remove", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Approver/orginst-user-add.html.twig")
     */
    public function removeGroupAction(Request $request, $instid )
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //exit('not implemented');

        $removedRoles = array();

        $removedRoles[] = $this->removeVacReqGroupByInstitution($instid,"ROLE_VACREQ_APPROVER_",$request);
        $removedRoles[] = $this->removeVacReqGroupByInstitution($instid,"ROLE_VACREQ_SUBMITTER_",$request);

        if( count($removedRoles) > 0 ) {
            //Event Log
            $event = "Business/Vacation Group [" . $institution->getTreeName() . "] has been removed by removing roles: ".implode(", ",$removedRoles);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $event, $user, $institution, $request, 'Business/Vacation Role Removed');

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
    public function removeVacReqGroupByInstitution($instid,$rolePartialName,$request=null) {
        $em = $this->getDoctrine()->getManager();

        $roleName = null;
        $userNamesArr = array();

        //1) find approver roles with institution
        $role = null;
        $roles = $em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName("vacreq",$rolePartialName,$instid);
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
            $users = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleName,"infos.lastName",true);
            foreach( $users as $user ) {
                $user->removeRole($roleName);
                $userNamesArr[] = $user."";
            }

            //Do not delete the roles themselves and do not delete the organizational group from the Institution tree.
            //$em->remove($role);

            $em->flush();
        }

        //Event Log
        if( $role && count($userNamesArr) > 0 ) {
            $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($instid);
            $institutionTreeName = null;
            if( $institution ) {
                $institutionTreeName = $institution->getTreeName();
            }
            $eventType = "Business/Vacation Group Updated";
            $event = $institutionTreeName.": The role " . $roleName . " has been removed from the users: " . implode(", ", $userNamesArr);
            $userSecUtil = $this->container->get('user_security_utility');
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $event, $user, $institution, $request, $eventType);
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
     * @Route("/organizational-institution-emailusers/{instid}", name="vacreq_orginst_emailusers", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Approver/orginst-emailusers.html.twig")
     */
    public function emailUsersAction(Request $request, $instid)
    {

        $em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        //vacreq_util
        //$vacreqUtil = $this->get('vacreq_util');
        $entity = $this->vacreqUtil->getSettingsByInstitution($instid);

        $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        if( !$entity ) {
            $entity = new VacReqSettings($institution);
        }

        $params = array();

        $form = $this->createForm(
            VacReqEmailusersType::class,
            $entity,
            array(
                'form_custom_value' => $params,
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
     * @Route("/organizational-institution-emailusers-update/{instid}/{users}", name="vacreq_orginst_emailusers_update", methods={"GET", "POST"}, options={"expose"=true})
     */
    public function emailUsersUpdateAction(Request $request, $instid, $users)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

//        $keytype = $request->query->get('keytype');
//        $keytype = trim($keytype);

        $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //vacreq_util
        //$vacreqUtil = $this->get('vacreq_util');
        $entity = $this->vacreqUtil->getSettingsByInstitution($instid);

        if( !$entity ) {
            $entity = new VacReqSettings($institution);
        }

        $res = $this->vacreqUtil->settingsAddRemoveUsers( $entity, $users );

//        foreach( explode(",",$users) as $emailUserStr ) {
//
//            echo "emailUserStr=".$emailUserStr."<br>";
//            $emailUser = $em->getRepository('AppUserdirectoryBundle:User')->find($emailUserStr);
//            $entity->addEmailUser($emailUser);

        if( $res ) {

            $originalUsers = $res['originalUsers'];
            $newUsers = $res['newUsers'];

            $em->persist($entity);
            $em->flush();

            //Event Log
            $event = "Email users has been updated for Business/Vacation Group " . $institution .
                "; Original email users=".implode(", ",$originalUsers).
                "; New email users=".implode(", ",$newUsers);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $event, $user, $institution, $request, 'Business/Vacation Group Updated');

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
     * @Route("/my-group/", name="vacreq_mygroup", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Group/mygroup.html.twig")
     */
    public function myGroupAction(Request $request)
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        set_time_limit(600);
        ini_set('memory_limit', '2048M');

        //$em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //vacreq_util
        //$vacreqUtil = $this->get('vacreq_util');

        $userids = null;

        //find groups for logged in user
        //$params = array('asObject'=>true,'roleSubStrArr'=>array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR'));
        //$groups = $vacreqUtil->getVacReqOrganizationalInstitutions($user,$params);  //"business-vacation",true);
        $groupParams = array('asObject'=>true);
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
        $groupParams['exceptPermissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
        $groupParams['statusArr'] = array('default','user-added');
        $groups = $this->vacreqUtil->getGroupsByPermission($user,$groupParams);
        //echo "groups=".count($groups)."<br>";

        //accrued days up to this month calculated by vacationAccruedDaysPerMonth
        $accruedDays = $this->vacreqUtil->getAccruedDaysUpToThisMonth();

        //Current Academic Year
//        $currentYear = new \DateTime();
//        $currentYear = $currentYear->format('Y');
//        $previousYear = $currentYear - 1;
//        $yearRange = $previousYear."-".$currentYear;
        $yearRange = $this->vacreqUtil->getCurrentAcademicYearRange();

        /////////////// users filter form ///////////////////
        $filterform = $this->createFormBuilder()
            ->add('filterusers', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                'label' => false,
                'required' => false,
                'multiple' => true,
                //'choice_label' => 'name',
                'attr' => array('class'=>'combobox combobox-width', 'placeholder'=>"Employee"),
                //'disabled' => true,    //$readOnly,   //($this->params['review'] ? true : false),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.infos","infos")
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->andWhere("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'")
                        //->andWhere("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                        ->orderBy("infos.lastName","ASC");
                },
            ))
            ->add('filter', SubmitType::class, array('label' => 'Filter','attr' => array('class' => 'btn btn-sm btn-default')))
            ->getForm();

        $filterform->handleRequest($request);

        if( $filterform->isSubmitted() && $filterform->isValid() ) {
            $users = $filterform["filterusers"]->getData();
            $useridsArr = array();
            foreach( $users as $thisUser ) {
                $useridsArr[] = $thisUser->getId();
            }
            $userids = implode("-",$useridsArr);
        }
        /////////////// EOF: users filter form ///////////////////


        return array(
            'groups' => $groups,
            'accruedDays' => $accruedDays,
            'yearRange' => $yearRange,
            //'entity' => $entity,
            'filterform' => $filterform->createView(),
            'userids' => $userids
            //'organizationalGroupName' => $institution."",
            //'organizationalGroupId' => $instid,
        );
    }

    /**
     * @Route("/my-single-group/{groupId}/{userids}", name="vacreq_mysinglegroup", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Group/my-single-group.html.twig")
     */
    public function mySingleGroupAction( Request $request, $groupId, $userids )
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo "groupId=".$groupId."<br>";
        $em = $this->getDoctrine()->getManager();
        //$vacreqUtil = $this->get('vacreq_util');

        //find role submitters by institution
        //$submitters = $vacreqUtil->getSubmittersFromSubmittedRequestsByGroup($groupId);
        $submitters = $this->vacreqUtil->getUsersByGroupId($groupId,"ROLE_VACREQ_SUBMITTER");

        //filter users
        if( $userids ) {
            //echo "userids=$userids<br>";
            $useridsArr = explode("-",$userids);
            $newSubmitters = array();
            foreach( $submitters as $submitter ) {
                if( in_array($submitter->getId(),$useridsArr) ) {
                    $newSubmitters[] = $submitter;
                }
            }
            $submitters = $newSubmitters;
        }

        $group = $em->getRepository('AppUserdirectoryBundle:Institution')->find($groupId);

        $yearRanges = array();
        //Current Academic Year
        //$currentYear = new \DateTime();
        //$currentYear = $currentYear->format('Y');
        //$previousYear = $currentYear - 1;
        //$yearRanges[] = $previousYear."-".$currentYear;
        //$yearRange = $vacreqUtil->getCurrentAcademicYearRange();
        $yearRanges[] = $this->vacreqUtil->getCurrentAcademicYearRange();

        //Current Academic Year - 1
        //$currentYear = $currentYear - 1;
        //$previousYear = $currentYear - 1;
        //$yearRanges[] = $previousYear."-".$currentYear;
        $yearRanges[] = $this->vacreqUtil->getPreviousAcademicYearRange();

        //Current Academic Year - 2
        //$currentYear = $currentYear - 1;
        //$previousYear = $currentYear - 1;
        //$yearRanges[] = $previousYear."-".$currentYear;
        $yearRanges[] = $this->vacreqUtil->getPreviousAcademicYearRange(1);

        $yearRangesColor = array('#c1e2b3','#d0e9c6','#dff0d8');

        return array(
            'groupId' => $groupId,
            'submitters' => $submitters,
            'groupName' => $group."",
            'yearRanges' => $yearRanges,
            'yearRangesColor' => $yearRangesColor,
            'totalAllocatedDays' => $this->vacreqUtil->getTotalAccruedDays()
        );
    }



}
