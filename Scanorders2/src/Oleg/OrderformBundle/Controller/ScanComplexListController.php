<?php

namespace Oleg\OrderformBundle\Controller;


use Oleg\UserdirectoryBundle\Controller\ComplexListController;
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


class ScanComplexListController extends ComplexListController
{


    /**
     * @Route("/list/laboratoty-tests/", name="employees_labtests_pathaction_list")
     *
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




    /**
     * @Route("/laboratory-tests/show/{id}", name="employees_labtests_pathaction_show_standalone", requirements={"id" = "\d+"})
     * @Route("/admin/laboratory-tests/edit/{id}", name="employees_labtests_pathaction_edit_standalone", requirements={"id" = "\d+"})
     *
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ComplexList:list.html.twig")
     */
    public function showListAction(Request $request, $id)
    {

        return $this->showList($request,$id);
    }


    /**
     * @Route("/admin/laboratory-tests/new", name="employees_labtests_pathaction_new_standalone")
     *
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:ComplexList:list.html.twig")
     */
    public function newListAction(Request $request)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_SUBMITTER') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        return $this->newList($request);
    }


    /**
     * @Route("/admin/laboratory-tests/new", name="employees_labtests_pathaction_new_post_standalone")
     *
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:ComplexList:list.html.twig")
     */
    public function createListAction( Request $request )
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        return $this->createList($request);
    }


    /**
     * @Route("/admin/laboratory-tests/{id}", name="employees_labtests_pathaction_edit_put_standalone",requirements={"id" = "\d+"})
     *
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:ComplexList:list.html.twig")
     */
    public function updateListAction( Request $request, $id )
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        return $this->updateList($request,$id);
    }



    public function createCreateForm($entity,$cycle,$mapper) {

        $em = $this->getDoctrine()->getManager();

        $disabled = false;
        $method = null;

        //echo "cycle=".$cycle."<br>";
        //echo "formType=".$mapper['fullFormType']."<br>";

        $path = $this->container->getParameter('employees.sitename').'_'.$mapper['pathname'].'_pathaction_'.$cycle;

        //create new page
        if( $cycle == "new_standalone" ) {
            //on a new page show a form with method=POST and action=create_post_standalone
            $method = "POST";
            $path = $this->container->getParameter('employees.sitename').'_'.$mapper['pathname'].'_pathaction_'.'new_post_standalone';
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
            $path = $this->container->getParameter('employees.sitename').'_'.$mapper['pathname'].'_pathaction_'.'edit_put_standalone';
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


        return $form;
    }



    public function classListMapper( $route ) {

        //$route = employees_locations_pathaction_list
        $pieces = explode("_pathaction_", $route);
        $name = str_replace("employees_","",$pieces[0]);
        $cycle = $pieces[1];
        $bundlePrefix = "Oleg";
        $bundleName = "UserdirectoryBundle";

        switch( $name ) {

            case "labtests":
                $className = "LabTest";
                $displayName = "Laboratory Tests";
                $singleName = "Laboratory Test";
                $formType = "LabTestType";
                $bundleName = "OrderformBundle";
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
