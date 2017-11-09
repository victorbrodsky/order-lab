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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 9/26/2017
 * Time: 4:49 PM
 */

namespace Oleg\TranslationalResearchBundle\Controller;


use Doctrine\Common\Collections\ArrayCollection;
use Oleg\TranslationalResearchBundle\Entity\Project;
use Oleg\TranslationalResearchBundle\Entity\SpecialtyList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


/**
 * Project FormNode controller.
 */
class ProjectFormNodeController extends ProjectController
{

    /**
     * Creates a new project entity with formnode.
     *
     * @Route("/project/new", name="translationalresearch_project_new_selector")
     * @Template("OlegTranslationalResearchBundle:Project:new-project-selector.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newProjectSelectorAction(Request $request)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $specialties = $transresUtil->getTransResProjectSpecialties();

        return array(
            'specialties' => $specialties,
            'title' => "New Project Selector"
        );
    }


    /**
     * Creates a new project entity with formnode.
     *
     * @Route("/project/new/{specialtyStr}", name="translationalresearch_project_new")
     * @Template("OlegTranslationalResearchBundle:Project:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newFormNodeAction(Request $request, $specialtyStr)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $cycle = "new";

        //$specialty is a url prefix (i.e. "new-ap-cp-project")
//        $specialtyAbbreviation = SpecialtyList::getProjectAbbreviationFromUrlPrefix($specialty);
//        if( !$specialtyAbbreviation ) {
//            throw new \Exception( "Project specialty abbreviation is not found by name '".$specialty."'" );
//        }
//        $specialty = $em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->findOneByAbbreviation($specialtyAbbreviation);
//        if( !$specialty ) {
//            throw new \Exception( "Project specialty is not found by name '".$specialtyAbbreviation."'" );
//        }
        //$specialty is a url prefix (i.e. "new-ap-cp-project")
        $specialty = $transresUtil->getSpecialtyObject($specialtyStr);

        $testing = false;
        //$testing = true;

//        $project = new Project($user);
//        $project->setVersion(1);
//
//        $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName('Pathology and Laboratory Medicine');
//        $project->setInstitution($institution);
//
//        //set order category
//        $categoryStr = "HemePath Translational Research Project";  //"Pathology Call Log Entry";
//        //$categoryStr = "Nesting Test"; //testing
//        $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
//
//        if( !$messageCategory ) {
//            throw new \Exception( "Message category is not found by name '".$categoryStr."'" );
//        }
//        $project->setMessageCategory($messageCategory);

        $project = $this->createProjectEntity($user,null);

        $project->setProjectSpecialty($specialty);

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

        $form = $this->createProjectForm($project,$cycle,$request);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($project);
//            $em->flush();
//
//            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
//        }

        $messageTypeId = true;//testing
        $formnodetrigger = 1;
        if( $messageTypeId ) {
            $formnodetrigger = 0; //build formnodes from top to bottom
        }

        //top message category id
        $formnodeTopHolderId = null;
        //$categoryStr = "Pathology Call Log Entry";
        //$messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
        $messageCategory = $project->getMessageCategory();
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //exit("Project submitted");

            //new
            if ($form->getClickedButton() && 'saveAsDraft' === $form->getClickedButton()->getName()) {
                //Save Project as Draft => state='draft'
                $project->setState('draft');
            }

            //new
            if ($form->getClickedButton() && 'saveAsComplete' === $form->getClickedButton()->getName()) {
                //Complete Submission => state='submit'
                $project->setState('completed');
            }

            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($project);

            if( !$testing ) {
                $em->persist($project);
                $em->flush();

                $project->generateOid();
                $em->flush();
            }

            //process form nodes
            $formNodeUtil = $this->get('user_formnode_utility');
            $formNodeUtil->processFormNodes($request,$project->getMessageCategory(),$project,$testing); //testing

            $msg = "Project with ID ".$project->getOid()." has been successfully created";

            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Project Created";
            $msg = $msg . " by ".$project->getSubmitter()->getUsernameOptimal();
            $transresUtil->setEventLog($project,$eventType,$msg,$testing);

            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
        }

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Create Project",
            'formnodetrigger' => $formnodetrigger,
            'formnodeTopHolderId' => $formnodeTopHolderId
        );
    }










    /**
     * Get Project Edit page
     * Originally edit form generates a new entity Project with new id and same oid.
     *
     * @Route("/project/edit/{id}", name="translationalresearch_project_edit")
     * @Template("OlegTranslationalResearchBundle:Project:edit.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Project $project)
    {

//        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER')) {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
        //TODO: ediatble by admin and requester only
        $transresUtil = $this->container->get('transres_util');

        if(
            $transresUtil->isAdminOrPrimaryReviewer() ||
            $transresUtil->isProjectEditableByRequester($project)
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$userSecUtil = $this->get('user_security_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $cycle = "edit";
        $formtype = "translationalresearch-project";

        $class = new \ReflectionClass($project);
        $className = $class->getShortName();          //ObjectTypeText
        $classNamespace = $class->getNamespaceName(); //Oleg\UserdirectoryBundle\Entity

        $testing = false;
        //$testing = true;

        $project = $this->createProjectEntity($user,$project);

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

        $form = $this->createProjectForm($project,$cycle,$request);

        $messageTypeId = true;//testing
        $formnodetrigger = 1;
        if( $messageTypeId ) {
            $formnodetrigger = 0; //build formnodes from top to bottom
        }

        //top message category id
        $formnodeTopHolderId = null;
        //$categoryStr = "Pathology Call Log Entry";
        $messageCategory = $project->getMessageCategory();
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //exit("Project update submitted");

            $project->setUpdateUser($user);

            $startProjectReview = false;

            $originalStateStr = $project->getState();
            $originalStateLabel = $transresUtil->getStateLabelByName($originalStateStr);

            $msg = "Project ID ".$project->getOid() ." has been successfully updated";

            //////////// remove the relationship between the review and the project ////////////
            $transresUtil->removeReviewsFromProject($project,$originalIrbReviews,$project->getIrbReviews());
            $transresUtil->removeReviewsFromProject($project,$originalAdminReviews,$project->getAdminReviews());
            $transresUtil->removeReviewsFromProject($project,$originalCommitteeReviews,$project->getCommitteeReviews());
            $transresUtil->removeReviewsFromProject($project,$originalFinalReviews,$project->getFinalReviews());
            //////////// EOF remove the relationship between the review and the project ////////////

            //edit
//            if ($form->getClickedButton() && 'saveAsDraft' === $form->getClickedButton()->getName()) {
//                //Save Project as Draft => state='draft'
//                $project->setState('draft');
//            }

            //edit
            if ($form->getClickedButton() && 'saveAsComplete' === $form->getClickedButton()->getName()) {
                //Complete Submission => state='submit'
                if( $project->getState() == 'draft' ) {
                    $project->setState('completed');
                }
            }
            if ($form->getClickedButton() && 'submitIrbReview' === $form->getClickedButton()->getName()) {
                //Complete Submission => state='submit'
                if( $project->getState() == 'completed' || $project->getState() == 'draft' ) {
                    $project->setState('irb_review');
                    $startProjectReview = true;

                    $label = $transresUtil->getStateLabelByName($project->getState());
                    $msg = "Project ID ".$project->getOid()." has been sent to the stage '$label' from '".$originalStateLabel."'";
                }
            }

            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($project);

            if( !$testing ) {
                $em->persist($project);
                $em->flush();
            }

            //testing
//            print "<pre>";
//            var_dump($_POST);
//            print "</pre><br>";
//            echo "formnode[420]=".$_POST['formnode[420]']."<br>";
//            echo "formnode[421]=".$_POST['formnode[421]']."<br>";

            //process form nodes
            $formNodeUtil = $this->get('user_formnode_utility');
            $formNodeUtil->processFormNodes($request,$project->getMessageCategory(),$project,$testing); //testing

            $msg = $msg . " by ".$project->getUpdateUser()->getUsernameOptimal();

            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            //eventlog
            $eventType = "Project Updated";
            $transresUtil->setEventLog($project,$eventType,$msg,$testing);

            if( $startProjectReview ) {
                //send confirmation email
                $break = "\r\n";
                //get project url
                $projectUrl = $transresUtil->getProjectShowUrl($project);
                $emailBody = $msg . $break.$break. "Please click on the URL below to view this project:".$break.$projectUrl;
                $transresUtil->sendNotificationEmails($project,null,$msg,$emailBody,$testing);
            }

            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
        }

        $eventType = "Project Viewed";
        $msg = "Project ID ".$project->getOid() ." has been viewed on the edit page.";
        $transresUtil->setEventLog($project,$eventType,$msg,$testing);

        return array(
            'project' => $project,
            'edit_form' => $form->createView(),
            'cycle' => $cycle,
            'formtype' => $formtype,
            'title' => "Edit Project ID ".$project->getOid(),
            'triggerSearch' => 0,
            'formnodetrigger' => $formnodetrigger,
            'formnodeTopHolderId' => $formnodeTopHolderId,
            'entityNamespace' => $classNamespace,
            'entityName' => $className,
            'entityId' => $project->getId(),
            'sitename' => $this->container->getParameter('translationalresearch.sitename'),
        );
    }


    public function createProjectEntity($user,$project=null) {

        $em = $this->getDoctrine()->getManager();

        if( !$project ) {
            $project = new Project($user);
            $project->setVersion(1);
        }

        if( !$project->getInstitution() ) {
            $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName('Pathology and Laboratory Medicine');
            $project->setInstitution($institution);
        }

        //set order category
        if( !$project->getMessageCategory() ) {
            $categoryStr = "HemePath Translational Research Project";  //"Pathology Call Log Entry";
            //$categoryStr = "Nesting Test"; //testing
            $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);

            if (!$messageCategory) {
                throw new \Exception("Message category is not found by name '" . $categoryStr . "'");
            }
            $project->setMessageCategory($messageCategory);
        }

        return $project;
    }




    /**
     * @Route("/project/generate-form-node-tree/", name="translationalresearch_generate_form_node_tree")
     * @Method("GET")
     */
    public function generateFormNodeAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transResFormNodeUtil = $this->get('transres_formnode_util');
        $count = $transResFormNodeUtil->generateTransResFormNode();

        exit("Form Node Tree generated: ".$count);
    }
}