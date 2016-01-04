<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/23/15
 * Time: 11:28 AM
 */

namespace Oleg\OrderformBundle\Security\Voter;


use Oleg\OrderformBundle\Entity\Message;
use Oleg\UserdirectoryBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;



class MessageVoter extends BaseVoter {

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

    const SIGN = 'sign';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::SHOW, self::EDIT, self::AMEND, self::DELETE, self::CREATE, self::SIGN, self::CHANGESTATUS))) {
            //exit("Message Voter: Not supported attribute=".$attribute."<br>");
            return false;
        }

        // only vote on Patient hierarchy objects inside this voter
        if( !$subject instanceof Message ) {
            //exit("Message Voter: Not supported subject=".$subject."<br>");
            return false;
        }

        //echo "Supported subject=".$subject."<br>";
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
            case self::SHOW:
                return $this->canView($subject, $token);

            case self::EDIT:
            case self::AMEND:
                return $this->canEdit($subject, $token);

            case self::CHANGESTATUS:
                return $this->canChangeStatus($subject, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }


    protected function isOwner($subject, TokenInterface $token) {

        $user = $token->getUser();

        if( $subject->getProvider()->getId() === $user->getId() ) {
            //echo "user is provider <br>";
            return true;
        }

        foreach( $subject->getProxyuser() as $proxyuser ) {
            if( $proxyuser->getUser() && $proxyuser->getUser()->getId() === $user->getId() ) {
                return true;
            }
        }

        return false;
    }
} 