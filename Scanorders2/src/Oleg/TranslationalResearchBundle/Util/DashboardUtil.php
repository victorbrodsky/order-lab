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
            "22. Generated Invoices by Status for Funded Projects" =>               "fees-by-invoices-per-funded-projects",
            "23. Generated Invoices by Status for Non-Funded Projects (Top 10)" =>  "fees-by-invoices-per-nonfunded-projects",
            "24. Generated Invoices by Status per PI (Top 10)" =>                   "fees-by-invoices-per-pi",
            //Pathologists Involved and number of projects
            "25. Total Invoiced Amounts for Projects per Pathologist Involved (Top 10)" =>             "fees-by-invoices-per-projects-per-pathologist-involved",
            "26. Total Invoiced Amounts for Funded Projects per Pathologist Involved (Top 10)" =>      "fees-by-invoices-per-funded-projects-per-pathologist-involved",
            "27. Total Invoiced Amounts for Non-Funded Projects per Pathologist Involved (Top 10)" =>  "fees-by-invoices-per-nonfunded-projects-per-pathologist-involved",

            "28. Total Number of Projects per Type" => "projects-per-type",
            "29. Total Number of Work Requests per Business Purpose" => "requests-per-business-purpose",

            "30. Turn-around Statistics: Average number of days to complete a Work Request" => "turn-around-statistics-days-complete-request",
            "31. Turn-around Statistics: Average number of days for each project request approval phase" => "turn-around-statistics-days-project-state",
            "" => "",
            "" => "",
            "" => "",
            "" => ""
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

    public function getChart( $dataArr, $title, $type='pie', $layoutArray=null, $valuePrefixLabel=null ) {

        if( count($dataArr) == 0 ) {
            return array();
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
                    if( strpos($valuePrefixLabel,'$') !== false ) {
                        $label = $label . " " . $valuePrefixLabel . $this->getNumberFormat($value);
                    } else {
                        $label = $label . " " . $valuePrefixLabel . $value;
                    }
                    //echo "value=$value<br>";
                }
                $labels[] = $label;
                $values[] = $value;
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

        if( count($values) == 0 ) {
            return array();
            //return array('error'=>"No data found corresponding to this chart parameters");
        }

        //echo "<pre>";
        //print_r($stackDataArray);
        //echo "</pre>";

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

    public function getInvoicesByFilter($startDate, $endDate, $projectSpecialties, $addOneEndDay=true, $compareType='last invoice generation date') {
        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:Invoice');
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
        $createDate = null; //get enter state date
        $updateDate = null; //get exit state date
        foreach($reviews as $review) {
            //phase enter date
            $enterDate = $this->getPreviousStateEnterDate($project,$state);
            if( !$enterDate ) {
                $enterDate = $review->getCreatedate();
            }
            if( $createDate ) {
                if( $enterDate < $createDate ) {
                    $createDate = $enterDate;
                }
            } else {
                $createDate = $enterDate;
            }
            //phase exit date
            if( $updateDate ) {
                if( $review->getUpdatedate() > $updateDate ) {
                    $updateDate = $review->getUpdatedate();
                }
            } else {
                $updateDate = $review->getUpdatedate();
            }
        }

        if( !$createDate ) {
            $createDate = $project->getCreateDate();
        }

        if( !$updateDate && $state == "final_approved" ) {
            //echo "final state=".$state."<br>";
            $updateDate = $project->getApprovalDate();
        }
        if( !$updateDate ) {
            //echo "***state=".$state."<br>";
            $updateDate = $project->getUpdatedate();
        } else {
            //echo "###<br>";
        }

        //Number of days to go from review's createdate to review's updatedate
        $dDiff = $createDate->diff($updateDate);
        //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
        $days = $dDiff->days;
        //echo $state.": days=".$days."<br>";
        $days = intval($days);
        return $days;
    }
    public function getStateExitDate($project,$state) {
        $transresUtil = $this->container->get('transres_util');
        $reviews = $transresUtil->getReviewsByProjectAndState($project,$state);

        //get earliest create date and latest update date
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
    public function getReviewExitDate($project,$state) {
        $transresUtil = $this->container->get('transres_util');
        $reviews = $transresUtil->getReviewsByProjectAndState($project,$state);

        //get earliest create date and latest update date
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
    public function getPreviousStateEnterDate($project,$state) {
        if( $state == "irb_review" ) {
            $date = $project->getCreateDate();
            //$date = $this->getStateEnterDate($project,"irb_review");
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

        ///////////// 1. Principle Investigators by Affiliation ///////////////////
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

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $pis = $project->getPrincipalInvestigators();
                foreach ($pis as $pi) {
                    //1. Principle Investigators by Affiliation
                    if( $this->isUserBelongsToInstitution($pi,$department) ) {
                        //WCM Pathology Faculty - WCM Department of Pathology and Laboratory Medicine in any Title’s department field
                        $piWcmPathologyCounter++;
                    } elseif ( $this->isUserBelongsToInstitution($pi,$institution) ) {
                        //WCM Other Departmental Faculty - WCM institution
                        $piWcmCounter++;
                    } else {
                        //Other Institutions
                        $piOtherCounter++;
                    }
                }
            }

            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';

            $titleTotal = $piWcmPathologyCounter + $piWcmCounter;
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

            $labels = array(
                "$institutionAbbreviation $departmentAbbreviation Faculty"." ".$piWcmPathologyCounter,
                "$institutionAbbreviation Other Departmental Faculty"." ".$piWcmCounter,
                //'Other Institutions'." ".$piOtherCounter
            );
            //$values = array($piWcmPathologyCounter,$piWcmCounter,$piOtherCounter);
            $values = array($piWcmPathologyCounter,$piWcmCounter);

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
        ///////////// EOF 1. Principle Investigators by Affiliation ///////////////////

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

                    $titleCount++;
                }
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

                $pis = $project->getPrincipalInvestigators();
                foreach ($pis as $pi) {
                    $userName = $pi->getUsernameOptimal();
                    $userId = $pi->getId();

                    if( $fundingNumber ) {
                        if( isset($piFundedProjectCountArr[$userId]) && isset($piFundedProjectCountArr[$userId]['value']) ) {
                            $count = $piFundedProjectCountArr[$userId]['value'] + 1;
                        } else {
                            $count = 1;
                        }
                        $piFundedProjectCountArr[$userId]['value'] = $count;
                        $piFundedProjectCountArr[$userId]['label'] = $userName;
                        $piFundedProjectCountArr[$userId]['objectid'] = $userId;
                        $piFundedProjectCountArr[$userId]['pi'] = $userId;
                        $piFundedProjectCountArr[$userId]['show-path'] = "project";

                        $titleCount++;
                    }
                }//foreach $pis
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

                $pis = $project->getPrincipalInvestigators();
                foreach ($pis as $pi) {
                    $userName = $pi->getUsernameOptimal();
                    $userId = $pi->getId();

                    if( $fundingNumber ) {
                        //do nothing
                    } else {
                        if( isset($piUnFundedProjectCountArr[$userId]) && isset($piUnFundedProjectCountArr[$userId]['value']) ) {
                            $count = $piUnFundedProjectCountArr[$userId]['value'] + 1;
                        } else {
                            $count = 1;
                        }
                        $piUnFundedProjectCountArr[$userId]['value'] = $count;
                        $piUnFundedProjectCountArr[$userId]['label'] = $userName;
                        $piUnFundedProjectCountArr[$userId]['objectid'] = $userId;
                        $piUnFundedProjectCountArr[$userId]['pi'] = $userId;
                        $piUnFundedProjectCountArr[$userId]['show-path'] = "project";

                        $titleCount++;
                    }
                }//foreach $pis
            }//foreach $projects

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piUnFundedProjectCountTopArr = $this->getTopMultiArray($piUnFundedProjectCountArr,$showOther);
            $filterArr['funded'] = false;
            $chartsArray = $this->getChartByMultiArray( $piUnFundedProjectCountTopArr, $filterArr, $chartName,"pie",null," : ");
        }
        ///////////////// EOF 4. Total number of Non-Funded Projects per PI (Top 10) /////////////////

        //2a. Total number of projects per Pathologist Involved (Top 10)
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

                    $titleCount++;
                }
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
        // 3a. Total number of Funded Projects per Pathologist Involved (Top 10)
        if( $chartType == "funded-projects-per-pathologist-involved" ) {
            $pathologistFundedProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $fundingNumber = $project->getFunded();

                $pathologists = $project->getPathologists();
                foreach ($pathologists as $pathologist) {
                    $userName = $pathologist->getUsernameOptimal();
                    if( $fundingNumber ) {
                        if (isset($pathologistFundedProjectCountArr[$userName])) {
                            $count = $pathologistFundedProjectCountArr[$userName] + 1;
                        } else {
                            $count = 1;
                        }
                        $pathologistFundedProjectCountArr[$userName] = $count;
                        $titleCount++;
                    }
                }//foreach $pathologists
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

                $pathologists = $project->getPathologists();
                foreach ($pathologists as $pathologist) {
                    $userName = $pathologist->getUsernameOptimal();
                    if( $fundingNumber ) {
                        //do nothing
                    } else {
                        if (isset($pathologistNonFundedProjectCountArr[$userName])) {
                            $count = $pathologistNonFundedProjectCountArr[$userName] + 1;
                        } else {
                            $count = 1;
                        }
                        $pathologistNonFundedProjectCountArr[$userName] = $count;
                        $titleCount++;
                    }
                }//foreach $pathologists
            }//foreach $projects

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $pathologistNonFundedProjectCountTopArr = $this->getTopArray($pathologistNonFundedProjectCountArr,$showOther);
            //$filterArr['funded'] = true;
            $chartsArray = $this->getChart($pathologistNonFundedProjectCountTopArr, $chartName,"pie",$layoutArray," : ");
        }
        ///////////////// EOF 4a. Total number of Non-Funded Projects per Pathologist Involved (Top 10) /////////////////


        //Work request statistics
        //5. Total Number of Work Requests by Funding Source
        if( $chartType == "requests-by-funding-source" ) {

            $fundedRequestCount = 0;
            $notFundedRequestCount = 0;

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                if( $transRequest->getFundedAccountNumber() ) {
                    $fundedRequestCount++;
                } else {
                    $notFundedRequestCount++;
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

            $labels = array('Funded'." : ".$fundedRequestCount,'Non-Funded'." : ".$notFundedRequestCount);
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

        //6. Total number of Requests per Project (Top 10)
        if( $chartType == "requests-per-project" ) {
            $requestPerProjectArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                $project = $transRequest->getProject();
                $projectIndex = $project->getOid(false);
                $projectId = $project->getId();
                $piIdArr = array();

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
            $showOther = $this->getOtherStr($showLimited,"Projects");
            $requestPerProjectTopArr = $this->getTopMultiArray($requestPerProjectArr,$showOther);
            $filterArr['funded'] = null;
            $chartsArray = $this->getChartByMultiArray($requestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ");
        }

        //7. Total number of Requests per Funded Project (Top 10)
        if( $chartType == "requests-per-funded-projects" ) {
            $fundedRequestPerProjectArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                if( $transRequest->getFundedAccountNumber() ) {
                    $project = $transRequest->getProject();
                    $projectIndex = $project->getOid(false);
                    $projectId = $project->getId();
                    $piIdArr = array();

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
            $showOther = $this->getOtherStr($showLimited,"Projects");
            $fundedRequestPerProjectTopArr = $this->getTopMultiArray($fundedRequestPerProjectArr,$showOther);
            $filterArr['funded'] = true;
            $chartsArray = $this->getChartByMultiArray( $fundedRequestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ");
        }

        //8. Total number of Requests per Non-Funded Project (Top 10)
        if( $chartType == "requests-per-nonfunded-projects" ) {
            $unFundedRequestPerProjectArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                if( $transRequest->getFundedAccountNumber() ) {
                    //do nothing
                } else {
                    $project = $transRequest->getProject();
                    $projectIndex = $project->getOid(false);
                    $projectId = $project->getId();
                    $piIdArr = array();

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
            $showOther = $this->getOtherStr($showLimited,"Projects");
            $unFundedRequestPerProjectTopArr = $this->getTopMultiArray($unFundedRequestPerProjectArr,$showOther);
            $filterArr['funded'] = false;
            $chartsArray = $this->getChartByMultiArray( $unFundedRequestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ");
        }

        //Work request statistics: Products/Services
        //9. TRP Service Productivity by Products/Services (Top 10)
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
            $showOther = $this->getOtherStr($showLimited,"Products/Services");
            $quantityCountByCategoryTopArr = $this->getTopArray($quantityCountByCategoryArr,$showOther);
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );
            $chartsArray = $this->getChart($quantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ");

        }

        //10. TRP Service Productivity for Funded Projects (Top 10)
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
            $showOther = $this->getOtherStr($showLimited,"Projects");
            $fundedQuantityCountByCategoryTopArr = $this->getTopArray($fundedQuantityCountByCategoryArr,$showOther);
            $chartsArray = $this->getChart($fundedQuantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ");
        }

        //11. TRP Service Productivity for Non-Funded Projects (Top 10)
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
            $showOther = $this->getOtherStr($showLimited,"Projects");
            $unFundedQuantityCountByCategoryTopArr = $this->getTopArray($unFundedQuantityCountByCategoryArr,$showOther);
            $chartsArray = $this->getChart($unFundedQuantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ");
        }

        //11a. TRP Service Productivity by Products/Services
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
            $showOther = $this->getOtherStr($showLimited,"Projects");
            $fundedQuantityCountByCategoryTopArr = $this->getTopArray($fundedQuantityCountByCategoryArr,$showOther);
            $unFundedQuantityCountByCategoryTopArr = $this->getTopArray($unFundedQuantityCountByCategoryArr,$showOther);

            $combinedTrpData = array();
            $combinedTrpData['Funded'] = $fundedQuantityCountByCategoryTopArr;  //$fundedQuantityCountByCategoryArr;
            $combinedTrpData['Not-Funded'] = $unFundedQuantityCountByCategoryTopArr;    //$unFundedQuantityCountByCategoryArr;
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

            $fundedTotalFees = $this->getNumberFormat($fundedTotalFees);
            $unFundedTotalFees = $this->getNumberFormat($unFundedTotalFees);

            $labels = array('Funded : $'.$fundedTotalFees,'Non-Funded : $'.$unFundedTotalFees);
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

        //13. Total Fees per Funded Project (Top 10)
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
                    $totalFee = $this->getNumberFormat($totalFee);
                    $fundedTotalFeesByRequestArr[$projectIndex] = $totalFee;

                    $titleCount = $titleCount + $subtotalFee;
                }

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"Projects");
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
                    $totalFee = $this->getNumberFormat($totalFee);
                    $unFundedTotalFeesByRequestArr[$projectIndex] = $totalFee;

                    $titleCount = $titleCount + $subtotalFee;
                }

            }//foreach $requests

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            $showOther = $this->getOtherStr($showLimited,"Projects");
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
                $totalFee = $this->getNumberFormat($totalFee);
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
                    $totalFee = $this->getNumberFormat($totalFee);
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
                    $totalFee = $this->getNumberFormat($totalFee);
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
                    if ($invoice->getStatus() == "Paid in Full") {
                        $paidInvoices++;
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

            $labels = array('Paid'.' : $'.$this->getNumberFormat($totalFundedPaidFees),'Unpaid (Due)'.' : $'.$this->getNumberFormat($totalFundedDueFees));
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

        //23. Generated Invoices by Status per Funded Project (Top 10)
        if( $chartType == "fees-by-invoices-per-nonfunded-projects" ) {
            $invoicesByProjectArr = array();
            $invoicesFeesByProjectArr = array();

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

                //Generated Invoices by Status per Funded Project (Top 10)
                if ($transRequest->getFundedAccountNumber()) {
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
                }

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");
            //Generated Invoices by Status per Funded Project (Top 10)
            $showOther = $this->getOtherStr($showLimited,"Projects");
            $invoicesByProjectTopArr = $this->getTopArray($invoicesByProjectArr,$showOther);
            $invoicesFeesByProjectTopArr = $this->getTopArray($invoicesFeesByProjectArr,$showOther); //public function getTopArray(
            //merge two to attach fees to label
            $invoicesByProjectTopArr = $this->attachSecondValueToFirstLabel($invoicesByProjectTopArr,$invoicesFeesByProjectTopArr," : $");
            $chartsArray = $this->getChart($invoicesByProjectTopArr, $chartName,'pie',$layoutArray);
        }

        //24. Generated Invoices by Status per PI (Top 10)
        if( $chartType == "fees-by-invoices-per-pi" ) {
            $invoicesFeesByPiArr = array();
            $invoicePaidFeeArr = array();
            $invoiceDueFeeArr = array();

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
                if ($transRequest->getFundedAccountNumber()) {
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
                }

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");

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

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");

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
                }

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");

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
                }

            }//foreach invoices

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($titleCount),"$");

            $descriptionArr = array(
                array("paid $"," : $","limegreen","money",$invoicePaidFeeArr),
                array("due $"," : $","red","money",$invoiceDueFeeArr)
            );
            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther,$descriptionArr);
            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName,'pie',$layoutArray);
        }
        ///////////// EOF "23. Total Invoiced Amounts of Non-Funded Projects per Pathologist Involved (Top 10)" /////////////

        //"28. Total Number of Projects per Type" => "projects-per-type"
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


        //"29. Total Number of Requests per Business Purpose" => "requests-per-business-purpose"
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

        //"30. Turn-around Statistics: Average number of days to complete a Work Request" => "turn-around-statistics-days-complete-request"
        if( $chartType == "turn-around-statistics-days-complete-request" ) {
            $averageDays = array();

            $startDate->modify( 'first day of last month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                $thisEndDate->modify( 'first day of next month' );
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y").": ";
                $category = null;
                $transRequests = $this->getRequestsByAdvanceFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$category,array("completed","completedNotified"));
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

//            if( $category ) {
//                //$categoryName = $this->tokenTruncate($category->getProductIdAndName(),50);
//                $categoryName = $category->getProductId();
//                $categoryStr = " (".$categoryName.")";
//            } else {
//                $categoryStr = null;
//            }
//            $chartName = $chartName.$categoryStr;

            $chartsArray = $this->getChart($averageDays, $chartName,'bar',$layoutArray);
        }

        //"31. Turn-around Statistics: Average number of days for each project request approval phase" => "turn-around-statistics-days-project-state"
//        if( $chartType == "111_turn-around-statistics-days-project-state" ) {
//            $transresUtil = $this->container->get('transres_util');
//            $averageDays = array();
//
//            $reviewStates = array(
//                array("irb_review","irb_missinginfo"),
//                array("admin_review","admin_missinginfo"),
//                array("committee_review"),
//                array("final_review"),
//            );
//
//            foreach($reviewStates as $reviewStateArr) {
//
//                $state = $reviewStateArr[0];
//                $stateLabel = $transresUtil->getStateLabelByName($state);
//
//                $projects = $this->getProjectsByFilter($startDate, $endDate, $projectSpecialtyObjects, $reviewStateArr);
//                echo "### $state projects count=".count($projects)."<br>";
//
//                $daysTotal = 0;
//                $count = 0;
//
//                foreach ($projects as $project) {
//
//                    $days = $this->getDiffDaysByProjectState($project,$state);
//                    if( $days > 0 ) {
//                        $daysTotal = $daysTotal + $days;
//                        $count++;
//                    }
//
//                }//foreach project
//
//                if( $count > 0 ) {
//                    $avgDaysInt = round($daysTotal/$count);
//                    //echo "daysTotal=".$daysTotal."; count=".$count."<br>";
//                    //echo "average days=".round($daysTotal / $count)."<br>";
//                    //$averageDays[$startDateLabel] = $daysTotal;
//                    $averageDays[$stateLabel] = $avgDaysInt;
//                } else {
//                    $averageDays[$stateLabel] = null;
//                }
//
//            }//foreach states
//
//            $chartsArray = $this->getChart($averageDays, $chartName,'bar',$layoutArray);
//        }
        if( $chartType == "turn-around-statistics-days-project-state" ) {
            $transresUtil = $this->container->get('transres_util');

            $reviewStates = array(
               "irb_review",
                "admin_review",
                "committee_review",
                "final_review"
            );

            //$state = $reviewStateArr[0];
            //$stateLabel = $transresUtil->getStateLabelByName($state);

            $projects = $this->getProjectsByFilter($startDate, $endDate, $projectSpecialtyObjects, $reviewStateArr);
            //echo "### $state projects count=".count($projects)."<br>";

            $averageDays = array();
            $countArr = array();

            foreach ($projects as $project) {

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
                            $countArr[$stateLabel] = $days;
                        }
                    }

                }//foreach state

            }//foreach project

            $averageDaysNew = array();
            foreach($averageDays as $stateLabel=>$days) {
                $count = $countArr[$stateLabel];
                $avgDaysInt = round($days/$count);
                $averageDaysNew[$stateLabel] = $avgDaysInt;
            }

            $chartsArray = $this->getChart($averageDaysNew, $chartName,'bar',$layoutArray);
        }

        if( $chartType == "" ) {

        }

        if( $chartType == "" ) {

        }

        if( $chartType == "" ) {

        }

        if( $chartType == "" ) {

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
            $chartKey = $this->getChartTypeByValue($chartType);
            $chartsArray['warning'] = "Chart data is not found for '$chartKey'";
            $chartsArray['error'] = false;
            return $chartsArray;
        }

        
        return $chartsArray;
    }

}