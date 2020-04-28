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
 * Time: 9:27 AM
 */

namespace App\ResAppBundle\Util;

//Singleton
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

//NOT USED
class ReportGeneratorManager {

    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

    private static $container;
    
    private static $logger;

    private static $running = false;

    private static $queue = array();   

    private $currentQueueElementId;

    private static $timestamp;
    
    private $processes = array();
    
    //private $consoledir = '"cd C:\Php\Wampp\wamp\www\scanorder\Scanorders2\"';


    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance( $container=null )
    {
        if (null === static::$instance) {
            static::$instance = new static();
            //static::$instance->setContainer($container);
            if( $container ) {
                static::$container = $container;
                static::$logger = static::$container->get('logger');
            }
            self::$logger->notice("test logger init!");
        } else {
            self::$logger->notice("test logger already initialized");
        }
               
        

        return static::$instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

//    private function setContainer($container) {
//        $this->container = $container;
//    }
//    
//    private function getContainer() {
//        return $this->container;
//    }






    public function addToQueue($id,$asap=false) {

        $newQueueElement = array('id'=>$id, 'timestamp'=> new \DateTime());

//        if( $asap ) {
//            array_unshift(self::$queue, $newQueueElement); //Prepend one or more elements to the beginning of an array
//        } else {
//            array_push(self::$queue, $newQueueElement); //Push one or more elements onto the end of array
//        }
        self::$queue[] = $newQueueElement;
        
        //Running Processes Asynchronously
        //$process = new Process('php ../bin/console resapp:generatereportrun');
        //$process = new Process('php ..'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'console resapp:generatereportrun');
        $process = Process::fromShellCommandline('php ..'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'console resapp:generatereportrun');
        $process->mustRun();             
        
        $this->processes[] = $process;
        self::$logger->notice("create process pid " . $process->getPid() );
        if( $process->isRunning() ) {
            echo "process is running with pid=".$process->getPid()."<br>";
        }
        echo "added to queue id=".$id." => queue count " . count(self::$queue) . "<br>"; 
        echo "processes count " . count($this->processes) . "<br>";    
        
        while( $process->isRunning() ) {
            echo ".";
            usleep(50000);
        }
        echo "<br>";
        
//        $process->wait(function ($type, $buffer) {
//            if (Process::ERR === $type) {
//                echo 'ERR > '.$buffer;
//            } else {
//                echo 'OUT > '.$buffer;
//            }
//        });
        
//        while (count($processes) > 0) {
//            foreach ($processes as $i => $process) {
//                if( !$process->isStarted() ) {
//                    echo "Process starts pid=".$process->getPid()."\n";
//                    $process->start();
//                    continue;
//                } 
//                
//                //echo "Process running pid=".$process->getPid()."\n";
//
//                echo $process->getIncrementalOutput();
//                echo $process->getIncrementalErrorOutput();
//
//                if (!$process->isRunning()) {
//                    echo "Process stopped pid=".$process->getPid()."\n";
//                    unset($processes[$i]);
//                }
//            }//foreach
//        }//while

        
        //try to run in command console by process component
        //$this->tryRun();
        
        return;
    }

    public function tryRun() {

        //echo "Echo: try Run queue count " . count($this->queue) . "<br>";
        self::$logger->notice("try Run queue count " . count(self::$queue) );

        if( !self::$running && count(self::$queue) > 0 ) {

            //make sure libreoffice is not running

            self::$running = true;
            self::$timestamp = new \DateTime();

            $currentQueueElement = array_pop(self::$queue); //Pop the element off the end of array

            //logger start event
            self::$logger->notice("Start running res report id=" . $currentQueueElement['id'] . "; remaining in queue " . count(self::$queue) );

            $time_start = microtime(true);

            $resappRepGen = $this->container->get('resapp_reportgenerator');
            $res = $resappRepGen->generateResAppReport( $currentQueueElement['id'] );
            //////////////// run by process component ////////////////
//            try {
//                $process = new Process( 'php ../bin/console resapp:generatereport ' . $currentQueueElement['id'] );
//                $process->setTimeout(1200); //secs => 20 min
//                $process->run();
//                $this->currentQueueElementId = $process->getPid();
//
//                //executes after the command finishes
//                if (!$process->isSuccessful()) {
//                    //throw new \RuntimeException($process->getErrorOutput());
//                    //logger finish event
//                    self::$logger->warning('Process: "php app/console resapp:generatereport resappid=' . $currentQueueElement['id'] . '"' . ' isSuccessful: ' . $process->getErrorOutput() );
//                }
//
//                //get output
//                $res = $process->getOutput();
//
//            } catch( ProcessFailedException $e ) {
//                echo $e->getMessage();
//                self::$logger->warning('Process: "php app/console resapp:generatereport resappid=' . $currentQueueElement['id'] . '"' . ' failed: ' . $e->getMessage() );
//            }
            //////////////// EOF run by process component ////////////////

            $time_end = microtime(true);
            $execution_time = ($time_end - $time_start);

            //logger finish event
            //self::$logger->notice("Finished running res report resappid=" . $currentQueueElement['id'] . "; executed in " . $execution_time . " sec" . "; report path=" . $res['report'] );
            self::$logger->notice("Finished running res report resappid=" . $currentQueueElement['id'] . "; executed in " . $execution_time . " sec" . "; res=" . $res );

            
            //reset all queue related parameters
            self::$running = false;
            self::$timestamp = null;
            $this->currentQueueElementId = null;

            //run next in queue
            //$this->tryRun();

        }

        return;
    }

    private function runGenerateReport($id) {

    }


} 