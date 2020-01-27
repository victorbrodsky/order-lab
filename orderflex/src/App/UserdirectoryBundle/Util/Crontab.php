<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 1/27/2020
 * Time: 12:32 PM
 */

namespace App\UserdirectoryBundle\Util;


//Credits to https://www.kavoir.com/2011/10/php-crontab-class-to-add-and-remove-cron-jobs.html
class Crontab {

    // In this class, array instead of string would be the standard input / output format.

    // Legacy way to add a job:
    // $output = shell_exec('(crontab -l; echo "'.$job.'") | crontab -');

    static private function stringToArray_Modified($jobs = '') {
        //echo "1jobs=$jobs <br>";
        $jobs = trim($jobs);
        //echo "2jobs=$jobs <br>";
        $array = array();
        if (strpos($jobs, "\r\n") !== false) {
            $array = explode("\r\n", $jobs); // trim() gets rid of the last \r\n
        }
        if (strpos($jobs, "\n") !== false) {
            $array = explode("\n", $jobs); // trim() gets rid of the last \r\n
        }
        if (strpos($jobs, "\r") !== false) {
            $array = explode("\r", $jobs); // trim() gets rid of the last \r\n
        }
        //$array = explode("\r\n", trim($jobs)); // trim() gets rid of the last \r\n
        foreach ($array as $key => $item) {
            if ($item == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }
    static private function stringToArray($jobs = '') {
        $array = explode("\r\n", trim($jobs)); // trim() gets rid of the last \r\n
        foreach ($array as $key => $item) {
            if ($item == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }

    static private function arrayToString($jobs = array()) {
        $string = implode("\r\n", $jobs);
        return $string;
    }

    static public function getJobs() {
        $output = shell_exec('crontab -l');
        //dump($output);
        return self::stringToArray($output);
    }

    static public function getJobsAsSimpleArray() {
        exec('crontab -l', $output); //output is array
        //dump($output);
        return $output;
    }



    static public function saveJobs($jobs = array()) {
        $output = shell_exec('echo "'.self::arrayToString($jobs).'" | crontab -');
        return $output;
    }

    static public function doesJobExist($job = '') {
        $jobs = self::getJobs();
        //echo "1job=$job <br>";
        //echo "1jobs: <br>";
        //dump($jobs);
        if (in_array($job, $jobs)) {
            //echo "$job exists!!!<br>";
            return true;
        } else {
            //echo "$job does not exists???<br>";
            return false;
        }
    }

    static public function addJob($job = '') {
        if (self::doesJobExist($job)) {
            return false;
        } else {
            $jobs = self::getJobs();
            $jobs[] = $job;
            return self::saveJobs($jobs);
        }
    }

    static public function removeJob($job = '') {
        if (self::doesJobExist($job)) {

            $jobs = self::getJobs();

            //echo "removeJob=$job <br>";
            //echo "All jobs: <br>";
            //dump($jobs);

            //$key = array_search($job, $jobs);
            //echo "key=$key <br>";
            //unset($jobs[$key]);

            unset($jobs[array_search($job, $jobs)]);

            $jobs = self::getJobs();
            //echo "1job=$job <br>";
            //echo "1jobs: <br>";
            //dump($jobs);

            return self::saveJobs($jobs);
        } else {
            return false;
        }
    }

}
