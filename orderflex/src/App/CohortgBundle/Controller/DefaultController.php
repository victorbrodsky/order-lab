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

namespace App\CohortgBundle\Controller;





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
    #[Route(path: '/about', name: 'cohortg_about_page')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function aboutAction(Request $request)
    {
        return array('sitename' => $this->getParameter('cohortg.sitename'));
    }

    #[Route(path: '/', name: 'cohortg_home', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/home.html.twig')]
    public function indexAction( Request $request ) {

        $title = 'Cohort Generator';

        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/publications', name: 'cohortg_publications', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/publications.html.twig')]
    public function publicationsAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/new-project-inquiry', name: 'cohortg_new-project-inquiry', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/new-project-inquiry.html.twig')]
    public function newProjectInquiryAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/people', name: 'cohortg_people', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/people.html.twig')]
    public function peopleAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/applications', name: 'cohortg_applications', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/dashboard.html.twig')]
    public function applicationsAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }
    
    #[Route(path: '/histocore-and-ihc-lab', name: 'cohortg_histocore_and_ihc_lab', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/histocore-and-ihc-lab.html.twig')]
    public function histocoreAndIhcLabAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/misi-lab', name: 'cohortg_misi-lab', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/misi-lab.html.twig')]
    public function misiLabAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/experimental-cellular-therapy-lab', name: 'cohortg_ect', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/ect.html.twig')]
    public function ectAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/genomics-lab', name: 'cohortg_genomiclab', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/genomics-lab.html.twig')]
    public function genomicLabAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/clinical-pathology-research-lab', name: 'cohortg_cpresearchlab', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/cp-research-lab.html.twig')]
    public function cpResearchLabAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/computational-pathology-lab', name: 'cohortg_comppathlab', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/comppathlab.html.twig')]
    public function expCellLabAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/project-requests', name: 'cohortg_project_requests', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/project_requests.html.twig')]
    public function projectRequestsAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/cohort-generator', name: 'cohortg_dashboard_cohortg', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/dashboard-cohort-generator.html.twig')]
    public function cohortgAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/cohort-generator', name: 'cohortg_dashboard_cohort_generator', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/dashboard-cohort-generator.html.twig')]
    public function cohortGeneratorAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/regulatory-templates', name: 'cohortg_dashboard_regulatory_templates', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/regulatory-templates.html.twig')]
    public function regulatoryTemplatesAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/publication-manager', name: 'cohortg_dashboard_publications', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/empty.html.twig')]
    public function publicationsManagerAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/prostate-cancer-research-data-explorer', name: 'cohortg_dashboard_spore', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/empty.html.twig')]
    public function dashboardSporeAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/experimental-cellular-therapy-lab/investigator-engagement-guide', name: 'cohortg_investigator_engagement_guide', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/empty.html.twig')]
    public function investigatorEngagementGuideAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }
    
    #[Route(path: '/experimental-cellular-therapy-lab/irb-ready-workflow-summary', name: 'cohortg_irb_ready_workflow_summary', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/empty.html.twig')]
    public function irbWorkflowSummaryAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }
    
    #[Route(path: '/experimental-cellular-therapy-lab/service-menu', name: 'cohortg_ctem_service', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/empty.html.twig')]
    public function ctemServiceAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/histocore-and-ihc-lab/service-menu', name: 'cohortg_service_menu', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/empty.html.twig')]
    public function serviceMenuAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/histocore-and-ihc-lab/sample-submission-checklist', name: 'cohortg_sample_submission_checklist', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/empty.html.twig')]
    public function sampleSubmissionChecklistAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/histocore-and-ihc-lab/publications', name: 'cohortg_histocore_publications', methods: ['GET'])]
    #[Template('AppCohortgBundle/Home/empty.html.twig')]
    public function histocorePublicationsAction( Request $request ) {
        $title = 'Cohort Generator';
        return array(
            'title' => $title,
        );
    }

    public function homeAction( Request $request, RouterInterface $router, string $page=null ): Response
    {
        if( $request->get('_route') == 'cohortg_index' ) {
            return $this->redirect( $this->generateUrl('cohortg_home') );
        }

        $base = $this->getParameter('kernel.project_dir') .
            '/public/orderassets/AppCohortgBundle/cohortg_site/localhost_3000/';

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
            'href="/cohort-generator/$1"',
            $html
        );

        $basePath = rtrim($request->getBasePath(), '/');
        $prefix   = $basePath . '/cohort-generator';

        $html = preg_replace(
            '/href="([^":]+)\.html"/i',
            'href="' . $prefix . '/$1"',
            $html
        );

        $html = preg_replace(
            '#href="/?cohort-generator/([^"]*)"#i',
            'href="' . $prefix . '/$1"',
            $html
        );

        $homeUrl = $router->generate(
            'main_common_home',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $homeUrl = '<a href="'.$homeUrl.'">Home</a>';

        if( $this->getUser() ) {
            $loginUrl = $router->generate(
                'employees_logout',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $loginUrl = '<a href="'.$loginUrl.'">Logout</a>';
        } else {
            $loginUrl = $router->generate(
                'cohortg_login',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $loginUrl = '<a href="' . $loginUrl . '">Login</a>';
        }

        $html = str_replace(
            'Weill Cornell Medicine · Cohort Generator',
            'Weill Cornell Medicine'.' · Cohort Generator · '.$homeUrl . ' · ' . $loginUrl,
            $html
        );

        $html = preg_replace(
            '/(src|href)="(css|js|images|assets)\//i',
            '$1="/cohortg_site/localhost_3000/$2/',
            $html
        );

        if(1) {
            $html = preg_replace_callback(
                '/_next\/image\?url=%2Fimages%2F([^"&]+).*?"/i',
                function ($matches) use ($findRealFile) {
                    $real = $findRealFile($matches[1]);
                    return '/cohortg_site/localhost_3000/_next/' . $real . '"';
                },
                $html
            );

            $html = preg_replace_callback(
                '/src="_next\/([^"?]+)\?url=%2Fimages%2F([^"&]+).*?"/i',
                function ($matches) use ($findRealFile) {
                    $real = $findRealFile($matches[2]);
                    return 'src="/cohortg_site/localhost_3000/_next/' . $real . '"';
                },
                $html
            );

            $html = preg_replace(
                '/(src|srcset)="_next\//i',
                '$1="/cohortg_site/localhost_3000/_next/',
                $html
            );
        }

        $html = preg_replace(
            '/(src|srcset)="\/\//i',
            '$1="/',
            $html
        );

        return $this->render('AppCohortgBundle/Default/index.html.twig', [
            'site_html' => $html,
        ]);
    }
}
