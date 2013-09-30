<?php

namespace Oleg\OrderformBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Oleg\OrderformBundle\Security\Authentication\Token\WsseUserToken;
use Oleg\OrderformBundle\Security\User\WebserviceUserProvider;

include_once 'DatabaseRoutines.php';

class WsseProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $cacheDir;

    public function __construct(UserProviderInterface $userProvider, $cacheDir)
    {     
        //echo "Custom Authentication<br>";
//        exit();
        $this->userProvider = $userProvider;
        $this->cacheDir     = $cacheDir;            
    }

    public function authenticate(TokenInterface $token)
    {
        echo "WSSE Custom Authentication<br>";
        //exit();
        
        //make my user provider to work
        //$webServiceUserProvider = new WebserviceUserProvider();
        echo "Load user ... <br>";
        $user = $this->userProvider->loadUserByUsername($token->getUsername());
        //$user = $webServiceUserProvider->loadUserByUsername($token->getUsername());
        echo "User is loaded! <br>";
        print_r($user);
        
        echo "<br>Token:<br>";
        print_r($token);
        //exit();
        
        if( $user && $this->validateDigest($token->getUsername(), $token->digest, $token->nonce, $token->created, $user->getPassword()) ) {
            $authenticatedToken = new WsseUserToken($user->getRoles());
            $authenticatedToken->setUser($user);

            echo "ok: authenticatedToken:<br>";
            //print_r($authenticatedToken);
            
            return $authenticatedToken;
        } else {
            echo "not ok: authenticatedToken:<br>";
            //print_r($authenticatedToken);
            throw new AuthenticationException('The WSSE authentication failed.');
        }
    }

    protected function validateDigest($username, $digest, $nonce, $created, $secret)
    {
        echo "Custom Authentication<br>";
        //exit();
        echo "validateDigest<br>";
        //echo "username=".$username.", digest=".$digest.", secret=".$secret."<br>";
        
        $AuthResult = ADB_Authenticate($username, $digest);
        
        if( isset($AuthResult['UserId']) ) {
            echo "Aperio auth OK<br>";
            return true;
        } else {
            echo "Aperio auth FAILED<br>";
            return false;
        }
        
        //print_r($userData);
        
        // Check created time is not in the future
//        if (strtotime($created) > time()) {
//            return false;
//        }

        // Expire timestamp after 5 minutes
//        if (time() - strtotime($created) > 300) {
//            return false;
//        }

        // Validate nonce is unique within 5 minutes
//        if (file_exists($this->cacheDir.'/'.$nonce) && file_get_contents($this->cacheDir.'/'.$nonce) + 300 > time()) {
//            throw new NonceExpiredException('Previously used nonce detected');
//        }
        // If cache directory does not exist we create it
//        if (!is_dir($this->cacheDir)) {
//            mkdir($this->cacheDir, 0777, true);
//        }
//        file_put_contents($this->cacheDir.'/'.$nonce, time());

        // Validate Secret
        $expected = base64_encode(sha1(base64_decode($nonce).$created.$secret, true));

        //return $digest === $expected;     
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof WsseUserToken;
    }
}

?>
