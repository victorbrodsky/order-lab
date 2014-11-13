<?php

namespace Oleg\UserdirectoryBundle\Controller;



use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Oleg\UserdirectoryBundle\Form\LocationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;

use Doctrine\Common\Collections\ArrayCollection;


use Oleg\UserdirectoryBundle\Entity\Location;



class LocationController extends Controller
{


    /**
     * @Route("/locations/new", name="employees_new_location")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Location:location.html.twig")
     */
    public function newLocationAction(Request $request)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $location = new Location();

        $isAdmin = $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR');

        //em, admin, currentUser, read_only, cicle
        $params = array('read_only'=>false,'admin'=>$isAdmin,'currentUser'=>false,'cicle'=>'create_location','em'=>$em);
        $form = $this->createForm(new LocationType($params,$location), $location, array('disabled' => false));

//        $form = $this->createForm(new LocationType('create',$location,$this->get('security.context'),$em), array(
//            'action' => $this->generateUrl($this->container->getParameter('employees.sitename').'_location_update', array('id' => $location->getId())),
//            'method' => 'PUT',
//        ));

        return array(
            'entity' => $location,
            'form' => $form->createView(),
            'cicle' => 'create_location'    //show_user
        );
    }


    /**
     * @Route("/users/new", name="employees_create_user")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:Profile:register.html.twig")
     */
    public function createUserAction( Request $request )
    {
        return $this->createUser($request);
    }
    public function createUser($request) {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();



        return array(
//            'entity' => $user,
//            'form' => $form->createView(),
//            'cicle' => 'create_user',
//            'user_id' => '',
//            'sitename' => $this->container->getParameter('employees.sitename')
        );
    }





}
