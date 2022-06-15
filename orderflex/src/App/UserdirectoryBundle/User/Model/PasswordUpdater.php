<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\UserdirectoryBundle\User\Model;

//use FOS\UserBundle\Model\UserInterface;
//use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
//use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
//use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;

/**
 * Class updating the hashed password in the user when there is a new password.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class PasswordUpdater implements PasswordUpdaterInterface
{
    private $encoderFactory;

//    public function __construct(EncoderFactoryInterface $encoderFactory)
//    {
//        $this->encoderFactory = $encoderFactory;
//    }
    public function __construct(PasswordHasherFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function hashPassword(UserInterface $user)
    {
        $plainPassword = $user->getPlainPassword();

        if (0 === strlen((string)$plainPassword)) {
            return;
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        if ($encoder instanceof NativePasswordHasher) { //NativePasswordEncoder, BCryptPasswordEncoder
            $user->setSalt(null);
        } else {
            $salt = rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
            $user->setSalt($salt);
        }

        $hashedPassword = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($hashedPassword);
        $user->eraseCredentials();
    }
}
