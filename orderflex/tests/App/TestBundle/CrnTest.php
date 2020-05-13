<?php

namespace Tests\App\TestBundle;

use Tests\App\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class CrnTest extends WebTestBase
{

    public function testLoginPageAction() {

        $phpVersion = phpversion();
        echo "[Crn,PHP=".$phpVersion."]";
        
        //$this->getTestClient();
        $crawler = $this->client->request('GET', '/critical-result-notifications/login');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Critical Result Notifications")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Log In")')->count()
        );
    }

    public function testAboutAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/critical-result-notifications/about');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

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

    public function testFormNodeValue() {

        if( $this->environment == "nodata" ) {
            echo "nodata";
            return;
        }

        $this->logIn();

        $mapper = array(
            'entityName' => 'Message',
            'entityNamespace' => 'App\OrderformBundle\Entity',
        );

        //get non empty formnode and use getFormNodeValueByFormnodeAndReceivingmapper to find value
        $treeRepository = $this->em->getRepository("AppUserdirectoryBundle:ObjectTypeText");
        $dql =  $treeRepository->createQueryBuilder("list");
        $dql->select('list');
        $dql->where("list.entityName = :entityName AND list.entityNamespace = :entityNamespace");
        $dql->andWhere("list.value IS NOT NULL AND list.value <> ''");
        $dql->andWhere('list.entityId IS NOT NULL');
        $dql->andWhere('list.formNode IS NOT NULL');

        $query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";

        $query->setParameters(
            array(
                'entityName' => $mapper['entityName'],
                'entityNamespace' => $mapper['entityNamespace'],
            )
        );

        $objectTypeTexts = $query->getResult();

        if( count($objectTypeTexts) > 0 ) {
            $objectTypeText = end($objectTypeTexts);
            $formNode = $objectTypeText->getFormNode();

            if( !$formNode ) {
                $this->assertTrue(false,"formNode not found");
            }

            $mapper = array(
                'entityName' => 'Message',
                'entityNamespace' => 'App\OrderformBundle\Entity',
                'entityId' => $objectTypeText->getEntityId()
            );

            $originalFormnodeValue = $objectTypeText->getValue();
            $originalFormnodeValue = trim($originalFormnodeValue);
            $this->assertNotEmpty(
                $originalFormnodeValue,
                "Original formNode value is empty: 
                objectTypeTextId=".$objectTypeText->getId().
                "; formNode=".$formNode
            );

            $formNodeUtil = $this->testContainer->get('user_formnode_utility');
            $complexRes = $formNodeUtil->getFormNodeValueByFormnodeAndReceivingmapper($formNode,$mapper);
            if( $complexRes ) {
                $formNodeValue = $complexRes['formNodeValue'];
                //$receivingEntity = $complexRes['receivingEntity'];
                $formNodeValue = trim($formNodeValue);
                $this->assertNotEmpty($formNodeValue,"formNodeValue is empty");

                //echo "formNode Values compare: [$formNodeValue] != [$originalFormnodeValue]";
                $this->assertEquals($formNodeValue, $originalFormnodeValue, "formNode Values are not the same [$formNodeValue] != [$originalFormnodeValue]");

            } else {
                $this->assertTrue(false,"getFormNodeValueByFormnodeAndReceivingmapper complexRes not found");
            }

        } else {
            $this->assertTrue(false,"ObjectTypeText not found");
        }
    }

    public function testHomeAction() {
        $this->logIn();

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/critical-result-notifications/');
        //$crawler = $this->client->request('GET', '/critical-result-notifications/?filter[messageStatus]=All except deleted&filter[messageCategory]=CRN Log Entry_32&filter[mrntype]=1');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Critical Result Notification Case List")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Patient Name")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("MRN")')->count()
        );


        if( $this->environment == "nodata" ) {
            echo "nodata";
            return;
        }

        //Test view data
        //TODO: check DB if CRN entries are existed.

        $crnUtil = $this->testContainer->get('crn_util');
        $messages = $crnUtil->getUndeletedMessages();
        //echo "[CRNs=".count($messages)."]";

        if( count($messages) == 0 ) {
            echo "[CRNs=".count($messages)."=>Stop testHomeAction]";
            return;
        }

        if( count($messages) >= 10 ) {
            $entriesCount = 10;
        } else {
            $entriesCount = count($messages);
        }

        //condition for assertGreaterThan 0
        $entriesCount = $entriesCount - 1;
        //echo "[entriesCount=".$entriesCount."]";

        $links = $crawler->filter('.crn_entry_view_link');

        $this->assertGreaterThan(
            $entriesCount, //we have 10 entries per page
            $links->count()
        );

        $links = $crawler->filter('.crn-patient-name');
        $this->assertGreaterThan(
            $entriesCount, //we have 10 entries per page
            $links->count()
        );

//        $links = $crawler->filter('.formnode-field-notempty-value');
//        $this->assertGreaterThan(
//            10, //19, //we should have 2 (history, outcome) per entry entries per page
//            $links->count()
//        );

//        $newCrawler = $crawler->filter('tbody.table-tbody-hover:contains("History/Findings")')
//            ->last()
//            ->parents()
//            ->first()
//        ;
//        exit('');

//        $crawler->filter('tbody.table-tbody-hover:contains("History/Findings")')->each(function ($node, $i) {
//            //return $node->attr('href');
//            //var_dump($node);
//            //$nodeThis = $node->filter('.formnode-field-notempty-value > p')->eq($i);
//            var_dump($node);
//            $this->assertGreaterThan(
//                0,
//                $node->filter('html:contains("formnode-field-notempty-value")')->count()
//            );
//            //exit('000');
//        });
//        exit('111');

//        $tbodys = $crawler->filter('tbody.table-tbody-hover:contains("History/Findings")');
//        echo "tbodys count=".count($tbodys)."<br>";
//        foreach( $tbodys as $tbody ) {
//            $tbody->filter('.formnode-field-notempty-value');
//            var_dump($tbody);
//            //exit('exit111');
//        }
//        exit('exit333');

        $records = $crawler->filter('.crn-patient-name');
        //echo "records count=".count($records)."<br>";
        foreach( $records as $record ) {
            //var_dump($record);
            //$href = $record; //->getContent();
            //echo "records=".$record->getAttribute("nodeValue")."";
            //$xmlAuthorID = $record->getElementsByTagName( "AuthorID" );
            $value = $record->nodeValue;
            //echo "records=".trim($value)."";
            //exit('exit111');
            $this->assertNotEmpty($value,"Patient name is empty");
        }
        //exit('exit222');

        //formnode-field-notempty-value
        $records = $crawler->filter('.formnode-field-notempty-value');
        //echo "records count=".count($records)."<br>";
        foreach( $records as $record ) {
            //var_dump($record);
            //$href = $record; //->getContent();
            //echo "records=".$record->getAttribute("nodeValue")."";
            //$xmlAuthorID = $record->getElementsByTagName( "AuthorID" );
            $value = $record->textContent;
            //echo "records=".trim($value)."";
            //exit('exit111');
            $this->assertNotEmpty($value,"Field value is empty");
        }

        //return;

        $link = $crawler
            //->filter('a:contains("/order/critical-result-notifications/entry/view/")') // find all links with the text "Greet"
            //->filter('.crn_entry_view_link')
            ->filter('.crn_entry_view_link')
            ->eq(0) // select the first link in the list
            ->link()
        ;

//        $link2 = $crawler
//            //->filter('a:contains("/order/critical-result-notifications/entry/view/")') // find all links with the text "Greet"
//            //->filter('.crn_entry_view_link')
//            ->filter('.crn_entry_view_link')
//            ->eq(1) // select the first link in the list
//            ->link()
//        ;
//        $crawler2 = $this->client->click($link2);
//        //formnode-field-notempty-value
//        $records = $crawler2->filter('.formnode-field-notempty-value');
//        echo "records count=".count($records)."<br>";
//        foreach( $records as $record ) {
//            //var_dump($record);
//            $value = $record->textContent;
//            echo "records=".trim($value)."";
//            $this->assertNotEmpty($value,"Field value is empty");
//        }
//        exit('exit222');
        //$links = $crawler->filter('a:contains("/order/critical-result-notifications/entry/view/")');
        //print_r($links);
        //exit('////////////////////');

        //echo "crnLink=[$link]";
        $crawler = $this->client->click($link);

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Encounter Info")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Entry")')->count()
        );

//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("crn-current-message-id")')->count()
//        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("patient-holder-1")')->count()
//        );

//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("History/Findings")')->count()
//        );
        //TODO: add assert to make sure that td > p > has text
//        $count = $crawler->filter('td:contains("crn-patient-name")')->count();
//        exit('count='.$count);
//        $record = $crawler->filter('.crn-patient-name > i')->eq(0)->link();
//        echo "record=".$record."<br>";
//        exit('000');
//        $trRecords = $crawler->filter('tbody');
//        echo "records count=".count($trRecords)."<br>";
//        exit('000');
//        $records = $crawler->filter('.crn-patient-name');
//        echo "records count=".count($records)."<br>";
//        foreach( $records as $record ) {
//            $href = $record->filter('.crn-patient-name > href');
//            echo "href=$href";
//            exit('exit111');
//        }
//        exit('exit222');
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Laboratory Values")')->count()
//        );
        //This is a special, non-common test case
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Laboratory Values of Interest")')->count()
//        );
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
            $crawler->filter('html:contains("Edit Entry")')->count() + $crawler->filter('html:contains("Amend Entry")')->count() //Amend or Edit
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("View Event Log")')->count()
        );

//        //formnode-field-notempty-value
//        $records = $crawler->filter('.formnode-field-notempty-value');
//        //echo "records count=".count($records)."<br>";
//        foreach( $records as $record ) {
//            //var_dump($record);
//            $value = $record->textContent;
//            //echo "records=".trim($value)."";
//            $this->assertNotEmpty($value,"Field value is empty");
//        }
//        $crawler->filter('tbody.table-tbody-hover:contains("History/Findings")')->each(function ($node, $i) {
//            //return $node->attr('href');
//            //var_dump($node);
//            //$nodeThis = $node->filter('.formnode-field-notempty-value > p')->eq($i);
//            //var_dump($node);
//            $this->assertGreaterThan(
//                0,
//                $node->filter('html:contains("formnode-field-notempty-value")')->count()
//            );
//            //exit('000');
//        });
        //exit('111');
        //exit('exit222');

        //Test edit
//        $link = $crawler
//            ->filter('a:contains("Edit Entry")') // find all links with the text "Greet"
//            ->eq(0) // select the second link in the list
//            ->link()
//        ;

        if( $crawler->filter('html:contains("Edit Entry")')->count() > 0 ) {
            $editAmendLink = "Edit Entry";
        }
        if( $crawler->filter('html:contains("Amend Entry")')->count() > 0 ) {
            $editAmendLink = "Amend Entry";
        }
        $link = $crawler->selectLink($editAmendLink)->link();
        $crawler = $this->client->click($link);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Encounter Info")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Healthcare Provider")')->count()
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
            $crawler->filter('html:contains("List")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add patient to the list")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add accession to the list")')->count()
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

        $rolesCount = $crawler->filter('html:contains("Submitter role(s) at submission time:")')->count()
            + $crawler->filter('html:contains("Signee role(s) at signature time:")')->count();
        $this->assertGreaterThan(
            0,
            $rolesCount
        );

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Finalize and Sign")')->count()
        );
    }

    public function testEventLogAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/critical-result-notifications/event-log/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Event Log showing")')->count()
        );
    }

    //TODO:
//    public function testComplexPatientsAction() {
//        $this->logIn();
//        $crawler = $this->client->request('GET', '/critical-result-notifications/patient-list/15/pathology-call-complex-patients');
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Recent Patients")')->count()
//        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Add Patient")')->count()
//        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Manage Patient Lists")')->count()
//        );
//    }

    public function testRecentPatientsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/critical-result-notifications/recent-patients');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Recent Patients")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Actions")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Last Name")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("First Name")')->count()
        );
    }

    public function testNewEntryAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/critical-result-notifications/entry/new');
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
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("History/Findings")')->count()
//        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Impression/Outcome")')->count()
//        );
    }

    public function testSiteSettingsAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/critical-result-notifications/settings/');
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
        $crawler = $this->client->request('GET', '/critical-result-notifications/resources/');

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
        $crawler = $this->client->request('GET', '/critical-result-notifications/settings/edit-resources/show');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Update Critical Result Notification Resources")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Description:")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Update")')->count()
        );
    }

    public function testAuthorizedUsersAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/critical-result-notifications/authorized-users/');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Authorized Users for Critical Result Notification")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Add Authorized User")')->count()
        );
    }

    public function testProfileAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/critical-result-notifications/user/1');
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
        $crawler = $this->client->request('GET', '/critical-result-notifications/tasks/to-do');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Critical Result Notification Case List")')->count()
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

    public function testDataEditPatientInfoAction() {
        $this->logIn();
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/critical-result-notifications/find-and-edit-patient-record');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Edit Patient Info")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Find Patient")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("MRN Type")')->count()
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

        $crnUtil = $this->testContainer->get('crn_util');

        $nextId = $crnUtil->getNextEncounterGeneratedId();
        //AUTOGENERATEDENCOUNTERID-0000000000401 (Auto-generated Encounter Number)
        $this->assertStringContainsStringIgnoringCase("AUTOGENERATEDENCOUNTERID-",$nextId);

        $resList = $crnUtil->getPatientList();
        $this->assertGreaterThan(0, count($resList));

        $patientLists = $crnUtil->getDefaultPatientLists();
        $this->assertGreaterThan(0, count($patientLists));

        $providers = $crnUtil->getReferringProvidersWithUserWrappers();
        $this->assertGreaterThan(0, count($providers));

        //$msg = $crnUtil->getTotalTimeSpentMinutes();
        //$this->assertStringContainsStringIgnoringCase("During the current week", $msg);

        $messageCategory = $crnUtil->getDefaultMessageCategory();
        $this->assertGreaterThan(0, $messageCategory->getId());

        $keytypemrn = $crnUtil->getDefaultMrnType();
        $this->assertGreaterThan(0, $keytypemrn->getId());
    }



}
