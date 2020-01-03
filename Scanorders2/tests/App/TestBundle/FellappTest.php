<?php

namespace Tests\App\TestBundle;

use Tests\App\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class FellappTest extends WebTestBase
{

    public function testLoginPageAction() {

        $phpVersion = phpversion();
        echo "[PHP=".$phpVersion."]";
        
        $this->getClient();

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/fellowship-applications/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Fellowship Applications")')->count()
        );
    }

    public function testHomeAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/fellowship-applications/?filter[startDates]=2021&filter[active]=1&filter[complete]=1&filter[interviewee]=1&filter[priority]=1&filter[accepted]=1&filter[acceptedandnotified]=1');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Applications matching search criteria")')->count()
        );
    }

    public function testEventLogAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/fellowship-applications/event-log/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Event Log showing")')->count()
        );
    }

    public function testAboutAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/fellowship-applications/about');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Current Version")')->count()
        );
        //$linkName = '/translational-research/about';
        //$this->testGetLink($linkName,"Current Version");
        //$this->testGetLink($linkName);
    }

    public function testSiteSettingsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/fellowship-applications/settings/');
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
        $crawler = $this->client->request('GET', '/fellowship-applications/authorized-users/');

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
        $crawler = $this->client->request('GET', '/fellowship-applications/user/1');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("View Details")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Preferred Contact Info")')->count()
        );
    }

    public function testAddNewFellapp() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/fellowship-applications/new/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Application Receipt Date:")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Applicant Data")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Itinerary / Interview Schedule (Please upload PDFs to ensure proper integration in the Complete Application PDF)")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Signature")')->count()
        );
    }

    public function testMyInterview() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/fellowship-applications/my-interviewees/?filter[startDates]=2021&filter[active]=1&filter[complete]=1&filter[interviewee]=1&filter[priority]=1&filter[accepted]=1&filter[acceptedandnotified]=1');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Last successful import:")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Fellowship Type")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Medical School")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("U1 [C1]")')->count()
        );
    }

    public function testSendRejectionEmail() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/fellowship-applications/send-rejection-emails?filter[startDates]=2021&filter[active]=1&filter[complete]=1&filter[interviewee]=1&filter[priority]=1&filter[reject]=1');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Fellowship Type")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Medical School")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("U1 [C1]")')->count()
        );
    }

    public function testFellowshipSettings() {
        $this->logIn();

        $crawler = $this->client->request('GET', '/fellowship-applications/fellowship-types-settings');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Please use this page to add or remove Directors, Coordinators and Interviewers for specific fellowship type")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add a New Fellowship Application Type")')->count()
        );
    }

    public function testFormConfig() {
        $this->logIn();

        $crawler = $this->client->request('GET', '/fellowship-applications/form-status-and-appearance/edit');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Accepting Submission")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Fellowship Application Types")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Current Configuration File on Google Drive")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Link to the Application Page")')->count()
        );
    }

    public function testShowApplication() {
        $this->logIn();

        $fellapps = $this->em->getRepository('AppFellAppBundle:FellowshipApplication')->findAll();

        if( count($fellapps) > 0 ) {
            $fellapp = end($fellapps);
            $fellappId = $fellapp->getId();
        } else {
            echo "Skip testShowApplication; There are no fellowship applications found";
            return null;
        }

        //Test Show
        $crawler = $this->client->request('GET', '/fellowship-applications/show/'.$fellappId);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Download Application as a PDF")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Applicant Data")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Signature")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Edit")')->count()
        );


        //Test Edit
        $crawler = $this->client->request('GET', '/fellowship-applications/edit/'.$fellappId);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Fellowship Application ID")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Applicant Data")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Signature")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Update")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Cancel")')->count()
        );
    }
}
