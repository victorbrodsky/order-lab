<?php

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

        $user = $this->get('security.context')->getToken()->getUser();

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        //search box
        $form = $this->createForm(new DeidentifierSearchType(), null);

        //get search string
        $form->bind($request);  //use bind instead of handleRequest. handleRequest does not get filter data
        $accessionNumber = $form->get('accessionNumber')->getData();
        $accessionType = $form->get('accessionType')->getData();

        //echo "accessionNumber=".$accessionNumber."<br>";
        //echo "accessionType=".$accessionType."<br>";

        $error = null;
        $pagination = null;

        if( $form->get('search')->isClicked() || $form->get('generate')->isClicked() ) {

            if( !$accessionNumber ) {
                $error = new FormError("Please specify Accession Number");
                $form->get('accessionNumber')->addError($error);
            }

            if( !$accessionType ) {
                $error = new FormError("Please specify Accession Type");
                $form->get('accessionType')->addError($error);
            }

            //exit('new generate!');

        }

        if( !$error && ($form->get('search')->isClicked() || ($accessionNumber && $accessionType) ) ) {

            //get accession numbers by number and type

//            $accessions = $em->getRepository('OlegOrderformBundle:AccessionAccession')->findBy(
//                array('field' => $accessionNumber, 'keytype' => $accessionType),
//                array('creationdate' => 'DESC')
//            );

            $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");

            //$pagination = $this->searchAccession($accessionType,$accessionNumber,$wcmc);

            $query = $this->getAccessionQuery($accessionType,$accessionNumber,$wcmc);
            //echo "sql=".$query->getSql()."<br>";
            //$pagination = $query->getResult(); //accessions

            $limit = 20;
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $query,
                $this->get('request')->query->get('page', 1),   /*page number*/
                $limit                                          /*limit per page*/
            );

            //echo "pagination count=" . count($pagination) . "<br>";
        }

        if( !$error && $form->get('generate')->isClicked() ) {

            $single = true;

            $extra = array();
            $extra["keytype"] = $accessionType->getId();

            $validity = array('valid');

            $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
            $institutions = array();
            $institutions[] = $wcmc->getId();

            $accession = $em->getRepository('OlegOrderformBundle:Accession')->findOneByIdJoinedToField($institutions,$accessionNumber,"Accession","accession",$validity,$single,$extra);

            if( !$accession ) {

                if( !$accessionNumber ) {
                    //exit("No accessionNumber=".$accessionNumber);
                    throw $this->createNotFoundException("Generate a new deidentifier: No accession number is provided. accessionNumber=".$accessionNumber);
                }

                //create a new accession object
                $accession = $this->
                //$status, $provider, $className, $fieldName, $parent = null, $fieldValue = null, $extra = null, $withfields = true, $flush=true
                    $accession = $em->getRepository('OlegOrderformBundle:Accession')->createElement(
                        $wcmc->getId(),     //institution
                        "valid",            //status. if null => STATUS_RESERVED
                        $user,              //provider
                        "Accession",        //$className
                        "accession",        //$fieldName
                        null,               //$parent
                        $accessionNumber,   //$fieldValue
                        $extra,             //$extra
                        false               //$withfields
                    );

            }

            $deidentifier = $this->getNewDeidentificator($accession->getId());

            if( !$deidentifier ) {
                throw $this->createNotFoundException('Unable to calculate a new Deidentifier Number.');
            }

            $accession = $this->addNewDeidentifier($accession->getId(),$deidentifier);

            $pathParams = $this->getPathParams($accession);
            return $this->redirect( $this->generateUrl('deidentifier_home',$pathParams) );

            //$pagination = $this->searchAccession($accessionType,$accessionNumber,$wcmc);
            //$pagination = array($accession);

//            $query = $this->getAccessionQuery($accessionType,$accessionNumber,$wcmc);
//            //echo "sql=".$query->getSql()."<br>";
//            $limit = 20;
//            $paginator  = $this->get('knp_paginator');
//            $pagination = $paginator->paginate(
//                $query,
//                $this->get('request')->query->get('page', 1),   /*page number*/
//                $limit                                          /*limit per page*/
//            );
        }

        return array(
            'accessreqs' => count($accessreqs),
            'form' => $form->createView(),
            'pagination' => $pagination //accessions
        );
    }

    public function searchAccession($accessionType,$accessionNumber,$inst) {
        $em = $this->getDoctrine()->getManager();

        $single = false;

        $extra = array();
        $extra["keytype"] = $accessionType->getId();

        $validity = array('valid');

        $institutions = array();
        $institutions[] = $inst->getId();

        $accessions = $em->getRepository('OlegOrderformBundle:Accession')->findOneByIdJoinedToField($institutions,$accessionNumber,"Accession","accession",$validity,$single,$extra);

        return $accessions;
    }


    /**
     * @Route("/generate/{accessionId}", name="deidentifier_generate")
     * @Template("OlegDeidentifierBundle:Default:index.html.twig")
     * @Method("GET")
     */
    public function generateAction( Request $request, $accessionId ) {

        if( false == $this->get('security.context')->isGranted('ROLE_DEIDENTIFICATOR_USER') ){
            return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        }

        //echo "generateAction: accessionId=".$accessionId."<br>";

        //get a new deidentifier number

        $deidentifier = $this->getNewDeidentificator($accessionId);
        //exit('new deidentifier='.$deidentifier);

        $accession = $this->addNewDeidentifier($accessionId,$deidentifier);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'New generated Deidentifier Number: <strong>' . $deidentifier . '</strong>'
        );

        $pathParams = $this->getPathParams($accession);
        return $this->redirect( $this->generateUrl('deidentifier_home',$pathParams) );
    }

    public function getPathParams($accession) {
        $pathParams = array();
        if( $accession ) {
            $key = $accession->obtainValidKeyfield();
            if( $key ) {
                $accessionType = $key->getKeytype()->getId();
                $accessionNumber = $key->getField();

                $pathParams = array(
                    'deidentifier_search_box[accessionType]' => $accessionType,
                    'deidentifier_search_box[accessionNumber]' => $accessionNumber
                );

            } else {
                throw $this->createNotFoundException('Unable to find a valid Accession Number.');
            }
            //exit('accessionNumber='.$accessionNumber);
        }
        return $pathParams;
    }

    public function getAccessionQuery($accessionType,$accessionNumber,$institution) {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('OlegOrderformBundle:Accession');
        $dql =  $repository->createQueryBuilder("accession");
        $dql->select('accession');
        $dql->leftJoin("accession.accession", "accessionAccession");
        $dql->leftJoin("accessionAccession.keytype", "keytype");

        $dql->where("accessionAccession.field = :accessionNumber AND keytype.id = :accessionType AND accession.institution = :institution");

        $query = $em->createQuery($dql);

        $query->setParameters( array(
                'accessionNumber' => $accessionNumber,
                //'accessionNumber' => '%'.$accessionNumber.'%',
                'accessionType' => $accessionType->getId(),
                'institution' => $institution->getId()
            )
        );

        //echo "sql=".$query->getSql()."<br>";

        return $query;
    }

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
        $dql->select('MAX(CAST(SUBSTRING(accessionAccession.field, 5) AS UNSIGNED)) as maxDeidentifier');

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

        $status = 'deidentifier';
        $source = null; //SourceSystemList

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
