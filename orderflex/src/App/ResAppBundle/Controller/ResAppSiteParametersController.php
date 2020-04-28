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

namespace App\ResAppBundle\Controller;

use App\ResAppBundle\Entity\ResappSiteParameter;
use App\ResAppBundle\Form\ResappSiteParameterType;
use App\UserdirectoryBundle\Controller\SiteParametersController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

use App\UserdirectoryBundle\Entity\SiteParameters;
use App\UserdirectoryBundle\Form\SiteParametersType;
use App\UserdirectoryBundle\Util\UserUtil;

/**
 * SiteParameters controller.
 *
 * @Route("/settings")
 */
class ResAppSiteParametersController extends SiteParametersController
{

    /**
     * Lists all SiteParameters entities.
     *
     * @Route("/site-settings/", name="resapp_sitesettings_siteparameters", methods={"GET"})
     * @Template("AppUserdirectoryBundle/SiteParameters/site-index.html.twig")
     */
    public function indexSiteSettingsAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }
        return $this->indexParameters($request);
    }

    /**
     * Lists all SiteParameters entities.
     *
     * @Route("/", name="resapp_siteparameters", methods={"GET"})
     * @Template("AppUserdirectoryBundle/SiteParameters/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }
        return $this->indexParameters($request);
    }

    /**
     * Displays a form to edit an existing SiteParameters entity.
     *
     * @Route("/{id}/edit", name="resapp_siteparameters_edit", methods={"GET"})
     * @Template("AppUserdirectoryBundle/SiteParameters/edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        return $this->editParameters($request,$id);
    }

    /**
     * Edits an existing SiteParameters entity.
     *
     * @Route("/{id}", name="resapp_siteparameters_update", methods={"PUT"})
     * @Template("AppUserdirectoryBundle/SiteParameters/edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request, $id);
    }


    /**
     * ResAppSiteParameter
     *
     * @Route("/specific-site-parameters/edit-page/", name="resapp_siteparameters_edit_specific_site_parameters", methods={"GET","POST"})
     * @Template("AppResAppBundle/SiteParameter/edit.html.twig")
     */
    public function resappSiteParameterEditAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $cycle = "edit";

        $resappSiteParameter = $this->getOrCreateNewResAppParameters();

        $form = $this->createResAppSiteParameterForm($resappSiteParameter,$cycle);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();

            //exit('submit');
            $em->persist($resappSiteParameter);
            $em->flush();

            return $this->redirect($this->generateUrl('resapp_siteparameters'));
        }

        return array(
            'entity' => $resappSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Update Residency Specific Site Parameters"
        );
    }

    /**
     * ResAppSiteParameter Show
     *
     * @Route("/specific-site-parameters/show/", name="resapp_siteparameters_show_specific_site_parameters", methods={"GET"})
     * @Template("AppResAppBundle/SiteParameter/edit-content.html.twig")
     */
    public function resappSiteParameterShowAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $cycle = "show";

        $resappSiteParameter = $this->getOrCreateNewResAppParameters();
        //echo "resappSiteParameter=".$resappSiteParameter->getId()."<br>";

        $form = $this->createResAppSiteParameterForm($resappSiteParameter,$cycle);

        return array(
            'entity' => $resappSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Residency Specific Site Parameters"
        );
    }

    public function createResAppSiteParameterForm($entity, $cycle) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $disabled = false;
        if( $cycle == "show" ) {
            $disabled = true;
        }

        $params = array(
            'cycle' => $cycle,
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
        );

        $form = $this->createForm(ResappSiteParameterType::class, $entity, array(
            'form_custom_value' => $params,
            'disabled' => $disabled
        ));

        return $form;
    }

    //Get or Create a new ResAppSiteParameter
    public function getOrCreateNewResAppParameters() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();
        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }
        $siteParameters = $entities[0];

        $resappSiteParameter = $siteParameters->getResappSiteParameter();

        //create one ResAppSiteParameter
        if( !$resappSiteParameter ) {
            //echo "ResAppSiteParameter null <br>";
            $resappSiteParameter = new ResappSiteParameter();
            $siteParameters->setResappSiteParameter($resappSiteParameter);
            $em->flush();
        }

        return $resappSiteParameter;
    }
    
}
