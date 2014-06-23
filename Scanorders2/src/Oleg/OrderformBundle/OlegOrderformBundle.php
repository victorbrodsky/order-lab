<?php

namespace Oleg\OrderformBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oleg\OrderformBundle\Security\Factory\AperioFactory;

use Oleg\OrderformBundle\Helper\Parameters;

class OlegOrderformBundle extends Bundle
{
    
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new AperioFactory());

    }

    public function getParent()
    {
        return 'FOSUserBundle';
    }
    
}
