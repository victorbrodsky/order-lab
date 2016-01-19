<?php

//To initialize this bundle make sure:
//1) add a new source to SourceSystemList "Deidentifier"
//2) add a new AccessionType "Deidentifier ID"
//3) add new roles by running "Populate All Lists With Default Values" in user directory list manager

namespace Oleg\DeidentifierBundle\Controller;

use Oleg\DeidentifierBundle\Form\DeidentifierSearchType;
use Oleg\OrderformBundle\Entity\AccessionAccession;
use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="deidentifier_home")
     * @Template("OlegDeidentifierBundle:Default:index.html.twig")
     * @Method("GET")
     */
    public function indexAction( Request $request ) {

        if( false == $this->get('security.context')->isGranted('ROLE_DEIDENTIFICATOR_USER') ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //$user = $this->get('security.context')->getToken()->getUser();

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();


//        //permittedInstitutions for generation
//        $securityUtil = $this->get('order_security_utility');
//        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
//        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
//        $orderUtil = $this->get('scanorder_utility');
//        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,null);
//        $params = array(
//            'permittedInstitutions' => $permittedInstitutions,
//        );
//
//        //search box
//        $form = $this->createForm(new DeidentifierSearchType($params), null);

        $form = $this->createSearchForm();

//        //get search string
//        $form->bind($request);  //use bind instead of handleRequest. handleRequest does not get filter data
//        $accessionNumber = $form->get('accessionNumber')->getData();
//        $accessionType = $form->get('accessionType')->getData();
//
//        //echo "accessionNumber=".$accessionNumber."<br>";
//        //echo "accessionType=".$accessionType."<br>";
//
//        $error = null;
//        $pagination = null;
//
//        if( $form->get('generate')->isClicked() ) {
//
//            if( !$accessionNumber ) {
//                $error = new FormError("Please specify Accession Number");
//                $form->get('accessionNumber')->addError($error);
//            }
//
//            if( !$accessionType ) {
//                $error = new FormError("Please specify Accession Type");
//                $form->get('accessionType')->addError($error);
//            }
//
//            //exit('new generate!');
//
//        }
//
//        /////////////////////// Generate ///////////////////////
//        if( !$error && $form->get('generate')->isClicked() ) {
//
//            $single = true;
//
//            $extra = array();
//            $extra["keytype"] = $accessionType->getId();
//
//            $validity = array('valid','deidentifier-valid','deidentifier');
//
//            $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
//            $institutions = array();
//            $institutions[] = $wcmc->getId();
//
//            $accession = $em->getRepository('OlegOrderformBundle:Accession')->findOneByIdJoinedToField($institutions,$accessionNumber,"Accession","accession",$validity,$single,$extra);
//
//            if( !$accession ) {
//
//                if( !$accessionNumber ) {
//                    //exit("No accessionNumber=".$accessionNumber);
//                    throw $this->createNotFoundException("Generate a new deidentifier: No accession number is provided. accessionNumber=".$accessionNumber);
//                }
//
//                //create a new accession object
//                //$status, $provider, $className, $fieldName, $parent = null, $fieldValue = null, $extra = null, $withfields = true, $flush=true
//                $accession = $em->getRepository('OlegOrderformBundle:Accession')->createElement(
//                    $wcmc->getId(),     //institution
//                    "valid",            //status. if null => STATUS_RESERVED
//                    $user,              //provider
//                    "Accession",        //$className
//                    "accession",        //$fieldName
//                    null,               //$parent
//                    $accessionNumber,   //$fieldValue
//                    $extra,             //$extra
//                    false               //$withfields
//                );
//
//                if( !$accession ) {
//                    throw $this->createNotFoundException('Unable to create a new Accession with Accession Number='.$accessionNumber);
//                }
//
//                //set source
//                $source = $em->getRepository('OlegUserdirectoryBundle:SourceSystemList')->findOneByName("Deidentifier");
//                if( !$source ) {
//                    throw $this->createNotFoundException('Unable to find Deidentifier in SourceSystemList by name='."Deidentifier");
//                }
//                $accession->setSource($source);
//
//                $em->persist($accession);
//                $em->flush($accession);
//
//            }
//
//            $deidentifier = $this->getNewDeidentificator($accession->getId());
//
//            if( !$deidentifier ) {
//                throw $this->createNotFoundException('Unable to calculate a new Deidentifier Number.');
//            }
//
//            $accession = $this->addNewDeidentifier($accession->getId(),$deidentifier);
//
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                'New generated Deidentifier Number: <strong>' . $deidentifier . '</strong>' . '<br>'.
//                'New generated Accession <strong>' . $accession->obtainFullObjectName() . '</strong>'
//            );
//
//            $pathParams = $this->getPathParams($accession);
//            return $this->redirect( $this->generateUrl('deidentifier_home',$pathParams) );
//        }
//        /////////////////////// EOF Generate ///////////////////////

        $accessionTypes = $em->getRepository('OlegOrderformBundle:AccessionType')->findBy( array('type'=>array('default','user-added')) );

        return array(
            'accessiontypes' => $accessionTypes,
            'accessreqs' => count($accessreqs),
            'form' => $form->createView(),
            //'msg' => "test test test test"
        );
    }

    public function createSearchForm() {
        //permittedInstitutions for generation
        $user = $this->get('security.context')->getToken()->getUser();
        $securityUtil = $this->get('order_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        $orderUtil = $this->get('scanorder_utility');
        $permittedInstitutions = $orderUtil->getAllScopeInstitutions($permittedInstitutions,null);
        $params = array(
            'permittedInstitutions' => $permittedInstitutions,
        );

        //search box
        $form = $this->createForm(new DeidentifierSearchType($params), null);

        return $form;
    }

//    public function getAccessionTypesAction() {
//        $em = $this->getDoctrine()->getManager();
//        $accessionTypes = $em->getRepository('OlegOrderformBundle:AccessionType')->findBy( array('type'=>array('default','user-added')) );
//
//        $accessionTypeArr = array();
//        foreach( $accessionTypes as $accessionType) {
//            $accessionTypeObject = array('id'=>$accessionType->getId(),'text'=>$accessionType."");
//            $accessionTypeArr[] = $accessionTypeObject;
//        }
//
//        //return $accessionTypes;
//
//        $response = new Response();
//        $response->setContent($accessionTypeArr);
//        return $response;
//    }


    /**
     * @Route("/re-identify/", name="deidentifier_search")
     * @Template("OlegDeidentifierBundle:Search:search.html.twig")
     * @Method("GET")
     */
    public function searchAction( Request $request ) {

        if( false == $this->get('security.context')->isGranted('ROLE_DEIDENTIFICATOR_USER') ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        //get search string
        $accessionNumber = $request->query->get('accessionNumber');
        $accessionType = $request->query->get('accessionType');

        //echo "accessionNumber=".$accessionNumber."<br>";
        //echo "accessionType=".$accessionType."<br>";

        $error = null;
        $pagination = null;

        //Search across all institutions that are listed in PHI Scope of the user by default
        $securityUtil = $this->get('order_security_utility');
        $userSiteSettings = $securityUtil->getUserPerSiteSettings($user);
        $permittedInstitutions = $userSiteSettings->getPermittedInstitutionalPHIScope();
        $institutionIds = array();
        foreach( $permittedInstitutions as $permittedInstitution ) {
            $institutionIds[] = $permittedInstitution->getId();
        }
        $query = $this->getAccessionQuery($accessionType,$accessionNumber,$institutionIds,$request);
        //echo "sql=".$query->getSql()."<br>";

        //$pagination = $query->getResult(); //accessions
        //echo "pagination count=" . count($pagination) . "<br>";
        //exit();

        if( $query ) {

            $limit = 20;
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $query,
                $this->get('request')->query->get('page', 1),   /*page number*/
                $limit,                                         /*limit per page*/
                array('defaultSortFieldName' => 'accessionAccession.id', 'defaultSortDirection' => 'asc')
            );

            //echo "pagination count=" . count($pagination) . "<br>";
        }


        $accessionTypes = $em->getRepository('OlegOrderformBundle:AccessionType')->findBy( array('type'=>array('default','user-added')) );

        $accessionTypeObj = $em->getRepository('OlegOrderformBundle:AccessionType')->find($accessionType);

        return array(
            'accessionTypeId' => $accessionType,
            'accessionTypeStr' => $accessionTypeObj."",
            'accessionNumber' => $accessionNumber,
            'accessiontypes' => $accessionTypes,
            'pagination' => $pagination //accessions
        );
    }

    public function searchAccession($accessionTypeId,$accessionNumber,$institutions,$single=false) {
        $em = $this->getDoctrine()->getManager();

        $extra = array();
        $extra["keytype"] = $accessionTypeId;

        $validity = array('valid','deidentifier-valid','deidentifier');

        //$institutions = array();
        //$institutions[] = $inst->getId();

        //findOneByIdJoinedToField already include collaboration based on the provided permitted $institutions
        $accessions = $em->getRepository('OlegOrderformBundle:Accession')->findOneByIdJoinedToField($institutions,$accessionNumber,"Accession","accession",$validity,$single,$extra);

        return $accessions;
    }





    /**
     * @Route("/generate/", name="deidentifier_generate")
     * @Template("OlegDeidentifierBundle:Default:index.html.twig")
     * @Method("GET")
     */
    public function generateAction( Request $request ) {

        if( false == $this->get('security.context')->isGranted('ROLE_DEIDENTIFICATOR_USER') ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        //get search string
        $institution = $request->query->get('institution');
        $accessionNumber = $request->query->get('accessionNumber');
        $accessionTypeId = $request->query->get('accessionType');

        //echo "institution=".$institution."<br>";
        //echo "accessionNumber=".$accessionNumber."<br>";
        //echo "accessionType=".$accessionTypeId."<br>";
        //exit();

        if( !$accessionNumber ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Accession Number is not provided.'
            );
            return $this->redirect( $this->generateUrl('deidentifier_home') );
        }

        if( !$accessionTypeId ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Accession Type is not provided.'
            );
            return $this->redirect( $this->generateUrl('deidentifier_home') );
        }

        if( !$institution ) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                'Institution is not provided.'
            );
            return $this->redirect( $this->generateUrl('deidentifier_home') );
        }

        $institutions = array($institution);

        $accession = $this->searchAccession($accessionTypeId,$accessionNumber,$institutions,true);

        $msg = '';
        if( !$accession ) {
            $accession = $this->createNewAccession($accessionTypeId,$accessionNumber,$institution);
            $msg = 'New generated Accession <strong>' . $accession->obtainFullObjectName() . '</strong>';
        }

        $accessionId = $accession->getId();

        echo "generateAction: accessionId=".$accessionId."<br>";

        //get a new deidentifier number

        $deidentifier = $this->getNewDeidentificator($accessionId);
        //exit('new deidentifier='.$deidentifier);

        $accession = $this->addNewDeidentifier($accessionId,$deidentifier);

        $msg = '<strong>' . $deidentifier . '</strong>' . ' generated for ' . $accession->obtainFullValidKeyName() . $msg;

//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            $msg
//        );

        //$pathParams = $this->getPathParams($accession);
        //return $this->redirect( $this->generateUrl('deidentifier_home',$pathParams) );

        $form = $this->createSearchForm();
        $accessionTypes = $em->getRepository('OlegOrderformBundle:AccessionType')->findBy( array('type'=>array('default','user-added')) );

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        return array(
            //'permittedInstitutions' => $permittedInstitutions,
            'accessiontypes' => $accessionTypes,
            'accessreqs' => count($accessreqs),
            'form' => $form->createView(),
            'msg' => $msg
            //'pagination' => $pagination //accessions
        );
    }

    private function createNewAccession($accessionType,$accessionNumber,$institution) {

        if( !$accessionNumber ) {
            throw $this->createNotFoundException("Generate a new deidentifier: No accession number is provided. accessionNumber=".$accessionNumber);
        }

        if( !$accessionType ) {
            throw $this->createNotFoundException("Generate a new deidentifier: No accession type is provided. accessionType=".$accessionType);
        }

        if( !$institution ) {
            throw $this->createNotFoundException("Generate a new deidentifier: No institution is provided. institution=".$institution);
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        //parameters
        $extra = array();
        $extra["keytype"] = $accessionType;

        //create a new accession object
        //$status, $provider, $className, $fieldName, $parent = null, $fieldValue = null, $extra = null, $withfields = true, $flush=true
        $accession = $em->getRepository('OlegOrderformBundle:Accession')->createElement(
            $institution,       //institution
            "valid",            //status. if null => STATUS_RESERVED
            $user,              //provider
            "Accession",        //$className
            "accession",        //$fieldName
            null,               //$parent
            $accessionNumber,   //$fieldValue
            $extra,             //$extra
            false               //$withfields
        );

        if( !$accession ) {
            throw $this->createNotFoundException('Unable to create a new Accession with Accession Number='.$accessionNumber);
        }

        //set source
        $source = $em->getRepository('OlegUserdirectoryBundle:SourceSystemList')->findOneByName("Deidentifier");
        if( !$source ) {
            throw $this->createNotFoundException('Unable to find Deidentifier in SourceSystemList by name='."Deidentifier");
        }
        $accession->setSource($source);

        $em->persist($accession);
        $em->flush($accession);

        return $accession;
    }

    public function getPathParams($accession) {
        $pathParams = array();
        if( $accession ) {
            $key = $accession->obtainValidKeyfield();
            if( $key ) {
                $accessionType = $key->getKeytype()->getId();
                $accessionNumber = $key->getField();

                $pathParams = array(
                    'accessionType' => $accessionType,
                    'accessionNumber' => $accessionNumber
                );

            } else {
                throw $this->createNotFoundException('Unable to find a valid Accession Number.');
            }
            //exit('accessionNumber='.$accessionNumber);
        }
        return $pathParams;
    }

    public function getAccessionQuery($accessionTypeId,$accessionNumber,$institutions,$request) {
        $em = $this->getDoctrine()->getManager();

        //first get accession
        $accessions = $this->searchAccession($accessionTypeId,$accessionNumber,$institutions,false);
        if( !$accessions || count($accessions) == 0 ) {
            return null;
            //exit("accession is not found; accessionType=" . $accessionTypeId . ", accessionNumber=" . $accessionNumber . ", institution=" . $institution);
        }
        //echo "accession count=".count($accessions)."<br>";

        $repository = $em->getRepository('OlegOrderformBundle:AccessionAccession');
        $dql =  $repository->createQueryBuilder("accessionAccession");
        $dql->select('accessionAccession');
        $dql->leftJoin("accessionAccession.accession", "accession");
        $dql->leftJoin("accession.institution", "institution");
        $dql->leftJoin("accessionAccession.keytype", "keytype");

        //$dql->where("accession = :accession"); // AND keytype.id = :accessionType

        $accessionIdArr = array();
        foreach( $accessions as $accession ) {
            $accessionIdArr[] = "accession = " . $accession->getId();
        }
        $accessionIdStr = implode(" OR ", $accessionIdArr);
        $dql->where($accessionIdStr);

        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
        $postData = $request->query->all();
        if( isset($postData['sort']) ) {
            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
        }

        $query = $em->createQuery($dql);

//        $query->setParameters( array(
//                'accession' => $accession->getId(),
//                //'accessionNumber' => '%'.$accessionNumber.'%',
//                //'accessionType' => $accessionType->getId()
//            )
//        );

        //echo "sql=".$query->getSql()."<br>";

        return $query;
    }

//    public function get_AccessionQuery_ORIG_ACCESSION($accessionType,$accessionNumber,$institution,$request) {
//        $em = $this->getDoctrine()->getManager();
//        $repository = $em->getRepository('OlegOrderformBundle:Accession');
//        $dql =  $repository->createQueryBuilder("accession");
//        $dql->select('accession');
//        $dql->leftJoin("accession.accession", "accessionAccession");
//        $dql->leftJoin("accessionAccession.keytype", "keytype");
//
//        $dql->where("accessionAccession.field = :accessionNumber AND keytype.id = :accessionType AND accession.institution = :institution");
//
//        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
//        $postData = $request->query->all();
//        if( isset($postData['sort']) ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }
//
//        $query = $em->createQuery($dql);
//
//        $query->setParameters( array(
//                'accessionNumber' => $accessionNumber,
//                //'accessionNumber' => '%'.$accessionNumber.'%',
//                'accessionType' => $accessionType->getId(),
//                'institution' => $institution->getId()
//            )
//        );
//
//        //echo "sql=".$query->getSql()."<br>";
//
//        return $query;
//    }

    //get a new deidentifier number
    public function getNewDeidentificator($accessionId) {

        if( !$this->get('security.context')->isGranted('ROLE_DEIDENTIFICATOR_ADMIN') ) {
            return null;
        }

        $defaultDeidentifierPrefix = "DID";
        $defaultDeidentifier = $defaultDeidentifierPrefix."-1";

        if( !$accessionId ) {
            return $defaultDeidentifier;
        }

        $em = $this->getDoctrine()->getManager();

        $deidentifier = null;

        $deidentifierType = $em->getRepository('OlegOrderformBundle:AccessionType')->findOneByName("Deidentifier ID");
        if( !$deidentifierType ) {
            throw $this->createNotFoundException('Unable to find Deidentifier ID AccessionType entity.');
        }

        /////////////////////// get maxDeidentifier /////////////////////////
        $repository = $em->getRepository('OlegOrderformBundle:AccessionAccession');
        $dql =  $repository->createQueryBuilder("accessionAccession");

        //use something like: SELECT MAX(CAST(SUBSTRING(invoice_number, 4, length(invoice_number)-3) AS UNSIGNED))
        //$dql->select('MAX(CAST(accessionAccession.original AS UNSIGNED)) as maxDeidentifier'); //working correct with cast and original field
        //DID-10 => start at index 5
        //UNSIGNED is not defined in SQL server version used in Aperio => use INTEGER
        $castAs = "INTEGER";
        if( $this->getParameter('database_driver') == 'pdo_mysql' ) {
            $castAs = "UNSIGNED";
        }
        $dql->select('MAX(CAST(SUBSTRING(accessionAccession.field, 5) AS '.$castAs.')) as maxDeidentifier');

        //$dql->where("accessionAccession.accession = :accessionId AND accessionAccession.keytype = :accessionType");
        $dql->where("accessionAccession.keytype = :accessionType");
        //$dql->orderBy("fellapp.interviewScore","ASC");

        $query = $em->createQuery($dql);

        $query->setParameters( array(
                //'accessionId' => $accessionId,
                'accessionType' => $deidentifierType->getId()
            )
        );
        $accessionAccessions = $query->getResult();
        //echo "accessionAccessions count=".count($accessionAccessions)."<br>";
        ////////////////////////////////////////////////

        if( count($accessionAccessions) == 1 ) {
            $accessionAccession = $accessionAccessions[0];

//            echo "accessionAccession:<br>";
//            print_r($accessionAccession);
//            echo "<br>";

            $maxDeidentifier = $accessionAccession['maxDeidentifier'];
            //echo "maxDeidentifier=".$maxDeidentifier."<br>";

            if( !$maxDeidentifier ) {
                $deidentifier = $defaultDeidentifier;
            } else {
                //echo "maxDeidentifier=".$maxDeidentifier."<br>";
                $maxDeidentifierInt = intval($maxDeidentifier);
                $deidentifierIntNext = $maxDeidentifierInt + 1;
                $deidentifier = $defaultDeidentifierPrefix."-".$deidentifierIntNext;
            }

        } else {
            throw $this->createNotFoundException('Unable to find a single Accession entity.');
        }

        //exit('deidentifier='.$deidentifier);
        return $deidentifier;
    }

    public function addNewDeidentifier( $accessionId, $deidentifier ) {

        if( !$accessionId ) {
            throw $this->createNotFoundException('Accession ID is not provided.');
        }

        if( !$deidentifier ) {
            throw $this->createNotFoundException('Deidentifier Number is not provided.');
        }

        $em = $this->getDoctrine()->getManager();
        $accession = $em->getRepository('OlegOrderformBundle:Accession')->find($accessionId);

        if( !$accession ) {
            throw $this->createNotFoundException('Accession is not found by ID ' . $accessionId);
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $status = 'deidentified-valid';
        //$source = null; //SourceSystemList
        $source = $em->getRepository('OlegUserdirectoryBundle:SourceSystemList')->findOneByName("Deidentifier");
        if( !$source ) {
            throw $this->createNotFoundException('Unable to find Deidentifier in SourceSystemList by name='."Deidentifier");
        }

        $accessionAccession = new AccessionAccession($status,$user,$source);

        $deidentifierType = $em->getRepository('OlegOrderformBundle:AccessionType')->findOneByName("Deidentifier ID");
        if( !$deidentifierType ) {
            throw $this->createNotFoundException('Unable to find Deidentifier ID AccessionType entity.');
        }
        $accessionAccession->setKeytype($deidentifierType);

        $accessionAccession->setField($deidentifier);

        //get original number
        $pieces = explode("-", $deidentifier);
        $maxDeidentifierStr = $pieces[1];
        $maxDeidentifierInt = intval($maxDeidentifierStr);
        //echo "maxDeidentifierInt=".$maxDeidentifierInt."<br>";

        $accessionAccession->setOriginal($maxDeidentifierInt);

        $accession->addAccession( $accessionAccession );

        $em->flush($accession);

        return $accession;
    }

    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->get('security.context')->isGranted('ROLE_DEIDENTIFICATOR_ADMIN') ) {
            return null;
        }
        $userSecUtil = $this->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->container->getParameter('deidentifier.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
    }
}
