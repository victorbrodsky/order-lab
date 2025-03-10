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
 * User: ch3
 * Date: 9/1/2017
 * Time: 3:04 PM
 */

namespace App\UserdirectoryBundle\Command;


//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class TestCommand extends Command
{
    //protected static $defaultName = 'app:simple-tests';
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
            //->setName('app:simple-tests')
            ->setDescription('Run simple tests');
    }

    //php bin/console app:simple-tests --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {

        $logger = $this->container->get('logger');

        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
        $transresPdfUtil = $this->container->get('transres_pdf_generator');
        $fellappApplicationId = 1507; //1;

        //////// TESTING ////////
        //fellapp_download
        $router = $this->container->get('router');
        $pageUrl = $router->generate(
            'fellapp_download',
            array(
                'id' => $fellappApplicationId
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        //// replace tenant base in $pageUrl //////
        $userTenantUtil = $this->container->get('user_tenant_utility');
        $tenantUrlBase = $userTenantUtil->getTenantUrlBase();
        $pageUrl = str_replace("http://localhost/","http://localhost/".$tenantUrlBase."/",$pageUrl);
        //// EOF replace tenant base in $pageUrl //////
        $logger->notice("execute: Simple test: pageurl=[".$pageUrl."]");
        echo "execute: Simple test: pageurl=". $pageUrl . "<br>";

        //$userUtil = $this->container->get('user_utility');
        //$host = $userUtil->getRealSchemeAndHttpHost();
        //echo "execute: Simple test: host=[". $host . "]<br>";

        exit();
        /////// EOF TESTING /////////


        //$reportsUploadPathFellApp = "Reports";
        //$uploadpath = $this->container->getParameter('fellapp.uploadpath');
        //$uploadReportPath = 'Uploaded' . DIRECTORY_SEPARATOR . $uploadpath . DIRECTORY_SEPARATOR .$reportsUploadPathFellApp;
        $uploadReportPath = "Uploaded/fellapp/Reports";

        ///usr/local/bin/order-lab/Scanorders2/web/Uploaded/fellapp/Reports
        //$reportPath = $this->container->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $uploadReportPath. DIRECTORY_SEPARATOR;
        $reportPath = $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $uploadReportPath. DIRECTORY_SEPARATOR;

        $outdir = $reportPath . 'temp_' . $fellappApplicationId . DIRECTORY_SEPARATOR;

        $now = date("H:s:i");
        $now = str_replace(":","_",$now);

        $resultArr = array();

        //wkhtmltopdf
        $applicationFilePath = $outdir . "application_ID" . $fellappApplicationId . "_wkhtmltopdf_" . $now . ".pdf";
        $logger->notice("applicationFilePath=[".$applicationFilePath."]");
        $result = $fellappRepGen->generateApplicationPdf($fellappApplicationId,$applicationFilePath);
        $resultArr[] = $result;

        //phantomjs
        $applicationFilePath = $outdir . "application_ID" . $fellappApplicationId . "_phantomjs_" . $now . ".pdf";
        $logger->notice("applicationFilePath=[".$applicationFilePath."]");
        //$result = $fellappRepGen->generateApplicationPdf($fellappApplicationId,$applicationFilePath);
        $pdfPath = "fellapp_download";
        $pdfPathParametersArr = array('id' => $fellappApplicationId);
        $result = $transresPdfUtil->generatePdfPhantomjs($pdfPath,$pdfPathParametersArr,$applicationFilePath,null);
        $resultArr[] = $result;

        $resultTotal = implode("\r\n",$resultArr);
        $output->writeln($resultTotal);

        return Command::SUCCESS;
    }





    protected function configure_user()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:create-user')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new user.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a user...')
        ;
    }


    protected function execute_user(InputInterface $input, OutputInterface $output)
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);

        // outputs a message followed by a "\n"
        $output->writeln('Whoa!');

        // outputs a message without adding a "\n" at the end of the line
        $output->write('You are about to ');
        $output->write('create a user.');


        $cmd = 'php bin/console swiftmailer:spool:send --env=prod';
        $last_line = system($cmd, $retval);
        $output->writeln($retval);

    }



}