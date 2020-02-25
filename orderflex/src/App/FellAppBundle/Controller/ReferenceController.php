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
use App\FellAppBundle\Entity\GoogleFormConfig;
use App\FellAppBundle\Entity\Reference;
use App\FellAppBundle\Form\GoogleFormConfigType;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ReferenceController extends OrderAbstractController
{

    /**
     * @Route("/invite-references-submit-letters/{id}", name="fellapp_invite_references_submit_letters")
     * @Method({"GET"})
     */
    public function InviteReferencesToSubmitLettersAction(Request $request, FellowshipApplication $fellapp) {

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') === false )
        {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        //$userSecUtil = $this->container->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        //testing
        //$res = $fellappRecLetterUtil->sendInvitationEmailsToReferences($fellapp,true);
        //echo "res:<br>";
        //print_r($res);
        //exit();

//        $referenceNames = array();
//        foreach($fellapp->getReferences() as $reference) {
//            if( count($reference->getDocuments()) == 0 ) {
//                //send invitation email
//                $res = $fellappRecLetterUtil->inviteSingleReferenceToSubmitLetter($reference,$fellapp);
//                if( $res['res'] == true ) {
//                    $this->get('session')->getFlashBag()->add(
//                        'notice',
//                        $res['msg']
//                    );
//                } else {
//                    $this->get('session')->getFlashBag()->add(
//                        'warning',
//                        $res['msg']
//                    );
//                }
//            }
//        }

        $resArr = $fellappRecLetterUtil->sendInvitationEmailsToReferences($fellapp,true);
        foreach($resArr as $res) {
            if( $res['res'] == true ) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $res['msg']
                );
            } else {
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    $res['msg']
                );
            }
        }

//        if( count($referenceNames) > 0 ) {
//            $msg = 'All remaining references '.implode(", ",$referenceNames).' have been invited to submit letters.';
//        } else {
//            $msg = "No invitations have been sent.";
//        }
//
//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            $msg
//        );

        return $this->redirect($this->generateUrl('fellapp_show',array('id' => $fellapp->getId())));
    }


    /**
     * @Route("/invite-reference-submit-letter/{id}/{referenceid}", name="fellapp_invite_reference_submit_letter")
     * @Method({"GET"})
     */
    public function InviteReferenceToSubmitLetterAction(Request $request, FellowshipApplication $fellapp, $referenceid) {

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') === false )
        {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        //$userSecUtil = $this->container->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $reference = $this->getDoctrine()->getRepository('AppFellAppBundle:Reference')->find($referenceid);
        if( !$reference ) {
            throw new \Exception("No reference found by ID ".$referenceid);
        }

        //send invitation
        $res = $fellappRecLetterUtil->inviteSingleReferenceToSubmitLetter($reference,$fellapp);

        if( $res['res'] == true ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                $res['msg']
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'warning',
                $res['msg']
            );
        }

        return $this->redirect($this->generateUrl('fellapp_show',array('id' => $fellapp->getId())));
    }

    /**
     * @Route("/reference-letter-received/{id}", name="fellapp_reference_letter_received")
     * @Method("GET")
     */
    public function ReferenceLetterReceivedAction( Request $request, Reference $reference ) {

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') === false )
        {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        if( false == $this->get('security.authorization_checker')->isGranted("update","FellowshipApplication") ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $reference->setRecLetterReceived(true);

        //exit("reference set checkbox");

        //$em->persist($reference);
        $em->flush();

        $msg = "Set recommendation status by ".$reference->getFullName()." to 'uploaded'";

        //Event Log
        $eventType = 'Fellowship Application Updated';
        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$msg,$user,$reference,$request,$eventType);

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }
    

}
