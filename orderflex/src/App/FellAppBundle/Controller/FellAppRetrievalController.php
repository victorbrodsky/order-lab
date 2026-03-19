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
        $fellapp = $em->getRepository(FellowshipApplication::class)->findOneBy(['googleFormId' => $hashkey]);
        
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
            $client = HttpClient::create();
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
     * Generate xlsx file from FellowshipApplication data
     */
    private function generateXlsxData( FellowshipApplication $fellapp, $hashkey ) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $sheet->setCellValue('A1', 'Field');
        $sheet->setCellValue('B1', 'Value');
        
        $row = 2;
        
        // Add hashkey
        $sheet->setCellValue('A' . $row, 'Hashkey');
        $sheet->setCellValue('B' . $row, $hashkey);
        $row++;
        
        // FellowshipApplication fields
        $sheet->setCellValue('A' . $row, 'ID');
        $sheet->setCellValue('B' . $row, $fellapp->getId());
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Google Form ID');
        $sheet->setCellValue('B' . $row, $fellapp->getGoogleFormId());
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Start Date');
        $sheet->setCellValue('B' . $row, $fellapp->getStartDate() ? $fellapp->getStartDate()->format('Y-m-d') : '');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'End Date');
        $sheet->setCellValue('B' . $row, $fellapp->getEndDate() ? $fellapp->getEndDate()->format('Y-m-d') : '');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Fellowship Subspecialty');
        $sheet->setCellValue('B' . $row, $fellapp->getFellowshipSubspecialty() ? $fellapp->getFellowshipSubspecialty()->getName() : '');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Global Fellowship Specialty');
        $sheet->setCellValue('B' . $row, $fellapp->getGlobalFellowshipSpecialty() ? $fellapp->getGlobalFellowshipSpecialty()->getName() : '');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Institution');
        $sheet->setCellValue('B' . $row, $fellapp->getInstitution() ? $fellapp->getInstitution()->getName() : '');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Reprimand');
        $sheet->setCellValue('B' . $row, $fellapp->getReprimand());
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Lawsuit');
        $sheet->setCellValue('B' . $row, $fellapp->getLawsuit());
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Honors');
        $sheet->setCellValue('B' . $row, $fellapp->getHonors());
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Publications');
        $sheet->setCellValue('B' . $row, $fellapp->getPublications());
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Memberships');
        $sheet->setCellValue('B' . $row, $fellapp->getMemberships());
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Signature Name');
        $sheet->setCellValue('B' . $row, $fellapp->getSignatureName());
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Signature Date');
        $sheet->setCellValue('B' . $row, $fellapp->getSignatureDate() ? $fellapp->getSignatureDate()->format('Y-m-d H:i:s') : '');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Notes');
        $sheet->setCellValue('B' . $row, $fellapp->getNotes());
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Interview Score');
        $sheet->setCellValue('B' . $row, $fellapp->getInterviewScore());
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Application Status');
        $sheet->setCellValue('B' . $row, $fellapp->getAppStatus() ? $fellapp->getAppStatus()->getName() : '');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Timestamp');
        $sheet->setCellValue('B' . $row, $fellapp->getTimestamp() ? $fellapp->getTimestamp()->format('Y-m-d H:i:s') : '');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Interview Date');
        $sheet->setCellValue('B' . $row, $fellapp->getInterviewDate() ? $fellapp->getInterviewDate()->format('Y-m-d') : '');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'User');
        $sheet->setCellValue('B' . $row, $fellapp->getUser() ? $fellapp->getUser()->getUsername() : '');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Rank');
        $sheet->setCellValue('B' . $row, $fellapp->getRank() ? $fellapp->getRank()->getName() : '');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Retrieval Method');
        $sheet->setCellValue('B' . $row, $fellapp->getRetrievalMethod() ? $fellapp->getRetrievalMethod()->getName() : '');
        $row++;
        
        // Counts for related entities
        $sheet->setCellValue('A' . $row, 'Cover Letters Count');
        $sheet->setCellValue('B' . $row, count($fellapp->getCoverLetters()));
        $row++;
        
        $sheet->setCellValue('A' . $row, 'CVs Count');
        $sheet->setCellValue('B' . $row, count($fellapp->getCvs()));
        $row++;
        
        $sheet->setCellValue('A' . $row, 'References Count');
        $sheet->setCellValue('B' . $row, count($fellapp->getReferences()));
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Reports Count');
        $sheet->setCellValue('B' . $row, count($fellapp->getReports()));
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Trainings Count');
        $sheet->setCellValue('B' . $row, count($fellapp->getTrainings()));
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Examinations Count');
        $sheet->setCellValue('B' . $row, count($fellapp->getExaminations()));
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Documents Count');
        $sheet->setCellValue('B' . $row, count($fellapp->getDocuments()));
        $row++;
        
        // Auto-size columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        
        // Write to string
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $xlsxData = ob_get_clean();
        
        return $xlsxData;
    }
    
    
    //(6) "URL of the recommendation letter upload page hosted by the public tandem hub server tenant instance (to append hash ID)" -
    // set it by default to the value "https://view.online/fellowship-applications/submit-a-letter-of-recommendation"

}
