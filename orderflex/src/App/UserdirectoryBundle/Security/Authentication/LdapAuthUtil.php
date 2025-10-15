<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 3/4/15
 * Time: 1:37 PM
 */

namespace App\UserdirectoryBundle\Security\Authentication;



use App\OrderformBundle\Security\Util\PacsvendorUtil;
//use App\Saml\Util\SamlConfigProvider;
use App\UserdirectoryBundle\Entity\IdentifierTypeList; //process.py script: replaced namespace by ::class: added use line for classname=IdentifierTypeList


use App\UserdirectoryBundle\Entity\UsernameType; //process.py script: replaced namespace by ::class: added use line for classname=UsernameType

use App\UserdirectoryBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use OneLogin\Saml2\Auth;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
//use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Constraints\DateTime;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

//use Symfony\Component\Security\Core\Util\StringUtils;

class LdapAuthUtil {

    private $container;        //container
    private $em;        //entity manager
    private $logger;
    private $requestStack;
    private $passwordHasher;

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em,
        RequestStack $requestStack,
        UserPasswordHasherInterface $passwordHasher
    )
    {
        $this->container = $container;
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->logger = $container->get('logger');
        $this->passwordHasher = $passwordHasher;
    }

    //Updated field for Site Settings:
    //
    //Username authentication method:
    //1- Entered sAMAccountName as Common Name (CN) in Base Distinguished Name (DN)
    //2- Retrieved userPrincipalName (UPN) associated with entered sAMAccountName as Bind Distinguished Name (DN) [if available, otherwise send entered sAMAccountName]
    //
    //Short:
    //1- sAMAccountName as CN in base DN
    //2- Retrieved userPrincipalName or sAMAccountName as bind DN
    //
    //Abbreviated:
    //1- sAMAccountNameAsCNinBaseDN
    //2- userPrincipalNameOrsAMAccountNameAsBindDN

    //Do not use search before bind. Search might take a long time

    public function LdapAuthentication($token, $ldapType=1) {

        $authUtil = $this->container->get('authenticator_utility');
        $this->logger->notice("Start Ldap Authentication: ldapType=[$ldapType]");
        //exit("Ldap Authentication: ldapType=[$ldapType]");

        $username = $token->getUsername();
        $password = $token->getCredentials();
        //return $user = $authUtil->findUserByUsername($username); //testing, overwrite login

        //get clean username
        $userSecUtil = $this->container->get('user_security_utility');
        $usernameClean = $userSecUtil->createCleanUsername($username);
        //$usernamePrefix = $userSecUtil->getUsernamePrefix($username);
        //exit("usernameClean=[$usernameClean], susernamePrefix=[$usernamePrefix]");

        $ldapUserData = null;

        $userSecUtil = $this->container->get('user_security_utility');
        $postfix = $this->getPostfix($ldapType);
        $ldapBindDN = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu'.$postfix);

        //fork wcm and others
        if(  str_contains($ldapBindDN, 'dc=wcmc-ad') ) {
            //WCM Ldap
            $this->logger->notice("before ldapBindV1");
            $ldapRes = $this->ldapBindV1($username,$password,$ldapType);
        } else {
            //Others Ldap
            // $ldapBindDN = 'oli2002'
            // @ldap_bind($ldapConn,$ldapBindDN,$password);
            $this->logger->notice("before searchLdapV2");

            $ldapUserData = $this->searchLdapV2($username,$ldapType);
            if (isset($ldapUserData['userprincipalname'][0])) {
                $upn = $ldapUserData['userprincipalname'][0];
                //echo "userPrincipalName=[$upn] <br>";
            } else {
                $upn = $username;
                //echo "userPrincipalName not found in LDAP entry.<br>";
            }

            $this->logger->notice("before ldapBindV2, upn=$upn");
            $ldapRes = $this->ldapBindV2($upn,$password,$ldapType);
        }

        //if user exists in ldap, try bind this user and password
        //$ldapRes = $this->ldapBind($usernameClean,$password,$ldapType); //LdapAuthenticationByUsernamePassword

        if( $ldapRes == NULL ) {
            //exit('ldap failed');
            //$this->logger->error("LdapAuthentication: can not bind user by usernameClean=[".$usernameClean."]; token=[".$token->getCredentials()."]");
            $this->logger->error("Ldap Authentication: can not ldap bind user by usernameClean=[".$usernameClean."];");

            $user = $authUtil->findUserByUsername($username);

            $authUtil->validateFailedAttempts($user);

            return NULL;
        }
        //exit('ldap success');

        //check if user already exists in DB
        $user = $authUtil->findUserByUsername($username);
        //echo "Ldap user =".$user."<br>";

        if( $user ) {
            //echo "DB user found=".$user->getUsername()."<br>";
            //exit();

            $this->logger->notice("findUserByUsername: authenticated successfully, existing user found in DB by token->getUsername()=".$username);

            if( $this->canLogin($user) === false ) {
                $this->logger->warning("Ldap Authentication: User cannot login ".$user);
                return NULL;
            }

            //exit('ldap user return');
            return $user;
        } else {
            $this->logger->warning("findUserByUsername: Can not find existing user in DB by token->getUsername()=".$username);
        }

        //echo "1<br>";

        //////////////////// Construct a new user ////////////////////
        if( !$user ) {
            return $this->createNewLdapUser($username,$ldapType,$ldapUserData);
        }

        return NULL;
    }

    //$username - primaryPublicUserId and userkeytype (i.e. cwid1_@_ldap-user)
    public function createNewLdapUser( $username, $ldapType=1, $ldapUserData=NULL ) {
        // Construct a new LDAP user
        $user = $this->getUserInLdap($username,$ldapType,$ldapUserData);

        if( !$user ) {
            return NULL;
        }

        exit("createNewLdapUser: user=".$user);
        //////////////////// save user to DB ////////////////////
        $userManager = $this->container->get('user_manager');
        $userManager->updateUser($user);

        return $user;
    }

    //Guard auth requires if user exists
    //$username - primaryPublicUserId and userkeytype (i.e. cwid1_@_ldap-user)
    public function getUserInLdap( $username, $ldapType=1, $ldapUserData=NULL ) {
        // Construct a new LDAP user
        $userSecUtil = $this->container->get('user_security_utility');

        $usernameClean = $userSecUtil->createCleanUsername($username);
        $usernamePrefix = $userSecUtil->getUsernamePrefix($username);

        if( !$ldapUserData ) {
            $ldapUserData = $this->searchLdap($usernameClean, $ldapType);
        }

        if( $ldapUserData == NULL || count($ldapUserData) == 0 ) {
            $this->logger->error("Ldap Search: can not find user by usernameClean=" . $usernameClean);
            return NULL;
        } else {
            $this->logger->notice("Ldap Search: user found by  usernameClean=" . $usernameClean);
        }

        $this->logger->notice("Ldap Search: create a new user (not in DB) found by token->getUsername()=".$username);
        $user = $userSecUtil->constractNewUser($username);
        //echo "user=".$user->getUsername()."<br>";

        $user->setCreatedby('ldap');

        //modify user: set keytype and primary public user id
        $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);

        if( !$userkeytype ) {
            //$userUtil = new UserUtil();
            //$userUtil = $this->get('user_utility');
            //$count_usernameTypeList = $userUtil->generateUsernameTypes();
            $userkeytype = $userSecUtil->getUsernameType($this->usernamePrefix);
            //echo "userkeytype=".$userkeytype."<br>";
        }

        $user->setKeytype($userkeytype);
        $user->setPrimaryPublicUserId($usernameClean);

        if( $ldapUserData ) {

            //$user->setEmail($ldapUserData['mail']);
            if( array_key_exists('mail', $ldapUserData) ) {
                $user->setEmail($ldapUserData['mail']);
            }

            if( array_key_exists('givenName', $ldapUserData) ) {
                $user->setFirstName($ldapUserData['givenName']);
            }

            if( array_key_exists('lastName', $ldapUserData) ) {
                $user->setLastName($ldapUserData['lastName']);
            }

            if( array_key_exists('displayName', $ldapUserData) ) {
                $user->setDisplayName($ldapUserData['displayName']);
            }

            if( array_key_exists('telephoneNumber', $ldapUserData) ) {
                $user->setPreferredPhone($ldapUserData['telephoneNumber']);
            }

            if( array_key_exists('mobile', $ldapUserData) ) {
                $user->setPreferredMobilePhone($ldapUserData['mobile']);
            }
        }

        return $user;
    }

    //Make sure the Authentication first retrieves the user's userPrincipalName value using the command
    // at the bottom of this email and then uses the retrieved userPrincipalName
    // to authenticate the user, not the uid and not the supplied user name via the web page.
    // If this fixes authentication, add
    // "Authenticate using: userPrincipalName OR sAMAccountName" field
    // where the value can be specified on Site Settings page and set it to userPrincipalName
//    public function ldapBind( $username, $password, $ldapType=1 ) {
//        $userSecUtil = $this->container->get('user_security_utility');
//        $postfix = $this->getPostfix($ldapType);
//        $ldapBindDN = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu'.$postfix);
//
//        //fork wcm and others
//        if(  !str_contains($ldapBindDN, 'dc=wcmc-ad') ) {
//            // Others Ldap:
//            // $ldapBindDN = 'oli2002'
//            // @ldap_bind($ldapConn,$ldapBindDN,$password);
//            return $this->ldapBindV2($username,$password,$ldapType);
//        }
//
//        // WCM Ldap:
//        // $ldapBindDN = cn='oli2002',cn=Users,dc=a,dc=wcmc-ad,dc=net
//        // $ldapBindDN = uid='oli2002',cn=Users,dc=a,dc=wcmc-ad,dc=net
//        // $ldapBindDN = cn='oli2002',cn=Users,dc=a,dc=wcmc-ad,dc=net
//        // @ldap_bind($cnx,$ldapBindDN,$password);
//        return $this->ldapBindV1($username,$password,$ldapType);
//    }



    //return 1 if bind successful
    //return NULL if failed
    public function ldapBindV1( $username, $password, $ldapType=1 ) {
        //return 1; //testing!!!: enable testing login
        //step 1
        if( $this->simpleLdapV1($username,$password,"cn",$ldapType) ) {
            return 1;
        }

        //return NULL; //testing: remove it in prod

        if( $this->simpleLdapV1($username,$password,"uid",$ldapType) ) {
            return 1;
        }

//        //step 2
//        if( substr(php_uname(), 0, 7) == "Windows" ){
//            return $this->ldapBindWindows($username,$password,$ldapType);
//        }
//        else {
//            return $this->ldapBindUnix($username,$password,$ldapType);
//        }

        return NULL;
    }

    public function ldapBindV2( $username, $password, $ldapType=1 ) {
        //return 1; //testing!!!: enable testing login
        //step 1
        if( $this->simpleLdapV2($username,$password,"cn",$ldapType) ) {
            return 1;
        }

        return NULL;
    }



    // tested by public ldap server: https://www.forumsys.com/tutorials/integration-how-to/ldap/online-ldap-test-server/
    // AD/LDAP Server Address: ldap.forumsys.com
    // AD/LDAP Server Port (Default: 389): 389
    // AD/LDAP Server OU: dc=example,dc=com
    // AD/LDAP Server Account User Name: null (must be null for no ldap search)
    // AD/LDAP Server Account Password: null (must be null for no ldap search)
    // LDAP/AD Authenticator Relative Path (Default: "../src/App/UserdirectoryBundle/Util/" ): null (doesn't matter for simpleLdap)
    // LDAP/AD Authenticator File Name (Default: "LdapSaslCustom.exe" ): null (doesn't matter for simpleLdap)
    //
    // WCM Ldap:
    // $ldapBindDN = cn='oli2002',cn=Users,dc=a,dc=wcmc-ad,dc=net
    // $ldapBindDN = uid='oli2002',cn=Users,dc=a,dc=wcmc-ad,dc=net
    // @ldap_bind($cnx,$ldapBindDN,$password);
    //
    // Others Ldap:
    // $ldapBindDN = 'oli2002'
    // @ldap_bind($ldapConn,$ldapBindDN,$password);
    //
    // tested by public ldap server: https://www.zflexldapadministrator.com/index.php/blog/82-free-online-ldap
    // Server: www.zflexldap.com
    // Port: 389
    // AD/LDAP Server OU: ou=users,ou=guests,dc=zflexsoftware,dc=com
    // Username: guest1 Password: guest1password
    //supports multiple aDLDAPServerOu: cn=Users,dc=a,dc=wcmc-ad,dc=net;ou=NYP Users,dc=a,dc=wcmc-ad,dc=net
    public function simpleLdapV1($username, $password, $userPrefix="uid", $ldapType=1) {
        //$this->logger->notice("Simple Ldap. $username, $password");

        //exit("simpleLdap");
        //set_time_limit(3); //testing
        //putenv('LDAPTLS_REQCERT=never'); // /etc/openldap/ldap.conf

        $userSecUtil = $this->container->get('user_security_utility');
        $postfix = $this->getPostfix($ldapType);

        $LDAPHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress'.$postfix);
        $LDAPPort = $userSecUtil->getSiteSettingParameter('aDLDAPServerPort'.$postfix);
        $this->logger->notice("simple Ldap: LDAPHost=".$LDAPHost.", LDAPPort=".$LDAPPort);

        $cnx = $this->connectToLdap($LDAPHost,$LDAPPort);

        if (!$cnx) {
            throw new \Exception("LDAP connection failed to $LDAPHost:$LDAPPort");
            //return NULL;
        } else {
            $this->logger->notice("simple Ldap: Connected to ldap server");
        }

        $origLdapBindDN = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu'.$postfix); //scientists,dc=example,dc=com

        $res = null;
        $ldapBindDNArr = explode(";",$origLdapBindDN);
        //echo "count=".count($ldapBindDNArr)."<br>";
        foreach( $ldapBindDNArr as $ldapBindDN) {
            $ldapBindDN = $userPrefix."=".$username.",".$ldapBindDN;
            //$ldapBindDN = "cn=$username,ou=NYP Users,ou=External,dc=a,dc=wcmc-ad,dc=net"; //testing
            //$this->logger->notice("simple Ldap: ldapBindDN=".$ldapBindDN);
            $res = @ldap_bind($cnx,$ldapBindDN,$password); //simpleLdap
            //$res = ldap_bind($cnx,$ldapBindDN,$password); //simpleLdap

            //$res = 1; //testing!!! allow authenticate

            if( $res ) {
                $this->logger->notice("simple Ldap: OK ldapBindDN=".$ldapBindDN);
                break;
            } else {
                $this->logger->notice("simple Ldap: NOTOK ldapBindDN=".$ldapBindDN);
            }
        }

        if( !$res ) {
            //echo $mech." - could not sasl bind to LDAP by SASL<br>";
            $this->logger->notice("simple Ldap: ldap_error(cnx)=".ldap_error($cnx)."; res=".$res."; user=".$username);
            //$this->logger->notice("ldapBindUnix: ldap_error=".ldap_error($cnx));
            ldap_error($cnx);
            ldap_unbind($cnx);
            return NULL;
        } else {
            $this->logger->notice("simple Ldap: Successfully authenticated by simple ldap ldap_bind for $username");
            ldap_unbind($cnx);
            return 1;
        }

        $this->logger->notice("Simple ldap failed for unknown reason for $username");
        return NULL;
    }

    public function simpleLdapV2($username, $password, $userPrefix="uid", $ldapType=1) {

        echo "username=$username <br>";
        $this->logger->notice("simple Ldap V2: before searchLdap: username=".$username);

        $userSecUtil = $this->container->get('user_security_utility');
        $postfix = $this->getPostfix($ldapType);
        $ldapHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress'.$postfix);
        $ldapPort = $userSecUtil->getSiteSettingParameter('aDLDAPServerPort'.$postfix);
        $this->logger->notice("simple Ldap V2: LDAPHost=".$ldapHost.", LDAPPort=".$ldapPort);

//        $searchRes = $this->searchLdapV2($username,$ldapType);
//        dump($searchRes);
//        if (isset($searchRes['userprincipalname'][0])) {
//            $userPrincipalName = $searchRes['userprincipalname'][0];
//            echo "userPrincipalName=[$userPrincipalName] <br>";
//        } else {
//            $userPrincipalName = $username;
//            echo "userPrincipalName not found in LDAP entry.<br>";
//        }

        //exit('exit simpleLdapV2');

        // Full DN for binding
        //$dn = "CN=path-svc-binduser,OU=Current,OU=People,DC=accounts,DC=ad,DC=wustl,DC=edu";
        //$dn = "path-svc-binduser";
        //$dn = $userPrincipalName;
        //echo "1 simpleLdap: dn=$dn <br>";

        //WCM Ldap
//        $origLdapBindDN = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu'.$postfix);
//        $ldapBindDNArr = explode(";",$origLdapBindDN);
//        $ldapBindDN = $ldapBindDNArr[0];
//        $dn = $userPrefix."=".$username.",".$ldapBindDN;
//        //$dn = $userPrefix."=".$username;
//        echo "2 simpleLdap: dn=$dn <br>";

        //$dn = $this->getPrincipalName($username, $password, $userPrefix="uid", $ldapType=1);
        //$password = "";

        // Connect to LDAP
        $ldapConn = ldap_connect($ldapHost, $ldapPort);
        if (!$ldapConn) {
            die("Failed to connect to LDAP server.");
        }

        // Set LDAP options
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

//        foreach( $ldapBindDNArr as $ldapBindDN) {
//            $ldapBindDN = $userPrefix."=".$username.",".$ldapBindDN;
//            //$ldapBindDN = "cn=$username,ou=NYP Users,ou=External,dc=a,dc=wcmc-ad,dc=net"; //testing
//            //$this->logger->notice("simple Ldap V2: ldapBindDN=".$ldapBindDN);
//            $res = @ldap_bind($ldapConn,$ldapBindDN,$password); //simpleLdap
//            //$res = ldap_bind($cnx,$ldapBindDN,$password); //simpleLdap
//
//            //$res = 1; //testing!!! allow authenticate
//
//            if( $res ) {
//                $this->logger->notice("simple Ldap V2: OK ldapBindDN=".$ldapBindDN);
//                break;
//            } else {
//                $this->logger->notice("simple Ldap V2: NOTOK ldapBindDN=".$ldapBindDN);
//            }
//        }

        // Bind
        $bind = @ldap_bind($ldapConn, $username, $password);

        if ($bind) {
            $this->logger->notice("simple Ldap V2: OK username=".$username);
            //echo "LDAP bind successful dn=$dn <br>";
            return 1;
        } else {
            $this->logger->notice("simple Ldap V2: NOTOK username=".$username." Error=".ldap_error($ldapConn));
            //echo "LDAP bind failed.<br>";
            //echo "Error: " . ldap_error($ldapConn) . "<br>";
        }

        //exit("simpleLdap test");
        ldap_unbind($ldapConn);
        return NULL;
    }


    public function searchLdap($username,$ldapType=1,$withWarning=true) {

        //echo "username=".$username."<br>";
        $userSecUtil = $this->container->get('user_security_utility');

        $postfix = $this->getPostfix($ldapType);

        //$dn = "CN=Users,DC=a,DC=wcmc-ad,DC=net";
        //$dn = "CN=Users";
        //$ldapDc = $this->container->getParameter('ldapou');

        $origLdapBindDN = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu'.$postfix); //old: a.wcmc-ad.net, new: cn=Users,dc=a,dc=wcmc-ad,dc=net

//        $dcArr = explode(".",$ldapDc);
//        foreach( $dcArr as $dc ) {
//            $dn = $dn . ",DC=".$dc;
//        }

        //$dn = $ldapDc;
        //for wcmc must be: cn=Users,dc=a,dc=wcmc-ad,dc=net
        //echo "dn=[".$dn."]<br>";

        //$dn = "cn=read-only-admin,dc=example,dc=com";
        //$dn = "uid=tesla,dc=example,dc=com";
        //echo "dn=".$dn."<br>";

        //$LDAPUserAdmin = $this->container->getParameter('ldapusername');
        $LDAPUserAdmin = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountUserName'.$postfix); //cn=read-only-admin,dc=example,dc=com
        //$LDAPUserPasswordAdmin = $this->container->getParameter('ldappassword');
        $LDAPUserPasswordAdmin = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountPassword'.$postfix);

        if( $LDAPUserAdmin && $LDAPUserPasswordAdmin ) {
            //ok
        } else {
            //no search
            return NULL;
            //return array('givenName'=>$username,'lastName'=>$username,'displayName'=>$username);
        }

        //$LDAPHost = $this->container->getParameter('ldaphost');
        $LDAPHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress'.$postfix);
        //echo "LDAPHost=".$LDAPHost."<br>";
        $cnx = $this->connectToLdap($LDAPHost);

        //$filter="(ObjectClass=Person)";
        //$filter="(CN=".$username.")";
        //$filter = "(sAMAccountName=".$username.")";

        $filter = "(|(CN=$username)(sAMAccountName=$username))"; //use cn or sAMAccountName to search by username (cwid)

        //test
        //$LDAPUserAdmin = "cn=ro_admin,ou=sysadmins,dc=zflexsoftware,dc=com";
        //$LDAPUserPasswordAdmin = "zflexpass";
        //$origLdapBindDN = "ou=users,ou=guests,dc=zflexsoftware,dc=com";

        $res = @ldap_bind($cnx, $LDAPUserAdmin, $LDAPUserPasswordAdmin); //searchLdap
        //$res = $this->ldapBind($LDAPUserAdmin,$LDAPUserPasswordAdmin);
        if( !$res ) {
            $this->logger->error("search Ldap: ldap_bind failed with admin authentication username="."[".$LDAPUserAdmin."]");
                //."; LDAPUserPasswordAdmin="."[".$LDAPUserPasswordAdmin."]");
            //echo "Could not bind to LDAP: user=".$LDAPUserAdmin."<br>";
            //testing!!!: allow to login without LDAP admin bind
            $adminLdapBindRequired = true;
            //$adminLdapBindRequired = false;
            if( $adminLdapBindRequired ) {
                ldap_error($cnx);
                ldap_unbind($cnx);
                //exit("error ldap_bind");
                return NULL;
            }
        } else {
            $this->logger->notice("search Ldap: ldap_bind OK with admin authentication username=" . $LDAPUserAdmin);
            //echo "OK simple LDAP: user=".$LDAPUserAdmin."<br>";
            //exit("OK simple LDAP: user=".$LDAPUserAdmin."<br>");
        }

        $LDAPFieldsToFind = array("mail", "title", "sn", "givenName", "displayName", "telephoneNumber", "mobile", "company"); //sn - lastName
        //$LDAPFieldsToFind = array("sn");   //, "givenName", "displayName", "telephoneNumber");
        //$LDAPFieldsToFind = array("cn", "samaccountname");

        //$origLdapBindDN = "dc=a,dc=wcmc-ad,dc=net"; //testing
        //echo "origLdapBindDN=".$origLdapBindDN."<br>";
        //echo "filter=".$filter."<br>";

        //$sr = ldap_search($cnx, $origLdapBindDN, $filter, $LDAPFieldsToFind);

        $sr = null;
        $ldapBindDNArr = explode(";",$origLdapBindDN);
        //echo "count=".count($ldapBindDNArr)."<br>";
        foreach( $ldapBindDNArr as $ldapBindDN) {
            $this->logger->notice("search Ldap: ldapBindDN=".$ldapBindDN);
            //$sr = ldap_search($cnx, $ldapBindDN, $filter, $LDAPFieldsToFind);
            if( $withWarning ) {
                $sr = ldap_search($cnx, $ldapBindDN, $filter, $LDAPFieldsToFind);
            } else {
                $sr = @ldap_search($cnx, $ldapBindDN, $filter, $LDAPFieldsToFind);
            }

            if( $sr ) {
                $this->logger->notice("search Ldap: ldap_search OK with filter=" . $filter . "; bindDn=".$ldapBindDN);
                $info = ldap_get_entries($cnx, $sr);

//                echo "<pre>";
//                print_r($info);
//                echo "</pre>";

                if( $info["count"] > 0 ) {
                    $this->logger->notice("search Ldap: info: displayName=".$info[0]['displayname'][0]);
                    break;
                } else {
                    $this->logger->notice("search Ldap: ldap_search NOTOK = info null");
                }
            } else {
                $this->logger->error("search Ldap: ldap_search NOTOK with filter=" . $filter . "; bindDn=".$ldapBindDN);
            }
        }

        if( !$sr ) {
            //echo 'Search failed <br>';
            //exit('Search failed');
            $this->logger->error("search Ldap: ldap_search failed with filter=" . $filter);
            ldap_error($cnx);
            ldap_unbind($cnx);
            return NULL;
        }

        $info = ldap_get_entries($cnx, $sr);

        //$this->logger->notice("search Ldap: ldap_search ok with ldapBindDN=".$ldapBindDN."; filter=" . $filter . "; count=".$info["count"]);
        //print_r($info);
        //dump($info); //testing
        //exit('111');

        $searchRes = array();

        for ($x=0; $x<$info["count"]; $x++) {

            if( array_key_exists('ou', $info[$x]) ) {
                $searchRes['ou'] = $info[$x]['ou'][0];
            }
            if( array_key_exists('uid', $info[$x]) ) {
                $searchRes['uid'] = $info[$x]['uid'][0];
            }

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
            if( array_key_exists('mobile', $info[$x]) ) {
                $searchRes['mobile'] = $info[$x]['mobile'][0];
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

            //$this->logger->notice("search Ldap: mail=" . $searchRes['mail'] . "; lastName=".$searchRes['lastName']);

            //we have only one result
            break;
        }

//        if( count($searchRes) == 0 ) {
//            //echo "no search results <br>";
//        }
        //print_r($searchRes);
        //exit('Search OK');
        ldap_unbind($cnx);

        return $searchRes;
    }

    //return user's ldap attributes in array
    public function searchLdapV2($username, $ldapType=1) {
        $userSecUtil = $this->container->get('user_security_utility');
        $postfix = $this->getPostfix($ldapType);

        //$ldapHost = "ldaps://accounts-***.edu";
        $ldapHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress'.$postfix);
        $ldapPort = $userSecUtil->getSiteSettingParameter('aDLDAPServerPort'.$postfix);
        $baseDn = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu'.$postfix);

        $this->logger->notice("searchLdapV2: LDAPHost=[".$ldapHost."], LDAPPort=[$ldapPort], baseDn=[$baseDn]");
        //LDAPHost=[ldap://accounts-ldap.wusm.wustl.edu], LDAPPort=[636], baseDn=[OU=Current,OU=People,DC=accounts,DC=ad,DC=wustl,DC=edu]
        //$ldapHost = "ldaps://accounts-ldap.wusm.wustl.edu";
        //$ldapPort = 636;
        //$baseDn = "OU=Current,OU=People,DC=accounts,DC=ad,DC=wustl,DC=edu";

        // Service account credentials
        //$serviceDn = "path-";
        //$servicePass = "";
        $serviceDn = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountUserName'.$postfix); //cn=read-only-admin,dc=example,dc=com
        $servicePass = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountPassword'.$postfix);
        $this->logger->notice("searchLdapV2: serviceDn=[".$serviceDn."], servicePass=[$servicePass]");

        if (empty($username)) {
            //throw new \Exception("Username is missing.");
            $this->logger->error("searchLdapV2: Username is missing");
            return NULL;
        }

        // Connect
        $ldapConn = ldap_connect($ldapHost, $ldapPort);
        if (!$ldapConn) {
            //throw new \Exception("LDAP connection failed.");
            $this->logger->error("searchLdapV2: DAP connection failed. ".ldap_error($ldapConn));
            return NULL;
        }

        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        // Bind with service account
        if (!@ldap_bind($ldapConn, $serviceDn, $servicePass)) {
            //throw new \Exception("LDAP bind failed: " . ldap_error($ldapConn));
            $this->logger->error("searchLdapV2: LDAP bind failed for $serviceDn: ".ldap_error($ldapConn));
            return NULL;
        }

        // Search for user by sAMAccountName
        $filter = "(sAMAccountName=$username)";
        $attributes = []; // empty array = fetch all attributes
        $search = @ldap_search($ldapConn, $baseDn, $filter, $attributes);

        if (!$search) {
            //throw new \Exception("LDAP search failed: " . ldap_error($ldapConn));
            $this->logger->error("searchLdapV2: LDAP search failed: ".ldap_error($ldapConn));
            return NULL;
        }

        $entries = ldap_get_entries($ldapConn, $search);
        ldap_unbind($ldapConn);

        if ($entries["count"] === 0) {
            //throw new \Exception("User '$username' not found.");
            $this->logger->error("searchLdapV2: user not found: username=[$filter]");
            return NULL;
        }

        return $entries[0]; // return full attribute set for the user
    }

    public function getPostfix($ldapType) {
        if( $ldapType == 2 || $ldapType == '2' ) {
            return "2";
        }
        return "";
    }

    //return ldap connection
    public function connectToLdap( $LDAPHost, $LDAPPort=389 ) {

        //$cnx = @ldap_connect($LDAPHost,$LDAPPort);
        $cnx = ldap_connect($LDAPHost,$LDAPPort);

        if( !$cnx ) {
            $this->logger->warning("Ldap: Could not connect to LDAP");
            return NULL;
        }

        if( !ldap_set_option($cnx, LDAP_OPT_NETWORK_TIMEOUT, 10) ) {
            $this->logger->warning("Ldap: Could not set timeout 10 second");
            ldap_unbind($cnx);
            return NULL;
        }

        if( !ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3) ) {
            $this->logger->warning("Ldap: Could not set version 3");
            ldap_unbind($cnx);
            return NULL;
        }

//        if( !ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0) ) {
//            $this->logger->warning("Ldap: Could not disable referrals");
//            ldap_unbind($cnx);
//            return NULL;
//        }

        if( !ldap_set_option($cnx, LDAP_OPT_SIZELIMIT, 1) ) {
            $this->logger->warning("Ldap: Could not set limit to 1");
            ldap_unbind($cnx);
            return NULL;
        }

        return $cnx;
    }




    /////////////////// AJAX LDAP SEARCH ///////////////
    //serach by cwid, email, last name
    public function searchMultipleUserLdap($searchvalue, $inputType) {

        $userDataArr = null;
        $primaryPublicUserId = null;

        if( $inputType == "primaryPublicUserId" ) {
            $primaryPublicUserId = $searchvalue;
        }

        if( $inputType == "email" ) {
            $emailParts = explode("@",$searchvalue);
            if( count($emailParts) == 2 ) {
                $firstEmailPart = $emailParts[0];
                $secondEmailPart = $emailParts[1];
                $publicUserId = $firstEmailPart;
            }
            $primaryPublicUserId = $firstEmailPart;
        }

        ///////////////// Search Ldap ///////////////////
        $resArr = $this->searchMultipleUserBranchLdap($searchvalue,"lastName",1);
        echo "resArr:<pre>";
        print_r($resArr);
        echo "</pre><br>";
        exit('exit');

        $userCwidDataArr1 = $this->searchMultipleUserBranchLdap($searchvalue,"primaryPublicUserId",1);
        $userCwidDataArr2 = $this->searchMultipleUserBranchLdap($searchvalue,"primaryPublicUserId",2);
        echo "userCwidDataArr1:<pre>";
        print_r($userCwidDataArr1);
        echo "</pre><br>";

        $userLastnameDataArr1 = $this->searchMultipleUserBranchLdap($searchvalue,"lastName",1);
        $userLastnameDataArr2 = $this->searchMultipleUserBranchLdap($searchvalue,"lastName",2);
        echo "userLastnameDataArr1:<pre>";
        print_r($userLastnameDataArr1);
        echo "</pre><br>";
        ///////////////// EOF Search Ldap ///////////////////
        exit('exit');

        return $userDataArr;
    }
    public function searchMultipleUserBranchLdap( $searchvalue, $seacrhType, $ldapType ) {

        //Server: ldap.forumsys.com
        //Port: 389
        //Bind DN: cn=read-only-admin,dc=example,dc=com
        //Bind Password: password
        //All user passwords are password.
        //ou=mathematicians,dc=example,dc=com
        //riemann
        //gauss
        //euler
        //euclid


        $userSecUtil = $this->container->get('user_security_utility');

        $postfix = $this->getPostfix($ldapType);

        $origLdapBindDN = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu'.$postfix); //old: a.wcmc-ad.net, new: cn=Users,dc=a,dc=wcmc-ad,dc=net

        //$LDAPUserAdmin = $this->container->getParameter('ldapusername');
        $LDAPUserAdmin = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountUserName'.$postfix); //cn=read-only-admin,dc=example,dc=com
        //$LDAPUserPasswordAdmin = $this->container->getParameter('ldappassword');
        $LDAPUserPasswordAdmin = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountPassword'.$postfix);

        if( $LDAPUserAdmin && $LDAPUserPasswordAdmin ) {
            //ok
        } else {
            //no search
            return NULL;
            //return array('givenName'=>$username,'lastName'=>$username,'displayName'=>$username);
        }

        //$LDAPHost = $this->container->getParameter('ldaphost');
        $LDAPHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress'.$postfix);
        //echo "LDAPHost=".$LDAPHost."<br>";
        $cnx = $this->connectToLdap($LDAPHost);

        if( $seacrhType == "primaryPublicUserId" ) {
            $filter = "(cn=" . $searchvalue . ")";
        }
        elseif( $seacrhType == "lastName" ) {
            $filter = "(sn=*" . $searchvalue . "*)";
        }

        $res = @ldap_bind($cnx, $LDAPUserAdmin, $LDAPUserPasswordAdmin); //searchMultipleUserBranchLdap
        //$res = $this->ldapBind($LDAPUserAdmin,$LDAPUserPasswordAdmin);
        if( !$res ) {
            $this->logger->error("search Ldap: ldap_bind failed with admin authentication username=" . $LDAPUserAdmin);
            echo "Could not bind to LDAP: user=".$LDAPUserAdmin."<br>";
            ldap_error($cnx);
            ldap_unbind($cnx);
            //exit("error ldap_bind");
            return NULL;
        } else {
            //$this->logger->notice("search Ldap: ldap_bind OK with admin authentication username=" . $LDAPUserAdmin);
            echo "OK simple LDAP: user=".$LDAPUserAdmin."<br>";
            //exit("OK simple LDAP: user=".$LDAPUserAdmin."<br>");
        }

        echo "origLdapBindDN=[".$origLdapBindDN."]<br>";

        $LDAPFieldsToFind = array("cn", "mail", "title", "sn", "givenName", "displayName", "telephoneNumber", "mobile", "company"); //sn - lastName
        //$LDAPFieldsToFind = array("cn", "sn", "displayName"); //sn - lastName

        $displayNameArr = array();
        $infoArr = array();
        $sr = null;
        $ldapBindDNArr = explode(";",$origLdapBindDN);
        //echo "count=".count($ldapBindDNArr)."<br>";
        foreach( $ldapBindDNArr as $ldapBindDN) {
            //$this->logger->notice("search Ldap: ldapBindDN=".$ldapBindDN);
            echo "filter=".$filter."; ldapBindDN=[".$ldapBindDN."]<br>";
            $sr = ldap_search($cnx, $ldapBindDN, $filter, $LDAPFieldsToFind);
            //$filter = "(uid=*)";
            //$sr = ldap_search($cnx, $ldapBindDN, $filter);

//            if(0) {
//                $entry = ldap_first_entry($cnx, $sr);
//                do {
//                    $dn = ldap_get_dn($cnx, $entry);
//                    echo "DN=[" . $dn . "]<br>";
//                } while ($entry = ldap_next_entry($cnx, $entry));
//            }

            if(1) {
                $info = ldap_get_entries($cnx, $sr);
                echo "info count=" . $info["count"] . "<br>";
                //$info = $this->getLdapEntries($cnx, $sr);
                echo "<br><br>############info:<pre>";
                print_r($info);
                echo "</pre>#############<br><br>";

                foreach($info as $infoThis) {
                    echo "sn=".$infoThis['sn'][0];
                }

                $infoArr[] = $info;

//                foreach($info as $infoThis) {
//                    $displayname = $infoThis["displayname"];
//                    echo "displayname=" . $displayname . "<br>";
//                    $displayNameArr[] = $displayname;
//                }

                for ($x = 0; $x < $info["count"]; $x++) {
                    //echo "<br><br>############info:<pre>";
                    //print_r($info[$x]);
                    //echo "</pre>#############<br><br>";
                    //$cn = $info[$x]['cn'][0];
                    //echo "cn=".$cn."<br>";
                    //$sn = $info[$x]['sn'][0];
                    //echo "sn=".$sn."<br>";
                    echo "############info[x]:<pre>";
                    print_r($info[$x]);
                    echo "</pre>#############<br>";
                    $displayname = $info[$x]["displayname"][0];
                    $displayNameArr[] = $displayname;

                    //foreach($info[$x]["displayname"] as $displayname) {
                    //    $displayNameArr[] = $displayname;
                    //    echo "displayname=" . $displayname . "<br>";
                    //}

                    //echo "*******:<pre>";
                    //print_r($info[$x]["displayname"]);
                    //echo "</pre>#############<br>";
                }
            }

        }//foreach $ldapBindDN

        //$info = ldap_get_entries($cnx, $sr);

        echo "<br><br>############displayNameArr:<pre>";
        print_r($displayNameArr);
        echo "</pre>#############<br><br>";
        exit('infoArr count='.count($displayNameArr));

        $searchResArr = array();

        foreach($infoArr as $info) {
            $searchRes = array();

            for ($x = 0; $x < $info["count"]; $x++) {

                if (array_key_exists('ou', $info[$x])) {
                    $searchRes['ou'] = $info[$x]['ou'][0];
                }
                if (array_key_exists('uid', $info[$x])) {
                    $searchRes['uid'] = $info[$x]['uid'][0];
                }

                if (array_key_exists('cn', $info[$x])) {
                    $searchRes['cn'] = $info[$x]['cn'][0];
                }
                if (array_key_exists('mail', $info[$x])) {
                    $searchRes['mail'] = $info[$x]['mail'][0];
                }
                if (array_key_exists('title', $info[$x])) {
                    $searchRes['title'] = $info[$x]['title'][0];
                }
                if (array_key_exists('givenname', $info[$x])) {
                    $searchRes['givenName'] = $info[$x]['givenname'][0];
                }
                if (array_key_exists('sn', $info[$x])) {
                    $searchRes['lastName'] = $info[$x]['sn'][0];
                }
                if (array_key_exists('displayname', $info[$x])) {
                    $searchRes['displayName'] = $info[$x]['displayname'][0];
                }
                if (array_key_exists('telephonenumber', $info[$x])) {
                    $searchRes['telephoneNumber'] = $info[$x]['telephonenumber'][0];
                }
                if( array_key_exists('mobile', $info[$x]) ) {
                    $searchRes['mobile'] = $info[$x]['mobile'][0];
                }
                if (array_key_exists('company', $info[$x])) {
                    $searchRes['company'] = $info[$x]['company'][0];    //not used currently
                }

                if (array_key_exists('givenName', $searchRes) && !$searchRes['givenName']) {
                    $searchRes['givenName'] = "";   //$username;
                }

                if (array_key_exists('lastName', $searchRes) && !$searchRes['lastName']) {
                    $searchRes['lastName'] = "";    //$username;
                }

                //print "\nActive Directory says that:<br />";
                //print "givenName is: ".$searchRes['givenName']."<br>";
                //print "familyName is: ".$searchRes['lastName']."<br>";
                //print_r($info[$x]);

                //$this->logger->notice("search Ldap: mail=" . $searchRes['mail'] . "; lastName=".$searchRes['lastName']);

                //we have only one result
                //break;
            }

            $searchResArr[] = $searchRes;
        }

//        if( count($searchRes) == 0 ) {
//            //echo "no search results <br>";
//        }
        //print_r($searchRes);
        //exit('Search OK');

        ldap_unbind($cnx);

        return $searchResArr;

    }
    public function getLdapEntries($conn,$srchRslt) {
        // will use ldap_get_values_len() instead and build the array
        // note: it's similar with the array returned by
        // ldap_get_entries() except it has no "count" elements
        $i=0;
        $entry = ldap_first_entry($conn, $srchRslt);
        do {
            $attributes = ldap_get_attributes($conn, $entry);
            for($j=0; $j<$attributes['count']; $j++) {
                $values = ldap_get_values_len($conn, $entry,$attributes[$j]);
                $srchRslt[$i][$attributes[$j]] = $values;
            }
            $i++;
        }
        while ($entry = ldap_next_entry($conn, $entry));
        //we're done
        return ($srchRslt);
    }
    /////////////////// EOF AJAX LDAP SEARCH ///////////////
} 