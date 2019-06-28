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
//use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
            "1. Principle Investigators by Affiliation (linked)" =>                      "pi-by-affiliation",
            "2. Total Number of Projects per PI (Top 10) (linked)" =>                    "projects-per-pi",
            "3. Total Number of Funded Projects per PI (Top 10) (linked)" =>             "funded-projects-per-pi",
            "4. Total Number of Non-Funded Projects per PI (Top 10) (linked)" =>         "nonfunded-projects-per-pi",
            //Pathologists Involved and number of projects
            "5. Total Number of Projects per Pathologist Involved (Top 10)" =>             "projects-per-pathologist-involved",
            "6. Total Number of Funded Projects per Pathologist Involved (Top 10)" =>      "funded-projects-per-pathologist-involved",
            "7. Total Number of Non-Funded Projects per Pathologist Involved (Top 10)" =>  "nonfunded-projects-per-pathologist-involved",
            //Work request statistics
            "8. Total Number of Work Requests by Funding Source (linked)" =>                 "requests-by-funding-source",
            "9. Projects with Most Work Requests (Top 10) (linked)" =>                       "requests-per-project",
            "10. Funded Projects with Most Work Requests (Top 10) (linked)" =>      "requests-per-funded-projects",
            "11. Non-Funded Projects with Most Work Requests (Top 10) (linked)" =>  "requests-per-nonfunded-projects",
            //   Products/Services
            "12. Service Productivity by Products/Services (Top 35)" =>     "service-productivity-by-service",
            "13. Service Productivity for Funded Projects (Top 25)" =>      "service-productivity-by-service-per-funded-projects",
            "14. Service Productivity for Non-Funded Projects (Top 25)" =>  "service-productivity-by-service-per-nonfunded-projects",
            "15. TRP Service Productivity by Products/Services" =>              "service-productivity-by-service-compare-funded-vs-nonfunded-projects",
            //Productivity statistics based on work requests
            "16. Total Fees by Work Requests" =>                                "fees-by-requests",
            "17. Total Fees per Funded Project (Top 10)" =>                     "fees-by-requests-per-funded-projects",
            "18. Total Fees per Non-Funded Project (Top 10)" =>                 "fees-by-requests-per-nonfunded-projects",
            "19. Total Fees per Investigator (Top 10)" =>                       "fees-by-investigators",
            "20. Total Fees per Investigator for Funded Projects (Top 10)" =>   "fees-by-investigators-per-funded-projects",
            "21. Total Fees per Investigator for Non-Funded Projects (Top 10)"=>"fees-by-investigators-per-nonfunded-projects",
            //Financial statistics based on invoices
            "22. Paid Invoices by Month" =>                              "fees-by-invoices-paid-per-month",
            "23. Generated Invoices for Funded Projects" =>              "fees-by-invoices-per-funded-projects",
            "24. Generated Invoices for Non-Funded Projects (Top 10)" =>  "fees-by-invoices-per-nonfunded-projects",
            "25. Generated Invoices per PI (Top 10)" =>                   "fees-by-invoices-per-pi",
            //Pathologists Involved and number of projects
            "26. Total Invoiced Amounts for Projects per Pathologist Involved (Top 10)" =>             "fees-by-invoices-per-projects-per-pathologist-involved",
            "27. Total Invoiced Amounts for Funded Projects per Pathologist Involved (Top 10)" =>      "fees-by-invoices-per-funded-projects-per-pathologist-involved",
            "28. Total Invoiced Amounts for Non-Funded Projects per Pathologist Involved (Top 10)" =>  "fees-by-invoices-per-nonfunded-projects-per-pathologist-involved",
            "29. Total Fees per Involved Pathologist for Non-Funded Projects (Top 10)" =>              "fees-per-nonfunded-projects-per-pathologist-involved",

            "30. Total Number of Projects per Type (linked)" => "projects-per-type",
            "31. Total Number of Work Requests per Business Purpose" => "requests-per-business-purpose",

            "32. Turn-around Statistics: Average number of days to complete a Work Request (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-request",
            "33. Turn-around Statistics: Number of days to complete each Work Request (based on 'Completed and Notified' requests) (linked)" => "turn-around-statistics-days-complete-per-request",
            "34. Turn-around Statistics: Number of days to complete each Work Request with products/services (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-per-request-with-product",
            "35. Turn-around Statistics: Average number of days for each project request approval phase" => "turn-around-statistics-days-project-state",
            "36. Turn-around Statistics: Number of days for each project request approval phase (linked)" => "turn-around-statistics-days-per-project-state",
            "37. Turn-around Statistics: Average number of days for invoices to be paid (based on fully and partially paid invoices)" => "turn-around-statistics-days-paid-invoice",
            "38. Turn-around Statistics: Number of days for each invoice to be paid (based on fully and partially paid invoices) (linked)" => "turn-around-statistics-days-per-paid-invoice",
            "39. Turn-around Statistics: Top 10 PIs with most delayed unpaid invoices (linked)" => "turn-around-statistics-pis-with-delayed-unpaid-invoices",
            "40. Turn-around Statistics: Top 10 PIs with highest total unpaid, overdue invoices (linked)" => "turn-around-statistics-pis-with-highest-total-unpaid-invoices",
            "41. Turn-around Statistics: Top 10 PIs by index (delay in months * invoiced amount, aggregate) for unpaid, overdue invoices (linked)" => "turn-around-statistics-pis-combining-total-delayed-unpaid-invoices",

            "42. Number of PIs in AP/CP vs Hematopathology (linked)" => "compare-projectspecialty-pis",
            "43. Number of AP/CP vs Hematopathology Project Requests (linked)" => "compare-projectspecialty-projects",
            "44. Number of AP/CP vs Hematopathology Project Requests (linked)" => "compare-projectspecialty-projects-stack",
            "45. Number of AP/CP vs Hematopathology Work Requests (linked)" => "compare-projectspecialty-requests",
            "46. Number of AP/CP vs Hematopathology Invoices (linked)" => "compare-projectspecialty-invoices",

            "47. Total Fees per Project Request Type (Top 10) (linked)" => "projects-fees-per-type",
            "48. Total Fees per Funded Project Request Type (Top 10) (linked)" => "projects-funded-fees-per-type",
            "49. Total Fees per Non-Funded Project Request Type (Top 10) (linked)" => "projects-unfunded-fees-per-type",

            "50. Total Fees per Work Requests Business Purpose (Top 10)" => "requests-fees-per-business-purpose",
            "51. Total Fees per Funded Work Requests Business Purpose (Top 10)" => "requests-funded-fees-per-business-purpose",
            "52. Total Fees per Non-Funded Work Requests Business Purpose (Top 10)" => "requests-unfunded-fees-per-business-purpose",

            "53. Turn-around Statistics: Number of days to complete each Work Request with person (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-per-request-with-user",
            "54. Turn-around Statistics: Top 50 most delinquent invoices (linked)" => "turn-around-statistics-delayed-unpaid-invoices-by-days",

            "55. Number of reminder emails sent per month (linked)" => "reminder-emails-per-month",

            "56. Number of successful logins for the TRP site per month" => "successful-logins-trp",
            "57. Number of successful logins per site" => "successful-logins-site",
            "58. Number of unique successful logins per site per month" => "successful-unique-logins-site-month",
            "59. Number of unique successful logins per site per week" => "successful-unique-logins-site-week",

            //"60. PIs with most projects" => "pis-with-most-projects",
            //"61. PIs with highest expenditures" => "pis-with-highest-expenditures",

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
            //echo $user.": parentNode:".$parentInstitution."(".$parentInstitution->getId().") and node:".$institution."(".$institution->getId().") are the same? <br>";
            if( $this->em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode($parentInstitution,$institution) ) {
                //echo $user.": isUserBelongsToInstitution Yes! <br>";
                return true;
            }
        }

        return false;
    }

    public function getNumberFormat($number,$digits=null) {
        //$number = 123456789;
        return number_format($number,$digits);
    }

    public function getOtherStr( $showLimited, $otherPrefix ) {
        if( $showLimited ) {
            return false; //show top ten only without others
        }
        return $otherPrefix;
    }

    //select top 10, BUT make sure the other PIs are still shown as "Other"
    public function getTopArray($piProjectCountArr, $showOthers=false, $descriptionArr=array(), $maxLen=50, $limit=10) {
        arsort($piProjectCountArr);
        //$limit = 10;
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
                    $descrPrefix = $descriptionSingleArr['descrPrefix'];
                    $descrPostfix = $descriptionSingleArr['descrPostfix'];
                    $valuePrefix = $descriptionSingleArr['valuePrefix'];
                    $valuePostfix = $descriptionSingleArr['valuePostfix'];
                    $descrColor = $descriptionSingleArr['descrColor'];
                    $descrType = $descriptionSingleArr['descrType'];
                    $descrValueArr = $descriptionSingleArr['descrValueArr'];
                    $descrValue = $descrValueArr[$index];
                    if( $descrType == "money" ) {
                        $descrValue = $this->getNumberFormat($descrValue);
                    }
                    if( $descrValue ) {
                        if( $descrColor ) {
                            $descr[] = '<span style="color:'.$descrColor.'">' . $descrPrefix . $descrValue . $descrPostfix . '</span>';
                        } else {
                            $descr[] = $descrPrefix . $descrValue;
                        }
                    }
                }//foreach

                if( count($descr) > 0 ) {
                    if( is_array($value) ) {
                        $valueLabel = $value['value'];
                    } else {
                        $valueLabel = $value;
                    }
                    if( strpos($valuePrefix,'$') !== false ) {
                        $valueLabel = $this->getNumberFormat($valueLabel);
                    } else {
                        $valueLabel = $valueLabel;
                    }
                    $index = $index . " " . $valuePrefix . $valueLabel . $valuePostfix . " (" . implode(", ",$descr) . ")";
                }

                $piProjectCountTopShortArr[$index] = $value;
            }//foreach

            return $piProjectCountTopShortArr;
        }//if

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

        //if( !$showOthers ) {
            //“Show only the top 10” - if it is checked, show only the top ten projects, if it is not checked, show the top 100
            //$limit = 20;
        //}

        //$otherId = "All other $showOthers combined";
        $otherId = str_replace("[[otherStr]]",$showOthers,$this->otherId);

        $otherObjectids = array();

        $count = 1;
        $piProjectCountTopArr = array();
        foreach($piProjectCountArr as $id=>$arr) {
            $value = $arr['value'];
            $label = $arr['label'];
            $objectid = $arr['objectid'];
            $pi = $arr['pi'];

            $showPath = null;
            $link = null;
            if( isset($arr['show-path']) ) {
                $showPath = $arr['show-path'];
            }
            if( isset($arr['link']) ) {
                $link = $arr['link'];
            }

            //echo "value=".$value."<br>";
            //echo $username.": ".$count."<br>";
            if( $value && $value != 0 ) {
                if ($count <= $limit || !$limit) {
                    $piProjectCountTopArr[$id]['value'] = $value;
                    $piProjectCountTopArr[$id]['label'] = $label;
                    $piProjectCountTopArr[$id]['show-path'] = $showPath;
                    $piProjectCountTopArr[$id]['link'] = $link;
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
                        $piProjectCountTopArr[$otherId]['link'] = $link;
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
                $link = $arr['link'];
                $pi = $arr['pi'];
                $objectid = $arr['objectid'];
                //echo "objectid=".$objectid."<br>";
                $label = $this->tokenTruncate($label,$maxLen);
                $piProjectCountTopShortArr[$id]['value'] = $value;
                $piProjectCountTopShortArr[$id]['label'] = $label;
                $piProjectCountTopShortArr[$id]['show-path'] = $showPath;
                $piProjectCountTopShortArr[$id]['link'] = $link;
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

    public function adjustBrightness($hex, $steps) {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));

        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
        }

        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';

        foreach ($color_parts as $color) {
            $color   = hexdec($color); // Convert to decimal
            $color   = max(0,min(255,$color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }

        return $return;
    }

    public function getChart( $dataArr, $title, $type='pie', $layoutArray=null, $valuePrefixLabel=null, $valuePostfixLabel=null, $descriptionArr=null, $hoverinfo=null ) {

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
            $origLabel = $label;
            if( is_array($valueData) ) {
                $value = $valueData["value"];
                $link = $valueData["link"];
            } else {
                $value = $valueData;
                $link = null;
            }
            //value
            if ($type == "bar" || ($value && $value != 0)) {
                if( ($valuePrefixLabel || $valuePostfixLabel) && $value ) {
                    if (strpos($valuePrefixLabel, '$') !== false) {
                        $label = $label . " " . $valuePrefixLabel . $this->getNumberFormat($value) . $valuePostfixLabel;
                    } else {
                        $label = $label . " " . $valuePrefixLabel . $value . $valuePostfixLabel;
                    }
                    //echo "value=$value<br>";
                }

                if( $descriptionArr ) {
                    if( isset($descriptionArr[$origLabel]) ) {
                        $label = $label . $descriptionArr[$origLabel];
                    }
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

        //color array for bars
//        if( $type == "bar" || $type == "stack" ) {
//            //$chartDataArray['marker']['color'] = array('rgb(142,124,195)','red','green');
//            $colors = array();
//            $initColor = "#3366CC";
//            $step = 100/count($values);
//            $count = 0;
//            foreach($values as $value) {
//                if($value) {
//                    $colors[] = $this->adjustBrightness($initColor,$count);
//                    $count = $count + 10;;
//                } else {
//                    $colors[] = 'white';
//                }
//            }
//
//            $chartDataArray['marker'] = array('color'=>$colors);    //['color'] = array('rgb(142,124,195)','red','green');
//        }

        //$chartDataArray["text"] = "111";
        $chartDataArray["textinfo"] = "value+percent";
        //hoverinfo: label+text+value+percent
        $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
        $chartDataArray['direction'] = 'clockwise';
        //$chartDataArray["hoverinfo"] = "percent+label";
        $chartDataArray["hoverinfo"] = $hoverinfo;

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
    public function getChartByMultiArray( $dataArr, $filterArr, $title, $type='pie', $layoutArray=null, $valuePrefixLabel=null, $hoverinfo=null ) {

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
            $objectid = $arr['objectid'];
            $pi = $arr['pi'];

            $showPath = null;
            $link = null;
            if( isset($arr['show-path']) ) {
                $showPath = $arr['show-path'];
            }
            if( isset($arr['link']) ) {
                $link = $arr['link'];
            }

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
                        //'filter[]' => $projectSpecialtyObjects
                    );

                    if( $funded === true ) {
                        $linkFilterArr['filter[fundingType]'] = 'Funded';
                    }
                    if( $funded === false ) {
                        $linkFilterArr['filter[fundingType]'] = 'Non-Funded';
                    }

                    if( count($projectSpecialtyObjects) > 0 ) {
                        $projectSpecialtyObject = $projectSpecialtyObjects[0];
                        $linkFilterArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();
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
                        $linkFilterArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();
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
                        //'filter[]' => $projectSpecialtyObjects,
                        'filter[searchProjectType]' => $objectid
                    );

                    if( count($projectSpecialtyObjects) > 0 ) {
                        $projectSpecialtyObject = $projectSpecialtyObjects[0];
                        $linkFilterArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();
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
            }//if bar or value

        }//foreach

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
        //$chartDataArray["hoverinfo"] = "percent+label";
        $chartDataArray["hoverinfo"] = $hoverinfo;

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
        //$stackDataSumArray = array();
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

//                if( isset($stackDataSumArray[$label]) ) {
//                    $sumValue = $stackDataSumArray[$label] + $value;
//                } else {
//                    $sumValue = $value;
//                }
//                $stackDataSumArray[$label] = $sumValue;
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
    public function getRequestsByAdvanceFilter($startDate, $endDate, $projectSpecialties, $productservice, $states=null, $addOneEndDay=true) {
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

        if( $productservice ) {
            $dql->leftJoin('request.products','products');
            $dql->leftJoin('products.category','category');
            $dql->andWhere("category.id = :categoryId");
            $dqlParameters["categoryId"] = $productservice; //->getId();
        }

        $query = $em->createQuery($dql);

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        //echo implode(",",$projectSpecialtyNamesArr)." Projects=".count($projects)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";

        return $projects;
    }

    public function getInvoicesByFilter($startDate, $endDate, $projectSpecialties, $states=null, $overdue=false, $addOneEndDay=true, $compareType='last invoice generation date') {
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

        if( $overdue ) {
            $todayDate = new \DateTime();
            //$todayDate->modify('+1 day'); //make sure it's overdue (not considering hours and time zone difference)
            $dql->andWhere("invoice.dueDate IS NOT NULL AND :todayDate > invoice.dueDate");
            $dqlParameters["todayDate"] = $todayDate->format('Y-m-d');
        }

        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);
        //echo "query=".$query->getSql()."<br>";

        $projects = $query->getResult();

        //echo implode(",",$projectSpecialtyNamesArr)." Projects=".count($projects)." (".$startDate->format('d-M-Y')." - ".$endDate->format('d-M-Y').")<br>";

        return $projects;
    }

    public function getTitleWithTotal($chartName,$total,$prefix=null,$postfix="total") {
        //$postfix = "total quantity";
        //$postfix = "total";
        //if( $prefix ) {
        //    $postfix = "total";
        //}
        return $chartName . " - " . $prefix . $total . " " . $postfix;
    }

    public function getTotalSegmentCount($arr) {
        //print_r($arr);
        $titleCount = 0;
        foreach($arr as $id=>$thisArr) {
            if( is_array($thisArr) ) {
                $titleCount = $titleCount + $thisArr['value'];
            } else {
                if( is_integer($thisArr) ) {
                    $titleCount = $titleCount + intval($thisArr);
                } else {
                    //???
                    $titleCount = $titleCount + intval($thisArr);
                }
            }
        }
        return $titleCount;
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

//        //Number of days to go from review's createdate to review's updatedate
//        $dDiff = $startDate->diff($endDate);
//        //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//        $days = $dDiff->days;
//        //echo $state.": days=".$days."<br>";
//        $days = intval($days);
//
//        //show minimum 1 day
//        if( !$days || $days == 0 ) {
//            $days = 1;
//        }

        $days = $this->calculateDays($startDate,$endDate);

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

    public function getProjectRequestInvoiceChart($apcpProjects,$resStatArr,$startDateLabel) {
        $transresRequestUtil = $this->container->get('transres_request_util');
        //get requests, invoices

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

        $resStatArr['projects'][$startDateLabel] = count($apcpProjects);
        $resStatArr['requests'][$startDateLabel] = $requestCount;
        $resStatArr['invoices'][$startDateLabel] = $invoiceCount;

        return $resStatArr;
    }

    public function calculateDays($startDate,$endDate) {
        //1) calculate days
        $dDiff = $startDate->diff($endDate);
        //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
        $days = $dDiff->days;
        //echo "days=".$days."<br>";
        $days = intval($days);

        if( !$days || $days == 0 ) {
            $days = 1;
        }

        return $days;
    }



    public function getDashboardChart($request) {

        //ini_set('memory_limit', '30000M');

        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        $projectSpecialty = $request->query->get('projectSpecialty');
        $showLimited = $request->query->get('showLimited');
        $chartType = $request->query->get('chartType');
        $productservice = $request->query->get('productservice');

        //echo "start=".$startDate."<br>";
        //echo "end=".$endDate."<br>";

        if( $startDate ) {
            $startDate = date_create_from_format('m/d/Y', $startDate); //10/31/2017 to DateTime
        }
        if( $endDate ) {
            $endDate = date_create_from_format('m/d/Y', $endDate); //10/31/2017 to DateTime
        }

        if( $startDate ) {
            $startDateStr = $startDate->format('m/d/Y');
        }
        if( $endDate ) {
            $endDateStr = $endDate->format('m/d/Y');
        }
        //echo "start=".$startDate->format('m/d/Y')."<br>";
        //echo "end=".$endDate->format('m/d/Y')."<br>";

        $projectSpecialtyObjects = array();
        //echo "projectSpecialty=".$projectSpecialty."<br>";
        if( $projectSpecialty != 0 ) {
            //echo "projectSpecialty=".$projectSpecialty."<br>";
            $projectSpecialtyObject = $this->em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }
        //exit('1');

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

        //1. Principle Investigators by Affiliation (linked)
        if( $chartType == "pi-by-affiliation" ) {

            $userSecUtil = $this->container->get('user_security_utility');
            //$piWcmPathologyCounter = 0;
            //$piWcmCounter = 0;
            //$piOtherCounter = 0;
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

            $projectsPerPi1 = array();
            $projectsPerPi2 = array();
            $projectsPerPi3 = array();
            $totalProjects = 0;
            $projectsCount1 = 0;
            $projectsCount2 = 0;
            $projectsCount3 = 0;

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            //echo "Projects count=".count($projects)."<br>";

            foreach($projects as $project) {
                //$pis = $project->getPrincipalInvestigators();
                $pis = $project->getAllPrincipalInvestigators();
                $count1 = false;
                $count2 = false;
                $count3 = false;

                foreach ($pis as $pi) {
                    //1. Principle Investigators by Affiliation
                    if( $this->isUserBelongsToInstitution($pi,$department) ) {
                        //WCM Pathology Faculty - WCM Department of Pathology and Laboratory Medicine in any Title’s department field
                        //$piWcmPathologyCounter++;
                        $count1 = true;
                        $projectsPerPi1[] = $pi->getId();
                        //$totalProjects++;
                        //echo $totalProjects."(WCM Pathology Faculty): PI=$pi; Project ID=".$project->getId()."<br>";
                    }
                    elseif ( $this->isUserBelongsToInstitution($pi,$institution) ) {
                        //WCM Other Departmental Faculty - WCM institution
                        //Non-WCM Pathology faculty PIs
                        //$piWcmCounter++;
                        $count2 = true;
                        $projectsPerPi2[] = $pi->getId();
                        //$totalProjects++;
                        //echo $totalProjects."(Non-WCM Pathology faculty): PI=$pi; Project ID=".$project->getId()."<br>";
                    } else {
                        //Other Institutions
                        //$piOtherCounter++;
                        $count3 = true;
                        $projectsPerPi3[] = $pi->getId();
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

            }//foreach project
            //exit('111');

            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';

            $links = array();

            //////////// WCM Pathology Faculty ////////////
            $projectsPerPi1 = array_unique($projectsPerPi1);
            $piWcmPathologyCounter = count($projectsPerPi1);

            $linkFilterArr = array(
                'filter[state][0]' => 'final_approved',
                'filter[state][1]' => 'closed',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                //'filter[]' => $projectSpecialtyObjects
            );
            $userIndex = 0;
            foreach($projectsPerPi1 as $thisPi) {
                $linkFilterArr['filter[principalInvestigators]['.$userIndex.']'] = $thisPi;
                $userIndex++;
            }
            $link = $this->container->get('router')->generate(
                'translationalresearch_project_index',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $links[] = $link;
            //////////// EOF WCM Pathology Faculty ////////////

            //////////// Non-WCM Pathology faculty PIs ////////////
            $projectsPerPi2 = array_unique($projectsPerPi2);
            $piWcmCounter = count($projectsPerPi2);

            $linkFilterArr = array(
                'filter[state][0]' => 'final_approved',
                'filter[state][1]' => 'closed',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                //'filter[]' => $projectSpecialtyObjects
            );
            $userIndex = 0;
            foreach($projectsPerPi2 as $thisPi) {
                $linkFilterArr['filter[principalInvestigators]['.$userIndex.']'] = $thisPi;
                $userIndex++;
            }
            $link = $this->container->get('router')->generate(
                'translationalresearch_project_index',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $links[] = $link;
            //////////// EOF Non-WCM Pathology faculty PIs ////////////

            //////////// Other Institutions ////////////
            $projectsPerPi3 = array_unique($projectsPerPi3);
            $piOtherCounter = count($projectsPerPi3);

            $linkFilterArr = array(
                'filter[state][0]' => 'final_approved',
                'filter[state][1]' => 'closed',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                //'filter[]' => $projectSpecialtyObjects
            );
            $userIndex = 0;
            foreach($projectsPerPi3 as $thisPi) {
                $linkFilterArr['filter[principalInvestigators]['.$userIndex.']'] = $thisPi;
                $userIndex++;
            }
            $link = $this->container->get('router')->generate(
                'translationalresearch_project_index',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $links[] = $link;
            //////////// EOF Other Institutions ///////////////

            $titleTotal = $piWcmPathologyCounter + $piWcmCounter + $piOtherCounter;
            $chartName = $this->getTitleWithTotal($chartName,$titleTotal,null,"PIs total");

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
                "Other Institutions' faculty PIs with ".$projectsCount3." projects: ".$piOtherCounter
            );

            $values = array($piWcmPathologyCounter,$piWcmCounter,$piOtherCounter);
            //$values = array($piWcmPathologyCounter,$piWcmCounter);

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $chartDataArray["textinfo"] = "value+percent";
            //$chartDataArray["textinfo"] = "value";
            $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
            $chartDataArray['direction'] = 'clockwise';
            $chartDataArray["hoverinfo"] = "percent+label";

            //links
            $chartDataArray["links"] = $links;

            $dataArray[] = $chartDataArray;

            $chartsArray = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
        }

        //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
        //project's list might show the different matching projects, because in the filter principalInvestigators
        // are filtered by $dql->andWhere("principalInvestigators.id IN (:principalInvestigators) OR principalIrbInvestigator.id IN (:principalInvestigators)");
        if( $chartType == "projects-per-pi" ) {

            $piProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            //echo "projects=".count($projects)."<br>";

            foreach($projects as $project) {
                //$pis = $project->getPrincipalInvestigators();
                $pis = $project->getAllPrincipalInvestigators();
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
            //exit('111');

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piProjectCountTopArr = $this->getTopMultiArray($piProjectCountArr,$showOther); // getTopMultiArray(
            $filterArr['funded'] = null;

            //135 project requests (129 unique) total
            $totalSegmentCount = $this->getTotalSegmentCount($piProjectCountTopArr);

            //$chartName = $chartName . " - " . $totalCount . " total";
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            //Projects per PI
            //                                           $dataArr,              $title,                                $type='pie', $layoutArray=null, $valuePrefixLabel=null
            $chartsArray = $this->getChartByMultiArray( $piProjectCountTopArr, $filterArr, $chartName,"pie",null," : ","percent+label");
        }
        ///////////////// EOF 2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED) /////////////////

        // 3. Total number of Funded Projects per PI (Top 10)
        if( $chartType == "funded-projects-per-pi" ) {
            $piFundedProjectCountArr = array();

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);

            foreach($projects as $project) {
                $fundingNumber = $project->getFunded();

                if( $fundingNumber ) {

                    //$pis = $project->getPrincipalInvestigators();
                    $pis = $project->getAllPrincipalInvestigators();
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

                        //$titleCount = $titleCount + $count;

                    }//foreach $pis

                    $titleCount++;
                }//if

            }//foreach $projects

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piFundedProjectCountTopArr = $this->getTopMultiArray($piFundedProjectCountArr,$showOther);

            $totalSegmentCount = $this->getTotalSegmentCount($piFundedProjectCountTopArr);
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            $filterArr['funded'] = true;
            $chartsArray = $this->getChartByMultiArray( $piFundedProjectCountTopArr, $filterArr, $chartName,"pie",null," : ","percent+label");

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

                    //$pis = $project->getPrincipalInvestigators();
                    $pis = $project->getAllPrincipalInvestigators();
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

                        //$titleCount = $titleCount + $count;
                    }//foreach $pis

                    $titleCount++;
                }
            }//foreach $projects

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piUnFundedProjectCountTopArr = $this->getTopMultiArray($piUnFundedProjectCountArr,$showOther);

            $totalSegmentCount = $this->getTotalSegmentCount($piUnFundedProjectCountTopArr);
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            $filterArr['funded'] = false;
            $chartsArray = $this->getChartByMultiArray( $piUnFundedProjectCountTopArr, $filterArr, $chartName,"pie",null," : ","percent+label");
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

                    //$titleCount = $titleCount + $count;
                }

                $titleCount++;
            }

//            $showOther = $this->getOtherStr($showLimited,"Pathologist Involved");
//            $piProjectCountMultiTopArr = $this->getTopMultiArray($pathologistProjectCountMultiArr,$showOther); // getTopMultiArray(
//            $filterArr['funded'] = null;
//            $chartsArray = $this->getChartByMultiArray( $piProjectCountMultiTopArr, $filterArr, "2a. Total number of projects per Pathologist Involved (Top 10)","pie",null," : ");

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $pathologistProjectCountTopArr = $this->getTopArray($pathologistProjectCountArr,$showOther);

            $totalSegmentCount = $this->getTotalSegmentCount($pathologistProjectCountTopArr);
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            $chartsArray = $this->getChart($pathologistProjectCountTopArr,$chartName,'pie',$layoutArray," : ",null,null,"percent+label");

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

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $pathologistFundedProjectCountTopArr = $this->getTopArray($pathologistFundedProjectCountArr,$showOther);

            $totalSegmentCount = $this->getTotalSegmentCount($pathologistFundedProjectCountTopArr);
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            $filterArr['funded'] = true;
            $chartsArray = $this->getChart($pathologistFundedProjectCountTopArr, $chartName,"pie",$layoutArray," : ",null,null,"percent+label");
        }
        ///////////////// EOF 3a. Total number of Funded Projects per Pathologist Involved (Top 10) /////////////////
        // 7. Total number of Non-Funded Projects per Pathologist Involved (Top 10)
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

                        $titleCount = $titleCount + $count;
                    }//foreach $pathologists

                    $titleCount++;
                }
            }//foreach $projects

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $pathologistNonFundedProjectCountTopArr = $this->getTopArray($pathologistNonFundedProjectCountArr,$showOther);

            $totalSegmentCount = $this->getTotalSegmentCount($pathologistNonFundedProjectCountTopArr);
            $chartName = $this->getTitleWithTotal($chartName,$totalSegmentCount,null,"project requests ($titleCount unique) total");

            //$filterArr['funded'] = true;
            $chartsArray = $this->getChart($pathologistNonFundedProjectCountTopArr, $chartName,"pie",$layoutArray," : ",null,null,"percent+label");
        }
        ///////////////// EOF 4a. Total number of Non-Funded Projects per Pathologist Involved (Top 10) /////////////////


        //Work request statistics
        //8. Total Number of Work Requests by Funding Source
        if( $chartType == "requests-by-funding-source" ) {

            $fundedRequestCount = 0;
            $notFundedRequestCount = 0;

            $fundedProjectArr = array();
            $unfundedProjectArr = array();

            $testArr = array();
            $testing = false;

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                $project = $transRequest->getProject();
                $projectId = $project->getId();
                $fundedAccountNumber = $transRequest->getFundedAccountNumber();
                $fundedAccountNumber = trim($fundedAccountNumber);

                if($testing) {
                    //echo $transRequest->getOid().": fundedAccountNumber=[$fundedAccountNumber] <br>";
                    $testArr[$transRequest->getOid()]++;
                    //if( $fundedAccountNumber && strval($fundedAccountNumber) !== strval(intval($fundedAccountNumber)) ) {
                    if ($fundedAccountNumber && filter_var($fundedAccountNumber, FILTER_VALIDATE_INT) === false) {
                        echo $transRequest->getOid() . ": NOT INTEGER: [$fundedAccountNumber] <br>";
                    }
                }

                if( $fundedAccountNumber ) {
                    $fundedRequestCount++;
                    $fundedProjectArr[$projectId] = 1;
                } else {
                    $notFundedRequestCount++;
                    $unfundedProjectArr[$projectId] = 1;
                }
            }//foreach

            if($testing) {
                foreach ($testArr as $reqId => $reqCount) {
                    //echo $reqId." count=".$reqCount."<br>";
                    if ($reqCount != 1) {
                        echo $reqId . " !!!count=" . $reqCount . "<br>";
                    }
                }
                //print_r($testArr);
                exit("fundedRequestCount=$fundedRequestCount; notFundedRequestCount=$notFundedRequestCount");
            }

            $chartName = $this->getTitleWithTotal($chartName,$fundedRequestCount+$notFundedRequestCount,null,"work requests total");

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

            $links = array();
            //////////// Funded ////////////
            $linkFilterArr = array(
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
                'filter[endDate]' => $endDateStr,
                'filter[fundingType]' => 'Funded'
            );

            $link = $this->container->get('router')->generate(
                'translationalresearch_request_index_filter',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $links[] = $link;
            //////////// EOF Funded ////////////
            //////////// Non-Funded ////////////
            $linkFilterArr = array(
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
                'filter[endDate]' => $endDateStr,
                'filter[fundingType]' => 'Non-Funded'
            );

            $link = $this->container->get('router')->generate(
                'translationalresearch_request_index_filter',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $links[] = $link;
            //////////// EOF Non-Funded ////////////

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $chartDataArray["textinfo"] = "value+percent";
            $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
            $chartDataArray['direction'] = 'clockwise';
            $chartDataArray["hoverinfo"] = "percent+label";
            $chartDataArray["links"] = $links;

            $dataArray[] = $chartDataArray;

            $chartsArray = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
        }

        //9. Projects with Most Work Requests (Top 10)
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

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"work requests total");
            $showOther = $this->getOtherStr($showLimited,"projects");
            $requestPerProjectTopArr = $this->getTopMultiArray($requestPerProjectArr,$showOther);
            $filterArr['funded'] = null;
            $chartsArray = $this->getChartByMultiArray($requestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ","percent+label");
        }

        //10. Funded Projects with Most Work Requests (Top 10)
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

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"work requests total");
            $showOther = $this->getOtherStr($showLimited,"projects");
            $fundedRequestPerProjectTopArr = $this->getTopMultiArray($fundedRequestPerProjectArr,$showOther);
            $filterArr['funded'] = true;
            $chartsArray = $this->getChartByMultiArray( $fundedRequestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ","percent+label");
        }

        //11. Non-Funded Projects with Most Work Requests (Top 10)
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

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"work requests total");
            $showOther = $this->getOtherStr($showLimited,"projects");
            $unFundedRequestPerProjectTopArr = $this->getTopMultiArray($unFundedRequestPerProjectArr,$showOther);
            $filterArr['funded'] = false;
            $chartsArray = $this->getChartByMultiArray( $unFundedRequestPerProjectTopArr, $filterArr, $chartName,"pie",$layoutArray," : ","percent+label");
        }

        //Work request statistics: Products/Services 
        //12. Service Productivity by Products/Services (Top 35)
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

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"items total");
            $showOther = $this->getOtherStr($showLimited,"items");
            //                                              $piProjectCountArr, $showOthers=false, $descriptionArr=array(), $maxLen=50, $limit=10
            $quantityCountByCategoryTopArr = $this->getTopArray(
                $quantityCountByCategoryArr,    //$dataCountArr
                $showOther,                     //$showOthers
                array(),                        //$descriptionArr=array()
                50,                             //$maxLen=50
                35                              //$limit
            );
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );
            $chartsArray = $this->getChart($quantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ",null,null,"percent+label");
        }

        //13. Service Productivity for Funded Projects (Top 25)
        if( $chartType == "service-productivity-by-service-per-funded-projects" ) {
            $fundedQuantityCountByCategoryArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                foreach($transRequest->getProducts() as $product) {
                    $category = $product->getCategory();
                    if( $category ) {
                        $categoryIndex = $category->getProductIdAndName();
                        $productQuantity = $product->getQuantity();
                        //10. Service Productivity for Funded Projects (Top 25)
                        if( $transRequest->getFundedAccountNumber() ) {
                            //10. Service Productivity for Funded Projects (Top 25)
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

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"items total");
            $showOther = $this->getOtherStr($showLimited,"items");
            $fundedQuantityCountByCategoryTopArr = $this->getTopArray(
                $fundedQuantityCountByCategoryArr,
                $showOther,
                array(),                        //$descriptionArr=array()
                50,                             //$maxLen=50
                25                              //$limit
                );
            $chartsArray = $this->getChart($fundedQuantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ",null,null,"percent+label");
        }

        //14. Service Productivity for Non-Funded Projects (Top 10)
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
                            //11. Service Productivity for Non-Funded Projects (Top 10)
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

            $chartName = $this->getTitleWithTotal($chartName,$titleCount,null,"items total");
            $showOther = $this->getOtherStr($showLimited,"items");
            $unFundedQuantityCountByCategoryTopArr = $this->getTopArray(
                $unFundedQuantityCountByCategoryArr,
                $showOther,
                array(),                        //$descriptionArr=array()
                50,                             //$maxLen=50
                25                              //$limit
            );
            $chartsArray = $this->getChart($unFundedQuantityCountByCategoryTopArr, $chartName,'pie',$layoutArray," : ",null,null,"percent+label");
        }

        //"15. TRP Service Productivity by Products/Services" => "service-productivity-by-service-compare-funded-vs-nonfunded-projects"
        if( $chartType == "service-productivity-by-service-compare-funded-vs-nonfunded-projects" ) {
            $fundedQuantityCountByCategoryArr = array();
            $unFundedQuantityCountByCategoryArr = array();
            $stackDataSumArray = array();

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
                            //11. Service Productivity for Non-Funded Projects (Top 10)
                            if (isset($unFundedQuantityCountByCategoryArr[$categoryIndex])) {
                                $count = $unFundedQuantityCountByCategoryArr[$categoryIndex] + $productQuantity;
                            } else {
                                $count = $productQuantity;
                            }
                            $unFundedQuantityCountByCategoryArr[$categoryIndex] = $count;
                        }
                        $titleCount = $titleCount + $productQuantity;

                        if( isset($stackDataSumArray[$categoryIndex]) ) {
                            $sum = $stackDataSumArray[$categoryIndex] + $productQuantity;
                        } else {
                            $sum = $productQuantity;
                        }
                        $stackDataSumArray[$categoryIndex] = $sum;
                    }
                }
            }//foreach $requests

            //sort by value in key=>value
            arsort($stackDataSumArray);
            $fundedSortedArr = array();
            $unfundedSortedArr = array();
            foreach($stackDataSumArray as $categoryIndex=>$count) {
                //echo $categoryIndex."=".$count."<br>";
                $fundedSortedArr[$categoryIndex] = $fundedQuantityCountByCategoryArr[$categoryIndex];
                $unfundedSortedArr[$categoryIndex] = $unFundedQuantityCountByCategoryArr[$categoryIndex];
            }

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            //$showOther = $this->getOtherStr($showLimited,"projects");
            //$fundedQuantityCountByCategoryTopArr = $this->getTopArray($fundedQuantityCountByCategoryArr,$showOther);
            //$unFundedQuantityCountByCategoryTopArr = $this->getTopArray($unFundedQuantityCountByCategoryArr,$showOther);

            $combinedTrpData = array();
            $combinedTrpData['Funded'] = $fundedSortedArr; //$fundedQuantityCountByCategoryArr;
            $combinedTrpData['Not-Funded'] = $unfundedSortedArr; //$unFundedQuantityCountByCategoryArr;
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
            $chartDataArray["hoverinfo"] = "percent+label";
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
                //$pis = $project->getPrincipalInvestigators();
                $pis = $project->getAllPrincipalInvestigators();
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
            $chartsArray = $this->getChart($fundedTotalFeesByRequestTopArr, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //18. Total Fees per Non-Funded Project (Top 10)
        if( $chartType == "fees-by-requests-per-nonfunded-projects" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $unFundedTotalFeesByRequestArr = array();

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                $project = $transRequest->getProject();
                $projectIndex = $project->getOid(false);
                //$pis = $project->getPrincipalInvestigators();
                $pis = $project->getAllPrincipalInvestigators();
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
            $chartsArray = $this->getChart($unFundedTotalFeesByRequestTopArr, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
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
            $chartsArray = $this->getChart($totalFeesByInvestigatorTopArr, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
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
            $chartsArray = $this->getChart($fundedTotalFeesByInvestigatorTopArr, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
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
            $chartsArray = $this->getChart($unFundedTotalFeesByInvestigatorTopArr, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //"22. Paid Invoices by Month" =>                              "fees-by-invoices-paid-per-month",
        if( $chartType == "fees-by-invoices-paid-per-month" ) {

            $paidArr = array();
            $descriptionArr = array();

            $invoiceStates = array("Paid in Full","Paid Partially");
            $compareType = "date when status changed to paid in full";

            $startDate->modify( 'first day of last month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                $thisEndDate->modify( 'first day of next month' );
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y").": ";

                $invoices = $this->getInvoicesByFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$invoiceStates,false,true,$compareType);
                //get invoices by paidDate

                $startDate->modify( 'first day of next month' );

                foreach( $invoices as $invoice ) {

                    $paidThisInvoiceFee = intval($invoice->getPaid());

                    if( isset($paidArr[$startDateLabel]) ) {
                        $paidThisInvoiceFee = $paidArr[$startDateLabel] + $paidThisInvoiceFee;
                    }

                    $paidArr[$startDateLabel] = $paidThisInvoiceFee;
                }

                $descriptionArr[$startDateLabel] = " (" . count($invoices) . " invoices)";

            } while( $startDate < $endDate );

            //$dataArr, $title, $type='pie', $layoutArray=null, $valuePrefixLabel=null, $valuePostfixLabel=null, $descriptionArr=array()
            $chartsArray = $this->getChart($paidArr,$chartName,'bar',$layoutArray,"$",null,$descriptionArr,"percent+label"); // getChart(
        }

        //23. Generated Invoices by Status for Funded Projects
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
            $chartDataArray["hoverinfo"] = "percent+label";
            $dataArray[] = $chartDataArray;

            $chartsArray = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
            /////////////////////////////
        }

        //"24. Generated Invoices by Status for Non-Funded Projects (Top 10)" =>  "fees-by-invoices-per-nonfunded-projects"
        if( $chartType == "fees-by-invoices-per-nonfunded-projects" ) {
            $invoicesByProjectArr = array();
            $invoicesFeesByProjectArr = array();
            $totalInvoices = 0;

            $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects);
            foreach( $invoices as $invoice ) {

                $transRequest = $invoice->getTransresRequest();
                $project = $transRequest->getProject();
                $projectIndex = $project->getOid(false);
                //$pis = $project->getPrincipalInvestigators();
                $pis = $project->getAllPrincipalInvestigators();
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
            $invoicesFeesByProjectTopArr = $this->getTopArray($invoicesFeesByProjectArr,$showOther);
            //merge two to attach fees to label
            $invoicesByProjectTopArr = $this->attachSecondValueToFirstLabel($invoicesByProjectTopArr,$invoicesFeesByProjectTopArr," : $");
            $chartsArray = $this->getChart($invoicesByProjectTopArr,$chartName." (".$totalInvoices." invoices)",'pie',$layoutArray,null,null,null,"percent+label");

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
                array(
                    'descrPrefix'   => "paid $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "limegreen",
                    'descrType'     => "money",
                    'descrValueArr' => $invoicePaidFeeArr
                ),
                array(
                    'descrPrefix'   => "due $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "red",
                    'descrType'     => "money",
                    'descrValueArr' => $invoiceDueFeeArr
                ),
            );

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $invoicesFeesByPiArrTop = $this->getTopArray($invoicesFeesByPiArr,$showOther,$descriptionArr);
            $chartsArray = $this->getChart($invoicesFeesByPiArrTop,$chartName,'pie',$layoutArray,null,null,null,"percent+label");
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
                array(
                    'descrPrefix'   => "paid $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "limegreen",
                    'descrType'     => "money",
                    'descrValueArr' => $invoicePaidFeeArr
                ),
                array(
                    'descrPrefix'   => "due $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "red",
                    'descrType'     => "money",
                    'descrValueArr' => $invoiceDueFeeArr
                ),
            );

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther,$descriptionArr);
            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
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
                array(
                    'descrPrefix'   => "paid $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "limegreen",
                    'descrType'     => "money",
                    'descrValueArr' => $invoicePaidFeeArr
                ),
                array(
                    'descrPrefix'   => "due $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "red",
                    'descrType'     => "money",
                    'descrValueArr' => $invoiceDueFeeArr
                ),
            );

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther,$descriptionArr);
            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
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
                array(
                    'descrPrefix'   => "paid $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "limegreen",
                    'descrType'     => "money",
                    'descrValueArr' => $invoicePaidFeeArr
                ),
                array(
                    'descrPrefix'   => "due $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => " : $",
                    'valuePostfix'  => null,
                    'descrColor'    => "red",
                    'descrType'     => "money",
                    'descrValueArr' => $invoiceDueFeeArr
                ),
            );

            $showOther = $this->getOtherStr($showLimited,"pathologists involved");
            $invoicesFeesByPathologistArrTop = $this->getTopArray($invoicesFeesByPathologistArr,$showOther,$descriptionArr);
            $chartsArray = $this->getChart($invoicesFeesByPathologistArrTop, $chartName." (".$totalInvoices." invoices)",'pie',$layoutArray,null,null,null,"percent+label");

            if( is_array($chartsArray) && count($chartsArray) == 0 ) {
                $warningNoData = "There are no invoices associated with un-funded project requests that specify an involved pathologist during the selected time frame.".
                "<br>Chart '$chartName' has not been generated.";
            }
        }

        //"29. Total Fees per Involved Pathologist for Non-Funded Projects (Top 10)" =>  "fees-per-nonfunded-projects-per-pathologist-involved",
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

        //"30. Total Number of Projects per Type" => "projects-per-type"
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
            $chartsArray = $this->getChartByMultiArray( $projectTypeArrTop, $filterArr, $chartName,"pie",null," : ","percent+label");
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
            $chartsArray = $this->getChart($requestBusinessPurposeArrTop, $chartName,'pie',$layoutArray," : ",null,null,"percent+label");
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
                $transRequests = $this->getRequestsByAdvanceFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$productservice,$statuses);
                $startDate->modify( 'first day of next month' );

                //echo "<br>";
                //echo "transRequests=".count($transRequests)." (".$startDateLabel.")<br>";

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

//                    $dDiff = $submitted->diff($completed);
//                    //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                    $days = $dDiff->days;
//                    //echo "days=".$days."<br>";
//                    $days = intval($days);

                    $days = $this->calculateDays($submitted,$completed);

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
            $transRequests = $this->getRequestsByAdvanceFilter($startDate,$thisEndDate,$projectSpecialtyObjects,$productservice,$statuses);

            $daysTotal = 0;
            //$count = 0;

            foreach($transRequests as $transRequest) {

                //Number of days to go from Submitted to Completed
                $submitted = $transRequest->getCreateDate();

                $completed = $transRequest->getCompletedDate();
                if( !$completed ) {
                    continue;
                }

//                $dDiff = $submitted->diff($completed);
//                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                $days = $dDiff->days;
//                //echo "days=".$days."<br>";
//                $days = intval($days);
                $days = $this->calculateDays($submitted,$completed);

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
                    //$averageDays[$index] = array("value"=>$days,"link"=>$link);
                }
                //minimum 1 day
                if( !$days || $days == 0 ) {
                    $days = 1;
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
            $transRequests = $this->getRequestsByAdvanceFilter($startDate, $thisEndDate, $projectSpecialtyObjects, $productservice, $statuses);

            $requestCategoryWeightQuantityArr = array();

            foreach ($transRequests as $transRequest) {

                //Number of days to go from Submitted to Completed
                $submitted = $transRequest->getCreateDate();

                $completed = $transRequest->getCompletedDate();
                if (!$completed) {
                    continue;
                }

                //1) calculate days
//                $dDiff = $submitted->diff($completed);
//                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                $days = $dDiff->days;
//                //echo "days=".$days."<br>";
//                $days = intval($days);
                $days = $this->calculateDays($submitted,$completed);

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
                        //minimum 1 day
                        if( !$weightedQuantity || $weightedQuantity == 0 ) {
                            $weightedQuantity = 1;
                        }
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

        //"35. Turn-around Statistics: Average number of days for each project request approval phase" => "turn-around-statistics-days-project-state"
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

        //"36. Turn-around Statistics: Number of days for each project request approval phase" => "turn-around-statistics-days-per-project-state"
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
                $startDate->modify( 'first day of next month' );

                //echo "<br>";
                //echo "invoices=".count($invoices)." (".$startDateLabel.")<br>";

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

//                    $dDiff = $issued->diff($paid);
//                    //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                    $days = $dDiff->days;
//                    //echo "days=".$days."<br>";
//                    $days = intval($days);
                    $days = $this->calculateDays($issued,$paid);

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

//                $dDiff = $issued->diff($paid);
//                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                $days = $dDiff->days;
//                //echo "days=".$days."<br>";
//                $days = intval($days);
                $days = $this->calculateDays($issued,$paid);

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

        //"39. Turn-around Statistics: Top 10 PIs with most delayed unpaid invoices" => "turn-around-statistics-pis-with-delayed-unpaid-invoices",
        if( $chartType == "turn-around-statistics-pis-with-delayed-unpaid-invoices" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');

            $pisUnpaidInvoicesArr = array();
            $invoiceDueArr = array();

            //get unpaid and delayd invoices
            $invoiceStates = array("Unpaid/Issued");
            $invoices = $this->getInvoicesByFilter($startDate,$endDate,$projectSpecialtyObjects,$invoiceStates,true);
            //$invoices = $transresRequestUtil->getOverdueInvoices();

            foreach($invoices as $invoice) {
                $pi = $invoice->getPrincipalInvestigator();
                if( $pi ) {
                    $piIndex = $pi->getUsernameOptimal();
                    if( isset($pisUnpaidInvoicesArr[$piIndex]) ) {
                        $count = $pisUnpaidInvoicesArr[$piIndex]['value'] + 1;
                    } else {
                        $count = 1;
                    }
                    //$pisUnpaidInvoicesArr[$piIndex] = $count;
                    $todayDate = new \DateTime();
                    $linkFilterArr = array(
                        'filter[status][0]' => "Unpaid/Issued",
                        'filter[startCreateDate]' => $startDateStr,
                        'filter[endCreateDate]' => $endDateStr,
                        'filter[endDate]' => $todayDate->format('m/d/Y'),
                        'filter[version]' => "Latest",
                        'filter[principalInvestigator]' => $pi->getId()
                    );
                    $link = $this->container->get('router')->generate(
                        'translationalresearch_invoice_index_filter',
                        $linkFilterArr,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $pisUnpaidInvoicesArr[$piIndex] = array('value'=>$count,'link'=>$link);

                    $due = intval($invoice->getDue());
                    if( isset($invoiceDueArr[$piIndex]) ) {
                        $due = $invoiceDueArr[$piIndex] + $due;
                    }
                    $invoiceDueArr[$piIndex] = $due;

                    $titleCount++;
                }
            }//foreach

            //$titleCount = $titleCount . " (invoices ".count($invoices).")";

            $descriptionArr = array(
                array(
                    'descrPrefix'   => "due $",
                    'descrPostfix'  => null,
                    'valuePrefix'   => ": (",
                    'valuePostfix'  => " invoices)",
                    'descrColor'    => "red",
                    'descrType'     => "money",
                    'descrValueArr' => $invoiceDueArr
                ),
            );

            $chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $showOther = $this->getOtherStr($showLimited,"PIs");
            $pisUnpaidInvoicesArrTop = $this->getTopArray($pisUnpaidInvoicesArr,$showOther,$descriptionArr);
            $chartsArray = $this->getChart($pisUnpaidInvoicesArrTop, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
        }

        //"39. Turn-around Statistics: Top 10 PIs with highest total unpaid, overdue invoices" => "turn-around-statistics-pis-with-highest-total-unpaid-invoices",
        if( $chartType == "turn-around-statistics-pis-with-highest-total-unpaid-invoices" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');

            $pisUnpaidInvoicesTotalArr = array();
            $totalUnpaid = 0;

            //$invoices = $transresRequestUtil->getOverdueInvoices();
            $invoiceStates = array("Unpaid/Issued");
            $invoices = $this->getInvoicesByFilter($startDate,$endDate,$projectSpecialtyObjects,$invoiceStates,true);

            foreach($invoices as $invoice) {
                $pi = $invoice->getPrincipalInvestigator();
                if( $pi ) {
                    $piIndex = $pi->getUsernameOptimal(); // . " (".$invoice->getOid().")";
                    //$pisUnpaidInvoicesTotalArr[$piIndex] = $invoice->getTotal();
                    $total = $invoice->getTotal();
                    $totalUnpaid = $totalUnpaid + intval($total);

                    if (isset($pisUnpaidInvoicesTotalArr[$piIndex])) {
                        //$count = $pisUnpaidInvoicesArr[$piIndex] + 1;
                        $total = $pisUnpaidInvoicesTotalArr[$piIndex]['value'] + $total;
                    }
                    //$pisUnpaidInvoicesTotalArr[$piIndex] = $total;
                    $todayDate = new \DateTime();
                    $linkFilterArr = array(
                        'filter[status][0]' => "Unpaid/Issued",
                        'filter[startCreateDate]' => $startDateStr,
                        'filter[endCreateDate]' => $endDateStr,
                        'filter[endDate]' => $todayDate->format('m/d/Y'),
                        'filter[version]' => "Latest",
                        'filter[principalInvestigator]' => $pi->getId()
                    );
                    $link = $this->container->get('router')->generate(
                        'translationalresearch_invoice_index_filter',
                        $linkFilterArr,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $pisUnpaidInvoicesTotalArr[$piIndex] = array('value'=>$total,'link'=>$link);

                    $titleCount++;
                }
            }//foreach

            //$titleCount = $titleCount . " (invoices ".count($invoices).")";

            //$chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $chartName = $chartName . " (" . $titleCount . " invoices for total $" . $this->getNumberFormat($totalUnpaid) . ")";

            $showOther = $this->getOtherStr($showLimited,"Invoices");
            $pisUnpaidInvoicesTotalArrTop = $this->getTopArray($pisUnpaidInvoicesTotalArr,$showOther);
            $chartsArray = $this->getChart($pisUnpaidInvoicesTotalArrTop, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //"40. Turn-around Statistics: Top 10 PIs combining amounts and delay duration for unpaid, overdue invoices" => "turn-around-statistics-pis-combining-total-delayed-unpaid-invoices",
        if( $chartType == "turn-around-statistics-pis-combining-total-delayed-unpaid-invoices" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');

            $pisCombinedArr = array();
            $pisTotalUnpaidArr = array();
            $pisDaysArr = array();
            $pisCountArr = array();
            $pisIdArr = array();

            $totalUnpaid = 0;
            $totalCombined = 0;

            //$invoices = $transresRequestUtil->getOverdueInvoices();
            $invoiceStates = array("Unpaid/Issued");
            $invoices = $this->getInvoicesByFilter($startDate,$endDate,$projectSpecialtyObjects,$invoiceStates,true);

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

                    $pisIdArr[$piIndex] = $pi->getId();

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

                //$pisCombinedArrNew[$newIndex] = $combined;
                $todayDate = new \DateTime();
                $linkFilterArr = array(
                    'filter[status][0]' => "Unpaid/Issued",
                    'filter[startCreateDate]' => $startDateStr,
                    'filter[endCreateDate]' => $endDateStr,
                    'filter[endDate]' => $todayDate->format('m/d/Y'),
                    'filter[version]' => "Latest",
                    'filter[principalInvestigator]' => $pisIdArr[$index]
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_invoice_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $pisCombinedArrNew[$newIndex] = array('value'=>$combined,'link'=>$link);
            }

            //$chartName = $this->getTitleWithTotal($chartName,$titleCount);
            $chartName = $chartName . " (" . $titleCount . " invoices for total combined index $" . $this->getNumberFormat($totalCombined) . ")";

            $layoutArray['width'] = $layoutArray['width'] * 1.3; //1400;

            $showOther = $this->getOtherStr($showLimited,"Invoices");
            //getTopArray($piProjectCountArr, $showOthers=false, $descriptionArr=array(), $maxLen=50)
            $pisCombinedArrTop = $this->getTopArray($pisCombinedArrNew,$showOther,array(),150);
            $chartsArray = $this->getChart($pisCombinedArrTop, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //"42. Number of PIs in AP/CP vs Hematopathology" => "compare-projectspecialty-pis",
        if( $chartType == "compare-projectspecialty-pis" ) {
            $transresUtil = $this->container->get('transres_util');
            $specialtyApcpObject = $transresUtil->getSpecialtyObject("ap-cp");
            $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");

            //$startDate,$endDate,$projectSpecialties,$states
            $apcpProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyApcpObject));
            $hemaProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyHemaObject));

            $apcpPisArr = array();
            $hemaPisArr = array();
            foreach($apcpProjects as $project) {
                foreach($project->getAllPrincipalInvestigators() as $pi) {
                    $apcpPisArr[] = $pi->getId();
                }
            }
            foreach($hemaProjects as $project) {
                foreach($project->getAllPrincipalInvestigators() as $pi) {
                    $hemaPisArr[] = $pi->getId();
                }
            }

            $apcpPisArr = array_unique($apcpPisArr);
            $hemaPisArr = array_unique($hemaPisArr);

            $pisDataArr = array();

            //array(value,link)
            $linkFilterArr = array(
                'filter[state][0]' => 'final_approved',
                'filter[state][1]' => 'closed',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                //'filter[]' => $projectSpecialtyObjects,
                'filter[searchProjectType]' => null,
                'filter[projectSpecialty][]' => $specialtyApcpObject->getId(),
                //'filter[principalInvestigators][]' => implode(",",$apcpPisArr)
            );
            $index = 0;
            foreach($apcpPisArr as $piId) {
                $filterIndex = "filter[principalInvestigators][".$index."]";
                //echo "filterIndex=".$filterIndex."<br>";
                $linkFilterArr[$filterIndex] = $piId;
                $index++;
            }
            $link = $this->container->get('router')->generate(
                'translationalresearch_project_index',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            //$pisDataArr['AP/CP PIs'] = count($apcpPisArr);
            $pisDataArr['AP/CP PIs'] = array('value'=>count($apcpPisArr),'link'=>$link);


            $linkFilterArr = array(
                'filter[state][0]' => 'final_approved',
                'filter[state][1]' => 'closed',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                //'filter[]' => $projectSpecialtyObjects,
                'filter[searchProjectType]' => null,
                'filter[projectSpecialty][]' => $specialtyHemaObject->getId()
            );
            $index = 0;
            foreach($hemaPisArr as $piId) {
                $filterIndex = "filter[principalInvestigators][".$index."]";
                $linkFilterArr[$filterIndex] = $piId;
                $index++;
            }
            $link = $this->container->get('router')->generate(
                'translationalresearch_project_index',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            //$pisDataArr['Hematopathology PIs'] = count($hemaPisArr);
            $pisDataArr['Hematopathology PIs'] = array('value'=>count($hemaPisArr),'link'=>$link);

            $chartsArray = $this->getChart($pisDataArr, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
        }

        //"42. Number of AP/CP vs Hematopathology Project Requests" => "compare-projectspecialty-projects",
        if( $chartType == "compare-projectspecialty-projects" ) {
            $transresUtil = $this->container->get('transres_util');
            $specialtyApcpObject = $transresUtil->getSpecialtyObject("ap-cp");
            $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");

            //$startDate,$endDate,$projectSpecialties,$states
            $apcpProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyApcpObject));
            $hemaProjects = $this->getProjectsByFilter($startDate,$endDate,array($specialtyHemaObject));

//            $apcpPisArr = array();
//            $hemaPisArr = array();
//            foreach($apcpProjects as $project) {
//                foreach($project->getPrincipalInvestigators() as $pi) {
//                    $apcpPisArr[] = $pi->getId();
//                }
//            }
//            foreach($hemaProjects as $project) {
//                foreach($project->getPrincipalInvestigators() as $pi) {
//                    $hemaPisArr[] = $pi->getId();
//                }
//            }

            $projectsDataArr = array();

            //array(value,link)
            $linkFilterArr = array(
                'filter[state][0]' => 'final_approved',
                'filter[state][1]' => 'closed',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                'filter[searchProjectType]' => null,
                'filter[projectSpecialty][]' => $specialtyApcpObject->getId(),
            );
            $link = $this->container->get('router')->generate(
                'translationalresearch_project_index',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            //$projectsDataArr['AP/CP Project Requests'] = count($apcpProjects);
            $projectsDataArr['AP/CP Project Requests'] = array('value'=>count($apcpProjects),'link'=>$link);

            //array(value,link)
            $linkFilterArr = array(
                'filter[state][0]' => 'final_approved',
                'filter[state][1]' => 'closed',
                'filter[startDate]' => $startDateStr,
                'filter[endDate]' => $endDateStr,
                'filter[searchProjectType]' => null,
                'filter[projectSpecialty][]' => $specialtyHemaObject->getId(),
            );
            $link = $this->container->get('router')->generate(
                'translationalresearch_project_index',
                $linkFilterArr,
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            //$projectsDataArr['Hematopathology Project Requests'] = count($hemaProjects);
            $projectsDataArr['Hematopathology Project Requests'] = array('value'=>count($hemaProjects),'link'=>$link);

            $chartsArray = $this->getChart($projectsDataArr, $chartName,'pie',$layoutArray,null,null,null,"percent+label");
        }

        //"43. Number of AP/CP vs Hematopathology Project Requests" => "compare-projectspecialty-projects-stack",
        if( $chartType == "compare-projectspecialty-projects-stack" ) {
            $transresUtil = $this->container->get('transres_util');
            $specialtyApcpObject = $transresUtil->getSpecialtyObject("ap-cp");
            $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");

            $apcpResultStatArr = array();
            $hemaResultStatArr = array();
            $datesArr = array();

            //get startDate and add 1 month until the date is less than endDate
            $startDate->modify( 'first day of last month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                $thisEndDate->modify( 'first day of next month' );
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
                //$startDate,$endDate,$projectSpecialties,$states,$addOneEndDay
                $apcpProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyApcpObject),null,false);
                $hemaProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyHemaObject),null,false);
                $startDate->modify( 'first day of next month' );

                $apcpResultStatArr[$startDateLabel] = count($apcpProjects);
                $hemaResultStatArr[$startDateLabel] = count($hemaProjects);
            } while( $startDate < $endDate );

            //AP/CP
            $apcpProjectsData = array();
            foreach($apcpResultStatArr as $date=>$value ) {
                //$apcpProjectsData[$date] = $value;
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[state][0]' => 'final_approved',
                    'filter[state][1]' => 'closed',
                    'filter[startDate]' => $dates['startDate'],
                    'filter[endDate]' => $dates['endDate'],
                    'filter[searchProjectType]' => null,
                    //'filter[projectSpecialty][]' => $specialtyHemaObject->getId(),
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_index',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $apcpProjectsData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Hema
            $hemaProjectsData = array();
            foreach($hemaResultStatArr as $date=>$value ) {
                //$hemaProjectsData[$date] = $value;
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[state][0]' => 'final_approved',
                    'filter[state][1]' => 'closed',
                    'filter[startDate]' => $dates['startDate'],
                    'filter[endDate]' => $dates['endDate'],
                    'filter[searchProjectType]' => null,
                    //'filter[projectSpecialty][]' => $specialtyHemaObject->getId(),
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_index',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $hemaProjectsData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Projects
            $combinedProjectsData = array();
            $combinedProjectsData['AP/CP'] = $apcpProjectsData;
            $combinedProjectsData['Hematopathology'] = $hemaProjectsData;

            $chartsArray = $this->getStackedChart($combinedProjectsData, $chartName, "stack");
        }

        //"44. Number of AP/CP vs Hematopathology Work Requests" => "compare-projectspecialty-requests",
        if( $chartType == "compare-projectspecialty-requests" ) {
            $transresUtil = $this->container->get('transres_util');
            $specialtyApcpObject = $transresUtil->getSpecialtyObject("ap-cp");
            $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");

            $apcpResultStatArr = array();
            $hemaResultStatArr = array();
            $datesArr = array();

            //get startDate and add 1 month until the date is less than endDate
            $startDate->modify( 'first day of last month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                $thisEndDate->modify( 'first day of next month' );
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
                //$startDate,$endDate,$projectSpecialties,$states,$addOneEndDay

                $apcpRequests = $this->getRequestsByFilter($startDate,$thisEndDate,array($specialtyApcpObject));
                $hemaRequests = $this->getRequestsByFilter($startDate,$thisEndDate,array($specialtyHemaObject));

                $startDate->modify( 'first day of next month' );

                $apcpResultStatArr[$startDateLabel] = count($apcpRequests);
                $hemaResultStatArr[$startDateLabel] = count($hemaRequests);

            } while( $startDate < $endDate );

            //AP/CP
            $apcpRequestsData = array();
            foreach($apcpResultStatArr as $date=>$value ) {
                $dates = $datesArr[$date];
                $linkFilterArr = array(
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
                    'filter[startDate]' => $dates['startDate'],
                    'filter[endDate]' => $dates['endDate'],
                    'filter[searchProjectType]' => null,
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_request_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $apcpRequestsData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Hema
            $hemaRequestsData = array();
            foreach($hemaResultStatArr as $date=>$value ) {
                $dates = $datesArr[$date];
                $linkFilterArr = array(
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
                    'filter[startDate]' => $dates['startDate'],
                    'filter[endDate]' => $dates['endDate'],
                    'filter[searchProjectType]' => null,
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_request_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $hemaRequestsData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Requests
            $combinedRequestsData = array();
            $combinedRequestsData['AP/CP'] = $apcpRequestsData;
            $combinedRequestsData['Hematopathology'] = $hemaRequestsData;
            $chartsArray = $this->getStackedChart($combinedRequestsData, $chartName, "stack");
        }

        //"45. Number of AP/CP vs Hematopathology Invoices" => "compare-projectspecialty-invoices",
        if( $chartType == "compare-projectspecialty-invoices" ) {
            $transresUtil = $this->container->get('transres_util');
            $specialtyApcpObject = $transresUtil->getSpecialtyObject("ap-cp");
            $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");

            $apcpResultStatArr = array();
            $hemaResultStatArr = array();
            $datesArr = array();

            //get startDate and add 1 month until the date is less than endDate
            $startDate->modify( 'first day of last month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                $thisEndDate->modify( 'first day of next month' );
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
                //$startDate,$endDate,$projectSpecialties,$states,$addOneEndDay

                //$apcpProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyApcpObject),null,false);
                //$hemaProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyHemaObject),null,false);
                $apcpInvoices = $this->getInvoicesByFilter($startDate,$thisEndDate, array($specialtyApcpObject));
                $hemaInvoices = $this->getInvoicesByFilter($startDate,$thisEndDate, array($specialtyHemaObject));

//                echo "<br>### $startDateLabel ###<br>";
//                foreach($apcpInvoices as $inv){
//                    echo "apcp inv id=".$inv->getOid()."<br>";
//                }
//                foreach($hemaInvoices as $inv){
//                    echo "hema inv id=".$inv->getOid()."<br>";
//                }

                $startDate->modify( 'first day of next month' );

                //$apcpResultStatArr = $this->getProjectRequestInvoiceChart($apcpProjects,$apcpResultStatArr,$startDateLabel);
                //$hemaResultStatArr = $this->getProjectRequestInvoiceChart($hemaProjects,$hemaResultStatArr,$startDateLabel);
                $apcpResultStatArr[$startDateLabel] = count($apcpInvoices);
                $hemaResultStatArr[$startDateLabel] = count($hemaInvoices);

            } while( $startDate < $endDate );

            //AP/CP
            $apcpInvoicesData = array();
            foreach($apcpResultStatArr as $date=>$value ) {
                //$apcpInvoicesData[$date] = $value;
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[status][0]' => "Unpaid/Issued",
                    'filter[status][1]' => "Paid in Full",
                    'filter[status][2]' => "Paid Partially",
                    'filter[status][3]' => 'Refunded Fully',
                    'filter[status][4]' => 'Refunded Partially',
                    'filter[startCreateDate]' => $dates['startDate'], //dueDate, therefore we can not filter invoices list
                    'filter[endCreateDate]' => $dates['endDate'],
                    'filter[version]' => "Latest"
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_invoice_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $apcpInvoicesData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Hema
            $hemaInvoicesData = array();
            foreach($hemaResultStatArr as $date=>$value ) {
                //$hemaInvoicesData[$date] = $value;
                $dates = $datesArr[$date];
                $linkFilterArr = array(
                    'filter[status][0]' => "Unpaid/Issued",
                    'filter[status][1]' => "Paid in Full",
                    'filter[status][2]' => "Paid Partially",
                    'filter[status][3]' => 'Refunded Fully',
                    'filter[status][4]' => 'Refunded Partially',
                    'filter[startCreateDate]' => $dates['startDate'],
                    'filter[endCreateDate]' => $dates['endDate'],
                    'filter[version]' => "Latest"
                );
                $link = $this->container->get('router')->generate(
                    'translationalresearch_invoice_index_filter',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $hemaInvoicesData[$date] = array('value'=>$value,'link'=>$link);
            }

            //Invoices
            $combinedInvoicesData = array();
            $combinedInvoicesData['AP/CP'] = $apcpInvoicesData;
            $combinedInvoicesData['Hematopathology'] = $hemaInvoicesData;
            $chartsArray = $this->getStackedChart($combinedInvoicesData, $chartName, "stack"); //" getStackedChart("
        }

        //"47. Total Fees per Project Request Type" => "projects-fees-per-type",
        if( $chartType == "projects-fees-per-type" ) {
            $transresUtil = $this->container->get('transres_util');
            $projectTypeArr = array();
            $totalFees = 0;

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($projects as $project) {
                $projectType = $project->getProjectType();
                if( $projectType ) {
                    $projectTypeId = $projectType->getId();
                    $projectTypeName = $projectType->getName();
                } else {
                    $projectTypeId = "No Type";
                    $projectTypeName = "No Type";;
                }

                $invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
                $totalFee = $invoicesInfos['total'];
                if( !$totalFee || $totalFee == 0 ) {
                    continue;
                }

                $totalFees = $totalFees + $totalFee;

                if( isset($projectTypeArr[$projectTypeId]) && isset($projectTypeArr[$projectTypeId]['value']) ) {
                    $totalFee = $projectTypeArr[$projectTypeId]['value'] + $totalFee;
                }
                $projectTypeArr[$projectTypeId]['value'] = $totalFee;
                $projectTypeArr[$projectTypeId]['label'] = $projectTypeName;
                $projectTypeArr[$projectTypeId]['objectid'] = $projectTypeId;
                $projectTypeArr[$projectTypeId]['pi'] = null;
                //$projectTypeArr[$projectTypeId]['show-path'] = null; //"project-type";

                //link
                $linkFilterArr = array(
                    'filter[state][0]' => 'final_approved',
                    'filter[state][1]' => 'closed',
                    'filter[startDate]' => $startDateStr,
                    'filter[endDate]' => $endDateStr,
                    'filter[searchProjectType]' => $projectTypeId
                    //'filter[searchProjectType]' => $project->getId()
                );
                $count = 0;
                foreach($projectSpecialtyObjects as $projectSpecialtyObject) {
                    $linkFilterArr["filter[searchProjectType][".$count."]"] = $projectSpecialtyObject->getId();
                    $count++;
                }
                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_index',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $projectTypeArr[$projectTypeId]['link'] = $link;
            }

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalFees),"$");
            $showOther = $this->getOtherStr($showLimited,"Project Types");
            $projectTypeArrTop = $this->getTopMultiArray($projectTypeArr,$showOther);

            $chartsArray = $this->getChartByMultiArray( $projectTypeArrTop, $filterArr, $chartName,"pie",null," : $","percent+label");
            //$chartsArray = $this->getChart($projectTypeArrTop, $chartName,'pie',$layoutArray," : $");
        }

        //"48. Total Fees per Funded Project Request Type (Top 10) (linked)" => "projects-funded-fees-per-type",
        if( $chartType == "projects-funded-fees-per-type" ) {
            $transresUtil = $this->container->get('transres_util');
            $projectTypeArr = array();
            $totalFees = 0;

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($projects as $project) {

                if( !$project->getFunded() ) {
                    continue;
                }

                $projectType = $project->getProjectType();
                if( $projectType ) {
                    $projectTypeId = $projectType->getId();
                    $projectTypeName = $projectType->getName();
                } else {
                    $projectTypeId = "No Type";
                    $projectTypeName = "No Type";;
                }

                $invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
                $totalFee = $invoicesInfos['total'];
                if( !$totalFee || $totalFee == 0 ) {
                    continue;
                }

                $totalFees = $totalFees + $totalFee;

                if( isset($projectTypeArr[$projectTypeId]) && isset($projectTypeArr[$projectTypeId]['value']) ) {
                    $totalFee = $projectTypeArr[$projectTypeId]['value'] + $totalFee;
                }
                $projectTypeArr[$projectTypeId]['value'] = $totalFee;
                $projectTypeArr[$projectTypeId]['label'] = $projectTypeName;
                $projectTypeArr[$projectTypeId]['objectid'] = $projectTypeId;
                $projectTypeArr[$projectTypeId]['pi'] = null;
                //$projectTypeArr[$projectTypeId]['show-path'] = "project-type";

                //link
                $linkFilterArr = array(
                    'filter[state][0]' => 'final_approved',
                    'filter[state][1]' => 'closed',
                    'filter[startDate]' => $startDateStr,
                    'filter[endDate]' => $endDateStr,
                    'filter[searchProjectType]' => $projectTypeId
                );
                $count = 0;
                foreach($projectSpecialtyObjects as $projectSpecialtyObject) {
                    $linkFilterArr["filter[searchProjectType][".$count."]"] = $projectSpecialtyObject->getId();
                    $count++;
                }
                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_index',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $projectTypeArr[$projectTypeId]['link'] = $link;
            }

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalFees),"$");
            $showOther = $this->getOtherStr($showLimited,"Project Types");
            $projectTypeArrTop = $this->getTopMultiArray($projectTypeArr,$showOther);

            $chartsArray = $this->getChartByMultiArray( $projectTypeArrTop, $filterArr, $chartName,"pie",null," : $","percent+label");
        }

        //"49. Total Fees per Non-Funded Project Request Type (Top 10) (linked)" => "projects-unfunded-fees-per-type",
        if( $chartType == "projects-unfunded-fees-per-type" ) {
            $transresUtil = $this->container->get('transres_util');
            $projectTypeArr = array();
            $totalFees = 0;

            $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($projects as $project) {

                if( $project->getFunded() ) {
                    continue;
                }

                $projectType = $project->getProjectType();
                if( $projectType ) {
                    $projectTypeId = $projectType->getId();
                    $projectTypeName = $projectType->getName();
                } else {
                    $projectTypeId = "No Type";
                    $projectTypeName = "No Type";;
                }

                $invoicesInfos = $transresUtil->getInvoicesInfosByProject($project);
                $totalFee = $invoicesInfos['total'];
                if( !$totalFee || $totalFee == 0 ) {
                    continue;
                }

                $totalFees = $totalFees + $totalFee;

                if( isset($projectTypeArr[$projectTypeId]) && isset($projectTypeArr[$projectTypeId]['value']) ) {
                    $totalFee = $projectTypeArr[$projectTypeId]['value'] + $totalFee;
                }
                $projectTypeArr[$projectTypeId]['value'] = $totalFee;
                $projectTypeArr[$projectTypeId]['label'] = $projectTypeName;
                $projectTypeArr[$projectTypeId]['objectid'] = $projectTypeId;
                $projectTypeArr[$projectTypeId]['pi'] = null;
                //$projectTypeArr[$projectTypeId]['show-path'] = "project-type";

                //link
                $linkFilterArr = array(
                    'filter[state][0]' => 'final_approved',
                    'filter[state][1]' => 'closed',
                    'filter[startDate]' => $startDateStr,
                    'filter[endDate]' => $endDateStr,
                    'filter[searchProjectType]' => $projectTypeId
                );
                $count = 0;
                foreach($projectSpecialtyObjects as $projectSpecialtyObject) {
                    $linkFilterArr["filter[searchProjectType][".$count."]"] = $projectSpecialtyObject->getId();
                    $count++;
                }
                $link = $this->container->get('router')->generate(
                    'translationalresearch_project_index',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $projectTypeArr[$projectTypeId]['link'] = $link;
            }

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalFees),"$");
            $showOther = $this->getOtherStr($showLimited,"Project Types");
            $projectTypeArrTop = $this->getTopMultiArray($projectTypeArr,$showOther);

            $chartsArray = $this->getChartByMultiArray( $projectTypeArrTop, $filterArr, $chartName,"pie",null," : $","percent+label");
        }

        //"50. Total Fees per Work Requests Business Purpose" => "requests-fees-per-business-purpose",
        if( $chartType == "requests-fees-per-business-purpose" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $requestBusinessPurposeArr = array();
            $totalFees = 0;
            //$testing = true;
            $testing = false;

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $thisTransRequest) {

                $fee = $transresRequestUtil->getTransResRequestFeeHtml($thisTransRequest);
                if( !$fee || $fee == 0 ) {
                    continue;
                }

                $totalFees = $totalFees + $fee;

                if($testing) {
                    echo "<br>thisTransRequest=" . $thisTransRequest->getOid() . "; fee=" . $fee . "<br>";
                }

                $businessPurposes = $thisTransRequest->getBusinessPurposes();

                if( count($businessPurposes) == 0 ) {
                    //$totalFees = $totalFees + $fee;
                    if( isset($requestBusinessPurposeArr["No Business Purpose"]) ) {
                        $fee = $requestBusinessPurposeArr["No Business Purpose"] + $fee;
                    }
                    $requestBusinessPurposeArr["No Business Purpose"] = $fee;
                }

                foreach($businessPurposes as $businessPurpose) {
                    $businessPurposeName = $businessPurpose->getName();
                    $thisFee = $fee;
                    if( isset($requestBusinessPurposeArr[$businessPurposeName]) ) {
                        $thisFee = $requestBusinessPurposeArr[$businessPurposeName] + $thisFee;
                    }
                    $requestBusinessPurposeArr[$businessPurposeName] = $thisFee;
                    if($testing) {
                        echo "businessPurposeName=".$businessPurposeName."; fee=".$thisFee."<br>";
                    }
                }
            }

            $totalFees = $this->getNumberFormat($totalFees);
            $chartName = $this->getTitleWithTotal($chartName,$totalFees,"$");
            $showOther = $this->getOtherStr($showLimited,"Business Purposes");
            $requestBusinessPurposeArrTop = $this->getTopArray($requestBusinessPurposeArr,$showOther);
            $chartsArray = $this->getChart($requestBusinessPurposeArrTop, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");

            if($testing) {
                echo "<br>";
                print_r($requestBusinessPurposeArr);
                echo "<br>totalFees=".$totalFees."<br>";
                exit();
            }
        }

        //"51. Total Fees per Funded Work Requests Business Purpose (Top 10)" => "requests-funded-fees-per-business-purpose",
        if( $chartType == "requests-funded-fees-per-business-purpose" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $requestBusinessPurposeArr = array();
            $totalFees = 0;

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                if( !$transRequest->getProject()->getFunded() ) {
                    continue;
                }

                $fee = $transresRequestUtil->getTransResRequestFeeHtml($transRequest);
                if( !$fee || $fee == 0 ) {
                    continue;
                }

                $totalFees = $totalFees + $fee;

                $businessPurposes = $transRequest->getBusinessPurposes();

                if( count($businessPurposes) == 0 ) {
                    if( isset($requestBusinessPurposeArr["No Business Purpose"]) ) {
                        $fee = $requestBusinessPurposeArr["No Business Purpose"] + $fee;
                    }
                    $requestBusinessPurposeArr["No Business Purpose"] = $fee;
                }

                foreach($businessPurposes as $businessPurpose) {
                    $businessPurposeName = $businessPurpose->getName();

                    if( isset($requestBusinessPurposeArr[$businessPurposeName]) ) {
                        $fee = $requestBusinessPurposeArr[$businessPurposeName] + $fee;
                    }
                    $requestBusinessPurposeArr[$businessPurposeName] = $fee;
                }
            }

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalFees),"$");
            $showOther = $this->getOtherStr($showLimited,"Business Purposes");
            $requestBusinessPurposeArrTop = $this->getTopArray($requestBusinessPurposeArr,$showOther);
            $chartsArray = $this->getChart($requestBusinessPurposeArrTop, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //"52. Total Fees per Non-Funded Work Requests Business Purpose (Top 10)" => "requests-unfunded-fees-per-business-purpose",
        if( $chartType == "requests-unfunded-fees-per-business-purpose" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');
            $requestBusinessPurposeArr = array();
            $totalFees = 0;

            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {

                if( $transRequest->getProject()->getFunded() ) {
                    continue;
                }

                $fee = $transresRequestUtil->getTransResRequestFeeHtml($transRequest);
                if( !$fee || $fee == 0 ) {
                    continue;
                }

                $totalFees = $totalFees + $fee;

                $businessPurposes = $transRequest->getBusinessPurposes();

                if( count($businessPurposes) == 0 ) {
                    if( isset($requestBusinessPurposeArr["No Business Purpose"]) ) {
                        $fee = $requestBusinessPurposeArr["No Business Purpose"] + $fee;
                    }
                    $requestBusinessPurposeArr["No Business Purpose"] = $fee;
                }

                foreach($businessPurposes as $businessPurpose) {
                    $businessPurposeName = $businessPurpose->getName();

                    if( isset($requestBusinessPurposeArr[$businessPurposeName]) ) {
                        $fee = $requestBusinessPurposeArr[$businessPurposeName] + $fee;
                    }
                    $requestBusinessPurposeArr[$businessPurposeName] = $fee;
                }
            }

            $chartName = $this->getTitleWithTotal($chartName,$this->getNumberFormat($totalFees),"$");
            $showOther = $this->getOtherStr($showLimited,"Business Purposes");
            $requestBusinessPurposeArrTop = $this->getTopArray($requestBusinessPurposeArr,$showOther);
            $chartsArray = $this->getChart($requestBusinessPurposeArrTop, $chartName,'pie',$layoutArray," : $",null,null,"percent+label");
        }

        //"53. Turn-around Statistics: Number of days to complete each Work Request with person (based on 'Completed and Notified' requests)" => "turn-around-statistics-days-complete-per-request-with-product-by-user",
        if( $chartType == "turn-around-statistics-days-complete-per-request-with-user" ) {
            $averageDays = array();

            $statuses = array("completedNotified");
            $transRequests = $this->getRequestsByAdvanceFilter($startDate, $thisEndDate, $projectSpecialtyObjects, $productservice, $statuses);

            foreach ($transRequests as $transRequest) {

                //Number of days to go from Submitted to Completed
                $submitted = $transRequest->getCreateDate();

                $completed = $transRequest->getCompletedDate();
                if (!$completed) {
                    continue;
                }

//                //1) calculate days
//                $dDiff = $submitted->diff($completed);
//                //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
//                $days = $dDiff->days;
//                //echo "days=".$days."<br>";
//                $days = intval($days);
                $days = $this->calculateDays($submitted,$completed);

                if( !$days || $days == 0 ) {
                    $days = 1;
                }

                $index = $transRequest->getOid();
                $completedUser = $transRequest->getCompletedBy();
                if( !$completedUser ) {
                    $completedUser = $transRequest->getUpdateUser();
                }
                if( $completedUser ) {
                    $index = $index . ", " . $completedUser->getUsernameOptimal();
                }

                if( isset($averageDays[$index]) ) {
                    $days = $averageDays[$index] + $days;
                }
                $averageDays[$index] = $days;

            }//foreach

            $layoutArray = array(
                'height' => $this->height*1.5,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b' => 400)
            );

            //exit("Exit");

            $valuePrefixLabel = "(";
            $valuePostfixLabel = " days)";
            $chartsArray = $this->getChart($averageDays, $chartName,'bar',$layoutArray,$valuePrefixLabel,$valuePostfixLabel); // getChart(
        }

        //"54. Turn-around Statistics: Top 50 most delinquent invoices" => "turn-around-statistics-delayed-unpaid-invoices-by-days",
        if( $chartType == "turn-around-statistics-delayed-unpaid-invoices-by-days" ) {
            $transresRequestUtil = $this->container->get('transres_request_util');

            $invoiceDueDaysArr = array();
            //$invoiceDueArr = array();

            //get unpaid and delayd invoices
            //$invoices = $transresRequestUtil->getOverdueInvoices();
            $invoiceStates = array("Unpaid/Issued");
            $invoices = $this->getInvoicesByFilter($startDate,$endDate,$projectSpecialtyObjects,$invoiceStates,true);

            foreach($invoices as $invoice) {

                $nowDate = new \DateTime();
                $dueDate = $invoice->getDueDate();
                if( !$dueDate ) {
                    continue; //ignore invoices without duedate
                }

                $days = $this->calculateDays($dueDate,$nowDate);

                //APCP843-REQ16111-V1 to PIFirstName PILastName X days ago on MM/DD/YY for $XXX.XX - (123) 444-5555
                $index = $invoice->getOid();
                $pi = $invoice->getPrincipalInvestigator();
                if( $pi ) {
                    $index = $index . " to " . $pi->getUsernameOptimal();
                }
                $index = $index . " " . $days . " days ago";

//                $issuedDate = $invoice->getIssuedDate();
//                if( $issuedDate ) {
//                    $index = $index . " on " . $issuedDate->format("m/d/Y");
//                } else {
//                    $index = $index . " due on " . $dueDate->format("m/d/Y");
//                }
                $index = $index . " due on " . $dueDate->format("m/d/Y");

                $due = $invoice->getDue();
                if( $due ) {
                    $index = $index . " for $" . $this->getNumberFormat($due);
                }
                $phone = $pi->getSinglePhone();
                if( $phone ) {
                    $index = $index . " - " . $phone;
                }

                //$invoiceDueDaysArr[$index] = $days;
                $invoiceShowUrl = $transresRequestUtil->getInvoiceShowUrl($invoice,false,$invoice->getOid(),true);
                $invoiceDueDaysArr[$index] = array('value'=>$days,'link'=>$invoiceShowUrl);

                //$invoiceDueArr[$index] = $invoice->getDue();

            }//foreach

            //$titleCount = $titleCount . " (invoices ".count($invoices).")";

            $layoutArray = array(
                'height' => $this->height*1.5,
                'width' => $this->width,
                'title' => $chartName,
                'margin' => array('b' => 600)
            );

//            $descriptionArr = array(
//                array(
//                    'descrPrefix'   => "due $",
//                    'descrPostfix'  => null,
//                    'valuePrefix'   => ": (",
//                    'valuePostfix'  => " overdue days)",
//                    'descrColor'    => "red",
//                    'descrType'     => "money",
//                    'descrValueArr' => $invoiceDueArr
//                ),
//            );
            $descriptionArr = array();

            //$chartName = $this->getTitleWithTotal($chartName,count($invoices));
            //109 unpaid invoices in total
            $chartName = $chartName . " - " . count($invoices) . " unpaid invoices in total";

           //$showOther = $this->getOtherStr($showLimited,"Invoices");
            $invoiceDueDaysArrTop = $this->getTopArray($invoiceDueDaysArr,false,$descriptionArr,$maxLen=100, $limit=50);
            arsort($invoiceDueDaysArrTop);
            $chartsArray = $this->getChart($invoiceDueDaysArrTop, $chartName,'bar',$layoutArray);
        }

        //"55. Number of reminder emails sent per month (linked)" => "reminder-emails-per-month",
        if( $chartType == "reminder-emails-per-month" ) {
            $transresUtil = $this->container->get('transres_util');

            if( count($projectSpecialtyObjects) > 0 ) {
                $projectSpecialtyObject = $projectSpecialtyObjects[0];
            } else {
                $projectSpecialtyObject = null;
            }

            //////// Construct link ////////
            $ProjectReminderEventTypeId = null;
            $RequestReminderEventTypeId = null;
            $InvoiceReminderEventTypeId = null;
            $ProjectReminderEventType = $this->em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName("Project Reminder Email");
            if( $ProjectReminderEventType ) {
                $ProjectReminderEventTypeId = $ProjectReminderEventType->getId();
            }
            $RequestReminderEventType = $this->em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName("Work Request Reminder Email");
            if( $RequestReminderEventType ) {
                $RequestReminderEventTypeId = $RequestReminderEventType->getId();
            }
            $InvoiceReminderEventType = $this->em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName("Unpaid Invoice Reminder Email");
            if( $InvoiceReminderEventType ) {
                $InvoiceReminderEventTypeId = $InvoiceReminderEventType->getId();
            }
            
            $linkFilterArr = array(
                //'filter[startdate]' => $startDateStr,
                //'filter[enddate]' => $endDateStr,
                'filter[eventType][0]' => $ProjectReminderEventTypeId,
                'filter[eventType][1]' => $RequestReminderEventTypeId,
                'filter[eventType][2]' => $InvoiceReminderEventTypeId
            );

//            $link = $this->container->get('router')->generate(
//                'translationalresearch_logger',
//                $linkFilterArr,
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
            //////// EOF Construct link ////////

            $unpaidInvoicesArr = array();
            $delayedProjectsArr = array();
            $delayedRequestsArr = array();
            $delayedCompletedRequestsArr = array();
            $delayedCompletedNoInvoiceRequestsArr = array();
            $datesArr = array();

            $delayedProjectsCount = 0;
            $delayedRequestsCount = 0;
            $delayedCompletedRequestsCount = 0;
            $delayedCompletedNoInvoiceRequestsCount = 0;
            $unpaidInvoicesCount = 0;

            $pendingStates = array(
                'active',
                'pendingInvestigatorInput',
                'pendingHistology',
                'pendingImmunohistochemistry',
                'pendingMolecular',
                'pendingCaseRetrieval',
                'pendingTissueMicroArray',
                'pendingSlideScanning'
            );
            $completedStates = array(
                'completed'
            );
            $completedNoInvoiceStates = array(
                'completedNotified'
            );

            //get startDate and add 1 month until the date is less than endDate
            $startDate->modify( 'first day of last month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                $thisEndDate->modify( 'first day of next month' );
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                $unpaidInvoicesRemindersCount = $transresUtil->getUnpaidInvoiceRemindersCount($startDate,$thisEndDate,$projectSpecialtyObjects);
                $delayedProjectRemindersCount = $transresUtil->getDelayedProjectRemindersCount($startDate,$thisEndDate,$projectSpecialtyObjects);
                $delayedRequestRemindersCount = $transresUtil->getDelayedRequestRemindersCount($startDate,$thisEndDate,$projectSpecialtyObjects,$pendingStates);
                $delayedCompletedRequestRemindersCount = $transresUtil->getDelayedRequestRemindersCount($startDate,$thisEndDate,$projectSpecialtyObjects,$completedStates);
                $delayedCompletedNoInvoiceRequestRemindersCount = $transresUtil->getDelayedRequestRemindersCount($startDate,$thisEndDate,$projectSpecialtyObjects,$completedNoInvoiceStates);

                $startDate->modify( 'first day of next month' );

                $unpaidInvoicesArr[$startDateLabel] = $unpaidInvoicesRemindersCount;
                $unpaidInvoicesCount += $unpaidInvoicesRemindersCount;

                $delayedProjectsArr[$startDateLabel] = $delayedProjectRemindersCount;
                $delayedProjectsCount += $delayedProjectRemindersCount;
                
                $delayedRequestsArr[$startDateLabel] = $delayedRequestRemindersCount;
                $delayedRequestsCount += $delayedRequestRemindersCount;

                $delayedCompletedRequestsArr[$startDateLabel] = $delayedCompletedRequestRemindersCount;
                $delayedCompletedRequestsCount += $delayedCompletedRequestRemindersCount;

                $delayedCompletedNoInvoiceRequestsArr[$startDateLabel] = $delayedCompletedNoInvoiceRequestRemindersCount;
                $delayedCompletedNoInvoiceRequestsCount += $delayedCompletedNoInvoiceRequestRemindersCount;

            } while( $startDate < $endDate );


            //Reminders
            $combinedData = array();
            //$combinedData['Unpaid Invoices'] = $unpaidInvoicesArr;
            $delayedInvoicesData = array();
            foreach($unpaidInvoicesArr as $date=>$value ) {

                $dates = $datesArr[$date];
                $linkFilterArr['filter[startdate]'] = $dates['startDate'];
                $linkFilterArr['filter[enddate]'] = $dates['endDate'];
                $link = $this->container->get('router')->generate(
                    'translationalresearch_logger',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $delayedInvoicesData[$date] = array('value'=>$value,'link'=>$link);
            }
            $combinedData["$unpaidInvoicesCount Unpaid Invoices"] = $delayedInvoicesData;

            //$combinedData['Delayed Project Requests'] = $delayedProjectsArr;
            //Use IRB review delayed days for all states
            $modifiedState = "irbreview";
            $projectReminderDelayField = 'projectReminderDelay'.$modifiedState;
            $reminderDelay = $transresUtil->getTransresSiteProjectParameter($projectReminderDelayField, null, $projectSpecialtyObject);
            if (!$reminderDelay) {
                $reminderDelay = 14; //default 14 days
            }
            //$combinedData["Project requests taking longer than $reminderDelay days to review"] = $delayedProjectsArr;
            //show event log
            $delayedProjectsData = array();
            foreach($delayedProjectsArr as $date=>$value ) {

                $dates = $datesArr[$date];
                $linkFilterArr['filter[startdate]'] = $dates['startDate'];
                $linkFilterArr['filter[enddate]'] = $dates['endDate'];
                $link = $this->container->get('router')->generate(
                    'translationalresearch_logger',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $delayedProjectsData[$date] = array('value'=>$value,'link'=>$link);
            }
            $combinedData["$delayedProjectsCount Project requests taking longer than $reminderDelay days to review"] = $delayedProjectsData;

            //$combinedData['Delayed Pending Work Request'] = $delayedRequestsArr;
            $reminderDelay = $transresUtil->getTransresSiteProjectParameter("pendingRequestReminderDelay", null, $projectSpecialtyObject);
            if (!$reminderDelay) {
                $reminderDelay = 28; //default 28 days
            }
            //$combinedData["Work requests taking longer than $reminderDelay days to complete"] = $delayedRequestsArr;
            $delayedRequestsData = array();
            foreach($delayedRequestsArr as $date=>$value ) {

                $dates = $datesArr[$date];
                $linkFilterArr['filter[startdate]'] = $dates['startDate'];
                $linkFilterArr['filter[enddate]'] = $dates['endDate'];
                $link = $this->container->get('router')->generate(
                    'translationalresearch_logger',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $delayedRequestsData[$date] = array('value'=>$value,'link'=>$link);
            }
            $combinedData["$delayedRequestsCount Work requests taking longer than $reminderDelay days to complete"] = $delayedRequestsData;

            //$combinedData['Delayed Completed Work Request'] = $delayedCompletedRequestsArr;
            $reminderDelay = $transresUtil->getTransresSiteProjectParameter("completedRequestReminderDelay", null, $projectSpecialtyObject);
            if (!$reminderDelay) {
                $reminderDelay = 4; //default 4 days
            }
            //$combinedData["Work requests completed for over $reminderDelay days in need of submitter notifications"] = $delayedCompletedRequestsArr;
            $delayedCompletedRequestsData = array();
            foreach($delayedCompletedRequestsArr as $date=>$value ) {

                $dates = $datesArr[$date];
                $linkFilterArr['filter[startdate]'] = $dates['startDate'];
                $linkFilterArr['filter[enddate]'] = $dates['endDate'];
                $link = $this->container->get('router')->generate(
                    'translationalresearch_logger',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $delayedCompletedRequestsData[$date] = array('value'=>$value,'link'=>$link);
            }
            $combinedData["$delayedCompletedRequestsCount Work requests completed for over $reminderDelay days in need of submitter notifications"] = $delayedCompletedRequestsData;

            //$combinedData['Delayed Completed and Notified Work Request without Invoices'] = $delayedCompletedNoInvoiceRequestsArr;
            $reminderDelay = $transresUtil->getTransresSiteProjectParameter("completedNoInvoiceRequestReminderDelay", null, $projectSpecialtyObject);
            if (!$reminderDelay) {
                $reminderDelay = 7; //default 7 days
            }
            //$combinedData["Work requests completed for over $reminderDelay days without invoices"] = $delayedCompletedNoInvoiceRequestsArr;
            $delayedCompletedNoInvoiceRequestsData = array();
            foreach($delayedCompletedNoInvoiceRequestsArr as $date=>$value ) {

                $dates = $datesArr[$date];
                $linkFilterArr['filter[startdate]'] = $dates['startDate'];
                $linkFilterArr['filter[enddate]'] = $dates['endDate'];
                $link = $this->container->get('router')->generate(
                    'translationalresearch_logger',
                    $linkFilterArr,
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $delayedCompletedNoInvoiceRequestsData[$date] = array('value'=>$value,'link'=>$link);
            }
            $combinedData["$delayedCompletedNoInvoiceRequestsCount Work requests completed for over $reminderDelay days without invoices"] = $delayedCompletedNoInvoiceRequestsData;

            //Total emails
            $totalEmails = $delayedProjectsCount + $delayedRequestsCount + $delayedCompletedRequestsCount + $delayedCompletedNoInvoiceRequestsCount + $unpaidInvoicesCount;
            if( $totalEmails ) {
                $chartName = $chartName . " ($totalEmails total)";
            }

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack"); //" getStackedChart("
        }

        //"56. Number of successful log ins for the TRP site per month" => "successful-logins-trp",
        if( $chartType == "successful-logins-trp" ) {
            $transresUtil = $this->container->get('transres_util');

            $loginsArr = array();
            $totalLoginCount = 0;

            $startDate->modify( 'first day of last month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                $thisEndDate->modify( 'first day of next month' );
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                $loginCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'translationalresearch');
                $totalLoginCount += $loginCount;

                $startDate->modify( 'first day of next month' );

                $loginsArr[$startDateLabel] = $loginCount;


            } while( $startDate < $endDate );

            $combinedData["TRP Login"] = $loginsArr;

            $chartName = $chartName . " (" . $totalLoginCount . " Total)";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");
        }

        //"57. Number of successful logins per site" => "successful-logins-site"
        if( $chartType == "successful-logins-site" ) {
            $transresUtil = $this->container->get('transres_util');

            $loginsEmployeesArr = array();
            $loginsTranslationalresearchArr = array();
            $loginsFellappArr = array();
            $loginsVacreqArr = array();
            $loginsCalllogArr = array();
            //$loginsScanArr = array();

            $totalLoginCount = 0;

            $startDate->modify( 'first day of last month' );
            do {
                $startDateLabel = $startDate->format('M-Y');
                $thisEndDate = clone $startDate;
                $thisEndDate->modify( 'first day of next month' );
                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                $loginEmployeesCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'employees');
                $loginsEmployeesArr[$startDateLabel] = $loginEmployeesCount;
                $totalLoginCount += $loginCount;

                $loginTranslationalresearchCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'translationalresearch');
                $loginsTranslationalresearchArr[$startDateLabel] = $loginTranslationalresearchCount;
                $totalLoginCount += $loginTranslationalresearchCount;

                $loginFellappCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'fellapp');
                $loginsFellappArr[$startDateLabel] = $loginFellappCount;
                $totalLoginCount += $loginFellappCount;

                $loginVacreqCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'vacreq');
                $loginsVacreqArr[$startDateLabel] = $loginVacreqCount;
                $totalLoginCount += $loginVacreqCount;

                $loginCalllogCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'calllog');
                $loginsCalllogArr[$startDateLabel] = $loginCalllogCount;
                $totalLoginCount += $loginCalllogCount;

                //$loginScanCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'scan');
                //$loginsScanArr[$startDateLabel] = $loginScanCount;
                //$totalLoginCount += $loginScanCount;

                $startDate->modify( 'first day of next month' );


                
            } while( $startDate < $endDate );

            $combinedData["Translational Research Logins"] = $loginsTranslationalresearchArr;
            $combinedData["Employee Directory Logins"] = $loginsEmployeesArr;
            $combinedData["Fellowship Applications Logins"] = $loginsFellappArr;
            $combinedData["Vacation Request Logins"] = $loginsVacreqArr;
            $combinedData["Call Log Book Logins"] = $loginsCalllogArr;
            //$combinedData["Glass Slide Scan Orders Logins"] = $loginsScanArr;

            $chartName = $chartName . " (" . $totalLoginCount . " Total)";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");
        }

        //"58. Number of successful logins per user" => "successful-logins-user"
//        if( $chartType == "successful-logins-user" ) {
//            $transresUtil = $this->container->get('transres_util');
//
//            $unique = true;
//            //$unique = false;
//
//            //$loginsArr = array();
//            $loginsUserArr = array();
//            $userArr = array();
//
//            //$totalLoginCount = 0;
//
//            $startDate->modify( 'first day of last month' );
//            do {
//                $startDateLabel = $startDate->format('M-Y');
//                $thisEndDate = clone $startDate;
//                $thisEndDate->modify( 'first day of next month' );
//                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
//                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
//
//                $loginsArr = $transresUtil->getLoginsUniqueUser($startDate,$thisEndDate,$unique);
//
//                foreach($loginsArr as $loginUser) {
//                    $loginUserId = $loginUser['id'];
//                    if( isset($userArr[$loginUserId]) ) {
//                        $userTitle = $userArr[$loginUserId];
//                    } else {
//                        $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->find($loginUserId);
//                        $userTitle = $user->getUsernameOptimal();
//                        $userArr[$loginUserId] = $userTitle;
//                    }
//                    //$user = $loginUser['user'];
//                    //echo "user=".$user."<br>";
//                    $loginsUserArr[$userTitle][$startDateLabel]++;
//                    //$loginsUserArr[$startDateLabel][$login->getUser()->getUsernameOptimal()]++;
//
//                    //$totalLoginCount++;
//                }
//
//                //$loginsUserArr[$user->getUsernameOptimal()]++;
//
//                $startDate->modify( 'first day of next month' );
//
//            } while( $startDate < $endDate );
//
//            foreach($loginsUserArr as $startDateLabel=>$userDataArr) {
//                $combinedData[$startDateLabel] = $userDataArr;
//            }
//
//            //$combinedData["Translational Research Logins"] = $loginsTranslationalresearchArr;
//
//            //$chartName = $chartName . " (" . $totalLoginCount . " Total)";
//
//            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");
//        }
        //"58. Number of successful logins per month" => "successful-unique-logins-site-month"
        if( $chartType == "successful-unique-logins-site-month" ) {
            $transresUtil = $this->container->get('transres_util');

            //single bar for a given week would be divided by sub-site and each bar segment should show the total number of unique user logins

            $loginsEmployeesArr = array();
            $loginsTranslationalresearchArr = array();
            $loginsFellappArr = array();
            $loginsVacreqArr = array();
            $loginsCalllogArr = array();
            //$loginsScanArr = array();

            $totalLoginCount = 0;

            $startDate->modify( 'first day of last month' );

            $interval = new \DateInterval('P1M');
            $dateRange = new \DatePeriod($startDate, $interval, $endDate);

            foreach( $dateRange as $startDate ) {
                //+6 days
                $thisEndDate = clone $startDate;
                $thisEndDate->add(new \DateInterval('P6D'));

                $startDateLabel = $startDate->format('d-M-Y');

                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                $loginEmployeesCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'employees',true);
                $loginsEmployeesArr[$startDateLabel] = $loginEmployeesCount;
                $totalLoginCount += $loginCount;

                $loginTranslationalresearchCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'translationalresearch',true);
                $loginsTranslationalresearchArr[$startDateLabel] = $loginTranslationalresearchCount;
                $totalLoginCount += $loginTranslationalresearchCount;

                $loginFellappCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'fellapp',true);
                $loginsFellappArr[$startDateLabel] = $loginFellappCount;
                $totalLoginCount += $loginFellappCount;

                $loginVacreqCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'vacreq',true);
                $loginsVacreqArr[$startDateLabel] = $loginVacreqCount;
                $totalLoginCount += $loginVacreqCount;

                $loginCalllogCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'calllog',true);
                $loginsCalllogArr[$startDateLabel] = $loginCalllogCount;
                $totalLoginCount += $loginCalllogCount;

            }

            $combinedData["Translational Research Logins"] = $loginsTranslationalresearchArr;
            $combinedData["Employee Directory Logins"] = $loginsEmployeesArr;
            $combinedData["Fellowship Applications Logins"] = $loginsFellappArr;
            $combinedData["Vacation Request Logins"] = $loginsVacreqArr;
            $combinedData["Call Log Book Logins"] = $loginsCalllogArr;
            //$combinedData["Glass Slide Scan Orders Logins"] = $loginsScanArr;

            $chartName = $chartName . " (" . $totalLoginCount . " Total)";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");
        }

        //"59. Number of unique successful logins per site per week" => "successful-unique-logins-site-week",
        if( $chartType == "successful-unique-logins-site-week" ) {
            $transresUtil = $this->container->get('transres_util');

            //single bar for a given week would be divided by sub-site and each bar segment should show the total number of unique user logins

            $loginsEmployeesArr = array();
            $loginsTranslationalresearchArr = array();
            $loginsFellappArr = array();
            $loginsVacreqArr = array();
            $loginsCalllogArr = array();
            //$loginsScanArr = array();

            $totalLoginCount = 0;

            $startDate->modify( 'first day of last month' );

            $interval = new \DateInterval('P1W');
            $dateRange = new \DatePeriod($startDate, $interval, $endDate);

            foreach( $dateRange as $startDate ) {
                //+6 days
                $thisEndDate = clone $startDate;
                $thisEndDate->add(new \DateInterval('P6D'));

                $startDateLabel = $startDate->format('d-M-Y');

                $datesArr[$startDateLabel] = array('startDate'=>$startDate->format('m/d/Y'),'endDate'=>$thisEndDate->format('m/d/Y'));
                //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";

                $loginEmployeesCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'employees',true);
                $loginsEmployeesArr[$startDateLabel] = $loginEmployeesCount;
                $totalLoginCount += $loginCount;

                $loginTranslationalresearchCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'translationalresearch',true);
                $loginsTranslationalresearchArr[$startDateLabel] = $loginTranslationalresearchCount;
                $totalLoginCount += $loginTranslationalresearchCount;

                $loginFellappCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'fellapp',true);
                $loginsFellappArr[$startDateLabel] = $loginFellappCount;
                $totalLoginCount += $loginFellappCount;

                $loginVacreqCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'vacreq',true);
                $loginsVacreqArr[$startDateLabel] = $loginVacreqCount;
                $totalLoginCount += $loginVacreqCount;

                $loginCalllogCount = $transresUtil->getLoginCount($startDate,$thisEndDate,'calllog',true);
                $loginsCalllogArr[$startDateLabel] = $loginCalllogCount;
                $totalLoginCount += $loginCalllogCount;

            }

            $combinedData["Translational Research Logins"] = $loginsTranslationalresearchArr;
            $combinedData["Employee Directory Logins"] = $loginsEmployeesArr;
            $combinedData["Fellowship Applications Logins"] = $loginsFellappArr;
            $combinedData["Vacation Request Logins"] = $loginsVacreqArr;
            $combinedData["Call Log Book Logins"] = $loginsCalllogArr;
            //$combinedData["Glass Slide Scan Orders Logins"] = $loginsScanArr;

            $chartName = $chartName . " (" . $totalLoginCount . " Total)";

            $chartsArray = $this->getStackedChart($combinedData, $chartName, "stack");
        }

        //"60. PIs with most projects" => "pis-with-most-projects"
        //for 60 total their project requests in any status except Draft or Canceled
        if( $chartType == "pis-with-most-projects" ) {

        }

        //"61. PIs with highest expenditures" => "pis-with-highest-expenditures"
        //for 61 total their paid invoices with status "Paid" for the time period in the filter well
        if( $chartType == "pis-with-highest-expenditures" ) {

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