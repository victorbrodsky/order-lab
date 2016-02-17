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


abstract class BaseVoter extends Voter {

    const CREATE = 'create';
    const READ   = 'read';
    const UPDATE = 'update';
    const DELETE = 'delete'; //mark it inactive/invalid since we don't delete; this and 3 above are for Data Quality role

    const CHANGESTATUS = 'changestatus';

    protected $decisionManager;
    protected $em;
    protected $container;

    public function __construct(AccessDecisionManagerInterface $decisionManager, $em, $container)
    {
        $this->decisionManager = $decisionManager;
        $this->em = $em;
        $this->container = $container;
    }

    //isGranted("read", "Accession") or isGranted("read", $accession)
    //$subject: string (i.e. "FellowshipApplication") or entity
    protected function supports($attribute, $subject)
    {

        $attribute = $this->convertAttribute($attribute);

        // if the attribute isn't one we support, return false
        if( !in_array($attribute, array(self::CREATE, self::READ, self::UPDATE, self::CHANGESTATUS)) ) {
            //exit("Not supported attribute=".$attribute."<br>");
            return false;
        }

        $className = $this->getClassName($subject);

        //check if the $attribute (action or ROLE_) is in PermissionObjectList
        //$permissionObjects = $this->em->getRepository('OlegUserdirectoryBundle:User')->isUserHasPermissionObjectAction( $user, $className, "read" );
        $repository = $this->em->getRepository('OlegUserdirectoryBundle:PermissionObjectList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');
        $dql->where("list.name = :name");
        $query = $this->em->createQuery($dql);
        $query->setParameters( array('name'=>$className) );
        $permissionObjects = $query->getResult();

        if( count($permissionObjects) > 0 ) {
            return true;
        }

        //echo "Supported subject=".$subject."<br>";
        return false;
    }

    //if return false it redirect to main page (access_denied_url?): "You don't have permission to visit this page on Scan Order site. If you already applied for access, then try to Re-Login"
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        $attribute = $this->convertAttribute($attribute);

        $user = $token->getUser();

        if( !$user instanceof User ) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch($attribute) {

            case self::CREATE:
                return $this->canCreate($subject, $token);

            case self::READ:
                return $this->canView($subject, $token);

            case self::UPDATE:
                return $this->canEdit($subject, $token);

            case self::CHANGESTATUS:
                return $this->canChangeStatus($subject, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    protected function canView($subject, TokenInterface $token)
    {
        //echo "canView? <br>";
        //exit('1');

        //return false; //test

        // if they can edit, they can view
        if( $this->canEdit($subject, $token) ) {
            //echo "user can edit <br>";
            return true;
        }

        $user = $token->getUser();

        //$subject: string (i.e. "FellowshipApplication") or entity
        if( is_object($subject) ) {

            $securityUtil = $this->container->get('order_security_utility');
            //minimum requirement: subject must be under user's permitted/collaborated institutions
            //don't perform this check for dummy, empty objects
            if( $subject->getId() && $subject->getInstitution() ) {
                if( $securityUtil->isObjectUnderUserPermittedCollaboratedInstitutions($subject, $user, array("Union")) == false) {
                    return false;
                }
            }

        } else {
            //if subject is string, then it must be used only to show a list of entities =>
            //there is no institution info => skip the institution check
        }

        $className = $this->getClassName($subject);

        //check if the user has role with a permission $subject class name (i.e. "Patient") and "read"
        if( $this->em->getRepository('OlegUserdirectoryBundle:User')->isUserHasPermissionObjectAction( $user, $className, "read" ) ) {
            //exit('can View! exit');
            return true;
        } else {
            //echo "can not view ".$className."<br>";
        }

        //exit('can not View exit');
        return false;
    }

    //status change can be done only by submitter(owner), ordering provider, or service chief
    protected function canChangeStatus($subject, TokenInterface $token) {

        // if they can edit, they can view
        if( $this->canEdit($subject, $token) ) {
            return true;
        }

        //exit("canChangeStatus: not implemented yet");

        return false;
    }

    //$subject: string (i.e. "FellowshipApplication") or entity
    protected function canEdit($subject, TokenInterface $token)
    {
        //echo "canEdit? <br>";
        //echo "subject=".$subject."<br>";

        $user = $token->getUser();

        if( !$user instanceof User ) {
            return false;
        }

        //dummy object just created with as new => can not edit dummy object
        if( is_object($subject) && !$subject->getId() ) {
            return false;
        }

        //ROLE_PLATFORM_DEPUTY_ADMIN can do anything
        if( $this->decisionManager->decide($token, array('ROLE_PLATFORM_DEPUTY_ADMIN')) ) {
            return true;
        }

        //minimum requirement: subject must be under user's permitted/collaborated institutions
        if( is_object($subject) ) {
            $securityUtil = $this->container->get('order_security_utility');
            if ($securityUtil->isObjectUnderUserPermittedCollaboratedInstitutions($subject, $user, array("Union")) == false) {
                return false;
            }
        }

        //echo "can not Edit! <br>";
        return false;
    }

    protected function canCreate($subject, TokenInterface $token)
    {
        exit("Create is not supported for subject=" . $subject);
    }


    protected function getClassName($subject) {

        if( is_object($subject) ) {
            //get object class name
            $class = new \ReflectionClass($subject);
            $className = $class->getShortName();
        } else {
            $className = $subject;
        }

        return $className;
    }

    protected function convertAttribute($attribute)
    {
        switch($attribute) {

            case 'view':
            case 'show':
                return self::READ;

            case 'edit':
            case 'amend':
                return self::UPDATE;

            default:
                return $attribute;

        }

        return $attribute;
    }

}
