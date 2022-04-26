<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 1/27/16
 * Time: 9:27 AM
 */

namespace App\TranslationalResearchBundle\Security\Voter;


//use App\OrderformBundle\Security\Voter\PatientHierarchyVoter;
use App\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
//use Symfony\Component\Security\Core\User\UserInterface;
//use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

use App\UserdirectoryBundle\Entity\User;



class TranslationalResearchPermissionVoter extends BasePermissionVoter //BasePermissionVoter   //PatientHierarchyVoter
{

    protected function getSiteRoleBase() : string
    {
        return 'TRANSRES';
    }

    protected function getSitename() : string
    {
        return 'translationalresearch';  //Site abbreviation i.e. fellapp, not fellowship-applications
    }


}


