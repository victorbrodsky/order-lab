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
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class AperioListener implements ListenerInterface  {

    protected $securityContext;
    protected $authenticationManager;
    protected $providerKey;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, $providerKey = null)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
    }

    public function handle(GetResponseEvent $event)
    {

        //use default handle
        return;

        //exit("using handle Aperio Listener");

        $request = $event->getRequest();
        $username = $request->get('_username');
        $password = $request->get('_password');
        //echo "username=".$username.", password=".$password."<br>";

        if( !$username || $username == "" ) {
            return;
        }

        $unauthenticatedToken = new UsernamePasswordToken(
            $username,
            $password,
            $this->providerKey
        );

        try {

            $authToken = $this->authenticationManager->authenticate($unauthenticatedToken);

            $this->securityContext->setToken($authToken);

            return;

        } catch (AuthenticationException $failed) {

            //exit('aperio auth error');

            // To deny the authentication clear the token. This will redirect to the login page.
            // Make sure to only clear your token, not those of other authentication listeners.
            $unauthenticatedToken = $this->securityContext->getToken();
            if( $unauthenticatedToken instanceof AperioToken ) {//&& $this->providerKey === $unauthenticatedToken->getProviderKey()) {
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