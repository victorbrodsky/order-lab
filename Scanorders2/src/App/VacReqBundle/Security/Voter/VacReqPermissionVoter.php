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

namespace Oleg\VacReqBundle\Security\Voter;


use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;




class VacReqPermissionVoter extends BasePermissionVoter //BasePermissionVoter   //PatientHierarchyVoter
{

    const CHANGESTATUS_CARRYOVER = 'changestatus-carryover';
    // if the attribute isn't one we support, return false
    protected function supportAttribute($attribute, $subject) {
        if( parent::supportAttribute($attribute, $subject) ) {
            return true;
        } else {
            if( in_array($attribute, array(self::CHANGESTATUS_CARRYOVER)) ) {
                return true;
            }
        }

        return false;
    }
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        if( parent::voteOnAttribute($attribute, $subject, $token) ) {
            return true;
        } else {
            switch($attribute) {
                case self::CHANGESTATUS_CARRYOVER:
                    return $this->canChangeCarryoverStatus($subject, $token);
            }
        }

        return false;
    }


    protected function getSiteRoleBase() {
        //exit('111');
        return 'VACREQ';
    }

    protected function getSitename() {
        return 'vacreq';  //Site abbreviation i.e. fellapp, not fellowship-applications
    }



    protected function canView($subject, TokenInterface $token) {
        //echo "canView: ... <br>";
        if( parent::canView($subject, $token) ) {
            //echo "canView: parent YES<br><br>";
            return true;
        } else {
            //echo "canView: parent NO<br>";
        }

        if( $this->canChangeStatus($subject, $token) ) {
            return true;
        }

        //author can view
        if( $this->isAuthor($subject, $token) ) {
            //echo "canView: author can view <br><br>";
            return true;
        }

        //echo "canView: checkLocalPermission: <br>";
        return $this->checkLocalPermission($subject, $token);
    }

    protected function canEdit($subject, TokenInterface $token) {
        //echo "canEdit: ... <br>";
        if( parent::canEdit($subject, $token) ) {
            return true;
        } {
            //echo "parent canEdit: NO <br>";
        }

        //author can not edit
        if( $this->isAuthor($subject, $token) ) {
            return false;
        }

        return $this->checkLocalPermission($subject, $token);
    }

    //status change: user can view and update the subject
    protected function canChangeStatus($subject, TokenInterface $token) {
        //exit("canChangeStatus: ...");

        //ROLE_PLATFORM_DEPUTY_ADMIN can do anything
        if( $this->decisionManager->decide($token, array('ROLE_PLATFORM_DEPUTY_ADMIN')) ) {
            //exit('admin!');
            return true;
        }

        // if they can edit, they can view
        if( $this->canEdit($subject, $token) ) {
            //echo "canChangeStatus: can edit! <br>";

            //add if user has appropriate admin role: overwrite in the particular permission voter
            //check if approver with the same institution: compare subject->getInstitution() and user's approver role->getInstitution()
            //$user = $token->getUser();
            //echo "canChangeStatus: has approver role? <br>";
            if( $this->hasApproverRoleInstitution($subject,$token) ) {
                //exit("canChangeStatus: has approver role => ok");
                return true;
            }

            //check for tentative pre-approval: use tentativeInstitution
            //echo "canChangeStatus: has tentative approver role? <br>";
            $tentative = true;
            if( $this->hasApproverRoleInstitution($subject,$token,$tentative) ) {
                //exit("canChangeStatus: has tentative approver role => ok");
                return true;
            }
        } else {
            //echo "can not edit <br>";
        }

        //echo "canChangeStatus: check if author <br>";
        //author can not change status
        if( $this->isAuthor($subject,$token) ) {
            return false;
        }

        //check for tentative pre-approval: use tentativeInstitution
        $tentative = true;
        if( $this->hasApproverRoleInstitution($subject,$token,$tentative) ) {
            //exit("canChangeStatus: can change status!");
            return true;
        }

        //exit("canChangeStatus: can not change status!");
        return false;
    }

    public function canChangeCarryoverStatus($subject, TokenInterface $token) {
        //exit("canChangeCarryoverStatus: ...");
        if( $this->canChangeStatus($subject, $token) ) {
            $user = $token->getUser();
            //ROLE_VACREQ_SUPERVISOR_WCM_PATHOLOGY
            $roleName = "ROLE_VACREQ_SUPERVISOR";
            $hasSupervisorRole = $this->em->getRepository('OlegUserdirectoryBundle:User')->
                isUserHasSiteAndPartialRoleName($user, $this->getSitename(), $roleName);
            if( $hasSupervisorRole ) {
                return true;
            }

//            //additionally check for supervisor role
//            $user = $token->getUser();
//            $vacreqUtil = $this->container->get('vacreq_util');
//            $groupParams = array('asObject'=>true);
//            $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
//            $groupInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);
//            //check if subject has at least one of the $groupInstitutions
////            foreach( $groupInstitutions as $inst ) {
////                if( $inst->getId() == $subjectInst->getId() ) {
////                    return true;
////                }
////            }
//            if( count($groupInstitutions) > 0 ) {
//                return true;
//            } else {
//                return false;
//            }
        }
        return false;
    }

    private function checkLocalPermission($subject, TokenInterface $token) {
//        $user = $token->getUser();
//        if( !$user instanceof User ) {
//            return false;
//        }
//        //author can see his request
//        if( is_object($subject) ) {
//            if( $subject->getUser()->getId() == $user->getId() ) {
//                return true;
//            }
//        }

        //check if approver with the same institution: compare subject->getInstitution() and user's approver role->getInstitution()
        if( $this->hasApproverRoleInstitution($subject,$token) ) {
            return true;
        }

        return false;
    }

    //check if approver with the same institution: compare subject->getInstitution() and user's approver role->getInstitution()
    private function hasApproverRoleInstitution( $subject, TokenInterface $token, $tentative=false ) {
        $user = $token->getUser();
        if( !$user instanceof User ) {
            return false;
        }

        if( $tentative ) {
            $subjectInst = $subject->getTentativeInstitution();
        } else {
            $subjectInst = $subject->getInstitution();
        }
        //echo "subjectInst=".$subjectInst."<br>";

        //get approver role for subject institution
        if( $subjectInst ) {

            //get user allowed groups
            $vacreqUtil = $this->container->get('vacreq_util');

            //old get groups method
//            $groupParams = array(
//                'roleSubStrArr' => array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR'),
//                'asObject' => true
//            );
//            $groupInstitutions = $vacreqUtil->getVacReqOrganizationalInstitutions($user,$groupParams);

            if( $tentative ) {
                $tentativeGroupParams = array();
                $tentativeGroupParams['asObject'] = true;
                $tentativeGroupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
                $groupInstitutions = $vacreqUtil->getGroupsByPermission($user,$tentativeGroupParams);
            } else {
                $groupParams = array('asObject'=>true);
                $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
                $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
                $groupInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);
            }

            //check if subject entity has at least one of the $groupInstitutions
            foreach( $groupInstitutions as $inst ) {
                //echo "inst=".$inst."<br>";
                if( $inst->getId() == $subjectInst->getId() ) {
                    return true;
                }
            }

        }
        return false;
    }


//    protected function canChangeStatus($subject, TokenInterface $token) {
//        return false;
//    }

}


