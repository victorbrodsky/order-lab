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

    /**
     * @Route("/user-directory", name="scan_listusers")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:users.html.twig")
     */
    public function indexUserAction()
    {
        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect($this->generateUrl('scan-order-nopermission'));
        }

        return $this->indexUser();
    }


    /**
     * @Route("/users/{id}", name="scan_showuser", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function showUserAction($id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_USER') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }


        $userViewArr = $this->showUser($id,$this->container->getParameter('scan.sitename'));

        //add scan user site setting form
        $res = $this->getScanSettingsForm($id,'show');
        $form = $res['form'];
        $userViewArr['form_scansettings'] = $form->createView();

        //add research projects
        $projects = $this->getResearchProjects($id);
        $userViewArr['projects'] = $projects;

        //add educational courses
        $courses = $this->getEducationalCourses($id);
        $userViewArr['courses'] = $courses;

        return $userViewArr;
    }

    /**
     * @Route("/edit-user-profile/{id}", name="scan_user_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function editUserAction($id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $userViewArr = $this->editUser($id,$this->container->getParameter('scan.sitename'));

        //add scan user site setting form
        $res = $this->getScanSettingsForm($id,'edit');
        $form = $res['form'];
        $userViewArr['form_scansettings'] = $form->createView();

        //add research projects
        $projects = $this->getResearchProjects($id);
        $userViewArr['projects'] = $projects;

        //add educational courses
        $courses = $this->getEducationalCourses($id);
        $userViewArr['courses'] = $courses;

        return $userViewArr;
    }

    /**
     * @Route("/edit-user-profile/{id}", name="scan_user_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function updateUserAction(Request $request, $id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $userViewArr = $this->updateUser( $request, $id, $this->container->getParameter('scan.sitename') );

        //get scan user site setting form
        $res = $this->getScanSettingsForm($id,'edit');
        $form = $res['form'];
        $entity = $res['entity'];

        $originalInsts = $entity->getpermittedInstitutionalPHIScope();

        $form->handleRequest($request);

        if( count($entity->getPermittedInstitutionalPHIScope()) == 0 && $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN')) { //&& $entity->getUser()->getUsername() != 'system'
            //exit('no inst');
            $instLink = '<a href="'.$this->generateUrl('institutions-list').'">add the new institution name directly.</a>';
            $error = new FormError("Please add at least one permitted institution. If you do not see your institution listed, please inform the System Administrator or ".$instLink);
            $form->get('permittedInstitutionalPHIScope')->addError($error);
        }

        //var_dump( $form->getErrors() );

        if( $form->isValid() ) {

            //check if insts were changed and user is not admin
            if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
                $currentInsts = $entity->getpermittedInstitutionalPHIScope();
                if( count($currentInsts) != count($originalInsts) ) {
                    return $this->redirect( $this->generateUrl('logout') );
                }
                foreach( $currentInsts as $inst ) {
                    if( !$originalInsts->contains($inst) ) {
                        return $this->redirect( $this->generateUrl('logout') );
                    }
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('scan_showuser', array('id' => $id)));
        }

        $userViewArr['form_scansettings'] = $form->createView();

        //add research projects
        $projects = $this->getResearchProjects($id);
        $userViewArr['projects'] = $projects;

        //add educational courses
        $courses = $this->getEducationalCourses($id);
        $userViewArr['courses'] = $courses;

        return $userViewArr;
    }


    /**
     * @Route("/lockunlock/change/{id}/{status}", name="scan_lockunlock_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function lockUnlockChangeAction($id, $status) {

        if (false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $this->lockUnlock($id, $status);

        return $this->redirect($this->generateUrl($this->container->getParameter('scan.sitename').'_listusers'));
    }






////////////////////////// Below Controller methods for scan site-settings only. Currently not used. ///////////////////////////////////////

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
        $secUtil = $this->get('order_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

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
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        return $this->getScanSettings($id,'edit');
    }
    public function getScanSettings($id,$cicle) {

        $res = $this->getScanSettingsForm($id,$cicle);
        $entity = $res['entity'];
        $form = $res['form'];
        $subjectuser = $res['subjectuser'];

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => $cicle,
            'userid' => $id,
            'username' => $subjectuser.""
        );
    }
    public function getScanSettingsForm($id,$cicle) {
        $secUtil = $this->get('order_security_utility');

        $disabled = true;

        $em = $this->getDoctrine()->getManager();

        $subjectuser = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
        if (!$subjectuser) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $entity = $secUtil->getUserPerSiteSettings($id);

        $user = $this->get('security.context')->getToken()->getUser();

        if( !$entity ) {

            $entity = new PerSiteSettings();
            $entity->setUser($subjectuser);
            $entity->setAuthor($user);
            //$entity->setType(PerSiteSettings::TYPE_RESTRICTED);
        }

        if( $cicle == 'edit' ) {
            $disabled = false;
        }

        $form = $this->createForm(new PerSiteSettingsType($user,$this->get('security.context')->isGranted('ROLE_ADMIN')), $entity, array(
            'action' => $this->generateUrl('scan_order_settings_update', array('id' => $id)),
            'method' => 'PUT',
            'disabled' => $disabled
        ));

        $res = array();
        $res['entity'] = $entity;
        $res['form'] = $form;
        $res['subjectuser'] = $subjectuser;

        return $res;
    }

    /**
     * @Route("/site-settings/edit/user/{id}", name="scan_order_settings_update", requirements={"id" = "\d+"})
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Admin:site-settings.html.twig")
     */
    public function updateScanSettingsAction(Request $request, $id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $updateArr = $this->updateScanSettings($request, $id);

        return $updateArr;
    }
    public function updateScanSettings(Request $request, $id) {
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
            $entity->setUser($subjectuser);
            $entity->setAuthor($user);
            //$entity->setType(PerSiteSettings::TYPE_RESTRICTED);
        }

        $entity->setUpdateAuthor($user);
        $entity->setUpdateAuthorRoles($user->getRoles());

        $form = $this->createForm(new PerSiteSettingsType($user,$this->get('security.context')->isGranted('ROLE_ADMIN')), $entity, array(
            'action' => $this->generateUrl('scan_order_settings_update', array('id' => $id)),
            'method' => 'PUT',
        ));
        //$form->add('submit', 'submit', array('label' => 'Update'));

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


    public function getResearchProjects($userid) {

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:ProjectTitleList');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');
        $dql->groupBy("project");
        $dql->innerJoin("project.principals", "principal");
        $dql->where("principal.principal = :userid");

        $query = $em->createQuery($dql)->setParameters( array( 'userid'=>$userid ) );

        $projects = $query->getResult();

        return $projects;
    }


    public function getEducationalCourses($userid) {

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:CourseTitleList');
        $dql =  $repository->createQueryBuilder("course");
        $dql->select('course');
        $dql->groupBy("course");
        $dql->innerJoin("course.directors", "director");
        $dql->where("director.director = :userid");

        $query = $em->createQuery($dql)->setParameters( array( 'userid'=>$userid ) );

        $courses = $query->getResult();

        return $courses;
    }


}
