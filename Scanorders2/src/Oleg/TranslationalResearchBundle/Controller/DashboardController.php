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
class DashboardController extends Controller
{

    private $width = 1200;
    private $height = 600;
    private $otherId = "All other [[otherStr]] combined";
    private $otherSearchStr = "All other ";

    /**
     * @Route("/graphs/", name="translationalresearch_dashboard_choices")
     * @Template("OlegTranslationalResearchBundle:Dashboard:dashboard-choices.html.twig")
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

        //testing
//        $dashboardUtil = $this->container->get('transres_dashboard');
//        $em = $this->getDoctrine()->getManager();
//        $invoices = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findAll();
//        foreach($invoices as $invoice) {
//            $issuedDate = $dashboardUtil->getInvoiceIssuedDate($invoice);
//            if( $issuedDate ) {
//                echo "issuedDate=" . $issuedDate->format('Y-m-d H:i:s') . "<br>";
//            }
//        }

        //$userSecUtil = $this->container->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();

        //ini_set('memory_limit', '30000M'); //2GB
        //$memory_limit = ini_get('memory_limit');
        //echo "memory_limit=".$memory_limit."<br>";

        $filterform = $this->getFilter();
        $filterform->handleRequest($request);

//        $showLimited = $filterform['showLimited']->getData();
//        //echo "showLimited=".$showLimited."<br>";
//
//        $startDate = $filterform['startDate']->getData();
//        $endDate = $filterform['endDate']->getData();
//        $projectSpecialty = $filterform['projectSpecialty']->getData();
//        if( $projectSpecialty != 0 ) {
//            $projectSpecialtyObject = $em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
//            $projectSpecialtyObjects[] = $projectSpecialtyObject;
//        }

//        $chartTypes = $filterform['chartType']->getData();
//        foreach($chartTypes as $chartType) {
//            echo "chartType=".$chartType."<br>";
//        }

        return array(
            'title' => "Translational Research Dashboard",
            'filterform' => $filterform->createView(),
            'chartsArray' => array(),
            'spinnerColor' => '#85c1e9',
//            'chartTypes' => $chartTypes
        );
    }

    /**
     * @Route("/single-chart/", name="translationalresearch_single_chart", options={"expose"=true})
     */
    public function singleChartAction( Request $request )
    {

        if ($this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->container->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $dashboardUtil = $this->container->get('transres_dashboard');

        $chartsArray = $dashboardUtil->getDashboardChart($request);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($chartsArray));
        return $response;
    }

    public function getFilter( $showLimited=false, $withCompareType=false ) {
        $transresUtil = $this->container->get('transres_util');
        $dashboardUtil = $this->container->get('transres_dashboard');
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
            "showLimited" => true
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
        //$filterform->handleRequest($request);
        //////////// EOF Filter ////////////

        return $filterform;
    }





    public function getNumberFormat($number,$digits=null) {
        return number_format($number,$digits);
    }

    public function getOtherStr( $showLimited, $otherPrefix ) {
        if( $showLimited ) {
            return false; //show top ten only without others
        }
        return $otherPrefix;
    }

    //select top 10, BUT make sure the other PIs are still shown as "Other"
    public function getTopArray($piProjectCountArr, $showOthers=false, $descriptionArr=array(), $maxLen=50) {
        arsort($piProjectCountArr);
        $limit = 10;
        //$limit = 3;
        //$showOthers = true;
        //$otherId = "All other $showOthers combined";
        $otherId = str_replace("[[otherStr]]",$showOthers,$this->otherId);

        $count = 1;
        $piProjectCountTopArr = array();
        foreach($piProjectCountArr as $username=>$value) {
            //echo $username.": ".$count."<br>";
            if( $count <= $limit || !$limit ) {
                if( $value && $value != 0 ) {
                    //echo "add value=".$value."<br>";
                    $piProjectCountTopArr[$username] = $value;
                }
            } else {
                if( $showOthers !== false ) {
                    //echo "show Others <br>";
                    if (isset($piProjectCountTopArr[$otherId])) {
                        $value = $piProjectCountTopArr[$otherId] + $value;
                    } else {
                        //$value = 1;
                    }
                    $piProjectCountTopArr[$otherId] = $value;
                }
            }
            $count++;
        }

        if( $maxLen ) {
            $piProjectCountTopShortArr = array();
            foreach($piProjectCountTopArr as $index=>$value) {
                $index = $this->tokenTruncate($index,$maxLen);

                $descr = array();
                foreach($descriptionArr as $descriptionSingleArr) {
                    $descrPrefix = $descriptionSingleArr[0];
                    $descrFirstPrefix = $descriptionSingleArr[1];
                    $descrColor = $descriptionSingleArr[2];
                    $descrValueArr = $descriptionSingleArr[3];
                    $descrValue = $descrValueArr[$index];
                    if( $descrValue ) {
                        if( $descrColor ) {
                            $descr[] = '<span style="color:'.$descrColor.'">'.$descrPrefix . $descrValue.'</span>';
                        } else {
                            $descr[] = $descrPrefix . $descrValue;
                        }
                    }
                }

                if( count($descr) > 0 ) {
                    $index = $index . " " . $descrFirstPrefix . $value . " (" . implode(", ",$descr) . ")";
                }

                $piProjectCountTopShortArr[$index] = $value;
            }
            return $piProjectCountTopShortArr;
        }

        return $piProjectCountTopArr;
    }
    public function  getTopMultiArray($piProjectCountArr, $showOthers=false, $descriptionArr=array(), $maxLen=50) {
        //arsort($piProjectCountArr);
        usort($piProjectCountArr, function($a, $b) {
            return $b['value'] - $a['value'];
        });

//        echo "<pre>";
//        print_r($piProjectCountArr);
//        echo "</pre>";

        $limit = 10;
        //$limit = 3;
        //$showOthers = true;

        //$otherId = "All other $showOthers combined";
        $otherId = str_replace("[[otherStr]]",$showOthers,$this->otherId);

        $otherObjectids = array();

        $count = 1;
        $piProjectCountTopArr = array();
        foreach($piProjectCountArr as $id=>$arr) {
            $value = $arr['value'];
            $label = $arr['label'];
            $objectid = $arr['objectid'];
            $showPath = $arr['show-path'];
            $pi = $arr['pi'];
            //echo "value=".$value."<br>";
            //echo $username.": ".$count."<br>";
            if( $value && $value != 0 ) {
                if ($count <= $limit || !$limit) {
                    $piProjectCountTopArr[$id]['value'] = $value;
                    $piProjectCountTopArr[$id]['label'] = $label;
                    $piProjectCountTopArr[$id]['show-path'] = $showPath;
                    $piProjectCountTopArr[$id]['objectid'] = $objectid;
                    $piProjectCountTopArr[$id]['pi'] = $pi;
                } else {
                    if( $showOthers !== false ) {
                        //echo "show Others <br>";
                        if (isset($piProjectCountTopArr[$otherId]) && isset($piProjectCountTopArr[$otherId]['value'])) {
                            $thisValue = $piProjectCountTopArr[$otherId]['value'] + $value;
                        } else {
                            $thisValue = $value;
                        }
                        //echo $label.": ".$value."=>".$thisValue."<br>";
                        $piProjectCountTopArr[$otherId]['value'] = $thisValue;
                        $piProjectCountTopArr[$otherId]['label'] = $otherId;
                        $piProjectCountTopArr[$otherId]['show-path'] = $showPath;
                        $piProjectCountTopArr[$otherId]['objectid'] = null;
                        $piProjectCountTopArr[$otherId]['pi'] = $pi;
                        $otherObjectids[] = $objectid;
                    }
                }
            }
            $count++;
        }

        if( $showOthers ) {
            $piProjectCountTopArr[$otherId]['objectid'] = $otherObjectids;
        }

        if( $maxLen ) {
            $piProjectCountTopShortArr = array();
            foreach($piProjectCountTopArr as $id=>$arr) {
                $value = $arr['value'];
                $label = $arr['label'];
                $showPath = $arr['show-path'];
                $pi = $arr['pi'];
                $objectid = $arr['objectid'];
                //echo "objectid=".$objectid."<br>";
                $label = $this->tokenTruncate($label,$maxLen);
                $piProjectCountTopShortArr[$id]['value'] = $value;
                $piProjectCountTopShortArr[$id]['label'] = $label;
                $piProjectCountTopShortArr[$id]['show-path'] = $showPath;
                $piProjectCountTopShortArr[$id]['objectid'] = $objectid;
                $piProjectCountTopShortArr[$id]['pi'] = $pi;
            }
            return $piProjectCountTopShortArr;
        }

        return $piProjectCountTopArr;
    }

    public function tokenTruncate($string, $your_desired_width) {
        $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
        $parts_count = count($parts);

        $postfix = null;
        $length = 0;
        $last_part = 0;
        for (; $last_part < $parts_count; ++$last_part) {
            $length += strlen($parts[$last_part]);
            if ($length > $your_desired_width) {
                $postfix = "...";
                break;
            }
        }

        $res = implode(array_slice($parts, 0, $last_part));
        $res = trim($res) . $postfix;
        //$res = $res . $postfix;
        //echo "res=[".$res."]<br>";

        return $res;    //implode(array_slice($parts, 0, $last_part)).$postfix;
    }

    public function attachSecondValueToFirstLabel($firstArr,$secondArr,$prefix) {
        $resArr = array();
        foreach($firstArr as $index=>$value) {
            $index = $index . " " . $prefix . $secondArr[$index];
            $resArr[$index] = $value;
        }
        return $resArr;
    }

    public function addChart( $chartsArray, $dataArr, $title, $type='pie', $layoutArray=null, $valuePrefixLabel=null ) {

        if( count($dataArr) == 0 ) {
            return $chartsArray;
        }

        $labels = array();
        $values = array();
        //$text = array();

        if( !$layoutArray ) {
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );
        }

        if( $title ) {
            $layoutArray['title'] = $title;
        }

        foreach( $dataArr as $label => $value ) {
            if( $type == "bar" || ($value && $value != 0) ) {
                if( $valuePrefixLabel && $value ) {
                    $label = $label . " " . $valuePrefixLabel . $value;
                }
                $labels[] = $label;
                $values[] = $value;
                //$text[] = $value;
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

        //$chartDataArray["text"] = "111";
        $chartDataArray["textinfo"] = "value+percent";
        //hoverinfo: label+text+value+percent
        $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
        $chartDataArray['direction'] = 'clockwise';

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

        $chartsArray[] = array('newline'=>true);

        return $chartsArray;
    }
    public function addChartByMultiArray( $chartsArray, $dataArr, $filterArr, $title, $type='pie', $layoutArray=null, $valuePrefixLabel=null ) {

        if( count($dataArr) == 0 ) {
            return $chartsArray;
        }

        $startDate = $filterArr['startDate'];
        $endDate = $filterArr['endDate'];
        $projectSpecialtyObjects = $filterArr['projectSpecialtyObjects'];
        $funded = $filterArr['funded'];

//        $projectId = null;
//        if( isset($filterArr['funded']) ) {
//            $projectId = $filterArr['projectId'];
//        }

        if( $startDate ) {
            $startDateStr = $startDate->format('m/d/Y');
        }
        if( $endDate ) {
            $endDateStr = $endDate->format('m/d/Y');
        }

//        echo "<pre>";
//        print_r($dataArr);
//        echo "</pre>";

        $labels = array();
        $values = array();
        //$text = array();

        if( !$layoutArray ) {
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );
        }

        if( $title ) {
            $layoutArray['title'] = $title;
        }

        foreach( $dataArr as $id=>$arr ) {
            $value = $arr['value'];
            $label = $arr['label'];
            $showPath = $arr['show-path'];
            $objectid = $arr['objectid'];
            $pi = $arr['pi'];
            $link = null;
            if( $type == "bar" || ($value && $value != 0) ) {
                if( $valuePrefixLabel && $value ) {
                    $label = $label . " " . $valuePrefixLabel . $value;
                }

                if( $showPath == 'project' ) {

                    $linkFilterArr = array(
                        'filter[state][0]' => 'final_approved',
                        'filter[state][1]' => 'closed',
                        'filter[startDate]' => $startDateStr,
                        'filter[endDate]' => $endDateStr,
                        'filter[]' => $projectSpecialtyObjects
                    );

                    if( $funded === true ) {
                        $linkFilterArr['filter[fundingType]'] = 'Funded';
                    }
                    if( $funded === false ) {
                        $linkFilterArr['filter[fundingType]'] = 'Non-Funded';
                    }

                    if( count($projectSpecialtyObjects) > 0 ) {
                        $projectSpecialtyObject = $projectSpecialtyObjects[0];
                        $linkFilterArr['filter[projectSpecialty]'] = $projectSpecialtyObject->getId();
                    }

                    if( strpos($id, $this->otherSearchStr) !== false && is_array($objectid) ) {
                        $userIndex = 0;
                        foreach($objectid as $thisObjectid) {
                            $linkFilterArr['filter[principalInvestigators]['.$userIndex.']'] = $thisObjectid;
                            $userIndex++;
                        }
                    } else {
                        $linkFilterArr['filter[principalInvestigators][]'] = $objectid;
                    }

                    $link = $this->container->get('router')->generate(
                        'translationalresearch_project_index',
                        $linkFilterArr,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    //$linkLabel = "link";
                    //$label = '<font color="red">'.$label.'</font>';
                    //$label = '<a target="_blank" href="'.$link.'">'.$label.'</a>';
                    //$label = $label . " " . $link;
                }

                if( $showPath == 'request' ) {

                    $linkFilterArr = array(
                        'filter[project]' => $objectid,
                        'filter[progressState][0]' => 'active',
                        'filter[progressState][1]' => 'completed',
                        'filter[progressState][2]' => 'completedNotified',
                        'filter[progressState][3]' => 'pendingInvestigatorInput',
                        'filter[progressState][4]' => 'pendingHistology',
                        'filter[progressState][5]' => 'pendingImmunohistochemistry',
                        'filter[progressState][6]' => 'pendingMolecular',
                        'filter[progressState][7]' => 'pendingCaseRetrieval',
                        'filter[progressState][8]' => 'pendingTissueMicroArray',
                        'filter[progressState][9]' => 'pendingSlideScanning',
                        'filter[startDate]' => $startDateStr,
                        'filter[endDate]' => $endDateStr
                    );

                    if( $funded === true ) {
                        $linkFilterArr['filter[fundingType]'] = 'Funded';
                    }
                    if( $funded === false ) {
                        $linkFilterArr['filter[fundingType]'] = 'Non-Funded';
                    }

                    if( count($projectSpecialtyObjects) > 0 ) {
                        $projectSpecialtyObject = $projectSpecialtyObjects[0];
                        $linkFilterArr['filter[projectSpecialty]'] = $projectSpecialtyObject->getId();
                    }

                    if( strpos($id, $this->otherSearchStr) !== false ) {
                        $linkFilterArr = null;
                    } else {
                        if( is_array($pi) ) {
                            $userIndex = 0;
                            foreach($pi as $thisPi) {
                                $linkFilterArr['filter[principalInvestigators]['.$userIndex.']'] = $thisPi;
                                $userIndex++;
                            }
                        } else {
                            $linkFilterArr['filter[principalInvestigators][]'] = $pi;
                        }
                    }

                    if( $linkFilterArr ) {
                        //echo "### $label<br>";
                        $link = $this->container->get('router')->generate(
                            'translationalresearch_request_index_filter',
                            $linkFilterArr,
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                    }
                    //$linkLabel = "link";
                    //$label = '<font color="red">'.$label.'</font>';
                    //$label = '<a target="_blank" href="'.$link.'">'.$label.'</a>';
                    //$label = $label . " " . $link;
                }

                $labels[] = $label;
                $values[] = $value;
                $links[] = $link;
                //$text[] = $value;
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

        $chartDataArray["links"] = $links;
        //$chartDataArray["text"] = "111";
        $chartDataArray["textinfo"] = "value+percent";
        //hoverinfo: label+text+value+percent
        $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
        $chartDataArray['direction'] = 'clockwise';

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

        $chartsArray[] = array('newline'=>true);

        return $chartsArray;
    }

    public function addStackedChart( $chartsArray, $combinedDataArr, $title ) {

        if( count($combinedDataArr) == 0 ) {
            return $chartsArray;
        }

        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
            'margin' => array('b'=>200)
//            'yaxis' => array(
//                'automargin' => true
//            ),
//            'xaxis' => array(
//                'automargin' => true,
//            ),
        );

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
        $chartsArray[] = array('newline'=>true);

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
        //$transresUtil = $this->container->get('transres_util');

        $repository = $em->getRepository('OlegTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->where("project.state = 'final_approved' OR project.state = 'closed'");

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

    public function isUserBelongsToInstitution($user, $parentInstitution) {
        if( !$parentInstitution ) {
            return false;
        }

        $em = $this->getDoctrine()->getManager();

        //get all user's institutions
        $institutions = $user->getInstitutions();

        foreach($institutions as $institution) {
            if( $em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode($parentInstitution,$institution) ) {
                return true;
            }
        }

        return false;
    }

    public function getRequestsByFilter($startDate, $endDate, $projectSpecialties, $addOneEndDay=true) {
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

    public function getInvoicesByFilter($startDate, $endDate, $projectSpecialties, $addOneEndDay=true, $compareType) {
        $em = $this->getDoctrine()->getManager();
        //$transresUtil = $this->container->get('transres_util');

        $repository = $em->getRepository('OlegTranslationalResearchBundle:Invoice');
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');
        $dql->leftJoin('invoice.transresRequest','request');

        //Exclude Work requests with status=Canceled and Draft
        $dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE AND invoice.status != 'canceled'");

        $dqlParameters = array();

        if( $startDate ) {
            //$startDateCriterion = 'request.createDate >= :startDate';
            if( $compareType == 'work request submission date' ) {
                $startDateCriterion = 'request.createDate >= :startDate';
            } elseif( $compareType == 'last invoice generation date' ) {
                $startDateCriterion = 'invoice.createDate >= :startDate';
            } elseif( $compareType == "date when status changed to paid in full" ) {
                $startDateCriterion = 'invoice.paidDate >= :startDate';
            } else {
                $startDateCriterion = 'request.createDate >= :startDate';
            }
            //echo "startDateCriterion=$startDateCriterion <br>";
            $dql->andWhere($startDateCriterion);
            $dqlParameters['startDate'] = $startDate->format('Y-m-d'); //H:i:s
        }
        if( $endDate ) {
            if( $addOneEndDay ) {
                $endDate->modify('+1 day');
            }
            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('request.createDate <= :endDate');

            //$endDateCriterion = 'request.createDate <= :endDate';
            if( $compareType == 'work request submission date' ) {
                $endDateCriterion = 'request.createDate <= :endDate';
            } elseif( $compareType == 'last invoice generation date' ) {
                $endDateCriterion = 'invoice.createDate <= :endDate';
            } elseif( $compareType == "date when status changed to paid in full" ) {
                $endDateCriterion = 'invoice.paidDate <= :endDate';
            } else {
                $endDateCriterion = 'request.createDate <= :endDate';
            }
            //echo "endDateCriterion=$endDateCriterion <br>";
            $dql->andWhere($endDateCriterion);

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



    /**
     * http://127.0.0.1/order/translational-research/dashboard/graphs/populate-dates
     *
     * @Route("/graphs/populate-dates", name="translationalresearch_dashboard_populate_dates")
     */
    public function dashboardPopulateDatesAction( Request $request )
    {
        exit("Disabled Invoice's IssuedDate");

        if( $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->container->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        //testing
        $dashboardUtil = $this->container->get('transres_dashboard');
        $em = $this->getDoctrine()->getManager();

        //$invoices = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findAll();
        $invoiceStates = array("Paid in Full","Paid Partially");
        $repository = $em->getRepository('OlegTranslationalResearchBundle:Invoice');
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');
        $dql->leftJoin('invoice.transresRequest','request');
        //Exclude Work requests with status=Canceled and Draft
        //$dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE AND invoice.status != 'canceled'");

        //$dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE");
        $dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE AND invoice.issuedDate IS NULL");

        foreach($invoiceStates as $state) {
            $stateArr[] = "invoice.status = '".$state."'";
        }
        if( count($stateArr) > 0 ) {
            //$dql->andWhere("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE AND (".implode(" OR ",$stateArr).")");
            //$dql->where("(".implode(" OR ",$stateArr).")");
            $dql->andWhere("(".implode(" OR ",$stateArr).")");
        }

        $query = $em->createQuery($dql);
        $invoices = $query->getResult();
        echo "Invoices count=".count($invoices)."<br>";

        $count = 0;
        foreach($invoices as $invoice) {
            $issuedDate = $dashboardUtil->getInvoiceIssuedDate($invoice);
            if( $issuedDate ) {
                echo $invoice->getOid()."(".$invoice->getCreateDate()->format('Y-m-d H:i:s')."): issuedDate=" . $issuedDate->format('Y-m-d H:i:s') . "<br>";
                //$invoice->setIssuedDate($issuedDate);
                //$em->flush($invoice);
                $count++;
            } else {
                echo $invoice->getOid()."(".$invoice->getCreateDate()->format('Y-m-d H:i:s')."): no issuedDate" . "<br>";
            }
        }

        exit("Exit populating dates: count=".$count);
    }

    /**
     * http://127.0.0.1/order/translational-research/dashboard/graphs/populate-dates-requests
     *
     * @Route("/graphs/populate-dates-requests", name="translationalresearch_dashboard_populate_dates_requests")
     */
    public function dashboardPopulateRequestDatesAction( Request $request )
    {
        exit("Disabled Request's CompletedDate");

        if( $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->container->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        //testing
        //$dashboardUtil = $this->container->get('transres_dashboard');
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        //$dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE");
        $dql->where("request.progressState = 'completedNotified' AND request.completedDate IS NULL");

        $query = $em->createQuery($dql);
        $requests = $query->getResult();
        echo "Request count=".count($requests)."<br>";

        $count = 0;
        foreach($requests as $thisRequest) {
            $completedDate = $this->getRequestCompletedDate($thisRequest);
            if( $completedDate ) {
                echo $thisRequest->getOid()."(".$thisRequest->getCreateDate()->format('Y-m-d H:i:s')."): issuedDate=" . $completedDate->format('Y-m-d H:i:s') . "<br>";
                //$thisRequest->setCompletedDate($completedDate);
                //$thisRequest->setCompletedDateSet(true);
                //$em->flush($thisRequest);
                $count++;
            } else {
                echo $thisRequest->getOid()."(".$thisRequest->getCreateDate()->format('Y-m-d H:i:s')."): no completedDate" . "<br>";
                //exit("exit: no date found");
            }
        }

        exit("Exit populating dates: count=".$count);
    }
    public function getRequestCompletedDate($request) {
        $em = $this->getDoctrine()->getManager();
        //get the date from event log
        $repository = $em->getRepository('OlegUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");
        //$dql->innerJoin('logger.eventType', 'eventType');
        //$dql->leftJoin('logger.objectType', 'objectType');
        //$dql->leftJoin('logger.site', 'site');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
        //$dql->where("logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());

        //Work Request ID APCP843-REQ16216 billing state has been changed to Invoiced, triggered by invoice status change to Unpaid/Issued
        $dql->where("logger.entityName = 'TransResRequest' AND logger.entityId = ".$request->getId());

        //$dql->andWhere("logger.event LIKE '%"."status changed to '/Unpaid/Issued"."%'"); //status changed to 'Unpaid/Issued'
        //$dql->andWhere("logger.event LIKE :eventStr OR logger.event LIKE :eventStr2");
        $dql->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");

        $dql->orderBy("logger.id","DESC");
        $query = $em->createQuery($dql);

        //The status of the work request APCP668-REQ16553 has been changed from 'Pending Histology' to 'Completed and Notified' by Susanna Mirabelli - sum2029 (WCM CWID)
        $search = "The status of the work request ".$request->getOid()." has been changed from";
        $search2 = " to 'Completed and Notified' by ";

        //$search = "Unpaid/Issued";
        //$search = "";
        //$search = "status changed to ";
        //$search2 = "status changed to 'Unpaid/Issued'";
        $query->setParameters(
            array(
                'eventStr' => '%'.$search.'%',
                'eventStr2' => '%'.$search2.'%'
            )
        );

        $loggers = $query->getResult();

        //2) try to use "Invoice PDF Issued" event "Invoice APCP668-REQ14079-V1 PDF has been sent by email ..."
        if( count($loggers) == 0 ) {
            $dql2 = $repository->createQueryBuilder("logger");
            $dql2->where("logger.entityName = 'TransResRequest' AND logger.entityId = ".$request->getId());
            $dql2->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");

            $dql2->orderBy("logger.id","DESC");
            $query2 = $em->createQuery($dql2);

            //Your request APCP668-REQ14079) for the project: (APCP668 (14541)) is completed. Please coordinate with Translational Research Program lab for material transportation.
            $search1 = "Your request ".$request->getOid();
            $search2 = " is completed.";
            $query2->setParameters(
                array(
                    'eventStr' => '%'.$search1.'%',
                    'eventStr2' => '%'.$search2.'%'
                )
            );

            $loggers = $query2->getResult();
        }

        //3)
        if( count($loggers) == 0 ) {
            $dql3 = $repository->createQueryBuilder("logger");
            $dql3->where("logger.entityName = 'TransResRequest' AND logger.entityId = ".$request->getId());
            $dql3->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2 AND logger.event LIKE :eventStr3");

            $dql3->orderBy("logger.id","DESC");
            $query3 = $em->createQuery($dql3);

            //The work for your request APCP845-REQ14084 ... has been completed.
            $requestOid = $request->getOid();
            $requestOid = str_replace("APCP","",$requestOid);
            $requestOid = str_replace("HP","",$requestOid);
            $requestOid = "P".$requestOid;
            $search1 = "The work for your request ";
            $search2 = $requestOid;
            //$search1 = $requestOid . " submitted on ";
            $search3 = " has been completed.";
            //echo "try 3: [$search1] AND [$search2] <br>";
            $query3->setParameters(
                array(
                    'eventStr' => '%'.$search1.'%',
                    'eventStr2' => '%'.$search2.'%',
                    'eventStr3' => '%'.$search3.'%'
                )
            );

            $loggers = $query3->getResult();
        }

        //4) Request APCP936-REQ14092 has been sent to Completed
        if( count($loggers) == 0 ) {
            $dql4 = $repository->createQueryBuilder("logger");
            $dql4->where("logger.entityName = 'TransResRequest' AND logger.entityId = ".$request->getId());
            $dql4->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");

            $dql4->orderBy("logger.id","DESC");
            $query4 = $em->createQuery($dql4);

            //The work for your request APCP845-REQ14084 ... has been completed.
            $requestOid = $request->getOid();
            $requestOid = str_replace("APCP","",$requestOid);
            $requestOid = str_replace("HP","",$requestOid);
            $requestOid = "P".$requestOid;
            $search1 = $requestOid;
            $search2 = " has been sent to Completed.";
            //echo "try 4: [$search1] AND [$search2] <br>";
            $query4->setParameters(
                array(
                    'eventStr' => '%'.$search1.'%',
                    'eventStr2' => '%'.$search2.'%',
                )
            );

            $loggers = $query4->getResult();
        }

        //5) Request APCP936-REQ14092 has been sent to Completed
        if( count($loggers) == 0 ) {
            $dql4 = $repository->createQueryBuilder("logger");
            $dql4->where("logger.entityName = 'TransResRequest' AND logger.entityId = ".$request->getId());
            $dql4->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");

            $dql4->orderBy("logger.id","DESC");
            $query4 = $em->createQuery($dql4);

            //Your request APCP874-REQ14095) for the project: (APCP874 (15019)) is completed.
            $requestOid = $request->getOid();
            $requestOid = str_replace("APCP","",$requestOid);
            $requestOid = str_replace("HP","",$requestOid);
            $requestOid = "P".$requestOid;
            $search1 = $requestOid;
            $search2 = " is completed.";
            //echo "try 5: [$search1] AND [$search2] <br>";
            $query4->setParameters(
                array(
                    'eventStr' => '%'.$search1.'%',
                    'eventStr2' => '%'.$search2.'%',
                )
            );

            $loggers = $query4->getResult();
        }

        //6) Work Request APCP1080-REQ16124 has been updated. The request's current status is 'Completed'.
        if( count($loggers) == 0 ) {
            $dql4 = $repository->createQueryBuilder("logger");
            $dql4->where("logger.entityName = 'TransResRequest' AND logger.entityId = ".$request->getId());
            $dql4->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");

            $dql4->orderBy("logger.id","DESC");
            $query4 = $em->createQuery($dql4);

            //Your request APCP874-REQ14095) for the project: (APCP874 (15019)) is completed.
            $requestOid = $request->getOid();
            $requestOid = str_replace("APCP","",$requestOid);
            $requestOid = str_replace("HP","",$requestOid);
            $requestOid = "P".$requestOid;
            $search1 = $requestOid . " has been updated.";
            $search2 = "The request's current status is 'Completed'.";
            //echo "try 6: [$search1] AND [$search2] <br>";
            $query4->setParameters(
                array(
                    'eventStr' => '%'.$search1.'%',
                    'eventStr2' => '%'.$search2.'%',
                )
            );

            $loggers = $query4->getResult();
        }

        //7) Work Request APCP1080-REQ16124 has been updated. The request's current status is 'Completed'.
        if( count($loggers) == 0 ) {
            $dql4 = $repository->createQueryBuilder("logger");
            $dql4->where("logger.entityName = 'TransResRequest' AND logger.entityId = ".$request->getId());
            $dql4->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");

            $dql4->orderBy("logger.id","DESC");
            $query4 = $em->createQuery($dql4);

            //Your request APCP874-REQ14095) for the project: (APCP874 (15019)) is completed.
            $requestOid = $request->getOid();
            $requestOid = str_replace("APCP","",$requestOid);
            $requestOid = str_replace("HP","",$requestOid);
            $requestOid = "P".$requestOid;
            $search1 = $requestOid . " has been updated.";
            $search2 = "The request's current status is 'Completed and Notified'.";
            echo "try 7: [$search1] AND [$search2] <br>";
            $query4->setParameters(
                array(
                    'eventStr' => '%'.$search1.'%',
                    'eventStr2' => '%'.$search2.'%',
                )
            );

            $loggers = $query4->getResult();
        }

        //echo $invoice->getOid().": loggers count=".count($loggers)."<br>";
        //foreach($loggers as $logger) {
        //    echo "logger.id=".$logger->getId()."; TransResRequest id=".$request->getId()."<br>";
        //}

        if( count($loggers) > 0 ) {
            $logger = $loggers[0];
            //echo "@@@ logger.id=".$logger->getId()."; TransResRequest id=".$request->getId()."<br>";
            $date = $logger->getCreationdate();
        } else {
            $date = null;
        }

        return $date;
    }

    /**
     * http://127.0.0.1/order/translational-research/dashboard/graphs/populate-dates-projects
     *
     * @Route("/graphs/populate-dates-projects", name="translationalresearch_dashboard_populate_dates_projects")
     */
    public function dashboardPopulateProjectDatesAction( Request $request )
    {
        //exit("Disabled Project's startReviewDate");

        if( $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->container->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        ini_set('max_execution_time', 600); //600 sec => 10 min

        //testing
        //$dashboardUtil = $this->container->get('transres_dashboard');
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('OlegTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        //$dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE");
        //"project.state = 'final_approved' OR project.state = 'closed'"
        $dql->where("(project.state = 'final_approved' OR project.state = 'closed') AND project.startReviewDate IS NULL");

        $dql->andWhere("project.approvalDate > '2018-07-01'");

        $query = $em->createQuery($dql);
        $projects = $query->getResult();
        echo "Project count=".count($projects)."<br>";

        $count = 0;
        foreach($projects as $project) {
            $startReviewDate = $this->getProjectStartReviewDate($project);
            if( $startReviewDate ) {
                echo $project->getOid()."(".$project->getCreateDate()->format('Y-m-d H:i:s')."): startReviewDate=" . $startReviewDate->format('Y-m-d H:i:s') . "<br>";
                //$project->setStartReviewDate($startReviewDate);
                //$em->flush($project);
                $count++;
            } else {
                echo $project->getOid()."(".$project->getCreateDate()->format('Y-m-d H:i:s')."): no startReviewDate" . "<br>";
                //exit("exit: no date found");
            }
        }

        exit("Exit populating dates: count=".$count);
    }
    public function getProjectStartReviewDate($project) {
        $em = $this->getDoctrine()->getManager();
        //get the date from event log
        $repository = $em->getRepository('OlegUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");
        //$dql->innerJoin('logger.eventType', 'eventType');
        //$dql->leftJoin('logger.objectType', 'objectType');
        //$dql->leftJoin('logger.site', 'site');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
        //$dql->where("logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());

        //Work Request ID APCP843-REQ16216 billing state has been changed to Invoiced, triggered by invoice status change to Unpaid/Issued
        $dql->where("logger.entityName = 'Project' AND logger.entityId = ".$project->getId());

        //$dql->andWhere("logger.event LIKE '%"."status changed to '/Unpaid/Issued"."%'"); //status changed to 'Unpaid/Issued'
        //$dql->andWhere("logger.event LIKE :eventStr OR logger.event LIKE :eventStr2");
        $dql->andWhere("logger.event LIKE :eventStr");

        $dql->orderBy("logger.id","DESC");
        $query = $em->createQuery($dql);

        //Project ID APCP3368 has been successfully updated and the status has been changed from 'Draft' to 'IRB Review'
        $projectOid = $project->getOid();
        $projectOid = str_replace("APCP","",$projectOid);
        $projectOid = str_replace("HP","",$projectOid);
        $projectOid = "P".$projectOid;
        $search = $projectOid." has been successfully updated and the status has been changed from 'Draft' to 'IRB Review'";
        //$search2 = " to 'Completed and Notified' by ";

        $query->setParameters(
            array(
                'eventStr' => '%'.$search.'%',
                //'eventStr2' => '%'.$search2.'%'
            )
        );

        $loggers = $query->getResult();


        //2) Thank you for your submission! Your project request has been received and assigned the following ID of APCP2202.
        if( count($loggers) == 0 ) {
            $dql = $repository->createQueryBuilder("logger");
            $dql->where("logger.entityName = 'Project' AND logger.entityId = ".$project->getId());
            $dql->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");

            $dql->orderBy("logger.id","DESC");
            $query = $em->createQuery($dql);

            $requestOid = $project->getOid();
            $requestOid = str_replace("APCP","",$requestOid);
            $requestOid = str_replace("HP","",$requestOid);
            $requestOid = "P".$requestOid;
            $search1 = "Thank you for your submission! Your project request has been received and assigned the following ID of ";
            $search2 = $requestOid;
            echo "try 2: [$search1] AND [$search2] <br>";
            $query->setParameters(
                array(
                    'eventStr' => '%'.$search1.'%',
                    'eventStr2' => '%'.$search2.'%',
                )
            );

            $loggers = $query->getResult();
        }

        //3) Thank you for your submission! Your project request has been assigned an ID of APCP2182 and will be reviewed.
        if( count($loggers) == 0 ) {
            $dql = $repository->createQueryBuilder("logger");
            $dql->where("logger.entityName = 'Project' AND logger.entityId = ".$project->getId());
            $dql->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");

            $dql->orderBy("logger.id","DESC");
            $query = $em->createQuery($dql);

            $requestOid = $project->getOid();
            $requestOid = str_replace("APCP","",$requestOid);
            $requestOid = str_replace("HP","",$requestOid);
            $requestOid = "P".$requestOid;
            $search1 = "Thank you for your submission! Your project request has been assigned an ID of ";
            $search2 = $requestOid;
            echo "try 3: [$search1] AND [$search2] <br>";
            $query->setParameters(
                array(
                    'eventStr' => '%'.$search1.'%',
                    'eventStr2' => '%'.$search2.'%',
                )
            );

            $loggers = $query->getResult();
        }

        if( count($loggers) > 0 ) {
            $logger = $loggers[0];
            //echo "@@@ logger.id=".$logger->getId()."; TransResRequest id=".$request->getId()."<br>";
            $date = $logger->getCreationdate();
        } else {
            $date = null;
        }

        return $date;
    }

}
