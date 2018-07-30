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
     * @Route("/pi-statistics/", name="translationalresearch_dashboard_pilevel")
     * @Route("/project-statistics/", name="translationalresearch_dashboard_projectlevel")
     * @Route("/invoice-statistics/", name="translationalresearch_dashboard_invoicelevel")
     * @Template("OlegTranslationalResearchBundle:Dashboard:dashboard.html.twig")
     */
    public function piStatisticsAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $routeName = $request->get('_route');
        $infos = array();

        //////////// Filter ////////////
        //default date range from today to 1 year back
        $params = array(
            //'startDate' => $today,
            //'endDate' => $today
            "projectSpecialty" => true
        );
        $filterform = $this->createForm(FilterDashboardType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));
        $filterform->handleRequest($request);
        //////////// EOF Filter ////////////

        $layoutArray = array(
            'height' => 600,
            'width' =>  600,
        );

//            var data = [{
//                values: [19, 26, 55],
//                labels: ['Residential', 'Non-Residential', 'Utility'],
//                type: 'pie'
//            }];
        //$labels = array('Residential', 'Non-Residential', 'Utility');
        //$values = array(19, 26, 55);

        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $projectSpecialties = $filterform['projectSpecialty']->getData();

        $chartsArray = array();

        $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialties);

        //Projects per PIs
        if( $routeName == "translationalresearch_dashboard_pilevel" ) {

            $title = "Dashboard: PI Statistics";
            $piProjectCountArr = array();
            $piTotalArr = array();
            $piRequestsArr = array();

            foreach($projects as $project) {
                $invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
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
                    //$invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
                    if (isset($piTotalArr[$userName])) {
                        $total = $piTotalArr[$userName] + $invoicesInfos['total'];
                    } else {
                        $total = $invoicesInfos['total'];
                    }
                    $piTotalArr[$userName] = $total;

                    //#Requests per PI
                    //$requestsCount = count($project->getRequests());
                    $requestsCount = $transresUtil->getNumberOfFundedRequests($project);
                    if (isset($piRequestsArr[$userName])) {
                        $total = $piRequestsArr[$userName] + $requestsCount;
                    } else {
                        $total = $requestsCount;
                    }
                    $piRequestsArr[$userName] = $total;
                }
            }

            ///////////// top $piProjectCountArr //////////////
            $piProjectCountTopArr = $this->getTopArray($piProjectCountArr);
            //Projects per PI
            $chartsArray = $this->addChart( $chartsArray, $piProjectCountTopArr, "Number of Projects per PI");
            ///////////// EOF top $piProjectCountArr //////////////

            //Total per PI
            $piTotalTopArr = $this->getTopArray($piTotalArr);
            $chartsArray = $this->addChart( $chartsArray, $piTotalTopArr, "Total($) of Projects per PI");

            //We likes to see which funded PI”s are using the TRP lab,
            // so we can try to capture a (Top Ten PI’s) and the percent of services they requested from TRP lab.
            $piRequestsTopArr = $this->getTopArray($piRequestsArr);
            $chartsArray = $this->addChart( $chartsArray, $piRequestsTopArr, "Number of Funded Requests per PI");

        }

        if( $routeName == "translationalresearch_dashboard_projectlevel" ) {
            $piTotalArr = array();
            $title = "Dashboard: Project Statistics";
            $layoutArray['title'] = "Number of Funded vs Un-Funded Projects";
            $nameValueArr = array();
            $fundedCount = 0;
            $unfundedCount = 0;

            foreach ($projects as $project) {
                //$fundingNumber = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"If funded, please provide account number");
                $fundingNumber = $project->getFundedAccountNumber();
                if( $fundingNumber ) {
                    $fundedCount++;
                } else {
                    $unfundedCount++;
                }

//                //Number of partially paid to Total Invoices
//                $invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
//                if (isset($piTotalArr[$userName])) {
//                    $total = $piTotalArr[$userName] + $invoicesInfos['total'];
//                } else {
//                    $total = $invoicesInfos['total'];
//                }
//                $piTotalArr[$userName] = $total;
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


        //Lastly, we would like to capture in a pie chart
        // the Totals (Billed – Paid – Outstanding) by either percentage or Total values.
        if( $routeName == "translationalresearch_dashboard_invoicelevel" ) {
            $title = "Dashboard: Invoice Statistics";
            $invoiceDataArr = array();
            $total = 0;
            $paid = 0;
            $due = 0;
            foreach($projects as $project) {
                $invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
                $total = $total + $invoicesInfos['total'];
                $paid = $paid + $invoicesInfos['paid'];
                $due = $due + $invoicesInfos['due'];
            }

            $invoiceDataArr['Total Billed($)'] = $total;
            $invoiceDataArr['Paid($)'] = $paid;
            $invoiceDataArr['Outstanding($)'] = $due;

            $chartsArray = $this->addChart( $chartsArray, $invoiceDataArr, "Invoices: Billed – Paid – Outstanding");
        }


        if( $routeName == "translationalresearch_dashboard_compare" ) {

            //Pie charts of the number of PIs in Hemepath vs AP/CP


            //number of Hematopathology vs AP/CP project requests as a Pie chart


            //3 bar graphs showing the number of project requests, work requests, invoices per month since
            // the beginning based on submission date: Total, Hematopatholgy, AP/CP


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


    /**
     * @Route("/comparison-statistics/", name="translationalresearch_dashboard_compare")
     * @Template("OlegTranslationalResearchBundle:Dashboard:dashboard.html.twig")
     */
    public function compareStatisticsAction( Request $request )
    {

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl($this->container->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $infos = array();

        //////////// Filter ////////////
        //default date range from today to 1 year back
        $params = array(
            //'startDate' => $today,
            //'endDate' => $today
            "projectSpecialty" => false
        );
        $filterform = $this->createForm(FilterDashboardType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));
        $filterform->handleRequest($request);
        //////////// EOF Filter ////////////

        $title = "Dashboard: Comparison Statistics";

        $layoutArray = array(
            'height' => 600,
            'width' => 600,
        );

//            var data = [{
//                values: [19, 26, 55],
//                labels: ['Residential', 'Non-Residential', 'Utility'],
//                type: 'pie'
//            }];
        //$labels = array('Residential', 'Non-Residential', 'Utility');
        //$values = array(19, 26, 55);

        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $specialtyApcpObject = $transresUtil->getSpecialtyObject("ap-cp");
        $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");

        $chartsArray = array();

        $apcpProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyApcpObject));
        $hemaProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyHemaObject));

        ///////////////// Pie charts of the number of PIs in Hemepath vs AP/CP /////////////////
        $apcpPisArr = array();
        $hemaPisArr = array();
        foreach($apcpProjects as $project) {
            foreach($project->getPrincipalInvestigators() as $pi) {
                $apcpPisArr[] = $pi->getId();
            }
        }
        foreach($hemaProjects as $project) {
            foreach($project->getPrincipalInvestigators() as $pi) {
                $hemaPisArr[] = $pi->getId();
            }
        }

        $apcpPisArr = array_unique($apcpPisArr);
        $hemaPisArr = array_unique($hemaPisArr);

        $pisDataArr = array();
        $pisDataArr['AP/CP PIs'] = count($apcpPisArr);
        $pisDataArr['Hematopathology PIs'] = count($hemaPisArr);

        $chartsArray = $this->addChart( $chartsArray, $pisDataArr, "Number of PIs in Hematopathology vs AP/CP");
        ///////////////// EOF Pie charts of the number of PIs in Hemepath vs AP/CP /////////////////



        ///////////////// number of Hematopathology vs AP/CP project requests as a Pie chart /////////////////
        $projectsDataArr = array();
        $projectsDataArr['AP/CP Project Requests'] = count($apcpProjects);
        $projectsDataArr['Hematopathology Project Requests'] = count($hemaProjects);

        $chartsArray = $this->addChart( $chartsArray, $projectsDataArr, "Number of Hematopathology vs AP/CP Project Requests");
        ///////////////// EOF number of Hematopathology vs AP/CP project requests as a Pie chart /////////////////



        //3 bar graphs showing the number of project requests, work requests, invoices per month since
        // the beginning based on submission date: Total, Hematopatholgy, AP/CP
        /////////// number of project requests, work requests, invoices per month  ///////////

        $apcpResultStatArr = array();
        $hemaResultStatArr = array();

        //get startDate and add 1 month until the date is less than endDate
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        //$filterform['startDate']->setData($startDate);
        //$apcpProjects = $this->getProjectsByFilter($filterform,"ap-cp");
        $startDate->modify( 'first day of last month' );
        do {
            //$startDate->modify( 'first day of last month' );
            //$filterform['startDate']->setData($startDate);
            $startDateLabel = $startDate->format('M-Y');
            $thisEndDate = clone $startDate;
            $thisEndDate->modify( 'first day of next month' );
            //$filterform['endDate']->setData($thisEndDate);
            //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
            //$barName = "Number of project requests, work requests, invoices per month ".$startDate->format("m/d/Y")." - ".$thisEndDate->format("m/d/Y");
            $apcpProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyApcpObject),false);
            $hemaProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyHemaObject),false);
            $startDate->modify( 'first day of next month' );

            //echo "<br>";
            //get requests, invoices
//            //$requestArr = array();
//            $invoiceCount = 0;
//            $requestCount = 10;
//            foreach($apcpProjects as $project) {
//                foreach($project->getRequests() as $request) {
//                    //$requestArr[] = $request;
//                    $requestCount++;
//                    $latestInvoice = $transresRequestUtil->getLatestInvoice($request);
//                    if( $latestInvoice ) {
//                        $invoiceCount++;
//                    }
//                }
//            }
//            echo "invoiceCount=$invoiceCount<br>";
//            $fullStatArr = array();
//            $fullStatArr['Project Requests'] = count($apcpProjects);
//            $fullStatArr['Work Requests'] = $requestCount;
//            $fullStatArr['Invoices'] = $invoiceCount;
//
//            $chartsArray = $this->addChart( $chartsArray, $fullStatArr, $barName);

            //echo "hemaProjects=".count($hemaProjects)." (".$startDateLabel.")<br>";

            $apcpResultStatArr = $this->getProjectRequestInvoiceChart($apcpProjects,$apcpResultStatArr,$startDateLabel);
            $hemaResultStatArr = $this->getProjectRequestInvoiceChart($hemaProjects,$hemaResultStatArr,$startDateLabel);

        } while( $startDate < $endDate );

        //$apcpProjects = $apcpProjectsData['projects'];
        //$apcpRequests = $apcpProjectsData['requests'];
        //$apcpInvoices = $apcpProjectsData['invoices'];

        //AP/CP
        $apcpProjectsData = array();
        foreach($apcpResultStatArr['projects'] as $date=>$value ) {
            $apcpProjectsData[$date] = $value;
        }
        //$chartsArray = $this->addChart( $chartsArray, $apcpProjectsData, "Number of AP/CP Project Requests by months", "bar");

        $apcpRequestsData = array();
        foreach($apcpResultStatArr['requests'] as $date=>$value ) {
            $apcpRequestsData[$date] = $value;
        }
        //$chartsArray = $this->addChart( $chartsArray, $apcpRequestsData, "Number of AP/CP Work Requests by months", "bar");

        $apcpInvoicesData = array();
        foreach($apcpResultStatArr['invoices'] as $date=>$value ) {
            $apcpInvoicesData[$date] = $value;
        }
        //$chartsArray = $this->addChart( $chartsArray, $apcpInvoicesData, "Number of AP/CP Invoices by months", "bar");

        //Hema
        $hemaProjectsData = array();
        foreach($hemaResultStatArr['projects'] as $date=>$value ) {
            $hemaProjectsData[$date] = $value;
        }
        //$chartsArray = $this->addChart( $chartsArray, $hemaProjectsData, "Number of Hematopathology Project Requests by months", "bar");

        $hemaRequestsData = array();
        foreach($hemaResultStatArr['requests'] as $date=>$value ) {
            $hemaRequestsData[$date] = $value;
        }
        //$chartsArray = $this->addChart( $chartsArray, $hemaRequestsData, "Number of Hematopathology Work Requests by months", "bar");

        $hemaInvoicesData = array();
        foreach($hemaResultStatArr['invoices'] as $date=>$value ) {
            $hemaInvoicesData[$date] = $value;
        }
        //$chartsArray = $this->addChart( $chartsArray, $hemaInvoicesData, "Number of Hematopathology Invoices by months", "bar");

        //Projects
        $combinedProjectsData = array();
        $combinedProjectsData['AP/CP'] = $apcpProjectsData;
        $combinedProjectsData['Hematopathology'] = $hemaProjectsData;
        $chartsArray = $this->addStackedChart( $chartsArray, $combinedProjectsData, "Number of AP/CP and Hematopathology Projects by months", "stack");

        //Requests
        $combinedRequestsData = array();
        $combinedRequestsData['AP/CP'] = $apcpRequestsData;
        $combinedRequestsData['Hematopathology'] = $hemaRequestsData;
        $chartsArray = $this->addStackedChart( $chartsArray, $combinedRequestsData, "Number of AP/CP and Hematopathology Requests by months", "stack");

        //Invoices
        $combinedInvoicesData = array();
        $combinedInvoicesData['AP/CP'] = $apcpInvoicesData;
        $combinedInvoicesData['Hematopathology'] = $hemaInvoicesData;
        $chartsArray = $this->addStackedChart( $chartsArray, $combinedInvoicesData, "Number of AP/CP and Hematopathology Invoices by months", "stack");

        //echo "<pre>";
        //print_r($apcpProjectsData);
        //echo "</pre>";

        //$projectRequestInvoiceDataArr[] = array();
        //$chartsArray = $this->addChart( $chartsArray, $apcpProjectsData, "Number of AP/CP Project Requests by months", "bar");

        //echo "chartsArray:<pre>";
        //print_r($chartsArray);
        //echo "</pre>";

        /////////// EOF number of project requests, work requests, invoices per month  ///////////


        return array(
            'infos' => $infos,
            'title' => $title,
            'filterform' => $filterform->createView(),
            //'dataArray' => $dataArray,
            //'layoutArray' => $layoutArray
            'chartsArray' => $chartsArray
        );
    }


    //select top 25, BUT make sure the other PIs are still shown as "Other"
    public function getTopArray($piProjectCountArr) {
        arsort($piProjectCountArr);
        $limit = 25;
        $count = 0;
        $piProjectCountTopArr = array();
        foreach($piProjectCountArr as $username=>$value) {
            //echo $username.": ".$count."<br>";
            if( $count < $limit ) {
                $piProjectCountTopArr[$username] = $value;
            } else {
                if (isset($piProjectCountTopArr['Other'])) {
                    $value = $piProjectCountTopArr['Other'] + $value;
                } else {
                    //$value = 1;
                }
                $piProjectCountTopArr['Other'] = $value;
            }
            $count++;
        }

        return $piProjectCountTopArr;
    }

    public function addChart( $chartsArray, $dataArr, $title, $type='pie' ) {

        if( count($dataArr) == 0 ) {
            return $chartsArray;
        }

        $labels = array();
        $values = array();
        $layoutArray['title'] = $title;

        foreach( $dataArr as $label => $value ) {
            if( $type == "bar" || $value ) {
                $labels[] = $label;
                $values[] = $value;
            }
        }

        if( count($values) == 0 ) {
            return $chartsArray;
        }

        $xAxis = "labels";
        $yAxis = "values";
        if( $type == "bar" || $type == "stack" ) {
            $xAxis = "x";
            $yAxis = "y";
        }

        $chartDataArray = array();
        $chartDataArray[$xAxis] = $labels;
        $chartDataArray[$yAxis] = $values;
        $chartDataArray['type'] = $type;
        $dataArray[] = $chartDataArray;

        //$chartsArray['layout'] = $layoutArray;
        //$chartsArray['data'] = $dataArray;

//        echo "<pre>";
//        print_r($dataArray);
//        echo "</pre>";

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );

        return $chartsArray;
    }

    public function addStackedChart( $chartsArray, $combinedDataArr, $title ) {

        if( count($combinedDataArr) == 0 ) {
            return $chartsArray;
        }

        $layoutArray['title'] = $title;
        $layoutArray['barmode'] = 'stack';

        $stackDataArray = array();
        $xAxis = "x";
        $yAxis = "y";

        foreach($combinedDataArr as $name=>$dataArr) {
            $chartDataArray = array();
            $labels = array();
            $values = array();
            foreach ($dataArr as $label => $value) {
                //if ($value) {
                    $labels[] = $label;
                    $values[] = $value;
                //}
            }

            //if( count($values) == 0 ) {
            //    continue;
            //}

            $chartDataArray[$xAxis] = $labels;
            $chartDataArray[$yAxis] = $values;
            $chartDataArray['name'] = $name;
            $chartDataArray['type'] = 'bar';

            $stackDataArray[] = $chartDataArray;
        }

        //echo "<pre>";
        //print_r($stackDataArray);
        //echo "</pre>";

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $stackDataArray
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


    public function getProjectsByFilter($startDate, $endDate, $projectSpecialties, $addOneEndDay=true) {
        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        $repository = $em->getRepository('OlegTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        //$dql->where("project.state=:state");
        //$projects = $repository->findAll();
        //$query = $dql->getQuery();
        //echo "projectId=".$project->getId()."<br>";
        //echo "reviewId=".$reviewId."<br>";

        //$startDate = $filterform['startDate']->getData();
        //$endDate = $filterform['endDate']->getData();
//        if( $projectSpecialtyAbbreviation == null ) {
//            $projectSpecialties = $filterform['projectSpecialty']->getData();
//        } else {
//            $specialtyObject = $transresUtil->getSpecialtyObject($projectSpecialtyAbbreviation);
//            $projectSpecialties[] = $specialtyObject;
//        }

        $dqlParameters = array();

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d'); //H:i:s
        }
        if( $endDate ) {
            if( $addOneEndDay ) {
                $endDate->modify('+1 day');
            }
            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d'); //H:i:s
        }

        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
            $projectSpecialtyIdsArr = array();
            $projectSpecialtyNamesArr = array();
            foreach($projectSpecialties as $projectSpecialty) {
                //echo "projectSpecialty=$projectSpecialty<br>";
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
                $projectSpecialtyNamesArr[] = $projectSpecialty."";
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        }

        $query = $em->createQuery($dql);

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        //echo implode(",",$projectSpecialtyNamesArr)." Projects=".count($projects)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";

        return $projects;
    }

    public function getProjectRequestInvoiceChart($apcpProjects,$resStatArr,$startDateLabel) {
        $transresRequestUtil = $this->container->get('transres_request_util');
        //get requests, invoices

       //$resStatArr['projects'];

        //$projectStatData = array();

        $invoiceCount = 0;
        $requestCount = 0;
        foreach($apcpProjects as $project) {
            foreach($project->getRequests() as $request) {
                //$requestArr[] = $request;
                $requestCount++;
                $latestInvoice = $transresRequestUtil->getLatestInvoice($request);
                if( $latestInvoice ) {
                    $invoiceCount++;
                }
            }
        }
        //echo "invoiceCount=$invoiceCount<br>";
        //$fullStatArr = array();

        //$fullStatArr['projects'] = count($apcpProjects);
        //$fullStatArr['requests'] = $requestCount;
        //$fullStatArr['invoices'] = $invoiceCount;

        $resStatArr['projects'][$startDateLabel] = count($apcpProjects);
        $resStatArr['requests'][$startDateLabel] = $requestCount;
        $resStatArr['invoices'][$startDateLabel] = $invoiceCount;

        return $resStatArr;
    }

}
