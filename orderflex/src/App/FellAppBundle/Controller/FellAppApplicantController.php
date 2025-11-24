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

namespace App\FellAppBundle\Controller;



use App\FellAppBundle\Entity\Interview; //process.py script: replaced namespace by ::class: added use line for classname=Interview


use App\UserdirectoryBundle\Entity\FellowshipSubspecialty; //process.py script: replaced namespace by ::class: added use line for classname=FellowshipSubspecialty
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;

use App\FellAppBundle\Entity\FellowshipApplication;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;


class FellAppApplicantController extends OrderAbstractController {



    #[Route(path: '/interview-modal/{id}', name: 'fellapp_interview_modal', methods: ['GET'], options: ['expose' => true])]
    #[Template('AppFellAppBundle/Interview/modal.html.twig')]
    public function interviewModalAction(Request $request, $id) {

        if( false == $this->isGranted('ROLE_FELLAPP_USER') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        $res = "";

        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
        $entity = $em->getRepository(FellowshipApplication::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }



        return array(
            'entity' => $entity,
            'pathbase' => 'fellapp',
            'sitename' => $this->getParameter('fellapp.sitename')
        );
    }




    #[Route(path: '/interview-score-rank/{id}', name: 'fellapp_interviewe_score_rank', methods: ['GET'])]
    public function intervieweScoreRankAction(Request $request, $id) {

        if( false == $this->isGranted('ROLE_FELLAPP_USER') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappUtil = $this->container->get('fellapp_util');

        //echo "invite interviewers to rate <br>";
        //exit('intervieweScoreRankAction');
        $res = "";

        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
        $entity = $em->getRepository(FellowshipApplication::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        if( $entity->getInterviewScore() == null || $entity->getInterviewScore() <= 0 ) {
            $response = new Response();
            $response->setContent($res);
            return $response;
        }

        $fellappType = $entity->getFellowshipSubspecialty();
        $fellappGlobalType = $entity->getGlobalFellowshipSpecialty();

        //$startDate = $entity->getStartDate();
        //$transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');
        //$startDateStr = $transformer->transform($startDate);

//        $applicants = $em->getRepository('AppFellAppBundle:FellowshipApplication')->find($id);
        $repository = $em->getRepository(FellowshipApplication::class);
        $dql = $repository->createQueryBuilder("fellapp");
        //TODO: optimize this by a single query without foreach loop
//        ->select('((SELECT COUNT(1) AS num FROM stats  WHERE stats.marks  > s.marks ) + 1)  AS rank')
//        ->from('Stats s')
//        ->where('s.user_id = ?', $user_id )
//        ->orderBy('rank');
        //$dql->select('((SELECT COUNT(1) AS num FROM stats  WHERE stats.marks  > s.marks ) + 1)  AS rank');
        $dql->select('fellapp');
        $dql->leftJoin("fellapp.fellowshipSubspecialty", "fellowshipSubspecialty");


        if( $fellappType ) {
            $dql->where("fellowshipSubspecialty.id = " . $fellappType->getId());
        }
        if( $fellappGlobalType ) {
            $dql->where("globalFellowshipSpecialty.id = " . $fellappGlobalType->getId());
        }

        $startDate = $entity->getStartDate();
        $startDateStr = $startDate->format('Y');

        //$bottomDate = $startDateStr."-01-01";
        //$topDate = $startDateStr."-12-31";
        //echo "old: bottomDate=$bottomDate, topDate=$topDate <br>";

        $startEndDates = $fellappUtil->getAcademicYearStartEndDates($startDateStr);
        $bottomDate = $startEndDates['startDate'];
        $topDate = $startEndDates['endDate'];
        //echo "new: bottomDate=$bottomDate, topDate=$topDate <br>";

        $dql->andWhere("fellapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'" );

        $dql->andWhere("fellapp.interviewScore IS NOT NULL AND fellapp.interviewScore != '0'");

        $dql->orderBy("fellapp.interviewScore","ASC");

        $query = $dql->getQuery();
        $applicantions = $query->getResult();

        //echo "applicants=".count($applicantions)."<br>";

        if( count($applicantions) > 0 ) {

            $rank = 1;
            foreach( $applicantions as $applicantion ) {
                if( $applicantion->getId() == $id ) {
                    break;
                }
                $rank++;
            }

            //Combined Interview Score: X (Nth best of M available in [Fellowship specialty] for [Year])
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

            if( !$fellappType ) {
                $fellappType = $fellappGlobalType;
            }

            $res = "Interview Score (lower is better): ".
                $entity->getInterviewScore().
                " (".$rankStr." best of ".count($applicantions).
                " available in ".$fellappType." for ".$startDateStr.")";

        }

        $response = new Response();
        $response->setContent($res);
        return $response;
    }



    #[Route(path: '/invite-interviewers-to-rate/{id}', name: 'fellapp_inviteinterviewerstorate', methods: ['GET'])]
    public function inviteInterviewersToRateAction(Request $request, $id) {

        if( false == $this->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
        $entity = $em->getRepository(FellowshipApplication::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        $emails = array();
        $event = "Invited interviewers to rate fellowship application ID " . $id . " ".$entity->getUser().".";

        //get all interviews
        foreach( $entity->getInterviews() as $interview ) {
            if( !$interview->getTotalRank() || $interview->getTotalRank() <= 0 ) {
                //send email to interviewer with links to PDF and Interview object to fill out.
                $email = $this->sendInvitationEmail($interview);
                if( $email ) {
                    $emails[] = $email;
                }
            } else {
                $event = $event . "<br>" . "Skipped interviewer ".$interview->getInterviewerInfo().", because the corresponding evaluation form has been rated.";
            }
        }

        $this->sendConfirmationEmail($emails,$entity,$event,$request); //to admin
        
        //return $this->redirect( $this->generateUrl('fellapp_home') );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode("ok"));
        return $response;
    }

    #[Route(path: '/invite-interviewer-to-rate/{interviewId}', name: 'fellapp_invite_single_interviewer_to_rate', methods: ['GET'])]
    public function inviteSingleInterviewerToRateAction(Request $request, $interviewId) {

        if( false == $this->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:Interview'] by [Interview::class]
        $interview = $em->getRepository(Interview::class)->find($interviewId);

        if( !$interviewId ) {
            throw $this->createNotFoundException('Interviewer can not be found: interviewId='.$interviewId);
        }

        $email = $this->sendInvitationEmail($interview);

        $fellapp = $interview->getFellapp();

        $emails = array();
        if( $email ) {
            $emails[] = $email;
        }

        $event = "Invited interviewer to rate fellowship application ID " . $fellapp->getId() . " ".$fellapp->getUser().".";
        $this->sendConfirmationEmail($emails,$fellapp,$event,$request);

//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode("ok"));
//        return $response;

        if( $email ) {
            $this->addFlash(
                'notice',
                "A personal invitation email has been sent to " . $interview->getInterviewer() . " " . $email
            );
        }

        return $this->redirect( $this->generateUrl('fellapp_show',array('id' => $fellapp->getId())) );
    }


    public function sendInvitationEmail( $interview ) {

        $logger = $this->container->get('logger');
        $emailUtil = $this->container->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();
        $fellapp = $interview->getFellapp();
        $applicant = $fellapp->getUser();
        $interviewer = $interview->getInterviewer();

        if( !$interviewer ) {
            $logger->error("send InvitationEmail: No interviewer exists for interview=" . $interview );
            return null;
        }

        if( !$fellapp->getRecentItinerary() ) {
            $appLink = $this->generateUrl( 'fellapp_show', array("id"=>$fellapp->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
            $appHref = '<a href="'.$appLink.'">'.$applicant->getUsernameOptimal().' (fellowship application ID# '.$fellapp->getId().')'.'</a>';
            $this->addFlash(
                'warning',
                'Email invitations to evaluate '.$appHref.' have not been sent. Please upload Itinerary and try again.'
            );

            $logger->error("send InvitationEmail: No recent itinerary found for fellapp ID=" . $fellapp->getId() );
            return null;
        }

        $attachmentPath = null;
        $attachmentFilename = null;
        $recentReport = $fellapp->getTheMostRecentReport();
        if( $recentReport ) {
            //$attachmentPath = $recentReport->getAbsoluteUploadFullPath();
            $attachmentPath = $recentReport->getAttachmentEmailPath(); //test is not implemented, unless this function is moved to utility
            $attachmentFilename = $recentReport->getDescriptiveFilename();
        } else {
            $logger->error("send InvitationEmail: no recent report found for fellapp ID".$fellapp->getId());
        }

        //get email
        $email = $interviewer->getEmail();

        //$userutil = new UserUtil();
        //$adminemail = $userutil->getSiteSetting($em,'siteEmail');
        $user = $this->getUser();
        $senderEmail = $user->getEmail();

        //fellapp_file_download
        //$scheduleDocumentId = $fellapp->getRecentItinerary()->getId();
        //$scheduleLink = $this->generateUrl( 'fellapp_file_download', array("id"=>$scheduleDocumentId), true );
        $scheduleLink = $this->generateUrl( 'fellapp_download_itinerary_pdf', array("id"=>$fellapp->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
        $scheduleLink = $this->convertToHref($scheduleLink);

        //fellapp_interview_edit
        //$interviewFormLink = $this->generateUrl( 'fellapp_interview_edit', array("id"=>$interview->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
        //$interviewFormLink = $this->convertToHref($interviewFormLink);
        //fellapp_applicant_edit
        $applicationFormLink = $this->generateUrl( 'fellapp_application_edit', array("id"=>$fellapp->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
        $applicationFormLink = $this->convertToHref($applicationFormLink);

        //$pdfLink = $this->generateUrl( 'fellapp_file_download', array("id"=>$fellapp->getRecentReport()->getId()), true );
        $pdfLink = $this->generateUrl( 'fellapp_download_pdf', array("id"=>$fellapp->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
        $pdfLink = $this->convertToHref($pdfLink);

        //$break = "\r\n";
        $break = "<br>";

        $text = "Dear " . $interviewer->getUsernameOptimal().",".$break.$break;
        $text .= "Please review the FELLOWSHIP INTERVIEW SCHEDULE for the candidate ".$applicant->getUsernameOptimal()." and submit your evaluation after the interview.".$break.$break;

        $text .= "The INTERVIEW SCHEDULE URL link:" . $break . $scheduleLink . $break.$break;

        //$text .= "The ONLINE EVALUATION FORM URL personalize link:" . $break . $interviewFormLink . $break.$break;
        $text .= "The ONLINE EVALUATION FORM URL link:" . $break . $applicationFormLink . $break.$break;

        $text .= "The COMPLETE APPLICATION PDF link:" . $break . $pdfLink . $break.$break;

        $remoteAccessUrl = $userSecUtil->getSiteSettingParameter('remoteAccessUrl');
        if( $remoteAccessUrl ) {
            $remoteAccessUrl = "(".$remoteAccessUrl.")";
        }
        $text .= "If you are off site, please connect via VPN first $remoteAccessUrl and then follow the links above.";
        $text .= $break.$break;

        $text .= "If you have any additional questions, please don't hesitate to email " . $senderEmail . $break.$break;

        $logger->notice("send InvitationEmail: Before send email to " . $email);

        //get interview date string
        $interviewDateStr = $interview->getInterviewDateStr();

        $cc = null; //"oli2002@med.cornell.edu";
        $emailUtil->sendEmail(
            $email,
            "Fellowship Candidate (".$applicant->getUsernameOptimal().$interviewDateStr.") Interview Application and Evaluation Form",
            $text,
            $cc,
            $senderEmail,
            $attachmentPath,
            $attachmentFilename
        );

        $logger->notice("send InvitationEmail: Email has been sent to " . $email . $interviewDateStr);

        return $email;
    }

    public function convertToHref($url) {
        return '<a href="'.$url.'">'.$url.'</a>';
    }

    public function sendConfirmationEmail( $emails, $fellapp, $event, $request ) {

        if( $emails && count($emails) > 0 ) {
            $emailStr = " Emails have been sent to the following: ".implode(", ",$emails);
        } else {
            $emailStr = " Emails have not been sent: there are no destination emails.";
        }

        $logger = $this->container->get('logger');
        $emailUtil = $this->container->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();
        $event = $event . "<br>" . $emailStr;
        $userSecUtil->createUserEditEvent(
            $this->getParameter('fellapp.sitename'),
            $event,
            $systemUser,
            $fellapp,
            $request,
            'Fellowship Application Rating Invitation Emails Resent'
        );

        //return $this->redirect( $this->generateUrl('fellapp_home') );

        //if( $emails && count($emails) > 0 ) {
            $this->addFlash(
                'notice',
                $event
            );
        //}

        //send only 1 email to coordinator
        $user = $this->getUser();
        $senderEmail = $user->getEmail();

        //get coordinator emails
        $coordinatorEmails = null;
        $fellappUtil = $this->container->get('fellapp_util');
        $coordinatorEmails = $fellappUtil->getCoordinatorsOfFellAppEmails($fellapp);

        //make sure current user get confirmation email too: insert it to coordinator emails
        if( $coordinatorEmails == null || !in_array($senderEmail, $coordinatorEmails) ) {
            $coordinatorEmails[] = $senderEmail;
        }

        //$coordinatorEmails = implode(", ",$coordinatorEmails);
        //print_r($coordinatorEmails);
        //exit('1');

        //get interview date string
        $interviewDateStr = "";
        $interviewDate = $fellapp->getInterviewDate();
        if( $interviewDate ) {
            //$interviewDate->setTimezone(new DateTimeZone("UTC"));
            $interviewDateStr = ", interview date ".$interviewDate->format('m/d/Y');
        }

        $attachmentPath = null;
        $attachmentFilename = null;
        $recentReport = $fellapp->getTheMostRecentReport();
        if( $recentReport ) {
            //$attachmentPath = $recentReport->getAbsoluteUploadFullPath();
            $attachmentPath = $recentReport->getAttachmentEmailPath(); //test is not implemented, unless this function is moved to utility
            $attachmentFilename = $recentReport->getDescriptiveFilename();
        }

        $applicant = $fellapp->getUser();
        $emailUtil->sendEmail(
            $coordinatorEmails,
            "Fellowship Candidate (ID# ".$fellapp->getId()." ".$applicant->getUsernameOptimal().$interviewDateStr.") Interview Application and Evaluation Form",
            $event,
            null,
            $senderEmail,
            $attachmentPath,
            $attachmentFilename
        );

        $logger->notice("send ConfirmationEmail: "."Fellowship Candidate (ID# ".$fellapp->getId()." ".$applicant->getUsernameOptimal().$interviewDateStr.": Send confirmation email from " . $senderEmail . " to coordinators:".implode(", ",$coordinatorEmails));
    }



    #[Route(path: '/invite-observers-to-view/{id}', name: 'fellapp_inviteobservers', methods: ['GET'])]
    public function inviteObserversToRateAction(Request $request, $id) {

        if( false == $this->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();


        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
        $entity = $em->getRepository(FellowshipApplication::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        $logger = $this->container->get('logger');
        $emailUtil = $this->container->get('user_mailer_utility');

        $emails = array();

        //get all interviews
        $user = $this->getUser();
        $senderEmail = $user->getEmail();

        foreach( $entity->getObservers() as $observer ) {
            //$pdfLink = $this->generateUrl( 'fellapp_file_download', array("id"=>$entity->getRecentReport()->getId()), true );
            $pdfLink = $this->generateUrl( 'fellapp_download_pdf', array("id"=>$entity->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
            $pdfLink = $this->convertToHref($pdfLink);

            //fellapp_file_download
            $scheduleLink = null;
            if( $entity->getRecentItinerary() ) {
                //$scheduleDocumentId = $entity->getRecentItinerary()->getId();
                //$scheduleLink = $this->generateUrl( 'fellapp_file_download', array("id"=>$scheduleDocumentId), true );
                $scheduleLink = $this->generateUrl( 'fellapp_download_itinerary_pdf', array("id"=>$entity->getId()), UrlGeneratorInterface::ABSOLUTE_URL );
                $scheduleLink = $this->convertToHref($scheduleLink);
            }

            //get email
            $email = $observer->getEmail();
            $emails[] = $email;

            $applicant = $entity->getUser();

            //$break = "\r\n";
            $break = "<br>";

            $text = "Dear " . $observer->getUsernameOptimal().",".$break.$break;
            $text .= "Please review the FELLOWSHIP APPLICATION for the candidate ".$applicant->getUsernameOptimal() . " (ID: ".$entity->getId().")".$break.$break;

            $text .= "The COMPLETE APPLICATION PDF link:" . $break . $pdfLink . $break.$break;

            if( $scheduleLink ) {
                $text .= "The INTERVIEW SCHEDULE URL link:" . $break . $scheduleLink . $break.$break;
            }

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
                "Fellowship Candidate (".$applicant->getUsernameOptimal().") Application",
                $text,
                null,
                $senderEmail,
                $attachmentPath,
                $attachmentFilename
            );

            $logger->notice("inviteObserversToRateAction: Send observer invitation email from " . $senderEmail . " to :".$email);
        }

        $event = "Invited observers to view fellowship application ID " . $id . " ".$entity->getUser().".";
        $this->sendConfirmationEmail($emails,$entity,$event,$request);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode("ok"));
        return $response;
    }


    #[Route(path: '/download-interview-applicants-list-pdf/{currentYear}/{fellappTypeId}/{fellappIds}', name: 'fellapp_download_interview_applicants_list_pdf', methods: ['GET'])]
    public function downloadInterviewApplicantsListAction(Request $request, $currentYear, $fellappTypeId, $fellappIds) {

        if( false == $this->isGranted('ROLE_FELLAPP_COORDINATOR') &&
            false == $this->isGranted('ROLE_FELLAPP_DIRECTOR') &&
            false == $this->isGranted('ROLE_FELLAPP_INTERVIEWER') &&
            false == $this->isGranted('ROLE_FELLAPP_OBSERVER')
        ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
        $em = $this->getDoctrine()->getManager();
        $fellowshipSubspecialty = null;
        $institutionNameFellappName = "";

        if( $fellappTypeId && $fellappTypeId > 0 ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            $fellowshipSubspecialty = $em->getRepository(FellowshipSubspecialty::class)->find($fellappTypeId);
        }

        if( $fellowshipSubspecialty ) {
            $institution = $fellowshipSubspecialty->getInstitution();
            $institutionNameFellappName = $institution." ".$fellowshipSubspecialty." ";
        }

        //url=http://collage.med.cornell.edu/order/fellowship-applications/show/2
        //$url = $this->generateUrl('fellapp_show',array('id' => 2),true);
        //echo "url=".$url."<br>";
        //exit();

        //[YEAR] [WCMC (top level of actual institution)] [FELLOWSHIP-TYPE] Fellowship Candidate Data generated on [DATE] at [TIME] EST.xls
        //"Interview Evaluations for FELLOWSHIP-TYPE YEAR generated for LoggedInUserFirstName LoggedInUserLastName on DATE TIME EST.docx
        $fileName = $currentYear." ".$institutionNameFellappName."Interview Evaluations generated on ".date('m/d/Y H:i').".pdf";
        $fileName = str_replace("  ", " ", $fileName);
        $fileName = str_replace(" ", "-", $fileName);
        $fileName = str_replace(",", "-", $fileName);

        //take care of authentication
        $session = $request->getSession(); //$this->container->get('session');
        $session->save();
        session_write_close();
        $PHPSESSID = $session->getId();

    if( 1 ) {

        $pageUrl = $this->generateUrl('fellapp_interview_applicants_list', array('fellappIds'=>$fellappIds), UrlGeneratorInterface::ABSOLUTE_URL); // use absolute path!

        $output = $fellappRepGen->getSnappyPdf()->getOutput($pageUrl, array(
            'cookie' => array(
                'PHPSESSID' => $PHPSESSID
            )));

    } else {

//        $fellappUtil = $this->container->get('fellapp_util');
//        $entities = $fellappUtil->createInterviewApplicantList( $fellappIds );
//
//        $html = "";
//        foreach( $entities as $fellapp ) {
//            $interviewModalHtml = $this->container->get('templating')->render('AppFellAppBundle/Interview/applicant-interview-info.html.twig',
//                array(
//                    'entity' => $fellapp,
//                    'pathbase' => 'fellapp',
//                    'sitename' => $this->getParameter('fellapp.sitename')
//                )
//            );
//
//            $html = $html . '<div style="overflow: hidden; page-break-after:always;">'.
//                    $interviewModalHtml.
//                    '</div>';
//        }
//
//        $output = $this->container->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
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


    #[Route(path: '/download-interview-applicants-list-doc/{currentYear}/{fellappTypeId}/{fellappIds}', name: 'fellapp_download_interview_applicants_list_doc', methods: ['GET'])]
    public function downloadInterviewApplicantsListDocAction(Request $request, $currentYear, $fellappTypeId, $fellappIds) {

        if( false == $this->isGranted('ROLE_FELLAPP_COORDINATOR') &&
            false == $this->isGranted('ROLE_FELLAPP_DIRECTOR') &&
            false == $this->isGranted('ROLE_FELLAPP_INTERVIEWER') &&
            false == $this->isGranted('ROLE_FELLAPP_OBSERVER')
        ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $fellowshipSubspecialty = null;
        $institutionNameFellappName = "";

        if( $fellappTypeId && $fellappTypeId > 0 ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            $fellowshipSubspecialty = $em->getRepository(FellowshipSubspecialty::class)->find($fellappTypeId);
        }

        if( $fellowshipSubspecialty ) {
            $institution = $fellowshipSubspecialty->getInstitution();
            $institutionNameFellappName = $institution." ".$fellowshipSubspecialty." ";
        }

        //[YEAR] [WCMC (top level of actual institution)] [FELLOWSHIP-TYPE] Fellowship Candidate Data generated on [DATE] at [TIME] EST.xls
        //"Interview Evaluations for FELLOWSHIP-TYPE YEAR generated for LoggedInUserFirstName LoggedInUserLastName on DATE TIME EST.docx
        $fileName = $currentYear." ".$institutionNameFellappName."Interview Evaluations generated on ".date('m/d/Y H:i').".doc";
        $fileName = str_replace("  ", " ", $fileName);
        $fileName = str_replace(" ", "-", $fileName);
        $fileName = str_replace(",", "-", $fileName);

        //get filtered fell applications
        $fellappUtil = $this->container->get('fellapp_util');
        $entities = $fellappUtil->createInterviewApplicantList( $fellappIds );

//        $interviewsDocHtml = $this->container->get('templating')->render('AppFellAppBundle/Interview/applicants-interview-info-doc.html.twig',
//            array(
//                'entities' => $entities,
//                'pathbase' => 'fellapp',
//                'cycle' => 'show',
//                'sitename' => $this->getParameter('fellapp.sitename')
//            )
//        );
        $interviewsDocHtml = $this->container->get('twig')->render('AppFellAppBundle/Interview/applicants-interview-info-doc.html.twig',
            array(
                'entities' => $entities,
                'pathbase' => 'fellapp',
                'cycle' => 'show',
                'sitename' => $this->getParameter('fellapp.sitename')
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

    #[Route(path: '/interview-applicants-list/{fellappIds}', name: 'fellapp_interview_applicants_list', methods: ['GET'])]
    #[Template('AppFellAppBundle/Interview/applicants-interview-info.html.twig')]
    public function showInterviewApplicantsListAction(Request $request, $fellappIds) {

        if( false == $this->isGranted('ROLE_FELLAPP_COORDINATOR') &&
            false == $this->isGranted('ROLE_FELLAPP_DIRECTOR') &&
            false == $this->isGranted('ROLE_FELLAPP_INTERVIEWER') &&
            false == $this->isGranted('ROLE_FELLAPP_OBSERVER')
        ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $entities = $fellappUtil->createInterviewApplicantList( $fellappIds );

        return array(
            'entities' => $entities,
            'pathbase' => 'fellapp',
            'cycle' => 'show',
            'sitename' => $this->getParameter('fellapp.sitename')
        );
    }

} 