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

namespace App\TranslationalResearchBundle\Security\Authentication;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\UserdirectoryBundle\Security\Authentication\LoginSuccessHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;


class TranslationalResearchLoginSuccessHandler extends LoginSuccessHandler {


    public function __construct( ContainerInterface $container, EntityManagerInterface $em )
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
        //$em = $this->em;
        //$user = $token->getUser();

        //get original target path
        $indexLastRoute = '_security.'.$this->firewallName.'.target_path';
        $targetUrl = $request->getSession()->get($indexLastRoute);

        $redirectResponse = parent::onAuthenticationSuccess($request,$token);

        $url = $redirectResponse->getTargetUrl();
        //echo "targetUrl=".$targetUrl."; url=".$url."<br>";

        //IF: new project creation page "order/translational-research/project/new" and if user does have ROLE_TRANSRES_REQUESTER and specialty role
        //THEN: assign minimum role for new project page "ROLE_TRANSRES_REQUESTER"
        //THEN: redirect to confirmation page
        //THEN: from confirmation page redirect to new project page
        if( $targetUrl == $url && strpos($url,"order/translational-research/project/new") !== false ) {

            //get specialty
            $transresUtil = $this->container->get('transres_util');
            $specialtyObject = null;
            $specialtyStr = basename($url);
            //echo "specialtyStr=".$specialtyStr."<br>";
            if( $specialtyStr == "hematopathology" || $specialtyStr == "ap-cp" ) {
                $specialtyObject = $transresUtil->getSpecialtyObject($specialtyStr);
                $redirectPath = "translationalresearch_project_new";
            } else {
                $specialtyStr = null;
                $redirectPath = "translationalresearch_project_new_selector";
            }

            //check if user does not have ROLE_TRANSRES_REQUESTER and specialty role
            $transresUtil = $this->container->get('transres_util');
            $roleAddedArr = $transresUtil->addMinimumRolesToCreateProject($specialtyObject);

            //if( false == $this->secAuth->isGranted('ROLE_TRANSRES_REQUESTER') ) {
            //if roles has been added then redirect to account confirmation page
            if( count($roleAddedArr) > 0 ) {

                ///////////////// Redirect to account confirmation page /////////////////
                $confirmationUrl = $this->router->generate('translationalresearch_account_confirmation',
                    array(
                        'redirectPath' => $redirectPath,
                        'specialty' => $specialtyStr
                    )
                );
                //echo "set redirect to $confirmationUrl <br>";
                //exit('exit '.$confirmationUrl);

                $response = new RedirectResponse($confirmationUrl);
                return $response;
                ///////////////// EOF Redirect to account confirmation page /////////////////
            }

        }

        //$redirectResponse = parent::onAuthenticationSuccess($request,$token);

        return $redirectResponse;
    }

    //overwrite parent basic user check for minimum role
    public function checkBasicRole($user,$targetUrl=null) {
        if( strpos($targetUrl,"order/translational-research/project/new") !== false ) {
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