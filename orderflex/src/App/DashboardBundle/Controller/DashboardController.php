<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 10/11/2021
 * Time: 11:03 AM
 */

namespace App\DashboardBundle\Controller;


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
     * @Route("/single-chart/", name="translationalresearch_single_chart", options={"expose"=true})
     */
    public function singleOrigChartAction( Request $request )
    {

        if ($this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $dashboardUtil = $this->container->get('transres_dashboard');

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

        dump($chartsArray);
        exit('111');

        //$chartsArray = array();

        return array(
            'title' => "Single chart topic",
            'chartsArray' => $chartsArray
        );
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

}