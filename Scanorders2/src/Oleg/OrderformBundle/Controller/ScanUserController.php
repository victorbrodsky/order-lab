<?php

namespace Oleg\OrderformBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use Oleg\OrderformBundle\Entity\PerSiteSettings;
use Oleg\OrderformBundle\Form\PerSiteSettingsType;

use Oleg\UserdirectoryBundle\Controller\UserController;



class ScanUserController extends UserController
{

    private $sitename = 'scan';

    /**
     * @Route("/user-directory", name="scan_listusers")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:users.html.twig")
     */
    public function indexUserAction()
    {
        return $this->indexUser();
    }


    /**
     * @Route("/users/{id}", name="scan_showuser", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function showUserAction($id)
    {
        return $this->showUser($id,$this->sitename);
    }

    /**
     * @Route("/edit-user-profile/{id}", name="scan_user_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function editUserAction($id)
    {
        return $this->editUser($id,$this->sitename);
    }

    /**
     * @Route("/users/{id}", name="scan_user_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function updateUserAction(Request $request, $id)
    {
        return $this->updateUser($request,$id);
    }





//    /**
//     * @Route("/site-settings/create/user/{id}", name="scan_order_settings_create", requirements={"id" = "\d+"})
//     * @Method("POST")
//     * @Template("OlegOrderformBundle:Admin:site-settings.html.twig")
//     */
//    public function createScanSettingsAction( Request $request, $id )
//    {
//        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
//            return $this->redirect($this->generateUrl('scan-order-nopermission'));
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = new PerSiteSettings();
//
//        $form = $this->createForm(new PerSiteSettingsType(), $entity, array(
//            'action' => $this->generateUrl('_create'),
//            'method' => 'POST',
//        ));
//
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em->persist($entity);
//            $em->flush();
//            return $this->redirect($this->generateUrl('scan_order_settings_show'),array('id' => $id));
//        }
//
//        return array(
//            'entity' => $entity,
//            'form' => $form->createView(),
//            'cicle' => 'show',
//            'userid' => $id,
//        );
//    }

//    /**
//     * Creates a form to create an entity.
//     * @param $entity The entity
//     * @return \Symfony\Component\Form\Form The form
//     */
//    private function createCreateForm()
//    {
//
//        $entity = new PerSiteSettings();
//
//        $newForm = new PerSiteSettingsType();
//
//        $form = $this->createForm($newForm, $entity, array(
//            'action' => $this->generateUrl('_create'),
//            'method' => 'POST',
//        ));
//
//        $form->add('submit', 'submit', array('label' => 'Create','attr'=>array('class'=>'btn btn-warning')));
//
//        return $form;
//    }

    /**
     * @Route("/site-settings/show/user/{id}", name="scan_order_settings_show", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:site-settings.html.twig")
     */
    public function showScanSettingsAction($id)
    {
        return $this->getScanSettings($id,'show');
    }

    /**
     * @Route("/site-settings/edit/user/{id}", name="scan_order_settings_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:site-settings.html.twig")
     */
    public function editScanSettingsAction($id)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect($this->generateUrl('scan-order-nopermission'));
        }

        return $this->getScanSettings($id,'edit');

    }
    public function getScanSettings($id,$cicle) {

        $secUtil = $this->get('order_security_utility');

        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $disabled = true;

        $em = $this->getDoctrine()->getManager();

        $subjectuser = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
        if (!$subjectuser) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $entity = $secUtil->getUserPerSiteSettings($id);

        if( !$entity ) {

            $user = $this->get('security.context')->getToken()->getUser();

            $entity = new PerSiteSettings();
            $entity->setSiteName('scanorder');
            $entity->setUser($subjectuser);
            $entity->setAuthor($user);
            $entity->setType(PerSiteSettings::TYPE_RESTRICTED);
        }

        if( $cicle == 'edit' ) {
            $disabled = false;
        }

        $form = $this->createForm(new PerSiteSettingsType(), $entity, array(
            'action' => $this->generateUrl('scan_order_settings_update', array('id' => $id)),
            'method' => 'PUT',
            'disabled' => $disabled
        ));

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => $cicle,
            'userid' => $id,
            'username' => $subjectuser.""
        );
    }

    /**
     * @Route("/site-settings/edit/user/{id}", name="scan_order_settings_update", requirements={"id" = "\d+"})
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Admin:site-settings.html.twig")
     */
    public function updateScanSettingsAction(Request $request, $id)
    {

        //exit('update');

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        $secUtil = $secUtil = $this->get('order_security_utility');

        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $entity = $secUtil->getUserPerSiteSettings($id);

        if( !$entity ) {

            $subjectuser = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
            if (!$subjectuser) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }
            $entity = new PerSiteSettings();
            $entity->setSiteName('scanorder');
            $entity->setUser($subjectuser);
            $entity->setAuthor($user);
            $entity->setType(PerSiteSettings::TYPE_RESTRICTED);
        }

        $entity->setUpdateAuthor($user);
        $entity->setUpdateAuthorRoles($user->getRoles());

        $form = $this->createForm(new PerSiteSettingsType(), $entity, array(
            'action' => $this->generateUrl('scan_order_settings_update', array('id' => $id)),
            'method' => 'PUT',
        ));
        $form->add('submit', 'submit', array('label' => 'Update'));

        $form->handleRequest($request);

        if( count($entity->getPermittedInstitutionalPHIScope()) == 0 && $entity->getUser()->getUsername() != 'system' ) {
            $instLink = '<a href="'.$this->generateUrl('institutions-list').'">add the new institution name directly.</a>';
            $error = new FormError("Please add at least one permitted institution. If you do not see your institution listed, please inform the System Administrator or ".$instLink);
            $form->get('permittedInstitutionalPHIScope')->addError($error);
        }

        if( $form->isValid() ) {
            //exit('update form is valid');
            //$this->removeCollection($entity,$originalAdminTitles,'getAdministrativeTitles');
            //$this->removeCollection($entity,$originalAppTitles,'getAppointmentTitles');
            //$this->removeCollection($entity,$originalLocations,'getLocations');

            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('scan_order_settings_show', array('id' => $id)));
        }

        //exit('update form invalid');
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'cicle' => 'edit',
            'userid' => $id
        );
    }


}
