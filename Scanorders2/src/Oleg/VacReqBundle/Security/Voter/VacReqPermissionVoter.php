<?php
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

    protected function getSiteRoleBase() {
        //exit('111');
        return 'VACREQ';
    }

    protected function getSitename() {
        return 'vacreq';  //Site abbreviation i.e. fellapp, not fellowship-applications
    }


    protected function canView($subject, TokenInterface $token) {

        if( parent::canView($subject, $token) ) {
            return true;
        }

        return $this->checkLocalPermission($subject, $token);
    }

    protected function canEdit($subject, TokenInterface $token) {

        if( parent::canEdit($subject, $token) ) {
            return true;
        }

        return $this->checkLocalPermission($subject, $token);
    }

    //status change: user can view and update the subject
    protected function canChangeStatus($subject, TokenInterface $token) {

        //exit("canChangeStatus: not implemented yet");

        //ROLE_PLATFORM_DEPUTY_ADMIN can do anything
        if( $this->decisionManager->decide($token, array('ROLE_PLATFORM_DEPUTY_ADMIN')) ) {
            //exit('admin!');
            return true;
        }

        // if they can edit, they can view
        if( $this->canEdit($subject, $token) ) {

            //add if user has appropriate admin role: overwrite in the particular permission voter
            //check if approver with the same institution: compare subject->getInstitution() and user's approver role->getInstitution()
            //$user = $token->getUser();
            if( $this->hasApproverRoleInstitution($subject,$token) ) {
                return true;
            }

            //check for tentative pre-approval: use tentativeInstitution
            $tentative = true;
            if( $this->hasApproverRoleInstitution($subject,$token,$tentative) ) {
                return true;
            }
        }

        return false;
    }


    private function checkLocalPermission($subject, TokenInterface $token) {
        //check if owner
        $user = $token->getUser();
        if( !$user instanceof User ) {
            return false;
        }

        //author can see his request
        if( is_object($subject) ) {
            if( $subject->getUser()->getId() == $user->getId() ) {
                return true;
            }
        }

        //check if approver with the same institution: compare subject->getInstitution() and user's approver role->getInstitution()
        if( $this->hasApproverRoleInstitution($subject,$token) ) {
            return true;
        }

        return false;
    }

    private function hasApproverRoleInstitution( $subject, TokenInterface $token, $tentative=false ) {
        $user = $token->getUser();

        if( $tentative ) {
            $subjectInst = $subject->getTentativeInstitution();
        } else {
            $subjectInst = $subject->getInstitution();
        }

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
                $groupInstitutions = $vacreqUtil->getTentativeGroups($user,true);
            } else {
                $groupParams = array('asObject'=>true);
                $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
                $groupInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);
            }

            //check if subject has at least one of the $groupInstitutions
            foreach( $groupInstitutions as $inst ) {
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


