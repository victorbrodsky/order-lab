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


class CronCommand extends ContainerAwareCommand {


    protected function configure() {
        $this
            ->setName('cron:importfellapp')
            ->setDescription('Import and Populate Fellowship Applications from Google Form');
    }

    //php app/console cron:importfellapp --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->getContainer()->get('logger');

        //testing
//        $fellappUtil = $this->getContainer()->get('fellapp_util');
//        $em = $this->getContainer()->get('doctrine')->getEntityManager();
//        $fellowshipApplication = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find(162); //162
//        $fellappUtil->sendConfirmationEmailsOnApplicationPopulation($fellowshipApplication,$fellowshipApplication->getUser());
//        exit('email test');
        //EOF testing

        $fellappImportPopulateUtil = $this->getContainer()->get('fellapp_importpopulate_util');

        $result = $fellappImportPopulateUtil->processFellAppFromGoogleDrive();

        $logger->notice("Cron job processing FellApp from Google Drive finished with result=".$result);

        $output->writeln($result);

//
//        $result = "";
//        //import
//        $fileDb = $fellappUtil->importFellApp();
//        if( $fileDb ) {
//            $eventImport = 'FellApp Imported: '.$fileDb;
//            $logger->notice($eventImport);
//        } else {
//            $eventImport = 'FellApp Imported Failed';
//            $logger->error($eventImport);
//        }
//        $result = "Import: ".$eventImport;
//
//
//        //populate
//        $path = getcwd() . "/web";
//        $populatedCount = $fellappUtil->populateFellApp($path);
//        if( $populatedCount >= 0 ) {
//            $eventPopulate = "Populated ".$populatedCount." Fellowship Applicantions.";
//            $logger->notice($eventPopulate);
//        } else {
//            $eventPopulate = "Google API service failed!";
//            $logger->error($eventPopulate);
//        }
//        $result = $result . "; Populate: " . $eventPopulate;
//
//        //delete old
//        $googlesheetmanagement = $this->getContainer()->get('fellapp_googlesheetmanagement');
//        $deletedDocumentIds = $googlesheetmanagement->deleteOldSheetFellApp();
//        if( $deletedDocumentIds ) {
//            $eventDelete = 'FellApp Spreadsheet Deleted: '.$deletedDocumentIds;
//            $logger->notice($eventDelete);
//        } else {
//            $eventDelete = 'None FellApp Spreadsheet Deleted';
//            $logger->notice($eventDelete);
//        }
//        $result = $result . "; Delete Old Sheet: ".$eventDelete;
//
//        $output->writeln($result);
    }




//    //php app/console cron:importfellapp --env=prod
//    protected function execute_old(InputInterface $input, OutputInterface $output) {
//
//        $logger = $this->getContainer()->get('logger');
//        $fellappUtil = $this->getContainer()->get('fellapp_util');
//        $result = "";
//
//        //import
//        $fileDb = $fellappUtil->importFellApp();
//        if( $fileDb ) {
//            $eventImport = 'FellApp Imported: '.$fileDb;
//            $logger->notice($eventImport);
//        } else {
//            $eventImport = 'FellApp Imported Failed';
//            $logger->error($eventImport);
//        }
//        $result = "Import: ".$eventImport;
//
//
//        //populate
//        $path = getcwd() . "/web";
//        $populatedCount = $fellappUtil->populateFellApp($path);
//        if( $populatedCount >= 0 ) {
//            $eventPopulate = "Populated ".$populatedCount." Fellowship Applicantions.";
//            $logger->notice($eventPopulate);
//        } else {
//            $eventPopulate = "Google API service failed!";
//            $logger->error($eventPopulate);
//        }
//        $result = $result . "; Populate: " . $eventPopulate;
//
//        //delete
//        $googlesheetmanagement = $this->getContainer()->get('fellapp_googlesheetmanagement');
//        $deletedDocumentIds = $googlesheetmanagement->deleteOldSheetFellApp();
//        if( $deletedDocumentIds ) {
//            $evenstDelete = 'FellApp Spreadsheet Deleted: '.$deletedDocumentIds;
//            $logger->notice($eventDelete);
//        } else {
//            $eventDelete = 'None FellApp Spreadsheet Deleted';
//            $logger->notice($eventDelete);
//        }
//        $result = $result . "; Delete Old Sheet: ".$eventDelete;
//
//        $output->writeln($result);
//    }

} 