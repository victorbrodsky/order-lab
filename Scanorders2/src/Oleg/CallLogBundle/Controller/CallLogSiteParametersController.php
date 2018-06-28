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

namespace Oleg\CallLogBundle\Controller;


use Oleg\CallLogBundle\Entity\CalllogSiteParameter;
use Oleg\CallLogBundle\Form\CalllogSiteParameterType;
use Oleg\UserdirectoryBundle\Entity\SiteParameters;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\UserdirectoryBundle\Controller\SiteParametersController;



/**
 * SiteParameters controller.
 *
 * @Route("/settings")
 */
class CallLogSiteParametersController extends SiteParametersController
{

    /**
     * Lists all SiteParameters entities.
     *
     * @Route("/", name="calllog_siteparameters")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:SiteParameters:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        ////////// Create a new CalllogSiteParameter //////////
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();
        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }
        $siteParameters = $entities[0];

        //create one CalllogSiteParameter
        if( !$siteParameters->getCalllogSiteParameter() ) {
            //echo "CalllogSiteParameter null <br>";
            $calllogSiteParameter = new CalllogSiteParameter();
            $siteParameters->setCalllogSiteParameter($calllogSiteParameter);
            $em->flush();
        }
        ////////// EOF Create a new CalllogSiteParameter //////////

        return $this->indexParameters($request);
    }

    /**
     * Displays a form to edit an existing SiteParameters entity.
     *
     * @Route("/{id}/edit", name="calllog_siteparameters_edit")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:SiteParameters:edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        return $this->editParameters($request,$id,'ROLE_CALLLOG_PATHOLOGY_ATTENDING');
    }

    /**
     * Edits an existing SiteParameters entity.
     *
     * @Route("/{id}", name="calllog_siteparameters_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:SiteParameters:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request,$id,'ROLE_CALLLOG_PATHOLOGY_ATTENDING');
    }


    /**
     * Resources page
     *
     * @Route("/edit-resources/", name="calllog_siteparameters_resources_edit")
     * @Method("GET")
     * @Template("OlegCallLogBundle:SiteParameters:edit.html.twig")
     */
    public function editResourcesAction( Request $request )
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_PATHOLOGY_ATTENDING') ) {
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }

        $entity = $entities[0];

        return $this->redirect($this->generateUrl('calllog_siteparameters_edit', array('id'=>$entity->getId(),'param'=>'calllogResources')));
    }


    /**
     * CalllogSiteParameter
     *
     * @Route("/specific-site-parameters/edit/", name="calllog_siteparameters_edit_specific_site_parameters")
     * @Method({"GET", "POST"})
     * @Template("OlegCallLogBundle:SiteParameter:edit.html.twig")
     */
    public function calllogSiteParameterEditAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_PATHOLOGY_ATTENDING') ) {
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $cycle = "edit";

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();
        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }
        $siteParameters = $entities[0];
        $calllogSiteParameter = $siteParameters->getCalllogSiteParameter();
        echo "calllogSiteParameter=".$calllogSiteParameter->getId()."<br>";
        if( !$calllogSiteParameter ) {
            exit("no calllogSiteParameter");
        }

        $form = $this->createCalllogSiteParameterForm($calllogSiteParameter,$cycle);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            //exit('submit');
            $em->persist($calllogSiteParameter);
            $em->flush();

            return $this->redirect($this->generateUrl('calllog_siteparameters'));
        }

        return array(
            'entity' => $calllogSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Update Call Log Specific Site Parameters"
        );
    }

    /**
     * CalllogSiteParameter Show
     *
     * @Route("/specific-site-parameters/show/", name="calllog_siteparameters_show_specific_site_parameters")
     * @Method("GET")
     * @Template("OlegCallLogBundle:SiteParameter:edit-content.html.twig")
     */
    public function calllogSiteParameterShowAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_PATHOLOGY_ATTENDING') ) {
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $cycle = "show";

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();
        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }
        $siteParameters = $entities[0];
        $calllogSiteParameter = $siteParameters->getCalllogSiteParameter();

        $form = $this->createCalllogSiteParameterForm($calllogSiteParameter,$cycle);

        return array(
            'entity' => $calllogSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Call Log Specific Site Parameters"
        );
    }

    public function createCalllogSiteParameterForm($entity, $cycle) {
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

        $form = $this->createForm(CalllogSiteParameterType::class, $entity, array(
            'form_custom_value' => $params,
            'disabled' => $disabled
        ));

        return $form;
    }
    
}
