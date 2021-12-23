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


use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

//Role Voter check for the role's permission based on the object-action: isGranted("read", "Accession") or isGranted("read", $accession)
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
        //return true; //testing

        //echo "attribute=$attribute, subject=$subject<br>";
        //attribute=ROLE_TRANSRES_ADMIN__COVID19, subject=
        //attribute=read, subject=1. Principle Investigators by Affiliation (linked) (ChartList)

        //echo "subjectId=".$subject->getId()."<br>";
        //$this->getId(); //testing: make error stack
        //exit('dashboard: support');

        //$siteRoleBase = $this->getSiteRoleBase();
        $sitename = $this->getSitename();

        $attribute = $this->convertAttribute($attribute);

        // if the attribute isn't one we support, return false
        if( !$this->supportAttribute($attribute, $subject) ) {
            return false;
        }

        //////////// check if the $subject (className string or object) is in PermissionObjectList ////////////
        //Don't use ChartList in PermissionObjectList
        //////////// EOF check if the $subject (className string or object) is in PermissionObjectList ////////////

        //attribute is action (read, edit ...) or role ROLE_DASHBOARD_...
        //check if attribute is dashboard role. if yes => support true
        if( strpos($attribute, 'ROLE_') !== false ) {
            //echo 'true';
            if( strpos($attribute, '_DASHBOARD_') !== false ) {
                //echo 'true';
                return true;
            } else {
                return false;
            }
        }

        //make dashboard support to work without PermissionObjectList
        //////////// check if the $subject (className string or object) is in PermissionObjectList ////////////
        //$permissionObjects = $this->em->getRepository('AppUserdirectoryBundle:User')->isUserHasPermissionObjectAction( $user, $className, "read" );
        if( $subject ) {
            $className = $this->getClassName($subject);

            //echo "className=".$className."<br>";
            //echo "sitename=".$sitename."<br>";

            //make dashboard support to work without PermissionObjectList
            if( $className == "ChartList" ) {
                return true;
            }

            $repository = $this->em->getRepository('AppUserdirectoryBundle:PermissionObjectList');
            $dql = $repository->createQueryBuilder("list");
            $dql->select('list');
            $dql->leftJoin('list.sites', 'sites');
            $dql->where("(list.name = :objectname OR list.abbreviation = :objectname) AND (sites.name = :sitename OR sites.abbreviation = :sitename)");
            $query = $this->em->createQuery($dql);

            $query->setParameters(
                array(
                    'objectname' => $className,
                    'sitename' => $sitename
                )
            );

            $permissionObjects = $query->getResult();
            //echo "permissionObjects count=".count($permissionObjects)."<br>";

            if (count($permissionObjects) > 0) {
                //exit('dashboard: support true');
                return true;
            } else {
                //exit('dashboard: support false');
                return false;
            }
        }
        //////////// EOF check if the $subject (className string or object) is in PermissionObjectList ////////////

        //exit('dashboard: support');
        //exit('dashboard: support true');

        //echo "Supported voter: attribute=".$attribute."; subject=".$subject."<br>";
        return true;
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
        //exit('dashboard canView');

        //return false;

        $user = $token->getUser();

        if( !$user instanceof User ) {
            //exit('dashboard canView: not User');
            return false;
        }

        if( !$subject ) {
            //exit('dashboard canView: not Subject');
            return false;
        }

        // if they can edit, they can view
//        if( $this->canEdit($subject, $token) ) {
//            //exit('can edit!');
//            //echo "Base canView: user can edit <br>";
//            return true;
//        }

        $siteRoleBase = $this->getSiteRoleBase();
        //$sitename = $this->getSitename();

        //denyUsers: if user is in denyUsers => return false;
//        if( $subject->getDenyUsers()->contains($user) ) {
//            //exit("chart has DenyUsers");
//            return false;
//        }

        //testing session bag
//        $this->container->get('session')->getFlashBag()->add(
//            'permissionwarning',
//            'Session bag testing.'
//        );
        //$this->setPermissionErrorSession($subject,"Session attribute testing");
        //return false;

        if( $this->userIsDeniedByChart($user,$subject) ) {
            //exit('userIsDeniedByChart!');
            return false;
        }
        if( $this->userIsDeniedByTopic($user,$subject) ) {
            //exit('userIsDeniedByTopic!');
            return false;
        }

        //denyRoles: if user has denyRoles => return false;
//        $denyRoles = $subject->getDenyRoles();
//        //$userRoles = $user->getSiteRoles('dashboard');
//        $securityUtil = $this->container->get('user_security_utility');
//        $userRoles = $securityUtil->getUserRolesBySite($user,$sitename);
//        foreach( $userRoles as $userRole ) {
//            if( $userRole && $denyRoles->contains($userRole) ) {
//                //exit("chart has DenyRoles");
//                return false;
//            }
//        }

        if( $this->userHasChartDenyRoles($user,$subject) || $this->userHasTopicDenyRoles($user,$subject) ) {
            //exit('user has deny role!');
            return false;
        }

        //ROLE_DASHBOARD_ADMIN can do anything
        if( $this->decisionManager->decide($token, array('ROLE_'.$siteRoleBase.'_ADMIN')) ) {
            //exit('admin!');
            return true; //remove for testing
        }
        //exit('dashboard canView false');

        //accessRoles: if user has accessRoles => return true;
        if( $this->userHasChartAccessRoles($user,$subject) || $this->userHasTopicAccessRoles($user,$subject) ) {
            //exit("chart has user access roles");
            return true;
        }

        //exit("chart can not View");
        return false;
    }

    public function userIsDeniedByChart($user,$chart) {
        //return true; //testing
        if( $chart->getDenyUsers()->contains($user) ) {
            //exit("chart has DenyUsers");
//            $this->container->get('session')->getFlashBag()->add(
//                'warning',
//                "User is denied by chart '" . $chart . "'."
//            );
            $this->setPermissionErrorSession($chart,"User is denied by chart '" . $chart . "'.");
            //dump($session = $this->container->get('session'));
            return true;
        }
        return false;
    }
    public function userIsDeniedByTopic($user,$chart) {
        foreach( $chart->getTopics() as $topic ) {
            if( $topic->getDenyUsers()->contains($user) ) {
                //exit("chart has DenyUsers");
//                $this->container->get('session')->getFlashBag()->add(
//                    'permissionwarning',
//                    "User is denied by topic '" . $topic . "'."
//                );
                $this->setPermissionErrorSession($chart,"User is denied by topic '" . $topic . "'.");
                return true;
            }
        }
        return false;
    }

    public function userHasChartDenyRoles($user,$chart) {
        $denyRoles = $chart->getDenyRoles();
        //$userRoles = $user->getSiteRoles('dashboard');
        $securityUtil = $this->container->get('user_security_utility');
        $userRoles = $securityUtil->getUserRolesBySite($user,$this->getSitename());
        foreach( $userRoles as $userRole ) {
            if( $userRole && $denyRoles->contains($userRole) ) {
                //exit("chart has DenyRoles [$userRole]: ".$userRole->getId());
//                $this->container->get('session')->getFlashBag()->add(
//                    'permissionwarning',
//                    "Role '$userRole' is denied by chart '" . $chart . "'."
//                );
                //$userRole = "ROLE_DASHBOARD_VICE_CHAIR_CLINICAL_PATHOLOGY";
                //$roleEntity = $this->em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($userRole);
                //$roleStr = $roleEntity."";
//                if( $roleEntity ) {
//                    $roleStr = $roleEntity->getAlias();
//                } else {
//                    $roleStr = "!!! Role not found by name [$userRole]!!!";
//                }
                $roleStr = $userRole->getAlias(); //$securityUtil->getRoleAliasByName($userRole);
                if( !$roleStr ) {
                    exit("Role not found by [$userRole]");
                    $roleStr = $userRole;
                }
                $this->setPermissionErrorSession($chart,"Role '$roleStr' is denied by chart '" . $chart . "'.");
                return true;
            }
        }
        return false;
    }
    public function userHasTopicDenyRoles($user,$chart) {
        foreach( $chart->getTopics() as $topic ) {
            $denyRoles = $topic->getDenyRoles();
            //$userRoles = $user->getSiteRoles('dashboard');
            $securityUtil = $this->container->get('user_security_utility');
            $userRoles = $securityUtil->getUserRolesBySite($user,$this->getSitename());
            foreach( $userRoles as $userRole ) {
                $userRole = trim($userRole);
                if( $userRole && $denyRoles->contains($userRole) ) {
                    //exit("chart has DenyRoles");
//                    $this->container->get('session')->getFlashBag()->add(
//                        'permissionwarning',
//                        "Role '$userRole' is denied by topic '" . $topic . "'."
//                    );
                    $role = $this->em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($userRole);
                    $roleStr = $userRole."";
                    if( $role ) {
                        $roleStr = $role->getAlias();
                    } else {
                        //$roleStr = "Role not found by name $userRole";
                    }
                    $this->setPermissionErrorSession($chart,"Role '$roleStr' is denied by topic '" . $topic . "'.");
                    return true;
                }
            }
        }
        return false;
    }


    public function userHasChartAccessRoles($user,$chart) {
        $securityUtil = $this->container->get('user_security_utility');

        $sitename = $this->getSitename();
        $userRolesId = $securityUtil->getUserRoleIdsBySite($user,$sitename);
        //dump($userRolesId);

        $repository = $this->em->getRepository('AppDashboardBundle:ChartList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');
        $dql->leftJoin("list.accessRoles", "accessRoles");
        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->andWhere("list.id = :chartId");
        $dql->andWhere("accessRoles IN (:userRoles)");

        $dql->orderBy("list.orderinlist","ASC");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
            'chartId' => $chart->getId(),
            'userRoles' => $userRolesId
        );

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();
        //echo "charts=".count($charts)."<br>";

        if( count($charts) > 0 ) {
            return true;
        }

        return false;
    }

    public function userHasTopicAccessRoles($user,$chart) {
        $securityUtil = $this->container->get('user_security_utility');

        $sitename = $this->getSitename();
        $userRolesId = $securityUtil->getUserRoleIdsBySite($user,$sitename);
        //dump($userRolesId);

        $repository = $this->em->getRepository('AppDashboardBundle:TopicList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');
        $dql->leftJoin("list.accessRoles", "accessRoles");
        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->andWhere("list.id = :chartId");
        $dql->andWhere("accessRoles IN (:userRoles)");

        $dql->orderBy("list.orderinlist","ASC");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
            'chartId' => $chart->getId(),
            'userRoles' => $userRolesId
        );

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $charts = $query->getResult();
        //echo "charts=".count($charts)."<br>";

        if( count($charts) > 0 ) {
            return true;
        }

        return false;
    }

    public function setPermissionErrorSession($chart,$error) {
        $session = $this->container->get('session');
        $session->set('permission-error-'.$chart->getId(), $error);
    }


    protected function canEdit($subject, TokenInterface $token) {
        //exit('dashboard canEdit');

        if( parent::canEdit($subject,$token) ) {
            return $this->dashboardAdditionalCheck($subject,$token);
        }
        //exit('dashboard canEdit false');

        //ROLE_DASHBOARD_ADMIN can do anything
        $siteRoleBase = $this->getSiteRoleBase();
        if( $this->decisionManager->decide($token, array('ROLE_'.$siteRoleBase.'_ADMIN')) ) {
            //exit('admin!');
            return true; //remove for testing
        }

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


