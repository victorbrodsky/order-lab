<?php

namespace App\TranslationalResearchBundle\Controller;

use App\UserdirectoryBundle\Entity\EventObjectTypeList;
use Doctrine\Common\Collections\ArrayCollection;
use App\TranslationalResearchBundle\Entity\Invoice;
use App\TranslationalResearchBundle\Entity\TransResRequest;
use App\TranslationalResearchBundle\Form\FilterInvoiceType;
use App\TranslationalResearchBundle\Form\InvoiceType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use App\UserdirectoryBundle\Entity\User;

/**
 * Invoice controller.
 */
#[Route(path: 'invoice')]
class InvoiceController extends OrderAbstractController
{

    //* @Route("/list-all/", name="translationalresearch_invoice_index_all")
    //* @Route("/list-all-my/", name="translationalresearch_invoice_index_all_my")
    //* @Route("/list-all-issued/", name="translationalresearch_invoice_index_all_issued")
    //* @Route("/list-all-pending/", name="translationalresearch_invoice_index_all_pending")
    /**
     * Lists all invoice entities.
     */
    #[Route(path: '/list-request/{id}', name: 'translationalresearch_invoice_index', methods: ['GET'])]
    #[Route(path: '/list/', name: 'translationalresearch_invoice_index_filter', methods: ['GET'])]
    #[Route(path: '/list/{invoicetype}', name: 'translationalresearch_invoice_index_type', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Invoice/index.html.twig')]
    public function indexAction(Request $request, TransResRequest $transresRequest=null, $invoicetype=null)
    {
        if (false === $this->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        //replace $this->container->get('security.token_storage')
        $user = $this->getUser();
        //exit('user='.$user);

        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        $routeName = $request->get('_route');
        $advancedFilter = 0;
        $title = "List of Invoices";
        $metaTitle = null;

        //Default variables
        $irbNumber = NULL;
        $fundingType = NULL;
        $fundingNumber = NULL;
        $version = NULL;
        $totalMax = NULL;
        $totalMin = NULL;
        $endCreateDate = NULL;
        $startCreateDate = NULL;
        $idSearch = NULL;
        $status = NULL;
        $startDate = NULL;
        $endDate = NULL;
        $priceList = NULL;

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $repository = $em->getRepository(Invoice::class);
        $dql = $repository->createQueryBuilder("invoice");
        $dql->select('invoice');

        $dql->leftJoin('invoice.submitter', 'submitter');
        $dql->leftJoin('invoice.salesperson', 'salesperson');
        //$dql->leftJoin('salesperson.infos', 'salespersonInfos');
        $dql->leftJoin('invoice.transresRequest', 'transresRequest');
        $dql->leftJoin('transresRequest.submitter', 'requestSubmitter');
        $dql->leftJoin('requestSubmitter.infos', 'requestSubmitterInfos');
        $dql->leftJoin('invoice.principalInvestigator', 'principalInvestigator');
        $dql->leftJoin('invoice.billingContact', 'billingContact');

        $dql->leftJoin('transresRequest.project', 'project');
        $dql->leftJoin('project.principalInvestigators', 'projectPrincipalInvestigators');
        $dql->leftJoin('project.billingContact', 'projectBillingContact');

        $dqlParameters = array();

        if ($routeName == 'translationalresearch_invoice_index') { // /list-request/{id}
            //Title
            $requestUrl = $transresRequestUtil->getRequestShowUrl($transresRequest, false);
            $thisLink = "<a href=" . $requestUrl . ">" . "Request ID " . $transresRequest->getOid() . "</a>";
            //$title = "List of Invoices for Request ID ".$transresRequest->getOid();
            $title = "List of Invoices for " . $thisLink;
            $metaTitle = "List of Invoices for Request ID " . $transresRequest->getOid();

            return $this->redirectToRoute(
                'translationalresearch_invoice_index_filter',
                array(
                    'id' => $transresRequest->getId(),
                    'filter[idSearch]' => $transresRequest->getOid(),
                    'title' => $title,
                )
            );

            //$dql->where("transresRequest.id = :transresRequestId");
            //$dqlParameters["transresRequestId"] = $transresRequest->getId();
        }

        //////// create filter //////////
        $versions = $transresRequestUtil->getInvoiceComplexVersions(100);
        $transresPricesList = $transresUtil->getPricesList();

        $trpAdminOrTech = false;
        if(
            $this->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            $trpAdminOrTech = true;
        }

        $params = array(
            'routeName' => $routeName,
            'transresRequest' => $transresRequest,
            'versions' => $versions,
            'statuses' => $transresRequestUtil->getInvoiceStatuses(),
            'humanAnimalName' => $transresUtil->getHumanAnimalName("brackets"),
            //'SecurityAuthChecker' => $this->container, //$this->container->get('security.authorization_checker'),
            'trpAdminOrTech' => $trpAdminOrTech,
            'transresPricesList' => $transresPricesList
        );
        $filterform = $this->createForm(FilterInvoiceType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));

        $filterform->handleRequest($request);

        $filterTitle = trim((string)$request->get('title'));
        //$filterwell = trim((string)$request->get('filterwell') );

        //$filterType = trim((string)$request->get('type') );
        $invoicetypeLowerCase = null;
        //echo "invoicetype=$invoicetype<br>";
        if( $invoicetype ) {
            $invoicetype = str_replace("-", " ", $invoicetype);
            $invoicetypeLowerCase = strtolower($invoicetype);
        }
        //echo "invoicetypeLowerCase=$invoicetypeLowerCase<br>";

//        if(
//            ($routeName == "translationalresearch_invoice_index_filter" && !$filterTitle)
//            ||
//            ($invoicetype && strpos(strtolower($invoicetype), 'all') !== false)
//        ) {
//            if(
//                $transresUtil->isAdminOrPrimaryReviewer() ||
//                $this->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ||
//                $this->isGranted('ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY') ||
//                $this->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP') ||
//                $this->isGranted('ROLE_TRANSRES_EXECUTIVE_COVID19') ||
//                $this->isGranted('ROLE_TRANSRES_EXECUTIVE_MISI')
//            ) {
//                //show
//            } else {
//                return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
//            }
//        }

        if ($invoicetype && $invoicetypeLowerCase == strtolower("All Invoices")) {
            //filter nothing
            $title = "All Invoices";
        }

        if ($invoicetype && $invoicetypeLowerCase != strtolower("All Invoices")) {
//            if( $invoicetype == "All Invoices" ) {
//                //filter nothing
//                $title = "All Invoices";
//            }

            if ($invoicetypeLowerCase == strtolower("All Issued Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[status][0]' => "Unpaid/Issued",
                        'filter[status][1]' => "Paid in Full",
                        'filter[status][2]' => "Paid Partially",
                        'filter[status][3]' => 'Refunded Fully',
                        'filter[status][4]' => 'Refunded Partially',
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("All Pending Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[status][]' => "Pending",
                        'title' => $invoicetype,
                    )
                );
            }

            //Personal Invoices
//            if( $invoicetype == "My Invoices (I am Submitter, Salesperson or PI)" ) {
//                return $this->redirectToRoute(
//                    'translationalresearch_invoice_index_filter',
//                    array(
//                        'filter[submitter]' => $user->getId(),
//                        'filter[salesperson]' => $user->getId(),
//                        'filter[principalInvestigator]' => $user->getId(),
//                    )
//                );
//            }
//            if( $invoicetype == "Invoices Sent to Me" ) {
//                return $this->redirectToRoute(
//                    'translationalresearch_invoice_index_filter',
//                    array(
//                        'filter[principalInvestigator]' => $user->getId(),
//                    )
//                );
//            }
            if( $invoicetypeLowerCase == strtolower("All my invoices") ) {
                //all Invoices for all Work Requests issued for Projects where I am listed in any way (submitter, PI, etc).
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        //'filter[submitter]' => $user->getId(),
                        //'filter[salesperson]' => $user->getId(),
                        //'filter[principalInvestigator]' => $user->getId(),

                        //'filter[status][0]' => "Unpaid/Issued",
                        //'filter[status][1]' => "Paid in Full",
                        //'filter[status][2]' => "Paid Partially",
                        //'filter[status][3]' => 'Refunded Fully',
                        //'filter[status][4]' => 'Refunded Partially',

                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("All my outstanding invoices")) {
                //all outstanding Invoices for all Work Requests issued, but not paid for Projects where I am listed in any way (submitter, PI, etc).
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        //'filter[submitter]' => $user->getId(),
                        //'filter[salesperson]' => $user->getId(),
                        //'filter[principalInvestigator]' => $user->getId(),

                        'filter[status][0]' => "Unpaid/Issued",
                        //'filter[status][1]' => "Paid in Full",
                        //'filter[status][2]' => "Paid Partially",
                        //'filter[status][3]' => 'Refunded Fully',
                        //'filter[status][4]' => 'Refunded Partially',

                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Issued invoices I generated")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[submitter]' => $user->getId(),
                        'filter[status][0]' => "Unpaid/Issued",
                        'filter[status][1]' => "Paid in Full",
                        'filter[status][2]' => "Paid Partially",
                        'filter[status][3]' => 'Refunded Fully',
                        'filter[status][4]' => 'Refunded Partially',
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Invoices where I am the salesperson")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[salesperson]' => $user->getId(),
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Invoices where I am the PI")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[principalInvestigator]' => $user->getId(),
                        'title' => $invoicetype,
                    )
                );
            }
            //"Unpaid Invoices where I am a PI", "Unpaid Invoices sent to Me"
            if ($invoicetypeLowerCase == strtolower("Outstanding invoices where I am the PI")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[principalInvestigator]' => $user->getId(),
                        'filter[status][0]' => "Unpaid/Issued",
                        'title' => $invoicetype,
                    )
                );
            }

            //Latest
            if ($invoicetypeLowerCase == strtolower("Latest Versions of All Invoices Except Canceled")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][]' => "All Invoices Except Canceled",
                        'title' => $invoicetype,
                        //'filterwell' => 'closed'
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Latest Versions of All Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Latest Versions of Issued (Unpaid) Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Unpaid/Issued",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Latest Versions of Pending (Unissued) Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Pending",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Latest Versions of Paid Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Paid in Full",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Latest Versions of Partially Paid Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Paid Partially",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Latest Versions of Paid and Partially Paid Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Paid in Full",
                        'filter[status][1]' => "Paid Partially",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Latest Versions of Canceled Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Canceled",
                        'title' => $invoicetype,
                    )
                );
            }

            //Old
            if ($invoicetypeLowerCase == strtolower("Old Versions of All Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Old Versions of Issued (Unpaid) Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Unpaid/Issued",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Old Versions of Pending (Unissued) Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Pending",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Old Versions of Paid Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Paid in Full",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Old Versions of Partially Paid Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Paid Partially",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Old Versions of Paid and Partially Paid Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Paid in Full",
                        'filter[status][1]' => "Paid Partially",
                        'title' => $invoicetype,
                    )
                );
            }
            if ($invoicetypeLowerCase == strtolower("Old Versions of Canceled Invoices")) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Canceled",
                        'title' => $invoicetype,
                    )
                );
            }

        } else {
            $submitter = $filterform['submitter']->getData();
            $principalInvestigator = $filterform['principalInvestigator']->getData();
            $salesperson = $filterform['salesperson']->getData();
            $billingContact = $filterform['billingContact']->getData();
            $status = $filterform['status']->getData();
            $idSearch = $filterform['idSearch']->getData();
            $totalMin = $filterform['totalMin']->getData();
            $totalMax = $filterform['totalMax']->getData();
            $startDate = $filterform['startDate']->getData();
            $endDate = $filterform['endDate']->getData();
            $version = $filterform['version']->getData();
            $fundingNumber = $filterform['fundingNumber']->getData();
            $fundingType = $filterform['fundingType']->getData();
            $irbNumber = $filterform['irbNumber']->getData();
            $startCreateDate = $filterform['startCreateDate']->getData();
            $endCreateDate = $filterform['endCreateDate']->getData();
            //echo "totalMin=".$totalMin."<br>";
            //$advancedWell = $filterform['well']->getData();

            if (isset($filterform['priceList'])) {
                $priceList = $filterform['priceList']->getData();
            }
        }
        ////// EOF create filter //////////

        //get logged in user specialties
        $userSpecialties = $transresUtil->getTransResProjectSpecialties();

        //echo "filterTitle=$filterTitle, invoicetype=$invoicetype <br>";
        $onlyForAdmin = false;
        if ($invoicetype && strpos(strtolower($invoicetype), 'all') !== false) {
            $onlyForAdmin = true;
        }
        //echo "1onlyForAdmin=$onlyForAdmin <br>";
        //echo "routeName=$routeName <br>";
        if ($routeName == "translationalresearch_invoice_index_filter") {
            if (!$idSearch && !$filterTitle) {
                //echo "check: idSearch and filterTitle null <br>";
                $onlyForAdmin = true;
            }
        }
        //$onlyForAdmin = false; //testing
        //echo "3onlyForAdmin=$onlyForAdmin <br>";
        if ($onlyForAdmin) {

            //modify to be auto specialty
            $adminOrReviewerAllow = false;
            $executiveAllow = false;
            $billingadminAllow = false;
            foreach($userSpecialties as $userSpecialty) {
                $specialtyStr = $userSpecialty->getUppercaseName();
                //echo "specialtyStr=$specialtyStr <br>";
                if( $transresUtil->isAdminOrPrimaryReviewer(null,$userSpecialty) ) {
                    $adminOrReviewerAllow = true;
                }
                if( $this->isGranted('ROLE_TRANSRES_EXECUTIVE_'.$specialtyStr) ) {
                    $executiveAllow = true;
                }
                if( $this->isGranted('ROLE_TRANSRES_BILLING_ADMIN_'.$specialtyStr) ) {
                    $billingadminAllow = true;
                }
            }

            if (
                $adminOrReviewerAllow || $executiveAllow || $billingadminAllow
                //$transresUtil->isAdminOrPrimaryReviewer() ||
                //$this->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ||
                //$this->isGranted('ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY') ||
                //$this->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP') ||
                //$this->isGranted('ROLE_TRANSRES_EXECUTIVE_COVID19') ||
                //$this->isGranted('ROLE_TRANSRES_EXECUTIVE_MISI')
            ) {
                //show for admin, executive, billing admin without any restrictions
            } else {
                //exit('no permission onlyForAdmin');
                //return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));

                //If a regualr user (IP, contact ... ), then filter by project's associates
                $dql->andWhere(
                    "
                    submitter.id = :projectUserId 
                    OR principalInvestigator.id = :projectUserId 
                    OR salesperson.id = :projectUserId 
                    OR billingContact.id = :projectUserId
                    OR projectPrincipalInvestigators.id = :projectUserId
                    OR projectBillingContact.id = :projectUserId
                    "
                );
                $dqlParameters["projectUserId"] = $user->getId();
            }
        }

//        if( $routeName == "translationalresearch_invoice_index_filter" ) {
//            $title = "List of All Invoices";
//        }
//        if( $routeName == "translationalresearch_invoice_index_all_my" ) {
//            $title = "List of All My Invoices";
//            $submitter = $user;
//        }
//        if( $routeName == "translationalresearch_invoice_index_all_issued" ) {
//            $title = "List of All Issued Invoices";
//            $status = "Unpaid/Issued";
//            //TODO: show also "Paid in Full" and "Paid Partially": allow multiple selection
//        }
//        if( $routeName == "translationalresearch_invoice_index_all_pending" ) {
//            $title = "List of All Pending Invoices";
//            $status = "Pending";
//        }

        //if ($invoicetypeLowerCase == strtolower("My Invoices")) {
        if( strtolower($filterTitle) == strtolower("All my invoices") ) {
            //all Invoices for all Work Requests issued for Projects where I am listed in any way (submitter, PI, etc).
            //Use OR
            $dql->andWhere(
                "
                submitter.id = :userId 
                OR principalInvestigator.id = :userId 
                OR salesperson.id = :userId 
                OR billingContact.id = :userId
                OR projectPrincipalInvestigators.id = :userId
                OR projectBillingContact.id = :userId
                "
            );
            $dqlParameters["userId"] = $user->getId();
            //set all user filter to NULL to prevent AND query conditions
            $submitter = null;
            $principalInvestigator = null;
            $salesperson = null;
            $billingContact = null;
            //$status = null;

            $title = $filterTitle = "My Invoices, where I am a Submitter, PI, Billing Contact or a Sales Person";
            //exit($title);
        }

        if( strtolower($filterTitle) == strtolower("All my outstanding invoices") ) {
            //all Invoices for all Work Requests all invoices that are issued but not paid for Projects where I am listed in any way (submitter, PI, etc).
            $dql->andWhere(
                "
                submitter.id = :userId 
                OR principalInvestigator.id = :userId 
                OR salesperson.id = :userId 
                OR billingContact.id = :userId
                OR projectPrincipalInvestigators.id = :userId
                OR projectBillingContact.id = :userId
                "
            );
            $dqlParameters["userId"] = $user->getId();
            //set all user filter to NULL to prevent AND query conditions
            $submitter = null;
            $principalInvestigator = null;
            $salesperson = null;
            $billingContact = null;
            //$status = null;

            $title = $filterTitle = "My Outstanding Invoices, issued but not paid, where I am a Submitter, PI, Billing Contact or a Sales Person";
            //exit($title);
        }

        if ($submitter) {
            //echo "Submitter=$submitter<br>";
            $dql->andWhere("submitter.id = :submitterId");
            $dqlParameters["submitterId"] = $submitter->getId();
            $advancedFilter++;
        }

        if ($status && count($status) > 0) {
            $allExceptCanceled = "";
            if (in_array("All Invoices Except Canceled", $status)) {
                $allExceptCanceled = "invoice.status != 'Canceled'";
            }
            foreach ($status as $statusKey => $statusName) {
                //echo "status=".$statusName."<br>";
                if ($statusName == "All Invoices Except Canceled") {
                    unset($status[$statusKey]);
                }
            }
            //print_r($status);

            if (count($status) > 0) {
                if ($allExceptCanceled) {
                    $allExceptCanceled = " AND " . $allExceptCanceled;
                }
                $dql->andWhere("invoice.status IN (:statuses)" . $allExceptCanceled);
                $dqlParameters["statuses"] = $status;
            } else {
                $dql->andWhere($allExceptCanceled);
            }
        }

        if ($idSearch) {
            $dql->andWhere("invoice.oid LIKE :idSearch");
            $dqlParameters["idSearch"] = "%" . $idSearch . "%";
        }

//        if( $principalInvestigators && count($principalInvestigators)>0 ) {
//            $dql->andWhere("principalInvestigators.id IN (:principalInvestigators)");
//            $principalInvestigatorsIdsArr = array();
//            foreach($principalInvestigators as $principalInvestigator) {
//                $principalInvestigatorsIdsArr[] = $principalInvestigator->getId();
//            }
//            $dqlParameters["principalInvestigators"] = implode(",",$principalInvestigatorsIdsArr);
//            $advancedFilter++;
//        }
        if ($principalInvestigator) {
            //echo "PI=$principalInvestigator <br>";
            $dql->andWhere("principalInvestigator.id = :principalInvestigatorId OR projectPrincipalInvestigators.id = :principalInvestigatorId");
            $dqlParameters["principalInvestigatorId"] = $principalInvestigator->getId();
            //$advancedFilter++;
        }

        if ($startDate) {
            $dql->andWhere('invoice.dueDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }
        if ($endDate) {
            $endDate->modify('+1 day');
            $dql->andWhere('invoice.dueDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }

        if ($startCreateDate) {
            $dql->andWhere('invoice.createDate >= :startCreateDate');
            $dqlParameters['startCreateDate'] = $startCreateDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }
        if ($endCreateDate) {
            $endCreateDate->modify('+1 day');
            $dql->andWhere('invoice.createDate <= :endCreateDate');
            $dqlParameters['endCreateDate'] = $endCreateDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }

        if ($salesperson) {
            //echo "salesperson=$salesperson<br>";
            $dql->andWhere("salesperson.id = :salespersonId");
            $dqlParameters["salespersonId"] = $salesperson->getId();
            $advancedFilter++;
        }

        if ($billingContact) {
            $dql->andWhere("billingContact.id = :billingContact OR projectBillingContact.id = :billingContact");
            $dqlParameters["billingContact"] = $billingContact->getId();
            $advancedFilter++;
        }

        if ($totalMin) {
            $dql->andWhere('invoice.total >= :totalMin');
            $dqlParameters['totalMin'] = $totalMin;
            $advancedFilter++;
        }

        if ($totalMax) {
            $dql->andWhere('invoice.total <= :totalMax');
            $dqlParameters['totalMax'] = $totalMax;
            $advancedFilter++;
        }

        if ($version) {
            if ($version == "Latest") {
                $dql->andWhere('invoice.latestVersion = TRUE');
            } elseif ($version == "Old") {
                $dql->andWhere('invoice.latestVersion != TRUE ');
            } else {
                $dql->andWhere('invoice.version = :version');
                $dqlParameters['version'] = $version;
            }
            $advancedFilter++;
        }

        if ($fundingNumber) {
            $dql->andWhere("invoice.fundedAccountNumber LIKE :fundedAccountNumber");
            $dqlParameters["fundedAccountNumber"] = "%" . $fundingNumber . "%";
            $advancedFilter++;
        }

        if ($fundingType) {
            if ($fundingType == "Funded") {
                $dql->andWhere("invoice.fundedAccountNumber IS NOT NULL");
                $advancedFilter++;
            }
            if ($fundingType == "Non-Funded") {
                $dql->andWhere("invoice.fundedAccountNumber IS NULL");
                $advancedFilter++;
            }
        }

        if ($irbNumber) {
            //$dql->leftJoin('transresRequest.project', 'project');
            $dql->andWhere("project.irbNumber LIKE :irbNumber OR project.iacucNumber LIKE :irbNumber");
            $dqlParameters["irbNumber"] = "%" . $irbNumber . "%";
            $advancedFilter++;
        }

        if ($priceList) {
            if ($priceList != 'all') {
                //$dql->leftJoin('transresRequest.project', 'project');
                $dql->leftJoin('project.priceList', 'priceList');
                if( $priceList == 'external' ) {
                //if ($priceList == 'default') {
                    $dql->andWhere("priceList.id IS NULL");
                } else {
                    $dql->andWhere("priceList.id = :priceListId");
                    $dqlParameters["priceListId"] = $priceList;
                }
                $advancedFilter++;
            }
        }

        //if not ROLE_PLATFORM_ADMIN and ROLE_PLATFORM_DEPUTY_ADMIN => filter by specialty
        if( !$this->isGranted("ROLE_PLATFORM_DEPUTY_ADMIN") ) {
            if(
                $this->isGranted('ROLE_TRANSRES_ADMIN') ||
                $this->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
                $this->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ||
                $this->isGranted('ROLE_TRANSRES_TECHNICIAN') ||
                $this->isGranted('ROLE_TRANSRES_EXECUTIVE')
            ) {
                $projectSpecialtyIds = array();
                foreach ($userSpecialties as $userSpecialty) {
                    $projectSpecialtyIds[] = $userSpecialty->getId();
                }
                if (count($projectSpecialtyIds) > 0) {
                    //$dql->leftJoin('project.projectSpecialty', 'projectSpecialty');
                    //$dql->leftJoin('transresRequest.project', 'project');
                    $dql->andWhere("project.projectSpecialty IN (:projectSpecialties)");
                    $dqlParameters["projectSpecialties"] = $projectSpecialtyIds;
                }
            }
        }

        $limit = 30;
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        if (count($dqlParameters) > 0) {
            $query->setParameters($dqlParameters);
        }

        //echo "query=".$query->getSql()."<br>";

        $paginationParams = array(
            'defaultSortFieldName' => 'invoice.id',
            'defaultSortDirection' => 'DESC',
            'wrap-queries' => true
        );

        $paginator = $this->container->get('knp_paginator');
        $invoices = $paginator->paginate(
            $query,
            $request->query->get('page', 1),    /*page number*/
            $limit,                             /*limit per page*/
            $paginationParams
        );

        //$latestVersion = $transresRequestUtil->getLatestInvoiceVersion($transresRequest);

        //echo "invoicetype=".$invoicetype."<br>";
        //echo "title=".$title."<br>";
        if ($filterTitle) {
            $title = $filterTitle;
        }
        if (!$metaTitle) {
            $metaTitle = $title;
        }

        $eventObjectType = $em->getRepository(EventObjectTypeList::class)->findOneByName("Invoice");
        if ($eventObjectType) {
            $eventObjectTypeId = $eventObjectType->getId();
        } else {
            $eventObjectTypeId = null;
        }
        //echo "eventObjectTypeId=".$eventObjectTypeId."<br>";

        $matchingStrInvoice = $transresRequestUtil->getMatchingStrInvoiceByDqlParameters($dql, $dqlParameters);
        $matchingStrInvoiceStr = $matchingStrInvoice['resultStr'];
        $matchingStrInvoiceIds = $matchingStrInvoice['ids'];
        //$totalStrInvoice = $transresRequestUtil->getTotalStrInvoice();
        //$title = $title . " (" . $matchingStrInvoiceStr . "; " . $totalStrInvoice . ")";
        $title = $title . " (" . $matchingStrInvoiceStr . ")";

        $matchingStrInvoiceIds = implode("-", $matchingStrInvoiceIds);

//        if( $filterwell && $filterwell == 'closed' ) {
//            //$advancedFilter = 0;
//        }
        if ($filterTitle) {
            $advancedFilter = 0;
        }

        //check if the filter well is set to show either a
        // single project ID’s invoices ($idSearch)
        // OR
        // a single PI’s invoices ($principalInvestigator) ($principalInvestigator)
        // (or both of these filter well fields have values)
        $exportUnpaidSummary = 0;
        if(
            $routeName == "translationalresearch_invoice_index_filter" ||
            $routeName == "translationalresearch_invoice_index_type"
        ) {
            if ($principalInvestigator) {
                $exportUnpaidSummary = 1;
            }
            if ($idSearch) {
                $exportUnpaidSummary = 1;
//                //$idSearch does not have '-REQ'
//                if (strpos((string)$idSearch, '-REQ') === false) {
//                    $exportUnpaidSummary = 1;
//                }
            }
         }

        return array(
            'invoices' => $invoices,
            'matchingStrInvoiceIds' => $matchingStrInvoiceIds,
            'exportUnpaidSummary' => $exportUnpaidSummary,
            'transresRequest' => $transresRequest,
            'title' => $title,
            'metaTitle' => $metaTitle,
            'filterform' => $filterform->createView(),
            'advancedFilter' => $advancedFilter,
            'eventObjectTypeId' => $eventObjectTypeId
            //'latestVersion' => $latestVersion
        );
    }

    /**
     * Creates a new invoice entity.
     */
    #[Route(path: '/new/{id}', name: 'translationalresearch_invoice_new', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Invoice/new.html.twig')]
    public function newAction(Request $request, TransResRequest $transresRequest)
    {
        //if( false === $this->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
        //    return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        //}

        //$em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $user = $this->getUser();
        //$user = null; //testing
        $cycle = "new";

        $project = $transresRequest->getProject();

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice=NULL,"create") === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to create this invoice."
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $invoice = $transresRequestUtil->createNewInvoice($transresRequest,$user);

        $originalInvoiceStatus = $invoice->getStatus();

        $form = $this->createInvoiceForm($invoice,$cycle,$transresRequest); //new

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //exit('new');

//            //update subsidy for new invoice : done in create Submit New Invoice
//            $transresRequestUtil->updateInvoiceSubsidy($invoice);

            if ($invoice->getStatus() == "Unpaid/Issued") {
                $invoice->setIssuedDate(new \DateTime());
            }

            //remove product with not existing fee schedule
            //$transresRequestUtil->processProductFromInvoiceItem($invoice);

            $msg = $transresRequestUtil->createSubmitNewInvoice($transresRequest,$invoice);

            //generate new PDF
            $msg2 = $this->processInvoiceAfterSave($invoice,$form,$user,$request); //new

            $invoiceStatus = $invoice->getStatus();
            if( $invoiceStatus != $originalInvoiceStatus ) {
                $transresRequestUtil->syncInvoiceRequestStatus($invoice, $invoiceStatus);
            }

            //$msg = "New Invoice has been successfully created for the request ID ".$transresRequest->getOid();

            $msg = $msg . $msg2;

            $this->addFlash(
                'notice',
                $msg
            );

            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
        }//$form->isSubmitted()

        //$invoiceDefaultTotal = $transresRequestUtil->calculateDefaultTotalByInvoice($invoice);
        $invoiceDefaultTotal = $transresRequest->calculateDefaultTotalByRequest();

        //list of RequestCategoryTypeList
        $productArr = $transresRequestUtil->getFeeSchedule($transresRequest);
        
        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            'form' => $form->createView(),
            'title' => "New Invoice for the Request ID ".$transresRequest->getOid(),
            'cycle' => $cycle,
            'invoiceDefaultTotal' => $invoiceDefaultTotal,
            'productArr' => $productArr
        );
    }

    /**
     * Creates a new invoice entity.
     */
    #[Route(path: '/partial/new/{id}', name: 'translationalresearch_partial_invoice_new', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Invoice/new-partial-invoice.html.twig')]
    public function newPartialInvoiceAction(Request $request, TransResRequest $transresRequest)
    {
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $user = $this->getUser();
        //$user = null; //testing
        $cycle = "new";

        $project = $transresRequest->getProject();

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice=NULL,"create") === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to create this invoice."
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $invoice = $transresRequestUtil->createNewInvoice($transresRequest,$user);

        $originalInvoiceStatus = $invoice->getStatus();

        $form = $this->createInvoiceForm($invoice,$cycle,$transresRequest); //new

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //exit('new');

//            //update subsidy for new invoice : done in create Submit New Invoice
//            $transresRequestUtil->updateInvoiceSubsidy($invoice);

            if ($invoice->getStatus() == "Unpaid/Issued") {
                $invoice->setIssuedDate(new \DateTime());
            }

            //remove product with not existing fee schedule
            //$transresRequestUtil->processProductFromInvoiceItem($invoice);

            $msg = $transresRequestUtil->createSubmitNewInvoice($transresRequest,$invoice);

            //generate new PDF
            $msg2 = $this->processInvoiceAfterSave($invoice,$form,$user,$request); //new

            $invoiceStatus = $invoice->getStatus();
            if( $invoiceStatus != $originalInvoiceStatus ) {
                $transresRequestUtil->syncInvoiceRequestStatus($invoice, $invoiceStatus);
            }

            //$msg = "New Invoice has been successfully created for the request ID ".$transresRequest->getOid();

            $msg = $msg . $msg2;

            $this->addFlash(
                'notice',
                $msg
            );

            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
        }//$form->isSubmitted()

        //$invoiceDefaultTotal = $transresRequestUtil->calculateDefaultTotalByInvoice($invoice);
        $invoiceDefaultTotal = $transresRequest->calculateDefaultTotalByRequest();

        //list of RequestCategoryTypeList
        $productArr = $transresRequestUtil->getFeeSchedule($transresRequest);

        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            'form' => $form->createView(),
            'title' => "New Partial Invoice for the Request ID ".$transresRequest->getOid(),
            'cycle' => $cycle,
            'invoiceDefaultTotal' => $invoiceDefaultTotal,
            'productArr' => $productArr
        );
    }

    /**
     * Finds and displays a invoice entity.
     */
    #[Route(path: '/show/{oid}', name: 'translationalresearch_invoice_show', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Invoice/new.html.twig')]
    public function showAction(Request $request, $oid)
    {
//        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();

        //1) try to find by oid
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $invoice = $em->getRepository(Invoice::class)->findOneByOid($oid);

        if( !$invoice ) {
            //2) try to find by id
            //First check if id is integer
            if( ctype_digit($oid) ) {
                $invoice = $em->getRepository(Invoice::class)->find($oid);
            }
        }
        if( !$invoice ) {
            //Get $transresRequest from oid
            $transresRequest = $transresRequestUtil->findWorkRequestByInvoiceOid($oid);
            //echo "transresRequest=".$transresRequest."<br>";
            if( $transresRequest ) {
                $invoice = $transresRequestUtil->getLatestInvoice($transresRequest);
            }

            if( $invoice ) {
                $url = $this->container->get('router')->generate(
                    'translationalresearch_invoice_show',
                    array(
                        'oid' => $invoice->getOid()
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $linkLatestInvoice = "<a target='_blank' " .
                    "href=" . $url . ">" . $invoice->getOid() . "</a>";

                $invoiceWarningMsg = "The invoice could not be found by ID $oid" .
                    "<br> Please click this link to view the latest invoice " . $linkLatestInvoice;
            } else {
                $invoiceWarningMsg = "The invoice could not be found by ID $oid";
            }

            //exit('$invoiceWarningMsg='.$invoiceWarningMsg);

            $this->addFlash(
                'warning',
                $invoiceWarningMsg
            );

            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            //throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"view") === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to view this invoice"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "show";
        //$routeName = $request->get('_route');

        $form = $this->createInvoiceForm($invoice,$cycle); //show

        $deleteForm = $this->createDeleteForm($invoice);

        $transresRequest = $invoice->getTransresRequest();
        //echo "transresRequest=".$transresRequest."<br>";

        $eventType = "Invoice Viewed";
        $msg = "Invoice ".$invoice->getOid() ." has been viewed.";
        $transresUtil->setEventLog($invoice,$eventType,$msg);

        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'cycle' => $cycle,
            'title' => "Invoice ID ".$invoice->getOid(),
            'invoiceDefaultTotal' => NULL,
            'productArr' => NULL
        );
    }

    /**
     * Displays a form to edit an existing invoice entity.
     */
    #[Route(path: '/edit/{oid}', name: 'translationalresearch_invoice_edit', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Invoice/new.html.twig')]
    public function editAction(Request $request, $oid)
    {

//        if( false === $this->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $invoice = $em->getRepository(Invoice::class)->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

        if( $invoice->getLatestVersion() !== true ) {
            $this->addFlash(
                'notice',
                "The old version of the invoice can not be edited."
            );
            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));

        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"update") === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to edit this invoice"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

//        // Create an ArrayCollection of the current Tag objects in the database
//        $originalInvoiceItems = new ArrayCollection();
//        foreach($invoice->getInvoiceItems() as $invoiceItem) {
//            echo "1 invoiceItem=" . $invoiceItem . "<br>";
//            $originalInvoiceItems->add($invoiceItem);
//        }

        //update due date
        $invoice->reSetDueDate();
        //echo "due date=".$invoice->getDueDate()->format("Y-m-d")."<br>";

        $invoiceSerializedOriginalStr = $invoice->getSerializeStr();
        //echo "invoiceSerializedOriginalStr=$invoiceSerializedOriginalStr<br>";
        //exit();

        //Get $transresRequest (Assume invoice has a single $transresRequest)
        $transresRequest = $invoice->getTransresRequest();
        
        $user = $this->getUser();
        $cycle = "edit";

        $originalInvoiceStatus = $invoice->getStatus();
        //$originalPaid = $invoice->getPaid();
        //$originalDue = $invoice->getDue();

        //$deleteForm = $this->createDeleteForm($invoice);

//        foreach($invoice->getInvoiceItems() as $invoiceItem) {
//            $invoiceProduct = $invoiceItem->getProduct();
//            $itemCode = $invoiceItem->getItemCode();
//            echo "1 ".$invoiceItem."<br>";
//        }

        //$editForm = $this->createForm('App\TranslationalResearchBundle\Form\InvoiceType', $invoice);
        $editForm = $this->createInvoiceForm($invoice,$cycle); //edit

//        foreach($invoice->getInvoiceItems() as $invoiceItem) {
//            $invoiceProduct = $invoiceItem->getProduct();
//            $itemCode = $invoiceItem->getItemCode();
//            echo "2 ".$invoiceItem."<br>";
//        }

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

//            foreach($invoice->getInvoiceItems() as $invoiceItem) {
//                $invoiceProduct = $invoiceItem->getProduct();
//                $itemCode = $invoiceItem->getItemCode();
//                echo "3 ".$invoiceItem."<br>";
//            }
//            exit('edit invoice');
            
//            //process invoiceItems
//            // remove the relationship between the tag and the Task
//            foreach($originalInvoiceItems as $invoiceItem) {
//                echo "Remove?: ".$invoiceItem->getId()."<br>";
//                if( false === $invoice->getInvoiceItems()->contains($invoiceItem) ) {
//                    echo "Remove: ".$invoiceItem->getId().": ItemCode=" . $invoiceItem->getItemCode() . ", product=".$invoiceItem->getProduct()."<br>";
//                    // remove the Task from the Tag
//                    $invoice->getInvoiceItems()->removeElement($invoiceItem);
//                    // if it was a many-to-one relationship, remove the relationship like this
//                    $invoiceItem->setInvoice(null);
//                    $em->persist($invoiceItem);
//                    // if you wanted to delete the Tag entirely, you can also do that
//                    $em->remove($invoiceItem);
//                }
//            }
//            exit('edit invoice');

//            foreach($invoice->getInvoiceItems() as $invoiceItem) {
//                echo "3 invoiceItem=" . $invoiceItem . "<br>";
//            }
//            exit(111);

            //remove product with not existing fee schedule
            //$transresRequestUtil->processProductFromInvoiceItem($invoice);

            //update subsidy for updated invoice
            $subsidy = $transresRequestUtil->updateInvoiceSubsidy($invoice);
            //exit("create new invoice: subsidy=$subsidy"); //testing

            //update user
            $invoice->setUpdateUser($user);

            //update oid: don't update Invoice version on edit. Only the last version can be edited.

//            //use the values in Invoice’s Quantity fields to overwrite/update the associated Request’s "Completed #" fields
//            $transresRequestUtil->updateRequestCompletedFieldsByInvoice($invoice); //edit

            //update parent work request products by invoice's invoiceItems
            $transresRequestUtil->updateWorkRequestProductsByInvoice($invoice); //edit //testing: remove to see that remove->add new invoiceitem

            $transresRequestUtil->updateInvoiceStatus($invoice);
            
            //Set Paid to Total and Due to 0 if values is unchanged
            //Please fix the display of “paid” and “due” amounts:
            // Once the invoice is marked as “paid in full” via any method,
            // the “paid” field/column should be set to the amount
            // that used to be “due” and the “due” amount should be set to zero.
            //paid is set to 'total' instead of 'due', because due might be updated incrementally by multiple partial payment until it reaches the 'total'
            if( $originalInvoiceStatus != $invoice->getStatus() ) {
                if ($invoice->getStatus() == "Paid in Full") {
                    $invoice->setPaid($invoice->getTotal());
                    $invoice->setDue(NULL);
                    //echo "Status modified<br>";
//                    if ($invoice->getPaid() != $originalPaid) {
//                        //don't update if changed
//                    } else {
//                        //echo "update Paid<br>";
//                        $invoice->setPaid($invoice->getTotal());
//                        $invoice->setDue(NULL);
//                    }
//                    if ($invoice->getDue() != $originalDue) {
//                        //don't update if changed
//                    } else {
//                        //echo "update Due<br>";
//                        $invoice->setDue(NULL);
//                    }
                }
                if ($invoice->getStatus() == "Unpaid/Issued") {
                    $invoice->setIssuedDate(new \DateTime());
                }
            }
            //exit("status=".$invoice->getStatus());

            $em->flush(); //edit

            $invoiceStatus = $invoice->getStatus();
            if( $invoiceStatus != $originalInvoiceStatus ) {
                $transresRequestUtil->syncInvoiceRequestStatus($invoice, $invoiceStatus);
            }

            //generate new PDF
            $msg2 = $this->processInvoiceAfterSave($invoice,$editForm,$user,$request); //edit

            $msg = "Invoice with ID ".$invoice->getOid()." has been updated.";

            $msg = $msg . $msg2;

            $this->addFlash(
                'notice',
                $msg
            );

            $eventType = "Invoice Updated";
            $msg = "Invoice with ID ".$invoice->getOid()." has been updated.";

            //changes
            //$invoiceUpdatedDb = $em->getRepository('AppTranslationalResearchBundle:Invoice')->findOneByOid($invoice->getOid());
            $invoiceSerializedUpdatedStr = $invoice->getSerializeStr();
            if( $invoiceSerializedUpdatedStr != $invoiceSerializedOriginalStr ) {
                $chanesStr =    "<strong>Original Invoice:</strong><br>" . $invoiceSerializedOriginalStr . "<br>" .
                                "<strong>Updated Invoice:</strong><br>" . $invoiceSerializedUpdatedStr;
                $msg = $msg . "<br>" . $chanesStr;
            }

            $transresUtil->setEventLog($invoice,$eventType,$msg);

            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
        }

        //$invoiceDefaultTotal = $transresRequestUtil->calculateDefaultTotalByInvoice($invoice);
        $invoiceDefaultTotal = $transresRequest->calculateDefaultTotalByRequest();

        $productArr = $transresRequestUtil->getFeeSchedule($transresRequest);

        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            'form' => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
            'cycle' => $cycle,
            'title' => "Invoice ID ".$invoice->getOid(),
            'invoiceDefaultTotal' => $invoiceDefaultTotal,
            'productArr' => $productArr
        );
    }

    /**
     * Deletes a invoice entity.
     */
    #[Route(path: '/delete/{id}', name: 'translationalresearch_invoice_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, Invoice $invoice)
    {
        //exit("Delete is not allowed.");

        if( false === $this->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');

        $form = $this->createDeleteForm($invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $msg = "Invoice with ID ".$invoice->getOid()." has been successfully deleted.";

            $em = $this->getDoctrine()->getManager();
            $em->remove($invoice);
            $em->flush();

            $this->addFlash(
                'notice',
                $msg
            );

            $eventType = "Invoice Deleted";
            $transresUtil->setEventLog($invoice,$eventType,$msg);
        }

        return $this->redirectToRoute('translationalresearch_invoice_index_type', array('invoicetype'=>"All Invoices"));
    }

    /**
     * Generate Invoice PDF
     */
    #[Route(path: '/generate-invoice-pdf/{id}', name: 'translationalresearch_invoice_generate_pdf', methods: ['GET'])]
    public function generateInvoicePdfAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $transresPdfUtil = $this->container->get('transres_pdf_generator');
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');
        $user = $this->getUser();

        //$invoice = $em->getRepository('AppTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $invoice = $em->getRepository(Invoice::class)->find($id);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (id) '" . $id . "'");
        }

//        if( false === $transresPermissionUtil->isInvoiceBillingContact($invoice,$user) ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"generate-invoice-pdf") === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to generate PDF for this invoice"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $res = $transresPdfUtil->generateInvoicePdf($invoice,$user,$request);
        
        //$filename = $res['filename'];
        //$pdf = $res['pdf'];
        $size = $res['size'];

        //$msg = "PDF has been created for Invoice ID " . $invoice->getOid() . "; filename=".$filename."; size=".$size;
        //$msg = "Draft Translational Research Invoice ".$invoice->getOid()." has been generated"."; PDF size=".$size;
        //Draft invoice has been generated for the [Center for Translational Pathology]
        $msg = "Draft invoice ".$invoice->getOid()." has been generated for the ".$transresUtil->getBusinessEntityName()."; PDF size=".$size;

        //3) send by email to recipient (principalInvestigator)
        //Send the most recent Invoice PDF by Email
        $msgEmail = $transresRequestUtil->sendNewInvoicePDFGeneratedEmail($invoice);

        $msg = $msg . "<br>" . $msgEmail;

        //exit("<br><br>".$msg);

        $this->addFlash(
            'notice',
            $msg
        );

        //return $this->redirectToRoute('translationalresearch_invoice_index_all');
        return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));

    }

    /**
     * Show PDF version of invoice
     */
    #[Route(path: '/download-invoice-pdf/{id}', name: 'translationalresearch_invoice_download', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Invoice/pdf-show.html.twig')]
    public function downloadPdfAction(Request $request, TokenStorageInterface $tokenStorage, Invoice $invoice)
    {
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->getUser();
        $user = $this->getUser();
        $logger = $this->container->get('logger');
        //$routeName = $request->get('_route');
        $userSecUtil = $this->container->get('user_security_utility');
        $transresRequestUtil = $this->container->get('transres_request_util');

        //download: user or localhost
        //$user = $this->getUser();
        //download link can be accessed by a console as localhost with role PUBLIC_ACCESS, so simulate login manually
        if( !($user instanceof User) ) {
            $firewall = 'ldap_translationalresearch_firewall';
            $systemUser = $userSecUtil->findSystemUser();
            if( $systemUser ) {
                $token = new UsernamePasswordToken($systemUser, $firewall, $systemUser->getRoles());
                //$this->container->get('security.token_storage')->setToken($token);
                //$this->container->get('security.token_storage')->setToken($token);
                $tokenStorage->setToken($token);
            }
            $logger->notice("Download view: Logged in as systemUser=".$systemUser);
        } else {
            $logger->notice("Download view: Token user is valid security user=".$user);
        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"view") === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to view this invoice"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "download";
        //$routeName = $request->get('_route');

        //$form = $this->createInvoiceForm($invoice,$cycle);

        //$deleteForm = $this->createDeleteForm($invoice);

        $showAdditionalUnitPrice = 0;
        //$showAdditionalUnitPrice = 1;

        if( $showAdditionalUnitPrice ) {
            $pricesDifferent = false;
            foreach($invoice->getInvoiceItems() as $invoiceItem) {
                if( $invoiceItem->getUnitPrice() != $invoiceItem->getAdditionalUnitPrice() ) {
                    $pricesDifferent = true;
                }
            }
            if( !$pricesDifferent ) {
                $showAdditionalUnitPrice = 0;
            }
        }

        //$showRemittance = 0;
        $transresRequest = $invoice->getTransresRequest();
        $showRemittance = $transresRequestUtil->getTransresSiteParameter('showRemittance',$transresRequest);

        return array(
            //'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            //'form' => $form->createView(),
            //'delete_form' => $deleteForm->createView(),
            'cycle' => $cycle,
            'title' => "Invoice ID ".$invoice->getOid(),
            'showAdditionalUnitPrice' => $showAdditionalUnitPrice,
            'showRemittance' => $showRemittance
        );
    }

    /**
     * Show the most recent PDF version of invoice
     * 'Invoice $invoice' might not exists => use id to find and generate flash message to the user
     */
    #[Route(path: '/download-recent-invoice-pdf/{id}', name: 'translationalresearch_invoice_download_recent', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Invoice/pdf-show.html.twig')]
    public function downloadRecentPdfAction(Request $request, $id)
    {
//        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository(Invoice::class)->find($id);
        if( !$invoice ) {
            $this->addFlash(
                'warning',
                'Invoice with ID '. $id . ' does not exist.'
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$em = $this->getDoctrine()->getManager();
        //$user = $this->getUser();
        //$logger = $this->container->get('logger');
        //$routeName = $request->get('_route');
        //$userSecUtil = $this->container->get('user_security_utility');
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$transresUtil = $this->container->get('transres_util');

        //$em = $this->getDoctrine()->getManager();
        //$invoice = $em->getRepository('AppTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        //if( !$invoice ) {
        //    throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        //}

        //$transresRequest = $invoice->getTransresRequest();
        //$project = $transresRequest->getProject();

//        if(
//            false === $transresRequestUtil->isInvoiceBillingContact($invoice,$user) &&
//            false === $transresUtil->isProjectRequester($project) &&
//            false === $this->isGranted('ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY') &&
//            false === $this->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP')
//        ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"view") === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to view this invoice"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //get the most recent PDF document
        $invoicePDF = $invoice->getRecentPDF();

        if( $invoicePDF ) {

//            $routeName = $request->get('_route');
//            if( $routeName == "fellapp_view_pdf" ) {
//                return $this->redirect( $this->generateUrl('fellapp_file_view',array('id' => $reportDocument->getId())) );
//            } else {
//                return $this->redirect( $this->generateUrl('fellapp_file_download',array('id' => $reportDocument->getId())) );
//            }

            return $this->redirect( $this->generateUrl('translationalresearch_file_view',array('id' => $invoicePDF->getId())) );

        } else {
            $this->addFlash(
                'warning',
                'Invoice PDF does not exists.'
            );

            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
        }
    }


    /**
     * Creates a form to delete a invoice entity.
     *
     * @param Invoice $invoice The invoice entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Invoice $invoice)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('translationalresearch_invoice_delete', array('id' => $invoice->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    public function createInvoiceForm( $invoice, $cycle, $transresRequest=null ) {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $transresRequestUtil = $this->container->get('transres_request_util');

        if( !$transresRequest ) {
            $transresRequest = $invoice->getTransresRequest();
        }

        //PIs of the request's pis
        if( $transresRequest ) {
            //$principalInvestigators = $transresRequest->getPrincipalInvestigators();
            //show ALL users of the site
            $principalInvestigators = null;
        } else {
            $principalInvestigators = null;
        }
        //echo "pi count=".count($principalInvestigators)."<br>";
        
        //testing
//        foreach($invoice->getInvoiceItems() as $invoiceItem) {
//            $itemCode = $invoiceItem->getItemCode();
//            echo $invoiceItem->getId().": itemCode=".$itemCode."<br>";
//        }
        
        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'invoice' => $invoice,
            'statuses' => $transresRequestUtil->getInvoiceStatuses(),
            'principalInvestigators' => $principalInvestigators,
            //'SecurityAuthChecker' => $this->container->get('security.authorization_checker'),
            'transres_request_util' => $transresRequestUtil
        );

        $disabled = true;

        if( $cycle == "new" ) {
            $disabled = false;
        }

        if( $cycle == "show" ) {
            $disabled = true;
        }

        if( $cycle == "edit" ) {
            $disabled = false;
        }

        if( $cycle == "download" ) {
            $disabled = true;
        }

        $form = $this->createForm(InvoiceType::class, $invoice, array(
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));

        return $form;
    }


    #[Route(path: '/get-billto-info/', name: 'translationalresearch_invoice_get_billto_info', methods: ['GET'], options: ['expose' => true])]
    public function getBillToInfoAction( Request $request ) {
        //set permission: project irb reviewer or admin
        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $userDownloadUtil = $this->container->get('user_download_utility');
        $em = $this->getDoctrine()->getManager();
        $newline = "\n";
        $res = "NotOK";

        $userId = trim((string)$request->get('userId') );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $billToUser = $em->getRepository(User::class)->find($userId);

        if( $billToUser ) {
            $res = $userDownloadUtil->getLabelSingleUser($billToUser,$newline,true);
        }

        $response = new Response($res);
        return $response;
    }

    #[Route(path: '/send-invoice-pdf-by-email/{oid}', name: 'translationalresearch_invoice_send_pdf_email', methods: ['GET'])]
    public function sendByEmailAction( Request $request, $oid ) {
        //$user = $this->getUser();
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$transresPermissionUtil = $this->container->get('transres_permission_util');
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $invoice = $em->getRepository(Invoice::class)->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

//        if( false === $transresPermissionUtil->isInvoiceBillingContact($invoice,$user) ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"send-invoice-pdf-email") === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to send the invoice pdf by email"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //Send the most recent Invoice PDF by Email
        $msg = $transresRequestUtil->sendInvoicePDFByEmail($invoice);

        $this->addFlash(
            'notice',
            $msg
        );


        return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
    }

    public function processInvoiceAfterSave( $invoice, $form, $user, $request ) {

        $transresPdfUtil = $this->container->get('transres_pdf_generator');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');

        $newline = "<br>"; //"\n";
        $msg = "";

        //echo "clicked btn=".$form->getClickedButton()->getName()."<br>";
        //exit('1');

        if( $form->getClickedButton() && 'saveAndGeneratePdf' === $form->getClickedButton()->getName() ) {
            //save and generate Invoice PDF
            //1) supposed that invoice has been already saved
            //2) generate Invoice PDF
            $res = $transresPdfUtil->generateInvoicePdf($invoice,$user,$request);

            //$filename = $res['filename'];
            //$pdf = $res['pdf'];
            $size = $res['size'];
            if( !$size ) {
                return "Invoice generation failed!";
            }

            //$msg = "PDF has been created for Invoice ID " . $invoice->getOid() . "; filename=".$filename."; size=".$size;
            //Draft Translation Research Invoice for work request APCP12-REQ12 has been generated
            //$msg = "Draft Translational Research Invoice ".$invoice->getOid()." has been generated"."; PDF size=".$size;
            //Draft invoice has been generated for the [Center for Translational Pathology]
            $msg = "Draft invoice ".$invoice->getOid()." has been generated for the ".$transresUtil->getBusinessEntityName()."; PDF size=".$size;

            //3) send by email to recipient (principalInvestigator)
            //Send the most recent Invoice PDF by Email
            $msgEmail = $transresRequestUtil->sendNewInvoicePDFGeneratedEmail($invoice);

            $msg = $msg . $newline . $msgEmail;
        }

        if( $form->getClickedButton() && 'saveAndGeneratePdfAndSendByEmail' === $form->getClickedButton()->getName() ) {
            //save, generate Invoice PDF and send by email to recipient (principalInvestigator)
            //1) supposed that invoice has been already saved
            //2) generate Invoice PDF
            $res = $transresPdfUtil->generateInvoicePdf($invoice,$user);

            //$filename = $res['filename'];
            //$pdf = $res['pdf'];
            $size = $res['size'];
            if( !$size ) {
                return "Invoice generation failed!";
            }

            //$msg = "PDF has been created for Invoice ID " . $invoice->getOid() . "; filename=".$filename."; size=".$size;
            //$msg = "Draft Translational Research Invoice ".$invoice->getOid()." has been generated and sent by email.". "; PDF size=".$size;
            //Draft invoice has been generated for the [Center for Translational Pathology]
            $msg = "Draft invoice ".$invoice->getOid()." has been generated and sent by email for the ".$transresUtil->getBusinessEntityName()."; PDF size=".$size;

            //3) send by email to recipient (principalInvestigator)
            //Send the most recent Invoice PDF by Email
            $msgSendByEmail = $transresRequestUtil->sendInvoicePDFByEmail($invoice);

            $msg = $msg . $newline . $msgSendByEmail;
        }

        if( !$msg ) {
            $msg = $newline . $msg;
        }

        return $msg;
    }

    /**
     * NOT USED
     */
    #[Route(path: '/change-status/{oid}', name: 'translationalresearch_invoice_change_status', methods: ['GET'])]
    public function changeStatusAction( Request $request, $oid ) {

//        if( false === $this->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        $user = $this->getUser();
        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $invoice = $em->getRepository(Invoice::class)->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

//        if( false === $transresRequestUtil->isInvoiceBillingContact($invoice,$user) ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"change-status") === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to change the invoice status"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $status = trim((string)$request->get('status') );

        $msg = "Invoice's (ID ".$invoice->getOid().") status has not been updated to '" . $status . "'";

        if( $status ) {
            $invoice->setStatus($status);
            $msg = "Changed Invoice's (ID ".$invoice->getOid().") status to '".$status."'";

            //If change to unpaid status, then display popup modale with paid amount.

            //If change status to fully paid, then update the invoice's paid amount with total amount.
            if( $status == "Paid in Full" ) {
                $total = $invoice->getTotal();
                if( $total ) {
                    $invoice->setPaid($total);
                    $invoice->setDue(NULL);

                    //update "Balance Due"
                    //$due = $invoice->getTotal() - $invoice->getPaid();
                    //$invoice->setDue($due);

                    $msg = $msg."<br>"."Invoice paid value set to '".$total."'";
                }
            }

            $em->persist($invoice);
            $em->flush();

            $eventType = "Invoice Updated";
            $transresUtil->setEventLog($invoice,$eventType,$msg);
        }

        $this->addFlash(
            'notice',
            $msg
        );

        //return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
        return $this->redirectToRoute('translationalresearch_invoice_index_type', array('id'=>null,'invoicetype'=>"All Invoices"));
    }

    #[Route(path: '/update-invoice-ajax/', name: 'translationalresearch_invoice_update_ajax', methods: ['POST'], options: ['expose' => true])]
    public function updateInvoiceAjaxAction( Request $request ) {
//        if( false === $this->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        $transresPdfUtil = $this->container->get('transres_pdf_generator');

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $res = "NotOK";

        $invoiceId = trim((string)$request->get('invoiceId') );
        $paid = trim((string)$request->get('paid') );
        $total = trim((string)$request->get('total') );
        $discountNumeric = trim((string)$request->get('discountNumeric') );
        $discountPercent = trim((string)$request->get('discountPercent') );
        $administrativeFee = trim((string)$request->get('administrativeFee') );
        $due = trim((string)$request->get('due') );
        $comment = trim((string)$request->get('comment') );
        $status = trim((string)$request->get('status') );

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Invoice'] by [Invoice::class]
        $invoice = $em->getRepository(Invoice::class)->find($invoiceId);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice id '" . $invoiceId . "'");
        }

//        if( false === $transresRequestUtil->isInvoiceBillingContact($invoice,$user) ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"update") === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to update this invoice"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$originalPaid = $invoice->getPaid();
        //$originalDue = $invoice->getDue();

        $invoiceSerializedOriginalStr = $invoice->getSerializeStr();


        if( $discountNumeric == 0 || $discountNumeric == "" ) {
            $discountNumeric = NULL;
        }
        if( $discountPercent == 0 || $discountPercent == "" ) {
            $discountPercent = NULL;
        }
        if( $administrativeFee == 0 || $administrativeFee == "" ) {
            $administrativeFee = NULL;
        }
        if( $paid == 0 || $paid == "" ) {
            $paid = NULL;
        }
        if( $due == 0 || $due == "" ) {
            $due = NULL;
        }
        if( $total == 0 || $total == "" ) {
            $total = NULL;
        }

        $invoice->setComment($comment);

        $invoice->setPaid($paid);

        $invoice->setDiscountNumeric($discountNumeric);
        $invoice->setDiscountPercent($discountPercent);
        $invoice->setAdministrativeFee($administrativeFee);

        $invoice->setTotal($total);

        //Re-Calculate Balance Due
        //$due = $invoice->getTotal() - $invoice->getPaid();
        $invoice->setDue($due);

        //change-status
        //$status = "Paid Partially";
        $invoice->setStatus($status);

        //Set Paid to Total and Due to 0 if values is unchanged
        //Please fix the display of “paid” and “due” amounts:
        // Once the invoice is marked as “paid in full” via any method,
        // the “paid” field/column should be set to the amount
        // that used to be “due” and the “due” amount should be set to zero.
        //paid is set to 'total' instead of 'due', because due might be updated incrementally by multiple partial payment until it reaches the 'total'
        if( $status == "Paid in Full" ) {
            $invoice->setPaid($total);
            $invoice->setDue(NULL);
//            if( $paid != $originalPaid ) {
//                //don't update if changed
//            } else {
//                $invoice->setPaid($total);
//                $invoice->setDue(NULL);
//            }
//            if( $due != $originalDue ) {
//                //don't update if changed
//            } else {
//                $invoice->setDue(NULL);
//            }
        }

        //update subsidy for updated invoice by ajax
        $transresRequestUtil->updateInvoiceSubsidy($invoice);

        $em->persist($invoice);
        $em->flush();

        $eventType = "Invoice Updated";
        $msg = "Invoice's (ID ".$invoice->getOid().") Paid ($) value has been updated to '" . $invoice->getPaid() . "'"
            . " and status changed to '$status'";

        //changes
        //$invoiceUpdatedDb = $em->getRepository('AppTranslationalResearchBundle:Invoice')->findOneByOid($invoice->getOid());
        $invoiceSerializedUpdatedStr = $invoice->getSerializeStr();
        if( $invoiceSerializedUpdatedStr != $invoiceSerializedOriginalStr ) {
            $chanesStr =    "<strong>Original Invoice:</strong><br>" . $invoiceSerializedOriginalStr . "<br>" .
                "<strong>Updated Invoice:</strong><br>" . $invoiceSerializedUpdatedStr;
            $msg = $msg . "<br>" . $chanesStr;
        }

        $transresUtil->setEventLog($invoice,$eventType,$msg);

        //generate Invoice PDF
        $transresPdfUtil->generateInvoicePdf($invoice,$user,$request);

        $res = "OK";

        $response = new Response($res);
        return $response;
    }


    #[Route(path: '/download-spreadsheet/', name: 'translationalresearch_download_invoice_spreadsheet', methods: ['POST'])]
    public function downloadInvoicesCsvAction( Request $request ) {
        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresRequestUtil = $this->container->get('transres_request_util');

        $ids = $request->request->get('ids');
        //echo "ids=".$ids."<br>";
        //exit('111');

        $idsArr = explode('-', $ids);
        $idsArr = array_reverse($idsArr);

        //$fileName = "Invoices".".xlsx"; //cell type can not be set in xlsx
        $fileName = "Invoices".".csv";

        $transresRequestUtil->createtInvoicesCsvSpout( $idsArr, $fileName );

        exit();
    }

    /**
     * Allow all ROLE_TRANSRES_BILLING_ADMIN and PI and BILLING CONTACTS to have access to the JV download function
     * JV unpaid billing summary template.xlsx
     * http://127.0.0.1/order/index_dev.php/translational-research/invoice/download-unpaid-spreadsheet/
     */
    #[Route(path: '/download-unpaid-spreadsheet/', name: 'translationalresearch_download_unpaid_invoice_spreadsheet', methods: ['GET', 'POST'])]
    public function downloadUnpaidInvoicesCsvAction( Request $request ) {
        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //testing
        //if( false === $this->isGranted('ROLE_PLATFORM_ADMIN') ) {
        //    exit("This functionality is under maintenance. Please try again later.");
        //}

        $transresRequestUtil = $this->container->get('transres_request_util');

        $ids = $request->request->get('ids');
        //echo "ids=".$ids."<br>";
        //exit('exit downloadUnpaidInvoicesCsvAction');

        $idsArr = explode('-', $ids);
        $idsArr = array_reverse($idsArr);

        //remove duplicates
        $idsArr = array_unique($idsArr);

        //$fileName = "Invoices".".xlsx"; //cell type can not be set in xlsx
        //$template = null;
        $template = __DIR__ . '/../Util/JV_eForm_Trans_Upload_Template.xlsx';

        //Unpaid-Billing-Summary-PI-FirstName-LastName-Project-ID-ID1-ID2-ID3-Generated-on-MM-DD-YYYY-at-HH-MM-SS.xlsx
        //$fileName = "Unpaid-Billing-Summary-Invoices".".csv";
        
        $transresRequestUtil->exportUnpaidInvoices( $idsArr, $template );

        exit();
    }

//    /**
//     * @Route("/unpaid-invoice-reminder/show-summary", name="translationalresearch_unpaid_invoice_reminder_show", methods={"GET"})
//     * @Route("/unpaid-invoice-reminder/send-emails", name="translationalresearch_unpaid_invoice_reminder_send", methods={"GET"})
//     */
//    public function unpaidInvoiceReminderAction( Request $request )
//    {
//        if( false === $this->isGranted('ROLE_TRANSRES_ADMIN') ) {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
//
//        $transresRequestUtil = $this->container->get('transres_request_util');
//
//        $routeName = $request->get('_route');
//        $showSummary = false;
//
//        if( $routeName == "translationalresearch_unpaid_invoice_reminder_show" ) {
//            $showSummary = true;
//        }
//
//        $results = $transresRequestUtil->sendReminderUnpaidInvoices($showSummary);
//
//        if( $showSummary === true ) {
//            $invoiceCounter = 0;
//
//            foreach($results as $result) {
//                $invoiceCounter = $invoiceCounter + count($result);
//            }
//
//            return $this->render("AppTranslationalResearchBundle/Invoice/unpaid-invoice-index.html.twig",
//                array(
//                    'title' => $invoiceCounter." Unpaid Invoices",
//                    'invoiceGroups' => $results,
//                    'invoiceCounter' => $invoiceCounter
//                )
//            );
//        }
//
//        $this->addFlash(
//            'notice',
//            "Sending reminder emails for unpaid invoices: ".$results
//        );
//
//        return $this->redirectToRoute('translationalresearch_invoice_index_filter');
//    }
}
