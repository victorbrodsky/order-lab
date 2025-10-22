<?php
declare(strict_types=1);

/**
 * Copyright (c) 2017 Cornell University
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 */

namespace App\UserdirectoryBundle\Security\Authentication;

use App\UserdirectoryBundle\Entity\IdentifierTypeList;
use App\UserdirectoryBundle\Entity\UsernameType;
use App\UserdirectoryBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use OneLogin\Saml2\Auth;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LdapAuthUtil
{
    private ContainerInterface $container;
    private EntityManagerInterface $em;
    private $logger;
    private RequestStack $requestStack;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em,
        RequestStack $requestStack,
        UserPasswordHasherInterface $passwordHasher
    ) {
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
        $this->logger->notice("Start Ldap Authentication: ldapType={$ldapType}");

        $username = $token->getUsername();
        $password = $token->getCredentials();
        //return $user = $authUtil->findUserByUsername($username); //testing, overwrite login

        //get clean username
        $userSecUtil = $this->container->get('user_security_utility');
        $usernameClean = $userSecUtil->createCleanUsername($username);
        $this->logger->notice("Start Ldap Authentication: username=[$username],usernameClean=[$usernameClean]");
        //username=[brodsky_@_ldap-user],usernameClean=[brodsky]

        $userSecUtil = $this->container->get('user_security_utility');
        $postfix = $this->getPostfix($ldapType);
        $ldapBindDN = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu'.$postfix);

        $ldapUserData = null;
        $ldapRes = null;

        try {
            if (str_contains((string)$ldapBindDN, 'dc=wcmc-ad')) {
                // WCMC-specific flow
                $this->logger->notice("Using ldapBindV1 for host with wcmc-ad in OU");
                $ldapRes = $this->ldapBindV1($usernameClean, $password, $ldapType);
            } else {
                // Generic flow: first search, then bind by userPrincipalName if available
                $this->logger->notice("Searching LDAP for usernameClean={$usernameClean}");
                $ldapUserData = $this->searchLdapV2($usernameClean, $ldapType);
                $upn = $this->extractAttributeValue($ldapUserData, 'userPrincipalName') ?? $usernameClean;
                $this->logger->notice("Binding with UPN/username={$upn}");
                $ldapRes = $this->ldapBindV2($upn, $password, $ldapType);
            }
        } catch (\Throwable $e) {
            $this->logger->error('LDAP auth flow exception: ' . $e->getMessage());
            $ldapRes = null;
        }

        if ($ldapRes === null) {
            $this->logger->error("Ldap Authentication: can not ldap bind user by usernameClean=[{$usernameClean}]");
            $user = $authUtil->findUserByUsername($username);
            $authUtil->validateFailedAttempts($user);
            return null;
        }

        //check if user already exists in DB
        $user = $authUtil->findUserByUsername($username);
        if ($user) {
            $this->logger->notice("Authenticated successfully, existing user found in DB by username={$username}");
            if ($authUtil->canLogin($user) === false) {
                return null;
            }
            return $user;
        }

        // Create new user if not exists
        return $this->createNewLdapUser($username, $ldapType, $ldapUserData);
    }

    /**
     * Create & persist a new LDAP-based user.
     */
    public function createNewLdapUser($username, $ldapType = 1, $ldapUserData = null)
    {
        $user = $this->getUserInLdap($username, $ldapType, $ldapUserData);
        if (!$user) {
            $this->logger->error("createNewLdapUser: LDAP user not found/construct failed for {$username}");
            return null;
        }

        $userManager = $this->container->get('user_manager');
        // user_manager->updateUser expects the framework user model; ensure compatible
        $userManager->updateUser($user);

        return $user;
    }

    /**
     * Build a User object from LDAP attributes.
     */
    public function getUserInLdap($username, $ldapType = 1, $ldapUserData = null)
    {
        $userSecUtil = $this->container->get('user_security_utility');
        $usernameClean = $userSecUtil->createCleanUsername($username);
        $usernamePrefix = $userSecUtil->getUsernamePrefix($username);

        if( !$ldapUserData ) {
            $ldapUserData = $this->searchLdap($usernameClean, $ldapType);
        }

        //dump($ldapUserData); //testing

        if( $ldapUserData == NULL || count($ldapUserData) == 0 ) {
            $this->logger->error("Ldap Search: ldapUserData exists for usernameClean=" . $usernameClean);
            return NULL;
        } else {
            $this->logger->notice("Ldap Search: ldapUserData is empty for  usernameClean=" . $usernameClean);
        }

        $this->logger->notice("Ldap Search: create a new user (not in DB) found by username={$username}");
        $user = $userSecUtil->constractNewUser($username);
        $user->setCreatedby('ldap');

        //modify user: set keytype and primary public user id
        $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);
        if ($userkeytype) {
            $user->setKeytype($userkeytype);
        }
        $user->setPrimaryPublicUserId($usernameClean);

        // Normalize to lowercase keys for case-insensitive access
        $normalized = array_change_key_case($ldapUserData, CASE_LOWER);

        if (!empty($normalized['mail'])) {
            $user->setEmail($normalized['mail']);
        }
        if (!empty($normalized['givenname'])) {
            $user->setFirstName($normalized['givenname']);
        }
        if (!empty($normalized['sn'])) {
            $user->setLastName($normalized['sn']);
        }
        if (!empty($normalized['displayname'])) {
            $user->setDisplayName($normalized['displayname']);
        }
        if (!empty($normalized['telephonenumber'])) {
            $user->setPreferredPhone($normalized['telephonenumber']);
        }
        if (!empty($normalized['mobile'])) {
            $user->setPreferredMobilePhone($normalized['mobile']);
        }

        return $user;
    }

    //return 1 if bind successful
    //return NULL if failed
    public function ldapBindV1( $username, $password, $ldapType=1 ) {
        //return 1; //testing!!!: enable testing login
        //step 1
        if( $this->simpleLdapV1($username,$password,"cn",$ldapType) ) {
            return 1;
        }
        if ($this->simpleLdapV1($username, $password, 'uid', $ldapType)) {
            return 1;
        }
        return null;
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

        $LDAPHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress' . $postfix);
        $LDAPPort = $userSecUtil->getSiteSettingParameter('aDLDAPServerPort' . $postfix);
        $this->logger->notice("simpleLdapV1: LDAPHost={$LDAPHost}, LDAPPort={$LDAPPort}");

        $origLdapBindDN = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu' . $postfix);
        if (empty($origLdapBindDN)) {
            return null;
        }

        $cnx = $this->connectToLdap($LDAPHost, (int)$LDAPPort);
        if (!$cnx) {
            return null;
        }

        $ldapBindDNArr = explode(';', $origLdapBindDN);
        foreach ($ldapBindDNArr as $ldapBindDN) {
            $ldapBindDN = trim($ldapBindDN);
            if ($ldapBindDN === '') {
                continue;
            }

            // Construct bind DN possibilities
            $possibleDns = [
                "{$userPrefix}={$username},{$ldapBindDN}",
                "cn={$username},{$ldapBindDN}",
                "{$username}" // sometimes simple uid only is used
            ];

            foreach ($possibleDns as $dn) {
                if ($dn === '') {
                    continue;
                }
                $this->logger->notice("simpleLdapV1: attempting bind DN={$dn}");
                if ($this->bindWithCredentials($cnx, $dn, $password)) {
                    ldap_unbind($cnx);
                    return 1;
                }
            }
        }

        ldap_unbind($cnx);
        $this->logger->notice("Simple ldap failed for username={$username}");
        return null;
    }

    /**
     * Version 2: simple bind by username (UPN) on provided host:port
     */
    public function simpleLdapV2($username, $password, $userPrefix = "uid", $ldapType = 1)
    {
        $userSecUtil = $this->container->get('user_security_utility');
        $postfix = $this->getPostfix($ldapType);
        $ldapHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress' . $postfix);
        $ldapPort = $userSecUtil->getSiteSettingParameter('aDLDAPServerPort' . $postfix);
        $this->logger->notice("simpleLdapV2: LDAPHost={$ldapHost}, LDAPPort={$ldapPort}");

        $ldapConn = $this->connectToLdap($ldapHost, (int)$ldapPort);
        if (!$ldapConn) {
            return null;
        }

        try {
            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

            if (@ldap_bind($ldapConn, $username, $password)) {
                return 1;
            }
        } finally {
            ldap_unbind($ldapConn);
        }

        return null;
    }

    /**
     * Helper: connect to LDAP server with sane defaults.
     */
    public function connectToLdap($LDAPHost, $LDAPPort = 389)
    {
        if (empty($LDAPHost)) {
            $this->logger->warning('connectToLdap: empty LDAPHost');
            return null;
        }

        $cnx = @ldap_connect($LDAPHost, $LDAPPort);
        if (!$cnx) {
            $this->logger->error("Ldap: Failed to connect to {$LDAPHost}:{$LDAPPort}");
            return null;
        }

        ldap_set_option($cnx, LDAP_OPT_NETWORK_TIMEOUT, 10);
        ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($cnx, LDAP_OPT_SIZELIMIT, 1);

        return $cnx;
    }

    /**
     * Bind helper that returns boolean.
     */
    private function bindWithCredentials($cnx, $dn, $password): bool
    {
        try {
            $res = @ldap_bind($cnx, $dn, $password);
            return $res === true;
        } catch (\Throwable $e) {
            $this->logger->error('LDAP bind error: ' . $e->getMessage());
            return false;
        }
    }

    //return $searchRes key->value array (key is case sensitive)
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

    /**
     * Search LDAP and return a simple associative array of attributes (case-insensitive keys).
     */
    public function searchLdapV2($username, $ldapType = 1)
    {
        $userSecUtil = $this->container->get('user_security_utility');
        $postfix = $this->getPostfix($ldapType);

        $ldapHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress' . $postfix);
        $ldapPort = $userSecUtil->getSiteSettingParameter('aDLDAPServerPort' . $postfix);
        $baseDn = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu' . $postfix);

        $serviceDn = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountUserName' . $postfix);
        $servicePass = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountPassword' . $postfix);

        if (empty($username) || empty($serviceDn) || empty($servicePass)) {
            $this->logger->warning('searchLdapV2: missing username or service account');
            return null;
        }

        $ldapConn = $this->connectToLdap($ldapHost, (int)$ldapPort);
        if (!$ldapConn) {
            return null;
        }

        try {
            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

            if (!@ldap_bind($ldapConn, $serviceDn, $servicePass)) {
                $this->logger->error("searchLdapV2: service bind failed for {$serviceDn}");
                return null;
            }

            $filter = "(sAMAccountName={$username})";
            $attributes = []; // fetch all
            $search = @ldap_search($ldapConn, $baseDn, $filter, $attributes);
            if (!$search) {
                $this->logger->error("searchLdapV2: ldap_search failed with filter={$filter}, baseDn={$baseDn}");
                return null;
            }

            $info = ldap_get_entries($ldapConn, $search);
            if (empty($info) || !isset($info['count']) || $info['count'] === 0) {
                $this->logger->notice("searchLdapV2: user not found by filter={$filter}");
                return null;
            }

            // Normalize the first entry into a simple associative array (lowercase keys).
            $entry = $info[0];
            $result = [];
            foreach ($entry as $k => $v) {
                if (!is_int($k) && is_array($v) && isset($v[0])) {
                    $result[strtolower($k)] = $v[0];
                }
            }

            return $result;
        } finally {
            ldap_unbind($ldapConn);
        }
    }

    /**
     * Extract first attribute value from LDAP normalized result (array with lowercase keys).
     */
    private function extractAttributeValue(?array $ldapData, string $attribute): ?string
    {
        if (empty($ldapData)) {
            return null;
        }
        $key = strtolower($attribute);
        return $ldapData[$key] ?? null;
    }

    public function getPostfix($ldapType)
    {
        return ($ldapType == 2 || $ldapType === '2') ? '2' : '';
    }

    /* Note: many auxiliary AJAX search methods were removed or simplified.
       If you need them re-added, we should implement them using the same helpers above
       and avoid echoes/exits. */
}