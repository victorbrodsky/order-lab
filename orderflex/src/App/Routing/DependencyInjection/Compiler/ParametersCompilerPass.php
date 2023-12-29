<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/29/2023
 * Time: 12:42 PM
 */

namespace App\Routing\DependencyInjection\Compiler;


use App\UserdirectoryBundle\Entity\SiteParameters;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

//How to load Symfony's config parameters from database (Doctrine)
//https://stackoverflow.com/questions/28713495/how-to-load-symfonys-config-parameters-from-database-doctrine
//https://symfony.com/doc/current/service_container/compiler_passes.html
//Defined in Kernel.php

class ParametersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $em = $container->get('doctrine.orm.default_entity_manager');

        $siteParameters = $em->getRepository(SiteParameters::class)->findAll();

        if( count($siteParameters) > 0 ) {
            $this->setRoutingParameters($container,$siteParameters[0]);
        }

        //$paypal_params = $em->getRepository('featureBundle:paymentGateways')->findAll();
        //$container->setParameter('paypal_data', $paypal_params);
        //exit('11111');
    }

    public function setRoutingParameters( $container, $siteParameters ) {

        //dump($siteParameters);
        //exit('111');

        $authServerNetwork = $siteParameters->getAuthServerNetwork();
        echo '$authServerNetwork='.$authServerNetwork."<br>";

        if( !$authServerNetwork ) {
            return;
        }

        if( $authServerNetwork->getName() == 'Internet (Hub)' ) {

            $hostedUserGroups = $authServerNetwork->getHostedUserGroups();
            echo '$hostedUserGroup count='.count($hostedUserGroups)."<br>";
            foreach($hostedUserGroups as $hostedUserGroup) {
                echo '$hostedUserGroup='.$hostedUserGroup."<br>";
            }

            $multilocales = 'main|c/wcm/pathology|c/lmh/pathology';
            $container->setParameter('multilocales', $multilocales);
        }



        //$container->setParameter('multilocales', $multilocales);
    }
}