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

namespace App\ResAppBundle\Command;


use App\ResAppBundle\Util\ReportGeneratorManager;
//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;


class GenerateReportRunCommand extends Command {

    protected static $defaultName = 'resapp:generatereportrun';
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
            //->setName('resapp:generatereportrun')
            ->setDescription('Try to generate resident application report')
//            ->addArgument(
//                'argument',
//                InputArgument::OPTIONAL,
//                'Residency Application Report request argument (asap,overwrite)'
//            )
            ;
    }

    //php bin/console resapp:generatereportrun
    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->container->get('logger');
        $logger->notice("Run Command: try run");
        //echo "testing Run Command: try run<br>"; //testing
        
        //$result = ReportGeneratorManager::getInstance($this->container)->tryRun();

        //$argument = $input->getArgument('argument');
        //echo "argument=".$argument."<br>";

        $resappRepGen = $this->container->get('resapp_reportgenerator');

        ///////// testing /////////
        //$result = $resappRepGen->testCmd();
        //exit('end of testCmd: result='.$result);
        ///////// EOF testing /////////

        $result = $resappRepGen->tryRun();
        
        $logger->notice("Run Command: result report filename=".$result);

        $output->writeln($result);
        //$output->writeln('run finished');

    }

} 