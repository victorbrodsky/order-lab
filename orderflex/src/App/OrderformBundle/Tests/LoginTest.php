<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 7/29/14
 * Time: 12:25 PM
 */

namespace App\OrderformBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class LoginTest extends WebTestCase {

//    private $client = null;
//
//    public function setUp()
//    {
//        $this->client = static::createClient();
//    }

    public function testLoginPage()
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/order/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("O R D E R")')->count()
        );

        $crawler = $client->request('GET', '/scan/login');
        //print_r($crawler);
//        foreach ($crawler as $domElement) {
//            print $domElement->nodeName;
//        }
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Scan Orders")')->count()
        );

    }


    //TODO: login failed for pacsvendor users:
    //comment out spl_autoload_register(array('LoggerAutoloader', 'autoload')); in E:\Program Files (x86)\pacsvendor\pacsname\htdocs\log4php\LoggerAutoloader.php
    //[2014-07-30 19:14:11] ldap_driver.DEBUG: ldap_bind(testprocessor, ****) [] []
    //[2014-07-30 19:14:32] ldap_driver.DEBUG: 0x31 (Invalid credentials; 80090308: LdapErr: DSID-0C0903A9, comment: AcceptSecurityContext error, data 52e, v1db1): testprocessor@a.wcmc-ad.net [] []
    public function testLoginProcess()
    {

        $client = static::createClient();
        $client->followRedirects();

        //$cookie = new Cookie('locale2', 'en', time() + 3600 * 24 * 7, '/', null, false, false);
        //$client->getCookieJar()->set($cookie);

        $_SERVER['HTTP_USER_AGENT'] = 'phpunit test';

        // Visit user login page and login
        $crawler = $client->request('GET', '/scan/login');

        //test if login page is opened
        $this->assertTrue($client->getResponse()->isSuccessful());

        // Select based on button value, or id or name for buttons
        $form = $crawler->selectButton('Log In')->form();

        // set some values
        //$form['_username'] = 'testprocessor';
        //$form['_password'] = 'testprocessor1';

        $form['_username'] = '';
        $form['_password'] = '';

        //$client->insulate();

        // submit the form
        $crawler = $client->submit($form);

        //$this->assertTrue($client->getResponse()->isSuccessful());

        //echo "\n\n\nclient response:\n\n\n";
        //echo $crawler->html();
        //var_dump($crawler->links());
        //print_r($client->getResponse()->getContent());
        //echo "\n\n\n";


        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Welcome to the Scan Order System")')->count()
        );

        $crawler = $client->request('GET', '/scan/my-scan-orders');

        $this->assertTrue($client->getResponse()->isSuccessful());


        //$this->assertEquals('Hello', 'Hello');

//        echo "client response:<br>";
//        var_dump($client->getResponse()->getContent());
//        echo "<br>";

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("My Scan Orders")')->count()
        );


        //test form submit
        $crawler = $client->request('GET', '/scan/scan-order/multi-slide/new');

//        echo "client response:<br>";
//        var_dump($client->getResponse()->getContent());
//        echo "<br>";

        $this->assertTrue($client->getResponse()->isSuccessful());

        //$next = $crawler2->selectButton('Next')->link();
        //$next = $crawler->filter('button:contains("Next")')->eq(1)->link();
        //$crawler2 = $client->click($next);

        $form = $crawler->selectButton('btnSubmit')->form();

        $form['oleg_orderformbundle_messagetype[patient][0][encounter][0][procedure][0][accession][0][part][0][block][0][slide][0][title]'] = 'Slide submitted by phpunit test';

        $form['oleg_orderformbundle_messagetype[patient][0][encounter][0][procedure][0][accession][0][part][0][block][0][slide][0][slidetype]'] = 7;

        $form['oleg_orderformbundle_messagetype[patient][0][clinicalHistory][0][field]'] = 'clinical history test';

        $form['oleg_orderformbundle_messagetype[patient][0][mrn][0][field]'] = '0000000';



        $_POST['btnSubmit'] = "btnSubmit";

        //sleep(10);

        $crawler = $client->submit($form);

//        echo "client response:<br>";
//        var_dump($client->getResponse()->getContent());
//        echo "<br>";
        //exit();

        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Thank you for your order")')->count()
        );
    }






    
    public function dddtestSecuredMyOrders()
    {
        $client = static::createClient();
        $client->followRedirects();

        $this->logIn($client);

        echo "client response:<br>";
        var_dump($client->getResponse());
        //exit();

        $crawler = $client->request('GET', '/scan/my-scan-orders');

        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertGreaterThan(0, $crawler->filter('html:contains("Welcome to the Scan Order System")')->count());
    }


    private function logIn($client)
    {
        $session = $client->getContainer()->get('session');

        $firewall = 'external_ldap_firewall';
        $token = new UsernamePasswordToken('testprocessor', null, $firewall, array('ROLE_PLATFORM_DEPUTY_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

} 