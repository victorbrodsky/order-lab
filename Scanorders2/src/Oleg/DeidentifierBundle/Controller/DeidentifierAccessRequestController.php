<?php

namespace Oleg\DeidentifierBundle\Controller;

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
class DeidentifierAccessRequestController extends AccessRequestController
{

    public function __construct() {
        $this->siteName = 'deidentifier';
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Deidentifier';
        $this->roleBanned = 'ROLE_DEIDENTIFICATOR_BANNED';
        $this->roleUser = 'ROLE_DEIDENTIFICATOR_USER';
        $this->roleUnapproved = 'ROLE_DEIDENTIFICATOR_UNAPPROVED';
        $this->roleEditor = 'ROLE_DEIDENTIFICATOR_ADMIN';
    }

    /**
     * @Route("/access-requests/new/create", name="deidentifier_access_request_new_plain")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreatePlainAction()
    {
        //exit('accessRequestCreatePlainAction');
        return parent::accessRequestCreatePlain();
    }

    /**
     * @Route("/access-requests/new", name="deidentifier_access_request_new")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreateAction()
    {
        return parent::accessRequestCreateAction();
    }

    /**
     * @Route("/access-requests/new/pending", name="deidentifier_access_request_create")
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
     * @Route("/access-requests", name="deidentifier_accessrequest_list")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:AccessRequest:access_request_list.html.twig")
     */
    public function accessRequestIndexAction()
    {
        return parent::accessRequestIndexAction();
    }

    /**
     * @Route("/access-requests/change-status/{id}/{status}", name="deidentifier_accessrequest_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestChangeAction($id, $status)
    {
        return parent::accessRequestChangeAction($id, $status);
    }


}
