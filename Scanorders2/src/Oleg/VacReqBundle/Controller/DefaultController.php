<?php

namespace Oleg\VacReqBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//vacreq site

class DefaultController extends Controller
{

    /**
     * @Route("/about", name="vacreq_about_page")
     * @Template("OlegUserdirectoryBundle:Default:about.html.twig")
     */
    public function aboutAction( Request $request ) {
        return array('sitename'=>$this->container->getParameter('vacreq.sitename'));
    }

    /**
     * @Route("/", name="vacreq_home")
     * @Template("OlegVacReqBundle:Default:index.html.twig")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('OlegVacReqBundle:Default:index.html.twig');
    }
}
