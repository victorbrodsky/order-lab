<?php

namespace Oleg\VacReqBundle\Controller;

use Oleg\VacReqBundle\Entity\VacReqRequest;
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
     * Creates a new VacReqRequest entity.
     *
     * @Route("/new", name="vacreq_new")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Request:edit.html.twig")
     */
    public function newAction(Request $request)
    {
        $vacReqRequest = new VacReqRequest();

        $form = $this->createRequestForm(null,'new');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($vacReqRequest);
            $em->flush();

            return $this->redirectToRoute('vacreq_show', array('id' => $vacReqRequest->getId()));
        }

        return array(
            'vacReqRequest' => $vacReqRequest,
            'form' => $form->createView(),
        );
    }


    /**
     * Finds and displays a VacReqRequest entity.
     *
     * @Route("/show/{id}", name="vacreq_show")
     * @Method("GET")
     * @Template("OlegVacReqBundle:Request:edit.html.twig")
     */
    public function showAction(Request $request, $id)
    {
        if( false == $this->get('security.context')->isGranted('ROLE_DEIDENTIFICATOR_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegVacReqBundle:VacReqRequestForm')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Vacation Request by id='.$id);
        }

        return array(
            'entity' => $entity,
            //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing VacReqRequest entity.
     *
     * @Route("/edit/{id}", name="vacreq_edit")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Request:edit.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        //$deleteForm = $this->createDeleteForm($vacReqRequest);
        //$editForm = $this->createForm('Oleg\VacReqBundle\Form\VacReqRequestType', $vacReqRequest);

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegVacReqBundle:VacReqRequestForm')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Vacation Request by id='.$id);
        }

        $form = $this->createRequestForm($entity,'edit');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirectToRoute('vacreq_edit', array('id' => $entity->getId()));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            //'delete_form' => $deleteForm->createView(),
        );
    }



//    /**
//     * @Route("/request/{id}", name="vacreq_request_show")
//     * @Route("/request/edit/{id}", name="vacreq_request_edit")
//     * @Method("GET")
//     * @Template("OlegVacReqBundle:Request:new.html.twig")
//     */
//    public function newRequestAction(Request $request, $id)
//    {
//
//        if( false == $this->get('security.context')->isGranted('ROLE_DEIDENTIFICATOR_USER') ) {
//            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('OlegVacReqBundle:VacReqRequestForm')->find($id);
//
//        if( !$entity ) {
//            throw $this->createNotFoundException('Unable to find Vacation Request by id='.$id);
//        }
//
//        $routeName = $request->get('_route');
//
//        $cycle = "show";
//        if( $routeName = 'vacreq_request_edit' ) {
//            $cycle = "edit";
//        }
//
//        //VacReqRequestType Form
//
//        $form = $this->createRequestForm($entity, $cycle);
//
//        if( 1 ) {
//            $admin = true;
//        } else {
//            $admin = false;
//        }
//
//        return array(
//            'form' => $form->createView(),
//            'admin' => $admin,
//            'cycle' => $cycle
//        );
//    }


    public function createRequestForm( $entity, $cycle ) {

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        if( !$entity ) {
            $entity = new VacReqRequest($user);
        }

        $admin = false;
        if( $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            $admin = true;
        }

        $params = array(
            'sc' => $this->get('security.context'),
            'em' => $em,
            'user' => $entity->getUser(),
            'cycle' => $cycle,
            'roleAdmin' => $admin
        );

        $disabled = false;
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
