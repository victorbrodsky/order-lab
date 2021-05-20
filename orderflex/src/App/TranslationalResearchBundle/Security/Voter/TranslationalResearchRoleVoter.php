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
 * Date: 1/27/16
 * Time: 9:27 AM
 */

namespace App\TranslationalResearchBundle\Security\Voter;


use App\UserdirectoryBundle\Security\Voter\BaseRoleVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\UserdirectoryBundle\Entity\User;



class TranslationalResearchRoleVoter extends BaseRoleVoter {

    protected function getSiteRoleBase() {
        return 'TRANSRES';
    }

    protected function getSitename() {
        return 'translationalresearch';  //Site abbreviation i.e. fellapp, not fellowship-applications
    }


    //Role Voter: attribute is ROLE_...
    //Vote if a user has a general site role
    protected function siteSpecificRoleSupport($attribute, $subject, $sitename, $siteRoleBase) {

        //echo "DeidentifierVoter: support <br>";
        //echo "TRANSRES: ".$sitename.': siteSpecificRoleSupport: attribute='.$attribute."<br>";
        //echo 'subject='.$subject."<br>";

        //does not support UNAPPROVED and BANNED roles for this voter
        if( strpos($attribute, '_UNAPPROVED') !== false || strpos($attribute, '_BANNED') !== false ) {
            //exit('do not support _UNAPPROVED or _BANNED roles');
            return false;
        }

        //Exception for TRANSRES: security.yml will not have dynamically generated admin roles (i.e. ROLE_TRANSRES_ADMIN_USCAP)
//        //all general ADMIN roles are checked by a default voter using role hierarchy in security.yml
//        if( $attribute == 'ROLE_'.$siteRoleBase.'_ADMIN' ) {
//            //exit('do not support ' . 'ROLE_'.$siteRoleBase.'_ADMIN');
//            return false;
//        }

        return true;
    }


    //evaluate if this user has this role (attribute)
    public function voteOnSiteSpecificAttribute($attribute, $subject, TokenInterface $token, $sitename, $siteRoleBase) {
        //exit('voteOnSiteSpecificAttribute: attribute='.$attribute);
        //return false; //testing
        //echo $sitename.': voteOn SiteSpecific Attribute: attribute='.$attribute.", siteRoleBase=".$siteRoleBase."<br>";
        //echo 'attribute='.$attribute."<br>";
        //echo 'subject='.$subject."<br>";
        $user = $token->getUser();
        //return true;

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
                    return false;
                }

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

        ///////////// Check for ROLE_TRANSRES_TECHNICIAN_USCAP if user has role ROLE_TRANSRES_ADMIN_USCAP /////////////
        //1) get the last element '_' => specialty
        $specialty = NULL;
        $attributeArr = explode('_',$attribute);
        if( count($attributeArr) > 0 ) {
            $specialty = end($attributeArr);
            //search specialty DB if exists
            $specialty = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->findOneByRolename($specialty);
        }
        //echo "specialty=$specialty<br>";
        if( $specialty ) {
            $specialtyRolename = $specialty->getRolename();
            //2) check if user has a admin role for this specialty
            $adminPartialRole = "ROLE_TRANSRES_ADMIN_".$specialtyRolename;
            $roleObjects = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserRolesBySiteAndPartialRoleName($user,$sitename,$adminPartialRole);
            //echo $adminPartialRole.": roleObjects count=".count($roleObjects)."<br>";
            if( count($roleObjects) > 0 ) {
                //exit($adminPartialRole.': Dummy partial rolename-site ok');
                return true;
            }
        } else {
            //Check for general role (without specialty) ROLE_TRANSRES_TECHNICIAN if user has role ROLE_TRANSRES_ADMIN_USCAP
            //specialty not provided. i.e. 'ROLE_TRANSRES_TECHNICIAN'
            //2) check if user has a admin role for this specialty
            $adminPartialRole = "ROLE_TRANSRES_ADMIN";
            $roleObjects = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserRolesBySiteAndPartialRoleName($user,$sitename,$adminPartialRole);
            //echo $adminPartialRole.": roleObjects count=".count($roleObjects)."<br>";
            if( count($roleObjects) > 0 ) {
                //exit($adminPartialRole.': Dummy partial rolename-site ok');
                return true;
            }
        }
        ///////////// EOF Check for ROLE_TRANSRES_TECHNICIAN_USCAP if user has role ROLE_TRANSRES_ADMIN_USCAP /////////////

        //exit($attribute.': no access');
        return false;

        //throw new \LogicException('This code should not be reached!');
    }


}


