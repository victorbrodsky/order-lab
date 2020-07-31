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

namespace App\ResAppBundle\Controller;

use Doctrine\ORM\Query\ResultSetMapping;
use App\UserdirectoryBundle\Controller\LoggerController;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Entity\Logger;
use App\UserdirectoryBundle\Form\LoggerType;

/**
 * Logger controller.
 *
 * @Route("/event-log")
 */
class ResAppLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="resapp_logger", methods={"GET"})
     * @Template("AppResAppBundle/Logger/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }
//        if( false == $this->get('security.authorization_checker')->isGranted("read","ResidencyApplication") ){
//            return $this->redirect( $this->generateUrl('resapp-nopermission') );
//        }

        //TODO: add residency type filtering for each object:
        //1) get residency type useing ObjectType and ObjectId
        //2) keep only objects with residency type equal to a residency type of the user's role

        $params = array(
            'sitename'=>$this->getParameter('resapp.sitename')
        );
        return $this->listLogger($params,$request);
    }


    /**
     * Filter by Object Type "ResidencyApplication" and Object ID
     *
     * @Route("/application-log/{id}", name="resapp_application_log", methods={"GET"})
     * @Template("AppResAppBundle/Logger/index.html.twig")
     */
    public function applicationLogAction(Request $request,$id) {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

//        if( false == $this->get('security.authorization_checker')->isGranted("read","ResidencyApplication") ){
//            return $this->redirect( $this->generateUrl('resapp-nopermission') );
//        }

        $em = $this->getDoctrine()->getManager();

        $resApp = $em->getRepository('AppResAppBundle:ResidencyApplication')->find($id);
        if( !$resApp ) {
            throw $this->createNotFoundException('Unable to find Residency Application by id='.$id);
        }

        if( false == $this->get('security.authorization_checker')->isGranted("read",$resApp) ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $objectType = $em->getRepository('AppUserdirectoryBundle:EventObjectTypeList')->findOneByName("ResidencyApplication");
        if( !$objectType ) {
            throw $this->createNotFoundException('Unable to find EventObjectTypeList by name='."ResidencyApplication");
        }

        return $this->redirect($this->generateUrl(
            'resapp_event-log-per-object_log',
            array(
                'filter[objectType][]' => $objectType->getId(),
                'filter[objectId]' => $id)
            )
        );
    }

    /**
     * Filter by Object Type "ResidencyApplication" and Object ID
     *
     * @Route("/event-log-per-object/", name="resapp_event-log-per-object_log", methods={"GET"})
     * @Template("AppResAppBundle/Logger/index.html.twig")
     */
    public function applicationPerObjectLogAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $params = array(
            'sitename' => $this->getParameter('resapp.sitename'),
//            'hideObjectType' => true,
//            'hideObjectId' => true,
//            'hideIp' => true,
//            'hideRoles' => true,
            //'hideId' => true
        );
        $loggerFormParams = $this->listLogger($params,$request);

        ///////////// make sure objectTypes is set /////////////
        $objectTypes = array();
        $objectId = null;

        $filter = $request->query->get('filter');

        if( count($filter) > 0 ) {
            $objectTypes = $filter['objectType'];
            $objectId = $filter['objectId'];
        }

        if( $objectId == null ) {
            throw $this->createNotFoundException('Activity Log resapp id is not provided');
        }

        if( count($objectTypes) == 0 ) {
            $objectType = $em->getRepository('AppUserdirectoryBundle:EventObjectTypeList')->findOneByName("ResidencyApplication");
            if( !$objectType ) {
                throw $this->createNotFoundException('Unable to find EventObjectTypeList by name='."ResidencyApplication");
            }
            //add eventTypes and users
            return $this->redirect($this->generateUrl('resapp_event-log-per-object_log',
                array(
                    'filter[objectType][]' => $objectType->getId(),
                    'filter[objectId]' => $objectId,
                )
            ));
        }
        ///////////// EOF make sure eventTypes and users are set /////////////


        $resApp = $em->getRepository('AppResAppBundle:ResidencyApplication')->find($objectId);
        if( !$resApp ) {
            throw $this->createNotFoundException('Unable to find Residency Application by id='.$objectId);
        }

        if( false == $this->get('security.authorization_checker')->isGranted("read",$resApp) ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }


        $loggerFormParams['hideUserAgent'] = true;
        $loggerFormParams['hideWidth'] = true;
        $loggerFormParams['hideHeight'] = true;
        $loggerFormParams['hideADServerResponse'] = true;

        $loggerFormParams['hideIp'] = true;
        $loggerFormParams['hideRoles'] = true;
        $loggerFormParams['hideId'] = true;         //Event ID
        $loggerFormParams['hideObjectType'] = true;
        $loggerFormParams['hideObjectId'] = true;

        //get title postfix
        $filterform = $loggerFormParams['filterform'];
        $objectTypes = $filterform['objectType']->getData();
        $objectId = $filterform['objectId']->getData();

        $em = $this->getDoctrine()->getManager();
        $objectType = $em->getRepository('AppUserdirectoryBundle:EventObjectTypeList')->find($objectTypes[0]);

        //Camel Case
        $objectTypeArr = preg_split('/(?=[A-Z])/',$objectType);
        $objectType = implode(' ', $objectTypeArr);

        //$loggerFormParams['titlePostfix'] = " for ".$objectType.": ".$objectId;//for ResidencyApplication: 162
        //Event Log showing 1 matching "Generate Accession Deidentifier ID" event(s) for user: firstname lastname - cwid
        //$loggerFormParams['titlePostfix'] = " matching \"".$eventType."\" event(s) for user: ".$user;
        $eventlogTitle = $this->getParameter('eventlog_title');
        if( $loggerFormParams['filtered'] ) {
            $loggerFormParams['eventLogTitle'] = $eventlogTitle . " showing " . count($loggerFormParams['pagination']) . " matching event(s)".
                " for ".$objectType.": ".$objectId;
        }

        return $loggerFormParams;
    }

    //filter ResidencyApplication objects:
    // select loggers where entityName is not ResidencyApplication or
    // entityName is ResidencyApplication and ResidencyApplication object is the same as user's residency type
//    public function addCustomDql($dql) {
//
//        //show all for admin
//        if( $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_ADMIN') ) {
//            $dql->select('logger');
//            return $dql;
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        //1) get user's role's residency types
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $roleObjects = $em->getRepository('AppUserdirectoryBundle:User')->findUserRolesBySiteAndPartialRoleName($user, 'resapp', "ROLE_RESAPP_");
//        $residencyTypes = array();
//        foreach ($roleObjects as $roleObject) {
//            if ($roleObject->getResidencyTrack()) {
//                $residencyTypes[] = $roleObject->getResidencyTrack()->getId() . "";  //$roleObject->getResidencyTrack()."";
//                //echo "role add=" . $roleObject->getResidencyTrack()->getId() . ":" . $roleObject->getResidencyTrack()->getName() . "<br>";
//            }
//        }
//        //echo "count=" . count($residencyTypes) . "<br>";
//
//        //2) subquery to get a residency application object with logger.entityId and residencyTrack in the $residencyTypes array
//        $subquery = $em->createQueryBuilder()
//            ->select('resapp.id')
//            ->from('AppResAppBundle:ResidencyApplication', 'resapp')
//            ->leftJoin('resapp.residencyTrack','residencyTrack')
//            ->where('CAST(resapp.id AS TEXT) = logger.entityId AND residencyTrack.id IN('.implode(",", $residencyTypes).')') //AND residencyTrack.id IN(37)
//            ->getDQL();
//        $subquery = '('.$subquery.')';
//
//        //3) main query to get logger objects, where use $subquery (residency application object)
//        //$query = $em->createQueryBuilder();
//        //$dql->from('AppUserdirectoryBundle:Logger', 'logger');
//
//        $dql->select('logger');
//        $entityName = 'ResidencyApplication';
//
//        //filter ResidencyApplication objects:
//        // select loggers where entityName is not ResidencyApplication or
//        // entityName is ResidencyApplication and ResidencyApplication object is the same as user's residency type
//        $dql->andWhere("logger.entityName != '".$entityName."' OR ( logger.entityName = '".$entityName."' AND logger.entityId=".$subquery.")");
//
//        //$query->andWhere("logger.entityName = '".$entityName."' AND logger.entityId=".$subquery);
//        //$query->andWhere("logger.entityName IS NULL OR (logger.entityName='ResidencyApplication' AND loggerEntity.id IS NOT NULL)");
//
//        return $dql;
//    }



}
