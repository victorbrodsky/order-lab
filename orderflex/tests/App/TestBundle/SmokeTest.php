<?php
/**
 * Created by Oleg Ivanov.
 * Date: 7/6/2026
 * Time: 11:23 AM
 */

namespace Tests\App\TestBundle;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestBase
{
    //#[DataProvider('urlProvider')]
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public static function urlProvider(): \Generator
    {
        yield ['/'];
        yield ['/directory/users'];

        yield ['/translational-research/projects/'];
        yield ['/translational-research/work-requests/list/'];
        yield ['/translational-research/invoice/list/All-Invoices'];

        yield ['/time-away-request/'];
        yield ['/time-away-request/incoming-requests/'];
        yield ['/time-away-request/groups/'];
        yield ['/time-away-request/carry-over-request/new'];

        yield ['/call-log-book/'];
        yield ['/call-log-book/entry/new'];

        yield ['/fellowship-applications'];
        yield ['/fellowship-applications/fellowship-types-settings'];

        yield ['/residency-applications'];
    }
}

