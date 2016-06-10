<?php

namespace Oleg\CallLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{

    /**
     * @Route("/about", name="calllog_about_page")
     * @Template("OlegUserdirectoryBundle:Default:about.html.twig")
     */
    public function aboutAction( Request $request ) {
        return array('sitename'=>$this->container->getParameter('calllog.sitename'));
    }


    /**
     * @Route("/", name="calllog_home")
     * @Template("OlegCallLogBundle:CallLog:home.html.twig")
     */
    public function homeAction( Request $request ) {

        $title = "Call Case Log";

        return array(
            //'entity' => $entity,
            //'form' => $form->createView(),
            //'cycle' => $cycle,
            'title' => $title,
        );

    }


//    public function indexAction()
//    {
//        return $this->render('OlegCallLogBundle:Default:index.html.twig');
//    }
}
