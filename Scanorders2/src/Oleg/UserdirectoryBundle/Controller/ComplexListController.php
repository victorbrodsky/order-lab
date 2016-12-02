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
     * @Route("/list/locations/", name="employees_locations_pathaction_list")
     * @Route("/list/buildings/", name="employees_buildings_pathaction_list")
     * @Route("/list/research-labs/", name="employees_researchlabs_pathaction_list")
     * @Route("/list/grants/", name="employees_grants_pathaction_list")
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ComplexList:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->getList($request,$this->container->getParameter('employees.sitename'));
    }
    public function getList($request,$sitename) {

        $routeName = $request->get('_route');

        $mapper = $this->classListMapper($routeName,$request);

        $repository = $this->getDoctrine()->getRepository($mapper['bundleName'].':'.$mapper['className']);
        $dql =  $repository->createQueryBuilder("ent");
        $dql->select('ent');
        $dql->groupBy('ent');

        if( $mapper['pathname'] == 'locations' ) {
            $dql->leftJoin("ent.geoLocation", "geoLocation");
            $dql->addGroupBy('geoLocation');
            $dql->leftJoin("ent.user", "user");
            $dql->addGroupBy('user');
        }

        if( $mapper['pathname'] == 'buildings' ) {
            $dql->leftJoin("ent.geoLocation", "geoLocation");
            $dql->addGroupBy('geoLocation');
            $dql->leftJoin("ent.institutions", "institutions");
            $dql->addGroupBy('institutions');
        }

        if( $mapper['pathname'] == 'researchlabs' ) {
            $dql->leftJoin("ent.user", "user");
            $dql->addGroupBy('user');
            $dql->leftJoin("ent.institution", "institution");
            $dql->addGroupBy('institution');
        }

        if( $mapper['pathname'] == 'grants' ) {
            $dql->leftJoin("ent.user", "user");
            $dql->addGroupBy('user');
            $dql->leftJoin("ent.sourceOrganization", "sourceOrganization");
            $dql->addGroupBy('sourceOrganization');
        }

        if( $mapper['pathname'] == 'labtests' ) {
            $dql->leftJoin("ent.labTestType", "labTestType");
            $dql->addGroupBy('labTestType');
        }

        $dql->leftJoin("ent.creator", "creator");
        $dql->leftJoin("ent.updatedby", "updatedby");

        $dql->addGroupBy('creator.username');
        $dql->addGroupBy('updatedby.username');

        $dql->leftJoin("ent.synonyms", "synonyms");
        $dql->addGroupBy('synonyms.name');
        $dql->leftJoin("ent.original", "original");
        $dql->addGroupBy('original.name');

        //$dql->leftJoin("ent.geoLocation", "geoLocation");
        //$dql->addGroupBy('geoLocation');


        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
//        $postData = $request->query->all();
//        if( isset($postData['sort']) ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }

        //echo "dql=".$dql."<br>";

        $em = $this->getDoctrine()->getManager();
        $limit = 50;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit /*limit per page*/
            ,array('defaultSortFieldName' => 'ent.orderinlist', 'defaultSortDirection' => 'asc')
        );

        return array(
            'entities' => $entities,
            'singleName' => $mapper['singleName'],
            'displayName' => "List of ".$mapper['displayName'],
            'pathname' => $mapper['pathname'],
            'sitename' => $sitename
        );
    }



    /**
     * @Route("/location/show/{id}", name="employees_locations_pathaction_show_standalone", requirements={"id" = "\d+"})
     * @Route("/location/edit/{id}", name="employees_locations_pathaction_edit_standalone", requirements={"id" = "\d+"})
     *
     * @Route("/buildings/show/{id}", name="employees_buildings_pathaction_show_standalone", requirements={"id" = "\d+"})
     * @Route("/admin/buildings/edit/{id}", name="employees_buildings_pathaction_edit_standalone", requirements={"id" = "\d+"})
     *
     * @Route("/research-labs/show/{id}", name="employees_researchlabs_pathaction_show_standalone", requirements={"id" = "\d+"})
     * @Route("/admin/research-labs/edit/{id}", name="employees_researchlabs_pathaction_edit_standalone", requirements={"id" = "\d+"})
     *
     * @Route("/grants/show/{id}", name="employees_grants_pathaction_show_standalone", requirements={"id" = "\d+"})
     * @Route("/admin/grants/edit/{id}", name="employees_grants_pathaction_edit_standalone", requirements={"id" = "\d+"})
     *
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ComplexList:new.html.twig")
     */
    public function showListAction(Request $request, $id)
    {

        $routeName = $request->get('_route');

        if(
            $routeName == "employees_locations_pathaction_edit_standalone" ||
            $routeName == "employees_buildings_pathaction_edit_standalone" ||
            $routeName == "employees_researchlabs_pathaction_edit_standalone" ||
            $routeName == "employees_grants_pathaction_edit_standalone"
        ) {
            if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
                return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
            }
        }

        return $this->showList($request,$id,$this->container->getParameter('employees.sitename'));
    }
    public function showList(Request $request, $id, $sitename)
    {

        $routeName = $request->get('_route');

        $mapper = $this->classListMapper($routeName,$request);

        //get cycle
        $pieces = explode("_pathaction_", $routeName);
        $cycle = $pieces[1];

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);

        if( $mapper['pathname'] == 'grants' ) {
            $entity->createAttachmentDocument();
        }

        $form = $this->createCreateForm($entity,$cycle,$mapper,$sitename);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'id' => $entity->getId(),
            'singleName' => $mapper['singleName'],
            'displayName' => "List of ".$mapper['displayName'],
            'pathname' => $mapper['pathname'],
            'sitename' => $sitename
        );
    }


    /**
     * @Route("/location/new", name="employees_locations_pathaction_new_standalone")
     * @Route("/admin/buildings/new", name="employees_buildings_pathaction_new_standalone")
     * @Route("/admin/research-labs/new", name="employees_researchlabs_pathaction_new_standalone")
     * @Route("/admin/grants/new", name="employees_grants_pathaction_new_standalone")
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ComplexList:new.html.twig")
     */
    public function newListAction(Request $request)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->newList($request,$this->container->getParameter('employees.sitename'));
    }
    public function newList(Request $request, $sitename)
    {

        $routeName = $request->get('_route');

        $mapper = $this->classListMapper($routeName,$request);

        $entityClass = $mapper['fullClassName'];

        $cycle = 'new_standalone';

        $user = $this->get('security.context')->getToken()->getUser();

        $entity = new $entityClass($user);

        $form = $this->createCreateForm($entity,$cycle,$mapper,$sitename);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'id' => '',
            'singleName' => $mapper['singleName'],
            'displayName' => "List of ".$mapper['displayName'],
            'pathname' => $mapper['pathname'],
            'sitename' => $sitename
        );
    }


    /**
     * @Route("/location/new", name="employees_locations_pathaction_new_post_standalone")
     * @Route("/admin/buildings/new", name="employees_buildings_pathaction_new_post_standalone")
     * @Route("/admin/research-labs/new", name="employees_researchlabs_pathaction_new_post_standalone")
     * @Route("/admin/grants/new", name="employees_grants_pathaction_new_post_standalone")
     *
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:ComplexList:new.html.twig")
     */
    public function createListAction( Request $request )
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->createList($request,$this->container->getParameter('employees.sitename'));
    }
    public function createList( Request $request, $sitename )
    {

        $em = $this->getDoctrine()->getManager();

        $cycle = 'new_post_standalone';

        $user = $this->get('security.context')->getToken()->getUser();

        $routeName = $request->get('_route');

        $mapper = $this->classListMapper($routeName,$request);

        $entityClass = $mapper['fullClassName'];

        $entity = new $entityClass($user);

        $form = $this->createCreateForm($entity,$cycle,$mapper,$sitename);

        $form->handleRequest($request);

//        echo "loc errors:<br>";
//        print_r($form->getErrors());
//        echo "<br>loc string errors:<br>";
//        print_r($form->getErrorsAsString());
//        echo "<br>";
//        echo "creator=".$entity->getCreator()."<br>";
//        exit();

//        if( $form->isValid() ) {
//            exit("ok complex for classname ".$entityClass."<br>");
//        } else {
//            echo "labtest name=".$entity->getName()."<br>";
//            //exit("error complex for classname ".$entityClass."<br>");
//        }

        if( $form->isValid() ) {

            //echo "pathname=".$mapper['pathname']."<br>";
            if( $mapper['pathname'] == 'locations' ) {
                //set parents for institution tree for Administrative and Academical Titles
                $userUtil = new UserUtil();
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
                $sc = $this->get('security.context');
                $userUtil->setUpdateInfo($entity,$em,$sc);
            }

            if( $mapper['pathname'] == 'researchlabs' ) {
                $userUtil = new UserUtil();
                $sc = $this->get('security.context');
                $userUtil->setUpdateInfo($entity,$em,$sc);
            }

            if( $mapper['pathname'] == 'grants' ) {

                //process attachment documents
                if( $entity->getAttachmentContainer() ) {
                    foreach( $entity->getAttachmentContainer()->getDocumentContainers() as $documentContainer) {
                        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $documentContainer );
                    }
                    //echo "grant's documents count:".count($entity->getAttachmentContainer()->getDocumentContainers()->first()->getDocuments())."<br>";
                }

                $userUtil = new UserUtil();
                $sc = $this->get('security.context');
                $userUtil->setUpdateInfo($entity,$em,$sc);
            }

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($sitename.'_'.$mapper['pathname'].'_pathaction_show_standalone', array('id' => $entity->getId())));
        }

        //echo "error complex for classname ".$entityClass."<br>";

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'id' => '',
            'singleName' => $mapper['singleName'],
            'displayName' => "List of ".$mapper['displayName'],
            'pathname' => $mapper['pathname'],
            'sitename' => $sitename
        );
    }


    /**
     * @Route("/location/update/{id}", name="employees_locations_pathaction_edit_put_standalone",requirements={"id" = "\d+"})
     * @Route("/admin/buildings/update/{id}", name="employees_buildings_pathaction_edit_put_standalone",requirements={"id" = "\d+"})
     * @Route("/admin/research-labs/update/{id}", name="employees_researchlabs_pathaction_edit_put_standalone",requirements={"id" = "\d+"})
     * @Route("/admin/grants/update/{id}", name="employees_grants_pathaction_edit_put_standalone",requirements={"id" = "\d+"})
     *
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:ComplexList:new.html.twig")
     */
    public function updateListAction( Request $request, $id )
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        return $this->updateList($request,$id,$this->container->getParameter('employees.sitename'));
    }
    public function updateList( Request $request, $id, $sitename )
    {

        $routeName = $request->get('_route');
        $mapper = $this->classListMapper($routeName,$request);

        $cycle = 'edit_put_standalone';

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($mapper['bundleName'].':'.$mapper['className'])->find($id);

        //update author can be set to any user, not a current user
        $entity->setUpdateAuthor(null);

        $form = $this->createCreateForm($entity,$cycle,$mapper,$sitename);

        $form->handleRequest($request);

        //echo "loc errors:<br>";
        //$errors = $form->getErrors();
        //$errors = $form->getErrors(true, false);
        //print_r($errors);
        //echo "<br>";
        //exit();

        if( 1 || $form->isValid() ) {

            //echo "pathname=".$mapper['pathname']."<br>";

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

            if( $mapper['pathname'] == 'researchlabs' ) {
                $userUtil = new UserUtil();
                $em = $this->getDoctrine()->getManager();
                $sc = $this->get('security.context');
                $userUtil->setUpdateInfo($entity,$em,$sc);
            }

            if( $mapper['pathname'] == 'grants' ) {

                //process attachment documents
                if( $entity->getAttachmentContainer() ) {
                    foreach( $entity->getAttachmentContainer()->getDocumentContainers() as $documentContainer) {
                        $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments( $documentContainer );
                    }
                    //echo "grant's documents count:".count($entity->getAttachmentContainer()->getDocumentContainers()->first()->getDocuments())."<br>";
                }

                $userUtil = new UserUtil();
                $em = $this->getDoctrine()->getManager();
                $sc = $this->get('security.context');
                $userUtil->setUpdateInfo($entity,$em,$sc);
            }

            if( $mapper['pathname'] == 'labtests' ) {
                $userUtil = new UserUtil();
                $em = $this->getDoctrine()->getManager();
                $sc = $this->get('security.context');
                $userUtil->setUpdateInfo($entity,$em,$sc);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($sitename.'_'.$mapper['pathname'].'_pathaction_show_standalone', array('id' => $entity->getId())));
        }
        //exit('error');
        //echo "error loc <br>";

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'id' => '',
            'singleName' => $mapper['singleName'],
            'displayName' => "List of ".$mapper['displayName'],
            'pathname' => $mapper['pathname'],
            'sitename' => $sitename
        );
    }



    public function createCreateForm($entity,$cycle,$mapper,$sitename) {

        $em = $this->getDoctrine()->getManager();

        $disabled = false;
        $method = null;

        //echo "cycle=".$cycle."<br>";
        //echo "formType=".$mapper['fullFormType']."<br>";
        //echo "entity ID=".$entity->getId()."<br>";
        //if( $entity->getCreatedate() ) {
        //    echo "entity creationdate=" . $entity->getCreatedate()->format('d-m-Y') . "<br>";
        //}

        $path = $sitename.'_'.$mapper['pathname'].'_pathaction_'.$cycle;

        //create new page
        if( $cycle == "new_standalone" ) {
            //on a new page show a form with method=POST and action=create_post_standalone
            $method = "POST";
            $path = $sitename.'_'.$mapper['pathname'].'_pathaction_'.'new_post_standalone';
            $action = $this->generateUrl($path);
        }

        //create: submit action
        if( $cycle == "new_post_standalone" ) {
            $method = "POST";
            $action = $this->generateUrl($path);
        }

        //show existing page
        if( $cycle == "show_standalone" ) {
            $method = "GET";
            $action = $this->generateUrl($path, array('id' => $entity->getId()));
            $disabled = true;
        }

        //edit existing page
        if( $cycle == "edit_standalone" ) {
            //on a edit page show a form with method=PUT and action=edit_put_standalone
            $method = "PUT";
            $path = $sitename.'_'.$mapper['pathname'].'_pathaction_'.'edit_put_standalone';
            $action = $this->generateUrl($path, array('id' => $entity->getId()));
        }

        //edit: submit action
        if( $cycle == "edit_put_standalone" ) {
            $method = "PUT";
            $action = $this->generateUrl($path, array('id' => $entity->getId()));
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $isAdmin = $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR');

        $params = array('read_only'=>false,'admin'=>$isAdmin,'currentUser'=>false,'cycle'=>$cycle,'em'=>$em,'user'=>$user);

        $form = $this->createForm(new $mapper['fullFormType']($params,$entity), $entity, array(
            'disabled' => $disabled,
            'action' => $action,
            'method' => $method,
        ));

        //echo "after entity ID=".$entity->getId()."<br>";
        return $form;
    }



    public function classListMapper( $route, $request ) {

        //$route = employees_locations_pathaction_list
        $pieces = explode("_pathaction_", $route);
        $name = str_replace("employees_","",$pieces[0]);
        $cycle = $pieces[1];
        $bundlePrefix = "Oleg";
        $bundleName = "UserdirectoryBundle";

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
            case "researchlabs":
                $className = "ResearchLab";
                $displayName = "Research Labs";
                $singleName = "Research Lab";
                $formType = "ResearchLabType";
                break;
            case "grants":
                $className = "Grant";
                $displayName = "Grants";
                $singleName = "Grant";
                $formType = "GrantType";
                break;
            default:
                $className = null;
                $displayName = null;
        }

        //echo "className=".$className.", displayName=".$displayName."<br>";

        $res = array();
        $res['className'] = $className;
        $res['fullFormType'] = $bundlePrefix."\\".$bundleName."\\Form\\".$formType;
        $res['fullClassName'] = $bundlePrefix."\\".$bundleName."\\Entity\\".$className;
        $res['bundleName'] = $bundlePrefix.$bundleName;
        $res['displayName'] = $displayName;
        $res['singleName'] = $singleName;
        $res['pathname'] = $name;

        return $res;
    }

}
