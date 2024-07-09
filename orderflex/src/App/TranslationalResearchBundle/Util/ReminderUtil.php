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

namespace App\TranslationalResearchBundle\Util;



use App\TranslationalResearchBundle\Entity\Project; //process.py script: replaced namespace by ::class: added use line for classname=Project


use App\UserdirectoryBundle\Entity\Logger; //process.py script: replaced namespace by ::class: added use line for classname=Logger


use App\TranslationalResearchBundle\Entity\TransResRequest; //process.py script: replaced namespace by ::class: added use line for classname=TransResRequest
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
//use Doctrine\Common\Collections\ArrayCollection;
use App\TranslationalResearchBundle\Entity\Invoice;
//use App\TranslationalResearchBundle\Entity\InvoiceItem;
//use App\TranslationalResearchBundle\Entity\ReminderEmail;
//use App\TranslationalResearchBundle\Entity\TransResSiteParameters;
//use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


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

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {
        $this->container = $container;
        $this->em = $em;
    }


    public function sendReminderUnpaidInvoices($showSummary=false, $testing=false) {
        $transresUtil = $this->container->get('transres_util');
        $logger = $this->container->get('logger');

        $resultArr = array();

        $projectSpecialties = $transresUtil->getTransResProjectSpecialties(false);
        $logger->notice("Found ".count($projectSpecialties)." TRP project specialties");

        foreach($projectSpecialties as $projectSpecialty) {
            $results = $this->sendReminderUnpaidInvoicesBySpecialty($projectSpecialty,$showSummary,$testing);
            if( $results ) {
                $resultArr[] = $results;
            }
            if( $testing ) {
                break; //try to do it only once in testing mode
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
        $logger->notice($result);

        return $result;
    }
    public function sendReminderUnpaidInvoicesBySpecialty( $projectSpecialty, $showSummary=false, $testing=false ) {
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');
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

        //$testing = false;
        //$testing = true;

        //$invoiceReminderSchedule: invoiceDueDateMax,reminderIntervalMonths,maxReminderCount (i.e. 3,3,5)
        $invoiceReminderSchedule = $transresUtil->getTransresSiteProjectParameter('invoiceReminderSchedule',null,$projectSpecialty); //6,9,12,15,18

        //testing
        if( $testing ) {
            $invoiceReminderSchedule = "1,1,5";
        }

        if( $invoiceReminderSchedule ) {
            $invoiceReminderScheduleArr = explode(",",$invoiceReminderSchedule);
            if( count($invoiceReminderScheduleArr) == 3 ) {
                $invoiceDueDateMax = $invoiceReminderScheduleArr[0];    //over due in months (integer)
                $reminderInterval = $invoiceReminderScheduleArr[1];     //reminder interval in months (integer)
                $maxReminderCount = $invoiceReminderScheduleArr[2];     //max reminder count (integer)
            }
        } else {
            $logger->error("No invoiceReminderSchedule is set for ".$projectSpecialty);
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
            $logger->notice("invoiceDueDateMax is not set. Invoice reminder emails are not sent."." projectSpecialty=".$projectSpecialty);
            return "invoiceDueDateMax is not set. Invoice reminder emails are not sent.";
        }
        if( !$reminderInterval ) {
            $logger->notice("reminderInterval is not set. Invoice reminder emails are not sent."." projectSpecialty=".$projectSpecialty);
            return "reminderInterval is not set. Invoice reminder emails are not sent.";
        }
        if( !$maxReminderCount ) {
            $logger->notice("maxReminderCount is not set. Invoice reminder emails are not sent."." projectSpecialty=".$projectSpecialty);
            return "maxReminderCount is not set. Invoice reminder emails are not sent.";
        }

        $invoiceDueDateMax = trim((string)$invoiceDueDateMax);
        $reminderInterval = trim((string)$reminderInterval);
        $maxReminderCount = trim((string)$maxReminderCount);

        $params = array();

        $invoiceReminderSubject = $transresUtil->getTransresSiteProjectParameter('invoiceReminderSubject',null,$projectSpecialty);
        if( !$invoiceReminderSubject ) {
            //$invoiceReminderSubject = "[TRP] Translational Research Unpaid Invoice Reminder: [[INVOICE ID]]";
            $invoiceReminderSubject = "[TRP] Unpaid Invoice Reminder from the ".$transresUtil->getBusinessEntityName().": [[INVOICE ID]]";
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

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $repository = $this->em->getRepository(Invoice::class);
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');

        $dql->leftJoin('invoice.transresRequest','transresRequest');
        $dql->leftJoin('transresRequest.project','project');
        $dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $dql->where("projectSpecialty.id = :specialtyId");
        $params["specialtyId"] = $projectSpecialty->getId();

        $dql->andWhere("invoice.status = :unpaid AND invoice.latestVersion = TRUE"); //Unpaid/Issued
        $params["unpaid"] = "Unpaid/Issued";

        if( !$testing ) {
            /////////1. (dueDate < currentDate - invoiceDueDateMax) //////////////
            //overDueDate = currentDate - invoiceDueDateMax;
            $overDueDate = new \DateTime("-" . $invoiceDueDateMax . " months");
            //echo "overDueDate=".$overDueDate->format('Y-m-d H:i:s').$newline;
            $dql->andWhere("invoice.dueDate IS NOT NULL AND invoice.dueDate < :overDueDate");
            $params["overDueDate"] = $overDueDate->format('Y-m-d H:i:s');
            ////////////// EOF //////////////

            /////////.2 (invoiceLastReminderSentDate IS NULL OR invoiceLastReminderSentDate < currentDate - reminderInterval) ///////////
            $overDueReminderDate = new \DateTime("-" . $reminderInterval . " months");
            $dql->andWhere("invoice.invoiceLastReminderSentDate IS NULL OR invoice.invoiceLastReminderSentDate < :overDueReminderDate");
            $params["overDueReminderDate"] = $overDueReminderDate->format('Y-m-d H:i:s');
            ////////////// EOF //////////////

            /////////3. (invoiceReminderCount < maxReminderCount) ////////////////////////
            $dql->andWhere("invoice.invoiceReminderCount IS NULL OR invoice.invoiceReminderCount < :maxReminderCount");
            $params["maxReminderCount"] = $maxReminderCount;
            ////////////// EOF //////////////
        }

        if( $testing ) {
            $dql->orWhere("invoice.id=1 OR invoice.id=2");
            //$dql->orWhere("invoice.id=1");
            //$dql->andWhere("invoice.id=4760"); //dev
            //$dql->andWhere("invoice.id=4730"); //test
        }

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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
        //echo "$projectSpecialty count invoices=".count($invoices)."<br>";

        if( $showSummary ) {
            return $invoices;
        }


        //testing
        //$testInvoiceId = 'APCP1002-REQ17582-V1'; //dev
        //$testInvoiceId = 'APCP606-REQ20735-V1'; //prod
        //$testInvoice = $this->em->getRepository('AppTranslationalResearchBundle:Invoice')->findOneByOid($testInvoiceId);
        //$invoices = array($testInvoice);

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
            if( !$testing ) {
                $invoice->setInvoiceLastReminderSentDate(new \DateTime());
            }

            //set reminder counter
            $invoiceReminderCounter = $invoice->getInvoiceReminderCount();
            if( !$invoiceReminderCounter ) {
                $invoiceReminderCounter = 0;
            }
            $invoiceReminderCounter = intval($invoiceReminderCounter);
            $invoiceReminderCounter++;
            if( !$testing ) {
                $invoice->setInvoiceReminderCount($invoiceReminderCounter);
            }

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
            //$logger->notice("Invoice OID=".$invoice->getOid()."; invoicePDF=".$invoicePDF);
            if ($invoicePDF) {

                //It's working when run from the web.
                //On cron getAttachmentEmailPath returns NULL because it uses getcwd()=/usr/share/httpd and as
                // the result is NULL
                $attachmentPath = $invoicePDF->getAttachmentEmailPath(); //test is implemented
                //Result: attachmentPath=
                //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex\public\
                //Uploaded\transres\InvoicePDF
                //\Invoice-PDF-APCP668-REQ14079-V1-Bing-He-generated-on-09-21-2018-at-12-12-15_UTC.pdf

                //It's working with cron
                if( !$attachmentPath ) {
                    //$attachmentPath = $invoicePDF->getAbsoluteUploadFullPath();
                    $attachmentPath = $userServiceUtil->getDocumentAbsoluteUrl($invoicePDF);
                    //Result: http://127.0.0.1/Uploaded/transres/InvoicePDF/
                    //Invoice-PDF-APCP668-REQ14079-V1-Bing-He-generated-on-09-21-2018-at-12-12-15_UTC.pdf;
                }

                //TODO: live server => $attachmentPath is empty
                $logger->notice("invoicePDF exists: invoicePDF=".$invoicePDF."; attachmentPath=".$attachmentPath);
                if( $testing ) {
                    if( !$attachmentPath ) {
                        $logger->error("Attachment is NULL: invoice OID=".$invoice->getOid().", attachmentPath=".$attachmentPath);
                        //exit("Attachment is NULL: invoice OID=".$invoice->getOid().", attachmentPath=".$attachmentPath);
                    }
                }
            }
            $logger->notice('test email: invoice='.$invoice->getOid()."; invoicePDF=".$invoicePDF.
                "; attachmentPath=".$attachmentPath."; getcwd=".getcwd());
            //Cronjob: getcwd=/usr/share/httpd

            //replace [[...]]
            $transresRequest = $invoice->getTransresRequest();
            $project = $transresRequest->getProject();
            $invoiceReminderSubjectReady = $transresUtil->replaceTextByNamingConvention($invoiceReminderSubject,$project,$transresRequest,$invoice);
            $invoiceReminderBodyReady = $transresUtil->replaceTextByNamingConvention($invoiceReminderBody,$project,$transresRequest,$invoice);

            //testing:
            //$piEmailArr = array('oli2002@med.cornell.edu');
            //$ccs = 'oli2002@med.cornell.edu';
            if( $testing ) {
                $piEmailArr = array('oli2002@med.cornell.edu');
                $invoiceReminderEmail = 'oli2002@med.cornell.edu';
                $ccs = $invoiceReminderEmail;
                $invoiceReminderSubjectReady = "Testing email - please ignore. ".$invoiceReminderSubjectReady;
                $invoiceReminderBodyReady = "Testing email - please ignore. ".$invoiceReminderBodyReady;
            }

            //TODO: get results on Monday after bulk reminder emails are sent. Check attachment url.
            //https://swiftmailer.symfony.com/docs/messages.html
            //Check allow_url_fopen, check permission
            //                    $emails,     $subject,                     $message,              $ccs=null, $fromEmail=null, $attachmentPath=null, $attachmentFilename=null
            $emailUtil->sendEmail($piEmailArr, $invoiceReminderSubjectReady, $invoiceReminderBodyReady, $ccs, $invoiceReminderEmail, $attachmentPath);

            $invoiceMsg = "Reminder email for the unpaid Invoice ".$invoice->getOid(). " has been sent to ".implode(";",$piEmailArr) . "; ccs:".$ccs.
            "<br>Subject: ".$invoiceReminderSubjectReady."<br>Body: ".$invoiceReminderBodyReady."<br>attachmentPath=".$attachmentPath;
            ////////////// EOF send email //////////////

            //EventLog
            if( !$testing ) {
                $userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'), $invoiceMsg, $systemuser, $invoice, null, $eventType);
            }

            $resultArr[] = $invoice->getOid();
            $sentInvoices++;

            //testing
            if( $testing ) {
                exit('test email: invoice='.$invoice->getOid()."; invoicePDF=".$invoicePDF."; attachmentPath=".$attachmentPath."; getcwd=".getcwd());
            }
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
        if( strpos((string)$state,'review') !== false ) {
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

        $projectReminderDelay = trim((string)$projectReminderDelay);

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

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $this->em->getRepository(Project::class);
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

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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
            $ccs = $transresUtil->getTransResAdminEmails($project,true,true); //send reminder email

            //replace comments
            if( strpos((string)$projectReminderBody, '[[PROJECT STATUS COMMENTS]]') !== false ) {
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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");
        $dql->innerJoin('logger.eventType', 'eventType');
        //$dql->leftJoin('logger.objectType', 'objectType');
        //$dql->leftJoin('logger.site', 'site');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
        $dql->where("logger.entityNamespace = 'App\TranslationalResearchBundle\Entity' AND logger.entityName = 'Project' AND logger.entityId = '".$project->getId()."'");

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Project Reminder Email";

        $dql->andWhere("logger.event LIKE :eventStr");
        $eventStr = "Reminder email for the Project " . $project->getOid() . " in the status '" . $stateStr . "'";
        $dqlParameters['eventStr'] = "%" . $eventStr . "%";

        $dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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
            $reminderDelay = trim((string)$reminderDelay);

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
            $reminderDelay = trim((string)$reminderDelay);

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
            $reminderDelay = trim((string)$reminderDelay);

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

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $repository = $this->em->getRepository(TransResRequest::class);
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

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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
            $ccs = $transresUtil->getTransResAdminEmails($project,true,true); //send reminder email

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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");
        $dql->innerJoin('logger.eventType', 'eventType');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
        $dql->where("logger.entityNamespace = 'App\TranslationalResearchBundle\Entity' AND logger.entityName = 'TransResRequest' AND logger.entityId = '".$request->getId()."'");

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Work Request Reminder Email";

        $dql->andWhere("logger.event LIKE :eventStr");
        $eventStr = "Reminder email for the Work Request " . $request->getOid() . " in the status '" . $state . "'";
        $dqlParameters['eventStr'] = "%" . $eventStr . "%";

        $dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        //echo $request->getOid().": loggers=".count($loggers)."<br>";
        //exit();

        return $loggers;
    }
    
    //221
    //I (cron expiring): “Upcoming” expiring projects - send only once
    //Add a field to the project object (visible only on view/edit pages and only to TRP Admin role and above) that stores the “Upcoming expiration notification state” = “Notified Once” / NULL
    //J (cron expired): send an email to the users with the “TRP Admin” role for every NEWLY expired project as described above - send only once
    //“Notification of expiration state” = “Notified Once” / NULL
    public function sendProjectExpirationReminder( $testing=false ) {
        $transresUtil = $this->container->get('transres_util');

        //$testing = false;
        //$testing = true;

        $expiringProjectCount = 0;
        $expiredProjectCount = 0;
        $autoCloseProjectCount = 0;

        $projectSpecialties = $transresUtil->getTransResProjectSpecialties(false);
        foreach($projectSpecialties as $projectSpecialty) {
            //1) expiring projects notification
            $expiringProjectCount = $expiringProjectCount + $this->sendExpiringProjectReminderPerSpecialty($projectSpecialty,$testing);

            //2) expired projects notification
            $expiredProjectCount = $expiredProjectCount + $this->sendExpiredProjectReminderPerSpecialty($projectSpecialty,$testing);

            //3) auto-closure after expiration
            $autoCloseProjectCount = $autoCloseProjectCount + $this->closeExpiredProjectPerSpecialty($projectSpecialty,$testing);
        }

        if( $testing ) {
            echo "expiringProjectCount=$expiringProjectCount <br>";
            echo "expiredProjectCount=$expiredProjectCount <br>";
            echo "autoCloseProjectCount=$autoCloseProjectCount <br>";
        }
        
        return "Notification emails: expiringProjectCount=$expiringProjectCount, expiredProjectCount=$expiredProjectCount, autoCloseProjectCount=$autoCloseProjectCount";
    }
    //Expiring - Upcoming expiration notification
    public function sendExpiringProjectReminderPerSpecialty($projectSpecialty, $testing=false) {
        $transresUtil = $this->container->get('transres_util');
        //$newline = "\r\n";
        $newline = "<br>";

        //We don't need projectExprApply, since we can use sendExpriringProjectEmail and sendExpiredProjectEmail
        //$projectExprApply = $transresUtil->getTransresSiteProjectParameter('projectExprApply',null,$projectSpecialty);

        //Use site settings parameters from (8 fields)
        $sendExpriringProjectEmail = $transresUtil->getTransresSiteProjectParameter('sendExpriringProjectEmail',null,$projectSpecialty);

        $projectExprDurationEmail = $transresUtil->getTransresSiteProjectParameter('projectExprDurationEmail',null,$projectSpecialty); //6 months

        //A2) projectExprApply - Apply project request expiration notification rule to this project request type: [Yes/No]
        //A) projectExprDurationEmail - Default number of months in advance of the project request expiration date when the
        //                              automatic notification requesting a progress report should be sent

        if( !$sendExpriringProjectEmail || !$projectExprDurationEmail ) {
            return false;
        }

        //now   <--projectExprDurationEmail--> expirationDate
        $now = new \DateTime();
        $nowStr = $now->format('Y-m-d');

        $addMonthStr = "+".$projectExprDurationEmail." months";
        $upcomingDeadline = new \DateTime($addMonthStr); //now + duration
        //if( $upcomingDeadline > $expirationDate ) => send email
        //echo $projectSpecialty.": projectExprDurationEmail=$projectExprDurationEmail, upcomingDeadline=".$upcomingDeadline->format('Y-m-d H:i:s')."<br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $this->em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $dql->where("projectSpecialty.id = :specialtyId");
        $params["specialtyId"] = $projectSpecialty->getId();

        //only for non-funded projects
        $dql->andWhere("project.funded != TRUE");

        //only with expectedExpirationDate
        $dql->andWhere("project.expectedExpirationDate IS NOT NULL");

//        $dql->andWhere("project.state = 'final_approved'");
        $dql->andWhere("project.state = :approved");
        $params['approved'] = "final_approved";

        //$dql->andWhere("project.expectedExpirationDate IS NOT NULL AND :upcomingDeadline > project.expectedExpirationDate");
        //$params["upcomingDeadline"] = $upcomingDeadline->format('Y-m-d H:i:s');

        $dql->andWhere('(project.expectedExpirationDate BETWEEN :nowDatetime and :upcomingDeadline)');
        $params['nowDatetime'] = $nowStr;
        $params['upcomingDeadline'] = $upcomingDeadline->format('Y-m-d');

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters(
            $params
        );

        $projects = $query->getResult();
        if( $testing ) {
            echo "$projectSpecialty count expiring projects=" . count($projects) . "$newline $newline";
        }

        $projectCounter = 0;

        foreach( $projects as $project ) {
            $res = $this->sendExpiritaionReminderEmail($project,$testing);
            if( $res ) {
                $projectCounter++;
            }
        }

        return $projectCounter;
    }
    public function sendExpiritaionReminderEmail( $project, $testing=false ) {
        if( !$project ) {
            return false;
        }

        //only for non-funded projects. clear for all funded projects.
        if( $project->getFunded() ) {
            return false;
        }

        //“Upcoming expiration notification state” = “Notified Once” / NULL
        $expirationNotifyCounter = $project->getExpirationNotifyCounter();
        if( $expirationNotifyCounter ) {
            return false;
        }

        $transresUtil = $this->container->get('transres_util');

        $sendExpriringProjectEmail = $transresUtil->getTransresSiteProjectParameter('sendExpriringProjectEmail',$project);
        if( $sendExpriringProjectEmail === false ) {
            return false;
        }

        $subject = $transresUtil->getTransresSiteProjectParameter('expiringProjectEmailSubject',$project);
        if( !$subject ) {
            $subject = "[TRP] Please submit a progress report for project ".$project->getOid();
        }
        $subject = $transresUtil->replaceTextByNamingConvention($subject,$project,null,null);

        $body = $transresUtil->getTransresSiteProjectParameter('expiringProjectEmailBody',$project);
        if( !$body ) {
            $projectExpirationStr = "Unknown";
            $expirationDate = $project->getExpectedExpirationDate();
            if( $expirationDate ) {
                $projectExpirationStr = $expirationDate->format("m/d/Y");
            }
            $body = "To enable you to continue to submit work requests for your project".
                " ".$project->getOid()." titled '".$project->getTitle()."', Center for Translational Pathology".
                " is requesting a progress report for this project. ".
                "According to our records, the expiration date for this project request is $projectExpirationStr.";
        }
        $body = $transresUtil->replaceTextByNamingConvention($body,$project,null,null);

        $from = $transresUtil->getTransresSiteProjectParameter('expiringProjectEmailFrom',$project);
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

        //send out the notification email to all project's requesters (all requesters except billing contact)
        //$emailArr = $transresUtil->getRequesterPisContactsSubmitterEmails($project);
        $emailArr = $transresUtil->getRequesterEmails($project,true,false); //$withBillingContact=false
        if( count($emailArr) == 0 ) {
            return false;
        }
        //echo "requesterEmails=".implode(",",$emailArr)."<br>";

        //Send email
        if( $testing === false ) {
            $emailUtil = $this->container->get('user_mailer_utility');
            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail($emailArr, $subject, $body, null, $from);
        }

        //This notification should only be sent once for a given combination of project id and Expiration date (so write it to the Event Log every time it is done)
        //“Upcoming expiration notification state” = “Notified Once” / NULL
        $project->incrementExpirationNotifyCounter();
        if( $testing === false ) {
            $this->em->flush();
        }

        //EventLog
        $eventType = "Project Expiration Reminder Email";
        $msg = "Expiration email sent to ".implode(",",$emailArr)."<br>".
            "Subject:<br>".$subject . "<br><br>Body:<br>" . $body;
        if( $testing === false ) {
            $transresUtil->setEventLog($project, $eventType, $msg);
        } else {
            echo "<br>EXPIRATION msg=$msg<br>";
        }
        
        return true;
    }

    //Expired - send expired notification
    //send an email to the users with the “TRP Admin” role for every NEWLY expired project
    public function sendExpiredProjectReminderPerSpecialty($projectSpecialty, $testing=false) {

        $transresUtil = $this->container->get('transres_util');
        //$newline = "\r\n";
        $newline = "<br>";

        //If the “Send reminder” variable for this given project request type is set to “Yes” (in A2 above) and the number of day in A above is set (?)

        //Use projectExprDurationChangeStatus in days (90 days)?
        $projectExprDurationChangeStatus = $transresUtil->getTransresSiteProjectParameter('projectExprDurationChangeStatus',null,$projectSpecialty);
        if( !$projectExprDurationChangeStatus ) {
            return false;
        }

        //Use site settings parameters from (8 fields): 5,6,7,8
        $sendExpiredProjectEmail = $transresUtil->getTransresSiteProjectParameter('sendExpiredProjectEmail',null,$projectSpecialty);
        if( !$sendExpiredProjectEmail ) {
            return false;
        }

        //now   <--projectExprDurationEmail--> expirationDate
        $now = new \DateTime();
        $nowStr = $now->format('Y-m-d');
        //echo "nowStr=$nowStr <br>";

        //$projectExprDurationChangeStatus in days
        //$projectExprDurationChangeStatus = 120; //testing

        //echo "projectExprDurationChangeStatus=$projectExprDurationChangeStatus <br>";
        //$addDaysStr = "+".$projectExprDurationChangeStatus." days";
        $addDaysStr = "-".$projectExprDurationChangeStatus." days";
        $expirationDuration = new \DateTime($addDaysStr); //now - duration days
        $expirationDurationStr = $expirationDuration->format('Y-m-d');
        //echo "expirationDurationStr=$expirationDurationStr <br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $this->em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $dql->where("projectSpecialty.id = :specialtyId");
        $params["specialtyId"] = $projectSpecialty->getId();

        //only for non-funded projects. Ignore all funded projects
        $dql->andWhere("project.funded != TRUE");

        //only with expectedExpirationDate
        $dql->andWhere("project.expectedExpirationDate IS NOT NULL");

        //$dql->andWhere("project.state = 'final_approved'");
        $dql->andWhere("project.state = :approved");
        $params['approved'] = "final_approved";

        //$dql->andWhere(':nowDate > project.expectedExpirationDate');
        //$params['nowDate'] = $nowStr;

        //expirationDate ------------ now
        $dql->andWhere('(:nowDate > project.expectedExpirationDate)');
        $params['nowDate'] = $nowStr;

        //expirationDate ------------ now -------------- +90 days
        //$dql->andWhere('(:nowDate BETWEEN project.expectedExpirationDate and :expirationDuration)');

        //expirationDate ------------ +90 days ------------ now
        $dql->andWhere('(:expirationDuration < project.expectedExpirationDate)');

        $params['expirationDuration'] = $expirationDurationStr;

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters(
            $params
        );

        $projects = $query->getResult();
        if( $testing ) {
            echo "$projectSpecialty count expired projects=" . count($projects) . "$newline $newline";
        }

        $projectCounter = 0;

        foreach( $projects as $project ) {
            //echo "Expired project ".$project->getOid()."<br>";
            $res = $this->sendExpiredReminderEmail($project,$testing);
            if( $res ) {
                $projectCounter++;
            }
        }

        return $projectCounter;
    }
    public function sendExpiredReminderEmail( $project, $testing=false ) {
        if( !$project ) {
            return false;
        }

        //only for non-funded projects. clear for all funded projects.
        if( $project->getFunded() ) {
            return false;
        }

        //“Upcoming expiration notification state” = “Notified Once” / NULL
        $expiredNotifyCounter = $project->getExpiredNotifyCounter();
        if( $expiredNotifyCounter ) {
            return false;
        }

        $transresUtil = $this->container->get('transres_util');

        $sendExpiredProjectEmail = $transresUtil->getTransresSiteProjectParameter('sendExpiredProjectEmail',$project);
        if( $sendExpiredProjectEmail === false ) {
            return false;
        }

        $subject = $transresUtil->getTransresSiteProjectParameter('expiredProjectEmailSubject',$project);
        if( !$subject ) {
            $subject = "[TRP] Project ".$project->getOid()." has reached its expiration date";
        }
        $subject = $transresUtil->replaceTextByNamingConvention($subject,$project,null,null);

        $body = $transresUtil->getTransresSiteProjectParameter('expiredProjectEmailBody',$project);
        if( !$body ) {
            $projectExpirationStr = "Unknown";
            $expirationDate = $project->getExpectedExpirationDate();
            if( $expirationDate ) {
                $projectExpirationStr = $expirationDate->format("m/d/Y");
            }
            $body = "According to our records, the project ".$project->getOid()." titled '".$project->getTitle()."' has reached".
                " its expiration date of $projectExpirationStr.".
                " You may not be able to submit additional work requests for an expired project.";
        }
        $body = $transresUtil->replaceTextByNamingConvention($body,$project,null,null);

        $from = $transresUtil->getTransresSiteProjectParameter('expiredProjectEmailFrom',$project);
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

        //send out the notification email to all project's requesters (all requesters except billing contact)
        //$emailArr = $transresUtil->getRequesterPisContactsSubmitterEmails($project);
        $emailArr = $transresUtil->getRequesterEmails($project,true,false); //$withBillingContact=false
        if( count($emailArr) == 0 ) {
            return false;
        }
        //echo "requesterEmails=".implode(",",$emailArr)."<br>";

        //Send email
        if( $testing === false ) {
            $emailUtil = $this->container->get('user_mailer_utility');
            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail($emailArr, $subject, $body, null, $from);
        }

        //This notification should only be sent once
        //“Upcoming expired notification state” = “Notified Once” / NULL
        $project->incrementExpiredNotifyCounter();
        if( $testing === false ) {
            $this->em->flush();
        }

        //EventLog
        $eventType = "Project Expired Reminder Email";
        $msg = "Expired email sent to ".implode(",",$emailArr)."<br>".
            "Subject:<br>".$subject . "<br><br>Body:<br>" . $body;
        if( $testing === false ) {
            $transresUtil->setEventLog($project,$eventType,$msg);
        } else {
            echo "<br>EXPIRED msg=$msg<br>";
        }

        return true;
    }

    //K (cron auto-close): Auto-close project request
    public function closeExpiredProject( $projectSpecialty, $testing=false ) {

        //Apply project request auto-closure after expiration rule to this project request type: [Yes/No] and default to “Yes” by default
        //$projectExprApplyChangeStatus
        //$projectExprApplyChangeStatus = $transresUtil->getTransresSiteProjectParameter('projectExprApplyChangeStatus',$project);
        //if( $projectExprApplyChangeStatus === false ) {
        //    return false;
        //}

        //Default number of days after the project request expiration date when the project request status should be set to 'Closed'
        //(leave blank to never auto-close): [90] - default to 90 (days)
        //$projectExprDurationChangeStatus

        //Auto-close project request N days after expiration date (use the value from the field in A above)
        //$projectExprDuration

        //1)project expiration date is set when project created
        //2) cron: auto-close project after $projectExprDurationChangeStatus(90 days) after expiration date
        //   if now+$projectExprDurationChangeStatus(90 days) > expectedExpirationDate(project expiration date)

    }
    //K (cron auto-close): Auto-close project request
    public function closeExpiredProjectPerSpecialty($projectSpecialty, $testing=false) {

        $transresUtil = $this->container->get('transres_util');
        //$newline = "\r\n";
        $newline = "<br>";

        //Apply project request auto-closure after expiration rule to this project request type
        $projectExprApplyChangeStatus = $transresUtil->getTransresSiteProjectParameter('projectExprApplyChangeStatus',null,$projectSpecialty);
        if( !$projectExprApplyChangeStatus ) {
            return false;
        }

        //Default number of days after the project request expiration date when the project request status should be set to 'Closed'
        $projectExprDurationChangeStatus = $transresUtil->getTransresSiteProjectParameter('projectExprDurationChangeStatus',null,$projectSpecialty);
        if( !$projectExprDurationChangeStatus ) {
            return false;
        }

        //This automatic status switch should only be done ONCE per project ID + Expiration Date value combination (autoClosureCounter)

        //now   > expirationDate + $projectExprDurationChangeStatus(days)
        $now = new \DateTime();
        $nowStr = $now->format('Y-m-d');
        //echo "nowStr=$nowStr <br>";

        //$projectExprDurationChangeStatus in days
        //$projectExprDurationChangeStatus = 1; //testing
        //$projectExprDurationChangeStatus = 365*8; //testing

        //echo "projectExprDurationChangeStatus=$projectExprDurationChangeStatus <br>";
        $addDaysStr = "-".$projectExprDurationChangeStatus." days";
        $autoCloseDate = new \DateTime($addDaysStr); //now - duration days
        $autoCloseDateStr = $autoCloseDate->format('Y-m-d');
        //echo "autoCloseDateStr=$autoCloseDateStr <br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $this->em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $dql->where("projectSpecialty.id = :specialtyId");
        $params["specialtyId"] = $projectSpecialty->getId();

        //only for non-funded projects. Ignore all funded projects
        $dql->andWhere("project.funded != TRUE");

        //only with expectedExpirationDate
        $dql->andWhere("project.expectedExpirationDate IS NOT NULL");

        //$dql->andWhere("project.state = 'final_approved'");
        $dql->andWhere("project.state = :approved");
        $params['approved'] = "final_approved";

        //$dql->andWhere(':nowDate > project.expectedExpirationDate');
        //$params['nowDate'] = $nowStr;

        //expirationDate ------------ now
        $dql->andWhere('(:nowDate > project.expectedExpirationDate)');
        $params['nowDate'] = $nowStr;

        //expirationDate ------------ +90 days ------------ now
        $dql->andWhere('(:autoCloseDate > project.expectedExpirationDate)');
        $params['autoCloseDate'] = $autoCloseDateStr;

        $dql->orderBy("project.id","DESC");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters(
            $params
        );

        $projects = $query->getResult();
        if( $testing ) {
            echo "$projectSpecialty count auto-closure projects=" . count($projects) . "$newline $newline";
        }

        $projectCounter = 0;

        foreach( $projects as $project ) {

            if( $testing ) {
                $projectExpirationStr = "Unknown";
                $expirationDate = $project->getExpectedExpirationDate();
                if ($expirationDate) {
                    $projectExpirationStr = $expirationDate->format("m/d/Y");
                }
                echo $projectCounter . ": Auto-close project " . $project->getOid() . ", state=" . $project->getState() . ", funded=" . $project->isFunded() . ", exp=" . $projectExpirationStr . "<br>";
            }

            $res = $this->autoCloseExpiredProject($project,$testing);
            //$res = true;
            if( $res ) {
                $projectCounter++;
                break;
            }
        }

        return $projectCounter;
    }
    public function autoCloseExpiredProject( $project, $testing=false ) {
        if( !$project ) {
            return false;
        }

        //only for non-funded projects. clear for all funded projects.
        if( $project->getFunded() ) {
            return false;
        }

        //AutoClosureCounter = “Notified Once” / NULL
        $autoClosureCounter = $project->getAutoClosureCounter();
        if( $autoClosureCounter ) {
            return false;
        }

        $transresUtil = $this->container->get('transres_util');

        $projectExprApplyChangeStatus = $transresUtil->getTransresSiteProjectParameter('projectExprApplyChangeStatus',$project);
        if( $projectExprApplyChangeStatus === false ) {
            return false;
        }

        //Default number of days after the project request expiration date when the project request status should be set to 'Closed'
        $projectExprDurationChangeStatus = $transresUtil->getTransresSiteProjectParameter('projectExprDurationChangeStatus',$project);
        if( !$projectExprDurationChangeStatus ) {
            return false;
        }

        $projectExpirationStr = "Unknown";
        $expirationDate = $project->getExpectedExpirationDate();
        if( $expirationDate ) {
            $projectExpirationStr = $expirationDate->format("m/d/Y");
        }

        //Close project
        $project->setState("closed");

        //AutoClosureCounter = “Notified Once” / NULL
        $project->incrementAutoClosureCounter();

        if( $testing === false ) {
            $this->em->flush();
        }

        //////////////// email ////////////////
        $break = "<br>";
        $emailUtil = $this->container->get('user_mailer_utility');
        //$emailSubject = "Your project request ".$project->getOid()." has been auto closed";
        //Subject: Your project HP3528 has been marked as "inactive"
        $emailSubject = "Your project ".$project->getOid()." has been marked as \"inactive\"";

        //shown as a 'localhost'
//        $projectUrl = $this->container->get('router')->generate(
//            'translationalresearch_project_show',
//            array(
//                'id' => $project->getId(),
//            ),
//            UrlGeneratorInterface::ABSOLUTE_URL
//        );
//        $projectUrl = '<a href="'.$projectUrl.'">'.$projectUrl.'</a>';
        $projectUrl = $transresUtil->getProjectShowUrl($project);

        //$emailBody = "Your project request ".$project->getOid().
        //    " has been auto closed $projectExprDurationChangeStatus days after its expiration date $projectExpirationStr.";

        $trpAdminNameEmail = "";
        $adminsUsers = $transresUtil->getTransResAdminEmails($project,false,true);
        $adminsArr = array();
        $adminsCcs = array();
        foreach($adminsUsers as $adminsUser) {
            $adminsUserNameEmail = $adminsUser->getUsernameShortest()."";
            $adminsUserEmail = $adminsUser->getSingleEmail(false);
            if( $adminsUserEmail ) {
                $adminsCcs[] = $adminsUserEmail;
                $adminsUserNameEmail = $adminsUserNameEmail." at ".$adminsUserEmail;
            }
            $adminsArr[] = $adminsUserNameEmail;
        }
        if( count($adminsArr) > 0 ) {
            $trpAdminNameEmail = implode(", ",$adminsArr);
        }

        //TRPAdminFirstName TRPAdminLastName at [EMAIL]
        $emailBody = "Your project ".$project->getOid().
            " (\"".$project->getTitle()."\")".
            " has been automatically marked as \"inactive\" (\"closed\")".
            " since ".$projectExprDurationChangeStatus." days have passed after the expiration date of".
            " ".$projectExpirationStr.
            " documented in the system. You may not be able to submit additional work requests for this".
            " project as a result of this status change.".
            "".$break.$break.
            "If you feel the status of this project should remain active and".
            " the expiration date should be updated, please contact the administrator".
            " " . $trpAdminNameEmail
            ;

        $emailBody = $emailBody . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;

        $requesterEmails = $transresUtil->getRequesterMiniEmails($project);
        //$adminsCcs = $transresUtil->getTransResAdminEmails($project,true,true); //after project canceled
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);

        if( $testing === false ) {
            $emailUtil->sendEmail($requesterEmails, $emailSubject, $emailBody, $adminsCcs, $senderEmail);
        }
        //////////////// EOF email ////////////////

        //EventLog
        $eventType = "Project Canceled";
        $msg = "Project auto-close notification email sent to requesters=".implode(",",$requesterEmails)."; adminsCcs=".implode(",",$adminsCcs)."<br>".
            "Subject:<br>".$emailSubject . "<br><br>Body:<br>" . $emailBody;
        if( $testing === false ) {
            $transresUtil->setEventLog($project,$eventType,$msg);
        } else {
            echo "<br>Auto-Close msg=$msg<br>";
        }

        return true;
    }

//    public function autoCloseExpiredProjectTest( $project, $testing=false ) {
//        //TODO: test if it shown as a 'localhost'
//        $transresUtil = $this->container->get('transres_util');
//        $projectUrl = $transresUtil->getProjectShowUrl($project);
//
//        $projectUrl = '<a href="'.$projectUrl.'">'.$projectUrl.'</a>';
//        exit("projectUrl=$projectUrl");
//    }
}



