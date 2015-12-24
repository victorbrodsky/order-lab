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
use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Entity\Procedure;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;



class PatientHierarchyVoter extends Voter {

    //you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT))) {
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
            return false;
        }

        return true;
    }

    //if return false it redirect to main page (access_denied_url?): "You don't have permission to visit this page on Scan Order site. If you already applied for access, then try to Re-Login"
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView($subject, User $user)
    {
        echo "canView <br>";
        // if they can edit, they can view
        if( $this->canEdit($subject, $user) ) {
            echo "user can edit <br>";
            return true;
        }

        // the object could have, for example, a method isPrivate()
        // that checks a boolean $private property
        //return !$subject->isPrivate();

        //TODO:
        //1) find roles with permissions related to Patient, Encounter ...
        //2) check for each roles if user hasRole

        return false;
    }

    private function canEdit($subject, User $user)
    {
        echo "canEdit <br>";

        // this assumes that the data object has a getOwner() method
        // to get the entity of the user who owns this data object
        //return $user === $subject->getOwner();

        if( $subject->getProvider()->getId() === $user->getId() ) {
            echo "user is provider <br>";
            return true;
        }

//        if( in_array('ROLE_SCANORDER_ADMIN', $user->getRoles()) ) {
//            return true;
//        }

        // ROLE_SCANORDER_ADMIN can do anything
        if ($this->decisionManager->decide($user, array('ROLE_SCANORDER_ADMIN'))) {
            return true;
        }

        echo "can not Edit! <br>";

        return false;
    }

} 