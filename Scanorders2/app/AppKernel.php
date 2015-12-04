<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;


class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Oneup\UploaderBundle\OneupUploaderBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Ensepar\Html2pdfBundle\EnseparHtml2pdfBundle(),
            new Spraed\PDFGeneratorBundle\SpraedPDFGeneratorBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),

            //new Acme\DemoBundle\AcmeDemoBundle(),
            new Oleg\UserdirectoryBundle\OlegUserdirectoryBundle(),
            new Oleg\OrderformBundle\OlegOrderformBundle(),
            new Oleg\FellAppBundle\OlegFellAppBundle(),

            new Bmatzner\FontAwesomeBundle\BmatznerFontAwesomeBundle(),
        );

//		echo "environment:<br>";
//		print_r($this->getEnvironment());
//        echo "<br>";
		
        if (in_array($this->getEnvironment(), array('dev', 'test'))) {

            //disable opcache for dev and test environment
            ini_set('opcache.enable',false);

            //$bundles[] = new Acme\DemoBundle\AcmeDemoBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

}
