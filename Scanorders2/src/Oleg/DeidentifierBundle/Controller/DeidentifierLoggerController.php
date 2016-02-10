<?php

namespace Oleg\DeidentifierBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\UserdirectoryBundle\Entity\Logger;
use Oleg\UserdirectoryBundle\Form\LoggerType;

use Oleg\UserdirectoryBundle\Controller\LoggerController;

/**
 * Logger controller.
 *
 * @Route("/event-log")
 */
class DeidentifierLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="deidentifier_logger")
     * @Method("GET")
     * @Template("OlegDeidentifierBundle:Logger:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false == $this->get('security.context')->isGranted("ROLE_DEIDENTIFICATOR_ADMIN") ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

		$params = array('sitename'=>$this->container->getParameter('deidentifier.sitename'));
        return $this->listLogger($params,$request);
    }


    /**
     * @Route("/user/{id}/all", name="deidentifier_logger_user_all")
     * @Method("GET")
     * @Template("OlegDeidentifierBundle:Logger:index.html.twig")
     */
    public function getAuditLogAllAction(Request $request)
    {
        $postData = $request->get('postData');
        $userid = $request->get('id');
        //$onlyheader = $request->get('onlyheader');

        //echo "postData=<br>";
        //print_r($postData);

        $entityName = 'User';

        $params = array(
            'sitename'=>$this->container->getParameter('deidentifier.sitename'),
            'entityNamespace'=>'Oleg\UserdirectoryBundle\Entity',
            'entityName'=>$entityName,
            'entityId'=>$userid,
            'postData'=>$postData,
            'onlyheader'=>false,
            'allsites'=>true
        );

        $logger =  $this->listLogger($params,$request);

        return $logger;
    }


    /**
     * Generation Log with eventTypes = "Generate Accession Deidentifier ID"
     *
     * @Route("/generation-log/", name="deidentifier_generation_log")
     * @Method("GET")
     * @Template("OlegDeidentifierBundle:Logger:index.html.twig")
     */
    public function generationLogAction(Request $request)
    {
        if( false == $this->get('security.context')->isGranted("create", "Accession") ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $eventType = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName("Generate Accession Deidentifier ID");

        if( !$eventType ) {
            throw $this->createNotFoundException('EventTypeList is not found by name ' . "Generate Accession Deidentifier ID");
        }

        return $this->redirect($this->generateUrl('deidentifier_logger', array('filter[eventType][]' => $eventType->getId() )));
    }

}
