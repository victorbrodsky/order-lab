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
 * Date: 3/24/14
 * Time: 11:59 AM
 * To change this template use File | Settings | File Templates.
 */

namespace App\OrderformBundle\Security\Util;

//All user roles are checked by security context, not $user->hasRole() function. hasRole function will not work for global roles set by security.uml

use Doctrine\Common\Collections\ArrayCollection;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\UserdirectoryBundle\Security\Util\UserSecurityUtil;
use App\UserdirectoryBundle\Entity\PerSiteSettings;


// Note for institution permissions:

//OrderUtil.php
// addInstitutionQueryCriterion($user,$criteriastr)
//  -> getInstitutionQueryCriterion($user) {
//     1) User's PermittedInstitutions
//        $permittedInstitutions = $securityUtil->getUserPermittedInstitutions($user);
//     2) Collaboration check
//        $collaborations = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findCollaborationsByNode($permittedInstitution);
//        foreach( $collaborations as $collaboration ) {
//          foreach( $collaboration->getInstitutions() as $collaborationInstitution ) {
//          }
//        }

//SecurityUtil.php
// hasUserPermission( $entity, $user )
//  -> getUserPermittedInstitutions($user) {
//    1) check if the user belongs to the same institution
//        $permittedInstitutions = $this->getUserPermittedInstitutions($user);
//    2) Check for collaboration
//        $collaborations = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findCollaborationsByNode($permittedInstitution);
//        foreach( $collaborations as $collaboration ) {
//            foreach( $collaboration->getInstitutions() as $collaborationInstitution ) {
//                if(getRepository('AppUserdirectoryBundle:Institution')->isNodeUnderParentnode($collaborationInstitution,$entity->getInstitution()) ) {
//                    $hasCollaborationInst = true;
//                    break;
//                }
//            }
//        }


class SecurityUtil extends UserSecurityUtil {

    //user has permission to perform the view/edit the valid field, created by someone else, if he/she is submitter or ROLE_SCANORDER_PROCESSOR or service chief or division chief
    //Added 25Nov2015: If user A submits a scan order with WCMC as the Institutional PHI Scope in Order Info and user B belongs to the institution NYP,
    // they can not see each other's orders/patient data/etc.
    //$entity is object: message or patient, accession, part ...
    //$collaborationTypesStrArr: array("Union","Intersection","Untrusted Intersection"); if null - ignore collaboration.
    //$actionStrArr: array("show","edit","amend"); if null - ignore (allow) all actions; if not supported action - allow this action.
    //Used by: CheckController (check button on patient hierarchy), MultiScanOrderController (show patient hierarchy in the order)
    public function hasUserPermission( $entity, $user, $collaborationTypesStrArr=array("Union"), $actionStrArr=array("show") ) {
        //echo "hasUserPermission <br>";
        if( $entity == null ) {
            return true;
        }

        if( $user == null ) {
            return false;
        }

        if( !$entity->getInstitution() ) {
            throw new \Exception( 'Entity is not linked to any Institution. Entity:'.$entity );
        }

        ///////////////// 1) check if the object is under user's permitted institutions /////////////////
        //check if entity is under user's permitted and collaborated institutions
        if( $this->isObjectUnderUserPermittedCollaboratedInstitutions( $entity, $user, $collaborationTypesStrArr ) == false ) {
            //exit("isObjectUnderUserPermittedCollaboratedInstitutions false");
            return false;
        }
        ///////////////// EOF 1) /////////////////

        ///////////////// 2) check if logged in user is granted given action for a given object $entity (using voter) /////////////////
        if( $this->isLoggedUserGrantedObjectActions($entity,$actionStrArr) ) {
            return true;
        }
        ///////////////// EOF /////////////////

        //exit("hasUserPermission: no permission to show ".$entity);
        return false;
    }

    //check user actions
    private function isLoggedUserGrantedObjectActions( $entity, $actionStrArr ) {
        if( !$actionStrArr ) {
            return false;
        }
        foreach( $actionStrArr as $action ) {
            //echo "check action=".$action."<br>";
            if( false === $this->secAuth->isGranted($action, $entity) ) {
                return false;
            }
        }

        //echo "Logged in user can perform action=".$action." on object=".$entity."<br>";
        return true;
    }

    public function isObjectUnderUserPermittedCollaboratedInstitutions( $entity, $user, $collaborationTypesStrArr ) {
        $permittedInstitutions = $this->getUserPermittedInstitutions($user);

        //a) check permitted institutions
        if( $this->em->getRepository('AppUserdirectoryBundle:Institution')->isNodeUnderParentnodes($permittedInstitutions,$entity->getInstitution()) ) {
            return true;
        }

        //b) if user's permitted institutions are not enough to access this entity => check for collaboration institutions
        $orderUtil = $this->container->get('scanorder_utility');
        $collaborationInstitutions = $orderUtil->getPermittedScopeCollaborationInstitutions($permittedInstitutions,$collaborationTypesStrArr,false);

        //echo "collaborationInstitutions count=".count($collaborationInstitutions)."<br>";
        //foreach( $collaborationInstitutions as $collaborationInstitution ) {
            //echo "collaborationInstitution=".$collaborationInstitution."<br>";
        //}

        if( $this->em->getRepository('AppUserdirectoryBundle:Institution')->isNodeUnderParentnodes($collaborationInstitutions,$entity->getInstitution()) ) {
            return true;
        }
        //exit("no collaboration institutions");

        return false;
    }


//    //wrapper for hasUserPermission
//    public function hasPermission( $entity, $security_content ) {
//        return $this->hasUserPermission($entity,$security_content->getToken()->getUser());
//    }

    //check if the given user can perform given actions on the content of the given order
    public function isUserAllowOrderActions( $order, $user, $actions=null ) {
        //echo "is User Allow OrderActions <br>";
        if( !$this->hasUserPermission( $order, $user, array("Union"), $actions ) ) {
            //exit('has permission false');
            return false;
        }

        //if actions are not specified => allow all actions
        if( $actions == null ) {
            return true;
        }

        //if actions is not array => return false
        if( !is_array($actions) ) {
            throw new \Exception('Actions must be an array');
            //return false;
        }

        //at this point, actions array has list of actions to performed by this user
        //echo "order=".$order->getId()."<br>";
        //print_r($actions);

        //processor and division chief can perform any actions
        if(
            $this->secAuth->isGranted('ROLE_SCANORDER_ADMIN') ||
            $this->secAuth->isGranted('ROLE_SCANORDER_PROCESSOR') ||
            $this->secAuth->isGranted('ROLE_SCANORDER_DIVISION_CHIEF')
        ) {
            return true;
        }

        //submitter(owner) and ordering provider can perform any actions
        //echo $order->getProvider()->getId() . " ?= " . $user->getId() . "<br>";
        $isProxyUser = false;
        foreach( $order->getProxyuser() as $proxyuser ) {
            if( $proxyuser->getUser() && $proxyuser->getUser()->getId() === $user->getId() ) {
                $isProxyUser = true;
                break;
            }
        }

        if( $order->getProvider()->getId() === $user->getId() || $isProxyUser ) {
            return true;
        }

        //order's institution
        $orderInstitution = $order->getInstitution();

        $userSiteSettings = $this->getUserPerSiteSettings($user);
        $userChiefServices = $userSiteSettings->getChiefServices();

        //service chief can perform any actions
        //if( $userChiefServices->contains($service) ) {
        //    return true;
        //}

        //service chief can perform any actions for all orders under his/her service scope
        foreach( $userChiefServices as $userChiefService ) {
            if( $this->em->getRepository('AppUserdirectoryBundle:Institution')->isNodeUnderParentnode($userChiefService, $orderInstitution) ) {
                return true;
            }
        }

        //At this point we have only regular users
        //print_r($actions);

        $actionAllowed = false;

        //for each action
        foreach( $actions as $action ) {

            //echo "action=".$action."<br>";

            //status change can be done only by submitter(owner), ordering provider, or service chief: it would not get here, so not allowed
            if( $action == 'changestatus' ) {
                return false;
            }

            //amend can be done only by submitter(owner), ordering provider, or service chief: it would not get here, so not allowed
            if( $action == 'amend' ) {
                return false;
            }

            //edit can be done only by submitter(owner), ordering provider, or service chief: it would not get here, so not allowed
            if( $action == 'edit' ) {
                return false;
            }

            //show is allowed if the user belongs to the same service
            if( $action == 'show' ) {
                $actionAllowed = true;
                //show action is allowed to all users which passed hasUserPermission and reached this point, so disable the code below.
//                $userServices = $userSiteSettings->getScanOrderInstitutionScope();
//                foreach( $userServices as $userService ) {
//                    if( $this->em->getRepository('AppUserdirectoryBundle:Institution')->isNodeUnderParentnode($userService, $orderInstitution) ) {
//                        return true;
//                    }
//                }
            }
        }

        if( $actionAllowed ) {
            return true;
        }

        //exit('is User Allow Order Actions: no permission');
        return false;
    }

    public function getUserPermittedInstitutions($user) {

        $institutions = new ArrayCollection();

        $entity = $this->getUserPerSiteSettings($user);

        if( !$entity ) {
            //echo "no UserPerSiteSettings found for ".$user."<br>";
            return $institutions;
        }

        $institutions = $entity->getPermittedInstitutionalPHIScope();

        return $institutions;
    }

//    public function getUserDefaultService($user) {
//        $entity = $this->getUserPerSiteSettings($user);
//
//        if( !$entity )
//            return null;
//
//        return $entity->getDefaultService();
//    }

    public function getScanOrdersServicesScope($user) {

        $institutions = new ArrayCollection();
        //$institution = null;

        $entity = $this->getUserPerSiteSettings($user);

        if( !$entity ) {
            //echo "!entity <br>";
            return $institutions;
        }

        $institutions = $entity->getScanOrderInstitutionScope();

        return $institutions;
    }

    public function getUserChiefServices($user) {

        $services = new ArrayCollection();

        $entity = $this->getUserPerSiteSettings($user);

        if( !$entity )
            return $services;

        $services = $entity->getChiefServices();

        return $services;
    }

    public function getUserPerSiteSettings($user) {
        if( $user instanceof User ) {
            return $user->getPerSiteSettings();
        } else {
            return null;
        }
        //$entity = $this->em->getRepository('AppOrderformBundle:PerSiteSettings')->findOneByUser($user);
        //return $entity;
    }

//    public function getDefaultDepartmentDivision($message,$userSiteSettings) {
//
//        if( $service = $message->getScanorder()->getService() ) {
//            $division = $service->getParent();
//            $department = $division->getParent();
//        } else {
//            //first get default division and department
//            $department = $userSiteSettings->getDefaultDepartment();
//            if( !$department ) {
//                //set default department to Pathology and Laboratory Medicine
//                $department = $this->em->getRepository('AppUserdirectoryBundle:Department')->findOneByName('Pathology and Laboratory Medicine');
//
//            }
//            if( $message->getInstitution() == null || ($message->getInstitution() && $department->getParent()->getId() != $message->getInstitution()->getId()) ) {
//                $department = null;
//            }
//
//            $division = $userSiteSettings->getDefaultDivision();
//            if( !$division ) {
//                //set default division to Anatomic Pathology
//                $division = $this->em->getRepository('AppUserdirectoryBundle:Division')->findOneByName('Anatomic Pathology');
//            }
//            if( $department == null || ($department && $division && $division->getParent()->getId() != $department->getId()) ) {
//                $division = null;
//            }
//
//        }
////        echo $department->getParent()->getId()."?=?".$message->getInstitution()->getId()."<br>";
//
//
//        $params = array();
//        $params['department'] = $department;
//        $params['division'] = $division;
//
//        return $params;
//    }

    public function addInstitutionalPhiScopeWCMC($user,$creator) {
        $inst = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation('WCM');
        $persitesettings = $this->getUserPerSiteSettings($user);
        if( !$persitesettings ) {
            //set institution to per site settings
            $persitesettings = new PerSiteSettings();
            $persitesettings->setAuthor($creator);
            $persitesettings->setUser($user);
            $persitesettings->addPermittedInstitutionalPHIScope($inst);
            ////////// EOF assign Institution //////////
        }
        $persitesettings->addPermittedInstitutionalPHIScope($inst);
        return $persitesettings;
    }

    public function getTooltip($user) {
        $tooltip = true;
        $siteSettings = $this->getUserPerSiteSettings($user);
        if( $siteSettings ) {
            $tooltip = $siteSettings->getTooltip();
        } else {
            //echo 'siteSettings not exists';
            //exit();
        }
        return $tooltip;
    }


}