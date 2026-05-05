<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\SporeBundle\Controller;

use App\UserdirectoryBundle\Controller\UserController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class SporeUserController extends UserController
{
    #[Route(path: '/user/{id}', name: 'spore_showuser', methods: ['GET'], requirements: ['id' => '\d+'], options: ['expose' => true])]
    #[Template('AppUserdirectoryBundle/Profile/show_user.html.twig')]
    public function showUserOptimizedAction( Request $request, $id ) {
        return $this->showUserOptimized($request, $id, $this->getParameter('spore.sitename'));
    }

    #[Route(path: '/edit-user-profile/{id}', name: 'spore_user_edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/Profile/edit_user.html.twig')]
    public function editUserAction(Request $request, $id)
    {
        $secUtil = $this->container->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('spore-nopermission') );
        }
        $editUser = $this->editUser($request,$id, $this->getParameter('spore.sitename'));
        if( $editUser === false ) {
            return $this->redirect( $this->generateUrl('spore-nopermission') );
        }
        return $editUser;
    }

    #[Route(path: '/edit-user-profile/{id}', name: 'spore_user_update', methods: ['PUT'])]
    #[Template('AppUserdirectoryBundle/Profile/edit_user.html.twig')]
    public function updateUserAction(Request $request, $id)
    {
        $secUtil = $this->container->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('spore-nopermission') );
        }
        return $this->updateUser( $request, $id, $this->getParameter('spore.sitename') );
    }
}
