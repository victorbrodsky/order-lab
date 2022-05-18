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

    protected function getSiteRoleBase() : string
    {
        return 'DASHBOARD';
    }

    protected function getSitename() : string
    {
        return 'dashboard';  //Site abbreviation
    }

    //isGranted("read", "Accession") or isGranted("read", $accession)
    //$attribute: string i.e. "read"
    //$subject: string (i.e. "FellowshipApplication") or entity
    protected function supports($attribute, $subject) : bool
    {
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
        if( strpos((string)$attribute, 'ROLE_') !== false ) {
            //echo 'true';
            if( strpos((string)$attribute, '_DASHBOARD_') !== false ) {
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

    //Can view according to chart's: accessRoles, denyRoles and denyUsers
    //usage: $this->get('security.authorization_checker')->isGranted('view', $chart)
    protected function canView($subject, TokenInterface $token) : bool
    {
        //exit('dashboard canView');

        //return true; //testing
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
        //TODO: test all below
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

    public function userIsDeniedByChart($user,$chart) : bool
    {
        //return true; //testing
        if( $chart->getDenyUsers()->contains($user) ) {
            //exit("chart has DenyUsers");
            $this->setPermissionErrorSession($chart,"User is denied by chart '" . $chart . "'.");
            //dump($session = $this->container->get('session'));
            return true;
        }
        return false;
    }
    public function userIsDeniedByTopic($user,$chart) : bool
    {
        foreach( $chart->getTopics() as $topic ) {
            if( $topic->getDenyUsers()->contains($user) ) {
                //exit("chart has DenyUsers");
                $this->setPermissionErrorSession($chart,"User is denied by topic '" . $topic . "'.");
                return true;
            }
            //TODO: check all parents
            if( $this->userIsDeniedByParentTopics($user,$chart,$topic) ) {
                //$error = "User is denied by topic's parent '" . $topic . "'.";
                //$this->setPermissionErrorSession($chart,$error);
                return true;
            }
        }
        return false;
    }
    public function userIsDeniedByParentTopics($user,$chart,$topic) : bool
    {
        //return true;
        return false;
    }

    public function userHasChartDenyRoles($user,$chart) : bool
    {
        $denyRoles = $chart->getDenyRoles();
        //$userRoles = $user->getSiteRoles('dashboard');
        $securityUtil = $this->container->get('user_security_utility');
        $userRoles = $securityUtil->getUserRolesBySite($user,$this->getSitename());
        foreach( $userRoles as $userRole ) {
            if( $userRole && $denyRoles->contains($userRole) ) {
                //exit("chart has DenyRoles [$userRole]: ".$userRole->getId());
                $roleStr = $userRole->getAlias(); //$securityUtil->getRoleAliasByName($userRole);
                if( !$roleStr ) {
                    //exit("Role not found by [$userRole]");
                    $roleStr = $userRole;
                }
                $this->setPermissionErrorSession($chart,"Role '$roleStr' is denied by chart '" . $chart . "'.");
                return true;
            }
        }
        return false;
    }
    public function userHasTopicDenyRoles($user,$chart) : bool
    {
        foreach( $chart->getTopics() as $topic ) {
            $denyRoles = $topic->getDenyRoles();
            //$userRoles = $user->getSiteRoles('dashboard');
            $securityUtil = $this->container->get('user_security_utility');
            $userRoles = $securityUtil->getUserRolesBySite($user,$this->getSitename());
            foreach( $userRoles as $userRole ) {
                if( $userRole && $denyRoles->contains($userRole) ) {
                    //exit("chart has DenyRoles");
                    $roleStr = $userRole->getAlias(); //$securityUtil->getRoleAliasByName($userRole);
                    if( !$roleStr ) {
                        //exit("Role not found by [$userRole]");
                        $roleStr = $userRole;
                    }
                    $this->setPermissionErrorSession($chart,"Role '$roleStr' is denied by topic '" . $topic . "'.");
                    return true;
                }
                //TODO: check all parents
            }
        }
        return false;
    }


    public function userHasChartAccessRoles($user,$chart) : bool
    {
        //return true; //testing

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

    public function userHasTopicAccessRoles($user,$chart) : bool
    {
        $securityUtil = $this->container->get('user_security_utility');

        $sitename = $this->getSitename();
        $userRolesId = $securityUtil->getUserRoleIdsBySite($user,$sitename);
        //dump($userRolesId);

        $repository = $this->em->getRepository('AppDashboardBundle:TopicList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->leftJoin("list.accessRoles", "accessRoles");
        $dql->leftJoin("list.charts", "charts");
        //$dql->leftJoin("list.parent", "parent");
        //$dql->leftJoin("list.parent", "parent");

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->andWhere("accessRoles IN (:userRoles)");

        //check if topic has this chart
        $dql->andWhere("charts.id = :chartId");

        $dql->orderBy("list.orderinlist","ASC");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
            'chartId' => $chart->getId(),
            'userRoles' => $userRolesId
        );

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $topics = $query->getResult();

        //echo "chart id=".$chart->getId()."<br>";
        //echo "topics=".count($topics)."<br>";

        if( count($topics) > 0 ) {
            return true;
        }

        return false;
    }

    public function setPermissionErrorSession($chart,$error) : void
    {
        //Use session to store error attribute
        $session = $this->container->get('session');
        $session->set('permission-error-'.$chart->getId(), $error);
    }


    protected function canEdit($subject, TokenInterface $token) : bool
    {
        //return true; //testing
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
    public function dashboardAdditionalCheck($subject,$token) : bool
    {
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


