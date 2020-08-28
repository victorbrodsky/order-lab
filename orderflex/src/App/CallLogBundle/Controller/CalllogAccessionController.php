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

namespace App\CallLogBundle\Controller;



use App\CallLogBundle\Form\CalllogAccessionDummyType;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


///**
// * Calllog Accession controller.
// *
// * @Route("/accession")
// */

class CalllogAccessionController extends OrderAbstractController {

    /**
     * Accession List
     * @Route("/accession-list/{listid}/{listname}", name="calllog_accession_list")
     * @Template("AppCallLogBundle/AccessionList/accession-list.html.twig")
     */
    public function complexAccessionListAction(Request $request, $listid, $listname)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $calllogUtil = $this->get('calllog_util');
        //$securityUtil = $this->get('user_security_utility');
        //$user = $this->get('security.token_storage')->getToken()->getUser();

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

        $accessionListType = $calllogUtil->getCalllogAccessionListType();
        if( $accessionListType ) {
            $dql->leftJoin("list.accessionListTypes", "accessionListTypes");
            $dql->andWhere("accessionListTypes.id = :accessionListTypeId");
            $parameters['accessionListTypeId'] = $accessionListType->getId();
        }

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
//        $system = $securityUtil->getDefaultSourceSystem($this->getParameter('calllog.sitename'));
//        $newAccession = new Accession(true,$status,$user,$system);
        $accessionForm = $this->createAccessionForm();

        //src/App/CalllogBundle/Resources/views/AccessionList/accession-list.html.twig
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
     * @Route("/recent-accessions", name="calllog_recent_accessions")
     * @Template("AppCallLogBundle/AccessionList/recent-accessions.html.twig")
     */
    public function recentAccessionsAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        //$securityUtil = $this->get('user_security_utility');
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        //listing Accessions whose notes have been updated in the last 96 hours

        $parameters = array();

        $repository = $em->getRepository('AppOrderformBundle:Accession');
        $dql = $repository->createQueryBuilder("accession");

        $dql->leftJoin("accession.message", "message");
        $dql->leftJoin("message.editorInfos", "editorInfos");
        $dql->leftJoin("message.calllogEntryMessage", "calllogEntryMessage");
        $dql->leftJoin("calllogEntryMessage.calllogTasks", "calllogTasks");

        $dql->leftJoin("accession.procedure", "procedure");
        $dql->leftJoin("procedure.encounter", "encounter");
        $dql->leftJoin("encounter.patient", "patient");

        $dql->leftJoin("patient.lastname", "lastname");
        $dql->leftJoin("patient.firstname", "firstname");
        $dql->leftJoin("patient.mrn", "mrn");

        $dql->leftJoin("accession.accession", "accessionaccession");

        $dql->where("calllogEntryMessage.id IS NOT NULL");
        //$dql->andWhere("message.orderdate >= :hours96Ago OR editorInfos.modifiedOn >= :hours96Ago OR calllogTasks.statusUpdatedDate >= :hours96Ago");

        if(1) {
            $andWhere = "message.orderdate >= :hours96Ago OR editorInfos.modifiedOn >= :hours96Ago OR calllogTasks.statusUpdatedDate >= :hours96Ago";
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
                //'defaultSortFieldName' => 'accession.id',
                'defaultSortFieldName' => 'message.orderdate',
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
        //$calllogUtil = $this->get('calllog_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $sitename = $this->getParameter('calllog.sitename');

        $userTimeZone = $userSecUtil->getSiteSettingParameter('timezone',$sitename);

        $params = array(
            'cycle' => 'new',
            'user' => $user,
            'em' => $em,
            'container' => $this->container,
            'type' => null,
            'formtype' => 'calllog-entry',
            'complexLocation' => false,
            'alias' => false,
            'timezoneDefault' => $userTimeZone,
        );

        $form = $this->createForm(CalllogAccessionDummyType::class, null, array(
            'form_custom_value' => $params,
            //'form_custom_value_entity' => $patient
        ));

        return $form;
    }

    /**
     * Search Accession
     * @Route("/accession/search", name="calllog_search_accession", methods={"GET"}, options={"expose"=true})
     * @Template()
     */
    public function patientSearchAction(Request $request)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $accessionNumber = trim($request->get('accessionnumber'));
        $accessionType = trim($request->get('accessiontype'));

        $calllogUtil = $this->get('calllog_util');
        $accession = $calllogUtil->findExistingAccession($accessionNumber,$accessionType);

        $patientInfo = null;
        $accessionId = null;

        if( $accession ) {
            $patient = $accession->obtainPatient();
            if( $patient ) {
                $patientInfo = $patient->obtainPatientInfoSimple();
            }
            $accessionId = $accession->getId();
        }

        $resData = array();
        $resData['patientInfo'] = $patientInfo;
        $resData['accessionId'] = $accessionId;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($resData));
        return $response;
    }


    /**
     * @Route("/accession/remove-accession-from-list/{accessionId}/{accessionListId}", name="calllog_remove_accession_from_list")
     */
    public function removeAccessionFromListAction(Request $request, $accessionId, $accessionListId) {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER')) {
            return $this->redirect($this->generateUrl('calllog-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $accessionList = $em->getRepository('AppOrderformBundle:AccessionListHierarchy')->find($accessionListId);
        if( !$accessionList ) {
            throw new \Exception( "AccessionListHierarchy not found by id $accessionListId" );
        }

        //remove accession from the list
        $repository = $em->getRepository('AppOrderformBundle:AccessionListHierarchy');
        $dql = $repository->createQueryBuilder("list");

        $dql->leftJoin("list.accession", "accession");

        $dql->where("accession = :accessionId");
        $parameters['accessionId'] = $accessionId;

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        $accessions = $query->getResult();

        $msgArr = array();
        foreach( $accessions as $accessionNode ) {
            $accessionNode->setType('disabled');
            $accession = $accessionNode->getAccession();
            //TODO: remove this accession from all CalllogEntryMessage (addAccessionToList, accessionList): find all message with this accession where addAccessionToList is true and set to false?
            $msgArr[$accession->getId()] = $accession->obtainFullValidKeyName();
        }
        $em->flush();

        $msg = implode('<br>',$msgArr);
        if( $msg ) {
            $msg = "Removed accession:<br>" . $msg;
        }

        $this->get('session')->getFlashBag()->add(
            'pnotify',
            $msg
        );

        $listName = $accessionList->getName()."";
        $listNameLowerCase = str_replace(" ","-",$listName);
        $listNameLowerCase = strtolower($listNameLowerCase);

        return $this->redirect($this->generateUrl('calllog_accession_list',array('listname'=>$listNameLowerCase,'listid'=>$accessionListId)));
    }



    /**
     * @Route("/accession/add-accession-to-list/{accessionListId}/{accessionId}", name="calllog_add_accession_to_list")
     * @Route("/accession/add-accession-to-list-ajax/{accessionListId}/{accessionId}", name="calllog_add_accession_to_list_ajax", options={"expose"=true})
     *
     * @Template("AppCallLogBundle/AccessionList/accession-list.html.twig")
     */
    public function addAccessionToListAction(Request $request, $accessionListId, $accessionId) {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
        }

        $scanorderUtil = $this->container->get('scanorder_utility');
        $calllogUtil = $this->get('calllog_util');
        $em = $this->getDoctrine()->getManager();

        $accessionList = $em->getRepository('AppOrderformBundle:AccessionListHierarchy')->find($accessionListId);
        if( !$accessionList ) {
            throw new \Exception( "AccessionListHierarchy not found by id $accessionListId" );
        }

        //add accession from the list
        $accession = $em->getRepository('AppOrderformBundle:Accession')->find($accessionId);
        if( !$accession ) {
            throw new \Exception( "Accession not found by id $accessionId" );
        }

        //exit("before adding accession");
        $accessionListType = $calllogUtil->getCalllogAccessionListType();
        $newListElement = $scanorderUtil->addAccessionToAccessionList($accession,$accessionList,$accessionListType);

        if( $newListElement ) {
            //Accession added to the Pathology Calllog Accession list
            $msg = "Accession " . $newListElement->getAccession()->obtainFullValidKeyName() . " has been added to the " . $accessionList->getName() . " list";
            $pnotify = 'pnotify';
        } else {
            $msg = "Accession " . $accession->obtainFullValidKeyName() . " HAS NOT BEEN ADDED to the " . $accessionList->getName() . " list. Probably, this accession already exists in this list.";
            $pnotify = 'pnotify-error';
        }

        $this->get('session')->getFlashBag()->add(
            $pnotify,
            $msg
        );

        //return OK
        if( $request->get('_route') == "calllog_add_accession_to_list_ajax" ) {
            $res = "OK";
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($res));
            return $response;
        }

        $listName = $accessionList->getName()."";
        $listNameLowerCase = str_replace(" ","-",$listName);
        $listNameLowerCase = strtolower($listNameLowerCase);

        return $this->redirect($this->generateUrl('calllog_accession_list',array('listname'=>$listNameLowerCase,'listid'=>$accessionListId)));
    }
    
}