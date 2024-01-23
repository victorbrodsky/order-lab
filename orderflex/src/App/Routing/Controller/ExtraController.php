<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 12/1/2023
 * Time: 5:13 PM
 */

//https://symfony.com/doc/6.4/routing/custom_route_loader.html

namespace App\Routing\Controller;

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

        $multilocales = $this->getParameter('multilocales');
        //$multilocales = $this->getParameter('multilocales-urls'); //system|c/wcm/pathology|c/lmh/pathology
        //$multilocales = "main|c/wcm/pathology|c/lmh/pathology";
        $multilocalesUrlArr = explode("|", $multilocales);
        //dump($multilocalesUrlArr);
        //exit('111');

        $params = array(
            'title' => $title,
            'multilocales' => $multilocalesUrlArr
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

    //#[Route(path: '/system-init', name: 'system_home', methods: ['GET'])]
    //#[Template('AppUserdirectoryBundle/Default/home.html.twig')]
    public function systemHomeAction(Request $request)
    {
        //exit('exit systemHomeAction');
        $title = "System Manager";

        $multilocales = $this->getParameter('multilocales');
        //$multilocales = $this->getParameter('multilocales-urls'); //system|c/wcm/pathology|c/lmh/pathology
        //$multilocales = "main|c/wcm/pathology|c/lmh/pathology";
        $multilocalesUrlArr = explode("|", $multilocales);
        //dump($multilocalesUrlArr);
        //exit('111');

        $params = array(
            'title' => $title,
            'multilocales' => $multilocalesUrlArr
        );


        return $this->render('AppUserdirectoryBundle/System/home.html.twig', $params);
        
        //exit('<br>extra');
        return $params;
        //return $this->render('AppRouting/home.html.twig', ['param' => array()]);
        //$response = new Response();
        //return $response;
    }

    public function systemInitAction(Request $request)
    {
        exit('exit systemInitAction');
        $title = "Welcome to the View!";

        $multilocales = $this->getParameter('multilocales');
        //$multilocales = $this->getParameter('multilocales-urls'); //system|c/wcm/pathology|c/lmh/pathology
        //$multilocales = "main|c/wcm/pathology|c/lmh/pathology";
        $multilocalesUrlArr = explode("|", $multilocales);
        //dump($multilocalesUrlArr);
        //exit('111');

        $params = array(
            'title' => $title,
            'multilocales' => $multilocalesUrlArr
        );

        return $this->render('lucky/number.html.twig', ['number' => $number]);

        //exit('<br>extra');
        return $params;
        //return $this->render('AppRouting/home.html.twig', ['param' => array()]);
        //$response = new Response();
        //return $response;
    }

}