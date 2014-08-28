<?php

namespace Oleg\OrderformBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Oleg\UserdirectoryBundle\Controller\AccessRequestController;

/**
 * AccessRequest controller.
 */
class ScanAccessRequestController extends AccessRequestController
{

    /**
     * @Route("/access-requests/new/create", name="scan_access_request_new_plain")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreatePlainAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_UNAPPROVED') &&
            false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_BANNED')
        ) {
            return $this->redirect($this->generateUrl($this->container->getParameter('scan.sitename').'_login'));
        }

        $user = $this->get('security.context')->getToken()->getUser();

        return $this->accessRequestCreateNew($user->getId(),$this->container->getParameter('scan.sitename'));
    }

    /**
     * @Route("/access-requests/new/{id}/{sitename}", name="scan_access_request_new", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreateAction($id,$sitename)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_UNAPPROVED_SUBMITTER') &&
            false === $this->get('security.context')->isGranted('ROLE_SCANORDER_BANNED')
        ) {
            return $this->redirect($this->generateUrl($sitename.'_login'));
        }

        return $this->accessRequestCreateNew($id,$sitename);
    }

    /**
     * @Route("/access-requests/new/{id}/{sitename}", name="scan_access_request_create", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestAction($id,$sitename)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_UNAPPROVED') ) {
            //return $this->redirect( $this->generateUrl($sitename.'_logout') );
        }

        return $this->accessRequestCreate($id,$sitename);
    }


    /**
     * Lists all Access Request.
     *
     * @Route("/access-requests", name="scan_accessrequest_list")
     * @Method("GET")
     * @Template("OlegOrderformBundle:AccessRequest:access_request_list.html.twig")
     */
    public function accessRequestIndexAction()
    {
        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        return $this->accessRequestIndexList($this->container->getParameter('scan.sitename'));
    }


    /**
     * @Route("/access-requests/{id}/{status}/{role}/status", name="scan_accessrequest_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestChangeAction($id, $status, $role)
    {

        if (false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR')) {
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
            $entity->addRole('ROLE_SCANORDER_SUBMITTER');
            $entity->addRole('ROLE_SCANORDER_ORDERING_PROVIDER');
            $accReq->setStatus(AccessRequest::STATUS_APPROVED);
        }

        if( $status == "declined" ) {
            $entity->setRoles(array());
            $entity->addRole('ROLE_SCANORDER_BANNED');
            $accReq->setStatus(AccessRequest::STATUS_DECLINED);
        }

        if( $status == "active" ) {
            $entity->setRoles(array());
            $entity->addRole('ROLE_SCANORDER_UNAPPROVED_SUBMITTER');
            $accReq->setStatus(AccessRequest::STATUS_ACTIVE);
        }

        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl($this->container->getParameter('scan.sitename').'_accessrequest_list'));
    }
    
}
