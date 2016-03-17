<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 8/28/15
 * Time: 8:47 AM
 */

namespace Oleg\FellAppBundle\Util;


use Clegginabox\PDFMerger\PDFMerger;
use Doctrine\ORM\EntityNotFoundException;
use Oleg\FellAppBundle\Controller\FellAppController;
use Oleg\FellAppBundle\Form\FellowshipApplicationType;
use Oleg\UserdirectoryBundle\Entity\Document;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

//use Symfony\Component\Process\Exception\ProcessFailedException;
//use Symfony\Component\Process\Process;

use Oleg\FellAppBundle\Entity\ReportQueue;
use Oleg\FellAppBundle\Entity\Process;

class ReportGenerator {


    protected $em;
    protected $sc;
    protected $container;
    protected $templating;
    protected $uploadDir;
    protected $processes;
    
    //protected $WshShell;
    protected $runningGenerationReport;
    //protected $env;


    public function __construct( $em, $sc, $container, $templating ) {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
        $this->templating = $templating;

        //fellapp.uploadpath = fellapp
        $this->uploadDir = 'Uploaded/'.$this->container->getParameter('fellapp.uploadpath');

        $this->runningGenerationReport = false;

        //TODO: check if user's time zones are still correct
        date_default_timezone_set('America/New_York');
    }



    public function regenerateAllReports() {

        $queue = $this->getQueue();

        //reset queue
        $this->resetQueue($queue);

        //remove all waiting processes
        $query = $this->em->createQuery('DELETE FROM OlegFellAppBundle:Process p');
        $numDeleted = $query->execute();

        //add all reports generation to queue
        $fellapps = $this->em->getRepository('OlegFellAppBundle:FellowshipApplication')->findAll();
        foreach( $fellapps as $fellapp ) {
            $this->addFellAppReportToQueue($fellapp->getId());
        }

        return $numDeleted;
    }

    public function resetQueueRun() {

        $queue = $this->getQueue();

        //reset queue
        $numUpdated = $this->resetQueue($queue);

        //reset processes
//        $repository = $this->em->getRepository('OlegFellAppBundle:Process');
//        $dql =  $repository->createQueryBuilder("process");
//        $dql->select('process');
//        $dql->where("process.startTimestamp IS NOT NULL");

//        $query = $this->em->createQuery('UPDATE OlegFellAppBundle:Process p SET p.startTimestamp = NULL WHERE p.startTimestamp IS NOT NULL');
//        $numUpdated = $query->execute();

        $cmd = 'php ../app/console fellapp:generatereportrun --env=prod';
        $this->windowsCmdRunAsync($cmd);

        return $numUpdated;
    }
    
    
    
    //starting entry to generate report request
    //$argument: asap, overwrite
    public function addFellAppReportToQueue( $id, $argument='overwrite' ) {

        $logger = $this->container->get('logger');
        $queue = $this->getQueue();

        $processesDb = null;
        if( $argument != 'overwrite' ) {
            //$argument == asap
            $processesDb = $this->em->getRepository('OlegFellAppBundle:Process')->findOneByFellappId($id);
        }

        //add as a new process only if argument is 'overwrite'
        if( $processesDb == null ) {
            $process = new Process($id);
            $process->setArgument($argument);
            $queue->addProcess($process);
            $this->em->flush();
            $logger->notice("Added new process to queue: Fellowship Application ID=".$id);
        }

        //move all reports to OldReports
        $fellapp = $this->em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);
        foreach( $fellapp->getReports() as $report ) {
            $fellapp->removeReport($report);
            $fellapp->addOldReport($report);
        }
        $this->em->flush();

        $logger->notice("call tryRun() asynchronous");

        //call tryRun() asynchronous
        //$cmd = 'php ../app/console fellapp:generatereportrun --env=' . $this->env;
        $cmd = 'php ../app/console fellapp:generatereportrun --env=prod';
        $this->windowsCmdRunAsync($cmd);
        
    }

    //http://www.somacon.com/p395.php
    public function windowsCmdRunAsync($cmd) {

        //TESTING
        //$this->tryRun();
        //return;

        $oExec = null;
        //$WshShell = new \COM("WScript.Shell");
        //$oExec = $WshShell->Run($cmd, 0, false);

        //$oExec = pclose(popen("start ". $cmd, "r"));
        $oExec = pclose(popen("start /B ". $cmd, "r"));
        //$oExec = exec($cmd);

        return $oExec;
    }
    
    public function tryRun() {

        $logger = $this->container->get('logger');
        $logger->notice("tryRun() started");

        $reportFileName = 'noFileName';
        
        $queue = $this->getQueue();

        //reset old running process in queue
        if( $queue->getRunningProcess() ) {

            //$logger->notice("Try Run queue: queue has running process id= " . $queue->getRunningProcess()->getId() );
            if( $this->isProcessHang($queue->getRunningProcess()) ) { //10*60sec=600 minuts limit
                $logger->warning("Try Run queue: reset queue because queue has HANG running process id= " . $queue->getRunningProcess()->getId() );
                //reset queue
                $this->resetQueue($queue);
            }
        }

        //get processes with asap flag
        $processes = $this->em->getRepository('OlegFellAppBundle:Process')->findBy(
            array(
                'startTimestamp' => NULL,
                'argument' => 'asap'
            ),
            array('queueTimestamp' => 'ASC') //ASC => most recent will be the last
        );

        //get processes with NULL timestamp
        if( count($processes) == 0 ) {
            $processes = $this->em->getRepository('OlegFellAppBundle:Process')->findBy(
                array('startTimestamp' => NULL),
                array('queueTimestamp' => 'ASC') //ASC => most recent will be the last
            );
        }

        //get all other processes in queue
        if( count($processes) == 0 ) {
            $processes = $this->em->getRepository('OlegFellAppBundle:Process')->findBy(
                array(),
                array('queueTimestamp' => 'ASC') //ASC => most recent will be the last
            );
        }

        //get the first process
        $process = null;
        if( count($processes) > 0 ) {
            $process = $processes[0];
        }

        $starttime = 'not started yet';
        if( $process && $process->getStartTimestamp() ) {
            $starttime = $process->getStartTimestamp()->format('Y-m-d H:i:s');
            //$logger->notice("Try Run queue: next process to run id=".$process->getId());
        }

//        if( $this->runningGenerationReport ) {
//            $logger->notice("Try Run queue: runningGenerationReport is true");
//        } else {
//            $logger->notice("Try Run queue: runningGenerationReport is false");
//        }

        //echo "Echo: try Run queue count " . count($processes) . ": running process id=".$queue->getRunningProcess()."<br>";
        $logger->notice("Try Run queue: runningGenerationReport=".$this->runningGenerationReport."; processes count=" . count($processes) . "; running process id=".$queue->getRunningProcess()."; process starttime=".$starttime);

        if( !$this->runningGenerationReport && $process && !$queue->getRunningProcess() ) {

            $logger->notice("Conditions allow to run process getFellappId=".$process->getFellappId());
            
            //1) prepare to run
            //1a) reset queue
            $this->resetQueue($queue);
            
            //1b) make sure libreoffice is not running
            //soffice.bin
            //$task_pattern = '~(helpctr|jqs|javaw?|iexplore|acrord32)\.exe~i';
            $task_pattern = '~(soffice.bin|soffice.exe)~i';
            if( $this->isTaskRunning($task_pattern) ) {
                //echo 'task running!!! <br>';
                $logger->warning("libreoffice task is running!");
                if( $this->isProcessHang($process) ) {
                    $this->killTaskByName("soffice");
                    $logger->warning("libreoffice is running and hang => kill task; fellapp id=" . $process->getFellappId() );
                } else {
                    //$this->killTaskByName("soffice");
                    //wait and try run again?
                    $logger->warning("libreoffice is running but not hang => return (wait until next try run); fellapp id=" . $process->getFellappId() );
                    return;
                }
            } else {
                //task is not running => continue
            }
           

            //1c) set running flag
            $this->runningGenerationReport = true;
            $queue->setRunningProcess($process);
            $queue->setRunning(true);
            $process->setStartTimestamp(new \DateTime());
            $this->em->flush();

            //echo "count processes=".count($processes)."<br>";
            //$logger->notice("5 Start running fell report id=" . $process->getFellappId() . "; remaining in queue " . count($processes) );

            //logger start event
            //echo "Start running fell report id=" . $process->getFellappId() . "; remaining in queue " . (count($processes)-1) ."<br>";
            $logger->notice("Start running fell report id=" . $process->getFellappId());

            //$time_start = microtime(true);

            //2) generate pdf report
            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
            $res = $fellappRepGen->generateFellAppReport( $process->getFellappId() );

            //$time_end = microtime(true);
            //$execution_time = ($time_end - $time_start);           
            
            //logger finish event
            //self::$logger->notice("Finished running fell report fellappid=" . $currentQueueElement['id'] . "; executed in " . $execution_time . " sec" . "; report path=" . $res['report'] );
            //$logger->notice("Finished running fell report fellappid=" . $process->getFellappId() . "; executed in " . $execution_time . " sec" . "; res=" . $res['report'] );
            
            //3) reset all queue related parameters
            $this->resetQueue($queue,$process);

            //4) run next in queue
            //$this->tryRun();
            //$cmd = 'php ../app/console fellapp:generatereportrun --env=' . $this->env;
            $cmd = 'php ../app/console fellapp:generatereportrun --env=prod';
            $this->windowsCmdRunAsync($cmd);

            $reportFileName = $res['filename'];
        }

        return $reportFileName;
    }

    //check if the process has been running for 10 minutes
    public function isProcessHang($process) {
        if( !$process->getStartTimestamp() ) {
            return false;
        }
        $now = new \DateTime();
        $nowtime = $now->getTimestamp();
        $started = $process->getStartTimestamp()->getTimestamp();
        if( round(abs($nowtime - $started)) > 600 ) { //10min*60sec=600sec minutes limit
            return true;
        }
        return false;
    }
    
    //$kill_pattern = '~(helpctr|jqs|javaw?|iexplore|acrord32)\.exe~i';
    public function isTaskRunning($kill_pattern) {
        $logger = $this->container->get('logger');
        // get tasklist
        $task_list = array();

        exec("tasklist 2>NUL", $task_list);

        foreach ($task_list AS $task_line)
        {
            //$logger->warning('taskline='.$task_line);
            if (preg_match($kill_pattern, $task_line, $out))
            {
                //echo "=> Detected: ".$out[1]."\n";
                $logger->warning("Task Detected: ".$out[1]);
                //$logger->warning(print_r($out));
                //exec("taskkill /F /IM ".$out[1].".exe 2>NUL");
                return true;
            }
        }
        return false;
    }

    public function killTaskByName($taskname) {
        $logger = $this->container->get('logger');
        $logger->warning('killing task='.$taskname);
        exec("taskkill /F /IM ".$taskname.".* 2>NUL");
        $task_pattern = '~(soffice.bin|soffice.exe)~i';
        if( !$this->isTaskRunning($task_pattern) ) {
            $logger->warning('Deleted task='.$taskname);
        } else {
            $logger->warning('Failed to delete task='.$taskname);
        }
    }

    public function getQueue() {

        $queue = null;

        $queues = $this->em->getRepository('OlegFellAppBundle:ReportQueue')->findAll();

        //must be only one
        if( count($queues) > 0 ) {
            $queue = $queues[0];
        }

        if( count($queues) == 0 ) {
            $queue = new ReportQueue();
            $this->em->persist($queue);
            $this->em->flush();
        }

        return $queue;
    }

    public function resetQueue($queue,$process=null) {
        //reset queue
        if( $process ) {
            $queue->removeProcess($process);
            $this->em->remove($process);
        }

        $queue->setRunningProcess(NULL);
        $queue->setRunning(false);

        $this->em->flush();
        $this->runningGenerationReport = false;

        //clear start timestamp for all processes
        $query = $this->em->createQuery('UPDATE OlegFellAppBundle:Process p SET p.startTimestamp = NULL WHERE p.startTimestamp IS NOT NULL');
        $numUpdated = $query->execute();

        return $numUpdated;
    }



    //**************************************************************************************//
    ////////////////// generate Fellowship Application Report //////////////////////////////
    //generate Fellowship Application Report; can be run from console by: "php app/console fellapp:generatereport fellappid". fellappid is id of the fellowship application.
    public function generateFellAppReport( $id ) {

        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        $logger = $this->container->get('logger');

        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        $entity = $this->em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw new EntityNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        //generate file name: LastName_FirstName_FellowshipType_StartYear.pdf
        $currentDate = new \DateTime();
        $subjectUser = $entity->getUser();
//        $filename =
//            "ID".$id.
//            "_".$subjectUser->getLastNameUppercase().
//            "_".$subjectUser->getFirstNameUppercase().
//            "_".$entity->getFellowshipSubspecialty()->getName().
//            "_".$entity->getStartDate()->format('Y').
//            ".pdf";
        //Cytopathology-Fellowship-Application-2017-ID47-Smith-John-generated-on-12-25-2015-at-02-13-pm.pdf
        if( $entity->getFellowshipSubspecialty() ) {
            $fellappType = $entity->getFellowshipSubspecialty()->getName();
        } else {
            $fellappType = "Unknown";
            $logger->warning("Unknown fellowship type for fellapp id=".$entity->getId());
        }
        $fellappType = str_replace(" ","-",$fellappType);
        $filename =
            $fellappType."-Fellowship-Application".
            "-".$entity->getStartDate()->format('Y').
            "-ID".$id.
            "-".$subjectUser->getLastNameUppercase().
            "-".$subjectUser->getFirstNameUppercase().
            "-generated-on-".$currentDate->format('m-d-Y').'-at-'.$currentDate->format('h-i-s-a').'_EDT'.
            ".pdf";

        //replace all white spaces to _
        $filename = str_replace(" ","_",$filename);

        $logger->notice("Start to generate report for ID=".$id."; filename=".$filename);

        //check and create Report and temp folders
        $userUtil = new UserUtil();
        $reportsUploadPathFellApp = $userUtil->getSiteSetting($this->em,'reportsUploadPathFellApp');
        if( !$reportsUploadPathFellApp ) {
            $reportsUploadPathFellApp = "Reports";
            $logger->warning('reportsUploadPathFellApp is not defined in Site Parameters. Use default "'.$reportsUploadPathFellApp.'" folder.');
        }
        $uploadReportPath = $this->uploadDir.'/'.$reportsUploadPathFellApp;   //'Uploaded/'.$this->container->getParameter('fellapp.uploadpath').'/Reports';

        $reportPath = $this->container->get('kernel')->getRootDir() . '/../web/' . $uploadReportPath.'/';
        if( !file_exists($uploadReportPath) ) {
            mkdir($uploadReportPath, 0700, true);
            chmod($uploadReportPath, 0700);
        }

        $outdir = $reportPath.'temp_'.$id.'/';

        //echo "before generateApplicationPdf id=".$id."; outdir=".$outdir."<br>";
        //0) generate application pdf
        $applicationFilePath = $outdir . "application_ID" . $id . ".pdf";
        $this->generateApplicationPdf($id,$applicationFilePath);
        //$logger->notice("Successfully Generated Application PDF from HTML for ID=".$id."; file=".$applicationFilePath);

        //1) get all upload documents
        $filePathsArr = array();

        //itinerarys
        $itineraryDocument = $entity->getRecentItinerary();
        if( $itineraryDocument ) {
            $filePathsArr[] = $itineraryDocument->getFileSystemPath();
        }

        //check if photo is not image
        $photo = $entity->getRecentAvatar();
        if( $photo ) {
            $ext = pathinfo($photo->getOriginalName(), PATHINFO_EXTENSION);
            $photoUrl = null;
            if( $ext == 'pdf' ) {
                $filePathsArr[] = $photo->getFileSystemPath();
            }
        }

        //application form
        $filePathsArr[] = $applicationFilePath;

        //cv
        $recentDocumentCv = $entity->getRecentCv();
        if( $recentDocumentCv ) {
            $filePathsArr[] = $recentDocumentCv->getFileSystemPath();
        }

        //cover letter
        $recentCoverLetter = $entity->getRecentCoverLetter();
        if( $recentCoverLetter ) {
            $filePathsArr[] = $recentCoverLetter->getFileSystemPath();
        }

        //scores
        $scores = $entity->getExaminationScores();
        foreach( $scores as $score ) {
            $filePathsArr[] = $score->getFileSystemPath();
        }

        //Reprimand
        $reprimand = $entity->getRecentReprimand();
        if( $reprimand ) {
            $filePathsArr[] = $reprimand->getFileSystemPath();
        }

        //Legal Explanation
        $legalExplanation = $entity->getRecentLegalExplanation();
        if( $legalExplanation ) {
            $filePathsArr[] = $legalExplanation->getFileSystemPath();
        }

        //references
        $references = $entity->getReferenceLetters();
        foreach( $references as $reference ) {
            $filePathsArr[] = $reference->getFileSystemPath();
        }

        //other documents
        $otherDocuments = $entity->getDocuments();
        foreach( $otherDocuments as $otherDocument ) {
            $filePathsArr[] = $otherDocument->getFileSystemPath();
        }

        $createFlag = true;

        //2) convert all uploads to pdf using LibreOffice
        $fileNamesArr = $this->convertToPdf( $filePathsArr, $outdir );
        //$logger->notice("Successfully converted all uploads to PDF for ID=".$id."; files count=".count($fileNamesArr));

        //3) merge all pdfs
        $uniqueid = $filename;  //"report_ID" . $id;
        $fileUniqueName = $filename;    //$uniqueid . ".pdf";
        $filenameMerged = $reportPath . $fileUniqueName;
        $this->mergeByPDFMerger($fileNamesArr,$filenameMerged );
        //$logger->notice("Successfully generated Application report pdf ok; path=" . $filenameMerged );

        if( count($entity->getReports()) > 0 ) {
            $createFlag = false;
        }

        //4) add the report to application report DB
        $filesize = filesize($filenameMerged);
        $this->createFellAppReportDB($entity,$systemUser,$uniqueid,$filename,$fileUniqueName,$uploadReportPath,$filesize);

        //log event       
        if( $createFlag ) {
            $actionStr = "created";
        } else {
            $actionStr = "updated";
        }
        $event = "Report for Fellowship Application with ID".$id." has been successfully ".$actionStr." " . $filename;
        //echo $event."<br>";
        //$logger->notice($event);

        //eventType should be something 'Fellowship Application Report Updated'?
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$entity,null,'Fellowship Application Updated');


        //delete application temp folder
        $this->deleteDir($outdir);

        $res = array(
            'filename' => $filename,
            'report' => $filenameMerged,
            'size' => $filesize
        );

        $logger->notice($event);

        return $res;
    }
    ////////////////// EOF generate Fellowship Application Report //////////////////////////////
    //**************************************************************************************//


    //use KnpSnappyBundle to convert html to pdf
    //http://wkhtmltopdf.org must be installed on server
    public function generateApplicationPdf($applicationId,$applicationOutputFilePath) {
        $logger = $this->container->get('logger');

        if( file_exists($applicationOutputFilePath) ) {
            $logger->notice("generateApplicationPdf: unlink file already exists path=" . $applicationOutputFilePath );
            unlink($applicationOutputFilePath);
        }

        ini_set('max_execution_time', 300); //300 sec

        //generate application URL
        $router = $this->container->get('router');

        $context = $this->container->get('router')->getContext();
        
        //$rootDir = $this->container->get('kernel')->getRootDir();
        //echo "rootDir=".$rootDir."<br>";
        //echo "getcwd=".getcwd()."<br>";
        
        $env = $this->container->get('kernel')->getEnvironment();
        //echo "env=".$env."<br>";
        //$logger->notice("env=".$env."<br>");

        //http://192.168.37.128/order/app_dev.php/fellowship-applications/download-pdf/49
        $context->setHost('localhost');
        $context->setScheme('http');
        $context->setBaseUrl('/order');

//        if( $env == 'dev' ) {
//            //$context->setHost('localhost');
//            $context->setBaseUrl('/order/app_dev.php');
//        }
//        if( $env == 'prod' ) {
//            //$context->setHost('localhost');
//            $context->setBaseUrl('/order');
//        }
                
        //$context->setHost('localhost');
        //$context->setScheme('http');
        //$context->setBaseUrl('/scanorder/Scanorders2/web');
        
        //$url = $router->generate('fellapp_download',array('id' => $applicationId),true); //fellowship-applications/show/43
        //echo "url=". $url . "<br>";
        //$pageUrl = "http://localhost/order".$url;
        //http://localhost/scanorder/Scanorders2/web/fellowship-applications/
        //http://localhost/scanorder/Scanorders2/web/app_dev.php/fellowship-applications/?filter[startDate]=2017#
        
        //$pageUrl = "http://localhost/scanorder/Scanorders2/web/app_dev.php/fellowship-applications/download/".$applicationId;
        //$pageUrl = "http://localhost/scanorder/Scanorders2/web/fellowship-applications/download/".$applicationId;

        //fellapp_download
        $pageUrl = $router->generate('fellapp_download',array('id' => $applicationId),true); //this does not work from console: 'order' is missing
        //echo "pageurl=". $pageUrl . "<br>";

        //save session        
        //$session = $this->container->get('session');
        //$session->save();
        //session_write_close();
        //echo "seesion name=".$session->getName().", id=".$session->getId()."<br>";       

        //$logger->notice("before knp_snappy generate: pageUrl=".$pageUrl);

        //$application =
        $this->container->get('knp_snappy.pdf')->generate(
            $pageUrl,
            $applicationOutputFilePath
            //array('cookie' => array($session->getName() => $session->getId()))
        );

        //echo "generated ok! <br>";
    }

//    public function generateFromTwig($applicationId,$applicationOutputFilePath) {
//        $args = $this->getShowParameters($applicationId,'fellapp_home'); //fellapp_download
//        $this->container->get('knp_snappy.pdf')->generateFromHtml(
//            $this->renderView(
//                'OlegFellAppBundle:Form:download.html.twig',
//                $args
//            ),
//            $applicationOutputFilePath
//        );
//    }


  
//    private function logIn()
//    {
//        $firewall = 'ldap_fellapp_firewall';
//        
//        $userSecUtil = $this->container->get('user_security_utility');
//        $systemUser = $userSecUtil->findSystemUser();
//        $token = new UsernamePasswordToken($systemUser, null, $firewall, array('ROLE_PLATFORM_ADMIN'));
//        $this->container->get('security.context')->setToken($token);
//        $session = $this->container->get('session');
//        $session->set('_security_'.$firewall, serialize($token));
//        
//        //$this->container->get('security.token_storage')->setToken($token);
//        //We no longer need to manually save the token to the session either. 
//        //The token storage handles that                     
//        //$this->container->get('security.context')->setToken($token);
//        //$session = $this->container->get('session'); 
//        //$session->set('_security_'.$firewall, serialize($token));
//        $session->save(); 
//        session_write_close();
//        return $session;
//        
//        $session = $this->container->get('session');       
//        $token = new UsernamePasswordToken('systemuser_@_wcmc-cwid', 'systempassword', $firewall, array('ROLE_PLATFORM_ADMIN')); 
//        //$token->setAuthenticated(true);
//        $session->set('_security_'.$firewall, serialize($token));              
//        $this->sc->setToken($token);
//        //$session->save();
//        $tokenExisted = $this->sc->getToken();
//        if($tokenExisted->isAuthenticated() ) {
//            echo "token auth! <br>";
//        } else {
//            echo "token is not auth!!!!!!<br>";
//        }
//        //$session->save();
//        return $session;
//    }   
//    private function logIn1()
//    {
//        $session = $this->container->get('session');
//
//        $firewall = 'ldap_fellapp_firewall';
//        $token = new UsernamePasswordToken('system', null, $firewall, array('ROLE_FELLAPP_ADMIN'));
//        $session->set('_security_'.$firewall, serialize($token));
//        $session->save();
//
//        //$cookie = new Cookie($session->getName(), $session->getId());
//        //$this->container->getCookieJar()->set($cookie);
//    }
//    private function logIn2() {
//        $firewall = 'ldap_fellapp_firewall';
//        // create the authentication token
//        $userSecUtil = $this->container->get('user_security_utility');
//        $systemUser = $userSecUtil->findSystemUser();
//        $token = new UsernamePasswordToken(
//            $systemUser,
//            null,
//            $firewall,
//            $systemUser->getRoles());
//
//        // give it to the security context
//        $this->container->get('security.context')->setToken($token);
//
//        $session = $this->container->get('session');
//        $session->set('_security_'.$firewall, serialize($token));
//        //$session->save();
//
//        //save session
//        $session = $this->container->get('session');
////        $session->save();
////        session_write_close();
////        echo "session=".$session->getName() . ", id=". $session->getId() . "<br>";
//    }
    //    public function getShowParameters($id,$routeName) {
//        $userSecUtil = $this->container->get('user_security_utility');
//        $user = $userSecUtil->findSystemUser();
//        $em = $this->em;
//
//        //$fellApps = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->findAll();
//        $entity = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);
//
//        if( !$entity ) {
//            throw new EntityNotFoundException('Unable to find Fellowship Application by id='.$id);
//        }
//
//        if( $routeName == "fellapp_download" ) {
//            $cycle = 'download';
//            $disabled = true;
//            $method = "GET";
//            $action = null;
//        }
//
//        $params = array(
//            'cycle' => $cycle,
//            'sc' => $this->sc,
//            'em' => $em,
//            'user' => $entity->getUser(),
//            'cloneuser' => null,
//            'roles' => $user->getRoles()
//        );
//
//        $formFactory = $this->container->get('form.factory');
//        $form = $formFactory->create(
//            new FellowshipApplicationType($params),
//            $entity,
//            array(
//                'disabled' => $disabled,
//                //'method' => $method,
//                //'action' => $action
//            )
//        );
//
//        return array(
//            'form' => $form->createView(),
//            'entity' => $entity,
//            'pathbase' => 'fellapp',
//            'cycle' => $cycle,
//            'sitename' => $this->container->getParameter('fellapp.sitename')
//        );
//    }


    //convert all uploads to pdf using LibreOffice
    protected function convertToPdf( $filePathsArr, $outdir ) {

        $logger = $this->container->get('logger');
        $fileNamesArr = array();

        //C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\vendor\olegutil\LibreOfficePortable\App\libreoffice\program\soffice.exe
        //$cmd = '"C:\Program Files (x86)\LibreOffice 5\program\soffice" --headless -convert-to pdf -outdir "'.$outdir.'"';

        //'"C:\Program Files (x86)\LibreOffice 5\program\soffice" --headless -convert-to pdf -outdir'
        $userUtil = new UserUtil();
        $libreOfficeConvertToPDFCommandFellApp = $userUtil->getSiteSetting($this->em,'libreOfficeConvertToPDFCommandFellApp');
        if( !$libreOfficeConvertToPDFCommandFellApp ) {
            throw new \InvalidArgumentException('libreOfficeConvertToPDFCommandFellApp is not defined in Site Parameters.');
        }

        $cmd = $libreOfficeConvertToPDFCommandFellApp . ' "' . $outdir . '"';

        //echo "cmd=" . $cmd . "<br>";

        foreach( $filePathsArr as $filePath ) {

            //$outFilename = $outdir . basename($filePath);
            $outFilename = $outdir . pathinfo($filePath, PATHINFO_FILENAME) . ".pdf";
            //echo "outFilename=".$outFilename."<br>";
            //exit('1');

            $fileNamesArr[] = $outFilename;

            //if( file_exists($filePath) ) {
            //C:\Php\Wampp\wamp\www\scanorder\Scanorders2\web\Uploaded\fellapp\FellowshipApplicantUploads
            //C:\Php\Wampp\wamp\www\scanorder\Scanorders2\Uploaded/fellapp/FellowshipApplicantUploads/1440850972_id=0B2FwyaXvFk1eSDBwb1ZnUktkU3c.docx
            //quick fix for home
            //$filePath = str_replace("Wampp\wamp\www\scanorder\Scanorders2", "Wampp\wamp\www\scanorder\Scanorders2\web", $filePath);
            
            //echo "exists filePath=".$filePath."<br>";
            //continue;
            //}

            $cmd = $cmd .' "'.$filePath.'"';

            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            if( $ext != 'pdf' ) { //TESTING!!!

                //$shellout = shell_exec( $cmd );
                $shellout = exec( $cmd );

                if( $shellout ) {
                    //echo "shellout=".$shellout."<br>";
                    //$logger->notice("LibreOffice converted input file=" . $filePath);
                }

            } else {

                //$filePath = str_replace("/","\\",$filePath);
                //$filePath = '"'.$filePath.'"';

                echo "\nsource=".$filePath."\n<br>";
                echo "dest=".$outFilename."\n<br>";


                if( file_exists($filePath) ) {
                    echo "source exists \n<br>";
                } else {
                    echo "source does not exist\n<br>";
                }

                if( !file_exists($outFilename) ) {
                //if( strpos($filePath,'application_ID') === false ) {
                    if( !copy($filePath, $outFilename ) ) {
                        echo "failed to copy $filePath...\n<br>";
                    }
                }

                $shellout = ' pdf => just copied ';
            }


            //$logger->debug("convertToPdf: " . $shellout);

        }

        return $fileNamesArr;
    }

    //TODO: try https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/
    //if file already exists then it is replaced with a new one
    protected function mergeByPDFMerger_ORIG( $filesArr, $filenameMerged ) {
        $logger = $this->container->get('logger');
        $pdf = new PDFMerger();

        foreach( $filesArr as $file ) {
//            echo "add merge: filepath=(".$file.") => ";
            if( file_exists($file) ) {
                $pdf->addPDF($file, 'all');
                //$logger->notice("PDFMerger: merged file path=" . $file );
            } else {
                //$logger->warning("PDFMerger: pdf file does not exists path=" . $file );
                //new \Exception("PDFMerger: pdf file does not exists path=" . $file);
            }
        }

        $pdf->merge('file', $filenameMerged);
    }

    protected function mergeByPDFMerger( $filesArr, $filenameMerged ) {

        $logger = $this->container->get('logger');

        $filesStr = $this->convertFilesArrToString($filesArr);

        $filenameMerged = str_replace("/","\\", $filenameMerged);
        $filenameMerged = str_replace("app\..","", $filenameMerged);
        $filenameMerged = '"'.$filenameMerged.'"';

        //echo "filenameMerged=".$filenameMerged."<br>";

        //C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\vendor\olegutil\PDFTKBuilderPortable\App\pdftkbuilder\pdftk.exe
        $pdftkLocation = '"C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\vendor\olegutil\PDFTKBuilderPortable\App\pdftkbuilder\pdftk" ';
//        $userUtil = new UserUtil();
//        $pdftkLocation = $userUtil->getSiteSetting($this->em,'pdftkLocationFellApp');
//        if( !$pdftkLocation ) {
//            throw new \InvalidArgumentException('pdftkLocationFellApp is not defined in Site Parameters.');
//        }
//        $pdftkLocation = '"' . $pdftkLocation . '" ';

        //quick fix for c.med running on E:
        //if( strpos(getcwd(),'E:') !== false ) {
        //    $pdftkLocation = str_replace('C:','E:',$pdftkLocation);
        //}

        $cmd = $pdftkLocation . $filesStr . ' cat output ' . $filenameMerged . ' dont_ask';
        //echo "cmd=".$cmd."<br>";

        $output = null;
        $return = null;
        $shellout = exec( $cmd, $output, $return );
        //$shellout = exec( $cmd );

        //$logger->error("pdftk output: " . print_r($output));
        //$logger->error("pdftk return: " . $return);

        //return 0 => ok, return 1 => failed
        if( $return == 1 ) {

            //event log
            $event = "Probably there is an encrypted pdf: try to process by gs; pdftk failed cmd=" . $cmd;
            //echo $event."<br>";
            $logger->notice($event);
            $userSecUtil = $this->container->get('user_security_utility');
            $systemUser = $userSecUtil->findSystemUser();
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Fellowship Application Creation Failed');

            $filesInArr = $this->processFilesGostscript($filesArr);

            $filesInStr = $this->convertFilesArrToString($filesInArr, false);
            //$logger->notice('pdftk encrypted filesInStr='.$filesInStr);

            $cmd = $pdftkLocation . $filesInStr . ' cat output ' . $filenameMerged . ' dont_ask';
            //$logger->notice('pdftk encrypted: cmd='.$cmd);

            $output = null;
            $return = null;
            $shellout = exec( $cmd, $output, $return );
            //$shellout = exec( $cmd );

            //$logger->error("pdftk 2 output: " . print_r($output));
            //$logger->error("pdftk 2 return: " . $return);

            if( $return == 1 ) { //error
                //event log
                $event = "ERROR: 'Complete Application PDF' will not be generated! pdftk failed: " . $cmd;
                $logger->error($event);
                $fellappUtil = $this->container->get('fellapp_util');
                $fellappUtil->sendEmailToSystemEmail("Complete Application PDF will not be generated - pdftk failed", $event);

                $userSecUtil = $this->container->get('user_security_utility');
                $systemUser = $userSecUtil->findSystemUser();
                $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Fellowship Application Creation Failed');
            }

        }

//        if( file_exists($filenameMerged) ) {
//            echo "filenameMerged exists \n<br>";
//        } else {
//            echo "filenameMerged does not exist\n<br>";
//            //exit('my error');
//        }

    }

    public function convertFilesArrToString($filesArr,$withquotes=true) {
        $filesStr = "";

        foreach( $filesArr as $file ) {

            //echo "add merge: filepath=(".$file.") <br>";

            if( $withquotes ) {
                $filesStr = $filesStr . ' ' . '"' . $file . '"';
            } else {
                $filesStr = $filesStr . ' '  . $file;
            }

        }

        $filesStr = str_replace("/","\\", $filesStr);
        $filesStr = str_replace("app\..","", $filesStr);

        return $filesStr;
    }

    public function processFilesGostscript( $filesArr ) {

        $logger = $this->container->get('logger');

        $filesOutArr = array();

        $gsLocation = '"C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\vendor\olegutil\Ghostscript\bin\gswin64c.exe" ';
//        $userUtil = new UserUtil();
//        $gsLocation = $userUtil->getSiteSetting($this->em,'gsPathFellApp');
//        if( !$gsLocation ) {
//            throw new \InvalidArgumentException('gsPathFellApp is not defined in Site Parameters.');
//        }
//        $gsLocation = '"' . $gsLocation . '" ';

        //quick fix for c.med running on E:
//        if( strpos(getcwd(),'E:') !== false ) {
//            $gsLocation = str_replace('C:','E:',$gsLocation);
//        }

        foreach( $filesArr as $file ) {

            //$ "C:\Users\DevServer\Desktop\php\Ghostscript\bin\gswin64c.exe" -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile="C:\Temp New\out\out.pdf" -c .setpdfwrite -f "C:\Temp New\test.pdf"
            //"C:\Users\DevServer\Desktop\php\Ghostscript\bin\gswin64.exe"
            $cmd = $gsLocation . ' -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite ';

            //echo "add merge: filepath=(".$file.") <br>";
            $filesStr = '"' . $file . '"';

            $filesStr = str_replace("/","\\", $filesStr);
            $filesStr = str_replace("app\..","", $filesStr);

            $outFilename = pathinfo($file, PATHINFO_DIRNAME) . '\\' . pathinfo($file, PATHINFO_FILENAME) . "_gs.pdf";

            $outFilename = '"'.$outFilename.'"';

            $outFilename = str_replace("/","\\", $outFilename);
            $outFilename = str_replace("app\..","", $outFilename);

            //$logger->notice('GS: outFilename='.$outFilename);

            //gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=unencrypted.pdf -c .setpdfwrite -f encrypted.pdf
            $cmd = $cmd . '-sOutputFile=' . $outFilename . ' -c .setpdfwrite -f ' . $filesStr ;
            //$logger->notice('GS: cmd='.$cmd);

            $output = null;
            $return = null;
            exec( $cmd, $output, $return );

            //$logger->error("GS output: " . print_r($output));
            //$logger->error("GS return: " . $return);

            if( $return == 1 ) {
                //event log
                $event = "ERROR: 'Complete Application PDF' will no be generated! GS failed: " . $cmd;
                $logger->error($event);
                $fellappUtil = $this->container->get('fellapp_util');
                $fellappUtil->sendEmailToSystemEmail("Complete Application PDF will no be generated - GS failed", $event);

                $userSecUtil = $this->container->get('user_security_utility');
                $systemUser = $userSecUtil->findSystemUser();
                $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Fellowship Application Creation Failed');
            }

            $filesOutArr[] = $outFilename;

        }

        return $filesOutArr;
    }


    protected static function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new \InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }



    //create fellapp report in DB
    //path = Uploaded/fellapp/Reports
    protected function createFellAppReportDB($holderEntity,$author,$uniqueid,$title,$fileUniqueName,$path,$filesize) {

        $object = new Document($author);
        $object->setUniqueid($uniqueid);
        $object->setOriginalname($title);
        $object->setTitle($title);
        $object->setUniquename($fileUniqueName);
        $object->setUploadDirectory($path);
        $object->setSize($filesize);

        $fellappReportType = $this->em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName('Complete Fellowship Application in PDF');

        if( $fellappReportType ) {
            $object->setType($fellappReportType);
        }

        //remove all reports
        foreach( $holderEntity->getReports() as $report ) {
            $holderEntity->removeReport($report);
            $this->em->remove($report);
        }

        //add report
        $holderEntity->addReport($object);

        $this->em->persist($holderEntity);
        $this->em->persist($object);
        $this->em->flush();

    }






//    protected function spraed($html) {
//        $pdfGenerator = $this->get('spraed.pdf.generator');
//
//        return new Response($pdfGenerator->generatePDF($html),
//            200,
//            array(
//                'Content-Type' => 'application/pdf',
//                'Content-Disposition' => 'inline; filename="out.pdf"'
//            )
//        );
//
//        exit;
//    }
//
//    protected function html2pdf($html) {
//
//        //$params = $this->getShowParameters($id,'fellapp_download');
//        //$html = $this->renderView('OlegFellAppBundle:Form:download.html.twig',$params);
//
//        try {
//
//            //$html2pdf = $this->get('html2pdf_factory')->create('P','A4','fr');
//            $html2pdf = $this->get('html2pdf_factory')->create();
//
//            $html2pdf->pdf->SetDisplayMode('real');
//            //$html2pdf->pdf->SetDisplayMode('fullpage');
//            $html2pdf->writeHTML($html);
//            $html2pdf->Output('examplepdf.pdf');
//
//            //return new Response();
//            exit;
//
//        } catch(HTML2PDF_exception $e) {
//            echo $e;
//            exit;
//        }
//    }

    
    
    

} 