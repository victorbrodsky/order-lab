<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 8/20/15
 * Time: 2:33 PM
 */

namespace Oleg\FellAppBundle\Command;


use Oleg\FellAppBundle\Util\ReportGeneratorManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateReportRunCommand extends ContainerAwareCommand {


    protected function configure() {
        $this
            ->setName('fellapp:generatereportrun')
            ->setDescription('Import and Populate Fellowship Applications from Google Form')
//            ->addArgument(
//                'id',
//                InputArgument::REQUIRED,
//                'Fellowship Application id'
//            )
            ;
    }

    //php app/console fellapp:generatereportrun
    protected function execute(InputInterface $input, OutputInterface $output) {

        $result = ReportGeneratorManager::tryRun();

        $output->writeln($result);

    }

} 