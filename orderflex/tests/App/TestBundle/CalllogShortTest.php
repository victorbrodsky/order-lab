<?php

namespace Tests\App\TestBundle;

use Tests\App\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

//use Tests\App\TestBundle\WebTestBase;


class CalllogShortTest extends WebTestBase
{

    public function testLoginPageAction() {

        $phpVersion = phpversion();
        echo "[PHP=".$phpVersion."]";

        //$calllogUtil = self::$container->get('calllog_util');

        //$this->getTestClient();
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/call-log-book/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Call Log Book")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Log In")')->count()
        );
    }

}
