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

class ProjectSyncCronCommand extends Command {

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
            ->setDescription('Translational Research Project Sync with public server');
    }

    //php bin/console cron:project-sync --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {

        $logger = $this->container->get('logger');

        $interfaceTransferUtil = $this->container->get('interface_transfer_utility');
        $transferDatas = $interfaceTransferUtil->getSlaveToMasterTransfer();

        $logger->notice("Cron to sync project with public server: result=".$transferDatas);

        $output->writeln($transferDatas); //testing
        return Command::SUCCESS; //testing
    }

}