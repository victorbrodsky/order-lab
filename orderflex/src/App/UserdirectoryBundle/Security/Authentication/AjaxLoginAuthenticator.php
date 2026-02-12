<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 2/12/2026
 * Time: 4:47 PM
 */

namespace App\UserdirectoryBundle\Security\Authentication;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AjaxLoginAuthenticator extends AbstractAuthenticator
{
    private $container;

    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }

    public function supports(Request $request): ?bool
    {
        // Trigger only for AJAX login endpoint
        return $request->isMethod('POST')
            && $request->attributes->get('_route') === 'employees_ajax_login';
    }

    public function authenticate(Request $request): Passport
    {
        $logger = $this->container->get('logger');
        $logger->notice('AjaxLoginAuthenticator authenticate: start');
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;
        $logger->notice("AjaxLoginAuthenticator authenticate: username=$username, password=$password");

        if (!$username || !$password) {
            throw new AuthenticationException('Missing credentials');
        }

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?JsonResponse {
        return new JsonResponse([
            'authenticated' => true
        ]);
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): ?JsonResponse {
        return new JsonResponse([
            'authenticated' => false,
            'error' => $exception->getMessage()
        ], 401);
    }
}
