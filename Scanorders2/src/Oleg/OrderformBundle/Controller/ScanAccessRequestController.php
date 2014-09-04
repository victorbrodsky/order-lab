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

        $userSecUtil = $this->get('user_security_utility');

        $user = $this->get('security.context')->getToken()->getUser();

        //the user might be authenticated by another site. If the user does not have lowest role => assign unapproved role to trigger access request
        if( false === $userSecUtil->hasGlobalUserRole('ROLE_SCANORDER_SUBMITTER',$user) ) {
            //exit('adding unapproved');
            $user->addRole('ROLE_SCANORDER_UNAPPROVED_SUBMITTER');
        }

        if( false === $userSecUtil->hasGlobalUserRole('ROLE_SCANORDER_UNAPPROVED_SUBMITTER',$user)) {
            //return $this->redirect($this->generateUrl($this->container->getParameter('scan.sitename').'_login'));

            //exit('nopermission create scan access request for non ldap user');

            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have permission to visit Scan Order site."
            );

            return $this->redirect( $this->generateUrl('main_common_home') );
        }

        return $this->accessRequestCreateNew($user->getId(),$this->container->getParameter('scan.sitename'));
    }

    /**
     * @Route("/access-requests/new/{id}/{sitename}", name="scan_access_request_new", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreateAction($id,$sitename)
    {

        $userSecUtil = $this->get('user_security_utility');
        if( false === $userSecUtil->hasGlobalUserRole('ROLE_SCANORDER_UNAPPROVED_SUBMITTER') ) {
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
        $userSecUtil = $this->get('user_security_utility');
        if( false === $userSecUtil->hasGlobalUserRole('ROLE_SCANORDER_UNAPPROVED_SUBMITTER') ) {
            return $this->redirect($this->generateUrl($sitename.'_login'));
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
     * @Route("/access-requests/change-status/{id}/{status}", name="scan_accessrequest_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestChangeAction($id, $status)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //$accReq = $em->getRepository('OlegUserdirectoryBundle:AccessRequest')->findOneByUser($id);
        $userSecUtil = $this->get('user_security_utility');
        $accReq = $userSecUtil->getUserAccessRequest($id,$this->container->getParameter('scan.sitename'));

        if( !$accReq ) {
            throw new \Exception( 'AccessRequest is not found by id=' . $id );
        }

        if( $status == "approved" ) {

            $entity->removeRole('ROLE_SCANORDER_UNAPPROVED_SUBMITTER');
            $entity->removeRole('ROLE_SCANORDER_BANNED');

            $entity->addRole('ROLE_SCANORDER_SUBMITTER');
            $entity->addRole('ROLE_SCANORDER_ORDERING_PROVIDER');

            if( $accReq )
                $accReq->setStatus(AccessRequest::STATUS_APPROVED);
        }

        if( $status == "declined" ) {

            $entity->removeRole('ROLE_SCANORDER_SUBMITTER');
            $entity->removeRole('ROLE_SCANORDER_ORDERING_PROVIDER');

            $entity->addRole('ROLE_SCANORDER_BANNED');

            if( $accReq )
                $accReq->setStatus(AccessRequest::STATUS_DECLINED);
        }

        if( $status == "active" ) {

            $entity->removeRole('ROLE_SCANORDER_SUBMITTER');
            $entity->removeRole('ROLE_SCANORDER_ORDERING_PROVIDER');

            $entity->addRole('ROLE_SCANORDER_UNAPPROVED_SUBMITTER');
            if( $accReq )
                $accReq->setStatus(AccessRequest::STATUS_ACTIVE);
        }

        $em->persist($entity);
        $em->persist($accReq);
        $em->flush();

        return $this->redirect($this->generateUrl($this->container->getParameter('scan.sitename').'_accessrequest_list'));
    }
    
}
