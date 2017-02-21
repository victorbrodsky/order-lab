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
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;



class FellAppManagement extends Controller {

    /**
     * @Route("/fellowship-types-settings", name="fellapp_fellowshiptype_settings")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Management:management.html.twig")
     */
    public function felltypeSettingsAction(Request $request) {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.context')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $fellappUtil = $this->container->get('fellapp_util');

        //get all fellowship types using institution
        $fellowshipTypes = $fellappUtil->getFellowshipTypesByInstitution(true);


        return array(
            'entities' => $fellowshipTypes
        );

    }



    /**
     * @Route("/fellowship-type/{id}", name="fellapp_fellowshiptype_setting_show")
     * @Route("/fellowship-type/edit/{id}", name="fellapp_fellowshiptype_setting_edit")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Management:new.html.twig")
     */
    public function showAction(Request $request, $id) {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.context')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $felltype = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->find($id);

        if( !$felltype ) {
            throw $this->createNotFoundException('Unable to find Fellowship Subspecialty Type by id='.$id);
        }

        $routeName = $request->get('_route');

        $args = $this->getShowParameters($routeName,$felltype);

        //TODO: list all other users with related fellowship roles for this fellowship type

        return $this->render('OlegFellAppBundle:Management:new.html.twig', $args);

    }



    /**
     * @Route("/fellowship-type/update/{id}", name="fellapp_fellowshiptype_setting_update")
     * @Method("PUT")
     * @Template("OlegFellAppBundle:Management:new.html.twig")
     */
    public function updateAction(Request $request, $id) {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->get('security.context')->isGranted('ROLE_FELLAPP_DIRECTOR') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $felltype = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty')->find($id);

        if( !$felltype ) {
            throw $this->createNotFoundException('Unable to find Fellowship Subspecialty Type by id='.$id);
        }

        //TODO: delete role if a user is removed from default list?

        $form = $this->createForm( new FellowshipSubspecialtyType(),$felltype);

        $form->handleRequest($request);


        if( !$form->isSubmitted() ) {
            //echo "form is not submitted<br>";
            $form->submit($request);
        }

        if( $form->isValid() ) {

            //exit('form valid');

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





    public function getShowParameters($routeName, $felltype) {

        if( $routeName == "fellapp_fellowshiptype_setting_show" ) {
            $cycle = 'show';
            $disabled = true;
            $method = "GET";
            $action = $this->generateUrl('fellapp_fellowshiptype_setting_edit', array('id' => $felltype->getId()));
        }

        if( $routeName == "fellapp_fellowshiptype_setting_edit" ) {
            $cycle = 'edit';
            $disabled = false;
            $method = "PUT";
            $action = $this->generateUrl('fellapp_fellowshiptype_setting_update', array('id' => $felltype->getId()));
        }


        $form = $this->createForm(
            new FellowshipSubspecialtyType(),
            $felltype,
            array(
                'disabled' => $disabled,
                'method' => $method,
                'action' => $action
            )
        );

        return array(
            'cycle' => $cycle,
            'entity' => $felltype,
            'form' => $form->createView()
        );
    }




    //assign ROLE_FELLAPP_INTERVIEWER corresponding to application
    public function assignFellAppAccessRoles($fellowshipSubspecialty,$users,$roleSubstr) {

        $em = $this->getDoctrine()->getManager();

        $interviewerRoleFellType = null;
        $interviewerFellTypeRoles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findByFellowshipSubspecialty($fellowshipSubspecialty);
        foreach( $interviewerFellTypeRoles as $role ) {
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

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
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

    //$roleStr = ROLE_FELLAPP_INTERVIEWER_WCMC_BREASTPATHOLOGY
    public function addUsersToFellowshipSubspecialty( $fellowshipSubspecialty, $users, $roleName, $bossType ) {

        $em = $this->getDoctrine()->getManager();

        //$roleStr = ROLE_FELLAPP_INTERVIEWER_WCMC_BREASTPATHOLOGY
        $roleStr = "ROLE_FELLAPP_".$bossType."_WCMC_".$roleName;

        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($roleStr);
        if( !$role ) {
            exit('no role found by name='.$roleStr);
        }

        //$userObjects = array();

        foreach( $users as $userCwid ) {

            //cwidstr_@_wcmc-cwid
            $username = $userCwid."_@_wcmc-cwid";

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

}
