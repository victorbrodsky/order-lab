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


    public function getCsvApplicationsData( $csvFileName ) {

        echo "csvFileName=$csvFileName <br>";

        if (file_exists($csvFileName)) {
            //echo "The file $inputFileName exists";
        } else {
            //echo "The file $inputFileName does not exist";
            return "The file $csvFileName does not exist";
        }

        $handsomtableJsonData = array();

        //$reader = ReaderEntityFactory::createReaderFromFile($csvFileName);
        $reader = ReaderEntityFactory::createCSVReader();

        $reader->open($csvFileName);

        //around 3877 columns, 833 not empty columns

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                // do stuff with the row
                if( $rowNumber == 1 ) {
                    continue;
                }
                $cells = $row->getCells();
                echo "rowNumber=$rowNumber <br>";
                dump($cells);

                //get cell index by header
                //$emailCell = $cells[4];
                //$cell->getValue();

            }
        }

        $reader->close();

        return $handsomtableJsonData;
    }
    public function cvsHeaderMapper() {

        $map = array(
            "AAMC ID" => "AAMC ID",
            //"Applicant ID" => "ERAS Application ID"
            "Residency Track" => "Residency Track", //?

            "Applicant Applied Date" => "Application Season Start Date",
            "Applicant Applied Date" => "Application Season End Date",
            "Applicant Applied Date" => "Expected Residency Start Date",
            "Applicant Applied Date" => "Expected Graduation Date",

            "First Name" => "First Name",
            "Last Name" => "Last Name",
            "Last Name" => "Last Name",

            "E-mail" => "Preferred Email",

            "Most Recent Medical School" => "Medical School Name",
            "Medical School Attendance Dates" => "Medical School Graduation Date", //8/2014 - 5/2019

            "USMLE Step 1 Score" => "USMLE Step 1 Score",
            "USMLE Step 2 CK Score" => "USMLE Step 2 CK Score",
            "USMLE Step 3 Score" => "USMLE Step 3 Score",

            "Citizenship" => "Country of Citizenship",
            "Current Visa Status" => "Visa Status",

            "Self Identify" => "Is the applicant a member of any of the following groups?",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
            "" => "",
        );

        return $map;
    }


    public function getHandsomtableDataArray($parsedDataArr) {
        $tableDataArr = array();

        foreach($parsedDataArr as $parsedData) {
            $rowArr = array();

            $currentDate = new \DateTime();
            $currentDateStr = $currentDate->format('m\d\Y H:i:s');

            $rowArr["Application Receipt Date"]['id'] = 1;
            $rowArr["Application Receipt Date"]['value'] = $currentDateStr;

            //echo "Residency Track:".$pdfTextArray["Residency Track"]."<br>";
            $rowArr["Residency Track"] = NULL; //$parsedData["Residency Track"];

            //Application Season Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
            $rowArr["Application Season Start Date"] = NULL; //$parsedData["Application Season Start Date"];

            //Application Season End Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
            $rowArr["Application Season End Date"] = NULL; //$parsedData["Application Season End Date"];

            //Expected Residency Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
            $rowArr["Expected Residency Start Date"] = NULL; //$parsedData["Expected Residency Start Date"];

            //Expected Graduation Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
            $rowArr["Expected Graduation Date"] = NULL; //$parsedData["Expected Graduation Date"];

            //// get last, first name ////
            $fullName = $parsedData["Name:"];
            $fullNameArr = explode(",",$fullName);
            if( count($fullNameArr) > 1) {
                $lastName = trim($fullNameArr[0]);
                $firstName = trim($fullNameArr[1]);
            } else {
                $lastName = $fullName;
                $firstName = NULL;
            }
            //// EOF get last, first name ////

            //First Name
            $rowArr["First Name"]['id'] = 1;
            $rowArr["First Name"]['value'] = $firstName;

            //Last Name
            $rowArr["Last Name"]['id'] = 1;
            $rowArr["Last Name"]['value'] = $lastName;

            //Middle Name
            $rowArr["Middle Name"] = NULL;

            //Preferred Email
            $rowArr["Preferred Email"]['id'] = 1;
            $rowArr["Preferred Email"]['value'] = $parsedData["Email:"];

            //Couple’s Match:
            $rowArr["Couple’s Match"]['id'] = 1;
            $rowArr["Couple’s Match"]['value'] = $parsedData["Participating as a Couple in NRMP:"];

            //Preferred Email
            $rowArr["ERAS Application ID"]['id'] = 1;
            $rowArr["ERAS Application ID"]['value'] = $parsedData["Applicant ID:"];

            $tableDataArr[] = $rowArr;
        }

        return $tableDataArr;
    }

    public function getParsedDataArray($pdfArr) {

        $parsedDataArr = array();
        
        foreach($pdfArr as $erasFile) {
            $parsedDataArr[] = $this->extractDataPdf($erasFile);
        }

        return $parsedDataArr;
    }

    public function getTestApplications() {
        $repository = $this->em->getRepository('AppResAppBundle:ResidencyApplication');
        $dql = $repository->createQueryBuilder("resapp");
        $dql->select('resapp');
        $dql->leftJoin('resapp.coverLetters','coverLetters');
        $dql->where("coverLetters IS NOT NULL");
        $dql->orderBy("resapp.id","DESC");
        $query = $this->em->createQuery($dql);
        $query->setMaxResults(10);
        $resapps = $query->getResult();
        echo "resapps count=".count($resapps)."<br>";
        
        return $resapps;
    }
    public function getTestPdfApplications() {
        $resapps = $this->getTestApplications();
        echo "resapps count=".count($resapps)."<br>";

        $resappPdfs = array();

        foreach($resapps as $resapp) {
            $erasFiles = $resapp->getCoverLetters();
            $erasFile = null;
            $processedGsFile = null;

            if( count($erasFiles) > 0 ) {
                $erasFile = $erasFiles[0];
            } else {
                continue;
            }

            if( !$erasFile ) {
                continue;
            }

            if( strpos($erasFile, '.pdf') !== false ) {
                //PDF
            } else {
                echo "Skip: File is not PDF <br>";
                continue;
            }

            //echo "get ERAS from ID=".$resapp->getId()."<br>";
            $resappPdfs[] = $erasFile;
        }

        return $resappPdfs;
    }
    
    //Compressed PDF is version > 1.4
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

    public function getKeyFields($text) {

        $keysArr = array();

        foreach( $this->getKeyFieldArr() as $key=>$endArr ) {
            //echo "key=$key<br>";

//            foreach($endArr as $endStr) {
//                $field = $this->getPdfField($text, $key, $endStr);
//            }
            $field = $this->getShortestField($text, $key, $endArr);

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

        $fieldsArr = array();

        $fieldsArr["Applicant ID:"] = $endArr;
        $fieldsArr["AAMC ID:"] = $endArr;
        $fieldsArr["Email:"] = $endArr;
        $fieldsArr["Name:"] = $endArr;
        $fieldsArr["Birth Date:"] = $endArr;
        $fieldsArr["USMLE ID:"] = $endArr;
        $fieldsArr["NBOME ID:"] = $endArr;
        $fieldsArr["NRMP ID:"] = $endArr;
        $fieldsArr["Gender:"] = $endArr;
        $fieldsArr["Participating as a Couple in NRMP:"] = $endArr;
        $fieldsArr["Present Mailing Address:"] = $endArr;
        $fieldsArr["Preferred Phone #:"] = $endArr;

        return $fieldsArr;
    }
    public function getShortestField($text, $key, $endArr) {
        $minLength = NULL;
        $minField = NULL;

        foreach($endArr as $endStr) {
            $field = $this->getPdfField($text,$key,$endStr);
            $fieldLen = strlen($field);
            if( $minLength === NULL || $fieldLen <= $minLength ) {
                $minLength = $fieldLen;
                $minField = $field;
            }
        }

        return $minField;
    }

    public function getKeyFields_Single($text) {

        $keysArr = array();

        foreach( $this->getKeyFieldArr_Single() as $key=>$endStr ) {
            echo "key=$key, endStr=$endStr<br>";
            $field = $this->getPdfField($text,$key,$endStr);
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

                echo "$key=[" . $field . "]<br>";
                $keysArr[$key] = $field;
            }
        }
        return $keysArr;
    }
    public function getKeyFieldArr_Single() {

        $fieldsArr = array();

        $fieldsArr["Applicant ID:"] = "AAMC ID:";
        $fieldsArr["AAMC ID:"] = "Most Recent Medical School:";
        $fieldsArr["Email:"] = "Gender:";
        $fieldsArr["Name:"] = "Previous Last Name:";
        $fieldsArr["Birth Date:"] = "Authorized to Work in the US:";
        $fieldsArr["USMLE ID:"] = "NBOME ID:";
        $fieldsArr["NBOME ID:"] = "Email:";
        $fieldsArr["NRMP ID:"] = "Participating in the NRMP Match:";

        return $fieldsArr;
    }

    public function getPdfField($text,$startStr,$endStr) {
        //$startStr = "Applicant ID:";
        //$endStr = "AAMC ID:";
        $field = $this->string_between_two_string2($text, $startStr, $endStr);
        //echo "field=[".$field ."]<br>";
        if ($field) {
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
} 