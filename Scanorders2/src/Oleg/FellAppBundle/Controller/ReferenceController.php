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

namespace Oleg\FellAppBundle\Controller;

use Oleg\FellAppBundle\Entity\FellowshipApplication;
use Oleg\FellAppBundle\Entity\GoogleFormConfig;
use Oleg\FellAppBundle\Entity\Reference;
use Oleg\FellAppBundle\Form\GoogleFormConfigType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ReferenceController extends Controller
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

        //$userSecUtil = $this->container->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $referenceNames = array();
        foreach($fellapp->getReferences() as $reference) {
            if( count($reference->getDocuments()) == 0 ) {
                //send invitation
                $this->inviteSingleReferenceToSubmitLetter($reference);
                $referenceNames[] = $reference->getFullName();
            }
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'All remaining references '.implode(", ",$referenceNames).' have been invited to submit letters.'
        );

        return $this->redirect($this->generateUrl('fellapp_show',array('id' => $fellapp->getId())));
    }


    /**
     * @Route("/invite-reference-submit-letter/{id}/{referenceid}", name="fellapp_invite_reference_submit_letter")
     * @Method({"GET"})
     */
    public function InviteReferenceToSubmitLetterAction(Request $request, FellowshipApplication $fellapp, Reference $reference) {

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') === false )
        {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //$userSecUtil = $this->container->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        //send invitation
        $this->inviteSingleReferenceToSubmitLetter($reference);

        $referenceName = $reference->getFullName();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Reference '.$referenceName.' has been invited to submit letters.'
        );

        return $this->redirect($this->generateUrl('fellapp_show',array('id' => $fellapp->getId())));
    }

    public function inviteSingleReferenceToSubmitLetter($reference) {

        return true;
    }

}
