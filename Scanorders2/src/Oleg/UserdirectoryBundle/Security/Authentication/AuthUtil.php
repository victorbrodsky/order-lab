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
    private $logger;

    private $supportedUsertypesAperio = array('aperio');
    private $supportedUsertypesLdap = array('wcmc-cwid');

    public function __construct($sc,$em)
    {
        $this->sc = $sc;
        $this->em = $em;
        $this->logger = $sc->get('logger');
    }


    //TODO: remove unused classes related to aperio provider: AperioProvider, AperioFactory, AperioListener and all classes that use them
    public function AperioAuthentication($token, $userProvider) {

        echo "AperioAuthentication<br>";
        //exit();

        $userSecUtil = $this->sc->get('user_security_utility');

        $usernamePrefix = $userSecUtil->getUsernamePrefix($token->getUsername());
        if( in_array($usernamePrefix, $this->supportedUsertypesAperio) == false ) {
            $this->logger->warning('Aperio Authentication: the usertype '.$usernamePrefix.' can not be authenticated by ' . implode(', ',$this->supportedUsertypesAperio));
            return NULL;
        }

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
            $this->logger->warning('LDAP Authentication: the usertype '.$usernamePrefix.' can not be authenticated by ' . implode(', ',$this->supportedUsertypesLdap));
            return NULL;
        }

        //first search this user if exists in ldap directory
        $searchRes = $this->searchLdap($usernameClean);
        if( $searchRes == NULL || count($searchRes) == 0 ) {
            return NULL;
        }

        echo "user exists in ldap directory<br>";

        //if user exists in ldap, try bind this user and password
        $ldapRes = $this->ldapBind($usernameClean,$token->getCredentials());
        if( $ldapRes == NULL ) {
            //exit('ldap failed');
            return NULL;
        }
        //exit('ldap success');

        //check if user already exists in DB
        $user = $this->findUserByUsername($token->getUsername());
        echo "Ldap user =".$user."<br>";

        if( $user ) {
            echo "DB user found=".$user->getUsername()."<br>";
            //exit();
            return $user;
        }

        echo "1<br>";

        //////////////////// constract a new user ////////////////////
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

        if( $searchRes ) {
            $user->setEmail($searchRes['mail']);
            $user->setFirstName($searchRes['givenName']);
            $user->setLastName($searchRes['lastName']);
            $user->setDisplayName($searchRes['displayName']);
            $user->setPreferredPhone($searchRes['telephoneNumber']);
        }

        //TODO: remove this on production!
        if( $user->getUsername() == "oli2002_@_wcmc-cwid" ) {
            $user->addRole('ROLE_PLATFORM_ADMIN');
        }

        //exit('ldap ok');

        //////////////////// save user to DB ////////////////////
        $userManager = $this->sc->get('fos_user.user_manager');
        $userManager->updateUser($user);

        return $user;


        return NULL;
    }


    public function findUserByUsername($username) {

        $userManager = $this->sc->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);

        return $user;
    }

    //return 1 if bind successful
    //return NULL if failed
    public function ldapBind( $username, $password ) {
        if( substr(php_uname(), 0, 7) == "Windows" ){
            return $this->ldapBindWindows($username,$password);
        }
        else {
            return $this->ldapBindUnix($username,$password);
        }
    }

    //return 1 if bind successful
    //return NULL if failed
    public function ldapBindWindows( $username, $password ) {

        //echo "Windows ldap<br>";

        //Ldap authentication using exe script
        $LDAPHost = $this->sc->getParameter('ldaphost');
        $LDAPPort = $this->sc->getParameter('ldapport');

        //$exePath = "../src/Oleg/UserdirectoryBundle/Util/";
        $exePath = $this->sc->getParameter('ldapexepath');
        //$exeFile = "LdapSaslCustom.exe";
        $exeFile = $this->sc->getParameter('ldapexefilename');

        $command = $exePath.$exeFile;
        //$command = $exeFile;
        //echo "command=".$command."<br>";

        $command = escapeshellarg($command);
        $LDAPHost = escapeshellarg($LDAPHost);
        $LDAPPort = escapeshellarg($LDAPPort);
        $username = escapeshellarg($username);
        $password = escapeshellarg($password);

        $commandParams = escapeshellcmd($command.' '.$LDAPHost.' '.$LDAPPort.' '.$username.' '.$password);
        //echo "commandParams=".$commandParams."<br>";

        exec(
            $commandParams, //input
            $output,        //output array
            $return         //return value
        );

        //echo "return=".$return."<br>";
        //echo "output:<br>";
        //print_r($output);
        //echo "<br>";

        if( $return == 1 && count($output) > 0 ) {
            if( $output[0] == 'LDAP_SUCCESS' ) {
                return 1;
            }
        }

        return NULL;
    }

    //TODO: must be tested on unix environment
    public function ldapBindUnix( $username, $password ) {
        $this->logger->warning("Unix system detected. Must be tested!");
        $LDAPHost = $this->sc->getParameter('ldaphost');
        $mech = "GSSAPI";
        $cnx = $this->connectToLdap($LDAPHost);

        $res = ldap_sasl_bind($cnx,NULL,$password,$mech,NULL,$username,NULL);
        if( !$res ) {
            echo $mech." - could not sasl bind to LDAP by SASL<br>";
            ldap_error($cnx);
            ldap_unbind($cnx);
            return NULL;
        } else {
            ldap_unbind($cnx);
            return 1;
        }
        return NULL;
    }

    public function searchLdap($username) {

        echo "username=".$username."<br>";


        $LDAPHost = $this->sc->getParameter('ldaphost');
        echo "LDAPHost=".$LDAPHost."<br>";

        //$dn = "CN=Users,DC=a,DC=wcmc-ad,DC=net";
        $dn = "CN=Users";
        $ldapDc = $this->sc->getParameter('ldapou');
        $dcArr = explode(".",$ldapDc);
        foreach( $dcArr as $dc ) {
            $dn = $dn . ",DC=".$dc;
        }
        echo "dn=".$dn."<br>";

        $LDAPUserAdmin = $this->sc->getParameter('ldapusername');
        $LDAPUserPasswordAdmin = $this->sc->getParameter('ldappassword');

        //$filter="(ObjectClass=Person)";
        $filter="(cn=".$username.")";

        $cnx = $this->connectToLdap($LDAPHost);

        $res = ldap_bind($cnx,$LDAPUserAdmin,$LDAPUserPasswordAdmin);
        if( !$res ) {
        	echo "Could not bind to LDAP: user=".$LDAPUserAdmin."<br>";
            ldap_error($cnx);
            ldap_unbind($cnx);
            return NULL;
        } else {
        	//echo "OK simple LDAP: user=".$LDAPUserAdmin."<br>";
        }

        $LDAPFieldsToFind = array("mail", "title", "sn", "givenName", "displayName", "telephoneNumber", "company"); //sn - lastName
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

            if( array_key_exists('mail', $info[$x]) ) {
                $searchRes['mail'] = $info[$x]['mail'][0];
            }
            if( array_key_exists('title', $info[$x]) ) {
                $searchRes['title'] = $info[$x]['title'][0];
            }
            if( array_key_exists('givenname', $info[$x]) ) {
                $searchRes['givenName'] = $info[$x]['givenname'][0];
            }
            if( array_key_exists('sn', $info[$x]) ) {
                $searchRes['lastName'] = $info[$x]['sn'][0];
            }
            if( array_key_exists('displayname', $info[$x]) ) {
                $searchRes['displayName'] = $info[$x]['displayname'][0];
            }
            if( array_key_exists('telephonenumber', $info[$x]) ) {
                $searchRes['telephoneNumber'] = $info[$x]['telephonenumber'][0];
            }
            if( array_key_exists('company', $info[$x]) ) {
                $searchRes['company'] = $info[$x]['company'][0];    //not used currently
            }

            if( array_key_exists('givenName',$searchRes) && !$searchRes['givenName'] ) {
                $searchRes['givenName'] = "";   //$username;
            }

            if( array_key_exists('lastName',$searchRes) && !$searchRes['lastName'] ) {
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

    //return ldap connection
    public function connectToLdap( $LDAPHost ) {

        $cnx = ldap_connect($LDAPHost);
        if( !$cnx ) {
            $this->logger->warning("Ldap: Could not connect to LDAP");
            return NULL;
        }

        if( !ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3) ) {
            $this->logger->warning("Ldap: Could not set version 3");
            ldap_unbind($cnx);
            return NULL;
        }

        if( !ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0) ) {
            $this->logger->warning("Ldap: Could not disable referrals");
            ldap_unbind($cnx);
            return NULL;
        }

        if( !ldap_set_option($cnx, LDAP_OPT_SIZELIMIT, 1) ) {
            $this->logger->warning("Ldap: Could not set limit to 1");
            ldap_unbind($cnx);
            return NULL;
        }

        return $cnx;
    }


} 