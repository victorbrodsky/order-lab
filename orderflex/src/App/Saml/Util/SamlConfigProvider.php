<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 10/15/2024
 * Time: 11:44 AM
 */

namespace App\Saml\Util;

use App\Saml\Entity\SamlConfig;
use Doctrine\ORM\EntityManagerInterface;
//use App\UserdirectoryBundle\Repository\SamlConfigRepository;
use OneLogin\Saml2\Settings;
use Symfony\Component\HttpFoundation\RequestStack;

class SamlConfigProvider
{

    //private $samlConfigRepository;

    public function __construct(
        private EntityManagerInterface $em,
        //private SamlConfigRepository $samlConfigRepository,
        private RequestStack $requestStack
    ) {
        //$this->samlConfigRepository = $samlConfigRepository;
        //$this->samlConfigRepository = $doctrine->getRepository('CmsBundle:Page');
        //$this->samlConfigRepository = SamlConfig::getRepository($this->em);
    }

    public function getConfig(string $client): array
    {
        return $this->getTestConfig($client);

        //$config = $this->samlConfigRepository->findByClient($client);
        $config = $this->em->getRepository(SamlConfig::class)->findByClient($client);

        if (!$config) {
            throw new \Exception('SAML configuration not found for client ' . $client);
        }

        list($scheme, $host) = $this->getSPEntityId();

        $schemeAndHost = sprintf('%s://%s', $scheme, $host);

        return [
            'settings' => new Settings([
                'idp' => [
                    'entityId' => $schemeAndHost."/saml/metadata/".$client,
                    'singleSignOnService' => ['url' => $config->getIdpSsoUrl()],
                    'singleLogoutService' => ['url' => $config->getIdpSloUrl()],
                    'x509cert' => $config->getIdpCert(),
                ],
                'sp' => [
                    'entityId' => $schemeAndHost,
                    'assertionConsumerService' => [
                        'url' => $schemeAndHost."/saml/acs/".$client,
                    ],
                    'singleLogoutService' => [
                        'url' => $schemeAndHost."/saml/logout/".$client,
                    ],
                    'privateKey' => $config->getSpPrivateKey(),
                ],
            ]),
            'identifier' => $config->getIdentifierAttribute(),
            'autoCreate' => $config->getAutoCreate(),
            'attributeMapping' => $config->getAttributeMapping(),
            'CustomerUrl' => $config->getSpEntityId(),
        ];
    }


    private function getSPEntityId()
    {
        $scheme = $this->requestStack->getCurrentRequest()->getScheme();
        echo "1 scheme=$scheme <br>";
        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }

        $host = $this->requestStack->getCurrentRequest()->getHost();
        if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        }

        return [$scheme, $host];
    }

    private function getTestConfig( string $client ): array
    {

        $config = new SamlConfig();

        $IdpSsoUrl = "https://login-proxy-test.weill.cornell.edu/idp/profile/SAML2/Redirect/SSO";
        $config->setIdpSsoUrl($IdpSsoUrl);

        $IdpSloUrl = "https://login-proxy-test.weill.cornell.edu/idp/profile/SAML2/Redirect/SLO";
        $config->setIdpSloUrl($IdpSloUrl);

        $IdpCert = 'testttt';
        $config->setIdpCert($IdpCert);

        $SpPrivateKey = 'testttt';
        $config->setSpPrivateKey($SpPrivateKey);

        $IdentifierAttribute = ''; //limit 255
        $config->setIdentifierAttribute($IdentifierAttribute);

        $autoCreate = true; //bool
        $config->setAutoCreate($autoCreate);

        $attributeMapping = array(); //json
        $config->setAttributeMapping($attributeMapping);

        $spEntityId = ''; //text
        $config->setSpEntityId($spEntityId);

        list($scheme, $host) = $this->getSPEntityId();
        echo '$scheme='.$scheme.', host='.$host."<br>";
        exit('getTestConfig');

        //testing
        //$scheme = 'https';
        //$host = 'view.online/c/wcm/pathology/directory/';
        //$host = 'view.online/c/wcm/pathology/';
        //$host = 'view.online/c/wcm/pathology/index_dev.php/directory/';

        //testing
        //$scheme = 'https';
        //$host = 'view-test.med.cornell.edu/directory/';

        //exit('$scheme='.$scheme.', host='.$host);

        $schemeAndHost = sprintf('%s://%s', $scheme, $host);

        $settings = array(
            'idp' => [
                'entityId' => $schemeAndHost."/saml/metadata/".$client,
                'singleSignOnService' => ['url' => $config->getIdpSsoUrl()],
                'singleLogoutService' => ['url' => $config->getIdpSloUrl()],
                'x509cert' => $config->getIdpCert(),
            ],
            'sp' => [
                'entityId' => $schemeAndHost,
                'assertionConsumerService' => [
                    'url' => $schemeAndHost."/saml/acs/".$client,
                ],
                'singleLogoutService' => [
                    'url' => $schemeAndHost."/saml/logout/".$client,
                ],
                'privateKey' => $config->getSpPrivateKey(),
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
            'settings' => $settings,
            'identifier' => $config->getIdentifierAttribute(),
            'autoCreate' => $config->getAutoCreate(),
            'attributeMapping' => $config->getAttributeMapping(),
            'CustomerUrl' => $config->getSpEntityId(),
        ];
    }

}