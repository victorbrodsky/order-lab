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
 * Date: 8/8/14
 * Time: 4:20 PM
 */

namespace Oleg\UserdirectoryBundle\Services;


use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\UserdirectoryBundle\Util\UserUtil;

class ExceptionListener {

    private $container;
    private $em;
    protected $secTokenStorage;
    protected $secAuthChecker;
    private $logger;

    public function __construct(ContainerInterface $container, $em)
    {
        $this->container = $container;
        $this->em = $em;
        $this->logger = $this->container->get('logger');

        $this->secAuthChecker = $container->get('security.authorization_checker');
        $this->secTokenStorage = $container->get('security.token_storage');
    }


    public function onKernelException(GetResponseForExceptionEvent $event) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        if( $this->secTokenStorage->getToken() ) {
            $user = $this->secTokenStorage->getToken()->getUser();
        } else {
            $user = null;
        }

        $request = $event->getRequest();

        // You get the exception object from the received event
        $exception = $event->getException();

//        $message = sprintf(
//            'Error: %s with code: %s',
//            $exception->getMessage(),
//            $exception->getCode()
//
//        );

        $message = "Error: " . $exception->getMessage() . " with code" . $exception->getCode() .
            "<br>File: ".$exception->getFile() .
            "<br>Line: ".$exception->getLine()
        ;

        if( $request ) {
            $domain = $request->getSchemeAndHttpHost();
        } else {
            $domain = "Unknown Server";
        }

        //echo "<br><br>";
        //echo "file=".$exception->getFile()."<br>";
        //echo "line=".$exception->getLine()."<br>";

        //exit('111');

        if( $userSecUtil->getSiteSettingParameter('emailCriticalError') === true ) {

            if( $request ) {
                $controller = $request->attributes->get('_controller');
                $sitename = $this->getSiteName($controller);
            } else {
                $sitename = "employees";
            }

            //$emails = $userSecUtil->getUserEmailsByRole($sitename,null,array("ROLE_PLATFORM_ADMIN","ROLE_PLATFORM_DEPUTY_ADMIN"));
            $emails = $userSecUtil->getUserEmailsByRole($sitename,"Platform Administrator");
            //echo "emails: <br>";
            //print_r($emails);
            //exit('111');

            //except these users
            $exceptionUsers = $userSecUtil->getSiteSettingParameter('emailCriticalErrorExceptionUsers');
            $exceptionUsersEmails = array();
            foreach($exceptionUsers as $exceptionUser) {
               // echo "exceptionUser=".$exceptionUser."<br>";
                $exceptionUsersEmails[] = $exceptionUser->getSingleEmail();
            }

            if( count($exceptionUsersEmails) > 0 ) {
                $emails = array_diff($emails, $exceptionUsersEmails);
            }
            //echo "emails: <br>";
            //print_r($emails);
            //exit('111');

            //2- If the checkbox is checked, Send an email to all users with System Administrator role saying:
            $subject = "Server Critical Error";
            $dateStr = date("m/d/Y \\a\\t H:i:s");
            //exit("dateStr=".$dateStr);
            //On MM/DD/YYYY, at HH:MM:SS the following error has been logged on the [server domain name/C.MED.CORNELL.EDU vs Collage, or IP address etc]: [text of error]
            $msg = "On $dateStr the following error has been logged on the $domain";
            $msg = $msg . ": <br>" . $message;
            $emailUtil->sendEmail($emails,$subject,$msg);

            //EventLog
            $msg = "Email notification has been sent to " . implode(", ",$emails) . "<br>" . "Subject: " . $subject . "<br>Body: " . $msg;
            $userSecUtil->createUserEditEvent($sitename,$msg,$user,null,$request,"Critical Error Email Sent");

            //exit('Yes emailCriticalError');
        } else {
            //exit('NO emailCriticalError');
        }

        $maxErrorCounter = $userSecUtil->getSiteSettingParameter('restartServerErrorCounter');
        $maxErrorCounter = null; //testing: httpd might cause unable to start the server normally after rebooting the server
        if( $maxErrorCounter ) {

            //get number of critical errors in the last 10 minutes
            $minutes = 10;
            //$minutes = 100; //testing
            $eventType="Critical Error Email Sent";
            $errorCounter = $this->getErrorCount($eventType,$minutes);
            //exit("errorCounter=".$errorCounter);

            if( $errorCounter > $maxErrorCounter ) {
                //EventLog
                $msg = $domain . " has been restarted after $errorCounter errors in $minutes minutes";
                $userSecUtil->createUserEditEvent($sitename,$msg,$user,null,$request,"Restart Server");

                //Restart Server
                $path = 'E:/Program Files (x86)/Aperio/WebServer/bin/';
                //$path = "C:/Program Files (x86)/Ampps/apache/bin/";

                $path = '"'.$path.'httpd'.'"';

                //C:\Program Files (x86)\Ampps\apache\bin
                //E:\Program Files (x86)\Aperio\WebServer\bin
                //httpd -k restart
                //"E:/Program Files (x86)/Aperio/WebServer/bin/httpd" -k restart;
                //$command = "E:/Program Files (x86)/Aperio/WebServer/bin/httpd"." -k install";
                $command = $path." -k install";
                //$output = shell_exec($command);
                //$logger->notice("Command=" . $command . ": " . $output);
                exec( $command, $output, $return_var );
                $logger->notice("Command=" . $command . "; output=" . var_dump($output) . "; return=".var_dump($return_var));

                //$command = "E:/Program Files (x86)/Aperio/WebServer/bin/httpd"." -k restart";
                //$command = "E:/Program Files (x86)/Aperio/WebServer/bin/httpd"." -k stop";
                //C:\Program Files (x86)\Ampps\apache\bin
                $command = $path." -k restart";
                //$command = $path." -k stop";
                //$command = $path."httpd"." -k stop";
                //$output = shell_exec($command);
                //$logger->notice("Command=" . $command . ": " . $output);
                exec( $command, $output, $return_var );
                $logger->notice("Command=" . $command . "; output=" . var_dump($output) . "; return=".var_dump($return_var));
            }

            //exit('Yes restartServerErrorCounter');
        } else {
            //exit('NO restartServerErrorCounter');
        }
    }

    public function getSiteName($controller) {
        if( strpos($controller,'Oleg\UserdirectoryBundle') !== false ) {
            return "employees";
        }
        if( strpos($controller,'Oleg\OrderformBundle') !== false ) {
            return "scan";
        }
        if( strpos($controller,'Oleg\FellAppBundle') !== false ) {
            return "fellapp";
        }
        if( strpos($controller,'Oleg\DeidentifierBundle') !== false ) {
            return "deidentifier";
        }
        if( strpos($controller,'Oleg\VacReqBundle') !== false ) {
            return "vacreq";
        }
        if( strpos($controller,'Oleg\CallLogBundle') !== false ) {
            return "calllog";
        }
        if( strpos($controller,'Oleg\TranslationalResearchBundle') !== false ) {
            return "translationalresearch";
        }

        return null;
    }

    public function getErrorCount( $eventType="Critical Error Email Sent", $minutes=10 ) {

        $endDate = new \DateTime();
        $startDate = new \DateTime();
        $startDate = $startDate->modify("-$minutes minutes");
        //echo "startDate=".$startDate->format('Y-m-d H:i:s')."; endDate=".$endDate->format('Y-m-d H:i:s')."<br>";

        $dqlParameters = array();

        //get the date from event log
        $repository = $this->em->getRepository('OlegUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");
        $dql->innerJoin('logger.eventType', 'eventType');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
        //$dql->where("logger.entityNamespace = 'Oleg\TranslationalResearchBundle\Entity' AND logger.entityName = 'TransResRequest' AND logger.entityId = ".$request->getId());

        $dql->where("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = $eventType;

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('logger.creationdate >= :startDate');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('logger.creationdate <= :endDate');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        $dql->orderBy("logger.id","DESC");
        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        $count = count($loggers);

        return $count;
    }

} 