<?php

namespace Oleg\VacReqBundle\Controller;

use Oleg\VacReqBundle\Form\VacReqRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//vacreq site

class RequestController extends Controller
{


    /**
     * @Route("/", name="vacreq_home")
     * @Template("OlegVacReqBundle:Default:index.html.twig")
     * @Method("GET")
     */

    /**
     * @Route("/request/{id}", name="vacreq_request_show")
     * @Route("/request/edit/{id}", name="vacreq_request_edit")
     * @Method("GET")
     * @Template("OlegVacReqBundle:Request:new.html.twig")
     */
    public function newRequestAction(Request $request, $id)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_DEIDENTIFICATOR_USER') ) {
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegVacReqBundle:VacReqRequestForm')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Vacation Request by id='.$id);
        }

        $routeName = $request->get('_route');

        $cycle = "show";
        if( $routeName = 'vacreq_request_edit' ) {
            $cycle = "edit";
        }

        //VacReqRequestType Form

        $form = $this->createRequestForm($entity, $cycle);

        if( 1 ) {
            $admin = true;
        } else {
            $admin = false;
        }

        return array(
            'form' => $form->createView(),
            'admin' => $admin,
            'cycle' => $cycle
        );
    }



    public function createRequestForm( $entity, $cycle ) {

        if( $cycle == 'show' ) {
            $disabled = true;
        }

        $form = $this->createForm(
            new VacReqRequestType($params),
            $entity,
            array(
                'disabled' => $disabled,
                //'method' => $method,
                //'action' => $action
            )
        );

        return $form;
    }
}
