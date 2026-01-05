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
 * User: ch3
 * Date: 4/27/2016
 * Time: 11:35 AM
 */

namespace App\TranslationalResearchBundle\Util;



use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution


use App\TranslationalResearchBundle\Entity\SpecialtyList; //process.py script: replaced namespace by ::class: added use line for classname=SpecialtyList


use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User


use App\UserdirectoryBundle\Entity\SiteList; //process.py script: replaced namespace by ::class: added use line for classname=SiteList


use App\TranslationalResearchBundle\Entity\ProjectTypeList; //process.py script: replaced namespace by ::class: added use line for classname=ProjectTypeList
use App\TranslationalResearchBundle\Entity\Prices;
use App\TranslationalResearchBundle\Entity\PriceTypeList;
use App\TranslationalResearchBundle\Entity\RequestCategoryTypeList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\TranslationalResearchBundle\Entity\AntibodyList;
use App\TranslationalResearchBundle\Entity\CommitteeReview;
use App\TranslationalResearchBundle\Entity\Project;
use App\TranslationalResearchBundle\Entity\TransResRequest;
//use App\UserdirectoryBundle\Security\Authentication\AuthUtil;
//use App\UserdirectoryBundle\Util\UserSecurityUtil;
//use App\UserdirectoryBundle\Util\UserUtil;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
//use Symfony\Component\Validator\Constraints\DateTime;

//use FOS\RestBundle\View\View;

class TransResImportData
{

    private $em;
    private $container;

    private $usernamePrefix = 'ldap-user';
    private $headerMapArr = null;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {
        $this->container = $container;
        $this->em = $em;
    }

    //Import Work Requests
    public function importWorkRequests( $request, $filename, $startRaw=2, $endRaw=null ) {
        set_time_limit(18000); //18000 seconds => 5 hours 3600sec=>1 hour
        ini_set('memory_limit', '7168M');

        $transresUtil = $this->container->get('transres_util');
        $userSecUtil = $this->container->get('user_security_utility');
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $logger = $this->container->get('logger');
        $em = $this->em;

        $userMapper = $this->getUserMapper('TRF_EMAIL_INFO.xlsx');

        $inputFileName = __DIR__ . "/" . $filename;
        echo "==================== Processing $filename =====================<br>";
        $logger->notice("==================== Processing $filename =====================");

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        echo "highestRow=".$highestRow."; highestColum=".$highestColumn."<br>";

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);

        $this->headerMapArr = $this->getHeaderMap($headers);

        ////////////// add system user /////////////////
        //$systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        $institution = $userSecUtil->getAutoAssignInstitution();
        if( !$institution ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $institution = $em->getRepository(Institution::class)->findOneByName('Pathology and Laboratory Medicine');
        }

        //////// Admin user ///////////
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $specialty = $this->em->getRepository(SpecialtyList::class)->findOneByAbbreviation("ap-cp");
        if( !$specialty ) {
            exit("Project specialty not found by abbreviation=ap-cp");
        }
        $reviewers = $transresUtil->getDefaultReviewerInfo("admin_review",$specialty,true);
        $adminReviewer = $reviewers[0];
        //////// EOF Admin user /////////

        $count = 0;
        $commentRequestArr = array();

        $i = 0;
        $batchSize = 20;
        //$classical = false;
        $classical = true;

        $limitRow = $highestRow;
        if( $endRaw && $endRaw <= $highestRow ) {
            $limitRow = $endRaw;
        }

        if( $startRaw < 2 ) {
            $startRaw = 2; //minimum raw
        }

        echo "start Iteration from $startRaw to ".$limitRow."<br>";
        $logger->notice("start Iteration from $startRaw to ".$limitRow);

        //for each request in excel (start at row 2)
        for( $row = $startRaw; $row <= $limitRow; $row++ ) {

            $count++;

            //testing
            //if( $count == 2 ) {
                //faster?
            //    exit("count limit $count");
            //}

            $commentArr = array();

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);


            //SERVICE_ID
            //PROJECT_ID

            //SURGICAL_PATHOLOGY
            //IMMUNOPATHOLOGY
            //CYTOGENETICS
            //MOLECULAR_PATHOLOGY
            //MOLECULAR_HEMATOPATHOLOGY
            //MOLECULAR_DIAGNOSTICS

            //TOTAL_CASES
            //PARAFFIN_BLOCK
            //FRESH_FROZEN_TISSUE
            //FROZEN_STORAGE

            //TOTAL_BLOCKS
            //STAINED
            //STAINED_NUM_BLOCK
            //UNSTAINED
            //UNSTAINED_NUM_BLOCK
            //UNSTAINED_IHC
            //UNSTAINED_IHC_NUM_BLOCK
            //SPEC_STAINED
            //SPEC_STAINED_NUM_BLOCK
            //PARA_RNA_DNA
            //PARA_RNA_DNA_NUM_BLOCK
            //TMA_CORES	TMA_CORES_NUM_BLOCK

            //FLOW_CYTOMETRY
            //IMMUNOHISTOCHEMISTRY
            //FISH
            //TMA
            //LASER_CAPTURE

            //PERFORMED_BY

            //CYTOGENETICS_ANTIBODY
            //FISH_ANTIBODY
            //NUM_PROBES

            //INTERPRETATION

            //TECHNICAL_SUPPORT_FROM
            //TECHNICAL_SUPPORT_TO

            //SUBMITTED_BY
            //STATUS_ID
            //APPROVAL_DATE
            //REQUESTED_COMMENT
            //ADMIN_COMMENT
            //CREATED_DATE
            //ASPIRATE_SMEARS
            //CONTACT_NAME
            //CONTACT_EMAIL
            //exit("exit");

            $exportId = $this->getValueByHeaderName('PROJECT_ID', $rowData, $headers);
            $exportId = trim((string)$exportId);
            echo $exportId."<br>";


            $requestID = $this->getValueByHeaderName('SERVICE_ID', $rowData, $headers);
            $requestID = trim((string)$requestID);
            //$requestID = $requestID."0000000"; //test
            echo "<br>" . $count . ": Project ID " . $exportId . ", RS ID " . $requestID . "<br>";

//            //test
//            //CREATED_DATE
//            $CREATED_DATE_STR = $this->getValueByHeaderName('CREATED_DATE', $rowData, $headers);
//            if( $CREATED_DATE_STR ) {
//                //echo "CREATED_DATE_STR=".$CREATED_DATE_STR."<br>";
//                $CREATED_DATE = $this->transformDatestrToDate($CREATED_DATE_STR);
//            } else {
//                exit("Created date does not exists.");
//                $transresRequest->setCreateDate(null);
//            }
//            continue;

            //echo $count." [".$exportId."]: ";
            //exit("exit");

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
            $transresRequest = $em->getRepository(TransResRequest::class)->findOneByExportId($requestID);
            //echo "transresRequest=".$transresRequest->getExportId()."<br>";
            if (!$transresRequest) {
                $transresRequest = new TransResRequest();
                $transresRequest->setExportId($requestID);
                $transresRequest->setInstitution($institution);
                $transresRequest->setVersion(1);

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
                $project = $this->em->getRepository(Project::class)->findOneByExportId($exportId);
                if (!$project) {
                    exit("Project wit external ID '$exportId' does not exist.");
                }

                //$project->addRequest($transresRequest);
                $transresRequest->setProject($project);
            } else {
                echo "Request already exists with ID " . $requestID . "<br>";
                continue;
            }

            $formDataArr = array();

            //1) Lab
            $labs = array();
            $SURGICAL_PATHOLOGY = $this->getValueByHeaderName('SURGICAL_PATHOLOGY', $rowData, $headers);
            if ($SURGICAL_PATHOLOGY) {
                $labs[] = "Surgical Pathology";
            }
            $IMMUNOPATHOLOGY = $this->getValueByHeaderName('IMMUNOPATHOLOGY', $rowData, $headers);
            if ($IMMUNOPATHOLOGY) {
                $labs[] = "Immunopathology";
            }
            $CYTOGENETICS = $this->getValueByHeaderName('CYTOGENETICS', $rowData, $headers);
            if ($CYTOGENETICS) {
                $labs[] = "Cytogenetics";
            }
            $MOLECULAR_PATHOLOGY = $this->getValueByHeaderName('MOLECULAR_PATHOLOGY', $rowData, $headers);
            if ($MOLECULAR_PATHOLOGY) {
                $labs[] = "Molecular Pathology";
            }
            $MOLECULAR_HEMATOPATHOLOGY = $this->getValueByHeaderName('MOLECULAR_HEMATOPATHOLOGY', $rowData, $headers);
            if ($MOLECULAR_HEMATOPATHOLOGY) {
                $labs[] = "Molecular HematoPathology";
            }
            $MOLECULAR_DIAGNOSTICS = $this->getValueByHeaderName('MOLECULAR_DIAGNOSTICS', $rowData, $headers);
            if ($MOLECULAR_DIAGNOSTICS) {
                $labs[] = "Molecular Diagnostics";
            }
            if (count($labs) > 0) {
                $labsStr = "Lab: " . implode(", ", $labs);
                $formDataArr[] = $labsStr;
                //echo $labsStr."<br>";
            }

            //2) Tissue Procurement/Processing
            //TOTAL_CASES
            //PARAFFIN_BLOCK
            //FRESH_FROZEN_TISSUE
            //FROZEN_STORAGE
            $processings = array();
            $TOTAL_CASES = $this->getValueByHeaderName('TOTAL_CASES', $rowData, $headers);
            if ($TOTAL_CASES) {
                $processings[] = "Total # of patients/cases: " . $TOTAL_CASES;
            }
            $PARAFFIN_BLOCK = $this->getValueByHeaderName('PARAFFIN_BLOCK', $rowData, $headers);
            if ($PARAFFIN_BLOCK) {
                $processings[] = "Paraffin Blocks Processing";
            }
            $FRESH_FROZEN_TISSUE = $this->getValueByHeaderName('FRESH_FROZEN_TISSUE', $rowData, $headers);
            if ($FRESH_FROZEN_TISSUE) {
                $processings[] = "Fresh/Frozen Tissue Procurement";
            }
            $FROZEN_STORAGE = $this->getValueByHeaderName('FROZEN_STORAGE', $rowData, $headers);
            if ($FROZEN_STORAGE) {
                $processings[] = "Frozen Tissue Storage";
            }
            if (count($processings) > 0) {
                $processingsStr = "Tissue Procurement/Processing:<br>" . implode("<br>", $labs);
                $formDataArr[] = $processingsStr;
                //echo $processingsStr."<br>";
            }

            //3) Archival Specimens
            //TOTAL_BLOCKS
            //STAINED
            //STAINED_NUM_BLOCK
            //UNSTAINED
            //UNSTAINED_NUM_BLOCK
            //UNSTAINED_IHC	UNSTAINED_IHC_NUM_BLOCK
            //SPEC_STAINED
            //SPEC_STAINED_NUM_BLOCK
            //PARA_RNA_DNA
            //PARA_RNA_DNA_NUM_BLOCK
            //TMA_CORES	TMA_CORES_NUM_BLOCK
            $specimens = array();
            $TOTAL_BLOCKS = $this->getValueByHeaderName('TOTAL_BLOCKS', $rowData, $headers);
            if ($TOTAL_BLOCKS) {
                $specimens[] = "Total # of blocks: " . $TOTAL_BLOCKS;
            }
            $STAINED = $this->getValueByHeaderName('STAINED', $rowData, $headers);
            if ($STAINED) {
                $STAINED_NUM_BLOCK = $this->getValueByHeaderName('STAINED_NUM_BLOCK', $rowData, $headers);
                if ($STAINED_NUM_BLOCK) {
                    $specimens[] = "Slides - stained #" . $STAINED_NUM_BLOCK;
                }
            }
            $UNSTAINED = $this->getValueByHeaderName('UNSTAINED', $rowData, $headers);
            if ($UNSTAINED) {
                $UNSTAINED_NUM_BLOCK = $this->getValueByHeaderName('UNSTAINED_NUM_BLOCK', $rowData, $headers);
                if ($UNSTAINED_NUM_BLOCK) {
                    $specimens[] = "Slides - unstained #" . $UNSTAINED_NUM_BLOCK;
                }
            }
            $UNSTAINED_IHC = $this->getValueByHeaderName('UNSTAINED_IHC', $rowData, $headers);
            if ($UNSTAINED_IHC) {
                $UNSTAINED_IHC_NUM_BLOCK = $this->getValueByHeaderName('UNSTAINED_IHC_NUM_BLOCK', $rowData, $headers);
                if ($UNSTAINED_IHC_NUM_BLOCK) {
                    $specimens[] = "Slides - unstained for IHC #" . $UNSTAINED_IHC_NUM_BLOCK;
                }
            }
            $SPEC_STAINED = $this->getValueByHeaderName('SPEC_STAINED', $rowData, $headers);
            if ($SPEC_STAINED) {
                $SPEC_STAINED_NUM_BLOCK = $this->getValueByHeaderName('SPEC_STAINED_NUM_BLOCK', $rowData, $headers);
                if ($SPEC_STAINED_NUM_BLOCK) {
                    $specimens[] = "Special Stains #" . $SPEC_STAINED_NUM_BLOCK;
                }
            }
            $PARA_RNA_DNA = $this->getValueByHeaderName('PARA_RNA_DNA', $rowData, $headers);
            if ($PARA_RNA_DNA) {
                $PARA_RNA_DNA_NUM_BLOCK = $this->getValueByHeaderName('PARA_RNA_DNA_NUM_BLOCK', $rowData, $headers);
                if ($PARA_RNA_DNA_NUM_BLOCK) {
                    $specimens[] = "Paraffin Sections for RNA/DNA (TUBE) #" . $PARA_RNA_DNA_NUM_BLOCK;
                }
            }
            $TMA_CORES = $this->getValueByHeaderName('TMA_CORES', $rowData, $headers);
            if ($TMA_CORES) {
                $TMA_CORES_NUM_BLOCK = $this->getValueByHeaderName('TMA_CORES_NUM_BLOCK', $rowData, $headers);
                if ($TMA_CORES_NUM_BLOCK) {
                    $specimens[] = "TMA cores for RNA/DNA analysis (TUBE) #" . $TMA_CORES_NUM_BLOCK;
                }
            }
            if (count($specimens) > 0) {
                $processingsStr = "Archival Specimens:<br>" . implode("<br>", $specimens);
                $formDataArr[] = $processingsStr;
                //echo $processingsStr."<br>";
            }

            //4) processingTypes
            //FLOW_CYTOMETRY
            //IMMUNOHISTOCHEMISTRY
            //FISH
            //TMA
            //LASER_CAPTURE
            $processingTypes = array();
            $FLOW_CYTOMETRY = $this->getValueByHeaderName('FLOW_CYTOMETRY', $rowData, $headers);
            if ($FLOW_CYTOMETRY) {
                $processingTypes[] = "Flow Cytometry";
            }
            $IMMUNOHISTOCHEMISTRY = $this->getValueByHeaderName('IMMUNOHISTOCHEMISTRY', $rowData, $headers);
            if ($IMMUNOHISTOCHEMISTRY) {
                $processingTypes[] = "Immunohistochemistry";
            }
            $FISH = $this->getValueByHeaderName('FISH', $rowData, $headers);
            if ($FISH) {
                $processingTypes[] = "FISH";
            }
            $TMA = $this->getValueByHeaderName('TMA', $rowData, $headers);
            if ($TMA) {
                $processingTypes[] = "Tissue Microarray";
            }
            $LASER_CAPTURE = $this->getValueByHeaderName('LASER_CAPTURE', $rowData, $headers);
            if ($LASER_CAPTURE) {
                $processingTypes[] = "Laser Capture Microdissection";
            }
            if (count($processingTypes) > 0) {
                $processingTypesStr = implode("<br>", $processingTypes);
                $formDataArr[] = $processingTypesStr;
                //echo $processingsStr."<br>";
            }

            //4 PERFORMED_BY
            $PERFORMED_BY = $this->getValueByHeaderName('PERFORMED_BY', $rowData, $headers);
            if ($PERFORMED_BY) {
                if ($PERFORMED_BY == 1) {
                    $formDataArr[] = "In-House (Starr-7)";
                }
                if ($PERFORMED_BY == 2) {
                    $formDataArr[] = "Performed by Researcher";
                }
            }


            //5) Table

            //6) Footer
            //CYTOGENETICS_ANTIBODY
            //FISH_ANTIBODY
            //NUM_PROBES
            $footers = array();
            $CYTOGENETICS_ANTIBODY = $this->getValueByHeaderName('CYTOGENETICS_ANTIBODY', $rowData, $headers);
            if ($CYTOGENETICS_ANTIBODY) {
                $footers[] = "Conventional Cytogenetics";
            }
            $FISH_ANTIBODY = $this->getValueByHeaderName('FISH_ANTIBODY', $rowData, $headers);
            if ($FISH_ANTIBODY) {
                $footers[] = "FISH";
            }
            $NUM_PROBES = $this->getValueByHeaderName('NUM_PROBES', $rowData, $headers);
            if ($NUM_PROBES) {
                $footers[] = "# Probes: " . $NUM_PROBES;
            }
            if (count($footers) > 0) {
                $footersStr = implode("<br>", $footers);
                $formDataArr[] = $footersStr;
            }
            //INTERPRETATION
            $INTERPRETATION = $this->getValueByHeaderName('INTERPRETATION', $rowData, $headers);
            //echo "INTERPRETATION=[$INTERPRETATION]<br>";
            if ($INTERPRETATION != '' && $INTERPRETATION != NULL) {
                $INTERPRETATION = intval($INTERPRETATION);
                if ($INTERPRETATION === 1) {
                    $formDataArr[] = "Interpretation by Pathologist: Yes";
                }
                if ($INTERPRETATION === 0) {
                    $formDataArr[] = "Interpretation by Pathologist: No";
                }
            }

            //TECHNICAL_SUPPORT_FROM
            $TECHNICAL_SUPPORT_FROM = $this->getValueByHeaderName('TECHNICAL_SUPPORT_FROM', $rowData, $headers);
            if ($TECHNICAL_SUPPORT_FROM) {
                $TECHNICAL_SUPPORT_FROM_DATE = $this->transformDatestrToDate($TECHNICAL_SUPPORT_FROM, "m/Y");
                $transresRequest->setSupportStartDate($TECHNICAL_SUPPORT_FROM_DATE);
            }

            //TECHNICAL_SUPPORT_TO
            $TECHNICAL_SUPPORT_TO = $this->getValueByHeaderName('TECHNICAL_SUPPORT_TO', $rowData, $headers);
            if ($TECHNICAL_SUPPORT_TO) {
                $TECHNICAL_SUPPORT_TO_DATE = $this->transformDatestrToDate($TECHNICAL_SUPPORT_TO, "m/Y");
                $transresRequest->setSupportEndDate($TECHNICAL_SUPPORT_TO_DATE);
            }

            //SUBMITTED_BY
            $SUBMITTED_BY = $this->getValueByHeaderName('SUBMITTED_BY', $rowData, $headers);
            if ($SUBMITTED_BY) {
                $submitterUser = $userMapper[$SUBMITTED_BY];
                if( $submitterUser ) {
                    $transresRequest->setSubmitter($submitterUser);
                    $transresRequest->setContact($submitterUser);
                } else {
                    echo "User not found by SUBMITTED_BY=".$SUBMITTED_BY."<br>";
                }
            }

            //STATUS_ID
            $STATUS_ID = $this->getValueByHeaderName('STATUS_ID', $rowData, $headers);
            if( $STATUS_ID ) {
                $requestStateArr = $this->statusRequestMapper($STATUS_ID);
                $statusProgress = $requestStateArr['progress'];
                $statusBilling = $requestStateArr['billing'];
                if ($statusProgress && $statusBilling) {
                    $transresRequest->setProgressState($statusProgress);
                    $transresRequest->setBillingState($statusBilling);
                } else {
                    exit("Request progress state is not defined by STATUS_ID=[" . $STATUS_ID . "]");
                }
            } else {
                $statusProgress = "completed";
                $statusBilling = "paid";
                $commentArr[] = "Request status is pre-set by default values: progress to $statusProgress and billing status to $statusBilling";
                $transresRequest->setProgressState($statusProgress);
                $transresRequest->setBillingState($statusBilling);
            }

            //APPROVAL_DATE
            $APPROVAL_DATE_STR = $this->getValueByHeaderName('APPROVAL_DATE', $rowData, $headers);
            if ($APPROVAL_DATE_STR) {
                $APPROVAL_DATE = $this->transformDatestrToDate($APPROVAL_DATE_STR);
                $transresRequest->setProgressApprovalDate($APPROVAL_DATE);
            }

            //REQUESTED_COMMENT
            $REQUESTED_COMMENT = $this->getValueByHeaderName('REQUESTED_COMMENT', $rowData, $headers);
            if ($REQUESTED_COMMENT) {
                $transresRequest->setComment($REQUESTED_COMMENT);
            }

//            //ADMIN_COMMENT
//            $ADMIN_COMMENT = $this->getValueByHeaderName('ADMIN_COMMENT', $rowData, $headers);
//            if( $ADMIN_COMMENT ) {
//                //add it later when ID is generated.
//                //$this->addComment($request, $adminReviewer, $transresRequest, $ADMIN_COMMENT, "progress", "[imported comment]");
//            }

            //CREATED_DATE
            $CREATED_DATE_STR = $this->getValueByHeaderName('CREATED_DATE', $rowData, $headers);
            if( $CREATED_DATE_STR ) {
                //echo "CREATED_DATE_STR=".$CREATED_DATE_STR."<br>";
                $CREATED_DATE = $this->transformDatestrToDate($CREATED_DATE_STR);
                $transresRequest->setCreateDate($CREATED_DATE);
            } else {
                exit("Created date does not exists.");
                $transresRequest->setCreateDate(null);
            }
            //exit('test');

            //CONTACT_EMAIL
            $CONTACT_EMAIL = $this->getValueByHeaderName('CONTACT_EMAIL', $rowData, $headers);
            if( $CONTACT_EMAIL ) {
                $contactUsers = $this->getUserByEmail($CONTACT_EMAIL, $requestID, 'CONTACT_EMAIL');
                if (count($contactUsers) > 0) {
                    $contactUser = $contactUsers[0];
                    $transresRequest->setContact($contactUser);
                }
            }

            //ASPIRATE_SMEARS
            $ASPIRATE_SMEARS = $this->getValueByHeaderName('ASPIRATE_SMEARS', $rowData, $headers);
            if( $ASPIRATE_SMEARS ) {
                $formDataArr[] = "Aspirate Smears: ".$ASPIRATE_SMEARS;
            }
            //CONTACT_NAME
            $CONTACT_NAME = $this->getValueByHeaderName('CONTACT_NAME', $rowData, $headers);
            if( $CONTACT_NAME ) {
                $formDataArr[] = "Contact Name: ".$CONTACT_NAME;
            }
            //CONTACT_EMAIL
            if( $CONTACT_EMAIL ) {
                $formDataArr[] = "Contact Email: ".$CONTACT_EMAIL;
            }


            /////////////// Set default users from project ///////////////////
            //$projectFundedAccountNumber = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"If funded, please provide account number");
            $projectFundedAccountNumber = $project->getFundedAccountNumber();
            if( $projectFundedAccountNumber ) {
                $transresRequest->setFundedAccountNumber($projectFundedAccountNumber);
            }

            //pre-populate Request's Billing Contact by Project's Billing Contact
            if( $project->getBillingContact() ) {
                $transresRequest->setContact($project->getBillingContact());
            }

            //pre-populate Request's Support End Date by Project's IRB Expiration Date
            if( $project->getIrbExpirationDate() ) {
                $transresRequest->setSupportEndDate($project->getIrbExpirationDate());
            }

            //pre-populate PIs
            $transreqPis = $project->getPrincipalInvestigators();
            foreach( $transreqPis as $transreqPi ) {
                $transresRequest->addPrincipalInvestigator($transreqPi);
            }

            $submitterUser = $transresRequest->getSubmitter();
            if( !$submitterUser ) {
                $contactUser = $transresRequest->getContact();
                if( $contactUser ) {
                    $transresRequest->setSubmitter($submitterUser);
                } else {
                    exit("No Submitter or Contact user defined for ".$requestID);
                }
            }
            /////////////// EOF Set default users from project ///////////////////

            //Final check submitter and contact
            $defaultUser = null;
            $transreqPis = $project->getPrincipalInvestigators();
            if( count($transreqPis) > 0 ) {
                $defaultUser = $transreqPis[0];
            } else {
                exit("No PI is defined for ".$requestID);
            }
            //Contact User
            $currentContactUser = $transresRequest->getContact();
            if( !$currentContactUser ) {
                $transresRequest->setContact($defaultUser);
                $commentArr[] = "Default Contact user is pre-set using PI $defaultUser";
            }
            $currentSubmitterUser = $transresRequest->getSubmitter();
            if( !$currentSubmitterUser ) {
                $transresRequest->setSubmitter($defaultUser);
                $commentArr[] = "Default Submitter user is pre-set using PI $defaultUser";
            }

            $formDataStr = null;
            if( count($formDataArr) > 0 ) {
                $formDataStr = implode("\r\n",$formDataArr);
                //echo $formDataStr."\r\n";
                $formDataStr = str_replace("<br>","\r\n",$formDataStr);
                $formDataStr =
                    "### Original Exported Service Request Form ###\r\n".
                    $formDataStr."\r\n".
                    "########################################";
            }

            $commentStr = null;
            if( count($commentArr) > 0 ) {
                $commentStr = implode("\r\n",$commentArr);
                //echo $commentStr."\r\n";
                if( $formDataStr ) {
                    $commentStr = "\r\n \r\n" . "Note:" . "\r\n" . $commentStr;
                } else {
                    $commentStr = "Note:" . "\r\n" . $commentStr;
                }

            }

            if( $formDataStr || $commentStr ) {
                $requestComment = $transresRequest->getComment();
                //Append to the Comment
                $requestComment = $requestComment . "\r\n \r\n" . $formDataStr . $commentStr;
                $transresRequest->setComment($requestComment);
            }

            //save project to DB before form nodes
            $saveFlag = true;
            //$saveFlag = false;
            if( $saveFlag ) {
                if( $classical ) {
                    $em->persist($transresRequest);
                    $em->flush();

                    $transresRequest->generateOid();
                    $em->flush($transresRequest);

                    //ADMIN_COMMENT. Save it when the Request's ID is generated.
                    $ADMIN_COMMENT = $this->getValueByHeaderName('ADMIN_COMMENT', $rowData, $headers);
                    if( $ADMIN_COMMENT ) {
                        $this->addComment($request, $adminReviewer, $transresRequest, $ADMIN_COMMENT, "progress", "[imported comment]",$CREATED_DATE_STR);
                    }
                    $logger->notice("Imported request with ID=".$transresRequest->getOid());
                } else {
                    $em->persist($transresRequest); //it looks like we don't have any other new objects created, which require persist

                    //$inst = $transresRequest->getInstitution();
                    //$em->persist($inst);

                    $i++;
                    if( ($i % $batchSize) === 0 ) {
                        echo "****************** Request batch flush ************<br>";
                        $em->flush();
                        $em->clear(); // Detaches all objects from Doctrine!
                    }

                    //ADMIN_COMMENT. Save it when the Request's ID is generated.
                    $ADMIN_COMMENT = $this->getValueByHeaderName('ADMIN_COMMENT', $rowData, $headers);
                    if( $ADMIN_COMMENT ) {
                        //echo "added to commentRequestArr: [$CREATED_DATE_STR] [$ADMIN_COMMENT]<br>";
                        $commentElement = array('request'=>$transresRequest,'comment'=>$ADMIN_COMMENT,'date'=>$CREATED_DATE_STR);
                        $commentRequestArr[] = $commentElement;
//                        echo "<pre>";
//                        print_r($commentRequestArr);
//                        echo "</pre>";
                    }
                }

            }

            //exit('111');
        }//forloop

        //Persist objects that did not make up an entire batch
        if( !$classical ) {
            echo "<br>****************** Request flush remaining ************<br>";
            $em->flush();
            $em->clear();
        }

        //TODO: try to make batch flush and then addComment using $commentRequestArr($transresRequest=>array($ADMIN_COMMENT,$CREATED_DATE_STR))

//        echo "<pre>";
//        print_r($commentRequestArr);
//        echo "</pre>";

        if( !$classical ) {
            //1) generate Oid
            echo "<br>Process OID <br>";
            $i = 0;
            $batchSize = 20;
            foreach ($commentRequestArr as $commentDateArr) {
                $transresRequest = $commentDateArr['request'];
                $transresRequest->generateOid();
                echo "generated OID=" . $transresRequest->getExportId() . "<br>";

                $em->persist($transresRequest);

                $i++;
                if (($i % $batchSize) === 0) {
                    echo "****************** generated OID batch flush ************<br>";
                    $em->flush();
                    $em->clear(); // Detaches all objects from Doctrine!
                }
            }
            echo "****************** generated OID flush remaining ************<br>";
            $em->flush();
            $em->clear();

            //2) add comments
            echo "Process Comments <br>";
            foreach ($commentRequestArr as $commentDateArr) {
                $transresRequest = $commentDateArr['request'];
                echo "Comment for Request ExportID=" . $transresRequest->getExportId() . "<br>";
                $commentStr = $commentDateArr['comment'];
                echo "comment=$commentStr <br>";
                $dateStr = $commentDateArr['date'];
                echo "date=" . $dateStr . "<br>";
                $this->addComment($request, $adminReviewer, $transresRequest, $commentStr, "progress", "[imported comment]", $dateStr);
            }
        }//classical

        // Detaches all objects from Doctrine!
        $em->clear();
        //gc_collect_cycles();

        return "Added $count Work Requests";
    }

    public function editWorkRequests($request, $filename, $startRaw=2, $endRaw=null) {
        set_time_limit(18000); //18000 seconds => 5 hours 3600sec=>1 hour
        ini_set('memory_limit', '7168M');

        $transresUtil = $this->container->get('transres_util');
        //$userSecUtil = $this->container->get('user_security_utility');
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $logger = $this->container->get('logger');
        $em = $this->em;

        $userMapper = $this->getUserMapper('TRF_EMAIL_INFO.xlsx');

        $inputFileName = __DIR__ . "/" . $filename;
        echo "==================== Processing $filename =====================<br>";
        $logger->notice("==================== Processing $filename =====================");

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        echo "highestRow=".$highestRow."; highestColum=".$highestColumn."<br>";

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);

        $this->headerMapArr = $this->getHeaderMap($headers);

        ////////////// add system user /////////////////
        //$systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        //$institution = $userSecUtil->getAutoAssignInstitution();
        //if( !$institution ) {
        //    $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName('Pathology and Laboratory Medicine');
        //}

        //////// Admin user ///////////
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $specialty = $this->em->getRepository(SpecialtyList::class)->findOneByAbbreviation("ap-cp");
        if( !$specialty ) {
            exit("Project specialty not found by abbreviation=ap-cp");
        }
        $reviewers = $transresUtil->getDefaultReviewerInfo("admin_review",$specialty,true);
        $adminReviewer = $reviewers[0];
        //////// EOF Admin user /////////

        $count = 0;
        //$commentRequestArr = array();

        //$i = 0;
        //$batchSize = 20;
        //$classical = false;
        //$classical = true;

        $limitRow = $highestRow;
        if( $endRaw && $endRaw <= $highestRow ) {
            $limitRow = $endRaw;
        }

        if( $startRaw < 2 ) {
            $startRaw = 2; //minimum raw
        }

        echo "start Iteration from $startRaw to ".$limitRow."<br>";
        $logger->notice("start Iteration from $startRaw to ".$limitRow);

        //for each request in excel (start at row 2)
        for( $row = $startRaw; $row <= $limitRow; $row++ ) {

            $count++;

            //testing
            //if( $count == 2 ) {
            //faster?
            //    exit("count limit $count");
            //}

            //$commentArr = array();

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $requestID = $this->getValueByHeaderName('SERVICE_ID', $rowData, $headers);
            $requestID = trim((string)$requestID);
            //$requestID = $requestID."0000000"; //test
            echo "<br>" . $count . ": RS ID " . $requestID . "<br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
            $transresRequest = $em->getRepository(TransResRequest::class)->findOneByExportId($requestID);
            if( !$transresRequest ) {
                exit("Request not found by External ID ".$requestID);
            }


            //CREATED_DATE
            $CREATED_DATE_STR = $this->getValueByHeaderName('CREATED_DATE', $rowData, $headers);

            //ADMIN_COMMENT. Save it when the Request's ID is generated.
            $ADMIN_COMMENT = $this->getValueByHeaderName('ADMIN_COMMENT', $rowData, $headers);
            if( $ADMIN_COMMENT ) {
                //$request, $author, $entity, $commentStr, $commentType, $prefix="[imported comment]", $createDateStr=null, $parentComment=null, $newThread=true
                $res = $this->addComment($request,$adminReviewer,$transresRequest,$ADMIN_COMMENT,"progress","[imported comment]",$CREATED_DATE_STR,null,false);
                echo "res=$res<br>";
            }


        }//for
    }


    public function getUserMapper($filename) {

        $logger = $this->container->get('logger');

        $mapper = array();

        $inputFileName = __DIR__ . "/" . $filename; //'/TRF_PROJECT_INFO.xlsx';
        echo "==================== Processing $filename =====================<br>";

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);


        //for each request in excel (start at row 2)
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $userId = $this->getValueByHeaderName('USER_ID', $rowData, $headers);
            $email = $this->getValueByHeaderName('EMAIL', $rowData, $headers);

            $user = $this->getUserBySingleEmail($email);

            $mapper[$userId] = $user;
        }

        return $mapper;
    }
    public function getUserBySingleEmail($email) {
        $logger = $this->container->get('logger');
        $email = trim((string)$email);
        $email = strtolower($email);
        $emailParts = explode("@", $email);

        if( count($emailParts) == 0 || count($emailParts) == 1 ) {
            return null;
        }

        $emailParts1 = $emailParts[1];
        if( $emailParts1 == "med.cornell.edu" || $emailParts1 == "nyp.org" ) {
            //ok
        } else {
            $msg = "email [".$email."] is not CWID user";
            //echo $msg."<br>";
            $logger->warning("getUserBySingleEmail: ".$msg);
        }

        $cwid = $emailParts[0];
        //$username = $cwid."_@_". $this->usernamePrefix;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $user = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId($cwid);

        return $user;
    }

    //TRF_COMMITTEE_REV
    public function importCommitteeComments( $request, $filename ) {
        $transresUtil = $this->container->get('transres_util');
        $logger = $this->container->get('logger');

        $userMapper = $this->getUserMapper('TRF_EMAIL_INFO.xlsx');

        $inputFileName = __DIR__ . "/" . $filename;
        echo "==================== Processing $filename =====================<br>";

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);


        $count = 0;

        //for each request in excel (start at row 2)
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);


            $exportId = $this->getValueByHeaderName('PROJECT_ID', $rowData, $headers);
            $exportId = trim((string)$exportId);
            echo $exportId.", ";

            $comment = $this->getValueByHeaderName('COMMITTEE_COMMENT', $rowData, $headers);

            //DATE_SEND
            $dateSendStr = $this->getValueByHeaderName('DATE_SEND', $rowData, $headers);

            $userId = $this->getValueByHeaderName('USER_ID', $rowData, $headers);

            if( $comment ) {

                $prefix = "[imported comment]";

                $reviewerUser = $userMapper[$userId];
                if( !$reviewerUser ) {
                    //////// get default committee reviewer ///////////
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
                    $specialty = $this->em->getRepository(SpecialtyList::class)->findOneByAbbreviation("ap-cp");
                    if( !$specialty ) {
                        exit("Project specialty not found by abbreviation=ap-cp");
                    }
                    $reviewers = $transresUtil->getDefaultReviewerInfo("committee_review",$specialty,true);
                    $committeeReviewer = $reviewers[0];
                    //////// EOF get default committee reviewer /////////
                    $reviewerUser = $committeeReviewer;
                    $prefix = "[imported comment submitted by $userId]";
                }

                if( $reviewerUser ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
                    $project = $this->em->getRepository(Project::class)->findOneByExportId($exportId);
                    if( !$project ) {
                        exit("Project wit external ID '$exportId' does not exist.");
                    }

                    //add this user as non-primary committee reviewer
                    if( false === $transresUtil->isReviewsReviewer($reviewerUser, $project->getCommitteeReviews()) ) {
                        $reviewEntity = new CommitteeReview($reviewerUser);

                        $reviewEntity->setReviewer($reviewerUser);

                        //add as non-primary primaryReview boolean
                        $reviewEntity->setPrimaryReview(false);

                        $project->addCommitteeReview($reviewEntity);
                    }
                }

                $this->addComment($request,$reviewerUser,$project,$comment,"committee_review",$prefix,$dateSendStr);
            }

            //exit('111');
            $count++;
        }

        return "Added $count Committee comments";
    }

    //TRF_COMMENTS_RESP
    public function importCommitteeComments2( $request, $filename ) {

        $transresUtil = $this->container->get('transres_util');
        $logger = $this->container->get('logger');

        $userMapper = $this->getUserMapper('TRF_EMAIL_INFO.xlsx');

        $inputFileName = __DIR__ . "/" . $filename; //'/TRF_PROJECT_INFO.xlsx';
        echo "==================== Processing $filename =====================<br>";

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);

        //////// Admin user ///////////
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $specialty = $this->em->getRepository(SpecialtyList::class)->findOneByAbbreviation("ap-cp");
        if( !$specialty ) {
            exit("Project specialty not found by abbreviation=ap-cp");
        }
        $reviewers = $transresUtil->getDefaultReviewerInfo("admin_review",$specialty,true);
        $adminReviewer = $reviewers[0];
        //////// EOF Admin user /////////

        $count = 0;

        //for each request in excel (start at row 2)
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);


            //REVIEWER_REQUEST_ID	USER_NAME	PROJECT_ID	REVIEWER	PI_RESPONSE	CREATED_DATE

            $exportId = $this->getValueByHeaderName('PROJECT_ID', $rowData, $headers);
            $exportId = trim((string)$exportId);

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
            $project = $this->em->getRepository(Project::class)->findOneByExportId($exportId);
            if( !$project ) {
                exit("Project wit external ID '$exportId' does not exist.");
            }
            echo "Project External ID=".$exportId."<br>";

            //TODO: add reviewer and PI comments as the committee comments. Set created date.
            //exit('TODO: add reviewer and PI comments as the committee comments');
            $reviewerComment = $this->getValueByHeaderName('REVIEWER', $rowData, $headers);
            $piComment = $this->getValueByHeaderName('PI_RESPONSE', $rowData, $headers);

            //CREATED_DATE
            $createDateStr = $this->getValueByHeaderName('CREATED_DATE', $rowData, $headers);

            $userId = $this->getValueByHeaderName('USER_NAME', $rowData, $headers);

            if( $reviewerComment ) {

                $reviewerUser = $userMapper[$userId];
                if( $reviewerUser ) {
                    //add this user as non-primary committee reviewer
                    if( false === $transresUtil->isReviewsReviewer($reviewerUser, $project->getCommitteeReviews()) ) {
                        $reviewEntity = new CommitteeReview($reviewerUser);

                        $reviewEntity->setReviewer($reviewerUser);

                        //add as non-primary primaryReview boolean
                        $reviewEntity->setPrimaryReview(false);

                        $project->addCommitteeReview($reviewEntity);
                    }
                }

                $prefix = "[imported reviewer comment]";

                if( !$reviewerUser ) {
                    $reviewerUser = $adminReviewer;
                    $prefix = "[imported reviewer comment submitted by $userId]";
                }
                if(!$reviewerUser) {
                    exit("No reviewer is found for project export ID ".$exportId);
                }

                $reviewerCommentEntity = $this->addComment($request,$reviewerUser,$project,$reviewerComment,"committee_review",$prefix,$createDateStr);
                $count++;
            }

            if( $piComment ) {
                $commentUser = null;
                $pis = $project->getPrincipalInvestigators();
                if( count($pis) > 0 ) {
                    $commentUser = $pis[0];
                } else {
                    $commentUser = $project->getSubmitter();
                }
                if( !$commentUser ) {
                    exit("PI or Submitter not found in the project with export ID ".$exportId);
                }
                $this->addComment($request,$commentUser,$project,$piComment,"committee_review","[imported PI comment]",$createDateStr,$reviewerCommentEntity);
            }

        }

        return "Added $count Committee comments";
    }





    // url: /import-old-data/
    //import projects from: TRF_DRAFT_PROJECT and RF_PROJECT_INFO
    public function importOldData( $request, $filename, $importFlag ) {

        ini_set('max_execution_time', 3600); //3600 seconds = 60 minutes

        $logger = $this->container->get('logger');
        $em = $this->em;
        $userSecUtil = $this->container->get('user_security_utility');
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');

        $testing = false;
        //$testing = true;

        //$email = "oli2002@med.cornell.edu";
        //$requests = array();

        //$default_time_zone = $this->container->getParameter('default_time_zone');
        //$userSecUtil = $this->container->get('user_security_utility');
        //$userkeytype = $userSecUtil->getUsernameType($this->usernamePrefix);

        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $specialty = $this->em->getRepository(SpecialtyList::class)->findOneByAbbreviation("ap-cp");
        if( !$specialty ) {
            exit("Project specialty not found by abbreviation=ap-cp");
        }

        //Admin user
        $reviewers = $transresUtil->getDefaultReviewerInfo("admin_review",$specialty,true);
        $adminReviewer = $reviewers[0];

        $notExistingStatuses = array();
        $notExistingUsers = array();
        $count = 0;
        $batchSize = 20;

        $inputFileName = __DIR__ . "/" . $filename; //'/TRF_PROJECT_INFO.xlsx';
        echo "==================== Processing $filename =====================<br>";

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);


        //for each request in excel (start at row 2)
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //Insert row data array into the database
            //echo $row.": ";
            //var_dump($rowData);
            //echo "<br>";

            $exportId = $this->getValueByHeaderName('PROJECT_ID', $rowData, $headers);
            $exportId = trim((string)$exportId);
            echo "<br>########## exportId=".$exportId."#############<br>";
            //exit('$project OID='.$exportId);

            //if( $exportId != 1840 ) {continue;} //testing

//            $project = $this->em->getRepository('AppTranslationalResearchBundle:Project')->findOneByExportId($exportId);
//            if( $project ) {
//                if( !$testing ) {
//                    continue; //testing
//                }
//            }

            //Process Project
            if( $importFlag == 'project' || $importFlag == 'project_adminComments' ) {
                $res = $this->importProject($request, $adminReviewer, $rowData, $headers, $exportId, $specialty, $systemUser, $notExistingStatuses, $notExistingUsers, $testing);
                $notExistingStatuses = $res['notExistingStatuses'];
                $notExistingUsers = $res['notExistingUsers'];
                $project = $res['project'];
            }

            if( $importFlag == 'project_edit' ) {
                $project = $this->editProject($request, $adminReviewer, $rowData, $headers, $exportId, $specialty, $systemUser, $notExistingStatuses, $notExistingUsers, $count, $testing);
                //save project to DB
                if( !$testing ) {
                    if(0) {
                        $em->persist($project);
                        $em->flush($project);
                    } else {
                        if( ($count % $batchSize) === 0 ) {
                            echo "************* flush projects ************** <br>";
                            $em->flush();
                            $em->clear(); // Detaches all objects from Doctrine!
                        }
                    }
                }
            }

            if( $importFlag == 'adminComments' || $importFlag == 'project_adminComments' ) {
                //print_r($headers);echo "<br>";
                //$exportId = 13443;
                //$rowData = 3;
                $commentRes = $this->importAdminComments($request,$adminReviewer,$rowData,$headers,$exportId);
                echo 'end of admin Comments: '.$commentRes."<br>";
            }

//            if( $importFlag == 'committeeComments' ) {
//                $commentRes = $this->import Committee Comments($request,$rowData,$headers,$exportId,$userMapper);
//                echo 'end of committee Comments: '.$commentRes."<br>";
//            }

            $count++;

            //if( $count > 6 ) {
            //    exit('count test');
            //}

            //echo "<br>";
            //exit('$project OID='.$project->getOid());
        }//for each request

        if( $importFlag == 'project_edit' ) {
            $em->flush(); //Persist objects that did not make up an entire batch
            $em->clear();
        }

        $notExistingStatuses = array_unique($notExistingStatuses);
        foreach($notExistingStatuses as $notExistingStatus) {
            echo "$notExistingStatus <br>";
        }

        $errorCount=1;
        foreach($notExistingUsers as $notExistingUser) {
            echo $errorCount.": ".$notExistingUser."<br>";
            $errorCount++;
        }

        $result = "Imported requests = " . $count;
        //exit($result);

        return $result;
    }

    public function importProject( $request, $adminReviewer, $rowData, $headers, $exportId, $specialty, $systemUser, $notExistingStatuses, $notExistingUsers, $testing=false ) {
        $transresUtil = $this->container->get('transres_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');
        $em = $this->em;

        $thisNotExistingUsers = array(); //only for required users

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $project = $this->em->getRepository(Project::class)->findOneByExportId($exportId);
        if( $project ) {
            if( !$testing ) {
                //ignore existing request to prevent overwrite
                $res = array(
                    'notExistingStatuses' => $notExistingStatuses,
                    'notExistingUsers' => $notExistingUsers,
                    'project' => $project
                );
                return $res;
            }
        } else {
            //new Project
            $project = new Project();
        }

        $project->setVersion(1);
        $project->setImportDate(new \DateTime());

        if( !$project->getInstitution() ) {
            $institution = $userSecUtil->getAutoAssignInstitution();
            if( !$institution ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                $institution = $em->getRepository(Institution::class)->findOneByName('Pathology and Laboratory Medicine');
            }
            $project->setInstitution($institution);
        }

        //set order category
//            if( !$project->getMessageCategory() ) {
//                $categoryStr = "HemePath Translational Research Project";  //"Pathology Call Log Entry";
//                //$categoryStr = "Nesting Test"; //testing
//                $messageCategory = $em->getRepository('AppOrderformBundle:MessageCategory')->findOneByName($categoryStr);
//
//                if (!$messageCategory) {
//                    throw new \Exception("Message category is not found by name '" . $categoryStr . "'");
//                }
//                $project->setMessageCategory($messageCategory);
//            }

        $project->setExportId($exportId);

        $project->setProjectSpecialty($specialty);

        //CREATED_DATE TODO: test this date
        $CREATED_DATE_STR = $this->getValueByHeaderName('CREATED_DATE', $rowData, $headers); //24-OCT-12
        if( $CREATED_DATE_STR ) {
            $CREATED_DATE = $this->transformDatestrToDate($CREATED_DATE_STR);
            $project->setCreateDate($CREATED_DATE);
        }

        //IRB_EXPIRATION_DATE
        $irbExpDate = null;
        $irbExpDateStr = $this->getValueByHeaderName('IRB_EXPIRATION_DATE', $rowData, $headers);
        //echo "irbExpDateStr=".$irbExpDateStr."<br>";
        if( $irbExpDateStr ) {
            $irbExpDate = $this->transformDatestrToDate($irbExpDateStr);
            if( $irbExpDate ) {
                $project->setIrbExpirationDate($irbExpDate);
                //$this->setValueToFormNodeNewProject($project, "IRB Expiration Date", $irbExpDate);
                //echo "irbExpDate=" . $irbExpDate->format('d-m-Y') . "<br>";
            }
        }

        //STATUS_ID
        $statusID = $this->getValueByHeaderName('STATUS_ID', $rowData, $headers);
        $statusStr = $this->statusMapper($statusID);
        if( $statusStr ) {
            $project->setState($statusStr);
        } else {
            echo "Status not define=".$statusID.":".$this->statusMapper($statusID,true) . "<br>";
            $notExistingStatuses[] = $this->statusMapper($statusID,true);
        }

        $requestersArr = array();
        $requestersStrArr = array();

        //SUBMITTED_BY
        $submitterCwid = $this->getValueByHeaderName('SUBMITTED_BY', $rowData, $headers);
        $requestersStrArr[] = "SUBMITTED_BY: ".$submitterCwid;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $submitterUser = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId($submitterCwid);
        if( $submitterUser ) {
            $project->setSubmitter($submitterUser);
        } else {
            $msg = "Submitter not found by SUBMITTED_BY=".$submitterUser;
            //exit($msg);
            //echo $msg."<br>";
            $logger->warning($msg);
        }

        //Contact
        $contactEmails = $this->getValueByHeaderName('EMAIL', $rowData, $headers);
        $requestersStrArr[] = "EMAIL: ".$contactEmails;
        $contactEmails = strtolower($contactEmails);
        $contactUsers = $this->getUserByEmail($contactEmails,$exportId,'EMAIL');
        if( count($contactUsers) > 0 ) {
            if( !$project->getSubmitter() ) {
                $submitter = $contactUsers[0];
                //echo "1 submitter=".$submitter."<br>";
                $project->setSubmitter($submitter);
            }
            foreach($contactUsers as $contactUser) {
                $project->addContact($contactUser);
                $requestersArr[] = $contactUser;
            }
        } else {
            $msg = "Contact user not found by EMAIL=".$contactEmails;
            //exit($msg);
            //echo $msg."<br>";
            $logger->warning($msg);
            //$notExistingUsers[] = $exportId." [###Critical###]: ".$msg;
            $thisNotExistingUsers[] = $msg;
        }

        //PI
        $piEmail = $this->getValueByHeaderName('PI_EMAIL', $rowData, $headers);
        $requestersStrArr[] = "PI_EMAIL: ".$piEmail;
        $piUsers = $this->getUserByEmail($piEmail,$exportId,'PI_EMAIL');
        if( count($piUsers) > 0 ) {
            foreach($piUsers as $user) {
                $project->addPrincipalInvestigator($user);
                $requestersArr[] = $user;
            }
        } else {
            $msg = "PI user not found by PI_EMAIL=" . $piEmail;
            //echo $msg . "<br>";
            $thisNotExistingUsers[] = $msg;
            $logger->warning($msg);

            //try to get by PRI_INVESTIGATOR
            $priInvestigatorsOriginal = $this->getValueByHeaderName('PRI_INVESTIGATOR', $rowData, $headers);
            $priInvestigators = $this->cleanString($priInvestigatorsOriginal);
            $requestersStrArr[] = "PRI_INVESTIGATOR: ".$priInvestigators;
            $priInvestigators = $this->cleanUsername($priInvestigators);
            $priInvestigatorsArr = explode(",",$priInvestigators);
            $piFound = false;
            foreach($priInvestigatorsArr as $pi) {
                //assume "amy chadburn": second if family name
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                $thisUser = $this->em->getRepository(User::class)->findOneByAnyNameStr($pi);
                if( $thisUser ) {
                    $project->addPrincipalInvestigator($thisUser);
                    $requestersArr[] = $thisUser;
                    $piFound = true;
                } else {
                    $msg = "PI user not found by PRI_INVESTIGATOR=".$pi;
                    //$notExistingUsers[] = $exportId.": ".$msg;
                    $logger->warning($msg);
                    //exit($msg);
                }
            }
            if( !$piFound ) {
                $thisNotExistingUsers[] = "PI user not found by PRI_INVESTIGATOR=".$priInvestigatorsOriginal;
            }
        }

        //Pathologists Involved
        $pathEmail = $this->getValueByHeaderName('PATH_EMAIL', $rowData, $headers);
        $requestersStrArr[] = "PATH_EMAIL: ".$pathEmail;
        $pathUsers = $this->getUserByEmail($pathEmail,$exportId,'PATH_EMAIL');
        if( count($pathUsers) > 0 ) {
            foreach($pathUsers as $user) {
                $project->addPathologist($user);
                $requestersArr[] = $user;
            }
        } else {
            $msg = "Pathology user not found by PATH_EMAIL=".$pathEmail;
            //exit($msg);
            //echo $msg."<br>";
            if( $pathEmail ) {
                $logger->warning($msg);
                $thisNotExistingUsers[] = $msg;
            }
        }

        //CO_INVESTIGATOR
        $coInvestigatorsOriginal = $this->getValueByHeaderName('CO_INVESTIGATOR', $rowData, $headers);
        $coInvestigators = $this->cleanString($coInvestigatorsOriginal);
        $requestersStrArr[] = "CO_INVESTIGATOR: ".$coInvestigators;
        $coInvestigators = $this->cleanUsername($coInvestigators);
        $coInvestigatorsArr = explode(",",$coInvestigators);
        $coinvFound = false;
        foreach($coInvestigatorsArr as $coInvestigator) {
            //echo "coInvestigator=".$coInvestigator."<br>";
            //assume "amy chadburn": second if family name
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $thisUser = $this->em->getRepository(User::class)->findOneByAnyNameStr($coInvestigator);
            if( $thisUser ) {
                $project->addCoInvestigator($thisUser);
                $requestersArr[] = $thisUser;
                $coinvFound = true;
            } else {
                $msg = "Co-Investigator user not found by CO_INVESTIGATOR=".$coInvestigatorsOriginal;
                if( $coInvestigator ) {
                    $logger->warning($msg);
                    //$notExistingUsers[] = $exportId . ": " . $msg;
                }
                //exit($msg);
            }
            //}

        }
        if( !$coinvFound && $coInvestigatorsOriginal ) {
            $thisNotExistingUsers[] = "Co-Investigator user not found by CO_INVESTIGATOR=".$coInvestigatorsOriginal;
        }

        $criticalErrorArr = array();
        if( !$project->getSubmitter() ) {
            if( count($requestersArr) > 0 ) {
                $submitter = $requestersArr[0];
                //echo "2 submitter=".$submitter."<br>";
                $project->setSubmitter($submitter);
                $thisNotExistingUsers[] = "Submitter user is pre-set by the first requester $submitter";
                //echo "Submitter is populated by first requester:";
                //print_r($requestersArr);
                //foreach($requestersArr as $requester) {
                //    echo $requester."<br>";
                //}
                //echo "<br>";
            } else {
                $criticalErrorArr[] = "Submitter";
            }
        }
        //add system user if not set
        if( !$project->getSubmitter() ) {
            //echo "3 submitter=".$submitter."<br>";
            $thisNotExistingUsers[] = "Submitter user is not found during the import.";
            $project->setSubmitter($systemUser);
        }

        $pis = $project->getPrincipalInvestigators();
        if( count($pis) == 0 ) {
            if( count($requestersArr) > 0 ) {
                $pi = $requestersArr[0];
                $project->addPrincipalInvestigator($pi);
                $thisNotExistingUsers[] = "PI user is pre-set by the first requester $pi";
                //echo "PI is populated by first requester:";
                //print_r($requestersArr);
                //foreach($requestersArr as $requester) {
                //    echo $requester."<br>";
                //}
                //echo "<br>";
            } else {
                $criticalErrorArr[] = "PI";
            }
        }
        //add system user if not set
        $pis = $project->getPrincipalInvestigators();
        if( count($pis) == 0 ) {
            $thisNotExistingUsers[] = "PI user is not found during the import.";
            $project->addPrincipalInvestigator($systemUser);
        }

        //Contacts
        $projectContacts = $project->getContacts();
        if( count($projectContacts) == 0 ) {
            if( count($requestersArr) > 0 ) {
                $projectContact = $requestersArr[0];
                $project->addContact($projectContact);
                $thisNotExistingUsers[] = "Contact user is pre-set by the first requester $projectContact";
            } else {
                $criticalErrorArr[] = "Contact";
            }
        }
        //add system user if not set
        $projectContacts = $project->getContacts();
        if( count($projectContacts) == 0 ) {
            $thisNotExistingUsers[] = "Contact user is not found during the import.";
            $project->addContact($systemUser);
        }

        if( count($criticalErrorArr) > 0 ) {
            $notexpired = false;
            if( $irbExpDate && $irbExpDate > new \DateTime("now") ) {
                //$notexpired = "***not expired/closed***";
                $notexpired = true;
            }
            $notclosed = false;
            if( $statusStr != "closed" ) {
                $notclosed = true;
            }

            if( $notexpired && $notclosed ) {
                $criticalErrorStr = $exportId . " (Status:" . $statusStr . "; Created:" . $CREATED_DATE_STR . "; IRB EXP:" . $irbExpDateStr . ")";
                $errorMsg = $criticalErrorStr ." ". implode(",", $criticalErrorArr) . " Undefined" . ". Requesters: " . implode("; ", $requestersStrArr);
                $notExistingUsers[] = $errorMsg;
                $thisNotExistingUsers[] = $errorMsg;
            }
        }

        //Billing Contact
        if( $project->getSubmitter() ) {
            $project->setBillingContact($project->getSubmitter());
        }

        //DATE_APPROVAL
        $DATE_APPROVAL_STR = $this->getValueByHeaderName('DATE_APPROVAL', $rowData, $headers);
        //echo "DATE_APPROVAL_STR=".$DATE_APPROVAL_STR."<br>";
        if( $DATE_APPROVAL_STR ) {
            $DATE_APPROVAL = $this->transformDatestrToDate($DATE_APPROVAL_STR);
            $project->setApprovalDate($DATE_APPROVAL);
        }

        //PROJECT_TYPE_ID
        $PROJECT_TYPE_ID = $this->getValueByHeaderName('PROJECT_TYPE_ID', $rowData, $headers);
        $projectType = $this->projectTypeMapper($PROJECT_TYPE_ID);
        if( $projectType ) {
            $project->setProjectType($projectType);
        }

        //PROJECT_TITLE
        $title = $this->getValueByHeaderName('PROJECT_TITLE', $rowData, $headers);
        $project->setTitle($title);
        if( $title ) {
            //$this->setValueToFormNodeNewProject($project, "Title", $title);
            $project->setTitle($title);
        }
        //echo "title=".$title."<br>";

        //IRB_NUMBER
        $irbNumber = $this->getValueByHeaderName('IRB_NUMBER', $rowData, $headers);
        if( $irbNumber ) {
            //$this->setValueToFormNodeNewProject($project, "IRB Number", $irbNumber);
            $project->setIrbNumber($irbNumber);
            //echo "irbNumber=" . $irbNumber . "<br>";
        }

        //PROJECT_FUNDED
        $funded = $this->getValueByHeaderName('PROJECT_FUNDED', $rowData, $headers);
        if( isset($funded) ) {
            //$this->setValueToFormNodeNewProject($project, "Funded", $funded);
            $project->setFunded($funded);
        }

        //ACCOUNT_NUMBER
        $fundedAccountNumber = $this->getValueByHeaderName('ACCOUNT_NUMBER', $rowData, $headers);
        if( isset($fundedAccountNumber) ) {
            //$this->setValueToFormNodeNewProject($project, "If funded, please provide account number", $fundedAccountNumber);
            $project->setFundedAccountNumber($fundedAccountNumber);
        }

        //DESCRIPTION
        $DESCRIPTION = $this->getValueByHeaderName('DESCRIPTION', $rowData, $headers);
        if( $DESCRIPTION ) {
            //$this->setValueToFormNodeNewProject($project, "Brief Description", $DESCRIPTION);
            $project->setDescription($DESCRIPTION);
        }

        //BUDGET_OUTLINE
        $budgetSummary = $this->getValueByHeaderName('BUDGET_OUTLINE', $rowData, $headers);
        if( $budgetSummary ) {
            //$this->setValueToFormNodeNewProject($project, "Provide a Detailed Budget Outline/Summary", $budgetSummary);
            $project->setBudgetSummary($budgetSummary);
        }

        //ESTIMATED_COSTS
        $estimatedCost = $this->getValueByHeaderName('ESTIMATED_COSTS', $rowData, $headers);
        if( isset($estimatedCost) ) {
            //$this->setValueToFormNodeNewProject($project, "Estimated Total Costs ($)", $estimatedCost);
            $project->setTotalCost($estimatedCost);
        }
        /////////////////////


        //ADMIN_COMMENT
        //$ADMIN_COMMENT = $this->getValueByHeaderName('ADMIN_COMMENT', $rowData, $headers);
        //TODO:???

        //BIO_STAT_COMMENT ???

        //PI_SUBMITTED_IRB ???

        //REQ_BIO_STAT ???

        //BIO_STAT_HAS_REVIEW ???

        //PREVIOUS_STATUS_ID ???

        //REVISED ???

        //HAS_FUNDING_APPROVAL ???

        //FUNDING_APPROVAL_DATE ???

        //FUNDING_APPROVAL_COMMENT ???

        //Add all default reviewers except Committee
        $transresUtil->addDefaultStateReviewers($project,"irb_review");
        $transresUtil->addDefaultStateReviewers($project,"admin_review");
        $transresUtil->addDefaultStateReviewers($project,"final_review");

        //save project to DB before form nodes
        if( !$testing ) {
            $em->persist($project);
            $em->flush();

            $project->generateOid();
            $em->flush($project);
        }

        //echo "thisNotExistingUsers=".count($thisNotExistingUsers)."; notExistingStatuses=".count($notExistingStatuses)."<br>";
        //exit('111');
        if (count($thisNotExistingUsers) > 0) {
            //record this to admin comment;
            //$request, $adminReviewer, $project, $adminComment, "(imported comment)"
            $thisNotExistingUsersStr = implode("; ", $thisNotExistingUsers);
            echo "thisNotExistingUsersStr=".$thisNotExistingUsersStr."<br>";
            if( !$testing ) {
                $this->addComment($request, $adminReviewer, $project, $thisNotExistingUsersStr, "admin_review", "[import warning - not existing users]");
            }
        }

        if (count($notExistingStatuses) > 0) {
            //record this to admin comment;
            $notExistingStatusesStr = implode("; ", $notExistingStatuses);
            echo "notExistingStatusesStr=".$notExistingStatusesStr."<br>";
            if (!$testing) {
                $this->addComment($request, $adminReviewer, $project, $notExistingStatusesStr, "admin_review", "[import warning - not existing statuses]");
            }
        }

        $res = array(
            'notExistingStatuses' => $notExistingStatuses,
            'notExistingUsers' => $notExistingUsers,
            'project' => $project
        );

        return $res;
    }


    public function editProject( $request, $adminReviewer, $rowData, $headers, $exportId, $specialty, $systemUser, $notExistingStatuses, $notExistingUsers, $count, $testing=false ) {
        //$transresUtil = $this->container->get('transres_util');
        //$logger = $this->container->get('logger');
        //$em = $this->em;

        //$testing = true;

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $project = $this->em->getRepository(Project::class)->findOneByExportId($exportId);
        if( $project ) {
            //ok
        } else {
            //new Project
            exit("Project with external ID $exportId does not exists.");
        }


        //CREATED_DATE TODO: test this date
        $CREATED_DATE_STR = $this->getValueByHeaderName('CREATED_DATE', $rowData, $headers); //24-OCT-12
        echo "CREATED_DATE_STR=".$CREATED_DATE_STR."<br>";
        if( $CREATED_DATE_STR ) {
            $CREATED_DATE = $this->transformDatestrToDate($CREATED_DATE_STR);
            //echo "setCreateDate=".$CREATED_DATE->format('m-d-Y')."<br>";
            $project->setCreateDate($CREATED_DATE);
            echo "after set CreateDate=".$project->getCreateDate()->format('m-d-Y')."<br>";
            //exit('1');
        } else {
            echo "setCreateDate null <br>";
            $project->setCreateDate(NULL);
        }

        //IRB_EXPIRATION_DATE
        $irbExpDate = null;
        $irbExpDateStr = $this->getValueByHeaderName('IRB_EXPIRATION_DATE', $rowData, $headers);
        echo "irbExpDateStr=".$irbExpDateStr."<br>";
        if( $irbExpDateStr ) {
            $irbExpDate = $this->transformDatestrToDate($irbExpDateStr);
            if( $irbExpDate ) {
                $project->setIrbExpirationDate($irbExpDate);
                //$this->setValueToFormNodeNewProject($project, "IRB Expiration Date", $irbExpDate);
                //echo "irbExpDate=" . $irbExpDate->format('d-m-Y') . "<br>";
            }
        } else {
            echo "setIrbExpirationDate null <br>";
            $project->setIrbExpirationDate(NULL);
        }

        //DATE_APPROVAL
        $DATE_APPROVAL_STR = $this->getValueByHeaderName('DATE_APPROVAL', $rowData, $headers);
        echo "DATE_APPROVAL_STR=".$DATE_APPROVAL_STR."<br>";
        if( $DATE_APPROVAL_STR ) {
            $DATE_APPROVAL = $this->transformDatestrToDate($DATE_APPROVAL_STR);
            $project->setApprovalDate($DATE_APPROVAL);
        } else {
            echo "setApprovalDate null <br>";
            $project->setApprovalDate(NULL);
        }

        //STATUS_ID
        $statusID = $this->getValueByHeaderName('STATUS_ID', $rowData, $headers);
        $statusStr = $this->statusMapper($statusID);
        echo "Status ==".$statusStr."<br>";
        if( $statusStr ) {
            $project->setState($statusStr);
        } else {
            echo "Status not define=".$statusID.":".$this->statusMapper($statusID,true) . "<br>";
            $project->setState($statusStr);
        }
        //exit('111');
        /////////////////////

        //$em->persist($project);
        //$em->flush($project);
        //echo "get CreateDate=".$project->getCreateDate()->format('m-d-Y')."<br>";
        //exit('eof project '.$exportId);

//        //save project to DB
//        if( !$testing ) {
//            if(0) {
//                $em->persist($project);
//                $em->flush();
//            } else {
//                $batchSize = 20;
//                $em->persist($project);
//                if (($count % $batchSize) === 0) {
//                    echo "************* flush projects ************** <br>";
//                    $em->flush($project);
//                    $em->clear(); // Detaches all objects from Doctrine!
//                }
//            }
//        }

        return $project;
    }//editProject

    public function importAdminComments($request, $adminReviewer, $rowData, $headers, $exportId) {
        $adminComment = $this->getValueByHeaderName('ADMIN_COMMENT', $rowData, $headers);
        if( $adminComment ) {

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
            $project = $this->em->getRepository(Project::class)->findOneByExportId($exportId);
            if( !$project ) {
                exit("Project wit external ID '$exportId' does not exist.");
            }
            //echo "Project ID=".$project->getId()."<br>";

            $commentEntity = $this->addComment($request, $adminReviewer, $project, $adminComment, "admin_review", "[imported comment]");
            return $commentEntity;
        }

        return null;
    }


    public function addComment( $request, $author, $entity, $commentStr, $commentType, $prefix="[imported comment]", $createDateStr=null, $parentComment=null, $newThread=true) {
        $userServiceUtil = $this->container->get('user_service_utility');
        $commentManager = $this->container->get('fos_comment.manager.comment');
        $threadManager = $this->container->get('fos_comment.manager.thread');

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();
        //echo "className=".$className."<br>";

        if( !$entity ) {
            exit("Exit: $className is null for comment=".$commentStr);
        }
        if( !$author ) {
            exit("Exit: Author is null. $className export ID# ".$entity->getExportId());
        }
        if( !$commentStr ) {
            exit("Exit: Comment is null. $className export ID# ".$entity->getExportId());
        }
        if( !$commentType ) {
            exit("Exit: Comment Type is null. $className export ID# ".$entity->getExportId());
        }

        $res = null;

        //http://localhost/order/translational-research/import-old-data/
        $uri = $request->getUri();
        $uri = str_replace("/import-old-data/","",$uri); //http://localhost/order/translational-research
        //echo "uri=".$uri."<br>";
        //exit('111');

        //echo "adminComment=".$adminComment."<br>";
        if( $commentStr ) {

            $threadId = null;
            $permalink = null;

            if( $className == "Project" ) {
                //transres-Project-3-irb_review, transres-Project-18-admin_review
                $threadId = "transres-Project-" . $entity->getId() . "-" . $commentType;   //"admin_review";
                //http://localhost/order/translational-research/project/review/25
                //http://localhost/order/translational-research/project/review/18
                $permalink = $uri . "/project/review/" . $entity->getId();
            }
            if( $className == "TransResRequest" ) {
                //transres-Request-33-progress
                //transres-Request-33-billing
                $threadId = "transres-Request-". $entity->getId() . "-" . $commentType;   //"progress";
                //http://localhost/order/translational-research/request/progress/review/33
                $permalink = $uri . "/request/progress/review/" . $entity->getId();
            }
            if( !$threadId || !$permalink ) {
                exit("Exit: ThreadId or Permalink not defined.");
            }

            //echo "threadId=".$threadId."<br>";
            //echo "permalink=".$permalink."<br>";

            $thread = $threadManager->findThreadById($threadId);

            if ($thread) {
                //don't re-created the thread for this project
                //don't re-created the same comment for this project
                //echo "Thread already exists<br>";
                //return null;

                $comment = $userServiceUtil->findOneCommentByThreadBodyAuthor($thread,$commentStr,$author);
                if( $comment ) {
                    //echo $comment->getId().": Comment already exists <br>";
                    return $comment;
                }

                //echo "1 thread exists $threadId<br>";
                if( !$newThread ) {
                    //don't create new thread and comment
                    //echo "2 thread exists $threadId<br>";
                    return $threadId;
                }
            }
            //return "will be created: ".$commentStr; //test


            if( null === $thread ) {
                $thread = $threadManager->createThread();
                $thread->setId($threadId);

                //$permalink = $uri . "/project/review/" . $entity->getId();
                $thread->setPermalink($permalink);

                // Add the thread
                $threadManager->saveThread($thread);
            }

            //set Author
            $comment = $commentManager->createComment($thread,$parentComment);
            $comment->setAuthor($author);

            if( $createDateStr ) {
                $createDate = $this->transformDatestrToDate($createDateStr);
                $comment->setCreatedAt($createDate);
            }

            //set Depth
            //$comment->setDepth(0);

            //set Prefix
            $comment->setPrefix($prefix);

            //set comment body
            //$commentStr = $prefix . "" . $commentStr;
            $comment->setBody($commentStr);

            $comment->setAuthorType("Administrator");
            $comment->setAuthorTypeDescription("Administrator");

            //$commentManager->saveComment($comment);
            //if( 0 )
            if ($commentManager->saveComment($comment) !== false) {
                //exit("Comment saved successful!!!");
                //return $this->getViewHandler()->handle($this->onCreateCommentSuccess($form, $threadId, null));
                //View::createRouteRedirect('fos_comment_get_thread_comment', array('id' => $id, 'commentId' => $form->getData()->getId()), 201);
            }

            //$res = "Comment created '$commentStr' with threadID=" . $thread->getId() . "; commentID" . $comment->getId() . "<br>";
        }

        return $comment;
    }

    public function cleanUsername( $username ) {
        $username = str_replace(", MD","",$username);
        $username = str_replace(", M.D.","",$username);
        $username = str_replace(",M.D.","",$username);
        $username = str_replace(", PhD","",$username);
        $username = str_replace(", PH.D","",$username);
        $username = str_replace(", Ph.D","",$username);
        $username = str_replace("Dr.","",$username);
        $username = str_replace(" MD;","",$username);

        return $username;
    }

    public function cleanString( $string ) {
        $string = str_replace(" MD ","",$string);
        $string = str_replace(" PhD ","",$string);
        return $string;
    }

    public function getUserByEmail($emailStr,$exportId,$emailType) {
        $logger = $this->container->get('logger');

        $emailStr = strtolower($emailStr);
        $emailStr = str_replace(";",",",$emailStr);
        //if( strpos((string)$emailStr,",") !== false ) {
            $emails = explode(",",$emailStr);
        //} else {
        //    $emails = array($emailStr);
        //}

        $users = array();
        foreach($emails as $email) {
            $email = trim((string)$email);
            $emailParts = explode("@", $email);

            if( count($emailParts) == 0 || count($emailParts) == 1 ) {
                continue;
            }

            $emailParts1 = $emailParts[1];
            if( $emailParts1 == "med.cornell.edu" || $emailParts1 == "nyp.org" ) {
                //ok
            } else {
                $msg = "email [".$emailStr."] is not CWID user";
                //echo $msg."<br>";
                $logger->warning("getUserByEmail: ".$msg);
            }

            $cwid = $emailParts[0];
            //$username = $cwid."_@_". $this->usernamePrefix;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $user = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId($cwid);
            if( $user ) {
                $users[] = $user;
            }

            if( !$user ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                $user = $this->em->getRepository(User::class)->findOneByEmail($email);
                if( $user ) {
                    $users[] = $user;
                }
            }

            if( !$user ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                $userArr = $this->em->getRepository(User::class)->findUserByUserInfoEmail($email);
                if( count($userArr) == 1 ) {
                    $users[] = $userArr[0];
                }
                if( count($userArr) > 1 ) {
                    exit("multiple users found by email ".$email);
                }
            }

            //try to find and create by LDAP
            $user = $this->createNewUserByLdap($cwid);
            if( $user ) {
                $users[] = $user;
            }

            if( !$user ) {
                $msg = "Project Export ID=".$exportId.": No user found by email [".$email."]; type=".$emailType;
                //echo $msg."<br>";
                //exit($msg);
                $logger->warning($msg);
            }

        }

        return $users;
    }

    public function createNewUserByLdap($cwid) {

        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');

        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment == 'dev' ) {
            return NULL;
        }

        if( !$cwid ) {
            return NULL;
        }

        //first search this user if exists in ldap directory
        //$authUtil = new AuthUtil($this->container,$this->em);
        $authUtil = $this->container->get('authenticator_utility');
        $searchRes = $authUtil->searchLdap($cwid);
        if( $searchRes == NULL || count($searchRes) == 0 ) {
            $logger->error("LdapAuthentication: can not find user by usernameClean=".$cwid);
            return NULL;
        }

        //check if the user already exists in DB $cwid
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $user = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId($cwid);
        if( $user ) {
            return $user;
        }

        $username = $cwid . "_@_" . "ldap-user";

        $usernameClean = $userSecUtil->createCleanUsername($username);
        $usernamePrefix = $userSecUtil->getUsernamePrefix($username);

        //////////////////// constract a new user ////////////////////

        $logger->notice("LdapAuthentication: create a new user found by username=".$username);
        $user = $userSecUtil->constractNewUser($username);
        //echo "user=".$user->getUsername()."<br>";

        $user->setCreatedby('ldap-transerimport');

        //modify user: set keytype and primary public user id
        $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);

        if( !$userkeytype ) {
            exit("keytype does not exists ".$usernamePrefix);
        }

        $user->setKeytype($userkeytype);
        $user->setPrimaryPublicUserId($usernameClean);

        $user->setLocked(false);

        if( $searchRes ) {
            $user->setEmail($searchRes['mail']);
            $user->setFirstName($searchRes['givenName']);
            $user->setLastName($searchRes['lastName']);
            $user->setDisplayName($searchRes['displayName']);
            $user->setPreferredPhone($searchRes['telephoneNumber']);
        }

        //assign minimum roles
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SiteList'] by [SiteList::class]
        $siteObject = $this->em->getRepository(SiteList::class)->findOneByAbbreviation("translationalresearch");
        $lowestRoles = $siteObject->getLowestRoles();
        foreach($lowestRoles as $role) {
            $user->addRole($role);
        }

        //exit('ldap ok');

        //////////////////// save user to DB ////////////////////
        //$userManager = $this->container->get('fos_user.user_manager');
        $userManager = $this->container->get('user_manager');
        $userManager->updateUser($user);

        return $user;
    }

    public function getNewestDate($date1,$date2) {
        if( $date1 && !$date2 ) {
            return $date1;
        }

        if( !$date1 && $date2 ) {
            return $date2;
        }

        if( $date1 && $date2 ) {
            //if( strtotime($date1) > strtotime($date2) ) {
            if( $date1 > $date2 ) {
                return $date1;
            } else {
                return $date2;
            }
        }

        return null;
    }

    public function convertDateTimeToStr($datetime) {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        $dateStr = $transformer->transform($datetime);
        return $dateStr;
    }

    public function getHeaderMap($headers) {
        $headerMapArr = array();
        //print_r($headers[0]);
        $col = 0; //$rowData starts with 0
        foreach($headers[0] as $header) {
            //headerStr => column
            $headerMapArr[$header] = $col;
            $col++;
        }
        //echo "<pre>";
        //print_r($headerMapArr);
        //echo "</pre>";
        //exit('111');
        return $headerMapArr;
    }
    public function getFastValueByHeaderName($headerStr, $rowData, $headers, $headerMapArr) {
        //echo "<pre>";
        //print_r($rowData[0]);
        //echo "</pre>";
        //use excel get by row and column
        $column = $headerMapArr[$headerStr];
        $value = $rowData[0][$column];
        //$cellValue = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($column,$row)->getValue();
        //exit($headerStr.'='.$value);

        $value = str_replace("_x000D_","\r\n",$value);
        $value = str_replace("x000D","\r\n",$value);

        return $value;
    }
    public function getValueByHeaderName($header, $row, $headers) {
        if( $this->headerMapArr ) {
            //faster?
            return $this->getFastValueByHeaderName($header, $row, $headers, $this->headerMapArr);
        }

        $res = null;

        if( !$header ) {
            return $res;
        }

        //echo "header=".$header."; row=".$row[0]."<br>";
        //print_r($headers[0]);echo "<br>";
        //print_r($headers);
        //print_r($row[0]);

        //echo "cwid=(".$headers[0][39].")<br>";

        $key = array_search($header, $headers[0]);
        //echo "<br>key=".$key."<br>";

        if( $key === false ) {
            //echo "key is false !!!!!!!!!!<br>";
            return $res;
        }

        if( array_key_exists($key, $row[0]) ) {
            $res = $row[0][$key];
        }

        $res = str_replace("_x000D_","\r\n",$res);
        $res = str_replace("x000D","\r\n",$res);

        //echo "res=".$res."<br>";
        if( $res == "NULL" ) {
            //exit('exit null');
            $res = null;
        }

        if( $res ) {
            $res = trim((string)$res);
        }

        //echo "res=".$res."<br>";
        return $res;
    }

    public function setValueToFormNodeNewProject( $project, $fieldName, $value ) {
        return null;

        $transresRequestUtil = $this->container->get('transres_request_util');
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $formNodeUtil = $this->container->get('user_formnode_utility');
        $receivingObject = $transresRequestUtil->setValueToFormNodeProject($project,$fieldName,$value);
        if( !$receivingObject ) {
            //$thisFormNode = $this->em->getRepository("AppUserdirectoryBundle:FormNode")->find($formNodeId);
            $thisFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents($fieldName);

            //echo "create formnode=".$thisFormNode."<br>";
            //$testing = true;
            $testing = false;
            $formNodeUtil->processFormNodeByType($thisFormNode,$value,$project,$testing);
        }
        //re-try
        $receivingObject = $transresRequestUtil->setValueToFormNodeProject($project,$fieldName,$value);
    }

    public function transformDatestrToDate($datestr, $formatType="j-M-y")
    {
        //$userSecUtil = $this->container->get('user_security_utility');
        //$date = $userSecUtil->transformDatestrToDateWithSiteEventLog($datestr, $this->container->getParameter('translationalresearch.sitename'));
//        if( $date ) {
//            $date->setTimezone(new \DateTimeZone("UTC"));
//            //echo "ok<br>";
//        } else {
//            //exit("date object is null for datestr=".$datestr);
//        }

        //'j-M-Y', '15-Feb-2009'
        //23-APR-07
        echo "dateStr=".$datestr;
        //M/y
        $date = \DateTime::createFromFormat($formatType,$datestr);
        echo " =>".$date->format("d-m-Y")."<br>";

        return $date;
    }

    public function getApproverByUserId($userId) {
        $cwid = $this->userMapper($userId);
        $username = $cwid."_@_". $this->usernamePrefix;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $approver = $this->em->getRepository(User::class)->findOneByUsername($username);
        if( !$approver ) {
            $logger = $this->container->get('logger');
            $logger->error("Can not find user by username=".$username);
            //echo "Can not find user by username=".$username."<br>";
        }
        return $approver;
    }

    public function statusRequestMapper( $statusId ) {
        $status = null;
        $statusProgress = null;
        $statusBilling = null;

//        progressState
//        #5 places
//        - draft
//        - active
//        - canceled
//        - completed
//        - completedNotified
//        #7 pending places
//        - pendingInvestigatorInput
//        - pendingHistology
//        - pendingImmunohistochemistry
//        - pendingMolecular
//        - pendingCaseRetrieval
//        - pendingTissueMicroArray
//        - pendingSlideScanning

        //billingState
//        - draft
//        - active
//        - approvedInvoicing
//        - canceled
//        - missinginfo
//        - invoiced
//        - paid
//        - refunded
//        - partiallyRefunded

        switch( $statusId ){
            case "2":
                //$status = "pending";
                $statusProgress = "active";
                $statusBilling = "active";
                break;
            case "5":
                //TODO:???
                //$status = "completed";
                $statusProgress = "completed";
                $statusBilling = "paid";
                break;
        }

        $statusNew = array(
            'progress' => $statusProgress,
            'billing' => $statusBilling
        );

        return $statusNew;
    }

    public function statusMapper( $statusId, $asOriginalStr=false ) {

//        1	pending
//        2	admin-review
//        3	committee-review
//        4	committee-approval
//        5	active
//        0	draft
//        7	admin-approval
//        8	bio-statistical consultation
//        9	pending resubmission
//        10	pending revision
//        11	pending bio-statistical revision
//        6	irb-review
//        14	pending bio-statistical request
//        13	pending funding approval
//        12	closed

        $status = null;
        $statusNew = null;

        switch( $statusId ){
            case "0":
                $status = "draft";
                $statusNew = "draft";
                break;
            case "1":
                $status = "pending";
                $statusNew = "draft";
                break;
            case "2":
                $status = "admin-review";
                $statusNew = "admin_review";
                break;
            case "3":
                $status = "committee-review";
                $statusNew = "committee_review";
                break;
            case "4":
                $status = "committee-approval";
                $statusNew = "final_review";
                break;
            case "5":
                $status = "active";
                $statusNew = "final_approved";
                break;
            case "6":
                $status = "irb-review";
                $statusNew = "irb_review";
                break;
            case "7":
                $status = "admin-approval";
                $statusNew = "committee_review";
                break;
            case "8":
                $status = "bio-statistical consultation";
                $statusNew = "closed";
                break;
            case "9":
                $status = "pending resubmission";
                //$statusNew = "draft";
                $statusNew = "closed";
                break;
            case "10":
                $status = "pending revision";
                //$statusNew = "draft";
                $statusNew = "closed";
                break;
            case "11":
                $status = "pending bio-statistical revision";
                //$statusNew = "draft";
                $statusNew = "closed";
                break;
            case "12":
                $status = "closed";
                $statusNew = "closed";
                break;
            case "13":
                $status = "pending funding approval";
                //$statusNew = "draft";
                $statusNew = "closed";
                break;
            case "14":
                $status = "pending bio-statistical request";
                //$statusNew = "draft";
                $statusNew = "closed";
                break;
            default:
                $status = "pending";
                $statusNew = "draft";
        }

        if( $asOriginalStr ) {
            return $status;
        }

        return $statusNew;
    }

    public function projectTypeMapper( $id ) {

        //1	Case Study	0
        //2	Descriptive Study	0
        //3	Association Study - Request Statistical Support	1

        $status = null;
        $statusNewSystem = null;

        switch( $id ){
            case "1":
                //$status = "Case Study";
                $statusNewSystem = "Clinical Research (Case Study)";
                break;
            case "2":
                //$status = "Descriptive Study";
                $statusNewSystem = "Experimental Research (Descriptive Study)";
                break;
            case "3":
                //$status = "Association Study - Request Statistical Support";
                $statusNewSystem = "Education/Teaching (Pathology Faculty)";
                break;
        }

        if( $statusNewSystem ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:ProjectTypeList'] by [ProjectTypeList::class]
            $listEntity = $this->em->getRepository(ProjectTypeList::class)->findOneByName($statusNewSystem);
            return $listEntity;
        }

        return null;
    }

    //Update Request from "UpdatedReqStatus.xlsx": Price, Status, Comment
    public function updateRequests( $request, $filename ) {

        $logger = $this->container->get('logger');

        $inputFileName = __DIR__ . "/" . $filename; //'/TRF_PROJECT_INFO.xlsx';
        echo "==================== Processing $filename =====================<br>";

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        echo "Highest row=$highestRow <br>";

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);

        $batchSize = 300;
        $count = 0;

        //for each request in excel (start at row 2)
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //Insert row data array into the database
            //echo $row.": ";
            //var_dump($rowData);
            //echo "<br>";

            $exportId = $this->getValueByHeaderName('REQUEST', $rowData, $headers);
            $exportId = trim((string)$exportId);
            //echo "<br>########## Request exportId=" . $exportId . "#############<br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
            $transresRequest = $this->em->getRepository(TransResRequest::class)->findOneByExportId($exportId);
            if( !$transresRequest ) {
                //exit("Request not found by External ID ".$exportId);
                continue;
            }

            $price = $this->getValueByHeaderName('TOTAL CHARGE', $rowData, $headers);
            $status = $this->getValueByHeaderName('STATUS', $rowData, $headers);
            $comment = $this->getValueByHeaderName('COMMENT', $rowData, $headers);
            //echo "Request $exportId: Price=$price; status=$status; comment=$comment <br>";
            //exit('111');

            $updatedCount = 0;

            if( $status ) {
                if( $status == "Completed" ) {
                    //$status = "completed";
                    $statusProgress = "completed";
                    $statusBilling = "paid";
                }
                if( $status == "Suspended" ) {
                    //Comments for two requests said "canceled", therefore I assume that the status is "canceled".
                    //$status = "pending";
                    $statusProgress = "canceled";
                    $statusBilling = "canceled";
                }

                if( $transresRequest->getProgressState() != $statusProgress ) {
                    $transresRequest->setProgressState($statusProgress);
                    $updatedCount++;
                }
                if( $transresRequest->getBillingState() != $statusBilling ) {
                    $transresRequest->setBillingState($statusBilling);
                    $updatedCount++;
                }
            }

            if( $comment ) {
                $requestComment = $transresRequest->getComment();
                if( $requestComment ) {
                    if( strpos((string)$requestComment, $comment) === false ) {
                        //Append to the Comment
                        $requestComment = $requestComment . "\r\n \r\n" .
                            "### Updated comment: ###" . "\r\n" . $comment . "\r\n" . "#########";
                        $transresRequest->setComment($requestComment);
                        $updatedCount++;
                    }
                }
            }

            if( $price ) {
                $requestComment = $transresRequest->getComment();
                if( $requestComment ) {
                    if( strpos((string)$requestComment, $price) === false ) {
                        //Append to the Comment
                        $requestComment = $requestComment . "\r\n \r\n" .
                            "### Updated price: ###" . "\r\n" . $price . "\r\n" . "#########";
                        $transresRequest->setComment($requestComment);
                        $updatedCount++;
                    }
                }
            }

            if( $updatedCount > 0 ) {
                //$this->em->flush($transresRequest);
                //echo $count.": Updated $updatedCount times<br>";
                echo ".";
            }

            if( ($count % $batchSize) === 0 ) {
                $this->em->flush();
                ////$this->em->clear(); // Detaches all objects from Doctrine!
                echo "<br>";
            }

            $count++;
        }

        $this->em->flush();
        echo "<br>";

        return "Processed $count records";
    }

    public function userRoleMapper() {
//        1	Request
//        2	Admin Review
//        3	Committee Review
//        4	Final Approval
//        5	Admin Review/Final Approval
//        6	Admin View Only
//        9	Biostatistical Review
//        7	IRB Review
//        8	IRB Review/Committee Review
    }

    public function createAntibodyList($filename) {
        $importUtil = $this->container->get('transres_import');

        $res1 = $importUtil->generateAntibodyList($filename);

        $res2 = "";
        if( $res1 === true ) {
            $res2 = $importUtil->setAntibodyListProperties();
        }

        //exit("generateAntibodyListAction: Finished with res=".$res);
        return $res1 . "<br>" . $res2;
    }
    public function generateAntibodyList($filename) {

        //AntibodyList
        //INSERT INTO `IHC_antibody` (`id`, `category`, `name`, `altname`, `company`, `catalog`, `lot`, `igconcentration`, `clone`, `host`, `reactivity`, `control`, `protocol`, `retrieval`, `dilution`, `storage`, `comment`, `datasheet`, `pdf`) VALUES
        //(1, 'M', 'Androgen Receptor', 'AR ', 'Abcam', 'ab74272', 'GR32463-1', '0.2 mg/ml', 'Poly', 'Rabbit ', 'Human, mouse', 'Xenograft Control/Prostate Ca.', 'Envision Rabbit R. ', 'H130', '1:200', '-20 oC', 'Project: 12743 RS#: 30323 PI: Rubin/Kyung Condition confirmed by Dr. Rubin/Kyung on 03/09/2011', 'http://www.abcam.com/Androgen-Receptor-antibody-ab74272.html', 'upload/pdf/1296507249.pdf'),

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:AntibodyList'] by [AntibodyList::class]
        $lists = $this->em->getRepository(AntibodyList::class)->findAll();
        if( count($lists) > 0 ) {
            return "AntibodyList is already exists.";
        }

        //check if db compatable with filename
        $userServiceUtil = $this->container->get('user_service_utility');
        $dbInfo = $userServiceUtil->getDbVersion(); //PostgreSQL 14.3, compiled by Visual C++ build 1914, 64-bit
        $dbInfoLower = strtolower($dbInfo);
        //ihc_antibody_postgresql.sql
        if( str_contains($filename, 'postgresql') ) {
            if( str_contains($dbInfoLower, 'postgresql') === false ) {
                return "File ".$filename. " is not compatable with current database " . $dbInfo;
            }
        }
        //ihc_antibody_mssql.sql
        if( str_contains($filename, 'mssql') ) {
            if( str_contains($dbInfoLower, 'mssql') === false ) {
                return "File ".$filename. " is not compatable with current database " . $dbInfo;
            }
        }
        //ihc_antibody_mysql.sql
        if( str_contains($filename, 'mysql') ) {
            if( str_contains($dbInfoLower, 'mysql') === false ) {
                return "File ".$filename. " is not compatable with current database " . $dbInfo;
            }
        }

        //$filename = 'ihc_antibody.sql';
        $inputFileName = __DIR__ . "/" . $filename;

        if (file_exists($inputFileName)) {
            //echo "The file $filename exists";
        } else {
            return "The file $inputFileName does not exist";
        }

        echo "==================== Processing $filename =====================<br>";

        $sql = file_get_contents($inputFileName);  // Read file contents

        $this->em->getConnection()->exec($sql);  // Execute native SQL

        $this->em->flush();

        //exit("generateAntibodyList: Finished");
        return true;
    }
    public function setAntibodyListProperties() {
        $userSecUtil = $this->container->get('user_security_utility');
        $systemuser = $userSecUtil->findSystemUser();

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:AntibodyList'] by [AntibodyList::class]
        $lists = $this->em->getRepository(AntibodyList::class)->findAll();

        //set creator, createdate, type, orderinlist and creation time
        $orderinlist = 1;
        $counter = 0;
        $batchSize = 20;

        foreach($lists as $list) {
            if( !$list->getType() || !$list->getCreatedate() ) {
                echo "set Properties for list ID=".$list->getId()."<br>";
                $list->setCreator($systemuser);
                $list->setCreatedate(new \DateTime());
                $list->setType('default');
                $list->setOrderinlist($orderinlist);
                $list->setVersion(1);
                $orderinlist = $orderinlist + 10;

                if( ($counter % $batchSize) === 0 ) {
                    $this->em->flush();
                    //$this->em->clear(); // Detaches all objects from Doctrine!
                }

                $counter++;
            }
        }

        $this->em->flush(); //Persist objects that did not make up an entire batch
        //$this->em->clear();

        $res = "Inserted $counter anti body records to the AntibodyList object.";

        return $res;
    }

    //Run by: http://127.0.0.1/order/translational-research/update-insert-antibody-list
    //TODO: dilution convert
    //3;"M";"Bcl-6 -  Rabbit Anti-mouse";NULL;"Santa Cruz";"Bcl6 (sc-858)";;"200 ug/ml";"Poly";"Rabbit";"Mouse	 human	 rat";"I08-995 A1 (#22)) Flip CD19 Promotor/";"Envision Rabbit Refine";"H230";"1 to 50";"4C";"Project: 10820 RS#:  PI: Cesarman ";"http://www.scbt.com/datasheet-858-bcl-6-n-3-antibody.html";" "
    //3, 'M', 'Bcl-6 -  Rabbit Anti-mouse', NULL, 'Santa Cruz', 'Bcl6 (sc-858)', '', '200 ug/ml', 'Poly', 'Rabbit', 'Mouse, human, rat', 'I08-995 A1 (#22)) Flip CD19 Promotor/', 'Envision Rabbit Refine', 'H230', '1 to 50', '4C', 'Project: 10820 RS#:  PI: Cesarman ', 'http://www.scbt.com/datasheet-858-bcl-6-n-3-antibody.html', ' '),
    //1;"M";"Androgen Receptor";"AR ";"Abcam";"ab74272";"GR32463-1";"0.2 mg/ml";"Poly";"Rabbit ";"Human	 mouse";"Xenograft Control/Prostate Ca.";"Envision Rabbit R. ";"H130";"1:200";"-20 oC";"Project: 12743 RS#: 30323 PI: Rubin/Kyung Condition confirmed by Dr. Rubin/Kyung on 03/09/2011";"http://www.abcam.com/Androgen-Receptor-antibody-ab74272.html";"upload/pdf/1296507249.pdf"
    //1	M	Androgen Receptor	AR 	Abcam	ab74272	GR32463-1	0.2 mg/ml	Poly	Rabbit 	Human, mouse	Xenograft Control/Prostate Ca.	Envision Rabbit R. 	H130	0.180555556	-20 oC	Project: 12743 RS#: 30323 PI: Rubin/Kyung Condition confirmed by Dr. Rubin/Kyung on 03/09/2011	http://www.abcam.com/Androgen-Receptor-antibody-ab74272.html	upload/pdf/1296507249.pdf
    public function updateInsertAntibodyList($filename) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        $inputFileName = __DIR__ . "/" . $filename; //'/TRF_PROJECT_INFO.xlsx';
        echo "==================== Processing $filename =====================<br>";

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        echo "Highest row=$highestRow <br>";

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);

        $systemuser = $userSecUtil->findSystemUser();

        $batchSize = 300;
        $count = 0;

        //for each request in excel (start at row 2)
        for( $row = 2; $row <= $highestRow; $row++ ) {

            $update = false;

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //Insert row data array into the database
            //echo $row.": ";
            //var_dump($rowData);
            //echo "<br>";

            //id
            $antibodyId = $this->getValueByHeaderName('id', $rowData, $headers);
            $antibodyId = trim((string)$antibodyId);
            //echo "<br>########## antibodyId=" . $antibodyId . "#############<br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:AntibodyList'] by [AntibodyList::class]
            $antibody = $this->em->getRepository(AntibodyList::class)->find($antibodyId);

//            if( !$antibody ) {
//                $antibody = $this->em->getRepository('AppTranslationalResearchBundle:AntibodyList')->findOneByExportId($antibodyId);
//                echo "Found by exportId antibodyId=" . $antibodyId . "<br>";
//            }

            if( !$antibody ) {
                exit("Request not found by External ID ".$antibodyId);
                //create a new antibody record
                $antibody = new AntibodyList($systemuser);

                $antibody->setId($antibodyId);
                $antibody->setExportId($antibodyId);

                $antibody->setType('default');

                //$classNamespaceShort = "AppTranslationalResearchBundle";
                $bundleName = "TranslationalResearchBundle";
                $className = "AntibodyList";
                //$classFullName = $classNamespaceShort . ":" . $className;
                $classFullName = "App\\" . $bundleName . "\\Entity\\" . $className;
                $orderinlist = $userSecUtil->getMaxField($classFullName);
                echo "Create a new orderinlist=$orderinlist<br>";

                if( $orderinlist ) {
                    $antibody->setOrderinlist($orderinlist);
                }

                $this->em->persist($antibody);

                //Explicitly set Id with Doctrine when using “AUTO” strategy
                //DOES NOT WORK ON SQL SERVER MSSQL
                $metadata = $this->em->getClassMetaData(get_class($antibody));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

                $update = true;
            }

            //echo "orderinlist=".$antibody->getOrderinlist()."<br>";

            //category
            $category = $this->getValueByHeaderName('category', $rowData, $headers);
            if( $antibody->getCategory() != $category ) {
                echo "update category=[".$antibody->getCategory()."]=>[".$category."]<br>";
                $antibody->setCategory($category);
                $update = true;
            }

            //name
            $name = $this->getValueByHeaderName('name', $rowData, $headers);
            if( $antibody->getName() != $name ) {
                echo "update name=[".$antibody->getName()."]=>[".$name."]<br>";
                $antibody->setName($name);
                $update = true;
            }

            //altname
            $altname = $this->getValueByHeaderName('altname', $rowData, $headers);
            if( $antibody->getAltname() != $altname ) {
                echo "update altname=[".$antibody->getAltname()."]=>[".$altname."]<br>";
                $antibody->setAltname($altname);
                $update = true;
            }

            //company
            $company = $this->getValueByHeaderName('company', $rowData, $headers);
            if( $antibody->getCompany() != $company ) {
                $antibody->setCompany($company);
                $update = true;
            }

            //catalog
            $catalog = $this->getValueByHeaderName('catalog', $rowData, $headers);
            if( $antibody->getCatalog() != $catalog ) {
                $antibody->setCatalog($catalog);
                $update = true;
            }

            //lot
            $lot = $this->getValueByHeaderName('lot', $rowData, $headers);
            if( $antibody->getLot() != $lot ) {
                $antibody->setLot($lot);
                $update = true;
            }

            //igconcentration
            $igconcentration = $this->getValueByHeaderName('igconcentration', $rowData, $headers);
            if( $antibody->getIgconcentration() != $igconcentration ) {
                $antibody->setIgconcentration($igconcentration);
                $update = true;
            }

            //clone
            $clone = $this->getValueByHeaderName('clone', $rowData, $headers);
            if( $antibody->getClone() != $clone ) {
                $antibody->setClone($clone);
                $update = true;
            }

            //host
            $host = $this->getValueByHeaderName('host', $rowData, $headers);
            if( $antibody->getHost() != $host ) {
                $antibody->setHost($host);
                $update = true;
            }

            //reactivity
            $reactivity = $this->getValueByHeaderName('reactivity', $rowData, $headers);
            if( $antibody->getReactivity() != $reactivity ) {
                $antibody->setReactivity($reactivity);
                $update = true;
            }

            //control
            $control = $this->getValueByHeaderName('control', $rowData, $headers);
            if( $antibody->getControl() != $control ) {
                $antibody->setControl($control);
                $update = true;
            }

            //protocol
            $protocol = $this->getValueByHeaderName('protocol', $rowData, $headers);
            if( $antibody->getProtocol() != $protocol ) {
                $antibody->setProtocol($protocol);
                $update = true;
            }

            //retrieval
            $retrieval = $this->getValueByHeaderName('retrieval', $rowData, $headers);
            if( $antibody->getRetrieval() != $retrieval ) {
                $antibody->setRetrieval($retrieval);
                $update = true;
            }

            //dilution
            $dilution = $this->getValueByHeaderName('dilution', $rowData, $headers);
            if( $antibody->getDilution() != $dilution ) {
                $antibody->setDilution($dilution);
                $update = true;
                echo "dilution=$dilution<br>";
            }

            //storage
            $storage = $this->getValueByHeaderName('storage', $rowData, $headers);
            if( $antibody->getStorage() != $storage ) {
                $antibody->setStorage($storage);
                $update = true;
            }

            //comment
            $comment = $this->getValueByHeaderName('comment', $rowData, $headers);
            if( $antibody->getComment() != $comment ) {
                $antibody->setComment($comment);
                $update = true;
            }

            //datasheet
            $datasheet = $this->getValueByHeaderName('datasheet', $rowData, $headers);
            if( $antibody->getDatasheet() != $datasheet ) {
                $antibody->setDatasheet($datasheet);
                $update = true;
            }

            //pdf
            $pdf = $this->getValueByHeaderName('pdf', $rowData, $headers);
            if( $antibody->getPdf() != $pdf ) {
                $antibody->setPdf($pdf);
                $update = true;
            }

            if( $update ) {
                echo "<br>########## antibodyId=" . $antibodyId . "#############<br>";
                echo "### Updated ID=".$antibody->getId()." <br>";
                $count++;
                $this->em->flush($antibody);
            } else {
                //echo "*** Not updated <br>";
            }
        }

        //$this->em->flush();
        echo "Processed $count records <br>";
        exit("exit");

        return "Processed $count records";
    }

    //NOT USED: DOES NOT WORK ON SQL SERVER MSSQL
    //run: http://127.0.0.1/order/translational-research/sync-id-antibody-list
    public function syncIdAntibodyList() {
        //$antibodies = $this->em->getRepository('AppTranslationalResearchBundle:AntibodyList')->findAll();
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:AntibodyList'] by [AntibodyList::class]
        $repository = $this->em->getRepository(AntibodyList::class);
        $dql =  $repository->createQueryBuilder("antibody");
        $dql->select('antibody');
        $dql->where("antibody.exportId IS NOT NULL");
        $dql->orderBy("antibody.id","DESC");
        $query = $dql->getQuery();
        $antibodies = $query->getResult();
        echo "antibodies count:".count($antibodies)."<br>";

        $count = 0;
        foreach( $antibodies as $antibody ) {
            $exportId = $antibody->getExportId();
            //echo "reset ID: [".$antibody->getId()."] to [".$exportId."]<br>";
            if( $exportId && $exportId != $antibody->getId() ) {

                echo "reset ID: [".$antibody->getId()."] to [".$exportId."]<br>";

                //$this->em->persist($antibody);
                $antibody->setId($exportId);

                //Explicitly set Id with Doctrine when using “AUTO” strategy
                //DOES NOT WORK ON SQL SERVER MSSQL
                //$metadata = $this->em->getClassMetaData(get_class($antibody));
                $metadata = $this->getEntityManager()->getClassMetaData(AntibodyList::class);

                $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

                $this->em->persist($antibody);

                //$antibody->setId($exportId);

                //$this->em->flush($antibody);
                $this->em->flush();

                $count++;
            }
        }

        exit("exit count=".$count);
        return $count;
    }


    public function addNewFees($inputFileName) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $transresUtil = $this->container->get('transres_util');

        $systemuser = $userSecUtil->findSystemUser();
        $specialtyMisiObject = $transresUtil->getSpecialtyObject("misi");
        if( !$specialtyMisiObject ) {
            exit("specialtyMisiObject misi is not found");
        }

        //$inputFileName = __DIR__ . "/" . $filename; //'/TRF_PROJECT_INFO.xlsx';
        echo "==================== Processing $inputFileName =====================<br>";

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);


        $count = 0;

        //for each request in excel (start at row 2)
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $code = $this->getValueByHeaderName('CODE', $rowData, $headers);
            $serviceCategory = $this->getValueByHeaderName('SERVICE CATEGORY', $rowData, $headers);
            $price = $this->getValueByHeaderName('PRICE', $rowData, $headers);
            $unit = $this->getValueByHeaderName('UNIT', $rowData, $headers);

            echo $count.": Code=[$code]";
            echo "; serviceCategory=[$serviceCategory]";
            echo "; price=[$price]";
            echo "; unit=[$unit] <br>";

            //Check if already exists by $code
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequestCategoryTypeList'] by [RequestCategoryTypeList::class]
            $feeDb = $this->em->getRepository(RequestCategoryTypeList::class)->findOneByProductId($code);
            if( $feeDb ) {
                echo "Fee already exists $feeDb <br>";
                continue;
            }

            $fee = new RequestCategoryTypeList($systemuser);

            $fee->setType('default');

            //Get orderinlist
            //$classNamespaceShort = "AppTranslationalResearchBundle";
            $bundleName = "TranslationalResearchBundle";
            $className = "RequestCategoryTypeList";
            //$classFullName = $classNamespaceShort . ":" . $className;
            $classFullName = $classFullName = "App\\" . $bundleName . "\\Entity\\" . $className;
            $orderinlist = $userSecUtil->getMaxField($classFullName);
            echo "Create a new orderinlist=$orderinlist<br>";

            if( $orderinlist ) {
                $fee->setOrderinlist($orderinlist);
            }

            //Name $serviceCategory
            if( $fee->getName() != $serviceCategory ) {
                $fee->setName($serviceCategory);
                $update = true;
            }

            //Section:
            //Vectra Polaris
            //Starts with MISI-1XXX
            if( strpos((string)$code, 'MISI-1') !== false ) {
                $section = 'Vectra Polaris';
            }
            //CODEX
            //Starts with MISI-2XXX
            if( strpos((string)$code, 'MISI-2') !== false ) {
                $section = 'CODEX';
            }
            //GeoMX
            //Starts with MISI-3XXX
            if( strpos((string)$code, 'MISI-3') !== false ) {
                $section = 'GeoMX';
            }
            if( $section ) {
                echo "Section=$section <br>";
                if( $fee->getSection() != $section ) {
                    $fee->setSection($section);
                    $update = true;
                }
            }

            //Product ID
            if( $fee->getProductId() != $code ) {
                $fee->setProductId($code);
                $update = true;
            }

            //Fee
            if( $fee->getFee() != $price ) {
                $fee->setFee($price);
                $update = true;
            }

            //Fee Unit
            if( $fee->getFeeUnit() != $unit ) {
                $fee->setFeeUnit($unit);
                $update = true;
            }

            //Project Specialty
            $fee->addProjectSpecialty($specialtyMisiObject);


            if( $update ) {
                //echo "<br>########## fee=" . $fee . "#############<br>";
                echo "### Create Fee: ".$fee->getOptimalAbbreviationName()." <br><br>";
                $count++;
                $this->em->persist($fee);
                $this->em->flush();
            } else {
                //echo "*** Not created <br>";
            }
        }

        exit("Added $count fees");
    }


    //http://127.0.0.1/order/translational-research/update-multiple-fees
    public function addNewMultipleFees($inputFileName) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $transresUtil = $this->container->get('transres_util');

        $systemuser = $userSecUtil->findSystemUser();
        $specialtyMisiObject = $transresUtil->getSpecialtyObject("misi");
        if( !$specialtyMisiObject ) {
            exit("specialtyMisiObject misi is not found");
        }

        //$inputFileName = __DIR__ . "/" . $filename; //'/TRF_PROJECT_INFO.xlsx';
        echo "==================== Processing $inputFileName =====================<br>";

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);


        $count = 0;
        $updateCount = 0;

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:PriceTypeList'] by [PriceTypeList::class]
        $internalPriceList = $this->em->getRepository(PriceTypeList::class)->findOneByName("Internal Pricing");
        if( !$internalPriceList ) {
            exit("Internal price list does not exist");
        }

        //for each request in excel (start at row 2)
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $code = $this->getValueByHeaderName('CODE', $rowData, $headers);
            //$serviceCategory = $this->getValueByHeaderName('SERVICE CATEGORY', $rowData, $headers);
            //$price = $this->getValueByHeaderName('PRICE', $rowData, $headers);
            $unit = $this->getValueByHeaderName('UNIT', $rowData, $headers);
            $name  = $this->getValueByHeaderName('Fee Schedule (1/1/2021 - 12/31/2021)', $rowData, $headers);

            //regular fee (old)
            $fee = $this->getValueByHeaderName('FIRST ITEM (EXTERNAL)', $rowData, $headers);
            $feeAdditionalItem = $this->getValueByHeaderName('ADDITIONAL ITEM (EXTERNAL)', $rowData, $headers);
            //$fee = intval($fee);
            $fee = $this->toDecimal($fee);
            //$feeAdditionalItem = intval($feeAdditionalItem);
            $feeAdditionalItem = $this->toDecimal($feeAdditionalItem);

            //special internal fee (new)
            $internalFee = $this->getValueByHeaderName('FIRST ITEM (INTERNAL)', $rowData, $headers);
            $internalFeeAdditionalItem = $this->getValueByHeaderName('ADDITIONAL ITEM (INTERNAL)', $rowData, $headers);
            //$internalFee = intval($internalFee);
            //$internalFeeAdditionalItem = intval($internalFeeAdditionalItem);
            if( is_numeric($internalFee) ) {
                $internalFee = $this->toDecimal($internalFee);
            }
            if( is_numeric($internalFeeAdditionalItem) ) {
                $internalFeeAdditionalItem = $this->toDecimal($internalFeeAdditionalItem);
            }

            if(1) {
                echo "<br>".$count . ": Code=[$code]";
                echo "; name=[$name]";
                echo "; unit=[$unit]";
                echo "; fee=[$fee]";
                echo "; feeAdditionalItem=[$feeAdditionalItem]";
                echo "; internalFee=[$internalFee]";
                echo "; internalFeeAdditionalItem=[$internalFeeAdditionalItem] <br>";
            }

            //Check if already exists by $code
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequestCategoryTypeList'] by [RequestCategoryTypeList::class]
            $priceFeeDb = $this->em->getRepository(RequestCategoryTypeList::class)->findOneByProductId($code);
            if( $priceFeeDb ) {
                //echo "Fee already exists $feeDb <br>";
                //continue;
            } else {
                echo "Price fee does not exist in DB $code <br>";
                continue;

                if(0) {
                    $priceFeeDb = new RequestCategoryTypeList($systemuser);

                    $priceFeeDb->setType('default');

                    //Get orderinlist
                    $classNamespaceShort = "TranslationalResearchBundle";
                    $className = "RequestCategoryTypeList";
                    //$classFullName = $classNamespaceShort . ":" . $className;
                    $classFullName = $classFullName = "App\\" . $classNamespaceShort . "\\Entity\\" . $className;
                    $orderinlist = $userSecUtil->getMaxField($classFullName);
                    echo "Create a new orderinlist=$orderinlist<br>";

                    if ($orderinlist) {
                        $priceFeeDb->setOrderinlist($orderinlist);
                    }

                    //Name $serviceCategory
                    if ($priceFeeDb->getName() != $name) {
                        $priceFeeDb->setName($name);
                        $update = true;
                    }

                    $this->em->persist($priceFeeDb);
                }
            }

            $update = false;
            $updateArr = array();


            //$feeDb = intval($priceFeeDb->getFee());
            //$feeAdditionalItemDb = intval($priceFeeDb->getFeeAdditionalItem());
            $feeDb = $this->toDecimal($priceFeeDb->getFee());
            $feeAdditionalItemDb = $this->toDecimal($priceFeeDb->getFeeAdditionalItem());

            $nameDb = $priceFeeDb->getName()."";
            if(
                strtolower($name) != strtolower($nameDb)
                || $fee != $feeDb
                //|| strtolower($unit) != strtolower($priceFeeDb->getFeeUnit()."")
            ){
                //$priceFeeDb->setFee($fee);
                $update = true;
//                echo $code.': Difference<br>'.
//                'Old: "'.$name.'" $'.$feeDb.', unit= '.$unit.
//                '<br>New: "'.$priceFeeDb->getName().'" $'.$fee.', unit= '.$priceFeeDb->getFeeUnit().
//                ' <br>';
                $priceFeeDb->setName($name);
                $updateArr[] = "new name=[".$priceFeeDb->getName()."], old=[".$nameDb."]";
            }

//            $unitDb = $priceFeeDb->getFeeUnit()."";
//            if( strtolower($unit) != strtolower($unitDb) ) {
//                $priceFeeDb->setFeeUnit($unit);
//                $update = true;
//                //echo "!!! feeAdditionalItem different: [$feeAdditionalItem] != [".$feeAdditionalItemDb."] <br>";
//                $updateArr[] = "new unit=[".$priceFeeDb->getFeeUnit()."], old=[".$unitDb."]";
//            }

            if( $fee != $feeDb ) {
                $priceFeeDb->setFee($fee);
                $update = true;
                //echo $code.": !!! Fee different: [$fee] != [".$feeDb."] <br>";
                $updateArr[] = "new fee=[$fee], old=[$feeDb]";
            }

            if( $feeAdditionalItem != $feeAdditionalItemDb ) {
                $priceFeeDb->setFeeAdditionalItem($feeAdditionalItem);
                $update = true;
                //echo "!!! feeAdditionalItem different: [$feeAdditionalItem] != [".$feeAdditionalItemDb."] <br>";
                $updateArr[] = "new additional fee=[$feeAdditionalItem], old=[$feeAdditionalItemDb]";
            }

            //Process internal fees
            if(
                $internalFee && $internalFeeAdditionalItem
                && is_numeric($internalFee) && is_numeric($internalFeeAdditionalItem)
            ) {

                $internalFeeDb = NULL;
                $internalFeeAdditionalItemDb = NULL;

                $internalPriceObject = $priceFeeDb->getPrice($internalPriceList);
                if( $internalPriceObject ) {
                    echo "Price already exist $internalPriceObject <br>";
                    //$internalFeeDb = intval($internalPriceObject->getFee());
                    //$internalFeeAdditionalItemDb = intval($internalPriceObject->getFeeAdditionalItem());
                    $internalFeeDb = $this->toDecimal($internalPriceObject->getFee());
                    $internalFeeAdditionalItemDb = $this->toDecimal($internalPriceObject->getFeeAdditionalItem());
                }

                $updateInternalFee = false;
                $updateInternalAdditionalFee = false;

                //echo "??? fee=[$fee], internal fee=[$internalFee], old=[$internalFeeDb] <br>";
                if(
                    $internalFee !== NULL &&
                    $fee !== $internalFee &&
                    $internalFee !== $internalFeeDb
                    && is_numeric($internalFee)
                ) {
                    $updateInternalFee = true;
                    //echo "Update internal fee=[$internalFee], old=[$internalFeeDb] <br>";
                }

                //echo "??? additional fee=[$feeAdditionalItem], internal additional fee=[$internalFeeAdditionalItem], old=[$internalFeeAdditionalItemDb] <br>";
                if(
                    $internalFeeAdditionalItem !== NULL &&
                    $feeAdditionalItem !== $internalFeeAdditionalItem &&
                    $internalFeeAdditionalItem !== $internalFeeAdditionalItemDb
                    && is_numeric($internalFeeAdditionalItem)
                ) {
                    $updateInternalAdditionalFee = true;
                    //echo "Update internal additional fee=[$internalFeeAdditionalItem], old=[$internalFeeAdditionalItemDb] <br>";
                }

            }

            if( $updateInternalFee && $updateInternalAdditionalFee ) {
                if( !$internalPriceObject ) {
                    $internalPriceObject = new Prices();
                    $internalPriceObject->setPriceList($internalPriceList);
                    $priceFeeDb->addPrice($internalPriceObject);
                    //$this->em->persist($internalPriceObject);
                    $updateArr[] = "Created new internal specific price [$internalPriceList]";
                }

                if( $updateInternalFee ) {
                    $internalPriceObject->setFee($internalFee);
                    $update = true;
                    $updateArr[] = "new internal fee=[$internalFee], old=[$internalFeeDb]";
                }

                if( $updateInternalAdditionalFee ) {
                    $internalPriceObject->setFeeAdditionalItem($internalFeeAdditionalItem);
                    $update = true;
                    $updateArr[] = "new internal additional fee=[$internalFeeAdditionalItem], old=[$internalFeeAdditionalItemDb]";
                }
            } else {
                $updateArr[] = "Don't update internal fees: internalFee=[$internalFee] internalFeeAdditionalItem=[$internalFeeAdditionalItem]";
            }

            if( $update ) {
                //echo "<br>########## fee=" . $fee . "#############<br>";
                $updateStr = "No update";
                if( count($updateArr) > 0 ) {
                    $updateStr = "<br>".implode("<br>",$updateArr);
                }
                echo "### Update price: ".$priceFeeDb->getOptimalAbbreviationName().": ".$updateStr." <br><br>";
                $updateCount++;
                //$this->em->persist($priceFeeDb);
                //$this->em->flush();
            } else {
                //echo "*** Not created <br>";
            }

            $count++;
        }

        exit("Processed $count, updated $updateCount price fees");

        return $count;
    }

    //http://127.0.0.1/order/translational-research/batch-close-projects
    public function closeProjectsFromSpreadsheet($inputFileName,$request) {

        exit("Not permitted");

        //$logger = $this->container->get('logger');
        //$userSecUtil = $this->container->get('user_security_utility');
        $transresUtil = $this->container->get('transres_util');

        //$systemuser = $userSecUtil->findSystemUser();

        //$inputFileName = __DIR__ . "/" . $filename; //'/TRF_PROJECT_INFO.xlsx';
        echo "==================== Processing $inputFileName =====================<br>";

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            //$logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

//        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
//            NULL,
//            TRUE,
//            FALSE);

        $projectOids = array();
        $count = 0;

        //for each request in excel (start at row 2)
        for( $row = 1; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //print_r($rowData);
            //exit('111');

            $projectId = $rowData[0][0];
            //echo "1projectId=$projectId <br>";

            if( !$projectId ) {
                continue;
            }

            //convert APCP41 (10880) to APCP41
            if( strpos((string)$projectId, " (") !== false ) {
                $projectIdSplit = explode(" (", $projectId);
                $projectId = $projectIdSplit[0];
            }

            echo "projectId=[$projectId] <br>";
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
            $project = $this->em->getRepository(Project::class)->findOneByOid($projectId);

            if( $project ) {

                //$testing = false;
                $originalStateStr = $project->getState();
                $to = "closed";

                $project->setState($to);

                $this->em->flush($project);

                $projectOids[] = $project->getOid()." (original state=".$originalStateStr.")";

                $count++;
            } else {
                echo "!!! project is null <br>";
            }

            //exit();
        }

        //event log
        $resultMsg = $count." projects are closed in batch by a script: " . implode(", ",$projectOids);

        $eventType = "Project Updated";
        $transresUtil->setEventLog(NULL,$eventType,$resultMsg);
        //$this->container->get('session')
        $request->getSession()->getFlashBag()->add(
            'notice',
            $resultMsg
        );

        exit("Processed $count projects: $resultMsg");

        return $count;
    }


    public function toDecimal($number) {
        return number_format((float)$number, 2, '.', '');
    }

    public function populateProjectComment($filename, $startRaw=2, $endRaw=null) {

        exit('exit populateProjectComment');

        if (file_exists($filename)) {
            echo "EXISTS: The file $filename <br><br>";
        } else {
            echo "Does Not EXISTS: The file $filename <br><br>";
        }

        set_time_limit(18000); //18000 seconds => 5 hours 3600sec=>1 hour
        ini_set('memory_limit', '7168M');

        $transresUtil = $this->container->get('transres_util');
        $logger = $this->container->get('logger');

        $eventType = "Request Updated";

        //$userMapper = $this->getUserMapper('TRF_EMAIL_INFO.xlsx');

        //$inputFileName = __DIR__ . "/" . $filename;
        echo "==================== Processing $filename =====================<br>";
        $logger->notice("==================== Processing $filename =====================");

        try {
            if(1) {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filename);
                $reader->setReadDataOnly(true);
                $objPHPExcel = $reader->load($filename);
                //exit('111');
            }

            //$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            //$objPHPExcel = $reader->load($inputFileName);

            if(0) {
                //$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filename);
                $inputFileType = 'Xlsb';
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($filename);
                $objPHPExcel = $objReader->load($filename);
            }
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($filename,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        echo "highestRow=".$highestRow."; highestColum=".$highestColumn."<br>";

        $highestColumn = 'Q'; //max column for this file

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);

        $this->headerMapArr = $this->getHeaderMap($headers);

        $limitRow = $highestRow;
        if( $endRaw && $endRaw <= $highestRow ) {
            $limitRow = $endRaw;
        }

        if( $startRaw < 2 ) {
            $startRaw = 2; //minimum raw
        }

        echo "start Iteration from $startRaw to ".$limitRow."; highestColumn=".$highestColumn."<br>"; //start Iteration from 2 to 1048557
        $logger->notice("start Iteration from $startRaw to ".$limitRow."; highestColumn=".$highestColumn);
        //exit('111');

        $currentDate = date('Y-m-d H:i:s');
        $newline = "\n\r";

        $previousRequestId = null;
        $batchSize = 20;
        $count = 0;

        //for each request in excel (start at row 2)
        for( $row = $startRaw; $row <= $limitRow; $row++ ) {

            $count++;

            //testing
            //if( $row > 9 ) {
            //    exit("row limit $row");
            //}

            //$commentArr = array();

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //dump($rowData);
            //exit('111');

            $thisOriginalRequestID = $this->getValueByHeaderName('REQ#', $rowData, $headers);
            //$thisRequestID = '20489'; //test
            echo "<br>$row: thisOriginalRequestID=$thisOriginalRequestID <br>";

            if( $thisOriginalRequestID ) {
                //remove -i and _N
                $thisRequestID = str_replace('-i','',$thisOriginalRequestID);
                $thisRequestID = str_replace('_N','',$thisRequestID);
                //check if integer
                # Check if your variable is an integer
                if( filter_var($thisRequestID, FILTER_VALIDATE_INT) === false ) {
                    echo "[$thisRequestID] is not an integer <br>";
                    $thisRequestID = null;
                }
            }
            echo "thisRequestID=$thisRequestID <br>";
            if( $thisRequestID ) {
                $requestID = $previousRequestId = $thisRequestID;
            } else {
                echo "Use previous request ID=$requestID <br>";
                $requestID = $previousRequestId;
            }

            //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
            $transresRequest = $this->em->getRepository(TransResRequest::class)->findOneById($requestID);
            if( !$transresRequest ) {
                exit("Request not found by ID ".$requestID);
            }

            $comment = $transresRequest->getComment();
            if( $comment && str_contains($comment,"Added by 2023_IHC_BH (row $row)") ) {
                //if( !$thisRequestID ) {
                    //comment might be already exists from the previous row + current row is continue of the previous row
                    //skip
                    echo "Skip requestID=$requestID <br>";
                    continue;
                //}
            }

            $projectId = $this->getValueByHeaderName('Project', $rowData, $headers);
            $submitter = $this->getValueByHeaderName('Submitter', $rowData, $headers);
            $dateSubmitted = $this->getValueByHeaderName('Date submitted', $rowData, $headers);
            $bakedDate = $this->getValueByHeaderName('Baked date', $rowData, $headers);
            $trpTech = $this->getValueByHeaderName('TRP Tech', $rowData, $headers);
            $slideN = $this->getValueByHeaderName('Slide #', $rowData, $headers);
            $tissueType = $this->getValueByHeaderName('Tissue Type', $rowData, $headers);
            $abName = $this->getValueByHeaderName('Ab name', $rowData, $headers);
            $abcompany = $this->getValueByHeaderName('Ab company', $rowData, $headers);
            $catN = $this->getValueByHeaderName('Cat#', $rowData, $headers);
            $host = $this->getValueByHeaderName('Host', $rowData, $headers);
            $condition = $this->getValueByHeaderName('Condition', $rowData, $headers);
            $note = $this->getValueByHeaderName('Note', $rowData, $headers);
            $dateDone = $this->getValueByHeaderName('Date done', $rowData, $headers);
            $tat = $this->getValueByHeaderName('TAT', $rowData, $headers);

            $requestID = trim((string)$requestID);
            //$requestID = $requestID."0000000"; //test
            echo "requestID=[" . $requestID . "]" . "; projectId=[" . $projectId .  "] <br>";

            ////// Convert Dates ///////
            $dateSubmitted = intval($dateSubmitted);
            echo "dateSubmitted=[" . $dateSubmitted .  "] <br>";
            if( $dateSubmitted ) {
                //$dateSubmittedT = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateSubmitted);
                $dateSubmittedT = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($dateSubmitted);
                //echo "dateSubmittedT=[" . $dateSubmittedT . "] <br>";
                $dateSubmitted = date("Y-m-d", $dateSubmittedT);
                //$dateStr = $dateSubmittedT->format("Y-m-d H:i:s");
            } else {
                $dateSubmitted = "";
            }
            echo "dateSubmitted=[" . $dateSubmitted . "] <br>";

            $bakedDate = intval($bakedDate);
            echo "bakedDate=[" . $bakedDate .  "] <br>";
            if( $bakedDate ) {
                $bakedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($bakedDate);
                $bakedDate = date("Y-m-d", $bakedDate);
            } else {
                $bakedDate = "";
            }
            echo "bakedDate=[" . $bakedDate . "] <br>";

            $dateDone = intval($dateDone);
            echo "dateDone=[" . $dateDone .  "] <br>";
            if( $dateDone ) {
                $dateDone = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($dateDone);
                $dateDone = date("Y-m-d", $dateDone);
            } else {
                $dateDone = "";
            }
            echo "dateDone=[" . $dateDone . "] <br>";
            ////// EOF Convert Dates ///////


            $thisProject = $transresRequest->getProject();
            if( $thisProject ) {
                $thisProjectId = $thisProject->getId();
            }

            if( $projectId && str_contains($projectId,$thisProjectId) === false ) {
                if( $thisRequestID ) {
                    echo "thisRequestID=$thisRequestID: thisProjectId=[" . $thisProjectId . "] " . '!=' . " projectId=[" . $projectId . "] <br>";
                    exit("Project ID in this file does not match the Request's project ID in DB");
                }
            } else {
                //echo "thisProjectId=[" . $thisProjectId . "] " . '==' . " projectId=[" . $projectId .  "] <br>";
            }

            //$rowDataStr = "rowDataStr";// implode(";",$rowData);
            //$rowDataStr = implode(";",$rowData[0]);
            $rowDataStr =
                'REQ#='.$thisOriginalRequestID."; ".
                'Project='.$projectId."; ".
                'Submitter='.$submitter."; ".
                'Date submitted='.$dateSubmitted."; ".
                'Baked date='.$bakedDate."; ".
                'TRP Tech='.$trpTech."; ".
                'Slide #='.$slideN."; ".
                'Tissue Type='.$tissueType."; ".
                'Ab name='.$abName."; ".
                'Ab company='.$abcompany."; ".
                'Cat#='.$catN."; ".
                'Host='.$host."; ".
                'Condition='.$condition."; ".
                'Note='.$note."; ".
                'Date done='.$dateDone."; ".
                'TAT='.$tat
            ;

            //dump($rowData);
            echo "rowDataStr=$rowDataStr <br>";

            if( $thisRequestID ) {
                $prefix = "";
            } else {
                $prefix = " (use previous request $requestID)";
            }

            if( $comment ) {
                $comment = $comment.$newline;
            }
            $comment = $comment .
                "Added by 2023_IHC_BH (row $row) on " . $currentDate . $prefix .
                ": " . $rowDataStr;

            //dump($rowData);
            echo "comment=$comment <br>";
            $transresRequest->setComment($comment);

            $msg = "Comment of the work request $requestID has been updated by 2023_IHC_BH (appended by row $row): ".$rowDataStr;
            $transresUtil->setEventLog($thisProject,$eventType,$msg);

            if( ($count % $batchSize) === 0 ) {
                $this->em->flush();
            }

        }//for

        $this->em->flush();
    }

    public function addNewFeeSchedules($request, $filename, $feeScheduleVersion, $startRaw=2, $endRaw=null) {
        set_time_limit(18000); //18000 seconds => 5 hours 3600sec=>1 hour
        ini_set('memory_limit', '7168M');

        $userSecUtil = $this->container->get('user_security_utility');
        $transresUtil = $this->container->get('transres_util');
        $logger = $this->container->get('logger');
        $em = $this->em;

        if( !$filename ) {
            $filename = 'new_fees_schedule_2026.xlsx';
        }

        $inputFileName = __DIR__ . "/" . $filename;

        echo "==================== Processing $filename =====================<br>";
        $logger->notice("==================== Processing $filename =====================");

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        echo "highestRow=".$highestRow."; highestColum=".$highestColumn."<br>";

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);

        $this->headerMapArr = $this->getHeaderMap($headers);

        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        $bundleName = "TranslationalResearchBundle";
        $className = "RequestCategoryTypeList";
        $classFullName = "App\\" . $bundleName . "\\Entity\\" . $className;
        $orderinlist = $userSecUtil->getMaxField($classFullName);
        echo "Create a new orderinlist=$orderinlist<br>";

        $internalPriceList = $this->em->getRepository(PriceTypeList::class)->findOneByName("Internal Pricing");
        if( !$internalPriceList ) {
            exit("Internal price list does not exist");
        }

        $ctpWorkQueue = $transresUtil->getWorkQueueObject("CTP Lab");

        $count = 0;

        $limitRow = $highestRow;
        if( $endRaw && $endRaw <= $highestRow ) {
            $limitRow = $endRaw;
        }

        if( $startRaw < 2 ) {
            $startRaw = 2; //minimum raw
        }

        echo "start Iteration from $startRaw to ".$limitRow."<br>";
        $logger->notice("start Iteration from $startRaw to ".$limitRow);

        //for each request in excel (start at row 2)
        for( $row = $startRaw; $row <= $limitRow; $row++ ) {

            $count++;

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $catalog = $this->getValueByHeaderName('Catalog', $rowData, $headers);
            $catalog = trim((string)$catalog);
            //$requestID = $requestID."0000000"; //test
            if( !$catalog ) {
                //echo "Skip: empty row<br>";
                continue;
            }
            echo "<br>" . $count . ": catalog=" . $catalog . "<br>";

            //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
            $feeSchedule = $em->getRepository(RequestCategoryTypeList::class)->findOneByProductId($catalog);
            if( $feeSchedule ) {
                echo "Skip: RequestCategoryTypeList already exists, found by Product ID=".$catalog."<br>";
                continue;
            }

            $name = $this->getValueByHeaderName('Name', $rowData, $headers);
            if( $name ) {
                echo "name=$name<br>";
            }
//            $feeSchedule = $em->getRepository(RequestCategoryTypeList::class)->findOneByName($name);
//            if( !$feeSchedule ) {
//                echo "Skip: RequestCategoryTypeList already exists, found by name=".$name."<br>";
//                continue;
//            }

            $feeOne = $this->getValueByHeaderName('Fee for one', $rowData, $headers);
            $feeOne = $this->toDecimal($feeOne);
            if( $feeOne ) {
                echo "feeOne=$feeOne<br>";
            }

            $feeAdd = $this->getValueByHeaderName('Fee per additional item', $rowData, $headers);
            $feeAdd = $this->toDecimal($feeAdd);
            if( $feeAdd ) {
                echo "feeAdd=$feeAdd<br>";
            }

            $feeOneInternal = $this->getValueByHeaderName('Fee for one (Internal Pricing)', $rowData, $headers);
            $feeOneInternal = $this->toDecimal($feeOneInternal);
            if( $feeOneInternal ) {
                echo "feeOneInternal=$feeOneInternal<br>";
            }

            $feeAddInternal = $this->getValueByHeaderName('Fee per additional item (Internal Pricing)', $rowData, $headers);
            $feeAddInternal = $this->toDecimal($feeAddInternal);
            if( $feeAddInternal ) {
                echo "feeAddInternal=$feeAddInternal<br>";
            }

            $unit = $this->getValueByHeaderName('Unit', $rowData, $headers);
            if( $unit ) {
                echo "unit=$unit<br>";
            }

            if( $catalog && $name ) {
                $feeScheduleEntity = new RequestCategoryTypeList($systemUser);

                $orderinlist = $orderinlist + 10;
                echo "Create a new orderinlist=$orderinlist<br>";

                $userSecUtil->setDefaultList( $feeScheduleEntity, $orderinlist, $systemUser, $name );

                $feeScheduleEntity->setType('default');

                //$feeScheduleEntity->setSection(); //Section
                $feeScheduleEntity->setProductId($catalog); //Product ID
                $feeScheduleEntity->setFeeUnit($unit);

                $feeScheduleEntity->setFeeScheduleVersion($feeScheduleVersion);

                if( $ctpWorkQueue ) {
                    $feeScheduleEntity->addWorkQueue($ctpWorkQueue);
                }

                $feeScheduleEntity->setFee($feeOne);
                $feeScheduleEntity->setFeeAdditionalItem($feeAdd);
                $feeScheduleEntity->setInitialQuantity(1);

                if( $feeOneInternal && $feeAddInternal ) {
                    $internalPriceObject = new Prices();
                    $internalPriceObject->setPriceList($internalPriceList);
                    $feeScheduleEntity->addPrice($internalPriceObject);
                    $this->em->persist($internalPriceObject);
                    $updateArr[] = "Created new internal specific price [$internalPriceList]";

                    if( $feeOneInternal ) {
                        $internalPriceObject->setFee($feeOneInternal);
                    }

                    if( $feeAddInternal ) {
                        $internalPriceObject->setFeeAdditionalItem($feeAddInternal);
                    }

                    $internalPriceObject->setInitialQuantity(1);
                } else {
                    echo "Don't update internal fees: feeOneInternal=[$feeOneInternal] feeAddInternal=[$feeAddInternal]";
                }

                //$this->em->persist($feeScheduleEntity);
                //$this->em->flush();
                //exit("added $name");
            }

        }//for

        return $count;
    }
}