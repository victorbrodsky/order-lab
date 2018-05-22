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


class DefaultController extends Controller
{

    /**
     * @Route("/thanks-for-downloading/{id}/{sitename}", name="common_thankfordownloading")
     * @Template("OlegUserdirectoryBundle:Default:thanksfordownloading.html.twig")
     * @Method("GET")
     */
    public function thankfordownloadingAction(Request $request, $id, $sitename) {
        return array(
            'fileid' => $id,
            'sitename' => $sitename
        );
    }

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

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Spooled emails are send.'
        );

        return $this->redirectToRoute('employees_home');
    }
    
    /**
     * @Route("/emailtest/", name="employees_emailtest")
     * @Method({"GET"})
     */
    public function emailTestAction(Request $request)
    {

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $emailUtil = $this->container->get('user_mailer_utility');

        //$user = $this->get('security.token_storage')->getToken()->getUser();
        //$toEmail = $user->getSingleEmail();

        $toEmail = "cinava@yahoo.com,cinava10@gmail.com";
        $ccs = "oleg_iv@yahoo.com";//,cinava10@gmail.com,oli2002@med.cornell.edu";

        $emailRes = $emailUtil->sendEmail($toEmail, "Test Email Subject", "Test Email Message", $ccs);

        exit("email res=".$emailRes);

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Test email sent to: '.$toEmail.' and ccs to:'.$ccs
        );

        return $this->redirectToRoute('employees_home');
    }


//    /**
//     * @Route("/", name="employees_home")
//     * @Template("OlegUserdirectoryBundle:Default:home.html.twig")
//     */
//    public function indexAction()
//    {
//
//        if(
//            false == $this->get('security.authorization_checker')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
//            false == $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
//        ){
//            return $this->redirect( $this->generateUrl('login') );
//        }
//
//        //$form = $this->createForm(new SearchType(),null);
//
//        //$form->submit($request);  //use bind instead of handleRequest. handleRequest does not get filter data
//        //$search = $form->get('search')->getData();
//
//        //check for active access requests
//        $accessreqs = $this->getActiveAccessReq();
//
//
//        return array(
//            'accessreqs' => count($accessreqs)
//            //'form' => $form->createView(),
//        );
//    }
//
//    //check for active access requests
//    public function getActiveAccessReq() {
//        if( !$this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_ADMIN') ) {
//            return null;
//        }
//        $userSecUtil = $this->get('user_security_utility');
//        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->container->getParameter('employees.sitename'),AccessRequest::STATUS_ACTIVE);
//        return $accessreqs;
//    }


//    /**
//     * @Route("/admin", name="employees_admin")
//     * @Template("OlegUserdirectoryBundle:Default:index.html.twig")
//     */
//    public function adminAction()
//    {
//        $name = "This is an Employee Directory Admin Page!!!";
//        return array('name' => $name);
//    }
//
//
//    /**
//     * @Route("/hello/{name}", name="employees_hello")
//     * @Template()
//     */
//    public function helloAction($name)
//    {
//        return array('name' => $name);
//    }



}
