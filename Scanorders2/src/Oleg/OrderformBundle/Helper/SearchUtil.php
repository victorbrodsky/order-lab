<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 4/10/15
 * Time: 11:20 AM
 */

namespace Oleg\OrderformBundle\Helper;


class SearchUtil {

    private $em;
    private $container;
    private $sc;

    public function __construct( $em, $container, $sc ) {
        $this->em = $em;
        $this->container = $container;
        $this->sc = $sc;
    }



    public function searchAction($params) {

        $request = ( array_key_exists('request', $params) ? $params['request'] : null);
        $object = ( array_key_exists('object', $params) ? $params['object'] : null);
        $searchtype = ( array_key_exists('searchtype', $params) ? $params['searchtype'] : null);
        $search = ( array_key_exists('search', $params) ? $params['search'] : null);
        $exactmatch = ( array_key_exists('exactmatch', $params) ? $params['exactmatch'] : false);

        $securityUtil = $this->container->get('order_security_utility');
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

        $repository = $this->em->getRepository('OlegOrderformBundle:'.$object);
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
                $criteriastr .= "(";
                $criteriastr .= $this->getSearchStr('lastname.field',$search,$exactmatch);
                $criteriastr .= " OR " . $this->getSearchStr('firstname.field',$search,$exactmatch);
                $criteriastr .= " OR " . $this->getSearchStr('middlename.field',$search,$exactmatch);
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
        $user = $this->sc->getToken()->getUser();
        $orderUtil = $this->container->get('scanorder_utility');
        $criteriastr = $orderUtil->addInstitutionQueryCriterion($user,$criteriastr,array("Union"));

        if( $criteriastr ) {
            //echo "criteriastr=".$criteriastr."<br>";
            $dql->where($criteriastr);
        }

        if( $sort ) {
            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
        } else {
            $dql->orderBy($object.".id","DESC");
        }

        //echo "dql=".$dql."<br>";

        $query = $this->em->createQuery($dql);    //->setParameter('now', date("Y-m-d", time()));


        $limitFlag = true;
        $pageNumber = $request->query->get('page', 1);
        //echo "page=".$pageNumber."<br>";

        if( $limitFlag ) {
            $limit = 10;
            $paginator  = $this->container->get('knp_paginator');
            $pagination = $paginator->paginate(
                $query,
                $pageNumber,                /*page number*/
                $limit                      /*limit per page*/
            );
        } else {
            $pagination = $query->getResult();
        }

//        echo "pagination count=".count($pagination)."<br>";
//        foreach( $pagination as $item ) {
//            echo $object." ID=".$item->getId().", inst=".$item->getInstitution()."<br>";
//        }

        $returnArr[$object] = $pagination;

        return $returnArr;
    }

    public function getSearchStr($field,$search,$exactmatch=false) {

        $securityUtil = $this->container->get('order_security_utility');
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