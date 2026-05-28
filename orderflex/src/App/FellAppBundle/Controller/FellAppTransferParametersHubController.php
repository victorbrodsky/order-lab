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

use App\FellAppBundle\Entity\FellAppStatus;
use App\FellAppBundle\Entity\FellowshipApplication;
use App\FellAppBundle\Entity\GlobalFellowshipSpecialty;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\FellowshipSubspecialty;

use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\Institution;
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

#[Route(path: '/')]
class FellAppTransferParametersHubController extends OrderAbstractController
{

    // Caller Server: Transfer parameters from FellowshipSubspecialty to Remote (HUB) Server
    #[Route(path: '/transfer-specialty-parameters', name: 'fellapp_transfer_specialty_parameters', methods: ['GET'])]
    public function transferSpecialtyParametersAction( Request $request ) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $fellappImportPopulateHubUtil = $this->container->get('fellapp_importpopulate_hub_util');
        $em = $this->getDoctrine()->getManager();

        // Get API connection key for HMAC authentication
        $apiConnectionKey = $userSecUtil->getSiteSettingParameter(
            'apiConnectionKey',
            $this->container->getParameter('fellapp.sitename')
        );

        if( !$apiConnectionKey ) {
            $logger->warning('transferSpecialtyParametersAction: apiConnectionKey is not defined');
            $this->addFlash('warning', 'API Connection Key is not defined in Site Parameters.');
            return $this->redirect($this->generateUrl('fellapp_home'));
        }

        $apiHashConnectionKey = hash('sha256', $apiConnectionKey);

        // Generate HMAC for authentication
        $timestamp = time();
        $hmac = hash_hmac('sha256', 'fellapp-api:' . $timestamp, $apiHashConnectionKey);
        $logger->notice('transferSpecialtyParametersAction: $hmac='.$hmac);

        // Get all FellowshipSubspecialty entities with parameters set
        $fellowshipSubspecialties = $em->getRepository(FellowshipSubspecialty::class)->findAll();

        // Build parameters array
        $specialtyParameters = [];
        foreach ($fellowshipSubspecialties as $subspecialty) {
            // Get institution and name for matching on remote server
            $institution = $subspecialty->getInstitution();
            $institutionId = $institution ? $institution->getId() : null;
            $institutionName = $institution ? $institution->getName() : null;

            $specialtyParameters[] = [
                'id' => $subspecialty->getId(),
                'name' => $subspecialty->getName(),
                'institutionId' => $institutionId,
                'institutionName' => $institutionName,
                'duration' => $subspecialty->getDuration(),
                'submissionStart' => $subspecialty->getSubmissionStart() ? $subspecialty->getSubmissionStart()->format('Y-m-d') : null,
                'submissionEnd' => $subspecialty->getSubmissionEnd() ? $subspecialty->getSubmissionEnd()->format('Y-m-d') : null,
                'acceptingApplication' => $subspecialty->getAcceptingApplication()
            ];
        }

        // Get remote URL
        $remoteUrl = $userSecUtil->getSiteSettingParameter(
            'hubServerApiUrl',
            $this->container->getParameter('fellapp.sitename')
        );

        if( !$remoteUrl ) {
            $logger->warning('transferSpecialtyParametersAction: hubServerApiUrl is not defined');
            $this->addFlash('warning', 'Hub Server API URL is not defined in Site Parameters.');
            return $this->redirect($this->generateUrl('fellapp_home'));
        }

        // Replace the endpoint with receive-specialty-parameters
        $remoteUrl = str_replace('download-application-data', 'receive-specialty-parameters', $remoteUrl);

        try {
            $client = HttpClient::create([
                'verify_peer' => false,
                'verify_host' => false
            ]);

            // Send HMAC authentication headers and POST data
            $response = $client->request('POST', $remoteUrl, [
                'headers' => [
                    'X-HMAC' => $hmac,
                    'X-Timestamp' => $timestamp,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'specialtyParameters' => $specialtyParameters
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $data = $response->toArray();

            if ($statusCode === 200 && $data['success']) {
                $logger->notice('transferSpecialtyParametersAction: Successfully transferred ' . count($specialtyParameters) . ' specialties');
                $this->addFlash('notice', 'Successfully transferred specialty parameters to HUB. Updated: ' . ($data['updated'] ?? 0));
            } else {
                $logger->warning('transferSpecialtyParametersAction: Remote server error: ' . ($data['message'] ?? 'Unknown error'));
                $this->addFlash('warning', 'Failed to transfer parameters: ' . ($data['message'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            $logger->error('transferSpecialtyParametersAction: Exception: ' . $e->getMessage());
            $this->addFlash('error', 'Error transferring parameters: ' . $e->getMessage());
        }

        return $this->redirect($this->generateUrl('fellapp_home'));
    }

    // Remote Server API Endpoint: Receive specialty parameters and update GlobalFellowshipSpecialty
    #[Route(path: '/receive-specialty-parameters', name: 'fellapp_receive_specialty_parameters', methods: ['POST'])]
    public function receiveSpecialtyParametersAction( Request $request ) {
        $logger = $this->container->get('logger');
        $fellappImportPopulateHubUtil = $this->container->get('fellapp_importpopulate_hub_util');
        $em = $this->getDoctrine()->getManager();

        // Verify HMAC authentication from headers
        $hmacHeader = $request->headers->get('X-HMAC');
        $timestampHeader = $request->headers->get('X-Timestamp');
        $logger->notice('receiveSpecialtyParametersAction: $hmacHeader='.$hmacHeader);
        $logger->notice('receiveSpecialtyParametersAction: $timestampHeader='.$timestampHeader);

        if( !$hmacHeader || !$timestampHeader ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'HMAC authentication headers required'
            ], 401);
        }

        // Verify HMAC authentication
        if( $fellappImportPopulateHubUtil->authenticateHmac($hmacHeader,$timestampHeader) === false ) {
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

        $logger->notice('receiveSpecialtyParametersAction: authenticated successful');

        // Get JSON data from request
        $data = json_decode($request->getContent(), true);
        $specialtyParameters = $data['specialtyParameters'] ?? [];

        if( empty($specialtyParameters) ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No specialty parameters provided'
            ], 400);
        }

        $updated = 0;
        $currentDate = new \DateTime();

        foreach ($specialtyParameters as $params) {
            // Find GlobalFellowshipSpecialty by name and institution
            $qb = $em->getRepository(GlobalFellowshipSpecialty::class)->createQueryBuilder('g');
            $qb->where('g.name = :name');
            $qb->setParameter('name', $params['name']);

            if ($params['institutionId']) {
                $qb->leftJoin('g.institution', 'i');
                $qb->andWhere('i.id = :institutionId');
                $qb->setParameter('institutionId', $params['institutionId']);
            }

            $globalSpecialty = $qb->getQuery()->getOneOrNullResult();

            if (!$globalSpecialty) {
                $logger->notice('receiveSpecialtyParametersAction: GlobalFellowshipSpecialty not found for name=' . $params['name']);
                continue;
            }

            // Update parameters
            if (isset($params['duration'])) {
                $globalSpecialty->setDuration($params['duration']);
            }

            if (isset($params['submissionStart'])) {
                $submissionStart = $params['submissionStart'] ? new \DateTime($params['submissionStart']) : null;
                $globalSpecialty->setSubmissionStart($submissionStart);
            }

            if (isset($params['submissionEnd'])) {
                $submissionEnd = $params['submissionEnd'] ? new \DateTime($params['submissionEnd']) : null;
                $globalSpecialty->setSubmissionEnd($submissionEnd);
            }

            // If current date is between submissionStart and submissionEnd, set acceptingApplication to true
            $submissionStart = $globalSpecialty->getSubmissionStart();
            $submissionEnd = $globalSpecialty->getSubmissionEnd();

            if ($submissionStart && $submissionEnd) {
                if ($currentDate >= $submissionStart && $currentDate <= $submissionEnd) {
                    $globalSpecialty->setAcceptingApplication(true);
                    $logger->notice('receiveSpecialtyParametersAction: Set acceptingApplication=true for ' . $params['name']);
                } else {
                    $globalSpecialty->setAcceptingApplication(false);
                }
            } elseif (isset($params['acceptingApplication'])) {
                // Use provided value if dates are not set
                $globalSpecialty->setAcceptingApplication($params['acceptingApplication']);
            }

            $em->persist($globalSpecialty);
            $updated++;
            $logger->notice('receiveSpecialtyParametersAction: Updated GlobalFellowshipSpecialty ' . $params['name']);
        }

        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Successfully updated ' . $updated . ' specialty parameters',
            'updated' => $updated
        ]);
    }

}
