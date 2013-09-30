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
    private $cacheDir;

    public function __construct(UserProviderInterface $userProvider, $cacheDir)
    {
        $this->userProvider = $userProvider;
        $this->cacheDir     = $cacheDir;
    }

    public function authenticate(TokenInterface $token)
    {
        //echo "Aperio auth, token:<br>";
        //print_r($token);
        //exit("AperioProvider");

//        if (!$this->supports($token)) {
//            return null;
//        }

//        $username = $token->username;
//        if (empty($username)) {
//            $username = 'NONE_PROVIDED';
//        }

        //echo "<br>$token->username: pass=".$token->digest."<br>";
        //exit("AperioProvider");

        $AuthResult = $this->AperioAuth( $token->username, $token->digest );

        //echo "<br>AuthResult:<br>";
        //print_r($AuthResult);
        //exit("exit AperioProvider");

        if( isset($AuthResult['UserId']) && $AuthResult['ReturnCode'] == 0 ) {
            echo "<br>Aperio got UserId!<br>";

            //get user from DB or create a new user

//            $em = $this->getDoctrine()->getManager();
//            $user = $em->getRepository('OlegOrderformBundle:User')->findOneByUsername($token->username);
//            if( !$user ) {
//                $user = new User();
//                $user->setUsername($token->username);
//                $user->setEmail($AuthResult['E_Mail']);
//                $user->addRole('ROLE_USER');
//                $em->persist($user);
//                $em->flush();
//            }
//
//            $user = $em->getRepository('OlegOrderformBundle:User')->findOneByUsername($token->username);

            //$user = new User($token->username, '', array("ROLE_USER"), true, true, true, true);
            $user = new User();
            $user->setUsername($token->username);
            //$user->setEmail($AuthResult['E_Mail']);
            $user->addRole('ROLE_USER');

//            $authenticatedToken = new AperioUserToken($user->getRoles());

            //$authenticatedToken = new UsernamePasswordToken($user->getUsername(), '', 'secured_area', $user->getRoles());
            //$authenticatedToken->setAttribute('email', $user->getEmail());
            //$authenticatedToken->setUser($user);

            //$user = $this->userProvider->loadUserByUsername($token->getUsername());
            $authenticatedToken = new AperioUserToken($user->getRoles());
            //$authenticatedToken->setUser($user); //TODO: this causes infinite redirect

            //$this->get('security.context')->setToken($token);
            //return $this->redirect($this->generateUrl('scanorder_new'));
            return $authenticatedToken;

        } else {
            //exit("bad exit AperioProvider");
            throw new AuthenticationException('The Aperio authentication failed.');
        }

    }

//    protected function validateDigest($digest, $nonce, $created, $secret)
//    {
//        // Validate Secret
//        $expected = base64_encode(sha1(base64_decode($nonce).$created.$secret, true));
//
//        return $digest === $expected;
//    }

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
