<?php

namespace Oleg\FellAppBundle\Controller;

use Oleg\UserdirectoryBundle\Controller\SiteParametersController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\UserdirectoryBundle\Entity\SiteParameters;
use Oleg\UserdirectoryBundle\Form\SiteParametersType;
use Oleg\UserdirectoryBundle\Util\UserUtil;

/**
 * SiteParameters controller.
 *
 * @Route("/settings")
 */
class FellAppSiteParametersController extends SiteParametersController
{

    /**
     * Lists all SiteParameters entities.
     *
     * @Route("/", name="fellapp_siteparameters")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:SiteParameters:site-index.html.twig")
     */
    public function indexAction(Request $request)
    {
        return $this->indexParameters($request);
    }

    /**
     * Displays a form to edit an existing SiteParameters entity.
     *
     * @Route("/{id}/edit", name="fellapp_siteparameters_edit")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:SiteParameters:edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        return $this->editParameters($request,$id);
    }

    /**
     * Edits an existing SiteParameters entity.
     *
     * @Route("/{id}", name="fellapp_siteparameters_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:SiteParameters:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request, $id);
    }


}
