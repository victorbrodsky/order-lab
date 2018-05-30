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
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\UserdirectoryBundle\Entity\Logger;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserServiceUtil {

    protected $em;
    protected $secTokenStorage;
    protected $container;
    protected $m3;

    public function __construct( $em, $secTokenStorage, $container ) {
        $this->em = $em;
        $this->secTokenStorage = $secTokenStorage;
        $this->container = $container;
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
            $user = $this->secTokenStorage->getToken()->getUser();
        }
        $info = $this->getOrderDateStr($message,$user);
        if( $message && $message->getProvider() ) {
            $info = $info . " by ".$message->getProvider()->getUsernameOptimal();
        }
        return $info;
    }
    //DB datetime is UTC. Convert to the user's timezone.
    public function getOrderDateStr( $message, $user=null ) {
        //echo "getOrderDateStr <br>";
        if( !$message ) {
            return null;
        }

        $info = "";
        if( $message->getOrderdate() ) {
            if( !$user ) {
                $user = $this->secTokenStorage->getToken()->getUser();
            }
            $orderDate = $message->getOrderdate();
            //$orderDate = $this->convertFromUserTzToUserTz($orderDate,$message->getProvider(),$user);
            //$info = $message->getOrderdate()->format('m/d/Y') . " at " . $message->getOrderdate()->format('h:i a (T)');
            $orderDateUserTz = $this->convertToUserTimezone($orderDate,$user);
            //$viewingUserTz = $user->getPreferences()->getTimezone();
            $viewingUserTz = $orderDateUserTz->format('T');
            $info = $orderDateUserTz->format('m/d/Y') . " at " . $orderDateUserTz->format('h:i a') . " (" . $viewingUserTz . ")";
        }
        return $info;
    }
    public function getOrderDateTzStr( $message, $tz=null ) {
        //echo "getOrderDateTzStr <br>";
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
        //echo "getDatetimeTzStr <br>";
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


    //$field - field with the raw string (i.e. "lastname.field")
    //$fieldMetaphone - field with the metaphone key string (i.e. "lastname.fieldMetaphone")
    //$search - search string (i.e "McMastar")
    //$dql - pointer to the $dql object to modify
    //$queryParameters - pointer to $queryParameters array to modify
    public function getMetaphoneLike( $field, $fieldMetaphone, $search, &$dql, &$queryParameters ) {

        if( !($field && $search) ) {
            return null;
        }

//        $metaphoneKey = $this->getMetaphoneKey($search);
//        //echo "metaphoneKey:".$search."=>".$metaphoneKey."<br>";
//
//        if( $metaphoneKey ) {
//            $dql->andWhere("(".$field." LIKE :search"." OR ".$fieldMetaphone." LIKE :metaphoneKey".")");
//            $queryParameters['search'] = "%".$search."%";
//            $queryParameters['metaphoneKey'] = "%".$metaphoneKey."%";
//        } else {
//            $dql->andWhere($field." LIKE :search");
//            $queryParameters['search'] = "%".$search."%";
//            //echo "dql=".$dql->getSql()."<br>";
//        }

        $criterionStr = $this->getMetaphoneStrLike($field,$fieldMetaphone,$search,$queryParameters);
        if( $criterionStr ) {
            $dql->andWhere($criterionStr);
        }
    }

    public function getMetaphoneStrLike( $field, $fieldMetaphone, $search, &$queryParameters, $fieldIndex=null ) {
        $criterionStr = null;

        if( !($field && $search) ) {
            return null;
        }

        $metaphoneKey = $this->getMetaphoneKey($search);
        //echo "metaphoneKey:".$search."=>".$metaphoneKey."<br>";

        if( !$fieldIndex ) {
            $fieldIndex = "metaphoneKey";
        }

        if( $metaphoneKey ) {
            $criterionStr = "(".$field." LIKE :search".$fieldIndex." OR ".$fieldMetaphone." LIKE :".$fieldIndex.")";
            $queryParameters['search'.$fieldIndex] = "%".$search."%";
            $queryParameters[$fieldIndex] = "%".$metaphoneKey."%";
        } else {
            $criterionStr = $field." LIKE :search".$fieldIndex;
            $queryParameters['search'.$fieldIndex] = "%".$search."%";
        }

        return $criterionStr;
    }

    //Assistance => ASSTN
    //Assistants => ASSTN
    //Therefore: DB must have ASSTN in order to find Assistance
    public function getMetaphoneKey( $word ) {

        $this->initMetaphone();

        if( !$this->m3 ) {
            //$logger = $this->container->get('logger');
            //$logger->notice("m3 is null => return null");
            return null;
        }

        $this->m3->SetWord($word);

        //Encodes input string to one or two key values according to Metaphone 3 rules.
        $this->m3->Encode();

        if( $this->m3->m_primary ) {
            return $this->m3->m_primary;
        }

        if( $this->m3->m_secondary ) {
            return $this->m3->m_secondary;
        }

        return null;
    }

    //1) copy metaphone to the folder (i.e. "my folder")
    //2 enable metaphone in site setting
    //3) set the path to metaphone php file: i.e. "C:/my folder/metaphone3.php"
    public function initMetaphone() {

        //$logger = $this->container->get('logger');

        if( $this->m3 ) {
            //$logger->notice("Metaphone already initialized => return m3");
            return $this->m3;
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $enableMetaphone = $userSecUtil->getSiteSettingParameter('enableMetaphone');
        $pathMetaphone = $userSecUtil->getSiteSettingParameter('pathMetaphone');

        if( !($enableMetaphone && $pathMetaphone) ) {
            //$logger->notice("Metaphone enable or path are null => return null");
            $this->m3 = null;
            return null;
        }

        //testing
        //$logger->notice("init Metaphone");

        //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\scanorder\Scanorders2\vendor\olegutil\Metaphone3\metaphone3.php
        //require_once('"'.$pathMetaphone.'"');
        //$pathMetaphone = "'".$pathMetaphone."'";
        //$pathMetaphone = '"'.$pathMetaphone.'"';
        //$pathMetaphone = str_replace(" ", "\\ ", $pathMetaphone);
        require_once($pathMetaphone);

        $m3 = new \Metaphone3();

        $m3->SetEncodeVowels(TRUE);
        $m3->SetEncodeExact(TRUE);

        $this->m3 = $m3;

        return $m3;
    }

    public function metaphoneTest() {
        $this->metaphoneSingleTest("Jackson");
        $this->metaphoneSingleTest("Jacksa");
        $this->metaphoneSingleTest("Jaksa");

        $this->metaphoneSingleTest("mcmaster");
        $this->metaphoneSingleTest("macmaste");
        $this->metaphoneSingleTest("master");

        $this->metaphoneSingleTest("Michael Jackson");

        $this->metaphonePhpSingleTest("mcmaster");
        $this->metaphonePhpSingleTest("macmaste");
        $this->metaphonePhpSingleTest("master");
    }
    public function metaphoneSingleTest($input) {
        $output = $this->getMetaphoneKey($input);
        echo $input."=>".$output."<br>";
    }
    public function metaphonePhpSingleTest($input) {
        $output = metaphone($input);
        echo $input."=>".$output." (php)<br>";
    }

    public function isWinOs() {
        /* Some possible outputs:
        Linux localhost 2.4.21-0.13mdk #1 Fri Mar 14 15:08:06 EST 2003 i686
        Linux

        FreeBSD localhost 3.2-RELEASE #15: Mon Dec 17 08:46:02 GMT 2001
        FreeBSD

        Windows NT XN1 5.1 build 2600
        WINNT
        */

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //echo 'This is a server using Windows!';
            return true;
        } else {
            //echo 'This is a server not using Windows!';
        }

        return false;
    }

    public function browserCheck( $asString=false ) {
        //https://github.com/sinergi/php-browser-detector with MIT license
        $browser = new Browser();
        $name = $browser->getName();
        $version = $browser->getVersion();

        $os = new Os();
        $platform = $os->getName();

        $msg = "You appear to be using the <strong>outdated $name $version browser on $platform</strong>
        and it is not able to show you this site properly.<br>
        Please use Chrome, Firefox, Internet Explorer 9, Internet Explorer 10, Internet Explorer 11,
        or the Edge browser instead and visit this page again.<br>
        You can copy the URL of this page and paste it into the
        address bar of the other browser once you switch to it.";

        //Select2:
        //        IE 8+       >8
        //        Chrome 8+   >48
        //        Firefox 10+ >45
        //        Safari 3+
        //        Opera 10.6+ >12
        //Bootstrap: Safari on Windows not supported

        if( $asString ) {
            $browserInfo = $name . " " . $version . " on " . $platform;
            //echo "Your browser: " . $browserInfo . "<br>";
            return $browserInfo;
        }

        if( $name == Browser::IE ) {
            //Bootstrap IE 8+
            //Select2 IE 8+
            if( $version < 9 ) {
                return $msg;
            }
        }

        if( $name == Browser::SAFARI ) {
            //Bootstrap: Safari on Windows not supported
            if( $platform == Os::WINDOWS || $platform == Os::WINDOWS_PHONE ) {
                return $msg;
            }
        }

        if( $name == Browser::CHROME ) {
            if( $version < 48 ) {
                return $msg;
            }
        }

        if( $name == Browser::FIREFOX ) {
            if( $version < 45 ) {
                return $msg;
            }
        }

        if( $name == Browser::OPERA || $name == Browser::OPERA_MINI ) {
            if( $version < 12 ) {
                return $msg;
            }
        }

        return null;
    }

    //use it for deprecated choices secletion for Symfony>2.7
    public function flipArrayLabelValue( $keyLabelArr ) {
        if( !$keyLabelArr ) {
            return $keyLabelArr;
        }
        $labelValueArr = array();
        foreach( $keyLabelArr as $key=>$label ) {
            //echo "[$key] => [$label] <br>";
            if( $label ) {
                $labelValueArr[$label.""] = $key;
            }
        }
        return $labelValueArr;
    }
    

    public function getUniqueRegistrationLinkId( $className, $sometxt, $count=0 ) {
        if( $count > 100 ) { //limit: trying limit
            $limitRegistrationLinkId = uniqid($sometxt,true);
            $limitRegistrationLinkId = md5($limitRegistrationLinkId);
            //echo "limit return: $limitRegistrationLinkId<br>";
            return $limitRegistrationLinkId;
        }
        $registrationLinkId = uniqid(mt_rand(),true);
        //echo "registrationLinkId=$registrationLinkId<br>";
        $registrationLinkId = md5($registrationLinkId);
        //find if already exists
        $existedSignup = $this->em->getRepository('OlegUserdirectoryBundle:'.$className)->findByRegistrationLinkID($registrationLinkId);
        if( $existedSignup ) {
            $count++;
            //echo "try gen: existedLinkId=$registrationLinkId; count=$count<br>";
            $registrationLinkId = $this->getUniqueRegistrationLinkId($className,$sometxt,$count);
        }
        //echo "return gen: existedLinkId=$registrationLinkId; count=$count<br>";
        return $registrationLinkId;
    }

    public function findOneCommentByThreadBodyAuthor($thread, $bodyText, $author)
    {
        $repository = $this->em->getRepository('OlegUserdirectoryBundle:FosComment');
        $dql =  $repository->createQueryBuilder("comment");
        $dql->select('comment');

        $dql->leftJoin("comment.thread", "thread");
        $dql->leftJoin("comment.author", "author");

        $dql->where("thread.id = :threadId AND author.id = :authorId AND comment.body = :body");

        $parameters = array(
            "threadId" => $thread->getId(),
            "authorId" => $author->getId(),
            "body" => $bodyText
        );

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $comments = $query->getResult();

        if( count($comments) > 0 ) {
            $comment = $comments[0];
            //echo "Comment found ID=".$comment->getId()."<br>";
            return $comment;
        }

        //echo "Comment Not found by threadID=".$thread->getId()."; bodyText=".$bodyText."<br>";
        return null;
    }

    public function getListUserFilter($pathlink, $pathlinkLoc, $hasRoleSimpleView) {
        $userSecUtil = $this->container->get('user_security_utility');

        $res = array();
        $inst1 = null;
        $inst2 = null;

        $institution1 = $userSecUtil->getSiteSettingParameter("navbarFilterInstitution1");
        echo "institution1=".$institution1."<br>";
        if( $institution1 ) {
            $inst1 = $institution1->getAbbreviation();
        }
        $institution2 = $userSecUtil->getSiteSettingParameter("navbarFilterInstitution2");
        if( $institution2 ) {
            $inst2 = $institution2->getAbbreviation();
        }

        $instTypes = array(
            //'hr' => 'all',

            '[inst1] Pathology Employees' => 'all',
            '[inst1] Pathology Faculty' => 'all',
            '[inst1] Pathology Clinical Faculty' => 'all',
            '[inst1] Pathology Physicians' => 'notSimpleView',

            'hr' => 'all',

            '[inst1] Pathology Research Faculty' => 'all',
            '- [inst1] Pathology Principal Investigators of Research Labs' => 'all',
            '- [inst1] Pathology Faculty in Research Labs' => 'all',

            'hr' => 'all',

            '[inst1] Pathology Staff' => 'notSimpleView',
            '[inst2] Pathology Staff' => 'notSimpleView',
            '- [inst1] or [inst2] Pathology Staff in Research Labs' => 'all',

            'hr' => 'all',

            '[inst1] Anatomic Pathology Faculty' => 'all',
            '[inst2] Laboratory Medicine Faculty' => 'all',

            'hr' => 'all',

            '[inst1] or [inst2] Pathology Residents' => 'all',
            '- [inst1] or [inst2] AP/CP Residents' => 'notSimpleView',
            '- [inst1] or [inst2] AP Residents' => 'notSimpleView',
            '- [inst1] or [inst2] AP Only Residents' => 'notSimpleView',
            '- [inst1] or [inst2] CP Residents' => 'notSimpleView',
            '- [inst1] or [inst2] CP Only Residents' => 'notSimpleView',

            '[inst1] or [inst2] Pathology Fellows' => 'all',
            '[inst1] Non-academic Faculty' => 'all',
        );

        //first common element
        $linkUrl = $this->container->get('router')->generate(
            $pathlink,
            array(
                //no filter
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $href = '<li><a href="'.$linkUrl.'">'.'Employees'.'</a></li>';
        $res[] = $href;


        foreach($instTypes as $name=>$flag) {
            if( $name == 'hr' ) {
                $res[] = '<hr style="margin-bottom:0; margin-top:0;">';
                continue;
            }
            if( !$hasRoleSimpleView || !($hasRoleSimpleView && $flag == 'notSimpleView') ) {

                $href = $this->replaceInstFilter($name,$pathlink,$inst1,$inst2);
                if( $href ) {
                    $res[] = $href;
                }

            }
        }

        if( $pathlinkLoc ) {
            $locTypes = array(
                '[inst1] or [inst2] Pathology Common Locations' => 'all',
                '[inst1] Pathology Common Locations' => 'all',
                '[inst2] Pathology Common Locations' => 'all',
            );

            //first common element
            $res[] = '<hr style="margin-bottom:0; margin-top:0;">';

            $linkUrl = $this->container->get('router')->generate(
                $pathlink,
                array(
                    //no filter
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $href = '<li><a href="'.$linkUrl.'">'.'Common Locations'.'</a></li>';
            $res[] = $href;

            foreach($locTypes as $name=>$flag) {
                if( $name == 'hr' ) {
                    $res[] = '<hr style="margin-bottom:0; margin-top:0;">';
                    continue;
                }

                $href = $this->replaceInstFilter($name,$pathlinkLoc,$inst1,$inst2);
                if( $href ) {
                    $res[] = $href;
                }
            }
        }

        return $res;
    }
    public function replaceInstFilter($name,$pathlink,$inst1,$inst2) {
        $href = null;
        $nameInst = null;

        if( $inst1 && $inst2 ) {
            $nameInst = str_replace('[inst1]',$inst1,$name);
            $nameInst = str_replace('[inst2]',$inst2,$nameInst);
        }

        if( $inst1 && !$inst2 ) {
            if( strpos($name, '[inst1]') !== false ) {
                if( strpos($name, '[inst1]') !== false && strpos($name, '[inst2]') === false ) {
                    $nameInst = str_replace('[inst1]',$inst1,$name);
                }
            }
        }

        if( $inst2 && !$inst1 ) {
            if( strpos($name, '[inst2]') !== false ) {
                if( strpos($name, '[inst2]') !== false && strpos($name, '[inst1]') === false ) {
                    $nameInst = str_replace('[inst2]',$inst2,$name);
                }
            }
        }

        if( $nameInst ) {
            $linkUrl = $this->container->get('router')->generate(
                $pathlink,
                array(
                    'filter'=>str_replace('- ','',$nameInst),
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $href = '<li><a href="'.$linkUrl.'">'.$nameInst.'</a></li>';
        }
        //$href = '<li><a href="'.$linkUrl.'">'.$nameInst.'</a></li>';
        //$res[] = $href;

        return $href;
    }



    /////////////// NOT USED ///////////////////
    //NOT USED
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

    //TODO: or https://packagist.org/packages/glanchow/doctrine-fuzzy (cons: different DB requires different implementation of LEVENSHTEIN function)
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