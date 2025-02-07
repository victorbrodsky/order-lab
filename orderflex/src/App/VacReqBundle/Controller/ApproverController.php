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



use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution


use App\UserdirectoryBundle\Entity\SiteList; //process.py script: replaced namespace by ::class: added use line for classname=SiteList


use App\VacReqBundle\Entity\VacReqApprovalTypeList; //process.py script: replaced namespace by ::class: added use line for classname=VacReqApprovalTypeList
use App\VacReqBundle\Form\VacReqApprovalGroupType;
use App\VacReqBundle\Form\VacReqGroupManageApprovaltypesType;
use App\VacReqBundle\Form\VacReqSummaryFilterType;
use App\VacReqBundle\Util\VacReqUtil;
use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Entity\Roles;
use App\UserdirectoryBundle\Form\SimpleUserType;
use App\UserdirectoryBundle\Util\UserUtil;
use App\VacReqBundle\Entity\VacReqCarryOver;
use App\VacReqBundle\Entity\VacReqRequest;
use App\VacReqBundle\Entity\VacReqSettings;
use App\VacReqBundle\Entity\VacReqUserCarryOver;
use App\VacReqBundle\Form\VacReqGroupManageEmailusersType;
use App\VacReqBundle\Form\VacReqGroupType;
use App\VacReqBundle\Form\VacReqRequestType;
use App\VacReqBundle\Form\VacReqUserCarryOverType;
use App\VacReqBundle\Form\VacReqUserComboboxType;
use App\VacReqBundle\Form\VacReqUserType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
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

    //public function myRequestsAction(Request $request, VacReqUtil $vacreqUtil)
    #[Route(path: '/groups/', name: 'vacreq_approvers', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/approvers-list.html.twig')]
    public function myRequestsAction(Request $request)
    {

        if( false == $this->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //$vacreqUtil = $this->container->get('vacreq_util');
        $vacreqUtil = $this->vacreqUtil;
        $user = $this->getUser();
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

        //$groupParams['sortBy'] = 'list.name';
        $groupParams['sortBy'] = array('institution','name','ASC');

        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);
        //echo "organizationalInstitutions=".count($organizationalInstitutions)."<br>";

        //Since we can have multiple similar names (Anatomic Pathology Full-time, Anatomic Pathology Part-time),
        // we want to display it next to each other - therefore, sort by institution name.
        //Note: we can not sort properly by Role
        //dump($organizationalInstitutions);
        //exit('111');

        //get carryover approver groups
        $carryOverRequestGroups = array();
        if( $this->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
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
     */
    #[Route(path: '/carry-over-request-group/{groupId}', name: 'vacreq_carry_over_request_group_list', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/carry-over-request-group-list.html.twig')]
    public function carryOverRequestGroupAction(Request $request, $groupId)
    {

        if( false == $this->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$onlyWorking = true;
        $onlyWorking = false;

        //find role approvers by institution
        $approvers = array();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $roleApprovers = $em->getRepository(User::class)->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUPERVISOR', $groupId);
        //echo "roleApprovers=".count($roleApprovers)."<br>";
        //exit();

        $roleApprover = null;
        if( count($roleApprovers) > 0 ) {
            $roleApprover = $roleApprovers[0];
        }

        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $approvers = $em->getRepository(User::class)->findUserByRole($roleApprover->getName(),"infos.lastName",$onlyWorking);
        }
        //echo "approvers=".count($approvers)."<br>";

        //find role submitters by institution
//        $submitters = array();
//        $roleSubmitters = $em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUBMITTER', $groupId);
//        $roleSubmitter = $roleSubmitters[0];
//        //echo "roleSubmitter=".$roleSubmitter."<br>";
//        if( $roleSubmitter ) {
//            $submitters = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleSubmitter->getName(),"infos.lastName",$onlyWorking);
//        }s
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $submitters = $em->getRepository(User::class)->findUsersBySitePermissionObjectActionInstitution("vacreq","VacReqRequest","create",$groupId,$onlyWorking);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $organizationalGroupInstitution = $em->getRepository(Institution::class)->find($groupId);

        //vacreq_util
        //$vacreqUtil = $this->container->get('vacreq_util');
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
     */
    #[Route(path: '/organizational-institutions/{institutionId}', name: 'vacreq_orginst_list', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/orginst-list.html.twig')]
    public function organizationalInstitutionAction(Request $request, $institutionId)
    {

        if( false == $this->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->isGranted('ROLE_VACREQ_ADMIN') &&
            false == $this->isGranted('ROLE_VACREQ_PROXYSUBMITTER')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => institutionId=".$institutionId."<br>";
        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->vacreqUtil;

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $organizationalGroupInstitution = $em->getRepository(Institution::class)->find($institutionId);
        //echo "organizationalGroupInstitution=$organizationalGroupInstitution <br>";
        //Create proxyapprover and other new roles for existing org group
        $vacreqUtil->checkAndCreateVacReqRoles($organizationalGroupInstitution, $request);


        $rootInstitution = "N/A";
        //$parentOrganizationalGroupId = null;
        if( $organizationalGroupInstitution ) {
            $rootInstitution = $organizationalGroupInstitution->getRootName($organizationalGroupInstitution);
            if( !$rootInstitution ) {
                $rootInstitution = "Root";
            } else {
                if( $this->isGranted('ROLE_VACREQ_ADMIN') ) {
                    //$rootInstitution = $rootInstitution . ", ID# " . $rootInstitution->getId();
                    $rootInstitution = $rootInstitution->getNameAndId();
                }
            }
        }

        //$onlyWorking = true;
        $onlyWorking = false;

        //find role approvers by institution
        $approvers = array();
        $roleApprovers = $em->getRepository(User::class)->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_APPROVER', $institutionId);
        $roleApprover = $roleApprovers[0];
        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $approvers = $em->getRepository(User::class)->findUserByRole($roleApprover->getName(),"infos.lastName",$onlyWorking);
        }
        //echo "approvers=".count($approvers)."<br>";

        //find role submitters by institution
        $submitters = array();
        $roleSubmitters = $em->getRepository(User::class)->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUBMITTER', $institutionId);
        $roleSubmitter = $roleSubmitters[0];
        //echo "roleSubmitter=".$roleSubmitter."<br>";
        if( $roleSubmitter ) {
            $submitters = $em->getRepository(User::class)->findUserByRole($roleSubmitter->getName(),"infos.lastName",$onlyWorking);
        }
        //echo "submitters=".count($submitters)."<br>";

        //find role proxy submitters by institution
        $roleProxySubmitter = NULL;
        $proxySubmitters = array();
        $roleProxySubmitters = $em->getRepository(User::class)->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_PROXYSUBMITTER', $institutionId);
        if( count($roleProxySubmitters) > 0 ) {
            $roleProxySubmitter = $roleProxySubmitters[0];
        }
        //echo "roleSubmitter=".$roleSubmitter."<br>";
        if( $roleProxySubmitter ) {
            $proxySubmitters = $em->getRepository(User::class)->findUserByRole($roleProxySubmitter->getName(),"infos.lastName",$onlyWorking);
        }
        //echo "proxySubmitters=".count($proxySubmitters)."<br>";

        //$panelClass = "panel-info";
        $panelClass = "panel-success";
        $approvalGroupTypeStr = ""; //"None";
        $approvalGroupType = $vacreqUtil->getVacReqApprovalGroupType($organizationalGroupInstitution);
        if( $approvalGroupType ) {
//            if( $approvalGroupType->getName() != "Faculty" ) {
//                $panelClass = "panel-success";
//            }
            if( str_contains($approvalGroupType->getName(), 'Faculty') ) {
                //$panelClass = "panel-success";
                $panelClass = "panel-info";
            }
            $approvalGroupTypeStr = " (".$approvalGroupType->getName().")";
        }

        $settings = $vacreqUtil->getSettingsByInstitution($institutionId);

        $organizationalGroupInstitutionName = $organizationalGroupInstitution."";
        if( $this->isGranted('ROLE_VACREQ_ADMIN') ) {
            $organizationalGroupInstitutionName = $organizationalGroupInstitution->getNameAndId(); //$organizationalGroupInstitutionName . " ID# " . $organizationalGroupInstitution->getId();
        }


        return array(
            'approvalGroupType' => $approvalGroupTypeStr,
            'panelClass' => $panelClass,
            'approvers' => $approvers,
            'submitters' => $submitters,
            'proxySubmitters' => $proxySubmitters,
            'organizationalGroupId' => $institutionId,
            'organizationalGroupName' => $organizationalGroupInstitutionName, //$organizationalGroupInstitution."",
            'rootInstitution' => $rootInstitution."",
            //'parentOrganizationalGroupId' => $parentOrganizationalGroupId,
            'settings' => $settings,
        );
    }



    /**
     * General management page for a given organizational institution
     */
    #[Route(path: '/manage-group/{institutionId}', name: 'vacreq_orginst_management', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/orginst-management.html.twig')]
    public function orgInstManagementAction(Request $request, $institutionId)
    {

        if(
            false == $this->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => institutionId=".$institutionId."<br>";

        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->container->get('vacreq_util');
        //$onlyWorking = true;
        $onlyWorking = false;

        //find role approvers by institution
        $approvers = array();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $roleApprovers = $em->getRepository(User::class)->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_APPROVER', $institutionId);
        $roleApprover = $roleApprovers[0];
        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $approvers = $em->getRepository(User::class)->findUserByRole($roleApprover->getName(),"infos.lastName",$onlyWorking);
        }
        //echo "approvers=".count($approvers)."<br>";

        //$vacreqUtil = $this->container->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $this->vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $institutionId) == false ) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //find role submitters by institution
        $submitters = array();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $roleSubmitters = $em->getRepository(User::class)->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_SUBMITTER', $institutionId);
        $roleSubmitter = $roleSubmitters[0];
        //echo "roleSubmitter=".$roleSubmitter."<br>";
        if( $roleSubmitter ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $submitters = $em->getRepository(User::class)->findUserByRole($roleSubmitter->getName(),"infos.lastName",$onlyWorking);
        }

        //find role proxy submitters by institution
        $proxySubmitters = array();
        $roleProxySubmitter = NULL;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $roleProxySubmitters = $em->getRepository(User::class)->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_PROXYSUBMITTER', $institutionId);
        if( count($roleProxySubmitters) > 0 ) {
            $roleProxySubmitter = $roleProxySubmitters[0];
        }
        //echo "roleProxySubmitter=".$roleProxySubmitter."<br>";
        if( $roleProxySubmitter ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $proxySubmitters = $em->getRepository(User::class)->findUserByRole($roleProxySubmitter->getName(),"infos.lastName",$onlyWorking);
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $organizationalGroupInstitution = $em->getRepository(Institution::class)->find($institutionId);

        //get approval group type (Faculty, Fellows)
        $approvalGroupType = $vacreqUtil->getVacReqApprovalGroupType($organizationalGroupInstitution);

        $roleApproverId = null;
        if( $roleApprover ) {
            //echo "roleApprover=".$roleApprover."<br>";
            $roleApproverId = $roleApprover->getId();
        }

        $roleSubmitterId = null;
        if( $roleSubmitter ) {
            $roleSubmitterId = $roleSubmitter->getId();
        }

        $roleProxySubmitterId = null;
        if( $roleProxySubmitter ) {
            $roleProxySubmitterId = $roleProxySubmitter->getId();
        }

        //echo "approverRoleId=".$roleApproverId."<br>";

        return array(
            'approvers' => $approvers,
            'approverRoleId' => $roleApproverId,
            'submitters' => $submitters,
            'proxySubmitters' => $proxySubmitters,
            'submitterRoleId' => $roleSubmitterId,
            'proxySubmitterRoleId' => $roleProxySubmitterId,
            'organizationalGroupId' => $institutionId,
            'organizationalGroupName' => $organizationalGroupInstitution."",
            'approvalGroupType' => $approvalGroupType
        );
    }



    /**
     * A particular management page for a given organizational institution and user to show a well to update the role or remove a user
     */
    #[Route(path: '/organizational-institution-user-management/{userid}/{instid}/{roleId}', name: 'vacreq_orginst_user_management', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/orginst-user-management.html.twig')]
    public function userManagementAction(Request $request, $userid, $instid, $roleId )
    {

        if(
            false == $this->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$userid."<br>";

        $em = $this->getDoctrine()->getManager();

        //check if logged in user has approver role for $instid
        //$vacreqUtil = $this->container->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $this->vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $subjectUser = $em->getRepository(User::class)->find($userid);

        if( !$subjectUser ) {
            throw $this->createNotFoundException('Unable to find Vacation Request user by id='.$userid);
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $organizationalGroupInstitution = $em->getRepository(Institution::class)->find($instid);

        if( !$organizationalGroupInstitution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //Original Roles not associated with this site
        //$securityUtil = $this->container->get('user_security_utility');
        //$originalOtherRoles = $securityUtil->getUserRolesBySite( $subjectUser, 'vacreq', false );

        //Roles
        //$securityUtil = $this->container->get('user_security_utility');
        //$rolesArr = $securityUtil->getSiteRolesKeyValue('vacreq');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $roles = $em->getRepository(Roles::class)->findById($roleId);
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
     */
    #[Route(path: '/organizational-institution-user-update/{userid}/{instid}/{roleIds}', name: 'vacreq_orginst_user_update', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[Template('AppVacReqBundle/Approver/orginst-user-management.html.twig')]
    public function userManagementUpdateAction(Request $request, $userid, $instid, $roleIds )
    {
        exit("We don't need to update the roles from the Group Management page. We need only add or remove user.");

        if(
            false == $this->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //check if logged in user has approver role for $instid
        //$vacreqUtil = $this->container->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $this->vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $subjectUser = $em->getRepository(User::class)->find($userid);
        if( !$subjectUser ) {
            throw $this->createNotFoundException('Unable to find Vacation Request user by id='.$userid);
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $organizationalGroupInstitution = $em->getRepository(Institution::class)->find($instid);
        if( !$organizationalGroupInstitution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //echo "roleIds=".$roleIds."<br>";

        if( $roleIds || $roleIds == '0' ) {
            $roleArr = explode(',',$roleIds);
        } else {
            $roleArr = array();
        }

        $securityUtil = $this->container->get('user_security_utility');
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
            $this->addFlash(
                'notice',
                $event
            );

        }

        //return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
        exit('ok');
    }


    #[Route(path: '/organizational-institution-user-remove/{userid}/{instid}/{roleId}', name: 'vacreq_orginst_user_remove', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/orginst-user-management.html.twig')]
    public function removeUserAction(Request $request, $userid, $instid, $roleId )
    {

        if(
            false == $this->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //check if logged in user has approver role for $instid
        //$vacreqUtil = $this->container->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $this->vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $subjectUser = $em->getRepository(User::class)->find($userid);
        if( !$subjectUser ) {
            throw $this->createNotFoundException('Unable to find Vacation Request user by id='.$userid);
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $organizationalGroupInstitution = $em->getRepository(Institution::class)->find($instid);
        if( !$organizationalGroupInstitution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //get role by roletype
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $role = $em->getRepository(Roles::class)->find( $roleId );
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
            $this->addFlash(
                'notice',
                $event
            );

        }

        return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
    }

    #[Route(path: '/organizational-institution-user-add/{instid}/{roleId}/{btnName}', name: 'vacreq_orginst_add_user', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/orginst-user-add.html.twig')]
    public function addUserAction(Request $request, $instid, $roleId, $btnName )
    {

        if(
            false == $this->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //check if logged in user has approver role for $instid
        //$vacreqUtil = $this->container->get('vacreq_util');
        $partialRoleNames = array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR');
        if( $this->vacreqUtil->hasRoleNameAndGroup($partialRoleNames, $instid) == false ) {
            exit('no permission');
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $role = $em->getRepository(Roles::class)->find($roleId);

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

    #[Route(path: '/organizational-institution-user-add-action/{instid}/{roleId}', name: 'vacreq_orginst_add_action_user', methods: ['GET', 'POST'])]
    public function addRoleToUserAction(Request $request, $instid, $roleId )
    {

        if(
            false == $this->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo "instid=".$instid."<br>";
        //echo "roleId=".$roleId."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $role = $em->getRepository(Roles::class)->find($roleId);
        if( !$role ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Role by id='.$roleId);
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }


        $params = array(
            'cycle' => 'create',
            'readonly' => false,
            'btnName' => "User"
        );
        $form = $this->createForm(VacReqUserComboboxType::class,null,array('form_custom_value'=>$params));

        $form->handleRequest($request);

        //$users = $form['users']->getData();
        $users = $form->get('users')->getData();

        //$usersArr = array();

        foreach( $users as $thisUser ) {
            //echo "Add thisUser=".$thisUser.", role=".$role."<br>";
            $thisUser->addRole($role);
            $em->persist($thisUser);
            //$usersArr = $thisUser;
        }
        //exit('111');

        //$users = $request->query->get('users');
        //$users = trim((string)$users);

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
        $this->addFlash(
            'notice',
            implode(" ",$globalEventArr)
        );

        return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
    }



    #[Route(path: '/add-group', name: 'vacreq_group_add', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/orginst-add.html.twig')]
    public function addGroupAction(Request $request )
    {

        if( false == $this->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SiteList'] by [SiteList::class]
            $site = $em->getRepository(SiteList::class)->findOneByAbbreviation('vacreq');

            //add group
            //$instid = null;
            $institution = $form["institution"]->getData();
            $approvalType = $form["approvaltype"]->getData();

            if( !$institution || !$approvalType ) {
                //Flash
                $this->addFlash(
                    'warning',
                    "Please provide institution and approval group type"
                );
                return $this->redirectToRoute('vacreq_group_add');
            }

            $instid = $institution->getId();
            //exit('instid='.$instid);

            //TODO: add approval type to the vacreq institution group
            //$approvalType->addInstitution($institution);
            //TODO: add approval group to VacReqSettings

            $count = 0;

            //get ROLE NAME: Pathology Informatics => PATHOLOGYINFORMATCS
            $roleNameBase = str_replace(" ","",$institution->getName());
            $roleNameBase = strtoupper($roleNameBase);

            //create approver role
            $roleName = "ROLE_VACREQ_APPROVER_".$roleNameBase;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
            $approverRole = $em->getRepository(Roles::class)->findOneByName($roleName);
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
                //$em->flush($approverRole);
                $em->flush();

                $count++;
            } else {
                $approverType = $approverRole->getType();
                if( $approverType != 'default' && $approverType != 'user-added' ) {
                    $approverRole->setType('default');
                    $em->persist($approverRole);
                    //$em->flush($approverRole);
                    $em->flush();
                    $count++;
                }
            }

            //create submitter role
            $roleName = "ROLE_VACREQ_SUBMITTER_".$roleNameBase;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
            $submitterRole = $em->getRepository(Roles::class)->findOneByName($roleName);
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
                //$em->flush($submitterRole);
                $em->flush();

                $count++;
            } else {
                $submitterType = $submitterRole->getType();
                if( $submitterType != 'default' && $submitterType != 'user-added' ) {
                    $submitterRole->setType('default');
                    $em->persist($submitterRole);
                    //$em->flush($submitterRole);
                    $em->flush();
                    $count++;
                }
            }

            //create submitter role
            $roleName = "ROLE_VACREQ_PROXYSUBMITTER_".$roleNameBase;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
            $submitterRole = $em->getRepository(Roles::class)->findOneByName($roleName);
            if( !$submitterRole ) {
                $submitterRole = new Roles();
                $submitterRole = $userSecUtil->setDefaultList($submitterRole, null, $user, $roleName);
                $submitterRole->setLevel(35);
                $submitterRole->setAlias('Vacation Request Proxy Submitter for the ' . $institution->getName());
                $submitterRole->setDescription('Can search and create vacation requests for specified service on behalf of another person');
                $submitterRole->addSite($site);
                $submitterRole->setInstitution($institution);
                $userSecUtil->checkAndAddPermissionToRole($submitterRole, "Submit a Vacation Request", "VacReqRequest", "create");

                $em->persist($submitterRole);
                //$em->flush($submitterRole);
                $em->flush();

                $count++;
            } else {
                $submitterType = $submitterRole->getType();
                if( $submitterType != 'default' && $submitterType != 'user-added' ) {
                    $submitterRole->setType('default');
                    $em->persist($submitterRole);
                    //$em->flush($submitterRole);
                    $em->flush();
                    $count++;
                }
            }

            if( $count > 0 ) {
                //Event Log
                //$event = "New Business/Vacation Group " . $roleNameBase . " has been created for " . $institution->getName();
                //New Business/Vacation Group TEST has been created.
                $event = "New Business/Vacation Group ".$institution->getName()." has been created.";
                $userSecUtil = $this->container->get('user_security_utility');
                $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $event, $user, $institution, $request, 'Business/Vacation Group Created');

                //Flash
                $this->addFlash(
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
     */
    #[Route(path: '/organizational-institution-remove/{instid}', name: 'vacreq_group_remove', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/orginst-user-add.html.twig')]
    public function removeGroupAction(Request $request, $instid )
    {

        if( false == $this->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //exit('not implemented');

        $removedRoles = array();

        $removedRoles[] = $this->removeVacReqGroupByInstitution($instid,"ROLE_VACREQ_APPROVER_",$request);
        $removedRoles[] = $this->removeVacReqGroupByInstitution($instid,"ROLE_VACREQ_SUBMITTER_",$request);
        $removedRoles[] = $this->removeVacReqGroupByInstitution($instid,"ROLE_VACREQ_PROXYSUBMITTER_",$request);

        if( count($removedRoles) > 0 ) {
            //Event Log
            $event = "Business/Vacation Group [" . $institution->getTreeName() . "] has been removed by removing roles: ".implode(", ",$removedRoles);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $event, $user, $institution, $request, 'Business/Vacation Role Removed');

            //Flash
            $this->addFlash(
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

        //$onlyWorking = true;
        $onlyWorking = false;
        
        $roleName = null;
        $userNamesArr = array();

        //1) find approver roles with institution
        $role = null;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $roles = $em->getRepository(User::class)->findRolesBySiteAndPartialRoleName("vacreq",$rolePartialName,$instid);
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

        //1b) set ROLE_VACREQ_PROXYSUBMITTER_ role status disabled
        if( $rolePartialName == "ROLE_VACREQ_PROXYSUBMITTER_" ) {
            foreach ($roles as $thisRole) {
                $thisRole->setType('disabled');
            }
        }

        //2) remove approver role from all users
        if( $role ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $users = $em->getRepository(User::class)->findUserByRole($roleName,"infos.lastName",$onlyWorking);
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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $institution = $em->getRepository(Institution::class)->find($instid);
            $institutionTreeName = null;
            if( $institution ) {
                $institutionTreeName = $institution->getTreeName();
            }
            $eventType = "Business/Vacation Group Updated";
            $event = $institutionTreeName.": The role " . $roleName . " has been removed from the users: " . implode(", ", $userNamesArr);
            $userSecUtil = $this->container->get('user_security_utility');
            $user = $this->getUser();
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
    #[Route(path: '/organizational-institution-emailusers/{instid}', name: 'vacreq_orginst_emailusers', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/orginst-emailusers.html.twig')]
    public function emailUsersAction(Request $request, $instid)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->vacreqUtil->getSettingsByInstitution($instid);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        if( !$entity ) {
            $entity = new VacReqSettings($institution);
        }

        $params = array(
            'userFieldName' => 'emailUsers',
            'userLabel' => "E-Mail all requests and responses to:",
            'userClass' => 'vacreq-emailusers'
        );

        $users = $entity->getEmailUsersStr();
        //echo "emailUsers=".$users."<br>";

        $form = $this->createForm(
            VacReqGroupManageEmailusersType::class,
            $entity,
            array(
                'form_custom_value' => $params,
                'method' => "POST",
                //'action' => $action
            )
        );

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'organizationalGroupName' => $institution."",
            'organizationalGroupId' => $instid,
        );
    }
    #[Route(path: '/organizational-institution-emailusers-update/{instid}/{users}', name: 'vacreq_orginst_emailusers_update', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function emailUsersUpdateAction(Request $request, $instid, $users)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

//        $keytype = $request->query->get('keytype');
//        $keytype = trim((string)$keytype);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //vacreq_util
        //$vacreqUtil = $this->container->get('vacreq_util');
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
            $userSecUtil->createUserEditEvent(
                $this->getParameter('vacreq.sitename'),
                $event,
                $user,
                $institution,
                $request,
                'Business/Vacation Group Updated'
            );

            //Flash
            $this->addFlash(
                'notice',
                $event
            );

        }

//        }//foreach


        return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));

    }


    #[Route(path: '/organizational-institution-approvaltypes/{instid}', name: 'vacreq_orginst_approvaltypes', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/orginst-approvaltypes.html.twig')]
    public function approvalTypesAction(Request $request, $instid)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->vacreqUtil->getSettingsByInstitution($instid);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        if( !$entity ) {
            $entity = new VacReqSettings($institution);
        }

        $approvalGroupType = $entity->getApprovalType();
        //echo "approvalGroupType=$approvalGroupType <br>";

        $approvalGroupTypeId = NULL;
        $approvalGroupTypeName = NULL;
        if( $approvalGroupType ) {
            $approvalGroupTypeId = $approvalGroupType->getId();
            $approvalGroupTypeName = $approvalGroupType->getName();
        }

        $params = array(
            'approvalGroupType' => $approvalGroupType
        );

        $form = $this->createForm(
            VacReqGroupManageApprovaltypesType::class,
            $entity,
            array(
                'form_custom_value' => $params,
                'method' => "POST",
                //'action' => $action
            )
        );

        return array(
            'approvalGroupTypeName' => $approvalGroupTypeName,
            'approvalGroupType' => $approvalGroupTypeId,
            'entity' => $entity,
            'form' => $form->createView(),
            'organizationalGroupName' => $institution."",
            'organizationalGroupId' => $instid,
        );
    }
    #[Route(path: '/organizational-institution-approvaltypes-update/{instid}/{approvaltypeid}', name: 'vacreq_orginst_approvaltypes_update', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function approvalTypesUpdateAction(Request $request, $instid, $approvaltypeid)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $response = new Response();

        if( !$instid ) {
            //Flash
            $this->addFlash(
                'warning',
                'Approval group type is not updated: institution is not provided'
            );
            $response->setContent("not updated");
            return $response;
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        $entity = $this->vacreqUtil->getSettingsByInstitution($instid);

        if( !$entity ) {
            $entity = new VacReqSettings($institution);
        }

        $res = $this->vacreqUtil->settingsAddRemoveApprovalTypes($entity,$approvaltypeid);

        if( $res ) {

            $originalApprovalType = $res['originalApprovalType'];
            $newApprovalType = $res['newApprovalType'];

            $em->persist($entity);
            $em->flush();

            //Event Log
            $event = "Approval group type has been updated for " . $institution .
                "; Original type=".$originalApprovalType.
                "; New type=".$newApprovalType;
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent(
                $this->getParameter('vacreq.sitename'),
                $event,
                $user,
                $institution,
                $request,
                'Time Away Approval Group Type Updated'
            );

            //Flash
            $this->addFlash(
                'notice',
                $event
            );

            $response->setContent("success");
            return $response;
        }

        //Flash
        $this->addFlash(
            'warning',
            'Approval group type is not updated'
        );

        $response->setContent("not updated");
        return $response;
    }

    #[Route(path: '/organizational-institution-defaultinformusers/{instid}', name: 'vacreq_orginst_defaultinformusers', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/orginst-defaultinformusers.html.twig')]
    public function defaultInformUsersAction(Request $request, $instid)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->vacreqUtil->getSettingsByInstitution($instid);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        if( !$entity ) {
            $entity = new VacReqSettings($institution);
        }

        //exit('TODO defaultinformusers');
        $users = $entity->getDefaultInformUsersStr();
        //echo "defaultInformUsers=".$users."<br>";

        $params = array(
            'userFieldName' => 'defaultInformUsers',
            'userLabel' => "Send a notification to the following individuals:",
            'userClass' => 'vacreq-defaultinformusers'
        );

        $form = $this->createForm(
            VacReqGroupManageEmailusersType::class,
            $entity,
            array(
                'form_custom_value' => $params,
                'method' => "POST",
                //'action' => $action
            )
        );

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'organizationalGroupName' => $institution."",
            'organizationalGroupId' => $instid,
        );
    }
    #[Route(path: '/organizational-institution-defaultinformusers-update/{instid}/{users}', name: 'vacreq_orginst_defaultinformusers_update', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function defaultInformUsersUpdateAction(Request $request, $instid, $users)
    {
        //exit('TODO defaultinformusers');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        $entity = $this->vacreqUtil->getSettingsByInstitution($instid);

        if( !$entity ) {
            $entity = new VacReqSettings($institution);
        }

        $res = $this->vacreqUtil->settingsAddRemoveDefaultInformUsers($entity,$users);

        if( $res ) {

            $originalUsers = $res['originalUsers'];
            $newUsers = $res['newUsers'];

            $em->persist($entity);
            $em->flush();

            //Event Log
            $event = "Default inform users has been updated for Business/Vacation Group " . $institution .
                "; Original users=".implode(", ",$originalUsers).
                "; New users=".implode(", ",$newUsers);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent(
                $this->getParameter('vacreq.sitename'),
                $event,
                $user,
                $institution,
                $request,
                'Business/Vacation Group Updated' //replace 'Business/Vacation' by 'Time Away'
            );

            //Flash
            $this->addFlash(
                'notice',
                $event
            );

        }

        return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
    }
    #[Route(path: '/organizational-institution-defaultinformusers-ajax/{instid}', name: 'vacreq_orginst_defaultinformusers_ajax', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function defaultInformUsersAjaxAction(Request $request, $instid)
    {
        $response = new Response();
        //$response->headers->set('Content-Type', 'application/json');
        $defaultInformUsers = $this->vacreqUtil->showDefaultInformUsers($instid,true);
        $response->setContent($defaultInformUsers);
        return $response;
    }
    
    #[Route(path: '/organizational-institution-personaway-ajax/{instid}', name: 'vacreq_orginst_personaway_ajax', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function personAwayAjaxAction(Request $request, $instid)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        //$response->headers->set('Content-Type', 'application/json');
        //$submitters = $this->vacreqUtil->getSubmittersFromSubmittedRequestsByGroup($instid);
        $submittersSelect = $this->vacreqUtil->getUsersByGroupSelect($instid,"ROLE_VACREQ_SUBMITTER");
        if( count($submittersSelect) == 0 ) {
            //person away field can not be empty = > if zero => get default users
            $submittersSelect = $this->vacreqUtil->getUsersByGroupSelect(0,"ROLE_VACREQ_SUBMITTER");
        }
        $response->setContent(json_encode($submittersSelect));
        return $response;
    }


    #[Route(path: '/organizational-institution-proxysubmitterusers/{instid}', name: 'vacreq_orginst_proxysubmitterusers', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/orginst-proxysubmitterusers.html.twig')]
    public function proxySubmitterUsersAction(Request $request, $instid)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->vacreqUtil->getSettingsByInstitution($instid);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        if( !$entity ) {
            $entity = new VacReqSettings($institution);
        }

        //exit('TODO proxysubmitterusers');
        $users = $entity->getProxySubmitterUsersStr();
        //echo "proxySubmitterUsers=".$users."<br>";

        $params = array(
            'userFieldName' => 'proxySubmitterUsers',
            'userLabel' => "Proxy submitters:",
            'userClass' => 'vacreq-proxysubmitterusers'
        );

        $form = $this->createForm(
            VacReqGroupManageEmailusersType::class,
            $entity,
            array(
                'form_custom_value' => $params,
                'method' => "POST",
                //'action' => $action
            )
        );

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'organizationalGroupName' => $institution."",
            'organizationalGroupId' => $instid,
        );
    }
    #[Route(path: '/organizational-institution-proxysubmitterusers-update/{instid}/{users}', name: 'vacreq_orginst_proxysubmitterusers_update', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function proxySubmitterUsersUpdateAction(Request $request, $instid, $users)
    {
        //exit('TODO proxysubmitterusers');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        $entity = $this->vacreqUtil->getSettingsByInstitution($instid);

        if( !$entity ) {
            $entity = new VacReqSettings($institution);
        }

        $res = $this->vacreqUtil->settingsAddRemoveProxySubmitterUsers($entity,$users);

        if( $res ) {

            $originalUsers = $res['originalUsers'];
            $newUsers = $res['newUsers'];

            $em->persist($entity);
            $em->flush();

            //Event Log
            $event = "Proxy submitter users has been updated for Business/Vacation Group " . $institution .
                "; Original users=".implode(", ",$originalUsers).
                "; New users=".implode(", ",$newUsers);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent(
                $this->getParameter('vacreq.sitename'),
                $event,
                $user,
                $institution,
                $request,
                'Business/Vacation Group Updated' //replace 'Business/Vacation' by 'Time Away'
            );

            //Flash
            $this->addFlash(
                'notice',
                $event
            );

        }

        return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
    }


    /**
     * OLD TO DELETE
     */
    #[Route(path: '/organizational-institution-approval-group-type/{instid}', name: 'vacreq_orginst_approval_group_type', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Approver/approval-group-type.html.twig')]
    public function approvalGroupTypeAction(Request $request, $instid) {
        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->container->get('vacreq_util');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //exit("institution=$institution");

        $approvalGroupType = $vacreqUtil->getVacReqApprovalGroupType($institution);
        //echo "approvalGroupType=$approvalGroupType <br>";

        $approvalGroupTypeId = NULL;
        $approvalGroupTypeName = NULL;
        if( $approvalGroupType ) {
            $approvalGroupTypeId = $approvalGroupType->getId();
            $approvalGroupTypeName = $approvalGroupType->getName();
        }

        $params = array(
            'approvalGroupType' => $approvalGroupType
        );

        $form = $this->createForm(
            VacReqApprovalGroupType::class,
            null,
            array(
                'form_custom_value' => $params,
                'method' => "POST",
            )
        );

        return array(
            'approvalGroupTypeName' => $approvalGroupTypeName,
            'approvalGroupType' => $approvalGroupTypeId,
            'form' => $form->createView(),
            'organizationalGroupName' => $institution."",
            'organizationalGroupId' => $instid,
        );
    }
    /**
     * OLD TO DELETE
     */
    #[Route(path: '/organizational-institution-approval-group-type-update/{instid}/{approvalgrouptypeid}', name: 'vacreq_orginst_approval_group_type_update', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function approvalGroupTypeUpdateAction(Request $request, $instid, $approvalgrouptypeid)
    {
        $response = new Response();
        //$response->headers->set('Content-Type', 'application/json');

        if( !$instid || !$approvalgrouptypeid ) {
            //Flash
            $this->addFlash(
                'notice',
                "Please provide institution and approval group type"
            );
            $response->setContent("Please provide institution and approval group type");
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        //vacreq_util
        $vacreqUtil = $this->container->get('vacreq_util');

        if( $institution ) {

            $originalApprovalGroupType = $vacreqUtil->getVacReqApprovalGroupType($institution);
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqApprovalTypeList'] by [VacReqApprovalTypeList::class]
            $approvalGroupType = $em->getRepository(VacReqApprovalTypeList::class)->find($approvalgrouptypeid);

            $typeUpdated = false;
            $originalApprovalGroupTypeId = NULL;
            $approvalGroupTypeId = NULL;
            if( $originalApprovalGroupType ) {
                $originalApprovalGroupTypeId = $originalApprovalGroupType->getId();
            }
            if( $approvalGroupType ) {
                $approvalGroupTypeId = $approvalGroupType->getId();
            }

            if( $approvalGroupType ) {
                //$approvalGroupType->clearInstitutions();
                if( $originalApprovalGroupType ) {
                    $originalApprovalGroupType->removeInstitution($institution);
                }
                $approvalGroupType->addInstitution($institution);
                $typeUpdated = true;
            } else {
                //throw $this->createNotFoundException('Unable to find VacReqApprovalTypeList by id='.$approvalgrouptypeid);
                //Do not remove existing approval group type if new one is not set
                //if( $originalApprovalGroupType ) {
                //    $originalApprovalGroupType->removeInstitution($institution);
                //}
            }

            //exit("institution=$institution, approvalgrouptype=$approvalGroupType");

            if( $typeUpdated === false || $originalApprovalGroupTypeId == $approvalGroupTypeId ) {
                $event = "Approval group type has not been updated for " . $institution .
                    "; Current approval group type is ".$approvalGroupType;
                //Event Log
                $userSecUtil = $this->container->get('user_security_utility');
                $userSecUtil->createUserEditEvent(
                    $this->getParameter('vacreq.sitename'),
                    $event,
                    $user,
                    $institution,
                    $request,
                    'Business/Vacation Approval Group Type Updated'
                );
            } else {
                $em->flush();
                $event = "Approval group type has been updated for " . $institution .
                    "; Original approval group type is ".$originalApprovalGroupType.
                    "; New approval group type is ".$approvalGroupType; //." (ID ".$approvalgrouptypeid.")";
            }

            //Flash
            $this->addFlash(
                'notice',
                $event
            );

            $response->setContent("success");
            return $response;
        }

        $response->setContent("not updated");
        return $response;
        //return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
    }



    //My Group vacreq_mygroup
    /**
     * Get groups and populate them in mySingleGroupAction
     * TODO: don't show all users by default. Show it empty if filter is not set.
     */
    #[Route(path: '/my-group/', name: 'vacreq_my_group', methods: ['GET', 'POST'])]
    #[Route(path: '/summary/', name: 'vacreq_summary', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Group/mygroup.html.twig')]
    public function myGroupAction(Request $request)
    {

        if( false == $this->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->isGranted('ROLE_VACREQ_PROXYSUBMITTER') &&
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $routename = $request->get('_route');
        if( $routename == 'vacreq_my_group' ) {
            $vacreqUtil = $this->container->get('vacreq_util');
            $groupTypes = $vacreqUtil->getApprovalGroupTypes();
            $arr = array();
            foreach($groupTypes as $groupType) {
                $arr['filter[types]['.$groupType->getId().']'] = $groupType->getId();
            }

            return $this->redirectToRoute(
                'vacreq_summary',
                $arr
            );
        }

        set_time_limit(600);
        ini_set('memory_limit', '2048M');

        //$em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //vacreq_util
        //$vacreqUtil = $this->container->get('vacreq_util');

        $userids = null;
        //$approvaltypes = null;
        $filteredGroups = array();

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
        //$accruedDays = $this->vacreqUtil->getAccruedDaysUpToThisMonth();

        $yearRange = $this->vacreqUtil->getCurrentAcademicYearRange();

        /////////////// filter form ///////////////////
        $params = array(

        );

        $filterform = $this->createForm(VacReqSummaryFilterType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));
        
        $filterform->handleRequest($request);

        if( $filterform->isSubmitted() && $filterform->isValid() ) {
            $users = $filterform["users"]->getData();
            $useridsArr = array();
            foreach( $users as $thisUser ) {
                $useridsArr[] = $thisUser->getId();
            }
            $userids = implode("-",$useridsArr);

            $filterapprovaltypes = $filterform["types"]->getData();
            //dump($filterapprovaltypes);
            //dump($users);
            //exit('111');
            foreach( $groups as $group ) {
                $thisApprovalGroupType = $this->vacreqUtil->getApprovalGroupTypeByInstitution($group->getId());
                if( $thisApprovalGroupType ) {
                    foreach( $filterapprovaltypes as $filterapprovaltype ) {
                        if ($thisApprovalGroupType->getId() == $filterapprovaltype->getId()) {
                            $filteredGroups[] = $group;
                        }
                    }
                } else {
                    if( count($filterapprovaltypes) == 0 ) {
                        $filteredGroups[] = $group;
                    }
                }
            }
        }
        /////////////// EOF: filter form ///////////////////
        //dump($users);
        //echo "group length=".count($filteredGroups)."<br>";
        //exit('111');

        $showall = true;
        if( count($filteredGroups) > 3 ) {
            $showall = false;
        }

//        if( $showall === false && !$userids ) {
//            $this->addFlash(
//                'pnotify',
//                'Please select employee(s) to see the summary'
//            );
//        }

        return array(
            //'groups' => $groups,
            'groups' => $filteredGroups,
            //'accruedDays' => $accruedDays,
            'yearRange' => $yearRange,
            //'entity' => $entity,
            'filterform' => $filterform->createView(),
            'userids' => $userids,
            'showall' => $showall
            //'approvaltypes' => $approvaltypes
            //'organizationalGroupName' => $institution."",
            //'organizationalGroupId' => $instid,
        );
    }

    #[Route(path: '/my-single-group/{groupId}/{showall}/{userids}/{approvaltypes}', name: 'vacreq_mysinglegroup', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Group/my-single-group.html.twig')]
    public function mySingleGroupAction( Request $request, $groupId, $showall, $userids )
    {

        if (false == $this->isGranted('ROLE_VACREQ_SUPERVISOR') &&
            false == $this->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->isGranted('ROLE_VACREQ_PROXYSUBMITTER') &&
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //echo "groupId=".$groupId."<br>";
        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->container->get('vacreq_util');

        if( $showall || $userids ) {
            //find role submitters by institution
            //$submitters = $vacreqUtil->getSubmittersFromSubmittedRequestsByGroup($groupId);
            $submitters = $this->vacreqUtil->getUsersByGroupId($groupId, "ROLE_VACREQ_SUBMITTER");
        } else {
            $submitters = array(); //don't show all users by default
        }

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

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $group = $em->getRepository(Institution::class)->find($groupId);

        //$approvalGroupType
//        $approvalGroupTypeStr = $vacreqUtil->getVacReqApprovalGroupType($group,true);
//        if( $approvalGroupTypeStr ) {
//            $approvalGroupTypeStr = " (" . $approvalGroupTypeStr . ")";
//        }
        //$totalAllocatedDays = NULL;
        //$panelClass = "panel-info";
        $panelClass = "panel-success";
        $approvalGroupTypeStr = ""; //"None";
        $approvalGroupType = $vacreqUtil->getVacReqApprovalGroupType($group);
        if( $approvalGroupType ) {
            //if( $approvalGroupType->getName() != "Faculty" ) {
            //    $panelClass = "panel-success";
            //}
            if( str_contains($approvalGroupType->getName(), 'Faculty') ) {
                //$panelClass = "panel-success";
                $panelClass = "panel-info";
            }
            $approvalGroupTypeStr = " (".$approvalGroupType->getName().")";
            //$totalAllocatedDays = $vacreqUtil->getTotalAccruedDaysByGroup($approvalGroupType);
            //echo "mySingleGroupAction totalAllocatedDays=$totalAllocatedDays <br>";
        }

        //get accrued days by institution
        $accruedDays = NULL;
        if( count($submitters) > 0 ) {
            $accruedDays = $vacreqUtil->getAccruedDaysUpToThisMonthByInstitution($groupId);
        }
        
        $yearRanges = array();
        //Current Academic Year
        $yearRanges[] = $this->vacreqUtil->getCurrentAcademicYearRange();

        //Current Academic Year - 1
        $yearRanges[] = $this->vacreqUtil->getPreviousAcademicYearRange();

        //Current Academic Year - 2
        $yearRanges[] = $this->vacreqUtil->getPreviousAcademicYearRange(1);

        $yearRangesColor = array('#c1e2b3','#d0e9c6','#dff0d8');

        return array(
            'groupId' => $groupId,
            'submitters' => $submitters, //person away
            'groupName' => $group."",
            'accruedDays' => $accruedDays,
            'approvalGroupTypeStr' => $approvalGroupTypeStr,
            'panelClass' => $panelClass,
            'yearRanges' => $yearRanges,
            'yearRangesColor' => $yearRangesColor,
            //'totalAllocatedDays' => $this->vacreqUtil->getTotalAccruedDays_OLD(),
            //'totalAllocatedDays' => $totalAllocatedDays, //$this->vacreqUtil->getTotalAccruedDays(),
            'trFontSize' => "10px",
            'fontWeight' => "normal"
        );
    }

    #[Route(path: '/generate-default-group', name: 'vacreq_generate_default_group', methods: ['GET'])]
    public function generateDefaultGroupAction(Request $request )
    {

        if( false == $this->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //echo " => userId=".$id."<br>";

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //get submitter groups
        $vacreqUtil = $this->vacreqUtil;
        $groupParams = array('asObject'=>true);
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
        $groupParams['exceptPermissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
        $groupParams['statusArr'] = array('default','user-added');
        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);
        if( count($organizationalInstitutions) > 0 ) {
            $this->addFlash(
                'warning',
                "Business/Vacation group is not empty."
            );
            return $this->redirect($this->generateUrl('employees_siteparameters'));
        }

        $defaultInstName = "Pathology and Laboratory Medicine";
        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution',
            'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
            'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
        );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $pathology = $em->getRepository(Institution::class)->findByChildnameAndParent(
            $defaultInstName,
            $wcmc,
            $mapper
        );
//        $defaultInstName = "Pathology Informatics";
//        $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
//            $defaultInstName,
//            $pathology,
//            $mapper
//        );

        if( !$institution ) {
            $msg = "Default group '$defaultInstName' not found";
            //Flash
            $this->addFlash(
                'warning',
                $msg
            );
            return $this->redirect($this->generateUrl('employees_siteparameters'));
        }


        $userSecUtil = $this->container->get('user_security_utility');
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SiteList'] by [SiteList::class]
        $site = $em->getRepository(SiteList::class)->findOneByAbbreviation('vacreq');

        $count = 0;

        //get ROLE NAME: Pathology Informatics => PATHOLOGYINFORMATCS
        $roleNameBase = str_replace(" ","",$institution->getName());
        $roleNameBase = strtoupper($roleNameBase);

        //create approver role
        $roleName = "ROLE_VACREQ_APPROVER_".$roleNameBase;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $approverRole = $em->getRepository(Roles::class)->findOneByName($roleName);
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
            //$em->flush($approverRole);
            $em->flush();

            $count++;
        } else {
            $approverType = $approverRole->getType();
            if( $approverType != 'default' && $approverType != 'user-added' ) {
                $approverRole->setType('default');
                $em->persist($approverRole);
                //$em->flush($approverRole);
                $em->flush();
                $count++;
            }
        }

        //create submitter role
        $roleName = "ROLE_VACREQ_SUBMITTER_".$roleNameBase;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $submitterRole = $em->getRepository(Roles::class)->findOneByName($roleName);
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
            //$em->flush($submitterRole);
            $em->flush();

            $count++;
        } else {
            $submitterType = $submitterRole->getType();
            if( $submitterType != 'default' && $submitterType != 'user-added' ) {
                $submitterRole->setType('default');
                $em->persist($submitterRole);
                //$em->flush($submitterRole);
                $em->flush();
                $count++;
            }
        }

        //create submitter role
        $roleName = "ROLE_VACREQ_PROXYSUBMITTER_".$roleNameBase;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $submitterRole = $em->getRepository(Roles::class)->findOneByName($roleName);
        if( !$submitterRole ) {
            $submitterRole = new Roles();
            $submitterRole = $userSecUtil->setDefaultList($submitterRole, null, $user, $roleName);
            $submitterRole->setLevel(35);
            $submitterRole->setAlias('Vacation Request Proxy Submitter for the ' . $institution->getName());
            $submitterRole->setDescription('Can search and create vacation requests for specified service on behalf of another person');
            $submitterRole->addSite($site);
            $submitterRole->setInstitution($institution);
            $userSecUtil->checkAndAddPermissionToRole($submitterRole, "Submit a Vacation Request", "VacReqRequest", "create");

            $em->persist($submitterRole);
            //$em->flush($submitterRole);
            $em->flush();

            $count++;
        } else {
            $submitterType = $submitterRole->getType();
            if( $submitterType != 'default' && $submitterType != 'user-added' ) {
                $submitterRole->setType('default');
                $em->persist($submitterRole);
                //$em->flush($submitterRole);
                $em->flush();
                $count++;
            }
        }

        if( $count > 0 ) {
            //Event Log
            //$event = "New Business/Vacation Group " . $roleNameBase . " has been created for " . $institution->getName();
            $event = "New Business/Vacation Group ".$institution->getName()." has been created.";
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $event, $user, $institution, $request, 'Business/Vacation Group Created');

            //Flash
            $this->addFlash(
                'notice',
                $event
            );
        }

        return $this->redirect($this->generateUrl('employees_siteparameters'));
    }
    

}
