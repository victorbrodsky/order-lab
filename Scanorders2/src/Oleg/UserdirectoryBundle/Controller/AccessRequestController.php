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

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_UNAPPROVED') &&
            false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_BANNED')
        ) {
            return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_login'));
        }

        $user = $this->get('security.context')->getToken()->getUser();

        return $this->accessRequestCreateNew($user->getId(),$this->container->getParameter('employees.sitename'));
    }

    /**
     * @Route("/access-requests/new/{id}/{sitename}", name="employees_access_request_new", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreateAction($id,$sitename)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_UNAPPROVED') &&
            false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_BANNED')
        ) {
            return $this->redirect($this->generateUrl($sitename.'_login'));
        }

        return $this->accessRequestCreateNew($id,$sitename);

    }
    public function accessRequestCreateNew($id,$sitename) {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$user) {
            return $this->redirect($this->generateUrl($sitename.'_login'));
            //throw $this->createNotFoundException('Unable to find User.');
        }

        $secUtil = $this->get('order_security_utility');
        $userAccessReq = $secUtil->getUserAccessRequest($user,$sitename);

        if( $userAccessReq && $userAccessReq->getStatus() == AccessRequest::STATUS_ACTIVE ) {

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());

            $text = "You have requested access on " . $dateStr . ". Your request has not been approved yet. Please contact the system administrator by emailing ".$this->container->getParameter('default_system_email')." if you have any questions.";

            //$this->get('security.context')->setToken(null);
            //$this->get('request')->getSession()->invalidate();

            return $this->render('OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig',array('text'=>$text,'sitename'=>$sitename));
        }

        if( $userAccessReq && $userAccessReq->getStatus() == AccessRequest::STATUS_DECLINED ) {

            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());
            $text = 'You have requested access on '.$dateStr.'. Your request has been declined. Please contact the system administrator by emailing '.$this->container->getParameter('default_system_email').' if you have any questions.';

            return $this->render('OlegUserdirectoryBundle:AccessRequest:request_confirmation.html.twig',array('text'=>$text,'sitename'=>$sitename));
        }

        return array(
            'userid' => $id,
            'sitename' => $sitename
        );
    }



    /**
     * @Route("/access-requests/new/{id}/{sitename}", name="employees_access_request_create", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestAction($id,$sitename)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_UNAPPROVED') ) {
            return $this->redirect( $this->generateUrl($sitename.'_logout') );
        }

        return $this->accessRequestCreate($id,$sitename);

    }
    public function accessRequestCreate($id,$sitename) {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User.');
        }

        //$user->setAppliedforaccess('active');
        //$user->setAppliedforaccessdate( new \DateTime() );

        $secUtil = $this->get('order_security_utility');
        $userAccessReq = $secUtil->getUserAccessRequest($user,$sitename);

        echo "sitename=".$sitename."<br>";

        if( $userAccessReq ) {
            //throw $this->createNotFoundException('AccessRequest is already created for this user');
            $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
            $dateStr = $transformer->transform($userAccessReq->getCreatedate());

            $text = "You have requested access on " . $dateStr . ". " .
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
        $emailUtil = new EmailUtil();

        $emailUtil->sendEmail( $email, "Access request confirmation for site: ".$sitename, $text );

        $emailStr = "";
        if( $email && $email != "" ) {
            $emailStr = "\r\nConfirmation email was sent to ".$email;
        }

        $text = 'Your access request was successfully submitted and and will be reviewed.'.$emailStr;

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
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
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

        return array(
            'entities' => $pagination,
            'roles' => $rolesArr,
            'sitename' => $sitename
        );

    }


    /**
     * @Route("/access-requests/{id}/{status}/{role}/status", name="employees_accessrequest_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestChangeAction($id, $status, $role)
    {

        if (false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //$entity->setAppliedforaccess($status);
        $accReq = $em->getRepository('OlegUserdirectoryBundle:AccessRequest')->findOneByUser($id);

        if( $status == "approved" && $role == "submitter" ) {
            $entity->setRoles(array());
            $entity->addRole('ROLE_USERDIRECTORY_OBSERVER');
            $accReq->setStatus(AccessRequest::STATUS_APPROVED);
        }

        if( $status == "declined" ) {
            $entity->setRoles(array());
            $entity->addRole('ROLE_USERDIRECTORY_BANNED');
            $accReq->setStatus(AccessRequest::STATUS_DECLINED);
        }

        if( $status == "active" ) {
            $entity->setRoles(array());
            $entity->addRole('ROLE_USERDIRECTORY_UNAPPROVED');
            $accReq->setStatus(AccessRequest::STATUS_ACTIVE);
        }

        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_accessrequest_list'));
    }
    
}
