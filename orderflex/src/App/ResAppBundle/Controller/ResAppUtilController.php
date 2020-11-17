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

namespace App\ResAppBundle\Controller;

//use App\ResAppBundle\Entity\ResidencyApplication;
//use App\ResAppBundle\Form\ResAppUploadType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;


class ResAppUtilController extends OrderAbstractController
{

    /**
     * @Route("/get-notification-email-infos/", name="resapp_get_notification_email_infos", methods={"GET"}, options={"expose"=true})
     */
    public function GetNotificationEmailInfosAction(Request $request) {

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') === false )
        {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $resappId = trim($request->get('id'));
        $emailType = trim($request->get('emailType')); //accepted, rejected
        
        $resapp = $this->getDoctrine()->getRepository('AppResAppBundle:ResidencyApplication')->find($resappId);
        if( !$resapp ) {
            throw $this->createNotFoundException('Unable to find Residency Application by id='.$resappId);
        }

        if( false == $this->get('security.authorization_checker')->isGranted("update",$resapp) ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $resappUtil = $this->container->get('resapp_util');
        $userSecUtil = $this->get('user_security_utility');

        $warning = $resappUtil->getRejectionAcceptanceEmailWarning($resapp);

        $emailSubject = $userSecUtil->getSiteSettingParameter($emailType.'EmailSubject',$this->getParameter('resapp.sitename'));
        $emailBody = $userSecUtil->getSiteSettingParameter($emailType.'EmailBody',$this->getParameter('resapp.sitename'));

        //$rejectedEmailSubject = $userSecUtil->getSiteSettingParameter('rejectedEmailSubject',$this->getParameter('resapp.sitename'));
        //$rejectedEmailBody = $userSecUtil->getSiteSettingParameter('rejectedEmailBody',$this->getParameter('resapp.sitename'));

        $subject = $resappUtil->siteSettingsConstantReplace($emailSubject,$resapp);
        $body = $resappUtil->siteSettingsConstantReplace($emailBody,$resapp);

        if( $subject && $body ) {
            $res = array(
                'warning' => $warning,
                'subject' => $subject,
                'body' => $body
            );
        } else {
            $res = "NOTOK";
        }

        $response = new Response();
        $response->setContent(json_encode($res));
        return $response;
    }

    /**
     * @Route("/ethnicities", name="resapp_get_ethnicities", methods={"GET","POST"}, options={"expose"=true})
     */
    public function getEthnicitiesAction(Request $request) {

        $resappUtil = $this->container->get('resapp_util');
        $ethnicities = $resappUtil->getDefaultEthnicitiesArray();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($ethnicities));
        return $response;
    }

    /**
     * @Route("/resapps-current-year", name="resapp_get_resapps_current_year", methods={"GET","POST"}, options={"expose"=true})
     */
    public function getResApplicationsForThisYearAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $resappPdfUtil = $this->container->get('resapp_pdfutil');

//        $archiveStatus = $em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("archive");
//        if (!$archiveStatus) {
//            throw new EntityNotFoundException('Unable to find entity by name=' . "archive");
//        }
//        $hideStatus = $em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("hide");
//        if (!$archiveStatus) {
//            throw new EntityNotFoundException('Unable to find entity by name=' . "hide");
//        }
//        $declinedStatus = $em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("declined");
//        if (!$declinedStatus) {
//            throw new EntityNotFoundException('Unable to find entity by name=' . "declined");
//        }
//        $rejectedStatus = $em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("reject");
//        if (!$rejectedStatus) {
//            throw new EntityNotFoundException('Unable to find entity by name=' . "reject");
//        }
//        $rejectedandnotifiedStatus = $em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("rejectedandnotified");
//        if (!$rejectedandnotifiedStatus) {
//            throw new EntityNotFoundException('Unable to find entity by name=' . "rejectedandnotified");
//        }
//        $exceptStatusArr = array($archiveStatus,$hideStatus,$declinedStatus,$rejectedStatus,$rejectedandnotifiedStatus);
        $resapps = $resappPdfUtil->getEnabledResapps();

        $resappsInfoArr = array();
        foreach($resapps as $resapp) {
            //Add to John Smith’s application (ID 1234)
            //$applicantName = $resapp->getApplicantFullName();
            //$resappsInfoArr[] = "Add to ".$applicantName."'s application (ID ".$resapp->getId().")";
            $resappsInfoArr[] = $resapp->getAddToStr();
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($resappsInfoArr));
        return $response;
    }
}
