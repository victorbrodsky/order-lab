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

namespace Oleg\TranslationalResearchBundle\Controller;


use Oleg\UserdirectoryBundle\Controller\SecurityController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

use Oleg\UserdirectoryBundle\Util\UserUtil;

class TranslationalResearchSecurityController extends SecurityController
{

    /**
     * @Route("/login", name="translationalresearch_login")
     *
     * @Method("GET")
     * @Template()
     */
    public function loginAction( Request $request ) {
        //exit('translationalresearch: loginAction');
        return parent::loginAction($request);
    }


    /**
     * @Route("/setloginvisit/", name="translationalresearch_setloginvisit")
     *
     * @Method("GET")
     */
    public function setAjaxLoginVisit( Request $request )
    {
        //exit('translationalresearch: setAjaxLoginVisit');
        return parent::setAjaxLoginVisit($request);
    }


    /**
     * @Route("/no-permission", name="translationalresearch-nopermission")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Security:nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        $empty = $request->get('empty');
        $additionalMessage = $request->get('additionalMessage');

        return array(
            'sitename' => $this->container->getParameter('translationalresearch.sitename'),
            'empty' => $empty,
            'additionalMessage' => $additionalMessage
        );
    }


    /**
     * @Route("/idlelogout", name="translationalresearch_idlelogout")
     * @Route("/idlelogout/{flag}", name="translationalresearch_idlelogout-saveorder")
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        //exit('translationalresearch: idlelogoutAction');
        return parent::idlelogoutAction($request,$flag);
    }


}

?>
