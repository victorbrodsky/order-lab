<?php

namespace Tests\App\TestBundle;

use Tests\App\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class UserTest extends WebTestBase
{

    public function testLoginPageAction() {

        $phpVersion = phpversion();
        echo "[PHP=".$phpVersion."]";

        $this->getTestClient();
        $crawler = $this->client->request('GET', '/directory/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Employee Directory")')->count()
        );
    }

    public function testHomeAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/directory/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Welcome to the Employee Directory!")')->count()
        );
    }

    public function testEventLogAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/directory/event-log/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Event Log showing")')->count()
        );
    }

    public function testAboutAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/directory/about');
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
        //$linkName = '/translational-research/about';
        //$this->testGetLink($linkName,"Current Version");
        //$this->testGetLink($linkName);
    }

    public function testListCurrentAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/directory/users');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("All Current Employees")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Email List")')->count()
        );
    }

    public function testAddUserAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/directory/user/new');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Create New User")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Name and Preferred Contact Info")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add Employee")')->count()
        );
    }

    public function testSiteSettingsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/directory/settings/');
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

    public function testPlatformListManagerAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/directory/admin/list-manager/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Platform List Manager")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Create a new entry")')->count()
        );
    }

    public function testAuthorizedUsersAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/directory/authorized-users/');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Authorized Users for Employee Directory")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add Authorized User")')->count()
        );
    }

    public function testProfileAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/directory/user/1');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("View Details")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Preferred Contact Info")')->count()
        );
    }

    public function testHierarchyManagerAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/directory/admin/hierarchies/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Hierarchy Manager")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Institution Tree Management")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Patient Lists Hierarchy Management")')->count()
        );
    }

}
