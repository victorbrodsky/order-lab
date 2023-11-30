<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/30/2023
 * Time: 4:26 PM
 */

namespace App\UserdirectoryBundle\Controller;


use App\UserdirectoryBundle\Services\Router;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
//use App\Service\Router;

//Testing dynamic route
class RouterController extends AbstractController
{

    //#[Route(path: '/{path}', name: 'router', requirements: ['path' => '.+'])]
    public function router( Request $request, Router $router, $path )
    {
        exit($path);
        $result = $router->handle($path);

        if( $result ){
            $result['args']['request'] = $request;
            return $this->forward($result['class'], $result['args']);
        }


        throw $this->createNotFoundException('error page not found!');
    }

}