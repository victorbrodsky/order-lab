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
 * Date: 8/20/15
 * Time: 2:33 PM
 */

namespace App\UserdirectoryBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class CertificateCronCommand extends Command {

    //protected static $defaultName = 'cron:certificate';
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
            //->setName('console.command')
            //->setCommand('cron:certificate')
            ->setDescription('Cron job to check SSL certificate expiration date')
            ->addArgument('domain', InputArgument::REQUIRED, 'Server domain, for example, view.online');
    }

    
    //php bin/console cron:certificate --env=prod view.online
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $userServiceUtil = $this->container->get('user_service_utility');

        $domain = $input->getArgument('domain');

        $daysRemaining = $userServiceUtil->checkSslCertificate($domain,$renew=false);
        $output->writeln($daysRemaining);

        if( $daysRemaining === NULL || $daysRemaining < 14 ) {
            //renew certificate
            $userServiceUtil->updateSslCertificate($domain,$daysRemaining);
        }
        
        //return true;
        return Command::SUCCESS;
    }

}

//cron: sudo crontab -e
//Every 2 minutes: */2 * * * *
//Every day at 3 AM: 0 3 * * *
// */2 * * * * /usr/bin/php /srv/order-lab-tenantapptest/orderflex/bin/console cron:certificate --env=prod




