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

namespace App\TranslationalResearchBundle\Controller;



use App\OrderformBundle\Helper\ErrorHelper;
use App\TranslationalResearchBundle\Entity\RequestCategoryTypeList; //process.py script: replaced namespace by ::class: added use line for classname=RequestCategoryTypeList


use App\TranslationalResearchBundle\Entity\BusinessPurposeList; //process.py script: replaced namespace by ::class: added use line for classname=BusinessPurposeList


use App\UserdirectoryBundle\Entity\Document; //process.py script: replaced namespace by ::class: added use line for classname=Document


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution


use App\OrderformBundle\Entity\MessageCategory; //process.py script: replaced namespace by ::class: added use line for classname=MessageCategory


use App\TranslationalResearchBundle\Entity\PriceTypeList; //process.py script: replaced namespace by ::class: added use line for classname=PriceTypeList


use App\TranslationalResearchBundle\Entity\SpecialtyList; //process.py script: replaced namespace by ::class: added use line for classname=SpecialtyList


use App\UserdirectoryBundle\Entity\PlatformListManagerRootList; //process.py script: replaced namespace by ::class: added use line for classname=PlatformListManagerRootList

use App\TranslationalResearchBundle\Form\FeeFilterType;
use App\UserdirectoryBundle\Entity\EventObjectTypeList;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use App\OrderformBundle\Form\DataTransformer\AccessionTypeTransformer;
use App\TranslationalResearchBundle\Entity\DataResult;
use App\TranslationalResearchBundle\Entity\Product;
use App\TranslationalResearchBundle\Entity\Project;
use App\TranslationalResearchBundle\Entity\TransResRequest;
use App\TranslationalResearchBundle\Form\FilterRequestType;
use App\TranslationalResearchBundle\Form\TransResRequestType;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use App\UserdirectoryBundle\Form\ListFilterType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
//use Symfony\Component\Stopwatch\Stopwatch;


/**
 * Request FormNode controller.
 */
class RequestController extends OrderAbstractController
{

    /**
     * Creates a new request entity with formnode.
     */
    #[Route(path: '/project/{id}/work-request/new/', name: 'translationalresearch_request_new', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[Route(path: '/work-request/new/', name: 'translationalresearch_new_standalone_request', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[Template('AppTranslationalResearchBundle/Request/new.html.twig')]
    public function newFormNodeAction(Request $request, Project $project=null)
    {
        //exit("Work request submission temporarily is not available. Please try again later in 5-10 min.");
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

//        if(
//            false == $this->isGranted('ROLE_TRANSRES_REQUESTER') &&
//            $transresUtil->isProjectRequester($project) === false
//        )
        if( false === $transresPermissionUtil->hasRequestPermission('create',null) ) {
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

        $availableProjects = NULL;

        if( $project ) {
            if( $transresRequestUtil->isRequestCanBeCreated($project) !== 1 ) {
                $this->addFlash(
                    'warning',
                    "You can not submit a new work request for an expired project"
                );
                return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            }
        }

        // $_GET: get project id parameter for standalone new work request page
        $projectId = NULL;
        $routeName = $request->get('_route');
        if( $routeName == "translationalresearch_new_standalone_request" ) {
            $projectId = $request->query->get('id');
            if( $projectId ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
                $project = $em->getRepository(Project::class)->find($projectId);
            }

            if( !$project ) {
                $availableProjects = $transresUtil->getAvailableProjects(true, true, true, false);
                if( count($availableProjects) > 0 ) {
                    $project = $availableProjects[0];
                }
            }
        }

        //echo "0 projectId=$projectId, project=".$project."<br>";
        
        if( $project ) {

            //exit('111');

            if( $transresRequestUtil->isRequestCanBeCreated($project) !== 1 ) {
                $this->addFlash(
                    'warning',
                    "You can not submit a new work request for an expired project"
                );
                return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            }

            $transresRequest->setProject($project);
            $title = "New Work Request for project ID ".$project->getOid();

            //if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            if( false === $transresPermissionUtil->hasRequestPermission('create',$transresRequest) ) {
//                $this->addFlash(
//                    'warning',
//                    "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
//                );
                return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            }

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
            if( $project->getImplicitExpirationDate() ) {
                $transresRequest->setSupportEndDate($project->getImplicitExpirationDate());
            }

            //pre-populate PIs
            $transreqPis = $project->getPrincipalInvestigators();
            foreach( $transreqPis as $transreqPi ) {
                $transresRequest->addPrincipalInvestigator($transreqPi);
            }

            //pre-populate "Business Purpose(s)" by Project's Type:
            //if project type = "USCAP Submission", set the default value for the Business Purpose of the new Work Request as "USCAP-related"
            if( $project->getProjectType() && $project->getProjectType()->getName() == "USCAP Submission" ) {
                $businessPurpose = $em->getRepository(BusinessPurposeList::class)->findOneByName("USCAP-related");
                //echo "businessPurpose=".$businessPurpose."<br>";
                if( $businessPurpose ) {
                    $transresRequest->addBusinessPurpose($businessPurpose);
                }
            }
        }

        $form = $this->createRequestForm($transresRequest,$cycle,$request,$availableProjects); //new

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

//        if( $form->isSubmitted() && $form->isValid() ) {
//            exit("submitted and valid");
//        } else {
//            exit("NOT submitted and valid");
//        }
//        if(0) {
//            if( $form->isValid() ) {
//                exit("valid");
//            } else {
//                $errorHelper = new ErrorHelper();
//                $errors = $errorHelper->getErrorMessages($form);
//                echo "<br>form errors:<br>";
//                dump($errors);
//
//                echo "<br>errors:<br>" . $form->getErrors() . "<br>";
//                //echo "errors as string=" . $form->getErrorsAsString() . "<br>";
//                exit("NOT valid");
//            }
//        }

        if( $form->isSubmitted() ) {

            if( $form->isValid() ) {
                //OK
            } else {
                //Error
                $errorHelper = new ErrorHelper();
                $errors = $errorHelper->getErrorMessages($form);
                $errorsStr = "N/A";
                if( is_array($errors) && count($errors) > 0 ) {
                    $errorsStr = json_encode($errors);
                }
                throw new \Exception("New work request: form is not valid. Error:".$errorsStr);
            }

            //exit("Request submitted"); //testing

            //testing process existingProjectGoals
            //$transresUtil->processExistingProjectGoals($transresRequest,$request,$form);

            $project = $transresRequest->getProject();

            //new: pre-populate Request's Support End Date by Project's IRB Expiration Date
            if( !$transresRequest->getSupportEndDate() ) {
                if( $project->getImplicitExpirationDate() ) {
                    $transresRequest->setSupportEndDate($project->getImplicitExpirationDate());
                }
            }

            //set project's funded account number
            $break = "<br>"; //text/html
            $changedMsg = "";

            //turn off update "do not update project's fund number related by work request fund number
            //use updateProjectFundNumber
            $updateProjectFundNumber = $transresRequestUtil->getTransresSiteParameter('updateProjectFundNumber',$transresRequest);
            if( $updateProjectFundNumber ) {
                $originalFundedAccountNumber = $project->getFundedAccountNumber();
                $fundedAccountNumber = $transresRequest->getFundedAccountNumber();
                if ($fundedAccountNumber && $fundedAccountNumber != $originalFundedAccountNumber) {
                    $project->setFundedAccountNumber($fundedAccountNumber);
                    $changedMsg = $changedMsg . $break . "Project's Account Fund Number has been updated: ";
                    $changedMsg = $changedMsg . $break . "Original account number " . $originalFundedAccountNumber;
                    $changedMsg = $changedMsg . $break . "New account number " . $project->getFundedAccountNumber();
                    $changedMsg = $break . $break . $changedMsg;
                }
            }

            //set submitter to product
            foreach($transresRequest->getProducts() as $product) {
                if( !$product->getSubmitter() ) {
                    $product->setSubmitter($user);
                }
            }

            $transresUtil->assignMinimumRequestRoles($transresRequest);

            $em->getRepository(Document::class)->processDocuments($transresRequest,"document");
            $em->getRepository(Document::class)->processDocuments($transresRequest,"packingSlipPdf");
            $em->getRepository(Document::class)->processDocuments($transresRequest,"oldPackingSlipPdf");

            $this->processTableData($transresRequest,$form,$user); //new

            if( $testing ) {
                echo "Btn clicked=".$form->getClickedButton()->getName()."<br>";
            }

            //new
            if ($form->getClickedButton() && 'saveAsDraft' === $form->getClickedButton()->getName()) {
                //Save Project as Draft => state='draft'
                $transresRequest->setProgressState('draft');
                $transresRequest->setBillingState('draft');

                //Every time a new work request is saved as draft for the first time, save the timestamp in both “Submitted on” field AND “Saved as Draft on” field
                $nowDate = new \DateTime();
                $transresRequest->setSavedAsDraftDate($nowDate);
                $transresRequest->setCreateDate($nowDate);
            }

            //new
            if ($form->getClickedButton() && 'saveAsComplete' === $form->getClickedButton()->getName()) {
                //Complete Submission => state='submit'
                $transresRequest->setProgressState('active');
                $transresRequest->setBillingState('active');
                
                $transresRequest->setCreateDate(new \DateTime()); //serve as submitted date
                $transresRequest->setSubmitter($user);
            }

            //set product's orderableStatus to "Requested" if not set
            $transresRequestUtil->setProductsStatus($transresRequest,"Requested"); //new

            if( !$testing ) {
                $em->persist($transresRequest);
                $em->flush();

                //set oid
                $transresRequest->generateOid();
                $em->flush();
            }

            //process form nodes
            if( $formnode ) {
                $formNodeUtil = $this->container->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request, $transresRequest->getMessageCategory(), $transresRequest, $testing);
            }

            //process existingProjectGoals
            $projectGoalMsg = $transresUtil->processExistingProjectGoals($transresRequest,$form);

            //send over budget email
            $overBudgetEmail = $transresUtil->sendProjectOverBudgetEmail($transresRequest); //new

            $msg = "New work request " . $transresRequest->getOid() . " has been submitted.";
            $msg = $msg . $changedMsg . $projectGoalMsg;
            $msg = str_replace($break,"<br>",$msg);

            if ($testing) {
                exit('form is submitted and finished, msg=' . $msg);
            }

//            if( $this->isGranted('ROLE_TRANSRES_ADMIN') ) {
//                $this->addFlash(
//                    'notice',
//                    $msg . $break . $break . $overBudgetEmail
//                );
//            } else {
//                $this->addFlash(
//                    'notice',
//                    $msg
//                );
//            }
            $this->addFlash(
                'notice',
                $msg
            );

            //////////// Event Log and Email for Create //////////////////
            //exit("create: ID=".$transresRequest->getOid()."; state=".$transresRequest->getProgressState());
            if( $transresRequest->getProgressState() == 'active' ) {
                $eventType = "Request Created";
                $msg = "New work request (" . $transresRequest->getOid() . ") has been submitted.";
                $msg = $msg . $changedMsg . $projectGoalMsg;

                $requestUrl = $transresRequestUtil->getRequestShowUrl($transresRequest);
                $msg = $msg . $break.$break . "To view this work request, please visit the link below: " . $break . $requestUrl;

                $msg = $msg . $break.$break .
                    "This request is being processed and a notification will be sent out once it has been completed and the deliverables (if any) are ready for pick up. There are no materials ready for pick up yet.";

                $subject = "New work request has been submitted (" . $transresRequest->getOid().")";
                $emailRes = $transresRequestUtil->sendRequestNotificationEmails($transresRequest, $subject, $msg, $testing);

                $transresUtil->setEventLog($transresRequest, $eventType, $emailRes);
            }
            //////////// EOF Event Log and Email for Create //////////////////

            return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
        }


        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'defaultAccessionType' => null,
            'defaultAntibodyType' => null,
            'handsometableData' => null,
            //'formnodetrigger' => $formnodetrigger,
            //'formnodeTopHolderId' => $formnodeTopHolderId,
            'routeName' => $request->get('_route')
        );
    }



    /**
     * Get TransResRequest Edit page
     */
    #[Route(path: '/work-request/edit/{id}', name: 'translationalresearch_request_edit', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Request/new.html.twig')]
    public function editAction(Request $request, TransResRequest $transresRequest)
    {
        //exit("Work request submission temporarily is not available. Please try again later in 5-10 min.");
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $formnode = false;
        $cycle = "edit";
        $formtype = "translationalresearch-request";

        $class = new \ReflectionClass($transresRequest);
        $className = $class->getShortName();          //ObjectTypeText
        $classNamespace = $class->getNamespaceName(); //App\UserdirectoryBundle\Entity

        $testing = false;
        //$testing = true;

        $project = $transresRequest->getProject();

        if( false === $transresPermissionUtil->hasRequestPermission('update',$transresRequest) ) {
            if( $transresUtil->isProjectRequester($project) && $transresRequest->getProgressState() != 'draft' ) {
                $stageLabel = $transresRequestUtil->getRequestStateLabelByName($transresRequest->getProgressState(), 'progress');
                $this->addFlash(
                    'warning',
                    "You can not edit this Working Request, because it's not in the Draft stage. Current stage is " . $stageLabel
                );
                //return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
                return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
            }
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

//            $updateInvoiceAnswer = $form->get('updateinvoiceanswer')->getData();
//            echo "updateInvoiceAnswer=".$updateInvoiceAnswer."<br>";
//            if ( $form->getClickedButton() ) {
//                echo "button clicked=".$form->getClickedButton()->getName()." => ";
//            }
//            exit("updateInvoiceAnswer=".$updateInvoiceAnswer);

            //set project's funded account number
            $changedMsg = "";
            $break = "<br>";

            //turn off update "do not update project's fund number related by work request fund number
            //use updateProjectFundNumber
            //$updateProjectFundNumber = false;
            $updateProjectFundNumber = $transresRequestUtil->getTransresSiteParameter('updateProjectFundNumber',$transresRequest);
            if( $updateProjectFundNumber ) {
                $originalFundedAccountNumber = $project->getFundedAccountNumber();
                $fundedAccountNumber = $transresRequest->getFundedAccountNumber();
                if ($fundedAccountNumber && $fundedAccountNumber != $originalFundedAccountNumber) {
                    $project->setFundedAccountNumber($fundedAccountNumber);
                    $changedMsg = $changedMsg . $break . "Project's Account Fund Number has been updated: ";
                    $changedMsg = $changedMsg . $break . "Original account number " . $originalFundedAccountNumber;
                    $changedMsg = $changedMsg . $break . "New account number " . $project->getFundedAccountNumber();
                }
            }

            //update updateBy
            $transresRequest->setUpdateUser($user);

            //edit: pre-populate Request's Support End Date by Project's IRB Expiration Date
            if( !$transresRequest->getSupportEndDate() ) {
                if( $project->getImplicitExpirationDate() ) {
                    $transresRequest->setSupportEndDate($project->getImplicitExpirationDate());
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
                    //don't delete $product entity because it might be still used in InvoiceItem entity
                    //$em->remove($product);
                }
            }

            $transresUtil->assignMinimumRequestRoles($transresRequest);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
            $em->getRepository(Document::class)->processDocuments($transresRequest,"document");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
            $em->getRepository(Document::class)->processDocuments($transresRequest,"packingSlipPdf");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
            $em->getRepository(Document::class)->processDocuments($transresRequest,"oldPackingSlipPdf");

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
            //echo "clicked btn=".$form->getClickedButton()->getName()."<br>";
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

                    $transresRequest->setCreateDate(new \DateTime()); //serve as submitted date
                    $transresRequest->setSubmitter($user);
                }
            }

            if( $transresRequest->getProgressState() == "completed" ) {
                $transresRequest->setCompletedBy($user);
            }
            if( $transresRequest->getProgressState() == "completedNotified" ) {
                $transresRequest->setCompletedBy($user);
            }

            //set product's orderableStatus to "Requested" if not set
            $transresRequestUtil->setProductsStatus($transresRequest,"Requested"); //edit

            if( !$testing ) {
                $em->persist($transresRequest);
                $em->flush();
            }

            //If Work Request’s Progress Status is changed
            $progressState = $transresRequest->getProgressState();
            if( $progressState != $originalProgressState ) {
                $transresRequestUtil->syncRequestStatus($transresRequest,$progressState,$testing);
                $transresRequestUtil->setOrderableStatusByWorkRequestStatus($transresRequest,$originalProgressState,$progressState);
            } else {
                //work status has not been changed implicitly - might be dangerous to silently change the status of the work request => do not use this function on WorkRequest edit
                //$transresRequestUtil->setWorkRequestStatusByOrderableStatus($transresRequest);
            }

            //testing
//            print "<pre>";
//            var_dump($_POST);
//            print "</pre><br>";
//            echo "formnode[420]=".$_POST['formnode[420]']."<br>";
//            echo "formnode[421]=".$_POST['formnode[421]']."<br>";
            
            //process form nodes
            if( $formnode ) {
                $formNodeUtil = $this->container->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request, $transresRequest->getMessageCategory(), $transresRequest, $testing); //testing
            }

            //TODO: get 'updateinvoiceanswer':
            //update => re-generate invoice
            //update-send => re-generate invoice and send
            //no-update => do nothing
            $updateInvoiceAnswer = $form->get('updateinvoiceanswer')->getData();
            $newInvoiceMsg = $transresRequestUtil->updateInvoiceByWorkRequest($transresRequest,$updateInvoiceAnswer);
//            if( $newInvoice ) {
//                $newInvoiceMsg = "";
//            }
//            if( $updateInvoiceAnswer == 'update' ) {
//                updateInvoiceByWorkRequest($transresRequest,$updateInvoiceAnswer);
//            }
//            if( $updateInvoiceAnswer == 'update-send' ) {
//
//            }

            //send over budget email
            $overBudgetEmail = $transresUtil->sendProjectOverBudgetEmail($transresRequest); //edit

            if( $changedMsg ) {
                $changedMsg = $break.$break . $changedMsg;
            }

            $msg = "Work Request ".$transresRequest->getOid()." has been updated.";
            $msg = $msg . $changedMsg;
            $msg = str_replace($break,"<br>",$msg);

            if( $testing ) {
                echo "ClickedButton=".$form->getClickedButton()->getName()."<br>";
                exit('form is submitted and finished, msg='.$msg);
            }

//            if( $this->isGranted('ROLE_TRANSRES_ADMIN') ) {
//                $this->addFlash(
//                    'notice',
//                    $msg . $break.$break . $overBudgetEmail . $break.$break . $newInvoiceMsg
//                );
//            } else {
//                $this->addFlash(
//                    'notice',
//                    $msg . $break.$break . $newInvoiceMsg
//                );
//            }
            if( $newInvoiceMsg ) {
                $newInvoiceMsg = $break.$break . $newInvoiceMsg;
            }
            $this->addFlash(
                'notice',
                $msg . $newInvoiceMsg
            );

            //////////// Event Log and Email for Update //////////////////
            $progressLabel = $transresRequestUtil->getRequestStateLabelByName($transresRequest->getProgressState(),"progress");
            $msg = "Work Request ".$transresRequest->getOid() ." has been updated.";
            $msg = $msg . $break.$break . "The request's current status is '$progressLabel'.";
            $msg = $msg . $changedMsg;

            $requestUrl = $transresRequestUtil->getRequestShowUrl($transresRequest);
            $msg = $msg . $break.$break . "To view this work request, please visit the link below: " . $break . $requestUrl;

            //update Request email
            if( $transresRequest->getProgressState() == 'active' ) {

                $msg = $msg . $break.$break .
                    "This request is being processed and a notification will be sent out once it has been completed and the deliverables (if any) are ready for pick up. There are no materials ready for pick up yet.";

                //exit("create: ID=".$transresRequest->getOid()."; state=".$transresRequest->getProgressState());
                $subject = "[CTP] Work Request " . $transresRequest->getOid() . " has been updated and its status was set to 'Active'.";
                $emailRes = $transresRequestUtil->sendRequestNotificationEmails($transresRequest, $subject, $msg, $testing);

                $msg = $emailRes;
            }

            $eventType = "Request Updated";
            $msg = str_replace($break,"<br>",$msg);
            $transresUtil->setEventLog($transresRequest,$eventType,$msg);
            //////////// EOF Event Log and Email for Update //////////////////

            //Update and Change the State
            if ($form->getClickedButton() && 'saveAsUpdateChangeProgressState' === $form->getClickedButton()->getName()) {
                //exit('saveAsUpdateChangeProgressState');
                return $this->redirectToRoute('translationalresearch_request_review_progress_state', array('id' => $transresRequest->getId()));
            }
            if ($form->getClickedButton() && 'saveAsUpdateChangeBillingState' === $form->getClickedButton()->getName()) {
                //exit('saveAsUpdateChangeBillingState');
                return $this->redirectToRoute('translationalresearch_request_review_billing_state', array('id' => $transresRequest->getId()));
            }

            return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
        }

        //$sitename = $this->getParameter('translationalresearch.sitename');
        //$defaultAccessionType = $userSecUtil->getSiteSettingParameter('accessionType',$sitename);
//        $projectSpecialty = $project->getProjectSpecialty();
//        $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();
//        $siteParameter = $transresRequestUtil->findCreateSiteParameterEntity($projectSpecialtyAbbreviation);
//        if( !$siteParameter ) {
//            throw new \Exception("SiteParameter is not found by specialty '" . $projectSpecialtyAbbreviation . "'");
//        }
//        $defaultAccessionType = $siteParameter->getAccessionType();
        $defaultAccessionType = $transresRequestUtil->getTransresSiteParameter('accessionType',$transresRequest);
        //echo "defaultAccessionType=".$defaultAccessionType."<br>";
        //exit('222');

        //defaultAntibodyType
        $defaultAntibodyType = null;

        $eventType = "Request Viewed";
        $msg = "Work Request ".$transresRequest->getOid() ." has been viewed on the edit page.";
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
            'sitename' => $this->getParameter('translationalresearch.sitename'),
            'routeName' => $request->get('_route'),
            //'handsometableData' => json_encode($jsonData)
            'handsometableData' => $jsonData,
            'defaultAccessionType' => $defaultAccessionType,
            'defaultAntibodyType' => $defaultAntibodyType
        );
    }

    //return created/updated array of DataResult objects existing in the Request
    public function processTableData( $transresRequest, $form, $user ) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
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


            $barcodeArr = $this->getValueByHeaderName('Sample Name',$row,$headers);
            $barcodeValue = $barcodeArr['val'];
            $barcodeId = $barcodeArr['id'];
            //echo "barcodeId=".$barcodeId." <br>";
            //echo "barcodeValue=".$barcodeValue." <br>";

            //$barcodeImageArr = $this->getValueByHeaderName('Barcode',$row,$headers);
            //$barcodeImageValue = $barcodeImageArr['val'];
            //$barcodeImageId = $barcodeImageArr['id'];

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
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:DataResult'] by [DataResult::class]
                    $dataResult = $em->getRepository(DataResult::class)->find($objectId);
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

        //$res['val'] = $row[$key]['value'];
        if( array_key_exists('value',$row[$key]) ) {
            $res['val'] = $row[$key]['value'];
        } else {
            $res['val'] = null;
        }

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
//                $systemStr = $system->getName();
//                $abbreviation = $system->getAbbreviation();
//                if( $abbreviation ) {
//                    $systemStr = $abbreviation;
//                }
                $rowArr['Source']['id'] = $system->getId();
                $rowArr['Source']['value'] = $system->getOptimalName(); //$systemStr;
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
            $rowArr['Sample Name']['id'] = $dataResult->getId();
            $rowArr['Sample Name']['value'] = $dataResult->getBarcode();

            //Comment
            $rowArr['Comment']['id'] = $dataResult->getId();
            $rowArr['Comment']['value'] = $dataResult->getComment();


            $jsonData[] = $rowArr;
        }

        return $jsonData;
    }

    /**
     * Displays the list of requests for the given project.
     */
    #[Route(path: '/work-request/show/{id}', name: 'translationalresearch_request_show', methods: ['GET'])]
    #[Route(path: '/work-request/show-with-packingslip/{id}', name: 'translationalresearch_request_show_with_packingslip', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Request/new.html.twig')]
    public function showAction(Request $request, TransResRequest $transresRequest)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->getUser();

        $cycle = "show";
        $project = $transresRequest->getProject();

//        if(
//            false === $this->isGranted('ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY') &&
//            false === $this->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP') &&
//            false == $this->isGranted('ROLE_TRANSRES_REQUESTER_HEMATOPATHOLOGY') &&
//            false == $this->isGranted('ROLE_TRANSRES_REQUESTER_APCP') &&
//            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN_HEMATOPATHOLOGY') &&
//            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN_APCP') &&
//            $transresUtil->isProjectRequester($project) === false
//        ) {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
//
//
//        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        if( false === $transresPermissionUtil->hasRequestPermission('view',$transresRequest) ) {
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
        $fee = $transresRequestUtil->getTransResRequestSubTotal($transresRequest);
        if( $fee ) {
            $feeHtml = " (fee $".$fee.")";
        }

        $eventType = "Request Viewed";
        $msg = "Work Request ".$transresRequest->getOid() ." has been viewed on the show review page.";
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
            'title' => "Work Request ".$transresRequest->getOid() . $feeHtml,
            'routeName' => $request->get('_route'),
            //'handsometableData' => json_encode($jsonData)
            'handsometableData' => $jsonData,
            'showPackingSlip' => $showPackingSlip,
            'defaultAccessionType' => null,
            'defaultAntibodyType' => null,
            //'delete_form' => $deleteForm->createView(),
            //'review_forms' => $reviewFormViews
        );
    }

    /**
     * Finds and displays all requests for the given project
     */
    #[Route(path: '/project/{id}/requests', name: 'translationalresearch_request_index', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Request/index.html.twig')]
    public function indexAction(Request $request, Project $project)
    {
//        if(
//            false === $this->isGranted('ROLE_TRANSRES_REQUESTER') &&
//            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
//        ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
//        }

        $transresUtil = $this->container->get('transres_util');

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //Redirect according project ID
//        return $this->redirectToRoute(
//            'translationalresearch_request_index_filter',
//            array(
//                'filter[project]' => $project->getId(),
//            )
//        );

        //Redirect according project ID by projectSearch //TODO: optimization search
        return $this->redirectToRoute(
            'translationalresearch_request_index_filter',
            array(
                'filter[projectSearch]' => $project->getOid(false), //$project->getProjectInfoNameWithPIsChoice(),
            )
        );
    }

    //OPTIMIZATION:
    //TRY composer: config: "optimize-autoloader": true
    /**
     * Finds and displays the filtered requests lists
     */
    #[Route(path: '/work-requests/list/', name: 'translationalresearch_request_index_filter', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Request/index.html.twig')]
    public function myRequestsAction(Request $request)
    {

        //max optimization: 3 sec (14 MB)
        //with matching: 17 sec (62 MB)
        //with matching and filter: 19 (28) sec (62 MB)
        //without matching and with filter: 18 sec (60 MB)
        //with matching and with filter (no project): 15 sec (60 MB)

        $withfilter = true;
        //$withfilter = false; //testing!!!

        $withMatching = true;
        //$withMatching = false; //testing!!!

        $timer = false;
        //$timer = true; //testing!!!

//        if( $timer ) {
//            $stopwatch = new Stopwatch();
//            //$time_pre = microtime(true);
//            $stopwatch->start('myRequestsAction');
//            $stopwatch->start('Paginator');
//        }

//        if(
//            false === $this->isGranted('ROLE_TRANSRES_REQUESTER') &&
//            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
//        ) {
//            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
//        }
        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        //set_time_limit(600); //600 seconds => 10 min
        //ini_set('memory_limit', '3072M');

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $routeName = $request->get('_route');
        $title = "Work Requests";
        $formnode = false;
        $titleAdd = "";


        //TESTING
        //return $this->testingReturn($request,$stopwatch);

        $advancedFilter = 0;

        //get allowed and denied projectSpecialties
        $projectSpecialtyAllowedRes = $transresUtil->getAllowedProjectSpecialty($user);
        $projectSpecialtyAllowedArr = $projectSpecialtyAllowedRes['projectSpecialtyAllowedArr'];
        $projectSpecialtyDeniedArr = $projectSpecialtyAllowedRes['projectSpecialtyDeniedArr'];

        if( count($projectSpecialtyAllowedArr) == 0 ) { //testing getTransResAdminEmails
            $sysAdminEmailArr = $transresUtil->getTransResAdminEmails(null,true,true); //send warning email if no specialty
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
        $requestId = null;
        $externalId = null;
        $submitter = null;
        $progressStates = null;
        $billingStates = null;
        $categories = null;
        $projectSpecialties = null;
        $projectFilter = null;
        $projectSearch = null;
        $searchStr = null;
        $startDate = null;
        $endDate = null;
        $principalInvestigators = null;
        $billingContact = null;
        $completedBy = null;
        $fundingNumber = null;
        $fundingType = null;
        $filterType = null;
        $filterTitle = null;
        $projectSpecialties = array();
        $submitter = null;
        $project = null;
        //$ids = array();
        $showOnlyMyProjects = false;
        $priceList = null;
        $workQueues = array();


        if( $withfilter ) {

            //total loading time 25 sec
            //loading time without users and projects filter reduces to 3 sec

        //$transresUsers = $transresUtil->getAppropriatedUsers();
        $transresUsers = array(); //testing users (removing users from the filter) //TODO: reduces loading time from 25 sec to 20 sec

        //$transresUsers = $em->getRepository('AppUserdirectoryBundle:User')->findNotFellowshipUsers();
        //TESTING
        //return $this->testingReturn($request,$stopwatch);

        //$availableProjects = $transresUtil->getAvailableRequesterOrReviewerProjects();
        //array() will disable whole project list loading and will enable typeahead. Other changes:
            //1) name="translationalresearch_request_index") => enable return with line 'filter[projectSearch]' => $project->getOid(false),
            //2) enable line: 'filter[projectSearch]' => $objectid
        $availableProjects = array(); //testing projects (removing project from the filter) //TODO: reduces loading time from 25 sec to 8 sec !!!

        $progressStateArr = $transresRequestUtil->getProgressStateArr();
        $billingStateArr = $transresRequestUtil->getBillingStateArr();

        //add "All except Drafts"
        $progressStateArr["All except Drafts"] = "All-except-Drafts";
        $progressStateArr["All except Drafts and Canceled"] = "All-except-Drafts-and-Canceled";

        //shown list only to users with Site Administrator, Technologist, Platform Administrator, and Deputy Platform Administrator" roles
        $showCompletedByUser = false;
        if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() || $this->isGranted('ROLE_TRANSRES_TECHNICIAN') ) {
            $showCompletedByUser = true;
        }

        $transresPricesList = $transresUtil->getPricesList();

        $trpAdminOrTech = false;
        if(
            $this->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            $trpAdminOrTech = true;
        }

        $params = array(
            //'SecurityAuthChecker' => $this->container->get('security.authorization_checker'),
            'trpAdminOrTech' => $trpAdminOrTech,
            'transresUsers' => $transresUsers,
            'progressStateArr' => $progressStateArr,
            'billingStateArr' => $billingStateArr,
            'routeName' => $routeName,
            'projectSpecialtyAllowedArr' => $projectSpecialtyAllowedArr,
            'availableProjects' => $availableProjects,
            'showCompletedByUser' => $showCompletedByUser,
            'transresPricesList' => $transresPricesList
        );
        $filterform = $this->createForm(FilterRequestType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));

        $filterform->handleRequest($request);

        //TESTING
        //return $this->testingReturn($request,$stopwatch);

//        if ($timer) {
//            $event = $stopwatch->stop('handleRequest');
//            echo "handleRequest duration: " . ($event->getDuration() / 1000) . " sec<br>";
//        }

        $submitter = null;
        $project = null;

//        if ($timer) {
//            $stopwatch->start('getFilterData');
//        }

        if(1) {
            if (isset($filterform['categories'])) {
                $categories = $filterform['categories']->getData();
            }

            if( isset($filterform['submitter']) ) {
                $submitter = $filterform['submitter']->getData();
            }
            //echo "submitter=".$submitter."<br>";

            $principalInvestigators = null;
            if( isset($filterform['principalInvestigators']) ) {
                $principalInvestigators = $filterform['principalInvestigators']->getData();
            }
            //echo "PIs=".$principalInvestigators."<br>";

            $billingContact = null;
            if( isset($filterform['billingContact']) ) {
                $billingContact = $filterform['billingContact']->getData();
            }

            $completedBy = null;
            if( isset($filterform['completedBy']) ) {
                $completedBy = $filterform['completedBy']->getData();
            }

            $requestId = $filterform['requestId']->getData();
            $externalId = $filterform['externalId']->getData();
            //$submitter = $filterform['submitter']->getData();
            $progressStates = $filterform['progressState']->getData();
            $billingStates = $filterform['billingState']->getData();
            //$categories = $filterform['categories']->getData();
            $projectSpecialties = $filterform['projectSpecialty']->getData();
            $searchStr = $filterform['comment']->getData();
            $sampleName = $filterform['sampleName']->getData();
            $startDate = $filterform['startDate']->getData();
            $endDate = $filterform['endDate']->getData();
            //$principalInvestigators = $filterform['principalInvestigators']->getData();
            //$accountNumber = $filterform['accountNumber']->getData();
            //$billingContact = $filterform['billingContact']->getData();
            $fundingNumber = $filterform['fundingNumber']->getData();
            $fundingType = $filterform['fundingType']->getData();
            $filterType = trim((string)$request->get('type'));
            $filterTitle = trim((string)$request->get('title'));

            //replace - with space
            //echo "filterType=$filterType <br>"; //All-COVID-19-Requests
            $filterType = str_replace("-", " ", $filterType);
            $filterType = str_replace("COVID 19","COVID-19",$filterType); //All COVID 19 Requests => All COVID-19 Requests
            $filterTypeLowerCase = strtolower($filterType);

            if( isset($filterform['project']) ) {
                $projectFilter = $filterform['project']->getData();
            }
            if(isset($filterform['projectSearch']) ) {
                $projectSearch = $filterform['projectSearch']->getData();
            }
            if(isset($filterform['priceList']) ) {
                $priceList = $filterform['priceList']->getData();
            }
            if(isset($filterform['workQueues']) ) {
                $workQueues = $filterform['workQueues']->getData();
            }

        }

        //$showMatchingAndTotal = $filterform['showMatchingAndTotal']->getData();
        //echo "filterType=$filterType<br>";
        //exit();

        if (isset($filterform['submitter'])) {
            $submitter = $filterform['submitter']->getData();
        }
        if (isset($filterform['project'])) {
            $project = $filterform['project']->getData();
        }
//        if( isset($filterform['projectSpecialty']) ) {
//            $projectSpecialties = $filterform['projectSpecialty']->getData();
//        } else {
//            $projectSpecialties = $projectSpecialtyAllowedArr;
//        }

//        if ($timer) {
//            $event = $stopwatch->stop('getFilterData');
//            echo "getFilterData duration: " . ($event->getDuration() / 1000) . " sec<br>";
//        }
        //////// EOF create filter //////////

        //echo "project=".$project."<br>";
        //echo "project ID=".$project->getId()."; INFO=".$project->getProjectInfoName()."<br>";
        //exit('project='.$project);


        //force to set project specialty filter for non-admin users
        if ($transresUtil->isAdminOrPrimaryReviewer() === false) {

            //TODO: fix no specialty cases
            if (0 && count($projectSpecialties) == 0) {
                //echo "allowed spec=".count($projectSpecialtyAllowedArr)."<br>";
                //echo "filterType=".$filterType."<br>";
                $projectSpecialtyReturn = $transresUtil->getReturnIndexSpecialtyArray($projectSpecialtyAllowedArr, $project, $filterType);
                //print_r($projectSpecialtyReturn);
                //exit("no spec");
                return $this->redirectToRoute(
                    $routeName,
                    $projectSpecialtyReturn
                );
            }

            if (count($projectSpecialties) == 0) {
                $projectSpecialties = $projectSpecialtyAllowedArr;
            }

            //if specialty contains $projectSpecialtyDeniedArr => exit
            foreach ($projectSpecialties as $thisProjectSpecialty) {
                if ($projectSpecialtyDeniedArr->contains($thisProjectSpecialty)) {
                    $this->addFlash(
                        'warning',
                        "You project specialty $thisProjectSpecialty conflicting with your allowed specialty"
                    );

                    return $this->redirect($this->generateUrl($this->getParameter('translationalresearch.sitename') . '-nopermission'));
                }
            }

        }//if not admin

        //Non admin, Primary Reviewers and Executive can see all projects.
        // All other users can view only their projects (where they are requesters: PI, Pathologists Involved, Co-Investigators, Contacts, Billing Contacts)
        if ($transresUtil->isAdminOrPrimaryReviewerOrExecutive() || $this->isGranted('ROLE_TRANSRES_TECHNICIAN')) {
            $showOnlyMyProjects = false;
        } else {
            $showOnlyMyProjects = true;
        }
        if ($submitter) {
            $showOnlyMyProjects = false;
        }
        //echo 'showOnlyMyProjects='.$showOnlyMyProjects."<br>";

        //////////////// get Requests IDs with the form node filter ////////////////
//        $ids = array();
//        if ($formnode) {
//            //echo "use formnode<br>";
//            if ($categories) {
//                $categoryIds = $transresRequestUtil->getRequestIdsFormNodeByCategory($category);
//                $ids = array_merge($ids, $categoryIds);
//            }
//            if ($searchStr) {
//                $commentIds = $transresRequestUtil->getRequestIdsFormNodeByComment($searchStr);
//                $ids = array_merge($ids, $commentIds);
//            }
//        }
//        if (count($ids) > 0) {
//            $ids = array_unique($ids);
//            //print_r($ids);
//        }
        //////////////// EOF get Requests IDs with the form node filter ////////////////

        //exit('start filtering requests');

        if ($filterType) {
            $filterTypeDone = false;

            if( $filterTypeLowerCase == strtolower("All Requests (including Drafts)") ) {
                $title = "All Work Requests (including Drafts)";
                $filterTypeDone = true;
            }
            if( $filterTypeLowerCase == strtolower("All Requests") ) {
                //$title = "All Work Requests";
                //$filterTypeDone = true;
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[progressState][0]' => "All-except-Drafts-and-Canceled",
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
            if ($filterTypeLowerCase == strtolower("My Submitted Requests") ) {
                //exit('start filtering '.$filterType);
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[submitter]' => $user->getId(),
                        'title' => $filterType,
                    )
                );
            }

            //set filter's progressState or add a filter option "my projects only"
            if ($filterTypeLowerCase == strtolower("Submitted Requests for My Projects") ) {
                //exit('start filtering '.$filterType);
                //where I'm a project's requester
                $filterTypeDone = true;
                $showOnlyMyProjects = true;
                $progressStates = array("All-except-Drafts-and-Canceled");
                $titleAdd = "All Except Draft and Canceled";

//                return $this->redirectToRoute(
//                    'translationalresearch_request_index_filter',
//                    array(
//                        'filter[submitter]' => $user->getId(),
//                        'title' => $filterType,
//                    )
//                );
            }
            //set filter's progressState or add a filter option "my projects only"
            if ($filterTypeLowerCase == strtolower("Draft Requests for My Projects") ) {
                //exit('start filtering '.$filterType);
                //where I'm a project's requester
                $filterTypeDone = true;
                $showOnlyMyProjects = true;
                $progressStates = array('draft');
                $titleAdd = "Draft ";
            }
            if ($filterTypeLowerCase == strtolower("My Draft Requests") ) {
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
            if ($filterTypeLowerCase == strtolower("Requests I Completed") ) {
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[completedBy]' => $user->getId(),
                        'filter[progressState][0]' => "completed",
                        'filter[progressState][1]' => "completedNotified",
                        'title' => $filterType,
                    )
                );
            }

            if ($filterTypeLowerCase == strtolower("All AP/CP Requests") ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("ap-cp");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "All-except-Drafts-and-Canceled",
                        'title' => $filterType,
                    )
                );
            }
            if ($filterTypeLowerCase == strtolower("All Hematopathology Requests") ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("hematopathology");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "All-except-Drafts-and-Canceled",
                        'title' => $filterType,
                    )
                );
            }
            if ($filterTypeLowerCase == strtolower("All COVID-19 Requests") ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("covid19");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "All-except-Drafts-and-Canceled",
                        'title' => $filterType,
                    )
                );
            }
            if ($filterTypeLowerCase == strtolower("All MISI Requests") ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("misi");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "All-except-Drafts-and-Canceled",
                        'title' => $filterType,
                    )
                );
            }
            if ($filterTypeLowerCase == strtolower("All Requests (including Drafts)") ) {
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => '',
                        'filter[progressState][]' => "",
                        'title' => $filterType,
                    )
                );
            }
            //echo "filterTypeLowerCase=$filterTypeLowerCase<br>";
            $filterTypeLowerCaseArr = explode(' ',$filterTypeLowerCase);
            if( count($filterTypeLowerCaseArr) == 3 &&
                strpos((string)$filterTypeLowerCase, strtolower('All')) !== false &&
                strpos((string)$filterTypeLowerCase, strtolower('Requests')) !== false
            ) {

                $specialtyName = str_replace('Requests','',$filterType); //All MISI
                $specialtyName = str_replace('All','',$specialtyName); //MISI
                $specialtyName = trim((string)$specialtyName);
                //echo "specialtyName=$specialtyName<br>";

                $projectSpecialtyObject = $transresUtil->getSpecialtyObject($specialtyName);
                if( $projectSpecialtyObject ) {
                    return $this->redirectToRoute(
                        'translationalresearch_request_index_filter',
                        array(
                            'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                            'filter[progressState][0]' => "All-except-Drafts-and-Canceled",
                            'title' => $filterType,
                        )
                    );
                }
            }

            //"Pending" is all status except, Canceled, Completed, CompletedNotified
            if ($filterTypeLowerCase == strtolower("All Pending Requests") ) {
                $pendingRequestArr = $transresRequestUtil->getFilterPendingRequestArr($filterType);

                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    $pendingRequestArr
                );
            }
            if ($filterTypeLowerCase == strtolower("All AP/CP Pending Requests") ) {
                $pendingRequestArr = $transresRequestUtil->getFilterPendingRequestArr($filterType);

                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("ap-cp");
                $pendingRequestArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();

                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    $pendingRequestArr
                );
            }
            if ($filterTypeLowerCase == strtolower("All Hematopathology Pending Requests") ) {
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
            if ($filterTypeLowerCase == strtolower("All COVID-19 Pending Requests") ) {
                $pendingRequestArr = $transresRequestUtil->getFilterPendingRequestArr($filterType);

                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("covid19");
                $pendingRequestArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();

                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    $pendingRequestArr
                );
            }
            if ($filterTypeLowerCase == strtolower("All MISI Pending Requests") ) {
                $pendingRequestArr = $transresRequestUtil->getFilterPendingRequestArr($filterType);

                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("misi");
                $pendingRequestArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();

                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    $pendingRequestArr
                );
            }
            if( strpos((string)$filterTypeLowerCase, strtolower('Pending Requests')) !== false ) {
                $specialtyName = str_replace('Pending Requests','',$filterType); //All MISI
                $specialtyName = str_replace('All','',$specialtyName); //MISI
                $specialtyName = trim((string)$specialtyName);
                //echo "specialtyName=$specialtyName<br>";

                $pendingRequestArr = $transresRequestUtil->getFilterPendingRequestArr($filterType);
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject($specialtyName);
                if( $projectSpecialtyObject ) {
                    $pendingRequestArr['filter[projectSpecialty][]'] = $projectSpecialtyObject->getId();
                    return $this->redirectToRoute(
                        'translationalresearch_request_index_filter',
                        $pendingRequestArr
                    );
                }
            }

            if ($filterTypeLowerCase == strtolower("All Active Requests") ) {
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[progressState][0]' => "active",
                        'title' => $filterType,
                    )
                );
            }
            if ($filterTypeLowerCase == strtolower("All AP/CP Active Requests") ) {
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
            if ($filterTypeLowerCase == strtolower("All Hematopathology Active Requests") ) {
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
            if ($filterTypeLowerCase == strtolower("All COVID-19 Active Requests") ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("covid19");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "active",
                        'title' => $filterType,
                    )
                );
            }
            if ($filterTypeLowerCase == strtolower("All MISI Active Requests") ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("misi");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "active",
                        'title' => $filterType,
                    )
                );
            }
            if( strpos((string)$filterTypeLowerCase, strtolower('Active Requests')) !== false ) {
                $specialtyName = str_replace('Active Requests','',$filterType); //All MISI
                $specialtyName = str_replace('All','',$specialtyName); //MISI
                $specialtyName = trim((string)$specialtyName);
                //echo "specialtyName=$specialtyName<br>";
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject($specialtyName);
                if( $projectSpecialtyObject ) {
                    return $this->redirectToRoute(
                        'translationalresearch_request_index_filter',
                        array(
                            'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                            'filter[progressState][0]' => "active",
                            'title' => $filterType,
                        )
                    );
                }
            }

            if ($filterTypeLowerCase == strtolower("All Completed Requests") ) {
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[progressState][0]' => "completed",
                        'title' => $filterType,
                    )
                );
            }
            if ($filterTypeLowerCase == strtolower("All AP/CP Completed Requests") ) {
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
            if ($filterTypeLowerCase == strtolower("All Hematopathology Completed Requests") ) {
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
            if ($filterTypeLowerCase == strtolower("All COVID-19 Completed Requests") ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("covid19");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "completed",
                        'title' => $filterType,
                    )
                );
            }
            if ($filterTypeLowerCase == strtolower("All MISI Completed Requests") ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("misi");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "completed",
                        'title' => $filterType,
                    )
                );
            }
            if( strpos((string)$filterTypeLowerCase, strtolower('Completed Requests')) !== false ) {
                $specialtyName = str_replace('Completed Requests','',$filterType); //All MISI
                $specialtyName = str_replace('All','',$specialtyName); //MISI
                $specialtyName = trim((string)$specialtyName);
                //echo "specialtyName=$specialtyName<br>";
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject($specialtyName);
                if( $projectSpecialtyObject ) {
                    return $this->redirectToRoute(
                        'translationalresearch_request_index_filter',
                        array(
                            'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                            'filter[progressState][0]' => "completed",
                            'title' => $filterType,
                        )
                    );
                }
            }

            if ($filterTypeLowerCase == strtolower("All Completed and Notified Requests") ) {
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[progressState][0]' => "completedNotified",
                        'title' => $filterType,
                    )
                );
            }
            if ($filterTypeLowerCase == strtolower("All AP/CP Completed and Notified Requests") ) {
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
            if ($filterTypeLowerCase == strtolower("All Hematopathology Completed and Notified Requests") ) {
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
            if ($filterTypeLowerCase == strtolower("All COVID-19 Completed and Notified Requests") ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("covid19");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "completedNotified",
                        'title' => $filterType,
                    )
                );
            }
            if ($filterTypeLowerCase == strtolower("All MISI Completed and Notified Requests") ) {
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject("misi");
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
                    array(
                        'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                        'filter[progressState][0]' => "completedNotified",
                        'title' => $filterType,
                    )
                );
            }
            if( strpos((string)$filterTypeLowerCase, strtolower('Completed and Notified Requests')) !== false ) {
                $specialtyName = str_replace('Completed and Notified Requests','',$filterType); //All MISI
                $specialtyName = str_replace('All','',$specialtyName); //MISI
                $specialtyName = trim((string)$specialtyName);
                //echo "specialtyName=$specialtyName<br>";
                $projectSpecialtyObject = $transresUtil->getSpecialtyObject($specialtyName);
                if( $projectSpecialtyObject ) {
                    return $this->redirectToRoute(
                        'translationalresearch_request_index_filter',
                        array(
                            'filter[projectSpecialty][]' => $projectSpecialtyObject->getId(),
                            'filter[progressState][0]' => "completedNotified",
                            'title' => $filterType,
                        )
                    );
                }
            }

            if ($filterTypeLowerCase == strtolower("All Requests with Work Queues") ) {
                //$workQueueObject = $transresUtil->getWorkQueueObject("CTP Lab");
                $workQueues = $transresUtil->getWorkQueues();
                $filterArr = array('title'=>$filterType);
                $count = 0;
                foreach($workQueues as $workQueue) {
                    //'filter[workQueues][0]'
                    $index = "filter[workQueues][$count]";
                    $filterArr[$index] = $workQueue->getId();
                    $count++;
                }
                //var_dump($filterArr);
                //exit('111');
                //filter[workQueues]=1
                //filter[workQueues][]=1&filter[workQueues][]=2
                return $this->redirectToRoute(
                    'translationalresearch_request_index_filter',
//                    array(
//                        'filter[workQueues][0]' => 1,
//                        'filter[workQueues][1]' => 2,
//                        'title' => $filterType,
//                    )
                    $filterArr
                );
            }
            //Generic work queues with substring "Work Queue"
            //if( $filterTypeLowerCase == strtolower("CTP Lab Work Queue") ) {
            if( strpos((string)$filterTypeLowerCase, strtolower('Work Queue')) !== false ) {
                //echo "filterTypeLowerCase=$filterTypeLowerCase<br>";
                //echo "filterType=$filterType<br>";
                $workQueueName = str_replace('Work Queue','',$filterType); //CTP Lab
                $workQueueName = trim((string)$workQueueName);
                //echo "workQueueName=$workQueueName<br>";
                $workQueueObject = $transresUtil->getWorkQueueObject($workQueueName);
                //echo "workQueueObject=$workQueueObject<br>";
                if( $workQueueObject ) {
                    return $this->redirectToRoute(
                        'translationalresearch_request_index_filter',
                        array(
                            'filter[workQueues][0]' => $workQueueObject->getId(),
                            'title' => $filterType,
                        )
                    );
                }
            }


            //not pre-set filter
//            if( $filterType != "All Requests" ) {
//                $this->addFlash(
//                    'notice',
//                    "Filter pre-set type '$filterType' is not defined"
//                );
//            }

            if (!$filterTypeDone) {
                $this->addFlash(
                    'notice',
                    "Filter pre-set type '$filterType' is not defined"
                );
                //exit("Filter Type not known " . $filterType);
            }
        }
    }
        //exit("Start filtering...");

//        if( $timer ) {
//            //$time_pre2 = microtime(true);
//            $stopwatch->start('createQueryBuilder');
//        }

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $repository = $em->getRepository(TransResRequest::class);
        $dql =  $repository->createQueryBuilder("transresRequest");
        $dql->select('transresRequest');

        $dql->leftJoin('transresRequest.submitter','submitter');
        $dql->leftJoin('transresRequest.contact','contact');
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

            $dql->leftJoin('project.principalInvestigators','projectPrincipalInvestigators');
            $dql->leftJoin('project.principalIrbInvestigator','projectPrincipalIrbInvestigator');
            $dql->leftJoin('project.submitInvestigators','projectSubmitInvestigators');
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
                "projectSubmitInvestigators.id = :userId OR ".
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

        //echo "projectSearch=[".$projectSearch."] <br>";
        if( $projectSearch ) {
            $projectId = null;
            if (strpos((string)$projectSearch, ', ') !== false) {
                //get id
                $projectSearchArr = explode(", ",$projectSearch);
                if( count($projectSearchArr) > 1 ) {
                    $projectOid = $projectSearchArr[0];
                    //get id (remove APCP or HP)
                    $projectId = (int) filter_var($projectOid, FILTER_SANITIZE_NUMBER_INT);
                }
                if( !$projectId ) {
                    $projectOid = $projectSearch;
                    //get id (remove APCP or HP)
                    $projectId = (int) filter_var($projectOid, FILTER_SANITIZE_NUMBER_INT);
                }
            } else {
                //get id (remove APCP or HP)
                $projectId = (int) filter_var($projectSearch, FILTER_SANITIZE_NUMBER_INT);
            }

            if( $projectId ) {
                //echo "projectId=[".$projectId."] <br>";
                $dql->andWhere("project.id = :projectId");
                $dqlParameters["projectId"] = $projectId;
            }
        }

        if( $priceList ) {
            if( $priceList != 'all' ) {
                $dql->leftJoin('project.priceList','priceList');
                if( $priceList == 'external' ) {
                //if( $priceList == 'default' ) {
                    $dql->andWhere("priceList.id IS NULL");
                } else {
                    $dql->andWhere("priceList.id = :priceListId");
                    $dqlParameters["priceListId"] = $priceList;
                }
                $advancedFilter++;
            }
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
            if( in_array("All-except-Drafts-and-Canceled", $progressStates )) {
                $allExceptDraft = " OR (transresRequest.progressState != 'draft' AND transresRequest.progressState != 'canceled') OR transresRequest.progressState IS NULL";
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

        if( $completedBy ) {
            $dql->leftJoin('transresRequest.completedBy','completedBy');
            $dql->andWhere("completedBy.id = :completedById");
            $dqlParameters["completedById"] = $completedBy->getId();
            $advancedFilter++;
        }

        if( $externalId ) {
            //echo $externalId."<br>";
            //$dql->andWhere('LOWER(transresRequest.exportId) LIKE LOWER(:exportId)');
            $dql->andWhere("CAST(transresRequest.exportId as VARCHAR) LIKE LOWER(:exportId)");
            $dqlParameters['exportId'] = "%".$externalId."%";
            $advancedFilter++;
        }

        if( $requestId ) {
            $dql->andWhere('LOWER(transresRequest.oid) LIKE LOWER(:requestId)');
            $dqlParameters['requestId'] = "%".$requestId."%";
        }

//        if( $accountNumber ) {
//            $dql->andWhere("transresRequest.fundedAccountNumber = :fundedAccountNumber");
//            $dqlParameters["fundedAccountNumber"] = $accountNumber;
//            $advancedFilter++;
//        }

        if( $fundingNumber ) {
            $dql->andWhere("LOWER(transresRequest.fundedAccountNumber) LIKE LOWER(:fundedAccountNumber)");
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

        if( $sampleName ) {
            $dql->leftJoin('transresRequest.dataResults','dataResults');
            $dql->andWhere("LOWER(dataResults.barcode) LIKE LOWER(:sampleName)");
            $dqlParameters["sampleName"] = "%".$sampleName."%";
            $advancedFilter++;
        }

        if( $principalInvestigators && count($principalInvestigators)>0 ) {
            $principalInvestigatorsIdsArr = array();
            foreach($principalInvestigators as $principalInvestigator) {
                //echo "PI=".$principalInvestigator."; id=".$principalInvestigator->getId()."<br>";
                $principalInvestigatorsIdsArr[] = $principalInvestigator->getId();
            }
            $dql->andWhere("principalInvestigators.id IN (:principalInvestigators)");
            $dqlParameters["principalInvestigators"] = $principalInvestigatorsIdsArr;   //implode(",",$principalInvestigatorsIdsArr);
            //$dql->andWhere("principalInvestigators.id = 379"); //signupUser9 379
            $advancedFilter++;
        }

        if( !$formnode ) {
            $dql->leftJoin('transresRequest.products','products');
            if( $categories && count($categories) > 0 ) {
                $dql->leftJoin('products.category','category');
                //$dql->andWhere("category.id = :categoryId");
                //$dqlParameters["categoryId"] = $category;
                $dql->andWhere("category.id IN (:categoryIds)"); //TODO: categories
                $dqlParameters["categoryIds"] = $categories;
            }
            if ($searchStr) {
                $dql->leftJoin('transresRequest.dataResults','dataResults');
                //$dql->andWhere("(category.name LIKE :categoryStr OR category.productId LIKE :categoryStr OR category.feeUnit LIKE :categoryStr OR category.fee LIKE :categoryStr)");
                $commentCriterion = "LOWER(products.comment) LIKE LOWER(:searchStr) OR LOWER(products.note) LIKE LOWER(:searchStr) OR LOWER(transresRequest.comment) LIKE LOWER(:searchStr) OR LOWER(dataResults.comment) LIKE LOWER(:searchStr)";
                $dqlParameters["searchStr"] = "%".$searchStr."%";

                //add search fos bundle comments
                $requestCommentIds = $transresRequestUtil->getRequestIdsByFosComment($searchStr);
                if( count($requestCommentIds) > 0 ) {
                    $commentCriterion = $commentCriterion . " OR " . "transresRequest.id IN (:requestCommentIds)";
                    $dqlParameters["requestCommentIds"] = $requestCommentIds;
                }

                $dql->andWhere($commentCriterion);

                $advancedFilter++;
            }
        }

        if( $workQueues && count($workQueues) > 0 ) {
            //TODO: filter workqueue by project's price list
            if( in_array("products", $dql->getAllAliases()) ) {
                //join already exists
            } else {
                $dql->leftJoin('transresRequest.products','products');
            }
            $dql->leftJoin('products.category','category');
            $dql->leftJoin('category.workQueues','workQueues');

            //$prices
            $dql->leftJoin('category.prices','prices');
            //$dql->leftJoin('prices.workQueues','priceWorkQueues');

            $workQueuesIdsArr = array();
            foreach($workQueues as $workQueue) {
                $workQueuesIdsArr[] = $workQueue->getId();
            }

            $dql->andWhere("workQueues.id IN (:workQueues)"); //use only workQueues in the default price list
            //$dql->andWhere("priceWorkQueues.id IN (:workQueues)");
            //$dql->andWhere("workQueues.id IN (:workQueues) OR priceWorkQueues.id IN (:workQueues)");

            $dqlParameters["workQueues"] = $workQueuesIdsArr;

            $advancedFilter++;
        }

//        if( count($ids) > 0 ) {
//            //echo "using ids <br>";
//            //$dql->andWhere("transresRequest.id IN (:ids)");
//            //$dqlParameters["ids"] = implode(",",$ids);
//            $dql->andWhere("transresRequest.id IN (:ids)");
//            $dqlParameters["ids"] = $ids;
//        }
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
        
        $dql->groupBy("transresRequest, project, submitterInfos");

        //testing
        //$dql->andWhere("transresRequest.id = 2");

        $limit = 20;
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        //doctrine cache queries
        //$query->useQueryCache(true);
        //$query->useResultCache(true);

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
            'defaultSortFieldName' => 'transresRequest.createDate',
            'defaultSortDirection' => 'DESC',
            'wrap-queries' => true
        );

//        if( $timer ) {
//            $event = $stopwatch->stop('createQueryBuilder');
//            echo "createQueryBuilder duration: ".($event->getDuration()/1000)." sec<br>";
//
//            //$time_pre2 = microtime(true);
//            $stopwatch->start('PaginatorResult');
//        }

        //TESTING
        //$query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        $paginator  = $this->container->get('knp_paginator');
        $transresRequests = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                                         /*limit per page*/
            $paginationParams
        );
        //echo "transresRequests count=".count($transresRequests)."<br>";

        //TESTING
        //return $this->testingReturn($request,$stopwatch);

//        if( $timer ) {
////            $time_post2 = microtime(true); //microseconds
////            $exec_time2 = round(($time_post2 - $time_pre2), 1);
////            echo "Paginator exec_time=$exec_time2<br>";
//
//            $event = $stopwatch->stop('PaginatorResult');
//            echo "PaginatorResult duration: ".($event->getDuration()/1000)." sec<br>";
//
//            $event = $stopwatch->stop('Paginator');
//            echo "Paginator duration: ".($event->getDuration()/1000)." sec<br>";
//        }

        if( $filterTitle ) {
            $title = $filterTitle;
        }

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

//            if( $timer ) {
//                $stopwatch->start('GetTitle');
//            }

            //$allFilteredTransresRequests = $query2->getResult();
            //echo "allFilteredTransresRequests=".count($allFilteredTransresRequests)."<br>";
            //$allGlobalRequests = $em->getRepository('AppTranslationalResearchBundle:TransResRequest')->findAll();
            //$title = $title . " (Matching " . count($allTransresRequests) . ", Total " . count($allGlobalRequests) . ")";
            //$allTransresRequests = $transresUtil->getTotalRequestCountByDqlParameters($dql,$dqlParameters);
            $matchingStrWorkRequestIds = $transresUtil->getMatchingRequestArrByDqlParameters($dql,$dqlParameters);
            $allTransresRequests = count($matchingStrWorkRequestIds);

            //$matchingStrWorkRequestIds = array(1,2,3);
            
            $allGlobalRequests = $transresUtil->getTotalRequestCount();
            $title = $title . " (Matching " . $allTransresRequests . ", Total " . $allGlobalRequests . $requestTotalFeeHtml . ")";
            
            $matchingStrWorkRequestIds = implode("-",$matchingStrWorkRequestIds);

//            if( $timer ) {
//                $event = $stopwatch->stop('GetTitle');
//                echo "GetTitle duration: " . ($event->getDuration() / 1000) . " sec<br>";
//            }
        }

//        if( $timer ) {
//            $event = $stopwatch->stop('myRequestsAction');
//            echo "myRequestsAction duration: ".($event->getDuration()/1000)." sec<br>";
//            echo "myRequestsAction memory: ".($event->getMemory()/1000000)." MB<br>";
//        }

        $eventObjectType = $em->getRepository(EventObjectTypeList::class)->findOneByName("TransResRequest");
        if( $eventObjectType ) {
            $eventObjectTypeId = $eventObjectType->getId();
        } else {
            $eventObjectTypeId = null;
        }

        $filterformView = null;
        //$filterDisable = true;
        if( $withfilter ) {
            $filterformView = $filterform->createView();
            //$filterDisable = false;
        }

        //Template: AppTranslationalResearchBundle/Request/index.html.twig
        $formArray = array(
            'transresRequests' => $transresRequests,
            'filterform' => $filterformView,
            'title' => $title,
            'requestTotalFeeHtml' => null, //$requestTotalFeeHtml
            'advancedFilter' => $advancedFilter,
            'project' => $project,
            'eventObjectTypeId' => $eventObjectTypeId,
            'matchingStrWorkRequestIds' => $matchingStrWorkRequestIds,
            //'filterDisable' => $filterDisable, //testing
            //'hideaction' => true,    //testing
            //'hiderows' => true,      //testing
        );

        if( !$withfilter ) {
            $formArray['filterDisable'] = true;
        }

        return $formArray;
    }
//    public function testingReturn($request,$stopwatch=null) {
    //        //TESTING
    //        $em = $this->getDoctrine()->getManager();
    //        $title = "Work Requests";
    //        $repository = $em->getRepository('AppTranslationalResearchBundle:TransResRequest');
    //        $dql =  $repository->createQueryBuilder("transresRequest");
    //        $dql->select('transresRequest');
    //        $dql->where("transresRequest.id=2");
    //        $query = $dql->getQuery(); //$query = $em->createQuery($dql);
    //        $paginationParams = array(
    //            'defaultSortFieldName' => 'transresRequest.id',
    //            'defaultSortDirection' => 'DESC',
    //            'wrap-queries' => true
    //        );
    //        $paginator  = $this->container->get('knp_paginator');
    //        $transresRequests = $paginator->paginate(
    //            $query,
    //            $request->query->get('page', 1),   /*page number*/
    //            10,                                         /*limit per page*/
    //            $paginationParams
    //        );
    //        $event = $stopwatch->stop('myRequestsAction');
    //        echo "myRequestsAction duration: ".($event->getDuration()/1000)." sec<br>";
    //        echo "myRequestsAction memory: ".($event->getMemory()/1000000)." MB<br>";
    //        return array(
    //            'filterDisable' => true, //testing
    //            'transresRequests' => $transresRequests,
    //            //'allTransresRequests' => $allTransresRequests,
    //            //'project' => null,
    //            //'filterform' => $filterform->createView(),
    //            'title' => $title,
    //            'requestTotalFeeHtml' => null, //$requestTotalFeeHtml
    //            //'advancedFilter' => $advancedFilter,
    //            'project' => null,
    //            'hideaction' => true,
    //            'hiderows' => true,
    //
    //        );
    //    }
    #[Route(path: '/download-spreadsheet/', name: 'translationalresearch_download_request_spreadsheet', methods: ['POST'])]
    public function downloadRequestsCsvAction( Request $request ) {
        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresRequestUtil = $this->container->get('transres_request_util');

        $ids = $request->request->get('ids');
        //echo "ids=".$ids."<br>";
        //exit('111');

        if( !$ids ) {
            exit("No work requests found");
        }

        $idsArr = explode('-', $ids);
        $idsArr = array_reverse($idsArr);

        //$fileName = "Invoices".".xlsx"; //cell type can not be set in xlsx
        $fileName = "WorkRequests".".csv";

        $transresRequestUtil->createtWorkRequestCsvSpout( $idsArr, $fileName );

        exit();
    }

    #[Route(path: '/download-requester-emails/', name: 'translationalresearch_download_requester_emails', methods: ['POST'])]
    public function downloadRequesterEmailsCsvAction( Request $request ) {
        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresRequestUtil = $this->container->get('transres_request_util');

        $ids = $request->request->get('ids');
        //echo "ids=".$ids."<br>";
        //exit('111');

        if( !$ids ) {
            exit("No work requests found");
        }

        $idsArr = explode('-', $ids);
        $idsArr = array_reverse($idsArr);

        //$fileName = "Invoices".".xlsx"; //cell type can not be set in xlsx
        $fileName = "WorkRequesterEmails".".csv";

        $limit = NULL;
        $transresRequestUtil->createWorkRequesterEmails( $idsArr, $fileName, $limit );

        exit();
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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                $autoAssignInstitution = $em->getRepository(Institution::class)->findOneByName('Pathology and Laboratory Medicine');
            }
            $transresRequest->setInstitution($autoAssignInstitution);
        }

        //set order category
        if( $formnode && !$transresRequest->getMessageCategory() ) {
            $categoryStr = "HemePath Translational Research Request";  //"Pathology Call Log Entry";
            //$categoryStr = "Nesting Test"; //testing
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageCategory'] by [MessageCategory::class]
            $messageCategory = $em->getRepository(MessageCategory::class)->findOneByName($categoryStr);
            if (!$messageCategory) {
                throw new \Exception("Message category is not found by name '" . $categoryStr . "'");
            }
            $transresRequest->setMessageCategory($messageCategory);
        }

        return $transresRequest;
    }
    public function createRequestForm( TransResRequest $transresRequest, $cycle, $request, $availableProjects=null )
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $routeName = $request->get('_route');

        $billingStateChoiceArr = $transresRequestUtil->getBillingStateArr();
        $progressStateChoiceArr = $transresRequestUtil->getProgressStateArr();

        //categoryListLink
        $categoryListLink = null;
//        if( $this->isGranted('ROLE_TRANSRES_ADMIN') ) {
//            $categoryListUrl = $this->container->get('router')->generate(
//                'transresrequestcategorytypes-list_translationalresearch',
//                array(),
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
//            //$categoryListLink = " (<a target='_blank' href=" . $categoryListUrl . ">" . "Category Type List Management" . "</a>)";
//            //glyphicon glyphicon-wrench
//            $categoryListLink = " <a data-toggle='tooltip' title='Products/Services (Fee Schedule) List Management' href=".
//                $categoryListUrl."><span class='glyphicon glyphicon-wrench'></span></a>";
//        }
        $categoryListUrl = $this->container->get('router')->generate(
            'translationalresearchfeesschedule-list',
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $categoryListLink = " <a target='_blank' data-toggle='tooltip' title='Products/Services (Fee Schedule) List' href=".
            $categoryListUrl."><span class='glyphicon glyphicon-wrench'></span></a>";


        $projectSpecialties = array();
        if( $transresRequest ) {
            $project = $transresRequest->getProject();
            $projectSpecialty = $transresRequest->getProjectSpecialty();
            if( $projectSpecialty ) {
                $projectSpecialties = $transresRequestUtil->getProductServiceByProjectSpecialty($projectSpecialty,$project);
            }
        }

        //for non-funded projects, show "Funding Number (Optional):"
        //transres_formnode_util.getProjectFormNodeFieldByName(project,"Funded")
        $project = $transresRequest->getProject();
        $fundedNumberLabel = "Fund Number:";
        //if( $project && !$transResFormNodeUtil->getProjectFormNodeFieldByName($project,"Funded") ) {
        if( $project && !$project->getFunded() ) {
            $fundedNumberLabel = "Fund Number (Optional):";
        }

//        $trpAdmin = false;
//        if( $this->isGranted('ROLE_TRANSRES_ADMIN') ) {
//            $trpAdmin = true;
//        }
        $trpAdmin = $transresUtil->isAdmin($project);
        $trpTech = $transresUtil->isTech($project);
        $trpPrimaryReviewer = $transresUtil->isPrimaryReviewer($project);
        $trpAdvancedUser = $transresUtil->isAdvancedUser($project);

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'transresUtil' => $transresUtil,
            //'SecurityAuthChecker' => $this->container->get('security.authorization_checker'),
            'trpAdmin' => $trpAdmin,
            'trpAdvancedUser' => $trpAdvancedUser,
            'transresRequest' => $transresRequest,
            'routeName' => $routeName,
            'saveAsUpdate' => false,
            'saveAsDraft' => false,
            'saveAsComplete' => false,
            'updateRequest' => false,
            'saveAsUpdateChangeProgressState' => false,
            'saveAsUpdateChangeBillingState' => false,
            //'projects' => null,
            'availableProjects' => null,
            'project' => null,
            'billingStateChoiceArr' => $billingStateChoiceArr,
            'progressStateChoiceArr' => $progressStateChoiceArr,
            'categoryListLink' => $categoryListLink,
            'projectSpecialties' => $projectSpecialties,
            'fundedNumberLabel' => $fundedNumberLabel,
            'humanAnimalNameSlash' => $transresUtil->getHumanAnimalName()
        );

        $params['admin'] = false;

//        if(
//            $this->isGranted('ROLE_TRANSRES_ADMIN') ||
//            $this->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
//            $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
//        ) {
        if( $trpAdmin || $trpTech || $trpPrimaryReviewer ) {
            $params['admin'] = true;
        } else {
            //TODO: do not add reviewers
        }

        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $params['transresPermissionUtil'] = $transresPermissionUtil;

        $disabled = false;

        if( $cycle == "new" ) {
            $disabled = false;
            $params['saveAsDraft'] = true;
            $params['saveAsComplete'] = true;

            if( $routeName == "translationalresearch_new_standalone_request" ) {
                //getAvailableProjects($finalApproved=true, $notExpired=true, $requester=true, $reviewer=true)
                if( !$availableProjects ) {
                    $availableProjects = $transresUtil->getAvailableProjects(true, true, true, false);
                }
                $params['availableProjects'] = $availableProjects;
                $params['project'] = $project;
                //echo "1 project=".$project."<br>";
            } else {
                $params['availableProjects'] = array($project);
            }

            if( $project ) {
                $projectGoals = $transresUtil->findProjectGoals($project->getId());
                $existingProjectGoals = array();
                foreach($projectGoals as $existingProjectGoal) {
                    $description = $transresUtil->tokenTruncate($existingProjectGoal->getDescription(), 100);
                    $existingProjectGoals[$description] = $existingProjectGoal->getId();
                }
                $params['existingProjectGoals'] = $existingProjectGoals;
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

            //show "Update Changes and Completion/Billing Status"
            if( $transresRequest->getProgressState() != 'draft' ) {
                $params['saveAsUpdateChangeProgressState'] = true;
            }
            if( $transresRequest->getBillingState() != 'draft' ) {
                $params['saveAsUpdateChangeBillingState'] = true;
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



    #[Route(path: '/request/generate-form-node-tree/', name: 'translationalresearch_generate_form_node_tree_request', methods: ['GET'])]
    public function generateFormNodeAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $count = $transResFormNodeUtil->generateTransResFormNodeRequest();

        exit("Form Node Tree generated: ".$count);
    }




    /**
     * Finds and displays a progress review form for this request entity.
     */
    #[Route(path: '/work-request/progress/review/{id}', name: 'translationalresearch_request_review_progress_state', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Request/review.html.twig')]
    public function reviewProgressAction(Request $request, TransResRequest $transresRequest)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');

//        $transresRequestUtil = $this->container->get('transres_request_util');
//        if(
//            $transresRequestUtil->isRequestProgressReviewable($transresRequest) && //check state
//            (
//                $transresUtil->isAdminOrPrimaryReviewer() ||
//                $transresRequestUtil->isRequestProgressReviewer($transresRequest)
//            )
//        ) {
//            //ok
//        } else {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
        if( false === $transresPermissionUtil->hasRequestPermission("progress-review",$transresRequest) ) {
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
            'title' => "Completion Progress Status Update for Work Request ".$transresRequest->getOid(),
            'handsometableData' => $jsonData //Error in twig: var _handsometableDataArr = {{ handsometableData|json_encode|raw }}; => Variable "handsometableData" does not exist.
        );
    }

    /**
     * Finds and displays a billing review form for this request entity.
     */
    #[Route(path: '/work-request/billing/review/{id}', name: 'translationalresearch_request_review_billing_state', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Request/review.html.twig')]
    public function reviewBillingAction(Request $request, TransResRequest $transresRequest)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');

//        $transresRequestUtil = $this->container->get('transres_request_util');
//        if(
//            $transresRequestUtil->isRequestProgressReviewable($transresRequest) && //check state
//            (
//                $transresUtil->isAdminOrPrimaryReviewer() ||
//                $transresRequestUtil->isRequestProgressReviewer($transresRequest)
//            )
//        ) {
//            //ok
//        } else {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }
        if( false === $transresPermissionUtil->hasRequestPermission("billing-review",$transresRequest) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "show";

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //show billing review

        $eventType = "Request Viewed";
        $msg = "Request ".$transresRequest->getOid() ." has been viewed on the billing review page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        //get Table $jsonData
        $jsonData = $this->getTableData($transresRequest);

        return array(
            'transresRequest' => $transresRequest,
            'project' => $transresRequest->getProject(),
            'form' => $form->createView(),
            'cycle' => $cycle,
            'statMachineType' => 'billing',
            'title' => "Billing Progress Status Update for Work Request ".$transresRequest->getOid(),
            'handsometableData' => $jsonData //Error in twig: var _handsometableDataArr = {{ handsometableData|json_encode|raw }}; => Variable "handsometableData" does not exist.

        );
    }


    #[Route(path: '/request/update-irb-exp-date/', name: 'translationalresearch_update_irb_exp_date', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function updateIrbExpDateAction( Request $request ) {
        //set permission: project irb reviewer or admin
//        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
//        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        //$userServiceUtil = $this->container->get('user_service_utility');

        $updatePdf = trim((string)$request->get('updatePdf'));
        $projectId = trim((string)$request->get('projectId'));
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $project = $em->getRepository(Project::class)->find($projectId);

        $permission = true;

        if( $transresUtil->isAdminOrPrimaryReviewer($project) || $transresUtil->isProjectEditableByRequester($project) ) {
            //ok
        } else {
            //return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            $permission = false;
        }

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            $permission = false;
        }

        $res = "NotOK";

        if( $permission == false ) {
            $response = new Response($res);
            return $response;
        }

        if(
            $transresUtil->isAdminOrPrimaryReviewer($project) ||
            $this->isReviewsReviewer($user,$project->getIrbReviews())
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if( $project ) {
            $originalIrbExpDateStr = "Unknown";
            $originalIrbExpDate = $project->getIrbExpirationDate();

            $value = trim((string)$request->get('value'));
            //echo "value=".$value."<br>";
            $irbExpDate = \DateTime::createFromFormat('m/d/Y', $value);

            $logger = $this->container->get('logger');
            $logger->notice("irbExpDate=".$irbExpDate->format("m/d/Y H:i:s").", originalIrbExpDate=".$originalIrbExpDate->format("m/d/Y H:i:s"));

            if( $irbExpDate != $originalIrbExpDate ) {
                $logger->notice("irbExpDate is not the same originalIrbExpDate");

                if ($originalIrbExpDate) {
                    $originalIrbExpDateStr = $originalIrbExpDate->format('m/d/Y');
                }

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

                //generate project PDF
                if ($updatePdf) {
                    $logger = $this->container->get('logger');
                    $logger->notice("translationalresearch_update_irb_exp_date updated PDF: updatePdf=$updatePdf");
                    $transresPdfUtil = $this->container->get('transres_pdf_generator');
                    $transresPdfUtil->generateAndSaveProjectPdf($project, $user, $request); //update_irb_exp_date
                    $em->flush();
                }

                //add eventlog changed IRB
                $eventType = "Project Updated";
                $res = "Project ID " . $project->getOid() . " has been updated: " .
                    $transresUtil->getHumanName() . " Expiration Date changed from " .
                    $originalIrbExpDateStr . " to " . $value;
                $transresUtil->setEventLog($project, $eventType, $res);

            } else {
                $logger->notice("irbExpDate is the same originalIrbExpDate");
                $res = "Expiration Date for project ID " . $project->getOid() . " is unchanged."; //" has not been updated";
            }

            $this->addFlash(
                'notice',
                $res
            );
        }

        $response = new Response($res);
        return $response;
    }

    #[Route(path: '/request/update-project-pricelist/', name: 'translationalresearch_update_project_pricelist', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function updateProjectPriceListAction( Request $request ) {
        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        $updatePdf = trim((string)$request->get('updatePdf'));
        $projectId = trim((string)$request->get('projectId'));
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $project = $em->getRepository(Project::class)->find($projectId);

        $permission = true;
        $res = "NotOK";

        if( $transresUtil->isAdminOrPrimaryReviewer($project) ) {
            //ok
        } else {
            //return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            $permission = false;
        }

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            $permission = false;
        }

        if( $permission == false ) {
            $response = new Response($res);
            return $response;
        }

        if( $project ) {
            $pricelistid = trim((string)$request->get('pricelistid'));
            //echo "pricelistid=".$pricelistid."<br>";

            if( !$pricelistid ) {
                $pricelistid = NULL;
            }

            $originalPriceListId = NULL; //"None";
            $originalPriceList = $project->getPriceList();
            if( $originalPriceList ) {
                $originalPriceListId = $originalPriceList->getId();
            }

            if( $originalPriceListId != $pricelistid ) {

                $priceList = NULL;
                if ($pricelistid) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:PriceTypeList'] by [PriceTypeList::class]
                    $priceList = $em->getRepository(PriceTypeList::class)->find($pricelistid);
                }

                //if( $priceList ) {
                if( $originalPriceListId != $pricelistid ) {
                    $project->setPriceList($priceList);
                    $em->flush();

                    //generate project PDF
                    if( $updatePdf ) {
                        $logger = $this->container->get('logger');
                        $logger->notice("translationalresearch_update_project_pricelist updated PDF: updatePdf=$updatePdf");
                        $transresPdfUtil = $this->container->get('transres_pdf_generator');
                        $user = $this->getUser();
                        $transresPdfUtil->generateAndSaveProjectPdf($project, $user, $request); //update_project_pricelist
                        $em->flush();
                    }
                }

                if( !$priceList ) {
                    $priceList = "Default";
                }

                if( !$originalPriceList ) {
                    $originalPriceList = "Default";
                }

                //add eventlog changed Admin Review
                if( $originalPriceList != $priceList ) {
                    $eventType = "Project Updated";
                    $res = "Project ID " . $project->getOid() . " has been updated: " .
                        " Price list changed from " .
                        $originalPriceList . " to " . $priceList;
                    $transresUtil->setEventLog($project,$eventType,$res);

                    $this->addFlash(
                        'notice',
                        $res
                    );
                }
            } else {
                if( !$originalPriceList ) {
                    $originalPriceList = "Default";
                }
                $res = $originalPriceList. " price list for project ID " . $project->getOid() . " is unchanged."; //" has not been updated";
            }
        } else {
            $res = "Logical error: project not found by ID $projectId";
        }

        $response = new Response($res);
        return $response;
    }

    #[Route(path: '/request/update-project-approvedprojectbudget/', name: 'translationalresearch_update_project_approvedprojectbudget', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function updateApprovedProjectBudgetAction( Request $request ) {
        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $user = $this->getUser();

        $updatePdf = trim((string)$request->get('updatePdf'));
        $projectId = trim((string)$request->get('projectId'));
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $project = $em->getRepository(Project::class)->find($projectId);

        $permission = true;
        $res = "NotOK";

        if( $transresUtil->isAdminOrPrimaryReviewer($project) ) {
            //ok
        } else {
            //return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            $permission = false;
        }

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
//            );
//            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            $permission = false;
        }

        if( $permission == false ) {
            $response = new Response($res);
            return $response;
        }

        if( $project ) {
            $approvedProjectBudget = trim((string)$request->get('approvedProjectBudget'));
            //echo "approvedProjectBudget=".$approvedProjectBudget."<br>";

            $originalApprovedProjectBudget = $project->getApprovedProjectBudget();

            if( $originalApprovedProjectBudget != $approvedProjectBudget ) {
                
                $project->setApprovedProjectBudget($approvedProjectBudget);
                $project->setUpdateUser($user);
                $em->flush();

                //generate project PDF
                if( $updatePdf ) {
                    $logger = $this->container->get('logger');
                    $logger->notice("translationalresearch_update_project_approvedprojectbudget updated PDF: updatePdf=$updatePdf");
                    $transresPdfUtil = $this->container->get('transres_pdf_generator');
                    $transresPdfUtil->generateAndSaveProjectPdf($project, $user, $request); //update_project_approvedprojectbudget
                    $em->flush();
                }

                $transresUtil->sendProjectApprovedBudgetUpdateEmail($project,$originalApprovedProjectBudget);
                
                $eventType = "Project Approved Budget Updated";
                $res = "Project " . $project->getOid() . " has been updated: " .
                    "Approved Project Budget changed from " .
                    $originalApprovedProjectBudget . " to " . $project->getApprovedProjectBudget();
                $transresUtil->setEventLog($project,$eventType,$res);

                $this->addFlash(
                    'notice',
                    $res
                );
            } else {
                $res = "Approved Project Budget for project ID " . $project->getOid() . " is unchanged.";
            }
        } else {
            //$res = "Logical error: project not found by ID $projectId";
        }

        $response = new Response($res);
        return $response;
    }

    #[Route(path: '/request/update-project-nobudgetlimit/', name: 'translationalresearch_update_project_nobudgetlimit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function updateNoBudgetLimitAction( Request $request ) {
        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        $updatePdf = trim((string)$request->get('updatePdf'));
        $projectId = trim((string)$request->get('projectId'));
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $project = $em->getRepository(Project::class)->find($projectId);

        $permission = true;
        $res = "NotOK";

        if( $transresUtil->isAdminOrPrimaryReviewer($project) ) {
            //ok
        } else {
            //return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            $permission = false;
        }

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//            $this->addFlash(
//                'warning',
//                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
//            );
            //return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
            $permission = false;
        }

        if( $permission == false ) {
            $response = new Response($res);
            return $response;
        }

        if( $project ) {
            $noBudgetLimit = trim((string)$request->get('noBudgetLimit'));
            //echo "noBudgetLimit=".$noBudgetLimit."<br>";

            $originalNoBudgetLimit = $project->getNoBudgetLimit();

            if( $originalNoBudgetLimit != $noBudgetLimit ) {

                if( $noBudgetLimit ) {
                    $noBudgetLimit = true;
                } else {
                    $noBudgetLimit = false;
                }

                $project->setNoBudgetLimit($noBudgetLimit);
                $em->flush();

                //generate project PDF
                if( $updatePdf ) {
                    $logger = $this->container->get('logger');
                    $logger->notice("translationalresearch_update_project_nobudgetlimit updated PDF: updatePdf=$updatePdf");
                    $transresPdfUtil = $this->container->get('transres_pdf_generator');
                    $user = $this->getUser();
                    $transresPdfUtil->generateAndSaveProjectPdf($project, $user, $request); //update_project_nobudgetlimit
                    $em->flush();
                }

                $originalNoBudgetLimitStr = "No";
                if( $originalNoBudgetLimit ) {
                    $originalNoBudgetLimitStr = "Yes";
                }

                $noBudgetLimitStr = "No";
                if( $noBudgetLimit ) {
                    $noBudgetLimitStr = "Yes";
                }

                $transresUtil->sendProjectNoBudgetUpdateEmail($project,$originalNoBudgetLimit);
                
                $eventType = "Project Approved Budget Limit Updated";
                $res = "Project " . $project->getOid() . " has been updated: " .
                    "No budget limit changed from " .
                    $originalNoBudgetLimitStr . " to " . $noBudgetLimitStr;
                $transresUtil->setEventLog($project,$eventType,$res);

                $this->addFlash(
                    'notice',
                    $res
                );
            } else {
                $res = "No budget limit for project ID " . $project->getOid() . " is unchanged.";
            }
        } else {
            //$res = "Logical error: project not found by ID $projectId";
        }

        $response = new Response($res);
        return $response;
    }


    #[Route(path: '/request/fee-schedule', name: 'translationalresearchfeesschedule-list', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Request/fee-schedule.html.twig')]
    public function feeScheduleAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$transresUtil = $this->container->get('transres_util');
        //$specialties = $transresUtil->getTransResProjectSpecialties(false);

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $specialties = $em->getRepository(SpecialtyList::class)->findBy(
            array(
                'type' => array("default","user-added")
            ),
            array('orderinlist' => 'ASC')
        );
        $filterSpecialties = array();
        foreach($specialties as $specialty) {
            $filterSpecialties[$specialty->getShortName()] = $specialty->getId();
        }
        $params = array(
            'specialties'=>$filterSpecialties
        );

        $filterform = $this->createForm(FeeFilterType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));
        $filterform->handleRequest($request);
        $search = $filterform['search']->getData();
        $specialties = $filterform['specialties']->getData();

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequestCategoryTypeList'] by [RequestCategoryTypeList::class]
        $repository = $em->getRepository(RequestCategoryTypeList::class); //fee schedule list
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');
        $dql->leftJoin("list.projectSpecialties", "projectSpecialties");
        //$dql->innerJoin("list.projectSpecialties", "projectSpecialties");
        $dql->leftJoin("list.prices", "prices");

        $dqlParameters = array();

        if( $search ) {
            $searchStr = "";

            if( is_numeric($search) ) {
                //echo "int <br>";
                $searchInt = intval($search);
                $searchStr = "list.id = :searchInt OR";
                $dqlParameters['searchInt'] = $searchInt;
            }

            $searchStr = $searchStr."
                LOWER(list.name) LIKE LOWER(:search) 
                OR LOWER(list.abbreviation) LIKE LOWER(:search) 
                OR LOWER(list.shortname) LIKE LOWER(:search) 
                OR LOWER(list.description) LIKE LOWER(:search)
                ";

            $searchStr = $searchStr . " OR LOWER(list.section) LIKE LOWER(:search)";
            $searchStr = $searchStr . " OR LOWER(list.productId) LIKE LOWER(:search)";
            $searchStr = $searchStr . " OR LOWER(list.feeUnit) LIKE LOWER(:search)";
            $searchStr = $searchStr . " OR LOWER(list.fee) LIKE LOWER(:search)";

            $dql->andWhere($searchStr);
            $dqlParameters['search'] = '%'.$search.'%';
        }

        if( $specialties && count($specialties) > 0 ) {

            //$dql->andWhere("projectSpecialties.id != 5");
            //echo "specialties=".implode(",", $specialties)."<br>";

            //Working: 1) get list's (fees) ids get list with not existed specialty, then 2) use these ids to filter the list
            if(1) {
                $specialtiesStr = implode(",", $specialties);
                //https://stackoverflow.com/questions/48942150/many-to-many-relation-select-all-a-except-for-those-linked-to-b
                //1) get list's (fees) ids get list with not existed specialty
                $conn = $em->getConnection();
                $subDql = "
                    SELECT list.id as id
                    FROM transres_requestcategorytypelist list
                    WHERE NOT EXISTS (
                      SELECT 1
                      FROM transres_requestcategory_specialty b
                      WHERE list.id = b.requestcategorytypelist_id
                      AND b.specialtylist_id IN ($specialtiesStr)
                    )
                ";
                $filteredIds = $conn->executeQuery($subDql)->fetchAll(\PDO::FETCH_COLUMN); //fetchAll(\PDO::FETCH_COLUMN,0)

                //2) use these ids to filter the list
                $whereFilteredIds = "list.id IN (:filteredIds)";
                $dqlParameters['filteredIds'] = $filteredIds;
                $dql->andWhere($whereFilteredIds);
            }

            //to review 'IN' and 'NOT IN' if working correctly
            if(0) {
                $transresUtil = $this->container->get('transres_util');
                $orderableProjectSpecialtyIds = $transresUtil->orderableProjectReverseSpecialties($specialties,false);
                $specialtyStr = "projectSpecialties.id IN (" . implode(",", $orderableProjectSpecialtyIds) . ")";
                //echo "specialtyStr=$specialtyStr <br>";
                $dql->andWhere($specialtyStr);

                $specialtyStr = "projectSpecialties.id NOT IN (" . implode(",", $specialties) . ")";
                //echo "specialtyStr=$specialtyStr <br>";
                $dql->andWhere($specialtyStr);
            }

            //$dql->orderBy("list.orderinlist", "ASC"); //testing
        }

        $limit = 30;
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters( $dqlParameters );
        }

        if(0) {
            //$query->setMaxResults(1000);
            echo "query=" . $query->getSql() . "<br>";
            $lists = $query->getResult();
            echo "count=".count($lists)."<br>";
            foreach ($lists as $list) {
                $specArr = array();
                foreach ($list->getProjectSpecialties() as $spec) {
                    $specArr[] = $spec . " (".$spec->getId().")";
                }
                echo $list->getId() . ": hide specialties for " . implode(", ", $specArr) . "<br>";
            }
            dump($lists);
            exit('111');
        }

        $paginationParams = array(
            'defaultSortFieldName' => 'list.orderinlist',
            'defaultSortDirection' => 'ASC',
            'wrap-queries' => true
        );

        $paginator  = $this->container->get('knp_paginator');
        $fees = $paginator->paginate(
            $query,
            $request->query->get('page', 1),    /*page number*/
            $limit,                             /*limit per page*/
            $paginationParams
        );

        $adminUser = false;
        if( $this->isGranted('ROLE_TRANSRES_ADMIN') ) {
            $adminUser = true;
        }

        //get linkToListId by RequestCategoryTypeList from user_platformlistmanagerrootlist (PlatformListManagerRootList)
        $linkToListId = NULL;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PlatformListManagerRootList'] by [PlatformListManagerRootList::class]
        $linkToList = $em->getRepository(PlatformListManagerRootList::class)->findOneByListName('RequestCategoryTypeList');
        if( $linkToList ) {
            $linkToListId = $linkToList->getId();
        }

        return array(
            'filterform' => $filterform->createView(),
            'fees' => $fees,
            'title' => "Fee Schedule",
            'adminUser' => $adminUser,
            'pathbase' => "translationalresearchfeesschedule",
            'linkToListId' => $linkToListId,
        );
    }

    #[Route(path: '/request/fee-schedule/new', name: 'translationalresearchfeesschedule_new_translationalresearch', methods: ['GET'])]
    public function newFeeScheduleAction(Request $request)
    {
        if(
            false == $this->isGranted('ROLE_TRANSRES_ADMIN') &&
            false === $this->isGranted('ROLE_TRANSRES_TECHNICIAN')
        ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        return $this->redirect($this->generateUrl('transresrequestcategorytypes_new'));
    }


    /**
     * Deletes a request entity.
     */
    #[Route(path: '/delete-multiple-requests/', name: 'translationalresearch_requests_multiple_delete', methods: ['GET'])]
    public function deleteMultipleProjectsAction(Request $request)
    {
        exit("Not Available");
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        set_time_limit(600); //600 seconds => 10 min
        ini_set('memory_limit', '2048M');

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $repository = $em->getRepository(TransResRequest::class);
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


    /**
     * http://127.0.0.1/order/translational-research/project-typeahead-search/oid/100/APCP33
     *
     * Used by typeahead js
     */
    #[Route(path: '/project-typeahead-search/{type}/{limit}/{search}', name: 'translationalresearch_project_typeahead_search', methods: ['GET'], options: ['expose' => true])]
    public function getUserDataSearchAction(Request $request) {

        $transresUtil = $this->container->get('transres_util');

        $type = trim((string)$request->get('type') );
        $search = trim((string)$request->get('search') );
        $limit = trim((string)$request->get('limit') );

        //echo "type=".$type."<br>";
        //echo "search=".$search."<br>";
        //echo "limit=".$limit."<br>";
        //exit();

        $availableProjects = $transresUtil->getAvailableRequesterOrReviewerProjects($type,$limit,$search);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($availableProjects));
        return $response;
    }

    /**
     * Finds and displays filtered product/service according to the project specialty
     */
    #[Route(path: '/productservice/ajax/{id}', name: 'translationalresearch_get_productservice_ajax', methods: ['GET'], options: ['expose' => true])]
    public function getProductServiceByProjectAction(Request $request, Project $project)
    {
        if (false == $this->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
            $this->addFlash(
                'warning',
                "You don't have a permission to access the ".$project->getProjectSpecialty()." project specialty"
            );
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $specialty = $project->getProjectSpecialty();

        $products = $transresRequestUtil->getProductServiceByProjectSpecialty($specialty,$project);
        
        $output = array(
            "products" => $products,
            "projectSpecialty" => $specialty.""
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

}