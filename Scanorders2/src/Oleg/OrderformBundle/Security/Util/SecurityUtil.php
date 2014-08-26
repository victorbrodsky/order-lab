<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 3/24/14
 * Time: 11:59 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Security\Util;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\UserdirectoryBundle\Security\Util\UserSecurityUtil;

class SecurityUtil extends UserSecurityUtil {

    //user has permission to perform the view/edit the valid field, created by someone else, if he/she is submitter or ROLE_SCANORDER_PROCESSOR or service chief or division chief
    //$entity is object: orderinfo or patient, accession, part ...
    public function hasUserPermission( $entity, $user ) {

        if( $entity == null ) {
            return true;
        }

        if( $user == null ) {
            return false;
        }

        if( !$entity->getInstitution() ) {
            throw new \Exception( 'Entity is not linked to any Institution. Entity:'.$entity );
        }

        ///////////////// 1) check if the user belongs to the same institution /////////////////
        $hasInst = false;

        $allowedInstitutions = $this->getUserPermittedInstitutions($user);
        if( $allowedInstitutions->contains($entity->getInstitution()) ) {
            $hasInst = true;
        }
//        foreach( $allowedInstitutions as $inst ) {
//            //echo "compare: ".$inst->getId()."=?".$entity->getInstitution()->getId()."<br>";
//            if( $inst->getId() == $entity->getInstitution()->getId() ) {
//                $hasInst = true;
//            }
//        }

        if( $hasInst == false ) {
            return false;
        }
        ///////////////// EOF 1) /////////////////


        ///////////////// 2) check if the user is processor or service, division chief /////////////////
        if(
            $user->hasRole('ROLE_SCANORDER_ADMIN') ||
            $user->hasRole('ROLE_SCANORDER_PROCESSOR') ||
            $user->hasRole('ROLE_SCANORDER_DIVISION_CHIEF') ||
            $user->hasRole('ROLE_SCANORDER_SERVICE_CHIEF')
        ){
            return true;
        }
        ///////////////// EOF 2) /////////////////

        ///////////////// 3) submitters  /////////////////
        if( $user->hasRole('ROLE_SCANORDER_SUBMITTER') ) {
            return true;
        }
        ///////////////// EOF 3) /////////////////

        ///////////////// 4) pathology members  /////////////////
//        if(
//            true === $user->hasRole('ROLE_SCANORDER_PATHOLOGY_RESIDENT') ||
//            true === $user->hasRole('ROLE_SCANORDER_PATHOLOGY_FELLOW') ||
//            true === $user->hasRole('ROLE_SCANORDER_PATHOLOGY_FACULTY')
//        ) {
//            return true;
//        }
        ///////////////// EOF 4) /////////////////

        return false;

    }

//    //wrapper for hasUserPermission
//    public function hasPermission( $entity, $security_content ) {
//        return $this->hasUserPermission($entity,$security_content->getToken()->getUser());
//    }

    //check if the given user can perform given actions on the content of the given order
    public function isUserAllowOrderActions( $order, $user, $actions=null ) {

        if( !$this->hasUserPermission( $order, $user ) ) {
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

        //processor and division chief can perform any actions
        if(
            $user->hasRole('ROLE_SCANORDER_ADMIN') ||
            $user->hasRole('ROLE_SCANORDER_PROCESSOR') ||
            $user->hasRole('ROLE_SCANORDER_DIVISION_CHIEF')
        ) {
            return true;
        }

        //submitter(owner) and ordering provider can perform any actions
        //echo $order->getProvider()->getId() . " ?= " . $user->getId() . "<br>";
        if( $order->getProvider()->getId() === $user->getId() || $order->getProxyuser()->getId() === $user->getId() ) {
            return true;
        }

        //order's service
        $service = $order->getService();

        $userSiteSettings = $this->getUserPerSiteSettings($user);

        //service chief can perform any actions
        $userChiefServices = $userSiteSettings->getChiefServices();
        if( $userChiefServices->contains($service) ) {
            return true;
        }

        //At this point we have only regular users

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
                //echo "action: show <br>";
                $userServices = $userSiteSettings->getScanOrdersServicesScope();
                if( $userServices->contains($service) ) {
                    return true;
                }
            }
        }

        //exit('is User Allow Order Actions: no permission');
        return false;
    }

    public function getUserPermittedInstitutions($user) {

        $institutions = new ArrayCollection();

        $entity = $this->getUserPerSiteSettings($user);

        if( !$entity )
            return $institutions;

        $institutions = $entity->getPermittedInstitutionalPHIScope();

        return $institutions;
    }

    public function getUserDefaultService($user) {
        $entity = $this->getUserPerSiteSettings($user);

        if( !$entity )
            return null;

        return $entity->getDefaultService();
    }

    public function getUserScanorderServices($user) {

        $services = new ArrayCollection();

        $entity = $this->getUserPerSiteSettings($user);

        if( !$entity )
            return $services;

        $services = $entity->getScanOrdersServicesScope();

        return $services;
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
        $entity = $this->em->getRepository('OlegOrderformBundle:PerSiteSettings')->findOneBy(
            array('user' => $user, 'siteName' => 'scanorder')
        );

        return $entity;
    }


}