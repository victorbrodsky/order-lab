<?php

//namespace Oleg\TranslationalResearchBundle\Tests\Controller;
//namespace Oleg\TranslationalResearchBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


// ./bin/simple-phpunit

class DefaultControllerTest extends WebTestCase
{

//    public function testAdd()
//    {
//        $result = 30+12;
//
//        // assert that your calculator added the numbers correctly!
//        $this->assertEquals(42, $result);
//    }

    public function testHomeAction() {
        $client = static::createClient();

        //http://localhost/order/directory/login
        //$crawler = $client->request('GET', '/translational-research/login');
        $crawler = $client->request('GET', '/order/directory/login');

        $uri = $client->getRequest()->getUri();
        echo "uri=$uri \n";
        //exit("uri=$uri");

        //$content = $client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('a:contains("The following sites are available")')->count()
            //$crawler->filter('html:contains("The following sites are available")')->count()
            //$crawler->filter('html:contains("Please use your")')->count()
        );

//        $this->assertContains(
//            'The following sites are available',
//            $client->getResponse()->getContent()
//        );
    }

    public function testAboutAction() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/directory/about');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Current Version")')->count()
        );
    }
}
