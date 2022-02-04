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


use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\VacReqBundle\Entity\VacReqRequest;
use App\VacReqBundle\Form\VacReqFloatingDayType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//vacreq site

class FloatingDayController extends OrderAbstractController
{

    /**
     * @Route("/floating-day-request", name="vacreq_floating_day", methods={"GET"})
     * @Template("AppVacReqBundle/FloatingDay/floating-day.html.twig")
     */
    public function FloatingDayAction(Request $request) {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_OBSERVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUBMITTER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->get('user_service_utility');
        $vacreqUtil = $this->get('vacreq_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = new VacReqRequest($user);
        
        $params = array();
        $params['em'] = $em;
        //$params['supervisor'] = $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR');

//        $floatingNote = "The Juneteenth Holiday may be used as a floating holiday
//        only if you have an NYPH appointment. You can request a floating holiday however,
//        it must be used in the same fiscal year ending June 30, 2022. It cannot be carried over";
        $floatingNote = $userSecUtil->getSiteSettingParameter('floatingDayNote','vacreq');

        //$title = "Floating Day (The page and functionality are under construction!)";
        $title = $userSecUtil->getSiteSettingParameter('floatingDayName','vacreq');
        $title = $title . " - The page and functionality are under construction!";

        $cycle = 'new';

        $form = $this->createRequestForm($entity,$cycle,$request);

        $form->handleRequest($request);


        if( $form->isSubmitted() && $form->isValid() ) { //new
        
        
        }
        
        

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'floatingNote' => $floatingNote
        );
    }

    public function createRequestForm( $entity, $cycle, $request ) {

        $em = $this->getDoctrine()->getManager();
        $userServiceUtil = $this->get('user_service_utility');
        $vacreqUtil = $this->get('vacreq_util');
        $routeName = $request->get('_route');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $admin = false;
        if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
            $admin = true;
        }

        $roleApprover = false;
        if( $this->get('security.authorization_checker')->isGranted("changestatus", $entity) ) {
            $roleApprover = true;
        }

        $groupParams = array();

        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') == false ) {
            $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
        }

        $tentativeInstitutions = null;

        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);
        //echo "1 organizationalInstitutions count=".count($organizationalInstitutions)."<br>";

        //include this request institution to the $organizationalInstitutions array
        $organizationalInstitutions = $vacreqUtil->addRequestInstitutionToOrgGroup( $entity, $organizationalInstitutions );
        //echo "2 organizationalInstitutions count=".count($organizationalInstitutions)."<br>";

        //include this request institution to the $tentativeInstitutions array
        $tentativeInstitutions = $vacreqUtil->addRequestInstitutionToOrgGroup( $entity, $tentativeInstitutions, "tentativeInstitution" );

        if( count($organizationalInstitutions) == 0 ) {
            //If count($organizationalInstitutions) == 0 then try to run http://hosthame/order/directory/admin/sync-db/

            if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
                //admin user
                $groupPageUrl = $this->generateUrl(
                    "vacreq_approvers",
                    array(),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $warningMsg = "You don't have any group and/or assigned Submitter roles for the Business/Vacation Request site.".
                    ' <a href="'.$groupPageUrl.'" target="_blank">Please create a group and/or assign a Submitter role to your user account.</a> ';
            } else {
                //regular user
                $adminUsers = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole("ROLE_VACREQ_ADMIN", "infos.lastName", true);
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

                $warningMsg = "You don't have any group and/or assigned Submitter roles for the Business/Vacation Request site.".
                    " Please contact the site administrator to create a group and/or get a Submitter role for your account.".$emailStr;
            }
            //Flash
            $this->get('session')->getFlashBag()->add(
                'warning',
                $warningMsg
            );
        }

        //get holidays url
        $userSecUtil = $this->container->get('user_security_utility');
        $holidaysUrl = $userSecUtil->getSiteSettingParameter('holidaysUrl','vacreq');
        if( !$holidaysUrl ) {
            throw new \InvalidArgumentException('holidaysUrl is not defined in Site Parameters.');
        }
        $holidaysUrl = '<a target="_blank" href="'.$holidaysUrl.'">holidays</a>';

        //echo "roleApprover=".$roleApprover."<br>";
        //echo "roleCarryOverApprover=".$roleCarryOverApprover."<br>";

        $params = array(
            'container' => $this->container,
            'em' => $em,
            'user' => $entity->getUser(),
            'cycle' => $cycle,
            'roleAdmin' => $admin,
            'roleApprover' => $roleApprover,
            'organizationalInstitutions' => $userServiceUtil->flipArrayLabelValue($organizationalInstitutions),
            'tentativeInstitutions' => $userServiceUtil->flipArrayLabelValue($tentativeInstitutions),
            'holidaysUrl' => $holidaysUrl,
            'maxCarryOverVacationDays' => $userSecUtil->getSiteSettingParameter('maxCarryOverVacationDays','vacreq'),
            'noteForCarryOverDays' => $userSecUtil->getSiteSettingParameter('noteForCarryOverDays','vacreq'),
            //'maxVacationDays' => $userSecUtil->getSiteSettingParameter('maxVacationDays','vacreq'),
            //'noteForVacationDays' => $userSecUtil->getSiteSettingParameter('noteForVacationDays','vacreq'),
        );
        
        //set default floating day
        $floatingDayType = NULL;
        $parameters = array();
        $repository = $em->getRepository('AppVacReqBundle:VacReqFloatingTypeList');
        $dql = $repository->createQueryBuilder('list');
        $dql->andWhere("(list.type = :typedef OR list.type = :typeadd)");
        $dql->orderBy("list.orderinlist","ASC");
        $parameters['typedef'] = 'default';
        $parameters['typeadd'] = 'user-added';
        $query = $em->createQuery($dql);
        if( count($parameters) > 0 ) {
            $query->setParameters($parameters);
        }
        $floatingDayTypes = $query->getResult();
        if( count($floatingDayTypes) > 0 ) {
            $floatingDayType = $floatingDayTypes[0];
        }

        $params['defaultFloatingDayType'] = $floatingDayType;

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

        $form = $this->createForm(
            VacReqFloatingDayType::class,
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

}
