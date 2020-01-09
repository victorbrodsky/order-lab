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

namespace App\CallLogBundle\Controller;


use App\CallLogBundle\Form\CalllogLoggerFilterType;
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
class CallLogLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="calllog_logger")
     * @Method("GET")
     * @Template("AppCallLogBundle/Logger/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_ADMIN") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

		$params = array('sitename'=>$this->container->getParameter('calllog.sitename'));
        $loggerFormParams = $this->listLogger($params,$request);

        return $loggerFormParams;
    }


    /**
     * @Route("/user/{id}/all", name="calllog_logger_user_all")
     * @Method("GET")
     * @Template("AppCallLogBundle/Logger/index.html.twig")
     */
    public function getAuditLogAllAction(Request $request)
    {
        $postData = $request->get('postData');
        $userid = $request->get('id');

        $entityName = 'User';

        $params = array(
            'sitename'=>$this->container->getParameter('calllog.sitename'),
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


//    /**
//     * Generation Log with eventTypes = "Generate Vacation Request"
//     *
//     * @Route("/generation-log/", name="calllog_generation_log")
//     * @Method("GET")
//     * @Template("AppCallLogBundle/Logger/index.html.twig")
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
     * @Template("AppCallLogBundle/Logger/index.html.twig")
     */
    public function myGenerationLogAction(Request $request) {
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_USER") ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userSecUtil = $this->get('user_security_utility');

        $eventType = $em->getRepository('AppUserdirectoryBundle:EventTypeList')->findOneByName("New Call Log Book Entry Submitted");
        if( !$eventType ) {
            throw $this->createNotFoundException('EventTypeList is not found by name ' . "New Call Log Book Entry Submitted");
        }

        //$objectType = $em->getRepository('AppUserdirectoryBundle:EventObjectTypeList')->findOneByName("Message");
        $objectType = $userSecUtil->getObjectByNameTransformer($user,"Message",'UserdirectoryBundle','EventObjectTypeList');
        if( !$objectType ) {
            throw $this->createNotFoundException('EventObjectTypeList is not found by name ' . "Message");
        }
        if( $objectType ) {
            $objectTypeId = $objectType->getId();
        } else {
            $objectTypeId = null;
        }

        ///////////// make sure eventTypes and users are set /////////////

        $objectTypes = array();
        $eventTypes = array();
        $users = array();

        $filter = $request->query->get('filter');

        if( count($filter) > 0 ) {
            $eventTypes = $filter['eventType'];
            $users = $filter['user'];
            $objectTypes = $filter['objectType'];
        }

//        echo 'eventType count='.count($eventTypes).'<br>';
//        foreach( $eventTypes as $eventType ) {
//            echo "eventType=".$eventType."<br>";
//        }
//        echo 'users count='.count($users).'<br>';
        //exit('1');

        //a user without Admin level role (ROLE_CALLLOG_ADMIN) can NOT change the filter in the URL to a user not equal to the currently logged in user.
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_ADMIN") ){
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
                    'filter[objectType][]' => $objectTypeId,
                    'filter[user][]' => $user->getId(),
                )
            ));
        }
        ///////////// EOF make sure eventTypes and users are set /////////////
        $params = array(
            'sitename' => $this->container->getParameter('calllog.sitename'),
            'showCapacity' => true,
            'entityId' => $user->getId()
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

        //print_r($eventTypes);
        //print_r($objectTypes);
        //echo "eventTypes=".$eventTypes."<br>";
        //exit();

        $em = $this->getDoctrine()->getManager();
        $eventType = $em->getRepository('AppUserdirectoryBundle:EventTypeList')->find($eventTypes[0]);
        $objectType = $em->getRepository('AppUserdirectoryBundle:EventObjectTypeList')->find($objectTypes[0]);
        $user = $em->getRepository('AppUserdirectoryBundle:User')->find($users[0]);

        //Event Log showing 1 matching "New Call Log Book Entry Submitted" event(s) for user:
        //$loggerFormParams['titlePostfix'] = " matching \"".$eventType."\" event(s) for user: ".$user;
        $eventlogTitle = $this->container->getParameter('eventlog_title');
        if( $loggerFormParams['filtered'] ) {
            $loggerFormParams['eventLogTitle'] = $eventlogTitle . " showing " . count($loggerFormParams['pagination']) . " matching ".
                "\"".$eventType."\" event(s) and \"" .$objectType.  "\" object(s) for user: ".$user;
        }

        //exit('before return');
        return $loggerFormParams;

    }

    public function createLoggerFilter($request,$params) {
        //$userid = $params['entityId'];
        $userid = ( array_key_exists('entityId', $params) ? $params['entityId'] : null);
        //echo "userid=".$userid."<br>";
        $routename = $request->get('_route');
        //echo "route=".$routename."<br>";
        //Start Date, Start Time, End Date, End Time, User [Select2 dropdown), Event Type [Entity Updated], [Free Text Search value for Event column] [Filter Button]
        return $this->createForm(CalllogLoggerFilterType::class, null, array(
            'method'=>'GET',
            'action' => $this->generateUrl($routename, array('id' => $userid)),
            'attr' => array('class'=>'well form-search'),
            'form_custom_value'=>$params,
        ));
    }

    //For calllog, for "My Entrees" page when $filterform has $capacity: add AND filter by $capacity (Submitter or Attending)
    //otherwise, use parent method and filter by $filterform['user']
    public function processOptionalFields( $dql, &$dqlParameters, $filterform, $filtered ) {

        //capacity:
        //$capacity = $filterform['capacity']->getData();
        if( $filterform->has('capacity') ) {
            $capacity = $filterform['capacity']->getData();
        } else {
            $capacity = null;
        }
        //echo "capacity=".$capacity."<br>";

        if( !$capacity ) {
            // by default this would be blank and the page would show any entries where the logged in user ($currentUser) is either "Submitter" OR "Attending"
            // => use parent generic method
            return parent::processOptionalFields($dql,$dqlParameters,$filterform,$filtered);
        }

        //echo "CallLogLoggerController: capacity=".$capacity."<br>";
        //echo "process Optional Fields <br>";
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $currentUserName = "Attending Physician: ".$currentUser."";
        //$currentUserName = $currentUser->getPrimaryPublicUserId()."";
        //echo "CallLogLoggerController: currentUserName=".$currentUserName."<br>";

        //the "Capacity" column would show whether the logged in user is a "Submitter" or the "Attending" for this Entry in that row;
        // by default this would be blank and the page would show any entries where the logged in user ($currentUser) is either "Submitter" OR "Attending"

        if( $capacity == "Submitter" ) {
            //echo "show only logger records where user=$currentUser <br>";
            $dql->andWhere("logger.user = :currentUser");
            $dqlParameters['currentUser'] = $currentUser->getId();
            $filtered = true;
        }
        if( $capacity == "Attending" ) {
            //encounter_1_attendingPhysicians_0_field
            //AppOrderformBundle:Message message

            //1) create select Message with encounter->attendingPhysicians->field(Wrapper)->user == $currentUser
//            $entryBodySearchStr =
//                " SELECT message.id FROM AppOrderformBundle:Message message ".
//                " LEFT JOIN message.encounter encounter ON message.id = encounter.message_id ".
//                " LEFT JOIN scan_encounterAttendingPhysician attendingPhysician ON encounter.id = attendingPhysician.encounter_id ".
//                " LEFT JOIN user_userWrapper userWrapper ON attendingPhysician.id = userWrapper.user ".
//                " WHERE ".
//                "(message.id = objectEntity.entityId AND objectEntity.entityName='Message' AND objectEntity.value LIKE :entryBodySearch)";
//            $dql->andWhere("EXISTS (".$entryBodySearchStr.")");

            //echo "show only logger records where user=$currentUserName <br>";
            $dql->andWhere("LOWER(logger.event) LIKE LOWER(:currentUserName)");
            $dqlParameters['currentUserName'] = '%'.$currentUserName.'%';

            //$dqlParameters['loggerUser'] = "IS NOT NULL";

            $filtered = true;
        }

//        if( !$capacity ) {
//            //by default this would be blank and the page would show any entries where the logged in user ($currentUser) is either "Submitter" OR "Attending"
//
//            $dql->andWhere("logger.user = :currentUser OR logger.event LIKE :currentUserName");
//            $dqlParameters['currentUser'] = $currentUser->getId();
//            $dqlParameters['currentUserName'] = '%'.$currentUserName.'%';
//
//            $filtered = true;
//        }

        //$dql->andWhere("logger.entityId = :objectId");
        //$dqlParameters['objectId'] = $objectId;

//        echo "<pre>";
//        print_r($dqlParameters);
//        echo "</pre>";

        return $filtered;
    }

    /**
     * Generation Log with eventTypes = "New Call Log Book Entry Submitted" and users = current user id
     *
     * @Route("/event-log-per-object/", name="calllog_event-log-per-object_log")
     * @Method("GET")
     * @Template("AppCallLogBundle/Logger/index.html.twig")
     */
    public function calllogEventLogPerObjectAction(Request $request)
    {
        if (false == $this->get('security.authorization_checker')->isGranted("ROLE_CALLLOG_USER")) {
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

        //$filterform = $this->createLoggerFilter($request,$params);
        //$filterform->handleRequest($request);
        //$objectId = $filterform['objectId']->getData();
        //echo "calllog: objectId=".$objectId."<br>";
        //print_r($request);

//        $objectId = $_GET['filter[objectId]'];
//        echo "calllog: filter[objectId]=".$objectId."<br>";
//
//        $objectId = $_GET['testkey'];
//        echo "calllog: testkey=".$objectId."<br>";
//
//        if( isset($_GET['filter[objectId]']) ) {
//            $objectId = $_GET['filter[objectId]'];
//            echo "calllog: objectId=".$objectId."<br>";
//            $objectId = "566,568,570";
//            //$filterform->get('objectId')->setData($objectId);
//        }

        //$objectId = "566";
        //$filterform['objectId']->setData($objectId);

        $loggerFormParams = $this->listLogger($params,$request);

        return $loggerFormParams;
    }

}
