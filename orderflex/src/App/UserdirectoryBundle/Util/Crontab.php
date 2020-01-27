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

    static private function stringToArray($jobs = '') {
        $jobs = trim($jobs);
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

    static public function getJobsOrig() {
        $output = shell_exec('crontab -l');
        //dump($output);
        return self::stringToArray($output);
    }

    static public function getJobs() {
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
        echo "1job=$job <br>";
        echo "1jobs: <br>";
        dump($jobs);
        if (in_array($job, $jobs)) {
            return true;
        } else {
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
            unset($jobs[array_search($job, $jobs)]);
            return self::saveJobs($jobs);
        } else {
            return false;
        }
    }

}
