<?php

//namespace Oleg\TranslationalResearchBundle\Tests\Controller;
//namespace Oleg\TranslationalResearchBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

//./bin/simple-phpunit tests/Oleg/TranslationalResearchBundle/Controller/TranslationalResearchControllerTest.php

class TranslationalResearchControllerTest extends WebTestCase
{

    private $client = null;

    public function setUp()
    {
//        $this->client = static::createClient();
//        $this->client->request('GET', '/', [], [], [
//            'HTTP_HOST'       => '127.0.0.1',
//            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
//        ]);

        $this->client = static::createClient([], [
            'HTTP_HOST'       => '127.0.0.1',
            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
        ]);
    }


//    public function testAdd()
//    {
//        $result = 30+12;
//
//        // assert that your calculator added the numbers correctly!
//        $this->assertEquals(42, $result);
//    }

    public function testHomeAction() {
        //$this->logIn();
        $client = static::createClient();

        //http://localhost/order/directory/login
        $crawler = $client->request('GET', 'http://127.0.0.1/order/');
        //$crawler = $this->client->request('GET', '/order/');

        $uri = $client->getRequest()->getUri();
        echo "login uri=$uri \r\n";
        //exit("uri=$uri");

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('a:contains("The following sites are available")')->count()
        //$crawler->filter('html:contains("The following sites are available")')->count()
        //$crawler->filter('html:contains("Please use your")')->count()
        );

        exit("Exit login test");
//        $this->assertContains(
//            'The following sites are available',
//            $this->client->getResponse()->getContent()
//        );
    }

//    public function testUnderConstruction() {
//        //under-construction
//        $this->logIn();
//        //$client = static::createClient();
//
//        //http://localhost/order/directory/login
//        //$crawler = $this->client->request('GET', '/translational-research/login');
//        $crawler = $this->client->request('GET', '/order/directory/under-construction');
//
//        $uri = $this->client->getRequest()->getUri();
//        echo "under-construction uri=$uri \r\n";
//        //exit("uri=$uri");
//
//        //$content = $this->client->getResponse()->getContent();
//        //exit("content=$content");
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Currently Undergoing Maintenance")')->count()
//        //$crawler->filter('html:contains("The following sites are available")')->count()
//        //$crawler->filter('html:contains("Please use your")')->count()
//        );
//
//        exit("exit under-construction");
//    }

    public function testLoginProcess()
    {
        $client = static::createClient();
        $client->followRedirects();

        //$cookie = new Cookie('locale2', 'en', time() + 3600 * 24 * 7, '/', null, false, false);
        //$client->getCookieJar()->set($cookie);

        $_SERVER['HTTP_USER_AGENT'] = 'phpunit test';

        // Visit user login page and login
        $crawler = $client->request('GET', '/order/directory/login');

        echo "\n\n\nclient response:\n\n\n";
        //echo $crawler->html();
        //var_dump($crawler->links());
        print_r($client->getResponse()->getContent());
        echo "\n\n\n";
        exit('Exit on login page');

        $uri = $client->getRequest()->getUri();
        echo "login uri=$uri \r\n";
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
        $crawler = $client->submit($form);

        //$this->assertTrue($client->getResponse()->isSuccessful());
        exit('000');
        echo "\n\n\nclient response:\n\n\n";
        //echo $crawler->html();
        //var_dump($crawler->links());
        print_r($client->getResponse()->getContent());
        echo "\n\n\n";
        exit('111');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Welcome to the Scan Order System")')->count()
        );

        $crawler = $client->request('GET', '/order/directory/');

        $this->assertTrue($client->getResponse()->isSuccessful());


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


//        //$next = $crawler2->selectButton('Next')->link();
//        //$next = $crawler->filter('button:contains("Next")')->eq(1)->link();
//        //$crawler2 = $client->click($next);
//
//        $form = $crawler->selectButton('btnSubmit')->form();
//
//        $form['oleg_orderformbundle_messagetype[patient][0][encounter][0][procedure][0][accession][0][part][0][block][0][slide][0][title]'] = 'Slide submitted by phpunit test';
//
//        $form['oleg_orderformbundle_messagetype[patient][0][encounter][0][procedure][0][accession][0][part][0][block][0][slide][0][slidetype]'] = 7;
//
//        $form['oleg_orderformbundle_messagetype[patient][0][clinicalHistory][0][field]'] = 'clinical history test';
//
//        $form['oleg_orderformbundle_messagetype[patient][0][mrn][0][field]'] = '0000000';
//
//
//
//        $_POST['btnSubmit'] = "btnSubmit";
//
//        //sleep(10);
//
//        $crawler = $client->submit($form);
//
////        echo "client response:<br>";
////        var_dump($client->getResponse()->getContent());
////        echo "<br>";
//        //exit();
//
//        $this->assertTrue($client->getResponse()->isSuccessful());
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Thank you for your order")')->count()
//        );
    }




    public function testAboutAction() {

        $this->logIn();

        $crawler = $this->client->request('GET', '/order/directory/about');
        $uri = $this->client->getRequest()->getUri();
        echo "about uri=$uri \r\n";
        //http://127.0.0.1/order/translational-research/about
        //http://127.0.0.1/order/directory/about
        $this->assertGreaterThan(
            0,
            $crawler->filter('a:contains("Current Version")')->count()
        );
    }




    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewallName = 'ldap_fellapp_firewall';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'scan_auth';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken('administrator', null, $firewallName, ['ROLE_PLATFORM_ADMIN']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
