<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 10/15/2024
 * Time: 11:44 AM
 */

namespace App\Saml\Util;

use App\Saml\Entity\SamlConfig;
//use App\Saml\Repository\SamlConfigRepository;

use Doctrine\ORM\EntityManagerInterface;
use OneLogin\Saml2\Settings;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\KernelInterface;
use Psr\Log\LoggerInterface;

class SamlConfigProvider
{

    //private $samlConfigRepository;

    public function __construct(
        private EntityManagerInterface $em,
        //private SamlConfigRepository $samlConfigRepository,
        private RequestStack $requestStack,
        private ContainerInterface $container,
        private KernelInterface $appKernel,
        private LoggerInterface $logger
    ) {
        //$this->samlConfigRepository = $samlConfigRepository;
        //$this->samlConfigRepository = $doctrine->getRepository('CmsBundle:Page');
        //$this->samlConfigRepository = SamlConfig::getRepository($this->em);
    }

    //$client is the user's email or domain
    public function getConfig(string $client): ?array
    {
        //return $this->getTestConfig($client);

        //$client is the user's email, findByClient is using the email's domain
        // and find configuration file by name=email's domain (name='med.cornell.edu')

        $config = NULL;

        //check if $client is email
        if( str_contains($client, '@') ) {
            $config = $this->em->getRepository(SamlConfig::class)->findByClient($client);
        }

        if( !$config ) {
            $config = $this->em->getRepository(SamlConfig::class)->findByDomain($client);
        }

        if (!$config) {
            return NULL;
            //throw new \Exception('SAML configuration not found for client ' . $client);
        }

        list($scheme, $host) = $this->getSPEntityId();

        $schemeAndHost = sprintf('%s://%s', $scheme, $host);
        $this->logger->notice("SamlConfigProvider->getConfig: schemeAndHost=".$schemeAndHost);
        //echo '$schemeAndHost='.$schemeAndHost."<br>"; //https://view.online/c/wcm/pathology/
        //exit('111');

        $this->logger->notice("SamlConfigProvider->getConfig: idp singleLogoutService=".$config->getIdpSloUrl());
        $this->logger->notice("SamlConfigProvider->getConfig: sp singleLogoutService=".$schemeAndHost."saml/logout/".$client);

        //echo 'idp cert='.$config->getIdpCert()."<br>";
        //echo 'SpPrivateKey='.$config->getSpPrivateKey()."<br>";
        //echo 'IdentifierAttribute='.$config->getIdentifierAttribute()."<br>";

        //with encryption error:
        //https://login-test.weill.cornell.edu/idp/saml2/idp/SSOService.php?
        //SAMLRequest=fVJdb9swDPwrht5txcpHVyEJkC0YFqBbgzjbQ18KWaIbAbKkiXLT%2FPspdlN0WxG9EDryjkeCcxSt8XzVxYPdwe8OMGYvrbHI%2B8SCdMFyJ1Ajt6IF5FHyavX9jrNixH1w0UlnyDvKdYZAhBC1syTbrBfk8XY8HovZtJasLsv6ZtKUM3arPrGRAsWaaXqTRkI9umlI9gsCJuaCJKFER%2BxgYzEKGxM0YpO8LPNyvGclL2d8On0g2TpNo62IPesQo0dOqXFP2uYxpYojaGMK6YKFFEF1VCtP01SNNkDPphndgdIBZKRVdU%2By1cX%2FF2exayFUEJ61hJ%2B7u387JJmX07U%2BrVOdgcIfPD0vjuIQWS4k9uiggJ5k29dFf9ZWaft0fcf1UIT8236%2Fzbf31Z4s52dl3u8sLD%2Fw%2BYFF9HP6njUfLuVH6rdZb53R8pR9daEV8bqdM6JV3vSlPAZhUYONhF4UK%2Bl88nv5vl4hhM36zemzhmPhrNEWqKRH2VIv4sGlAU50cPk374K9SdP%2Fz3z5Bw%3D%3D
        //https://login-test.weill.cornell.edu/idp/module.php/core/loginuserpassorg.php?

        //$encriptionFlag = true;
        //$encriptionFlag = false;

        $settings = array(
            'strict' => false,
            //'strict' => true,

            // Set a BaseURL to be used instead of try to guess
            // the BaseURL of the view that process the SAML Message.
            // Ex http://sp.example.com/
            //    http://example.com/sp/
            'baseurl' => null, //'https://view.online/c/wcm/pathology/',
            //'baseurl' => 'https://view.online/c/wcm/pathology/',

            // Enable debug mode (to print errors).
            //'debug' => true,

            'security' => [
//                'nameIdEncrypted' => $encriptionFlag,
//                'authnRequestsSigned' => $encriptionFlag,
//                'logoutRequestSigned' => $encriptionFlag,
//                'logoutResponseSigned' => $encriptionFlag,
//                'signMetadata' => $encriptionFlag,
//                'wantMessagesSigned' => $encriptionFlag,
//                'wantAssertionsEncrypted' => $encriptionFlag,

//                // Indicates a requirement for the <saml:Assertion> elements received by
//                // this SP to be signed. [Metadata of the SP will offer this info]
                //'wantAssertionsSigned' => false,

////                // Indicates a requirement for the NameID element on the SAMLResponse
////                // received by this SP to be present.
//                'wantNameId' => true,

//                // Indicates a requirement for the NameID received by
//                // this SP to be encrypted.
//                'wantNameIdEncrypted' => false,
//
////                // Authentication context.
////                // Set to false and no AuthContext will be sent in the AuthNRequest.
////                // Set true or don't present this parameter and you will get an AuthContext 'exact' 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'.
////                // Set an array with the possible auth context values: array ('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509').
                //'requestedAuthnContext' => false,

////                // Indicates if the SP will validate all received xmls.
////                // (In order to validate the xml, 'strict' and 'wantXMLValidation' must be true).
//                'wantXMLValidation' => true,

//                // If true, SAMLResponses with an empty value at its Destination
//                // attribute will not be rejected for this fact.
//                'relaxDestinationValidation' => true, //false,
//
//                // If true, the toolkit will not raised an error when the Statement Element
//                // contain atribute elements with name duplicated
//                'allowRepeatAttributeName' => false,

//                // If true, Destination URL should strictly match to the address to
//                // which the response has been sent.
//                // Notice that if 'relaxDestinationValidation' is true an empty Destination
//                // will be accepted.
//                'destinationStrictlyMatches' => false,

//                // If true, SAMLResponses with an InResponseTo value will be rejected if not
//                // AuthNRequest ID provided to the validation method.
//                'rejectUnsolicitedResponsesWithInResponseTo' => false,

                // Algorithm that the toolkit will use on signing process. Options:
                //    'http://www.w3.org/2000/09/xmldsig#rsa-sha1'
                //    'http://www.w3.org/2000/09/xmldsig#dsa-sha1'
                //    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256'
                //    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha384'
                //    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha512'
                // Notice that sha1 is a deprecated algorithm and should not be used
                //'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',

                // Algorithm that the toolkit will use on digest process. Options:
                //    'http://www.w3.org/2000/09/xmldsig#sha1'
                //    'http://www.w3.org/2001/04/xmlenc#sha256'
                //    'http://www.w3.org/2001/04/xmldsig-more#sha384'
                //    'http://www.w3.org/2001/04/xmlenc#sha512'
                // Notice that sha1 is a deprecated algorithm and should not be used
                //'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',

                // ADFS URL-Encodes SAML data as lowercase, and the toolkit by default uses
                // uppercase. Turn it True for ADFS compatibility on signature verification
                //'lowercaseUrlencoding' => false,
            ],
            'idp' => [
                'entityId' => $schemeAndHost."saml/metadata/".$client,
                'singleSignOnService' => [
                    //'url' => 'https://login-proxy-test.weill.cornell.edu/idp/profile/SAML2/Redirect/SSO/'.$client, //$config->getIdpSsoUrl(),
                    //'url' => $config->getIdpSsoUrl(),
                    'url' => $schemeAndHost."saml/acs/".$client,
                    //'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
                ],
                'singleLogoutService' => [
                    'url' => $config->getIdpSloUrl(),
                    //'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
                ],
                'x509cert' => $config->getIdpCert(),
                //'privateKey' => $config->getSpPrivateKey(),
                //'url' => '',
            ],
            'sp' => [
                'entityId' => $schemeAndHost,
                'assertionConsumerService' => [
                    'url' => $schemeAndHost."saml/acs/".$client,
                    //"binding" => "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
                    //'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
                ],
                'singleLogoutService' => [
                    'url' => $schemeAndHost."saml/logout/".$client,
                    //"binding" => "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
                    //'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
                ],
                //'x509cert' => $config->getIdpCert(),
                'privateKey' => $config->getSpPrivateKey(),
                //'url' => '',
            ],
        );

        return [
//            'settings' => new Settings([
//                'idp' => [
//                    'entityId' => $schemeAndHost."/saml/metadata/".$client,
//                    'singleSignOnService' => ['url' => $config->getIdpSsoUrl()],
//                    'singleLogoutService' => ['url' => $config->getIdpSloUrl()],
//                    'x509cert' => $config->getIdpCert(),
//                ],
//                'sp' => [
//                    'entityId' => $schemeAndHost,
//                    'assertionConsumerService' => [
//                        'url' => $schemeAndHost."/saml/acs/".$client,
//                    ],
//                    'singleLogoutService' => [
//                        'url' => $schemeAndHost."/saml/logout/".$client,
//                    ],
//                    'privateKey' => $config->getSpPrivateKey(),
//                ],
//            ]),
            //'client' => $client,
            'settings' => $settings,
            'identifier' => $config->getIdentifierAttribute(),
            'autoCreate' => $config->getAutoCreate(),
            'attributeMapping' => $config->getAttributeMapping(),
            'CustomerUrl' => $config->getSpEntityId(),
            //'CustomerUrl' => 'http://view.online/c/wcm/pathology/'
        ];
    }


    private function getSPEntityId()
    {
        $userTenantUtil = $this->container->get('user_tenant_utility');
        $tenantArr = $userTenantUtil->getCurrentTenantArr($this->requestStack->getCurrentRequest());

        $urlslug = '';
        if( $tenantArr && isset($tenantArr['urlslug']) ) {
            $urlslug = $tenantArr['urlslug'];
        }
        
        $scheme = $this->requestStack->getCurrentRequest()->getScheme();
        //echo "1 scheme=$scheme <br>"; //http

        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }
        //echo "2 scheme=$scheme <br>"; //http

        $scheme = 'https'; //tenants are behind haproxy, therefore, schema will be http
        //echo "3 scheme=$scheme <br>"; //http

        $host = $this->requestStack->getCurrentRequest()->getHost();
        //echo "1 host=$host <br>"; //view.online

        //$host = $userTenantUtil->getCurrentTenantHost($this->requestStack->getCurrentRequest()); //view.online/c/wcm/pathology

        if( $urlslug && $urlslug != '/' ) {
            $host = $host . "/" . $urlslug . "/";
        }

        //$uri = $this->requestStack->getCurrentRequest()->getUri();
        //echo "1 uri=$uri <br>"; //http://view.online/c/wcm/pathology/saml/login/oli2002@med.cornell.edu

        if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];

            //$urlslug = $userTenantUtil->getCurrentTenantUrlslug($this->requestStack->getCurrentRequest());
            if( $urlslug && $urlslug != '/' ) {
                $host = $host . "/" . $urlslug . "/";
            }
        }
        //echo "2 host=$host <br>";

        return [$scheme, $host];
    }

//    private function getTestConfig( string $client ): array
//    {
//
//        $projectRoot = $this->appKernel->getProjectDir();
//        //echo "projectRoot=".$projectRoot."<br>";
//
//        //$samlconfigYamlPath = $projectRoot . "/" . "config" . "/" . "samlconfig.yaml";
//        //$samlconfig = Yaml::parse(file_get_contents($samlconfigYamlPath));
//        //$certificate = $samlconfig['saml']['certificate'];
//        //exit('getTestConfig: certificate='.$certificate);
//        //$privatekey = $samlconfig['saml']['privatekey'];
//
//        $certPath = $projectRoot . "/" . "config" . "/" . "saml_cert.pem";
//        if (file_exists($certPath)) {
//            //echo "The file $certPath exists <br>";
//        } else {
//            echo "The file $certPath does not exist <br>";
//        }
//        $certificate = file_get_contents($certPath);
//        //$certificate = file($certPath,FILE_IGNORE_NEW_LINES);
//        //Signature validation failed. SAML Response rejected
//        //$certificate = implode('',$certificate);
//        //dump($certificate);
//        //exit('111');
//        //echo "certificate= [$certificate] <br>";
//        //dump($certificate);
//
//        $privatekeyPath = $projectRoot . "/" . "config" . "/" . "saml_private.pem";
//        if (file_exists($privatekeyPath)) {
//            //echo "The file $privatekeyPath exists <br>";
//        } else {
//            echo "The file $privatekeyPath does not exist <br>";
//        }
//        $privatekey = file_get_contents($privatekeyPath);
//        //$privatekey = file($privatekeyPath,FILE_IGNORE_NEW_LINES);
//        //Signature validation failed. SAML Response rejected
//        //$privatekey = implode('',$privatekey);
//        //Signature validation failed. SAML Response rejected
//        //echo "private key= [$privatekey] <br>";
//
//        //Use separate file for private key
//
//        $config = new SamlConfig();
//
//        $IdpSsoUrl = "https://login-proxy-test.weill.cornell.edu/idp/profile/SAML2/Redirect/SSO";
//        $config->setIdpSsoUrl($IdpSsoUrl);
//
//        $IdpSloUrl = "https://login-proxy-test.weill.cornell.edu/idp/profile/SAML2/Redirect/SLO";
//        $config->setIdpSloUrl($IdpSloUrl);
//
//        $IdpCert = $certificate;
//        $config->setIdpCert($IdpCert);
//        //echo "cert=".$config->getIdpCert()."<br>";
//        //exit('111');
//
//        $SpPrivateKey = $privatekey;
//        $config->setSpPrivateKey($SpPrivateKey);
//        //echo "SpPrivateKey=".$config->getSpPrivateKey()."<br>";
//        //exit('111');
//
//        $IdentifierAttribute = 'email'; //limit 255
//        $config->setIdentifierAttribute($IdentifierAttribute);
//
//        $autoCreate = true; //bool
//        $config->setAutoCreate($autoCreate);
//
//        $attributeMapping = array(); //json
//        $config->setAttributeMapping($attributeMapping);
//
//        $spEntityId = 'https://login-proxy-test.weill.cornell.edu/idp'; //text
//        $config->setSpEntityId($spEntityId);
//
//        list($scheme, $host) = $this->getSPEntityId();
//        //echo '$scheme='.$scheme.', host='.$host."<br>";
//        //exit('getTestConfig');
//
//        //testing
//        //$scheme = 'https';
//        //$host = 'view.online/c/wcm/pathology/directory/';
//        $host = 'view.online/c/wcm/pathology/';
//        //$host = 'view.online/c/wcm/pathology/index_dev.php/directory/';
//
//        //testing
//        //$scheme = 'https';
//        //$host = 'view-test.med.cornell.edu/directory/';
//
//        //exit('$scheme='.$scheme.', host='.$host);
//
//        $schemeAndHost = sprintf('%s://%s', $scheme, $host);
//
//        //TRy to set 'security' => array ('destinationStrictlyMatches' => false)
//
//        $settings = array(
//            'strict' => false,
//            // Enable debug mode (to print errors).
//            'debug' => true,
//            'security' => [
//                'nameIdEncrypted' => false,
//                'authnRequestsSigned' => false,
//                'logoutRequestSigned' => false,
//                'logoutResponseSigned' => false,
//                'signMetadata' => false,
//                'wantMessagesSigned' => false,
//                'wantAssertionsEncrypted' => false,
//
//                // Indicates a requirement for the <saml:Assertion> elements received by
//                // this SP to be signed. [Metadata of the SP will offer this info]
//                'wantAssertionsSigned' => false,
//
////                // Indicates a requirement for the NameID element on the SAMLResponse
////                // received by this SP to be present.
//                'wantNameId' => true,
//
//                // Indicates a requirement for the NameID received by
//                // this SP to be encrypted.
//                'wantNameIdEncrypted' => false,
//
////                // Authentication context.
////                // Set to false and no AuthContext will be sent in the AuthNRequest.
////                // Set true or don't present this parameter and you will get an AuthContext 'exact' 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'.
////                // Set an array with the possible auth context values: array ('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509').
////                'requestedAuthnContext' => false,
////
////                // Indicates if the SP will validate all received xmls.
////                // (In order to validate the xml, 'strict' and 'wantXMLValidation' must be true).
////                'wantXMLValidation' => true,
////
////                // If true, SAMLResponses with an empty value at its Destination
////                // attribute will not be rejected for this fact.
////                'relaxDestinationValidation' => true, //false,
////
////                // If true, the toolkit will not raised an error when the Statement Element
////                // contain atribute elements with name duplicated
////                'allowRepeatAttributeName' => false,
////
////                // If true, Destination URL should strictly match to the address to
////                // which the response has been sent.
////                // Notice that if 'relaxDestinationValidation' is true an empty Destination
////                // will be accepted.
////                'destinationStrictlyMatches' => false,
////
////                // If true, SAMLResponses with an InResponseTo value will be rejected if not
////                // AuthNRequest ID provided to the validation method.
////                'rejectUnsolicitedResponsesWithInResponseTo' => false,
//
//                // Algorithm that the toolkit will use on signing process. Options:
//                //    'http://www.w3.org/2000/09/xmldsig#rsa-sha1'
//                //    'http://www.w3.org/2000/09/xmldsig#dsa-sha1'
//                //    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256'
//                //    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha384'
//                //    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha512'
//                // Notice that sha1 is a deprecated algorithm and should not be used
//                //'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
//
//                // Algorithm that the toolkit will use on digest process. Options:
//                //    'http://www.w3.org/2000/09/xmldsig#sha1'
//                //    'http://www.w3.org/2001/04/xmlenc#sha256'
//                //    'http://www.w3.org/2001/04/xmldsig-more#sha384'
//                //    'http://www.w3.org/2001/04/xmlenc#sha512'
//                // Notice that sha1 is a deprecated algorithm and should not be used
//                //'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
//
//                // ADFS URL-Encodes SAML data as lowercase, and the toolkit by default uses
//                // uppercase. Turn it True for ADFS compatibility on signature verification
//                //'lowercaseUrlencoding' => false,
//            ],
//            'idp' => [
//                'entityId' => $schemeAndHost."/saml/metadata/".$client,
//                'singleSignOnService' => ['url' => $config->getIdpSsoUrl()],
//                'singleLogoutService' => ['url' => $config->getIdpSloUrl()],
//                'x509cert' => $config->getIdpCert(),
//                'privateKey' => $config->getSpPrivateKey(),
//            ],
//            'sp' => [
//                'entityId' => $schemeAndHost,
//                'assertionConsumerService' => [
//                    'url' => $schemeAndHost."/saml/acs/".$client,
//                    "binding" => "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
//                ],
//                'singleLogoutService' => [
//                    'url' => $schemeAndHost."/saml/logout/".$client,
//                    "binding" => "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
//                ],
//                'x509cert' => $config->getIdpCert(),
//                'privateKey' => $config->getSpPrivateKey(),
//            ],
//        );
//
//        return [
////            'settings' => new Settings([
////                'idp' => [
////                    'entityId' => $schemeAndHost."/saml/metadata/".$client,
////                    'singleSignOnService' => ['url' => $config->getIdpSsoUrl()],
////                    'singleLogoutService' => ['url' => $config->getIdpSloUrl()],
////                    'x509cert' => $config->getIdpCert(),
////                ],
////                'sp' => [
////                    'entityId' => $schemeAndHost,
////                    'assertionConsumerService' => [
////                        'url' => $schemeAndHost."/saml/acs/".$client,
////                    ],
////                    'singleLogoutService' => [
////                        'url' => $schemeAndHost."/saml/logout/".$client,
////                    ],
////                    'privateKey' => $config->getSpPrivateKey(),
////                ],
////            ]),
//            'settings' => $settings,
//            'identifier' => $config->getIdentifierAttribute(),
//            'autoCreate' => $config->getAutoCreate(),
//            'attributeMapping' => $config->getAttributeMapping(),
//            'CustomerUrl' => $config->getSpEntityId(),
//        ];
//    }

}