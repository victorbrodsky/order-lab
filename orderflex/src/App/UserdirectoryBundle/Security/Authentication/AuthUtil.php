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

class AuthUtil {

    private $container;        //container
    private $em;        //entity manager
    private $logger;
    private $requestStack;
    private $passwordHasher;
    //private $samlConfigProvider;
    //protected $session;
    //private $hasherFactory;

    //private $supportedUsertypesExternal = array('external');
    //private $supportedUsertypesLdap = null; //array('ldap-user');
    //private $supportedUsertypesLocal = array('local-user');

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em,
        //Session $session,
        RequestStack $requestStack,
        UserPasswordHasherInterface $passwordHasher
        //SamlConfigProvider $samlConfigProvider
        //PasswordHasherFactory $hasherFactory
    )
    {
        $this->container = $container;
        $this->em = $em;
        $this->requestStack = $requestStack;
        //$this->session = $session;
        $this->logger = $container->get('logger');

        $this->passwordHasher = $passwordHasher;
        //$this->hasherFactory = $hasherFactory;

        //set $supportedUsertypesLdap from defaultPrimaryPublicUserIdType
//        $userSecUtil = $this->container->get('user_security_utility');
//        $defaultPrimaryPublicUserIdType = $userSecUtil->getSiteSettingParameter('defaultPrimaryPublicUserIdType');
//        if( $defaultPrimaryPublicUserIdType ) {
//            $this->supportedUsertypesLdap[] = $defaultPrimaryPublicUserIdType->getAbbreviation();
//        }
    }


    public function samlAuthentication($token) {

        if( !$token->getCredentials() ) {
            //empty password
            $this->logger->notice("samlAuthentication: no credentials in the token => exit without authentication.");
            return NULL;
        }

        //get clean username
        //$userSecUtil = $this->container->get('user_security_utility');
        //$usernameClean = $userSecUtil->createCleanUsername($token->getUsername());

        $username = $token->getUsername();

        $this->logger->notice("samlAuthentication: get user by uesrname=".$username);

        if( $username ) {
            $emailArr = explode('@', $username);
            $domain = $emailArr[1];
            $authUtil = $this->container->get('authenticator_utility');
            $authUtil->samlAuthenticationByDomain($domain);
            //TODO: use stay and return user (if success) or null (if fail) like all other auth methods.
        }

        //check if user already exists in DB
        $user = $this->findUserByUsername($username);

        if( $user ) {
            $userEmail = $user->getSingleEmail();
            if( $userEmail ) {
                exit('samlAuthentication: OK user='.$user->getId());
            }
        }

        return NULL;
    }
    //$stay=false
    public function samlAuthenticationByDomain( $domain, $lastRoute=null ) {
        if( !$domain ) {
            return NULL;
        }

        $samlConfigProviderUtil = $this->container->get('saml_config_provider_util');

        //echo "domain=$domain <br>";
        //$emailArr = explode('@', $email);
        //$domain = $emailArr[1];
    
        //$user = $this->em->getRepository(User::class)->findOneUserByUserInfoUseridEmail($email);

        $config = $samlConfigProviderUtil->getConfig($domain);
        if( !$config ) {
            return NULL;
        }

        $auth = new Auth($config['settings']);

        //exit('$lastRoute='.$lastRoute);
        //TODO: $lastRoute is not working
        $parameters = array();
        $parameters['RelayState'] = $lastRoute;
        //$auth->login(null,$parameters); //make redirect to SAML page and after to $lastRoute

        $stay = true;
        $stay = false;
        $urlString = $auth->login(null, $parameters, false, $stay, true); //make redirect to SAML page
        //exit('$urlString='.$urlString);

//        if( $stay == true ) {
//            $newTargetUrl = "";
//            //$parameters, $forceAuthn, $isPassive, $stay, $setNameIdPolicy, $nameIdValueReq
//            $urlString = $auth->login(null, array(), false, $stay, true); //make redirect to SAML page
//            exit('$urlString='.$urlString);
//        } else {
//            $auth->login($lastRoute); //make redirect to SAML page and after to $lastRoute
//        }

        if(0) {
            $errors = $auth->getErrors();  // This method receives an array with the errors
            // that could took place during the process

            if (!empty($errors)) {
                echo '<p>', implode(', ', $errors), '</p>';
            }

            // This check if the response was
            if (!$auth->isAuthenticated()) {      // successfully validated and the user
                echo "<p>Not authenticated</p>";  // data retrieved or not
                exit('not authenticated');
            } else {
                exit('authenticated!!!');
            }
        }
    }
    public function samlAuthenticationByEmail( $email ) {
        if( !$email ) {
            return NULL;
        }

        $samlConfigProviderUtil = $this->container->get('saml_config_provider_util');

        $user = $this->em->getRepository(User::class)->findOneUserByUserInfoUseridEmail($email);

        $config = $samlConfigProviderUtil->getConfig($user->getSingleEmail());
        if( !$config ) {
            return NULL;
        }
        
        $auth = new Auth($config['settings']);
        $auth->login();

        $errors = $auth->getErrors();  // This method receives an array with the errors
        // that could took place during the process

        if (!empty($errors)) {
            echo '<p>', implode(', ', $errors), '</p>';
        }

        // This check if the response was
        if( !$auth->isAuthenticated() ) {      // successfully validated and the user
            echo "<p>Not authenticated</p>";  // data retrieved or not
            exit('not authenticated');
        } else {
            exit('authenticated!!!');
        }
    }

    public function LocalAuthentication($token) {

        //echo "LocalAuthentication<br>";
        //echo "username=".$token->getUsername()."<br>";
        //exit();
        //return NULL;

        if( !$token->getCredentials() ) {
            //empty password
            $this->logger->notice("LocalAuthentication: no credentials in the token => exit without authentication.");
            return NULL;
        }

        //get clean username
        //$userSecUtil = $this->container->get('user_security_utility');
        //$usernameClean = $userSecUtil->createCleanUsername($token->getUsername());

        $this->logger->notice("LocalAuthentication: get user by uesrname=".$token->getUsername());

        //check if user already exists in DB
        $user = $this->findUserByUsername($token->getUsername());
        //return $user; //testing
        //echo "Local DB user =".$user."<br>";
        //exit();

        if( $user ) {
            $this->logger->notice("Local Authentication: local user found by username=".$token->getUsername()."; userId=".$user->getId());

            if( !$this->canLogin($user) ) {
                //exit("User can not login");
                $this->logger->notice("User can not login ".$user);
                return NULL;
            }

            //check password
            $encodeRes = $this->isPasswordValid($user,$token->getCredentials()); //does not work
            //$encodeRes = 1; //testing!!! allow authenticate with wrong password

            if( $encodeRes ) {
                //exit('password invalid ['.$token->getCredentials().']');
                return $user;
            } else {
                $this->validateFailedAttempts($user);
                $this->logger->notice("Local Authentication: password is invalid");
                return NULL;
            }

        }

        return NULL;
    }


    public function PacsvendorAuthentication($token) {

        //echo "PacsvendorAuthentication<br>";
        //exit();

        $pacsvendorUtil = new PacsvendorUtil();

        $user = $pacsvendorUtil->pacsvendorAuthenticateToken( $token, $this->container, $this->em );

        //return $user; //testing!!! allow auth external

        if( $user ) {
            //echo "pacsvendor user found=".$user->getUsername()."<br>";

            if( $this->canLogin($user) === false ) {
                $this->logger->notice("PacsvendorAuthentication: User can not login ".$user);
                return NULL;
            }

            return $user;
        }

        return NULL;
    }

    public function LdapAuthentication($token, $ldapType=1) {
        return $this->LdapAuthenticationByUsernamePassword($token->getUsername(),$token->getCredentials(),$ldapType);
    }
    public function LdapAuthenticationByUsernamePassword($username, $password, $ldapType=1) {

        $this->logger->notice("Ldap Authentication: ldapType=[$ldapType]");
        //exit("Ldap Authentication: ldapType=[$ldapType]");

        //return $user = $this->findUserByUsername($username); //testing, overwrite login

        //get clean username
        $userSecUtil = $this->container->get('user_security_utility');
        $usernameClean = $userSecUtil->createCleanUsername($username);
        //$usernamePrefix = $userSecUtil->getUsernamePrefix($username);
        //exit("usernameClean=[$usernameClean], susernamePrefix=[$usernamePrefix]");

        $searchRes = null;

        //if user exists in ldap, try bind this user and password
        $ldapRes = $this->ldapBind($usernameClean,$password,$ldapType);
        if( $ldapRes == NULL ) {
            //exit('ldap failed');
            //$this->logger->error("LdapAuthentication: can not bind user by usernameClean=[".$usernameClean."]; token=[".$token->getCredentials()."]");
            $this->logger->error("Ldap Authentication: can not bind user by usernameClean=[".$usernameClean."];");

            $user = $this->findUserByUsername($username);

//            //if user found, try to authenticate by identifier
//            if( $user ) {
//                $this->simpleIdentifierAuthetication($token);
//            }

            $this->validateFailedAttempts($user);

            return NULL;
        }
        //exit('ldap success');

        //check if user already exists in DB
        $user = $this->findUserByUsername($username);
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
            return $this->createNewLdapUser($username, $ldapType);
        }

        return NULL;

//        $searchRes = $this->searchLdap($usernameClean,$ldapType);
//
//        if( $searchRes == NULL || count($searchRes) == 0 ) {
//            $this->logger->error("Ldap Authentication: can not find user by usernameClean=" . $usernameClean);
//            return NULL;
//        } else {
//            $this->logger->notice("Ldap Authentication: user found by  usernameClean=" . $usernameClean);
//        }
//
//        $this->logger->notice("LdapAuthentication: create a new user found by token->getUsername()=".$username);
//        $user = $userSecUtil->constractNewUser($username);
//        //echo "user=".$user->getUsername()."<br>";
//
//        $user->setCreatedby('ldap');
//
//        //modify user: set keytype and primary public user id
//        $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);
//
//        if( !$userkeytype ) {
//            $userUtil = new UserUtil();
//            $count_usernameTypeList = $userUtil->generateUsernameTypes($this->em);
//            $userkeytype = $userSecUtil->getUsernameType($this->usernamePrefix);
//            //echo "userkeytype=".$userkeytype."<br>";
//        }
//
//        $user->setKeytype($userkeytype);
//        $user->setPrimaryPublicUserId($usernameClean);
//
//        if( $searchRes ) {
//            $user->setEmail($searchRes['mail']);
//            $user->setFirstName($searchRes['givenName']);
//            $user->setLastName($searchRes['lastName']);
//            $user->setDisplayName($searchRes['displayName']);
//            $user->setPreferredPhone($searchRes['telephoneNumber']);
//        }
//
//        //cwid is admin cwid
//        //if( $user->getUsername() == "cwid1_@_ldap-user" || $user->getUsername() == "cwid2_@_ldap-user" ) {
//        //    $user->addRole('ROLE_PLATFORM_ADMIN');
//        //}
//
//        //exit('ldap ok');
//
//        //////////////////// save user to DB ////////////////////
//        $userManager = $this->container->get('fos_user.user_manager');
//        $userManager->updateUser($user);
//        return $user;
    }

    //Guard auth requires if user exists
    //$username - primaryPublicUserId and userkeytype (i.e. cwid1_@_ldap-user)
    public function getUserInLdap( $username, $ldapType=1 ) {
        // Construct a new LDAP user
        $userSecUtil = $this->container->get('user_security_utility');

        $usernameClean = $userSecUtil->createCleanUsername($username);
        $usernamePrefix = $userSecUtil->getUsernamePrefix($username);

        $searchRes = $this->searchLdap($usernameClean,$ldapType);

        if( $searchRes == NULL || count($searchRes) == 0 ) {
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

        if( $searchRes ) {

            //$user->setEmail($searchRes['mail']);
            if( array_key_exists('mail', $searchRes) ) {
                $user->setEmail($searchRes['mail']);
            }

            if( array_key_exists('givenName', $searchRes) ) {
                $user->setFirstName($searchRes['givenName']);
            }

            if( array_key_exists('lastName', $searchRes) ) {
                $user->setLastName($searchRes['lastName']);
            }

            if( array_key_exists('displayName', $searchRes) ) {
                $user->setDisplayName($searchRes['displayName']);
            }

            if( array_key_exists('telephoneNumber', $searchRes) ) {
                $user->setPreferredPhone($searchRes['telephoneNumber']);
            }

            if( array_key_exists('mobile', $searchRes) ) {
                $user->setPreferredMobilePhone($searchRes['mobile']);
            }
        }

        return $user;
    }

    //$username - primaryPublicUserId and userkeytype (i.e. cwid1_@_ldap-user)
    public function createNewLdapUser( $username, $ldapType=1 ) {
        // Construct a new LDAP user
        $user = $this->getUserInLdap($username,$ldapType);

        if( !$user ) {
            return NULL;
        }

        //cwid is admin cwid
        //if( $user->getUsername() == "cwid1_@_ldap-user" || $user->getUsername() == "cwid2_@_ldap-user" ) {
        //    $user->addRole('ROLE_PLATFORM_ADMIN');
        //}
        //dump($user);
        //exit('ldap ok');

        //////////////////// save user to DB ////////////////////
        //$userManager = $this->container->get('fos_user.user_manager');
        $userManager = $this->container->get('user_manager');
        $userManager->updateUser($user);

        return $user;
    }

    //Do not use search before bind. Search might take a long time
    public function LdapAuthenticationWithSearch($token, $ldapType=1) {

        //$this->logger->notice("LdapAuthentication: LDAP authenticate user by token->getUsername()=".$token->getUsername());
        //echo "LdapAuthentication<br>";
        //exit();

//        if( !$this->supportedUsertypesLdap ) {
//            $this->logger->notice('LDAP usertype is not set.');
//            return false;
//        }

        //get clean username
        $userSecUtil = $this->container->get('user_security_utility');
        $usernameClean = $userSecUtil->createCleanUsername($token->getUsername());

        $usernamePrefix = $userSecUtil->getUsernamePrefix($token->getUsername());

        $searchRes = null;
        $withNewUserPrePopulation = true;
        //$withNewUserPrePopulation = false; //testing
        //$userSearchRequired = true;
        $userSearchRequired = false; //auth without required user search is more flexible if admin bind failed
        if( $withNewUserPrePopulation ) {

            //////////////// first search this user if exists in ldap directory ////////////////
            $searchRes = $this->searchLdap($usernameClean,$ldapType);
            //////////////// EOF first search this user if exists in ldap directory ////////////////

            if( $searchRes == NULL || count($searchRes) == 0 ) {
                $this->logger->error("LdapAuthentication: can not find user by usernameClean=" . $usernameClean);
                //$this->logger->error("LdapAuthentication: can not find user by usernameClean=[" . $usernameClean . "]; token=[" . $token->getCredentials() . "]");
                //$this->logger->error(print_r($searchRes));

                if($userSearchRequired) {
                    return NULL;
                }
            } else {
                $this->logger->notice("LdapAuthentication: user found by  usernameClean=" . $usernameClean);
                /////// EOF testing ///////
//                $user = $this->findUserByUsername($token->getUsername());
//                if( $user ) {
//                    $userEmail = $user->getSingleEmail();
//                    if (strpos((string)$userEmail, '@nyp.org') !== false) {
//                        $this->logger->error("LdapAuthentication: NYP user found by usernameClean=[" . $usernameClean . "]; token=[" . $token->getCredentials() . "]");
//                    }
//                }
                /////// testing ///////
            }
        }


        //echo "user exists in ldap directory<br>";
        //$this->logger->notice("LdapAuthentication: user found in LDAP by usernameClean=".$usernameClean);

        //if user exists in ldap, try bind this user and password
        $ldapRes = $this->ldapBind($usernameClean,$token->getCredentials(),$ldapType); //LdapAuthenticationWithSearch
        if( $ldapRes == NULL ) {
            //exit('ldap failed');
            //$this->logger->error("LdapAuthentication: can not bind user by usernameClean=[".$usernameClean."]; token=[".$token->getCredentials()."]");
            $this->logger->error("LdapAuthentication: can not bind user by usernameClean=[".$usernameClean."];");

            $user = $this->findUserByUsername($token->getUsername());
            $this->validateFailedAttempts($user);

            return NULL;
        }
        //exit('ldap success');

        //check if user already exists in DB
        $user = $this->findUserByUsername($token->getUsername());
        //echo "Ldap user =".$user."<br>";

        if( $user ) {
            //echo "DB user found=".$user->getUsername()."<br>";
            //exit();

            $this->logger->notice("findUserByUsername: existing user found in DB by token->getUsername()=".$token->getUsername());

            if( $this->canLogin($user) === false ) {
                $this->logger->warning("LdapAuthentication: User cannot login ".$user);
                return NULL;
            }

            return $user;
        } else {
            $this->logger->warning("findUserByUsername: Can not find existing user in DB by token->getUsername()=".$token->getUsername());
        }

        //echo "1<br>";

        //////////////////// constract a new user ////////////////////


//        $user = $this->findUserByUsernameAsEmail($token->getUsername());
//        if( $user ) {
//            $this->logger->notice("Ldap Authentication: Exit: Username is not cwid. User found in DB by token->getUsername()=".$token->getUsername());
//            return NULL;
//        }

        //testing
        //if( $usernameClean == "oli2002" ) {
            //exit("attempt generate new admin user");
        //}
        //exit("attempt generate new user");

        $this->logger->notice("LdapAuthentication: create a new user found by token->getUsername()=".$token->getUsername());
        $user = $userSecUtil->constractNewUser($token->getUsername());
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

        if( $searchRes ) {
            $user->setEmail($searchRes['mail']);
            $user->setFirstName($searchRes['givenName']);
            $user->setLastName($searchRes['lastName']);
            $user->setDisplayName($searchRes['displayName']);
            $user->setPreferredPhone($searchRes['telephoneNumber']);
            $user->setPreferredMobilePhone($searchRes['mobile']);
        }

        //cwid is admin cwid
        //if( $user->getUsername() == "cwid1_@_ldap-user" || $user->getUsername() == "cwid2_@_ldap-user" ) {
        //    $user->addRole('ROLE_PLATFORM_ADMIN');
        //}

        //exit('ldap ok');

        //////////////////// save user to DB ////////////////////
        //$userManager = $this->container->get('fos_user.user_manager');
        $userManager = $this->container->get('user_manager');
        $userManager->updateUser($user);

        return $user;
    }

    //Used by ajax authenticate-user/
    public function authenticateUserToken( $subjectUser, $token ) {

        if( !$subjectUser ) {
            return NULL;
        }

        $username = $token->getUsername();

        //oli2002c_@_local-user, oli2002c_@_ldap-user
        $usernameArr = explode("_@_", $username);
        if( count($usernameArr) != 2 ) {
            $this->logger->warning("Invalid username ".$username);
            return NULL;
        }

        $identifierUsername = $usernameArr[0];
        $identifierKeytype = $usernameArr[1];

        //Case 1: "Local User"
        if( $identifierKeytype == 'local-user' ) {
            $token->setUser($subjectUser);
            $this->logger->notice('Trying authenticating the local user with username=' . $identifierUsername);
            $user = $this->LocalAuthentication($token);

            return $user;
        }

        //Case 2: LDAP User
        if( $identifierKeytype == 'ldap-user' || $identifierKeytype == 'ldap2-user' ) {
            //Case 2: "NYP CWID"
            $token->setUser($subjectUser);
            $this->logger->notice('Trying authenticating the LDAP user with username=' . $identifierUsername);
            $user = $this->LdapAuthentication($token);

            if( !$user ) {
                //Try to use user's credential authentication under Credentials->Identifiers-> identifier type "Local User"
                //This identifier must have status "Verified by Administrator" and checked "Identifier enables system/service access" checkbox
                $user = $this->simpleIdentifierAuthetication($token,false);
            }

            return $user;
        }

        return NULL;
    }

    //check identifier by keytype i.e. "Local User", identifier number and fields username and password (identifier number)
    public function identifierAuthentication($token) {

        $username = $token->getUsername();
        //$credentials = $token->getCredentials();
        $this->logger->notice("identifierAuthentication with username ".$username);

        //oli2002c_@_local-user, oli2002c_@_ldap-user
        $usernameArr = explode("_@_", $username);
        if( count($usernameArr) != 2 ) {
            $this->logger->warning("Invalid username ".$username);
            return NULL;
        }

        //$identifierUsername = $usernameArr[0];
        $identifierKeytype = $usernameArr[1];

        //$identifierUsername = "oli2002";

        //echo "username=".$username."<br>";
        //exit('1');

        //try all matching identifier with primary user identifier type
        //1) get identifier types from Identifier Types (IdentifierTypeList)
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:IdentifierTypeList'] by [IdentifierTypeList::class]
        $identifiers = $this->em->getRepository(IdentifierTypeList::class)->findBy(array('type' => array('default','user-added')));

        //2) get current Primary Public User ID Type (i.e. "Local User")
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
        $identifierKeytype = $this->em->getRepository(UsernameType::class)->findOneByAbbreviation($identifierKeytype);
        if( !$identifierKeytype ) {
            $this->logger->warning("Identifier not found by abbreviation=".$identifierKeytype);
            return NULL;
        }

        foreach( $identifiers as $identifier ) {
            $this->logger->notice($identifier->getName()."?=".$identifierKeytype->getName());

            if( $identifier->getName() == $identifierKeytype->getName() ) {

                $this->logger->notice("identifier match: ".$identifier);
                $subjectUser = $this->authenticateUserByIdentifierType($username, $token->getCredentials(), $identifierKeytype->getName());
                if( $subjectUser ) {
                    $this->addEventLog($subjectUser,$identifier);

                    if( $this->canLogin($subjectUser) === false ) {
                        return NULL;
                    }

                    return $subjectUser;
                } else {
                    $this->logger->notice("User not found by authenticateUserByIdentifierType function. username=".$username."; identifierKeytype=".$identifierKeytype);
                }

                return NULL;
            }//if match
        }

        //exit("no user found by username=$identifierUsername keytype=$identifierKeytype");
        $this->logger->warning("Identifiers are not authenticated; Identifiers count=".count($identifiers));
        return NULL;
    }
    //find a user by user's identifier
    public function authenticateUserByIdentifierType( $username, $credentials, $identifierKeytypeName ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:IdentifierTypeList'] by [IdentifierTypeList::class]
        $identifierKeytype = $this->em->getRepository(IdentifierTypeList::class)->findOneByName($identifierKeytypeName);
        if( !$identifierKeytype ) {
            $this->logger->notice('identifierKeytype not found by name='.$identifierKeytypeName);
            return NULL;
        }

        //STATUS_VERIFIED = 1
        $identifierStatus = 1;

        //enableAccess = true
        $identifierEnableAccess = true;

        //External Type": oleg_userdirectorybundle_user_credentials_identifiers_1_keytype
        //"External ID":  oleg_userdirectorybundle_user_credentials_identifiers_1_field
        //Status:         oleg_userdirectorybundle_user_credentials_identifiers_1_status
        //Enabled:        oleg_userdirectorybundle_user_credentials_identifiers_1_enableAccess
        $repository = $this->em->getRepository(User::class);
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');

        $dql->leftJoin("user.credentials", "credentials");
        $dql->leftJoin("credentials.identifiers", "identifiers");
        //$dql->leftJoin("identifiers.keytype", "keytype");

        $dql->where("identifiers.field = :identifierField AND identifiers.keytype = :identifierKeytype");
        $dql->andWhere("identifiers.status = :identifierStatus AND identifiers.enableAccess = :identifierEnableAccess");
        $dql->andWhere("user.username=:username");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        //$this->logger->notice('username='.$username.'; identifierKeytype='.$identifierKeytype->getId()."; identifierField=".$credentials."; identifierStatus=".$identifierStatus."; identifierEnableAccess=".$identifierEnableAccess);

        $query->setParameters(array(
            'username' => $username,
            'identifierKeytype' => $identifierKeytype->getId(),
            'identifierField' => $credentials,
            'identifierStatus' => $identifierStatus,
            'identifierEnableAccess' => $identifierEnableAccess,
        ));

        $users = $query->getResult();

        if( count($users) == 1 ) {
            $singleUser = $users[0];
            $this->logger->notice('Ok: User found with username='.$username);
            return $singleUser;
        }

        if( count($users) == 0  ) {
            $this->logger->warning('No user found with username='.$username);
            return NULL;
        }

        if( count($users) > 1 ) {
            $this->logger->warning('Multiple users found with username='.$username);
            return NULL;
        }

        return NULL;
    }
    //"Logged in using [PublicIdentifierType] [PublicIdentifier ID]"
    //$identifierKeytypeStr, $identifierUsername
    public function addEventLog( $subjectUser, $identifier ) {
        //record edit user to Event Log
        $event = "Logged in using identifier '$identifier' and user '$subjectUser'";

        //$request = $this->container->get('request'); //http://localhost/order/directory/login_check
        if( $this->requestStack ) {
            $request = $this->requestStack->getCurrentRequest();
        } else {
            $request = $this->container->get('request_stack')->getCurrentRequest();
        }

        //get sitename as "fellowship-applications" or "directory"
        $currentUrl = $request->getUri();

        $sitenameFull = parse_url($currentUrl, PHP_URL_PATH); ///order/directory/login_check
        //echo "sitenameFull=$sitenameFull<br>";
        $sitenameArr = explode("/",$sitenameFull); ///order/directory/login_check
        $sitename = $sitenameArr[count($sitenameArr)-2];
        //echo "sitename=$sitename<br>";

        //exit("sitename=$sitename");

        $userSecUtil = $this->container->get('user_security_utility');
        //$sitename,$event,$user,$subjectEntities,$request,$action='Unknown Event'
        $userSecUtil->createUserEditEvent($sitename,$event,$subjectUser,$subjectUser,$request,'Successful Login');
    }

    public function simpleIdentifierAuthetication($token, $fullAuth=true) {
        //find verified identifier with "Local User" type.
        // Use oleg_userdirectorybundle_user[credentials][identifiers][0][field] as entered password

        $username = $token->getUsername();
        //$credentials = $token->getCredentials();
        //$this->logger->notice("identifierAuthentication with username ".$username);

//        //oli2002c_@_local-user, oli2002c_@_ldap-user
//        $usernameArr = explode("_@_", $username);
//        if( count($usernameArr) != 2 ) {
//            $this->logger->warning("Invalid username ".$username);
//            return NULL;
//        }

        //$identifierUsername = $usernameArr[0];
        $identifierKeytypeName = "Local User";

        //$this->logger->notice("identifier match: ".$identifier);
        $subjectUser = $this->authenticateUserByIdentifierType($username, $token->getCredentials(), $identifierKeytypeName);
        //echo "subjectUser=$subjectUser <br>";

        if( $subjectUser ) {

            if( $fullAuth ) {
                $this->addEventLog($subjectUser, $identifierKeytypeName);

                if ($this->canLogin($subjectUser) === false) {
                    //exit('cannot login');
                    //return NULL;
                }
            }

            $this->logger->notice("User authenticated successfully by simpleIdentifierAuthetication function. username=".$username."; identifierKeytype=".$identifierKeytypeName);

            //exit('login OK!');
            return $subjectUser;
        } else {
            $this->logger->notice("User not found by simpleIdentifierAuthetication function. username=".$username."; identifierKeytype=".$identifierKeytypeName);
        }

        return NULL;
    }


    public function findUserByUsername($username) {

        //$userSecUtil = $this->container->get('user_security_utility');
        //$userSecUtil->switchDb();//$this->em->getConnection());

        //$userManager = $this->container->get('fos_user.user_manager');
        $userManager = $this->container->get('user_manager');
        $username = trim((string)$username);
        $username = strtolower($username);
        $user = $userManager->findUserByUsername($username);
        return $user;
    }

//    public function findUserByUsernameAsEmail($username) {
//
//        if( strpos((string)$username, '@') !== false ) {
//            $cwidArr = explode("@",$username);
//            if( count($cwidArr) > 1 ) {
//                $cwid = $cwidArr[0];
//                if( $cwid ) {
//                    $cwid = trim((string)$cwid);
//                }
//            }
//        }
//        //exit("cwid=[$cwid]");
//
//        $query = $this->em->createQueryBuilder()
//            ->from('AppUserdirectoryBundle:User', 'user')
//            ->select("user")
//            ->leftJoin("user.infos", "infos")
//            ->where("infos.email LIKE :cwid OR infos.displayName LIKE :cwid")
//            ->setParameters( array(
//                'cwid' => $cwid
//            ));
//
//        $users = $query->getQuery()->getResult();
//
//        if( count($users) > 0 ) {
//            $user = $users[0];
//            return $user;
//        }
//
//        return NULL;
//    }

    //return 1 if bind successful
    //return NULL if failed
    public function ldapBind( $username, $password, $ldapType=1 ) {
        //return 1;
        //step 1
        if( $this->simpleLdap($username,$password,"cn",$ldapType) ) {
            return 1;
        }

        if( $this->simpleLdap($username,$password,"uid",$ldapType) ) {
            return 1;
        }

        //step 2
        if( substr(php_uname(), 0, 7) == "Windows" ){
            return $this->ldapBindWindows($username,$password,$ldapType);
        }
        else {
            return $this->ldapBindUnix($username,$password,$ldapType);
        }

        return NULL;
    }

    //return 1 if bind successful
    //return NULL if failed
    public function ldapBindWindows( $username, $password, $ldapType=1 ) {

        //echo "Windows ldap<br>";
        $userSecUtil = $this->container->get('user_security_utility');

        $postfix = $this->getPostfix($ldapType);

        //Ldap authentication using exe script
        //$LDAPHost = $this->container->getParameter('ldaphost');
        $LDAPHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress'.$postfix);

        //$LDAPPort = $this->container->getParameter('ldapport');
        $LDAPPort = $userSecUtil->getSiteSettingParameter('aDLDAPServerPort'.$postfix);

        //$exePath = "../src/App/UserdirectoryBundle/Util/";
        //$exePath = $this->container->getParameter('ldapexepath');
        $exePath = $userSecUtil->getSiteSettingParameter('ldapExePath'.$postfix);

        //$exeFile = "LdapSaslCustom.exe";
        //$exeFile = $this->container->getParameter('ldapexefilename');
        $exeFile = $userSecUtil->getSiteSettingParameter('ldapExeFilename'.$postfix);

        $command = $exePath.$exeFile;
        //$command = $exeFile;
        //echo "command=".$command."<br>";
        //$this->logger->notice("ldapBindWindows: command=[$command]; LDAPHost=[$LDAPHost]; LDAPPort=[$LDAPPort]; username=[$username]; token=[$password]");

        $command = $this->w32escapeshellarg($command); //escapeshellarg
        $LDAPHost = $this->w32escapeshellarg($LDAPHost);
        $LDAPPort = $this->w32escapeshellarg($LDAPPort);
        $username = $this->w32escapeshellarg($username);
        $password = $this->w32escapeshellarg($password); //escapeshellarg: replaces %(percent sign) with a space
        //$this->logger->notice("ldapBindWindows: command=[$command]; LDAPHost=[$LDAPHost]; LDAPPort=[$LDAPPort]; username=[$username]; token=[$password]");
        $this->logger->notice("ldapBindWindows: command=[$command]; LDAPHost=[$LDAPHost]; LDAPPort=[$LDAPPort]; username=[$username]");

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
        $this->logger->notice("ldapBindWindows: return=".$return.". outputArr=[".implode(";",$output)."]");

        if( $return == 1 && count($output) > 0 ) {
            if( $output[0] == 'LDAP_SUCCESS' ) {
                return 1;
            }
        }

        return NULL;
    }

    public function w32escapeshellarg($s) {
        return '"' . addcslashes($s, '\\"') . '"';
    }

    //TODO: must be tested on unix environment
    //PHP ldap_sasl_bind is not documented. It's better to use LdapSaslCustom.cpp
    public function ldapBindUnix( $username, $password, $ldapType=1 ) {

        $this->logger->warning("Unix system detected. ldap_sasl_bind is not supported.");
        return NULL;


        $userSecUtil = $this->container->get('user_security_utility');

        $postfix = $this->getPostfix($ldapType);

        //$LDAPHost = $this->container->getParameter('ldaphost');
        $LDAPHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress'.$postfix);
        $mech = "GSSAPI";
        //$mech = "DIGEST-MD5";
        //$mech = "LDAP_AUTH_NEGOTIATE";
        $cnx = $this->connectToLdap($LDAPHost);

        //testing
        ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);

        $origLdapBindDN = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu'.$postfix); //scientists,dc=example,dc=com
        $res = null;
        $ldapBindDNArr = explode(";",$origLdapBindDN);
        //echo "count=".count($ldapBindDNArr)."<br>";
        foreach( $ldapBindDNArr as $ldapBindDN) {
            //$ldapBindDN = $userPrefix."=".$username.",".$ldapBindDN;
            $this->logger->notice("ldap Bind Unix: ldapBindDN=".$ldapBindDN);
            //echo "ldapBindDN=".$ldapBindDN."<br>";
            $res = ldap_sasl_bind(
                $cnx,       //1 resource link
                NULL,       //2 binddn
                $password,  //3 password
                $mech,      //4 sals mech
                NULL,       //5 sals realm
                $username,  //6 auth id
                $ldapBindDN //7 props: 'dn:uid=tommy,ou=people,dc=example,dc=com'
            );
            if( $res ) {
                break;
            } else {
                $this->logger->notice("ldapBindUnix: res=".$res);
                $this->logger->notice("ldapBindUnix: ldap_error=".ldap_error($cnx));
            }
        }

//        $res = ldap_sasl_bind(
//            $cnx,       //1 resource link
//            NULL,       //2 binddn
//            $password,  //3 password
//            $mech,      //4 sals mech
//            NULL,       //5 sals realm
//            $username,  //6 auth id
//            NULL        //7 props: 'dn:uid=tommy,ou=people,dc=example,dc=com'
//        );

        if( !$res ) {
            //echo $mech." - could not sasl bind to LDAP by SASL<br>";
            //$this->logger->notice("ldapBindUnix: res=".$res);
            //$this->logger->notice("ldapBindUnix: ldap_error=".ldap_error($cnx));
            ldap_error($cnx);
            ldap_unbind($cnx);
            return NULL;
        } else {
            $this->logger->notice("ldapBindUnix: Success!!! res=".$res);
            ldap_unbind($cnx);
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
    // tested by public ldap server: https://www.zflexldapadministrator.com/index.php/blog/82-free-online-ldap
    // Server: www.zflexldap.com
    // Port: 389
    // AD/LDAP Server OU: ou=users,ou=guests,dc=zflexsoftware,dc=com
    // Username: guest1 Password: guest1password
    //supports multiple aDLDAPServerOu: cn=Users,dc=a,dc=wcmc-ad,dc=net;ou=NYP Users,dc=a,dc=wcmc-ad,dc=net
    public function simpleLdap($username, $password, $userPrefix="uid", $ldapType=1) {
        //$this->logger->notice("Simple Ldap. $username, $password");

        //exit("simpleLdap");
        //set_time_limit(3); //testing
        //putenv('LDAPTLS_REQCERT=never'); // /etc/openldap/ldap.conf

        $userSecUtil = $this->container->get('user_security_utility');
        $postfix = $this->getPostfix($ldapType);

        $LDAPHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress'.$postfix);
        $LDAPPort = $userSecUtil->getSiteSettingParameter('aDLDAPServerPort'.$postfix);
        $cnx = $this->connectToLdap($LDAPHost,$LDAPPort);

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

        //$ldapBindDN = $userPrefix."=".$username.",".$ldapBindDN;
        ////test: https://www.forumsys.com/tutorials/integration-how-to/ldap/online-ldap-test-server/
        ////$password = "password";
        ////$ldapBindDN = "uid=tesla,dc=example,dc=com"; //workings
        ////test: https://www.zflexldapadministrator.com/index.php/blog/82-free-online-ldap
        ////$this->logger->notice("Simple ldap: before ldap_bind");
        //$res = @ldap_bind($cnx,$ldapBindDN,$password);

        //$this->logger->notice("Simple ldap: after ldap_bind");

        //echo "ldap res=".$res."<br>";
        //echo "ldap ldap_error=".ldap_error($cnx)."<br>";
        //exit();

        if( !$res ) {
            //echo $mech." - could not sasl bind to LDAP by SASL<br>";
            $this->logger->notice("simple Ldap: ldap_error=".ldap_error($cnx)."; res=".$res."; user=".$username);
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

    //It might work
    //remove: fabiang/sasl symfony/ldap
    public function laminasBind( $username, $password, $ldapType=1 ) {

        echo "username=[$username], password=[$password] <br>";

        //$host = 'a.wcmc-ad.net';
        $userSecUtil = $this->container->get('user_security_utility');

        $postfix = $this->getPostfix($ldapType);

        $LDAPHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress'.$postfix);

        $options = [
            'host'              => $LDAPHost,
            //'username'          => 'xxx',
            //'password'          => 'xxx',
            //'bindRequiresDn'    => false,
            //'bindRequiresDn'    => true,
            'accountDomainName' => $LDAPHost,
            //'baseDn'            => 'dc=a,dc=wcmc-ad,dc=net',
            //'baseDn'            => 'cn=Users,dc=a,dc=wcmc-ad,dc=net',
            //'baseDn'            => 'ou=NYP Users,ou=External,dc=a,dc=wcmc-ad,dc=net',
            //'useSsl'            => true,
            //'useStartTls'      => true
        ];

        $ldap = new \Laminas\Ldap\Ldap($options);
        //$ldap->bind($username, $password);

        try {
            $ldap->bind($username, $password);
            //$acctname = $ldap->getCanonicalAccountName($username);
            //$acctname = $ldap->getCanonicalAccountName($username, \Laminas\Ldap\Ldap::ACCTNAME_FORM_DN);
            //echo "SUCCESS: authenticated $acctname\n";
            echo "SUCCESS: authenticated";
            return 1;
        } catch (LdapException $zle) {
            echo '  ' . $zle->getMessage() . "\n";
            //if ($zle->getCode() === LdapException::LDAP_X_DOMAIN_MISMATCH) {
            //    continue;
            //}
            $this->logger->notice("Laminas Bind failed:" . $zle->getMessage());
            exit("Laminas Bind failed:" . $zle->getMessage());

            return NULL;
        }

        //dump($ldap);
        //exit('EOF');

        //$acctname = $ldap->getCanonicalAccountName($username, \Laminas\Ldap\Ldap::ACCTNAME_FORM_DN);

        $acctname = $ldap->getCanonicalAccountName($username, \Laminas\Ldap\Ldap::ACCTNAME_FORM_DN);
        echo "acctname=[$acctname] <br>";

        //dump($acctname);

        echo "EOF loginLaminasTest <br>";
        exit('EOF');
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
            //testing: allow to login without LDAP admin bind
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

    public function canLogin($user) {
        //return true;
        if( $user->getLocked() ) {
            $this->logger->warning("User is locked");

            $userSecUtil = $this->container->get('user_security_utility');
            $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
            $msg = " This account has been locked to prevent unauthorized access.<br>".
                " Please contact the ".$systemEmail." to request account re-activation.";
            $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add(
                'warning',
                $msg
            );

            //exit($msg);
            return false;
        }
        if( !$user->isEnabled() ) {
            $this->logger->warning("User is not enabled");
            //exit("User is not enabled");
            return false;
        }

        return true;
    }

    //if the current attempt is not successful, AND the failed attempt count
    // for this user is greater than or equal to the “Permitted failed log in attempts”
    // in site settings (5), AND the timestamp of the last failed attempt is newer
    // than the timestamp of the last successful attempt, then lock the account
    public function validateFailedAttempts($user) {

        if( !$user ) {
            return;
        }

        //if( $user->hasRole("ROLE_PLATFORM_ADMIN") || $user->hasRole("ROLE_PLATFORM_DEPUTY_ADMIN") ) {
        //    return true;
        //}

        //if the current attempt is not successful, AND the failed attempt count
        // for this user is greater than or equal to the “Permitted failed log in attempts”
        // in site settings (5), AND the timestamp of the last failed attempt is newer
        // than the timestamp of the last successful attempt, then lock the account
        $userSecUtil = $this->container->get('user_security_utility');
        $permittedFailedLoginAttempt = $userSecUtil->getSiteSettingParameter('permittedFailedLoginAttempt');
        if( $permittedFailedLoginAttempt ) {

            $user->incrementFailedAttemptCounter();

            $userFailedAttempts = $user->getFailedAttemptCounter();

            //echo "userFailedAttempts=".$userFailedAttempts." > ".$permittedFailedLoginAttempt."<br>";
            if( $userFailedAttempts > $permittedFailedLoginAttempt ) {
                //lock
                $user->setEnabled(false);
                //$this->em->flush($user);
                $this->em->flush();

                $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
                $msg = $permittedFailedLoginAttempt." attempts have been made to log into this account with incorrect credentials.<br>".
                    " This account has been locked to prevent unauthorized access.<br>".
                    " Please contact the ".$systemEmail." to request account re-activation.";
                $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add(
                    'warning',
                    $msg
                );

                //EventLog
                $systemuser = $userSecUtil->findSystemUser();
                $userSecUtil->createUserEditEvent("employees",$msg,$systemuser,$user,null,'Permitted Failed Log in Attempts');

                throw new AuthenticationException($msg);
            } else {
                //$this->em->flush($user);
                $this->em->flush();
            }

        }//if $permittedFailedLoginAttempt
    }




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

//    public function getPasswordHasher() {
//        return $this->passwordHasher;
//    }
    public function getEncodedPassword($user,$plaintextPassword) {
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        return $hashedPassword;
    }
    public function isPasswordValid($user,$plaintextPassword) {
        if( $this->passwordHasher->isPasswordValid($user, $plaintextPassword) ) {
            return true;
        }
        return false;
    }

    public function getADUsersByCwids( $cwids, $ldapType=1,$withWarning=true ) {
        //echo "username=".$username."<br>";
        $userSecUtil = $this->container->get('user_security_utility');

        $postfix = $this->getPostfix($ldapType);

        $origLdapBindDN = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu'.$postfix); //old: a.wcmc-ad.net, new: cn=Users,dc=a,dc=wcmc-ad,dc=net
        //$origLdapBindDN = "ou=NYP Users,ou=External,dc=a,dc=wcmc-ad,dc=net";
        //echo "origLdapBindDN=".$origLdapBindDN."<br>";

        $LDAPUserAdmin = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountUserName'.$postfix); //cn=read-only-admin,dc=example,dc=com
        $LDAPUserPasswordAdmin = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountPassword'.$postfix);

        if( $LDAPUserAdmin && $LDAPUserPasswordAdmin ) {
            //ok
        } else {
            //no search
            return NULL;
        }

        $LDAPHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress'.$postfix);
        $cnx = $this->connectToLdap($LDAPHost);

        //$filter="(ObjectClass=Person)";
        //$filter="(CN=".$username.")";
        //$filter = "(sAMAccountName=".$username.")";

//        $filter = "(|(CN=$username)(sAMAccountName=$username))"; //use cn or sAMAccountName to search by username (cwid)
//        $filter = "(|(CN=".$username.")(CN=oli2002))";
//        //https://stackoverflow.com/questions/42415694/how-to-query-multiple-users-from-ldap
//        $filter = "(|(uid=vib9002)(uid=aab9027)(uid=oli2002)(uid=adm9073))";
//        $filter = "(|(uid=aab9027)(uid=adm9073)(uid=adm9057)(uid=ado9026)(uid=aeb9010))";
//        //$filter = "(|(uid=adm9073))";
//        $filter = "(|(company=NYP)(mail=adm9073@med.cornell.edu)(department='Star 3'))";
//        $filter = "(|(mail=*))";

        $cwidsArr = array();
        $count = 0;
        foreach($cwids as $cwid) {
            if( str_contains($cwid,'(') || str_contains($cwid,')') ) {
                //bad username
            } else {
                $cwidsArr[] = "(CN=$cwid)";
                $count++;
            }
        }
        $filter = "(|".implode("",$cwidsArr).")";
        //echo $count.": filter=".$filter."<br>";

        $res = @ldap_bind($cnx, $LDAPUserAdmin, $LDAPUserPasswordAdmin); //searchLdap
        //$res = $this->ldapBind($LDAPUserAdmin,$LDAPUserPasswordAdmin);
        if (!$res) {
            $this->logger->error("search Ldap: ldap_bind failed with admin authentication username=" . "[" . $LDAPUserAdmin . "]" . "; LDAPUserPasswordAdmin=" . "[" . $LDAPUserPasswordAdmin . "]");
            //echo "Could not bind to LDAP: user=".$LDAPUserAdmin."<br>";
            //testing: allow to login without LDAP admin bind
            $adminLdapBindRequired = true;
            //$adminLdapBindRequired = false;
            if ($adminLdapBindRequired) {
                ldap_error($cnx);
                ldap_unbind($cnx);
                //exit("error ldap_bind");
                return NULL;
            }
        } else {
            //$this->logger->notice("search Ldap: ldap_bind OK with admin authentication username=" . $LDAPUserAdmin);
        }

        //$LDAPFieldsToFind = array("mail", "title", "sn", "givenName", "displayName", "telephoneNumber", "mobile", "company"); //sn - lastName
        //$LDAPFieldsToFind = array("sn");   //, "givenName", "displayName", "telephoneNumber");
        //$LDAPFieldsToFind = array("cn", "samaccountname");
        $LDAPFieldsToFind = ["cn"];

        //$origLdapBindDN = "dc=a,dc=wcmc-ad,dc=net"; //testing
        //echo "origLdapBindDN=".$origLdapBindDN."<br>";
        //echo "filter=".$filter."<br>";

        //$sr = ldap_search($cnx, $ldapBindDN, $filter, $LDAPFieldsToFind);

        $ldadUsers = array();
        $sr = null;
        $ldapBindDNArr = explode(";",$origLdapBindDN);
        //echo "count=".count($ldapBindDNArr)."<br>";

        foreach( $ldapBindDNArr as $ldapBindDN) {
            //$this->logger->notice("search Ldap: ldapBindDN=".$ldapBindDN);
            //$sr = ldap_search($cnx, $ldapBindDN, $filter, $LDAPFieldsToFind);
            if( $withWarning ) {
                $sr = ldap_search(
                    $cnx,               //ldap
                    $ldapBindDN,        //base
                    $filter,            //filter
                    $LDAPFieldsToFind,   //attributes
                    0,                  //attributes_only
                    0                  //sizelimit
                );
            } else {
                $sr = @ldap_search(
                    $cnx,               //ldap
                    $ldapBindDN,        //base
                    $filter,            //filter
                    $LDAPFieldsToFind,   //attributes
                    0,                  //attributes_only
                    0                  //sizelimit
                );
            }

            if( $sr ) {
                //$this->logger->notice("search Ldap: ldap_search OK with filter=" . $filter . "; bindDn=".$ldapBindDN);
                $info = ldap_get_entries($cnx, $sr);
                //dump($info);
                //exit('111');

                $cleanRes = $this->cleanUpEntry($info);
                $ldadUsers = array_merge($ldadUsers, $cleanRes);
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

        ldap_unbind($cnx);

//        if( $ldadUsers ) {
//            $ldadUsers = array_unique($ldadUsers);
//        }

        //$this->logger->notice("search Ldap: ldap_search ok with ldapBindDN=".$ldapBindDN."; filter=" . $filter . "; count=".$info["count"]);
        //dump($ldadUsers);
        //exit('111');

        return $ldadUsers;
    }

    public function cleanUpEntry( $entry ) {
        $retEntry = array();
        for( $i = 0; $i < $entry['count']; $i++ ) {
            if( is_array($entry[$i]) ) {
                //dump($entry[$i]);
                //exit;
                $cn = $entry[$i]["cn"][0];
                $retEntry[$cn] = $entry[$i]["dn"];
            }
        }
        return $retEntry;
    }

    public function checkUsersAD( $ldapType=1, $withWarning=true, $limit=0 ) {

        $this->logger->notice("checkUsersAD: check user status in AD; limit=$limit");
        set_time_limit(1200);

        $ldapKeyType1Id = null;
        $ldapKeyType2Id = null;

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
        $ldapKeyType1 = $this->em->getRepository(UsernameType::class)->findOneBy(array('abbreviation'=>'ldap-user'));
        if( $ldapKeyType1 ) {
            $ldapKeyType1Id = $ldapKeyType1->getId();
        }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
        $ldapKeyType2 = $this->em->getRepository(UsernameType::class)->findOneBy(array('abbreviation'=>'ldap2-user'));
        if( $ldapKeyType2 ) {
            $ldapKeyType2Id = $ldapKeyType2->getId();
        }

        //$yesterday = new \DateTime('yesterday');
        $yesterday = date('Y-m-d H:i:s',strtotime("-1 days")); //2021-10-28 14:56:34
        //$yesterday = date('Y-m-d H:i:s',strtotime("-1 min"));
        //echo "yesterday=$yesterday <br>";

        $repository = $this->em->getRepository(User::class);
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin("user.infos","infos");

        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->leftJoin("employmentStatus.employmentType", "employmentType");
        $dql->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL");
        //$dql->where("employmentType.name NOT LIKE 'Pathology % Applicant' OR employmentType.id IS NULL");

        $params = array();
        $keytypeStr = "";
        if( $ldapKeyType1Id ) {
            $keytypeStr = "user.keytype = :keytype1";
            $params['keytype1'] = $ldapKeyType1Id;
        }
        if( $ldapKeyType2Id ) {
            if( $keytypeStr ) {
                $keytypeStr = $keytypeStr . " OR " . "user.keytype = :keytype2";
            } else {
                $keytypeStr = "user.keytype = :keytype2";
            }
            $params['keytype2'] = $ldapKeyType2Id;
        }

        if( $keytypeStr ) {
            $dql->andWhere($keytypeStr);
        }

        //get only users with lastAdCheck < $yesterday
        $dql->andWhere("user.lastAdCheck IS NULL OR user.lastAdCheck < :yesterday");
        $params['yesterday'] = $yesterday;

        $dql->orderBy("infos.lastName","ASC");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        if( count($params) > 0 ) {
            $query->setParameters($params);
        }

        if( $limit ) {
            $query->setMaxResults($limit);
        }

        $users = $query->getResult();
        //echo "users ".count($users)."<br>";
        //exit('111');

        //////////// connect to LDAP/AD ////////////
        $userSecUtil = $this->container->get('user_security_utility');

        $postfix = $this->getPostfix($ldapType);

        $origLdapBindDN = $userSecUtil->getSiteSettingParameter('aDLDAPServerOu'.$postfix); //old: a.wcmc-ad.net, new: cn=Users,dc=a,dc=wcmc-ad,dc=net
        //$origLdapBindDN = "ou=NYP Users,ou=External,dc=a,dc=wcmc-ad,dc=net";
        //echo "origLdapBindDN=".$origLdapBindDN."<br>";

        $LDAPUserAdmin = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountUserName'.$postfix); //cn=read-only-admin,dc=example,dc=com
        $LDAPUserPasswordAdmin = $userSecUtil->getSiteSettingParameter('aDLDAPServerAccountPassword'.$postfix);

        if( $LDAPUserAdmin && $LDAPUserPasswordAdmin ) {
            //ok
        } else {
            //no search
            return NULL;
        }

        $LDAPHost = $userSecUtil->getSiteSettingParameter('aDLDAPServerAddress'.$postfix);
        $cnx = $this->connectToLdap($LDAPHost);

        $res = @ldap_bind($cnx, $LDAPUserAdmin, $LDAPUserPasswordAdmin); //searchLdap
        //$res = $this->ldapBind($LDAPUserAdmin,$LDAPUserPasswordAdmin);
        if (!$res) {
            $this->logger->error("checkUsersAD: ldap_bind failed with admin authentication username=" . "[" . $LDAPUserAdmin . "]" . "; LDAPUserPasswordAdmin=" . "[" . $LDAPUserPasswordAdmin . "]");
            //echo "Could not bind to LDAP: user=".$LDAPUserAdmin."<br>";
            //testing: allow to login without LDAP admin bind
            $adminLdapBindRequired = true;
            //$adminLdapBindRequired = false; //testing. For live, use $adminLdapBindRequired = true
            if ($adminLdapBindRequired) {
                ldap_error($cnx);
                ldap_unbind($cnx);
                //exit("error ldap_bind");
                return NULL;
            }
        } else {
            //$this->logger->notice("checkUsersAD: ldap_bind OK with admin authentication username=" . $LDAPUserAdmin);
        }

        $LDAPFieldsToFind = ["cn"];
        //////////// EOF connect to LDAP/AD ////////////

        $adCount = 0;
        $lastAdCheckDateTime = new \DateTime();
        //$yesterday = new \DateTime('yesterday');

        foreach($users as $user) {
            //$this->logger->notice("checkUsersAD: check user $user");

//            $lastCheck = $user->getLastAdCheck();
//            if( $lastCheck ) {
//                if( $lastCheck > $yesterday ) {
//                    echo "Skip: lastCheck > yesterday";
//                    continue;
//                }
//            }

            $user->setLastAdCheck($lastAdCheckDateTime);
            $user->setActiveAD(false);

            $cwid = $user->getCleanUsername();
            //$cwid = 'oli2002111';
            //$cwid = 'oli2002';

            if( str_contains($cwid,'(') || str_contains($cwid,')') ) {
                continue; //bad cwid
            }

            $filter="(cn=".$cwid.")";
            //$filter="cn=".$cwid."";
            //$filter = "(|(CN=$cwid)(sAMAccountName=$cwid))";
            //echo "filter=$filter <br>";

            $ldapBindDNArr = explode(";",$origLdapBindDN);
            //echo "count=".count($ldapBindDNArr)."<br>";

            foreach( $ldapBindDNArr as $ldapBindDN) {

                //$this->logger->notice("search Ldap: ldapBindDN=".$ldapBindDN);
                //$sr = ldap_search($cnx, $ldapBindDN, $filter, $LDAPFieldsToFind);
                if( $withWarning ) {
                    $sr = ldap_search(
                        $cnx,               //ldap
                        $ldapBindDN,        //base
                        $filter,            //filter
                        $LDAPFieldsToFind,  //attributes
                        0,                  //attributes_only
                        0                   //sizelimit
                    );
                } else {
                    $sr = @ldap_search(
                        $cnx,               //ldap
                        $ldapBindDN,        //base
                        $filter,            //filter
                        $LDAPFieldsToFind,  //attributes
                        0,                  //attributes_only
                        0                   //sizelimit
                    );
                }

                if( $sr ) {
                    //searched ldap_search AD ok
                    //$this->logger->notice("checkUsersAD: ldap_search OK with filter=" . $filter . "; bindDn=".$ldapBindDN);

                    $info = ldap_get_entries($cnx, $sr);
                    //dump($info);
                    $count = $info['count'];
                    //exit('111 $count='.$count);
                    //echo 'count='.$count;

                    if( $count == 1 ) {
                        $user->setActiveAD(true);
                        //echo " ".$ldapBindDN." AD: user=$user, username=".$user->getUsername()." <br>";
                        //echo "AD: user=$user (".$ldapBindDN.")<br>";
                        $adCount++;
                        break; //break this "foreach( $ldapBindDNArr as $ldapBindDN)"
                    } else {
                        //echo " ".$ldapBindDN." NOT in AD: user=$user, key=".$user->getKeytype()." <br>";
                        //echo "NOT in AD: user=$user (".$ldapBindDN.")<br>";
                    }
                } else {
                    $this->logger->error("checkUsersAD: ldap_search NOTOK with filter=" . $filter . "; bindDn=".$ldapBindDN);
                    //echo "checkUsersAD: ldap_search NOTOK with filter=" . $filter . "; bindDn=".$ldapBindDN."<br>";
                    //$user->setActiveAD(false);
                }

            }//foreach $ldapBindDNArr

            //$this->em->flush($user);
            $this->em->flush();

        }//foreach $users

        //disconnect
        ldap_unbind($cnx);

        //exit('exit checkUsersAD. $adCount='.$adCount);
        return $adCount;
    }

} 