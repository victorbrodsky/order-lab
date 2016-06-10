<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 1/27/16
 * Time: 9:27 AM
 */

namespace Oleg\CallLogBundle\Security\Voter;


use Oleg\UserdirectoryBundle\Security\Voter\BaseRoleVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;



class CallLogRoleVoter extends BaseRoleVoter {

    protected function getSiteRoleBase() {
        return 'CALLLOG';
    }

    protected function getSitename() {
        return 'calllog';  //Site abbreviation i.e. fellapp, not fellowship-applications
    }


}


