<?php

namespace Oleg\FellAppBundle\Controller;

use Oleg\UserdirectoryBundle\Controller\LoggerController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\UserdirectoryBundle\Entity\Logger;
use Oleg\UserdirectoryBundle\Form\LoggerType;

/**
 * Logger controller.
 *
 * @Route("/event-log")
 */
class FellAppLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="fellapp_logger")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Logger:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $params = array(
            'sitename'=>$this->container->getParameter('fellapp.sitename')
        );
        return $this->listLogger($params,$request);
    }


    /**
     * Filter by Object Type "FellowshipApplication" and Object ID
     *
     * @Route("/application-log/{id}", name="fellapp_application_log")
     * @Method("GET")
     * @Template("OlegFellAppBundle:Logger:index.html.twig")
     */
    public function applicationLogAction(Request $request,$id) {

//        if(
//            false == $this->get('security.context')->isGranted('ROLE_FELLAPP_COORDINATOR') &&
//            false == $this->get('security.context')->isGranted('ROLE_FELLAPP_DIRECTOR') &&
//            false == $this->get('security.context')->isGranted('ROLE_FELLAPP_ADMIN')
//        ) {
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }

        if( false == $this->get('security.context')->isGranted("read","FellowshipApplication") ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $fellApp = $em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($id);
        if( !$fellApp ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        if( false == $this->get('security.context')->isGranted("read",$fellApp) ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $objectType = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->findOneByName("FellowshipApplication");
        if( !$objectType ) {
            throw $this->createNotFoundException('Unable to find EventObjectTypeList by name='."FellowshipApplication");
        }

        return $this->redirect($this->generateUrl('fellapp_logger', array('filter[objectType][]' => $objectType->getId(), 'filter[objectId]' => $id)));
    }

}
