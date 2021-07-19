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

namespace App\TranslationalResearchBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class ExpirationCronCommand extends Command {

    protected static $defaultName = 'cron:expiration-reminder-emails';
    private $container;
    private $em;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em)
    {
        parent::__construct();

        $this->container = $container;
        $this->em = $em;
    }

    protected function configure() {
        $this
            //->setName('cron:expiration-reminder-emails')
            ->setDescription('Translational Research Unpaid Invoice Reminder Email');
    }

    //php bin/console cron:expiration-reminder-emails --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->container->get('logger');
        //$fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');
        //$result = $fellappImportPopulateUtil->processFellAppFromGoogleDrive();
        //$logger->notice("Cron job processing FellApp from Google Drive finished with result=".$result);
        //$output->writeln($result);

        $transresReminderUtil = $this->container->get('transres_reminder_util');

        $showSummary = false; //send email reminder
        //$showSummary = true; //testing: show unpaid invoices only without sending emails

        $logger->notice("Cron expiration-reminder-emails with showSummary=".$showSummary);

        ////////////// expiration projects //////////////
        $results = $transresReminderUtil->sendProjectExpirationReminder($showSummary);
        if( is_array($results) ) {
            //$results = "Unpaid invoices=".count($results);
            //echo "#########array#########";
            //$results = implode(", ",$results);
            $invoiceCounter = 0;
            foreach($results as $result) {
                if( is_array($result) ) {
                    $invoiceCounter = $invoiceCounter + count($result);
                }
            }
            $results = "Unpaid invoices=".$invoiceCounter;
        }
        //$output->writeln($results); //testing
        //return true; //testing
        ////////////// EOF expiration projects //////////////

        ////////////// delayed projects //////////////
        $states = array("irb_review", "admin_review", "committee_review", "final_review", "irb_missinginfo", "admin_missinginfo");
        $finalResults = array();

        foreach($states as $state) {
            $projectResults = $transresReminderUtil->sendReminderReviewProjects($state,$showSummary);
            if( is_array($projectResults) ) {
                $projectResults = count($projectResults);
            }
            $finalResults[$state] = $projectResults;
        }

        $projectResultsArr = array();
        foreach($finalResults as $state=>$projectResults) {
            $projectResultsArr[] = $state.": ".$projectResults;
        }
        $results = $results . "; " . implode(", ",$projectResultsArr);
        ////////////// EOF delayed projects //////////////

        ////////////// delayed requests //////////////
        $states = array(
            'active',
            'pendingInvestigatorInput',
            'pendingHistology',
            'pendingImmunohistochemistry',
            'pendingMolecular',
            'pendingCaseRetrieval',
            'pendingTissueMicroArray',
            'pendingSlideScanning',
            'completed',
            'completedNotified'
        );
        $finalResults = array();

        foreach($states as $state) {
            $requestResults = $transresReminderUtil->sendReminderPendingRequests($state,$showSummary);
            if( is_array($requestResults) ) {
                $requestResults = count($requestResults);
            }
            $finalResults[$state] = $requestResults;
        }

        $requestResultsArr = array();
        foreach($finalResults as $state=>$requestResults) {
            $requestResultsArr[] = $state.": ".$requestResults;
        }
        $results = $results . "; " . implode(", ",$requestResultsArr);
        ////////////// EOF delayed requests //////////////

        $logger->notice("Cron expiration-reminder-emails result=".$results);

        $output->writeln($results);

    }

}