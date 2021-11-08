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

namespace App\UserdirectoryBundle\Security\Voter;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\UserdirectoryBundle\Entity\User;


//Role have permission objects (Permission);
//Permission object has one permission (PermissionList);
//Permission has one PermissionObjectList and one PermissionActionList

//Role Voter check for the general roles such as ROLE_FELLAPP_USER, ROLE_FELLAPP_BANNED etc.: isGranted("ROLE_FELLAPP_USER")

abstract class BaseRoleVoter extends Voter {

    protected $decisionManager;
    protected $em;
    protected $container;

    public function __construct(AccessDecisionManagerInterface $decisionManager, EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->decisionManager = $decisionManager;
        $this->em = $em;
        $this->container = $container;
    }


    //isGranted("ROLE_DEIDENTIFICATOR_USER") or isGranted("ROLE_DEIDENTIFICATOR_BANNED")
    //$attribute: ROLE_...
    //$subject: null
    protected function supports($attribute, $subject) {

        $siteRoleBase = $this->getSiteRoleBase();
        $sitename = $this->getSitename();

        //echo "Supported voter?: attribute=".$attribute."; subject=".$subject."<br>";

        //support USERDIRECTORY roles only
        if( strpos($attribute, 'ROLE_'.$siteRoleBase.'_') === false ) {
            return false;
        }

        return $this->siteSpecificRoleSupport($attribute, $subject, $sitename, $siteRoleBase);
    }

    //evaluate if this user has this role (attribute)
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {

        //return false; //testing

        $siteRoleBase = $this->getSiteRoleBase();
        $sitename = $this->getSitename();

        return $this->voteOnSiteSpecificAttribute($attribute, $subject, $token, $sitename, $siteRoleBase);
    }




    //Role Voter: attribute is ROLE_...
    //Vote if a user has a general site role
    protected function siteSpecificRoleSupport($attribute, $subject, $sitename, $siteRoleBase) {

        //echo "DeidentifierVoter: support <br>";
        //echo $sitename.': siteSpecificRoleSupport: attribute='.$attribute."<br>";
        //echo 'subject='.$subject."<br>";

        //does not support UNAPPROVED and BANNED roles for this voter
        if( strpos($attribute, '_UNAPPROVED') !== false || strpos($attribute, '_BANNED') !== false ) {
            //exit('do not support _UNAPPROVED or _BANNED roles');
            return false;
        }

        //all general ADMIN roles are checked by a default voter using role hierarchy in security.yml
        if( $attribute == 'ROLE_'.$siteRoleBase.'_ADMIN' ) {
            //exit('do not support ' . 'ROLE_'.$siteRoleBase.'_ADMIN');
            return false;
        }

        //exit('siteSpecificRoleSupport OK ');
        return true;
    }

    //evaluate if this user has this role (attribute)
    public function voteOnSiteSpecificAttribute($attribute, $subject, TokenInterface $token, $sitename, $siteRoleBase) {
        //exit('voteOnSiteSpecificAttribute');
        //return false; //testing
        //echo $sitename.': voteOn SiteSpecific Attribute: attribute='.$attribute.", siteRoleBase=".$siteRoleBase."<br>";
        //echo 'attribute='.$attribute."<br>";
        //echo 'subject='.$subject."<br>";
        //return true;

        $user = $token->getUser();

        if( !$user instanceof User ) {
            // the user must be logged in; if not, deny access
            //exit('user is not object');
            return false;
        }

        //if ROLE_PLATFORM_ADMIN => allow all
        if( $this->decisionManager->decide($token, array('ROLE_PLATFORM_ADMIN')) ) {
            //exit('admin!: '.'ROLE_PLATFORM_ADMIN');
            return true;
        }
        //if ROLE_PLATFORM_ADMIN => allow all
        if( $this->decisionManager->decide($token, array('ROLE_PLATFORM_DEPUTY_ADMIN')) ) {
            //exit('admin!: '.'ROLE_PLATFORM_DEPUTY_ADMIN');
            return true;
        }

        //ROLE_DEIDENTIFICATOR_ADMIN can do anything
        if( $this->decisionManager->decide($token, array('ROLE_'.$siteRoleBase.'_ADMIN')) ) {
            //exit('admin!: '.'ROLE_'.$siteRoleBase.'_ADMIN');
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
                    return false;
                }

                return true;
                //return VoterInterface::ACCESS_GRANTED;
            }

        }

        //Check if a $user has this role or partial role name ($attribute) with given $sitename
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


    public function hasGeneralSiteRole( $user, $sitename ) {

        foreach( $user->getRoles() as $roleStr ) {
            //echo 'roleStr='.$roleStr."<br>";

            if( $roleStr == "ROLE_TESTER" ) {
                //ignore Tester Role, because this role is generic for all sites
                continue;
            }

            $role = $this->em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($roleStr);
            if( $role ) {
                foreach( $role->getSites() as $site ) {
                    //echo 'role='.$role.", site=".$site->getName()."<br>";
                    if( $site->getName()."" == $sitename."" || $site->getAbbreviation()."" == $sitename ) {
                        //echo "access true <br>";
                        return true;
                    }
                }
            }
        }

        return false;
    }


}
