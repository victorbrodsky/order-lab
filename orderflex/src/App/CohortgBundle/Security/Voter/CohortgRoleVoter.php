<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\CohortgBundle\Security\Voter;

use App\UserdirectoryBundle\Security\Voter\BaseRoleVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CohortgRoleVoter extends BaseRoleVoter {

    protected function getSiteRoleBase() {
        return 'COHORTG';
    }

    protected function getSitename() {
        return 'cohortg';
    }

    protected function canEdit($subject, TokenInterface $token) {
        return false;
    }

    protected function canChangeStatus($subject, TokenInterface $token) {
        return false;
    }
}
