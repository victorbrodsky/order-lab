<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 1/27/16
 * Time: 9:27 AM
 */

namespace Oleg\FellAppBundle\Security\Voter;


use Oleg\UserdirectoryBundle\Security\Voter\BaseRoleVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;



class FellAppRoleVoter extends BaseRoleVoter {

    protected function getSiteRoleBase() {
        return 'FELLAPP';
    }

    protected function getSitename() {
        return 'fellapp';  //Site abbreviation i.e. fellapp, not fellowship-applications
    }

}


