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

namespace App\CrnBundle\Controller;

use App\UserdirectoryBundle\Controller\UserController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CrnUserController extends UserController
{

    /**
     * Optimized show user
     * @Route("/user/{id}", name="crn_showuser", methods={"GET"}, requirements={"id" = "\d+"}, options={"expose"=true})
     * @Template("AppUserdirectoryBundle/Profile/show_user.html.twig")
     */
    public function showUserOptimizedAction( Request $request, $id ) {
        return $this->showUserOptimized($request, $id, $this->getParameter('crn.sitename'));
    }


    /**
     * @Route("/edit-user-profile/{id}", name="crn_user_edit", methods={"GET"}, requirements={"id" = "\d+"})
     * @Template("AppUserdirectoryBundle/Profile/edit_user.html.twig")
     */
    public function editUserAction(Request $request, $id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        $editUser = $this->editUser($request,$id, $this->getParameter('crn.sitename'));

        if( $editUser === false ) {
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        return $editUser;
    }

    /**
     * @Route("/edit-user-profile/{id}", name="crn_user_update", methods={"PUT"})
     * @Template("AppUserdirectoryBundle/Profile/edit_user.html.twig")
     */
    public function updateUserAction(Request $request, $id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        return $this->updateUser( $request, $id, $this->getParameter('crn.sitename') );
    }

}
