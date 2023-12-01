<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 12/1/2023
 * Time: 5:08 PM
 */

//https://symfony.com/doc/6.4/routing/custom_route_loader.html

namespace App\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ExtraLoader extends Loader {

    private $isLoaded = false;

    public function load($resource, string $type = null): RouteCollection
    {

        //exit('pre load');

        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        //exit('load');

        $routes = new RouteCollection();

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
        return 'extra' === $type;
    }
}