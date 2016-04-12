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

class RequestIndexController extends Controller
{

    /**
     * Creates a new VacReqRequest entity.
     *
     * @Route("/my-requests/", name="vacreq_myrequests")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Default:index.html.twig")
     */
    public function myRequestsAction(Request $request)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegVacReqBundle:VacReqRequest')->findAll();

        return array(
            'entities' => $entities,
        );
    }


    /**
     * Creates a new VacReqRequest entity.
     *
     * @Route("/incoming-requests/", name="vacreq_incomingrequests")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Default:index.html.twig")
     */
    public function incomingRequestsAction(Request $request)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegVacReqBundle:VacReqRequest')->findAll();

        return array(
            'entities' => $entities,
        );
    }

}
