<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *  Created by Oleg Ivanov oli2002
 */

// config/routes.php
//Conditional auto configuration routes for single or multi tenancy
//according to the "Server Role and Network Access:" = "Internet (Hub)”


use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
//\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $container

return static function (RoutingConfigurator $routes): void {

    $connection_channel = "%connection_channel%"; //$container->getParameter('connection_channel');
    $connection_channel = '%connection_channel%';
    echo "connection_channel=".$connection_channel."<br>";

    $multitenancy = false;
    //$multitenancy = true;

    if( $multitenancy ) {
        //Multi tenancy server
        $routes
            //->resource('routes-default.yaml')
            ->import('routes-default.yaml', 'attribute')
            ->prefix([
                // don't prefix URLs for English, the default locale
                'main' => '',
                'c-wcm-pathology' => '/c/wcm/pathology',
                'c-lmh-pathology' => '/c/lmh/pathology'
            ])
            ->schemes([$connection_channel])
            ->requirements([
                '_locale' => 'c/wcm/pathology|c/lmh/pathology'
            ])
        ;

        //$routes->schemes([$connection_channel]);
//        $routes->requirements([
//            '_locale' => 'c/wcm/pathology|c/lmh/pathology'
//        ]);

    } else {
        //Single server
        $routes->import('routes-default.yaml', 'attribute')
//            ->schemes([
//                $connection_channel
//            ])
        ;
    }

};
