<?php

namespace Oleg\OrderformBundle\Controller;


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
class ScanLoggerController extends LoggerController
{

    /**
     * Lists all Logger entities.
     *
     * @Route("/", name="scan_logger")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Logger:index.html.twig")
     */
    public function indexAction()
    {
		$params = array('sitename'=>$this->container->getParameter('scan.sitename'));
        return $this->listLogger($params);
    }




}
