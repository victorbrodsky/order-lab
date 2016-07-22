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


class SwiftCronCommand extends ContainerAwareCommand {


    protected function configure() {
        $this
            ->setName('cron:swift')
            ->setDescription('Cron job to send emails from file spool');
    }


    //php app/console cron:swift --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) {

        $logger = $this->getContainer()->get('logger');

        //$userSecUtil = $this->getContainer()->get('user_security_utility');

        $cmd = 'php app/console swiftmailer:spool:send --env=prod';

        //$oExec = pclose(popen("start /B ". $cmd, "r"));
        //$result = exec($cmd);

        // Outputs all the result of shellcommand "ls", and returns
        // the last output line into $last_line. Stores the return value
        // of the shell command in $retval.
        $last_line = system($cmd, $retval);

        // Printing additional info
        //Last line of the output: ' . $last_line . '
        //Return value: ' . $retval;

        $logger->notice("cron:swift: Last line of the output:".$last_line."; Return value:".$retval);

        $output->writeln($retval);
    }

} 