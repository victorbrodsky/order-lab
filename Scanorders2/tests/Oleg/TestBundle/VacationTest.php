<?php

namespace Tests\Oleg\TestBundle;

use Tests\Oleg\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class VacationTest extends WebTestBase
{

    public function testLoginPageAction() {
        $this->getClient();
        $crawler = $this->client->request('GET', '/vacation-request/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Vacation Request")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Log In")')->count()
        );
    }

    public function testHomeAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/vacation-request/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Vacation/Business Travel Request")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Emergency Contact Info (optional)")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Submit")')->count()
        );

        //name="btnCreate" id="btnCreateVacReq"
        //$btn = $crawler->selectButton('#btnCreateVacReq')->link();
        //$crawler = $this->client->click($btn);
        //$btn = $crawler->filter('#btnCreateVacReq')->eq(0)->link();
        //$crawler = $this->client->click($btn);

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('div:contains("Please choose an organizational group")')->count()
//        );
    }

    public function testEventLogAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/vacation-request/event-log/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Event Log showing")')->count()
        );
    }

    public function testAboutAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/vacation-request/about');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Current Version")')->count()
        );
        //$linkName = '/translational-research/about';
        //$this->testGetLink($linkName,"Current Version");
        //$this->testGetLink($linkName);
    }

    public function testListIncomingRequestsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/vacation-request/incoming-requests/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Incoming Business Travel & Vacation Requests")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Business Travel Requests")')->count()
        );
    }

    public function testMyRequestsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/vacation-request/my-requests/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("My Business Travel & Vacation Requests")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Business Travel Requests")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Filter")')->count()
        );
    }

    ///vacation-request/incoming-requests/?filter%5Bstartdate%5D=&filter%5Benddate%5D=&filter%5BrequestType%5D=2&filter%5BacademicYear%5D=&filter%5Buser%5D=&filter%5Bsubmitter%5D=&filter%5BorganizationalInstitutions%5D=
    public function testIncomingCarryOverAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/vacation-request/incoming-requests/?filter[startdate]=&filter[enddate]=&filter[requestType]=2&filter[academicYear]=&filter[user]=&filter[submitter]=&filter[organizationalInstitutions]=');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Incoming Carry Over Requests")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Carry Over")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Filter")')->count()
        );
    }

    public function testSiteSettingsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/vacation-request/settings/');
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

    public function testMyGroupAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/vacation-request/my-group/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("My Group")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Accrued Vacation Days as of today")')->count()
        );
    }

    public function testAuthorizedUsersAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/vacation-request/authorized-users/');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Authorized Users for Vacation Request")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add Authorized User")')->count()
        );
    }

    public function testProfileAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/vacation-request/user/1');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("View Details")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Preferred Contact Info")')->count()
        );
    }

    public function testManageGroups() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/vacation-request/groups/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Vacation & Business Travel Request Groups")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add Group")')->count()
        );
    }

    public function testCreateRequestCarryOver() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/vacation-request/carry-over-request/new');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Request carry over of vacation days")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Submit")')->count()
        );
    }

    public function testShowEditRequest() {
        $this->logIn();

        $requests = $this->em->getRepository('OlegVacReqBundle:VacReqRequest')->findAll();
        if( count($requests) > 0 ) {

            $request = end($requests);
            $requestId = $request->getId();

            $crawler = $this->client->request('GET', '/vacation-request/show/'.$requestId);

            //$content = $this->client->getResponse()->getContent();
            //exit("content=$content");

            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Vacation/Business Travel Request")')->count()
            );
//            $this->assertGreaterThan(
//                0,
//                $crawler->filter('html:contains("Vacation Travel")')->count()
//            );
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("First Day Back In Office")')->count()
            );
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Emergency Contact Info (optional)")')->count()
            );
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Edit")')->count()
            );

            $crawler = $this->client->request('GET', '/vacation-request/edit/'.$requestId);

            //$content = $this->client->getResponse()->getContent();
            //exit("content=$content");

            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Vacation/Business Travel Request")')->count()
            );
//            $this->assertGreaterThan(
//                0,
//                $crawler->filter('html:contains("Vacation Travel")')->count()
//            );
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("First Day Back In Office")')->count()
            );
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Emergency Contact Info (optional)")')->count()
            );
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Update")')->count()
            );
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Cancel")')->count()
            );
        } else {
            echo "Skip testShowEditRequest, vacation/business requests not found";
        }
    }

}
