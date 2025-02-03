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



use App\UserdirectoryBundle\Controller\OrderAbstractController;

use App\UserdirectoryBundle\Entity\User;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;


class EmailController extends OrderAbstractController
{

    #[Route(path: '/send-queued-emails/', name: 'employees_send_spooled_emails', methods: ['GET'])]
    public function sendSpooledEmailsAction(Request $request)
    {

        if (!$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
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
        $this->addFlash(
            'notice',
            $msg
        );

        return $this->redirectToRoute('employees_home');
    }
    
    #[Route(path: '/send-a-test-email/', name: 'employees_emailtest', methods: ['GET', 'POST'])]
    #[Template('AppUserdirectoryBundle/Email/email-test.html.twig')]
    public function emailTestAction(Request $request, MailerInterface $mailer)
    {

        //Test default symfony email
//        $email = (new Email())
//            ->from('cinava@yahoo.com')
//            ->to('cinava@yahoo.com')
//            //->cc('cc@example.com')
//            //->bcc('bcc@example.com')
//            //->replyTo('fabien@example.com')
//            //->priority(Email::PRIORITY_HIGH)
//            ->subject('Time for Symfony Mailer!')
//            ->text('Sending emails is fun again!')
//            ->html('<p>See Twig integration for better HTML integration!</p>');
//        $mailer->send($email);
//        Check the spam folder

        if (!$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $emailUtil = $this->container->get('user_mailer_utility');
        //$userSecUtil = $this->container->get('user_security_utility');
        $newline =  "<br>\n";
        //$newline =  "\n";

        //$user = $this->getUser();
        //$toEmail = $user->getSingleEmail();

        $emails = null;
        if( isset($_POST['email']) ) {
            $emails = $_POST['email'];
        }

        $ccs = null;
        if( isset($_POST['emailcc']) ) {
            $ccs = $_POST['emailcc'];
        }

        $usersStr = null;
        if( isset($_POST['emailcc']) ) {
            $usersStr = $_POST['users'];
        }

        if( $emails || $ccs || $usersStr ) {
            //$toEmail = "cinava@yahoo.com,cinava10@gmail.com";
            //$ccs = "oleg_iv@yahoo.com";//,cinava10@gmail.com,oli2002@med.cornell.edu";

            //exit("emails=".$emails."; cc=".$ccs);

            //{{ app.request.schemeAndHttpHost }}
            //$schemeAndHttpHost = $request->getSchemeAndHttpHost();
            //replace $request->getSchemeAndHttpHost() with getRealSchemeAndHttpHost($request)
            $userUtil = $this->container->get('user_utility');
            $schemeAndHttpHost = $userUtil->getRealSchemeAndHttpHost($request);

            //ORDER Platform Test Message 01/01/18 12:34:57
            $today = new \DateTime();
            $msg = "ORDER Platform Test Message from " . $schemeAndHttpHost . " on " . $today->format('m/d/Y H:i:s');

            if( $usersStr ) {
                $usersEmails = array();
                $usersArr = explode(",", $usersStr);
                foreach($usersArr as $userCwid) {
                    $userCwid = trim($userCwid);
                    $userCwid = strtolower($userCwid);
                    $em = $this->getDoctrine()->getManager();
                    $repository = $em->getRepository(User::class);
                    $dql = $repository->createQueryBuilder("user");
                    $dql->where("user.username LIKE :username");
                    $query = $dql->getQuery(); //$query = $em->createQuery($dql);
                    $query->setParameter("username",$userCwid."%");
                    $users = $query->getResult();
                    echo "Generated users count=".count($users)."<br>";
                    $user = null;
                    if( count($users) > 0 ) {
                        $user = $users[0];
                        echo "user=$user <br>";
                        $usersEmails[] = $user->getSingleEmail();;
                    }
                }
                //dump($usersEmails);
                //exit('111');
                $usersEmails = array_unique($usersEmails);
                $thisMsg =
                    "### Please ignore this testing email ###" . $newline . $newline .
                    $msg . $newline .
                    " Receiver users: ".$usersStr. $newline .
                    " emails: " . implode(', ',$usersEmails) . $newline .
                    " css: ".$ccs;
                $emailRes1 = $emailUtil->sendEmail($usersEmails, $msg, $thisMsg, $ccs);
                $this->addFlash(
                    'notice',
                    $thisMsg
                    //'Test email sent to users: '.$usersStr.'; emails: '.implode(', ',$usersEmails).'; ccs:'.$ccs. "<br> Status: ".$emailRes1
                );

                return $this->redirectToRoute('employees_home');
            }

            $thisMsg =
                "### Please ignore this testing email ###" . $newline . $newline.
                $msg . $newline .
                " Receiver emails: " . $emails . $newline .
                ' css: '.$ccs;;
            $emailRes = $emailUtil->sendEmail($emails, $thisMsg, $msg, $ccs);
            //exit("email res=".$emailRes);

            $confirmation = 'Test email sent to: '.$emails.'; ccs to:'.$ccs. "<br> Status: ".$emailRes;
            $confirmation = $confirmation .
                "<br><br>If you do not receive the confirmation message within".
                " a few minutes of signing up, please check your spam or junk mail ".
                "folder just in case the confirmation email got delivered there ".
                "instead of your inbox. If so, select the confirmation message ".
                "and click Not Junk/Spam, which will allow future messages to get through.";

            //Flash
            $this->addFlash(
                'notice',
                $confirmation
            );

            return $this->redirectToRoute('employees_home');
        }

        //exit("email res=".$emailRes);

        return array();
    }

    /**
     * https://collage.med.cornell.edu/order/directory/list-notify-users/
     */
    #[Route(path: '/list-notify-users/', name: 'employees_list_notify_users')]
    #[Template('AppUserdirectoryBundle/Email/list-notify-users.html.twig')]
    public function someTestingAction()
    {
        if (false === $this->isGranted('ROLE_USERDIRECTORY_OBSERVER')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        //get users with notifiyUsers
        $repository = $em->getRepository(User::class);
        $dql = $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin('user.notifyUsers', 'notifyUsers');
        $dql->where("notifyUsers IS NOT NULL");
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
        $users = $query->getResult();

//        echo "users count=" . count($users) . "<br>";
//        foreach ($users as $user) {
//            echo $user . " => " . $user->getSingleEmail() . "<br>";
//        }

        return array(
            'users' => $users
        );
    }


}
