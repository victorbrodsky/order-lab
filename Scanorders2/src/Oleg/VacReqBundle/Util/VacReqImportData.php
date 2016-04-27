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