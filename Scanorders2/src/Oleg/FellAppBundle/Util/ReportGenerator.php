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
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

//use Symfony\Component\Process\Exception\ProcessFailedException;
//use Symfony\Component\Process\Process;

use Oleg\FellAppBundle\Entity\ReportQueue;
use Oleg\FellAppBundle\Entity\Process;


//The last working commit before changing directory separator: 78518efa68a8d81070ea87755f40586f4534faae

class ReportGenerator {


    protected $em;
    protected $container;
    protected $templating;
    protected $uploadDir;
    protected $processes;
    
    //protected $WshShell;
    protected $generatereportrunCmd;
    protected $runningGenerationReport;
    //protected $env;

    public function __construct( $em, $container, $templating ) {
        $this->em = $em;
        $this->container = $container;
        $this->templating = $templating;

        $this->uploadDir = 'Uploaded';

        //$this->generatereportrunCmd = 'php ../bin/console fellapp:generatereportrun --env=prod';
        $this->generatereportrunCmd = 'php ..'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'console fellapp:generatereportrun --env=prod';

        $this->runningGenerationReport = false;

        //TODO: check if user's time zones are still correct: this will overwrite the default time zone on the server!
        //date_default_timezone_set('America/New_York');
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

        $this->windowsCmdRunAsync($this->generatereportrunCmd);

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

        //add as a new process only if argument is 'overwrite' or process is not created yet
        if( $processesDb == null ) {
            $process = new Process($id);
            $process->setArgument($argument);
            $queue->addProcess($process);
            $this->em->flush();
            $logger->notice("Added new process to queue: Fellowship Application ID=".$id."; queue ID=".$queue->getId());
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
        $this->windowsCmdRunAsync($this->generatereportrunCmd);

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

        //$logger = $this->container->get('logger');
        //$logger->notice("Windows Cmd Run Sync: oExec=".$oExec);

        return $oExec;
    }
    
    public function tryRun() {

        $logger = $this->container->get('logger');
        $logger->notice("tryRun() started");

        $reportFileName = 'TryRun: Dummy Report File Name';
        
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
                    $logger->error("libreoffice is running and hang => kill task; fellapp id=" . $process->getFellappId() );
                } else {
                    //$this->killTaskByName("soffice");
                    //wait and try run again?
                    $logger->error("libreoffice is running but not hang => return (wait until next try run); fellapp id=" . $process->getFellappId() );
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
            $this->windowsCmdRunAsync($this->generatereportrunCmd);

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
        if( round(abs($nowtime - $started)) > 600 ) { //10min*60sec=600sec (10 minutes) limit
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

        $logger = $this->container->get('logger');
        $queue = null;

        $queues = $this->em->getRepository('OlegFellAppBundle:ReportQueue')->findAll();
        //$logger->notice("Current queue count=".count($queues));

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
        $fileFullReportUniqueName = $this->constructUniqueFileName($entity,"Fellowship-Application");
        $logger->notice("Start to generate full report for ID=".$id."; filename=".$fileFullReportUniqueName);

        //check and create Report and temp folders
        $reportsUploadPathFellApp = $userSecUtil->getSiteSettingParameter('reportsUploadPathFellApp');
        if( !$reportsUploadPathFellApp ) {
            $reportsUploadPathFellApp = "Reports";
            $logger->warning('reportsUploadPathFellApp is not defined in Site Parameters. Use default "'.$reportsUploadPathFellApp.'" folder.');
        }
        //$uploadReportPath = $this->uploadDir.'/'.$reportsUploadPathFellApp;
        $uploadReportPath = $this->uploadDir.DIRECTORY_SEPARATOR.$reportsUploadPathFellApp;

        //$reportPath = $this->container->get('kernel')->getRootDir() . '/../web/' . $uploadReportPath;
        $reportPath = $this->container->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR. $uploadReportPath;
        $reportPath = realpath($reportPath);

        if( !file_exists($reportPath) ) {
            mkdir($reportPath, 0700, true);
            chmod($reportPath, 0700);
        }

        //It works
        $outdir = $reportPath.'/temp_'.$id.'/';
        //$logger->notice("1 outdir=".$outdir);

        //Don't use it: DIRECTORY_SEPARATOR CAUSED ERROR: 'Complete Application PDF' will no be generated! GS failed:
        //$outdir = $reportPath . DIRECTORY_SEPARATOR . 'temp_'.$id . DIRECTORY_SEPARATOR;
        //$logger->notice("2 outdir=".$outdir);

        //echo "before generateApplicationPdf id=".$id."; outdir=".$outdir."<br>";
        //0) generate application pdf
        $applicationFilePath = $outdir . "application_ID" . $id . ".pdf";
        //$logger->notice("applicationFilePath=".$applicationFilePath);
        $this->generateApplicationPdf($id,$applicationFilePath);
        //$logger->notice("Successfully Generated Application PDF from HTML for ID=".$id."; file=".$applicationFilePath);

        //1) get all upload documents
        $filePathsArr = array();
        $fileErrors = array();

        //itinerarys
        $itineraryDocument = $entity->getRecentItinerary();
        if( $itineraryDocument ) {
            if( $this->isValidFile($itineraryDocument,$fileErrors,"Itenirary") ) {
                $filePathsArr[] = $userSecUtil->getAbsoluteServerFilePath($itineraryDocument);
            }
        }

        //check if photo is not image
        $photo = $entity->getRecentAvatar();
        if( $photo ) {
            if( $this->isValidFile($photo,$fileErrors,"Photo") ) {
                $ext = pathinfo($photo->getOriginalnameClean(), PATHINFO_EXTENSION);
                $photoUrl = null;
                if ($ext == 'pdf') {
                    $filePathsArr[] = $userSecUtil->getAbsoluteServerFilePath($photo);
                }
            }
        }

        //application form
        if( $applicationFilePath ) {
            $filePathsArr[] = $applicationFilePath;
        }

        //cv
        $recentDocumentCv = $entity->getRecentCv();
        if( $recentDocumentCv ) {
            if( $this->isValidFile($recentDocumentCv,$fileErrors,"CV") ) {
                $filePathsArr[] = $userSecUtil->getAbsoluteServerFilePath($recentDocumentCv);
            }
        }

        //cover letter
        $recentCoverLetter = $entity->getRecentCoverLetter();
        if( $recentCoverLetter ) {
            if( $this->isValidFile($recentCoverLetter,$fileErrors,"Cover Letter") ) {
                $filePathsArr[] = $userSecUtil->getAbsoluteServerFilePath($recentCoverLetter);
            }
        }

        //scores
        $scores = $entity->getExaminationScores();
        foreach( $scores as $score ) {
            if( $this->isValidFile($score,$fileErrors,"Score") ) {
                $filePathsArr[] = $userSecUtil->getAbsoluteServerFilePath($score);
            }
        }

        //Reprimand
        $reprimand = $entity->getRecentReprimand();
        if( $reprimand ) {
            if( $this->isValidFile($reprimand,$fileErrors,"Reprimand") ) {
                $filePathsArr[] = $userSecUtil->getAbsoluteServerFilePath($reprimand);
            }
        }

        //Legal Explanation
        $legalExplanation = $entity->getRecentLegalExplanation();
        if( $legalExplanation ) {
            if( $this->isValidFile($legalExplanation,$fileErrors,"Legal Explanation") ) {
                $filePathsArr[] = $userSecUtil->getAbsoluteServerFilePath($legalExplanation);
            }
        }

        //references
        $references = $entity->getReferenceLetters();
        foreach( $references as $reference ) {
            if( $this->isValidFile($reference,$fileErrors,"Reference Letter") ) {
                $filePathsArr[] = $userSecUtil->getAbsoluteServerFilePath($reference);
            }
        }

        //other documents
        $otherDocuments = $entity->getDocuments();
        foreach( $otherDocuments as $otherDocument ) {
            if( $this->isValidFile($otherDocument,$fileErrors,"Other Document") ) {
                $filePathsArr[] = $userSecUtil->getAbsoluteServerFilePath($otherDocument);
            }
        }

        //Send a single warning email to admin
        if( count($fileErrors) > 0 ) {
            //print_r($fileErrors);
            //$logger->notice("fileErrors=".print_r($fileErrors));
            //exit();

            //fellapp admin
            $confirmationEmailFellApp = $userSecUtil->getSiteSettingParameter('confirmationEmailFellApp');
            $toEmailsArr = array($confirmationEmailFellApp);

            ////////// Potential error subject //////////////
            //Subject: PDF not properly generated for Applicant [XXX]
            $fellappInfo = $entity->getInfo();
            $hostname = "(" . $userSecUtil->getSiteSettingParameter('environment') . " server)";
            $errorEmailSubject = "PDF not properly generated for application $fellappInfo $hostname";
            //$logger->notice("errorEmailSubject=".$errorEmailSubject);
            ////////// EOF Potential error subject //////////////

            //Body: The fellowship application system was unable to generate the complete application PDF file
            // for applicant [XXX] to [FellowshipSpecialty], FirstName LastName at HH:MM on MM/DD/YYYY.
            $errorMsg = "The fellowship application system was unable to properly generate the complete application PDF file".
                        " for application $fellappInfo. <br><br>Corrupted PDF file(s):<br><br>";
            $errorMsg = $errorMsg . implode("<br><br>",$fileErrors);

            //To address this issue, please follow these steps:
            $errorMsg = $errorMsg . "<br><br>" . "Please replace the corrupted file(s) for this applicant.";
            //$logger->error($errorMsg);
            $userSecUtil->sendEmailToSystemEmail($errorEmailSubject,$errorMsg,$toEmailsArr);
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$errorMsg,$systemUser,null,null,'Corrupted File');
        }

        $createFlag = true;

        //2) convert all uploads to pdf using LibreOffice
        $fileNamesArr = $this->convertToPdf( $filePathsArr, $outdir, $entity );
        //$logger->notice("Successfully converted all uploads to PDF for ID=".$id."; files count=".count($fileNamesArr));

        //3) merge all pdfs
        //$uniqueid = $filename;  //"report_ID" . $id;
        //$fileUniqueName = $filename;    //$uniqueid . ".pdf";
        //$filenameMerged = $reportPath . '/' . $fileFullReportUniqueName;
        $filenameMerged = $reportPath . DIRECTORY_SEPARATOR . $fileFullReportUniqueName;
        $this->mergeByPDFMerger($fileNamesArr,$filenameMerged,$entity);
        //$logger->notice("Successfully generated Application report pdf ok; path=" . $filenameMerged );

        if( count($entity->getReports()) > 0 ) {
            $createFlag = false;
        }

        //4) add the report to application report DB
        $filesize = filesize($filenameMerged);
        $deleteOldFileFromServer = false;
        $documentPdf = $this->createFellAppReportDB($entity,"report",$systemUser,$fileFullReportUniqueName,$uploadReportPath,$filesize,'Complete Fellowship Application PDF',$deleteOldFileFromServer);
        if( $documentPdf ) {
            $documentPdfId = $documentPdf->getId();
        } else {
            $documentPdfId = null;
        }

        //keep application form pdf for "Application PDF without attached documents"
        $fileUniqueName = $this->constructUniqueFileName($entity,"Fellowship-Application-Without-Attachments");
        //$formReportPath = $reportPath . '/' . $fileUniqueName;
        $formReportPath = $reportPath . DIRECTORY_SEPARATOR . $fileUniqueName;
        if( file_exists($applicationFilePath) ) {
            if( !copy($applicationFilePath, $formReportPath ) ) {
                //echo "failed to copy $applicationFilePath...\n<br>";
                $logger->warning("failed to copy Application PDF without attached documents ".$applicationFilePath);
            } else {
                $formReportSize = filesize($formReportPath);
                //$holderEntity,$holderMethodSingularStr,$author,$uniqueTitle,$path,$filesize,$documentType
                $deleteOldFileFromServer = true;
                $this->createFellAppReportDB($entity,"formReport",$systemUser,$fileUniqueName,$uploadReportPath,$formReportSize,'Fellowship Application PDF Without Attached Documents',$deleteOldFileFromServer);
            }
        } else {
            $logger->warning("Original Application PDF without attached documents does not exists on path: ".$applicationFilePath);
        }

        //log event       
        if( $createFlag ) {
            $actionStr = "created";
        } else {
            $actionStr = "updated";
        }
        $event = "Report for Fellowship Application with ID".$id." has been successfully ".$actionStr." " . $fileFullReportUniqueName . " (PDF document ID".$documentPdfId.")";
        //echo $event."<br>";
        //$logger->notice($event);

        //eventType should be something 'Fellowship Application Report Updated'?
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$entity,null,'Fellowship Application Updated');


        //delete application temp folder
        $this->deleteDir($outdir);

        $res = array(
            'filename' => $fileFullReportUniqueName,
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
        $userSecUtil = $this->container->get('user_security_utility');

        if( file_exists($applicationOutputFilePath) ) {
            $logger->notice("generateApplicationPdf: unlink file already exists path=" . $applicationOutputFilePath );
            unlink($applicationOutputFilePath);
        }

        ini_set('max_execution_time', 300); //300 sec

        $connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
        if( !$connectionChannel ) {
            $connectionChannel = 'http';
        }

        //generate application URL
        $router = $this->container->get('router');

        $context = $this->container->get('router')->getContext();
        
        //$rootDir = $this->container->get('kernel')->getRootDir();
        //echo "rootDir=".$rootDir."<br>";
        //echo "getcwd=".getcwd()."<br>";
        
        //$env = $this->container->get('kernel')->getEnvironment();
        //echo "env=".$env."<br>";
        //$logger->notice("env=".$env."<br>");

        //http://192.168.37.128/order/app_dev.php/fellowship-applications/download-pdf/49
        $context->setHost('localhost');
        $context->setScheme($connectionChannel);
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
        $pageUrl = $router->generate('fellapp_download',array('id' => $applicationId),UrlGeneratorInterface::ABSOLUTE_URL); //this does not work from console: 'order' is missing
        //$logger->notice("### pageUrl=".$pageUrl);
        //echo "pageurl=". $pageUrl . "<br>";
        //exit();

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

    //convert all uploads to pdf using LibreOffice
    protected function convertToPdf( $filePathsArr, $outdir, $entity ) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');
        $systemUser = $userSecUtil->findSystemUser();

        ////////// Potential error subject //////////////
        $fellappInfo = $entity->getInfo();
        $hostname = "(" . $userSecUtil->getSiteSettingParameter('environment') . " server)";
        $errorEmailSubject = "PDF conversion failed for Applicant $fellappInfo $hostname";
        //$logger->notice("errorEmailSubject=".$errorEmailSubject);
        ////////// EOF Potential error subject //////////////

        //fellapp admin
        $confirmationEmailFellApp = $userSecUtil->getSiteSettingParameter('confirmationEmailFellApp');
        $toEmailsArr = array($confirmationEmailFellApp);

        $fileNamesArr = array();

        if( $userServiceUtil->isWinOs() ) {
            //$logger->notice('libreOffice Windows');
            //C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\vendor\olegutil\LibreOfficePortable\App\libreoffice\program\soffice.exe
            //$cmd = '"C:\Program Files (x86)\LibreOffice 5\program\soffice" --headless -convert-to pdf -outdir "'.$outdir.'"';
            //"C:\Program Files (x86)\LibreOffice 5\program\soffice" --headless -convert-to pdf -outdir
            $libreOfficeConvertToPDFPathFellApp = $userSecUtil->getSiteSettingParameter('libreOfficeConvertToPDFPathFellApp');
            if (!$libreOfficeConvertToPDFPathFellApp) {
                throw new \InvalidArgumentException('libreOfficeConvertToPDFPathFellApp is not defined in Site Parameters.');
            }

            $libreOfficeConvertToPDFFilenameFellApp = $userSecUtil->getSiteSettingParameter('libreOfficeConvertToPDFFilenameFellApp');
            if (!$libreOfficeConvertToPDFFilenameFellApp) {
                throw new \InvalidArgumentException('libreOfficeConvertToPDFFilenameFellApp is not defined in Site Parameters.');
            }

            $libreOfficeConvertToPDFArgumentsdFellApp = $userSecUtil->getSiteSettingParameter('libreOfficeConvertToPDFArgumentsdFellApp');
            if (!$libreOfficeConvertToPDFArgumentsdFellApp) {
                throw new \InvalidArgumentException('libreOfficeConvertToPDFArgumentsdFellApp is not defined in Site Parameters.');
            }
        } else {
            //$logger->notice('libreOffice not Windows');
            $libreOfficeConvertToPDFPathFellApp = $userSecUtil->getSiteSettingParameter('libreOfficeConvertToPDFPathFellAppLinux');
            if (!$libreOfficeConvertToPDFPathFellApp) {
                throw new \InvalidArgumentException('libreOfficeConvertToPDFPathFellAppLinux is not defined in Site Parameters.');
            }

            $libreOfficeConvertToPDFFilenameFellApp = $userSecUtil->getSiteSettingParameter('libreOfficeConvertToPDFFilenameFellAppLinux');
            if (!$libreOfficeConvertToPDFFilenameFellApp) {
                throw new \InvalidArgumentException('libreOfficeConvertToPDFFilenameFellAppLinux is not defined in Site Parameters.');
            }

            $libreOfficeConvertToPDFArgumentsdFellApp = $userSecUtil->getSiteSettingParameter('libreOfficeConvertToPDFArgumentsdFellAppLinux');
            if (!$libreOfficeConvertToPDFArgumentsdFellApp) {
                throw new \InvalidArgumentException('libreOfficeConvertToPDFArgumentsdFellAppLinux is not defined in Site Parameters.');
            }
        }

        //$cmd = '"' . $libreOfficeConvertToPDFPathFellApp . '\\' . $libreOfficeConvertToPDFFilenameFellApp .
        //       '" ' . $libreOfficeConvertToPDFArgumentsdFellApp . ' "' . $outdir . '"';

        $cmd = '"' . $libreOfficeConvertToPDFPathFellApp . DIRECTORY_SEPARATOR . $libreOfficeConvertToPDFFilenameFellApp .
            '" ' . $libreOfficeConvertToPDFArgumentsdFellApp . ' "' . $outdir . '"';

        //echo "cmd=" . $cmd . "<br>";

        //$logger->notice("Convert to PDF: input file count=".count($filePathsArr));

        foreach( $filePathsArr as $filePath ) {

            $filePath = realpath($filePath);

            if( !file_exists($filePath) ) {
                $errorMsg = "Convert to PDF: Input file does not exist for Fellowship Application $fellappInfo: filePath=".$filePath;
                $logger->error($errorMsg);
                $userSecUtil->sendEmailToSystemEmail("Convert to PDF: Input file does not exist",$errorMsg,$toEmailsArr);
                $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$errorMsg,$systemUser,null,null,'Corrupted File');
                continue; //ignore this file
            }

            //$outFilename = $outdir . basename($filePath);
            $outFilename = $outdir . pathinfo($filePath, PATHINFO_FILENAME) . ".pdf";
            //echo "outFilename=".$outFilename."<br>";
            //exit('1');

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

                //$logger->notice("###PDF converting: cmd=".$cmd);

                //$shellout = shell_exec( $cmd );
                $shellout = exec( $cmd );

                if( $shellout ) {
                    //echo "shellout=".$shellout."<br>";
                    //$logger->notice("LibreOffice converted input file=" . $filePath);
                } else {
                    $errorMsg = "Fellowship Application $fellappInfo - LibreOffice failed to convert input file=" . $filePath;
                    $logger->error($errorMsg);
                    $userSecUtil->sendEmailToSystemEmail($errorEmailSubject,$errorMsg,$toEmailsArr);
                    $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$errorMsg,$systemUser,null,null,'Corrupted File');
                    continue; //ignore this file
                }

                if( !file_exists($outFilename) ) {
                    $errorMsg = "Fellowship Application $fellappInfo - Output file does not exist after PDF generation!!!: outFilename=".$outFilename;
                    $logger->error($errorMsg);
                    $userSecUtil->sendEmailToSystemEmail($errorEmailSubject,$errorMsg,$toEmailsArr);
                    $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$errorMsg,$systemUser,null,null,'Corrupted File');
                    continue; //ignore this file
                }

            } else {

                //$filePath = str_replace("/","\\",$filePath);
                //$filePath = '"'.$filePath.'"';

                //echo "\nsource=".$filePath."\n<br>";
                //echo "dest=".$outFilename."\n<br>";


                if( file_exists($filePath) ) {
                    //ok
                } else {
                    //echo "source does not exist\n<br>";
                    $errorMsg = "Fellowship Application $fellappInfo - convert To Pdf: source does not exist; filePath=".$filePath;
                    $logger->error($errorMsg);
                    $userSecUtil->sendEmailToSystemEmail($errorEmailSubject,$errorMsg,$toEmailsArr);
                    $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$errorMsg,$systemUser,null,null,'Corrupted File');
                    continue; //ignore this file
                }

                //check if this PDF is readable
                if( $this->isPdfCorrupted($filePath) ) {
                    //Body: The fellowship application system was unable to generate the complete application PDF file
                    // for applicant [XXX] to [FellowshipSpecialty], FirstName LastName at HH:MM on MM/DD/YYYY.
                    $errorMsg = "The fellowship application system was unable to convert the file $filePath to PDF for application $fellappInfo. <br>Error: PDF is corrupted";
                    $errorMsg = $errorMsg . "<br>" . "Please replace the corrupted file for this applicant.";
                    $logger->error($errorMsg);
                    $userSecUtil->sendEmailToSystemEmail($errorEmailSubject,$errorMsg,$toEmailsArr);
                    $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$errorMsg,$systemUser,null,null,'Corrupted File');
                    continue; //ignore this file
                }

                if( !file_exists($outFilename) ) {
                    if( !copy($filePath, $outFilename ) ) {
                        //echo "failed to copy $filePath...\n<br>";
                        $errorMsg = "Fellowship Application $fellappInfo - Failed to copy to temp folder; filePath=".$filePath;
                        $logger->error($errorMsg);
                        $userSecUtil->sendEmailToSystemEmail($errorEmailSubject,$errorMsg,$toEmailsArr);
                        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$errorMsg,$systemUser,null,null,'Corrupted File');
                        continue; //ignore this file
                    }
                }

                //$shellout = ' pdf => just copied ';
            }

            //add file to PDF array
            $fileNamesArr[] = $outFilename;

            //$logger->notice("convertToPdf: " . $shellout);

        }//foreach

        return $fileNamesArr;
    }
    public function isPdfCorrupted($filePath) {
        $logger = $this->container->get('logger');
        $corrupted = 0;
        //$logger->notice("Checking PDF=".$filePath);

        ////////// checking header "%PDF-" ///////////////
        $fp = fopen($filePath, 'r');
        // move to the 0th byte
        fseek($fp, 0);
        $data = fread($fp, 5);   // read 5 bytes from byte 0
        //echo "Header=".$data."<br>";
        //$logger->notice("Header=".$data);
        //if(strcmp($data,"%PDF-")==0) {
        if( strpos($data, '%PDF-') !== false ) {
            //echo "The PDF File is not Corrupted.<br>";
        } else {
            //echo "The PDF File is  Corrupted.<br>";
            $logger->error("Header is not valid: [".$data . "] for ".$filePath);
            $corrupted++;
        }
        fclose($fp);
        ////////// EOF checking header "%PDF-" ///////////////

        ///////////// checking footer "%%EOF" ///////////////
        if(1) {
            $file = file($filePath);
            $endfile = trim($file[count($file) - 1]);
            //echo "endfile=".$endfile."<br>";
            //$logger->notice("endfile=" . $endfile);
            $n = "%%EOF";
            //if( $endfile === $n ) {
            if( strpos($endfile, $n) !== false ) {
                //echo "good <br>";
            } else {
                //echo "corrupted <br>";
                $logger->error("Endfile is not valid: [".$endfile . "] for ".$filePath);
                $corrupted++;
            }
        }
        ///////////// EOF checking footer "%%EOF" ///////////////

        //echo "Corrupted count=".$corrupted."<br>";
        //$logger->notice("Corrupted count=".$corrupted);

        if( $corrupted === 0 ) {
            return false;
        }

        return true;
    }
    public function isValidFile( $file, &$fileErrors, $fileType ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $filePath = $userSecUtil->getAbsoluteServerFilePath($file);
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if( $ext == 'pdf' ) {
            if( $this->isPdfCorrupted($filePath) ) {
                $error = "<b>".$fileType . "</b>" . " (" . $file->getDescriptiveFilename() . ")";

                //The uploaded file that appears to have caused this issue is located here:
                $error = $error . "<br>" . "The location of the corrupted file " . $filePath;

                //and can also be viewed by following this link:
                $router = $this->container->get('router');
                $fileLink = $router->generate(
                    'fellapp_file_download',
                    array(
                        'id' => $file->getId()
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $fileLink = '<a href="'.$fileLink.'">'.$fileLink.'</a>';
                $error = $error . " and can also be viewed by following this link: " . $fileLink;

                $fileErrors[] = $error;

                return false;
            }
        }
        return true;
    }

//    //TODO: try https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/
//    //if file already exists then it is replaced with a new one
//    protected function mergeByPDFMerger_ORIG( $filesArr, $filenameMerged ) {
//        $logger = $this->container->get('logger');
//        $pdf = new PDFMerger();
//
//        foreach( $filesArr as $file ) {
////            echo "add merge: filepath=(".$file.") => ";
//            if( file_exists($file) ) {
//                $pdf->addPDF($file, 'all');
//                //$logger->notice("PDFMerger: merged file path=" . $file );
//            } else {
//                //$logger->warning("PDFMerger: pdf file does not exists path=" . $file );
//                //new \Exception("PDFMerger: pdf file does not exists path=" . $file);
//            }
//        }
//
//        $pdf->merge('file', $filenameMerged);
//    }

    protected function mergeByPDFMerger( $filesArr, $filenameMerged, $fellapp ) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');

        $filesStr = $this->convertFilesArrToString($filesArr);

        //$filenameMerged = str_replace("/","\\", $filenameMerged);
        //$filenameMerged = str_replace("app\..","", $filenameMerged);
        $filenameMerged = $this->strReplace($filenameMerged);

        $filenameMerged = '"'.$filenameMerged.'"';

        //echo "filenameMerged=".$filenameMerged."<br>";

        if( $userServiceUtil->isWinOs() ) {
            //$logger->notice('pdftk Windows');
            //C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\vendor\olegutil\PDFTKBuilderPortable\App\pdftkbuilder\pdftk.exe
            //$pdftkLocation = '"C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\vendor\olegutil\PDFTKBuilderPortable\App\pdftkbuilder\pdftk" ';
            $userUtil = new UserUtil();
            $pdftkPathFellApp = $userUtil->getSiteSetting($this->em, 'pdftkPathFellApp');
            if (!$pdftkPathFellApp) {
                throw new \InvalidArgumentException('pdftkPathFellApp is not defined in Site Parameters.');
            }

            $pdftkFilenameFellApp = $userUtil->getSiteSetting($this->em, 'pdftkFilenameFellApp');
            if (!$pdftkFilenameFellApp) {
                throw new \InvalidArgumentException('pdftkFilenameFellApp is not defined in Site Parameters.');
            }

            $pdftkArgumentsFellApp = $userUtil->getSiteSetting($this->em, 'pdftkArgumentsFellApp');
            if (!$pdftkArgumentsFellApp) {
                throw new \InvalidArgumentException('pdftkArgumentsFellApp is not defined in Site Parameters.');
            }
        } else {
            //$logger->notice('pdftk not Windows');
            $userUtil = new UserUtil();
            $pdftkPathFellApp = $userUtil->getSiteSetting($this->em, 'pdftkPathFellAppLinux');
            if (!$pdftkPathFellApp) {
                throw new \InvalidArgumentException('pdftkPathFellAppLinux is not defined in Site Parameters.');
            }

            $pdftkFilenameFellApp = $userUtil->getSiteSetting($this->em, 'pdftkFilenameFellAppLinux');
            if (!$pdftkFilenameFellApp) {
                throw new \InvalidArgumentException('pdftkFilenameFellAppLinux is not defined in Site Parameters.');
            }

            $pdftkArgumentsFellApp = $userUtil->getSiteSetting($this->em, 'pdftkArgumentsFellAppLinux');
            if (!$pdftkArgumentsFellApp) {
                throw new \InvalidArgumentException('pdftkArgumentsFellAppLinux is not defined in Site Parameters.');
            }
        }

        //$pdftkLocation = '"' . $pdftkPathFellApp . '\\' . $pdftkFilenameFellApp . '"';
        $pdftkLocation = '"' . $pdftkPathFellApp . DIRECTORY_SEPARATOR . $pdftkFilenameFellApp . '"';

        //quick fix for c.med running on E:
        //collage is running on C:
//        if( strpos(getcwd(),'E:') !== false ) {
//            $pdftkLocation = str_replace('C:','E:',$pdftkLocation);
//        }

        //$cmd = $pdftkLocation . $filesStr . ' cat output ' . $filenameMerged . ' dont_ask';

        //replace ###parameter### by appropriate variable
        //###inputFiles### cat output ###outputFile### dont_ask
        $pdftkArgumentsFellApp = str_replace('###inputFiles###',$filesStr,$pdftkArgumentsFellApp);
        $pdftkArgumentsFellApp = str_replace('###outputFile###',$filenameMerged,$pdftkArgumentsFellApp);

        $cmd = $pdftkLocation . ' ' . $pdftkArgumentsFellApp;

        //echo "cmd=".$cmd."<br>";

        $output = null;
        $return = null;
        $shellout = exec( $cmd, $output, $return );
        //$shellout = exec( $cmd );

        //$logger->error("pdftk output: " . print_r($output));
        //$logger->error("pdftk return: " . $return);

        //return 0 => ok, return 1 => failed
        if( $return == 1 ) {

            //$logger->error("pdftk return: " . implode("; ",$return));
            $logger->error("pdftk return=".$return."; output=".print_r($output));

            //from command cause Error:
            //ERROR: 'Complete Application PDF' will not be generated! pdftk failed:
            // "E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\\web\Uploaded\fellapp\Reports\temp_192\application_ID192.pdf"
            // "E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\\web\Uploaded\fellapp\Reports\temp_192\1460046558ID0B2FwyaXvFk1edVYta1FTLThEalk.pdf"
            // "E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\\web\Uploaded\fellapp\Reports\temp_192\1460046558ID0B2FwyaXvFk1eendWbUdzV0ZNelU.pdf"
            // "E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\\web\Uploaded\fellapp\Reports\temp_192\1460046559ID0B2FwyaXvFk1eMWdxSjhGdDBWQW8.pdf"
            // cat output
            // "E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\\web\Uploaded\fellapp\Reports\Breast-Pathology-Fellowship-Application-2018-ID192-Doe7-Linda-generated-on-04-07-2016-at-05-12-14-pm_UTC.pdf"
            // dont_ask [] []
            // reason: 1460046558ID0B2FwyaXvFk1edVYta1FTLThEalk files don't exists
            //correct: E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\web\Uploaded\fellapp\Reports
            //actual : E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\web\Uploaded\fellapp\Reports

            //event log
            $event = "Probably there is an encrypted pdf: try to process by gs; pdftk failed cmd=" . $cmd;
            //echo $event."<br>";
            $logger->warning($event);
            $systemUser = $userSecUtil->findSystemUser();
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Fellowship Application Creation Failed');

            $filesInArr = $this->processFilesGostscript($filesArr);
            //$logger->notice("GS output; filesInArr=".implode("; ",$filesInArr));

            $filesInStr = $this->convertFilesArrToString($filesInArr, false);
            //$logger->warning('pdftk encrypted filesInStr='.$filesInStr);

            //$cmd = $pdftkLocation . $filesInStr . ' cat output ' . $filenameMerged . ' dont_ask';

            //replace ###parameter### by appropriate variable
            $pdftkArgumentsFellApp = $userUtil->getSiteSetting($this->em,'pdftkArgumentsFellApp');
            if( !$pdftkArgumentsFellApp ) {
                throw new \InvalidArgumentException('pdftkArgumentsFellApp is not defined in Site Parameters.');
            }

            //###inputFiles### cat output ###outputFile### dont_ask
            $pdftkArgumentsFellApp = str_replace('###inputFiles###',$filesInStr,$pdftkArgumentsFellApp);
            $pdftkArgumentsFellApp = str_replace('###outputFile###',$filenameMerged,$pdftkArgumentsFellApp);

            $cmd = $pdftkLocation . ' ' . $pdftkArgumentsFellApp;

            //$logger->notice('pdftk encrypted: cmd='.$cmd);

            $output = null;
            $return = null;
            $shellout = exec( $cmd, $output, $return );
            //$shellout = exec( $cmd );

            //$logger->error("pdftk 2 output: " . print_r($output));
            //$logger->error("pdftk 2 return: " . $return);

            if( $return == 1 ) { //error
                //event log
                $subjectUser = $fellapp->getUser();
                $fellappInfoStr = "ID #".$fellapp->getId()." (".$subjectUser."): ";
                $event = "ERROR: ".$fellappInfoStr."'Complete Application PDF' will not be generated! Probably there is an encrypted pdf. pdftk second run failed: " . $cmd;
                $logger->error($event);
                $logger->error("pdftk second run return=".$return."; output=".print_r($output));
                //$logger->error("GS return=".implode("; ",$return));

                //send email
                $userSecUtil->sendEmailToSystemEmail("ERROR: ".$fellappInfoStr."Probably there is an encrypted pdf. Complete Application PDF will not be generated - pdftk failed", $event);

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

        //$filesStr = str_replace("/","\\", $filesStr);
        //$filesStr = str_replace("app\..","", $filesStr);
        $filesStr = $this->strReplace($filesStr);

        return $filesStr;
    }

    public function processFilesGostscript( $filesArr ) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->container->get('user_service_utility');
        $systemUser = $userSecUtil->findSystemUser();

        $filesOutArr = array();

        if( $userServiceUtil->isWinOs() ) {
            //$logger->notice('gs Windows');
            //$gsLocation = '"C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\vendor\olegutil\Ghostscript\bin\gswin64c.exe" ';
            $userUtil = new UserUtil();
            $gsPathFellApp = $userUtil->getSiteSetting($this->em, 'gsPathFellApp');
            if (!$gsPathFellApp) {
                throw new \InvalidArgumentException('gsPathFellApp is not defined in Site Parameters.');
            }

            $gsFilenameFellApp = $userUtil->getSiteSetting($this->em, 'gsFilenameFellApp');
            if (!$gsFilenameFellApp) {
                throw new \InvalidArgumentException('gsFilenameFellApp is not defined in Site Parameters.');
            }

            $gsArgumentsFellAppOrig = $userUtil->getSiteSetting($this->em,'gsArgumentsFellApp');
            if( !$gsArgumentsFellAppOrig ) {
                throw new \InvalidArgumentException('gsArgumentsFellApp is not defined in Site Parameters.');
            }
        } else {
            //$logger->notice('gs not Windows');
            $userUtil = new UserUtil();
            $gsPathFellApp = $userUtil->getSiteSetting($this->em, 'gsPathFellAppLinux');
            if (!$gsPathFellApp) {
                throw new \InvalidArgumentException('gsPathFellAppLinux is not defined in Site Parameters.');
            }

            $gsFilenameFellApp = $userUtil->getSiteSetting($this->em, 'gsFilenameFellAppLinux');
            if (!$gsFilenameFellApp) {
                throw new \InvalidArgumentException('gsFilenameFellAppLinux is not defined in Site Parameters.');
            }

            $gsArgumentsFellAppOrig = $userUtil->getSiteSetting($this->em,'gsArgumentsFellAppLinux');
            if( !$gsArgumentsFellAppOrig ) {
                throw new \InvalidArgumentException('gsArgumentsFellAppLinux is not defined in Site Parameters.');
            }
        }

        //$gsLocation = '"' . $gsPathFellApp . '\\' . $gsFilenameFellApp . '"';
        $gsLocation = '"' . $gsPathFellApp . DIRECTORY_SEPARATOR . $gsFilenameFellApp . '"';

        //quick fix for c.med running on E:
//        if( strpos(getcwd(),'E:') !== false ) {
//            $gsLocation = str_replace('C:','E:',$gsLocation);
//        }

        foreach( $filesArr as $file ) {

//            $gsArgumentsFellApp = $userUtil->getSiteSetting($this->em,'gsArgumentsFellApp');
//            if( !$gsArgumentsFellApp ) {
//                throw new \InvalidArgumentException('gsArgumentsFellApp is not defined in Site Parameters.');
//            }

            //$ "C:\Users\DevServer\Desktop\php\Ghostscript\bin\gswin64c.exe" -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile="C:\Temp New\out\out.pdf" -c .setpdfwrite -f "C:\Temp New\test.pdf"
            //"C:\Users\DevServer\Desktop\php\Ghostscript\bin\gswin64.exe"
            //$cmd = $gsLocation . ' -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite ';

            //echo "add merge: filepath=(".$file.") <br>";
            $filesStr = '"' . $file . '"';

            $logger->notice('GS: before inputFiles='.$filesStr);
            //$filesStr = str_replace("/","\\", $filesStr);
            //$filesStr = str_replace("app\..","", $filesStr);
            $filesStr = $this->strReplace($filesStr);

            //$outFilename = pathinfo($file, PATHINFO_DIRNAME) . '\\' . pathinfo($file, PATHINFO_FILENAME) . "_gs.pdf";
            $outFilename = pathinfo($file, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($file, PATHINFO_FILENAME) . "_gs.pdf";

            $outFilename = '"'.$outFilename.'"';

            //$outFilename = str_replace("/","\\", $outFilename);
            //$outFilename = str_replace("app\..","", $outFilename);
            $outFilename = $this->strReplace($outFilename);

            $logger->notice('GS: inputFiles='.$filesStr);
            $logger->notice('GS: outFilename='.$outFilename);

            //gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=unencrypted.pdf -c .setpdfwrite -f encrypted.pdf
            //$cmd = $cmd . '-sOutputFile=' . $outFilename . ' -c .setpdfwrite -f ' . $filesStr ;

            //replace ###parameter### by appropriate variable
            //-q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile= ###outputFile###  -c .setpdfwrite -f ###inputFiles###
            //$logger->notice('0 gsArgumentsFellApp='.$gsArgumentsFellApp);
            $gsArgumentsFellApp = $gsArgumentsFellAppOrig."";
            $gsArgumentsFellApp = str_replace('###inputFiles###',$filesStr,$gsArgumentsFellApp);
            $gsArgumentsFellApp = str_replace('###outputFile###',$outFilename,$gsArgumentsFellApp);
            //$logger->notice('gsArgumentsFellApp='.$gsArgumentsFellApp);

            $cmd = $gsLocation . ' ' . $gsArgumentsFellApp;

            //$logger->notice('GS cmd='.$cmd);

            $output = null;
            $return = null;
            exec( $cmd, $output, $return );

            //$logger->error("GS output: " . print_r($output));
            //$logger->error("GS return: " . $return);

            if( $return == 1 ) {
                //event log
                $event = "ERROR: 'Complete Application PDF' will no be generated! GS failed: " . $cmd."; GS output=".implode("; ",$output);
                $logger->error($event);
                $userSecUtil->sendEmailToSystemEmail("Complete Application PDF will no be generated - GS failed", $event);

                $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Fellowship Application Creation Failed');
            } else {
                //$logger->notice("GS converter OK: cmd=".$cmd."; output=".implode(";",$output));
            }

            //$logger->notice("GS final outFilename=".$outFilename);
            $filesOutArr[] = $outFilename;

        }

        return $filesOutArr;
    }

    public function strReplace($string) {
        //$string = str_replace("/","\\", $string);
        //$string = str_replace("app\..","", $string);
        $string = str_replace("\\",DIRECTORY_SEPARATOR, $string);
        $string = str_replace("/",DIRECTORY_SEPARATOR, $string);
        $string = str_replace("app".DIRECTORY_SEPARATOR."..","", $string);

        return $string;
    }


    protected static function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new \InvalidArgumentException("$dirPath must be a directory");
        }
        //if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        //    $dirPath .= '/';
        //}
        if (substr($dirPath, strlen($dirPath) - 1, 1) != DIRECTORY_SEPARATOR) {
            $dirPath .= DIRECTORY_SEPARATOR;
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
    protected function createFellAppReportDB($holderEntity,$holderMethodSingularStr,$author,$uniqueTitle,$path,$filesize,$documentType,$deleteOldFileFromServer) {

        $logger = $this->container->get('logger');

        $object = new Document($author);

        $object->setUniqueid($uniqueTitle);
        $object->setCleanOriginalname($uniqueTitle);
        $object->setTitle($uniqueTitle);
        $object->setUniquename($uniqueTitle);

        $object->setUploadDirectory($path);
        $object->setSize($filesize);

        $transformer = new GenericTreeTransformer($this->em, $author, "DocumentTypeList", "UserdirectoryBundle");
        $documentType = trim($documentType);
        $documentTypeObject = $transformer->reverseTransform($documentType);
        if( $documentTypeObject ) {
            $object->setType($documentTypeObject);
        }

        //constructs methods: "getReports", "removeReport", "addReport"
        $getMethod = "get".$holderMethodSingularStr."s";
        $removeMethod = "remove".$holderMethodSingularStr;
        $addMethod = "add".$holderMethodSingularStr;

        //do not remove documents Application PDF
        //move all reports to OldReports
        if( $holderMethodSingularStr == "report" ) {
            foreach ($holderEntity->getReports() as $report) {
                $holderEntity->removeReport($report);
                $holderEntity->addOldReport($report);
            }
        }

        //remove all reports for Application PDF without attached documents
        if( $holderMethodSingularStr == "formReport" ) {
            foreach ($holderEntity->$getMethod() as $report) {

                //delete file from server
                if ($deleteOldFileFromServer) {
                    $filePath = $report->getServerPath();
                    if (file_exists($filePath)) {
                        //$logger->notice("create FellApp ReportDB: unlink file path=" . $filePath);
                        unlink($filePath);
                    } else {
                        $logger->warning("create FellApp ReportDB: cannot unlink file path=" . $filePath);
                    }
                }

                //delete file from DB
                $holderEntity->$removeMethod($report);
                $this->em->remove($report);
            }
        }

        //add report
        $holderEntity->$addMethod($object);

        $this->em->persist($holderEntity);
        $this->em->persist($object);
        $this->em->flush();

        $logger->notice("Document created with ID=".$object->getId()." for fellapp ID=".$holderEntity->getId() . "; documentType=".$documentType);

        return $object;
    }

    //Cytopathology-Fellowship-Application-2017-ID47-Smith-John-generated-on-12-25-2015-at-02-13-pm.pdf
    //$filenameStr: i.e. "Fellowship-Application"
    protected function constructUniqueFileName($entity,$filenameStr) {

        $logger = $this->container->get('logger');

        $currentDate = new \DateTime();
        $subjectUser = $entity->getUser();

        if( $entity->getFellowshipSubspecialty() ) {
            $fellappType = $entity->getFellowshipSubspecialty()->getName();
        } else {
            $fellappType = "Unknown";
            $logger->warning("Unknown fellowship type for fellapp id=".$entity->getId());
        }

        $serverTimezone = date_default_timezone_get(); //server timezone
        $fellappType = str_replace(" ","-",$fellappType);

        $filename =
            $fellappType."-".$filenameStr.           //"-Fellowship-Application".
            "-".$entity->getStartDate()->format('Y').
            "-ID".$entity->getId().
            "-".$subjectUser->getLastNameUppercase().
            "-".$subjectUser->getFirstNameUppercase().
            "-generated-on-".$currentDate->format('m-d-Y').'-at-'.$currentDate->format('h-i-s-a').'_'.$serverTimezone.
            ".pdf";

        //replace all white spaces to _
        $filename = str_replace(" ","_",$filename);
        $filename = str_replace("/","_",$filename);
        $filename = str_replace("\\","_",$filename);
        $filename = str_replace(DIRECTORY_SEPARATOR,"_",$filename);

        return $filename;
    }


    //test method for console command
    public function testCmd() {

        $fellapp = $this->em->getRepository('OlegFellAppBundle:FellowshipApplication')->find(6);
        $avatar = $fellapp->getAvatars()->last();

        //$serverPath = $avatar->getFullServerPath();
        //echo "serverPath=".$serverPath." ";

        //$applicationOutputFilePath = getcwd() . "/web/" . $avatar->getUploadDirectory() . "/test/test.pdf";
        $applicationOutputFilePath = getcwd() . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $avatar->getUploadDirectory() . DIRECTORY_SEPARATOR. "test".DIRECTORY_SEPARATOR."test.pdf";
        echo "path=".$applicationOutputFilePath." ";

        $this->generateApplicationPdf($fellapp->getId(),$applicationOutputFilePath);

        exit();
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