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

namespace Oleg\FellAppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Oleg\FellAppBundle\Entity\FellowshipApplication;
use Oleg\FellAppBundle\Entity\Interview;
use Oleg\FellAppBundle\Form\FellAppCreateFellowshipType;
use Oleg\FellAppBundle\Form\FellAppFellowshipApplicationType;
use Oleg\FellAppBundle\Form\FellAppManagementType;
use Oleg\FellAppBundle\Form\FellowshipSubspecialtyType;
use Oleg\FellAppBundle\Form\InterviewType;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\OrderformBundle\Helper\ErrorHelper;
use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Oleg\UserdirectoryBundle\Entity\Reference;
use Oleg\FellAppBundle\Form\FellAppFilterType;
use Oleg\FellAppBundle\Form\FellowshipApplicationType;
use Oleg\UserdirectoryBundle\Util\EmailUtil;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;



class FellAppManagement extends Controller {

    /**
     * @Route("/fellowship-types-settings", name="fellapp_fellowshiptype_settings")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Management:management.html.twig")
     */
    public function felltypeSettingsAction(Request $request) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();
        $fellappUtil = $this->container->get('fellapp_util');

        //get all fellowship types using institution: FellowshipSubspecialty objects that have $coordinators, $directors, $interviewers
        $fellowshipTypes = $fellappUtil->getFellowshipTypesByInstitution(true);

        //when the role (i.e. coordinator) is added by editing the user's profile directly, this FellowshipSubspecialty object is not updated.
        //Synchronise the FellowshipSubspecialty's $coordinators, $directors, $interviewers with the user profiles based on the specific roles
        $fellappUtil->synchroniseFellowshipSubspecialtyAndProfileRoles($fellowshipTypes);

        //manual message how to add/remove fellowship types
//        $linkUrl = $this->generateUrl(
//            "fellowshipsubspecialtys-list",
//            array(),
//            UrlGeneratorInterface::ABSOLUTE_URL
//        );
//        $manual = "Tips: Fellowship types can be added or removed by editing 'Fellowship Subspecialties' list.";
//        $manual = $manual." ".'<a href="'.$linkUrl.'" target="_blank">Please associate the department with the appropriate fellowship subspecialties.</a>';
//        $manual = $manual."<br>"."For example, to add a new fellowship type choose an appropriate subspecialty from the list and set the institution to 'Weill Cornell Medical College => Pathology and Laboratory Medicine'";
//
//        //testing
//        $manual = $manual."<br>Also, 3 roles (Coordinator, Director, Interviewer) must be created with association to an appropriate fellowship subspecialty type.";
//        $manual = $manual." Please use the button 'Add a New Fellowship Type' to add a new fellowship type when it will be ready (under construction).";
        $manual = null; //Use add new fellowship type button instead.

        return array(
            'entities' => $fellowshipTypes,
            'manual' => $manual
        );

    }

    /**
     * @Route("/add-fellowship-application-type", name="fellapp_fellowship_application_type_add")
     * @Method({"GET", "POST"})
     * @Template("OlegFellAppBundle:Management:new-fellowship-application-type.html.twig")
     */
    public function addFellowshipApplicationTypeAction(Request $request )
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //exit("addFellowshipTypeAction");
        //echo " => userId=".$id."<br>";

        $fellappUtil = $this->container->get('fellapp_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

//        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->find($roleId);
//
//        if( !$role ) {
//            throw $this->createNotFoundException('Unable to find Vacation Request Role by id='.$roleId);
//        }

        //form with 'Fellowship Subspecialties' list
        $form = $this->createForm(FellAppFellowshipApplicationType::class);

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $msg = "";

            $testing = false;
            //$testing = true;
            //exit("addFellowshipTypeAction submit");

            //$userSecUtil = $this->container->get('user_security_utility');
            //$site = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation('fellapp');

            $subspecialtyType = $form["fellowshipsubspecialtytype"]->getData();
            if( !$subspecialtyType ) {
                //Flash
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    "Please select Fellowship Subspecialty"
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
                'prefix' => 'Oleg',
                'bundleName' => 'UserdirectoryBundle',
                'className' => 'Institution'
            );

            $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
            $pathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
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

                    return $this->redirectToRoute('fellapp_fellowshiptype_settings');
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
            //name: ROLE_FELLAPP_DIRECTOR_WCM_BREASTPATHOLOGY
            //alias: Fellowship Program Interviewer WCMC Breast Pathology
            //Description: Access to specific Fellowship Application type as Interviewer
            //site: fellapp
            //Institution: WCMC
            //FellowshipSubspecialty: Breast Pathology
            //Permissions: Create a New Fellowship Application, Modify a Fellowship Application, Submit an interview evaluation

            $countInt = $fellappUtil->createOrEnableFellAppRole($subspecialtyType,"INTERVIEWER",$pathology,$testing);
            if( $countInt > 0 ) {
                $msg = $msg . " INTERVIEWER role has been created/enabled.";
                $count = $count + $countInt;
            }

            $countInt = $fellappUtil->createOrEnableFellAppRole($subspecialtyType,"COORDINATOR",$pathology,$testing);
            if( $countInt > 0 ) {
                $msg = $msg . " COORDINATOR role has been created/enabled.";
                $count = $count + $countInt;
            }

            $countInt = $fellappUtil->createOrEnableFellAppRole($subspecialtyType,"DIRECTOR",$pathology,$testing);
            if( $countInt > 0 ) {
                $msg = $msg . " DIRECTOR role has been created/enabled.";
                $count = $count + $countInt;
            }

            //////// EOF 2) create a new role (if not existed) ////////
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //exit('subspecialtyType finished');

            if( $count > 0 && !$testing ) {
                //Event Log
                $event = "New Fellowship Application Type " . $subspecialtyType->getName() . " has been created by " . $user . ". " . $msg;
                $userSecUtil = $this->container->get('user_security_utility');
                $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $event, $user, $subspecialtyType, $request, 'Fellowship Application Type Created');

                //Flash
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $event
                );
            }

            return $this->redirectToRoute('fellapp_fellowshiptype_settings');
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
     * @Route("/fellowship-application-type-remove/{fellaptypeid}", name="fellapp_fellowship_application_type_remove")
     * @Method({"GET", "POST"})
     */
    public function removeFellowshipApplicationTypeAction(Request $request, $fellaptypeid )
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo " => userId=".$id."<br>";
        //exit('removeFellowshipTypeAction id='.$fellaptypeid);

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $subspecialtyType = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->find($fellaptypeid);
        if( !$subspecialtyType ) {
            throw $this->createNotFoundException('Unable to find FellowshipSubspecialty by id='.$fellaptypeid);
        }

        //exit('not implemented');

        //1) unlink FellowshipSubspecialty and Institution
        $inst = $subspecialtyType->getInstitution();
        $subspecialtyType->setInstitution(null);
        $em->persist($subspecialtyType);
        $em->flush($subspecialtyType);

        //2) set roles to disabled
        $removedRoles = array();
        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findByFellowshipSubspecialty($subspecialtyType);
        foreach( $roles as $role ) {
            $role->setType('disabled');
            $em->persist($role);
            $em->flush($role);
            $removedRoles[] = $role->getName()."";
        }

        if( count($removedRoles) > 0 ) {
            //Event Log
            $event = "Fellowship Application Type " . $subspecialtyType->getName() . " has been removed by " . $user ." by unlinking institution ".$inst.
                " and disabling corresponding roles: ".implode(", ",$removedRoles);
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $event, $user, $subspecialtyType, $request, 'Fellowship Application Type Removed');

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );
        }

        return $this->redirectToRoute('fellapp_fellowshiptype_settings');
    }


    /**
     * @Route("/fellowship-type/show/{id}", name="fellapp_fellowshiptype_setting_show")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Management:new.html.twig")
     */
    public function showAction(Request $request, $id) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $em = $this->getDoctrine()->getManager();
        $cycle = "show";

        $felltype = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->find($id);

        if( !$felltype ) {
            throw $this->createNotFoundException('Unable to find Fellowship Subspecialty Type by id='.$id);
        }

        //when the role (i.e. coordinator) is added by editing the user's profile directly, this FellowshipSubspecialty object is not updated.
        //Synchronise the FellowshipSubspecialty's $coordinators, $directors, $interviewers with the user profiles based on the specific roles
        $fellappUtil->synchroniseFellowshipSubspecialtyAndProfileRoles( array($felltype) );

        //$routeName = $request->get('_route');
        //$args = $this->getFellappSpecialtyForm($routeName,$felltype);
        //return $this->render('OlegFellAppBundle:Management:new.html.twig', $args);

        $form = $this->getFellappSpecialtyForm($felltype,$cycle);

        return array(
            'cycle' => $cycle,
            'entity' => $felltype,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/fellowship-type/edit/{id}", name="fellapp_fellowshiptype_setting_edit")
     * @Method({"GET", "POST"})
     * @Template("OlegFellAppBundle:Management:new.html.twig")
     */
    public function editAction(Request $request, $id) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $em = $this->getDoctrine()->getManager();
        $cycle = "edit";

        $felltype = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->find($id);

        if( !$felltype ) {
            throw $this->createNotFoundException('Unable to find Fellowship Subspecialty Type by id='.$id);
        }

        $origDirectors = new ArrayCollection();
        foreach( $felltype->getDirectors() as $item ) {
            $origDirectors->add($item);
        }

        $origCoordinators = new ArrayCollection();
        foreach( $felltype->getCoordinators() as $item ) {
            $origCoordinators->add($item);
        }
        //$fellappUtil->printUsers($origCoordinators,"origCoordinators");

        $origInterviewers = new ArrayCollection();
        foreach( $felltype->getInterviewers() as $item ) {
            $origInterviewers->add($item);
        }

        //$form = $this->createForm(FellowshipSubspecialtyType::class,$felltype);
        $form = $this->getFellappSpecialtyForm($felltype,$cycle);

        $form->handleRequest($request);


        if( $form->isSubmitted() && $form->isValid() ) {
            //exit('form valid');

            //1) Remove role if a user is removed from default list (Remove,Add Order is important!)
            //compare original and final users => get removed users => for each removed user, remove the role
            $fellappUtil->processRemovedUsersByFellowshipSetting($felltype,$felltype->getDirectors(),$origDirectors,"_DIRECTOR_");
            $fellappUtil->processRemovedUsersByFellowshipSetting($felltype,$felltype->getCoordinators(),$origCoordinators,"_COORDINATOR_");
            $fellappUtil->processRemovedUsersByFellowshipSetting($felltype,$felltype->getInterviewers(),$origInterviewers,"_INTERVIEWER_");
            //exit('test');

            //2 Add role (Remove,Add Order is important!)
            $this->assignFellAppAccessRoles($felltype,$felltype->getDirectors(),"DIRECTOR");
            $this->assignFellAppAccessRoles($felltype,$felltype->getCoordinators(),"COORDINATOR");
            $this->assignFellAppAccessRoles($felltype,$felltype->getInterviewers(),"INTERVIEWER");

            $em->persist($felltype);
            $em->flush();


            return $this->redirect($this->generateUrl('fellapp_fellowshiptype_setting_show',array('id' => $felltype->getId())));
        }

        //exit('form is not valid');

        return array(
            'form' => $form->createView(),
            'entity' => $felltype,
            'cycle' => 'edit',
        );

    }

    public function getFellappSpecialtyForm($felltype, $cycle) {

//        if( $routeName == "fellapp_fellowshiptype_setting_show" ) {
//            $cycle = 'show';
//            $disabled = true;
//            $method = "GET";
//            $action = $this->generateUrl('fellapp_fellowshiptype_setting_edit', array('id' => $felltype->getId()));
//        }
//        if( $routeName == "fellapp_fellowshiptype_setting_edit" ) {
//            $cycle = 'edit';
//            $disabled = false;
//            $method = "PUT";
//            $action = $this->generateUrl('fellapp_fellowshiptype_setting_update', array('id' => $felltype->getId()));
//        }

        if( $cycle == "show" ) {
            $disabled = true;
        }

        if( $cycle == "edit" ) {
            $disabled = false;
        }

        $form = $this->createForm(
            FellowshipSubspecialtyType::class,
            $felltype,
            array(
                'disabled' => $disabled,
                //'method' => $method,
                //'action' => $action
            )
        );

//        return array(
//            'cycle' => $cycle,
//            'entity' => $felltype,
//            'form' => $form->createView()
//        );

        return $form;
    }




    //assign ROLE_FELLAPP_INTERVIEWER corresponding to application
    public function assignFellAppAccessRoles($fellowshipSubspecialty,$users,$roleSubstr) {

        //echo "assignFellAppAccessRoles: fellowshipSubspecialty=$fellowshipSubspecialty; roleSubstr=$roleSubstr <br>";
        $em = $this->getDoctrine()->getManager();

        $interviewerRoleFellType = null;
        $interviewerFellTypeRoles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findByFellowshipSubspecialty($fellowshipSubspecialty);
        foreach( $interviewerFellTypeRoles as $role ) {
            //echo "assignFellAppAccessRoles: $role ?= $roleSubstr <br>";
            if( strpos($role,$roleSubstr) !== false ) {
                $interviewerRoleFellType = $role;
                break;
            }
        }
        if( !$interviewerRoleFellType ) {
            throw new EntityNotFoundException('Unable to find role by FellowshipSubspecialty='.$fellowshipSubspecialty);
        }

        foreach( $users as $user ) {

            if( $user ) {

                //$user->addRole('ROLE_USERDIRECTORY_OBSERVER');
                //$user->addRole('ROLE_FELLAPP_USER');
                
                //add general role
                //$user->addRole('ROLE_FELLAPP_'.$roleSubstr);

                //add specific interviewer role
                $user->addRole($interviewerRoleFellType->getName());

            }
        }


    }




    /**
     * @Route("/populate-default", name="fellapp_populate_default")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Management:management.html.twig")
     */
    public function populateDefaultAction(Request $request) {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $fellappUtil = $this->container->get('fellapp_util');


        //populate default directors, coordinators, interviewers

        //BREASTPATHOLOGY
        $BREASTPATHOLOGY = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Breast Pathology");
        $users = array(
            'cwid',
            'cwid',
            'cwid',
            'cwid'
        );
        //interviewers
        $this->addUsersToFellowshipSubspecialty( $BREASTPATHOLOGY, $users, "BREASTPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToFellowshipSubspecialty( $BREASTPATHOLOGY, array('cwid'), "BREASTPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToFellowshipSubspecialty( $BREASTPATHOLOGY, array('cwid'), "BREASTPATHOLOGY", "DIRECTOR" );


        //CYTOPATHOLOGY
        $Cytopathology = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Cytopathology");
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
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, $users, "CYTOPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "CYTOPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "CYTOPATHOLOGY", "DIRECTOR" );

        //GASTROINTESTINALPATHOLOGY
        $Cytopathology = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Gastrointestinal Pathology");
        $users = array(
            'cwid',
            'cwid',
            'cwid',
            'cwid',
            'cwid'
        );
        //interviewers
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, $users, "GASTROINTESTINALPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "GASTROINTESTINALPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "GASTROINTESTINALPATHOLOGY", "DIRECTOR" );


        //GENITOURINARYPATHOLOGY
        $Cytopathology = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Genitourinary Pathology");
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
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, $users, "GENITOURINARYPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "GENITOURINARYPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "GENITOURINARYPATHOLOGY", "DIRECTOR" );

        //GYNECOLOGICPATHOLOGY
        $Cytopathology = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Gynecologic Pathology");
        $users = array(
            'cwid',
            'cwid',
            'cwid',
            'cwid'
        );
        //interviewers
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, $users, "GYNECOLOGICPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "GYNECOLOGICPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "GYNECOLOGICPATHOLOGY", "DIRECTOR" );

        //HEMATOPATHOLOGY
        $Cytopathology = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Hematopathology");
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
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, $users, "HEMATOPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "HEMATOPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "HEMATOPATHOLOGY", "DIRECTOR" );


        //MOLECULARGENETICPATHOLOGY
        $Cytopathology = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->findOneByName("Molecular Genetic Pathology");
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
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, $users, "MOLECULARGENETICPATHOLOGY", "INTERVIEWER" );
        //coordinators
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "MOLECULARGENETICPATHOLOGY", "COORDINATOR" );
        //directors
        $this->addUsersToFellowshipSubspecialty( $Cytopathology, array('cwid'), "MOLECULARGENETICPATHOLOGY", "DIRECTOR" );


        //get all fellowship types using institution
        $fellowshipTypes = $fellappUtil->getFellowshipTypesByInstitution(true);

        //exit('1');
        return array(
            'entities' => $fellowshipTypes
        );

    }

    //$roleStr = ROLE_FELLAPP_INTERVIEWER_WCM_BREASTPATHOLOGY
    public function addUsersToFellowshipSubspecialty( $fellowshipSubspecialty, $users, $roleName, $bossType ) {

        $em = $this->getDoctrine()->getManager();

        //$roleStr = ROLE_FELLAPP_INTERVIEWER_WCM_BREASTPATHOLOGY
        $roleStr = "ROLE_FELLAPP_".$bossType."_WCM_".$roleName;

        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($roleStr);
        if( !$role ) {
            exit('no role found by name='.$roleStr);
        }

        //$userObjects = array();

        foreach( $users as $userCwid ) {

            //cwidstr_@_ldap-user
            $username = $userCwid."_@_ldap-user";

            $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername($username);
            if( !$user ) {
                exit('no user found by username='.$username);
            }

            //$userObjects[] = $user;

            if( strpos($roleStr,'INTERVIEWER') !== false ) {
                if( !$fellowshipSubspecialty->isUserExistByMethodStr($user, 'getInterviewers') ) {
                    $fellowshipSubspecialty->addInterviewer($user);
                }
            }

            if( strpos($roleStr,'COORDINATOR') !== false ) {
                if( !$fellowshipSubspecialty->isUserExistByMethodStr($user, 'getCoordinators') ) {
                    $fellowshipSubspecialty->addCoordinator($user);
                }
            }

            if( strpos($roleStr,'DIRECTOR') !== false ) {
                if( !$fellowshipSubspecialty->isUserExistByMethodStr($user, 'getDirectors') ) {
                    $fellowshipSubspecialty->addDirector($user);
                }
            }

        } //foreach


        if( strpos($roleStr,'COORDINATOR') !== false ) {
            $this->assignFellAppAccessRoles($fellowshipSubspecialty,$fellowshipSubspecialty->getCoordinators(),"COORDINATOR");
        }

        if( strpos($roleStr,'DIRECTOR') !== false ) {
            $this->assignFellAppAccessRoles($fellowshipSubspecialty,$fellowshipSubspecialty->getDirectors(),"DIRECTOR");
        }

        $em->flush();
    }


    /**
     * @Route("/update-inst-user-role", name="fellapp_update_inst_user_role")
     * @Method("GET")
     */
    public function updateUserInstRoleAction(Request $request)
    {

        exit("Only one time run");

        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_ADMIN')) {
            return $this->redirect($this->generateUrl('fellapp-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $fellappUtil = $this->container->get('fellapp_util');


        //1) Change roles
        if(1) {
            $repository = $em->getRepository('OlegUserdirectoryBundle:Roles');
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
            //$users = $em->getRepository('OlegUserdirectoryBundle:User')->findUsersByRoles($roleArr);

            //$whereArr[] = 'u.roles LIKE '."'%\"" . $role . "\"%'";
            $repository = $em->getRepository('OlegUserdirectoryBundle:User');
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
