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

namespace App\FellAppBundle\Security\Voter;


use App\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class FellAppPermissionVoter extends BasePermissionVoter
{

    protected function getSiteRoleBase() : string
    {
        return 'FELLAPP';
    }

    protected function getSitename() : string
    {
        return 'fellapp';  //Site abbreviation i.e. fellapp, not fellowship-applications
    }


    protected function canView($subject, TokenInterface $token) : bool
    {
        //exit('fellapp canView');

        //can view if user is an interviewer or observer
        if( is_object($subject) ) {
            if( $this->isObserverOrInterviewer($subject, $token) ) {
                return true;
            }

            //if subject (FellowshipApplication) has user == $token
            if( $this->isOwner($subject, $token) ) {
                return true;
            }
        }

        //testing: if can edit than can view: it's already done in the parent class
//        if( $this->canEdit($subject,$token) ) {
//            return true;
//        }
        //exit('fellapp no canEdit');

        //TODO: how to deal with ROLE_FELLAPP_PUBLIC_SUBMITTER?
        //if (in_array('ROLE_FELLAPP_PUBLIC_SUBMITTER', $token->getRoleNames(), true)) {
        //    return true;
        //}


        if( parent::canView($subject,$token) ) {
            //exit('fellapp parent canView parent ok'); //testing exit
            return $this->fellappAdditionalCheck($subject,$token);
            //return true;
        }
        //exit('fellapp canView false'); //testing exit

        return false;
    }

    protected function canEdit($subject, TokenInterface $token) : bool
    {
        //exit('fellapp canEdit');

        if( $this->isOwner($subject, $token) ) {
            return true;
        }

        if( parent::canEdit($subject,$token) ) {
            //exit('fellapp parent canEdit');
            return $this->fellappAdditionalCheck($subject,$token);
        } else {
            //exit('fellapp parent canEdit NO');
        }

        //exit('fellapp canEdit false');

        return false;
    }

    //additional check for fellapp permission to access this object: user is Observers/Interviewers or hasSameFellowshipTypeId
    public function fellappAdditionalCheck($subject,$token) : bool
    {
        if( is_object($subject) ) {
            $user = $token->getUser();
            $fellappUtil = $this->container->get('fellapp_util');
            if ($fellappUtil->hasFellappPermission($user, $subject)) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    public function isObserverOrInterviewer($subject,$token) : bool
    {
        if( is_object($subject) ) {
            $user = $token->getUser();

            //echo $subject->getId().": check if user is observer <br>";
            //if user is observer of this fellapp
            if( $subject->getObservers()->contains($user) ) {
                //echo "user is observer!!! <br>";
                return true;
            }

            //echo $subject->getId().": check if user is interviewer <br>";
            //if user is interviewer of this fellapp
            //if( $subject->getInterviews()->contains($user) ) {
            if( $subject->getInterviewByUser($user) ) {
                //echo "user is interviewer!!! <br>";
                return true;
            }
        }

        return false;
    }

    public function isOwner($subject,$token) {
        //if subject (FellowshipApplication) has user == $token
        $applicant = $subject->getUser();
        if( $applicant ) {
            $user = $token->getUser();
            if( $user && is_object($user) ) {
                if( $applicant->getId() && $user->getId() && $applicant->getId() === $user->getId() ) {
                    return true;
                }
            }
        }
        return false;
    }


//    protected function canView($subject, TokenInterface $token) {
//        //exit('fellapp canView');
//
//        if( parent::canView($subject,$token) ) {
//
//            //additional check for fellapp permission to access this object: user is Observers or hasSameFellowshipTypeId
//            if( is_object($subject) ) {
//                $user = $token->getUser();
//                $fellappUtil = $this->container->get('fellapp_util');
//                if ($fellappUtil->hasFellappPermission($user, $subject)) {
//                    //exit('fellapp canView true');
//                    return true;
//                } else {
//                    return false;
//                }
//            }
//
//            return true;
//        }
//        //exit('fellapp canView false');
//
//        return false;
//    }
}


