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

    #[Route(path: '/test', name: 'ctp_test_home', methods: ['GET'])]
    #[Template('AppCtpBundle/Default/home.html.twig')]
    public function indexAction( Request $request ) {

        if( false == $this->isGranted('ROLE_CTP_USER') ){
            return $this->redirect( $this->generateUrl('ctp-nopermission') );
        }

        $title = 'Center for Translational Pathology';

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();
        //echo "accessreq count=".count($accessreqs)."<br>";
        $accessreqsCount = 0;
        if( is_array($accessreqs) ) {
            $accessreqsCount = count($accessreqs);
        }

        //echo "project dir=".$this->getParameter('kernel.project_dir')."<br>"; //C:\Users\cinav\Documents\WCMC\ORDER\order-lab\orderflex
        //$path = $this->getParameter('kernel.project_dir') . '/public/static/myfile.html';
        $path = 'C:/MyWebSites/path2path/localhost_3000/index.html';
        $html = file_get_contents($path);

        return array(
            'title' => $title,
            'accessreqs' => $accessreqsCount,
            'html' => $html,
        );
    }

    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->isGranted('ROLE_CTP_ADMIN') ) {
            return null;
        }
        $userSecUtil = $this->container->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->getParameter('ctp.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
    }


    #[Route('/{page}', name: 'ctp_home', defaults: ['page' => 'index'])]
    public function mirror( string $page ): Response
    {
        $base = $this->getParameter('kernel.project_dir') . '/public/ctp_site/localhost_3000/';
        $file = $base . $page . '.html';

        if (!file_exists($file)) {
            throw $this->createNotFoundException("Page not found: $page");
        }

        $html = file_get_contents($file);

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

        //
        // 1. Rewrite internal links
        //
        $html = preg_replace(
            '/href="([^":]+)\.html"/i',
            'href="/center-for-translational-pathology/$1"',
            $html
        );

        //
        // 2. Rewrite CSS/JS paths
        //
        $html = preg_replace(
            '/(src|href)="(css|js|images|assets)\//i',
            '$1="/ctp_site/localhost_3000/$2/',
            $html
        );

        //
        // 3. Rewrite Next.js optimized images
        //
        $html = preg_replace_callback(
            '/_next\/image\?url=%2Fimages%2F([^"&]+).*?"/i',
            function ($matches) use ($findRealFile) {
                $real = $findRealFile($matches[1]);
                return '/ctp_site/localhost_3000/_next/' . $real . '"';
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
            },
            $html
        );

        //
        // 5. Rewrite any remaining _next/... paths
        //
        $html = preg_replace(
            '/(src|srcset)="_next\//i',
            '$1="/ctp_site/localhost_3000/_next/',
            $html
        );

        //
        // 6. Fix accidental leading double slashes
        //
        $html = preg_replace(
            '/(src|srcset)="\/\//i',
            '$1="/',
            $html
        );

        return $this->render('AppCtpBundle/Mirror/wrapper.html.twig', [
            'site_html' => $html,
        ]);
    }

}
