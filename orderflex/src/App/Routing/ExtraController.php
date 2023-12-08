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
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;

class ExtraController extends OrderAbstractController {

    #[Template('AppRouting/home.html.twig')]
    public function extra(Request $request)
    {

        $title = "Welcome to the View!";

        $params = array(
            'title' => $title
        );
        
        //exit('<br>extra');
        return $params;
        //return $this->render('AppRouting/home.html.twig', ['param' => array()]);
        //$response = new Response();
        //return $response;
    }

    public function extra_orig(Request $request): Response
    {
        //exit('<br>extra');
        $response = new Response();
        return $response;
    }

}