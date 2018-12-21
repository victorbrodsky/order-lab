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

namespace Oleg\TranslationalResearchBundle\Util;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Oleg\TranslationalResearchBundle\Entity\AdminReview;
use Oleg\TranslationalResearchBundle\Entity\CommitteeReview;
use Oleg\TranslationalResearchBundle\Entity\FinalReview;
use Oleg\TranslationalResearchBundle\Entity\IrbReview;
use Oleg\TranslationalResearchBundle\Entity\SpecialtyList;
use Oleg\TranslationalResearchBundle\Entity\TransResSiteParameters;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 8/25/2017
 * Time: 09:48 AM
 */
class DashboardUtil
{

    protected $container;
    protected $em;
    protected $secTokenStorage;
    protected $secAuth;

    private $width = 1200;
    private $height = 600;
    private $otherId = "All other [[otherStr]] combined";
    private $otherSearchStr = "All other ";

    private $lightFilter = true;

    public function __construct( $em, $container ) {
        $this->container = $container;
        $this->em = $em;
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        //$this->secToken = $container->get('security.token_storage')->getToken(); //$user = $this->secToken->getUser();
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
    }
    
    public function getChartTypes() {
        $chartTypes = array(
            //PI/Project statistics
            "1. Principle Investigators by Affiliation" =>                      "pi-by-affiliation",
            "2. Total Number of Projects per PI (Top 10)" =>                    "projects-per-pi",
            "3. Total Number of Funded Projects per PI (Top 10)" =>             "funded-projects-per-pi",
            "4. Total Number of Non-Funded Projects per PI (Top 10)" =>         "nonfunded-projects-per-pi",
            //Pathologists Involved and number of projects
            "5. Total Number of Projects per Pathologist Involved (Top 10)" =>             "projects-per-pathologist-involved",
            "6. Total Number of Funded Projects per Pathologist Involved (Top 10)" =>      "funded-projects-per-pathologist-involved",
            "7. Total Number of Non-Funded Projects per Pathologist Involved (Top 10)" =>  "nonfunded-projects-per-pathologist-involved",
            //Work request statistics
            "8. Total Number of Work Requests by Funding Source" =>                 "requests-by-funding-source",
            "9. Total Number of Work Requests per Project (Top 10)" =>              "requests-per-project",
            "10. Total Number of Work Requests per Funded Project (Top 10)" =>      "requests-per-funded-projects",
            "11. Total Number of Work Requests per Non-Funded Project (Top 10)" =>  "requests-per-nonfunded-projects",
            //   Products/Services
            "12. TRP Service Productivity by Products/Services (Top 10)" =>     "service-productivity-by-service",
            "13. TRP Service Productivity for Funded Projects (Top 10)" =>      "service-productivity-by-service-per-funded-projects",
            "14. TRP Service Productivity for Non-Funded Projects (Top 10)" =>  "service-productivity-by-service-per-nonfunded-projects",
            "15. TRP Service Productivity by Products/Services" =>              "service-productivity-by-service-compare-funded-vs-nonfunded-projects",
            //Productivity statistics based on work requests
            "16. Total Fees by Work Requests" =>                                "fees-by-requests",
            "17. Total Fees per Funded Project (Top 10)" =>                     "fees-by-requests-per-funded-projects",
            "18. Total Fees per Non-Funded Project (Top 10)" =>                 "fees-by-requests-per-nonfunded-projects",
            "19. Total Fees per Investigator (Top 10)" =>                       "fees-by-investigators",
            "20. Total Fees per Investigator for Funded Projects (Top 10)" =>   "fees-by-investigators-per-funded-projects",
            "21. Total Fees per Investigator for Non-Funded Projects (Top 10)"=>"fees-by-investigators-per-nonfunded-projects",
            //Financial statistics based on invoices
            "22. Generated Invoices for Funded Projects" =>               "fees-by-invoices-per-funded-projects",
            "23. Generated Invoices for Non-Funded Projects (Top 10)" =>  "fees-by-invoices-per-nonfunded-projects",
            "24. Generated Invoices per PI (Top 10)" =>                   "fees-by-invoices-per-pi",
            //Pathologists Involved and number of projects
            "25. Total Invoiced Amounts for Projects per Pathologist Involved (Top 10)" =>             "fees-by-invoices-per-projects-per-pathologist-involved",
            "26. Total Invoiced Amounts for Funded Projects per Pathologist Involved (Top 10)" =>      "fees-by-invoices-per-funded-projects-per-pathologist-involved",
            "27. Total Invoiced Amounts for Non-Funded Projects per Pathologist Involved (Top 10)" =>  "fees-by-invoices-per-nonfunded-projects-per-pathologist-involved",
            "28. Total Fees per Involved Pathologist for Non-Funded Projects (Top 10)" =>  "fees-per-nonfunded-projects-per-pathologist-involved",

            "29. Total Number of Projects per Type" => "projects-per-type",
            "30. Total Number of Work Requests per Business Purpose" => "requests-per-business-purpose",

            "31. Turn-around Statistics: Average number of days to complete a Work Request (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-request",
            "32. Turn-around Statistics: Number of days to complete each Work Request (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-per-request",
            "33. Turn-around Statistics: Number of days to complete each Work Request with products/services (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-per-request-with-product",
            "34. Turn-around Statistics: Average number of days for each project request approval phase" => "turn-around-statistics-days-project-state",
            "35. Turn-around Statistics: Number of days for each project request approval phase" => "turn-around-statistics-days-per-project-state",
            "36. Turn-around Statistics: Average number of days for invoices to be paid (based on fully and partially paid invoices)" => "turn-around-statistics-days-paid-invoice",
            "37. Turn-around Statistics: Number of days for each invoice to be paid (based on fully and partially paid invoices)" => "turn-around-statistics-days-per-paid-invoice",
            "38. Turn-around Statistics: Top 10 PIs with most delayed unpaid invoices" => "turn-around-statistics-pis-with-delayed-unpaid-invoices",
            "39. Turn-around Statistics: Top 10 PIs with highest total unpaid, overdue invoices" => "turn-around-statistics-pis-with-highest-total-unpaid-invoices",
            "40. Turn-around Statistics: Top 10 PIs combining index (delay in months * total) for unpaid, overdue invoices" => "turn-around-statistics-pis-combining-total-delayed-unpaid-invoices",

            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
        );
        return $chartTypes;
    }
    public function getChartTypeByValue($value) {
        $this->getChartTypes();
        $key = array_search($value, $this->getChartTypes());
        return $key;
    }

    public function isUserBelongsToInstitution($user, $parentInstitution) {
        if( !$parentInstitution ) {
            return false;
        }

        //get all user's institutions
        $institutions = $user->getInstitutions();

        foreach($institutions as $institution) {
            if( $this->em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode($parentInstitution,$institution) ) {
                return true;
            }
        }

        return false;
    }

    public function getNumberFormat($number,$digits=null) {
        //$number = 123456789;
        //return $this->toMoney($number,'');
        return number_format($number,$digits);
    }
    function toMoney($val,$symbol='$',$r=2) {
        $n = $val;
        $c = is_float($n) ? 1 : number_format($n,$r);
        $d = '.';
        $t = ',';
        $sign = ($n < 0) ? '-' : '';
        $i = $n=number_format(abs($n),$r);
        $j = (($j = $i.length) > 3) ? $j % 3 : 0;

        return  $symbol.$sign .($j ? substr($i,0, $j) + $t : '').preg_replace('/(\d{3})(?=\d)/',"$1" + $t,substr($i,$j)) ;
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
            //$value = $this->getNumberFormat($value);
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
                    $descrType = $descriptionSingleArr[3];
                    $descrValueArr = $descriptionSingleArr[4];
                    $descrValue = $descrValueArr[$index];
                    if( $descrType == "money" ) {
                        $descrValue = $this->getNumberFormat($descrValue);
                    }
                    if( $descrValue ) {
                        if( $descrColor ) {
                            $descr[] = '<span style="color:'.$descrColor.'">'.$descrPrefix . $descrValue.'</span>';
                        } else {
                            $descr[] = $descrPrefix . $descrValue;
                        }
                    }
                }

                if( count($descr) > 0 ) {
                    if( strpos($descrFirstPrefix,'$') !== false ) {
                        $valueLabel = $this->getNumberFormat($value);
                    } else {
                        $valueLabel = $value;
                    }
                    $index = $index . " " . $descrFirstPrefix . $valueLabel . " (" . implode(", ",$descr) . ")";
                }

                $piProjectCountTopShortArr[$index] = $value;
            }
            return $piProjectCountTopShortArr;
        }

        return $piProjectCountTopArr;
    }
    public function getTopMultiArray($piProjectCountArr, $showOthers=false, $descriptionArr=array(), $maxLen=50) {
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

    public function getChart( $dataArr, $title, $type='pie', $layoutArray=null, $valuePrefixLabel=null ) {

        if( count($dataArr) == 0 ) {
            return array();
        }

        $labels = array();
        $values = array();
        $links = array();
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

        foreach( $dataArr as $label => $valueData ) {
            if( is_array($valueData) ) {
                $value = $valueData["value"];
                $link = $valueData["link"];
            } else {
                $value = $valueData;
                $link = null;
            }
            //value
            if ($type == "bar" || ($value && $value != 0)) {
                if ($valuePrefixLabel && $value) {
                    if (strpos($valuePrefixLabel, '$') !== false) {
                        $label = $label . " " . $valuePrefixLabel . $this->getNumberFormat($value);
                    } else {
                        $label = $label . " " . $valuePrefixLabel . $value;
                    }
                    //echo "value=$value<br>";
                }
                $labels[] = $label;
                $values[] = $value;
                //$text[] = $value;
                if( $link ) {
                    $links[] = $link;
                }
            }
        }

        if( count($values) == 0 ) {
            return array();
            //return array('error'=>"No data found corresponding to this chart parameters");
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

        $chartsArray = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );

        return $chartsArray;
    }
    public function getChartByMultiArray( $dataArr, $filterArr, $title, $type='pie', $layoutArray=null, $valuePrefixLabel=null ) {

        if( count($dataArr) == 0 ) {
            return array();
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
                    if( strpos($valuePrefixLabel,'$') !== false ) {
                        $label = $label . " " . $valuePrefixLabel . $this->getNumberFormat($value);
                    } else {
                        $label = $label . " " . $valuePrefixLabel . $value;
                    }
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
                        //'filter[project]' => $objectid,
                        'filter[projectSearch]' => $objectid, //TODO: optimization search
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

                if( $showPath == 'project-type' ) {
                    $linkFilterArr = array(
                        'filter[state][0]' => 'final_approved',
                        'filter[state][1]' => 'closed',
                        'filter[startDate]' => $startDateStr,
                        'filter[endDate]' => $endDateStr,
                        'filter[]' => $projectSpecialtyObjects,
                        'filter[searchProjectType]' => $objectid
                    );

                    if( count($projectSpecialtyObjects) > 0 ) {
                        $projectSpecialtyObject = $projectSpecialtyObjects[0];
                        $linkFilterArr['filter[projectSpecialty]'] = $projectSpecialtyObject->getId();
                    }

                    $link = $this->container->get('router')->generate(
                        'translationalresearch_project_index',
                        $linkFilterArr,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }

                $labels[] = $label;
                $values[] = $value;
                $links[] = $link;
                //$text[] = $value;
            }
        }

        if( count($values) == 0 ) {
            return array();
            //return array('error'=>"No data found corresponding to this chart parameters");
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

        $chartsArray = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );

        return $chartsArray;
    }

    public function getStackedChart( $combinedDataArr, $title ) {

        if( count($combinedDataArr) == 0 ) {
            return array();
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

        //$layoutArray['categoryorder'] = 'array';
        //$layoutArray['categoryarray'] = array('TRP-1003 Unstained slides from paraffin-embedded or frozen tissue','TRP-1002 Embedding frozen tissue in OCT block');

        $stackDataArray = array();
        $stackDataSumArray = array();
        $xAxis = "x";
        $yAxis = "y";

        foreach($combinedDataArr as $name=>$dataArr) {
            $chartDataArray = array();
            $labels = array();
            $values = array();
            foreach ($dataArr as $label => $valueData) {

                if( is_array($valueData) ) {
                    $value = $valueData["value"];
                    $link = $valueData["link"];
                } else {
                    $value = $valueData;
                    $link = null;
                }

                $labels[] = $label;
                $values[] = $value;
                $links[] = $link;

                if( isset($stackDataSumArray[$label]) ) {
                    $sumValue = $stackDataSumArray[$label] + $value;
                } else {
                    $sumValue = $value;
                }
                $stackDataSumArray[$label] = $sumValue;
            }

            //if( count($values) == 0 ) {
            //    continue;
            //}

            $chartDataArray[$xAxis] = $labels;
            $chartDataArray[$yAxis] = $values;
            $chartDataArray['name'] = $name;
            $chartDataArray['type'] = 'bar';
            $chartDataArray['links'] = $links;

            $stackDataArray[] = $chartDataArray;
        }

        if( count($values) == 0 ) {
            return array();
            //return array('error'=>"No data found corresponding to this chart parameters");
        }

        $testing = false;
        //$testing = true;
        if($testing) {
            //sort $stackDataArray by value

            echo "<pre>";
            print_r($stackDataArray);
            echo "</pre>";

//          echo "<pre>";
//          print_r($stackDataSumArray);
//          echo "</pre>";
            arsort($stackDataSumArray);
            echo "<pre>";
            print_r($stackDataSumArray);
            echo "</pre>";

            foreach ($stackDataSumArray as $label => $value) {

                foreach ($stackDataArray as $stackData) {
                    foreach ($stackData as $key => $data) {
                        if ($key == $xAxis) {

                        }
                    }

                }

            }
            exit('111');
        }

        $chartsArray = array(
            'layout' => $layoutArray,
            'data' => $stackDataArray
        );

        return $chartsArray;
    }

    public function attachSecondValueToFirstLabel($firstArr,$secondArr,$prefix) {
        $resArr = array();
        foreach($firstArr as $index=>$value) {
            //$index = $index . " " . $prefix . $secondArr[$index];
            if( strpos($prefix,'$') !== false ) {
                $index = $index . " " . $prefix . $this->getNumberFormat($secondArr[$index]);
            } else {
                $index = $index . " " . $prefix . $secondArr[$index];
            }
            $resArr[$index] = $value;
        }
        return $resArr;
    }

    public function getProjectsByFilter($startDate, $endDate, $projectSpecialties, $states=null, $addOneEndDay=true) {

        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        if( !$states ) {
            $dql->where("project.state = 'final_approved' OR project.state = 'closed'");
        } else {
            //$dql->where("request.progressState = '".$state."'");
            foreach($states as $state) {
                $stateArr[] = "project.state = '".$state."'";
            }
            if( count($stateArr) > 0 ) {
                $dql->where("(".implode(" OR ",$stateArr).")");
            }
        }

        $dqlParameters = array();

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d'); //H:i:s
            //$dqlParameters['startDate'] = $startDate;
        }
        if( $endDate ) {
            if( $addOneEndDay ) {
                $endDate->modify('+1 day');
            }
            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('project.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d'); //H:i:s
            //$dqlParameters['endDate'] = $endDate;
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

        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        //echo implode(",",$projectSpecialtyNamesArr)." Projects=".count($projects)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";

        return $projects;
    }

    public function getRequestsByFilter($startDate, $endDate, $projectSpecialties, $addOneEndDay=true) {
        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
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

        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        //echo implode(",",$projectSpecialtyNamesArr)." Projects=".count($projects)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";

        return $projects;
    }
    public function getRequestsByAdvanceFilter($startDate, $endDate, $projectSpecialties, $category, $states=null, $addOneEndDay=true) {
        $em = $this->em;
        //$transresUtil = $this->container->get('transres_util');

        $repository = $em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        //Exclude Work requests with status=Canceled and Draft
        if( !$states ) {
            $dql->where("request.progressState != 'draft' AND request.progressState != 'canceled'");
        } else {
            //$dql->where("request.progressState = '".$state."'");
            foreach($states as $state) {
                $stateArr[] = "request.progressState = '".$state."'";
            }
            if( count($stateArr) > 0 ) {
                $dql->where("(".implode(" OR ",$stateArr).")");
            }
        }

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

        if( $category ) {
            $dql->leftJoin('request.products','products');
            $dql->leftJoin('products.category','category');
            $dql->andWhere("category.id = :categoryId");
            $dqlParameters["categoryId"] = $category->getId();
        }

        $query = $em->createQuery($dql);

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        //echo implode(",",$projectSpecialtyNamesArr)." Projects=".count($projects)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";

        return $projects;
    }

    public function getInvoicesByFilter($startDate, $endDate, $projectSpecialties, $states=null, $addOneEndDay=true, $compareType='last invoice generation date') {
        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:Invoice');
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');
        $dql->leftJoin('invoice.transresRequest','request');

        //Exclude Work requests with status=Canceled and Draft
        //$dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE AND invoice.status != 'canceled'");
        if( !$states ) {
            //Exclude Work requests with status=Canceled and Draft
            $dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE AND invoice.status != 'canceled'");
        } else {
            foreach($states as $state) {
                $stateArr[] = "invoice.status = '".$state."'";
            }
            if( count($stateArr) > 0 ) {
                $dql->where("request.progressState != 'draft' AND request.progressState != 'canceled' AND invoice.latestVersion = TRUE AND (".implode(" OR ",$stateArr).")");
            }
        }

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

        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        //echo implode(",",$projectSpecialtyNamesArr)." Projects=".count($projects)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";

        return $projects;
    }

    public function getTitleWithTotal($chartName,$total,$prefix=null) {
        return $chartName . " - " . $prefix . $total . " total";
    }

    public function getDiffDaysByProjectState($project,$state) {
        $transresUtil = $this->container->get('transres_util');
        $reviews = $transresUtil->getReviewsByProjectAndState($project,$state);

        //get earliest create date and latest update date
        $startDate = null; //get enter state date
        $endDate = null; //get exit state date
        foreach($reviews as $review) {
            //phase start (enter) date
            $enterDate = $this->getPreviousStateEnterDate($project,$state);
            if( !$enterDate ) {
                $enterDate = $review->getCreatedate();
            }
            if( $startDate ) {
                if( $enterDate < $startDate ) {
                    $startDate = $enterDate;
                }
            } else {
                $startDate = $enterDate;
            }

            //phase end (exit) date
            if( $project->getApprovalDate() && $state == "final_review" ) {
                $endDate = $project->getApprovalDate();
                //echo "1 $state: ".$endDate->format("Y-m-d")."<br>";
                if( $endDate ) {
                    //echo "$state: ".$endDate->format("Y-m-d")."<br>";
                    continue;
                }
            }

            if( $state == "committee_review" ) {
                if( $review->getPrimaryReview() ) {
                    if ($endDate) {
                        if ($review->getUpdatedate() > $endDate) {
                            $endDate = $review->getUpdatedate();
                        }
                    } else {
                        $endDate = $review->getUpdatedate();
                    }
                }
            } else {
                if ($endDate) {
                    if ($review->getUpdatedate() > $endDate) {
                        $endDate = $review->getUpdatedate();
                    }
                } else {
                    $endDate = $review->getUpdatedate();
                }
            }
        }//foreach review

        if( !$startDate ) {
            $startDate = $project->getCreateDate();
        }

//        if( !$endDate && $state == "final_review" ) {
//            //echo "final state=".$state."<br>";
//            $endDate = $project->getApprovalDate();
//        }
//        if( $project->getApprovalDate() && $state == "final_review" ) {
//            $endDate = $project->getApprovalDate();
//        }

        if( !$endDate ) {
            //echo "***state=".$state."<br>";
            $endDate = $project->getUpdatedate();
        } else {
            //echo "###<br>";
        }

        if( $endDate < $startDate ) {
            $endDate = $startDate;
        }

        if( $startDate && $endDate ) {
            //ok
        } else {
            return null;
        }

        //echo $startDate->format("Y-m-d")." => ".$endDate->format("Y-m-d")." (".$state.")<br>";

        //Number of days to go from review's createdate to review's updatedate
        $dDiff = $startDate->diff($endDate);
        //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
        $days = $dDiff->days;
        //echo $state.": days=".$days."<br>";
        $days = intval($days);

        //show minimum 1 day
        if( !$days ) {
            $days = 1;
        }

        return $days;
    }
    public function getStateExitDate($project,$state) {
        $transresUtil = $this->container->get('transres_util');
        $reviews = $transresUtil->getReviewsByProjectAndState($project,$state);

        //get latest update date
        $exitDate = null; //get exit state date
        foreach($reviews as $review) {
            if( $exitDate ) {
                if( $review->getUpdatedate() > $exitDate ) {
                    $exitDate = $review->getUpdatedate();
                }
            } else {
                $exitDate = $review->getUpdatedate();
            }
        }
        return $exitDate;
    }
//    public function getReviewExitDate($project,$state) {
//        $transresUtil = $this->container->get('transres_util');
//        $reviews = $transresUtil->getReviewsByProjectAndState($project,$state);
//
//        //get earliest create date and latest update date
//        $exitDate = null; //get exit state date
//        foreach($reviews as $review) {
//            if( $exitDate ) {
//                if( $review->getUpdatedate() > $exitDate ) {
//                    $exitDate = $review->getUpdatedate();
//                }
//            } else {
//                $exitDate = $review->getUpdatedate();
//            }
//        }
//        return $exitDate;
//    }
    public function getPreviousStateEnterDate($project,$state) {
        if( $state == "irb_review" ) {
            $date = $project->getStartReviewDate();
            if( !$date ) {
                $date = $project->getCreateDate();
                //$date = $this->getStateEnterDate($project,"irb_review");
            }
        }
        if( $state == "admin_review" ) {
            $date = $this->getStateExitDate($project,"irb_review");
        }
        if( $state == "committee_review" ) {
            $date = $this->getStateExitDate($project,"admin_review");
        }
        if( $state == "final_review" ) {
            $date = $this->getStateExitDate($project,"committee_review");
        }
        return $date;
    }
    public function getStateTitleWithAverageDays($irbTitle,$projectPhaseArr) {
        $irbCount = count($projectPhaseArr);
        if( $irbCount > 0 ) {
            $irbDays = 0;
            foreach ($projectPhaseArr as $index => $valueData) {
                if( is_array($valueData) ) {
                    $days = $valueData['value'];
                } else {
                    $days = $valueData;
                }
                $irbDays = $irbDays + $days;
            }
            $irbTitle = $irbTitle . " (Average " . round($irbDays/$irbCount) . " days)";
        }
        return $irbTitle;
    }
    public function getInvoiceIssuedDate($invoice) {
        //continue;
        //$issued = $invoice->getCreateDate();

        $request = $invoice->getTransresRequest();

        //get the date from event log
        $repository = $this->em->getRepository('OlegUserdirectoryBundle:Logger');
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
        $dql->andWhere("logger.event LIKE :eventStr");

        $dql->orderBy("logger.id","DESC");
        $query = $this->em->createQuery($dql);

        //$search = "status changed to 'Unpaid/Issued'";
        $search = "invoice status change to Unpaid/Issued";

        //$search = "Unpaid/Issued";
        //$search = "";
        //$search = "status changed to ";
        //$search2 = "status changed to 'Unpaid/Issued'";
        $query->setParameters(
            array(
                'eventStr' => '%'.$search.'%',
                //'eventStr2' => '%'.$search2.'%'
            )
        );

        $loggers = $query->getResult();

        //try to use "Invoice PDF Issued" event "Invoice APCP668-REQ14079-V1 PDF has been sent by email ..."
        if( count($loggers) == 0 ) {
            $dql2 = $repository->createQueryBuilder("logger");
            $dql2->where("logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
            $dql2->andWhere("logger.event LIKE :eventStr");

            $dql2->orderBy("logger.id","DESC");
            $query2 = $this->em->createQuery($dql2);

            $search2 = "Invoice ".$invoice->getOid()." PDF has been sent by email";
            $query2->setParameters(
                array(
                    'eventStr' => '%'.$search2.'%',
                )
            );

            $loggers = $query2->getResult();
        }

        //echo $invoice->getOid().": loggers count=".count($loggers)."<br>";
        //foreach($loggers as $logger) {
        //    echo "logger.id=".$logger->getId()."; TransResRequest id=".$request->getId()."<br>";
        //}

        if( count($loggers) > 0 ) {
            $logger = $loggers[0];
            //echo "@@@ logger.id=".$logger->getId()."; TransResRequest id=".$request->getId()."<br>";
            $issued = $logger->getCreationdate();
        } else {
            $issued = null;
        }

        return $issued;
    }



    public function getDashboardChart($request) {

        //ini_set('memory_limit', '30000M');

        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        $projectSpecialty = $request->query->get('projectSpecialty');
        $showLimited = $request->query->get('showLimited');
        $chartType = $request->query->get('chartType');

        if( $startDate ) {
            $startDate = date_create_from_format('m/d/Y', $startDate); //10/31/2017 to DateTime
        }
        if( $endDate ) {
            $endDate = date_create_from_format('m/d/Y', $endDate); //10/31/2017 to DateTime
        }

        if( $projectSpecialty != 0 ) {
            $projectSpecialtyObject = $this->em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }

        $filterArr = array(
            'startDate'=>$startDate,
            'endDate'=>$endDate,
            'projectSpecialtyObjects' => $projectSpecialtyObjects,
            'showLimited' => $showLimited,
            'funded' => null
        );

        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
        );

        //echo "startDate=".$startDate."<br>";

        $titleCount = 0;
        $chartName = $this->getChartTypeByValue($chartType);

        $chartsArray = null;
        $warningNoData = null;

        //1. Principle Investigators by Affiliation
        if( $chartType == "pi-by-affiliation" ) {

            $userSecUtil = $this->container->get('user_security_utility');
            $piWcmPathologyCounter = 0;
            $piWcmCounter = 0;
            $piOtherCounter = 0;
            $departmentAbbreviation = "Department";
            $institutionAbbreviation = "Institution";
            $institution = null;
            $department = $userSecUtil->getSiteSettingParameter('transresDashboardInstitution');
            if( $department ) {
                $departmentAbbreviation = $department."";
                $institution = $department->getParent();
                if( $institution ) {
                    $institutionAbbreviation = $institution."";
                }
            }

            $projectsCount1 = 0;
            $projectsCount2 = 0;
            $projectsCount3 = 0;

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $pis = $project->getPrincipalInvestigators();
                $count1 = false;
                $count2 = false;
                $count3 = false;

                foreach ($pis as $pi) {
                    //1. Principle Investigators by Affiliation
                    if( $this->isUserBelongsToInstitution($pi,$department) ) {
                        //WCM Pathology Faculty - WCM Department of Pathology and Laboratory Medicine in any Title’s department field
                        $piWcmPathologyCounter++;
                        $count1 = true;
                    }
                    elseif ( $this->isUserBelongsToInstitution($pi,$institution) ) {
                        //WCM Other Departmental Faculty - WCM institution
                        $piWcmCounter++;
                        $count2 = true;
                    } else {
                        //Other Institutions
                        $piOtherCounter++;
                        $count3 = true;
                    }
                }//foreach pi

                if( $count1 ) {
                    $projectsCount1++;
                }
                if( $count2 ) {
                    $projectsCount2++;
                }
                if( $count3 ) {
                    $projectsCount3++;
                }
            }

            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';

            $titleTotal = $piWcmPathologyCounter + $piWcmCounter + $piOtherCounter;
            $chartName = $this->getTitleWithTotal($chartName,$titleTotal);

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => $chartName //"1. Principle Investigators by Affiliation"
            );

            //$institutionAbbreviation = "WCM";
            //$departmentAbbreviation = "Pathology";
            //$piWcmPathologyCounter = 2;
            //$piWcmCounter = 5;

            $instName = $institutionAbbreviation . " " . $departmentAbbreviation;

            //WCMC pathology faculty PIs with 134 projects: 26
            //Non-WCMC pathology faculty PIs with 211 projects: 37
            $labels = array(
                "$instName faculty PIs with ".$projectsCount1." projects: ".$piWcmPathologyCounter,
                //"$institutionAbbreviation Other Departmental faculty PIs with ".$projectsCount2." projects: ".$piWcmCounter,
                "Non-".$instName." faculty PIs with ".$projectsCount2." projects: ".$piWcmCounter,
                "Other Institutions faculty PIs with ".$projectsCount3." projects: ".$piOtherCounter
            );

            $values = array($piWcmPathologyCounter,$piWcmCounter,$piOtherCounter);
            //$values = array($piWcmPathologyCounter,$piWcmCounter);

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $chartDataArray["textinfo"] = "value+percent";
            $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
            $chartDataArray['direction'] = 'clockwise';
            $dataArray[] = $chartDataArray;

            $chartsArray = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
        }

        //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
        if( $chartType == "projects-per-pi" ) {

            $piProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $pis = $project->getPrincipalInvestigators();
                foreach ($pis as $pi) {
                    $userName = $pi->getUsernameOptimal();
                    $userId = $pi->getId();

                    //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
                    if( isset($piProjectCountArr[$userId]) && isset($piProjectCountArr[$userId]['value']) ) {
                        $count = $piProjectCountArr[$userId]['value'] + 1;
                    } else {
                        $count = 1;
                    }
                    $piProjectCountArr[$userId]['value'] = $count;
                    $piProjectCountArr[$userId]['label'] = $userName;
                    $piProjectCountArr[$userId]['objectid'] = $userId;
                    $piProjectCountArr[$userId]['pi'] = $userId;
                    $piProjectCountArr[$userId]['show-path'] = "project";
                }

                $titleCount++;
            }

            //$chartName = $chartName . " - " . $totalCount . " total";
            $chartName = $this->getTitleWithTotal($chartName,$titleCount);

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piProjectCountTopArr = $this->getTopMultiArray($piProjectCountArr,$showOther); // getTopMultiArray(
            $filterArr['funded'] = null;
            //Projects per PI
            //                                           $dataArr,              $title,                                $type='pie', $layoutArray=null, $valuePrefixLabel=null
            $chartsArray = $this->getChartByMultiArray( $piProjectCountTopArr, $filterArr, $chartName,"pie",null," : ");
        }
        ///////////////// EOF 2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED) /////////////////

        // 3. Total number of Funded Projects per PI (Top 10)
        if( $chartType == "funded-projects-per-pi" ) {
            $piFundedProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $fundingNumber = $project->getFunded();

                if( $fundingNumber ) {

                    $pis = $project->getPrincipalInvestigators();
                    foreach ($pis as $pi) {
                        $userName = $pi->getUsernameOptimal();
                        $userId = $pi->getId();

                        if (isset($piFundedProjectCountArr[$userId]) && isset($piFundedProjectCountArr[$userId]['value'])) {
                            $count = $piFundedProjectCountArr[$userId]['value'] + 1;
                        } else {
                            $count = 1;
                        }
                        $piFundedProjectCountArr[$userId]['value'] = $count;
                        $piFundedProjectCountArr[$userId]['label'] = $userName;
                        $piFundedProjectCountArr[$userId]['objectid'] = $userId;
                        $piFundedProjectCountArr[$userId]['pi'] = $userId;
                        $piFundedProjectCountArr[$userId]['show-path'] = "project";

                    }//foreach $pis

                    $titleCount++;
                }//if

            }//foreach $projects

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piFundedProjectCountTopArr = $this->getTopMultiArray($piFundedProjectCountArr,$showOther);
            $filterArr['funded'] = true;
            $chartsArray = $this->getChartByMultiArray( $piFundedProjectCountTopArr, $filterArr, $chartName,"pie",null," : ");

        }
        ///////////////// EOF 3. Total number of Funded Projects per PI (Top 10) /////////////////

        //4. Total number of Non-Funded Projects per PI (Top 10)
        if( $chartType == "nonfunded-projects-per-pi" ) {
            $piUnFundedProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $fundingNumber = $project->getFunded();

                if( $fundingNumber ) {
                    //do nothing
                } else {

                    $pis = $project->getPrincipalInvestigators();
                    foreach ($pis as $pi) {
                        $userName = $pi->getUsernameOptimal();
                        $userId = $pi->getId();

                        if (isset($piUnFundedProjectCountArr[$userId]) && isset($piUnFundedProjectCountArr[$userId]['value'])) {
                            $count = $piUnFundedProjectCountArr[$userId]['value'] + 1;
                        } else {
                            $count = 1;
                        }
                        $piUnFundedProjectCountArr[$userId]['value'] = $count;
                        $piUnFundedProjectCountArr[$userId]['label'] = $userName;
                        $piUnFundedProjectCountArr[$userId]['objectid'] = $userId;
                        $piUnFundedProjectCountArr[$userId]['pi'] = $userId;
                        $piUnFundedProjectCountArr[$userId]['show-path'] = "project";


                    }//foreach $pis

                    $titleCount++;
                }
            }//foreach $projects

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piUnFundedProjectCountTopArr = $this->getTopMultiArray($piUnFundedProjectCountArr,$showOther);
            $filterArr['funded'] = false;
            $chartsArray = $this->getChartByMultiArray( $piUnFundedProjectCountTopArr, $filterArr, $chartName,"pie",null," : ");
        }
        ///////////////// EOF 4. Total number of Non-Funded Projects per PI (Top 10) /////////////////

        //5. Total Number of Projects per Pathologist Involved (Top 10)
        if( $chartType == "projects-per-pathologist-involved" ) {
            $pathologistProjectCountArr = array();
            //$pathologistProjectCountMultiArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $pathologists = $project->getPathologists();
                foreach ($pathologists as $pathologist) {
                    $userName = $pathologist->getUsernameOptimal();
//                    $userId = $pathologist->getId();
//                    //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
//                    if( isset($pathologistProjectCountMultiArr[$userId]) && isset($pathologistProjectCountMultiArr[$userId]['value']) ) {
//                        $count = $pathologistProjectCountMultiArr[$userId]['value'] + 1;
//                    } else {
//                        $count = 1;
//                    }
//                    $pathologistProjectCountMultiArr[$userId]['value'] = $count;
//                    $pathologistProjectCountMultiArr[$userId]['label'] = $userName;
//                    $pathologistProjectCountMultiArr[$userId]['objectid'] = $userId;
//                    $pathologistProjectCountMultiArr[$userId]['pi'] = $userId;
//                    //$pathologistProjectCountMultiArr[$userId]['show-path'] = "project";

                    if (isset($pathologistProjectCountArr[$userName])) {
                        $count = $pathologistProjectCountArr[$userName] + 1;
                    } else {
                        $count = 1;
                    }
                    $pathologistProjectCountArr[$userName] = $count;
                }

                $titleCount++;
            }

//            $showOther = $this->getOtherStr($showLimited,"Pathologist Involved");
//            $piProjectCountMultiTopArr = $this->getTopMultiArray($pathologistProjectCountMultiArr,$showOther); // getTopMultiArray(
//            $filterArr['funded'] = null;
//            $chartsArray = $this->getChartByMultiArray( $piProjectCountMultiTopArr, $filterArr, "2a. Total number of projects per Pathologist Involved (Top 10)","pie",null," : ");

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $pathologistProjectCountTopArr = $this->getTopArray($pathologistProjectCountArr,$showOther);
            $chartsArray = $this->getChart($pathologistProjectCountTopArr, $chartName,'pie',$layoutArray," : ");

        }
        ///////////////// EOF 2a. Total number of projects per Pathologist Involved (Top 10) /////////////////
        // 6. Total number of Funded Projects per Pathologist Involved (Top 10)
        if( $chartType == "funded-projects-per-pathologist-involved" ) {
            $pathologistFundedProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $fundingNumber = $project->getFunded();
                if( $fundingNumber ) {

                    $pathologists = $project->getPathologists();
                    foreach ($pathologists as $pathologist) {
                        $userName = $pathologist->getUsernameOptimal();
                            if (isset($pathologistFundedProjectCountArr[$userName])) {
                                $count = $pathologistFundedProjectCountArr[$userName] + 1;
                            } else {
                                $count = 1;
                            }
                            $pathologistFundedProjectCountArr[$userName] = $count;
                    }//foreach $pathologists

                    $titleCount++;
                }
            }//foreach $projects

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $pathologistFundedProjectCountTopArr = $this->getTopArray($pathologistFundedProjectCountArr,$showOther);
            $filterArr['funded'] = true;
            $chartsArray = $this->getChart($pathologistFundedProjectCountTopArr, $chartName,"pie",$layoutArray," : ");
        }
        ///////////////// EOF 3a. Total number of Funded Projects per Pathologist Involved (Top 10) /////////////////
        // 4a. Total number of Non-Funded Projects per Pathologist Involved (Top 10)
        if( $chartType == "nonfunded-projects-per-pathologist-involved" ) {
            $pathologistNonFundedProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $fundingNumber = $project->getFunded();
                if( $fundingNumber ) {
                    //do nothing
                } else {

                    $pathologists = $project->getPathologists();
                    foreach ($pathologists as $pathologist) {
                        $userName = $pathologist->getUsernameOptimal();

                            if (isset($pathologistNonFundedProjectCountArr[$userName])) {
                                $count = $pathologistNonFundedProjectCountArr[$userName] + 1;
                            } else {
                                $count = 1;
                            }
                            $pathologistNonFundedProjectCountArr[$userName] = $count;

                    }//foreach $pathologists

                    $titleCount++;
                }
            }//foreach $projects

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $pathologistNonFundedProjectCountTopArr = $this->getTopArray($pathologistNonFundedProjectCountArr,$showOther);
            //$filterArr['funded'] = true;
            $chartsArray = $this->getChart($pathologistNonFundedProjectCountTopArr, $chartName,"pie",$layoutArray," : ");
        }
        ///////////////// EOF 4a. Total number of Non-Funded Projects per Pathologist Involved (Top 10) /////////////////


        //Work request statistics
        //8. Total Number of Work Requests by Funding Source
        if( $chartType == "requests-by-funding-source" ) {

            $fundedRequestCount = 0;
            $notFundedRequestCount = 0;

            $fundedProjectArr = array();
            $unfundedProjectArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                $project = $transRequest->getProject();
                $projectId = $project->getId();
                if( $transRequest->getFundedAccountNumber() ) {
                    $fundedRequestCount++;
                    $fundedProjectArr[$projectId] = 1;
                } else {
                    $notFundedRequestCount++;
                    $unfundedProjectArr[$projectId] = 1;
                }
            }//foreach

            $chartName = $this->getTitleWithTotal($chartName,$fundedRequestCount+$notFundedRequestCount);

            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => $chartName //"5. Total Number of Work Requests by Funding Source"
            );

            $fundedProjectCount = 0;
            foreach($fundedProjectArr as $projectCount) {
                $fundedProjectCount = $fundedProjectCount + $projectCount;
            }

            $unfundedProjectCount = 0;
            foreach($unfundedProjectArr as $projectCount) {
                $unfundedProjectCount = $unfundedProjectCount + $projectCount;
            }

            //Work Requests for 154 Funded Projects: 1298
            $fundedLabel = "Work Requests for $fundedProjectCount Funded Projects"." : ".$fundedRequestCount;
            //Work Requests for 12 Non-Funded Projects: 445
            $unfundedLabel = "Work Requests for $unfundedProjectCount Non-Funded Projects"." : ".$notFundedRequestCount;

            $labels = array($fundedLabel,$unfundedLabel);
            $values = array($fundedRequestCount,$notFundedRequestCount);

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $chartDataArray["textinfo"] = "value+percent";
            $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
            $chartDataArray['direction'] = 'clockwise';
            $dataArray[] = $chartDataArray;

            $chartsArray = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
        }

        //9. Total Number of Work Requests per Project (Top 10)
        if( $chartType == "requests-per-project" ) {
            $requestPerProjectArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                $project = $transRequest->getProject();
                $projectId = $project->getId();
                $piIdArr = array();

                //in the legend after the project ID show the name of the PI: projectID by username: XXXX
                $projectIndex = $project->getOid(false);
                $piArr = array();
                foreach($transRequest->getPrincipalInvestigators() as $pi) {
                    $piArr[] = $pi->getUsernameOptimal();
                }
                if( count($piArr) > 0 ) {
                    $projectIndex = $projectIndex . " by " . implode(", ",$piArr);
                }

                if( isset($requestPerProjectArr[$projectId]) && isset($requestPerProjectArr[$projectId]['value']) ) {
                    $count = $requestPerProjectArr[$projectId]['value'] + 1;
                } else {
                    $count = 1;
                }
                $requestPerProjectArr[$projectId]['value'] = $count;
                $requestPerProjectArr[$projectId]['label'] = $projectIndex;
                $requestPerProjectArr[$projectId]['objectid'] = $projectId;
                $requestPerProjectArr[$projectId]['pi'] = $piIdArr;
                $requestPerProjectArr[$projectId]['show-path'] = "request";

                $titleCount++;
            }//foreach

            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"projects");
            $requestPerProjectTopArr = $this->getTopMultiArray($requestPerProjectArr,$showOther);
            $filterArr['funded'] = null;
            $chartsArray = $this->getChartByMultiArray($requestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ");
        }

        //10. Total Number of Work Requests per Funded Project (Top 10)
        if( $chartType == "requests-per-funded-projects" ) {
            $fundedRequestPerProjectArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                if( $transRequest->getFundedAccountNumber() ) {
                    $project = $transRequest->getProject();
                    $projectId = $project->getId();
                    $piIdArr = array();

                    //in the legend after the project ID show the name of the PI: projectID by username: XXXX
                    $projectIndex = $project->getOid(false);
                    $piArr = array();
                    foreach($transRequest->getPrincipalInvestigators() as $pi) {
                        $piArr[] = $pi->getUsernameOptimal();
                    }
                    if( count($piArr) > 0 ) {
                        $projectIndex = $projectIndex . " by " . implode(", ",$piArr);
                    }

                    if( isset($fundedRequestPerProjectArr[$projectId]) && isset($fundedRequestPerProjectArr[$projectId]['value']) ) {
                        $count = $fundedRequestPerProjectArr[$projectId]['value'] + 1;
                    } else {
                        $count = 1;
                    }
                    $fundedRequestPerProjectArr[$projectId]['value'] = $count;
                    $fundedRequestPerProjectArr[$projectId]['label'] = $projectIndex;
                    $fundedRequestPerProjectArr[$projectId]['objectid'] = $projectId;
                    $fundedRequestPerProjectArr[$projectId]['pi'] = $piIdArr;
                    $fundedRequestPerProjectArr[$projectId]['show-path'] = "request";
                    $titleCount++;
                }
            }

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"projects");
            $fundedRequestPerProjectTopArr = $this->getTopMultiArray($fundedRequestPerProjectArr,$showOther);
            $filterArr['funded'] = true;
            $chartsArray = $this->getChartByMultiArray( $fundedRequestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ");
        }

        //11. Total Number of Work Requests per Non-Funded Project (Top 10)
        if( $chartType == "requests-per-nonfunded-projects" ) {
            $unFundedRequestPerProjectArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {
                    $project = $transRequest->getProject();
                    $projectId = $project->getId();
                    $piIdArr = array();

                    //in the legend after the project ID show the name of the PI: projectID by username: XXXX
                    $projectIndex = $project->getOid(false);
                    $piArr = array();
                    foreach($transRequest->getPrincipalInvestigators() as $pi) {
                        $piArr[] = $pi->getUsernameOptimal();
                    }
                    if( count($piArr) > 0 ) {
                        $projectIndex = $projectIndex . " by " . implode(", ",$piArr);
                    }

                    if( isset($unFundedRequestPerProjectArr[$projectId]) && isset($unFundedRequestPerProjectArr[$projectId]['value']) ) {
                        $count = $unFundedRequestPerProjectArr[$projectId]['value'] + 1;
                    } else {
                        $count = 1;
                    }
                    $unFundedRequestPerProjectArr[$projectId]['value'] = $count;
                    $unFundedRequestPerProjectArr[$projectId]['label'] = $projectIndex;
                    $unFundedRequestPerProjectArr[$projectId]['objectid'] = $projectId;
                    $unFundedRequestPerProjectArr[$projectId]['pi'] = $piIdArr;
                    $unFundedRequestPerProjectArr[$projectId]['show-path'] = "request";
                    $titleCount++;
                }
            }

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"projects");
            $unFundedRequestPerProjectTopArr = $this->getTopMultiArray($unFundedRequestPerProjectArr,$showOther);
            $filterArr['funded'] = false;
            $chartsArray = $this->getChartByMultiArray( $unFundedRequestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ");
        }

        //Work request statistics: Products/Services
        //12. TRP Service Productivity by Products/Services (Top 10)
        if( $chartType == "service-productivity-by-service" ) {
            $quantityCountByCategoryArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                foreach($transRequest->getProducts() as $product) {
                    $category = $product->getCategory();
                    if( $category ) {
                        $categoryIndex = $category->getProductIdAndName();
                        $productQuantity = $product->getQuantity();
                        //9. TRP Service Productivity by Category Types (Top 10)
                        if (isset($quantityCountByCategoryArr[$categoryIndex])) {
                            $count = $quantityCountByCategoryArr[$categoryIndex] + $productQuantity;
                        } else {
                            $count = $productQuantity;
                        }
                        $quantityCountByCategoryArr[$categoryIndex] = $count;
                        /////////////
                        $titleCount = $titleCount + $productQuantity;
                    }
                }
            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"products/services");
            $quantityCountByCategoryTopArr = $this->getTopArray($quantityCountByCategoryArr,$showOther);
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );
            $chartsArray = $this->getChart($quantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ");

        }

        //13. TRP Service Productivity for Funded Projects (Top 10)
        if( $chartType == "service-productivity-by-service-per-funded-projects" ) {
            $fundedQuantityCountByCategoryArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                foreach($transRequest->getProducts() as $product) {
                    $category = $product->getCategory();
                    if( $category ) {
                        $categoryIndex = $category->getProductIdAndName();
                        $productQuantity = $product->getQuantity();
                        //10. TRP Service Productivity for Funded Projects (Top 10)
                        if( $transRequest->getFundedAccountNumber() ) {
                            //10. TRP Service Productivity for Funded Projects (Top 10)
                            if (isset($fundedQuantityCountByCategoryArr[$categoryIndex])) {
                                $count = $fundedQuantityCountByCategoryArr[$categoryIndex] + $productQuantity;
                            } else {
                                $count = $productQuantity;
                            }
                            $fundedQuantityCountByCategoryArr[$categoryIndex] = $count;
                            $titleCount = $titleCount + $productQuantity;
                        }
                    }
                }
            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"projects");
            $fundedQuantityCountByCategoryTopArr = $this->getTopArray($fundedQuantityCountByCategoryArr,$showOther);
            $chartsArray = $this->getChart($fundedQuantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ");
        }

        //14. TRP Service Productivity for Non-Funded Projects (Top 10)
        if( $chartType == "service-productivity-by-service-per-nonfunded-projects" ) {
            $unFundedQuantityCountByCategoryArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                foreach($transRequest->getProducts() as $product) {
                    $category = $product->getCategory();
                    if( $category ) {
                        $categoryIndex = $category->getProductIdAndName();
                        $productQuantity = $product->getQuantity();
                        //10. TRP Service Productivity for Funded Projects (Top 10)
                        if( $transRequest->getFundedAccountNumber() ) {
                            //do nothing
                        } else {
                            //11. TRP Service Productivity for non-Funded projects (Top 10)
                            if (isset($unFundedQuantityCountByCategoryArr[$categoryIndex])) {
                                $count = $unFundedQuantityCountByCategoryArr[$categoryIndex] + $productQuantity;
                            } else {
                                $count = $productQuantity;
                            }
                            $unFundedQuantityCountByCategoryArr[$categoryIndex] = $count;
                            $titleCount = $titleCount + $productQuantity;
                        }
                    }
                }
            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"projects");
            $unFundedQuantityCountByCategoryTopArr = $this->getTopArray($unFundedQuantityCountByCategoryArr,$showOther);
            $chartsArray = $this->getChart($unFundedQuantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ");
        }

        //"15. TRP Service Productivity by Products/Services" => "service-productivity-by-service-compare-funded-vs-nonfunded-projects"
        if( $chartType == "service-productivity-by-service-compare-funded-vs-nonfunded-projects" ) {
            $fundedQuantityCountByCategoryArr = array();
            $unFundedQuantityCountByCategoryArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                foreach($transRequest->getProducts() as $product) {
                    $category = $product->getCategory();
                    if( $category ) {
                        $categoryIndex = $category->getProductIdAndName();
                        $productQuantity = $product->getQuantity();
                        if( $transRequest->getFundedAccountNumber() ) {
                            //10. TRP Service Productivity for Funded Projects (Top 10)
                            if (isset($fundedQuantityCountByCategoryArr[$categoryIndex])) {
                                $count = $fundedQuantityCountByCategoryArr[$categoryIndex] + $productQuantity;
                            } else {
                                $count = $productQuantity;
                            }
                            $fundedQuantityCountByCategoryArr[$categoryIndex] = $count;
                        } else {
                            //11. TRP Service Productivity for non-Funded projects (Top 10)
                            if (isset($unFundedQuantityCountByCategoryArr[$categoryIndex])) {
                                $count = $unFundedQuantityCountByCategoryArr[$categoryIndex] + $productQuantity;
                            } else {
                                $count = $productQuantity;
                            }
                            $unFundedQuantityCountByCategoryArr[$categoryIndex] = $count;
                        }
                        $titleCount = $titleCount + $productQuantity;
                    }
                }
            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            //$showOther = $this->getOtherStr($showLimited,"projects");
            //$fundedQuantityCountByCategoryTopArr = $this->getTopArray($fundedQuantityCountByCategoryArr,$showOther);
            //$unFundedQuantityCountByCategoryTopArr = $this->getTopArray($unFundedQuantityCountByCategoryArr,$showOther);

            $combinedTrpData = array();
            $combinedTrpData['Funded'] = $fundedQuantityCountByCategoryArr; //$fundedQuantityCountByCategoryTopArr;  //$fundedQuantityCountByCategoryArr;
            $combinedTrpData['Not-Funded'] = $unFundedQuantityCountByCategoryArr; //$unFundedQuantityCountByCategoryTopArr;    //$unFundedQuantityCountByCategoryArr;
            $chartsArray = $this->getStackedChart($combinedTrpData, $chartName, "stack");
        }

        //16. Total Fees by Work Requests
        if( $chartType == "fees-by-requests" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $subtotalFees = 0;
            $fundedTotalFees = 0;
            $unFundedTotalFees = 0;

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $subtotalFee = intval($transresRequestUtil->getTransResRequestFeeHtml($transRequest));
                $subtotalFees = $subtotalFees + $subtotalFee;

                if( $transRequest->getFundedAccountNumber() ) {
                    $fundedTotalFees = $fundedTotalFees + $subtotalFee;
                } else {
                    $unFundedTotalFees = $unFundedTotalFees + $subtotalFee;
                }

                $titleCount++;
            }//foreach $requests

            //12. Total Fees by Work Requests (Total $)
            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';
            $subtotalFees = $this->getNumberFormat($subtotalFees);

            $chartName = $chartName. " (Total $".$subtotalFees.")";

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => $chartName
            );

            $fundedTotalFeesLabel = $this->getNumberFormat($fundedTotalFees);
            $unFundedTotalFeesLabel = $this->getNumberFormat($unFundedTotalFees);

            $labels = array('Funded : $'.$fundedTotalFeesLabel,'Non-Funded : $'.$unFundedTotalFeesLabel);
            $values = array($fundedTotalFees,$unFundedTotalFees);

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $chartDataArray["textinfo"] = "value+percent";
            $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
            $chartDataArray['direction'] = 'clockwise';
            $dataArray[] = $chartDataArray;

            $chartsArray = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
            /////////////////////

        }

        //17. Total Fees per Funded Project (Top 10)
        if( $chartType == "fees-by-requests-per-funded-projects" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $fundedTotalFeesByRequestArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $project = $transRequest->getProject();
                $projectIndex = $project->getOid(false);
                $pis = $project->getPrincipalInvestigators();
                $piInfoArr = array();
                foreach( $pis as $pi ) {
                    if( $pi ) {
                        $piInfoArr[] = $pi->getUsernameOptimal();
                    }
                }
                if( count($piInfoArr) > 0 ) {
                    $projectIndex = $projectIndex . " (" . implode(", ",$piInfoArr) . ")";
                }

                $subtotalFee = intval($transresRequestUtil->getTransResRequestFeeHtml($transRequest));
                //$subtotalFees = $subtotalFees + $subtotalFee;

                //17. Total Fees per Funded Project (Top 10)
                if( $transRequest->getFundedAccountNumber() ) {
                    if (isset($fundedTotalFeesByRequestArr[$projectIndex])) {
                        $totalFee = $fundedTotalFeesByRequestArr[$projectIndex] + $subtotalFee;
                    } else {
                        $totalFee = $subtotalFee;
                    }
                    //$totalFee = $this->getNumberFormat($totalFee);
                    $fundedTotalFeesByRequestArr[$projectIndex] = $totalFee;

                    $titleCount = $titleCount + $subtotalFee;
                }

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"projects");
            $fundedTotalFeesByRequestTopArr = $this->getTopArray($fundedTotalFeesByRequestArr,$showOther);
            $chartsArray = $this->getChart($fundedTotalFeesByRequestTopArr, $chartName,'pie',$layoutArray," : $");
        }

        //18. Total Fees per Non-Funded Project (Top 10)
        if( $chartType == "fees-by-requests-per-nonfunded-projects" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $unFundedTotalFeesByRequestArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $project = $transRequest->getProject();
                $projectIndex = $project->getOid(false);
                $pis = $project->getPrincipalInvestigators();
                $piInfoArr = array();
                foreach( $pis as $pi ) {
                    if( $pi ) {
                        $piInfoArr[] = $pi->getUsernameOptimal();
                    }
                }
                if( count($piInfoArr) > 0 ) {
                    $projectIndex = $projectIndex . " (" . implode(", ",$piInfoArr) . ")";
                }

                $subtotalFee = intval($transresRequestUtil->getTransResRequestFeeHtml($transRequest));
                //$subtotalFees = $subtotalFees + $subtotalFee;

                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {
                    //14. Total Fees per non-funded Project (Top 10)
                    if (isset($unFundedTotalFeesByRequestArr[$projectIndex])) {
                        $totalFee = $unFundedTotalFeesByRequestArr[$projectIndex] + $subtotalFee;
                    } else {
                        $totalFee = $subtotalFee;
                    }
                    //$totalFee = $this->getNumberFormat($totalFee);
                    $unFundedTotalFeesByRequestArr[$projectIndex] = $totalFee;

                    $titleCount = $titleCount + $subtotalFee;
                }

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"projects");
            $unFundedTotalFeesByRequestTopArr = $this->getTopArray($unFundedTotalFeesByRequestArr,$showOther);
            $chartsArray = $this->getChart($unFundedTotalFeesByRequestTopArr, $chartName,'pie',$layoutArray," : $");
        }

        //19. Total Fees per Investigator (Top 10)
        if( $chartType == "fees-by-investigators" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $totalFeesByInvestigatorArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $pis = $transRequest->getPrincipalInvestigators();
                if( count($pis) > 0 ) {
                    $pi = $pis[0];
                    $investigatorIndex = $pi->getUsernameOptimal();
                }

                $subtotalFee = intval($transresRequestUtil->getTransResRequestFeeHtml($transRequest));
                //$subtotalFees = $subtotalFees + $subtotalFee;

                //15. Total Fees per Investigator (Top 10)
                if (isset($totalFeesByInvestigatorArr[$investigatorIndex])) {
                    $totalFee = $totalFeesByInvestigatorArr[$investigatorIndex] + $subtotalFee;
                } else {
                    $totalFee = $subtotalFee;
                }
                //$totalFee = $this->getNumberFormat($totalFee);
                $totalFeesByInvestigatorArr[$investigatorIndex] = $totalFee;
                /////////////////////////////

                $titleCount = $titleCount + $subtotalFee;

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"Investigators");
            $totalFeesByInvestigatorTopArr = $this->getTopArray($totalFeesByInvestigatorArr,$showOther);
            $chartsArray = $this->getChart($totalFeesByInvestigatorTopArr, $chartName,'pie',$layoutArray," : $");
        }

        //20. Total Fees per Investigator (Funded) (Top 10)
        if( $chartType == "fees-by-investigators-per-funded-projects" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $fundedTotalFeesByInvestigatorArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $pis = $transRequest->getPrincipalInvestigators();
                if( count($pis) > 0 ) {
                    $pi = $pis[0];
                    $investigatorIndex = $pi->getUsernameOptimal();
                }

                $subtotalFee = intval($transresRequestUtil->getTransResRequestFeeHtml($transRequest));
                //$subtotalFees = $subtotalFees + $subtotalFee;

                if( $transRequest->getFundedAccountNumber() ) {
                    if (isset($fundedTotalFeesByInvestigatorArr[$investigatorIndex])) {
                        $totalFee = $fundedTotalFeesByInvestigatorArr[$investigatorIndex] + $subtotalFee;
                    } else {
                        $totalFee = $subtotalFee;
                    }
                    //$totalFee = $this->getNumberFormat($totalFee);
                    $fundedTotalFeesByInvestigatorArr[$investigatorIndex] = $totalFee;

                    $titleCount = $titleCount + $subtotalFee;
                }

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"Investigators");
            $fundedTotalFeesByInvestigatorTopArr = $this->getTopArray($fundedTotalFeesByInvestigatorArr,$showOther);
            $chartsArray = $this->getChart($fundedTotalFeesByInvestigatorTopArr, $chartName,'pie',$layoutArray," : $");
        }

        //21. Total Fees per Investigator (Non-Funded) (Top 10)
        if( $chartType == "fees-by-investigators-per-nonfunded-projects" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $unFundedTotalFeesByInvestigatorArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $pis = $transRequest->getPrincipalInvestigators();
                if( count($pis) > 0 ) {
                    $pi = $pis[0];
                    $investigatorIndex = $pi->getUsernameOptimal();
                }

                $subtotalFee = intval($transresRequestUtil->getTransResRequestFeeHtml($transRequest));
                //$subtotalFees = $subtotalFees + $subtotalFee;

                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {
                    //17. Total Fees per Investigator (non-Funded) (Top 10)
                    if (isset($unFundedTotalFeesByInvestigatorArr[$investigatorIndex])) {
                        $totalFee = $unFundedTotalFeesByInvestigatorArr[$investigatorIndex] + $subtotalFee;
                    } else {
                        $totalFee = $subtotalFee;
                    }
                    //$totalFee = $this->getNumberFormat($totalFee);
                    $unFundedTotalFeesByInvestigatorArr[$investigatorIndex] = $totalFee;

                    $titleCount = $titleCount + $subtotalFee;
                }

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"Investigators");
            $unFundedTotalFeesByInvestigatorTopArr = $this->getTopArray($unFundedTotalFeesByInvestigatorArr,$showOther);
            $chartsArray = $this->getChart($unFundedTotalFeesByInvestigatorTopArr, $chartName,'pie',$layoutArray," : $");
        }

        //22. Generated Invoices by Status for Funded Projects
        if( $chartType == "fees-by-invoices-per-funded-projects" ) {

            $paidInvoices = 0;
            $unpaidInvoices = 0;
            $totalInvoices = 0;
            $totalFundedPaidFees = 0;
            $totalFundedDueFees = 0;
            $totalThisInvoiceVerificationFees = 0;

            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $transRequest = $invoice->getTransresRequest();
                $paidThisInvoiceFee = intval($invoice->getPaid());
                $dueThisInvoiceFee = intval($invoice->getDue());

                //18. Generated Invoices by Status from Funded Projects (Total invoiced $152K)
                if ($transRequest->getFundedAccountNumber()) {
                    if( $invoice->getStatus() == "Paid in Full" || $invoice->getStatus() == "Paid Partially" ) {
                        $paidInvoices++;
                    }
                    if( $invoice->getStatus() == "Unpaid/Issued" ) {
                        $unpaidInvoices++;
                    }
                    $totalInvoices++;
                    $totalFundedPaidFees = $totalFundedPaidFees + $paidThisInvoiceFee;
                    $totalFundedDueFees = $totalFundedDueFees + $dueThisInvoiceFee;
                    $totalThisInvoiceVerificationFees = $totalThisInvoiceVerificationFees + ($paidThisInvoiceFee + $dueThisInvoiceFee);
                }
                //////////////////////////////////////////////

            }//foreach invoices

            //18. Generated Invoices by Status from Funded Projects (Total invoiced $152K)
            //22. Generated Invoices by Status for Funded Projects
            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                //'title' => "18. Generated Invoices from Funded Projects (Total invoiced $".$totalThisInvoiceVerificationFees."; Total invoices: ".$totalInvoices.", 'Paid in Full' invoices: ".$paidInvoices.")"
                'title' => $chartName." (Total invoiced $".$this->getNumberFormat($totalThisInvoiceVerificationFees)
                    ."; Total invoices: ".$totalInvoices.", 'Paid in Full' invoices: ".$paidInvoices.")"
            );

            $labels = array(
                $paidInvoices.' Paid Invoices'.' : $'.$this->getNumberFormat($totalFundedPaidFees),                 //78 Paid Invoices: $xx
                $unpaidInvoices.' Unpaid (Due) Invoices'.' : $'.$this->getNumberFormat($totalFundedDueFees)  //154 Unpaid (Due) Invoices: $xx
            );
            $values = array($totalFundedPaidFees,$totalFundedDueFees);

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $chartDataArray["textinfo"] = "value+percent";
            $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
            $chartDataArray['marker'] = array('colors' => array("rgb(44, 160, 44)", "rgb(214, 39, 40)") );
            $chartDataArray['direction'] = 'clockwise';
            $dataArray[] = $chartDataArray;

            $chartsArray = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
            /////////////////////////////
        }

        //"23. Generated Invoices by Status for Non-Funded Projects (Top 10)" =>  "fees-by-invoices-per-nonfunded-projects"
        if( $chartType == "fees-by-invoices-per-nonfunded-projects" ) {
            $invoicesByProjectArr = array();
            $invoicesFeesByProjectArr = array();
            $totalInvoices = 0;

            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $transRequest = $invoice->getTransresRequest();
                $project = $transRequest->getProject();
                $projectIndex = $project->getOid(false);
                $pis = $project->getPrincipalInvestigators();
                $piInfoArr = array();
                foreach( $pis as $pi ) {
                    if( $pi ) {
                        $piInfoArr[] = $pi->getUsernameOptimal();
                    }
                }
                if( count($piInfoArr) > 0 ) {
                    $projectIndex = $projectIndex . " (" . implode(", ",$piInfoArr) . ")";
                }

                $totalThisInvoiceFee = intval($invoice->getTotal());

                //Generated Invoices by Status for Non-Funded Projects
                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {
                    //Generated Invoices by Status per Funded Project (Top 10)
                    if (isset($invoicesByProjectArr[$projectIndex])) {
                        $count = $invoicesByProjectArr[$projectIndex] + 1;
                    } else {
                        $count = 1;
                    }
                    $invoicesByProjectArr[$projectIndex] = $count;
                    //fees
                    if (isset($invoicesFeesByProjectArr[$projectIndex])) {
                        $totalFee = $invoicesFeesByProjectArr[$projectIndex] + $totalThisInvoiceFee;
                    } else {
                        $totalFee = $totalThisInvoiceFee;
                    }
                    //$totalFee = 123456;
                    $invoicesFeesByProjectArr[$projectIndex] = $totalFee;

                    $titleCount = $titleCount + $totalThisInvoiceFee;
                    $totalInvoices++;
                }

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            //Generated Invoices by Status per Funded Project (Top 10)
            $showOther = $this->getOtherStr($showLimited,"projects");
            $invoicesByProjectTopArr = $this->getTopArray($invoicesByProjectArr,$showOther);
            $invoicesFeesByProjectTopArr = $this->getTopArray($invoicesFeesByProjectArr,$showOther); //public function getTopArray(
            //merge two to attach fees to label
            $invoicesByProjectTopArr = $this->attachSecondValueToFirstLabel($invoicesByProjectTopArr,$invoicesFeesByProjectTopArr," : $");
            $chartsArray = $this->getChart($invoicesByProjectTopArr,$chartName." (".$totalInvoices." invoices)",'pie',$layoutArray);

            if( is_array($chartsArray) && count($chartsArray) == 0 ) {
                $warningNoData = "There are no invoices associated with un-funded project requests during the selected time frame.".
                    "<br>Chart '$chartName' has not been generated.";
            }
        }

        //24. Generated Invoices by Status per PI (Top 10)
        if( $chartType == "fees-by-invoices-per-pi" ) {
            $invoicesFeesByPiArr = array();
            $invoicePaidFeeArr = array();
            $invoiceDueFeeArr = array();
            $totalInvoices = 0;

            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $transRequest = $invoice->getTransresRequest();

                if( $invoice ) {
                    $investigator = $invoice->getPrincipalInvestigator();
                    if ($investigator) {
                        $investigatorIndex = $investigator->getUsernameOptimal();
                    } else {
                        $submitter = $invoice->getSubmitter();
                        $investigatorIndex = $submitter->getUsernameOptimal();
                    }
                } else {
                    $pis = $transRequest->getPrincipalInvestigators();
                    if( count($pis) > 0 ) {
                        $pi = $pis[0];
                        $investigatorIndex = $pi->getUsernameOptimal();
                    }
                }

                $totalThisInvoiceFee = intval($invoice->getTotal());
                $paidThisInvoiceFee = intval($invoice->getPaid());
                $dueThisInvoiceFee = intval($invoice->getDue());

                //24. Generated Invoices by Status per PI (Top 10)
                //if( $transRequest->getFundedAccountNumber() ) { //TODO: why funded?
                    //Total fees
                    if (isset($invoicesFeesByPiArr[$investigatorIndex])) {
                        $totalFee = $invoicesFeesByPiArr[$investigatorIndex] + $totalThisInvoiceFee;
                    } else {
                        $totalFee = $totalThisInvoiceFee;
                    }
                    //$totalFee = 123456;
                    $invoicesFeesByPiArr[$investigatorIndex] = $totalFee;

                    //paid
                    if (isset($invoicePaidFeeArr[$investigatorIndex])) {
                        $totalFee = $invoicePaidFeeArr[$investigatorIndex] + $paidThisInvoiceFee;
                    } else {
                        $totalFee = $paidThisInvoiceFee;
                    }
                    $invoicePaidFeeArr[$investigatorIndex] = $totalFee;

                    //unpaid
                    if (isset($invoiceDueFeeArr[$investigatorIndex])) {
                        $totalFee = $invoiceDueFeeArr[$investigatorIndex] + $dueThisInvoiceFee;
                    } else {
                        $totalFee = $dueThisInvoiceFee;
                    }
                    $invoiceDueFeeArr[$investigatorIndex] = $totalFee;

                    $titleCount = $titleCount + $totalThisInvoiceFee;
                    $totalInvoices++;
                //}

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $chartName = $chartName." (".$totalInvoices." invoices)";

            //24. Generated Invoices by Status per PI (Top 10)
            $descriptionArr = array(
                array("paid $"," : $","limegreen","money",$invoicePaidFeeArr),
                array("due $"," : $","red","money",$invoiceDueFeeArr)
            );
            $showOther = $this->getOtherStr($showLimited,"PIs");
            $invoicesFeesByPiArrTop = $this->getTopArray($invoicesFeesByPiArr,$showOther,$descriptionArr);
            $chartsArray = $this->getChart($invoicesFeesByPiArrTop,$chartName,'pie',$layoutArray);
        }

        //"25. Total Invoiced Amounts of Projects per Pathologist Involved (Top 10)" =>             "fees-by-invoices-per-projects-per-pathologist-involved",
        if( $chartType == "fees-by-invoices-per-projects-per-pathologist-involved" ) {
            $invoicesFeesByPathologistArr = array();
            $invoicePaidFeeArr = array();
            $invoiceDueFeeArr = array();
            $totalInvoices = 0;

            //get latest invoices Excluding Work requests with status=Canceled and Draft
            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $totalThisInvoiceFee = intval($invoice->getTotal());
                $paidThisInvoiceFee = intval($invoice->getPaid());
                $dueThisInvoiceFee = intval($invoice->getDue());

                $transRequest = $invoice->getTransresRequest();
                $project = $transRequest->getProject();
                $pathologists = $project->getPathologists();

                foreach($pathologists as $pathologist) {
                    $pathologistIndex = $pathologist->getUsernameOptimal();

                    //Total fees
                    if (isset($invoicesFeesByPathologistArr[$pathologistIndex])) {
                        $totalFee = $invoicesFeesByPathologistArr[$pathologistIndex] + $totalThisInvoiceFee;
                    } else {
                        $totalFee = $totalThisInvoiceFee;
                    }
                    $invoicesFeesByPathologistArr[$pathologistIndex] = $totalFee;

                    //paid
                    if (isset($invoicePaidFeeArr[$pathologistIndex])) {
                        $totalFee = $invoicePaidFeeArr[$pathologistIndex] + $paidThisInvoiceFee;
                    } else {
                        $totalFee = $paidThisInvoiceFee;
                    }
                    $invoicePaidFeeArr[$pathologistIndex] = $totalFee;

                    //unpaid
                    if (isset($invoiceDueFeeArr[$pathologistIndex])) {
                        $totalFee = $invoiceDueFeeArr[$pathologistIndex] + $dueThisInvoiceFee;
                    } else {
                        $totalFee = $dueThisInvoiceFee;
                    }
                    $invoiceDueFeeArr[$pathologistIndex] = $totalFee;

                    $titleCount = $titleCount + $totalThisInvoiceFee;
                }

                $totalInvoices++;

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $chartName = $chartName." (".$totalInvoices." invoices)";

            $descriptionArr = array(
                array("paid $"," : $","limegreen","money",$invoicePaidFeeArr),
                array("due $"," : $","red","money",$invoiceDueFeeArr)
            );
            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther,$descriptionArr);
            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName,'pie',$layoutArray);
        }

        //"26. Total Invoiced Amounts of Funded Projects per Pathologist Involved (Top 10)" =>      "fees-by-invoices-per-funded-projects-per-pathologist-involved"
        if( $chartType == "fees-by-invoices-per-funded-projects-per-pathologist-involved" ) {
            $invoicesFeesByPathologistArr = array();
            $invoicePaidFeeArr = array();
            $invoiceDueFeeArr = array();
            $totalInvoices = 0;

            //get latest invoices Excluding Work requests with status=Canceled and Draft
            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $totalThisInvoiceFee = intval($invoice->getTotal());
                $paidThisInvoiceFee = intval($invoice->getPaid());
                $dueThisInvoiceFee = intval($invoice->getDue());

                $transRequest = $invoice->getTransresRequest();
                $project = $transRequest->getProject();
                $pathologists = $project->getPathologists();

                if ($transRequest->getFundedAccountNumber()) {
                    foreach ($pathologists as $pathologist) {
                        $pathologistIndex = $pathologist->getUsernameOptimal();

                        //Total fees
                        if (isset($invoicesFeesByPathologistArr[$pathologistIndex])) {
                            $totalFee = $invoicesFeesByPathologistArr[$pathologistIndex] + $totalThisInvoiceFee;
                        } else {
                            $totalFee = $totalThisInvoiceFee;
                        }
                        $invoicesFeesByPathologistArr[$pathologistIndex] = $totalFee;

                        //paid
                        if (isset($invoicePaidFeeArr[$pathologistIndex])) {
                            $totalFee = $invoicePaidFeeArr[$pathologistIndex] + $paidThisInvoiceFee;
                        } else {
                            $totalFee = $paidThisInvoiceFee;
                        }
                        $invoicePaidFeeArr[$pathologistIndex] = $totalFee;

                        //unpaid
                        if (isset($invoiceDueFeeArr[$pathologistIndex])) {
                            $totalFee = $invoiceDueFeeArr[$pathologistIndex] + $dueThisInvoiceFee;
                        } else {
                            $totalFee = $dueThisInvoiceFee;
                        }
                        $invoiceDueFeeArr[$pathologistIndex] = $totalFee;

                        $titleCount = $titleCount + $totalThisInvoiceFee;
                    }
                    $totalInvoices++;
                }

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $chartName = $chartName." (".$totalInvoices." invoices)";

            $descriptionArr = array(
                array("paid $"," : $","limegreen","money",$invoicePaidFeeArr),
                array("due $"," : $","red","money",$invoiceDueFeeArr)
            );
            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther,$descriptionArr);
            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName,'pie',$layoutArray);
        }
        ///////////////// EOF "26. Total Invoiced Amounts of Funded Projects per Pathologist Involved (Top 10)" /////////////////

        //"27. Total Invoiced Amounts of Non-Funded Projects per Pathologist Involved (Top 10)" =>  "fees-by-invoices-per-nonfunded-projects-per-pathologist-involved"
        if( $chartType == "fees-by-invoices-per-nonfunded-projects-per-pathologist-involved" ) {
            $invoicesFeesByPathologistArr = array();
            $invoicePaidFeeArr = array();
            $invoiceDueFeeArr = array();
            $totalInvoices = 0;

            //get latest invoices Excluding Work requests with status=Canceled and Draft
            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $totalThisInvoiceFee = intval($invoice->getTotal());
                $paidThisInvoiceFee = intval($invoice->getPaid());
                $dueThisInvoiceFee = intval($invoice->getDue());

                $transRequest = $invoice->getTransresRequest();
                $project = $transRequest->getProject();
                $pathologists = $project->getPathologists();

                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {
                    foreach($pathologists as $pathologist) {
                        $pathologistIndex = $pathologist->getUsernameOptimal();

                        //Total fees
                        if (isset($invoicesFeesByPathologistArr[$pathologistIndex])) {
                            $totalFee = $invoicesFeesByPathologistArr[$pathologistIndex] + $totalThisInvoiceFee;
                        } else {
                            $totalFee = $totalThisInvoiceFee;
                        }
                        $invoicesFeesByPathologistArr[$pathologistIndex] = $totalFee;

                        //paid
                        if (isset($invoicePaidFeeArr[$pathologistIndex])) {
                            $totalFee = $invoicePaidFeeArr[$pathologistIndex] + $paidThisInvoiceFee;
                        } else {
                            $totalFee = $paidThisInvoiceFee;
                        }
                        $invoicePaidFeeArr[$pathologistIndex] = $totalFee;

                        //unpaid
                        if (isset($invoiceDueFeeArr[$pathologistIndex])) {
                            $totalFee = $invoiceDueFeeArr[$pathologistIndex] + $dueThisInvoiceFee;
                        } else {
                            $totalFee = $dueThisInvoiceFee;
                        }
                        $invoiceDueFeeArr[$pathologistIndex] = $totalFee;

                        $titleCount = $titleCount + $totalThisInvoiceFee;
                    }
                    $totalInvoices++;
                }

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");

            $descriptionArr = array(
                array("paid $"," : $","limegreen","money",$invoicePaidFeeArr),
                array("due $"," : $","red","money",$invoiceDueFeeArr)
            );
            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther,$descriptionArr);
            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName." (".$totalInvoices." invoices)",'pie',$layoutArray);

            if( is_array($chartsArray) && count($chartsArray) == 0 ) {
                $warningNoData = "There are no invoices associated with un-funded project requests that specify an involved pathologist during the selected time frame.".
                "<br>Chart '$chartName' has not been generated.";
            }
        }

        //"28. Total Fees per Involved Pathologist for Non-Funded Projects (Top 10)" =>  "fees-per-nonfunded-projects-per-pathologist-involved",
        if( $chartType == "fees-per-nonfunded-projects-per-pathologist-involved" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $invoicesFeesByPathologistArr = array();
            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {

                    $subtotalFee = intval($transresRequestUtil->getTransResRequestFeeHtml($transRequest));

                    $project = $transRequest->getProject();
                    $pathologists = $project->getPathologists();

                    foreach ($pathologists as $pathologist) {
                        $pathologistIndex = $pathologist->getUsernameOptimal();

                        //Total fees
                        if (isset($invoicesFeesByPathologistArr[$pathologistIndex])) {
                            $totalFee = $invoicesFeesByPathologistArr[$pathologistIndex] + $subtotalFee;
                        } else {
                            $totalFee = $subtotalFee;
                        }
                        $invoicesFeesByPathologistArr[$pathologistIndex] = $totalFee;

                        $titleCount = $titleCount + $subtotalFee;
                    }
                }//if

            }//foreach $requests


            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");

            $showOther = $this->getOtherStr($showLimited,"pathologists involved combined");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther);
            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName,'pie',$layoutArray);
        }
        ///////////// EOF "23. Total Invoiced Amounts of Non-Funded Projects per Pathologist Involved (Top 10)" /////////////

        //"29. Total Number of Projects per Type" => "projects-per-type"
        if( $chartType == "projects-per-type" ) {
            $projectTypeArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($projects as $project) {
                $projectType = $project->getProjectType();
                if( $projectType ) {
                    $projectId = $projectType->getId();
                    $projectName = $projectType->getName();
                } else {
                    $projectId = "No Type";
                    $projectName = "No Type";;
                }

                if( isset($projectTypeArr[$projectId]) && isset($projectTypeArr[$projectId]['value']) ) {
                    $count = $projectTypeArr[$projectId]['value'] + 1;
                } else {
                    $count = 1;
                }
                $projectTypeArr[$projectId]['value'] = $count;
                $projectTypeArr[$projectId]['label'] = $projectName;
                $projectTypeArr[$projectId]['objectid'] = $projectId;
                $projectTypeArr[$projectId]['pi'] = null;
                $projectTypeArr[$projectId]['show-path'] = "project-type";

                $titleCount++;
            }

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"Project Types");
            $projectTypeArrTop = $this->getTopMultiArray($projectTypeArr,$showOther);
            $chartsArray = $this->getChartByMultiArray( $projectTypeArrTop, $filterArr, $chartName,"pie",null," : ");
        }


        //"30. Total Number of Requests per Business Purpose" => "requests-per-business-purpose"
        if( $chartType == "requests-per-business-purpose" ) {
            $requestBusinessPurposeArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $businessPurposes = $transRequest->getBusinessPurposes();

                foreach($businessPurposes as $businessPurpose) {
                    $businessPurposeName = $businessPurpose->getName();
                    if (isset($requestBusinessPurposeArr[$businessPurposeName])) {
                        $count = $requestBusinessPurposeArr[$businessPurposeName] + 1;
                    } else {
                        $count = 1;
                    }
                    $requestBusinessPurposeArr[$businessPurposeName] = $count;

                    $titleCount++;
                }
            }

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"Business Purposes");
            $requestBusinessPurposeArrTop = $this->getTopArray($requestBusinessPurposeArr,$showOther);
            $chartsArray = $this->getChart($requestBusinessPurposeArrTop, $chartName,'pie',$layoutArray," : ");
        }

        //"31. Turn-around Statistics: Average number of days to complete a Work Request (based on Completed and Notified requests)" => "turn-around-statistics-days-complete-request"
        if( $chartType == "turn-around-statistics-days-complete-request" ) {
            $averageDays = array();

            //$statuses = array("completed","completedNotified");
            $statuses = array("completedNotified");

            $startDate->modify( 'first day of last month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                $thisEndDate->modify( 'first day of next month' );
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y").": ";
                $category = null;
                $transRequests = $this->getRequestsByAdvanceFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$category,$statuses);
                //$transRequests = $this->getRequestsByAdvanceFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$category);
                $startDate->modify( 'first day of next month' );

                //echo "<br>";
                //echo "transRequests=".count($transRequests)." (".$startDateLabel.")<br>";

                //$apcpResultStatArr = $this->getProjectRequestInvoiceChart($transRequests,$apcpResultStatArr,$startDateLabel);

                $daysTotal = 0;
                $count = 0;

                foreach($transRequests as $transRequest) {

                    //Number of days to go from Submitted to Completed
                    $submitted = $transRequest->getCreateDate();

                    //$updated = $transRequest->getUpdateDate(); //assumption: the update date for completed requests is the same as $completedDate
                    $completed = $transRequest->getCompletedDate();
                    if( !$completed ) {
                        continue;
                    }

                    $dDiff = $submitted->diff($completed);
                    //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
                    $days = $dDiff->days;
                    //echo "days=".$days."<br>";
                    $days = intval($days);
                    if( $days > 0 ) {
                        $daysTotal = $daysTotal + intval($days);
                        $count++;
                    }
                }

                //$transRequestsCount = count($transRequests);
                //if( $transRequestsCount ) {
                    //$startDateLabel = $startDateLabel . " (" . $transRequestsCount . " requests)";
                //}
                $startDateLabel = $startDateLabel . " (" . count($transRequests) . " requests)";

                if( $count > 0 ) {
                    $avgDaysInt = round($daysTotal/$count);
                    $averageDays[$startDateLabel] = $avgDaysInt;
                } else {
                    $averageDays[$startDateLabel] = null;
                }


            } while( $startDate < $endDate );

//            if( $category ) {
//                //$categoryName = $this->tokenTruncate($category->getProductIdAndName(),50);
//                $categoryName = $category->getProductId();
//                $categoryStr = " (".$categoryName.")";
//            } else {
//                $categoryStr = null;
//            }
//            $chartName = $chartName.$categoryStr;

            $chartsArray = $this->getChart($averageDays,$chartName,'bar',$layoutArray);
        }

        //"32. Turn-around Statistics: Number of days to complete each Work Request (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-per-request",
        if( $chartType == "turn-around-statistics-days-complete-per-request" ) {
            $averageDays = array();

            //$statuses = array("completed","completedNotified");
            $statuses = array("completedNotified");
            $transRequests = $this->getRequestsByAdvanceFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$category,$statuses);

            $daysTotal = 0;
            //$count = 0;

            foreach($transRequests as $transRequest) {

                //Number of days to go from Submitted to Completed
                $submitted = $transRequest->getCreateDate();

                $completed = $transRequest->getCompletedDate();
                if( !$completed ) {
                    continue;
                }

                $dDiff = $submitted->diff($completed);
                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
                $days = $dDiff->days;
                //echo "days=".$days."<br>";
                $days = intval($days);
                if( $days > 0 ) {
                    $daysTotal = $daysTotal + intval($days);
                    //$count++;
                }

                $index = $transRequest->getOid();

                $link = $this->container->get('router')->generate(
                    'translationalresearch_request_show',
                    array("id"=>$transRequest->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                if( isset($averageDays[$index]) ) {
                    //$averageDays[$index] = $averageDays[$index] + $days;
                    //$existingArr = $averageDays[$index];
                    //$existingDays = $existingArr["value"];
                    $existingDays = $averageDays[$index]["value"];
                    $days = $days + $existingDays;
                    $averageDays[$index] = array("value"=>$days,"link"=>$link);
                }
                //$averageDays[$index] = $days;
                $averageDays[$index] = array("value"=>$days,"link"=>$link);

                //$averageDays[$startDateLabel] = array("value"=>$avgDaysInt,"link"=>$link);
                //$averageDays[$startDateLabel] = $avgDaysInt;

            }//foreach

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => $chartName,
                'margin' => array('b'=>200)
            );

            $chartsArray = $this->getChart($averageDays, $chartName,'bar',$layoutArray);
        }

        //"33. Turn-around Statistics: Number of days to complete each Work Request with products/services (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-per-request-with-product",
        if( $chartType == "turn-around-statistics-days-complete-per-request-with-product" ) {
            $averageDays = array();

            $statuses = array("completedNotified");
            $transRequests = $this->getRequestsByAdvanceFilter($startDate, $thisEndDate, $projectSpecialtyObjects, $category, $statuses);

            $requestCategoryWeightQuantityArr = array();

            foreach ($transRequests as $transRequest) {

                //Number of days to go from Submitted to Completed
                $submitted = $transRequest->getCreateDate();

                $completed = $transRequest->getCompletedDate();
                if (!$completed) {
                    continue;
                }

                //1) calculate days
                $dDiff = $submitted->diff($completed);
                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
                $days = $dDiff->days;
                //echo "days=".$days."<br>";
                $days = intval($days);

                $index = $transRequest->getOid();

                //2) calculate weight
                $totalQuantity = 0;
                foreach ($transRequest->getProducts() as $product) {
                    $quantity = $product->getQuantity();
                    $totalQuantity = $totalQuantity + intval($quantity);
                }
                if( $totalQuantity ) {
                    $weight = $days / $totalQuantity;
                } else {
                    $weight = 1;
                }

//                $link = $this->container->get('router')->generate(
//                    'translationalresearch_request_show',
//                    array("id"=>$transRequest->getId()),
//                    UrlGeneratorInterface::ABSOLUTE_URL
//                );

                //3) convert quantity as weighted days
                foreach ($transRequest->getProducts() as $product) {
                    $quantity = $product->getQuantity();
                    $category = $product->getCategory();
                    if( $category ) {
                        $categoryIndex = $category->getShortInfo();
                        $weightedQuantity = $weight * $quantity;
                        $requestCategoryWeightQuantityArr[$categoryIndex][$index] = $weightedQuantity;
                        //$requestCategoryWeightQuantityArr[$categoryIndex][$index] = array("value"=>$weightedQuantity,"link"=>$link);
                    }
                }

            }//foreach

            $combinedTrpData = array();
            foreach($requestCategoryWeightQuantityArr as $categoryIndex=>$arr) {
                $combinedTrpData[$categoryIndex] = $arr;
            }

            //$projectIrbPhaseArr[$index] = $days;
            //$combinedTrpData = array();
            //$combinedTrpData['IRB Review'] = $projectIrbPhaseArr;
            //$chartsArray = $this->getStackedChart($combinedTrpData, $chartName, "stack");

            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b' => 200)
            );

            //exit("Exit");
            $chartsArray = $this->getStackedChart($combinedTrpData, $chartName, "stack");
        }

        //"34. Turn-around Statistics: Average number of days for each project request approval phase" => "turn-around-statistics-days-project-state"
        if( $chartType == "turn-around-statistics-days-project-state" ) {
            $transresUtil = $this->container->get('transres_util');

            $reviewStates = array("irb_review","admin_review","committee_review","final_review");

            $projectStates = null;
            //$projectStates = array('final_approved','closed','final_rejected');

            //init array
            $averageDays = array();
            foreach($reviewStates as $state) {
                $stateLabel = $transresUtil->getStateLabelByName($state);
                $averageDays[$stateLabel] = 0;
            }

            $projects = $this->getProjectsByFilter($startDate, $endDate, $projectSpecialtyObjects, $projectStates);
            //echo "### $state projects count=".count($projects)."<br>";

            $countArr = array();

            foreach ($projects as $project) {
                //echo "<br>############ ".$project->getOid()." ############ <br>";

                foreach($reviewStates as $state) {

                    $stateLabel = $transresUtil->getStateLabelByName($state);

                    $days = $this->getDiffDaysByProjectState($project, $state);
                    if ($days > 0) {
                        if( isset($averageDays[$stateLabel]) ) {
                            $averageDays[$stateLabel] = $averageDays[$stateLabel] + $days;
                        } else {
                            $averageDays[$stateLabel] = $days;
                        }

                        if( isset($countArr[$stateLabel]) ) {
                            $countArr[$stateLabel] = $countArr[$stateLabel] + 1;
                        } else {
                            $countArr[$stateLabel] = 1;
                        }
                    }

                }//foreach state

            }//foreach project

            //exit("exit: $chartName");

            $averageDaysNew = array();
            foreach($averageDays as $stateLabel=>$days) {
                $count = $countArr[$stateLabel];
                if( $count > 0 ) {
                    //$stateLabel = $stateLabel . " (" . $count . " projects)";
                    $avgDaysInt = round($days / $count);
                    $averageDaysNew[$stateLabel] = $avgDaysInt;
                }
            }

            $chartName = $chartName . " (based on " . count($projects) . " approved or closed projects)";

            $chartsArray = $this->getChart($averageDaysNew, $chartName,'bar',$layoutArray);
        }

        //"35. Turn-around Statistics: Number of days for each project request approval phase" => "turn-around-statistics-days-per-project-state"
        if( $chartType == "turn-around-statistics-days-per-project-state" ) {

            $projectIrbPhaseArr = array();
            $projectAdminPhaseArr = array();
            $projectCommitteePhaseArr = array();
            $projectFinalPhaseArr = array();

            $reviewStates = array("irb_review","admin_review","committee_review","final_review");

            //'final_approved' OR project.state = 'closed OR 'final_rejected'
            $projectStates = null;
            //$projectStates = array('final_approved','closed','final_rejected');
            $projects = $this->getProjectsByFilter($startDate, $endDate, $projectSpecialtyObjects, $projectStates);
            //echo "### $state projects count=".count($projects)."<br>";

            foreach ($projects as $project) {

                $index = $project->getOid();

                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_show',
                    array("id"=>$project->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                foreach($reviewStates as $state) {

                    $days = $this->getDiffDaysByProjectState($project, $state);

                    if ($days > 0) {
                        if ($state == "irb_review") {
                            //$projectIrbPhaseArr[$index] = $days;
                            $projectIrbPhaseArr[$index] = array("value"=>$days,"link"=>$link);
                        }
                        if ($state == "admin_review") {
                            //$projectAdminPhaseArr[$index] = $days;
                            $projectAdminPhaseArr[$index] = array("value"=>$days,"link"=>$link);
                        }
                        if ($state == "committee_review") {
                            //$projectCommitteePhaseArr[$index] = $days;
                            $projectCommitteePhaseArr[$index] = array("value"=>$days,"link"=>$link);
                        }
                        if ($state == "final_review") {
                            //$projectFinalPhaseArr[$index] = $days;
                            $projectFinalPhaseArr[$index] = array("value"=>$days,"link"=>$link);
                        }
                    }

                }//foreach state

            }//foreach project

            $chartName = $chartName . " (based on " . count($projects) . " approved or closed projects)";

            $irbTitle = $this->getStateTitleWithAverageDays('IRB Review',$projectIrbPhaseArr);
            $adminTitle = $this->getStateTitleWithAverageDays('Admin Review',$projectAdminPhaseArr);
            $committeeTitle = $this->getStateTitleWithAverageDays('Committee Review',$projectCommitteePhaseArr);
            $finalTitle = $this->getStateTitleWithAverageDays('Final Review',$projectFinalPhaseArr);

            $combinedTrpData = array();
            $combinedTrpData[$irbTitle] = $projectIrbPhaseArr;
            $combinedTrpData[$adminTitle] = $projectAdminPhaseArr;
            $combinedTrpData[$committeeTitle] = $projectCommitteePhaseArr;
            $combinedTrpData[$finalTitle] = $projectFinalPhaseArr;

            $chartsArray = $this->getStackedChart($combinedTrpData, $chartName, "stack");
        }

        //third bar graph showing how many days on average it took for Invoices to go from “Issued” to “Paid”
        //"36. Turn-around Statistics: Average number of days for invoices to be paid" =>                 "turn-around-statistics-days-paid-invoice"
        if( $chartType == "turn-around-statistics-days-paid-invoice" ) {
            $averageDays = array();

            $invoiceStates = array("Paid in Full","Paid Partially");

            $startDate->modify( 'first day of last month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                $thisEndDate->modify( 'first day of next month' );
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y").": ";
                $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects, $invoiceStates);
                //$transRequests = $this->getRequestsByAdvanceFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$category);
                $startDate->modify( 'first day of next month' );

                //echo "<br>";
                //echo "invoices=".count($invoices)." (".$startDateLabel.")<br>";

                //$apcpResultStatArr = $this->getProjectRequestInvoiceChart($transRequests,$apcpResultStatArr,$startDateLabel);

                $daysTotal = 0;
                $count = 0;

                foreach($invoices as $invoice) {
                    //echo "invoice=".$invoice->getOid()."<br>";
                    //Number of days to go from Submitted to Completed
                    $issued = $invoice->getIssuedDate(); //“Issued”
                    if( 0 && !$issued ) {
                        continue;
                        //all issued dates are pre-populated by http://127.0.0.1/order/translational-research/dashboard/graphs/populate-dates
                        $issued = $this->getInvoiceIssuedDate($invoice);
                    }
                    if( !$issued ) {
                        //exit('no issue date');
                        continue;
                        $issued = $invoice->getCreateDate();
                    }
                    $paid = $invoice->getPaidDate(); //“Paid”
                    if( !$paid ) {
                        //continue;
                        $paid = $invoice->getUpdateDate(); //“Paid”
                    }
                    $dDiff = $issued->diff($paid);
                    //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
                    $days = $dDiff->days;
                    //echo "days=".$days."<br>";
                    $days = intval($days);
                    if( $days > 0 ) {
                        $daysTotal = $daysTotal + intval($days);
                        $count++;
                    }
                }

                $startDateLabel = $startDateLabel . " (" . count($invoices) . " invoices)";

                if( $count > 0 ) {
                    $avgDaysInt = round($daysTotal/$count);
                    $averageDays[$startDateLabel] = $avgDaysInt;
                } else {
                    $averageDays[$startDateLabel] = null;
                }

            } while( $startDate < $endDate );

            $chartsArray = $this->getChart($averageDays, $chartName,'bar',$layoutArray);
        }

        //"37. Turn-around Statistics: Number of days for each invoice to be paid (based on fully and partially paid invoices)" => "turn-around-statistics-days-per-paid-invoice",
        if( $chartType == "turn-around-statistics-days-per-paid-invoice" ) {
            $invoiceStates = array("Paid in Full","Paid Partially");
            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects, $invoiceStates);
            //echo "### $state invoices count=".count($invoices)."<br>";

            $countArr = array();

            foreach($invoices as $invoice) {
                //echo "invoice=".$invoice->getOid()."<br>";
                //Number of days to go from Submitted to Completed
                $issued = $invoice->getIssuedDate(); //“Issued”
                if( !$issued ) {
                    //exit('no issue date');
                    continue;
                    //$issued = $invoice->getCreateDate();
                }
                $paid = $invoice->getPaidDate(); //“Paid”
                if( !$paid ) {
                    //continue;
                    $paid = $invoice->getUpdateDate(); //“Paid”
                }
                $dDiff = $issued->diff($paid);
                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
                $days = $dDiff->days;
                //echo "days=".$days."<br>";
                $days = intval($days);
                if( $days > 0 ) {
                    $invoiceIndex = $invoice->getOid();
                    //$countArr[$invoiceIndex] = $days;

                    $link = $this->container->get('router')->generate(
                        'translationalresearch_invoice_show',
                        array("oid"=>$invoice->getId()),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    $countArr[$invoiceIndex] = array("value"=>$days,"link"=>$link);
                }
            }

            //$chartName = $chartName . " (based on " . count($projects) . " approved or closed projects)";

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => $chartName,
                'margin' => array('b'=>200)
            );

            $chartsArray = $this->getChart($countArr, $chartName,'bar',$layoutArray);
        }

        //"38. Turn-around Statistics: Top 10 PIs with most delayed unpaid invoices" => "turn-around-statistics-pis-with-delayed-unpaid-invoices",
        if( $chartType == "turn-around-statistics-pis-with-delayed-unpaid-invoices" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');

            $pisUnpaidInvoicesArr = array();

            //get unpaid and delayd invoices
            //$invoiceStates = array("Unpaid/Issued");
            //$invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects, $invoiceStates);
            $invoices = $transresRequestUtil->getOverdueInvoices();

            foreach($invoices as $invoice) {
                $pi = $invoice->getPrincipalInvestigator();
                if( $pi ) {
                    $piIndex = $pi->getUsernameOptimal();
//                    if( isset($pisUnpaidInvoicesArr[$piIndex]) ) {
//                        $pisUnpaidInvoicesArr[$piIndex] = $pisUnpaidInvoicesArr[$piIndex] + 1;
//                    } else {
//                        $pisUnpaidInvoicesArr[$piIndex] = 1;
//                    }

                    if (isset($pisUnpaidInvoicesArr[$piIndex])) {
                        $count = $pisUnpaidInvoicesArr[$piIndex] + 1;
                    } else {
                        $count = 1;
                    }
                    $pisUnpaidInvoicesArr[$piIndex] = $count;

                    $titleCount++;
                }
            }//foreach

            //$titleCount = $titleCount . " (invoices ".count($invoices).")";

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"PIs");
            $pisUnpaidInvoicesArrTop = $this->getTopArray($pisUnpaidInvoicesArr,$showOther);
            $chartsArray = $this->getChart($pisUnpaidInvoicesArrTop, $chartName,'pie',$layoutArray," : ");
        }

        //"39. Turn-around Statistics: Top 10 PIs with highest total unpaid, overdue invoices" => "turn-around-statistics-pis-with-highest-total-unpaid-invoices",
        if( $chartType == "turn-around-statistics-pis-with-highest-total-unpaid-invoices" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');

            $pisUnpaidInvoicesTotalArr = array();
            $totalUnpaid = 0;

            $invoices = $transresRequestUtil->getOverdueInvoices();

            foreach($invoices as $invoice) {
                $pi = $invoice->getPrincipalInvestigator();
                if( $pi ) {
                    $piIndex = $pi->getUsernameOptimal(); // . " (".$invoice->getOid().")";
                    //$pisUnpaidInvoicesTotalArr[$piIndex] = $invoice->getTotal();
                    $total = $invoice->getTotal();
                    $totalUnpaid = $totalUnpaid + intval($total);

                    if (isset($pisUnpaidInvoicesTotalArr[$piIndex])) {
                        //$count = $pisUnpaidInvoicesArr[$piIndex] + 1;
                        $total = $pisUnpaidInvoicesTotalArr[$piIndex] + $total;
                    }
                    $pisUnpaidInvoicesTotalArr[$piIndex] = $total;

                    $titleCount++;
                }
            }//foreach

            //$titleCount = $titleCount . " (invoices ".count($invoices).")";

            //$chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $chartName = $chartName . " (" . $titleCount . " invoices for total $" . $this->getNumberFormat($totalUnpaid) . ")";

            $showOther = $this->getOtherStr($showLimited,"Invoices");
            $pisUnpaidInvoicesTotalArrTop = $this->getTopArray($pisUnpaidInvoicesTotalArr,$showOther);
            $chartsArray = $this->getChart($pisUnpaidInvoicesTotalArrTop, $chartName,'pie',$layoutArray," : $");
        }

        //"40. Turn-around Statistics: Top 10 PIs combining amounts and delay duration for unpaid, overdue invoices" => "turn-around-statistics-pis-combining-total-delayed-unpaid-invoices",
        if( $chartType == "turn-around-statistics-pis-combining-total-delayed-unpaid-invoices" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');

            $pisCombinedArr = array();
            $pisTotalUnpaidArr = array();
            $pisDaysArr = array();
            $pisCountArr = array();

            $totalUnpaid = 0;
            $totalCombined = 0;

            $invoices = $transresRequestUtil->getOverdueInvoices();

            foreach($invoices as $invoice) {
                $pi = $invoice->getPrincipalInvestigator();
                if( $pi ) {
                    $piIndex = $pi->getUsernameOptimal();
                    $total = $invoice->getTotal();
                    $total = intval($total);
                    $totalUnpaid = $totalUnpaid + $total;

                    //get number of due days (months)
                    $nowDate = new \DateTime();
                    $dueDate = $invoice->getDueDate();
                    if( !$dueDate ) {
                        continue; //ignore invoices without duedate
                    }
                    $diff = $nowDate->diff($dueDate);
                    $months = (($diff->format('%y') * 12) + $diff->format('%m'));  //full months difference;
                    $days = $diff->days;
                    //echo "days=".$days."<br>";
                    $dueTimeNumber = intval($months);
                    //if months is less than 1, use 1
                    //if( !$dueTimeNumber || $dueTimeNumber <= 0 ) {
                    //    $dueTimeNumber = 1;
                    //}

                    //multiply invoice amount by the number of associated months it has remained unpaid
                    //for example - $100 unpaid invoice from 5 months ago => 5 x $100 + $600 invoice x 3 months ago = $2300 for this PI
                    $combined = $total * $dueTimeNumber;
                    $totalCombined = $totalCombined + $combined;

                    //combined index
                    if (isset($pisCombinedArr[$piIndex])) {
                        //$count = $pisUnpaidInvoicesArr[$piIndex] + 1;
                        $combined = $pisCombinedArr[$piIndex] + $combined;
                    }
                    $pisCombinedArr[$piIndex] = $combined;

                    //total owed
                    if (isset($pisTotalUnpaidArr[$piIndex])) {
                        $total = $pisTotalUnpaidArr[$piIndex] + $total;
                    }
                    $pisTotalUnpaidArr[$piIndex] = $total;

                    //median number of days invoice has been unpaid
                    if (isset($pisDaysArr[$piIndex])) {
                        $days = $pisDaysArr[$piIndex] + $days;
                    }
                    $pisDaysArr[$piIndex] = $days;

                    //count for this PI
                    if (isset($pisCountArr[$piIndex])) {
                        $count = $pisCountArr[$piIndex] + 1;
                    } else {
                        $count = 1;
                    }
                    $pisCountArr[$piIndex] = $count;

                    $titleCount++;
                }
            }//foreach

            //$titleCount = $titleCount . " (invoices ".count($invoices).")";

            //in the legend titles list PI name : total owed : median number of days invoice has been unpaid
            $pisCombinedArrNew = array();
            foreach($pisCombinedArr as $index => $combined) {
                //total
                $total = $pisTotalUnpaidArr[$index];
                //$total = $this->getNumberFormat($total);

                //days
                $days = $pisDaysArr[$index];
                $count = $pisCountArr[$index];
                if( $count ) {
                    $days = round($days / $count);
                } else {
                    $days = "unknown";
                }

                //new index (legend)
                $newIndex = $index . " ($" . $this->getNumberFormat($total) . " total owed, " . $days . " average number of days invoice has been unpaid)";
                $pisCombinedArrNew[$newIndex] = $combined;
            }

            //$chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $chartName = $chartName . " (" . $titleCount . " invoices for total combined index $" . $this->getNumberFormat($totalCombined) . ")";

            $layoutArray['width'] = $layoutArray['width'] * 1.3; //1400;

            $showOther = $this->getOtherStr($showLimited,"Invoices");
            //getTopArray($piProjectCountArr, $showOthers=false, $descriptionArr=array(), $maxLen=50)
            $pisCombinedArrTop = $this->getTopArray($pisCombinedArrNew,$showOther,array(),150);
            $chartsArray = $this->getChart($pisCombinedArrTop, $chartName,'pie',$layoutArray," : $");
        }

        if( $chartType == "" ) {

        }

        if( $chartType == "" ) {

        }



        //$chartsArray = array(); //testing
        //$chartsArray = null; //testing

        if( !is_array($chartsArray) ) {
            //echo "null <br>";
            $chartKey = $this->getChartTypeByValue($chartType);
            $chartsArray['error'] = "Chart type '$chartKey' is not valid";
            $chartsArray['warning'] = false;
            return $chartsArray;
        }

        if( is_array($chartsArray) && count($chartsArray) == 0 ) {
            //echo "count is 0 <br>";
            if( !$warningNoData ) {
                $chartKey = $this->getChartTypeByValue($chartType);
                $warningNoData = "Chart data is not found for '$chartKey'";
            }
            $chartsArray['warning'] = $warningNoData;   //"Chart data is not found for '$chartKey'";
            $chartsArray['error'] = false;
            return $chartsArray;
        }

        
        return $chartsArray;
    }

}