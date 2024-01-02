<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/29/2023
 * Time: 12:42 PM
 */

namespace App\Routing\DependencyInjection\Compiler;


use App\UserdirectoryBundle\Entity\SiteParameters;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

//use Symfony\Component\HttpKernel\Config\FileLocator;
//use Symfony\Component\Routing\Loader\YamlFileLoader;

//How to load Symfony's config parameters from database (Doctrine)
//https://stackoverflow.com/questions/28713495/how-to-load-symfonys-config-parameters-from-database-doctrine
//https://symfony.com/doc/current/service_container/compiler_passes.html
//Defined in Kernel.php

class ParametersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        //echo '###ParametersCompilerPass process'.'<br>###'; exit('111');
        $em = $container->get('doctrine.orm.default_entity_manager');

        $siteParameters = $em->getRepository(SiteParameters::class)->findAll();
        //exit('$siteParameters count='.count($siteParameters));
        if( count($siteParameters) > 0 ) {
            //exit('111');
            $this->setRoutingParameters($container,$siteParameters[0]);
        }

        //$paypal_params = $em->getRepository('featureBundle:paymentGateways')->findAll();
        //$container->setParameter('paypal_data', $paypal_params);
        //exit('11111');
    }

    public function setRoutingParameters( $container, $siteParameters ) {

        //dump($siteParameters);
        //exit('setRoutingParameters 111');

        $authServerNetwork = $siteParameters->getAuthServerNetwork();
        //echo '$authServerNetwork='.$authServerNetwork."<br>";

        if( !$authServerNetwork ) {
            return;
        }

        $multitenancy = 'singletenancy'; //USed by CustomTenancyLoader

        if( $authServerNetwork->getName() == 'Internet (Hub)' ) {
            echo 'ParametersCompilerPass: $authServerNetwork Name='.$authServerNetwork->getName()."<br>";

            $hostedUserGroups = $authServerNetwork->getHostedUserGroups();
            //echo '$hostedUserGroup count='.count($hostedUserGroups)."<br>";
            foreach($hostedUserGroups as $hostedUserGroup) {
                //echo '$hostedUserGroup='.$hostedUserGroup."<br>";
            }

            $multitenancy = 'multitenancy'; //USed by CustomTenancyLoader
            $container->setParameter('defaultlocale', 'main');
            $container->setParameter('locdel', '/'); //locale delimeter '/'

            $multilocales = 'main|c/wcm/pathology|c/lmh/pathology';
            $container->setParameter('multilocales', $multilocales);

//            $configDirectory = __DIR__.'/../../../../../config/packages/';
//            $loader = new YamlFileLoader($container, new FileLocator($configDirectory));
//            $loader->load('firewalls.yml');
//            $loader->load('security_access_control.yml');
        }

        $container->setParameter('multitenancy', $multitenancy);
        echo '###ParametersCompilerPass multitenancy='.$multitenancy.'###'; //exit('111');

        //$container->setParameter('multilocales', $multilocales);
    }
}