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

namespace App\FellAppBundle\Controller;


use App\UserdirectoryBundle\Controller\SecurityController;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

use App\UserdirectoryBundle\Util\UserUtil;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class FellAppSecurityController extends SecurityController
{

    /**
     * @Route("/login", name="fellapp_login")
     * @Template()
     */
    public function loginAction( Request $request, AuthenticationUtils $authenticationUtils ) {
        return parent::loginAction($request,$authenticationUtils);
    }


    /**
     * @Route("/setloginvisit/", name="fellapp_setloginvisit", methods={"GET"})
     */
    public function setAjaxLoginVisit( Request $request )
    {
        return parent::setAjaxLoginVisit($request);
    }


    /**
     * @Route("/no-permission", name="fellapp-nopermission", methods={"GET"})
     * @Template("AppUserdirectoryBundle/Security/nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        //$message = null;
        $empty = $request->get('empty');

        return array(
            'sitename' => $this->getParameter('fellapp.sitename'),
            'empty' => $empty
        );
    }



    /**
     * @Route("/idle-log-out", name="fellapp_idlelogout")
     * @Route("/idle-log-out/{flag}", name="fellapp_idlelogout-saveorder")
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        return parent::idlelogoutAction($request,$flag);
//        $userSecUtil = $this->get('user_security_utility');
//        $sitename = $this->getParameter('fellapp.sitename');
//        return $userSecUtil->idleLogout( $request, $sitename, $flag );
    }





//    /**
//     * @Route("/setloginvisit/", name="fellapp_setloginvisit", methods={"GET"})
//     */
//    public function setAjaxLoginVisit( Request $request )
//    {
//        //echo "height=".$request->get('display_width').", width=".$request->get('display_height')." ";
//        $options = array();
//        $em = $this->getDoctrine()->getManager();
//        $userUtil = new UserUtil();
//        $options['sitename'] = $this->getParameter('scan.sitename');
//        $options['eventtype'] = "Login Page Visit";
//        $options['event'] = "Scan Order login page visit";
//        $options['serverresponse'] = "";
//        $userUtil->setLoginAttempt($request,$this->get('security.context'),$em,$options);
//
//        $response = new Response();
//        $response->setContent('OK');
//        return $response;
//    }



}

?>
