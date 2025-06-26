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

namespace App\VacReqBundle\Controller;



use App\UserdirectoryBundle\Entity\Document;
use App\VacReqBundle\Entity\VacReqRequestTypeList; //process.py script: replaced namespace by ::class: added use line for classname=VacReqRequestTypeList


use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Util\ErrorHelperUser;
use App\VacReqBundle\Entity\VacReqRequest;
use App\VacReqBundle\Entity\VacReqRequestBusiness;
use App\VacReqBundle\Entity\VacReqRequestVacation;
use App\VacReqBundle\Form\VacReqRequestType;
use App\VacReqBundle\Util\VacReqImportData;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

//vacreq site

class RequestController extends OrderAbstractController
{


    /**
     * Creates a new VacReqRequest entity.
     *
     *
     */
    #[Route(path: '/', name: 'vacreq_home', methods: ['GET', 'POST'])]
    #[Route(path: '/new', name: 'vacreq_new', methods: ['GET', 'POST'])]
    #[Route(path: '/carry-over-request/new', name: 'vacreq_carryoverrequest', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Request/edit.html.twig')]
    public function newAction(Request $request)
    {

        if( $this->isGranted('ROLE_VACREQ_OBSERVER') &&
            !$this->isGranted('ROLE_VACREQ_SUBMITTER') &&
            !$this->isGranted('ROLE_VACREQ_PROXYSUBMITTER') &&
            !$this->isGranted('ROLE_VACREQ_APPROVER') &&
            !$this->isGranted('ROLE_VACREQ_SUPERVISOR')
        ) {
            return $this->redirect( $this->generateUrl('vacreq_awaycalendar') );
        }

        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->container->get('vacreq_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $vacreqCalendarUtil = $this->container->get('vacreq_calendar_util');

        $user = $this->getUser();
        $approvalGroupType = $vacreqUtil->getSingleApprovalGroupType($user);
        $routeName = $request->get('_route');
        $newCarryOverRequestStr = null;

        //Testing accrued days
        if(0) {
            $vacreqUtil->testAccruedDays();
        }
        
        //permitted only for users with allowCarryOver
        if( $routeName == "vacreq_carryoverrequest" ) {
            if (
                $vacreqUtil->canCreateNewCarryOverRequest($user) == false &&
                $this->isGranted('ROLE_VACREQ_SUPERVISOR') == false &&
                $this->isGranted('ROLE_VACREQ_ADMIN') == false
            ) {

                if( $approvalGroupType ) {
                    $warning = "As ".strtolower($approvalGroupType).", you are not permitted to submit carry over requests.";
                } else {
                    $warning = "You are not permitted to submit carry over requests.";
                }
                $this->addFlash(
                    'warning',
                    $warning
                );
                return $this->redirect($this->generateUrl('vacreq-nopermission'));
            }
        }

        $entity = new VacReqRequest($user);

        if( false == $this->isGranted("create", $entity) ) {
            //since this is a home page redirect users according to the roles
            //approvers - redirect to incoming requests page
            if( $this->isGranted('ROLE_VACREQ_APPROVER') ) {
                //Flash
                $this->addFlash(
                    'notice',
                    "You don't have a submitter role in order to submit a request. You have been redirected to the 'Incoming Requests' page."
                );
                return $this->redirectToRoute('vacreq_incomingrequests');
            }

            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //$approvalGroupType = $vacreqUtil->getSingleApprovalGroupType($user);

        //set request type
        if( $routeName == "vacreq_carryoverrequest" ) {
            //carryover request
            $requestType = $em->getRepository(VacReqRequestTypeList::class)->findOneByAbbreviation("carryover");
            $title = "Request carry over of vacation days";
            $eventType = "Carry Over Request Created";

            //set Source year (2015)
            //$entity->setSourceYear( date("Y")-1 );
            //set Destination year (2016)
            //$entity->setDestinationYear( date("Y") );

            //$newCarryOverRequestStr = $vacreqUtil->getNewCarryOverRequestString($user,$approvalGroupType);

            //check if 'days' parameter is set in http request
            $carryOverRequestDays = $request->query->get('days');
            if( $carryOverRequestDays ) {
                $entity->setCarryOverDays($carryOverRequestDays);
            }

            $sourceYear = $request->query->get('sourceYear');
            if( $sourceYear ) {
                $entity->setSourceYear($sourceYear);
            }

            $destinationYear = $request->query->get('destinationYear');
            if( $destinationYear ) {
                $entity->setDestinationYear($destinationYear);
            }

            //set tentativeStatus only for non executive submitter
            //$userExecutiveSubmitter = $user->hasRole("ROLE_VACREQ_SUBMITTER_EXECUTIVE");
            //if( !$userExecutiveSubmitter && $entity->getTentativeStatus() == NULL ) {
            //    $entity->setTentativeStatus('pending');
            //}

        } else {
            //business/vacation request
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestTypeList'] by [VacReqRequestTypeList::class]
            $requestType = $em->getRepository(VacReqRequestTypeList::class)->findOneByAbbreviation("business-vacation");
            $title = "Vacation/Business Travel Request";
            $eventType = "Business/Vacation Request Created";

            //show only on vacation request page, hide on carryover page
            $newCarryOverRequestStr = $vacreqUtil->getNewCarryOverRequestString($user,$approvalGroupType);
        }

        //If the current month is July or August, AND the logged in user has the number of remaining vacation days > 0 IN THE PREVIOUS ACADEMIC YEAR
        //$previousCarryOverRequest = $vacreqUtil->getNewCarryOverRequestString($user);

        $entity->setRequestType($requestType);

        //set phone
        $phone = $vacreqUtil->getSubmitterPhone($user);
        $entity->setPhone($phone);

        //set emergency info
        $vacreqUtil->setEmergencyInfo($user,$entity);

        $cycle = 'new';

        $form = $this->createRequestForm($entity,$cycle,$request); //new

        $form->handleRequest($request);

        //check for overlapped date range
        if( $routeName != "vacreq_carryoverrequest" ) {
            $vacreqUser = $entity->getUser();
            if( !$vacreqUser ) {
                $vacreqUser = $user;
            }
            $overlappedRequests = $vacreqUtil->checkRequestForOverlapDates($vacreqUser, $entity); //check for newAction
            if (count($overlappedRequests) > 0) {
                //exit('error: count overlappedRequests='.count($overlappedRequests));
                //$errorMsg = 'You provided overlapped vacation date range with a previous approved vacation request(s) with ID #' . implode(',', $overlappedRequestIds);
                $errorMsg = $vacreqUtil->getOverlappedMessage( $entity, $overlappedRequests, true ); //new
                //echo "new errorMsg=$errorMsg <br>";
                //$form->addError(new FormError($errorMsg));
                $form['requestVacation']['startDate']->addError(new FormError($errorMsg));
                //$form['requestVacation']['endDate']->addError(new FormError($errorMsg));
            } else {
                //exit('no overlaps found');
            }
        }

        //check carry over days limit
        if( $routeName == "vacreq_carryoverrequest" ) {
            if( false == $this->isGranted('ROLE_VACREQ_ADMIN') ) {
                //check carry over days limit
                //$maxCarryOverVacationDays = $userSecUtil->getSiteSettingParameter('maxCarryOverVacationDays', 'vacreq');
                $maxCarryOverVacationDays = $vacreqUtil->getValueApprovalGroupTypeByUser('maxCarryOverVacationDays',$user,$approvalGroupType);
                $carryOverDays = $entity->getCarryOverDays();
                if ($carryOverDays && $maxCarryOverVacationDays) {
                    if ($carryOverDays > $maxCarryOverVacationDays) {
                        $errorMsg = "As per policy, the number of days that can be carried over to the following year is limited to the maximum of "
                            . $maxCarryOverVacationDays;
                        $form['carryOverDays']->addError(new FormError($errorMsg));
                    }
                }
            }
        }
        //exit('testing');

        if( $form->isSubmitted() && $form->isValid() ) { //new

            //exit('form is valid');

            //set final (global) first day away
            $entity->setFinalFirstDayAway(); //new

            //set entire request to 'pending'
            $entity->setBusinessVacationEntireStatus('pending');

            //remove sub requests if empty
            if( !$entity->hasBusinessRequest() ) {
                $subRequestB = $entity->getRequestBusiness();
                if( $subRequestB ) {
                    $entity->setRequestBusiness(null);
                    $em->remove($subRequestB);
                }
            }
            if( !$entity->hasVacationRequest() ) {
                $subRequestV = $entity->getRequestVacation();
                if( $subRequestV ) {
                    $entity->setRequestVacation(null);
                    $em->remove($subRequestV);
                }
            }

            //set tentativeStatus only if tentativeInstitution is set (for non executive submitter)
            $userExecutiveSubmitter = $entity->getUser()->hasRole("ROLE_VACREQ_SUBMITTER_EXECUTIVE");
            if( !$userExecutiveSubmitter && $entity->getTentativeInstitution() ) {
                $entity->setTentativeStatus('pending');
            } else {
                $entity->setTentativeInstitution(NULL);
                $entity->setTentativeStatus(NULL);
            }

            ///////////// TODO: add default inform users to informUsers: $entity->addInformUser(); /////////////
//            $orgInstitution = $entity->getInstitution();
//            if( $orgInstitution ) {
//                $vacreqSettings = $vacreqUtil->getSettingsByInstitution($orgInstitution->getId());
//                foreach ($vacreqSettings->getDefaultInformUsers() as $defaultInformUser) {
//                    $entity->addInformUser($defaultInformUser);
//                }
//            } else {
//                throw $this->createNotFoundException('Unable to find orgInstitution in vacreq request: '.$entity);
//            }
            //$vacreqUtil->setInformUsers($entity);
            ///////////// EOF add default inform users to informUsers: $entity->addInformUser(); /////////////

            //testing
            //echo "sourceYear=".$entity->getSourceYear()."<br>";
            //exit('1');

            //testing
            //add note for holidays
            //Add a sentence into the email notification to the approver upon away request submission
            // IF (and only if) the automatically calculated quantity of total days away was changed by the submitter to a different value.
            //$message = $vacreqCalendarUtil->getHolidaysNote($entity);
            //exit("getHolidaysNote: ".$message);

            if( $entity->hasBusinessRequest() ) {
                $em->getRepository(Document::class)->processDocuments($entity->getRequestBusiness(), 'travelIntakeForm');
            }

            $testing = false;
            //$testing = true;
            if( !$testing ) {
                $em->persist($entity);
                $em->flush();
            }

            $requestName = $entity->getRequestName();
            $emailUtil = $this->container->get('user_mailer_utility');
            //$break = "\r\n";
            $break = "<br>";

            //set confirmation email to submitter and approver and email users
            $css = null;
            $personAway = $entity->getUser();
            $personAwayEmail = $personAway->getSingleEmail();

            if( $personAway->getId() != $user->getId() ) {
                //cc to submitter
                $css = $user->getSingleEmail();
            }

            if( !$personAwayEmail ) {
                //throw $this->createNotFoundException("Person email is null: personAway=".$personAway);
                $personAwayEmail = $css;
                $this->addFlash(
                    'notice',
                    "Away person's email is not set. Sent confirmation email to $personAwayEmail instead."
                );
            }

            $subject = $requestName." ID #".$entity->getId()." Confirmation";
            $message = "Dear ".$entity->getUser()->getUsernameOptimal().",".$break.$break;

            if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
                //You have successfully submitted request #1781 for X vacation days to be carried over from 20XX-20YY to 20YY-20ZZ.
                $message .= "You have successfully submitted the request #".$entity->getId()." for ".$entity->getCarryOverDays();
                $message .= " vacation days to be carried over from ".$entity->getSourceYearRange()." to ".$entity->getDestinationYearRange().".";
            } else {
                //vacation/business request
                $message .= "You have successfully submitted the ".$requestName." #".$entity->getId().".";
            }
            $message .= $break.$break.$entity->printRequest($this->container)."".$break;
            
            // IF (and only if) the automatically calculated quantity of total days away was changed by the submitter to a different value.
            $daysDifferenceNote = $vacreqCalendarUtil->getDaysDifferenceNote($entity);
            if( $daysDifferenceNote ) {
                $message = $message . " " . $daysDifferenceNote . $break;
            }
            
            $message .= $break."You will be notified once your request is reviewed and its status changes.";
            $message .= $break.$break."**** PLEASE DO NOT REPLY TO THIS EMAIL ****";
            $emailUtil->sendEmail( $personAwayEmail, $subject, $message, $css, null );
            
            //set confirmation email to approver and email users
            $approversNameStr = $vacreqUtil->sendConfirmationEmailToApprovers( $entity );

            //Event Log
            $event = $requestName . " for ".$entity->getUser()." has been submitted.".
                " Confirmation email has been sent to ".$approversNameStr;
            $event = $event . $break.$break. $entity->printRequest();

            if( $daysDifferenceNote ) {
                $event = $event . $break . $daysDifferenceNote;
            }

            if( $testing === false ) {
                $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $event, $user, $entity, $request, $eventType);
            }

            //exit('exit event='.$event);

            //Flash
            $this->addFlash(
                'notice',
                $event
            );

            //check if requested carry over days are already approved or denied
            if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
                //check if requested carry over days are already approved or denied
                $resCarryOverRequest = $vacreqUtil->processVacReqCarryOverRequest($entity,true); //new carryover request
                $carryOverWarningMessageLog = $resCarryOverRequest['carryOverWarningMessageLog'];
                $eventType = "Existing Days Carry Over Request Created";
                if( $testing === false ) {
                    $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'),
                        $carryOverWarningMessageLog, $user, $entity, $request, $eventType);
                }
            }

            if( $testing ) {
                exit("Request submitted: ".$entity);
            }

            return $this->redirectToRoute('vacreq_show', array('id' => $entity->getId()));
        }//if $form -> isSubmitted()

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();
        $accessreqsCount = 0;
        if( is_array($accessreqs) ) {
            $accessreqsCount = count($accessreqs);
        }

        //calculate approved vacation days in total.
        //$bruteForce = false;
        $totalApprovedDaysString = $vacreqUtil->getApprovedDaysString($user);
        //echo "totalApprovedDaysString=".$totalApprovedDaysString."<br>";

        //get header messages
        //$messages = $vacreqUtil->getHeaderInfoMessages( $user );
        //$accruedDaysString = $messages['accruedDaysString'];
        //$carriedOverDaysString = $messages['carriedOverDaysString'];
        //$remainingDaysString = $messages['remainingDaysString'];

        ///////////// TODO: get new header with parameters from VacReqApprovalTypeList /////////////
        // (noteForVacationDays, noteForCarryOverDays, accrued days, max days)
        //$approvalGroupType = $vacreqUtil->getSingleApprovalGroupType($user);
        $messages = $vacreqUtil->getHeaderInfoMessages($user, $approvalGroupType);
        $accruedDaysString = $messages['accruedDaysString'];
        $carriedOverDaysString = $messages['carriedOverDaysString'];
        $remainingDaysString = $messages['remainingDaysString'];
        $totalAccruedDays = $messages['totalAccruedDays'];
        $remainingDays = $messages['remainingDays'];
        $overlapped = $messages['overlapped'];

        //Notes are getting from VacReqApprovalTypeList (currently 'fellow' and 'faculty' approval group types)
        $noteForVacationDays = NULL;
        //$noteForCarryOverDays = NULL;
        if( $routeName == "vacreq_new" ) {
            $noteForVacationDays = $vacreqUtil->getValueApprovalGroupTypeByUser("noteForVacationDays",$user,$approvalGroupType);
            //$noteForCarryOverDays = $vacreqUtil->getValueApprovalGroupTypeByUser("noteForCarryOverDays",$user,$approvalGroupType);
        }
        //echo "noteForVacationDays=$noteForVacationDays <br>";

        $noteForCarryOverDays = NULL;
        if( $routeName == "vacreq_carryoverrequest" ) {
            $noteForCarryOverDays = $vacreqUtil->getValueApprovalGroupTypeByUser("noteForCarryOverDays",$user,$approvalGroupType);
        }
        //echo "noteForCarryOverDays=$noteForCarryOverDays <br>";

        ///////////// EOF get new header with parameters from VacReqApprovalTypeList /////////////

        //get $requestTypeCarryOverId
        $carryoverPendingRequestsCount = 0;
        $carryoverPendingRequests = $vacreqUtil->getPendingCarryOverRequests($user);
        if( is_array($carryoverPendingRequests) ) {
            $carryoverPendingRequestsCount = count($carryoverPendingRequests);
        }
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestTypeList'] by [VacReqRequestTypeList::class]
        $requestTypeCarryOver = $em->getRepository(VacReqRequestTypeList::class)->findOneByAbbreviation("carryover");
        if( $requestTypeCarryOver ) {
            $requestTypeCarryOverId = $requestTypeCarryOver->getId();
        } else {
            $requestTypeCarryOverId = null;
        }

        $overlappedMessage = $vacreqUtil->getHeaderOverlappedMessage($user);

//        $internationalTravelRegistryLink = '<a target="_blank" '.
//            'href="https://riskmanagement.weill.cornell.edu/travel/travel-registration">'.
//            'International Travel Registry</a>';
//        $internationalTravelRegistryLink2 = '<a target="_blank" '.
//            'href="https://weillcornell.az1.qualtrics.com/jfe/form/SV_5oTGNyMbGzai074">'.
//            'Complete a brief registration form</a>';
//        $internationalTravelRegistry = "WCM is committed to ensuring that faculty, staff,".
//        " and students remain safe when traveling. Moving forward, all individuals conducting".
//        " WCM activities will be required to document their trip in the $internationalTravelRegistryLink.".
//        " Registering your trip allows you to receive real-time alerts and notices that are specific".
//        " to your travel and facilitates WCM emergency support when needed.".
//        "<br><br>".
//        " $internationalTravelRegistryLink2";

        $internationalTravelRegistry = $userSecUtil->getSiteSettingParameter('intTravelNote','vacreq');

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'accessreqs' => $accessreqsCount,
            'carryoverPendingRequests' => $carryoverPendingRequestsCount,
            'requestTypeCarryOverId' => $requestTypeCarryOverId, //function
            'title' => $title,
            'overlappedMessage' => $overlappedMessage, //function
            'totalAccruedDays' => $totalAccruedDays,
            'remainingDays' => $remainingDays,
            //Get header's note from VacReqApprovalTypeList
            'noteForVacationDays' => $noteForVacationDays,
            'noteForCarryOverDays' => $noteForCarryOverDays,
            //First header - vacation infor + carry over
            'totalApprovedDaysString' => $totalApprovedDaysString, //function
            //header
            'accruedDaysString' => $accruedDaysString, //getHeaderInfoMessages
            'carriedOverDaysString' => $carriedOverDaysString, //getHeaderInfoMessages
            'remainingDaysString' => $remainingDaysString, //getHeaderInfoMessages
            //Second header - only carry over
            'newCarryOverRequestStr' => $newCarryOverRequestStr, //function
            'internationalTravelRegistry' => $internationalTravelRegistry,
            'overlapped' => $overlapped

        );
    }


    /**
     * Show: Finds and displays a VacReqRequest entity.
     *
     *
     */
    #[Route(path: '/show/{id}', name: 'vacreq_show', methods: ['GET'])]
    #[Template('AppVacReqBundle/Request/edit.html.twig')]
    public function showAction(Request $request, $id)
    {
        if( false == $this->isGranted('ROLE_VACREQ_USER') ) {
            //exit('show: no permission');
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $entity = $em->getRepository(VacReqRequest::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Vacation Request by id='.$id);
        }

        if( false == $this->isGranted("read", $entity) ) {
            //exit('show: no permission');
            //TODO: fix it for proxy submitter
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }
        //exit('show: ok permission');

        //echo "req=".$entity->printRequest($this->container);
        //exit('1');

        $cycle = 'show';

        //get request type
        if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
            $title = "Request carry over of vacation days";
        } else {
            $title = "Vacation/Business Travel Request";
        }

        $form = $this->createRequestForm($entity,$cycle,$request); //show

        return array(
            'entity' => $entity,
            'cycle' => $cycle,
            'form' => $form->createView(),
            'title' => $title
            //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edit: Displays a form to edit an existing VacReqRequest entity.
     *
     *
     */
    #[Route(path: '/edit/{id}', name: 'vacreq_edit', methods: ['GET', 'POST'])]
    #[Route(path: '/review/{id}', name: 'vacreq_review', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Request/edit.html.twig')]
    public function editAction(Request $request, $id)
    {
        //$deleteForm = $this->createDeleteForm($vacReqRequest);
        //$editForm = $this->createForm('App\VacReqBundle\Form\VacReqRequestType', $vacReqRequest);

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->container->get('vacreq_util');
        $user = $this->getUser();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $entity = $em->getRepository(VacReqRequest::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Vacation Request by id='.$id);
        }

//        //can not edit if request is already processed by an approver: status == completed
//        if( $entity->getStatus() == 'completed' ) {
//            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
//        }

        //TODO: for simplicity and safety, does not allow to edit a submitted request if not admin.
        //TODO: why? When request is submitted, email notification sent to the approver with request information.
        //TODO: If a user will edit the request, the approver can click on the email link to approve/deny modified request considering outdated request information.
        //can't edit or review if request is not pending
//        if( $entity->getStatus() != "pending" && $entity->getOverallStatus() != "pending" ) {
//            //Flash
//            $this->addFlash(
//                'warning',
//                'This request can not be modified because it is not pending anymore.'
//            );
//            return $this->redirectToRoute('vacreq_show', array('id' => $entity->getId()));
//        }


        //check permission
        $routName = $request->get('_route');
        if( false == $this->isGranted('ROLE_VACREQ_ADMIN') ) {
            if ($routName == 'vacreq_review') {
                if (false == $this->isGranted("changestatus", $entity)) {
                    //exit("vacreq_review: no permission to changestatus");
                    $this->addFlash(
                        'warning',
                        "no permission to review/change the status of this carry over request."
                    );
                    return $this->redirect($this->generateUrl('vacreq-nopermission'));
                }
            } else {
                if (false == $this->isGranted("update", $entity)) {
                    //exit('vacreq_edit: no permission to update');
                    $this->addFlash(
                        'warning',
                        "no permission to update this request."
                    );
                    return $this->redirect($this->generateUrl('vacreq-nopermission'));
                }
            }
        }
        //exit('testing: approval of carry over request OK'); //testing

        $cycle = 'edit';
        if( $routName == 'vacreq_review' ) {
            $cycle = 'review';
        }

        $changedInfoArr = array();

        $originalTentativeStatus = $entity->getTentativeStatus();
        $originalStatus = $entity->getStatus();
        //$originalCarryOverDays = $entity->getCarryOverDays();

        //check if requested carry over days are already approved or denied
        if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
            //check if requested carry over days are already approved or denied
            $resCarryOverRequest = $vacreqUtil->processVacReqCarryOverRequest($entity,true);    //review carryover request
            $carryOverWarningMessage = $resCarryOverRequest['carryOverWarningMessage'];
            $carryOverWarningMessageLog = $resCarryOverRequest['carryOverWarningMessageLog'];
        } else {
            $carryOverWarningMessage = null;
            $carryOverWarningMessageLog = null;
        }

        ///// Set totalAccruedDays and remainingDays for carry-over validation /////
        $approvalGroupType = NULL;
        $vacreqSettings = $vacreqUtil->getSettingsByVacreq($entity);
        if( $vacreqSettings ) {
            $approvalGroupType = $vacreqSettings->getApprovalType();
        }
        $messages = $vacreqUtil->getHeaderInfoMessages($user, $approvalGroupType);
        //$accruedDaysString = $messages['accruedDaysString'];
        //$carriedOverDaysString = $messages['carriedOverDaysString'];
        //$remainingDaysString = $messages['remainingDaysString'];
        $totalAccruedDays = $messages['totalAccruedDays'];
        $remainingDays = $messages['remainingDays'];
        ///// EOF Set totalAccruedDays and remainingDays for carry-over validation /////

        $form = $this->createRequestForm($entity,$cycle,$request); //edit/review

        $form->handleRequest($request);

        //check for overlapped date range
        //$overallStatus = $entity->getStatus();
        //$routeName = $request->get('_route');
        if( $entity->getRequestTypeAbbreviation() == "business-vacation" ) {

            $overlappedRequests = $vacreqUtil->checkRequestForOverlapDates($entity->getUser(), $entity);    //check for editAction
            //echo 'overlappedRequests count='.count($overlappedRequests)."<br>";
            if (count($overlappedRequests) > 0) {
                //$errorMsg = 'This request has overlapped vacation date range with a previous approved vacation request(s) with ID #' . implode(',', $overlappedRequestIds);
                $errorMsg = $vacreqUtil->getOverlappedMessage( $entity, $overlappedRequests, true ); //edit, review
                //echo "edit errorMsg=$errorMsg <br>";
                //$form['requestVacation']['startDate']->addError(new FormError($errorMsg));
                //$form['requestVacation']['approverComment']->addError(new FormError($errorMsg));
                //$form['requestVacation']->addError(new FormError($errorMsg));
                $form->addError(new FormError($errorMsg));

//                if( $form->isSubmitted() ) {
//                    $errorHelper = new ErrorHelperUser();
//                    $errors = $errorHelper->getErrorMessages($form);
//                    echo "<br>form errors:<br>";
//                    print_r($errors);
//                }

                //print_r($form->getErrors());
                //$errors = $form->getErrorsAsString();
//                $errors = (string) $form->getErrors(true);
//                echo "errors=[$errors]<br>";
//                if( $form->isSubmitted() ) {
//                    if ($form->isValid()) {
//                        echo "form valid<br>";
//                    } else {
//                        echo "form invalid!!!<br>";
//                    }
//                }

            } else {
                //exit('no overlaps found');
            }
        }

        $totalAccruedDays = NULL;
        $remainingDays = NULL;
        //check carry over days limit (edit). Should we have this only for "new" request?
        if( $entity->getRequestTypeAbbreviation() == "carryover"  ) {
            if( false == $this->isGranted('ROLE_VACREQ_ADMIN') ) {
                //check carry over days limit
                //$userSecUtil = $this->container->get('user_security_utility');
                //$maxCarryOverVacationDays = $userSecUtil->getSiteSettingParameter('maxCarryOverVacationDays', 'vacreq');
                $approvalGroupType = NULL;
                $vacreqSettings = $vacreqUtil->getSettingsByVacreq($entity);
                if( $vacreqSettings ) {
                    $approvalGroupType = $vacreqSettings->getApprovalType();
                }

                $maxCarryOverVacationDays = $vacreqUtil->getValueApprovalGroupTypeByUser('maxCarryOverVacationDays',$entity->getUser(),$approvalGroupType);
                $carryOverDays = $entity->getCarryOverDays();
                if ($carryOverDays && $maxCarryOverVacationDays) {
                    if ($carryOverDays > $maxCarryOverVacationDays) {
                        $errorMsg = "As per policy, the number of days that can be carried over to the following year is limited to the maximum of "
                            . $maxCarryOverVacationDays;
                        //exit($errorMsg);
                        $form['carryOverDays']->addError(new FormError($errorMsg));
                    }
                }
            }
        }

        if( $form->isSubmitted() && $form->isValid() ) { //edit, review

            //exit("Review request");

            /////////////// log status ////////////////////////
            $statusMsg = $entity->getId()." (".$routName.")".": set by user=".$user;
            if( $entity->hasBusinessRequest() ) {
                $statusB = $entity->getRequestBusiness()->getStatus();
                $statusMsg = $statusMsg . " statusBusiness=".$statusB;
            }
            if( $entity->hasVacationRequest() ) {
                $statusV = $entity->getRequestVacation()->getStatus();
                $statusMsg = $statusMsg . " statusVacation=".$statusV;
            }
            $logger->notice($statusMsg);
            /////////////// EOF log status ////////////////////////

            //exit('form is valid');
            if( $routName == 'vacreq_review' ) { //review
                ///////////////// review //////////////////////////

                if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
                    ///////////////// carryover //////////////////////

                    $action = "Undefined Action";
                    $changedStatusCount = 0;
                    $step = 'first-step'; //force to approval step

                    $newTentativeStatus = $entity->getTentativeStatus();
                    //echo "$originalTentativeStatus=?".$newTentativeStatus."<br>"; //testing
                    if(  $newTentativeStatus && $originalTentativeStatus != $newTentativeStatus ) {
                        $status = $newTentativeStatus;
                        $changedStatusCount++;
                    }
                    $newStatus = $entity->getStatus();
                    //echo "$originalStatus=?".$newStatus."<br>"; //testing
                    if( $newStatus && $originalStatus != $newStatus ) {
                        $status = $newStatus;
                        $changedStatusCount++;
                        $step = 'second-step';
                    }

                    //if two statuses are changed (only admin can do it), then use one step (as executive) approval with the final status
                    //echo "changedStatusCount=$changedStatusCount <br>"; //testing
                    if( $changedStatusCount > 1 ) {
                        //echo "########## set tentative to NULL <br>";
                        //$entity->setTentativeStatus(NULL);
                        //$entity->setTentativeInstitution(NULL);
                        $status = $entity->getStatus();
                        //$status = $newStatus;
                        //$step = 'second-step';
                    }

                    //if org inst is null => always second step
                    if( !$entity->getInstitution() ) {
                        $step = 'second-step';
                    }

                    $testing = false;
                    //$testing = true;

                    if( $changedStatusCount > 0 ) {
                        //reset statuses to original
                        $entity->setTentativeStatus($originalTentativeStatus);
                        $entity->setStatus($originalStatus);

                        $withRedirect = false;
                        $update = true;
                        //main logic to change status and send emails
                        $action = $vacreqUtil->processChangeStatusCarryOverRequest( 
                            $entity, 
                            $status, 
                            $user, 
                            $request, 
                            $withRedirect, 
                            $update, 
                            $step, 
                            $testing ); //review
                        //exit("action=".$action);

                        if( $action == 'vacreq-nopermission' ) {
                            return $this->redirectToRoute('vacreq-nopermission');
                        }

                        if( $testing === false ) {
                            $em->persist($entity);
                            $em->flush();
                        } else {
                            exit("EOF editAction: review");
                        }

                        $logger->notice("Review CarryOver request ID=".$entity->getId()."; status=".$status."; resulting action=".$action);
                    } else {
                        $logger->warning("Review CarryOver request ID=".$entity->getId()."; failed to process: changedStatusCount=".$changedStatusCount);
                    }

                    $eventType = 'Carry Over Request Updated';

                } //carryover
                    else
                {
                    ///////////////// business/vacation request //////////////////////
                    //set final (global) status according to sub-requests status:
                    //only two possible actions: reject or approved
                    $entity->setFinalStatus(); //vacreq_review

                    $overallStatus = $entity->getStatus();

                    if( $overallStatus == "pending" ) {
                        $entity->setApprover(null);
                    } else {
                        $entity->setApprover($user);
                    }
                    //exit('vacreq: overallStatus='.$overallStatus."; status=".$entity->getDetailedStatus());

                    $entity->setExtraStatus(NULL);
                    $em->persist($entity);
                    $em->flush();

                    $eventType = 'Business/Vacation Request '.ucwords($overallStatus);
                    $action = $overallStatus;

                    //send respond email for the request changed by the form
                    $vacreqUtil->sendSingleRespondEmailToSubmitter( $entity, $user, $overallStatus );
                }

            }//$routName == 'vacreq_review'
                else
            {
                ///////////////// update vacreq_edit (edit page does not allow to change status) //////////////////////////

                $entity->setUpdateUser($user);

                //remove sub requests if empty
                if( !$entity->hasBusinessRequest() ) {
                    //echo "no business req => remove <br>";
                    $subRequestB = $entity->getRequestBusiness();
                    if( $subRequestB ) {
                        $entity->setRequestBusiness(null);
                        $em->remove($subRequestB);
                    }
                } else {
                    //$subRequestB = $entity->getRequestBusiness();
                    //echo "yes business req<br>";
                    //$em->persist($subRequestB);
                    //$em->persist($entity->getRequestBusiness());
                }
                if( !$entity->hasVacationRequest() ) {
                    $subRequestV = $entity->getRequestVacation();
                    if( $subRequestV ) {
                        $entity->setRequestVacation(null);
                        $em->remove($subRequestV);
                    }
                } else {
                    //$subRequestV = $entity->getRequestVacation();
                    //$em->persist($subRequestV);
                    //$em->persist($entity->getRequestVacation());
                }
                //exit('1');

                ///////////// TODO: add default inform users to informUsers: $entity->addInformUser(); /////////////
//                $orgInstitution = $entity->getInstitution();
//                if( $orgInstitution ) {
//                    $vacreqSettings = $vacreqUtil->getSettingsByInstitution($orgInstitution->getId());
//                    foreach ($vacreqSettings->getDefaultInformUsers() as $defaultInformUser) {
//                        $entity->addInformUser($defaultInformUser);
//                    }
//                } else {
//                    throw $this->createNotFoundException('Unable to find orgInstitution in vacreq request: '.$entity);
//                }
                //echo "1count=".count($entity->getInformUsers())."<br>";
                //$vacreqUtil->setInformUsers($entity);
                //echo "2count=".count($entity->getInformUsers())."<br>";
                    //exit('111');
                ///////////// EOF add default inform users to informUsers: $entity->addInformUser(); /////////////

                /////////////// Add event log on edit (edit or add collection) ///////////////
                /////////////// Must run before flash DB. When DB is flashed getEntityChangeSet() will not work ///////////////
                $changedInfoArr = $vacreqUtil->setEventLogChanges($entity);

                //re-set final (global) first day away
                $entity->setFinalFirstDayAway(); //update

                //TODO: processDocument
                if( $entity->hasBusinessRequest() ) {
                    $em->getRepository(Document::class)->processDocuments($entity->getRequestBusiness(), 'travelIntakeForm');
                }

                //echo "0 business req=".$entity->getRequestBusiness()."<br>";
                //exit('1');

                $em->persist($entity);
                $em->flush();
                //echo "1 business req=".$entity->getRequestBusiness()."<br>";
                //exit('1');

                $action = "updated";

                if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
                    $eventType = 'Carry Over Request Updated';
                } else {
                    $eventType = 'Business/Vacation Request Updated';
                }

            } //if else: review or update

            if( $action == 'pending' ) {
                $action = 'set to Pending';
            }

            //Event Log
            //$break = "\r\n";
            $break = "<br>";
            $event = "Request ID #".$entity->getID()." for ".$entity->getUser()." has been ".$action." by ".$user.$break;
            $event .= $entity->getDetailedStatus().$break.$break;
            $userSecUtil = $this->container->get('user_security_utility');

            //Flash
            $this->addFlash(
                'notice',
                $event
            );

            //get request type
            if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
                //This will make sure that only one carryover request exists for the current year, and it will cancel all previous carryover requests
                $vacreqUtil->syncVacReqCarryOverRequest($entity,$originalStatus); //vacreq_review, vacreq_edit
            }

            //set event log for objects
            if( count($changedInfoArr) > 0 ) {
                //$user = $this->getUser();
                $event .= "Updated Data:".$break;
                $event .= implode("<br>", $changedInfoArr);
            }

            $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

            if( $carryOverWarningMessageLog ) {
                $eventType = 'Existing Days Carry Over Request Updated';
                $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'),$carryOverWarningMessageLog,$user,$entity,$request,$eventType);
            }

            if( $routName == 'vacreq_review' ) {
                return $this->redirectToRoute('vacreq_incomingrequests',array('filter[requestType]'=>$entity->getRequestType()->getId()));
            } else {
                return $this->redirectToRoute('vacreq_show', array('id' => $entity->getId()));
            }

        }

        $review = false;
        if( $request ) {
            if( $request->get('_route') == 'vacreq_review' ) {
                $review = true;
            }
        }

        //get request type
        if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
            $title = "Request carry over of vacation days";
        } else {
            $title = "Vacation/Business Travel Request";
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'review' => $review,
            'title' => $title,
            'carryOverWarningMessage' => $carryOverWarningMessage,
            'totalAccruedDays' => $totalAccruedDays,
            'remainingDays' => $remainingDays
            //'delete_form' => $deleteForm->createView(),
        );
    }



    /**
     * approved, rejected, pending, canceled
     */
    #[Route(path: '/status/{id}/{requestName}/{status}', name: 'vacreq_status_change', methods: ['GET'])]
    #[Route(path: '/estatus/{id}/{requestName}/{status}', name: 'vacreq_status_email_change', methods: ['GET'])]
    #[Template('AppVacReqBundle/Request/edit.html.twig')]
    public function statusAction(Request $request, $id, $requestName, $status)
    {

        //if( false == $this->isGranted('ROLE_VACREQ_APPROVER') ) {
        //    return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        //}

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $routeName = $request->get('_route');
        $user = $this->getUser();
        $vacreqUtil = $this->container->get('vacreq_util');

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $entity = $em->getRepository(VacReqRequest::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Request by id=' . $id);
        }

        //check permissions
//        if( $this->isGranted('ROLE_VACREQ_APPROVER') || $this->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
//            if( false == $this->isGranted("changestatus", $entity) ) {
//                return $this->redirect($this->generateUrl('vacreq-nopermission'));
//            }
//        } elseif( $this->isGranted("update", $entity) ) {
//            if( $status != 'canceled' && $status != 'pending' && $status != 'cancellation-request' ) {
//                return $this->redirect($this->generateUrl('vacreq-nopermission'));
//            }
//        } else {
//            return $this->redirect($this->generateUrl('vacreq-nopermission'));
//        }

        $originalStatus = $entity->getStatus();

        /////////////// log status ////////////////////////
        $logger->notice("RequestController statusAction: " . $entity->getId() . " (" . $routeName . ")" . ": status=" . $status . "; set by user=" . $user);
        /////////////// EOF log status ////////////////////////

//        if( $this->isGranted("update", $entity) ) {
//            exit($entity->getId().": can update OK");
//        }
//        exit($entity->getId().": can not update");

        if( $this->isGranted("changestatus", $entity) ) {
            //Approvers can change status to anything
        }
//        elseif( $this->isGranted("read", $entity) )
//        {
//            //viewers (submitter, person away) can only set status to: canceled, pending
//            if( $status != "canceled" && $status != "pending" ) {
//                //Flash
//                $this->addFlash(
//                    'warning',
//                    "You can not change status of this ".$entity->getRequestName()." with ID #".$entity->getId()." to ".$status
//                );
//                $logger->error($user." has no permission to change status to ".
//                    $status." for request ID #".$entity->getId().
//                    ". Reason: status can be changed only to pending or canceled");
//                return $this->redirect($this->generateUrl('vacreq-nopermission'));
//
//        }
        elseif( $this->isGranted("update", $entity) )
        {
            //exit("can update OK");
            //Owner can only set status to: canceled, pending
            if( $status != "canceled" && $status != "pending" ) {
                //Flash
                $this->addFlash(
                    'warning',
                    "You can not change status of this ".$entity->getRequestName()." with ID #".$entity->getId()." to ".$status
                );
                $logger->error($user." has no permission to change status to ".$status." for request ID #".$entity->getId().". Reason: request is not pending or canceled");
                return $this->redirect($this->generateUrl('vacreq-nopermission'));
            }
        } else {
            $logger->error($user." has no permission to change status to ".$status." for request ID #".$entity->getId().". Reason: user does not have permission to changestatus or update for this request");
            //exit("nopermission statusAction: ".$status);
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //if not pending and vacreq_status_email_change => redirect to incoming request page
        if( $entity->getStatus() != "pending" && $routeName == 'vacreq_status_email_change' ) {
            $modificationDate = "";
            $updateDate = $entity->getUpdateDate();
            if( $updateDate ) {
                $modificationDate = " on ".$updateDate->format('m/d/Y H:i:s');
            }
            //Flash
            $this->addFlash(
                'notice',
                //"This ".$entity->getRequestName()." ID #" . $entity->getId()." has already been completed by ".$entity->getApprover()
                "This ".$entity->getRequestName()." ID #" . $entity->getId()." is not pending anymore and has been modified by ".$entity->getUpdateUser().$modificationDate
            );
            return $this->redirectToRoute('vacreq_incomingrequests',array('filter[requestType]'=>$entity->getRequestType()->getId()));
        }


        //check for overlapped date range if a new status is approved
        if( $status == "approved" ) {
            $overlappedRequests = $vacreqUtil->checkRequestForOverlapDates($entity->getUser(), $entity); //check for statusAction
            //exit("count=".count($overlappedRequests));
            if (count($overlappedRequests) > 0) {

                //If the dates overlap with the dates of another APPROVED request EXACTLY =>
                //This request has been canceled as a duplicate." and set the status to "Canceled" instead of "Approved".
                if( $vacreqUtil->hasOverlappedExactly( $entity, $overlappedRequests ) ) {

                    //set status to cancel
                    $status = 'canceled';

                    $errorMsg = $vacreqUtil->getOverlappedMessage( $entity, $overlappedRequests, null, true ); //change status: approved, rejected, pending, canceled
                    $errorMsg .= "<br>This request has been canceled as a duplicate.<br>";

                    $this->addFlash(
                        'warning',
                        $errorMsg
                    );
                } else {
                    $errorMsg = $vacreqUtil->getOverlappedMessage( $entity, $overlappedRequests );  //change status: approved, rejected, pending, canceled
                    $this->addFlash(
                        'warning',
                        $errorMsg
                    );
                    return $this->redirectToRoute('vacreq_show',array('id'=>$entity->getId()));
                }

            } else {
                //exit('no overlaps found');
            }
        }


        if( $status ) {

            $statusSet = false;

            if( $requestName == 'business' ) {
                $businessRequest = $entity->getRequestBusiness();
                if( $businessRequest ) {
                    $businessRequest->setStatus($status);
                    //set overall status
                    if( $status == 'pending' ) {
                        $entity->setStatus('pending');
                    } else {
                        $entity->setStatus('completed');
                    }
                    $statusSet = true;
                }
            }

            if( $requestName == 'vacation' ) {
                $vacationRequest = $entity->getRequestVacation();
                if( $vacationRequest ) {
                    $vacationRequest->setStatus($status);
                    //set overall status
                    if( $status == 'pending' ) {
                        $entity->setStatus('pending');
                    } else {
                        $entity->setStatus('completed');
                    }
                    $statusSet = true;
                }
            }

            if( $requestName == 'entire' ) {

                $requestName = $entity->getRequestName(); //'business travel and vacation';

                $entity->setEntireStatus($status);

                if( $entity->getRequestTypeAbbreviation() == "business-vacation" ) {
                    if( $status != 'canceled' && $status != 'pending' ) {
                        $status = 'completed';
                    }
                }
                if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
                    //exit("status=".$status);

                    //#489 (41)
                    if( $status == "approved" ) {
                        //process carry over request days if request is approved
                        $res = $vacreqUtil->processVacReqCarryOverRequest($entity); //change status
                        if( $res && $res['exists'] == true ) {
                            //warning for overwrite:
                            //"FirstName LastName already has X days carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year on file.
                            // This carry over request asks for N days to be carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year.
                            // Please enter the total amount of days that should be carried over 20YY-20ZZ academic year to the 20ZZ-20MM academic year: [ ]"
                            //exit('exists days='.$res['days']);
                            return $this->redirectToRoute('vacreq_review',array('id'=>$entity->getId()));
                        }
                        if( $res && $res['exists'] == false ) {
                            //save
                            $userCarryOver = $res['userCarryOver'];
                            $em->persist($userCarryOver);
                        }
                    }

                    //re-submit request
                    //echo "statuses:".$status." && ".$originalStatus."<br>";
                    if( $status == "pending" && $originalStatus == "canceled" ) {
                        //re-set tentative status according to the submitter group
                        //set tentativeStatus only for non executive submitter
                        $userExecutiveSubmitter = $entity->getUser()->hasRole("ROLE_VACREQ_SUBMITTER_EXECUTIVE");

                        //re-set tentative status according to the TentativeInstitution
                        if( !$userExecutiveSubmitter && $entity->getTentativeInstitution() ) {
                            $entity->setTentativeStatus('pending');
                        } else {
                            $entity->setTentativeStatus(NULL);
                        }
                    }

//                    if( $status == "canceled" && $originalStatus == "approved" ) {
//                        //TODO: reset user's VacReqUserCarryOver object?
//                        //reset user's VacReqUserCarryOver object: remove VacReqCarryOver for this canceled request year
//                        $vacreqUtil->deleteCanceledVacReqCarryOverRequest($entity);
//                    }

                    //if institution is null (one step approval) => sync status for both
                    if( !$entity->getInstitution() ) {
                        $entity->setTentativeStatus($status);
                    }

                }//carryover


                $entity->setStatus($status);
                $statusSet = true;
            }

            if( $statusSet ) {

                if( $status == "pending" ) {
                    $entity->setApprover(null);
                } else {
                    $entity->setApprover($user);
                }

                $entity->setExtraStatus(NULL);
                $em->persist($entity);
                $em->flush();

                if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
                    $vacreqUtil->syncVacReqCarryOverRequest($entity, $originalStatus); //vacreq_status_change, vacreq_status_email_change
                }

                //return $this->redirectToRoute('vacreq_home');

                //send respond confirmation email to a submitter
                if( $status == 'canceled' ) {
                    //an email should be sent to approver saying
                    // "FirstName LastName canceled/withdrew their business travel / vacation request described below:"
                    // and list all variable names and values in the email.
                    $approversNameStr = $vacreqUtil->sendCancelEmailToApprovers( $entity, $user, $status );
                } else {
                    $approversNameStr = null;
                    //send confirmation email by express link to change status (email or link in the list)
                    $vacreqUtil->sendSingleRespondEmailToSubmitter( $entity, $user, $status );
                }

                $removeCarryoverStr = "";
//                if( $entity->getRequestTypeAbbreviation() == "carryover" && $status == "canceled" && $originalStatus == "approved" ) {
//                    //TODO: reset user's VacReqUserCarryOver object? Take care of this case by syncVacReqCarryOverRequest
//                    //reset user's VacReqUserCarryOver object: remove VacReqCarryOver for this canceled request year
//                    $removeCarryoverStr = " ".$vacreqUtil->deleteCanceledVacReqCarryOverRequest($entity).".";
//                }
                //exit("test");

                //Flash
                $statusStr = $status;
                if( $status == 'pending' ) {
                    $statusStr = 'set to Pending';
                }

                //re-submit request
                if( $status == "pending" && $originalStatus == "canceled" ) {
                    //send a confirmation email to approver
                    $approversNameStr = $vacreqUtil->sendConfirmationEmailToApprovers( $entity );
                    $statusStr = 're-submitted';
                }

                $event = ucwords($requestName)." ID #" . $entity->getId() . " for " . $entity->getUser() . " has been " . $statusStr . " by " . $user;
                $event .= ": ".$entity->getDetailedStatus().".";
                if( $approversNameStr ) {
                    $event .= " Confirmation email(s) have been sent to ".$approversNameStr.".";
                }

                $event .= $removeCarryoverStr;

                $this->addFlash(
                    'notice',
                    $event
                );

                if( $entity->getRequestTypeAbbreviation() == "carryover" ) {
                    $eventType = 'Carry Over Request Updated';
                } else {
                    $eventType = 'Business/Vacation Request Updated';
                }

                //Event Log
                $userSecUtil = $this->container->get('user_security_utility');
                $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $event, $user, $entity, $request, $eventType);

            }

        }

        //redirect to myrequests for owner
        if(
            $entity->getUser()->getId() == $user->getId() ||
            $entity->getSubmitter()->getId() == $user->getId()
        ) {
            return $this->redirectToRoute("vacreq_myrequests",array('filter[requestType]'=>$entity->getRequestType()->getId()));
        }

        $url = $request->headers->get('referer');
        //exit('url='.$url);

        //when status is changed from email, then the url is a system home page
        if( $url && strpos((string)$url, 'incoming-requests') !== false ) {
            return $this->redirect($url);
        }

        //return $this->redirectToRoute('vacreq_show', array('id' => $entity->getId()));
        return $this->redirectToRoute('vacreq_incomingrequests',array('filter[requestType]'=>$entity->getRequestType()->getId()));
    }

    /**
     * submitter can submit a "cancellation-request" for an entire, already approved request
     */
    #[Route(path: '/status-cancellation-request/{id}', name: 'vacreq_status_cancellation-request', methods: ['GET'])]
    public function statusCancellationRequestAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $entity = $em->getRepository(VacReqRequest::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Request by id='.$id);
        }

        //check permissions
        if(
            false == $this->isGranted("update", $entity) &&
            (
                $entity->getUser()->getId() != $user->getId() &&
                $entity->getSubmitter()->getId() != $user->getId()
            )//author can request cancellation
        ) {
            //exit("No permission: update");
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        if( !$entity->isOverallStatus('approved') ) {
            //Flash
            $this->addFlash(
                'notice',
                'You can not submit a Cancellation Requested for not approved request.'
            );
            return $this->redirectToRoute("vacreq_myrequests",array('filter[requestType]'=>$entity->getRequestType()->getId()));
        }

        $entity->setExtraStatus("Cancellation Requested");
        $em->flush();

        $requestName = $entity->getRequestName();
        $userNameOptimal = $entity->getUser()->getUsernameOptimal();
        $eventSubject = $userNameOptimal." is requesting cancellation of a ".ucwords($requestName)." ID #" . $entity->getId();

        //send email to an approver
        //$break = "\r\n";
        $break = "<br>";

        //The approver can then change the status from "Cancellation Requested" to either "Cancellation Approved (Canceled)" or "Cancellation Denied (Approved)"
        //cancellation-request => cancellation-request-approved
        //cancellation-request => cancellation-request-rejected

        //set confirmation email to approver and email users
        $approveLink = $this->container->get('router')->generate(
            'vacreq_status_cancellation-request_email_change',
            array(
                'id' => $entity->getId(),
                'status' => 'cancellation-request-approved'
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $rejectLink = $this->container->get('router')->generate(
            'vacreq_status_cancellation-request_email_change',
            array(
                'id' => $entity->getId(),
                'status' => 'cancellation-request-rejected'
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = "Dear ###emailuser###," . $break.$break;

        //FirstName LastName is no longer planning to be away and is requesting cancellation of the following
        // Business Travel / Vacation request approved on XX/XX/XXXX:
        $message .= $userNameOptimal." is no longer planning to be away and is requesting cancellation of the following ";
        $message .= ucwords($requestName)." approved on ".$entity->getApprovedRejectDate()->format('m/d/Y H:i:s').":".$break;

        //[all form field titles and their values, 1 per line]
        $message .= $break.$entity->printRequest($this->container)."".$break;

        //To approve cancellation of this request, please follow this link
        // (the days in this request will no longer count towards FirstName LastName's vacation / business travel):
        $message .= "To approve cancellation of this request, please follow this link ";
        $message .= "(the days in this request will no longer count towards ".$userNameOptimal."'s vacation / business travel):".$break;
        $message .= $approveLink;

        //To reject cancellation of this request, please follow this link
        // (the days in this request will still count towards FirstName LastName's vacation / business travel):
        $message .= $break.$break."To reject cancellation of this request, please follow this link ";
        $message .= "(the days in this request will still count towards ".$userNameOptimal."'s vacation / business travel):".$break;
        $message .= $rejectLink;

        $vacreqUtil = $this->container->get('vacreq_util');
        $approversNameStr = $vacreqUtil->sendGeneralEmailToApproversAndEmailUsers($entity,$eventSubject,$message);

        $eventSubject = $eventSubject.". Email(s) have been sent to ".$approversNameStr;

        //Flash
        $this->addFlash(
            'notice',
            $eventSubject
        );

        $eventType = 'Business/Vacation Request Updated';

        //Event Log
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $eventSubject, $user, $entity, $request, $eventType);

        return $this->redirectToRoute("vacreq_myrequests",array('filter[requestType]'=>$entity->getRequestType()->getId()));
    }
    /**
     * approver can change a status of a "cancellation-request" for an entire, already approved request
     * The approver can then change the status from "Cancellation Requested" to either "Cancellation Approved (Canceled)" or "Cancellation Denied (Approved)"
     * cancellation-request => cancellation-request-approved => canceled
     * cancellation-request => cancellation-request-rejected => approved
     */
    #[Route(path: '/cancellation-request/status/{id}/{status}', name: 'vacreq_status_cancellation-request_change', methods: ['GET'])]
    #[Route(path: '/cancellation-request/estatus/{id}/{status}', name: 'vacreq_status_cancellation-request_email_change', methods: ['GET'])]
    public function statusCancellationRequestChangeAction(Request $request, $id, $status) {
        $em = $this->getDoctrine()->getManager();
        $routeName = $request->get('_route');
        $user = $this->getUser();
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $entity = $em->getRepository(VacReqRequest::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Request by id=' . $id);
        }

        $logger = $this->container->get('logger');
        $logger->notice("RequestController statusCancellationRequestChangeAction: ".$entity->getId()." (".$routeName.")".": status=".$status."; set by user=".$user);

        //check permissions
        if( false == $this->isGranted("changestatus", $entity) ) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //if not Cancellation Requested and vacreq_status_cancellation-request_email_change => redirect to incoming request page
        if( $entity->getExtraStatus() != "Cancellation Requested" && $routeName == 'vacreq_status_cancellation-request_email_change' ) {
            //Flash
            $this->addFlash(
                'notice',
                "This cancellation request for ".$entity->getRequestName()." ID #" . $entity->getId()." has already been completed by ".$entity->getApprover()
            );
            return $this->redirectToRoute('vacreq_incomingrequests',array('filter[requestType]'=>$entity->getRequestType()->getId()));
        }

        //cancellation-request-approved => canceled
        //cancellation-request-rejected => approved
        if( $status == "cancellation-request-approved" ) {
            $entity->setExtraStatus("Cancellation Approved (Canceled)");
            $status = "canceled";
            $entity->setStatus($status);
            $entity->setEntireStatus($status);
        }
        if( $status == "cancellation-request-rejected" ) {
            $entity->setExtraStatus("Cancellation Denied (Approved)");
            $status = "approved"; //for approved => the overall status is completed
            $entity->setStatus("completed");
            $entity->setEntireStatus($status);
        }

        $entity->setApprover($user);
        $em->flush();

        $requestName = $entity->getRequestName();
        $userNameOptimal = $entity->getUser()->getUsernameOptimal();
        $eventSubject = $entity->getExtraStatus()." of a ".ucwords($requestName)." ID #" . $entity->getId() . " (" . $userNameOptimal . ") by " . $user;

        //Flash
        $this->addFlash(
            'notice',
            $eventSubject
        );

        $eventType = 'Business/Vacation Request Updated';

        //Event Log
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'), $eventSubject, $user, $entity, $request, $eventType);

        //set confirmation email to submitter and email users
        $vacreqUtil = $this->container->get('vacreq_util');
        //$break = "\r\n";
        $break = "<br>";
        $message = $eventSubject . $break . $break . $entity->printRequest($this->container);
        $vacreqUtil->sendSingleRespondEmailToSubmitter( $entity, $user, null, $message );

        $url = $request->headers->get('referer');

        //when status is changed from email, then the url is a system home page
        if( $url && strpos((string)$url, 'incoming-requests') !== false ) {
            return $this->redirect($url);
        }

        return $this->redirectToRoute('vacreq_incomingrequests',array('filter[requestType]'=>$entity->getRequestType()->getId()));
    }

    #[Route(path: '/send-reminder-email/{id}', name: 'vacreq_send_reminder_email', methods: ['GET'])]
    public function sendReminderEmailAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        //$user = $this->getUser();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $entity = $em->getRepository(VacReqRequest::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Request by id=' . $id);
        }

        //check permissions
//        if( false == $this->isGranted("update", $entity) && false === $this->isGranted('ROLE_VACREQ_SUPERVISOR')) {
//            return $this->redirect($this->generateUrl('vacreq-nopermission'));
//        }
        if(
            $this->isGranted("read", $entity) ||
            $this->isGranted("update", $entity) ||
            $this->isGranted('ROLE_VACREQ_ADMIN') ||
            $this->isGranted('ROLE_VACREQ_SUPERVISOR')
        )
        {
            //OK send reminder email: read, supervisor
        } else {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //set confirmation email to approver and email users
        $vacreqUtil = $this->container->get('vacreq_util');
        $approversNameStr = $vacreqUtil->sendConfirmationEmailToApprovers( $entity );

        $eventSubject = 'Reminder email(s) has been sent to '.$approversNameStr;

        //Flash
        $this->addFlash(
            'notice',
            $eventSubject
        );

        return $this->redirectToRoute("vacreq_myrequests",array('filter[requestType]'=>$entity->getRequestType()->getId()));
    }



    public function createRequestForm( $entity, $cycle, $request, $approvalGroupType=NULL )
    {

        $em = $this->getDoctrine()->getManager();
        $userServiceUtil = $this->container->get('user_service_utility');
        $vacreqUtil = $this->container->get('vacreq_util');
        $routeName = $request->get('_route');

        $user = $this->getUser();

        $roleCarryOverApprover = false;

        $admin = false;
        if ($this->isGranted('ROLE_VACREQ_ADMIN')) {
            $admin = true;
        }

        $roleApprover = false;
        if ($this->isGranted("changestatus", $entity)) {
            $roleApprover = true;
        }

        $roleProxySubmitter = false;
        if( $this->isGranted("ROLE_VACREQ_PROXYSUBMITTER", $entity) ) {
            $roleProxySubmitter = true;
        }

        $requestType = $entity->getRequestType();

        //get submitter groups: VacReqRequest, create
        $tentativeInstitutions = null;
        $groupParams = array();
        if ($requestType->getAbbreviation() == "carryover") {

            //tentative institutions
            $tentativeGroupParams = array();
            if ($entity->getUser()) {
                $userExecutiveSubmitter = $entity->getUser()->hasRole("ROLE_VACREQ_SUBMITTER_EXECUTIVE");
            } else {
                $userExecutiveSubmitter = $user->hasRole("ROLE_VACREQ_SUBMITTER_EXECUTIVE");
            }
            if (!$userExecutiveSubmitter) {
                $tentativeGroupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'create');
                if ($this->isGranted('ROLE_VACREQ_ADMIN') == false) {
                    $tentativeGroupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
                }
                $tentativeInstitutions = $vacreqUtil->getGroupsByPermission($user, $tentativeGroupParams);
            }

            //carry-over institution
            $groupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');

            $roleCarryOverApprover = false;
            if ($this->isGranted("changestatus-carryover", $entity)) {
                $roleCarryOverApprover = true;
            }

        } else {
            $groupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'create');
            if ($this->isGranted('ROLE_VACREQ_ADMIN') == false) {
                $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
            }
        }

        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user, $groupParams);

        //include this request institution to the $organizationalInstitutions array
        $organizationalInstitutions = $vacreqUtil->addRequestInstitutionToOrgGroup($entity, $organizationalInstitutions);

        //for carry-over request only Tentative Approval group can exists and the Organizational Group might be empty
        //set $organizationalInstitutions to empty if entity has only tentative institution and does not have org institution
        if( $requestType->getAbbreviation() == "carryover") {
            if( $entity->getTentativeInstitution() && !$entity->getInstitution() ) {
                $organizationalInstitutions = array();
            }
        }

        //include this request institution to the $tentativeInstitutions array
        $tentativeInstitutions = $vacreqUtil->addRequestInstitutionToOrgGroup($entity, $tentativeInstitutions, "tentativeInstitution");

        //for carry-over request only Tentative Approval group can exists and the Organizational Group might be empty
        //For example, Brooklyn Methodist is independent institution with a single stage approval
        if( count($organizationalInstitutions) == 0 && ($tentativeInstitutions === null || count($tentativeInstitutions) == 0) ) {
            //If count($organizationalInstitutions) == 0 then try to run http://hosthame/order/directory/admin/sync-db/

            if( $this->isGranted('ROLE_VACREQ_ADMIN') ) {
                //admin user
                $groupPageUrl = $this->generateUrl(
                    "vacreq_approvers",
                    array(),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $warningMsg = "You don't have any group and/or assigned Submitter roles for the Business/Vacation Request site.".
                    ' <a href="'.$groupPageUrl.'" target="_blank">Please create a group and/or assign a Submitter role to your user account.</a> ';

                //Flash
                $this->addFlash(
                    'warning',
                    $warningMsg
                );
            } else {
                //regular user
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                $adminUsers = $em->getRepository(User::class)->findUserByRole("ROLE_VACREQ_ADMIN", "infos.lastName", true);
                $emails = array();
                foreach ($adminUsers as $adminUser) {
                    $singleEmail = $adminUser->getSingleEmail();
                    if ($singleEmail) {
                        $emails[] = $adminUser->getSingleEmail();
                    }
                }
                $emailStr = "";
                if (count($emails) > 0) {
                    $emailStr = " Administrator email(s): " . implode(", ", $emails);
                }

                $warningMsg = "You don't have any group and/or assigned Submitter roles for the Business/Vacation Request site." .
                    " Please contact the site administrator to create a group and/or get a Submitter role for your account." . $emailStr;

                //Flash
                $this->addFlash(
                    'warning',
                    $warningMsg
                );
            }

        }//if count($organizationalInstitutions) == 0

        //get holidays url
        $userSecUtil = $this->container->get('user_security_utility');
        $holidaysUrl = $userSecUtil->getSiteSettingParameter('holidaysUrl','vacreq');
        if( !$holidaysUrl ) {
            //throw new \InvalidArgumentException('holidaysUrl is not defined in Site Parameters.');
            //Flash
            $this->addFlash(
                'warning',
                'holidaysUrl is not defined in Site Parameters.'
            );
        }
        $holidaysUrl = '<a target="_blank" href="'.$holidaysUrl.'">holidays</a>';

        //echo "roleApprover=".$roleApprover."<br>";
        //echo "roleCarryOverApprover=".$roleCarryOverApprover."<br>";

        //$maxCarryOverVacationDays = $userSecUtil->getSiteSettingParameter('maxCarryOverVacationDays','vacreq');
        //$noteForCarryOverDays = $userSecUtil->getSiteSettingParameter('noteForCarryOverDays','vacreq');
        //$groupInstitution = $entity->getInstitution();
        //echo "groupInstitution=$groupInstitution <br>";

        $maxCarryOverVacationDays = NULL;
        $noteForCarryOverDays = NULL;
        if ($requestType->getAbbreviation() == "carryover") {
//            $approvalGroupType = NULL;
//            $groupInstitution = $entity->getTentativeInstitution();
//            if( !$groupInstitution ) {
//                $groupInstitution = $entity->getInstitution();
//            }
//            //echo "groupInstitution=$groupInstitution <br>";
//            if( $groupInstitution ) {
//                $approvalGroupType = $vacreqUtil->getVacReqApprovalGroupType($groupInstitution);
//            }

            $approvalGroupType = NULL;
            $vacreqSettings = $vacreqUtil->getSettingsByVacreq($entity);
            if( $vacreqSettings ) {
                $approvalGroupType = $vacreqSettings->getApprovalType();
            }

            //echo "approvalGroupType=$approvalGroupType <br>";
            $maxCarryOverVacationDays = $vacreqUtil->getValueApprovalGroupTypeByUser('maxCarryOverVacationDays', $entity->getUser(), $approvalGroupType);
            $noteForCarryOverDays = $vacreqUtil->getValueApprovalGroupTypeByUser('noteForCarryOverDays', $entity->getUser(), $approvalGroupType);
        }
        //echo "maxCarryOverVacationDays=$maxCarryOverVacationDays, noteForCarryOverDays=$noteForCarryOverDays <br>";

        $params = array(
            'container' => $this->container,
            'em' => $em,
            'entity' => $entity,
            'user' => $entity->getUser(),
            'cycle' => $cycle,
            'roleAdmin' => $admin,
            'roleApprover' => $roleApprover,
            'roleProxySubmitter' => $roleProxySubmitter,
            'roleCarryOverApprover' => $roleCarryOverApprover,
            'organizationalInstitutions' => $userServiceUtil->flipArrayLabelValue($organizationalInstitutions),
            'tentativeInstitutions' => $userServiceUtil->flipArrayLabelValue($tentativeInstitutions),
            'holidaysUrl' => $holidaysUrl,
            'maxCarryOverVacationDays' => $maxCarryOverVacationDays,
            'noteForCarryOverDays' => $noteForCarryOverDays,
            //'maxVacationDays' => $userSecUtil->getSiteSettingParameter('maxVacationDays','vacreq'),
            //'noteForVacationDays' => $userSecUtil->getSiteSettingParameter('noteForVacationDays','vacreq'),
        );

        $disabled = false;
        $method = 'GET';

        if( $cycle == 'show' ) {
            $disabled = true;
        }

        if( $cycle == 'new' ) {
            $method = 'POST';
        }

        if( $cycle == 'edit' ) {
            $method = 'POST';
        }

        if( $cycle == 'review' ) {
            $method = 'POST';
        }

        $params['review'] = false;
        if( $request ) {
            if( $routeName == 'vacreq_review' ) {
                $params['review'] = true;
            }
        }

        $params['requestType'] = $requestType;

        if( $requestType->getAbbreviation() == "carryover" ) {
            //set Source year (2015)
            //$entity->setSourceYear( date("Y")-1 );
            //set Destination year (2016)
            //$entity->setDestinationYear( date("Y") );
            //TODO: get years according to current date (border conditions)
            $nextYearRange = (date("Y"))."-".(date("Y")+1);
            $currentYearRange = (date("Y")-1)."-".(date("Y"));
            $previousYearRange = (date("Y")-2)."-".(date("Y")-1);
            //$previousPreviousYearRange = (date("Y")-2)."-".(date("Y")-1);

            //sourceYearRanges: current academic year and previous academic year
//            $sourceYearRanges = array(
//                (date("Y")-1) => $currentYearRange,     //THIS YEAR (default)
//                (date("Y")-2) => $previousYearRange     //PREVIOUS YEAR
//            );
            $sourceYearRanges = array(
                $currentYearRange => (date("Y")-1),     //THIS YEAR (default)
                $previousYearRange => (date("Y")-2)     //PREVIOUS YEAR
            );
            $params['sourceYearRanges'] = $sourceYearRanges;

            //destinationYearRanges: Current Academic Year and Next Academic year
//            $destinationYearRanges = array(
//                (date("Y")) => $nextYearRange,        //NEXT YEAR (default)
//                (date("Y")-1) => $currentYearRange    //THIS YEAR
//            );
            $destinationYearRanges = array(
                $nextYearRange => (date("Y")),        //NEXT YEAR (default)
                $currentYearRange => (date("Y")-1)    //THIS YEAR
            );
            $params['destinationYearRanges'] = $destinationYearRanges;
        }


        $form = $this->createForm(
            VacReqRequestType::class,
            $entity,
            array(
                'form_custom_value' => $params,
                'disabled' => $disabled,
                'method' => $method,
                //'action' => $action
            )
        );

        return $form;
    }


    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->isGranted('ROLE_VACREQ_ADMIN') ) {
            return null;
        }
        $userSecUtil = $this->container->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->getParameter('vacreq.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
    }





    ////////////////////////////// service and testing methods //////////////////////////////////
    #[Route(path: '/import-old-data/', name: 'vacreq_import_old_data', methods: ['GET'])]
    public function importOldDataAction(Request $request) {

        if( !$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $vacReqImportData = $this->container->get('vacreq_import_data');
        $res = $vacReqImportData->importOldData();

        //exit('Imported result: '.$res);

        //Flash
        $this->addFlash(
            'notice',
            'Imported result: '.$res
        );

        return $this->redirectToRoute('vacreq_incomingrequests');
    }


    #[Route(path: '/delete-imported-old-data/', name: 'vacreq_delete_imported_old_data', methods: ['GET'])]
    public function deleteImportedOldDataAction(Request $request) {

        if( !$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $em->getRepository(VacReqRequest::class);

        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');
        //$dql->where("request.exportId != 0");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $requests = $query->getResult();

        $batchSize = 20;
        $count = 0;
        foreach( $requests as $request ) {

//            echo "reqId=".$request->getId()."<br>";
//            if( $request->hasBusinessRequest() ) {
//                echo "businessId=" . $request->getRequestBusiness()->getID() . "<br>";
//            }
//            if( $request->hasVacationRequest() ) {
//                echo "vacationId=" . $request->getRequestVacation()->getID() . "<br>";
//            }

            $em->remove($request);

            $em->flush();
            //exit('removed');

            if( ($count % $batchSize) === 0 ) {
                $em->flush();
                //$em->clear(); // Detaches all objects from Doctrine!
            }

            $count++;
        }

        $em->flush(); //Persist objects that did not make up an entire batch
        $em->clear();

        //exit('Remove result: '.$count);

        //Flash
        $this->addFlash(
            'notice',
            'Removed requests: '.count($requests)
        );

        return $this->redirectToRoute('vacreq_incomingrequests');
    }


    #[Route(path: '/setdates-imported-old-data/', name: 'vacreq_setdates_imported_old_data', methods: ['GET'])]
    public function setdatesImportedOldDataAction(Request $request) {

        if( !$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $em->getRepository(VacReqRequest::class);

        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin("request.requestType", "requestType");

        $dql->where("requestType.abbreviation = 'business-vacation'");
        $dql->andWhere("request.firstDayAway IS NULL");
        $dql->andWhere("request.firstDayBackInOffice IS NULL");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $requests = $query->getResult();

        $count = 0;
        foreach( $requests as $request ) {
            //echo "request=".$request->getId()."<br>";

//            $res = $request->getFinalStartEndDates();
//            $startDate = $res['startDate'];
//            $endDate = $res['endDate'];
//            echo "request=".$request->getId()."; startDate=".$startDate->format('Y-m-d')."; endDate=".$endDate->format('Y-m-d')."<br>";
//            $request->setFirstDayAway($startDate);
//            $request->setFirstDayBackInOffice($endDate);

            //set first day away
            $firstDateAway = $request->getFirstDateAway(null);
            $request->setFirstDayAway($firstDateAway);

//            $startDate = "";
//            if( $request->getFirstDayAway() ) {
//                $startDate = $request->getFirstDayAway()->format('Y-m-d');
//            }
//            $endDate = "";
//            if( $request->getFirstDayBackInOffice() ) {
//                $endDate = $request->getFirstDayBackInOffice()->format('Y-m-d');
//            }
//            echo "request=".$request->getId()."; startDate=".$startDate."; endDate=".$endDate."; requestType=".$request->getRequestType()."<br>";

            $em->flush();

            $count++;
        }

        //exit('Updated result: '.$count);

        //Flash
        $this->addFlash(
            'notice',
            'Updated dates requests: '.count($requests)
        );

        return $this->redirectToRoute('vacreq_incomingrequests');
    }


    /**
     * TODO: clean overlap approved vacation requests
     */
    #[Route(path: '/overlaps/', name: 'vacreq_overlaps', methods: ['GET'])]
    public function getOverlapRequestsAction(Request $request)
    {

        if (!$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $logger = $this->container->get('logger');

        //find unique users
        $userUniqueRequests = $this->findUniqueUserRequests();
        //exit('1');

        $count = 1;
        foreach( $userUniqueRequests as $userUniqueRequest ) {
            $userId = $userUniqueRequest['userId'];
//            $logger->error($count." ########## user: ".$userUniqueRequest->getUser());
//            echo $count." ########## user: ".$userUniqueRequest->getUser()."<br>";
            $count = $this->analyzeRequests($userId,$count);
            //$logger->error('#########################');
            //echo "#########################<br><br>";
            //$count++;
        }


        //exit('2');
        return $this->redirectToRoute('vacreq_incomingrequests');
    }
    public function findUniqueUserRequests() {
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $em->getRepository(VacReqRequest::class);

        $dql =  $repository->createQueryBuilder("request");
        //$dql->select('request');
        $dql->select('DISTINCT user.id as userId');

        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("request.requestType", "requestType");
        $dql->leftJoin("request.requestVacation", "requestVacation");

        $dql->where("requestType.abbreviation = 'business-vacation'");
        $dql->andWhere("requestVacation.status='approved'");

        //$dql->groupBy('user.id');
        //$dql->addGroupBy('request');

        //$dql->orderBy('request.id');

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $requests = $query->getResult();
        //echo "requests with users=".count($requests)."<br>";
        return $requests;
    }
    public function analyzeRequests($userId,$count=1) {
        $vacreqUtil = $this->container->get('vacreq_util');
        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $user = $em->getRepository(User::class)->find($userId);
        $overlapRequests = $vacreqUtil->getOverlappedUserRequests($user,true,true);
        //$overlapRequests = $vacreqUtil->getNotOverlapNumberOfWorkingDays($user,'requestVacation');

        if( count($overlapRequests) > 0 ) {
            $logger->error($count." ^########## user: " . $user);
            //echo $count." ^########## user: " . $user . "<br><br><br>";
            $count++;
        }
        return $count;
    }


    #[Route(path: '/update-carryover/', name: 'vacreq_update_carryover', methods: ['GET'])]
    public function getUpdateCarryOverRequestsAction(Request $request)
    {

        if (!$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            //exit('no permission');
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $logger = $this->container->get('logger');
        $vacreqUtil = $this->container->get('vacreq_util');
        $em = $this->getDoctrine()->getManager();

        //find pending carryover request
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $em->getRepository(VacReqRequest::class);
        $dql =  $repository->createQueryBuilder("request");
        $dql->leftJoin("request.institution", "institution");
        $dql->leftJoin("request.requestType", "requestType");

        $dql->where("request.status = 'pending' AND requestType.abbreviation = 'carryover'");

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $pendingRequests = $query->getResult();
        //echo "pending request count=".count($pendingRequests)."<br><br>";
        //exit('exit');

        $count = 1;
        foreach( $pendingRequests as $pendingRequest ) {

            //echo "<br>req ID=".$pendingRequest->getId()."<br>";

            $submitter = $pendingRequest->getUser();

            $userExecutiveSubmitter = $submitter->hasRole("ROLE_VACREQ_SUBMITTER_EXECUTIVE");

            $sendEmail = false;

            if( !$userExecutiveSubmitter ) {
                //1) set tentativeInstitution
                $tentativeInstitution = null;

                $tentativeGroupParams = array('asObject'=>true);
                $tentativeGroupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'create');
                $tentativeGroupParams['asUser'] = true;
                //$tentativeGroupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'create');
                //if( $this->isGranted('ROLE_VACREQ_ADMIN') == false ) {
                    //$tentativeGroupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
                //}
                $tentativeInstitutions = $vacreqUtil->getGroupsByPermission($submitter, $tentativeGroupParams);
                //echo "tentativeInstitutions count=".count($tentativeInstitutions)."<br>";
                if( count($tentativeInstitutions) > 1 ) {
                    $tentativeInstitutionArr = array();
                    foreach( $tentativeInstitutions as $tentativeInstitution ) {
                        $tentativeInstitutionArr[] = $tentativeInstitution."";
                    }
                    $tentativeInstitutionStr = implode(", ",$tentativeInstitutionArr);
                    $msg = "Multiple tentative institutions: ";
                    $msg .= "ID #".$pendingRequest->getId().":  $tentativeInstitutionStr for ".$submitter."<br>";
                    $logger->notice($msg);
                    echo $msg;
                    continue;
                }
                if( count($tentativeInstitutions) == 1 ) {
                    $tentativeInstitution = $tentativeInstitutions[0];
                }

                if( $tentativeInstitution && !$pendingRequest->getTentativeInstitution() ) {

                    $pendingRequest->setTentativeInstitution($tentativeInstitution);

                    //2) set tentative status
                    if( !$pendingRequest->getTentativeStatus() ) {
                        $pendingRequest->setTentativeStatus('pending');
                    }

                    $em->persist($pendingRequest);
                    $em->flush();

                    $msg = "ID #".$pendingRequest->getId().": set tentative inst $tentativeInstitution and tentative status=pending for ".$submitter."<br>";
                    $logger->notice($msg);
                    echo $msg;
                    $sendEmail = true;
                }

            } else {
                $msg = "EXECUTIVE: ";
                $msg .= "ID #".$pendingRequest->getId().$submitter."<br>";
                echo $msg;
                $logger->notice($msg);
                $sendEmail = true;
            }

            //resend email to approvers
            if( $sendEmail ) {
                $approversNameStr = "";
                $sendCopy = false;
                $approversNameStr = $vacreqUtil->sendConfirmationEmailToApprovers($pendingRequest,$sendCopy);

                $logger->notice("Sent confirmation email to ".$approversNameStr);

                //Flash
                $this->addFlash(
                    'notice',
                    $msg. " Sent emails=".$approversNameStr
                );
            }

        }


        //exit('finished');
        return $this->redirectToRoute('vacreq_incomingrequests');
    }
    public function getInst() {

    }

}
