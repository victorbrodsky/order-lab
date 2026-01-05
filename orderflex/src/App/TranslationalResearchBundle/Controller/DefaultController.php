<?php

namespace App\TranslationalResearchBundle\Controller;


use App\TranslationalResearchBundle\Entity\AntibodyCategoryTagList;
use App\TranslationalResearchBundle\Entity\AntibodyLabList;
use App\TranslationalResearchBundle\Entity\AntibodyList;
use App\UserdirectoryBundle\Entity\FosComment; //process.py script: replaced namespace by ::class: added use line for classname=FosComment
use App\TranslationalResearchBundle\Entity\SpecialtyList; //process.py script: replaced namespace by ::class: added use line for classname=SpecialtyList
use App\TranslationalResearchBundle\Entity\Invoice; //process.py script: replaced namespace by ::class: added use line for classname=Invoice
use App\TranslationalResearchBundle\Entity\PriceTypeList; //process.py script: replaced namespace by ::class: added use line for classname=PriceTypeList
use App\TranslationalResearchBundle\Entity\Project; //process.py script: replaced namespace by ::class: added use line for classname=Project
use App\TranslationalResearchBundle\Entity\RequestCategoryTypeList; //process.py script: replaced namespace by ::class: added use line for classname=RequestCategoryTypeList
use App\TranslationalResearchBundle\Entity\OrderableStatusList; //process.py script: replaced namespace by ::class: added use line for classname=OrderableStatusList
use App\TranslationalResearchBundle\Entity\Product;
use App\TranslationalResearchBundle\Entity\TransResRequest;
//use App\UserdirectoryBundle\Entity\Grant;
//use App\UserdirectoryBundle\Entity\SiteList;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Util\LargeFileDownloader;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DefaultController extends OrderAbstractController
{

    #[Route(path: '/about', name: 'translationalresearch_about_page', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function aboutAction( Request $request ) {

        //$em = $this->getDoctrine()->getManager();
        //$transresRequestUtil = $this->container->get('transres_request_util');
        //$transresUtil = $this->container->get('transres_util');

        //$userServiceUtil = $this->container->get('user_service_utility');
        //$userServiceUtil->browserCheck();

        //test
        //$test = $this->getDummyClass();
        //$test = new DummyClass();

        //Work Request ID APCP756-REQ17009(FROM Original State 'completedNotified'): 'progress' (TO) transition not found by name pendingHistology_completedNotified with code0
//        $transresRequestUtil = $this->container->get('transres_request_util');
//        $transitionName = "pendingHistology_completedNotified";
//        $statMachineType = "progress";
//        $em = $this->getDoctrine()->getManager();
//        $transresRequest = $em->getRepository('AppTranslationalResearchBundle:TransResRequest')->find(756);
//        echo "current state=".$transresRequest->getProgressState()."<br>";
//        $transition = $transresRequestUtil->getTransitionByName($transresRequest,$transitionName,$statMachineType);
//        if( !$transition ) {
//            exit($statMachineType.": Not found by transitionName=".$transitionName);
//        } else {
//            exit($statMachineType.": Found by transitionName=".$transitionName);
//        }

//        //testing isInvoiceAlreadySent($invoice)
//        $em = $this->getDoctrine()->getManager();
//        $invoice = $em->getRepository('AppTranslationalResearchBundle:Invoice')->findOneByOid('APCP3353-REQ20459-V28');
//        $transresRequestUtil = $this->container->get('transres_request_util');
//        if( $transresRequestUtil->isInvoiceAlreadySent($invoice) ) {
//            echo "invoice already sent<br>";
//        } else {
//            echo "invoice not sent<br>";
//        }

//        //testing $subTotal = $invoice->calculateSubTotal();
//        $em = $this->getDoctrine()->getManager();
//        $invoice = $em->getRepository('AppTranslationalResearchBundle:Invoice')->findOneByOid('APCP3353-REQ20459-V31');
//        $subTotal = $invoice->calculateSubTotal();
//        $invoice->setSubTotal($subTotal);
//        echo $invoice->getOid().": subTotal=$subTotal<br>";
//
//        $total = $invoice->calculateTotal();
//        $invoice->setTotal($total);
//        $invoice->setDue($total);
//        echo "total=$total<br>";
//
//        $transresRequestUtil = $this->container->get('transres_request_util');
//        $subsidy = $transresRequestUtil->updateInvoiceSubsidy($invoice);
//        echo "subsidy=$subsidy<br>";

        //testing transres ROLE hierarchy
        //$userSecUtil = $this->container->get('user_security_utility');
        //$userSecUtil->roleHierarchyTest();
        //exit("EOF role testing");

//        //testing review
//        $transresUtil = $this->container->get('transres_util');
//        $em = $this->getDoctrine()->getManager();
//        $user = $this->getUser();
//        echo "$user<br>";
//        $project = $em->getRepository('AppTranslationalResearchBundle:Project')->find(3359);
//        echo "project=".$project->getOid().":<br><br>";
//        if($transresUtil->isAdminOrPrimaryReviewer() ) {
//            echo "isAdminOrPrimaryReviewer<br>";
//        }
//        if($transresUtil->isProjectReviewer($project) ) {
//            echo "isProjectReviewer<br>";
//        }
//        if($transresUtil->isProjectStateReviewer($project,$user) ) {
//            echo "isProjectStateReviewer<br>";
//        }
//        exit("EOF review testing");

        //testing fee schedule->specialty filter
//        $specialtyId = 5; //MISI
//        $specialtyId = 7; //USCAP
//        $projectSpecialty = $em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->find($specialtyId);
//        echo "specialty = ID ".$projectSpecialty->getId()." $projectSpecialty <br><br>";
//        $fees = $transresRequestUtil->getProductServiceByProjectSpecialty($projectSpecialty);
//        //$fees = $transresRequestUtil->getProductServiceByProjectSpecialtyTest3($projectSpecialty);
//        echo "fees count=".count($fees)."<br>";
//        foreach($fees as $fee) {
//            echo $fee->getId()." fee = ".$fee->getProductId(). "; hide for=[" . $fee->getProjectSpecialtiesStr() . "]; ".$fee->getName()."<br>";
//        }
//        exit('111');

//        //testing getTransResAdminEmails
//        $project = $em->getRepository('AppTranslationalResearchBundle:Project')->find(3356);
//        $admins = $transresUtil->getTransResAdminEmails($project,true,true);
//        foreach($admins as $admin) {
//            echo "admin1=".$admin."<br>";
//        }
//        $admins = $transresUtil->getTransResAdminEmails(null,true,true);
//        foreach($admins as $admin) {
//            echo "admin2=".$admin."<br>";
//        }
//
//        if( $project ) {
//            $projectSpecialty = $project->getProjectSpecialty();
//        }
//        if( $projectSpecialty ) {
//            $specialtyPostfix = $projectSpecialty->getUppercaseName();
//            $specialtyPostfix = "_" . $specialtyPostfix;
//        } else {
//            $specialtyPostfix = null;
//        }
//        $specialtyPostfix = null;
//        $role = "ROLE_TRANSRES_ADMIN" . $specialtyPostfix;
//        echo "role=".$role." <br>";
//        $admins = $em->getRepository('AppUserdirectoryBundle:User')->findUsersByRoles(array($role));
//        echo "admins3=".count($admins)."<br>";

        //$transresUtil->addTransresRolesBySpecialtyWorkQueue();
        //exit('111');

//        //expiration date
        //$transresReminderUtil = $this->container->get('transres_reminder_util');
        //$transresReminderUtil->sendProjectExpirationReminder(true); //$testing=true
        //exit('111');

//        $allFees = $transresUtil->getFees();
//        echo "allFees=".count($allFees)."<br><br>";
//        $totalCount = 0;
//        $count = 0;
//        foreach($allFees as $fee) {
//            $exist = false;
//            $productId = $fee->getProductId();
//            if( $productId && strpos((string)$productId, 'TRP-') !== false ) {
//                $exist = true;
//            }
//            if( $productId && strpos((string)$productId, 'MISI-') !== false ) {
//                $exist = true;
//            }
//            if( !$exist ) {
//                $count++;
//                echo $count.": ".$fee->getShortInfo()."<br>";
//            }
//
//            $totalCount++;
//            echo $totalCount.": ".$fee->getShortInfo()."<br>";
//        }
//        echo "<br><br>";
        
        //$res = $transresUtil->syncFeeAndWorkQueue($testing=true); //$testing=true
        //exit($res);

        //$res = $transresUtil->getUserAssociatedSpecificPriceList();
        //dump($res);
        //exit('111');

        //Testing reminder email with attachments
        //$transresReminderUtil = $this->container->get('transres_reminder_util');
        //$res = $transresReminderUtil->sendReminderUnpaidInvoices(false,$testing=true);
        //exit('111');

//        if(0) {
//            $emailUtil = $this->container->get('user_mailer_utility');
//            $email = "oli2002@med.cornell.edu";
//            $invoice = NULL;
//            $userSecUtil = $this->container->get('user_security_utility');
//            $environment = $userSecUtil->getSiteSettingParameter('environment');
//            if ($environment == "dev") {
//                $invoice = $em->getRepository('AppTranslationalResearchBundle:Invoice')->find(4760); //dev
//            }
//            if ($environment == "test") {
//                $invoice = $em->getRepository('AppTranslationalResearchBundle:Invoice')->find(4730); //test
//            }
//            if ($environment == "live") {
//                $invoice = $em->getRepository('AppTranslationalResearchBundle:Invoice')->find(7323); //prod
//            }
//            if (!$invoice) {
//                exit("Invoice not defined for environment=$environment");
//            }
//            $invoicePDF = $invoice->getRecentPDF();
//            $attachmentPath = $invoicePDF->getAttachmentEmailPath();
//            $emailUtil->sendEmail($email, "Test Email Invoice", "Test Email Invoice", null, $email, $attachmentPath);
//            $res = $invoice->getId() . ": attachmentPath=$attachmentPath <br>";
//            exit($res);
//        }

        //$secTokenStorage = $this->container->get('security.token_storage');
        //$secTokenStorage = $this->container->get('security.token_storage');
        //$secTokenStorage = $this->container->get('security.context')->getToken();
        //$user = $secTokenStorage->getToken()->getUser();
        //exit('$user='.$user);

//        $emailUtil = $this->container->get('user_mailer_utility');
//        $subject = $body = 'Test email';
//        $emailRecipients = array('cinava@yahoo.com,',' cinava@yahoo.com ','oli2002@med.cornell.edu, ','oli2002@med.cornell.edu,');
//        $adminsCcs = 'oli2002@med.cornell.edu,cinava@yahoo.com, ,,';
//        $senderEmail = 'oli2002@med.cornell.edu';
//        $emailUtil->sendEmail( $emailRecipients, $subject, $body, $adminsCcs, $senderEmail );

        //$transresReminderUtil = $this->container->get('transres_reminder_util');
        //$project = $em->getRepository('AppTranslationalResearchBundle:Project')->find(3364);
        //$transresReminderUtil->autoCloseExpiredProject($project,true);

//        $projects = $em->getRepository('AppTranslationalResearchBundle:Project')->findAll();
//        $project = end($projects);
//        $projectUrl = $this->container->get('router')->generate(
//            'translationalresearch_project_show',
//            array(
//                'id' => $project->getId(),
//            ),
//            UrlGeneratorInterface::ABSOLUTE_URL
//        );
//        $projectUrl = '<a href="'.$projectUrl.'">'.$projectUrl.'</a>';
//        echo "projectUrl=$projectUrl <br>";

        //$transresUtil = $this->container->get('transres_util');
        //$transresUtil->feeFilterTest();
        //$fee = $em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList')->findOneById(5);
        //echo "fee=".$fee."<br>";
        //exit('111');

        //$project = $em->getRepository('AppTranslationalResearchBundle:Project')->find(3644); //live 3644
        //$resArr = $transresUtil->getTransResAdminEmails($project,false,true);
        //dump($resArr);
        //exit('111');

        //testing IDENTITY, AUTO
        //$user = $this->getUser();
//        $em = $this->getDoctrine()->getManager();
//        $product = new Product();
//        echo "product id (IDENTITY)=".$product->getId()."<br>";
//        $site = new SiteList();
//        $em->persist($site);
//        echo "site id (Auto)=".$site->getId()."<br>";
//        $grant = new Grant();
//        $em->persist($grant);
//        echo "grant id (Auto)=".$grant->getId()."<br>";
//        exit();
        //$orderinlist = $transresUtil->findNextProjectGoalOrderinlist(3566);
        //exit('$orderinlist='.$orderinlist);

//        $em = $this->getDoctrine()->getManager();
//        $transresUtil = $this->container->get('transres_util');
//        $user = $this->getUser();
//        $user = $em->getRepository(User::class)->find(7305);
//        echo "getProjectsAllowedByUser:". $user->getId() ."<br>";
//        //exit('111');
//        $projects = $transresUtil->getProjectsAllowedByUser($user);

//        $submitters = $transresUtil->getAllUserSubmittedProject();
//        echo "total submitters=".count($submitters)."<br>";
//        $count = 0;
//        foreach($submitters as $submitter) {
//            $count++;
//            echo $count.': '.$submitter."<br>";
//        }
//        exit('1');
        
        return array('sitename'=>$this->getParameter('translationalresearch.sitename'));
    }

    #[Route(path: '/thanks-for-downloading/{id}/{sitename}', name: 'translationalresearch_thankfordownloading', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Default/thanksfordownloading.html.twig')]
    public function thankfordownloadingAction(Request $request, $id, $sitename) {
        return array(
            'fileid' => $id,
            'sitename' => $sitename
        );
    }

    /**
     * Used in TrpTest->testSendInvoiceEmail()
     */
    #[Route(path: '/test-invoice-email', name: 'translationalresearch_test-invoice-email')]
    public function testInvoiceEmailAction( Request $request ) {

        $emailUtil = $this->container->get('user_mailer_utility');
        $res = $emailUtil->sendInvoiceTestEmail();
        //echo $res;

        $response = new Response();
        //$response->headers->set('Content-Type', 'application/json');
        $response->setContent($res);
        return $response;

        //return $res;
        //return array('sitename'=>$this->getParameter('translationalresearch.sitename'));
    }


//    /**
    //     * @Route("/", name="translationalresearch_home", methods={"GET"})
    //     * @Template("AppTranslationalResearchBundle/Default/index.html.twig")
    //     */
    //    public function indexAction( Request $request ) {
    //
    //        if( false == $this->container->get('security.context')->isGranted('ROLE_TRANSRES_USER') ){
    //            //exit('deidentifier: no permission');
    //            return $this->redirect( $this->generateUrl('translationalresearch-nopermission') );
    //        }
    //
    //        return $this->redirect( $this->generateUrl('translationalresearch_project_index') );
    //
    ////        return array(
    ////            'title' => "Translational Research"
    ////        );
    //    }
    #[Route(path: '/download/human-tissue-form', name: 'translationalresearch_download_humanTissueForm')]
    public function downloadHumanTissueFormAction( Request $request ) {

        $originalname = "human_tissue_request_form.pdf";
        $abspath = "";

        //orderassets\AppTranslationalResearchBundle\downloads
        $abspath = "orderassets\\AppTranslationalResearchBundle\\downloads\\".$originalname;

        $size = null;//$document->getSize();

        $downloader = new LargeFileDownloader();
        $downloader->downloadLargeFile($abspath, $originalname, $size);

        exit;
    }

    #[Route(path: '/download/new-study-intake-form/{specialtyId}', name: 'translationalresearch_download_new_study_intake_form')]
    public function downloadNewStudyIntakeFormAction( Request $request, $specialtyId=NULL ) {

        //$originalname = "trp_new_study_intake_form.pdf";
        //$originalname = "ctp_new_study_intake_form.docx";

        //orderassets\AppTranslationalResearchBundle\downloads
        //$abspath = "orderassets\\AppTranslationalResearchBundle\\downloads\\".$originalname;

        if(1) {

            $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
            $projectSpecialtyObject = $em->getRepository(SpecialtyList::class)->find($specialtyId);

            $transresUtil = $this->container->get('transres_util');
            $transresIntakeForm = $transresUtil->getTransresSiteParameterFile("transresIntakeForms",NULL,$projectSpecialtyObject);
            if( $transresIntakeForm ) {
                //$abspath = $transresIntakeForm->getAbsoluteUploadFullPath();
                //$abspath = $transresIntakeForm->getRelativeUploadFullPath();
                $abspath = $transresIntakeForm->getServerPath();
                $originalname = $transresIntakeForm->getOriginalnameClean();
                //echo $originalname.": abspath=$abspath <br>";
            } else {
                //echo "no transresIntakeForm <br>";
                $abspath = NULL;
                $originalname = NULL;
            }
        }
        //exit('111');

        $size = null;//$document->getSize();

        $downloader = new LargeFileDownloader();
        $downloader->downloadLargeFile($abspath, $originalname, $size);

        exit;
    }

    #[Route(path: '/transresitemcodes', name: 'translationalresearch_get_transresitemcodes_ajax', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function getTransResItemCodesAjaxAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $pricelistId = $request->query->get('pricelistId');
        $invoiceId = $request->query->get('invoiceId');
        $transresRequestId = $request->query->get('transresRequestId');

        $output = array();

        //TODO: duplicate TRP-10003 in http://127.0.0.1/order/translational-research/invoice/new/20451

        ///////////////// add not existed item code for invoiceItems without product /////////////////
        if( $invoiceId ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
            $invoice = $em->getRepository(Invoice::class)->find($invoiceId);
            if ($invoice) {
                foreach ($invoice->getInvoiceItems() as $invoiceItem) {
                    $product = $invoiceItem->getProduct();
                    if( $product ) {

                        $category = $product->getCategory();
                        //echo "category=$category<br>";

                        if( !$category ) {
                            $itemCode = $invoiceItem->getItemCode();
                            //echo $invoiceItem->getId().": itemCode=".$itemCode."<br>";

                            $output[] = array(
                                'id' => $itemCode,
                                'text' => $itemCode,
                            );

//                            if( $this->inArray($output,$itemCode,$itemCode) == false ) {
//                                $output[] = array(
//                                    'id' => $itemCode,
//                                    'text' => $itemCode,
//                                );
//                            }
                        }
                    } else {
                        $itemCode = $invoiceItem->getItemCode();
                        $output[] = array(
                            'id' => $itemCode,
                            'text' => $itemCode,
                        );

//                        if( $this->inArray($output,$itemCode,$itemCode) == false ) {
//                            $output[] = array(
//                                'id' => $itemCode,
//                                'text' => $itemCode,
//                            );
//                        }
                    }
                }
            }
        }
        ///////////////// EOF add not existed item code for invoiceItems without product /////////////////

        //////////////// get price list ////////////////
        if( $pricelistId == 'trp-default-pricelist' ) {
            $priceList = NULL;
        } else {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:PriceTypeList'] by [PriceTypeList::class]
            $priceList = $em->getRepository(PriceTypeList::class)->find($pricelistId);
        }
        //////////////// EOF get price list ////////////////

        ///////////// get default fee schedules (product/services) for specialty ////////////////
        $projectSpecialty = NULL;
        if( $transresRequestId ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
            $transresRequest = $em->getRepository(TransResRequest::class)->find($transresRequestId);
            $projectSpecialty = $transresRequest->getProjectSpecialty();
        }

        $transresRequestUtil = $this->container->get('transres_request_util');
        $categories = $transresRequestUtil->getProductServiceByProjectSpecialty($projectSpecialty);

        foreach ($categories as $category) {

            $initialFee = $category->getPriceFee($priceList);
            //echo "initialFee=[$initialFee] <br>";
            if( $initialFee === NULL ) {
                continue;
            }

            //show not just the item codes, but item codes followed by both the Description and Initial/Additional prices
            $output[] = array(
                'id' => $category->getId(),
                'text' => $category->getOptimalAbbreviationName($priceList), //Use the same as in ProductType.php -> category
            );

//            $key = $category->getId();
//            $val = $category->getOptimalAbbreviationName($priceList);
//            if( $this->inArray($output,$key,$val) == false ) {
//                $output[] = array(
//                    'id' => $key,
//                    'text' => $val, //Use the same as in ProductType.php -> category
//                );
//            }

        }
        ///////////// EOF get default fee schedules (product/services) for specialty ////////////////

        //////////// get products from work request /////////////////
        //get products from work request (for example, for new invoice, when fee is not showing anymore because it was disable, or fee does not have this specialty)
        if( $transresRequest ) {
            foreach($transresRequest->getProducts() as $product) {
                $category = $product->getCategory();
                if( !$category ) {
                    continue;
                }

                //echo "category=".$category->getId()."<br>";
                //check if this fee schedule does not exists in output array
                $key = $category->getId();
                $val = $category->getOptimalAbbreviationName($priceList);
                if( $this->inArray($output,$key,$val) == false ) {
                    $output[] = array(
                        'id' => $category->getId(),
                        'text' => $category->getOptimalAbbreviationName($priceList), //Use the same as in ProductType.php -> category
                    );
                }
            }
        }
        //////////// EOF get products from work request /////////////////
        //exit('111');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }
    function inArray($array, $key, $val) {
        foreach($array as $item) {
//            if( isset($item[$key]) && $item[$key] == $val ) {
//                return true;
//            }
            if( isset($item[$key]) ) {
                if( $item[$key] == $val ) {
                    return true;
                }
            }
        }
        return false;
    }
    #[Route(path: '/transresitemcodes-orig', name: 'translationalresearch_get_transresitemcodes_orig_ajax', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function getTransResItemCodesAjaxOrigAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $pricelistId = $request->query->get('pricelistId');
        $invoiceId = $request->query->get('invoiceId');
        $transresRequestId = $request->query->get('transresRequestId');

        $query = $em->createQueryBuilder()
            //->from('AppTranslationalResearchBundle:RequestCategoryTypeList', 'list')
            ->from(RequestCategoryTypeList::class, 'list')
            ->select("list")
            ->orderBy("list.orderinlist","ASC");

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        $categories = $query->getQuery()->getResult();

        $abbreviation = '';

        if( $pricelistId == 'trp-default-pricelist' ) {
            $abbreviation = '';
            $priceList = NULL;
        } else {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:PriceTypeList'] by [PriceTypeList::class]
            $priceList = $em->getRepository(PriceTypeList::class)->find($pricelistId);

            if( $priceList ) {
                $abbreviation = $priceList->getAbbreviation();
            }

            //$quantitiesArr = $product->calculateQuantities($priceList);
        }


        if( $abbreviation ) {
            $abbreviation = "-".$abbreviation;
        }

        $output = array();

        //add not existed item code for invoiceItems without product
        if( $invoiceId ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
            $invoice = $em->getRepository(Invoice::class)->find($invoiceId);
            if ($invoice) {
                foreach ($invoice->getInvoiceItems() as $invoiceItem) {
                    $product = $invoiceItem->getProduct();
                    if( $product ) {

                        $category = $product->getCategory();

                        if( !$category ) {
                            $itemCode = $invoiceItem->getItemCode();
                            //echo $invoiceItem->getId().": itemCode=".$itemCode."<br>";

                            $output[] = array(
                                'id' => $itemCode,
                                'text' => $itemCode,
                            );
                        }
                    } else {
                        $itemCode = $invoiceItem->getItemCode();
                        $output[] = array(
                            'id' => $itemCode,
                            'text' => $itemCode,
                        );
                    }
                }
            }
        }

        foreach ($categories as $category) {

//            $initialQuantity = $category->getPriceInitialQuantity($priceList);
//            $initialFee = $category->getPriceFee($priceList);
//            $additionalFee = $category->getPriceFeeAdditionalItem($priceList);
//            $categoryItemCode = $category->getProductId($priceList);
//            $categoryName = $category->getName();

            $initialFee = $category->getPriceFee($priceList);
            //echo "initialFee=[$initialFee] <br>";
            if( $initialFee === NULL ) {
                continue;
            }

            //show not just the item codes, but item codes followed by both the Description and Initial/Additional prices
            $output[] = array(
                'id' => $category->getId(),
                //'id' => $category->getProductId().$abbreviation,
                //'text' => $category->getProductId().$abbreviation,
                'text' => $category->getOptimalAbbreviationName($priceList), //Use the same as in ProductType.php -> category
            );
        }

        //testing, add: new code item 1
//        $output[] = array(
//            'id' => "new code item 1",
//            'text' => "new code item 1",
//        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    #[Route(path: '/transres-project-remaining-budget', name: 'translationalresearch_get_project_remaining_budget_ajax', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function getTransResRecalculateProjectRemainingBudgetAjaxAction(Request $request) {

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();

        //$projectId = $request->query->get('projectId');
        //$productArr = $request->query->get('productArr');

        $projectId = $request->get('projectId');
        $workrequestId = $request->get('workrequestId');
        $productsArr = $request->get('productsArr');

        //print_r($productsArr);
        //echo "projectId=$projectId, workrequestId=$workrequestId <br>";
        //exit('111');

        if( !$projectId ) {
            $output = NULL;
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($output));
            return $response;
        }

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $project = $em->getRepository(Project::class)->find($projectId);
        if( !$project ) {
            $output = NULL;
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($output));
            return $response;
        }

        $transresRequestProducts = array();
        $transresRequest = NULL;
        if( $workrequestId ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
            $transresRequest = $em->getRepository(TransResRequest::class)->find($workrequestId);
            $transresRequestProducts = $transresRequest->getProducts();
        }
        if( !$transresRequest ) {
            $transresRequest = new TransResRequest();
        }

        $priceList = $project->getPriceList();
        $remainingBudget = $originalRemainingBudget = $project->getRemainingBudget();

        if( $remainingBudget !== NULL ) {
            //
        } else {
            $remainingBudget = 0;
        }

        $dummyProduct = new Product();

        $processedProducts = array();
        $grandTotal = 0;

        //calculate this work request total
        foreach($productsArr as $productArr) {
            $category = NULL;
            $productId = $productArr['productId'];
            $categoryId = $productArr['categoryId'];
            $quantity = $productArr['quantity'];
            //echo "quantity=$quantity, productId=$productId, categoryId=$categoryId <br>";

            if( $categoryId ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequestCategoryTypeList'] by [RequestCategoryTypeList::class]
                $category = $em->getRepository(RequestCategoryTypeList::class)->find($categoryId);
            }
            
            if( $category && $quantity ) {
                $quantitiesArr = $dummyProduct->calculateQuantitiesByQuantityAndCategory($priceList,$quantity,$category);
                $initialQuantity = $quantitiesArr['initialQuantity'];
                $additionalQuantity = $quantitiesArr['additionalQuantity'];
                $initialFee = $quantitiesArr['initialFee'];
                $additionalFee = $quantitiesArr['additionalFee'];
                $categoryItemCode = $quantitiesArr['categoryItemCode'];
                $categoryName = $quantitiesArr['categoryName'];

                // add/show somehow "comment" from Work Request ?

                if( $initialQuantity && $initialFee ) {
                    //Total
                    //$total = $this->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
                    $total = $transresRequest->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
                    //echo "total=$total<br>";

                    if( $total ) {
                        $grandTotal = $grandTotal + $total;
                    }
                }
            }//if $category && $quantity

            //Case if existing product in DB edited => subtract grandTotal from the product in DB
            if( $productId ) {
                $processedProducts[] = $productId;

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Product'] by [Product::class]
                $productDb = $em->getRepository(Product::class)->find($productId);

                if( $productDb ) {
                    $total = $this->calculateProductTotal($transresRequest, $productDb, $priceList, $quantity, $category);
                    if ($total) {
                        $grandTotal = $grandTotal - $total;
                    }
                }
            }//if $productId

        }//foreach product in html

        //Case product removed from this Work Request in html
        foreach($transresRequestProducts as $transresRequestProduct) {
            //check if progressState != draft, canceled
            $progressState = $transresRequestProduct->getProgressState();
            //check if billingState != draft, canceled
            $billingState = $transresRequestProduct->getBillingState();

            $skip = false;
            if( $progressState == 'draft' || $progressState == 'canceled' ) {
                $skip = true;
            }
            if( $billingState == 'draft' || $billingState == 'canceled' ) {
                $skip = true;
            }

            if( $skip == false ) {
                $quantity = $transresRequestProduct->getQuantity();
                $category = $transresRequestProduct->getCategory();
                $total = $this->calculateProductTotal($transresRequest,$transresRequestProduct,$priceList, $quantity, $category);

                if ($total) {
                    $grandTotal = $grandTotal - $total;
                }
            }
        }

        //echo "1remainingBudget=$remainingBudget, grandTotal=$grandTotal<br>";
        if( $grandTotal ) {
            //$remainingBudget = $transresRequestUtil->toDecimal($remainingBudget);
            //echo "2remainingBudget=$remainingBudget, grandTotal=$grandTotal<br>";
            $remainingBudget = $remainingBudget - $grandTotal;
            //echo "3remainingBudget=$remainingBudget<br>";

            $remainingBudgetValue = $transresUtil->toDecimal($remainingBudget);
        }

//        $negative = false;
//        if( $remainingBudget < 0 ) {
//            $negative = true;
//        }

        //$remainingBudget = $transresUtil->toMoney($remainingBudget);
        //echo "4remainingBudget=$remainingBudget<br>";
        $remainingBudget = $transresUtil->dollarSignValue($remainingBudget);
        //$remainingBudget = $transresUtil->moneyDollarSignValue($remainingBudget);
        //echo "5remainingBudget=$remainingBudget<br>";

        $transresRequest = NULL;
        $dummyProduct = NULL;

        //dump($productArr);
        //print_r($productArr);

        //echo "remainingBudget=$remainingBudget<br>";
        //exit('111');

        //testing
        $output[] = array(
            'error' => NULL,
            'remainingBudget' => $remainingBudget, //"$"."100.00"
            'remainingBudgetValue' => $remainingBudgetValue,
            //'originalRemainingBudget' => $originalRemainingBudget
            //'negative' => $negative
        );

        //$output = $remainingBudget;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }
    public function calculateProductTotal($transresRequest,$product,$priceList, $quantity, $category) {
        $total = 0;
        if( $product ) {
            $quantitiesArr = $product->calculateQuantitiesByQuantityAndCategory($priceList, $quantity, $category);
            $initialQuantity = $quantitiesArr['initialQuantity'];
            $additionalQuantity = $quantitiesArr['additionalQuantity'];
            $initialFee = $quantitiesArr['initialFee'];
            $additionalFee = $quantitiesArr['additionalFee'];

            if ($initialQuantity && $initialFee) {
                $total = $transresRequest->getTotalFeesByQuantity($initialFee, $additionalFee, $initialQuantity, $additionalQuantity);
                //echo "total=$total<br>";
            }
        }

        return $total;
    }


    /**
     * http://localhost/order/translational-research/import-old-data/0
     * 1) Disable comments first in the FosCommentListener - $disable = true
     * 2) Make sure the Admin and default AP-CP reviewers are set correctly
     * 3) Run Steps 1, 2, 3 and 4
     * 4) Run Step 5
     */
    #[Route(path: '/import-old-data/{startRow}', name: 'translationalresearch_import_old_data', methods: ['GET'])]
    public function importOldDataAction(Request $request, $startRow=null) {

        if( !$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        exit('not allowed');
        set_time_limit(10800); //18000 seconds => 5 hours
        ini_set('memory_limit', '7168M');

        $em = $this->getDoctrine()->getManager();
        echo "startRow=".$startRow."<br>";

        $resProject = null;
        $resAdminComments = null;

        $resArr = array();

        $importUtil = $this->container->get('transres_import');

        //Step 1: import projects and admin Comments from TRF_PROJECT_INFO
        if(0) {
            //import projects
            $resArr[] = $importUtil->importOldData($request, 'TRF_PROJECT_INFO.xlsx', 'project_adminComments');
        }
        if(0) {
            //import projects
            $resProject = $importUtil->importOldData($request, 'TRF_PROJECT_INFO.xlsx', 'project');
            //import admin comments
            //$resAdminComments = $importUtil->importOldData($request, 'TRF_PROJECT_INFO.xlsx', 'adminComments');

            $resArr[] = $resProject . "<br>========= EOF TRF_PROJECT_INFO ===========<br>" . $resAdminComments;
        }
        if(0) {
            //edit project
            $resArr[] = $importUtil->importOldData($request, 'TRF_PROJECT_INFO.xlsx', 'project_edit');
        }

        //Step 2: import projects and admin Comments from TRF_DRAFT_PROJECT
        if(0) {
            //import projects
            $resArr[] = $importUtil->importOldData($request, 'TRF_DRAFT_PROJECT_INFO.xlsx', 'project_adminComments');
        }
        if(0) {
            //import projects
            $resProject = $importUtil->importOldData($request, 'TRF_DRAFT_PROJECT.xlsx', 'project');
            //import admin comments
            $resAdminComments = $importUtil->importOldData($request, 'TRF_DRAFT_PROJECT.xlsx', 'adminComments');

            $resArr[] = $resProject . "<br>========= EOF TRF_DRAFT_PROJECT ===========<br>" . $resAdminComments;
        }
        if(0) {
            //edit project
            $resArr[] = $importUtil->importOldData($request, 'TRF_DRAFT_PROJECT.xlsx', 'project_edit');
        }

        //Step 3: import Committee Comments from TRF_COMMITTEE_REV
        //Committee comments
        if(0) {
            $resArr[] = $importUtil->importCommitteeComments($request, 'TRF_COMMITTEE_REVIEW_INFO.xlsx');
        }

        //Step 4: import Committee Comments from TRF_COMMENTS_RESP
        if(0) {
            $resArr[] = $importUtil->importCommitteeComments2($request, 'TRF_COMMENTS_RESPONSE_INFO.xlsx');
        }

        //Step 5: import working requests (~14k ~10 hours)
        if(0) {
            //use only 1000 per time
//            $startRow = 2;
//            $endRow = $startRow + 1000;
//            echo "Start: $startRow, end: $endRow <br>";
//            $time_start = microtime(true);
//            $resCount = $importUtil->importWorkRequests($request, 'TRF_REQUESTED_2.xlsx', $startRow, $endRow);
//            $time_end = microtime(true);
//
//            //dividing with 60 will give the execution time in minutes otherwise seconds
//            $execution_time = ($time_end - $time_start)/60;
//            //execution time of the script
//            //echo '<b>Total Execution Time:</b> '.$execution_time.' Mins <br>';
//            echo '<b>Total Execution Time:</b> '.number_format((float) $execution_time, 2).' Mins <br>';

            if( !$startRow ) {
                $startRow = 2;
            }

            //$filename = 'TRF_REQUESTED_SERVICE_INFO.xlsx';
            $filename = 'TRF_REQUESTED_SERVICE_INFO_1.xlsx';

            //check
            //$this->importRequests($request,$filename,$startRow,null);

            $this->importRequests($request,$filename,$startRow,1000);

            //$this->importRequests($request,$filename,3000);
            //$this->importRequests($request,$filename,2,1000);
            //$this->importRequests($request,$filename,5000);

        }

        //6) Update Request from "UpdatedReqStatus.xlsx": Price, Status, Comment
        if(0) {
            $filename = "Updated_All_REQUESTS_08202018.xlsx";
            $resArr[] = $importUtil->updateRequests($request,$filename);
        }

        ///////////////////// AUX Functions ///////////////////////
        //edit requests without oid
        if(0) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
            $repository = $em->getRepository(TransResRequest::class);
            $dql =  $repository->createQueryBuilder("request");
            $dql->select('request');

            $dql->leftJoin('request.principalInvestigators','principalInvestigators');

            $dql->andWhere("request.oid IS NULL");
            //$dql->andWhere("project.oid IS NULL");
            //$dql->andWhere("principalInvestigators.id IS NULL");

            $query = $dql->getQuery();

            $requests = $query->getResult();
            echo "requests count=".count($requests)."<br>";

            foreach($requests as $transresRequest) {
                $transresRequest->generateOid();
                $em->flush($transresRequest);
            }
        }

        //add missing request's comment
        if(0) {
            $filename = 'TRF_REQUESTED_1.xlsx';
            $startRow = 2;//3908;
            $endRow = null;//3927;
            $resCount = $importUtil->editWorkRequests($request, $filename, $startRow, $endRow);
        }
        ///////////////////// EOF AUX Functions ///////////////////////

        $res = implode("<br><br>",$resArr);

        exit('Imported result: '.$res);

        //Flash
        $this->addFlash(
            'notice',
            'Imported result: '.$res
        );
        
        return $this->redirectToRoute('translationalresearch_home');
    }

    public function importRequests( $request, $filename, $startRow, $increment=1000 ) {
        set_time_limit(10800); //18000 seconds => 5 hours
        ini_set('memory_limit', '7168M');

        $importUtil = $this->container->get('transres_import');
        //use only 500 per time
        //$startRow = 2;
        if( $increment ) {
            $endRow = $startRow + $increment;
        } else {
            $endRow = null;
        }
        echo "Start: $startRow, end: $endRow <br>";
        $time_start = microtime(true);
        $resCount = $importUtil->importWorkRequests($request, $filename, $startRow, $endRow);
        $time_end = microtime(true);

        //dividing with 60 will give the execution time in minutes otherwise seconds
        $execution_time = ($time_end - $time_start)/60;
        //execution time of the script
        //echo '<b>Total Execution Time:</b> '.$execution_time.' Mins <br>';
        echo '<b>Imported '.$resCount.' requests; Total Execution Time:</b> '.number_format((float) $execution_time, 2).' Mins <br>';
    }


    /**
     * http://localhost/order/translational-research/barcode-demo
     */
    #[Route(path: '/barcode-demo', name: 'translationalresearch_barcode-demo')]
    #[Template('AppTranslationalResearchBundle/Request/barcodedemo.html.twig')]
    public function barcodeDemoAction( Request $request ) {
        return array();
    }

    /**
     * generateAntibodyList and setAntibodyListProperties
     * run: http://127.0.0.1/order/translational-research/generate-antibody-list/ihc_antibody_mssql.sql
     * run: http://127.0.0.1/order/translational-research/generate-antibody-list/ihc_antibody_mssql_2.sql
     */
    #[Route(path: '/generate-antibody-list/{filename}', name: 'translationalresearch_generate_antibody_list')]
    public function generateAntibodyListAction(Request $request, $filename) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        //exit("generateAntibodyList: ".$filename);

        $importUtil = $this->container->get('transres_import');
        $res = $importUtil->createAntibodyList($filename);
        //exit("generateAntibodyListAction: Finished with res=".$res);

        //Flash
        $this->addFlash(
            'notice',
            "Antibody list imported result ($filename): <br>".$res
        );

//        //update antibody list
//        $filename = "IHC_antibody-11_16Nov2018.csv";
//        $res = $importUtil->updateInsertAntibodyList($filename);
//        //Flash
//        $this->addFlash(
//            'notice',
//            "Antibody list imported result ($filename): <br>".$res
//        );

        //exit("res=".$res);
        return $this->redirectToRoute('employees_siteparameters');
    }
    /**
     * Load Antibody list into Platform List Manager
     * run: http://localhost/order/translational-research/set-properties-antibody-list/
     */
    #[Route(path: '/set-properties-antibody-list/', name: 'translationalresearch_set_properties_antibody_list')]
    public function setPropertiesAntibodyListAction() {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $importUtil = $this->container->get('transres_import');
        $res = $importUtil->setAntibodyListProperties();

        //Flash
        $this->addFlash(
            'notice',
            'Antibody set properties result: '.$res
        );

//        $filename = "IHC_antibody-11_16Nov2018.csv";
//        $res = $importUtil->updateInsertAntibodyList($filename);
//        //Flash
//        $this->addFlash(
//            'notice',
//            "Antibody list imported result ($filename): <br>".$res
//        );

        //exit("res=".$res);
        return $this->redirectToRoute('employees_siteparameters');
    }

    /**
     * Update or Insert AntibodyList
     * run: http://127.0.0.1/order/translational-research/update-insert-antibody-list
     */
    #[Route(path: '/update-insert-antibody-list', name: 'translationalresearch_update_insert_antibody_list')]
    public function updateInsertAntibodyListAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        //exit("generateAntibodyList: ".$filename);

        $importUtil = $this->container->get('transres_import');

        $filename = "IHC_antibody-11_16Nov2018.csv";
        $res = $importUtil->updateInsertAntibodyList($filename);
        //exit("generateAntibodyListAction: Finished with res=".$res);

        //Flash
        $this->addFlash(
            'notice',
            "Antibody list imported result ($filename): <br>".$res
        );

        //exit("res=".$res);
        return $this->redirectToRoute('employees_siteparameters');
    }
    /**
     * Sync ID for AntibodyList
     * run: http://127.0.0.1/order/translational-research/sync-id-antibody-list
     */
    #[Route(path: '/sync-id-antibody-list', name: 'translationalresearch_sync_id_antibody_list')]
    public function syncIdAntibodyListAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("syncIdAntibodyListAction");

        $importUtil = $this->container->get('transres_import');
        $res = $importUtil->syncIdAntibodyList();

        //Flash
        $this->addFlash(
            'notice',
            "Antibody sync id result: <br>".$res
        );

        //exit("res=".$res);
        return $this->redirectToRoute('employees_siteparameters');
    }

    /**
     * http://127.0.0.1/order/translational-research/update-projects-implicit-date
     */
    #[Route(path: '/update-projects-implicit-date', name: 'translationalresearch_update_projects_implicit_date')]
    public function updateProjectsImplicitDateAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("updateProjectsImplicitDateAction: Not allowed");

        $count = $this->updateCommentObject($request);
        exit("<br>End of update comment's object: ".$count);

        $count = $this->updateInvoicePaidDue($request);
        exit("End of update invoice's paid and due: ".$count);

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $projects = $em->getRepository(Project::class)->findAll();

        //$project = $em->getRepository('AppTranslationalResearchBundle:Project')->find(3294);
        //$projects = array($project);

        //$batchSize = 20;
        $i = 0;

        foreach($projects as $project) {
            $implicitExpDate = $project->calculateAndSetImplicitExpirationDate();
            if($implicitExpDate) {
                $i++;
                //echo "update implicitExpDate=" . $implicitExpDate->format('Y-m-d') . "<br>";
                $em->flush($project);
            }
            //$em->persist($project);
//            if (($i % $batchSize) === 0) {
//                $em->flush($project);
//                $em->clear(); // Detaches all objects from Doctrine!
//            }
//            $i++;
        }
        //$em->flush(); //Persist objects that did not make up an entire batch
        //$em->clear();

        exit("End of update project's implicit dates: ".$i);
    }

//    /**
//     * http://127.0.0.1/order/translational-research/update-invoice-paid-due
//     *
//     * @Route("/update-invoice-paid-due", name="translationalresearch_update_invoice_paid_due")
//     */
    public function updateInvoicePaidDue( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("updateInvoicePaidDue: Not allowed");

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $invoices = $em->getRepository(Invoice::class)->findByStatus('Paid in Full');
        echo "Found invoices=".count($invoices)."<br>";

        $i = 1;

        foreach($invoices as $invoice) {
            if ($invoice->getStatus() == "Paid in Full") {
                if( $invoice->getDue() && !$invoice->getPaid() ) {
                    echo $i.": Original: total=".$invoice->getTotal()."; paid=".$invoice->getPaid()."; due=".$invoice->getDue()."<br>";
                    $invoice->setPaid($invoice->getTotal());
                    $invoice->setDue(NULL);
                    $em->flush($invoice);
                    echo $i.": Updated: total=".$invoice->getTotal()."; paid=".$invoice->getPaid()."; due=".$invoice->getDue()."<br><br>";
                    $i++;
                }
            }
        }

        //exit("End of update project's implicit dates: ".$i);
        return $i-1;
    }

    public function updateCommentObject(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FosComment'] by [FosComment::class]
        $repository = $em->getRepository(FosComment::class);
        $dql =  $repository->createQueryBuilder("foscomment");
        $dql->select('foscomment');

        $dql->where("foscomment.entityName IS NULL");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $comments = $query->getResult();
        echo "comments count=".count($comments)."<br>";

        $batchSize = 20;
        $count = 0;

        foreach($comments as $comment) {
            if( !$comment->getEntityId() || !$comment->getEntityName() || !$comment->getEntityNamespace() ) {

                $commentId = $comment->getThread();
                //echo "commentId=".$commentId."<br>";
                //get request ID from $commentId 'transres-Request-13541-billing'
                $commentIdArr = explode("-", $commentId);
                if( count($commentIdArr) >= 3 ) {
                    $threadEntityName = $commentIdArr[1];
                    $requestId = $commentIdArr[2];
                    if( $threadEntityName && $requestId ) {

                        if( $threadEntityName == "Request" ) {
                            $threadEntityName = "TransResRequest";
                        }

                        //$entity = $em->getRepository('AppTranslationalResearchBundle:'.$threadEntityName)->find($requestId);
                        $entity = $em->getRepository('App\\TranslationalResearchBundle\\Entity\\'.$threadEntityName)->find($requestId);
                        if( $entity ) {
                            $comment->setObject($entity);
                            echo $comment->getEntityId()." ";

                            $em->flush($comment);

//                            if (($count % $batchSize) === 0) {
//                                $em->flush();
//                                $em->clear(); // Detaches all objects from Doctrine!
//                            }

                            $count++;
                        }
                    }
                }

            }
        }

        $em->flush(); //Persist objects that did not make up an entire batch
        $em->clear();

        return $count;
    }


    //update fees
    /**
     * http://127.0.0.1/order/translational-research/update-fees
     */
    #[Route(path: '/update-fees', name: 'translationalresearch_update_fees')]
    public function updateFeesAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("updateFeesAction: Not allowed");

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        //SpecialtyList
        $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");
        $specialtyAPCPObject = $transresUtil->getSpecialtyObject("ap-cp");
        $specialtyCovid19Object = $transresUtil->getSpecialtyObject("covid19");
        $specialtyMisiObject = $transresUtil->getSpecialtyObject("misi");

        $count = 0;

        foreach($fees as $fee) {

            if( count($fee->getProjectSpecialties()) > 0 ) {
                echo "Skip $fee <br>";
                continue;
            }

            echo $count.": Update fee ID=".$fee->getId().": $fee <br>";
            $fee->addProjectSpecialty($specialtyHemaObject);
            $fee->addProjectSpecialty($specialtyAPCPObject);
            $fee->addProjectSpecialty($specialtyCovid19Object);

            $em->flush();

            $count++;
        }


        exit("End add new fees: ".$count);
    }

    /**
     * http://127.0.0.1/order/translational-research/add-misi-fees
     */
    #[Route(path: '/add-misi-fees', name: 'translationalresearch_add_misi_fees')]
    #[Template('AppTranslationalResearchBundle/Default/upload-csv-file.html.twig')]
    public function addMisiFeesAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("addMisiFeesAction: Not allowed");

        //$em = $this->getDoctrine()->getManager();
        //$transresUtil = $this->container->get('transres_util');
        $importUtil = $this->container->get('transres_import');

        $form = $this->createFormBuilder()
            ->add('file', FileType::class)
            ->add('upload', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $inputFileName = $form['file']->getData();
            echo "inputFileName1=" . $inputFileName . "<br>";
            //exit('111');

            $importUtil->addNewFees($inputFileName);

            //exit("End addMisiFeesAction");
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * NOT USED: use a separate field to show mergeInfo
     * http://127.0.0.1/order/translational-research/merge-project-info
     */
    #[Route(path: '/merge-project-info', name: 'translationalresearch_merge-project-info')]
    public function mergeProjectInfoAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("mergeProjectInfoAction: Not allowed");

        $em = $this->getDoctrine()->getManager();
        $logger = $this->container->get('logger');

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $projects = $em->getRepository(Project::class)->findAll();
        echo "projects=".count($projects)."<br>";

        $newline = "\n";
        $count = 0;
        $countUpdated = 0;

        foreach($projects as $project) {
            $count++;

            if( $count > 10 ) {
                break;
            }

            $mergeInfo = $project->mergeHiddenFields();
            if($mergeInfo) {
                $description = $project->getDescription();

                //$description = $description . "<br><br>" . "Previously used in the project fields (currently hidden):<br>" . $mergeInfo;
                $description = $description . $newline.$newline.
                    "-------------------------------------------------- " .
                    $newline .
                    "Previously used in the project fields (currently hidden):" .
                    $newline . $mergeInfo;

                $project->setDescription($description);
                echo $project->getId().": Desciption: ".$description."<br>";

                $msg = $project->getId().": updated mergeInfo=" . $mergeInfo . "<br>";
                echo "<br>".$msg."<br>";
                //$logger->notice($msg);
                //$em->flush();
                $countUpdated++;
            }
        }

        exit("mergeProjectInfoAction: processed projects=$count, updated projects=".$countUpdated);
    }


    /**
     * http://127.0.0.1/order/translational-research/update-multiple-fees
     */
    #[Route(path: '/update-multiple-fees', name: 'translationalresearch_update_multiple_fees')]
    #[Template('AppTranslationalResearchBundle/Default/upload-csv-file.html.twig')]
    public function updateMultipleFeesAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("updateMultipleFeesAction: Not allowed");

        //$em = $this->getDoctrine()->getManager();
        //$transresUtil = $this->container->get('transres_util');
        $importUtil = $this->container->get('transres_import');

        $form = $this->createFormBuilder()
            ->add('file', FileType::class)
            ->add('upload', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $inputFileName = $form['file']->getData();
            echo "inputFileName1=" . $inputFileName . "<br>";
            //exit('111');

            $count = $importUtil->addNewMultipleFees($inputFileName);

            exit("End updateMultipleFeesAction: count=".$count);
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * http://127.0.0.1/order/translational-research/update-project-price-list
     */
    #[Route(path: '/update-project-price-list', name: 'translationalresearch_update_project_list')]
    public function updateProjectPriceListAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("updateProjectPriceListAction: Not allowed");

        $em = $this->getDoctrine()->getManager();

        $projectIds = array(
            "APCP2182",
            "APCP2230",
            "APCP2268",
            "APCP3335",
            "APCP3409",
            "APCP3421",
            "APCP3421",
            "APCP3457",
            "APCP3500",
            "APCP3500",
            "APCP3501",
            "APCP3501",
            "APCP3503",
            "APCP3504",
            "APCP3505",
            "APCP3506",
            "APCP3512",
            "APCP676",
            "APCP756",
            "APCP847",
            "APCP870",
            "APCP951",
            "COVID3384",
            "HP1061",
            "HP2213",
            "HP3429",
            "HP464",
            "HP584",
            "HP99"
        );

        $priceListName = "Internal Pricing";
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:PriceTypeList'] by [PriceTypeList::class]
        $internalPriceList = $em->getRepository(PriceTypeList::class)->findOneByName($priceListName);
        if( !$internalPriceList ) {
            exit("$priceListName list does not exist");
        }
        echo "internalPriceList=".$internalPriceList."<br>";

        $count = 0;
        foreach($projectIds as $projectOid) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
            $project = $em->getRepository(Project::class)->findOneByOid($projectOid);
            echo "project=".$project."<br>";
            if( $project ) {
                $priceList = $project->getPriceList();
                if( !$priceList || $priceList->getName() != $priceListName ) {
                    $count++;
                    $project->setPriceList($internalPriceList);
                    $em->flush();
                } else {
                    echo "Price list already exists = $priceList <br>";
                }
            } else {
                echo "!!! project not found by id=".$projectOid."<br>";
            }
        }

        exit("updated $count projects");
    }

    /**
     * http://127.0.0.1/order/translational-research/update-remove-external-price-list
     */
    #[Route(path: '/update-remove-external-price-list', name: 'translationalresearch_update_remove_external_price_list')]
    public function removeExternalPriceListAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("removeExternalPriceListAction: Not allowed");

        $em = $this->getDoctrine()->getManager();

        $priceListName = "External Pricing";
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:PriceTypeList'] by [PriceTypeList::class]
        $externalPriceList = $em->getRepository(PriceTypeList::class)->findOneByName($priceListName);
        if( !$externalPriceList ) {
            exit("$externalPriceList list does not exist");
        }
        echo "externalPriceList=".$externalPriceList."<br>";

        //$projects = $em->getRepository('AppTranslationalResearchBundle:Project')->findOneByOid($projectOid);
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');
        $dql->leftJoin('project.priceList','priceList');
        $dql->where("priceList.id = :externalPriceList");
        $params = array(
            'externalPriceList' => $externalPriceList->getId(),
        );
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        $query->setParameters($params);
        //echo "query=".$query->getSql()."<br>";
        $projects = $query->getResult();
        echo "projects=".count($projects)."<br>";

        $count = 0;
        foreach($projects as $project) {
            echo "project=".$project."<br>";
            if( $project ) {
                $priceList = $project->getPriceList();
                if( $priceList && $priceList->getName() == $priceListName ) {
                    $count++;
                    $project->setPriceList(NULL);
                    $em->flush();
                } else {
                    echo "Price list does not exist = $priceList <br>";
                }
            } else {
                echo "!!! project is null <br>";
            }
        }

        exit("updated $count projects");
    }

    /**
     * http://127.0.0.1/order/translational-research/update-project-budget
     */
    #[Route(path: '/update-project-budget', name: 'translationalresearch_update_project_budget')]
    public function updateProjectBudgetAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("updateProjectBudgetAction: Not allowed");

        $em = $this->getDoctrine()->getManager();

        //$projects = $em->getRepository('AppTranslationalResearchBundle:Project')->findOneByOid($projectOid);
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');
        $dql->where("project.noBudgetLimit IS NULL");
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        //echo "query=".$query->getSql()."<br>";
        $projects = $query->getResult();
        echo "projects=".count($projects)."<br>";

        $count = 0;
        foreach($projects as $project) {
            //echo "project=".$project."<br>";
            if( $project ) {
                //For “Non-funded” projects already in the system,
                // set the “No budget limit” to UNchecked,
                // and copy a valid value from the Estimated Costs to Approved Budget.
                $project->autoPopulateApprovedProjectBudget();
                $em->flush();
                $count++;
            } else {
                echo "!!! project is null <br>";
            }
        }

        exit("updated $count projects");
    }

    /**
     * http://127.0.0.1/order/translational-research/update-project-total
     */
    #[Route(path: '/update-project-total', name: 'translationalresearch_update_project_total')]
    public function updateProjectTotalAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("updateProjectTotalAction: Not allowed");

        ini_set('max_execution_time', '600'); //600 seconds = 10 minutes

        $em = $this->getDoctrine()->getManager();

        //$projects = $em->getRepository('AppTranslationalResearchBundle:Project')->findOneByOid($projectOid);
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');
        $dql->orderBy("project.id","DESC");
        //$dql->where("project.total IS NULL");
        //$dql->where("project.total IS NOT NULL");
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        //echo "query=".$query->getSql()."<br>";
        $projects = $query->getResult();
        echo "projects=".count($projects)."<br>";

        $batchSize = 20;
        $count = 0;
        $countDiff = 0;

        foreach($projects as $project) {
            //echo "project=".$project."<br>";
            if( $project ) {

                $invoicesInfos = $project->getInvoicesInfosByProject();
                $grandTotal = $invoicesInfos['grandTotal'];

                //$total = $project->updateProjectTotal();
                $total = $project->getTotal();
                //echo $project->getId().": total=$total <br>";
                //echo $project->getId().": totals: $total != $grandTotal <br>";

                if( $total !== NULL && $grandTotal != $total ) {
                    echo $project->getId() . ": Project total different: current=$total, correct=$grandTotal <br>";
                    $countDiff++;

                    $project->updateProjectTotal();

                    if (($count % $batchSize) === 0) {
                        $em->flush();
                    }
                    $count++;
                }
            } else {
                echo "!!! project is null <br>";
            }
        }

        $em->flush(); //Persist objects that did not make up an entire batch

        exit("Project diff count = $countDiff");

        exit("updated $count projects");
    }

//    /**
    //     * http://127.0.0.1/order/translational-research/batch-close-projects
    //     *
    //     * @Route("/batch-close-projects", name="translationalresearch_batch_close_projects")
    //     */
    //    public function closeProjectsAction1( Request $request ) {
    //        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
    //            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
    //        }
    //
    //        exit("closeProjectsAction: Not allowed");
    //
    //        $em = $this->getDoctrine()->getManager();
    //
    //        //$projects = $em->getRepository('AppTranslationalResearchBundle:Project')->findOneByOid($projectOid);
    //        $repository = $em->getRepository('AppTranslationalResearchBundle:Project');
    //        $dql =  $repository->createQueryBuilder("project");
    //        $dql->select('project');
    //        $dql->where("project.noBudgetLimit IS NULL");
    //        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
    //        //echo "query=".$query->getSql()."<br>";
    //        $projects = $query->getResult();
    //        echo "projects=".count($projects)."<br>";
    //
    //
    //
    //        $projectOids = array();
    //
    //        $count = 0;
    //        foreach($projects as $project) {
    //            //echo "project=".$project."<br>";
    //            if( $project ) {
    //
    //                //$testing = false;
    //                $originalStateStr = $project->getState();
    //                $to = "closed";
    //
    //                $project->setState($to);
    //
    //                //$em->flush($project);
    //
    //                $projectOids[] = $project->getOid()." (original state=".$originalStateStr.")";
    //
    //                $count++;
    //            } else {
    //                echo "!!! project is null <br>";
    //            }
    //        }
    //
    //        //event log
    //        $eventType = "Project Updated";
    //        $resultMsg = $count." projects are closed in batch by a script: " . implode(", ",$projectOids);
    //
    //        //$transresUtil->setEventLog(NULL,$eventType,$resultMsg);
    //
    //        $this->addFlash(
    //            'notice',
    //            $resultMsg
    //        );
    //
    //        exit("updated $count projects");
    //    }
    /**
     * http://127.0.0.1/order/translational-research/batch-close-projects
     */
    #[Route(path: '/batch-close-projects', name: 'translationalresearch_batch_close_projects')]
    #[Template('AppTranslationalResearchBundle/Default/upload-csv-file.html.twig')]
    public function closeProjectsAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("closeProjectsAction: Not allowed");

        //$em = $this->getDoctrine()->getManager();
        //$transresUtil = $this->container->get('transres_util');
        $importUtil = $this->container->get('transres_import');

        $form = $this->createFormBuilder()
            ->add('file', FileType::class)
            ->add('upload', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $inputFileName = $form['file']->getData();
            echo "inputFileName=" . $inputFileName . "<br>";
            //exit('111');

            $count = $importUtil->closeProjectsFromSpreadsheet($inputFileName,$request);

            exit("End closeProjectsAction: count=".$count);
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * http://127.0.0.1/order/translational-research/email-notation-test
     */
    #[Route(path: '/email-notation-test', name: 'translationalresearch_email_notation_test')]
    public function emailNotationTestAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        //exit("emailNotationTestAction: Not allowed");

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        $notations = "
        <h4>Project Request:</h4>
        <br>[[PROJECT ID]] - project request ID,
        <br>[[PROJECT ID TITLE]] - project ID, Title,
        <br>[[PROJECT TITLE SHORT]] - short project title,
        <br>[[PROJECT TITLE]] - full project title,
        <br>[[PROJECT PIS]] - project pis,
        <br>[[PROJECT PATHOLOGIST LIST]] - project pathologist involved list,
        <br>[[PROJECT BILLING CONTACT LIST]] - project billing contact list,
        <br>[[PROJECT SUBMISSION DATE]] - project submission date,
        <br>[[PROJECT STATUS]] - project status,
        <br>[[PROJECT STATUS COMMENTS]] - comments for the current project's stage,
        <br>[[PROJECT FUNDED]] - project \"Funded\" or \"Non-funded\",
        <br>[[PROJECT SHOW URL]] - project request show url,
        <br>[[PROJECT EDIT URL]] - project request edit url,
        <br>[[PROJECT REQUESTS URL]] - link to list of all work requests for this project,
        <br>[[PROJECT NON-CANCELED INVOICES URL]] - link to list of all latest non-canceled invoices for this project,


        <h4>Work Request:</h4>
        <br>[[REQUEST ID]] - work request ID,
        <br>[[REQUEST SUBMITTER]] - work request submitter,
        <br>[[REQUEST SUBMISSION DATE]] - work request submission date,
        <br>[[REQUEST UPDATE DATE]] - work request update date,
        <br>[[REQUEST PROGRESS STATUS]] - work request progress status,
        <br>[[REQUEST BILLING STATUS]] - work request billing status,
        <br>[[REQUEST SHOW URL]] - work request show url,
        <br>[[REQUEST CHANGE PROGRESS STATUS URL]] - work request change progress state url,
        <br>[[REQUEST NEW INVOICE URL]] - create a new invoice url for this work request,

        <h4>Invoice:</h4>
        <br>[[INVOICE ID]] - invoice ID,
        <br>[[INVOICE SHOW URL]] - invoice url,
        <br>[[INVOICE AMOUNT DUE]] - invoice amount due,
        <br>[[INVOICE DUE DATE AND DAYS AGO]] - invoice due date

        <h4>Budget:</h4>
        <br>[[PROJECT PRICE LIST]] - project price list (with quotes '...' if not default),
        <br>[[PROJECT APPROVED BUDGET]] - project approved budget,
        <br>[[PROJECT REMAINING BUDGET]] - project remaining budget,
        <br>[[PROJECT OVER BUDGET]] - project over budget amount (the same as negative project remaining budget),
        <br>[[PROJECT SUBSIDY]] - project subsidy,
        <br>[[PROJECT VALUE]] - project value (invoiced or not),

        <br>[[PROJECT NUMBER INVOICES]] - number of the latest invoices for this project,

        <br>[[PROJECT NUMBER OUTSTANDING INVOICES]] - number of the outstanding invoices (issued-unpaid, partially paid) for this project,
        <br>[[PROJECT AMOUNT OUTSTANDING INVOICES]] - amount of the outstanding invoices (issued-unpaid, partially paid) for this project,
        <br>[[PROJECT NUMBER PAID INVOICES]] - number of the paid invoices (Paid in Full) for this project,
        <br>[[PROJECT AMOUNT PAID INVOICES]] - amount of the paid invoices (Paid in Full) for this project,

        <br>[[REQUEST VALUE]] - work request amount (invoiced or not),
        <br>[[PROJECT VALUE WITHOUT INVOICES]] - amount for work requests without invoices for this project,
        ";

        //$notations = "No invoice";
        $invoice = NULL;
        $params = array();

        //$invoices = $transresRequestUtil->getOverdueInvoices();
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $repository = $em->getRepository(Invoice::class);
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');

        $dql->leftJoin('invoice.transresRequest','transresRequest');
        $dql->leftJoin('transresRequest.project','project');
        $dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $dql->where("invoice.latestVersion = TRUE");

        $dql->andWhere("invoice.status = :status"); //Unpaid/Issued
        $params["status"] = "Pending";
        //$params["status"] = "active";

        $dql->orderBy("invoice.id","DESC");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        if( count($params) > 0 ) {
            $query->setParameters($params);
        }

        $invoices = $query->getResult();

        //$invoices = $em->getRepository('AppTranslationalResearchBundle:Invoice')->findAll();

        echo "invoices=".count($invoices)."<br>";

        if( count($invoices) > 0 ) {
            $invoice = $invoices[0];
            echo "invoice OID=".$invoice->getOid()."<br>";
        }

        if( $invoice ) {
            //echo "2invoice=".$invoice."<br>";
            $transresRequest = $invoice->getTransresRequest();
            $project = $transresRequest->getProject();
            $notations = $transresUtil->replaceTextByNamingConvention($notations, $project, $transresRequest, $invoice);
            //echo "notations=".$notations."<br>";
        } else {
            $notations = "No invoice";
        }

        exit($notations);
    }


    #[Route(path: '/transres-test-email-notation-ajax', name: 'translationalresearch_test_email_notation_ajax', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function getTransResTestEmailNotationAjaxAction(Request $request) {

        //$transresUtil = $this->container->get('transres_util');
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();

        //$newline = "\n";
        $newline =  "<br>\n";
        $invoice = NULL;

        $invoiceId = $request->get('invoiceId');
        $specialtyId = $request->get('specialtyId');

        //echo "invoiceId=$invoiceId<br>";

        if( $invoiceId ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
            $invoice = $em->getRepository(Invoice::class)->find($invoiceId);
        }

        if( $invoice ) {
            $transresRequest = $invoice->getTransresRequest();
            $project = $transresRequest->getProject();
        } else {
            $transresRequest = NULL;
            $project = NULL;
        }

        $emailText = "";

        $fields = array(

            //Project Request Settings
            'specimenDetailsComment',
            'messageToUsers',
            'humanTissueFormNote',

            //Project Computational Pathology Email
            'compEmailSubject',
            'compEmailBody',

            //Project Expiration Settings (Only for Non-Funded Projects)
            'expiringProjectEmailSubject',
            'expiringProjectEmailBody',
            'expiredProjectEmailSubject',
            'expiredProjectEmailBody',

            //Project Reactivation
            'projectReactivationSubject',
            'projectReactivationBody',

            //Work Request Default Settings
            'requestCompletedNotifiedEmailSubject',
            'requestCompletedNotifiedEmail',
            'emailNoteConcern',

            //Packing Slip Default Settings
            'transresPackingSlipTitle',
            'transresPackingSlipHeadline1',
            'transresPackingSlipHeadline2',

            //Invoice Default Settings
            'transresNotificationEmailSubject',
            'transresNotificationEmail',

            //Reminder Email Settings
            'invoiceReminderSubject',
            'invoiceReminderBody',

            'projectReminderSubjectreview',
            'projectReminderBodyreview',

            'projectReminderSubjectmissinginfo',
            'projectReminderBodymissinginfo',

            'pendingRequestReminderSubject',
            'pendingRequestReminderBody',

            'completedRequestReminderSubject',
            'completedRequestReminderBody',

            'completedNoInvoiceRequestReminderSubject',
            'completedNoInvoiceRequestReminderBody',

            //Budget Email Settings
            'overBudgetSubject',
            'overBudgetBody',

            'approvedBudgetSubject',
            'approvedBudgetBody',
            'budgetLimitRemovalSubject',
            'budgetLimitRemovalBody'

        );

        foreach($fields as $field) {
            $text = $this->getEmailNotation($field, $project, $transresRequest, $invoice, $specialtyId);
            if( $text ) {
                $emailText = $emailText . $text . $newline . $newline;
            }
        }

        //exit("emailText=<br><br>".$emailText);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($emailText));
        return $response;
    }
    public function getEmailNotation( $field, $project, $transresRequest, $invoice, $specialtyId) {
        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        //$newline = "\n";
        //$newline = "<br>";
        $newline =  "<br>\n";

        $testing = true;
        $projectSpecialty = NULL;

        if( $specialtyId && $specialtyId != 'default' ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
            $projectSpecialty = $em->getRepository(SpecialtyList::class)->find($specialtyId);
            $useDefault = false;
        } else {
            $useDefault = true;
        }

        if( $specialtyId == 'default' ) {
            $projectSpecialty = NULL;
            $useDefault = true;
            $text = $transresUtil->getTransresSiteProjectParameter($field,$project,$projectSpecialty,$useDefault,$testing);
        } else {
            $text = $transresUtil->getTransresSiteProjectParameter($field,$project,$projectSpecialty,$useDefault,$testing);
            if( !$text ) {
                $projectSpecialty = NULL;
                $useDefault = false;
                $text = $transresUtil->getTransresSiteProjectParameter($field,$project,$projectSpecialty,$useDefault,$testing);
            }
        }

        $text = $transresUtil->replaceTextByNamingConvention($text, $project, $transresRequest, $invoice);

        //exit("text=<br><br>".$text);

        return $text;

//        if( $text ) {
//            $restext = $restext . $newline.$newline.
////                "<b>".$field."</b>".
////                "<b>".$field." (".$projectSpecialty.")"."</b>".
////                    $field.
////                ":".$newline.$text;
//                $text
//            ;
//        }
//
//        return $restext;
    }


    /**
     * http://127.0.0.1/order/translational-research/test-trp-site-parameters
     */
    #[Route(path: '/test-trp-site-parameters', name: 'translationalresearch_test_trp_site_parameters')]
    public function testTrpSiteParametersAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        //exit("emailNotationTestAction: Not allowed");

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $projectSpecialty = $em->getRepository(SpecialtyList::class)->findOneByName("AP/CP");

        //$invoices = $transresRequestUtil->getOverdueInvoices();
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $dql->andWhere("projectSpecialty.id = :projectSpecialtyId"); //Unpaid/Issued
        $params["projectSpecialtyId"] = $projectSpecialty->getId();
        //$params["status"] = "active";

        $dql->orderBy("project.id","DESC");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        if( count($params) > 0 ) {
            $query->setParameters($params);
        }

        $projects = $query->getResult();

        //$invoices = $em->getRepository('AppTranslationalResearchBundle:Invoice')->findAll();

        echo "projects=".count($projects)."<br>";

        $project = NULL;

        if( count($projects) > 0 ) {
            $project = $projects[0];
            echo "Project OID=".$project->getOid()."<br>";
        }

        if( $project ) {

            //$fieldName, $project=null, $projectSpecialty=null, $useDefault=false, $testing=false
            $approvedBudgetSendEmail = $transresUtil->getTransresSiteProjectParameter('approvedBudgetSendEmail',$project,null,false,true);
            echo "approvedBudgetSendEmail=".$approvedBudgetSendEmail."<br>";
            $approvedBudgetSendEmail = $transresUtil->getTransresSiteProjectParameter('approvedBudgetSendEmail',$project);
            echo "approvedBudgetSendEmailStr=".$this->getBooleanStr($approvedBudgetSendEmail)."<br>";

            $overBudgetSendEmail = $transresUtil->getTransresSiteProjectParameter('overBudgetSendEmail',$project,null,false,true);
            echo "overBudgetSendEmail=".$overBudgetSendEmail."<br>";
            $overBudgetSendEmail = $transresUtil->getTransresSiteProjectParameter('overBudgetSendEmail',$project);
            echo "overBudgetSendEmailStr=".$this->getBooleanStr($overBudgetSendEmail)."<br>";

            exit("exit");

        } else {
            $res = "No project";
        }

        exit($res);
    }
    public function getBooleanStr($value) {
        $valueStr = "Unknown";
        if ($value === NULL) {
            $valueStr = "NULL ";
        }
        if ($value === TRUE) {
            $valueStr = "TRUE ";
        }
        if ($value === FALSE) {
            $valueStr = "FALSE ";
        }
        return $valueStr;
    }

    /**
     * http://127.0.0.1/order/index_dev.php/translational-research/reverse-fee-schedule-list
     */
    #[Route(path: '/reverse-fee-schedule-list/', name: 'translationalresearch_reverse_fee_schedule_list')]
    public function reverseFeeScheduleListAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("reverseFeeScheduleListAction not allowed");

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        $abbreviations = array(
            "hematopathology",
            "ap-cp",
            "covid19",
            "misi",
            "uscap",
            "ap",
            "cp"
        );

        //new specialties
//        "uscap"
//        "ap"
//        "cp"

        //SpecialtyList
        $projectSpecialties = array();
        foreach($abbreviations as $abbreviation) {
            $projectSpecialtyObject = $transresUtil->getSpecialtyObject($abbreviation);
            if( $projectSpecialtyObject ) {
                $projectSpecialties[$abbreviation] = $projectSpecialtyObject;
            } else {
                echo "Project specialty $abbreviation not found <br>";
            }
        }


        $query = $em->createQueryBuilder()
            //->from('AppTranslationalResearchBundle:RequestCategoryTypeList', 'list')
            ->from(RequestCategoryTypeList::class, 'list')
            ->select("list")
            ->orderBy("list.orderinlist","ASC");

//        $now = new \DateTime('now');
//        $updateonDateStr = $now->format('Y-m-d H:i:s');
//        //2021-05-27 13:37:29
//        //                 2021-05-27 20:22:02 <= '2021-05-27 20:20:38'
//        echo "updateonDateStr=$updateonDateStr < '2021-05-27 20:20:38'<br>";
//        $query->where("list.updatedon IS NULL OR list.updatedon < '$updateonDateStr'");

        //$query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        $fees = $query->getQuery()->getResult();

        $testing = true;
        //$testing = false;

        echo "testing=$testing <br>";
        echo "fees count=".count($fees)."<br><br>";

        $infoArr = array();
        $count = 0;
        $updateCount = 0;
        foreach($fees as $fee) {

            $count++;

            if( count($fee->getProjectSpecialties()) == 0 ) {
                echo "Skip $fee <br>";
                continue;
            }

            $thisAbbreviationsStr = "";
            $diffStr = "";
            $thisAbbreviations = array();

            $specialties = $fee->getProjectSpecialties();
            foreach($specialties as $specialty) {
                $thisAbbreviations[] = $specialty->getAbbreviation();
            }

            if( count($thisAbbreviations) > 0 ) {
                $thisAbbreviationsStr = implode(", ",$thisAbbreviations);
            }

            $diff = array_diff($abbreviations,$thisAbbreviations);

//            if( count($diff) > 0 ) {
//                $diffStr = implode(", ",$diff);
//            }

            //$info = "ID ".$fee->getId()." (".$fee->getProductId()."): $fee [".$thisAbbreviationsStr. "]=>[" . $diffStr ."]";
            $info1 = "ID ".$fee->getId()." (".$fee->getProductId()."): $fee [".$thisAbbreviationsStr. "]";
            //echo $info1."<br>";

            if( count($diff) > 0 ) {
                $fee->clearProjectSpecialties();

                foreach($diff as $thisAbbreviation) {
                    $projectSpecialty = $projectSpecialties[$thisAbbreviation];
                    $fee->addProjectSpecialty($projectSpecialty);
                    //echo $projectSpecialty->getAbbreviation()." ";
                }
                //echo "<br><br>";

                if( !$testing ) {
                    $em->flush();
                }

                $updateCount++;
            }

            $resAbbreviations = array();
            $specialties = $fee->getProjectSpecialties();
            foreach($specialties as $specialty) {
                $resAbbreviations[] = $specialty->getAbbreviation();
            }
            $info2 = " => Not available for [".implode(",",$resAbbreviations)."]<br>";

            $info = $info1 . $info2;
            //echo $info;

            //eventlog
            $eventType = "List Updated";
            $msgSpecialtyChanged = "Updated fee schedule ".$info;
            //echo $msgSpecialtyChanged;
            if( !$testing ) {
                $transresUtil->setEventLog($fee, $eventType, $msgSpecialtyChanged);
            }

            $infoArr[] = $info;

            //break;

        }//foreach fess

        if( count($infoArr) > 0 ) {
            $infoStr = implode("", $infoArr);
            echo "<br><br>".$infoStr;

            //eventlog
            $eventType = "List Updated";
            $msgSpecialtyChanged = "Fee schedule updated:<br>".$infoStr;
            if( !$testing ) {
                $transresUtil->setEventLog(null, $eventType, $msgSpecialtyChanged);
            }
        }


        exit("EOF reverseFeeScheduleListAction: total=$count, updated=$updateCount");
    }

    /**
     * http://127.0.0.1/order/index_dev.php/translational-research/update-products-in-work-requests
     */
    #[Route(path: '/update-products-in-work-requests/', name: 'translationalresearch_update_products_in_work_requests')]
    public function updateProductsInWorkRequestsAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("updateProductsInWorkRequestsAction not allowed");

        $em = $this->getDoctrine()->getManager();
        //$transresUtil = $this->container->get('transres_util');

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $totalWorkRequests = $em->getRepository(TransResRequest::class)->findAll();
        echo "totalWorkRequests=".count($totalWorkRequests)."<br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Product'] by [Product::class]
        $totalproducts = $em->getRepository(Product::class)->findAll();
        echo "totalproducts=".count($totalproducts)."<br>";

        $this->notCompletedWorkRequests();
        $this->productsWithStatus();

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:OrderableStatusList'] by [OrderableStatusList::class]
        $completedProductStatus = $em->getRepository(OrderableStatusList::class)->findOneByName("Completed");
        if( !$completedProductStatus ) {
            exit("OrderableStatusList not found by name 'Completed'");
        }

        //find all products with work completed requests
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Product'] by [Product::class]
        $repository = $em->getRepository(Product::class);
        $dql =  $repository->createQueryBuilder("product");
        $dql->select('product');

        $dql->leftJoin('product.transresRequest','transresRequest');
        $dql->leftJoin('product.orderableStatus','orderableStatus');

        $dql->where("transresRequest IS NOT NULL");
        $dql->andWhere("orderableStatus IS NULL");

        $dql->andWhere("transresRequest.progressState = 'completed' OR transresRequest.progressState = 'completedNotified'");

        $dql->orderBy("product.id","DESC");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $products = $query->getResult();

        echo "products=".count($products)."<br>";

        $count = 0;
        $updateCount = 0;
        $updatedProducts = array();
        $transresRequests = array();

        $testing = false;
        //$testing = true;

        foreach($products as $product) {
            $transresRequest = $product->getTransresRequest();
            //echo "transresRequest=".$transresRequest->getOid()."<br>";

            $progressState = $transresRequest->getProgressState();
            echo $count.": ".$transresRequest->getOid().": progressState=".$progressState."<br>";
            if( $progressState != 'completed' && $progressState != 'completedNotified' ) {
                continue; //skip
            }

            $transresRequests[$transresRequest->getOid()] = $transresRequest->getOid();

            $currentProductStatus = $product->getOrderableStatus();
            //if( !$currentProductStatus || $currentProductStatus && $currentProductStatus->getId() != $completedProductStatus->getId() ) {
            if( !$currentProductStatus ) {
                if( $testing == false ) {
                    $product->setOrderableStatus($completedProductStatus);
                }
                $updateCount++;
                $updatedProducts[] = $product->getId();
            }
            $count++;
        }

        if( $updateCount > 0 ) {
            if( $testing == false ) {
                $em->flush();
            }
            echo "flushed <br>";

            $user = $this->getUser();
            $eventType = "Request Updated";
            $transresUtil = $this->container->get('transres_util');
            $msgInfo = "Products have been set to 'Completed' by updateProductsInWorkRequestsAction by " . $user . " (total products ".count($updatedProducts)."): <br>" . implode(", ",$updatedProducts);
            echo "msgInfo=".$msgInfo."<br>";
            if( $testing == false ) {
                $transresUtil->setEventLog(null,$eventType,$msgInfo);
            }

            $msgInfo = "Work Request's products have been set to 'Completed' by updateProductsInWorkRequestsAction by " . $user . " (total work requests ".count($transresRequests)."): <br>" . implode(", ",$transresRequests);
            echo "msgInfo=".$msgInfo."<br>";
            if( $testing == false ) {
                $transresUtil->setEventLog(null,$eventType,$msgInfo);
            }
        }

        //echo "transresRequests=".count($transresRequests)."<br>";
        //dump($transresRequests);

        exit("EOF updateProductsInWorkRequestsAction: total=$count, updated=$updateCount");
    }
    public function notCompletedWorkRequests() {
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $repository = $em->getRepository(TransResRequest::class);
        $dql =  $repository->createQueryBuilder("transresRequest");
        $dql->select('transresRequest');

        $dql->andWhere("transresRequest.progressState != 'completed' AND transresRequest.progressState != 'completedNotified'");

        $dql->orderBy("transresRequest.id","DESC");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $transresRequests = $query->getResult();
        echo "Not completed transresRequests=".count($transresRequests)."<br>";
    }
    public function productsWithStatus() {
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Product'] by [Product::class]
        $repository = $em->getRepository(Product::class);
        $dql =  $repository->createQueryBuilder("product");
        $dql->select('product');

        $dql->leftJoin('product.transresRequest','transresRequest');
        $dql->leftJoin('product.orderableStatus','orderableStatus');

        $dql->where("transresRequest IS NOT NULL");
        $dql->andWhere("orderableStatus IS NOT NULL");

        $dql->orderBy("transresRequest.id","DESC");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $products = $query->getResult();
        echo "Products with status=".count($products)."<br>";
    }

    /**
     * http://127.0.0.1/order/index_dev.php/translational-research/update-not-completed-products-in-work-requests/
     */
    #[Route(path: '/update-not-completed-products-in-work-requests/', name: 'translationalresearch_update_not_completed_products_in_work_requests')]
    public function updateNotCompletedProductsInWorkRequestsAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("updateNotCompletedProductsInWorkRequestsAction not allowed");

        //set all orderables that belong to the “Completed” work requests to “Completed”,
        // all orderables that belong to “Canceled” work requests to “Canceled by Performer”,
        // and all the rest should be set to status of “Requested”

        $em = $this->getDoctrine()->getManager();
        //$transresUtil = $this->container->get('transres_util');

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $totalWorkRequests = $em->getRepository(TransResRequest::class)->findAll();
        echo "totalWorkRequests=".count($totalWorkRequests)."<br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Product'] by [Product::class]
        $totalproducts = $em->getRepository(Product::class)->findAll();
        echo "totalproducts=".count($totalproducts)."<br>";

        $this->notCompletedWorkRequests();
        $this->productsWithStatus();

        //Canceled by Performer
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:OrderableStatusList'] by [OrderableStatusList::class]
        $canceledProductStatus = $em->getRepository(OrderableStatusList::class)->findOneByName("Canceled by Performer");
        if( !$canceledProductStatus ) {
            exit("OrderableStatusList not found by name 'Canceled by Performer'");
        }

        //Requested
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:OrderableStatusList'] by [OrderableStatusList::class]
        $requestedProductStatus = $em->getRepository(OrderableStatusList::class)->findOneByName("Requested");
        if( !$requestedProductStatus ) {
            exit("OrderableStatusList not found by name 'Requested'");
        }

        //find all products with work completed requests
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Product'] by [Product::class]
        $repository = $em->getRepository(Product::class);
        $dql =  $repository->createQueryBuilder("product");
        $dql->select('product');

        $dql->leftJoin('product.transresRequest','transresRequest');
        $dql->leftJoin('product.orderableStatus','orderableStatus');

        $dql->where("transresRequest IS NOT NULL");
        $dql->andWhere("orderableStatus IS NULL");

        $dql->andWhere("transresRequest.progressState != 'completed' AND transresRequest.progressState != 'completedNotified'");

        $dql->orderBy("product.id","DESC");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $products = $query->getResult();

        echo "Not completed products=".count($products)."<br>";

        $count = 0;
        $updateCountCanceled = 0;
        $updateCountRequested = 0;
        $updatedProductsCanceled = array();
        $updatedProductsRequested = array();
        $transresRequests = array();

        $testing = false;
        //$testing = true;

        foreach($products as $product) {
            $transresRequest = $product->getTransresRequest();
            //echo "transresRequest=".$transresRequest->getOid()."<br>";

            $progressState = $transresRequest->getProgressState();
            //echo $count.": ".$transresRequest->getOid().": progressState=".$progressState."<br>";
            if( $progressState == 'completed' || $progressState == 'completedNotified' ) {
                continue; //skip
            }

            $transresRequests[$transresRequest->getOid()] = $transresRequest->getOid();

            $currentProductStatus = $product->getOrderableStatus();
            //if( !$currentProductStatus || $currentProductStatus && $currentProductStatus->getId() != $completedProductStatus->getId() ) {
            if( !$currentProductStatus ) {
                if( $progressState == 'canceled' ) {
                    if( $testing == false ) {
                        $product->setOrderableStatus($canceledProductStatus);
                    }
                    //echo $count.": ".$transresRequest->getOid()." (".$progressState."): progressState=[".$currentProductStatus."]=>[".$canceledProductStatus."]<br>";
                    echo $count.": ".$transresRequest->getOid().": [".$progressState."]=>[".$canceledProductStatus."]<br>";
                    $updateCountCanceled++;
                    $updatedProductsCanceled[] = $product->getId();
                } else {
                    if( $testing == false ) {
                        $product->setOrderableStatus($requestedProductStatus);
                    }
                    //echo $count.": ".$transresRequest->getOid()." (".$progressState."): progressState=[".$currentProductStatus."]=>[".$requestedProductStatus."]<br>";
                    echo $count.": ".$transresRequest->getOid().": [".$progressState."]=>[".$requestedProductStatus."]<br>";
                    $updateCountRequested++;
                    $updatedProductsRequested[] = $product->getId();
                }
            }
            $count++;
        }

        if( $updateCountCanceled > 0 || $updateCountRequested ) {
            if( $testing == false ) {
                $em->flush();
            }
            echo "flushed <br>";

            $transresUtil = $this->container->get('transres_util');
            $user = $this->getUser();
            $eventType = "Request Updated";

            //Canceled
            $msgInfo = "Products have been set to 'Canceled' by updateNotCompletedProductsInWorkRequestsAction by " . $user .
                " (total products ".count($updatedProductsCanceled)."): <br>" . implode(", ",$updatedProductsCanceled);
            echo "msgInfo=".$msgInfo."<br>";
            if( $testing == false ) {
                $transresUtil->setEventLog(null,$eventType,$msgInfo);
            }

            //Requested
            $msgInfo = "Products have been set to 'Requested' by updateNotCompletedProductsInWorkRequestsAction by " . $user .
                " (total products ".count($updatedProductsRequested)."): <br>" . implode(", ",$updatedProductsRequested);
            echo "msgInfo=".$msgInfo."<br>";
            if( $testing == false ) {
                $transresUtil->setEventLog(null,$eventType,$msgInfo);
            }

            $msgInfo = "Work Request's products have been set to 'Completed' by updateProductsInWorkRequestsAction by " . $user .
                " (total work requests ".count($transresRequests)."): <br>" . implode(", ",$transresRequests);
            echo "msgInfo=".$msgInfo."<br>";
            if( $testing == false ) {
                $transresUtil->setEventLog(null,$eventType,$msgInfo);
            }
        }

        //echo "transresRequests=".count($transresRequests)."<br>";
        //dump($transresRequests);

        exit("EOF updateProductsInWorkRequestsAction: total=$count, updateCountCanceled=$updateCountCanceled, updateCountRequested=$updateCountRequested");
    }

    /**
     * http://127.0.0.1/order/index_dev.php/translational-research/populate-project-expiration-date
     */
    #[Route(path: '/populate-project-expiration-date/', name: 'translationalresearch_populate_project_expiration_date')]
    public function populateProjectExpectedExpirationDateAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("populateProjectExpectedExpirationDateAction not allowed");

        ini_set('max_execution_time', 900); //900 seconds = 15 minutes

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        $testing = false;
        //$testing = true;

        $count = 0;
        $updateCount = 0;
        $closedCount = 0;
        $approvedCount = 0;
        $updatedProjects = array();

        //find all projects without expiration date
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');
        $dql->where("project.expectedExpirationDate IS NULL");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        //$query->setParameters($params);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();
        echo "projects=".count($projects)."<br>";

        foreach($projects as $project) {

            if( $project->getExpectedExpirationDate() ) {
                continue;
            }

            //                                                          $project, $useProjectSubmissionDate=false
            $res = $transresUtil->calculateAndSetProjectExpectedExprDate($project,true);

            if( $res ) {

                $updatedProjects[] = $res; //$project->getId();

                $status = $project->getState();
                if( $status == 'closed' || $status == 'canceled' ) {
                    $closedCount++;
                }
                if( $status == 'final_approved' ) {
                    $approvedCount++;
                }

                if( $testing == false ) {
                    $em->flush();
                }

                $updateCount++;
            }

            $count++;
        }

        if( $updateCount > 0 ) {
            if ($testing == false) {
                $em->flush();
            }
        }

        echo "Total=".$count.", approvedCount=".$approvedCount.", closedCount=".$closedCount.", updatedCount=".$updateCount."<br>";

        //EventLog
        $msg = "Populated projects expectedExpirationDate: ".implode("; ",$updatedProjects)."<br>";
        echo "$msg <br>";

        if( $testing == false ) {
            $eventType = "Project Updated";
            $transresUtil->setEventLog(null,$eventType,$msg);
        }

        exit("EOF populateProjectExpectedExpirationDateAction: total=$count, updated=$updateCount");
    }

    /**
     * http://127.0.0.1/order/index_dev.php/translational-research/reset-project-expiration-date
     */
    #[Route(path: '/reset-project-expiration-date/', name: 'translationalresearch_reset_project_expiration_date')]
    public function resetProjectExpectedExpirationDateAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("resetProjectExpectedExpirationDateAction not allowed");

        ini_set('max_execution_time', 900); //900 seconds = 15 minutes

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        $testing = false;
        //$testing = true;

        $count = 0;
        $updateCount = 0;
        $closedCount = 0;
        $approvedCount = 0;
        $updatedProjects = array();

        //find all projects without expiration date
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->where("project.approvalDate IS NOT NULL");

        //non-funded
        $dql->andWhere("project.funded != TRUE");

        //state
        //$dql->andWhere("project.state = 'final_approved'");

        //$dql->andWhere("project.expectedExpirationDate IS NOT NULL");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        //$query->setParameters($params);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();
        echo "projects=".count($projects)."<br>";

        foreach($projects as $project) {

//            if( $project->getExpectedExpirationDate() ) {
//                continue;
//            }

            $originalExpDateStr = NULL;
            $originalExpDate = $project->getExpectedExpirationDate();
            if( $originalExpDate ) {
                $originalExpDateStr = $originalExpDate->format('d-m-Y');
            }

            $approvalDateStr = NULL;
            $approvalDate = $project->getApprovalDate();
            if( $approvalDate ) {
                $approvalDateStr = $approvalDate->format('d-m-Y');
            }

            $origExpiringCounter = $project->getExpirationNotifyCounter();
            $origExpiredCounter = $project->getExpiredNotifyCounter();

            //                                                          $project, $useProjectSubmissionDate=false, $useProjectApprovalDate=true
            $res = $transresUtil->calculateAndSetProjectExpectedExprDate($project,false,true);

            $expectedExprDateStr = NULL;
            $expectedExprDate = $project->getExpectedExpirationDate();
            if( $expectedExprDate ) {
                $expectedExprDateStr = $expectedExprDate->format('d-m-Y');
            }

            $expiringCounter = $project->getExpirationNotifyCounter();
            $expiredCounter = $project->getExpiredNotifyCounter();

            $newRes = $count.": ".$project->getOid().": approvalDate=".$approvalDateStr.
                ", Original exprDate=".$originalExpDateStr.
                ", new exprDate=".$expectedExprDateStr.
                ", expiringCounter=$origExpiringCounter->$expiringCounter, expiredCounter=$origExpiredCounter->$expiredCounter"
            ;
            //echo $newRes."<br>";
            $updatedProjects[] = $newRes;

            if( $res ) {

                //$updatedProjects[] = $res; //$project->getId();

                $status = $project->getState();
                if( $status == 'closed' || $status == 'canceled' ) {
                    $closedCount++;
                }
                if( $status == 'final_approved' ) {
                    $approvedCount++;
                }

                if( $testing == false ) {
                    $em->flush();
                }

                $updateCount++;
            }

            $count++;
        }

        if( $updateCount > 0 ) {
            if ($testing == false) {
                $em->flush();
            }
        }

        echo "Total=".$count.", approvedCount=".$approvedCount.", closedCount=".$closedCount.", updatedCount=".$updateCount."<br>";

        //EventLog
        $break = "<br>";
        //$break = "; ";
        $msg = "Reset projects expectedExpirationDate: ".implode($break,$updatedProjects)."<br>";
        echo "$msg <br>";

        if( $testing == false ) {
            $eventType = "Project Updated";
            $transresUtil->setEventLog(null,$eventType,$msg);
        }

        exit("EOF resetProjectExpectedExpirationDateAction: total=$count, updated=$updateCount");
    }

    //Clear out/delete values in the “Expected expiration date” fields for all “funded” projects so they would not expire
    //If project switch from funded to non-funded?
    /**
     * http://127.0.0.1/order/index_dev.php/translational-research/clear-funded-project-expiration-date
     */
    #[Route(path: '/clear-funded-project-expiration-date/', name: 'translationalresearch_clear_funded_project_expiration_date')]
    public function clearFundedProjectExpectedExpirationDateAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("clearFundedProjectExpectedExpirationDateAction not allowed");

        ini_set('max_execution_time', 900); //900 seconds = 15 minutes

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        $testing = false;
        //$testing = true;

        $count = 0;
        //$updateCount = 0;
        //$closedCount = 0;
        //$approvedCount = 0;
        $updatedProjects = array();

        //find all projects without expiration date
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        //$dql->where("project.approvalDate IS NOT NULL");

        //non-funded
        //$dql->andWhere("project.funded != TRUE");

        //funded
        $dql->andWhere("project.funded = TRUE");

        //state
        //$dql->andWhere("project.state = 'final_approved'");

        $dql->andWhere("project.expectedExpirationDate IS NOT NULL");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        //$query->setParameters($params);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();
        echo "projects=".count($projects)."<br>";

        foreach($projects as $project) {

//            if( $project->getExpectedExpirationDate() ) {
//                continue;
//            }

            $originalExpDateStr = NULL;
            $originalExpDate = $project->getExpectedExpirationDate();
            if( $originalExpDate ) {
                $originalExpDateStr = $originalExpDate->format('d-m-Y');
            }

            $approvalDateStr = NULL;
            $approvalDate = $project->getApprovalDate();
            if( $approvalDate ) {
                $approvalDateStr = $approvalDate->format('d-m-Y');
            }

            $origExpiringCounter = $project->getExpirationNotifyCounter();
            $origExpiredCounter = $project->getExpiredNotifyCounter();

            //clear expectedExpirationDate and counters
            $project->setExpectedExpirationDate(NULL);
            $project->setExpirationNotifyCounter(0);
            $project->setExpiredNotifyCounter(0);

            $expectedExprDateStr = NULL;
            $expectedExprDate = $project->getExpectedExpirationDate();
            if( $expectedExprDate ) {
                $expectedExprDateStr = $expectedExprDate->format('d-m-Y');
            }

            $newRes = $count.": ".$project->getOid().
                ": Clear exprDate=".$originalExpDateStr.
                ", funded=".$project->isFunded()
                //", new exprDate=".$expectedExprDateStr.
                //", expiringCounter=$origExpiringCounter, expiredCounter=$origExpiredCounter"
            ;
            //echo $newRes."<br>";
            $updatedProjects[] = $newRes;


            if( $testing == false ) {
                $em->flush();
            }

            //$updateCount++;

            $count++;
        }

        if( $count > 0 ) {
            if ($testing == false) {
                $em->flush();
            }
        }

        //echo "Total=".$count.", approvedCount=".$approvedCount.", closedCount=".$closedCount.", updatedCount=".$updateCount."<br>";
        echo "count=".$count."<br>";

        //EventLog
        $break = "<br>";
        //$break = "; ";
        $msg = "Clear projects expectedExpirationDate: <br>".implode($break,$updatedProjects)."<br>";
        echo "$msg <br>";

        if( $testing == false ) {
            $eventType = "Project Updated";
            $transresUtil->setEventLog(null,$eventType,$msg);
        }

        exit("EOF clearFundedProjectExpectedExpirationDateAction: total=$count");
    }

    /**
     * Clear expectedExpirationDate for all non-funded projects without Approval date
     *
     * http://127.0.0.1/order/index_dev.php/translational-research/clear-nonfunded-project-expdate-without-approvaldate
     */
    #[Route(path: '/clear-nonfunded-project-expdate-without-approvaldate/', name: 'translationalresearch_clear_nonfunded_project_expdate_without_approvaldate')]
    public function clearNonFundedProjectExpectedExpdateAction(Request $request) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        exit("clearNonFundedProjectExpectedExpdateAction not allowed");

        ini_set('max_execution_time', 900); //900 seconds = 15 minutes

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        $testing = false;
        //$testing = true;

        $count = 0;
        $updatedProjects = array();

        //find all projects without expiration date
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        //$dql->where("project.approvalDate IS NOT NULL");

        //non-funded
        $dql->andWhere("project.funded != TRUE");

        //no approval date
        $dql->andWhere("project.approvalDate IS NULL");

        //state
        //$dql->andWhere("project.state = 'final_approved'");

        $dql->andWhere("project.expectedExpirationDate IS NOT NULL");

        $dql->orderBy("project.expectedExpirationDate","ASC");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        //$query->setParameters($params);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();
        echo "projects=".count($projects)."<br>";

        foreach($projects as $project) {

            $originalExpDateStr = NULL;
            $originalExpDate = $project->getExpectedExpirationDate();
            if( $originalExpDate ) {
                $originalExpDateStr = $originalExpDate->format('d-m-Y');
            }

            //clear expectedExpirationDate and counters
            $project->setExpectedExpirationDate(NULL);
            $project->setExpirationNotifyCounter(0);
            $project->setExpiredNotifyCounter(0);

            $newRes = $count.": ".$project->getOid().
                " [".$project->getState()."]".
                ": Clear exprDate=".$originalExpDateStr.
                ", funded=".$project->isFunded()
                //", new exprDate=".$expectedExprDateStr.
                //", expiringCounter=$origExpiringCounter, expiredCounter=$origExpiredCounter"
            ;
            //echo $newRes."<br>";
            $updatedProjects[] = $newRes;

            if( $testing == false ) {
                $em->flush();
            }

            $count++;
        }

        if( $count > 0 ) {
            if ($testing == false) {
                $em->flush();
            }
        }

        echo "Total=".$count."<br>";

        //EventLog
        $break = "<br>";
        //$break = "; ";
        $msg = "Clear non-funded projects expectedExpirationDate without Approval date:<br>".implode($break,$updatedProjects)."<br>";
        echo "$msg <br>";

        if( $testing == false ) {
            $eventType = "Project Updated";
            $transresUtil->setEventLog(null,$eventType,$msg);
        }

        exit("EOF clearNonFundedProjectExpectedExpdateAction: total=$count");
    }


//    #[Route(path: '/multi-level-test/', name: 'translationalresearch_multi-level-test', methods: ['GET'])]
//    #[Template('AppTranslationalResearchBundle/Default/multi-level-test.html.twig')]
//    public function multiLevelMenuTestAction( Request $request ) {
//
//        return array();
//    }

    //Populate and replace $category by $categoryTags
    #[Route(path: '/antibody-category-tag-create/', name: 'translationalresearch_antibody-category-tag-create', methods: ['GET'])]
    public function antibodyCategoryTagsCreateAction( Request $request ) {
        exit("antibodyCategoryTagsCreateAction not allowed");
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $count = 1;

        $repository = $em->getRepository(AntibodyList::class);
        $dql =  $repository->createQueryBuilder("antibody");
        $dql->select('antibody.category');

        $dql->distinct('antibody.category');
        //$dql->groupBy('antibody.category');
        //$dql->addGroupBy('antibody.id');

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        //$query->setParameters($params);
        //echo "query=".$query->getSql()."<br>";

        $antibodys = $query->getResult();
        echo "antibodys=".count($antibodys)."<br>";

        $categoryArr = array();

        foreach($antibodys as $antibody) {
            $name = trim($antibody['category']);
            if( $name ) {
                //echo $count.": original category=[".$name . "]<br>";
                echo $name . "<br>";
            }
        }

        exit('111');
    }
//    public function stringToArray($string, $delimiters) {
//        //return array of string parts separated by arra of delimeters
//        $delimiter_regex = '/[' . preg_quote(implode('', $delimiters), '/') . ']/';
//        return preg_split($delimiter_regex, $string);
//    }

    #[Route(path: '/antibody-add-category-tag/', name: 'translationalresearch_antibody-add-category-tag', methods: ['GET'])]
    public function antibodyAddCategoryTagAction( Request $request ) {
        exit("antibodyAddCategoryTagAction not allowed");
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');

        $user = $this->getUser();
        $count = 0;

        $repository = $em->getRepository(AntibodyList::class);
        $dql =  $repository->createQueryBuilder("antibody");
        $dql->select('antibody');
        $query = $dql->getQuery();
        $antibodys = $query->getResult();
        echo "antibodys=".count($antibodys)."<br>";

        //$categoryTags = $em->getRepository(AntibodyCategoryTagList::class)->findAll();
        //echo "categoryTags=".count($categoryTags)."<br>";

        $antibodyMap = $this->antibodyCategoryMap();

        foreach($antibodys as $antibody) {
            //echo $count.": antibody=[".$antibody . "]<br>";
            $category = $antibody->getCategory()."";
            //echo "### ".$count.": antibody ID=".$antibody->getId().", category=[$category]<br>";

            if( !$category ) {
                continue;
            }

            $category = trim($category);

            //echo "### ".$count.": antibody ID=".$antibody->getId().", category=[$category]<br>";
            $categoryTag = NULL;
            if( isset($antibodyMap[$category]) ) {
                $categoryTag = $antibodyMap[$category];
            }

            if( $categoryTag ) {
                if (is_array($categoryTag)) {
                    foreach ($categoryTag as $singleCategoryTag) {
                        //echo "### " . $count . " category=[$category] == [$singleCategoryTag]<br>";
                        $this->addSingleCategoryTag($antibody,$singleCategoryTag);
                        $count++;
                    }
                } else {
                    //echo "### " . $count . " category=[$category] == [$categoryTag]<br>";
                    $this->addSingleCategoryTag($antibody,$categoryTag);
                    $count++;
                }
            } else {
                echo "### category=[$category] == [NOT DEFINED]<br>";
                //$count++;
            }

        }

        exit('added '.$count." category tags");
    }
    public function addSingleCategoryTag( $antibody, $categoryTag ) {
        $em = $this->getDoctrine()->getManager();
        $categoryTagEntity = $em->getRepository(AntibodyCategoryTagList::class)->findOneByName($categoryTag);
        if( $categoryTagEntity ) {
            if( !$antibody->getCategoryTags()->contains($categoryTagEntity) ) {
                $antibody->addCategoryTag($categoryTagEntity);
                $em->flush();
                echo "ID#".$antibody->getId().": Add category tag $categoryTag <br>";
            } else {
                echo "Category tag $categoryTag already exists <br>";
            }
        } else {
            echo "Category tag not found by name  $categoryTag<br>";
        }
    }
    public function antibodyCategoryMap() {
        //antibodys=38
        //R-red
        //R (DO NOT USE)
        //mT
        //T-US
        //R-M
        //D-T
        //D-M
        //M-red
        //M (?)
        //C-New
        //R/T
        //TUS
        //R-H, M
        //R
        //RTU
        //DO NOT USE
        //        C
        //R-H, Pig
        //D-R
        //M
        //VP_IHC-H
        //test
        //R-RED
        //VP_IHC-Human
        //R-ISH
        //TUS-R
        //C-NEW
        //R-H/M
        //R-M,H
        //TUS-M
        //R-M/H
        //T
        //R-H
        //R-H only
        //VP_IHC-mouse
        //VP_IHC-human
        //M/R
        return array(
            "M" => "Mouse",
            "R-RED" => "Human",
            "R-red" => "Human",
            "mT" => "Failed",
            "R (DO NOT USE)" => "Failed",
            "T-US" => "Failed",
            "R-M" => "Mouse",
            "D-T" => null,
            "D-M" => null,
            "M-red" => "Mouse",
            "M (?)" => "Mouse",
            "C-New" => "Human",
            "R/T" => "Failed",
            "TUS" => "Failed",
            "R-H, M" => array("Human","Mouse"),
            "R" => "Human",
            "RTU" => "Human",
            "DO NOT USE" => "Failed",
            "C" => "Human",
            "R-H, Pig" => array("Human","Pig"),
            "D-R" => null,
            "VP_IHC-H" => "Human",
            "VP_IHC-Human" => "Human",
            "R-ISH" => "In situ hybridization",
            "TUS-R" => "Failed",
            "C-NEW" => "Human",
            "R-H/M" => array("Human","Mouse"),
            "R-M,H" => array("Human","Mouse"),
            "TUS-M" => "Failed",
            "R-M/H" => array("Human","Mouse"),
            "T" => "Failed",
            "R-H" => "Human",
            "R-H only" => "Human",
            "VP_IHC-mouse" => "Mouse",
            "VP_IHC-human" => "Human",
            "M/R" => "Mouse",
        );
    }

    //set antibody's openToPublic according to the categoryTags' openToPublic
    #[Route(path: '/antibody-category-set-public/', name: 'translationalresearch_antibody-category-tag-create', methods: ['GET'])]
    public function antibodyCategorySetPublicAction( Request $request ) {
        exit("antibodyCategorySetPublicAction not allowed");
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$user = $this->getUser();
        $count = 0;

        $repository = $em->getRepository(AntibodyList::class);
        $dql =  $repository->createQueryBuilder("antibody");
        $dql->select('antibody');

        $dql->leftJoin('antibody.categoryTags','categoryTags');

        //$dql->andWhere("antibody.openToPublic = TRUE");

        $dql->andWhere("categoryTags.openToPublic = TRUE");
        $dql->andWhere("(antibody.openToPublic IS NULL)");
        //$dql->andWhere("(antibody.openToPublic IS NULL OR antibody.openToPublic = FALSE)");
        $dql->orderBy("antibody.id","ASC");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $antibodys = $query->getResult();
        echo "public antibodys=".count($antibodys)."<br>";
        //exit('111');

        $categoryArr = array();

        foreach($antibodys as $antibody) {
            //echo $antibody . "<br>";
//            $categoryTags = $antibody->getCategoryTags();
//            foreach($categoryTags as $categoryTag) {
//                echo $antibody->getId()." (".$antibody->getName()."): ".$categoryTag . "<br>";
//            }
            echo $antibody->getId().": ".$antibody->getName(). "<br>";
            $antibody->setOpenToPublic(true);
            $em->flush();
            $count++;
        }

        exit('antibodyCategorySetPublicAction count='.$count);
    }

    #[Route(path: '/populate-project-comment-ihc/', name: 'translationalresearch_populate_project_comment_ihc', methods: ['GET'])]
    public function populateProjectCommentAction( Request $request ) {
        exit("populateProjectCommentAction not allowed");
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        $importUtil = $this->container->get('transres_import');

        $projectDir = $this->container->get('kernel')->getProjectDir();
        echo '$projectDir='.$projectDir.'<br>';
        $filename = $projectDir . DIRECTORY_SEPARATOR. '..' . DIRECTORY_SEPARATOR . 'importLists'.DIRECTORY_SEPARATOR.
            '2023_IHC_BH_single.xls';
        echo '$filename='.$filename.'<br>';

        $importUtil->populateProjectComment($filename,$startRaw=1,$endRow=1400);

        exit('end of populateProjectCommentAction');
    }

    //Set default 'TRP' lab to all existing antibodies
    #[Route(path: '/antibody-set-trp-lab/', name: 'translationalresearch_antibody-set-trp-lab', methods: ['GET'])]
    public function antibodySetTrpLabAction( Request $request ) {
        exit("antibodySetTrpLabAction not allowed");
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        //$user = $this->getUser();
        //$count = 1;

        $em = $this->getDoctrine()->getManager();

        $trpLab = $em->getRepository(AntibodyLabList::class)->findOneByAbbreviation("TRP");
        if( !$trpLab ) {
            exit("AntibodyLabList TRP does not exist");
        }

        $repository = $em->getRepository(AntibodyList::class);
        $dql =  $repository->createQueryBuilder("antibody");
        $dql->select('antibody');

        $dql->leftJoin('antibody.antibodyLabs','antibodyLabs');

        $dql->andWhere("antibodyLabs IS NULL");

        $query = $dql->getQuery();
        //$query->setParameters($params);
        //echo "query=".$query->getSql()."<br>";

        $antibodies = $query->getResult();
        echo "antibodies=".count($antibodies)."<br>";

        $count = 0;

        foreach($antibodies as $antibody) {
            //echo $antibody->getId().": ".$antibody->getName(). "";
            //$name = $antibody->getName();
            $labs = $antibody->getAntibodyLabs();
            if( count($labs) > 0 ) {
                echo "Skip: antibody ".$antibody->getName()." has labs [".$antibody->getAntibodyLabStr()."]<br>";
                continue;
//                echo $antibody->getId().": ".$antibody->getName(). "";
//                foreach ($labs as $lab) {
//                    echo ": " . $lab->getName();
//                }
            } else {
                //Add "TRP" lab
                $antibody->addAntibodyLab($trpLab);
                $em->flush();
                echo $count.": Added lab to ".$antibody->getId().", ".$antibody->getName(). ": [".$antibody->getAntibodyLabStr(). "]<br>";
                $count++;
            }
            //echo "<br>";
        }
        //$em->flush();

        exit('Added lab to antibodies ='.$count);
    }


    //127.0.0.1/translational-research/antibody-create-panels
    #[Route(path: '/antibody-create-panels/', name: 'translationalresearch_antibody-create-panels', methods: ['GET'])]
    public function antibodyCreatePanelsAction( Request $request )
    {
        exit("antibodyCreatePanelsAction not allowed");
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');

        $projectRoot = $this->container->get('kernel')->getProjectDir(); // ....../orderflex
        echo "projectRoot=$projectRoot <br>";

        $pdfFilePath = $projectRoot . "/src/App/TranslationalResearchBundle/Util/MISI_Antibody_Panels.pdf";
        echo "pdfFilePath=$pdfFilePath <br>";
        if (file_exists($pdfFilePath)) {
            echo "The file $pdfFilePath exists";
        } else {
            echo "The file $pdfFilePath does not exist";
        }

        //Try to read PDF => panels are not shown => useless
        //$resappPdfUtil = $this->container->get('resapp_pdfutil');
        //$pdfText = $resappPdfUtil->extractPdfText($pdfFilePath);
        //dump($pdfText);

        //Use MISI_with_panels.xlsx
        //Add separators for panels '### '

        $excelFilePath = $projectRoot ."/src/App/TranslationalResearchBundle/Util/MISI_with_panels.xlsx";
        echo "excelFilePath=$excelFilePath <br>";
        if (file_exists($excelFilePath)) {
            echo "The file $excelFilePath exists";
        } else {
            echo "The file $excelFilePath does not exist";
        }

        $transresUtil->processExcelMisiPanels($excelFilePath);

        exit('EOF antibodyCreatePanelsAction');
    }

    //127.0.0.1/translational-research/antibody-make-public-misi
    #[Route(path: '/antibody-make-public-misi/', name: 'translationalresearch_antibody_make_public_misi', methods: ['GET'])]
    public function makePublicMISIAction( Request $request )
    {
        exit("makePublicMISIAction not allowed");
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();

        $misiLab = $em->getRepository(AntibodyLabList::class)->findOneByAbbreviation("MISI");
        if( !$misiLab ) {
            exit("AntibodyLabList MISI does not exist");
        }

        $repository = $em->getRepository(AntibodyList::class);
        $dql =  $repository->createQueryBuilder("antibody");
        $dql->select('antibody');

        $dql->leftJoin('antibody.antibodyLabs','antibodyLabs');

        $dql->where("antibodyLabs = :misiId");
        $params = array(
            'misiId' => $misiLab->getId()
        );

        $dql->andWhere("antibody.openToPublic IS NULL");

        $query = $dql->getQuery();

        $query->setParameters($params);
        //echo "query=".$query->getSql()."<br>";

        $antibodies = $query->getResult();
        echo "makePublicMISIAction: antibodies=".count($antibodies)."<br>";

        $antibodyIds = array();
        $count = 0;
        foreach($antibodies as $antibody) {
            $antibody->setOpenToPublic(true);
            $antibodyIds[] = $antibody->getId();
            //$em->flush();
            $count++;
        }
        //$em->flush();

        $msg = "Set to OpenToPublic MISI antibodies count=".$count.".";
        $msg = $msg . " IDs:" . implode(", ",$antibodyIds);
        $logger->notice($msg);

        exit('EOF makePublicMISIAction: '.$msg);
    }


    /**
     * Update RequestCategoryTypeList to allow to add new fee schedule with new version $feeScheduleVersion=2
     * http://127.0.0.1/translational-research/add-fee-schedule
     */
    #[Route(path: '/add-fee-schedule', name: 'translationalresearch_add_fee_schedule')]
    public function updateFeeScheduleAction( Request $request ) {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('employees.sitename').'-nopermission') );
        }

        //exit("updateFeeScheduleAction: Not allowed");

        $em = $this->getDoctrine()->getManager();
        $importUtil = $this->container->get('transres_import');

        $filename = 'new_fees_schedule_2026.xlsx';
        
        $count = $importUtil->addNewFeeSchedules($request,$filename,$feeScheduleVersion=2);

        exit("updateFeeScheduleAction: End add new Fees Schedule: ".$count);
    }

}
