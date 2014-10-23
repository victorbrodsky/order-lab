<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
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

    /**
     * @Route("/access-requests/new/create", name="employees_access_request_new_plain")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreatePlainAction()
    {

        $userSecUtil = $this->get('user_security_utility');

        $user = $this->get('security.context')->getToken()->getUser();

        //the user might be authenticated by another site. If the user does not have lowest role => assign unapproved role to trigger access request
        if( false === $userSecUtil->hasGlobalUserRole('ROLE_USERDIRECTORY_OBSERVER',$user) ) {
            $user->addRole('ROLE_USERDIRECTORY_UNAPPROVED');
        }

        if( false === $userSecUtil->hasGlobalUserRole('ROLE_USERDIRECTORY_UNAPPROVED',$user) ) {

            //relogin the user, because when admin approves accreq, the user must relogin to update the role in security context. Or update security context (How?)
            //return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_login'));

            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have permission to visit Employee Directory site."."<br>".
                "If you already applied for access, then try to " . "<a href=".$this->generateUrl($this->container->getParameter('scan.sitename').'_logout',true).">Re-Login</a>"
            );
            return $this->redirect( $this->generateUrl('main_common_home') );
        }

        $roles = array(
            "unnaproved" => "ROLE_USERDIRECTORY_UNAPPROVED",
            "banned" => "ROLE_USERDIRECTORY_BANNED",
        );

        return $this->accessRequestCreateNew($user->getId(),$this->container->getParameter('employees.sitename'),$roles);
    }

    /**
     * @Route("/access-requests/new/{id}/{sitename}", name="employees_access_request_new", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreateAction($id,$sitename)
    {

        $userSecUtil = $this->get('user_security_utility');
        if( false === $userSecUtil->hasGlobalUserRole('ROLE_USERDIRECTORY_UNAPPROVED') ) {
            return $this->redirect($this->generateUrl($sitename.'_login'));
        }

        $roles = array(
            "unnaproved" => "ROLE_USERDIRECTORY_UNAPPROVED",
            "banned" => "ROLE_USERDIRECTORY_BANNED",
        );

        return $this->accessRequestCreateNew($id,$sitename,$roles);

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

        if( $sitename == $this->container->getParameter('employees.sitename') ) {
            $sitenameFull = "Employee Directory";
        }
        if( $sitename == $this->container->getParameter('scan.sitename') ) {
            $sitenameFull = "Scan Orders";
        }

        // Case 1: user has active accreq
        if( $userAccessReq && $userAccessReq->getStatus() == AccessRequest::STATUS_ACTIVE ) {

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());

            $text = "You have requested access to ".$sitenameFull." on " . $dateStr . ". Your request has not been approved yet. Please contact the system administrator by emailing ".$this->container->getParameter('default_system_email')." if you have any questions.";

            //$this->get('security.context')->setToken(null);
            //$this->get('request')->getSession()->invalidate();

            return $this->render('OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig',array('text'=>$text,'sitename'=>$sitename));
        }

        // Case 2: user has accreq but it was declined
        if( $userAccessReq && $userAccessReq->getStatus() == AccessRequest::STATUS_DECLINED ) {

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());
            $text = 'You have requested access to '.$sitenameFull.' on '.$dateStr.'. Your request has been declined. Please contact the system administrator by emailing '.$this->container->getParameter('default_system_email').' if you have any questions.';

            return $this->render('OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig',array('text'=>$text,'sitename'=>$sitename));
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
            'userid' => $id,
            'sitename' => $sitename,
            'sitenamefull'=>$sitenameFull
        );
    }



    /**
     * @Route("/access-requests/new/{id}/{sitename}", name="employees_access_request_create", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestAction($id,$sitename)
    {
//        $userSecUtil = $this->get('user_security_utility');
//        if( false === $userSecUtil->hasGlobalUserRole('ROLE_USERDIRECTORY_UNAPPROVED') ) {
//            return $this->redirect( $this->generateUrl($sitename.'_logout') );
//        }

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

        if( $sitename == $this->container->getParameter('employees.sitename') ) {
            $sitenameFull = "Employee Directory";
        }
        if( $sitename == $this->container->getParameter('scan.sitename') ) {
            $sitenameFull = "Scan Orders";
        }

        //echo "sitename=".$sitename."<br>";

        if( $userAccessReq ) {
            //throw $this->createNotFoundException('AccessRequest is already created for this user');
            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());

            $text = "You have requested access to ".$sitenameFull." on " . $dateStr . ". " .
                "The status of your request is " . $userAccessReq->getStatusStr() . "." .
                "Please contact the system administrator by emailing ".$this->container->getParameter('default_system_email')." if you have any questions.";

            return $this->render('OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig',array('text'=>$text,'sitename'=>$sitename));
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
        $subject = "[O R D E R] Access request for ".$sitenameFull." received from ".$user->getPrimaryUseridKeytypeStr();
        $msg = $user->getPrimaryUseridKeytypeStr()." submitted a request to access ".$sitenameFull.". Please visit ".$incomingReqPage." to approve or deny it.";

        $userSecUtil = $this->get('user_security_utility');
        $emails = $userSecUtil->getUserEmailsByRole($sitename,"Administrator");
        $emailsStr = implode(", ", $emails);
        $headers = $userSecUtil->getUserEmailsByRole($sitename,"Platform Administrator");
        $headersArr = implode(", ", $headers);

        $emailUtil->sendEmail( $emailsStr, $subject, $msg, $em, $headersArr );
        ///////////////// EOF /////////////////

        return $this->render('OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig',array('text'=>$text,'sitename'=>$sitename));
    }


    /**
     * Lists all Access Request.
     *
     * @Route("/access-requests", name="employees_accessrequest_list")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request_list.html.twig")
     */
    public function accessRequestIndexAction()
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        return $this->accessRequestIndexList($this->container->getParameter('employees.sitename'));
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
        $dql->innerJoin('user.keytype','keytype');
        $dql->where("accreq.siteName = '" . $sitename . "'" );
        //$dql->where("accreq.status = ".AccessRequest::STATUS_ACTIVE." OR accreq.status = ".AccessRequest::STATUS_DECLINED." OR accreq.status = ".AccessRequest::STATUS_APPROVED);
        $dql->orderBy("accreq.status","DESC");

        $limit = 30;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        if( $sitename == $this->container->getParameter('employees.sitename') ) {
            $sitenameFull = "Employee Directory";
        }
        if( $sitename == $this->container->getParameter('scan.sitename') ) {
            $sitenameFull = "Scan Orders";
        }

        return array(
            'entities' => $pagination,
            'roles' => $rolesArr,
            'sitename' => $sitename,
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

        if (false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR')) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $userSecUtil = $this->get('user_security_utility');
        $accReq = $userSecUtil->getUserAccessRequest($id,$this->container->getParameter('employees.sitename'));

        if( !$accReq ) {
            throw new \Exception( 'AccessRequest is not found by id=' . $id );
        }

        if( $status == "approved" ) {
            //$entity->setRoles(array());
            $entity->removeRole('ROLE_USERDIRECTORY_UNAPPROVED');
            $entity->removeRole('ROLE_USERDIRECTORY_BANNED');

            $entity->addRole('ROLE_USERDIRECTORY_OBSERVER');
            if( $accReq )
                $accReq->setStatus(AccessRequest::STATUS_APPROVED);
        }

        if( $status == "declined" ) {
            //$entity->setRoles(array());
            $entity->removeRole('ROLE_USERDIRECTORY_OBSERVER');

            $entity->addRole('ROLE_USERDIRECTORY_BANNED');
            if( $accReq )
                $accReq->setStatus(AccessRequest::STATUS_DECLINED);
        }

        if( $status == "active" ) {
            //$entity->setRoles(array());
            $entity->removeRole('ROLE_USERDIRECTORY_OBSERVER');

            $entity->addRole('ROLE_USERDIRECTORY_UNAPPROVED');
            if( $accReq )
                $accReq->setStatus(AccessRequest::STATUS_ACTIVE);
        }

        $em->persist($entity);
        $em->persist($accReq);
        $em->flush();

        return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_accessrequest_list'));
    }




}
