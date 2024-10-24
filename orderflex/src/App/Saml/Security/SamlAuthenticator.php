<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 10/15/2024
 * Time: 4:49 PM
 */

namespace App\Saml\Security;

use App\Saml\Util\SamlConfigProvider;
use App\Saml\Security\SamlUserProvider;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OneLogin\Saml2\Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SamlAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private SamlConfigProvider $samlConfigProvider,
        private SamlUserProvider $userProvider,
        private JWTTokenManagerInterface $jWTManager
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'saml_acs';
    }

    public function authenticate(Request $request): Passport
    {
        exit('SamlAuthenticator: authenticate');
        $client = $request->attributes->get('client');
        $config = $this->samlConfigProvider->getConfig($client);
        $auth = new Auth($config['settings']);
        $auth->processResponse();
        if (!$auth->isAuthenticated()) {
            throw new AuthenticationException('SAML authentication failed.');
        }

        $attributes = $auth->getAttributes();
        $identifierAttribute = $config['identifier'];
        $identifierValue = $attributes[$identifierAttribute][0];

        // Load or create the user
        $this->userProvider->setIdentifierField($identifierAttribute);
        $user = $this->userProvider->loadUserByIdentifier($identifierValue);

        if (!$user && $config['autoCreate']) {
            $user = $this->userProvider->createUserFromSamlAttributes($identifierValue, $attributes, $config['attributeMapping']);
        }

        if (!$user) {
            throw new AuthenticationException('User not found and auto-creation is disabled.');
        }

        return new SelfValidatingPassport(new UserBadge($identifierValue, function () use ($user) {
            return $user;
        }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
    {
        // On success, generate JWT and return it
        $user = $token->getUser();
        $jwt = $this->generateJwtToken($user);

        $client = $request->attributes->get('client');
        $config = $this->samlConfigProvider->getConfig($client);

        $opw = $config['CustomerUrl'];

        $url = sprintf("%s?j=%s", $opw, $jwt);
        return new RedirectResponse($url);

    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // On failure, return appropriate response
        return new JsonResponse(['error' => $exception->getMessageKey()], Response::HTTP_UNAUTHORIZED);
    }

    private function generateJwtToken($user)
    {
        return $this->jWTManager->create($user);
    }
}
