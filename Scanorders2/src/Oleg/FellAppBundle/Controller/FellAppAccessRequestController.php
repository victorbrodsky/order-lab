<?php

namespace Oleg\FellAppBundle\Controller;

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
class FellAppAccessRequestController extends AccessRequestController
{

    public function __construct() {
        $this->siteName = 'fellapp';
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Fellowship Applications';
        $this->roleBanned = 'ROLE_FELLAPP_BANNED';
        $this->roleUser = 'ROLE_FELLAPP_USER';
        $this->roleUnapproved = 'ROLE_FELLAPP_UNAPPROVED';
        $this->roleEditor = 'ROLE_FELLAPP_COORDINATOR';
    }

    /**
     * @Route("/access-requests/new/create", name="fellapp_access_request_new_plain")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreatePlainAction()
    {
        return parent::accessRequestCreatePlain();
    }

    /**
     * @Route("/access-requests/new", name="scan_access_request_new")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreateAction()
    {
        return parent::accessRequestCreateAction();
    }

    /**
     * @Route("/access-requests/new/pending", name="fellapp_access_request_create")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestAction()
    {
        return parent::accessRequestAction();
    }

    /**
     * Lists all Access Request.
     *
     * @Route("/access-requests", name="fellapp_accessrequest_list")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request_list.html.twig")
     */
    public function accessRequestIndexAction()
    {
        return parent::accessRequestIndexAction();
    }

    /**
     * @Route("/access-requests/change-status/{id}/{status}", name="fellapp_accessrequest_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestChangeAction($id, $status)
    {
        return parent::accessRequestChangeAction($id, $status);
    }


}
