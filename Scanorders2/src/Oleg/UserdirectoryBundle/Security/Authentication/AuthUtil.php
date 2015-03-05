<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 3/4/15
 * Time: 1:37 PM
 */

namespace Oleg\UserdirectoryBundle\Security\Authentication;


use Oleg\OrderformBundle\Security\Util\AperioUtil;

class AuthUtil {

    private $sc;
    private $em;

    private $supportedUsertypesLdap = array('wcmc-cwid');

    public function __construct($sc,$em)
    {
        $this->sc = $sc;
        $this->em = $em;
    }



    public function AperioAuthentication($token, $userProvider) {

        echo "AperioAuthentication<br>";
        //exit();

        $aperioUtil = new AperioUtil();

        $user = $aperioUtil->aperioAuthenticateToken( $token, $this->sc, $this->em );

        if( $user ) {
            //echo "Aperio user found=".$user->getUsername()."<br>";
            //exit();
            return $user;
        }

        return NULL;
    }



    public function LdapAuthentication($token, $userProvider) {

        echo "LdapAuthentication<br>";
        //exit();

        //get clean username
        $userSecUtil = $this->sc->get('user_security_utility');
        $usernameClean = $userSecUtil->createCleanUsername($token->getUsername());

        $usernamePrefix = $userSecUtil->getUsernamePrefix($token->getUsername());
        if( in_array($usernamePrefix, $this->supportedUsertypesLdap) == false ) {
            //exit('LDAP Authentication error');
            //throw new BadCredentialsException('LDAP Authentication: the usertype '.$usernamePrefix.' can not be authenticated by ' . implode(', ',$this->supportedUsertypesLdap));
            return NULL;
        }

        $ldapRes = $this->ldapBind($usernameClean,$token->getCredentials());

        if( $ldapRes ) {

            $user = $this->findUserByUsername($token->getUsername());
            echo "Ldap user =".$user."<br>";

            if( $user ) {
                echo "DB user found=".$user->getUsername()."<br>";
                //exit();
                return $user;
            }

            echo "1<br>";

            //constract a new user
            $user = $userSecUtil->constractNewUser($token->getUsername());
            echo "user=".$user->getUsername()."<br>";

            $user->setCreatedby('ldap');

            //modify user: set keytype and primary public user id
            $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);

            if( !$userkeytype ) {
                $userUtil = new UserUtil();
                $count_usernameTypeList = $userUtil->generateUsernameTypes($this->em);
                $userkeytype = $userSecUtil->getUsernameType($this->usernamePrefix);
                echo "userkeytype=".$userkeytype."<br>";
            }

            $user->setKeytype($userkeytype);
            $user->setPrimaryPublicUserId($usernameClean);

            //get user info from Active Directory using php ldap function
            $searchRes = $this->searchLdap($usernameClean);

            if( $searchRes ) {
                $user->setEmail($searchRes['mail']);
                $user->setFirstName($searchRes['givenName']);
                $user->setLastName($searchRes['lastName']);
                $user->setDisplayName($searchRes['displayName']);
                $user->setPreferredPhone($searchRes['telephoneNumber']);
            }

            exit('ldap ok');

            //save user to DB
            $userManager = $this->sc->get('fos_user.user_manager');
            $userManager->updateUser($user);

            return $user;

        } else {
            //exit('ldap failed');
        }


        return NULL;
    }


    public function findUserByUsername($username) {

        $userManager = $this->sc->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);

        return $user;
    }


    public function ldapBind( $username, $password ) {

        //Ldap authentication using exe script
        $LDAPHost = "cumcdcp02.a.wcmc-ad.net";
        $exePath = "C:/Program Files (x86)/Aperio/Spectrum/htdocs/order/scanorder/Scanorders2/src/Oleg/UserdirectoryBundle/Util/";
        $exeFile = "LdapSaslCustom.exe";
        $command = $exePath.$exeFile;
        $command = $exeFile;

        $commandParams = $command.' '.$LDAPHost.' '.$username.' '.$password;
        //echo "commandParams=".$commandParams."<br>";

        exec(
            $commandParams,
            $output,
            $return
        );

        echo "return=".$return."<br>";
        echo "output:<br>";
        print_r($output);
        echo "<br>";

        if( $return == 1 && count($output) > 0 ) {
            if( $output[0] == 'LDAP_SUCCESS' ) {
                return 1;
            }
        }

        return NULL;
    }

    public function searchLdap($username) {

        echo "username=".$username."<br>";

        //$filter="(ObjectClass=Person)";
        $filter="(cn=".$username.")";

        $dn = "CN=Users,DC=a,DC=wcmc-ad,DC=net";
        $LDAPHost = "cumcdcp02.a.wcmc-ad.net";
        $LDAPUserAdmin = "svc_aperio_spectrum";
        $LDAPUserPasswordAdmin = "Aperi0,123";

        $cnx = ldap_connect($LDAPHost) or die("Could not connect to LDAP");
        if( !$cnx ) {
            return NULL;
        }

        if( !ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3) ) {
            echo 'Could not set version 3 <br>';
            return NULL;
        }

        if( !ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0) ) {
            echo 'Could not disable referrals <br>';
            return NULL;
        }

        if( !ldap_set_option($cnx, LDAP_OPT_SIZELIMIT, 1) ) {
            echo 'Could not set limit <br>';
            return NULL;
        }

        $res = ldap_bind($cnx,$LDAPUserAdmin,$LDAPUserPasswordAdmin);
        if( !$res ) {
        	echo "Could not bind to LDAP: user=".$LDAPUserAdmin."<br>";
            ldap_error($cnx);
            ldap_unbind($cnx);
            return NULL;
        } else {
        	echo "OK simple LDAP: user=".$LDAPUserAdmin."<br>";
        }

        $LDAPFieldsToFind = array("mail", "title", "sn", "givenName", "displayName", "telephoneNumber"); //sn - lastName
        //$LDAPFieldsToFind = array("sn");   //, "givenName", "displayName", "telephoneNumber");
        //$LDAPFieldsToFind = array("cn", "samaccountname");

        $sr = ldap_search($cnx, $dn, $filter, $LDAPFieldsToFind);

        if( !$sr ) {
            echo 'Search failed <br>';
            ldap_error($cnx);
            ldap_unbind($cnx);
            return NULL;
        }

        $info = ldap_get_entries($cnx, $sr);

        $searchRes = array();

        for ($x=0; $x<$info["count"]; $x++) {
            $searchRes['mail'] = $info[$x]['mail'][0];
            $searchRes['title'] = $info[$x]['title'][0];
            $searchRes['givenName'] = $info[$x]['givenname'][0];
            $searchRes['lastName'] = $info[$x]['sn'][0];
            $searchRes['displayName'] = $info[$x]['displayname'][0];
            $searchRes['telephoneNumber'] = $info[$x]['telephonenumber'][0];

            if( !$searchRes['givenName'] ) {
                $searchRes['givenName'] = "";   //$username;
            }

            if( !$searchRes['lastName'] ) {
                $searchRes['lastName'] = "";    //$username;
            }

            //print "\nActive Directory says that:<br />";
            //print "givenName is: ".$searchRes['givenName']."<br>";
            //print "familyName is: ".$searchRes['lastName']."<br>";

            //print_r($info[$x]);

            //we have only one result
            break;
        }

//        if( count($searchRes) == 0 ) {
//            echo "no search results <br>";
//        }

        return $searchRes;
    }



} 