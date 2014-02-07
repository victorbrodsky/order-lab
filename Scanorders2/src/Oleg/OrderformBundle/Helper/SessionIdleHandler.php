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

class SessionIdleHandler
{

    protected $session;
    protected $securityContext;
    protected $router;
    protected $maxIdleTime;

    public function __construct(SessionInterface $session, SecurityContextInterface $securityContext, RouterInterface $router, $maxIdleTime = 0)
    {
        $this->session = $session;
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->maxIdleTime = $maxIdleTime;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {

            return;
        }

        if ($this->maxIdleTime > 0) {

            $this->session->start();
            $lapse = time() - $this->session->getMetadataBag()->getLastUsed();

            //$msg = "'lapse=".$lapse.", max idle time=".$this->maxIdleTime."'";
            //echo "console.log(".$msg.")";

            if ($lapse > $this->maxIdleTime) {

                $this->securityContext->setToken(null);
                $this->session->getFlashBag()->set('info', 'You have been logged out due to inactivity.');

                // Change the route if you are not using FOSUserBundle.
                $event->setResponse(new RedirectResponse($this->router->generate('fos_user_security_login')));
            }
        }
    }

}