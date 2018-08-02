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
 * User: ch3
 * Date: 9/26/2017
 * Time: 4:49 PM
 */

namespace Oleg\TranslationalResearchBundle\Controller;


use Doctrine\Common\Collections\ArrayCollection;
use Oleg\OrderformBundle\Form\DataTransformer\AccessionTypeTransformer;
use Oleg\TranslationalResearchBundle\Entity\DataResult;
use Oleg\TranslationalResearchBundle\Entity\Product;
use Oleg\TranslationalResearchBundle\Entity\Project;
use Oleg\TranslationalResearchBundle\Entity\TransResRequest;
use Oleg\TranslationalResearchBundle\Form\FilterRequestType;
use Oleg\TranslationalResearchBundle\Form\TransResRequestType;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Stopwatch\Stopwatch;


/**
 * Request FormNode controller.
 */
class RequestController extends Controller
{

    /**
     * Creates a new request entity with formnode.
     *
     * @Route("/project/{id}/work-request/new/", name="translationalresearch_request_new")
     * @Route("/work-request/new/", name="translationalresearch_new_standalone_request")
     * @Template("OlegTranslationalResearchBundle:Request:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newFormNodeAction(Request $request, Project $project=null)
    {
        $transResFormNodeUtil = $this->get('transres_formnode_util');
        $transresRequestUtil = $this->get('transres_request_util');
        $transresUtil = $this->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER') &&
            $transresUtil->isProjectRequester($project) === false
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }


        $cycle = "new";
        $formnode = false;

        $testing = false;
        //$testing = true;

        $transresRequest = $this->createRequestEntity($user,null);

        //add one Product or Service
        $product = new Product($user);
        $transresRequest->addProduct($product);

        $title = "New Work Request";

        if( $project ) {

            if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
                );
                return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            }

            $transresRequest->setProject($project);
            $title = "New Work Request for project ID ".$project->getOid();

            //$projectFundedAccountNumber = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"If funded, please provide account number");
            $projectFundedAccountNumber = $project->getFundedAccountNumber();
            if( $projectFundedAccountNumber ) {
                $transresRequest->setFundedAccountNumber($projectFundedAccountNumber);
            }

            //pre-populate Request's Billing Contact by Project's Billing Contact
            if( $project->getBillingContact() ) {
                $transresRequest->setContact($project->getBillingContact());
            }

            //new: pre-populate Request's Support End Date by Project's IRB Expiration Date
            if( $project->getIrbExpirationDate() ) {
                $transresRequest->setSupportEndDate($project->getIrbExpirationDate());
            }

            //pre-populate PIs
            $transreqPis = $project->getPrincipalInvestigators();
            foreach( $transreqPis as $transreqPi ) {
                $transresRequest->addPrincipalInvestigator($transreqPi);
            }
        }

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //new

//        $messageTypeId = true;//testing
//        $formnodetrigger = 1;
//        if( $messageTypeId ) {
//            $formnodetrigger = 0; //build formnodes from top to bottom
//        }

        //top message category id
//        $formnodeTopHolderId = null;
//        $messageCategory = $transresRequest->getMessageCategory();
//        if( $messageCategory ) {
//            $formnodeTopHolderId = $messageCategory->getId();
//        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //exit("Project submitted");

            $project = $transresRequest->getProject();

            //new: pre-populate Request's Support End Date by Project's IRB Expiration Date
            if( !$transresRequest->getSupportEndDate() ) {
                if( $project->getIrbExpirationDate() ) {
                    $transresRequest->setSupportEndDate($project->getIrbExpirationDate());
                }
            }

            //set project's funded account number
            $changedMsg = "";
            //$changedProjectFundNumber = false;
            $originalFundedAccountNumber = $project->getFundedAccountNumber();
            $fundedAccountNumber = $transresRequest->getFundedAccountNumber();
            if( $fundedAccountNumber && $fundedAccountNumber != $originalFundedAccountNumber ) {
                $project->setFundedAccountNumber($fundedAccountNumber);
                //set formnode field
//                $transresRequestUtil->setValueToFormNodeProject($project, "If funded, please provide account number", $fundedAccountNumber);
                $project->setFundedAccountNumber($fundedAccountNumber);
                //$changedProjectFundNumber = true;
                $changedMsg = $changedMsg . "<br>Project's Account Fund Number has been updated: ";
                $changedMsg = $changedMsg . "<br>Original account number " . $originalFundedAccountNumber;
                $changedMsg = $changedMsg . "<br>New account number " . $project->getFundedAccountNumber();
            }

            //set submitter to product
            foreach($transresRequest->getProducts() as $product) {
                if( !$product->getSubmitter() ) {
                    $product->setSubmitter($user);
                }
            }

            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($transresRequest,"document");
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($transresRequest,"packingSlipPdf");
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($transresRequest,"oldPackingSlipPdf");

            $this->processTableData($transresRequest,$form,$user); //new

            if( $testing ) {
                echo "Btn clicked=".$form->getClickedButton()->getName()."<br>";
            }

            //new
            if ($form->getClickedButton() && 'saveAsDraft' === $form->getClickedButton()->getName()) {
                //Save Project as Draft => state='draft'
                $transresRequest->setProgressState('draft');
                $transresRequest->setBillingState('draft');
            }

            //new
            if ($form->getClickedButton() && 'saveAsComplete' === $form->getClickedButton()->getName()) {
                //Complete Submission => state='submit'
                $transresRequest->setProgressState('active');
                $transresRequest->setBillingState('active');
            }

            if( !$testing ) {
                $em->persist($transresRequest);
                $em->flush();

                //set oid
                $transresRequest->generateOid();
                $em->flush();
            }

            //process form nodes
            if( $formnode ) {
                $formNodeUtil = $this->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request, $transresRequest->getMessageCategory(), $transresRequest, $testing);
            }

            $msg = "New work request has been successfully submitted for the project ID ".$project->getOid();
            $msg = $msg . $changedMsg;

            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Request Created";
            $msg = "New work request with ID ".$transresRequest->getOid()." has been successfully submitted for the project ID ".$project->getOid();
            $msg = $msg . $changedMsg;
            $transresUtil->setEventLog($transresRequest,$eventType,$msg);

            $subject = "New work request has been successfully submitted for the project ID ".$project->getOid();
            $msg = str_replace("<br>","\r\n",$msg);
            $transresRequestUtil->sendRequestNotificationEmails($transresRequest,$subject,$msg,$testing);

            return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
        }


        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            //'formnodetrigger' => $formnodetrigger,
            //'formnodeTopHolderId' => $formnodeTopHolderId,
            'routeName' => $request->get('_route')
        );
    }



    /**
     * Get TransResRequest Edit page
     *
     * @Route("/request/edit/{id}", name="translationalresearch_request_edit")
     * @Template("OlegTranslationalResearchBundle:Request:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, TransResRequest $transresRequest)
    {
        //$transResFormNodeUtil = $this->get('transres_formnode_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $formnode = false;
        $cycle = "edit";
        $formtype = "translationalresearch-request";

        $class = new \ReflectionClass($transresRequest);
        $className = $class->getShortName();          //ObjectTypeText
        $classNamespace = $class->getNamespaceName(); //Oleg\UserdirectoryBundle\Entity

        $testing = false;
        //$testing = true;

        $project = $transresRequest->getProject();

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN') &&
            $transresUtil->isProjectRequester($project) === false
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if( $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') === false ) {
            if ($transresUtil->isProjectRequester($project)) {
                if ($transresRequest->getProgressState() != 'draft') {
                    $stageLabel = $transresRequestUtil->getRequestStateLabelByName($transresRequest->getProgressState(), 'progress');
                    $this->get('session')->getFlashBag()->add(
                        'warning',
                        "You can not edit this Working Request, because it's not in the Draft stage. Current stage is " . $stageLabel
                    );
                    //return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
                    return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
                }
            }
        }

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$projectFundedAccountNumber = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"If funded, please provide account number");
        //if( $projectFundedAccountNumber ) {
        //    $transresRequest->setFundedAccountNumber($projectFundedAccountNumber);
        //}

        $transresRequest = $this->createRequestEntity($user,$transresRequest);

        // Create an ArrayCollection of the current Tag objects in the database
        $originalProducts = new ArrayCollection();
        foreach($transresRequest->getProducts() as $product) {
            $originalProducts->add($product);
        }

        // Create an ArrayCollection of the current DataResult objects in the database
        $originalDataResults = new ArrayCollection();
        foreach($transresRequest->getDataResults() as $dataResult) {
            $originalDataResults->add($dataResult);
        }

        //get Table $jsonData
        $jsonData = $this->getTableData($transresRequest);
        //print_r($jsonData);
//        echo 'jsonData:<pre>';
//        print_r($jsonData);
//        echo  '</pre>';

        $originalProgressState = $transresRequest->getProgressState();

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //edit

//        $messageTypeId = true;//testing
//        $formnodetrigger = 1;
//        if( $messageTypeId ) {
//            $formnodetrigger = 0; //build formnodes from top to bottom
//        }

        //top message category id
//        $formnodeTopHolderId = null;
//        //$categoryStr = "Pathology Call Log Entry";
//        $messageCategory = $transresRequest->getMessageCategory();
//        if( $messageCategory ) {
//            $formnodeTopHolderId = $messageCategory->getId();
//        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //exit("Request update submitted");

            //set project's funded account number
            $changedMsg = "";
            //$changedProjectFundNumber = false;
            $originalFundedAccountNumber = $project->getFundedAccountNumber();
            $fundedAccountNumber = $transresRequest->getFundedAccountNumber();
            if( $fundedAccountNumber && $fundedAccountNumber != $originalFundedAccountNumber ) {
                $project->setFundedAccountNumber($fundedAccountNumber);
                //set formnode field
//                $transresRequestUtil->setValueToFormNodeProject($project, "If funded, please provide account number", $fundedAccountNumber);
                $project->setFundedAccountNumber($fundedAccountNumber);
                //$changedProjectFundNumber = true;
                $changedMsg = $changedMsg . "<br>Project's Account Fund Number has been updated: ";
                $changedMsg = $changedMsg . "<br>Original account number " . $originalFundedAccountNumber;
                $changedMsg = $changedMsg . "<br>New account number " . $project->getFundedAccountNumber();
            }

            //update updateBy
            $transresRequest->setUpdateUser($user);

            //edit: pre-populate Request's Support End Date by Project's IRB Expiration Date
            if( !$transresRequest->getSupportEndDate() ) {
                if( $project->getIrbExpirationDate() ) {
                    $transresRequest->setSupportEndDate($project->getIrbExpirationDate());
                }
            }

            //process Product or Service sections
            // remove the relationship between the tag and the Task
            foreach($originalProducts as $product) {
                if( false === $transresRequest->getProducts()->contains($product) ) {
                    // remove the Task from the Tag
                    $transresRequest->getProducts()->removeElement($product);
                    // if it was a many-to-one relationship, remove the relationship like this
                    $product->setTransresRequest(null);
                    $em->persist($product);
                    // if you wanted to delete the Tag entirely, you can also do that
                    $em->remove($product);
                }
            }

            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($transresRequest,"document");
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($transresRequest,"packingSlipPdf");
            $em->getRepository('OlegUserdirectoryBundle:Document')->processDocuments($transresRequest,"oldPackingSlipPdf");

            $updatedDataResults = $this->processTableData($transresRequest,$form,$user); //edit

            // remove the relationship between the tag and the Task
            foreach($originalDataResults as $dataResult) {
                //echo "??? remove dataResult ID=".$dataResult->getId()."<br>";
                if (false === $updatedDataResults->contains($dataResult)) {
                    // remove the Task from the Tag
                    //echo "remove dataResult ID=".$dataResult->getId()."<br>";
                    $transresRequest->getDataResults()->removeElement($dataResult);
                    // if it was a many-to-one relationship, remove the relationship like this
                    $dataResult->setTransresRequest(null);
                    $em->persist($dataResult);
                    // if you wanted to delete the Tag entirely, you can also do that
                    $em->remove($dataResult);
                }
            }

            //edit
            if ($form->getClickedButton() && 'saveAsDraft' === $form->getClickedButton()->getName()) {
                //Save Project as Draft => state='draft'
                $transresRequest->setProgressState('draft');
                $transresRequest->setBillingState('draft');
            }

            //edit
            if ($form->getClickedButton() && 'saveAsComplete' === $form->getClickedButton()->getName()) {
                //Complete Submission => state='submit'
                if( $transresRequest->getProgressState() == 'draft' ) {
                    $transresRequest->setProgressState('active');
                    $transresRequest->setBillingState('active');
                }
            }

            if( !$testing ) {
                $em->persist($transresRequest);
                $em->flush();
            }

            //If Work Request’s Progress Status is changed
            $progressState = $transresRequest->getProgressState();
            if( $progressState != $originalProgressState ) {
                $transresRequestUtil->syncRequestStatus($transresRequest,$progressState,$testing);
            }

            //testing
//            print "<pre>";
//            var_dump($_POST);
//            print "</pre><br>";
//            echo "formnode[420]=".$_POST['formnode[420]']."<br>";
//            echo "formnode[421]=".$_POST['formnode[421]']."<br>";

            //process form nodes
            if( $formnode ) {
                $formNodeUtil = $this->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request, $transresRequest->getMessageCategory(), $transresRequest, $testing); //testing
            }

            $msg = "Request ".$transresRequest->getOid()." has been successfully updated for the project ID ".$project->getOid();
            $msg = $msg . $changedMsg;

            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Request Updated";
            $msg = "Request ".$transresRequest->getOid() ." has been updated.";
            $msg = $msg . $changedMsg;
            $transresUtil->setEventLog($transresRequest,$eventType,$msg);

            $subject = "Request ".$transresRequest->getOid()." has been successfully updated for the project ID ".$project->getOid();
            $msg = str_replace("<br>","\r\n",$msg);
            $transresRequestUtil->sendRequestNotificationEmails($transresRequest,$subject,$msg,$testing);

            return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
        }

        //$sitename = $this->container->getParameter('translationalresearch.sitename');
        //$defaultAccessionType = $userSecUtil->getSiteSettingParameter('accessionType',$sitename);
        $projectSpecialty = $project->getProjectSpecialty();
        $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();
        $siteParameter = $transresRequestUtil->findCreateSiteParameterEntity($projectSpecialtyAbbreviation);
        if( !$siteParameter ) {
            throw new \Exception("SiteParameter is not found by specialty '" . $projectSpecialtyAbbreviation . "'");
        }
        $defaultAccessionType = $siteParameter->getAccessionType();
        //echo "defaultAccessionType=".$defaultAccessionType."<br>";

        $eventType = "Request Viewed";
        $msg = "Request ".$transresRequest->getOid() ." has been viewed on the edit page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'formtype' => $formtype,
            'title' => "Edit Work Request ".$transresRequest->getOid(),
            'triggerSearch' => 0,
            //'formnodetrigger' => $formnodetrigger,
            //'formnodeTopHolderId' => $formnodeTopHolderId,
            'entityNamespace' => $classNamespace,
            'entityName' => $className,
            'entityId' => $transresRequest->getId(),
            'sitename' => $this->container->getParameter('translationalresearch.sitename'),
            'routeName' => $request->get('_route'),
            //'handsometableData' => json_encode($jsonData)
            'handsometableData' => $jsonData,
            'defaultAccessionType' => $defaultAccessionType
        );
    }

    //return created/updated array of DataResult objects existing in the Request
    public function processTableData( $transresRequest, $form, $user ) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //////////////// process handsontable rows ////////////////
        $datajson = $form->get('datalocker')->getData();
//        echo "<br>datajson:<br>";
//        var_dump($datajson);
//        echo "<br>";
//        echo 'datajson:<pre>';
//        print_r($datajson);
//        echo  '</pre>';

        $data = json_decode($datajson, true);
        //$data = $datajson;
//        echo 'data:<pre>';
//        print_r($data);
//        echo  '</pre>';

        $updatedDataResults = new ArrayCollection();

        if( $data == null ) {
            //exit('Table order data is null.');
            //throw new \Exception( 'Table order data is null.' );
            return $updatedDataResults;
        }

        //$headers = array_shift($data);
        $headers = $data["header"];
        //var_dump($headers);
        //echo "<br><br>";

        //echo "entity inst=".$entity->getInstitution()."<br>";
        //exit();

        foreach( $data["row"] as $row ) {
//            echo "<br>row:<br>";
//            var_dump($row);
//            echo "<br>";
            //exit();

            $systemArr = $this->getValueByHeaderName('Source',$row,$headers);
//            echo "<br>systemArr:<br>";
//            var_dump($systemArr);
//            echo "<br>";

            $systemValue = $systemArr['val'];
            $systemId = $systemArr['id'];
            //echo "systemId=".$systemId." <br>";
            //echo "systemValue=".$systemValue." <br>";
            //TODO: get AccessionType Entity

            $accArr = $this->getValueByHeaderName('Accession ID',$row,$headers);
            $accValue = $accArr['val'];
            $accId = $accArr['id'];
            //echo "accValue=".$accValue." <br>";

            $partArr = $this->getValueByHeaderName('Part ID',$row,$headers);
            $partValue = $partArr['val'];
            $partId = $partArr['id'];
            //echo "partId=".$partId." <br>";
            //echo "partValue=".$partValue." <br>";

            $blockArr = $this->getValueByHeaderName('Block ID',$row,$headers);
            $blockValue = $blockArr['val'];
            $blockId = $blockArr['id'];
            //echo "blockId=".$blockId." <br>";
            //echo "blockValue=".$blockValue." <br>";
            //exit('1');

            $slideArr = $this->getValueByHeaderName('Slide ID',$row,$headers);
            $slideValue = $slideArr['val'];
            $slideId = $slideArr['id'];

            $stainArr = $this->getValueByHeaderName('Stain Name',$row,$headers);
            $stainValue = $stainArr['val'];
            $stainId = $stainArr['id'];

            $antibodyArr = $this->getValueByHeaderName('Antibody',$row,$headers);
            $antibodyValue = $antibodyArr['val'];
            $antibodyId = $antibodyArr['id'];

            $otherArr = $this->getValueByHeaderName('Other ID',$row,$headers);
            $otherValue = $otherArr['val'];
            $otherId = $otherArr['id'];


            $barcodeArr = $this->getValueByHeaderName('Barcode',$row,$headers);
            $barcodeValue = $barcodeArr['val'];
            $barcodeId = $barcodeArr['id'];
            //echo "barcodeId=".$barcodeId." <br>";
            //echo "barcodeValue=".$barcodeValue." <br>";

            $barcodeImageArr = $this->getValueByHeaderName('Barcode Image',$row,$headers);
            $barcodeImageValue = $barcodeImageArr['val'];
            $barcodeImageId = $barcodeImageArr['id'];

            $commentArr = $this->getValueByHeaderName('Comment',$row,$headers);
            $commentValue = $commentArr['val'];
            $commentId = $commentArr['id'];

            if( $accValue || $barcodeValue || $commentValue) {
                //get $dataResult object
                $dataResult = null;
                $objectId = null;
                if( $systemId || $accId || $barcodeId ) {
                    if( $systemId ) {
                        $objectId = $systemId;
                    }
                    if( !$objectId && $accId ) {
                        $objectId = $accId;
                    }
                    if( !$objectId && $barcodeId ) {
                        $objectId = $barcodeId;
                    }
                    if( !$objectId && $commentId ) {
                        $objectId = $commentId;
                    }
                }

                if( $objectId ) {
                    $dataResult = $em->getRepository('OlegTranslationalResearchBundle:DataResult')->find($objectId);
                    //echo "dataResult found=".$dataResult->getSystem()."<br>";
                }
                //exit();

                if( $dataResult ) {
                    $updatedDataResults->add($dataResult);
                } else {
                    $dataResult = new DataResult($user);
                }

                if( $systemValue ) {
                    $accTransformer = new AccessionTypeTransformer($em,$user);
                    $systemEntity = $accTransformer->reverseTransform($systemValue);
                    //echo "systemEntity=".$systemEntity->getId()."name=".$systemEntity."<br>";
                    //exit('111');
                    $dataResult->setSystem($systemEntity);
                }

                if( $antibodyValue ) {
                    $transformer = new GenericTreeTransformer($em, $user, 'AntibodyList','TranslationalResearchBundle');
                    $antibodyEntity = $transformer->reverseTransform($antibodyValue);
                    //echo "systemEntity=".$systemEntity->getId()."name=".$systemEntity."<br>";
                    //exit('111');
                    $dataResult->setAntibody($antibodyEntity);
                }

                $dataResult->setAccessionId($accValue);
                $dataResult->setPartId($partValue);
                $dataResult->setBlockId($blockValue);
                $dataResult->setSlideId($slideValue);
                $dataResult->setStainName($stainValue);
                $dataResult->setOtherId($otherValue);

                $dataResult->setBarcode($barcodeValue);
                //$dataResult->setBarcodeImage($barcodeImageValue);

                $dataResult->setComment($commentValue);

                $transresRequest->addDataResult($dataResult);
            }

        }//foreach row

        return $updatedDataResults;
    }
    public function getValueByHeaderName($header, $row, $headers) {

        $res = array();

        $key = array_search($header, $headers);

        $res['val'] = $row[$key]['value'];

        $id = null;

        if( array_key_exists('id', $row[$key]) ) {
            $id = $row[$key]['id'];
            //echo "id=".$id.", val=".$res['val']."<br>";
        }

        $res['id'] = $id;

        //echo "key=".$key.": id=".$res['id'].", val=".$res['val']."<br>";
        return $res;
    }

    public function getTableData($transresRequest) {
        $jsonData = array();

        foreach($transresRequest->getDataResults() as $dataResult) {
            $rowArr = array();

            //System
            $system = $dataResult->getSystem();
            if( $system ) {
                $rowArr['Source']['id'] = $system->getId();
                $rowArr['Source']['value'] = $system."";
            }

            //Accession ID
            $rowArr['Accession ID']['id'] = $dataResult->getId();
            $rowArr['Accession ID']['value'] = $dataResult->getAccessionId();

            //Part ID
            $rowArr['Part ID']['id'] = $dataResult->getId();
            $rowArr['Part ID']['value'] = $dataResult->getPartId();

            //Block ID
            $rowArr['Block ID']['id'] = $dataResult->getId();
            $rowArr['Block ID']['value'] = $dataResult->getBlockId();

            //Slide ID
            $rowArr['Slide ID']['id'] = $dataResult->getId();
            $rowArr['Slide ID']['value'] = $dataResult->getSlideId();

            //Stain Name
            $rowArr['Stain Name']['id'] = $dataResult->getId();
            $rowArr['Stain Name']['value'] = $dataResult->getStainName();

            //Antibody
            $antibody = $dataResult->getAntibody();
            if( $antibody ) {
                $rowArr['Antibody']['id'] = $antibody->getId();
                $rowArr['Antibody']['value'] = $antibody."";
            }

            //Other ID
            $rowArr['Other ID']['id'] = $dataResult->getId();
            $rowArr['Other ID']['value'] = $dataResult->getOtherId();

            //Barcode
            $rowArr['Barcode']['id'] = $dataResult->getId();
            $rowArr['Barcode']['value'] = $dataResult->getBarcode();

            //Comment
            $rowArr['Comment']['id'] = $dataResult->getId();
            $rowArr['Comment']['value'] = $dataResult->getComment();


            $jsonData[] = $rowArr;
        }

        return $jsonData;
    }

    /**
     * Displays the list of requests for the given project.
     *
     * @Route("/request/show/{id}", name="translationalresearch_request_show")
     * @Route("/request/show-with-packingslip/{id}", name="translationalresearch_request_show_with_packingslip")
     * @Template("OlegTranslationalResearchBundle:Request:new.html.twig")
     * @Method("GET")
     */
    public function showAction(Request $request, TransResRequest $transresRequest)
    {
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $cycle = "show";
        $project = $transresRequest->getProject();

        if(
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER') &&
            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN') &&
            $transresUtil->isProjectRequester($project) === false
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }


        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //get Table $jsonData
        $jsonData = $this->getTableData($transresRequest);
//        echo 'jsonData:<pre>';
//        print_r($jsonData);
//        echo  '</pre>';

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //show

        //$deleteForm = $this->createDeleteForm($project);

        //create a review form (for example, IrbReview form if logged in user is a reviewer or reviewer delegate)
        //1) if project is in the review state: irb_review, admin_review, committee_review or final_review
        //2) if the current user is added to this project as the reviewer for the state above
        //$reviewFormViews = $transresUtil->getReviewForm($project,$user);

        $feeHtml = null;
        $fee = $transresRequestUtil->getTransResRequestFeeHtml($transresRequest);
        if( $fee ) {
            $feeHtml = " (fee $".$fee.")";
        }

        $eventType = "Request Viewed";
        $msg = "Request ".$transresRequest->getOid() ." has been viewed on the show review page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        $showPackingSlip = false;
        if( $request->get('_route') == "translationalresearch_request_show_with_packingslip" ) {
            $showPackingSlip = true;
        }

        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Request ".$transresRequest->getOid() . $feeHtml,
            'routeName' => $request->get('_route'),
            //'handsometableData' => json_encode($jsonData)
            'handsometableData' => $jsonData,
            'showPackingSlip' => $showPackingSlip,
            'defaultAccessionType' => null
            //'delete_form' => $deleteForm->createView(),
            //'review_forms' => $reviewFormViews
        );
    }

    /**
     * Finds and displays all requests for the given project
     *
     * @Route("/project/{id}/requests", name="translationalresearch_request_index")
     * @Template("OlegTranslationalResearchBundle:Request:index.html.twig")
     * @Method("GET")
     */
    public function indexAction(Request $request, Project $project)
    {
//        if(
//            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER') &&
//            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
//        ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        $transresUtil = $this->container->get('transres_util');

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //Redirect according project ID
        return $this->redirectToRoute(
            'translationalresearch_request_index_filter',
            array(
                'filter[project]' => $project->getId(),
            )
        );
    }

    /**
     * Finds and displays the filtered requests lists
     *
     * @Route("/work-requests/list/", name="translationalresearch_request_index_filter")
     * @Template("OlegTranslationalResearchBundle:Request:index.html.twig")
     * @Method("GET")
     */
    public function myRequestsAction(Request $request)
    {

        $timer = false;
        $timer = true;
        if( $timer ) {
            $stopwatch = new Stopwatch();
            //$time_pre = microtime(true);
            $stopwatch->start('myRequestsAction');
            $stopwatch->start('Paginator');
        }

//        if(
//            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER') &&
//            false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
//        ) {
//            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
//        }
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        set_time_limit(1800); //1800 seconds => 30 min
        ini_set('memory_limit', '3072M');

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $routeName = $request->get('_route');
        $title = "Work Requests";
        $formnode = false;

        $advancedFilter = 0;

        //get allowed and denied projectSpecialties
        $projectSpecialtyAllowedRes = $transresUtil->getAllowedProjectSpecialty($user);
        $projectSpecialtyAllowedArr = $projectSpecialtyAllowedRes['projectSpecialtyAllowedArr'];
        $projectSpecialtyDeniedArr = $projectSpecialtyAllowedRes['projectSpecialtyDeniedArr'];

        if( count($projectSpecialtyAllowedArr) == 0 ) {
            $sysAdminEmailArr = $transresUtil->getTransResAdminEmails(true,true);
            $errorMsg = "You don't have any allowed project specialty in your profile.".
                "<br>Please contact the system admin(s):".
                "<br>".implode(", ",$sysAdminEmailArr);
            //exit($errorMsg);
            //no allowed specialty
            return array(
                'filterError' => true,
                'title' => $errorMsg,
            );
        }

        //////// create filter //////////
        $availableProjects = $transresUtil->getAvailableRequesterOrReviewerProjects();
        $progressStateArr = $transresRequestUtil->getProgressStateArr();
        $billingStateArr = $transresRequestUtil->getBillingStateArr();
        $transresUsers = $transresUtil->getAppropriatedUsers();

        //add "All except Drafts"
        $progressStateArr["All except Drafts"] = "All-except-Drafts";

        if( $timer ) {
            $stopwatch->start('FilterRequestType');
        }

        $params = array(
            'transresUsers' => $transresUsers,
            'progressStateArr'=>$progressStateArr,
            'billingStateArr'=>$billingStateArr,
            'routeName'=>$routeName,
            'projectSpecialtyAllowedArr' => $projectSpecialtyAllowedArr,
            'availableProjects' => $availableProjects
        );
        $filterform = $this->createForm(FilterRequestType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));

        if( $timer ) {
            $event = $stopwatch->stop('FilterRequestType');
            echo "FilterRequestType duration: ".($event->getDuration()/1000)." sec<br>";

            $stopwatch->start('handleRequest');
        }

        $filterform->handleRequest($request);

        if( $timer ) {
            $event = $stopwatch->stop('handleRequest');
            echo "handleRequest duration: ".($event->getDuration()/1000)." sec<br>";
        }

        $submitter = null;
        $project = null;

        if( $timer ) {
            $stopwatch->start('getFilterData');
        }

        $requestId = $filterform['requestId']->getData();
        $externalId = $filterform['externalId']->getData();
        $submitter = $filterform['submitter']->getData();
        $progressStates = $filterform['progressState']->getData();
        $billingStates = $filterform['billingState']->getData();
        $category = $filterform['category']->getData();
        $projectSpecialties = $filterform['projectSpecialty']->getData();
        $projectFilter = $filterform['project']->getData();

        $searchStr = $filterform['comment']->getData();
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $principalInvestigators = $filterform['principalInvestigators']->getData();
        //$accountNumber = $filterform['accountNumber']->getData();
        $billingContact = $filterform['billingContact']->getData();
        $fundingNumber = $filterform['fundingNumber']->getData();
        $fundingType = $filterform['fundingType']->getData();
        $filterType = trim( $request->get('type') );
        $filterTitle = trim( $request->get('title') );

        //replace - with space
        $filterType = str_replace("-"," ",$filterType);

        //$showMatchingAndTotal = $filterform['showMatchingAndTotal']->getData();
        //echo "filterType=$filterType<br>";
        //exit();

        if( isset($filterform['submitter']) ) {
            $submitter = $filterform['submitter']->getData();
        }
        if( isset($filterform['project']) ) {
            $project = $filterform['project']->getData();
        }
//        if( isset($filterform['projectSpecialty']) ) {
//            $projectSpecialties = $filterform['projectSpecialty']->getData();
//        } else {
//            $projectSpecialties = $projectSpecialtyAllowedArr;
//        }

        if( $timer ) {
            $event = $stopwatch->stop('getFilterData');
            echo "getFilterData duration: ".($event->getDuration()/1000)." sec<br>";
        }
        //////// EOF create filter //////////

        //echo "project=".$project."<br>";
        //echo "project ID=".$project->getId()."; INFO=".$project->getProjectInfoName()."<br>";
        //exit('project='.$project);


        //force to set project specialty filter for non-admin users
        if( $transresUtil->isAdminOrPrimaryReviewer() === false ) {

            //TODO: fix no specialty cases
            if( 0 && count($projectSpecialties) == 0 ) {
                //echo "allowed spec=".count($projectSpecialtyAllowedArr)."<br>";
                //echo "filterType=".$filterType."<br>";
                $projectSpecialtyReturn = $transresUtil->getReturnIndexSpecialtyArray($projectSpecialtyAllowedArr,$project,$filterType);
                //print_r($projectSpecialtyReturn);
                //exit("no spec");
                return $this->redirectToRoute(
                    $routeName,
                    $projectSpecialtyReturn
                );
            }

            if( count($projectSpecialties) == 0 ) {
                $projectSpecialties = $projectSpecialtyAllowedArr;
            }

            //if specialty contains $projectSpecialtyDeniedArr => exit
            foreach($projectSpecialties as $thisProjectSpecialty) {
                if( $projectSpecialtyDeniedArr->contains($thisProjectSpecialty) ) {
                    $this->get('session')->getFlashBag()->add(
                        'warning',
                        "You project specialty $thisProjectSpecialty conflicting with your allowed specialty"
                    );

                    return $this->redirect($this->generateUrl($this->container->getParameter('translationalresearch.sitename') . '-nopermission'));
                }
            }

        }//if not admin

        //Non admin, Primary Reviewers and Executive can see all projects.
        // All other users can view only their projects (where they are requesters: PI, Pathologists Involved, Co-Investigators, Contacts, Billing Contacts)
        if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() || $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN') ) {
            $showOnlyMyProjects = false;
        } else {
            $showOnlyMyProjects = true;
        }
        if( $submitter ) {
            $showOnlyMyProjects = false;
        }
        //echo 'showOnlyMyProjects='.$showOnlyMyProjects."<br>";

        //////////////// get Requests IDs with the form node filter ////////////////
        $ids = array();
        if( $formnode ) {
            //echo "use formnode<br>";
            if ($category) {
                $categoryIds = $transresRequestUtil->getRequestIdsFormNodeByCategory($category);
                $ids = array_merge($ids, $categoryIds);
            }
            if ($searchStr) {
                $commentIds = $transresRequestUtil->getRequestIdsFormNodeByComment($searchStr);
                $ids = array_merge($ids, $commentIds);
            }
        }
        if( count($ids) > 0 ) {
            $ids = array_unique($ids);
            //print_r($ids);
        }
        //////////////// EOF get Requests IDs with the form node filter ////////////////

        //exit('start filtering requests');

        if( $filterType ) {
            $filterTypeDone = false;

            if( $filterType == "All Requests (including Drafts)" ) {
                $title = "All Work Requests (including Drafts)";
                $filterTypeDone = true;
            }
            if( $filterType == "All Requests" ) {
                //$title = "All Work Requests";
                //$filterTypeDone = true;
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[progressState][0]' => "All-except-Drafts",
                        'title' => "All Work Requests",
                    )
                );
            }

//            if( $filterType == "My Requests" ) {
//                //exit('start filtering '.$filterType);
//                return $this->redirectToRoute(
//                    'translationalresearch_request_index_filter',
//                    array(
//                        'filter[submitter]' => $user->getId(),
//                        'title' => $filterType,
//                    )
//                );
//            }
            if( $filterType == "My Submitted Requests" ) {
                //exit('start filtering '.$filterType);
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[submitter]' => $user->getId(),
                        'title' => $filterType,
                    )
                );
            }

            $titleAdd = "";
            //set filter's progressState or add a filter option "my projects only"
            if( $filterType == "Submitted Requests for My Projects" ) {
                //exit('start filtering '.$filterType);
                //where I'm a project's requester
                $filterTypeDone = true;
                $showOnlyMyProjects = true;
                $progressStates = array('All-except-Drafts');
                $titleAdd = "All Except Draft ";

//                return $this->redirectToRoute(
//                    'translationalresearch_request_index_filter',
//                    array(
//                        'filter[submitter]' => $user->getId(),
//                        'title' => $filterType,
//                    )
//                );
            }
            //set filter's progressState or add a filter option "my projects only"
            if( $filterType == "Draft Requests for My Projects" ) {
                //exit('start filtering '.$filterType);
                //where I'm a project's requester
                $filterTypeDone = true;
                $showOnlyMyProjects = true;
                $progressStates = array('draft');
                $titleAdd = "Draft ";
            }
            if( $filterType == "My Draft Requests" ) {
                //exit('start filtering '.$filterType);
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[submitter]' => $user->getId(),
                        'filter[progressState][0]' => "draft",
                        'title' => $filterType,
                    )
                );
            }

            if( $filterType == "All AP/CP Requests" ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("ap-cp");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "All-except-Drafts",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "All Hematopathology Requests" ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("hematopathology");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "All-except-Drafts",
                        'title' => $filterType,
                    )
                );
            }

            //"Pending" is all status except, Canceled, Completed, CompletedNotified
            if( $filterType == "All Pending Requests" ) {
                $pendingRequestArr = $transresRequestUtil->getFilterPendingRequestArr($filterType);

                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    $pendingRequestArr
                );
            }
            if( $filterType == "All AP/CP Pending Requests" ) {
                $pendingRequestArr = $transresRequestUtil->getFilterPendingRequestArr($filterType);

                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("ap-cp");
                $pendingRequestArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();

                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    $pendingRequestArr
                );
            }
            if( $filterType == "All Hematopathology Pending Requests" ) {
                $pendingRequestArr = $transresRequestUtil->getFilterPendingRequestArr($filterType);

                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("hematopathology");
                $pendingRequestArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();

                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    $pendingRequestArr
//                    array(
//                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
//                        'filter[progressState][0]' => "active",
//                        'filter[progressState][1]' => "investigator",
//                        'filter[progressState][2]' => "histo",
//                        'filter[progressState][3]' => "ihc",
//                        'filter[progressState][4]' => "mol",
//                        'filter[progressState][5]' => "retrieval",
//                        'filter[progressState][6]' => "payment",
//                        'filter[progressState][7]' => "slidescanning",
//                        'filter[progressState][8]' => "block",
//                        'filter[progressState][9]' => "other",
//                        'title' => $filterType,
//                    )
                );
            }

            if( $filterType == "All Active Requests" ) {
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[progressState][0]' => "active",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "All AP/CP Active Requests" ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("ap-cp");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "active",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "All Hematopathology Active Requests" ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("hematopathology");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "active",
                        'title' => $filterType,
                    )
                );
            }

            if( $filterType == "All Completed Requests" ) {
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[progressState][0]' => "completed",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "All AP/CP Completed Requests" ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("ap-cp");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "completed",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "All Hematopathology Completed Requests" ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("hematopathology");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "completed",
                        'title' => $filterType,
                    )
                );
            }

            if( $filterType == "All Completed and Notified Requests" ) {
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[progressState][0]' => "completedNotified",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "All AP/CP Completed and Notified Requests" ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("ap-cp");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "completedNotified",
                        'title' => $filterType,
                    )
                );
            }
            if( $filterType == "All Hematopathology Completed and Notified Requests" ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("hematopathology");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "completedNotified",
                        'title' => $filterType,
                    )
                );
            }

            //not pre-set filter
//            if( $filterType != "All Requests" ) {
//                $this->get('session')->getFlashBag()->add(
//                    'notice',
//                    "Filter pre-set type '$filterType' is not defined"
//                );
//            }

            if( !$filterTypeDone ) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    "Filter pre-set type '$filterType' is not defined"
                );
                exit("Filter Type not known ".$filterType);
            }
        }

        //exit("Start filtering...");

        if( $timer ) {
            //$time_pre2 = microtime(true);
            $stopwatch->start('createQueryBuilder');
        }

        $repository = $em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("transresRequest");
        $dql->select('transresRequest');

        $dql->leftJoin('transresRequest.submitter','submitter');
        $dql->leftJoin('transresRequest.project','project');
        $dql->leftJoin('submitter.infos','submitterInfos');
        $dql->leftJoin('transresRequest.principalInvestigators','principalInvestigators');

        $dqlParameters = array();

//        if( $filterType ) {
//            if( $filterType == "My Requests" ) {
//                $title = "My Requests";
//                $dql->andWhere("submitter.id = :submitterId");
//                $dqlParameters["submitterId"] = $user->getId();
//            }
//        }

//        if( $routeName == "translationalresearch_all_requests" ) {
//            $title = "All Requests";
//        }

        ///////// filters //////////
        if( $showOnlyMyProjects ) {

            $submitter = null;

            $dql->leftJoin('transresRequest.contact','contact');
            $dql->leftJoin('project.principalInvestigators','projectPrincipalInvestigators');
            $dql->leftJoin('project.principalIrbInvestigator','projectPrincipalIrbInvestigator');
            $dql->leftJoin('project.coInvestigators','projectCoInvestigators');
            $dql->leftJoin('project.pathologists','projectPathologists');
            $dql->leftJoin('project.billingContact','projectBillingContact');
            $dql->leftJoin('project.contacts','projectContacts');
            $dql->leftJoin('project.submitter','projectSubmitter');

            $dql->andWhere(
                //Request requesters
                "principalInvestigators.id = :userId OR ".
                "contact.id = :userId OR ".
                "submitter.id = :userId OR ".
                //project's requesters
                "projectPrincipalInvestigators.id = :userId OR ".
                "projectPrincipalIrbInvestigator.id = :userId OR ".
                "projectCoInvestigators.id = :userId OR ".
                "projectPathologists.id = :userId OR ".
                "projectContacts.id = :userId OR ".
                "projectBillingContact.id = :userId OR ".
                "projectSubmitter.id = :userId"

            );

            $dqlParameters["userId"] = $user->getId();
            $filterTitle = $titleAdd."Requests for My Project (where I am a requester directly or where I am a project's requester)";
        }

//        if( $projectSpecialty ) {
//            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
//            $dql->andWhere("projectSpecialty.id = :projectSpecialtyId");
//            $dqlParameters["projectSpecialtyId"] = $projectSpecialty->getId();
//        }
        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
            $projectSpecialtyIdsArr = array();
            foreach($projectSpecialties as $projectSpecialty) {
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        }

        if( $projectFilter ) {
            $dql->andWhere("project.id = :projectId");
            $dqlParameters["projectId"] = $projectFilter->getId();
        }

        if( $submitter ) {
            $dql->andWhere("submitter.id = :submitterId");
            $dqlParameters["submitterId"] = $submitter->getId();
        }

        if( $project ) {
            $dql->andWhere("project.id = :projectId");
            $dqlParameters["projectId"] = $project->getId();
        }

        if( $progressStates && count($progressStates) > 0 ) {
            $allExceptDraft = "";
            if( in_array("All-except-Drafts", $progressStates )) {
                $allExceptDraft = " OR transresRequest.progressState != 'draft' OR transresRequest.progressState IS NULL";
            }
            $dql->andWhere("transresRequest.progressState IN (:progressStates)".$allExceptDraft);
            $dqlParameters["progressStates"] = $progressStates;
        }

        if( $billingStates && count($billingStates)>0 ) {
            //$dql->andWhere("transresRequest.billingState IN (:billingStates)");
            //$dqlParameters["billingStates"] = implode(",",$billingStates);
            $dql->andWhere("transresRequest.billingState IN (:billingStates)");
            $dqlParameters["billingStates"] = $billingStates;
        }

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('transresRequest.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }
        if( $endDate ) {
            $endDate->modify('+1 day');
            $dql->andWhere('transresRequest.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }

        if( $billingContact ) {
            $dql->andWhere("contact.id = :billingContactId");
            $dqlParameters["billingContactId"] = $billingContact->getId();
            $advancedFilter++;
        }

        if( $externalId ) {
            //echo $externalId."<br>";
            $dql->andWhere('transresRequest.exportId LIKE :exportId');
            $dqlParameters['exportId'] = "%".$externalId."%";
            $advancedFilter++;
        }

        if( $requestId ) {
            $dql->andWhere('transresRequest.oid LIKE :requestId');
            $dqlParameters['requestId'] = "%".$requestId."%";
        }

//        if( $accountNumber ) {
//            $dql->andWhere("transresRequest.fundedAccountNumber = :fundedAccountNumber");
//            $dqlParameters["fundedAccountNumber"] = $accountNumber;
//            $advancedFilter++;
//        }

        if( $fundingNumber ) {
            $dql->andWhere("transresRequest.fundedAccountNumber LIKE :fundedAccountNumber");
            $dqlParameters["fundedAccountNumber"] = "%".$fundingNumber."%";
            $advancedFilter++;
        }

        if( $fundingType ) {
            //echo "fundingType=" . $fundingType . "<br>";
            if( $fundingType == "Funded" ) {
                $dql->andWhere("transresRequest.fundedAccountNumber IS NOT NULL");
                $advancedFilter++;
            }
            if( $fundingType == "Non-Funded" ) {
                $dql->andWhere("transresRequest.fundedAccountNumber IS NULL");
                $advancedFilter++;
            }
        }

        if( $principalInvestigators && count($principalInvestigators)>0 ) {
            $dql->andWhere("principalInvestigators.id IN (:principalInvestigators)");
            $principalInvestigatorsIdsArr = array();
            foreach($principalInvestigators as $principalInvestigator) {
                $principalInvestigatorsIdsArr[] = $principalInvestigator->getId();
            }
            $dqlParameters["principalInvestigators"] = $principalInvestigatorsIdsArr;   //implode(",",$principalInvestigatorsIdsArr);
            $advancedFilter++;
        }

        if( !$formnode ) {
            $dql->leftJoin('transresRequest.products','products');
            if ($category) {
                $dql->leftJoin('products.category','category');
                $dql->andWhere("category.id = :categoryId");
                $dqlParameters["categoryId"] = $category;
            }
            if ($searchStr) {
                //$dql->andWhere("(category.name LIKE :categoryStr OR category.productId LIKE :categoryStr OR category.feeUnit LIKE :categoryStr OR category.fee LIKE :categoryStr)");
                $dql->andWhere("products.comment LIKE :searchStr");
                $dqlParameters["searchStr"] = "%".$searchStr."%";
                $advancedFilter++;
            }
        }

        if( count($ids) > 0 ) {
            //echo "using ids <br>";
            //$dql->andWhere("transresRequest.id IN (:ids)");
            //$dqlParameters["ids"] = implode(",",$ids);
            $dql->andWhere("transresRequest.id IN (:ids)");
            $dqlParameters["ids"] = $ids;
        }
        ///////// EOF filters //////////

        //echo "showMatchingAndTotal=".$showMatchingAndTotal."<br>";
//        if( $showMatchingAndTotal == "WithTotal" ) {
//            $withMatching = true; //slower 7.5 sec
//            $advancedFilter++;
//        } else {
//            $withMatching = false; //twice faster 3.5 sec
//        }
        //$withMatching = true; //slower 7.5 sec
        //$withMatching = false; //twice faster 3.5 sec
        
        $dql->groupBy("transresRequest");

        //testing
        $dql->andWhere("transresRequest.id = 2");

        $limit = 10;
        $query = $em->createQuery($dql);

        //doctrine cache queries
        $query->useQueryCache(true);
        $query->useResultCache(true);

        //if($withMatching) {
            //$query2 = $em->createQuery($dql);
        //}

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
            //if($withMatching) {
                //$query2->setParameters($dqlParameters);
            //}
        }

        //echo "query=".$query->getSql()."<br>";

        //$allTransresRequests = $query->getResult();

        $paginationParams = array(
            'defaultSortFieldName' => 'transresRequest.id',
            'defaultSortDirection' => 'DESC',
            'wrap-queries' => true
        );

        if( $timer ) {
            $event = $stopwatch->stop('createQueryBuilder');
            echo "createQueryBuilder duration: ".($event->getDuration()/1000)." sec<br>";

            //$time_pre2 = microtime(true);
            $stopwatch->start('PaginatorResult');
        }

        $paginator  = $this->get('knp_paginator');
        $transresRequests = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                                         /*limit per page*/
            $paginationParams
        );
        //echo "transresRequests count=".count($transresRequests)."<br>";

        if( $timer ) {
//            $time_post2 = microtime(true); //microseconds
//            $exec_time2 = round(($time_post2 - $time_pre2), 1);
//            echo "Paginator exec_time=$exec_time2<br>";

            $event = $stopwatch->stop('PaginatorResult');
            echo "PaginatorResult duration: ".($event->getDuration()/1000)." sec<br>";

            $event = $stopwatch->stop('Paginator');
            echo "Paginator duration: ".($event->getDuration()/1000)." sec<br>";
        }

        if( $filterTitle ) {
            $title = $filterTitle;
        }

        $withMatching = true;
        $withMatching = false;
        if( $withMatching ) {
            //Title
            $requestTotalFeeHtml = null;
            if( $project ) {
                $projectUrl = $this->container->get('router')->generate(
                    'translationalresearch_project_show',
                    array(
                        'id' => $project->getId(),
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $projectLink = "<a href=" . $projectUrl . ">" . "Project ID " . $project->getOid() . "</a>";
                //$title = "Requests for the project ID ".$project->getOid();
                $title = "Work Requests for " . $projectLink;

                $requestTotalFeeHtml = $transresRequestUtil->getTransResRequestTotalFeeHtml($project);
                if( $requestTotalFeeHtml ) {
                    $requestTotalFeeHtml = "; " . $requestTotalFeeHtml;
                }
            }

            if( $timer ) {
                $stopwatch->start('GetTitle');
            }

//        $withMatching = true;
//        $withMatching = false;
//        if($withMatching) {
            //$allFilteredTransresRequests = $query2->getResult();
            //echo "allFilteredTransresRequests=".count($allFilteredTransresRequests)."<br>";
            //$allGlobalRequests = $em->getRepository('OlegTranslationalResearchBundle:TransResRequest')->findAll();
            //$title = $title . " (Matching " . count($allTransresRequests) . ", Total " . count($allGlobalRequests) . ")";
            $allTransresRequests = $transresUtil->getTotalRequestCountByDqlParameters($dql,$dqlParameters);
            $allGlobalRequests = $transresUtil->getTotalRequestCount();
            $title = $title . " (Matching " . $allTransresRequests . ", Total " . $allGlobalRequests . $requestTotalFeeHtml . ")";

            if( $timer ) {
                $event = $stopwatch->stop('GetTitle');
                echo "GetTitle duration: " . ($event->getDuration() / 1000) . " sec<br>";
            }
        }

        //$allGlobalRequests = $em->getRepository('OlegTranslationalResearchBundle:TransResRequest')->findAll();
        //$title = $title . " (Matching " . count($allTransresRequests) . ", Total " . count($allGlobalRequests) . ")";

//        if( count($allTransresRequests) > 0 ) {
//            $pageNumber = $transresRequests->getCurrentPageNumber();
//            $items = $transresRequests->getItems();
//            $startPageItems = (intval($pageNumber) - 1) * intval($limit) + 1;
//            $endPageItems = intval($startPageItems) + count($items) - 1;
//            //echo "pageNumber=$pageNumber; items=".count($items)."; startPageItems=".$startPageItems."; endPageItems=".$endPageItems."<br>";
//            $title = $title . " (" . $startPageItems . " of " . $endPageItems . ", Total " . count($allTransresRequests) . ")";
//        } else {
//            $title = $title . " (Total " . count($allTransresRequests) . ")";
//        }

        if( $timer ) {
            $event = $stopwatch->stop('myRequestsAction');
            echo "myRequestsAction duration: ".($event->getDuration()/1000)." sec<br>";
            echo "myRequestsAction memory: ".($event->getMemory()/1000000)." MB<br>";
        }

        //Template: OlegTranslationalResearchBundle:Request:index.html.twig
        return array(
            //'filterDisable' => true, //testing
            'transresRequests' => $transresRequests,
            //'allTransresRequests' => $allTransresRequests,
            //'project' => null,
            'filterform' => $filterform->createView(),
            'title' => $title,
            'requestTotalFeeHtml' => null, //$requestTotalFeeHtml
            'advancedFilter' => $advancedFilter,
            'project' => $project,
            //'hideaction' => true,
            //'hiderows' => true,

        );
    }



    public function createRequestEntity($user,$transresRequest=null,$formnode=false) {

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        if( !$transresRequest ) {
            $transresRequest = new TransResRequest($user);
            $transresRequest->setVersion(1);
        }

        if( !$transresRequest->getInstitution() ) {
            $autoAssignInstitution = $userSecUtil->getAutoAssignInstitution();
            if( !$autoAssignInstitution ) {
                $autoAssignInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName('Pathology and Laboratory Medicine');
            }
            $transresRequest->setInstitution($autoAssignInstitution);
        }

        //set order category
        if( $formnode && !$transresRequest->getMessageCategory() ) {
            $categoryStr = "HemePath Translational Research Request";  //"Pathology Call Log Entry";
            //$categoryStr = "Nesting Test"; //testing
            $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
            if (!$messageCategory) {
                throw new \Exception("Message category is not found by name '" . $categoryStr . "'");
            }
            $transresRequest->setMessageCategory($messageCategory);
        }

        return $transresRequest;
    }
    public function createRequestForm( TransResRequest $transresRequest, $cycle, $request )
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->get('transres_request_util');
        $transResFormNodeUtil = $this->get('transres_formnode_util');
        $routeName = $request->get('_route');

        $billingStateChoiceArr = $transresRequestUtil->getBillingStateArr();
        $progressStateChoiceArr = $transresRequestUtil->getProgressStateArr();

        //categoryListLink
        $categoryListLink = null;
        if( $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ) {
            $categoryListUrl = $this->container->get('router')->generate(
                'transresrequestcategorytypes-list_translationalresearch',
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            //$categoryListLink = " (<a target='_blank' href=" . $categoryListUrl . ">" . "Category Type List Management" . "</a>)";
            //glyphicon glyphicon-wrench
            $categoryListLink = " <a data-toggle='tooltip' title='Category Type List Management' href=".$categoryListUrl."><span class='glyphicon glyphicon-wrench'></span></a>";
        }

        //for non-funded projects, show "Funding Number (Optional):"
        //transres_formnode_util.getProjectFormNodeFieldByName(project,"Funded")
        $project = $transresRequest->getProject();
        $fundedNumberLabel = "Fund Number:";
        //if( $project && !$transResFormNodeUtil->getProjectFormNodeFieldByName($project,"Funded") ) {
        if( $project && !$project->getFunded() ) {
            $fundedNumberLabel = "Fund Number (Optional):";
        }

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'transresUtil' => $transresUtil,
            'SecurityAuthChecker' => $this->get('security.authorization_checker'),
            'transresRequest' => $transresRequest,
            'routeName' => $routeName,
            'saveAsUpdate' => false,
            'saveAsDraft' => false,
            'saveAsComplete' => false,
            'updateRequest' => false,
            //'projects' => null,
            'availableProjects' => null,
            'billingStateChoiceArr' => $billingStateChoiceArr,
            'progressStateChoiceArr' => $progressStateChoiceArr,
            'categoryListLink' => $categoryListLink,
            'fundedNumberLabel' => $fundedNumberLabel
        );

        $params['admin'] = false;

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            $params['admin'] = true;
        } else {
            //TODO: do not add reviewers
        }

        $disabled = false;

        if( $cycle == "new" ) {
            $disabled = false;
            $params['saveAsDraft'] = true;
            $params['saveAsComplete'] = true;

            if( $routeName == "translationalresearch_new_standalone_request" ) {
                $availableProjects = $transresUtil->getAvailableProjects(true,true,true,false);
                $params['availableProjects'] = $availableProjects;
            }
        }

        if( $cycle == "show" ) {
            $disabled = true;
            //$params['updateRequest'] = true;
        }

        if( $cycle == "edit" ) {
            $disabled = false;
            if( $transresRequest->getProgressState() != 'draft' ) {
                $params['saveAsDraft'] = true;
            }
            if( $transresRequest->getProgressState() == 'draft' ) {
                $params['saveAsComplete'] = true;
            }
            if( $transresRequest->getProgressState() == 'draft' ) {
                $params['saveAsUpdate'] = true;
            }

            //Make sure that the Request can be edited only in the "Draft" stage. Admin can edit the Request in any stage.
            if( $params['admin'] ) {
                $params['saveAsUpdate'] = true;
            }
        }

        if( $cycle == "set-state" ) {
            $disabled = false;
        }

        $form = $this->createForm(TransResRequestType::class, $transresRequest, array(
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));

        return $form;
    }



    /**
     * @Route("/request/generate-form-node-tree/", name="translationalresearch_generate_form_node_tree_request")
     * @Method("GET")
     */
    public function generateFormNodeAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transResFormNodeUtil = $this->get('transres_formnode_util');
        $count = $transResFormNodeUtil->generateTransResFormNodeRequest();

        exit("Form Node Tree generated: ".$count);
    }




    /**
     * Finds and displays a progress review form for this request entity.
     *
     * @Route("/request/progress/review/{id}", name="translationalresearch_request_review_progress_state")
     * @Template("OlegTranslationalResearchBundle:Request:review.html.twig")
     * @Method("GET")
     */
    public function reviewProgressAction(Request $request, TransResRequest $transresRequest)
    {
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        if(
            $transresRequestUtil->isRequestProgressReviewable($transresRequest) && //check state
            (
                $transresUtil->isAdminOrPrimaryReviewer() ||
                $transresRequestUtil->isRequestProgressReviewer($transresRequest)
            )
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "show";

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //show progress review

        $eventType = "Request Viewed";
        $msg = "Request ".$transresRequest->getOid() ." has been viewed on the progress review page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        //get Table $jsonData
        $jsonData = $this->getTableData($transresRequest);

        $project = $transresRequest->getProject();

        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'statMachineType' => 'progress',
            'title' => "Progress Review Request ".$transresRequest->getOid(),
            'handsometableData' => $jsonData
        );
    }

    /**
     * Finds and displays a billing review form for this request entity.
     *
     * @Route("/request/billing/review/{id}", name="translationalresearch_request_review_billing_state")
     * @Template("OlegTranslationalResearchBundle:Request:review.html.twig")
     * @Method("GET")
     */
    public function reviewBillingAction(Request $request, TransResRequest $transresRequest)
    {
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        if(
            $transresRequestUtil->isRequestProgressReviewable($transresRequest) && //check state
            (
                $transresUtil->isAdminOrPrimaryReviewer() ||
                $transresRequestUtil->isRequestProgressReviewer($transresRequest)
            )
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "show";

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //show billing review

        $eventType = "Request Viewed";
        $msg = "Request ".$transresRequest->getOid() ." has been viewed on the billing review page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        return array(
            'transresRequest' => $transresRequest,
            'project' => $transresRequest->getProject(),
            'form' => $form->createView(),
            'cycle' => $cycle,
            'statMachineType' => 'billing',
            'title' => "Billing Review Request ".$transresRequest->getOid(),
        );
    }


    /**
     * @Route("/request/update-irb-exp-date/", name="translationalresearch_update_irb_exp_date", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function updateIrbExpDateAction( Request $request ) {
        //set permission: project irb reviewer or admin
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$transresRequestUtil = $this->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        //$userServiceUtil = $this->container->get('user_service_utility');
        $res = "NotOK";

        $projectId = trim( $request->get('projectId') );
        $project = $em->getRepository('OlegTranslationalResearchBundle:Project')->find($projectId);

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if(
            $transresUtil->isAdminOrPrimaryReviewer() ||
            $this->isReviewsReviewer($user,$project->getIrbReviews())
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if( $project ) {
            $originalIrbExpDateStr = "Unknown";
            if( $project->getIrbExpirationDate() ) {
                $originalIrbExpDateStr = $project->getIrbExpirationDate()->format('m/d/Y');
            }

            $value = trim($request->get('value'));
            //echo "value=".$value."<br>";
            $irbExpDate = \DateTime::createFromFormat('m/d/Y', $value);
            //$irbExpDate = $userServiceUtil->convertFromUtcToUserTimezone($irbExpDate,$user);
            //echo "value=".$irbExpDate->format("m/d/Y H:i:s")."<br>";
            $project->setIrbExpirationDate($irbExpDate);

            //$receivingObject = $transresRequestUtil->setValueToFormNodeProject($project, "IRB Expiration Date", $value);
            //echo "value=".$value."<br>";
            //$valueDateTime = \DateTime::createFromFormat('m/d/Y',$value);
            //$project->setIrbExpirationDate($valueDateTime);

            //$em->flush($receivingObject);
            //$em->flush($project);
            $em->flush();

            //add eventlog changed IRB
            $eventType = "Project Updated";
            $res = "Project ID ".$project->getOid() ." has been updated: ".
                "IRB Expiration Date changed form ".$originalIrbExpDateStr." to ".$value;
            $transresUtil->setEventLog($project,$eventType,$res);
        }

        $response = new Response($res);
        return $response;
    }


    /**
     * Deletes a request entity.
     *
     * @Route("/delete-multiple-requests/", name="translationalresearch_requests_multiple_delete")
     * @Method("GET")
     */
    public function deleteMultipleProjectsAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        set_time_limit(1800); //1800 seconds => 30 min
        ini_set('memory_limit', '2048M');

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin('request.principalInvestigators','principalInvestigators');

        $dql->andWhere("request.exportId IS NOT NULL");
        //$dql->andWhere("project.oid IS NULL");
        //$dql->andWhere("principalInvestigators.id IS NULL");

        $query = $dql->getQuery();

        $requests = $query->getResult();
        echo "requests count=".count($requests)."<br>";

        foreach($requests as $transresRequest) {
            $this->deleteRequest($transresRequest);
            //exit('111');
        }

        exit("EOF deleteMultipleRequestsAction");
        return $this->redirectToRoute('translationalresearch_project_index');
    }
    public function deleteRequest( $transresRequest ) {
        echo $transresRequest->getId().": Delete request OID=".$transresRequest->getOid()."<br>";
        $em = $this->getDoctrine()->getManager();

        //principalInvestigators
        //foreach( $transresRequest->getPrincipalInvestigators() as $pi) {
        //    $transresRequest->removePrincipalInvestigator($pi);
        //}

        //delete documents

        $project = $transresRequest->getProject();
        $project->removeRequest($transresRequest);
        $transresRequest->setProject(null);

        $em->remove($transresRequest);
        $em->flush();
    }

}