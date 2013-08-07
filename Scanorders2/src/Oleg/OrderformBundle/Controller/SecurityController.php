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
include_once('DatabaseRoutines.php');
use Oleg\OrderformBundle\Security\AperioLdap\cDataClient;
//include_once( 'src\Oleg\OrderformBundle\Security\AperioLdap\cDataClient.php' );
//include_once '/cDataClient.php';
//include_once( 'src\Oleg\OrderformBundle\Security\AperioLdap\Authenticate.php' );

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
     * @Template("OlegOrderformBundle:ScanOrder:new.html.twig")
     */
    public function loginCheckAction( Request $request )
    {                   
        //echo "login check";
        //exit();
        //
        //Aperio authentication         
        $loginName = $this->get('request')->request->get('_username');
        $password = $this->get('request')->request->get('_password');
        
        $user = $this->AperioAuth($loginName,$password); 
              
//        //$session = new Session();
//        //$session->start();
//        $session = $request->getSession();
//        $myuser = $session->get('FullName');
//        echo "my user=".$myuser."<br>";
//        exit();
         
        //var_dump($_SESSION);
        //echo "<br>";
        //exit();               
        
//        $session = $this->get('session');
//        $session->set('user', array(
//            'username' => '',
//            'email' => '',
//            'role' => ''
//        ));
        
        
        //$user = $this->get('security.context')->getToken()->getUser();
        
        if( $user ) {
            
            $token = new UsernamePasswordToken($user->getUsername(), null, 'secured_area', array('ROLE_USER'));
            $this->get('security.context')->setToken($token);     
            //$this->get('security.context')->getToken()->setUser($user);
                      
//            $session->set('user', array(
//                'username' => $user->getUsername(),
//                'email' => $user->getEmail(),
//                'role' => $user->getRoles()    
//            ));
            //echo " ok user=".$user->getUsername();
            //exit();
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
        
        $DataServerURL = GetDataServerURL();
        //echo "url=".$DataServerURL;
        //exit();
        
        //it works@
        $client = new \Aperio_Aperio($DataServerURL);//,"","","","");
        $AuthResult = $client->Authenticate($loginName,$password);
//        echo "aut <br>";
//        print_r($res);
//        exit();
              
//        $AuthResult = ADB_Authenticate($loginName, $password);            
        
        //$UsersTable = GetTableObj('Users');
	//$User = $UsersTable->GetOneRecord($AuthResult['UserId']);
        
        
        // Get Symfony to interface with this existing session
        //$session = new Session(new PhpBridgeSessionStorage());

        // symfony will now interface with the existing PHP session
        //$session->start();      
        
//        echo "global sess<br>";
        //$session = $this->container->get('session');
//        var_dump($session);
//        var_dump($_SESSION);
//        echo "<br>";
//        exit();
                   
        if( isset($AuthResult['UserId']) && $AuthResult['ReturnCode'] == 0 ) {
            
            $user = new User();
            $user->setUsername($loginName);
            $user->setEmail($AuthResult['E_Mail']);
            $user->addRole('ROLE_USER');
                
            return $user;
        } else {
            
            //unset($_POST);
            //echo "Login failed";
            
            return null;
        }
    }
    
    
    //------------------------------------------------------------------
    // ADB_Authenticate - Verify that the login/password exist in the db
    //	if a match is found, then return the authentication token and 
    //	store the user status in the 
    //	
    //------------------------------------------------------------------
    function ADB_Authenticate($UserName, $Password)
    {

//            echo "!!!!!!!!!!!!!! ADB_Authenticate session=<br>";
//            print_r($_SESSION);
//            echo "<br>";
//            exit();

        $DataServerURL = 'a.wcmc-ad.net';

            $client = GetSOAPSecurityClient();

            // since this is usually the first call to dataserver put it in a try/catch block
            // because it might fail if dataserver is not accessible.  If an exception occurs
            // we can display a decent error message.
            try
            {       
//                    $params = array('UserName'=>$UserName, 'PassWord'=>$Password);
//                    $res = $client->__soapCall(	'Logon',																	//SOAP Method Name
//                                                    array(
//                                                        'soap_version'=>SOAP_1_2,
//                                                        $params                                                   
//                                                        ));    
                    $res = $client->__soapCall(	'Logon',																	//SOAP Method Name
                                                    array(
                                                        'soap_version'=>SOAP_1_2,
                                                        new SoapParam($UserName, 'UserName'), 		//Parameters
                                                        new SoapParam($Password, 'PassWord'),                                                   
                                                        ));            
            }
            catch (Exception $e) 
            {		

                    trigger_error("Spectrum SOAP Error:  Unable to communicate with DataServer at $DataServerURL", E_USER_ERROR);
            }


            if (is_array($res) && ($res['LogonResult']->ASResult == 0))
            {
                    $ReturnArray['ReturnCode'] = 0;
                    $ReturnArray['Token'] = $res['Token'];
                    $ReturnArray['UserId'] = $res['UserData']->UserId;
                    $ReturnArray['UserMustChangePassword'] = $res['UserData']->UserMustChangePassword;
            }
            elseif (is_object($res))
            {
                    $ReturnArray['ReturnCode'] = $res->ASResult;
                    $ReturnArray['ReturnText'] = $res->ASMessage;
            }
            else
            {
                    $ReturnArray = array('ReturnCode'=>'-1','ReturnText'=>'');
            }

            echo "!!!!!!!!!!!!!! ADB_Authenticate session=<br>";
            print_r($res);
            echo "<br>";
            exit();

            return $ReturnArray;

    }
    
}

?>
