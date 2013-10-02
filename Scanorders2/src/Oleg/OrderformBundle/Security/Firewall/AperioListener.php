<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oleg\OrderformBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Oleg\OrderformBundle\Security\Authentication\Token\AperioUserToken;
use Oleg\OrderformBundle\Entity\User;


/**
 * The AbstractAuthenticationListener is the preferred base class for all
 * browser-/HTTP-based authentication requests.
 *
 * Subclasses likely have to implement the following:
 * - an TokenInterface to hold authentication related data
 * - an AuthenticationProvider to perform the actual authentication of the
 *   token, retrieve the UserInterface implementation from a database, and
 *   perform the specific account checks using the UserChecker
 *
 * By default, this listener only is active for a specific path, e.g.
 * /login_check. If you want to change this behavior, you can overwrite the
 * requiresAuthentication() method.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AperioListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {

//        echo "Aperio handle<br>";
        //exit("AperioListener");

        if( $this->securityContext->getToken() ){
            return;
        }

        $request = $event->getRequest();
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');

        $token = new AperioUserToken();
        $token->username = $username;
        $token->digest = $password;

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);

            $session = $request->getSession();
            $session->set('_security_secured_area',  serialize($authToken));

//            exit("Aperio custom auth ok!");
            //$event->stopPropagation();

            return;
        } catch (AuthenticationException $failed) {
            //throw new AuthenticationException('The Aperio authentication failed:'.$failed);
        }

    }
}
