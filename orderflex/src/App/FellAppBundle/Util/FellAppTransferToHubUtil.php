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
 * User: DevServer
 * Date: 8/20/15
 * Time: 4:21 PM
 */

namespace App\FellAppBundle\Util;

use App\UserdirectoryBundle\Entity\FellowshipSubspecialty;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;


class FellAppTransferToHubUtil {

    protected $em;
    protected $container;

    protected $uploadDir;

    public function __construct(
        EntityManagerInterface $em,
        ContainerInterface $container
    ) {
        $this->em = $em;
        $this->container = $container;
        $this->uploadDir = 'Uploaded';
    }

    /**
     * Transfer specialty parameters from FellowshipSubspecialty (local) to GlobalFellowshipSpecialty (HUB)
     * Uses HMAC authentication for secure API communication
     * 
     * @return array Result with 'success', 'message', and 'updated' keys
     */
    public function transferParametersToHub() {
        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');
        $em = $this->em;

        // Get API connection key for HMAC authentication
        $apiConnectionKey = $userSecUtil->getSiteSettingParameter(
            'apiConnectionKey',
            $this->container->getParameter('fellapp.sitename')
        );

        if( !$apiConnectionKey ) {
            $logger->warning('transferParametersToHub: apiConnectionKey is not defined');
            return [
                'success' => false,
                'message' => 'API Connection Key is not defined in Site Parameters.',
                'updated' => 0
            ];
        }

        $apiHashConnectionKey = hash('sha256', $apiConnectionKey);

        // Generate HMAC for authentication
        $timestamp = time();
        $hmac = hash_hmac('sha256', 'fellapp-api:' . $timestamp, $apiHashConnectionKey);
        $logger->notice('transferParametersToHub: $hmac='.$hmac);

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
            $logger->warning('transferParametersToHub: hubServerApiUrl is not defined');
            return [
                'success' => false,
                'message' => 'Hub Server API URL is not defined in Site Parameters.',
                'updated' => 0
            ];
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
                $logger->notice('transferParametersToHub: Successfully transferred ' . count($specialtyParameters) . ' specialties');
                return [
                    'success' => true,
                    'message' => 'Successfully transferred specialty parameters to HUB. Updated: ' . ($data['updated'] ?? 0),
                    'updated' => $data['updated'] ?? 0
                ];
            } else {
                $logger->warning('transferParametersToHub: Remote server error: ' . ($data['message'] ?? 'Unknown error'));
                return [
                    'success' => false,
                    'message' => 'Failed to transfer parameters: ' . ($data['message'] ?? 'Unknown error'),
                    'updated' => 0
                ];
            }

        } catch (\Exception $e) {
            $logger->error('transferParametersToHub: Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error transferring parameters: ' . $e->getMessage(),
                'updated' => 0
            ];
        }
    }
    
} 