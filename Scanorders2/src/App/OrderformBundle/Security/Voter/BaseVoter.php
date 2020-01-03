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

namespace App\OrderformBundle\Security\Voter;


use App\OrderformBundle\Entity\Message;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


//NOT USED ANYMORE
abstract class BaseVoter extends Voter {

    const CREATE = 'create';
    const READ   = 'read';
    const UPDATE = 'update';
    const DELETE = 'delete'; //mark it inactive/invalid since we don't delete; this and 3 above are for Data Quality role

    const CHANGESTATUS = 'changestatus';

    protected $decisionManager;
    protected $em;
    protected $container;

    public function __construct(AccessDecisionManagerInterface $decisionManager, $em, $container)
    {
        $this->decisionManager = $decisionManager;
        $this->em = $em;
        $this->container = $container;
    }

    protected function convertAttribute($attribute)
    {
        switch($attribute) {

            case 'view':
            case 'show':
                return self::READ;

            case 'edit':
            case 'amend':
                return self::UPDATE;

            default:
                return $attribute;

        }

        return $attribute;
    }

    protected function canView($subject, TokenInterface $token)
    {
        //echo "canView? <br>";

        //return false; //test

        // if they can edit, they can view
        if( $this->canEdit($subject, $token) ) {
            //echo "user can edit <br>";
            return true;
        }

        $user = $token->getUser();
        $securityUtil = $this->container->get('order_security_utility');
        //exit('1');
        //minimum requirement: subject must be under user's permitted/collaborated institutions
        //don't perform this check for dummy, empty objects
        if( $subject->getId() && $securityUtil->isObjectUnderUserPermittedCollaboratedInstitutions( $subject, $user, array("Union") ) == false ) {
            return false;
        }

        //TODO: implement the permission: find out if the user has a role for corresponding to the given subject and 'read' action.
        if(1) {
            //1) find roles with permissions related to a subject (Patient, Encounter ...)
            //2) check for each roles if user hasRole

            //get object class name
            $class = new \ReflectionClass($subject);
            $className = $class->getShortName();

            if( $className == "Message" ) {
                $className = "Order";
            }
            echo "className ".$className."<br>";
            //exit('1');

            //TODO: we need to define what is "Order", "Patient" and "Patient Data" permissions. Patient has a Procedure, Encounter, Accession etc.

            //check if the user has role with a permission $subject class name (i.e. "Patient") and "read"
            if( $this->em->getRepository('AppUserdirectoryBundle:User')->isUserHasPermissionObjectAction( $user, $className, "read" ) ) {
                //exit('can View! exit');
                return true;
            } else {
                echo "can not view ".$className."<br>";
            }

            //echo "can not view subject=".$subject."<br>";
            //exit('can not View exit');
        }

        return false;
    }

    //status change can be done only by submitter(owner), ordering provider, or service chief
    protected function canChangeStatus($subject, TokenInterface $token) {

        // if they can edit, they can view
        if( $this->canEdit($subject, $token) ) {
            return true;
        }

        return false;
    }

    protected function canEdit($subject, TokenInterface $token)
    {
        //echo "canEdit? <br>";

        //dummy object just created with as new => can not edit dummy object
        if( !$subject->getId() ) {
            return false;
        }

        if( $this->isOwner($subject, $token) ) {
            //echo "user is provider <br>";
            return true;
        }

        //ROLE_PLATFORM_DEPUTY_ADMIN can do anything
        if( $this->decisionManager->decide($token, array('ROLE_PLATFORM_DEPUTY_ADMIN')) ) {
            return true;
        }

        $user = $token->getUser();
        $securityUtil = $this->container->get('order_security_utility');

        //minimum requirement: subject must be under user's permitted/collaborated institutions
        if( $securityUtil->isObjectUnderUserPermittedCollaboratedInstitutions( $subject, $user, array("Union") ) == false ) {
            return false;
        }

        //subject's institution
        $subjectInstitution = $subject->getInstitution();

        //service chief can perform any actions if the objects under his/her service scope
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        $userChiefServices = $userSiteSettings->getChiefServices();
        if( $this->em->getRepository('AppUserdirectoryBundle:Institution')->isNodeUnderParentnodes( $userChiefServices, $subjectInstitution ) ) {
            return true;
        }

        //ROLE_SCANORDER_ADMIN can do anything if object is under his permitted institutions
        if( $this->decisionManager->decide($token, array('ROLE_SCANORDER_ADMIN')) ) {
            return true;
        }

        //echo "can not Edit! <br>";
        return false;
    }

    protected function canCreate($subject, TokenInterface $token)
    {
        exit("Create is not supported for subject=" . $subject);
    }

}
