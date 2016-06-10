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


//    /**
//     * Generation Log with eventTypes = "Generate Vacation Request" and users = current user id
//     *
//     * @Route("/event-log-per-user-per-event-type/", name="calllog_my_generation_log")
//     * @Method("GET")
//     * @Template("OlegCallLogBundle:Logger:index.html.twig")
//     */
//    public function myGenerationLogAction(Request $request)
//    {
//
//    }

}
