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

namespace App\TranslationalResearchBundle\Controller;

use App\TranslationalResearchBundle\Entity\SpecialtyList;
use App\TranslationalResearchBundle\Entity\WorkQueueList;
use App\UserdirectoryBundle\Controller\ListController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TransResListController extends ListController
{
    protected $sitename = "translationalresearch";
    protected $postPath = "_translationalresearch";

    /**
     * @Route("/list/translational-research-work-request-products-and-services/", name="transresrequestcategorytypes-list_translationalresearch", methods={"GET"})
     *
     * @Route("/list/translational-research-project-specialties/", name="transresprojectspecialties-list_translationalresearch", methods={"GET"})
     * @Route("/list/translational-research-project-specialties-list/", name="transresprojectspecialties-list", methods={"GET"})
     *
     * @Template("AppUserdirectoryBundle/ListForm/index.html.twig")
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
     * @Route("/list/antibodies/", name="antibodies-list_translationalresearch", methods={"GET"})
     * @Template("AppTranslationalResearchBundle/Request/antibodies.html.twig")
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
        
        $listArr = $this->getList($request);
        $listArr['title'] = "Antibodies";
        $listArr['postPath'] = "_translationalresearch";

        return $listArr;
    }


    /**
     * @Route("/list/translational-research-work-request-products-and-services/", name="transresrequestcategorytypes_create_translationalresearch", methods={"POST"})
     * @Route("/list/antibodies/", name="antibodies_create_translationalresearch", methods={"POST"})
     * @Route("/list/translational-research-project-specialties/", name="transresprojectspecialties_create_translationalresearch", methods={"POST"})
     *
     * @Template("AppUserdirectoryBundle/ListForm/new.html.twig")
     */
    public function createAction(Request $request)
    {
        //exit("trp createList");

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        return $this->createList($request);
    }

    public function postProcessList($entity) {
        
        //exit('transres post ProcessList');

        $userSecUtil = $this->get('user_security_utility');

        if( $entity instanceof SpecialtyList ) {
            //Use this only for SpecialtyList
            $userSecUtil->addTransresRolesBySpecialty($entity);
        }

        if( $entity instanceof WorkQueueList ) {
            //Use this only for SpecialtyList
            $userSecUtil->addTransresRolesByWorkQueue($entity);
        }

        //exit('transres post ProcessList');

        return NULL;
    }

    /**
     * @Route("/list/translational-research-work-request-products-and-services/new", name="transresrequestcategorytypes_new_translationalresearch", methods={"GET"})
     * @Route("/list/antibodies/new", name="antibodies_new_translationalresearch", methods={"GET"})
     *
     * @Route("/list/translational-research-project-specialties/new", name="transresprojectspecialties_new_translationalresearch", methods={"GET"})
     * @Route("/list/translational-research-project-specialties-new/new", name="transresprojectspecialties_new", methods={"GET"})
     *
     * @Template("AppUserdirectoryBundle/ListForm/new.html.twig")
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
     * ("/list/translational-research-project-specialties/{id}", name="transresprojectspecialties_show_translationalresearch", methods={"GET"})
     *
     * @Route("/list/translational-research-work-request-products-and-services/{id}", name="transresrequestcategorytypes_show_translationalresearch", methods={"GET"})
     * @Route("/list/antibodies/{id}", name="antibodies_show_translationalresearch", methods={"GET"})
     * @Route("/list/translational-research-project-specialties/{id}", name="transresprojectspecialties_show_translationalresearch", methods={"GET"})
     * @Route("/list/translational-research-project-specialties-show/{id}", name="transresprojectspecialties_show", methods={"GET"})
     *
     * @Template("AppUserdirectoryBundle/ListForm/show.html.twig")
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
     * @Route("/list/translational-research-work-request-products-and-services/{id}/edit", name="transresrequestcategorytypes_edit_translationalresearch", methods={"GET"})
     * @Route("/list/antibodies/{id}/edit", name="antibodies_edit_translationalresearch", methods={"GET"})
     *
     * @Route("/list/translational-research-project-specialties/{id}/edit", name="transresprojectspecialties_edit_translationalresearch", methods={"GET"})
     * @Route("/list/translational-research-project-specialties-edit/{id}/edit", name="transresprojectspecialties_edit", methods={"GET"})
     *
     * @Template("AppUserdirectoryBundle/ListForm/edit.html.twig")
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
     * @Route("/list/translational-research-work-request-products-and-services/{id}", name="transresrequestcategorytypes_update_translationalresearch", methods={"PUT"})
     * @Route("/list/antibodies/{id}", name="antibodies_update_translationalresearch", methods={"PUT"})
     * @Route("/list/translational-research-project-specialties/{id}", name="transresprojectspecialties_update_translationalresearch", methods={"PUT"})
     *
     * @Template("AppUserdirectoryBundle/ListForm/edit.html.twig")
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
