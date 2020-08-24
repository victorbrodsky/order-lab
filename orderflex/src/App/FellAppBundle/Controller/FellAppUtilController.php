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

namespace App\FellAppBundle\Controller;

use App\FellAppBundle\Entity\FellowshipApplication;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;


class FellAppUtilController extends OrderAbstractController
{

    /**
     * @Route("/get-notification-email-infos/", name="fellapp_get_notification_email_infos", methods={"GET"}, options={"expose"=true})
     */
    public function GetNotificationEmailInfosAction(Request $request) {

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') === false )
        {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappId = trim($request->get('id'));
        $emailType = trim($request->get('emailType')); //accepted, rejected
        
        $fellapp = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->find($fellappId);
        if( !$fellapp ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$fellappId);
        }

        if( false == $this->get('security.authorization_checker')->isGranted("update",$fellapp) ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $userSecUtil = $this->get('user_security_utility');

        $warning = $fellappUtil->getRejectionAcceptanceEmailWarning($fellapp);

        $emailSubject = $userSecUtil->getSiteSettingParameter($emailType.'EmailSubject',$this->getParameter('fellapp.sitename'));
        $emailBody = $userSecUtil->getSiteSettingParameter($emailType.'EmailBody',$this->getParameter('fellapp.sitename'));

        //$rejectedEmailSubject = $userSecUtil->getSiteSettingParameter('rejectedEmailSubject',$this->getParameter('fellapp.sitename'));
        //$rejectedEmailBody = $userSecUtil->getSiteSettingParameter('rejectedEmailBody',$this->getParameter('fellapp.sitename'));

        $subject = $fellappUtil->siteSettingsConstantReplace($emailSubject,$fellapp);
        $body = $fellappUtil->siteSettingsConstantReplace($emailBody,$fellapp);

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
