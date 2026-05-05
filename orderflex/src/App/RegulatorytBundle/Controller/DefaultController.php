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

namespace App\RegulatorytBundle\Controller;

use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Entity\Roles;
use App\OrderformBundle\Entity\Message;
use App\UserdirectoryBundle\Entity\ObjectTypeText;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends OrderAbstractController
{
    #[Route(path: '/about', name: 'regulatoryt_about_page')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function aboutAction(Request $request)
    {
        return array('sitename' => $this->getParameter('regulatoryt.sitename'));
    }

    #[Route(path: '/', name: 'regulatoryt_home', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/home.html.twig')]
    public function indexAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/publications', name: 'regulatoryt_publications', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/publications.html.twig')]
    public function publicationsAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/new-project-inquiry', name: 'regulatoryt_new-project-inquiry', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/new-project-inquiry.html.twig')]
    public function newProjectInquiryAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/people', name: 'regulatoryt_people', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/people.html.twig')]
    public function peopleAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/applications', name: 'regulatoryt_applications', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/dashboard.html.twig')]
    public function applicationsAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }
    
    #[Route(path: '/histocore-and-ihc-lab', name: 'regulatoryt_histocore_and_ihc_lab', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/histocore-and-ihc-lab.html.twig')]
    public function histocoreAndIhcLabAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/misi-lab', name: 'regulatoryt_misi-lab', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/misi-lab.html.twig')]
    public function misiLabAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/experimental-cellular-therapy-lab', name: 'regulatoryt_ect', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/ect.html.twig')]
    public function ectAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/genomics-lab', name: 'regulatoryt_genomiclab', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/genomics-lab.html.twig')]
    public function genomicLabAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/clinical-pathology-research-lab', name: 'regulatoryt_cpresearchlab', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/cp-research-lab.html.twig')]
    public function cpResearchLabAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/computational-pathology-lab', name: 'regulatoryt_comppathlab', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/comppathlab.html.twig')]
    public function expCellLabAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/project-requests', name: 'regulatoryt_project_requests', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/project_requests.html.twig')]
    public function projectRequestsAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/tissue-microarrays', name: 'regulatoryt_dashboard_tma', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/dashboard-tma.html.twig')]
    public function tmaAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/cohort-generator', name: 'regulatoryt_dashboard_cohort_generator', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/dashboard-cohort-generator.html.twig')]
    public function cohortGeneratorAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/regulatory-templates', name: 'regulatoryt_dashboard_regulatory_templates', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/regulatory-templates.html.twig')]
    public function regulatoryTemplatesAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/publication-manager', name: 'regulatoryt_dashboard_publications', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/empty.html.twig')]
    public function publicationsManagerAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/prostate-cancer-research-data-explorer', name: 'regulatoryt_dashboard_spore', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/empty.html.twig')]
    public function dashboardSporeAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/experimental-cellular-therapy-lab/investigator-engagement-guide', name: 'regulatoryt_investigator_engagement_guide', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/empty.html.twig')]
    public function investigatorEngagementGuideAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }
    
    #[Route(path: '/experimental-cellular-therapy-lab/irb-ready-workflow-summary', name: 'regulatoryt_irb_ready_workflow_summary', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/empty.html.twig')]
    public function irbWorkflowSummaryAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }
    
    #[Route(path: '/experimental-cellular-therapy-lab/service-menu', name: 'regulatoryt_ctem_service', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/empty.html.twig')]
    public function ctemServiceAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/histocore-and-ihc-lab/service-menu', name: 'regulatoryt_service_menu', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/empty.html.twig')]
    public function serviceMenuAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/histocore-and-ihc-lab/sample-submission-checklist', name: 'regulatoryt_sample_submission_checklist', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/empty.html.twig')]
    public function sampleSubmissionChecklistAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    #[Route(path: '/histocore-and-ihc-lab/publications', name: 'regulatoryt_histocore_publications', methods: ['GET'])]
    #[Template('AppRegulatorytBundle/Home/empty.html.twig')]
    public function histocorePublicationsAction( Request $request ) {
        $title = 'Regulatory Templates';
        return array('title' => $title);
    }

    public function homeAction( Request $request, RouterInterface $router, string $page=null ): Response
    {
        if( $request->get('_route') == 'regulatoryt_index' ) {
            return $this->redirect( $this->generateUrl('regulatoryt_home') );
        }

        $base = $this->getParameter('kernel.project_dir') .
            '/public/orderassets/AppRegulatorytBundle/regulatoryt_site/localhost_3000/';

        if( !$page ) {
            $page = 'index';
        }

        $file = $base . $page . '.html';

        if (!file_exists($file)) {
            throw $this->createNotFoundException("Page not found: $page");
        }

        $html = file_get_contents($file);

        $findRealFile = function(string $basename) use ($base) {
            $folder = $base . '_next/';
            $files = scandir($folder);
            foreach ($files as $f) {
                if (str_starts_with($f, pathinfo($basename, PATHINFO_FILENAME))) {
                    return $f;
                }
            }
            return $basename;
        };

        $html = preg_replace(
            '/href="([^":]+)\.html"/i',
            'href="/regulatory-templates/$1"',
            $html
        );

        $basePath = rtrim($request->getBasePath(), '/');
        $prefix   = $basePath . '/regulatory-templates';

        $html = preg_replace(
            '/href="([^":]+)\.html"/i',
            'href="' . $prefix . '/$1"',
            $html
        );

        $html = preg_replace(
            '#href="/?regulatory-templates/([^"]*)"#i',
            'href="' . $prefix . '/$1"',
            $html
        );

        $homeUrl = $router->generate('main_common_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $homeUrl = '<a href="'.$homeUrl.'">Home</a>';

        if( $this->getUser() ) {
            $loginUrl = $router->generate('employees_logout', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $loginUrl = '<a href="'.$loginUrl.'">Logout</a>';
        } else {
            $loginUrl = $router->generate('regulatoryt_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $loginUrl = '<a href="' . $loginUrl . '">Login</a>';
        }

        $html = str_replace(
            'Weill Cornell Medicine · Regulatory Templates',
            'Weill Cornell Medicine'.' · Regulatory Templates · '.$homeUrl . ' · ' . $loginUrl,
            $html
        );

        $html = preg_replace(
            '/(src|href)="(css|js|images|assets)\//i',
            '$1="/regulatoryt_site/localhost_3000/$2/',
            $html
        );

        if(1) {
            $html = preg_replace_callback(
                '/_next\/image\?url=%2Fimages%2F([^"&]+).*?"/i',
                function ($matches) use ($findRealFile) {
                    $real = $findRealFile($matches[1]);
                    return '/regulatoryt_site/localhost_3000/_next/' . $real . '"';
                },
                $html
            );

            $html = preg_replace_callback(
                '/src="_next\/([^"?]+)\?url=%2Fimages%2F([^"&]+).*?"/i',
                function ($matches) use ($findRealFile) {
                    $real = $findRealFile($matches[2]);
                    return 'src="/regulatoryt_site/localhost_3000/_next/' . $real . '"';
                },
                $html
            );

            $html = preg_replace(
                '/(src|srcset)="_next\//i',
                '$1="/regulatoryt_site/localhost_3000/_next/',
                $html
            );
        }

        $html = preg_replace('/(src|srcset)="\/\//i', '$1="/', $html);

        return $this->render('AppRegulatorytBundle/Default/index.html.twig', [
            'site_html' => $html,
        ]);
    }
}
