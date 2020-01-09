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

namespace App\UserdirectoryBundle\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class EmailController extends Controller
{

    /**
     * @Route("/send-queued-emails/", name="employees_send_spooled_emails")
     * @Method({"GET"})
     */
    public function sendSpooledEmailsAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $emailUtil = $this->container->get('user_mailer_utility');

        //test
        //$res = $emailUtil->createEmailCronJob();
        //exit('create Windows cron: '.$res);

        $emailRes = $emailUtil->sendSpooledEmails();

        if( $emailRes ) {
            $msg = 'Spooled emails have been sent. Result: '.$emailRes;
        } else {
            //Please verify your Mailer setting.
            $msg = 'Spooled emails have not been sent. Result: '.$emailRes;
        }

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        return $this->redirectToRoute('employees_home');
    }
    
    /**
     * @Route("/send-a-test-email/", name="employees_emailtest")
     * @Template("AppUserdirectoryBundle/Email/email-test.html.twig")
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

        if( isset($_POST['email']) ) {
            $emails = $_POST['email'];
        }
        if( isset($_POST['emailcc']) ) {
            $ccs = $_POST['emailcc'];
        }

        if( isset($emails) ) {
            //$toEmail = "cinava@yahoo.com,cinava10@gmail.com";
            //$ccs = "oleg_iv@yahoo.com";//,cinava10@gmail.com,oli2002@med.cornell.edu";

            //exit("emails=".$emails."; cc=".$ccs);

            //{{ app.request.schemeAndHttpHost }}
            $schemeAndHttpHost = $request->getSchemeAndHttpHost();

            //ORDER Platform Test Message 01/01/18 12:34:57
            $today = new \DateTime();
            $msg = "ORDER Platform Test Message from " . $schemeAndHttpHost . " on " . $today->format('m/d/Y H:i:s');

            $emailRes = $emailUtil->sendEmail($emails, $msg, $msg, $ccs);
            //exit("email res=".$emailRes);

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Test email sent to: '.$emails.' and ccs to:'.$ccs. "<br> Status: ".$emailRes
            );

            return $this->redirectToRoute('employees_home');
        }

        //exit("email res=".$emailRes);

        return array();
    }




}
