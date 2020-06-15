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

//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class CronCommand extends Command {

    protected static $defaultName = 'cron:invoice-reminder-emails';
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
            //->setName('cron:invoice-reminder-emails')
            ->setDescription('Translational Research Unpaid Invoice Reminder Email');
    }

    //php app/console cron:invoice-reminder-emails --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) {

        //$logger = $this->container->get('logger');
        //$fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');
        //$result = $fellappImportPopulateUtil->processFellAppFromGoogleDrive();
        //$logger->notice("Cron job processing FellApp from Google Drive finished with result=".$result);
        //$output->writeln($result);

        $transresReminderUtil = $this->container->get('transres_reminder_util');

        ////////////// unpaid invoices //////////////
        $results = $transresReminderUtil->sendReminderUnpaidInvoices();
        $output->writeln($results); //testing
        return true; //testing
        ////////////// EOF unpaid invoices //////////////

        ////////////// delayed projects //////////////
        $states = array("irb_review", "admin_review", "committee_review", "final_review", "irb_missinginfo", "admin_missinginfo");
        $finalResults = array();

        foreach($states as $state) {
            $projectResults = $transresReminderUtil->sendReminderReviewProjects($state);
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
            $requestResults = $transresReminderUtil->sendReminderPendingRequests($state);
            $finalResults[$state] = $requestResults;
        }

        $requestResultsArr = array();
        foreach($finalResults as $state=>$requestResults) {
            $requestResultsArr[] = $state.": ".$requestResults;
        }
        $results = $results . "; " . implode(", ",$requestResultsArr);
        ////////////// EOF delayed requests //////////////

        $output->writeln($results);

    }

}