<?php

namespace Tests\App\TestBundle;

use Tests\App\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class ResappTest extends WebTestBase
{

    public function testLoginPageAction() {

        $phpVersion = phpversion();
        echo "[Resapp,PHP=".$phpVersion."]";
        
        //$this->getTestClient();

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/residency-applications/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Residency Applications")')->count()
        );
    }

//    public function testHomeAction() {
//        $this->logIn();
//
//        $this->client->followRedirects();
//
//        //?filter[startDates]=2022&filter[active]=1&filter[complete]=1&filter[interviewee]=1&filter[priority]=1&filter[accepted]=1&filter[acceptedandnotified]=1
//        $crawler = $this->client->request('GET', '/residency-applications/?filter[startDates]=2022&filter[active]=1&filter[complete]=1&filter[interviewee]=1&filter[priority]=1&filter[accepted]=1&filter[acceptedandnotified]=1');
//        //$crawler = $this->client->request('GET', '/residency-applications/?filter[startDates]=2022');
//
//
//        //$this->client->followRedirects();
//        //$crawler = $this->client->request('GET', '/residency-applications/');
//
//        //$content = $this->client->getResponse()->getContent();
//        //exit("content=$content");
//
////        $this->assertGreaterThan(
////            0,
////            $crawler->filter('html:contains("Applications matching search criteria")')->count()
////        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Medical School")')->count()
//        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Residency")')->count()
//        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Filter")')->count()
//        );
//    }

    public function testEventLogAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/residency-applications/event-log/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Event Log showing")')->count()
        );
    }

    public function testAboutAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/residency-applications/about');
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

    public function testSiteSettingsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/residency-applications/settings/');
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
        $crawler = $this->client->request('GET', '/residency-applications/authorized-users/');

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
        $crawler = $this->client->request('GET', '/residency-applications/user/1');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("View Details")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Preferred Contact Info")')->count()
        );
    }

    public function testAddNewResapp() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/residency-applications/new/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Application Receipt Date:")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Applicant")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Residency Application Type")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Itinerary / Interview Schedule (Please upload PDFs to ensure proper integration in the Complete Application PDF)")')->count()
        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Signature")')->count()
//        );
    }

    public function testMyInterview() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/residency-applications/my-interviewees/?filter[startDates]=2021&filter[active]=1&filter[complete]=1&filter[interviewee]=1&filter[priority]=1&filter[accepted]=1&filter[acceptedandnotified]=1');

//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Last successful import:")')->count()
//        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Residency Track")')->count()
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
        $crawler = $this->client->request('GET', '/residency-applications/send-rejection-emails?filter[startDates]=2021&filter[active]=1&filter[complete]=1&filter[interviewee]=1&filter[priority]=1&filter[reject]=1');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Residency Track")')->count()
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

    public function testResowshipSettings() {
        $this->logIn();

        $crawler = $this->client->request('GET', '/residency-applications/residency-types-settings');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Please use this page to add or remove Directors, Coordinators and Interviewers for specific residency track")')->count()
        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Add a New Residency Application Type")')->count()
//        );
    }

    //TODO: PHPUnit\\Framework\\Error\\Deprecated(code: 8192): implode(): Passing glue string after array is deprecated. Swap the parameters at /opt/order-lab/orderflex/vendor/google/apiclient/src/Google/Http/REST.php:134)
    //TODO: use Google API v2
//    public function testFormConfig() {
//        $this->logIn();
//
//        $crawler = $this->client->request('GET', '/residency-applications/form-status-and-appearance/edit');
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Accepting Submission")')->count()
//        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Residency Application Types")')->count()
//        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Current Configuration File on Google Drive")')->count()
//        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Link to the Application Page")')->count()
//        );
//    }

    public function testShowApplication() {
        $this->logIn();

        $resapps = $this->em->getRepository('AppResAppBundle:ResidencyApplication')->findAll();

        if( count($resapps) > 0 ) {
            $resapp = end($resapps);
            $resappId = $resapp->getId();
        } else {
            echo "Skip testShowApplication; There are no residency applications found";
            return null;
        }

        //Test Show
        $crawler = $this->client->request('GET', '/residency-applications/show/'.$resappId);

//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Download Application as a PDF")')->count()
//        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Applicant")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Residency Application Type")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Edit")')->count()
        );


        //Test Edit
        $crawler = $this->client->request('GET', '/residency-applications/edit/'.$resappId);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Residency Application ID")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Applicant")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Residency Application Type")')->count()
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
