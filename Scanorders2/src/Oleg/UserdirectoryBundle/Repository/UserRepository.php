<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;


class UserRepository extends EntityRepository {


    public function findAllByInstitutionNodeAsUserArray( $nodeid ) {

        $users = $this->findAllByInstitutionNode($nodeid);
        $output = $this->convertUsersToArray($users,$nodeid);

        return $output;
    }

    public function findAllByInstitutionNode( $nodeid ) {

        $query = $this->_em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:User', 'user')
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

        $lastName = trim($nameStrArr[0]);
        $firstName = trim($nameStrArr[1]);

        $query = $this->_em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:User', 'user')
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



    public function findOneUserByRole($role) {

        $user = null;

        $users = $this->findUserByRole($role);

        if( count($users) > 0 ) {
            $user = $users[0];
        }

        return $user;
    }

    public function findUserByRole( $role, $orderBy="user.id" ) {

        //$user = null;

        $query = $this->_em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:User', 'user')
            ->select("user")
            ->leftJoin("user.infos","infos")
            ->where("user.roles LIKE :role")
            ->orderBy($orderBy,"ASC")
            ->setParameter('role', '%"' . $role . '"%');

        return $query->getQuery()->getResult();
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

        if( count($roles) > 0 ) {
            //echo "findUserRolesByObjectAction!!! object=".$object."<br>";
            return true;
        }

        return false;
    }

    //check if user has direct permission
    public function isUserHasDirectPermissionObjectAction( $user, $object, $action ) {

        $query = $this->_em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Permission', 'permissions')
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
            //echo "role=".$role."<br>";
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

        //check if user's roles have permission
        $query = $this->_em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Roles', 'list')
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
    public function findRolesByObjectActionInstitutionSite($objectStr, $actionStr, $institutionId, $sitename, $roleName=null) {

        //check if user's roles have permission
        $query = $this->_em->createQueryBuilder()->from('OlegUserdirectoryBundle:Roles', 'list');
        $query->select("list");

        $query->leftJoin("list.permissions","permissions");
        $query->leftJoin("permissions.permission","permission");
        $query->leftJoin("permission.permissionObjectList","permissionObjectList");
        $query->leftJoin("permission.permissionActionList","permissionActionList");

        $query->where("permissionActionList.name = :permissionActionStr OR permissionActionList.abbreviation = :permissionActionStr");
        $query->andWhere("permissionObjectList.name = :permissionObjectStr OR permissionObjectList.abbreviation = :permissionObjectStr");

        $parameters = array(
            'permissionObjectStr' => $objectStr,
            'permissionActionStr' => $actionStr
        );

        if( $institutionId ) {
            $query->leftJoin("list.institution","institution");
            $institution = $this->_em->getRepository('OlegUserdirectoryBundle:Institution')->find($institutionId);
            //echo "institution=".$institution->getNodeNameWithRoot()."<br>";
            //get inst criterion string tree with collaboration
            //$instStr = $this->_em->getRepository('OlegUserdirectoryBundle:Institution')->
            //        getCriterionStrForCollaborationsByNode($institution,"institution",array("Intersection"),false,false);
            //get simple inst criterion string tree (without collaboration)
            $instStr = $this->_em->getRepository('OlegUserdirectoryBundle:Institution')->selectNodesUnderParentNode($institution,"institution",false);
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

        $query->orderBy("list.id","ASC");
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

            //find user role object (i.e. ROLE_VACREQ_SUPERVISOR_WCMC_PATHOLOGY)
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
    //find user parent roles specified by sitename, objectStr, actionStr
    public function findUserParentRolesBySitePermissionObjectAction( $user, $sitename, $parentObjectStr, $parentActionStr, $childObjectStr,$childActionStr ) {

        $userParentRoles = new ArrayCollection();

        //find this user roles
        $userRoles = $this->findUserRolesBySitePermissionObjectAction($user,$sitename,$childObjectStr,$childActionStr);
        //echo "userRole count=".count($userRoles)."<br>";

        //find parent roles
        $parentRoles = $this->findRolesByObjectActionInstitutionSite($parentObjectStr,$parentActionStr,null,$sitename);
        //echo "parentRoles=".count($parentRoles)."<br>";

        foreach( $parentRoles as $parentRole ) {
            //check if the $userRoles is under $parentRole
            foreach( $userRoles as $userRole ) {
                //echo "parentRole=".$parentRole."; userRole=".$userRole."<br>";
                if( $this->_em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode($parentRole->getInstitution(), $userRole->getInstitution()) ) {
                    if( $parentRole && !$userParentRoles->contains($parentRole) ) {
                        $userParentRoles->add($parentRole);
                    }
                }
            }
        }

        return $userParentRoles;
    }

    public function findRolesBySiteAndPartialRoleName( $sitename, $rolePartialName, $institutionId=null ) {

        $parameters = array(
            'sitename' => $sitename,
            'roleName' => '%' . $rolePartialName . '%'
        );

        //check if user's roles have permission
        $query = $this->_em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Roles', 'list')
            ->select("list")
            ->leftJoin("list.sites","sites");

        $query->where("list.name LIKE :roleName AND (sites.name = :sitename OR sites.abbreviation = :sitename)");

        if( $institutionId ) {
            $query->andWhere("list.institution = :institutionId");
            $parameters['institutionId'] = $institutionId;
        }

        $query->orderBy("list.id","ASC");

        $query->setParameters($parameters);

        //echo "sql=".$query->getQuery()->getSql()."<br>";

        $roles = $query->getQuery()->getResult();

        return $roles;
    }

    //find users by roles specified by sitename, objectStr, actionStr and with institution equal to institutuionId or with instition children roles
    public function findUsersBySitePermissionObjectActionInstitution( $sitename, $objectStr, $actionStr, $institutionId ) {

        $roles = $this->findRolesByObjectActionInstitutionSite($objectStr, $actionStr, $institutionId, $sitename);

        //construct with "user.roles LIKE '%ROLE_VACREQ_SUBMITTER_CLINICALPATHOLOGY%'"
        $withLikes = array();
        foreach( $roles as $role ) {
            $withLikes[] = "user.roles LIKE '%".$role->getName()."%'";
        }
        $withLikesStr = implode(" OR ", $withLikes);
        //echo "withLikesStr=".$withLikesStr."<br>";

        $query = $this->_em->createQueryBuilder()->from('OlegUserdirectoryBundle:User', 'user');
        $query->select("user");

        //$query->leftJoin("OlegUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE '%ROLE_VACREQ_SUBMITTER_CLINICALPATHOLOGY%'");
        //$query->leftJoin("OlegUserdirectoryBundle:Roles", "roles", "WITH", $withLikesStr);

        $query->where($withLikesStr);

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
        echo "permission=".$permission."<br>";

        $query = $this->_em->createQueryBuilder()->from('OlegUserdirectoryBundle:User', 'user');
        $query->select("user");

        //$whereStr = "administrativeTitles.institution = :nodeid OR appointmentTitles.institution = :nodeid OR medicalTitles.institution = :nodeid";
        //$whereStr = "institution.id = :nodeid";
        //$whereStr = "(SELECT role FROM OlegUserdirectoryBundle:Roles at WHERE role.sites = :sitename) AS userrole";
        //$whereStr = "role.name LIKE '%ROLE_%'";

        //$whereStr = "institution.id = :nodeid";

        //$query->where($whereStr);
        //$query->addSelect($whereStr);

        $query->where("sites.name = :sitename OR sites.abbreviation = :sitename");
        $query->andWhere("permissions = :permission");

        //$query->andWhere("roles.institution = :institutionId");

        //$query->leftJoin("user.roles", "roles");
        //$query->leftJoin("OlegUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE roles.name");
        //$query->innerJoin("OlegUserdirectoryBundle:Roles", "roles", "WITH", "roles.name IN (user.roles)");

        //$query->leftJoin("OlegUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE '%ROLE_VACREQ_SUBMITTER_%'");
        //$query->leftJoin("OlegUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE '%ROLE_VACREQ_SUBMITTER_CYTOPATHOLOGY%'");
        $query->leftJoin("OlegUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE '%roles.name%'");
        //$query->leftJoin("OlegUserdirectoryBundle:Roles", "roles", "WITH", "user.roles IS NOT NULL");
        //$query->leftJoin("OlegUserdirectoryBundle:Roles", "roles", "WITH", "user.roles LIKE '%ROLE_VACREQ_SUBMITTER_%'");

        $query->leftJoin("roles.sites", "sites");
        $query->leftJoin("roles.permissions", "permissions");

        $query->leftJoin("roles.institution","institution");
        $institution = $this->_em->getRepository('OlegUserdirectoryBundle:Institution')->find($institutionId);
        $instStr = $this->_em->getRepository('OlegUserdirectoryBundle:Institution')->selectNodesUnderParentNode($institution,"institution",false);
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

        echo "query=".$query."<br>";

        $users = $query->getQuery()->getResult();
        echo "<br>users count=".count($users)."<br>";

        return $users;
    }

    //check if user has direct permission
    public function findPermissionByObjectAction( $objectStr, $actionStr, $single=true ) {

        $query = $this->_em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Permission', 'permissions')
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

}

