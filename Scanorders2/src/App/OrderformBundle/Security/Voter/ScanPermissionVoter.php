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

namespace Oleg\OrderformBundle\Security\Voter;


//use App\OrderformBundle\Entity\Accession;
//use App\OrderformBundle\Entity\Block;
//use App\OrderformBundle\Entity\Encounter;
//use App\OrderformBundle\Entity\Imaging;
//use App\OrderformBundle\Entity\Message;
//use App\OrderformBundle\Entity\Part;
//use App\OrderformBundle\Entity\Patient;
//use App\OrderformBundle\Entity\Procedure;
//use App\OrderformBundle\Entity\Slide;
//use App\UserdirectoryBundle\Entity\User;
use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Entity\Encounter;
use Oleg\OrderformBundle\Entity\Imaging;
use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\Procedure;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;



class ScanPermissionVoter extends BasePermissionVoter {

    const SIGN = 'sign';


    protected function getSiteRoleBase() {
        return 'SCANORDER';
    }

    protected function getSitename() {
        return 'scan';  //Site abbreviation i.e. fellapp, not fellowship-applications
    }



    //TODO: might add additional check:
    //isOwner - owner can perform any actions for this object
    //isChief - service chief can perform any actions if the objects under his/her service scope

    //$subject: string (i.e. "FellowshipApplication") or entity
    protected function canView($subject, TokenInterface $token)
    {
        //echo "subject=".$subject."<br>";
        //exit('Scan PermissionVoter: canView');

        if( parent::canView($subject,$token) ) {
            return true;
        }

        if( $this->isOwner($subject, $token) ) {
            //echo "user is provider <br>";
            return true;
        }

        //exit('can not view');

        return false;
    }

    //$subject: string (i.e. "FellowshipApplication") or entity
    protected function canEdit($subject, TokenInterface $token)
    {
        //exit('2');
        if( parent::canEdit($subject,$token) ) {
            return true;
        }

        if( !is_object($subject) ) {
            return false;
        }

        if( $this->isOwner($subject, $token) ) {
            //echo "user is provider <br>";
            return true;
        }

        //service chief can perform any actions if the objects under his/her service scope
        //subject's institution
        $subjectInstitution = $subject->getInstitution();
        if( $subjectInstitution ) {
            $user = $token->getUser();
            $securityUtil = $this->container->get('order_security_utility');
            $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
            $userChiefServices = $userSiteSettings->getChiefServices();
            if ($this->em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnodes($userChiefServices, $subjectInstitution)) {
                return true;
            }
        }

        return false;
    }


    //TODO: must decide how to deal with Patient hierarchy:
    // if permission is given to patient object, does it mean that
    // this permission propagates to the underlying patient objects.
    // Possible Solution:
    // 1) Create permission objects for each patient underlying objects with corresponding object and action:
    //    "View Patient", "View Encounter", "View Procedure", "View Accession", "View Part", "View Block", "View Slide", "View Scan" ...
    // 2) Create a role ROLE_SCANORDER_WCM_ALL_UDERLYING_PATIENT_DATA_VIEW - View all underlying Patient objects
    // 3) Assign permission objects from (1) to the role ROLE_SCANORDER_WCM_ALL_UDERLYING_PATIENT_DATA_VIEW



    protected function isOwner($subject, TokenInterface $token) {

        if( !method_exists($subject, "getProvider") ){
            return false;
        }

        if( !$subject->getId() || !$subject->getProvider() ) {
            return false;
        }

        $user = $token->getUser();

        if( $subject->getProvider()->getId() === $user->getId() ) {
            //echo "user is provider <br>";
            return true;
        }

        return false;
    }


} 