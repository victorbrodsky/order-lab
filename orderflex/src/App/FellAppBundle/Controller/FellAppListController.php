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

namespace App\FellAppBundle\Controller;

use App\UserdirectoryBundle\Controller\ListController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class FellAppListController extends ListController
{
    protected $sitename = "fellapp";
    protected $postPath = "_fellapp";

    /**
     * @Route("/list/visa-status/", name="visastatus-list_fellapp")
     *
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/ListForm/index.html.twig")
     */
    public function indexVisaStatusesAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect($this->generateUrl('fellapp-nopermission'));
        }

        return $this->getList($request);
    }

    /**
     * @Route("/list/visa-status/", name="visastatus_create_fellapp")
     *
     * @Method("POST")
     * @Template("AppUserdirectoryBundle/ListForm/new.html.twig")
     */
    public function createAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect($this->generateUrl('fellapp-nopermission'));
        }

        return $this->createList($request);
    }

    /**
     * @Route("/list/visa-status/new", name="visastatus_new_fellapp")
     *
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/ListForm/new.html.twig")
     */
    public function newAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect($this->generateUrl('fellapp-nopermission'));
        }

        return $this->newList($request);
    }

    /**
     * @Route("/list/visa-status/{id}", name="visastatus_show_fellapp")
     *
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/ListForm/show.html.twig")
     */
    public function showAction(Request $request,$id)
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect($this->generateUrl('fellapp-nopermission'));
        }

        return $this->showList($request,$id,true);
    }

    /**
     * @Route("/list/visa-status/{id}/edit", name="visastatus_edit_fellapp")
     *
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/ListForm/edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect($this->generateUrl('fellapp-nopermission'));
        }

        return $this->editList($request,$id);
    }

    /**
     * @Route("/list/visa-status/{id}", name="visastatus_update_fellapp")
     *
     * @Method("PUT")
     * @Template("AppUserdirectoryBundle/ListForm/edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect($this->generateUrl('fellapp-nopermission'));
        }

        return $this->updateList($request,$id);
    }


}
