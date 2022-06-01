<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 6/1/2022
 * Time: 1:20 PM
 */

namespace Tests\App\TestBundle;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

//class SimpleWebTest extends WebTestCase
class SimpleWebTest extends WebTestBase
{

    public function testLoginPageAction() {
        //$this->getInit();
        //$this->client = static::createClient();

        //dump($this->client);
        //exit();

        $crawler = $this->client->request('GET', '/directory/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Employee Directory")')->count()
        );
    }

//    public function testLogin2PageAction() {
//        $client = static::createClient();
//
//        $crawler = $client->request('GET', '/directory/login');
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
//        $crawler = $client->request('GET', '/');
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