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
            ->setDescription('Try to generate fellowship application report')
//            ->addArgument(
//                'argument',
//                InputArgument::OPTIONAL,
//                'Fellowship Application Report request argument (asap,overwrite)'
//            )
            ;
    }

    //php app/console fellapp:generatereportrun
    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->getContainer()->get('logger');
        $logger->notice("Run Command: try run");
        
        //$result = ReportGeneratorManager::getInstance($this->getContainer())->tryRun();

        //$argument = $input->getArgument('argument');
        //echo "argument=".$argument."<br>";

        $fellappRepGen = $this->getContainer()->get('fellapp_reportgenerator');
        $result = $fellappRepGen->testCmd();
        exit('1');
        $result = $fellappRepGen->tryRun();
        
        $logger->notice("Run Command: result report filename=".$result);

        $output->writeln($result);
        //$output->writeln('run finished');

    }

} 