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

namespace Oleg\UserdirectoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SectionUserController extends UserController
{


    /**
     * @Route("/my-team-ajax/", name="employees_my_team")
     * @Template("OlegUserdirectoryBundle:SectionUser:my-team.html.twig")
     * @Method({"GET", "POST"})
     */
    public function myTeamAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
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

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($userid);

        return array(
            'user' => $user,
            'cycle' => 'show_user',
            'teamType' => $teamType,
            //'postData' => $postData,
            'sitename' => $this->container->getParameter('employees.sitename'),
        );

//        $showUserArr = $this->showUser($userid,$this->container->getParameter('employees.sitename'),false);
//
//        $template = $this->render('OlegUserdirectoryBundle:Profile:edit_user_only.html.twig',$showUserArr)->getContent();
//
//        $json = json_encode($template);
//        $response = new Response($json);
//        $response->headers->set('Content-Type', 'application/json');
//        return $response;
    }



    /**
     * @Route("/user-wrapper-ajax", name="employees_user_wrapper_ajax", options={"expose"=true})
     * @Template("OlegUserdirectoryBundle:SectionUser:user-wrapper.html.twig")
     * @Method({"GET", "POST"})
     */
    public function userWrapperAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $userid = $request->query->get('userid');
        $cycle = $request->query->get('cycle');
        //echo "userid=".$userid."<br>";

        if( strpos($cycle, 'show') !== false ) {
            $userWrappers = $em->getRepository('OlegUserdirectoryBundle:UserWrapper')->findByUser($userid);

            //find patients by patient's encounter's provider: patient->encounters->provider
            $repository = $em->getRepository('OlegOrderformBundle:Patient');
            $dql = $repository->createQueryBuilder("patient");
            $dql->leftJoin("patient.encounter", "encounter");
            $dql->where("encounter.provider = :userId");
            $parameters['userId'] = $userid;
            $dql->orderBy("patient.id","ASC"); //show latest first
            $query = $em->createQuery($dql);
            $query->setParameters($parameters);
            //echo "sql=".$query->getSql()."<br>";
            $patients = $query->getResult();

            //$patients = array();
        } else {
            //show all wrappers where user is this user or null
            //$userWrappers = $em->getRepository('OlegUserdirectoryBundle:UserWrapper')->findAll();
            $repository = $em->getRepository('OlegUserdirectoryBundle:UserWrapper');
            $dql = $repository->createQueryBuilder("wrapper");
            $dql->leftJoin("wrapper.user", "user");
            $dql->where("user = :userId OR user.id IS NULL");
            $parameters['userId'] = $userid;
            $dql->orderBy("wrapper.id","ASC"); //show latest first
            $query = $em->createQuery($dql);
            $query->setParameters($parameters);
            //echo "sql=".$query->getSql()."<br>";
            $userWrappers = $query->getResult();
        }



        return array(
            'userid' => $userid,
            'userWrappers' => $userWrappers,
            'patients' => $patients,
            'cycle' => $cycle,
            'sitename' => $this->container->getParameter('employees.sitename'),
        );

//        $showUserArr = $this->showUser($userid,$this->container->getParameter('employees.sitename'),false);
//
//        $template = $this->render('OlegUserdirectoryBundle:Profile:edit_user_only.html.twig',$showUserArr)->getContent();
//
//        $json = json_encode($template);
//        $response = new Response($json);
//        $response->headers->set('Content-Type', 'application/json');
//        return $response;
    }


}