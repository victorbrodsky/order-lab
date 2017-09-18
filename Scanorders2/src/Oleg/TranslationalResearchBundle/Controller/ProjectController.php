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

//use Graphp\GraphViz\GraphViz;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\TranslationalResearchBundle\Entity\Project;
use Oleg\TranslationalResearchBundle\Form\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Dumper\GraphvizDumper;
use Symfony\Component\Workflow\Transition;

/**
 * Project controller.
 *
 * @Route("project")
 */
class ProjectController extends Controller
{

    /**
     * @Route("/home/", name="translationalresearch_home")
     * @Method("GET")
     */
    public function homeAction()
    {
        return $this->redirectToRoute('translationalresearch_project_index');
    }

    /**
     * Lists all project entities.
     *
     * @Route("/", name="translationalresearch_project_index")
     * @Template("OlegTranslationalResearchBundle:Project:index.html.twig")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //$projects = $em->getRepository('OlegTranslationalResearchBundle:Project')->findAll();

        $repository = $em->getRepository('OlegTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->leftJoin('project.principalInvestigators','principalInvestigators');
        $dql->leftJoin('principalInvestigators.infos','principalInvestigatorsInfos');

        $limit = 30;
        $query = $em->createQuery($dql);

        $paginationParams = array(
            'defaultSortFieldName' => 'project.id',
            'defaultSortDirection' => 'DESC'
        );

        $paginator  = $this->get('knp_paginator');
        $projects = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                                         /*limit per page*/
            $paginationParams
        );

        return array(
            'projects' => $projects,
            'title' => "Projects"
        );
    }

    /**
     * Creates a new project entity.
     *
     * @Route("/new", name="translationalresearch_project_new")
     * @Template("OlegTranslationalResearchBundle:Project:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $transresUtil = $this->container->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $cycle = "new";

        $project = new Project($user);

//        $defaultReviewersAdded = false;
//        if(
//            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
//            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
//            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
//        ) {
//            //add all default reviewers
//            $transresUtil->addDefaultStateReviewers($project);
//            $defaultReviewersAdded = true;
//        }

        //new: add all default reviewers
        $transresUtil->addDefaultStateReviewers($project);

        //$form = $this->createForm('Oleg\TranslationalResearchBundle\Form\ProjectType', $project);
        $form = $this->createProjectForm($project,$cycle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
        }

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Create Project"
        );
    }

    /**
     * Finds and displays a project entity.
     *
     * @Route("/{id}", name="translationalresearch_project_show")
     * @Template("OlegTranslationalResearchBundle:Project:show.html.twig")
     * @Method("GET")
     */
    public function showAction(Project $project)
    {
        $transresUtil = $this->container->get('transres_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $cycle = "show";

        $form = $this->createProjectForm($project,$cycle);

        $deleteForm = $this->createDeleteForm($project);

        //create a review form (for example, IrbReview form if logged in user is a reviewer or reviewer delegate)
        //1) if project is in the review state: irb_review, admin_review, committee_review or final_approval
        //2) if the current user is added to this project as the reviewer for the state above
        //$reviewFormViews = $transresUtil->getReviewForm($project,$user);

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Project ID ".$project->getId(),
            'delete_form' => $deleteForm->createView(),
            //'review_forms' => $reviewFormViews
        );

//        return array(
//            'project' => $project,
//            'cycle' => 'show',
//            'delete_form' => $deleteForm->createView(),
//            'title' => "Project ID ".$project->getId()
//        );
    }

    /**
     * Displays a form to edit an existing project entity.
     *
     * @Route("/{id}/edit", name="translationalresearch_project_edit")
     * @Template("OlegTranslationalResearchBundle:Project:edit.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Project $project)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');
        $em = $this->getDoctrine()->getManager();

        $cycle = "edit";

        //edit: add all default reviewers
        $transresUtil->addDefaultStateReviewers($project);

        ///////////// get originals /////////////
        //IRB Reviews
        $originalIrbReviews = new ArrayCollection();
        foreach ($project->getIrbReviews() as $review) {
            $originalIrbReviews->add($review);
        }
        //Admin Reviews
        $originalAdminReviews = new ArrayCollection();
        foreach ($project->getAdminReviews() as $review) {
            $originalAdminReviews->add($review);
        }
        //Committee Reviews
        $originalCommitteeReviews = new ArrayCollection();
        foreach ($project->getCommitteeReviews() as $review) {
            $originalCommitteeReviews->add($review);
        }
        //Final Reviews
        $originalFinalReviews = new ArrayCollection();
        foreach ($project->getFinalReviews() as $review) {
            $originalFinalReviews->add($review);
        }
        ///////////// EOF get originals /////////////


        $deleteForm = $this->createDeleteForm($project);
        //$editForm = $this->createForm('Oleg\TranslationalResearchBundle\Form\ProjectType', $project);
        $editForm = $this->createProjectForm($project,$cycle);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $project->setUpdateUser($user);


            //////////// remove the relationship between the review and the project ////////////
//            //IRB Reviews
//            foreach ($originalIrbReviews as $review) {
//                if (false === $project->IrbReviews()->contains($review)) {
//                    // remove the Task from the Tag
//                    $project->IrbReviews()->removeElement($review);
//
//                    // if it was a many-to-one relationship, remove the relationship like this
//                    $review->setProject(null);
//
//                    $em->persist($review);
//
//                    // if you wanted to delete the Tag entirely, you can also do that
//                    $em->remove($review);
//                }
//            }
            $transresUtil->removeReviewsFromProject($project,$originalIrbReviews,$project->getIrbReviews());
            $transresUtil->removeReviewsFromProject($project,$originalAdminReviews,$project->getAdminReviews());
            $transresUtil->removeReviewsFromProject($project,$originalCommitteeReviews,$project->getCommitteeReviews());
            $transresUtil->removeReviewsFromProject($project,$originalFinalReviews,$project->getFinalReviews());
            //////////// EOF remove the relationship between the review and the project ////////////

            //testing
//            $irbs = $project->getIrbReviews();
//            echo "irbs count=".count($irbs)."<br>";
//            foreach($irbs as $irb) {
//                echo "irb=".$irb->getReviewer()."<br>";
//            }
            //exit('exit');

            $em->flush();

            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
        }

//        echo "irbReview count=".count($project->getIrbReviews())."<br>";
//        foreach($project->getIrbReviews() as $review){
//            echo "reviewer=".$review->getReviewer()->getUsernameOptimal()."<br>";
//        }

        return array(
            'project' => $project,
            'edit_form' => $editForm->createView(),
            'cycle' => $cycle,
            'delete_form' => $deleteForm->createView(),
            'title' => "Edit Project ID ".$project->getId()
        );
    }

    /**
     * Deletes a project entity.
     *
     * @Route("/{id}", name="translationalresearch_project_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Project $project)
    {
        $form = $this->createDeleteForm($project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($project);
            $em->flush();
        }

        return $this->redirectToRoute('translationalresearch_project_index');
    }

    private function createProjectForm( Project $project, $cycle )
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');

        $disabled = false;

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'SecurityAuthChecker' => $this->get('security.authorization_checker'),
            'project' => $project
        );

        if( $cycle == "show" ) {
            $disabled = true;
        }

        $params['admin'] = false;
        $params['showIrbReviews'] = false;
        $params['showAdminReviews'] = false;
        $params['showCommitteeReviews'] = false;
        $params['showFinalReviews'] = false;
        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
        ) {
            $params['admin'] = true;
            $params['showIrbReviews'] = true;
            $params['showAdminReviews'] = true;
            $params['showCommitteeReviews'] = true;
            $params['showFinalReviews'] = true;
        }

        //show if owner
        if( $transresUtil->hasProjectReviewer($user,$project->getIrbReviews()) ) {
            $params['showIrbReviews'] = true;
        }
        if( $transresUtil->hasProjectReviewer($user,$project->getAdminReviews()) ) {
            $params['showAdminReviews'] = true;
        }
        if( $transresUtil->hasProjectReviewer($user,$project->getCommitteeReviews()) ) {
            $params['showCommitteeReviews'] = true;
        }
        if( $transresUtil->hasProjectReviewer($user,$project->getFinalReviews()) ) {
            $params['showFinalReviews'] = true;
        }

        //check if reviewer
//        $params['reviewer'] = false;
//        if(  ) {
//
//        }

        $form = $this->createForm(ProjectType::class, $project, array(
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));

        return $form;
    }

    /**
     * Creates a form to delete a project entity.
     *
     * @param Project $project The project entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Project $project)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('translationalresearch_project_delete', array('id' => $project->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

}
