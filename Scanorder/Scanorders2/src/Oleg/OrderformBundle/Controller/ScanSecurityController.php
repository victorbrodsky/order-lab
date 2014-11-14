<?php
namespace Oleg\OrderformBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

use Oleg\OrderformBundle\Security\Util\AperioUtil;

use Oleg\UserdirectoryBundle\Controller\SecurityController;
use Oleg\UserdirectoryBundle\Util\UserUtil;

class ScanSecurityController extends SecurityController
{

    /**
     * @Route("/login", name="scan_login")
     * @Method("GET")
     * @Template()
     */
    public function loginAction( Request $request ) {
        $sitename = $this->container->getParameter('scan.sitename');

        $formArr = $this->loginPage($sitename);

        $em = $this->getDoctrine()->getManager();
        $usernametypes = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findBy( array('type' => array('default', 'user-added')), array('orderinlist' => 'ASC') );

        $formArr['usernametypes'] = $usernametypes;

        return $this->render(
            'OlegUserdirectoryBundle:Security:login.html.twig',
            $formArr
        );
    }


    /**
     * @Route("/idlelogout", name="scan_idlelogout")
     * @Route("/idlelogout/{flag}", name="scan_idlelogout-saveorder")
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        $userSecUtil = $this->get('user_security_utility');
        $sitename = $this->container->getParameter('scan.sitename');
        return $userSecUtil->idleLogout( $request, $sitename, $flag );
    }


    /**
     * @Route("/setloginvisit/", name="scan_setloginvisit")
     * @Method("GET")
     */
    public function setAjaxLoginVisit( Request $request )
    {
        //echo "height=".$request->get('display_width').", width=".$request->get('display_height')." ";
        $options = array();
        $em = $this->getDoctrine()->getManager();
        $userUtil = new UserUtil();
        $options['sitename'] = $this->container->getParameter('scan.sitename');
        $options['eventtype'] = "Login Page Visit";
        $options['event'] = "Scan Order login page visit";
        $options['serverresponse'] = "";
        $userUtil->setLoginAttempt($request,$this->get('security.context'),$em,$options);

        $response = new Response();
        $response->setContent('OK');
        return $response;
    }


    /**
     * @Route("/scan-order/no-permission", name="scan-order-nopermission")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Security:nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        return array(
            //'returnpage' => '',
        );
    }

   
//    /**
//     * @Route("/login_check", name="login_check")
//     * @Method("POST")
//     * @Template("OlegOrderformBundle:ScanOrder:new_orig.html.twig")
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



    /**
     * @Route("/admin/load-roles-from-aperio", name="load-roles-from-aperio")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Security:load-roles-from-aperio.html.twig")
     */
    public function loadRolesFromAperioAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'You do not have permission to visit this page'
            );
            return $this->redirect($this->generateUrl('scan-order-nopermission'));
        }

        $notfoundusers = array();
        $results = array();
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('OlegUserdirectoryBundle:User')->findAll();

        //echo "count=".count($users)."<br>";

        foreach( $users as $user ) {

            //************** get Aperio group roles and ROLE_SCANORDER_ORDERING_PROVIDER for this user **************//
            $aperioUtil = new AperioUtil();

            $username = $user->getCleanUsername()."";

            //echo "username=".$username. " => ";

            $userid = $aperioUtil->getUserIdByUserName($username);

            //echo "userid=".$userid." => ";

            if( !$userid || $userid == '' ) {

                $userArr = array();
                $userArr['user'] = $user;
                //$userArr['stats'] = $stats;
                $notfoundusers[] = $userArr;

            } else {

                $aperioRoles = $aperioUtil->getUserGroupMembership($userid);

                $addedRoles = $aperioUtil->setUserPathologyRolesByAperioRoles( $user, $aperioRoles );

                if( count($addedRoles) == 0 ) {

                    $stats = 'No changes';

                } else {

                    $stats = 'Added roles of ';
                    $count = 1;
                    foreach( $addedRoles as $addedRole ) {
                        //echo "role=(".$addedRole.") ";
                        $stats = $stats . $addedRole;
                        if( count($addedRoles) > $count ) {
                            $stats = $stats . ', ';
                        }
                        $count++;
                    }

                    $em->persist($user);
                    $em->flush();
                }

                //$url = $this->generateUrl('showuser', array('id' => $user->getId()) );
                //$userLink = '<a href="'.$url.'">'.$user.'</a>';
                $userArr = array();
                $userArr['user'] = $user;
                $userArr['stats'] = $stats;
                $results[] = $userArr;

            }
            //************** end of  Aperio group roles **************//

        }

        return array(
            'results' => $results,
            'notfoundusers' => $notfoundusers
        );

    }

}

?>
