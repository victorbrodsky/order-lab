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

//        if (!$this->supports($token)) {
//            return null;
//        }

//        echo "<br>$token->username: pass=".$token->digest."<br>";
//        exit("AperioProvider");

        $AuthResult = $this->AperioAuth( $token->username, $token->digest );

//        echo "<br>AuthResult:<br>";
//        print_r($AuthResult);
        //exit("exit AperioProvider");

        if( isset($AuthResult['UserId']) && $AuthResult['ReturnCode'] == 0 ) {
            //echo "<br>Aperio got UserId!<br>";

            //get user from DB or create a new user
            $userManager = $this->serviceContainer->get('fos_user.user_manager');

            $user = $userManager->findUserByUsername($token->username);

            if( !$user ) {
                $userManager = $this->serviceContainer->get('fos_user.user_manager');
                $user = $userManager->createUser();
                $user->setUsername($token->username);

                if( isset($AuthResult['E_Mail']) && $AuthResult['E_Mail'] != "" ) {
                    $email = $AuthResult['E_Mail'];
                } else {
                    $email = "emptyemail@emptyemail.com";
                }
                //echo "email=".$email."<br>";

                $user->setEmail($email);
                $user->setPassword(''); //$token->digest);
                $user->setEnabled(1);
                //$rolesArr = array('ROLE_USER');
                //$user->setRoles($rolesArr);
                $user->addRole('ROLE_USER');
//                $user->addRole('ROLE_ADMIN');
                //echo "user roles count=".count($user->getRoles())."<br>";
                //exit("before update user");

                $userManager->updateUser($user);
            }

            $authenticatedToken = new AperioUserToken($user->getRoles());
//            $authenticatedToken = new UsernamePasswordToken($user->getUsername(), '', 'main', $user->getRoles());

            $authenticatedToken->setAttribute('email', $user->getEmail());
            $authenticatedToken->setUser($user);

            //echo "user roles count=".count($user->getRoles())."<br>";

            //print_r($user);
            //exit("before return");

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

        //echo "Aperio Auth !!!";
        //exit();
        //echo " skip login=".$loginName.", pass=". $password." <br>";

        if( 1 ) {
            include_once '\Skeleton.php';
            //$DataServerURL = "http://127.0.0.1:86";
            $DataServerURL = GetDataServerURL();
            $client = new \Aperio_Aperio($DataServerURL);//,"","","","");
            $AuthResult = $client->Authenticate($loginName,$password);

        } else {

            $AuthResult = array(
                'UserId' => 11,
                'ReturnCode' => 0
            );
            $loginName = 'oli2002';
        }

//        print_r($AuthResult);
//        exit();

        return $AuthResult;
    }

}

?>
