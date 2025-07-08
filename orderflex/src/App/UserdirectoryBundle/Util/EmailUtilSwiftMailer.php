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



use App\FellAppBundle\Entity\FellowshipApplication; //process.py script: replaced namespace by ::class: added use line for classname=FellowshipApplication


use App\TranslationalResearchBundle\Entity\Invoice; //process.py script: replaced namespace by ::class: added use line for classname=Invoice


use App\TranslationalResearchBundle\Entity\TransResRequest; //process.py script: replaced namespace by ::class: added use line for classname=TransResRequest


use App\FellAppBundle\Entity\Interview; //process.py script: replaced namespace by ::class: added use line for classname=Interview
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\FellAppBundle\Controller\FellAppApplicantController;

//use Crontab\Crontab;
//use Crontab\Job;


//EmailUtil.php on line 58:
//Swift_SmtpTransport {#8238 ▼
//    #buffer: Swift_Transport_StreamBuffer {#8226 ▼
//    #sequence: 0
//    -filters: []
//    -writeBuffer: ""
//    -mirrors: []
//    -stream: null
//    -in: null
//    -out: null
//    -params: []
//    -replacementFactory: Swift_StreamFilters_StringReplacementFilterFactory {#8242 ▼
//        -filters: []
//    }
//    -translations: []
//  }
//  #started: false
//  #domain: "[127.0.0.1]"
//  #eventDispatcher: Swift_Events_SimpleEventDispatcher {#8214 ▼
//    -eventMap: array:5 [▼
//      "Swift_Events_CommandEvent" => "Swift_Events_CommandListener"
//      "Swift_Events_ResponseEvent" => "Swift_Events_ResponseListener"
//      "Swift_Events_SendEvent" => "Swift_Events_SendListener"
//      "Swift_Events_TransportChangeEvent" => "Swift_Events_TransportChangeListener"
//      "Swift_Events_TransportExceptionEvent" => "Swift_Events_TransportExceptionListener"
//    ]
//    -listeners: []
//  }
//  #addressEncoder: Swift_AddressEncoder_IdnAddressEncoder {#8218}
//  #pipelining: null
//  #pipeline: []
//  #sourceIp: null
//  -handlers: array:1 [▼
//    "AUTH" => Swift_Transport_Esmtp_AuthHandler {#8243 ▼
//      -authenticators: array:5 [▼
//        0 => Swift_Transport_Esmtp_Auth_CramMd5Authenticator {#8236}
//        1 => Swift_Transport_Esmtp_Auth_LoginAuthenticator {#8241}
//        2 => Swift_Transport_Esmtp_Auth_PlainAuthenticator {#8239}
//        3 => Swift_Transport_Esmtp_Auth_NTLMAuthenticator {#8207}
//    4 => Swift_Transport_Esmtp_Auth_XOAuth2Authenticator {#8223}
//        ]
//        -username: null
//        -password: null
//        -auth_mode: null
//        -esmtpParams: []
//    }
//  ]
//  -capabilities: []
//    -params: array:8 [▼
//    "protocol" => ""
//    "host" => "smtp.med.cornell.edu"
//    "port" => 25
//    "timeout" => 30
//    "blocking" => 1
//    "tls" => false
//    "type" => 1
//    "stream_context_options" => array:1 [▼
//      "ssl" => array:3 [▼
//        "allow_self_signed" => true
//        "verify_peer" => false
//        "verify_peer_name" => false
//      ]
//    ]
//  ]
//}


/**
 * Description of EmailUtil
 *
 * @author Cina
 */
class EmailUtilSwiftMailer {

    protected $em;
    protected $container;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {
        $this->em = $em;
        $this->container = $container;
    }

    //php bin/console swiftmailer:spool:send --env=prod
    //$emails: single, comma separated emails, or array of emails
    //$ccs: single, comma separated emails, or array of emails (optional)
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

        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');
        //set_time_limit(0); //set time limit to 600 sec == 10 min

        //echo "emails=".$emails."<br>";
        //print_r($emails);
        //echo "ccs=".$ccs."<br>";
        //$logger->notice("emails=".$emails);
        //$logger->notice("ccs=".$ccs);

//        if( $this->hasConnection() == false ) {
//            $logger->error("sendEmail: connection error");
//            //exit('no connection');
//            return false;
//        }
        //exit('yes connection');

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
                $subject = "[CTP] " . $subject;
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
            if (strpos((string)$url, "/time-away-request/") !== false) {
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

        $emails = $this->checkEmails($emails);
        $ccs = $this->checkEmails($ccs);

        if( count($emails) == 0 ) {
            //$logger->error("sendEmail: Email has not been sent, because emails array is empty");
            $logger->error("sendEmail: Email has not been sent ('To:' emails array is empty): From:".$fromEmail."; subject=".$subject."; body=".$body);
            return false;
        }

//        if( count($emails) > 0 ) {
//            if( !$emails[0] ) {
//                $logger->error("sendEmail: emails[0] empty=" . $emails[0]);
//                return false;
//            }
//        }

//        $logger->notice("emails count=".count($emails));
//        $logger->notice("emails=".implode(", ",$emails));
//        $logger->notice("emails[0]=".$emails[0]);

//        if( $this->em ) {
//            $smtpServerAddress = $userSecUtil->getSiteSettingParameter('smtpServerAddress');
//            $smtp_host_ip = gethostbyname($smtpServerAddress);
//            //$logger->notice("smtpServerAddress=".$smtpServerAddress." => smtp_host_ip=".$smtp_host_ip);
//            //$message = \Swift_Message::newInstance($smtp_host_ip);
//            $mailer = $this->getSwiftMailer();
//        } else {
//            $logger->error("this->em is null in sendEmail: use default Swift_Message::newInstance(). subject=".$subject);
//            $message = \Swift_Message::newInstance();
//        }

//        $message = \Swift_Message::newInstance();
        $message = new \Swift_Message();

        $message->setSubject($subject);
        $message->setFrom($fromEmail);

        //for html
        $body = str_replace("\r\n","<br>",$body);

        $message->setBody(
            $body,
            'text/html'
            //'text/plain'
        );

        $mailerDeliveryAddresses = trim((string)$userSecUtil->getSiteSettingParameter('mailerDeliveryAddresses'));
        if( $mailerDeliveryAddresses ) {
            $mailerDeliveryAddresses = str_replace(" ","",$mailerDeliveryAddresses);
            $mailerDeliveryAddresses = $this->checkEmails($mailerDeliveryAddresses);
            $message->setTo($mailerDeliveryAddresses);
        } else {
            $message->setTo($emails);
            if( $ccs ) {
                $message->setCc($ccs);
            }
        }

        //send copy email to siteEmail via setBcc
        $userSecUtil = $this->container->get('user_security_utility');
        $siteEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
        if( $siteEmail ) {
            $message->setBcc($siteEmail);
        }

            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'Emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */

        // Optionally add any attachments
        if( $attachmentPath ) {

            //Get absolute path
            //$appPath = $this->container->getParameter('kernel.root_dir');
            //$webPath = realpath($appPath . '/../web');
            //echo "webPath=$webPath<br>";

            //echo "attachmentPath=$attachmentPath<br>";
            $attachment = \Swift_Attachment::fromPath($attachmentPath);
            if( $attachmentFilename ) {
                $attachment->setFilename($attachmentFilename);
            }
            if( $attachment ) {
                $logger->notice("Attachment exists; fromPath=".$attachmentPath);
            } else {
                $logger->notice("Attachment is NULL; fromPath=".$attachmentPath);
            }
            $message->attach($attachment);
        }

        $ccStr = "";
        if( $ccs && count($ccs)>0 ) {
            $ccStr = implode("; ",$ccs);
        }
        $emailsStr = "";
        if( $emails && count($emails)>0 ) {
            $emailsStr = implode("; ",$emails);
        }

        $mailer = $this->getSwiftMailer();
        if( !$mailer ) {
            $logger->notice("sendEmail: Email has not been sent: From:".$fromEmail.
                "; To:".$emailsStr."; CC:".$ccStr."; subject=".$subject."; body=".$body.
                "; attachmentPath=".$attachmentPath);
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
        }catch(\Swift_TransportException $e){
            $emailRes = $e->getMessage() ;
        }

        $logger->notice("sendEmail: Email sent: res=".$emailRes."; From:".$fromEmail.
            "; To:".$emailsStr."; CC:".$ccStr."; subject=".$subject."; body=".$body.
            "; attachmentPath=".$attachmentPath);

        return $emailRes;
    }

    public function checkEmails($emails) {
        //$logger = $this->container->get('logger');

        if( !$emails ) {
            return $emails;
        }

        if( is_array($emails) ) {
            return $this->validateEmailsArr($emails);
            //return $emails;
        }

        //$logger = $this->container->get('logger');
        //$logger->notice("checkEmails: input emails=".print_r($emails));
        if( strpos((string)$emails, ',') !== false ) {
            $emails = str_replace(" ","",$emails);
            //return explode(',', $emails);
            return $this->validateEmailsArr(explode(',', $emails));
        } else {
            if( $emails ) {
                //return array( $emails );
                return $this->validateEmailsArr(array($emails));
            }
        }

        //$logger->notice("checkEmails: output emails=".implode(";",$emails));
        //return $emails;
        return $this->validateEmailsArr($emails);
    }
    public function validateEmailsArr($emails) {
        $validEmails = array();

        if( !is_array($emails) ) {
            return $validEmails;
        }

        foreach($emails as $email) {
            if( $email ) {
                $validEmails[] = $email;
            }
        }

        return $validEmails;
    }

    //https://ourcodeworld.com/articles/read/14/swiftmailer-send-mails-from-php-easily-and-effortlessly
    public function getSwiftMailer() {
        $userSecUtil = $this->container->get('user_security_utility');

        $useSpool = $userSecUtil->getSiteSettingParameter('mailerSpool');
        if( $useSpool ) {
            $spoolPath = $this->container->get('kernel')->getProjectDir() .
                DIRECTORY_SEPARATOR . "app" .
                DIRECTORY_SEPARATOR . "spool".
                DIRECTORY_SEPARATOR . "default";
            $spool = new \Swift_FileSpool($spoolPath);
            //$transport = \Swift_SpoolTransport::newInstance($spool);
            $transport = new \Swift_SpoolTransport($spool);
        } else {
            $transport = $this->getSmtpTransport();
            if( !$transport ) {
                return null;
            }
        }

        //$mailer = \Swift_Mailer::newInstance($transport);
        $mailer = new \Swift_Mailer($transport);

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

        //echo "before transport newInstance <br>";
        //$transport = \Swift_SmtpTransport::newInstance();
        $transport = new \Swift_SmtpTransport();
        //echo "after transport newInstance <br>";
        if( !$transport ) {
            return null;
        }

        $transport->setHost($host);

        if( $port ) {
            $transport->setPort($port);
        }

        if( $username ) {
            $transport->setUsername($username);
        }

        if( $password ) {
            $transport->setPassword($password);
        }

        if( $authMode ) {
            $transport->setAuthMode($authMode);
        }
        
        if( $encrypt ) {
            $transport->setEncryption($encrypt);
        }

        $transport->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false)));

        return $transport;
    }

    public function sendSpooledEmails() {
        $userSecUtil = $this->container->get('user_security_utility');

        $transport = $this->getSmtpTransport();
        if( !$transport ) {
            return null;
        }

        $useSpool = $userSecUtil->getSiteSettingParameter('mailerSpool');
        if( $useSpool ) {
            $spoolPath = $this->container->get('kernel')->getProjectDir() .
                DIRECTORY_SEPARATOR . "app" .
                DIRECTORY_SEPARATOR . "spool".
                DIRECTORY_SEPARATOR . "default";
            $spool = new \Swift_FileSpool($spoolPath);

            $spool->recover();
            $res = $spool->flushQueue($transport);

            return $res;
        }

        return null;
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
        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
            $fellapp = $this->em->getRepository(FellowshipApplication::class)->find(1414); //8-testing, 1414-collage, 1439-live
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
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $invoice = $this->em->getRepository(Invoice::class)->findOneByOid($oid); //8-testing, 1414-collage, 1439-live
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
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $transresRequest = $this->em->getRepository(TransResRequest::class)->find($id);
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
        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:Interview'] by [Interview::class]
            $interview = $this->em->getRepository(Interview::class)->find($interviewId);
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
