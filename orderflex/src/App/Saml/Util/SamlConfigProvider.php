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
use Symfony\Component\DependencyInjection\ContainerInterface;

class SamlConfigProvider
{

    //private $samlConfigRepository;

    public function __construct(
        private EntityManagerInterface $em,
        //private SamlConfigRepository $samlConfigRepository,
        private RequestStack $requestStack,
        private ContainerInterface $container
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
        $userTenantUtil = $this->container->get('user_tenant_utility');
        $tenantArr = $userTenantUtil->getCurrentTenantArr($this->requestStack->getCurrentRequest());

        $urlslug = '';
        if( $tenantArr && isset($tenantArr['urlslug']) ) {
            $urlslug = $tenantArr['urlslug'];
        }
        
        $scheme = $this->requestStack->getCurrentRequest()->getScheme();
        //echo "1 scheme=$scheme <br>"; //http
        $scheme = 'https'; //tenants are behind haproxy, therefore, schema will be http
        //echo "2 scheme=$scheme <br>"; //http

        //TODO: get $scheme from this tenant's DB

        if(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }
        //echo "2 scheme=$scheme <br>"; //http

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

        $IdentifierAttribute = 'email'; //limit 255
        $config->setIdentifierAttribute($IdentifierAttribute);

        $autoCreate = true; //bool
        $config->setAutoCreate($autoCreate);

        $attributeMapping = array(); //json
        $config->setAttributeMapping($attributeMapping);

        $spEntityId = 'https://login-proxy-test.weill.cornell.edu/idp'; //text
        $config->setSpEntityId($spEntityId);

        list($scheme, $host) = $this->getSPEntityId();
        //echo '$scheme='.$scheme.', host='.$host."<br>";
        //exit('getTestConfig');

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