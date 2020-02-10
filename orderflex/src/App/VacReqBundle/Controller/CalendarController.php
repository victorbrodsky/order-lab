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


use App\VacReqBundle\Form\VacReqCalendarFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//use ADesigns\CalendarBundle\Event\CalendarEvent;
//use ADesigns\CalendarBundle\Entity\EventEntity;

//vacreq site

class CalendarController extends AbstractController
{

    /**
     * Template("AppVacReqBundle/Calendar/calendar.html.twig")
     * show the names of people who are away that day (one name per "event"/line).
     *
     * @Route("/away-calendar/", name="vacreq_awaycalendar")
     * @Method({"GET"})
     * @Template("AppVacReqBundle/Calendar/calendar-tattali.html.twig")
     */
    public function awayCalendarAction(Request $request) {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_OBSERVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUBMITTER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $userServiceUtil = $this->get('user_service_utility');
        $vacreqUtil = $this->get('vacreq_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $params = array();
        $params['em'] = $em;
        $params['supervisor'] = $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR');

        ///// NOT USED /////
        if(0) {
            //get submitter groups: VacReqRequest, create
            $groupParams = array();

            $groupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'create');
            $groupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus');
            if ($this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') == false) {
                $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
            }

            //to get the select filter with all groups under the supervisor group, find the first upper supervisor of this group.
            if ($this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR')) {
                $subjectUser = $user;
            } else {
                $groupParams['asSupervisor'] = true;
                $subjectUser = $vacreqUtil->getClosestSupervisor($user);
            }
            //echo "subjectUser=".$subjectUser."<br>";
            if (!$subjectUser) {
                $subjectUser = $user;
            }

            $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($subjectUser,$groupParams);
        }
        ///// EOF NOT USED /////

        $organizationalInstitutions = $vacreqUtil->getAllGroupsByUser($user);
//        foreach($organizationalInstitutions as $id=>$organizationalInstitution) {
//            echo $id.": group=".$organizationalInstitution."<br>";
//        }

        //$params['organizationalInstitutions'] = $organizationalInstitutions;
        $params['organizationalInstitutions'] = $userServiceUtil->flipArrayLabelValue($organizationalInstitutions);   //flipped

        $groupId = $request->query->get('group');
        //echo "groupId=".$groupId."<br>";

        $params['groupId'] = $groupId;

        $filterform = $this->createForm(VacReqCalendarFilterType::class, null, array('form_custom_value'=>$params));


        return array(
            'vacreqfilter' => $filterform->createView(),
            'groupId' => $groupId
        );
    }

}
