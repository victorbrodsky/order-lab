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

namespace App\CtpBundle\Controller;





use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Entity\Roles; //process.py script: replaced namespace by ::class: added use line for classname=Roles
use App\OrderformBundle\Entity\Message;
use App\UserdirectoryBundle\Entity\ObjectTypeText;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use App\UserdirectoryBundle\Entity\User;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends OrderAbstractController
{
    #[Route(path: '/about', name: 'ctp_about_page')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function aboutAction(Request $request)
    {
        return array('sitename' => $this->getParameter('ctp.sitename'));
    }

    #[Route(path: '/', name: 'ctp_home', methods: ['GET'])]
    #[Template('AppctpBundle/Default/index.html.twig')]
    public function indexAction( Request $request ) {

        if( false == $this->isGranted('ROLE_CTP_USER') ){
            return $this->redirect( $this->generateUrl('ctp-nopermission') );
        }

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();
        //echo "accessreq count=".count($accessreqs)."<br>";
        $accessreqsCount = 0;
        if( is_array($accessreqs) ) {
            $accessreqsCount = count($accessreqs);
        }

        //$form = $this->createGenerateForm();

        return array(
            'accessreqs' => $accessreqsCount,
            //'form' => $form->createView(),
        );
    }

    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->isGranted('ROLE_CTP_ADMIN') ) {
            return null;
        }
        $userSecUtil = $this->container->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->getParameter('ctp.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
    }

//    #[Route(path: '/resources/', name: 'ctp_resources')]
//    #[Template('AppCtpBundle/Ctp/resources.html.twig')]
//    public function resourcesAction(Request $request)
//    {
//        if( false === $this->isGranted('ROLE_CTP_USER') ) {
//            return $this->redirect( $this->generateUrl('ctp-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository(CtpSiteParameter::class)->findAll();
//
//        if( count($entities) != 1 ) {
//            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
//        }
//
//        $entity = $entities[0];
//
//        $resourcesText = $entity->getCtpResource();
//
//        return array(
//            //'entity' => $entity,
//            //'form' => $form->createView(),
//            //'cycle' => $cycle,
//            'title' => "Resources",
//            'resourcesText' => $resourcesText
//        );
//    }
}
