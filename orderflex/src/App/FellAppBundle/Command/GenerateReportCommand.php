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

namespace App\FellAppBundle\Command;


//use App\FellAppBundle\Util\ReportGeneratorManager;
//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class GenerateReportCommand extends Command {

    //protected static $defaultName = 'fellapp:generatereport';
    private $container;
    private $em;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em)
    {
        parent::__construct();

        $this->container = $container;
        $this->em = $em;
    }

    protected function configure() : void 
    {
        $this
            //->setName('fellapp:generatereport')
            ->setDescription('Import and Populate Fellowship Applications from Google Form')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Fellowship Application id'
            )
            ;
    }

    //php app/console fellapp:generatereport fellappid
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {

        $id = $input->getArgument('id');

        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
        
         if(1) {
            $res = $fellappRepGen->generateFellAppReport( $id );
         } else {
            //testing
            $reportsUploadPathFellApp = "Reports";
            //$userUtil = new UserUtil();
            //$reportsUploadPathFellApp = $userUtil->getSiteSetting($this->em,'reportsUploadPathFellApp');
            $uploadReportPath = 'Uploaded/' . $this->container->getParameter('fellapp.uploadpath').'/'.$reportsUploadPathFellApp;
            //$reportPath = $this->container->get('kernel')->getRootDir() . '/../public/' . $uploadReportPath.'/';
             $reportPath = $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $uploadReportPath . DIRECTORY_SEPARATOR;

             $outdir = $reportPath.'temp_'.$id.DIRECTORY_SEPARATOR;
            $applicationFilePath = $outdir . "application_ID" . $id . ".pdf";
            $res = $fellappRepGen->generateApplicationPdf($id,$applicationFilePath);
        }
        
        $output->writeln($res);

        return Command::SUCCESS;
    }


} 