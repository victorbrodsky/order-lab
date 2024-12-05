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
use Psr\Log\LoggerInterface;

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

//NOT USED
//USE CustomGuardAuthenticator instead

class SamlAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private SamlConfigProvider $samlConfigProvider,
        private SamlUserProvider $userProvider,
        private JWTTokenManagerInterface $jWTManager,
        private LoggerInterface $logger
    ) {
    }

    public function supports(Request $request): ?bool
    {
        $this->logger->notice("SamlAuthenticator: supports");
        //exit('SamlAuthenticator: supports');
        //return false;//testing
        return $request->attributes->get('_route') === 'saml_acs_default_disable';
    }

    public function authenticate(Request $request): Passport
    {
        $testing = true;
        $testing = false;
        //exit('SamlAuthenticator: authenticate');
        $this->logger->notice("SamlAuthenticator: authenticate");

        //dump($request);
        //exit('authenticate');

        $relayState = $request->getPayload()->get('RelayState');
        if( $testing ) {
            $samlResponse = $request->getPayload()->get('SAMLResponse');
            //echo 'relayState='.$relayState."<br>";
        }

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

        if( $testing ) {
            $xmlDocument = $auth->getLastResponseXML(); //getXMLDocument();
            dump($xmlDocument);
            //exit('after $xmlDocument');
        }

        //isAuthenticated fail
        if (!$auth->isAuthenticated()) {
            if( $testing ) {
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
            }
            throw new AuthenticationException('SAML authentication failed.');
        } else {
            echo '<br> isAuthenticated Success! <br>';
        }
        //exit('after isAuthenticated');

        //TODO: load attributes
        $attributes = $auth->getAttributes();
        $identifierAttribute = $config['identifier'];
        echo 'authenticate: identifierAttribute='.$identifierAttribute."<br>";
        //dump($attributes);
        //exit('authenticate');
        $modifiedIdentifierAttribute = 'user.'.$identifierAttribute; //saml has 'user.email' attribute; or use 'userPrincipalName'
        $identifierValue = $attributes[$modifiedIdentifierAttribute][0];
        echo 'authenticate: identifierValue='.$identifierValue."<br>"; //identifierValue=oli2002@med.cornell.edu

        //exit('before setIdentifierField');

        // Load or create the user
        $this->userProvider->setIdentifierField($identifierAttribute);          //'email'
        $user = $this->userProvider->loadUserByIdentifier($identifierValue);    //'oli2002@med.cornell.edu'

        echo "user=".$user."<br>";

        //if (!$user && $config['autoCreate']) {
        //    $user = $this->userProvider->createUserFromSamlAttributes($identifierValue, $attributes, $config['attributeMapping']);
        //}

        if (!$user) {
            throw new AuthenticationException('User not found and auto-creation is disabled.');
        }

        //set session: $session->set('logintype','saml-sso');
        $session = $request->getSession();
        $session->set('logintype','saml-sso');
        $logintype = $session->get('logintype');
        $this->logger->notice("authenticate: logintype=".$logintype);

        echo "before SelfValidatingPassport, user=".$user."<br>";
        return new SelfValidatingPassport(new UserBadge($identifierValue, function () use ($user) {
            echo "SelfValidatingPassport OK, user=".$user."<br>";
            return $user;
        }));
    }

//    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey=null) : Response
//    {
//        $authenticationSuccess = $this->container->get($this->sitename.'_authentication_handler');
//        return $authenticationSuccess->onAuthenticationSuccess($request,$token);
//    }
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
    {
        // On success, generate JWT and return it
        $user = $token->getUser();
        $jwt = $this->generateJwtToken($user);
        //echo "jwt=".$jwt."<br>";

        //dump($token);
        //dump($request->attributes);
        //exit('onAuthenticationSuccess');

        //$client = $request->attributes->get('client');

        //get email domain from $user email
        $client = $user->getSingleEmail();

        $config = $this->samlConfigProvider->getConfig($client);

        $opw = $config['CustomerUrl'];

        $url = sprintf("%s?j=%s", $opw, $jwt);
        //echo "url=".$url."<br>";
        //exit('onAuthenticationSuccess');

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
