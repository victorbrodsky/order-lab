<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 1/22/14
 * Time: 1:21 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Security\Authentication;

use FR3D\LdapBundle\Ldap\LdapManager as BaseLdapManager;
use FR3D\LdapBundle\Model\LdapUserInterface;
use FR3D\LdapBundle\Driver\LdapDriverInterface;
use Symfony\Component\Security\Core\User\UserInterface;

//use Oleg\OrderformBundle\Helper\Parameters;

class LdapManager extends BaseLdapManager
{

    private $timezone;
    private $em;

    public function __construct( LdapDriverInterface $driver, $userManager, array $params, $timezone = null, $em = null ) {

        //print_r($params);
        //exit("constractor ldap <br>");

        parent::__construct($driver,$userManager,$params);

        $this->timezone = $timezone;
        $this->em = $em;
    }

    protected function hydrate(UserInterface $user, array $entry)
    {

        //exit("UserdirectoryBundle using ldap! <br>");
        //echo "UserdirectoryBundle user name=".$user->getUsername()."<br>";

        parent::hydrate($user, $entry);

        //echo "UserdirectoryBundle user name=".$user->getUsername()."<br>";
        //exit("UserdirectoryBundle using ldap! <br>");

        $user->setCreatedby('ldap');
        $user->getPreferences()->setTimezone($this->timezone);

        if( $user->getUsername() == "oli2002" || $user->getUsername() == "vib9020" ) {
            $user->addRole('ROLE_ADMIN');
        }


    }

}