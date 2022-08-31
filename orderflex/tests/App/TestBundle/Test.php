<?php

namespace Tests\App\TestBundle;

use Tests\App\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;



class Test extends WebTestBase
{

    public function testLoginPageAction() {

        $phpVersion = phpversion();
        echo "[PHP=".$phpVersion."]";

        //$this->getTestClient();
        $crawler = $this->client->request('GET', '/directory/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Employee Directory")')->count()
        );
    }

    public function testBaseContainerAction() {
        $this->logIn();

        $phpVersion = phpversion();
        echo "[PHP=".$phpVersion."]";

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/directory/test/container/testbase');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("TestBaseController")')->count()
        );

//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Container Test")')->count()
//        );
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("user=")')->count()
//        );
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("id=")')->count()
//        );
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("id=")')->count()
//        );
    }

    public function testContainerAction() {
        $this->logIn();

        $phpVersion = phpversion();
        echo "[PHP=".$phpVersion."]";

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/directory/test/container/test');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("TestController")')->count()
        );

//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Container Test")')->count()
//        );
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("user=")')->count()
//        );
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("id=")')->count()
//        );
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("id=")')->count()
//        );
    }

}
