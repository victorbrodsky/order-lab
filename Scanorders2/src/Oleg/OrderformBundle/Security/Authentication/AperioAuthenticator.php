<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 1/22/14
 * Time: 8:33 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Security\UserProvider as FosUserProvider;
use Oleg\OrderformBundle\Entity\User;

class AperioAuthenticator extends FosUserProvider implements SimpleFormAuthenticatorInterface
{
    private $encoderFactory;
    private $serviceContainer;
    private $ldap = true;

    public function __construct(EncoderFactoryInterface $encoderFactory, $serviceContainer)
    {
        //exit("Aperio Authenticator constructor <br>");
        $this->encoderFactory = $encoderFactory;
        $this->serviceContainer = $serviceContainer;
    }

    public function createToken(Request $request, $username, $password, $providerKey)
    {
        //echo "create Token_TODEL <br>";
        //exit("AperioAuthenticator");
        return new UsernamePasswordToken($username, $password, $providerKey);
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        //echo "AperioAuthenticator: user name=".$token->getUsername().", Credentials=".$token->getCredentials()."<br>";
        //exit("AperioAuthenticator: authenticateToken");

        $AuthResult = $this->AperioAuth( $token->getUsername(), $token->getCredentials() );

        //print_r($AuthResult);
        //exit();

        if( isset($AuthResult['UserId']) && $AuthResult['ReturnCode'] == 0 ) {
            //echo "<br>Aperio got UserId!<br>";

//            $user = $userProvider->findUser($token->getUsername());
            $userManager = $this->serviceContainer->get('fos_user.user_manager');
            $user = $userManager->findUserByUsername($token->getUsername());

            if( !$user ) {

                //echo "No user found. Create a new User<br>";

                $user = $userManager->createUser();

                $user->setUsername($token->getUsername());
                $user->setEmail($AuthResult['E_Mail']);
                $user->setEnabled(1);
                $user->setCreatedby('aperio');

                //set Roles: aperio users can submit order by default.
                $user->addRole('ROLE_SUBMITTER');           //Submitter
                $user->addRole('ROLE_ORDERING_PROVIDER');   //Ordering Provider

                //TDODD: Remove: for testing at home;
                if( !$this->ldap ) {
                    echo "Aperio Auth: Remove it !!!";
                    $user->setUsername("testuser4");
                    $user->addRole('ROLE_ADMIN');
                }

                if( $token->getUsername() == "oli2002" || $token->getUsername() == "vib9020" ) {
                    $user->addRole('ROLE_ADMIN');
                }

                $user->setPassword("");

                $userManager->updateUser($user);

            } //if user

            return new UsernamePasswordToken($user, 'bar', $providerKey, $user->getRoles());

        } else {
            throw new AuthenticationException('The Aperio authentication failed.');
        }

        throw new AuthenticationException('Aperio: Invalid username or password');
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        //echo "supports Token_TODEL <br>";
        //exit("AperioAuthenticator");
        return $token instanceof UsernamePasswordToken && $token->getProviderKey() === $providerKey;
    }


    private function AperioAuth( $loginName, $password ) {

        //echo "Aperio Auth Changeit back !!!";
        //exit();
        //echo " skip login=".$loginName.", pass=". $password." <br>";

        if( $this->ldap ) {
            include_once '\Skeleton.php';
            //$DataServerURL = "http://127.0.0.1:86";
            $DataServerURL = GetDataServerURL();
            $client = new \Aperio_Aperio($DataServerURL);//,"","","","");
            $AuthResult = $client->Authenticate($loginName,$password);

        } else {
            echo "Aperio Auth Changeit back !!!";
            $AuthResult = array(
                'UserId' => 11,
                'ReturnCode' => 0,
                'E_Mail' => 'email@dummy'
            );
            //$loginName = 'oli2002';
        }

        //print_r($AuthResult);
        //exit();

        return $AuthResult;
    }
}