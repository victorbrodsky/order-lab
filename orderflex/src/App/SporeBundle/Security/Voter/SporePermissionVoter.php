<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\SporeBundle\Security\Voter;

use App\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SporePermissionVoter extends BasePermissionVoter
{
    protected function getSiteRoleBase() : string
    {
        return 'SPORE';
    }

    protected function getSitename() : string
    {
        return 'spore';
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
