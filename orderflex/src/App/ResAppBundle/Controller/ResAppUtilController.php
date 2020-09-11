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

}
