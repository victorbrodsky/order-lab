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
        $loggerFormParams = $this->listLogger($params,$request);

        return $loggerFormParams;
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

        //return $this->redirect($this->generateUrl('deidentifier_logger', array('filter[eventType][]' => $eventType->getId() )));
        ///////////// make sure eventTypes are set /////////////
        $eventTypes = array();

        $filter = $request->query->get('filter');

        if( count($filter) > 0 ) {
            $eventTypes = $filter['eventType'];
        }

        if( count($eventTypes) == 0 ) {
            //add eventTypes
            return $this->redirect($this->generateUrl('deidentifier_generation_log',
                array(
                    'filter[eventType][]' => $eventType->getId()
                )
            ));
        }
        ///////////// EOF make sure eventTypes and users are set /////////////


        $params = array(
            'sitename' => $this->container->getParameter('deidentifier.sitename'),
            'hideEventType' => true,
        );
        $loggerFormParams = $this->listLogger($params,$request);

        $loggerFormParams['hideUserAgent'] = true;
        $loggerFormParams['hideWidth'] = true;
        $loggerFormParams['hideHeight'] = true;
        $loggerFormParams['hideADServerResponse'] = true;

        $loggerFormParams['hideIp'] = true;
        $loggerFormParams['hideRoles'] = true;
        $loggerFormParams['hideId'] = true;         //Event ID
        $loggerFormParams['hideObjectType'] = true;
        $loggerFormParams['hideObjectId'] = true;

        return $loggerFormParams;
    }


    /**
     * Generation Log with eventTypes = "Generate Accession Deidentifier ID" and users = current user id
     *
     * @Route("/event-log-per-user-per-event-type/", name="deidentifier_my_generation_log")
     * @Method("GET")
     * @Template("OlegDeidentifierBundle:Logger:index.html.twig")
     */
    public function myGenerationLogAction(Request $request)
    {
        if( false == $this->get('security.context')->isGranted("ROLE_DEIDENTIFICATOR_USER") ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $eventType = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName("Generate Accession Deidentifier ID");

        if( !$eventType ) {
            throw $this->createNotFoundException('EventTypeList is not found by name ' . "Generate Accession Deidentifier ID");
        }

        ///////////// make sure eventTypes and users are set /////////////
        $user = $this->get('security.context')->getToken()->getUser();

        $eventTypes = array();
        $users = array();

        $filter = $request->query->get('filter');

        if( count($filter) > 0 ) {
            $eventTypes = $filter['eventType'];
            $users = $filter['user'];
        }
        //echo 'eventType count='.count($eventTypes).'<br>';
        //echo 'users count='.count($users).'<br>';
        //exit('1');

        if( count($eventTypes) == 0 || count($users) == 0 ) {
            //echo 'assign and redirect back <br>';
            //add eventTypes and users
            return $this->redirect($this->generateUrl('deidentifier_my_generation_log',
                array(
                    'filter[eventType][]' => $eventType->getId(),
                    'filter[user][]' => $user->getId(),
                )
            ));
        }
        ///////////// EOF make sure eventTypes and users are set /////////////

        $params = array(
            'sitename' => $this->container->getParameter('deidentifier.sitename'),
            //'hideObjectType' => true,
            //'hideObjectId' => true,
            //'hideUser' => true,
            //'hideEventType' => true
        );
        $loggerFormParams = $this->listLogger($params,$request);

        $loggerFormParams['hideUserAgent'] = true;
        $loggerFormParams['hideWidth'] = true;
        $loggerFormParams['hideHeight'] = true;
        $loggerFormParams['hideADServerResponse'] = true;

        $loggerFormParams['hideIp'] = true;
        $loggerFormParams['hideRoles'] = true;
        $loggerFormParams['hideId'] = true;         //Event ID
        //$loggerFormParams['hideObjectType'] = true;
        //$loggerFormParams['hideObjectId'] = true;

        $loggerFormParams['hideUser'] = true;
        $loggerFormParams['hideEventType'] = true;

        //get title postfix: Event Log showing 9 matching “EVENT TYPE” events for user: First Name LastName (CWID)
        $filterform = $loggerFormParams['filterform'];
        $eventTypes = $filterform['eventType']->getData();
        $users = $filterform['user']->getData();

        $em = $this->getDoctrine()->getManager();
        $eventType = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->find($eventTypes[0]);
        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($users[0]);

        //Event Log showing 1 matching "Generate Accession Deidentifier ID" event(s) for user: Victor Brodsky - vib9020 (WCMC CWID)
        //$loggerFormParams['titlePostfix'] = " matching \"".$eventType."\" event(s) for user: ".$user;
        $eventlogTitle = $this->container->getParameter('eventlog_title');
        if( $loggerFormParams['filtered'] ) {
            $loggerFormParams['eventLogTitle'] = $eventlogTitle . " showing " . count($loggerFormParams['pagination']) . " matching ".
            "\"".$eventType."\" event(s) for user: ".$user;
        }

        //exit('before return');
        return $loggerFormParams;
    }

}
