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
 * Date: 10/8/2021
 * Time: 12:12 PM
 */

namespace App\DashboardBundle\Security\Voter;


use App\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class DashboardPermissionVoter extends BasePermissionVoter
{

    protected function getSiteRoleBase() {
        return 'DASHBOARD';
    }

    protected function getSitename() {
        return 'dashboard';  //Site abbreviation
    }

    //isGranted("read", "Accession") or isGranted("read", $accession)
    //$attribute: string i.e. "read"
    //$subject: string (i.e. "FellowshipApplication") or entity
    protected function supports($attribute, $subject) {
        //return false; //testing
        exit('dashboard: support');

        //$siteRoleBase = $this->getSiteRoleBase();
        //$sitename = $this->getSitename();

        $attribute = $this->convertAttribute($attribute);

        // if the attribute isn't one we support, return false
        if( !$this->supportAttribute($attribute, $subject) ) {
            return false;
        }

        //////////// check if the $subject (className string or object) is in PermissionObjectList ////////////
        //Don't use ChartList in PermissionObjectList
        //////////// EOF check if the $subject (className string or object) is in PermissionObjectList ////////////

        //echo "Supported voter: attribute=".$attribute."; subject=".$subject."<br>";
        return true;
    }

    protected function usePermissionObjectList() {
        echo "dashboard: usePermissionObjectList <br>";
        return false;
    }

//    protected function canView_ORIG($subject, TokenInterface $token) {
//        //exit('dashboard canView');
//
//        if( parent::canView($subject,$token) ) {
//            //exit('dashboard parent canView parent ok');
//            return $this->dashboardAdditionalCheck($subject,$token);
//            //return true;
//        }
//        //exit('dashboard canView false');
//
//        return false;
//    }
    //Can view according to chart's: accessRoles, denyRoles and denyUsers
    //usage: $this->get('security.authorization_checker')->isGranted('view', $chart)
    protected function canView($subject, TokenInterface $token) {
        exit('dashboard canView');

        $user = $token->getUser();

        if( !$user instanceof User ) {
            return false;
        }

        // if they can edit, they can view
        if( $this->canEdit($subject, $token) ) {
            //echo "Base canView: user can edit <br>";
            return true;
        }

        $siteRoleBase = $this->getSiteRoleBase();
        $sitename = $this->getSitename();

        //ROLE_DASHBOARD_ADMIN can do anything
        if( $this->decisionManager->decide($token, array('ROLE_'.$siteRoleBase.'_ADMIN')) ) {
            //exit('admin!');
            return true;
        }
        //exit('dashboard canView false');

        //denyRoles: if user has denyRoles => return false;

        //denyUsers: if user is in denyUsers => return false;

        //accessRoles: if user has accessRoles => return true;

        return false;
    }

    protected function canEdit($subject, TokenInterface $token) {
        //exit('dashboard canEdit');

        if( parent::canEdit($subject,$token) ) {
            return $this->dashboardAdditionalCheck($subject,$token);
        }
        //exit('dashboard canEdit false');

        return false;
    }

    //additional check for dashboard permission
    public function dashboardAdditionalCheck($subject,$token) {
        return true;

        if( is_object($subject) ) {
            //exit('is_object($subject)');
            $user = $token->getUser();
            $dashboardUtil = $this->container->get('dashboard_util');
            if ($dashboardUtil->hasDashboardPermission($user, $subject)) {
                return true;
            } else {
                return false;
            }
        }
        //exit('no is_object($subject)');

        return true;
    }







}


