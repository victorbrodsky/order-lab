<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 12/1/2023
 * Time: 5:13 PM
 */

//https://symfony.com/doc/6.4/routing/custom_route_loader.html

namespace App\Routing;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ExtraController extends OrderAbstractController
//class ExtraController extends AbstractController
{

    //#[Route(path: '/extra', name: 'extraRoute')]

    public function extra(mixed $parameter): Response
    {
        //exit('<br>extra');
        return new Response($parameter);
    }
}