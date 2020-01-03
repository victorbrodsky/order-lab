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

namespace App\DeidentifierBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\UserdirectoryBundle\Entity\Logger;
use App\UserdirectoryBundle\Form\LoggerType;

use App\UserdirectoryBundle\Controller\LoggerController;

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
     * @Template("AppDeidentifierBundle:Logger:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_DEIDENTIFICATOR_ADMIN") ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

		$params = array('sitename'=>$this->container->getParameter('deidentifier.sitename'));
        $loggerFormParams = $this->listLogger($params,$request);

        return $loggerFormParams;
    }


    /**
     * @Route("/user/{id}/all", name="deidentifier_logger_user_all")
     * @Method("GET")
     * @Template("AppDeidentifierBundle:Logger:index.html.twig")
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
            'entityNamespace'=>'App\UserdirectoryBundle\Entity',
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
     * @Template("AppDeidentifierBundle:Logger:index.html.twig")
     */
    public function generationLogAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted("create", "Accession") ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $eventType = $em->getRepository('AppUserdirectoryBundle:EventTypeList')->findOneByName("Generate Accession Deidentifier ID");

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
     * @Template("AppDeidentifierBundle:Logger:index.html.twig")
     */
    public function myGenerationLogAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_DEIDENTIFICATOR_USER") ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $eventType = $em->getRepository('AppUserdirectoryBundle:EventTypeList')->findOneByName("Generate Accession Deidentifier ID");

        if( !$eventType ) {
            throw $this->createNotFoundException('EventTypeList is not found by name ' . "Generate Accession Deidentifier ID");
        }

        ///////////// make sure eventTypes and users are set /////////////
        $user = $this->get('security.token_storage')->getToken()->getUser();

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

        //a user without Admin level role (ROLE_DEIDENTIFICATOR_ADMIN) can NOT change the filter in the URL to a user not equal to the currently logged in user.
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_DEIDENTIFICATOR_ADMIN") ){
            foreach( $users as $thisUserId ) {
                //echo "thisUserId=".$thisUserId."<br>";
                if( $thisUserId != $user->getId() ) {
                    return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
                }
            }
        }

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
        $eventType = $em->getRepository('AppUserdirectoryBundle:EventTypeList')->find($eventTypes[0]);
        $user = $em->getRepository('AppUserdirectoryBundle:User')->find($users[0]);

        $eventlogTitle = $this->container->getParameter('eventlog_title');
        if( $loggerFormParams['filtered'] ) {
            $loggerFormParams['eventLogTitle'] = $eventlogTitle . " showing " . count($loggerFormParams['pagination']) . " matching ".
            "\"".$eventType."\" event(s) for user: ".$user;
        }

        //exit('before return');
        return $loggerFormParams;
    }

}
