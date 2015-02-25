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
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Util\UserUtil;

class LdapManager extends BaseLdapManager
{

    private $logger;

    private $timezone;
    private $em;
    private $container;

    private $supportedUsertypes = array('wcmc-cwid');
    private $usernamePrefix;


    public function __construct( LdapDriverInterface $driver, $userManager, array $params, $container, $em ) {

        //print_r($params);
        //exit("constractor ldap <br>");

        $this->logger = $container->get('logger');

        parent::__construct($driver,$userManager,$params);

        $this->timezone = $container->getParameter('default_time_zone');
        $this->em = $em;
        $this->container = $container;
    }



    //username can be in form of nyh\cap9083, so it must purify for DB lookup
    public function findUserByUsername($username)
    {

        //exit('findUserByUsername: username='.$username);

        $userSecUtil = $this->container->get('user_security_utility');

        $pureName = $this->cleanUsernamePrefix($username);
        echo "pureName=".$pureName."<br>";

        //check if username is valid (has prefix)
        if( $userSecUtil->usernameIsValid($pureName) !== true ) {
            //exit('not valid');
            throw new BadCredentialsException('The username '.$pureName.' is not valid.');
        }

        //don't authenticate users without WCMC CWID keytype
        $usernamePrefix = $userSecUtil->getUsernamePrefix($pureName);
        if( in_array($usernamePrefix, $this->supportedUsertypes) == false ) {
            //exit('LDAP Authentication error');
            throw new BadCredentialsException('LDAP Authentication: the usertype '.$usernamePrefix.' can not be authenticated by ' . implode(', ',$this->supportedUsertypes));
        }

        $this->usernamePrefix = $usernamePrefix;
        //echo "usernamePrefix=".$usernamePrefix."<br>";

        //clean username
        $usernameClean = $userSecUtil->createCleanUsername($pureName);
        echo "usernameClean=".$usernameClean."<br>";

        $user =  parent::findUserByUsername($usernameClean);

        if( !$user ) {
            $this->logger->warning('User not found with username='.$usernameClean);
            //exit('User not found with username='.$usernameClean);
        }

        //echo "<br>found user=".$user->getUsername()."<br>";
        //exit('after find');

        //set original username with prefix
        if( $user ) {
            $user->setUsernameForce( $username );
        }

        return $user;
    }

    protected function hydrate(UserInterface $user, array $entry) {

        //exit('username='.$user->getUsername());

        parent::hydrate($user, $entry);

        $userUtil = new UserUtil();

        $user->setCreatedby('ldap');
        $user->getPreferences()->setTimezone($this->timezone);

        //modify user: set keytype and primary public user id
        $usernameClean = $user->getUsername();
        $userSecUtil = $this->container->get('user_security_utility');
        $userkeytype = $userSecUtil->getUsernameType($this->usernamePrefix);

        //first time login when DB is clean
        //echo "userkeytype=".$userkeytype."<br>";
        if( !$userkeytype ) {
            $count_usernameTypeList = $userUtil->generateUsernameTypes($this->em);
            //echo "generated user types=".$count_usernameTypeList."<br>";
            $userkeytype = $userSecUtil->getUsernameType($this->usernamePrefix);
            //echo "userkeytype=".$userkeytype."<br>";
            //exit();
        }

        $user->setKeytype($userkeytype);
        $user->setPrimaryPublicUserId($usernameClean);

        //TODO: remove this on production!
        if(     $user->getPrimaryPublicUserId() == "oli2002"
            ||  $user->getPrimaryPublicUserId() == "vib9020"
            //||  $user->getPrimaryPublicUserId() == "svc_aperio_spectrum"
        ) {
            $user->addRole('ROLE_PLATFORM_ADMIN');
        }

        //add default locations
        $userUtil->addDefaultLocations($user,null,$this->em,$this->container);

        //replace admin title by object
        $userUtil->replaceAdminTitleByObject($user,null,$this->em,$this->container);

        echo "<br>hydrate: user's keytype=".$user->getKeytype()." <br>";
        echo "user's username=".$user->getUsername()." <br>";
        echo "user's primaryPublicUserId=".$user->getPrimaryPublicUserId()." <br>";
        //print_r($user->getRoles());
        //exit('exit hydrate');

    }

    public function bind(UserInterface $user, $password)
    {
        //exit('<br>bind: username='.$user->getUsername());
        echo "<br>before: user's username=".$user->getUsername()." <br>";

        $originalUsername = $user->getUsername();

        //don't authenticate users without WCMC CWID keytype
        $userSecUtil = $this->container->get('user_security_utility');
        $usernamePrefix = $userSecUtil->getUsernamePrefix($originalUsername);
        if( in_array($usernamePrefix, $this->supportedUsertypes) == false ) {
            //exit('not valid, usernamePrefix='.$usernamePrefix);
            throw new BadCredentialsException('LDAP Authentication: the usertype '.$usernamePrefix.' can not be authenticated by ' . implode(', ',$this->supportedUsertypes));
        }

        //always clean username before bind, use primaryPublicUserId
        $user->setUsernameForce( $user->getPrimaryPublicUserId() );
        //$user->setUsernameForce( 'nyh\cap9083' );

        echo "<br>before parent bind: user's username=[".$user->getUsername()."]<br>";

        $bindRes = parent::bind($user, $password);

        echo "bindRes=".$bindRes."<br>";
        //exit();

        if( $bindRes ) {
            //replace back original username
            $user->setUsernameForce( $originalUsername );
            //replace only username
            $user->setUniqueUsername();
        } else {
            $this->logger->warning('Bind failed. bindRes='.$bindRes.", username=".$user->getUsername());
        }

        //testing: check the user's bind result
        //echo "after: user's username=".$user->getUsername()." <br>";
        echo "<br>bindRes=".$bindRes."<br>";
        exit('exit bind');

        return $bindRes;
    }


//    public function makesureKeytypeExist() {
//        //generate keytypes
//        $userutil = new UserUtil();
//        $count_usernameTypeList = $userutil->generateUsernameTypes($this->em);
//        echo "generated user types=".$count_usernameTypeList."<br>";
//    }

    //get rid of "nyh/" prefix
     public function cleanUsernamePrefix( $username ) {
         //return $username;
         $separator = "\\";
         if( strpos($username,$separator) !== false ) {
            $usernameArr = explode($separator,$username);
            return $usernameArr[1];
         } else {
             return $username;
         }
    }


}