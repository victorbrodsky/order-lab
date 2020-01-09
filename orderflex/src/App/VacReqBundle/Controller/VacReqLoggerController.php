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

namespace App\VacReqBundle\Controller;


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
class VacReqLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="vacreq_logger")
     * @Method("GET")
     * @Template("AppVacReqBundle/Logger/index.html.twig")
     */
    public function indexAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted("ROLE_VACREQ_ADMIN") ){
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

		$params = array('sitename'=>$this->container->getParameter('vacreq.sitename'));
        $loggerFormParams = $this->listLogger($params,$request);

        return $loggerFormParams;
    }


    /**
     * @Route("/user/{id}/all", name="vacreq_logger_user_all")
     * @Method("GET")
     * @Template("AppVacReqBundle/Logger/index.html.twig")
     */
    public function getAuditLogAllAction(Request $request)
    {
        $postData = $request->get('postData');
        $userid = $request->get('id');

        $entityName = 'User';

        $params = array(
            'sitename'=>$this->container->getParameter('vacreq.sitename'),
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
//     * @Route("/generation-log/", name="vacreq_generation_log")
//     * @Method("GET")
//     * @Template("AppVacReqBundle/Logger/index.html.twig")
//     */
//    public function generationLogAction(Request $request)
//    {
//
//    }


//    /**
//     * Generation Log with eventTypes = "Generate Vacation Request" and users = current user id
//     *
//     * @Route("/event-log-per-user-per-event-type/", name="vacreq_my_generation_log")
//     * @Method("GET")
//     * @Template("AppVacReqBundle/Logger/index.html.twig")
//     */
//    public function myGenerationLogAction(Request $request)
//    {
//
//    }

}
