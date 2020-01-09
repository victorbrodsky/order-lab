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

namespace App\TranslationalResearchBundle\Controller;


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
class TranslationalResearchLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="translationalresearch_logger")
     * @Method("GET")
     * @Template("AppTranslationalResearchBundle/Logger/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_TRANSRES_ADMIN") ){
            return $this->redirect( $this->generateUrl('translationalresearch-nopermission') );
        }

		$params = array('sitename'=>$this->container->getParameter('translationalresearch.sitename'));
        $loggerFormParams = $this->listLogger($params,$request);

        return $loggerFormParams;
    }


    /**
     * @Route("/user/{id}/all", name="translationalresearch_logger_user_all")
     * @Method("GET")
     * @Template("AppTranslationalResearchBundle/Logger/index.html.twig")
     */
    public function getAuditLogAllAction(Request $request)
    {
        $postData = $request->get('postData');
        $userid = $request->get('id');

        $entityName = 'User';

        $params = array(
            'sitename'=>$this->container->getParameter('translationalresearch.sitename'),
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
//     * @Route("/generation-log/", name="translationalresearch_generation_log")
//     * @Method("GET")
//     * @Template("AppTranslationalResearchBundle/Logger/index.html.twig")
//     */
//    public function generationLogAction(Request $request)
//    {
//
//    }


//    /**
//     * Generation Log with eventTypes = "Generate Vacation Request" and users = current user id
//     *
//     * @Route("/event-log-per-user-per-event-type/", name="translationalresearch_my_generation_log")
//     * @Method("GET")
//     * @Template("AppTranslationalResearchBundle/Logger/index.html.twig")
//     */
//    public function myGenerationLogAction(Request $request)
//    {
//
//    }

    /**
     *
     * @Route("/event-log-per-object/", name="translationalresearch_event-log-per-object_log")
     * @Method("GET")
     * @Template("AppTranslationalResearchBundle/Logger/index.html.twig")
     */
    public function transresEventLogPerObjectAction(Request $request)
    {
        if (false == $this->get('security.authorization_checker')->isGranted("ROLE_TRANSRES_ADMIN")) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $params = array(
            'sitename' => $this->container->getParameter('translationalresearch.sitename'),
            //'objectType' => $objectType,
            //'objectId' => $objectId
            //'disabled' => true
            //'disabledObjectType' => true,
            //'disabledObjectId' => true
        );

        $loggerFormParams = $this->listLogger($params,$request);

        $filterform = $loggerFormParams['filterform'];
        $objectTypes = $filterform['objectType']->getData();
        $objectId = $filterform['objectId']->getData();
        //echo "objectId=".$objectId."<br>";

        if( count($objectTypes) > 0 ) {
            $objectType = $objectTypes[0];
        } else {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "Object Type is not defined."
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //permission: check if user has permission to view the specified object
        $em = $this->getDoctrine()->getManager();

        $objectNamespace = "App\\TranslationalResearchBundle\\Entity";

        //App\UserdirectoryBundle\Entity
        $objectNamespaceArr = explode("\\",$objectNamespace);
        $objectNamespaceClean = $objectNamespaceArr[0].$objectNamespaceArr[1];

        $objectName = $em->getRepository('AppUserdirectoryBundle:EventObjectTypeList')->find($objectType);
        if( !$objectName ) {
            throw $this->createNotFoundException('Unable to find EventObjectTypeList by objectType id='.$objectType);
        }

        //echo "objectName=".$objectName."<br>";
        //if( $objectName != "Project" ) {
        //    return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        //}

        $subjectEntity = $em->getRepository($objectNamespaceClean.':'.$objectName)->find($objectId);
        if( !$subjectEntity ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "Object not found by ID ".$objectId
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }


        $transresUtil = $this->container->get('transres_util');

        if( method_exists($subjectEntity, "getProjectSpecialty") ) {
            if( $transresUtil->isUserAllowedSpecialtyObject($subjectEntity->getProjectSpecialty()) === false ) {
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    "You don't have a permission to access the ".$subjectEntity->getProjectSpecialty()." project specialty"
                );
                return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            }
        }

        //$builder = $filterform->getBuilder();

        //$objectTypes = $filterform['objectType']->getData();
        //$objectId = $filterform['objectId']->getData();

        $loggerFormParams['hideObjectId'] = true;

        return $loggerFormParams;
    }

}
