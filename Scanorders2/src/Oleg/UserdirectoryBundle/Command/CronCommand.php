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

    //php C:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\app/console cron:importfellapp
    protected function configure_test()
    {
        $this
            ->setName('cron:importfellapp')
            ->setDescription('Import and Populate Fellowship Applications from Google Form')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Who do you want to greet?'
        )
        ->addOption(
            'yell',
            null,
            InputOption::VALUE_NONE,
            'If set, the task will yell in uppercase letters'
        );
    }
    protected function execute_test(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        if ($name) {
            $text = 'Hello '.$name;
        } else {
            $text = 'Hello';
        }

        if ($input->getOption('yell')) {
            $text = strtoupper($text);
        }

        $logger = $this->getContainer()->get('logger');
        $logger->notice('Notice: Greeted: '.$text);
        $logger->error('Error: Greeted: '.$text);

        $output->writeln($text);
    }

    protected function configure() {
        $this
            ->setName('cron:importfellapp')
            ->setDescription('Import and Populate Fellowship Applications from Google Form');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->getContainer()->get('logger');
        $fellappUtil = $this->getContainer()->get('fellapp_import');
        $result = "";

    if(0) {
        $fileDb = $fellappUtil->importFellApp();
        if( $fileDb ) {
            $eventImport = 'FellApp Imported: '.$fileDb;
            $logger->warning($eventImport);
        } else {
            $eventImport = 'FellApp Imported Failed';
            $logger->error($eventImport);
        }
        $result = "Import: ".$eventImport;
    }

    if(1) {
        $path = getcwd() . "/web";
        $populatedCount = $fellappUtil->populateFellApp($path);
        if( $populatedCount >= 0 ) {
            $eventPopulate = "Populated ".$populatedCount." Fellowship Applicantions.";
            $logger->warning($eventPopulate);
        } else {
            $eventPopulate = "Google API service failed!";
            $logger->error($eventPopulate);
        }
        $result = $result . "; Populate: " . $eventPopulate;
    }

        $output->writeln($result);
    }

} 