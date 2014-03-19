<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 3/18/14
 * Time: 5:59 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;

use Oleg\OrderformBundle\Security\Authentication\AperioToken;
use Oleg\OrderformBundle\Security\Authentication\AperioProvider;

class AperioListener implements ListenerInterface {

    protected $securityContext;
    protected $authenticationManager;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        //print_r($authenticationManager);
    }

    public function handle(GetResponseEvent $event)
    {

        //TODO: it is not used
        return;
        //exit("using handle Aperio Listener");

        $request = $event->getRequest();

        //$username = $request->get('_username');
        //$password = $request->get('_password');
        //echo "username=".$username.", password=".$password."<br>";

        $token = new AperioToken();
        //$token->setUsername($username);
        //$token->setCredentials($password);

        //$user = $this->userProvider->loadUserByUsername($token->username);
        //$token->setUser($user);

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);
            return;

        } catch (AuthenticationException $failed) {

            //exit('aperio auth error');
            // ... you might log something here

            // To deny the authentication clear the token. This will redirect to the login page.
            // Make sure to only clear your token, not those of other authentication listeners.
             $token = $this->securityContext->getToken();
             if( $token instanceof AperioToken ) {  //&& $this->providerKey === $token->getProviderKey()) {
                 $this->securityContext->setToken(null);
             }
             return;
        }

        // By default deny authorization
        $response = new Response();
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
    }

}