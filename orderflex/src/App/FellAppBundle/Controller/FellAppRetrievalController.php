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

use App\UserdirectoryBundle\Entity\Document;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\IOFactory;


//API key $hashkey is generated on Caller and Remote servers must be the same in order for Remote server data back.
//Use Hash-based message authentication code (or HMAC)
//HMAC is used to authenticate API calls between Caller and Remote servers using a shared secret key
//$userSecUtil = $this->container->get('user_security_utility');
//$secretKey = $userSecUtil->getSiteSettingParameter('secretKey');
//$hash = hash_hmac('sha256', $hashkey . $timestamp, $secretKey);

#[Route(path: '/')]
class FellAppRetrievalController extends OrderAbstractController
{
    //http://127.0.0.1/fellowship-applications/retrieve-application-data
    // Caller Server: Make API call to Remote Server
    #[Route(path: '/retrieve-application-data', name: 'fellapp_retrieve_application_data', methods: ['GET'])]
    public function retrieveApplicationDataAction( Request $request ) {
        
        // Get secret key for HMAC authentication
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');
        
        if( !$secretKey ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Secret key not configured'
            ], 500);
        }
        
        // Generate HMAC for authentication (include timestamp to prevent replay attacks)
        $timestamp = time();
        $hmac = hash_hmac('sha256', 'fellapp-api:' . $timestamp, $secretKey);
        $logger->notice('retrieveApplicationDataAction: $hmac='.$hmac);
        $logger->notice('retrieveApplicationDataAction: $timestamp='.$timestamp);

        // (1) Make API call to Remote Server
        // Get min_id from request or use 0 as default (get all new applications)
        $minId = $request->query->get('min_id', 0);
        $remoteUrl = 'https://view.online/fellowship-applications/download-application-data?min_id=' . $minId;

        try {
            //$client = HttpClient::create();
            $client = HttpClient::create([
                'verify_peer' => false,
                'verify_host' => false
            ]);
            
            // Send HMAC authentication headers
            $response = $client->request('GET', $remoteUrl, [
                'headers' => [
                    'X-HMAC' => $hmac,
                    'X-Timestamp' => $timestamp
                ]
            ]);
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
            
            // Save file locally - COMMENTED OUT: Do not save file locally, just show records
            file_put_contents($filepath, $xlsxData);

            if(1) {
                //dump($response['remote_response']);
                //dump($xlsxData);

                //Use populateSpreadsheet
                //$this->populateSpreadsheetFromFilename($filepath);
                $this->xlsxFileParser($filepath);

                exit('retrieveApplicationDataAction');

                //$xlsxBase64 = $response['remote_response']['xlsx_base64'];
                $xlsxBase64 = $xlsxData;//['remote_response'];//['xlsx_base64'];
                $this->previewXlsx($response);

// 1. Decode Base64 → binary XLSX content
                $binaryXlsx = base64_decode($xlsxBase64);
// 2. Load spreadsheet from string (no temp file needed)
                // Load from memory stream
                $temp = fopen('php://memory', 'r+');
                fwrite($temp, $binaryXlsx);
                rewind($temp);
                $reader = new XlsxReader();
                $spreadsheet = $reader->load($temp);
                $data = $spreadsheet->getActiveSheet()->toArray();
                //dump($data);
                //exit('retrieveApplicationDataAction');
// 3. Get active sheet
                //$sheet = $spreadsheet->getActiveSheet();
// 4. Convert to array
                //$data = $sheet->toArray();
                foreach ($data as $row) {
                    echo "<tr>";
                    foreach ($row as $cell) {
                        echo "<td>" . htmlspecialchars($cell) . "</td>";
                    }
                    echo "</tr>";
                }

                echo "</table>";
                exit('retrieveApplicationDataAction');
            }

            //use the HASH values for each specialty on Caller and Remote servers
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Application data retrieved and stored successfully',
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
    public function populateSpreadsheetFromFilename( $filepath ) {
        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

        //$document = new Document(); //dummy document
        //$document->getServerPath(); //'Uploaded/fellapp/Spreadsheets/Pathology Fellowships Application Form (Responses).xlsx';

        //populateSpreadsheet( $document, $datafile=null, $deleteSourceRow=false, $testing=false )
        //$fellappImportPopulateUtil->populateSpreadsheet($document,$datafile=null,$deleteSourceRow=false,$testing=false);
    }
    public function xlsxFileParser( $xlsxFile ) {
        // Load spreadsheet
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($xlsxFile);

        // Remove temp file
        //unlink($xlsxFile);

        // Convert to array
        $rows = $spreadsheet->getActiveSheet()->toArray();
        // Dump or loop
        //dump($rows);

        $header = $rows[0];
        $headerLen = count($header);
        echo "header count=".$headerLen."<br>";
        array_shift($rows);   // removes row 0

        foreach($rows as $row) {
            //dump($row);
            //for( $i = 1; $i <= $headerLen; $i++ ) {
                //echo "The number is: " . $i . "<br>";
                //$key = array_search("ID", $header);
                //$value = $row[$key];
                //$keyName = $headerLen[$i];
                //$value = $row[$i];
                //echo $keyName.": value=$value <br>";

            $value = $this->getRowValue('ID',$row,$header);
            //echo "ID value=$value <br>";

            $originalAppId = $this->getRowValue('originalAppId',$row,$header);
            //echo "originalAppId=$originalAppId <br>";
            //}
        }
        die;
    }
    public function getRowValue( $keyName, $row, $header ) {
        $key = array_search($keyName, $header);
        $value = $row[$key];
        echo "$keyName=$value <br>";
        return $value;
    }
    public function previewXlsx($response)
    {
        $data = $response->toArray();
        $xlsxBase64 = $data['remote_response']['xlsx_base64'];
        $binaryXlsx = base64_decode($xlsxBase64);

        // Load from memory stream
        $temp = fopen('php://memory', 'r+');
        fwrite($temp, $binaryXlsx);
        rewind($temp);

        $reader = new XlsxReader();
        $spreadsheet = $reader->load($temp);

        $data = $spreadsheet->getActiveSheet()->toArray();

        // Build HTML manually
        $html = "<html><body><table border='1' cellpadding='5'>";

        foreach ($data as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= "<td>" . htmlspecialchars($cell) . "</td>";
            }
            $html .= "</tr>";
        }

        $html .= "</table></body></html>";
        dump($html);
        exit('111');

        return new Response($html);

    }


    // Remote Server API Endpoint
    // (4) "URL of the API endpoint hosted by the public tandem hub server tenant instance" -
    // set it by default to the value "https://view.online/fellowship-applications/download-application-data"
    #[Route(path: '/download-application-data', name: 'fellapp_download_application_data', methods: ['GET'])]
    public function downloadApplicationDataAction( Request $request ) {
        $logger = $this->container->get('logger');
        // Remote Server: Receive API call and generate xlsx

        // Verify HMAC authentication from headers
        $hmacHeader = $request->headers->get('X-HMAC');
        $timestampHeader = $request->headers->get('X-Timestamp');
        $logger->notice('downloadApplicationDataAction: $hmacHeader='.$hmacHeader);
        $logger->notice('downloadApplicationDataAction: $timestampHeader='.$timestampHeader);

        if( !$hmacHeader || !$timestampHeader ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'HMAC authentication headers required'
            ], 401);
        }

        // Get secret key for HMAC verification
        $userSecUtil = $this->container->get('user_security_utility');
        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');

        if( !$secretKey ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Secret key not configured'
            ], 500);
        }

        // Verify HMAC (use hash_equals for constant-time comparison)
        $expectedHmac = hash_hmac('sha256', 'fellapp-api:' . $timestampHeader, $secretKey);

        if( !hash_equals($expectedHmac, $hmacHeader) ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid HMAC authentication'
            ], 401);
        }

        // Optional: Check timestamp to prevent replay attacks (e.g., allow 5 minute window)
        $currentTime = time();
        $requestTime = intval($timestampHeader);
        $timeWindow = 300; // 5 minutes

        if( abs($currentTime - $requestTime) > $timeWindow ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Request timestamp expired'
            ], 401);
        }

        // Find FellowshipApplications with ID > min_id
        $em = $this->getDoctrine()->getManager();
        $minId = $request->query->get('min_id', 0);
        
        $fellapps = $em->getRepository(FellowshipApplication::class)->createQueryBuilder('f')
            ->where('f.id > :minId')
            ->setParameter('minId', $minId)
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult();
        
        if( empty($fellapps) ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No new FellowshipApplications found with ID > ' . $minId
            ], 404);
        }

        // Generate xlsx file with all new applications
        $xlsxData = $this->generateXlsxData($fellapps);

        $filename = 'fellowship_applications_min_id_' . $minId . '_' . date('Y-m-d-H-i-s') . '.xlsx';

        // Return JSON response with xlsx data as base64
        return new JsonResponse([
            'success' => true,
            'filename' => $filename,
            'xlsx_base64' => base64_encode($xlsxData)
        ]);
    }

    /**
     * Generate xlsx file from FellowshipApplication data - HORIZONTAL LAYOUT
     * @param FellowshipApplication[] $fellapps
     */
    private function generateXlsxData( array $fellapps ) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $userSecUtil = $this->container->get('user_security_utility');
        $instanceId = $userSecUtil->getSiteSettingParameter('instanceId');

        // Define all headers in the exact order requested
        $headers = [
            'ID', 'originalAppId', 'instanceId', 'timestamp', 'lastName', 'firstName', 'middleName',
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

        // Process each application as a new row
        $row = 2;
        foreach ($fellapps as $fellapp) {
            $this->populateRow($sheet, $fellapp, $headers, $row);
            $row++;
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
     * Populate a single row with FellowshipApplication data
     */
    private function populateRow( $sheet, FellowshipApplication $fellapp, array $headers, int $row ) {
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

        // Prepare data array
        $data = [];

        // Basic fields
        $formId = $this->getFormId($fellapp);
        $data['ID'] = $formId;
        $data['originalAppId'] = $fellapp->getId(); //original fellowship application ID
        $data['instanceId'] = $instanceId;

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
        $presentGeoLocation = $presentLocation ? $presentLocation->getGeoLocation() : null;
        $data['presentAddressStreet1'] = $presentGeoLocation ? $presentGeoLocation->getStreet1() : '';
        $data['presentAddressStreet2'] = $presentGeoLocation ? $presentGeoLocation->getStreet2() : '';
        $data['presentAddressCity'] = $presentGeoLocation ? $presentGeoLocation->getCity() : '';
        $data['presentAddressState'] = $presentGeoLocation && $presentGeoLocation->getState() ? $presentGeoLocation->getState()->getName() : '';
        $data['presentAddressZip'] = $presentGeoLocation ? $presentGeoLocation->getZip() : '';
        $data['presentAddressCountry'] = $presentGeoLocation && $presentGeoLocation->getCountry() ? $presentGeoLocation->getCountry()->getName() : '';
        $data['samePAddress'] = '';

        // Permanent Address
        $permLocation = null;
        if ($locations->count() > 1) {
            $permLocation = $locations->get(1);
        }
        $permGeoLocation = $permLocation ? $permLocation->getGeoLocation() : null;
        $data['permanentAddressStreet1'] = $permGeoLocation ? $permGeoLocation->getStreet1() : '';
        $data['permanentAddressStreet2'] = $permGeoLocation ? $permGeoLocation->getStreet2() : '';
        $data['permanentAddressCity'] = $permGeoLocation ? $permGeoLocation->getCity() : '';
        $data['permanentAddressState'] = $permGeoLocation && $permGeoLocation->getState() ? $permGeoLocation->getState()->getName() : '';
        $data['permanentAddressZip'] = $permGeoLocation ? $permGeoLocation->getZip() : '';
        $data['permanentAddressCountry'] = $permGeoLocation && $permGeoLocation->getCountry() ? $permGeoLocation->getCountry()->getName() : '';

        $data['telephoneHome'] = '';
        $data['telephoneWork'] = '';
        $data['telephoneMobile'] = '';
        $data['telephoneFax'] = '';
        $data['email'] = $user ? $user->getEmail() : '';

        $data['dateOfBirth'] = '';
        //TODO: currently we don't have DOB in our application form
//        if ($user && $user->getDob()) {
//            $data['dateOfBirth'] = $user->getDob()->format('Y-m-d');
//        }

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

            if ($training->getGeoLocation()) {
                $city = $training->getGeoLocation()->getCity();
                $state = $training->getGeoLocation()->getState() ? $training->getGeoLocation()->getState()->getName() : '';
                $country = $training->getGeoLocation()->getCountry() ? $training->getGeoLocation()->getCountry()->getName() : '';
            }

            if (stripos($trainingType, 'undergraduate') !== false && $data['undergraduateSchoolName'] == '') {
                $data['undergraduateSchoolStart'] = $startDate;
                $data['undergraduateSchoolEnd'] = $endDate;
                $data['undergraduateSchoolName'] = $institution;
                $data['undergraduateSchoolCity'] = $city;
                $data['undergraduateSchoolState'] = $state;
                $data['undergraduateSchoolCountry'] = $country;
                $majors = $training->getMajors();
                $data['undergraduateSchoolMajor'] = $majors->count() > 0 ? $majors->first()->getName() : '';
                $data['undergraduateSchoolDegree'] = $training->getDegree() ? $training->getDegree()->getName() : '';
            } elseif (stripos($trainingType, 'graduate') !== false && $data['graduateSchoolName'] == '') {
                $data['graduateSchoolStart'] = $startDate;
                $data['graduateSchoolEnd'] = $endDate;
                $data['graduateSchoolName'] = $institution;
                $data['graduateSchoolCity'] = $city;
                $data['graduateSchoolState'] = $state;
                $data['graduateSchoolCountry'] = $country;
                $majors = $training->getMajors();
                $data['graduateSchoolMajor'] = $majors->count() > 0 ? $majors->first()->getName() : '';
                $data['graduateSchoolDegree'] = $training->getDegree() ? $training->getDegree()->getName() : '';
            } elseif (stripos($trainingType, 'medical') !== false && $data['medicalSchoolName'] == '') {
                $data['medicalSchoolStart'] = $startDate;
                $data['medicalSchoolEnd'] = $endDate;
                $data['medicalSchoolName'] = $institution;
                $data['medicalSchoolCity'] = $city;
                $data['medicalSchoolState'] = $state;
                $data['medicalSchoolCountry'] = $country;
                $majors = $training->getMajors();
                $data['medicalSchoolMajor'] = $majors->count() > 0 ? $majors->first()->getName() : '';
                $data['medicalSchoolDegree'] = $training->getDegree() ? $training->getDegree()->getName() : '';
            } elseif (stripos($trainingType, 'residency') !== false && $data['residencyName'] == '') {
                $data['residencyStart'] = $startDate;
                $data['residencyEnd'] = $endDate;
                $data['residencyName'] = $institution;
                $data['residencyCity'] = $city;
                $data['residencyState'] = $state;
                $data['residencyCountry'] = $country;
                $residencySpecialty = $training->getResidencySpecialty();
                $data['residencyArea'] = $residencySpecialty ? $residencySpecialty->getName() : '';
            } elseif (stripos($trainingType, 'gme') !== false || stripos($trainingType, 'fellowship') !== false) {
                if ($data['gme1Name'] == '') {
                    $data['gme1Start'] = $startDate;
                    $data['gme1End'] = $endDate;
                    $data['gme1Name'] = $institution;
                    $data['gme1City'] = $city;
                    $data['gme1State'] = $state;
                    $data['gme1Country'] = $country;
                    $fellowshipSubspecialty = $training->getFellowshipSubspecialty();
                    $data['gme1Area'] = $fellowshipSubspecialty ? $fellowshipSubspecialty->getName() : '';
                } elseif ($data['gme2Name'] == '') {
                    $data['gme2Start'] = $startDate;
                    $data['gme2End'] = $endDate;
                    $data['gme2Name'] = $institution;
                    $data['gme2City'] = $city;
                    $data['gme2State'] = $state;
                    $data['gme2Country'] = $country;
                    $fellowshipSubspecialty = $training->getFellowshipSubspecialty();
                    $data['gme2Area'] = $fellowshipSubspecialty ? $fellowshipSubspecialty->getName() : '';
                }
            } else {
                $majors = $training->getMajors();
                $area = $majors->count() > 0 ? $majors->first()->getName() : '';
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

        // Process examinations - get first exam and use specific getters
        $exam = $examinations->first();
        if ($exam) {
            // USMLE Step 1
            $usmleStep1Date = $exam->getUSMLEStep1DatePassed();
            $data['USMLEStep1DatePassed'] = $usmleStep1Date ? $usmleStep1Date->format('Y-m-d') : '';
            $data['USMLEStep1Score'] = $exam->getUSMLEStep1Score() ?? '';
            $data['USMLEStep1Percentile'] = $exam->getUSMLEStep1Percentile() ?? '';

            // USMLE Step 2 CK
            $usmleStep2CKDate = $exam->getUSMLEStep2CKDatePassed();
            $data['USMLEStep2CKDatePassed'] = $usmleStep2CKDate ? $usmleStep2CKDate->format('Y-m-d') : '';
            $data['USMLEStep2CKScore'] = $exam->getUSMLEStep2CKScore() ?? '';
            $data['USMLEStep2CKPercentile'] = $exam->getUSMLEStep2CKPercentile() ?? '';

            // USMLE Step 2 CS
            $usmleStep2CSDate = $exam->getUSMLEStep2CSDatePassed();
            $data['USMLEStep2CSDatePassed'] = $usmleStep2CSDate ? $usmleStep2CSDate->format('Y-m-d') : '';
            $data['USMLEStep2CSScore'] = $exam->getUSMLEStep2CSScore() ?? '';
            $data['USMLEStep2CSPercentile'] = $exam->getUSMLEStep2CSPercentile() ?? '';

            // USMLE Step 3
            $usmleStep3Date = $exam->getUSMLEStep3DatePassed();
            $data['USMLEStep3DatePassed'] = $usmleStep3Date ? $usmleStep3Date->format('Y-m-d') : '';
            $data['USMLEStep3Score'] = $exam->getUSMLEStep3Score() ?? '';
            $data['USMLEStep3Percentile'] = $exam->getUSMLEStep3Percentile() ?? '';

            // ECFMG
            $data['ECFMGCertificate'] = $exam->getECFMGCertificate() ? 'Yes' : '';
            $data['ECFMGCertificateNumber'] = $exam->getECFMGCertificateNumber() ?? '';
            $ecfmgDate = $exam->getECFMGCertificateDate();
            $data['ECFMGCertificateDate'] = $ecfmgDate ? $ecfmgDate->format('Y-m-d') : '';

            // COMLEX Level 1
            $comlexLevel1Date = $exam->getCOMLEXLevel1DatePassed();
            $data['COMLEXLevel1DatePassed'] = $comlexLevel1Date ? $comlexLevel1Date->format('Y-m-d') : '';
            $data['COMLEXLevel1Score'] = $exam->getCOMLEXLevel1Score() ?? '';
            $data['COMLEXLevel1Percentile'] = $exam->getCOMLEXLevel1Percentile() ?? '';

            // COMLEX Level 2
            $comlexLevel2Date = $exam->getCOMLEXLevel2DatePassed();
            $data['COMLEXLevel2DatePassed'] = $comlexLevel2Date ? $comlexLevel2Date->format('Y-m-d') : '';
            $data['COMLEXLevel2Score'] = $exam->getCOMLEXLevel2Score() ?? '';
            $data['COMLEXLevel2Percentile'] = $exam->getCOMLEXLevel2Percentile() ?? '';

            // COMLEX Level 3
            $comlexLevel3Date = $exam->getCOMLEXLevel3DatePassed();
            $data['COMLEXLevel3DatePassed'] = $comlexLevel3Date ? $comlexLevel3Date->format('Y-m-d') : '';
            $data['COMLEXLevel3Score'] = $exam->getCOMLEXLevel3Score() ?? '';
            $data['COMLEXLevel3Percentile'] = $exam->getCOMLEXLevel3Percentile() ?? '';
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
            $activeStatus = $license->getActive();
            $active = $activeStatus ? $activeStatus->getName() : '';

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
            $board = $cert->getCertifyingBoardOrganization() ? $cert->getCertifyingBoardOrganization()->getName() : '';
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
            $data['recommendation' . $refNum . 'LastName'] = $ref->getName() ?? '';
            $data['recommendation' . $refNum . 'Degree'] = $ref->getDegree() ?? '';
            $data['recommendation' . $refNum . 'Phone'] = $ref->getPhone() ?? '';
            $data['recommendation' . $refNum . 'Title'] = $ref->getTitle() ?? '';
            $institution = $ref->getInstitution();
            $data['recommendation' . $refNum . 'Institution'] = $institution ? $institution->getName() : '';
            $data['recommendation' . $refNum . 'Email'] = $ref->getEmail() ?? '';

            $refLoc = $ref->getGeoLocation();
            if ($refLoc) {
                $data['recommendation' . $refNum . 'AddressStreet1'] = $refLoc->getStreet1() ?? '';
                $data['recommendation' . $refNum . 'AddressStreet2'] = $refLoc->getStreet2() ?? '';
                $city = $refLoc->getCity();
                $data['recommendation' . $refNum . 'AddressCity'] = $city ? $city->getName() : '';
                $state = $refLoc->getState();
                $data['recommendation' . $refNum . 'AddressState'] = $state ? $state->getName() : '';
                $data['recommendation' . $refNum . 'AddressZip'] = $refLoc->getZip() ?? '';
                $country = $refLoc->getCountry();
                $data['recommendation' . $refNum . 'AddressCountry'] = $country ? $country->getName() : '';
            }

            $refIndex++;
        }

        // Final fields
        $data['honors'] = $fellapp->getHonors() ?? '';
        $data['publications'] = $fellapp->getPublications() ?? '';
        $data['memberships'] = $fellapp->getMemberships() ?? '';
        $data['signatureName'] = $fellapp->getSignatureName() ?? '';
        $data['signatureDate'] = $fellapp->getSignatureDate() ? $fellapp->getSignatureDate()->format('Y-m-d H:i:s') : '';

        // Set data in the specified row (horizontal layout)
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $row, $data[$header] ?? '');
            $col++;
        }
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

    //googleFormId
    private function getFormId( $fellapp ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $instanceId = $userSecUtil->getSiteSettingParameter('instanceId');
        $currentDateTime = new \DateTime();
        return $fellapp->getId() . ($instanceId ? "_" . $instanceId : "") . "_" . $currentDateTime->format('Y-m-d-H-i-s');
    }
    
    //(6) "URL of the recommendation letter upload page hosted by the public tandem hub server tenant instance (to append hash ID)" -
    // set it by default to the value "https://view.online/fellowship-applications/submit-a-letter-of-recommendation"

}
