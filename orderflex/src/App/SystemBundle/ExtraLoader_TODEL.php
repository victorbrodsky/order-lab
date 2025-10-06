<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 12/1/2023
 * Time: 5:08 PM
 */

//https://symfony.com/doc/6.4/routing/custom_route_loader.html
//The routes defined using custom route loaders will be automatically cached by the framework.
//So whenever you change something in the loader class itself, don't forget to clear the cache.

namespace App\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

//NOT USED
class ExtraLoader_TODEL extends Loader {

    private $isLoaded = false;

    public function load($resource, string $type = null): RouteCollection
    {

        //exit('pre load');

        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        //exit('load');

        $routes = new RouteCollection();

        //https://stackoverflow.com/questions/32421469/symfony-routing-how-to-define-global-requirements-constraints-for-wildcards
        $globalRequirements = [
            'any' => '.*',
            //'tenantprefix' => '.*',
//            'id' => '[0-9]+',
//            'ident' => '[A-Za-z0-9]+',
//            'date' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
//            'year' => '[0-9]{4}',
        ];

        //dump($routes);
        //exit('111');

        /** @var RouteCollection $routes */
        $routes->addRequirements($globalRequirements);
        return $routes;


        // prepare a new route
        $path = '/extra/{parameter}';
        $defaults = [
            '_controller' => 'App\Routing\ExtraController::extra',
        ];
        $requirements = [
            'parameter' => '\d+',
        ];
        $route = new Route($path, $defaults, $requirements);

        // add the new route to the route collection
        $routeName = 'extraRoute';
        $routes->add($routeName, $route);

        $this->isLoaded = true;

        //dump($routes);
        //exit('111');

        return $routes;
    }

    public function supports($resource, string $type = null): bool
    {
        //exit('type='.$type);
        //return true; //'extra' === $type;
        return 'extra' === $type;
    }
}