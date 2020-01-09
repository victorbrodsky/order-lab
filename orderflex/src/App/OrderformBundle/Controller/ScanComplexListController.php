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


use App\UserdirectoryBundle\Controller\ComplexListController;
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

use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use App\UserdirectoryBundle\Form\LocationType;
use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Entity\Location;


class ScanComplexListController extends ComplexListController
{


    /**
     * @Route("/list/laboratoty-tests/", name="scan_labtests_pathaction_list")
     *
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/ComplexList/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->getList($request,$this->container->getParameter('scan.sitename'));
    }




    /**
     * @Route("/laboratory-tests/show/{id}", name="scan_labtests_pathaction_show_standalone", requirements={"id" = "\d+"})
     * @Route("/admin/laboratory-tests/edit/{id}", name="scan_labtests_pathaction_edit_standalone", requirements={"id" = "\d+"})
     *
     *
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/ComplexList/new.html.twig")
     */
    public function showListAction(Request $request, $id)
    {

        $routeName = $request->get('_route');

        if(
            $routeName == $this->container->getParameter('scan.sitename')."_labtests_pathaction_edit_standalone"
        ) {
            if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
                return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
            }
        }

        return $this->showList($request,$id,$this->container->getParameter('scan.sitename'));
    }


    /**
     * @Route("/admin/laboratory-tests/new", name="scan_labtests_pathaction_new_standalone")
     *
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/ComplexList/new.html.twig")
     */
    public function newListAction(Request $request)
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->newList($request,$this->container->getParameter('scan.sitename'));
    }


    /**
     * @Route("/admin/laboratory-tests/new", name="scan_labtests_pathaction_new_post_standalone")
     *
     * @Method("POST")
     * @Template("AppUserdirectoryBundle/ComplexList/new.html.twig")
     */
    public function createListAction( Request $request )
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->createList($request,$this->container->getParameter('scan.sitename'));
    }


    /**
     * @Route("/admin/laboratory-tests/{id}", name="scan_labtests_pathaction_edit_put_standalone",requirements={"id" = "\d+"})
     *
     * @Method("PUT")
     * @Template("AppUserdirectoryBundle/ComplexList/new.html.twig")
     */
    public function updateListAction( Request $request, $id )
    {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('scan.sitename').'-order-nopermission') );
        }

        return $this->updateList($request,$id,$this->container->getParameter('scan.sitename'));
    }





    public function classListMapper( $route, $request ) {

        //$route = scan_locations_pathaction_list
        $pieces = explode("_pathaction_", $route);
        $name = str_replace("scan_","",$pieces[0]);
        $cycle = $pieces[1];
        $bundlePrefix = "App";
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
