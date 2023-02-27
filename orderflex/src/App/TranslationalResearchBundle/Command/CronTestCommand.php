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
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class CronTestCommand extends Command {

    //protected static $defaultName = 'cron:test-attachment-emails';
    private $container;
    private $em;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em)
    {
        parent::__construct();

        $this->container = $container;
        $this->em = $em;
    }

    protected function configure() : void 
    {
        $this
            ->setDescription('Translational Research Unpaid Invoice Reminder Email Test');
    }

    //php bin/console cron:test-attachment-emails --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {

        $logger = $this->container->get('logger');
        //$fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');
        //$result = $fellappImportPopulateUtil->processFellAppFromGoogleDrive();
        //$logger->notice("Cron job processing FellApp from Google Drive finished with result=".$result);
        //$output->writeln($result);

        $transresReminderUtil = $this->container->get('transres_reminder_util');

        $showSummary = false; //send email reminder
        //$showSummary = true; //testing: show unpaid invoices only without sending emails

        $logger->notice("Cron to test invoice reminder emails with attachment with showSummary=".$showSummary);

//        ////////////// expiration projects //////////////
//        //testing
//        $testing = true;
//        $results = "";
//        $projectExpirationResults = $transresReminderUtil->sendProjectExpirationReminder($testing);
//        $results = $results . "; " . $projectExpirationResults;
//        exit($results);
//        ////////////// EOF expiration projects //////////////


        ////////////// unpaid invoices //////////////
        //$testing = false;
        $testing = true;
        $results = $transresReminderUtil->sendReminderUnpaidInvoices($showSummary,$testing);
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

        $output->writeln($results); //testing
        return Command::SUCCESS; //testing
        ////////////// EOF unpaid invoices //////////////

    }

}