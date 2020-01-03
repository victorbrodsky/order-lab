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

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;

use App\UserdirectoryBundle\Entity\PerSiteSettings;
use App\UserdirectoryBundle\Form\PerSiteSettingsType;

use App\UserdirectoryBundle\Controller\UserController;



class ScanUserController extends UserController
{

    /**
     * @Route("/users", name="scan_listusers")
     * @Route("/users/previous", name="scan_listusers_previous")
     * @Method("GET")
     * @Template("AppOrderformBundle:Admin:users.html.twig")
     */
    public function indexUserAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) {
            return $this->redirect($this->generateUrl('scan-nopermission'));
        }

        $filter = trim( $request->get('filter') );

        $time = 'current_only';
        $routeName = $request->get('_route');
        if( $routeName == "scan_listusers_previous" ) {
            $time = 'past_only';
        }

        $params = array('filter'=>$filter,'time'=>$time);
        $res = $this->indexUser($request,$params);
        $res['filter'] = $filter;

        return $res;
    }


    /**
     * @Route("/user/{id}", name="scan_showuser", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppOrderformBundle:Profile:edit_user.html.twig")
     */
    public function showUserAction(Request $request, $id)
    {
        //$secUtil = $this->get('user_security_utility');
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USER') ) {    //!$secUtil->isCurrentUser($id) &&
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $userViewArr = $this->showUser($request,$id,$this->container->getParameter('scan.sitename'));

        if( $userViewArr === false ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        //add scan user site setting form
//        $res = $this->getScanSettingsForm($id,'show');
//        $form = $res['form'];
//        $userViewArr['form_scansettings'] = $form->createView();

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
     * @Template("AppOrderformBundle:Profile:edit_user.html.twig")
     */
    public function editUserAction(Request $request, $id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $userViewArr = $this->editUser($request,$id,$this->container->getParameter('scan.sitename'));

        if( $userViewArr === false ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

//        //add scan user site setting form
//        $res = $this->getScanSettingsForm($id,'edit');
//        $form = $res['form'];
//        $userViewArr['form_scansettings'] = $form->createView();

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
     * @Template("AppOrderformBundle:Profile:edit_user.html.twig")
     */
    public function updateUserAction(Request $request, $id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        //$userViewArr = $this->updateUser( $request, $id, $this->container->getParameter('scan.sitename') );
        return $this->updateUser( $request, $id, $this->container->getParameter('scan.sitename') );


        ///////////////////////// moved to general user controller /////////////////////////
        $scanSecUtil = $this->get('order_security_utility');
        $scanSiteSettings = $scanSecUtil->getUserPerSiteSettings($id);

        //get originals collections
        $originalInsts = new ArrayCollection();
        $originalServices = new ArrayCollection();
        $originalChiefServices = new ArrayCollection();

        if( $scanSiteSettings ) {
            foreach( $scanSiteSettings->getPermittedInstitutionalPHIScope() as $item) {
                $originalInsts->add($item);
            }

//            foreach( $scanSiteSettings->getScanOrdersServicesScope() as $item) {
//                $originalServices->add($item);
//            }

//            foreach( $scanSiteSettings->getChiefServices() as $item) {
//                $originalChiefServices->add($item);
//            }
        }


        $userViewArr = $this->updateUser( $request, $id, $this->container->getParameter('scan.sitename') );
        return $this->redirect($this->generateUrl('scan_showuser', array('id' => $id)));

        //get scan user site setting form
//        $res = $this->getScanSettingsForm($id,'edit');
//        $form = $res['form'];
//        $entity = $res['entity'];
//
//        $form->handleRequest($request);

        $form = $userViewArr['form'];
        $entity = $userViewArr['entity'];

        if( count($entity->getPerSiteSettings()->getPermittedInstitutionalPHIScope()) == 0 && $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN')) { //&& $entity->getUser()->getUsername() != 'system'
            //exit('no inst');
            $instLink = '<a href="'.$this->generateUrl('institutions-list').'">add the new institution name directly.</a>';
            $error = new FormError("Please add at least one permitted institution. If you do not see your institution listed, please inform the System Administrator or ".$instLink);
            $form->get('permittedInstitutionalPHIScope')->addError($error);
        }

        //var_dump( $form->getErrors() );

        if( 0==1 && $form->isValid() ) { //test: remove permittedInstitutionalPHIScope processing from scan and move it to user controller

            //check if insts were changed and user is not admin
            if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN') ) {
                $currentInsts = $entity->getPermittedInstitutionalPHIScope();
                if( count($currentInsts) != count($originalInsts) ) {
                    $this->setSessionForbiddenNote("Change Institutions");
                    throw new ForbiddenOverwriteException("You do not have permission to perform this operation: Change Institutions");
                    //return $this->redirect( $this->generateUrl('logout') );
                }
                foreach( $currentInsts as $inst ) {
                    if( !$originalInsts->contains($inst) ) {
                        $this->setSessionForbiddenNote("Change Institutions");
                        throw new ForbiddenOverwriteException("You do not have permission to perform this operation: Change Institutions");
                        //return $this->redirect( $this->generateUrl('logout') );
                    }
                }
            }


            ////////////////////// set Edit event log for Scan Settings //////////////////////
            $em = $this->getDoctrine()->getManager();
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets(); // do not compute changes if inside a listener

            $eventArr = array();

            //log simple fields
            $changeset = $uow->getEntityChangeSet($entity);
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset );

            //log permittedInstitutionalPHIScope
            $collDiffStr = $this->getDiffCollectionStr($originalInsts,$entity->getPermittedInstitutionalPHIScope());
            if( $collDiffStr ) {
                $eventArr[] = $collDiffStr;
            }

            //log scanOrdersServicesScope
//            $collDiffStr = $this->getDiffCollectionStr($originalServices,$entity->getScanOrdersServicesScope());
//            if( $collDiffStr ) {
//                $eventArr[] = $collDiffStr;
//            }

            //log chiefServices
//            $collDiffStr = $this->getDiffCollectionStr($originalChiefServices,$entity->getChiefServices());
//            if( $collDiffStr ) {
//                $eventArr[] = $collDiffStr;
//            }


            if( count($eventArr) > 0 ) {
                $subjectuser = $userViewArr['entity'];
                $user = $this->get('security.token_storage')->getToken()->getUser();
                $event = "User information of ".$subjectuser." has been changed by ".$user.":"."<br>";
                $event = $event . implode("<br>", $eventArr);
                $secUtil->createUserEditEvent($this->container->getParameter('scan.sitename'),$event,$user,$subjectuser,$request,'User record updated');
            }
            ////////////////////// EOF set Edit event log for Scan Settings //////////////////////


            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('scan_showuser', array('id' => $id)));
        }

        //$userViewArr['form_scansettings'] = $form->createView();

        //add research projects
        $projects = $this->getResearchProjects($id);
        $userViewArr['projects'] = $projects;

        //add educational courses
        $courses = $this->getEducationalCourses($id);
        $userViewArr['courses'] = $courses;

        return $userViewArr;
        ///////////////////////// EOF moved to general user controller /////////////////////////
    }


    /**
     * @Route("/lockunlock/change/{id}/{status}", name="scan_lockunlock_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function lockUnlockChangeAction(Request $request, $id, $status) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $this->lockUnlock($request, $id, $status, $this->container->getParameter('scan.sitename'));

        return $this->redirect($this->generateUrl($this->container->getParameter('scan.sitename').'_listusers'));
    }


    public function getDiffCollectionStr($origColl,$currColl) {
        $removeArr = array();
        foreach( $origColl as $col ) {
            if( false === $currColl->contains($col) ) {
                $removeArr[] = "<strong>"."Removed: ".$col." ".$this->getEntityId($col)."</strong>";
            }
        }
        return implode("<br>", $removeArr);
    }



////////////////////////// Below Controller methods for scan site-settings only. Currently not used. ///////////////////////////////////////

    /**
     * @Route("/site-settings/show/user/{id}", name="scan_order_settings_show", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppOrderformBundle:Admin:site-settings.html.twig")
     */
    public function showScanSettingsAction($id)
    {
        $secUtil = $this->get('order_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        return $this->getScanSettings($id,'show');
    }

    /**
     * @Route("/site-settings/edit/user/{id}", name="scan_order_settings_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppOrderformBundle:Admin:site-settings.html.twig")
     */
    public function editScanSettingsAction($id)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        return $this->getScanSettings($id,'edit');
    }
    public function getScanSettings($id,$cycle) {

        $res = $this->getScanSettingsForm($id,$cycle);
        $entity = $res['entity'];
        $form = $res['form'];
        $subjectuser = $res['subjectuser'];

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'userid' => $id,
            'username' => $subjectuser.""
        );
    }
    public function getScanSettingsForm($id,$cycle) {
        $secUtil = $this->get('order_security_utility');

        $disabled = true;

        $em = $this->getDoctrine()->getManager();

        $subjectuser = $em->getRepository('AppUserdirectoryBundle:User')->find($id);
        if (!$subjectuser) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $entity = $secUtil->getUserPerSiteSettings($id);

        $user = $this->get('security.token_storage')->getToken()->getUser();

        if( !$entity ) {

            $entity = new PerSiteSettings();
            $entity->setUser($subjectuser);
            $entity->setAuthor($user);
            //$entity->setType(PerSiteSettings::TYPE_RESTRICTED);
        }

        if( $cycle == 'edit' ) {
            $disabled = false;
        }

        $params = array('em' => $em );
        //PerSiteSettingsType($user,$this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN')
        $form = $this->createForm(PerSiteSettingsType::class, $entity, array(
            'form_custom_value_user' => $user,
            'form_custom_value_roleAdmin' => $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN'),
            'form_custom_value' => $params,
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
     * @Template("AppOrderformBundle:Admin:site-settings.html.twig")
     */
    public function updateScanSettingsAction(Request $request, $id)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $updateArr = $this->updateScanSettings($request, $id);

        return $updateArr;
    }
    public function updateScanSettings(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $secUtil = $secUtil = $this->get('order_security_utility');

        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        $entity = $secUtil->getUserPerSiteSettings($id);

        if( !$entity ) {

            $subjectuser = $em->getRepository('AppUserdirectoryBundle:User')->find($id);
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

        $params = array('em' => $em );
        //PerSiteSettingsType($user,$this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN'),$params)
        $form = $this->createForm(PerSiteSettingsType::class, $entity, array(
            'form_custom_value_user' => $user,
            'form_custom_value_roleAdmin' => $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_ADMIN'),
            'form_custom_value' => $params,
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
            'cycle' => 'edit',
            'userid' => $id
        );
    }


    public function getResearchProjects($userid) {

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('AppOrderformBundle:ProjectTitleTree');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');
        $dql->groupBy("project");
        //$dql->innerJoin("project.principals", "principal");
        //$dql->where("principal.principal = :userid");
        $dql->innerJoin("project.researches", "researches");
        $dql->innerJoin("researches.userWrappers", "userWrappers");
        $dql->innerJoin("userWrappers.user", "userWrapperUser");
        $dql->where("userWrapperUser.id = :userid");

        $query = $em->createQuery($dql)->setParameters( array( 'userid'=>$userid ) );

        $projects = $query->getResult();

        return $projects;
    }


    public function getEducationalCourses($userid) {

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('AppOrderformBundle:CourseTitleTree');
        $dql =  $repository->createQueryBuilder("course");
        $dql->select('course');
        $dql->groupBy("course");
        //$dql->innerJoin("course.directors", "director");
        //$dql->where("director.director = :userid");

        $dql->innerJoin("course.educationals", "educationals");
        $dql->innerJoin("educationals.userWrappers", "userWrappers");
        $dql->innerJoin("userWrappers.user", "userWrapperUser");
        $dql->where("userWrapperUser.id = :userid");

        $query = $em->createQuery($dql)->setParameters( array( 'userid'=>$userid ) );

        $courses = $query->getResult();

        return $courses;
    }


}
