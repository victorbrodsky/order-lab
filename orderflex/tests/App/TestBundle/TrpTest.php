<?php

namespace Tests\App\TestBundle;

use Tests\App\TestBundle\WebTestBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

//./bin/simple-phpunit tests/App/TranslationalResearchBundle/Controller/TranslationalResearchControllerTest.php

class TrpTest extends WebTestBase
{

    public function testHomeAction() {

        $phpVersion = phpversion();
        echo "[Trp,PHP=".$phpVersion."]";

        //$this->getTestClient();
        $crawler = $this->client->request('GET', '/translational-research/login');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Please use your CWID to log in")')->count()
        );
    }

    public function testProjectAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/translational-research/projects/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Project Requests")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("New Project Request")')->count()
        );

        //link Review Project if exists
        if( $crawler->filter('html:contains("Review Project")')->count() > 0 ) {
            $link = $crawler->selectLink('Review Project')->link();
            $crawler = $this->client->click($link);

            //$content = $this->client->getResponse()->getContent();
            //exit("content=$content");

            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Review Project request ")')->count()
            );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Add Comment Without Changing Status")')->count()
//        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Type your comment here...")')->count()
//        );
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Approve Project Request")')->count() +
                $crawler->filter('html:contains("Recommend Approval")')->count()
            );
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Reject Project Request")')->count() +
                $crawler->filter('html:contains("Recommend Rejection")')->count()
            );
        }
    }

    public function testShowProjectApplication() {
        //return;

        $this->logIn();

        $projects = $this->em->getRepository('AppTranslationalResearchBundle:Project')->findAll();
        //$transresUtil = $this->container->get('transres_util');
        //$projects = $transresUtil->getAvailableProjects();

        if( count($projects) > 0 ) {
            $project = end($projects);
            $projectId = $project->getId();
        } else {
            echo "Skip testShowProjectApplication; There are no available projects found";
            return null;
        }

        //Test Show
        //[2019-12-12 21:43:17] request.CRITICAL: Uncaught PHP Exception Twig\Error\RuntimeError:
        // "Impossible to access an attribute ("id") on a null variable in "AppUserdirectoryBundle::Default/usermacros.html.twig"."
        // at C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\scanorder\Scanorders2\vendor\twig\twig\src\Template.php line 581
        // {"exception":"[object] (Twig\\Error\\RuntimeError(code: 0): Impossible to access an attribute (\"id\") on a null variable
        // in \"AppUserdirectoryBundle::Default/usermacros.html.twig\". at
        // C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\vendor\\twig\\twig\\src\\Template.php:581)"} []

        $crawler = $this->client->request('GET', '/translational-research/project/show/'.$projectId);
        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Edit Project Request")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Principal Investigator(s) for the project:")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Tissue Request Details")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Edit Project Request")')->count()
        );


        //Test Edit
        $crawler = $this->client->request('GET', '/translational-research/project/edit/'.$projectId);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Please make sure to update your Project Request prior leaving this page.")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Principal Investigator(s) for the project")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Tissue Request Details")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Save Changes")')->count()
        );
    }

    public function testShowRequestApplication() {
        //return;

        $this->logIn();

        $transresUtil = $this->testContainer->get('transres_util');
        $requests = $transresUtil->getTotalRequests();
        if( count($requests) > 0 ) {
            $transRequest = end($requests);
            $requestId = $transRequest->getId();
        } else {
            echo "Skip testShowRequestApplication; There are no available requests found";
            return null;
        }

        //Test Show
        $crawler = $this->client->request('GET', '/translational-research/work-request/show/'.$requestId);
        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Work Request")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Request Details")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Product or Service")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Edit work request")')->count()
        );


        //Test Edit
        $crawler = $this->client->request('GET', '/translational-research/work-request/edit/'.$requestId);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Edit Work Request")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Product or Service")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Work Request Info")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Update Changes")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Associated Project Info")')->count()
        );
    }

    public function testShowInvoiceApplication() {
        //return;

        $this->logIn();

        $invoices = $this->em->getRepository('AppTranslationalResearchBundle:Invoice')->findAll();

        if( count($invoices) > 0 ) {
            $invoice = end($invoices);
            $invoiceId = $invoice->getOId();
            //echo "invoiceId=$invoiceId";
        } else {
            echo "Skip testShowInvoiceApplication; There are no available invoices found";
            return null;
        }

        //Test Show
        $crawler = $this->client->request('GET', '/translational-research/invoice/show/'.$invoiceId);
        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Invoice ID")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Show associated invoices for this work request")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Invoice Items")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Invoice in PDF(s)")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Edit invoice")')->count()
        );

        //link Edit invoice
        $link = $crawler->selectLink('Edit invoice')->link();
        $crawler = $this->client->click($link);


        //Test Edit
        //$crawler = $this->client->request('GET', '/translational-research/invoice/edit/'.$invoiceId);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Invoice ID ")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Show associated invoices for this work request")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Invoice Items")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Invoice in PDF(s)")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Update and Regenerate PDF Invoice")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Cancel")')->count()
        );
    }

    public function testInvoicePdfSlip() {
        $this->logIn();

        $invoices = $this->em->getRepository('AppTranslationalResearchBundle:Invoice')->findAll();
        if( count($invoices) > 0 ) {

            $invoice = end($invoices);
            $invoiceId = $invoice->getId();

            $crawler = $this->client->request('GET', '/translational-research/invoice/download-invoice-pdf/'.$invoiceId);

            //$content = $this->client->getResponse()->getContent();
            //exit("content=$content");

            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Invoice")')->count()
            );
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Bill To:")')->count()
            );
            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Bill From:")')->count()
            );
//            $this->assertGreaterThan(
//                0,
//                $crawler->filter('html:contains("Detach and return with payment")')->count()
//            );
        } else {
            echo "Skip testInvoicePdfSlip, invoices not found";
        }
    }

    public function testDefaultReviewersAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/translational-research/default-reviewers/ap-cp');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Default Reviewers for AP/CP")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Default Reviewers for AP/CP Final Review")')->count()
        );
    }

    public function testFeeScheduleAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/translational-research/request/fee-schedule');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Fee Schedule")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Histology")')->count()
        );
    }

    public function testAntibodiesAction() {
        $this->logIn();

        //https://github.com/KnpLabs/knp-components/issues/90
        //request.CRITICAL: Uncaught PHP Exception UnexpectedValueException: "There is no component aliased by [list] in the given Query" at OrderByWalker.php line 54
        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/translational-research/list/antibodies/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Antibodies")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Alternative Name")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Create a new entry")')->count()
        );
    }

    //Translational Research Dashboard
    public function testDashboardAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/translational-research/dashboard/graphs/?filter[startDate]=12/12/2018&filter[endDate]=12/12/2019&filter[projectSpecialty][]=0');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Translational Research Dashboard")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Products/Services")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Filter")')->count()
        );

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");
    }

    //http://127.0.0.1/order/translational-research/dashboard/graphs/?filter%5BstartDate%5D=12/12/2018&filter%5BendDate%5D=12/12/2019&filter%5BprojectSpecialty%5D%5B%5D=0&filter%5BchartType%5D%5B0%5D=compare-projectspecialty-pis&filter%5BchartType%5D%5B1%5D=compare-projectspecialty-projects&filter%5BchartType%5D%5B2%5D=compare-projectspecialty-projects-stack&filter%5BchartType%5D%5B3%5D=compare-projectspecialty-requests&filter%5BchartType%5D%5B4%5D=compare-projectspecialty-invoices
    public function testDashboardComparasionAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/translational-research/dashboard/graphs/?filter[startDate]=12/12/2018&filter[endDate]=12/12/2019&filter[projectSpecialty][]=0&filter[chartType][0]=compare-projectspecialty-pis&filter[chartType][1]=compare-projectspecialty-projects&filter[chartType][2]=compare-projectspecialty-projects-stack&filter[chartType][3]=compare-projectspecialty-requests&filter[chartType][4]=compare-projectspecialty-invoices');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Translational Research Dashboard")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Total Number of")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Total Number of Projects per PI (Top) (linked)")')->count()
        );

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");
    }

    public function testUnpaidInvoiceReminderAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/translational-research/unpaid-invoice-reminder/show-summary');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("invoices have remained unpaid.")')->count()
        );
    }

    public function testEventLogAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/translational-research/event-log/');

        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Event Log showing")')->count()
        );
    }

    public function testPackingSlip() {
        $this->logIn();

        $transresUtil = $this->testContainer->get('transres_util');
        $requests = $transresUtil->getTotalRequests();
        if( count($requests) > 0 ) {

            $transRequest = end($requests);
            $requestId = $transRequest->getId();

            //TODO: check if request has a packing slip PDF

            $crawler = $this->client->request('GET', '/translational-research/work-request/download-packing-slip-pdf/'.$requestId);

            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Packing Slip")')->count()
            );


//            //Generate Packing Slip
//            $authorUser = $this->user;
//            $request = $this->client->getRequest();
//            $transresPdfUtil = $this->container->get('transres_pdf_generator');
//            $res = $transresPdfUtil->generatePackingSlipPdf($transRequest,$authorUser,$request);
//            $size = $res['size'];
//            // assert that size is greater than zero
//            $this->assertGreaterThan(100, $size);

        } else {
            echo "Skip testPackingSlip, work requests not found";
        }
    }



    public function testAboutAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/translational-research/about');
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

    public function testNewProjectAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/translational-research/project/new/ap-cp');
        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("AP/CP Project Request")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Save Draft Project Request")')->count()
        );
    }
//    public function testNewCovid19ProjectAction() {
//        $this->logIn();
//        $crawler = $this->client->request('GET', '/translational-research/project/new/covid19');
//        //$content = $this->client->getResponse()->getContent();
//        //exit("content=$content");
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("COVID-19 Project Request")')->count()
//        );
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Save Draft Project Request")')->count()
//        );
//    }
    public function testNewMisiProjectAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/translational-research/project/new/misi');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Multiparametric In Situ Imaging Project Request")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Save Draft Project Request")')->count()
        );
    }

    public function testRequestAction() {
        $this->logIn();

        unset($_GET['sort']);
        $crawler = $this->client->request('GET', '/translational-research/work-requests/list/?filter[progressState][0]=All-except-Drafts-and-Canceled&title=All Work Requests');
        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("All Work Requests")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Products/Services")')->count()
        );
    }

    public function testNewRequestAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/translational-research/work-request/new/');
        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("New Work Request")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Save Work Request as Draft")')->count()
        );
    }

    public function testNewInvoiceAction() {
        $this->logIn();
        //$this->client->followRedirects();

        $transresUtil = $this->testContainer->get('transres_util');
        $requests = $transresUtil->getTotalRequests();
        if( count($requests) > 0 ) {
            $request = end($requests);
            $requestId = $request->getId();
            //echo "requestID=$requestId \n\r";

            $crawler = $this->client->request('GET', '/translational-research/invoice/new/'.$requestId);
            //$content = $this->client->getResponse()->getContent();
            //exit("content=$content");

            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("New Invoice for the Request")')->count()
            );

            $this->assertGreaterThan(
                0,
                $crawler->filter('html:contains("Show associated invoices for the same work request")')->count()
            );
        } else {
            echo "Skip testNewInvoiceAction, work requests not found";
        }
    }

    public function testIndexQueuesAction() {
        $this->logIn();
        $crawler = $this->client->request('GET', '/translational-research/orderables/');
        //$content = $this->client->getResponse()->getContent();
        //exit("content=$content");
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Work Queues")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Matching")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Requested Quantity")')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Product or Service")')->count()
        );
    }

//    public function testUnderConstruction() {
//        //under-construction
//        $this->logIn();
//        //$client = static::createClient();
//
//        //http://localhost/order/directory/login
//        //$crawler = $this->client->request('GET', '/translational-research/login');
//        $crawler = $this->client->request('GET', '/order/directory/under-construction');
//
//        $uri = $this->client->getRequest()->getUri();
//        echo "under-construction uri=$uri \r\n";
//        //exit("uri=$uri");
//
//        //$content = $this->client->getResponse()->getContent();
//        //exit("content=$content");
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Currently Undergoing Maintenance")')->count()
//        //$crawler->filter('html:contains("The following sites are available")')->count()
//        //$crawler->filter('html:contains("Please use your")')->count()
//        );
//
//        exit("exit under-construction");
//    }

//    public function testLoginProcess()
//    {
//        return;
//
//        $client = static::createClient();
//        $client->followRedirects();
//
//        //$cookie = new Cookie('locale2', 'en', time() + 3600 * 24 * 7, '/', null, false, false);
//        //$client->getCookieJar()->set($cookie);
//
//        $_SERVER['HTTP_USER_AGENT'] = 'phpunit test';
//
//        // Visit user login page and login
//        $crawler = $client->request('GET', '/order/directory/login');
//
//        echo "\n\n\nclient response:\n\n\n";
//        //echo $crawler->html();
//        //var_dump($crawler->links());
//        print_r($client->getResponse()->getContent());
//        echo "\n\n\n";
//        exit('Exit on login page');
//
//        $uri = $client->getRequest()->getUri();
//        echo "login uri=$uri \r\n";
//        //exit('000 crawler');
//        //test if login page is opened
//        //$this->assertTrue($client->getResponse()->isSuccessful());
//        //exit('000 assertTrue');
//        // Select based on button value, or id or name for buttons
//        $form = $crawler->selectButton('Log In')->form();
//
//        // set some values
//        $form['_username'] = 'username';
//        $form['_password'] = 'pa$$word';
//
//        //$form['_username'] = '';
//        // $form['_password'] = '';
//
//        //$client->insulate();
//
//        // submit the form
//        $crawler = $client->submit($form);
//
//        //$this->assertTrue($client->getResponse()->isSuccessful());
//        exit('000');
//        echo "\n\n\nclient response:\n\n\n";
//        //echo $crawler->html();
//        //var_dump($crawler->links());
//        print_r($client->getResponse()->getContent());
//        echo "\n\n\n";
//        exit('111');
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Welcome to the Scan Order System")')->count()
//        );
//
//        $crawler = $client->request('GET', '/order/directory/');
//
//        $this->assertTrue($client->getResponse()->isSuccessful());
//
//
//        //$this->assertEquals('Hello', 'Hello');
//
////        echo "client response:<br>";
////        var_dump($client->getResponse()->getContent());
////        echo "<br>";
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Welcome to the Employee Directory!")')->count()
//        );
//
//
//        //test form submit
//        $crawler = $client->request('GET', '/order/directory/new');
//
////        echo "client response:<br>";
////        var_dump($client->getResponse()->getContent());
////        echo "<br>";
//
//        $this->assertTrue($client->getResponse()->isSuccessful());
//
//        $this->assertGreaterThan(
//            0,
//            $crawler->filter('html:contains("Create New User")')->count()
//        );
//
//
////        //$next = $crawler2->selectButton('Next')->link();
////        //$next = $crawler->filter('button:contains("Next")')->eq(1)->link();
////        //$crawler2 = $client->click($next);
////
////        $form = $crawler->selectButton('btnSubmit')->form();
////
////        $form['oleg_orderformbundle_messagetype[patient][0][encounter][0][procedure][0][accession][0][part][0][block][0][slide][0][title]'] = 'Slide submitted by phpunit test';
////
////        $form['oleg_orderformbundle_messagetype[patient][0][encounter][0][procedure][0][accession][0][part][0][block][0][slide][0][slidetype]'] = 7;
////
////        $form['oleg_orderformbundle_messagetype[patient][0][clinicalHistory][0][field]'] = 'clinical history test';
////
////        $form['oleg_orderformbundle_messagetype[patient][0][mrn][0][field]'] = '0000000';
////
////
////
////        $_POST['btnSubmit'] = "btnSubmit";
////
////        //sleep(10);
////
////        $crawler = $client->submit($form);
////
//////        echo "client response:<br>";
//////        var_dump($client->getResponse()->getContent());
//////        echo "<br>";
////        //exit();
////
////        $this->assertTrue($client->getResponse()->isSuccessful());
////
////        $this->assertGreaterThan(
////            0,
////            $crawler->filter('html:contains("Thank you for your order")')->count()
////        );
//    }







}
