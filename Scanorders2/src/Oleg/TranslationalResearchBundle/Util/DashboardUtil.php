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
            "2. Total number of projects per PI (Top 10)" =>                    "projects-per-pi",
            "3. Total number of Funded Projects per PI (Top 10)" =>             "funded-projects-per-pi",
            "4. Total number of Non-Funded Projects per PI (Top 10)" =>         "nonfunded-projects-per-pi",
            //Work request statistics
            "5. Total Number of Work Requests by Funding Source" =>             "requests-by-funding-source",
            "6. Total number of Requests per Project (Top 10)" =>               "requests-per-project",
            "7. Total number of Requests per Funded Project (Top 10)" =>        "requests-per-funded-projects",
            "8. Total number of Requests per Non-Funded Project (Top 10)" =>    "requests-per-nonfunded-projects",
            //   Products/Services
            "9. TRP Service Productivity by Products/Services (Top 10)" =>      "service-productivity-by-service",
            "10. TRP Service Productivity for Funded Projects (Top 10)" =>      "service-productivity-by-service-per-funded-projects",
            "11. TRP Service Productivity for Non-Funded Projects (Top 10)" =>  "service-productivity-by-service-per-nonfunded-projects",
            "11a. TRP Service Productivity by Products/Services" =>             "service-productivity-by-service-compare-funded-vs-nonfunded-projects",
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
                    $label = $label . " " . $valuePrefixLabel . $value;
                }
                $labels[] = $label;
                $values[] = $value;
                //$text[] = $value;
            }
        }

        if( count($values) == 0 ) {
            return array();
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
            return array();
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





    public function getProjectsByFilter($startDate, $endDate, $projectSpecialties, $addOneEndDay=true) {

        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->where("project.state = 'final_approved' OR project.state = 'closed'");

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







    public function getDashboardChart($request) {

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

        //echo "startDate=".$startDate."<br>";

        $chartsArray = array();

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

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => "1. Principle Investigators by Affiliation"
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
                }
            }

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piProjectCountTopArr = $this->getTopMultiArray($piProjectCountArr,$showOther); // getTopMultiArray(
            $filterArr['funded'] = null;
            //Projects per PI
            //                                           $dataArr,              $title,                                $type='pie', $layoutArray=null, $valuePrefixLabel=null
            $chartsArray = $this->getChartByMultiArray( $piProjectCountTopArr, $filterArr, "2. Total number of projects per PI (Top 10)","pie",null," : "); // addChart(

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
                    }
                }//foreach $pis
            }//foreach $projects

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piFundedProjectCountTopArr = $this->getTopMultiArray($piFundedProjectCountArr,$showOther);
            $filterArr['funded'] = true;
            $chartsArray = $this->getChartByMultiArray( $piFundedProjectCountTopArr, $filterArr, "3. Total number of Funded Projects per PI (Top 10)","pie",null," : ");

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
                    }
                }//foreach $pis
            }//foreach $projects

            $showOther = $this->getOtherStr($showLimited,"PIs");
            $piUnFundedProjectCountTopArr = $this->getTopMultiArray($piUnFundedProjectCountArr,$showOther);
            $filterArr['funded'] = false;
            $chartsArray = $this->getChartByMultiArray( $piUnFundedProjectCountTopArr, $filterArr, "4. Total number of Non-Funded Projects per PI (Top 10)","pie",null," : ");
        }
        ///////////////// EOF 4. Total number of Non-Funded Projects per PI (Top 10) /////////////////




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

            $dataArray = array();
            $chartDataArray = array();
            $type = 'pie';

            $layoutArray = array(
                'height' => $this->height,
                'width' =>  $this->width,
                'title' => "5. Total Number of Work Requests by Funding Source"
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
            }//foreach

            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );
            $showOther = $this->getOtherStr($showLimited,"Projects");
            $requestPerProjectTopArr = $this->getTopMultiArray($requestPerProjectArr,$showOther);
            $filterArr['funded'] = null;
            $chartsArray = $this->getChartByMultiArray($requestPerProjectTopArr, $filterArr, "6. Total number of Requests per Project (Top 10)","pie",$layoutArray," : ");
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
                }
            }

            $showOther = $this->getOtherStr($showLimited,"Projects");
            $fundedRequestPerProjectTopArr = $this->getTopMultiArray($fundedRequestPerProjectArr,$showOther);
            $filterArr['funded'] = true;
            $chartsArray = $this->getChartByMultiArray( $fundedRequestPerProjectTopArr, $filterArr, "7. Total number of Requests per Funded Project (Top 10)","pie",$layoutArray," : ");
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
                }
            }

            $showOther = $this->getOtherStr($showLimited,"Projects");
            $unFundedRequestPerProjectTopArr = $this->getTopMultiArray($unFundedRequestPerProjectArr,$showOther);
            $filterArr['funded'] = false;
            $chartsArray = $this->getChartByMultiArray( $unFundedRequestPerProjectTopArr, $filterArr, "8. Total number of Requests per Non-Funded Project (Top 10)","pie",$layoutArray," : ");
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
                    }
                }
            }

            $showOther = $this->getOtherStr($showLimited,"Products/Services");
            $quantityCountByCategoryTopArr = $this->getTopArray($quantityCountByCategoryArr,$showOther);
            $layoutArray = array(
                'height' => $this->height,
                'width' => $this->width,
            );
            $chartsArray = $this->getChart($quantityCountByCategoryTopArr, "9. TRP Service Productivity by Products/Services (Top 10)",'pie',$layoutArray," : ");

        }

        //10. TRP Service Productivity for Funded Projects (Top 10)
        if( $chartType == "service-productivity-by-service-per-funded-projects" ) {
            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                $project = $transRequest->getProject();
                $projectIndex = $project->getOid(false);
                $projectId = $project->getId();
                $piIdArr = array();
            }
        }

        //11. TRP Service Productivity for Non-Funded Projects (Top 10)
        if( $chartType == "service-productivity-by-service-per-nonfunded-projects" ) {

        }

        //11a. TRP Service Productivity by Products/Services
        if( $chartType == "service-productivity-by-service-compare-funded-vs-nonfunded-projects" ) {

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

        if( $chartType == "" ) {

        }

        if( $chartType == "" ) {

        }

        if( $chartType == "" ) {

        }

        if( $chartType == "" ) {
            $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
            foreach($requests as $transRequest) {
                $project = $transRequest->getProject();
                $projectIndex = $project->getOid(false);
                $projectId = $project->getId();
                $piIdArr = array();
            }
        }




        if( count($chartsArray) == 0 ) {
            $chartKey = $this->getChartTypeByValue($chartType);
            $chartsArray['error'] = "Chart type '$chartKey' is not found";
        } else {
            $chartsArray['error'] = false;
        }
        
        return $chartsArray;
    }
    
}