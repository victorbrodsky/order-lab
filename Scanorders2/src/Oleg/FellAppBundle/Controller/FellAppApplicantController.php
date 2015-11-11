<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 11/11/15
 * Time: 3:42 PM
 */

namespace Oleg\FellAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\FellAppBundle\Entity\FellowshipApplication;
use Oleg\UserdirectoryBundle\Entity\User;

class FellAppApplicantController extends Controller {



    /**
     * @Route("/interview-modal/{id}", name="fellapp_interview_modal")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Interview:modal.html.twig")
     */
    public function interviewModalAction(Request $request, $id) {

        if( false == $this->get('security.context')->isGranted('ROLE_FELLAPP_USER') ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "invite interviewers to rate <br>";
        //exit();
        $res = "";

        $logger = $this->container->get('logger');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }



        return array(
            'entity' => $entity,
            'pathbase' => 'fellapp',
            'sitename' => $this->container->getParameter('fellapp.sitename')
        );
    }



} 