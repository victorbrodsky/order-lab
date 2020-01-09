<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\OrderformBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

use App\OrderformBundle\Security\Util\PacsvendorUtil;

use App\UserdirectoryBundle\Controller\SecurityController;

class ScanSecurityController extends SecurityController
{

    /**
     * @Route("/login", name="scan_login")
     * @Method("GET")
     * @Template()
     */
    public function loginAction( Request $request ) {
        return parent::loginAction($request);
    }


    /**
     * @Route("/idle-log-out", name="scan_idlelogout")
     * @Route("/idle-log-out/{flag}", name="scan_idlelogout-saveorder")
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        return parent::idlelogoutAction($request,$flag);

//        $userSecUtil = $this->get('user_security_utility');
//        $sitename = $this->container->getParameter('scan.sitename');
//        return $userSecUtil->idleLogout( $request, $sitename, $flag );
    }


    /**
     * @Route("/setloginvisit/", name="scan_setloginvisit")
     * @Method("GET")
     */
    public function setAjaxLoginVisit( Request $request )
    {
        return parent::setAjaxLoginVisit($request);

//        //echo "height=".$request->get('display_width').", width=".$request->get('display_height')." ";
//        $options = array();
//        $em = $this->getDoctrine()->getManager();
//        $userUtil = new UserUtil();
//        $options['sitename'] = $this->container->getParameter('scan.sitename');
//        $options['eventtype'] = "Login Page Visit";
//        $options['event'] = "Scan Order login page visit";
//        $options['serverresponse'] = "";
//        $userUtil->setLoginAttempt($request,$this->get('security.context'),$em,$options);
//
//        $response = new Response();
//        $response->setContent('OK');
//        return $response;
    }


    /**
     * @Route("/scan-order/no-permission", name="scan-nopermission")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/Security/nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        $empty = $request->get('empty');

        return array(
            'sitename' => $this->container->getParameter('scan.sitename'),
            'empty' => $empty
        );
    }

   
//    /**
//     * @Route("/login_check", name="login_check")
//     * @Method("POST")
//     * @Template("AppOrderformBundle/ScanOrder/new_orig.html.twig")
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
     * @Route("/admin/load-roles-from-pacsvendor", name="load-roles-from-pacsvendor")
     * @Method("GET")
     * @Template("AppOrderformBundle/Security/load-roles-from-pacsvendor.html.twig")
     */
    public function loadRolesFromPacsvendorAction()
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'You do not have permission to visit this page'
            );
            return $this->redirect($this->generateUrl('scan-nopermission'));
        }

        $notfoundusers = array();
        $results = array();
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('AppUserdirectoryBundle:User')->findAll();

        //echo "count=".count($users)."<br>";

        foreach( $users as $user ) {

            //************** get pacsvendor group roles and ROLE_SCANORDER_ORDERING_PROVIDER for this user **************//
            $pacsvendorUtil = new PacsvendorUtil();

            $username = $user->getCleanUsername()."";

            //echo "username=".$username. " => ";

            $userid = $pacsvendorUtil->getUserIdByUserName($username);

            //echo "userid=".$userid." => ";

            if( !$userid || $userid == '' ) {

                $userArr = array();
                $userArr['user'] = $user;
                //$userArr['stats'] = $stats;
                $notfoundusers[] = $userArr;

            } else {

                $pacsvendorRoles = $pacsvendorUtil->getUserGroupMembership($userid);

                $addedRoles = $pacsvendorUtil->setUserPathologyRolesByPacsvendorRoles( $user, $pacsvendorRoles );

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
            //************** end of pacsvendor group roles **************//

        }

        return array(
            'results' => $results,
            'notfoundusers' => $notfoundusers
        );

    }

}

?>
