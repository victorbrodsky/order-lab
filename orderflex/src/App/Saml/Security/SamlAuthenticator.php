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
        private JWTTokenManagerInterface $jWTManager,
        //private LoggerInterface $logger
    ) {
    }

    public function supports(Request $request): ?bool
    {
        //exit('SamlAuthenticator: supports');
        //return false;//testing
        return $request->attributes->get('_route') === 'saml_acs';
    }

    public function authenticate(Request $request): Passport
    {
        //exit('SamlAuthenticator: authenticate');
        //$this->logger->notice("SamlAuthenticator: authenticate");

        //dump($request);
        //exit('authenticate');

        $relayState = $request->getPayload()->get('RelayState');
        $samlResponse = $request->getPayload()->get('SAMLResponse');
        //echo 'relayState='.$relayState."<br>";

        $client = '';
        //$somestring = '/login/';
        if( str_contains($relayState,'/login/')) {
            //$client = (string) substr($somestring, strrpos("/$somestring", '/'));
            $parts = explode('/', $relayState);
            $client = array_pop($parts);
        }
        //exit('client='.$client);

        //TODO: {"error":"An authentication exception occurred."}. Where occurs

        //$client = $request->attributes->get('client');
        $config = $this->samlConfigProvider->getConfig($client);
        $auth = new Auth($config['settings']);
        //exit('after new Auth');

        $auth->processResponse();
        //exit('after processResponse');

        //isAuthenticated fail
        if (!$auth->isAuthenticated()) {
            dump($auth->getErrors());
            dump($auth->getLastErrorException());
            //getLastErrorException:
            // The response was received at
            // http://view.online/c/wcm/pathology/saml/acs
            // instead of
            // https://view.online/c/wcm/pathology/saml/acs
            dump($auth->getLastErrorReason());

            //Error in Response.php line 428: If find a Signature on the Response, validates it checking the original response
            exit('after isAuthenticated false'); //OneLogin: Auth -> Response->isValid
            throw new AuthenticationException('SAML authentication failed.');
        }
        exit('after isAuthenticated');

        $attributes = $auth->getAttributes();
        $identifierAttribute = $config['identifier'];
        $identifierValue = $attributes[$identifierAttribute][0];

        exit('before setIdentifierField');

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
