<?php

namespace Oleg\UserdirectoryBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\UserdirectoryBundle\Entity\AccessRequest;


class DefaultController extends Controller
{

    /**
     * @Route("/", name="employees_home")
     * @Template("OlegUserdirectoryBundle:Default:home.html.twig")
     */
    public function indexAction()
    {

        if(
            false == $this->get('security.context')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        return array('accessreqs' => count($accessreqs));
    }

    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->get('security.context')->isGranted('ROLE_USERDIRECTORY_ADMIN') ) {
            return null;
        }
        $userSecUtil = $this->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->container->getParameter('employees.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
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
