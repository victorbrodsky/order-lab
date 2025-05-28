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

namespace App\TranslationalResearchBundle\Util;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
//use Doctrine\Common\Collections\ArrayCollection;
//use App\TranslationalResearchBundle\Entity\Invoice;
//use App\TranslationalResearchBundle\Entity\InvoiceItem;
//use App\TranslationalResearchBundle\Entity\TransResSiteParameters;
//use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;


/**
 * Created by Oleg Ivanov.
 * Date: 8/13/2018
 * Time: 09:48 AM
 * container name: transres_permission_util
 */
class TransResPermissionUtil
{

    protected $container;
    protected $em;
    protected $security;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->container = $container;
        $this->em = $em;
        $this->security = $security;
    }

    /////////////// INVOICE ///////////////////////
    public function areInvoicesShowableToUser($project) {
        //$user = $this->security->getUser();
        $transresUtil = $this->container->get('transres_util');

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) ) {
            return true;
        }

        //check if the user is
        // technologists (ROLE_TRANSRES_TECHNICIAN)/sys admin/platform admin/deputy platform admin/executive committee member/default reviewers
        if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive($project) ) {
            return true;
        }

        $specialtyStr = $project->getProjectSpecialty()->getUppercaseName();
        //echo "specialtyStr=$specialtyStr <br>";

        if( $this->security->isGranted('ROLE_TRANSRES_TECHNICIAN_'.$specialtyStr) ) {
            return true;
        }

        if( $this->security->isGranted('ROLE_TRANSRES_BILLING_ADMIN_'.$specialtyStr) ) {
            return true;
        }

        //this also check if isUserAllowedSpecialtyObject
        if( $transresUtil->isProjectReviewer($project) ) {
            return true;
        }

        if( $transresUtil->isProjectRequester($project) ) {
            return true;
        }

        return false;
    }
    //similar to isGranted("read",$entity)
    //TODO: executive only has view permission
    public function isUserHasInvoicePermission( $invoice, $action ) {
        $user = $this->security->getUser();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        $project = NULL;

        $processed = false;
        if( $invoice ) {
            if( $this->isUserAllowedAccessInvoiceBySpecialty($invoice) == false ) {
                return false;
            }
        }
        //exit('1');

        //Executive can only view invoice
        //if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() ) {
        //    return true;
        //}
        if( $transresUtil->isAdminOrPrimaryReviewer() ) {
            return true;
        }

        if( $invoice ) {
            $transresRequest = $invoice->getTransresRequest();
            if ($transresRequest) {
                //ok
            } else {
                return true;
            }

            $project = $transresRequest->getProject();
            if ($project) {
                //ok
            } else {
                return true;
            }
        }

        if( $project ) {
            $specialtyStr = $project->getProjectSpecialty()->getUppercaseName();
            $specialtyStr = "_".$specialtyStr;
        } else {
            $specialtyStr = "";
        }

        if( $this->security->isGranted('ROLE_TRANSRES_TECHNICIAN'.$specialtyStr) ) {
            return true;
        }

        if( $this->security->isGranted('ROLE_TRANSRES_BILLING_ADMIN'.$specialtyStr) ) {
            return true;
        }

        if( !$invoice ) {
            if( $action == "create" ) {
                //return true;
                //Exception: executive role can not create invoice
                //if( $this->security->isGranted('ROLE_TRANSRES_EXECUTIVE'.$specialtyStr) ) {
                //    return false;
                //}
                //Who can create a new invoice?: all above (technician, billing admin, sales person)
                //If invoice is NULL, only ROLE_TRANSRES_BILLING_ADMIN_ can create a new invoice, so isInvoiceSalesPerson here is pointless
                //if( $this->isInvoiceSalesPerson($invoice,$user) ) {
                //    return true;
                //}
                return false;
            } else {
                //exit("Logical Error: Invoice is NULL and action is $action");
                return false;
            }
        }

//        if( $action == "create" ) {
//            $processed = true;
//            if( $this->security->isGranted('ROLE_TRANSRES_BILLING_ADMIN_'.$specialtyStr) ) {
//                return true;
//            }
//        }

        //show: to users associated with this invoice, request or project
        if( $action == "view" ) {
            $processed = true;

            if( $this->isInvoiceSalesPerson($invoice,$user) ) {
                return true;
            }

            if( $this->isInvoicePiAndBillingContact($invoice,$user) ) {
                return true;
            }

            //associated with the request as requester
            if( $transresRequestUtil->isRequestRequester($transresRequest) ) {
                return true;
            }

            //associated with the request as reviewer
            if( $transresRequestUtil->isRequestStateReviewer($transresRequest) ) {
                return true;
            }

            //associated with the project
            if( $transresUtil->isProjectRequester($project) ) {
                return true;
            }

            if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() ) {
                return true;
            }
        }

        //view-pdf: show pdf if user can not edit, but can view
        if( $action == "view-pdf" ) {
            $processed = true;

            //if( $this->isUserHasInvoicePermission($invoice,"view") and $this->isUserHasInvoicePermission($invoice,"update") == false ) {
            //    return true;
            //}

            if( $this->isInvoiceSalesPerson($invoice,$user) ) {
                return true;
            }

            if( $this->isInvoicePiAndBillingContact($invoice,$user) ) {
                return true;
            }

            //associated with the request as requester
            if( $transresRequestUtil->isRequestRequester($transresRequest) ) {
                return true;
            }

            //associated with the request as reviewer
            if( $transresRequestUtil->isRequestStateReviewer($transresRequest) ) {
                return true;
            }

            //associated with the project
            if( $transresUtil->isProjectRequester($project) ) {
                return true;
            }

            if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() ) {
                return true;
            }
        }

        //edit: admin, technicians,
        if( $action == "update" ) {
            $processed = true;

            if( $this->isInvoiceSalesPerson($invoice,$user) ) {
                return true;
            }
        }

        if( $action == "generate-invoice-pdf" ) {
            $processed = true;

            if( $this->isInvoiceSalesPerson($invoice,$user) ) {
                return true;
            }
        }

        if( $action == "send-invoice-pdf-email" ) {
            $processed = true;

            if( $this->isInvoiceSalesPerson($invoice,$user) ) {
                return true;
            }
        }

        if( $action == "change-status" ) {
            $processed = true;

            if( $this->isInvoiceSalesPerson($invoice,$user) ) {
                return true;
            }
        }

        if( !$processed ) {
            //exit("Action is invalid: $action");
            $logger = $this->container->get('logger');
            $logger->warning("isUserHasInvoicePermission: Action is invalid: $action");
        }

        return false;
    }
    //check if user allowed to access by the project's specialty
    public function isUserAllowedAccessInvoiceBySpecialty($invoice) {
        $transresUtil = $this->container->get('transres_util');
        $transresRequest = $invoice->getTransresRequest();
        if( $transresRequest ) {
            //ok
        } else {
            return true;
        }
        $project = $transresRequest->getProject();
        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) ) {
            return true;
        }
        return false;
    }
    public function isInvoiceSalesPerson( $invoice, $user ) {
        if( $invoice ) {
            $transresRequest = $invoice->getTransresRequest();
            if ($transresRequest) {
                //ok, go next check
            } else {
                return false;
            }
        }

        $project = $transresRequest->getProject();
        if( $project ) {
            //ok, go next check
        } else {
            return false;
        }

        $specialtyStr = $project->getProjectSpecialty()->getUppercaseName();
        //echo "specialtyStr=$specialtyStr <br>";

        //ROLE_TRANSRES_BILLING_ADMIN role
        if( $this->security->isGranted('ROLE_TRANSRES_BILLING_ADMIN_'.$specialtyStr) ) {
            return true;
        }

        //Invoice's billing contact (salesperson)
        if( $invoice ) {
            $salesperson = $invoice->getSalesperson();
            if ($salesperson && $salesperson->getId() == $user->getId()) {
                return true;
            }
        }

        return false;
    }
    public function isInvoicePiAndBillingContact( $invoice, $user ) {
        $transresRequest = $invoice->getTransresRequest();
        if( $transresRequest ) {
            //ok
        } else {
            return false;
        }

        $project = $transresRequest->getProject();
        if( $project ) {
            //ok
        } else {
            return false;
        }

        $specialtyStr = $project->getProjectSpecialty()->getUppercaseName();

        //ROLE_TRANSRES_BILLING_ADMIN role
        if( $this->security->isGranted('ROLE_TRANSRES_BILLING_ADMIN_'.$specialtyStr) ) {
            return true;
        }

        //Invoice's billing contact
        $billingPerson = $invoice->getBillingContact();
        if( $billingPerson && $billingPerson->getId() == $user->getId() ) {
            return true;
        }

        //salesperson
        $salesperson = $invoice->getSalesperson();
        if( $salesperson && $salesperson->getId() == $user->getId() ) {
            return true;
        }

        //Invoice's PI
        $pi = $invoice->getPrincipalInvestigator();
        if( $pi && $pi->getId() == $user->getId() ) {
            return true;
        }

        //Invoice's Submitter
        $submitter = $invoice->getSubmitter();
        if( $submitter && $submitter->getId() == $user->getId() ) {
            return true;
        }

        return false;
    }
    /////////////// EOF INVOICE ///////////////////////


    /////////////// PROJECT ///////////////////////
    public function hasProjectPermission( $action, $project=null, $projectSpecialtyObject=null ) {

        $transresUtil = $this->container->get('transres_util');

        $done = false;

        $specialtyStr = null;
        if( $project ) {
            $specialtyStr = $project->getProjectSpecialty()->getUppercaseName();
            $specialtyStr = "_" . $specialtyStr;
        }

        if( !$project && $projectSpecialtyObject ) {
            $specialtyStr = $projectSpecialtyObject->getUppercaseName();
            $specialtyStr = "_" . $specialtyStr;
        }
//        echo "specialtyStr=$specialtyStr; action=$action<br>";
//
//        if( $this->security->isGranted("ROLE_TRANSRES_REQUESTER".$specialtyStr) ) {
//            echo "[ROLE_TRANSRES_REQUESTER".$specialtyStr . "] role is OK <br>";
//        }
//        if( $this->security->isGranted("ROLE_TRANSRES_REQUESTER_COVID19") ) {
//            echo "2covid role is OK <br>";
//        }
//        if( $this->security->isGranted("ROLE_TRANSRES_REQUESTER_APCP") ) {
//            echo "2apcp role is OK <br>";
//        }

        if( $action == "create" ) {
            $done = true;
            if(
                $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_REQUESTER".$specialtyStr)
            ) {
                return true;
            } else {
                //echo "No creator role <br>";
            }
        }

        if( $action == "update" || $action == "edit" ) {
            $done = true;
            if(
                $this->security->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr)
                //|| $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER'.$specialtyStr)
            ) {
                return true;
            }

            //check if the user is a ROLE_TRANSRES_PRIMARY_REVIEWER of this project
            if( $project ) {
                $user = $this->security->getUser();
                if( $transresUtil->isReviewsReviewer($user, $project->getFinalReviews()) ) {
                    return true;
                }
            }

            //if( $transresUtil->isProjectEditableByRequester($project,false) ) {
            //    return true;
            //}

            $state = $project->getState();
            if( strpos((string)$state, '_rejected') !== false || $state == 'draft' ) {
                if( $transresUtil->isProjectRequester($project,false) === true ) {
                    return true;
                }
            }

            //return true if the project is in missinginfo state and logged in user is a requester or admin
            if( $transresUtil->isProjectStateRequesterResubmit($project,false) === true ) {
                return true;
            }
        }

        if( $action == "view" || $action == "shows" ) {
            $done = true;

            //all request's requesters associated with this project.
            //We can search all requests and then verify if this user is request's requester
            //but for performance sake, just show the project to requester roles
            //NO for above: AP/CP Project Requester would be able to view all AP/CP projects
//            if( $this->security->isGranted("ROLE_TRANSRES_REQUESTER".$specialtyStr) ) {
//                exit('hasProjectPermission: ROLE_TRANSRES_REQUESTER'.$specialtyStr);
//                return true;
//            }

            if(
                $this->security->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr)
                //|| $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER'.$specialtyStr)
            ) {
                return true;
            }

            //exit('hasProjectPermission');
            //check if the user is a ROLE_TRANSRES_PRIMARY_REVIEWER of this project
            if( $project ) {
                $user = $this->security->getUser();
                if( $transresUtil->isReviewsReviewer($user, $project->getFinalReviews()) ) {
                    return true;
                }
                
                //show if user has ROLE_TRANSRES_BIOINFORMATICIAN and this project->sendComputationalEmail == TRUE
                if( $project->sendComputationalEmail() ) {
                    //exit('hasProjectPermission: sendComputationalEmail='.$project->sendComputationalEmail());
                    if( $this->security->isGranted("ROLE_TRANSRES_BIOINFORMATICIAN") ) {
                        return true;
                    }
                }
            } //if view/show

            if(
                $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_EXECUTIVE".$specialtyStr)
            ) {
                return true;
            }

            if( $project && $transresUtil->isProjectRequester($project,false) ) {
                return true;
            }

            if( $project && $transresUtil->isProjectReviewer($project,false) ) {
                return true;
            }

            //all request's requesters associated with this project
//            if( $this->security->isGranted("ROLE_TRANSRES_REQUESTER".$specialtyStr) ) {
//                return true;
//            }

        }

        if( $action == "cancel" ) {
            $done = true;
            if( $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ) {
                return true;
            }

            if( $transresUtil->isProjectRequester($project,false) ) {
                return true;
            }
        }

        if( $action == "close" ) {
            $done = true;
            if( $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ) {
                return true;
            }
        }

        if( $action == "approve" ) {
            $done = true;
            if( $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ) {
                return true;
            }
        }

        if( $action == "delete" ) {
            $done = true;
            if( $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ) {
                return true;
            }
        }

        if( $action == "review" ) {
            $done = true;
            if(
                $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr)
            ) {
                return true;
            }
    
            if( $transresUtil->isProjectReviewer($project,false) ) {
                return true;
            }
        }

        if( $action == "funded-final-review" ) {
            $done = true;
            if( $project ) {
                if( $project->getFunded() ) {
                    if(
                        $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                        $this->security->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr)
                    ) {
                        return true;
                    }
                }
            }
        }

        if( $action == "view-log" ) {
            $done = true;
            if(
                $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr) ||
                $this->security->isGranted('ROLE_TRANSRES_EXECUTIVE'.$specialtyStr)
            ) {
                return true;
            }
        }

        if( $action == "list" ) {
            $done = true;
            if( $this->security->isGranted("ROLE_TRANSRES_USER") ) {
                return true;
            }
            if(
                $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr) ||
                $this->security->isGranted('ROLE_TRANSRES_EXECUTIVE'.$specialtyStr)
            ) {
                return true;
            }

            if(
                $this->security->isGranted("ROLE_TRANSRES_REQUESTER".$specialtyStr)
            ) {
                return true;
            }
        }

        if( !$done ) {
            throw new \Exception( 'Action is not defined: '.$action );
        }

        return false;
    }
    /////////////// EOF PROJECT ///////////////////////


    /////////////// Request ///////////////////////
    public function hasRequestPermission( $action, $request=null ) {

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        $done = false;
        $project = null;

        if( $request ) {
            $project = $request->getProject();
        }

        $specialtyStr = null;
        if( $project ) {
            $specialtyStr = $project->getProjectSpecialty()->getUppercaseName();
            $specialtyStr = "_" . $specialtyStr;
        }

        if( $action == "create" ) {
            $done = true;
            if(
                $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr)
            ) {
                return true;
            }

            if( $project ) {
                if( $transresUtil->isProjectRequester($project) ) {
                    if( $transresRequestUtil->isRequestCanBeCreated($project) === 1 ) {
                        return true;
                    }
                }
            } else {
                if( $this->security->isGranted("ROLE_TRANSRES_REQUESTER".$specialtyStr) ) {
                    return true;
                }
            }
        }

        //all actions below require request and project
        if( !$request ) {
            return false;
        }
        if( !$project ) {
            return false;
        }

        if( $action == "update" || $action == "edit" ) {
            $done = true;
            if(
                $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr)
            ) {
                return true;
            }

            if( $request->getProgressState() == 'draft' ) {
                if ($transresUtil->isProjectRequester($project) ) {
                    return true;
                }

                if ($transresRequestUtil->isRequestRequester($request)) {
                    return true;
                }
            }
        }

        if( $action == "view" || $action == "show" ) {
            $done = true;

            if(
                $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_EXECUTIVE".$specialtyStr)
            ) {
                return true;
            }

            if( $transresUtil->isProjectRequester($project) ) {
                return true;
            }

            if( $transresRequestUtil->isRequestRequester($request) ) {
                return true;
            }
        }

        if( $action == "delete" ) {
            $done = true;

            if( $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ) {
                return true;
            }
        }

        if( $action == "progress-review" ) {
            $done = true;
//            if(
//                $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
//                $this->security->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr)
//            ) {
//                return true;
//            }
            if(
                $transresRequestUtil->isRequestProgressReviewable($request) &&
                (
                    $transresUtil->isAdminOrPrimaryReviewer($project) ||
                    $transresRequestUtil->isRequestProgressReviewer($request)
                )
            ) {
                return true;
            }
        }
        if( $action == "billing-review" ) {
            $done = true;
            if(
                $transresRequestUtil->isRequestBillingReviewable($request) &&
                (
                    $transresUtil->isAdminOrPrimaryReviewer($project) ||
                    $transresRequestUtil->isRequestBillingReviewer($request)
                )
            ) {
                return true;
            }
        }

        if( $action == "packing-slip" ) {
            $done = true;
            if(
                $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                //$this->security->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr) ||
                $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr)
            ) {
                return true;
            }
        }

        if( !$done ) {
            throw new \Exception( 'Action is not defined: '.$action );
        }

        return false;
    }

    //Test case:
    // Kenny admin for AP/CP CTP Lab
    // Jeff admin for AP/CP
    public function hasProductPermission( $action, $product=null ) {

        if( !$product ) {
            return true;
        }

        if( $action == "update" || $action == "edit" ) {

            $user = $this->security->getUser();

            $project = NULL;
            $request = $product->getTransresRequest();
            if( $request ) {
                $project = $request->getProject();
            }

            $specialtyStr = null;
            if( $project ) {
                $specialtyStr = $project->getProjectSpecialty()->getUppercaseName();
                $specialtyStr = "_" . $specialtyStr;
            }

            if( !$specialtyStr ) {
                return true;
            }

            //ROLE_TRANSRES_TECHNICIAN_APCP_QUEUECTP
            $specialtyQueueRole = $user->hasPartialRole($specialtyStr."_QUEUE");

            //Always allow for Platform Admin
            if( $this->security->isGranted("ROLE_PLATFORM_DEPUTY_ADMIN") ) {
                return true;
            }

//            if(
//                $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
//                $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr)
//            ) {
//                //echo "allow <br>";
//                return true;
//            }
//            else {
//                //echo "notallow <br>";
//                return false;
//            }

            //TODO: if user does not have a specific QUEUE role => check generic role
//            if( $specialtyQueueRole == false ) {
//
//                //echo $category->getProductId().": Role2=["."ROLE_TRANSRES_TECHNICIAN" . $specialtyStr . "]<br>";
//                if (
//                    $this->security->isGranted("ROLE_TRANSRES_ADMIN" . $specialtyStr) ||
//                    $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN" . $specialtyStr)
//                ) {
//                    return true;
//                }
//
//            }

            //$specialtyStr = "";//testing

            //1) user has exact roles
            if(
                $user->hasRole("ROLE_TRANSRES_ADMIN" . $specialtyStr) ||
                $user->hasRole("ROLE_TRANSRES_TECHNICIAN" . $specialtyStr)
            ) {
                //echo "has exact roles<br>";
                return true;
            }

            //$category = $product->getCategory();
            $workQueues = $product->getWorkQueues();
            //echo "workQueues count=".count($workQueues)."<br>";

            ///////////// check work queues ////////////////
            if( $workQueues && count($workQueues) > 0 ) {
                foreach ($workQueues as $workQueue) {
                    $workQueueStr = "_" . $workQueue->getAbbreviation();

                    //echo $category->getProductId().": Role1=["."ROLE_TRANSRES_TECHNICIAN" . $specialtyStr . $workQueueStr."]<br>";
                    //2) user has specialty+workqueue role
                    if (
                        $this->security->isGranted("ROLE_TRANSRES_ADMIN" . $specialtyStr . $workQueueStr) ||
                        $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN" . $specialtyStr . $workQueueStr)
                    ) {
                        return true;
                    }

                    //This case can be disabled because they can be handled by (1)
                    //3) if user does not have '_QUEUE' role check if user has a generic technician role for this specialty
                    if( $specialtyQueueRole == false ) {
                        if(
                            $this->security->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                            $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr)
                        ) {
                            return true;
                        }
                    }


                }//foreach
                ///////////// EOF check work queues ////////////////
            }
            else {

                ///////////// Product does not have work queue ////////////////
                //These two cases below can be disabled because they can be handled by (1)
                //case 'ROLE_TRANSRES_TECHNICIAN_APCP' and 'ROLE_TRANSRES_TECHNICIAN_APCP_QUEUEAPCP'
                //'ROLE_TRANSRES_TECHNICIAN_APCP' => allow
                //TODO: if user has a specialty + QUEUE role => check if has general role
                if( $specialtyQueueRole == false ) {

                    //echo $category->getProductId().": Role1=["."ROLE_TRANSRES_TECHNICIAN" . $specialtyStr . "]<br>";
                    //4) user has generic role
                    if (
                        $this->security->isGranted("ROLE_TRANSRES_ADMIN" . $specialtyStr) ||
                        $this->security->isGranted("ROLE_TRANSRES_TECHNICIAN" . $specialtyStr)
                    ) {
                        return true;
                    }

                } else {

                    //echo $category->getProductId().": Role2=["."ROLE_TRANSRES_TECHNICIAN" . $specialtyStr . "]<br>";
                    //5) user has exact role
                    if (
                        $user->hasRole("ROLE_TRANSRES_ADMIN" . $specialtyStr) ||
                        $user->hasRole("ROLE_TRANSRES_TECHNICIAN" . $specialtyStr)
                    ) {
                        return true;
                    }

                }
                ///////////// EOF Product does not have work queue ////////////////

            }

        }//if action

        return false;
    }
    /////////////// EOF Request ///////////////////////


}



