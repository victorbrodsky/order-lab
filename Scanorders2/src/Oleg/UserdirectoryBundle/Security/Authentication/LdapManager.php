<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 1/22/14
 * Time: 1:21 PM
 * To change this template use File | Settings | File Templates.
 */

//Note: this ldap extends FR3D\LdapBundle\Security\Authentication\LdapAuthenticationProvider
//Note: findUserBy: $entries = $this->driver->search($this->params['baseDn'], $filter, $this->ldapAttributes); causes login delay
//Note: execution order: findUserByUsername, findUserBy, hydrate, bind
//If user already exists in DB then LdapManager->findUserByUsername is not used.
//Therefore: first user is checked by fosuser bundle if it exists in DB, then it check in LDAP. => user is got from DB or new user is created by LDAP
//Then user is authenticated by LDAP bind
//So to overwrite username different from LDAP, login page username should be split by two fields: user keytype and username

namespace Oleg\UserdirectoryBundle\Security\Authentication;

use FR3D\LdapBundle\Ldap\LdapManager as BaseLdapManager;
use FR3D\LdapBundle\Model\LdapUserInterface;
use FR3D\LdapBundle\Driver\LdapDriverInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Oleg\UserdirectoryBundle\Entity\User;

class LdapManager extends BaseLdapManager
{

    private $timezone;
    private $em;
    private $container;


    public function __construct( LdapDriverInterface $driver, $userManager, array $params, $container, $em ) {

        //print_r($params);
        //exit("constractor ldap <br>");

        parent::__construct($driver,$userManager,$params);

        $this->timezone = $container->getParameter('default_time_zone');
        $this->em = $em;
        $this->container = $container;
    }


    protected function hydrate(UserInterface $user, array $entry) {

        parent::hydrate($user, $entry);

        $user->setCreatedby('ldap');
        $user->getPreferences()->setTimezone($this->timezone);

        //modify user: set keytype and primary public user id
        $usernameClean = $user->getUsername();
        $userSecUtil = $this->container->get('user_security_utility');
        $userkeytype = $userSecUtil->getDefaultUserKeytypeSafe();
        $user->setKeytype($userkeytype);
        $user->setPrimaryPublicUserId($usernameClean);

        //TODO: remove this on production!
        if(     $user->getPrimaryPublicUserId() == "oli2002"
            ||  $user->getPrimaryPublicUserId() == "vib9020"
            ||  $user->getPrimaryPublicUserId() == "svc_aperio_spectrum"
        ) {
            $user->addRole('ROLE_ADMIN');
        }

//        echo "<br>hydrate: user's keytype=".$user->getKeytype()." <br>";
//        echo "user's username=".$user->getUsername()." <br>";
//        echo "user's primaryPublicUserId=".$user->getPrimaryPublicUserId()." <br>";
//        print_r($user->getRoles());
        //exit('exit hydrate');

    }






    public function findUserByUsername($username)
    {
        //clean username
        $userSecUtil = $this->container->get('user_security_utility');
        $usernameClean = $userSecUtil->createCleanUsername($username);

        return parent::findUserByUsername($usernameClean);
    }


    public function bind(UserInterface $user, $password)
    {

//        echo "before: user's username=".$user->getUsername()." <br>";

        //always clean username before bind, use primaryPublicUserId
        $user->setUsername( $user->getPrimaryPublicUserId() );

        $bindRes = parent::bind($user, $password);

        if( $bindRes ) {
            //replace only username
            $user->setUniqueUsername();
        }

//        echo "after: user's username=".$user->getUsername()." <br>";
//        echo "<br>bindRes=".$bindRes."<br>";
//        //exit('exit bind');

        return $bindRes;
    }



}