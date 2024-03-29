<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 10/11/2021
 * Time: 11:03 AM
 */

namespace App\DashboardBundle\Controller;



use App\DashboardBundle\Entity\TopicList; //process.py script: replaced namespace by ::class: added use line for classname=TopicList
use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\DashboardBundle\Entity\ChartTypeList; //process.py script: replaced namespace by ::class: added use line for classname=ChartTypeList
use App\DashboardBundle\Entity\ChartList;
use App\DashboardBundle\Form\FilterDashboardType;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DashboardController extends OrderAbstractController
{

    //filter[projectSpecialty][] - not working, filter[projectSpecialty] - working
    //private $emptyProjectSpecialtyFilter = null; //"All" => 0 "AP/CP" => 2
    /**
     * Template("AppDashboardBundle/Dashboard/dashboard-choices.html.twig")
     * Dashboard home page .../dashboards/
     */
    #[Route(path: '/', name: 'dashboard_home')]
    #[Template('AppDashboardBundle/React/dashboard-choices.html.twig')]
    public function dashboardChoicesAction( Request $request )
    {
        if( $this->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $dashboardUtil = $this->container->get('dashboard_util');

        $initHomePage = true;
        if(
            $request->query->has('title') ||
            $request->query->has('projectSpecialty') ||
            $request->query->has('chartType') ||
            $request->query->has('startDate') ||
            $request->query->has('endDate')
        ) {
            $initHomePage = false;
        }
        //echo "title=$title<br>";

        //check if user is not authenticated IS_AUTHENTICATED_FULLY
//        if( !$this->isGranted('IS_AUTHENTICATED_FULLY') ) {
//            $initHomePage = false;
//        }

        //Redirect if filter is empty
        if( $initHomePage ) {

            //1) Add Favorite charts
            $favoriteCharts = $dashboardUtil->getFavorites();
            if (count($favoriteCharts) > 0) {
                return $this->redirectToRoute('dashboard_single_favorite', array('id' => 'all'));
            }

            $userSecUtil = $this->container->get('user_security_utility');
            //2) If none, add Default Dashboard Topic
            $defaultTopic = $userSecUtil->getSiteSettingParameter('topic','dashboard');
            if( $defaultTopic ) {
                return $this->redirectToRoute('dashboard_single_topic_id', array('id' => $defaultTopic->getId()));
            }

            //3) If none, add Default Dashboard Charts
            $defaultCharts = $userSecUtil->getSiteSettingParameter('charts','dashboard');
            if( count($defaultCharts) > 0 ) {
                $now = new \DateTime('now');
                $endDateStr = $now->format('m/d/Y');
                $startDateStr = $now->modify('-1 year')->format('m/d/Y');

                $redirectParams = array(
                    'filter[startDate]' => $startDateStr,
                    'filter[endDate]' => $endDateStr,
                    //'filter[projectSpecialty][]' => 0,
                    //'filter[projectSpecialty][0]' => '0', //$this->emptyProjectSpecialtyFilter, //filter[projectSpecialty][] => 0
                    'title' => "Default Charts"
                );
                $count = 0;

                foreach ($defaultCharts as $chart) {

                    if( $this->isViewPermitted($chart) === false ) {
                        continue;
                    }

                    $redirectParams['filter[chartType]['.$count.']'] = $chart->getAbbreviation();
                    $count++;
                }

                //redirect to home page with preset filter with chart types
                return $this->redirectToRoute('dashboard_home', $redirectParams);
            }//if $defaultCharts
        }//if $initHomePage

        //exit('dashboardChoicesAction: before getFilter');

        //if( $this->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
            $filterform = $this->getFilter();
        //} else {
        //    $filterform = $this->getFilterPublic();
        //}

        //$filterform = $this->getFilter();
        //exit('dashboardChoicesAction: after getFilter');

        $filterform->handleRequest($request);

        //chartType
//        $useWarning = true;
//        $autoLoad = $request->query->get('auto');
//        if( isset($autoLoad) ) {
//            if( $autoLoad ) {
//                //echo "auto is true <br>";
//                $useWarning = false;
//            } else {
//                //echo "auto is false <br>";
//            }
//        } else {
//            //echo "auto not set <br>";
//            //$useWarning = true;
//        }
//        if( $useWarning ) {
//            echo "useWarning is true <br>";
//            $useWarning = false;
//        } else {
//            echo "useWarning is false <br>";
//        }
        //exit('111');
        $chartTypesCount = 0;
        $chartTypes = $filterform["chartType"]->getData();
        if( $chartTypes ) {
            $chartTypesCount = count($chartTypes);
        }
//        if( !$useWarning ) {
//            $chartTypesCount = 0;
//        }

        $maxDisplayCharts = 20;
        if( $chartTypesCount > $maxDisplayCharts ) {
            $this->addFlash(
                'pnotify',
                "Please click 'Display' button to generate multiple charts"
            );
        }

        $title = $request->query->get('title');
        if( !$title ) {
            $title = 'Dashboard';
        }

        $defaultTopicId = $request->query->get('defaultTopicId');
        if( !$defaultTopicId ) {
            $defaultTopicId = NULL;
        }

        return array(
            'title' => $title,
            'filterform' => $filterform->createView(),
            'chartsArray' => array(),
            'spinnerColor' => '#85c1e9',
            //'useWarning' => $useWarning,
            'chartTypesCount' => $chartTypesCount,
            'maxDisplayCharts' => $maxDisplayCharts,
            'defaultTopicId' => $defaultTopicId,
            'public' => false
        );
    }

    public function getFilter( $showLimited=false, $withCompareType=false ) {
        $transresUtil = $this->container->get('transres_util');
        $dashboardUtil = $this->container->get('dashboard_util');
        //////////// Filter ////////////
        //default date range from today to 1 year back
        $projectSpecialtiesWithAll = array('All'=>0);
        $projectSpecialties = $transresUtil->getTransResProjectSpecialties();
        foreach($projectSpecialties as $projectSpecialty) {
            $projectSpecialtiesWithAll[$projectSpecialty->getName()] = $projectSpecialty->getId();
        }

        $endDate = new \DateTime('now');
        $startDate = new \DateTime('now');
        //set to today
        //$endDate = $endDate->modify('-3 year');
        $startDate = $startDate->modify('-1 year');//->format('m/d/Y');
        //dump($startDate);
        //dump($endDate);
        //exit('111');

        $params = array(
            'startDate' => $startDate,
            'endDate' => $endDate,
            "projectSpecialty" => true,
            "projectSpecialties" => $projectSpecialtiesWithAll,
            "compareType" => false,
            "showLimited" => true,
            "category" => true
        );

        if( $withCompareType ) {
            $params["compareType"] = true;
        }

        if( $showLimited ) {
            $params["showLimited"] = $showLimited;
        }

        //chartTypes
        $params["chartType"] = true;
        $params["chartTypes"] = $dashboardUtil->getChartTypes();

        //echo "before createForm <br>";
        //$params = array();

        $filterform = $this->createForm(FilterDashboardType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));
        //exit('after createForm');
        //////////// EOF Filter ////////////

        return $filterform;
    }

    /**
     * Public Dashboards: https://bitbucket.org/victorbrodsky/trp/issues/261/public-dashboards
     */
    #[Route(path: '/public', name: 'dashboard_public')]
    #[Template('AppDashboardBundle/React/dashboard-public.html.twig')]
    public function dashboardPublicChoicesAction( Request $request )
    {
        //B- checks if the user is logged in and forwards to the usual URL
        // if the user is logged in (making use of the chart ID array in the URL,
        // so the logged in user still sees the same charts with one click).
        if( $this->isGranted('ROLE_DASHBOARD_USER') ) {
            return $this->redirectToRoute('dashboard_home');
        }

        //exit('111');

        //$dashboardUtil = $this->container->get('dashboard_util');
        $filterform = $this->getFilterPublic();
        $filterform->handleRequest($request);

        //chartType
//        $useWarning = true;
//        $autoLoad = $request->query->get('auto');
//        if( isset($autoLoad) ) {
//            if( $autoLoad ) {
//                //echo "auto is true <br>";
//                $useWarning = false;
//            } else {
//                //echo "auto is false <br>";
//            }
//        } else {
//            //echo "auto not set <br>";
//            //$useWarning = true;
//        }

        $chartTypesCount = 0;
        $chartTypes = $filterform["chartType"]->getData();
        if( $chartTypes ) {
            $chartTypesCount = count($chartTypes);
        }
//        if( !$useWarning ) {
//            $chartTypesCount = 0;
//        }

        $maxDisplayCharts = 20;
        if( $chartTypesCount > $maxDisplayCharts ) {
            $this->addFlash(
                'pnotify',
                "Please click 'Display' button to generate multiple charts"
            );
        }

        $title = $request->query->get('title');
        if( !$title ) {
            $title = 'Public Dashboards';
        }

        return array(
            'title' => $title,
            'filterform' => $filterform->createView(),
            'chartsArray' => array(),
            'spinnerColor' => '#85c1e9',
//            'useWarning' => $useWarning,
            'chartTypesCount' => $chartTypesCount,
            'maxDisplayCharts' => $maxDisplayCharts,
            'public' => true
        );
    }

    public function getFilterPublic( $showLimited=false, $withCompareType=false ) {
        //$transresUtil = $this->container->get('transres_util');
        $dashboardUtil = $this->container->get('dashboard_util');

        //////////// Filter ////////////

        //default date range from today to 1 year back
        $projectSpecialtiesWithAll = array('All'=>0);
//        $projectSpecialties = $transresUtil->getTransResProjectSpecialties();
//        foreach($projectSpecialties as $projectSpecialty) {
//            $projectSpecialtiesWithAll[$projectSpecialty->getName()] = $projectSpecialty->getId();
//        }

        $endDate = new \DateTime('now');
        $startDate = new \DateTime('now');
        //set to today
        //$endDate = $endDate->modify('-3 year');
        $startDate = $startDate->modify('-1 year');//->format('m/d/Y');

        $params = array(
            'startDate' => $startDate,
            'endDate' => $endDate,
            "projectSpecialty" => true,
            "projectSpecialties" => $projectSpecialtiesWithAll,
            "compareType" => false,
            "showLimited" => true,
            "category" => true
        );

        if( $withCompareType ) {
            $params["compareType"] = true;
        }

        if( $showLimited ) {
            $params["showLimited"] = $showLimited;
        }

        $publicCharts = $dashboardUtil->getPublicChartTypes();

        //dump($publicCharts);
        //exit('111');

        //chartTypes
        $params["chartType"] = true;
        $params["chartTypes"] = $publicCharts;

        //chartTypesShow
        $params["chartTypesShow"] = $publicCharts;

        $filterform = $this->createForm(FilterDashboardType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));
        //////////// EOF Filter ////////////

        return $filterform;
    }

    /**
     * single dashboard chart. id - chart ID
     */
    #[Route(path: '/chart/{id}', name: 'dashboard_single_chart_id')]
    #[Template('AppDashboardBundle/Dashboard/dashboard.html.twig')]
    public function singleChartAction( Request $request, $id ) {

        //return array('sitename'=>$this->getParameter('dashboard.sitename'));

        $chartsArray = array();

        $title = "Single chart (Not Implemented yet)";

        return array(
            'title' => $title,
            'chartsArray' => $chartsArray
        );
    }
    /**
     * From transres
     */
    #[Route(path: '/single-chart/', name: 'dashboard_single_chart', options: ['expose' => true])]
    public function singleOrigChartAction( Request $request )
    {

//        if( $this->isGranted('ROLE_DASHBOARD_USER') ) {
//            //ok
//        } else {
//            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
//        }

        //dump($request);
        //exit('111');

        $dashboardUtil = $this->container->get('dashboard_util');

        $chartsArray = $dashboardUtil->getDashboardChart($request);

        //dump($chartsArray);
        //exit('EOF singleOrigChartAction');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        //$response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setStatusCode(200);
        $response->setContent(json_encode($chartsArray));

        //#Testing http cache. cache publicly for 3600 seconds (1 hour)
        //$response->setPublic();
        //$response->setMaxAge(3600);

        return $response;
    }


    /**
     * single dashboard topic. id - topic ID
     * load the selected charts without any additional user interaction
     */
    #[Route(path: '/topic/{id}', name: 'dashboard_single_topic_id')]
    #[Template('AppDashboardBundle/Dashboard/dashboard.html.twig')]
    public function singleTopicByIdAction( Request $request, $id ) {

//        if( $this->isGranted('ROLE_DASHBOARD_USER') ) {
//            //ok
//        } else {
//            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
//        }

        $em = $this->getDoctrine()->getManager();

        if( !$id ) {
            $error = "Topic id is not provided";
            $this->addFlash(
                'warning',
                $error
            );
            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:TopicList'] by [TopicList::class]
        $topic = $em->getRepository(TopicList::class)->find($id);
        if( !$topic ) {
            $error = "Topic is not found by ID '".$id."'";
            //throw new \Exception($error);

            $this->addFlash(
                'warning',
                $error
            );

            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        //find charts by $topic
        $dashboardUtil = $this->container->get('dashboard_util');
        //$children = false;
        $children = true;
        $chartsArray = $dashboardUtil->getChartsByTopic($topic,$children);

        //dump($chartsArray);
        //exit('111');

        $now = new \DateTime('now');
        $endDateStr = $now->format('m/d/Y');
        $startDateStr = $now->modify('-1 year')->format('m/d/Y');
        
        $redirectParams = array(
            'filter[startDate]' => $startDateStr,
            'filter[endDate]' => $endDateStr,
            //'filter[projectSpecialty][]' => $this->emptyProjectSpecialtyFilter,
            'title' => "Topic '".$topic->getName()."'",
            'defaultTopicId' => $id,
            'auto' => true
        );
        $count = 0;

        foreach ($chartsArray as $chart) {

            if( $this->isViewPermitted($chart) === false ) {
                //exit('chart '.$chart->getName().' not permitted');
                continue;
            }

            $redirectParams['filter[chartType]['.$count.']'] = $chart->getAbbreviation();
            $count++;
        }

        //redirect to home page with preset filter with chart types
        return $this->redirectToRoute(
            'dashboard_home',
            $redirectParams
        );
    }

    /**
     * Search by Dashboard topic
     */
    #[Route(path: '/search-topic/', name: 'dashboard_search_topic', methods: ['GET'])]
    #[Template('AppDashboardBundle/Search/search.html.twig')]
    public function searchDashboardTopicAction( Request $request ) {

        if( $this->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        //$em = $this->getDoctrine()->getManager();
        //$user = $this->getUser();

        //$dashboardTopicSearch = $request->request->get('dashboardTopicSearch');
        //$dashboardTopicSearch = $request->query->get('dashboardTopicSearch'); //Financial_3
        $topicId = $request->query->get('dashboardTopicSearch'); //Financial_3

        if( !$topicId ) {
            $this->addFlash(
                'warning',
                "Chart topic is empty"
            );
            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        //list($topicName, $topicId) = explode("_", $dashboardTopicSearch);
        //echo "ID=".$topicId.", topicName=$topicName <br>";

        //exit("Not implemented yet. dashboardTopicSearch=".$dashboardTopicSearch);

//        if( $topicName == "All Charts" ) {
//            $topicAllCharts = $em->getRepository('AppDashboardBundle:TopicList')->find($topicId);
//        }

        return $this->singleTopicByIdAction($request, $topicId);
    }

    /**
     * charts belonging to a single organizational group. id - organizational group associated with the displayed charts
     * load the selected charts without any additional user interaction
     */
    #[Route(path: '/service/{id}', name: 'dashboard_single_service')]
    #[Template('AppDashboardBundle/Dashboard/dashboard.html.twig')]
    public function singleServiceAction( Request $request, $id ) {

        if( $this->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        if( !$id ) {
            $error = "Service id is not provided";
            $this->addFlash(
                'warning',
                $error
            );
            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $institution = $em->getRepository(Institution::class)->find($id);
        if( !$institution ) {
            $error = "Institution is not found by ID '".$id."'";
            $this->addFlash(
                'warning',
                $error
            );

            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        //find charts by $topic
        $dashboardUtil = $this->container->get('dashboard_util');
        $chartsArray = $dashboardUtil->getChartsByInstitution($institution);

        //dump($chartsArray);
        //exit('EOF single Service Action');

        $now = new \DateTime('now');
        $endDateStr = $now->format('m/d/Y');
        $startDateStr = $now->modify('-1 year')->format('m/d/Y');

        $redirectParams = array(
            'filter[startDate]' => $startDateStr,
            'filter[endDate]' => $endDateStr,
            //'filter[projectSpecialty][]' => $this->emptyProjectSpecialtyFilter,
            'title' => "Service '".$institution->getName()."'",
            'auto' => true
        );
        $count = 0;

        foreach ($chartsArray as $chart) {

            if( $this->isViewPermitted($chart) === false ) {
                //exit('chart '.$chart->getName().' not permitted');
                continue;
            }

            $redirectParams['filter[chartType]['.$count.']'] = $chart->getAbbreviation();
            $count++;
        }

        //redirect to home page with preset filter with chart types
        return $this->redirectToRoute(
            'dashboard_home',
            $redirectParams
        );
    }

    /**
     * charts belonging to a single type. id - chart type ID
     */
    #[Route(path: '/chart-type/{id}', name: 'dashboard_single_type')]
    #[Template('AppDashboardBundle/Dashboard/dashboard.html.twig')]
    public function singleTypeAction( Request $request, $id ) {

        if( $this->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        if( !$id ) {
            $error = "Chart type  id is not provided";
            $this->addFlash(
                'warning',
                $error
            );
            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartTypeList'] by [ChartTypeList::class]
        $chartType = $em->getRepository(ChartTypeList::class)->find($id);
        if( !$chartType ) {
            $error = "Chart type is not found by ID '".$id."'";
            $this->addFlash(
                'warning',
                $error
            );

            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        //find charts by $topic
        $dashboardUtil = $this->container->get('dashboard_util');
        $chartsArray = $dashboardUtil->getChartsByChartType($chartType);

        //dump($chartsArray);
        //exit('EOF single Service Action');

        $now = new \DateTime('now');
        $endDateStr = $now->format('m/d/Y');
        $startDateStr = $now->modify('-1 year')->format('m/d/Y');

        $redirectParams = array(
            'filter[startDate]' => $startDateStr,
            'filter[endDate]' => $endDateStr,
            //'filter[projectSpecialty][]' => $this->emptyProjectSpecialtyFilter,
            'title' => "Chart type '".$chartType->getName()."'"
        );
        $count = 0;

        foreach ($chartsArray as $chart) {

            if( $this->isViewPermitted($chart) === false ) {
                //exit('chart '.$chart->getName().' not permitted');
                continue;
            }

            $redirectParams['filter[chartType]['.$count.']'] = $chart->getAbbreviation();
            $count++;
        }

        //redirect to home page with preset filter with chart types
        return $this->redirectToRoute(
            'dashboard_home',
            $redirectParams
        );
    }

    /**
     * charts belonging to a single favorite. id - user ID
     */
    #[Route(path: '/favorites/{id}', name: 'dashboard_single_favorite')]
    #[Template('AppDashboardBundle/Dashboard/dashboard.html.twig')]
    public function singleFavoritesAction( Request $request, $id ) {

        if( $this->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $dashboardUtil = $this->container->get('dashboard_util');
        $em = $this->getDoctrine()->getManager();

        if( !$id ) {
            $error = "Chart id is not provided";
            $this->addFlash(
                'warning',
                $error
            );
            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        $now = new \DateTime('now');
        $endDateStr = $now->format('m/d/Y');
        $startDateStr = $now->modify('-1 year')->format('m/d/Y');

        $redirectParams = array(
            'filter[startDate]' => $startDateStr,
            'filter[endDate]' => $endDateStr,
            //'filter[projectSpecialty][]' => $this->emptyProjectSpecialtyFilter,
            //'title' => $title
        );

        if( strpos((string)$id, 'all-favorites-') !== false ) {
            //multiple charts
            $id = str_replace('all-favorites-','',$id); //now id=1-2-4-7
            $idsArr = explode('-',$id);

            $title = "Favorite charts";
            $redirectParams['title'] = $title;
            //$redirectParams['filter[chartType][0]'] = $chart->getAbbreviation();

            $counter = 1;
            foreach($idsArr as $chartId) {
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
                $chart = $em->getRepository(ChartList::class)->find($chartId);
                if( !$chart ) {
                    continue;
                }

                if( $this->isViewPermitted($chart) === false ) {
                    continue;
                }

                $redirectParams['filter[chartType]['.$counter.']'] = $chart->getAbbreviation();
                $counter++;
            }

        }
        elseif( $id === 'all' ) {
            //echo "dashboard_single_favorite: id=$id <br>";
            $counter = 1;
            $favoriteCharts = $dashboardUtil->getFavorites();
            $title = "Favorite charts";
            $redirectParams['title'] = $title;
            foreach($favoriteCharts as $favoriteChart) {
                if( $this->isViewPermitted($favoriteChart) === false ) {
                    continue;
                }

                $redirectParams['filter[chartType]['.$counter.']'] = $favoriteChart->getAbbreviation();
                $counter++;
            }
        }
        else {
            //single chart
        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
            $chart = $em->getRepository(ChartList::class)->find($id);
            if( !$chart ) {
                $error = "Chart is not found by ID '".$id."'";
                $this->addFlash(
                    'warning',
                    $error
                );

                return $this->redirect( $this->generateUrl('dashboard_home') );
            }

            if( $this->isViewPermitted($chart) === false ) {
                return $this->redirect( $this->generateUrl('dashboard_home') );
            }

            $title = "Favorite chart '".$chart->getName()."'";
            $redirectParams['title'] = $title;
            $redirectParams['filter[chartType][0]'] = $chart->getAbbreviation();
        }

        //dump($redirectParams);
        //exit("EOF dashboard_single_favorite: id=$id");

        //redirect to home page with preset filter with chart types
        return $this->redirectToRoute(
            'dashboard_home',
            $redirectParams
        );
    }

    public function isViewPermitted($chart) {
        
        //check isChartPublic
        $dashboardUtil = $this->container->get('dashboard_util');
        if( $dashboardUtil->isChartPublic($chart) ) {
            return true;
        }
        
        if( $this->isGranted('read', $chart) === true ) {
            return true;
        }

        //get admin email
        $userSecUtil = $this->container->get('user_security_utility');
        $adminemail = $userSecUtil->getSiteSettingParameter('siteEmail');

        $error = "You do not have access to this chart '".$chart->getName()."'. Please request access by contacting your site administrator $adminemail.";
        $this->addFlash(
            'warning',
            $error
        );

        //exit('Not permitted');
        return false;
    }

    #[Route(path: '/dashboard-toggle-favorite', name: 'dashboard_toggle_favorite', methods: ['POST'], options: ['expose' => true])]
    public function dashboardToggleFavoriteAction( Request $request )
    {
        if( $this->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $chartId = trim((string)$request->get('chartId') );

        //process.py script: replaced namespace by ::class: ['AppDashboardBundle:ChartList'] by [ChartList::class]
        $chart = $em->getRepository(ChartList::class)->find($chartId);
        if( !$chart ) {
            exit("Chart not found by ID $chartId");
        }

        //Allow add/remove favorite without permission check?
        //Not permitted chart will no be visible anyway.
//        if( $this->isViewPermitted($chart) === false ) {
//            exit("No permission to add this chart to favorites");
//        }

        //echo "chart ID=".$chart->getId()."<br>";
        //$chart->getFavoriteUsers();
        //toggle favorite user
        if( $chart->isFavorite($user) ) {
            $chart->removeFavoriteUser($user);
        } else {
            $chart->addFavoriteUser($user);
        }

        $em->flush();

        $result = array(
            'result' => "OK",
            'favorite' => $chart->isFavorite($user)
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        //$response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setStatusCode(200);
        $response->setContent(json_encode($result));
        return $response;
    }


}