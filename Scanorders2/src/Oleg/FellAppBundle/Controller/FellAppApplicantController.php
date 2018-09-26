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

namespace Oleg\FellAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\FellAppBundle\Entity\FellowshipApplication;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class FellAppApplicantController extends Controller {



    /**
     * @Route("/interview-modal/{id}", name="fellapp_interview_modal")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Interview:modal.html.twig")
     */
    public function interviewModalAction(Request $request, $id) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_USER') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        $res = "";

        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }



        return array(
            'entity' => $entity,
            'pathbase' => 'fellapp',
            'sitename' => $this->container->getParameter('fellapp.sitename')
        );
    }




    /**
     * @Route("/interview-score-rank/{id}", name="fellapp_interviewe_score_rank")
     * @Method("GET")
     */
    public function intervieweScoreRankAction(Request $request, $id) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_USER') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        $res = "";

        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        if( $entity->getInterviewScore() == null || $entity->getInterviewScore() <= 0 ) {
            $response = new Response();
            $response->setContent($res);
            return $response;
        }

        $fellappType = $entity->getFellowshipSubspecialty();

        //$startDate = $entity->getStartDate();
        //$transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');
        //$startDateStr = $transformer->transform($startDate);

//        $applicants = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);
        $repository = $em->getRepository('OlegFellAppBundle:FellowshipApplication');
        $dql = $repository->createQueryBuilder("fellapp");
        //TODO: optimize this by a single query without foreach loop
//        ->select('((SELECT COUNT(1) AS num FROM stats  WHERE stats.marks  > s.marks ) + 1)  AS rank')
//        ->from('Stats s')
//        ->where('s.user_id = ?', $user_id )
//        ->orderBy('rank');
        //$dql->select('((SELECT COUNT(1) AS num FROM stats  WHERE stats.marks  > s.marks ) + 1)  AS rank');
        $dql->select('fellapp');
        $dql->leftJoin("fellapp.fellowshipSubspecialty", "fellowshipSubspecialty");

        $dql->where("fellowshipSubspecialty.id = " . $fellappType->getId() );

        $startDate = $entity->getStartDate();
        $startDateStr = $startDate->format('Y');
        $bottomDate = $startDateStr."-01-01";
        $topDate = $startDateStr."-12-31";
        $dql->andWhere("fellapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'" );

        $dql->andWhere("fellapp.interviewScore IS NOT NULL AND fellapp.interviewScore != '0'");

        $dql->orderBy("fellapp.interviewScore","ASC");

        $query = $em->createQuery($dql);
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

            $res = "Interview Score: ".
                $entity->getInterviewScore().
                " (".$rankStr." best of ".count($applicantions).
                " available in ".$fellappType." for ".$startDateStr.")";

        }

        $response = new Response();
        $response->setContent($res);
        return $response;
    }



    /**
     * @Route("/invite-interviewers-to-rate/{id}", name="fellapp_inviteinterviewerstorate")
     * @Method("GET")
     */
    public function inviteInterviewersToRateAction(Request $request, $id) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

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

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode("ok"));
        return $response;
    }

    /**
     * @Route("/invite-interviewer-to-rate/{interviewId}", name="fellapp_invite_single_interviewer_to_rate")
     * @Method("GET")
     */
    public function inviteSingleInterviewerToRateAction(Request $request, $interviewId) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        //$logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        $interview = $em->getRepository('OlegFellAppBundle:Interview')->find($interviewId);

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

        $this->get('session')->getFlashBag()->add(
            'notice',
            "A personal invitation email has been sent to ".$interview->getInterviewer()." ".$email
        );
        return $this->redirect( $this->generateUrl('fellapp_show',array('id' => $fellapp->getId())) );
    }


    public function sendInvitationEmail( $interview ) {

        $logger = $this->container->get('logger');
        $emailUtil = $this->get('user_mailer_utility');
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
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Email invitations to evaluate '.$appHref.' have not been sent. Please upload Itinerary and try again.'
            );

            $logger->error("send InvitationEmail: No recent itinerary found for fellapp ID=" . $fellapp->getId() );
            return null;
        }

        $attachmentPath = null;
        $recentReport = $fellapp->getTheMostRecentReport();
        if( $recentReport ) {
            $attachmentPath = $recentReport->getAbsoluteUploadFullPath();
        } else {
            $logger->error("send InvitationEmail: no recent report found for fellapp ID".$fellapp->getId());
        }

        //get email
        $email = $interviewer->getEmail();

        //$userutil = new UserUtil();
        //$adminemail = $userutil->getSiteSetting($em,'siteEmail');
        $user = $this->get('security.token_storage')->getToken()->getUser();
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

        $text .= "If you have any additional questions, please don't hesitate to email " . $senderEmail . $break.$break;

        $logger->notice("send InvitationEmail: Before send email to " . $email);

        //get interview date string
        $interviewDateStr = $interview->getInterviewDateStr();

        $cc = null; //"oli2002@med.cornell.edu";
        $emailUtil->sendEmail( $email, "Fellowship Candidate (".$applicant->getUsernameOptimal().$interviewDateStr.") Interview Application and Evaluation Form", $text, $cc, $senderEmail, $attachmentPath );

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
        $emailUtil = $this->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();
        $event = $event . "<br>" . $emailStr;
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$fellapp,$request,'Fellowship Application Rating Invitation Emails Resent');

        //return $this->redirect( $this->generateUrl('fellapp_home') );

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
        $recentReport = $fellapp->getTheMostRecentReport();
        if( $recentReport ) {
            $attachmentPath = $recentReport->getAbsoluteUploadFullPath();
        }

        $applicant = $fellapp->getUser();
        $emailUtil->sendEmail( $coordinatorEmails, "Fellowship Candidate (ID# ".$fellapp->getId()." ".$applicant->getUsernameOptimal().$interviewDateStr.") Interview Application and Evaluation Form", $event, null, $senderEmail, $attachmentPath );

        $logger->notice("send ConfirmationEmail: "."Fellowship Candidate (ID# ".$fellapp->getId()." ".$applicant->getUsernameOptimal().$interviewDateStr.": Send confirmation email from " . $senderEmail . " to coordinators:".implode(", ",$coordinatorEmails));
    }



    /**
 * @Route("/invite-observers-to-view/{id}", name="fellapp_inviteobservers")
 * @Method("GET")
 */
    public function inviteObserversToRateAction(Request $request, $id) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();


        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        $logger = $this->container->get('logger');
        $emailUtil = $this->get('user_mailer_utility');

        $emails = array();

        //get all interviews
        $user = $this->get('security.token_storage')->getToken()->getUser();
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
            $recentReport = $entity->getTheMostRecentReport();
            if( $recentReport ) {
                $attachmentPath = $recentReport->getAbsoluteUploadFullPath();
            }

            $emailUtil->sendEmail( $email, "Fellowship Candidate (".$applicant->getUsernameOptimal().") Application", $text, null, $senderEmail, $attachmentPath );

            $logger->notice("inviteObserversToRateAction: Send observer invitation email from " . $senderEmail . " to :".$email);
        }

        $event = "Invited observers to view fellowship application ID " . $id . " ".$entity->getUser().".";
        $this->sendConfirmationEmail($emails,$entity,$event,$request);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode("ok"));
        return $response;
    }


    /**
     * @Route("/download-interview-applicants-list-pdf/{currentYear}/{fellappTypeId}/{fellappIds}", name="fellapp_download_interview_applicants_list_pdf")
     * @Method("GET")
     */
    public function downloadInterviewApplicantsListAction(Request $request, $currentYear, $fellappTypeId, $fellappIds) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_INTERVIEWER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_OBSERVER')
        ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $fellowshipSubspecialty = null;
        $institutionNameFellappName = "";

        if( $fellappTypeId && $fellappTypeId > 0 ) {
            $fellowshipSubspecialty = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->find($fellappTypeId);
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

        //take care of authentication
        $session = $this->get('session');
        $session->save();
        session_write_close();
        $PHPSESSID = $session->getId();

    if( 1 ) {

        $pageUrl = $this->generateUrl('fellapp_interview_applicants_list', array('fellappIds'=>$fellappIds), UrlGeneratorInterface::ABSOLUTE_URL); // use absolute path!

        $output = $this->get('knp_snappy.pdf')->getOutput($pageUrl, array(
            'cookie' => array(
                'PHPSESSID' => $PHPSESSID
            )));

    } else {

        $fellappUtil = $this->container->get('fellapp_util');
        $entities = $fellappUtil->createInterviewApplicantList( $fellappIds );

        $html = "";
        foreach( $entities as $fellapp ) {
            $interviewModalHtml = $this->container->get('templating')->render('OlegFellAppBundle:Interview:applicant-interview-info.html.twig',
                array(
                    'entity' => $fellapp,
                    'pathbase' => 'fellapp',
                    'sitename' => $this->container->getParameter('fellapp.sitename')
                )
            );

            $html = $html . '<div style="overflow: hidden; page-break-after:always;">'.
                    $interviewModalHtml.
                    '</div>';
        }

        $output = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
            'cookie' => array(
                'PHPSESSID' => $PHPSESSID
            )));


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
     * @Route("/download-interview-applicants-list-doc/{currentYear}/{fellappTypeId}/{fellappIds}", name="fellapp_download_interview_applicants_list_doc")
     * @Method("GET")
     */
    public function downloadInterviewApplicantsListDocAction(Request $request, $currentYear, $fellappTypeId, $fellappIds) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_INTERVIEWER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_OBSERVER')
        ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $fellowshipSubspecialty = null;
        $institutionNameFellappName = "";

        if( $fellappTypeId && $fellappTypeId > 0 ) {
            $fellowshipSubspecialty = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->find($fellappTypeId);
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

        //get filtered fell applications
        $fellappUtil = $this->container->get('fellapp_util');
        $entities = $fellappUtil->createInterviewApplicantList( $fellappIds );

        $interviewsDocHtml = $this->container->get('templating')->render('OlegFellAppBundle:Interview:applicants-interview-info-doc.html.twig',
            array(
                'entities' => $entities,
                'pathbase' => 'fellapp',
                'cycle' => 'show',
                'sitename' => $this->container->getParameter('fellapp.sitename')
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
     * @Route("/interview-applicants-list/{fellappIds}", name="fellapp_interview_applicants_list")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Interview:applicants-interview-info.html.twig")
     */
    public function showInterviewApplicantsListAction(Request $request, $fellappIds) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_INTERVIEWER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_OBSERVER')
        ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $entities = $fellappUtil->createInterviewApplicantList( $fellappIds );

        return array(
            'entities' => $entities,
            'pathbase' => 'fellapp',
            'cycle' => 'show',
            'sitename' => $this->container->getParameter('fellapp.sitename')
        );
    }

} 