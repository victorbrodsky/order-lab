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

namespace App\ResAppBundle\Controller;

use App\ResAppBundle\Entity\InputDataFile;
use App\ResAppBundle\Entity\ResidencyApplication;
use App\ResAppBundle\Form\ResAppUploadCsvType;
use App\ResAppBundle\Form\ResAppUploadType;
use App\ResAppBundle\PdfParser\PDFService;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use setasign\Fpdi\Fpdi;
//use Smalot\PdfParser\Parser;
//use Spatie\PdfToText\Pdf;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Pdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;


class ResAppBulkUploadController extends OrderAbstractController
{

    /**
     * Upload Multiple Applications via CSV
     *
     * @Route("/upload/", name="resapp_upload_multiple_applications", methods={"GET","POST"})
     * @Template("AppResAppBundle/Upload/upload-csv-applications.html.twig")
     */
    public function uploadCsvMultipleApplicationsAction(Request $request)
    {
        //exit('test exit uploadCsvMultipleApplicationsAction');
        if (
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') === false
        ) {
            return $this->redirect($this->generateUrl('resapp-nopermission'));
        }

        //exit("Upload Multiple Applications is under construction");

        $resappPdfUtil = $this->container->get('resapp_pdfutil');
        $em = $this->getDoctrine()->getManager();

        $cycle = 'new';

        $inputDataFile = new InputDataFile();

        //get Table $jsonData
        $handsomtableJsonData = array(); //$this->getTableData($inputDataFile);

        //$form = $this->createUploadForm($cycle);
//        $params = array(
//            //'resTypes' => $userServiceUtil->flipArrayLabelValue($residencyTypes), //flipped
//            //'defaultStartDates' => $defaultStartDates
//        );
//        $form = $this->createForm(ResAppUploadCsvType::class, null,
//            array(
//                //'method' => 'GET',
//                //'form_custom_value'=>$params
//            )
//        );

        if(0) {
            $form = $this->createForm(ResAppUploadCsvType::class,null);
        } else {
//            $form = $this->createForm(ResAppUploadType::class,null);
            $params = array(
//                //'resTypes' => $userServiceUtil->flipArrayLabelValue($residencyTypes), //flipped
//                //'defaultStartDates' => $defaultStartDates
            );
            $form = $this->createForm(ResAppUploadType::class, $inputDataFile,
                array(
                    'form_custom_value' => $params
                )
            );
        }

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            //dump($form);
            //exit("form submitted");

            if(0) {
                $inputFileName = $form['file']->getData();
                echo "inputFileName1=" . $inputFileName . "<br>";
                //$inputFileName = $form->get('file')->getData();
                //echo "inputFileName2=".$inputFileName."<br>";

                $pdfFilePaths = $resappPdfUtil->getPdfFilesInSameFolder($inputFileName);

            } else {
                $pdfFilePaths = array();
                $pdfFiles = array();
                $inputFileName = NULL;

                $em->getRepository('AppUserdirectoryBundle:Document')->processDocuments( $inputDataFile, 'erasFile' );
                $em->persist($inputDataFile);
                $em->flush();

                $files = $inputDataFile->getErasFiles();
                foreach( $files as $file ) {
                    $ext = $file->getExtension();
                    if( $ext == 'csv' ) {
                        $inputFileName = $file->getFullServerPath();
                    } elseif ($ext == 'pdf') {
                        $pdfFilePaths[] = $file->getFullServerPath();
                        $pdfFiles[] = $file;
                    }
                }
            }
            echo "inputFileName=" . $inputFileName . "<br>";
            echo "pdfFilePaths count=" . count($pdfFilePaths) . "<br>";
            dump($pdfFilePaths);

//            //remove all documents
//            foreach( $inputDataFile->getErasFiles() as $file ) {
//                $inputFileName->removeElement($file);
//                $em->remove($file);
//            }
//            $em->remove($inputFileName);
//            $em->flush();
//
//            exit(111);

            $handsomtableJsonData = $resappPdfUtil->getCsvApplicationsData($inputFileName,$pdfFiles);

            if( !is_array($handsomtableJsonData) ) {

                $this->get('session')->getFlashBag()->add(
                    'warning',
                    $handsomtableJsonData
                );

                $handsomtableJsonData = array();
            }

            //remove all documents
            foreach( $inputDataFile->getErasFiles() as $file ) {
                $inputDataFile->removeErasFile($file);
                $em->remove($file);
            }
            $em->remove($inputDataFile);
            $em->flush();

            //exit(111);

//            $pdfArr = $resappPdfUtil->getTestPdfApplications();
//            $dataArr = $resappPdfUtil->getParsedDataArray($pdfArr);
//            $handsomtableJsonData = $resappPdfUtil->getHandsomtableDataArray($dataArr);
            //dump($handsomtableJsonData);
            //exit('111');

            //exit("parsed res=".implode(";",$res));

            //$dataArr = $this->getDataArray();

            //get Table $jsonData
            //$jsonData = $this->getTableData($dataArr);
        }

        return array(
            'form' => $form->createView(),
            'cycle' => $cycle,
            'inputDataFile' => $inputDataFile,
            'handsometableData' => $handsomtableJsonData
        );
    }

    /**
     * Upload Multiple Applications via PDF
     *
     * @Route("/upload-pdf/", name="resapp_upload_pdf_multiple_applications", methods={"GET"})
     * @Template("AppResAppBundle/Upload/upload-applications.html.twig")
     */
    public function uploadPdfMultipleApplicationsAction(Request $request)
    {

        if (
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') === false
        ) {
            return $this->redirect($this->generateUrl('resapp-nopermission'));
        }

        //exit("Upload Multiple Applications is under construction");

        $resappPdfUtil = $this->container->get('resapp_pdfutil');
        //$em = $this->getDoctrine()->getManager();

        $cycle = 'new';
        
        $inputDataFile = new InputDataFile();

        //get Table $jsonData
        $handsomtableJsonData = array(); //$this->getTableData($inputDataFile);

        //$form = $this->createUploadForm($cycle);
        $params = array(
            //'resTypes' => $userServiceUtil->flipArrayLabelValue($residencyTypes), //flipped
            //'defaultStartDates' => $defaultStartDates
        );
        $form = $this->createForm(ResAppUploadType::class, $inputDataFile,
            array(
                'method' => 'GET',
                'form_custom_value'=>$params
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {

            //exit("form submitted");

            //$em->getRepository('AppUserdirectoryBundle:Document')->processDocuments($inputDataFile); //Save new entry

            //Testing: get PDF C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\temp\eras.pdf
            //CrossReferenceException:
            // This PDF document probably uses a compression technique which is not supported by the free parser shipped with FPDI.
            // (See https://www.setasign.com/fpdi-pdf-parser for more details)
            //$path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\eras.pdf";

//            $projectRoot = $this->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
//            //echo "projectRoot=$projectRoot<br>";
//            //exit($projectRoot);
//            $parentRoot = str_replace('order-lab','',$projectRoot);
//            $parentRoot = str_replace('orderflex','',$parentRoot);
//            $parentRoot = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,'',$parentRoot);
//            //echo "parentRoot=$parentRoot<br>";
//            $filename = "eras_gs.pdf";
//            //$filename = "eras.pdf";
//            $path = $parentRoot.DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR.$filename;
//            //$path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\eras.pdf";
//            echo "path=$path<br>";

            //PackingSlip.pdf
            //$path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\PackingSlip.pdf";

            //https://packagist.org/packages/setasign/fpdi
            //NOT WORKING: This PDF document probably uses a compression technique which is not supported by the free parser shipped with FPDI. (See https://www.setasign.com/fpdi-pdf-parser for more details)
            //Use GhostScript?
            //$res = $this->parsePdfSetasign($path);
            //exit();

            //Other PDF parsers:
            //https://packagist.org/packages/smalot/pdfparser (LGPL-3.0)
            //https://packagist.org/packages/wrseward/pdf-parser (MIT)
            //https://packagist.org/packages/rafikhaceb/tcpdi (Apache-2.0 License)
            //pdftotext - open source library (GPL)

            //$res = array();

            //https://packagist.org/packages/smalot/pdfparser (LGPL-3.0) (based on https://tcpdf.org/)
            //$res[] = $this->parsePdfSmalot($path);

            //https://gist.github.com/cirovargas (MIT)
            //$res[] = $this->parsePdfCirovargas($path);
            //exit();

            //https://github.com/spatie/pdf-to-text
            //require pdftotext (which pdftotext): yum install poppler-utils
            //$res[] = $this->parsePdfSpatie($path);

            $pdfArr = $resappPdfUtil->getTestPdfApplications();
            $dataArr = $resappPdfUtil->getParsedDataArray($pdfArr);
            $handsomtableJsonData = $resappPdfUtil->getHandsomtableDataArray($dataArr);
            dump($handsomtableJsonData);
            //exit('111');

            //exit("parsed res=".implode(";",$res));

            //$dataArr = $this->getDataArray();

            //get Table $jsonData
            //$jsonData = $this->getTableData($dataArr);
        }

        return array(
            'form' => $form->createView(),
            'cycle' => $cycle,
            'inputDataFile' => $inputDataFile,
            'handsometableData' => $handsomtableJsonData
        );
    }

    /**
     * Upload Multiple Applications
     *
     * @Route("/pdf-parser-test/", name="resapp_updf_parser_test", methods={"GET"})
     * @Template("AppResAppBundle/Upload/upload-applications.html.twig")
     */
    public function pdfParserTestAction(Request $request)
    {
        //exit("not allowed. one time run method.");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        //$resappRepGen = $this->container->get('resapp_reportgenerator');
        $resappPdfUtil = $this->container->get('resapp_pdfutil');
        //$em = $this->getDoctrine()->getManager();

//        $repository = $this->getDoctrine()->getRepository('AppResAppBundle:ResidencyApplication');
//        $dql =  $repository->createQueryBuilder("resapp");
//        $dql->select('resapp');
//        $dql->leftJoin('resapp.coverLetters','coverLetters');
//        $dql->where("coverLetters IS NOT NULL");
//        $dql->orderBy("resapp.id","DESC");
//        $query = $em->createQuery($dql);
//        $query->setMaxResults(10);
//        $resapps = $query->getResult();
//        echo "resapps count=".count($resapps)."<br>";

        $resapps = $resappPdfUtil->getTestApplications();

        foreach($resapps as $resapp) {
            //echo "get ERAS from ID=".$resapp->getId()."<br>";
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

            echo "get ERAS from ID=".$resapp->getId()."<br>";

//            $erasFilePath = $erasFile->getAttachmentEmailPath();
//            echo "erasFilePath=$erasFilePath<br>";
//            if( $resappPdfUtil->isPdfCompressed($erasFilePath) ) {
//                echo "Compressed <br>";
//
//                if(1) {
//
//                    $processedFiles = $resappRepGen->processFilesGostscript(array($erasFilePath));
//                    if (count($processedFiles) > 0) {
//                        //$dir = dirname($erasFilePath);
//                        $processedGsFile = $processedFiles[0];
//                        $processedGsFile = str_replace('"', '', $processedGsFile);
//                        //$path = $dir.DIRECTORY_SEPARATOR.$path;
//                        //$path = "C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/temp/eras_gs.pdf";
//                        echo "processedGsFile=" . $processedGsFile . "<br>";
//
//                    } else {
//                        return null;
//                    }
//                }
//
//            } else {
//                echo "Not Compressed (version < 1.4) <br>";
//            }

            //get data from ERAS file

//            if( $processedGsFile ) {
//                $parsedDataArr = $this->parsePdfSpatie($processedGsFile);
//                dump($parsedDataArr);
//                exit("GS processed");
//            } else {
//                $parsedDataArr = $this->parsePdfSpatie($erasFile);
//                dump($parsedDataArr);
//            }

            $erasFilePath = $erasFile->getAttachmentEmailPath();
            $extractedText = $resappPdfUtil->parsePdfCirovargas($erasFilePath);
            echo $extractedText."<br><br>";
            //dump($extractedText);

            $parsedDataArr = $resappPdfUtil->extractDataPdf($erasFile);
            dump($parsedDataArr);

            //exit("EOF $erasFile");

        }

        exit('EOF pdfParserTestAction');
    }

    

    public function parsePdfSetasign($path) {

        if (file_exists($path)) {
            //echo "The file $path exists<br>";
        } else {
            //==echo "The file $path does not exist<br>";
        }

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

        $path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\eras_gs.pdf";

        // create a document instance
        //$document = SetaPDF_Core_Document::loadByFilename('Laboratory-Report.pdf');
        // create an extractor instance
        //$extractor = new SetaPDF_Extractor($document);
        // get the plain text from page 1
        //$result = $extractor->getResultByPageNumber(1);


//        // initiate FPDI
        $pdf = new Fpdi();
//        // add a page
        //$pdf->AddPage();
//        // set the source file
        $pdf->setSourceFile($path); //"Fantastic-Speaker.pdf";
//        // import page 1
        //$tplId = $pdf->importPage(1);
        $tplId = $pdf->importPage(2);
//        //dump($tplId);
//        //exit('111');
        $pdf->AddPage();
//        // use the imported page and place it at point 10,10 with a width of 100 mm
        $pdf->useTemplate($tplId, 10, 10, 100);
//
//        //$pdf->Write();
//        //$pdf->WriteHTML($html);
        $pdf->Output('I', 'generated.pdf');
//
//        //dump($pdf->Output());
//        //exit('111');
//        //dump($pdf);
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

        $startStr = "Applicant ID:";
        $endStr = "AAMC ID:";
        $field = $this->getPdfField($text,$startStr,$endStr);
        echo "Cirovargas: $startStr=[".$field."]<br>";
        //exit();

        dump($text);
        //exit();
        return $field;
    }
    public function parsePdfSmalot($path) {

        if (file_exists($path)) {
            //echo "The file $path exists <br>";
        } else {
            echo "The file $path does not exist <br>";
        }

        $field = null;

        // Parse pdf file and build necessary objects.
        $parser = new Parser();
        $pdf    = $parser->parseFile($path);

        // Retrieve all pages from the pdf file.
        $pages  = $pdf->getPages();

        // Loop over each page to extract text.
        $counter = 1;
        foreach ($pages as $page) {
            $pdfTextPage = $page->getText();

//            if(1) {
//                //$str, $starting_word, $ending_word
//                $startStr = "Applicant ID:";
//                $endStr = "AAMC ID:";
//                $applicationId = $this->string_between_two_string2($pdfTextPage, $startStr, $endStr);
//                //echo "applicationId=[".$applicationId ."]<br>";
//                if ($applicationId) {
//                    $applicationId = trim($applicationId);
//                    //$applicationId = str_replace(" ","",$applicationId);
//                    //$applicationId = str_replace("\t","",$applicationId);
//                    //$applicationId = str_replace("\t\n","",$applicationId);
//                    $applicationId = str_replace("'", '', $applicationId);
//                    $applicationId = preg_replace('/(\v|\s)+/', ' ', $applicationId);
//                    echo "applicationId=[".$applicationId."]<br>";
//                    //echo "Page $counter: <br>";
//                    //dump($pdfTextPage);
//                    echo "Page $counter=[".$pdfTextPage."]<br>";
//                    exit("string found $startStr");
//                }
//            }
            $startStr = "Applicant ID:";
            $endStr = "AAMC ID:";
            $field = $this->getPdfField($pdfTextPage,$startStr,$endStr);
            if( $field ) {
                echo "Smalot: $startStr=[" . $field . "]<br>";
                break;
            }
            //exit();

            //echo "Page $counter: <br>";
            //dump($pdfTextPage);

            //echo "Page $counter=[".$pdfTextPage."]<br>";

            $counter++;
        }

        return $field;
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
            $pdftotextPath = '/mingw64/bin/pdftotext';
        } else {
            $pdftotextPath = '/bin/pdftotext';
        }

        $pdftotext = new Pdf($pdftotextPath);

        //$path = "C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/temp/eras_gs.pdf";
        //$path = '"'.$path.'"';
        //$path = "'".$path."'";
        $path = realpath($path);
        echo "Spatie source pdf path=".$path."<br>";

        $text = $pdftotext->setPdf($path)->text();

//        $startStr = "Applicant ID:";
//        $endStr = "AAMC ID:";
//        $field = $this->getPdfField($text,$startStr,$endStr);
//        if( $field ) {
//            echo "Spatie: $startStr=[" . $field . "]<br>";
//        }

        $keysArr = $this->getKeyFields($text);

        echo "keysArr=".count($keysArr)."<br>";
        dump($keysArr);

        return $keysArr;
    }

    public function getDataArray() {

        $em = $this->getDoctrine()->getManager();

        $dataArr = array();

        $applicationDatas = array(1,2,3); //test
        $nowDate = new \DateTime();

        $counter = 0;
        foreach($applicationDatas as $applicationData) {

            $counter++;
            $pdfTextArray = array();

            $residencyTrack = $em->getRepository('AppUserdirectoryBundle:ResidencyTrackList')->find($counter);
            $pdfTextArray["Residency Track"] = $residencyTrack->getName();

            //Application Season Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
            $pdfTextArray["Application Season Start Date"] = $nowDate->format("m/d/Y H:i:s");

            //Application Season End Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
            $pdfTextArray["Application Season End Date"] = $nowDate->format("m/d/Y H:i:s");

            //Expected Residency Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
            $pdfTextArray["Expected Residency Start Date"] = $nowDate->format("m/d/Y H:i:s");

            //Expected Graduation Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
            $pdfTextArray["Expected Graduation Date"] = $nowDate->format("m/d/Y H:i:s");

            //First Name
            $pdfTextArray["First Name"] = "First Name".$counter;

            //Last Name
            $pdfTextArray["Last Name"] = "Last Name".$counter;

            //Middle Name
            $pdfTextArray["Middle Name"] = "Middle Name".$counter;

            //Preferred Email
            $pdfTextArray["Preferred Email"] = "PreferredTestEmail".$counter."@yahoo.com";

            $dataArr[] = $pdfTextArray;
        }


        return $dataArr;
    }


    public function getTableData($pdfTextsArray) {
        $jsonData = array();

        foreach($pdfTextsArray as $pdfTextArray) {
            $rowArr = array();

            $currentDate = new \DateTime();
            $currentDateStr = $currentDate->format('m\d\Y H:i:s');

            if(1) {
                $rowArr["Application Receipt Date"] = $currentDateStr;

                echo "Residency Track:".$pdfTextArray["Residency Track"]."<br>";
                $rowArr["Residency Track"] = $pdfTextArray["Residency Track"];

                //Application Season Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
                $rowArr["Application Season Start Date"] = $pdfTextArray["Application Season Start Date"];

                //Application Season End Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
                $rowArr["Application Season End Date"] = $pdfTextArray["Application Season End Date"];

                //Expected Residency Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
                $rowArr["Expected Residency Start Date"] = $pdfTextArray["Expected Residency Start Date"];

                //Expected Graduation Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
                $rowArr["Expected Graduation Date"] = $pdfTextArray["Expected Graduation Date"];

                //First Name
                $rowArr["First Name"] = $pdfTextArray["First Name"];

                //Last Name
                $rowArr["Last Name"] = $pdfTextArray["Last Name"];

                //Middle Name
                $rowArr["Middle Name"] = $pdfTextArray["Middle Name"];

                //Preferred Email
                $rowArr["Preferred Email"] = $pdfTextArray["Preferred Email"];
            } else {
                $rowArr["Accession ID"] = "S11-1";

                $rowArr["Part ID"] = "1";

                //Application Season Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
                $rowArr["Block ID"] = "2";

                //Application Season End Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
                $rowArr["Slide ID"] = "Slide ID";

                //Expected Residency Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
                $rowArr["Stain Name"] = "Stain Name";

                //Expected Graduation Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
                $rowArr["Other ID"] = "Other ID";

                //First Name
                $rowArr["Sample Name"] = "Sample Name";

            }

            if(0) {
                //Medical School Graduation Date
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Medical School Name
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Degree (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //USMLE Step 1 Score
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //USMLE Step 2 CK Score
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //USMLE Step 3 Score
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Country of Citizenship (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Visa Status (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Is the applicant a member of any of the following groups? (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Number of first author publications
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Number of all publications
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //AOA (show the same checkmark in the Handsontable cell as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Couple’s Match:
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Post-Sophomore Fellowship
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Previous Residency Start Date
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Previous Residency Graduation/Departure Date
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Previous Residency Institution
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Previous Residency City
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Previous Residency State (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Previous Residency Country (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Previous Residency Track (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //ERAS Application ID
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //ERAS Application (show the cells in this column as blank - this is where you will show the original ERAS file name of the PDF once it uploads)
                $rowArr["xxx"] = $pdfTextArray["xxx"];

                //Duplicate? (locked field, leave empty by default)
                $rowArr["xxx"] = $pdfTextArray["xxx"];
            }

            $jsonData[] = $rowArr;
        }

        return $jsonData;
    }

//    public function getKeyFieldArr() {
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

//    public function getKeyFields($text) {
//
//        $keysArr = array();
//
//        foreach( $this->getKeyFieldArr() as $key=>$endStr ) {
//            echo "key=$key, endStr=$endStr<br>";
//            $field = $this->getPdfField($text,$key,$endStr);
//            if( $field ) {
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
//                echo "$key=[" . $field . "]<br>";
//                $keysArr[$key] = $field;
//            }
//        }
//        return $keysArr;
//    }
//    public function getPdfField($text,$startStr,$endStr) {
//        //$startStr = "Applicant ID:";
//        //$endStr = "AAMC ID:";
//        $field = $this->string_between_two_string2($text, $startStr, $endStr);
//        //echo "field=[".$field ."]<br>";
//        if ($field) {
//            $field = trim($field);
//            //$field = str_replace(" ","",$field);
//            //$field = str_replace("\t","",$field);
//            //$field = str_replace("\t\n","",$field);
//            $field = str_replace("'", '', $field);
//            $field = preg_replace('/(\v|\s)+/', ' ', $field);
//            //echo "$startStr=[".$field."]<br>";
//            //echo "Page $counter: <br>";
//            //dump($text);
//            //echo "Page=[".$text."]<br>";
//            //exit("string found $startStr");
//            //exit();
//            return $field;
//        }
//        return null;
//    }
//    public function string_between_two_string($str, $starting_word, $ending_word)
//    {
//        $subtring_start = strpos($str, $starting_word);
//        //Adding the strating index of the strating word to
//        //its length would give its ending index
//        $subtring_start += strlen($starting_word);
//        //Length of our required sub string
//        $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
//        // Return the substring from the index substring_start of length size
//        return substr($str, $subtring_start, $size);
//    }
//    public function string_between_two_string2($str, $starting_word, $ending_word){
//        $arr = explode($starting_word, $str);
//        if (isset($arr[1])){
//            $arr = explode($ending_word, $arr[1]);
//            return $arr[0];
//        }
//        return '';
//    }

}
