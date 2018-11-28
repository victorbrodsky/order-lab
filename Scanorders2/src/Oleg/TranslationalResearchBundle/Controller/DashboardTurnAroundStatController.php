<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\TranslationalResearchBundle\Form\FilterDashboardType;
use Oleg\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
     * @Template("OlegTranslationalResearchBundle:Dashboard:dashboard-turn-around-stat.html.twig")
     */
    public function dashboardChoicesAction( Request $request )
    {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->container->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        exit("Under construction");

        //$userSecUtil = $this->container->get('user_security_utility');
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
            $projectSpecialtyObject = $em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }

        $category = $filterform['category']->getData();

//        $chartTypes = $filterform['chartType']->getData();
//        foreach($chartTypes as $chartType) {
//            echo "chartType=".$chartType."<br>";
//        }

        $transRequests = $this->getRequestsByFilter($startDate, $endDate, $projectSpecialtyObjects, $category);

        //12. Total Fees by Work Requests (Total $400K)
        $dataArray = array();
        $chartDataArray = array();
        $type = 'pie';
        $subtotalFees = $this->getNumberFormat(50);

        $layoutArray = array(
            'height' => $this->height,
            'width' =>  $this->width,
            'title' => "12. Total Fees by Work Requests (Total $".$subtotalFees.")"
        );

        $fundedTotalFees = $this->getNumberFormat(30);
        $unFundedTotalFees = $this->getNumberFormat(20);

        $labels = array('Funded : $'.$fundedTotalFees,'Non-Funded : $'.$unFundedTotalFees);
        $values = array($fundedTotalFees,$unFundedTotalFees);

        $chartDataArray['values'] = $values;
        $chartDataArray['labels'] = $labels;
        $chartDataArray['type'] = $type;
        $chartDataArray["textinfo"] = "value+percent";
        $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
        $chartDataArray['direction'] = 'clockwise';
        $dataArray[] = $chartDataArray;

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );
        $chartsArray[] = array('newline'=>true);
        /////////////////////

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
//        $repository = $em->getRepository('OlegTranslationalResearchBundle:RequestCategoryTypeList');
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

    public function getRequestsByFilter($startDate, $endDate, $projectSpecialties, $category, $addOneEndDay=true) {
        $em = $this->getDoctrine()->getManager();
        //$transresUtil = $this->container->get('transres_util');

        $repository = $em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        //Exclude Work requests with status=Canceled and Draft
        $dql->where("request.progressState != 'draft' AND request.progressState != 'canceled'");

        $dqlParameters = array();

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('request.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d'); //H:i:s
        }
        if( $endDate ) {
            if( $addOneEndDay ) {
                $endDate->modify('+1 day');
            }
            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('request.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d'); //H:i:s
        }

        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            $dql->leftJoin('request.project','project');
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

}
