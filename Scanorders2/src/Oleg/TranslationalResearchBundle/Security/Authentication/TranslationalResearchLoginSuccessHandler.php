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

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/7/13
 * Time: 11:24 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\TranslationalResearchBundle\Security\Authentication;

use Oleg\UserdirectoryBundle\Security\Authentication\LoginSuccessHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;


class TranslationalResearchLoginSuccessHandler extends LoginSuccessHandler {


    public function __construct( $container, $em )
    {
        parent::__construct($container,$em);

        $this->siteName = $container->getParameter('translationalresearch.sitename');
        $this->siteNameStr = 'Translational Research System';
        $this->roleBanned = 'ROLE_TRANSRES_BANNED';
        $this->roleUser = 'ROLE_TRANSRES_USER';
        $this->roleUnapproved = 'ROLE_TRANSRES_UNAPPROVED';
        $this->firewallName = 'ldap_translationalresearch_firewall';
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {

        //exit("onAuthenticationSuccess");
        $em = $this->em;
        $user = $token->getUser();

        //get original target path
        $indexLastRoute = '_security.'.$this->firewallName.'.target_path';
        $targetUrl = $request->getSession()->get($indexLastRoute);

        $redirectResponse = parent::onAuthenticationSuccess($request,$token);

        $url = $redirectResponse->getTargetUrl();
        //echo "targetUrl=".$targetUrl."; url=".$url."<br>";

        //IF: new project creation page "order/translational-research/project/new/" and if user does have ROLE_TRANSRES_REQUESTER and specialty role
        //THEN: assign minimum role for new project page "ROLE_TRANSRES_REQUESTER"
        //THEN: redirect to confirmation page
        //THEN: from confirmation page redirect to new project page
        if( $targetUrl == $url && strpos($url,"order/translational-research/project/new/") !== false ) {

            if( false == $this->secAuth->isGranted('ROLE_TRANSRES_REQUESTER') ) {

                ///////////////// update user roles ////////////////////
                $transresUtil = $this->container->get('transres_util');
                $specialtyStr = basename($url);
                $specialtyObject = $transresUtil->getSpecialtyObject($specialtyStr);

                if( $specialtyObject ) {
                    $specialtyRole = $transresUtil->getSpecialtyRole($specialtyObject);
                    if( false == $this->secAuth->isGranted($specialtyRole) ) {
                        $user->addRole($specialtyRole);
                        $roleMsgArr[] = $specialtyRole . " role has been added";
                    }
                }

                $user->addRole('ROLE_TRANSRES_REQUESTER');
                $roleMsgArr[] = "ROLE_TRANSRES_REQUESTER" . " role has been added";

                $em->flush($user);
                ///////////////// EOF update user roles ////////////////////

                ///////////////// Event Log /////////////////
                $userSecUtil = $this->container->get('user_security_utility');
                $eventType = "User record updated";
                $eventMsg = "User information of " . $user . " has been automatically changed to be able to access a new $specialtyStr project page:" . "<br>";
                $eventMsg = $eventMsg . implode("<br>",$roleMsgArr);
                $userSecUtil->createUserEditEvent($this->siteName, $eventMsg, $user, $user, $request, $eventType);
                ///////////////// EOF Event Log /////////////////

                ///////////////// Redirect to account confirmation page /////////////////
                $redirectPath = "translationalresearch_project_new";
                $confirmationUrl = $this->router->generate('translationalresearch_account_confirmation',
                    array(
                        //'id' => $user->getId(),
                        'redirectPath' => $redirectPath,
                        'specialty' => $specialtyStr
                    )
                );
                //echo "set redirect to $confirmationUrl <br>";
                //exit('exit '.$confirmationUrl);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    "Permission to create a new $specialtyObject project has been automatically granted by the system. Your activities will be recorded."
                );

                $response = new RedirectResponse($confirmationUrl);
                return $response;
                ///////////////// EOF Redirect to account confirmation page /////////////////
            }

        }

        $redirectResponse = parent::onAuthenticationSuccess($request,$token);

        return $redirectResponse;
    }

    //overwrite parent basic user check for minimum role
    public function checkBasicRole($user,$targetUrl=null) {
        if( strpos($targetUrl,"order/translational-research/project/new/") !== false ) {
            //allow new users to continue to the new project page
            return;
        }
        //parent (general) check
        parent::checkBasicRole($user,$targetUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        return parent::onAuthenticationFailure($request,$exception);
    }

}