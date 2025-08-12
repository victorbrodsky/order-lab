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
 * Date: 8/06/2025
 * Time: 10:25 AM
 */

namespace App\UserdirectoryBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class CertificateUpdateCronCommand extends Command {

    //protected static $defaultName = 'cron:certificate-update';
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
            //->setCommand('cron:certificate-update')
            ->setDescription('Cron job to update SSL certificate if expiration date is close')
            ->addArgument('domain', InputArgument::REQUIRED, 'Server domain, for example, view.online');
    }

    //Can be executed only as root
    //php bin/console cron:certificate-update --env=prod view.online
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $userServiceUtil = $this->container->get('user_service_utility');

        $domain = $input->getArgument('domain');

        $daysRemaining = NULL;
        $organization = NULL;
        $resArr = $userServiceUtil->checkSslCertificate($domain,$sendEmail=false);
        if( $resArr ) {
            $daysRemaining = $resArr['DaysRemaining'];
            $organization = $resArr['Organization'];
        }

        //$output->writeln($daysRemaining);

        $minDaysRemaining = 14; //default
        //$minDaysRemaining = 160; //testing
        $info = 'N/A';
        if( $organization && $organization == "Let's Encrypt" ) {
            if( $daysRemaining === NULL || $daysRemaining < $minDaysRemaining ) {
                //renew certificate
                $output->writeln("renew certificate");
                $info = $userServiceUtil->updateSslCertificate($domain, $daysRemaining, $organization);
            }
        }

        $output->writeln("daysRemaining=".$daysRemaining.", organization=".$organization.", info=".$info);

        //return true;
        return Command::SUCCESS;
    }

}

//cron: sudo crontab -e
//Every 2 minutes: */2 * * * *
//Every day at 3 AM: 0 3 * * *
// */2 * * * * /usr/bin/php /srv/order-lab-tenantapptest/orderflex/bin/console cron:certificate --env=prod




