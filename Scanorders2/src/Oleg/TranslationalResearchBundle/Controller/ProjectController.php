<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\TranslationalResearchBundle\Entity\Project;
use Oleg\TranslationalResearchBundle\Form\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $cycle = "new";

        $project = new Project($user);
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
        $cycle = "show";

        $form = $this->createProjectForm($project,$cycle);

        $deleteForm = $this->createDeleteForm($project);

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Project ID ".$project->getId(),
            'delete_form' => $deleteForm->createView(),
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

        $cycle = "edit";

        $deleteForm = $this->createDeleteForm($project);
        //$editForm = $this->createForm('Oleg\TranslationalResearchBundle\Form\ProjectType', $project);
        $editForm = $this->createProjectForm($project,$cycle);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $project->setUpdateUser($user);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
        }

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

        $disabled = false;

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'project' => $project
        );

        if( $cycle == "show" ) {
            $disabled = true;
        }

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
