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

    // Caller Server: Transfer parameters from FellowshipSubspecialty to Remote (HUB) Server
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
                //$globalSpecialty->setAcceptingApplication($params['acceptingApplication']);
            }

            //always set acceptingApplication - it will override the dates
            $globalSpecialty->setAcceptingApplication($params['acceptingApplication']);

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
