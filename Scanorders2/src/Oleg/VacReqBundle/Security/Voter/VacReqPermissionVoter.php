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
        if( $this->hasApproverRoleInstitution($subject,$user) ) {
            return true;
        }

        return false;
    }

    private function hasApproverRoleInstitution($subject,$user) {
        //get approver role for subject institution
        if( $subject->getInstitution() ) {
            $approverRoles = $this->em->getRepository('OlegUserdirectoryBundle:User')->
                findUserRolesBySiteAndPartialRoleName($user, "vacreq", "ROLE_VACREQ_APPROVER_", $subject->getInstitution()->getId() );
            if( count($approverRoles) > 0 ) {
                return true;
            }
        }
        return false;
    }


//    protected function canChangeStatus($subject, TokenInterface $token) {
//        return false;
//    }

}


