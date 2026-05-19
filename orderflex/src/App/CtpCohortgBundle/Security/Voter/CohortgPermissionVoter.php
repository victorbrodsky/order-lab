<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\CtpCohortgBundle\Security\Voter;

use App\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CohortgPermissionVoter extends BasePermissionVoter
{
    protected function getSiteRoleBase() : string
    {
        return 'COHORTG';
    }

    protected function getSitename() : string
    {
        return 'cohortg';
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
