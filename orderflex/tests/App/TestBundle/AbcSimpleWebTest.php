<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 6/1/2022
 * Time: 1:20 PM
 */

namespace Tests\App\TestBundle;


use App\UserdirectoryBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

//class SimpleWebTest extends WebTestCase
class AbcSimpleWebTest extends WebTestBase
{

//    public function testVisitingWhileLoggedIn()
//    {
//
//        $client = static::createClient([], [
//            'HTTP_HOST' => '127.0.0.1',
//            'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
//            //'HTTPS' => false
//        ]);
//
//        // retrieve the test user
//        $systemUser = $this->getUser();
//        //exit('$systemUser='.$systemUser);
//
//        // simulate $testUser being logged in
//        $client->loginUser($systemUser,$firewallContext = 'scan_auth');
//
//        $client->followRedirects();
//
//        // test e.g. the profile page
//        //$client->request('GET', '/'.$this->tenantprefix.'directory/about');
//
//        //$content = $client->getResponse()->getContent();
//        //exit("content=$content");
//
//        //$this->assertResponseIsSuccessful();
//        //$this->assertSelectorTextContains('p', 'Current Version for branch master');
//
//
//        $crawler = $client->request('GET', '/'.$this->tenantprefix.'directory/about');
//
//        //$content = $client->getResponse()->getContent();
//        //exit("content=$content");
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Current Version for branch master")')->count()
//        );
//
////        $this->assertGreaterThan(
////            0,
////            $crawler->filter('html:contains("O R D E R")')->count()
////        );
//    }
//
//    public function getUser()
//    {
//        $em = static::getContainer()->get('doctrine.orm.entity_manager');
//
//        $userSecUtil = static::getContainer()->get('user_security_utility');
//        $systemUser = $userSecUtil->findSystemUser();
//
//        if( !$systemUser ) {
//            $systemUser = $em->getRepository('AppUserdirectoryBundle:User')->findOneByPrimaryPublicUserId('Administrator');
//        }
//
//        if( !$systemUser ) {
//            $systemUser = $em->getRepository('AppUserdirectoryBundle:User')->findOneByPrimaryPublicUserId('administrator');
//        }
//
//        return $systemUser;
//    }

    public function testLoginPageAction() {

        $phpVersion = phpversion();
        echo "[Start Testing. PHP=".$phpVersion."]";
        //echo "[Resapp]";

        $userServiceUtil = $this->testContainer->get('user_service_utility');
        echo "[DB=".$userServiceUtil->getDbVersion()."]";

        $loginUrl = '/'.$this->tenantprefix.'directory/login';
        $crawler = $this->client->request('GET', $loginUrl);
        echo 'login url='.$loginUrl.'<br>';

        //$client = static::createClient();
        //$crawler = $client->request('GET', '/'.$this->tenantprefix.'directory/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Employee Directory")')->count()
        );
    }

//    public function testHomeAction() {
//
//        $this->logIn();
//        //return;
//
//        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/');
//
//        //$content = $this->client->getResponse()->getContent();
//        //exit("content=$content");
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Welcome to the Employee Directory!")')->count()
//        );
//    }

//    public function testLogin2PageAction() {
//        $client = static::createClient();
//
//        $crawler = $client->request('GET', '/'.$this->tenantprefix.'directory/login');
//
//        //$content = $this->client->getResponse()->getContent();
//        //exit("content=$content");
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Employee Directory")')->count()
//        );
//    }

//    public function testWeb()
//    {
//        // This calls KernelTestCase::bootKernel(), and creates a
//        // "client" that is acting as the browser
//        $client = static::createClient();
//
//        // Request a specific page
//        $crawler = $client->request('GET', '/'.$this->tenantprefix.''.$this->tenantprefix.');
//
//        // Validate a successful response and some content
//        $this->assertResponseIsSuccessful();
//
//        $this->assertSelectorTextContains('h2', 'O R D E R');
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Welcome to the O R D E R platform!")')->count()
//        );
//    }

}