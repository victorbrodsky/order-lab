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
     * @Route("/pi-project-statistics/", name="translationalresearch_dashboard_project")
     * @Template("OlegTranslationalResearchBundle:Dashboard:dashboard.html.twig")
     */
    public function projectStatisticsAction( Request $request )
    {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->container->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $filterform = $this->getFilter();
        $filterform->handleRequest($request);

        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $projectSpecialty = $filterform['projectSpecialty']->getData();
        if( $projectSpecialty != 0 ) {
            $projectSpecialtyObject = $em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }

        $projects = $this->getProjectsByFilter($startDate,$endDate,$projectSpecialtyObjects);
        //echo "projects=".count($projects)."<br>";

        //1. Principle Investigators by Affiliation
        $piWcmPathologyCounter = 0;
        $piWcmCounter = 0;
        $piOtherCounter = 0;
        $mapper = array(
            'prefix' => 'Oleg',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution'
        );
        $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
        $wcmPathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
            "Pathology and Laboratory Medicine",
            $wcmc,
            $mapper
        );
        ////////////////////

        //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
        $piProjectCountArr = array();

        // 3. Total number of Funded Projects per PI (Top 10)
        $piFundedProjectCountArr = array();
        //4. Total number of Non-Funded Projects per PI (Top 10)
        $piUnFundedProjectCountArr = array();

        foreach($projects as $project) {

            $fundingNumber = $project->getFundedAccountNumber();

            //1. Principle Investigators by Affiliation
            //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
            $pis = $project->getPrincipalInvestigators();
            foreach ($pis as $pi) {
                $userName = $pi->getUsernameOptimal();

                //1. Principle Investigators by Affiliation
                if( $this->isUserBelongsToInstitution($pi,$wcmPathology) ) {
                    //WCM Pathology Faculty - WCM Department of Pathology and Laboratory Medicine in any Title’s department field
                    $piWcmPathologyCounter++;
                } elseif ( $this->isUserBelongsToInstitution($pi,$wcmc) ) {
                    //WCM Other Departmental Faculty - WCM institution
                    $piWcmCounter++;
                } else {
                    //Other Institutions
                    $piOtherCounter++;
                }

                //2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED)
                if (isset($piProjectCountArr[$userName])) {
                    $count = $piProjectCountArr[$userName] + 1;
                } else {
                    $count = 1;
                }
                $piProjectCountArr[$userName] = $count;

                /////////// 3,4 Total number of Funded/Un-Funded Projects per PI (Top 10) ////////////////
                if( $fundingNumber ) {
                    // 3. Total number of Funded Projects per PI (Top 10)
                    if (isset($piFundedProjectCountArr[$userName])) {
                        $count = $piFundedProjectCountArr[$userName] + 1;
                    } else {
                        $count = 1;
                    }
                    $piFundedProjectCountArr[$userName] = $count;
                } else {
                    //4. Total number of Non-Funded Projects per PI (Top 10)
                    if (isset($piUnFundedProjectCountArr[$userName])) {
                        $count = $piUnFundedProjectCountArr[$userName] + 1;
                    } else {
                        $count = 1;
                    }
                    $piUnFundedProjectCountArr[$userName] = $count;
                }
                /////////// EOF 3,4 Total number of Funded/Un-Funded Projects per PI (Top 10) ////////////////



            }
        } //foreach $projects

        $chartsArray = array();

        ///////////// 1. Principle Investigators by Affiliation ///////////////////
        $dataArray = array();
        $chartDataArray = array();
        $type = 'pie';

        $layoutArray = array(
            'height' => 600,
            'width' =>  600,
            'title' => "Principle Investigators by Affiliation"
        );

        $labels = array(
            'WCM Pathology Faculty'." ".$piWcmPathologyCounter,
            'WCM Other Departmental Faculty'." ".$piWcmCounter,
            'Other Institutions'." ".$piOtherCounter
        );
        $values = array($piWcmPathologyCounter,$piWcmCounter,$piOtherCounter);

        $chartDataArray['values'] = $values;
        $chartDataArray['labels'] = $labels;
        $chartDataArray['type'] = $type;
        $chartDataArray["textinfo"] = "value+percent";
        $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
        $dataArray[] = $chartDataArray;

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );
        ///////////// EOF 1. Principle Investigators by Affiliation ///////////////////

        ///////////// 2. Total number of projects (XXX) per PI (Top 5/10) (APPROVED & CLOSED) - $piProjectCountArr //////////////
        $piProjectCountTopArr = $this->getTopArray($piProjectCountArr);
        //Projects per PI
        $chartsArray = $this->addChart( $chartsArray, $piProjectCountTopArr, "Total number of projects per PI (Top 10)","pie",null," ");
        ///////////// EOF top $piProjectCountArr //////////////

        /////////// 3,4 Total number of Funded/Un-Funded Projects per PI (Top 10) ////////////////
        //3. Funded Projects per PI
        $piFundedProjectCountTopArr = $this->getTopArray($piFundedProjectCountArr);
        $chartsArray = $this->addChart( $chartsArray, $piFundedProjectCountTopArr, "Total number of Funded Projects per PI (Top 10)","pie",null," ");
        //4. Un-Funded Projects per PI
        $piUnFundedProjectCountTopArr = $this->getTopArray($piUnFundedProjectCountArr);
        //Funded Projects per PI
        $chartsArray = $this->addChart( $chartsArray, $piUnFundedProjectCountTopArr, "Total number of Non-Funded Projects per PI (Top 10)","pie",null," ");
        /////////// EOF 3,4 Total number of Funded/Un-Funded Projects per PI (Top 10) ////////////////

        return array(
            'title' => "PI/PROJECT STATISTICS (APPROVED or CLOSED)".", ".count($projects)." Total Matching Projects",
            'filterform' => $filterform->createView(),
            'chartsArray' => $chartsArray
        );
    }

    /**
     * @Route("/work-request-statistics/", name="translationalresearch_dashboard_request")
     * @Template("OlegTranslationalResearchBundle:Dashboard:dashboard.html.twig")
     */
    public function requestStatisticsAction( Request $request )
    {

        if ($this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->container->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $filterform = $this->getFilter();
        $filterform->handleRequest($request);

        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $projectSpecialty = $filterform['projectSpecialty']->getData();
        if( $projectSpecialty != 0 ) {
            $projectSpecialtyObject = $em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }

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
            $projectIndex = $project->getOid();
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

            //5. Total Number of Work Requests (XXXX) by Funding Source
            if( $transRequest->getFundedAccountNumber() ) {
                $fundedRequestCount++;
            } else {
                $notFundedRequestCount++;
            }
            //////////////////////

            //6. Total number of Requests per Project (Top 10)
            if (isset($requestPerProjectArr[$projectIndex])) {
                $count = $requestPerProjectArr[$projectIndex] + 1;
            } else {
                $count = 1;
            }
            $requestPerProjectArr[$projectIndex] = $count;
            //////////////////////

            //7,8. Total number of Requests per Funded/Un-Funded Project (Top 10)
            if( $transRequest->getFundedAccountNumber() ) {
                //7. Total number of Requests per Funded Project (Top 10)
                if (isset($fundedRequestPerProjectArr[$projectIndex])) {
                    $count = $fundedRequestPerProjectArr[$projectIndex] + 1;
                } else {
                    $count = 1;
                }
                $fundedRequestPerProjectArr[$projectIndex] = $count;
            } else {
                //8. Total number of Requests per Non_Funded Project (Top 10)
                if (isset($unFundedRequestPerProjectArr[$projectIndex])) {
                    $count = $unFundedRequestPerProjectArr[$projectIndex] + 1;
                } else {
                    $count = 1;
                }
                $unFundedRequestPerProjectArr[$projectIndex] = $count;
            }
            //////////////////////

            //9. TRP Service Productivity by Category Types (Top 10)
            //9- Group work requests Based on what is ordered (“Category”) & sorted by Total Quantity (1 work request ordering 1000 slides counts as 1000)
            foreach($transRequest->getProducts() as $product) {
                $category = $product->getCategory();
                if( $category ) {
                    $categoryIndex = $category->getName();
                    //9. TRP Service Productivity by Category Types (Top 10)
                    if (isset($quantityCountByCategoryArr[$categoryIndex])) {
                        $count = $quantityCountByCategoryArr[$categoryIndex] + 1;
                    } else {
                        $count = 1;
                    }
                    $quantityCountByCategoryArr[$categoryIndex] = $count;
                    /////////////

                    //10,11. TRP Service Productivity for Funded/Not-Funded Projects (Top 10)
                    if( $transRequest->getFundedAccountNumber() ) {
                        //10. TRP Service Productivity for Funded Projects (Top 10)
                        if (isset($fundedQuantityCountByCategoryArr[$categoryIndex])) {
                            $count = $fundedQuantityCountByCategoryArr[$categoryIndex] + 1;
                        } else {
                            $count = 1;
                        }
                        $fundedQuantityCountByCategoryArr[$categoryIndex] = $count;
                    } else {
                        //11. TRP Service Productivity for non-Funded projects (Top 10)
                        if (isset($unFundedQuantityCountByCategoryArr[$categoryIndex])) {
                            $count = $unFundedQuantityCountByCategoryArr[$categoryIndex] + 1;
                        } else {
                            $count = 1;
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
            'height' => 600,
            'width' =>  800,
            'title' => "Total Number of Work Requests by Funding Source"
        );

        $labels = array('Funded'." ".$fundedRequestCount,'Non-Funded'." ".$notFundedRequestCount);
        $values = array($fundedRequestCount,$notFundedRequestCount);

        $chartDataArray['values'] = $values;
        $chartDataArray['labels'] = $labels;
        $chartDataArray['type'] = $type;
        $chartDataArray["textinfo"] = "value+percent";
        $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
        $dataArray[] = $chartDataArray;

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );
        ////////////////////

        //6. Total number of Requests per Project (Top 10)
        $requestPerProjectTopArr = $this->getTopArray($requestPerProjectArr);
        $layoutArray = array(
            'height' => 600,
            'width' => 1200,
        );
        $chartsArray = $this->addChart( $chartsArray, $requestPerProjectTopArr, "Total number of Requests per Project (Top 10)",'pie',$layoutArray," ");
        ////////////////////

        //7,8. Total number of Requests per Funded/Un-Funded Project (Top 10)
        $layoutArray = array(
            'height' => 600,
            'width' => 1200,
        );
        //7. Total number of Requests per Funded Project (Top 10)
        $fundedRequestPerProjectTopArr = $this->getTopArray($fundedRequestPerProjectArr);
        $chartsArray = $this->addChart( $chartsArray, $fundedRequestPerProjectTopArr, "Total number of Requests per Funded Project (Top 10)",'pie',$layoutArray," ");
        //8. Total number of Requests per Non_Funded Project (Top 10)
        $unFundedRequestPerProjectTopArr = $this->getTopArray($unFundedRequestPerProjectArr);
        $chartsArray = $this->addChart( $chartsArray, $unFundedRequestPerProjectTopArr, "Total number of Requests per Non-Funded Project (Top 10)",'pie',$layoutArray," ");
        ////////////////////

        //9. TRP Service Productivity by Category Types (Top 10)
        //9- Group work requests Based on what is ordered (“Category”) & sorted by Total Quantity (1 work request ordering 1000 slides counts as 1000)
        $quantityCountByCategoryTopArr = $this->getTopArray($quantityCountByCategoryArr);
        $layoutArray = array(
            'height' => 600,
            'width' => 1200,
        );
        $chartsArray = $this->addChart( $chartsArray, $quantityCountByCategoryTopArr, "TRP Service Productivity by Category Types (Top 10)",'pie',$layoutArray," ");
        ///////////////////////////

        //10,11. TRP Service Productivity for Funded/Not-Funded Projects (Top 10)
        $layoutArray = array(
            'height' => 600,
            'width' => 1200,
        );
        //10. TRP Service Productivity for Funded Projects (Top 10)
        $fundedQuantityCountByCategoryTopArr = $this->getTopArray($fundedQuantityCountByCategoryArr);
        $chartsArray = $this->addChart( $chartsArray, $fundedQuantityCountByCategoryTopArr, "TRP Service Productivity for Funded Projects (Top 10)",'pie',$layoutArray," ");
        //11. TRP Service Productivity for Non-Funded Projects (Top 10)
        $unFundedQuantityCountByCategoryTopArr = $this->getTopArray($unFundedQuantityCountByCategoryArr);
        $chartsArray = $this->addChart( $chartsArray, $unFundedQuantityCountByCategoryTopArr, "TRP Service Productivity for Non-Funded Projects (Top 10)",'pie',$layoutArray," ");
        ////////////////////////////////

        return array(
            'title' => "WORK REQUESTS STATISTICS".", ".count($requests)." Total Matching Requests",
            'filterform' => $filterform->createView(),
            'chartsArray' => $chartsArray
        );
    }

    /**
     * @Route("/financial-statistics/", name="translationalresearch_dashboard_financial")
     * @Template("OlegTranslationalResearchBundle:Dashboard:dashboard.html.twig")
     */
    public function financialStatisticsAction( Request $request )
    {

        if ($this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE')
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->container->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $filterform = $this->getFilter(true);
        $filterform->handleRequest($request);

        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();

        $compareType = $filterform['compareType']->getData();
        $compareType = str_replace("-"," ",$compareType);

        $projectSpecialty = $filterform['projectSpecialty']->getData();
        if ($projectSpecialty != 0) {
            $projectSpecialtyObject = $em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->find($projectSpecialty);
            $projectSpecialtyObjects[] = $projectSpecialtyObject;
        }



        //Get data from the invoice perspective
        //12- Show radio buttons allowing work request submission date vs last invoice generation date vs date when status changed to “paid in full”;
        // Source of dollar amount is “Total Fees”
        //12. Total Fees by Work Requests (Total $400K)
        //13. Total Fees per Funded Project (Top 10)
        //14. Total Fees per non-funded Project (Top 10)
        //15. Total Fees per Investigator (Top 10)
        //16. Total Fees per Investigator (Funded) (Top 10)
        //17. Total Fees per Investigator (non-Funded) (Top 10)
        //18. Generated Invoices by Status from Funded Projects (Total invoiced $152K)
        //19. Generated Invoices by Status per Funded Project (Top 10)
        //20. Generated Invoices by Status per PI (Top 10)

        $invoices = $this->getInvoicesByFilter($startDate, $endDate, $projectSpecialtyObjects, true, $compareType);

        $totalInvoicesFees = 0;
        $fundedTotalFees = 0;
        $unFundedTotalFees = 0;

        $fundedTotalFeesByRequestArr = array();
        $unFundedTotalFeesByRequestArr = array();

        $totalFeesByInvestigatorArr = array();
        $fundedTotalFeesByInvestigatorArr = array();
        $unFundedTotalFeesByInvestigatorArr = array();

        $paidInvoices = 0;
        $unpaidInvoices = 0;
        $totalFundedPaidFee = 0;
        $totalFundedUnPaidFee = 0;
        $totalFundedFees = 0;

        $invoicesByProjectArr = array();
        $invoicesByPiArr = array();
        $invoicesFeesByProjectArr = array();
        $invoicesFeesByPiArr = array();

        foreach($invoices as $invoice) {

            $transRequest = $invoice->getTransresRequest();

            $project = $transRequest->getProject();
            $projectIndex = $project->getOid();
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

            $investigator = $invoice->getPrincipalInvestigator();
            if( $investigator ) {
                $investigatorIndex = $investigator->getUsernameOptimal();
            } else {
                $submitter = $invoice->getSubmitter();
                $investigatorIndex = $submitter->getUsernameOptimal();
            }

            $totalInvoiceFee = intval($invoice->getTotal());
            $totalInvoicesFees = $totalInvoicesFees + $totalInvoiceFee;

            //12. Total Fees by Work Requests (Total $400K)
            ////////////////////

            //13. Total Fees per Funded Project (Top 10)
            //14. Total Fees per non-funded Project (Top 10)
            if( $invoice->getFundedAccountNumber() ) {
                //12. Total Fees by Work Requests (Total $400K)
                $fundedTotalFees = $fundedTotalFees + $totalInvoiceFee;
                //13. Total Fees per Funded Project (Top 10)
                if (isset($fundedTotalFeesByRequestArr[$projectIndex])) {
                    $totalFee = $fundedTotalFeesByRequestArr[$projectIndex] + $totalInvoiceFee;
                } else {
                    $totalFee = $totalInvoiceFee;
                }
                $fundedTotalFeesByRequestArr[$projectIndex] = $totalFee;
            } else {
                //12. Total Fees by Work Requests (Total $400K)
                $unFundedTotalFees = $unFundedTotalFees + $totalInvoiceFee;
                //14. Total Fees per non-funded Project (Top 10)
                if (isset($unFundedTotalFeesByRequestArr[$projectIndex])) {
                    $totalFee = $unFundedTotalFeesByRequestArr[$projectIndex] + $totalInvoiceFee;
                } else {
                    $totalFee = $totalInvoiceFee;
                }
                $unFundedTotalFeesByRequestArr[$projectIndex] = $totalFee;
            }
            /////////////////////

            //15. Total Fees per Investigator (Top 10)
            if (isset($totalFeesByInvestigatorArr[$investigatorIndex])) {
                $totalFee = $totalFeesByInvestigatorArr[$investigatorIndex] + $totalInvoiceFee;
            } else {
                $totalFee = $totalInvoiceFee;
            }
            $totalFeesByInvestigatorArr[$investigatorIndex] = $totalFee;
            /////////////////////////////

            //16. Total Fees per Investigator (Funded) (Top 10)
            //17. Total Fees per Investigator (non-Funded) (Top 10)
            if( $invoice->getFundedAccountNumber() ) {
                //16. Total Fees per Investigator (Funded) (Top 10)
                if (isset($fundedTotalFeesByInvestigatorArr[$investigatorIndex])) {
                    $totalFee = $fundedTotalFeesByInvestigatorArr[$investigatorIndex] + $totalInvoiceFee;
                } else {
                    $totalFee = $totalInvoiceFee;
                }
                $fundedTotalFeesByInvestigatorArr[$investigatorIndex] = $totalFee;
            } else {
                //17. Total Fees per Investigator (non-Funded) (Top 10)
                if (isset($unFundedTotalFeesByInvestigatorArr[$projectIndex])) {
                    $totalFee = $unFundedTotalFeesByInvestigatorArr[$projectIndex] + $totalInvoiceFee;
                } else {
                    $totalFee = $totalInvoiceFee;
                }
                $unFundedTotalFeesByInvestigatorArr[$projectIndex] = $totalFee;
            }
            ////////////////////////////////////////

            //18. Generated Invoices by Status from Funded Projects (Total invoiced $152K)
            if( $invoice->getFundedAccountNumber() ) {
                if ($invoice->getStatus() == "Paid in Full") {
                    $paidInvoices++;
                    $totalFundedPaidFee = $totalFundedPaidFee + $totalInvoiceFee;
                } else {
                    $unpaidInvoices++;
                    $totalFundedUnPaidFee = $totalFundedUnPaidFee + $totalInvoiceFee;
                }
                $totalFundedFees = $totalFundedFees + $totalInvoiceFee;
            }
            //////////////////////////////////////////////

            //19. Generated Invoices by Status per Funded Project (Top 10)
            //20. Generated Invoices by Status per PI (Top 10)
            if( $invoice->getFundedAccountNumber() ) {
                //19. Generated Invoices by Status per Funded Project (Top 10)
                if (isset($invoicesByProjectArr[$projectIndex])) {
                    $count = $invoicesByProjectArr[$projectIndex] + 1;
                } else {
                    $count = 1;
                }
                $invoicesByProjectArr[$projectIndex] = $count;
                //fees
                if (isset($invoicesFeesByProjectArr[$projectIndex])) {
                    $totalFee = $invoicesFeesByProjectArr[$projectIndex] + $totalInvoiceFee;
                } else {
                    $totalFee = $totalInvoiceFee;
                }
                $invoicesFeesByProjectArr[$projectIndex] = $totalFee;

                //20. Generated Invoices by Status per PI (Top 10)
                if (isset($invoicesByPiArr[$investigatorIndex])) {
                    $count = $invoicesByPiArr[$investigatorIndex] + 1;
                } else {
                    $count = 1;
                }
                $invoicesByPiArr[$investigatorIndex] = $count;
                //fees
                if (isset($invoicesFeesByPiArr[$investigatorIndex])) {
                    $totalFee = $invoicesFeesByPiArr[$investigatorIndex] + $totalInvoiceFee;
                } else {
                    $totalFee = $totalInvoiceFee;
                }
                $invoicesFeesByPiArr[$investigatorIndex] = $totalFee;
            }
            /////////////////////////////////////////

        } //foreach invoices

        $chartsArray = array();

        //12. Total Fees by Work Requests (Total $400K)
        $dataArray = array();
        $chartDataArray = array();
        $type = 'pie';

        $layoutArray = array(
            'height' => 600,
            'width' =>  600,
            'title' => "Total Fees by Work Requests (Total $".$totalInvoicesFees.")"
        );

        $labels = array('Funded $'.$fundedTotalFees,'Non-Funded $'.$unFundedTotalFees);
        $values = array($fundedTotalFees,$unFundedTotalFees);

        $chartDataArray['values'] = $values;
        $chartDataArray['labels'] = $labels;
        $chartDataArray['type'] = $type;
        $chartDataArray["textinfo"] = "value+percent";
        $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
        $dataArray[] = $chartDataArray;

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );
        /////////////////////

        $chartsArray[] = array('newline'=>true);

        //13. Total Fees per Funded Project (Top 10)
        //14. Total Fees per non-funded Project (Top 10)
        $layoutArray = array(
            'height' => 600,
            'width' => 800,
        );
        //13. Total Fees per Funded Project (Top 10)
        $fundedTotalFeesByRequestTopArr = $this->getTopArray($fundedTotalFeesByRequestArr);
        $chartsArray = $this->addChart( $chartsArray, $fundedTotalFeesByRequestTopArr, "Total Fees per Funded Project (Top 10)",'pie',$layoutArray,"$");
        //14. Total Fees per non-funded Project (Top 10)
        $unFundedTotalFeesByRequestTopArr = $this->getTopArray($unFundedTotalFeesByRequestArr);
        $chartsArray = $this->addChart( $chartsArray, $unFundedTotalFeesByRequestTopArr, "Total Fees per Non-Funded Project (Top 10)",'pie',$layoutArray,"$");
        ////////////////////////////////

        //15. Total Fees per Investigator (Top 10)
        $layoutArray = array(
            'height' => 600,
            'width' => 800,
        );
        $totalFeesByInvestigatorTopArr = $this->getTopArray($totalFeesByInvestigatorArr);
        $chartsArray = $this->addChart( $chartsArray, $totalFeesByInvestigatorTopArr, "Total Fees per Investigator (Top 10)",'pie',$layoutArray,"$");
        ////////////////////////////

        $chartsArray[] = array('newline'=>true);

        //16. Total Fees per Investigator (Funded) (Top 10)
        //17. Total Fees per Investigator (non-Funded) (Top 10)
        $layoutArray = array(
            'height' => 600,
            'width' => 800,
        );
        //16. Total Fees per Investigator (Funded) (Top 10)
        $fundedTotalFeesByInvestigatorTopArr = $this->getTopArray($fundedTotalFeesByInvestigatorArr);
        $chartsArray = $this->addChart( $chartsArray, $fundedTotalFeesByInvestigatorTopArr, "Total Fees per Investigator (Funded) (Top 10)",'pie',$layoutArray,"$");
        //17. Total Fees per Investigator (non-Funded) (Top 10)
        $unFundedTotalFeesByInvestigatorTopArr = $this->getTopArray($unFundedTotalFeesByInvestigatorArr);
        $chartsArray = $this->addChart( $chartsArray, $unFundedTotalFeesByInvestigatorTopArr, "Total Fees per Investigator (Non-Funded) (Top 10)",'pie',$layoutArray,"$");
        ////////////////////////////////////////

        //18. Generated Invoices by Status from Funded Projects (Total invoiced $152K)
        $dataArray = array();
        $chartDataArray = array();
        $type = 'pie';

        $layoutArray = array(
            'height' => 600,
            'width' =>  600,
            'title' => "Generated Invoices by Status from Funded Projects (Total invoiced $".$totalFundedFees.")"
        );

        $labels = array('Paid'.' $'.$totalFundedPaidFee,'Unpaid (Due)'.' $'.$totalFundedUnPaidFee);
        $values = array($paidInvoices,$unpaidInvoices);

        $chartDataArray['values'] = $values;
        $chartDataArray['labels'] = $labels;
        $chartDataArray['type'] = $type;
        $chartDataArray["textinfo"] = "value+percent";
        $chartDataArray["outsidetextfont"] = array('size'=>1,'color'=>'white');
        $dataArray[] = $chartDataArray;

        $chartsArray[] = array(
            'layout' => $layoutArray,
            'data' => $dataArray
        );
        /////////////////////////////

        $chartsArray[] = array('newline'=>true);

        //19. Generated Invoices by Status per Funded Project (Top 10)
        //20. Generated Invoices by Status per PI (Top 10)
        $layoutArray = array(
            'height' => 600,
            'width' => 800,
        );
        //19. Generated Invoices by Status per Funded Project (Top 10)
        $invoicesByProjectTopArr = $this->getTopArray($invoicesByProjectArr); // addChart(
        $invoicesFeesByProjectTopArr = $this->getTopArray($invoicesFeesByProjectArr);
        //merge two to attach fees to label
        $invoicesByProjectTopArr = $this->attachSecondValueToFirstLabel($invoicesByProjectTopArr,$invoicesFeesByProjectTopArr,"$");
        $chartsArray = $this->addChart( $chartsArray, $invoicesByProjectTopArr, "Generated Invoices by Status per Funded Project (Top 10)",'pie',$layoutArray);

        //20. Generated Invoices by Status per PI (Top 10)
        $invoicesByPiTopArr = $this->getTopArray($invoicesByPiArr);
        $invoicesFeesByPiTopArr = $this->getTopArray($invoicesFeesByPiArr);
        //merge two to attach fees to label
        $invoicesByPiTopArr = $this->attachSecondValueToFirstLabel($invoicesByPiTopArr,$invoicesFeesByPiTopArr,"$");
        $chartsArray = $this->addChart( $chartsArray, $invoicesByPiTopArr, "Generated Invoices by Status per PI (Top 10)",'pie',$layoutArray);
        //////////////////////////////////////////////

        return array(
            'title' => "FINANCIAL STATISTICS ($)" . ", " . count($invoices) . " Total Matching Invoices",
            'filterform' => $filterform->createView(),
            'chartsArray' => $chartsArray
        );
    }

    public function getFilter( $withCompareType=false ) {
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
            "compareType" => false
        );

        if( $withCompareType ) {
            $params["compareType"] = true;
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
     * @Template("OlegTranslationalResearchBundle:Dashboard:dashboard.html.twig")
     */
    public function piStatisticsAction( Request $request ) {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE') ) {
            //ok
        } else {
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
        if( $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE') ) {
            //ok
        } else {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        //$transresRequestUtil = $this->container->get('transres_request_util');
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

        $chartsArray = $this->addChart( $chartsArray, $pisDataArr, "Number of PIs in AP/CP vs Hematopathology");
        ///////////////// EOF Pie charts of the number of PIs in Hemepath vs AP/CP /////////////////



        ///////////////// number of Hematopathology vs AP/CP project requests as a Pie chart /////////////////
        $projectsDataArr = array();
        $projectsDataArr['AP/CP Project Requests'] = count($apcpProjects);
        $projectsDataArr['Hematopathology Project Requests'] = count($hemaProjects);

        $chartsArray = $this->addChart( $chartsArray, $projectsDataArr, "Number of AP/CP vs Hematopathology Project Requests");
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
        $chartsArray = $this->addStackedChart( $chartsArray, $combinedProjectsData, "Number of AP/CP vs Hematopathology Project Requests", "stack");

        //Requests
        $combinedRequestsData = array();
        $combinedRequestsData['AP/CP'] = $apcpRequestsData;
        $combinedRequestsData['Hematopathology'] = $hemaRequestsData;
        $chartsArray = $this->addStackedChart( $chartsArray, $combinedRequestsData, "Number of AP/CP vs Hematopathology Work Requests", "stack");

        //Invoices
        $combinedInvoicesData = array();
        $combinedInvoicesData['AP/CP'] = $apcpInvoicesData;
        $combinedInvoicesData['Hematopathology'] = $hemaInvoicesData;
        $chartsArray = $this->addStackedChart( $chartsArray, $combinedInvoicesData, "Number of AP/CP vs Hematopathology Invoices", "stack");

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


    //select top 10, BUT make sure the other PIs are still shown as "Other"
    public function getTopArray($piProjectCountArr, $maxLen=80) {
        arsort($piProjectCountArr);
        $limit = 10;
        $count = 1;
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

        if( $maxLen ) {
            $piProjectCountTopShortArr = array();
            foreach($piProjectCountTopArr as $index=>$value) {
                if( strlen($index) > $maxLen ) {
                    $index = substr($index, 0, $maxLen) . '...';
                }
                $piProjectCountTopShortArr[$index] = $value;
            }
            return $piProjectCountTopShortArr;
        }

        return $piProjectCountTopArr;
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
                'height' => 600,
                'width' => 600,
            );
        }

        if( $title ) {
            $layoutArray['title'] = $title;
        }

        foreach( $dataArr as $label => $value ) {
            if( $type == "bar" || $value ) {
                if( $valuePrefixLabel ) {
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
            $startDateCriterion = 'request.createDate >= :startDate';
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

            $endDateCriterion = 'request.createDate <= :endDate';
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
