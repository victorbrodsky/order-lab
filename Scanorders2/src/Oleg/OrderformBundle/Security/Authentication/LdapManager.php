<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 1/22/14
 * Time: 1:21 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Security\Authentication;

use FR3D\LdapBundle\Ldap\LdapManager as BaseLdapManager;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LdapManager extends BaseLdapManager
{
    protected function hydrate(UserInterface $user, array $entry)
    {
        parent::hydrate($user, $entry);

        // Your custom code
        $user->setCreatedby('ldap');

        if( $user->getUsername() == "oli2002" || $user->getUsername() == "vib9020" ) {
            $user->addRole('ROLE_SUPER_ADMIN');
        }

    }
}