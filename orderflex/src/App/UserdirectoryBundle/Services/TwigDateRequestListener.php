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
use Symfony\Bundle\SecurityBundle\Security;
use App\UserdirectoryBundle\Util\UserServiceUtil;
use Twig\Environment;

//Twig listener to modify timezone as per http://stackoverflow.com/questions/9886058/how-can-i-set-the-default-date-format-for-twig-templates-in-symfony2

class TwigDateRequestListener {

    protected $twig;
    protected $defaultTimeZone;
    protected $security;
    protected $userServiceUtil;

    function __construct(Environment $twig, $defaultTimeZone, Security $security, UserServiceUtil $userServiceUtil) {
        $this->twig = $twig;
        $this->defaultTimeZone = $defaultTimeZone;
        $this->security = $security;
        $this->userServiceUtil = $userServiceUtil;
    }

    public function onKernelRequest(RequestEvent $event) {
        //$this->twig->getExtension('core')->setDateFormat('Y-m-d', '%d days');

        //resolve effective timezone: SiteParameters::instanceTimeZone takes precedence over
        //the current user's UserPreferences::timezone, falling back to the configured default
        $timezone = $this->userServiceUtil->getEffectiveTimezone();
        if( !$timezone ) {
            $timezone = $this->defaultTimeZone;
        }
        //echo "timezone=$timezone <br>";

        //IMPORTANT: do NOT call date_default_timezone_set($timezone) here with the resolved
        //per-user/per-instance timezone. All entities store their timestamps via
        //new \DateTime() in #[ORM\PrePersist]/#[ORM\PreUpdate] callbacks (e.g. Logger::setCreationdate()),
        //which use PHP's CURRENT default timezone at the moment of write AND Doctrine also uses
        //the current default timezone to interpret raw DB datetime strings back into DateTime
        //objects on hydration (no explicit timezone is stored in the "timestamp without time zone"
        //columns). If the default timezone varies per-request based on the viewing/acting user, both
        //writes and reads become inconsistent, corrupting stored timestamps and shifting displayed
        //times unpredictably (e.g. a login recorded at 9:10am New York time displaying as 13:10 to a
        //different viewer). PHP's default timezone must stay fixed at UTC always, so all timestamps
        //are consistently stored/read as UTC. The resolved effective timezone below is only applied
        //to Twig's date filter, which converts the (always-UTC) DateTime to the display timezone at
        //render time without touching the underlying value.
        date_default_timezone_set('UTC');

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