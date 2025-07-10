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

namespace App\VacReqBundle\Controller;


use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\SiteParameters;
use App\VacReqBundle\Entity\VacReqSiteParameter;
use App\VacReqBundle\Form\VacReqSiteParameterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;

use App\UserdirectoryBundle\Controller\SiteParametersController;



/**
 * SiteParameters controller.
 */
#[Route(path: '/settings')]
class VacReqSiteParametersController extends SiteParametersController
{

    /**
     * Lists all SiteParameters entities.
     */
    #[Route(path: '/', name: 'vacreq_siteparameters', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/index.html.twig')]
    public function indexAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }
        return $this->indexParameters($request);
    }

    /**
     * Displays a form to edit an existing SiteParameters entity.
     */
    #[Route(path: '/{id}/edit', name: 'vacreq_siteparameters_edit', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/edit.html.twig')]
    public function editAction(Request $request,$id)
    {
        return $this->editParameters($request,$id,'ROLE_VACREQ_ADMIN');
    }

    /**
     * Edits an existing SiteParameters entity.
     */
    #[Route(path: '/{id}', name: 'vacreq_siteparameters_update', methods: ['PUT'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/edit.html.twig')]
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request,$id,'ROLE_VACREQ_ADMIN');
    }





    /**
     * VacreqSiteParameter
     */
    #[Route(path: '/specific-site-parameters/edit-page/', name: 'vacreq_siteparameters_edit_specific_site_parameters', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/SiteParameter/edit.html.twig')]
    public function vacreqSiteParameterEditAction( Request $request ) {

        //exit('vacreqSiteParameterEditAction');

        if( false === $this->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $user = $this->getUser();
        $cycle = "edit";

        $vacreqSiteParameter = $this->getOrCreateNewVacReqParameters();
        //echo "vacreqSiteParameter=".$vacreqSiteParameter->getId()."<br>";

        $form = $this->createVacreqSiteParameterForm($vacreqSiteParameter,$cycle);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();

            $em->getRepository(Document::class)->processDocuments($vacreqSiteParameter, 'travelIntakePdf');

            //exit('submit');
            $em->persist($vacreqSiteParameter);
            $em->flush();

            return $this->redirect($this->generateUrl('vacreq_siteparameters'));
        }

        return array(
            'entity' => $vacreqSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'user' => $user,
            'title' => "Update Vacation Request Specific Site Parameters"
        );
    }

    /**
     * VacreqSiteParameter Show
     */
    #[Route(path: '/specific-site-parameters/show/', name: 'vacreq_siteparameters_show_specific_site_parameters', methods: ['GET'])]
    #[Template('AppVacReqBundle/SiteParameter/edit-content.html.twig')]
    public function vacreqSiteParameterShowAction( Request $request ) {

        if( false === $this->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $user = $this->getUser();
        $cycle = "show";

        $vacreqSiteParameter = $this->getOrCreateNewVacReqParameters();
        //echo "vacreqSiteParameter=".$vacreqSiteParameter->getId()."<br>";

        $form = $this->createVacreqSiteParameterForm($vacreqSiteParameter,$cycle);

        return array(
            'entity' => $vacreqSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'user' => $user,
            'title' => "Vacation Request Specific Site Parameters"
        );
    }

    public function createVacreqSiteParameterForm($entity, $cycle) {
        $user = $this->getUser();
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

        $form = $this->createForm(VacReqSiteParameterType::class, $entity, array(
            'form_custom_value' => $params,
            'disabled' => $disabled
        ));

        return $form;
    }

    //Get or Create a new VacreqSiteParameter
    public function getOrCreateNewVacReqParameters() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository(SiteParameters::class)->findAll();
        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }
        $siteParameters = $entities[0];

        $vacreqSiteParameter = $siteParameters->getVacreqSiteParameter();

        //create one VacreqSiteParameter
        if( !$vacreqSiteParameter ) {
            //echo "VacreqSiteParameter null <br>";
            $vacreqSiteParameter = new VacReqSiteParameter();
            $siteParameters->setVacreqSiteParameter($vacreqSiteParameter);
            $em->flush();
        }

        return $vacreqSiteParameter;
    }


}
