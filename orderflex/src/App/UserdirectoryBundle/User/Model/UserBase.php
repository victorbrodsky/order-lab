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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

//https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/config/doctrine-mapping/User.orm.xml
//<field name="username" column="username" type="string" length="180" />
//<field name="usernameCanonical" column="username_canonical" type="string" length="180" unique="true" />
//<field name="email" column="email" type="string" length="180" />
//<field name="emailCanonical" column="email_canonical" type="string" length="180" unique="true" />
//<field name="enabled" column="enabled" type="boolean" />
//<field name="salt" column="salt" type="string" nullable="true" />
//<field name="password" column="password" type="string" />
//<field name="lastLogin" column="last_login" type="datetime" nullable="true" />
//<field name="confirmationToken" column="confirmation_token" type="string" length="180" unique="true" nullable="true" />
//<field name="passwordRequestedAt" column="password_requested_at" type="datetime" nullable="true" />
//<field name="roles" column="roles" type="array" />


/**
 * Storage agnostic user object.
 * Based on Fos User:
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class UserBase implements UserInterface, PasswordAuthenticatedUserInterface #, GroupableInterface
{

//    const ROLE_DEFAULT = 'ROLE_USER';
    //    const ROLE_SUPER_ADMIN = 'ROLE_PLATFORM_ADMIN'; //'ROLE_SUPER_ADMIN';
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\Column(name: 'username', type: 'string', length: 180)]
    protected $username;

    #[ORM\Column(name: 'username_canonical', type: 'string', length: 180)]
    protected $usernameCanonical;

    #[ORM\Column(name: 'email', type: 'string', nullable: true)]
    protected $email;

    #[ORM\Column(name: 'email_canonical', type: 'string', nullable: true)]
    protected $emailCanonical;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'enabled', type: 'boolean')]
    protected $enabled;

    /**
     * The salt to use for hashing.
     */
    #[ORM\Column(name: 'salt', type: 'string', nullable: true)]
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     */
    #[ORM\Column(name: 'password', type: 'string')]
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     */
    protected $plainPassword;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'last_login', type: 'datetime', nullable: true)]
    protected $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it.
     *
     * @var string|null
     */
    #[ORM\Column(name: 'confirmation_token', type: 'string', length: 180, nullable: true)]
    protected $confirmationToken;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(name: 'password_requested_at', type: 'datetime', nullable: true)]
    protected $passwordRequestedAt;

//    /**
    //     * @var GroupInterface[]|Collection
    //     */
    //    protected $groups;
    /**
     * @var array
     */
    #[ORM\Column(name: 'roles', type: 'array')]
    protected $roles;


    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->enabled = false;
        $this->roles = array();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($role): self
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function serialize()
//    {
//        return serialize(array(
//            $this->password,
//            $this->salt,
//            $this->usernameCanonical,
//            $this->username,
//            $this->enabled,
//            $this->id,
//            $this->email,
//            $this->emailCanonical,
//        ));
//    }
    /**
     * @return string
     *
     * @final
     */
    public function serialize(): array
    {
        return serialize($this->__serialize());
    }
    //implements the Serializable interface, which is deprecated. Implement __serialize() and __unserialize()
    //public function __serialize(): array {}
    public function __serialize(): array
    {
        return array(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical,
        );
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function unserialize($serialized)
//    {
//        $data = unserialize($serialized);
//
//        if (13 === count($data)) {
//            // Unserializing a User object from 1.3.x
//            unset($data[4], $data[5], $data[6], $data[9], $data[10]);
//            $data = array_values($data);
//        } elseif (11 === count($data)) {
//            // Unserializing a User from a dev version somewhere between 2.0-alpha3 and 2.0-beta1
//            unset($data[4], $data[7], $data[8]);
//            $data = array_values($data);
//        }
//
//        list(
//            $this->password,
//            $this->salt,
//            $this->usernameCanonical,
//            $this->username,
//            $this->enabled,
//            $this->id,
//            $this->email,
//            $this->emailCanonical
//        ) = $data;
//    }
    /**
     * @param string $serialized
     *
     * @return void
     *
     * @final
     */
    public function unserialize($serialized): void
    {
        $this->__unserialize(unserialize($serialized));
    }
    //public function __unserialize(array $data): void {}
    public function __unserialize($data): void
    {
        //$this->unserialize($data);
        //$data = unserialize($serialized);

        if (13 === count($data)) {
            // Unserializing a User object from 1.3.x
            unset($data[4], $data[5], $data[6], $data[9], $data[10]);
            $data = array_values($data);
        } elseif (11 === count($data)) {
            // Unserializing a User from a dev version somewhere between 2.0-alpha3 and 2.0-beta1
            unset($data[4], $data[7], $data[8]);
            $data = array_values($data);
        }

        list(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical
            ) = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsernameCanonical(): ?string
    {
        return $this->usernameCanonical;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailCanonical(): ?string
    {
        return $this->emailCanonical;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Gets the last login time.
     *
     * @return \DateTime|null
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

//        foreach ($this->getGroups() as $group) {
//            $roles = array_merge($roles, $group->getRoles());
//        }

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired(): bool
    {
        return true;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role): bool
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
            return true;
        }

        //return $this;
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsername($username): ?self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsernameCanonical($usernameCanonical): ?self
    {
        $this->usernameCanonical = $usernameCanonical;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSalt($salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email): ?self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailCanonical($emailCanonical): self
    {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($boolean): self
    {
        $this->enabled = (bool) $boolean;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSuperAdmin($boolean): self
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPlainPassword($password): self
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastLogin(\DateTime $time = null): self
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfirmationToken($confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPasswordRequestedAt(\DateTime $date = null): self
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordRequestNonExpired($ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
               $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles(array $roles): self
    {
        $this->roles = array();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function canonicalize($string): ?string
    {
        if (null === $string) {
            return null;
        }

        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);

        return $result;
    }



//    /**
//     * {@inheritdoc}
//     */
//    public function getGroups()
//    {
//        return $this->groups ?: $this->groups = new ArrayCollection();
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function getGroupNames()
//    {
//        $names = array();
//        foreach ($this->getGroups() as $group) {
//            $names[] = $group->getName();
//        }
//
//        return $names;
//    }

//    /**
//     * {@inheritdoc}
//     */
//    public function hasGroup($name)
//    {
//        return in_array($name, $this->getGroupNames());
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function addGroup(GroupInterface $group)
//    {
//        if (!$this->getGroups()->contains($group)) {
//            $this->getGroups()->add($group);
//        }
//
//        return $this;
//    }

//    /**
//     * {@inheritdoc}
//     */
//    public function removeGroup(GroupInterface $group)
//    {
//        if ($this->getGroups()->contains($group)) {
//            $this->getGroups()->removeElement($group);
//        }
//
//        return $this;
//    }
}
