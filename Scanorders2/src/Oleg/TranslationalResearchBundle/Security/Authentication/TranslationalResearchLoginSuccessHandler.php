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

        $redirectResponse = parent::onAuthenticationSuccess($request,$token);

//        if( $this->secAuth->isGranted('ROLE_TRANSRES_USER') ) {
//            return $redirectResponse;
//        }

        $user = $token->getUser();
        $url = $redirectResponse->getTargetUrl();
        echo "url=".$url."<br>";
        //exit("exit");

        //$targetPath = $request->getSession()->get('_security.ldap_translationalresearch_firewall.target_path');
        //$targetPath = $redirectResponse->getTargetPath();
        //echo "targetPath=".$targetPath."<br>";

        //If new project creation page "order/translational-research/project/new/" and if user does have ROLE_TRANSRES_REQUESTER and specialty role
        // assign these minimum roles to submit a new project
        if( strpos($url,"order/translational-research/project/new/") !== false ) {

            if( false == $this->secAuth->isGranted('ROLE_TRANSRES_REQUESTER') ) {

//                $user->addRole('ROLE_TRANSRES_REQUESTER');
//                $roleMsgArr[] = "ROLE_TRANSRES_REQUESTER" . " role has been added";
//
//                //Event Log
//                $userSecUtil = $this->container->get('user_security_utility');
//                $specialtyStr = basename($url);
//                $eventType = "User record updated";
//                $eventMsg = "User information of " . $user . " has been automatically changed to be able to access a new project page:" . "<br>";
//                $eventMsg = $eventMsg . "New project for $specialtyStr specialty" . "<br>";
//                $eventMsg = $eventMsg . "ROLE_TRANSRES_REQUESTER" . " role has been added";
//                $userSecUtil->createUserEditEvent($this->siteName, $eventMsg, $user, $user, $request, $eventType);

                $specialtyStr = basename($url);
                $redirectPath = "translationalresearch_project_new";
                $confirmationUrl = $this->router->generate('translationalresearch_account-confirmation',
                    array(
                        'id' => $user->getId(),
                        'redirectPath' => $redirectPath,
                        'specialty' => $specialtyStr
                    )
                );
                echo "set redirect to $confirmationUrl <br>";
                $response = new RedirectResponse($confirmationUrl);
                //exit('exit '.$confirmationUrl);
                return $response;
            }

        }

        $redirectResponse = parent::onAuthenticationSuccess($request,$token);

        //exit("exit");
        return $redirectResponse;

//            $transresUtil = $this->container->get('transres_util');
//
//            $specialtyStr = basename($url);
//            echo "specialtyStr=".$specialtyStr."<br>";
//            $specialtyObject = $transresUtil->getSpecialtyObject($specialtyStr);
//            echo "specialtyObject=".$specialtyObject."<br>";
//
//            if( $specialtyObject ) {
//                $specialtyRole = $transresUtil->getSpecialtyRole($specialtyObject);
//                if( false == $this->secAuth->isGranted($specialtyRole) ) {
//                    $user->addRole($specialtyRole);
//                    $flushUser = true;
//                    $roleMsgArr[] = $specialtyRole . " role has been added";
//                }
//            }
//
//            //1) check if user does not have minimum roles to submit a new project => redirect to account confirmation page
//            if( false == $this->secAuth->isGranted('ROLE_TRANSRES_REQUESTER') || false == $this->secAuth->isGranted($specialtyRole) ) {
//                //$confirmationUrl = "/order/translational-research/account-confirmation/";
//                //$redirectResponse->setTargetUrl($confirmationUrl,array('id'=>$user->getId(),'redirectUrl'=>$url));
//                $redirectPath = "translationalresearch_project_new";
//                $confirmationUrl = $this->router->generate('translationalresearch_account-confirmation',
//                    array(
//                        'id' => $user->getId(),
//                        'redirectPath' => $redirectPath,
//                        'specialty' => $specialtyStr
//                    )
//                );
//                echo "set redirect to $confirmationUrl <br>";
//                $response = new RedirectResponse($confirmationUrl);
//                //exit('exit '.$confirmationUrl);
//                return $response;
//
//                //$redirectResponse->setTargetUrl($confirmationUrl);
//                //return $redirectResponse;
//            }
//
//            exit("exit");
//            //return $redirectResponse;
//
//            //////////// below not used /////////////////
//            $roleMsgArr = array();
//            $flushUser = false;
//
//            if( false == $this->secAuth->isGranted('ROLE_TRANSRES_REQUESTER') ) {
//                $user->addRole('ROLE_TRANSRES_REQUESTER');
//                $flushUser = true;
//                $roleMsgArr[] = "ROLE_TRANSRES_REQUESTER" . " role has been added";
//            }
//
//            $specialtyStr = basename($url);
//            echo "specialtyStr=".$specialtyStr."<br>";
//            $specialtyObject = $transresUtil->getSpecialtyObject($specialtyStr);
//            echo "specialtyObject=".$specialtyObject."<br>";
//
//            if( $specialtyObject ) {
//                $specialtyRole = $transresUtil->getSpecialtyRole($specialtyObject);
//                if( false == $this->secAuth->isGranted($specialtyRole) ) {
//                    $user->addRole($specialtyRole);
//                    $flushUser = true;
//                    $roleMsgArr[] = $specialtyRole . " role has been added";
//                }
//            }
//            if( $flushUser ) {
//                exit('flush user');
//                //$em->flush($user);
//
//                //Event Log
//                $userSecUtil = $this->container->get('user_security_utility');
//                $eventType = "User record updated";
//                //$msg = $msg . " by ".$project->getSubmitter()->getUsernameOptimal();
//                //$transresUtil->setEventLog($project,$eventType,$msg);
//
//                $eventMsg = "User information of ".$user." has been automatically changed on :"."<br>";
//                $eventMsg = $eventMsg . "<br>" . implode("<br>", $removedCollections);
//                $userSecUtil = $this->get('user_security_utility');
//                $userSecUtil->createUserEditEvent($this->siteName,$eventMsg,$user,$user,$request,$eventType);
//            }
//        }

        //exit("exit");
        //return $redirectResponse;

        //return parent::onAuthenticationSuccess($request,$token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        return parent::onAuthenticationFailure($request,$exception);
    }

}