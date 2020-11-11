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
 * User: oli2002
 * Date: 11/11/15
 * Time: 3:42 PM
 */

namespace App\ResAppBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

use App\ResAppBundle\Entity\ResidencyApplication;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;


class ResAppApplicantController extends OrderAbstractController {



    /**
     * @Route("/interview-modal/{id}", name="resapp_interview_modal", methods={"GET"})
     * @Template("AppResAppBundle/Interview/modal.html.twig")
     */
    public function interviewModalAction(Request $request, $id) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_USER') ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        $res = "";

        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppResAppBundle:ResidencyApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Residency Application by id='.$id);
        }



        return array(
            'entity' => $entity,
            'pathbase' => 'resapp',
            'sitename' => $this->getParameter('resapp.sitename')
        );
    }




    /**
     * @Route("/interview-score-rank/{id}", name="resapp_interviewe_score_rank", methods={"GET"})
     */
    public function intervieweScoreRankAction(Request $request, $id) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_USER') ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        $res = "";

        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();
        $resappUtil = $this->container->get('resapp_util');

        $entity = $em->getRepository('AppResAppBundle:ResidencyApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Residency Application by id='.$id);
        }

        if( $entity->getInterviewScore() == null || $entity->getInterviewScore() <= 0 ) {
            $response = new Response();
            $response->setContent($res);
            return $response;
        }

        $resappType = $entity->getResidencyTrack();

        //$startDate = $entity->getStartDate();
        //$transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');
        //$startDateStr = $transformer->transform($startDate);

        ////////////// Getting comparison 1st best of 1 available in AP/CP for 2021 ///////////////
//        $applicants = $em->getRepository('AppResAppBundle:ResidencyApplication')->find($id);
        $repository = $em->getRepository('AppResAppBundle:ResidencyApplication');
        $dql = $repository->createQueryBuilder("resapp");
        //TODO: optimize this by a single query without foreach loop
//        ->select('((SELECT COUNT(1) AS num FROM stats  WHERE stats.marks  > s.marks ) + 1)  AS rank')
//        ->from('Stats s')
//        ->where('s.user_id = ?', $user_id )
//        ->orderBy('rank');
        //$dql->select('((SELECT COUNT(1) AS num FROM stats  WHERE stats.marks  > s.marks ) + 1)  AS rank');
        $dql->select('resapp');
        $dql->leftJoin("resapp.residencyTrack", "residencyTrack");

        $dql->where("residencyTrack.id = " . $resappType->getId() );

        $startDate = $entity->getStartDate();
        $startDateStr = $startDate->format('Y');
        $bottomDate = $startDateStr."-01-01";
        $topDate = $startDateStr."-12-31";
        $dql->andWhere("resapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'" );

        $dql->andWhere("resapp.interviewScore IS NOT NULL AND resapp.interviewScore != '0'");

        $dql->orderBy("resapp.interviewScore","ASC");

        $query = $em->createQuery($dql);
        $applicantions = $query->getResult();

        //echo "applicants=".count($applicantions)."<br>";
        ////////////// EOF Getting comparison 1st best of 1 available in AP/CP for 2021 ///////////////

        if( count($applicantions) > 0 ) {

            $rank = 1;
            foreach( $applicantions as $applicantion ) {
                if( $applicantion->getId() == $id ) {
                    break;
                }
                $rank++;
            }

            //Combined Interview Score: X (Nth best of M available in [Residency specialty] for [Year])
            //Combined Interview Score: 3.3 (1st best of 6 available in Cytopathology for 2017)

            $rankStr = $rank."th";

            if( $rank == 1 ) {
                $rankStr = $rank."st";
            }
            if( $rank == 2 ) {
                $rankStr = $rank."nd";
            }
            if( $rank == 3 ) {
                $rankStr = $rank."rd";
            }

            $res = "Interview Score (lower is better): ".
                $entity->getInterviewScore().
                " (".$rankStr." best of ".count($applicantions).
                " available in ".$resappType." for ".$startDateStr.")";

            //Average Fit for Program: 1.33 (A-, scored by 3 of 6 interviewers)
            $res = $res . "<br>" . "Average Fit for Program (lower is better): " . $entity->getCalculatedAverageFit();
        }

        $response = new Response();
        $response->setContent($res);
        return $response;
    }



    /**
     * @Route("/invite-interviewers-to-rate/{id}", name="resapp_inviteinterviewerstorate", methods={"GET"})
     */
    public function inviteInterviewersToRateAction(Request $request, $id) {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR')
        ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppResAppBundle:ResidencyApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Residency Application by id='.$id);
        }

        $emails = array();
        $emailErrorArr = array();
        $event = "Invited interviewers to rate residency application ID " . $id . " ".$entity->getUser().".";

        //get all interviews
        foreach( $entity->getInterviews() as $interview ) {
            if( !$interview->getTotalRank() || $interview->getTotalRank() <= 0 ) {
                //send email to interviewer with links to PDF and Interview object to fill out.
                $emailResArr = $this->sendInvitationEmail($interview);
                $email = $emailResArr['email'];
                $emailError = $emailResArr['error'];
                if( $email ) {
                    $emails[] = $email;
                } else {
                    $emailErrorArr[] = $emailError;
                }
            } else {
                $event = $event . "<br>" . "Skipped interviewer ".$interview->getInterviewerInfo().", because the corresponding evaluation form has been rated.";
            }
        }

        $emailErrorStr = NULL;
        if( count($emailErrorArr) > 0 ) {
            $emailErrorStr = implode("; ",$emailErrorArr);
        }

        $this->sendConfirmationEmail($emails,$entity,$event,$request,$emailErrorStr); //to admin
        
        //return $this->redirect( $this->generateUrl('resapp_home') );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode("ok"));
        return $response;
    }

    /**
     * @Route("/invite-interviewer-to-rate/{interviewId}", name="resapp_invite_single_interviewer_to_rate", methods={"GET"})
     */
    public function inviteSingleInterviewerToRateAction(Request $request, $interviewId) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        $interview = $em->getRepository('AppResAppBundle:Interview')->find($interviewId);

        if( !$interviewId ) {
            throw $this->createNotFoundException('Interviewer can not be found: interviewId='.$interviewId);
        }

        $emailResArr = $this->sendInvitationEmail($interview);
        $email = $emailResArr['email'];
        $emailError = $emailResArr['error'];

        $resapp = $interview->getResapp();

        $emails = array();
        if( $email ) {
            $emails[] = $email;
        }

        $event = "Invited interviewer to rate residency application ID " . $resapp->getId() . " ".$resapp->getUser().".";
        $this->sendConfirmationEmail($emails,$resapp,$event,$request,$emailError);

//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode("ok"));
//        return $response;

        $this->get('session')->getFlashBag()->add(
            'notice',
            "A personal invitation email has been sent to ".$interview->getInterviewer()." ".$email
        );
        return $this->redirect( $this->generateUrl('resapp_show',array('id' => $resapp->getId())) );
    }


    public function sendInvitationEmail( $interview ) {

        $emailResArr = array();
        $logger = $this->container->get('logger');
        $emailUtil = $this->get('user_mailer_utility');
        //$em = $this->getDoctrine()->getManager();
        $resapp = $interview->getResapp();
        $applicant = $resapp->getUser();
        $interviewer = $interview->getInterviewer();

        if( !$interviewer ) {
            $logger->error("send InvitationEmail: No interviewer exists for interview=" . $interview );
            //return null;
            $emailResArr['email'] = NULL;
            $emailResArr['error'] = "send InvitationEmail: No interviewer exists for interview=" . $interview;
            return $emailResArr;
        }

        if( !$resapp->getRecentItinerary() ) {
            $appLink = $this->generateUrl( 'resapp_show', array("id"=>$resapp->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
            $appHref = '<a href="'.$appLink.'">'.$applicant->getUsernameOptimal().' (residency application ID# '.$resapp->getId().')'.'</a>';
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Email invitations to evaluate '.$appHref.' have not been sent. Please upload Itinerary and try again.'
            );

            $logger->error("send InvitationEmail: No recent itinerary found for residency application ID=" . $resapp->getId() );
            //return null;
            $emailResArr['email'] = NULL;
            $emailResArr['error'] = "send InvitationEmail: No recent itinerary found for residency application ID=" . $resapp->getId();
            return $emailResArr;
        }

        $attachmentPath = null;
        $attachmentFilename = null;
        $recentReport = $resapp->getTheMostRecentReport();
        if( $recentReport ) {
            //$attachmentPath = $recentReport->getAbsoluteUploadFullPath();
            $attachmentPath = $recentReport->getAttachmentEmailPath(); //test is not implemented, unless this function is moved to utility
            $attachmentFilename = $recentReport->getDescriptiveFilename();
        } else {
            $logger->error("send InvitationEmail: no recent report found for resapp ID".$resapp->getId());
        }

        //get email
        $email = $interviewer->getEmail();

        //$userutil = new UserUtil();
        //$adminemail = $userutil->getSiteSetting($em,'siteEmail');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $senderEmail = $user->getEmail();

        //resapp_file_download
        //$scheduleDocumentId = $resapp->getRecentItinerary()->getId();
        //$scheduleLink = $this->generateUrl( 'resapp_file_download', array("id"=>$scheduleDocumentId), true );
        $scheduleLink = $this->generateUrl( 'resapp_download_itinerary_pdf', array("id"=>$resapp->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
        $scheduleLink = $this->convertToHref($scheduleLink);

        //resapp_interview_edit
        //$interviewFormLink = $this->generateUrl( 'resapp_interview_edit', array("id"=>$interview->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
        //$interviewFormLink = $this->convertToHref($interviewFormLink);
        //resapp_applicant_edit
        $applicationFormLink = $this->generateUrl( 'resapp_application_edit', array("id"=>$resapp->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
        $applicationFormLink = $this->convertToHref($applicationFormLink);

        //$pdfLink = $this->generateUrl( 'resapp_file_download', array("id"=>$resapp->getRecentReport()->getId()), true );
        $pdfLink = $this->generateUrl( 'resapp_download_pdf', array("id"=>$resapp->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
        $pdfLink = $this->convertToHref($pdfLink);

        //$break = "\r\n";
        $break = "<br>";

        $text = "Dear " . $interviewer->getUsernameOptimal().",".$break.$break;
        $text .= "Please review the RESIDENCY INTERVIEW SCHEDULE for the candidate ".$applicant->getUsernameOptimal()." and submit your evaluation after the interview.".$break.$break;

        //$text .= "The INTERVIEW SCHEDULE URL link:" . $break . $scheduleLink . $break.$break;

        //$text .= "The ONLINE EVALUATION FORM URL personalize link:" . $break . $interviewFormLink . $break.$break;
        $text .= "The ONLINE EVALUATION FORM URL link:" . $break . $applicationFormLink . $break.$break;

        $text .= "The ITINERARY and COMPLETE APPLICATION PDF link:" . $break . $pdfLink . $break.$break;

        $text .= "If you have any additional questions, please don't hesitate to email " . $senderEmail . $break.$break;

        $logger->notice("send InvitationEmail: Before send email to " . $email);

        //get interview date string
        $interviewDateStr = $interview->getInterviewDateStr();

        $cc = null; //"oli2002@med.cornell.edu";
        $emailUtil->sendEmail(
            $email,
            "Residency Candidate (".$applicant->getUsernameOptimal().$interviewDateStr.") Interview Application and Evaluation Form",
            $text,
            $cc,
            $senderEmail,
            $attachmentPath,
            $attachmentFilename
        );

        $logger->notice("send InvitationEmail: Email has been sent to " . $email . $interviewDateStr);

        //return $email;

        $emailResArr['email'] = $email;
        $emailResArr['error'] = NULL;
        return $emailResArr;
    }

    public function convertToHref($url) {
        return '<a href="'.$url.'">'.$url.'</a>';
    }

    public function sendConfirmationEmail( $emails, $resapp, $event, $request, $emailError ) {

        if( $emails && count($emails) > 0 ) {
            $emailStr = " Emails have been sent to the following: ".implode(", ",$emails);
        } else {
            //$emailStr = " Emails have not been sent: there are no destination emails. Probably itinerary or interviewer(s) do not exists.";
            $emailStr = " Emails have not been sent: there are no destination emails.";
            if( $emailError ) {
                $emailStr = $emailStr . "<br>Error: $emailError";
            }
        }

        $logger = $this->container->get('logger');
        $emailUtil = $this->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();
        $event = $event . "<br>" . $emailStr;
        $userSecUtil->createUserEditEvent($this->getParameter('resapp.sitename'),$event,$systemUser,$resapp,$request,'Residency Application Rating Invitation Emails Resent');

        //return $this->redirect( $this->generateUrl('resapp_home') );

        //if( $emails && count($emails) > 0 ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );
        //}

        //send only 1 email to coordinator
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $senderEmail = $user->getEmail();

        //get coordinator emails
        $coordinatorEmails = null;
        $resappUtil = $this->container->get('resapp_util');
        $coordinatorEmails = $resappUtil->getCoordinatorsOfResAppEmails($resapp);

        //make sure current user get confirmation email too: insert it to coordinator emails
        if( $coordinatorEmails == null || !in_array($senderEmail, $coordinatorEmails) ) {
            $coordinatorEmails[] = $senderEmail;
        }

        //$coordinatorEmails = implode(", ",$coordinatorEmails);
        //print_r($coordinatorEmails);
        //exit('1');

        //get interview date string
        $interviewDateStr = "";
        $interviewDate = $resapp->getInterviewDate();
        if( $interviewDate ) {
            //$interviewDate->setTimezone(new DateTimeZone("UTC"));
            $interviewDateStr = ", interview date ".$interviewDate->format('m/d/Y');
        }

        $attachmentPath = null;
        $attachmentFilename = null;
        $recentReport = $resapp->getTheMostRecentReport();
        if( $recentReport ) {
            //$attachmentPath = $recentReport->getAbsoluteUploadFullPath();
            $attachmentPath = $recentReport->getAttachmentEmailPath(); //test is not implemented, unless this function is moved to utility
            $attachmentFilename = $recentReport->getDescriptiveFilename();
        }

        $applicant = $resapp->getUser();
        $emailUtil->sendEmail(
            $coordinatorEmails,
            "Residency Candidate (ID# ".$resapp->getId()." ".$applicant->getUsernameOptimal().$interviewDateStr.") Interview Application and Evaluation Form",
            $event,
            null,
            $senderEmail,
            $attachmentPath,
            $attachmentFilename
        );

        $logger->notice("send ConfirmationEmail: "."Residency Candidate (ID# ".$resapp->getId()." ".$applicant->getUsernameOptimal().$interviewDateStr.": Send confirmation email from " . $senderEmail . " to coordinators:".implode(", ",$coordinatorEmails));
    }



    /**
     * @Route("/invite-observers-to-view/{id}", name="resapp_inviteobservers", methods={"GET"})
     */
    public function inviteObserversToRateAction(Request $request, $id) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();


        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppResAppBundle:ResidencyApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Residency Application by id='.$id);
        }

        $logger = $this->container->get('logger');
        $emailUtil = $this->get('user_mailer_utility');

        $emails = array();
        $emailErrorArr = array();

        //get all interviews
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $senderEmail = $user->getEmail();

        foreach( $entity->getObservers() as $observer ) {
            //$pdfLink = $this->generateUrl( 'resapp_file_download', array("id"=>$entity->getRecentReport()->getId()), true );
            $pdfLink = $this->generateUrl( 'resapp_download_pdf', array("id"=>$entity->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
            $pdfLink = $this->convertToHref($pdfLink);

            //resapp_file_download
//            $scheduleLink = null;
//            if( $entity->getRecentItinerary() ) {
//                $scheduleLink = $this->generateUrl( 'resapp_download_itinerary_pdf', array("id"=>$entity->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
//                $scheduleLink = $this->convertToHref($scheduleLink);
//            }

            //get email
            $email = $observer->getEmail();
            if( $email ) {
                $emails[] = $email;
            } else {
                $emailErrorArr[] = "Email is empty for observer".$observer->getUsernameOptimal();
            }

            $applicant = $entity->getUser();

            //$break = "\r\n";
            $break = "<br>";

            $text = "Dear " . $observer->getUsernameOptimal().",".$break.$break;
            $text .= "Please review the RESIDENCY APPLICATION for the candidate ".$applicant->getUsernameOptimal() . " (ID: ".$entity->getId().")".$break.$break;

            $text .= "The ITINERARY and COMPLETE APPLICATION PDF link:" . $break . $pdfLink . $break.$break;

//            if( $scheduleLink ) {
//                $text .= "The INTERVIEW SCHEDULE URL link:" . $break . $scheduleLink . $break.$break;
//            }

            $text .= "If you have any additional questions, please don't hesitate to email " . $senderEmail . $break.$break;

            $attachmentPath = null;
            $attachmentFilename = null;
            $recentReport = $entity->getTheMostRecentReport();
            if( $recentReport ) {
                //$attachmentPath = $recentReport->getAbsoluteUploadFullPath();
                $attachmentPath = $recentReport->getAttachmentEmailPath(); //test is not implemented, unless this function is moved to utility
                $attachmentFilename = $recentReport->getDescriptiveFilename();
            }

            $emailUtil->sendEmail(
                $email,
                "Residency Candidate (".$applicant->getUsernameOptimal().") Application",
                $text,
                null,
                $senderEmail,
                $attachmentPath,
                $attachmentFilename
            );

            $logger->notice("inviteObserversToRateAction: Send observer invitation email from " . $senderEmail . " to :".$email);
        }

        $emailErrorStr = NULL;
        if( count($emailErrorArr) > 0 ) {
            $emailErrorStr = implode("; ",$emailErrorArr);
        }

        $event = "Invited observers to view residency application ID " . $id . " ".$entity->getUser().".";
        $this->sendConfirmationEmail($emails,$entity,$event,$request,$emailErrorStr);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode("ok"));
        return $response;
    }


    /**
     * @Route("/download-interview-applicants-list-pdf/{currentYear}/{resappTypeId}/{resappIds}", name="resapp_download_interview_applicants_list_pdf", methods={"GET"})
     */
    public function downloadInterviewApplicantsListAction(Request $request, $currentYear, $resappTypeId, $resappIds) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_INTERVIEWER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_OBSERVER')
        ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $resappRepGen = $this->container->get('resapp_reportgenerator');
        $em = $this->getDoctrine()->getManager();
        $residencyTrack = null;
        $institutionNameResappName = "";

        if( $resappTypeId && $resappTypeId > 0 ) {
            $residencyTrack = $em->getRepository('AppUserdirectoryBundle:ResidencyTrackList')->find($resappTypeId);
        }

        if( $residencyTrack ) {
            $institution = $residencyTrack->getInstitution();
            $institutionNameResappName = $institution." ".$residencyTrack." ";
        }

        //url=http://collage.med.cornell.edu/order/residency-applications/show/2
        //$url = $this->generateUrl('resapp_show',array('id' => 2),true);
        //echo "url=".$url."<br>";
        //exit();

        //[YEAR] [WCMC (top level of actual institution)] [RESIDENCY-TYPE] Residency Candidate Data generated on [DATE] at [TIME] EST.xls
        //"Interview Evaluations for RESIDENCY-TYPE YEAR generated for LoggedInUserFirstName LoggedInUserLastName on DATE TIME EST.docx
        $fileName = $currentYear." ".$institutionNameResappName."Interview Evaluations generated on ".date('m/d/Y H:i').".pdf";
        $fileName = str_replace("  ", " ", $fileName);
        $fileName = str_replace(" ", "-", $fileName);
        $fileName = str_replace(",", "-", $fileName);

        //take care of authentication
        $session = $this->get('session');
        $session->save();
        session_write_close();
        $PHPSESSID = $session->getId();

    if( 1 ) {

        $pageUrl = $this->generateUrl('resapp_interview_applicants_list', array('resappIds'=>$resappIds), UrlGeneratorInterface::ABSOLUTE_URL); // use absolute path!

        //$output = $this->get('knp_snappy.pdf')->getOutput(
        $output = $resappRepGen->getSnappyPdf()->getOutput(
            $pageUrl, 
            array(
                'cookie' => array(
                    'PHPSESSID' => $PHPSESSID
                )
            )
        );

    } else {

//        $resappUtil = $this->container->get('resapp_util');
//        $entities = $resappUtil->createInterviewApplicantList( $resappIds );
//
//        $html = "";
//        foreach( $entities as $resapp ) {
//            $interviewModalHtml = $this->container->get('templating')->render('AppResAppBundle/Interview/applicant-interview-info.html.twig',
//                array(
//                    'entity' => $resapp,
//                    'pathbase' => 'resapp',
//                    'sitename' => $this->getParameter('resapp.sitename')
//                )
//            );
//
//            $html = $html . '<div style="overflow: hidden; page-break-after:always;">'.
//                    $interviewModalHtml.
//                    '</div>';
//        }
//
//        $output = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
//            'cookie' => array(
//                'PHPSESSID' => $PHPSESSID
//            )));


    }


        return new Response(
            $output,
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="'.$fileName.'"'
            )
        );

    }


    /**
     * @Route("/download-interview-applicants-list-doc/{currentYear}/{resappTypeId}/{resappIds}", name="resapp_download_interview_applicants_list_doc", methods={"GET"})
     */
    public function downloadInterviewApplicantsListDocAction(Request $request, $currentYear, $resappTypeId, $resappIds) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_INTERVIEWER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_OBSERVER')
        ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $residencyTrack = null;
        $institutionNameResappName = "";

        if( $resappTypeId && $resappTypeId > 0 ) {
            $residencyTrack = $em->getRepository('AppUserdirectoryBundle:ResidencyTrackList')->find($resappTypeId);
        }

        if( $residencyTrack ) {
            $institution = $residencyTrack->getInstitution();
            $institutionNameResappName = $institution." ".$residencyTrack." ";
        }

        //[YEAR] [WCMC (top level of actual institution)] [RESIDENCY-TYPE] Residency Candidate Data generated on [DATE] at [TIME] EST.xls
        //"Interview Evaluations for RESIDENCY-TYPE YEAR generated for LoggedInUserFirstName LoggedInUserLastName on DATE TIME EST.docx
        $fileName = $currentYear." ".$institutionNameResappName."Interview Evaluations generated on ".date('m/d/Y H:i').".doc";
        $fileName = str_replace("  ", " ", $fileName);
        $fileName = str_replace(" ", "-", $fileName);
        $fileName = str_replace(",", "-", $fileName);

        //get filtered res applications
        $resappUtil = $this->container->get('resapp_util');
        $entities = $resappUtil->createInterviewApplicantList( $resappIds );

//        $interviewsDocHtml = $this->container->get('templating')->render('AppResAppBundle/Interview/applicants-interview-info-doc.html.twig',
//            array(
//                'entities' => $entities,
//                'pathbase' => 'resapp',
//                'cycle' => 'show',
//                'sitename' => $this->getParameter('resapp.sitename')
//            )
//        );
        $interviewsDocHtml = $this->get('twig')->render('AppResAppBundle/Interview/applicants-interview-info-doc.html.twig',
            array(
                'entities' => $entities,
                'pathbase' => 'resapp',
                'cycle' => 'show',
                'sitename' => $this->getParameter('resapp.sitename')
            )
        );

        return new Response(
            $interviewsDocHtml,
            200,
            array(
                'Content-Type'          => 'application/msword',    //doc
                //'Content-Type'          => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', //docx
                //'Content-Type'          => 'application/vnd.ms-word',    //docx
                'Content-Disposition'   => 'attachment; filename="'.$fileName.'"'
            )
        );

    }

    /**
     * @Route("/interview-applicants-list/{resappIds}", name="resapp_interview_applicants_list", methods={"GET"})
     * @Template("AppResAppBundle/Interview/applicants-interview-info.html.twig")
     */
    public function showInterviewApplicantsListAction(Request $request, $resappIds) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_INTERVIEWER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_OBSERVER')
        ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $resappUtil = $this->container->get('resapp_util');
        $entities = $resappUtil->createInterviewApplicantList( $resappIds );

        return array(
            'entities' => $entities,
            'pathbase' => 'resapp',
            'cycle' => 'show',
            'sitename' => $this->getParameter('resapp.sitename')
        );
    }

} 