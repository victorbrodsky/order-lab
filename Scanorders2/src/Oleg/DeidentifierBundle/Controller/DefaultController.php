<?php

namespace Oleg\DeidentifierBundle\Controller;

use Oleg\DeidentifierBundle\Form\DeidentifierSearchType;
use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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

        //check for active access requests
        $accessreqs = $this->getActiveAccessReq();

        //search box
        $form = $this->createForm(new DeidentifierSearchType(), null);

        //get search string
        $form->bind($request);  //use bind instead of handleRequest. handleRequest does not get filter data
        $accessionNumber = $form->get('accessionNumber')->getData();
        $accessionType = $form->get('accessionType')->getData();

        echo "accessionNumber=".$accessionNumber."<br>";
        echo "accessionType=".$accessionType."<br>";

        $pagination = null;

        if( $accessionNumber != "" ) {

            //get accession numbers by number and type

//            $accessions = $em->getRepository('OlegOrderformBundle:AccessionAccession')->findBy(
//                array('field' => $accessionNumber, 'keytype' => $accessionType),
//                array('creationdate' => 'DESC')
//            );

            $limit = 20;

            $repository = $em->getRepository('OlegOrderformBundle:AccessionAccession');
            $dql =  $repository->createQueryBuilder("accession");
            $dql->select('accession');
            $dql->leftJoin("accession.keytype", "keytype");

            //$dql->where("accession.field LIKE '%".$accessionNumber."%'");
            //$dql->where("accession.field = '".$accessionNumber."' AND keytype.id = ".$accessionType->getId());
            //$dql->where("accession.field LIKE :accessionNumber AND keytype.id = :accessionType");
            $dql->where("accession.field LIKE :accessionNumber");

            $query = $em->createQuery($dql);

            $query->setParameters( array(
                    //'accessionNumber' => $accessionNumber,
                    'accessionNumber' => "'%".$accessionNumber."%'",
                    //'accessionNumber' => '"%'.$accessionNumber.'%"',
                    //'accessionType' => $accessionType->getId()
                )
            );

            echo "sql=".$query->getSql()."<br>";

            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $query,
                $this->get('request')->query->get('page', 1),   /*page number*/
                $limit                                          /*limit per page*/
            );

            echo "pagination count=" . count($pagination) . "<br>";
        }

        return array(
            'accessreqs' => count($accessreqs),
            'form' => $form->createView(),
            'pagination' => $pagination
        );
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
