<?php

namespace Oleg\VacReqBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\VacReqBundle\Entity\VacReqRequest;
use Oleg\VacReqBundle\Form\VacReqFilterType;
use Oleg\VacReqBundle\Form\VacReqRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//vacreq site

class RequestIndexController extends Controller
{

    /**
     * Creates a new VacReqRequest entity.
     *
     * @Route("/my-requests/", name="vacreq_myrequests")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Request:index.html.twig")
     */
    public function myRequestsAction(Request $request)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //$em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('OlegVacReqBundle:VacReqRequest')->findAll();

        $params = array(
            'sitename'=>$this->container->getParameter('vacreq.sitename')
        );
        return $this->listRequests($params, $request);
    }


    /**
     * Creates a new VacReqRequest entity.
     *
     * @Route("/incoming-requests/", name="vacreq_incomingrequests")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Request:index.html.twig")
     */
    public function incomingRequestsAction(Request $request)
    {

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegVacReqBundle:VacReqRequest')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    public function processFilter( $dql, $request, $params ) {

        $dqlParameters = array();
        $filterRes = array();
        $filtered = false;

        //////////////////// get list of users with "unknown" user ////////////////////
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:User');
        $dqlFilterUser = $repository->createQueryBuilder('user');
        $dqlFilterUser->select('user');
        $dqlFilterUser->leftJoin("user.infos","infos");
        //$dqlFilterUser->where("user.keytype IS NOT NULL");
        $dqlFilterUser->orderBy("infos.lastName","ASC");
        $queryFilterUser = $em->createQuery($dqlFilterUser);
        $filterUsers = $queryFilterUser->getResult();
        //echo "count=".count($filterUsers)."<br>";
        //add unknown dummy user
        $unknown = new User();
        $unknown->setDisplayName("unknown");
        $em->persist($unknown);
        //$filterUsers[] = $unknown;
        array_unshift($filterUsers, $unknown);
        $params['filterUsers'] = $filterUsers;
        //////////////////// EOF get list of users with "unknown" user ////////////////////

        $filterform = $this->createForm(new VacReqFilterType($params), null);

        $filterform->bind($request);

        $startdate = $filterform['startdate']->getData();
        $enddate = $filterform['enddate']->getData();
        $user = $filterform['user']->getData();
        //$year = $filterform['year']->getData();
        echo "userID=".$user->getId()."<br>";

        $currentUser = $this->get('security.context')->getToken()->getUser();

        if( $user ) {
            $where = "";
            if( $where != "" ) {
                $where .= " OR ";
            }
            if( $user->getId() ) {
                $where .= "request.user=".$user->getId();
            } else {
                $where .= "request.user IS NULL";
            }
            $dql->andWhere($where);

            $filtered = true;
        }

        echo "startdate=".$startdate."<br>";
        if( $startdate ) {
            $dql->andWhere("request.createDate >= :startdate");

            $startdate = $this->convertFromUserTimezonetoUTC($startdate,$currentUser);
            $dqlParameters['startdate'] = $startdate;

            $filtered = true;
        }

        if( $enddate ) {
            $dql->andWhere("request.createDate <= :enddate");

            $enddate = $this->convertFromUserTimezonetoUTC($enddate,$currentUser);
            $dqlParameters['enddate'] = $enddate;

            $filtered = true;
        }

        $filterRes['form'] = $filterform;
        $filterRes['dqlParameters'] = $dqlParameters;
        $filterRes['filtered'] = $filtered;

        return $filterRes;
    }


    public function listRequests( $params, $request ) {

        $em = $this->getDoctrine()->getManager();

        $sitename = ( array_key_exists('sitename', $params) ? $params['sitename'] : null);

        $repository = $em->getRepository('OlegVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');
        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("user.infos", "infos");
        $dql->leftJoin("request.requestBusiness", "requestBusiness");
        $dql->leftJoin("request.requestVacation", "requestVacation");

        //$dql->where("requestBusiness.startDate IS NOT NULL OR requestVacation.startDate IS NOT NULL");

        $filterRes = $this->processFilter( $dql, $request, $params );
        $filterform = $filterRes['form'];
        $dqlParameters = $filterRes['dqlParameters'];
        $filtered = $filterRes['filtered'];

        $limit = 30;
        $query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters( $dqlParameters );
        }

        echo "query=".$query->getSql()."<br>";

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        return array(
            'filterform' => $filterform,
            'vacreqfilter' => $filterform->createView(),
            'pagination' => $pagination,
            'sitename' => $sitename,
            'filtered' => $filtered,
            'routename' => $request->get('_route')
        );
    }

}
