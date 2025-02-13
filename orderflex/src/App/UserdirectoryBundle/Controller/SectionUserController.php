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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 11/1/2016
 * Time: 10:32 AM
 */

namespace App\UserdirectoryBundle\Controller;



use App\OrderformBundle\Entity\Patient; //process.py script: replaced namespace by ::class: added use line for classname=Patient


use App\UserdirectoryBundle\Entity\UserWrapper; //process.py script: replaced namespace by ::class: added use line for classname=UserWrapper


use App\UserdirectoryBundle\Entity\User;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SectionUserController extends UserController
{


    #[Route(path: '/my-team-ajax/', name: 'employees_my_team', methods: ['GET', 'POST'])]
    #[Template('AppUserdirectoryBundle/SectionUser/my-team.html.twig')]
    public function myTeamAction( Request $request ) {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $userid = $request->query->get('userid');
        $teamType = $request->query->get('teamType');
        //$currentPath = $request->query->get('currentPath');
        //echo "currentPath=".$currentPath."<br>";
        //$postData = $request->get('postData');

        //$request->setTargetUrl("/order/directory/");
        //$postData = $request->query->all();

        //echo "userid=".$userid."<br>";

        $user = $em->getRepository(User::class)->find($userid);

        return array(
            'user' => $user,
            'cycle' => 'show_user',
            'teamType' => $teamType,
            //'postData' => $postData,
            'sitename' => $this->getParameter('employees.sitename'),
        );

//        $showUserArr = $this->showUser($userid,$this->getParameter('employees.sitename'),false);
//
//        $template = $this->render('AppUserdirectoryBundle/Profile/edit_user_only.html.twig',$showUserArr)->getContent();
//
//        $json = json_encode($template);
//        $response = new Response($json);
//        $response->headers->set('Content-Type', 'application/json');
//        return $response;
    }



    #[Route(path: '/user-wrapper-ajax', name: 'employees_user_wrapper_ajax', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[Template('AppUserdirectoryBundle/SectionUser/user-wrapper.html.twig')]
    public function userWrapperAction( Request $request ) {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $userid = $request->query->get('userid');
        $cycle = $request->query->get('cycle');
        //echo "userid=".$userid."<br>";

        if( strpos((string)$cycle, 'show') !== false ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserWrapper'] by [UserWrapper::class]
            $userWrappers = $em->getRepository(UserWrapper::class)->findByUser($userid);

            //find patients by patient's encounter's provider: patient->encounters->provider
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:Patient'] by [Patient::class]
            $repository = $em->getRepository(Patient::class);
            $dql = $repository->createQueryBuilder("patient");
            $dql->leftJoin("patient.encounter", "encounter");
            $dql->where("encounter.provider = :userId");
            $parameters['userId'] = $userid;
            $dql->orderBy("patient.id","ASC"); //show latest first
            $query = $dql->getQuery(); //$query = $em->createQuery($dql);
            $query->setParameters($parameters);
            //echo "sql=".$query->getSql()."<br>";
            $patients = $query->getResult();

            //$patients = array();
        } else {
            //show all wrappers where user is this user or null
            //$userWrappers = $em->getRepository('AppUserdirectoryBundle:UserWrapper')->findAll();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserWrapper'] by [UserWrapper::class]
            $repository = $em->getRepository(UserWrapper::class);
            $dql = $repository->createQueryBuilder("wrapper");
            $dql->leftJoin("wrapper.user", "user");
            $dql->where("user = :userId OR user.id IS NULL");
            $parameters['userId'] = $userid;
            $dql->orderBy("wrapper.id","ASC"); //show latest first
            $query = $dql->getQuery(); //$query = $em->createQuery($dql);
            $query->setParameters($parameters);
            //echo "sql=".$query->getSql()."<br>";
            $userWrappers = $query->getResult();
        }



        return array(
            'userid' => $userid,
            'userWrappers' => $userWrappers,
            'patients' => $patients,
            'cycle' => $cycle,
            'sitename' => $this->getParameter('employees.sitename'),
        );

//        $showUserArr = $this->showUser($userid,$this->getParameter('employees.sitename'),false);
//
//        $template = $this->render('AppUserdirectoryBundle/Profile/edit_user_only.html.twig',$showUserArr)->getContent();
//
//        $json = json_encode($template);
//        $response = new Response($json);
//        $response->headers->set('Content-Type', 'application/json');
//        return $response;
    }


}