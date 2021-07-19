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
            ->setDescription('Translational Research Project Expiration Reminder Email');
    }

    //php bin/console cron:expiration-reminder-emails --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->container->get('logger');

        $transresReminderUtil = $this->container->get('transres_reminder_util');

        $showSummary = false; //send email reminder
        //$showSummary = true; //testing: show unpaid invoices only without sending emails

        $logger->notice("Cron expiration-reminder-emails with showSummary=".$showSummary);

        $results = $transresReminderUtil->sendProjectExpirationReminder($showSummary);

        $logger->notice("Cron expiration-reminder-emails result=".$results);

        $output->writeln($results);

    }

}