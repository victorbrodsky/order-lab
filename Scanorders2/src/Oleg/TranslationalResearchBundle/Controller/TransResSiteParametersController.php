<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\TranslationalResearchBundle\Entity\Invoice;
use Oleg\TranslationalResearchBundle\Entity\TransResRequest;
use Oleg\TranslationalResearchBundle\Form\FilterInvoiceType;
use Oleg\TranslationalResearchBundle\Form\InvoiceType;
use Oleg\UserdirectoryBundle\Entity\TransResSiteParameters;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Oleg\UserdirectoryBundle\Entity\User;

/**
 * SiteParameters controller.
 *
 * @Route("site-parameters")
 */
class TransResSiteParametersController extends Controller
{

    /**
     * Lists all SiteParameters entities.
     *
     * @Route("/list/", name="translationalresearch_siteparameters_index")
     * @Template("OlegTranslationalResearchBundle:SiteParameters:index.html.twig")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }



        return array(
            //
        );
    }

    /**
     * Creates a new invoice entity.
     *
     * @Route("/new/", name="translationalresearch_siteparameters_new")
     * @Template("OlegTranslationalResearchBundle:SiteParameters:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

//        //$em = $this->getDoctrine()->getManager();
//        //$transresUtil = $this->get('transres_util');
//        $transresRequestUtil = $this->get('transres_request_util');
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        //$user = null; //testing
//        $cycle = "new";
//
//        $invoice = $transresRequestUtil->createNewInvoice($transresRequest,$user);
//
//        $form = $this->createInvoiceForm($invoice,$cycle,$transresRequest);
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            //exit('new');
//
//            $msg = $transresRequestUtil->createSubmitNewInvoice($transresRequest,$invoice,$form);
//
//            if( $form->getClickedButton() && 'saveAndSend' === $form->getClickedButton()->getName() ) {
//                //TODO: generate and send PDF
//            }
//
//            //$msg = "New Invoice has been successfully created for the request ID ".$transresRequest->getOid();
//
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                $msg
//            );
//
//            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
//        }

//        return array(
//            'transresRequest' => $transresRequest,
//            'invoice' => $invoice,
//            'form' => $form->createView(),
//            'title' => "New Invoice for the Request ID ".$transresRequest->getOid(),
//            'cycle' => $cycle
//        );
    }

    /**
     * Finds and displays entity.
     *
     * @Route("/show/{id}", name="translationalresearch_siteparameters_show")
     * @Template("OlegTranslationalResearchBundle:SiteParameters:new.html.twig")
     * @Method("GET")
     */
    public function showAction(Request $request, TransResSiteParameters $siteParameters)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $cycle = "show";

//        $form = $this->createInvoiceForm($siteParameters,$cycle);
//
//        //$deleteForm = $this->createDeleteForm($invoice);
//
//        //Get $transresRequest (Assume invoice has a single $transresRequest)
//        $transresRequest = null;
//        $transresRequests = $invoice->getTransresRequests();
//        if( count($transresRequests) > 0 ) {
//            $transresRequest = $transresRequests[0];
//        }
//
//        return array(
//            'transresRequest' => $transresRequest,
//            'invoice' => $invoice,
//            'form' => $form->createView(),
//            //'delete_form' => $deleteForm->createView(),
//            'cycle' => $cycle,
//            'title' => "Invoice ID ".$invoice->getOid(),
//        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/edit/{id}", name="translationalresearch_siteparameters_edit")
     * @Template("OlegTranslationalResearchBundle:SiteParameters:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, TransResSiteParameters $siteParameters)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

//        $em = $this->getDoctrine()->getManager();
//        $transresUtil = $this->get('transres_util');
//        //$transresRequestUtil = $this->get('transres_request_util');
//
//        $invoice = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findOneByOid($oid);
//        if( !$invoice ) {
//            throw new \Exception("Invoice is not found by invoice number (oid) '" . $oid . "'");
//        }
//
//        if( $invoice->getLatestVersion() !== true ) {
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                "The old version of the invoice can not be edited."
//            );
//            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
//
//        }
//
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $cycle = "edit";
//
//        //$deleteForm = $this->createDeleteForm($invoice);
//
//        //$editForm = $this->createForm('Oleg\TranslationalResearchBundle\Form\InvoiceType', $invoice);
//        $editForm = $this->createInvoiceForm($invoice,$cycle);
//
//        $editForm->handleRequest($request);
//
//        if ($editForm->isSubmitted() && $editForm->isValid()) {
//
//            //update user
//            $invoice->setUpdateUser($user);
//
//            //update oid: don't update Invoice version on edit. Only the last version can be edited.
//
//            $this->getDoctrine()->getManager()->flush();
//
//            $msg = "Invoice with ID ".$invoice->getOid()." has been updated.";
//
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                $msg
//            );
//
//            $eventType = "Invoice Updated";
//            $msg = "Invoice with ID ".$invoice->getOid()." has been updated.";
//            $transresUtil->setEventLog($invoice,$eventType,$msg);
//
//            return $this->redirectToRoute('translationalresearch_invoice_show', array('oid' => $invoice->getOid()));
//        }
//
//        return array(
//            'transresRequest' => $transresRequest,
//            'invoice' => $invoice,
//            'form' => $editForm->createView(),
//            //'delete_form' => $deleteForm->createView(),
//            'cycle' => $cycle,
//            'title' => "Invoice ID ".$invoice->getOid(),
//        );
    }


    public function createSiteParameterForm( $siteParameter, $cycle ) {

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
//        $params = array(
//            'cycle' => $cycle,
//            'em' => $em,
//            'user' => $user,
//            'invoice' => $invoice,
//            'principalInvestigators' => $principalInvestigators,
//            //'piEm' => $piEm,
//            'SecurityAuthChecker' => $this->get('security.authorization_checker'),
//        );
//
//        if( $cycle == "new" ) {
//            $disabled = false;
//        }
//
//        if( $cycle == "show" ) {
//            $disabled = true;
//        }
//
//        if( $cycle == "edit" ) {
//            $disabled = false;
//        }
//
//        if( $cycle == "download" ) {
//            $disabled = true;
//        }
//
//        $form = $this->createForm(InvoiceType::class, $invoice, array(
//            'form_custom_value' => $params,
//            'disabled' => $disabled,
//        ));
//
//        return $form;
    }

}
