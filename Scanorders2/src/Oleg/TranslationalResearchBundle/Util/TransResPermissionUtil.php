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

namespace Oleg\TranslationalResearchBundle\Util;


use Doctrine\Common\Collections\ArrayCollection;
use Oleg\TranslationalResearchBundle\Entity\Invoice;
use Oleg\TranslationalResearchBundle\Entity\InvoiceItem;
use Oleg\TranslationalResearchBundle\Entity\TransResSiteParameters;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Created by PhpStorm.
 * Date: 8/13/2018
 * Time: 09:48 AM
 * container name: transres_permission_util
 */
class TransResPermissionUtil
{

    protected $container;
    protected $em;
    protected $secTokenStorage;
    protected $secAuth;

    public function __construct( $em, $container ) {
        $this->container = $container;
        $this->em = $em;
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
    }

    /////////////// INVOICE ///////////////////////
    public function areInvoicesShowableToUser($project) {
        $user = $this->secTokenStorage->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');

        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) ) {
            return true;
        }

        //check if the user is
        // technologists (ROLE_TRANSRES_TECHNICIAN)/sys admin/platform admin/deputy platform admin/executive committee member/default reviewers
        if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() ) {
            return true;
        }

        $specialtyStr = $project->getProjectSpecialty()->getUppercaseName();

        if( $this->container->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN_'.$specialtyStr) ) {
            return true;
        }

        if( $this->container->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_BILLING_ADMIN_'.$specialtyStr) ) {
            return true;
        }

        //this also check if isUserAllowedSpecialtyObject
        if( $transresUtil->isProjectReviewer($project) ) {
            return true;
        }

        return false;
    }
    //similar to isGranted("read",$entity)
    public function isUserHasInvoicePermission( $invoice, $action ) {
        $user = $this->secTokenStorage->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');

        $processed = false;
        if( $invoice ) {
            if( $this->isUserAllowedAccessInvoiceBySpecialty($invoice) == false ) {
                return false;
            }
        }
        //exit('1');

        if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() ) {
            return true;
        }

        $transresRequest = $invoice->getTransresRequest();
        if( $transresRequest ) {
            //ok
        } else {
            return true;
        }

        $project = $transresRequest->getProject();
        if( $project ) {
            //ok
        } else {
            return true;
        }

        $specialtyStr = $project->getProjectSpecialty()->getUppercaseName();

        if( $this->container->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN_'.$specialtyStr) ) {
            return true;
        }

        if( $this->container->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_BILLING_ADMIN_'.$specialtyStr) ) {
            return true;
        }

        if( !$invoice ) {
            if( $action == "create" ) {
                return true;
            } else {
                //exit("Logical Error: Invoice is NULL and action is $action");
                return false;
            }
        }

//        if( $action == "create" ) {
//            $processed = true;
//            if( $this->secAuth->isGranted('ROLE_TRANSRES_BILLING_ADMIN_'.$specialtyStr) ) {
//                return true;
//            }
//        }

        //show: to users associated with this invoice, request or project
        if( $action == "view" ) {
            $processed = true;

            if( $this->isInvoiceBillingContact($invoice,$user) ) {
                return true;
            }

            //associated with the request as requester
            if( $this->isRequestRequester($transresRequest) ) {
                return true;
            }

            //associated with the request as reviewer
            if( $this->isRequestStateReviewer($transresRequest) ) {
                return true;
            }

            //associated with the project
            if( $transresUtil->isProjectRequester($project) ) {
                return true;
            }
        }

        //view-pdf: show pdf if user can not edit, but can view
        if( $action == "view-pdf" ) {
            $processed = true;

            //if( $this->isUserHasInvoicePermission($invoice,"view") and $this->isUserHasInvoicePermission($invoice,"update") == false ) {
            //    return true;
            //}

            //associated with the request as requester
            if( $this->isRequestRequester($transresRequest) ) {
                return true;
            }

            //associated with the request as reviewer
            if( $this->isRequestStateReviewer($transresRequest) ) {
                return true;
            }

            //associated with the project
            if( $transresUtil->isProjectRequester($project) ) {
                return true;
            }
        }

        //edit: admin, technicians,
        if( $action == "update" ) {
            $processed = true;

            if( $this->isInvoiceBillingContact($invoice,$user) ) {
                return true;
            }
        }

        if( $action == "send-invoice-pdf-email" ) {
            $processed = true;

            if( $this->isInvoiceBillingContact($invoice,$user) ) {
                return true;
            }
        }

        if( $action == "change-status" ) {
            $processed = true;

            if( $this->isInvoiceBillingContact($invoice,$user) ) {
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
    public function isInvoiceBillingContact( $invoice, $user ) {
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
        if( $this->secAuth->isGranted('ROLE_TRANSRES_BILLING_ADMIN_'.$specialtyStr) ) {
            return true;
        }

        //Invoice's billing contact (salesperson)
        $salesperson = $invoice->getSalesperson();
        if( $salesperson->getId() == $user->getId() ) {
            return true;
        }

        return false;
    }
    /////////////// EOF INVOICE ///////////////////////


    /////////////// PROJECT ///////////////////////
    public function hasProjectPermission( $action, $project=null ) {

        $specialtyStr = null;
        if( $project ) {
            $specialtyStr = $project->getProjectSpecialty()->getUppercaseName();
            $specialtyStr = "_" . $specialtyStr;
        }

        if( $action == "create" ) {
            if(
                $this->secAuth->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                $this->secAuth->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr) ||
                $this->secAuth->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr) ||
                $this->secAuth->isGranted("ROLE_TRANSRES_REQUESTER".$specialtyStr)
            ) {
                return true;
            }
        }

        if( $action == "update" ) {

        }

        if( $action == "view" ) {
            
        }

        if( $action == "delete" ) {

        }

        if( $action == "review" ) {

            if(
                $this->secAuth->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                $this->secAuth->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr) ||
                $this->secAuth->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr)
            ) {
                return true;
            }



        }

        if( $action == "view-log" ) {
            if(
                $this->secAuth->isGranted("ROLE_TRANSRES_ADMIN".$specialtyStr) ||
                $this->secAuth->isGranted("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyStr) ||
                $this->secAuth->isGranted("ROLE_TRANSRES_TECHNICIAN".$specialtyStr) ||
                $this->secAuth->isGranted('ROLE_TRANSRES_EXECUTIVE'.$specialtyStr)
            ) {
                return true;
            }
        }

    }
    /////////////// EOF PROJECT ///////////////////////




}



