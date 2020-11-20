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

namespace App\ResAppBundle\Controller;

//use App\ResAppBundle\Entity\ResidencyApplication;
//use App\ResAppBundle\Form\ResAppUploadType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;


class ResAppUtilController extends OrderAbstractController
{

    /**
     * @Route("/get-notification-email-infos/", name="resapp_get_notification_email_infos", methods={"GET"}, options={"expose"=true})
     */
    public function GetNotificationEmailInfosAction(Request $request) {

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_COORDINATOR') === false &&
            $this->get('security.authorization_checker')->isGranted('ROLE_RESAPP_DIRECTOR') === false )
        {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $resappId = trim($request->get('id'));
        $emailType = trim($request->get('emailType')); //accepted, rejected
        
        $resapp = $this->getDoctrine()->getRepository('AppResAppBundle:ResidencyApplication')->find($resappId);
        if( !$resapp ) {
            throw $this->createNotFoundException('Unable to find Residency Application by id='.$resappId);
        }

        if( false == $this->get('security.authorization_checker')->isGranted("update",$resapp) ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $resappUtil = $this->container->get('resapp_util');
        $userSecUtil = $this->get('user_security_utility');

        $warning = $resappUtil->getRejectionAcceptanceEmailWarning($resapp);

        $emailSubject = $userSecUtil->getSiteSettingParameter($emailType.'EmailSubject',$this->getParameter('resapp.sitename'));
        $emailBody = $userSecUtil->getSiteSettingParameter($emailType.'EmailBody',$this->getParameter('resapp.sitename'));

        //$rejectedEmailSubject = $userSecUtil->getSiteSettingParameter('rejectedEmailSubject',$this->getParameter('resapp.sitename'));
        //$rejectedEmailBody = $userSecUtil->getSiteSettingParameter('rejectedEmailBody',$this->getParameter('resapp.sitename'));

        $subject = $resappUtil->siteSettingsConstantReplace($emailSubject,$resapp);
        $body = $resappUtil->siteSettingsConstantReplace($emailBody,$resapp);

        if( $subject && $body ) {
            $res = array(
                'warning' => $warning,
                'subject' => $subject,
                'body' => $body
            );
        } else {
            $res = "NOTOK";
        }

        $response = new Response();
        $response->setContent(json_encode($res));
        return $response;
    }

    /**
     * @Route("/ethnicities", name="resapp_get_ethnicities", methods={"GET","POST"}, options={"expose"=true})
     */
    public function getEthnicitiesAction(Request $request) {

        $resappUtil = $this->container->get('resapp_util');
        $ethnicities = $resappUtil->getDefaultEthnicitiesArray();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($ethnicities));
        return $response;
    }

    /**
     * @Route("/resapps-current-year", name="resapp_get_resapps_current_year", methods={"GET","POST"}, options={"expose"=true})
     */
    public function getResApplicationsForThisYearAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $resappPdfUtil = $this->container->get('resapp_pdfutil');
        
        $resapps = $resappPdfUtil->getEnabledResapps();

        $resappsInfoArr = array();
        foreach($resapps as $resapp) {
            //Add to John Smith’s application (ID 1234)
            //$applicantName = $resapp->getApplicantFullName();
            //$resappsInfoArr[] = "Add to ".$applicantName."'s application (ID ".$resapp->getId().")";
            $resappsInfoArr[] = $resapp->getAddToStr();
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($resappsInfoArr));
        return $response;
    }

    /**
     * @Route("/resapp-check-duplicate", name="resapp_check_duplicate", methods={"GET","POST"}, options={"expose"=true})
     */
    public function checkDuplicateAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $resappPdfUtil = $this->container->get('resapp_pdfutil');

        //validate
        //If “Create New Record” is selected and a record for the person already exists (search for the Last Name + First Name among
        // the existing applications in the current year’s applications without statuses of Hidden and Archived),
        // before beginning the bulk import, show a modal:
        //“Applications for LastName1 FirstName1, LastName2 FirstName2, … already exist in the system.
        // Would you like to create new (possibly duplicate) records for these applications?” (Yes) (No)

        //$duplicateArr = $this->checkDuplicate($rowArr,$handsomtableJsonData);

        $tabledata = $request->get('tabledata');

        $data = json_decode($tabledata, true);

        dump($data);
        //exit('111');

        if( $data == null ) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode("Table is empty"));
            return $response;
        }

        $duplicateInfoArr = array();
        $headers = $data["header"];

        //construct $handsomtableJsonData in format (array of $thisRowArr['AAMC ID']['value']):
        // $thisRowArr['Header Title']['value'] = $value;
        // $thisRowArr['Header Title']['id'] = $id;
        // $handsomtableJsonData[] = $thisRowArr;
        $handsomtableJsonData = array();
        foreach( $data["row"] as $row ) {
            $thisRowArr = array();
            foreach($headers as $header) {
                $cellMeta = $resappPdfUtil->getValueByHeaderName($header,$row,$headers);
                $thisRowArr[$header.""]['value'] = $cellMeta['val'];
                $thisRowArr[$header.""]['id'] = $cellMeta['id'];
            }
            $handsomtableJsonData[] = $thisRowArr;
        }
        dump($handsomtableJsonData);

        $rowCount = 0;

        foreach( $data["row"] as $row ) {
            $actionArr = $resappPdfUtil->getValueByHeaderName('Action',$row,$headers);
            $actionValue = $actionArr['val'];
            $actionId = $actionArr['id'];
            echo "actionId=".$actionId." <br>";
            echo "actionValue=".$actionValue." <br>";

            if( $actionValue != "Create New Record" ) {
                //continue;
            }

            $erasIdArr = $resappPdfUtil->getValueByHeaderName('ERAS Application ID',$row,$headers);
            $erasIdValue = $erasIdArr['val'];
            //$erasIdId = $erasIdArr['id'];

            $aamcIdArr = $resappPdfUtil->getValueByHeaderName('AAMC ID',$row,$headers);
            $aamcIdValue = $aamcIdArr['val'];
            //$aamcIdId = $aamcIdArr['id'];

            $residencyStartDateArr = $resappPdfUtil->getValueByHeaderName('Expected Residency Start Date',$row,$headers);
            $residencyStartDateValue = $residencyStartDateArr['val'];
            //$residencyStartDateId = $residencyStartDateArr['id'];

            $receiptDateArr = $resappPdfUtil->getValueByHeaderName('Application Receipt Date',$row,$headers);
            $receiptDateValue = $receiptDateArr['val'];
            //$receiptDateId = $receiptDateArr['id'];

            $emailArr = $resappPdfUtil->getValueByHeaderName('Preferred Email',$row,$headers);
            $emailValue = $emailArr['val'];
            $emailValue = strtolower($emailValue);
            //$emailId = $emailArr['id'];

            $firstNameArr = $resappPdfUtil->getValueByHeaderName('First Name',$row,$headers);
            $firstNameValue = $firstNameArr['val'];
            //$firstNameId = $firstNameArr['id'];

            $lastNameArr = $resappPdfUtil->getValueByHeaderName('Last Name',$row,$headers);
            $lastNameValue = $lastNameArr['val'];
            //$lastNameId = $lastNameArr['id'];

            $residencyApplicationDb = NULL;
            if( $erasIdValue ) {
                $residencyApplicationDb = $em->getRepository('AppResAppBundle:ResidencyApplication')->findOneByErasApplicantId($erasIdValue);
                //echo "1Found resapp?: $residencyApplicationDb <br>";
            }

            if( !$residencyApplicationDb ) {
                //Try to find by aamcId and startDate ("Expected Residency Start Date")
                $rowArr = array();
                $rowArr['AAMC ID']['value'] = $aamcIdValue;
                $rowArr['ERAS Application ID']['value'] = $erasIdValue;
                $rowArr['Expected Residency Start Date']['value'] = $residencyStartDateValue; //07/01/2019
                $rowArr['Application Receipt Date']['value'] = $receiptDateValue; //10/21/2020
                $rowArr['Preferred Email']['value'] = $emailValue;
                $rowArr['Last Name']['value'] = $lastNameValue;
                $rowArr['First Name']['value'] = $firstNameValue;

                $duplicateDbResApps = $resappPdfUtil->getDuplicateDbResApps($rowArr);
                if( count($duplicateDbResApps) > 0  ) {
                    $residencyApplicationDb = $duplicateDbResApps[0];
                }

                if( $residencyApplicationDb ) {
                    $duplicateInfoArr[] = "Duplicate in row $rowCount: ".$residencyApplicationDb->getId();
                }

                //check for duplicate in $handsomtableJsonData TODO:
                if(1) {

                    $duplicateRowArr = $resappPdfUtil->getDuplicateTableResApps($rowArr, $handsomtableJsonData, $rowCount);
                    if ($duplicateRowArr) {
                        $thisExpectedResidencyStartDate = $duplicateRowArr['Expected Residency Start Date']['value'];
                        $thisEmail = $duplicateRowArr['Preferred Email']['value'];
                        $thisFirstName = $duplicateRowArr['First Name']['value'];
                        $thisLastName = $duplicateRowArr['Last Name']['value'];
                        $duplicateInfoArr[] = "Duplicate in batch in row $rowCount: $thisFirstName $thisLastName $thisEmail for Expected Residency Start Date $thisExpectedResidencyStartDate";
                    } else {
                        //$rowArr['Issue']['id'] = null;
                        //$rowArr['Issue']['value'] = "Not Duplicated";
                    }
                }

                $rowCount++;

                //echo "2Found resapp? (count=".count($duplicateDbResApps)."): $residencyApplicationDb <br>";
            }
        }//foreach( $data["row"] as $row ) {


        
        //$resapps = $resappPdfUtil->getEnabledResapps();


//        foreach($resapps as $resapp) {
//            //Add to John Smith’s application (ID 1234)
//            //$applicantName = $resapp->getApplicantFullName();
//            //$resappsInfoArr[] = "Add to ".$applicantName."'s application (ID ".$resapp->getId().")";
//            $resappsInfoArr[] = $resapp->getAddToStr();
//        }

        $duplicateInfoArr[] = "Test Error";

        dump($duplicateInfoArr);
        exit('111');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($duplicateInfoArr));
        return $response;
    }
}
