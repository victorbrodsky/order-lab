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

namespace Oleg\UserdirectoryBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class TestCommand extends ContainerAwareCommand
{

    protected function configure() {
        $this
            ->setName('cron:simple-tests')
            ->setDescription('Run simple tests');
    }

    //php app/console cron:simple-tests --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->getContainer()->get('logger');

        $fellappRepGen = $this->getContainer()->get('fellapp_reportgenerator');
        $fellappApplicationId = 1;

        //$reportsUploadPathFellApp = "Reports";
        //$uploadpath = $this->getContainer()->getParameter('fellapp.uploadpath');
        //$uploadReportPath = 'Uploaded' . DIRECTORY_SEPARATOR . $uploadpath . DIRECTORY_SEPARATOR .$reportsUploadPathFellApp;
        $uploadReportPath = "Uploaded/fellapp/Reports";

        ///usr/local/bin/order-lab/Scanorders2/web/Uploaded/fellapp/Reports
        $reportPath = $this->getContainer()->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . $uploadReportPath. DIRECTORY_SEPARATOR;

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
        $result = $this->generatePdfPhantomjs($pdfPath,$pdfPathParametersArr,$applicationFilePath,null);
        $resultArr[] = $result;

        $resultTotal = implode("\r\n",$resultArr);
        $output->writeln($resultTotal);
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