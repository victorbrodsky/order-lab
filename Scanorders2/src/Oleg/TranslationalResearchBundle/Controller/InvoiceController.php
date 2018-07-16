<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\TranslationalResearchBundle\Entity\Invoice;
use Oleg\TranslationalResearchBundle\Entity\TransResRequest;
use Oleg\TranslationalResearchBundle\Form\FilterInvoiceType;
use Oleg\TranslationalResearchBundle\Form\InvoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Oleg\UserdirectoryBundle\Entity\User;

/**
 * Invoice controller.
 *
 * @Route("invoice")
 */
class InvoiceController extends Controller
{

    //* @Route("/list-all/", name="translationalresearch_invoice_index_all")
    //* @Route("/list-all-my/", name="translationalresearch_invoice_index_all_my")
    //* @Route("/list-all-issued/", name="translationalresearch_invoice_index_all_issued")
    //* @Route("/list-all-pending/", name="translationalresearch_invoice_index_all_pending")

    /**
     * Lists all invoice entities.
     *
     * @Route("/list-request/{id}", name="translationalresearch_invoice_index")
     * @Route("/list/", name="translationalresearch_invoice_index_filter")
     * @Template("OlegTranslationalResearchBundle:Invoice:index.html.twig")
     * @Method("GET")
     */
    public function indexAction(Request $request, TransResRequest $transresRequest=null)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresRequestUtil = $this->get('transres_request_util');
        $routeName = $request->get('_route');
        $advancedFilter = 0;
        $title = "List of Invoices";
        $metaTitle = null;

        $repository = $em->getRepository('OlegTranslationalResearchBundle:Invoice');
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');

        $dql->leftJoin('invoice.submitter','submitter');
        $dql->leftJoin('invoice.salesperson','salesperson');
        $dql->leftJoin('salesperson.infos','salespersonInfos');
        $dql->leftJoin('invoice.transresRequest','transresRequest');
        $dql->leftJoin('invoice.principalInvestigator','principalInvestigator');

        $dqlParameters = array();

        if( $routeName == "translationalresearch_invoice_index" ) {

            //Title
            $requestUrl = $transresRequestUtil->getRequestShowUrl($transresRequest);
            $thisLink = "<a href=".$requestUrl.">"."Request ID ".$transresRequest->getOid()."</a>";
            //$title = "List of Invoices for Request ID ".$transresRequest->getOid();
            $title = "List of Invoices for " . $thisLink;
            $metaTitle = "List of Invoices for Request ID ".$transresRequest->getOid();

            $dql->where("transresRequest.id = :transresRequestId");
            $dqlParameters["transresRequestId"] = $transresRequest->getId();
        }

        //////// create filter //////////
        $versions = $transresRequestUtil->getInvoiceComplexVersions(100);

        $params = array(
            'routeName'=>$routeName,
            'transresRequest'=>$transresRequest,
            'versions'=>$versions,
            'statuses' => $transresRequestUtil->getInvoiceStatuses(),
        );
        $filterform = $this->createForm(FilterInvoiceType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));

        $filterform->handleRequest($request);

        $filterType = trim( $request->get('type') );
        $filterTitle = trim( $request->get('title') );

        $filterType = str_replace("-"," ",$filterType);

        if( $filterType ) {
            if( $filterType == "All Invoices" ) {
                //filter nothing
                $title = "All Invoices";
            }
            if( $filterType == "All Issued Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[status][0]' => "Unpaid/Issued",
                        'filter[status][1]' => "Paid in Full",
                        'filter[status][2]' => "Paid Partially",
                        'filter[status][3]' => 'Refunded Fully',
                        'filter[status][4]' => 'Refunded Partially',
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "All Pending Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[status][]' => "Pending",
                        'title' => $filterType,
                    )
                );
            }

            //Personal Invoices
//            if( $filterType == "My Invoices (I am Submitter, Salesperson or PI)" ) {
//                return $this->redirectToRoute(
//                    'translationalresearch_invoice_index_filter',
//                    array(
//                        'filter[submitter]' => $user->getId(),
//                        'filter[salesperson]' => $user->getId(),
//                        'filter[principalInvestigator]' => $user->getId(),
//                    )
//                );
//            }
//            if( $filterType == "Invoices Sent to Me" ) {
//                return $this->redirectToRoute(
//                    'translationalresearch_invoice_index_filter',
//                    array(
//                        'filter[principalInvestigator]' => $user->getId(),
//                    )
//                );
//            }
            if( $filterType == "My Invoices" ) {
                //all Invoices for all Work Requests issued for Projects where I am listed in any way (submitter, PI, etc).
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[submitter]' => $user->getId(),
                        'filter[salesperson]' => $user->getId(),
                        'filter[principalInvestigator]' => $user->getId(),

                        'filter[status][0]' => "Unpaid/Issued",
                        'filter[status][1]' => "Paid in Full",
                        'filter[status][2]' => "Paid Partially",
                        'filter[status][3]' => 'Refunded Fully',
                        'filter[status][4]' => 'Refunded Partially',

                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Issued invoices I generated" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[submitter]' => $user->getId(),
                        'filter[status][0]' => "Unpaid/Issued",
                        'filter[status][1]' => "Paid in Full",
                        'filter[status][2]' => "Paid Partially",
                        'filter[status][3]' => 'Refunded Fully',
                        'filter[status][4]' => 'Refunded Partially',
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Invoices where I am the salesperson" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[salesperson]' => $user->getId(),
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Invoices where I am the PI" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[principalInvestigator]' => $user->getId(),
                        'title' => $filterType,
                    )
                );
            }
            //"Unpaid Invoices where I am a PI", "Unpaid Invoices sent to Me"
            if( $filterType == "Unpaid Invoices where I am the PI" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[principalInvestigator]' => $user->getId(),
                        'filter[status][0]' => "Unpaid/Issued",
                        'title' => $filterType,
                    )
                );
            }

            //Latest
            if( $filterType == "Latest Versions of All Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Latest Versions of Issued (Unpaid) Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Unpaid/Issued",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Latest Versions of Pending (Unissued) Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Pending",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Latest Versions of Paid Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Paid in Full",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Latest Versions of Partially Paid Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Paid Partially",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Latest Versions of Paid and Partially Paid Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Paid in Full",
                        'filter[status][1]' => "Paid Partially",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Latest Versions of Canceled Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Canceled",
                        'title' => $filterType,
                    )
                );
            }

            //Old
            if( $filterType == "Old Versions of All Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Old Versions of Issued (Unpaid) Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Unpaid/Issued",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Old Versions of Pending (Unissued) Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Pending",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Old Versions of Paid Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Paid in Full",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Old Versions of Partially Paid Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Paid Partially",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Old Versions of Paid and Partially Paid Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Paid in Full",
                        'filter[status][1]' => "Paid Partially",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "Old Versions of Canceled Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Canceled",
                        'title' => $filterType,
                    )
                );
            }

        } else {
            $submitter = $filterform['submitter']->getData();
            $principalInvestigator = $filterform['principalInvestigator']->getData();
            $salesperson = $filterform['salesperson']->getData();
            $status = $filterform['status']->getData();
            $idSearch = $filterform['idSearch']->getData();
            $totalMin = $filterform['totalMin']->getData();
            $totalMax = $filterform['totalMax']->getData();
            $startDate = $filterform['startDate']->getData();
            $endDate = $filterform['endDate']->getData();
            $version = $filterform['version']->getData();
            $fundingNumber = $filterform['fundingNumber']->getData();
            $fundingType = $filterform['fundingType']->getData();
        }
        ////// EOF create filter //////////

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

        if( $filterTitle == "My Invoices" ) {
            //all Invoices for all Work Requests issued for Projects where I am listed in any way (submitter, PI, etc).
            //Use OR
            $dql->andWhere("submitter.id = :userId OR principalInvestigator.id = :userId OR salesperson.id = :userId");
            $dqlParameters["userId"] = $user->getId();
            //set all user filter to NULL to prevent AND query conditions
            $submitter = null;
            $principalInvestigator = null;
            $salesperson = null;
        }

        if( $submitter ) {
            //echo "Submitter=$submitter<br>";
            $dql->andWhere("submitter.id = :submitterId");
            $dqlParameters["submitterId"] = $submitter->getId();
        }

        if( $status ) {
            //$statusStr = "'".implode("','",$status)."'";
            //$dql->andWhere("invoice.status IN (".$statusStr.")");
            $dql->andWhere("invoice.status IN (:statuses)");
            $dqlParameters["statuses"] = $status;
        }

        if( $idSearch ) {
            $dql->andWhere("invoice.oid LIKE :idSearch");
            $dqlParameters["idSearch"] = "%".$idSearch."%";
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
        if( $principalInvestigator ) {
            //echo "PI=$principalInvestigator <br>";
            $dql->andWhere("principalInvestigator.id = :principalInvestigatorId");
            $dqlParameters["principalInvestigatorId"] = $principalInvestigator->getId();
            $advancedFilter++;
        }

        if( $startDate ) {
            $dql->andWhere('invoice.dueDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }
        if( $endDate ) {
            $endDate->modify('+1 day');
            $dql->andWhere('invoice.dueDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }

        if( $salesperson ) {
            //echo "salesperson=$salesperson<br>";
            $dql->andWhere("salesperson.id = :salespersonId");
            $dqlParameters["salespersonId"] = $salesperson->getId();
            $advancedFilter++;
        }

        if( $totalMin ) {
            $dql->andWhere('invoice.total >= :totalMin');
            $dqlParameters['totalMin'] = $totalMin;
            $advancedFilter++;
        }

        if( $totalMax ) {
            $dql->andWhere('invoice.total <= :totalMax');
            $dqlParameters['totalMax'] = $totalMax;
            $advancedFilter++;
        }

        if( $version ) {
            if( $version == "Latest" ) {
                $dql->andWhere('invoice.latestVersion = TRUE');
            } elseif( $version == "Old" ) {
                $dql->andWhere('invoice.latestVersion != TRUE ');
            } else {
                $dql->andWhere('invoice.version = :version');
                $dqlParameters['version'] = $version;
            }
            $advancedFilter++;
        }

        if( $fundingNumber ) {
            $dql->andWhere("invoice.fundedAccountNumber LIKE :fundedAccountNumber");
            $dqlParameters["fundedAccountNumber"] = "%".$fundingNumber."%";
            $advancedFilter++;
        }

        if( $fundingType ) {
            if( $fundingType == "Funded" ) {
                $dql->andWhere("invoice.fundedAccountNumber IS NOT NULL");
                $advancedFilter++;
            }
            if( $fundingType == "Non-Funded" ) {
                $dql->andWhere("invoice.fundedAccountNumber IS NULL");
                $advancedFilter++;
            }
        }

        $limit = 30;
        $query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        //echo "query=".$query->getSql()."<br>";

        $paginationParams = array(
            'defaultSortFieldName' => 'invoice.id',
            'defaultSortDirection' => 'DESC',
            'wrap-queries' => true
        );

        $paginator  = $this->get('knp_paginator');
        $invoices = $paginator->paginate(
            $query,
            $request->query->get('page', 1),    /*page number*/
            $limit,                             /*limit per page*/
            $paginationParams
        );

        //$latestVersion = $transresRequestUtil->getLatestInvoiceVersion($transresRequest);

        //echo "filterType=".$filterType."<br>";
        //echo "title=".$title."<br>";
        if( $filterTitle ) {
            $title = $filterTitle;
        }
        if( !$metaTitle ) {
            $metaTitle = $title;
        }

        return array(
            'invoices' => $invoices,
            'transresRequest' => $transresRequest,
            'title' => $title,
            'metaTitle' => $metaTitle,
            'filterform' => $filterform->createView(),
            'advancedFilter' => $advancedFilter,
            //'latestVersion' => $latestVersion
        );
    }

    /**
     * Creates a new invoice entity.
     *
     * @Route("/new/{id}", name="translationalresearch_invoice_new")
     * @Template("OlegTranslationalResearchBundle:Invoice:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, TransResRequest $transresRequest)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        //$em = $this->getDoctrine()->getManager();
        $transresUtil = $this->get('transres_util');
        $transresRequestUtil = $this->get('transres_request_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$user = null; //testing
        $cycle = "new";

        $project = $transresRequest->getProject();

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $invoice = $transresRequestUtil->createNewInvoice($transresRequest,$user);

        $originalInvoiceStatus = $invoice->getStatus();

        $form = $this->createInvoiceForm($invoice,$cycle,$transresRequest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //exit('new');

            $msg = $transresRequestUtil->createSubmitNewInvoice($transresRequest,$invoice);

            $msg2 = $this->processInvoiceAfterSave($invoice,$form,$user);

            $invoiceStatus = $invoice->getStatus();
            if( $invoiceStatus != $originalInvoiceStatus ) {
                $transresRequestUtil->syncInvoiceRequestStatus($invoice, $invoiceStatus);
            }

            //$msg = "New Invoice has been successfully created for the request ID ".$transresRequest->getOid();

            $msg = $msg . $msg2;

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
        }

        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            'form' => $form->createView(),
            'title' => "New Invoice for the Request ID ".$transresRequest->getOid(),
            'cycle' => $cycle
        );
    }

    /**
     * Finds and displays a invoice entity.
     *
     * @Route("/show/{oid}", name="translationalresearch_invoice_show")
     * @Template("OlegTranslationalResearchBundle:Invoice:new.html.twig")
     * @Method("GET")
     */
    public function showAction(Request $request, $oid)
    {
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();

        //1) try to find by oid
        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            //2) try to find by id
            $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->find($oid);
        }
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"view") === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to view this invoice"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "show";
        $routeName = $request->get('_route');

        $form = $this->createInvoiceForm($invoice,$cycle);

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
        );
    }

    /**
     * Displays a form to edit an existing invoice entity.
     *
     * @Route("/edit/{oid}", name="translationalresearch_invoice_edit")
     * @Template("OlegTranslationalResearchBundle:Invoice:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $oid)
    {

//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->get('transres_util');
        $transresRequestUtil = $this->get('transres_request_util');

        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

        if( $invoice->getLatestVersion() !== true ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                "The old version of the invoice can not be edited."
            );
            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));

        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"update") === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to edit this invoice"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $invoiceSerializedOriginalStr = $invoice->getSerializeStr();
        //echo "invoiceSerializedOriginalStr=$invoiceSerializedOriginalStr<br>";
        //exit();

        //Get $transresRequest (Assume invoice has a single $transresRequest)
        $transresRequest = $invoice->getTransresRequest();

        $originalInvoiceStatus = $invoice->getStatus();

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $cycle = "edit";

        //$deleteForm = $this->createDeleteForm($invoice);

        //$editForm = $this->createForm('Oleg\TranslationalResearchBundle\Form\InvoiceType', $invoice);
        $editForm = $this->createInvoiceForm($invoice,$cycle);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            //update user
            $invoice->setUpdateUser($user);

            //update oid: don't update Invoice version on edit. Only the last version can be edited.

            //use the values in Invoice’s Quantity fields to overwrite/update the associated Request’s "Completed #" fields
            $transresRequestUtil->updateRequestCompletedFieldsByInvoice($invoice);

            $transresRequestUtil->updateInvoiceStatus($invoice);

            $em->flush();

            $invoiceStatus = $invoice->getStatus();
            if( $invoiceStatus != $originalInvoiceStatus ) {
                $transresRequestUtil->syncInvoiceRequestStatus($invoice, $invoiceStatus);
            }

            $msg2 = $this->processInvoiceAfterSave($invoice,$editForm,$user);

            $msg = "Invoice with ID ".$invoice->getOid()." has been updated.";

            $msg = $msg . $msg2;

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Invoice Updated";
            $msg = "Invoice with ID ".$invoice->getOid()." has been updated.";

            //changes
            //$invoiceUpdatedDb = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($invoice->getOid());
            $invoiceSerializedUpdatedStr = $invoice->getSerializeStr();
            if( $invoiceSerializedUpdatedStr != $invoiceSerializedOriginalStr ) {
                $chanesStr =    "<strong>Original Invoice:</strong><br>" . $invoiceSerializedOriginalStr . "<br>" .
                                "<strong>Updated Invoice:</strong><br>" . $invoiceSerializedUpdatedStr;
                $msg = $msg . "<br>" . $chanesStr;
            }

            $transresUtil->setEventLog($invoice,$eventType,$msg);

            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
        }

        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            'form' => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
            'cycle' => $cycle,
            'title' => "Invoice ID ".$invoice->getOid(),
        );
    }

    /**
     * Deletes a invoice entity.
     *
     * @Route("/delete/{id}", name="translationalresearch_invoice_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Invoice $invoice)
    {
        //exit("Delete is not allowed.");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->get('transres_util');

        $form = $this->createDeleteForm($invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $msg = "Invoice with ID ".$invoice->getOid()." has been successfully deleted.";

            $em = $this->getDoctrine()->getManager();
            $em->remove($invoice);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Invoice Deleted";
            $transresUtil->setEventLog($invoice,$eventType,$msg);
        }

        return $this->redirectToRoute('translationalresearch_invoice_index_filter', array('type'=>"All Invoices"));
    }

    /**
     * Generate Invoice PDF
     *
     * @Route("/generate-invoice-pdf/{oid}", name="translationalresearch_invoice_generate_pdf")
     * @Method("GET")
     */
    public function generateInvoicePdfAction(Request $request, $oid) {

        $em = $this->getDoctrine()->getManager();
        $transresPdfUtil = $this->get('transres_pdf_generator');
        $transresRequestUtil = $this->get('transres_request_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

        if( false === $transresRequestUtil->isInvoiceBillingContact($invoice,$user) ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"view") === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to view this invoice"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $res = $transresPdfUtil->generateInvoicePdf($invoice,$user);
        
        $filename = $res['filename'];
        $pdf = $res['pdf'];
        $size = $res['size'];

        $msg = "PDF has been created for Invoice ID " . $invoice->getOid() . "; filename=".$filename."; size=".$size;

        //exit("<br><br>".$msg);

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        //return $this->redirectToRoute('translationalresearch_invoice_index_all');
        return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));

    }

    /**
     * Show PDF version of invoice
     *
     * @Route("/download-invoice-pdf/{oid}", name="translationalresearch_invoice_download")
     * @Template("OlegTranslationalResearchBundle:Invoice:pdf-show.html.twig")
     * @Method("GET")
     */
    public function downloadPdfAction(Request $request, $oid)
    {
        //$em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $logger = $this->container->get('logger');
        //$routeName = $request->get('_route');
        $userSecUtil = $this->container->get('user_security_utility');
        $transresRequestUtil = $this->get('transres_request_util');

        //download: user or localhost
        //$user = $this->get('security.token_storage')->getToken()->getUser();
        //download link can be accessed by a console as localhost with role IS_AUTHENTICATED_ANONYMOUSLY, so simulate login manually
        if( !($user instanceof User) ) {
            $firewall = 'ldap_translationalresearch_firewall';
            $systemUser = $userSecUtil->findSystemUser();
            if( $systemUser ) {
                $token = new UsernamePasswordToken($systemUser, null, $firewall, $systemUser->getRoles());
                $this->get('security.token_storage')->setToken($token);
                //$this->get('security.token_storage')->setToken($token);
            }
            $logger->notice("Download view: Logged in as systemUser=".$systemUser);
        } else {
            $logger->notice("Download view: Token user is valid security.token_storage user=".$user);
        }

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"view") === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to view this invoice"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "download";
        //$routeName = $request->get('_route');

        //$form = $this->createInvoiceForm($invoice,$cycle);

        //$deleteForm = $this->createDeleteForm($invoice);

        return array(
            //'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            //'form' => $form->createView(),
            //'delete_form' => $deleteForm->createView(),
            'cycle' => $cycle,
            'title' => "Invoice ID ".$invoice->getOid(),
        );
    }

    /**
     * Show the most recent PDF version of invoice
     *
     * @Route("/download-recent-invoice-pdf/{oid}", name="translationalresearch_invoice_download_recent")
     * @Template("OlegTranslationalResearchBundle:Invoice:pdf-show.html.twig")
     * @Method("GET")
     */
    public function downloadRecentPdfAction(Request $request, $oid)
    {
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();
        //$logger = $this->container->get('logger');
        //$routeName = $request->get('_route');
        //$userSecUtil = $this->container->get('user_security_utility');
        $transresRequestUtil = $this->get('transres_request_util');
        //$transresUtil = $this->get('transres_util');

        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

        //$transresRequest = $invoice->getTransresRequest();
        //$project = $transresRequest->getProject();

//        if(
//            false === $transresRequestUtil->isInvoiceBillingContact($invoice,$user) &&
//            false === $transresUtil->isProjectRequester($project) &&
//            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY') &&
//            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP')
//        ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"view") === false ) {
            $this->get('session')->getFlashBag()->add(
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
            $this->get('session')->getFlashBag()->add(
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
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresRequestUtil = $this->get('transres_request_util');

        if( !$transresRequest ) {
            $transresRequest = $invoice->getTransresRequest();
        }

//        $principalInvestigators = $invoice->getPrincipalInvestigators();
//        $pisArr = array();
//        foreach($principalInvestigators as $principalInvestigator) {
//            //$em->persist($principalInvestigator);
//            $pi = $em->getRepository('OlegUserdirectoryBundle:User')->find($principalInvestigator->getId());
//            $pisArr[] = $pi;
//        }
        //$piEm = $this->getDoctrine()->getManager('OlegTranslationalResearchBundle:Invoice');
        //$piEm = $invoice->getEntityManagerName();
        //$piEm = $this->getDoctrine()->getManager('default');

        //PIs of the request's pis
        if( $transresRequest ) {
            $principalInvestigators = $transresRequest->getPrincipalInvestigators();
        }
        //echo "pi count=".count($principalInvestigators)."<br>";
        
        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'invoice' => $invoice,
            'statuses' => $transresRequestUtil->getInvoiceStatuses(),
            'principalInvestigators' => $principalInvestigators,
            //'piEm' => $piEm,
            'SecurityAuthChecker' => $this->get('security.authorization_checker'),
        );

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


    /**
     * @Route("/get-billto-info/", name="translationalresearch_invoice_get_billto_info", options={"expose"=true})
     * @Method({"GET"})
     */
    public function getBillToInfoAction( Request $request ) {
        //set permission: project irb reviewer or admin
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $userDownloadUtil = $this->get('user_download_utility');
        $em = $this->getDoctrine()->getManager();
        $newline = "\n";
        $res = "NotOK";

        $userId = trim( $request->get('userId') );
        $billToUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($userId);

        if( $billToUser ) {
            $res = $userDownloadUtil->getLabelSingleUser($billToUser,$newline,true);
        }

        $response = new Response($res);
        return $response;
    }

    /**
     * @Route("/send-invoice-pdf-by-email/{oid}", name="translationalresearch_invoice_send_pdf_email")
     * @Method({"GET"})
     */
    public function sendByEmailAction( Request $request, $oid ) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresRequestUtil = $this->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();

        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

        if( false === $transresRequestUtil->isInvoiceBillingContact($invoice,$user) ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"send-invoice-pdf-email") === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to send the invoice pdf by email"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //Send the most recent Invoice PDF by Email
        $msg = $transresRequestUtil->sendInvoicePDFByEmail($invoice);

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );


        return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
    }

    public function processInvoiceAfterSave( $invoice, $form, $user ) {

        $transresPdfUtil = $this->get('transres_pdf_generator');
        $transresRequestUtil = $this->get('transres_request_util');

        $newline = "<br>"; //"\n";
        $msg = "";

        //echo "clicked btn=".$form->getClickedButton()->getName()."<br>";
        //exit('1');

        if( $form->getClickedButton() && 'saveAndGeneratePdf' === $form->getClickedButton()->getName() ) {
            //save and generate Invoice PDF
            //1) supposed that invoice has been already saved
            //2) generate Invoice PDF
            $res = $transresPdfUtil->generateInvoicePdf($invoice,$user);

            $filename = $res['filename'];
            //$pdf = $res['pdf'];
            $size = $res['size'];

            $msg = "PDF has been created for Invoice ID " . $invoice->getOid() . "; filename=".$filename."; size=".$size;
        }

        if( $form->getClickedButton() && 'saveAndGeneratePdfAndSendByEmail' === $form->getClickedButton()->getName() ) {
            //save, generate Invoice PDF and send by email to recipient (principalInvestigator)
            //1) supposed that invoice has been already saved
            //2) generate Invoice PDF
            $res = $transresPdfUtil->generateInvoicePdf($invoice,$user);

            $filename = $res['filename'];
            //$pdf = $res['pdf'];
            $size = $res['size'];

            $msg = "PDF has been created for Invoice ID " . $invoice->getOid() . "; filename=".$filename."; size=".$size;

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
     * @Route("/change-status/{oid}", name="translationalresearch_invoice_change_status")
     * @Method({"GET"})
     */
    public function changeStatusAction( Request $request, $oid ) {

//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresRequestUtil = $this->get('transres_request_util');
        $transresUtil = $this->get('transres_util');
        $em = $this->getDoctrine()->getManager();

        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

//        if( false === $transresRequestUtil->isInvoiceBillingContact($invoice,$user) ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"change-status") === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to change the invoice status"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $status = trim( $request->get('status') );

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

                    //update "Balance Due"
                    $due = $invoice->getTotal() - $invoice->getPaid();
                    $invoice->setDue($due);

                    $msg = $msg."<br>"."Invoice paid value set to '".$total."'";
                }
            }

            $em->persist($invoice);
            $em->flush();

            $eventType = "Invoice Updated";
            $transresUtil->setEventLog($invoice,$eventType,$msg);
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        //return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
        return $this->redirectToRoute('translationalresearch_invoice_index_filter', array('id'=>null,'type'=>"All Invoices"));
    }

    /**
     * @Route("/update-invoice-ajax/", name="translationalresearch_invoice_update_ajax", options={"expose"=true})
     * @Method({"POST"})
     */
    public function updateInvoiceAjaxAction( Request $request ) {
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        $transresRequestUtil = $this->get('transres_request_util');
        $transresUtil = $this->get('transres_util');
        $transresPdfUtil = $this->get('transres_pdf_generator');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $res = "NotOK";

        $oid = trim( $request->get('invoiceOid') );
        $paid = trim( $request->get('paid') );
        $total = trim( $request->get('total') );
        $discountNumeric = trim( $request->get('discountNumeric') );
        $discountPercent = trim( $request->get('discountPercent') );
        $due = trim( $request->get('due') );
        $comment = trim( $request->get('comment') );
        $status = trim( $request->get('status') );

        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

//        if( false === $transresRequestUtil->isInvoiceBillingContact($invoice,$user) ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        // Check if user allowed to access by the project's specialty
//        if( $transresRequestUtil->isUserAllowedAccessInvoiceBySpecialty($invoice) === false ) {
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                "You don't have a permission to access this specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
        if( $transresRequestUtil->isUserHasInvoicePermission($invoice,"update") === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to update this invoice"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $invoiceSerializedOriginalStr = $invoice->getSerializeStr();


        if( $discountNumeric == 0 ) {
            $discountNumeric = NULL;
        }
        if( $discountPercent == 0 ) {
            $discountPercent = NULL;
        }
        if( $paid == 0 ) {
            $paid = NULL;
        }
        if( $due == 0 ) {
            $due = NULL;
        }
        if( $total == 0 ) {
            $total = NULL;
        }

        $invoice->setComment($comment);

        $invoice->setPaid($paid);

        $invoice->setDiscountNumeric($discountNumeric);
        $invoice->setDiscountPercent($discountPercent);

        $invoice->setTotal($total);

        //Re-Calculate Balance Due
        //$due = $invoice->getTotal() - $invoice->getPaid();
        $invoice->setDue($due);

        //change-status
        //$status = "Paid Partially";
        $invoice->setStatus($status);

        $em->persist($invoice);
        $em->flush();

        $eventType = "Invoice Updated";
        $msg = "Invoice's (ID ".$invoice->getOid().") Paid ($) value has been updated to '" . $paid . "'"
            . " and status changed to '$status'";

        //changes
        //$invoiceUpdatedDb = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($invoice->getOid());
        $invoiceSerializedUpdatedStr = $invoice->getSerializeStr();
        if( $invoiceSerializedUpdatedStr != $invoiceSerializedOriginalStr ) {
            $chanesStr =    "<strong>Original Invoice:</strong><br>" . $invoiceSerializedOriginalStr . "<br>" .
                "<strong>Updated Invoice:</strong><br>" . $invoiceSerializedUpdatedStr;
            $msg = $msg . "<br>" . $chanesStr;
        }

        $transresUtil->setEventLog($invoice,$eventType,$msg);

        //generate Invoice PDF
        $transresPdfUtil->generateInvoicePdf($invoice,$user);

        $res = "OK";

        $response = new Response($res);
        return $response;
    }
}
