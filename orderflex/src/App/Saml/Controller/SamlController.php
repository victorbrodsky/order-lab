<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 10/15/2024
 * Time: 11:51 AM
 */

namespace App\Saml\Controller;

use App\Saml\Entity\SamlConfig;
use App\Saml\Security\SamlUserProvider;
use App\Saml\Util\SamlConfigProvider;
use App\Saml\Security\SamlAuthenticator;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use OneLogin\Saml2\Auth;
use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OneLogin\Saml2\Settings;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;


//Set in .env:
//JWT_SECRET_KEY=%kernel.project_dir%/config/saml_private.pem
//JWT_PUBLIC_KEY=%kernel.project_dir%/config/saml_cert.pem
//JWT_PASSPHRASE=
//TODO: set these keys dynamically from DB

class SamlController extends OrderAbstractController //AbstractController
{

    public function __construct(
        private SamlConfigProvider $samlConfigProvider,
        private UserAuthenticatorInterface $userAuthenticator,
        private SamlAuthenticator $authenticator,
        private SamlUserProvider $samlUserProvider,
        private LoggerInterface $logger
    ) {
        //empty constructor
    }

    //1) https://view.online/c/wcm/pathology/saml/login/oli2002@med.cornell.edu
    //2) SamlAuthenticator->supports
    //3) SamlAuthenticator->authenticate
    //4) 

    //Test: https://login-proxy-test.weill.cornell.edu/idp/saml2/idp/SSOService.php?spentityid=https://view.online/c/wcm/pathology/
    // 127.0.0.1/saml/login/oli2002
    // https://view.online/c/wcm/pathology/saml/login/oli2002@med.cornell.edu
    // https://view.online/c/wcm/pathology/saml/login/vib9020@med.cornell.edu
    // https://view.online/c/wcm/pathology/saml/login/oli2002@med.cornell.edu/employees
    // 127.0.0.1/saml/login/oli2002@med.cornell.edu
    //#[Route(path: '/about', name: 'employees_about_page')]
    //#[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    ///**
    // * @Route("/saml/login/{client}", name="saml_login", requirements={"client"=".+"})
    //*/
    //#[Route(path: '/saml/login/{client}', name: 'saml_login'), requirements:["client"=>".+"]]
    #[Route(path: '/login/{client}/{sitename}', name: 'saml_login', requirements: ['client' => '.+'], options: ['expose' => true])]
    public function login(Request $request, $client, $sitename): Response
    {
        //var_dump(getenv('JWT_SECRET_KEY'));
        //exit('saml login');
        //$this->logger->notice("Starting SAML login for client: $client");

        $config = $this->samlConfigProvider->getConfig($client);
        //$this->logger->notice("SAML login after config");
        //dump($config);

        $this->logger->notice("SAML login after config: sitename=$sitename");

        $lastRoute = $request->query->get('lastroute');
        $this->logger->notice("SAML login after config: lastRoute=$lastRoute");

        if( !$config ) {
            return $this->redirect($this->generateUrl($sitename.'_login'));
        }

        $auth = new Auth($config['settings']);
        //$this->logger->notice("SAML login after new Auth");

        $useEmailLastRoute = true;
        //$useEmailLastRoute = false;
        if( $useEmailLastRoute ) {

            //store current user in the RelayState: client_#_$lastRoute
            //$deliemeter = "_#_";
            //$deliemeter = "__";
            $deliemeter = "_**_";

            $lastRoute = $client . $deliemeter . $sitename . $deliemeter . $lastRoute;
            //$this->logger->notice("Starting SAML login for client: modified lastRoute=$lastRoute");

            $auth->login($lastRoute);
        } else {
            $auth->login();
        }

        //$this->logger->notice("SAML login after login");

        // The login method does a redirect, so we won't reach this line
        return new Response('Redirecting to IdP...', 302);
    }

    #[Route(path: '/acs/original/{client}', name: 'saml_acs_orig', requirements: ['client' => '.+'])]
    public function acsOrig(Request $request, $client): Response
    {
        //exit('acsOrig');
        $this->logger->notice("Processing SAML ACS original for client: $client");

        $config = $this->samlConfigProvider->getConfig($client);
        $auth = new Auth($config['settings']);
        $auth->processResponse();

        if (!$auth->isAuthenticated()) {
            $this->logger->error("SAML authentication failed for client: $client");
            return new Response('SAML authentication failed.', Response::HTTP_UNAUTHORIZED);
        }

        $attributes = $auth->getAttributes();
        $identifier = $attributes[$config['identifier']][0];

        try {
            $user = $this->samlUserProvider->loadUserByIdentifier($identifier);
            return $this->userAuthenticator->authenticateUser(
                $user,
                $this->authenticator,
                $request
            );
        } catch (\Exception $e) {
            $this->logger->error("Error during SAML authentication for client: $client, error: " . $e->getMessage());
            return new Response('Authentication exception occurred.', Response::HTTP_UNAUTHORIZED);
        }
    }

    //The root 'saml_acs' is authenticated by SamlAuthenticator->authenticate(Request $request)
    //If the root is different, then th  is controller authentication is used
    //https://view.online/c/wcm/pathology/saml/login/oli2002@med.cornell.edu
    #[Route(path: '/acs', name: 'saml_acs_default')]
    public function acs(Request $request): Response
    {
        //echo "client=$client <br>";
        //exit('acs Test');
        $this->logger->notice("Processing SAML ACS saml_acs_default for client");

        //dump($request);
        //exit('acsTest');

        $relayState = $request->getPayload()->get('RelayState');
        //$samlResponse = $request->getPayload()->get('SAMLResponse');
        //echo 'relayState='.$relayState."<br>";

        $client = '';
        //$somestring = '/login/';
        if( str_contains($relayState,'/login/')) {
            //$client = (string) substr($somestring, strrpos("/$somestring", '/'));
            $parts = explode('/', $relayState);
            $client = array_pop($parts);
        }
        //exit('client='.$client);

        $config = $this->samlConfigProvider->getConfig($client);
        $auth = new Auth($config['settings']);
        //exit('acsTest after new Auth');

        $auth->processResponse();
        //exit('acsTest after processResponse');

        if (!$auth->isAuthenticated()) {
            $this->logger->error("SAML authentication failed for client: $client");
            return new Response('SAML authentication failed.', Response::HTTP_UNAUTHORIZED);
        }

        $attributes = $auth->getAttributes();
        //$identifier = $attributes[$config['identifier']][0];

        $identifierAttribute = $config['identifier'];

        //dump($attributes);
        //dump($config);
        //exit('111');

        $modifiedIdentifierAttribute = 'user.'.$identifierAttribute; //saml has 'user.email' attribute; or use 'userPrincipalName'
        $identifierValue = $attributes[$modifiedIdentifierAttribute][0];
        //echo 'acs: identifierValue='.$identifierValue."<br>"; //identifierValue=oli2002@med.cornell.edu

        //$this->samlUserProvider->setIdentifierField($identifierAttribute);          //'email'
        //$user = $this->samlUserProvider->loadUserByIdentifier($identifierValue);
        //exit('111 user='.$user);

        try {
            $this->samlUserProvider->setIdentifierField($identifierAttribute);          //'email'
            $user = $this->samlUserProvider->loadUserByIdentifier($identifierValue);
            //exit('111 user='.$user);
            return $this->userAuthenticator->authenticateUser(
                $user,
                $this->authenticator,
                $request
            );
        } catch (\Exception $e) {
            //Error during SAML authentication for client: oli2002@med.cornell.edu, error:
            // Unable to create a signed JWT from the given configuration.
            //Update public/private keys in .env and then convert it DB
            $this->logger->error("Error during SAML authentication for client: $client, error: " . $e->getMessage());
            return new Response('Authentication exception occurred.', Response::HTTP_UNAUTHORIZED);
        }
    }

    //NOT USED: because client is not provided, use /logout/ instead
    //check symfony available routes: No route found for "POST http://view.online/c/wcm/pathology/saml/logout
    //https://view.online/c/wcm/pathology/saml/logout/oli2002@med.cornell.edu
    #[Route(path: '/logout/{client}', name: 'saml_logout_orig', requirements: ['client' => '.+'])]
    public function logoutOrig(Request $request, string $client): Response
    {
        //exit('logoutOrig');
        $this->logger->notice("Starting SAML logout for client: $client");
        $config = $this->samlConfigProvider->getConfig($client);
        try {

            //dump($config);
            //exit('logout');

//            $user = $this->getUser();
//            if( $user ) {
//                //exit('User exists='.$user->getId());
//                if( $this->tokenStorage ) {
//                    $this->tokenStorage->setToken(null);
//                }
//                //$userSecUtil = $this->container->get('user_security_utility');
//                //$userSecUtil->userLogoutSymfony7(true);
//            }
            //exit('User does not exist');

            $this->logger->notice("Starting SAML logout: before new Auth");
            $auth = new Auth($config['settings']);

            $this->logger->notice("Starting SAML logout: before logout");

            //$returnTo = 'https://view.online/c/wcm/pathology/directory/login';
            //$logoutUrl = $auth->logout($returnTo,array(),null,null,$stay = true);
            $auth->logout();
            $this->logger->notice("Starting SAML logout: after logout");
            //$this->logger->notice("Starting SAML logout: after logout: logoutUrl=".$logoutUrl);
            //exit('logout');

            // The logout method does a redirect, so we won't reach this line
            return new Response('Redirecting to IdP for logout...', 302);
        } catch (Error $e) {
            $this->logger->critical(sprintf('Unable to logout client with message: "%s"', $e->getMessage()));
            throw new UnprocessableEntityHttpException('Error while trying to logout');
        }
    }
    //Used in SAML logout final step
    // https://view.online/c/wcm/pathology/saml/logout/oli2002@med.cornell.edu
    #[Route(path: '/logout', name: 'saml_logout')]
    public function logout(Request $request): Response
    {
        //exit('logout');
        //$this->logger->notice("SamlController logout: Start");

        $relayState = $request->getPayload()->get('RelayState');
        $this->logger->notice("SamlController logout: 1relayState=".$relayState);

        //$relayState = str_replace('http','https',$relayState);
        $userUtil = $this->container->get('user_utility');
        $scheme = $userUtil->getRealScheme($request);
        $relayState = str_replace('http',$scheme,$relayState);
        $this->logger->notice("SamlController logout: 2relayState=".$relayState);

        //return new Response('Redirecting to IdP for logout...', 302);
        return $this->redirect( $relayState );
    }

//    #[Route(path: '/sls/{client}', name: 'saml_sls', requirements: ['client' => '.+'])]
//    public function sls(Request $request, string $client): Response
//    {
//        exit('sls');
//        $this->logger->notice("Processing SAML Logout for client: $client");
//
//        $config = $this->samlConfigProvider->getConfig($client);
//        $auth = new Auth($config['settings']);
//
//        $auth->processSLO();
//
//        $errors = $auth->getErrors();
//        if (!empty($errors)) {
//            return new Response('SAML Logout failed: ' . implode(', ', $errors), Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
//
//        // Redirection après une déconnexion réussie
//        return $this->redirect(sprintf('https://%s/sign-in?nosso=1', $config['CustomerUrl']));
//    }

    //https://view.online/c/wcm/pathology/saml/login/oli2002@med.cornell.edu
    //https://view.online/c/wcm/pathology/saml/metadata/oli2002@med.cornell.edu
    #[Route(path: '/metadata/{client}', name: 'saml_metadata', requirements: ['client' => '.+'])]
    public function metadata(string $client): Response
    {
        //dump($client);
        //exit('metadata');
        $this->logger->notice("metadata: client: $client");

        //exit('0 testing metadata');
        $config = $this->samlConfigProvider->getConfig($client);
        //$config['settings']['client'] = $client;
        $metadata = (new Settings($config['settings']))->getSPMetadata();

        //Unable to locate metadata for 'https://view.online/c/wcm/pathology/directory/'
        //dump($metadata);
        //exit('testing metadata');

        return new Response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

//    //#[Template('AppUserdirectoryBundle/Default/thanksfordownloading.html.twig')]
//    //http://127.0.0.1/saml/settings/oli2002
//    #[Route(path: '/settings/{client}', name: 'saml_settings', requirements: ['client' => '.+'])]
//    public function settingsAction( string $client ): Response
//    {
//        $this->logger->notice("settingsAction");
//        //exit('0 testing metadata');
//        //$config = $this->samlConfigProvider->getConfig($client);
//        //$metadata = (new Settings($config['settings']))->getSPMetadata();
//
//        $title = "SAML configuration";
//
//        $em = $this->getDoctrine()->getManager();
//        $configEntity = $em->getRepository(SamlConfig::class)->findByClient($client);
//        if( !$configEntity ) {
//            //create or add $configEntity for this tenant.
//            $configEntity = new SamlConfig();
//            exit('Create $configEntity');
//
//        }
//
//        $config = $this->samlConfigProvider->getConfig($client);
//
//        //exit('Exit: settingsAction');
//
//        return $this->render('AppSaml/config.html.twig', [
//            // this array defines the variables passed to the template,
//            // where the key is the variable name and the value is the variable value
//            // (Twig recommends using snake_case variable names: 'foo_bar' instead of 'fooBar')
//            'config' => $config,
//            'title' => $title,
//        ]);
//    }

}