<?php

namespace Oleg\VacReqBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\Roles;
use Oleg\UserdirectoryBundle\Form\SimpleUserType;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\VacReqBundle\Entity\VacReqCarryOver;
use Oleg\VacReqBundle\Entity\VacReqRequest;
use Oleg\VacReqBundle\Entity\VacReqSettings;
use Oleg\VacReqBundle\Entity\VacReqUserCarryOver;
use Oleg\VacReqBundle\Form\VacReqEmailusersType;
use Oleg\VacReqBundle\Form\VacReqGroupType;
use Oleg\VacReqBundle\Form\VacReqRequestType;
use Oleg\VacReqBundle\Form\VacReqUserCarryOverType;
use Oleg\VacReqBundle\Form\VacReqUserComboboxType;
use Oleg\VacReqBundle\Form\VacReqUserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



class CarryOverController extends Controller
{


//    /**
//     * @Route("/carry-over-request/review/{id}", name="vacreq_carryoverrequest_review")
//     * @Method({"GET", "POST"})
//     * @Template("OlegVacReqBundle:CarryOver:carryoverrequest.html.twig")
//     */
//    public function carryOverRequestReviewAction(Request $request, $id)
//    {
//
//        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
//            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//        //$vacreqUtil = $this->get('vacreq_util');
//        exit('not implemented');
//
//        $subjectUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($userId);
//
//        $userCarryOver = $em->getRepository('OlegVacReqBundle:VacReqUserCarryOver')->findOneByUser($userId);
//
//        if( !$userCarryOver ) {
//            $userCarryOver = new VacReqUserCarryOver($subjectUser);
//        }
//
//        //add next year, current year, [Current -1], [Current -2]
//        $this->addCarryOverByYears($userCarryOver);
//
//        $cycle = 'edit';
//
//        $form = $this->createCarryOversForm($userCarryOver,$cycle,$request);
//
//        $form->handleRequest($request);
//
//        if( $form->isSubmitted() && $form->isValid() ) {
//
//            $em->persist($userCarryOver);
//            $em->flush();
//
//            //Event Log
////            $eventType = "Business/Vacation Request Created";
//            $event = "Carry Over Days for ".$subjectUser." has been updated";
////            $userSecUtil = $this->container->get('user_security_utility');
////            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);
//
//            //Flash
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                $event
//            );
//
//            return $this->redirectToRoute('vacreq_mygroup');
//        }
//
//        return array(
//            'subjectUser' => $subjectUser,
//            'form' => $form->createView(),
//            'cycle' => $cycle,
//        );
//
//    }



    /**
     * @Route("/carry-over-vacation-days/{userId}", name="vacreq_carryover")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Group:carryover.html.twig")
     */
    public function carryOverAction(Request $request, $userId)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        //$vacreqUtil = $this->get('vacreq_util');

        $subjectUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($userId);

        $userCarryOver = $em->getRepository('OlegVacReqBundle:VacReqUserCarryOver')->findOneByUser($userId);

        if( !$userCarryOver ) {
            $userCarryOver = new VacReqUserCarryOver($subjectUser);
        }

        //add next year, current year, [Current -1], [Current -2]
        $this->addCarryOverByYears($userCarryOver);

        $cycle = 'edit';

        $form = $this->createCarryOversForm($userCarryOver,$cycle,$request);

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            $em->persist($userCarryOver);
            $em->flush();

            //Event Log
//            $eventType = "Business/Vacation Request Created";
            $event = "Carry Over Days for ".$subjectUser." has been updated";
//            $userSecUtil = $this->container->get('user_security_utility');
//            $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );

            return $this->redirectToRoute('vacreq_mygroup');
        }

        return array(
            'subjectUser' => $subjectUser,
            'form' => $form->createView(),
            'cycle' => $cycle,
        );

    }
    public function createCarryOversForm( $entity, $cycle, $request=null ) {

        $em = $this->getDoctrine()->getManager();
        //$vacreqUtil = $this->get('vacreq_util');
        //$user = $this->get('security.context')->getToken()->getUser();

        $params = array(
            'sc' => $this->get('security.context'),
            'em' => $em,
            'cycle' => $cycle,
        );

//        $disabled = false;
//        $method = 'GET';
//        if( $cycle == 'edit' ) {
//            $method = 'POST';
//        }

        $form = $this->createForm(
            new VacReqUserCarryOverType($params),
            $entity,
            array(
                //'disabled' => $disabled,
                //'method' => $method,
                //'action' => $action
            )
        );

        return $form;
    }
    //add next year, current year, [Current -1], [Current -2]
    public function addCarryOverByYears( $userCarryOver ) {
        //next year: 2016-2017
        //$nextStartYear = date("Y"); //2016
        $this->addCarryOverByAcademicYear($userCarryOver,+1);

        //current year: 2015-2016 (reference point: current academical year is 2015-2016)
        //$currentStartYear = date("Y")-1; //2015
        $this->addCarryOverByAcademicYear($userCarryOver,0);

        //current-1 year: 2014-2015
        //$currentMinus1StartYear = date("Y")-2; //2014
        $this->addCarryOverByAcademicYear($userCarryOver,-1);

        //current-2 year: 2013-2014
        //$currentMinus2StartYear = date("Y")-3; //2013
        $this->addCarryOverByAcademicYear($userCarryOver,-2);
    }
    public function addCarryOverByAcademicYear( $userCarryOver, $yearIndex ) {

        $vacreqUtil = $this->get('vacreq_util');

        //get current academical start year:
        $currentStartYear = date("Y"); //2016
        $startAcademicYearStr = $vacreqUtil->getEdgeAcademicYearDate( $currentStartYear, "Start" );
        $startAcademicYearDate = new \DateTime($startAcademicYearStr); //2015-07-01
        //echo "startAcademicYearDate=".$startAcademicYearDate->format("Y-m-d")."<br>";

        if( new \DateTime() > $startAcademicYearDate ) {
            $currentStartYear = date("Y")-1; //2015
        } else {
            $currentStartYear = date("Y"); //2016
        }

        $startYear = $currentStartYear + $yearIndex;

        $carryOver = $userCarryOver->getCarryOverByYear($startYear);
        if( !$carryOver ) {
            $carryOver = new VacReqCarryOver();
            $carryOver->setYear($startYear);
            $userCarryOver->addCarryOver($carryOver);
        }
    }





    /**
     * approved, rejected, pending, canceled
     * @Route("/carry-over-vacation-days/status/{id}/{requestName}/{status}", name="vacreq_status_change_carryover")
     * @Route("/carry-over-vacation-days/estatus/{id}/{requestName}/{status}", name="vacreq_status_email_change_carryover")
     * @Method({"GET"})
     * @Template("OlegVacReqBundle:Request:edit.html.twig")
     */
    public function statusAction(Request $request, $id, $requestName, $status) {

//        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') ) {
//            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
//        }

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $routeName = $request->get('_route');
        $user = $this->get('security.context')->getToken()->getUser();
        $vacreqUtil = $this->get('vacreq_util');
        $emailUtil = $this->container->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $break = "\r\n";

        if( !$status ) {
            throw $this->createNotFoundException('Status is invalid: status='.$status);
        }

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

//        //testing
//        if( $entity->getTentativeStatus() == 'pending' ) {
//            $tentative = true;
//        } else {
//            $tentative = false;
//        }
//        if( $tentative ) {
//            $subjectInst = $entity->getTentativeInstitution();
//        } else {
//            $subjectInst = $entity->getInstitution();
//        }
//
//        //get approver role for subject institution
//        if( $subjectInst ) {
//
//            //get user allowed groups
//            $vacreqUtil = $this->container->get('vacreq_util');
//
//            if( $tentative ) {
//                $tentativeGroupParams = array();
//                $tentativeGroupParams['asObject'] = true;
//                $tentativeGroupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
//                $groupInstitutions = $vacreqUtil->getGroupsByPermission($user,$tentativeGroupParams);
//            } else {
//                $groupParams = array('asObject'=>true);
//                $tentativeGroupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
//                $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
//                $groupInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);
//            }
//
//            //check if subject has at least one of the $groupInstitutions
//            foreach( $groupInstitutions as $inst ) {
//                echo $inst." == ".$subjectInst."<br>";
//                if( $inst->getId() == $subjectInst->getId() ) {
//                    exit('permission ok!');
//                }
//            }
//
//        }
//        exit('permission not ok');

        echo "tent status=".$entity->getTentativeStatus()."<br>";
        if(
            $this->get('security.context')->isGranted("changestatus", $entity)
        ) {
            //OK
        } else {
            exit('Status: no permission to approve/reject');
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

//        if( $entity->getTentativeStatus() == 'pending' ) {
//            //first step: group approver
//            if( $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') &&
//                $this->get('security.context')->isGranted("changestatus", $entity)
//            ) {
//                //OK
//            } else {
//                exit('TentativeStatus: no permission to approve/reject');
//                return $this->redirect( $this->generateUrl('vacreq-nopermission') );
//            }
//        } else {
//            //second step: supervisor
//            if( $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') &&
//                $this->get('security.context')->isGranted("changestatus-carryover", $entity)
//            ) {
//                //OK
//            } else {
//                exit('Status: no permission to approve/reject');
//                return $this->redirect( $this->generateUrl('vacreq-nopermission') );
//            }
//        }

        /////////////// log status ////////////////////////
        $logger->notice($entity->getId()." (".$routeName.")".": status=".$status."; set by user=".$user);
        /////////////// EOF log status ////////////////////////


//        //if not pending and vacreq_status_email_change => redirect to incoming request page
//        if( $entity->getStatus() != "pending" && $routeName == 'vacreq_status_email_change' ) {
//            //Flash
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                "This ".$entity->getRequestName()." ID #" . $entity->getId()." has already been completed by ".$entity->getApprover()
//            );
//            return $this->redirectToRoute('vacreq_incomingrequests');
//        }

        //Now we have two cases: first and second step approval

        if( $entity->getTentativeStatus() == 'pending' ) {
            ////////////// first step: group approver ///////////////////
            //setTentativeInstitution to approved or rejected

            $entity->setTentativeStatus($status);

            if( $status == "pending" ) {
                $entity->setTentativeApprover(null);
            } else {
                $entity->setTentativeApprover($user);
                $entity->setTentativeApprovedRejectDate(new \DateTime());
            }

            //send email to supervisor for a final approval
            if( $status == 'approved' ) {
                $approversNameStr = $vacreqUtil->sendConfirmationEmailToApprovers($entity);

                //Event Log
                $requestName = $entity->getRequestName();
                $eventType = 'Carry Over Request Updated';
                $event = $requestName . " ID #".$entity->getId()." for ".$entity->getUser()." has been tentatively approved by ".$entity->getTentativeApprover().". ".
                    "Email for a final approval has been sent to ".$approversNameStr;
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

                //Flash
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $event
                );
            }

            //send email to submitter
            if( $status == 'rejected' ) {

                //since it's rejected then set status to rejected
                $entity->setApprover($user);
                $entity->setStatus($status);

                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was rejected.
                $subjectRejected = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange()." was rejected.";

                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
                $bodyRejected = $entity->getTentativeApprover(). " has rejected your request ID #".$entity->getId()." to carry over ".
                    $entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();

                //request info
                $bodyRejected .= $break.$break.$entity;

                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectRejected, $bodyRejected, null, null );

                //Event Log
                $requestName = $entity->getRequestName();
                $eventType = 'Carry Over Request Updated';
                $event = $requestName . " ID #".$entity->getId()." for ".$entity->getUser()." has been tentatively rejected by ".
                    $entity->getTentativeApprover().". ".
                    "Confirmation email has been sent to the submitter ".$entity->getUser().".";
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

                //Flash
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $event
                );
            }


        } else {
            ////////////// second step: supervisor //////////////

            $entity->setStatus($status);

            if( $status == "pending" ) {
                $entity->setApprover(null);
            } else {
                $entity->setApprover($user);
            }

            if( $status == "approved" ) {
                //process carry over request days if request is approved
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

                //send a confirmation email to submitter
                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was approved.
                $subjectApproved = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange() . " was approved.";

                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
                $bodyApproved = $entity->getTentativeApprover(). " has approved your request to carry over ".
                    $entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();

                //request info
                $bodyApproved .= $break.$break.$entity;

                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectApproved, $bodyApproved, null, null );
            }


            //send email to submitter
            if( $status == 'rejected' ) {
                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was rejected.
                $subjectRejected = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange() . " was rejected.";

                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
                $bodyRejected = $entity->getTentativeApprover(). " has rejected your request to carry over ".
                    $entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();

                //request info
                $bodyRejected .= $break.$break.$entity;

                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectRejected, $bodyRejected, null, null );

                //Event Log
                $requestName = $entity->getRequestName();
                $eventType = 'Carry Over Request Updated';
                $event = $requestName . " for ".$entity->getUser()." has been tentatively rejected by.".$entity->getTentativeApprover().
                    "Email for a final approval has been sent to the submitter ".$entity->getUser()->getSingleEmail();
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

                //Flash
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $event
                );
            }
        }


        $em->persist($entity);
        $em->flush();


        $url = $request->headers->get('referer');
        //exit('url='.$url);

        //when status is changed from email, then the url is a system home page
        if( $url && strpos($url, 'incoming-requests') !== false ) {
            return $this->redirect($url);
        }

        //return $this->redirectToRoute('vacreq_show', array('id' => $entity->getId()));
        return $this->redirectToRoute('vacreq_incomingrequests',array('filter[requestType]'=>$entity->getRequestType()->getId()));
    }



}
