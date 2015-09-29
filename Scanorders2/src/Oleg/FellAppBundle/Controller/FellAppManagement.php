<?php

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

            if( $user && !$user->hasRole('ROLE_FELLAPP_'.$roleSubstr) ) {

                //add general role
                $user->addRole('ROLE_FELLAPP_'.$roleSubstr);

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

}
