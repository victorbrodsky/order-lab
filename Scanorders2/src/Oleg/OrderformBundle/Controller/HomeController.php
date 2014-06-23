<?php

namespace Oleg\OrderformBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class HomeController extends Controller {

    public function mainCommonHomeAction() {
        return $this->render('OlegOrderformBundle:Default:main-common-home.html.twig');
    }

}
