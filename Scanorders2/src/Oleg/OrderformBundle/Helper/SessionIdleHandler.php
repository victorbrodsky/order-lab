<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/7/14
 * Time: 3:03 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Helper;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oleg\OrderformBundle\Helper\UserUtil;

//This handle will (independently from JS) verify if max idle time out is reached and logout user on the first page redirect or reload

class SessionIdleHandler
{

    protected $session;
    protected $securityContext;
    protected $router;
    protected $maxIdleTime;
    protected $em;

    public function __construct(SessionInterface $session, SecurityContextInterface $securityContext, RouterInterface $router, $em )
    {
        $this->session = $session;
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->em = $em;

        $userUtil = new UserUtil();
        $this->maxIdleTime = $userUtil->getMaxIdleTime($this->em);
    }

    public function onKernelRequest(GetResponseEvent $event)
    {

        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        if( $this->maxIdleTime > 0 ) {

            $this->session->start();
            $lapse = time() - $this->session->getMetadataBag()->getLastUsed();

            //$msg = "'lapse=".$lapse.", max idle time=".$this->maxIdleTime."'";
            //echo $msg;
            //exit();

            if ($lapse > $this->maxIdleTime) {

                $event->setResponse(new RedirectResponse($this->router->generate('logout'))); //idlelogout

            }
        }
    }


}