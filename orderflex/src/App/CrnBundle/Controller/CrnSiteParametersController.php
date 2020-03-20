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

namespace App\CrnBundle\Controller;


use App\CrnBundle\Entity\CrnSiteParameter;
use App\CrnBundle\Form\CrnSiteParameterType;
use App\UserdirectoryBundle\Entity\SiteParameters;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

use App\UserdirectoryBundle\Controller\SiteParametersController;



/**
 * SiteParameters controller.
 *
 * @Route("/settings")
 */
class CrnSiteParametersController extends SiteParametersController
{

    /**
     * Lists all SiteParameters entities.
     *
     * @Route("/", name="crn_siteparameters", methods={"GET"})
     * @Template("AppUserdirectoryBundle/SiteParameters/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        //exit("crn indexAction");

        $this->getOrCreateNewCrnParameters();

        return $this->indexParameters($request);
    }

    /**
     * Displays a form to edit an existing SiteParameters entity.
     *
     * @Route("/{id}/edit", name="crn_siteparameters_edit", methods={"GET"})
     * @Template("AppUserdirectoryBundle/SiteParameters/edit.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        //exit("crn editAction id=".$id);
        return $this->editParameters($request,$id,'ROLE_CRN_PATHOLOGY_ATTENDING');
    }

    /**
     * Edits an existing SiteParameters entity.
     *
     * @Route("/{id}", name="crn_siteparameters_update", methods={"PUT"})
     * @Template("AppUserdirectoryBundle/SiteParameters/edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request,$id,'ROLE_CRN_PATHOLOGY_ATTENDING');
    }


    /**
     * Resources page
     *
     * @Route("/edit-resources/show", name="crn_siteparameters_resources_edit", methods={"GET"})
     * @Template("AppCrnBundle/SiteParameters/edit.html.twig")
     */
    public function editResourcesAction( Request $request )
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_CRN_PATHOLOGY_ATTENDING') ) {
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();

        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }

        $entity = $entities[0];

        return $this->redirect($this->generateUrl('crn_siteparameters_edit', array('id'=>$entity->getId(),'param'=>'crnResources')));
    }


    /**
     * CrnSiteParameter
     *
     * @Route("/specific-site-parameters/edit-page/", name="crn_siteparameters_edit_specific_site_parameters", methods={"GET", "POST"})
     * @Template("AppCrnBundle/SiteParameter/edit.html.twig")
     */
    public function crnSiteParameterEditAction( Request $request ) {

        //exit('crnSiteParameterEditAction');

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_CRN_PATHOLOGY_ATTENDING') ) {
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        $cycle = "edit";

        $crnSiteParameter = $this->getOrCreateNewCrnParameters();
        //echo "crnSiteParameter=".$crnSiteParameter->getId()."<br>";

        $form = $this->createCrnSiteParameterForm($crnSiteParameter,$cycle);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();

            //exit('submit');
            $em->persist($crnSiteParameter);
            $em->flush();

            return $this->redirect($this->generateUrl('crn_siteparameters'));
        }

        return array(
            'entity' => $crnSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Update Critical Result Notification Specific Site Parameters"
        );
    }

    /**
     * CrnSiteParameter Show
     *
     * @Route("/specific-site-parameters/show/", name="crn_siteparameters_show_specific_site_parameters", methods={"GET"})
     * @Template("AppCrnBundle/SiteParameter/edit-content.html.twig")
     */
    public function crnSiteParameterShowAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_CRN_PATHOLOGY_ATTENDING') ) {
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        $cycle = "show";

        $crnSiteParameter = $this->getOrCreateNewCrnParameters();
        //echo "crnSiteParameter=".$crnSiteParameter->getId()."<br>";

        $form = $this->createCrnSiteParameterForm($crnSiteParameter,$cycle);

        return array(
            'entity' => $crnSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Critical Result Notification Specific Site Parameters"
        );
    }

    public function createCrnSiteParameterForm($entity, $cycle) {
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

        $form = $this->createForm(CrnSiteParameterType::class, $entity, array(
            'form_custom_value' => $params,
            'disabled' => $disabled
        ));

        return $form;
    }

    //Get or Create a new CrnSiteParameter
    public function getOrCreateNewCrnParameters() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();
        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }
        $siteParameters = $entities[0];

        $crnSiteParameter = $siteParameters->getCrnSiteParameter();

        //create one CrnSiteParameter
        if( !$crnSiteParameter ) {
            //echo "CrnSiteParameter null <br>";
            $crnSiteParameter = new CrnSiteParameter();
            $siteParameters->setCrnSiteParameter($crnSiteParameter);
            $em->flush();
        }

        return $crnSiteParameter;
    }
}
