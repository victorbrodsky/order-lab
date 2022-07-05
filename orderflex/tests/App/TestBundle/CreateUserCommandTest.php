<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/21/2022
 * Time: 10:27 AM
 */

namespace Tests\App\TestBundle;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends KernelTestCase
{
    protected $client = null;

    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        //$this->logIn();
        $this->getTestClient();
        $this->logIn();

        $command = $application->find('cron:statustest');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            // pass arguments to the helper
            //'username' => 'Wouter',

            // prefix the key with two dashes when passing options,
            // e.g: '--some-option' => 'option_value',
        ]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Testing cron job', $output);
    }


    public function testFellappVerifyImportExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('cron:verifyimport');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            // pass arguments to the helper
            //'username' => 'Wouter',

            // prefix the key with two dashes when passing options,
            // e.g: '--some-option' => 'option_value',
        ]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Verify Fellowship application import', $output);
    }


    public function logIn() {
        $systemUser = $this->getUser();

        $firewallContext = 'scan_auth';

        // simulate $testUser being logged in
        $this->client->loginUser($systemUser,$firewallContext);
    }
    public function getUser()
    {
        $userSecUtil = $this->client->getContainer()->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        if( !$systemUser ) {
            $systemUser = $this->em->getRepository('AppUserdirectoryBundle:User')->findOneByPrimaryPublicUserId('Administrator');
        }

        if( !$systemUser ) {
            $systemUser = $this->em->getRepository('AppUserdirectoryBundle:User')->findOneByPrimaryPublicUserId('administrator');
        }

        return $systemUser;
    }
    public function getTestClient(array $options = array(), array $server = array()) {
        //TODO: detect if HTTP or HTTPS used by url

        //To specify http channel run it as: HTTP=1 ./bin/phpunit
        //To specify https channel (default) run it as: ./bin/phpunit
        $channel = getenv('HTTP');
        //echo "channel=[".$httpsChannel."]<br>";
        if( $channel ) {
            //echo "HTTP";
            $httpsChannel = false;
        } else {
            //echo "HTTPS";
            $httpsChannel = true;
        }

//        $client = static::createClient();
//        $userUtil = $client->getContainer()->get('user_utility');
//        $scheme = $userUtil->getScheme();
//        //exit("scheme=$scheme");
//        if( $scheme ) {
//            if( strtolower($scheme) == 'http' ) {
//                //echo "HTTP";
//                $httpsChannel = false;
//            } else {
//                //echo "HTTPS";
//                $httpsChannel = true;
//            }
//        }

        $client = static::createClient([], [
            'HTTP_HOST' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
            'HTTPS' => $httpsChannel
        ]);

        $this->client = $client;

        //Alternative of setting HTTPS: When running on https this will follow redirect from http://127.0.0.1 to https://127.0.0.1
        //$this->client->followRedirects();
    }
    
}