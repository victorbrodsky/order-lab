<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Oleg\TranslationalResearchBundle\Entity\Invoice;
use Oleg\TranslationalResearchBundle\Entity\TransResRequest;
use Oleg\TranslationalResearchBundle\Form\FilterInvoiceType;
use Oleg\TranslationalResearchBundle\Form\InvoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Oleg\UserdirectoryBundle\Entity\User;


class ReminderController extends Controller
{
    
    /**
     * @Route("/unpaid-invoice-reminder/show-summary", name="translationalresearch_unpaid_invoice_reminder_show")
     * @Route("/unpaid-invoice-reminder/send-emails", name="translationalresearch_unpaid_invoice_reminder_send")
     * @Method({"GET"})
     */
    public function unpaidInvoiceReminderAction( Request $request )
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $transresReminderUtil = $this->get('transres_reminder_util');

        $routeName = $request->get('_route');
        $showSummary = false;

        if( $routeName == "translationalresearch_unpaid_invoice_reminder_show" ) {
            $showSummary = true;
        }

        $results = $transresReminderUtil->sendReminderUnpaidInvoices($showSummary);

        if( $showSummary === true ) {
            $invoiceCounter = 0;

            foreach($results as $result) {
                $invoiceCounter = $invoiceCounter + count($result);
            }

            //send reminder emails after 6 months overdue every 3 months for 5 times
            $criterions = null;
            $criterionsArr = array();
            $projectSpecialties = $transresUtil->getTransResProjectSpecialties(false);
            foreach($projectSpecialties as $projectSpecialty) {
                $invoiceReminderSchedule = $transresUtil->getTransresSiteProjectParameter('invoiceReminderSchedule',null,$projectSpecialty); //6,9,12,15,18
                if( $invoiceReminderSchedule ) {
                    $invoiceReminderScheduleArr = explode(",",$invoiceReminderSchedule);
                    if( count($invoiceReminderScheduleArr) == 3 ) {
                        $invoiceDueDateMax = $invoiceReminderScheduleArr[0];    //over due in months (integer) - 6
                        $reminderInterval = $invoiceReminderScheduleArr[1];     //reminder interval in months (integer) - 3
                        $maxReminderCount = $invoiceReminderScheduleArr[2];     //max reminder count (integer) - 5

                        //AP/CP invoices due over 1 month(s) ago currently result in 5 automatic reminder emails every 1 month(s).
                        //Hematopathology invoices due over 1 month(s) ago currently result in 5 automatic reminder emails every 1 month(s).
                        //$criterionsArr[] = $projectSpecialty->getName()." - for over $invoiceDueDateMax months (reminder email will send every $reminderInterval months for $maxReminderCount times)";
                        $criterionsArr[] = $projectSpecialty->getName()." invoices due over $invoiceDueDateMax month(s) ago currently result in $maxReminderCount automatic reminder emails every $reminderInterval month(s).";
                    }
                }
            }
            if( count($criterionsArr) > 0 ) {
                $criterions = "<br>" . implode("<br>",$criterionsArr);
            }

            //The following invoices have remained unpaid for over X days:
            $title = "The following $invoiceCounter invoices have remained unpaid.".$criterions;
            //21 invoices have remained unpaid.
            $title = "$invoiceCounter invoices have remained unpaid. ".$criterions;

            return $this->render("OlegTranslationalResearchBundle:Reminder:unpaid-invoice-index.html.twig",
                array(
                    'title' => $title, //$invoiceCounter." Unpaid Invoices corresponding to the reminder schedule"."".$criterions,
                    'invoiceGroups' => $results,
                    'invoiceCounter' => $invoiceCounter
                )
            );
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            "Sending reminder emails for unpaid invoices: ".$results
        );

        return $this->redirectToRoute('translationalresearch_invoice_index_filter');
    }


    /**
     * http://127.0.0.1/order/translational-research/project-request-review-reminder/show-summary
     *
     * @Route("/project-request-review-reminder/show-summary", name="translationalresearch_project_reminder_show")
     * @Route("/project-request-review-reminder/send-emails", name="translationalresearch_project_reminder_send")
     * @Method({"GET"})
     */
    public function projectReminderAction( Request $request )
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $transresReminderUtil = $this->get('transres_reminder_util');

        $routeName = $request->get('_route');
        $showSummary = false;

        if( $routeName == "translationalresearch_project_reminder_show" ) {
            $showSummary = true;
        }

        $states = array("irb_review", "admin_review", "committee_review", "final_review", "irb_missinginfo", "admin_missinginfo");
        //$states = array("irb_review");
        $finalResults = array();

        $reminderDelayByStateProjectSpecialtyArr = array();

        foreach($states as $state) {
            $results = $transresReminderUtil->sendReminderReviewProjects($state,$showSummary);
            //echo "results count=".count($results)."<br>";
            //print_r($results);

            //overdue date
            $stateLabel = $transresUtil->getStateLabelByName($state);
            $modifiedState = str_replace("_","",$state);
            $projectReminderDelayField = 'projectReminderDelay'.$modifiedState;
            //$reminderDelayArr = array();
            $projectSpecialties = $transresUtil->getTransResProjectSpecialties(false);
            foreach($projectSpecialties as $projectSpecialtyObject) {
                $reminderDelay = $transresUtil->getTransresSiteProjectParameter($projectReminderDelayField, null, $projectSpecialtyObject);
                if (!$reminderDelay) {
                    $reminderDelay = 14; //default 14 days
                }
                //$reminderDelayArr[] = $reminderDelay . " days for " . $projectSpecialtyObject;
                $reminderDelayByStateProjectSpecialtyArr[$projectSpecialtyObject.""][$stateLabel] = $reminderDelay;
            }
            //$reminderDelayStr = implode(", ",$reminderDelayArr);


            //$finalResults[$stateLabel . " (over " . $reminderDelayStr . ")"] = $results;
            $finalResults[$stateLabel] = $results;
        }

        if( $showSummary === true ) {
            $projectCounter = 0;

            foreach($finalResults as $state=>$results) {
                foreach($results as $result) {
                    $projectCounter = $projectCounter + count($result);
                }
            }

//            echo "<pre>";
//            print_r($reminderDelayByStateProjectSpecialtyArr);
//            echo "</pre>";

            //The following periods are used to identify AP/CP project requests due for a reminder:
            // IRB Review stage - 14 days,
            // Awaiting Additional Review from Submitter: 14 days,
            // Administrative Review stage: 14 days,
            // Committee Review: 14 days,
            // Final Review: 14 days.
            $titleInfoArr = array();
            foreach($reminderDelayByStateProjectSpecialtyArr as $projectSpecialtyStr => $reminderDelayByStateProjectSpecialty) {
                $reminderArr = array();
                foreach($reminderDelayByStateProjectSpecialty as $stateLabel=>$reminderDelay) {
                    $reminderArr[] = $stateLabel." - ".$reminderDelay;
                }
                $titleInfoArr[] = "<br>The following periods are used to identify $projectSpecialtyStr project requests due for a reminder:<br>".implode(", ",$reminderArr);
            }

            //The following project requests are pending review for over X days:
            //$title = $projectCounter." Delayed Project Requests";
            //0 project requests are pending review.
            $title = "$projectCounter project requests are pending review.<br>" . implode("<br>",$titleInfoArr);


            return $this->render("OlegTranslationalResearchBundle:Reminder:project-request-reminder-index.html.twig",
                array(
                    'title' => $title,
                    'finalResults' => $finalResults,
                    'entityCounter' => $projectCounter,
                    'sendEmailPath' => 'translationalresearch_project_reminder_send',
                    'showPath' => 'translationalresearch_project_show',
                    'emptyMessage' => 'There are no delayed project requests corresponding to the site setting parameters'
                )
            );
        }

        foreach($finalResults as $state=>$results) {
            //$stateStr = $transresUtil->getStateLabelByName($state);
            $this->get('session')->getFlashBag()->add(
                'notice',
                "Sending reminder emails for delayed project requests ($state): " . $results
            );
        }

        return $this->redirectToRoute('translationalresearch_project_index');
    }


    /**
     * http://127.0.0.1/order/translational-research/work-request-pending-reminder/show-summary
     *
     * @Route("/work-request-pending-reminder/show-summary", name="translationalresearch_request_pending_reminder_show")
     * @Route("/work-request-pending-reminder/send-emails",  name="translationalresearch_request_pending_reminder_send")
     *
     * @Route("/work-request-completed-not-notified-reminder/show-summary", name="translationalresearch_request_completed_reminder_show")
     * @Route("/work-request-completed-not-notified-reminder/send-emails",  name="translationalresearch_request_completed_reminder_send")
     *
     * @Route("/work-request-completed-no-invoice-issued-reminder/show-summary", name="translationalresearch_request_completed_no_invoice_issued_reminder_show")
     * @Route("/work-request-completed-no-invoice-issued-reminder/send-emails",  name="translationalresearch_request_completed_no_invoice_issued_reminder_send")
     *
     * @Method({"GET"})
     */
    public function delayedRequestReminderAction( Request $request )
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresReminderUtil = $this->get('transres_reminder_util');

        $routeName = $request->get('_route');
        $showSummary = true;

        //$reminderDelayStr = "";
        //$reminderDelay = null;
        $reminderDelayArr = array();
        $reminderDelayByStateProjectSpecialtyArr = array();
        $projectSpecialties = $transresUtil->getTransResProjectSpecialties(false);

        if( strpos($routeName, "translationalresearch_request_pending_reminder") !== false ) {
            //$title = "Delayed Pending Work Requests";

            //The following work requests are pending completion for over X days:
            $title = "[[REQUEST_COUNTER]] following work requests are pending completion";
            //x AP/CP work requests are pending completion for over 28 days.
            //y Hematopathology work requests are pending completion for over 28 days.
            $title = "[[REQUEST_COUNTER]] [[PROJECT_SPECIALTY]] work requests are pending completion for over [[REMINDER_DELAY]] days.";
            foreach($projectSpecialties as $projectSpecialtyObject) {
                $reminderDelay = $transresUtil->getTransresSiteProjectParameter("pendingRequestReminderDelay", null, $projectSpecialtyObject);
                if (!$reminderDelay) {
                    $reminderDelay = 14; //default 14 days
                }
                //$reminderDelayArr[] = $reminderDelay . " days for " . $projectSpecialtyObject;
                $reminderDelayArr[$projectSpecialtyObject->getUppercaseShortName()] = $reminderDelay;
                //x AP/CP work requests are pending completion for over 28 days.
                $reminderDelayByStateProjectSpecialtyArr[$projectSpecialtyObject.""] = $reminderDelay;
            }
            //$reminderDelayStr = implode(", ",$reminderDelayArr);

            $sendEmailPath = "translationalresearch_request_pending_reminder_send";
            $states = array(
                'active',
                'pendingInvestigatorInput',
                'pendingHistology',
                'pendingImmunohistochemistry',
                'pendingMolecular',
                'pendingCaseRetrieval',
                'pendingTissueMicroArray',
                'pendingSlideScanning'
            );
            if( $routeName == "translationalresearch_request_pending_reminder_send" ) {
                $showSummary = false;
            }
        }

        if( strpos($routeName, "translationalresearch_request_completed_reminder") !== false ) {
            //$title = "Delayed Completed Work Requests";
            //The following work requests have been completed for over X days, but the request submitter has not been notified:
            //$title = "[[REQUEST_COUNTER]] following work requests have been completed, but the request submitter has not been notified";
            //0 AP/CP work requests have been completed for over 4 days without the submitter being notified.
            $title = "[[REQUEST_COUNTER]] [[PROJECT_SPECIALTY]] work requests have been completed for over [[REMINDER_DELAY]] days without the submitter being notified.";
            foreach($projectSpecialties as $projectSpecialtyObject) {
                $reminderDelay = $transresUtil->getTransresSiteProjectParameter("completedRequestReminderDelay", null, $projectSpecialtyObject);
                if (!$reminderDelay) {
                    $reminderDelay = 14; //default 14 days
                }
                //$reminderDelayArr[] = $reminderDelay . " days for " . $projectSpecialtyObject;
                $reminderDelayArr[$projectSpecialtyObject->getUppercaseShortName()] = $reminderDelay;
                $reminderDelayByStateProjectSpecialtyArr[$projectSpecialtyObject.""] = $reminderDelay;
            }
            //$reminderDelayStr = implode(", ",$reminderDelayArr);

            $sendEmailPath = "translationalresearch_request_completed_reminder_send";
            $states = array(
                'completed'
            );
            if( $routeName == "translationalresearch_request_completed_reminder_send" ) {
                $showSummary = false;
            }
        }

        if( strpos($routeName, "translationalresearch_request_completed_no_invoice_issued_reminder") !== false ) {
            //$title = "Completed and Notified Work Requests without Issued Invoice";
            //The following work requests have been completed for over X days without any invoices:
            //$title = "[[REQUEST_COUNTER]] following work requests have been completed without any invoices";
            //0 AP/CP work requests have been completed for over 7 days without any invoices.
            $title = "[[REQUEST_COUNTER]] [[PROJECT_SPECIALTY]] following work requests have been completed for over [[REMINDER_DELAY]] days without any invoices.";
            foreach($projectSpecialties as $projectSpecialtyObject) {
                $reminderDelay = $transresUtil->getTransresSiteProjectParameter("completedNoInvoiceRequestReminderDelay", null, $projectSpecialtyObject);
                if (!$reminderDelay) {
                    $reminderDelay = 14; //default 14 days
                }
                //$reminderDelayArr[] = $reminderDelay . " days for " . $projectSpecialtyObject;
                $reminderDelayArr[$projectSpecialtyObject->getUppercaseShortName()] = $reminderDelay;
                $reminderDelayByStateProjectSpecialtyArr[$projectSpecialtyObject.""] = $reminderDelay;
            }
            //$reminderDelayStr = implode(", ",$reminderDelayArr);

            $sendEmailPath = "translationalresearch_request_completed_no_invoice_issued_reminder_send";
            $states = array(
                'completedNotified'
            );
            if( $routeName == "translationalresearch_request_completed_no_invoice_issued_reminder_send" ) {
                $showSummary = false;
            }
        }

        $finalResults = array();

        //send email
        foreach($states as $state) {
            $results = $transresReminderUtil->sendReminderPendingRequests($state,$showSummary);
            $state = $transresRequestUtil->getProgressStateLabelByName($state);
            $finalResults[$state] = $results;
        }

        //$results = $transresReminderUtil->sendReminderPendingRequests($state,$showSummary);
        //echo "results count=".count($results)."<br>";
        //print_r($results);
        //$finalResults[$state] = $results;

        //sho summary
        if( $showSummary === true ) {
            $counter = 0;
            $titleInfo = array();

            $projectSpecialtyCounter = array();

            foreach($finalResults as $state=>$results) {
                foreach($results as $transResRequests) {
                    $counter = $counter + count($transResRequests);

                    //count project specialty reminders
                    foreach($reminderDelayArr as $projectSpecialtyShortName=>$reminderDays) {
                        foreach($transResRequests as $transResRequest) {
                            if (strpos($transResRequest->getOid(), $projectSpecialtyShortName) !== false) {
                                $projectSpecialtyCounter[$projectSpecialtyShortName]++;
                            }
                        }
                    }
                }
            }

            foreach($projectSpecialtyCounter as $projectSpecialtyShortName=>$counterDays) {
                $reminderDays = $reminderDelayArr[$projectSpecialtyShortName];
                //0 AP/CP work requests are pending completion for over 28 days.
                //$titleInfo[] = "$counterDays $projectSpecialtyShortName work requests are pending completion for over $reminderDays days.";
                //$title = "[[REQUEST_COUNTER]] [[PROJECT_SPECIALTY]] work requests are pending completion for over [[REMINDER_DELAY]] days.";
                $titleModified = str_replace("[[REQUEST_COUNTER]]",$counterDays,$title);
                $titleModified = str_replace("[[PROJECT_SPECIALTY]]",$projectSpecialtyShortName,$titleModified);
                $titleModified = str_replace("[[REMINDER_DELAY]]",$reminderDays,$titleModified);
                $titleInfo[] = $titleModified;
            }

            //The following periods are used to identify AP/CP project requests due for a reminder:
            // IRB Review stage - 14 days,
            // Awaiting Additional Review from Submitter: 14 days,
            // Administrative Review stage: 14 days,
            // Committee Review: 14 days,
            // Final Review: 14 days.
            $titleInfoArr = array();
            foreach($reminderDelayByStateProjectSpecialtyArr as $projectSpecialtyStr => $reminderDelayByStateProjectSpecialty) {
                $titleInfoArr[] = "$reminderDelayByStateProjectSpecialty days period are used to identify $projectSpecialtyStr project requests due for a reminder";
            }

            $titleStr = str_replace("[[REQUEST_COUNTER]]",$counter,$title);
            //$title = $titleStr . "<br>" . implode("<br>",$titleInfoArr);

            $titleNew = implode("<br>",$titleInfo);

            return $this->render("OlegTranslationalResearchBundle:Reminder:project-request-reminder-index.html.twig",
                array(
                    //'title' => $title . "<br><br><br>" . $titleNew,
                    'title' => $titleNew,
                    'finalResults' => $finalResults,
                    'entityCounter' => $counter,
                    'sendEmailPath' => $sendEmailPath,
                    'showPath' => 'translationalresearch_request_show',
                    'emptyMessage' => "There are no $titleStr corresponding to the site setting parameters"
                )
            );
        }

//        //send email
//        foreach($states as $state) {
//            $results = $transresReminderUtil->sendReminderPendingRequests($state,$showSummary);
//            $state = $transresRequestUtil->getProgressStateLabelByName($state);
//            $finalResults[$state] = $results;
//        }

        foreach($finalResults as $state=>$results) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                "Sending reminder emails for $title ($state): " . $results
            );
        }

        return $this->redirectToRoute('translationalresearch_request_index_filter');
    }
    

}
