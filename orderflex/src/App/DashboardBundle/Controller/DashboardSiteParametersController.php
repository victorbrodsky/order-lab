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

namespace App\DashboardBundle\Controller;


use App\DashboardBundle\Entity\DashboardSiteParameter;
use App\DashboardBundle\Form\DashboardSiteParameterType;
use App\UserdirectoryBundle\Entity\SiteParameters;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Controller\SiteParametersController;



/**
 * SiteParameters controller.
 */
#[Route(path: '/settings')]
class DashboardSiteParametersController extends SiteParametersController
{

    /**
     * Lists all SiteParameters entities.
     */
    #[Route(path: '/', name: 'dashboard_siteparameters', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/index.html.twig')]
    public function indexAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('dashboard-nopermission') );
        }
        return $this->indexParameters($request);
    }

    /**
     * Displays a form to edit an existing SiteParameters entity.
     */
    #[Route(path: '/{id}/edit', name: 'dashboard_siteparameters_edit', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/edit.html.twig')]
    public function editAction(Request $request,$id)
    {
        return $this->editParameters($request,$id);
    }

    /**
     * Edits an existing SiteParameters entity.
     */
    #[Route(path: '/{id}', name: 'dashboard_siteparameters_update', methods: ['PUT'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/edit.html.twig')]
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request, $id);
    }


    /**
     * DashboardSiteParameter Show
     */
    #[Route(path: '/specific-site-parameters/show/', name: 'dashboard_siteparameters_show_specific_site_parameters', methods: ['GET'])]
    #[Template('AppDashboardBundle/SiteParameter/edit-content.html.twig')]
    public function dashboardSiteParameterShowAction( Request $request ) {

        if( false === $this->isGranted('ROLE_DASHBOARD_ADMIN') ) {
            return $this->redirect( $this->generateUrl('dashboard-nopermission') );
        }

        $cycle = "show";

        $dashboardSiteParameter = $this->getOrCreateNewDashboardParameters($cycle);
        //echo "dashboardSiteParameter=".$dashboardSiteParameter->getId()."<br>";

        $form = $this->createDashboardSiteParameterForm($dashboardSiteParameter,$cycle);

        return array(
            'entity' => $dashboardSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Dashboard Specific Site Parameters"
        );
    }

    #[Route(path: '/specific-site-parameters/edit-page/', name: 'dashboard_siteparameters_edit_specific_site_parameters', methods: ['GET', 'POST'])]
    #[Template('AppDashboardBundle/SiteParameter/edit.html.twig')]
    public function dashboardSiteParameterEditAction( Request $request ) {

        //exit('dashboardSiteParameterEditAction');

        if( false === $this->isGranted('ROLE_DASHBOARD_ADMIN') ) {
            return $this->redirect( $this->generateUrl('dashboard-nopermission') );
        }

        $cycle = "edit";

        $dashboardSiteParameter = $this->getOrCreateNewDashboardParameters($cycle);
        //echo "dashboardSiteParameter=".$dashboardSiteParameter->getId()."<br>";

        $form = $this->createDashboardSiteParameterForm($dashboardSiteParameter,$cycle);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();

            //exit('submit');
            $em->persist($dashboardSiteParameter);
            $em->flush();

            return $this->redirect($this->generateUrl('dashboard_siteparameters'));
        }

        return array(
            'entity' => $dashboardSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Update Dashboard Specific Site Parameters"
        );
    }

    public function createDashboardSiteParameterForm($entity, $cycle) {
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

        $form = $this->createForm(DashboardSiteParameterType::class, $entity, array(
            'form_custom_value' => $params,
            'disabled' => $disabled
        ));

        return $form;
    }

    //Get or Create a new DashboardSiteParameter
    public function getOrCreateNewDashboardParameters( $cycle ) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository(SiteParameters::class)->findAll();
        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }
        $siteParameters = $entities[0];

        $dashboardSiteParameter = $siteParameters->getDashboardSiteParameter();

        //create one DashboardSiteParameter
        if( !$dashboardSiteParameter ) {
            //echo "DashboardSiteParameter null <br>";
            $dashboardSiteParameter = new DashboardSiteParameter();

            $siteParameters->setDashboardSiteParameter($dashboardSiteParameter);
            $em->flush();
        }

        return $dashboardSiteParameter;
    }

}
