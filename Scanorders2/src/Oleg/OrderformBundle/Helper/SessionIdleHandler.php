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

use Oleg\UserdirectoryBundle\Util\UserUtil;

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

        if( HttpKernelInterface::MASTER_REQUEST != $event->getRequestType() ) {
            return;
        }

        //*************** set url for redirection ***************//
        //$this->setSessionLastRoute( $event );
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


    //http://www.fractalizer.ru/frpost_658/symfony2-how-redirect-user-to-a-previous-page-correctly/
    public function setSessionLastRoute( $event ) {

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
        }
        //unset($routeParams['_route']);

        $routeData = array('name' => $routeName, 'params' => $routeParams);

        //Skipping duplicates, logins and logout
        $thisRoute = $session->get('this_route', array());

        $pos = strpos( $routeName, "scan-order" );
        if( $pos === false ) {
            //$dontSetRedirect++;
        }

        if(
            //$thisRoute == $routeData['name'] ||
            $routeName == 'login' ||
            //$routeName == 'scan-order-nopermission' ||
            $routeName == 'scan_setloginvisit' ||
            $routeName == 'employees_setloginvisit' ||
            $routeName == 'logout' ||
            $routeName == 'getmaxidletime' ||
            $routeName == '_wdt'
        ) {
            $dontSetRedirect++;
        }

        if( $dontSetRedirect == 0 ) {
            if( $session->get('last_route_arr') && count($session->get('last_route_arr')) > 0 ) {
                $routeNameArr = $session->get('last_route_arr');
            } else {
                $routeNameArr = array();
                $session->set('last_route_arr',$routeNameArr);
            }
            $target_path = $session->get('_security.aperio_ldap_firewall.target_path');
            $routeNameArr[] =  $routeName;
            $session->set('last_route', $routeName);
            $session->set('this_route', $routeData);
            $session->set('full_url', $fullUrl);
            $session->set('last_route_arr', $routeNameArr);
            $session->set('target_path', $target_path);
            //echo "set session rout=".$routeName."<br>";
        }
//        echo "<br> kernel routeName=".$routeName."<br>";
//        $referer = $request->headers->get('referer');
//        echo "referer=".$referer."<br>";
//        $referer_url = $session->get('_security.aperio_ldap_firewall.target_path');
//        echo "referer_url=".$referer_url."<br>";
//        print_r($session);
//        exit();
    }


}