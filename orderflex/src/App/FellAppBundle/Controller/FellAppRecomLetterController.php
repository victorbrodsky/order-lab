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
use App\FellAppBundle\Form\ReferenceType;
use App\UserdirectoryBundle\Controller\ListController;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\Institution;
//use App\UserdirectoryBundle\Entity\States;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class FellAppRecomLetterController extends ListController
{


    //https://view.online/fellowship-applications/submit-a-letter-of-recommendation
    //https://view.online/fellowship-applications/submit-a-letter-of-recommendation?HASHofLETTER
    #[Route(path: '/submit-a-letter-of-recommendation', name: 'fellapp_recom_letter', methods: ['GET'])]
    #[Template('AppFellAppBundle/RecomLetter/recomLetter.html.twig')]
    public function recomLetterAction(Request $request)
    {
//        if( false == $this->isGranted('ROLE_FELLAPP_ADMIN') ) {
//            return $this->redirect($this->generateUrl('fellapp-nopermission'));
//        }
        //receive base64 JSON encoded data: https://view.online/fellowship-applications/submit-a-letter-of-recommendation?data=eyJSZWZlcmVuY2Ut...
        $encoded = $request->query->get('data'); // or $request->get('data')
        $base64 = strtr($encoded, '-_', '+/');
        $json = base64_decode($base64);
        $data = json_decode($json, true);
        //dump($data);
        //exit('data');

        //testing
        //$refData['Reference']['Institution'] =
        $institution = '';
        $state = '';
        $city = '';
        $country = '';

        $cycle = 'show';
        $reference = new Reference();

        // Populate reference data from JSON
        if (isset($data['Applicant'])) {
            $applicantData = $data['Applicant'];
            $firstName = $applicantData['FirstName'];
            $lastName = $applicantData['LastName'];
            $email = $applicantData['Email'];
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
        }

        // Store reference letter hash ID if provided
        if (isset($data['Reference-Letter-ID'])) {
            $reference->setRecLetterHashId($data['Reference-Letter-ID']);
        }

        $fellappSpecialty = null;
        $fellappStart = null;
        $fellappEnd = null;
        if (isset($data['Fellowship'])) {
            $fellappSpecialty = $data['Fellowship']['Type'];
            $fellappStart = $data['Fellowship']['Start'];
            $fellappEnd = $data['Fellowship']['End'];
        }

        $disabled = true;
        //$disabled = false;
        $params = array(
            'cycle' => $cycle,
            'em' => $this->getDoctrine()->getManager()
        );
        $form = $this->createForm(ReferenceType::class, $reference, array(
            'method' => 'GET',
            'form_custom_value'=>$params,
            'disabled' => $disabled,
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$data = $form->getData();
            exit('submitted');

            $this->addFlash('success', 'Recommendation letter submitted successfully.');

            return $this->redirectToRoute('app_recom_letter');
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
        );

    }



}
