<?php

namespace Oleg\OrderformBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Oleg\OrderformBundle\Security\Authentication\Token\AperioUserToken;
use Oleg\OrderformBundle\Security\AperioLdap\cDataClient;
use Oleg\OrderformBundle\Entity\User;

class AperioProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $serviceContainer;

    public function __construct(UserProviderInterface $userProvider, $serviceContainer)
    {
        $this->userProvider = $userProvider;
        $this->serviceContainer = $serviceContainer;
    }

    public function authenticate(TokenInterface $token)
    {
        //echo "Aperio auth, token:<br>";
        //print_r($token);
        //exit("AperioProvider");

        if (!$this->supports($token)) {
            return null;
        }

//        echo "<br>$token->username: pass=".$token->digest."<br>";
//        exit("AperioProvider");

        $AuthResult = $this->AperioAuth( $token->username, $token->digest );

        //echo "<br>AuthResult:<br>";
        //print_r($AuthResult);
        //exit("<br>exit AperioProvider");

        if( isset($AuthResult['UserId']) && $AuthResult['ReturnCode'] == 0 ) {
            //echo "<br>Aperio got UserId!<br>";

            //get user from DB or create a new user
            $userManager = $this->serviceContainer->get('fos_user.user_manager');

            $user = $userManager->findUserByUsername($token->username);

            if( !$user ) {
                $userManager = $this->serviceContainer->get('fos_user.user_manager');   //TODO: remove it
                $user = $userManager->createUser();
                $user->setUsername($token->username);

//                if( isset($AuthResult['E_Mail']) && $AuthResult['E_Mail'] != "" ) {
//                    $email = $AuthResult['E_Mail'];
//                } else {
//                    $email = "";    //"emptyemail@emptyemail.com";
//                }
//                $email = $AuthResult['E_Mail'];

                $user->setEmail($AuthResult['E_Mail']);
                $user->setEnabled(1);
                $user->setCreatedby('aperio');
                $user->addRole('ROLE_USER');                //Submitter
                
                //TODO: for testing at home!!!
                echo "Aperio Auth: Remove it !!!";
                $user->setUsername("testuser4");
                $user->addRole('ROLE_SUPER_ADMIN'); 

                if( $token->username == "oli2002" || $token->username == "vib9020" ) {
                    $user->addRole('ROLE_SUPER_ADMIN');
                }

                if( $token->username == "svc_aperio_spectrum" || $token->username == "Administrator" || $token->username == "administrator" ) {
                    //$user->addRole('ROLE_ADMIN');
                } else {
                    $user->addRole('ROLE_ORDERING_PROVIDER');   //Ordering Provider
                }

                $encoder = $this->serviceContainer->get('security.encoder_factory')->getEncoder($user);
                $encodedPass = $encoder->encodePassword($token->digest, $user->getSalt());
                $user->setPassword($encodedPass);
                //TODO: do not store password in DB. Implement AperioAuthenticationProvider as LdapAuthenticationProvider or DAOAuthenticationProvider
                //$user->setPassword("");

                $userManager->updateUser($user);
            }

            $authenticatedToken = new AperioUserToken($user->getRoles());

            $authenticatedToken->setAttribute('email', $user->getEmail());
            $authenticatedToken->setUser($user);

//            print_r($user);
//            exit("<br>Aperio auth ok: before return<br>");

            return $authenticatedToken;
        } else {
            //exit("bad exit AperioProvider");
            throw new AuthenticationException('The Aperio authentication failed.');
        }

    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof AperioUserToken;
    }

    private function AperioAuth( $loginName, $password ) {

        echo "Aperio Auth Changeit back !!!";
        //exit();
        //echo " skip login=".$loginName.", pass=". $password." <br>";

        if( 0 ) {
            include_once '\Skeleton.php';
            //$DataServerURL = "http://127.0.0.1:86";
            $DataServerURL = GetDataServerURL();
            $client = new \Aperio_Aperio($DataServerURL);//,"","","","");
            $AuthResult = $client->Authenticate($loginName,$password);

        } else {

            $AuthResult = array(
                'UserId' => 11,
                'ReturnCode' => 0,
                'E_Mail' => 'email@dummy'
            );
            $loginName = 'oli2002';
        }

//        print_r($AuthResult);
//        exit();

        return $AuthResult;
    }



}

?>
