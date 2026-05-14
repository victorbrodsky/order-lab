<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\CtpRegulatorytBundle\Controller;

use App\UserdirectoryBundle\Controller\UserController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class RegulatorytUserController extends UserController
{
    #[Route(path: '/user/{id}', name: 'ctpregulatoryt_showuser', methods: ['GET'], requirements: ['id' => '\d+'], options: ['expose' => true])]
    #[Template('AppUserdirectoryBundle/Profile/show_user.html.twig')]
    public function showUserOptimizedAction( Request $request, $id ) {
        return $this->showUserOptimized($request, $id, $this->getParameter('regulatoryt.sitename'));
    }

    #[Route(path: '/edit-user-profile/{id}', name: 'ctpregulatoryt_user_edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/Profile/edit_user.html.twig')]
    public function editUserAction(Request $request, $id)
    {
        $secUtil = $this->container->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('regulatoryt-nopermission') );
        }
        $editUser = $this->editUser($request,$id, $this->getParameter('regulatoryt.sitename'));
        if( $editUser === false ) {
            return $this->redirect( $this->generateUrl('regulatoryt-nopermission') );
        }
        return $editUser;
    }

    #[Route(path: '/edit-user-profile/{id}', name: 'ctpregulatoryt_user_update', methods: ['PUT'])]
    #[Template('AppUserdirectoryBundle/Profile/edit_user.html.twig')]
    public function updateUserAction(Request $request, $id)
    {
        $secUtil = $this->container->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('regulatoryt-nopermission') );
        }
        return $this->updateUser( $request, $id, $this->getParameter('regulatoryt.sitename') );
    }
}
