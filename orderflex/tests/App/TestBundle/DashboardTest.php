<?php

namespace Tests\App\TestBundle;

use Tests\App\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class DashboardTest extends WebTestBase
{

    public function testLoginPageAction() {

        $phpVersion = phpversion();
        echo "[Dashboard,PHP=".$phpVersion."]";
        
        //$this->getTestClient();

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/dashboards/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Dashboards")')->count()
        );
    }

    public function testEventLogAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/dashboards/event-log/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Event Log showing")')->count()
        );
    }

    public function testAboutAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/dashboards/about');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("PHP_VERSION")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Kernel")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Symfony")')->count()
        );
    }

    public function testSiteSettingsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/dashboards/settings/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Platform Settings")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Miscellaneous")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Populate Lists")')->count()
        );
    }

    public function testAuthorizedUsersAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/dashboards/authorized-users/');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Authorized Users")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add Authorized User")')->count()
        );
    }

    public function testProfileAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/dashboards/user/1');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("View Details")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Preferred Contact Info")')->count()
        );
    }

    public function testHomePageAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/dashboards/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Favorites")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Topic")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Service")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Type")')->count()
        );
    }









}
