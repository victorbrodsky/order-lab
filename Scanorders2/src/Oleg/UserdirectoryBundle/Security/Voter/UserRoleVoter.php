<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/23/15
 * Time: 11:28 AM
 */

namespace Oleg\UserdirectoryBundle\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;


class UserRoleVoter extends BaseRoleVoter {

    protected function getSiteRoleBase() {
        return 'USERDIRECTORY';
    }

    protected function getSitename() {
        return 'directory';     //Site abbreviation i.e. fellapp, not fellowship-applications
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