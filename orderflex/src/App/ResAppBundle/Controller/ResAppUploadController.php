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
            $res = $this->parsePdf($path);

            exit("parsed res=$res");

            //get Table $jsonData
            $jsonData = $this->getTableData($inputDataFile);
        }

        return array(
            'form' => $form->createView(),
            'cycle' => $cycle,
            'inputDataFile' => $inputDataFile,
            'handsometableData' => $jsonData
        );
    }

    public function parsePdf($path) {

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

    public function getTableData($transresRequest) {
        $jsonData = array();

        foreach($transresRequest->getDataResults() as $dataResult) {
            $rowArr = array();

            //System
            $system = $dataResult->getSystem();
            if( $system ) {
//                $systemStr = $system->getName();
//                $abbreviation = $system->getAbbreviation();
//                if( $abbreviation ) {
//                    $systemStr = $abbreviation;
//                }
                $rowArr['Source']['id'] = $system->getId();
                $rowArr['Source']['value'] = $system->getOptimalName(); //$systemStr;
            }

            //Accession ID
            $rowArr['Accession ID']['id'] = $dataResult->getId();
            $rowArr['Accession ID']['value'] = $dataResult->getAccessionId();

            //Part ID
            $rowArr['Part ID']['id'] = $dataResult->getId();
            $rowArr['Part ID']['value'] = $dataResult->getPartId();

            //Block ID
            $rowArr['Block ID']['id'] = $dataResult->getId();
            $rowArr['Block ID']['value'] = $dataResult->getBlockId();

            //Slide ID
            $rowArr['Slide ID']['id'] = $dataResult->getId();
            $rowArr['Slide ID']['value'] = $dataResult->getSlideId();

            //Stain Name
            $rowArr['Stain Name']['id'] = $dataResult->getId();
            $rowArr['Stain Name']['value'] = $dataResult->getStainName();

            //Antibody
            $antibody = $dataResult->getAntibody();
            if( $antibody ) {
                $rowArr['Antibody']['id'] = $antibody->getId();
                $rowArr['Antibody']['value'] = $antibody."";
            }

            //Other ID
            $rowArr['Other ID']['id'] = $dataResult->getId();
            $rowArr['Other ID']['value'] = $dataResult->getOtherId();

            //Barcode
            $rowArr['Sample Name']['id'] = $dataResult->getId();
            $rowArr['Sample Name']['value'] = $dataResult->getBarcode();

            //Comment
            $rowArr['Comment']['id'] = $dataResult->getId();
            $rowArr['Comment']['value'] = $dataResult->getComment();


            $jsonData[] = $rowArr;
        }

        return $jsonData;
    }

}
