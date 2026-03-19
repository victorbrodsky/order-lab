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

namespace App\FellAppBundle\Controller;

use App\FellAppBundle\Entity\FellowshipApplication;
use App\UserdirectoryBundle\Controller\OrderAbstractController;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


#[Route(path: '/')]
class FellAppRetrievalController extends OrderAbstractController
{

    // Remote Server API Endpoint
    // (4) "URL of the API endpoint hosted by the public tandem hub server tenant instance" -
    // set it by default to the value "https://view.online/fellowship-applications/download-application-data"
    #[Route(path: '/download-application-data/{hashkey}', name: 'fellapp_download_application_data', methods: ['GET'])]
    public function downloadApplicationDataAction( Request $request, $hashkey = null ) {
        // Remote Server: Receive API call and generate xlsx
        
        if( !$hashkey ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Hashkey is required'
            ], 400);
        }
        
        // Find FellowshipApplication by hashkey
        $em = $this->getDoctrine()->getManager();
        //$fellapp = $em->getRepository(FellowshipApplication::class)->findOneBy(['googleFormId' => $hashkey]);
        $fellapp = $em->getRepository(FellowshipApplication::class)->find(30);

        if( !$fellapp ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'FellowshipApplication not found for hashkey: ' . $hashkey
            ], 404);
        }
        
        // Generate xlsx file
        $xlsxData = $this->generateXlsxData($fellapp, $hashkey);
        
        // Return JSON response with xlsx data as base64
        return new JsonResponse([
            'success' => true,
            'hashkey' => $hashkey,
            'filename' => 'fellowship_application_' . $hashkey . '.xlsx',
            'xlsx_base64' => base64_encode($xlsxData)
        ]);
    }
    

    //http://127.0.0.1/fellowship-applications/retrieve-application-data/abc
    // Caller Server: Make API call to Remote Server
    #[Route(path: '/retrieve-application-data/{hashkey}', name: 'fellapp_retrieve_application_data', methods: ['GET'])]
    public function retrieveApplicationDataAction( Request $request, $hashkey = null ) {
        
        if( !$hashkey ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Hashkey is required'
            ], 400);
        }
        
        $expectedHashkey = "abc";
        
        // (6) Authenticate hashkey
        if( $hashkey !== $expectedHashkey ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid hashkey authentication'
            ], 401);
        }
        
        // (1) Make API call to Remote Server
        //$remoteUrl = 'https://view.online/fellowship-applications/download-application-data/' . $hashkey;
        $remoteUrl = 'https://view.online/fellowship-applications/download-application-data/' . $hashkey;

        try {
            //$client = HttpClient::create();
            $client = HttpClient::create([
                'verify_peer' => false,
                'verify_host' => false
            ]);
            $response = $client->request('GET', $remoteUrl);
            $statusCode = $response->getStatusCode();
            
            if( $statusCode !== 200 ) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Remote server returned error: ' . $statusCode
                ], 500);
            }
            
            // (5) Receive JSON from Remote Server
            $data = $response->toArray();
            
            if( !$data['success'] ) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Remote server error: ' . ($data['message'] ?? 'Unknown error')
                ], 500);
            }
            
            // (7) Decode xlsx data and store locally
            $xlsxData = base64_decode($data['xlsx_base64']);
            $filename = $data['filename'];
            
            // Store in order-lab\orderflex\public\Uploaded\fellapp\Spreadsheets
            $storagePath = $this->getParameter('kernel.project_dir') . '/public/Uploaded/fellapp/Spreadsheets';
            
            // Create directory if it doesn't exist
            if( !is_dir($storagePath) ) {
                mkdir($storagePath, 0777, true);
            }
            
            $filepath = $storagePath . '/' . $filename;
            
            // Save file locally
            file_put_contents($filepath, $xlsxData);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Application data retrieved and stored successfully',
                'hashkey' => $hashkey,
                'filename' => $filename,
                'filepath' => $filepath,
                'remote_response' => $data
            ]);
            
        } catch( \Exception $e ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving application data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    
    /**
     * Generate xlsx file from FellowshipApplication data - HORIZONTAL LAYOUT
     */
    private function generateXlsxData( FellowshipApplication $fellapp, $hashkey ) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $userSecUtil = $this->container->get('user_security_utility');
        $instanceId = $userSecUtil->getSiteSettingParameter('instanceId');

        // Get related entities
        $user = $fellapp->getUser();
        $trainings = $fellapp->getTrainings();
        $references = $fellapp->getReferences();
        $locations = $fellapp->getLocations();
        $examinations = $fellapp->getExaminations();
        $stateLicenses = $fellapp->getStateLicenses();
        $boardCerts = $fellapp->getBoardCertifications();
        $citizenships = $fellapp->getCitizenships();
        $avatars = $fellapp->getAvatars();
        $cvs = $fellapp->getCvs();
        $coverLetters = $fellapp->getCoverLetters();
        $reprimandDocs = $fellapp->getReprimandDocuments();
        $lawsuitDocs = $fellapp->getLawsuitDocuments();

        // Define all headers in the exact order requested
        $headers = [
            'ID', 'timestamp', 'lastName', 'firstName', 'middleName',
            'uploadedPhotoUrl', 'uploadedCVUrl', 'uploadedCoverLetterUrl', 'uploadedUSMLEScoresUrl',
            'fellowshipType', 'trainingPeriodStart', 'trainingPeriodEnd',
            'otherNames', 'presentAddressStreet1', 'presentAddressStreet2', 'presentAddressCity',
            'presentAddressState', 'presentAddressZip', 'presentAddressCountry', 'samePAddress',
            'permanentAddressStreet1', 'permanentAddressStreet2', 'permanentAddressCity',
            'permanentAddressState', 'permanentAddressZip', 'permanentAddressCountry',
            'telephoneHome', 'telephoneWork', 'telephoneMobile', 'telephoneFax', 'email',
            'dateOfBirth', 'citizenshipCountry', 'visaStatus',
            'undergraduateSchoolStart', 'undergraduateSchoolEnd', 'undergraduateSchoolName',
            'undergraduateSchoolCity', 'undergraduateSchoolState', 'undergraduateSchoolCountry',
            'undergraduateSchoolMajor', 'undergraduateSchoolDegree',
            'graduateSchoolStart', 'graduateSchoolEnd', 'graduateSchoolName',
            'graduateSchoolCity', 'graduateSchoolState', 'graduateSchoolCountry',
            'graduateSchoolMajor', 'graduateSchoolDegree',
            'medicalSchoolStart', 'medicalSchoolEnd', 'medicalSchoolName',
            'medicalSchoolCity', 'medicalSchoolState', 'medicalSchoolCountry',
            'medicalSchoolMajor', 'medicalSchoolDegree',
            'residencyStart', 'residencyEnd', 'residencyName', 'residencyCity',
            'residencyState', 'residencyCountry', 'residencyArea',
            'gme1Start', 'gme1End', 'gme1Name', 'gme1City', 'gme1State', 'gme1Country', 'gme1Area',
            'gme2Start', 'gme2End', 'gme2Name', 'gme2City', 'gme2State', 'gme2Country', 'gme2Area',
            'otherExperience1Start', 'otherExperience1End', 'otherExperience1Name', 'otherExperience1Description',
            'otherExperience1Institution', 'otherExperience1City', 'otherExperience1State', 'otherExperience1Country',
            'otherExperience2Start', 'otherExperience2End', 'otherExperience2Name', 'otherExperience2Description',
            'otherExperience2Institution', 'otherExperience2City', 'otherExperience2State', 'otherExperience2Country',
            'otherExperience3Start', 'otherExperience3End', 'otherExperience3Name', 'otherExperience3Description',
            'otherExperience3Institution', 'otherExperience3City', 'otherExperience3State', 'otherExperience3Country',
            'USMLEStep1DatePassed', 'USMLEStep1Score', 'USMLEStep1Percentile',
            'USMLEStep2CKDatePassed', 'USMLEStep2CKScore', 'USMLEStep2CKPercentile',
            'USMLEStep2CSDatePassed', 'USMLEStep2CSScore', 'USMLEStep2CSPercentile',
            'USMLEStep3DatePassed', 'USMLEStep3Score', 'USMLEStep3Percentile',
            'ECFMGCertificate', 'ECFMGCertificateNumber', 'ECFMGCertificateDate',
            'COMLEXLevel1DatePassed', 'COMLEXLevel1Score', 'COMLEXLevel1Percentile',
            'COMLEXLevel2DatePassed', 'COMLEXLevel2Score', 'COMLEXLevel2Percentile',
            'COMLEXLevel3DatePassed', 'COMLEXLevel3Score', 'COMLEXLevel3Percentile',
            'medicalLicensure1Country', 'medicalLicensure1State', 'medicalLicensure1DateIssued',
            'medicalLicensure1Number', 'medicalLicensure1Active',
            'medicalLicensure2Country', 'medicalLicensure2State', 'medicalLicensure2DateIssued',
            'medicalLicensure2Number', 'medicalLicensure2Active',
            'suspendedLicensure', 'uploadedReprimandExplanationUrl', 'legalSuit', 'uploadedLegalExplanationUrl',
            'boardCertification1Board', 'boardCertification1Area', 'boardCertification1Date',
            'boardCertification2Board', 'boardCertification2Area', 'boardCertification2Date',
            'boardCertification3Board', 'boardCertification3Area', 'boardCertification3Date',
            'recommendation1FirstName', 'recommendation1LastName', 'recommendation1Degree', 'recommendation1Phone',
            'recommendation1Title', 'recommendation1Institution', 'recommendation1Email',
            'recommendation1AddressStreet1', 'recommendation1AddressStreet2', 'recommendation1AddressCity',
            'recommendation1AddressState', 'recommendation1AddressZip', 'recommendation1AddressCountry',
            'recommendation2FirstName', 'recommendation2LastName', 'recommendation2Degree', 'recommendation2Phone',
            'recommendation2Title', 'recommendation2Institution', 'recommendation2Email',
            'recommendation2AddressStreet1', 'recommendation2AddressStreet2', 'recommendation2AddressCity',
            'recommendation2AddressState', 'recommendation2AddressZip', 'recommendation2AddressCountry',
            'recommendation3FirstName', 'recommendation3LastName', 'recommendation3Degree', 'recommendation3Phone',
            'recommendation3Title', 'recommendation3Institution', 'recommendation3Email',
            'recommendation3AddressStreet1', 'recommendation3AddressStreet2', 'recommendation3AddressCity',
            'recommendation3AddressState', 'recommendation3AddressZip', 'recommendation3AddressCountry',
            'recommendation4FirstName', 'recommendation4LastName', 'recommendation4Degree', 'recommendation4Phone',
            'recommendation4Title', 'recommendation4Institution', 'recommendation4Email',
            'recommendation4AddressStreet1', 'recommendation4AddressStreet2', 'recommendation4AddressCity',
            'recommendation4AddressState', 'recommendation4AddressZip', 'recommendation4AddressCountry',
            'honors', 'publications', 'memberships', 'signatureName', 'signatureDate'
        ];

        // Set headers in row 1 (horizontal layout)
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
            $col++;
        }

        // Prepare data array
        $data = [];

        // Basic fields
        $currentDateTime = new \DateTime();
        $data['ID'] = $fellapp->getId() . ($instanceId ? "_" . $instanceId : "") . "_" . $currentDateTime->format('Y-m-d-H-i-s');
        $data['timestamp'] = $fellapp->getTimestamp() ? $fellapp->getTimestamp()->format('Y-m-d H:i:s') : '';
        $data['lastName'] = $user ? $user->getLastName() : '';
        $data['firstName'] = $user ? $user->getFirstName() : '';
        $data['middleName'] = $user ? $user->getMiddleName() : '';

        // Document URLs
        $data['uploadedPhotoUrl'] = $this->getFirstDocumentUrl($avatars);
        $data['uploadedCVUrl'] = $this->getFirstDocumentUrl($cvs);
        $data['uploadedCoverLetterUrl'] = $this->getFirstDocumentUrl($coverLetters);
        $data['uploadedUSMLEScoresUrl'] = '';
        $data['uploadedReprimandExplanationUrl'] = $this->getFirstDocumentUrl($reprimandDocs);
        $data['uploadedLegalExplanationUrl'] = $this->getFirstDocumentUrl($lawsuitDocs);

        // Fellowship Type and Training Period
        $data['fellowshipType'] = $fellapp->getFellowshipSubspecialty() ? $fellapp->getFellowshipSubspecialty()->getName() : '';
        if (!$data['fellowshipType'] && $fellapp->getGlobalFellowshipSpecialty()) {
            $data['fellowshipType'] = $fellapp->getGlobalFellowshipSpecialty()->getName();
        }
        $data['trainingPeriodStart'] = $fellapp->getStartDate() ? $fellapp->getStartDate()->format('Y-m-d') : '';
        $data['trainingPeriodEnd'] = $fellapp->getEndDate() ? $fellapp->getEndDate()->format('Y-m-d') : '';

        $data['otherNames'] = '';

        // Present Address
        $presentLocation = $locations->first();
        $data['presentAddressStreet1'] = $presentLocation ? $presentLocation->getStreet1() : '';
        $data['presentAddressStreet2'] = $presentLocation ? $presentLocation->getStreet2() : '';
        $data['presentAddressCity'] = $presentLocation ? $presentLocation->getCity() : '';
        $data['presentAddressState'] = $presentLocation && $presentLocation->getState() ? $presentLocation->getState()->getName() : '';
        $data['presentAddressZip'] = $presentLocation ? $presentLocation->getZip() : '';
        $data['presentAddressCountry'] = $presentLocation && $presentLocation->getCountry() ? $presentLocation->getCountry()->getName() : '';
        $data['samePAddress'] = '';

        // Permanent Address
        $permLocation = null;
        if ($locations->count() > 1) {
            $permLocation = $locations->get(1);
        }
        $data['permanentAddressStreet1'] = $permLocation ? $permLocation->getStreet1() : '';
        $data['permanentAddressStreet2'] = $permLocation ? $permLocation->getStreet2() : '';
        $data['permanentAddressCity'] = $permLocation ? $permLocation->getCity() : '';
        $data['permanentAddressState'] = $permLocation && $permLocation->getState() ? $permLocation->getState()->getName() : '';
        $data['permanentAddressZip'] = $permLocation ? $permLocation->getZip() : '';
        $data['permanentAddressCountry'] = $permLocation && $permLocation->getCountry() ? $permLocation->getCountry()->getName() : '';

        $data['telephoneHome'] = '';
        $data['telephoneWork'] = '';
        $data['telephoneMobile'] = '';
        $data['telephoneFax'] = '';
        $data['email'] = $user ? $user->getEmail() : '';

        $data['dateOfBirth'] = '';
        if ($user && $user->getDob()) {
            $data['dateOfBirth'] = $user->getDob()->format('Y-m-d');
        }

        $citizenship = $citizenships->first();
        $data['citizenshipCountry'] = $citizenship && $citizenship->getCountry() ? $citizenship->getCountry()->getName() : '';
        $data['visaStatus'] = $citizenship ? $citizenship->getVisa() : '';

        // Initialize all training fields
        $trainingFields = [
            'undergraduateSchoolStart', 'undergraduateSchoolEnd', 'undergraduateSchoolName',
            'undergraduateSchoolCity', 'undergraduateSchoolState', 'undergraduateSchoolCountry',
            'undergraduateSchoolMajor', 'undergraduateSchoolDegree',
            'graduateSchoolStart', 'graduateSchoolEnd', 'graduateSchoolName',
            'graduateSchoolCity', 'graduateSchoolState', 'graduateSchoolCountry',
            'graduateSchoolMajor', 'graduateSchoolDegree',
            'medicalSchoolStart', 'medicalSchoolEnd', 'medicalSchoolName',
            'medicalSchoolCity', 'medicalSchoolState', 'medicalSchoolCountry',
            'medicalSchoolMajor', 'medicalSchoolDegree',
            'residencyStart', 'residencyEnd', 'residencyName', 'residencyCity',
            'residencyState', 'residencyCountry', 'residencyArea',
            'gme1Start', 'gme1End', 'gme1Name', 'gme1City', 'gme1State', 'gme1Country', 'gme1Area',
            'gme2Start', 'gme2End', 'gme2Name', 'gme2City', 'gme2State', 'gme2Country', 'gme2Area',
            'otherExperience1Start', 'otherExperience1End', 'otherExperience1Name', 'otherExperience1Description',
            'otherExperience1Institution', 'otherExperience1City', 'otherExperience1State', 'otherExperience1Country',
            'otherExperience2Start', 'otherExperience2End', 'otherExperience2Name', 'otherExperience2Description',
            'otherExperience2Institution', 'otherExperience2City', 'otherExperience2State', 'otherExperience2Country',
            'otherExperience3Start', 'otherExperience3End', 'otherExperience3Name', 'otherExperience3Description',
            'otherExperience3Institution', 'otherExperience3City', 'otherExperience3State', 'otherExperience3Country'
        ];
        foreach ($trainingFields as $field) {
            $data[$field] = '';
        }

        // Process trainings
        foreach ($trainings as $training) {
            $trainingType = $training->getTrainingType() ? $training->getTrainingType()->getName() : '';
            $startDate = $training->getStartDate() ? $training->getStartDate()->format('Y-m-d') : '';
            $endDate = $training->getCompletionDate() ? $training->getCompletionDate()->format('Y-m-d') : '';
            $institution = $training->getInstitution() ? $training->getInstitution()->getName() : '';
            $city = '';
            $state = '';
            $country = '';

            if ($training->getLocation()) {
                $city = $training->getLocation()->getCity();
                $state = $training->getLocation()->getState() ? $training->getLocation()->getState()->getName() : '';
                $country = $training->getLocation()->getCountry() ? $training->getLocation()->getCountry()->getName() : '';
            }

            if (stripos($trainingType, 'undergraduate') !== false && $data['undergraduateSchoolName'] == '') {
                $data['undergraduateSchoolStart'] = $startDate;
                $data['undergraduateSchoolEnd'] = $endDate;
                $data['undergraduateSchoolName'] = $institution;
                $data['undergraduateSchoolCity'] = $city;
                $data['undergraduateSchoolState'] = $state;
                $data['undergraduateSchoolCountry'] = $country;
                $data['undergraduateSchoolMajor'] = $training->getDepartment() ?? '';
                $data['undergraduateSchoolDegree'] = $training->getDegree() ? $training->getDegree()->getName() : '';
            } elseif (stripos($trainingType, 'graduate') !== false && $data['graduateSchoolName'] == '') {
                $data['graduateSchoolStart'] = $startDate;
                $data['graduateSchoolEnd'] = $endDate;
                $data['graduateSchoolName'] = $institution;
                $data['graduateSchoolCity'] = $city;
                $data['graduateSchoolState'] = $state;
                $data['graduateSchoolCountry'] = $country;
                $data['graduateSchoolMajor'] = $training->getDepartment() ?? '';
                $data['graduateSchoolDegree'] = $training->getDegree() ? $training->getDegree()->getName() : '';
            } elseif (stripos($trainingType, 'medical') !== false && $data['medicalSchoolName'] == '') {
                $data['medicalSchoolStart'] = $startDate;
                $data['medicalSchoolEnd'] = $endDate;
                $data['medicalSchoolName'] = $institution;
                $data['medicalSchoolCity'] = $city;
                $data['medicalSchoolState'] = $state;
                $data['medicalSchoolCountry'] = $country;
                $data['medicalSchoolMajor'] = $training->getDepartment() ?? '';
                $data['medicalSchoolDegree'] = $training->getDegree() ? $training->getDegree()->getName() : '';
            } elseif (stripos($trainingType, 'residency') !== false && $data['residencyName'] == '') {
                $data['residencyStart'] = $startDate;
                $data['residencyEnd'] = $endDate;
                $data['residencyName'] = $institution;
                $data['residencyCity'] = $city;
                $data['residencyState'] = $state;
                $data['residencyCountry'] = $country;
                $data['residencyArea'] = $training->getDepartment() ?? '';
            } elseif (stripos($trainingType, 'gme') !== false || stripos($trainingType, 'fellowship') !== false) {
                if ($data['gme1Name'] == '') {
                    $data['gme1Start'] = $startDate;
                    $data['gme1End'] = $endDate;
                    $data['gme1Name'] = $institution;
                    $data['gme1City'] = $city;
                    $data['gme1State'] = $state;
                    $data['gme1Country'] = $country;
                    $data['gme1Area'] = $training->getDepartment() ?? '';
                } elseif ($data['gme2Name'] == '') {
                    $data['gme2Start'] = $startDate;
                    $data['gme2End'] = $endDate;
                    $data['gme2Name'] = $institution;
                    $data['gme2City'] = $city;
                    $data['gme2State'] = $state;
                    $data['gme2Country'] = $country;
                    $data['gme2Area'] = $training->getDepartment() ?? '';
                }
            } else {
                if ($data['otherExperience1Name'] == '') {
                    $data['otherExperience1Start'] = $startDate;
                    $data['otherExperience1End'] = $endDate;
                    $data['otherExperience1Name'] = $institution;
                    $data['otherExperience1Description'] = $training->getDescription() ?? '';
                    $data['otherExperience1Institution'] = $institution;
                    $data['otherExperience1City'] = $city;
                    $data['otherExperience1State'] = $state;
                    $data['otherExperience1Country'] = $country;
                } elseif ($data['otherExperience2Name'] == '') {
                    $data['otherExperience2Start'] = $startDate;
                    $data['otherExperience2End'] = $endDate;
                    $data['otherExperience2Name'] = $institution;
                    $data['otherExperience2Description'] = $training->getDescription() ?? '';
                    $data['otherExperience2Institution'] = $institution;
                    $data['otherExperience2City'] = $city;
                    $data['otherExperience2State'] = $state;
                    $data['otherExperience2Country'] = $country;
                } elseif ($data['otherExperience3Name'] == '') {
                    $data['otherExperience3Start'] = $startDate;
                    $data['otherExperience3End'] = $endDate;
                    $data['otherExperience3Name'] = $institution;
                    $data['otherExperience3Description'] = $training->getDescription() ?? '';
                    $data['otherExperience3Institution'] = $institution;
                    $data['otherExperience3City'] = $city;
                    $data['otherExperience3State'] = $state;
                    $data['otherExperience3Country'] = $country;
                }
            }
        }

        // Initialize examination fields
        $examFields = [
            'USMLEStep1DatePassed', 'USMLEStep1Score', 'USMLEStep1Percentile',
            'USMLEStep2CKDatePassed', 'USMLEStep2CKScore', 'USMLEStep2CKPercentile',
            'USMLEStep2CSDatePassed', 'USMLEStep2CSScore', 'USMLEStep2CSPercentile',
            'USMLEStep3DatePassed', 'USMLEStep3Score', 'USMLEStep3Percentile',
            'ECFMGCertificate', 'ECFMGCertificateNumber', 'ECFMGCertificateDate',
            'COMLEXLevel1DatePassed', 'COMLEXLevel1Score', 'COMLEXLevel1Percentile',
            'COMLEXLevel2DatePassed', 'COMLEXLevel2Score', 'COMLEXLevel2Percentile',
            'COMLEXLevel3DatePassed', 'COMLEXLevel3Score', 'COMLEXLevel3Percentile'
        ];
        foreach ($examFields as $field) {
            $data[$field] = '';
        }

        // Process examinations
        foreach ($examinations as $exam) {
            $examType = $exam->getExaminationType() ? $exam->getExaminationType()->getName() : '';
            $datePassed = $exam->getCompletionDate() ? $exam->getCompletionDate()->format('Y-m-d') : '';
            $score = $exam->getScore() ?? '';

            if (stripos($examType, 'USMLE Step 1') !== false) {
                $data['USMLEStep1DatePassed'] = $datePassed;
                $data['USMLEStep1Score'] = $score;
            } elseif (stripos($examType, 'USMLE Step 2 CK') !== false) {
                $data['USMLEStep2CKDatePassed'] = $datePassed;
                $data['USMLEStep2CKScore'] = $score;
            } elseif (stripos($examType, 'USMLE Step 2 CS') !== false) {
                $data['USMLEStep2CSDatePassed'] = $datePassed;
                $data['USMLEStep2CSScore'] = $score;
            } elseif (stripos($examType, 'USMLE Step 3') !== false) {
                $data['USMLEStep3DatePassed'] = $datePassed;
                $data['USMLEStep3Score'] = $score;
            } elseif (stripos($examType, 'ECFMG') !== false) {
                $data['ECFMGCertificate'] = 'Yes';
                $data['ECFMGCertificateNumber'] = $exam->getCertificateNum() ?? '';
                $data['ECFMGCertificateDate'] = $datePassed;
            } elseif (stripos($examType, 'COMLEX Level 1') !== false) {
                $data['COMLEXLevel1DatePassed'] = $datePassed;
                $data['COMLEXLevel1Score'] = $score;
            } elseif (stripos($examType, 'COMLEX Level 2') !== false) {
                $data['COMLEXLevel2DatePassed'] = $datePassed;
                $data['COMLEXLevel2Score'] = $score;
            } elseif (stripos($examType, 'COMLEX Level 3') !== false) {
                $data['COMLEXLevel3DatePassed'] = $datePassed;
                $data['COMLEXLevel3Score'] = $score;
            }
        }

        // Initialize licensure fields
        $licenseFields = [
            'medicalLicensure1Country', 'medicalLicensure1State', 'medicalLicensure1DateIssued',
            'medicalLicensure1Number', 'medicalLicensure1Active',
            'medicalLicensure2Country', 'medicalLicensure2State', 'medicalLicensure2DateIssued',
            'medicalLicensure2Number', 'medicalLicensure2Active'
        ];
        foreach ($licenseFields as $field) {
            $data[$field] = '';
        }

        // Process state licenses
        $licenseIndex = 0;
        foreach ($stateLicenses as $license) {
            $country = $license->getCountry() ? $license->getCountry()->getName() : '';
            $state = $license->getState() ? $license->getState()->getName() : '';
            $dateIssued = $license->getLicenseIssuedDate() ? $license->getLicenseIssuedDate()->format('Y-m-d') : '';
            $number = $license->getLicenseNumber() ?? '';
            $active = $license->getLicenseActive() ? 'Yes' : 'No';

            if ($licenseIndex == 0) {
                $data['medicalLicensure1Country'] = $country;
                $data['medicalLicensure1State'] = $state;
                $data['medicalLicensure1DateIssued'] = $dateIssued;
                $data['medicalLicensure1Number'] = $number;
                $data['medicalLicensure1Active'] = $active;
            } elseif ($licenseIndex == 1) {
                $data['medicalLicensure2Country'] = $country;
                $data['medicalLicensure2State'] = $state;
                $data['medicalLicensure2DateIssued'] = $dateIssued;
                $data['medicalLicensure2Number'] = $number;
                $data['medicalLicensure2Active'] = $active;
            }
            $licenseIndex++;
        }

        $data['suspendedLicensure'] = '';
        $data['legalSuit'] = $fellapp->getLawsuit() ?? '';

        // Initialize board certification fields
        $certFields = [
            'boardCertification1Board', 'boardCertification1Area', 'boardCertification1Date',
            'boardCertification2Board', 'boardCertification2Area', 'boardCertification2Date',
            'boardCertification3Board', 'boardCertification3Area', 'boardCertification3Date'
        ];
        foreach ($certFields as $field) {
            $data[$field] = '';
        }

        // Process board certifications
        $certIndex = 0;
        foreach ($boardCerts as $cert) {
            $board = $cert->getBoard() ? $cert->getBoard()->getName() : '';
            $area = $cert->getSpecialty() ? $cert->getSpecialty()->getName() : '';
            $date = $cert->getIssueDate() ? $cert->getIssueDate()->format('Y-m-d') : '';

            if ($certIndex == 0) {
                $data['boardCertification1Board'] = $board;
                $data['boardCertification1Area'] = $area;
                $data['boardCertification1Date'] = $date;
            } elseif ($certIndex == 1) {
                $data['boardCertification2Board'] = $board;
                $data['boardCertification2Area'] = $area;
                $data['boardCertification2Date'] = $date;
            } elseif ($certIndex == 2) {
                $data['boardCertification3Board'] = $board;
                $data['boardCertification3Area'] = $area;
                $data['boardCertification3Date'] = $date;
            }
            $certIndex++;
        }

        // Initialize recommendation fields
        for ($i = 1; $i <= 4; $i++) {
            $data['recommendation' . $i . 'FirstName'] = '';
            $data['recommendation' . $i . 'LastName'] = '';
            $data['recommendation' . $i . 'Degree'] = '';
            $data['recommendation' . $i . 'Phone'] = '';
            $data['recommendation' . $i . 'Title'] = '';
            $data['recommendation' . $i . 'Institution'] = '';
            $data['recommendation' . $i . 'Email'] = '';
            $data['recommendation' . $i . 'AddressStreet1'] = '';
            $data['recommendation' . $i . 'AddressStreet2'] = '';
            $data['recommendation' . $i . 'AddressCity'] = '';
            $data['recommendation' . $i . 'AddressState'] = '';
            $data['recommendation' . $i . 'AddressZip'] = '';
            $data['recommendation' . $i . 'AddressCountry'] = '';
        }

        // Process references/recommendations
        $refIndex = 0;
        foreach ($references as $ref) {
            $refNum = $refIndex + 1;
            if ($refNum > 4) break;

            $data['recommendation' . $refNum . 'FirstName'] = $ref->getFirstName() ?? '';
            $data['recommendation' . $refNum . 'LastName'] = $ref->getLastName() ?? '';
            $data['recommendation' . $refNum . 'Degree'] = $ref->getDegree() ?? '';
            $data['recommendation' . $refNum . 'Phone'] = $ref->getPhone() ?? '';
            $data['recommendation' . $refNum . 'Title'] = $ref->getTitle() ?? '';
            $data['recommendation' . $refNum . 'Institution'] = $ref->getInstitution() ?? '';
            $data['recommendation' . $refNum . 'Email'] = $ref->getEmail() ?? '';

            $refLoc = $ref->getLocation();
            if ($refLoc) {
                $data['recommendation' . $refNum . 'AddressStreet1'] = $refLoc->getStreet1() ?? '';
                $data['recommendation' . $refNum . 'AddressStreet2'] = $refLoc->getStreet2() ?? '';
                $data['recommendation' . $refNum . 'AddressCity'] = $refLoc->getCity() ?? '';
                $data['recommendation' . $refNum . 'AddressState'] = $refLoc->getState() ? $refLoc->getState()->getName() : '';
                $data['recommendation' . $refNum . 'AddressZip'] = $refLoc->getZip() ?? '';
                $data['recommendation' . $refNum . 'AddressCountry'] = $refLoc->getCountry() ? $refLoc->getCountry()->getName() : '';
            }

            $refIndex++;
        }

        // Final fields
        $data['honors'] = $fellapp->getHonors() ?? '';
        $data['publications'] = $fellapp->getPublications() ?? '';
        $data['memberships'] = $fellapp->getMemberships() ?? '';
        $data['signatureName'] = $fellapp->getSignatureName() ?? '';
        $data['signatureDate'] = $fellapp->getSignatureDate() ? $fellapp->getSignatureDate()->format('Y-m-d H:i:s') : '';

        // Set data in row 2 (horizontal layout)
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 2, $data[$header] ?? '');
            $col++;
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Write to string
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $xlsxData = ob_get_clean();

        return $xlsxData;
    }


    /**
     * Helper function to get URL of first document
     */
    private function getFirstDocumentUrl($documents) {
        if (!$documents || $documents->count() == 0) {
            return '';
        }
        $doc = $documents->first();
        if ($doc) {
            return $doc->getAbsoluteUploadFullPath() ?? '';
        }
        return '';
    }
    
    
    //(6) "URL of the recommendation letter upload page hosted by the public tandem hub server tenant instance (to append hash ID)" -
    // set it by default to the value "https://view.online/fellowship-applications/submit-a-letter-of-recommendation"

}
