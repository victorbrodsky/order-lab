<?php

namespace App\TranslationalResearchBundle\Controller;

use App\TranslationalResearchBundle\Form\FilterDashboardType;
use App\UserdirectoryBundle\Util\LargeFileDownloader;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Date;


/**
 * @Route("dashboard")
 */
class DashboardTurnAroundStatController extends DashboardController
{

    private $width = 1200;
    private $height = 600;
    private $otherId = "All other [[otherStr]] combined";
    private $otherSearchStr = "All other ";

    /**
     * @Route("/graphs/turn-around-statistics", name="translationalresearch_dashboard_turn_around_stat")
     * @Template("AppTranslationalResearchBundle/Dashboard/dashboard-turn-around-stat.html.twig")
     */
    public function dashboardChoicesAction( Request $request )
    {

        if( $this->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        //exit("Under construction");

        $dashboardUtil = $this->container->get('transres_dashboard');
        $em = $this->getDoctrine()->getManager();

        //ini_set('memory_limit', '30000M'); //2GB
        //$memory_limit = ini_get('memory_limit');
        //echo "memory_limit=".$memory_limit."<br>";

        $filterform = $this->getFilter();
        $filterform->handleRequest($request);

//        $showLimited = $filterform['showLimited']->getData();
//        //echo "showLimited=".$showLimited."<br>";
//
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $projectSpecialty = $filterform['projectSpecialty']->getData();
        if( $projectSpecialty != 0 ) {
            $projectSpecialtyObject = $em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }

        $category = $filterform['category']->getData();

//        $chartTypes = $filterform['chartType']->getData();
//        foreach($chartTypes as $chartType) {
//            echo "chartType=".$chartType."<br>";
//        }

        $chartsArray = array();

        $averageDays = array();

        //get startDate and add 1 month until the date is less than endDate
        //$startDate = $filterform['startDate']->getData();
        //$endDate = $filterform['endDate']->getData();
        $startDate->modify( 'first day of last month' );
        do {
            $startDateLabel = $startDate->format('M-Y');
            $thisEndDate = clone $startDate;
            $thisEndDate->modify( 'first day of next month' );
            //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y").": ";
            $transRequests = $dashboardUtil->getRequestsByAdvanceFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$category,array("completed","completedNotified"));
            //$transRequests = $dashboardUtil->getRequestsByAdvanceFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$category);
            $startDate->modify( 'first day of next month' );

            //echo "<br>";
            //echo "transRequests=".count($transRequests)." (".$startDateLabel.")<br>";

            //$apcpResultStatArr = $this->getProjectRequestInvoiceChart($transRequests,$apcpResultStatArr,$startDateLabel);

            $daysTotal = 0;
            $count = 0;

            foreach($transRequests as $transRequest) {

                //Number of days to go from Submitted to Completed
                $submitted = $transRequest->getCreateDate();
                $updated = $transRequest->getUpdateDate();
                $dDiff = $submitted->diff($updated);
                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
                $days = $dDiff->days;
                //echo "days=".$days."<br>";
                $days = intval($days);
                if( $days > 0 ) {
                    $daysTotal = $daysTotal + intval($days);
                    $count++;
                }
            }

            if( $count > 0 ) {
                $avgDaysInt = round($daysTotal/$count);
                //echo "daysTotal=".$daysTotal."; count=".$count."<br>";
                //echo "average days=".round($daysTotal / $count)."<br>";
                //$averageDays[$startDateLabel] = $daysTotal;
                $averageDays[$startDateLabel] = $avgDaysInt;
            } else {
                $averageDays[$startDateLabel] = null;
            }


        } while( $startDate < $endDate );

        if( $category ) {
            //$categoryName = $this->tokenTruncate($category->getProductIdAndName(),50);
            $categoryName = $category->getProductId();
            $categoryStr = " (".$categoryName.")";
        } else {
            $categoryStr = null;
        }

        $chartsArray = $this->addChart( $chartsArray, $averageDays, "31. Turn-around Statistics: Average number of days to complete a Work Request".$categoryStr, "bar");


        return array(
            'title' => "Turn-around Statistics",
            'filterform' => $filterform->createView(),
            'chartsArray' => $chartsArray,
            'spinnerColor' => '#85c1e9',
//            'chartTypes' => $chartTypes
        );
    }

    public function getFilter( $showLimited=false, $withCompareType=false ) {
        $transresUtil = $this->container->get('transres_util');
        $dashboardUtil = $this->container->get('transres_dashboard');
        $em = $this->getDoctrine()->getManager();
        //////////// Filter ////////////
        //default date range from today to 1 year back
        $projectSpecialtiesWithAll = array('All'=>0);
        $projectSpecialties = $transresUtil->getTransResProjectSpecialties();
        foreach($projectSpecialties as $projectSpecialty) {
            $projectSpecialtiesWithAll[$projectSpecialty->getName()] = $projectSpecialty->getId();
        }

        //$categories (Product or Service)
//        $repository = $em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList');
//        $dql =  $repository->createQueryBuilder("project");
//        $dql->select('project');
//
//        $dql->where("project.state = 'final_approved' OR project.state = 'closed'");
//        $categories =

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
        //$dashboardUtil->getChartTypes();
        $params["chartType"] = false;
        //$params["chartTypes"] = $dashboardUtil->getChartTypes();


        $filterform = $this->createForm(FilterDashboardType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));
        //$filterform->handleRequest($request);
        //////////// EOF Filter ////////////

        return $filterform;
    }

//    public function getRequestsByAdvanceFilter($startDate, $endDate, $projectSpecialties, $category, $states=null, $addOneEndDay=true) {
//        $em = $this->getDoctrine()->getManager();
//        //$transresUtil = $this->container->get('transres_util');
//
//        $repository = $em->getRepository('AppTranslationalResearchBundle:TransResRequest');
//        $dql =  $repository->createQueryBuilder("request");
//        $dql->select('request');
//
//        //Exclude Work requests with status=Canceled and Draft
//        if( !$states ) {
//            $dql->where("request.progressState != 'draft' AND request.progressState != 'canceled'");
//        } else {
//            //$dql->where("request.progressState = '".$state."'");
//            foreach($states as $state) {
//                $stateArr[] = "request.progressState = '".$state."'";
//            }
//            if( count($stateArr) > 0 ) {
//                $dql->where("(".implode(" OR ",$stateArr).")");
//            }
//        }
//
//        $dqlParameters = array();
//
//        if( $startDate ) {
//            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
//            $dql->andWhere('request.createDate >= :startDate');
//            $dqlParameters['startDate'] = $startDate->format('Y-m-d'); //H:i:s
//        }
//        if( $endDate ) {
//            if( $addOneEndDay ) {
//                $endDate->modify('+1 day');
//            }
//            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
//            $dql->andWhere('request.createDate <= :endDate');
//            $dqlParameters['endDate'] = $endDate->format('Y-m-d'); //H:i:s
//        }
//
//        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
//            $dql->leftJoin('request.project','project');
//            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
//            $projectSpecialtyIdsArr = array();
//            $projectSpecialtyNamesArr = array();
//            foreach($projectSpecialties as $projectSpecialty) {
//                //echo "projectSpecialty=$projectSpecialty<br>";
//                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
//                $projectSpecialtyNamesArr[] = $projectSpecialty."";
//            }
//            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
//            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
//        }
//
//        if( $category ) {
//            $dql->leftJoin('request.products','products');
//            $dql->leftJoin('products.category','category');
//            $dql->andWhere("category.id = :categoryId");
//            $dqlParameters["categoryId"] = $category->getId();
//        }
//
//        $query = $em->createQuery($dql);
//
//        $query->setParameters($dqlParameters);
//        //echo "query=".$query->getSql()."<br>";
//
//        $projects = $query->getResult();
//
//        //echo implode(",",$projectSpecialtyNamesArr)." Projects=".count($projects)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";
//
//        return $projects;
//    }

}
