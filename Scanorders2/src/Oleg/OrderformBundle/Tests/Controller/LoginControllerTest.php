<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 7/29/14
 * Time: 12:25 PM
 */

namespace Oleg\OrderformBundle\Tests\Controller;


class LoginControllerTest {

    public function testCompleteScenario()
    {

        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Scan Order Submission")')->count()
        );

    }

} 