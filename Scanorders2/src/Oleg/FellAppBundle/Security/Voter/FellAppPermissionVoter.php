<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 1/27/16
 * Time: 9:27 AM
 */

namespace Oleg\FellAppBundle\Security\Voter;


use Oleg\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class FellAppPermissionVoter extends BasePermissionVoter
{

    protected function getSiteRoleBase() {
        return 'FELLAPP';
    }

    protected function getSitename() {
        return 'fellapp';  //Site abbreviation i.e. fellapp, not fellowship-applications
    }


    protected function canView($subject, TokenInterface $token) {
        //exit('fellapp canView');

        if( parent::canView($subject,$token) ) {
            //exit('fellapp canView parent ok');
            return $this->fellappAdditionalCheck($subject,$token);
        }
        //exit('fellapp canView false');

        return false;
    }

    protected function canEdit($subject, TokenInterface $token) {
        //exit('fellapp canEdit');

        if( parent::canEdit($subject,$token) ) {
            return $this->fellappAdditionalCheck($subject,$token);
        }
        //exit('fellapp canEdit false');

        return false;
    }

    //additional check for fellapp permission to access this object: user is Observers or hasSameFellowshipTypeId
    public function fellappAdditionalCheck($subject,$token) {
        if( is_object($subject) ) {
            $user = $token->getUser();
            $fellappUtil = $this->container->get('fellapp_util');
            if ($fellappUtil->hasFellappPermission($user, $subject)) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }




//    protected function canView($subject, TokenInterface $token) {
//        //exit('fellapp canView');
//
//        if( parent::canView($subject,$token) ) {
//
//            //additional check for fellapp permission to access this object: user is Observers or hasSameFellowshipTypeId
//            if( is_object($subject) ) {
//                $user = $token->getUser();
//                $fellappUtil = $this->container->get('fellapp_util');
//                if ($fellappUtil->hasFellappPermission($user, $subject)) {
//                    //exit('fellapp canView true');
//                    return true;
//                } else {
//                    return false;
//                }
//            }
//
//            return true;
//        }
//        //exit('fellapp canView false');
//
//        return false;
//    }
}


