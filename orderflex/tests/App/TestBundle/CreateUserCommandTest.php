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

    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

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
    
}