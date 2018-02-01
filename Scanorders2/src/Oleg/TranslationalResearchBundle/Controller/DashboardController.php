<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\TranslationalResearchBundle\Form\FilterDashboardType;
use Oleg\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date;


/**
 * @Route("dashboard")
 */
class DashboardController extends Controller
{

    /**
     * @Route("/pi-level/", name="translationalresearch_dashboard_pilevel")
     * @Route("/funded-level/", name="translationalresearch_dashboard_fundedlevel")
     * @Template("OlegTranslationalResearchBundle:Dashboard:pilevel.html.twig")
     */
    public function piLevelAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $routeName = $request->get('_route');
        $infos = array();

        //////////// Filter ////////////
        //default date range from today to 1 year back
//        $today = new \DateTime();
//        $todayStr = $today->format('m/d/Y');
        $params = array(
            //'startDate' => $today,
            //'endDate' => $today
        );
        $filterform = $this->createForm(FilterDashboardType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));
        $filterform->handleRequest($request);
        //////////// EOF Filter ////////////

        //$startDate = $filterform['startDate']->getData();
        //$endDate = $filterform['endDate']->getData();

//        $startDate = $filterform['startDate']->getData();
//        if( isset($startDate) ) {
//            echo "startDate=".$filterform['startDate']->format("m/d/Y")."<br>";
//            exit('dates are set');
//        } else {
//            exit('not set');
//        }
//        if( isset($filterform['startDate']) || isset($filterform['endDate']) ) {
//            //dates are set
//            //$startDate = $filterform['startDate']->getData();
//            //$endDate = $filterform['endDate']->getData();
//            //echo "startDate=".$startDate->format("m/d/Y")."<br>";
//            //exit('dates are set');
//        } else {
//            //exit('not set');
//            return $this->redirectToRoute(
//                $routeName,
//                array(
//                    'filter[startDate]' => $todayStr,
//                    'filter[endDate]' => $todayStr,
//                )
//            );
//        }

        $layoutArray = array(
            'height' => 600,
            'width' =>  600,
        );

//            var data = [{
//                values: [19, 26, 55],
//                labels: ['Residential', 'Non-Residential', 'Utility'],
//                type: 'pie'
//            }];

        $chartsArray = array();

        //$labels = array('Residential', 'Non-Residential', 'Utility');
        //$values = array(19, 26, 55);

        $projects = $this->getProjectPis($filterform);

        //Projects per PIs
        if( $routeName == "translationalresearch_dashboard_pilevel" ) {

            $title = "Dashboard: Projects per PI";
            $piProjectCountArr = array();
            $piTotalArr = array();

            foreach ($projects as $project) {
                $pis = $project->getPrincipalInvestigators();
                foreach ($pis as $pi) {
                    $userName = $pi->getUsernameOptimal();

                    //Projects per PI
                    if (isset($piProjectCountArr[$userName])) {
                        $count = $piProjectCountArr[$userName] + 1;
                    } else {
                        $count = 1;
                    }
                    $piProjectCountArr[$userName] = $count;

                    //Total($) per PI
                    $invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
                    if (isset($piTotalArr[$userName])) {
                        $total = $piTotalArr[$userName] + $invoicesInfos['total'];
                    } else {
                        $total = $invoicesInfos['total'];
                    }
                    $piTotalArr[$userName] = $total;
                }
            }

            //Projects per PI
            $chartsArray = $this->addChart( $chartsArray, $piTotalArr, "Number of Projects per PI");

            //Total per PI
            $chartsArray = $this->addChart( $chartsArray, $piTotalArr, "Total($) of Projects per PI");

        }

        if( $routeName == "translationalresearch_dashboard_fundedlevel" ) {
            $title = "Dashboard: Funded vs Un-Funded Projects";
            $layoutArray['title'] = "Number of Funded vs Un-Funded Projects";
            $nameValueArr = array();
            $fundedCount = 0;
            $unfundedCount = 0;
            foreach ($projects as $project) {
                $fundingNumber = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"If funded, please provide account number");
                if( $fundingNumber ) {
                    $fundedCount++;
                } else {
                    $unfundedCount++;
                }

                //Number of partially paid to Total Invoices
                $invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
                if (isset($piTotalArr[$userName])) {
                    $total = $piTotalArr[$userName] + $invoicesInfos['total'];
                } else {
                    $total = $invoicesInfos['total'];
                }
                $piTotalArr[$userName] = $total;
            }
            //echo "fundedCount=".$fundedCount."<br>";
            //echo "unfundedCount=".$unfundedCount."<br>";

            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';

            $labels = array('Number of Funded Projects','Number of Un-Funded Projects');
            $values = array($fundedCount,$unfundedCount);

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $dataArray[] = $chartDataArray;

            //$chartsArray['layout'] = $layoutArray;
            //$chartsArray['data'] = $dataArray;

            $chartsArray[] = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
        }



        return array(
            'infos' => $infos,
            'title' => $title,
            'filterform' => $filterform->createView(),
            //'dataArray' => $dataArray,
            //'layoutArray' => $layoutArray
            'chartsArray' => $chartsArray
        );
    }

    public function addChart( $chartsArray, $dataArr, $title, $type='pie' ) {

        if( count($dataArr) == 0 ) {
            return $chartsArray;
        }

        $labels = array();
        $values = array();
        $layoutArray['title'] = $title;

        foreach( $dataArr as $name => $value ) {
            if( $value ) {
                $labels[] = $name;
                $values[] = $value;
            }
        }

        if( count($values) == 0 ) {
            return $chartsArray;
        }

        $chartDataArray = array();
        $chartDataArray['values'] = $values;
        $chartDataArray['labels'] = $labels;
        $chartDataArray['type'] = $type;
        $dataArray[] = $chartDataArray;

        //$chartsArray['layout'] = $layoutArray;
        //$chartsArray['data'] = $dataArray;

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );

        return $chartsArray;
    }

//    /**
//     * @Route("/funded-level/", name="translationalresearch_dashboard_fundedlevel")
//     * @Template("OlegTranslationalResearchBundle:Dashboard:pilevel.html.twig")
//     */
//    public function fundedLevelAction( Request $request ) {
//
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }
//
//        $title = "Dashboard for Funded Project Level";
//        $infos = array();
//
//        //////////// Filter ////////////
//        $params = array();
//        $filterform = $this->createForm(FilterDashboardType::class, null,array(
//            'method' => 'GET',
//            'form_custom_value'=>$params
//        ));
//        $filterform->handleRequest($request);
//        //////////// EOF Filter ////////////
//
//        $params = array();
//        $filterform = $this->createForm(FilterDashboardType::class, null,array(
//            'method' => 'GET',
//            'form_custom_value'=>$params
//        ));
//
//        $filterform->handleRequest($request);
//
//        return array(
//            'infos' => $infos,
//            'title' => $title,
//        );
//    }


    public function getProjectPis($filterform) {
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('OlegTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        //$dql->where("project.state=:state");
        //$projects = $repository->findAll();
        //$query = $dql->getQuery();
        //echo "projectId=".$project->getId()."<br>";
        //echo "reviewId=".$reviewId."<br>";

        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $dqlParameters = array();

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
        }
        if( $endDate ) {
            $endDate->modify('+1 day');
            $dql->andWhere('project.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
        }

        $query = $em->createQuery($dql);

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        return $projects;
    }

}
