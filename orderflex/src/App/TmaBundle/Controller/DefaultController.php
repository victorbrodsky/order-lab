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

namespace App\TmaBundle\Controller;





use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Entity\Roles; //process.py script: replaced namespace by ::class: added use line for classname=Roles
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
    #[Route(path: '/about', name: 'tma_about_page')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function aboutAction(Request $request)
    {
        return array('sitename' => $this->getParameter('tma.sitename'));
    }

    #[Route(path: '/', name: 'tma_home', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/home.html.twig')]
    public function indexAction( Request $request ) {

//        if( false == $this->isGranted('ROLE_TMA_USER') ){
//            return $this->redirect( $this->generateUrl('tma-nopermission') );
//        }

        $title = 'Tissue Microarrays';

        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/publications', name: 'tma_publications', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/publications.html.twig')]
    public function publicationsAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/new-project-inquiry', name: 'tma_new-project-inquiry', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/new-project-inquiry.html.twig')]
    public function newProjectInquiryAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/people', name: 'tma_people', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/people.html.twig')]
    public function peopleAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/applications', name: 'tma_applications', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/dashboard.html.twig')]
    public function applicationsAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }
    
    #[Route(path: '/histocore-and-ihc-lab', name: 'tma_histocore_and_ihc_lab', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/histocore-and-ihc-lab.html.twig')]
    public function histocoreAndIhcLabAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/misi-lab', name: 'tma_misi-lab', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/misi-lab.html.twig')]
    public function misiLabAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    //Exp Cell Therapy Lab
    #[Route(path: '/experimental-cellular-therapy-lab', name: 'tma_ect', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/ect.html.twig')]
    public function ectAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/genomics-lab', name: 'tma_genomiclab', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/genomics-lab.html.twig')]
    public function genomicLabAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    //CP Research Lab
    #[Route(path: '/clinical-pathology-research-lab', name: 'tma_cpresearchlab', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/cp-research-lab.html.twig')]
    public function cpResearchLabAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    //Comp Path Lab
    #[Route(path: '/computational-pathology-lab', name: 'tma_comppathlab', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/comppathlab.html.twig')]
    public function expCellLabAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    //Comp Path Lab
    #[Route(path: '/project-requests', name: 'tma_project_requests', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/project_requests.html.twig')]
    public function projectRequestsAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    //tma_dashboard_tma
    #[Route(path: '/tissue-microarrays', name: 'tma_dashboard_tma', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/dashboard-tma.html.twig')]
    public function tmaAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    //tma_dashboard_cohort_generator
    #[Route(path: '/cohort-generator', name: 'tma_dashboard_cohort_generator', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/dashboard-cohort-generator.html.twig')]
    public function cohortGeneratorAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    //tma_dashboard_regulatory_templates
    #[Route(path: '/regulatory-templates', name: 'tma_dashboard_regulatory_templates', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/regulatory-templates.html.twig')]
    public function regulatoryTemplatesAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    //tma_dashboard_publications
    #[Route(path: '/publication-manager', name: 'tma_dashboard_publications', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/empty.html.twig')]
    public function publicationsManagerAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    //tma_dashboard_spore
    #[Route(path: '/prostate-cancer-research-data-explorer', name: 'tma_dashboard_spore', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/empty.html.twig')]
    public function dashboardSporeAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    //Investigator engagement guide
    #[Route(path: '/experimental-cellular-therapy-lab/investigator-engagement-guide', name: 'tma_investigator_engagement_guide', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/empty.html.twig')]
    public function investigatorEngagementGuideAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }
    
    #[Route(path: '/experimental-cellular-therapy-lab/irb-ready-workflow-summary', name: 'tma_irb_ready_workflow_summary', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/empty.html.twig')]
    public function irbWorkflowSummaryAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }
    
    #[Route(path: '/experimental-cellular-therapy-lab/service-menu', name: 'tma_ctem_service', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/empty.html.twig')]
    public function ctemServiceAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/histocore-and-ihc-lab/service-menu', name: 'tma_service_menu', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/empty.html.twig')]
    public function serviceMenuAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/histocore-and-ihc-lab/sample-submission-checklist', name: 'tma_sample_submission_checklist', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/empty.html.twig')]
    public function sampleSubmissionChecklistAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/histocore-and-ihc-lab/publications', name: 'tma_histocore_publications', methods: ['GET'])]
    #[Template('AppTmaBundle/Home/empty.html.twig')]
    public function histocorePublicationsAction( Request $request ) {
        $title = 'Tissue Microarrays';
        return array(
            'title' => $title,
        );
    }

    //TODO:
    //HistoCore & IHC Lab - card does not have border
    //New Project - Initial Inquiry - form has blue surrounding except top - remove it
    //New Project - Initial Inquiry - change top color

//    //check for active access requests
//    public function getActiveAccessReq() {
//        if( !$this->isGranted('ROLE_TMA_ADMIN') ) {
//            return null;
//        }
//        $userSecUtil = $this->container->get('user_security_utility');
//        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->getParameter('tma.sitename'),AccessRequest::STATUS_ACTIVE);
//        return $accessreqs;
//    }


    //1) https://view-test.med.cornell.edu/tissue-microarrays
    //2) login only for dashboard
    //3) add login to footer
    //0) https://view.online/c/wcm/pathology/ -> enable https://view.online/c/wcm/pathology/tissue-microarrays
    //1) image Weill Cornell Medicine -> as link to $homeUrl
    //2) text footer Weill Cornell Medicine -> as link to https://weillcornell.org/
    //3) Tissue Microarrays -> home of Tissue Microarrays
    //7) External Collaboration / Project Inquiry activate the same behaviour as original site
    //8) http://localhost:3000/project-requests-public: change url to /tissue-microarrays/new-project-inquiry
    //9) change urls for 6 squares
    //10) http://localhost:3000/path2path-dashboard-login -> login -> http://localhost:3000/path2path-dashboard
    //#[Route('/{page}', name: 'tma_home', defaults: ['page' => 'index'])]
    //#[Route('/index', name: 'tma_index')]
    //#[Route('/{page}', name: 'tma_home')]
    public function homeAction( Request $request, RouterInterface $router, string $page=null ): Response
    {
        if( $request->get('_route') == 'tma_index' ) {
            return $this->redirect( $this->generateUrl('tma_home') );
        }

        //$base = $this->getParameter('kernel.project_dir') . '/public/tma_site/localhost_3000/';
        $base = $this->getParameter('kernel.project_dir') .
            //'/public/tma_site/localhost_3000/';
            '/public/orderassets/AppTmaBundle/tma_site/localhost_3000/';
//            '/tma_site/localhost_3000/';
//            '/src/App/TmaBundle/Util/tma_site/localhost_3000/';
//            '/src/templates/AppTmaBundle/tma_site/localhost_3000/';

        if( !$page ) {
            $page = 'index';
        }

        $file = $base . $page . '.html';
        //exit('$file='.$file);

        if (!file_exists($file)) {
            throw $this->createNotFoundException("Page not found: $page");
        }

        $html = file_get_contents($file);

        //
        // DYNAMIC PREFIX (no hardcoding)
        //
        // Example request path:
        //   /c/wcm/pathology/tissue-microarrays/people
        //
        // $page = "people"
        // Remove "/people" → prefix = "/c/wcm/pathology/tissue-microarrays"
        //
        //$path = $request->getPathInfo();
        //$prefix = rtrim(substr($path, 0, -strlen($page)), '/');

        //
        // Helper: find real file in _next folder
        //
        $findRealFile = function(string $basename) use ($base) {
            $folder = $base . '_next/';
            $files = scandir($folder);

            foreach ($files as $f) {
                if (str_starts_with($f, pathinfo($basename, PATHINFO_FILENAME))) {
                    return $f; // return first matching file
                }
            }

            return $basename; // fallback
        };

        //exit('$html='.$html);

        //
        // 1. Rewrite internal links
        //
        $html = preg_replace(
            '/href="([^":]+)\.html"/i',
            'href="/tissue-microarrays/$1"',
            $html
        );

        //
        // 1. Rewrite internal links using dynamic prefix
        //
        $basePath = rtrim($request->getBasePath(), '/');
        $prefix   = $basePath . '/tissue-microarrays';

        // Case A: "people.html"
        $html = preg_replace(
            '/href="([^":]+)\.html"/i',
            'href="' . $prefix . '/$1"',
            $html
        );

        // Case B: "/tissue-microarrays/people"
        $html = preg_replace(
            '#href="/?tissue-microarrays/([^"]*)"#i',
            'href="' . $prefix . '/$1"',
            $html
        );

        //modify footer
        $homeUrl = $router->generate(
            'main_common_home',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $homeUrl = '<a href="'.$homeUrl.'">Home</a>';

        //show login
        if( $this->getUser() ) {
            $loginUrl = $router->generate(
                'employees_logout',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $loginUrl = '<a href="'.$loginUrl.'">Logout</a>';
        } else {
            $loginUrl = $router->generate(
                'tma_login',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $loginUrl = '<a href="' . $loginUrl . '">Login</a>';
        }

//        $wcmLink = '<a href="weillcornell.org/">Weill Cornell Medicine</a>'; //weillcornell.org/
//        //Weill Cornell Medicine · Tissue Microarrays
        $html = str_replace(
            'Weill Cornell Medicine · Tissue Microarrays',
            'Weill Cornell Medicine'.' · Tissue Microarrays · '.$homeUrl . ' · ' . $loginUrl,
            $html
        );

//        //<img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto"> as link https://weillcornell.org/
//        $wcmUrl = '<a href="https://weillcornell.org/"><img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto"></a>';
//        $html = str_replace(
//            '<img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto">',
//            $wcmUrl,
//            $html
//        );

        //
        // 2. Rewrite CSS/JS paths
        //
        $html = preg_replace(
            '/(src|href)="(css|js|images|assets)\//i',
            '$1="/tma_site/localhost_3000/$2/',
            //'$1="/orderassets/AppTmaBundle/tma_site/localhost_3000/$2/',
            $html
        );

        if(1) {
            //C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex\public\orderassets\AppTmaBundle\tma_site\localhost_3000\faviconbcf9.ico
            //
            // 3. Rewrite Next.js optimized images
            //
            $html = preg_replace_callback(
                //'/_next\/image\?url=%2Fimages%2F([^"&]+).*?"/i',
                '/_next\/image\?url=%2Fimages%2F([^"&]+).*?"/i',
                function ($matches) use ($findRealFile) {
                    $real = $findRealFile($matches[1]);
                    return '/tma_site/localhost_3000/_next/' . $real . '"';
                    //return '/orderassets/AppTmaBundle/tma_site/localhost_3000/_next/' . $real . '"';
                },
                $html
            );

            //
            // 4. Rewrite fallback Next.js JPEGs
            //
            $html = preg_replace_callback(
                '/src="_next\/([^"?]+)\?url=%2Fimages%2F([^"&]+).*?"/i',
                function ($matches) use ($findRealFile) {
                    $real = $findRealFile($matches[2]);
                    return 'src="/tma_site/localhost_3000/_next/' . $real . '"';
                    //return 'src="/orderassets/AppTmaBundle/tma_site/localhost_3000/_next/' . $real . '"';
                },
                $html
            );

            //
            // 5. Rewrite any remaining _next/... paths
            //
            $html = preg_replace(
                '/(src|srcset)="_next\//i',
                '$1="/tma_site/localhost_3000/_next/',
                //'$1="/orderassets/AppTmaBundle/tma_site/localhost_3000/_next/',
                $html
            );
        }

        //
        // 6. Fix accidental leading double slashes
        //
        $html = preg_replace(
            '/(src|srcset)="\/\//i',
            '$1="/',
            $html
        );

//        //<img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto"> as link https://weillcornell.org/
//        $wcmUrl = '<a href="https://weillcornell.org/"><img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto"></a>';
//        $html = str_replace(
//            '<img src="wcm-logo.png" alt="Weill Cornell Medicine" class="h-10 w-auto">',
//            $wcmUrl,
//            $html
//        );

//        $wcmLink = '<a href="weillcornell.org/">Weill Cornell Medicine</a>'; //weillcornell.org/
//        //Weill Cornell Medicine · Tissue Microarrays
//        $html = str_replace(
//            'Weill Cornell Medicine · Tissue Microarrays',
//            $wcmLink.' · Tissue Microarrays · '.$homeUrl . ' · ' . $loginUrl,
//            $html
//        );

        return $this->render('AppTmaBundle/Default/index.html.twig', [
            'site_html' => $html,
        ]);
    }


}
