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
use FR3D\LdapBundle\Driver\LdapDriverInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LdapManager extends BaseLdapManager
{

    private $timezone;

    public function __construct( LdapDriverInterface $driver, $userManager, array $params, $timezone = null ) {

        parent::__construct($driver,$userManager,$params);

        $this->timezone = $timezone;
    }

    protected function hydrate(UserInterface $user, array $entry)
    {

        //exit("using ldap! <br>");

        parent::hydrate($user, $entry);

        $user->setCreatedby('ldap');
        $user->getPreferences()->setTimezone($this->timezone);

        $user->addRole('ROLE_UNAPPROVED_SUBMITTER');

        if( $user->getUsername() == "oli2002" || $user->getUsername() == "vib9020" ) {
            $user->addRole('ROLE_ADMIN');
            $user->addRole('ROLE_SUBMITTER');
            $user->removeRole('ROLE_UNAPPROVED_SUBMITTER');
        }

    }

}