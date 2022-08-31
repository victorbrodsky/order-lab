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
 * Created by Oleg Ivanov.
 * User: oli2002
 */

namespace App\UserdirectoryBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class ExternalUrlMonitorCronCommand extends Command {

    //run command to check external url: test server view-med checks if live server view is running (view's url is responding)
    //protected static $defaultName = 'cron:externalurlmonitor';
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
            //->setName('cron:externalurlmonitor')
            //->setCommand('cron:externalurlmonitor')
            ->setDescription('Cron job to check externalurlmonitor');
    }

    
    //php bin/console cron:externalurlmonitor --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $userServiceUtil = $this->container->get('user_service_utility');
        $res = $userServiceUtil->checkExternalUrlMonitor();
        $output->writeln($res);
        
        //return true;
        return Command::SUCCESS;
    }

} 