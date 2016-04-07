<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 8/20/15
 * Time: 2:33 PM
 */

namespace Oleg\FellAppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CronDeleteOldSheetCommand extends ContainerAwareCommand {


    protected function configure() {
        $this
            ->setName('cron:deleteoldsheetfellapp')
            ->setDescription('Delete Old Fellowship Application Spreadsheets from the server');
    }

    //php app/console cron:deleteoldsheetfellapp --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->getContainer()->get('logger');

        //delete
        $fellappImportPopulateUtil = $this->getContainer()->get('fellapp_importpopulate_util');
        $deletedDocumentIds = $fellappImportPopulateUtil->deleteOldSheetFellApp();
        if( $deletedDocumentIds ) {
            $eventImport = 'FellApp Spreadsheet Deleted: '.$deletedDocumentIds;
            //$logger->notice($eventImport);
        } else {
            $eventImport = 'None FellApp Spreadsheet Deleted';
            //$logger->notice($eventImport);
        }
        $result = "Delete Old Sheet: ".$eventImport;

        $logger->notice($result);

        $output->writeln($result);
    }

} 