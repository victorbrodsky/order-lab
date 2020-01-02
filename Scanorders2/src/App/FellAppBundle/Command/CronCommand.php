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
        //testing checkAndSendCompleteEmail
//        $em = $this->getContainer()->get('doctrine')->getManager();
//        $fellappRecLetterUtil = $this->getContainer()->get('fellapp_rec_letter_util');
//        $fellapp = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find(1414); //8-test,1414-collage
//        $reference = $fellapp->getReferences()->first();
//        $fellappRecLetterUtil->checkReferenceAlreadyHasLetter($fellapp,$reference);
//        exit('eof test');
        //EOF testing

        $fellappImportPopulateUtil = $this->getContainer()->get('fellapp_importpopulate_util');
        $result = $fellappImportPopulateUtil->processFellAppFromGoogleDrive();
        $logger->notice("Cron job processing FellApp from Google Drive finished with result=".$result);

        if(1) {
            $fellappRecLetterUtil = $this->getContainer()->get('fellapp_rec_letter_util');
            $result2 = $fellappRecLetterUtil->processFellRecLetterFromGoogleDrive();
            $logger->notice("Cron job processing FellApp Recommendation Letters from Google Drive finished with result=" . $result2);
        }

        $result = $result . "; " . $result2;

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