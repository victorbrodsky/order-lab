<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 9/26/2017
 * Time: 4:49 PM
 */

namespace App\TranslationalResearchBundle\Controller;


use Doctrine\Common\Collections\ArrayCollection;
use App\TranslationalResearchBundle\Entity\DataResult;
use App\TranslationalResearchBundle\Entity\Product;
use App\TranslationalResearchBundle\Entity\Project;
use App\TranslationalResearchBundle\Entity\TransResRequest;
use App\TranslationalResearchBundle\Form\FilterRequestType;
use App\TranslationalResearchBundle\Form\TransResRequestType;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Work Request Packing Slip controller.
 *
 * @Route("work-request")
 */
class PackingSlipController extends AbstractController
{

    /**
     * Generate Packing Slip
     *
     * @Route("/generate-packing-slip/{id}", name="translationalresearch_generate_packing_slip")
     * @Template("AppTranslationalResearchBundle/Request/new.html.twig")
     * @Method("GET")
     */
    public function generatePackingSlipAction(Request $request, TransResRequest $transresRequest)
    {
        $transresUtil = $this->container->get('transres_util');
        $transresPdfUtil = $this->get('transres_pdf_generator');
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $project = $transresRequest->getProject();

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }


        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //testing
        //return $this->redirect( $this->generateUrl('translationalresearch_packing_slip_download',array('id'=>$transresRequest->getId()) ));

        //$invoice = $transresRequestUtil->getLatestInvoice($transresRequest);
        //echo "invoice OID=".$invoice->getOid()."<br>";


        $packingSlips = new ArrayCollection();
        foreach($transresRequest->getPackingSlipPdfs() as $packingSlip) {
            $packingSlips->add($packingSlip);
        }
        //echo "0 packingSlips count=".count($packingSlips)."<br>";

        //Generate Packing Slip
        $res = $transresPdfUtil->generatePackingSlipPdf($transresRequest,$user,$request);

        $filename = $res['filename'];
        //$pdf = $res['pdf'];
        $size = $res['size'];

        if( $size > 0 ) {
            //move $oldPackingSlips to oldPackingSlipPdfs
            $resave = false;
            foreach ($packingSlips as $packingSlip) {
                $transresRequest->removePackingSlipPdf($packingSlip);
                $transresRequest->addOldPackingSlipPdf($packingSlip);
                $resave = true;
            }
            if( $resave ) {
                $em->flush();
            }
        }

        $msg = "Packing Slip PDF has been generated for Work Request " . $transresRequest->getOid() . "; filename=".$filename."; size=".$size;

        //exit("<br><br>".$msg);

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        //view generated packing slip
        $packingSlips = $transresRequest->getPackingSlipPdfs();
        if( count($packingSlips) > 0 ) {
            $latestPackingSlip = $packingSlips->first();
            return $this->redirectToRoute('translationalresearch_file_view', array('id' => $latestPackingSlip->getId()));
        } else {
            return $this->redirectToRoute('translationalresearch_request_show_with_packingslip', array('id' => $transresRequest->getId()));
        }

        return $this->redirectToRoute('translationalresearch_request_show_with_packingslip', array('id' => $transresRequest->getId()));
    }


    /**
     * E-Mail Packing Slip to PIs and Submitter
     *
     * @Route("/email-packing-slip/{id}", name="translationalresearch_email_packing_slip")
     * @Method("GET")
     */
    public function emailPackingSlipAction(Request $request, TransResRequest $transresRequest)
    {
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $project = $transresRequest->getProject();

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }


        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //get latest packing slip PDF
        $pdf = $transresRequestUtil->getLatestPackingSlipPdf($transresRequest);
        //echo "pdf=".$pdf."<br>";
        //exit('1');

        //E-Mail Packing Slip to PIs and Submitter
        $subject = "Please review the attached deliverables for work request ".$transresRequest->getOid();
        // The Translational Research group is working on your request (REQ-ID)
        // and is planning to deliver the items listed in the attached document.
        // Please review the items and comments (if any), and if you have any concerns,
        // contact the Translational Research group by emailing [FirstName LastName] (email@address).
        // (mailto: link) list all users with Translational Research Administrator roles
        $body = "The Translational Research group is working on your request ".$transresRequest->getOid().
            " and is planning to deliver the items or services listed in the attached document.";

        $emailNoteConcern = $transresUtil->getTransresSiteProjectParameter('emailNoteConcern',$project);
        //echo "emailNoteConcern=[$emailNoteConcern]<br>";
        if( !$emailNoteConcern ) {
            $emailNoteConcern = "Please review the deliverables and comments (if any), and if you have any concerns,".
            " contact the Translational Research group by emailing [[EMAILS]]";
        }
        //echo "emailNoteConcern=[$emailNoteConcern]<br>";

        $body = $body . "<br><br>" . $emailNoteConcern;

        $res = $transresRequestUtil->sendPackingSlipPdfByEmail($transresRequest,$pdf,$subject,$body);

        $this->get('session')->getFlashBag()->add(
            'notice',
            $res
        );

        return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
    }


    /**
     * E-Mail Packing Slip to PIs and Submitter for Confirmation + Change Request Status to 'Pending Investigator'
     *
     * @Route("/email-packing-slip-and-change-status-to-pending-investigator/{id}", name="translationalresearch_email_packing_slip_change_status_pending_investigator")
     * @Method("GET")
     */
    public function emailPackingSlipChangeStatusPendingInvestigatorAction(Request $request, TransResRequest $transresRequest)
    {
        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $project = $transresRequest->getProject();

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }


        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //Print Packing Slip

        //get latest packing slip PDF
        $pdf = $transresRequestUtil->getLatestPackingSlipPdf($transresRequest);
        //echo "pdf=".$pdf."<br>";
        //exit('1');

        //E-Mail Packing Slip to PIs and Submitter for Confirmation + Change Request Status to 'Pending Investigator'
        $subject = "Please review the attached deliverables for work request ".$transresRequest->getOid()." and reply to confirm";
        // The Translational Research group is working on your work request (REQ-ID) for project “project title”,
        // and is planning to deliver the items listed in the attached document.
        // In order to enable the delivery, please review the items and comments (if any),
        // and confirm that you agree with this plan by emailing emailing [FirstName LastName] (email@address).
        // (mailto: link) list all users with Translational Research Administrator roles
        $body = "The Translational Research group is working on your request ".$transresRequest->getOid().
            " for project ".$project->getTitle().
            " and is planning to deliver the items or services listed in the attached document.".
            " In order to enable the delivery, please review the items and comments (if any),".
            " and confirm that you agree with this plan by emailing emailing [[EMAILS]]";
        $res = $transresRequestUtil->sendPackingSlipPdfByEmail($transresRequest,$pdf,$subject,$body);

        //Change Request Status to 'Pending Investigator' (ProgressState: pendingInvestigatorInput)
        $transresRequest->setProgressState("pendingInvestigatorInput");
        $em->flush($transresRequest);

        //Event Log
        $changeStatusStr = "Completion Progress Status of Work Request ID ".
            $transresRequest->getOid()." has been changed to " .
            $transresRequestUtil->getProgressStateLabelByName($transresRequest->getProgressState()).".";
        $eventType = "Request State Changed";
        $transresUtil->setEventLog($transresRequest,$eventType,$changeStatusStr);

        $res = $res . "<br>" . $changeStatusStr;

        $this->get('session')->getFlashBag()->add(
            'notice',
            $res
        );

        return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
    }



    /**
     * Show packing slip PDF version of Work Request
     * http://localhost/order/translational-research/work-request/download-packing-slip-pdf/3
     *
     * @Route("/download-packing-slip-pdf/{id}", name="translationalresearch_packing_slip_download")
     * @Template("AppTranslationalResearchBundle/Request/packing-slip-pdf-show.html.twig")
     * @Method("GET")
     */
    public function showPackingSlipAsPdfAction(Request $request, TransResRequest $transresRequest)
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

        $cycle = "download";

        $invoice = $transresRequestUtil->getLatestInvoice($transresRequest);
        //echo "invoice OID=".$invoice->getOid()."<br>";

        $packingSlipLogoFileName = $transresRequestUtil->getDefaultFile("transresPackingSlipLogos",null,$transresRequest);
        //echo "packingSlipLogoFileName=$packingSlipLogoFileName <br>";

        $barcodeImageSize = $transresRequestUtil->getTransresSiteParameter('barcodeSize',$transresRequest);
        if( !$barcodeImageSize ) {
            $barcodeImageSize = "54";
        }

        //body size
        $packingSlipFontSize = $transresRequestUtil->getTransresSiteParameter('transresPackingSlipFontSize',$transresRequest);
        if( !$packingSlipFontSize ) {
            $packingSlipFontSize = "14";
        }

        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            'packingSlipLogoFileName' => $packingSlipLogoFileName,
            'opacity' => 1, //0.6
            //'form' => $form->createView(),
            //'delete_form' => $deleteForm->createView(),
            'cycle' => $cycle,
            'title' => "Packing Slip for Work Request ID ".$transresRequest->getOid(),
            'barcodeImageSize' => $barcodeImageSize,
            'barcodeTdSize' => ($barcodeImageSize*2+10)."px;",
            'packingSlipFontSize' => $packingSlipFontSize
        );
    }
}