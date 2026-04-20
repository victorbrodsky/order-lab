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


class FellAppHubRecomLetterController extends ListController
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

        $fellappId = null;
        if (isset($data['Remote-Application-ID'])) {
            $fellappId = $data['Remote-Application-ID'];
        }
        if( !$fellappId ) {
            $msg = 'Something is wrong - the Remote-Application-ID is not set for '.$firstName. ' ' . $lastName . ' ' . $email;
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
        }

        //testing
        //$refData['Reference']['Institution'] =
        $institution = '';
        $state = '';
        $city = '';
        $country = '';

        $cycle = 'new';
        //TODO: instead of creating a new Reference, find existing Reference which is attached to the existing Fellowship Application
        //$reference = new Reference();
        //Use $recLetterHashId and Application ID $fellappId = $data['Remote-Application-ID'];
        $references = $em->getRepository(Reference::class)->createQueryBuilder('r')
            ->andWhere('r.fellapp = :fellapp')
            ->andWhere('r.recLetterHashId = :hash')
            ->setParameter('fellapp', $fellappId)
            ->setParameter('hash', $recLetterHashId)
            ->getQuery()
            ->getResult();

        if( count($references) == 1 ) {
            //perfect
            $reference = $references[0] ?? null;
        } elseif( count($references) == 0 ) {
            return new JsonResponse([
                'success' => false,
                'message' => "No reference found by fellappId=$fellappId and recLetterHashId=$recLetterHashId"
            ], 500);
        } elseif ( count($references) > 1 ) {
            return new JsonResponse([
                'success' => false,
                'message' => "Multiple references found by fellappId=$fellappId and recLetterHashId=$recLetterHashId"
            ], 500);
        } else {
            return new JsonResponse([
                'success' => false,
                'message' => "Logical error: references found by fellappId=$fellappId and recLetterHashId=$recLetterHashId"
            ], 500);
        }

        if( count($reference->getDocuments()) > 0 ) {
//            return new JsonResponse([
//                'success' => false,
//                'message' => "Reference letter has been already submitted"
//            ], 500);
            //return $this->redirectToRoute('fellapp_recom_letter_confirmation');
            return $this->render(
                'AppFellAppBundle/RecomLetter/recommendation-letter-confirmation.html.twig',
                [
                    'note1' => "Reference letter has been already submitted",
                    'note2' => "The fellowship application system has already received your letter of recommendation."
                ]
            );
        }

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

            //$em->persist($reference);
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
            'fellappId' => $fellappId,
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
        //$logger = $this->container->get('logger');
        $fellappImportPopulateHubUtil = $this->container->get('fellapp_importpopulate_hub_util');
        //$logger->notice("Starting retrieveRecommendationLettersAction");
        $response = $fellappImportPopulateHubUtil->retrieveRecommendationLetters();
        $this->addFlash(
            'notice',
            $response['message']
        );
        return $this->redirect( $this->generateUrl('fellapp_home') );
    }

    //TODO: pass institution id or name to find correct $apiHashConnectionKey on HUB with multiple institutions?
    //Remote Server: API Endpoint to send recommendation letters to Caller server
    // This action sends recommendation letters to the caller server
    #[Route(path: '/send-recommendation-letters', name: 'fellapp_send_recommendation_letters', methods: ['GET'])]
    public function sendRecommendationLettersAction( Request $request ) {
        $em = $this->getDoctrine()->getManager();
        $fellappImportPopulateHubUtil = $this->container->get('fellapp_importpopulate_hub_util');
        $logger = $this->container->get('logger');
        $logger->notice("Starting sendRecommendationLettersAction");

        // Get authentication headers
        $hmacHeader = $request->headers->get('X-HMAC');
        $timestampHeader = $request->headers->get('X-Timestamp');
        $hashIdsParam = $request->query->get('hashids');

        $logger->notice('sendRecommendationLettersAction: $hmacHeader='.$hmacHeader);
        $logger->notice('sendRecommendationLettersAction: $timestampHeader='.$timestampHeader);

        //HUB server can have multiple institutions with HASH: compare $hmacHeader foreach institution hash
        $authenticated = $fellappImportPopulateHubUtil->authenticateHmac($hmacHeader,$timestampHeader);
        if (!$authenticated) {
            $logger->error("HMAC authentication failed for hmacHeader=$hmacHeader");
            return new JsonResponse(['error' => 'Authentication failed'], 401);
        }

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
