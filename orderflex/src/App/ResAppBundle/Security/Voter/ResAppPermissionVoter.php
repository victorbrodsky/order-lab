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

namespace App\ResAppBundle\Security\Voter;


use App\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class ResAppPermissionVoter extends BasePermissionVoter
{

    protected function getSiteRoleBase() {
        return 'RESAPP';
    }

    protected function getSitename() {
        return 'resapp';  //Site abbreviation i.e. resapp, not residency-applications
    }


    protected function canView($subject, TokenInterface $token) {
        //exit('resapp canView');

        //can view if user is an interviewer or observer
        if( is_object($subject) ) {
            if( $this->isObserverOrInterviewer($subject, $token) ) {
                return true;
            }
        }

        if( parent::canView($subject,$token) ) {
            //exit('resapp parent canView parent ok');
            return $this->resappAdditionalCheck($subject,$token);
            //return true;
        }
        //exit('resapp canView false');

        return false;
    }

    protected function canEdit($subject, TokenInterface $token) {
        //exit('resapp canEdit');

        if( parent::canEdit($subject,$token) ) {
            return $this->resappAdditionalCheck($subject,$token);
        }
        //exit('resapp canEdit false');

        return false;
    }

    //additional check for resapp permission to access this object: user is Observers/Interviewers or hasSameResidencyTypeId
    public function resappAdditionalCheck($subject,$token) {
        if( is_object($subject) ) {
            $user = $token->getUser();
            $resappUtil = $this->container->get('resapp_util');
            if ($resappUtil->hasResappPermission($user, $subject)) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    public function isObserverOrInterviewer($subject,$token) {
        if( is_object($subject) ) {
            $user = $token->getUser();

            //echo $subject->getId().": check if user is observer <br>";
            //if user is observer of this resapp
            if( $subject->getObservers()->contains($user) ) {
                //echo "user is observer!!! <br>";
                return true;
            }

            //echo $subject->getId().": check if user is interviewer <br>";
            //if user is interviewer of this resapp
            //if( $subject->getInterviews()->contains($user) ) {
            if( $subject->getInterviewByUser($user) ) {
                //echo "user is interviewer!!! <br>";
                return true;
            }
        }

        return false;
    }




//    protected function canView($subject, TokenInterface $token) {
//        //exit('resapp canView');
//
//        if( parent::canView($subject,$token) ) {
//
//            //additional check for resapp permission to access this object: user is Observers or hasSameResidencyTypeId
//            if( is_object($subject) ) {
//                $user = $token->getUser();
//                $resappUtil = $this->container->get('resapp_util');
//                if ($resappUtil->hasResappPermission($user, $subject)) {
//                    //exit('resapp canView true');
//                    return true;
//                } else {
//                    return false;
//                }
//            }
//
//            return true;
//        }
//        //exit('resapp canView false');
//
//        return false;
//    }
}


