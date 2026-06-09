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

namespace App\CtpBundle\Controller;

use App\CtpBundle\Entity\CtpSiteParameter;
use App\CtpBundle\Form\CtpSiteParameterType;
use App\UserdirectoryBundle\Controller\SiteParametersController;
use App\UserdirectoryBundle\Entity\Document;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/settings')]
class CtpSiteParametersController extends SiteParametersController
{
    #[Route(path: '/site-settings/', name: 'ctp_sitesettings_siteparameters', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/site-index.html.twig')]
    public function indexSiteSettingsAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_CTP_ADMIN') ) {
            return $this->redirect($this->generateUrl('ctp-nopermission'));
        }
        return $this->indexParameters($request);
    }

    #[Route(path: '/', name: 'ctp_siteparameters', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/index.html.twig')]
    public function indexAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_CTP_ADMIN') ) {
            return $this->redirect($this->generateUrl('ctp-nopermission'));
        }
        return $this->indexParameters($request);
    }

    #[Route(path: '/{id}/edit', name: 'ctp_siteparameters_edit', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/edit.html.twig')]
    public function editAction(Request $request, $id)
    {
        return $this->editParameters($request, $id, 'ROLE_CTP_ADMIN');
    }

    #[Route(path: '/{id}', name: 'ctp_siteparameters_update', methods: ['PUT'])]
    #[Template('AppUserdirectoryBundle/SiteParameters/edit.html.twig')]
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request, $id, 'ROLE_CTP_ADMIN');
    }

    #[Route(path: '/specific-site-parameters/show/', name: 'ctp_siteparameters_show_specific_site_parameters', methods: ['GET'])]
    #[Template('AppCtpBundle/SiteParameter/edit-content.html.twig')]
    public function ctpSiteParameterShowAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_CTP_ADMIN') ) {
            return $this->redirect($this->generateUrl('ctp-nopermission'));
        }

        $cycle = 'show';
        $ctpSiteParameter = $this->getOrCreateNewCtpParameters();
        $form = $this->createCtpSiteParameterForm($ctpSiteParameter, $cycle);

        return [
            'entity' => $ctpSiteParameter,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => 'Center for Translational Pathology Specific Site Parameters',
        ];
    }

    #[Route(path: '/specific-site-parameters/edit-page/', name: 'ctp_siteparameters_edit_specific_site_parameters', methods: ['GET', 'POST'])]
    #[Template('AppCtpBundle/SiteParameter/edit.html.twig')]
    public function ctpSiteParameterEditAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_CTP_ADMIN') ) {
            return $this->redirect($this->generateUrl('ctp-nopermission'));
        }

        $cycle = 'edit';
        $ctpSiteParameter = $this->getOrCreateNewCtpParameters();
        $form = $this->createCtpSiteParameterForm($ctpSiteParameter, $cycle);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();
            $em->getRepository(Document::class)->processDocuments($ctpSiteParameter, 'ctpLogo');
            $em->persist($ctpSiteParameter);
            $em->flush();

            return $this->redirect($this->generateUrl('ctp_siteparameters'));
        }

        return [
            'entity' => $ctpSiteParameter,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => 'Update Center for Translational Pathology Specific Site Parameters',
        ];
    }

    public function createCtpSiteParameterForm($entity, $cycle)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $disabled = false;
        if( $cycle == 'show' ) {
            $disabled = true;
        }

        $params = [
            'cycle' => $cycle,
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
        ];

        return $this->createForm(CtpSiteParameterType::class, $entity, [
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ]);
    }

    public function getOrCreateNewCtpParameters()
    {
        $em = $this->getDoctrine()->getManager();

        $ctpSiteParameter = $em->getRepository(CtpSiteParameter::class)->findOneBy([], ['id' => 'ASC']);

        if( !$ctpSiteParameter ) {
            $ctpSiteParameter = new CtpSiteParameter();
            $em->persist($ctpSiteParameter);
            $em->flush();
        }

        return $ctpSiteParameter;
    }
}
