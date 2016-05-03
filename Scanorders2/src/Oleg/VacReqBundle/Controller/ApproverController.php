<?php

namespace Oleg\VacReqBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\Roles;
use Oleg\UserdirectoryBundle\Form\SimpleUserType;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\VacReqBundle\Entity\VacReqRequest;
use Oleg\VacReqBundle\Entity\VacReqSettings;
use Oleg\VacReqBundle\Form\VacReqEmailusersType;
use Oleg\VacReqBundle\Form\VacReqGroupType;
use Oleg\VacReqBundle\Form\VacReqRequestType;
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
     * Creates a new VacReqRequest entity.
     *
     * @Route("/groups/", name="vacreq_approvers")
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

//        //vacreq_util
//        $vacreqUtil = $this->get('vacreq_util');
//        $arraySettings = $vacreqUtil->getInstitutionSettingArray();

        return array(
            'organizationalInstitutions' => $organizationalInstitutions,
//            'arraySettings' => $arraySettings
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
            $eventType = "Business/Vacation Group Updated";
            $event = $organizationalGroupInstitution.": User ".$subjectUser." has been removed as ".$role->getAlias();
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$organizationalGroupInstitution,$request,$eventType);

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

        $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($instid);
        if( !$institution ) {
            throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
        }

        if(0) {
//            //Choose user by keytype and userid
//            $keytype = $request->query->get('keytype');
//            $keytype = trim($keytype);
//
//            $primaryPublicUserId = $request->query->get('primaryPublicUserId');
//            $primaryPublicUserId = trim($primaryPublicUserId);
//
//            //exit('primaryPublicUserId='.$primaryPublicUserId);
//
//            //find user in DB
//            $users = $em->getRepository('OlegUserdirectoryBundle:User')->findBy(array('keytype' => $keytype, 'primaryPublicUserId' => $primaryPublicUserId));
//
//            if( count($users) > 1 ) {
//                throw $this->createNotFoundException('Unable to find a Single User. Found users ' . count($users) );
//            }
//
//            if( count($users) == 1 ) {
//                $subjectUser = $users[0];
//            }
//
//            if( count($users) == 0 ) {
//                $keytypeObj = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->find($keytype);
//                $this->get('session')->getFlashBag()->set(
//                    'notice',
//                    'User ' . $primaryPublicUserId . ' (' . $keytypeObj . ')' . ' not found.'." ".
//                    "Please use the 'Create New User' form to add a new user."
//                );
//                return $this->redirect( $this->generateUrl("employees_new_user",array("user-type"=>$keytype,"user-name"=>$primaryPublicUserId)) );
//            }
//
//            $subjectUser->addRole($role);
//            $em->persist($subjectUser);
//            $em->flush();
//
//            $event = "User ".$subjectUser." has been added as ".$role->getAlias();
//            $eventType = "User record updated";
//            //Event Log
//            $userSecUtil = $this->container->get('user_security_utility');
//            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$subjectUser,$request,$eventType);

        } else {

            $params = array(
                'cycle' => 'create',
                'readonly' => false,
            );
            $form = $this->createForm(new VacReqUserComboboxType($params));

            $form->handleRequest($request);

            //$users = $form['users']->getData();
            $users = $form->get('users')->getData();

            $usersStr = array();

            foreach( $users as $user ) {
                //echo "user=".$user."<br>";
                $user->addRole($role);
                $em->persist($user);
                $usersStr[] = $user;
            }

            //$users = $request->query->get('users');
            //$users = trim($users);

            $em->flush();

            $event = $institution.": the following users have been added as ".$role->getAlias().": ".implode(",",$usersStr);
            $eventType = "Business/Vacation Group Updated";

            //Event Log
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$institution,$request,$eventType);

            //exit();
        }

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            $event
        );

        return $this->redirectToRoute('vacreq_orginst_management', array('institutionId'=>$instid));
    }



    /**
     * @Route("/organizational-institution-add", name="vacreq_group_add")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-add.html.twig")
     */
    public function addGroupAction(Request $request )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') || false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
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
            $userutil = new UserUtil();
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
                $approverRole = $userutil->setDefaultList($approverRole, 1000, $user, $roleName);
                $approverRole->setLevel(50);
                $approverRole->setAlias('Vacation Request Approver for the ' . $institution->getName());
                $approverRole->setDescription('Can search and approve vacation requests for specified service');
                $approverRole->addSite($site);
                $approverRole->setInstitution($institution);
                $userSecUtil->checkAndAddPermissionToRole($approverRole, "Approve a Vacation Request", "VacReqRequest", "changestatus");

                $em->persist($approverRole);
                $em->flush($approverRole);

                $count++;
            }

            //create submitter role
            $roleName = "ROLE_VACREQ_SUBMITTER_".$roleNameBase;
            $submitterRole = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($roleName);
            if( !$submitterRole ) {
                $submitterRole = new Roles();
                $submitterRole = $userutil->setDefaultList($submitterRole, 1000, $user, $roleName);
                $submitterRole->setLevel(30);
                $submitterRole->setAlias('Vacation Request Approver for the ' . $institution->getName());
                $submitterRole->setDescription('Can search and approve vacation requests for specified service');
                $submitterRole->addSite($site);
                $submitterRole->setInstitution($institution);
                $userSecUtil->checkAndAddPermissionToRole($submitterRole, "Submit a Vacation Request", "VacReqRequest", "create");

                $em->persist($submitterRole);
                $em->flush($submitterRole);

                $count++;
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
     * @Route("/organizational-institution-remove/{instid}", name="vacreq_group_remove")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Approver:orginst-user-add.html.twig")
     */
    public function removeGroupAction(Request $request, $instid )
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') || false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
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

        $removedRoles[] = $this->removeVacReqGroupByInstitution($instid,"ROLE_VACREQ_APPROVER_",$request);
        $removedRoles[] = $this->removeVacReqGroupByInstitution($instid,"ROLE_VACREQ_SUBMITTER_",$request);

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

        //2) remove approver role from all users
        if( $role ) {
            $users = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleName);
            foreach( $users as $user ) {
                $user->removeRole($roleName);
            }

            $em->remove($role);
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

        if( !$entity ) {
            $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($instid);
            if( !$institution ) {
                throw $this->createNotFoundException('Unable to find Vacation Request Institution by id='.$instid);
            }
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

}
