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
 * Time: 11:20 AM
 */

namespace App\OrderformBundle\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchUtil {

    private $em;
    private $container;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {
        $this->em = $em;
        $this->container = $container;
    }



    public function searchAction($params) {

        $request = ( array_key_exists('request', $params) ? $params['request'] : null);
        $object = ( array_key_exists('object', $params) ? $params['object'] : null);
        $searchtype = ( array_key_exists('searchtype', $params) ? $params['searchtype'] : null);
        $search = ( array_key_exists('search', $params) ? $params['search'] : null);
        $exactmatch = ( array_key_exists('exactmatch', $params) ? $params['exactmatch'] : false);

        $securityUtil = $this->container->get('user_security_utility');
        $search = $securityUtil->mysql_escape_mimic($search);

        $returnArr = array();

        if( $object ) {
            $returnArr[$object] = null;
        }

        if( !$request ) {
            return $returnArr;
        }

        if( $searchtype && !$search ) {
            return $returnArr;
        }


        //sorting
        $postData = $request->query->all();
        $sort = null;
        if( isset($postData['sort']) ) {
            $sort = $postData['sort'];
        }

        $repository = $this->em->getRepository('AppOrderformBundle:'.$object);
        $dql =  $repository->createQueryBuilder($object);
        $dql->select($object);
        $dql->leftJoin($object.".institution", "institution");

        $criteriastr = "";

        switch( $searchtype ) {
            case 'MRN':
                $dql->leftJoin("patient.mrn", "mrn");
                //$dql->leftJoin("patient.institution", "institution");
                $criteriastr = $this->getSearchStr('mrn.field',$search,$exactmatch);
                break;
            case 'Patient Name':
                //echo "searchtype=".$searchtype."<br>";
                //$dql->leftJoin("patient.institution", "institution");
                $dql->leftJoin("patient.lastname", "lastname");
                $dql->leftJoin("patient.firstname", "firstname");
                $dql->leftJoin("patient.middlename", "middlename");

                $dql->leftJoin("patient.encounter", "encounter");
                $dql->leftJoin("encounter.patlastname", "patlastname");
                $dql->leftJoin("encounter.patfirstname", "patfirstname");
                $dql->leftJoin("encounter.patmiddlename", "patmiddlename");

                $criteriastr .= "(";

                $criteriastr .= $this->getSearchStr('lastname.field',$search,$exactmatch);
                $criteriastr .= " OR " . $this->getSearchStr('firstname.field',$search,$exactmatch);
                $criteriastr .= " OR " . $this->getSearchStr('middlename.field',$search,$exactmatch);

                $criteriastr .= " OR " . $this->getSearchStr('patlastname.field',$search,$exactmatch);
                $criteriastr .= " OR " . $this->getSearchStr('patfirstname.field',$search,$exactmatch);
                $criteriastr .= " OR " . $this->getSearchStr('patmiddlename.field',$search,$exactmatch);

                $criteriastr .= ")";
                break;
            case 'Accession Number':
                //echo "i equals 0";
                break;
            case 'Organ Source for Part':
            case 'Neoplastic Tumor Source':
            case 'Part Type':
                //echo $searchtype;
                break;
            case 'Procedure Type':
                //echo $searchtype;
                break;
            default:
                $criteriastr = "";
        }

        //check for institution and collaboration (only union)
        //$user = $this->container->get('security.token_storage')->getToken()->getUser();
        //$orderUtil = $this->container->get('scanorder_utility');
        //$criteriastr = $orderUtil->addInstitutionQueryCriterion($user,$criteriastr,array("Union"));

        if( $criteriastr ) {
            //echo "criteriastr=".$criteriastr."<br>";
            $dql->where($criteriastr);
        }

        if( $sort ) {
            //$dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
        } else {
            $dql->orderBy($object.".id","DESC");
        }

        //echo "dql=".$dql."<br>";

        $query = $this->em->createQuery($dql);    //->setParameter('now', date("Y-m-d", time()));


        $limitFlag = true;
        $pageNumber = $request->query->get('page', 1);
        //echo "page=".$pageNumber."<br>";

        if( $limitFlag ) {
            $limit = 20;
            $paginator  = $this->container->get('knp_paginator');
            $pagination = $paginator->paginate(
                $query,
                $pageNumber,                /*page number*/
                $limit                      /*limit per page*/
            );
        } else {
            $pagination = $query->getResult();
        }

        //echo "pagination count=".count($pagination)."<br>";
//        foreach( $pagination as $item ) {
//            echo $object." ID=".$item->getId().", inst=".$item->getInstitution()."<br>";
//        }

        $returnArr[$object] = $pagination;

        return $returnArr;
    }

    public function getSearchStr($field,$search,$exactmatch=false) {

        $securityUtil = $this->container->get('user_security_utility');
        $search = $securityUtil->mysql_escape_mimic($search);

        $prefix = " '";
        $postfix = "' ";
        $equal = '=';

        if( !$exactmatch ) {
            $prefix = $prefix . "%";
            $postfix = "%" . $postfix;
            $equal = ' LIKE ';
        }

        $searchStr = $field . $equal . $prefix . $search . $postfix;
        //echo "searchStr=".$searchStr."<br>";

        return $searchStr;
    }

} 