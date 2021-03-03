<?php

namespace App\TranslationalResearchBundle\Controller;

use App\TranslationalResearchBundle\Form\FilterDashboardType;
use App\UserdirectoryBundle\Util\LargeFileDownloader;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Date;


/**
 * @Route("simple-dashboard")
 */
class DashboardSimpleController extends OrderAbstractController
{

    private $width = 1200;
    private $height = 600;
    private $otherId = "All other [[otherStr]] combined";
    private $otherSearchStr = "All other ";

    /**
     * @Route("/pi-project-statistics/", name="translationalresearch_dashboard_project")
     * @Template("AppTranslationalResearchBundle/Dashboard/dashboard.html.twig")
     */
    public function projectStatisticsAction( Request $request )
    {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $filterform = $this->getFilter();
        $filterform->handleRequest($request);

        $showLimited = $filterform['showLimited']->getData();
        //echo "showLimited=".$showLimited."<br>";

        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $projectSpecialty = $filterform['projectSpecialty']->getData();
        if( $projectSpecialty != 0 ) {
            $projectSpecialtyObject = $em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }

//        if( $startDate ) {
//            $startDateStr = $startDate->format('m/d/Y');
//        }
//        if( $endDate ) {
//            $endDateStr = $endDate->format('m/d/Y');
//        }

        $filterArr = array(
            'startDate'=>$startDate,
            'endDate'=>$endDate,
            'projectSpecialtyObjects' => $projectSpecialtyObjects,
            'showLimited' => $showLimited,
            'funded' => null
        );

        $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
        //echo "projects=".count($projects)."<br>";

        /////////////// 1. Principle Investigators by Affiliation /////////////////////
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
        /////////////// EOF 1. Principle Investigators by Affiliation /////////////////////

        //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
        $piProjectCountArr = array();

        // 3. Total number of Funded Projects per PI (Top 10)
        $piFundedProjectCountArr = array();
        //4. Total number of Non-Funded Projects per PI (Top 10)
        $piUnFundedProjectCountArr = array();

        foreach($projects as $project) {

            //$fundingNumber = $project->getFundedAccountNumber();
            $fundingNumber = $project->getFunded();

            //1. Principle Investigators by Affiliation
            //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
            $pis = $project->getPrincipalInvestigators();
            foreach ($pis as $pi) {
                $userName = $pi->getUsernameOptimal();
                $userId = $pi->getId();

//                $linkFilterArr = array(
//                    //'filter[principalInvestigators][]' => $objectid,
//                    'filter[state][0]' => 'final_approved',
//                    'filter[state][1]' => 'closed',
//                    'filter[startDate]' => $startDateStr,
//                    'filter[endDate]' => $endDateStr
//                );
//                if( $fundingNumber === true ) {
//                    $linkFilterArr['filter[fundingType]'] = 'Funded';
//                }
//                if( $fundingNumber === false ) {
//                    $linkFilterArr['filter[fundingType]'] = 'Non-Funded';
//                }

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

                //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
//                if (isset($piProjectCountArr[$userName])) {
//                    $count = $piProjectCountArr[$userName] + 1;
//                } else {
//                    $count = 1;
//                }
//                $piProjectCountArr[$userName] = $count;
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

                //$linkFilterArr['filter[fundingType]'] = null;
//                $link = $this->container->get('router')->generate(
//                    'translationalresearch_request_index_filter',
//                    $linkFilterArr,
//                    UrlGeneratorInterface::ABSOLUTE_URL
//                );
//                $piProjectCountArr[$userId]['show-path'] = $link;

                /////////// 3,4 Total number of Funded/Un-Funded Projects per PI (Top 10) ////////////////
                if( $fundingNumber ) {
                    // 3. Total number of Funded Projects per PI (Top 10)
//                    if (isset($piFundedProjectCountArr[$userName])) {
//                        $count = $piFundedProjectCountArr[$userName] + 1;
//                    } else {
//                        $count = 1;
//                    }
//                    $piFundedProjectCountArr[$userName] = $count;
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
                } else {
                    //4. Total number of Non-Funded Projects per PI (Top 10)
//                    if (isset($piUnFundedProjectCountArr[$userName])) {
//                        $count = $piUnFundedProjectCountArr[$userName] + 1;
//                    } else {
//                        $count = 1;
//                    }
//                    $piUnFundedProjectCountArr[$userName] = $count;
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
                /////////// EOF 3,4 Total number of Funded/Un-Funded Projects per PI (Top 10) ////////////////



            } //foreach $pi
        } //foreach $project

        $chartsArray = array();

        ///////////// 1. Principle Investigators by Affiliation ///////////////////
        $dataArray = array();
        $chartDataArray = array();
        $type = 'pie';

        $layoutArray = array(
            'height' => $this->height,
            'width' =>  $this->width,
            'title' => "1. Principle Investigators by Affiliation"
        );

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

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );
        $chartsArray[] = array('newline'=>true);
        ///////////// EOF 1. Principle Investigators by Affiliation ///////////////////

        ///////////// 2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED) - $piProjectCountArr //////////////
        //TODO: add link to the filtered project list by PI
        $showOther = $this->getOtherStr($showLimited,"PIs");
        $piProjectCountTopArr = $this->getTopMultiArray($piProjectCountArr,$showOther); // getTopMultiArray(
        $filterArr['funded'] = null;
        //Projects per PI
        //                              $chartsArray, $dataArr,              $title,                                $type='pie', $layoutArray=null, $valuePrefixLabel=null
        $chartsArray = $this->addChartByMultiArray( $chartsArray, $piProjectCountTopArr, $filterArr, "2. Total number of projects per PI (Top 10)","pie",null," : "); // addChart(
        ///////////// EOF top $piProjectCountArr //////////////

        /////////// 3,4 Total number of Funded/Un-Funded Projects per PI (Top 10) ////////////////
        //3. Funded Projects per PI
        $showOther = $this->getOtherStr($showLimited,"PIs");
        $piFundedProjectCountTopArr = $this->getTopMultiArray($piFundedProjectCountArr,$showOther);
        $filterArr['funded'] = true;
        $chartsArray = $this->addChartByMultiArray( $chartsArray, $piFundedProjectCountTopArr, $filterArr, "3. Total number of Funded Projects per PI (Top 10)","pie",null," : ");
        //4. Un-Funded Projects per PI
        //Funded Projects per PI
        $showOther = $this->getOtherStr($showLimited,"PIs");
        $piUnFundedProjectCountTopArr = $this->getTopMultiArray($piUnFundedProjectCountArr,$showOther);
        $filterArr['funded'] = false;
        $chartsArray = $this->addChartByMultiArray( $chartsArray, $piUnFundedProjectCountTopArr, $filterArr, "4. Total number of Non-Funded Projects per PI (Top 10)","pie",null," : ");
        /////////// EOF 3,4 Total number of Funded/Un-Funded Projects per PI (Top 10) ////////////////

        return array(
            'title' => "PI/PROJECT STATISTICS (APPROVED or CLOSED)".", ".count($projects)." Total Matching Projects",
            'filterform' => $filterform->createView(),
            'chartsArray' => $chartsArray
        );
    }

    /**
     * @Route("/work-request-statistics/", name="translationalresearch_dashboard_request")
     * @Template("AppTranslationalResearchBundle/Dashboard/dashboard.html.twig")
     */
    public function requestStatisticsAction( Request $request )
    {

        if ($this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $filterform = $this->getFilter();
        $filterform->handleRequest($request);

        $showLimited = $filterform['showLimited']->getData();
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();

        $projectSpecialty = $filterform['projectSpecialty']->getData();
        if( $projectSpecialty != 0 ) {
            $projectSpecialtyObject = $em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }

        $filterArr = array(
            'startDate'=>$startDate,
            'endDate'=>$endDate,
            'projectSpecialtyObjects' => $projectSpecialtyObjects,
            'showLimited' => $showLimited,
            'funded' => null
        );

        $requests = $this->getRequestsByFilter($startDate,$endDate,$projectSpecialtyObjects);
        //echo "requests=".count($requests)."<br>";

        $fundedRequestCount = 0;
        $notFundedRequestCount = 0;

        $requestPerProjectArr = array();
        $fundedRequestPerProjectArr = array();
        $unFundedRequestPerProjectArr = array();
        $quantityCountByCategoryArr = array();
        $fundedQuantityCountByCategoryArr = array();
        $unFundedQuantityCountByCategoryArr = array();

        foreach($requests as $transRequest) {

            $project = $transRequest->getProject();
            $projectIndex = $project->getOid(false);
            $projectId = $project->getId();
            $pis = $project->getPrincipalInvestigators();
            $piInfoArr = array();
            $piIdArr = array();
            foreach( $pis as $pi ) {
                if( $pi ) {
                    $piInfoArr[] = $pi->getUsernameOptimal();
                    $piIdArr[] = $pi->getId();
                }
            }
            if( count($piInfoArr) > 0 ) {
                $projectIndex = $projectIndex . " (" . implode(", ",$piInfoArr) . ")";
            }

            //5. Total Number of Work Requests (XXXX) by Funding Source
            if( $transRequest->getFundedAccountNumber() ) {
                $fundedRequestCount++;
            } else {
                $notFundedRequestCount++;
            }
            //////////////////////

            //6. Total number of Requests per Project (Top 10)
//            if (isset($requestPerProjectArr[$projectIndex])) {
//                $count = $requestPerProjectArr[$projectIndex] + 1;
//            } else {
//                $count = 1;
//            }
//            $requestPerProjectArr[$projectIndex] = $count;
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
            //$requestPerProjectArr[$projectId]['projectid'] = $projectId;
            //////////////////////

            //7,8. Total number of Requests per Funded/Un-Funded Project (Top 10)
            if( $transRequest->getFundedAccountNumber() ) {
                //7. Total number of Requests per Funded Project (Top 10)
//                if (isset($fundedRequestPerProjectArr[$projectIndex])) {
//                    $count = $fundedRequestPerProjectArr[$projectIndex] + 1;
//                } else {
//                    $count = 1;
//                }
//                $fundedRequestPerProjectArr[$projectIndex] = $count;
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
            } else {
                //8. Total number of Requests per Non_Funded Project (Top 10)
//                if (isset($unFundedRequestPerProjectArr[$projectIndex])) {
//                    $count = $unFundedRequestPerProjectArr[$projectIndex] + 1;
//                } else {
//                    $count = 1;
//                }
//                $unFundedRequestPerProjectArr[$projectIndex] = $count;
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
            //////////////////////

            //9. TRP Service Productivity by Category Types (Top 10)
            //9- Group work requests Based on what is ordered (“Category”) & sorted by Total Quantity (1 work request ordering 1000 slides counts as 1000)
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

                    //10,11. TRP Service Productivity for Funded/Not-Funded Projects (Top 10)
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
                }
            }
            ///////////////////////////

        } //foreach $requests

        $chartsArray = array();

        //5. Total Number of Work Requests (XXXX) by Funding Source
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

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );
        $chartsArray[] = array('newline'=>true);
        ////////////////////

        //6. Total number of Requests per Project (Top 10)
        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
        );
        $showOther = $this->getOtherStr($showLimited,"Projects");
        $requestPerProjectTopArr = $this->getTopMultiArray($requestPerProjectArr,$showOther);
        $filterArr['funded'] = null;
        $chartsArray = $this->addChartByMultiArray( $chartsArray, $requestPerProjectTopArr, $filterArr, "6. Total number of Requests per Project (Top 10)","pie",$layoutArray," : ");
        ////////////////////

        //7,8. Total number of Requests per Funded/Un-Funded Project (Top 10)
        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
        );
        //7. Total number of Requests per Funded Project (Top 10)
        $showOther = $this->getOtherStr($showLimited,"Projects");
        $fundedRequestPerProjectTopArr = $this->getTopMultiArray($fundedRequestPerProjectArr,$showOther);
        $filterArr['funded'] = true;
        $chartsArray = $this->addChartByMultiArray( $chartsArray, $fundedRequestPerProjectTopArr, $filterArr, "7. Total number of Requests per Funded Project (Top 10)","pie",$layoutArray," : ");
        //8. Total number of Requests per Non_Funded Project (Top 10)
        $showOther = $this->getOtherStr($showLimited,"Projects");
        $unFundedRequestPerProjectTopArr = $this->getTopMultiArray($unFundedRequestPerProjectArr,$showOther);
        $filterArr['funded'] = false;
        $chartsArray = $this->addChartByMultiArray( $chartsArray, $unFundedRequestPerProjectTopArr, $filterArr, "8. Total number of Requests per Non-Funded Project (Top 10)","pie",$layoutArray," : ");
        ////////////////////

        //9. TRP Service Productivity by Category Types (Top 10)
        //9- Group work requests Based on what is ordered (“Category”) & sorted by Total Quantity (1 work request ordering 1000 slides counts as 1000)
        $showOther = $this->getOtherStr($showLimited,"Products/Services");
        $quantityCountByCategoryTopArr = $this->getTopArray($quantityCountByCategoryArr,$showOther);
        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
        );
        $chartsArray = $this->addChart( $chartsArray, $quantityCountByCategoryTopArr, "9. TRP Service Productivity by Products/Services (Top 10)",'pie',$layoutArray," : ");
        ///////////////////////////

        //10,11. TRP Service Productivity for Funded/Not-Funded Projects (Top 10)
        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
        );
        //10. TRP Service Productivity for Funded Projects (Top 10)
        $showOther = $this->getOtherStr($showLimited,"Projects");
        $fundedQuantityCountByCategoryTopArr = $this->getTopArray($fundedQuantityCountByCategoryArr,$showOther);
        $chartsArray = $this->addChart( $chartsArray, $fundedQuantityCountByCategoryTopArr, "10. TRP Service Productivity for Funded Projects (Top 10)",'pie',$layoutArray," : ");
        //11. TRP Service Productivity for Non-Funded Projects (Top 10)
        $showOther = $this->getOtherStr($showLimited,"Projects");
        $unFundedQuantityCountByCategoryTopArr = $this->getTopArray($unFundedQuantityCountByCategoryArr,$showOther);
        $chartsArray = $this->addChart( $chartsArray, $unFundedQuantityCountByCategoryTopArr, "11. TRP Service Productivity for Non-Funded Projects (Top 10)",'pie',$layoutArray," : ");
        ////////////////////////////////

        //6) Add double bar chart for the "TRP Service Productivity by Category Type" chart (Funded - Non-Funded)
        $combinedTrpData = array();
        $combinedTrpData['Funded'] = $fundedQuantityCountByCategoryTopArr;  //$fundedQuantityCountByCategoryArr;
        $combinedTrpData['Not-Funded'] = $unFundedQuantityCountByCategoryTopArr;    //$unFundedQuantityCountByCategoryArr;
        $chartsArray = $this->addStackedChart( $chartsArray, $combinedTrpData, "11a. TRP Service Productivity by Products/Services", "stack");
        /////////////////////////////

        return array(
            'title' => "WORK REQUESTS STATISTICS".", ".count($requests)." Total Matching Requests",
            'filterform' => $filterform->createView(),
            'chartsArray' => $chartsArray
        );
    }

    /**
     * @Route("/productivity-statistics-based-on-work-requests/", name="translationalresearch_dashboard_financial_request")
     * @Template("AppTranslationalResearchBundle/Dashboard/dashboard.html.twig")
     */
    public function requestFinancialStatisticsAction( Request $request )
    {

        if ($this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();

        $filterform = $this->getFilter(true);
        $filterform->handleRequest($request);

        $showLimited = $filterform['showLimited']->getData();
        //echo "showLimited=".$showLimited."<br>";

        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();

        //$compareType = $filterform['compareType']->getData();
       // $compareType = str_replace("-"," ",$compareType);

        $projectSpecialty = $filterform['projectSpecialty']->getData();
        if ($projectSpecialty != 0) {
            $projectSpecialtyObject = $em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }

        //Get data from the request's perspective
        //12- Show radio buttons allowing work request submission date vs last invoice generation date vs date when status changed to “paid in full”;
        // Source of dollar amount is “Total Fees”
        //12. Total Fees by Work Requests (Total $400K)
        //13. Total Fees per Funded Project (Top 10)
        //14. Total Fees per non-funded Project (Top 10)

        //15. Total Fees per Investigator (Top 10)
        //16. Total Fees per Investigator (Funded) (Top 10)
        //17. Total Fees per Investigator (non-Funded) (Top 10)

        $transRequests = $this->getRequestsByFilter($startDate, $endDate, $projectSpecialtyObjects, true);

        $subtotalFees = 0;
        $fundedTotalFees = 0;
        $unFundedTotalFees = 0;

        $fundedTotalFeesByRequestArr = array();
        $unFundedTotalFeesByRequestArr = array();

        $totalFeesByInvestigatorArr = array();
        $fundedTotalFeesByInvestigatorArr = array();
        $unFundedTotalFeesByInvestigatorArr = array();


        foreach($transRequests as $transRequest) {

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

            $pis = $transRequest->getPrincipalInvestigators();
            if( count($pis) > 0 ) {
                $pi = $pis[0];
                $investigatorIndex = $pi->getUsernameOptimal();
            }

            //"Total Fees" to be based on the work request's  "Product or Service" fields
            // (quantity * product/category's fees per unit = Subtotal fees) - the same way as it is shown
            // on the Request's list in the column "Total Fees". This "Total Fees" will not count any discounts.
            $subtotalFee = intval($transresRequestUtil->getTransResRequestSubTotal($transRequest));

            $subtotalFees = $subtotalFees + $subtotalFee;

            //12. Total Fees by Work Requests (Total $400K)
            ////////////////////

            //13. Total Fees per Funded Project (Top 10)
            //14. Total Fees per non-funded Project (Top 10)
            if( $transRequest->getFundedAccountNumber() ) {
                //12. Total Fees by Work Requests (Total $400K)
                $fundedTotalFees = $fundedTotalFees + $subtotalFee;
                //13. Total Fees per Funded Project (Top 10)
                if (isset($fundedTotalFeesByRequestArr[$projectIndex])) {
                    $totalFee = $fundedTotalFeesByRequestArr[$projectIndex] + $subtotalFee;
                } else {
                    $totalFee = $subtotalFee;
                }
                $totalFee = $this->getNumberFormat($totalFee);
                $fundedTotalFeesByRequestArr[$projectIndex] = $totalFee;
            } else {
                //12. Total Fees by Work Requests (Total $400K)
                $unFundedTotalFees = $unFundedTotalFees + $subtotalFee;
                //14. Total Fees per non-funded Project (Top 10)
                if (isset($unFundedTotalFeesByRequestArr[$projectIndex])) {
                    $totalFee = $unFundedTotalFeesByRequestArr[$projectIndex] + $subtotalFee;
                } else {
                    $totalFee = $subtotalFee;
                }
                $totalFee = $this->getNumberFormat($totalFee);
                $unFundedTotalFeesByRequestArr[$projectIndex] = $totalFee;
            }
            /////////////////////

            //15. Total Fees per Investigator (Top 10)
            if (isset($totalFeesByInvestigatorArr[$investigatorIndex])) {
                $totalFee = $totalFeesByInvestigatorArr[$investigatorIndex] + $subtotalFee;
            } else {
                $totalFee = $subtotalFee;
            }
            $totalFee = $this->getNumberFormat($totalFee);
            $totalFeesByInvestigatorArr[$investigatorIndex] = $totalFee;
            /////////////////////////////

            //16. Total Fees per Investigator (Funded) (Top 10)
            //17. Total Fees per Investigator (non-Funded) (Top 10)
            if( $transRequest->getFundedAccountNumber() ) {
                //16. Total Fees per Investigator (Funded) (Top 10)
                if (isset($fundedTotalFeesByInvestigatorArr[$investigatorIndex])) {
                    $totalFee = $fundedTotalFeesByInvestigatorArr[$investigatorIndex] + $subtotalFee;
                } else {
                    $totalFee = $subtotalFee;
                }
                $totalFee = $this->getNumberFormat($totalFee);
                $fundedTotalFeesByInvestigatorArr[$investigatorIndex] = $totalFee;
            } else {
                //17. Total Fees per Investigator (non-Funded) (Top 10)
                if (isset($unFundedTotalFeesByInvestigatorArr[$investigatorIndex])) {
                    $totalFee = $unFundedTotalFeesByInvestigatorArr[$investigatorIndex] + $subtotalFee;
                } else {
                    $totalFee = $subtotalFee;
                }
                $totalFee = $this->getNumberFormat($totalFee);
                $unFundedTotalFeesByInvestigatorArr[$investigatorIndex] = $totalFee;
            }
            ////////////////////////////////////////

        } //foreach invoices

        $chartsArray = array();

        //12. Total Fees by Work Requests (Total $400K)
        $dataArray = array();
        $chartDataArray = array();
        $type = 'pie';
        $subtotalFees = $this->getNumberFormat($subtotalFees);

        $layoutArray = array(
            'height' => $this->height,
            'width' =>  $this->width,
            'title' => "12. Total Fees by Work Requests (Total $".$subtotalFees.")"
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

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );
        $chartsArray[] = array('newline'=>true);
        /////////////////////

        //13. Total Fees per Funded Project (Top 10)
        //14. Total Fees per non-funded Project (Top 10)
        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
        );
        //13. Total Fees per Funded Project (Top 10)
        $showOther = $this->getOtherStr($showLimited,"Projects");
        $fundedTotalFeesByRequestTopArr = $this->getTopArray($fundedTotalFeesByRequestArr,$showOther);
        $chartsArray = $this->addChart( $chartsArray, $fundedTotalFeesByRequestTopArr, "13. Total Fees per Funded Project (Top 10)",'pie',$layoutArray," : $");
        //14. Total Fees per non-funded Project (Top 10)
        $showOther = $this->getOtherStr($showLimited,"Projects");
        $unFundedTotalFeesByRequestTopArr = $this->getTopArray($unFundedTotalFeesByRequestArr,$showOther);
        $chartsArray = $this->addChart( $chartsArray, $unFundedTotalFeesByRequestTopArr, "14. Total Fees per Non-Funded Project (Top 10)",'pie',$layoutArray," : $");
        ////////////////////////////////

        //15. Total Fees per Investigator (Top 10)
        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
        );
        $showOther = $this->getOtherStr($showLimited,"Investigators");
        $totalFeesByInvestigatorTopArr = $this->getTopArray($totalFeesByInvestigatorArr,$showOther);
        $chartsArray = $this->addChart( $chartsArray, $totalFeesByInvestigatorTopArr, "15. Total Fees per Investigator (Top 10)",'pie',$layoutArray," : $");
        ////////////////////////////

        //$chartsArray[] = array('newline'=>true);

        //16. Total Fees per Investigator (Funded) (Top 10)
        //17. Total Fees per Investigator (non-Funded) (Top 10)
        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
        );
        //16. Total Fees per Investigator (Funded) (Top 10)
        $showOther = $this->getOtherStr($showLimited,"Investigators");
        $fundedTotalFeesByInvestigatorTopArr = $this->getTopArray($fundedTotalFeesByInvestigatorArr,$showOther);
        $chartsArray = $this->addChart( $chartsArray, $fundedTotalFeesByInvestigatorTopArr, "16. Total Fees per Investigator (Funded) (Top 10)",'pie',$layoutArray," : $");
        //17. Total Fees per Investigator (non-Funded) (Top 10)
        $showOther = $this->getOtherStr($showLimited,"Investigators");
        $unFundedTotalFeesByInvestigatorTopArr = $this->getTopArray($unFundedTotalFeesByInvestigatorArr,$showOther);
        $chartsArray = $this->addChart( $chartsArray, $unFundedTotalFeesByInvestigatorTopArr, "17. Total Fees per Investigator (Non-Funded) (Top 10)",'pie',$layoutArray," : $");
        ////////////////////////////////////////

        return array(
            'title' => "Work Requests Financial Statistics ($)" . ", " . count($transRequest) . " Total Matching Work Requests",
            'filterform' => $filterform->createView(),
            'chartsArray' => $chartsArray
        );
    }

    /**
     * @Route("/financial-statistics-based-on-invoices/", name="translationalresearch_dashboard_financial_invoice")
     * @Template("AppTranslationalResearchBundle/Dashboard/dashboard.html.twig")
     */
    public function invoiceFinancialStatisticsAction( Request $request )
    {

        if ($this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();

        $filterform = $this->getFilter(true,true);
        $filterform->handleRequest($request);

        $showLimited = $filterform['showLimited']->getData();
        //echo "showLimited=".$showLimited."<br>";

        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();

        $compareType = $filterform['compareType']->getData();
        $compareType = str_replace("-"," ",$compareType);

        $projectSpecialty = $filterform['projectSpecialty']->getData();
        if ($projectSpecialty != 0) {
            $projectSpecialtyObject = $em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }

        //Get data from the invoice perspective
        //12- Show radio buttons allowing work request submission date vs last invoice generation date vs date when status changed to “paid in full”;
        // Source of dollar amount is “Total Fees”

        //18. Generated Invoices by Status from Funded Projects (Total invoiced $152K)
        //19. Generated Invoices by Status per Funded Project (Top 10)
        //20. Generated Invoices by Status per PI (Top 10)

        $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects, true, $compareType);

        $totalInvoiceFees = 0;
        $paidInvoices = 0;
        $unpaidInvoices = 0;
        $totalInvoices = 0;

        $totalFundedPaidFees = 0;
        $totalFundedDueFees = 0;
        //$totalFundedFees = 0;

        //$invoiceTotalFee = 0;
        //$invoiceDueFee = 0;
        $totalThisInvoiceFees = 0;
        $totalThisInvoiceVerificationFees = 0;

        $invoicesByProjectArr = array();
        //$invoicesByPiArr = array();
        $invoicesFeesByProjectArr = array();
        $invoicesFeesByPiArr = array();

        $invoicePaidFeeArr = array();
        $invoiceDueFeeArr = array();

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

            $totalInvoiceFee = intval($invoice->getTotal());

            $totalInvoiceFees = $totalInvoiceFees + $totalInvoiceFee;

            $totalThisInvoiceFee = intval($invoice->getTotal());
            $paidThisInvoiceFee = intval($invoice->getPaid());
            $dueThisInvoiceFee = intval($invoice->getDue());

            //18. Generated Invoices by Status from Funded Projects (Total invoiced $152K)
            if ($transRequest->getFundedAccountNumber()) {
                if ($invoice->getStatus() == "Paid in Full") {
                    $paidInvoices++;
                    //$totalFundedPaidFees = $totalFundedPaidFees + $totalThisInvoiceFee;
                    //$totalFundedPaidFees = $totalFundedPaidFees + $paidThisInvoiceFee;
                } else {
                    $unpaidInvoices++;
                    //$totalFundedDueFees = $totalFundedDueFees + $dueThisInvoiceFee;
                }
                $totalInvoices++;
                $totalFundedPaidFees = $totalFundedPaidFees + $paidThisInvoiceFee;
                $totalFundedDueFees = $totalFundedDueFees + $dueThisInvoiceFee;
                //$totalFundedFees = $totalFundedFees + $totalInvoiceFee;
                $totalThisInvoiceFees = $totalThisInvoiceFees + $totalThisInvoiceFee;
                $totalThisInvoiceVerificationFees = $totalThisInvoiceVerificationFees + ($paidThisInvoiceFee + $dueThisInvoiceFee);
            }
            //////////////////////////////////////////////

            //19. Generated Invoices by Status per Funded Project (Top 10)
            //20. Generated Invoices by Status per PI (Top 10)
            if ($transRequest->getFundedAccountNumber()) {
                //19. Generated Invoices by Status per Funded Project (Top 10)
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
                $invoicesFeesByProjectArr[$projectIndex] = $totalFee;

                //20. Generated Invoices by Status per PI (Top 10)

                /////// count - NOT USED ///////
//                if (isset($invoicesByPiArr[$investigatorIndex])) {
//                    $count = $invoicesByPiArr[$investigatorIndex] + 1;
//                } else {
//                    $count = 1;
//                }
//                $invoicesByPiArr[$investigatorIndex] = $count;
                /////// EOF count - NOT USED ///////

                //Total fees
                if (isset($invoicesFeesByPiArr[$investigatorIndex])) {
                    $totalFee = $invoicesFeesByPiArr[$investigatorIndex] + $totalThisInvoiceFee;
                } else {
                    $totalFee = $totalThisInvoiceFee;
                }
                $invoicesFeesByPiArr[$investigatorIndex] = $totalFee;

                //paid
                //$invoiceTotalFee = $invoiceTotalFee + $totalThisInvoiceFee;
                if (isset($invoicePaidFeeArr[$investigatorIndex])) {
                    $totalFee = $invoicePaidFeeArr[$investigatorIndex] + $paidThisInvoiceFee;
                } else {
                    $totalFee = $paidThisInvoiceFee;
                }
                $invoicePaidFeeArr[$investigatorIndex] = $totalFee;

                //unpaid
                //$invoiceDueFee = $invoiceDueFee + $dueThisInvoiceFee;
                if (isset($invoiceDueFeeArr[$investigatorIndex])) {
                    $totalFee = $invoiceDueFeeArr[$investigatorIndex] + $dueThisInvoiceFee;
                } else {
                    $totalFee = $dueThisInvoiceFee;
                }
                $invoiceDueFeeArr[$investigatorIndex] = $totalFee;

                //$invoicesFeesByPiArr[$investigatorIndex] = array('total'=>$totalThisInvoiceFees,'paid'=>$totalFundedPaidFees,'due'=>$totalFundedDueFees);

            }
            /////////////////////////////////////////

        } //foreach invoices

        $chartsArray = array();

        //18. Generated Invoices by Status from Funded Projects (Total invoiced $152K)
        $dataArray = array();
        $chartDataArray = array();
        $type = 'pie';

        //$totalFundedFees = $totalFundedPaidFees + $totalFundedDueFees;

        $layoutArray = array(
            'height' => $this->height,
            'width' =>  $this->width,
            'title' => "18. Generated Invoices from Funded Projects (Total invoiced $".$totalThisInvoiceVerificationFees."; Total invoices: ".$totalInvoices.", 'Paid in Full' invoices: ".$paidInvoices.")"
        );

        $labels = array('Paid'.' : $'.$totalFundedPaidFees,'Unpaid (Due)'.' : $'.$totalFundedDueFees);
        $values = array($totalFundedPaidFees,$totalFundedDueFees);

        $chartDataArray['values'] = $values;
        $chartDataArray['labels'] = $labels;
        $chartDataArray['type'] = $type;
        $chartDataArray["textinfo"] = "value+percent";
        $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
        $chartDataArray['marker'] = array('colors' => array("rgb(44, 160, 44)", "rgb(214, 39, 40)") );
        $chartDataArray['direction'] = 'clockwise';
        $dataArray[] = $chartDataArray;

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );
        $chartsArray[] = array('newline'=>true);
        /////////////////////////////

        //19. Generated Invoices by Status per Funded Project (Top 10)
        //20. Generated Invoices by Status per PI (Top 10)
        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
        );
        //19. Generated Invoices by Status per Funded Project (Top 10)
        $showOther = $this->getOtherStr($showLimited,"Projects");
        $invoicesByProjectTopArr = $this->getTopArray($invoicesByProjectArr,$showOther);
        $invoicesFeesByProjectTopArr = $this->getTopArray($invoicesFeesByProjectArr,$showOther);
        //merge two to attach fees to label
        $invoicesByProjectTopArr = $this->attachSecondValueToFirstLabel($invoicesByProjectTopArr,$invoicesFeesByProjectTopArr," : $");
        $chartsArray = $this->addChart( $chartsArray, $invoicesByProjectTopArr, "19. Generated Invoices by Status per Funded Project (Top 10)",'pie',$layoutArray);

        //TODO: add paid/unpaid for each PI
        //20. Generated Invoices by Status per PI (Top 10)
        //$invoicesByPiTopArr = $this->getTopArray($invoicesByPiArr);
        //$invoicesFeesByPiTopArr = $this->getTopArrayAsArray($invoicesFeesByPiArr);
        $descriptionArr = array(
            array("paid $"," : $","limegreen",$invoicePaidFeeArr),
            array("due $"," : $","red",$invoiceDueFeeArr)
        );
        $showOther = $this->getOtherStr($showLimited,"PIs");
        $invoicesFeesByPiArrTop = $this->getTopArray($invoicesFeesByPiArr,$showOther,$descriptionArr);

        //merge two to attach fees to label
        //$invoicesByPiTopArr = $this->attachSecondValueToFirstLabel($invoicesByPiTopArr,$invoicesFeesByPiTopArr,"-$");

        //$invoicePaidFeeArr
        //$invoiceTotalFeeTopArr = $this->getTopArray($invoicesFeesByPiArr);

        $chartsArray = $this->addChart( $chartsArray, $invoicesFeesByPiArrTop, "20. Generated Invoices by Status per PI (Top 10)",'pie',$layoutArray);
        //////////////////////////////////////////////

        return array(
            'title' => "Invoices Financial Statistics($)" . ", " . count($transRequest) . " Total Matching Invoices",
            'filterform' => $filterform->createView(),
            'chartsArray' => $chartsArray
        );
    }

    public function getFilter( $showLimited=false, $withCompareType=false ) {
        $transresUtil = $this->container->get('transres_util');
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

        $filterform = $this->createForm(FilterDashboardType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));
        //$filterform->handleRequest($request);
        //////////// EOF Filter ////////////

        return $filterform;
    }




    /**
     * @Route("/pi-statistics/", name="translationalresearch_dashboard_pilevel")
     * @Route("/project-statistics/", name="translationalresearch_dashboard_projectlevel")
     * @Route("/invoice-statistics/", name="translationalresearch_dashboard_invoicelevel")
     * @Template("AppTranslationalResearchBundle/Dashboard/dashboard.html.twig")
     */
    public function piStatisticsAction( Request $request ) {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE') ) {
            //ok
        } else {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
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
            'height' => $this->height,
            'width' =>  $this->width,
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
            $chartsArray = $this->addChart( $chartsArray, $piProjectCountTopArr, "Number of Project Requests per PI");
            ///////////// EOF top $piProjectCountArr //////////////

            //Total per PI
            $piTotalTopArr = $this->getTopArray($piTotalArr);
            $chartsArray = $this->addChart( $chartsArray, $piTotalTopArr, "Total($) of Project Requests per PI");

            //We likes to see which funded PI”s are using the TRP lab,
            // so we can try to capture a (Top Ten PI’s) and the percent of services they requested from TRP lab.
            $piRequestsTopArr = $this->getTopArray($piRequestsArr);
            $chartsArray = $this->addChart( $chartsArray, $piRequestsTopArr, "Number of Funded Work Requests per PI");

        }

        if( $routeName == "translationalresearch_dashboard_projectlevel" ) {
            $piTotalArr = array();
            $title = "Dashboard: Project Statistics";
            $layoutArray['title'] = "Number of Funded vs Un-Funded Project Requests";
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

            $labels = array('Number of Funded Projects','Number of Un-Funded Project Requests');
            $values = array($fundedCount,$unfundedCount);

            $chartDataArray['values'] = $values;
            $chartDataArray['labels'] = $labels;
            $chartDataArray['type'] = $type;
            $chartDataArray["textinfo"] = "value+percent";
            $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
            $chartDataArray['direction'] = 'clockwise';
            $dataArray[] = $chartDataArray;

            //$chartsArray['layout'] = $layoutArray;
            //$chartsArray['data'] = $dataArray;

            $chartsArray[] = array(
                'layout' => $layoutArray,
                'data' => $dataArray
            );
            $chartsArray[] = array('newline'=>true);
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
     * @Template("AppTranslationalResearchBundle/Dashboard/dashboard.html.twig")
     */
    public function compareStatisticsAction( Request $request )
    {
        if( $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE') ) {
            //ok
        } else {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $infos = array();

        //////////// Filter ////////////
        //default date range from today to 1 year back
        $params = array(
            //'startDate' => $today,
            //'endDate' => $today
            "projectSpecialty" => false,
            "compareType" => false,
            "showLimited" => true
        );
        $filterform = $this->createForm(FilterDashboardType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));
        $filterform->handleRequest($request);
        //////////// EOF Filter ////////////

        $title = "Dashboard: Comparison Statistics";

        $layoutArray = array(
            'height' => $this->height,
            'width' => $this->width,
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

        $chartsArray = $this->addChart( $chartsArray, $pisDataArr, "21. Number of PIs in AP/CP vs Hematopathology");
        ///////////////// EOF Pie charts of the number of PIs in Hemepath vs AP/CP /////////////////



        ///////////////// number of Hematopathology vs AP/CP project requests as a Pie chart /////////////////
        $projectsDataArr = array();
        $projectsDataArr['AP/CP Project Requests'] = count($apcpProjects);
        $projectsDataArr['Hematopathology Project Requests'] = count($hemaProjects);

        $chartsArray = $this->addChart( $chartsArray, $projectsDataArr, "22. Number of AP/CP vs Hematopathology Project Requests");
        ///////////////// EOF number of Hematopathology vs AP/CP project requests as a Pie chart /////////////////



        //3 bar graphs showing the number of project requests, work requests, invoices per month since
        // the beginning based on submission date: Total, Hematopatholgy, AP/CP
        /////////// number of project requests, work requests, invoices per month  ///////////

        $apcpResultStatArr = array();
        $hemaResultStatArr = array();

        //get startDate and add 1 month until the date is less than endDate
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $startDate->modify( 'first day of last month' );
        do {
            $startDateLabel = $startDate->format('M-Y');
            $thisEndDate = clone $startDate;
            $thisEndDate->modify( 'first day of next month' );
            //echo "StartDate=".$startDate->format("d-M-Y")."; EndDate=".$thisEndDate->format("d-M-Y")."<br>";
            $apcpProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyApcpObject),false);
            $hemaProjects = $this->getProjectsByFilter($startDate,$thisEndDate,array($specialtyHemaObject),false);
            $startDate->modify( 'first day of next month' );

            //echo "<br>";
            //echo "hemaProjects=".count($hemaProjects)." (".$startDateLabel.")<br>";

            $apcpResultStatArr = $this->getProjectRequestInvoiceChart($apcpProjects,$apcpResultStatArr,$startDateLabel);
            $hemaResultStatArr = $this->getProjectRequestInvoiceChart($hemaProjects,$hemaResultStatArr,$startDateLabel);

        } while( $startDate < $endDate );

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
        $chartsArray = $this->addStackedChart( $chartsArray, $combinedProjectsData, "23. Number of AP/CP vs Hematopathology Project Requests", "stack");

        //Requests
        $combinedRequestsData = array();
        $combinedRequestsData['AP/CP'] = $apcpRequestsData;
        $combinedRequestsData['Hematopathology'] = $hemaRequestsData;
        $chartsArray = $this->addStackedChart( $chartsArray, $combinedRequestsData, "24. Number of AP/CP vs Hematopathology Work Requests", "stack");

        //Invoices
        $combinedInvoicesData = array();
        $combinedInvoicesData['AP/CP'] = $apcpInvoicesData;
        $combinedInvoicesData['Hematopathology'] = $hemaInvoicesData;
        $chartsArray = $this->addStackedChart( $chartsArray, $combinedInvoicesData, "25. Number of AP/CP vs Hematopathology Invoices", "stack");

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
//     * @Template("AppTranslationalResearchBundle/Dashboard/pilevel.html.twig")
//     */
//    public function fundedLevelAction( Request $request ) {
//
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
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

        $repository = $em->getRepository('AppTranslationalResearchBundle:Project');
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
            if( $em->getRepository('AppUserdirectoryBundle:Institution')->isNodeUnderParentnode($parentInstitution,$institution) ) {
                return true;
            }
        }

        return false;
    }

    public function getRequestsByFilter($startDate, $endDate, $projectSpecialties, $addOneEndDay=true) {
        $em = $this->getDoctrine()->getManager();
        //$transresUtil = $this->container->get('transres_util');

        $repository = $em->getRepository('AppTranslationalResearchBundle:TransResRequest');
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

    public function getInvoicesByFilter($startDate, $endDate, $projectSpecialties, $addOneEndDay=true, $compareType='last invoice generation date') {
        $em = $this->getDoctrine()->getManager();
        //$transresUtil = $this->container->get('transres_util');

        $repository = $em->getRepository('AppTranslationalResearchBundle:Invoice');
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
}
