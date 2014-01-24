<?php
namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Oleg\OrderformBundle\Entity\User;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @Method("GET")
     * @Template()
     */
    public function loginAction()
    {

        $request = $this->getRequest();
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render(
            'OlegOrderformBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
            )
        );

    }

    /**
     * @Route("/login_check", name="login_check")
     * @Method("POST")
     * @Template("OlegOrderformBundle:ScanOrder:new_orig.html.twig")
     */
    public function loginCheckAction( Request $request )
    {
        //exit("my login check!");
    }


    /**
     * @Route("/logout", name="logout")
     * @Template()
     */
    public function logoutAction( Request $request )
    {

        $this->get('security.context')->setToken(null);
        $this->get('request')->getSession()->invalidate();

        //return $this->forward('OlegOrderformBundle:Security:login');
        return $this->redirect($this->generateUrl('login'));
    }




}

?>
