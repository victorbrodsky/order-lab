<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

//use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/11111sdasdfsf")
 */
class OrderController extends Controller
{
    /**
     * @Route("/", name="order")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        
        //check if user is authenticated
//        $securityContext = $this->container->get('security.context');
//        if( $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') || 
//            $this->get('security.context')->isGranted('ROLE_USER')   
//                ){
//            $auth = true;
//            $username = $securityContext->getToken()->getUser()->getUsername();
//            //print_r($username);          
//        } else {
//            $auth = false;
//            $username = null;
//        }
        
        return array(
            //'auth' => $auth,
            //'username' => $username,
        );
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
