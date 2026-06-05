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

use App\FellAppBundle\Entity\GlobalFellowshipSpecialty;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


//API key $hashkey is generated on Caller and Remote servers must be the same in order for Remote server data back.
//Use Hash-based message authentication code (or HMAC)
//HMAC is used to authenticate API calls between Caller and Remote servers using a shared secret key

#[Route(path: '/')]
class FellAppTransferToHubController extends OrderAbstractController
{

    #[Route(path: '/transfer-to-hub', name: 'fellapp_transfer_to_hub', methods: ['GET'])]
    public function transferToHubAction( Request $request ) {
        $logger = $this->container->get('logger');
        $fellappTransferToHubUtil = $this->container->get('fellapp_transfer_to_hub_util');

        $result = $fellappTransferToHubUtil->transferParametersToHub();
        $logger->notice("transferToHubAction: transfering parameters to the HUB with result=" . $result['message']);
        
        if ($result['success']) {
            $this->addFlash('notice', "Successfully transferred parameters to HUB: " . $result['message']);
        } else {
            $this->addFlash('warning', "Failed to transfer parameters to HUB: " . $result['message']);
        }

        return $this->redirect($this->generateUrl('fellapp_home'));
    }

    // Local Server: Transfer parameters from FellowshipSubspecialty to Remote (HUB) Server
    #[Route(path: '/transfer-specialty-parameters', name: 'fellapp_transfer_specialty_parameters', methods: ['GET'])]
    public function transferSpecialtyParametersAction( Request $request ) {
        $logger = $this->container->get('logger');
        $fellappTransferToHubUtil = $this->container->get('fellapp_transfer_to_hub_util');

        $result = $fellappTransferToHubUtil->transferParametersToHub();

        if ($result['success']) {
            $logger->notice('transferSpecialtyParametersAction: ' . $result['message']);
            $this->addFlash('notice', $result['message']);
        } else {
            $logger->warning('transferSpecialtyParametersAction: ' . $result['message']);
            $this->addFlash('warning', $result['message']);
        }

        return $this->redirect($this->generateUrl('fellapp_home'));
    }

    // Remote Server API Endpoint: Receive specialty parameters and update GlobalFellowshipSpecialty
    #[Route(path: '/receive-specialty-parameters', name: 'fellapp_receive_specialty_parameters', methods: ['POST'])]
    public function receiveSpecialtyParametersAction( Request $request ) {
        $logger = $this->container->get('logger');
        $fellappImportPopulateHubUtil = $this->container->get('fellapp_importpopulate_hub_util');
        $fellappTransferToHubUtil = $this->container->get('fellapp_transfer_to_hub_util');
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

            //$qb->where('g.name = :name');
            //$qb->setParameter('name', $params['name']);

            $qb->where('g.apiHashConnectionKey = :specialtyHashConnectionKey');
            $qb->setParameter('specialtyHashConnectionKey', $params['specialtyHashConnectionKey']);

            if ($params['institutionId']) {
                $qb->leftJoin('g.institution', 'i');
                $qb->andWhere('i.id = :institutionId');
                $qb->setParameter('institutionId', $params['institutionId']);
            }

            $globalSpecialty = $qb->getQuery()->getOneOrNullResult();

            if (!$globalSpecialty) {
                //$logger->notice('receiveSpecialtyParametersAction: GlobalFellowshipSpecialty not found for name=' . $params['name']);
                $logger->notice('receiveSpecialtyParametersAction: GlobalFellowshipSpecialty not found for specialtyHashConnectionKey=' .
                    $params['specialtyHashConnectionKey']);
                continue;
            }

            $logger->notice('receiveSpecialtyParametersAction: GlobalFellowshipSpecialty found for specialtyHashConnectionKey=' .
                $params['specialtyHashConnectionKey'] . "!!!");

            // Process coordinators - check and create users if they don't exist (Run on Remote Server)
            if (isset($params['coordinators']) && is_array($params['coordinators'])) {
                $coordinators = $fellappTransferToHubUtil->checkAndCreateNewUsers($params['coordinators']);
                // Clear existing coordinators and add new ones
                foreach ($globalSpecialty->getCoordinators() as $existingCoordinator) {
                    $globalSpecialty->removeCoordinator($existingCoordinator);
                }
                foreach ($coordinators as $coordinator) {
                    $globalSpecialty->addCoordinator($coordinator);
                    //$logger->notice('receiveSpecialtyParametersAction: Added coordinator ' . $coordinator->getUsername() . ' to ' . $globalSpecialty->getName());
                    $logger->notice('receiveSpecialtyParametersAction: Added coordinator=[' . $coordinator . '] to ' . $globalSpecialty->getName());
                }
            }

            // Process directors - check and create users if they don't exist (Run on Remote Server)
            if (isset($params['directors']) && is_array($params['directors'])) {
                $directors = $fellappTransferToHubUtil->checkAndCreateNewUsers($params['directors']);
                // Clear existing directors and add new ones
                foreach ($globalSpecialty->getDirectors() as $existingDirector) {
                    $globalSpecialty->removeDirector($existingDirector);
                }
                foreach ($directors as $director) {
                    $globalSpecialty->addDirector($director);
                    //$logger->notice('receiveSpecialtyParametersAction: Added director ' . $director->getUsername() . ' to ' . $globalSpecialty->getName());
                    $logger->notice('receiveSpecialtyParametersAction: Added director=[' . $director . '] to ' . $globalSpecialty->getName());
                }
            }

            // Update parameters
            if (isset($params['duration'])) {
                $globalSpecialty->setDuration($params['duration']);
            } else {
                $globalSpecialty->setDuration(null);
            }

            if (isset($params['seasonYearStart'])) {
                $seasonYearStart = $params['seasonYearStart'] ? new \DateTime($params['seasonYearStart']) : null;
                $globalSpecialty->setSeasonYearStart($seasonYearStart);
                $logger->notice('Set seasonYearStart to '.$params['seasonYearStart']);
            } else {
                $globalSpecialty->setSeasonYearStart(null);
                $logger->notice('Set seasonYearStart to NULL');
            }

            if (isset($params['seasonYearEnd'])) {
                $seasonYearEnd = $params['seasonYearEnd'] ? new \DateTime($params['seasonYearEnd']) : null;
                $globalSpecialty->setSeasonYearEnd($seasonYearEnd);
                $logger->notice('Set setSeasonYearEnd to '.$params['seasonYearEnd']);
            } else {
                $globalSpecialty->setSeasonYearEnd(null);
                $logger->notice('Set setSeasonYearEnd to NULL');
            }

            if (isset($params['acceptingApplication'])) {
                $logger->notice('Set AcceptingApplication on HUB to '.$params['acceptingApplication'].' for ' . $globalSpecialty->getName());
                $globalSpecialty->setAcceptingApplication($params['acceptingApplication']);
            } else {
                $globalSpecialty->setAcceptingApplication(false);
            }

            //If dates are empty - nothing changed.
            //If $seasonYearStart not null -> check If today == seasonYearStart => enable accepting applications
            //If $seasonYearEnd not null -> check If today == seasonYearEnd => disable accepting applications
            $seasonYearStart = $globalSpecialty->getSeasonYearStart();
            $seasonYearEnd = $globalSpecialty->getSeasonYearEnd();

            //cron runs once per day, it cares about the calendar date, not the time
            //Doctrine’s date type stores only the date, but the timezone can still differ
            //Normalize everything to Y-m-d strings
            //Comparing Y-m-d strings is deterministic and avoids subtle bugs
            //This is clean, safe, and works exactly as intended for whole‑day logic.
//            $today = (new \DateTime('today'))->format('Y-m-d');
//            if ($seasonYearStart && $today === $seasonYearStart->format('Y-m-d')) {
//                //enableAcceptingApplications();
//                $globalSpecialty->setAcceptingApplication(true);
//                $logger->notice('receiveSpecialtyParametersAction: Set acceptingApplication=true for ' . $params['name']);
//            }
//            if ($seasonYearEnd && $today === $seasonYearEnd->format('Y-m-d')) {
//                //disableAcceptingApplications();
//                $globalSpecialty->setAcceptingApplication(false);
//                $logger->notice('receiveSpecialtyParametersAction: Set acceptingApplication=false for ' . $params['name']);
//            }
            $logger->notice('Run process AcceptingApplication on HUB for ' . $globalSpecialty->getName());
            $fellappTransferToHubUtil->processAcceptingApplication($globalSpecialty,$seasonYearStart,$seasonYearEnd);

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
