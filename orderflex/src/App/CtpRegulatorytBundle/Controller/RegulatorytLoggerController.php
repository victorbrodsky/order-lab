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

namespace App\CtpRegulatorytBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Entity\Logger;
use App\UserdirectoryBundle\Form\LoggerType;

use App\UserdirectoryBundle\Controller\LoggerController;

/**
 * Logger controller.
 */
#[Route(path: '/event-log')]
class RegulatorytLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     */
    #[Route(path: '/', name: 'regulatoryt_logger', methods: ['GET'])]
    #[Template('AppCtpRegulatorytBundle/Logger/index.html.twig')]
    public function indexAction(Request $request)
    {
        if( false == $this->isGranted("ROLE_REGULATORYT_ADMIN") ){
            return $this->redirect( $this->generateUrl('regulatoryt-nopermission') );
        }

		$params = array('sitename'=>$this->getParameter('regulatoryt.sitename'));
        $loggerFormParams = $this->listLogger($params,$request);

        return $loggerFormParams;
    }


    #[Route(path: '/user/{id}/all', name: 'regulatoryt_logger_user_all', methods: ['GET'])]
    #[Template('AppCtpRegulatorytBundle/Logger/index.html.twig')]
    public function getAuditLogAllAction(Request $request)
    {
        $postData = $request->attributes->get('postData', $request->query->get('postData', $request->request->get('postData')));
        $userid = $request->attributes->get('id');

        $entityName = 'User';

        $params = array(
            'sitename'=>$this->getParameter('regulatoryt.sitename'),
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

}
