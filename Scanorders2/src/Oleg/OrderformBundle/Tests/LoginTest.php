<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 7/29/14
 * Time: 12:25 PM
 */

namespace Oleg\OrderformBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class LoginTest extends WebTestCase {

    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testLoginPage()
    {


        $crawler = $this->client->request('GET', '/login');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Scan Order Submission")')->count()
        );

    }

    public function testLoginProcess()
    {

        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        //$this->assertEquals('Hello', 'Hello');

        // Select based on button value, or id or name for buttons
        //$form = $crawler->selectButton('Submit')->form();
        $form = $crawler->selectButton('Log In')->form();

        // set some values
        $form['_username'] = 'testprocessor';
        $form['_password'] = 'testprocessor1';

        // submit the form
        $crawler = $client->submit($form);

        //$this->assertTrue($client->getResponse()->isSuccessful());

        echo "client response:<br>";
        var_dump($client->getResponse()->getContent());
        echo "<br>";

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Welcome to the Scan Order System")')->count()
        );


        //$this->assertEquals('Hello', 'Hello');

//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("My Scan Orders")')->count()
//        );

    }






    
    public function dddtestSecuredMyOrders()
    {
        $this->logIn();

        echo "client response:<br>";
        var_dump($this->client->getResponse());
        //exit();

        $crawler = $this->client->request('GET', '/scan/my-scan-orders');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->assertGreaterThan(0, $crawler->filter('html:contains("Welcome to the Scan Order System")')->count());
    }


    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'aperio_ldap_firewall';
        $token = new UsernamePasswordToken('testprocessor', null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

} 