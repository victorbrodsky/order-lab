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

namespace App\UserdirectoryBundle\Controller;


use App\UserdirectoryBundle\Entity\TelephonySiteParameter;
use App\UserdirectoryBundle\Form\TelephonySiteParameterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Controller\SiteParametersController;


/**
 * TelephonySiteParameters controller.
 *
 * @Route("/telephony-settings")
 */
class TelephonySiteParametersController extends OrderAbstractController //SiteParametersController
{

    /**
     * @Route("/show-content/", name="employees_telephonysiteparameters_show", methods={"GET"})
     * @Template("AppUserdirectoryBundle/TelephonySiteParameters/telephony-form-content.html.twig")
     */
    public function telephonySiteParameterPreviewAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $cycle = "show";

        $telephonySiteParameter = $this->getOrCreateNewTelephonyParameters();
        //echo "telephonySiteParameter=".$telephonySiteParameter->getId()."<br>";

        $form = $this->createTelephonySiteParameterForm($telephonySiteParameter,$cycle);

        return array(
            'entity' => $telephonySiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Telephony Site Parameters"
        );
    }

    /**
     * @Route("/show/", name="employees_telephonysiteparameters_show", methods={"GET"})
     * @Template("AppUserdirectoryBundle/TelephonySiteParameters/telephony-form.html.twig")
     */
    public function telephonySiteParameterShowAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $cycle = "show";

        $telephonySiteParameter = $this->getOrCreateNewTelephonyParameters();
        //echo "telephonySiteParameter=".$telephonySiteParameter->getId()."<br>";

        $form = $this->createTelephonySiteParameterForm($telephonySiteParameter,$cycle);

        return array(
            'entity' => $telephonySiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Telephony Site Parameters"
        );
    }

    /**
     * @Route("/edit/", name="employees_telephonysiteparameters_edit", methods={"GET", "POST"})
     * @Template("AppUserdirectoryBundle/TelephonySiteParameters/telephony-form.html.twig")
     */
    public function telephonySiteParameterEditAction( Request $request ) {

        //exit('telephonySiteParameterEditAction');

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $cycle = "edit";

        $telephonySiteParameter = $this->getOrCreateNewTelephonyParameters();
        //echo "telephonySiteParameter=".$telephonySiteParameter->getId()."<br>";

        $form = $this->createTelephonySiteParameterForm($telephonySiteParameter,$cycle);
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();

            //exit('submit');
            $em->persist($telephonySiteParameter);
            $em->flush();

            return $this->redirect($this->generateUrl('employees_telephonysiteparameters_show'));
        }

        return array(
            'entity' => $telephonySiteParameter,
            'form'   => $form->createView(),
            'cycle' => $cycle,
            'title' => "Update Telephony Site Parameters"
        );
    }

    public function createTelephonySiteParameterForm($entity, $cycle) {
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

        $form = $this->createForm(TelephonySiteParameterType::class, $entity, array(
            'form_custom_value' => $params,
            'disabled' => $disabled
        ));

        return $form;
    }

    //Get or Create a new TelephonySiteParameter getOrCreateNewVacReqParameters
    public function getOrCreateNewTelephonyParameters() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppUserdirectoryBundle:SiteParameters')->findAll();
        if( count($entities) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($entities).'object(s)' );
        }
        $siteParameters = $entities[0];

        $telephonySiteParameter = $siteParameters->getTelephonySiteParameter();

        //create one TelephonySiteParameter
        if( !$telephonySiteParameter ) {
            $telephonySiteParameter = new TelephonySiteParameter();
            $siteParameters->setTelephonySiteParameter($telephonySiteParameter);
            $em->flush();
        }

        return $telephonySiteParameter;
    }


}
