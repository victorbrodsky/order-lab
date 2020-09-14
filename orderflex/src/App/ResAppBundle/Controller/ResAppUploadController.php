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
use App\ResAppBundle\Form\ResAppUploadType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Parser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;


class ResAppUploadController extends OrderAbstractController
{
    

    /**
     * Upload Multiple Applications
     *
     * @Route("/upload/", name="resapp_upload_multiple_applications", methods={"GET"})
     * @Template("AppResAppBundle/Upload/upload-applications.html.twig")
     */
    public function uploadMultipleApplicationsAction(Request $request)
    {

        if (
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') === false
        ) {
            return $this->redirect($this->generateUrl('resapp-nopermission'));
        }

        //exit("Upload Multiple Applications is under construction");

        $em = $this->getDoctrine()->getManager();

        $cycle = 'new';
        
        $inputDataFile = new InputDataFile();

        //get Table $jsonData
        $jsonData = array(); //$this->getTableData($inputDataFile);

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
            $path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\eras.pdf";
            //PackingSlip.pdf
            //$path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\PackingSlip.pdf";

            //https://packagist.org/packages/setasign/fpdi
            //NOT WORKING: This PDF document probably uses a compression technique which is not supported by the free parser shipped with FPDI. (See https://www.setasign.com/fpdi-pdf-parser for more details)
            //Use GhostScript?
            //$res = $this->parsePdfSetasign($path);

            //Other PDF parsers:
            //https://packagist.org/packages/smalot/pdfparser (LGPL-3.0)
            //https://packagist.org/packages/wrseward/pdf-parser (MIT)
            //https://packagist.org/packages/rafikhaceb/tcpdi (Apache-2.0 License)

            //https://packagist.org/packages/smalot/pdfparser (LGPL-3.0)
            //$res = $this->parsePdfSmalot($path);

            //exit("parsed res=$res");

            $dataArr = $this->getDataArray();

            //get Table $jsonData
            $jsonData = $this->getTableData($dataArr);
        }

        return array(
            'form' => $form->createView(),
            'cycle' => $cycle,
            'inputDataFile' => $inputDataFile,
            'handsometableData' => $jsonData
        );
    }

    public function parsePdfSetasign($path) {

        if (file_exists($path)) {
            echo "The file $path exists";
        } else {
            echo "The file $path does not exist";
        }

        // initiate FPDI
        $pdf = new Fpdi();
        // add a page
        $pdf->AddPage();
        // set the source file
        $pdf->setSourceFile($path); //"Fantastic-Speaker.pdf";
        // import page 1
        $tplId = $pdf->importPage(1);
        dump($tplId);
        // use the imported page and place it at point 10,10 with a width of 100 mm
        $pdf->useTemplate($tplId, 10, 10, 100);

        $pdf->Output();
        dump($pdf);
    }
    public function parsePdfSmalot($path) {

        if (file_exists($path)) {
            echo "The file $path exists";
        } else {
            echo "The file $path does not exist";
        }

        // Parse pdf file and build necessary objects.
        $parser = new Parser();
        $pdf    = $parser->parseFile($path);

        // Retrieve all pages from the pdf file.
        $pages  = $pdf->getPages();

        // Loop over each page to extract text.
        $counter = 1;
        foreach ($pages as $page) {
            $pdfTextPage = $page->getText();

            echo "Page $counter <br>";
            dump($pdfTextPage);
            $counter++;
        }


//        // Retrieve all details from the pdf file.
//        $details  = $pdf->getDetails();
//        // Loop over each property to extract values (string or array).
//        foreach ($details as $property => $value) {
//            if (is_array($value)) {
//                $value = implode(', ', $value);
//            }
//            echo $property . ' => ' . $value . "\n";
//        }
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

            if(0) {
                $rowArr["Application Receipt Date"] = $currentDateStr;

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

}
