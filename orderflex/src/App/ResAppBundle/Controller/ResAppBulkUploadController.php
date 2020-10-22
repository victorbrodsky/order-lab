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
use App\UserdirectoryBundle\Entity\Citizenship;
use App\UserdirectoryBundle\Entity\EmploymentStatus;
use App\UserdirectoryBundle\Entity\Examination;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\Training;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Doctrine\Common\Collections\ArrayCollection;
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

            if( $form->getClickedButton() === $form->get('upload') ) {
                //exit("Extracting applications from CSV");

                $pdfFilePaths = array();
                $pdfFiles = array();
                $inputFileName = NULL;

                $em->getRepository('AppUserdirectoryBundle:Document')->processDocuments($inputDataFile, 'erasFile');
                $em->persist($inputDataFile);
                $em->flush();

                $files = $inputDataFile->getErasFiles();
                foreach ($files as $file) {
                    $ext = $file->getExtension();
                    if ($ext == 'csv') {
                        $inputFileName = $file->getFullServerPath();
                    } elseif ($ext == 'pdf') {
                        $pdfFilePaths[] = $file->getFullServerPath();
                        $pdfFiles[] = $file;
                    }
                }

                //echo "inputFileName=" . $inputFileName . "<br>";
                //echo "pdfFilePaths count=" . count($pdfFilePaths) . "<br>";
                //dump($pdfFilePaths);

                $handsomtableJsonData = $resappPdfUtil->getCsvApplicationsData($inputFileName, $pdfFiles);

                if (!is_array($handsomtableJsonData)) {

                    $this->get('session')->getFlashBag()->add(
                        'warning',
                        $handsomtableJsonData
                    );

                    $handsomtableJsonData = array();
                }

                //remove all documents
                foreach ($inputDataFile->getErasFiles() as $file) {
                    $inputDataFile->removeErasFile($file);
                    $em->remove($file);
                }
                $em->remove($inputDataFile);
                $em->flush();
            }
            elseif( $form->getClickedButton() === $form->get('addbtn') ) {
                //exit("Adding Application to be implemented");

                $user = $this->get('security.token_storage')->getToken()->getUser();

                $this->processTableData($inputDataFile,$form); //new
                //$datajson = $form->get('datalocker')->getData();
                //dump($datajson);


                exit("Adding Application to be implemented");
            }
            else {
                exit("Unknown button clicked");
            }

        }//form submit

        $withdata = false;
        if( count($handsomtableJsonData) > 0 ) {
            $withdata = true;
        }

        return array(
            'form' => $form->createView(),
            'cycle' => $cycle,
            'inputDataFile' => $inputDataFile,
            'handsometableData' => $handsomtableJsonData,
            'withdata' => $withdata
        );
    }

    //return created/updated array of DataResult objects existing in the Request
    public function processTableData( $inputDataFile, $form ) {
        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');
        $resappImportFromOldSystemUtil = $this->container->get('resapp_import_from_old_system_util');

        $logger = $this->container->get('logger');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //////////////// process handsontable rows ////////////////
        $datajson = $form->get('datalocker')->getData();

        $data = json_decode($datajson, true);

        $updatedDataResults = new ArrayCollection();

        if( $data == null ) {
            //exit('Table order data is null.');
            //throw new \Exception( 'Table order data is null.' );
            return $updatedDataResults;
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
        $testing = true;
        $count = 0;

        foreach( $data["row"] as $row ) {

            $count++;

            echo "<br><br>row=$count:<br>";
//            var_dump($row);
//            echo "<br>";
            //exit();

            $actionArr = $this->getValueByHeaderName('Action',$row,$headers);
            $actionValue = $actionArr['val'];
            //$actionId = $actionArr['id'];

            //echo "actionId=".$actionId." <br>";
            //echo "actionValue=".$actionValue." <br>";

            $aamcIdArr = $this->getValueByHeaderName('AAMC ID',$row,$headers);
            $aamcIdValue = $aamcIdArr['val'];
            //$aamcIdId = $aamcIdArr['id'];
            //echo "aamcIdValue=".$aamcIdValue." <br>";
            //exit('111');

            $issueArr = $this->getValueByHeaderName('Issue',$row,$headers);
            $issueValue = $issueArr['val']; //i.e. "Dupliacte in batch"
            //$issueId = $issueArr['id'];

            $erasFileArr = $this->getValueByHeaderName('ERAS Application',$row,$headers);
            $erasFileValue = $erasFileArr['val'];
            $erasFileId = $erasFileArr['id']; //ERAS document ID
            $erasDocument = NULL;
            if( $erasFileId ) {
                $erasDocument = $em->getRepository('AppUserdirectoryBundle:Document')->find($erasFileId);
                echo "Found eras by id=$erasFileId: ".$erasDocument." <br>";
            }
            if( !$erasDocument ) {
                if( $erasFileValue ) {
                    $erasDocument = $em->getRepository('AppUserdirectoryBundle:Document')->findOneByOriginalname($erasFileValue);
                    echo "Found eras name id=$erasFileValue: ".$erasDocument." <br>";
                }
            }

            $erasIdArr = $this->getValueByHeaderName('ERAS Application ID',$row,$headers);
            $erasIdValue = $erasIdArr['val'];
            //$erasIdId = $erasIdArr['id'];

            $residencyApplicationDb = NULL;
            if( $erasIdValue ) {
                $residencyApplicationDb = $em->getRepository('AppResAppBundle:ResidencyApplication')->findOneByErasApplicantId($erasIdValue);
            }

            if( $actionValue != "Add" || $residencyApplicationDb ) {
                echo "Do not add row=$count <br>";

                //Remove eras application PDF document file
                if( $erasDocument ) {
                    $inputDataFile->removeErasFile($erasDocument);
                    $em->remove($erasDocument);
                    if( !$testing ) {
                        $em->flush();
                    }
                }

                continue;
            }
            

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

            $firstNameArr = $this->getValueByHeaderName('First Name',$row,$headers);
            $firstNameValue = $firstNameArr['val'];
            //$firstNameId = $firstNameArr['id'];

            $lastNameArr = $this->getValueByHeaderName('Last Name',$row,$headers);
            $lastNameValue = $lastNameArr['val'];
            //$lastNameId = $lastNameArr['id'];

            $middleNameArr = $this->getValueByHeaderName('Middle Name',$row,$headers);
            $middleNameValue = $middleNameArr['val'];
            //$middleNameId = $middleNameArr['id'];

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

            $usmle3Arr = $this->getValueByHeaderName('USMLE Step 3 Score',$row,$headers);
            $usmle3Value = $usmle3Arr['val'];
            //$usmle3Id = $usmle3Arr['id'];

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
                $training->setTrainingType($trainingType);

                $residencyApplication->addTraining($training);
                $resappUser->addTraining($training);

                //$schoolDegree = $resappImportFromOldSystemUtil->getDegreeMapping($degreeValue);
                
                if( $degreeValue ) {
                    if( !$testing ) {
                        $this->setTrainingDegree($training, $degreeValue, $user);
                    }
                } else {
                    exit("Uknown degreeValue=[$degreeValue]");
                }

                //$medSchoolGradDateTime = date("m/d/Y", strtotime($medSchoolGradDateValue));
//                echo "1medSchoolGradDateValue=$medSchoolGradDateValue => "; // 8/2014 - 5/2019
//                $medSchoolGradDateFull = NULL;
//                $medSchoolGradDateValueArr = explode("-",$medSchoolGradDateValue);
//                if( count($medSchoolGradDateValueArr) == 2 ) {
//                    $medSchoolGradDateMY = $medSchoolGradDateValueArr[1]; //"5/2019"
//                    $medSchoolGradDateMY = trim($medSchoolGradDateMY);
//                    //$medSchoolGradDateFull = "01/".$medSchoolGradDateMY;
//                    $splitGradDate=explode('/',$medSchoolGradDateMY);
//                    if( count($splitGradDate) == 2 ) {
//                        $medSchoolGradDateFull = trim($splitGradDate[0]) . "/01/" . trim($splitGradDate[1]);
//                    }
//                }
//                if( $medSchoolGradDateFull ) {
//                    //$medSchoolGradDateFull = date("d/m/Y", strtotime($medSchoolGradDateFull));
//                    //echo " 1medSchoolGradDateFull=$medSchoolGradDateFull => ";
//                    $medSchoolGradDateTime = $this->getDatetimeFromStr($medSchoolGradDateFull);
//                    echo "2medSchoolGradDateValue: $medSchoolGradDateValue => " . $medSchoolGradDateTime->format('d-m-Y') . "<br>";
//                    $training->setCompletionDate($medSchoolGradDateTime);
//                }
//                if( strpos($medSchoolGradDateValue, '-') !== false ) {
//                    $medSchoolGradDateTime = $this->getDatetimeFromStr($medSchoolGradDateValue);
//                    echo "3medSchoolGradDateValue: $medSchoolGradDateValue => " . $medSchoolGradDateTime->format('d-m-Y') . "<br>";
//                    $training->setCompletionDate($medSchoolGradDateTime);
//                } else {
//                    exit("Invalid grad date " . $medSchoolGradDateValue);
//                }
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

            $receiptDateArr = $this->getValueByHeaderName('Application Receipt Date',$row,$headers);
            $receiptDateValue = $receiptDateArr['val'];
            //$receiptDateId = $receiptDateArr['id'];
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
            if( $usmle1Value ) {
                $examination->setUSMLEStep1Score($usmle1Value);
            }
            if( $usmle2Value ) {
                $examination->setUSMLEStep2CKScore($usmle2Value);
            }
            if( $usmle3Value ) {
                $examination->setUSMLEStep3Score($usmle3Value);
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
                $residencyApplication->setEthnicity($ethnicGroupValue);
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
                //|| $previousResidencyTrackValue
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
            //uploadedPhotoUrl

            exit("End of process handsontable. Count=$count");

        }//foreach row

        if( $testing ) {
            exit("End of process handsontable. Count=$count");
        }

        return $updatedDataResults;
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

    //Create resapp user
    function createNewResappUser( $userArr ) {
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
                    exit("Multiple users found count=".count($users)." by email ".$email);
                }
            }
        }

        if( $user ) {
            return $user;
        }
        //create excel user
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


    //NOT USED
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
