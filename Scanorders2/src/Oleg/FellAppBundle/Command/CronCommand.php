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
        $fellappUtil = $this->getContainer()->get('fellapp_util');
        $result = "";

        //import
        $fileDb = $fellappUtil->importFellApp();
        if( $fileDb ) {
            $eventImport = 'FellApp Imported: '.$fileDb;
            $logger->notice($eventImport);
        } else {
            $eventImport = 'FellApp Imported Failed';
            $logger->error($eventImport);
        }
        $result = "Import: ".$eventImport;


        //populate
        $path = getcwd() . "/web";
        $populatedCount = $fellappUtil->populateFellApp($path);
        if( $populatedCount >= 0 ) {
            $eventPopulate = "Populated ".$populatedCount." Fellowship Applicantions.";
            $logger->notice($eventPopulate);
        } else {
            $eventPopulate = "Google API service failed!";
            $logger->error($eventPopulate);
        }
        $result = $result . "; Populate: " . $eventPopulate;


        $output->writeln($result);
    }

} 