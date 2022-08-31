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
//use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

//Twig listener to modify timezone as per http://stackoverflow.com/questions/9886058/how-can-i-set-the-default-date-format-for-twig-templates-in-symfony2

class TwigDateRequestListener {

    protected $twig;
    protected $defaultTimeZone;
    protected $security;

    function __construct(Environment $twig, $defaultTimeZone, Security $security) {
        $this->twig = $twig;
        $this->defaultTimeZone = $defaultTimeZone;
        $this->security = $security;
    }

    public function onKernelRequest(RequestEvent $event) {
        //$this->twig->getExtension('core')->setDateFormat('Y-m-d', '%d days');

        $user = null;
        $timezone = $this->defaultTimeZone;
        //echo "default timezone=$timezone <br>";

        $user = $this->security->getUser();

        if( $user && is_object($user) && $user->getPreferences()->getTimezone() ) {
            $timezone = $user->getPreferences()->getTimezone();
        }

//        $extensions = $this->twig->getExtensions();
//        foreach($extensions as $name => $extension) {
//            //echo "$name => $extension <br>";
//            echo "name=[$name] <br>";
//            //echo "$extension <br>";
//            //dump($extension);
//        }
        //$twigExtension = $this->twig->getExtension('Twig_Extension_CoreExtension');
        //echo "twigExtension=$twigExtension<br>";
        //exit('111');

        //$this->twig->getExtension('Core')->setTimezone($timezone);
        //$this->twig->getExtension('Twig_Extension_Core')->setTimezone($timezone);
        //$this->twig->getExtension('CoreExtension')->setTimezone($timezone);

        //echo "user timezone=$timezone <br>";

        //https://twig.symfony.com/doc/1.x/filters/date.html
        $this->twig->getExtension('\Twig\Extension\CoreExtension')->setTimezone($timezone); //new twig
    }

}