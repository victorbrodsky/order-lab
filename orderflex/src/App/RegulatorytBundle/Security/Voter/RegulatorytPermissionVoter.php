<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\RegulatorytBundle\Security\Voter;

use App\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RegulatorytPermissionVoter extends BasePermissionVoter
{
    protected function getSiteRoleBase() : string
    {
        return 'REGULATORYT';
    }

    protected function getSitename() : string
    {
        return 'regulatoryt';
    }

    protected function canEdit($subject, TokenInterface $token) : bool
    {
        return false;
    }

    protected function canChangeStatus($subject, TokenInterface $token) : bool
    {
        return false;
    }
}
