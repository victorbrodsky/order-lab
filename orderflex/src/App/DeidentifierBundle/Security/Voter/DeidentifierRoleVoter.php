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

namespace App\DeidentifierBundle\Security\Voter;


use App\UserdirectoryBundle\Security\Voter\BaseRoleVoter;
//use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;



class DeidentifierRoleVoter extends BaseRoleVoter {

    protected function getSiteRoleBase() {
        return 'DEIDENTIFICATOR';
    }

    protected function getSitename() {
        return 'deidentifier';  //Site abbreviation i.e. fellapp, not fellowship-applications
    }


//    private $decisionManager;
//    private $em;
//    private $container;
//
//    public function __construct(AccessDecisionManagerInterface $decisionManager, $em, $container)
//    {
//        $this->decisionManager = $decisionManager;
//        $this->em = $em;
//        $this->container = $container;
//    }
//
//    //Role Voter: attribute is ROLE_...
//    //Vote if a user has a general site role
//    public function supports($attribute, $subject)
//    {
//        //echo "DeidentifierVoter: support <br>";
//        //echo 'attribute='.$attribute."<br>";
//        //echo 'subject='.$subject."<br>";
//
//        //does not support UNAPPROVED and BANNED roles for this voter
//        if( strpos($attribute, '_UNAPPROVED') !== false || strpos($attribute, '_BANNED') !== false ) {
//            //exit('do not support _UNAPPROVED roles');
//            return false;
//        }
//
//        //all general roles are checked by a default voter using role hierarchy in security.yml
//        if( $attribute == 'ROLE_DEIDENTIFICATOR_ADMIN' ) {
//            return false;
//        }
//
//        //support DEIDENTIFICATOR roles only
//        if( strpos($attribute, 'ROLE_DEIDENTIFICATOR_') !== false ) {
//            return true;
//        }
//
//        return false;
//    }
//
//    //evaluate if this user has this role (attribute)
//    public function voteOnAttribute($attribute, $subject, TokenInterface $token)
//    {
//        //echo 'attribute='.$attribute."<br>";
//        //echo 'subject='.$subject."<br>";
//        $user = $token->getUser();
//        //return true;
//
//        if( !$user instanceof User ) {
//            // the user must be logged in; if not, deny access
//            //exit('user is not object');
//            return false;
//        }
//
////        //ignore banned role in this voter
////        if( $attribute == 'ROLE_DEIDENTIFICATOR_BANNED' ) {
////            //echo 'banned attribute='.$attribute."<br>";
////            if( $user->hasRole($attribute) ) {
////                //exit('user is banned');
////                return true;
////            } else {
////                return false;
////            }
////        }
////
////        //ignore banned role in this voter
////        if( $attribute == 'ROLE_DEIDENTIFICATOR_UNAPPROVED' ) {
////            if( $user->hasRole($attribute) ) {
////                //exit('user is unaproved: attribute='.$attribute);
////                return true;
////            } else {
////                return false;
////            }
////        }
//
//        //echo 'attribute='.$attribute."<br>";
//        //echo 'subject='.$subject."<br>";
//
//        //ROLE_DEIDENTIFICATOR_ADMIN can do anything
//        if( $this->decisionManager->decide($token, array('ROLE_DEIDENTIFICATOR_ADMIN')) ) {
//            //exit('admin!');
//            return true;
//        }
//
//        //check if user has this role including hiearchy roles
//        if( $user->hasRole($attribute) ) {
//            //if user has this role => access is granted
//            // exit('hasRole');
//            return true;
//        }
//
//        //check for general dummy role ROLE_DEIDENTIFICATOR_USER
//        if( $attribute == 'ROLE_DEIDENTIFICATOR_USER' ) {
//            //exit('check general user role='.$attribute);
//            if( $this->hasGeneralSiteRole($user,'deidentifier') ) {
//                //echo 'hasGeneralSiteRole yes <br>';
//                //exit('hasGeneralSiteRole');
//
//                //check if user has ROLE_DEIDENTIFICATOR_BANNED or ROLE_DEIDENTIFICATOR_UNAPPROVED
//                if( $user->hasRole("ROLE_DEIDENTIFICATOR_BANNED") || $user->hasRole("ROLE_DEIDENTIFICATOR_UNAPPROVED") ) {
//                    return false;
//                }
//
//                return true;
//                //return VoterInterface::ACCESS_GRANTED;
//            }
//
//        }
//
////        //check for enquirer dummy role ROLE_DEIDENTIFICATOR_ENQUIRER
////        if( $attribute == 'ROLE_DEIDENTIFICATOR_ENQUIRER' ) {
////
////            //check for permission this time: object: accession, action: read
////            if( $this->hasGeneralSiteRole($user,'deidentifier') ) {
////                //echo 'hasGeneralSiteRole yes <br>';
////                //exit('hasGeneralSiteRole');
////
////                //check if user has ROLE_DEIDENTIFICATOR_BANNED or ROLE_DEIDENTIFICATOR_UNAPPROVED
////                if( $user->hasRole("ROLE_DEIDENTIFICATOR_BANNED") || $user->hasRole("ROLE_DEIDENTIFICATOR_UNAPPROVED") ) {
////                    return false;
////                }
////
////                return true;
////                //return VoterInterface::ACCESS_GRANTED;
////            }
////
////        }
//
//        //exit('uknown dummy user role='.$attribute);
//
//        //Dummy unknown role: check if this role has appropriate site name
//        $roleObject = $this->em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($attribute);
//        if( $roleObject ) {
//            foreach( $roleObject->getSites() as $site ) {
//                if( $site->getName()."" == 'deidentifier' ) {
//                    //exit('Dummy unknown site ok');
//                    return true;
//                }
//            }
//        }
//
//        //exit('no access');
//        return false;
//
//        //throw new \LogicException('This code should not be reached!');
//    }
//
//
//    public function hasGeneralSiteRole( $user, $siteStr ) {
//
//        foreach( $user->getRoles() as $roleStr ) {
//            //echo 'roleStr='.$roleStr."<br>";
//            $role = $this->em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($roleStr);
//            if( $role ) {
//                foreach( $role->getSites() as $site ) {
//                    //echo 'role='.$role.", site=".$site->getName()."<br>";
//                    if( $site->getName()."" == $siteStr."" ) {
//                        //echo "access true <br>";
//                        return true;
//                    }
//                }
//            }
//        }
//
//        return false;
//    }


}


