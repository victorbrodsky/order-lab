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

use App\TranslationalResearchBundle\Entity\AntibodyList;
use App\TranslationalResearchBundle\Form\AntibodyType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AntibodyController extends OrderAbstractController
{
    //Custom Antibody list
    #[Route(path: '/list/antibodies/', name: 'antibodies-list_translationalresearch', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Request/antibodies.html.twig')]
    public function indexAntibodiesAction(Request $request)
    {
        if(
            false == $this->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN') &&
            false === $this->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }
        
        $listArr = $this->getList($request);
        $listArr['title'] = "Antibodies";
        $listArr['postPath'] = "_translationalresearch";

        return $listArr;
    }

    #[Route(path: '/antibody/new', name: 'translationalresearch_antibody_new', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Antibody/new.html.twig')]
    public function newAction(Request $request)
    {
        if(
            false == $this->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $user = $this->getUser();
        $cycle = "new";

        $antibody = new AntibodyList($user);

        $form = $this->createAntibodyForm($antibody,$cycle); //new

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            exit('antibody new');

            $msg = "Create new antibody";

            $this->addFlash(
                'notice',
                $msg
            );

            return $this->redirectToRoute('translationalresearch_antibody_show', array('id' => $antibody->getId()));
        }//$form->isSubmitted()

        return array(
            'antibody' => $antibody,
            'form' => $form->createView(),
            'title' => "New Antibody",
            'cycle' => $cycle
        );
    }


    #[Route(path: '/antibody/edit/{id}', name: 'translationalresearch_antibody_edit', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Antibody/new.html.twig')]
    public function editAction(Request $request, AntibodyList $antibody)
    {
        if(
            false == $this->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "edit";

        $form = $this->createAntibodyForm($antibody,$cycle); //new

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            exit('antibody edit');

            $msg = "Create new antibody";

            $this->addFlash(
                'notice',
                $msg
            );

            return $this->redirectToRoute('translationalresearch_antibody_show', array('id' => $antibody->getId()));
        }//$form->isSubmitted()

        return array(
            'antibody' => $antibody,
            'form' => $form->createView(),
            'title' => "New Antibody",
            'cycle' => $cycle
        );
    }

    #[Route(path: '/antibody/show/{id}', name: 'translationalresearch_antibody_show', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Antibody/new.html.twig')]
    public function showAction(Request $request, AntibodyList $antibody)
    {
        if( false == $this->isGranted('ROLE_TRANSRES_USER') ) { //ROLE_TRANSRES_REQUESTER
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $em = $this->getDoctrine()->getManager();

        $cycle = "show";

        $form = $this->createAntibodyForm($project, $cycle, $request); //show

        return array(
            'antibody' => $antibody,
            'form' => $form->createView(),
            'title' => "New Antibody",
            'cycle' => $cycle
        );
    }


    public function createAntibodyForm( $antibody, $cycle ) {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'antibody' => $antibody,
        );

        $disabled = true;

        if( $cycle == "new" ) {
            $disabled = false;
        }

        if( $cycle == "show" ) {
            $disabled = true;
        }

        if( $cycle == "edit" ) {
            $disabled = false;
        }

        if( $cycle == "download" ) {
            $disabled = true;
        }

        $form = $this->createForm(AntibodyType::class, $antibody, array(
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));

        return $form;
    }
}
