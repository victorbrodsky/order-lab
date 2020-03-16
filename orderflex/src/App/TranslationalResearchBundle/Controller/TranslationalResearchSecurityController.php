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

namespace App\TranslationalResearchBundle\Controller;


use App\UserdirectoryBundle\Controller\SecurityController;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\Security\Core\SecurityContext;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

use App\UserdirectoryBundle\Util\UserUtil;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class TranslationalResearchSecurityController extends SecurityController
{

    /**
     * @Route("/login", name="translationalresearch_login")
     * @Template()
     */
    public function loginAction( Request $request, AuthenticationUtils $authenticationUtils ) {
        //exit('translationalresearch: loginAction');
        return parent::loginAction($request,$authenticationUtils);
    }


    /**
     * @Route("/setloginvisit/", name="translationalresearch_setloginvisit", methods={"GET"})
     */
    public function setAjaxLoginVisit( Request $request )
    {
        //exit('translationalresearch: setAjaxLoginVisit');
        return parent::setAjaxLoginVisit($request);
    }


    /**
     * @Route("/no-permission", name="translationalresearch-nopermission", methods={"GET"})
     * @Template("AppUserdirectoryBundle/Security/nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        $empty = $request->get('empty');
        $additionalMessage = $request->get('additionalMessage');

        return array(
            'sitename' => $this->getParameter('translationalresearch.sitename'),
            'empty' => $empty,
            'additionalMessage' => $additionalMessage
        );
    }


    /**
     * @Route("/idle-log-out", name="translationalresearch_idlelogout")
     * @Route("/idle-log-out/{flag}", name="translationalresearch_idlelogout-saveorder")
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        //exit('translationalresearch: idlelogoutAction');
        return parent::idlelogoutAction($request,$flag);
    }

    public function getMessageToUsers() {
        $transresUtil = $this->container->get('transres_util');
        return $transresUtil->getTrpMessageToUsers();

//        //$sitename = $this->getParameter('translationalresearch.sitename');
//        //$showMessageToUsers = $userSecUtil->getSiteSettingParameter('showMessageToUsers',$sitename);
//        $showMessageToUsers = $transresUtil->getTransresSiteProjectParameter('notifyEmail');
//        if( $showMessageToUsers ) {
//            $messageToUsers = $transresUtil->getTransresSiteProjectParameter('messageToUsers');
//        } else {
//            $messageToUsers = null;
//        }
//
//        return $messageToUsers;
    }

}

?>
