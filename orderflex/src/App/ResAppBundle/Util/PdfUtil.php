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

/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 8/28/15
 * Time: 8:47 AM
 */

namespace App\ResAppBundle\Util;


//use Clegginabox\PDFMerger\PDFMerger;
use App\ResAppBundle\PdfParser\PDFService;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Doctrine\ORM\EntityManagerInterface;
use Spatie\PdfToText\Pdf;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityNotFoundException;
use App\ResAppBundle\Controller\ResAppController;
use App\ResAppBundle\Form\ResidencyApplicationType;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

//use Symfony\Component\Process\Exception\ProcessFailedException;
//use Symfony\Component\Process\Process as SymfonyProcess;

use App\ResAppBundle\Entity\ReportQueue;
use App\ResAppBundle\Entity\Process;


//The last working commit before changing directory separator: 78518efa68a8d81070ea87755f40586f4534faae

class PdfUtil {


    protected $em;
    protected $container;
    protected $session;
    protected $uploadDir;


    //public function __construct( EntityManagerInterface $em, ContainerInterface $container, Session $session ) {
    public function __construct( EntityManagerInterface $em, ContainerInterface $container, SessionInterface $session ) {
        $this->em = $em;
        $this->container = $container;
        $this->session = $session;

        $this->uploadDir = 'Uploaded';

        $userSecUtil = $this->container->get('user_security_utility');
        $resappuploadpath = $userSecUtil->getSiteSettingParameter('resappuploadpath'); //resapp/documents
        $path = 'Uploaded'.DIRECTORY_SEPARATOR.$resappuploadpath;
        $this->uploadPath = $path;  //'Uploaded'.DIRECTORY_SEPARATOR.$resappuploadpath.DIRECTORY_SEPARATOR;
    }

    public function getExistingApplicationsByPdf( $pdfFiles ) {
        $handsomtableJsonData = array();
        $usedPdfArr = array();
        $pdfInfoArr = array();

        foreach($pdfFiles as $pdfFile) {

            $residencyApplicationDb = NULL;
            $erasApplicantId = NULL;

            //$pdfInfoArr[$pdfFile->getId()] = array('file'=>$pdfFile,'text' => $pdfText, 'path' => $pdfFilePath, 'originalName'=>$pdfFile->getOriginalname());
            $pdfInfoArr[$pdfFile->getId()] = array('file'=>$pdfFile,'originalName'=>$pdfFile->getOriginalname());

            $originalFileName = $pdfFile->getOriginalname();
            //echo "originalFileName=$originalFileName <br>";
            if( $originalFileName ) {

//                //Try to find by a file name notation "...aid=12345678.pdf"
//                if(
//                    //strpos($originalFileName, 'ApplicantID=') !== false ||
//                    //strpos($originalFileName, 'AID=') !== false ||
//                    strpos($originalFileName, 'aid') !== false
//                ) {
//                    $originalFileNameSplit = explode('aid',$originalFileName);
//                    if( count($originalFileNameSplit) > 0 ) {
//                        $erasApplicantId = $originalFileNameSplit[1]; //2021248381.pdf
//                        $erasApplicantId = str_replace(".pdf","",$erasApplicantId);
//                        $erasApplicantId = str_replace("=","",$erasApplicantId);
//                        $erasApplicantId = str_replace(":","",$erasApplicantId);
//                        $erasApplicantId = str_replace("-","",$erasApplicantId);
//                        $erasApplicantId = str_replace("_","",$erasApplicantId);
//                        //echo "erasApplicantId=$erasApplicantId <br>"; //2021248381.pdf
////                        $aidSplit = explode('_',$aidPart);
////                        if( count($aidSplit) > 0 ) {
////                            $erasApplicantId = $aidSplit[0];
////                        } else {
////                            $aidSplit = explode('.',$aidPart); //End of file before extension "..._aid=12345678.pdf"
////                            if( count($aidSplit) > 0 ) {
////                                $erasApplicantId = $aidSplit[0];
////                            }
////                        }
//                        //echo "filename erasApplicantId=$erasApplicantId <br>";
//                        $residencyApplicationDb = $this->em->getRepository('AppResAppBundle:ResidencyApplication')->findOneByErasApplicantId($erasApplicantId);
//                    }
//                }

                $residencyApplicationDb = $this->findResApplicationByFileName($originalFileName);
            }

            //Try to find by PDF content: "Applicant ID:" or "AAMC ID:"
            //TODO: if multiple application exists, choose the one with status=active
            if( !$residencyApplicationDb ) {
                $pdfText = NULL;
                $pdfFilePath = $pdfFile->getFullServerPath();
                if ($pdfFilePath) {
                    $pdfText = $this->extractPdfText($pdfFilePath);
                    //if ($pdfText) {
                    //$pdfInfoArr[$pdfFile->getId()] = array('file'=>$pdfFile,'text' => $pdfText, 'path' => $pdfFilePath, 'originalName'=>$pdfFile->getOriginalname());
                    //}
                }

                if ($pdfText) {
                    $keyExistCount = 0;
                    $totalCount = 0;

                    $extractedErasApplicantID = $this->getSingleKeyField($pdfText, 'Applicant ID:');
                    //echo "erasApplicantID=$extractedErasApplicantID <br>";
                    if ($extractedErasApplicantID) {
                        //find resapp by Applicant ID
                        $residencyApplicationDb = $this->em->getRepository('AppResAppBundle:ResidencyApplication')->findOneByErasApplicantId($extractedErasApplicantID);
                    }
                    if( $residencyApplicationDb ) {
                        //echo "found by extractedErasApplicantID=$extractedErasApplicantID: ID=".$residencyApplicationDb->getId()."<br>";
                    }

                    $aamcID = $this->getSingleKeyField($pdfText, 'AAMC ID:');
                    //echo "aamcID=$aamcID <br>";
                    if( $aamcID && !$residencyApplicationDb ) {
                        $residencyApplicationDb = $this->em->getRepository('AppResAppBundle:ResidencyApplication')->findOneByAamcId($aamcID);
                    }
                    if( $residencyApplicationDb ) {
                        //echo "found by aamcID=$aamcID: ID=".$residencyApplicationDb->getId()."<br>";
                    }

                }
            }

            if( $residencyApplicationDb ) {
                //Construct $handsomtableJsonData

//                "Application Receipt Date" => "Applicant Applied Date",
//                "Application Season Start Date" => "Applicant Applied Date",
//                "Application Season End Date" => "Applicant Applied Date",
//                "Expected Residency Start Date" => "Applicant Applied Date",
//                "Expected Graduation Date" => "Applicant Applied Date",
//
//                "First Name" => "First Name",
//                "Middle Name" => "Middle Name",
//                "Last Name" => "Last Name",
//
//                "Preferred Email" => "E-mail",

                $usedPdfArr[$pdfFile->getId()] = true;

//                $rowArr = array();
//
//                $thisErasApplicantId = $residencyApplicationDb->getErasApplicantId();
//                if( !$thisErasApplicantId ) {
//                    $thisErasApplicantId = $erasApplicantId;
//                }
//
//                $rowArr['ERAS Application ID']['value'] = $thisErasApplicantId;
//                $rowArr['ERAS Application ID']['id'] = $residencyApplicationDb->getId();
//
//                $rowArr["AAMC ID"]['value'] = $residencyApplicationDb->getAamcId();
//                $rowArr["AAMC ID"]['id'] = $residencyApplicationDb->getId();
//
//                $rowArr['ERAS Application']['value'] = $pdfFile->getOriginalname();
//                $rowArr['ERAS Application']['id'] = $pdfFile->getId();
//
//                //$rowArr['Expected Residency Start Date']['value'] = $residencyApplicationDb->getStartDate();
//                //$rowArr["Expected Residency Start Date"]['id'] = $residencyApplicationDb->getId();
//
//                $applicantUser = $residencyApplicationDb->getUser();
//
//                $rowArr["Preferred Email"]['value'] = $applicantUser->getEmail();
//                $rowArr["Preferred Email"]['id'] = $applicantUser->getId();
//
//                $rowArr["First Name"]['value'] = $applicantUser->getFirstName();
//                $rowArr["First Name"]['id'] = $applicantUser->getId();
//
//                $rowArr["Last Name"]['value'] = $applicantUser->getLastName();
//                $rowArr["Last Name"]['id'] = $applicantUser->getId();
//
//                $rowArr['Status']['value'] = "Update PDF & ID Only, CSV is not provided";
//                $rowArr['Status']['id'] = -2; //$residencyApplicationDb->getId();
//
//                //change the value in the “Action” column to “Do not add”
//                $rowArr['Action']['value'] = "Update PDF & ID Only";
//                $rowArr['Action']['id'] = $residencyApplicationDb->getId();
//
//                $handsomtableJsonData[] = $rowArr;

                $rowArr = array();

                //$rowArr, $residencyApplicationDb, $erasApplicantId=NULL, $pdfFile=NULL
                $rowArr = $this->populateRowByExistedResapp($rowArr,$residencyApplicationDb,$pdfFile);

                $thisErasApplicantId = $residencyApplicationDb->getErasApplicantId();
                if( !$thisErasApplicantId ) {
                    $thisErasApplicantId = $erasApplicantId;
                }
                $rowArr['ERAS Application ID']['value'] = $thisErasApplicantId;
                $rowArr['ERAS Application ID']['id'] = $residencyApplicationDb->getId();

                //$rowArr['Status']['value'] = "Update PDF & ID Only, CSV is not provided";
                //$rowArr['Status']['id'] = -2; //$residencyApplicationDb->getId();

                //$rowArr['ERAS Application']['value'] = $pdfFile->getOriginalname();
                //$rowArr['ERAS Application']['id'] = $pdfFile->getId();


                //check If PDF is Existed In Resapp
                $existedPDF = $this->checkIfPDFExistInResapp($pdfFile,array($residencyApplicationDb));
                if( $existedPDF === false ) {
                    //New PDF
                    $rowArr['Action']['value'] = $residencyApplicationDb->getAddToStr();
                    $rowArr['Action']['id'] = $residencyApplicationDb->getId();

                    $rowArr['Status']['id'] = -2;
                    //$rowArr['Status']['value'] = "No match in CSV, previously uploaded PDF differs"; //match not found in CSV file
                    $rowArr['Status']['value'] = "CSV is not provided, new PDF"; //match not found in CSV file
                } else {
                    //PDF is already existed in the residency application
                    $rowArr['Action']['value'] = "Do not add";
                    $rowArr['Action']['id'] = null;

                    $rowArr['Status']['id'] = -2;
                    $rowArr['Status']['value'] = "CSV is not provided, same PDF previously uploaded"; //match not found in CSV file
                }


                $handsomtableJsonData[] = $rowArr;
            }
        }//foreach $pdfFiles

        $handsomtableJsonData = $this->addNotUsedPDFtoTable($handsomtableJsonData,$pdfInfoArr,$usedPdfArr,"CSV is not provided");
        //dump($handsomtableJsonData);
        //exit("111");

        if( count($handsomtableJsonData) == 0 ) {
            $handsomtableJsonData = "No match of existing residency applications and provided PDF files are found";
        }

        //dump($handsomtableJsonData);
        //exit('111');

        return $handsomtableJsonData;
    }


    public function getCsvApplicationsData( $csvFileName, $pdfFiles ) {

        //echo "csvFileName=$csvFileName <br>";
        $resappImportFromOldSystemUtil = $this->container->get('resapp_import_from_old_system_util');
        $userServiceUtil = $this->container->get('user_service_utility');

        $handsomtableJsonData = array();

        if( !$csvFileName ) {
            return "CSV file is missing";
        }

        if (file_exists($csvFileName)) {
            //echo "The file $inputFileName exists";
        } else {
            //echo "The file $inputFileName does not exist";
            return "The file $csvFileName does not exist";
            //return $handsomtableJsonData;
        }

        //$reader = ReaderEntityFactory::createReaderFromFile($csvFileName);
        $reader = ReaderEntityFactory::createCSVReader();

        $reader->open($csvFileName);

        //around 3877 columns, 833 not empty columns
        
        //Get header->column index map
        $header = array();
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                if( $rowNumber > 1 ) {
                    break;
                }

                $cells = $row->getCells();
                for($column = 0; $column <= 100000; $column++) {
                    //echo "The number is: $column <br>";

                    if( !isset($cells[$column]) ) {
                        break;
                    }

                    $thisCell = $cells[$column];
                    $thisCellValue = $thisCell->getValue();
                    if( $thisCellValue ) {
                        if( !isset($header[$thisCellValue]) ) {
                            $header[$thisCellValue] = $column;
                        }
                    }
                }

            }
        }
        //dump($header);
        //exit(111);

        $usedPdfArr = array();
        $pdfInfoArr = $this->getPdfTextArr($pdfFiles);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                // do stuff with the row
                if( $rowNumber == 1 ) {
                    continue;
                }
                $cells = $row->getCells();
                //echo "rowNumber=$rowNumber <br>";
                //dump($cells);

                //get cell index by header
                //$emailCell = $cells[4];
                //$cell->getValue();

                $rowArr = array();
                foreach( $this->getHeaderMap() as $handsomTitle => $headerTitle ) {
                    //echo "csvHeaderTitle=$headerTitle => $handsomTitle <br>";

//                    if( $handsomTitle == "Application Receipt Date" ) {
//                        //Applicant Applied Date
//                        //pre-populate with current date
//                        $rowArr[$handsomTitle]['id'] = 1;
//                        $rowArr[$handsomTitle]['value'] = date("m/d/Y");
//                        continue;
//                    }

                    if( isset($header[$headerTitle]) ) {
                        $column = $header[$headerTitle];

                        if( isset($cells[$column]) ) {
                            $cell = $cells[$column];
                            $cellValue = $cell->getValue();
                            $cellValue = trim($cellValue);

                            //echo $headerTitle." ($handsomTitle)"."( col=".$column."): cellValue=".$cellValue."<br>";

                            if ($handsomTitle == "Application Receipt Date") {
                                if( $cellValue ) {
                                    //use $cellValue "Applicant Applied Date"
                                } else {
                                    //pre-populate with current date
                                    $cellValue = date("m/d/Y");
                                }
                            }

                            if( $cellValue ) {
                                if ($handsomTitle == "Application Season Start Date") {
                                    //get year 9/29/2018 m/d/Y
                                    $year = $this->getYear($cellValue);
                                    $cellValue = "07/01/".$year;
                                    //$cellValue = $year."-12-31";
                                }
                                if ($handsomTitle == "Application Season End Date") {
                                    //get year 9/29/2018 m/d/Y
                                    $year = $this->getYear($cellValue);
                                    $year = $year + 1;
                                    $cellValue = "06/30/".$year;
                                }
                                if ($handsomTitle == "Expected Residency Start Date") {
                                    //get year 9/29/2018 m/d/Y
                                    $year = $this->getYear($cellValue);
                                    //$year->modify('+1 year');
                                    $year = $year + 1;
                                    $cellValue = "07/01/".$year;
                                }
                                if ($handsomTitle == "Expected Graduation Date") {
                                    //get year 9/29/2018 m/d/Y
                                    $year = $this->getYear($cellValue);
                                    $year = $year + 2;
                                    $cellValue = "06/30/".$year;
                                }

                                if ($handsomTitle == "Birth Date") {
                                    //make same format (mm/dd/YYYY) 5/5/1987=>05/05/1987
                                    $cellValue = date("m/d/Y", strtotime($cellValue));
                                }

                                //Map degree to the existing notations in DB
                                if( $handsomTitle == "Degree" ) {
                                    $cellValue = $resappImportFromOldSystemUtil->getDegreeMapping($cellValue);
                                }
                                if( $handsomTitle == "Medical School Graduation Date" ) {
                                    //echo "medSchoolGradDateValue=$cellValue => "; // 8/2014 - 5/2019
                                    $medSchoolGradDateFull = NULL;
                                    $medSchoolGradDateValueArr = explode("-",$cellValue);
                                    if( count($medSchoolGradDateValueArr) == 2 ) {
                                        $medSchoolGradDateMY = $medSchoolGradDateValueArr[1]; //"5/2019"
                                        $medSchoolGradDateMY = trim($medSchoolGradDateMY);
                                        //$medSchoolGradDateFull = "01/".$medSchoolGradDateMY;
                                        $splitGradDate=explode('/',$medSchoolGradDateMY);
                                        if( count($splitGradDate) == 2 ) {
                                            $medSchoolGradDateFull = trim($splitGradDate[0]) . "/01/" . trim($splitGradDate[1]);
                                        }
                                    }
                                    $cellValue = $medSchoolGradDateFull;
                                }
                                if( $handsomTitle == "Country of Citizenship" ) {
                                    $cellValue = $resappImportFromOldSystemUtil->getCitizenshipMapping($cellValue);
                                }
                                if( $handsomTitle == "Visa Status" ) {
                                    $cellValue = $resappImportFromOldSystemUtil->getVisaMapping($cellValue);
                                }

                                if( $handsomTitle == "Previous Residency Country" ) {
                                    $cellValue = $userServiceUtil->findCountryByIsoAlpha3($cellValue);
                                }

                                //Previous Residency Start Date 16-Jun (day-month) => 06/01/2016 (mm/dd/Y)
                                //2020: Jan-06
                                if( $handsomTitle == "Previous Residency Start Date" ) {
                                    $resStartDateFull = NULL;
                                    if( strpos($cellValue, '-') !== false ) {
                                        $splitResStartDate=explode('-',$cellValue);
                                    } else {
                                        $splitResStartDate=explode('/',$cellValue);
                                    }
                                    if( count($splitResStartDate) == 2 ) {
                                        //$resStartDateYear = trim($splitResStartDate[0]);
                                        //$resStartDateMonth = trim($splitResStartDate[1]);
                                        $resStartDateYear = trim($splitResStartDate[1]);
                                        $resStartDateMonth = trim($splitResStartDate[0]);
                                        if (is_numeric($resStartDateMonth)) {
                                            $nmonth = $resStartDateMonth;
                                        } else {
                                            $nmonth = date("m", strtotime($resStartDateMonth));
                                        }
                                        $resStartDateFull = $nmonth . "/01/" . $resStartDateYear;
                                    }
                                    //echo $handsomTitle.": Previous Residency Start Date: $resStartDateFull <br>";
                                    $cellValue = $resStartDateFull;
                                }

                                //Previous Residency Graduation/Departure Date 11-Mar
                                //2020: Jan-06
                                if( $handsomTitle == "Previous Residency Graduation/Departure Date" ) {
                                    $resEndDateFull = NULL;
                                    //$splitResEndDate=explode('-',$cellValue);
                                    if( strpos($cellValue, '-') !== false ) {
                                        $splitResEndDate=explode('-',$cellValue);
                                    } else {
                                        $splitResEndDate=explode('/',$cellValue);
                                    }
                                    if( count($splitResEndDate) == 2 ) {
                                        //$resEndDateYear = trim($splitResEndDate[0]);
                                        //$resEndDateMonth = trim($splitResEndDate[1]);
                                        $resEndDateYear = trim($splitResEndDate[1]);
                                        $resEndDateMonth = trim($splitResEndDate[0]);
                                        if (is_numeric($resEndDateMonth)) {
                                            $nmonth = $resEndDateMonth;
                                        } else {
                                            $nmonth = date("m", strtotime($resEndDateMonth));
                                        }
                                        $resEndDateFull = $nmonth . "/01/" . $resEndDateYear;
                                    }
                                    //echo $handsomTitle.": Previous Residency Graduation/Departure Date: $resEndDateFull <br>";
                                    $cellValue = $resEndDateFull;
                                    //exit("test exit after date");
                                }

                                //TODO: Is the applicant a member of any of the following groups?
                            }

                            $rowArr[$handsomTitle]['id'] = 1;
                            $rowArr[$handsomTitle]['value'] = $cellValue;
                        }
                    }
                }//foreach title

//                $rowArr = array();
//                $rowArr["First Name"]['id'] = 1;
//                $rowArr["First Name"]['value'] = "Test First Name";
//
//                //Last Name
//                $rowArr["Last Name"]['id'] = 1;
//                $rowArr["Last Name"]['value'] = "Test Last Name";
//
//                //Middle Name
//                $rowArr["Middle Name"] = NULL;
//
//                //Preferred Email
//                $rowArr["Preferred Email"]['id'] = 1;
//                $rowArr["Preferred Email"]['value'] = "Test Email";

                if(1) {
                    //Relax keys to identify matched PDF
                    $keysArr = array(
                        $rowArr["AAMC ID"]['value'],
                        $rowArr["Preferred Email"]['value']
                    );

                    if(
                        isset($rowArr["First Name"]) && isset($rowArr["First Name"]['value']) &&
                        isset($rowArr["Last Name"]) && isset($rowArr["Last Name"]['value'])
                    ) {
                        $keysArr[] = $rowArr["First Name"]['value'] . " " . $rowArr["Last Name"]['value'];
                        $keysArr[] = $rowArr["Last Name"]['value'] . " " . $rowArr["First Name"]['value'];
                    }

                    if (isset($rowArr["USMLE ID"]) && isset($rowArr["USMLE ID"]['value'])) {
                        $keysArr[] = $rowArr["USMLE ID"]['value'];
                    }

                    if (isset($rowArr["NBOME ID"]) && isset($rowArr["NBOME ID"]['value'])) {
                        $keysArr[] = $rowArr["NBOME ID"]['value'];
                    }

                    if (isset($rowArr["NRMP ID"]) && isset($rowArr["NRMP ID"]['value'])) {
                        $keysArr[] = $rowArr["NRMP ID"]['value'];
                    }

                    if (isset($rowArr["ERAS Application ID"]) && isset($rowArr["ERAS Application ID"]['value'])) {
                        $keysArr[] = $rowArr["ERAS Application ID"]['value'];
                    }

                } else {
                    //Strict keys to identify matched PDF
                    $keysArr = array(
                        $rowArr["AAMC ID"]['value'],
                        $rowArr["Preferred Email"]['value'],
                        $rowArr["Birth Date"]['value'],
                        $rowArr["USMLE ID"]['value'],
                        //$rowArr["NBOME ID"]['value'], //might be null
                        //$rowArr["NRMP ID"]['value'],
                    );
                    if (isset($rowArr["NRMP ID"]) && isset($rowArr["NRMP ID"]['value'])) {
                        $keysArr[] = $rowArr["NRMP ID"]['value'];
                    }
                }

                ////////////// Try to find match between application and PDF files (by ERAS Application ID?) //////////////////
                //$pdfFile = $this->findPdfByInfoArrByAllKeys($pdfInfoArr,$keysArr);
                $pdfFile = $this->findPdfByInfoArrByAnyKeys($pdfInfoArr,$keysArr);
                if( $pdfFile ) {
                    //echo "!!!! found ERAS Application:".$rowArr["Last Name"]['value']."<br>";
                    $usedPdfArr[$pdfFile->getId()] = true;
                    $rowArr['ERAS Application']['id'] = $pdfFile->getId();
                    $rowArr['ERAS Application']['value'] = $pdfFile->getOriginalname();

                    //TODO: get "ERAS Application ID" from PDF
                    $pdfText = $pdfInfoArr[$pdfFile->getId()]['text'];
                    $erasApplicantID = $this->getSingleKeyField($pdfText,'Applicant ID:');
                    if( $erasApplicantID ) {
                        $rowArr['ERAS Application ID']['id'] = null;
                        $rowArr['ERAS Application ID']['value'] = $erasApplicantID;
                    }
                } else {
                    //echo "Not found ERAS Application:".$rowArr["Last Name"]['value']."<br>";
                }
                ////////////// EOF Try to find match between application and PDF files (by ERAS Application ID?) //////////////////

                ////////////// check for duplicate //////////////////
                $duplicateRes = $this->checkDuplicate($rowArr,$handsomtableJsonData);
                $duplicateArr = $duplicateRes['duplicateInfoArr'];
                $duplicateResapps = $duplicateRes['duplicateResapps'];

                if( count($duplicateArr) > 0 ) {

                    //change the value in the “Action” column to “Do not add”
                    $rowArr['Action']['id'] = null;
                    $rowArr['Action']['value'] = "Do not add";

                    $rowArr['Status']['id'] = null;
                    $rowArr['Status']['value'] = implode(", ",$duplicateArr);

                    if( $pdfFile ) {

                        //Check if this PDF already attached to the application (if PDF different => Status="previously uploaded PDF differs")
                        //md5_file() in itself is slow. it takes 0.4 sec to return the md5 for a file of 70kb => pre-generate md5 for each file on upload or processDocument
                        $existedPDF = $this->checkIfPDFExistInResapp($pdfFile,$duplicateResapps);
                        if( $existedPDF === false ) {
                            //if $duplicateArr has "Previously Imported"
                            if( in_array("Previously Imported", $duplicateArr) ) {
                                //change the value in the “Action” column to "Update PDF & ID Only"
                                $rowArr['Action']['id'] = null;
                                $rowArr['Action']['value'] = "Update PDF & ID Only";

                                $rowArr['Status']['id'] = -2; //implode(",",$duplicateIds);
                                $rowArr['Status']['value'] = implode(", ", $duplicateArr) . ", " . "new PDF"; //"previously uploaded PDF differs";
                            }
                        }

                    }

                } else {
                    //No duplicate found => change the value in the “Action” column to “Add”
                    //Testing: comment out below for testing
                    $rowArr['Action']['id'] = null;
                    $rowArr['Action']['value'] = "Create New Record";
                }
                ////////////// EOF check for duplicate //////////////////

                //TODO:
                //"" => "Number of first author publications",
                //"Count of Peer Reviewed Book Chapter" => "Count of Peer Reviewed Book Chapter",
                //"Count of Peer Reviewed Journal Articles/Abstracts" => "Count of Peer Reviewed Journal Articles/Abstracts",
                //"Count of Peer Reviewed Online Publication" => "Count of Peer Reviewed Online Publication",
                //"Count of Scientific Monograph" => "Count of Scientific Monograph",
                $numberFirstAuthorPublications =
                    (int)$rowArr['Count of Peer Reviewed Book Chapter']['value']
                    + (int)$rowArr['Count of Peer Reviewed Journal Articles/Abstracts']['value']
                    + (int)$rowArr['Count of Peer Reviewed Online Publication']['value']
                    + (int)$rowArr['Count of Scientific Monograph']['value'];
                if( $numberFirstAuthorPublications ) {
                    $rowArr['Number of first author publications']['id'] = null;
                    $rowArr['Number of first author publications']['value'] = $numberFirstAuthorPublications;
                }

                //"" => "Number of all publications",
                //"Count of Non Peer Reviewed Online Publication" => "Count of Non Peer Reviewed Online Publication",
                //"Count of Other Articles" => "Count of Other Articles",
                $numberAllPublications =
                    $numberFirstAuthorPublications
                    + (int)$rowArr['Count of Non Peer Reviewed Online Publication']['value']
                    + (int)$rowArr['Count of Other Articles']['value'];
                if( $numberAllPublications ) {
                    $rowArr['Number of all publications']['id'] = null;
                    $rowArr['Number of all publications']['value'] = $numberAllPublications;
                }

                $handsomtableJsonData[] = $rowArr;
                
            }//foreach row
        }
        //dump($handsomtableJsonData);
        //exit(111);

        //added not used PDF files
        $handsomtableJsonData = $this->addNotUsedPDFtoTable($handsomtableJsonData,$pdfInfoArr,$usedPdfArr,"Not in CSV");
        //dump($handsomtableJsonData);
        //exit("111");

        //If all three values of a given application (from the CSV) are found in the text extracted from the PDF? 190(12)
        //No need: the row is already prepopulated from CSV and associated PDF file is inserted to the ERAS Application column

        $reader->close();

        return $handsomtableJsonData;
    }
    public function getHeaderMap() {
        //Handsomtable header title => CSV header title
        $map = array(
            "AAMC ID" => "AAMC ID",
            "ERAS Application ID" => "ERAS Application ID",
            //"Residency Track" => "Residency Track", //?

            "Application Receipt Date" => "Applicant Applied Date",
            "Application Season Start Date" => "Applicant Applied Date",
            "Application Season End Date" => "Applicant Applied Date",
            "Expected Residency Start Date" => "Applicant Applied Date",
            "Expected Graduation Date" => "Applicant Applied Date",

            "First Name" => "First Name",
            "Middle Name" => "Middle Name",
            "Last Name" => "Last Name",

            "Preferred Email" => "E-mail",

            "Medical School Name" => "Most Recent Medical School",
            "Medical School Graduation Date" => "Medical School Attendance Dates", //8/2014 - 5/2019
            "Degree" => "Medical Degree",

            "USMLE Step 1 Score" => "USMLE Step 1 Score",
            "USMLE Step 2 CK Score" => "USMLE Step 2 CK Score",
            "USMLE Step 3 Score" => "USMLE Step 3 Score",

            "Country of Citizenship" => "Citizenship",
            //"Visa Status" => "Current Visa Status",
            "Visa Status" => "Current Work Authorization",


            "Is the applicant a member of any of the following groups?" => "Self Identify",

            //1            Count of Non Peer Reviewed Online Publication
            //0            Count of Oral Presentation
            //1            Count of Other Articles
            //1            Count of Peer Reviewed Book Chapter
            //1            Count of Peer Reviewed Journal Articles/Abstracts
            //0            Count of Peer Reviewed Journal Articles/Abstracts(Other than Published)
            //1            Count of Peer Reviewed Online Publication
            //0            Count of Poster Presentation
            //1            Count of Scientific Monograph
            "Number of first author publications" => "Number of first author publications",
            "Count of Peer Reviewed Book Chapter" => "Count of Peer Reviewed Book Chapter",
            "Count of Peer Reviewed Journal Articles/Abstracts" => "Count of Peer Reviewed Journal Articles/Abstracts",
            "Count of Peer Reviewed Online Publication" => "Count of Peer Reviewed Online Publication",
            "Count of Scientific Monograph" => "Count of Scientific Monograph",

            "Number of all publications" => "Number of all publications",
            "Count of Non Peer Reviewed Online Publication" => "Count of Non Peer Reviewed Online Publication",
            "Count of Other Articles" => "Count of Other Articles",


            //"" => "AOA",
            "Couple’s Match" => "Participating as a Couple in NRMP",
            //"" => "Post-Sophomore Fellowship",

            //CSV fields:
            //Most Recent Medical School
            //Most Recent Medical Training City
            //Most Recent Medical Training Country
            //Most Recent Medical Training Director
            //Most Recent Medical Training Discipline
            //Most Recent Medical Training End Date
            //Most Recent Medical Training Program
            //Most Recent Medical Training Start Date
            //Most Recent Medical Training State
            //Most Recent Medical Training Supervisor
            //Handsomtable field => CSV field
            "Previous Residency Start Date" => "Most Recent Medical Training Start Date",
            "Previous Residency Graduation/Departure Date" => "Most Recent Medical Training End Date",
            "Previous Residency Institution" => "Most Recent Medical School",
            "Previous Residency City" => "Most Recent Medical Training City",
            "Previous Residency State" => "Most Recent Medical Training State",
            "Previous Residency Country" => "Most Recent Medical Training Country",
//            "" => "Previous Residency Track",
//            "" => "ERAS Application",

//            "" => "",
//            "" => "",
//            "" => "",
//            "" => "",
//            "" => "",

            //Extra fields to identify matched PDF
            "Birth Date" => "Date of Birth",
            "USMLE ID" => "USMLE ID",
            "NBOME ID" => "NBOME ID",
            "NRMP ID" => "NRMP ID"
        );

        return $map;
    }

    //populate row by existing residency application
    public function populateRowByExistedResapp( $rowArr, $residencyApplicationDb, $pdfFile=NULL ) {

        if( !$residencyApplicationDb ) {
            return $rowArr;
        }

        $thisErasApplicantId = $residencyApplicationDb->getErasApplicantId();
        if( $thisErasApplicantId ) {
            $rowArr['ERAS Application ID']['value'] = $thisErasApplicantId;
            $rowArr['ERAS Application ID']['id'] = $residencyApplicationDb->getId();
        }

        $rowArr["AAMC ID"]['value'] = $residencyApplicationDb->getAamcId();
        $rowArr["AAMC ID"]['id'] = $residencyApplicationDb->getId();

        if( $pdfFile ) {
            $rowArr['ERAS Application']['value'] = $pdfFile->getOriginalname();
            $rowArr['ERAS Application']['id'] = $pdfFile->getId();
        }

        $applicantUser = $residencyApplicationDb->getUser();

        $rowArr["Preferred Email"]['value'] = $applicantUser->getEmail();
        $rowArr["Preferred Email"]['id'] = $applicantUser->getId();

        $rowArr["First Name"]['value'] = $applicantUser->getFirstName();
        $rowArr["First Name"]['id'] = $applicantUser->getId();

        $rowArr["Last Name"]['value'] = $applicantUser->getLastName();
        $rowArr["Last Name"]['id'] = $applicantUser->getId();

        $rowArr['Middle Name']['value'] = $applicantUser->getMiddleName();
        $rowArr['Middle Name']['id'] = $applicantUser->getId();

        $applicationReceiptDate = $residencyApplicationDb->getTimestamp();
        if( $applicationReceiptDate ) {
            $rowArr['Application Receipt Date']['value'] = $applicationReceiptDate->format('m/d/Y');;
            $rowArr['Application Receipt Date']['id'] = null;
        }

        $rowArr['Residency Track']['value'] = $residencyApplicationDb->getResidencyTrack()."";
        $rowArr['Residency Track']['id'] = null;

        $trainings = $applicantUser->getTrainings();
        if( count($trainings) > 0 ) {

            $training = NULL;
            $previousTraining = NULL;

            foreach($trainings as $thisTraining) {
                $trainingTypeName = $thisTraining->getTrainingType()."";
                //echo $thisTraining->getId().": trainingTypeName=".$trainingTypeName."<br>";

                if( $trainingTypeName == "Residency" ) {
                    $previousTraining = $thisTraining;
                    continue;
                }

                if( $trainingTypeName == "Medical" ) {
                    $training = $thisTraining;
                    continue;
                }
            }

            //Residency School ($training)
            if( $training ) {
                $institution = $training->getInstitution();
                if ($institution) {
                    $rowArr['Medical School Name']['value'] = $institution . "";
                    $rowArr['Medical School Name']['id'] = $institution->getId();
                }

                $completionDate = $training->getCompletionDate();
                if ($completionDate) {
                    $rowArr['Medical School Graduation Date']['value'] = $completionDate->format('m/d/Y');
                    $rowArr['Medical School Graduation Date']['id'] = null;
                }

                $degree = $training->getDegree();
                if ($degree) {
                    $rowArr['Degree']['value'] = $degree . "";
                    $rowArr['Degree']['id'] = null;
                }
            }

            //Previous Residency ($previousTraining)
            if( $previousTraining ) {
                $previousTrainingStartDate = $previousTraining->getStartDate();
                if ($previousTrainingStartDate) {
                    $rowArr['Previous Residency Start Date']['value'] = $previousTrainingStartDate->format('m/d/Y');
                    $rowArr['Previous Residency Start Date']['id'] = null;
                }
                $previousTrainingCompletionDate = $previousTraining->getCompletionDate();
                if ($previousTrainingCompletionDate) {
                    $rowArr['Previous Residency Graduation/Departure Date']['value'] = $previousTrainingCompletionDate->format('m/d/Y');
                    $rowArr['Previous Residency Graduation/Departure Date']['id'] = null;
                }

                $previousTrainingInstitution = $previousTraining->getInstitution();
                if( $previousTrainingInstitution ) {
                    $rowArr['Previous Residency Institution']['value'] = $previousTrainingInstitution."";
                    $rowArr['Previous Residency Institution']['id'] = null;
                }

                $geoLocation = $previousTraining->getGeoLocation();
                //echo "geoLocation=$geoLocation <br>";
                if( $geoLocation ) {
                    $previousTrainingCity = $geoLocation->getCity();
                    if ($previousTrainingCity) {
                        $rowArr['Previous Residency City']['value'] = $previousTrainingCity . "";
                        $rowArr['Previous Residency City']['id'] = null;
                    }
                    $previousTrainingState = $geoLocation->getState();
                    if ($previousTrainingState) {
                        $rowArr['Previous Residency State']['value'] = $previousTrainingState . "";
                        $rowArr['Previous Residency State']['id'] = null;
                    }
                    $previousTrainingCountry = $geoLocation->getCountry();
                    if ($previousTrainingCountry) {
                        $rowArr['Previous Residency Country']['value'] = $previousTrainingCountry . "";
                        $rowArr['Previous Residency Country']['id'] = null;
                    }
                }

                $previousTrainingResidencyTrack = $previousTraining->getResidencyTrack();
                if( $previousTrainingResidencyTrack ) {
                    $rowArr['Previous Residency Track']['value'] = $previousTrainingResidencyTrack . "";
                    $rowArr['Previous Residency Track']['id'] = null;
                }
            }
            
        }

        $examinations = $residencyApplicationDb->getExaminations();
        if( count($examinations) > 0 ) {
            $examination = $examinations[0];

            $rowArr['USMLE Step 1 Score']['value'] = $examination->getUSMLEStep1Score();
            $rowArr['USMLE Step 1 Score']['id'] = null;

            $rowArr['USMLE Step 2 CK Score']['value'] = $examination->getUSMLEStep2CKScore();;
            $rowArr['USMLE Step 2 CK Score']['id'] = null;

            $rowArr['USMLE Step 3 Score']['value'] = $examination->getUSMLEStep3Score();;
            $rowArr['USMLE Step 3 Score']['id'] = null;
        }

        $citizenships = $residencyApplicationDb->getCitizenships();
        if( count($citizenships) > 0 ) {
            $citizenship = $citizenships[0];
            if( $citizenship ) {
                $rowArr['Country of Citizenship']['value'] = $citizenship->getCountry()."";
                $rowArr['Country of Citizenship']['id'] = null;

                $rowArr['Visa Status']['value'] = $citizenship->getVisa();
                $rowArr['Visa Status']['id'] = null;
            }

        }

        $rowArr['Is the applicant a member of any of the following groups?']['value'] = $residencyApplicationDb->getEthnicity();
        $rowArr['Is the applicant a member of any of the following groups?']['id'] = null;

        $rowArr['Number of first author publications']['value'] = $residencyApplicationDb->getFirstPublications();
        $rowArr['Number of first author publications']['id'] = null;

        $rowArr['Number of all publications']['value'] = $residencyApplicationDb->getAllPublications();;
        $rowArr['Number of all publications']['id'] = null;
        
        if( $residencyApplicationDb->getAoa() ) {
            $aoa = "Yes";
        } else {
            $aoa = "No";
        }
        $rowArr['AOA']['value'] = $aoa;
        $rowArr['AOA']['id'] = null;

        if( $residencyApplicationDb->getCouple() ) {
            $coupleMatch = "Yes";
        } else {
            $coupleMatch = "No";
        }
        $rowArr['Couple’s Match']['value'] = $coupleMatch;
        $rowArr['Couple’s Match']['id'] = null;

        $rowArr['Post-Sophomore Fellowship']['value'] = $residencyApplicationDb->getPostSoph()."";
        $rowArr['Post-Sophomore Fellowship']['id'] = null;

        // Application Season Start Date/Application Season End Date
        $applicationSeasonStartDate = $residencyApplicationDb->getApplicationSeasonStartDate();
        if( $applicationSeasonStartDate ) {
            $rowArr['Application Season Start Date']['value'] = $applicationSeasonStartDate->format('m/d/Y');
            $rowArr['Application Season Start Date']['id'] = null;
        }
        $applicationSeasonEndDate = $residencyApplicationDb->getApplicationSeasonEndDate();
        if( $applicationSeasonEndDate ) {
            $rowArr['Application Season End Date']['value'] = $applicationSeasonEndDate->format('m/d/Y');
            $rowArr['Application Season End Date']['id'] = null;
        }

        // Expected Residency Start Date/Expected Graduation Date
        $residencyStartDate = $residencyApplicationDb->getStartDate();
        if( $residencyStartDate ) {
            $rowArr['Expected Residency Start Date']['value'] = $residencyStartDate->format('m/d/Y');
            $rowArr['Expected Residency Start Date']['id'] = null;
        }
        $residencyEndDate = $residencyApplicationDb->getEndDate();
        if( $residencyEndDate ) {
            $rowArr['Expected Graduation Date']['value'] = $residencyEndDate->format('m/d/Y');
            $rowArr['Expected Graduation Date']['id'] = null;
        }

        return $rowArr;
    }

    //Used in getCsvApplicationsData
    public function checkDuplicate( $rowArr, $handsomtableJsonData ) {
        ////////////// check for duplicate //////////////////
        //$duplicateIds = array();
        $duplicateResapps = array();
        $duplicateArr = array();
        
        //check for duplicate in $handsomtableJsonData
        $duplicateTableResApps = $this->getDuplicateTableResApps($rowArr, $handsomtableJsonData);
        if( $duplicateTableResApps  ) {
            //$rowArr['Status']['id'] = null;
            //$rowArr['Status']['value'] = "Duplicate in batch";
            $duplicateArr[] = "Duplicate in batch";
        } else {
            //$rowArr['Status']['id'] = null;
            //$rowArr['Status']['value'] = "Not Duplicated";
        }
        
        //check for duplicate in DB
        $duplicateDbResApps = $this->getDuplicateDbResApps($rowArr);
        if( count($duplicateDbResApps) > 0  ) {
            //$rowArr['Status']['id'] = implode(",",$duplicateDbResApps);
            //$rowArr['Status']['value'] = "Previously Imported";
            foreach($duplicateDbResApps as $duplicateDbResApp) {
                //$duplicateIds[] = $duplicateDbResApp->getId();
                $duplicateResapps[] = $duplicateDbResApp;
            }
            $duplicateArr[] = "Previously Imported";
        } else {
            //$rowArr['Status']['id'] = null;
            //$rowArr['Status']['value'] = "Not Imported";
        }
//        if( count($duplicateArr) > 0 ) {
//            $rowArr['Status']['id'] = implode(",",$duplicateIds);
//            $rowArr['Status']['value'] = implode(", ",$duplicateArr);
//
//            //change the value in the “Action” column to “Do not add”
//            $rowArr['Action']['id'] = null;
//            $rowArr['Action']['value'] = "Do not add";
//        } else {
//            //No duplicate found => change the value in the “Action” column to “Add”
//            //Testing: comment out below for testing
//            //$rowArr['Action']['id'] = null;
//            //$rowArr['Action']['value'] = "Create New Record";
//        }
        ////////////// EOF check for duplicate //////////////////

        $duplicateRes = array();
        $duplicateRes['duplicateInfoArr'] = $duplicateArr;
        //$duplicateRes['duplicateIdArr'] = $duplicateIds;
        $duplicateRes['duplicateResapps'] = $duplicateResapps;

        return $duplicateRes;
    }
    
    public function addNotUsedPDFtoTable($handsomtableJsonData,$pdfInfoArr,$usedPdfArr,$csvStatus) {
        //return $handsomtableJsonData; //testing

        //get email, LastName FirstName and Date of Birth for each applicant from the current year without a status of Hidden or Archived
        $resapps = $this->getEnabledResapps();
        $resappInfoArr = array();
        foreach($resapps as $resapp) {
            $subjectUser = $resapp->getUser();
            if($subjectUser) {
                //TODO: add key values "ERAS Application ID", "AAMC ID"
                $resappInfoArr[$resapp->getId()] = array(
                    'email' => $subjectUser->getSingleEmail(), 
                    'lastname' => $subjectUser->getSingleLastName(),
                    'firstname' => $subjectUser->getSingleFirstName(),
                    'aamcId' => $resapp->getAamcId(),
                    'erasApplicantId' => $resapp->getErasApplicantId()
                );
            }
        }
        
        //added not used PDF files
        //$notUsedPdfArr = array();
        foreach($pdfInfoArr as $fileId=>$pdfFileArr) {
            if( isset($usedPdfArr[$fileId]) && $usedPdfArr[$fileId] ) {
                //used
            } else {

                $pdfFile = $pdfFileArr['file'];

                //$notUsedPdfArr[$fileId] = $pdfFileArr;
                //$pdfInfoArr[$pdfFile->getId()] = array('file'=>$pdfFile,'text' => $pdfText, 'path' => $pdfFilePath, 'originalName'=>$pdfFile->getOriginalname());
                $rowArr = array();
                $rowArr['ERAS Application']['id'] = $fileId;
                $rowArr['ERAS Application']['value'] = $pdfFileArr['originalName'];
                $rowArr['Status']['id'] = -1;
                $rowArr['Status']['value'] = $csvStatus; //"No match in CSV"; //match not found in CSV file
                //$rowArr['Action']['value'] = "Update PDF & ID Only";
                //$rowArr['Action']['id'] = $residencyApplicationDb->getId();

                //find file has Email, LastName FirstName
//                foreach($resappInfoArr as $resappId=>$resappInfoArr) {
//                    $pdfText = $pdfInfoArr[$resappId]['text'];
//                    $email = $resappInfoArr['email'];
//                    if( $email ) {
//                        if( strpos($pdfText, $email) !== false ) {
//                            //Found by email
//                        }
//                    }
//                }

                $foundResapp = $this->findResappByApplicant($resappInfoArr,$pdfFileArr); //find match for resapp info and given pdf
                if( $foundResapp ) {

                    //check If PDF is Existed In Resapp
                    $existedPDF = $this->checkIfPDFExistInResapp($pdfFile,array($foundResapp));

                    if( $existedPDF === false ) {
                        $rowArr['Action']['value'] = $foundResapp->getAddToStr();
                        $rowArr['Action']['id'] = $foundResapp->getId();

                        $rowArr['Status']['id'] = -1;
                        //$rowArr['Status']['value'] = "No match in CSV, previously uploaded PDF differs"; //match not found in CSV file
                        $rowArr['Status']['value'] = $csvStatus.", new PDF"; //"No match in CSV, new PDF"; //match not found in CSV file
                    } else {
                        $rowArr['Action']['value'] = "Do not add";
                        $rowArr['Action']['id'] = null;

                        $rowArr['Status']['id'] = -1;
                        $rowArr['Status']['value'] = $csvStatus.", same PDF previously uploaded"; //"No match in CSV, same PDF previously uploaded"; //match not found in CSV file
                    }

                } else {
                    //resapp not found
                    $rowArr['Action']['value'] = "Do not add";
                    $rowArr['Action']['id'] = null;

                    $rowArr['Status']['id'] = -1;
                    $rowArr['Status']['value'] = $csvStatus.", unexpected PDF Format"; //"No match in CSV"; //match not found in CSV file
                }

                //$rowArr, $residencyApplicationDb, $erasApplicantId=NULL, $pdfFile=NULL
                $rowArr = $this->populateRowByExistedResapp($rowArr,$foundResapp,$pdfFile);

//                //Add to John Smith’s application (ID 1234)
//                $resappIdArr = array();
//                $resappInfoArr = array();
//                foreach($this->getEnabledResapps() as $resapp) {
//                    echo "resapps=".$resapp->getId()."<br>";
//                    $resappIdArr[] = $resapp->getId();
//                    $resappInfoArr[] = "Add to ".$resapp->getId();
//                    //$rowArr['Action']['id'] = $resapp->getId();
//                    //$rowArr['Action']['value'] = "Add to ".$resapp->getId();
//                }
//                $rowArr['Action']['id'] = $resappIdArr;
//                $rowArr['Action']['value'] = $resappInfoArr;

                $handsomtableJsonData[] = $rowArr;
            }
        }

        return $handsomtableJsonData;
    }
    public function getEnabledResapps($exceptStatusArr=array()) {

        if( count($exceptStatusArr) == 0 ) {
            $archiveStatus = $this->em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("archive");
            if (!$archiveStatus) {
                throw new EntityNotFoundException('Unable to find entity by name=' . "archive");
            }
            $hideStatus = $this->em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("hide");
            if (!$archiveStatus) {
                throw new EntityNotFoundException('Unable to find entity by name=' . "hide");
            }
            $declinedStatus = $this->em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("declined");
            if (!$declinedStatus) {
                throw new EntityNotFoundException('Unable to find entity by name=' . "declined");
            }
            $rejectedStatus = $this->em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("reject");
            if (!$rejectedStatus) {
                throw new EntityNotFoundException('Unable to find entity by name=' . "reject");
            }
            $rejectedandnotifiedStatus = $this->em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("rejectedandnotified");
            if (!$rejectedandnotifiedStatus) {
                throw new EntityNotFoundException('Unable to find entity by name=' . "rejectedandnotified");
            }
            $exceptStatusArr = array($archiveStatus,$hideStatus,$declinedStatus,$rejectedStatus,$rejectedandnotifiedStatus);
        }

        //show the list of current residency applicants that do not have a status of Hidden or Archived for the current year
        $repository = $this->em->getRepository('AppResAppBundle:ResidencyApplication');
        $dql = $repository->createQueryBuilder("resapp");
        $dql->select('resapp');

        //ResAppStatus
        $whereArr = array();
        foreach($exceptStatusArr as $exceptStatus) {
            $whereArr[] = "resapp.appStatus != ".$exceptStatus->getId();
        }
        if( count($whereArr) > 0 ) {
            $whereStr = implode(" AND ", $whereArr);
            $dql->where($whereStr);
            //$dql->where("resapp.appStatus != :archive AND  resapp.appStatus != :hide");
        }

        //$dql->leftJoin("resapp.residencyTrack", "residencyTrack");
        //$dql->leftJoin("resapp.user", "user");
        //$dql->where("residencyTrack.id = :residencyTrackId");
        //$dql->andWhere("user.email = :applicantEmail");
        //$dql->andWhere("resapp.id != :resappId");

        //applicationSeasonStartDate: current application season year: 7-1-2020 to 6-30-2021 (7-1-currentyear to 6-30-nextyear)
        //$startDate = $resapp->getStartDate();
        $startDateStr = date("Y");
        $endDateStr = date("Y",strtotime('+1 year')); //nextyear
        $startDate = $startDateStr."-07-01";
        $endDate = $endDateStr."-06-30";
        $dql->andWhere("resapp.applicationSeasonStartDate BETWEEN '" . $startDate . "'" . " AND " . "'" . $endDate . "'" );

        $query = $this->em->createQuery($dql);

//        $query->setParameters(array(
//            "archive" => $archiveStatus->getId(),
//            "hide" => $hideStatus->getId(),
//        ));

        $resapps = $query->getResult();
        //echo "resapps=".count($resapps)."<br>";

        return $resapps;
    }
    //find match for resapp info and given pdf
    //Used in addNotUsedPDFtoTable
    public function findResappByApplicant($resappInfoArr,$pdfInfoArr) {

        //try to find by filename notation "...aid=12345678.pdf"
        if( isset($pdfInfoArr['originalName']) ) {
            $originalFileName = $pdfInfoArr['originalName'];
            $residencyApplicationDb = $this->findResApplicationByFileName($originalFileName);
            return $residencyApplicationDb;
        }

        $foundResappId = NULL;
        $pdfText = NULL;
        //dump($pdfInfoArr);
        //exit('111');

        if( isset($pdfInfoArr['text']) ) {
            $pdfText = $pdfInfoArr['text'];
        } else {
            $pdfFile = $pdfInfoArr['file'];
            if( $pdfFile ) {
                $pdfFilePath = $pdfFile->getFullServerPath();
                if ($pdfFilePath) {
                    $pdfText = $this->extractPdfText($pdfFilePath);
                    //$pdfInfoArr[$pdfFile->getId()] = array('file' => $pdfFile, 'text' => $pdfText, 'path' => $pdfFilePath, 'originalName' => $pdfFile->getOriginalname());
                }
            }
        }

        if( !$pdfText ) {
            return NULL;
        }

        //find file has Email, LastName FirstName
        foreach($resappInfoArr as $resappId=>$thisResappInfoArr) {

            //Search by "AAMC ID"
            $aamcId = $thisResappInfoArr['aamcId'];
            if( $aamcId ) {
                if( strpos($pdfText, $aamcId) !== false ) {
                    $foundResappId = $resappId;
                    break;
                }
            }

            //Search by "AAMC ID"
            $erasApplicantId = $thisResappInfoArr['erasApplicantId'];
            if( $erasApplicantId ) {
                if( strpos($pdfText, $erasApplicantId) !== false ) {
                    $foundResappId = $resappId;
                    break;
                }
            }

            //Search by email
            $email = $thisResappInfoArr['email'];
            if( $email ) {
                if( strpos($pdfText, $email) !== false ) {
                    //Found by email
                    $foundResappId = $resappId;
                    break;
                }
            }

            //Search by lastname and firstname
            $lastname = $thisResappInfoArr['lastname'];
            if( $lastname ) {
                if( strpos($pdfText, $lastname) !== false ) {
                    //Search by firstname
                    $firstname = $thisResappInfoArr['firstname'];
                    if( $firstname ) {
                        if( strpos($pdfText, $firstname) !== false ) {
                            //Found by firstname
                            $foundResappId = $resappId;
                            break;
                        }
                    }
                }
            }
        }

        if( $foundResappId ) {
            $residencyApplicationDb = $this->em->getRepository('AppResAppBundle:ResidencyApplication')->find($foundResappId);
            return $residencyApplicationDb;
        }

        return NULL;
    }

    //Try to find by a file name notation "...aid=12345678.pdf"
    public function findResApplicationByFileName( $originalFileName ) {
        if( !$originalFileName ) {
            return NULL;
        }
        if(
            //strpos($originalFileName, 'ApplicantID=') !== false ||
            //strpos($originalFileName, 'AID=') !== false ||
            strpos($originalFileName, 'aid') !== false
        ) {
            $originalFileNameSplit = explode('aid',$originalFileName);
            if( count($originalFileNameSplit) > 0 ) {
                $erasApplicantId = $originalFileNameSplit[1]; //2021248381.pdf
                $erasApplicantId = str_replace(".pdf","",$erasApplicantId);
                $erasApplicantId = str_replace("=","",$erasApplicantId);
                $erasApplicantId = str_replace(":","",$erasApplicantId);
                $erasApplicantId = str_replace("-","",$erasApplicantId);
                $erasApplicantId = str_replace("_","",$erasApplicantId);
                //echo "erasApplicantId=$erasApplicantId <br>"; //2021248381.pdf
//                        $aidSplit = explode('_',$aidPart);
//                        if( count($aidSplit) > 0 ) {
//                            $erasApplicantId = $aidSplit[0];
//                        } else {
//                            $aidSplit = explode('.',$aidPart); //End of file before extension "..._aid=12345678.pdf"
//                            if( count($aidSplit) > 0 ) {
//                                $erasApplicantId = $aidSplit[0];
//                            }
//                        }
                //echo "filename erasApplicantId=$erasApplicantId <br>";
                return $this->em->getRepository('AppResAppBundle:ResidencyApplication')->findOneByErasApplicantId($erasApplicantId);
            }
        }
        return NULL;
    }

    //Check if given PDF ($pdfFile) is the same as the most recent PDF in the res applications ($duplicateIds)
    //Use md5_file and file size
    public function checkIfPDFExistInResapp($pdfFile,$duplicateResapps) {

        //return false; //testing

        $thisHash = $this->getDocumentHash($pdfFile);
        $thisSize = $pdfFile->getSize();

        foreach($duplicateResapps as $duplicateResapp) {
            //check all documents (getDocuments)
            foreach($duplicateResapp->getDocuments() as $document) {
                $documentHash = $this->getDocumentHash($document);
                $documentSize = $document->getSize();
                //echo "compare document hash: ".$pdfFile->getOriginalnameClean()."[$thisHash]?=".$document->getOriginalnameClean()."[$documentHash] <br>";
                if( $thisHash && $documentHash && hash_equals($thisHash,$documentHash) ) {
                    //echo "document hash match <br>";
                    //echo "compare document size: ".$pdfFile->getOriginalnameClean()."[$thisSize]?=".$document->getOriginalnameClean()."[$documentSize] <br>";
                    if( $thisSize && $documentSize && $thisSize == $documentSize ) {
                        return true;
                    }
                }
            }

            //check the most recent ERAS (getRecentCoverLetter)
            $recentFile = $duplicateResapp->getRecentCoverLetter();
            if( $recentFile ) {
                $recentFileHash = $this->getDocumentHash($recentFile);
                $recentFileSize = $recentFile->getSize();
                //echo "compare eras hash: ".$pdfFile->getOriginalnameClean()."[$thisHash]?=".$recentFile->getOriginalnameClean()."[$recentFileHash] <br>";
                if ($thisHash && $recentFileHash && hash_equals($thisHash, $recentFileHash)) {
                    //echo "document eras match <br>";
                    //echo "compare eras size: ".$pdfFile->getOriginalnameClean()."[$thisSize]?=".$recentFile->getOriginalnameClean()."[$recentFileSize] <br>";
                    if ($thisSize && $recentFileSize && $thisSize == $recentFileSize) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
    public function getDocumentHash($document) {
        //$filename = $document->getFullServerPath();
        //$md5file = md5_file($filename);
        //return $md5file;

        if( !$document ) {
            return NULL;
        }

        return $document->getOrGenerateSetDocumentHash();
    }

    public function getPdfTextArr( $pdfFiles ) {
        $pdfInfoArr = array();
        foreach( $pdfFiles as $pdfFile ) {
            $pdfFilePath = $pdfFile->getFullServerPath();
            if ($pdfFilePath) {
                $pdfText = $this->extractPdfText($pdfFilePath);
                if ($pdfText) {
                    $pdfInfoArr[$pdfFile->getId()] = array('file'=>$pdfFile,'text' => $pdfText, 'path' => $pdfFilePath, 'originalName'=>$pdfFile->getOriginalname());
                }
            }
        }

        return $pdfInfoArr;
    }
    public function findPdfByInfoArrByAnyKeys($pdfInfoArr, $keysArr) {
        foreach( $pdfInfoArr as $fileId=>$pdfFileArr ) {
            $pdfFile = $pdfFileArr['file'];
            $pdfText = $pdfFileArr['text'];
            if( $pdfText ) {
                foreach ($keysArr as $keyStr) {
                    if( $keyStr ) {
                        if( strpos($pdfText, $keyStr) !== false ) {
                            //echo $pdfOriginalName. ": " . $keyStr." found<br>";
                            return $pdfFile;
                        }
                    }
                }
            }
        }

        return NULL;
    }
    public function findPdfByInfoArrByAllKeys( $pdfInfoArr, $keysArr ) {
        foreach( $pdfInfoArr as $fileId=>$pdfFileArr ) {
            $pdfFile = $pdfFileArr['file'];
            //$pdfOriginalName = $pdfFileArr['originalName'];
            $pdfText = $pdfFileArr['text'];
            //echo "<br>##############".$pdfOriginalName."#############<br>".$pdfText."<br><br>";
            $keyExistCount = 0;
            $totalCount = 0;
            foreach($keysArr as $keyStr) {
                if( $pdfText && $keyStr ) {
                    if( strpos($pdfText, $keyStr) !== false ) {
                        //echo $pdfOriginalName. ": " . $keyStr." found<br>";
                        $keyExistCount++;
                    } else {
                        //echo $pdfOriginalName. ": " . $keyStr." not found<br>";
                    }
                }
                $totalCount++;
            }
            if( $keyExistCount == $totalCount ) {
                return $pdfFile;
            }
        }

        return NULL;
    }
    public function findPdf( $pdfFilePaths, $keysArr ) {
        foreach( $pdfFilePaths as $pdfFilePath ) {
            $pdfText = $this->extractPdfText($pdfFilePath);
            //echo "<br>##############".$pdfFilePath."#############<br>".$pdfText."<br><br>";
            $keyExistCount = 0;
            $totalCount = 0;
            foreach($keysArr as $keyStr) {
                if( $pdfText && $keyStr ) {
                    if( strpos($pdfText, $keyStr) !== false ) {
                        echo $pdfFilePath. ": " . $keyStr." found<br>";
                        $keyExistCount++;
                    } else {
                        echo $pdfFilePath. ": " . $keyStr." not found<br>";
                    }
                }
                $totalCount++;
            }
            if( $keyExistCount == $totalCount ) {
                return $pdfFilePath;
            }
        }

        return NULL;
    }

    //check if duplicate exists, found by:
    //1) $erasApplicantId
    // 'Expected Residency Start Date' AND (email or aamcId or ($firstName and $lastName))
    public function getDuplicateDbResApps($rowArr) {
        //A- By ERAS Application ID, then separately
        //B- By Preferred e-mail + Expected Residency Start Date, and lastly, separately
        //C- By Last Name + First Name + Application Season Start Date + Expected Residency Start Date combination.

        //$aamcId = $rowArr['AAMC ID']['value'];
        $aamcId = NULL;
        if( isset($rowArr['AAMC ID']) ) {
            $aamcId = $rowArr['AAMC ID']['value'];
        }

        $erasApplicantId = NULL;
        if( isset($rowArr['ERAS Application ID']) ) {
            $erasApplicantId = $rowArr['ERAS Application ID']['value'];
        }

        //$email = $rowArr['Preferred Email']['value'];
        $email = NULL;
        if( isset($rowArr['Preferred Email']) ) {
            $email = $rowArr['Preferred Email']['value'];
        }

        $expectedResidencyStartDate = NULL;
        if( isset($rowArr['Expected Residency Start Date']) ) {
            $expectedResidencyStartDate = $rowArr['Expected Residency Start Date']['value']; //07/01/2021
        }

        $applicationReceiptDate = NULL;
        if( isset($rowArr['Application Receipt Date']) ) {
            $applicationReceiptDate = $rowArr['Application Receipt Date']['value']; //10/21/2020
        }

        $lastName = NULL;
        if( isset($rowArr['Last Name']) ) {
            $lastName = $rowArr['Last Name']['value'];
        }
        $firstName = NULL;
        if( isset($rowArr['First Name']) ) {
            $firstName = $rowArr['First Name']['value'];
        }

        //echo "aamcId=[$aamcId], email=[$email], expectedResidencyStartDate=[$expectedResidencyStartDate],
        //    applicationReceiptDate=[$applicationReceiptDate], erasApplicantId=[$erasApplicantId] <br>";

        $parameters = array();

        $repository = $this->em->getRepository('AppResAppBundle:ResidencyApplication');
        $dql = $repository->createQueryBuilder("resapp");
        $dql->select('resapp');
        $dql->leftJoin('resapp.user','user');
        $dql->leftJoin('user.infos','infos');
        $dql->orderBy("resapp.id","DESC"); //descending order when they are arranged from the largest to the smallest number

        //A- By ERAS Application ID, then separately
        if( $erasApplicantId ) {
            $dql->where("resapp.erasApplicantId = :erasApplicantId");
            $parameters["erasApplicantId"] = $erasApplicantId;
            $query = $this->em->createQuery($dql);
            $query->setParameters($parameters);
            $resapps = $query->getResult();
            return $resapps;
        }

        $dql->where("(resapp.aamcId = :aamcId OR LOWER(infos.email) = LOWER(:userInfoEmail) OR LOWER(infos.emailCanonical) = LOWER(:userInfoEmail))");
        $parameters["aamcId"] = $aamcId;
        $parameters["userInfoEmail"] = $email;

        if( $lastName && $firstName ) {
            $dql->orWhere("(LOWER(infos.firstName) = LOWER(:firstName) AND LOWER(infos.lastName) = LOWER(:lastName))");
            $parameters["firstName"] = $firstName;
            $parameters["lastName"] = $lastName;
        }

        if( $expectedResidencyStartDate || $applicationReceiptDate ) {
            //$dql->andWhere("resapp.startDate = :expectedResidencyStartDate");
            $dateStrArr = array();
            if( $expectedResidencyStartDate ) {
                $dateStrArr[] = "resapp.startDate = :expectedResidencyStartDate"; //startDate(date) in DB: 2019-07-01
                $parameters["expectedResidencyStartDate"] = $expectedResidencyStartDate;
            }
            if( $applicationReceiptDate ) {
                $dateStrArr[] = "resapp.timestamp >= :applicationReceiptDate"; //timestamp(datetime) in DB: 2018-09-29 10:00:00
                $parameters["applicationReceiptDate"] = $applicationReceiptDate;
            }
            if( count($dateStrArr) > 0 ) {
                $dateStr = implode(" OR ",$dateStrArr);
                if( $dateStr ) {
                    $dateStr = "(".$dateStr.")";
                    //echo "[$expectedResidencyStartDate], [$applicationReceiptDate]: dateStr=[$dateStr] <br>";
                    $dql->andWhere($dateStr);
                }
            }
        }

        $query = $this->em->createQuery($dql);
        //$query->setMaxResults(10);

        $query->setParameters($parameters);
        $resapps = $query->getResult();

        //echo "sql=".$query->getSql()."<br>";
        //echo "resapps count=".count($resapps)."<br>";
        //exit('111');

        return $resapps;
    }

    public function getDuplicateTableResApps( $rowArr, $handsomtableJsonData, $thisRow=null ) {
        if( count($handsomtableJsonData) == 0 ) {
            return NULL;
        }
        //dump($handsomtableJsonData);
        //exit('111');

//        $actionValue = NULL;
//        if( isset($rowArr['Action']) ) {
//            $actionValue = $rowArr['Action']['value'];
//        }
//        echo "getDuplicateTableResApps: actionValue=[".$actionValue."]<br>";

        $aamcId = $rowArr['AAMC ID']['value'];
        $expectedResidencyStartDate = $rowArr['Expected Residency Start Date']['value'];
        $email = $rowArr['Preferred Email']['value'];
        $lastName = $rowArr['Last Name']['value'];
        $firstName = $rowArr['First Name']['value'];

        $erasApplicantId = NULL;
        if( isset($rowArr['ERAS Application ID']) ) {
            $erasApplicantId = $rowArr['ERAS Application ID']['value'];
        }

        //make the first row index 1
        $rowCount = 0;

        foreach($handsomtableJsonData as $thisRowArr) {

            $rowCount++;

            $actionValue = NULL;
            if( isset($thisRowArr['Action']) ) {
                $actionValue = $thisRowArr['Action']['value'];
            }
            //echo "getDuplicateTableResApps: actionValue=[".$actionValue."]<br>";

            if( $actionValue != "Create New Record" ) {
                //$rowCount++;
                continue;
            }
            
            if( $thisRow && $rowCount == $thisRow ) {
                //$rowCount++;
                continue; //skip the same row
            }
            
            //$rowCount++;

            $thisAamcId = $thisRowArr['AAMC ID']['value'];
            $thisExpectedResidencyStartDate = $thisRowArr['Expected Residency Start Date']['value'];
            $thisEmail = $thisRowArr['Preferred Email']['value'];
            $thisLastName = $thisRowArr['Last Name']['value'];
            $thisFirstName = $thisRowArr['First Name']['value'];
            //echo "thisAamcId=$thisAamcId, thisLastName=$thisLastName<br>";

            $thisErasApplicantId = NULL;
            if( isset($rowArr['ERAS Application ID']) ) {
                $thisErasApplicantId = $rowArr['ERAS Application ID']['value'];
            }

            $erasApplicantIdSame = false; //ignore by default
            if( $erasApplicantId && $thisErasApplicantId ) {
                if( $erasApplicantId == $thisErasApplicantId ) {
                    $erasApplicantIdSame = true;
                } else {
                    $erasApplicantIdSame = false;
                }
            }
            if( $erasApplicantIdSame ) {
                return $thisRowArr;
            }

//            echo     "[$aamcId]=[$thisAamcId],"
//                    ."[$email]=[$thisEmail],"
//                    ."[$lastName=$thisLastName],"
//                    ."[$expectedResidencyStartDate]=[$thisExpectedResidencyStartDate],"
//                    ."[$erasApplicantId]=[$thisErasApplicantId]"
//                    ."<br>";

//            if(
//                $aamcId == $thisAamcId
//                && $expectedResidencyStartDate == $thisExpectedResidencyStartDate
//                && $email == $thisEmail
//                //&& $lastName == $thisLastName
//                && $erasApplicantIdSame
//            ) {
//                //echo "Duplicate!!!<br>";
//                return $thisRowArr;
//            } else {
//                //echo "NoDuplicate<br>";
//            }

            if( $expectedResidencyStartDate == $thisExpectedResidencyStartDate ) {

                if( $aamcId == $thisAamcId ) {
                    return $thisRowArr;
                }

                if( $email == $thisEmail ) {
                    return $thisRowArr;
                }

                if( $lastName && $thisLastName && $firstName && $thisFirstName ) {
                    if ($lastName == $thisLastName && $firstName == $thisFirstName) {
                        return $thisRowArr;
                    }
                }

            } else {
                //echo "NoDuplicate<br>";
            }

        }//foreach

        return NULL;
    }

    //get year 9/29/2018 m/d/Y
    public function getYear( $cellValue ) {
        //list($month, $day, $year) = explode("/", $cellValue);
        //echo "$cellValue: year=$year <br>";
        //return $year;
        $datetime = strtotime($cellValue);
        $year = date("Y", $datetime);
        //echo "$cellValue: year=$year <br>";
        return $year;
    }

    //NOT USED
    public function getPdfFilesInSameFolder($inputFileName) {
        $pathParts = pathinfo($inputFileName);
        $folderPath = $pathParts['dirname'];
        $files = scandir($folderPath);

        $pdfFilePaths = array();
        foreach($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            //echo "filePath ext=".$ext."<br>";
            if( $ext == 'pdf' ) {
                $pdfFilePaths[] = $file;
            }
        }

        return $pdfFilePaths;
    }

    public function extractPdfText($path,$asText=true) {
        return $this->extractPdfTextSpatie($path, $asText);
        //return $this->parsePdfCirovargas($path); //free alternative
    }
    //based on pdftotext. which pdftotext
    public function extractPdfTextSpatie($path, $asText=false) {

        if (file_exists($path)) {
            //echo "The file $path exists <br>";
        } else {
            echo "The file $path does not exist <br>";
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        // /mingw64/bin/pdftotext C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\temp\eras.pdf -

        //$pdftotextPath = '/mingw64/bin/pdftotext';
        $pdftotextPath = '/bin/pdftotext';

        if( $userServiceUtil->isWinOs() ) {
            //$pdftotextPath = '/mingw64/bin/pdftotext';
            //"C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\olegutil\pdftotext\bin64\pdftotext"
            $pdftotextPath = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\olegutil\\pdftotext\\bin64\\pdftotext";
        } else {
            $pdftotextPath = '/bin/pdftotext';
        }

        $pdftotext = new Pdf($pdftotextPath);

        //$path = "C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/temp/eras_gs.pdf";
        //$path = '"'.$path.'"';
        //$path = "'".$path."'";
        $path = realpath($path);
        //echo "Spatie source pdf path=".$path."<br>";

        $text = $pdftotext->setPdf($path)->text();
        //echo $text."<br><br>";
        //dump($text);
        //exit(111);

        if( $asText ) {
            return $text;
        }

//        $startStr = "Applicant ID:";
//        $endStr = "AAMC ID:";
//        $field = $this->getPdfField($text,$startStr,$endStr);
//        if( $field ) {
//            echo "Spatie: $startStr=[" . $field . "]<br>";
//        }

        $keysArr = $this->getKeyFields($text);

        //echo "keysArr=".count($keysArr)."<br>";
        //dump($keysArr);

        return $keysArr;
    }
    public function parsePdfCirovargas($path) {

        if (file_exists($path)) {
            //echo "The file $path exists<br>";
        } else {
            echo "The file $path does not exist<br>";
        }

        $field = null;

        if(0) {
            $resappRepGen = $this->container->get('resapp_reportgenerator');
            $processedFiles = $resappRepGen->processFilesGostscript(array($path));

            if (count($processedFiles) > 0) {
                $dir = dirname($path);
                $path = $processedFiles[0];
                $path = str_replace('"', '', $path);
                //$path = $dir.DIRECTORY_SEPARATOR.$path;
                $path = "C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/temp/eras_gs.pdf";
                echo "path=" . $path . "<br>";
            } else {
                return null;
            }
        }

        //$path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\eras_gs.pdf";
        //$path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\PackingSlip.pdf";

        $pdfService = new PDFService();
        $text = $pdfService->pdf2text($path);

        if('' == trim($text)) {
            //echo "Use parseFile:<br>";
            $text = $pdfService->parseFile($path);
        }

//        $startStr = "Applicant ID:";
//        $endStr = "AAMC ID:";
//        $field = $this->getPdfField($text,$startStr,$endStr);
//        echo "Cirovargas: $startStr=[".$field."]<br>";
        //exit();

        //dump($text);
        //exit();

        return $text;
    }

    public function findKeyInDocument($pdfFile,$key) {
        $pdfFilePath = $pdfFile->getFullServerPath();
        if ($pdfFilePath) {
            $pdfText = $this->extractPdfText($pdfFilePath);
            if ($pdfText) {
                $keyValue = $this->getSingleKeyField($pdfText,$key);
                return $keyValue;
                //$pdfInfoArr[$pdfFile->getId()] = array('file'=>$pdfFile,'text' => $pdfText, 'path' => $pdfFilePath, 'originalName'=>$pdfFile->getOriginalname());
            }
        }
        return NULL;
    }
    //$key = 'Applicant ID'
    public function getSingleKeyField($text,$key) {
        $keyFields = $this->getKeyFieldArr();

        //$fieldsArr = $keyFields[$key];
        $fieldsArr = $this->getKeyArr($keyFields,$key);
        if( !$fieldsArr ) {
            return NULL;
        }

        //$fieldsArr[] = array('field'=>"Applicant ID:",'startAnchor'=>"Applicant ID:",'endAnchor'=>$endArr,'length'=>11);
        $thisKey = NULL; //field to find
        if( isset($fieldsArr['field']) ) {
            $thisKey = $fieldsArr['field'];
        }
        if( $thisKey && $thisKey == $key ) {
            //OK
        } else {
            return NULL;
        }

        $startAnchor = NULL;
        $endAnchorArr = NULL;
        $length = NULL;
        $minLength = 2;
        $maxLength = 200;
        if( isset($fieldsArr['startAnchor']) ) {
            $startAnchor = $fieldsArr['startAnchor'];
        }
        if( isset($fieldsArr['endAnchor']) ) {
            $endAnchorArr = $fieldsArr['endAnchor'];
        }
        if( isset($fieldsArr['length']) ) {
            $length = $fieldsArr['length'];
        }
        if( isset($fieldsArr['minLength']) ) {
            $minLength = (int)$fieldsArr['minLength'];
        }
        if( isset($fieldsArr['maxLength']) ) {
            $maxLength = (int)$fieldsArr['maxLength'];
        }
        $field = $this->getShortestField($text, $startAnchor, $endAnchorArr, $minLength, $length, $maxLength);
        return $field;
    }

    public function getKeyFields($text) {

        $keysArr = array();

        foreach( $this->getKeyFieldArr() as $fieldsArr ) {
            //echo "key=$key<br>";

            //$fieldsArr[] = array('field'=>"Applicant ID:",'startAnchor'=>"Applicant ID:",'endAnchor'=>$endArr,'length'=>11);
            $key = NULL; //field to find
            $startAnchor = NULL;
            $endAnchorArr = NULL;
            $length = NULL;
            $minLength = 2;
            $maxLength = 200;
            //$minLength = NULL;
            //$maxLength = NULL;
            if( isset($fieldsArr['field']) ) {
                $key = $fieldsArr['field'];
            }
            if( isset($fieldsArr['startAnchor']) ) {
                $startAnchor = $fieldsArr['startAnchor'];
            }
            if( isset($fieldsArr['endAnchor']) ) {
                $endAnchorArr = $fieldsArr['endAnchor'];
            }
            if( isset($fieldsArr['length']) ) {
                $length = $fieldsArr['length'];
                //echo $key.": length=$length<br>";
            }
            if( isset($fieldsArr['minLength']) ) {
                $minLength = (int)$fieldsArr['minLength'];
                //echo $key.": minLength=$minLength<br>";
            }
            if( isset($fieldsArr['maxLength']) ) {
                $maxLength = (int)$fieldsArr['maxLength'];
                //echo $key.": maxLength=$maxLength<br>";
            }

//            foreach($fieldsArr as $endStr) {
//                $field = $this->getPdfField($text, $startAnchor, $endStr);
//            }
            //echo $key.": minLength=$minLength<br>";
            $field = $this->getShortestField($text, $startAnchor, $endAnchorArr, $minLength, $length, $maxLength);

            if( $field ) {

                //Exception
                if( $key == "Email:" ) {
                    $emailStrArr = explode(" ",$field);
                    foreach($emailStrArr as $emailStr) {
                        if (strpos($emailStr, '@') !== false) {
                            //echo 'true';
                            $field = $emailStr;
                            break;
                        }
                    }
                }
                //Exception
                if( $key == "Applicant ID:" ) {
                    $applicationIdStrArr = explode(" ",$field);
                    if( count($applicationIdStrArr) > 0 ) {
                        $field = $applicationIdStrArr[0];
                    }
                }

                //echo "$key=[" . $field . "]<br>";
                $keysArr[$key] = $field;
            }
        }
        return $keysArr;
    }

    public function getKeyFieldArr() {
        $userSecUtil = $this->container->get('user_security_utility');
        $keyFieldJson = $userSecUtil->getSiteSettingParameter('dataExtractionAnchor',$this->container->getParameter('resapp.sitename'));
        //$keyFieldJson = '{"Applicant ID:":"Applicant ID:,AAMC ID:,Email:","b":2,"c":3,"d":4,"e":5}';

        //// EOF get default JSON /////
        //$keyFieldJson = json_encode($this->getDefaultKeyFieldArr()); //testing
        //echo $keyFieldJson;
        //exit('111');
        //// EOF get default JSON /////

        if( $keyFieldJson ) {
            $keyFieldArr = json_decode($keyFieldJson, true); //json to associative arrays
            //echo "keyFieldArr=[".$keyFieldArr."]<br>";
            //dump($keyFieldArr);
            //exit('111');
            if( count($keyFieldArr) > 0 ) {
                return $keyFieldArr;
            }
        }

        //use default anchors
        return $this->getDefaultKeyFieldArr();
    }
    public function getDefaultKeyFieldArr() {

        /////// endArr ///////
        $endArr = array();
        $endArr[] = "Applicant ID:";
        $endArr[] = "AAMC ID:";
        $endArr[] = "Email:";
        $endArr[] = "Birth Date:";
        $endArr[] = "USMLE ID:";
        $endArr[] = "NBOME ID:";
        $endArr[] = "NRMP ID:";

        $endArr[] = "Most Recent Medical School:";
        $endArr[] = "Gender:";
        $endArr[] = "Previous Last";
        $endArr[] = "Previous Last Name:";
        $endArr[] = "Authorized to Work in the US:";
        $endArr[] = "Participating in the NRMP Match:";

        $endArr[] = "Authorized to Work in the US:";
        $endArr[] = "Current Work Authorization:";
        $endArr[] = "Permanent Mailing Address:";
        $endArr[] = "Preferred Phone #:";
        $endArr[] = "Alternate Phone #:";
        $endArr[] = "Self Identification:";
        /////// EOF endArr ///////

//        $fieldsArr = array();
//        $fieldsArr["Applicant ID:"] = array('endAnchor'=>$endArr,'length'=>11); //' 2021248381' => length=space+10=11
//        $fieldsArr["AAMC ID:"] = array('endAnchor'=>$endArr,'length'=>9); //' 14003481' => length=space+8=9
//        $fieldsArr["Email:"] = array('endAnchor'=>$endArr,'length'=>NULL);
//        $fieldsArr["Name:"] = array('endAnchor'=>$endArr,'length'=>NULL);
//        $fieldsArr["Birth Date:"] = array('endAnchor'=>$endArr,'length'=>NULL);
//        $fieldsArr["USMLE ID:"] = array('endAnchor'=>$endArr,'length'=>NULL);
//        $fieldsArr["NBOME ID:"] = array('endAnchor'=>$endArr,'length'=>NULL);
//        $fieldsArr["NRMP ID:"] = array('endAnchor'=>$endArr,'length'=>NULL);
//        $fieldsArr["Gender:"] = array('endAnchor'=>$endArr,'length'=>NULL);
//        $fieldsArr["Participating as a Couple in NRMP:"] = array('endAnchor'=>$endArr,'length'=>NULL);
//        $fieldsArr["Present Mailing Address:"] = array('endAnchor'=>$endArr,'length'=>NULL);
//        $fieldsArr["Preferred Phone #:"] = array('endAnchor'=>$endArr,'length'=>NULL);

        $fieldsArr = array();
        $fieldsArr[] = array('field'=>"Applicant ID:",'startAnchor'=>"Applicant ID:",'endAnchor'=>$endArr,'minLength'=>10,'length'=>11,'maxLength'=>11); //' 2021248381' => length=space+10=11
        $fieldsArr[] = array('field'=>"AAMC ID:",'startAnchor'=>"AAMC ID:",'endAnchor'=>$endArr,'minLength'=>8,'length'=>9,'maxLength'=>9); //' 14003481' => length=space+8=9
        $fieldsArr[] = array('field'=>"Email:",'startAnchor'=>"Email:",'endAnchor'=>$endArr,'minLength'=>NULL,'length'=>NULL,'maxLength'=>NULL);
        $fieldsArr[] = array('field'=>"Name:",'startAnchor'=>"Name:",'endAnchor'=>$endArr,'minLength'=>NULL,'length'=>NULL,'maxLength'=>NULL);
        $fieldsArr[] = array('field'=>"Birth Date:",'startAnchor'=>"Birth Date:",'endAnchor'=>$endArr,'minLength'=>NULL,'length'=>NULL,'maxLength'=>NULL);
        $fieldsArr[] = array('field'=>"USMLE ID:",'startAnchor'=>"USMLE ID:",'endAnchor'=>$endArr,'minLength'=>NULL,'length'=>NULL,'maxLength'=>NULL);
        $fieldsArr[] = array('field'=>"NBOME ID:",'startAnchor'=>"NBOME ID:",'endAnchor'=>$endArr,'minLength'=>NULL,'length'=>NULL,'maxLength'=>NULL);
        $fieldsArr[] = array('field'=>"NRMP ID:",'startAnchor'=>"NRMP ID:",'endAnchor'=>$endArr,'minLength'=>NULL,'length'=>NULL,'maxLength'=>NULL);
        $fieldsArr[] = array('field'=>"Gender:",'startAnchor'=>"Gender:",'endAnchor'=>$endArr,'minLength'=>NULL,'length'=>NULL,'maxLength'=>NULL);
        $fieldsArr[] = array('field'=>"Participating as a Couple in NRMP:",'startAnchor'=>"Participating as a Couple in NRMP:",'endAnchor'=>$endArr,'minLength'=>NULL,'length'=>NULL,'maxLength'=>NULL);
        $fieldsArr[] = array('field'=>"Present Mailing Address:",'startAnchor'=>"Present Mailing Address:",'endAnchor'=>$endArr,'minLength'=>NULL,'length'=>NULL,'maxLength'=>NULL);
        $fieldsArr[] = array('field'=>"Preferred Phone #:",'startAnchor'=>"Preferred Phone #:",'endAnchor'=>$endArr,'minLength'=>NULL,'length'=>NULL,'maxLength'=>NULL);

        return $fieldsArr;
    }
    public function getKeyArr( $keyFields, $key ) {
        if( $keyFields && count($keyFields) > 0 && $key ) {
            foreach($keyFields as $fieldsArr) {
                if( isset($fieldsArr['field']) ) {
                    if( $fieldsArr['field'] == $key ) {
                        return $fieldsArr;
                    }
                }
            }
        }
        return NULL;
    }
    public function getShortestField($text, $startAnchor, $endAnchorArr, $minLength, $length, $maxLength) {
        //echo "startAnchor=[$startAnchor] <br>";
        //echo "$text <br><br>";

        if( $endAnchorArr && count($endAnchorArr) > 0 ) {
            $thisMinLength = NULL;
            $thisMinField = NULL;
            foreach($endAnchorArr as $endAnchorStr) {
                $field = $this->getPdfField($text,$startAnchor,$endAnchorStr,$length);
                $fieldLen = strlen($field);
                if( $thisMinLength === NULL || $fieldLen <= $thisMinLength ) {
                    $thisMinLength = $fieldLen;
                    $thisMinField = $field;
                }
            }

            if( $this->isValidFieldLength($thisMinField, $minLength, $maxLength) ) {
                return $thisMinField;
            }
        }

        if( $length ) {
            //echo "try length=$length <br>";
            $field = $this->getPdfField($text,$startAnchor,NULL,$length);
            if( $this->isValidFieldLength($field, $minLength, $maxLength) ) {
                return $field;
            }
        }

        return NULL;
    }
    public function isValidFieldLength( $field, $minLength, $maxLength ) {
        $valid = true;
        $len = strlen($field);
        if( $minLength ) {
            if( $len >= $minLength ) {
                $valid = true;
            } else {
                $valid = false;
            }
        }
        if( $maxLength ) {
            if( $len <= $maxLength ) {
                $valid = true;
            } else {
                $valid = false;
            }
        }
        return $valid;
    }

    public function getPdfField($text,$startStr,$endStr,$length=NULL) {
        //echo "startStr=[$startStr] <br>";
        //$startStr = "Applicant ID:";
        //$endStr = "AAMC ID:";

        //if startStr does not exists => return NULL
        if( strpos($text, $startStr) === false ) {
            return NULL;
        }

        if( $endStr ) {
            $field = $this->string_between_two_string2($text, $startStr, $endStr);
            //$field = NULL; //testing
            //echo "field=[".$field ."]<br>";
            if( $field ) {
                $field = trim($field);
                //$field = str_replace(" ","",$field);
                //$field = str_replace("\t","",$field);
                //$field = str_replace("\t\n","",$field);
                $field = str_replace("'", '', $field);
                $field = preg_replace('/(\v|\s)+/', ' ', $field);
                //echo "$startStr=[".$field."]<br>";
                //echo "Page $counter: <br>";
                //dump($text);
                //echo "Page=[".$text."]<br>";
                //exit("string found $startStr");
                //exit();
                $field = trim($field);
                return $field;
            }
        }

        //try to use $length
        if( $length ) {
            //echo "length=[$length]<br>";
            //dump($text);
            //echo "text=".$text."<br>";
            $subtring_start = strpos($text, $startStr);
            //echo "1subtring_start=$subtring_start <br>";
            //echo "strlen($startStr)=".strlen($startStr)."<br>";
            $subtring_start = $subtring_start + strlen($startStr);
            //echo "2subtring_start=$subtring_start <br>";
            $field = substr($text, $subtring_start, $length);
            $field = trim($field);
            //echo "field=[$field]<br>";
            //exit("EOF getPdfField");
            return $field;
        }

        return null;
    }
    public function string_between_two_string($str, $starting_word, $ending_word)
    {
        $subtring_start = strpos($str, $starting_word);
        //Adding the strating index of the strating word to
        //its length would give its ending index
        $subtring_start += strlen($starting_word);
        //Length of our required sub string
        $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
        // Return the substring from the index substring_start of length size
        return substr($str, $subtring_start, $size);
    }
    public function string_between_two_string2($str, $starting_word, $ending_word){
        $arr = explode($starting_word, $str);
        if (isset($arr[1])){
            $arr = explode($ending_word, $arr[1]);
            return $arr[0];
        }
        return '';
    }

    protected static function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            //throw new \InvalidArgumentException("$dirPath must be a directory");
            return false;
        }
        //if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        //    $dirPath .= '/';
        //}
        if (substr($dirPath, strlen($dirPath) - 1, 1) != DIRECTORY_SEPARATOR) {
            $dirPath .= DIRECTORY_SEPARATOR;
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
    public function deletePublicDir($dirPath) {
        $this->deleteDir($dirPath);
    }



    //Compressed PDF is version > 1.4
    //Used for setasign (not used anymore).
    public function isPdfCompressed($pdfPath) {

        $pdfversion = $this->getPdfVersion($pdfPath);

        if( !$pdfversion ) {
            return false;
        }

        if( $pdfversion > "1.4" ){
            // proceed if PDF version greater than 1.4
            // convert with ghostscript to version 1.4

            return true;
        }
        else{
            // proceed if PDF version upto 1.4
            return false;
        }

        return false;
    }
    public function getPdfVersion($pdfPath) {
        // pdf version information
        $filepdf = fopen($pdfPath,"r");
        if ($filepdf) {
            $line_first = fgets($filepdf);
            fclose($filepdf);

            // extract number such as 1.4 ,1.5 from first read line of pdf file
            preg_match_all('!\d+!', $line_first, $matches);
            // save that number in a variable
            $pdfversion = implode('.', $matches[0]);
            echo "pdfversion=$pdfversion <br>";

            return $pdfversion;

        } else{
            echo "error opening the file.";
            exit();
        }

        return null;
    }

    //Not Used
    public function extractDataPdf( $pdfDocument ) {

        $resappRepGen = $this->container->get('resapp_reportgenerator');
        $userSecUtil = $this->container->get('user_security_utility');

        $tempdir = null;
        $processedGsFile = null;

        $pdfDocumentId = $pdfDocument->getId();
        $pdfPath = $pdfDocument->getAttachmentEmailPath();

        //testing
        if(0) {
            $projectRoot = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
            $parentRoot = str_replace('order-lab', '', $projectRoot);
            $parentRoot = str_replace('orderflex', '', $parentRoot);
            $parentRoot = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, '', $parentRoot);
            $filename = "eras_gs.pdf";
            //$filename = "eras.pdf";
            $pdfPath = $parentRoot . DIRECTORY_SEPARATOR . "temp" . DIRECTORY_SEPARATOR . $filename;
            //echo "pdfPath=$pdfPath<br>";
        }

        echo "pdfPath=$pdfPath<br>";
        if( $this->isPdfCompressed($pdfPath) ) {
            echo "Compressed <br>";

            //process via GhostCript
            if(1) {

                //$resappuploadpath = $userSecUtil->getSiteSettingParameter('resappuploadpath');

                //1) create temp dir
                //Uploaded\resapp\documents
                $uploadedFolder = realpath($this->uploadPath);
                //echo "destinationFolder=".$destinationFolder."<br>";
                if( !file_exists($uploadedFolder) ) {
                    echo "Create destination folder [$uploadedFolder]<br>";
                    mkdir($uploadedFolder, 0700, true);
                    chmod($uploadedFolder, 0700);
                }

                $tempdir = $uploadedFolder.DIRECTORY_SEPARATOR.'temp_'.$pdfDocumentId; //Uploaded\resapp\documents
                if( !file_exists($tempdir) ) {
                    //echo "Create destination temp folder [$tempdir]<br>";
                    mkdir($tempdir, 0700, true);
                    chmod($tempdir, 0700);
                }

                //2) copy to temp dir
                //$outFilename = pathinfo($file, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($file, PATHINFO_FILENAME) . "_gs.pdf";
                //$tempPdfFile = $tempdir . DIRECTORY_SEPARATOR . pathinfo($pdfPath, PATHINFO_FILENAME) . "_gs.pdf";
                $tempPdfFile = $tempdir . DIRECTORY_SEPARATOR . pathinfo($pdfPath, PATHINFO_FILENAME) . ".pdf";
                if( !file_exists($tempPdfFile) ) {
                    if( !copy($pdfPath, $tempPdfFile ) ) {
                        echo "failed to copy $pdfPath...\n<br>";
                        $errorMsg = "Residency Application document $pdfPath - Failed to copy to destination folder; filePath=".$tempPdfFile;
                        exit($errorMsg);
                    }
                }

                if( !file_exists($tempPdfFile) ) {
                    //echo "failed to copy $filePath...\n<br>";
                    $errorMsg = "Residency Application document $pdfPath - Failed to copy to destination folder; filePath=".$tempPdfFile;
                    exit($errorMsg);
                }

                //3) process via GhostCript
                $processedFiles = $resappRepGen->processFilesGostscript(array($tempPdfFile));
                if (count($processedFiles) > 0) {
                    //$dir = dirname($erasFilePath);
                    $processedGsFile = $processedFiles[0];
                    $processedGsFile = str_replace('"', '', $processedGsFile);
                    //$path = $dir.DIRECTORY_SEPARATOR.$path;
                    //$path = "C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/temp/eras_gs.pdf";
                    echo "processedGsFile=" . $processedGsFile . "<br>";
                } else {
                    return null;
                }
            }

        } else {
            echo "Not Compressed (version < 1.4) <br>";
        }//isPdfCompressed

        if( $processedGsFile ) {
            $parsedDataArr = $this->parsePdfSpatie($processedGsFile);
            //dump($parsedDataArr);
            //exit("GS processed");
        } else {
            $parsedDataArr = $this->parsePdfSpatie($pdfPath);
            //dump($parsedDataArr);
        }

        if( $tempdir ) {
            //echo "Delete temp dir: $tempdir <br>";
            $this->deleteDir($tempdir);
        }

        return $parsedDataArr;
    }
    //based on pdftotext. which pdftotext
    public function parsePdfSpatie($path) {

        if (file_exists($path)) {
            //echo "The file $path exists <br>";
        } else {
            echo "The file $path does not exist <br>";
        }

        $userServiceUtil = $this->container->get('user_service_utility');

        // /mingw64/bin/pdftotext C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\temp\eras.pdf -

        //$pdftotextPath = '/mingw64/bin/pdftotext';
        $pdftotextPath = '/bin/pdftotext';

        if( $userServiceUtil->isWinOs() ) {
            //$pdftotextPath = '/mingw64/bin/pdftotext';
            //"C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\olegutil\pdftotext\bin64\pdftotext"
            $pdftotextPath = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\olegutil\\pdftotext\\bin64\\pdftotext";
        } else {
            $pdftotextPath = '/bin/pdftotext';
        }

        $pdftotext = new Pdf($pdftotextPath);

        //$path = "C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/temp/eras_gs.pdf";
        //$path = '"'.$path.'"';
        //$path = "'".$path."'";
        $path = realpath($path);
        //echo "Spatie source pdf path=".$path."<br>";

        $text = $pdftotext->setPdf($path)->text();
        //dump($text);

//        $startStr = "Applicant ID:";
//        $endStr = "AAMC ID:";
//        $field = $this->getPdfField($text,$startStr,$endStr);
//        if( $field ) {
//            echo "Spatie: $startStr=[" . $field . "]<br>";
//        }

        $keysArr = $this->getKeyFields($text);

        //echo "keysArr=".count($keysArr)."<br>";
        //dump($keysArr);

        return $keysArr;
    }

    public function getValueByHeaderName($header, $row, $headers) {

        $res = array();

        $key = array_search($header, $headers);

        //$res['val'] = $row[$key]['value'];
        if( array_key_exists('value',$row[$key]) ) {
            $res['val'] = trim($row[$key]['value']);
        } else {
            $res['val'] = null;
        }

        $id = null;

        if( array_key_exists('id', $row[$key]) ) {
            $id = trim($row[$key]['id']);
            //echo "id=".$id.", val=".$res['val']."<br>";
        }

        $res['id'] = $id;

        //echo $header.": key=".$key.": id=".$res['id'].", val=".$res['val']."<br>";
        return $res;
    }
    
    //    public function getKeyFields_Single($text) {
//
//        $keysArr = array();
//
//        foreach( $this->getKeyFieldArr_Single() as $key=>$endStr ) {
//            echo "key=$key, endStr=$endStr<br>";
//            $field = $this->getPdfField($text,$key,$endStr);
//            if( $field ) {
//
//                //Exception
//                if( $key == "Email:" ) {
//                    $emailStrArr = explode(" ",$field);
//                    foreach($emailStrArr as $emailStr) {
//                        if (strpos($emailStr, '@') !== false) {
//                            //echo 'true';
//                            $field = $emailStr;
//                            break;
//                        }
//                    }
//                }
//                //Exception
//                if( $key == "Applicant ID:" ) {
//                    $applicationIdStrArr = explode(" ",$field);
//                    if( count($applicationIdStrArr) > 0 ) {
//                        $field = $applicationIdStrArr[0];
//                    }
//                }
//
//                echo "$key=[" . $field . "]<br>";
//                $keysArr[$key] = $field;
//            }
//        }
//        return $keysArr;
//    }
//    public function getKeyFieldArr_Single() {
//
//        $fieldsArr = array();
//
//        $fieldsArr["Applicant ID:"] = "AAMC ID:";
//        $fieldsArr["AAMC ID:"] = "Most Recent Medical School:";
//        $fieldsArr["Email:"] = "Gender:";
//        $fieldsArr["Name:"] = "Previous Last Name:";
//        $fieldsArr["Birth Date:"] = "Authorized to Work in the US:";
//        $fieldsArr["USMLE ID:"] = "NBOME ID:";
//        $fieldsArr["NBOME ID:"] = "Email:";
//        $fieldsArr["NRMP ID:"] = "Participating in the NRMP Match:";
//
//        return $fieldsArr;
//    }

    public function getEmbedPdf( $pdfDocument ) {
        if( !$pdfDocument ) {
            return NULL;
        }

        //docSnapshotPath = path(sitename~'_file_view', { 'id': documentEntity.id, 'viewType':viewType})
        //$pdfDocumentPath = $pdfDocument->getAttachmentEmailPath();
        //$pdfDocumentPath = $pdfDocument->getServerPath();
        $pdfDocumentPath = $pdfDocument->getAbsoluteUploadFullPath();
        //$pdfDocumentPath = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\ResidencyImport\\Test2\\testPdf_StevenAdamsFull.pdf";

        if( !$pdfDocumentPath ) {
            return NULL;
        }
        //echo "pdfDocumentPath=$pdfDocumentPath<br>";

        //$embedPdfHtml = '<embed src="'.$pdfDocumentPath.'" type="application/pdf" width="800px" height="2100px" class="responsive">';
        //$embedPdfHtml = '<embed src="'.$pdfDocumentPath.'" width="100%" height="100%" >';
        //$embedPdfHtml = '<iframe src="'.$pdfDocumentPath.'" style="width: 100%;height: 100%;border: none;"></iframe>';
        $embedPdfHtml = '<object type="application/pdf" width="400px" height="400px" data="'.$pdfDocumentPath.'"></object>';
        //$embedPdfHtml = '<object type="application/pdf" width="100%" height="100%" data="'.$pdfDocumentPath.'"></object>';

        //$embedPdfHtml = '<br><br>Complete Application in PDF (ID='.$pdfDocument->getId().'):<br>' . $embedPdfHtml;
        $embedPdfHtml = '<br><br>This Complete Application in PDF will be attached to the invitation email:<br>' . $embedPdfHtml;

        return $embedPdfHtml;
    }
    public function getEmbedPdfByInterview( $interview ) {
        if( !$interview ) {
            return NULL;
        }

        $resapp = $interview->getResApp();
        if( !$resapp ) {
            return NULL;
        }

        return $this->getEmbedPdf($resapp->getRecentReport());
    }

} 