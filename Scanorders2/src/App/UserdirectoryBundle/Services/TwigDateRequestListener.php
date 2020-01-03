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

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 3/19/14
 * Time: 3:36 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Services;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

//Twig listener to modify timezone as per http://stackoverflow.com/questions/9886058/how-can-i-set-the-default-date-format-for-twig-templates-in-symfony2

class TwigDateRequestListener {

    protected $twig;
    protected $secTokenStorage;
    protected $defaultTimeZone;

    function __construct(\Twig_Environment $twig, $secTokenStorage, $defaultTimeZone = null) {
        $this->twig = $twig;
        $this->secTokenStorage = $secTokenStorage;
        $this->defaultTimeZone = $defaultTimeZone;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        //$this->twig->getExtension('core')->setDateFormat('Y-m-d', '%d days');

        $user = null;
        $timezone = $this->defaultTimeZone;

        if( $this->secTokenStorage->getToken() ) {
            $user = $this->secTokenStorage->getToken()->getUser();
        }

        if( $user && is_object($user) && $user->getPreferences()->getTimezone() ) {
            $timezone = $user->getPreferences()->getTimezone();
        }

        //$this->twig->getExtension('core')->setTimezone($timezone);
        $this->twig->getExtension('Twig_Extension_Core')->setTimezone($timezone);
    }

}