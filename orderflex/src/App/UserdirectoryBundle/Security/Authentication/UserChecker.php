<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 12/6/2024
 * Time: 11:35 AM
 */

namespace App\UserdirectoryBundle\Security\Authentication;


use App\UserdirectoryBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        //if ($user->isDeleted()) {
        //    // the message passed to this exception is meant to be displayed to the user
        //    throw new CustomUserMessageAccountStatusException('Your user account no longer exists.');
        //}

        //redirect
        return new RedirectResponse( $this->router->generate($this->siteName.'_access_request_new') );

    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // user account is expired, the user may be notified
        //if ($user->isExpired()) {
        //    throw new AccountExpiredException('...');
        //}

//        if (!\in_array('foo', $token->getRoleNames())) {
//            throw new AccessDeniedException('...');
//        }
    }
}