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

namespace Oleg\TranslationalResearchBundle\Util;


use Doctrine\Common\Collections\ArrayCollection;
use Oleg\TranslationalResearchBundle\Entity\Invoice;
use Oleg\TranslationalResearchBundle\Entity\InvoiceItem;
use Oleg\TranslationalResearchBundle\Entity\ReminderEmail;
use Oleg\TranslationalResearchBundle\Entity\TransResSiteParameters;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 8/25/2017
 * Time: 09:48 AM
 */
class ReminderUtil
{

    protected $container;
    protected $em;
    protected $secTokenStorage;
    protected $secAuth;

    public function __construct( $em, $container ) {
        $this->container = $container;
        $this->em = $em;
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
    }


    public function sendReminderUnpaidInvoices($showSummary=false) {
        $transresUtil = $this->container->get('transres_util');

        $resultArr = array();

        $projectSpecialties = $transresUtil->getTransResProjectSpecialties(false);
        foreach($projectSpecialties as $projectSpecialty) {
            $results = $this->sendReminderUnpaidInvoicesBySpecialty($projectSpecialty,$showSummary);
            if( $results ) {
                $resultArr[] = $results;
            }
        }

        if( $showSummary ) {
            return $resultArr;
        }

        if( count($resultArr) > 0 ) {
            $result = implode(", ", $resultArr);
        } else {
            $result = "There are no unpaid overdue invoices corresponding to the site setting parameters.";
        }

        return $result;
    }
    public function sendReminderUnpaidInvoicesBySpecialty( $projectSpecialty, $showSummary=false ) {
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');
        $logger = $this->container->get('logger');
        $systemuser = $userSecUtil->findSystemUser();

        $invoiceDueDateMax = null;
        $reminderInterval = null;
        $maxReminderCount = null;
        //$newline = "\n";
        //$newline = "<br>";
        $resultArr = array();
        //$sentInvoiceEmailsArr = array();
        $eventType = "Unpaid Invoice Reminder Email";
        $sentInvoices = 0;

        $testing = false;
        //$testing = true;

        //$invoiceReminderSchedule: invoiceDueDateMax,reminderIntervalMonths,maxReminderCount (i.e. 3,3,5)
        $invoiceReminderSchedule = $transresUtil->getTransresSiteProjectParameter('invoiceReminderSchedule',null,$projectSpecialty); //6,9,12,15,18

        if( $invoiceReminderSchedule ) {
            $invoiceReminderScheduleArr = explode(",",$invoiceReminderSchedule);
            if( count($invoiceReminderScheduleArr) == 3 ) {
                $invoiceDueDateMax = $invoiceReminderScheduleArr[0];    //over due in months (integer)
                $reminderInterval = $invoiceReminderScheduleArr[1];     //reminder interval in months (integer)
                $maxReminderCount = $invoiceReminderScheduleArr[2];     //max reminder count (integer)
            }
        } else {
            return "No invoiceReminderSchedule is set";
        }
        //testing
        if( $testing ) {
            echo "Warning testing mode!!! <br>";
            $invoiceDueDateMax = 1;
            $reminderInterval = 1;
            $maxReminderCount = 5;
        }

        if( !$invoiceDueDateMax ) {
            return "invoiceDueDateMax is not set. Invoice reminder emails are not sent.";
        }
        if( !$reminderInterval ) {
            return "reminderInterval is not set. Invoice reminder emails are not sent.";
        }
        if( !$maxReminderCount ) {
            return "maxReminderCount is not set. Invoice reminder emails are not sent.";
        }

        $invoiceDueDateMax = trim($invoiceDueDateMax);
        $reminderInterval = trim($reminderInterval);
        $maxReminderCount = trim($maxReminderCount);

        $params = array();

        $invoiceReminderSubject = $transresUtil->getTransresSiteProjectParameter('invoiceReminderSubject',null,$projectSpecialty);
        if( !$invoiceReminderSubject ) {
            $invoiceReminderSubject = "[TRP] Translational Research Unpaid Invoice Reminder: [[INVOICE ID]]";
        }

        $invoiceReminderBody = $transresUtil->getTransresSiteProjectParameter('invoiceReminderBody',null,$projectSpecialty);
        if( !$invoiceReminderBody ) {
            $invoiceReminderBody = "Our records show that we have not received the $[[INVOICE AMOUNT DUE]] payment for the attached invoice  [[INVOICE ID]] issued on [[INVOICE DUE DATE AND DAYS AGO]].";
        }

        $invoiceReminderEmail = $transresUtil->getTransresSiteProjectParameter('invoiceReminderEmail',null,$projectSpecialty);
        //echo "settings: $invoiceReminderSchedule, $invoiceReminderSubject, $invoiceReminderBody, $invoiceReminderEmail".$newline;
        //echo "invoiceReminderSchedule=$invoiceReminderSchedule".$newline;

        //Send email reminder email if (issueDate does not exist, so use dueDate):
        //1. (dueDate < currentDate - invoiceDueDateMax) AND
        //2. (invoiceLastReminderSentDate IS NULL OR invoiceLastReminderSentDate < currentDate - reminderInterval) AND
        //3. (invoiceReminderCount < maxReminderCount)
        //When email sent, set invoiceLastReminderSentDate=currentDate, invoiceReminderCount++

        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:Invoice');
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');

        $dql->leftJoin('invoice.transresRequest','transresRequest');
        $dql->leftJoin('transresRequest.project','project');
        $dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $dql->where("projectSpecialty.id = :specialtyId");
        $params["specialtyId"] = $projectSpecialty->getId();

        $dql->andWhere("invoice.status = :unpaid AND invoice.latestVersion = TRUE"); //Unpaid/Issued
        $params["unpaid"] = "Unpaid/Issued";

        /////////1. (dueDate < currentDate - invoiceDueDateMax) //////////////
        //overDueDate = currentDate - invoiceDueDateMax;
        $overDueDate = new \DateTime("-".$invoiceDueDateMax." months");
        //echo "overDueDate=".$overDueDate->format('Y-m-d H:i:s').$newline;
        $dql->andWhere("invoice.dueDate IS NOT NULL AND invoice.dueDate < :overDueDate");
        $params["overDueDate"] = $overDueDate->format('Y-m-d H:i:s');
        ////////////// EOF //////////////

        /////////.2 (invoiceLastReminderSentDate IS NULL OR invoiceLastReminderSentDate < currentDate - reminderInterval) ///////////
        $overDueReminderDate = new \DateTime("-".$reminderInterval." months");
        $dql->andWhere("invoice.invoiceLastReminderSentDate IS NULL OR invoice.invoiceLastReminderSentDate < :overDueReminderDate");
        $params["overDueReminderDate"] = $overDueReminderDate->format('Y-m-d H:i:s');
        ////////////// EOF //////////////

        /////////3. (invoiceReminderCount < maxReminderCount) ////////////////////////
        $dql->andWhere("invoice.invoiceReminderCount IS NULL OR invoice.invoiceReminderCount < :maxReminderCount");
        $params["maxReminderCount"] = $maxReminderCount;
        ////////////// EOF //////////////

        if( $testing ) {
            $dql->orWhere("invoice.id=1 OR invoice.id=2");
            //$dql->orWhere("invoice.id=1");
        }

        $query = $this->em->createQuery($dql);

        $query->setParameters(
//            array(
//                "unpaid" => "Unpaid/Issued",
//                "overDueDate" => $overDueDate->format('Y-m-d H:i:s'),
//                "overDueReminderDate" => $overDueReminderDate->format('Y-m-d H:i:s'),
//                "maxReminderCount" => $maxReminderCount
//            )
            $params
        );

        $invoices = $query->getResult();
        //echo "$projectSpecialty count invoices=".count($invoices)."$newline";

        if( $showSummary ) {
            return $invoices;
        }

        foreach($invoices as $invoice) {

//            $dueDateStr = null;
//            $dueDate = $invoice->getDueDate();
//            if( $dueDate ) {
//                $dueDateStr = $dueDate->format('Y-m-d');
//            }
//            $lastSentDateStr = null;
//            $lastSentDate = $invoice->getInvoiceLastReminderSentDate();
//            if( $lastSentDate ) {
//                $lastSentDateStr = $lastSentDate->format('Y-m-d');
//            }
            //echo "###Reminder email (ID#".$invoice->getId()."): dueDate=".$dueDateStr.", reminderConter=".$invoice->getInvoiceReminderCount().", lastSentDate=".$lastSentDateStr."$newline";
            //$msg = "Sending reminder email for Invoice ".$invoice->getOid();
            //": dueDate=".$dueDateStr.", lastSentDate=".$lastSentDateStr.", reminderEmailConter=".$invoice->getInvoiceReminderCount();

            $logger->notice("Sending reminder email for Invoice ".$invoice->getOid());

            //set last reminder date
            $invoice->setInvoiceLastReminderSentDate(new \DateTime());

            //set reminder counter
            $invoiceReminderCounter = $invoice->getInvoiceReminderCount();
            if( !$invoiceReminderCounter ) {
                $invoiceReminderCounter = 0;
            }
            $invoiceReminderCounter = intval($invoiceReminderCounter);
            $invoiceReminderCounter++;
            $invoice->setInvoiceReminderCount($invoiceReminderCounter);

            //save to DB (disable for testing)
            if( !$testing ) {
                $this->em->flush($invoice);
            }

            ////////////// send email //////////////
            $piEmailArr = $transresRequestUtil->getInvoicePis($invoice);
            if (count($piEmailArr) == 0) {
                //return "There are no PI and/or Billing Contact emails. Email has not been sent.";
                $resultArr[] = "There are no PI and/or Billing Contact emails. Email has not been sent for Invoice ".$invoice->getOid();
                continue;
            }

            $salesperson = $invoice->getSalesperson();
            if ($salesperson) {
                $salespersonEmail = $salesperson->getSingleEmail(false);
                if ($salespersonEmail) {
                    $ccs = $salespersonEmail;
                }
            }
            if (!$ccs) {
                $submitter = $invoice->getSubmitter();
                if ($submitter) {
                    $submitterEmail = $submitter->getSingleEmail(false);
                    if ($submitterEmail) {
                        $ccs = $submitterEmail;
                    }
                }
            }

            //Attachment: Invoice PDF
            $attachmentPath = null;
            $invoicePDF = $invoice->getRecentPDF();
            if ($invoicePDF) {
                $attachmentPath = $invoicePDF->getAbsoluteUploadFullPath();
            }

            //replace [[...]]
            $transresRequest = $invoice->getTransresRequest();
            $project = $transresRequest->getProject();
            $invoiceReminderSubjectReady = $transresUtil->replaceTextByNamingConvention($invoiceReminderSubject,$project,$transresRequest,$invoice);
            $invoiceReminderBodyReady = $transresUtil->replaceTextByNamingConvention($invoiceReminderBody,$project,$transresRequest,$invoice);

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $piEmailArr, $invoiceReminderSubjectReady, $invoiceReminderBodyReady, $ccs, $invoiceReminderEmail, $attachmentPath );

            $invoiceMsg = "Reminder email for the unpaid Invoice ".$invoice->getOid(). " has been sent to ".implode(";",$piEmailArr) . "; ccs:".$ccs.
            "<br>Subject: ".$invoiceReminderSubjectReady."<br>Body: ".$invoiceReminderBodyReady;
            ////////////// EOF send email //////////////

            //EventLog
            $userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'), $invoiceMsg, $systemuser, $invoice, null, $eventType);

            $resultArr[] = $invoice->getOid();
            $sentInvoices++;

        }//foreach $invoices

        if( $sentInvoices == 0 ) {
            $logger->notice("There are no unpaid overdue invoices corresponding to the site setting parameters for ".$projectSpecialty);
        }

        $result = implode(", ",$resultArr);

        return $result;
    }


    //Projects
    //$state - irb_review, admin_review, committee_review, final_review, irb_missinginfo, admin_missinginfo
    public function sendReminderReviewProjects($state,$showSummary=false) {
        $transresUtil = $this->container->get('transres_util');

        $resultArr = array();

        $projectSpecialties = $transresUtil->getTransResProjectSpecialties(false);
        foreach($projectSpecialties as $projectSpecialty) {
            $results = $this->sendReminderReviewProjectsBySpecialty($state,$projectSpecialty,$showSummary);
            if( $results ) {
                $resultArr[] = $results;
            }
        }

        if( $showSummary ) {
            return $resultArr;
        }

        if( count($resultArr) > 0 ) {
            $result = implode(", ", $resultArr);
        } else {
            $result = "There are no delayed projects corresponding to the site setting parameters.";
        }

        return $result;
    }
    public function sendReminderReviewProjectsBySpecialty( $state, $projectSpecialty, $showSummary=false ) {
        $transresUtil = $this->container->get('transres_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');
        $logger = $this->container->get('logger');
        //$user = $this->secTokenStorage->getToken()->getUser();

        $systemuser = $userSecUtil->findSystemUser();
        $stateStr = $transresUtil->getStateLabelByName($state);

        $newline = "\r\n";
        $resultArr = array();
        $eventType = "Project Reminder Email";
        $sentProjects = 0;
        $daysAgo = 7;

        $testing = false;
        //$testing = true;

        //review or missinginfo
        if( strpos($state,'review') !== false ) {
            $reviewShortState = "Review";
        } else {
            $reviewShortState = "Missinginfo";
        }

        //convert irb_review to IrbReview
        $modifiedState = str_replace("_"," ",$state);
        $modifiedState = ucwords($modifiedState);
        //echo "modifiedState=$modifiedState <br>";
        $modifiedState = str_replace(" ","",$modifiedState);
        //echo "modifiedState=$modifiedState <br>";

        //Pending project request reminder email delay (in days)
        $projectReminderDelayField = 'projectReminderDelay'.$modifiedState;
        $projectReminderDelay = $transresUtil->getTransresSiteProjectParameter($projectReminderDelayField,null,$projectSpecialty); //6,9,12,15,18
        if( !$projectReminderDelay ) {
            $projectReminderDelay = 14; //default 14 days
            //return "$projectReminderDelayField is not set. Project reminder emails are not sent.";
        }

        $projectReminderDelay = trim($projectReminderDelay);

        $params = array();

        $projectReminderSubjectField = 'projectReminderSubject'.$reviewShortState; //review or missinginfo
        $projectReminderSubject = $transresUtil->getTransresSiteProjectParameter($projectReminderSubjectField,null,$projectSpecialty);
        if( !$projectReminderSubject ) {
            if( $reviewShortState == "Review" ) {
                //[TRP] Project Request APCP123 is awaiting your review (“First 15 characters of the Project Title...” from PIFirstName PILastName)
                $projectReminderSubject = "[TRP] Project Request: [[PROJECT ID]] is awaiting your review ('[[PROJECT TITLE SHORT]]' from [[PROJECT PIS]])";
            }
            if( $reviewShortState == "Missinginfo" ) {
                //[TRP] Project Request APCP123 is awaiting your review (“First 15 characters of the Project Title...” from PIFirstName PILastName)
                $projectReminderSubject = "[TRP] Project Request: [[PROJECT ID]] is awaiting your review ('[[PROJECT TITLE SHORT]]' from [[PROJECT PIS]])";
            }

        }
        if( !$projectReminderSubject ) {
            $projectReminderSubject = "[TRP] Project Request: [[PROJECT ID]] ('[[PROJECT TITLE SHORT]]' from [[PROJECT PIS]]) is delayed";
        }

        $projectReminderBodyField = 'projectReminderBody'.$reviewShortState; //review or missinginfo
        $projectReminderBody = $transresUtil->getTransresSiteProjectParameter($projectReminderBodyField,null,$projectSpecialty);
        if( !$projectReminderBody ) {
            if( $reviewShortState == "Review" ) {
                //Project request APCP123 submitted on 01/01/2018 titled “Full Project Title” (PI: FirstName LastName) is in the “IRB Review” stage and is awaiting your review.
                $projectReminderBody = "Project request [[PROJECT ID]] submitted on [[PROJECT SUBMISSION DATE]] titled '[[PROJECT TITLE]]' (PI: [[PROJECT PIS]]) is in the '[[PROJECT STATUS]]' stage and is awaiting your review.";
                $projectReminderBody = $projectReminderBody . $newline.$newline . "Please visit the link below to submit your opinion:".$newline."[[PROJECT SHOW URL]]";
            }
            if( $reviewShortState == "Missinginfo" ) {
                //Body: Please provide the requested additional information to enable us to review your project request APCP123 (“Full project title”).
                //[If comment field is not empty, show this paragraph] The reviewer has specified the following feedback:
                //[Body of the comment that was associated with switching the status of this project request ti “Pending Additional Information...”]
                //To provide the requested information, please visit the following link:
                //[Link to project request where the additional information can be typed in]
                //To cancel this project request, please visit the following link:
                //[Link to a page that allows canceling or actually cancels the project request, but first must show the “Are you sure you would like to cancel this project request?”]
                $projectReminderBody = "Please provide the requested additional information to enable us to review your project request [[PROJECT ID]] ('[[PROJECT TITLE]]').".

                    //Comments associated with the “Pending additional information from submitter for IRB Review” status of the project request:
                    //use existing state string: $state = "Pending additional information from submitter for Admin Review";
                    $newline.$newline."Comments associated with the '[[PROJECT STATUS]]' status of the project request:". $newline."[[PROJECT STATUS COMMENTS]]".

                    $newline.$newline."To provide the requested information, please visit the following link:".$newline."[[PROJECT EDIT URL]]".
                    //$newline.$newline."To cancel this project request, please visit the following link:".$newline."[[PROJECT CANCEL URL]]". //not possible to show "Are you sure?" in email body
                    "";
            }
        }
        if( !$projectReminderBody ) {
            $projectReminderBody = "Project request [[PROJECT ID]] submitted on [[PROJECT SUBMISSION DATE]] titled '[[PROJECT TITLE]]' (PI: [[PROJECT PIS]]) is in the '[[PROJECT STATUS]]' stage.";
            $projectReminderBody = $projectReminderBody . $newline.$newline . "Please visit the link below to view this project:".$newline."[[PROJECT SHOW URL]]";
        }

        $reminderEmail = $transresUtil->getTransresSiteProjectParameter('invoiceReminderEmail',null,$projectSpecialty);

        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $dql->where("projectSpecialty.id = :specialtyId");
        $params["specialtyId"] = $projectSpecialty->getId();

        $dql->andWhere("project.state = :status"); //Unpaid/Issued
        $params["status"] = $state;   //"Unpaid/Issued";
        //$dql->andWhere("project.state = 'irb_review'"); //Unpaid/Issued

        ///////// use updateDate //////////////
        $overDueDate = new \DateTime("-".$projectReminderDelay." days");
        //echo "overDueDate=".$overDueDate->format('Y-m-d H:i:s').$newline;
        $dql->andWhere("project.updateDate IS NOT NULL AND project.updateDate < :overDueDate");
        $params["overDueDate"] = $overDueDate->format('Y-m-d H:i:s');
        ////////////// EOF //////////////


        //if( $testing ) {
            //$dql->orWhere("project.id=1 OR project.id=2");
            //$dql->orWhere("invoice.id=1");
        //}

        $query = $this->em->createQuery($dql);

        $query->setParameters(
            $params
        );

        $projects = $query->getResult();
        //echo "$projectSpecialty count projects=".count($projects)."$newline";

        //filter project by the last reminder email from event log
        $today = new \DateTime();
        $lateProjects = array();
        foreach($projects as $project) {
            $loggers = $this->getProjectReminderEmails($project,$state,$stateStr);
            if( count($loggers) > 0 ) {
                $lastLogger = $loggers[0];
                $sentDate = $lastLogger->getCreationdate();
                $dDiff = $sentDate->diff($today);
                $days = $dDiff->days; //sent $days ago
                //$days = intval($days);
                //echo "days=".$days."<br>";
                if( $days > $daysAgo ) {
                    $lateProjects[] = $project;
                }
            } else {
                $lateProjects[] = $project;
            }
        }

//        foreach($projects as $project) {
//            echo "project ".$project->getOid()."<br>";
//        }
        //exit('exit projects reminder');

        if( $showSummary ) {
            return $lateProjects;
        }

        //exit('exit projects reminder');

        foreach($lateProjects as $project) {

            $logger->notice("Sending reminder email for Project ".$project->getOid() . "(" . $state . ")");

            //set the latest update reminder datetime for this particular review
            //$reminderEmail = new ReminderEmail($user,$state);
            //$project->addReminderEmail($reminderEmail);

            //save to DB (disable for testing)
//            if( !$testing ) {
//                $this->em->flush($project);
//            }

            ////////////// send email //////////////
            //Case 1) to Reviewers (irb_review, admin_review, committee_review, final_review)
            //Only send the reminder to Primary committee reviewer for project requests in Committee review.
            $emailArr = array();
            if( $state == "irb_review" || $state == "admin_review" || $state == "final_review" ) {
                $emailArr = $transresUtil->getProjectReviewers($project,$state,true);
            }
            if( $state == "committee_review" ) {
                $emailArr = $transresUtil->getCommiteePrimaryReviewerEmails($project);
            }
            //Case 2) to Submitter, Contact, AND PI (irb_missinginfo, admin_missinginfo)
            if( $state == "irb_missinginfo" || $state == "admin_missinginfo" ) {
                $emailArr = $transresUtil->getRequesterPisContactsSubmitterEmails($project);
            }

            if( count($emailArr) == 0 ) {
                //return "There are no PI and/or Billing Contact emails. Email has not been sent.";
                $resultArr[] = "There are no email recipients. Email has not been sent for Project ".$project->getOid();
                continue;
            }

            //admins as $ccs
            $ccs = $transresUtil->getTransResAdminEmails($project->getProjectSpecialty(),true,true);

            //replace comments
            if( strpos($projectReminderBody, '[[PROJECT STATUS COMMENTS]]') !== false ) {
                $reviewComments = $transresUtil->getReviewComments($project,"<hr>",$state);
                if( $reviewComments ) {
                    $reviewComments = "<hr>" . $reviewComments;
                } else {
                    $reviewComments = "No comments";
                }
                $projectReminderBodyReady = str_replace("[[PROJECT STATUS COMMENTS]]", $reviewComments, $projectReminderBody);
            } else {
                $projectReminderBodyReady = $projectReminderBody;
            }

            //replace [[...]]
            $projectReminderSubjectReady = $transresUtil->replaceTextByNamingConvention($projectReminderSubject,$project,null,null);
            $projectReminderBodyReady = $transresUtil->replaceTextByNamingConvention($projectReminderBodyReady,$project,null,null);

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailArr, $projectReminderSubjectReady, $projectReminderBodyReady, $ccs, $reminderEmail );

            $projectMsg = "Reminder email for the Project " . $project->getOid() . " in the status '" . $stateStr . "'".
                " has been sent to ".implode(", ",$emailArr).
                "; ccs:".implode(", ",$ccs).
                "<br>Subject: ".$projectReminderSubjectReady."<br>Body: ".$projectReminderBodyReady;
            ////////////// EOF send email //////////////

            //EventLog
            if( !$testing ) {
                $userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'), $projectMsg, $systemuser, $project, null, $eventType);
            }

            $resultArr[] = $project->getOid();
            $sentProjects++;

        }//foreach $projects

        if( $sentProjects == 0 ) {
            $logger->notice("There are no delayed projects corresponding to the site setting parameters for " . $projectSpecialty);
        }

        $result = implode(", ",$resultArr);

        return $result;
    }
    public function getProjectReminderEmails( $project, $state, $stateStr=null ) {

        if( !$stateStr ) {
            $transresUtil = $this->container->get('transres_util');
            $stateStr = $transresUtil->getStateLabelByName($state);
        }

        $dqlParameters = array();

        //get the date from event log
        $repository = $this->em->getRepository('OlegUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");
        $dql->innerJoin('logger.eventType', 'eventType');
        //$dql->leftJoin('logger.objectType', 'objectType');
        //$dql->leftJoin('logger.site', 'site');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
        $dql->where("logger.entityNamespace = 'Oleg\TranslationalResearchBundle\Entity' AND logger.entityName = 'Project' AND logger.entityId = ".$project->getId());

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Project Reminder Email";

        $dql->andWhere("logger.event LIKE :eventStr");
        $eventStr = "Reminder email for the Project " . $project->getOid() . " in the status '" . $stateStr . "'";
        $dqlParameters['eventStr'] = "%" . $eventStr . "%";

        $dql->orderBy("logger.id","DESC");
        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        //echo $project->getOid().": loggers=".count($loggers)."<br>";
        //exit();

        return $loggers;
    }


    public function sendReminderPendingRequests( $state, $showSummary=false ) {
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        $resultArr = array();

        $projectSpecialties = $transresUtil->getTransResProjectSpecialties(false);
        foreach ($projectSpecialties as $projectSpecialty) {
            $results = $this->sendReminderPendingRequestsBySpecialty($state, $projectSpecialty, $showSummary);
            if ($results) {
                $resultArr[] = $results;
            }
        }

        if( $showSummary ) {
            return $resultArr;
        }

        if( count($resultArr) > 0 ) {
            $result = implode(", ", $resultArr);
        } else {
            $stateStr = $transresRequestUtil->getProgressStateLabelByName($state);
            $result = "There are no delayed work requests ($stateStr) corresponding to the site setting parameters.";
        }

        return $result;
    }
    //return array of transResRequests
    public function sendReminderPendingRequestsBySpecialty( $state, $projectSpecialty, $showSummary=false ) {
        //$result = "No delayed pending requests";

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');
        $logger = $this->container->get('logger');
        //$user = $this->secTokenStorage->getToken()->getUser();

        $systemuser = $userSecUtil->findSystemUser();
        $stateStr = $transresRequestUtil->getProgressStateLabelByName($state);

        $newline = "\r\n";
        $resultArr = array();
        $eventType = "Work Request Reminder Email";
        $sentProjects = 0;
        $daysAgo = 7;

        $reminderDelay = null;
        $reminderSubject = null;
        $reminderBody = null;

        $testing = false;
        //$testing = true;

        if( $state == "completed" ) {

            $reminderDelay = $transresUtil->getTransresSiteProjectParameter("completedRequestReminderDelay", null, $projectSpecialty);
            if (!$reminderDelay) {
                $reminderDelay = 4; //default 4 days
            }
            $reminderDelay = trim($reminderDelay);

            $reminderSubject = $transresUtil->getTransresSiteProjectParameter("completedRequestReminderSubject", null, $projectSpecialty);
            if (!$reminderSubject) {
                //Work Request APCP123-REQ456 is completed and the submitter is waiting to be notified
                $reminderSubject = "Work Request [[REQUEST ID]] is completed and the submitter is waiting to be notified";
            }

            $reminderBody = $transresUtil->getTransresSiteProjectParameter("completedRequestReminderBody", null, $projectSpecialty);
            if (!$reminderBody) {
                //To review the details of the completed work request APCP123-REQ456 and to set its status to “Completed and Notified”
                // in order to automatically notify the submitter via email, please visit the following link:
                $reminderBody = "To review the details of the completed work request [[REQUEST ID]]".
                " and to set its status to 'Completed and Notified', in order to".
                " automatically notify the submitter via email, please visit the following link:".
                $newline . "[[REQUEST CHANGE PROGRESS STATUS URL]]";
            }
            
        } elseif( $state == "completedNotified" ) {

            $reminderDelay = $transresUtil->getTransresSiteProjectParameter("completedNoInvoiceRequestReminderDelay", null, $projectSpecialty);
            if (!$reminderDelay) {
                $reminderDelay = 7; //default 7 days
            }
            $reminderDelay = trim($reminderDelay);

            $reminderSubject = $transresUtil->getTransresSiteProjectParameter("completedNoInvoiceRequestReminderSubject", null, $projectSpecialty);
            if (!$reminderSubject) {
                //Subject: [TRP] Please issue the invoice for work request APCP123-REQ456
                $reminderSubject = "Please issue the invoice for work request [[REQUEST ID]]";
            }

            $reminderBody = $transresUtil->getTransresSiteProjectParameter("completedNoInvoiceRequestReminderBody", null, $projectSpecialty);
            if (!$reminderBody) {
                //Body: Work request has had the status of “Completed and Notified” since 01/02/2018.
                //To issue the invoice for this work request, please visit the following link:
                //[URL where the invoice can be issued for this work request]
                $reminderBody = "Work request has had the status of '[[REQUEST PROGRESS STATUS]]' since [[REQUEST UPDATE DATE]].".
                    $newline.$newline.
                    "To issue the invoice for this work request, please visit the following link:".
                    $newline . "[[REQUEST NEW INVOICE URL]]";
            }

        } else {

            //Pending project request reminder email delay (in days)
            $reminderDelay = $transresUtil->getTransresSiteProjectParameter("pendingRequestReminderDelay", null, $projectSpecialty);
            if (!$reminderDelay) {
                $reminderDelay = 28; //default 28 days
            }
            $reminderDelay = trim($reminderDelay);

            $reminderSubject = $transresUtil->getTransresSiteProjectParameter("pendingRequestReminderSubject", null, $projectSpecialty);
            if (!$reminderSubject) {
                //Work Request APCP123-REQ456 is awaiting completion since [Submission Date]
                $reminderSubject = "Work Request [[REQUEST ID]] is awaiting completion since [[REQUEST SUBMISSION DATE]]";
            }

            $reminderBody = $transresUtil->getTransresSiteProjectParameter("pendingRequestReminderBody", null, $projectSpecialty);
            if (!$reminderBody) {
                //To review the details of the work request APCP123-Req456 with the current status of “Current Status”, please visit the following link:
                $reminderBody = "To review the details of the work request [[REQUEST ID]] with the current status of '[[REQUEST PROGRESS STATUS]]', please visit the following link:" .
                    $newline . "[[REQUEST SHOW URL]]";
            }

        }//if state

        if( !$reminderDelay ) {
            //exit("Delay days parameter is not specified.");
            return "Delay days parameter is not specified.";
        }
        if( !$reminderSubject ) {
            //exit("Delay days parameter is not specified.");
            return "Email subject parameter is not specified.";
        }
        if( !$reminderBody ) {
            return "Email body parameter is not specified.";
        }

        $reminderEmail = $transresUtil->getTransresSiteProjectParameter('invoiceReminderEmail',null,$projectSpecialty);

        $params = array();

        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin('request.project','project');
        $dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $dql->where("projectSpecialty.id = :specialtyId");
        $params["specialtyId"] = $projectSpecialty->getId();

        $dql->andWhere("request.progressState = :state");
        $params["state"] = $state;

        //ignore all exported requests because they didn't have completedNotified state and invoices
        //this is for "completed", imported thousands of work requests (~12000 request).
        $dql->andWhere("request.exportId IS NULL");

        ///////// use updateDate //////////////
        $overDueDate = new \DateTime("-".$reminderDelay." days");
        //echo "overDueDate=".$overDueDate->format('Y-m-d H:i:s').$newline;
        $dql->andWhere("request.updateDate IS NOT NULL AND request.updateDate < :overDueDate");
        $params["overDueDate"] = $overDueDate->format('Y-m-d H:i:s');
        ////////////// EOF //////////////

        if( $state == "completedNotified" ) {
            //no issued invoice
            $dql->leftJoin('request.invoices','invoices');
            $dql->andWhere("invoices.id IS NULL");

            //funded only
            $dql->andWhere("request.fundedAccountNumber IS NOT NULL");

            //TODO: add billing status? Paid or not?
        }

        $query = $this->em->createQuery($dql);

        $query->setParameters(
            $params
        );

        $requests = $query->getResult();
//        echo "$projectSpecialty count requests($stateStr)=".count($requests)."$newline"."<br>";
//        foreach($requests as $request) {
//            echo "Request ".$request->getOid()."; invoices=".count($request->getInvoices())."<br>";
//        }

        //filter project by the last reminder email from event log
        $today = new \DateTime();
        $lateRequests = array();
        foreach($requests as $request) {
            $loggers = $this->getRequestReminderEmails($request,$state);
            if( count($loggers) > 0 ) {
                $lastLogger = $loggers[0];
                $sentDate = $lastLogger->getCreationdate();
                $dDiff = $sentDate->diff($today);
                $days = $dDiff->days; //sent $days ago
                //$days = intval($days);
                //echo "days=".$days."<br>";
                if( $days > $daysAgo ) {
                    $lateRequests[] = $request;
                }
            } else {
                $lateRequests[] = $request;
            }
        }

        if( $showSummary ) {
            return $lateRequests;
        }

        //Technicians emails
        $emailArr = $transresRequestUtil->getTechnicianEmails($projectSpecialty);

        foreach($lateRequests as $request) {

            $logger->notice("Sending reminder email for Work Request ".$request->getOid() . "(" . $state . ")");

            $project = $request->getProject();

            ////////////// send email //////////////

            if( count($emailArr) == 0 ) {
                //return "There are no PI and/or Billing Contact emails. Email has not been sent.";
                $resultArr[] = "There are no email recipients. Email has not been sent for Work Request ".$request->getOid();
                continue;
            }

            //admins as $ccs
            $ccs = $transresUtil->getTransResAdminEmails($project->getProjectSpecialty(),true,true);

            //replace [[...]]
            $reminderSubjectReady = $transresUtil->replaceTextByNamingConvention($reminderSubject,$project,$request,null);
            $reminderBodyReady = $transresUtil->replaceTextByNamingConvention($reminderBody,$project,$request,null);

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailArr, $reminderSubjectReady, $reminderBodyReady, $ccs, $reminderEmail );

            $requestMsg = "Reminder email for the Work Request " . $request->getOid() . " in the status '" . $state . "'" . " (" . $stateStr . ")".
                " has been sent to ".implode(", ",$emailArr).
                "; ccs:".implode(", ",$ccs).
                "<br>Subject: ".$reminderSubjectReady."<br>Body: ".$reminderBodyReady;
            ////////////// EOF send email //////////////

            //EventLog
            if( !$testing ) {
                $userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'), $requestMsg, $systemuser, $request, null, $eventType);
            }

            $resultArr[] = $request->getOid();
            $sentProjects++;

        }//foreach $requests

        if( $sentProjects == 0 ) {
            $logger->notice("There are no delayed work requests ($stateStr) corresponding to the site setting parameters for " . $projectSpecialty);
        }

        $result = implode(", ",$resultArr);

        return $result;
    }
    public function getRequestReminderEmails( $request, $state ) {
        $dqlParameters = array();

        //get the date from event log
        $repository = $this->em->getRepository('OlegUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");
        $dql->innerJoin('logger.eventType', 'eventType');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
        $dql->where("logger.entityNamespace = 'Oleg\TranslationalResearchBundle\Entity' AND logger.entityName = 'TransResRequest' AND logger.entityId = ".$request->getId());

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Work Request Reminder Email";

        $dql->andWhere("logger.event LIKE :eventStr");
        $eventStr = "Reminder email for the Work Request " . $request->getOid() . " in the status '" . $state . "'";
        $dqlParameters['eventStr'] = "%" . $eventStr . "%";

        $dql->orderBy("logger.id","DESC");
        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        //echo $request->getOid().": loggers=".count($loggers)."<br>";
        //exit();

        return $loggers;
    }

}



