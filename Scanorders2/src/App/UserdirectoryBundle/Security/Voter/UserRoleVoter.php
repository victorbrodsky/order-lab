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
 * User: ch3
 * Date: 12/23/15
 * Time: 11:28 AM
 */

namespace Oleg\UserdirectoryBundle\Security\Voter;


//use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;


class UserRoleVoter extends BaseRoleVoter {

    protected function getSiteRoleBase() {
        return 'USERDIRECTORY';
    }

    protected function getSitename() {
        return 'employees';     //Site abbreviation (i.e. fellapp), not site name (i.e. fellowship-applications)
    }

//    //isGranted("ROLE_DEIDENTIFICATOR_USER") or isGranted("ROLE_DEIDENTIFICATOR_BANNED")
//    //$attribute: ROLE_...
//    //$subject: null
//    protected function supports($attribute, $subject) {
//
//        //support USERDIRECTORY roles only
//        if( strpos($attribute, 'ROLE_'.self::SiteRoleBase.'_') === false ) {
//            return false;
//        }
//
//        return $this->siteSpecificRoleSupport($attribute, $subject, self::Sitename, self::SiteRoleBase);
//    }
//
//
//    //evaluate if this user has this role (attribute)
//    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
//    {
//
//        return $this->voteOnSiteSpecificAttribute($attribute, $subject, $token, self::Sitename, self::SiteRoleBase);
//    }

} 