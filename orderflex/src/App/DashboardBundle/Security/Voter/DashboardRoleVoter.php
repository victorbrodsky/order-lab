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
 * Created by PhpStorm.
 * User: oli2002
 * Date: 10/8/2021
 * Time: 12:12 PM
 */

namespace App\DashboardBundle\Security\Voter;


use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Security\Voter\BaseRoleVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

//Role Voter check for the general roles such as ROLE_DASHBOARD_USER, ROLE_DASHBOARD_BANNED etc.: isGranted("ROLE_DASHBOARD_USER")
class DashboardRoleVoter extends BaseRoleVoter {

    protected function getSiteRoleBase() {
        return 'DASHBOARD';
    }

    protected function getSitename() {
        return 'dashboard';  //Site abbreviation i.e. resapp, not residency-applications
    }



    //evaluate if this user has this role (attribute)
    public function voteOnSiteSpecificAttribute($attribute, $subject, TokenInterface $token, $sitename, $siteRoleBase) {
        //exit('dashboard voteOnSiteSpecificAttribute: attribute='.$attribute);
        //return false; //testing
        //echo $sitename.': voteOn SiteSpecific Attribute: attribute='.$attribute.", siteRoleBase=".$siteRoleBase."<br>";
        //echo 'dashboard attribute='.$attribute."<br>";
        //echo 'subject='.$subject."<br>";
        //return true;

        $user = $token->getUser();

        if( !$user instanceof User ) {
            // the user must be logged in; if not, deny access
            //exit('user is not object');
            return false;
        }

//        //Exception for TRANSRES: check ROLE_TRANSRES_ADMIN will be done by findUserRolesBySiteAndPartialRoleName below. Otherwise decisionManager will be going to endless loop
//        //ROLE_DEIDENTIFICATOR_ADMIN can do anything
//        if( $this->decisionManager->decide($token, array('ROLE_'.$siteRoleBase.'_ADMIN')) ) {
//            //exit('admin!: '.'ROLE_'.$siteRoleBase.'_ADMIN');
//            return true;
//        }

        //if ROLE_PLATFORM_ADMIN => allow all
        if( $this->decisionManager->decide($token, array('ROLE_PLATFORM_ADMIN')) ) {
            //exit('admin!: '.'ROLE_PLATFORM_ADMIN');
            return true;
        }
        //if ROLE_PLATFORM_ADMIN => allow all
        if( $this->decisionManager->decide($token, array('ROLE_PLATFORM_DEPUTY_ADMIN')) ) {
            //exit('admin!: '.'ROLE_PLATFORM_ADMIN');
            return true;
        }

        //check if user has this role including hiearchy roles
        if( $user->hasRole($attribute) ) {
            //if user has this role => access is granted
            //exit('hasRole');
            return true;
        }

        //check for general dummy _USER role ROLE_DEIDENTIFICATOR_USER
        if( $attribute == 'ROLE_'.$siteRoleBase.'_USER' ) {
            //exit('check general user role='.$attribute);
            if( $this->hasGeneralSiteRole($user,$sitename) ) {
                //echo 'hasGeneralSiteRole yes <br>';
                //exit('hasGeneralSiteRole');

                //check if user has ROLE_DEIDENTIFICATOR_BANNED or ROLE_DEIDENTIFICATOR_UNAPPROVED
                if( $user->hasRole("ROLE_".$siteRoleBase."_BANNED") || $user->hasRole("ROLE_".$siteRoleBase."_UNAPPROVED") ) {
                    //echo 'dashboard banned <br>';
                    return false;
                }

                //echo 'dashboard ok <br>';
                return true;
                //return VoterInterface::ACCESS_GRANTED;
            }

        }

        //Check if a $user has this role or partial role name ($attribute) with given $sitename
        //ROLE_TRANSRES_REQUESTER_USCAP has partial role ROLE_TRANSRES_REQUESTER
        //ROLE_TRANSRES_ADMIN_USCAP  has partial role ROLE_TRANSRES_ADMIN
        $roleObjects = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserRolesBySiteAndPartialRoleName($user,$sitename,$attribute);
        //echo $attribute.": roleObjects count=".count($roleObjects)."<br>";
        if( count($roleObjects) > 0 ) {
            //exit($attribute.': Dummy partial rolename-site ok');
            return true;
        }

        //exit($attribute.': no access');
        return false;

        //throw new \LogicException('This code should not be reached!');
    }

}


