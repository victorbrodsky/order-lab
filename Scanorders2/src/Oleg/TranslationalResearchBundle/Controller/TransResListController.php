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

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\UserdirectoryBundle\Controller\ListController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class TransResListController extends ListController
{
    protected $sitename = "translationalresearch";
    protected $postPath = "_translationalresearch";

    //     * @Route("/list/antibodies/", name="antibodies-list_translationalresearch")
    /**
     * @Route("/list/translational-research-request-category-types/", name="transresrequestcategorytypes-list_translationalresearch")
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        return $this->getList($request);
    }

    //Custom Antibody list
    /**
     * @Route("/list/antibodies/", name="antibodies-list_translationalresearch")
     *
     * @Method("GET")
     * @Template("OlegTranslationalResearchBundle:Request:antibodies.html.twig")
     */
    public function indexAntibodiesAction(Request $request)
    {
        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        return $this->getList($request);
    }


    /**
     * @Route("/list/translational-research-request-category-types/", name="transresrequestcategorytypes_create_translationalresearch")
     * @Route("/list/antibodies/", name="antibodies_create_translationalresearch")
     *
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:ListForm:new.html.twig")
     */
    public function createAction(Request $request)
    {
        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        return $this->createList($request);
    }

    /**
     * @Route("/list/translational-research-request-category-types/new", name="transresrequestcategorytypes_new_translationalresearch")
     * @Route("/list/antibodies/new", name="antibodies_new_translationalresearch")
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:new.html.twig")
     */
    public function newAction(Request $request)
    {
        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        return $this->newList($request);
    }

    /**
     * @Route("/list/translational-research-request-category-types/{id}", name="transresrequestcategorytypes_show_translationalresearch")
     * @Route("/list/antibodies/{id}", name="antibodies_show_translationalresearch")
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:show.html.twig")
     */
    public function showAction(Request $request,$id)
    {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        return $this->showList($request,$id,true);
    }

    /**
     * @Route("/list/translational-research-request-category-types/{id}/edit", name="transresrequestcategorytypes_edit_translationalresearch")
     * @Route("/list/antibodies/{id}/edit", name="antibodies_edit_translationalresearch")
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ListForm:edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        return $this->editList($request,$id);
    }

    /**
     * @Route("/list/translational-research-request-category-types/{id}", name="transresrequestcategorytypes_update_translationalresearch")
     * @Route("/list/antibodies/{id}", name="antibodies_update_translationalresearch")
     *
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:ListForm:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        return $this->updateList($request,$id);
    }
}
