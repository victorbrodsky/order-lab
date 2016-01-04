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



abstract class BaseVoter extends Voter {

    const VIEW = 'view';
    const SHOW = 'show';

    const EDIT = 'edit';
    const AMEND = 'amend';

    const DELETE = 'delete'; //mark it inactive/invalid since we don't delete; this and 3 above are for Data Quality role

    const CREATE = 'create';
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

    protected function canView($subject, TokenInterface $token)
    {
        //echo "canView? <br>";

        //return false; //test

        // if they can edit, they can view
        if( $this->canEdit($subject, $token) ) {
            //echo "user can edit <br>";
            return true;
        }

        //TODO:
        //1) find roles with permissions related to Patient, Encounter ...
        //2) check for each roles if user hasRole

        echo "can not view subject=".$subject."<br>";
        //exit('can not View exit');
        return false;
    }

    //status change can be done only by submitter(owner), ordering provider, or service chief
    protected function canChangeStatus($subject, TokenInterface $token) {

        // if they can edit, they can view
        if( $this->canEdit($subject, $token) ) {
            return true;
        }

        return false;
    }

    protected function canEdit($subject, TokenInterface $token)
    {
        //echo "canEdit? <br>";

        if( $this->isOwner($subject, $token) ) {
            //echo "user is provider <br>";
            return true;
        }

//        if( in_array('ROLE_SCANORDER_ADMIN', $user->getRoles()) ) {
//            return true;
//        }

        // ROLE_SCANORDER_ADMIN can do anything
        if ($this->decisionManager->decide($token, array('ROLE_SCANORDER_ADMIN'))) {
            return true;
        }

        $user = $token->getUser();

        //order's institution
        $orderInstitution = $subject->getInstitution();

        //service chief can perform any actions for all orders under his/her service scope
        $securityUtil = $this->container->get('order_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        $userChiefServices = $userSiteSettings->getChiefServices();
        foreach( $userChiefServices as $userChiefService ) {
            if( $this->em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode($userChiefService, $orderInstitution) ) {
                return true;
            }
        }

        //echo "can not Edit! <br>";
        return false;
    }

} 