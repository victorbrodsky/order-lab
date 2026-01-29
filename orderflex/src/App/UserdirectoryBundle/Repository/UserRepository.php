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

namespace App\UserdirectoryBundle\Repository;



use App\UserdirectoryBundle\Entity\BaseUserAttributes;
use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\UserdirectoryBundle\Entity\Permission;
use App\UserdirectoryBundle\Entity\Roles;
use App\UserdirectoryBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;


class UserRepository extends EntityRepository {


    public function findAllByInstitutionNodeAsUserArray( $nodeid, $onlyWorking=false ) {

        $users = $this->findAllByInstitutionNode($nodeid,$onlyWorking);
        $output = $this->convertUsersToArray($users,$nodeid);

        return $output;
    }

    public function findAllByInstitutionNode( $nodeid, $onlyWorking=false ) {

        $query = $this->_em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:User', 'user')
            ->from(User::class, 'user')
            ->select("user")
            ->groupBy('user');


        $query->orderBy("user.primaryPublicUserId","ASC");
        $query->leftJoin("user.administrativeTitles", "administrativeTitles");
        $query->leftJoin("user.appointmentTitles", "appointmentTitles");
        $query->leftJoin("user.medicalTitles", "medicalTitles");
        $query->leftJoin("user.researchLabs", "researchLabs");
        $query->where("administrativeTitles.institution = :nodeid OR appointmentTitles.institution = :nodeid OR medicalTitles.institution = :nodeid");
        $query->orWhere("researchLabs.institution = :nodeid");
        $query->setParameters( array("nodeid"=>$nodeid) );

        if( $onlyWorking ) {
            $curdate = date("Y-m-d", time());
            $query->leftJoin("user.employmentStatus", "employmentStatus");
            $currentusers = "employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."'";
            $query->andWhere($currentusers);
        }

        $users = $query->getQuery()->getResult();

        return $users;
    }


    public function convertUsersToArray( $users, $nodeid ) {

        $output = array();
        foreach( $users as $user ) {

            $userStr = $user->getUsernameShortest();

            $phoneArr = array();
            foreach( $user->getAllPhones() as $phone ) {
                $phoneArr[] = $phone['prefix'] . $phone['phone'];
            }
            if( count($phoneArr) > 0 ) {
                $userStr = $userStr . " " . implode(", ", $phoneArr);
            }

            $emailArr = array();
            foreach( $user->getAllEmail() as $email ) {
                $emailArr[] = $email['prefix'] . $email['email'];
            }
            if( count($emailArr) > 0 ) {
                $userStr = $userStr . " " . implode(", ", $emailArr);
            }

            $element = array(
                //'id' => 'addnodeid-'.$user->getId(),
                'id' => 'addnodeid'.$nodeid.'-'.$user->getId(),
                'addnodeid' => $user->getId(),
                'text' => $userStr,         //$user."",
                'type' => 'iconUser',
            );
            $output[] = $element;

        }//foreach

        return $output;
    }


    //Castro Martinez, Mario A: lastName, firstName
    public function findOneByNameStr( $nameStr, $orAnd="OR" ) {

        $user = null;

        $nameStrArr = explode(",",$nameStr);

        $lastName = trim((string)$nameStrArr[0]);
        $firstName = trim((string)$nameStrArr[1]);

        $query = $this->_em->createQueryBuilder()
            ->from(User::class, 'user')
            ->select("user");

        $query->leftJoin("user.infos", "infos");

        $query->where("infos.firstName = :firstName ".$orAnd." infos.lastName = :lastName");
        $query->setParameters( array("firstName"=>$firstName, "lastName"=>$lastName) );

        $users = $query->getQuery()->getResult();

        if( count($users) > 0 ) {
            $user = $users[0];
        }

        return $user;
    }

    //$nameStr is "Castro Martinez" or "Martinez Castro"
    public function findOneByAnyNameStr( $nameStr ) {

        $user = null;

        $nameStr = trim((string)$nameStr);
        $nameStrArr = explode(" ",$nameStr);

        $firstName = trim((string)$nameStrArr[0]);
        $lastName = trim((string)$nameStrArr[1]);

        $user = $this->findOneByFirstOrLastNameStr($lastName);

        if( !$user ) {
            $user = $this->findOneByFirstOrLastNameStr($firstName);
        }

        if( !$user ) {
            $user = $this->findOneByFirstOrLastNameStr($nameStr);
        }

        return $user;
    }
    public function findOneByFirstOrLastNameStr( $nameStr, $orAnd="OR" ) {

        //echo "findOneByFirstOrLastNameStr: nameStr=[".$nameStr."]<br>";

        $user = null;

        $query = $this->_em->createQueryBuilder()
            ->from(User::class, 'user')
            ->select("user");

        $query->leftJoin("user.infos", "infos");

        $query->where("infos.firstName = :firstName ".$orAnd." infos.lastName = :lastName");
        $query->setParameters( array("firstName"=>$nameStr, "lastName"=>$nameStr) );

        $users = $query->getQuery()->getResult();

        if( count($users) > 0 ) {
            $user = $users[0];
        }
        //echo "User=".$user."<br>";

        return $user;
    }

    public function findUserByUserInfoEmail( $email ) {
        //echo "email=".$email."<br>";
        $query = $this->_em->createQueryBuilder()
            ->from(User::class, 'user')
            ->select("user")
            ->leftJoin("user.infos","infos")
            ->where("infos.email = :userInfoEmail OR infos.emailCanonical = :userInfoEmail")
            ->orderBy("user.id","ASC")
            ->setParameter('userInfoEmail', $email)
        ;

        return $query->getQuery()->getResult();
    }

    public function findUsersByUserName(string $userName): array
    {
        return $this->createQueryBuilder('u')
            //->join('u.infos', 'i')
            ->andWhere('u.userName = :userName')
            ->setParameter('userName', $userName)
            ->getQuery()
            ->getResult();
    }

    public function findOneUserByUserInfoEmail( $email ) {
        $user = NULL;
        $users = $this->findUserByUserInfoEmail($email);
        if( count($users) > 0 ) {
            $user = $users[0];
        }
        return $user;
    }

    //find user by both: primaryPublicUserId and email
    public function findOneUserByUserInfoUseridEmail( $email ) {
        $user = NULL;

        $domain = explode('@', $email);
        $cwid = $domain[0];

        if( $email &&  $cwid ) {
            //ok continue
        } else {
            return $user;
        }

        $query = $this->_em->createQueryBuilder()
            ->from(User::class, 'user')
            ->select("user")
            ->leftJoin("user.infos","infos")
            ->where('user.primaryPublicUserId = :primaryPublicUserId')
            ->andWhere("(infos.email = :userInfoEmail OR infos.emailCanonical = :userInfoEmail)")
            ->orderBy("user.id","ASC") //Use the last created
            //->orderBy("user.lastActivity","ASC")
            //->setParameter('userInfoEmail', $email)
            ->setParameters( array(
                'userInfoEmail' => $email,
                'primaryPublicUserId' => $cwid,
            ))
        ;

        $users = $query->getQuery()->getResult();
        //echo "users=".count($users)."<br>";

        //Check if email address matches two or more user records.
        // In this case, choose a user with the latest login timestamp, if timestamp is empty, use DB latest user ID
        //However: users[0] will by the user with lastActivity is NULL, therefore, use user id (the last created user)
        if( count($users) > 0 ) {
            $user = $users[0];
        }

        return $user;
    }

    public function findOneUserByEmail( $email ) {
        $user = NULL;

        if( !$email ) {
            return $user;
        }

        $query = $this->_em->createQueryBuilder()
            ->from(User::class, 'user')
            ->select("user")
            ->leftJoin("user.infos","infos")
            ->where("(infos.email = :userInfoEmail OR infos.emailCanonical = :userInfoEmail)")
            ->orderBy("user.id","ASC") //Use the last created
            //->orderBy("user.lastActivity","DESC")
            //->setParameter('userInfoEmail', $email)
            ->setParameters( array(
                'userInfoEmail' => $email
            ))
        ;

        $users = $query->getQuery()->getResult();

//        echo "users=".count($users)."<br>";
//        foreach ($users as $user) {
//            $lastActivity = $user->getLastActivity();
//            $lastActivityStr = "N/A";
//            if( $lastActivity ) {
//                $lastActivityStr = $lastActivity->format('d-m-Y H:i:s');
//            }
//            echo "ID=".$user->getId().": user=".$user."; latsActive=".$lastActivityStr."<br>";
//        }

        if( count($users) > 0 ) {
            $user = $users[0];
        }

        return $user;
    }

    public function findOneUserByRole($role) {

        $user = null;

        $users = $this->findUserByRole($role);

        if( count($users) > 0 ) {
            $user = $users[0];
        }

        return $user;
    }

    public function findUserByRole( $role, $orderBy="user.id", $onlyWorking=false ) {

        //$user = null;
        //exit("findUserByRole");
        //echo "role=".$role."<br>";

        $query = $this->_em->createQueryBuilder()
            ->from(User::class, 'user')
            ->select("user")
            ->leftJoin("user.infos","infos")
            ->where("user.roles LIKE :role")
            ->orderBy($orderBy,"ASC")
            ->setParameter('role', '%"' . $role . '"%'); //'%"' will select exact match "ROLE_NAME"
            //->setParameter('role', '%' . $role . '%') //'%' will search a partial match ('Cytopathology part-time' and 'Cytopathology')
        ;

        if( $onlyWorking ) {
            $curdate = date("Y-m-d", time());
            $query->leftJoin("user.employmentStatus", "employmentStatus");
            $currentusers = "employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."'";
            $query->andWhere($currentusers);
        }

        return $query->getQuery()->getResult();
    }

    //$roles: role or partial role name
    public function findUsersByRoles($roles) {

        $whereArr = array();
        foreach($roles as $role) {
            //$whereArr[] = 'u.roles LIKE '."'%\"" . $role . "\"%'";
            $whereArr[] = 'u.roles LIKE '."'%" . $role . "%'";
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from(User::class, 'u')
            ->where( implode(' OR ',$whereArr) );

        //echo "query=".$qb."<br>";

        return $qb->getQuery()->getResult();
    }

    public function isUserHasPermissionObjectAction( $user, $object, $action ) {

        //check if user has direct permission
        $permissions = $this->isUserHasDirectPermissionObjectAction( $user, $object, $action );
        if( $permissions && count($permissions) > 0 ) {
            //echo "isUserHasDirectPermissionObjectAction!!! object=".$object."<br>";
            return true;
        }

        //check if user's roles have permission
        $atLeastOne = true;
        $roles = $this->findUserRolesByObjectAction($user, $object, $action, $atLeastOne );
        //echo "findUserRolesByObjectAction roles=".count($roles)."<br>";

        if( count($roles) > 0 ) {
            //echo "findUserRolesByObjectAction!!! object=".$object."<br>";
            return true;
        }

        return false;
    }

    //check if user has direct permission
    public function isUserHasDirectPermissionObjectAction( $user, $object, $action ) {

        $query = $this->_em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:Permission', 'permissions')
            ->from(Permission::class, 'permissions')
            ->select("permissions")
            ->leftJoin("permissions.user","user")
            ->leftJoin("permissions.permission","permission")
            ->leftJoin("permission.permissionObjectList","permissionObjectList")
            ->leftJoin("permission.permissionActionList","permissionActionList")
            ->where("user.id = :user AND (permissionObjectList.name = :permissionObject OR permissionObjectList.abbreviation = :permissionObject) AND permissionActionList.name = :permissionAction")
            ->orderBy("permissions.id","ASC")
            ->setParameters( array(
                'user' => $user->getId(),
                'permissionObject' => $object,
                'permissionAction' => $action
            ));
        //->setParameter('permissionAction', $action);

        //echo "sql=".$query->getQuery()->getSql()."<br>";

        $permissions = $query->getQuery()->getResult();

        return $permissions;
    }

    public function findUserRolesByObjectAction($user, $object, $action, $atLeastOne=true) {

        $userRoles = new ArrayCollection();

        //get all roles with corresponding permissions: object-action
        $roles = $this->findRolesByObjectAction($object, $action);
        //echo "roles count=".count($roles)."<br>";
        //exit('exit');

        //check if user has one of roles
        foreach( $roles as $role ) {
            //echo $action.": role=".$role."<br>";
            if( $user->hasRole($role) ) {
                if( $role && !$userRoles->contains($role) ) {
                    $userRoles->add($role);
                }

                if( $atLeastOne ) {
                    return $userRoles;
                }
            }
        }

        return $userRoles;
    }

    //get all roles with corresponding permissions: object-action
    public function findRolesByObjectAction($object, $action) {

        //echo "find RolesByObjectAction: object=".$object."; action=".$action."<br>";

        //check if user's roles have permission
        $query = $this->_em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:Roles', 'list')
            ->from(Roles::class, 'list')
            ->select("list")
            ->leftJoin("list.permissions","permissions")
            ->leftJoin("permissions.permission","permission")
            ->leftJoin("permission.permissionObjectList","permissionObjectList")
            ->leftJoin("permission.permissionActionList","permissionActionList")
            ->where("(permissionObjectList.name = :permissionObject OR permissionObjectList.abbreviation = :permissionObject) AND permissionActionList.name = :permissionAction")
            ->orderBy("list.id","ASC")
            ->setParameters( array(
                'permissionObject' => $object,
                'permissionAction' => $action
            ));
        //->setParameter('permissionAction', $action);

        //echo "sql=".$query->getQuery()->getSql()."<br>";

        $roles = $query->getQuery()->getResult();
        //echo "roles count=".count($roles)."<br>";
        //exit('exit');

        return $roles;
    }

    //get all roles with corresponding permissions: object-action
    public function findRolesByObjectActionInstitutionSite($objectStr, $actionStr, $institutionId, $sitename, $roleName=null, $sortBy=null) {

        //check if user's roles have permission
        //$query = $this->_em->createQueryBuilder()->from('AppUserdirectoryBundle:Roles', 'list');
        $query = $this->_em->createQueryBuilder()->from(Roles::class, 'list');
        $query->select("list");

        $query->leftJoin("list.permissions","permissions");
        $query->leftJoin("permissions.permission","permission");
        $query->leftJoin("permission.permissionObjectList","permissionObjectList");
        $query->leftJoin("permission.permissionActionList","permissionActionList");
        $query->leftJoin("list.institution","institution");

        $query->where("permissionActionList.name = :permissionActionStr OR permissionActionList.abbreviation = :permissionActionStr");
        $query->andWhere("permissionObjectList.name = :permissionObjectStr OR permissionObjectList.abbreviation = :permissionObjectStr");

        $parameters = array(
            'permissionObjectStr' => $objectStr,
            'permissionActionStr' => $actionStr
        );

        if( $institutionId ) {
            //$query->leftJoin("list.institution","institution");
            $institution = $this->_em->getRepository(Institution::class)->find($institutionId);
            //echo "institution=".$institution->getNodeNameWithRoot()."<br>";
            //get inst criterion string tree with collaboration
            //$instStr = $this->_em->getRepository('AppUserdirectoryBundle:Institution')->
            //        getCriterionStrForCollaborationsByNode($institution,"institution",array("Intersection"),false,false);
            //get simple inst criterion string tree (without collaboration)
            $instStr = $this->_em->getRepository(Institution::class)->selectNodesUnderParentNode($institution,"institution",false);
            //echo "instStr=".$instStr."<br>";
            $query->andWhere($instStr);

        }

        if( $sitename ) {
            $query->leftJoin("list.sites","sites");
            $query->andWhere("sites.name = :sitename OR sites.abbreviation = :sitename");
            $parameters['sitename'] = $sitename;
        }

        if( $roleName ) {
            $query->andWhere("list.name = :roleName OR sites.abbreviation = :roleName");
            $parameters['roleName'] = $roleName;
        }

        //print_r($parameters);

        if( $sortBy === null ) {
            $query->orderBy("list.id","ASC");
            //$sortBy = "list.id";
            //$query->orderBy($sortBy,"ASC");
        } else {
            $entity = $sortBy[0];
            $field = $sortBy[1];
            $direction = $sortBy[2];
            $query->orderBy($entity.'.'.$field,$direction);
        }

        $query->setParameters( $parameters);

        //echo "sql=".$query."<br>";

        $roles = $query->getQuery()->getResult();
        //echo "roles count=".count($roles)."<br>";

        //foreach( $roles as $role ) {
            //echo "role=".$role."<br>";
        //}
        //exit('exit');

        return $roles;
    }

    public function isUserHasSiteAndPartialRoleName( $user, $sitename, $rolePartialName, $institutionId=null ) {
        $userRoles = $this->findUserRolesBySiteAndPartialRoleName($user, $sitename, $rolePartialName, $institutionId);
        if( count($userRoles) > 0 ) {
            return true;
        }
        return false;
    }

    //method findUserRolesBySitePermissionObjectAction gets the same roles but appropriate input permissions
    //find user roles with exact $institutionId
    public function findUserRolesBySiteAndPartialRoleName( $user, $sitename, $rolePartialName, $institutionId=null, $atLeastOne=true ) {

        $userRoles = new ArrayCollection();

        $roles = $this->findRolesBySiteAndPartialRoleName( $sitename, $rolePartialName, $institutionId );

        //echo "roles count=".count($roles)."<br>";
        //exit('exit');

        //check if user has one of roles
        foreach( $roles as $role ) {
            //echo "role=".$role."<br>";
            if( $user->hasRole($role) ) {
                $userRoles->add($role);

                if( $atLeastOne ) {
                    return $userRoles;
                }
            }
        }

        return $userRoles;
    }

    //find user roles specified by sitename, objectStr, actionStr and with institution equal to institutuionId or with instition children roles
    public function findUserRolesBySitePermissionObjectAction( $user, $sitename, $objectStr, $actionStr, $institutionId=null ) {

        $userRoles = new ArrayCollection();

        $roleNames = $user->getRoles();

        foreach( $roleNames as $roleName ) {

            $roles = $this->findRolesByObjectActionInstitutionSite($objectStr, $actionStr, $institutionId, $sitename, $roleName);

            foreach( $roles as $role ) {

                if( $role && !$userRoles->contains($role) ) {
                    $userRoles->add($role);
                }

            }
        }

        return $userRoles;
    }
    //find user roles with child roles specified by sitename, objectStr, actionStr
    public function findUserChildRolesBySitePermissionObjectAction( $user, $sitename, $objectStr, $actionStr ) {

        $userRoles = new ArrayCollection();

        $roleNames = $user->getRoles();

        foreach( $roleNames as $roleName ) {

            //find user role object (i.e. ROLE_VACREQ_SUPERVISOR_WCM_PATHOLOGY)
            $roles = $this->findRolesByObjectActionInstitutionSite($objectStr, $actionStr, null, $sitename, $roleName);

            foreach( $roles as $role ) {
                //echo "###role=".$role."<br>";

                $childRoles = $this->findRolesByObjectActionInstitutionSite($objectStr, $actionStr, $role->getInstitution(), $sitename, null);

                foreach( $childRoles as $childRole ) {

                    if( $childRole && !$userRoles->contains($childRole) ) {
                        $userRoles->add($childRole);
                    }

                }//foreach userRole objects

            }//foreach

        }//foreach userRoles

        return $userRoles;
    }
    //find user parent roles specified by sitename, objectStr, actionStr:
    //ROLE_VACREQ_SUPERVISOR_WCM_PATHOLOGY is a parent role for ROLE_VACREQ_SUBMITTER_CLINICALPATHOLOGY because CLINICALPATHOLOGY is under WCMC_PATHOLOGY
    public function findUserParentRolesBySitePermissionObjectAction( $user, $sitename, $parentObjectStr, $parentActionStr, $childObjectStr, $childActionStr ) {

        $userParentRoles = new ArrayCollection();

        //find this user roles
        //echo "testing: childActionStr=".$childActionStr."<br>";
        $userRoles = $this->findUserRolesBySitePermissionObjectAction($user,$sitename,$childObjectStr,$childActionStr);
        //echo "userRole count=".count($userRoles)."<br>";
//        foreach( $userRoles as $userRole ) {
//            //echo "testing: userRole=".$userRole."<br>";
//        }

        //find parent roles
//        //echo "testing: parentActionStr=".$parentActionStr."<br>";
        $parentRoles = $this->findRolesByObjectActionInstitutionSite($parentObjectStr,$parentActionStr,null,$sitename);
        //echo "parentRoles=".count($parentRoles)."<br>";
//        foreach( $parentRoles as $parentRole ) {
//            //echo "testing: parentRoles=".$parentRole."<br>";
//        }

        foreach( $parentRoles as $parentRole ) {
            //check if the $userRoles is under $parentRole
            foreach( $userRoles as $userRole ) {
                //echo "parentRole=".$parentRole."; userRole=".$userRole."<br>";
                //$nodeUnderParent = $this->_em->getRepository('AppUserdirectoryBundle:Institution')->isNodeUnderParentnode($parentRole->getInstitution(), $userRole->getInstitution());
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                $nodeUnderParent = $this->_em->getRepository(Institution::class)->isNodeUnderCollaborationParentnode($parentRole->getInstitution(), $userRole->getInstitution());
                if( $nodeUnderParent ) {
                    if( $parentRole && !$userParentRoles->contains($parentRole) ) {
                        $userParentRoles->add($parentRole);
                    }
                }
            }
        }

//        foreach( $userParentRoles as $userParentRole ) {
//            //echo "testing: userParentRole=".$userParentRole."<br>";
//        }

        return $userParentRoles;
    }

    public function findRolesBySiteAndPartialRoleName( $sitename, $rolePartialName, $institutionId=null, $statusArr=array() ) {

        $parameters = array(
            'sitename' => $sitename,
            'roleName' => '%' . $rolePartialName . '%'
        );

        //check if user's roles have permission
        $query = $this->_em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:Roles', 'list')
            ->from(Roles::class, 'list')
            ->select("list")
            ->leftJoin("list.sites","sites");

        $query->where("list.name LIKE :roleName AND (sites.name = :sitename OR sites.abbreviation = :sitename)");

        if( $institutionId ) {
            $query->andWhere("list.institution = :institutionId");
            $parameters['institutionId'] = $institutionId;
        }

        if( $statusArr && count($statusArr)>0 ) {
            $statusCriterionArr = array();
            foreach( $statusArr as $status ) {
                $statusCriterionArr[] = "list.type = '".$status."'";
            }
            $statusCriterion = "(".implode(" OR ",$statusCriterionArr).")";
            //echo "statusCriterion=[$statusCriterion]<br>";
            $query->andWhere($statusCriterion);
        }

        $query->orderBy("list.id","ASC");

        $query->setParameters($parameters);

        //echo "sql=".$query->getQuery()->getSql()."<br>";

        $roles = $query->getQuery()->getResult();

        return $roles;
    }

    //find users by roles specified by sitename, objectStr, actionStr and with institution equal to institutuionId or with instition children roles
    public function findUsersBySitePermissionObjectActionInstitution( $sitename, $objectStr, $actionStr, $institutionId, $onlyWorking=false ) {

        $roles = $this->findRolesByObjectActionInstitutionSite($objectStr, $actionStr, $institutionId, $sitename);

        //construct with "user.roles LIKE '%ROLE_VACREQ_SUBMITTER_CLINICALPATHOLOGY%'"
        $withLikes = array();
        foreach( $roles as $role ) {
            $withLikes[] = "user.roles LIKE '%".$role->getName()."%'";
        }
        $withLikesStr = implode(" OR ", $withLikes);
        //echo "withLikesStr=".$withLikesStr."<br>";

        $query = $this->_em->createQueryBuilder()->from(User::class, 'user');
        $query->select("user");

        //$query->leftJoin("AppUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE '%ROLE_VACREQ_SUBMITTER_CLINICALPATHOLOGY%'");
        //$query->leftJoin("AppUserdirectoryBundle:Roles", "roles", "WITH", $withLikesStr);

        $query->where($withLikesStr);

        if( $onlyWorking ) {
            $curdate = date("Y-m-d", time());
            $query->leftJoin("user.employmentStatus", "employmentStatus");
            $currentusers = "employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."'";
            $query->andWhere($currentusers);
        }

        $query->orderBy("user.primaryPublicUserId","ASC");

        //echo "query=".$query."<br>";

        $users = $query->getQuery()->getResult();
        //echo "<br>users count=".count($users)."<br>";

        return $users;
    }
    public function findUsersBySitePermissionObjectActionInstitution_orig( $sitename, $objectStr, $actionStr, $institutionId ) {

        $permission = $this->findPermissionByObjectAction($objectStr,$actionStr);
        if( !$permission ) {
            return array();
        }
        //echo "permission=".$permission."<br>";

        $query = $this->_em->createQueryBuilder()->from(User::class, 'user');
        $query->select("user");

        //$whereStr = "administrativeTitles.institution = :nodeid OR appointmentTitles.institution = :nodeid OR medicalTitles.institution = :nodeid";
        //$whereStr = "institution.id = :nodeid";
        //$whereStr = "(SELECT role FROM AppUserdirectoryBundle:Roles at WHERE role.sites = :sitename) AS userrole";
        //$whereStr = "role.name LIKE '%ROLE_%'";

        //$whereStr = "institution.id = :nodeid";

        //$query->where($whereStr);
        //$query->addSelect($whereStr);

        $query->where("sites.name = :sitename OR sites.abbreviation = :sitename");
        $query->andWhere("permissions = :permission");

        //$query->andWhere("roles.institution = :institutionId");

        //$query->leftJoin("user.roles", "roles");
        //$query->leftJoin("AppUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE roles.name");
        //$query->innerJoin("AppUserdirectoryBundle:Roles", "roles", "WITH", "roles.name IN (user.roles)");

        //$query->leftJoin("AppUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE '%ROLE_VACREQ_SUBMITTER_%'");
        //$query->leftJoin("AppUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE '%ROLE_VACREQ_SUBMITTER_CYTOPATHOLOGY%'");
        //$query->leftJoin("AppUserdirectoryBundle:Roles", "roles", "WITH", "user.roles IS NOT NULL");
        //$query->leftJoin("AppUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE '%ROLE_VACREQ_SUBMITTER_%'");

        //$query->leftJoin("AppUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE '%roles.name%'");
        $query->leftJoin(Roles::class, "roles", "WITH", "user.roles LIKE '%roles.name%'");

        $query->leftJoin("roles.sites", "sites");
        $query->leftJoin("roles.permissions", "permissions");

        $query->leftJoin("roles.institution","institution");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $this->_em->getRepository(Institution::class)->find($institutionId);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $instStr = $this->_em->getRepository(Institution::class)->selectNodesUnderParentNode($institution,"institution",false);
        //echo "instStr=".$instStr."<br>";
        $query->andWhere($instStr);

        $query->orderBy("user.primaryPublicUserId","ASC");
        //$query->leftJoin("user.institution", "institution");
        //$query->groupBy('user');

        $query->setParameters(
            array(
                //"institutionId" => $institutionId,
                "permission" => $permission->getId(),
                "sitename" => $sitename,
                //'rolename' => '%"roles.name"%'
            )
        );

        //echo "query=".$query."<br>";

        $users = $query->getQuery()->getResult();
        //echo "<br>users count=".count($users)."<br>";

        return $users;
    }

    //check if user has direct permission
    public function findPermissionByObjectAction( $objectStr, $actionStr, $single=true ) {

        $query = $this->_em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:Permission', 'permissions')
            ->from(Permission::class, 'permissions')
            ->select("permissions")
            ->leftJoin("permissions.permission","permission")
            ->leftJoin("permission.permissionObjectList","permissionObjectList")
            ->leftJoin("permission.permissionActionList","permissionActionList")
            ->where("permissionActionList.name = :permissionActionStr")
            ->andWhere("permissionObjectList.name = :permissionObjectStr OR permissionObjectList.abbreviation = :permissionObjectStr")
            ->orderBy("permissions.id","ASC")
            ->setParameters( array(
                'permissionObjectStr' => $objectStr,
                'permissionActionStr' => $actionStr
            ));
        //->setParameter('permissionAction', $action);

        //echo "sql=".$query->getQuery()->getSql()."<br>";

        $permissions = $query->getQuery()->getResult();

        if( $single ) {
            if( count($permissions) > 0 ) {
                $permission = $permissions[0];
                return $permission;
            }
        }

        return $permissions;
    }

    public function findNotFellowshipUsers() {
        $query = $this->_em->createQueryBuilder()
            ->from(User::class, 'list')
            ->select("list")
            ->leftJoin("list.infos", "infos")
            ->where("list.createdby != 'googleapi'")
            ->orderBy("infos.displayName","ASC")
        ;
        //return $query->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)->getResult();
        return $query->getQuery()->getResult();
    }
    
//    public function getPendingAdminReview() {
//        if(1) {
//            $query = $this->_em->createQueryBuilder()
//                ->from(User::class, 'user')
//                ->select("user")
//                ->leftJoin("user.infos", "infos")
//                ->where("infos.email = 'cinava@yahoo.com' OR infos.emailCanonical = 'cinava@yahoo.com'")
//                ->orderBy("user.id", "ASC");
//            $pendings = $query->getQuery()->getResult();
//            $pendingCount = count($pendings);
//            echo "pendingCount=$pendingCount<br>";
//            exit("111");
//            //return $pendingCount;
//        }
//
//        //$totalcriteriastr = "user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system' AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL) AND (((administrativeTitles.status = 0 OR appointmentTitles.status = 0 OR medicalTitles.status = 0 OR locations.status = 0)) AND (((employmentStatus.id IS NULL) OR employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '2015-11-05')))";
//
//        //$em = $this->em; //getDoctrine()->getManager();
//        $repository = $this->_em->getRepository(User::class);
//        $dql = $repository->createQueryBuilder('user');
//        $dql->select('user');
//        $dql->where("user.id = 1");
//        $query = $dql->getQuery(); //$query = $this->_em->createQuery($dql);
//        $pendings = $query->getResult();
//        exit("111");
//        $pendingCount = count($pendings);
//        echo "pendingCount=$pendingCount<br>";
//        exit("111");
//
//        //return $pendingCount;
//
//
//        //$dql->select('COUNT(user.id)');
//
//        $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
//        $dql->leftJoin("user.appointmentTitles", "appointmentTitles");
//        $dql->leftJoin("user.medicalTitles", "medicalTitles");
//        $dql->leftJoin("user.locations", "locations");
//        $dql->leftJoin("user.employmentStatus", "employmentStatus");
//        $dql->leftJoin("employmentStatus.employmentType", "employmentType");
//
//        $dql->where($totalcriteriastr);
//
//        $query = $dql->getQuery(); //$query = $this->_em->createQuery($dql);
//
//        //$pending = 0;
//        //$pending = $query->getSingleScalarResult();
//        //$pending = $query->getOneOrNullResult();
//        //$pending = $query->getResult(\Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR);
//
//        //Symfony Exception: Doctrine\ORM\Query::getDQL(): Return value must be of type ?string, Doctrine\ORM\QueryBuilder returned
//        $pendings = $query->getResult();
//        //dump($pendings);
//        //exit('111');
//
//        //return $pendings;
//
//        $pendingCount = count($pendings);
//
//        //dump($pending);
//        //exit('111');
//
//        return $pendingCount;
//    }
//    public function getPendingReviewCriteria() {
//        $pendingStatus = BaseUserAttributes::STATUS_UNVERIFIED;
//        $criteriastr = "(".
//            "administrativeTitles.status = ".$pendingStatus.
//            " OR appointmentTitles.status = ".$pendingStatus.
//            " OR medicalTitles.status = ".$pendingStatus.
//            //" OR locations.status = ".$pendingStatus.
//            ")";
//
//        //current_only
//        $curdate = date("Y-m-d", time());
//        $criteriastr .= " AND (";
//        $criteriastr .= "employmentStatus.id IS NULL";
//        $criteriastr .= " OR ";
//        $criteriastr .= "employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."'";
//        $criteriastr .= ")";
//
//        //filter out system user
//        $totalcriteriastr = "user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'";
//
//        //filter out Pathology Fellowship Applicants
//        $totalcriteriastr = $totalcriteriastr . " AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)";
//
//        //activeAD
//        $totalcriteriastr = $totalcriteriastr . " AND (user.activeAD = TRUE AND user.enabled = TRUE)";
//
//        if( $criteriastr ) {
//            $totalcriteriastr = $totalcriteriastr . " AND (".$criteriastr.")";
//        }
//
//        return $totalcriteriastr;
//    }
    
}

