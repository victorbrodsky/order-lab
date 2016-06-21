<?php

namespace Oleg\CallLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CallEntryController extends Controller
{

    /**
     * Case List Page
     * @Route("/", name="calllog_home")
     *
     * Alerts: filtered case list
     * @Route("/alerts/", name="calllog_alerts")
     *
     * @Template("OlegCallLogBundle:CallLog:home.html.twig")
     */
    public function homeAction(Request $request)
    {

        $title = "Call Case List";
        $alerts = false;

        if( $request->get('_route') == "calllog_alerts" ) {
            $alerts = true;
            $title = $title . " (Alerts)";
        }

        return array(
            //'entity' => $entity,
            //'form' => $form->createView(),
            //'cycle' => $cycle,
            'alerts' => $alerts,
            'title' => $title,
        );

    }



    /**
     * Call Entry
     * @Route("/call-entry/", name="calllog_callentry")
     * @Template("OlegCallLogBundle:CallLog:call-entry.html.twig")
     */
    public function callEntryAction(Request $request)
    {
        //1) search box: MRN,Name...

        $title = "Call Entry";

        return array(
            //'entity' => $entity,
            //'form' => $form->createView(),
            //'cycle' => $cycle,
            'title' => $title,
        );
    }


    /**
     * Call Entry
     * @Route("/callentry/search", name="calllog_search_callentry")
     * @Method("GET")
     * @Template()
     */
    public function searchCallEntryAction(Request $request)
    {
        if( false == $this->get('security.context')->isGranted('ROLE_CALLLOG_USER') ){
            return $this->redirect( $this->generateUrl('calllog-nopermission') );
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

//            $searchUtil = $this->get('search_utility');
//            $object = 'patient';
//            $params = array('request'=>$request,'object'=>$object,'searchtype'=>$searchtype,'search'=>$search,'exactmatch'=>false);
//            $res = $searchUtil->searchAction($params);
//            $entities = $res[$object];
            $entities = null;
        }


        //echo "entities count=".count($entities)."<br>";

        return $this->render('OlegCallLogBundle:CallLog:home.html.twig', array(
            'patientsearch' => $search,
            'patientsearchtype' => $searchtype,
            'patiententities' => $entities,
        ));
    }


}
