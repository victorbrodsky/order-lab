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
     * Run on Local Server
     * 
     * @return array Result with 'success', 'message', and 'updated' keys
     */
    public function transferParametersToHub() {
        $userSecUtil = $this->container->get('user_security_utility');
        $fellappImportPopulateHubUtil = $this->container->get('fellapp_importpopulate_hub_util');
        $fellappTransferToHubUtil = $this->container->get('fellapp_transfer_to_hub_util');
        $logger = $this->container->get('logger');
        $em = $this->em;

        //Get API connection key for HMAC authentication
        $apiConnectionKey = $fellappImportPopulateHubUtil->getInstitutionApiConnectionKey();

        if( !$apiConnectionKey ) {
            $logger->warning('transferParametersToHub: apiConnectionKey is not defined');
            return [
                'success' => false,
                'message' => 'Transfer error: the API Connection Key is not configured for any institution.',
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

            $specialtyHashConnectionKey = null;
            $specialtyApiConnectionKey = $subspecialty->getApiConnectionKey();
            if( $specialtyApiConnectionKey ) {
                $specialtyHashConnectionKey = hash('sha256', $specialtyApiConnectionKey);
            }

            //TODO: should the 'acceptingApplication' on the local server to be set the same way as on the HUB server:
            //If dates are empty - nothing changed.
            //If $seasonYearStart not null -> check If today == seasonYearStart => enable accepting applications
            //If $seasonYearEnd not null -> check If today == seasonYearEnd => disable accepting applications
            $logger->notice('Run process AcceptingApplication on Local Server for ' . $subspecialty->getName());
            $processed = $fellappTransferToHubUtil->processAcceptingApplication(
                $subspecialty,
                $subspecialty->getSeasonYearStart(),
                $subspecialty->getSeasonYearEnd()
            );
            if( $processed ) {
                $em->flush();
            }

            $specialtyParameters[] = [
                'id' => $subspecialty->getId(),
                'specialtyHashConnectionKey' => $specialtyHashConnectionKey,
                'name' => $subspecialty->getName(),
                'institutionId' => $institutionId,
                'institutionName' => $institutionName,
                'duration' => $subspecialty->getDuration(),
                'seasonYearStart' => $subspecialty->getSeasonYearStart() ? $subspecialty->getSeasonYearStart()->format('Y-m-d') : null,
                'seasonYearEnd' => $subspecialty->getSeasonYearEnd() ? $subspecialty->getSeasonYearEnd()->format('Y-m-d') : null,
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

    public function processAcceptingApplication($specialty, $seasonYearStart, $seasonYearEnd) {
        //cron runs once per day, it cares about the calendar date, not the time
        //Doctrine’s date type stores only the date, but the timezone can still differ
        //Normalize everything to Y-m-d strings
        //Comparing Y-m-d strings is deterministic and avoids subtle bugs
        //This is clean, safe, and works exactly as intended for whole‑day logic.
        $logger = $this->container->get('logger');
        $processed = false;
        $today = (new \DateTime('today'))->format('Y-m-d');

        if ($seasonYearStart && $today === $seasonYearStart->format('Y-m-d')) {
            //enableAcceptingApplications();
            $specialty->setAcceptingApplication(true);
            $processed = true;
            $logger->notice('process AcceptingApplication: Set acceptingApplication=true for ' . $specialty->getName());
        }

        if ($seasonYearEnd && $today === $seasonYearEnd->format('Y-m-d')) {
            //disableAcceptingApplications();
            $specialty->setAcceptingApplication(false);
            $processed = true;
            $logger->notice('process AcceptingApplication: Set acceptingApplication=false for ' . $specialty->getName());
        }

        return $processed;
    }
    
} 