<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

//use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/order1")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name);
    }
    
    /**
     * @Route("/")
     * @Template()
     */
    public function form1Action()
    {
        //$task = new Task();
        //$form = $this->createForm(new TaskType(), $task);

        //'form' => $form->createView(),
        return $this->render('OlegOrderformBundle:Default:form1.html.twig', array(           
            'user' => 'username',
            'form' => 'form',
        ));
        
    }
}
