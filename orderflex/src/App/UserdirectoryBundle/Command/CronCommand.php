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


//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;


class CronCommand extends Command {

    protected static $defaultName = 'cron:delete-orphan';
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
            //->setName('cron:delete-orphan')
            ->setDescription('Cron job to delete orphan files older than 2 years of age');
    }

    //Make sure to set all current documents useObject (before implemented document useObject are NULL):
    //entityNamespace=notempty,entityName=notempty,entityId=notempty
    //UPDATE [ScanOrder].[dbo].[user_document]
    //SET entityNamespace='notempty',entityName='notempty',entityId='notempty'
    //WHERE entityNamespace IS NULL
    //
    //Cron job to delete orphan files (uploaded manually but not attached to the application by clicking "Update" that are older than 2 years of age).
    // 2 years => 365*2 = 730 days
    //php app/console cron:delete-orphan --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->container->get('logger');

        $userSecUtil = $this->container->get('user_security_utility');

        // 2 years => 365*2 = 730 days
        $days = 730;
        //$days = 1;

        $deletedDocumentIds = $userSecUtil->deleteOrphanFiles( $days, 'Fellowship Application Spreadsheet', 'except' );

        if( $deletedDocumentIds ) {
            $eventImport = 'Old Documents Deleted: '.$deletedDocumentIds;
            //$logger->notice($eventImport);
        } else {
            $eventImport = 'None Old Documents Deleted';
            //$logger->notice($eventImport);
        }
        $result = "Delete Old Documents: ".$eventImport;

        $logger->notice($result);

        $output->writeln($result);
    }

} 