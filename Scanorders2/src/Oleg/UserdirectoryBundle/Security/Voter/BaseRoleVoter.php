<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/23/15
 * Time: 11:28 AM
 */

namespace Oleg\UserdirectoryBundle\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Oleg\UserdirectoryBundle\Entity\User;


//Role have permission objects (Permission);
//Permission object has one permission (PermissionList);
//Permission has one PermissionObjectList and one PermissionActionList


abstract class BaseRoleVoter extends Voter {

    protected $decisionManager;
    protected $em;
    protected $container;

    public function __construct(AccessDecisionManagerInterface $decisionManager, $em, $container)
    {
        $this->decisionManager = $decisionManager;
        $this->em = $em;
        $this->container = $container;
    }


    //isGranted("ROLE_DEIDENTIFICATOR_USER") or isGranted("ROLE_DEIDENTIFICATOR_BANNED")
    //$attribute: ROLE_...
    //$subject: null
    protected function supports($attribute, $subject) {

        $siteRoleBase = $this->getSiteRoleBase();
        $sitename = $this->getSitename();

        //support USERDIRECTORY roles only
        if( strpos($attribute, 'ROLE_'.$siteRoleBase.'_') === false ) {
            return false;
        }

        return $this->siteSpecificRoleSupport($attribute, $subject, $sitename, $siteRoleBase);
    }

    //evaluate if this user has this role (attribute)
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {

        //return false; //testing

        $siteRoleBase = $this->getSiteRoleBase();
        $sitename = $this->getSitename();

        return $this->voteOnSiteSpecificAttribute($attribute, $subject, $token, $sitename, $siteRoleBase);
    }




    //Role Voter: attribute is ROLE_...
    //Vote if a user has a general site role
    protected function siteSpecificRoleSupport($attribute, $subject, $sitename, $siteRoleBase) {

        //echo "DeidentifierVoter: support <br>";
        //echo $sitename.': siteSpecificRoleSupport: attribute='.$attribute."<br>";
        //echo 'subject='.$subject."<br>";

        //does not support UNAPPROVED and BANNED roles for this voter
        if( strpos($attribute, '_UNAPPROVED') !== false || strpos($attribute, '_BANNED') !== false ) {
            //exit('do not support _UNAPPROVED or _BANNED roles');
            return false;
        }

        //all general ADMIN roles are checked by a default voter using role hierarchy in security.yml
        if( $attribute == 'ROLE_'.$siteRoleBase.'_ADMIN' ) {
            //exit('do not support ' . 'ROLE_'.$siteRoleBase.'_ADMIN');
            return false;
        }

        return true;
    }

    //evaluate if this user has this role (attribute)
    public function voteOnSiteSpecificAttribute($attribute, $subject, TokenInterface $token, $sitename, $siteRoleBase) {
        //return false; //testing
        //echo $sitename.': voteOnSiteSpecificAttribute: attribute='.$attribute."<br>";
        //echo 'attribute='.$attribute."<br>";
        //echo 'subject='.$subject."<br>";
        $user = $token->getUser();
        //return true;

        if( !$user instanceof User ) {
            // the user must be logged in; if not, deny access
            //exit('user is not object');
            return false;
        }

        //ROLE_DEIDENTIFICATOR_ADMIN can do anything
        if( $this->decisionManager->decide($token, array('ROLE_'.$siteRoleBase.'_ADMIN')) ) {
            //exit('admin!');
            return true;
        }

        //check if user has this role including hiearchy roles
        if( $user->hasRole($attribute) ) {
            //if user has this role => access is granted
            //exit('hasRole');
            return true;
        }

        //check for general dummy role ROLE_DEIDENTIFICATOR_USER
        if( $attribute == 'ROLE_'.$siteRoleBase.'_USER' ) {
            //exit('check general user role='.$attribute);
            if( $this->hasGeneralSiteRole($user,$sitename) ) {
                //echo 'hasGeneralSiteRole yes <br>';
                //exit('hasGeneralSiteRole');

                //check if user has ROLE_DEIDENTIFICATOR_BANNED or ROLE_DEIDENTIFICATOR_UNAPPROVED
                if( $user->hasRole("ROLE_".$siteRoleBase."_BANNED") || $user->hasRole("ROLE_".$siteRoleBase."_UNAPPROVED") ) {
                    return false;
                }

                return true;
                //return VoterInterface::ACCESS_GRANTED;
            }

        }

        //Dummy unknown role: check if this role has appropriate site name
        $roleObject = $this->em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($attribute);
        if( $roleObject ) {
            foreach( $roleObject->getSites() as $site ) {
                if( $site->getName()."" == $sitename || $site->getAbbreviation()."" == $sitename ) {
                    //exit('Dummy unknown site ok');
                    return true;
                }
            }
        }

        //exit('no access');
        return false;

        //throw new \LogicException('This code should not be reached!');
    }


    public function hasGeneralSiteRole( $user, $sitename ) {

        foreach( $user->getRoles() as $roleStr ) {
            //echo 'roleStr='.$roleStr."<br>";
            $role = $this->em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($roleStr);
            if( $role ) {
                foreach( $role->getSites() as $site ) {
                    //echo 'role='.$role.", site=".$site->getName()."<br>";
                    if( $site->getName()."" == $sitename."" || $site->getAbbreviation()."" == $sitename ) {
                        //echo "access true <br>";
                        return true;
                    }
                }
            }
        }

        return false;
    }


}
