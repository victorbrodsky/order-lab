<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 1/27/16
 * Time: 9:27 AM
 */

namespace Oleg\UserdirectoryBundle\Security\Voter;


use Oleg\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;



class UserPermissionVoter extends BasePermissionVoter
{

    protected function getSiteRoleBase() {
        return 'USERDIRECTORY';
    }

    protected function getSitename() {
        return 'employees';     //Site abbreviation (i.e. fellapp), not site name (i.e. fellowship-applications)
    }

}


