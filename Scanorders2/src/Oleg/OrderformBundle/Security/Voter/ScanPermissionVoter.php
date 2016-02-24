<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/23/15
 * Time: 11:28 AM
 */

namespace Oleg\OrderformBundle\Security\Voter;


//use Oleg\OrderformBundle\Entity\Accession;
//use Oleg\OrderformBundle\Entity\Block;
//use Oleg\OrderformBundle\Entity\Encounter;
//use Oleg\OrderformBundle\Entity\Imaging;
//use Oleg\OrderformBundle\Entity\Message;
//use Oleg\OrderformBundle\Entity\Part;
//use Oleg\OrderformBundle\Entity\Patient;
//use Oleg\OrderformBundle\Entity\Procedure;
//use Oleg\OrderformBundle\Entity\Slide;
//use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Entity\Encounter;
use Oleg\OrderformBundle\Entity\Imaging;
use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\Procedure;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\UserdirectoryBundle\Security\Voter\BasePermissionVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;



class ScanPermissionVoter extends BasePermissionVoter {

    const SIGN = 'sign';


    protected function getSiteRoleBase() {
        return 'SCANORDER';
    }

    protected function getSitename() {
        return 'scan';  //Site abbreviation i.e. fellapp, not fellowship-applications
    }

    protected function supports_ORIG($attribute, $subject) {

        if( !$this->supportAttribute($attribute, $subject) ) {
            return false;
        }

        // only vote on Patient hierarchy objects inside this voter
        if(
            $subject instanceof Patient ||
            $subject instanceof Encounter ||
            $subject instanceof Procedure ||
            $subject instanceof Accession ||
            $subject instanceof Part ||
            $subject instanceof Block ||
            $subject instanceof Slide ||
            $subject instanceof Imaging
        ) {
            //exit("Not supported subject=".$subject."<br>");
            return true;
        }

        return false;
    }

    //TODO: might add additional check:
    //isOwner - owner can perform any actions for this object
    //isChief - service chief can perform any actions if the objects under his/her service scope

    //$subject: string (i.e. "FellowshipApplication") or entity
    protected function canView($subject, TokenInterface $token)
    {
        //echo "subject=".$subject."<br>";
        //exit('Scan PermissionVoter: canView');

        if( parent::canView($subject,$token) ) {
            return true;
        }

        if( $this->isOwner($subject, $token) ) {
            //echo "user is provider <br>";
            return true;
        }

        //exit('can not view');

        return false;
    }

    //$subject: string (i.e. "FellowshipApplication") or entity
    protected function canEdit($subject, TokenInterface $token)
    {
        //exit('2');
        if( parent::canEdit($subject,$token) ) {
            return true;
        }

        if( !is_object($subject) ) {
            return false;
        }

        if( $this->isOwner($subject, $token) ) {
            //echo "user is provider <br>";
            return true;
        }

        //service chief can perform any actions if the objects under his/her service scope
        //subject's institution
        $subjectInstitution = $subject->getInstitution();
        if( $subjectInstitution ) {
            $user = $token->getUser();
            $securityUtil = $this->container->get('order_security_utility');
            $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
            $userChiefServices = $userSiteSettings->getChiefServices();
            if ($this->em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnodes($userChiefServices, $subjectInstitution)) {
                return true;
            }
        }

        return false;
    }


    //TODO: must decide how to deal with Patient hierarchy:
    // if permission is given to patient object, does it mean that
    // this permission propagates to the underlying patient objects.
    // Possible Solution:
    // 1) Create permission objects for each patient underlying objects with corresponding object and action:
    //    "View Patient", "View Encounter", "View Procedure", "View Accession", "View Part", "View Block", "View Slide", "View Scan" ...
    // 2) Create a role ROLE_SCANORDER_WCMC_ALL_UDERLYING_PATIENT_DATA_VIEW - View all underlying Patient objects
    // 3) Assign permission objects from (1) to the role ROLE_SCANORDER_WCMC_ALL_UDERLYING_PATIENT_DATA_VIEW



    protected function isOwner($subject, TokenInterface $token) {

        if( !method_exists($subject, "getProvider") ){
            return false;
        }

        if( !$subject->getId() || !$subject->getProvider() ) {
            return false;
        }

        $user = $token->getUser();

        if( $subject->getProvider()->getId() === $user->getId() ) {
            //echo "user is provider <br>";
            return true;
        }

        return false;
    }



//    //you can use anything
////    const VIEW = 'view';
////    const SHOW = 'show';
////
////    const EDIT = 'edit';
////    const AMEND = 'amend';
////
////    const DELETE = 'delete'; //mark it inactive/invalid since we don't delete; this and 3 above are for Data Quality role
////
////    const CREATE = 'create';
////    const CHANGESTATUS = 'changestatus';
//
//
//    protected function supports($attribute, $subject)
//    {
//        return false; //testing
//        echo "PatientHierarchyVoter: support <br>";
//
//        $attribute = $this->convertAttribute($attribute);
//
//        // if the attribute isn't one we support, return false
//        if (!in_array($attribute, array(self::CREATE, self::READ, self::UPDATE, self::DELETE, self::CHANGESTATUS))) {
//            //exit("Not supported attribute=".$attribute."<br>");
//            return false;
//        }
//
//        // only vote on Patient hierarchy objects inside this voter
//        if(
//            !$subject instanceof Patient &&
//            !$subject instanceof Encounter &&
//            !$subject instanceof Procedure &&
//            !$subject instanceof Accession &&
//            !$subject instanceof Part &&
//            !$subject instanceof Block &&
//            !$subject instanceof Slide &&
//            !$subject instanceof Imaging
//        ) {
//            //exit("Not supported subject=".$subject."<br>");
//            return false;
//        }
//
//        //echo "Supported subject=".$subject."<br>";
//        return true;
//    }
//
//    //if return false it redirect to main page (access_denied_url?): "You don't have permission to visit this page on Scan Order site. If you already applied for access, then try to Re-Login"
//    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
//    {
//        $attribute = $this->convertAttribute($attribute);
//
//        $user = $token->getUser();
//
//        if (!$user instanceof User) {
//            // the user must be logged in; if not, deny access
//            return false;
//        }
//
//        //echo "attribute=".$attribute."<br>";
//
//        switch($attribute) {
//
//            case self::CREATE:
//                return $this->canCreate($subject, $token);
//
//            case self::READ:
//                return $this->canView($subject, $token);
//
//            case self::UPDATE:
//                return $this->canEdit($subject, $token);
//
//            case self::CHANGESTATUS:
//                return $this->canChangeStatus($subject, $token);
//        }
//
//        throw new \LogicException('This code should not be reached!');
//    }
//
//

} 