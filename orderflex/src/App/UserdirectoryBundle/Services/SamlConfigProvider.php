<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 10/15/2024
 * Time: 11:44 AM
 */

namespace App\UserdirectoryBundle\Services;


use App\UserdirectoryBundle\Repository\SamlConfigRepository;
use OneLogin\Saml2\Settings;
use Symfony\Component\HttpFoundation\RequestStack;

class SamlConfigProvider
{

    public function __construct(
        private SamlConfigRepository $samlConfigRepository,
        private RequestStack $requestStack
    ) {
    }

    public function getConfig(string $client): array
    {
        $config = $this->samlConfigRepository->findByClient($client);

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
        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }

        $host = $this->requestStack->getCurrentRequest()->getHost();
        if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        }

        return [$scheme, $host];
    }

}