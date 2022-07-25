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

namespace App\VacReqBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\UserdirectoryBundle\Util\LargeFileDownloader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//vacreq site

class DefaultController extends OrderAbstractController
{

    /**
     * @Route("/about", name="vacreq_about_page")
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function aboutAction( Request $request ) {

        //testing
//        $em = $this->getDoctrine()->getManager();
//        $roleApprover = "ROLE_VACREQ_APPROVER_BROOKLYNMETHODIST";
//        $approvers = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleApprover,"infos.lastName",false);
//        echo "approvers=".count($approvers)."<br>";
//        $roleSubmitter = "ROLE_VACREQ_SUBMITTER_BROOKLYNMETHODIST";
//        $submitters = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleSubmitter,"infos.lastName",false);
//        echo "submitters=".count($submitters)."<br>";
//        exit('111');


//        $floatingDays = array(
//            array("06/29/2019", "2018-2019"),
//            array("07/29/2019", "2019-2020"),
//            array("06/29/2020", "2019-2020"),
//            array("06/29/2021", "2020-2021"),
//            array("07/01/2021", "2021-2022"),
//            array("02/17/2022", "2021-2022"),
//            array("06/29/2022", "2021-2022"),
//            array("06/30/2022", "2021-2022"),
//            array("08/29/2022", "2022-2023"),
//            array("06/29/2023", "2022-2023"),
//            array("07/01/2023", "2023-2024"),
//            array("06/25/2024", "2023-2024"),
//            array("07/01/2024", "2024-2025"),
//        );
//
//        foreach($floatingDays as $floatingDayArr) {
//            $floatingDay = $floatingDayArr[0];
//            $expectedRes = $floatingDayArr[1];
//
//            $floatingDayDate = \DateTime::createfromformat('m/d/Y',$floatingDay);
//
//            $vacreqUtil = $this->container->get('vacreq_util');
//            $yearRangeStr = $vacreqUtil->getAcademicYearBySingleDate($floatingDayDate);
//            echo "yearRangeStr: $floatingDay => $yearRangeStr == $expectedRes ";
//            if( $yearRangeStr == $expectedRes ) {
//                echo "OK <br>";
//            } else {
//                echo "NOTOK <br>";
//            }
//        }
//        exit('111');

//        $vacreqUtil = $this->container->get('vacreq_util');
//        $em = $this->getDoctrine()->getManager();
//        $user = $em->getRepository('AppUserdirectoryBundle:User')->find(375);
//        $groups = "";
//        $groupParams = array();
//        //$groupParams['statusArr'] = array('default','user-added');
//        $groupParams['asObject'] = true;
//        $groupParams['asUser'] = true;
//        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
//        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);
//        dump($organizationalInstitutions);
//        foreach($organizationalInstitutions as $organizationalInstitution) {
//            if( $groups ) {
//                $groups = $groups . ";";
//            }
//            $groups = $groups . $organizationalInstitution->getShortestName();
//        }
//        exit($user.': groups='.$groups);

        //Total Number of Vacation Requests
//        $yearRangeStr = '2020-2021';
//        $vacationRequests = $vacreqUtil->getRequestsByUserYears($user,$yearRangeStr,'vacation');
//        $businessRequests = $vacreqUtil->getRequestsByUserYears($user,$yearRangeStr,'business');
//        $totalCount = count($vacationRequests) + count($businessRequests);
//        exit($user.': totalCount='.$totalCount);

        //use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
        //TokenStorageInterface $tokenStorage
        //$tokenStorage->setToken(null); //testing
        //$sitename = 'vacreq';
        //return $this->redirect($this->generateUrl($sitename . '_logout'));
        
        return array('sitename'=>$this->getParameter('vacreq.sitename'));
    }

//    /**
//     * @Route("/", name="vacreq_home")
//     * @Template("AppVacReqBundle/Request/index.html.twig", methods={"GET"})
//     */
//    public function indexAction()
//    {
//        if( false == $this->isGranted('ROLE_VACREQ_USER') ) {
//            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//
//        $vacReqRequests = $em->getRepository('AppVacReqBundle:VacReqRequest')->findAll();
//
//        return array(
//            'vacReqRequests' => $vacReqRequests
//        );
//    }

    /**
     * @Route("/help", name="vacreq_help_page")
     * @Template("AppVacReqBundle/Default/help.html.twig")
     */
    public function helpAction( Request $request ) {

        $title = "Help";
        $filename = "floating-day-request.pdf";

        //$bundleFileName = '@AppTranslationalResearchBundle/Resources/public/images/'.$filename;
        $bundleFileName = "orderassets\\AppVacReqBundle\\help\\".$filename;

        return $this->viewDiskFileMethod($filename,$bundleFileName);


        if(0) {
            return array(
                'sitename' => $this->getParameter('vacreq.sitename'),
                'title' => $title,
                'bundleFileName' => $bundleFileName,
                'fileName' => $filename,
            );
        }
        if(0) {
            $size = null;//$document->getSize();

            $downloader = new LargeFileDownloader();
            $downloader->downloadLargeFile($bundleFileName, $filename, $size);

            exit;
        }
    }
    public function viewDiskFileMethod($filename,$bundleFileName) {

        if( false == $this->isGranted('IS_AUTHENTICATED_FULLY') ){
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $originalname = null;
        $size = null;
        $viewType = null;

        $response = new Response();

        $downloader = new LargeFileDownloader();
        $downloader->downloadLargeFile($bundleFileName, $filename, $size, true, "view", $viewType);
        exit;

//        if( $document ) {
//
//            //event log
//            //if( $viewType != 'snapshot' ) {
//            if( strpos((string)$viewType, 'snapshot') === false ) {
//                $user = $this->getUser();
//                $eventDescription = "Document has been viewed by " . $user;
//                $this->setDownloadEventLog($request, $document, $user, $sitename, $eventtype, $eventDescription);
//            }
//
//            if( strpos((string)$viewType, 'snapshot') === false ) {
//                $originalname = $document->getOriginalnameClean();
//                $abspath = $document->getAbsoluteUploadFullPath();
//                $size = $document->getSize();
//                //echo "not snapshot abspath=$abspath <br>";
//                //exit('exit notsnapshot');
//            } else {
//
//                $viewTypeArr = explode("-", $viewType);
//                if (count($viewTypeArr) > 1) {
//                    $resize = $viewTypeArr[1];
//                } else {
//                    $resize = null;
//                }
//                //$resize = null; //testing: disable resize images
//
//                //TODO: resize thumbnails http://127.0.0.1/order/fellowship-applications/generate-thumbnails
//                //get small thumbnail - i.e. used for the fellowship application list //small-18sec, original-25sec
//                if( $resize == "small" ) {
//                    $originalname = $document->getOriginalnameClean();
//                    //$size = $document->getSize();
//                    //$size = $document->getSizeBySize($resize);
//                    //$abspath = $document->getAbsoluteUploadFullPath($resize,true);
//                    $abspath = $document->getFileSystemPath($resize);
//                    //$abspath = "http://127.0.0.1/order/Uploaded/fellapp/FellowshipApplicantUploads/small-1557157978ID1J9qjngqM1Bt_PZedHfJtX1S_sALg8YS-.jpg";
//                    if( file_exists($abspath) ) {
//                        //echo "The file $abspath exists <br>";
//                        $abspath = $document->getAbsoluteUploadFullPath($resize,true);
//                    } else {
//                        //echo "The file $abspath does not exists <br>";
//                        //try to re-generate thumbnails for jpg and jpeg
//                        if( strpos((string)$originalname, '.jpg') !== false || strpos((string)$originalname, '.jpeg') !== false ) {
//                            $userServiceUtil = $this->container->get('user_service_utility');
//                            $destRes = $userServiceUtil->generateTwoThumbnails($document);
//                            if( $destRes ) {
//                                $logger = $this->container->get('logger');
//                                $logger->notice("Try to re-generate small thumbnail for $originalname. destRes=" . $destRes);
//                            }
//                        }
//
//                        $abspath = $document->getAbsoluteUploadFullPath($resize);
//                    }
//                    $size = $document->getSizeBySize($resize);
//                    //exit('exit small: '.$abspath."; size=".$size);
//                }
//                //get small thumbnail - i.e. used for the fellowship application view
//                elseif( $resize == "medium" ) {
//                    $originalname = $document->getOriginalnameClean();
//                    //$size = $document->getSize();
//                    //$size = $document->getSizeBySize($resize);
//                    //$abspath = $document->getAbsoluteUploadFullPath($resize,true);
//                    $abspath = $document->getFileSystemPath($resize);
//                    if( file_exists($abspath) ) {
//                        //echo "The file $abspath exists <br>";
//                        $abspath = $document->getAbsoluteUploadFullPath($resize,true);
//                    } else {
//                        //echo "The file $abspath does not exists <br>";
//                        //try to re-generate thumbnails
//                        if( strpos((string)$originalname, '.jpg') !== false || strpos((string)$originalname, '.jpeg') !== false ) {
//                            $userServiceUtil = $this->container->get('user_service_utility');
//                            $destRes = $userServiceUtil->generateTwoThumbnails($document);
//                            if( $destRes ) {
//                                $logger = $this->container->get('logger');
//                                $logger->notice("Try to re-generate medium thumbnail for $originalname. destRes=" . $destRes);
//                            }
//                        }
//
//                        $abspath = $document->getAbsoluteUploadFullPath($resize);
//                    }
//                    $size = $document->getSizeBySize($resize);
//                    //exit('exit medium: '.$abspath);
//                } else {
//                    //default
//                    $originalname = $document->getOriginalnameClean();
//                    $abspath = $document->getAbsoluteUploadFullPath();
//                    $size = $document->getSize();
//                    //echo "default abspath=$abspath <br>";
//                }
//            }
//
//            //There is no small, medium size for PDF. PDF is not resize and always the same size.
//            if( !$size ) {
//                $size = $document->getSize();
//            }
//
//            //abspath=http://127.0.0.1/order/Uploaded/fellapp/FellowshipApplicantUploads/1557157978ID1J9qjngqM1Bt_PZedHfJtX1S_sALg8YS-.jpg
//            //$abspath = "http://127.0.0.1/order/Uploaded/fellapp/FellowshipApplicantUploads/small-1557157978ID1J9qjngqM1Bt_PZedHfJtX1S_sALg8YS-.jpg";
//            //echo "abspath=$abspath <br>";
//            //exit(111);
//            //$logger = $this->container->get('logger');
//            //$logger->notice("abspath=$abspath");
//            if( $abspath || $originalname || $size ) {
//                //echo "abspath=".$abspath."<br>";
//                //echo "originalname=".$originalname."<br>";
//                //echo "$abspath: size=".$size."<br>";
//                //exit(111);
//                $downloader = new LargeFileDownloader();
//                ////$filepath, $filename=null, $size=null, $retbytes=true, $action="download", $viewType=null
//                //$viewType = null; //viewType allow to resize file, but it does not work properly, so disable it by setting to null
//                $downloader->downloadLargeFile($abspath, $originalname, $size, true, "view", $viewType);
//            } else {
//                exit ("File $originalname is not available");
//            }
//
//            exit;
//        } else {
//            $response->setContent('error');
//        }
//
//        return $response;
    }


    /**
     * //@Route("/download-spreadsheet-with-ids/{ids}", name="vacreq_download_spreadsheet_get_ids")
     *
     * @Route("/download-spreadsheet/", name="vacreq_download_spreadsheet", methods={"POST"})
     */
    public function downloadExcelAction( Request $request ) {
        if( false == $this->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $vacreqUtil = $this->container->get('vacreq_util');


        $ids = $request->request->get('ids');
        //echo "ids=".$ids."<br>";
        //exit('111');

        $fileName = "Stats".".xlsx";

        if(0) {
            $fileName = "PhpOffice_".$fileName;

            $excelBlob = $vacreqUtil->createtListExcel($ids);

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelBlob, 'Xlsx');
            //ob_end_clean();
            //$writer->setIncludeCharts(true);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            //header('Content-Disposition: attachment;filename="fileres.xlsx"');

            // Write file to the browser
            $writer->save('php://output');
        } else {
            //Spout
            $vacreqUtil->createtListExcelSpout( $ids, $fileName );
        }

        exit();
    }

    /**
     * @Route("/download-summary-report-spreadsheet/", name="vacreq_download_summary_report_spreadsheet", methods={"GET","POST"})
     */
    public function downloadSummaryReportExcelAction( Request $request ) {
        if( false == $this->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $vacreqUtil = $this->container->get('vacreq_util');

        $userIdsStr = $request->request->get('ids');
        //echo "ids=".$userIdsStr."<br>";
        //exit('111');

        //$yearRangeStr = $vacreqUtil->getCurrentAcademicYearRange();
        $yearRangeStr = $request->request->get('year');

        $fileName = "SummaryReportByName-".$yearRangeStr.".xlsx";

        //echo "yearRangeStr=".$yearRangeStr."<br>";

        //$yearRanges = array();
        //$yearRanges[] = $vacreqUtil->getCurrentAcademicYearRange();
        //$yearRanges[] = $vacreqUtil->getPreviousAcademicYearRange();
        //$yearRanges[] = $vacreqUtil->getPreviousAcademicYearRange(1);

        //ids - users ids with vacreq requests
        //$userIds = $vacreqUtil->getVacReqUsers();
        //echo "userIds=".count($userIds)."<br>";
        //exit('1');
        
        //Spout
        $vacreqUtil->createtSummaryReportByNameSpout($userIdsStr, $fileName,$yearRangeStr);

        exit();
    }

    /**
     * http://127.0.0.1/order/index_dev.php/vacation-request/multiple-carry-over-requests
     *
     * @Route("/multiple-carry-over-requests", name="vacreq_multiple_carry_over_requests")
     */
    public function multipleCarryOverRequestsAction( Request $request ) {
        if( false == $this->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        exit('Not allowed.');

        $em = $this->getDoctrine()->getManager();

        $status = 'approved';

        //1) get carry-over VacReqRequest with the same year and user
        $repository = $em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");

        $dql->select('request');
        //$dql->select('DISTINCT user.id, requestType.startDate, requestType.endDate, requestType.numberOfDays as numberOfDays');
        //$dql->select('DISTINCT user.id');

        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("request.requestType", "requestType");

        $dql->where("requestType.abbreviation = 'carryover'");

        $dql->andWhere("request.status = :status");
        $params['status'] = $status;

        //$dql->andWhere("request.destinationYear = :destinationYear");
        //$params['destinationYear'] = $year;

        $query = $em->createQuery($dql);

        if( count($params) > 0 ) {
            $query->setParameters($params);
        }

        $requests = $query->getResult();
        echo "requests=".count($requests)."<br>";

        $carryOverRequests = array();
        //$carryOverDays = array();

        if(1) {
            foreach ($requests as $thisRequest) {
                $user = $thisRequest->getUser();
                $user = $user . "";
                $destinationYear = $thisRequest->getDestinationYear();

                if (isset($carryOverRequests[$user][$destinationYear])) {
                    $count = $carryOverRequests[$user][$destinationYear];
                    $count++;
                    $carryOverRequests[$user][$destinationYear] = $count;
                } else {
                    $carryOverRequests[$user][$destinationYear] = 1;
                }
            }
            echo "carryOverRequests=" . count($carryOverRequests) . "<br><br>";


            foreach ($carryOverRequests as $userId => $userCarryOverRequest) {
                //echo "userId=".$userId."<br>";
                foreach ($userCarryOverRequest as $destinationYear => $userCarryOverRequest[$userId]) {
                    //echo $thisCarryOverRequest[$userId][$destinationYear]."<br>";
                    //echo "destinationYear=$destinationYear <br>";
                    //echo "count=".$userCarryOverRequest[$userId][$destinationYear]."<br>";
                    $count = $carryOverRequests[$userId][$destinationYear];
                    //echo "$userId: $destinationYear => $count ";
                    if ($count > 1) {
                        echo "$userId: $destinationYear => $count ";
                        echo "=> Duplicate !!!";
                        echo "<br>";
                    }
                    //echo "<br>";
                }

            }
        }

//        foreach ($requests as $thisRequest) {
//            $user = $thisRequest->getUser();
//            $user = $user . "";
//            $destinationYear = $thisRequest->getDestinationYear()."";
//            $thisDay = $thisRequest->getCarryOverDays();
//
//            if (isset($carryOverDays[$user][$destinationYear])) {
//                $days = $carryOverDays[$user][$destinationYear];
//                $days = $days + $thisDay;
//                $carryOverDays[$user][$destinationYear] = $days;
//            } else {
//                $carryOverDays[$user][$destinationYear] = $thisDay;
//            }
//
//        }
//
//        foreach($carryOverDays as $userId=>$userCarryOverDays ) {
//            //echo "userId=".$userId."<br>";
//            foreach($userCarryOverDays as $destinationYear=>$userCarryOverDays[$userId]) {
//                $days = $carryOverDays[$user][$destinationYear];
//                echo "$userId: $destinationYear => $days ";
//                echo "<br>";
//            }
//
//        }

        exit('EOF multipleCarryOverRequestsAction');
    }

    /**
     * http://127.0.0.1/order/index_dev.php/vacation-request/diff-carry-over-days
     *
     * @Route("/diff-carry-over-days", name="vacreq_diff_carry_over_days")
     */
    public function diffCarryOverDaysAction( Request $request )
    {
        if (false == $this->isGranted('ROLE_VACREQ_USER')) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        exit('Not allowed.');

        $vacreqUtil = $this->container->get('vacreq_util');
        $em = $this->getDoctrine()->getManager();

        $status = 'approved';

        //1) for all VacReqCarryOver => get days
        //2) find approved carry over request for this user and year
        //3) compare days

        $repository = $em->getRepository('AppVacReqBundle:VacReqCarryOver');
        $dql =  $repository->createQueryBuilder("carryover");

        $dql->select('carryover');

        $dql->leftJoin("carryover.userCarryOver", "userCarryOver");

        //$dql->leftJoin("userCarryOver.requestType", "requestType");
        //$dql->leftJoin("userCarryOver.user", "user");
        //$dql->where("requestType.abbreviation = 'carryover'");

        //$dql->andWhere("request.status = :status");
        //$params['status'] = $status;

        //$dql->andWhere("request.destinationYear = :destinationYear");
        //$params['destinationYear'] = $year;

        $query = $em->createQuery($dql);

//        if( count($params) > 0 ) {
//            $query->setParameters($params);
//        }

        $carryovers = $query->getResult();
        echo "carryovers=".count($carryovers)."<br>";

        foreach($carryovers as $carryover) {
            $carryOverUser = $carryover->getUserCarryOver();
            $user = $carryOverUser->getUser();
            $days = $carryover->getDays();
            $year = $carryover->getYear();

            $approvedRequests = $vacreqUtil->getCarryOverRequestsByUserStatusYear($user,'approved',$year);
            //echo "approvedRequests=".count($approvedRequests)."<br>";

            if( count($approvedRequests) > 1 ) {
                echo "$user: $year => $days days";
                echo "=> Duplicate !!!";
                echo "<br>";
            }

            if( count($approvedRequests) == 1 ) {
                $approvedRequest = $approvedRequests[0];
                $thisDays = $approvedRequest->getCarryOverDays();

                if( $thisDays != $days ) {
                    echo "$user: $year => Diff!!!: [$days] != [$thisDays]";
                    echo "<br>";
                }
            }
        }

        exit('EOF diffCarryOverDaysAction');
    }


    /**
     * http://127.0.0.1/order/index_dev.php/vacation-request/cancel-old-pending-vacation-requests
     *
     * @Route("/cancel-old-pending-vacation-requests", name="vacreq_cancel-old-pending-vacation-requests")
     */
    public function cancelOldPendingVacationRequestsAction( Request $request )
    {
        if( !$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        exit('Not allowed.');

        $user = $this->getUser();
        $userSecUtil = $this->container->get('user_security_utility');
        //$vacreqUtil = $this->container->get('vacreq_util');
        $em = $this->getDoctrine()->getManager();

        $params = array();
        $changeStatusTo = 'rejected';

        $repository = $em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");

        $dql->select('request');
        
        $dql->leftJoin("request.requestBusiness", "requestBusiness");
        $dql->leftJoin("request.requestVacation", "requestVacation");

        $dql->where("(requestBusiness IS NOT NULL OR requestVacation IS NOT NULL)");

        $dql->andWhere("(requestBusiness.status = :status OR requestVacation.status = :status)");
        $params['status'] = 'pending';

        $dql->andWhere("request.createDate < :maxCreateDate");
        $params['maxCreateDate'] = '2019-01-01';

        $query = $em->createQuery($dql);

        if( count($params) > 0 ) {
            $query->setParameters($params);
        }

        $requests = $query->getResult();
        echo "requests=".count($requests)."<br>";

        foreach($requests as $vacreqRequest) {

            $bStatusChanged = false;
            $vStatusChanged = false;

            $bRequest = NULL;
            $vRequest = NULL;

            $bOriginalStatus = "N/A";
            $vOriginalStatus = "N/A";

            $bNewStatus = "N/A";
            $vNewStatus = "N/A";

            if( $vacreqRequest->hasBusinessRequest() ) {
                $bRequest = $vacreqRequest->getRequestBusiness();
                $bOriginalStatus = $bRequest->getStatus();
            }

            if( $vacreqRequest->hasVacationRequest() ) {
                $vRequest = $vacreqRequest->getRequestVacation();
                $vOriginalStatus = $vRequest->getStatus();
            }

            echo $vacreqRequest->getId()."; Submitted=".
                $vacreqRequest->getCreateDate()->format('m-d-Y').
                "; bStatus=".$bOriginalStatus." vStatus=".$vOriginalStatus."<br>";

            if( $bOriginalStatus == 'pending' ) {
                $bRequest->setStatus($changeStatusTo);
                $bNewStatus = $bRequest->getStatus();
                $bStatusChanged = true;
            }
            if( $vOriginalStatus == 'pending' ) {
                $vRequest->setStatus($changeStatusTo);
                $vNewStatus = $vRequest->getStatus();
                $vStatusChanged = true;
            }

            if( $bStatusChanged || $vStatusChanged ) {
                $em->flush();
                $event = "Changed old pending status request ID#".$vacreqRequest->getId()."; Submitted=".
                    $vacreqRequest->getCreateDate()->format('m-d-Y').
                    "; ".
                    "bStatus: ".$bOriginalStatus." to ".$bNewStatus.
                    "; ".
                    "vStatus:".$vOriginalStatus." to ".$vNewStatus;
                echo $event."<br>";

                $eventType = "Business/Vacation Request Updated";
                $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'),$event,$user,$vacreqRequest,$request,$eventType);
            }

            echo "<br>";
        }

        exit('EOF cancelOldPendingRequestsAction');
    }

    /**
     * http://127.0.0.1/order/index_dev.php/vacation-request/cancel-old-pending-carryover-requests
     *
     * @Route("/cancel-old-pending-carryover-requests", name="vacreq_cancel-old-pending-carryover-requests")
     */
    public function cancelOldPendingCarryoverRequestsAction( Request $request )
    {
        if( !$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        exit('cancelOldPendingCarryoverRequestsAction Not allowed.');

        $user = $this->getUser();
        $userSecUtil = $this->container->get('user_security_utility');
        //$vacreqUtil = $this->container->get('vacreq_util');
        $em = $this->getDoctrine()->getManager();

        $params = array();
        $changeStatusTo = 'rejected';

        $repository = $em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");

        $dql->select('request');

        $dql->leftJoin("request.requestType", "requestType");

        $dql->where("(requestType.abbreviation = :requestTypeName AND request.carryOverDays IS NOT NULL)");
        //$dql->where("requestType.abbreviation = :requestTypeName");
        $params['requestTypeName'] = 'carryover';

        $dql->andWhere("request.status = :status");
        $params['status'] = 'pending';

        $dql->andWhere("request.createDate < :maxCreateDate");
        $params['maxCreateDate'] = '2020-01-01';

        $query = $em->createQuery($dql);

        if( count($params) > 0 ) {
            $query->setParameters($params);
        }

        $requests = $query->getResult();
        echo "carryover requests=".count($requests)."<br>";

        foreach($requests as $carryoverRequest) {

            $originalStatus = $carryoverRequest->getStatus();

            $carryoverRequest->setStatus($changeStatusTo);

            $newStatus = $carryoverRequest->getStatus();

            if( 1 ) {
                $em->flush();
                $event = "Changed old pending status for carry over request ID#".$carryoverRequest->getId()."; Submitted=".
                    $carryoverRequest->getCreateDate()->format('m-d-Y').
                    "; ".
                    "Change status: ".$originalStatus." to ".$newStatus;
                echo $event."<br>";

                $eventType = "Carry Over Request Updated";
                $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'),$event,$user,$carryoverRequest,$request,$eventType);
            }

            echo "<br>";
        }

        exit('EOF cancelOldPendingCarryoverRequestsAction');
    }
}
