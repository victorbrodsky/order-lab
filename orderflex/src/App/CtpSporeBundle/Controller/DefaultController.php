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

namespace App\CtpSporeBundle\Controller;

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
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends OrderAbstractController
{
    #[Route(path: '/about', name: 'spore_about_page')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function aboutAction(Request $request)
    {
        return array('sitename' => $this->getParameter('spore.sitename'));
    }

    #[Route(path: '/', name: 'spore_home', methods: ['GET'])]
    #[Template('AppSporeBundle/Home/spore-home-simple.html.twig')]
    public function indexAction(Request $request)
    {
        if( false == $this->isGranted('ROLE_SPORE_USER') ){
            return $this->redirect( $this->generateUrl('spore-nopermission') );
        }

        $title = 'Prostate Cancer Research Data Explorer';

        
        // Dashboard statistics - all zeros to match original empty state
        $dashboard = [
            'totalPatients' => 0,
            'totalSpecimens' => 0,
            'totalProcedures' => 0,
            'totalBlocks' => 0,
            'totalSlides' => 0,
            'totalBiomarkers' => 0,
            'totalTMACores' => 0,
            'biomarkerSummary' => [
                ['name' => 'ERG', 'count' => 0, 'positivePct' => 0, 'negativePct' => 0, 'equivocalPct' => 0],
                ['name' => 'PTEN', 'count' => 0, 'positivePct' => 0, 'negativePct' => 0, 'equivocalPct' => 0],
                ['name' => 'SPOP', 'count' => 0, 'positivePct' => 0, 'negativePct' => 0, 'equivocalPct' => 0],
                ['name' => 'SPINK1', 'count' => 0, 'positivePct' => 0, 'negativePct' => 0, 'equivocalPct' => 0],
            ],
            'gleasonData' => [],
            'biomarkerData' => [],
            'procedureTypes' => [],
            'raceDistribution' => [],
            'survivalData' => [
                ['name' => 'Alive', 'count' => 0, 'percentage' => 0],
                ['name' => 'Deceased', 'count' => 0, 'percentage' => 0],
                ['name' => 'Unknown', 'count' => 0, 'percentage' => 0],
            ],
        ];
        
        // Recent activity - empty to match original
        $recentActivity = [];
        
        return [
            'title' => $title,
            'dashboard' => $dashboard,
            'recentActivity' => $recentActivity,
        ];
    }

    #[Route(path: '/patients', name: 'spore_patients', methods: ['GET'])]
    #[Template('AppSporeBundle/Home/spore-patients.html.twig')]
    public function patientsAction(Request $request)
    {
        $search = $request->query->get('q', '');
        
        // Placeholder patient data - replace with actual database query
        $patients = [];
        
        return [
            'title' => 'Patient Browser',
            'search' => $search,
            'patients' => $patients,
        ];
    }

    #[Route(path: '/specimens', name: 'spore_specimens', methods: ['GET'])]
    #[Template('AppSporeBundle/Home/spore-specimens.html.twig')]
    public function specimensAction(Request $request)
    {
        $search = $request->query->get('accession', '');
        
        // Placeholder specimen data - replace with actual database query
        $specimens = [];
        
        return [
            'title' => 'Specimen Lookup',
            'search' => $search,
            'specimens' => $specimens,
        ];
    }

    #[Route(path: '/biomarkers', name: 'spore_biomarkers', methods: ['GET'])]
    #[Template('AppSporeBundle/Home/spore-biomarkers.html.twig')]
    public function biomarkersAction(Request $request)
    {
        // Placeholder biomarker summary data
        $biomarkers = [
            'erg' => ['positive' => 523, 'negative' => 724, 'equivocal' => 0],
            'pten' => ['positive' => 436, 'negative' => 811, 'equivocal' => 0],
            'spop' => ['positive' => 349, 'negative' => 898, 'equivocal' => 0],
            'spink1' => ['positive' => 224, 'negative' => 1023, 'equivocal' => 0],
        ];
        
        return [
            'title' => 'Biomarker Summary',
            'biomarkers' => $biomarkers,
        ];
    }

    #[Route(path: '/outcomes', name: 'spore_outcomes', methods: ['GET'])]
    #[Template('AppSporeBundle/Home/spore-outcomes.html.twig')]
    public function outcomesAction(Request $request)
    {
        // Placeholder outcomes data
        $outcomes = [
            'recurrenceRate' => 23.5,
            'medianFollowUp' => 68.4,
            'fiveYearSurvival' => 91.2,
        ];
        
        return [
            'title' => 'Outcomes Explorer',
            'outcomes' => $outcomes,
        ];
    }

    #[Route(path: '/api/dashboard', name: 'spore_api_dashboard', methods: ['GET'])]
    public function apiDashboardAction(Request $request): JsonResponse
    {
        $dashboard = [
            'totalPatients' => 1247,
            'totalSpecimens' => 3456,
            'totalBlocks' => 8921,
            'totalSlides' => 12453,
            'totalTMACores' => 5678,
            'gleasonDistribution' => [
                ['score' => '6 (3+3)', 'count' => 245, 'percentage' => 19.6],
                ['score' => '7 (3+4)', 'count' => 412, 'percentage' => 33.0],
                ['score' => '7 (4+3)', 'count' => 298, 'percentage' => 23.9],
                ['score' => '8 (4+4)', 'count' => 156, 'percentage' => 12.5],
                ['score' => '9 (4+5/5+4)', 'count' => 89, 'percentage' => 7.1],
                ['score' => '10 (5+5)', 'count' => 47, 'percentage' => 3.8],
            ],
        ];
        
        return new JsonResponse($dashboard);
    }
}
