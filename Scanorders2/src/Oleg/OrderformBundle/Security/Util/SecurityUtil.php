<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 3/24/14
 * Time: 11:59 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Security\Util;

//All user roles are checked by security context, not $user->hasRole() function. hasRole function will not work for global roles set by security.uml

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\UserdirectoryBundle\Security\Util\UserSecurityUtil;
use Oleg\OrderformBundle\Entity\PerSiteSettings;


// Note for institution permissions:

//OrderUtil.php
// addInstitutionQueryCriterion($user,$criteriastr)
//  -> getInstitutionQueryCriterion($user) {
//     1) User's PermittedInstitutions
//        $permittedInstitutions = $securityUtil->getUserPermittedInstitutions($user);
//     2) Collaboration check
//        $collaborations = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->findCollaborationsByNode($permittedInstitution);
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
//        $collaborations = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->findCollaborationsByNode($permittedInstitution);
//        foreach( $collaborations as $collaboration ) {
//            foreach( $collaboration->getInstitutions() as $collaborationInstitution ) {
//                if(getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode($collaborationInstitution,$entity->getInstitution()) ) {
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
    //Used by: CheckController (check button on patient hierarchy), MultiScanOrderController (show patient hierarchy in the order)
    public function hasUserPermission( $entity, $user, $collaborationTypesStrArr=array("Union") ) {
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

        ///////////////// 1) check if the user belongs to the same institution /////////////////
        $hasInst = false;

        //permittedInstitutionalPHIScope - institutions
        $permittedInstitutions = $this->getUserPermittedInstitutions($user);

        $entityInstitution = $entity->getInstitution();
        foreach( $permittedInstitutions as $permittedInstitution ) {
            //echo "permittedInstitution=".$permittedInstitution."<br>";
            if( $this->em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode($entityInstitution, $permittedInstitution) ) {
                //echo "entityInstitution=".$entityInstitution." has a node ".$permittedInstitution."<br>";
                $hasInst = true;
                //return true;
                break;
            }
        }

//        //This is a primitive check to verify if the order has the same institution as a user's allowedInstitutions
//        if( $permittedInstitutions->contains($entity->getInstitution()) ) {
//            echo "parentNode has a node <br>";
//            $hasInst = true;
//            //return true; //testing
//        }

        //echo "hasInst=".$hasInst."<br>";

        //if user's permitted institution is not enough to access this entity => check for collaboration
        if( $hasInst == false ) {

            $hasCollaborationInst = false;

            //Check for collaboration (check if the user belongs to an institution that is in collaboration with an institution to which the patient/order/etc belongs).

            $bruteForce = true;
            if( !$bruteForce ) {
                //check collaboration by one query. Might be need it for performance optimization?
                if( $this->em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeHasCollaborationWithUserPermittedInstitutions($entity->getInstitution(), $permittedInstitutions) ) {
                    //echo "Collaboration: true <br>";
                    $hasCollaborationInst = true;
                }
            } else {
                if(1){
                    //Brute force method with foreach
                    //1) find collaboration for user's institution if exists
                    //2) if collaboration exists, check if message's institution belongs to any institution of this collaboration
                    foreach( $permittedInstitutions as $permittedInstitution ) {
                        if( $hasCollaborationInst == false ) {
                            $collaborations = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->
                                findCollaborationsByNode( $permittedInstitution, $collaborationTypesStrArr ); //array("Union","Intersection")
                            foreach( $collaborations as $collaboration ) {
                                if( $hasCollaborationInst == false ) {
                                    foreach( $collaboration->getInstitutions() as $collaborationInstitution ) {
                                        if( $this->em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode($collaborationInstitution,$entity->getInstitution()) ) {
                                            //echo "Collaboration: true <br>";
                                            $hasCollaborationInst = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }//foreach
                } else {
                    $hasCollaborationInst = true;
                }
            }//if brute force

            if( $hasCollaborationInst ) {
                $hasInst = true;
            }
        }

//        $parentNode = $entity->getInstitution();
//        $node = $allowedInstitutions;
//        if( $this->em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode($parentNode, $node) ) {
//            $hasInst = true;
//            return true;
//        }

//        foreach( $allowedInstitutions as $inst ) {
//            //echo "compare: ".$inst->getId()."=?".$entity->getInstitution()->getId()."<br>";
//            if( $inst->getId() == $entity->getInstitution()->getId() ) {
//                $hasInst = true;
//            }
//        }

        if( $hasInst == false ) {
            //echo "hasInst == false <br>";
            return false;
        }

        ///////////////// EOF 1) /////////////////


        ///////////////// 2) check if the user is processor or service, division chief /////////////////
        if(
            $this->sc->isGranted('ROLE_SCANORDER_ADMIN') ||
            $this->sc->isGranted('ROLE_SCANORDER_PROCESSOR') ||
            $this->sc->isGranted('ROLE_SCANORDER_DIVISION_CHIEF') ||
            $this->sc->isGranted('ROLE_SCANORDER_SERVICE_CHIEF')
        ){
            return true;
        }
        ///////////////// EOF 2) /////////////////

        ///////////////// 3) submitters  /////////////////
        if( $this->sc->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return true;
        }
        ///////////////// EOF 3) /////////////////

        ///////////////// 4) pathology members  /////////////////
//        if(
//            $this->sc->isGranted('ROLE_SCANORDER_PATHOLOGY_RESIDENT') ||
//            $this->sc->isGranted('ROLE_SCANORDER_PATHOLOGY_FELLOW') ||
//            $this->sc->isGranted('ROLE_SCANORDER_PATHOLOGY_FACULTY')
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
            $this->sc->isGranted('ROLE_SCANORDER_ADMIN') ||
            $this->sc->isGranted('ROLE_SCANORDER_PROCESSOR') ||
            $this->sc->isGranted('ROLE_SCANORDER_DIVISION_CHIEF')
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
            if( $this->em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode($userChiefService, $orderInstitution) ) {
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
//                    if( $this->em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode($userService, $orderInstitution) ) {
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
//        $entity = $this->em->getRepository('OlegOrderformBundle:PerSiteSettings')->findOneBy(
//            array('user' => $user, 'siteName' => 'scanorder')
//        );
        $entity = $this->em->getRepository('OlegOrderformBundle:PerSiteSettings')->findOneByUser($user);

        return $entity;
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
//                $department = $this->em->getRepository('OlegUserdirectoryBundle:Department')->findOneByName('Pathology and Laboratory Medicine');
//
//            }
//            if( $message->getInstitution() == null || ($message->getInstitution() && $department->getParent()->getId() != $message->getInstitution()->getId()) ) {
//                $department = null;
//            }
//
//            $division = $userSiteSettings->getDefaultDivision();
//            if( !$division ) {
//                //set default division to Anatomic Pathology
//                $division = $this->em->getRepository('OlegUserdirectoryBundle:Division')->findOneByName('Anatomic Pathology');
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
        $inst = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName('Weill Cornell Medical College');
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