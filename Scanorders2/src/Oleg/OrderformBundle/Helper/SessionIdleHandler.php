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

        //*************** set url for redirection ***************//
        //http://www.fractalizer.ru/frpost_658/symfony2-how-redirect-user-to-a-previous-page-correctly/
        $dontSetRedirect = 0;

        /** @var \Symfony\Component\HttpFoundation\Request $request  */
        $request = $event->getRequest();
        /** @var \Symfony\Component\HttpFoundation\Session $session  */
        $session = $request->getSession();

        $routeParams = $this->router->match($request->getPathInfo());
        //print_r($routeParams);

        $fullUrl = $_SERVER['REQUEST_URI'];

        $routeName = $routeParams['_route'];
        //echo "<br> kernel routeName=".$routeName."<br>";

        if( $routeName[0] == '_' ) {
            $dontSetRedirect++;
            //echo "<br> kernel routeName=".$routeName."<br>";
            //echo "routeName[0]= ".$routeName[0];
            //exit();
        }
        //unset($routeParams['_route']);

        $routeData = array('name' => $routeName, 'params' => $routeParams);

        //Skipping duplicates, logins and logout
        $thisRoute = $session->get('this_route', array());

        $pos = strpos( $routeName, "scan-order" );
        if( $pos === false ) {
            //$dontSetRedirect++;
        }

        if( $thisRoute == $routeData['name'] ||
            $routeName == 'login' ||
            $routeName == 'setloginvisit' ||
            $routeName == 'logout' ||
            $routeName == 'getmaxidletime' ||
            $routeName == '_wdt'
        ) {
            $dontSetRedirect++;
        }

        if( $dontSetRedirect == 0 ) {
            //$session->set('last_route', $thisRoute);
            $session->set('last_route', $routeName);
            $session->set('this_route', $routeData);
            $session->set('full_url', $fullUrl);
            //echo "set session rout=".$routeName."<br>";
        }
        //echo "<br> kernel routeName=".$routeName."<br>";
        //print_r($session);
        //exit();
        //*************** end of set url for redirection ***************//

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