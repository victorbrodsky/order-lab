<?php

namespace Oleg\FellAppBundle\Controller;

use Oleg\UserdirectoryBundle\Controller\LoggerController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\UserdirectoryBundle\Entity\Logger;
use Oleg\UserdirectoryBundle\Form\LoggerType;

/**
 * Logger controller.
 *
 * @Route("/event-log")
 */
class FellAppLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="fellapp_logger")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Logger:index.html.twig")
     */
    public function indexAction()
    {
        $params = array(
            'sitename'=>$this->container->getParameter('fellapp.sitename')
        );
        return $this->listLogger($params);
    }


}
