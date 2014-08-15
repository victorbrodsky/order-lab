<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="employees_home")
     * @Template()
     */
    public function indexAction()
    {
        $name = "This is an Employee Directory";

        return array('name' => $name);
    }

    /**
     * @Route("/admin", name="employees_admin")
     * @Template("OlegUserdirectoryBundle:Default:index.html.twig")
     */
    public function adminAction()
    {
        $name = "This is an Employee Directory Admin Page!!!";
        return array('name' => $name);
    }


    /**
     * @Route("/hello/{name}", name="employees_hello")
     * @Template()
     */
    public function helloAction($name)
    {
        return array('name' => $name);
    }
}
