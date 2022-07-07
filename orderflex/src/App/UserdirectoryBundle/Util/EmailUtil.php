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

namespace App\UserdirectoryBundle\Util;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\FellAppBundle\Controller\FellAppApplicantController;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;


/**
 * @author oli2002
 */
class EmailUtil {

    protected $em;
    protected $container;
    protected $mailer;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, MailerInterface $mailer ) {
        $this->em = $em;
        $this->container = $container;
        $this->mailer = $mailer;
    }

    //php bin/console swiftmailer:spool:send --env=prod
    //$emails: string, comma separated string or address objects (new Address('fabien@example.com'), new Address('fabien@example.com', 'Fabien'), Address::fromString('Fabien Potencier <fabien@example.com>'))
    //$ccs: same as $emails (optional)
    //$subject: string
    //$body: html email text
    //$attachmentPath: absolute path to the attachment file (optional)
    //$attachmentFilename: attachment file name (optional)
    //$fromEmail: site's email or system email will be used if null (optional)
    public function sendEmail( $emails, $subject, $body, $ccs=null, $fromEmail=null, $attachmentPath=null, $attachmentFilename=null ) {

        //testing
        //$emails = "oli2002@med.cornell.edu, cinava@yahoo.com";
        //$emails = "oli2002@med.cornell.edu";
        //$ccs = null;
        //$this->sendThisEmail($this->mailer);
        //dump($this->mailer);
        //exit('111');

        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');
        //set_time_limit(0); //set time limit to 600 sec == 10 min

        $sitenameAbbreviation = null;
        $url = null;

        //site specific settings
        $request = $this->container->get('request_stack')->getCurrentRequest();
        //$logger->notice("sendEmail: after Request");
        if( $request ) {
            $url = $request->getRequestUri();
            //$logger->notice("sendEmail: url=".$url);
        }

        if( $url ) {
            if (strpos((string)$url, "/translational-research/") !== false) {
                $sitenameAbbreviation = "translationalresearch";
                //adding “[TRP] “ in front of every notifications’ subject line
                $subject = "[TRP] " . $subject;
            }
            if (strpos((string)$url, "/directory/") !== false) {
                $sitenameAbbreviation = "employees";
                $subject = "[EMPLOYEE DIRECTORY] " . $subject;
            }
            if (strpos((string)$url, "/fellowship-applications/") !== false) {
                $sitenameAbbreviation = "fellapp";
                $subject = "[Fellowship Applications] " . $subject;
            }
            if (strpos((string)$url, "/residency-applications/") !== false) {
                $sitenameAbbreviation = "resapp";
                $subject = "[Residency Applications] " . $subject;
            }
            if (strpos((string)$url, "/call-log-book/") !== false) {
                $sitenameAbbreviation = "calllog";
                $subject = "[Call Log Book] " . $subject;
            }
            if (strpos((string)$url, "/critical-result-notifications/") !== false) {
                $sitenameAbbreviation = "crn";
                $subject = "[Critical Result Notifications] " . $subject;
            }
            if (strpos((string)$url, "/vacation-request/") !== false) {
                $sitenameAbbreviation = "vacreq";
                $subject = "[Vacation Request] " . $subject;
            }
            if (strpos((string)$url, "/scan/") !== false) {
                $sitenameAbbreviation = "scan";
                $subject = "[Scan Order] " . $subject;
            }
            if (strpos((string)$url, "/deidentifier/") !== false) {
                $sitenameAbbreviation = "deidentifier";
                $subject = "[Deidentifier] " . $subject;
            }
            if (strpos((string)$url, "/dashboard/") !== false) {
                $sitenameAbbreviation = "dashboard";
                $subject = "[Dashboard] " . $subject;
            }
        }

        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment && $environment != 'live' ) {
            $subject = "[".$environment."] " . $subject;
        }

        if( !$emails || $emails == "" ) {
            //$logger->error("sendEmail: emails empty=".$emails);
            $logger->error("sendEmail: Email has not been sent (emails empty): subject=".$subject."; body=".$body);
            return false;
        }

        if( !$body || $body == "" ) {
            //$logger->error("sendEmail: message body empty=".$body);
            $logger->error("sendEmail: Email has not been sent (message body empty): subject=".$subject."; body=".$body);
            return false;
        }

        if( !$fromEmail ) {
            if( $sitenameAbbreviation ) {
                $fromEmail = $userSecUtil->getSiteFromEmail($sitenameAbbreviation);
            }
        }

        if( !$fromEmail ) {
            $fromEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
        }

        if( !$fromEmail ) {
            $logger->error("sendEmail: Email has not been sent (fromEmail empty): subject=".$subject."; body=".$body);
            return false;
        }
        //$logger->notice("sendEmail: sending email: subject=".$subject."; body=".$body."; fromEmail=".$fromEmail);

        //send copy email to siteEmail via bcc
        $userSecUtil = $this->container->get('user_security_utility');
        $bcc = $userSecUtil->getSiteSettingParameter('siteEmail');

        //$allEmails = "";
        $emails = $this->checkEmails($emails); //,'to',$allEmails);
        $ccs = $this->checkEmails($ccs); //,'css',$allEmails);
        $bcc = $this->checkEmails($bcc); //,'bcc',$allEmails);

        $resCc = array();
        $resBcc = array();

        //echo "fromEmail=[$fromEmail] <br>";
        //echo "fromEmail=[".$fromEmail."], emails=[".json_encode($emails)."], ccs=[".json_encode($ccs)."], bcc=[".json_encode($bcc)."] <br><br>";

        if( $emails && count($emails) > 0 ) {
            //OK
        } else {
            $logger->error("sendEmail: Email has not been sent ('To:' emails is empty): From:".$fromEmail."; subject=".$subject."; body=".$body);
            return false;
        }

        $message = new Email();
        //$mailer = $this->mailer;

        $message->subject($subject);
        $message->from($fromEmail);

        //for html
        $body = str_replace("\r\n","<br>",$body);

        $message->html($body);

        //re-route all emails to
        //$mailerDeliveryAddresses = NULL;
        $mailerDeliveryAddresses = trim((string)$userSecUtil->getSiteSettingParameter('mailerDeliveryAddresses'));
        //$mailerDeliveryAddresses = "cinava@yahoo.com,cinava@yahoo.com,oli2002@med.cornell.edu, ,,";
        if( $mailerDeliveryAddresses ) {

            $mailerDeliveryAddresses = $this->checkEmails($mailerDeliveryAddresses);
            //echo "mailerDeliveryAddresses2=[".json_encode($mailerDeliveryAddresses)."]<br>";
            //$message->to($mailerDeliveryAddresses);
            $message = $this->addEmailByType($message,$mailerDeliveryAddresses,'to');

        } else {

            //to
            //echo "emails=[".json_encode($emails)."]<br>";
            //$message->to($emails);
            $message = $this->addEmailByType($message,$emails,'to');

            //cc
            if( $ccs && count($ccs) > 0 ) {
                $resCc = $this->removeDuplicate($ccs,$emails);
                //echo "resCc=[".json_encode($resCc)."]<br>";
                //$message->cc($resCc);
                $message = $this->addEmailByType($message,$resCc,'cc');
            }

            //send copy email to siteEmail via bcc
            if( $bcc && count($bcc) > 0 ) {
                $resBcc = $this->removeDuplicate($bcc,$emails);

                $resBcc = $this->removeDuplicate($resBcc,$resCc);

                //echo "resBcc=[".json_encode($resBcc)."]<br>";
                //$message->bcc($resBcc);
                $message = $this->addEmailByType($message,$resBcc,'bcc');
            }
        }

        // Optionally add any attachments
        if( $attachmentPath ) {

            if( $attachmentPath ) {
                $logger->notice("Attachment exists; fromPath=".$attachmentPath);
            } else {
                $logger->notice("Attachment is NULL; fromPath=".$attachmentPath);
            }

            $message->attachFromPath($attachmentPath,$attachmentFilename);
        }

        $emailsStr = "";
        if( $emails && count($emails) > 0 ) {
            $emailsStr = implode(',',$emails);
        }
        $ccStr = "";
        if( $resCc && count($resCc) > 0 ) {
            $ccStr = implode(',',$resCc);
        }
        $bccStr = "";
        if( $resBcc && count($resBcc) > 0 ) {
            $bccStr = implode(',',$resBcc);
        }
        $mailerDeliveryAddressesStr = "";
        if( $mailerDeliveryAddresses && count($mailerDeliveryAddresses) > 0 ) {
            $mailerDeliveryAddressesStr = implode(',',$mailerDeliveryAddresses);
        }

        $mailer = $this->getMailer();

        if( !$mailer ) {
            $logger->notice("sendEmail: Email has not been sent: From:".$fromEmail.
                "; To:".$emailsStr.
                "; CC:".$ccStr.
                ", BCC:".$bccStr.
                "; subject=".$subject."; body=".$body.
                "; attachmentPath=".$attachmentPath).
                "; redirected=".$mailerDeliveryAddressesStr
            ;
        }
        //echo "after transport newInstance <br>";
        //$logger->notice("sendEmail: Trying to sent email: From:".$fromEmail."; To:".$emailsStr."; CC:".$ccStr."; subject=".$subject."; body=".$message);

        //When using send() the message will be sent just like it would be sent if you used your mail client.
        // An integer is returned which includes the number of successful recipients.
        // If none of the recipients could be sent to then zero will be returned, which equates to a boolean false.
        // If you set two To: recipients and three Bcc: recipients in the message and all of the recipients
        // are delivered to successfully then the value 5 will be returned.
        //$emailRes = $mailer->send($message);
        try{
            $emailRes = $mailer->send($message);
        }
        catch( \TransportExceptionInterface $e ){
            $emailRes = $e->getMessage() ;
            $logger->error($emailRes);
        }

        $msg = "sendEmail: From:".$fromEmail.
            "; To:".$emailsStr.
            "; CC:".$ccStr.
            ", BCC:".$bccStr.
            "; redirected=".$mailerDeliveryAddressesStr
        ;
        //echo $msg . "<br>";

        $msg = $msg .
            "; subject=".$subject."; body=".$body.
            "; attachmentPath=".$attachmentPath
        ;

        $logger->notice($msg);

        return $emailRes;
    }

    public function getMailer() {

        //return $this->mailer;

        $userSecUtil = $this->container->get('user_security_utility');

        $useSpool = $userSecUtil->getSiteSettingParameter('mailerSpool');
        if( $useSpool ) {
//            $spoolPath = $this->container->get('kernel')->getProjectDir() .
//                DIRECTORY_SEPARATOR . "app" .
//                DIRECTORY_SEPARATOR . "spool".
//                DIRECTORY_SEPARATOR . "default";
//            $spool = new \Swift_FileSpool($spoolPath);
//            $transport = new \Swift_SpoolTransport($spool);

            //TODO: Implement spool
            $transport = $this->getSmtpTransport();
            if( !$transport ) {
                return null;
            }
        } else {
            $transport = $this->getSmtpTransport();
            if( !$transport ) {
                return null;
            }
        }

        //$mailer = \Swift_Mailer::newInstance($transport);
        //$mailer = new \Swift_Mailer($transport);
        $mailer = new Mailer($transport);

        return $mailer;
    }
    public function getSmtpTransport() {

        $userSecUtil = $this->container->get('user_security_utility');

        $host = $userSecUtil->getSiteSettingParameter('smtpServerAddress');
        if( !$host ) {
            return null;
        }

        $port = $userSecUtil->getSiteSettingParameter('mailerPort');
        $encrypt = $userSecUtil->getSiteSettingParameter('mailerUseSecureConnection');
        $username = $userSecUtil->getSiteSettingParameter('mailerUser');
        //Note for Google email server: use Google App specific password
        //Enable 2-step verification
        //Generate Google App specific password
        $password = $userSecUtil->getSiteSettingParameter('mailerPassword');
        $authMode = $userSecUtil->getSiteSettingParameter('mailerAuthMode');
        //$trans = $userSecUtil->getSiteSettingParameter('mailerTransport');

        //$fromEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
        //echo '$fromEmail='.$fromEmail."<br>";
        //echo '$username='.$username.'; $password='.$password.'; $port='.$port.'; $host='.$host."<br>";

        //MAILER_DSN=smtp://****:****@smtp.office365.com:587?timeout=60
        $timeoutStr = "";
        $timeoutStr = "?timeout=60"; //timeout in seconds

        //https://serveanswer.com/questions/convert-swiftmailer-to-symfony-mailer-with-username-password-antiflood-plugin-and-failed-recipients
        $transport = Transport::fromDsn('smtp://'.urlencode((string)$username).':'.urlencode((string)$password).'@'.$host.':'.$port.$timeoutStr);

        return $transport;

//        //echo "before transport newInstance <br>";
//        //$transport = \Swift_SmtpTransport::newInstance();
//        //$transport = new \Swift_SmtpTransport();
//        //$transport = new Transport(); //Symfony\Component\Mailer\Transport
//        //$transport = new Transport\Smtp\EsmtpTransport('localhost');
//        //echo "after transport newInstance <br>";
//        if( !$transport ) {
//            return null;
//        }
//
//        $transport->setHost($host);
//
//        if( $port ) {
//            $transport->setPort($port);
//        }
//
//        if( $username ) {
//            $transport->setUsername($username);
//        }
//
//        if( $password ) {
//            $transport->setPassword($password);
//        }
//
//        if( $authMode ) {
//            $transport->setAuthMode($authMode);
//        }
//
//        if( $encrypt ) {
//            $transport->setEncryption($encrypt);
//        }
//
//        $transport->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false)));
//
//        return $transport;
    }//getSmtpTransport

    public function addEmailByType( $message, $emailArr, $type ) {
        if( $emailArr ) {
            $addedCounter = 0;
            foreach ($emailArr as $email) {
                if ($email) {
                    if ($type === 'to') {
                        if( $addedCounter == 0 ) {
                            $message->to($email);
                        } else {
                            $message->addTo($email);
                        }
                        $addedCounter++;
                    }
                    if ($type === 'cc') {
                        if( $addedCounter == 0 ) {
                            $message->cc($email);
                        } else {
                            $message->addCc($email);
                        }
                        $addedCounter++;
                        //$message->cc($email);
                    }
                    if ($type === 'bcc') {
                        if( $addedCounter == 0 ) {
                            $message->bcc($email);
                        } else {
                            $message->addBcc($email);
                        }
                        $addedCounter++;
                        //$message->bcc($email);
                    }
                }
            }
        }
        return $message;
    }

    //Convert emails to unique array
    //return: array of unique emails
    public function checkEmails( $emails ) {
        //$logger = $this->container->get('logger');

        $cleanEmailsArr = array();

        if( !$emails ) {
            return $cleanEmailsArr;
        }

        if( is_array($emails) ) {
            //array
            foreach($emails as $email) {
                if( $email ) {
                    $email = $this->cleanEmail($email);
                    if( $email ) {
                        $cleanEmailsArr[] = $email;
                    }
                }
            } //foreach

        } else {
            //string
            //if( $emails && str_contains($emails, ',') ) {
                $cleanEmailsArr = array();
                $emailsArr = explode(',', $emails);
                foreach($emailsArr as $email) {
                    if( $email ) {
                        $email = $this->cleanEmail($email);
                        if( $email ) {
                            $cleanEmailsArr[] = $email;
                        }
                    }
                }
            //}
        }

        if( count($cleanEmailsArr) > 0 ) {
            $cleanEmailsArr = array_unique($cleanEmailsArr);
        }

        return $cleanEmailsArr;
    }

    public function cleanEmail($email) {
        if( $email ) {
            $email = trim((string)$email);
            $email = str_replace(" ", "", $email);
            $email = str_replace(",,,", ",", $email);
            $email = str_replace(",,", ",", $email);
            $email = str_replace(",", "", $email);
        }

        return $email;
    }

    //$inputEmails:     array(e1,e5)
    //$emails:          array(e1,e2,e3,e4)
    //output emails:    e5
    public function removeDuplicate( $inputEmails, $emails ) {

        if( $inputEmails && count($inputEmails) > 0 ) {
            //
        } else {
            //all checking $inputEmails are empty => return NULL
            return NULL;
        }

        if( $emails && count($emails) > 0 ) {
            //
        } else {
            //all emails are empty => return all checking $inputEmails
            return $inputEmails;
        }

        if( $inputEmails && count($inputEmails) > 0 && $emails && count($emails) > 0 ) {
            $resultArr = array(); //array_diff($array1, $array2);

            foreach($inputEmails as $inputEmail) {
                if( in_array($inputEmail, $emails) ) {
                    continue; //skip
                }
                $resultArr[] = $inputEmail; //add to result email
            }

            if( count($resultArr) > 0 ) {
                return $resultArr;
            }

        }

        return NULL;
    }

    public function createEmailCronJob() {
        if( $this->isWindows() ){
            return $this->createEmailCronJobWindows();
        } else {
            return $this->createEmailCronJobLinux();
        }

    }

    //https://stackoverflow.com/questions/19641619/windows-7-scheduled-task-command-line
    public function createEmailCronJobWindows() {
        $userSecUtil = $this->container->get('user_security_utility');
        //$logger = $this->container->get('logger');

        $projectDir = $this->container->get('kernel')->getProjectDir();
        $cronJobName = "swift"; //"Swiftmailer_Order";

        //command:   "E:\Program Files (x86)\pacsvendor\WebServer\PHP\php.exe"
        //arguments: app/console cron:swift --env=prod
        //Start In:  E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2

        //command:    php
        //arguments(working): "E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\bin\console" cron:swift --env=prod
        $console = $projectDir.DIRECTORY_SEPARATOR."bin".DIRECTORY_SEPARATOR."console";
        $cronJobCommand = 'php \"'.$console.'\" cron:swift --env=prod';
        $cronJobCommand = '"'.$cronJobCommand.'"';

        $useSpool = $userSecUtil->getSiteSettingParameter('mailerSpool');
        $mailerFlushQueueFrequency = $userSecUtil->getSiteSettingParameter('mailerFlushQueueFrequency');

        if( $useSpool && $mailerFlushQueueFrequency ) {
            //first delete cron job
            $this->removeEmailCronJobWindows($cronJobName);
            //create cron job
            //SchTasks /Create /SC DAILY /TN “My Task” /TR “C:RunMe.bat” /ST 09:00
            //$command = 'SchTasks /Create /SC DAILY /TN "'.$cronJobName.'" /TR "'.$cronJobCommand.'" /ST 09:00';
            $command = 'SchTasks /Create /SC MINUTE /MO '.$mailerFlushQueueFrequency.
                ' /IT '.
                //' /RU system'.
                ' /TN '.$cronJobName.
                ' /TR '.$cronJobCommand.''
            ;
            //echo "SchTasks add: ".$command."<br>";
            //$logger->notice("SchTasks:".$command);
            $res = exec($command);
            return $res;
        } else {
            //remove cron job
            //SchTasks /Delete /TN “My Task”
            //$command = 'SchTasks /Delete /TN "'.$cronJobName.'" /F';
            //$command = 'SchTasks /Delete /TN '.$cronJobName;
            //echo "SchTasks remove: ".$command."<br>";
            //$res = exec($command);
            //exit("res=".$res);
            return $this->removeEmailCronJobWindows($cronJobName);
        }
    }
    public function removeEmailCronJobWindows($cronJobName) {
        $command = 'SchTasks /Delete /TN "'.$cronJobName.'" /F';
        //$command = 'SchTasks /Delete /TN '.$cronJobName;
        //echo "SchTasks remove: ".$command."<br>";
        $res = exec($command);
        //exit("res=".$res);
        return $res;
    }

    //https://github.com/yzalis/Crontab
    //run: php bin/console cron:swift --env=prod
    public function createEmailCronJobLinux() {

        //return "Not implemented for Symfony >=4";

        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');

        //$projectDir = $this->container->get('kernel')->getProjectDir();
        //$cronJobName = "php ".$projectDir.DIRECTORY_SEPARATOR."bin/console cron:swift --env=prod";

        $useSpool = $userSecUtil->getSiteSettingParameter('mailerSpool');
        //if( !$useSpool ) {
        //    $useSpool = true;
        //}
        
        $mailerFlushQueueFrequency = $userSecUtil->getSiteSettingParameter('mailerFlushQueueFrequency');
        if( !$mailerFlushQueueFrequency ) {
            $mailerFlushQueueFrequency = 15; //in minuts
        }
        
        //create cron job
        if( $useSpool && $mailerFlushQueueFrequency ) {

            //echo "create crontab commandJobName=cron:swift <br>";
            $res = $userServiceUtil->createEmailCronLinux($mailerFlushQueueFrequency);

            return $res;
        } else {
            //remove cron job
            $commandJobName = "cron:swift";
            //echo "remove crontab commandJobName=".$commandJobName."<br>";
            $res = $userServiceUtil->removeCronJobLinuxByCommandName($commandJobName);
            if( $res ) {
                $userUtil = $this->container->get('user_utility');
                $session = $userUtil->getSession(); //$this->container->get('session');
                $session->getFlashBag()->add(
                    'notice',
                    "Removed Cron Job:" . $commandJobName
                );
            }
        }

        //exit('email 111');

        return null;
    }

    public function isCronJobExists($crontab,$commandName) {
        foreach($crontab->getJobs() as $job) {
            //echo "job=".$job.", command=".$job->getCommand()."<br>";
            if( $commandName == $job->getCommand() ) {
                //echo "remove job ". $job."<br>";
                return true;
            }
        }
        return false;
    }

    public function removeCronJob($crontab,$commandName) {
        $resArr = array();
        foreach($crontab->getJobs() as $job) {
            //echo "job=".$job.", command=".$job->getCommand()."<br>";
            if( $commandName == $job->getCommand() ) {
                $resArr[] = $job."";
                $crontab->removeJob($job);
                $crontab->getCrontabFileHandler()->write($crontab);
            }
        }
        return implode("; ",$resArr);
    }

    public function getCronStatus() {
        if( $this->isWindows() ){
            return $this->getCronStatusWindowsEmail();
        } else {
            return $this->getCronStatusLinuxEmail();
        }
    }

    public function getCronStatusWindowsEmail() {
        $cronJobName = "swift"; //"Swiftmailer";
        $command = 'SchTasks | FINDSTR "'.$cronJobName.'"';
        $res = exec($command);

        if( $res ) {
            //$res = "Cron job status: " . $crontab->render();
            $res = '<font color="green">Cron job status: '.$res.'.</font>';
        } else {
            $res = '<font color="red">Cron job status: not found.</font>';
        }
        //exit($res);
        return $res;
    }

    public function getCronStatusLinuxEmail() {

        return "Not implemented for Symfony >=4";

        $res = '<font color="red">Cron job status: not found.</font>';
        $crontab = new Crontab();
        $crontabRender = $crontab->render();
        if( $crontabRender ) {
            //$res = "Cron job status: " . $crontab->render();
            $res = '<font color="green">Cron job status: '.$crontab->render().'.</font>';
        }
        //exit($res);
        return $res;
    }

    public function isWindows() {
        if( substr(php_uname(), 0, 7) == "Windows" ){
            return true;
        }
        return false;
    }



    //Testing
    public function sendInvoiceTestEmail() {
        $emailUtil = $this->container->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        //$email = "oli2002@med.cornell.edu";
        $siteEmail = $userSecUtil->getSiteSettingParameter('siteEmail');

        $invoice = NULL;
        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if ($environment == "dev") {
            $invoice = $this->em->getRepository('AppTranslationalResearchBundle:Invoice')->find(4760); //dev
        }
        if ($environment == "test") {
            $invoice = $this->em->getRepository('AppTranslationalResearchBundle:Invoice')->find(4730); //test
        }
        if ($environment == "live") {
            $invoice = $this->em->getRepository('AppTranslationalResearchBundle:Invoice')->find(7323); //prod
        }
        if (!$invoice) {
            return "Invoice not defined for environment=$environment";
            //exit("Invoice not defined for environment=$environment");
        }
        $invoicePDF = $invoice->getRecentPDF();
        $attachmentPath = $invoicePDF->getAttachmentEmailPath();
        $emailUtil->sendEmail($siteEmail, "Test Email Invoice", "Test Email Invoice", null, $siteEmail, $attachmentPath);

        //$res = $invoice->getId() . ": attachmentPath=$attachmentPath <br>";
        //$res = "Testing email has been sent. " . $res;

        $res = "Testing email sent";

        return $res;
    }






    

    //NOT USED
    //php bin/console swiftmailer:spool:send --env=prod: Unable to connect with TLS encryption
    public function hasConnection() {

        return true;

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

//        $environment = $userSecUtil->getSiteSettingParameter('environment');
//        if( $environment == 'dev'  ) {
//            $logger->notice("SendEmail is disabled for environment '".$environment."'");
//            return false;
//        }

        $smtp = $userSecUtil->getSiteSettingParameter('smtpServerAddress');
        //echo "smtp=" . $smtp . "<br>";
        //exit();

        $fp = fsockopen($smtp, 25, $errno, $errstr, 9) ;

        if (!$fp) {
            $logger->error("SendEmail server=$smtp; ERROR:$errno - $errstr");
            $result = false;
        } else {
            fclose($fp);
            $result = true;
        }

        return $result;
    }




    //Testing attachments
    public function testEmailWithAttachments() {

        exit('not allowed');

        if(0) {
            ///// Test 1) new reference letter ////////
            $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
            $fellapp = $this->em->getRepository('AppFellAppBundle:FellowshipApplication')->find(1414); //8-testing, 1414-collage, 1439-live
            $references = $fellapp->getReferences();
            $reference = $references->first();
            $letters = $reference->getDocuments();
            $uploadedLetterDb = $letters->first();
            $res = $fellappRecLetterUtil->sendRefLetterReceivedNotificationEmail($fellapp, $uploadedLetterDb);

            $fellappType = $fellapp->getFellowshipSubspecialty();
            $res = "ID=" . $fellapp->getId() . ", fellappType=" . $fellappType . ": res=" . $res . "<br>";
            echo "Test1: $res<br>";
            /////////////////////////
        }


        ////// Test 2) send invoice sendInvoicePDFByEmail /////////
        $transresRequestUtil = $this->container->get('transres_request_util');
        $oid = "APCP2173-REQ15079-V2"; //collage
        $invoice = $this->em->getRepository('AppTranslationalResearchBundle:Invoice')->findOneByOid($oid); //8-testing, 1414-collage, 1439-live
        if( !$invoice ) {
            exit("Invoice not found by oid=$oid");
        }
        $res = $transresRequestUtil->sendInvoicePDFByEmail($invoice);
        echo "Test2: $res<br>";
        /////////////////////////

        ////// Test 3) sendPackingSlipPdfByEmail //////////
        $transresRequestUtil = $this->container->get('transres_request_util');
        $subject = "Test packing slip pdf by email";
        $body = "Test packing slip pdf by email";
        $id = "15079";
        $transresRequest = $this->em->getRepository('AppTranslationalResearchBundle:TransResRequest')->find($id);
        if( !$transresRequest ) {
            exit("TransResRequest not found by id=$id");
        }
        $pdf = $transresRequestUtil->getLatestPackingSlipPdf($transresRequest);
        $res = $transresRequestUtil->sendPackingSlipPdfByEmail($transresRequest,$pdf,$subject,$body);
        echo "Test3: $res<br>";
        /////////////////////////

        ////// Test 4) sendReminderUnpaidInvoices->sendReminderUnpaidInvoicesBySpecialty  //////////
        $transresReminderUtil = $this->container->get('transres_reminder_util');
        $showSummary=false;
        $testing=true;
        $res = $transresReminderUtil->sendReminderUnpaidInvoices($showSummary,$testing);
        echo "Test4: $res<br>";
        /////////////////////////


        //test is not implemented, unless sendInvitationEmail function is moved to utility
        if(0) {
            /////// Test 5) /invite-interviewer-to-rate/{interviewId} //////////////
            $fellAppApplicantController = new FellAppApplicantController();
            $interviewId = 1414;
            $interview = $this->em->getRepository('AppFellAppBundle:Interview')->find($interviewId);
            if (!$interviewId) {
                exit('Interviewer can not be found: interviewId=' . $interviewId);
            }
            $res = $fellAppApplicantController->sendInvitationEmail($interview);
            echo "Test5: $res<br>";
            //////////////////////////////
        }

    }

}


//Notes:
// for testing use: swift_delivery_addresses: [oli2002@med.cornell.edu]
// for live: swift_delivery_addresses: []
//to run spool file: then php app/console swiftmailer:spool:send --env=prod > /dev/null 2>>app/logs/swift-error.log
//cmd /c YourProgram.exe >> app/logs/swiftlog.txt 2>&1

//To prevent tmp file not found (http://stackoverflow.com/questions/27323662/symfony2-send-email-warning-mkdir-no-such-file-or-directory-in):
//After comment this:
//if (is_writable($tmpDir = sys_get_temp_dir())) {
//    $preferences->setTempDir($tmpDir)->setCacheType('disk');
//}
//in the /vendor/swiftmailer/swiftmailer/lib/preferences.php everything works fine.
// I think that the problem was in the permission to the directory.
// Swiftmailer uses sys_get_temp_dir() function which trying refer to /tmp directory.



?>
