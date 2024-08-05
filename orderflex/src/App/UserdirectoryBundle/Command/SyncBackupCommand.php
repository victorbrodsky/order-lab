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

use App\UserdirectoryBundle\Util\SyncBackupUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class SyncBackupCommand extends Command {

    private $container;
    private $em;
    private $syncBackupUtil;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em, SyncBackupUtil $syncBackupUtil)
    {
        parent::__construct();

        $this->container = $container;
        $this->em = $em;
        $this->syncBackupUtil = $syncBackupUtil;
    }

    protected function configure() : void 
    {
        $this
            ->setName('cron:sync-backup-command')
            ->setDescription('Download backup files from the public to the internal server');
    }


    //F- Implement a view.med.cornell.edu cron job that reaches out to
    // view.online and picks up/downloads the back up files every 12 hours
    // from the back up destination folder (both the database file and
    // the zipped up uploaded files) and puts them into a dedicated
    // network shared folder
    // (subfolder of where the view.med.cornell.edu backups are uploaded.)

    // /bin/php bin/console cron:sync-backup-command --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $resStr = "N/A";

        $logger = $this->container->get('logger');
        $logger->notice("cron:sync-backup-command. Start.");

        $this->syncBackupUtil->downloadBackupFilesFromPublic();

        $output->writeln($resStr);

        return Command::SUCCESS;
    }
    
    
    
    
    

} 