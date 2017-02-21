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

namespace Oleg\CallLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CallLogEditController extends Controller
{

    /**
     * @Route("/delete/{messageId}", name="calllog_delete")
     * @Template("OlegUserdirectoryBundle:Default:about.html.twig")
     * @Method("GET")
     */
    public function deleteMessageAction(Request $request, $messageId)
    {
        if( false == $this->get('security.context')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $user = $this->get('security.context')->getToken()->getUser();
        //$securityUtil = $this->get('order_security_utility');
        $userSecUtil = $this->get('user_security_utility');
        //$orderUtil = $this->get('scanorder_utility');
        //$calllogUtil = $this->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

        $message = $em->getRepository('OlegOrderformBundle:Message')->find($messageId);
        if( !$message ) {
            throw new \Exception( "Message is not found by id ".$messageId );
        }

        if( $message->getMessageStatus() != "Deleted" ) {
            $message->setMessageStatusPrior($message->getMessageStatus());
        }

        $messageStatus = $em->getRepository('OlegOrderformBundle:MessageStatusList')->findOneByName("Deleted");
        if( !$messageStatus ) {
            throw new \Exception( "Message Status is not found by name '"."Deleted"."'" );
        }

        $message->setMessageStatus($messageStatus);

        $em->flush($message);

        //"Entry 123 for PatientFirstName PatientLastName (DOB: MM/DD/YYYY) submitted on
        // [submitted timestamp in MM/DD/YYYY HH:MM 24HR format] by SubmitterFirstName SubmitterLastName, MD successfully deleted
        $patientInfoStr = $message->getPatientNameMrnInfo();
        if( $patientInfoStr ) {
            $patientInfoStr = "for ".$patientInfoStr;
        }
        $msg = "Entry $messageId $patientInfoStr submitted on ".$message->getSubmitterInfo()." successfully deleted";
//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            $msg
//        );
        $this->get('session')->getFlashBag()->add(
            'pnotify',
            $msg
        );

        $eventType = "Call Log Book Entry Deleted";
        $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $msg, $user, $message, $request, $eventType);

        return $this->redirect($this->generateUrl('calllog_home'));
    }


    /**
     * @Route("/un-delete/{messageId}", name="calllog_undelete")
     * @Template("OlegUserdirectoryBundle:Default:about.html.twig")
     * @Method("GET")
     */
    public function unDeleteMessageAction(Request $request, $messageId)
    {
        if( false == $this->get('security.context')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $userSecUtil = $this->get('user_security_utility');

        $message = $em->getRepository('OlegOrderformBundle:Message')->find($messageId);
        if( !$message ) {
            throw new \Exception( "Message is not found by id ".$messageId );
        }

        $messageStatusPrior = $message->getMessageStatusPrior();

        if( !$messageStatusPrior ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Prior entry status is undefined, therefore, no modification has been performed.'
            );
            return $this->redirect($this->generateUrl('calllog_home'));
        }

        $message->setMessageStatus($messageStatusPrior);

        $em->flush($message);

        //Entry 123 for PatientFirstName PatientLastName (DOB: MM/DD/YYYY) submitted on
        // [submitted timestamp in MM/DD/YYYY HH:MM 24HR format] by SubmitterFirstName SubmitterLastName, MD successfully
        // un-deleted and status set to [name of status]
        $patientInfoStr = $message->getPatientNameMrnInfo();
        if( $patientInfoStr ) {
            $patientInfoStr = "for ".$patientInfoStr;
        }
        $msg = "Entry $messageId $patientInfoStr submitted on ".$message->getSubmitterInfo()." successfully un-deleted and status set to ".$messageStatusPrior;
//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            $msg
//        );
        $this->get('session')->getFlashBag()->add(
            'pnotify',
            $msg
        );

        $eventType = "Call Log Book Entry Undeleted";
        $userSecUtil->createUserEditEvent($this->container->getParameter('calllog.sitename'), $msg, $user, $message, $request, $eventType);

        return $this->redirect($this->generateUrl('calllog_home'));
    }

}
