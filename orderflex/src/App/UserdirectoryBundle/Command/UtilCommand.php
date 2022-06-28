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

namespace App\UserdirectoryBundle\Command;


//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

//Execute some temporary, utility commands such as populate large data
class UtilCommand extends Command {

    //protected static $defaultName = 'cron:util-command';
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
            ->setName('cron:util-command')
            ->setDescription('Some utility command');
    }


    //Cron job to periodically overnight copy data
    //php app/console cron:util-command --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {

        $em = $this->container->get('doctrine.orm.entity_manager');
        //$logger = $this->container->get('logger');
        $res = "EOF util-command";

        //$calllogUtil = $this->container->get('calllog_util');
        //$res = $calllogUtil->updateTextHtml();
        //exit("EOF updateTextHtmlAction. Res=".$res);

        if(0) {
            $oid = "APCP3296-REQ13549-V1"; //dev
            $oid = "APCP2173-REQ15079-V2"; //collage
            $invoice = $em->getRepository('AppTranslationalResearchBundle:Invoice')->findOneByOid($oid);
            if (!$invoice) {
                throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
            }
            $transresRequestUtil = $this->container->get('transres_request_util');
            $res = $transresRequestUtil->sendInvoicePDFByEmail($invoice);
        }

        if(0) {
            ///// rec letter ////////
            $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
            $fellapp = $em->getRepository('AppFellAppBundle:FellowshipApplication')->find(1414); //8-testing, 1414-collage, 1439-live
            $references = $fellapp->getReferences();
            $reference = $references->first();
            $letters = $reference->getDocuments();
            $uploadedLetterDb = $letters->first();
            $res = $fellappRecLetterUtil->sendRefLetterReceivedNotificationEmail($fellapp, $uploadedLetterDb);

            $fellappType = $fellapp->getFellowshipSubspecialty();
            $res = "ID=" . $fellapp->getId() . ", fellappType=" . $fellappType . ": res=" . $res . "<br>";
            /////////////////////////
        }

        $emailUtil = $this->container->get('user_mailer_utility');
        $emailUtil->testEmailWithAttachments();
        $res = "EOF testEmailWithAttachments";

        //$output->writeln($res);
        $output->writeln($res);

        return Command::SUCCESS;
    }
    
    
    
    
    

} 