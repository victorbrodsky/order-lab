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

use App\TranslationalResearchBundle\Form\ProjectChangeStatusConfirmationType;
use App\TranslationalResearchBundle\Entity\Project;
use App\TranslationalResearchBundle\Form\ProjectStateType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class ProjectChangeStatusController extends OrderAbstractController
{
    

    /**
     * Cancel project
     *
     * @Route("/cancel-project/{id}", name="translationalresearch_project_cancel", methods={"GET"})
     */
    public function cancelAction(Request $request, Project $project)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');

        if( false === $transresPermissionUtil->hasProjectPermission("cancel",$project) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $project->setState("canceled");

        $em->flush($project);

        //email
        $break = "<br>";
        $emailUtil = $this->container->get('user_mailer_utility');
        $emailSubject = "Your project request ".$project->getOid()." has been canceled";

        $projectUrl = $this->container->get('router')->generate(
            'translationalresearch_project_show',
            array(
                'id' => $project->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $projectUrl = '<a href="'.$projectUrl.'">'.$projectUrl.'</a>';

        $emailBody = "Your project request ".$project->getOid()." has been canceled by " . $user->getUsernameOptimal();

        //comment
        //$emailBody = $emailBody . $break.$break. "Status Comment:" . $break . $project->getStateComment();

        $requesterEmails = $transresUtil->getRequesterMiniEmails($project);
        $adminsCcs = $transresUtil->getTransResAdminEmails($project,true,true); //after project canceled
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);

        $emailBody = $emailBody . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;
        $emailUtil->sendEmail($requesterEmails,$emailSubject,$emailBody,$adminsCcs,$senderEmail);

        //eventlog
        $eventType = "Project Canceled";
        $transresUtil->setEventLog($project,$eventType,$emailBody);

        return $this->redirectToRoute('translationalresearch_project_index');
    }

    /**
     * @Route("/close-reactivation-project-ajax/", name="translationalresearch_close_reactivation_project_ajax", methods={"POST"}, options={"expose"=true})
     */
    public function closeReactivationProjectAjaxAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $res = array(); //"NOTOK";
        $res["flag"] = "NOTOK";

        //translationalresearch_project_close translationalresearch_project_close_without_notifications
        $routename = trim( $request->get('routename') );
        $reason = trim( $request->get('reason') );

        $res = NULL;
        $project = NULL;
        $projectId = trim( $request->get('projectId') );
        if( $projectId ) {
            $project = $em->getRepository('AppTranslationalResearchBundle:Project')->find($projectId);
        }

        if( false === $transresPermissionUtil->hasProjectPermission("close",$project) ) {
            //return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            $res["error"] = "No permission to close this project";
            $res["flag"] = "NOTOK";

            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($res));
            return $response;
        }


        $result = NULL;

        if( $routename == 'translationalresearch_project_close' || $routename == 'translationalresearch_project_close_without_notifications' ) {
            //Close project
            $result = $this->closeProject($project, $reason, $routename);
        }

        if( $routename == 'translationalresearch_project_approve' ) {
            //Reactivation project
            $targetStatus = "final_approved";
            //$targetStatusRequester = $user;
            $result = $this->reactivationProject($project, $reason, $routename, $targetStatus, $user);
        }

        if( !$result ) {
            $res["error"] = "Unknown error: route name '$routename'' is not valid";
            $res["flag"] = "NOTOK";
        } else {
            $res["flag"] = "OK";
            $res["error"] = $result;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($res));
        return $response;
    }
    
    /**
     * Close project
     *
     * @Route("/close-project/{id}", name="translationalresearch_project_close", methods={"GET"})
     * @Route("/close-project-without-notifications/{id}", name="translationalresearch_project_close_without_notifications", methods={"GET"})
     */
    public function closeAction(Request $request, Project $project)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');

        if( false === $transresPermissionUtil->hasProjectPermission("close",$project) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //exit('close project');

        //Close project
        $routeName = $request->get('_route');
        $reason = NULL;
        $this->closeProject($project,$reason,$routeName);

        return $this->redirectToRoute('translationalresearch_project_index');
    }
    public function closeProject( $project, $reason, $routeName ) {
        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $testing = false;
        $originalStateStr = $project->getState();
        $to = "closed";

        $project->setState($to);

        if( $reason ) {
            $project->updateClosureReason($reason,$user);
        }

        $em->flush($project);

        /////////////////////// email and logger ////////////////////////
//        $break = "<br>";
//        $emailUtil = $this->container->get('user_mailer_utility');
//        $emailSubject = "Your project request ".$project->getOid()." has been closed";
//
//        $projectUrl = $this->container->get('router')->generate(
//            'translationalresearch_project_show',
//            array(
//                'id' => $project->getId(),
//            ),
//            UrlGeneratorInterface::ABSOLUTE_URL
//        );
//        $projectUrl = '<a href="'.$projectUrl.'">'.$projectUrl.'</a>';
//
//        $emailBody = "Your project request ".$project->getOid()." has been closed by " . $user->getUsernameOptimal();
//
//        //comment
//        //$emailBody = $emailBody . $break.$break. "Status Comment:" . $break . $project->getStateComment();
//
//        $requesterEmails = $transresUtil->getRequesterMiniEmails($project);
//        $adminsCcs = $transresUtil->getTransResAdminEmails($project->getProjectSpecialty(),true,true); //only admin
//        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
//
//        $emailBody = $emailBody . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;
//        $emailUtil->sendEmail($requesterEmails,$emailSubject,$emailBody,$adminsCcs,$senderEmail);
//
//        //eventlog
//        $eventType = "Project Closed";
//        $transresUtil->setEventLog($project,$eventType,$emailBody);
        /////////////////////// EOF email and logger ////////////////////////


        //$routeName = $request->get('_route');
        $resultMsg = "Project request ".$project->getOid()." has been closed by $user";
        $sessionNotice = $resultMsg;

        if( $routeName == "translationalresearch_project_close" ) {
            //Send transition emails
            $resultMsg = $transresUtil->sendTransitionEmail($project, $originalStateStr, $testing,$reason);
            $sessionNotice = $transresUtil->getNotificationMsgByStates($originalStateStr,$to,$project,$reason);
        }

        if( $routeName == "translationalresearch_project_close_without_notifications" ) {
            $resultMsg = "Project request ".$project->getOid()." has been closed by $user without sending any email notifications."
                . " Reason: ".$reason;
            $sessionNotice = $resultMsg;
        }

        //event log
        $eventType = "Project Closed";
        $transresUtil->setEventLog($project,$eventType,$resultMsg,$testing);

        $this->get('session')->getFlashBag()->add(
            'notice',
            $sessionNotice
        //$transresUtil->getNotificationMsgByStates($originalStateStr,$to,$project)
        );

        return $sessionNotice;
    }
    //Send project reactivation approval requests
    public function reactivationProject( $project, $reason, $routename, $targetStatus, $targetStatusRequester ) {
        $transresUtil = $this->container->get('transres_util');
        $emailUtil = $this->container->get('user_mailer_utility');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //Send email to Project reactivation approver
        //sendProjectReactivationRequest
        $sendReactivationEmailRequest = $transresUtil->getTransresSiteProjectParameter('sendProjectReactivationRequest',$project);
        if( !$sendReactivationEmailRequest ) {
            //just simply approve project
            $sessionNotice = $this->approveProject($project);
            return $sessionNotice;
        }

        $projectId = $project->getId();
        $projectTitle = $project->getTitle();

        $date = new \DateTime();
        $dateStr = $date->format('m/d/Y \a\t H:i:s');

        $targetStatusStr = $transresUtil->getStateSimpleLabelByName($targetStatus);

//        $previoslyClosureReason = $project->getReactivationReason();
//        if( !$previoslyClosureReason ) {
//            $previoslyClosureReason = "N/A";
//        }

        if( $reason ) {
            $updateReason = "Reactivation requested with reason: ".$reason;
            $project->updateReactivationReason($updateReason,$user);
            $em->flush($project);
        }

        if( !$reason ) {
            $reason = "N/A";
        }

        $subject = $transresUtil->getTransresSiteProjectParameter('projectReactivationSubject',$project);
        if( !$subject ) {
            $subject = "Reactivation of a closed Project $projectId requested";
        }
        /////////////// Replace [[...]]] /////////////////////
        //<br>[[LATEST PROJECT REACTIVATION REASON]] - latest project reactivation reason (replace inside the sender function),
        if( $reason ) {
            if (strpos($subject, '[[LATEST PROJECT REACTIVATION REASON]]') !== false) {
                $subject = str_replace("[[LATEST PROJECT REACTIVATION REASON]]", $reason, $subject);
            }
        }
        //<br>[[PROJECT TARGET REACTIVATION STATUS]] - project reactivation target status (replace inside the sender function),
        if( $targetStatus ) {
            if (strpos($subject, '[[PROJECT TARGET REACTIVATION STATUS]]') !== false) {
                $targetStatusStr = $transresUtil->getStateSimpleLabelByName($targetStatus);
                $subject = str_replace("[[PROJECT TARGET REACTIVATION STATUS]]", $targetStatusStr, $subject);
            }
        }
        if (strpos($subject, '[[PROJECT REACTIVATION REQUESTER]]') !== false) {
            $subject = str_replace("[[PROJECT REACTIVATION REQUESTER]]", $user->getUsernameShortest(), $subject);
        }
        $subject = $transresUtil->replaceTextByNamingConvention($subject,$project,null,null);
        /////////////// EOF Replace [[...]]] /////////////////////

        $body = $transresUtil->getTransresSiteProjectParameter('projectReactivationBody',$project);
        if( !$body ) {
            $body = "Reactivation of a closed Project [ID] titled '$projectTitle' has been requested"
            ." by $targetStatusRequester on $dateStr with the following reason:"
            ."<br>".$reason
            ."<br>"."$targetStatusRequester was interested in changing the status to '$targetStatusStr'"
            ."<br><br>"."Previously documented reason for project closure:<br>[[PROJECT CLOSURE REASON]]" //$previoslyClosureReason
            ."<br><br>"."No new work requests can be accepted for this project while it remains 'closed'."
            ;

            //Reactivation of a closed Project [[PROJECT ID]] titled "[[PROJECT TITLE]]" has been requested by [[PROJECT REACTIVATION REQUESTER]] on [[CURRENT DATETIME]] with the following reason:
            //[[LATEST PROJECT REACTIVATION REASON]]
            //
            //[[PROJECT REACTIVATION REQUESTER]] was interested in changing the status to "[[PROJECT TARGET REACTIVATION STATUS]]"
            //
            //Previously documented reason for project closure: [[PROJECT CLOSURE REASON]]
            //
            //No new work requests can be accepted for this project while it remains "closed".
            //
            //To review this project request, please visit:
            //[[PROJECT SHOW URL]]
            //
            //To approve this project reactivation request and enable new work request submissions, please visit:
            //[[PROJECT REACTIVATION APPROVE URL]]
            //
            //To deny this project reactivation request and keep this project "closed", please visit:
            //[[PROJECT REACTIVATION DENY URL]]
        }

        /////////////// Replace [[...]]] /////////////////////
        //<br>[[LATEST PROJECT REACTIVATION REASON]] - latest project reactivation reason (replace inside the sender function),
        if( $reason ) {
            if (strpos($body, '[[LATEST PROJECT REACTIVATION REASON]]') !== false) {
                $body = str_replace("[[LATEST PROJECT REACTIVATION REASON]]", $reason, $body);
            }
        }
        //<br>[[PROJECT TARGET REACTIVATION STATUS]] - project reactivation target status (replace inside the sender function),
        if( $targetStatus ) {
            if (strpos($body, '[[PROJECT TARGET REACTIVATION STATUS]]') !== false) {
                $targetStatusStr = $transresUtil->getStateSimpleLabelByName($targetStatus);
                $body = str_replace("[[PROJECT TARGET REACTIVATION STATUS]]", $targetStatusStr, $body);
            }
        }
        if (strpos($subject, '[[PROJECT REACTIVATION REQUESTER]]') !== false) {
            $subject = str_replace("[[PROJECT REACTIVATION REQUESTER]]", $user->getUsernameShortest(), $subject);
        }
        $body = $transresUtil->replaceTextByNamingConvention($body,$project,null,null);
        /////////////// EOF Replace [[...]]] /////////////////////

        $from = $transresUtil->getTransresSiteProjectParameter('projectReactivationFromEmail',$project);
        if( !$from ) {
            $from = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
            if( !$from ) {
                $fromArr = $transresUtil->getTransResAdminEmails($project,true,true); //send reminder email
                if( count($fromArr) > 0 ) {
                    $from = $fromArr[0];
                }
            }
            if( !$from ) {
                $userSecUtil = $this->container->get('user_security_utility');
                $from = $userSecUtil->getSiteSettingParameter('siteEmail');
            }
        }

        $adminsCcs = $transresUtil->getTransResAdminEmails($project,true,true);

        //$reactivationApproverEmail = 'Project reactivation approver';
        //Find user with role 'Project reactivation approver'
        $reactivationApproverEmail = NULL;
        $reactivationApprovers = $em->getRepository('AppUserdirectoryBundle:User')->findUsersByRoles(array("ROLE_TRANSRES_PROJECT_REACTIVATION_APPROVER"));
        if( count($reactivationApprovers) > 0 ) {
            $reactivationApprover = $reactivationApprovers[0];
            $reactivationApproverEmail = $reactivationApprover->getSingleEmail(false);
        }
        if( !$reactivationApproverEmail ) {
            $reactivationApproverEmail = $adminsCcs;
        }

        //$adminsCcs = $transresUtil->getTransResAdminEmails($project,true,true); //new project after save
        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail($reactivationApproverEmail,$subject,$body,$adminsCcs,$from);

        //Event Log
        $eventType = "Project Reactivation Request";
        if( is_array($reactivationApproverEmail) ) {
            $reactivationApproverEmail = implode(", ",$reactivationApproverEmail);
        }
        $eventLogMsg = "Project Reactivation Approval Request with a reason: $reason. Email sent to ".$reactivationApproverEmail;
        $transresUtil->setEventLog($project,$eventType,$eventLogMsg);

        //Session notice
        $sessionNotice = "Your request to change the status has been sent to the designated reviewer for approval and the status will be changed once approved";

        $this->get('session')->getFlashBag()->add(
            'notice',
            $sessionNotice
        );

        return $sessionNotice;
    }

    /**
     * Approve project
     *
     * @Route("/approve-project/{id}", name="translationalresearch_project_approve", methods={"GET"})
     */
    public function approveAction(Request $request, Project $project)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');

        if(
            false === $transresPermissionUtil->hasProjectPermission("approve",$project) &&
            false === $transresPermissionUtil->hasProjectPermission("funded-final-review",$project)
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $this->approveProject($project);

        return $this->redirectToRoute('translationalresearch_project_index');
    }

    public function approveProject( $project, $reason=NULL, $reactivationReason=NULL ) {
        $transresPermissionUtil = $this->container->get('transres_permission_util');

        if(
            false === $transresPermissionUtil->hasProjectPermission("approve",$project) &&
            false === $transresPermissionUtil->hasProjectPermission("funded-final-review",$project)
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $testing = false;
        $originalStateStr = $project->getState();
        $to = "final_approved";

        $project->setState($to);

        $project->setApprovalDate(new \DateTime());

        //update expiration date only once on final_approved
        if( !$project->getExpectedExpirationDate() ) {
            $transresUtil->calculateAndSetProjectExpectedExprDate($project); //approve-project
        }
        
        if( $reason ) {
            $project->setReasonForStatusChange($reason);
        }
        
        if( $reactivationReason ) {
            $project->updateReactivationReason($reason,$user);
        }

        $em->flush($project);

        //////////////////// email and eventlog /////////////////////////
//        $break = "<br>";
//        $emailUtil = $this->container->get('user_mailer_utility');
//        $emailSubject = "Your project request ".$project->getOid()." has been approved";
//
//        $projectUrl = $this->container->get('router')->generate(
//            'translationalresearch_project_show',
//            array(
//                'id' => $project->getId(),
//            ),
//            UrlGeneratorInterface::ABSOLUTE_URL
//        );
//        $projectUrl = '<a href="'.$projectUrl.'">'.$projectUrl.'</a>';
//
//        $emailBody = "Your project request ".$project->getOid()." has been approved by " . $user->getUsernameOptimal();
//
//        //comment
//        //$emailBody = $emailBody . $break.$break. "Status Comment:" . $break . $project->getStateComment();
//
//        $requesterEmails = $transresUtil->getRequesterMiniEmails($project);
//        $adminsCcs = $transresUtil->getTransResAdminEmails($project->getProjectSpecialty(),true,true); //only admin
//        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
//
//        $emailBody = $emailBody . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;
//        $emailUtil->sendEmail($requesterEmails,$emailSubject,$emailBody,$adminsCcs,$senderEmail);
//
//        //eventlog
//        $eventType = "Project Approved";
//        $transresUtil->setEventLog($project,$eventType,$emailBody);
        //////////////////// EOF email and eventlog /////////////////////////

        //Send transition emails
        $resultMsg = $transresUtil->sendTransitionEmail($project,$originalStateStr,$testing);

        //event log
        $eventType = "Project Approved";
        $transresUtil->setEventLog($project,$eventType,$resultMsg,$testing);

        $noticeMsg = $transresUtil->getNotificationMsgByStates($originalStateStr,$to,$project);

        $this->get('session')->getFlashBag()->add(
            'notice',
            $noticeMsg
        );

        return $noticeMsg;
    }


    /**
     * @Route("/deny-project-reactivation/{id}", name="translationalresearch_project_reactivation_deny", methods={"GET"})
     */
    public function denyReactivationProjectAction(Request $request, Project $project)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');
        $emailUtil = $this->container->get('user_mailer_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (
            false === $transresPermissionUtil->hasProjectPermission("approve", $project) &&
            false === $transresPermissionUtil->hasProjectPermission("funded-final-review", $project)
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //Project reactivation approver: ROLE_TRANSRES_REACTIVATION_APPROVER
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REACTIVATION_APPROVER')) {
            return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        if ($project->getState() != 'closed') {
            //TODO: This project request [ProjectID] is already active and has a status of ‘[current status]’, updated by [FirstName LastName] on MM/DD/YYYY at HH:MM.
            $statusStr = $transresUtil->getStateSimpleLabelByName($project->getState());
            $errorMsg = "This project request ".$project->getOid()." is already active and has a status of '".$statusStr."'";
            $this->get('session')->getFlashBag()->add(
                'notice',
                $errorMsg
            );

            return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $projectOid = $project->getOid();
        $targetRequester = $project->getTargetStateRequester();
        $today = new \DateTime();
        $todayStr = $today->format('m/d/Y \a\t H:i');
        $statusStr = $transresUtil->getStateSimpleLabelByName($project->getState());
        $projectUrl = $this->container->get('router')->generate(
            'translationalresearch_project_show',
            array(
                'id' => $project->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $projectUrl = '<a href="'.$projectUrl.'">'.$projectUrl.'</a>';

        //233(10): new confirmation page
        //“Thank you! The status for project request [ProjectID] “[Project Title]” (FirstNameOfPI LastNameOfPI)
        // will remain 'Closed'. If you would like to change the status, please select it below and press “Update Status”
//        $piStr = "unknown PI";
//        $piArr = array();
//        foreach( $project->getPrincipalInvestigators() as $pi) {
//            $piArr[] = $pi->getUsernameShortest();
//        }
//        if( count($piArr) > 0 ) {
//            $piStr = implode("; ", $piArr);
//        }
        $piStr = $project->getPiStr();
        $msg = "The status for project request ".$projectOid." '".$project->getTitle()."' ($piStr) will remain 'Closed'";
        $noticeMsg = "Thank you! $msg. If you would like to change the status, please select it below and press 'Update Status'";
        $this->get('session')->getFlashBag()->add(
            'notice',
            $noticeMsg
        );

        //If the user picks a different status on the denial confirmation page and clicks “
        //Update status”, update the status silently and show a green well below with
        // “Status successfully updated to '[whatever status was picked]'.”
        // Do not send an email with this subsequent status update.

        //Write this event of new type “Project reactivation request denied” to the event log.
        //event log
        $eventType = "Project Reactivation Denied";
        $transresUtil->setEventLog($project,$eventType,$msg);

        //Send a notification email to all users with TRP admin role
        //Subject: Request to reactivate project [ProjectID] has been denied
        //Body: Request to reactivate project [ProjectID] submitted by FirstName LastName on MM/DD/YYYY at HH:MM has been denied by FirstName LastName on MM/DD/YYYY at HH:MM.
        //The current status of this project is “[current status]”.
        //To review this project request, please visit:
        //[Link project request view page]
        $subject = "Request to reactivate project $projectOid has been denied";
        $body = "Request to reactivate project $projectOid submitted by $targetRequester"
            //." on MM/DD/YYYY at HH:MM "
            ." has been denied by $user on $todayStr.";
        $body = $body . "<br>" . "The current status of this project is '$statusStr'.";
        $body = $body . "<br>To review this project request, please visit:<br>";
        $body = $body . $projectUrl;

        $adminsCcs = $transresUtil->getTransResAdminEmails($project,true,true);
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail($adminsCcs,$subject,$body,null,$senderEmail);


        return $this->redirectToRoute('translationalresearch_project_change_status_confirmation', array('id' => $project->getId()));
    }

    /**
     * @Route("/approve-project-reactivation/{id}", name="translationalresearch_project_reactivation_approve", methods={"GET"})
     */
    public function approveReactivationProjectAction(Request $request, Project $project)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');
        $emailUtil = $this->container->get('user_mailer_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (
            false === $transresPermissionUtil->hasProjectPermission("approve", $project) &&
            false === $transresPermissionUtil->hasProjectPermission("funded-final-review", $project)
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //Project reactivation approver: ROLE_TRANSRES_REACTIVATION_APPROVER
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REACTIVATION_APPROVER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        if( $project->getState() != 'closed' ) {
            //This project request [ProjectID] is already active and has a status of ‘[current status]’, updated by [FirstName LastName] on MM/DD/YYYY at HH:MM.
            $statusStr = $transresUtil->getStateSimpleLabelByName($project->getState());
            $errorMsg = "This project request ".$project->getOid()." is already active and has a status of '".$statusStr."'";
            $this->get('session')->getFlashBag()->add(
                'notice',
                $errorMsg
            );
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $this->approveProject($project);

        $projectOid = $project->getOid();
        $targetRequester = $project->getTargetStateRequester();
        $today = new \DateTime();
        $todayStr = $today->format('m/d/Y \a\t H:i');
        $statusStr = $transresUtil->getStateSimpleLabelByName($project->getState());
        $projectUrl = $this->container->get('router')->generate(
            'translationalresearch_project_show',
            array(
                'id' => $project->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $projectUrl = '<a href="'.$projectUrl.'">'.$projectUrl.'</a>';

        //233(9) - new confirmation page
        // Thank you! The status for project request [ProjectID] “[Project Title]” (FirstNameOfPI LastNameOfPI)
        // has been updated to [TargetStatus]. If you would like to change the status,
        // please select it below and press “Update status” (show a select2 dropdown
        // with all statuses from Platform Manager to the left of the button)
        $piStr = $project->getPiStr();
        $msg = "The status for project request ".$projectOid." '".$project->getTitle()."' ($piStr) has been updated to '$statusStr'";
        $noticeMsg = "Thank you! $msg. If you would like to change the status, please select it below and press 'Update Status'";
        $this->get('session')->getFlashBag()->add(
            'notice',
            $noticeMsg
        );

        //If the user picks a different status on the approval confirmation page and
        // clicks “Update Status”, update the status silently and show a green
        // well below with “Status successfully updated to '[whatever status was picked]'.”
        // Do not send an email with this subsequent status update.

        //Write this event of new type “Project reactivation request approved” to the event log.
        //event log
        $eventType = "Project Reactivation Approved";
        $transresUtil->setEventLog($project,$eventType,$msg);

        //Send a notification email to all users with TRP admin role
        // (since they are the only ones who could changed the status away from Closed)
        //Subject: Request to reactivate project [ProjectID] has been approved
        //Body: Request to reactivate project [ProjectID] submitted by FirstName LastName on MM/DD/YYYY at HH:MM has been approved by FirstName LastName on MM/DD/YYYY at HH:MM.
        //The current status of this project is “[current status]”.
        //To review this project request, please visit:
        //[Link project request view page]
        $subject = "Request to reactivate project $projectOid has been approved";
        $body = "Request to reactivate project $projectOid submitted by $targetRequester"
        //." on MM/DD/YYYY at HH:MM "
        ." has been approved by $user on $todayStr.";
        $body = $body . "<br>" . "The current status of this project is '$statusStr'.";
        $body = $body . "<br>To review this project request, please visit:<br>";
        $body = $body . $projectUrl;

        $adminsCcs = $transresUtil->getTransResAdminEmails($project,true,true);
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail($adminsCcs,$subject,$body,null,$senderEmail);

        return $this->redirectToRoute('translationalresearch_project_change_status_confirmation', array('id' => $project->getId()));
    }

    /**
     * Show project state change confirmation page with an option to change the state
     *
     * @Route("/project-state-confirmation/{id}", name="translationalresearch_project_change_status_confirmation", methods={"GET","POST"})
     * @Template("AppTranslationalResearchBundle/Project/project-state-confirmation.html.twig")
     */
    public function projectStateConfirmationAction(Request $request, Project $project)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REACTIVATION_APPROVER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->get('transres_util');
        //$user = $this->get('security.token_storage')->getToken()->getUser();
        $cycle = "new";

        //Create Form
        $stateChoiceArr = $transresUtil->getStateChoisesArr();
        $params = array(
            'stateChoiceArr' => $stateChoiceArr,
            'currentStateId' => $project->getState()
        );
        $form = $this->createForm(ProjectChangeStatusConfirmationType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //exit('new');

            //$newState = NULL;
            $newState = $form['updateState']->getData();
            //echo "newState=$newState <br>";

            $project->setState($newState);
            $em->flush($project);

            $msg = "Status successfully updated to '$newState'";

            //event log
            $msg = $msg . " on the project status confirmation page";
            $eventType = "Project Status Updated";
            $transresUtil->setEventLog($project,$eventType,$msg);

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

        }//$form->isSubmitted()

        $currentState = $project->getState();
        $currentStateStr = $transresUtil->getStateSimpleLabelByName($currentState);

        return array(
            'project' => $project,
            'form' => $form->createView(),
            'title' => "Confirmation of the project ".$project->getOid()." status change to '".$currentStateStr."'",
            'cycle' => $cycle,
        );
    }




    /////////////////// DELETE PROJECT /////////////////////
    /**
     * Deletes a project entity.
     *
     * @Route("/project-delete/{id}", name="translationalresearch_project_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Project $project)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $form = $this->createDeleteForm($project);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {
            //exit('deleting...');
            $this->deleteProject($project);
        }

        return $this->redirectToRoute('translationalresearch_project_index');
    }
    /**
     * Deletes a project entity.
     *
     * @Route("/project-delete-get/{id}", name="translationalresearch_project_delete_get", methods={"GET"})
     */
    public function deleteProjectAction(Request $request, Project $project)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        $emailUtil = $this->container->get('user_mailer_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $requesterEmails = $transresUtil->getRequesterMiniEmails($project);
        $adminsCcs = $transresUtil->getTransResAdminEmails($project,true,true); //after project deleted
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);

        $this->deleteProject($project);

        //email
        $break = "<br>";

        $emailSubject = "Project request ".$project->getOid()." has been deleted";
        $emailBody = "Project request ".$project->getOid()." has been deleted by " . $user->getUsernameOptimal();

        $emailUtil->sendEmail($requesterEmails,$emailSubject,$emailBody,$adminsCcs,$senderEmail);

        //eventlog
        $eventType = "Project Deleted";
        $transresUtil->setEventLog($project,$eventType,$emailBody);

        return $this->redirectToRoute('translationalresearch_project_index');
    }
    /**
     * Deletes a project entity.
     *
     * @Route("/delete-multiple-projects/", name="translationalresearch_projects_multiple_delete", methods={"GET"})
     */
    public function deleteMultipleProjectsAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        set_time_limit(600); //600 seconds => 10 min
        ini_set('memory_limit', '2048M');

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('AppTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->leftJoin('project.principalInvestigators','principalInvestigators');

        $dql->andWhere("project.exportId IS NOT NULL");
        //$dql->andWhere("project.oid IS NULL");
        //$dql->andWhere("principalInvestigators.id IS NULL");

        $query = $dql->getQuery();

        $projects = $query->getResult();
        echo "projects count=".count($projects)."<br>";

        foreach($projects as $project) {
            $this->deleteProject($project);
        }

        exit("EOF deleteMultipleProjectsAction");
        return $this->redirectToRoute('translationalresearch_project_index');
    }
    public function deleteProject( $project ) {
        echo $project->getID().": Delete project OID=".$project->getOid()."<br>";
        $em = $this->getDoctrine()->getManager();

        //principalInvestigators
        foreach( $project->getPrincipalInvestigators() as $pi) {
            $project->removePrincipalInvestigator($pi);
        }

        foreach( $project->getRequests() as $transresRequest) {
            $project->removeRequest($transresRequest);
            $transresRequest->setProject(null);
            $this->removeRequestFromDB($transresRequest);
        }
        
        //delete documents

        $em->remove($project);
        $em->flush();
    }
    public function removeRequestFromDB( $transresRequest ) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($transresRequest);
        $em->flush();
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


    /**
     * NOT USED
     * 
     * @Route("/project/set-state/{id}", name="translationalresearch_project_set_state", methods={"GET","POST"})
     * @Template("AppTranslationalResearchBundle/Project/set-state.html.twig")
     */
    public function setStateAction(Request $request, Project $project)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "set-state";

        //$form = $this->createProjectForm($project,$cycle,$request);

        $stateChoiceArr = $transresUtil->getStateChoisesArr();

        $params = array('stateChoiceArr'=>$stateChoiceArr);
        $form = $this->createForm(ProjectStateType::class, $project, array(
            'form_custom_value' => $params,
        ));

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
            'title' => "Set State for Project request ".$project->getOid()
        );
    }


}
