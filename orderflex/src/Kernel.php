<?php

namespace App;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use App\Routing\DependencyInjection\Compiler\ParametersCompilerPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
//use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
//use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    ////////////// Working /////////////////
    private const CONFIG_EXTS = '.{php,xml,yaml,yml}'; //'.{php,xml,yaml,yml}';
    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }


// The deprecations are still appearing because Symfony’s cache warmer uses the @!WebProfiler/… namespace,
// which deliberately bypasses templates/bundles/ overrides and compiles the original vendor template.
// There’s also no newer symfony/web-profiler-bundle release to update to.
// So, kernel.request listener were added in Kernel.php that installs a custom error handler
// after Symfony’s ErrorHandler and swallows only the three E_USER_DEPRECATED messages
// containing mailer.html.twig and the macro-tag warning. Everything else is passed through unchanged.
//Disable when symfony/web-profiler-bundle is available
    public function boot(): void
    {
        parent::boot();

        if ($this->debug && $this->container->has('event_dispatcher')) {
            $this->container->get('event_dispatcher')->addListener(
                'kernel.request',
                function (RequestEvent $event) {
                    static $registered = false;
                    if ($registered) {
                        return;
                    }
                    $registered = true;

                    $previousHandler = set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$previousHandler) {
                        if (E_USER_DEPRECATED === $errno
                            && false !== strpos($errstr, 'Using the "macro" tag outside the root of a template')
                            && false !== strpos($errstr, 'mailer.html.twig')) {
                            return true;
                        }
                        if ($previousHandler) {
                            return $previousHandler($errno, $errstr, $errfile, $errline);
                        }

                        return false;
                    });
                },
                2049
            );
        }
    }

//    public function registerBundles(): iterable
//    {
//        $contents = require $this->getProjectDir().'/config/bundles.php';
//        foreach ($contents as $class => $envs) {
//            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
//                yield new $class();
//            }
//        }
//    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        //(new Dotenv(false))->loadEnv(dirname(__DIR__).'/.env');
        //use Symfony\Component\Dotenv\Dotenv;
        //exit(__DIR__.'/../.env');
        //$dotenv = new Dotenv();
        //$dotenv->load(__DIR__.'/../.env');

        //$container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
        $container->setParameter('.container.dumper.inline_class_loader', \PHP_VERSION_ID < 70400 || $this->debug);
        $container->setParameter('.container.dumper.inline_factories', true);

        //$container->setParameter('secret','123');
        $confDir = $this->getProjectDir().'/config';

//        echo "1=".$confDir.'/{packages}/*'.self::CONFIG_EXTS."<br>";
//        echo "2=".$confDir.'/{packages}/'.$this->environment.'/*'.self::CONFIG_EXTS."<br>";
//        echo "3=".$confDir.'/{services}'.self::CONFIG_EXTS."<br>";
//        echo "4=".$confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS."<br>";
        //exit('111');

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

//    public function build(ContainerBuilder $container): void
//    {
//        //load Symfony's config parameters from database (Doctrine)
//        //https://symfony.com/doc/current/service_container/compiler_passes.html
//        $container->addCompilerPass(
//            new ParametersCompilerPass(),
//            //PassConfig::TYPE_AFTER_REMOVING,
//            //PassConfig::TYPE_REMOVE,
//            //PassConfig::TYPE_BEFORE_OPTIMIZATION
//            //PassConfig::TYPE_OPTIMIZE
//            PassConfig::TYPE_BEFORE_REMOVING
//            //1000
//        );
//    }

}
