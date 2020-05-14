<?php

namespace App\UserdirectoryBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TelephonySiteParameter extends OrderAbstractController {

    protected $siteName;

    public function __construct() {
        $this->siteName = 'employees'; //controller is not setup yet, so we can't use $this->getParameter('employees.sitename');
    }

    /**
     * @Route("/verify-mobile/{phoneNumber}", name="employees_verify_mobile", methods={"GET"})
     */
    public function verifyMobileAction()
    {
        $em = $this->getDoctrine()->getManager();

        $signUps = $em->getRepository('AppUserdirectoryBundle:SignUp')->findAll();

        return $this->render('AppUserdirectoryBundle/SignUp/index.html.twig', array(
            'signUps' => $signUps,
            'title' => "Sign Up for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName
        ));
    }



}
