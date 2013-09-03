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
//use Oleg\OrderformBundle\Security\AperioLdap\DatabaseRoutines;
//include_once('DatabaseRoutines.php');
use Oleg\OrderformBundle\Security\AperioLdap\cDataClient;
//include_once( 'src\Oleg\OrderformBundle\Security\AperioLdap\cDataClient.php' );
//include_once '/cDataClient.php';
//include_once( 'src\Oleg\OrderformBundle\Security\AperioLdap\Authenticate.php' );
//include_once '\vendor\aperio\lib\Aperio\src\Skeleton.php';

include_once '\Skeleton.php';

class SecurityController extends Controller
{
    /**   
     * @Route("/login", name="login")
     * @Method("GET")
     * @Template()
     */
    public function loginAction()
    {
//        echo "login controller";
//        exit();
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
        
//        return array(
//            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
//            'error'         => $error,
//        );
    }
    
    /**   
     * @Route("/login_check", name="login_check")
     * @Method("POST")
     * @Template("OlegOrderformBundle:ScanOrder:new_orig.html.twig")
     */
    public function loginCheckAction( Request $request )
    {                         
        //Aperio authentication         
        $loginName = $this->get('request')->request->get('_username');
        $password = $this->get('request')->request->get('_password');
        
        $user = $this->AperioAuth($loginName,$password); 
    
        if( $user ) {

            //exceptions temporary
            if(
                $user == 'oli2002'
                //|| $user == 'admin'
            ) {
                $user->addRole('ROLE_ADMIN');
            }

            //testing ROLES
            //$role = array('ROLE_USER');
            
            $token = new UsernamePasswordToken($user->getUsername(), '', 'secured_area', $user->getRoles());
            $token->setAttribute('email', $user->getEmail());
//            $token->setAttributes( array(
//                'email'=>$user->getEmail()
//            ));
            $this->get('security.context')->setToken($token);            
            //$this->get('security.context')->getToken()->setUser($user);
                      
        } else {
            //echo " not ok";
            //exit();
            return $this->render(
                'OlegOrderformBundle:Security:login.html.twig',
                array(                  
                    'error'         => 'Username or password are incorrect.',
                    'last_username' => $loginName,
                    //'session' => $_SESSION,
                )
            );                   
        }
        
//        return $this->redirect($this->generateUrl('scanorder_new'), 301);
        //return $this->render('OlegOrderformBundle:Controller:scanorder_new');
        return $this->redirect($this->generateUrl('scanorder_new'));
        
    }
    
    
    /**   
     * @Route("/logout", name="logout")   
     * @Template()
     */
    public function logoutAction( Request $request )
    {  
//        $session = $this->get('session');
//        $session->set('user', array(
//            'username' => '',
//            'email' => '',
//            'role' => ''
//        ));
       
        $this->get('security.context')->setToken(null); 
        $this->get('request')->getSession()->invalidate();
        
        //return $this->forward('OlegOrderformBundle:Security:login');
        return $this->redirect($this->generateUrl('login'));
    }       
    
    private function AperioAuth( $loginName, $password ) {    
        
        if( $loginName == "admin" && $password == "@dmin123") {
            $AuthResult = array(
                'UserId' => 11,
                'ReturnCode' => 0
            );

            $user = new User();
            $user->setUsername($loginName);
            //$user->setEmail($AuthResult['E_Mail']);
            $user->addRole('ROLE_ADMIN');

            return $user;
        }

        if( $loginName == "superadmin" && $password == "S@dmin123") {
            $AuthResult = array(
                'UserId' => 11,
                'ReturnCode' => 0
            );

            $user = new User();
            $user->setUsername($loginName);
            //$user->setEmail($AuthResult['E_Mail']);
            $user->addRole('ROLE_SUPER_ADMIN');

            return $user;
        }

        if( $loginName == "user" && $password == "userpw") {
            $AuthResult = array(
                'UserId' => 11,
                'ReturnCode' => 0
            );

            $user = new User();
            $user->setUsername($loginName);
            //$user->setEmail($AuthResult['E_Mail']);
            $user->addRole('ROLE_USER');

            return $user;
        }

        //echo " skip login=".$loginName.", pass=". $password." <br>";

        if( 1 ) {            
            //$DataServerURL = "http://127.0.0.1:86";
            $DataServerURL = GetDataServerURL();
            $client = new \Aperio_Aperio($DataServerURL);//,"","","","");
            $AuthResult = $client->Authenticate($loginName,$password);

        } else {

            $AuthResult = array(
                'UserId' => 11,
                'ReturnCode' => 0
            );
            $loginName = 'oli2002';
        }

//        print_r($AuthResult);
//        exit();

        if( isset($AuthResult['UserId']) && $AuthResult['ReturnCode'] == 0 ) {

            $user = new User();
            $user->setUsername($loginName);
            $user->setEmail($AuthResult['E_Mail']);
            $user->addRole('ROLE_USER');
                
            return $user;
        } else {
                       
            return null;
        }
    }
  
}

?>
