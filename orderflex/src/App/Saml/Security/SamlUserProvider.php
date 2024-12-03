<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 10/15/2024
 * Time: 4:47 PM
 */

namespace App\Saml\Security;

use App\UserdirectoryBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SamlUserProvider implements UserProviderInterface
{
    private $identifierField;
    private $container;

    public function __construct(private EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setIdentifierField(string $identifierField)
    {
        $this->identifierField = $identifierField;
    }

    public function loadUserByIdentifier(string $identifier): User
    {
        if (!$this->identifierField) {
            throw new \LogicException('Identifier field must be set before calling loadUserByIdentifier.');
        }

        //echo "identifierField=".$this->identifierField."<br>";
        //echo "identifier=$identifier <br>";

        //$user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $identifier]);

        //$authUtil = $this->container->get('authenticator_utility');
        $user = $this->entityManager->getRepository(User::class)->findOneUserByUserInfoUseridEmail($identifier);
        return $user;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function refreshUser(UserInterface $user): ?UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $getter  = 'get' . ucfirst($this->identifierField);
        if (method_exists($user, $getter)) {
            $value = $user->$getter();
            if($value !== "") {
                return $this->loadUserByIdentifier($value);
            }
        }

        return NULL;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    public function createUserFromSamlAttributes(string $identifier, array $attributes, array $attributeMapping): User
    {
        $user = new User();
        $setter = 'set' . ucfirst($this->identifierField);
        if (method_exists($user, $setter)) {
            $user->$setter($identifier);
        }

        foreach ($attributeMapping as $userField => $samlAttribute) {
            if (isset($attributes[$samlAttribute])) {
                $setter = 'set' . ucfirst($userField);
                if (method_exists($user, $setter)) {
                    $user->$setter($attributes[$samlAttribute][0]);
                }
            }
        }

        // Save new user to the database
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}