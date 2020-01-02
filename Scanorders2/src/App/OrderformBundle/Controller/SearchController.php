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
 * User: DevServer
 * Date: 4/10/15
 * Time: 12:22 PM
 */

namespace Oleg\OrderformBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;


class SearchController extends Controller {


    /**
     * Lists all Message entities.
     *
     * @Route("/patients/search", name="scan_search_patients")
     * @Method("GET")
     * @Template()
     */
    public function searchPatientAction( Request $request ) {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
            false == $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('login') );
        }

        $entities = null;

        $allgets = $request->query->all();;
        //$patientid = trim( $request->get('patientid') );
        //print_r($allgets);
        //echo "<br>";

        $searchtype = null;
        $search = null;

        foreach( $allgets as $thiskey => $thisvalue ) {
            $searchtype = $thiskey;
            $search = $thisvalue;
            break;
        }

        $searchtype = str_replace("_"," ",$searchtype);

        //$searchtype = trim( $request->get('searchtype') );
        //$search = trim( $request->get('search') );
        //echo "searchtype=".$searchtype."<br>";
        //echo "search=".$search."<br>";

        if( $searchtype != "" && $search != "" ) {

            $searchUtil = $this->get('search_utility');

            $object = 'patient';
            $params = array('request'=>$request,'object'=>$object,'searchtype'=>$searchtype,'search'=>$search,'exactmatch'=>false);
            $res = $searchUtil->searchAction($params);
            $entities = $res[$object];
        }

        //echo "entities count=".count($entities)."<br>";

        return $this->render('OlegOrderformBundle:Patient:index.html.twig', array(
            'patientsearch' => $search,
            'patientsearchtype' => $searchtype,
            'patiententities' => $entities,
        ));
    }


} 