<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 8/20/15
 * Time: 2:33 PM
 */

namespace Oleg\UserdirectoryBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CronCommand extends ContainerAwareCommand {


    protected function configure() {
        $this
            ->setName('cron:delete-orphan')
            ->setDescription('Cron job to delete orphan files older than 2 years of age');
    }

    //TODO: make sure to set all current documents useObject (before implemented document useObject are NULL):
    //TODO: entityNamespace=notempty,entityName=notempty,entityId=notempty
    //UPDATE [ScanOrder].[dbo].[user_document]
    //SET entityNamespace='notempty',entityName='notempty',entityId='notempty'
    //WHERE entityNamespace IS NULL
    //Cron job to delete orphan files (uploaded manually but not attached to the application by clicking "Update" that are older than 2 years of age).
    // 2 years => 365*2 = 730 days
    //php app/console cron:delete-orphan --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->getContainer()->get('logger');

        $userSecUtil = $this->getContainer()->get('user_security_utility');

        // 2 years => 365*2 = 730 days
        $days = 730;
        //$days = 1;

        $documentTypeFlag = 'except';

        $deletedDocumentIds = $userSecUtil->deleteOrphanFiles( $days, 'Fellowship Application Spreadsheet', $documentTypeFlag );

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