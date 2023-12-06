<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/30/2023
 * Time: 4:35 PM
 */

namespace App\UserdirectoryBundle\Services;


//Testing dynamic route
class Router
{

    public function handle($path)
    {
        switch ($path) {
            case "test":
                return [
                    "class" => "App\Controller\TestController::index",
                    "args"  => [
                        "locale" => 'en'
                    ]
                ];
            case "tester":
                return [
                    "class" => "App\Controller\TestController::index",
                    "args"  => [
                        "locale" => 'fr'
                    ]
                ];
            default:
                return false;
        }
    }

}