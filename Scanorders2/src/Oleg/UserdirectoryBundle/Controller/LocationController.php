<?php

namespace Oleg\UserdirectoryBundle\Controller;



use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Oleg\UserdirectoryBundle\Form\LocationType;
use Oleg\UserdirectoryBundle\Util\UserUtil;
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


//    /**
//     * @Route("/locations/list/", name="employees_list_location")
//     * @Method("GET")
//     * @Template("OlegUserdirectoryBundle:Location:index.html.twig")
//     */
//    public function indexLocationAction(Request $request)
//    {
//
//        $em = $this->getDoctrine()->getManager();
//
//        $locations = $em->getRepository('OlegUserdirectoryBundle:Location')->findAll();
//
//        return array(
//            'locations' => $locations
//        );
//    }

//    /**
//     * Search Location
//     *
//     * @Route("/locations/", name="locations_home")
//     * @Template("OlegUserdirectoryBundle:Location:locations-content.html.twig")
//     */
//    public function indexAction( Request $request ) {
//
//        $search = trim( $request->get('search') );
//
//        //echo "search=".$search."<br>";
//
//        $locations = null;
//        $pagination = null;
//        $roles = null;
//
//        if( $search != "" ) {
//            $userUtil = new UserUtil();
//            $locations = $userUtil->indexLocation($search, $request, $this->container, $this->getDoctrine());
//        }
//
//        return array(
//            'locations' => $locations,
//            'search' => $search
//        );
//    }
//
//    //render the search results for all search objects
//    public function getSearchLocationAction( $search, $page ) {
//
//        $locations = $this->searchLocation($search, $page);
//
//        return $this->render('OlegUserdirectoryBundle:Location:locations-content.html.twig', array(
//            'locations'=>$locations,
//            'search'=>$search
//        ));
//
//    }
//
//    public function searchLocation( $search, $page ) {
//
//        $request = $this->container->get('request');
//
//        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:Location');
//        $dql =  $repository->createQueryBuilder("location");
//        $dql->select('location');
//
//        $dql->leftJoin("location.user", "user");
//        $dql->leftJoin("location.service", "service");
//        $dql->leftJoin("location.building", "building");
//
//
//        $postData = $request->query->all();
//        print_r($postData);
//
//        if( !isset($postData['sort']) ) {
//            $dql->orderBy("location.name","ASC");
//        }
//
//        //search
//        $criteriastr = "";
//
//
//        //name
//        $criteriastr .= "location.name LIKE '%".$search."%' OR ";
//
//        //IC
//        $criteriastr .= "location.ic LIKE '%".$search."%' OR ";
//
//        //phone
//        $criteriastr .= "location.phone LIKE '%".$search."%' OR ";
//
//        //pager
//        $criteriastr .= "location.pager LIKE '%".$search."%' OR ";
//
//        //room
//        $criteriastr .= "location.room LIKE '%".$search."%' OR ";
//
//        //service
//        $criteriastr .= "service.name LIKE '%".$search."%' OR ";
//
//        //user.displayName
//        $criteriastr .= "user.displayName LIKE '%".$search."%' OR ";
//
//        //service
//        $criteriastr .= "building.name LIKE '%".$search."%'";
//
//
//        $dql->where($criteriastr);
//
//        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
//        if( isset($postData['sort']) ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }
//
//        //echo "Location dql=".$dql."<br>";
//
//        $em = $this->getDoctrine()->getManager();
//        $query = $em->createQuery($dql);    //->setParameter('now', date("Y-m-d", time()));
//
//        $limitFlag = true;
//        if( $limitFlag ) {
//            $limit = 10;
//            $paginator  = $this->get('knp_paginator');
//            $pagination = $paginator->paginate(
//                $query,
//                $page,  //$request->query->get('page', 1), /*page number*/
//                $limit/*limit per page*/
//            );
//        } else {
//            $pagination = $query->getResult();
//        }
//
//        //echo "locations count=".count($pagination)."<br>";
//
//        return $pagination;
//
//    }







    /**
     * @Route("/locations/show/{id}", name="employees_show_location", requirements={"id" = "\d+"})
     * @Route("/locations/edit/{id}", name="employees_edit_location", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Location:location.html.twig")
     */
    public function showLocationAction(Request $request, $id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $routeName = $request->get('_route');

        $cicle = 'show_location';

        if( $routeName == "employees_show_location" ) {
            $cicle = 'show_location';
        }

        if( $routeName == "employees_edit_location" ) {
            $cicle = 'edit_location';
        }

        $em = $this->getDoctrine()->getManager();

        $location = $em->getRepository('OlegUserdirectoryBundle:Location')->find($id);

        $form = $this->createCreateForm($location,$cicle);

        return array(
            'entity' => $location,
            'form' => $form->createView(),
            'cicle' => $cicle,    //show_user
            'id' => $location->getId()
        );
    }


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

        $cicle = 'create_location';

        $user = $this->get('security.context')->getToken()->getUser();

        $location = new Location($user);

        $form = $this->createCreateForm($location,$cicle);

        return array(
            'entity' => $location,
            'form' => $form->createView(),
            'cicle' => $cicle,    //show_user
            'id' => ''
        );
    }


    /**
     * @Route("/locations/new", name="employees_create_location")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:Location:location.html.twig")
     */
    public function createLocationAction( Request $request )
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $cicle = 'create_location';

        $user = $this->get('security.context')->getToken()->getUser();

        $location = new Location($user);

        $form = $this->createCreateForm($location,$cicle);

        $form->handleRequest($request);

        //print_r($form->getErrors());

        if ($form->isValid()) {

            //set parents for institution tree for Administrative and Academical Titles
            $userUtil = new UserUtil();
            $em = $this->getDoctrine()->getManager();
            $sc = $this->get('security.context');
            $userUtil->processInstTree($location,$em,$sc);

            $em = $this->getDoctrine()->getManager();
            $em->persist($location);
            $em->flush();

            return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_show_location', array('id' => $location->getId())));
        }

        //echo "error loc <br>";

        return array(
            'entity' => $location,
            'form' => $form->createView(),
            'cicle' => $cicle,
            'id' => ''
        );
    }


    /**
     * @Route("/locations/update/{id}", name="employees_update_location",requirements={"id" = "\d+"})
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:Location:location.html.twig")
     */
    public function updateLocationAction( Request $request, $id )
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $cicle = 'edit_location';

        $em = $this->getDoctrine()->getManager();

        $location = $em->getRepository('OlegUserdirectoryBundle:Location')->find($id);

        $form = $this->createCreateForm($location,$cicle);

        $form->handleRequest($request);

        print_r($form->getErrors());

        if ($form->isValid()) {

            //set parents for institution tree for Administrative and Academical Titles
            $userUtil = new UserUtil();
            $em = $this->getDoctrine()->getManager();
            $sc = $this->get('security.context');
            $userUtil->processInstTree($location,$em,$sc);

            $em = $this->getDoctrine()->getManager();
            $em->persist($location);
            $em->flush();

            return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_show_location', array('id' => $location->getId())));
        }

        echo "error loc <br>";

        return array(
            'entity' => $location,
            'form' => $form->createView(),
            'cicle' => $cicle,
            'id' => ''
        );
    }



    public function createCreateForm($entity,$cicle) {

        $em = $this->getDoctrine()->getManager();

        $disabled = false;
        $method = null;

        //echo "cicle=".$cicle."<br>";

        if( $cicle == "create_location" ) {
            $method = "POST";   //create
            $path = $this->container->getParameter('employees.sitename').'_create_location';
            $action = $this->generateUrl($path);
        }

        if( $cicle == "show_location" ) {
            $method = "GET";    //list
            $path = $this->container->getParameter('employees.sitename').'_show_location';
            $action = $this->generateUrl($path, array('id' => $entity->getId()));
            $disabled = true;
        }

        if( $cicle == "edit_location" ) {
            $method = "PUT";    //update
            $path = $this->container->getParameter('employees.sitename').'_update_location';
            $action = $this->generateUrl($path, array('id' => $entity->getId()));
        }

        $isAdmin = $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR');

        $params = array('read_only'=>false,'admin'=>$isAdmin,'currentUser'=>false,'cicle'=>$cicle,'em'=>$em);

        $form = $this->createForm(new LocationType($params,$entity), $entity, array(
            'disabled' => $disabled,
            'action' => $action,
            'method' => $method,
        ));

//        if( $cicle == "create_location" ) {
//            $form->add('submit', 'submit', array('label' => 'Create','attr'=>array('class'=>'btn btn-success')));
//        }
//
//        if( $cicle == "show_location" ) {
//            $form->add('submit', 'submit', array('label' => 'Edit','attr'=>array('class'=>'btn btn-success')));
//        }
//
//        if( $cicle == "edit_location" ) {
//            $form->add('submit', 'submit', array('label' => 'Update','attr'=>array('class'=>'btn btn-warning')));
//        }

        return $form;
    }

}
