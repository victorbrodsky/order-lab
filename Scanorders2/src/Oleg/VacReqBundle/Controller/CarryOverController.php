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

}
