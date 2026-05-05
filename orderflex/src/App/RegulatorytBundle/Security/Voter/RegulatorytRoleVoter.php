<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\RegulatorytBundle\Security\Voter;

use App\UserdirectoryBundle\Security\Voter\BaseRoleVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RegulatorytRoleVoter extends BaseRoleVoter {

    protected function getSiteRoleBase() {
        return 'REGULATORYT';
    }

    protected function getSitename() {
        return 'regulatoryt';
    }

    protected function canEdit($subject, TokenInterface $token) {
        return false;
    }

    protected function canChangeStatus($subject, TokenInterface $token) {
        return false;
    }
}
