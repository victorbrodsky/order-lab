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


    protected function supports_ORIG($attribute, $subject) {

        if( $this->supportAttribute($attribute, $subject) ) {
            //exit("supported subject=".$subject."<br>");
            return true;
        }

        //echo "FellApp Supported subject=".$subject."<br>";
        return false;
    }


    protected function canView($subject, TokenInterface $token) {
        //exit('fellapp canView');

        if( is_object($subject) ) {
            $user = $token->getUser();

            $fellappUtil = $this->container->get('fellapp_util');
            if ($fellappUtil->hasFellappPermission($user, $subject)) {
                //exit('fellapp canView true');
                return true;
            }
        }

        if( !parent::canView($subject,$token) ) {
            return false;
        }
        //exit('fellapp canView false');

        return false;
    }

//    protected function preViewCheck($subject,$token) {
//        //exit('1');
//        if( $this->preEditCheck($subject,$token) ) {
//            return true;
//        }
//        return false;
//    }
//
//    protected function preEditCheck($subject,$token) {
//        //exit('2');
//        $user = $token->getUser();
//        $fellappUtil = $this->container->get('fellapp_util');
//        if( $fellappUtil->hasFellappPermission($user,$subject) ) {
//            return true;
//        }
//        return false;
//    }

}


