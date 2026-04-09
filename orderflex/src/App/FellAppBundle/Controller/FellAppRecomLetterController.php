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

use App\FellAppBundle\Entity\Reference;
use App\FellAppBundle\Form\ReferenceSimpleType;
use App\FellAppBundle\Form\ReferenceType;
use App\UserdirectoryBundle\Controller\ListController;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\Institution;
//use App\UserdirectoryBundle\Entity\States;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpClient\HttpClient;


class FellAppRecomLetterController extends ListController
{

    //http://127.0.0.1/fellowship-applications/submit-a-letter-of-recommendation?data=eyJSZWZlcmVuY2UtTGV0dGVyLUlEIjoiMzFlOTA5YjFmMmUyMzgwNzBmZjEwNWFlOWQwZmM5MGVhZGJjZjViOCIsIklkZW50aWZpY2F0aW9uIjoid2NtcGF0aGRldiIsIkFwcGxpY2FudCI6eyJGaXJzdE5hbWUiOiJKb2huIDMiLCJMYXN0TmFtZSI6IkRvZSIsIkVtYWlsIjoiY2luYXZhMUB5YWhvby5jb20ifSwiRmVsbG93c2hpcCI6eyJUeXBlIjoiQ2xpbmljYWwgSW5mb3JtYXRpY3MiLCJTdGFydCI6IjA3XC8wMVwvMjAyNyIsIkVuZCI6IjA2XC8zMFwvMjAyOCJ9LCJSZWZlcmVuY2UiOnsiRmlyc3ROYW1lIjoiUmVmMUZpcnN0IiwiTGFzdE5hbWUiOiJSZWYxTGFzdCIsIkRlZ3JlZSI6Ik1EIiwiVGl0bGUiOiJSZWYxVGl0bGUiLCJJbnN0aXR1dGlvbiI6bnVsbCwiUGhvbmUiOm51bGwsIkVtYWlsIjoiY2luYXZhQHlhaG9vLmNvbSJ9fQ
    //https://view.online/fellowship-applications/submit-a-letter-of-recommendation
    //https://view.online/fellowship-applications/submit-a-letter-of-recommendation?HASHofLETTER
    //*** Remote server: submit recommendation letter ***/
    #[Route(path: '/submit-a-letter-of-recommendation', name: 'fellapp_recom_letter')]
    #[Template('AppFellAppBundle/RecomLetter/recommendation-letter.html.twig')]
    public function recomLetterAction(Request $request)
    {
//        if( false == $this->isGranted('ROLE_FELLAPP_ADMIN') ) {
//            return $this->redirect($this->generateUrl('fellapp-nopermission'));
//        }
        //receive base64 JSON encoded data from URL (GET) or from request (POST)
        $em = $this->getDoctrine()->getManager();
        $logger = $this->container->get('logger');
        $emailUtil = $this->container->get('user_mailer_utility');

        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        $confirmationEmailFellApp = $userSecUtil->getSiteSettingParameter(
            'confirmationEmailFellApp',
            $this->getParameter('fellapp.sitename')
        );
        if( !$confirmationEmailFellApp ) {
            $confirmationEmailFellApp = $userSecUtil->getSiteSettingParameter('siteEmail');
        }

        $data = [];
        $encoded = $request->query->get('data');
        if ($encoded) {
            // GET request with data in URL
            $base64 = strtr($encoded, '-_', '+/');
            $json = base64_decode($base64);
            $data = json_decode($json, true) ?? [];
            // Store data in session for POST submission
            $request->getSession()->set('recom_letter_data', $data);
        } elseif ($request->isMethod('POST')) {
            // POST request - retrieve data from session or form
            $data = $request->getSession()->get('recom_letter_data', []);
        }

        $applicantData = null;
        $firstName = 'Applicant FirstName';
        $lastName = 'Applicant LastName';
        $email = 'Applicant Email';

        // Populate reference data from JSON
        if (isset($data['Applicant'])) {
            $applicantData = $data['Applicant'];
            $firstName = $applicantData['FirstName'];
            $lastName = $applicantData['LastName'];
            $email = $applicantData['Email'];
        }

        // Store reference letter hash ID if provided
        $recLetterHashId = null;
        if (isset($data['Reference-Letter-ID'])) {
            $recLetterHashId = $data['Reference-Letter-ID'];
        }

        if( !$recLetterHashId ) {
            $msg = 'Something is wrong - the reference letter ID is not set for '.$firstName. ' ' . $lastName . ' ' . $email;
            $subject = $msg;

            $this->addFlash(
                'warning',
                $msg
            );

            $logger->error($msg);


            
            $emailUtil->sendEmail(
                $confirmationEmailFellApp,
                $subject,
                $msg
            );

            return new JsonResponse([
                'success' => false,
                'message' => $msg
            ], 500);
        }

        //testing
        //$refData['Reference']['Institution'] =
        $institution = '';
        $state = '';
        $city = '';
        $country = '';

        $cycle = 'new';
        $reference = new Reference();

        $reference->setRecLetterReceived(false);

        if( $recLetterHashId ) {
            $reference->setRecLetterHashId($recLetterHashId);
        }

        if (isset($data['Reference'])) {
            $refData = $data['Reference'];
            $reference->setFirstName($refData['FirstName'] ?? null);
            $reference->setName($refData['LastName'] ?? null);
            $reference->setDegree($refData['Degree'] ?? null);
            $reference->setTitle($refData['Title'] ?? null);
            //$reference->setInstitution($refData['Institution'] ?? null);
            $institution = $refData['Institution'];
            $reference->setPhone($refData['Phone'] ?? null);
            $reference->setEmail($refData['Email'] ?? null);

            //$institution = $refData['Institution'];
            //$em = $this->getDoctrine()->getManager();
            //$inst = $em->getRepository(Institution::class)->find(1);
            //$reference->setInstitution($inst);

            // Populate address if available
            if( isset($refData['Address']) ) {
                $addrData = $refData['Address'];
                $geoLocation = new GeoLocation();
                $geoLocation->setStreet1($addrData['Street1'] ?? null);
                $geoLocation->setStreet2($addrData['Street2'] ?? null);
                //$geoLocation->setCity($addrData['City'] ?? null); //CityList
                $geoLocation->setZip($addrData['Zip'] ?? null);
                //$geoLocation->setCountry($addrData['Country'] ?? null); //Countries

                if (isset($addrData['State']) && $addrData['State']) {
                    $state = $addrData['State'];
                }
                if (isset($addrData['City']) && $addrData['City']) {
                    $city = $addrData['City'];
                }
                if (isset($addrData['Institution']) && $addrData['Institution']) {
                    $institution = $addrData['Institution'];
                }

                // Find state by name if provided
//                if (isset($addrData['State']) && $addrData['State']) {
//                    $state = $this->getDoctrine()->getManager()->getRepository(States::class)->findOneByName($addrData['State']);
//                    if ($state) {
//                        $geoLocation->setState($state); //States
//                    }
//                }

                //exit('$geoLocation='.$geoLocation);
                $reference->setGeoLocation($geoLocation);
            }
        } else {

            $subject = "Reference letter submission error";
            $msg = $subject . ": the data does not contain any reference information.";

            $emailUtil->sendEmail(
                $confirmationEmailFellApp,
                $subject,
                $msg
            );

            return new JsonResponse([
                'success' => false,
                'message' => $msg
            ], 500);
        }

        $fellappSpecialty = null;
        $fellappStart = null;
        $fellappEnd = null;
        if (isset($data['Fellowship'])) {
            $fellappSpecialty = $data['Fellowship']['Type'];
            $fellappStart = $data['Fellowship']['Start'];
            $fellappEnd = $data['Fellowship']['End'];
        }

        $disabled = false;
        //$disabled = true;
        $params = array(
            'cycle' => $cycle,
            'em' => $this->getDoctrine()->getManager()
        );
        $form = $this->createForm(ReferenceType::class, $reference, array(
            'method' => 'POST',
            'form_custom_value'=>$params,
            'disabled' => $disabled,
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$data = $form->getData();
            //exit('submitted');

            $em->getRepository(Document::class)->processDocuments($reference);

            $this->addFlash('success', 'Recommendation letter submitted successfully.');

            $em->persist($reference);
            $em->flush();

            $emailSubject = "Recommendation Letter Submitted for {$applicantData['FirstName']} {$applicantData['LastName']}";

            $degreeStr = "";
            $degreeReference = strtolower($refData['Degree']);
            if(
                strpos((string)$degreeReference, 'md') !== false
                || strpos((string)$degreeReference, 'm.d.') !== false
                || strpos((string)$degreeReference, 'phd') !== false
                || strpos((string)$degreeReference, 'ph.d') !== false
                || strpos((string)$degreeReference, 'dr.') !== false
            ) {
                $degreeStr = "Dr. ";
            }

            $emailBody = "Dear {$degreeStr}{$refData['FirstName']} {$refData['LastName']},<br><br>".
                "This email confirms the submission of a recommendation letter for ".
                "{$applicantData['FirstName']} {$applicantData['LastName']}.<br><br>".
                "Sincerely,<br>".
                "Fellowship Program Coordinator";

            $emailUtil->sendEmail(
                $email,
                $emailSubject,
                $emailBody
                //$cc,
                //$senderEmail
            );

            return $this->redirectToRoute('fellapp_recom_letter_confirmation');
        }

//        return $this->render('recom_letter/form.html.twig', [
//            'form' => $form->createView(),
//        ]);

        return array(
            'form' => $form,
            'entity' => $reference,
            'cycle' => $cycle,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'institution' => $institution,
            'state' => $state,
            'city' => $city,
            'country' => $country,
            'fellappSpecialty' => $fellappSpecialty,
            'fellappStart' => $fellappStart,
            'fellappEnd' => $fellappEnd,
            'systemUser' => $systemUser
        );
    }
    //Remote Server: confirmation of recommendation letter submition
    #[Route(path: '/submit-a-letter-of-recommendation/confirmation', name: 'fellapp_recom_letter_confirmation')]
    #[Template('AppFellAppBundle/RecomLetter/recommendation-letter-confirmation.html.twig')]
    public function recomLetterConfirmationAction(Request $request)
    {
        return array(
        );
    }




    
    //Caller Server: Make API call to Remote Server
    // This action retrieves recommendation letters from the remote server
    //http://127.0.0.1/fellowship-applications/retrieve-recommendation-letters
    #[Route(path: '/retrieve-recommendation-letters', name: 'fellapp_retrieve_recommendation_letters', methods: ['GET'])]
    public function retrieveRecommendationLettersAction( Request $request ) {
        $logger = $this->container->get('logger');
        $logger->notice("Starting retrieveRecommendationLettersAction");

        // Get remote server URL from settings
        $userSecUtil = $this->container->get('user_security_utility');
        $fellappImportPopulateHubUtil = $this->container->get('fellapp_importpopulate_hub_util');
        //$remoteServerUrl = $userSecUtil->getSiteSettingParameter('externalServerHRecLetterUrl');
        // Get remote server URL from site settings
        $remoteUrl = $userSecUtil->getSiteSettingParameter(
            'hubServerApiUrl',
            $this->getParameter('fellapp.sitename'));
        if( !$remoteUrl ) {
            $logger->warning('fellappRemoteServerUrl is not defined in Site Parameters. Cannot download remote documents.');
            return false;
        }
        //$remoteUrl = https://view.online/fellowship-applications/download-application-data
        //Get $remoteBaseUrl=https://view.online
        $parts = parse_url($remoteUrl);
        $remoteServerUrl = $parts['scheme'] . '://' . $parts['host'];

        //$apiKey = $userSecUtil->getSiteSettingParameter('apiKey');

        if (!$remoteServerUrl) {
            $logger->error("Remote server URL not configured");
            return new JsonResponse(['error' => 'Remote server URL not configured'], 500);
        }

        // Find all references that need letters (recLetterReceived is false or null)
        // and have a recLetterHashId
        $em = $this->getDoctrine()->getManager();
//        $references = $em->getRepository(Reference::class)->findBy(
//            ['recLetterReceived' => null],
//            ['id' => 'ASC'],
//            2 // limit testing
//        );
        $qb = $em->getRepository(Reference::class)->createQueryBuilder('r');
        $qb->join('r.fellapp', 'f');
        $qb->andWhere('f.remoteId IS NOT NULL');
        //$qb->andWhere('r.recLetterReceived IS NOT NULL');
        $qb->andWhere('r.recLetterReceived IS NULL OR r.recLetterReceived = FALSE');
        $qb->orderBy('r.id', 'ASC');
        //$qb->setMaxResults(2); //testing limit
        $references = $qb->getQuery()->getResult();
//        echo "ref count=".count($references)."<br>";
//        foreach($references as $reference) {
//            if( $reference->getRecLetterReceived() != true ) {
//                echo "Ref ID=".$reference->getId()."<br>";
//                echo "Fellapp ID=".$reference->getFellapp()->getId()."<br>";
//            }
//        }
        //exit('111');

        $referencesToProcess = [];
        foreach ($references as $reference) {
            if ($reference->getRecLetterHashId() && !$reference->getRecLetterReceived()) {
                $referencesToProcess[] = $reference;
            }
        }

        if (empty($referencesToProcess)) {
            $logger->notice("No references need recommendation letters");
            return new JsonResponse(['message' => 'No references need recommendation letters', 'count' => 0]);
        }

        // Prepare request to remote server
//        $hashkey = uniqid('', true);
//        $timestamp = time();
//        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');
//        $hmac = hash_hmac('sha256', $hashkey . $timestamp, $secretKey);

        $apiHashConnectionKey = $fellappImportPopulateHubUtil->getInstitutionApiHashConnectionKey();
        //$logger->notice("Caller server: retrieveRecommendationLettersAction: apiHashConnectionKey=$apiHashConnectionKey");
        //exit('$apiHashConnectionKey='.$apiHashConnectionKey);
        if( !$apiHashConnectionKey ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Secret key not configured'
            ], 500);
        }
        // Generate HMAC for authentication (include timestamp to prevent replay attacks)
        $timestamp = time();
        $hmac = hash_hmac('sha256', 'fellapp-api:' . $timestamp, $apiHashConnectionKey);
        //$logger->notice('retrieveApplicationDataAction: $hmac='.$hmac);
        //$logger->notice('retrieveApplicationDataAction: $timestamp='.$timestamp);

        // Build list of hash IDs to request
        $hashIds = [];
        foreach ($referencesToProcess as $ref) {
            $hashIds[] = $ref->getRecLetterHashId();
        }

        $url = $remoteServerUrl . '/fellowship-applications/send-recommendation-letters';
        $url .= '?hashids=' . urlencode(implode(',', $hashIds));

        $logger->notice("Calling remote server: " . $url);

        // Make API call
        $client = HttpClient::create();
        try {
            // Send HMAC authentication headers
            $response = $client->request('GET', $url, [
                'timeout' => 300,
                'verify_peer' => false,
                'verify_host' => false,
                'headers' => [
                    'X-HMAC' => $hmac,
                    'X-Timestamp' => $timestamp
                ]
            ]);
            $statusCode = $response->getStatusCode();
            $content = $response->getContent();

            if ($statusCode !== 200) {
                $logger->error("Caller server: returned status $statusCode: $content");
                return new JsonResponse(['error' => 'Remote server error', 'status' => $statusCode], 500);
            }

            $data = json_decode($content, true);
            if (!isset($data['letters']) || !is_array($data['letters'])) {
                $logger->error("Caller server: Invalid response from remote server");
                return new JsonResponse(['error' => 'Invalid response from remote server'], 500);
            }

            $logger->notice("Caller server: letters count=" . count($data['letters']));

            $processedCount = 0;
            foreach ($data['letters'] as $letterData) {
                if (!isset($letterData['hashId']) || !isset($letterData['documentData'])) {
                    $logger->warning("Caller server: skip: $letterData does not have hashId and documentData");
                    continue;
                }

                // Find the local reference by hash ID
                $reference = $em->getRepository(Reference::class)->findOneBy([
                    'recLetterHashId' => $letterData['hashId']
                ]);

                if (!$reference) {
                    $logger->warning("Reference not found for hash ID: " . $letterData['hashId']);
                    continue;
                }

                // Skip if already received
                if ($reference->getRecLetterReceived()) {
                    $logger->notice("Reference already has letter received: " . $letterData['hashId']);
                    continue;
                }

                // Create and attach document
                $document = new Document($this->getUser());
                $document->setUniqueid($letterData['hashId']);
                $document->setOriginalname($letterData['filename'] ?? 'recommendation_letter.pdf');
                $document->setTitle('Recommendation Letter');

                // Decode and save file
                $fileData = base64_decode($letterData['documentData']);
                $uploadPath = $this->getParameter('fellapp.uploadpath');
                $filename = $letterData['hashId'] . '.pdf';
                $filepath = $uploadPath . '/' . $filename;

                // Ensure directory exists
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                file_put_contents($filepath, $fileData);
                $document->setUploadDirectory($uploadPath);
                $document->setUniquename($filename);
                $document->setSize(strlen($fileData));
                //$document->setMimeType('application/pdf');

                // Generate hash
                $document->generateDocumentHash($filepath);

                $em->persist($document);
                $reference->addDocument($document);
                $reference->setRecLetterReceived(true);

                //send separate API confirmation call to remote server to set $remoteReference->setRecLetterReceived(true);

                $processedCount++;
                $logger->notice("Caller server: Attached document to reference: " . $letterData['hashId']);
            }//foreach

            $em->flush();

            $logger->notice("Caller server: Processed $processedCount recommendation letters");
            return new JsonResponse([
                'message' => 'Recommendation letters retrieved successfully',
                'count' => $processedCount,
                'requested' => count($hashIds)
            ]);

        } catch (\Exception $e) {
            $logger->error("Error retrieving recommendation letters: " . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    //Remote Server: API Endpoint to send recommendation letters to Caller server
    // This action sends recommendation letters to the caller server
    #[Route(path: '/send-recommendation-letters', name: 'fellapp_send_recommendation_letters', methods: ['GET'])]
    public function sendRecommendationLettersAction( Request $request ) {
        $fellappImportPopulateHubUtil = $this->container->get('fellapp_importpopulate_hub_util');
        $logger = $this->container->get('logger');
        $logger->notice("Starting sendRecommendationLettersAction");

        // Get authentication headers
        $hmacHeader = $request->headers->get('X-HMAC');
        $timestampHeader = $request->headers->get('X-Timestamp');
        $hashIdsParam = $request->query->get('hashids');

        $logger->notice('sendRecommendationLettersAction: $hmacHeader='.$hmacHeader);
        $logger->notice('sendRecommendationLettersAction: $timestampHeader='.$timestampHeader);

        /////////// Verify HMAC ///////////
//        $userSecUtil = $this->container->get('user_security_utility');
//        $secretKey = $userSecUtil->getSiteSettingParameter('secretKey');
//        if( !$secretKey ) {
//            $logger->error('Secret key not configured');
//            return new JsonResponse(['error' => 'Secret key not configured'], 500);
//        }

        $apiHashConnectionKey = $fellappImportPopulateHubUtil->getInstitutionApiHashConnectionKey();
        $logger->notice("Remote server: sendRecommendationLettersAction: apiHashConnectionKey=$apiHashConnectionKey");
        //exit('$apiHashConnectionKey='.$apiHashConnectionKey);
        if( !$apiHashConnectionKey ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Secret key not configured'
            ], 500);
        }

        // Validate timestamp (allow 5 minute window)
        $currentTime = time();
        $requestTime = (int)$timestampHeader;
        if (abs($currentTime - $requestTime) > 300) {
            $logger->error("Request timestamp too old");
            return new JsonResponse(['error' => 'Request expired'], 401);
        }

        // Validate HMAC
        $expectedHmac = hash_hmac('sha256', 'fellapp-api:' . $timestampHeader, $apiHashConnectionKey);

        if (!hash_equals($expectedHmac, $hmacHeader)) {
            $logger->error("HMAC authentication failed. Expected: $expectedHmac, Got: $hmacHeader");
            return new JsonResponse(['error' => 'Authentication failed'], 401);
        }

        $em = $this->getDoctrine()->getManager();

        // Get hash IDs to process
        $hashIds = [];
        if ($hashIdsParam) {
            $hashIds = explode(',', $hashIdsParam);
        }
        $logger->notice("Remote server: hashIds count=".count($hashIds));

        $letters = [];

        if (!empty($hashIds)) {
            $logger->notice("Remote server: hashIds proceed. count=".count($hashIds));
            // Find references by specific hash IDs
            foreach ($hashIds as $hashId) {
                $reference = $em->getRepository(Reference::class)->findOneBy([
                    'recLetterHashId' => trim($hashId)
                ]);

                if (!$reference) {
                    $logger->warning("Reference not found for hash ID: $hashId");
                    continue;
                }

                // Get the most recent document
                $document = $reference->getRecentReferenceLetter();
                if (!$document) {
                    $logger->warning("No document found for reference: $hashId");
                    continue;
                }

                // Get file path
                $filepath = $document->getFullServerPath();
                if (!file_exists($filepath)) {
                    $logger->error("File not found: $filepath");
                    continue;
                }

                // Read and encode file
                $fileData = file_get_contents($filepath);
                $encodedData = base64_encode($fileData);

                $letters[] = [
                    'hashId' => $hashId,
                    'documentData' => $encodedData,
                    'filename' => $document->getOriginalname() ?? 'recommendation_letter.pdf',
                    'hash' => $document->getDocumentHash() ?? hash_file('md5', $filepath)
                ];

                // Mark as sent
                $reference->setRecLetterReceived(true); //Remote Server. setRecLetterReceived(true) on a separate confirmation API call
                $logger->notice("Sent recommendation letter for hash ID: $hashId");
            }
        } else {
            // Return all available recommendation letters
            $references = $em->getRepository(Reference::class)->findAll();
            $logger->notice("Remote server: No hashIds: Return all available recommendation letters. count=".count($references));

            foreach ($references as $reference) {
                if (!$reference->getRecLetterHashId()) {
                    continue;
                }

                $document = $reference->getRecentReferenceLetter();
                if (!$document) {
                    continue;
                }

                $filepath = $document->getFullServerPath();
                if (!file_exists($filepath)) {
                    continue;
                }

                $fileData = file_get_contents($filepath);
                $encodedData = base64_encode($fileData);

                $letters[] = [
                    'hashId' => $reference->getRecLetterHashId(),
                    'documentData' => $encodedData,
                    'filename' => $document->getOriginalname() ?? 'recommendation_letter.pdf',
                    'hash' => $document->getDocumentHash() ?? hash_file('md5', $filepath)
                ];

                $reference->setRecLetterReceived(true); //Remote Server
            }
        }

        $em->flush();

        $logger->notice("Remote Server: Returning " . count($letters) . " recommendation letters");

        return new JsonResponse([
            'success' => true,
            'letters' => $letters,
            'count' => count($letters)
        ]);
    }

}
