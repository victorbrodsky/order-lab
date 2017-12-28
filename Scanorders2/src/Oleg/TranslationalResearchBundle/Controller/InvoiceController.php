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

        $repository = $em->getRepository('OlegTranslationalResearchBundle:Invoice');
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');

        $dql->leftJoin('invoice.submitter','submitter');
        $dql->leftJoin('invoice.salesperson','salesperson');
        $dql->leftJoin('invoice.transresRequests','transresRequests');
        $dql->leftJoin('invoice.principalInvestigators','principalInvestigators');

        $dqlParameters = array();

        if( $routeName == "translationalresearch_invoice_index" ) {
            $title = "List of Invoices for Request ID ".$transresRequest->getOid();
            $dql->where("transresRequests.id = :transresRequestId");
            $dqlParameters["transresRequestId"] = $transresRequest->getId();
        }

        //////// create filter //////////
        $versions = $transresRequestUtil->getInvoiceComplexVersions(100);

        $params = array(
            'routeName'=>$routeName,
            'transresRequest'=>$transresRequest,
            'versions'=>$versions
        );
        $filterform = $this->createForm(FilterInvoiceType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));

        $filterform->handleRequest($request);

        $filterType = trim( $request->get('type') );

        if( $filterType ) {
            if( $filterType == "All Invoices" ) {
                //filter nothing
                $title = "List of All Invoices";
            }
            if( $filterType == "My Invoices" ) {
                //$title = "List of All My Invoices";
                //$submitter = $user;
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[submitter]' => $user->getId()
                    )
                );
            }
            if( $filterType == "All Issued Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[status][0]' => "Unpaid/Issued",
                        'filter[status][1]' => "Paid in Full",
                        'filter[status][2]' => "Paid Partially",
                    )
                );
            }
            if( $filterType == "All Pending Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[status][]' => "Pending"
                    )
                );
            }
            //Latest
            if( $filterType == "Latest Versions of All Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest"
                    )
                );
            }
            if( $filterType == "Latest Versions of Issued (Unpaid) Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Unpaid/Issued",
                    )
                );
            }
            if( $filterType == "Latest Versions of Pending (Unissued) Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Pending"
                    )
                );
            }
            if( $filterType == "Latest Versions of Paid Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Paid in Full",
                    )
                );
            }
            if( $filterType == "Latest Versions of Partially Paid Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Paid Partially",
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
                    )
                );
            }
            if( $filterType == "Latest Versions of Canceled Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Latest",
                        'filter[status][0]' => "Canceled",
                    )
                );
            }

            //Old
            if( $filterType == "Old Versions of All Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                    )
                );
            }
            if( $filterType == "Old Versions of Issued (Unpaid) Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Unpaid/Issued",
                    )
                );
            }
            if( $filterType == "Old Versions of Pending (Unissued) Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Pending",
                    )
                );
            }
            if( $filterType == "Old Versions of Paid Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Paid in Full",
                    )
                );
            }
            if( $filterType == "Old Versions of Partially Paid Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Paid Partially",
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
                    )
                );
            }
            if( $filterType == "Old Versions of Canceled Invoices" ) {
                return $this->redirectToRoute(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[version]' => "Old",
                        'filter[status][0]' => "Canceled",
                    )
                );
            }

        } else {
            $submitter = $filterform['submitter']->getData();
            $status = $filterform['status']->getData();
            $principalInvestigators = $filterform['principalInvestigators']->getData();
            $salesperson = $filterform['salesperson']->getData();
            $idSearch = $filterform['idSearch']->getData();
            $totalMin = $filterform['totalMin']->getData();
            $totalMax = $filterform['totalMax']->getData();
            $startDate = $filterform['startDate']->getData();
            $endDate = $filterform['endDate']->getData();
            $version = $filterform['version']->getData();
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
        
        

        if( $submitter ) {
            $dql->andWhere("submitter.id = :submitterId");
            $dqlParameters["submitterId"] = $submitter->getId();
        }

        if( $status ) {
            $statusStr = "'".implode("','",$status)."'";
            $dql->andWhere("invoice.status IN (".$statusStr.")");
        }

        if( $idSearch ) {
            $dql->andWhere("invoice.oid LIKE :idSearch");
            $dqlParameters["idSearch"] = "%".$idSearch."%";
        }

        if( $principalInvestigators && count($principalInvestigators)>0 ) {
            $dql->andWhere("principalInvestigators.id IN (:principalInvestigators)");
            $principalInvestigatorsIdsArr = array();
            foreach($principalInvestigators as $principalInvestigator) {
                $principalInvestigatorsIdsArr[] = $principalInvestigator->getId();
            }
            $dqlParameters["principalInvestigators"] = implode(",",$principalInvestigatorsIdsArr);
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

        $limit = 30;
        $query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        //echo "query=".$query->getSql()."<br>";

        $paginationParams = array(
            'defaultSortFieldName' => 'invoice.id',
            'defaultSortDirection' => 'DESC'
        );

        $paginator  = $this->get('knp_paginator');
        $invoices = $paginator->paginate(
            $query,
            $request->query->get('page', 1),    /*page number*/
            $limit,                             /*limit per page*/
            $paginationParams
        );

        //$latestVersion = $transresRequestUtil->getLatestInvoiceVersion($transresRequest);

        return array(
            'invoices' => $invoices,
            'transresRequest' => $transresRequest,
            'title' => $title,
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
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        //$em = $this->getDoctrine()->getManager();
        //$transresUtil = $this->get('transres_util');
        $transresRequestUtil = $this->get('transres_request_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$user = null; //testing
        $cycle = "new";

        $invoice = $transresRequestUtil->createNewInvoice($transresRequest,$user);

        $form = $this->createInvoiceForm($invoice,$cycle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //exit('new');

            $msg = $transresRequestUtil->createSubmitNewInvoice($transresRequest,$invoice,$form);

            if( $form->getClickedButton() && 'saveAndSend' === $form->getClickedButton()->getName() ) {
                //TODO: generate and send PDF
            }

            //$msg = "New Invoice has been successfully created for the request ID ".$transresRequest->getOid();

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
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

        $cycle = "show";
        $routeName = $request->get('_route');

        $form = $this->createInvoiceForm($invoice,$cycle);

        //$deleteForm = $this->createDeleteForm($invoice);

        //Get $transresRequest (Assume invoice has a single $transresRequest)
        $transresRequest = null;
        $transresRequests = $invoice->getTransresRequests();
        if( count($transresRequests) > 0 ) {
            $transresRequest = $transresRequests[0];
        }

        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            'form' => $form->createView(),
            //'delete_form' => $deleteForm->createView(),
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

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->get('transres_util');
        //$transresRequestUtil = $this->get('transres_request_util');

        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

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

            $this->getDoctrine()->getManager()->flush();

            $msg = "Invoice with ID ".$invoice->getOid()." has been updated.";

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Invoice Updated";
            $msg = "Invoice with ID ".$invoice->getOid()." has been updated.";
            $transresUtil->setEventLog($invoice,$eventType,$msg);

            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
        }

        //Get $transresRequest (Assume invoice has a single $transresRequest)
        $transresRequest = null;
        $transresRequests = $invoice->getTransresRequests();
        if( count($transresRequests) > 0 ) {
            $transresRequest = $transresRequests[0];
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
        exit("Delete is not allowed.");

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

        return $this->redirectToRoute('translationalresearch_invoice_index_all');
    }

    /**
     * Generate Invoice PDF
     *
     * @Route("/generate-invoice-pdf/{oid}", name="translationalresearch_invoice_generate_pdf")
     * @Template("OlegTranslationalResearchBundle:Invoice:new.html.twig")
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

        $res = $transresPdfUtil->generateInvoicePdf($transresRequest,$invoice,$user);
        
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
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        //$em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$logger = $this->container->get('logger');
        //$routeName = $request->get('_route');
        //$userSecUtil = $this->container->get('user_security_utility');
        $transresRequestUtil = $this->get('transres_request_util');

        $em = $this->getDoctrine()->getManager();
        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

        if( false === $transresRequestUtil->isInvoiceBillingContact($invoice,$user) ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
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

    public function createInvoiceForm( $invoice, $cycle ) {

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'invoice' => $invoice,
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
}
