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
use Oleg\UserdirectoryBundle\Entity\SiteParameters;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\UserdirectoryBundle\Entity\Logger;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Crontab\Crontab;
use Crontab\Job;

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

    public function convertFromUtcToUserTimezone($datetime,$user=null) {

        if( !$user ) {
            $user = $this->secTokenStorage->getToken()->getUser();
        }

        //$user_tz = 'America/New_York';
        //$user_tz = $user->getPreferences()->getTimezone();
        $user_tz = null;
        $preferences = $user->getPreferences();
        if( $preferences ) {
            $user_tz = $preferences->getTimezone();
        }
        if( !$user_tz ) {
            return $datetime;
        }

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
        
        //TODO: add timezone in the user's timezone
        //$user = $this->secTokenStorage->getToken()->getUser();
        //$dateTime = new \DateTime();
        //$dateTime->setDate($date);
        //$dateTime->setTime($time);
        //$dateTime->setTimezone($tz);
        //$datetimeTz = $userServiceUtil->convertToTimezone($dateTimeObject,$formValueTimezone);
        //$modifiedOnUserTz = $this->convertToUserTimezone($dateTime,$user);
        //$info = $info . " (" . $modifiedOnUserTz->format("m/d/Y at h:i (T)") . ")";
//                    $formValueStr = $formValueStr . " (".$modifiedOnUserTz->format("m/d/Y").")";
//                    exit($formValueStr);
        
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
        //echo "start browserCheck<br>";
        //https://github.com/sinergi/php-browser-detector with MIT license
        $browser = new Browser();
        $name = $browser->getName();
        $version = $browser->getVersion();

        $os = new Os();
        $platform = $os->getName();

        //$logger = $this->container->get('logger');
        //$logger->notice("$name $version browser on $platform");

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


    public function generateSiteParameters() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->em;

        $entities = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( count($entities) > 0 ) {
            $logger->notice("Exit generateSiteParameters: SiteParameters has been already generated.");
            return -1;
        }

        $logger->notice("Start generating SiteParameters");

        $defaultSystemEmail = $this->container->getParameter('default_system_email');

        $types = array(
            "connectionChannel" => "http",

            "maxIdleTime" => "30",
            "environment" => "dev",
            "siteEmail" => "email@email.com",
            "loginInstruction" => 'Please use your <a href="http://weill.cornell.edu/its/identity-security/identity/cwid/">CWID</a> to log in.',

            "enableAutoAssignmentInstitutionalScope" => true,

            "smtpServerAddress" => "smtp.gmail.com",
            "mailerPort" => "587",
            "mailerTransport" => "smtp",
            "mailerAuthMode" => "login",
            "mailerUseSecureConnection" => "tls",
            "mailerUser" => null,
            "mailerPassword" => null,
            "mailerSpool" => false,
            "mailerFlushQueueFrequency" => 15, //minuts
            "mailerDeliveryAddresses" => null,

            "aDLDAPServerAddress" => "ldap.forumsys.com",
            "aDLDAPServerPort" => "389",
            "aDLDAPServerOu" => "dc=example,dc=com",    //used for DC
            "aDLDAPServerAccountUserName" => null,
            "aDLDAPServerAccountPassword" => null,
            "ldapExePath" => "../src/Oleg/UserdirectoryBundle/Util/",
            "ldapExeFilename" => "LdapSaslCustom.exe",

            "dbServerAddress" => "127.0.0.1",
            "dbServerPort" => "null",
            "dbServerAccountUserName" => "null",
            "dbServerAccountPassword" => "null",
            "dbDatabaseName" => "null",

            "pacsvendorSlideManagerDBServerAddress" => "127.0.0.1",
            "pacsvendorSlideManagerDBServerPort" => "null",
            "pacsvendorSlideManagerDBUserName" => "null",
            "pacsvendorSlideManagerDBPassword" => "null",
            "pacsvendorSlideManagerDBName" => "null",

            "institutionurl" => "http://www.cornell.edu/",
            "institutionname" => "Cornell University",
            "subinstitutionurl" => "http://weill.cornell.edu",
            "subinstitutionname" => "Weill Cornell Medicine",
            "departmenturl" => "http://www.cornellpathology.com",
            "departmentname" => "Pathology and Laboratory Medicine Department",
            "showCopyrightOnFooter" => true,

            ///////////////////// FELLAPP /////////////////////
            "codeGoogleFormFellApp" => "",
            "confirmationEmailFellApp" => "",
            "confirmationSubjectFellApp" => "Your WCM/NYP fellowship application has been succesfully received",
            "confirmationBodyFellApp" => "Thank You for submitting the fellowship application to Weill Cornell Medical College/NewYork Presbyterian Hospital. ".
                "Once we receive the associated recommendation letters, your application will be reviewed and considered. ".
                "If You have any questions, please do not hesitate to contact me by phone or via email. ".
                "Sincerely, Jessica Misner Fellowship Program Coordinator Weill Cornell Medicine Pathology and Laboratory Medicine 1300 York Avenue, Room C-302 T 212.746.6464 F 212.746.8192",
            "clientEmailFellApp" => '',
            "p12KeyPathFellApp" => 'E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\src\Oleg\FellAppBundle\Util',
            "googleDriveApiUrlFellApp" => "https://www.googleapis.com/auth/drive https://spreadsheets.google.com/feeds",
            "userImpersonateEmailFellApp" => "olegivanov@pathologysystems.org",
            "templateIdFellApp" => "",
            "backupFileIdFellApp" => "",
            "folderIdFellApp" => "",
            "localInstitutionFellApp" => "Pathology Fellowship Programs (WCM)",
            "deleteImportedAplicationsFellApp" => false,
            "deleteOldAplicationsFellApp" => false,
            "yearsOldAplicationsFellApp" => 2,
            "spreadsheetsPathFellApp" => "fellapp/Spreadsheets",
            "applicantsUploadPathFellApp" => "fellapp/FellowshipApplicantUploads",
            "reportsUploadPathFellApp" => "fellapp/Reports",
            "applicationPageLinkFellApp" => "http://wcmc.pathologysystems.org/fellowship-application",
            "libreOfficeConvertToPDFPathFellApp" => 'C:\Program Files (x86)\LibreOffice 5\program',
            "libreOfficeConvertToPDFFilenameFellApp" => "soffice",
            "libreOfficeConvertToPDFArgumentsdFellApp" => "--headless -convert-to pdf -outdir",
            "pdftkPathFellApp" => 'C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\vendor\olegutil\PDFTKBuilderPortable\App\pdftkbuilder',
            "pdftkFilenameFellApp" => "pdftk",
            "pdftkArgumentsFellApp" => "###inputFiles### cat output ###outputFile### dont_ask",
            "gsPathFellApp" => 'C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\vendor\olegutil\Ghostscript\bin',
            "gsFilenameFellApp"=>"gswin64c.exe",
            "gsArgumentsFellApp"=>"-q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile= ###outputFile###  -c .setpdfwrite -f ###inputFiles###",
            //"libreOfficeConvertToPDFPathFellAppLinux" => "/usr/lib/libreoffice/program",
            //"libreOfficeConvertToPDFFilenameFellAppLinux" => "soffice",
            ///////////////////// EOF FELLAPP /////////////////////

            //VacReq
            "vacationAccruedDaysPerMonth" => '2',
            "academicYearStart" => new \DateTime('2017-07-01'),
            "academicYearEnd" => new \DateTime('2017-06-30'),
            "holidaysUrl" => "http://intranet.med.cornell.edu/hr/",

            "initialConfigurationCompleted" => false,

            "maintenance" => false,
            //"maintenanceenddate" => null,
            "maintenancelogoutmsg" =>   'The scheduled maintenance of this software has begun.'.
                ' The administrators are planning to return this site to a fully functional state on or before [[datetime]].',
                //'If you were in the middle of entering order information, it was saved as an "Unsubmitted" order '.
                //'and you should be able to submit that order after the maintenance is complete.',
            "maintenanceloginmsg" =>    'The scheduled maintenance of this software has begun.'.
                ' The administrators are planning to return this site to a fully functional state on or before [[datetime]].',
                //'If you were in the middle of entering order information, '.
                //'it was saved as an "Unsubmitted" order and you should be able to submit that order after the maintenance is complete.',

            //uploads
            "avataruploadpath" => "directory/avatars",
            "employeesuploadpath" => "directory/documents",
            "scanuploadpath" => "scan-order/documents",
            "fellappuploadpath" => "fellapp/documents",
            "vacrequploadpath" => "directory/vacreq",
            "transresuploadpath" => "transres/documents",

            "mainHomeTitle" => "Welcome to the O R D E R platform!",
            "listManagerTitle" => "List Manager",
            "eventLogTitle" => "Event Log",
            "siteSettingsTitle" => "Site Settings",

            ////////////////////////// LDAP notice messages /////////////////////////
            "noticeAttemptingPasswordResetLDAP" => "The password for your [[CWID]] can only be changed or reset by visiting the enterprise password management page or by calling the help desk at ‭1 (212) 746-4878‬.",
            //"noticeUseCwidLogin" => "Please use your CWID to log in",
            "noticeSignUpNoCwid" => "Sign up for an account if you have no CWID",
            "noticeHasLdapAccount" => "Do you (the person for whom the account is being requested) have a CWID username?",
            "noticeLdapName" => "Active Directory (LDAP)",
            ////////////////////////// EOF LDAP notice messages /////////////////////////

            "transresProjectSelectionNote" => 'If your project request involves collaboration with any
                                                <a target="_blank" href="https://pathology.weill.cornell.edu/clinical-services/hematopathology"
                                                >Weill Cornell Hematopathology faculty members</a>,<br>
                                                please press the "New Hematopathology Project Request" button.<br>
                                                For all other project requests, please press the "New AP/CP Project Request" button.',

            "contentAboutPage" => '
                <p>
                    This site is built on the platform titled "O R D E R" (as in the opposite of disorder).
                </p>

                <p>
                    Designers: Victor Brodsky, Oleg Ivanov
                </p>

                <p>
                    Developer: Oleg Ivanov
                </p>

                <p>
                    Quality Assurance Testers: Oleg Ivanov, Steven Bowe, Emilio Madrigal
                </p>

                <p>
                    We are continuing to improve this software. If you have a suggestion or believe you have encountered an issue, please don\'t hesitate to email
                <a href="mailto:'.$defaultSystemEmail.'" target="_top">'.$defaultSystemEmail.'</a> and attach relevant screenshots.
                </p>

                <br>

                <p>
                O R D E R is made possible by:
                </p>

                <br>

                <p>

                        <ul>


                    <li>
                        <a href="http://php.net">PHP</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://symfony.com">Symfony</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://doctrine-project.org">Doctrine</a>
                    </li>

                    <br>                  
					
					<li>
                        <a href="https://msdn.microsoft.com/en-us/library/aa366156.aspx">MSDN library: ldap_bind_s</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/symfony/SwiftmailerBundle">SwiftmailerBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/symfony/AsseticBundle">AsseticBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/FriendsOfSymfony/FOSUserBundle">FOSUserBundle</a>
                    </li>

                    <br>

                    <li>

                        <a href="https://github.com/1up-lab/OneupUploaderBundle">OneupUploaderBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.dropzonejs.com/">Dropzone JS</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.jstree.com/">jsTree</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/KnpLabs/KnpPaginatorBundle">KnpPaginatorBundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://twig.sensiolabs.org/doc/advanced.html">Twig</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://getbootstrap.com/">Bootstrap</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/kriskowal/q">JS promises Q</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://jquery.com">jQuery</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://jqueryui.com/">jQuery UI</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/RobinHerbots/jquery.inputmask">jQuery Inputmask</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://ivaynberg.github.io/select2/">Select2</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.eyecon.ro/bootstrap-datepicker/">Bootstrap Datepicker</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://www.malot.fr/bootstrap-datetimepicker/demo.php">Bootstrap DateTime Picker</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/twitter/typeahead.js/">Typeahead with Bloodhound</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://fengyuanchen.github.io/cropper/">Image Cropper</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://handsontable.com/">Handsontable</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/KnpLabs/KnpSnappyBundle">KnpSnappyBundle with wkhtmltopdf</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/myokyawhtun/PDFMerger">PDFMerger</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/bermi/password-generator">Password Generator</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/andreausu/UsuScryptPasswordEncoderBundle">Password Encoder</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://github.com/adesigns/calendar-bundle">jQuery FullCalendar bundle</a>
                    </li>

                    <br>

                    <li>
                        <a href="https://sciactive.com/pnotify/">PNotify JavaScript notifications</a>
                    </li>

                    <br>

                    <li>
                        <a href="http://casperjs.org/">CasperJS</a>
                    </li>

                </ul>
                </p>
            '
            //"underLoginMsgUser" => "",
            //"underLoginMsgScan => ""

        );

        //set default Third-Party Software Dependencies for Linux not used in container
        if( !$this->isWindows() ) {
            //set the same value as in setparameters.php run on deploy $wkhtmltopdfpath = "/usr/bin/xvfb-run /usr/bin/wkhtmltopdf";
            $types['wkhtmltopdfpathLinux'] = "/usr/bin/xvfb-run /usr/bin/wkhtmltopdf";

            //set other Linux parameters
            $types['libreOfficeConvertToPDFPathFellAppLinux'] = "/usr/lib/libreoffice/program";
            $types['libreOfficeConvertToPDFFilenameFellAppLinux'] = "soffice";
            $types['libreOfficeConvertToPDFArgumentsdFellAppLinux'] = "--headless -convert-to pdf -outdir";
            $types['pdftkPathFellAppLinux'] = "/usr/bin";
            $types['pdftkFilenameFellAppLinux'] = "pdftk";
            $types['pdftkArgumentsFellAppLinux'] = "###inputFiles### cat output ###outputFile### dont_ask";
            $types['gsPathFellAppLinux'] = "/usr/bin";
            $types['gsFilenameFellAppLinux'] = "gs";
            $types['gsArgumentsFellAppLinux'] = "-q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile= ###outputFile###  -c .setpdfwrite -f ###inputFiles###";
        }

        $params = new SiteParameters();

        $count = 0;
        foreach( $types as $key => $value ) {
            $method = "set".$key;
            $params->$method( $value );
            $count = $count + 10;
            $logger->notice("setter: $method");
        }

        //auto assign Institution
        $autoAssignInstitution = $userSecUtil->getAutoAssignInstitution();
        if( $autoAssignInstitution ) {
            $params->setAutoAssignInstitution($autoAssignInstitution);
            $logger->notice("Auto Assign Institution: $autoAssignInstitution");
        } else {
//            $institutionName = 'Weill Cornell Medical College';
//            $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName($institutionName);
//            if (!$institution) {
//                //throw new \Exception( 'Institution was not found for name='.$institutionName );
//            } else {
//                $params->setAutoAssignInstitution($institution);
//            }
            $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
            if( $wcmc ) {
                //exit('generateSiteParameters: No Institution: "WCM"');
                $mapper = array(
                    'prefix' => 'Oleg',
                    'bundleName' => 'UserdirectoryBundle',
                    'className' => 'Institution'
                );
                $autoAssignInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                    "Pathology and Laboratory Medicine",
                    $wcmc,
                    $mapper
                );
                if( $autoAssignInstitution ) {
                    $params->setAutoAssignInstitution($autoAssignInstitution);
                    $logger->notice("Auto Assign Generated Institution: $autoAssignInstitution");
                }
            }
        }
        $logger->notice("Finished with Auto Assign Institution");

        //set AllowPopulateFellApp to false
        $params->setAllowPopulateFellApp(false);

        $em->persist($params);
        $em->flush();

        if( $this->isWindows() ) {
            $emailUtil = $this->container->get('user_mailer_utility');
            $emailUtil->createEmailCronJob();
            $logger->notice("Created email cron job");
        } else {
            $this->createCronsLinux();
        }

        $logger->notice("Finished generateSiteParameters: count=".$count/10);

        return round($count/10);
    }

    public function isWindows() {
        if( substr(php_uname(), 0, 7) == "Windows" ){
            return true;
        }
        return false;
    }

    public function getGitVersionDate()
    {
        $ver = $this->getCurrentGitCommit('master');
        return $ver;


        $commitHash = $this->runProcess('git log --pretty="%h" -n1 HEAD');
        $commitDate = $this->runProcess('git log -n1 --pretty=%ci HEAD');
        $commitDateStr = null;
        if( $commitDate ) {
            $commitDateStr = $commitDate->format('Y-m-d H:m:s');
        }
        $ver = $commitHash . " (" . $commitDateStr . ")";
        //echo "ver=".$ver."<br>";
        //print_r($ver);
        return $ver;

        $MAJOR = 1;
        $MINOR = 2;
        $PATCH = 3;

        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));
        echo "hash=".$commitHash."<br>";

        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('UTC'));

        return $commitHash . " (" . $commitDate->format('Y-m-d H:m:s') . ")";
        //return sprintf('v%s.%s.%s-dev.%s (%s)', $MAJOR, $MINOR, $PATCH, $commitHash, $commitDate->format('Y-m-d H:m:s'));
    }

    /**
     * Get the hash of the current git HEAD
     * @param str $branch The git branch to check
     * @return mixed Either the hash or a boolean false
     */
    function getCurrentGitCommit( $branch='master' ) {
        $projectDir = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\scanorder\Scanorders2
        $projectDir = str_replace("Scanorders2","",$projectDir);
        $filename = $projectDir.".git".DIRECTORY_SEPARATOR."refs".DIRECTORY_SEPARATOR."heads".DIRECTORY_SEPARATOR.$branch;
        //echo $filename."<br>";

        //$filename = sprintf('.git/refs/heads/%s',$branch);
        $hash = file_get_contents($filename);
        $hash = trim($hash);

        $timestamp = filemtime($filename);
        if( $timestamp ) {
            $user = $this->secTokenStorage->getToken()->getUser();
            //$timestamp = date("F d Y H:i:s.",$timestamp);
            //$dateTime = new \DateTime($timestamp);
            $dateTime = new \DateTime();
            $dateTime->setTimestamp($timestamp);
            //$dateTimeUtc = \DateTime::createFromFormat('F d Y H:i:s',$timestamp);
            $dateTimeUser = $this->convertFromUtcToUserTimezone($dateTime,$user);
            $timestamp = $dateTimeUser->format("F d Y H:i");
        }

        if ( $hash ) {
            return "Current Version: " . $hash . "; " . $timestamp;
        } else {
            return false;
        }
    }


//    public function gitVersion() {
//        //exec('git describe --always',$version_mini_hash);
//        $version_mini_hash = $this->runProcess('git describe --always');
//        echo "version_mini_hash=".$version_mini_hash."<br>";
//        print_r($version_mini_hash);
//        exec('git rev-list HEAD | wc -l',$version_number);
//        exec('git log -1',$line);
//        $version['short'] = "v1.".trim($version_number[0]).".".$version_mini_hash[0];
//        $version['full'] = "v1.".trim($version_number[0]).".$version_mini_hash[0] (".str_replace('commit ','',$line[0]).")";
//        return $version;
//    }
    public function runProcess($command) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //echo 'This is a server using Windows!';
            $windows = true;
            $linux = false;
        } else {
            //echo 'This is a server not using Windows! Assume Linux';
            $windows = false;
            $linux = true;
        }

        $old_path = getcwd();
        //echo "webPath=$old_path<br>";

        $deploy_path = str_replace("web","",$old_path);
        //echo "deploy_path=$deploy_path<br>";
        //exit('111');

        if( is_dir($deploy_path) ) {
            //echo "deploy path exists! <br>";
        } else {
            //echo "not deploy path exists: $deploy_path <br>";
            exit('No deploy path exists in the filesystem; deploy_path=: '.$deploy_path);
        }

        //switch to deploy folder
        chdir($deploy_path);
        //echo "pwd=[".exec("pwd")."]<br>";

        if( $linux ) {
            $process = new Process($command);
            $process->setTimeout(1800); //sec; 1800 sec => 30 min
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $res = $process->getOutput();
        }

        if( $windows ) {
            $res = exec($command);
            //echo "res=".$res."<br>";
        }

        chdir($old_path);

        return $res;
    }

    public function classNameUrlMapper($className) {

        $mapArr = array(
            "SiteList"                  => "admin/list/sites",
            "User"                      => "user",
            "Patient"                   => "patient",
            "Message"                   => "entry/view",
            "Roles"                     => "admin/list-manager/id/4",
            "VacReqRequest"             => "show",
            "Document"                  => "file-view",
            "Institution"               => "admin/list/institutions",
            "FellowshipApplication"     => "show",
            "SiteParameters"            => "settings/settings-id", //"settings",
            "VacReqUserCarryOver"       => "show",
            "Project"                   => "project/show",
            "TransResRequest"           => "request/show",
            "DefaultReviewer"           => "default-reviewers/show",
            "Invoice"                   => "invoice/show",
        );

        $url = $mapArr[$className];

        return $url;
    }

    public function getSiteNameByAbbreviation($abbreviation) {
        $siteObject = $this->em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation($abbreviation);
        return $siteObject->getSiteName();
    }

    //TODO: generate two thumbnails: small and medium
    //get small thumbnail - i.e. used for the fellowship application list
    //get small thumbnail - i.e. used for the fellowship application view
    public function generateTwoThumbnails($document) {
        $res = NULL;
        $documentTypeObject = $document->getType();
        if( $documentTypeObject) {
            if( $documentTypeObject->getName() == "Fellowship Photo" || $documentTypeObject->getName() == "Avatar Image" ) {

                //$dest = $document->getAbsoluteUploadFullPath();
                //$dest = $document->getServerPath();
                //$dest = $document->getFullServerPath();

                $src = $document->getServerPath();
                $uniquename = $document->getUniquename();

//                if (file_exists($src)) {
//                    echo "The file $src exists <br>";
//                }
//                else {
//                    echo "The file $src does not exists <br>";
//                }

                //Small
                $desired_width = 65;
                $uniquenameSmall = "small" . "-" . $uniquename;
                $dest = str_replace($uniquename,$uniquenameSmall,$src);
                //echo $desired_width.": dest=".$dest."<br>";
                $destSmall = $this->makeThumb($src, $dest, $desired_width);

                //Medium
                $desired_width = 260;
                $uniquename = $document->getUniquename();
                $uniquenameSmall = "medium" . "-" . $uniquename;
                $dest = str_replace($uniquename,$uniquenameSmall,$src);
                //echo $desired_width.": dest=".$dest."<br>";
                $destMedium = $this->makeThumb($src, $dest, $desired_width);

                //exit(111);
                if( $destSmall || $destMedium ) {
                    $res = $destSmall . ", " . $destMedium;
                }
            }
        }
        return $res;
    }
    public function makeThumb($src, $dest, $desired_width) {

        if (file_exists($dest)) {
            //echo "The file $dest exists <br>";
            //$logger = $this->container->get('logger');
            //$logger->notice("$desired_width thumbnail already exists. dest=" . $dest);
            return null;
        }
        else {
            //echo "The file $dest does not exists <br>";
        }

        if( strpos($src, '.jpg') !== false || strpos($src, '.jpeg') !== false ) {
            //ok, file is jpeg
        } else {
            return null;
        }

        /* read the source image */
        $source_image = imagecreatefromjpeg($src);
        $width = imagesx($source_image);
        $height = imagesy($source_image);

        /* find the "desired height" of this thumbnail, relative to the desired width  */
        $desired_height = floor($height * ($desired_width / $width));

        /* create a new, "virtual" image */
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

        /* create the physical thumbnail image to its destination */
        imagejpeg($virtual_image, $dest);

        return $dest;
    }

    //Create cron jobs:
    //1) swiftMailer (implemented on email util (EmailUtil->createEmailCronJob))
    //2) importFellowshipApplications (every hour)
    //3) UnpaidInvoiceReminder (at 6 am every Monday)
    public function createCronsLinux() {

        $logger = $this->container->get('logger');
        $logger->notice("Creating cron jobs for Linux");

        //1) swiftMailer (implemented on email util (EmailUtil->createEmailCronJob))
        $emailUtil = $this->container->get('user_mailer_utility');
        $createEmailCronJob = $emailUtil->createEmailCronJobLinux();
        $logger->notice("Created email cron job: ".$createEmailCronJob);

        $projectDir = $this->container->get('kernel')->getProjectDir();
        $crontab = new Crontab();


        //////////////////// ImportFellowshipApplications ////////////////////
        //2) importFellowshipApplications (every hour)
        //Description: Import and Populate Fellowship Applications from Google Form
        //Command: php app/console cron:importfellapp --env=prod
        //Start in: E:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2
        //$fellappCronJobName = "ImportFellowshipApplications";

        $fellappCronJobCommand = "php ".$projectDir.DIRECTORY_SEPARATOR."bin/console cron:importfellapp --env=prod";

        $job = new Job();
        $job
            ->setMinute('0') //at minute 0 => every hour
            ->setHour('*')
            ->setDayOfMonth('*')
            ->setMonth('*')
            ->setDayOfWeek('*')
            ->setCommand($fellappCronJobCommand);

        //first delete existing cron job
        //$this->removeCronJob($crontab,$fellappCronJobCommand);

        if( !$this->isCronJobExists($crontab,$fellappCronJobCommand) ) {
            $crontab->addJob($job);
            //$crontab->write();
            $crontab->getCrontabFileHandler()->write($crontab);
            $logger->notice("Created importfellapp cron job");
        }

        //$res = $crontab->render();
        //////////////////// EOF ImportFellowshipApplications ////////////////////


        //////////////////// EOF UnpaidInvoiceReminder ////////////////////
        //3) UnpaidInvoiceReminder (at 6 am every Monday)
        //Description: Send reminder emails for unpaid invoices. Run every week on every Monday at 6am.
        //Command: php app/console cron:invoice-reminder-emails --env=prod
        //Start in: E:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2
        $fellappFrequency = 1; //hour

        $trpCronJobCommand = "php ".$projectDir.DIRECTORY_SEPARATOR."bin/console cron:invoice-reminder-emails --env=prod";

        $job = new Job();
        $job
            ->setMinute('00')
            ->setHour('06')
            ->setDayOfMonth('*')
            ->setMonth('*')
            ->setDayOfWeek('mon') //every monday (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
            ->setCommand($trpCronJobCommand);

        //first delete existing cron job
        //$this->removeCronJob($crontab,$trpCronJobCommand);

        if( !$this->isCronJobExists($crontab,$trpCronJobCommand) ) {
            $crontab->addJob($job);
            //$crontab->write();
            $crontab->getCrontabFileHandler()->write($crontab);
            $logger->notice("Created invoice-reminder-emails cron job");
        }

        //$res = $crontab->render();
        //////////////////// EOF UnpaidInvoiceReminder ////////////////////

        $res = $crontab->render();

        return $res;
    }
    public function isCronJobExists($crontab,$commandName) {
        foreach($crontab->getJobs() as $job) {
            //echo "job=".$job.", command=".$job->getCommand()."<br>";
            if( $commandName == $job->getCommand() ) {
                //echo "remove job ". $job."<br>";
                return true;
            }
        }
        return false;
    }
    public function removeCronJob($crontab,$commandName) {
        $resArr = array();
        foreach($crontab->getJobs() as $job) {
            //echo "job=".$job.", command=".$job->getCommand()."<br>";
            if( $commandName == $job->getCommand() ) {
                $resArr[] = $job."";
                $crontab->removeJob($job);
                $crontab->getCrontabFileHandler()->write($crontab);
            }
        }
        return implode("; ",$resArr);
    }
    public function getCronStatusLinux($crontab) {

        $res = '<font color="red">Cron job status: not found.</font>';
        //$crontab = new Crontab();
        $crontabRender = $crontab->render();
        if( $crontabRender ) {
            //$res = "Cron job status: " . $crontab->render();
            $res = '<font color="green">Cron job status: '.$crontab->render().'.</font>';
        }
        //exit($res);
        return $res;
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