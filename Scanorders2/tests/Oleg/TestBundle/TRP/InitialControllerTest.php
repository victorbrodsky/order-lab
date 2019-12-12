<?php

//namespace Oleg\TranslationalResearchBundle\Tests\Controller;
//namespace Oleg\TranslationalResearchBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

//./bin/simple-phpunit tests/Oleg/TranslationalResearchBundle/Controller/InitialControllerTest.php

class InitialControllerTest extends \Tests\Oleg\TestBundle\WebTestBase
{

//    private $client = null;
//
//    public function setUp()
//    {
////        $this->client = static::createClient();
////        $this->client->request('GET', '/', [], [], [
////            'HTTP_HOST'       => '127.0.0.1',
////            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
////        ]);
//
//        $this->client = static::createClient([], [
//            'HTTP_HOST'       => '127.0.0.1',
//            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
//        ]);
//    }


    public function testLoginProcess()
    {
        return;

//        $this->client = static::createClient([], [
//            'HTTP_HOST'       => '127.0.0.1',
//            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
//        ]);
        //$this->client->followRedirects();

        //$cookie = new Cookie('locale2', 'en', time() + 3600 * 24 * 7, '/', null, false, false);
        //$client->getCookieJar()->set($cookie);

        //$_SERVER['HTTP_USER_AGENT'] = 'phpunit test';

        // Visit user login page and login
        $crawler = $this->client->request('GET', '/directory/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Employee Directory")')->count()
        );

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Please use your CWID to log in")')->count()
        );
        //return;

        //$uri = $this->client->getRequest()->getUri();
        //echo "Directory Login uri=$uri \r\n";
        //exit('000 crawler');
        //test if login page is opened
        //$this->assertTrue($client->getResponse()->isSuccessful());
        //exit('000 assertTrue');
        // Select based on button value, or id or name for buttons
        $form = $crawler->selectButton('Log In')->form();

        // set some values
        $form['_username'] = 'username';
        $form['_password'] = 'pa$$word';

        //$form['_username'] = '';
        // $form['_password'] = '';

        //$client->insulate();

        // submit the form
        $crawler = $this->client->submit($form);

        //$this->assertTrue($client->getResponse()->isSuccessful());
        if(0) {
            exit('000');
            echo "\n\n\nclient response:\n\n\n";
            //echo $crawler->html();
            //var_dump($crawler->links());
            print_r($client->getResponse()->getContent());
            echo "\n\n\n";
            exit('111');
        }

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Welcome to the Scan Order System")')->count()
        );

        $crawler = $this->client->request('GET', '/directory/');

        //$this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Welcome to the Employee Directory!")')->count()
        );


        //$this->assertEquals('Hello', 'Hello');

//        echo "client response:<br>";
//        var_dump($client->getResponse()->getContent());
//        echo "<br>";

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Welcome to the Employee Directory!")')->count()
        );


        //test form submit
        $crawler = $client->request('GET', '/order/directory/new');

//        echo "client response:<br>";
//        var_dump($client->getResponse()->getContent());
//        echo "<br>";

        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Create New User")')->count()
        );
    }

    public function testHomePage() {

        return;

        if(1) {
            $this->logIn();
        } else {
            $this->client = static::createClient([], [
                'HTTP_HOST' => '127.0.0.1',
                'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
            ]);
        }

        ///under-construction
        $crawler = $this->client->request('GET', '/directory/');

        //$uri = $this->client->getRequest()->getUri();
        //echo "under-construction uri=$uri \r\n";
        //exit("uri=$uri");

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            //$crawler->filter('html:contains("Redirecting to")')->count()
            //$crawler->filter('html:contains("The following sites are available")')->count()
            $crawler->filter('html:contains("Welcome to the Employee Directory!")')->count()
        );

        //exit("Exit login test");
//        $this->assertContains(
//            'The following sites are available',
//            $this->client->getResponse()->getContent()
//        );
    }

    public function testUnderConstruction() {

        //return;

        if(1) {
            $this->logIn();
        } else {
            $this->client = static::createClient([], [
                'HTTP_HOST' => '127.0.0.1',
                'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
            ]);
        }

        ///under-construction
        $crawler = $this->client->request('GET', '/directory/under-construction');

        //$uri = $this->client->getRequest()->getUri();
        //echo "under-construction uri=$uri \r\n";
        //exit("uri=$uri");

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            //$crawler->filter('html:contains("Redirecting to")')->count()
            //$crawler->filter('html:contains("The following sites are available")')->count()
            $crawler->filter('html:contains("Currently Undergoing Maintenance")')->count()
        );

        //exit("Exit login test");
//        $this->assertContains(
//            'The following sites are available',
//            $this->client->getResponse()->getContent()
//        );
    }

    public function testPackingSlip() {
        return;

        if(0) {
            $this->logIn();
        } else {
            $this->client = static::createClient([], [
                'HTTP_HOST' => '127.0.0.1',
                'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
            ]);
        }

        //http://localhost/order/directory/login
        //$crawler = $this->client->request('GET', 'directory/');
        //$crawler = $this->client->request('GET', '/directory/under-construction');
        //$crawler = $this->client->request('GET', 'http://127.0.0.1/order/directory/under-construction');

        //http://127.0.0.1/order/translational-research/work-request/download-packing-slip-pdf/1
        //$crawler = $this->client->request('GET', 'http://127.0.0.1/translational-research/work-request/download-packing-slip-pdf/1');
        $crawler = $this->client->request('GET', '/translational-research/work-request/download-packing-slip-pdf/1');

        //$uri = $this->client->getRequest()->getUri();
        //echo "uri=$uri \r\n";
        //exit("uri=$uri");

        //$content = $this->client->getResponse()->getContent();
        //exit("home content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Packing Slip")')->count()
        );

    }


//    public function logIn()
//    {
//        $session = $this->client->getContainer()->get('session');
//
//        $firewallName = 'ldap_fellapp_firewall';
//        // if you don't define multiple connected firewalls, the context defaults to the firewall name
//        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
//        $firewallContext = 'scan_auth';
//
//        // you may need to use a different token class depending on your application.
//        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
//        $token = new UsernamePasswordToken('administrator', null, $firewallName, ['ROLE_PLATFORM_ADMIN']);
//        $session->set('_security_'.$firewallContext, serialize($token));
//        $session->save();
//
//        $cookie = new Cookie($session->getName(), $session->getId());
//        $this->client->getCookieJar()->set($cookie);
//    }
}
