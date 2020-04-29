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

namespace App\ResAppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use App\ResAppBundle\Entity\ResidencyApplication;
use App\ResAppBundle\Entity\Interview;
use App\ResAppBundle\Form\ResAppCreateResidencyType;
use App\ResAppBundle\Form\ResAppResidencyApplicationType;
use App\ResAppBundle\Form\ResAppManagementType;
use App\ResAppBundle\Form\ResidencySubspecialtyType;
use App\ResAppBundle\Form\InterviewType;
use App\UserdirectoryBundle\Entity\User;
use App\OrderformBundle\Helper\ErrorHelper;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Entity\Reference;
use App\ResAppBundle\Form\ResAppFilterType;
use App\ResAppBundle\Form\ResidencyApplicationType;
use App\UserdirectoryBundle\Util\EmailUtil;
use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;



class ResAppManagement extends OrderAbstractController {

    /**
     * @Route("/residency-types-settings", name="resapp_residencytype_settings", methods={"GET"})
     * @Template("AppResAppBundle/Management/management.html.twig")
     */
    public function restypeSettingsAction(Request $request) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();
        $resappUtil = $this->container->get('resapp_util');

        //get all residency types using institution: ResidencySubspecialty objects that have $coordinators, $directors, $interviewers
        $residencyTypes = $resappUtil->getResidencyTypesByInstitution(true);

        //when the role (i.e. coordinator) is added by editing the user's profile directly, this ResidencySubspecialty object is not updated.
        //Synchronise the ResidencySubspecialty's $coordinators, $directors, $interviewers with the user profiles based on the specific roles
        $resappUtil->synchroniseResidencySubspecialtyAndProfileRoles($residencyTypes);

        //manual message how to add/remove residency types
//        $linkUrl = $this->generateUrl(
//            "residencysubspecialtys-list",
//            array(),
//            UrlGeneratorInterface::ABSOLUTE_URL
//        );
//        $manual = "Tips: Residency types can be added or removed by editing 'Residency Subspecialties' list.";
//        $manual = $manual." ".'<a href="'.$linkUrl.'" target="_blank">Please associate the department with the appropriate residency subspecialties.</a>';
//        $manual = $manual."<br>"."For example, to add a new residency type choose an appropriate subspecialty from the list and set the institution to 'Weill Cornell Medical College => Pathology and Laboratory Medicine'";
//
//        //testing
//        $manual = $manual."<br>Also, 3 roles (Coordinator, Director, Interviewer) must be created with association to an appropriate residency subspecialty type.";
//        $manual = $manual." Please use the button 'Add a New Residency Type' to add a new residency type when it will be ready (under construction).";
        $manual = null; //Use add new residency type button instead.

        return array(
            'entities' => $residencyTypes,
            'manual' => $manual
        );

    }

    /**
     * @Route("/add-residency-application-type", name="resapp_residency_application_type_add", methods={"GET","POST"})
     * @Template("AppResAppBundle/Management/new-residency-application-type.html.twig")
     */
    public function addResidencyApplicationTypeAction(Request $request )
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        //exit("addResidencyTypeAction");
        //echo " => userId=".$id."<br>";

        $resappUtil = $this->container->get('resapp_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

//        $role = $em->getRepository('AppUserdirectoryBundle:Roles')->find($roleId);
//
//        if( !$role ) {
//            throw $this->createNotFoundException('Unable to find Vacation Request Role by id='.$roleId);
//        }

        //form with 'Residency Subspecialties' list
        $form = $this->createForm(ResAppResidencyApplicationType::class);

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $msg = "";

            $testing = false;
            //$testing = true;
            //exit("addResidencyTypeAction submit");

            //$userSecUtil = $this->container->get('user_security_utility');
            //$site = $em->getRepository('AppUserdirectoryBundle:SiteList')->findOneByAbbreviation('resapp');

            $subspecialtyType = $form["residencysubspecialtytype"]->getData();
            if( !$subspecialtyType ) {
                //Flash
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    "Please select Residency Subspecialty"
                );
                return array(
                    'form' => $form->createView(),
                );
            }

            //exit('subspecialtyType='.$subspecialtyType);
            $count = 0;

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //////// 1) link subspecialty with institution 'Weill Cornell Medical College => Pathology and Laboratory Medicine' ////////
            $mapper = array(
                'prefix' => 'App',
                'bundleName' => 'UserdirectoryBundle',
                'className' => 'Institution'
            );

            $wcmc = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
            $pathology = $em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $wcmc,
                $mapper
            );

            if( $pathology ) {
                if( $subspecialtyType->getInstitution() ) {
                    $msg = "Subspecialty ".$subspecialtyType->getName()." already has an associated institution ".$subspecialtyType->getInstitution().
                        ". No action performed: institution has not been changed, corresponding roles have not been created/enabled.";

                    //Flash
                    $this->get('session')->getFlashBag()->add(
                        'warning',
                        $msg
                    );

                    return $this->redirectToRoute('resapp_residencytype_settings');
                } else {
                    $subspecialtyType->setInstitution($pathology);
                    if (!$testing) {
                        $em->persist($subspecialtyType);
                        $em->flush($subspecialtyType);
                        $msg = "Subspecialty linked with an associated institution ".$subspecialtyType->getInstitution().".";
                    }
                    $count++;
                }
            }
            //////// EOF 1) link subspecialty with institution 'Weill Cornell Medical College => Pathology and Laboratory Medicine' ////////
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //////// 2) create a new role (if not existed) ////////
            //name: ROLE_RESAPP_DIRECTOR_WCM_BREASTPATHOLOGY
            //alias: Residency Program Interviewer WCMC Breast Pathology
            //Description: Access to specific Residency Application type as Interviewer
            //site: resapp
            //Institution: WCMC
            //ResidencySubspecialty: Breast Pathology
            //Permissions: Create a New Residency Application, Modify a Residency Application, Submit an interview evaluation

            $countInt = $resappUtil->createOrEnableResAppRole($subspecialtyType,"INTERVIEWER",$pathology,$testing);
            if( $countInt > 0 ) {
                $msg = $msg . " INTERVIEWER role has been created/enabled.";
                $count = $count + $countInt;
            }

            $countInt = $resappUtil->createOrEnableResAppRole($subspecialtyType,"COORDINATOR",$pathology,$testing);
            if( $countInt > 0 ) {
                $msg = $msg . " COORDINATOR role has been created/enabled.";
                $count = $count + $countInt;
            }

            $countInt = $resappUtil->createOrEnableResAppRole($subspecialtyType,"DIRECTOR",$pathology,$testing);
            if( $countInt > 0 ) {
                $msg = $msg . " DIRECTOR role has been created/enabled.";
                $count = $count + $countInt;
            }

            //////// EOF 2) create a new role (if not existed) ////////
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //exit('subspecialtyType finished');

            if( $count > 0 && !$testing ) {
                //Event Log
                $event = "New Residency Application Type " . $subspecialtyType->getName() . " has been created by " . $user . ". " . $msg;
                $userSecUtil = $this->container->get('user_security_utility');
                $userSecUtil->createUserEditEvent($this->getParameter('resapp.sitename'), $event, $user, $subspecialtyType, $request, 'Residency Application Type Created');

                //Flash
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $event
                );
            }

            return $this->redirectToRoute('resapp_residencytype_settings');
        }

        return array(
            'form' => $form->createView(),
            //'roleId' => $roleId,
            //'instid' => $instid
        );
    }

    /**
     * It should ONLY remove/strip all of THIS GROUP's roles from all users.
     * Do not delete the roles themselves and do not delete the organizational group from the Institution tree.
     *
     * @Route("/residency-application-type-remove/{resaptypeid}", name="resapp_residency_application_type_remove", methods={"GET","POST"})
     */
    public function removeResidencyApplicationTypeAction(Request $request, $resaptypeid )
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        //echo " => userId=".$id."<br>";
        //exit('removeResidencyTypeAction id='.$resaptypeid);

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $subspecialtyType = $em->getRepository('AppUserdirectoryBundle:ResidencySpecialty')->find($resaptypeid);
        if( !$subspecialtyType ) {
            throw $this->createNotFoundException('Unable to find ResidencySpecialty by id='.$resaptypeid);
        }

        //exit('not implemented');

        //1) unlink ResidencySubspecialty and Institution
        $inst = $subspecialtyType->getInstitution();
        $subspecialtyType->setInstitution(null);
        $em->persist($subspecialtyType);
        $em->flush($subspecialtyType);

        //2) set roles to disabled
        $removedRoles = array();
        $roles = $em->getRepository('AppUserdirectoryBundle:Roles')->findByResidencySubspecialty($subspecialtyType);
        foreach( $roles as $role ) {
            $role->setType('disabled');
            $em->persist($role);
            $em->flush($role);
            $removedRoles[] = $role->getName()."";
        }

        if( count($removedRoles) > 0 ) {
            //Event Log
            $event = "Residency Application Type " . $subspecialtyType->getName() . " has been removed by " . $user ." by unlinking institution ".$inst.
                " and disabling corresponding roles: ".implode(", ",$removedRoles);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->getParameter('resapp.sitename'), $event, $user, $subspecialtyType, $request, 'Residency Application Type Removed');

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );
        }

        return $this->redirectToRoute('resapp_residencytype_settings');
    }


    /**
     * @Route("/residency-type/show/{id}", name="resapp_residencytype_setting_show", methods={"GET"})
     * @Template("AppResAppBundle/Management/new.html.twig")
     */
    public function showAction(Request $request, $id) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $resappUtil = $this->container->get('resapp_util');
        $em = $this->getDoctrine()->getManager();
        $cycle = "show";

        $restype = $em->getRepository('AppUserdirectoryBundle:ResidencySpecialty')->find($id);

        if( !$restype ) {
            throw $this->createNotFoundException('Unable to find Residency Specialty Type by id='.$id);
        }

        //when the role (i.e. coordinator) is added by editing the user's profile directly, this ResidencySubspecialty object is not updated.
        //Synchronise the ResidencySubspecialty's $coordinators, $directors, $interviewers with the user profiles based on the specific roles
        $resappUtil->synchroniseResidencySubspecialtyAndProfileRoles( array($restype) );

        //$routeName = $request->get('_route');
        //$args = $this->getResappSpecialtyForm($routeName,$restype);
        //return $this->render('AppResAppBundle/Management/new.html.twig', $args);

        $form = $this->getResappSpecialtyForm($restype,$cycle);

        return array(
            'cycle' => $cycle,
            'entity' => $restype,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/residency-type/edit/{id}", name="resapp_residencytype_setting_edit", methods={"GET","POST"})
     * @Template("AppResAppBundle/Management/new.html.twig")
     */
    public function editAction(Request $request, $id) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $resappUtil = $this->container->get('resapp_util');
        $em = $this->getDoctrine()->getManager();
        $cycle = "edit";

        $restype = $em->getRepository('AppUserdirectoryBundle:ResidencySubspecialty')->find($id);

        if( !$restype ) {
            throw $this->createNotFoundException('Unable to find Residency Subspecialty Type by id='.$id);
        }

        $origDirectors = new ArrayCollection();
        foreach( $restype->getDirectors() as $item ) {
            $origDirectors->add($item);
        }

        $origCoordinators = new ArrayCollection();
        foreach( $restype->getCoordinators() as $item ) {
            $origCoordinators->add($item);
        }
        //$resappUtil->printUsers($origCoordinators,"origCoordinators");

        $origInterviewers = new ArrayCollection();
        foreach( $restype->getInterviewers() as $item ) {
            $origInterviewers->add($item);
        }

        //$form = $this->createForm(ResidencySubspecialtyType::class,$restype);
        $form = $this->getResappSpecialtyForm($restype,$cycle);

        $form->handleRequest($request);


        if( $form->isSubmitted() && $form->isValid() ) {
            //exit('form valid');

            //1) Remove role if a user is removed from default list (Remove,Add Order is important!)
            //compare original and final users => get removed users => for each removed user, remove the role
            $resappUtil->processRemovedUsersByResidencySetting($restype,$restype->getDirectors(),$origDirectors,"_DIRECTOR_");
            $resappUtil->processRemovedUsersByResidencySetting($restype,$restype->getCoordinators(),$origCoordinators,"_COORDINATOR_");
            $resappUtil->processRemovedUsersByResidencySetting($restype,$restype->getInterviewers(),$origInterviewers,"_INTERVIEWER_");
            //exit('test');

            //2 Add role (Remove,Add Order is important!)
            $this->assignResAppAccessRoles($restype,$restype->getDirectors(),"DIRECTOR");
            $this->assignResAppAccessRoles($restype,$restype->getCoordinators(),"COORDINATOR");
            $this->assignResAppAccessRoles($restype,$restype->getInterviewers(),"INTERVIEWER");

            $em->persist($restype);
            $em->flush();


            return $this->redirect($this->generateUrl('resapp_residencytype_setting_show',array('id' => $restype->getId())));
        }

        //exit('form is not valid');

        return array(
            'form' => $form->createView(),
            'entity' => $restype,
            'cycle' => 'edit',
        );

    }

    public function getResappSpecialtyForm($restype, $cycle) {

//        if( $routeName == "resapp_residencytype_setting_show" ) {
//            $cycle = 'show';
//            $disabled = true;
//            $method = "GET";
//            $action = $this->generateUrl('resapp_residencytype_setting_edit', array('id' => $restype->getId()));
//        }
//        if( $routeName == "resapp_residencytype_setting_edit" ) {
//            $cycle = 'edit';
//            $disabled = false;
//            $method = "PUT";
//            $action = $this->generateUrl('resapp_residencytype_setting_update', array('id' => $restype->getId()));
//        }

        if( $cycle == "show" ) {
            $disabled = true;
        }

        if( $cycle == "edit" ) {
            $disabled = false;
        }

        $form = $this->createForm(
            ResidencySubspecialtyType::class,
            $restype,
            array(
                'disabled' => $disabled,
                //'method' => $method,
                //'action' => $action
            )
        );

//        return array(
//            'cycle' => $cycle,
//            'entity' => $restype,
//            'form' => $form->createView()
//        );

        return $form;
    }




    //assign ROLE_RESAPP_INTERVIEWER corresponding to application
    public function assignResAppAccessRoles($residencySubspecialty,$users,$roleSubstr) {

        //echo "assignResAppAccessRoles: residencySubspecialty=$residencySubspecialty; roleSubstr=$roleSubstr <br>";
        $em = $this->getDoctrine()->getManager();

        $interviewerRoleResType = null;
        $interviewerResTypeRoles = $em->getRepository('AppUserdirectoryBundle:Roles')->findByResidencySubspecialty($residencySubspecialty);
        foreach( $interviewerResTypeRoles as $role ) {
            //echo "assignResAppAccessRoles: $role ?= $roleSubstr <br>";
            if( strpos($role,$roleSubstr) !== false ) {
                $interviewerRoleResType = $role;
                break;
            }
        }
        if( !$interviewerRoleResType ) {
            throw new EntityNotFoundException('Unable to find role by ResidencySubspecialty='.$residencySubspecialty);
        }

        foreach( $users as $user ) {

            if( $user ) {

                //$user->addRole('ROLE_USERDIRECTORY_OBSERVER');
                //$user->addRole('ROLE_RESAPP_USER');
                
                //add general role
                //$user->addRole('ROLE_RESAPP_'.$roleSubstr);

                //add specific interviewer role
                $user->addRole($interviewerRoleResType->getName());

            }
        }


    }




    /**
     * @Route("/populate-default", name="resapp_populate_default", methods={"GET"})
     * @Template("AppResAppBundle/Management/management.html.twig")
     */
    public function populateDefaultAction(Request $request) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $resappUtil = $this->container->get('resapp_util');


        //populate default directors, coordinators, interviewers

        //BREASTPATHOLOGY
        $BREASTPATHOLOGY = $em->getRepository('AppUserdirectoryBundle:ResidencySubspecialty')->findOneByName("Breast Pathology");
        $users = array(
            'cwid',
            'cwid',
            'cwid',
            'cwid'
        );
        //interviewers
        $this->addUsersToResidencySubspecialty( $BREASTPATHOLOGY, $users, "BREASTPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToResidencySubspecialty( $BREASTPATHOLOGY, array('cwid'), "BREASTPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToResidencySubspecialty( $BREASTPATHOLOGY, array('cwid'), "BREASTPATHOLOGY", "DIRECTOR" );


        //CYTOPATHOLOGY
        $Cytopathology = $em->getRepository('AppUserdirectoryBundle:ResidencySubspecialty')->findOneByName("Cytopathology");
        $users = array(
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid'
        );
        //interviewers
        $this->addUsersToResidencySubspecialty( $Cytopathology, $users, "CYTOPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "CYTOPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "CYTOPATHOLOGY", "DIRECTOR" );

        //GASTROINTESTINALPATHOLOGY
        $Cytopathology = $em->getRepository('AppUserdirectoryBundle:ResidencySubspecialty')->findOneByName("Gastrointestinal Pathology");
        $users = array(
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid'
        );
        //interviewers
        $this->addUsersToResidencySubspecialty( $Cytopathology, $users, "GASTROINTESTINALPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "GASTROINTESTINALPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "GASTROINTESTINALPATHOLOGY", "DIRECTOR" );


        //GENITOURINARYPATHOLOGY
        $Cytopathology = $em->getRepository('AppUserdirectoryBundle:ResidencySubspecialty')->findOneByName("Genitourinary Pathology");
        $users = array(
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid'
        );
        //interviewers
        $this->addUsersToResidencySubspecialty( $Cytopathology, $users, "GENITOURINARYPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "GENITOURINARYPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "GENITOURINARYPATHOLOGY", "DIRECTOR" );

        //GYNECOLOGICPATHOLOGY
        $Cytopathology = $em->getRepository('AppUserdirectoryBundle:ResidencySubspecialty')->findOneByName("Gynecologic Pathology");
        $users = array(
            'cwid',
            'cwid',
            'cwid',
            'cwid'
        );
        //interviewers
        $this->addUsersToResidencySubspecialty( $Cytopathology, $users, "GYNECOLOGICPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "GYNECOLOGICPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "GYNECOLOGICPATHOLOGY", "DIRECTOR" );

        //HEMATOPATHOLOGY
        $Cytopathology = $em->getRepository('AppUserdirectoryBundle:ResidencySubspecialty')->findOneByName("Hematopathology");
        $users = array(
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid'
        );
        //interviewers
        $this->addUsersToResidencySubspecialty( $Cytopathology, $users, "HEMATOPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "HEMATOPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "HEMATOPATHOLOGY", "DIRECTOR" );


        //MOLECULARGENETICPATHOLOGY
        $Cytopathology = $em->getRepository('AppUserdirectoryBundle:ResidencySubspecialty')->findOneByName("Molecular Genetic Pathology");
        $users = array(
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid'
        );
        //interviewers
        $this->addUsersToResidencySubspecialty( $Cytopathology, $users, "MOLECULARGENETICPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "MOLECULARGENETICPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToResidencySubspecialty( $Cytopathology, array('cwid'), "MOLECULARGENETICPATHOLOGY", "DIRECTOR" );


        //get all residency types using institution
        $residencyTypes = $resappUtil->getResidencyTypesByInstitution(true);

        //exit('1');
        return array(
            'entities' => $residencyTypes
        );

    }

    //$roleStr = ROLE_RESAPP_INTERVIEWER_WCM_BREASTPATHOLOGY
    public function addUsersToResidencySubspecialty( $residencySubspecialty, $users, $roleName, $bossType ) {

        $em = $this->getDoctrine()->getManager();

        //$roleStr = ROLE_RESAPP_INTERVIEWER_WCM_BREASTPATHOLOGY
        $roleStr = "ROLE_RESAPP_".$bossType."_WCM_".$roleName;

        $role = $em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($roleStr);
        if( !$role ) {
            exit('no role found by name='.$roleStr);
        }

        //$userObjects = array();

        foreach( $users as $userCwid ) {

            //cwidstr_@_ldap-user
            $username = $userCwid."_@_ldap-user";

            $user = $em->getRepository('AppUserdirectoryBundle:User')->findOneByUsername($username);
            if( !$user ) {
                exit('no user found by username='.$username);
            }

            //$userObjects[] = $user;

            if( strpos($roleStr,'INTERVIEWER') !== false ) {
                if( !$residencySubspecialty->isUserExistByMethodStr($user, 'getInterviewers') ) {
                    $residencySubspecialty->addInterviewer($user);
                }
            }

            if( strpos($roleStr,'COORDINATOR') !== false ) {
                if( !$residencySubspecialty->isUserExistByMethodStr($user, 'getCoordinators') ) {
                    $residencySubspecialty->addCoordinator($user);
                }
            }

            if( strpos($roleStr,'DIRECTOR') !== false ) {
                if( !$residencySubspecialty->isUserExistByMethodStr($user, 'getDirectors') ) {
                    $residencySubspecialty->addDirector($user);
                }
            }

        } //foreach


        if( strpos($roleStr,'COORDINATOR') !== false ) {
            $this->assignResAppAccessRoles($residencySubspecialty,$residencySubspecialty->getCoordinators(),"COORDINATOR");
        }

        if( strpos($roleStr,'DIRECTOR') !== false ) {
            $this->assignResAppAccessRoles($residencySubspecialty,$residencySubspecialty->getDirectors(),"DIRECTOR");
        }

        $em->flush();
    }


    /**
     * @Route("/update-inst-user-role", name="resapp_update_inst_user_role", methods={"GET"})
     */
    public function updateUserInstRoleAction(Request $request)
    {

        exit("Only one time run");

        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_ADMIN')) {
            return $this->redirect($this->generateUrl('resapp-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $resappUtil = $this->container->get('resapp_util');


        //1) Change roles
        if(1) {
            $repository = $em->getRepository('AppUserdirectoryBundle:Roles');
            $dql = $repository->createQueryBuilder("list");
            $dql->select('list');
            $dql->where("list.name LIKE :name");

            $parameters = array(
                "name" => '%' . 'WCMC' . '%'
            );

            $query = $dql->getQuery();
            $query->setParameters($parameters);

            $roles = $query->getResult();

            echo "roles=" . count($roles) . "<br>";

            foreach ($roles as $role) {
                $name = $role->getName();
                $alias = $role->getAlias();
                echo "role=" . $name . "; alias=" . $alias . "<br>";
                $name = str_replace("_WCMC_", "_WCM_", $name);
                $alias = str_replace("WCMC", "WCM", $alias);
                $role->setName($name);
                $role->setAlias($alias);
                $em->flush($role);
            }

            //exit("Exit Roles");
        }

        if(1) {
            //$roleArr = array('WCMC');
            //$users = $em->getRepository('AppUserdirectoryBundle:User')->findUsersByRoles($roleArr);

            //$whereArr[] = 'u.roles LIKE '."'%\"" . $role . "\"%'";
            $repository = $em->getRepository('AppUserdirectoryBundle:User');
            $dql = $repository->createQueryBuilder("user");
            $dql->select('user');
            $dql->where("user.roles LIKE :name");

            $parameters = array(
                "name" => '%' . 'WCMC' . '%'
            );

            $query = $dql->getQuery();
            $query->setParameters($parameters);

            $users = $query->getResult();

            echo "users=" . count($users) . "<br>";

            foreach ($users as $user) {

                foreach ($user->getRoles() as $role) {
                    if (strpos($role, '_WCMC_') !== false) {
                        echo $user.": role=" . $role . "<br>";
                        $roleNew = str_replace("_WCMC_", "_WCM_", $role);
                        echo $user.": roleNew=" . $roleNew . "<br>";
                        $user->removeRole($role);
                        $user->addRole($roleNew);
                        $em->flush($user);
                    }
                }

            }

            //exit("users=" . count($users));
        }

        exit("EOF updateUserInstRoleAction");
    }

}
