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

namespace App\CtpBundle\Controller;





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
    #[Route(path: '/about', name: 'ctp_about_page')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function aboutAction(Request $request)
    {
        return array('sitename' => $this->getParameter('ctp.sitename'));
    }

    #[Route(path: '/', name: 'ctp_home', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/home.html.twig')]
    public function indexAction( Request $request ) {

//        if( false == $this->isGranted('ROLE_CTP_USER') ){
//            return $this->redirect( $this->generateUrl('ctp-nopermission') );
//        }

        $title = 'Center for Translational Pathology';

        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/publications', name: 'ctp_publications', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/publications.html.twig')]
    public function publicationsAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/new-project-inquiry', name: 'ctp_new-project-inquiry', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/new-project-inquiry.html.twig')]
    public function newProjectInquiryAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/people', name: 'ctp_people', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/people.html.twig')]
    public function peopleAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/applications', name: 'ctp_applications', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/applications.html.twig')]
    public function applicationsAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }
    
    #[Route(path: '/histocore-and-ihc-lab', name: 'ctp_histocore-and-ihc-lab', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/histocore-and-ihc-lab.html.twig')]
    public function histocoreAndIhcLabAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/misi-lab', name: 'ctp_misi-lab', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/misi-lab.html.twig')]
    public function misiLabAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    //Exp Cell Therapy Lab
    #[Route(path: '/experimental-cellular-therapy-lab', name: 'ctp_ect', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/ect.html.twig')]
    public function ectAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    #[Route(path: '/genomics-lab', name: 'ctp_genomiclab', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/genomics-lab.html.twig')]
    public function genomicLabAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    //CP Research Lab
    #[Route(path: '/clinical-pathology-research-lab', name: 'ctp_cpresearchlab', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/cp-research-lab.html.twig')]
    public function cpResearchLabAction( Request $request ) {
        $title = 'Center for Translational Pathology';
        return array(
            'title' => $title,
        );
    }

    //Comp Path Lab
    #[Route(path: '/computational-pathology-lab', name: 'ctp_comppathlab', methods: ['GET'])]
    #[Template('AppCtpBundle/Home/comppathlab.html.twig')]
    public function expCellLabAction( Request $request ) {
        $title = 'Center for Translational Pathology';
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
//        if( !$this->isGranted('ROLE_CTP_ADMIN') ) {
//            return null;
//        }
//        $userSecUtil = $this->container->get('user_security_utility');
//        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->getParameter('ctp.sitename'),AccessRequest::STATUS_ACTIVE);
//        return $accessreqs;
//    }


    //1) https://view-test.med.cornell.edu/center-for-translational-pathology
    //2) login only for dashboard
    //3) add login to footer
    //0) https://view.online/c/wcm/pathology/ -> enable https://view.online/c/wcm/pathology/center-for-translational-pathology
    //1) image Weill Cornell Medicine -> as link to $homeUrl
    //2) text footer Weill Cornell Medicine -> as link to https://weillcornell.org/
    //3) Center for Translational Pathology -> home of Center for Translational Pathology
    //7) External Collaboration / Project Inquiry activate the same behaviour as original site
    //8) http://localhost:3000/project-requests-public: change url to /center-for-translational-pathology/new-project-inquiry
    //9) change urls for 6 squares
    //10) http://localhost:3000/path2path-dashboard-login -> login -> http://localhost:3000/path2path-dashboard
    //#[Route('/{page}', name: 'ctp_home', defaults: ['page' => 'index'])]
    //#[Route('/index', name: 'ctp_index')]
    //#[Route('/{page}', name: 'ctp_home')]
    public function homeAction( Request $request, RouterInterface $router, string $page=null ): Response
    {
        if( $request->get('_route') == 'ctp_index' ) {
            return $this->redirect( $this->generateUrl('ctp_home') );
        }

        //$base = $this->getParameter('kernel.project_dir') . '/public/ctp_site/localhost_3000/';
        $base = $this->getParameter('kernel.project_dir') .
            //'/public/ctp_site/localhost_3000/';
            '/public/orderassets/AppCtpBundle/ctp_site/localhost_3000/';
//            '/ctp_site/localhost_3000/';
//            '/src/App/CtpBundle/Util/ctp_site/localhost_3000/';
//            '/src/templates/AppCtpBundle/ctp_site/localhost_3000/';

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
        //   /c/wcm/pathology/center-for-translational-pathology/people
        //
        // $page = "people"
        // Remove "/people" → prefix = "/c/wcm/pathology/center-for-translational-pathology"
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
            'href="/center-for-translational-pathology/$1"',
            $html
        );

        //
        // 1. Rewrite internal links using dynamic prefix
        //
        $basePath = rtrim($request->getBasePath(), '/');
        $prefix   = $basePath . '/center-for-translational-pathology';

        // Case A: "people.html"
        $html = preg_replace(
            '/href="([^":]+)\.html"/i',
            'href="' . $prefix . '/$1"',
            $html
        );

        // Case B: "/center-for-translational-pathology/people"
        $html = preg_replace(
            '#href="/?center-for-translational-pathology/([^"]*)"#i',
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
                'ctp_login',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $loginUrl = '<a href="' . $loginUrl . '">Login</a>';
        }

//        $wcmLink = '<a href="weillcornell.org/">Weill Cornell Medicine</a>'; //weillcornell.org/
//        //Weill Cornell Medicine · Center for Translational Pathology
        $html = str_replace(
            'Weill Cornell Medicine · Center for Translational Pathology',
            'Weill Cornell Medicine'.' · Center for Translational Pathology · '.$homeUrl . ' · ' . $loginUrl,
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
            '$1="/ctp_site/localhost_3000/$2/',
            //'$1="/orderassets/AppCtpBundle/ctp_site/localhost_3000/$2/',
            $html
        );

        if(1) {
            //C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex\public\orderassets\AppCtpBundle\ctp_site\localhost_3000\faviconbcf9.ico
            //
            // 3. Rewrite Next.js optimized images
            //
            $html = preg_replace_callback(
                //'/_next\/image\?url=%2Fimages%2F([^"&]+).*?"/i',
                '/_next\/image\?url=%2Fimages%2F([^"&]+).*?"/i',
                function ($matches) use ($findRealFile) {
                    $real = $findRealFile($matches[1]);
                    return '/ctp_site/localhost_3000/_next/' . $real . '"';
                    //return '/orderassets/AppCtpBundle/ctp_site/localhost_3000/_next/' . $real . '"';
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
                    return 'src="/ctp_site/localhost_3000/_next/' . $real . '"';
                    //return 'src="/orderassets/AppCtpBundle/ctp_site/localhost_3000/_next/' . $real . '"';
                },
                $html
            );

            //
            // 5. Rewrite any remaining _next/... paths
            //
            $html = preg_replace(
                '/(src|srcset)="_next\//i',
                '$1="/ctp_site/localhost_3000/_next/',
                //'$1="/orderassets/AppCtpBundle/ctp_site/localhost_3000/_next/',
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
//        //Weill Cornell Medicine · Center for Translational Pathology
//        $html = str_replace(
//            'Weill Cornell Medicine · Center for Translational Pathology',
//            $wcmLink.' · Center for Translational Pathology · '.$homeUrl . ' · ' . $loginUrl,
//            $html
//        );

        return $this->render('AppCtpBundle/Default/index.html.twig', [
            'site_html' => $html,
        ]);
    }


}
