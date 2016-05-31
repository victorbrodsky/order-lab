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

        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->get('vacreq_util');

        $user = $this->get('security.context')->getToken()->getUser();

        $entity = new VacReqRequest($user);

        if( false == $this->get('security.context')->isGranted("create", $entity) ) {
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

        if( $form->isSubmitted() && $form->isValid() ) {

            //set final (global) fields
            $entity->setFinalFields();

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

            //Event Log
            $requestName = $entity->getRequestName();
            $event = $requestName . " for ".$entity->getUser()." has been created";
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );

            $emailUtil = $this->get('user_mailer_utility');
            $break = "\r\n";

            //set confirmation email to submitter and approver and email users
            $subject = $requestName." #".$entity->getId()." Confirmation";
            $subject = $requestName." #".$entity->getId()." Confirmation";
            $message = "Dear ".$entity->getUser()->getUsernameOptimal().",".$break.$break;
            $message .= "You have successfully submitted the ".$requestName." #".$entity->getId().". ";
            $message .= "The approver will review your request soon.";
            $message .= $break.$break."**** PLEASE DON'T REPLY TO THIS EMAIL ****";
            $emailUtil->sendEmail( $user->getSingleEmail(), $subject, $message, null, null );

            //set confirmation email to approver and email users
            $vacreqUtil->sendConfirmationEmailToApprovers( $entity );

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
        $totalApprovedDaysString = $vacreqUtil->getApprovedDaysString($user);
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
            'title' => $title,
            'newCarryOverRequest' => $newCarryOverRequest
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

        if ($form->isSubmitted() && $form->isValid()) {

            if( $routName == 'vacreq_review' ) { //review

                //set final (global) fields
                $entity->setFinalFields();

                $overallStatus = $entity->getStatus();

                if( $overallStatus == "pending" ) {
                    $entity->setApprover(null);
                } else {
                    $entity->setApprover($user);
                }

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

                //set final (global) fields
                $entity->setFinalFields();

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
            }

            if( $action == 'pending' ) {
                $action = 'set to Pending';
            }

            //Event Log
            $break = "\r\n";
            $event = "Request for ".$entity->getUser()." has been ".$action." by ".$user.$break.$break;
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
     * @Method({"GET"})
     * @Template("OlegVacReqBundle:Request:edit.html.twig")
     */
    public function statusAction(Request $request, $id, $requestName, $status) {

        //if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') ) {
        //    return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        //}

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $entity = $em->getRepository('OlegVacReqBundle:VacReqRequest')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Request by id='.$id);
        }

        if( false == $this->get('security.context')->isGranted("changestatus", $entity) ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
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
                    if ($status != 'canceled' && $status != 'pending') {
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

                $em->persist($entity);
                $em->flush();

                //return $this->redirectToRoute('vacreq_home');

                //Flash
                $statusStr = $status;
                if( $status == 'pending' ) {
                    $statusStr = 'set to Pending';
                }
                $event = ucwords($requestName)." ID " . $entity->getId() . " for " . $entity->getUser() . " has been " . $statusStr . " by " . $user;
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

                //send respond confirmation email to a submitter
                $vacreqUtil = $this->get('vacreq_util');

                if( $status == 'canceled' ) {
                    //an email should be sent to approver saying
                    // "FirstName LastName canceled/withdrew their business travel / vacation request described below:"
                    // and list all variable names and values in the email.
                    $vacreqUtil->sendCancelEmailToApprovers( $entity, $user, $status );
                } else {
                    $vacreqUtil->sendSingleRespondEmailToSubmitter( $entity, $user, $status );
                }

            }

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
        $groupParams = array();
        if( $requestType->getAbbreviation() == "carryover" ) {
            $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
        } else {
            $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
            if( $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') == false ) {
                $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
            }
        }
        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);

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
            'organizationalInstitutions' => $organizationalInstitutions,
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





    /**
     * @Route("/import-old-data/", name="vacreq_import_old_data")
     * @Method({"GET"})
     * @Template("OlegVacReqBundle:Request:edit.html.twig")
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
     * @Template("OlegVacReqBundle:Request:edit.html.twig")
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

}
