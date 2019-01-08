<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
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
 * Reminder email controller.
 *
 * @Route("reminder")
 */
class ReminderController extends Controller
{
    
    /**
     * @Route("/unpaid-invoice-reminder/show-summary", name="translationalresearch_unpaid_invoice_reminder_show")
     * @Route("/unpaid-invoice-reminder/send-emails", name="translationalresearch_unpaid_invoice_reminder_send")
     * @Method({"GET"})
     */
    public function unpaidInvoiceReminderAction( Request $request )
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresReminderUtil = $this->get('transres_reminder_util');

        $routeName = $request->get('_route');
        $showSummary = false;

        if( $routeName == "translationalresearch_unpaid_invoice_reminder_show" ) {
            $showSummary = true;
        }

        $results = $transresReminderUtil->sendReminderUnpaidInvoices($showSummary);

        if( $showSummary === true ) {
            $invoiceCounter = 0;

            foreach($results as $result) {
                $invoiceCounter = $invoiceCounter + count($result);
            }

            return $this->render("OlegTranslationalResearchBundle:Reminder:unpaid-invoice-index.html.twig",
                array(
                    'title' => $invoiceCounter." Unpaid Invoices",
                    'invoiceGroups' => $results,
                    'invoiceCounter' => $invoiceCounter
                )
            );
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            "Sending reminder emails for unpaid invoices: ".$results
        );

        return $this->redirectToRoute('translationalresearch_invoice_index_filter');
    }


    /**
     * http://127.0.0.1/order/translational-research/reminder/project-reminder/show-summary
     *
     * @Route("/project-reminder/show-summary", name="translationalresearch_project_reminder_show")
     * @Route("/project-reminder/send-emails", name="translationalresearch_project_reminder_send")
     * @Method({"GET"})
     */
    public function projectReminderAction( Request $request )
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresReminderUtil = $this->get('transres_reminder_util');

        $routeName = $request->get('_route');
        $showSummary = false;

        if( $routeName == "translationalresearch_project_reminder_show" ) {
            $showSummary = true;
        }

        $results = $transresReminderUtil->sendReminderReviewProjects($showSummary);

        if( $showSummary === true ) {
            $projectCounter = 0;

            foreach($results as $result) {
                $projectCounter = $projectCounter + count($result);
            }

            return $this->render("OlegTranslationalResearchBundle:Reminder:project-reminder-index.html.twig",
                array(
                    'title' => $projectCounter." Projects",
                    'projectGroups' => $results,
                    'projectCounter' => $projectCounter
                )
            );
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            "Sending reminder emails for unpaid invoices: ".$results
        );

        return $this->redirectToRoute('translationalresearch_invoice_index_filter');
    }
    
}
