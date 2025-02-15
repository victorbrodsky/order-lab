<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 12/1/2023
 * Time: 5:08 PM
 */

//Enable routes based on container parameters in Symfony 2.3
//https://stackoverflow.com/questions/18506028/enable-routes-based-on-container-parameters-in-symfony-2-3

namespace App\SystemBundle;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
//use Symfony\Component\Config\FileLocator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;


class CustomTenancyLoader extends Loader {

    private $container;
    private $em;
    //protected $security;

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em
        //Security $security
    )
    {
        $this->container = $container;
        $this->em = $em;
        //$this->security = $security;
    }

    public function supports( $resource, $type = null )
    {
        return $type === 'custom';
        //return $type === 'custom' && $this->param == 'multitenancy';
    }

    public function load( $resource, $type = null )
    {
        //echo '###CustomTenancyLoader load'.'<br>###'; exit('222');

        $multitenancy = $this->container->getParameter('multitenancy');
        //echo "CustomTenancyLoader: multitenancy=".$multitenancy."<br>";

        if( $multitenancy == 'multitenancy' ) {
            //$this->container->setParameter('defaultlocale', 'main');
            //$config = 'routes-multi.yaml';
            //if( $this->container->getParameter('systemdb') ) {
                //echo '###CustomTenancyLoader load'.'<br>###';
            //    $config = 'routes-multi-default.yaml';
            //}

            if( $this->container->getParameter('full-multitenancy') ) {
                $config = 'routes-multi-full.yaml';
            } else {
                $config = 'routes-multi-default.yaml';
            }

        } else {
            //$this->container->setParameter('defaultlocale', ''); //Impossible to call set() on a frozen ParameterBag
            $config = 'routes-single.yaml';
        }

        // This method will only be called if it suits the parameters
        $routes   = new RouteCollection;
        //$resource = '@AcmeFooBundle/Resources/config/custom_routing.yml';

        $configDirectory = __DIR__.'/../../../config/';
        //$configDirectory = '../../config/';

        $resource = $configDirectory.$config;
        //echo $multitenancy.": add resource=".$resource."<br>";
        //exit('CustomTenancyLoader');
        $logger = $this->container->get('logger');
        $logger->notice("CustomTenancyLoader load=".$multitenancy.": add config route=".$config);

        $type = 'yaml';

        $routes->addCollection($this->import($resource, $type));

        return $routes;
    }
}
