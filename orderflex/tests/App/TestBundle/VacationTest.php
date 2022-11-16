<?php

namespace Tests\App\TestBundle;

use Tests\App\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class VacationTest extends WebTestBase
{

    public function testLoginPageAction() {

        $phpVersion = phpversion();
        echo "[Vacation,PHP=".$phpVersion."]";
        
        //$this->getTestClient();
        $crawler = $this->client->request('GET', '/time-away-request/login');

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

    public function testgetAcademicYearByFloatingDay() {
        $this->logIn();

        //self::bootKernel();

        // returns the real and unchanged service container
        //$container = self::$kernel->getContainer();

        // gets the special container that allows fetching private services
        //$container = self::$container;

        // Create a stub for the SomeClass class.
        //$stub = $this->createStub(SomeClass::class);

        //$vacreqUtil = self::$container->get('vacreq_util');
        $vacreqUtil = $this->testContainer->get('vacreq_util');

        $floatingDays = array(
            array("06/29/2019", "2018-2019"),
            array("07/29/2019", "2019-2020"),
            array("06/29/2020", "2019-2020"),
            array("06/29/2021", "2020-2021"),
            array("07/01/2021", "2021-2022"),
            array("02/17/2022", "2021-2022"),
            array("06/29/2022", "2021-2022"),
            array("06/30/2022", "2021-2022"),
            array("08/29/2022", "2022-2023"),
            array("06/29/2023", "2022-2023"),
            array("07/01/2023", "2023-2024"),
            array("06/25/2024", "2023-2024"),
            array("07/01/2024", "2024-2025"),
        );

        foreach($floatingDays as $floatingDayArr) {
            $floatingDay = $floatingDayArr[0];
            $expectedRes = $floatingDayArr[1];

            $floatingDayDate = \DateTime::createfromformat('m/d/Y',$floatingDay);

            //$vacreqUtil = $this->get('vacreq_util');
            $yearRangeStr = $vacreqUtil->getAcademicYearBySingleDate($floatingDayDate);
            $this->assertTrue($yearRangeStr == $expectedRes);
        }


    }

    public function testHomeAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/time-away-request/');

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

    public function testCalendar() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/time-away-request/away-calendar/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Away Calendar")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Mon")')->count()
        );
    }

    public function testEventLogAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/time-away-request/event-log/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Event Log showing")')->count()
        );
    }

    public function testAboutAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/time-away-request/about');
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

    public function testListIncomingRequestsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/time-away-request/incoming-requests/');
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
        $crawler = $this->client->request('GET', '/time-away-request/my-requests/');
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

    ///time-away-request/incoming-requests/?filter%5Bstartdate%5D=&filter%5Benddate%5D=&filter%5BrequestType%5D=2&filter%5BacademicYear%5D=&filter%5Buser%5D=&filter%5Bsubmitter%5D=&filter%5BorganizationalInstitutions%5D=
    public function testIncomingCarryOverAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/time-away-request/incoming-requests/?filter[startdate]=&filter[enddate]=&filter[requestType]=2&filter[academicYear]=&filter[user]=&filter[submitter]=&filter[organizationalInstitutions]=');
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
        $crawler = $this->client->request('GET', '/time-away-request/settings/');
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
        $crawler = $this->client->request('GET', '/time-away-request/my-group/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Summary")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Accrued Vacation Days as of today")')->count()
        );
    }

    public function testAuthorizedUsersAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/time-away-request/authorized-users/');

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
        $crawler = $this->client->request('GET', '/time-away-request/user/1');
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
        $crawler = $this->client->request('GET', '/time-away-request/groups/');
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
        $crawler = $this->client->request('GET', '/time-away-request/carry-over-request/new');
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

        $requests = $this->em->getRepository('AppVacReqBundle:VacReqRequest')->findAll();
        if( count($requests) > 0 ) {

            $request = end($requests);
            $requestId = $request->getId();
            //echo "[$requestId=".$requestId."]";

            $crawler = $this->client->request('GET', '/time-away-request/show/'.$requestId);

            //$content = $this->client->getResponse()->getContent();
            //exit("content=$content");

            $countVacationTitle = $crawler->filter('html:contains("Vacation/Business Travel Request")')->count();
            $countCarryoverTitle = $crawler->filter('html:contains("Request carry over of vacation days")')->count();

            $this->assertGreaterThan(
                0,
                $countVacationTitle+$countCarryoverTitle
            );

            if( $countVacationTitle > 0 ) {
                $this->assertGreaterThan(
                    0,
                    $crawler->filter('html:contains("First Day Back In Office")')->count()
                );
                $this->assertGreaterThan(
                    0,
                    $crawler->filter('html:contains("Emergency Contact Info (optional)")')->count()
                );
            }

            if( $countCarryoverTitle > 0 ) {
                $this->assertGreaterThan(
                    0,
                    $crawler->filter('html:contains("Source Academic Year:")')->count()
                );
                $this->assertGreaterThan(
                    0,
                    $crawler->filter('html:contains("Number of days to carry over")')->count()
                );
            }
            
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Edit")')->count()
            );

            $crawler = $this->client->request('GET', '/time-away-request/edit/'.$requestId);

            //$content = $this->client->getResponse()->getContent();
            //exit("content=$content");

            $countVacationTitle = $crawler->filter('html:contains("Vacation/Business Travel Request")')->count();
            $countCarryoverTitle = $crawler->filter('html:contains("Request carry over of vacation days")')->count();

            $this->assertGreaterThan(
                0,
                $countVacationTitle+$countCarryoverTitle
            );

            if( $countVacationTitle > 0 ) {
                $this->assertGreaterThan(
                    0,
                    $crawler->filter('html:contains("First Day Back In Office")')->count()
                );
                $this->assertGreaterThan(
                    0,
                    $crawler->filter('html:contains("Emergency Contact Info (optional)")')->count()
                );
            }

            if( $countCarryoverTitle > 0 ) {
                $this->assertGreaterThan(
                    0,
                    $crawler->filter('html:contains("Source Academic Year")')->count()
                );
                $this->assertGreaterThan(
                    0,
                    $crawler->filter('html:contains("Number of days to carry over")')->count()
                );
            }

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
