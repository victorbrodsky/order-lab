<?php


namespace Oleg\DeidentifierBundle\Controller;

use Oleg\UserdirectoryBundle\Controller\UserController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DeidentifierUserController extends UserController
{

    /**
     * Optimized show user
     * @Route("/user/{id}", name="deidentifier_showuser", requirements={"id" = "\d+"}, options={"expose"=true})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:show_user.html.twig")
     */
    public function showUserOptimizedAction( Request $request, $id ) {
        return $this->showUserOptimized($request, $id, $this->container->getParameter('deidentifier.sitename'));
    }


    /**
     * @Route("/edit-user-profile/{id}", name="deidentifier_user_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function editUserAction($id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $editUser = $this->editUser($id, $this->container->getParameter('deidentifier.sitename'));

        if( $editUser === false ) {
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        return $editUser;
    }

    /**
     * @Route("/edit-user-profile/{id}", name="deidentifier_user_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function updateUserAction(Request $request, $id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        return $this->updateUser( $request, $id, $this->container->getParameter('deidentifier.sitename') );
    }

}
