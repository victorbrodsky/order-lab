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
// src/Twig/SiteExtension.php

namespace App\CrnBundle\Controller;

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SiteExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
        new TwigFunction('site_base_template', [$this, 'getBaseTemplate']),
        ];
    }
    
    public function getBaseTemplate(string $sitename): string
    {
        return match ($sitename) {
            'employees' => 'AppUserdirectoryBundle/Default/base.html.twig',
            'fellapp' => 'AppFellAppBundle/Default/base.html.twig',
            'resapp' => 'AppResAppBundle/Default/base.html.twig',
            'deidentifier' => 'AppDeidentifierBundle/Default/base.html.twig',
            'scan' => 'AppOrderformBundle/Default/base.html.twig',
            'vacreq' => 'AppVacReqBundle/Default/base.html.twig',
            'crn' => 'AppCrnBundle/Default/base.html.twig',
            'calllog' => 'AppCallLogBundle/Default/base.html.twig',
            'translationalresearch' => 'AppTranslationalResearchBundle/Default/base.html.twig',
            'dashboard' => 'AppDashboardBundle/Default/base.html.twig',
            //CTP
            'ctp' => 'AppCtpBundle/Default/base.html.twig',
            'tma' => 'AppCtpTmaBundle/Default/base.html.twig',
            'cohortg' => 'AppCtpCohortgBundle/Default/base.html.twig',
            'regulatoryt' => 'AppCtpRegulatorytBundle/Default/base.html.twig',
            'spore' => 'AppCtpSporeBundle/Default/base.html.twig',

            default => 'AppUserdirectoryBundle/Default/base.html.twig',
        };
    }
}
