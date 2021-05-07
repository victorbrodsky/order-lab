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

use App\UserdirectoryBundle\Entity\Roles;
use App\UserdirectoryBundle\Form\SimpleUserType;
use App\UserdirectoryBundle\Util\UserUtil;
use App\VacReqBundle\Entity\VacReqCarryOver;
use App\VacReqBundle\Entity\VacReqRequest;
use App\VacReqBundle\Entity\VacReqSettings;
use App\VacReqBundle\Entity\VacReqUserCarryOver;
use App\VacReqBundle\Form\VacReqEmailusersType;
use App\VacReqBundle\Form\VacReqGroupType;
use App\VacReqBundle\Form\VacReqRequestType;
use App\VacReqBundle\Form\VacReqUserCarryOverType;
use App\VacReqBundle\Form\VacReqUserComboboxType;
use App\VacReqBundle\Form\VacReqUserType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;



class CarryOverController extends OrderAbstractController
{


//    /**
//     * @Route("/carry-over-request/review/{id}", name="vacreq_carryoverrequest_review", methods={"GET", "POST"})
//     * @Template("AppVacReqBundle/CarryOver/carryoverrequest.html.twig")
//     */
//    public function carryOverRequestReviewAction(Request $request, $id)
//    {
//
//        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
//            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
//        }
//
//        $em = $this->getDoctrine()->getManager();
//        //$vacreqUtil = $this->get('vacreq_util');
//        exit('not implemented');
//
//        $subjectUser = $em->getRepository('AppUserdirectoryBundle:User')->find($userId);
//
//        $userCarryOver = $em->getRepository('AppVacReqBundle:VacReqUserCarryOver')->findOneByUser($userId);
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
////            $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);
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
     * @Route("/carry-over-vacation-days/{userId}", name="vacreq_carryover", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/Group/carryover.html.twig")
     */
    public function carryOverAction(Request $request, $userId)
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //$vacreqUtil = $this->get('vacreq_util');

        $subjectUser = $em->getRepository('AppUserdirectoryBundle:User')->find($userId);

        $userCarryOver = $em->getRepository('AppVacReqBundle:VacReqUserCarryOver')->findOneByUser($userId);

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
            $eventType = "Carry Over Request Updated";
            $event = "Carry Over Days for ".$subjectUser." has been updated:<br>".$userCarryOver->getCarryOverInfo();
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->createUserEditEvent($this->getParameter('vacreq.sitename'),$event,$user,$userCarryOver,$request,$eventType);

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
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $params = array(
            'container' => $this->container,
            'em' => $em,
            'cycle' => $cycle,
        );

//        $disabled = false;
//        $method = 'GET';
//        if( $cycle == 'edit' ) {
//            $method = 'POST';
//        }

        $form = $this->createForm(
            VacReqUserCarryOverType::class,
            $entity,
            array(
                'form_custom_value' => $params,
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

        //TODO: fixed using getCurrentAcademicYearStartEndDates
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
     * @Route("/carry-over-vacation-days/status/{id}/{requestName}/{status}", name="vacreq_status_change_carryover", methods={"GET"})
     * @Route("/carry-over-vacation-days/estatus/{id}/{requestName}/{status}", name="vacreq_status_email_change_carryover", methods={"GET"})
     * @Template("AppVacReqBundle/Request/edit.html.twig")
     */
    public function statusAction(Request $request, $id, $requestName, $status) {

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $routeName = $request->get('_route');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $vacreqUtil = $this->get('vacreq_util');
        //$emailUtil = $this->container->get('user_mailer_utility');
        //$userSecUtil = $this->container->get('user_security_utility');
        //$break = "\r\n";

        if( !$status ) {
            throw $this->createNotFoundException('Status is invalid: status='.$status);
        }

        //supported statuses: approved, rejected
        if( $status != 'approved' && $status != 'rejected' ) {
            throw $this->createNotFoundException('Status is not supported (supported statuses: approved, rejected): status='.$status);
        }

        $entity = $em->getRepository('AppVacReqBundle:VacReqRequest')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Request by id='.$id);
        }

        /////////////// check permission: if user is in approvers => ok ///////////////
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
            $permitted = false;
            $approvers = $vacreqUtil->getRequestApprovers($entity);
            $approversName = array();
            foreach ($approvers as $approver) {
                if ($user->getId() == $approver->getId()) {
                    //ok
                    $permitted = true;
                }
                $approversName[] = $approver . "";
            }
            if ($permitted == false) {
                //Flash
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    "You can not review this request. This request can be approved or rejected by " . implode("; ", $approversName)
                );
                return $this->redirect($this->generateUrl('vacreq-nopermission'));
            }
        }
        /////////////// EOF check permission: if user is in approvers => ok ///////////////

        //echo "tent status=".$entity->getTentativeStatus()."<br>";
        if(
            $this->get('security.authorization_checker')->isGranted("changestatus", $entity)
        ) {
            //OK
        } else {
            //exit('changestatus: no permission to approve/reject'); //testing
            $this->get('session')->getFlashBag()->add(
                'warning',
                "no permission to approve/reject this carry over request"
            );
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }
        //exit('testing: email approval of carry over request OK'); //testing

        /////////////// log status ////////////////////////
        $logger->notice("CarryOverController statusAction: ".$entity->getId()." (".$routeName.")".": status=".$status."; set by user=".$user);
        /////////////// EOF log status ////////////////////////

        $originalStatus = $entity->getStatus();

        //Now we have two cases: first and second step approval

        //don't allow to change final status
        if( $entity->getStatus() && $entity->getStatus() != 'pending' ) {
            $event = "This request ID #".$entity->getId()." has been already ".$entity->getStatus()." by ".$entity->getApprover().
            " on ".$entity->getApprovedRejectDate()->format('F jS, Y');
            //Flash
            $this->get('session')->getFlashBag()->add(
                'notice',
                $event
            );
            return $this->redirectToRoute('vacreq_show',array('id'=>$entity->getId()));
        }

        //check if requested carry over days are already approved or denied
        $onlyCheck = true;
        $res = $vacreqUtil->processVacReqCarryOverRequest($entity,$onlyCheck);
        if( $res && $res['exists'] == true ) {
            //warning for overwrite:
            //"FirstName LastName already has X days carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year on file.
            // This carry over request asks for N days to be carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year.
            // Please enter the total amount of days that should be carried over 20YY-20ZZ academic year to the 20ZZ-20MM academic year: [ ]"
            //exit('exists days='.$res['days']);
            return $this->redirectToRoute('vacreq_review',array('id'=>$entity->getId()));
        }

        /////////////////// TWO CASES: pre-approval and final approval ///////////////////
        //$withRedirect=true; $update=true;
        $action = $vacreqUtil->processChangeStatusCarryOverRequest( $entity, $status, $user, $request, true, true ); //vacreq_status_change_carryover, vacreq_status_email_change_carryover

        if( $action == 'vacreq_review' ) {
            return $this->redirectToRoute('vacreq_review',array('id'=>$entity->getId()));
        }
        if( $action == 'vacreq-nopermission' ) {
            return $this->redirectToRoute('vacreq-nopermission');
        }

        $em->persist($entity);
        $em->flush();

        $vacreqUtil->syncVacReqCarryOverRequest( $entity, $originalStatus );

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
