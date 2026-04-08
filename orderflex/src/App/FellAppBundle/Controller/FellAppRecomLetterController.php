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


class FellAppRecomLetterController extends ListController
{

    //http://127.0.0.1/fellowship-applications/submit-a-letter-of-recommendation?data=eyJSZWZlcmVuY2UtTGV0dGVyLUlEIjoiMzFlOTA5YjFmMmUyMzgwNzBmZjEwNWFlOWQwZmM5MGVhZGJjZjViOCIsIklkZW50aWZpY2F0aW9uIjoid2NtcGF0aGRldiIsIkFwcGxpY2FudCI6eyJGaXJzdE5hbWUiOiJKb2huIDMiLCJMYXN0TmFtZSI6IkRvZSIsIkVtYWlsIjoiY2luYXZhMUB5YWhvby5jb20ifSwiRmVsbG93c2hpcCI6eyJUeXBlIjoiQ2xpbmljYWwgSW5mb3JtYXRpY3MiLCJTdGFydCI6IjA3XC8wMVwvMjAyNyIsIkVuZCI6IjA2XC8zMFwvMjAyOCJ9LCJSZWZlcmVuY2UiOnsiRmlyc3ROYW1lIjoiUmVmMUZpcnN0IiwiTGFzdE5hbWUiOiJSZWYxTGFzdCIsIkRlZ3JlZSI6Ik1EIiwiVGl0bGUiOiJSZWYxVGl0bGUiLCJJbnN0aXR1dGlvbiI6bnVsbCwiUGhvbmUiOm51bGwsIkVtYWlsIjoiY2luYXZhQHlhaG9vLmNvbSJ9fQ

    //https://view.online/fellowship-applications/submit-a-letter-of-recommendation
    //https://view.online/fellowship-applications/submit-a-letter-of-recommendation?HASHofLETTER
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

        //$disabled = false;
        $disabled = true;
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

            $em->getRepository(Document::class)->processDocuments( $reference );

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

    #[Route(path: '/submit-a-letter-of-recommendation/confirmation', name: 'fellapp_recom_letter_confirmation')]
    #[Template('AppFellAppBundle/RecomLetter/recommendation-letter-confirmation.html.twig')]
    public function recomLetterSimpleAction(Request $request)
    {
        return array(
        );
    }


}
