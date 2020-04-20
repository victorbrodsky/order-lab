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
 * Date: 8/30/2016
 * Time: 12:19 PM
 */

namespace App\CrnBundle\Controller;


use App\CrnBundle\Form\CrnAccessionDummyType;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


///**
// * Crn Accession controller.
// *
// * @Route("/accession")
// */

class CrnAccessionController extends OrderAbstractController {

    /**
     * Accession List
     * @Route("/accession-list/{listid}/{listname}", name="crn_accession_list")
     * @Template("AppCrnBundle/AccessionList/accession-list.html.twig")
     */
    public function complexAccessionListAction(Request $request, $listid, $listname)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CRN_USER') ){
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $securityUtil = $this->get('user_security_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //$listname
        $listnameArr = explode('-',$listname);
        $listname = implode(' ',$listnameArr);
        $listname = ucwords($listname);
        //echo "list: name=$listname; id=$listid <br>";

        //get list name by $listname, convert it to the first char as Upper case and use it to find the list in DB
        //for now use the mock page accession-list.html.twig

        //get list by id
        $accessionGroup = $em->getRepository('AppOrderformBundle:AccessionListHierarchyGroupType')->findOneByName('Accession');

        $parameters = array();

        $repository = $em->getRepository('AppOrderformBundle:AccessionListHierarchy');
        $dql = $repository->createQueryBuilder("list");

        $dql->leftJoin("list.accession", "accession");
        $dql->leftJoin("accession.procedure", "procedure");
        $dql->leftJoin("procedure.encounter", "encounter");
        $dql->leftJoin("encounter.patient", "patient");

        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");
        $dql->leftJoin("patient.mrn", "mrn");

        $dql->leftJoin("accession.accession", "accessionaccession");

        $dql->where("list.parent = :parentId AND list.organizationalGroupType = :accessionGroup");
        $parameters['parentId'] = $listid;
        $parameters['accessionGroup'] = $accessionGroup->getId();

        $dql->andWhere("list.type = 'user-added' OR list.type = 'default'");

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        //echo "sql=".$query->getSql()."<br>";

        $limit = 30;
        $paginator  = $this->get('knp_paginator');
        $accessions = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            //$request->query->getInt('page', 1),
            $limit,      /*limit per page*/
            array(
                'defaultSortFieldName' => 'accession.id',
                'defaultSortDirection' => 'DESC',
                'wrap-queries'=>true
            )
        );
        //$accessions = $query->getResult();

        //echo "accessions=".count($accessions)."<br>";

        $accessionListHierarchyObject = $em->getRepository('AppUserdirectoryBundle:PlatformListManagerRootList')->findOneByName('Accession List Hierarchy');

        //create accession form for "Add Accession" section
//        $status = 'invalid';
//        $system = $securityUtil->getDefaultSourceSystem($this->getParameter('crn.sitename'));
//        $newAccession = new Accession(true,$status,$user,$system);
        $accessionForm = $this->createAccessionForm();

        //src/App/CrnBundle/Resources/views/AccessionList/accession-list.html.twig
        return array(
            'accessionListId' => $listid,
            'accessionNodes' => $accessions,
            'title' => $listname,   //"accession List",
            'platformListManagerRootListId' => $accessionListHierarchyObject->getId(),
            'accessionForm' => $accessionForm->createView(),
            'cycle' => 'new',
            'formtype' => 'add-accession-to-list',
            'mrn' => null,
            'mrntype' => null
        );
    }


    /**
     * Listing accessions whose notes have been updated in the last 96 hours (4 days)
     *
     * @Route("/recent-accessions", name="crn_recent_accessions")
     * @Template("AppCrnBundle/AccessionList/recent-accessions.html.twig")
     */
    public function recentAccessionsAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CRN_USER') ){
            return $this->redirect( $this->generateUrl('crn-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $securityUtil = $this->get('user_security_utility');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //listing Accessions whose notes have been updated in the last 96 hours

        $parameters = array();

        $repository = $em->getRepository('AppOrderformBundle:Accession');
        $dql = $repository->createQueryBuilder("accession");

        $dql->leftJoin("accession.message", "message");
        $dql->leftJoin("message.editorInfos", "editorInfos");
        $dql->leftJoin("message.crnEntryMessage", "crnEntryMessage");
        $dql->leftJoin("crnEntryMessage.crnTasks", "crnTasks");

        $dql->leftJoin("accession.procedure", "procedure");
        $dql->leftJoin("procedure.encounter", "encounter");
        $dql->leftJoin("encounter.patient", "patient");

        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");
        $dql->leftJoin("patient.mrn", "mrn");

        $dql->leftJoin("accession.accession", "accessionaccession");

        $dql->where("crnEntryMessage.id IS NOT NULL");
        //$dql->andWhere("message.orderdate >= :hours96Ago OR editorInfos.modifiedOn >= :hours96Ago OR crnTasks.statusUpdatedDate >= :hours96Ago");

        if(0) {
            $andWhere = "message.orderdate >= :hours96Ago OR editorInfos.modifiedOn >= :hours96Ago OR crnTasks.statusUpdatedDate >= :hours96Ago";
            $dql->andWhere($andWhere);

            $hours96Ago = new \DateTime();
            $hours96Ago->modify('-96 hours');
            //$hours96Ago->modify('-5 hours');
            //$parameters['hours96Ago'] = $hours96Ago->format('Y-m-d');
            $parameters['hours96Ago'] = $hours96Ago;
        }

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        //echo "sql=".$query->getSql()."<br>";

        $limit = 30;
        $paginator  = $this->get('knp_paginator');
        $accessions = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            //$request->query->getInt('page', 1),
            $limit,      /*limit per page*/
            array(
                'defaultSortFieldName' => 'accession.id',
                'defaultSortDirection' => 'DESC',
                'wrap-queries'=>true
            )
        );

        //$accessions = $query->getResult();
        //echo "accessions=".count($accessions)."<br>";

        return array(
            'accessions' => $accessions,
            'title' => "Recent Accessions (96 hours)",
        );
    }



    public function createAccessionForm() {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        //$crnUtil = $this->get('crn_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $sitename = $this->getParameter('crn.sitename');

        $userTimeZone = $userSecUtil->getSiteSettingParameter('timezone',$sitename);

        $params = array(
            'cycle' => 'new',
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            'type' => null,
            'formtype' => 'crn-entry',
            'complexLocation' => false,
            'alias' => false,
            'timezoneDefault' => $userTimeZone,
        );

        $form = $this->createForm(CrnAccessionDummyType::class, null, array(
            'form_custom_value' => $params,
            //'form_custom_value_entity' => $patient
        ));

        return $form;
    }
    
}