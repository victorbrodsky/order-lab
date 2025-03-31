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

namespace App\UserdirectoryBundle\Services;



use App\UserdirectoryBundle\Entity\Logger; //process.py script: replaced namespace by ::class: added use line for classname=Logger

//use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\FirewallMapInterface;

class ExceptionListener {

    private $container;
    private $em;
    //protected $secTokenStorage;
    //protected $secAuthChecker;
    protected $security;
    private $logger;
    private $firewallMap;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em, Security $security, FirewallMapInterface $firewallMap)
    {
        $this->container = $container;
        $this->em = $em;
        $this->logger = $this->container->get('logger');

        //$this->secAuthChecker = $container->get('security.authorization_checker');
        //$this->secTokenStorage = $container->get('security.token_storage');
        $this->security = $security;
        $this->firewallMap = $firewallMap;
    }


    public function onKernelException(ExceptionEvent $event) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

//        if( $this->secTokenStorage->getToken() ) {
//            $user = $this->secTokenStorage->getToken()->getUser();
//        } else {
//            $user = $userSecUtil->findSystemUser();
//        }

        $user = $this->security->getUser();
        if( !$user ) {
            $user = $userSecUtil->findSystemUser();
        }
        //exit("user=".$user);

        $request = $event->getRequest();

        // You get the exception object from the received event
        $exception = $event->getThrowable();

        $ip = $this->get_client_ip();

//        $message = sprintf(
//            'Error: %s with code: %s',
//            $exception->getMessage(),
//            $exception->getCode()
//
//        );
//        echo $exception->getCode();
        //echo "Trace: ".$exception->getTraceAsString()."<br>";
        //var_dump($exception->getTraceAsString());

        $ipFiltering = true; //Don't send Kernel exception if the server IP is used (vulnerability scan by ITS).
        //$ipFiltering = false;
        //Ignore if client request is coming from IP
        if( $ipFiltering ) {

            //Ignore if client request is coming from localhost
//            if( $this->is_ip($ip) ) {
//                //exit("2Ignore requests from=".$ip);
//                return false;
//            }

            $hostname = $request->getHost();

            if( $hostname == 'localhost' ) {
                $logger->notice("Ignoring hostname=$hostname");
                return false;
            }
            if( $hostname == 'iamahost' ) {
                $logger->notice("Ignoring hostname=$hostname");
                return false;
            }

            $hostname = str_replace('.','',$hostname);
            //$logger->notice("hostname=$hostname");

            //check if numeric
            if( is_numeric($hostname) ) {
                $logger->notice("Ignoring numeric hostname=$hostname");
                return false;
            }
        }

        $logger->notice("Original exception message=".$exception->getMessage());

        //Ignore: Unable to create the storage directory (/srv/order-lab/orderflex/var/cache/prod/profiler)
        if (strpos((string)$exception->getMessage(), 'Unable to create the storage directory') !== false) {
            return false;
        }
        if (strpos((string)$exception->getMessage(), 'var/cache/') !== false) {
            return false;
        }

        //Filter out apache requests every 10 min in Windows url http://wpad.nyp.org/wpad.dat
        if (strpos((string)$event->getRequest()->getUri(), '/wpad.dat') !== false) {
            return false;
        }

        $message = "Error: " . $exception->getMessage() . " with code" . $exception->getCode() .
            "<br>File: ".$exception->getFile() .
            "<br>Line: ".$exception->getLine()
            . "<br>Controller: ".$event->getRequest()->attributes->get('_controller')
            . "<br>Router: ".$event->getRequest()->attributes->get('_route')
            . "<br>Router Parameters: ".json_encode($event->getRequest()->attributes->get('_route_params'))
            . "<br>Full URI: ".$event->getRequest()->getUri()
            //. "<br>URI Path Info: ".$event->getRequest()->server->get('PATH_INFO')
            //. "<br>URI Query String: ".$event->getRequest()->getQueryString()
            //. "<br>Code:".$exception->getCode()
            . "<br>Trace: ". $exception->getTraceAsString()
            ."<br>User: ".$user
            ."<br>Client IP: ".$ip
        ;

        if( $request ) {
            //$domain = $request->getSchemeAndHttpHost();
            //Replace $request->getSchemeAndHttpHost() with getRealSchemeAndHttpHost($request)
            $userUtil = $this->container->get('user_utility');
            $domain = $userUtil->getRealSchemeAndHttpHost($request);
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
            $subject = "Critical Server Error";
            $dateStr = date("m/d/Y \\a\\t H:i:s");
            //exit("dateStr=".$dateStr);
            //On MM/DD/YYYY, at HH:MM:SS the following error has been logged on the [server domain name/C.MED.CORNELL.EDU vs Collage, or IP address etc]: [text of error]
            $msg = "On $dateStr the following error has been logged on the $domain";

            ////// Firewall info //////
            //$contextKey = $this->container->get('router')->getContext();
            //$contextKey = $this->container->get('security.context');
            //$contextKey = $this->container->get('security.authentication.ldap_employees_firewall.context');
            //$contextKey = $this->security->get('ldap_employees_firewall.context');
            $firewallConfig = $this->firewallMap->getFirewallConfig($request);
            if (null === $firewallConfig) {
                return;
            }
            $firewallName = $firewallConfig->getName();
            $context = $firewallConfig->getContext();
            $stateless = $firewallConfig->isStateless();
            if ($stateless) {
                $statelessStr = "true";
            } else {
                $statelessStr = "false";
            }
            $msg = $msg . " (firewallName=". $firewallName . ", context=". $context . ", stateless=" . $statelessStr . ")";
            ////// EOF Firewall info //////

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
        if( strpos((string)$controller,'App\UserdirectoryBundle') !== false ) {
            return "employees";
        }
        if( strpos((string)$controller,'App\OrderformBundle') !== false ) {
            return "scan";
        }
        if( strpos((string)$controller,'App\FellAppBundle') !== false ) {
            return "fellapp";
        }
        if( strpos((string)$controller,'App\ResAppBundle') !== false ) {
            return "resapp";
        }
        if( strpos((string)$controller,'App\DeidentifierBundle') !== false ) {
            return "deidentifier";
        }
        if( strpos((string)$controller,'App\VacReqBundle') !== false ) {
            return "vacreq";
        }
        if( strpos((string)$controller,'App\CallLogBundle') !== false ) {
            return "calllog";
        }
        if( strpos((string)$controller,'App\CrnBundle') !== false ) {
            return "crn";
        }
        if( strpos((string)$controller,'App\TranslationalResearchBundle') !== false ) {
            return "translationalresearch";
        }
        if( strpos((string)$controller,'App\DashboardBundle') !== false ) {
            return "dashboard";
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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");
        $dql->innerJoin('logger.eventType', 'eventType');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
        //$dql->where("logger.entityNamespace = 'App\TranslationalResearchBundle\Entity' AND logger.entityName = 'TransResRequest' AND logger.entityId = ".$request->getId());

        $dql->where("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = $eventType;

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('logger.creationdate >= :startDate');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('logger.creationdate <= :endDate');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        $dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        $count = count($loggers);

        return $count;
    }

    public function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    function is_ip($str) {
        $ret = filter_var($str, FILTER_VALIDATE_IP);

        return $ret;
    }

} 