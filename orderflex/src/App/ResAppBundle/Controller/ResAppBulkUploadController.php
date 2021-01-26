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
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\Citizenship;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\EmploymentStatus;
use App\UserdirectoryBundle\Entity\Examination;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\Training;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
//use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
//use setasign\Fpdi\Fpdi;
//use Smalot\PdfParser\Parser;
//use Spatie\PdfToText\Pdf;
//use App\ResAppBundle\PdfParser\PDFService;

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
        //exit('test uploadCsvMultipleApplicationsAction');
        if (
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') === false
        ) {
            return $this->redirect($this->generateUrl('resapp-nopermission'));
        }

        //exit("Upload Multiple Applications is under construction");

        $resappPdfUtil = $this->container->get('resapp_pdfutil');
        $resappRepGen = $this->container->get('resapp_reportgenerator');
        $userSecUtil = $this->container->get('user_security_utility');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $cycle = 'new';
        $errorMsg = '';

        $inputDataFile = new InputDataFile();

        //get Table $jsonData
        $handsomtableJsonData = array(); //$this->getTableData($inputDataFile);

        //////////// testing /////////////
//        if(0) {
//            $archiveStatus = $em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("archive");
//            if (!$archiveStatus) {
//                throw new EntityNotFoundException('Unable to find entity by name=' . "archive");
//            }
//            $hideStatus = $em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("hide");
//            if (!$archiveStatus) {
//                throw new EntityNotFoundException('Unable to find entity by name=' . "hide");
//            }
//            $rowArr = array();
//            $rowArr['ERAS Application']['id'] = 1;
//            $rowArr['ERAS Application']['value'] = 'test originalName';
//            //$rowArr['Status']['id'] = null;
//            //$rowArr['Status']['value'] = "";
//            //Add to John Smith’s application (ID 1234)
//            $resappIdArr = array();
//            $resappInfoArr = array();
//            foreach ($resappPdfUtil->getEnabledResapps() as $resapp) {
//                echo "resapps=" . $resapp->getId() . "<br>";
//                $resappIdArr[] = $resapp->getId();
//                $resappInfoArr[] = "Add to " . $resapp->getId();
//                //$rowArr['Action']['id'] = $resapp->getId();
//                //$rowArr['Action']['value'] = "Add to ".$resapp->getId();
//            }
//            $rowArr['Action']['id'] = $resappIdArr;
//            $rowArr['Action']['value'] = $resappInfoArr;
//
//            $handsomtableJsonData[] = $rowArr;
//        }
        //////////// EOF testing /////////////

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

            //1) Extracting applications from CSV and/or associate PDF
            if( $form->getClickedButton() === $form->get('upload') ) {
                //exit("Extracting applications from CSV");

                $em->getRepository('AppUserdirectoryBundle:Document')->processDocuments($inputDataFile, 'erasFile');
                $em->persist($inputDataFile);
                $em->flush();

                $processed = false;

                //first run to process zip files
                $files = $inputDataFile->getErasFiles();
                foreach ($files as $file) {
                    $ext = $file->getExtension();

                    if ($ext == 'zip') {
                        //extract archive $file
                        $zip = new \ZipArchive();

                        $zipFilePath = $file->getFullServerPath();
                        $res = $zip->open($zipFilePath);
                        if( $res === TRUE ) {
                            $destinationPath = 'Uploaded/resapp/documents';
                            $sourcePath = $destinationPath.'/temp_extract_path';
                            $zip->extractTo($sourcePath);
                            $zip->close();
                            //echo 'woot!';

                            //remove zip file from $inputDataFile
                            $inputDataFile->removeErasFile($file);

                            //process all files in temp folder and add them to $inputDataFile as addErasFile()
                            //$tempFiles = scandir($tempPath);
                            $tempFiles = array_diff(scandir($sourcePath), array('.', '..'));

                            foreach($tempFiles as $tempFile) {
                                //echo "tempFile=$tempFile <br>";

                                //$ext = $tempFile->getExtension();
                                $ext = pathinfo($tempFile, PATHINFO_EXTENSION);

                                if( $ext == 'csv' || $ext == 'pdf' ) {
                                    $this->createAndAddDocumentToInputDataFile($inputDataFile,$tempFile,$sourcePath,$destinationPath);
                                    $processed = true;
                                } elseif ($ext == 'zip') {
                                    //ignore zip file because it was processed previously
                                    //exit("Nested zip file is not allowed");
                                }
                            }

                            //delete $tempPath and all containing files
                            $resappPdfUtil->deletePublicDir($sourcePath);

                        } else {
                            //echo 'doh!';
                        }
                    }//if zip
                }//foreach file step 1

                if( $processed ) {
                    //$files = $inputDataFile->getErasFiles();
                    //echo "1 file count=" . count($files) . "<br>";

                    $em->getRepository('AppUserdirectoryBundle:Document')->processDocuments($inputDataFile, 'erasFile');
                    $em->persist($inputDataFile);
                    $em->flush();
                }

                //2) Final run to process all files in $inputDataFile (except zip files, because they were processed previously)
                $pdfFilePaths = array();
                $pdfFiles = array();
                $pdfFileNames = array();
                $inputFileName = NULL;

                $files = $inputDataFile->getErasFiles();
                //echo "2 file count=" . count($files) . "<br>";

                foreach ($files as $file) {
                    //echo "file=" . $file . "<br>";
                    $ext = $file->getExtension();
                    if ($ext == 'csv') {
                        if( !$inputFileName ) {
                            $inputFileName = $file->getFullServerPath();
                        } else {
                            $this->get('session')->getFlashBag()->add(
                                'warning',
                                "Multiple CSV files are not supported. CSV file ".$file->getOriginalName()." is ignored."
                            );
                        }
                    } elseif ($ext == 'pdf') {
                        $pdfFilePaths[] = $file->getFullServerPath();
                        $pdfFiles[] = $file;
                        $pdfFileNames[] = $file->getOriginalname();
                    } elseif ($ext == 'zip') {
                        //Ignore zip file because it was processed previously
                    }
                }

                //TODO: merge multiple CSV file

                //echo "inputFileName=" . $inputFileName . "<br>";
                //echo "pdfFilePaths count=" . count($pdfFilePaths) . "<br>";
                //dump($pdfFilePaths);
                //exit('111');

                if( $inputFileName || count($pdfFiles) > 0 ) {
                    if ($inputFileName) {
                        $handsomtableJsonData = $resappPdfUtil->getCsvApplicationsData($inputFileName, $pdfFiles);
                    } else {
                        if (count($pdfFiles) > 0) {
                            //Link existed applications in DB with provided PDF files
                            $handsomtableJsonData = $resappPdfUtil->getExistingApplicationsByPdf($pdfFiles);
                        } else {
                            $handsomtableJsonData = "PDF file(s) missing";
                        }
                    }
                } else {
                    $handsomtableJsonData = "CSV or PDF file(s) are missing";
                }

                if (!is_array($handsomtableJsonData)) {

                    $errorMsg = $handsomtableJsonData;

                        $this->get('session')->getFlashBag()->add(
                        'warning',
                        $handsomtableJsonData
                    );

                    $handsomtableJsonData = array();
                }

                //remove all documents
                if( count($handsomtableJsonData) == 0 ) {
                    foreach ($inputDataFile->getErasFiles() as $file) {
                        $inputDataFile->removeErasFile($file);
                        $em->remove($file);
                    }
                    $em->remove($inputDataFile);
                    $em->flush();
                    $processed = true;
                }

                //if( $processed ) {
                    //recreate form with new files in $inputDataFile
                    $form = $this->createForm(ResAppUploadType::class, $inputDataFile,
                        array(
                            'form_custom_value' => array()
                        )
                    );
                //}

                //Event Log
                $eventType = 'Residency Application Bulk Upload';
                $msg = "Upload and Extract Data: inputFileName=$inputFileName; " .
                    "pdfFiles=".implode(", ",$pdfFileNames) .
                    ". Extracted data rows=".count($handsomtableJsonData).". $errorMsg";
                $userSecUtil->createUserEditEvent($this->getParameter('resapp.sitename'),$msg,$user,null,$request,$eventType);
            } //Clicked Upload
            //Add applications or PDF
            elseif( $form->getClickedButton() === $form->get('addbtn') || $form->getClickedButton() === $form->get('addbtnforce') ) {
                //exit("Adding Application to the system"); //testing

                //$user = $this->get('security.token_storage')->getToken()->getUser();

                $resultArr = $this->processTableData($inputDataFile,$form); //new
                //$datajson = $form->get('datalocker')->getData();
                //dump($datajson);

                $updatedReasapps = $resultArr['updatedReasapps'];
                $updatedStrArr = $resultArr['updatedStr'];

                //exit("Adding Application to be implemented: ".$updatedStrArr);

                $updatedStr = NULL;

                if( count($updatedStrArr) > 0 ) {
                    foreach($updatedStrArr as $key=>$valueArr) {
                        $updatedStr = "<b>".$updatedStr . $key . "</b>". ":<br>" . implode("; ",$valueArr) . "<br><br>";
                    }
                   //$updatedStr = implode("<br>",$updatedStrArr);
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $updatedStr
                    );
                }

                //async PDF generation
                foreach($updatedReasapps as $updatedReasapp) {
                    $resappRepGen->addResAppReportToQueue( $updatedReasapp->getId(), 'overwrite' );
                }

                //Event Log
                $eventType = 'Residency Application Bulk Upload';
                //$updatedStr = implode(", ",$updatedStrArr);
                $msg = "Added/Upload residency application: $updatedStr";
                $userSecUtil->createUserEditEvent($this->getParameter('resapp.sitename'),$msg,$user,null,$request,$eventType);

                return $this->redirect($this->generateUrl('resapp_home'));
            }
            else {
                //Logical Error. Event Log
                $eventType = 'Residency Application Bulk Upload';
                $msg = "Unknown button clicked";
                $userSecUtil->createUserEditEvent($this->getParameter('resapp.sitename'),$msg,$user,null,$request,$eventType);

                exit("Unknown button clicked");
            }

        }//form submit

        $withdata = false;
        if( count($handsomtableJsonData) > 0 ) {
            $withdata = true;
        }

        //testing
        //$resappUtil = $this->container->get('resapp_util');
        //$defaultResidencyTrackId = $resappUtil->getDefaultResidencyTrack();
        //echo "defaultResidencyTrackId=$defaultResidencyTrackId <br>";
        //exit('111');

        return array(
            'form' => $form->createView(),
            'cycle' => $cycle,
            'inputDataFile' => $inputDataFile,
            'handsometableData' => $handsomtableJsonData,
            'withdata' => $withdata,
            //'defaultResidencyTrack' => $defaultResidencyTrack
        );
    }

    public function createAndAddDocumentToInputDataFile( $inputDataFile, $file, $sourcePath, $destinationPath ) {
        $em = $this->getDoctrine()->getManager();
        $fileDocument = $this->createDocument($file,$sourcePath,$destinationPath);
        $inputDataFile->addErasFile($fileDocument);
        $em->flush();
        //echo "added document to inputDataFile=".$fileDocument->getId()."<br>";
        return $fileDocument;
    }
    public function createDocument( $fileName, $sourcePath, $destinationPath ) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // it is possible, that two clients send a file with the
        // exact same filename, therefore we have to add the session
        // to the uuid otherwise we will get a mess
        //$uuid = md5(sprintf('%s.%s', $fileName, $session->getId()));
        //$uniquefilename = ""; //5fce61383d8ed.pdf or AP_CP-Residency-Application-Without-Attachments-2021-ID748-Tanaka-Kara-generated-on-12-07-2020-at-09-34-19-pm_UTC.pdf

        $currentDatetime = new \DateTime();
        $currentDatetimeTimestamp = $currentDatetime->getTimestamp();
        $fileUniqueName = $currentDatetimeTimestamp."-".$user->getUsernameOptimal()."-".$fileName;
        $fileUniqueName = str_replace(" ","-",$fileUniqueName);
        $fileUniqueName = str_replace(",","-",$fileUniqueName);
        $fileUniqueName = str_replace("(","-",$fileUniqueName);
        $fileUniqueName = str_replace(")","-",$fileUniqueName);

        //$sourcePath = realpath($sourcePath);
        $sourceFile = $sourcePath . DIRECTORY_SEPARATOR . $fileName;

        //$destinationPath = realpath($destinationPath);
        $destinationFile = $destinationPath . DIRECTORY_SEPARATOR . $fileUniqueName;

        //copy $file to
        if( file_exists($sourceFile) ) {
            //echo "The file $file exists";
            rename($sourceFile, $destinationFile);
        } else {
            //echo "The file $file does not exist";
            exit("The file $sourceFile does not exist");
        }

        $filesize = filesize($destinationFile);
        //echo "inputFileSize=".$inputFileSize."<br>";
        if( !$filesize ) {
            exit("Invalid file size for file=".$destinationFile);
        }

        $object = new Document($user);
        $object->setCleanOriginalname($fileName);
        $object->setUniquename($fileUniqueName);
        $object->setUploadDirectory($destinationPath);         // "Uploaded/resapp/documents"
        $object->setSize($filesize);

        $documentTypeName = "Residency ERAS Document";
        $documentErasType = $em->getRepository('AppUserdirectoryBundle:DocumentTypeList')->findOneByName($documentTypeName);
        $object->setType($documentErasType);

        //exit('exit upload listener');
        $em->persist($object);
        //$em->flush();

        return $object;
    }

    //return created/updated array of DataResult objects existing in the Request
    public function processTableData( $inputDataFile, $form ) {
        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');
        $resappImportFromOldSystemUtil = $this->container->get('resapp_import_from_old_system_util');
        $resappPdfUtil = $this->container->get('resapp_pdfutil');

        $logger = $this->container->get('logger');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //////////////// process handsontable rows ////////////////
        $datajson = $form->get('datalocker')->getData();

        $data = json_decode($datajson, true);

        $updatedReasapps = array();
        $updatedStrArr = array();
        $usedErasDocumentArr = array();

        if( $data == null ) {
            //exit('Table order data is null.');
            $resultArr = array(
                'updatedReasapps' => array(),
                'updatedStr' => array()
            );
            return $resultArr;
        }

        //$headers = array_shift($data);
        $headers = $data["header"];
        //var_dump($headers);
        //echo "<br><br>";

        //echo "entity inst=".$entity->getInstitution()."<br>";
        //exit();

        //$systemUser = $userSecUtil->findSystemUser();

        $userkeytype = $userSecUtil->getUsernameType('local-user');
        if( !$userkeytype ) {
            throw new EntityNotFoundException('Unable to find local user keytype');
        }

        $employmentType = $em->getRepository('AppUserdirectoryBundle:EmploymentType')->findOneByName("Pathology Residency Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Residency Applicant");
        }

        $activeStatus = $em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("active");
        if( !$activeStatus ) {
            throw new EntityNotFoundException('Unable to find entity by name='."active");
        }

        $trainingType = $em->getRepository('AppUserdirectoryBundle:TrainingTypeList')->findOneByName('Medical');
        if( !$trainingType ) {
            throw new EntityNotFoundException("TrainingTypeList not found by name=Medical");
        }

        $residencyTrainingType = $em->getRepository('AppUserdirectoryBundle:TrainingTypeList')->findOneByName('Residency');
        if( !$residencyTrainingType ) {
            throw new EntityNotFoundException("TrainingTypeList not found by name=Residency");
        }

        //////////////////////// assign local institution from SiteParameters ////////////////////////
        $instPathologyResidencyProgram = null;
        $localInstitutionResApp = $userSecUtil->getSiteSettingParameter('localInstitutionResApp',$this->getParameter('resapp.sitename'));

        if( strpos($localInstitutionResApp, " (") !== false ) {
            //Case 1: get string from SiteParameters - "Pathology and Laboratory Medicine (WCM)" "Pathology Residency Programs (WCMC)"
            $localInstitutionResAppArr = explode(" (", $localInstitutionResApp);
            if (count($localInstitutionResAppArr) == 2 && $localInstitutionResAppArr[0] != "" && $localInstitutionResAppArr[1] != "") {
                $localInst = trim($localInstitutionResAppArr[0]); //"Pathology and Laboratory Medicine" "Pathology Residency Programs"
                $rootInst = trim($localInstitutionResAppArr[1]);  //"(WCMC)"
                $rootInst = str_replace("(", "", $rootInst);
                $rootInst = str_replace(")", "", $rootInst);
                //$logger->warning('rootInst='.$rootInst.'; localInst='.$localInst);
                $wcmc = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation($rootInst);
                if( !$wcmc ) {
                    $wcmc = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName($rootInst);
                    if( !$wcmc ) {
                        throw new EntityNotFoundException('Unable to find Institution by name=' . $rootInst);
                    }
                }
                $instPathologyResidencyProgram = $em->getRepository('AppUserdirectoryBundle:Institution')->findNodeByNameAndRoot($wcmc->getId(), $localInst);
                if( !$instPathologyResidencyProgram ) {
                    throw new EntityNotFoundException('Unable to find Institution by name=' . $localInst);
                }
            }
        } else {
            //Case 2: get string from SiteParameters - "WCM" or "Weill Cornell Medical College"
            $instPathologyResidencyProgram = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation($localInstitutionResApp);
            if( !$instPathologyResidencyProgram ) {
                $instPathologyResidencyProgram = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName($localInstitutionResApp);
            }
        }

        if( !$instPathologyResidencyProgram ) {
            $logger->warning('Local Institution for Bulk Upload Application is not set or invalid; localInstitutionResApp='.$localInstitutionResApp);
        }
        //////////////////////// EOF assign local institution from SiteParameters ////////////////////////

        $testing = false;
        //$testing = true;
        $count = 0;

        foreach( $data["row"] as $row ) {

            $count++;

            echo "<br><br>row=$count:<br>";
//            var_dump($row);
//            echo "<br>";
            //exit();

            $actionArr = $this->getValueByHeaderName('Action',$row,$headers);
            $actionValue = $actionArr['val'];
            $actionId = $actionArr['id'];

            //echo "actionId=".$actionId." <br>";
            //echo "actionValue=".$actionValue." <br>";

            $aamcIdArr = $this->getValueByHeaderName('AAMC ID',$row,$headers);
            $aamcIdValue = $aamcIdArr['val'];
            //$aamcIdId = $aamcIdArr['id'];
            //echo "aamcIdValue=".$aamcIdValue." <br>";
            //exit('111');

            $issueArr = $this->getValueByHeaderName('Status',$row,$headers); //Issue
            $issueValue = $issueArr['val']; //i.e. "Dupliacte in batch"
            //$issueId = $issueArr['id'];
            $issueStr = "";
            if( $issueValue ) {
                $issueStr = " (with status '$issueValue')";
            }

            $erasFileArr = $this->getValueByHeaderName('ERAS Application',$row,$headers);
            $erasFileValue = $erasFileArr['val'];
            $erasFileId = $erasFileArr['id']; //ERAS document ID
            $erasDocument = NULL;
            if( $erasFileId ) {
                $erasDocument = $em->getRepository('AppUserdirectoryBundle:Document')->find($erasFileId);
                //echo "Found eras by id=$erasFileId: ".$erasDocument." <br>";
            }
            echo "Found eras by id=$erasFileId: ".$erasDocument." <br>";
//            if( !$erasDocument ) {
//                //Do not attempt to find document by filename, because if the is deleted but filename can be the same as previos PDF file existing in DB.
//                if( $erasFileValue ) {
//                    $erasDocument = $em->getRepository('AppUserdirectoryBundle:Document')->findOneByOriginalname($erasFileValue);
//                    echo "Found eras name id=$erasFileValue: ".$erasDocument." <br>";
//                }
//            }

            $erasIdArr = $this->getValueByHeaderName('ERAS Application ID',$row,$headers);
            $erasIdValue = $erasIdArr['val'];
            //$erasIdId = $erasIdArr['id'];

            $receiptDateArr = $this->getValueByHeaderName('Application Receipt Date',$row,$headers);
            $receiptDateValue = $receiptDateArr['val'];
            //$receiptDateId = $receiptDateArr['id'];

            $seasonStartDateArr = $this->getValueByHeaderName('Application Season Start Date',$row,$headers);
            $seasonStartDateValue = $seasonStartDateArr['val'];
            //$seasonStartDateId = $seasonStartDateArr['id'];

            $seasonEndDateArr = $this->getValueByHeaderName('Application Season End Date',$row,$headers);
            $seasonEndDateValue = $seasonEndDateArr['val'];
            //$seasonEndDateId = $seasonEndDateArr['id'];

            $residencyStartDateArr = $this->getValueByHeaderName('Expected Residency Start Date',$row,$headers);
            $residencyStartDateValue = $residencyStartDateArr['val'];
            //$residencyStartDateId = $residencyStartDateArr['id'];

            $expectedGradDateArr = $this->getValueByHeaderName('Expected Graduation Date',$row,$headers);
            $expectedGradDateValue = $expectedGradDateArr['val'];
            //$expectedGradDateId = $expectedGradDateArr['id'];

            $middleNameArr = $this->getValueByHeaderName('Middle Name',$row,$headers);
            $middleNameValue = $middleNameArr['val'];
            //$middleNameId = $middleNameArr['id'];

            $firstNameArr = $this->getValueByHeaderName('First Name',$row,$headers);
            $firstNameValue = $firstNameArr['val'];
            //$firstNameId = $firstNameArr['id'];

            $lastNameArr = $this->getValueByHeaderName('Last Name',$row,$headers);
            $lastNameValue = $lastNameArr['val'];
            //$lastNameId = $lastNameArr['id'];

            $emailArr = $this->getValueByHeaderName('Preferred Email',$row,$headers);
            $emailValue = $emailArr['val'];
            $emailValue = strtolower($emailValue);
            //$emailId = $emailArr['id'];

            $medSchoolGradDateArr = $this->getValueByHeaderName('Medical School Graduation Date',$row,$headers);
            $medSchoolGradDateValue = $medSchoolGradDateArr['val'];
            //$medSchoolGradDateId = $medSchoolGradDateArr['id'];

            $medSchoolNameArr = $this->getValueByHeaderName('Medical School Name',$row,$headers);
            $medSchoolNameValue = $medSchoolNameArr['val'];
            //$medSchoolNameId = $medSchoolNameArr['id'];

            $degreeArr = $this->getValueByHeaderName('Degree',$row,$headers);
            $degreeValue = $degreeArr['val'];
            //$degreeId = $degreeArr['id'];

            //$schoolDegree = $resappImportFromOldSystemUtil->getDegreeMapping($degreeValue); //testing
            //continue;

            $usmle1Arr = $this->getValueByHeaderName('USMLE Step 1 Score',$row,$headers);
            $usmle1Value = $usmle1Arr['val'];
            //$usmle1Id = $usmle1Arr['id'];

            $usmle2Arr = $this->getValueByHeaderName('USMLE Step 2 CK Score',$row,$headers);
            $usmle2Value = $usmle2Arr['val'];
            //$usmle2Id = $usmle2Arr['id'];

            $usmle2CSArr = $this->getValueByHeaderName('USMLE Step 2 CS Score',$row,$headers);
            $usmle2CSValue = $usmle2CSArr['val'];
            //$usmle2CSId = $usmle2Arr['id'];

            $usmle3Arr = $this->getValueByHeaderName('USMLE Step 3 Score',$row,$headers);
            $usmle3Value = $usmle3Arr['val'];
            //$usmle3Id = $usmle3Arr['id'];

            $comlex1Arr = $this->getValueByHeaderName('COMLEX Level 1 Score',$row,$headers);
            $comlex1Value = $comlex1Arr['val'];
            //$comlex1Id = $usmle1Arr['id'];

            $comlex2Arr = $this->getValueByHeaderName('COMLEX Level 2 CE Score',$row,$headers);
            $comlex2Value = $comlex2Arr['val'];
            //$comlex2Id = $usmle1Arr['id'];

            $comlex2PEArr = $this->getValueByHeaderName('COMLEX Level 2 PE Score',$row,$headers);
            $comlex2PEValue = $comlex2PEArr['val'];
            //$comlex2PEId = $usmle1Arr['id'];

            $comlex3Arr = $this->getValueByHeaderName('COMLEX Level 3 Score',$row,$headers);
            $comlex3Value = $comlex3Arr['val'];
            //$comlex3Id = $usmle3Arr['id'];
            
//            $applicantName = $firstNameValue . " " . $lastNameValue;
//            if( !$firstNameValue && !$lastNameValue ) {
//                $applicantName = "Unknown Applicant";
//            }
            $applicantName = $firstNameValue . " " . $lastNameValue;
            if( !$firstNameValue && !$lastNameValue ) {
                //extract applicant name from PDF $erasDocument
                if( $erasDocument ) {
                    $applicantName = $resappPdfUtil->findKeyInDocument($erasDocument, 'Name:');
                }
            }
            if( !$applicantName ) {
                $applicantName = "Unknown Applicant";
            }

            $residencyApplicationDb = NULL;
            if( $erasIdValue ) {
                $residencyApplicationDb = $em->getRepository('AppResAppBundle:ResidencyApplication')->findOneByErasApplicantId($erasIdValue);
                //echo "1Found resapp?: $residencyApplicationDb <br>";
            }

            if( !$residencyApplicationDb ) {
                //Try to find by aamcId and startDate ("Expected Residency Start Date")
                $rowArr = array();
                $rowArr['AAMC ID']['value'] = $aamcIdValue;
                $rowArr['ERAS Application ID']['value'] = $erasIdValue;
                $rowArr['Expected Residency Start Date']['value'] = $residencyStartDateValue; //07/01/2019
                $rowArr['Application Receipt Date']['value'] = $receiptDateValue; //10/21/2020
                $rowArr['Preferred Email']['value'] = $emailValue;
                $rowArr['Last Name']['value'] = $lastNameValue;
                $rowArr['First Name']['value'] = $firstNameValue;

                $duplicateDbResApps = $resappPdfUtil->getDuplicateDbResApps($rowArr);
                if( count($duplicateDbResApps) > 0  ) {
                    $residencyApplicationDb = $duplicateDbResApps[0];
                }
                //echo "2Found resapp? (count=".count($duplicateDbResApps)."): $residencyApplicationDb <br>";
            }

            if( $actionValue == "Update PDF & ID Only" ) {

                //$updateInfo = "";

                if( !$residencyApplicationDb ) {
                    $updateInfo = "ERAS Applicant ID $erasIdValue for " .
                        $applicantName . $issueStr.
                    ". ERROR: Existing application not found.";
                    $updatedStrArr["Skip updating PDF for existing residency application. ERROR: Existing application not found"][] = $updateInfo;
                    continue;
                }

                $reasppUpdated = false;
                //Update PDF only
                if ($erasDocument) {
                    $residencyApplicationDb->addCoverLetter($erasDocument);
                    $reasppUpdated = true;
                    $usedErasDocumentArr[$erasDocument->getId()] = true;
                    $updateInfo = "; PDF file updated ".$erasDocument->getOriginalname();
                } else {
                    $updateInfo = "; ERROR: PDF file not found";
                }
                //echo "updateInfo=$updateInfo; ID=".$erasDocument->getId()." <br>";
                //update $erasIdValue if null
                if ($erasIdValue && !$residencyApplicationDb->getErasApplicantId()) {
                    $residencyApplicationDb->setErasApplicantId($erasIdValue);
                    $reasppUpdated = true;
                    $updateInfo = "; Added ERAS applicant ID $erasIdValue";
                }

                if( !$erasIdValue ) {
                    $erasIdValue = "Unknown ERAS";
                }

                if ($reasppUpdated) {
                    if (!$testing) {
                        $em->flush();
                        $updatedReasapps[] = $residencyApplicationDb;

                        $updateInfo = "ERAS Applicant ID $erasIdValue for " . $applicantName .
                            " with ID=" . $residencyApplicationDb->getId() . $issueStr . $updateInfo;
                        $updatedStrArr["Updating PDF for existing residency application"][] = $updateInfo;
                        echo $updateInfo . "<br>";
                    }
                } else {
                    $updateInfo = "ERAS Applicant ID $erasIdValue for " . $applicantName .
                        " with ID=" . $residencyApplicationDb->getId() . $issueStr . $updateInfo;
                    $updatedStrArr["Skip updating PDF for existing residency application"][] = $updateInfo;
                }

                //dump($updatedStrArr);
                //exit('exit update pdf');
                continue;
            }

//            if( $actionValue == "Do not add" || $residencyApplicationDb ) {
//                //echo "Do not add row=$count <br>";
//
//                //Remove eras application PDF document file
//                $this->removeErasPdfFile($inputDataFile,$erasDocument,$usedErasDocumentArr,$testing);
//
//                if( $actionValue == "Do not add" && $residencyApplicationDb ) {
//                    $updatedStrArr["Skip existing residency application, marked as '$actionValue'$issueStr"][] = "$applicantName with ID=" . $residencyApplicationDb->getId();
//                }
//                elseif( $residencyApplicationDb ) {
//                    $updatedStrArr["Skip existing residency application$issueStr"][] = "$applicantName with ID=" . $residencyApplicationDb->getId();
//                    //exit("Skip existing residency application $actionValue !!!");
//                }
//                elseif( $actionValue ) {
//                    $updatedStrArr["Skip residency application, marked as '$actionValue'$issueStr"][] = $applicantName."";
//                }
//
//                continue;
//            } //action != Add
            if( $actionValue == "Do not add" ) {
                //echo "Do not add row=$count <br>";
                //Remove eras application PDF document file
                $this->removeErasPdfFile($inputDataFile,$erasDocument,$usedErasDocumentArr,$testing);
                //$updatedStrArr["Skip residency application, marked as '$actionValue'$issueStr"][] = $applicantName."";
                $updatedStrArr["Skip residency application, marked as '$actionValue'"][] = $applicantName.$issueStr;
                continue;
            } //action != Add

            //Get $actionId from 'Action' string: 'Add to FirstNAme LastName (ID 123)'
            if( strpos($actionValue, 'Add to ') !== false ) {
                echo "actionId=".$actionId." <br>";
                echo "actionValue=".$actionValue." <br>";
                if( !$actionId ) {
                    //actionValue=Add to Steven Adams's application (ID 746)
                    $actionIdArr = explode("(ID", $actionValue);
                    if (count($actionIdArr) == 2) {
                        $actionId = $actionIdArr[1];
                        $actionId = str_replace(")", "", $actionId);
                        $actionId = trim($actionId);
                    }
                }
            }

            if( strpos($actionValue, 'Add to ') !== false && $actionId ) {
                echo "actionId=".$actionId." <br>";
                echo "actionValue=".$actionValue." <br>";
                //Add PDF to this resapp by id $actionId
                $updateInfo = "";

//                $applicantName = $firstNameValue . " " . $lastNameValue;
//                if( !$firstNameValue && !$lastNameValue ) {
//                    //extract applicant name from PDF $erasDocument
//                    $applicantName = $resappPdfUtil->findKeyInDocument($erasDocument,'Name:');
//                    if( !$applicantName ) {
//                        $applicantName = "Unknown Applicant";
//                    }
//                }

                $residencyApplicationDb = $em->getRepository('AppResAppBundle:ResidencyApplication')->find($actionId);
                echo "residencyApplicationDb=".$residencyApplicationDb->getId()." <br>";
                if( !$residencyApplicationDb ) {
                    $updateInfo = "ERAS Applicant ID $erasIdValue for " . $applicantName .
                        " with ID=" . $actionId . $issueStr.
                        ". ERROR: Existing application not found.";
                    $updatedStrArr["Skip add PDF to the existing residency application. ERROR: Existing application not found"][] = $updateInfo;
                    continue;
                }

                $reasppUpdated = false;
                //Add PDF to Other Document section
                if( $erasDocument ) {
                    $residencyApplicationDb->addDocument($erasDocument);
                    $reasppUpdated = true;
                    $usedErasDocumentArr[$erasDocument->getId()] = true;
                    $updateInfo = "; PDF file added ".$erasDocument->getOriginalname();
                } else {
                    $updateInfo = "; ERROR: PDF file not found";
                }
                //echo "updateInfo=$updateInfo; ID=".$erasDocument->getId()." <br>";

                //update $erasIdValue if null
                if ($erasIdValue && !$residencyApplicationDb->getErasApplicantId()) {
                    $residencyApplicationDb->setErasApplicantId($erasIdValue);
                    $reasppUpdated = true;
                    $updateInfo = "; Added ERAS applicant ID $erasIdValue";
                }

                if( !$erasIdValue ) {
                    $erasIdValue = "Unknown ERAS";
                }

                if ($reasppUpdated) {
                    if (!$testing) {
                        $em->flush();
                        $updatedReasapps[] = $residencyApplicationDb;

                        $updateInfo = "ERAS Applicant ID $erasIdValue for " . $applicantName .
                             " with ID=" . $residencyApplicationDb->getId() . $issueStr . $updateInfo;
                        $updatedStrArr["Adding PDF to the existing residency application"][] = $updateInfo;
                        echo $updateInfo . "<br>";
                    } else {
                        $updateInfo = "ERAS Applicant ID $erasIdValue for " . $applicantName .
                            " with ID=" . $residencyApplicationDb->getId() . $issueStr . $updateInfo;
                        $updatedStrArr["Adding PDF to the existing residency application"][] = $updateInfo;
                        //echo $updateInfo . "<br>";
                    }
                } else {
                    $updateInfo = "ERAS Applicant ID $erasIdValue for " . $applicantName .
                         " with ID=" . $residencyApplicationDb->getId() . $issueStr . $updateInfo;
                    $updatedStrArr["Skip adding PDF to the existing residency application"][] = $updateInfo;
                }

                //dump($updatedStrArr);
                //exit('exit adding pdf to resapp ID='.$residencyApplicationDb->getId());
                continue;
            } //'Add to '

//            //Previous condition should catch this too ("Create New Record" + $residencyApplicationDb)
//            if( $actionValue == "Create New Record" ) {
//                //exit("$actionValue !!!");
//
//                /////// testing: remove this temporary condition below after testing is completed //////
//                if( $residencyApplicationDb ) {
//                    //Remove eras application PDF document file
//                    $this->removeErasPdfFile($inputDataFile,$erasDocument,$usedErasDocumentArr,$testing);
//                    $updatedStrArr["Testing: Skip residency application, marked as '$actionValue'$issueStr"][] = "$applicantName with ID=" . $residencyApplicationDb->getId();
//                    continue;
//                }
//                /////// EOF testing: remove this temporary condition below after testing is completed //////
//            }

            /////////////// $actionValue == "Create New Record" ///////////////
            $countryCitizenshipArr = $this->getValueByHeaderName('Country of Citizenship',$row,$headers);
            $countryCitizenshipValue = $countryCitizenshipArr['val'];
            //$countryCitizenshipId = $countryCitizenshipArr['id'];

            $visaStatusArr = $this->getValueByHeaderName('Visa Status',$row,$headers);
            $visaStatusValue = $visaStatusArr['val'];
            //$visaStatusId = $visaStatusArr['id'];

            $ethnicGroupArr = $this->getValueByHeaderName('Is the applicant a member of any of the following groups?',$row,$headers);
            $ethnicGroupValue = $ethnicGroupArr['val'];
            //$ethnicGroupId = $ethnicGroupArr['id'];

            $numberFirstAuthorPublicationsArr = $this->getValueByHeaderName('Number of first author publications',$row,$headers);
            $numberFirstAuthorPublicationsValue = $numberFirstAuthorPublicationsArr['val'];
            //$numberFirstAuthorPublicationsId = $numberFirstAuthorPublicationsArr['id'];

            $numberAllPublicationsArr = $this->getValueByHeaderName('Number of all publications',$row,$headers);
            $numberAllPublicationsValue = $numberAllPublicationsArr['val'];
            //$numberAllPublicationsId = $numberAllPublicationsArr['id'];

            $aoaArr = $this->getValueByHeaderName('AOA',$row,$headers);
            $aoaValue = $aoaArr['val'];
            //$aoaId = $aoaArr['id'];

            $coupleArr = $this->getValueByHeaderName('Couple’s Match',$row,$headers);
            $coupleValue = $coupleArr['val'];
            //$coupleId = $coupleArr['id'];

            $postSophomoreArr = $this->getValueByHeaderName('Post-Sophomore Fellowship',$row,$headers);
            $postSophomoreValue = $postSophomoreArr['val'];
            //$postSophomoreId = $postSophomoreArr['id'];
            
            $previousResidencyStartDateArr = $this->getValueByHeaderName('Previous Residency Start Date',$row,$headers);
            $previousResidencyStartDateValue = $previousResidencyStartDateArr['val'];
            //$previousResidencyStartDateId = $previousResidencyStartDateArr['id'];

            $previousResidencyGradDateArr = $this->getValueByHeaderName('Previous Residency Graduation/Departure Date',$row,$headers);
            $previousResidencyGradDateValue = $previousResidencyGradDateArr['val'];
            //$previousResidencyGradDateId = $previousResidencyGradDateArr['id'];

            $previousResidencyInstitutionArr = $this->getValueByHeaderName('Previous Residency Institution',$row,$headers);
            $previousResidencyInstitutionValue = $previousResidencyInstitutionArr['val'];
            //$previousResidencyInstitutionId = $previousResidencyInstitutionArr['id'];

            $previousResidencyCityArr = $this->getValueByHeaderName('Previous Residency City',$row,$headers);
            $previousResidencyCityValue = $previousResidencyCityArr['val'];
            //$previousResidencyCityId = $previousResidencyCityArr['id'];

            $previousResidencyStateArr = $this->getValueByHeaderName('Previous Residency State',$row,$headers);
            $previousResidencyStateValue = $previousResidencyStateArr['val'];
            //$previousResidencyStateId = $previousResidencyStateArr['id'];

            $previousResidencyCountryArr = $this->getValueByHeaderName('Previous Residency Country',$row,$headers);
            $previousResidencyCountryValue = $previousResidencyCountryArr['val'];
            //$previousResidencyCountryId = $previousResidencyCountryArr['id'];

            $previousResidencyTrackArr = $this->getValueByHeaderName('Previous Residency Track',$row,$headers);
            $previousResidencyTrackValue = $previousResidencyTrackArr['val']; //Not in CSV file
            //$previousResidencyTrackId = $previousResidencyTrackArr['id'];

            //Do no create if some key fields are missing
            //'First Name', 'Last Name', 'Preferred Email', 'Expected Residency Start Date'
            $missingFieldsArr = array();
            if( !$firstNameValue ) {
//                $updateInfo = $firstNameValue . " " . $lastNameValue . $issueStr.
//                    " Missing First Name.";
//                $updatedStrArr["Skip creating residency application (missing fields)"][] = $updateInfo;
//                continue;
                $missingFieldsArr[] = "First Name";
            }
            if( !$lastNameValue ) {
//                $updateInfo = $firstNameValue . " " . $lastNameValue . $issueStr.
//                    " Missing Last Name.";
//                $updatedStrArr["Skip creating residency application (missing fields)"][] = $updateInfo;
//                continue;
                $missingFieldsArr[] = "Last Name";
            }
            if( !$emailValue ) {
//                $updateInfo = $firstNameValue . " " . $lastNameValue . $issueStr.
//                    " Missing Preferred Email.";
//                $updatedStrArr["Skip creating residency application (missing fields)"][] = $updateInfo;
//                continue;
                $missingFieldsArr[] = "Preferred Email";
            }
            if( !$residencyStartDateValue ) {
//                $updateInfo = $firstNameValue . " " . $lastNameValue . $issueStr.
//                    " Missing Expected Residency Start Date.";
//                $updatedStrArr["Skip creating residency application (missing fields)"][] = $updateInfo;
//                continue;
                $missingFieldsArr[] = "Expected Residency Start Date";
            }
//            if( !$medSchoolNameValue ) {
//                $updateInfo = $firstNameValue . " " . $lastNameValue . $issueStr.
//                    ". ERROR: Missing Medical School.";
//                $updatedStrArr["Skip creating residency application. ERROR: Missing Medical School"][] = $updateInfo;
//                continue;
//            }
//            if( !$usmle1Value ) {
//                $updateInfo = $firstNameValue . " " . $lastNameValue . $issueStr.
//                    ". ERROR: Missing USMLE Score Step 1.";
//                $updatedStrArr["Skip creating residency application. ERROR: Missing USMLE Score Step 1"][] = $updateInfo;
//                continue;
//            }
            if( count($missingFieldsArr) > 0 ) {
                $updatedStrArr["Skip creating residency application (missing fields)"][] = implode(", ",$missingFieldsArr);
                continue;
            }

            ///////////////// Create new user or get the existed user //////////////////////
            $userArr = array(
                'creator' => $user, //$systemUser,
                'employmenttype' => $employmentType,
                'userkeytype' => $userkeytype,
                'email' => $emailValue,
                'firstname' => $firstNameValue,
                'lastname' => $lastNameValue,
                'middlename' => $middleNameValue,
                //'displayname' => $displayName
            );
            $resappUser = $this->createNewResappUser($userArr);
            ///////////////// EOF Create new user or get the existed user //////////////////////

            ///////////////// Create new ResidencyApplication //////////////////////
            $residencyApplication = new ResidencyApplication($user);
            $resappUser->addResidencyApplication($residencyApplication);

            $residencyApplication->setAppStatus($activeStatus);
            //$residencyApplication->setGoogleFormId($googleFormId);

            $residencyApplication->setAamcId($aamcIdValue);
            $residencyApplication->setErasApplicantId($erasIdValue);

            if( $erasDocument ) {
                $residencyApplication->addCoverLetter($erasDocument);
                $usedErasDocumentArr[$erasDocument->getId()] = true;
                $addedPdfInfo = " (Added PDF ".$erasDocument->getOriginalname().")";
            } else {
                $addedPdfInfo = " (PDF file is missing)";
            }

            if( $seasonStartDateValue ) {
                //$seasonStartDateTime = date("m/d/Y", strtotime($seasonStartDateValue));
                //echo "seasonStartDateValue=$seasonStartDateValue <br>";
                $seasonStartDateTime = $this->getDatetimeFromStr($seasonStartDateValue);
                $residencyApplication->setApplicationSeasonStartDate($seasonStartDateTime);
            }
            if( $seasonEndDateValue ) {
                //echo "seasonEndDateValue=$seasonEndDateValue <br>";
                //$seasonEndDateTime = date("m/d/Y", strtotime($seasonEndDateValue));
                $seasonEndDateTime = $this->getDatetimeFromStr($seasonEndDateValue);
                $residencyApplication->setApplicationSeasonEndDate($seasonEndDateTime);
            }

            if( $residencyStartDateValue ) {
                //echo "residencyStartDateValue=$residencyStartDateValue <br>";
                //$residencyStartDateTime = date("m/d/Y", strtotime($residencyStartDateValue));
                $residencyStartDateTime = $this->getDatetimeFromStr($residencyStartDateValue);
                $residencyApplication->setStartDate($residencyStartDateTime);
            }
            if( $expectedGradDateValue ) {
                //echo "expectedGradDateValue=$expectedGradDateValue <br>";
                //$expectedGradDateTime = date("m/d/Y", strtotime($expectedGradDateValue));
                $expectedGradDateTime = $this->getDatetimeFromStr($expectedGradDateValue);
                $residencyApplication->setEndDate($expectedGradDateTime);
            }

            //$medSchoolGradDateValue, $medSchoolNameValue, $degreeValue
            if( $medSchoolGradDateValue || $medSchoolNameValue || $degreeValue ) {
                $training = new Training($user);
                $training->setOrderinlist(10);
                $training->setTrainingType($trainingType); //Medical

                $residencyApplication->addTraining($training);
                $resappUser->addTraining($training);

                //$schoolDegree = $resappImportFromOldSystemUtil->getDegreeMapping($degreeValue);
                
                if( $degreeValue ) {
                    if( !$testing ) {
                        $resappImportFromOldSystemUtil->setTrainingDegree($training, $degreeValue, $user);
                    }
                } else {
                    //exit("Unknown degreeValue=[$degreeValue]");
                }

                if( $medSchoolGradDateValue ) {
                    $medSchoolGradDateTime = $this->getDatetimeFromStr($medSchoolGradDateValue);
                    //echo "medSchoolGradDateValue: $medSchoolGradDateValue => " . $medSchoolGradDateTime->format('d-m-Y') . "<br>";
                    $training->setCompletionDate($medSchoolGradDateTime);
                }

                if( $medSchoolNameValue ) {
                    $params = array('type'=>'Educational');
                    $medSchool = trim($medSchoolNameValue);
                    //$medSchool = $this->capitalizeIfNotAllCapital($medSchool);
                    if( !$testing ) {
                        $transformer = new GenericTreeTransformer($em, $user, 'Institution', null, $params);
                        $schoolNameEntity = $transformer->reverseTransform($medSchool);
                        $training->setInstitution($schoolNameEntity);
                    }
                }
            } //if $medSchoolGradDateValue, $medSchoolNameValue, $degreeValue

            $timestampDate = NULL;
            if( $receiptDateValue ) {
                //Convert $receiptDateValue 9/15/2018
                //make same format (mm/dd/YYYY) 5/5/1987=>05/05/1987
                //$cellValue = date("m/d/Y", strtotime($cellValue));
                //echo "Converting Application Receipt Date $receiptDateValue: ";
                $timestampDate = $this->getDatetimeFromStr($receiptDateValue);
                $residencyApplication->setTimestamp($timestampDate);
            }

            $resTrackArr = $this->getValueByHeaderName('Residency Track',$row,$headers);
            $resTrackValue = $resTrackArr['val'];
            //$resTrackId = $resTrackArr['id'];
            if( $resTrackValue ) {
                $residencyTrack = $em->getRepository('AppUserdirectoryBundle:ResidencyTrackList')->findOneByName($resTrackValue);
                //echo "residencyTrack found=".$residencyTrack."<br>";
                if( $residencyTrack ) {
                    $residencyApplication->setResidencyTrack($residencyTrack);
                }
            }

            if( $instPathologyResidencyProgram ) {
                $residencyApplication->setInstitution($instPathologyResidencyProgram);
            }

            //USMLE scores: $usmleStep1, $usmleStep2, $usmleStep3
            $examination = new Examination($user);
            //USMLE
            if( $usmle1Value ) {
                $examination->setUSMLEStep1Score($usmle1Value);
            }
            if( $usmle2Value ) {
                $examination->setUSMLEStep2CKScore($usmle2Value);
            }
            if( $usmle2CSValue ) {
                $examination->setUSMLEStep2CSScore($usmle2CSValue);
            }
            if( $usmle3Value ) {
                $examination->setUSMLEStep3Score($usmle3Value);
            }
            //COMLEX
            if( $comlex1Value ) {
                $examination->setCOMLEXLevel1Score($comlex1Value);
            }
            if( $comlex2Value ) {
                $examination->setCOMLEXLevel2Score($comlex2Value);
            }
            if( $comlex2PEValue ) {
                $examination->setCOMLEXLevel2PEScore($comlex2PEValue);
            }
            if( $comlex3Value ) {
                $examination->setCOMLEXLevel3Score($comlex3Value);
            }
            $residencyApplication->addExamination($examination);

            //$countryCitizenshipValue: U.S. Citizen, Foreign National Currently in the U.S. with Valid Visa Status
            if( $countryCitizenshipValue || $visaStatusValue ) {
                $citizenship = new Citizenship($user);
                $residencyApplication->addCitizenship($citizenship);

                if( $countryCitizenshipValue ) {
                    //$countryCitizenshipStr = $resappImportFromOldSystemUtil->getCitizenshipMapping($countryCitizenshipValue);
                    $countryCitizenshipValue = trim($countryCitizenshipValue);
                    $transformer = new GenericTreeTransformer($em, $user, 'Countries');
                    if( !$testing ) {
                        $citizenshipCountryEntity = $transformer->reverseTransform($countryCitizenshipValue);
                        $citizenship->setCountry($citizenshipCountryEntity);
                    }
                }

                if( $visaStatusValue ) {
                    $citizenship->setVisa($visaStatusValue);
                }
            }

            if( $ethnicGroupValue ) {
                $residencyApplication->setEthnicity($ethnicGroupValue); //string
            }

            if( $numberFirstAuthorPublicationsValue ) {
                $residencyApplication->setFirstPublications($numberFirstAuthorPublicationsValue);
            }

            if( $numberAllPublicationsValue ) {
                $residencyApplication->setAllPublications($numberAllPublicationsValue);
            }

            if( $aoaValue ) {
                if( strtolower($aoaValue) != "no" ) {
                    $residencyApplication->setAoa(true);
                }
            }

            if( $coupleValue ) {
                if( strtolower($coupleValue) != "no" ) {
                    $residencyApplication->setCouple(true);
                }
            }

            //Not available in ERAS CSV
            if( $postSophomoreValue ) {
                //PostSophList
                $transformer = new GenericTreeTransformer($em, $user, 'PostSophList', 'ResAppBundle');
                if( !$testing ) {
                    $postSophomoreEntity = $transformer->reverseTransform($postSophomoreValue);
                    $residencyApplication->setPostSoph($postSophomoreEntity);
                }
            }

            if( $previousResidencyStartDateValue ||
                $previousResidencyGradDateValue ||
                $previousResidencyInstitutionValue ||
                $previousResidencyCityValue ||
                $previousResidencyStateValue ||
                $previousResidencyCountryValue
                || $previousResidencyTrackValue
            ) {
                $training = new Training($user);
                $training->setOrderinlist(20);
                $residencyApplication->addTraining($training);
                $residencyApplication->getUser()->addTraining($training);

                $training->setTrainingType($residencyTrainingType);

                $params = array('type'=>'Medical');
                $transformer = new GenericTreeTransformer($em, $user, 'Institution', null, $params);
                $previousResidencyInstitutionEntity = $transformer->reverseTransform($previousResidencyInstitutionValue);
                $training->setInstitution($previousResidencyInstitutionEntity);

                //echo "Converting previousResidencyStartDate [$previousResidencyStartDateValue]: ";
                $previousResidencyStartDate = $this->getDatetimeFromStr($previousResidencyStartDateValue);
                if( $previousResidencyStartDate ) {
                    //echo "$previousResidencyStartDateValue =>" . $previousResidencyStartDate->format('dd-mm-Y') . "<br>";
                    $training->setStartDate($previousResidencyStartDate);
                }

                //echo "Converting previousResidencyGradDate [$previousResidencyGradDateValue]: ";
                $previousResidencyGradDate = $this->getDatetimeFromStr($previousResidencyGradDateValue);
                if( $previousResidencyGradDate ) {
                    //echo "$previousResidencyStartDateValue =>" . $previousResidencyStartDate->format('dd-mm-Y') . "<br>";
                    $training->setCompletionDate($previousResidencyGradDate);
                }

                if( $previousResidencyTrackValue ) {
                    $previousResidencyTrack = $em->getRepository('AppUserdirectoryBundle:ResidencyTrackList')->findOneByName($previousResidencyTrackValue);
                    if( $previousResidencyTrack ) {
                        $training->setResidencyTrack($previousResidencyTrack);
                    }
                }

                //GeoLocation
                if( $previousResidencyCityValue ||
                    $previousResidencyStateValue ||
                    $previousResidencyCountryValue
                ) {
                    $previousGeoLocation = new GeoLocation();
                    $training->setGeoLocation($previousGeoLocation);

                    //CityList
                    $transformer = new GenericTreeTransformer($em, $user, 'CityList');
                    if( !$testing ) {
                        $previousResidencyCityEntity = $transformer->reverseTransform($previousResidencyCityValue);
                        $previousGeoLocation->setCity($previousResidencyCityEntity);
                    }

                    //States
                    $transformer = new GenericTreeTransformer($em, $user, 'States');
                    if( !$testing ) {
                        $previousResidencyStateEntity = $transformer->reverseTransform($previousResidencyStateValue);
                        $previousGeoLocation->setState($previousResidencyStateEntity);
                    }

                    //$previousResidencyCountryValue Countries
                    $transformer = new GenericTreeTransformer($em, $user, 'Countries');
                    if( !$testing ) {
                        $previousResidencyCountryEntity = $transformer->reverseTransform($previousResidencyCountryValue);
                        $previousGeoLocation->setCountry($previousResidencyCountryEntity);
                    }

                }
            }

            ///////////////// EOF Create new ResidencyApplication //////////////////////
            /////////////// EOF $actionValue == "Create New Record" ///////////////

            if( !$testing ) {
                $em->flush();
                $updatedReasapps[] = $residencyApplication;

                $updateInfo = $firstNameValue." ".$lastNameValue." with ID ".$residencyApplication->getId().$issueStr.$addedPdfInfo;
                $updatedStrArr["Added residency application"][] = $updateInfo;
                echo $updateInfo."<br>";
            }

            //exit("End of process handsontable. Count=$count");

        }//foreach row

        //TODO: add generate application in PDF

        if( $testing ) {
            dump($updatedStrArr);
            exit("<br><br>End of process handsontable. Count=$count");
        }

        $resultArr = array(
            'updatedReasapps' => $updatedReasapps,
            'updatedStr' => $updatedStrArr
        );

        return $resultArr;
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

    //Remove eras application PDF document file if not used in $usedErasDocumentArr
    public function removeErasPdfFile($inputDataFile,$erasDocument,$usedErasDocumentArr,$testing=false) {
        $em = $this->getDoctrine()->getManager();
        //Remove eras application PDF document file
        if( $erasDocument ) {
            if( $usedErasDocumentArr && isset($usedErasDocumentArr[$erasDocument->getId()]) ) {
                //File is used previously => do not delete
                return false;
            }

            if( $inputDataFile ) {
                $inputDataFile->removeErasFile($erasDocument);
            }

            $em->remove($erasDocument);

            if( !$testing ) {
                $em->flush();
            }
        }

        return true;
    }

    public function getDatetimeFromStr( $datetimeStr ) {
        //echo "getting $datetimeStr <br>";
        if( !$datetimeStr ) {
            return $datetimeStr;
        }
        $resappImportFromOldSystemUtil = $this->container->get('resapp_import_from_old_system_util');
        $datetime = $resappImportFromOldSystemUtil->transformDatestrToDate($datetimeStr);
        //echo "$datetimeStr => ".$datetime->format('d-m-Y')."<br>";
        return $datetime;

        //$datetime = strtotime($datetimeStr);
//        $datetime = date("m/d/Y", strtotime($datetimeStr));  //string
//        echo "datetime=$datetime <br>";
//        echo "$datetimeStr => ".$datetime->format('d-m-Y')."<br>";
//        return $datetime;
    }

    //Create or get existing resapp user
    public function createNewResappUser( $userArr ) {
        $em = $this->getDoctrine()->getManager();

        $default_time_zone = $this->getParameter('default_time_zone');

        $creatorUser = $userArr['creator'];
        $employmentType = $userArr['employmenttype'];
        $userkeytype = $userArr['userkeytype'];
        $email = $userArr['email'];
        $firstName = $userArr['firstname'];
        $lastName = $userArr['lastname'];
        $middleName = $userArr['middlename'];

        /////////////// Create unique username (PrimaryPublicUserId aka cwid) ///////////////
        $lastNameCap = $this->capitalizeIfNotAllCapital($lastName);
        $firstNameCap = $this->capitalizeIfNotAllCapital($firstName);
        //$middleNameCap = $this->capitalizeIfNotAllCapital($middleName);

        $lastNameCap = preg_replace('/\s+/', '_', $lastNameCap);
        $firstNameCap = preg_replace('/\s+/', '_', $firstNameCap);

        //Last Name + First Name + Email
        $username = $lastNameCap . "_" . $firstNameCap . "_" . $email;

        $displayName = $firstName . " " . $lastName;
        if ($middleName) {
            $displayName = $firstName . " " . $middleName . " " . $lastName;
        }
        /////////////// EOF Create unique username ///////////////

        //check if the user already exists in DB by $googleFormId
        $user = $em->getRepository('AppUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($username);

        //Try to find by email
        if( !$user ) {
            if( $email ) {
                $email = strtolower($email);
                $users = $em->getRepository('AppUserdirectoryBundle:User')->findUserByUserInfoEmail($email);
                if( count($users) == 1 ) {
                    $user = $users[0];
                }
                if( count($users) > 1 ) {
                    $user = $users[0]; //use the first found user
                    //exit("Multiple users found count=".count($users)." by email ".$email);
                    //Event Log
                    $userSecUtil = $this->container->get('user_security_utility');
                    $eventType = 'Residency Application Bulk Upload';
                    $msg = "Warning: Multiple users found count=".count($users)." by email ".$email. ". Use the first found user $user";
                    $userSecUtil->createUserEditEvent($this->getParameter('resapp.sitename'),$msg,$creatorUser,null,null,$eventType);
                }
            }
        }

        //TODO: Applicant might exist in DB from the old system without email.
        //Try to find by First Name, Family Name and then in ERAS pdf find matching AAMC ID

        if( $user ) {
            echo "Found user in DB $user <br>";
            return $user;
        }

        //create excel user
        echo "Create new user $username<br>";
        $addobjects = false;
        $user = new User($addobjects);
        $user->setKeytype($userkeytype);
        $user->setPrimaryPublicUserId($username);

        //set unique username
        $usernameUnique = $user->createUniqueUsername();
        $user->setUsername($usernameUnique);
        $user->setUsernameCanonical($usernameUnique);


        $user->setEmail($email);
        $user->setEmailCanonical($email);

        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setMiddleName($middleName);
        $user->setDisplayName($displayName);
        $user->setPassword("");
        $user->setCreatedby('csv-eras');
        $user->getPreferences()->setTimezone($default_time_zone);
        $user->setLocked(true);

        //Pathology Residency Applicant in EmploymentStatus
        $employmentStatus = new EmploymentStatus($creatorUser);
        $employmentStatus->setEmploymentType($employmentType);
        $user->addEmploymentStatus($employmentStatus);

        $em->persist($user);

        return $user;
    }
    public function capitalizeIfNotAllCapital($s) {
        if( !$s ) {
            return $s;
        }
        $convert = false;
        //check if all UPPER
        if( strtoupper($s) == $s ) {
            $convert = true;
        }
        //check if all lower
        if( strtolower($s) == $s ) {
            $convert = true;
        }
        if( $convert ) {
            return ucwords( strtolower($s) );
        }
        return $s;
    }






    /**
     * Used for Testing Only: Upload Multiple Applications via PDF
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

            $pdfArr = $resappPdfUtil->getTestPdfApplications();
            $dataArr = $resappPdfUtil->getParsedDataArray($pdfArr);
            $handsomtableJsonData = $resappPdfUtil->getHandsomtableDataArray($dataArr);
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

            //ERAS Application ID
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

    /**
     * Used for Testing Only: Upload Multiple Applications
     *
     * @Route("/pdf-parser-test/", name="resapp_pdf_parser_test", methods={"GET"})
     * @Template("AppResAppBundle/Upload/upload-applications.html.twig")
     */
    public function pdfParserTestAction(Request $request)
    {
        exit("not allowed. one time run method.");

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

        $resapps = $this->getTestApplications();

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
    

//    public function parsePdfSetasign($path) {
//
//        if (file_exists($path)) {
//            //echo "The file $path exists<br>";
//        } else {
//            //==echo "The file $path does not exist<br>";
//        }
//
//        if(0) {
//            $resappRepGen = $this->container->get('resapp_reportgenerator');
//            $processedFiles = $resappRepGen->processFilesGostscript(array($path));
//
//            if (count($processedFiles) > 0) {
//                $dir = dirname($path);
//                $path = $processedFiles[0];
//                $path = str_replace('"', '', $path);
//                //$path = $dir.DIRECTORY_SEPARATOR.$path;
//                $path = "C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/temp/eras_gs.pdf";
//                echo "path=" . $path . "<br>";
//            } else {
//                return null;
//            }
//        }
//
//        $path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\eras_gs.pdf";
//
//        // create a document instance
//        //$document = SetaPDF_Core_Document::loadByFilename('Laboratory-Report.pdf');
//        // create an extractor instance
//        //$extractor = new SetaPDF_Extractor($document);
//        // get the plain text from page 1
//        //$result = $extractor->getResultByPageNumber(1);
//
//
////        // initiate FPDI
//        $pdf = new Fpdi();
////        // add a page
//        //$pdf->AddPage();
////        // set the source file
//        $pdf->setSourceFile($path); //"Fantastic-Speaker.pdf";
////        // import page 1
//        //$tplId = $pdf->importPage(1);
//        $tplId = $pdf->importPage(2);
////        //dump($tplId);
////        //exit('111');
//        $pdf->AddPage();
////        // use the imported page and place it at point 10,10 with a width of 100 mm
//        $pdf->useTemplate($tplId, 10, 10, 100);
////
////        //$pdf->Write();
////        //$pdf->WriteHTML($html);
//        $pdf->Output('I', 'generated.pdf');
////
////        //dump($pdf->Output());
////        //exit('111');
////        //dump($pdf);
//    }
//    public function parsePdfCirovargas($path) {
//
//        if (file_exists($path)) {
//            //echo "The file $path exists<br>";
//        } else {
//            echo "The file $path does not exist<br>";
//        }
//
//        $field = null;
//
//        if(0) {
//            $resappRepGen = $this->container->get('resapp_reportgenerator');
//            $processedFiles = $resappRepGen->processFilesGostscript(array($path));
//
//            if (count($processedFiles) > 0) {
//                $dir = dirname($path);
//                $path = $processedFiles[0];
//                $path = str_replace('"', '', $path);
//                //$path = $dir.DIRECTORY_SEPARATOR.$path;
//                $path = "C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/temp/eras_gs.pdf";
//                echo "path=" . $path . "<br>";
//            } else {
//                return null;
//            }
//        }
//
//        //$path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\eras_gs.pdf";
//        //$path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\temp\\PackingSlip.pdf";
//
//        $pdfService = new PDFService();
//        $text = $pdfService->pdf2text($path);
//
//        if('' == trim($text)) {
//            //echo "Use parseFile:<br>";
//            $text = $pdfService->parseFile($path);
//        }
//
//        $startStr = "Applicant ID:";
//        $endStr = "AAMC ID:";
//        $field = $this->getPdfField($text,$startStr,$endStr);
//        echo "Cirovargas: $startStr=[".$field."]<br>";
//        //exit();
//
//        dump($text);
//        //exit();
//        return $field;
//    }
//    public function parsePdfSmalot($path) {
//
//        if (file_exists($path)) {
//            //echo "The file $path exists <br>";
//        } else {
//            echo "The file $path does not exist <br>";
//        }
//
//        $field = null;
//
//        // Parse pdf file and build necessary objects.
//        $parser = new Parser();
//        $pdf    = $parser->parseFile($path);
//
//        // Retrieve all pages from the pdf file.
//        $pages  = $pdf->getPages();
//
//        // Loop over each page to extract text.
//        $counter = 1;
//        foreach ($pages as $page) {
//            $pdfTextPage = $page->getText();
//
////            if(1) {
////                //$str, $starting_word, $ending_word
////                $startStr = "Applicant ID:";
////                $endStr = "AAMC ID:";
////                $applicationId = $this->string_between_two_string2($pdfTextPage, $startStr, $endStr);
////                //echo "applicationId=[".$applicationId ."]<br>";
////                if ($applicationId) {
////                    $applicationId = trim($applicationId);
////                    //$applicationId = str_replace(" ","",$applicationId);
////                    //$applicationId = str_replace("\t","",$applicationId);
////                    //$applicationId = str_replace("\t\n","",$applicationId);
////                    $applicationId = str_replace("'", '', $applicationId);
////                    $applicationId = preg_replace('/(\v|\s)+/', ' ', $applicationId);
////                    echo "applicationId=[".$applicationId."]<br>";
////                    //echo "Page $counter: <br>";
////                    //dump($pdfTextPage);
////                    echo "Page $counter=[".$pdfTextPage."]<br>";
////                    //exit("string found $startStr");
////                }
////            }
//            $startStr = "Applicant ID:";
//            $endStr = "AAMC ID:";
//            $field = $this->getPdfField($pdfTextPage,$startStr,$endStr);
//            if( $field ) {
//                echo "Smalot: $startStr=[" . $field . "]<br>";
//                break;
//            }
//            //exit();
//
//            //echo "Page $counter: <br>";
//            //dump($pdfTextPage);
//
//            //echo "Page $counter=[".$pdfTextPage."]<br>";
//
//            $counter++;
//        }
//
//        return $field;
//    }

//    //based on pdftotext. which pdftotext
//    public function parsePdfSpatie($path) {
//
//        if (file_exists($path)) {
//            //echo "The file $path exists <br>";
//        } else {
//            echo "The file $path does not exist <br>";
//        }
//
//        $userServiceUtil = $this->container->get('user_service_utility');
//
//        // /mingw64/bin/pdftotext C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\temp\eras.pdf -
//
//        //$pdftotextPath = '/mingw64/bin/pdftotext';
//        $pdftotextPath = '/bin/pdftotext';
//
//        if( $userServiceUtil->isWinOs() ) {
//            $pdftotextPath = '/mingw64/bin/pdftotext';
//        } else {
//            $pdftotextPath = '/bin/pdftotext';
//        }
//
//        $pdftotext = new Pdf($pdftotextPath);
//
//        //$path = "C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/temp/eras_gs.pdf";
//        //$path = '"'.$path.'"';
//        //$path = "'".$path."'";
//        $path = realpath($path);
//        echo "Spatie source pdf path=".$path."<br>";
//
//        $text = $pdftotext->setPdf($path)->text();
//
////        $startStr = "Applicant ID:";
////        $endStr = "AAMC ID:";
////        $field = $this->getPdfField($text,$startStr,$endStr);
////        if( $field ) {
////            echo "Spatie: $startStr=[" . $field . "]<br>";
////        }
//
//        $keysArr = $this->getKeyFields($text);
//
//        echo "keysArr=".count($keysArr)."<br>";
//        dump($keysArr);
//
//        return $keysArr;
//    }

//    public function getDataArray() {
//
//        $em = $this->getDoctrine()->getManager();
//
//        $dataArr = array();
//
//        $applicationDatas = array(1,2,3); //test
//        $nowDate = new \DateTime();
//
//        $counter = 0;
//        foreach($applicationDatas as $applicationData) {
//
//            $counter++;
//            $pdfTextArray = array();
//
//            $residencyTrack = $em->getRepository('AppUserdirectoryBundle:ResidencyTrackList')->find($counter);
//            $pdfTextArray["Residency Track"] = $residencyTrack->getName();
//
//            //Application Season Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//            $pdfTextArray["Application Season Start Date"] = $nowDate->format("m/d/Y H:i:s");
//
//            //Application Season End Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//            $pdfTextArray["Application Season End Date"] = $nowDate->format("m/d/Y H:i:s");
//
//            //Expected Residency Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//            $pdfTextArray["Expected Residency Start Date"] = $nowDate->format("m/d/Y H:i:s");
//
//            //Expected Graduation Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//            $pdfTextArray["Expected Graduation Date"] = $nowDate->format("m/d/Y H:i:s");
//
//            //First Name
//            $pdfTextArray["First Name"] = "First Name".$counter;
//
//            //Last Name
//            $pdfTextArray["Last Name"] = "Last Name".$counter;
//
//            //Middle Name
//            $pdfTextArray["Middle Name"] = "Middle Name".$counter;
//
//            //Preferred Email
//            $pdfTextArray["Preferred Email"] = "PreferredTestEmail".$counter."@yahoo.com";
//
//            $dataArr[] = $pdfTextArray;
//        }
//
//
//        return $dataArr;
//    }


    //NOT USED
//    public function getTableData($pdfTextsArray) {
//        $jsonData = array();
//
//        foreach($pdfTextsArray as $pdfTextArray) {
//            $rowArr = array();
//
//            $currentDate = new \DateTime();
//            $currentDateStr = $currentDate->format('m\d\Y H:i:s');
//
//            if(1) {
//                $rowArr["Application Receipt Date"] = $currentDateStr;
//
//                echo "Residency Track:".$pdfTextArray["Residency Track"]."<br>";
//                $rowArr["Residency Track"] = $pdfTextArray["Residency Track"];
//
//                //Application Season Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//                $rowArr["Application Season Start Date"] = $pdfTextArray["Application Season Start Date"];
//
//                //Application Season End Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//                $rowArr["Application Season End Date"] = $pdfTextArray["Application Season End Date"];
//
//                //Expected Residency Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//                $rowArr["Expected Residency Start Date"] = $pdfTextArray["Expected Residency Start Date"];
//
//                //Expected Graduation Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//                $rowArr["Expected Graduation Date"] = $pdfTextArray["Expected Graduation Date"];
//
//                //First Name
//                $rowArr["First Name"] = $pdfTextArray["First Name"];
//
//                //Last Name
//                $rowArr["Last Name"] = $pdfTextArray["Last Name"];
//
//                //Middle Name
//                $rowArr["Middle Name"] = $pdfTextArray["Middle Name"];
//
//                //Preferred Email
//                $rowArr["Preferred Email"] = $pdfTextArray["Preferred Email"];
//            } else {
//                $rowArr["Accession ID"] = "S11-1";
//
//                $rowArr["Part ID"] = "1";
//
//                //Application Season Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//                $rowArr["Block ID"] = "2";
//
//                //Application Season End Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//                $rowArr["Slide ID"] = "Slide ID";
//
//                //Expected Residency Start Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//                $rowArr["Stain Name"] = "Stain Name";
//
//                //Expected Graduation Date (populate with the same default as on https://view.med.cornell.edu/residency-applications/new/ )
//                $rowArr["Other ID"] = "Other ID";
//
//                //First Name
//                $rowArr["Sample Name"] = "Sample Name";
//
//            }
//
//            if(0) {
//                //Medical School Graduation Date
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Medical School Name
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Degree (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //USMLE Step 1 Score
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //USMLE Step 2 CK Score
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //USMLE Step 3 Score
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Country of Citizenship (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Visa Status (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Is the applicant a member of any of the following groups? (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Number of first author publications
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Number of all publications
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //AOA (show the same checkmark in the Handsontable cell as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Couple’s Match:
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Post-Sophomore Fellowship
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Previous Residency Start Date
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Previous Residency Graduation/Departure Date
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Previous Residency Institution
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Previous Residency City
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Previous Residency State (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Previous Residency Country (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Previous Residency Track (show the same choices in the Handsontable cell dropdown menu as what is shown on https://view.med.cornell.edu/residency-applications/new/ for this field)
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //ERAS Application ID
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //ERAS Application (show the cells in this column as blank - this is where you will show the original ERAS file name of the PDF once it uploads)
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//
//                //Duplicate? (locked field, leave empty by default)
//                $rowArr["xxx"] = $pdfTextArray["xxx"];
//            }
//
//            $jsonData[] = $rowArr;
//        }
//
//        return $jsonData;
//    }

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
