<?php

namespace Oleg\CallLogBundle\Controller;


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
class CallLogLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="calllog_logger")
     * @Method("GET")
     * @Template("OlegCallLogBundle:Logger:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false == $this->get('security.context')->isGranted("ROLE_CALLLOG_ADMIN") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

		$params = array('sitename'=>$this->container->getParameter('calllog.sitename'));
        $loggerFormParams = $this->listLogger($params,$request);

        return $loggerFormParams;
    }


    /**
     * @Route("/user/{id}/all", name="calllog_logger_user_all")
     * @Method("GET")
     * @Template("OlegCallLogBundle:Logger:index.html.twig")
     */
    public function getAuditLogAllAction(Request $request)
    {
        $postData = $request->get('postData');
        $userid = $request->get('id');

        $entityName = 'User';

        $params = array(
            'sitename'=>$this->container->getParameter('calllog.sitename'),
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


//    /**
//     * Generation Log with eventTypes = "Generate Vacation Request"
//     *
//     * @Route("/generation-log/", name="calllog_generation_log")
//     * @Method("GET")
//     * @Template("OlegCallLogBundle:Logger:index.html.twig")
//     */
//    public function generationLogAction(Request $request)
//    {
//
//    }


    /**
     * Generation Log with eventTypes = "New Call Log Book Entry Submitted" and users = current user id
     *
     * @Route("/event-log-per-user-per-event-type/", name="calllog_my_generation_log")
     * @Method("GET")
     * @Template("OlegCallLogBundle:Logger:index.html.twig")
     */
    public function myGenerationLogAction(Request $request) {
        if( false == $this->get('security.context')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $eventType = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName("New Call Log Book Entry Submitted");
        if( !$eventType ) {
            throw $this->createNotFoundException('EventTypeList is not found by name ' . "New Call Log Book Entry Submitted");
        }

        $objectType = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->findOneByName("Message");
        if( !$objectType ) {
            throw $this->createNotFoundException('EventObjectTypeList is not found by name ' . "Message");
        }

        ///////////// make sure eventTypes and users are set /////////////
        $user = $this->get('security.context')->getToken()->getUser();

        $objectTypes = array();
        $eventTypes = array();
        $users = array();

        $filter = $request->query->get('filter');

        if( count($filter) > 0 ) {
            $eventTypes = $filter['eventType'];
            $users = $filter['user'];
            $objectTypes = $filter['objectType'];
        }
        //echo 'eventType count='.count($eventTypes).'<br>';
        //echo 'users count='.count($users).'<br>';
        //exit('1');

        //a user without Admin level role (ROLE_CALLLOG_ADMIN) can NOT change the filter in the URL to a user not equal to the currently logged in user.
        if( false == $this->get('security.context')->isGranted("ROLE_CALLLOG_ADMIN") ){
            foreach( $users as $thisUserId ) {
                //echo "thisUserId=".$thisUserId."<br>";
                if( $thisUserId != $user->getId() ) {
                    return $this->redirect( $this->generateUrl('calllog-nopermission') );
                }
            }
        }

        if( count($eventTypes) == 0 || count($users) == 0 || count($objectTypes) == 0 ) {
            //echo 'assign and redirect back <br>';
            //add eventTypes and users
            return $this->redirect($this->generateUrl('calllog_my_generation_log',
                array(
                    'filter[eventType][]' => $eventType->getId(),
                    'filter[objectType][]' => $objectType->getId(),
                    'filter[user][]' => $user->getId(),
                )
            ));
        }
        ///////////// EOF make sure eventTypes and users are set /////////////

        $params = array(
            'sitename' => $this->container->getParameter('calllog.sitename'),
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
        $objectTypes = $filterform['objectType']->getData();
        $users = $filterform['user']->getData();

        $em = $this->getDoctrine()->getManager();
        $eventType = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->find($eventTypes[0]);
        $objectType = $em->getRepository('OlegUserdirectoryBundle:EventObjectTypeList')->find($objectTypes[0]);
        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($users[0]);

        //Event Log showing 1 matching "New Call Log Book Entry Submitted" event(s) for user: Victor Brodsky - vib9020 (WCMC CWID)
        //$loggerFormParams['titlePostfix'] = " matching \"".$eventType."\" event(s) for user: ".$user;
        $eventlogTitle = $this->container->getParameter('eventlog_title');
        if( $loggerFormParams['filtered'] ) {
            $loggerFormParams['eventLogTitle'] = $eventlogTitle . " showing " . count($loggerFormParams['pagination']) . " matching ".
                "\"".$eventType."\" event(s) and \"" .$objectType.  "\" object(s) for user: ".$user;
        }

        //exit('before return');
        return $loggerFormParams;

    }

    /**
     * Generation Log with eventTypes = "New Call Log Book Entry Submitted" and users = current user id
     *
     * @Route("/event-log-per-object/", name="calllog_event-log-per-object_log")
     * @Method("GET")
     * @Template("OlegCallLogBundle:Logger:index.html.twig")
     */
    public function calllogEventLogPerObjectAction(Request $request)
    {
        if (false == $this->get('security.context')->isGranted("ROLE_CALLLOG_USER")) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        //filter[objectType][]=4
        //filter[objectId]=178

//        $filter = $request->query->get('filter');
//
//        if( count($filter) > 0 ) {
//            $objectTypes = $filter['objectType'];
//            $objectId = $filter['objectId'];
//        }
        //echo "$objectTypes, $objectId <br>";
        //exit();

        $params = array('sitename'=>$this->container->getParameter('calllog.sitename'));
        $loggerFormParams = $this->listLogger($params,$request);

        return $loggerFormParams;
    }

}
