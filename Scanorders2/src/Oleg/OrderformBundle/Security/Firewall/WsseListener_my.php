<?php

namespace Oleg\OrderformBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Oleg\OrderformBundle\Security\Authentication\Token\WsseUserToken;
use Oleg\OrderformBundle\Security\User\WebserviceUser;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

//use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

//extends AbstractAuthenticationListener //
class WsseListener implements ListenerInterface 
{
    protected $securityContext;
    protected $authenticationManager;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager)
    {
        //parent::__construct($securityContext, $authenticationManager);
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {           
        $request = $event->getRequest();
        
        //echo "Listener handle request:<br>";
        //print_r($request);
        //echo "<br><br>Others:<br>";       
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');
        //echo "username=".$username.", passowrd=".$password."<br>";

        //$wsseRegex = '/UsernameToken Username="([^"]+)", PasswordDigest="([^"]+)", Nonce="([^"]+)", Created="([^"]+)"/';
//        if (    !$request->headers->has('x-wsse') || 
//                1 !== preg_match($wsseRegex, $request->headers->get('x-wsse'), $matches)
//           ) 
//        {
//            
//        }
        //preg_match($wsseRegex, $request, $matches);
        //echo "matches:";
        //print_r($matches);
        
        $token = new WsseUserToken();
//        $token->setUser($matches[1]);
//        $token->digest   = $matches[2];
//        $token->nonce    = $matches[3];
//        $token->created  = $matches[4];
               
        //since we don't use wsse, make token with user and passowrd only
        $user = new WebserviceUser($username, $password, '', array());
        $token->setUser($user);
        $token->digest   = $password;
//        $token->nonce    = $matches[3];
//        $token->created  = $matches[4];

        //make aperio authentication
        
        //$authToken = $token;
        //$this->securityContext->setToken($authToken);     
        
        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);
            echo " Token is ok<br>";
            return;
        } catch (AuthenticationException $failed) {
            // ... you might log something here
            echo " Token is not ok<br>";
            // To deny the authentication clear the token. This will redirect to the login page.
            // Make sure to only clear your token, not those of other authentication listeners.
             $token = $this->securityContext->getToken();
//             if( $token instanceof WsseUserToken && $this->providerKey === $token->getProviderKey() ) {
                 $this->securityContext->setToken(null);
//             }
             return;

            // Deny authentication with a '403 Forbidden' HTTP response
            $response = new Response();
            $response->setStatusCode(403);
            $event->setResponse($response);                      

        }

        // By default deny authorization
        $response = new Response();
        $response->setStatusCode(403);
        $event->setResponse($response);
    }
    
}

?>
