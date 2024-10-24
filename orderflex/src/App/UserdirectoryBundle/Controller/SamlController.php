<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 10/15/2024
 * Time: 11:51 AM
 */

namespace App\UserdirectoryBundle\Controller;

use App\UserdirectoryBundle\Security\Authentication\SamlUserProvider;
use App\UserdirectoryBundle\Util\SamlConfigProvider;
use OneLogin\Saml2\Auth;
use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OneLogin\Saml2\Settings;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\UserdirectoryBundle\Security\Authentication\SamlAuthenticator;

class SamlController extends AbstractController
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

    // 127.0.0.1/directory/saml/login/oli2002
    //#[Route(path: '/about', name: 'employees_about_page')]
    //#[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    ///**
    // * @Route("/saml/login/{client}", name="saml_login", requirements={"client"=".+"})
    //*/
    //#[Route(path: '/saml/login/{client}', name: 'saml_login'), requirements:["client"=>".+"]]
    //#[Route(path: '/saml/login/{client}', name: 'user_saml_login', requirements: ['client' => '.+'])]
    public function login(Request $request, $client): Response
    {
        //exit('saml login');
        $this->logger->info("Starting SAML login for client: $client");

        $config = $this->samlConfigProvider->getConfig($client);
        $auth = new Auth($config['settings']);
        $auth->login();

        // The login method does a redirect, so we won't reach this line
        return new Response('Redirecting to IdP...', 302);
    }

//    /**
//     * @Route("/saml/acs/{client}", name="saml_acs", requirements={"client"=".+"})
//     */
//    #[Route(path: '/saml/acs/{client}', name: 'user_saml_acs', requirements: ['client' => '.+'])]
    public function acs(Request $request, $client): Response
    {
        $this->logger->info("Processing SAML ACS for client: $client");


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

//    /**
//     * @Route("/saml/logout/{client}", name="saml_logout", requirements={"client"=".+"})
//     */
//    #[Route(path: '/saml/logout/{client}', name: 'user_saml_logout', requirements: ['client' => '.+'])]
    public function logout(Request $request, string $client): Response
    {
        $this->logger->info("Starting SAML logout for client: $client");
        $config = $this->samlConfigProvider->getConfig($client);
        try {
            $auth = new Auth($config['settings']);
            $auth->logout();

            // The logout method does a redirect, so we won't reach this line
            return new Response('Redirecting to IdP for logout...', 302);
        } catch (Error $e) {
            $this->logger->critical(sprintf('Unable to logout client with message: "%s"', $e->getMessage()));
            throw new UnprocessableEntityHttpException('Error while trying to logout');
        }
    }

//    /**
//     * @Route("/saml/sls/{client}", name="saml_sls", requirements={"client"=".+"})
//     */
//    #[Route(path: '/saml/sls/{client}', name: 'user_saml_sls', requirements: ['client' => '.+'])]
    public function sls(Request $request, string $client): Response
    {
        $this->logger->info("Processing SAML Logout for client: $client");

        $config = $this->samlConfigProvider->getConfig($client);
        $auth = new Auth($config['settings']);

        $auth->processSLO();

        $errors = $auth->getErrors();
        if (!empty($errors)) {
            return new Response('SAML Logout failed: ' . implode(', ', $errors), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Redirection après une déconnexion réussie
        return $this->redirect(sprintf('https://%s/sign-in?nosso=1', $config['CustomerUrl']));
    }

//    /**
//     * @Route("/saml/metadata/{client}", name="saml_metadata", requirements={"client"=".+"})
//     */
    //https://view.online/c/wcm/pathology/saml/metadata/oli2002@med.cornell.edu
    //https://view.online/c/wcm/pathology/directory/saml/metadata/oli2002@med.cornell.edu
    //127.0.0.1/directory/saml/metadata/oli2002@med.cornell.edu
    //#[Route(path: '/directory/saml/metadata/{client}', name: 'user_saml_metadata', requirements: ['client' => '.+'])]
    //#[Route(path: '/saml/metadata/{client}', name: 'user_saml_metadata', requirements: ['client' => '.+'])]
    public function metadata(string $client): Response
    {
        $config = $this->samlConfigProvider->getConfig($client);
        $metadata = (new Settings($config['settings']))->getSPMetadata();
        return new Response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

}