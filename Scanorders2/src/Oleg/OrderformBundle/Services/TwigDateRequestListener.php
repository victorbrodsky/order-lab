<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 3/19/14
 * Time: 3:36 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Services;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContext;

//Twig listener to modify timezone as per http://stackoverflow.com/questions/9886058/how-can-i-set-the-default-date-format-for-twig-templates-in-symfony2

class TwigDateRequestListener {

    protected $twig;
    protected $sc;
    protected $defaultTimeZone;

    function __construct(\Twig_Environment $twig, SecurityContext $sc, $defaultTimeZone = null) {
        $this->twig = $twig;
        $this->sc = $sc;
        $this->defaultTimeZone = $defaultTimeZone;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        //$this->twig->getExtension('core')->setDateFormat('Y-m-d', '%d days');

        $user = null;
        $timezone = $this->defaultTimeZone;

        if( $this->sc->getToken() ) {
            $user = $this->sc->getToken()->getUser();
        }

        if( $user && is_object($user) && $user->getTimezone() ) {
            $timezone = $user->getTimezone();
        }

        $this->twig->getExtension('core')->setTimezone($timezone);

    }

}