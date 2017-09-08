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

namespace Oleg\TranslationalResearchBundle\Controller;


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
class TranslationalResearchLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="translationalresearch_logger")
     * @Method("GET")
     * @Template("OlegTranslationalResearchBundle:Logger:index.html.twig")
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
     * @Template("OlegTranslationalResearchBundle:Logger:index.html.twig")
     */
    public function getAuditLogAllAction(Request $request)
    {
        $postData = $request->get('postData');
        $userid = $request->get('id');

        $entityName = 'User';

        $params = array(
            'sitename'=>$this->container->getParameter('translationalresearch.sitename'),
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
//     * @Route("/generation-log/", name="translationalresearch_generation_log")
//     * @Method("GET")
//     * @Template("OlegTranslationalResearchBundle:Logger:index.html.twig")
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
//     * @Template("OlegTranslationalResearchBundle:Logger:index.html.twig")
//     */
//    public function myGenerationLogAction(Request $request)
//    {
//
//    }

}
