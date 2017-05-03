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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Util;



use Oleg\UserdirectoryBundle\Entity\Permission;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\UserdirectoryBundle\Entity\Logger;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserServiceUtil {

    protected $em;
    protected $sc;
    protected $container;
    protected $m3;

    public function __construct( $em, $sc, $container ) {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;

        $this->m3 = $this->initMetaphone();
    }

    public function convertFromUserTimezonetoUTC($datetime,$user) {

        //$user_tz = 'America/New_York';
        $user_tz = $user->getPreferences()->getTimezone();

        //echo "input datetime=".$datetime->format('Y-m-d H:i')."<br>";
        $datetimeTz = new \DateTime($datetime->format('Y-m-d H:i'), new \DateTimeZone($user_tz) );
        $datetimeUTC = $datetimeTz->setTimeZone(new \DateTimeZone('UTC'));
        //echo "output datetime=".$datetimeUTC->format('Y-m-d H:i')."<br>";

        return $datetimeUTC;
    }

    public function convertFromUtcToUserTimezone($datetime,$user) {

        //$user_tz = 'America/New_York';
        $user_tz = $user->getPreferences()->getTimezone();

        //echo "input datetime=".$datetime->format('Y-m-d H:i')."<br>";
        $datetimeUTC = new \DateTime($datetime->format('Y-m-d H:i'), new \DateTimeZone('UTC') );
        $datetimeTz = $datetimeUTC->setTimeZone(new \DateTimeZone($user_tz));

        //echo "output datetime=".$datetimeUTC->format('Y-m-d H:i')."<br>";

        return $datetimeTz;
    }

    public function convertToUserTimezone($datetime,$user) {

        //$user_tz = 'America/New_York';
        $user_tz = $user->getPreferences()->getTimezone();

        //echo "input datetime=".$datetime->format('Y-m-d H:i')."<br>";
        //$datetimeUTC = new \DateTime($datetime->format('Y-m-d H:i'), new \DateTimeZone('UTC') );
        $datetimeUserTz = $datetime->setTimeZone(new \DateTimeZone($user_tz));

        //echo "output datetime=".$datetimeUTC->format('Y-m-d H:i')."<br>";

        return $datetimeUserTz;
    }

    public function convertToTimezone($datetime,$tz) {
        //echo "input datetime=".$datetime->format('Y-m-d H:i')."<br>";
        //$datetimeUTC = new \DateTime($datetime->format('Y-m-d H:i'), new \DateTimeZone('UTC') );
        $datetimeTz = $datetime->setTimeZone(new \DateTimeZone($tz));
        //echo "output datetime=".$datetimeUTC->format('Y-m-d H:i')."<br>";

        return $datetimeTz;
    }

    //user1 - submitter, user2 - viewing user
    public function convertFromUserTzToUserTz($datetime,$user1,$user2) {

        //$user_tz = 'America/New_York';
        $user1_tz = $user1->getPreferences()->getTimezone();
        $user2_tz = $user2->getPreferences()->getTimezone();

        //echo "input datetime=".$datetime->format('Y-m-d H:i')."<br>";
        $datetimeUser1 = new \DateTime($datetime->format('Y-m-d H:i'), new \DateTimeZone($user1_tz));
        $datetimeUser2 = $datetimeUser1->setTimeZone(new \DateTimeZone($user2_tz));

        //echo "output datetime=".$datetimeUTC->format('Y-m-d H:i')."<br>";

        return $datetimeUser2;
    }

    //the timestamp must change based on the timezone set in Global User Preferences > TimeZone of the currently logged in user's profile
    public function getSubmitterInfo( $message, $user=null ) {
        if( !$user ) {
            $user = $this->sc->getToken()->getUser();
        }
        $info = $this->getOrderDateStr($message,$user);
        if( $message->getProvider() ) {
            $info = $info . " by ".$message->getProvider()->getUsernameOptimal();
        }
        return $info;
    }
    //DB datetime is UTC. Convert to the user's timezone.
    public function getOrderDateStr( $message, $user=null ) {
        //echo "getOrderDateStr <br>";
        $info = "";
        if( $message->getOrderdate() ) {
            if( !$user ) {
                $user = $this->sc->getToken()->getUser();
            }
            $orderDate = $message->getOrderdate();
            //$orderDate = $this->convertFromUserTzToUserTz($orderDate,$message->getProvider(),$user);
            //$info = $message->getOrderdate()->format('m/d/Y') . " at " . $message->getOrderdate()->format('h:i a (T)');
            $orderDateUserTz = $this->convertToUserTimezone($orderDate,$user);
            $viewingUserTz = $user->getPreferences()->getTimezone();
            $info = $orderDateUserTz->format('m/d/Y') . " at " . $orderDateUserTz->format('h:i a') . " (" . $viewingUserTz . ")";
        }
        return $info;
    }
    public function getOrderDateTzStr( $message, $tz=null ) {
        //echo "getOrderDateStr <br>";
        $info = "";
        if( $message->getOrderdate() ) {
            $orderDate = $message->getOrderdate();
            //$orderDateTz = $this->convertToTimezone($orderDate,$tz);
            //$info = $orderDateTz->format('m/d/Y') . " at " . $orderDateTz->format('h:i a') . " (" . $tz . ")";
            $info = $this->getDatetimeTzStr($orderDate,$tz);
        }
        return $info;
    }
    // 05/25/2017 at 3:25pm (Americas/New_York)
    public function getDatetimeTzStr( $datetime, $tz ) {
        //echo "getOrderDateStr <br>";
        //echo "input datetime=".$datetime->format('m/d/Y') . " at " . $datetime->format('h:i a') . " (" . $tz . ")"."<br>";
        $info = "";
        if( $datetime ) {
            $datetimeTz = $this->convertToTimezone($datetime,$tz);
            $info = $datetimeTz->format('m/d/Y') . " at " . $datetimeTz->format('h:i a') . " (" . $tz . ")";
        }
        //echo "output datetime=".$info."<br>";
        //exit('1');

        return $info;
    }

    // 05/25/2017 at 3:25pm (Americas/New_York)
    public function getSeparateDateTimeTzStr( $date, $time, $tz, $convertDate=true, $convertTime=true ) {
        //echo "getOrderDateStr <br>";
        //echo "input datetime=".$date->format('m/d/Y') . " at " . $time->format('h:i a') . " (" . $tz . ")"."<br>";
        //echo "date tz=".$date->getTimezone()->getName()."<br>";
        //echo "time tz=".$time->getTimezone()->getName()."<br>";

        $dateTz = $date;
        $timeTz = $time;
        if( $date && $convertDate ) {
            $dateTz = $this->convertToTimezone($date,$tz);
        }
        if( $time && $convertTime ) {
            $timeTz = $this->convertToTimezone($time,$tz);
        }
        $info = $dateTz->format('m/d/Y') . " at " . $timeTz->format('h:i a') . " (" . $tz . ")";

        //echo "output datetime=".$info."<br>";
        //exit('1');

        return $info;
    }

    //MSSQL error: [Microsoft][ODBC Driver 11 for SQL Server][SQL Server]'LEVENSHTEIN' is not a recognized built-in function name
    //try: http://stackoverflow.com/questions/41218952/is-not-a-recognized-built-in-function-name
    public function getFuzzyLike( $field, $search, &$dql, &$queryParameters ) {
        if( !($field && $search) ) {
            return null;
        }

//        $dql->andWhere($field." LIKE :search");
//        $queryParameters['search'] = "%".$search."%";

        $tolerance = 4;
        $dql->andWhere("LEVENSHTEIN(lastname.field,:search) <= :tolerance");
        $queryParameters['search'] = "%".$search."%";
        $queryParameters['tolerance'] = $tolerance;
    }
    public function getMetaphoneLike( $field, $search, &$dql, &$queryParameters ) {

        if( !($field && $search) ) {
            return null;
        }

        $userSecUtil = $this->container->get('user_security_utility');

        $enableMetaphone = $userSecUtil->getSiteSettingParameter('enableMetaphone');
        //$enableMetaphone = false;
        $pathMetaphone = $userSecUtil->getSiteSettingParameter('pathMetaphone');
        if( $enableMetaphone && $pathMetaphone ) {
            $outputArr = $this->getMetaphoneStrArr($search);

            $searchStr = "";
            $i = 0;
            foreach( $outputArr as $output ) {
                if( $output ) {
                    if ($searchStr) {
                        $searchStr = $searchStr . " OR ";
                    }
                    $searchStr = $searchStr . $field . " LIKE :search" . $i;

                    $queryParameters["search" . $i] = "%" . $output . "%";

                    $i++;
                }
            }

            if( $searchStr ) {
                if( $i > 1 ) {
                    $searchStr = "(" . $searchStr . ")";
                }
                $dql->andWhere($searchStr);
                //testing
                echo "searchStr=".$searchStr."<br>";
                print_r($queryParameters);
            }

        } else {
            $dql->andWhere($field." LIKE :search");
            $queryParameters['search'] = "%".$search."%";

            //echo "dql=".$dql->getSql()."<br>";
        }
    }

    //Assistance => ASSTN
    //Assistants => ASSTN
    //Therefore: DB must have ASSTN in order to find Assistance
    public function getMetaphoneKey( $word ) {

        if( !$this->m3 ) {
            return $word;
        }

        //test_word($m3, 'iron', 'ARN', '');
        $this->m3->SetWord($word);
        //Encodes input string to one or two key values according to Metaphone 3 rules.
        $this->m3->Encode();

        if( $this->m3->m_primary ) {
            return $this->m3->m_primary;
        }

        if( $this->m3->m_secondary ) {
            return $this->m3->m_secondary;
        }

        return $word;
    }

    public function initMetaphone() {
        $userSecUtil = $this->container->get('user_security_utility');
        $enableMetaphone = $userSecUtil->getSiteSettingParameter('enableMetaphone');
        $pathMetaphone = $userSecUtil->getSiteSettingParameter('pathMetaphone');

        if( !($enableMetaphone && $pathMetaphone) ) {
            return null;
        }

        //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\scanorder\Scanorders2\vendor\olegutil\Metaphone3\metaphone3.php
        //require_once('"'.$pathMetaphone.'"');
        //$pathMetaphone = "'".$pathMetaphone."'";
        require_once($pathMetaphone);

        $m3 = new \Metaphone3();

        $m3->SetEncodeVowels(TRUE);
        $m3->SetEncodeExact(TRUE);

        return $m3;
    }


    /////////////// NOT USED ///////////////////
    //TODO: try to use bundle: https://github.com/jr-k/JrkLevenshteinBundle
    //TODO: or https://packagist.org/packages/glanchow/doctrine-fuzzy
    public function getFuzzyTest() {
        $em = $this->em;
        $tolerance = 4;
        //$dql->andWhere("LEVENSHTEIN(lastname.field,:search) <= :tolerance");
        //$queryParameters['search'] = "%".$search."%";
        //$queryParameters['tolerance'] = $tolerance;

        $search = "last";

        //1)
        $sql = "
          SELECT id, field
          FROM scan_patientlastname
          WHERE field LIKE '%".$search."%'
        ";
        echo "sql=$sql<br>";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        foreach( $results as $result ) {
            echo "res=".$result['id'].": ".$result['field']."<br>";
        }

        if(1) {
            $repository = $em->getRepository('OlegOrderformBundle:PatientLastName');
            $dql = $repository->createQueryBuilder("list");
            $dql->select("list.id as id, LEVENSHTEIN(list.field, '".$search."') AS d");
            $dql->orderBy("d","ASC");
            $query = $em->createQuery($dql);

            //$query = $em
            //->createQueryBuilder('list')
            //->select('id, LEVENSHTEIN(list.field, :q) AS d')
            //->from($this->_entityName, 'g')
            //->orderby('d', 'ASC')
            //->setFirstResult($offset)
            //->setMaxResults($limit)
            //->setParameter('q', $search)
            //->getQuery();

            $results = $query->getResult();

            echo "<br>";
            foreach( $results as $result ) {
                echo "res=".$result['id'].": ".$result['d']."<br>";
            }

//            $repository = $this->em->getRepository('OlegUserdirectoryBundle:PermissionObjectList');
//            $dql =  $repository->createQueryBuilder("list");
//            $dql->select('list');
//            $dql->leftJoin('list.sites','sites');
//            $dql->where("(list.name = :objectname OR list.abbreviation = :objectname) AND (sites.name = :sitename OR sites.abbreviation = :sitename)");
//            $query = $this->em->createQuery($dql);

            //return $query->getResult();
        }

        //2)
        if(0){
            $sql = "SELECT id, field FROM scan_patientlastname WHERE ( LEVENSHTEIN(field,'".$search."') <= 4 )";
            echo "sql=$sql<br>";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();

            foreach( $results as $result ) {
                echo "res=".$result['id'].": ".$result['field']."<br>";
            }
        }
        return $results;
    }
    //Assistance => ASSTN
    //Assistants => ASSTN
    //Therefore: DB must have ASSTN in order to find Assistance
    public function getMetaphoneStrArr( $word, $primary=true ) {
        $outputArr = array();
        $outputArr[] = $word;

        $userSecUtil = $this->container->get('user_security_utility');
        $enableMetaphone = $userSecUtil->getSiteSettingParameter('enableMetaphone');
        $pathMetaphone = $userSecUtil->getSiteSettingParameter('pathMetaphone');

        if( !($enableMetaphone && $pathMetaphone) ) {
            return $outputArr;
        }

        //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\scanorder\Scanorders2\vendor\olegutil\Metaphone3\metaphone3.php
        //require_once('"'.$pathMetaphone.'"');
        //$pathMetaphone = "'".$pathMetaphone."'";
        require_once($pathMetaphone);

        $m3 = new \Metaphone3();

        $m3->SetEncodeVowels(TRUE);
        $m3->SetEncodeExact(TRUE);

        //test_word($m3, 'iron', 'ARN', '');
        $m3->SetWord($word);
        //Encodes input string to one or two key values according to Metaphone 3 rules.
        $m3->Encode();

        if( $primary ) {
            return $m3->m_primary;
        }

        $outputArr[] = $m3->m_primary;
        $outputArr[] = $m3->m_secondary;
        return $outputArr;
    }
    /////////////// EOF NOT USED ///////////////////

}