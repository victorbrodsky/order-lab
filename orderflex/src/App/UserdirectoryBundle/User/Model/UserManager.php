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

//use FOS\UserBundle\Util\CanonicalFieldsUpdater;
//use FOS\UserBundle\Util\PasswordUpdaterInterface;
use App\UserdirectoryBundle\Entity\User;
//use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Abstract User Manager implementation which can be used as base class for your
 * concrete manager.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class UserManager { //implements UserManagerInterface {

//    private $passwordUpdater;
//    private $canonicalFieldsUpdater;
//
//    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater)
//    {
//        $this->passwordUpdater = $passwordUpdater;
//        $this->canonicalFieldsUpdater = $canonicalFieldsUpdater;
//    }

    
    protected $objectManager;

    private $passwordUpdater;
    
    public function __construct(EntityManagerInterface $om, PasswordUpdaterInterface $passwordUpdater)
    {
        $this->passwordUpdater = $passwordUpdater;
        $this->objectManager = $om;
    }


    /**
     * {@inheritdoc}
     */
    public function createUser()
    {
        //$class = $this->getClass();
        //$user = new $class();

        $user = new User();
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByEmail($email)
    {
        return $this->findUserBy(array('emailCanonical' => $this->canonicalizeEmail($email)));
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByUsername($username) : ?UserInterface
    {
        return $this->findUserBy(array('usernameCanonical' => $this->canonicalizeUsername($username)));
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            $user = $this->findUserByEmail($usernameOrEmail);
            if (null !== $user) {
                return $user;
            }
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByConfirmationToken($token)
    {
        return $this->findUserBy(array('confirmationToken' => $token));
    }

    //updateUser
    /**
     * {@inheritdoc}
     */
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        $this->objectManager->persist($user);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updatePassword(UserInterface $user)
    {
        $this->passwordUpdater->hashPassword($user);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }
    
    protected function getRepository()
    {
        return $this->objectManager->getRepository($this->getClass());
    }
    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        //return "AppUserdirectoryBundle:User";
        return User::class;
    }


    ///////////// Util //////////////////
    /**
     * {@inheritdoc}
     */
    public function updateCanonicalFields(UserInterface $user)
    {
        $user->setUsernameCanonical($this->canonicalizeUsername($user->getUsername()));
        $user->setEmailCanonical($this->canonicalizeEmail($user->getEmail()));
    }
    
    /**
     * Canonicalizes an email.
     *
     * @param string|null $email
     *
     * @return string|null
     */
    public function canonicalizeEmail($email)
    {
        return $this->canonicalize($email);
    }

    /**
     * Canonicalizes a username.
     *
     * @param string|null $username
     *
     * @return string|null
     */
    public function canonicalizeUsername($username)
    {
        return $this->canonicalize($username);
    }

    /**
     * {@inheritdoc}
     */
    public function canonicalize($string)
    {
        if (null === $string) {
            return;
        }

        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }


    ////////////////// Not Used ////////////////////


//
//    /**
//     * @return PasswordUpdaterInterface
//     */
//    protected function getPasswordUpdater()
//    {
//        return $this->passwordUpdater;
//    }
//
//    /**
//     * @return CanonicalFieldsUpdater
//     */
//    protected function getCanonicalFieldsUpdater()
//    {
//        return $this->canonicalFieldsUpdater;
//    }
}
