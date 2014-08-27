<?php
namespace Oleg\UserdirectoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;


class SecurityController extends Controller
{

    /**
     * @Route("/login", name="employees_login")
     * @Method("GET")
     * @Template()
     */
    public function loginAction( Request $request ) {

        if(
            $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('scan-order-home') );
        }

        $request = $this->get('request_stack')->getCurrentRequest();
        $session = $request->getSession();

        // get the login error if there is one
        if( $request->attributes->has(SecurityContext::AUTHENTICATION_ERROR) ) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render(
            'OlegUserdirectoryBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
            )
        );

    }

   
//    /**
//     * @Route("/login_check", name="login_check")
//     * @Method("POST")
//     * @Template("OlegUserdirectoryBundle:ScanOrder:new_orig.html.twig")
//     */
//    public function loginCheckAction( Request $request )
//    {
//        //exit("my login check!");
//    }


//    /**
//     * @Route("/logout", name="logout")
//     * @Template()
//     */
//    public function logoutAction()
//    {
//        //echo "logout Action! <br>";
//        //exit();
//
//        $this->get('security.context')->setToken(null);
//        $this->get('request')->getSession()->invalidate();
//        return $this->redirect($this->generateUrl('login'));
//    }

}

?>
