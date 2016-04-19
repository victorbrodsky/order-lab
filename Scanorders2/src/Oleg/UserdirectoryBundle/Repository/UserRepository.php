<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;


class UserRepository extends EntityRepository {


    public function findAllByInstitutionNodeAsUserArray( $nodeid ) {

        $users = $this->findAllByInstitutionNode($nodeid);
        $output = $this->convertUsersToArray($users);

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
        $query->where("administrativeTitles.institution = :nodeid OR appointmentTitles.institution = :nodeid OR medicalTitles.institution = :nodeid");
        $query->setParameters( array("nodeid"=>$nodeid) );

        $users = $query->getQuery()->getResult();

        return $users;
    }


    public function convertUsersToArray( $users ) {

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
                'id' => 'addnodeid-'.$user->getId(),
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

    public function findUserByRole($role) {

        $user = null;

        $query = $this->_em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:User', 'list')
            ->select("list")
            ->where("list.roles LIKE :role")
            ->orderBy("list.id","ASC")
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
                $userRoles->add($role);

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


    public function findUserRolesBySiteAndPartialRoleName( $user, $sitename, $rolePartialName, $institutionId=null, $atLeastOne=true ) {

        $userRoles = new ArrayCollection();

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

        $query =

        //echo "sql=".$query->getQuery()->getSql()."<br>";

        $roles = $query->getQuery()->getResult();
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



}

