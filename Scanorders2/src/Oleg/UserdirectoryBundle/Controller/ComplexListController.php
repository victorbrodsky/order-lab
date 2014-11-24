<?php

namespace Oleg\UserdirectoryBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;

use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Oleg\UserdirectoryBundle\Form\LocationType;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\UserdirectoryBundle\Entity\Location;


class ComplexListController extends Controller
{


    /**
     * @Route("/admin/list/locations/", name="employees_locations_pathaction_list")
     * @Route("/admin/list/buildings/", name="employees_buildings_pathaction_list")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ComplexList:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->getList($request);
    }
    public function getList($request) {

        $routeName = $request->get('_route');

        $mapper = $this->classListMapper($routeName);

        $repository = $this->getDoctrine()->getRepository($mapper['bundleName'].':'.$mapper['className']);
        $dql =  $repository->createQueryBuilder("ent");
        $dql->select('ent');
        $dql->groupBy('ent');

        if( $mapper['pathname'] == 'locations' ) {
            $dql->leftJoin("ent.user", "user");
            $dql->addGroupBy('user');
        }
        if( $mapper['pathname'] == 'buildings' ) {
            $dql->leftJoin("ent.institution", "institution");
            $dql->addGroupBy('institution');
        }

        $dql->leftJoin("ent.creator", "creator");
        $dql->leftJoin("ent.updatedby", "updatedby");

        $dql->addGroupBy('creator.username');
        $dql->addGroupBy('updatedby.username');

        $dql->leftJoin("ent.synonyms", "synonyms");
        $dql->addGroupBy('synonyms.name');
        $dql->leftJoin("ent.original", "original");
        $dql->addGroupBy('original.name');

        $dql->leftJoin("ent.geoLocation", "geoLocation");
        $dql->addGroupBy('geoLocation');


        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
        $postData = $request->query->all();
        if( isset($postData['sort']) ) {
            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
        }

        //echo "dql=".$dql."<br>";

        $em = $this->getDoctrine()->getManager();
        $limit = 30;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        return array(
            'entities' => $entities,
            'singleName' => $mapper['singleName'],
            'displayName' => "List of ".$mapper['displayName'],
            'pathname' => $mapper['pathname']
        );
    }



    /**
     * @Route("/admin/list/locations/show/{id}", name="employees_locations_pathaction_show_standalone", requirements={"id" = "\d+"})
     * @Route("/admin/list/locations/edit/{id}", name="employees_locations_pathaction_edit_standalone", requirements={"id" = "\d+"})
     *
     * @Route("/admin/list/buildings/show/{id}", name="employees_buildings_pathaction_show_standalone", requirements={"id" = "\d+"})
     * @Route("/admin/list/buildings/edit/{id}", name="employees_buildings_pathaction_edit_standalone", requirements={"id" = "\d+"})
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ComplexList:list.html.twig")
     */
    public function showListAction(Request $request, $id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $routeName = $request->get('_route');

        $mapper = $this->classListMapper($routeName);

        //get cicle
        $pieces = explode("_pathaction_", $routeName);
        $cicle = $pieces[1];

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);

        $form = $this->createCreateForm($entity,$cicle,$mapper);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => $cicle,
            'id' => $entity->getId(),
            'singleName' => $mapper['singleName'],
            'displayName' => "List of ".$mapper['displayName'],
            'pathname' => $mapper['pathname']
        );
    }


    /**
     * @Route("/admin/list/locations/new", name="employees_locations_pathaction_new_standalone")
     * @Route("/admin/list/buildings/new", name="employees_buildings_pathaction_new_standalone")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ComplexList:list.html.twig")
     */
    public function newListAction(Request $request)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $routeName = $request->get('_route');

        $mapper = $this->classListMapper($routeName);

        $entityClass = $mapper['fullClassName'];

        $cicle = 'new_standalone';

        $user = $this->get('security.context')->getToken()->getUser();

        $entity = new $entityClass($user);

        $form = $this->createCreateForm($entity,$cicle,$mapper);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => $cicle,
            'id' => '',
            'singleName' => $mapper['singleName'],
            'displayName' => "List of ".$mapper['displayName'],
            'pathname' => $mapper['pathname']
        );
    }


    /**
     * @Route("/admin/list/locations/new", name="employees_locations_pathaction_new_post_standalone")
     * @Route("/admin/list/buildings/new", name="employees_buildings_pathaction_new_post_standalone")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:ComplexList:list.html.twig")
     */
    public function createListAction( Request $request )
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $cicle = 'new_post_standalone';

        $user = $this->get('security.context')->getToken()->getUser();

        $routeName = $request->get('_route');

        $mapper = $this->classListMapper($routeName);

        $entityClass = $mapper['fullClassName'];

        $entity = new $entityClass($user);

        $form = $this->createCreateForm($entity,$cicle,$mapper);

        $form->handleRequest($request);

//        echo "loc errors:<br>";
//        print_r($form->getErrors());
//        echo "<br>loc string errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";
//        echo "creator=".$entity->getCreator()."<br>";
//        exit();

        if( $form->isValid() ) {

            //echo "pathname=".$mapper['pathname']."<br>";
            if( $mapper['pathname'] == 'locations' ) {
                //set parents for institution tree for Administrative and Academical Titles
                $userUtil = new UserUtil();
                $em = $this->getDoctrine()->getManager();
                $sc = $this->get('security.context');
                $userUtil->processInstTree($entity,$em,$sc);

                //set Reviewed by Administration
                $entity->setStatus($entity::STATUS_VERIFIED);

                //set Location Privacy
                $locPrivacy = $em->getRepository('OlegUserdirectoryBundle:LocationPrivacyList')->findOneByName("Anyone can see this contact information");
                $entity->setPrivacy($locPrivacy);
            }

            if( $mapper['pathname'] == 'buildings' ) {
                $userUtil = new UserUtil();
                $em = $this->getDoctrine()->getManager();
                $sc = $this->get('security.context');
                $userUtil->setUpdateInfo($entity,$em,$sc);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('employees_'.$mapper['pathname'].'_pathaction_show_standalone', array('id' => $entity->getId())));
        }

        //echo "error loc <br>";

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => $cicle,
            'id' => '',
            'singleName' => $mapper['singleName'],
            'displayName' => "List of ".$mapper['displayName'],
            'pathname' => $mapper['pathname']
        );
    }


    /**
     * @Route("/admin/list/locations/update/{id}", name="employees_locations_pathaction_edit_put_standalone",requirements={"id" = "\d+"})
     * @Route("/admin/list/buildings/update/{id}", name="employees_buildings_pathaction_edit_put_standalone",requirements={"id" = "\d+"})
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:ComplexList:list.html.twig")
     */
    public function updateListAction( Request $request, $id )
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $routeName = $request->get('_route');
        $mapper = $this->classListMapper($routeName);

        $cicle = 'edit_put_standalone';

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);

        //update author can be set to any user, not a current user
        $entity->setUpdateAuthor(null);

        $form = $this->createCreateForm($entity,$cicle,$mapper);

        $form->handleRequest($request);


//        echo "loc errors:<br>";
//        print_r($form->getErrors());
//        echo "<br>loc string errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";
        //exit();

        if( $form->isValid() ) {

            if( $mapper['pathname'] == 'locations' ) {
                //set parents for institution tree for Administrative and Academical Titles
                $userUtil = new UserUtil();
                $em = $this->getDoctrine()->getManager();
                $sc = $this->get('security.context');
                $userUtil->processInstTree($entity,$em,$sc);
            }

            if( $mapper['pathname'] == 'buildings' ) {
                $userUtil = new UserUtil();
                $em = $this->getDoctrine()->getManager();
                $sc = $this->get('security.context');
                $userUtil->setUpdateInfo($entity,$em,$sc);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('employees_'.$mapper['pathname'].'_pathaction_show_standalone', array('id' => $entity->getId())));
        }

        echo "error loc <br>";

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => $cicle,
            'id' => '',
            'singleName' => $mapper['singleName'],
            'displayName' => "List of ".$mapper['displayName'],
            'pathname' => $mapper['pathname']
        );
    }



    public function createCreateForm($entity,$cicle,$mapper) {

        $em = $this->getDoctrine()->getManager();

        $disabled = false;
        $method = null;

        //echo "cicle=".$cicle."<br>";

        $path = $this->container->getParameter('employees.sitename').'_'.$mapper['pathname'].'_pathaction_'.$cicle;

        //create new page
        if( $cicle == "new_standalone" ) {
            //on a new page show a form with method=POST and action=create_post_standalone
            $method = "POST";
            $path = $this->container->getParameter('employees.sitename').'_'.$mapper['pathname'].'_pathaction_'.'new_post_standalone';
            $action = $this->generateUrl($path);
        }

        //create: submit action
        if( $cicle == "new_post_standalone" ) {
            $method = "POST";
            $action = $this->generateUrl($path);
        }

        //show existing page
        if( $cicle == "show_standalone" ) {
            $method = "GET";
            $action = $this->generateUrl($path, array('id' => $entity->getId()));
            $disabled = true;
        }

        //edit existing page
        if( $cicle == "edit_standalone" ) {
            //on a edit page show a form with method=PUT and action=edit_put_standalone
            $method = "PUT";
            $path = $this->container->getParameter('employees.sitename').'_'.$mapper['pathname'].'_pathaction_'.'edit_put_standalone';
            $action = $this->generateUrl($path, array('id' => $entity->getId()));
        }

        //edit: submit action
        if( $cicle == "edit_put_standalone" ) {
            $method = "PUT";
            $action = $this->generateUrl($path, array('id' => $entity->getId()));
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $isAdmin = $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR');

        $params = array('read_only'=>false,'admin'=>$isAdmin,'currentUser'=>false,'cicle'=>$cicle,'em'=>$em,'user'=>$user);

        $form = $this->createForm(new $mapper['fullFormType']($params,$entity), $entity, array(
            'disabled' => $disabled,
            'action' => $action,
            'method' => $method,
        ));


        return $form;
    }



    public function classListMapper( $route ) {

        //$route = employees_locations_pathaction_list
        $pieces = explode("_pathaction_", $route);
        $name = str_replace("employees_","",$pieces[0]);
        $cicle = $pieces[1];

        switch( $name ) {

            case "locations":
                $className = "Location";
                $displayName = "Locations";
                $singleName = "Location";
                $formType = "LocationType";
                break;
            case "buildings":
                $className = "BuildingList";
                $displayName = "Buildings";
                $singleName = "Building";
                $formType = "BuildingType";
                break;
            default:
                $className = null;
                $displayName = null;
        }

        //echo "className=".$className.", displayName=".$displayName."<br>";

        $res = array();
        $res['className'] = $className;
        $res['fullFormType'] = "Oleg\\UserdirectoryBundle\\Form\\".$formType;
        $res['fullClassName'] = "Oleg\\UserdirectoryBundle\\Entity\\".$className;
        $res['bundleName'] = "OlegUserdirectoryBundle";
        $res['displayName'] = $displayName;
        $res['singleName'] = $singleName;
        $res['pathname'] = $name;

        return $res;
    }

}
