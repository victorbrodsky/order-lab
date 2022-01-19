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


namespace App\DashboardBundle\Controller;

use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends OrderAbstractController
{
    /**
     * @Route("/about", name="dashboard_about_page")
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function aboutAction( Request $request ) {

        //testing
//        $dashboardUtil = $this->container->get('dashboard_util');
//        $filterTopics = $dashboardUtil->getFilterTopics();
//        dump($filterTopics);
//        exit();

//        $dashboardUtil = $this->container->get('dashboard_util');
//        $results = $dashboardUtil->getFilterFavorites();
//        dump($results);
//        exit('111');

//        $em = $this->getDoctrine()->getManager();
//        $res = "Test topics";
//        $dashboardInitUtil = $this->container->get('dashboard_init');
//        $chart = $em->getRepository('AppDashboardBundle:ChartList')->findOneByName('16. Total Fees of Items Ordered for Funded vs Non-Funded Projects');
//        if( !$chart ) {
//            exit('Chart not found');
//        }
//
//        $mapper = array(
//            'prefix' => 'App',
//            'bundleName' => 'DashboardBundle',
//            'className' => 'TopicList'
//        );
//        $root = $em->getRepository('AppDashboardBundle:TopicList')->findOneByName("All Charts");
//        if( !$root ) {
//            exit('No Root: "All Charts"');
//        }
//        if( $root->getLevel() != 0 ) {
//            exit('Root "All Charts" level is not 0');
//        }
//        $financial = $em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
//            "Financial",
//            $root,
//            $mapper
//        );
//        if( !$financial ) {
//            exit("Error: not found: Financial");
//        }
//        $financialTrp = $em->getRepository('AppDashboardBundle:TopicList')->findByChildnameAndParent(
//            "Translational Research",
//            $financial,
//            $mapper
//        );
//        if( !$financialTrp ) {
//            exit("Error: not found: Translational Research");
//        }
//        $charts1Arr = array();
//        $addChart1Arr = array("16. ");
//        $charts1Arr = $dashboardInitUtil->addSpecificTopic($chart,$financialTrp,$addChart1Arr,$charts1Arr);
//        $em->flush();
//        dump($charts1Arr);

        //$dashboardInitUtil = $this->container->get('dashboard_init');
        //$res = $dashboardInitUtil->initCharts($testing=true);

        //$dashboardInitUtil = $this->container->get('dashboard_init');
//        $res = $dashboardInitUtil->assignAdditionalTopicsAndRolesToCharts($testing=true);
//        $res = $dashboardInitUtil->assignTypesToCharts($testing=true);
        //$res = $dashboardInitUtil->assignRolesToCharts($testing=true);
        //exit($res);

        //testing permission error in session
//        $dashboardUtil = $this->container->get('dashboard_util');
//        $permissionErrorStr = NULL;
//        $em = $this->getDoctrine()->getManager();
//        $chartObject = $em->getRepository('AppDashboardBundle:ChartList')->find(60); //62.
//        if( !$chartObject ) {
//            $error = "Logical error: chart not found by id 60";
//            exit($error);
//        }
//        if( $this->get('security.authorization_checker')->isGranted('read', $chartObject) === false ) {
//            //$flashBagStr = $this->getSessionFlashBag();
//            //$permissionErrorStr = $dashboardUtil->getPermissionErrorSession($chartObject);
//        }
//        $permissionErrorStr = $dashboardUtil->getPermissionErrorSession($chartObject);
//        exit("Error: ".$permissionErrorStr);

//        $securityUtil = $this->container->get('user_security_utility');
//        $userRole = "ROLE_DASHBOARD_VICE_CHAIR_CLINICAL_PATHOLOGY";
//        $roleStr = $securityUtil->getRoleAliasByName($userRole);
//        if( !$roleStr ) {
//            echo "Role not found by [$userRole] <br>";
//        } else {
//            echo "Role found!!! by [$userRole] <br>";
//        }
//        exit();

//        $dashboardUtil = $this->container->get('dashboard_util');
//        $now = new \DateTime('now');
//        $endDate = $now;//->format('m/d/Y');
//        $thisEndDate = clone $endDate;
//        $startDate = $thisEndDate->modify('-1 month');//->format('m/d/Y');
//        $chartType = "chart-view-stat";
//        //echo "chartType=".$chartType."<br>";
//
//        $chart = $dashboardUtil->getChartByPartialName("1.");
//        exit("chart id=".$chart->getId());
//
//        $em = $this->getDoctrine()->getManager();
//        $chart = $em->getRepository('AppDashboardBundle:ChartList')->findOneByAbbreviation($chartType);
//        $count = $dashboardUtil->getChartViewCount($startDate,$endDate,$chart);
//        //exit($endDate->format('m/d/Y')."-".$startDate->format('m/d/Y').": ".$chart->getName().": count=$count");
//
//        $parametersArr = array(
//            'startDate' => $startDate->format('m/d/Y'),
//            'endDate' => $endDate->format('m/d/Y'),
//            'projectSpecialty' => NULL,
//            'showLimited' => NULL,
//            'chartType' => $chartType,
//            'productservice' => NULL,
//            'quantityLimit' => NULL
//        );
//
//        $chartsArray = $dashboardUtil->getDashboardChart(NULL,$parametersArr);
//        dump($chartsArray);
//        exit();

//        $endDate = new \DateTime('now');
//        $startDate = clone $endDate;
//        $startDate = $startDate->modify('-1 year');
//        $dashboardUtil = $this->container->get('dashboard_util');
//        $charts = $dashboardUtil->getViewedCharts($startDate,$endDate);
//        dump($charts);
//        exit();

        return array('sitename'=>$this->getParameter('dashboard.sitename'));
    }

//    /**
//     * @Route("/", name="dashboard_home")
//     * @Template("AppDashboardBundle/Default/index.html.twig")
//     */
//    public function indexAction( Request $request ) {
//        return array('sitename'=>$this->getParameter('dashboard.sitename'));
//    }


    /**
     * @Route("/test", name="dashboard_test")
     * @Template("AppDashboardBundle/Default/test.html.twig")
     */
    public function testAction( Request $request ) {

        exit('testAction disable');

        $testDataArr = array(1,2,3,4,5);

        return array(
            'sitename'=>$this->getParameter('dashboard.sitename'),
            'mytitle' => "This is my test page",
            'testData' => $testDataArr
        );
    }

    /**
     * NOT USED: moved to /list/init-dashboard-charts
     * 
     * @Route("/init-set-chart-list", name="dashboard_init_set_chart_list")
     * @Template("AppDashboardBundle/Default/test.html.twig")
     */
    public function setChartListAction( Request $request ) {

        exit('setChartListAction disable');

        $em = $this->getDoctrine()->getManager();

        //add the nine charts 55, 56, 57, 58, 59, 60, 61, 62, 63 (with IDs of 1 through 9) to the topic of “Site Utilization”,
        // associated with the Organizational Group of “Department of Pathology” under WCMC and “Department of Pathology” under NYP”,
        // visible to the these roles:
        //Dashboards-Site-Administrator-Department-Of-Pathology
        //Dashboards-Chairman-Department-Of-Pathology
        //Dashboards-Assistant-to-the-Chairman-Department-Of-Pathology
        //Dashboards-Administrator-Department-Of-Pathology
        //Dashboards-Associate-Administrator-Department-Of-Pathology
        //Dashboards-Financial-Administrator-Department-Of-Pathology

        //Add the Site Utilization charts into this list (accessible and downloadable):
//        Dashboards-Medical-Director-Pathology-Informatics-Department-Of-Pathology
//        Dashboards-Manager-Pathology-Informatics-Department-Of-Pathology
//        Dashboards-System-Administrator-Pathology-Informatics-Department-Of-Pathology
//        Dashboards-Software-Developer-Pathology-Informatics-Department-Of-Pathology

        $roles = array(
            "Dashboards-Site-Administrator-Department-Of-Pathology",
            "Dashboards-Chairman-Department-Of-Pathology",
            "Dashboards-Assistant-to-the-Chairman-Department-Of-Pathology",
            "Dashboards-Administrator-Department-Of-Pathology",
            "Dashboards-Associate-Administrator-Department-Of-Pathology",
            "Dashboards-Financial-Administrator-Department-Of-Pathology",

            "Dashboards-Medical-Director-Pathology-Informatics-Department-Of-Pathology",
            "Dashboards-Manager-Pathology-Informatics-Department-Of-Pathology",
            "Dashboards-System-Administrator-Pathology-Informatics-Department-Of-Pathology",
            "Dashboards-Software-Developer-Pathology-Informatics-Department-Of-Pathology",
        );

        $rolesArr = array();
        foreach($roles as $role) {
            $roleEntity = $em->getRepository('AppUserdirectoryBundle:Roles')->findOneByAbbreviation($role);
            if( !$roleEntity ) {
                exit("Can not find role by abbreviation '$role'");
            }

            $rolesArr[] = $roleEntity;
        }

        $siteUtilizationTopic = $em->getRepository('AppDashboardBundle:TopicList')->findOneByName("Site Utilization");
        if( !$siteUtilizationTopic ) {
            exit("TopicList not found by name 'Site Utilization'");
        }

        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution'
        );
        $wcmc = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
        if( !$wcmc ) {
            exit('No Institution: "WCM"');
        }
        if( $wcmc->getLevel() != 0 ) {
            exit('Institution "WCM" level is not 0');
        }
        $pathology = $em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
            "Pathology and Laboratory Medicine",
            $wcmc,
            $mapper
        );

        //55, 56, 57, 58, 59, 60, 61, 62, 63
        $names = array(55, 56, 57, 58, 59, 62, 63);

        $repository = $em->getRepository('AppDashboardBundle:ChartList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->leftJoin('list.topics','topics');

        $selectArr = array();
        foreach($names as $name) {
            $selectArr[] = "list.name LIKE '".$name."%'";
        }

        $selectWhere = implode(" OR ",$selectArr);

        $dql->where($selectWhere);
        $dql->andWhere("topics IS NULL");

        $query = $dql->getQuery();

        $charts = $query->getResult();
        echo "charts count=".count($charts)."<br>";
        $count = 0;

        foreach($charts as $chart) {
            echo "Process chart '$chart' <br>";

            if(0) {
                //add topic
                $chart->addTopic($siteUtilizationTopic);

                //add institution
                $chart->addInstitution($pathology);

                //assign roles accessRoles, downloadRoles
                foreach ($rolesArr as $role) {
                    $chart->addAccessRole($role);
                    $chart->addDownloadRole($role);
                }
            }

            //add topic
            $chart->addTopic($siteUtilizationTopic);

            $count++;
        }

        $em->flush();

        exit("EOF setChartListAction: count=$count");
    }

    /**
     * NOT USED: moved to /list/init-dashboard-charts
     * Set chart types (Line, Bar ...)
     *
     * @Route("/init-set-chart-type", name="dashboard_init_set_chart_type")
     * @Template("AppDashboardBundle/Default/test.html.twig")
     */
    public function setChartTypesAction( Request $request ) {

        exit('setChartTypesAction disable');

        $em = $this->getDoctrine()->getManager();
        $dashboardUtil = $this->container->get('dashboard_util');

        $now = new \DateTime('now');
        $endDate = $now->format('m/d/Y');
        $startDate = $now->modify('-1 year')->format('m/d/Y');

        $charts = $dashboardUtil->getChartTypes();

        $chartsArray = array();
        $chartTypeInvalidArr = array();

        $count = 0;

        foreach($charts as $chartType) {

            $chartType = $chartType."";
            $type = "";
            //echo "chartType=".$chartType."<br>";

            $parametersArr = array(
                'startDate' => $startDate,
                'endDate' => $endDate,
                'projectSpecialty' => NULL,
                'showLimited' => NULL,
                'chartType' => $chartType,
                'productservice' => NULL,
                'quantityLimit' => NULL
            );

            $chartsArray = $dashboardUtil->getDashboardChart(NULL,$parametersArr);
            //dump($chartsArray); exit('111');

            if( isset($chartsArray['data']) ) {
                $data = $chartsArray['data'];
                if( isset($data[0]['type']) ) {
                    $type = $data[0]['type'];
                    $type = ucfirst($type);
                    //echo $count.": chartType=".$chartType.", type=$type <br>";
                    //dump($data); exit('111');
                }
            } else {
                echo "Chart invalid: chartType=".$chartType."<br>";
                $chartTypeInvalidArr[] = $chartType;
                continue;
            }

            //find ChartList by $chartType
            $chartEntity = $em->getRepository('AppDashboardBundle:ChartList')->findOneByAbbreviation($chartType);
            if( !$chartEntity ) {
                exit("ChartList not find by abbreviation $chartType");
            }

            //check if chart type already set
            if( count($chartEntity->getChartTypes()) > 0 ) {
                echo $count.": $chartEntity already has type!!! <br>";
                continue;
            }

            //echo "type=$type <br>";
            //find ChartTypeList by $chartType
            $chartTypeEntity = $em->getRepository('AppDashboardBundle:ChartTypeList')->findOneByName($type);
            if( !$chartTypeEntity ) {
                exit("ChartTypeList not find by name $type");
            }

            $chartEntity->addChartType($chartTypeEntity);

            //testing
            if(1) {
                $thisChartTypeStr = NULL;
                foreach ($chartEntity->getChartTypes() as $thisChartType) {
                    $thisChartTypeStr = $thisChartTypeStr . $thisChartType->getName() . "";
                }
                echo "ID ".$chartEntity->getId()." - ". $chartEntity->getName(). " (" . $chartEntity->getAbbreviation() . "): ChartType=" . $thisChartTypeStr . "<br>";
            }


            if( $count > 200 ) {
                break;
            }

            $count++;
        }//foreach

        $em->flush();

        //$chartTypeInvalidArr
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $event = "Chart types are not set for invalid charts:<br>".implode('; ',$chartTypeInvalidArr);
        $userSecUtil = $this->get('user_security_utility');
        $sitename = $this->getParameter('dashboard.sitename');
        $userSecUtil->createUserEditEvent($sitename,$event,$user,null,$request,'Warning');

        //dump($chartsArray);
        exit('eof setChartTypesAction:<br>'.$event);
    }


    //@Template("AppDashboardBundle/Default/index.html.twig")
    /**
     * @Route("/react/{reactRouting}", name="dashboard_home_react", defaults={"reactRouting": null})
     */
    public function index()
    {
        return $this->render('AppDashboardBundle/Default/index.html.twig', array('testflag'=>'testflag1'));
    }

    /**
     * Test React
     * https://www.twilio.com/blog/building-a-single-page-application-with-symfony-php-and-react
     *
     * @Route("/api/users", name="dashboard_api_users", options={"expose"=true})
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getUsers()
    {
        $users = [
            [
                'id' => 1,
                'name' => 'Olususi Oluyemi',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation',
                'imageURL' => 'https://randomuser.me/api/portraits/women/50.jpg'
            ],
            [
                'id' => 2,
                'name' => 'Camila Terry',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation',
                'imageURL' => 'https://randomuser.me/api/portraits/men/42.jpg'
            ],
            [
                'id' => 3,
                'name' => 'Joel Williamson',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation',
                'imageURL' => 'https://randomuser.me/api/portraits/women/67.jpg'
            ],
            [
                'id' => 4,
                'name' => 'Deann Payne',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation',
                'imageURL' => 'https://randomuser.me/api/portraits/women/50.jpg'
            ],
            [
                'id' => 5,
                'name' => 'Donald Perkins',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation',
                'imageURL' => 'https://randomuser.me/api/portraits/men/89.jpg'
            ]
        ];

        $response = new Response();

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        $response->setContent(json_encode($users));

        return $response;
    }


    /**
     * @Route("/reset-chart-view-counter", name="dashboard_reset-chart-view-counter")
     */
    public function resetChartViewCounterAction( Request $request ) {

        exit('resetChartViewCounterAction disable');

        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');
        $endDate = $now;
        $startDate = $now->modify('-1 year');

        //get the date from event log
        $repository = $em->getRepository('AppUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");

        $dql->select("logger");

        $dql->innerJoin('logger.eventType', 'eventType');

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Dashboard Chart Viewed";

        //$dql->andWhere("logger.entityName = :entityName");
        //$dqlParameters['entityName'] = "ChartList";

        //$dql->andWhere('logger.creationdate >= :startDate');
        //$dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        //$dql->andWhere('logger.creationdate <= :endDate');
        //$dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        $query = $em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();
        echo "loggers=".count($loggers)."<br>";

        $count = 0;
        foreach($loggers as $logger) {
            $em->remove($logger);
            $count++;
        }

        $em->flush();

        exit('eof resetChartViewCounterAction: removed view eventlog='.$count);
    }

}
