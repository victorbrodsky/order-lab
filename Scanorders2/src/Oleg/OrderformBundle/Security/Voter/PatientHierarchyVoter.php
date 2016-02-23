<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/23/15
 * Time: 11:28 AM
 */

namespace Oleg\OrderformBundle\Security\Voter;


use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Entity\Encounter;
use Oleg\OrderformBundle\Entity\Imaging;
use Oleg\OrderformBundle\Entity\Message;
use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\Procedure;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;



class PatientHierarchyVoter extends BaseVoter {

    //you can use anything
//    const VIEW = 'view';
//    const SHOW = 'show';
//
//    const EDIT = 'edit';
//    const AMEND = 'amend';
//
//    const DELETE = 'delete'; //mark it inactive/invalid since we don't delete; this and 3 above are for Data Quality role
//
//    const CREATE = 'create';
//    const CHANGESTATUS = 'changestatus';


    protected function supports($attribute, $subject)
    {
        return false; //testing
        echo "PatientHierarchyVoter: support <br>";

        $attribute = $this->convertAttribute($attribute);

        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::CREATE, self::READ, self::UPDATE, self::DELETE, self::CHANGESTATUS))) {
            //exit("Not supported attribute=".$attribute."<br>");
            return false;
        }

        // only vote on Patient hierarchy objects inside this voter
        if(
            !$subject instanceof Patient &&
            !$subject instanceof Encounter &&
            !$subject instanceof Procedure &&
            !$subject instanceof Accession &&
            !$subject instanceof Part &&
            !$subject instanceof Block &&
            !$subject instanceof Slide &&
            !$subject instanceof Imaging
        ) {
            //exit("Not supported subject=".$subject."<br>");
            return false;
        }

        //echo "Supported subject=".$subject."<br>";
        return true;
    }

    //if return false it redirect to main page (access_denied_url?): "You don't have permission to visit this page on Scan Order site. If you already applied for access, then try to Re-Login"
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $attribute = $this->convertAttribute($attribute);

        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        //echo "attribute=".$attribute."<br>";

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


    protected function isOwner($subject, TokenInterface $token) {

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
} 