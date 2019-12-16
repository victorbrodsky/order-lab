<?php

namespace Tests\Oleg\TestBundle;

use Tests\Oleg\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class CalllogTest extends WebTestBase
{

    public function testLoginPageAction() {
        $this->getClient();
        $crawler = $this->client->request('GET', '/call-log-book/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Call Log Book")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Log In")')->count()
        );
    }

    public function testHomeAction() {
        $this->logIn();

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/call-log-book/');
        //$crawler = $this->client->request('GET', '/call-log-book/?filter[messageStatus]=All except deleted&filter[messageCategory]=Pathology Call Log Entry_32&filter[mrntype]=1');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Call Case List")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Patient Name")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("MRN")')->count()
        );


        //Test view
        $link = $crawler
            //->filter('a:contains("/order/call-log-book/entry/view/")') // find all links with the text "Greet"
            //->filter('.calllog_entry_view_link')
            ->filter('.calllog_entry_view_link')
            ->eq(0) // select the second link in the list
            ->link()
        ;
        //$links = $crawler->filter('a:contains("/order/call-log-book/entry/view/")');
        //print_r($links);
        //exit('////////////////////');

        $crawler = $this->client->click($link);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Encounter Info")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Entry")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("History/Findings")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Laboratory Values")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Laboratory Values of Interest")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Search aides and time tracking")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Amount of Time Spent in Minutes")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Submitter role(s) at submission time")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Edit Entry")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("View Event Log")')->count()
        );

        //Test edit
//        $link = $crawler
//            ->filter('a:contains("Edit Entry")') // find all links with the text "Greet"
//            ->eq(0) // select the second link in the list
//            ->link()
//        ;
        $link = $crawler->selectLink('Edit Entry')->link();
        $crawler = $this->client->click($link);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Encounter Info")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Referring Provider")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Encounter Info")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Encounter\'s Location")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Update Patient Info")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Patient List")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Tasks")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Search aides and time tracking")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Save Draft")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Submitter role(s) at submission time:")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Finalize and Sign")')->count()
        );
    }

    public function testEventLogAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/call-log-book/event-log/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Event Log showing")')->count()
        );
    }

    public function testAboutAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/call-log-book/about');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Current Version")')->count()
        );
        //$linkName = '/translational-research/about';
        //$this->testGetLink($linkName,"Current Version");
        //$this->testGetLink($linkName);
    }

    public function testComplexPatientsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/call-log-book/patient-list/15/pathology-call-complex-patients');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Pathology Call Complex Patients")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add Patient")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Manage Patient Lists")')->count()
        );
    }

    public function testNewEntryAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/call-log-book/entry/new');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("New Entry")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Patient Info")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("No single patient is referenced by this entry or ")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Find Patient")')->count()
        );
    }

    public function testSiteSettingsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/call-log-book/settings/');
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

    public function testResourcesAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/call-log-book/resources/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Resources")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("[Edit]")')->count()
        );
    }

    public function testResourcesEditAction() {
        $this->logIn();
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/call-log-book/settings/edit-resources/');
        //$crawler = $this->client->request('GET', '/call-log-book/settings/1/edit?param=calllogResources');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Update Site Settings: calllogResources")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Call Log Book Resources:")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Update")')->count()
        );
    }

    public function testAuthorizedUsersAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/call-log-book/authorized-users/');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Authorized Users for Call Log Book")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add Authorized User")')->count()
        );
    }

    public function testProfileAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/call-log-book/user/1');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("View Details")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Preferred Contact Info")')->count()
        );
    }

    public function testTodoTasksAction() {
        $this->logIn();
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/call-log-book/tasks/to-do');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Call Case List")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("toggleBtnListener")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("navigation")')->count()
        );
    }

    //getNextEncounterGeneratedId
    //getPatientList
    //getDefaultPatientLists
    //getReferringProvidersWithUserWrappers
    //getTotalTimeSpentMinutes
    //getDefaultMessageCategory
    //getDefaultMrnType
    public function testUtilMethods() {
        $this->logIn();

        $calllogUtil = $this->container->get('calllog_util');

        $nextId = $calllogUtil->getNextEncounterGeneratedId();
        //AUTOGENERATEDENCOUNTERID-0000000000401 (Auto-generated Encounter Number)
        $this->assertStringContainsStringIgnoringCase("AUTOGENERATEDENCOUNTERID-",$nextId);

        $resList = $calllogUtil->getPatientList();
        $this->assertGreaterThan(0, count($resList));

        $patientLists = $calllogUtil->getDefaultPatientLists();
        $this->assertGreaterThan(0, count($patientLists));

        $providers = $calllogUtil->getReferringProvidersWithUserWrappers();
        $this->assertGreaterThan(0, count($providers));

        //$msg = $calllogUtil->getTotalTimeSpentMinutes();
        //$this->assertStringContainsStringIgnoringCase("During the current week", $msg);

        $messageCategory = $calllogUtil->getDefaultMessageCategory();
        $this->assertGreaterThan(0, $messageCategory->getId());

        $keytypemrn = $calllogUtil->getDefaultMrnType();
        $this->assertGreaterThan(0, $keytypemrn->getId());
    }

}
