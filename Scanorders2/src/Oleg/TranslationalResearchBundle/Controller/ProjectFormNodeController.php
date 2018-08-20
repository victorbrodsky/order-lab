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
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $specialties = $transresUtil->getTransResProjectSpecialties(false);

        //check if user does not have ROLE_TRANSRES_REQUESTER and specialty role
        //$transresUtil->addMinimumRolesToCreateProject();

        return array(
            'specialties' => $specialties,
            'title' => "Please select the specialty for your project request"
        );
    }


    /**
     * Creates a new project entity with formnode.
     *
     * @Route("/project/formnode/new/{specialtyStr}", name="translationalresearch_project_formnode_new")
     * @Template("OlegTranslationalResearchBundle:Project:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newFormNodeAction(Request $request, $specialtyStr)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_USER')) { //ROLE_USER, IS_AUTHENTICATED_ANONYMOUSLY, ROLE_TRANSRES_REQUESTER
            //exit('NOT GRANTED: new project '.$specialtyStr);
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        //$specialty is a url prefix (i.e. "new-ap-cp-project")
        $specialty = $transresUtil->getSpecialtyObject($specialtyStr);

        //check if user does not have ROLE_TRANSRES_REQUESTER and specialty role
        $transresUtil->addMinimumRolesToCreateProject($specialty);

        $cycle = "new";

        if( $transresUtil->isUserAllowedSpecialtyObject($specialty) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the $specialty project specialty"
            );
            //exit('NO SPECIALTY: new project '.$specialtyStr);
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $testing = false;
        //$testing = true;

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

            $startProjectReview = false;

            //exit("clickedButton=".$form->getClickedButton()->getName());

            //new
            if ($form->getClickedButton() && 'saveAsDraft' === $form->getClickedButton()->getName()) {
                //Save Project as Draft => state='draft'
                $project->setState('draft');
            }

            //new
            if ($form->getClickedButton() && 'submitIrbReview' === $form->getClickedButton()->getName()) {
                //Submit to IRB review
                $project->setState('irb_review');
                $startProjectReview = true;
            }

            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($project,"document");
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($project,"irbApprovalLetter");
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($project,"humanTissueForm");

            if( !$testing ) {
                $em->persist($project);
                $em->flush();

                $project->generateOid();
                $em->flush();
            }

            //process form nodes
            $formNodeUtil = $this->get('user_formnode_utility');
            $formNodeUtil->processFormNodes($request,$project->getMessageCategory(),$project,$testing); //testing

//            //update project's irbExpirationDate
//            $projectIrbExpirationDate = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"IRB Expiration Date");
//            if( $projectIrbExpirationDate ) {
//                $expDate = date_create_from_format('m/d/Y', $projectIrbExpirationDate);
//                $project->setIrbExpirationDate($expDate);
//                $em->flush($project);
//            }
//            //update project's fundedAccountNumber
//            $projectFundedAccountNumber = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"If funded, please provide account number");
//            if( $projectFundedAccountNumber ) {
//                $project->setFundedAccountNumber($projectFundedAccountNumber);
//                $em->flush($project);
//            }
            $transresUtil->copyFormNodeFieldsToProject($project);

            //$label = $transresUtil->getStateLabelByName($project->getState());

            //Draft message:
            //Your project request draft has been saved and assigned ID [id].
            // In order to initiate the review of your project request,
            // please make sure to complete your submission once your draft is ready.
            // Project requests with a “draft” status will not be reviewed until they are finalized and submitted.
            $emailSubject = "Your project request draft has been saved and assigned ID ".$project->getOid();
            $msg = "Your project request draft has been saved and assigned ID ".$project->getOid().".".
                " In order to initiate the review of your project request,".
                " please make sure to complete your submission once your draft is ready.".
                " Project requests with a 'draft' status will not be reviewed until they are finalized and submitted.";
            if( $startProjectReview ) {
                //$msg = "Project ID ".$project->getOid()." has been successfully created and sent to the status '$label'";
                //Thank you for your submission! Your project request has been assigned an ID
                // of "[ID]" and will be reviewed. You should receive notifications of approval
                // status updates by email. You can also log back in to this site to review
                // the status of your project request, submit your subsequent work requests
                // (upon project request approval), and see your associated invoices (if any) as well.
                $emailSubject = "Your project request has been assigned an ID of ".$project->getOid();
                $msg = "Thank you for your submission! Your project request has been assigned an ID of ".$project->getOid().
                    " and will be reviewed.".
                    " You should receive notifications of approval status updates by email.".
                    " You can also log back in to this site to review the status of your project request, ".
                    "submit your subsequent work requests (upon project request approval), and see your associated invoices (if any) as well.";
            }

            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Project Created";
            $transresUtil->setEventLog($project,$eventType,$msg,$testing);

            if( $startProjectReview ) {
                //send confirmation email
                $break = "\r\n";
                //get project url
                $projectUrl = $transresUtil->getProjectShowUrl($project);
                $emailBody = $msg . $break.$break. "Please click on the URL below to view this project:".$break.$projectUrl;
                $transresUtil->sendNotificationEmails($project,null,$emailSubject,$emailBody,$testing);
            }

            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
        }

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $specialty->getName()." Project Request",
            'formnodetrigger' => $formnodetrigger,
            'formnodeTopHolderId' => $formnodeTopHolderId
        );
    }










    /**
     * Get Project Edit page
     * Originally edit form generates a new entity Project with new id and same oid.
     *
     * @Route("/project/formnode/edit/{id}", name="translationalresearch_project_formnode_edit")
     * @Template("OlegTranslationalResearchBundle:Project:edit.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Project $project)
    {

//        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER')) {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
        $transresUtil = $this->container->get('transres_util');

        if(
            $transresUtil->isAdminOrPrimaryReviewer() ||
            $transresUtil->isProjectEditableByRequester($project)
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
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
        $originalProjectState = $project->getState();
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
//            if ($form->getClickedButton() && 'saveAsComplete' === $form->getClickedButton()->getName()) {
//                //Complete Submission => state='submit'
//                if( $project->getState() == 'draft' ) {
//                    $project->setState('completed');
//                }
//            }

            //exit("clickedButton=".$form->getClickedButton()->getName());

            //exit('before set state to irb_review');
            if ($form->getClickedButton() && 'submitIrbReview' === $form->getClickedButton()->getName()) {
                //Submit to IRB review
                if( $project->getState() == 'draft' ) {
                    $project->setState('irb_review');
                    $startProjectReview = true;

                    $label = $transresUtil->getStateLabelByName($project->getState());
                    $msg = "Project ID ".$project->getOid()." has been successfully updated and the status has been changed from '$originalStateLabel' to '$label'";
                }
            }

            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($project,"document");
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($project,"irbApprovalLetter");
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($project,"humanTissueForm");

            //Change review's decision according to the state (if state has been changed manually)
            $eventResetMsg = null;
//            if( $originalProjectState != $project->getState() ) {
//                $eventResetMsg = $transresUtil->resetReviewDecision($project);
//            }

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
            //TODO: 1) check and fix formnode update. 2) adding the same dropdown item on update
            $formNodeUtil = $this->get('user_formnode_utility');
            $formNodeUtil->processFormNodes($request,$project->getMessageCategory(),$project,$testing); //testing

            //update project's irbExpirationDate and fundedAccountNumber
            $transresUtil->copyFormNodeFieldsToProject($project);

            $msg = $msg . " by ".$project->getUpdateUser()->getUsernameOptimal().".";

            $label = $transresUtil->getStateLabelByName($project->getState());
            $msg = $msg . " The project's current status is '".$label."'.";

            if( $testing ) {
                echo "<br>Enf of form submit<br>";
                echo "Clicked button=".$form->getClickedButton()->getName()."<br>";
                exit('Form is submitted and finished, msg='.$msg);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            //eventlog
            $eventType = "Project Updated";
            $transresUtil->setEventLog($project,$eventType,$msg.$eventResetMsg,$testing);

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
        $label = $transresUtil->getStateLabelByName($project->getState());
        $msg = $msg . " The project's current status is '".$label."'.";

        $transresUtil->setEventLog($project,$eventType,$msg,$testing);

        return array(
            'project' => $project,
            'edit_form' => $form->createView(),
            'cycle' => $cycle,
            'formtype' => $formtype,
            'title' => "Edit ".$project->getProjectInfoName(),
            'triggerSearch' => 0,
            'formnodetrigger' => $formnodetrigger,
            'formnodeTopHolderId' => $formnodeTopHolderId,
            'entityNamespace' => $classNamespace,
            'entityName' => $className,
            'entityId' => $project->getId(),
            'sitename' => $this->container->getParameter('translationalresearch.sitename'),
        );
    }


//    public function createProjectEntity($user,$project=null) {
//
//        $em = $this->getDoctrine()->getManager();
//
//        if( !$project ) {
//            $project = new Project($user);
//            $project->setVersion(1);
//        }
//
//        if( !$project->getInstitution() ) {
//            $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName('Pathology and Laboratory Medicine');
//            $project->setInstitution($institution);
//        }
//
//        //set order category
//        if( !$project->getMessageCategory() ) {
//            $categoryStr = "HemePath Translational Research Project";  //"Pathology Call Log Entry";
//            //$categoryStr = "Nesting Test"; //testing
//            $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
//
//            if (!$messageCategory) {
//                throw new \Exception("Message category is not found by name '" . $categoryStr . "'");
//            }
//            $project->setMessageCategory($messageCategory);
//        }
//
//        return $project;
//    }




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