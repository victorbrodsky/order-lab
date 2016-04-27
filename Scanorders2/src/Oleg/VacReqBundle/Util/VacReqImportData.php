<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/27/2016
 * Time: 11:35 AM
 */

namespace Oleg\VacReqBundle\Util;


class VacReqImportData
{

    protected $em;
    protected $sc;
    protected $container;


    public function __construct( $em, $sc, $container ) {

        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;

    }

    //REQUEST_STATUS_ID
    //BUS_REQUEST_STATUS_ID
    //VAC_REQUEST_STATUS_ID

    //PFVBTR_REQUEST_STATUS_SEL
    //1	pending
    //2	approved
    //3	rejected
    //4	completed
    //5	closed

    //PFVBTR_APPROVER_INFO
    //1	Barry Sleckman	bas2022@med.cornell.edu	Sara Lynch	sal2026@med.cornell.edu	Experimental Pathology	approver	yes
    //2	Dr. Jacob Rand	jar9135@med.cornell.edu	Sara 	sal2026@med.cornell.edu	Clinical Pathology	approver	yes
    //3	Attilio Orazi	ato9002@med.cornell.edu	Melissa Honore 	meh9043@nyp.org	Hematopathology	approver	yes
    //4	Alain Borczuk 	alb9003@med.cornell.edu	Rashida Eteng	rse9005@nyp.org	Surgical Pathology	approver	yes
    //5	Rana Hoda	rhoda@med.cornell.edu	Maxine Stevenson	mes9008@nyp.org	Cytopathology	approver	yes
    //20	Daniel M. Knowles	dknowles@med.cornell.edu	Sara Lynch	sal2026@med.cornell.edu	Executive Committee	executive	yes
    //6	Timothy Hla	tih2002@med.cornell.edu	Mario A. Castro Martinez	mcm2010@med.cornell.edu	Vascular Biology	approver	yes
    //19	Cynthia Magro				Dermatopathology	approver	no

    // url: /import-old-data/
    public function importOldData() {

        $email = "oli2002@med.cornell.edu";

        $requests = array();

        $vacreqEm = $this->container->get('doctrine')->getManager('vacreq');
        echo "get vacreq em<br>";

        $vacreqConnection = $vacreqEm->getConnection();
        echo "after connection vacreq <br>";

        //select USERID,FIRST_NAME,LAST_NAME from profiler2015.USER_INFO where EMAIL LIKE '%gul%';
        $statement = $vacreqConnection->prepare(
            "select USERID,FIRST_NAME,LAST_NAME from profiler2015.USER_INFO where EMAIL LIKE :email"
        );
        $statement->bindValue('email', "'%".$email."%'");

        echo "before execute<br>";
        $statement->execute();
        echo "after execute<br>";

        $results = $statement->fetchAll();

        echo "<br>Result:<br>";
        print_r($results);
        echo "<br><br>";

        // for INSERT, UPDATE, DELETE queries
        $affected_rows = $statement->rowCount();
        echo "Affected Rows=".$affected_rows."<br>";


        if( $affected_rows != 1 && count($results) != 1 ) {
            throw $this->createNotFoundException('Unable to find request');
        }

        $request = $results[0]['FIRST_NAME'];
        echo "request=".$request."<br>";

        exit('1');

        $result = "Imported requests = " . count($requests);
        return $result;
    }


}