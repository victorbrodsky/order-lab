<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 10/11/2021
 * Time: 11:03 AM
 */

namespace App\DashboardBundle\Controller;


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
     * single dashboard chart. id - chart ID
     *
     * @Route("/chart/{id}", name="dashboard_single_chart")
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

        //TODO: implement permission for a single chart
        if( $this->get('security.authorization_checker')->isGranted('ROLE_DASHBOARD_ADMIN') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $dashboardUtil = $this->container->get('dashboard_util');

        $chartsArray = $dashboardUtil->getDashboardChart($request);

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

        //return array('sitename'=>$this->getParameter('dashboard.sitename'));

        $em = $this->getDoctrine()->getManager();

        if( !$id ) {
            $error = "Topic name is not provided";
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


//        <a href="{{ path(translationalresearch_sitename~'_dashboard_choices',
//        {
//            'filter[startDate]':startDate,
//            'filter[endDate]':endDate,
//            'filter[projectSpecialty][]':0,
//            'filter[chartType][1]':'reminder-emails-per-month',
//            'filter[chartType][2]':'successful-logins-trp',
//            'filter[chartType][3]':'successful-logins-site',
//            'filter[chartType][4]':'successful-unique-logins-site-month',
//            'filter[chartType][5]':'successful-unique-logins-site-week',
//            'filter[chartType][6]':'new-and-edited-calllog-entries-per-day',
//            'filter[chartType][7]':'patients-calllog-per-day',
//        }) }}"
//            >Site utilization statistics</a>

        $redirectParams = array(
            //'filter[startDate]' => 'irb_review',
            //'filter[endDate]' => 'irb_missinginfo',
        );
        $count = 0;

        foreach ($chartsArray as $chart) {
            $redirectParams['filter[chartType]['.$count.']'] = $chart->getAbbreviation();
            $count++;
        }

        //redirect to dashboard_dashboard_choices with chart types
        return $this->redirectToRoute(
            'dashboard_dashboard_choices',
            $redirectParams
        );

        ///////////////////// EOF /////////////////////
        if( count($chartsArray) > 0 ) {
            $chart = $chartsArray[0];
        }

        $parametersArr = array(
            'startDate' => NULL,
            'endDate' => NULL,
            'projectSpecialty' => NULL,
            'showLimited' => NULL,
            'chartType' => $chart->getAbbreviation(),
            'productservice' => NULL,
            'quantityLimit' => NULL,
        );

        $dashboardUtil = $this->container->get('dashboard_util');
        $chartsArray = $dashboardUtil->getDashboardChart($parametersArr);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        //$response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setStatusCode(200);
        $response->setContent(json_encode($chartsArray));
        return $response;

        //$chartsArray = array();
//        return array(
//            'title' => "Single chart topic",
//            'chartsArray' => $chartsArray
//        );
    }
    /**
     * single dashboard topic. topicName - topic name
     *
     * @Route("/topic-name/{topicName}", name="dashboard_single_topic_name")
     * @Template("AppDashboardBundle/Dashboard/dashboard.html.twig")
     */
    public function singleTopicByNameAction( Request $request, $topicName ) {

        $em = $this->getDoctrine()->getManager();

        if( !$topicName ) {
            $error = "Topic name is not provided";
            $this->get('session')->getFlashBag()->add(
                'warning',
                $error
            );
            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        $topic = $em->getRepository('AppDashboardBundle:TopicList')->findByName($topicName);
        if( !$topic ) {
            $error = "Topic is not found by name '".$topicName."'";
            //throw new \Exception($error);

            $this->get('session')->getFlashBag()->add(
                'warning',
                $error
            );

            return $this->redirect( $this->generateUrl('dashboard_home') );
        }

        //find charts by $topic

        $chartsArray = array();

        return array(
            'title' => "Single chart topic",
            'chartsArray' => $chartsArray
        );
    }

    /**
     * charts belonging to a single organizational group. id - organizational group associated with the displayed charts
     *
     * @Route("/service/{id}", name="dashboard_single_service")
     * @Template("AppDashboardBundle/Dashboard/dashboard.html.twig")
     */
    public function singleServiceAction( Request $request, $id ) {

        //return array('sitename'=>$this->getParameter('dashboard.sitename'));

        $chartsArray = array();

        return array(
            'title' => "Single chart service",
            'chartsArray' => $chartsArray
        );
    }

    /**
     * charts belonging to a single type. id - chart type ID
     *
     * @Route("/chart-type/{id}", name="dashboard_single_type")
     * @Template("AppDashboardBundle/Dashboard/dashboard.html.twig")
     */
    public function singleTypeAction( Request $request, $id ) {

        //return array('sitename'=>$this->getParameter('dashboard.sitename'));

        $chartsArray = array();

        return array(
            'title' => "Single chart type",
            'chartsArray' => $chartsArray
        );
    }

    /**
     * charts belonging to a single favorite. id - user ID
     *
     * @Route("/favorites/{id}", name="dashboard_single_type")
     * @Template("AppDashboardBundle/Dashboard/dashboard.html.twig")
     */
    public function singleFavoritesAction( Request $request, $id ) {

        //return array('sitename'=>$this->getParameter('dashboard.sitename'));

        $chartsArray = array();

        return array(
            'title' => "Single chart type",
            'chartsArray' => $chartsArray
        );
    }



    /**
     * @Route("/graphs/", name="dashboard_dashboard_choices")
     * @Template("AppTranslationalResearchBundle/Dashboard/dashboard-choices.html.twig")
     */
    public function dashboardChoicesAction( Request $request )
    {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $filterform = $this->getFilter();
        $filterform->handleRequest($request);

        return array(
            'title' => "Translational Research Dashboard",
            'filterform' => $filterform->createView(),
            'chartsArray' => array(),
            'spinnerColor' => '#85c1e9',
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
        $params = array(
            //'startDate' => $today,
            //'endDate' => $today
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
        $dashboardUtil->getChartTypes();
        $params["chartType"] = true;
        $params["chartTypes"] = $dashboardUtil->getChartTypes();


        $filterform = $this->createForm(FilterDashboardType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));
        //////////// EOF Filter ////////////

        return $filterform;
    }

}