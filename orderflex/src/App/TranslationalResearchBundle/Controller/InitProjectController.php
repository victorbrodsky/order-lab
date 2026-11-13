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

use App\TranslationalResearchBundle\Entity\Project;
use App\TranslationalResearchBundle\Entity\IrbApprovalTypeList;
use App\TranslationalResearchBundle\Form\InitProjectType;
use App\TranslationalResearchBundle\Util\ProjectPdfBackgroundGenerator;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\EventObjectTypeList;
use App\UserdirectoryBundle\Entity\Institution;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Minimalistic controller for "Investigator's Initial Project Inquiry" (specialty 'init').
 * Separate from the full ProjectController pipeline: uses InitProjectType form
 * with only the fields shown on the CTP new-project-inquiry page.
 */
class InitProjectController extends OrderAbstractController
{
    /**
     * New "Investigator's Initial Project Inquiry" project request
     */
    #[Route(path: '/init-project/new', name: 'translationalresearch_initproject_new', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Project/init-project.html.twig')]
    public function initProjectNewAction(Request $request)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $specialtyStr = 'init';
        $specialty = $transresUtil->getSpecialtyObject($specialtyStr);

        if( false === $transresPermissionUtil->hasProjectPermission('create',null,$specialty) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //check enableNewProjectAccessPage (same as newProjectAction)
        if( $transresUtil->getTransresSiteProjectParameter('enableNewProjectAccessPage',null,$specialty) !== true ) {
            $adminEmailsStr = $transresUtil->getAdminEmailsStrBySpecialty(null,$specialty,false);
            $additionalMessage = "This project request type is currently not active. ".
                "Please select a different project request type or contact $adminEmailsStr";
            return $this->redirect($this->generateUrl('translationalresearch-nopermission',array('additionalMessage'=>$additionalMessage)));
        }

        //check if user does not have ROLE_TRANSRES_REQUESTER and specialty role
        $transresUtil->addMinimumRolesToCreateProject($specialty);

        if( $transresUtil->isUserAllowedSpecialtyObject($specialty) === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to access the $specialty project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $requesterGroupStr = $request->query->get('requester-group');

        //'WCM Investigator Project Inquiry' (requester-group=Internal) or
        //'External Collaboration Project Inquiry' (requester-group=External)
        $inquiryType = 'wcm';
        if( $requesterGroupStr && stripos($requesterGroupStr,'external') !== false ) {
            $inquiryType = 'external';
        }

        //create the project entity (same as newProjectAction's createProjectEntity)
        $project = new Project($user);
        $project->setVersion(1);

        if( !$project->getInstitution() ) {
            $autoAssignInstitution = $userSecUtil->getAutoAssignInstitution();
            if( !$autoAssignInstitution ) {
                $autoAssignInstitution = $em->getRepository(Institution::class)->findOneByName('Pathology and Laboratory Medicine');
            }
            if( $autoAssignInstitution ) {
                $project->setInstitution($autoAssignInstitution);
            }
        }

        $project->setProjectSpecialty($specialty);

        //Set requester group (i.e. ?requester-group=Internal)
        if( $requesterGroupStr ) {
            $requesterGroupObject = $transresUtil->getRequesterGroupObject($requesterGroupStr);
            if( $requesterGroupObject ) {
                $project->setRequesterGroup($requesterGroupObject);
            }
        }

        //set default exempt (same as newProjectAction)
        $exemptIrbApproval = $em->getRepository(IrbApprovalTypeList::class)->findOneByName("Not Exempt");
        $project->setExemptIrbApproval($exemptIrbApproval);
        $exemptIACUCApproval = $em->getRepository(IrbApprovalTypeList::class)->findOneByName("Exempt");
        $project->setExemptIACUCApproval($exemptIACUCApproval);

        //add all default reviewers (same as newProjectAction)
        $transresUtil->addDefaultStateReviewers($project);

        $cycle = 'new';
        $params = array(
            'cycle' => $cycle,
            'disabled' => false,
            'inquiryType' => $inquiryType,
            'saveAsDraft' => true,
            'submitIrbReview' => true,
            'updateProject' => false,
        );
        $form = $this->createForm(InitProjectType::class, $project, array(
            'form_custom_value' => $params,
        ));
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            //External form has no Project Title field (same as the CTP inquiry page)
            if( !$project->getTitle() ) {
                $project->setTitle('External Collaboration Project Inquiry');
            }

            $startProjectReview = false;

            if( $form->getClickedButton() && 'saveAsDraft' === $form->getClickedButton()->getName() ) {
                $project->setState('draft');
            }

            if( $form->getClickedButton() && 'submitIrbReview' === $form->getClickedButton()->getName() ) {
                $project->setState('irb_review');
                $startProjectReview = true;
            }

            $transresUtil->assignMinimumProjectRoles($project); //new

            $project->autoPopulateApprovedProjectBudget(); //new
            $project->calculateAndSetImplicitExpirationDate();
            $project->processShowHideFields();

            $em->persist($project);
            $em->flush();

            $project->generateOid();
            $em->flush();

            //generate project PDF (same as newProjectAction)
            $currentUserId = ($user && method_exists($user,'getId')) ? (int)$user->getId() : null;
            $projectPdfBackgroundGenerator = $this->container->get(ProjectPdfBackgroundGenerator::class);
            $projectPdfBackgroundGenerator->queueProjectPdfGeneration(
                $request,
                (int)$project->getId(),
                $currentUserId
            );

            //Same draft/review confirmation message and email as newProjectAction
            $emailSubject = "Your project request draft has been saved and assigned ID ".$project->getOid();
            $msg = "Your project request draft has been saved and assigned ID ".$project->getOid().".".
                " In order to initiate the review of your project request,".
                " please make sure to complete your submission once your draft is ready.".
                " Project requests with a 'draft' status will not be reviewed until they are finalized and submitted.";
            if( $startProjectReview ) {
                $emailSubject = "Your project request has been received and assigned the following ID: ".$project->getOid();
                $msg = "Thank you for your submission! Your project request '".$project->getTitle().
                    "' has been received and assigned the following ID of ".$project->getOid().".".
                    " It will be reviewed and you should receive notifications regarding its approval status by email.".
                    " You can also log back into the website for the ".$transresUtil->getBusinessEntityName().
                    " to review the status of your project request, ".
                    "submit your subsequent work requests (upon project request approval), and see your associated invoices (if any) as well.";
            }

            $this->addFlash(
                'notice',
                $msg
            );

            if( $startProjectReview ) {
                $break = "<br>";
                $projectUrl = $transresUtil->getProjectShowUrl($project);
                $emailBody = $msg . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;

                $emailUtil = $this->container->get('user_mailer_utility');
                $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
                $requesterEmails = $transresUtil->getRequesterMiniEmails($project);
                $adminsCcs = $transresUtil->getTransResAdminEmails($project,true,true);
                if( $requesterEmails ) {
                    $emailUtil->sendEmail($requesterEmails,$emailSubject,$emailBody,$adminsCcs,$senderEmail);
                }
            }

            return $this->redirectToRoute('translationalresearch_initproject_show',array('id'=>$project->getId()));
        }

        $title = "New Investigator's Initial Project Inquiry";
        if( $inquiryType === 'external' ) {
            $title = "New External Collaboration Project Inquiry";
        }

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'inquiryType' => $inquiryType,
            'title' => $title,
        );
    }

    //'wcm' (Internal requester group) or 'external' (External requester group)
    private function getInquiryType( Project $project ) {
        $requesterGroup = $project->getRequesterGroup();
        if( $requesterGroup ) {
            if( stripos($requesterGroup->getName().' '.$requesterGroup->getAbbreviation(),'external') !== false ) {
                return 'external';
            }
        }
        return 'wcm';
    }

    /**
     * Edit "Investigator's Initial Project Inquiry" project request
     */
    #[Route(path: '/init-project/edit/{id}', name: 'translationalresearch_initproject_edit', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Project/init-project.html.twig')]
    public function initProjectEditAction(Request $request, #[MapEntity] Project $project)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if( false === $transresPermissionUtil->hasProjectPermission('update',$project) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $inquiryType = $this->getInquiryType($project);

        $cycle = 'edit';
        $params = array(
            'cycle' => $cycle,
            'disabled' => false,
            'inquiryType' => $inquiryType,
            'saveAsDraft' => false,
            'submitIrbReview' => false,
            'updateProject' => true,
        );
        $form = $this->createForm(InitProjectType::class, $project, array(
            'form_custom_value' => $params,
        ));
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $project->autoPopulateApprovedProjectBudget();
            $project->calculateAndSetImplicitExpirationDate();
            $project->processShowHideFields();

            $em->persist($project);
            $em->flush();

            $this->addFlash(
                'notice',
                "Project request ".$project->getOid()." has been successfully updated."
            );

            return $this->redirectToRoute('translationalresearch_initproject_show',array('id'=>$project->getId()));
        }

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'inquiryType' => $inquiryType,
            'title' => "Edit Investigator's Initial Project Inquiry ".$project->getOid(),
        );
    }

    /**
     * View "Investigator's Initial Project Inquiry" project request
     */
    #[Route(path: '/init-project/show/{id}', name: 'translationalresearch_initproject_show', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Project/init-show.html.twig')]
    public function initProjectShowAction(Request $request, #[MapEntity] Project $project)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');

        if( false === $transresPermissionUtil->hasProjectPermission("view",$project) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //event log (same as showAction)
        $eventType = "Project Viewed";
        $msg = "Project request ".$project->getOid() ." has been viewed on the show page.";
        $label = $transresUtil->getStateLabelByName($project->getState());
        $msg = $msg . " The project's current status is ".$label.".";
        $transresUtil->setEventLog($project,$eventType,$msg);

        $canEdit = $transresPermissionUtil->hasProjectPermission("update",$project);
        $inquiryType = $this->getInquiryType($project);

        return array(
            'project' => $project,
            'cycle' => 'show',
            'inquiryType' => $inquiryType,
            'title' => $project->getProjectInfoName(),
            'canEdit' => $canEdit,
        );
    }
}
