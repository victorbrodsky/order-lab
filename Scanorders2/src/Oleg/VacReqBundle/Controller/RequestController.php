<?php

namespace Oleg\VacReqBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Oleg\VacReqBundle\Entity\VacReqRequest;
use Oleg\VacReqBundle\Entity\VacReqRequestBusiness;
use Oleg\VacReqBundle\Entity\VacReqRequestVacation;
use Oleg\VacReqBundle\Form\VacReqRequestType;
use Oleg\VacReqBundle\Util\VacReqImportData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

//vacreq site

class RequestController extends Controller
{


    /**
     * Creates a new VacReqRequest entity.
     *
     * @Route("/", name="vacreq_home")
     * @Route("/new", name="vacreq_new")
     * @Route("/carry-over-request/new", name="vacreq_carryoverrequest")
     *
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Request:edit.html.twig")
     */
    public function newAction(Request $request)
    {

        if( $this->get('security.context')->isGranted('ROLE_VACREQ_OBSERVER') &&
            !$this->get('security.context')->isGranted('ROLE_VACREQ_SUBMITTER') &&
            !$this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
            !$this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR')
        ) {
            return $this->redirect( $this->generateUrl('vacreq_awaycalendar') );
        }

        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->get('vacreq_util');

        $user = $this->get('security.context')->getToken()->getUser();

        //testing
//        $currentYear = new \DateTime();
//        $currentYear = $currentYear->format('Y');
//        $previousYear = $currentYear - 1;
//        $yearRange = $previousYear."-".$currentYear;
//        $approvedDays = $vacreqUtil->getApprovedTotalDaysAcademicYear($user,"vacation",$yearRange);
//        //print_r($approvedDays);
//        echo "<br>##### approvedDays=".$approvedDays['numberOfDays']."<br>";
//        if( $approvedDays['accurate'] ) {
//            echo "accurate<br>";
//        } else {
//            echo "inaccurate!<br>";
//        }
//        exit('end');
        //testing

        $entity = new VacReqRequest($user);

        if( false == $this->get('security.context')->isGranted("create", $entity) ) {
            //since this is a home page redirect users according to the roles
            //approvers - redirect to incoming requests page
            if( $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') ) {
                //Flash
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    "You don't have a submitter role in order to submit a request. You have been redirected to the 'Incoming Requests' page."
                );
                return $this->redirectToRoute('vacreq_incomingrequests');
            }

            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //set request type
        $routeName = $request->get('_route');
        if( $routeName == "vacreq_carryoverrequest" ) {
            //carryover request
            $requestType = $em->getRepository('OlegVacReqBundle:VacReqRequestTypeList')->findOneByAbbreviation("carryover");
            $title = "Request carry over of vacation days";
            $eventType = "Carry Over Request Created";

            //set Source year (2015)
            //$entity->setSourceYear( date("Y")-1 );
            //set Destination year (2016)
            //$entity->setDestinationYear( date("Y") );

            $newCarryOverRequest = null;

            //check if 'days' parameter is set in http request
            $carryOverRequestDays = $request->query->get('days');
            if( $carryOverRequestDays ) {
                $entity->setCarryOverDays($carryOverRequestDays);
            }

            //set tentativeStatus only for non executive submitter
            $userExecutiveSubmitter = $user->hasRole("ROLE_VACREQ_SUBMITTER_EXECUTIVE");
            if( !$userExecutiveSubmitter && $entity->getTentativeStatus() == NULL ) {
                $entity->setTentativeStatus('pending');
            }

        } else {
            //business/vacation request
            $requestType = $em->getRepository('OlegVacReqBundle:VacReqRequestTypeList')->findOneByAbbreviation("business-vacation");
            $title = "Vacation/Business Travel Request";
            $eventType = "Business/Vacation Request Created";

            $newCarryOverRequest = $vacreqUtil->getNewCarryOverRequestString($user);
        }
        $entity->setRequestType($requestType);

        //set phone
        $phone = $vacreqUtil->getSubmitterPhone($user);
        $entity->setPhone($phone);

        //set emergency info
        $vacreqUtil->setEmergencyInfo($user,$entity);

        $cycle = 'new';

        $form = $this->createRequestForm($entity,$cycle,$request);

        $form->handleRequest($request);

        //check for overlapped date range
        if( $routeName != "vacreq_carryoverrequest" ) {
            $overlappedRequests = $vacreqUtil->checkRequestForOverlapDates($user, $entity); //check for newAction
            if (count($overlappedRequests) > 0) {
                //exit('error: count overlappedRequests='.count($overlappedRequests));
                //$errorMsg = 'You provided overlapped vacation date range with a previous approved vacation request(s) with ID #' . implode(',', $overlappedRequestIds);
                $errorMsg = $vacreqUtil->getOverlappedMessage( $entity, $overlappedRequests, true );
                //$form->addError(new FormError($errorMsg));
                $form['requestVacation']['startDate']->addError(new FormError($errorMsg));
                //$form['requestVacation']['endDate']->addError(new FormError($errorMsg));
            } else {
                //exit('no overlaps found');
            }
        }
        //exit('testing');

        if( $form->isSubmitted() && $form->isValid() ) {

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

            //testing
            //echo "sourceYear=".$entity->getSourceYear()."<br>";
            //exit('1');

            $em->persist($entity);
            $em->flush();

            $requestName = $entity->getRequestName();
            $emailUtil = $this->get('user_mailer_utility');
            $break = "\r\n";

            //set confirmation email to submitter and approver and email users
            $css = null;
            $personAway = $entity->getUser();
            $personAwayEmail = $personAway->getSingleEmail();
            if( !$personAwayEmail ) {
                throw $this->createNotFoundException("Person email is null: personAwayEmail=".$personAwayEmail);
            }
            if( $personAway->getId() != $user->getId() ) {
                //cc to submitter
                $css = $user->getSingleEmail();
            }

            $subject = $requestName." ID #".$entity->getId()." Confirmation";
            $message = "Dear ".$entity->getUser()->getUsernameOptimal().",".$break.$break;

            if( $entity->getRequestType()->getAbbreviation() == "carryover" ) {
                //You have successfully submitted request #1781 for X vacation days to be carried over from 20XX-20YY to 20YY-20ZZ.
                $message .= "You have successfully submitted the request #".$entity->getId()." for ".$entity->getCarryOverDays();
                $message .= " vacation days to be carried over from ".$entity->getSourceYearRange()." to ".$entity->getDestinationYearRange().".";
            } else {
                $message .= "You have successfully submitted the ".$requestName." #".$entity->getId().".";
            }
            $message .= $break.$break.$entity."".$break;

            $message .= $break."You will be notified once your request is reviewed and its status changes.";
            $message .= $break.$break."**** PLEASE DO NOT REPLY TO THIS EMAIL ****";
            $emailUtil->sendEmail( $personAwayEmail, $subject, $message, $css, null );

            //set confirmation email to approver and email users
            $approversNameStr = $vacreqUtil->sendConfirmationEmailToApprovers( $entity );

            //Event Log
            $event = $requestName . " for ".$entity->getUser()." has been submitted. Confirmation email has been sent to ".$approversNameStr;
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

            //exit('exit event='.$event);

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );

            //check if requested carry over days are already approved or denied
            if( $entity->getRequestType()->getAbbreviation() == "carryover" ) {
                //check if requested carry over days are already approved or denied
                $resCarryOverRequest = $vacreqUtil->processVacReqCarryOverRequest($entity,true);
                $carryOverWarningMessageLog = $resCarryOverRequest['carryOverWarningMessageLog'];
                $eventType = "Existing Days Carry Over Request Created";
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$carryOverWarningMessageLog,$user,$entity,$request,$eventType);
            }

            return $this->redirectToRoute('vacreq_show', array('id' => $entity->getId()));
        }

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        //calculate approved vacation days in total.
        $bruteForce = false;
        $totalApprovedDaysString = $vacreqUtil->getApprovedDaysString($user,$bruteForce);
        //echo "totalApprovedDaysString=".$totalApprovedDaysString."<br>";

        //{{ yearRange }} Accrued Vacation Days as of today: {{ accruedDays }}
        //"You have accrued X vacation days this academic year (and will accrue X*12 by [date of academic year start from site settings, show as July 1st, 20XX]."
        //"You have accrued 10 vacation days this academic year (and will accrue 24 by July 1st, 2016."
        //accrued days up to this month calculated by vacationAccruedDaysPerMonth
        $accruedDays = $vacreqUtil->getAccruedDaysUpToThisMonth();
        $totalAccruedDays = $vacreqUtil->getTotalAccruedDays();
        $currentStartYear = date("Y")+1; //2016
        $startAcademicYearStr = $vacreqUtil->getEdgeAcademicYearDate( $currentStartYear, "Start" );
        $startAcademicYearDate = new \DateTime($startAcademicYearStr);
        $startAcademicYearDateStr = $startAcademicYearDate->format("F jS, Y");
        $accruedDaysString =    "You have accrued ".$accruedDays." vacation days this academic year".
                                " (and will accrue ".$totalAccruedDays." by ".$startAcademicYearDateStr.").";

        //If for the current academic year the value of carried over vacation days is not empty and not zero for the logged in user,
        // append a third sentence stating "You have Y additional vacation days carried over from [Current Academic Year -1, show as 2014-2015]."
        $carriedOverDays = $vacreqUtil->getUserCarryOverDays($user, date("Y")-1); //2015
        //echo "carriedOverDays=".$carriedOverDays."<br>";
        $carriedOverDaysString = null;
        if( $carriedOverDays ) {
            $lastYearRange = (date("Y")-2)."-".(date("Y")-1);
            $carriedOverDaysString = "You have ".$carriedOverDays." additional vacation days carried over from ".$lastYearRange.".";
        }

        $carryoverPendingRequests = $vacreqUtil->getPendingCarryOverRequests($user);
        $requestTypeCarryOver = $em->getRepository('OlegVacReqBundle:VacReqRequestTypeList')->findOneByAbbreviation("carryover");
        if( $requestTypeCarryOver ) {
            $requestTypeCarryOverId = $requestTypeCarryOver->getId();
        } else {
            $requestTypeCarryOverId = null;
        }

        //totalAllocatedDays - vacationDays + carryOverDays
        $remainingDaysRes = $vacreqUtil->totalVacationRemainingDays($user);
        $remainingDaysString = "You have ".$remainingDaysRes['numberOfDays']." remaining vacation days during the current academic year";
        if( !$remainingDaysRes['accurate'] ) {
            $remainingDaysString .= " (".$vacreqUtil->getInaccuracyMessage().")";
        }
        $remainingDaysString .= ".";

        //check for overlapped requests
        $overlappedMessage = null;
        $overlapRequests = $vacreqUtil->getOverlappedUserRequests($user);
        if( count($overlapRequests) > 0 ) {
            $overlappedRequestHrefs = array();
            foreach( $overlapRequests as $overlapRequest ) {
                $overlapRequestLink = $this->container->get('router')->generate(
                    'vacreq_show',
                    array(
                        'id' => $overlapRequest->getId(),
                    )
                    //UrlGeneratorInterface::ABSOLUTE_URL
                );
                $thisDateRange = $overlapRequest->getFinalStartEndDates('requestVacation');
                //$startDateStr = $thisDateRange['startDate']->format('Y/m/d');
                //$endDateStr = $thisDateRange['endDate']->format('Y/m/d');
                $thisDateRange = "(".$thisDateRange['startDate']->format('Y/m/d')."-".$thisDateRange['endDate']->format('Y/m/d').")";
                $overlapRequestHref = '<a href="'.$overlapRequestLink.'">ID #'.$overlapRequest->getId().' '.$thisDateRange.'</a>';
                $overlappedRequestHrefs[] = $overlapRequestHref;
            }
            $overlappedMessage = "You have ".count($overlapRequests)." overlapping approved vacation request(s) for the current academic year: <br>".implode("<br>",$overlappedRequestHrefs);
            $overlappedMessage .= "<br>This will affect the accuracy of the calculations of the total approved and carry over days.";
            $overlappedMessage .= "<br>You can fix these overlapped vacation requests by canceling them (click a 'Request Cancellation' action link in 'My Requests' page).";
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'accessreqs' => count($accessreqs),
            'carryoverPendingRequests' => count($carryoverPendingRequests),
            'requestTypeCarryOverId' => $requestTypeCarryOverId,
            'totalApprovedDaysString' => $totalApprovedDaysString,
            'accruedDaysString' => $accruedDaysString,
            'carriedOverDaysString' => $carriedOverDaysString,
            'remainingDaysString' => $remainingDaysString,
            'title' => $title,
            'newCarryOverRequest' => $newCarryOverRequest,
            'overlappedMessage' => $overlappedMessage
        );
    }


    /**
     * Show: Finds and displays a VacReqRequest entity.
     *
     * @Route("/show/{id}", name="vacreq_show")
     *
     * @Method("GET")
     * @Template("OlegVacReqBundle:Request:edit.html.twig")
     */
    public function showAction(Request $request, $id)
    {
        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_USER') ) {
            //exit('show: no permission');
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegVacReqBundle:VacReqRequest')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Vacation Request by id='.$id);
        }

        if( false == $this->get('security.context')->isGranted("read", $entity) ) {
            //exit('show: no permission');
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }
        //exit('show: ok permission');

        $cycle = 'show';

        //get request type
        if( $entity->getRequestType()->getAbbreviation() == "carryover" ) {
            $title = "Request carry over of vacation days";
        } else {
            $title = "Vacation/Business Travel Request";
        }

        $form = $this->createRequestForm($entity,$cycle,$request);

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
     * @Route("/edit/{id}", name="vacreq_edit")
     * @Route("/review/{id}", name="vacreq_review")
     *
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Request:edit.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        //$deleteForm = $this->createDeleteForm($vacReqRequest);
        //$editForm = $this->createForm('Oleg\VacReqBundle\Form\VacReqRequestType', $vacReqRequest);

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->get('vacreq_util');
        $user = $this->get('security.context')->getToken()->getUser();

        $entity = $em->getRepository('OlegVacReqBundle:VacReqRequest')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Vacation Request by id='.$id);
        }

//        //can not edit if request is already processed by an approver: status == completed
//        if( $entity->getStatus() == 'completed' ) {
//            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
//        }

        //can't edit or review if request is not pending
        if( $entity->getStatus() != "pending" && $entity->getOverallStatus() != "pending" ) {
            //Flash
            $this->get('session')->getFlashBag()->add(
                'warning',
                'This request can not be modified because it is not pending anymore.'
            );
            return $this->redirectToRoute('vacreq_show', array('id' => $entity->getId()));
        }


        //check permission
        $routName = $request->get('_route');
        if( $routName == 'vacreq_review' ) {
            if( false == $this->get('security.context')->isGranted("changestatus", $entity) ) {
                return $this->redirect( $this->generateUrl('vacreq-nopermission') );
            }
        } else {
            if( false == $this->get('security.context')->isGranted("update", $entity) ) {
                return $this->redirect( $this->generateUrl('vacreq-nopermission') );
            }
        }

        $cycle = 'edit';
        if( $routName == 'vacreq_review' ) {
            $cycle = 'review';
        }

        //check if requested carry over days are already approved or denied
        if( $entity->getRequestType()->getAbbreviation() == "carryover" ) {
            //check if requested carry over days are already approved or denied
            $resCarryOverRequest = $vacreqUtil->processVacReqCarryOverRequest($entity,true);
            $carryOverWarningMessage = $resCarryOverRequest['carryOverWarningMessage'];
            $carryOverWarningMessageLog = $resCarryOverRequest['carryOverWarningMessageLog'];
        } else {
            $carryOverWarningMessage = null;
            $carryOverWarningMessageLog = null;
        }

        $form = $this->createRequestForm($entity,$cycle,$request);

        $form->handleRequest($request);

        //check for overlapped date range
        //$overallStatus = $entity->getStatus();
        //$routeName = $request->get('_route');
        if( $entity->getRequestType()->getAbbreviation() == "business-vacation" ) {
//            if (
//                $routeName == "vacreq_edit"
//                ||
//                $routeName == "vacreq_review" && $overallStatus == "completed"
//            ) {
            $overlappedRequests = $vacreqUtil->checkRequestForOverlapDates($user, $entity);    //check for editAction
                if (count($overlappedRequests) > 0) {
                    //$errorMsg = 'This request has overlapped vacation date range with a previous approved vacation request(s) with ID #' . implode(',', $overlappedRequestIds);
                    $errorMsg = $vacreqUtil->getOverlappedMessage( $entity, $overlappedRequests, true );
                    $form['requestVacation']['startDate']->addError(new FormError($errorMsg));
                    //$form['requestVacation']['endDate']->addError(new FormError($errorMsg));
                } else {
                    //exit('no overlaps found');
                }
//            }
        }

        if ($form->isSubmitted() && $form->isValid()) {

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

                //set final (global) status according to sub-requests status:
                //only two possible actions: reject or approved
                $entity->setFinalStatus(); //vacreq_review

                $overallStatus = $entity->getStatus();

                if( $overallStatus == "pending" ) {
                    $entity->setApprover(null);
                } else {
                    $entity->setApprover($user);
                }

                $entity->setExtraStatus(NULL);
                $em->persist($entity);
                $em->flush();

                $eventType = 'Business/Vacation Request '.ucwords($overallStatus);
                $action = $overallStatus;

                //send respond email
                $vacreqUtil->sendSingleRespondEmailToSubmitter( $entity, $user, $overallStatus );

            } else { //update

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

                /////////////// Add event log on edit (edit or add collection) ///////////////
                /////////////// Must run before flash DB. When DB is flashed getEntityChangeSet() will not work ///////////////
                $changedInfoArr = $vacreqUtil->setEventLogChanges($entity);

                //re-set final (global) first day away
                $entity->setFinalFirstDayAway(); //update

                //echo "0 business req=".$entity->getRequestBusiness()."<br>";
                //exit('1');

                $em->persist($entity);
                $em->flush();
                //echo "1 business req=".$entity->getRequestBusiness()."<br>";
                //exit('1');

                $action = "updated";

                if( $entity->getRequestType()->getAbbreviation() == "carryover" ) {
                    $eventType = 'Carry Over Request Updated';
                } else {
                    $eventType = 'Business/Vacation Request Updated';
                }

            } //if else: review or update

            if( $action == 'pending' ) {
                $action = 'set to Pending';
            }

            //Event Log
            $break = "\r\n";
            $event = "Request for ".$entity->getUser()." has been ".$action." by ".$user.$break;
            $event .= $entity->getDetailedStatus().$break.$break;
            $userSecUtil = $this->container->get('user_security_utility');

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );

            //set event log for objects
            if( count($changedInfoArr) > 0 ) {
                //$user = $this->get('security.context')->getToken()->getUser();
                $event .= "Updated Data:".$break;
                $event .= implode("<br>", $changedInfoArr);
            }

            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

            if( $carryOverWarningMessageLog ) {
                $eventType = 'Existing Days Carry Over Request Updated';
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$carryOverWarningMessageLog,$user,$entity,$request,$eventType);
            }

            if( $routName == 'vacreq_review' ) {
                return $this->redirectToRoute('vacreq_incomingrequests');
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
        if( $entity->getRequestType()->getAbbreviation() == "carryover" ) {
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
            'carryOverWarningMessage' => $carryOverWarningMessage
            //'delete_form' => $deleteForm->createView(),
        );
    }



    /**
     * approved, rejected, pending, canceled
     * @Route("/status/{id}/{requestName}/{status}", name="vacreq_status_change")
     * @Route("/estatus/{id}/{requestName}/{status}", name="vacreq_status_email_change")
     * @Method({"GET"})
     * @Template("OlegVacReqBundle:Request:edit.html.twig")
     */
    public function statusAction(Request $request, $id, $requestName, $status) {

        //if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') ) {
        //    return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        //}

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $routeName = $request->get('_route');
        $user = $this->get('security.context')->getToken()->getUser();

        $entity = $em->getRepository('OlegVacReqBundle:VacReqRequest')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Request by id='.$id);
        }

        //check permissions
//        if( $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') || $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
//            if( false == $this->get('security.context')->isGranted("changestatus", $entity) ) {
//                return $this->redirect($this->generateUrl('vacreq-nopermission'));
//            }
//        } elseif( $this->get('security.context')->isGranted("update", $entity) ) {
//            if( $status != 'canceled' && $status != 'pending' && $status != 'cancellation-request' ) {
//                return $this->redirect($this->generateUrl('vacreq-nopermission'));
//            }
//        } else {
//            return $this->redirect($this->generateUrl('vacreq-nopermission'));
//        }


        /////////////// log status ////////////////////////
        $logger->notice($entity->getId()." (".$routeName.")".": status=".$status."; set by user=".$user);
        /////////////// EOF log status ////////////////////////

        if( $this->get('security.context')->isGranted("changestatus", $entity) ) {
            //Approvers can change status to anything
        } elseif( $this->get('security.context')->isGranted("update", $entity) ) {
            //Owner can only set status to: canceled, pending
            if( $status != "canceled" && $status != "pending" ) {
                //Flash
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    "You can not change status of this ".$entity->getRequestName()." with ID #".$entity->getId()." to ".$status
                );
                $logger->error($user." has no permission to change status to ".$status." for request ID #".$entity->getId().". Reason: request is not pending or canceled");
                return $this->redirect($this->generateUrl('vacreq-nopermission'));
            }
        } else {
            $logger->error($user." has no permission to change status to ".$status." for request ID #".$entity->getId().". Reason: user does not have permission to changestatus or update for this request");
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //if not pending and vacreq_status_email_change => redirect to incoming request page
        if( $entity->getStatus() != "pending" && $routeName == 'vacreq_status_email_change' ) {
            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                "This ".$entity->getRequestName()." ID #" . $entity->getId()." has already been completed by ".$entity->getApprover()
            );
            return $this->redirectToRoute('vacreq_incomingrequests');
        }


        //check for overlapped date range if a new status is approved
        if( $status == "approved" ) {
            $vacreqUtil = $this->get('vacreq_util');
            $overlappedRequests = $vacreqUtil->checkRequestForOverlapDates($user, $entity); //check for statusAction
            if (count($overlappedRequests) > 0) {

                //If the dates overlap with the dates of another APPROVED request EXACTLY =>
                //This request has been canceled as a duplicate." and set the status to "Canceled" instead of "Approved".
                if( $vacreqUtil->hasOverlappedExactly( $entity, $overlappedRequests ) ) {

                    //set status to cancel
                    $status = 'canceled';

                    $errorMsg = $vacreqUtil->getOverlappedMessage( $entity, $overlappedRequests, null, true );
                    $errorMsg .= "<br>This request has been canceled as a duplicate.<br>";

                    $this->get('session')->getFlashBag()->add(
                        'warning',
                        $errorMsg
                    );
                } else {
                    $errorMsg = $vacreqUtil->getOverlappedMessage( $entity, $overlappedRequests );
                    $this->get('session')->getFlashBag()->add(
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

                if( $entity->getRequestType()->getAbbreviation() == "business-vacation" ) {
                    if( $status != 'canceled' && $status != 'pending' ) {
                        $status = 'completed';
                    }
                }
                if( $entity->getRequestType()->getAbbreviation() == "carryover" ) {
                    //exit("status=".$status);

                    //#489 (41)
                    if( $status == "approved" ) {
                        //process carry over request days if request is approved
                        $vacreqUtil = $this->get('vacreq_util');
                        $res = $vacreqUtil->processVacReqCarryOverRequest($entity);
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
                }

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

                //return $this->redirectToRoute('vacreq_home');

                //send respond confirmation email to a submitter
                $vacreqUtil = $this->get('vacreq_util');
                if( $status == 'canceled' ) {
                    //an email should be sent to approver saying
                    // "FirstName LastName canceled/withdrew their business travel / vacation request described below:"
                    // and list all variable names and values in the email.
                    $approversNameStr = $vacreqUtil->sendCancelEmailToApprovers( $entity, $user, $status );
                } else {
                    $approversNameStr = null;
                    $vacreqUtil->sendSingleRespondEmailToSubmitter( $entity, $user, $status );
                }

                //Flash
                $statusStr = $status;
                if( $status == 'pending' ) {
                    $statusStr = 'set to Pending';
                }

                $event = ucwords($requestName)." ID #" . $entity->getId() . " for " . $entity->getUser() . " has been " . $statusStr . " by " . $user;
                $event .= ": ".$entity->getDetailedStatus().".";
                if( $approversNameStr ) {
                    $event .= " Confirmation email(s) have been sent to ".$approversNameStr.".";
                }

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $event
                );

                if( $entity->getRequestType()->getAbbreviation() == "carryover" ) {
                    $eventType = 'Carry Over Request Updated';
                } else {
                    $eventType = 'Business/Vacation Request Updated';
                }

                //Event Log
                $userSecUtil = $this->container->get('user_security_utility');
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'), $event, $user, $entity, $request, $eventType);

            }

        }

        //redirect to myrequests for owner
        if( $entity->getUser()->getId() == $user->getId() ) {
            return $this->redirectToRoute("vacreq_myrequests",array('filter[requestType]'=>$entity->getRequestType()->getId()));
        }

        $url = $request->headers->get('referer');
        //exit('url='.$url);

        //when status is changed from email, then the url is a system home page
        if( $url && strpos($url, 'incoming-requests') !== false ) {
            return $this->redirect($url);
        }

        //return $this->redirectToRoute('vacreq_show', array('id' => $entity->getId()));
        return $this->redirectToRoute('vacreq_incomingrequests',array('filter[requestType]'=>$entity->getRequestType()->getId()));
    }

    /**
     * submitter can submit a "cancellation-request" for an entire, already approved request
     * @Route("/status-cancellation-request/{id}", name="vacreq_status_cancellation-request")
     * @Method({"GET"})
     */
    public function statusCancellationRequestAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $entity = $em->getRepository('OlegVacReqBundle:VacReqRequest')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Request by id='.$id);
        }

        //check permissions
        if( false == $this->get('security.context')->isGranted("update", $entity) ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        if( !$entity->isOverallStatus('approved') ) {
            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                'You can not submit a Cancellation Requested for a not already approved request.'
            );
            return $this->redirectToRoute('vacreq_myrequests');
        }

        $entity->setExtraStatus("Cancellation Requested");
        $em->flush();

        $requestName = $entity->getRequestName();
        $userNameOptimal = $entity->getUser()->getUsernameOptimal();
        $eventSubject = $userNameOptimal." is requesting cancellation of a ".ucwords($requestName)." ID #" . $entity->getId();

        //send email to an approver
        $break = "\r\n";

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
        $message .= $break.$entity."".$break;

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

        $vacreqUtil = $this->get('vacreq_util');
        $approversNameStr = $vacreqUtil->sendGeneralEmailToApproversAndEmailUsers($entity,$eventSubject,$message);

        $eventSubject = $eventSubject.". Email(s) have been sent to ".$approversNameStr;

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            $eventSubject
        );

        $eventType = 'Business/Vacation Request Updated';

        //Event Log
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'), $eventSubject, $user, $entity, $request, $eventType);

        return $this->redirectToRoute('vacreq_myrequests');
    }
    /**
     * approver can change a status of a "cancellation-request" for an entire, already approved request
     * The approver can then change the status from "Cancellation Requested" to either "Cancellation Approved (Canceled)" or "Cancellation Denied (Approved)"
     * cancellation-request => cancellation-request-approved => canceled
     * cancellation-request => cancellation-request-rejected => approved
     *
     * @Route("/cancellation-request/status/{id}/{status}", name="vacreq_status_cancellation-request_change")
     * @Route("/cancellation-request/estatus/{id}/{status}", name="vacreq_status_cancellation-request_email_change")
     * @Method({"GET"})
     */
    public function statusCancellationRequestChaneAction(Request $request, $id, $status) {
        $em = $this->getDoctrine()->getManager();
        $routeName = $request->get('_route');
        $user = $this->get('security.context')->getToken()->getUser();

        $entity = $em->getRepository('OlegVacReqBundle:VacReqRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Request by id=' . $id);
        }

        $logger = $this->container->get('logger');
        $logger->notice($entity->getId()." (".$routeName.")".": status=".$status."; set by user=".$user);

        //check permissions
        if( false == $this->get('security.context')->isGranted("changestatus", $entity) ) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //if not Cancellation Requested and vacreq_status_cancellation-request_email_change => redirect to incoming request page
        if( $entity->getExtraStatus() != "Cancellation Requested" && $routeName == 'vacreq_status_cancellation-request_email_change' ) {
            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                "This cancellation request for ".$entity->getRequestName()." ID #" . $entity->getId()." has already been completed by ".$entity->getApprover()
            );
            return $this->redirectToRoute('vacreq_incomingrequests');
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
        $eventSubject = $entity->getExtraStatus()." of a ".ucwords($requestName)." ID #" . $entity->getId() . " by " . $userNameOptimal;

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            $eventSubject
        );

        $eventType = 'Business/Vacation Request Updated';

        //Event Log
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'), $eventSubject, $user, $entity, $request, $eventType);

        //set confirmation email to submitter and email users
        $vacreqUtil = $this->get('vacreq_util');
        $message = $eventSubject;
        $vacreqUtil->sendSingleRespondEmailToSubmitter( $entity, $user, null, $message );

        $url = $request->headers->get('referer');

        //when status is changed from email, then the url is a system home page
        if( $url && strpos($url, 'incoming-requests') !== false ) {
            return $this->redirect($url);
        }

        return $this->redirectToRoute('vacreq_incomingrequests',array('filter[requestType]'=>$entity->getRequestType()->getId()));
    }

    /**
     * @Route("/send-reminder-email/{id}", name="vacreq_send_reminder_email")
     * @Method({"GET"})
     */
    public function sendReminderEmailAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $entity = $em->getRepository('OlegVacReqBundle:VacReqRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Request by id=' . $id);
        }

        //check permissions
        if( false == $this->get('security.context')->isGranted("update", $entity) && false === $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR')) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        //set confirmation email to approver and email users
        $vacreqUtil = $this->get('vacreq_util');
        $approversNameStr = $vacreqUtil->sendConfirmationEmailToApprovers( $entity );

        $eventSubject = 'Reminder email(s) has been sent to '.$approversNameStr;

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            $eventSubject
        );

        return $this->redirectToRoute('vacreq_myrequests');
    }



    public function createRequestForm( $entity, $cycle, $request ) {

        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->get('vacreq_util');
        $routeName = $request->get('_route');

        $user = $this->get('security.context')->getToken()->getUser();
//        if( !$entity ) {
//            $entity = new VacReqRequest($user);
//        }

        $admin = false;
        if( $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            $admin = true;
        }

        $roleApprover = false;
        if( $this->get('security.context')->isGranted("changestatus", $entity) ) {
            $roleApprover = true;
        }

        $requestType = $entity->getRequestType();

        //get submitter groups: VacReqRequest, create
        $tentativeInstitutions = null;
        $groupParams = array();
        if( $requestType->getAbbreviation() == "carryover" ) {

            //tentative institutions
            $tentativeGroupParams = array();
            $tentativeGroupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
            if( $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') == false ) {
                $tentativeGroupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
            }
            $tentativeInstitutions = $vacreqUtil->getGroupsByPermission($user,$tentativeGroupParams);

            //carry-over institution
            $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');

            $roleCarryOverApprover = false;
            if( $this->get('security.context')->isGranted("changestatus-carryover", $entity) ) {
                $roleCarryOverApprover = true;
            }

        } else {
            $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
            if( $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') == false ) {
                $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
            }
        }
        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);
        //echo "organizationalInstitutions count=".count($organizationalInstitutions)."<br>";

        if( count($organizationalInstitutions) == 0 ) {
            $adminUsers = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole("ROLE_VACREQ_ADMIN");
            $emails = array();
            foreach( $adminUsers as $adminUser ) {
                $singleEmail = $adminUser->getSingleEmail();
                if( $singleEmail ) {
                    $emails[] = $adminUser->getSingleEmail();
                }
            }
            $emailStr = "";
            if( count($emails) > 0 ) {
                $emailStr = " Admin email(s): " . implode(", ", $emails);
            }
            //Flash
            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have any assigned Submitter role for a Business/Vacation Request.".
                " Please contact the site administrator to have a Submitter role.".$emailStr
            );
        }

        //get holidays url
        $userSecUtil = $this->container->get('user_security_utility');
        $holidaysUrl = $userSecUtil->getSiteSettingParameter('holidaysUrl');
        if( !$holidaysUrl ) {
            throw new \InvalidArgumentException('holidaysUrl is not defined in Site Parameters.');
        }
        $holidaysUrl = '<a target="_blank" href="'.$holidaysUrl.'">holidays</a>';

        $params = array(
            'sc' => $this->get('security.context'),
            'em' => $em,
            'user' => $entity->getUser(),
            'cycle' => $cycle,
            'roleAdmin' => $admin,
            'roleApprover' => $roleApprover,
            'roleCarryOverApprover' => $roleCarryOverApprover,
            'organizationalInstitutions' => $organizationalInstitutions,
            'tentativeInstitutions' => $tentativeInstitutions,
            'holidaysUrl' => $holidaysUrl
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
            $sourceYearRanges = array(
                (date("Y")-1) => $currentYearRange,     //THIS YEAR (default)
                (date("Y")-2) => $previousYearRange     //PREVIOUS YEAR
            );
            $params['sourceYearRanges'] = $sourceYearRanges;

            //destinationYearRanges: Current Academic Year and Next Academic year
            $destinationYearRanges = array(
                (date("Y")) => $nextYearRange,        //NEXT YEAR (default)
                (date("Y")-1) => $currentYearRange    //THIS YEAR
            );
            $params['destinationYearRanges'] = $destinationYearRanges;
        }

        $form = $this->createForm(
            new VacReqRequestType($params),
            $entity,
            array(
                'disabled' => $disabled,
                'method' => $method,
                //'action' => $action
            )
        );

        return $form;
    }


    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return null;
        }
        $userSecUtil = $this->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->container->getParameter('vacreq.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
    }





    ////////////////////////////// service and testing methods //////////////////////////////////
    /**
     * @Route("/import-old-data/", name="vacreq_import_old_data")
     * @Method({"GET"})
     */
    public function importOldDataAction(Request $request) {

        if( !$this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $vacReqImportData = $this->get('vacreq_import_data');
        $res = $vacReqImportData->importOldData();

        //exit('Imported result: '.$res);

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Imported result: '.$res
        );

        return $this->redirectToRoute('vacreq_incomingrequests');
    }


    /**
     * @Route("/delete-imported-old-data/", name="vacreq_delete_imported_old_data")
     * @Method({"GET"})
     */
    public function deleteImportedOldDataAction(Request $request) {

        if( !$this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('OlegVacReqBundle:VacReqRequest');

        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');
        //$dql->where("request.exportId != 0");

        $query = $em->createQuery($dql);

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
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Removed requests: '.count($requests)
        );

        return $this->redirectToRoute('vacreq_incomingrequests');
    }


    /**
     * @Route("/setdates-imported-old-data/", name="vacreq_setdates_imported_old_data")
     * @Method({"GET"})
     */
    public function setdatesImportedOldDataAction(Request $request) {

        if( !$this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('OlegVacReqBundle:VacReqRequest');

        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin("request.requestType", "requestType");

        $dql->where("requestType.abbreviation = 'business-vacation'");
        $dql->andWhere("request.firstDayAway IS NULL");
        $dql->andWhere("request.firstDayBackInOffice IS NULL");

        $query = $em->createQuery($dql);

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
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Updated dates requests: '.count($requests)
        );

        return $this->redirectToRoute('vacreq_incomingrequests');
    }


    /**
     * @Route("/emailtest/", name="vacreq_emailtest")
     * @Method({"GET"})
     */
    public function emailTestAction(Request $request)
    {

        if (!$this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $emailUtil = $this->container->get('user_mailer_utility');

        //$user = $this->get('security.context')->getToken()->getUser();
        //$toEmail = $user->getSingleEmail();

        $toEmail = "cinava@yahoo.com,cinava10@gmail.com";
        $ccs = "oleg_iv@yahoo.com,cinava10@gmail.com,oli2002@med.cornell.edu";

        $emailUtil->sendEmail($toEmail, "Test Email Subject", "Test Email Message", $ccs);

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Test email sent to: '.$toEmail.' and ccs to:'.$ccs
        );

        return $this->redirectToRoute('vacreq_incomingrequests');
    }


    /**
     * TODO: clean overlap approved vacation requests
     * @Route("/overlaps/", name="vacreq_overlaps")
     * @Method({"GET"})
     */
    public function getOverlapRequestsAction(Request $request)
    {

        if (!$this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
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


        exit('2');
        return $this->redirectToRoute('vacreq_incomingrequests');
    }
    public function findUniqueUserRequests() {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('OlegVacReqBundle:VacReqRequest');

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

        $query = $em->createQuery($dql);

        $requests = $query->getResult();
        echo "requests with users=".count($requests)."<br>";
        return $requests;
    }
    public function analyzeRequests($userId,$count=1) {
        $vacreqUtil = $this->get('vacreq_util');
        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($userId);
        $overlapRequests = $vacreqUtil->getOverlappedUserRequests($user,true,true);
        //$overlapRequests = $vacreqUtil->getNotOverlapNumberOfWorkingDays($user,'requestVacation');

        if( count($overlapRequests) > 0 ) {
            $logger->error($count." ^########## user: " . $user);
            echo $count." ^########## user: " . $user . "<br><br><br>";
            $count++;
        }
        return $count;
    }



}
