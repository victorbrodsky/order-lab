<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Form\AccessRequestManagementType;
use Oleg\UserdirectoryBundle\Form\AccessRequestUserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Oleg\UserdirectoryBundle\Util\EmailUtil;


//When admin approves acc req, the user must logout and login.

/**
 * AccessRequest controller.
 */
class AccessRequestController extends Controller
{

    protected $router;
    protected $siteName;
    protected $siteNameShowuser;
    protected $siteNameStr;
    protected $roleBanned;
    protected $roleUser;
    protected $roleUnapproved;

    public function __construct() {
        $this->siteName = 'employees'; //controller is not setup yet, so we can't use $this->container->getParameter('employees.sitename');
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Employee Directory';
        $this->roleBanned = 'ROLE_USERDIRECTORY_BANNED';
        $this->roleUser = 'ROLE_USERDIRECTORY_OBSERVER';
        $this->roleUnapproved = 'ROLE_USERDIRECTORY_UNAPPROVED';
        $this->roleEditor = 'ROLE_USERDIRECTORY_EDITOR';
    }

    /**
     * @Route("/access-requests/new/create", name="employees_access_request_new_plain")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreatePlainAction()
    {

        return $this->accessRequestCreatePlain();
    }
    public function accessRequestCreatePlain()
    {

        $userSecUtil = $this->get('user_security_utility');

        $user = $this->get('security.context')->getToken()->getUser();

        //the user might be authenticated by another site. If the user does not have lowest role => assign unapproved role to trigger access request
        if( false === $userSecUtil->hasGlobalUserRole($this->roleUser,$user) ) {
            $user->addRole($this->roleUnapproved);
        }

//        if( true === $userSecUtil->hasGlobalUserRole($this->roleUser,$user) ) {
//            return $this->redirect($this->generateUrl('employees-nopermission'));
//        }

        if( false === $userSecUtil->hasGlobalUserRole($this->roleUnapproved,$user) ) {

            //relogin the user, because when admin approves accreq, the user must relogin to update the role in security context. Or update security context (How?)
            //return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_login'));

            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have permission to visit this page on ".$this->siteNameStr." site."."<br>".
                "If you already applied for access, then try to " . "<a href=".$this->generateUrl($this->siteName.'_logout',true).">Re-Login</a>"
            );
            return $this->redirect( $this->generateUrl('main_common_home') );
        }

        $roles = array(
            "unnaproved" => $this->roleUnapproved,
            "banned" => $this->roleBanned,
        );

        return $this->accessRequestCreateNew($user->getId(),$this->siteName,$roles);
    }


    /**
     * @Route("/access-requests/new", name="employees_access_request_new")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreateAction()
    {

        $sitename = $this->siteName;

        $user = $this->get('security.context')->getToken()->getUser();

        $userSecUtil = $this->get('user_security_utility');
        if( false === $userSecUtil->hasGlobalUserRole($this->roleUnapproved,$user) ) {
            return $this->redirect($this->generateUrl($sitename.'_login'));
        }

        $roles = array(
            "unnaproved" => $this->roleUnapproved,
            "banned" => $this->roleBanned,
        );

        return $this->accessRequestCreateNew($user->getId(),$sitename,$roles);
    }


    // check for cases:
    // 1) user has active accreq
    // 2) user has accreq but it was declined
    // 3) user has role banned
    // 4) user has approved accreq, but user has ROLE_UNAPPROVED
    public function accessRequestCreateNew($id,$sitename,$roles) {

        //echo "create new accreq <br>";

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$user) {
            return $this->redirect($this->generateUrl($sitename.'_login'));
            //throw $this->createNotFoundException('Unable to find User.');
        }

        $secUtil = $this->get('order_security_utility');
        $userAccessReq = $secUtil->getUserAccessRequest($user,$sitename);

        $sitenameFull = $this->siteNameStr;

        // Case 1: user has active accreq
        if( $userAccessReq && $userAccessReq->getStatus() == AccessRequest::STATUS_ACTIVE ) {

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());

            $text = "You have requested access to ".$sitenameFull." on " . $dateStr . ". Your request has not been approved yet. Please contact the system administrator by emailing ".$this->container->getParameter('default_system_email')." if you have any questions.";

            //$this->get('security.context')->setToken(null);
            //$this->get('request')->getSession()->invalidate();

            return $this->render('OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig',array('text'=>$text,'sitename'=>$sitename,'pendinguser'=>true));
        }

        // Case 2: user has accreq but it was declined
        if( $userAccessReq && $userAccessReq->getStatus() == AccessRequest::STATUS_DECLINED ) {

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());
            $text = 'You have requested access to '.$sitenameFull.' on '.$dateStr.'. Your request has been declined. Please contact the system administrator by emailing '.$this->container->getParameter('default_system_email').' if you have any questions.';

            return $this->render('OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig',array('text'=>$text,'sitename'=>$sitename,'pendinguser'=>true));
        }

        // Case 3: user has role banned
        $userSecUtil = $this->get('user_security_utility');
        if( $userSecUtil->hasGlobalUserRole($roles['banned'],$user) ) {

            $this->get('session')->getFlashBag()->add(
                'warning',
                "You were banned to visit this site."."<br>".
                "You can try to " . "<a href=".$this->generateUrl($sitename.'_logout',true).">Re-Login</a>"
            );
            return $this->redirect( $this->generateUrl('main_common_home') );
        }

        // Case 4: user has approved accreq, but user has ROLE_UNAPPROVED
        if( $userAccessReq && $userAccessReq->getStatus() == AccessRequest::STATUS_APPROVED && $userSecUtil->hasGlobalUserRole($roles['unnaproved'],$user) ) {

            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have permission to visit this site because you have UNAPPROVED role."."<br>".
                "Please contact site system administrator ".$this->container->getParameter('default_system_email')."<br>".
                "You can try to " . "<a href=".$this->generateUrl($sitename.'_logout',true).">Re-Login</a>"
            );
            return $this->redirect( $this->generateUrl('main_common_home') );
        }

        //echo "create new accreq, exit??? <br>";

        return array(
            'sitename' => $sitename,
            'sitenamefull' => $sitenameFull,
            'pendinguser' => true
        );
    }



     /**
      * @Route("/access-requests/new/pending", name="employees_access_request_create")
      * @Method("POST")
      * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
      */
    public function accessRequestAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $id = $user->getId();
        $sitename = $this->siteName;

        return $this->accessRequestCreate($id,$sitename);

    }
    public function accessRequestCreate($id,$sitename) {

        //echo "create new accreq, post <br>";

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User.');
        }

        //$user->setAppliedforaccess('active');
        //$user->setAppliedforaccessdate( new \DateTime() );

        $secUtil = $this->get('order_security_utility');
        $userAccessReq = $secUtil->getUserAccessRequest($user,$sitename);

        $sitenameFull = $this->siteNameStr;

        //echo "sitename=".$sitename."<br>";

        if( $userAccessReq ) {

            if( $userAccessReq->getStatus() == AccessRequest::STATUS_APPROVED ) {
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    "The status of your request is " . $userAccessReq->getStatusStr() . ". " .
                    "Please re-login to access this site " . "<a href=".$this->generateUrl($sitename.'_logout',true).">Re-Login</a>"
                );
                return $this->redirect( $this->generateUrl('main_common_home') );
            }

            //throw $this->createNotFoundException('AccessRequest is already created for this user');
            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());

            $text = "You have requested access to ".$sitenameFull." on " . $dateStr . ". " .
                "The status of your request is " . $userAccessReq->getStatusStr() . "." .
                "Please contact the system administrator by emailing ".$this->container->getParameter('default_system_email')." if you have any questions.";

            return $this->render('OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig',array('text'=>$text,'sitename'=>$sitename,'pendinguser'=>true));
        }

        //Create a new active AccessRequest
        $accReq = new AccessRequest();
        $accReq->setStatus(AccessRequest::STATUS_ACTIVE);
        $accReq->setUser($user);
        $accReq->setSiteName($sitename);

        $em->persist($accReq);
        $em->flush();

        $email = $user->getEmail();
        $emailStr = "";
        if( $email && $email != "" ) {
            $emailStr = "\r\nConfirmation email was sent to ".$email;
        }

        $emailUtil = new EmailUtil();

        $siteurl = $this->generateUrl( $sitename.'_home', array(), true );

        $emailBody = 'Your access request for the ' .
            //'<a href="'.$siteurl.'">'.$sitenameFull.'</a>' .
            $sitenameFull . ': ' . $siteurl . ' ' .
            ' was successfully submitted and and will be reviewed.'.$emailStr;

        $emailUtil->sendEmail( $email, "Access request confirmation for site: ".$sitenameFull, $emailBody, $em );

        $text = 'Your access request was successfully submitted and and will be reviewed.'.$emailStr;

        ///////////////// Send an email to the preferred emails of the users who have Administrator role for a given site and CC the users with Platform Administrator role when an access request is submitted
        $incomingReqPage = $this->generateUrl( $sitename.'_home', array(), true );
        $subject = "[O R D E R] Access request for ".$sitenameFull." received from ".$user->getUsernameOptimal();
        $msg = $user->getUsernameOptimal()." submitted a request to access ".$sitenameFull.". Please visit ".$incomingReqPage." to approve or deny it.";

        $approveDelineMsg = "the access request from ".$user->getUsernameOptimal()." to access ".$sitenameFull.", visit the following link:";
        //add approve link
        $approvedLink = $this->generateUrl( $sitename.'_accessrequest_change', array("id"=>$id,"status"=>"approve"), true );
        $approvedMsg = "To approve " . $approveDelineMsg . "\r\n" . $approvedLink;

        //add decline link
        $declinedLink = $this->generateUrl( $sitename.'_accessrequest_change', array("id"=>$id,"status"=>"decline"), true );
        $declinedMsg = "To decline " . $approveDelineMsg . "\r\n" . $declinedLink;

        $msg = $msg . "\r\n"."\r\n" . $approvedMsg . "\r\n"."\r\n" . $declinedMsg;

        $userSecUtil = $this->get('user_security_utility');
        $emails = $userSecUtil->getUserEmailsByRole($sitename,"Administrator");
        $headers = $userSecUtil->getUserEmailsByRole($sitename,"Platform Administrator");

        if( !$emails ) {
            $emails = $headers;
            $headers = null;
        }

        //echo "user emails=".$emails."<br>";
        //echo "user headers=".$headers."<br>";
        //exit('1');

        $emailUtil->sendEmail( $emails, $subject, $msg, $em, $headers );
        ///////////////// EOF /////////////////

        return $this->render('OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig',array('text'=>$text,'sitename'=>$sitename,'pendinguser'=>true));
    }

//    public function reLoginUser($sitename) {
//        echo "relogin sitename=".$sitename."<br>";
//        $this->get('session')->getFlashBag()->add(
//            'warning',
//            "You must re-login to access this site " . "<a href=".$this->generateUrl($sitename.'_logout',true).">Re-Login</a>"
//        );
//        return $this->redirect( $this->generateUrl('main_common_home') );
//    }



    /**
     * Lists all Access Request.
     *
     * @Route("/access-requests", name="employees_accessrequest_list")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request_list.html.twig")
     */
    public function accessRequestIndexAction()
    {
        if( false === $this->get('security.context')->isGranted($this->roleEditor) ) {
            return $this->redirect( $this->generateUrl($this->siteName."-nopermission") );
        }

        return $this->accessRequestIndexList($this->siteName);
    }
    public function accessRequestIndexList( $sitename ) {

        $em = $this->getDoctrine()->getManager();

        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findAll();
        $rolesArr = array();
        foreach( $roles as $role ) {
            $rolesArr[$role->getName()] = $role->getAlias();
        }

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:AccessRequest');
        $dql =  $repository->createQueryBuilder("accreq");
        $dql->select('accreq');
        $dql->innerJoin('accreq.user','user');
        $dql->innerJoin('user.infos','infos');
        $dql->innerJoin('user.keytype','keytype');
        $dql->leftJoin('accreq.updatedby','updatedby');
        $dql->leftJoin('updatedby.infos','updatedbyinfos');
        $dql->where("accreq.siteName = '" . $sitename . "'" );
        //$dql->where("accreq.status = ".AccessRequest::STATUS_ACTIVE." OR accreq.status = ".AccessRequest::STATUS_DECLINED." OR accreq.status = ".AccessRequest::STATUS_APPROVED);
        
		$request = $this->get('request');
		$postData = $request->query->all();
		
		if( !isset($postData['sort']) ) { 
			$dql->orderBy("accreq.status","DESC");
		}
		
		//pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters       
//		if( isset($postData['sort']) ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }

        $limit = 30;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        $sitenameFull = $this->siteNameStr;

        return array(
            'entities' => $pagination,
            'roles' => $rolesArr,
            'sitename' => $sitename,
            'sitenameshowuser' => $this->siteNameShowuser,
            'sitenamefull'=>$sitenameFull
        );

    }


    /**
     * @Route("/access-requests/change-status/{id}/{status}", name="employees_accessrequest_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestChangeAction($id, $status)
    {

        if (false === $this->get('security.context')->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName."-nopermission") );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $userSecUtil = $this->get('user_security_utility');
        $accReq = $userSecUtil->getUserAccessRequest($id,$this->siteName);

        if( !$accReq ) {
            throw new \Exception( 'AccessRequest is not found by id=' . $id );
        }

        if( $status == "approved" || $status == "approve" ) {
            //$entity->setRoles(array());
            $entity->removeRole($this->roleUnapproved);
            $entity->removeRole($this->roleBanned);

            $entity->addRole($this->roleUser);
            if( $accReq )
                $accReq->setStatus(AccessRequest::STATUS_APPROVED);
        }

        if( $status == "declined" || $status == "decline" ) {
            //$entity->setRoles(array());
            $entity->removeRole($this->roleUser);

            $entity->addRole($this->roleBanned);
            if( $accReq )
                $accReq->setStatus(AccessRequest::STATUS_DECLINED);
        }

        if( $status == "active" ) {
            //$entity->setRoles(array());
            $entity->removeRole($this->roleUser);

            $entity->addRole($this->roleUnapproved);
            if( $accReq )
                $accReq->setStatus(AccessRequest::STATUS_ACTIVE);
        }

        //set updated by and updated author roles
        $user = $this->get('security.context')->getToken()->getUser();
        $accReq->setUpdatedby($user);
        $accReq->setUpdateAuthorRoles($user->getRoles());

        $em->persist($entity);
        $em->persist($accReq);
        $em->flush();

        //////// When the user's Access Request has been approved, send an email to the user from the email address in Site Settings with... ////
        $this->createAccessRequestUserNotification( $entity, $status, $this->siteName );
        //////////////////// EOF ////////////////////

        return $this->redirect($this->generateUrl($this->siteName.'_accessrequest_list'));
    }

    public function createAccessRequestUserNotification( $subjectUser, $status, $sitename ) {

        $sitenameFull = $this->siteNameStr;

        $user = $this->get('security.context')->getToken()->getUser();
        $siteLink = $this->generateUrl( $sitename.'_home', array(), true );
        $newline = "\r\n";
        $msg = "";

        if( $user->getEmail() ) {
            $adminEmail = $user->getEmail();
            $adminEmailStr = " (".$adminEmail.")";
        } else {
            $adminEmailStr = "";
        }

        if( $status == "approved" || $status == "approve" ) {
            $subject = "Your request to access ".$sitenameFull." has been approved";  //"Access granted to site: ".$sitenameFull;

            $msg = $subjectUser->getUsernameOptimal().",".$newline.$newline.
                "Your request to access ".$sitenameFull.": ".$siteLink." has been approved. You can now log in.".$newline.$newline.
                $user->getUsernameOptimal().$adminEmailStr;
        }


        if( $status == "declined" || $status == "decline" ) {
            $subject = "Your request to access ".$sitenameFull." has been denied";

            $msg = $subjectUser->getUsernameOptimal().",".$newline.$newline.
                "Your request to access ".$sitenameFull.": ".$siteLink." has been denied. For additional details please email ".$user->getUsernameOptimal().$adminEmailStr.".".$newline.$newline.
                $user->getUsernameOptimal().$adminEmailStr;
        }


        if( $msg != "" ) {
            $email = $subjectUser->getEmail();
            $emailUtil = new EmailUtil();
            $em = $this->getDoctrine()->getManager();

            //                 $email, $subject, $message, $em, $ccs=null, $adminemail=null
            $emailUtil->sendEmail( $email, $subject, $msg, $em, null, $adminEmail );
        }
    }


    //access request management page with the process to force the admin to select the "PHI Scope" Institution(s) and "Role(s)"
    /**
     * @Route("/access-requests/{id}", name="employees_accessrequest_management", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request_management.html.twig")
     */
    public function accessRequestManagementAction( $id )
    {

        if (false === $this->get('security.context')->isGranted($this->roleEditor)) {
            return $this->redirect( $this->generateUrl($this->siteName."-nopermission") );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $userSecUtil = $this->get('user_security_utility');
        $accReq = $userSecUtil->getUserAccessRequest($id,$this->siteName);

        if( !$accReq ) {
            throw new \Exception( 'AccessRequest is not found by id=' . $id );
        }


        $params = array(
            //'institutions' => $institutions,
            'sitename' => $this->siteName,
        );
        $form = $this->createForm(new AccessRequestUserType($params), $entity);

        return array(
            'form' => $form->createView(),
            'accreq' => $accReq,
            'entity' => $entity,
            'sitename' => $this->siteName,
            'sitenameshowuser' => $this->siteNameShowuser,
            'sitenamefull'=>$this->siteNameStr
        );
    }

}
