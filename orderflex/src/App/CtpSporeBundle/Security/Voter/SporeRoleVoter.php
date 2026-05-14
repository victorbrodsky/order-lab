<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\CtpSporeBundle\Security\Voter;

use App\UserdirectoryBundle\Security\Voter\BaseRoleVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SporeRoleVoter extends BaseRoleVoter {

    protected function getSiteRoleBase() {
        return 'CTPSPORE';
    }

    protected function getSitename() {
        return 'spore';
    }

    protected function canEdit($subject, TokenInterface $token) {
        return false;
    }

    protected function canChangeStatus($subject, TokenInterface $token) {
        return false;
    }
}
