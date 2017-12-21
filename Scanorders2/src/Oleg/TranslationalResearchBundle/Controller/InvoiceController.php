<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\TranslationalResearchBundle\Entity\Invoice;
use Oleg\TranslationalResearchBundle\Entity\TransResRequest;
use Oleg\TranslationalResearchBundle\Form\InvoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Invoice controller.
 *
 * @Route("invoice")
 */
class InvoiceController extends Controller
{
    /**
     * Lists all invoice entities.
     *
     * @Route("/list/{id}", name="translationalresearch_invoice_index")
     * @Route("/list-all/", name="translationalresearch_invoice_index_all")
     * @Template("OlegTranslationalResearchBundle:Invoice:index.html.twig")
     * @Method("GET")
     */
    public function indexAction(Request $request, TransResRequest $transresRequest=null)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $routeName = $request->get('_route');


        $repository = $em->getRepository('OlegTranslationalResearchBundle:Invoice');
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');

        $dql->leftJoin('invoice.submitter','submitter');
        $dql->leftJoin('invoice.salesperson','salesperson');
        $dql->leftJoin('invoice.transresRequests','transresRequests');

        $dqlParameters = array();

        if( $routeName == "translationalresearch_invoice_index_all" ) {
            //$invoices = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findAll();
            $title = "List of Invoices";
        }

        if( $routeName == "translationalresearch_invoice_index" ) {
            $title = "List of Invoices for Request ID ".$transresRequest->getOid();
            $dql->where("transresRequests.id = :transresRequestId");
            $dqlParameters["transresRequestId"] = $transresRequest->getId();
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

        return array(
            'invoices' => $invoices,
            'transresRequest' => $transresRequest,
            'title' => $title
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

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->get('transres_util');
        $transresRequestUtil = $this->get('transres_request_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$user = null; //testing
        $cycle = "new";

        if(0) {
            $userDownloadUtil = $this->get('user_download_utility');
            $invoice = new Invoice($user);

            $invoice->generateOid($transresRequest);

            $transresRequest->addInvoice($invoice);

            $newline = "\n";

            //pre-populate salesperson
            $transresRequestContact = $transresRequest->getContact();
            if ($transresRequestContact) {
                $invoice->setSalesperson($transresRequestContact);
            }

            ////////////// from //////////////
            $from = "Weill Cornell Medicine" . $newline . "Department of Pathology and" . $newline . "Laboratory Medicine";
            $from = $from . $newline . "1300 York Avenue, C302/Box 69 New York, NY 10065";

            if ($invoice->getSalesperson()) {
                $sellerStr = "";

                $phone = $invoice->getSalesperson()->getSinglePhoneAndPager();
                if (isset($phone['phone'])) {
                    $from = $from . $newline . "Tel: " . $phone['phone'];
                    $sellerStr = $sellerStr . " Tel: " . $phone['phone'];
                }

                $fax = $invoice->getSalesperson()->getAllFaxes();
                if ($fax) {
                    $from = $from . $newline . "Fax: " . $fax;
                    $sellerStr = $sellerStr . " Fax: " . $fax;
                }

                $email = $invoice->getSalesperson()->getSingleEmail();
                if ($email) {
                    $from = $from . $newline . "Email: " . $email;
                    $sellerStr = $sellerStr . " Email: " . $email;
                }
            }

            $invoice->setInvoiceFrom($from);
            ////////////// EOF from //////////////

            //footer:
            $footer = "Make check payable & mail to: Weill Cornell Medicine, 1300 York Ave, C302/Box69, New York, NY 10065 (Attn: Jeffrey Hernandez)";
            $invoice->setFooter($footer);

            //footer2:
            $invoice->setFooter2($sellerStr);

//        //footer3:
//        $footer3 = "------------------ Detach and return with payment ------------------";
//        $invoice->setFooter3($footer3);

            //pre-populate dueDate +30 days
            $dueDateStr = date('Y-m-d', strtotime("+30 days"));
            $dueDate = new \DateTime($dueDateStr);
            $invoice->setDueDate($dueDate);

            //pre-populate PIs
            $transreqPis = $transresRequest->getPrincipalInvestigators();
            foreach ($transreqPis as $transreqPi) {
                $invoice->addPrincipalInvestigator($transreqPi);
            }

            //invoiceTo (text): the first PI
            $billToUser = null;
            $pis = $invoice->getPrincipalInvestigators();
            if (count($pis) > 0) {
                $billToUser = $pis[0];
            }
            if ($billToUser) {
                $userlabel = $userDownloadUtil->getLabelSingleUser($billToUser, $newline, true);
                if ($userlabel) {
                    $invoice->setInvoiceTo($userlabel);
                }
            }

            //populate invoice items corresponding to the multiple requests
            $invoiceItems = $transresRequestUtil->getRequestItems($transresRequest);
            foreach ($invoiceItems as $invoiceItem) {
                $invoice->addInvoiceItem($invoiceItem);
            }
        }//if(0)
        $invoice = $transresRequestUtil->createNewInvoice($transresRequest,$user);

        $form = $this->createInvoiceForm($invoice,$cycle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //exit('new');

            if(0) {
                $em->persist($invoice);
                $em->flush();

                $invoice->generateOid($transresRequest);
                $em->flush($invoice);

                $msg = "New Invoice has been successfully created for the request ID " . $transresRequest->getOid();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $msg
                );

                $eventType = "Invoice Created";
                $msg = "New Invoice with ID " . $invoice->getOid() . " has been successfully submitted for the request ID " . $transresRequest->getOid();
                $transresUtil->setEventLog($invoice, $eventType, $msg);
            }
            $invoice = $transresRequestUtil->createSubmitNewInvoice($transresRequest,$invoice);

            $msg = "New Invoice has been successfully created for the request ID ".$transresRequest->getOid();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            return $this->redirectToRoute('translationalresearch_invoice_show', array('id'=>$transresRequest->getId(), 'oid' => $invoice->getOid()));
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
     * @Route("/show/{id}/{oid}", name="translationalresearch_invoice_show")
     * @Template("OlegTranslationalResearchBundle:Invoice:new.html.twig")
     * @Method("GET")
     */
    public function showAction(Request $request, TransResRequest $transresRequest, $oid)
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

        $deleteForm = $this->createDeleteForm($invoice);

        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'cycle' => $cycle,
            'title' => "Invoice for the Request ID ".$transresRequest->getOid(),
        );
    }

    /**
     * Displays a form to edit an existing invoice entity.
     *
     * @Route("/edit/{id}/{oid}", name="translationalresearch_invoice_edit")
     * @Template("OlegTranslationalResearchBundle:Invoice:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, TransResRequest $transresRequest, $oid)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->get('transres_util');

        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
        if( !$invoice ) {
            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $cycle = "edit";

        $deleteForm = $this->createDeleteForm($invoice);

        //$editForm = $this->createForm('Oleg\TranslationalResearchBundle\Form\InvoiceType', $invoice);
        $editForm = $this->createInvoiceForm($invoice,$cycle);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            //update user
            $invoice->setUpdateUser($user);

            $this->getDoctrine()->getManager()->flush();

            $msg = "Invoice with ID ".$invoice->getOid()." has been updated.";

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Invoice Updated";
            $msg = "Invoice with ID ".$invoice->getOid()." has been updated.";
            $transresUtil->setEventLog($invoice,$eventType,$msg);

            return $this->redirectToRoute('translationalresearch_invoice_show', array('id'=>$transresRequest->getId(), 'oid' => $invoice->getOid()));
        }

        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'cycle' => $cycle,
            'title' => "Invoice for the Request ID ".$transresRequest->getOid(),
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
     * Finds and displays a invoice entity.
     *
     * @Route("/generate-invoice-pdf/{id}", name="translationalresearch_invoice_generate_pdf")
     * @Template("OlegTranslationalResearchBundle:Invoice:new.html.twig")
     * @Method("GET")
     */
    public function generateInvoicePdfAction(Request $request, Invoice $invoice) {
        $transresPdfUtil = $this->get('transres_pdf_generator');

        $res = $transresPdfUtil->generateInvoicePdf($invoice);
        
        $filename = $res['filename'];
        $pdf = $res['pdf'];
        $size = $res['size'];

        $msg = "PDF has been created for Invoice ID " . $invoice->getOid() . "; filename=".$filename."; size=".$size;

        exit("<br><br>".$msg);

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        return $this->redirectToRoute('translationalresearch_invoice_index_all');
    }

    /**
     * Show PDF version of invoice
     *
     * @Route("/download-invoice-pdf/{id}/{oid}", name="translationalresearch_invoice_download")
     * @Template("OlegTranslationalResearchBundle:Invoice:pdf-show.html.twig")
     * @Method("GET")
     */
    public function downloadPdfAction(Request $request, TransResRequest $transresRequest, $oid)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $logger = $this->container->get('logger');
        $routeName = $request->get('_route');
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
        $routeName = $request->get('_route');

        //$form = $this->createInvoiceForm($invoice,$cycle);

        $deleteForm = $this->createDeleteForm($invoice);

        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            //'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'cycle' => $cycle,
            'title' => "Invoice for the Request ID ".$transresRequest->getOid(),
        );
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
