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

use Oleg\UserdirectoryBundle\Util\UserUtil;

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

        $userUtil = new UserUtil();

        $userkeytype = $userUtil->getDefaultUsernameType($this->em);

        if( $userkeytype == null ) {
            echo "keytype null <br>";
            //generate user keytypes
            $userUtil->generateUsernameTypes($this->em,null);
            $userkeytype = $userUtil->getDefaultUsernameType($this->em);
        }

        //$user->setKeytype($userkeytype);
        //$user->setPrimaryPublicUserId($entry['cn'][0]);

        print_r($entry);

        //$uniqueUserName = $user->createUniqueUsername();
        $originalCN = $entry['cn'][0];

        $uniqueUserName = $user->createUniqueUsernameByKeyKeytype($userkeytype,$originalCN);

        echo "<br>uniqueUserName=".$uniqueUserName."<br>";

        $entry['cn'][0] = $uniqueUserName;

        echo "<br><br>";

        print_r($entry);

        //exit();

        parent::hydrate($user, $entry);

        //echo "UserdirectoryBundle user name=".$user->getUsername()."<br>";
        //exit("UserdirectoryBundle using ldap! <br>");


        $user->setCreatedby('ldap');
        $user->getPreferences()->setTimezone($this->timezone);

        //assign default keytype
        //$user->setKeytype($userkeytype);
        //replace username
        //$user->setPrimaryPublicUserId($user->getUsername());
        //$user->setUniqueUsername();
        $user->setUsername($uniqueUserName);
        $user->setKeytype($userkeytype);
        $user->setPrimaryPublicUserId($originalCN);

        //echo "user's keytype=".$user->getKeytype()." <br>";
        //echo "user's username=".$user->getUsername()." <br>";
        //exit();

        //TODO: remove this on production!
        if( $user->getPrimaryPublicUserId() == "oli2002" || $user->getPrimaryPublicUserId() == "vib9020" ) {
            $user->addRole('ROLE_ADMIN');
        }

        echo "<br><br>user's keytype=".$user->getKeytype()." <br>";
        echo "user's username=".$user->getUsername()." <br>";
        echo "user's primaryPublicUserId=".$user->getPrimaryPublicUserId()." <br>";
        print_r($user->getRoles());
        //exit();

    }



}