<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 10/11/2021
 * Time: 11:03 AM
 */

namespace App\DashboardBundle\Controller;


use App\DashboardBundle\Entity\ChartList;
use App\DashboardBundle\Form\FilterDashboardType;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DashboardController extends OrderAbstractController
{

    /**
     * Template("AppDashboardBundle/Dashboard/dashboard-choices.html.twig")
     *
     * @Route("/", name="dashboard_home")
     * @Template("AppDashboardBundle/React/dashboard-choices.html.twig")
     */
    public function dashboardChoicesAction( Request $request )
    {
        if( $this->get('security.authorization_checker')->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $filterform = $this->getFilter();
        $filterform->handleRequest($request);

        //chartType
        $useWarning = true;
        $autoLoad = $request->query->get('auto');
        if( isset($autoLoad) ) {
            if( $autoLoad ) {
                //echo "auto is true <br>";
                $useWarning = false;
            } else {
                //echo "auto is false <br>";
            }
        } else {
            //echo "auto not set <br>";
            //$useWarning = true;
        }
//        if( $useWarning ) {
//            echo "useWarning is true <br>";
//            $useWarning = false;
//        } else {
//            echo "useWarning is false <br>";
//        }
        //exit('111');
        $chartTypesCount = 0;
        $chartTypes = $filterform['chartType']->getData();
        if( $chartTypes ) {
            $chartTypesCount = count($chartTypes);
        }
        if( !$useWarning ) {
            $chartTypesCount = 0;
        }
        if( $chartTypesCount > 3 ) {
            $this->get('session')->getFlashBag()->add(
                'pnotify',
                'Please click Filter button to generate multiple charts'
            );
        }

        $title = $request->query->get('title');
        if( !$title ) {
            $title = "Dashboard";
        }

        return array(
            'title' => $title,
            'filterform' => $filterform->createView(),
            'chartsArray' => array(),
            'spinnerColor' => '#85c1e9',
            'useWarning' => $useWarning,
            'testflag' => "11111"
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


        $filterform = $this->createForm(FilterDashboardType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));
        //////////// EOF Filter ////////////

        return $filterform;
    }

    /**
     * single dashboard chart. id - chart ID
     *
     * @Route("/chart/{id}", name="dashboard_single_chart_id")
     * @Template("AppDashboardBundle/Dashboard/dashboard.html.twig")
     */
    public function singleChartAction( Request $request, $id ) {

        //return array('sitename'=>$this->getParameter('dashboard.sitename'));

        $chartsArray = array();

        return array(
            'title' => "Single chart",
            'chartsArray' => $chartsArray
        );
    }
    /**
     * From transres
     *
     * @Route("/single-chart/", name="dashboard_single_chart", options={"expose"=true})
     */
    public function singleOrigChartAction( Request $request )
    {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $dashboardUtil = $this->container->get('dashboard_util');

        $chartsArray = $dashboardUtil->getDashboardChart($request);

        //dump($chartsArray);
        //exit('EOF singleOrigChartAction');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        //$response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setStatusCode(200);
        $response->setContent(json_encode($chartsArray));
        return $response;
    }


    /**
     * single dashboard topic. id - topic ID
     *
     * @Route("/topic/{id}", name="dashboard_single_topic_id")
     * @Template("AppDashboardBundle/Dashboard/dashboard.html.twig")
     */
    public function singleTopicByIdAction( Request $request, $id ) {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        if( !$id ) {
            $error = "Topic id is not provided";
            $this->get('session')->getFlashBag()->add(
                'warning',
                $error
            );
            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        $topic = $em->getRepository('AppDashboardBundle:TopicList')->find($id);
        if( !$topic ) {
            $error = "Topic is not found by ID '".$id."'";
            //throw new \Exception($error);

            $this->get('session')->getFlashBag()->add(
                'warning',
                $error
            );

            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        //find charts by $topic
        $dashboardUtil = $this->container->get('dashboard_util');
        $chartsArray = $dashboardUtil->getChartsByTopic($topic);

        //dump($chartsArray);
        //exit('111');

        $now = new \DateTime('now');
        $endDateStr = $now->format('m/d/Y');
        $startDateStr = $now->modify('-1 year')->format('m/d/Y');
        
        $redirectParams = array(
            'filter[startDate]' => $startDateStr,
            'filter[endDate]' => $endDateStr,
            'filter[projectSpecialty][]' => 0,
            'title' => "Topic '".$topic->getName()."'",
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
//    /**
//     * single dashboard topic. topicName - topic name
//     *
//     * @Route("/topic-name/{topicName}", name="dashboard_single_topic_name")
//     * @Template("AppDashboardBundle/Dashboard/dashboard.html.twig")
//     */
//    public function singleTopicByNameAction( Request $request, $topicName ) {
//
//        //TODO: implement permission for a single chart
//        if( $this->get('security.authorization_checker')->isGranted('ROLE_DASHBOARD_USER') ) {
//            //ok
//        } else {
//            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        if( !$topicName ) {
//            $error = "Topic name is not provided";
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                $error
//            );
//            return $this->redirect( $this->generateUrl('dashboard_home') );
//        }
//
//        $topic = $em->getRepository('AppDashboardBundle:TopicList')->findByName($topicName);
//        if( !$topic ) {
//            $error = "Topic is not found by name '".$topicName."'";
//            //throw new \Exception($error);
//
//            $this->get('session')->getFlashBag()->add(
//                'warning',
//                $error
//            );
//
//            return $this->redirect( $this->generateUrl('dashboard_home') );
//        }
//
//        //TODO: find charts by $topic name
//
////        if( $this->isViewPermitted($chart) === false ) {
////            exit('chart '.$chart->getName().' not permitted');
////            continue;
////        }
//
//        $chartsArray = array();
//
//        return array(
//            'title' => "Single chart topic",
//            'chartsArray' => $chartsArray
//        );
//    }

    /**
     * charts belonging to a single organizational group. id - organizational group associated with the displayed charts
     *
     * @Route("/service/{id}", name="dashboard_single_service")
     * @Template("AppDashboardBundle/Dashboard/dashboard.html.twig")
     */
    public function singleServiceAction( Request $request, $id ) {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        if( !$id ) {
            $error = "Service id is not provided";
            $this->get('session')->getFlashBag()->add(
                'warning',
                $error
            );
            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->find($id);
        if( !$institution ) {
            $error = "Institution is not found by ID '".$id."'";
            $this->get('session')->getFlashBag()->add(
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
            'filter[projectSpecialty][]' => 0,
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
     *
     * @Route("/chart-type/{id}", name="dashboard_single_type")
     * @Template("AppDashboardBundle/Dashboard/dashboard.html.twig")
     */
    public function singleTypeAction( Request $request, $id ) {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        if( !$id ) {
            $error = "Chart type  id is not provided";
            $this->get('session')->getFlashBag()->add(
                'warning',
                $error
            );
            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        $chartType = $em->getRepository('AppDashboardBundle:ChartTypeList')->find($id);
        if( !$chartType ) {
            $error = "Chart type is not found by ID '".$id."'";
            $this->get('session')->getFlashBag()->add(
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
            'filter[projectSpecialty][]' => 0,
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
     *
     * @Route("/favorites/{id}", name="dashboard_single_favorite")
     * @Template("AppDashboardBundle/Dashboard/dashboard.html.twig")
     */
    public function singleFavoritesAction( Request $request, $id ) {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        if( !$id ) {
            $error = "Chart id is not provided";
            $this->get('session')->getFlashBag()->add(
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
            'filter[projectSpecialty][]' => 0,
            //'title' => $title
        );

        if( strpos($id, 'all-favorites-') !== false ) {
            //multiple charts
            $id = str_replace('all-favorites-','',$id); //now id=1-2-4-7
            $idsArr = explode('-',$id);

            $title = "Favorite charts";
            $redirectParams['title'] = $title;
            //$redirectParams['filter[chartType][0]'] = $chart->getAbbreviation();

            $counter = 1;
            foreach($idsArr as $chartId) {
                $chart = $em->getRepository('AppDashboardBundle:ChartList')->find($chartId);
                if( !$chart ) {
                    continue;
                }

                if( $this->isViewPermitted($chart) === false ) {
                    continue;
                }

                $redirectParams['filter[chartType]['.$counter.']'] = $chart->getAbbreviation();
                $counter++;
            }

        } else {
            //single chart
            $chart = $em->getRepository('AppDashboardBundle:ChartList')->find($id);
            if( !$chart ) {
                $error = "Chart is not found by ID '".$id."'";
                $this->get('session')->getFlashBag()->add(
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

        //redirect to home page with preset filter with chart types
        return $this->redirectToRoute(
            'dashboard_home',
            $redirectParams
        );
    }

    public function isViewPermitted($chart) {
        if( $this->get('security.authorization_checker')->isGranted('read', $chart) === true ) {
            return true;
        }

        //get admin email
        $userSecUtil = $this->container->get('user_security_utility');
        $adminemail = $userSecUtil->getSiteSettingParameter('siteEmail');

        $error = "You do not have access to this chart '".$chart->getName()."'. Please request access by contacting your site administrator $adminemail.";
        $this->get('session')->getFlashBag()->add(
            'warning',
            $error
        );

        //exit('Not permitted');
        return false;
    }

    /**
     * @Route("/dashboard-toggle-favorite", name="dashboard_toggle_favorite", methods={"POST"}, options={"expose"=true})
     * @Template("AppDashboardBundle/Dashboard/dashboard-choices.html.twig")
     */
    public function dashboardToggleFavoriteAction( Request $request )
    {
        if( $this->get('security.authorization_checker')->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $chartId = trim( $request->get('chartId') );

        $chart = $em->getRepository('AppDashboardBundle:ChartList')->find($chartId);
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