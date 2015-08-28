<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 8/28/15
 * Time: 9:27 AM
 */

namespace Oleg\FellAppBundle\Util;

//Singleton
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ReportGeneratorManager {

    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

    private $container;

    private $running = false;

    private $queue = array();

    private $currentQueueElementId;

    private $timestamp;


    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance( $container )
    {
        if (null === static::$instance) {
            static::$instance = new static();
            static::$instance->setContainer($container);
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

    private function setContainer($container) {
        $this->container = $container;
    }






    public function addToQueue($id,$asap=false) {

        $newQueueElement = array('id'=>$id, 'timestamp'=> new \DateTime());

        if( $asap ) {
            array_unshift($this->queue, $newQueueElement); //Prepend one or more elements to the beginning of an array
        } else {
            array_push($this->queue, $newQueueElement); //Push one or more elements onto the end of array
        }

        //try to run in command console by process component
        //$this->tryRun();
        $process = new Process('php app/console fellapp:generatereportrun');
        $process->start();

        return;
    }

    public function tryRun() {

        $logger = $this->container->get('logger');
        $logger->notice("try Run queue count " . count($this->queue) );

        if( !$this->running && count($this->queue) > 0 ) {

            //make sure libreoffice is not running

            $this->running = true;
            $this->timestamp = new \DateTime();

            $currentQueueElement = array_pop($this->queue); //Pop the element off the end of array

            //logger start event
            $logger->notice("Start running fell report id=" . $currentQueueElement['id'] . "; remaining in queue " . count($this->queue) );

            $time_start = microtime(true);

            //$fellappRepGen = $this->container->get('fellapp_reportgenerator');
            //$res = $fellappRepGen->generateFellAppReport( $currentQueueElement['id'] );
            //////////////// run by process component ////////////////
            try {
                $process = new Process( 'php app/console fellapp:generatereport fellappid ' . $currentQueueElement['id'] );
                $process->setTimeout(1200); //secs => 20 min
                $process->run();
                $this->currentQueueElementId = $process->getPid();

                //executes after the command finishes
                if (!$process->isSuccessful()) {
                    //throw new \RuntimeException($process->getErrorOutput());
                    //logger finish event
                    $logger->warning('Process: "php app/console fellapp:generatereport fellappid ' . $currentQueueElement['id'] . '"' . ' failed: ' . $process->getErrorOutput() );
                }

                //get output
                $res = $process->getOutput();

            } catch( ProcessFailedException $e ) {
                echo $e->getMessage();
                $logger->warning('Process: "php app/console fellapp:generatereport fellappid ' . $currentQueueElement['id'] . '"' . ' failed: ' . $e->getMessage() );
            }
            //////////////// EOF run by process component ////////////////

            $time_end = microtime(true);
            $execution_time = ($time_end - $time_start);

            //logger finish event
            $logger->notice("Finished running fell report id=" . $currentQueueElement['id'] . "; executed in " . $execution_time . " sec" . "; report path=" . $res['report'] );

            //reset all queue related parameters
            $this->running = false;
            $this->timestamp = null;
            $this->currentQueueElementId = null;

            //run next in queue
            $this->tryRun();

        }

        return;
    }

    private function runGenerateReport($id) {

    }


} 