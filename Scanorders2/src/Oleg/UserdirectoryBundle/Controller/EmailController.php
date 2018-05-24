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

namespace Oleg\UserdirectoryBundle\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class EmailController extends Controller
{

    /**
     * @Route("/send-spooled-emails/", name="employees_send_spooled_emails")
     * @Method({"GET"})
     */
    public function sendSpooledEmailsAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $emailUtil = $this->container->get('user_mailer_utility');

        $emailRes = $emailUtil->sendSpooledEmails();

        if( $emailRes ) {
            $msg = 'Spooled emails have been sent.';
        } else {
            $msg = 'Spooled emails have not been sent. Please verify your Mailer setting.';
        }

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        return $this->redirectToRoute('employees_home');
    }
    
    /**
     * @Route("/emailtest/", name="employees_emailtest")
     * @Method({"GET","POST"})
     */
    public function emailTestAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $emailUtil = $this->container->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        //$user = $this->get('security.token_storage')->getToken()->getUser();
        //$toEmail = $user->getSingleEmail();

        $toEmail = "cinava@yahoo.com,cinava10@gmail.com";
        $ccs = "oleg_iv@yahoo.com";//,cinava10@gmail.com,oli2002@med.cornell.edu";

        //ORDER Platform Test Message 01/01/18 12:34:57
        $today = new \DateTime();
        $msg = "ORDER Platform Test Message " . $today->format('m/d/Y H:i:s');
        
        $emailRes = $emailUtil->sendEmail($toEmail, $msg, $msg, $ccs);

        exit("email res=".$emailRes);

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Test email sent to: '.$toEmail.' and ccs to:'.$ccs
        );

        return $this->redirectToRoute('employees_home');
    }




}
