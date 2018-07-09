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

namespace Oleg\TranslationalResearchBundle\Controller;


use Doctrine\Common\Collections\ArrayCollection;
use Oleg\TranslationalResearchBundle\Entity\DataResult;
use Oleg\TranslationalResearchBundle\Entity\Product;
use Oleg\TranslationalResearchBundle\Entity\Project;
use Oleg\TranslationalResearchBundle\Entity\TransResRequest;
use Oleg\TranslationalResearchBundle\Form\FilterRequestType;
use Oleg\TranslationalResearchBundle\Form\TransResRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
class PackingSlipController extends Controller
{

    /**
     * Print Packing Slip
     *
     * @Route("/print-packing_slip/{id}", name="translationalresearch_print_packing_slip")
     * @Template("OlegTranslationalResearchBundle:Request:new.html.twig")
     * @Method("GET")
     */
    public function printPackingSlipAction(Request $request, TransResRequest $transresRequest)
    {
        $transresUtil = $this->container->get('transres_util');
        $transresPdfUtil = $this->get('transres_pdf_generator');
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$em = $this->getDoctrine()->getManager();
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
        return $this->redirect( $this->generateUrl('translationalresearch_packing_slip_download',array('id'=>$transresRequest->getId()) ));


        //$invoice = $transresRequestUtil->getLatestInvoice($transresRequest);
        //echo "invoice OID=".$invoice->getOid()."<br>";

        //Generate Packing Slip
        $res = $transresPdfUtil->generatePackingSlipPdf($transresRequest,$user);

        //Print Packing Slip
        

        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
        );
    }


    /**
     * Print Packing Slip
     *
     * @Route("/print-packing_slip/{id}", name="translationalresearch_email_packing_slip")
     * @Template("OlegTranslationalResearchBundle:Request:new.html.twig")
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

        //Print Packing Slip

        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
        );
    }


    /**
     * E-Mail Packing Slip to PIs and Submitter for Confirmation + Change Request Status to 'Pending Investigator'
     *
     * @Route("/print-packing_slip_change_status_pending_investigator/{id}", name="translationalresearch_email_packing_slip_change_status_pending_investigator")
     * @Template("OlegTranslationalResearchBundle:Request:new.html.twig")
     * @Method("GET")
     */
    public function emailPackingSlipChangeStatusPendingInvestigatorAction(Request $request, TransResRequest $transresRequest)
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

        //Print Packing Slip

        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
        );
    }



    /**
     * Show Packing Slip PDF version of Work Request
     * http://localhost/order/translational-research/work-request/download-packing-slip-invoice-pdf/HP8-REQ20-V2
     *
     * @Route("/download-packing-slip-pdf/{id}", name="translationalresearch_packing_slip_download")
     * @Template("OlegTranslationalResearchBundle:Request:packing-slip-pdf-show.html.twig")
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

        return array(
            'transresRequest' => $transresRequest,
            'invoice' => $invoice,
            'packingSlipLogoFileName' => $packingSlipLogoFileName,
            'opacity' => 0.6,
            //'form' => $form->createView(),
            //'delete_form' => $deleteForm->createView(),
            'cycle' => $cycle,
            'title' => "Packing Slip for Work Request ID ".$transresRequest->getOid(),
        );
    }
}