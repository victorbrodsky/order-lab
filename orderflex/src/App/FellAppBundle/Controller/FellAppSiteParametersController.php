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

namespace App\FellAppBundle\Controller;

use App\FellAppBundle\Entity\FellappSiteParameter;
use App\FellAppBundle\Form\FellappSiteParameterType;
use App\UserdirectoryBundle\Controller\SiteParametersController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

use App\UserdirectoryBundle\Entity\SiteParameters;
use App\UserdirectoryBundle\Form\SiteParametersType;
use App\UserdirectoryBundle\Util\UserUtil;

/**
 * SiteParameters controller.
 *
 * @Route("/settings")
 */
class FellAppSiteParametersController extends SiteParametersController
{

    /**
     * Lists all SiteParameters entities.
     *
     * @Route("/site-settings/", name="fellapp_sitesettings_siteparameters", methods={"GET"})
     * @Template("AppUserdirectoryBundle/SiteParameters/site-index.html.twig")
     */
    public function indexSiteSettingsAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }
        return $this->indexParameters($request);
    }

    /**
     * Lists all SiteParameters entities.
     *
     * @Route("/", name="fellapp_siteparameters", methods={"GET"})
     * @Template("AppUserdirectoryBundle/SiteParameters/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }
        return $this->indexParameters($request);
    }

    /**
     * Displays a form to edit an existing SiteParameters entity.
     *
     * @Route("/{id}/edit", name="fellapp_siteparameters_edit", methods={"GET"})
     * @Template("AppUserdirectoryBundle/SiteParameters/edit.html.twig")
     */
    public function editAction(Request $request,$id)
    {
        return $this->editParameters($request,$id);
    }

    /**
     * Edits an existing SiteParameters entity.
     *
     * @Route("/{id}", name="fellapp_siteparameters_update", methods={"PUT"})
     * @Template("AppUserdirectoryBundle/SiteParameters/edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->updateParameters($request, $id);
    }


    /**
     * FellAppSiteParameter
     *
     * @Route("/specific-site-parameters/edit-page/", name="fellapp_siteparameters_edit_specific_site_parameters", methods={"GET","POST"})
     * @Template("AppFellAppBundle/SiteParameter/edit.html.twig")
     */
    public function fellappSiteParameterEditAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $cycle = "edit";

        $fellappSiteParameter = $this->getOrCreateNewFellAppParameters($cycle);

        $form = $this->createFellAppSiteParameterForm($fellappSiteParameter,$cycle);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();

            //set end default date as "Start" date - 1 day
            //exit('end date');
            if( $fellappSiteParameter->getFellappAcademicYearEnd() === NULL ) {
                $startDate = $fellappSiteParameter->getFellappAcademicYearStart();
                if( $startDate ) {
                    //"Start" date - 1 day
                    $thisEndDate = clone $startDate;
                    $thisEndDate->modify('-1 day');
                    $fellappSiteParameter->setFellappAcademicYearEnd($thisEndDate);
                }
//                else {
//                    $currentYear = intval(date("Y"));
//                    $june30 = new \DateTime($currentYear."-06-30");
//                    //echo "set start date=".$june30->format('yyyy-mm-dd')."<br>";
//                    $fellappSiteParameter->setFellappAcademicYearEnd($june30);
//                }
            }

            //exit('submit');
            $em->persist($fellappSiteParameter);
            $em->flush();

            return $this->redirect($this->generateUrl('fellapp_siteparameters'));
        }

        return array(
            'entity' => $fellappSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Update Fellowship Specific Site Parameters"
        );
    }

    /**
     * FellAppSiteParameter Show
     *
     * @Route("/specific-site-parameters/show/", name="fellapp_siteparameters_show_specific_site_parameters", methods={"GET"})
     * @Template("AppFellAppBundle/SiteParameter/edit-content.html.twig")
     */
    public function fellappSiteParameterShowAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $cycle = "show";

        $fellappSiteParameter = $this->getOrCreateNewFellAppParameters($cycle);
        //echo "fellappSiteParameter=".$fellappSiteParameter->getId()."<br>";

        $form = $this->createFellAppSiteParameterForm($fellappSiteParameter,$cycle);

        return array(
            'entity' => $fellappSiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Fellowship Specific Site Parameters"
        );
    }

    public function createFellAppSiteParameterForm($entity, $cycle) {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $disabled = false;
        if( $cycle == "show" ) {
            $disabled = true;
        }

        $params = array(
            'cycle' => $cycle,
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
        );

        $form = $this->createForm(FellappSiteParameterType::class, $entity, array(
            'form_custom_value' => $params,
            'disabled' => $disabled
        ));

        return $form;
    }

    //Get or Create a new FellAppSiteParameter
    public function getOrCreateNewFellAppParameters( $cycle ) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();
        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }
        $siteParameters = $entities[0];

        $fellappSiteParameter = $siteParameters->getFellappSiteParameter();

        //create one FellAppSiteParameter
        if( !$fellappSiteParameter ) {
            //echo "FellAppSiteParameter null <br>";
            $fellappSiteParameter = new FellappSiteParameter();

            $siteParameters->setFellappSiteParameter($fellappSiteParameter);
            $em->flush();
        }

        if(0) {
            //set start default 1 July 2021 if NULL
            if ($fellappSiteParameter->getFellappAcademicYearStart() === NULL) {
                $currentYear = intval(date("Y"));
                $july1 = new \DateTime($currentYear . "-07-01");
                //echo "set start date=".$july1->format('yyyy-mm-dd')."<br>";
                $fellappSiteParameter->setFellappAcademicYearStart($july1);

            }
            //set end default 30 June 2021 if NULL
            if ($fellappSiteParameter->getFellappAcademicYearEnd() === NULL) {
                $startDate = $fellappSiteParameter->getFellappAcademicYearStart();
                if ($startDate) {
                    //"Start" date - 1 day
                    $thisEndDate = clone $startDate;
                    $thisEndDate->modify('-1 day');
                    $fellappSiteParameter->setFellappAcademicYearEnd($thisEndDate);
                } else {
                    $currentYear = intval(date("Y"));
                    $june30 = new \DateTime($currentYear . "-06-30");
                    //echo "set start date=".$june30->format('yyyy-mm-dd')."<br>";
                    $fellappSiteParameter->setFellappAcademicYearEnd($june30);
                }
            }
            //echo "set  date <br>";
        }

        return $fellappSiteParameter;
    }
    
}
