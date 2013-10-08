<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/7/13
 * Time: 11:24 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Security\Firewall;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface {

    private $router;
    private $security;

    public function __construct(Router $router, SecurityContext $security)
    {
        $this->router = $router;
        $this->security = $security;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $response = new RedirectResponse($this->router->generate('index',array('filter_search_box[filter]' => 'All Not Filled')));
        } else {
            $referer_url = $request->headers->get('referer');
            $response = new RedirectResponse($referer_url);
        }

        return $response;
    }

}