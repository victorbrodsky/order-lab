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
        //echo "[User,PHP=".$phpVersion."]";

        $userServiceUtil = $this->testContainer->get('user_service_utility');
        //echo "[DB=".$userServiceUtil->getDbVersion()."]";

        //$this->client->followRedirects();
        //echo 'login url='.'/'.$this->tenantprefix.'directory/login'.'<br>';
        
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/login');

        //$client = static::createClient();
        //$crawler = $client->request('GET', '/'.$this->tenantprefix.'directory/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Employee Directory")')->count()
        );

        echo 'EOF testLoginPageAction<br>';
    }

    public function testHomeAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/');

        echo "url=".'/'.$this->tenantprefix.'directory/'."<br>";
        //$this->client->followRedirects();

        $content = $this->client->getResponse()->getContent();
        exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Welcome to the Employee Directory!")')->count()
        );
    }

    public function testEventLogAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/event-log/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Event Log showing")')->count()
        );
    }

    public function testAboutAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/about');
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

    public function testListCurrentAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/users');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("All Current Employees")')->count()
        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Email List")')->count()
//        );
    }

    public function testAddUserAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/user/new');
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
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/settings/');
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
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/admin/list-manager/');
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
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/authorized-users/');

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
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/user/1');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("View Details")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Preferred Contact Info")')->count()
        );

        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/edit-user-profile/1');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Edit Employee")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Preferred Contact Info")')->count()
        );
    }

    public function testHierarchyManagerAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/admin/hierarchies/');
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


    public function testFellappNewListManagerAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/admin/list/fellowship-subspecialties/new');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Fellowship Subspecialties")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add New")')->count()
        );
    }

    public function testRoleNewListManagerAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/admin/list/roles/new');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Roles")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add New")')->count()
        );
    }

    public function testBackupShowAction() {
        $this->logIn();

        $crawler = $this->client->request('GET', '/'.$this->tenantprefix.'directory/data-backup-management');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Data Backup Management")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Edit")')->count()
        );
    }

}
