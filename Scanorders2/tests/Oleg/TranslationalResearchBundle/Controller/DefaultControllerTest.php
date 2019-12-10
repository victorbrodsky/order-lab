<?php

namespace Oleg\TranslationalResearchBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }


    public function testAdd()
    {
        $result = 30+12;

        // assert that your calculator added the numbers correctly!
        $this->assertEquals(42, $result);
    }


    public function aboutActionTest() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/directory/about');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Hello World")')->count()
        );
    }
}
