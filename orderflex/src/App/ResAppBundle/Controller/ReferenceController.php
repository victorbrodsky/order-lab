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

use App\ResAppBundle\Entity\ResidencyApplication;
//use App\ResAppBundle\Entity\GoogleFormConfig;
use App\ResAppBundle\Entity\Reference;
//use App\ResAppBundle\Form\GoogleFormConfigType;
//use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

class ReferenceController extends OrderAbstractController
{

    /**
     * NOT USED: There are no reference letters to submit. This functionality has been derived from fellowship system.
     *
     * @Route("/invite-references-submit-letters/{id}", name="resapp_invite_references_submit_letters", methods={"GET"})
     */
    public function InviteReferencesToSubmitLettersAction(Request $request, ResidencyApplication $resapp) {

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') === false )
        {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $resappRecLetterUtil = $this->container->get('resapp_rec_letter_util');
        //$userSecUtil = $this->container->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        //testing
        //$res = $resappRecLetterUtil->sendInvitationEmailsToReferences($resapp,true);
        //echo "res:<br>";
        //print_r($res);
        //exit();

//        $referenceNames = array();
//        foreach($resapp->getReferences() as $reference) {
//            if( count($reference->getDocuments()) == 0 ) {
//                //send invitation email
//                $res = $resappRecLetterUtil->inviteSingleReferenceToSubmitLetter($reference,$resapp);
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

        $resArr = $resappRecLetterUtil->sendInvitationEmailsToReferences($resapp,true);
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

        return $this->redirect($this->generateUrl('resapp_show',array('id' => $resapp->getId())));
    }


    /**
     * @Route("/invite-reference-submit-letter/{id}/{referenceid}", name="resapp_invite_reference_submit_letter", methods={"GET"})
     */
    public function InviteReferenceToSubmitLetterAction(Request $request, ResidencyApplication $resapp, $referenceid) {

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') === false )
        {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $resappRecLetterUtil = $this->container->get('resapp_rec_letter_util');
        //$userSecUtil = $this->container->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $reference = $this->getDoctrine()->getRepository('AppResAppBundle:Reference')->find($referenceid);
        if( !$reference ) {
            throw new \Exception("No reference found by ID ".$referenceid);
        }

        //send invitation
        $res = $resappRecLetterUtil->inviteSingleReferenceToSubmitLetter($reference,$resapp);

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

        return $this->redirect($this->generateUrl('resapp_show',array('id' => $resapp->getId())));
    }

    /**
     * @Route("/reference-letter-received/{id}", name="resapp_reference_letter_received", methods={"GET"})
     */
    public function ReferenceLetterReceivedAction( Request $request, Reference $reference ) {

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') === false )
        {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        if( false == $this->get('security.authorization_checker')->isGranted("update","ResidencyApplication") ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $reference->setRecLetterReceived(true);

        //exit("reference set checkbox");

        //$em->persist($reference);
        $em->flush();

        $msg = "Set recommendation status by ".$reference->getFullName()." to 'uploaded'";

        //Event Log
        $eventType = 'Residency Application Updated';
        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userSecUtil->createUserEditEvent($this->getParameter('resapp.sitename'),$msg,$user,$reference,$request,$eventType);

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        return $this->redirect( $this->generateUrl('resapp_home') );
    }
    

}
