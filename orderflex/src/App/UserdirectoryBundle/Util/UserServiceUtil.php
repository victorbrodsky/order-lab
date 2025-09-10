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

namespace App\UserdirectoryBundle\Util;



use App\SystemBundle\DynamicConnection\DoctrineMultidatabaseConnection;
use App\SystemBundle\DynamicConnection\PrimaryReadReplicaConnection;
use App\SystemBundle\DynamicConnection\DynamicConnectionWrapper;
use App\SystemBundle\DynamicConnection\DynamicEntityManager;

use App\UserdirectoryBundle\Entity\FosComment; //process.py script: replaced namespace by ::class: added use line for classname=FosComment
use App\UserdirectoryBundle\Entity\UserInfo; //process.py script: replaced namespace by ::class: added use line for classname=UserInfo
use App\OrderformBundle\Entity\PatientLastName; //process.py script: replaced namespace by ::class: added use line for classname=PatientLastName
use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\UserdirectoryBundle\Entity\PlatformListManagerRootList; //process.py script: replaced namespace by ::class: added use line for classname=PlatformListManagerRootList

use App\ResAppBundle\Entity\ResappSiteParameter;
use App\UserdirectoryBundle\Entity\BaseUserAttributes;
use App\UserdirectoryBundle\Entity\Permission;
use App\UserdirectoryBundle\Entity\Roles;
use App\UserdirectoryBundle\Entity\SiteList;
use App\UserdirectoryBundle\Entity\SiteParameters;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use App\VacReqBundle\Entity\VacReqSiteParameter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
//use Doctrine\ORM\Tools\Setup;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Entity\Logger;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
//use Symfony\Component\Security\Core\Encoder\EncoderFactory;
//use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Twilio\Rest\Client;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

//use Crontab\Crontab;
//use Crontab\Job;

class UserServiceUtil {

    protected $em;
    protected $doctrine;
    protected $security;
    protected $container;
    protected $m3;

    public function __construct( 
        EntityManagerInterface $em, 
        Security $security, 
        ContainerInterface $container, 
        ManagerRegistry $doctrine 
    ) {
        $this->em = $em;
        $this->doctrine = $doctrine;
        $this->security = $security;
        $this->container = $container;
    }

    public function getDoctrine() : ManagerRegistry
    {
        return $this->doctrine;
    }

    public function convertFromUserTimezonetoUTC($datetime,$user) {

        //$user_tz = 'America/New_York';
        $user_tz = $user->getPreferences()->getTimezone();

        //echo "input datetime=".$datetime->format('Y-m-d H:i')."<br>";
        $datetimeTz = new \DateTime($datetime->format('Y-m-d H:i:s'), new \DateTimeZone($user_tz) );
        $datetimeUTC = $datetimeTz->setTimeZone(new \DateTimeZone('UTC'));
        //echo "output datetime=".$datetimeUTC->format('Y-m-d H:i')."<br>";

        return $datetimeUTC;
    }

    public function convertFromUtcToUserTimezone($datetime,$user=null) {

        if( !$user ) {
            $user = $this->security->getUser();
        }

        if( !$user ) {
            return $datetime;
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
        $datetimeUTC = new \DateTime($datetime->format('Y-m-d H:i:s'), new \DateTimeZone('UTC') );
        $datetimeTz = $datetimeUTC->setTimeZone(new \DateTimeZone($user_tz));

        //echo "output datetime=".$datetimeUTC->format('Y-m-d H:i')."<br>";

        return $datetimeTz;
    }

    public function convertToUserTimezone($datetime,$user=null) {

        if( !$user ) {
            $user = $this->security->getUser();
        }

        //$user_tz = 'America/New_York';
        $user_tz = $user->getPreferences()->getTimezone();
        if( !$user_tz ) {
            $user_tz = "America/New_York";
        }

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
            $user = $this->security->getUser();
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
                $user = $this->security->getUser();
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
        //$user = $this->security->getUser();
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

        if( file_exists($pathMetaphone) ) {
            //echo "The file $pathMetaphone exists";
        } else {
            //echo "The file $pathMetaphone does not exist";
            $this->m3 = null;
            return null;
        }

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
        //echo "name=".$name.", version=$version <br>";

        $msg = "You appear to be using the <strong>outdated $name [v $version] browser on $platform</strong>
        and it is not able to show you this site properly.<br>
        Please use Chrome, Firefox, or the Edge browser instead and visit this page again.<br>
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

        if( str_contains($version, '.') ) {
            $versionArr = explode('.', $version, 2);
            $version = (int)$versionArr[0];
        }
        //echo "version=$version <br>";

        if( $name == Browser::IE ) {
            //Bootstrap IE 8+
            //Select2 IE 8+
            $msgIe = "You appear to be using the <strong>Internet Explorer (IE) which is officially retired on June 15, 2022</strong>
        and it is not able to show you this site properly.<br>
        Please use Chrome, Firefox, or the Edge browser instead and visit this page again.<br>
        You can copy the URL of this page and paste it into the
        address bar of the other browser once you switch to it.";
            return $msgIe;
        }

        if( $name == Browser::EDGE ) {
            if( $version < 77 ) {
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
            //echo "version=$version <br>";
            if( $version < 48 ) {
                //echo "$version < 48 <br>";
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
        $existedSignup = $this->em->getRepository('App\\UserdirectoryBundle\\Entity\\'.$className)->findByRegistrationLinkID($registrationLinkId);
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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FosComment'] by [FosComment::class]
        $repository = $this->em->getRepository(FosComment::class);
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

        //second common element (all in one page)
        $linkUrl = $this->container->get('router')->generate(
            $pathlink,
            array(
                'filter'=>'one-page',
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $href = '<li><a href="'.$linkUrl.'">'.'Employees in one page'.'</a></li>';
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
            if( strpos((string)$name, '[inst1]') !== false ) {
                if( strpos((string)$name, '[inst1]') !== false && strpos((string)$name, '[inst2]') === false ) {
                    $nameInst = str_replace('[inst1]',$inst1,$name);
                }
            }
        }

        if( $inst2 && !$inst1 ) {
            if( strpos((string)$name, '[inst2]') !== false ) {
                if( strpos((string)$name, '[inst2]') !== false && strpos((string)$name, '[inst1]') === false ) {
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

    //TODO: optimize by using AppUserdirectoryBundle:SiteParameters as a service to query from DB only once
    //Get single or generate SettingParameter (Singleton)
    public function getSingleSiteSettingParameter( $createIfEmpty=false ) {
        //return null; //testing

        //$logger = $this->container->get('logger');
        $entities = $this->em->getRepository(SiteParameters::class)->findAll();

        //make sure sitesettings is initialized
        if( count($entities) != 1 ) {
            //$logger->notice("getSingleSiteSettingParameter: SiteParameters count=".count($entities)."; createIfEmpty=".$createIfEmpty);
            if( $createIfEmpty ) {
                $this->generateSiteParameters();
            }
            $entities = $this->em->getRepository(SiteParameters::class)->findAll();
        }

        if( count($entities) != 1 ) {
            if( $createIfEmpty ) {
                throw new \Exception(
                    'getSingleSiteSettingParameter: Must have only one parameter object. Found '.
                    count($entities).' object(s)'."; createIfEmpty=".$createIfEmpty
                );
            } else {
                return null;
            }
        }

        return $entities[0];
    }

    public function generateSiteParameters() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->em;

        $entities = $em->getRepository(SiteParameters::class)->findAll();

        if( count($entities) > 0 ) {
            $logger->notice("Exit generateSiteParameters: SiteParameters has been already generated.");
            $resappCount = $this->generateSubSiteParameters();
            if( $resappCount ) {
                return $resappCount;
            }
            return -1;
        }

        $logger->notice("generateSiteParameters: Start generating SiteParameters");

        $types = array(
            "connectionChannel" => "http",

            "maxIdleTime" => "60",
            "environment" => "dev",
            "siteEmail" => "", //"email@email.com",
            "loginInstruction" => 'Please use your <a href="https://its.weill.cornell.edu/services/accounts-and-access/center-wide-id">CWID</a> to log in.',
            "remoteAccessUrl" => "https://its.weill.cornell.edu/services/wifi-networks/remote-access",
            
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
            "ldapExePath" => "../src/App/UserdirectoryBundle/Util/",
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
            //"codeGoogleFormFellApp" => "",
            //"confirmationEmailFellApp" => "",
            //"confirmationSubjectFellApp" => "Your WCM/NYP fellowship application has been succesfully received",
//            "confirmationBodyFellApp" => "Thank You for submitting the fellowship application to Weill Cornell Medical College/NewYork Presbyterian Hospital. ".
//                "Once we receive the associated recommendation letters, your application will be reviewed and considered. ".
//                "If You have any questions, please do not hesitate to contact me by phone or via email. ".
//                "Sincerely, Jessica Misner Fellowship Program Coordinator Weill Cornell Medicine Pathology and Laboratory Medicine 1300 York Avenue, Room C-302 T 212.746.6464 F 212.746.8192",
            //"clientEmailFellApp" => '',
            //"p12KeyPathFellApp" => 'E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\src\App\FellAppBundle\Util',
            //"googleDriveApiUrlFellApp" => "https://www.googleapis.com/auth/drive https://spreadsheets.google.com/feeds",
            //"userImpersonateEmailFellApp" => "olegivanov@pathologysystems.org",
            //"templateIdFellApp" => "",
            //"backupFileIdFellApp" => "",
            //"folderIdFellApp" => "",
            //"localInstitutionFellApp" => "Pathology Fellowship Programs (WCM)",
            //"deleteImportedAplicationsFellApp" => false,
            //"deleteOldAplicationsFellApp" => false,
            //"yearsOldAplicationsFellApp" => 2,
            //"spreadsheetsPathFellApp" => "fellapp/Spreadsheets",
            //"applicantsUploadPathFellApp" => "fellapp/FellowshipApplicantUploads",
            //"reportsUploadPathFellApp" => "fellapp/Reports",
            //"applicationPageLinkFellApp" => "http://wcmc.pathologysystems.org/fellowship-application",
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
//            "vacationAccruedDaysPerMonth" => '2',
//            "academicYearStart" => new \DateTime('2017-07-01'),
//            "academicYearEnd" => new \DateTime('2017-06-30'),
//            "holidaysUrl" => "http://intranet.med.cornell.edu/hr/",

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

            "externalMonitorUrl" => "https://view.med.cornell.edu/",
            "monitorScript" => "python webmonitor.py -l 'https://view.med.cornell.edu/' -h 'smtp.med.cornell.edu' -s 'oli2002@med.cornell.edu' -r 'oli2002@med.cornell.edu'",

            //uploads
            "avataruploadpath" => "directory/avatars",
            "employeesuploadpath" => "directory/documents",
            "scanuploadpath" => "scan-order/documents",
            "fellappuploadpath" => "fellapp/documents",
            "resappuploadpath" => "resapp/documents",
            "vacrequploadpath" => "directory/vacreq",
            "transresuploadpath" => "transres/documents",
            "callloguploadpath" => "calllog/documents",
            "crnuploadpath" => "crn/documents",

            "mainHomeTitle" => "Welcome to the O R D E R platform!",
            "listManagerTitle" => "List Manager",
            "eventLogTitle" => "Event Log",
            "siteSettingsTitle" => "Site Settings",

            ////////////////////////// LDAP notice messages /////////////////////////
            "noticeAttemptingPasswordResetLDAP" => "The password for your [[CWID]] can only be changed or reset by visiting the enterprise password management page or by calling the help desk at ‭1 (212) 746-4878‬.",
            //"noticeUseCwidLogin" => "Please use your CWID to log in",
            //"noticeSignUpNoCwid" => "Sign up for an account if you have no CWID",
            "noticeSignUpNoCwid" => "Sign Up",
            //"noticeHasLdapAccount" => 'Do you (the person for whom the account is being requested) have a <a href=\"https://its.weill.cornell.edu/services/accounts-and-access/center-wide-id\">CWID</a> username?',
            "noticeHasLdapAccount" => "Do you (the person for whom the account is being requested) have an existing institutional user name?",
            //"noticeLdapName" => "Active Directory (LDAP)",
            "noticeLdapName" => "Existing institutional user name in Active Directory or LDAP",
            ////////////////////////// EOF LDAP notice messages /////////////////////////

            ////////////////////// Global TRP parameters //////////////////
            "transresProjectSelectionNote" => 'If your project request involves collaboration with any
                                                <a target="_blank" href="https://pathology.weill.cornell.edu/divisions/hematopathology"
                                                >Weill Cornell Hematopathology faculty members</a>,<br>
                                                please press the "New Hematopathology Project Request" button.<br>
                                                For all other project requests, please press the "New AP/CP Project Request" button.',

            "transresBusinessEntityName" => "Center for Translational Pathology",

            "transresBusinessEntityAbbreviation" => "CTP",
            ////////////////////// EOF Global TRP parameters //////////////////

            ////////////////////// EOF Third-Party Software //////////////////

            "contentAboutPage" => $this->getContentAboutPage()
            //"underLoginMsgUser" => "",
            //"underLoginMsgScan => ""

        );

        //set default Third-Party Software Dependencies for Linux not used in container
        if( !$this->isWindows() ) {
            //set the same value as in setparameters.php run on deploy $wkhtmltopdfpath = "/usr/bin/xvfb-run /usr/bin/wkhtmltopdf";
            $types['wkhtmltopdfpathLinux'] = $wkhtmltopdfpath = "/usr/bin/xvfb-run wkhtmltopdf";
            //$types['wkhtmltopdfpathLinux'] = "/usr/bin/xvfb-run /usr/bin/wkhtmltopdf";
            //$types['wkhtmltopdfpathLinux'] = "xvfb-run wkhtmltopdf";

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
            $types['phantomjsLinux'] = "/opt/phantomjs-2.1.1-linux-x86_64/bin/phantomjs";
            $types['rasterizeLinux'] = "/usr/local/bin/order-lab/packer/rasterize.js";
            //$types[''] = "";
            //$types[''] = "";
        }

        $siteParameters = $this->em->getRepository(SiteParameters::class)->findAll();

        if( count($siteParameters) == 0 ) {
            $params = new SiteParameters();
        } else {
            $params = $siteParameters[0];
        }

        $count = 0;
        foreach( $types as $key => $value ) {
            $method = "set".$key;
            $params->$method( $value );
            $count = $count + 10;
            $logger->notice("generateSiteParameters setter: $method");
        }

        //auto assign Institution
        $autoAssignInstitution = $userSecUtil->getAutoAssignInstitution();
        if( $autoAssignInstitution ) {
            $params->setAutoAssignInstitution($autoAssignInstitution);
            $logger->notice("Auto Assign Institution: $autoAssignInstitution");
        } else {
//            $institutionName = 'Weill Cornell Medical College';
//            $institution = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName($institutionName);
//            if (!$institution) {
//                //throw new \Exception( 'Institution was not found for name='.$institutionName );
//            } else {
//                $params->setAutoAssignInstitution($institution);
//            }
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
            if( $wcmc ) {
                $mapper = array(
                    'prefix' => 'App',
                    'bundleName' => 'UserdirectoryBundle',
                    'className' => 'Institution',
                    'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
                    'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
                );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                $autoAssignInstitution = $em->getRepository(Institution::class)->findByChildnameAndParent(
                    "Pathology and Laboratory Medicine",
                    $wcmc,
                    $mapper
                );
                if( $autoAssignInstitution ) {
                    $params->setAutoAssignInstitution($autoAssignInstitution);
                    $logger->notice("Auto Assign Generated Institution: $autoAssignInstitution");
                }
            } else {
                //exit('generateSiteParameters: No Institution: "WCM"');
                $logger->notice("Auto Assign Generated Institution is not set: No Institution found by abbreviation 'WCM'");
            }
        }
        $logger->notice("Finished with Auto Assign Institution");

        $em->persist($params);
        $em->flush();

//        if( $this->isWindows() ) {
//            $emailUtil = $this->container->get('user_mailer_utility');
//            $emailUtil->createEmailCronJob();
//            $logger->notice("Created email cron job");
//        } else {
//            $this->createCronsLinux();
//        }
        $this->createCrons();

        $resappCount = $this->generateSubSiteParameters();
        $count = $count + $resappCount;

        $logger->notice("Finished generateSiteParameters: count=".$count/10);

        return round($count/10);
    }
    public function getContentAboutPage() {
        $defaultSystemEmail = $this->container->getParameter('default_system_email');
        $contentAboutPage =
        '
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
        ';
        return $contentAboutPage;
    }

    public function generateSubSiteParameters() {
        $count = 0;
        $count = $count + $this->generateVacReqSiteParameters();
        $count = $count + $this->generateResAppSiteParameters();
        return $count;
    }

    public function generateVacReqSiteParameters() {
        $logger = $this->container->get('logger');
        //$userSecUtil = $this->container->get('user_security_utility');
        $em = $this->em;

        $entities = $em->getRepository(SiteParameters::class)->findAll();

        $siteParameters = null;
        if( count($entities) > 0 ) {
            $siteParameters = $entities[0];
        }

        if( !$siteParameters ) {
            $logger->notice("generateVacReqSiteParameters failed: SiteParameters does not exist.");
            return 0;
        }

        if( $siteParameters->getVacreqSiteParameter() ) {
            $logger->notice("VacReqSiteParameter already exists.");
            return 0;
        }

        $logger->notice("Start generating VacReqSiteParameter");

        $nowDate = new \DateTime();
        $floatingDayNote =  "The Juneteenth Holiday may be used as a floating holiday ".
                            "only if you have an NYPH appointment. You can request a floating holiday however, ".
                            "it must be used in the same fiscal year ending June 30, ".$nowDate->format('Y').". ".
                            "It cannot be carried over.";

        $types = array(
            //"academicYearStart" => null,
            //"academicYearEnd" => null,
            "academicYearStart" => new \DateTime('2017-07-01'),
            "academicYearEnd" => new \DateTime('2017-06-30'),
            "holidaysUrl" => "http://intranet.med.cornell.edu/hr/",
            //"vacationAccruedDaysPerMonth" => 2,
            //"maxVacationDays" => 24,
            //"maxCarryOverVacationDays" => 15,
            //"noteForVacationDays" => null,
            //"noteForCarryOverDays" => "As per policy, the number of days that can be carried over to the following year is limited to the maximum of 15",
            "floatingDayName" => "Floating Day",
            "floatingDayNote" => $floatingDayNote,
            "floatingRestrictDateRange" => true,
            "enableFloatingDay" => true,
        );

        $params = new VacReqSiteParameter();

        $count = 0;
        foreach( $types as $key => $value ) {
            $method = "set".$key;
            $params->$method( $value );
            $count = $count + 10;
            $logger->notice("generateVacReqSiteParameters setter: $method");
        }


        if( $count > 0 ) {
            $siteParameters->setVacreqSiteParameter($params);

            $em->persist($params);
            $em->flush();
        }

        $logger->notice("Finished generateVacReqSiteParameters: count=".$count/10);

        return round($count/10);
    }

    public function generateResAppSiteParameters() {
        $logger = $this->container->get('logger');
        //$userSecUtil = $this->container->get('user_security_utility');
        $em = $this->em;

        $entities = $em->getRepository(SiteParameters::class)->findAll();

        $siteParameters = null;
        if( count($entities) > 0 ) {
            $siteParameters = $entities[0];
        }

        if( !$siteParameters ) {
            $logger->notice("generateResAppSiteParameters failed: SiteParameters does not exist.");
            return 0;
        }

        if( $siteParameters->getResappSiteParameter() ) {
            $logger->notice("ResappSiteParameters already exists.");
            return 0;
        }

        $logger->notice("generateResAppSiteParameters: Start generating SiteParameters");


        $types = array(
            "acceptedEmailSubject" => "Congratulations on your acceptance to the [[RESIDENCY TYPE]] [[START YEAR]] residency at Weill Cornell Medicine",
            "acceptedEmailBody" => "Dear [[APPLICANT NAME]],

We are looking forward to having you join us as a [[RESIDENCY TYPE]] fellow in [[START YEAR]]!

Weill Cornell Medicine",

            "rejectedEmailSubject" => "Thank you for applying to the [[RESIDENCY TYPE]] [[START YEAR]] residency at Weill Cornell Medicine",

            "rejectedEmailBody" => "Dear [[APPLICANT NAME]],

We have reviewed your application to the [[RESIDENCY TYPE]] residency for [[START YEAR]], and we regret to inform you that we are unable to offer you a position at this time. Please contact us if you have any questions.

Weill Cornell Medicine",

            "confirmationSubjectResApp" => "Your WCM/NYP residency application has been successfully received",

            "confirmationBodyResApp" => "Thank You for submitting the residency application to Weill Cornell Medicine/NewYork Presbyterian Hospital.

Once we receive the associated recommendation letters, your application will be reviewed and considered.

If You have any questions, please do not hesitate to contact me by phone or via email.


Sincerely,

Residency Program Coordinator
Weill Cornell Medicine
Pathology and Laboratory Medicine",

            "localInstitutionResApp" => "Pathology Residency Programs (WCM)",
            "spreadsheetsPathResApp" => "resapp/Spreadsheets",
            "applicantsUploadPathResApp" => "resapp/ResidencyApplicantUploads",
            "reportsUploadPathResApp" => "resapp/Reports"
        );

        //testing
//        $params = $siteParameters->getResappSiteParameter();
//        if( !$params ) {
//            $params = new ResappSiteParameter();
//        }

        $params = new ResappSiteParameter();

        $count = 0;
        foreach( $types as $key => $value ) {
            $method = "set".$key;
            $params->$method( $value );
            $count = $count + 10;
            $logger->notice("generateResAppSiteParameters setter: $method");
        }


        if( $count > 0 ) {
            $siteParameters->setResappSiteParameter($params);

            $em->persist($params);
            $em->flush();
        }

        $logger->notice("Finished generateResAppSiteParameters: count=".$count/10);

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
        //$ver = $this->getCurrentGitCommit();
        //return $ver;

        $commitHash = $this->runProcess('git log --pretty="%h" -n1 HEAD');
        //$commitHash = $this->runProcess('pwd');
        $commitDate = $this->runProcess('git log -n1 --pretty=%ci HEAD');
        $commitDateStr = null;
        if( $commitDate ) {
            $commitDateStr = $commitDate->format('Y-m-d H:m:s');
        }
        $ver = $commitHash . " (" . $commitDateStr . ")";
        //echo "ver=".$ver."<br>";
        //print_r($ver);
        return $ver;

//        $MAJOR = 1;
//        $MINOR = 2;
//        $PATCH = 3;
//
//        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));
//        echo "hash=".$commitHash."<br>";
//
//        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
//        $commitDate->setTimezone(new \DateTimeZone('UTC'));
//
//        return $commitHash . " (" . $commitDate->format('Y-m-d H:m:s') . ")";
        //return sprintf('v%s.%s.%s-dev.%s (%s)', $MAJOR, $MINOR, $PATCH, $commitHash, $commitDate->format('Y-m-d H:m:s'));
    }

    /**
     * Get all branches: the hash of the current git HEAD
     */
    function getCurrentGitCommit() {
        $projectDir = $this->container->get('kernel')->getProjectDir();
        $path = $projectDir.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.".git"
            .DIRECTORY_SEPARATOR."refs".DIRECTORY_SEPARATOR."heads";

        $resArr = array();
        $res = "";

        if( $handle = opendir($path) ) {

            while (false !== ($entry = readdir($handle))) {

                if( $entry != "." && $entry != ".." ) {

                    //echo "$entry\n";
                    $branch = trim((string)$entry);
                    $resArr[] = $this->getBranchGitCommit($branch,$path);
                }
            }

            closedir($handle);
        }

        //$resArr[] = "Current Git: ".$this->getGitVersionDate();

        if( count($resArr) > 0 ) {
            $res = implode("<br>",$resArr);
        }

        return $res;
    }
    /**
     * Get the hash of the current git HEAD
     * @param str $branch The git branch to check
     * @return mixed Either the hash or a boolean false
     */
    function getBranchGitCommit( $branch='master', $path=NULL ) {
        $projectDir = $this->container->get('kernel')->getProjectDir(); //C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\scanorder\Scanorders2
        //echo "projectDir=$projectDir<br>";
        //$projectDir = str_replace("Scanorders2","",$projectDir);

        if( !$path ) {
            $path = $projectDir . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".git" . DIRECTORY_SEPARATOR .
                "refs" . DIRECTORY_SEPARATOR . "heads";
        }

        $filename = $path.DIRECTORY_SEPARATOR.$branch;
        //echo $filename."<br>";

        if( file_exists($filename) ) {
            //OK
        } else {
            return false;
        }

        //$filename = sprintf('.git/refs/heads/%s',$branch);
        $hash = file_get_contents($filename);
        $hash = trim((string)$hash);

        $timestamp = filemtime($filename);
        if( $timestamp ) {
            $user = $this->security->getUser();
            //$timestamp = date("F d Y H:i:s.",$timestamp);
            //$dateTime = new \DateTime($timestamp);
            $dateTime = new \DateTime();
            $dateTime->setTimestamp($timestamp);
            //$dateTimeUtc = \DateTime::createFromFormat('F d Y H:i:s',$timestamp);
            $dateTimeUser = $this->convertFromUtcToUserTimezone($dateTime,$user);
            $timestamp = $dateTimeUser->format("F d Y H:i");
        }

        if ( $hash ) {
            return "Current Version for branch $branch: " . $hash . "; " . $timestamp;
        } else {
            return false;
        }
    }

    /**
     * Get installed software (apache, php)
     */
    function getInstalledSoftware() {
        $res = NULL;
        $apacheVersion = NULL;
//        if(!function_exists('apache_get_version')){
//            function apache_get_version(){
//                if(!isset($_SERVER['SERVER_SOFTWARE']) || strlen((string)$_SERVER['SERVER_SOFTWARE']) == 0){
//                    return false;
//                }
//                return $_SERVER["SERVER_SOFTWARE"];
//            }
//        }
        //$apacheVersion = $_SERVER["SERVER_SOFTWARE"];
        //$apacheVersion = apache_get_version();

        if( !$apacheVersion ) {
            if (function_exists('apache_get_version')) {
                $apacheVersion = apache_get_version();
            }
        }
        if( !$apacheVersion ) {
            if (!isset($_SERVER['SERVER_SOFTWARE']) || strlen((string)$_SERVER['SERVER_SOFTWARE']) == 0) {
            } else {
                $apacheVersion = $_SERVER["SERVER_SOFTWARE"];
            }
        }

        //OS name and version
        $res = "OS: " . php_uname();

        if( $apacheVersion ) {
            $res = $res . "<br>" . "Apache: " . $apacheVersion;
        }

        $phpVersion = phpversion();
        $res = $res . "<br>" . "PHP: ".$phpVersion;
        $phpVersion2 = PHP_VERSION;
        $res = $res . "<br>" . "PHP_VERSION: ".$phpVersion2;

        //Get DB version
        $dbInfo = $this->getDbVersion();
        $res = $res . "<br>" . "DB: ".$dbInfo;

        //$dbName = $this->container->getParameter('database_name');
        $dbName = $this->em->getConnection()->getDatabase();
        $res = $res . "<br>" . "DB Name: ".$dbName;

        $host= gethostname();
        $ip = gethostbyname($host);
        $serverAddr = null;
        if( isset($_SERVER['SERVER_ADDR']) ) {
            $serverAddr = $_SERVER['SERVER_ADDR'];
        }
        if( $serverAddr && $ip != $serverAddr ) {
            $ip = $ip . " (". $serverAddr . ")";
        }
        $res = $res . "<br>" . "IP: " . $ip;

        //tenant base url
        if( $this->container->hasParameter('tenant_base') ) {
            $tenant_base = $this->container->getParameter('tenant_base');
            $res = $res . "<br>" . "Tenant base url: " . $tenant_base;
        }

        //connection_channel
        $res = $res . "<br>Note: If using HaProxy, internal connection channel should be 'http' and external connection channel should be 'https'";
        $connection_channel = $this->container->getParameter('connection_channel');
        $res = $res . "<br>" . "Internal connection channel: " . $connection_channel;

        //Real connection_channel if behind HaProxy
        $userUtil = $this->container->get('user_utility');
        $realScheme = $userUtil->getRealScheme();
        $res = $res . "<br>" . "External connection channel if using HaProxy: " . $realScheme;

        return $res;
    }

    function getFrameworkInfo() {
        $res = null;

        $projectRoot = $this->container->get('kernel')->getProjectDir();

        $phpPath = $this->getPhpPath();

        $command = $phpPath . " " . $projectRoot . "/bin/console about";

        //$process = new Process($command);
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(1800); //sec; 1800 sec => 30 min
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $info = $process->getOutput();

        //remove api secret
        $apiInfo = $this->getStringBetween($info,"APP_SECRET","DATABASE_URL");
        //echo "apiInfo=$apiInfo <br>";
        $info = str_replace($apiInfo, " *****<br>", $info);

        //$divider = "-------------------- ---------------------------------------------------------------------------------------";
        $divider = "\n";

        $replace = "$divider<br>";

        $info = str_replace($divider, $replace, $info);

        $res = $res . $info;

        return $res;
    }

    public function getStringBetween($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    //convert "AppFellAppBundle:FellowshipApplication" to App\FellAppBundle\Entity\FellowshipApplication
    public function convertNamespaceToClasspath($commentclass) {
        $classpath = null;
        $app = null;
        $bundle = null;
        $entity = "Entity";
        $className = null;

        if( str_contains($commentclass,"App") ) {
            $app = "App";
        }
        
        if( str_contains($commentclass,":") ) {
            $bundle = $this->getStringBetween($commentclass,$app,":"); //$string, $start, $end

            $pieces = explode(":", $commentclass);
            $className = $pieces[1]; //FellowshipApplication
        }

        if( $app && $entity && $className ) {
            $classpath = $app . "\\" . $bundle . "\\" . $entity . "\\" . $className;
            echo "classpath=$classpath <br>";
        }

        return $classpath;
    }

    public function getPhpPath() {
        $phpPath = "php";

        if( $this->isWinOs() ) {
            $phpPath = "php";
        } else {
//            $process = new Process("which php");
//            $process->setTimeout(1800); //sec; 1800 sec => 30 min
//            $process->run();
//            if (!$process->isSuccessful()) {
//                throw new ProcessFailedException($process);
//            }
//            $phpPath = $process->getOutput();
            //$phpPath = "/opt/remi/php74/root/usr/bin/php";
            //$phpPath = "/opt/remi/php81/root/usr/bin/php";
            $phpPath = "/opt/remi/php82/root/usr/bin/php";

            //$phpPath = "php";

            if( !file_exists($phpPath) ) {
                $phpPath = "/bin/php";
            }

            if( !file_exists($phpPath) ) {
                $phpPath = "php";
            }
        }

        return $phpPath;
    }

    public function getDbVersion() {
        //php bin/console dbal:run-sql 'SELECT version()'
        $projectRoot = $this->container->get('kernel')->getProjectDir();
        $phpPath = $this->getPhpPath();

        if( $this->isWinOs() ) {
            $command = $phpPath . " " . $projectRoot . "/bin/console" . " dbal:run-sql" . ' "SELECT version()"';
        } else {
            $command = $phpPath . " " . $projectRoot . "/bin/console" . " dbal:run-sql" . " 'SELECT version()'";
        }
        //echo "command=$command <br>";

        //$process = new Process($command);
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(1800); //sec; 1800 sec => 30 min
        $process->run();
        if( !$process->isSuccessful() ) {
            throw new ProcessFailedException($process);
        }
        $info = $process->getOutput();

        //dump($info);
        //exit('111');

        if( $info ) {
            //$dbInfo = $this->getStringBetween($info,') "','"');
            $dbInfo = $this->getStringBetween($info,'version','\r\n');
            $dbInfo = trim($info);
            $dbInfo = str_replace("version","",$dbInfo);
            $dbInfo = str_replace("-","",$dbInfo);
            return $dbInfo;
        }

        return $info;
    }

    function getDBVersionStr() {
        $postgreVersion = null;
        $dbInfo = $this->getDbVersion(); //PostgreSQL 14.3, compiled by Visual C++ build 1914, 64bit
//        if( str_contains($dbInfo, 'PostgreSQL 14') ) {
//            $postgreVersion = "postgresql-14";
//        }
//        if( str_contains($dbInfo, 'PostgreSQL 15') ) {
//            $postgreVersion = "postgresql-15";
//        }
//        if( str_contains($dbInfo, 'PostgreSQL 16') ) {
//            $postgreVersion = "postgresql-16";
//        }
//        if( str_contains($dbInfo, 'PostgreSQL 17') ) {
//            $postgreVersion = "postgresql-17";
//        }
        if( !$postgreVersion ) {
            $dbInfo = trim($dbInfo);
            $dbInfoArr = explode(' ', $dbInfo);
            if( count($dbInfoArr) > 2 ) {
                $dbver = $dbInfoArr[1]; //14.3
                $dbver = strtok($dbver, '.');
                if( $dbver ) {
                    $postgreVersion = "postgresql-$dbver";
                }
            }
        }
        return $postgreVersion;
    }

    public function restartDb() {
        $postgreVersionStr = $this->getDBVersionStr(); //postgresql-14
        $dbRestartCommand = "sudo systemctl restart $postgreVersionStr";
        return $this->runCommandByPython( $dbRestartCommand );
    }
    public function restartApache() {
        $apacheRestartCommand = "sudo systemctl restart httpd.service";
        return $this->runCommandByPython( $apacheRestartCommand );
    }
    public function runCommandByPython( $command ) {
        //restart DB sudo systemctl restart postgresql-14
        //$postgreVersionStr = $this->getDBVersionStr(); //postgresql-14
        //$dbRestartCommand = "sudo systemctl restart $postgreVersionStr";
        //echo "command=$command <br>";

        $projectRoot = $this->container->get('kernel')->getProjectDir();
        //$projectRoot = str_replace('order-lab', '', $projectRoot);
        $parentRoot = str_replace('orderflex', '', $projectRoot);
        $parentRoot = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, '', $parentRoot);

        $managePackagePath = $parentRoot .
            //DIRECTORY_SEPARATOR . 'order-lab' .
            //DIRECTORY_SEPARATOR . "utils" .
            "utils" .
            DIRECTORY_SEPARATOR . "db-manage" .
            DIRECTORY_SEPARATOR . "postgres-manage-python";
        //echo "managePackagePath=$managePackagePath <br>";

        $pythonScriptPath = $managePackagePath . DIRECTORY_SEPARATOR . "restart_db.py";

        $pyCommand = "python '$pythonScriptPath' --command  '$command'";
        //echo "pyCommand=$pyCommand <br>";

        $res = $this->runProcess($pyCommand);

        return $res;
    }

//    public function gitVersion() {
//        //exec('git describe --always',$version_mini_hash);
//        $version_mini_hash = $this->runProcess('git describe --always');
//        echo "version_mini_hash=".$version_mini_hash."<br>";
//        print_r($version_mini_hash);
//        exec('git rev-list HEAD | wc -l',$version_number);
//        exec('git log -1',$line);
//        $version['short'] = "v1.".trim((string)$version_number[0]).".".$version_mini_hash[0];
//        $version['full'] = "v1.".trim((string)$version_number[0]).".$version_mini_hash[0] (".str_replace('commit ','',$line[0]).")";
//        return $version;
//    }
    public function runProcess($command) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //echo 'This is a server using Windows!';
            $windows = true;
            $linux = false;

            //$windows = false;
            //$linux = true;
        } else {
            //echo 'This is a server not using Windows! Assume Linux';
            $windows = false;
            $linux = true;
        }

        $old_path = getcwd();
        //echo "webPath=$old_path<br>";

        $deploy_path = str_replace("public","",$old_path);
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
            //$process = new Process($command);
            $process = Process::fromShellCommandline($command);
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

    function runSymfonyProcessRealTime(array $command): void {
        $process = new Process($command);
        $process->setTimeout(0); // Optional: Disable timeout for long-running processes

        // Start the process and display output in real time
        $process->start(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'Error: ' . $buffer;
            } else {
                echo $buffer;
            }
        });

        // Wait for the process to finish
        $process->wait();
    }

    //Use this simple exec similarly to execInBackground, instead of using runAsyncProcess
    //The most frequent reason is that the main PHP-FPM process finishes serving
    // the web request and returns a response to the client. When this happens,
    // PHP-FPM may terminate its child process, which in turn can kill any child
    // processes it spawned, including the one started by Symfony's Process component.
    // The Process::start() method initiates an asynchronous process,
    // but it doesn't guarantee its independent execution beyond
    // the life of the parent PHP-FPM process.
    public function runAsyncExecProcess( $cmd ) {
        $logger = $this->container->get('logger');
        $logger->notice("runAsyncExecProcess cmd=" . $cmd);
        //echo exec($cmd, $oExec);
        $oExec = exec($cmd . " > /dev/null &");
        // /dev/null - discards all data written to it — like a black hole for output,
        // & - Runs the command in the background, so PHP doesn’t wait for it to finish
        //return $oExec;
        $res = "The Python script has been launched asynchronously.".
            " Progress updates will be sent to the admin via email.";
        return $res;
    }

    //Process::start() initiates a process asynchronously, meaning PHP's execution
    // continues without waiting for the external process to complete.
    // If the PHP script finishes and returns a response before the
    // Python script has fully spawned or executed,
    // the operating system might terminate the Python process.
    //Use $process->wait() after $process->start() to pause the PHP script until the external process finishes

    //The most frequent reason is that the main PHP-FPM process finishes serving
    // the web request and returns a response to the client. When this happens,
    // PHP-FPM may terminate its child process, which in turn can kill any child
    // processes it spawned, including the one started by Symfony's Process component.
    // The Process::start() method initiates an asynchronous process,
    // but it doesn't guarantee its independent execution beyond
    // the life of the parent PHP-FPM process.

    //Run similarly to execInBackground
    //Or use Symfony Messenger or a queue system
    //Option 1: Use  with  (Minimalist)
    //exec('nohup /srv/order-lab-tenantapptest/utils/db-manage/postgres-manage-python/venv/bin/python /srv/order-lab-tenantapptest/utils/db-manage/postgres-manage-python/manage_postgres_db.py > /tmp/db_restore.log 2>&1 &');

    //Installed:
    //sudo dnf install audit
    // sudo dnf install -y bpftrace
    //sudo bpftrace -e 'tracepoint:syscalls:sys_enter_execve { printf("PID %d started %s\n", pid, comm); }'
    //sudo bpftrace -e 'tracepoint:sched:sched_process_exit { printf("PID %d (%s) exited with code %d\n", pid, comm, args->exit_code >> 8); }'
/*
sudo bpftrace -e '
tracepoint:syscalls:sys_enter_execve
{
  printf("(%s) + PID %d (%s)\n", strftime("%H:%M:%S", nsecs), pid, comm);
}

tracepoint:sched:sched_process_exit
{
  printf("(%s) - PID %d (%s) exited\n", strftime("%H:%M:%S", nsecs), pid, comm);
}
'
*/

    //Use: sudo dnf install audit
    //sudo systemctl enable --now auditd
//sudo auditctl -a always,exit -F arch=b64 -F pid=<your_pid> -S execve -k symfony_process_exec
//sudo auditctl -a always,exit -F arch=b64 -F pid=<your_pid> -S exit -k symfony_process_exit


    public function runAsyncProcess($commandArr) {
        $logger = $this->container->get('logger');
        $commandArrStr = implode(" ",$commandArr);
        $logger->notice("runAsyncProcess: commandArr=[".$commandArrStr."]");
        $process = new Process($commandArr);
        $process->setTimeout(null);

        //Testing
        if(0) {
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            //echo $process->getOutput();
            $logger->notice($process->getOutput());
            return $process->getOutput();
        }

        if(1) {
            $process->start(); // Starts the process asynchronously
            if ($process->isRunning()) {
                $logger->notice("Process started successfully");
            } else {
                $logger->notice("Process failed to start");
            }
            //usleep(500000); // wait 0.5 seconds
            sleep(5); // Pauses for 5 seconds
            //Working because of while
//            while ($process->isRunning()) {
//                $logger->notice('runAsyncProcess: running ...');
//            }
//            if ($process->isSuccessful()) {
//                $logger->notice('runAsyncProcess: OK');
//            } else {
//                $logger->notice('runAsyncProcess: Error');
//            }
            //sleep(60);
            //$logger->notice("After sleep: ".$process->getOutput()); // Should print "done"
            //return "Test: ".$process->getOutput();
            //$process->wait();
        }

        if(0) {
            $process->start(function ($type, $buffer) {
                $logger = $this->container->get('logger');
                $logger->notice('runAsyncProcess: starting...');
                if (Process::ERR === $type) {
                    //echo 'Error: ' . $buffer;
                    $logger->notice('runAsyncProcess: ERR=' . $buffer);
                }
                if ($type === Process::OUT) {
                    $logger->notice('runAsyncProcess: buffer=' . $buffer);
                }
            });
        }

        // Optional: log the PID or check if it's running
        $logger = $this->container->get('logger');
        $res = 'Started Python script with PID: ' . $process->getPid() .
        " You will get a notification email when completed.";
        $logger->notice($res);

        return $res;
    }
    public function runAsyncProcessWithEmail( $action, $command ) {
        $logger = $this->container->get('logger');
        $logger->notice('runAsyncProcessWithEmail: Starting ...');

        $process = Process::fromShellCommandline($command);
        //$process = new Process($commandArr);

        if(1) {
            $process->start(function ($type, $buffer) use ($action) {
                $logger = $this->container->get('logger');
                $logger->notice('runAsyncProcessWithEmail: any buffer=' . $buffer);
                if ($type === Process::ERR) {
                    echo "STDERR: " . $buffer;
                }
                if ($type === Process::OUT) {
                    echo "STDOUT: " . $buffer;
                    $logger = $this->container->get('logger');
                    $logger->notice('runAsyncProcessWithEmail: out buffer=' . $buffer);
                    if (str_contains($buffer, 'trigger-successful-email')) {
                        $logger->notice('runAsyncProcessWithEmail: trigger-successful-email');
                        $this->completeDbActionEmail($action,'Success');
                    }
                    if (str_contains($buffer, 'trigger-error-email')) {
                        $logger->notice('runAsyncProcessWithEmail: trigger-error-email');
                        $this->completeDbActionEmail($action,'Fail');
                    }
                }
            });
        }

//        $process->start(function ($type, $buffer) use ($logger) {
//            $logger->notice("Output ($type): $buffer");
//        });

        if(0) {
            $process->start();
            while ($process->isRunning()) {
                $logger->notice('runAsyncProcessWithEmail: running ...');
            }
            if ($process->isSuccessful()) {
                $logger->notice('runAsyncProcessWithEmail: OK');
            } else {
                $logger->notice('runAsyncProcessWithEmail: Error');
            }
        }

        if(0) {
            $process->start(function ($type, $buffer) {
                $logger = $this->container->get('logger');
                $logger->notice('runAsyncProcessWithEmail: starting...');
                if (Process::ERR === $type) {
                    //echo 'Error: ' . $buffer;
                    $logger->notice('runAsyncProcessWithEmail: ERR=' . $buffer);
                } else {
                    $logger->notice('runAsyncProcessWithEmail: buffer=' . $buffer);
                }
            });
        }

//        if(0) {
//            $process->start();
//            //foreach make it synchronous
//            foreach ($process->getIterator() as $type => $data) {
//                if ($type === Process::OUT) {
//                    $logger = $this->container->get('logger');
//                    $logger->notice('runAsyncProcessWithEmail: out data=' . $data);
//                    if (str_contains($data, 'trigger-successful-email')) {
//                        $logger->notice('runAsyncProcessWithEmail: trigger-successful-email');
//                        $this->completeDbActionEmail('Success');
//                    }
//                }
//            }
//        }

        // Optional: log the PID or check if it's running
        $logger = $this->container->get('logger');
        $res = 'Started script with PID: ' . $process->getPid() .
            " You will get a notification email when completed.";
        $logger->notice($res);

        return $res;
    }
    public function completeDbActionEmail( $status, $message ) {
        $logger = $this->container->get('logger');
        $logger->notice('completeDbActionEmail');
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');
        $user = $this->security->getUser();
        //$environment = $userSecUtil->getSiteSettingParameter('environment');
        $emails = $userSecUtil->getUserEmailsByRole(null,"Platform Administrator");
        //siteEmail
        $sender = $userSecUtil->getSiteSettingParameter('siteEmail'); //might be adminemail@example.com
        if( $sender ) {
            if( $emails ) {
                $emails[] = $sender;
            } else {
                $emails = array($sender);
            }
        }
        //$emails[] = $user->getSingleEmail();
        $emails = array_values(array_diff($emails, ["adminemail@example.com"]));

        $status = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
        $status = escapeshellarg($status);

        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $message = escapeshellarg($message);
        
        $subject = "Action completed: status=$status";
        $msg = "Action completed: status=$status, message=$message";

//        if( $success ) {
//            $subject = "DB restore completed successfully";
//            $msg = "DB restore completed successfully";
//        } else {
//            $subject = "DB restore completed with error";
//            $msg = "DB restore completed with error";
//        }

        $logger->notice('completeDbActionEmail: before send email, emails='.implode(", ",$emails));
        $emailUtil->sendEmail($emails,$subject,$msg);

        $logger->notice('completeDbActionEmail: before event log');
        //Event Log
        if( $status == 'backup' ) {
            $eventType = "Create Backup Database";
        } elseif ( $status == 'restore' ) {
            $eventType = "Restore Backup Database";
        } elseif ( $status == 'folder-backup' ) {
            $eventType = "Create Backup Files";
        }else {
            $eventType = "Unknown Action Database";
        }
        $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'), $msg, $user, null, null, $eventType);
    }

    public function runProcess_NEW($script) {
        //$process = new Process($script);
        $process = Process::fromShellCommandline($script);
        $process->setTimeout(1800); //sec; 1800 sec => 30 min
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();
    }
    public function runDeployScript($update, $composer, $cache) {
        if( false === $this->security->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return null;
        }

        if( $update || $composer ) {
            if (false === $this->security->isGranted('ROLE_PLATFORM_ADMIN')) {
                return null;
            }
        }

        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
        //$this->container->compile();

        $dirSep = DIRECTORY_SEPARATOR;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo 'This is a server using Windows! <br>';
            $windows = true;
            $linux = false;
        } else {
            echo 'This is a server not using Windows! Assume Linux <br>';
            $windows = false;
            $linux = true;
        }

        $old_path = getcwd();
        //echo "webPath=$old_path<br>";

        $deploy_path = str_replace("public","",$old_path);
        echo "deploy_path=$deploy_path<br>";
        //exit('111');

        if( is_dir($deploy_path) ) {
            //echo "deploy path exists! <br>";
        } else {
            //echo "not deploy path exists: $deploy_path <br>";
            exit('No deploy path exists in the filesystem; deploy_path=: '.$deploy_path);
        }

        //switch to deploy folder
        echo chdir($deploy_path)."<br>";
        echo "pwd=[".exec("pwd")."]<br>";
        //exec("pwd");

        // Everything for owner and for others
        //chmod($old_path, 0777);

        $command = "git pull";
        //$command = "/usr/bin/git pull";

        //$linux
        if( $linux ) {
//            if( $cache ) {
//                //$this->runProcess("sudo chown -R www-data:www-data ".$old_path);
//                //$this->runProcess("php bin" . $dirSep . "console assets:install");
//                //$this->runProcess("php bin" . $dirSep . "console cache:clear --env=prod --no-debug");
//                $this->runProcess("bash deploy.sh");
//            }

            if( $update ) {
                $this->runProcess($command);
            }

            if( $composer ) {
                $this->runProcess("export COMPOSER_HOME=/usr/local/bin/order-lab && /usr/local/bin/composer self-update");
                $this->runProcess("export COMPOSER_HOME=/usr/local/bin/order-lab && /usr/local/bin/composer install");
            }
        }

        //$windows
        if( $windows ) {
//            if( $cache ) {
//                echo "windows deploy=" . exec("bash deploy.sh") . "<br>";
//            }//cache

            if( $update ) {
                echo "git pull=" . exec($command) . "<br>";
            }

            if( $composer ) {
                echo "composer.phar self-update=" . exec("composer.phar self-update") . "<br>";
                echo "composer.phar install=" . exec("composer.phar install") . "<br>";
            }
        }

        if( $cache ) {
            $this->clearCacheInstallAssets();
        }//cache

        //switch back to web folder
        $output = chdir($old_path);
        echo "<pre>$output</pre>";

        return;
        //exit('exit runDeployScript');
    }
    public function clearCacheInstallAssets( $kernel=null ) {

        if( !$kernel ) {
            $kernel = $this->container->get('kernel');
        }

        $application = new Application($kernel);
        $application->setAutoExit(false);

        //clear cache
        $input = new ArrayInput([
            'command' => 'cache:clear',
            // (optional) define the value of command arguments
            //'fooArgument' => 'barValue',
            // (optional) pass options to the command
            //'--bar' => 'fooValue',
            // (optional) pass options without value
            //'--baz' => true,
        ]);
        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);
        // return the output, don't use if you used NullOutput()
        $content1 = $output->fetch();
        //dump($content1);

        //install assets
        $input = new ArrayInput([
            'command' => 'assets:install',
        ]);
        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);
        // return the output, don't use if you used NullOutput()
        $content2 = $output->fetch();
        //dump($content2);

        $container = new ContainerBuilder();
        $container->compile();

        //exit('111');
        return $content1."; ".$content2;
    }

    public function updateMigrateDb() {
        //run: php bin/console doctrine:migrations:migrate --all-or-nothing

        $projectRoot = $this->container->get('kernel')->getProjectDir();
        $phpPath = $this->getPhpPath();

        if( $this->isWinOs() ) {
            $command = $phpPath . " " . $projectRoot . "/bin/console" . " doctrine:migrations:migrate --all-or-nothing -n";
        } else {
            $command = $phpPath . " " . $projectRoot . "/bin/console" . " doctrine:migrations:migrate --all-or-nothing -n";
        }

        $info = $this->runProcess($command);

//        //$process = new Process($command);
//        $process = Process::fromShellCommandline($command);
//        $process->setTimeout(1800); //sec; 1800 sec => 30 min
//        $process->run();
//        if( !$process->isSuccessful() ) {
//            throw new ProcessFailedException($process);
//        }
//        $info = $process->getOutput();

        return "DB update result: ".$info;
    }

    public function checkAndCreateNewDBs( $request, $authServerNetwork, $kernel ) {

        //$authServerNetwork

        $logger = $this->container->get('logger');
        $config = new \Doctrine\DBAL\Configuration();
        $output = array();
        
        foreach($authServerNetwork->getHostedGroupHolders() as $hostedGroupHolder) {
            if( $hostedGroupHolder->getEnabled() ) {

                $databasePort = $hostedGroupHolder->getDatabasePort();
                if( !$databasePort ) {
                    $databasePort = 5432;
                }

                $databaseHost = $hostedGroupHolder->getDatabaseHost();
                if( !$databaseHost ) {
                    $databaseHost = 'localhost';
                }

                $connectionParams = array(
                    'dbname' => $hostedGroupHolder->getDatabaseName(),
                    'user' => $hostedGroupHolder->getDatabaseUser(),
                    'password' => $hostedGroupHolder->getDatabasePassword(),
                    'host' => $databaseHost,
                    'driver' => $this->container->getParameter('database_driver'),
                    'wrapper_class' => DoctrineMultidatabaseConnection::class,
                    'port' => $databasePort,
                    'charset' => 'utf8'
                );

                //$this->testDb($request,$connectionParams,$kernel);

                //Check if DB exists
                $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
                $valid = $this->isConnectionValid($conn);
                echo $connectionParams['dbname'] . " valid=$valid <br>";

                if( $valid ) {
                    $msg = "Database " . $connectionParams['dbname'] . " already exists.";
                    $logger->notice($msg);
                    //return $msg;
                    $output[] = $msg;
                } else {
                    $output[] = $this->createNewDB($request,$connectionParams,$kernel);
                    $output[] = $this->updateSchema($request,$connectionParams,$kernel);
                    $output[] = $this->syncMigrationsDb($request,$connectionParams,$kernel);
                }
            }//if
        }

        return implode("<br>",$output);
    }

    //testing DB function
    public function testDb($request, $connectionParams, $kernel) {
        $logger = $this->container->get('logger');
        $doctrineConnection = $this->doctrine->getConnection();
        $doctrineConnection->changeDatabase($connectionParams);
        exit('testDb');
        //$application = new Application($kernel);
        //$application->setAutoExit(false);
        $contentOut = null;
        $msg = "createNewDB: DB created ".$connectionParams['dbname']."; output=".$contentOut;
        $logger->notice($msg);
        return $msg;
    }

    //Use App\SystemBundle\DynamicConnection/DoctrineMultidatabaseConnection
    //https://carlos-compains.medium.com/multi-database-doctrine-symfony-based-project-0c1e175b64bf
    public function createNewDB($request, $connectionParams, $kernel) {
        //dump($connectionParams);
        //exit('createNewDB');
        $logger = $this->container->get('logger');

        //$session = $request->getSession();
        //$session->set('create-custom-db', $connectionParams['dbname']);

        //$connectionParams['wrapperClass'] = DoctrineMultidatabaseConnection::class;
        $doctrineConnection = $this->doctrine->getConnection();
        $doctrineConnection->changeDatabase($connectionParams);

        $application = new Application($kernel);
        $application->setAutoExit(false);

        //doctrine:schema:update --em=systemdb --complete --force
        $arguments = [
            'command'          => 'doctrine:database:create',
            '--if-not-exists'  => null,
            '--no-interaction' => null
        ];

        $output = new BufferedOutput();
        $commandInput = new ArrayInput($arguments);
        $application->run($commandInput, $output);
        $contentOut = $output->fetch();
        unset($application);
        unset($kernel);

        //$session->set('create-custom-db', null);
        //$session->remove('create-custom-db');

        $msg = "createNewDB: DB created ".$connectionParams['dbname']."; output=".$contentOut;
        $logger->notice($msg);
        return $msg;
    }

    //Use App\SystemBundle\DynamicConnection/DoctrineMultidatabaseConnection
    //https://carlos-compains.medium.com/multi-database-doctrine-symfony-based-project-0c1e175b64bf
    public function updateSchema($request, $connectionParams, $kernel) {
        //dump($connectionParams);
        //exit('updateSchema');
        $logger = $this->container->get('logger');

        //$connectionParams['wrapperClass'] = DoctrineMultidatabaseConnection::class;
        $doctrineConnection = $this->doctrine->getConnection();
        $doctrineConnection->changeDatabase($connectionParams);

        $application = new Application($kernel);
        $application->setAutoExit(false);

        //doctrine:schema:update --em=systemdb --complete --force
        $arguments = [
            'command'          => 'doctrine:schema:update',
            '--complete'  => null,
            '--force' => null
        ];

        //doctrine:migrations:migrate --all-or-nothing
//        $arguments = [
//            'command'              => 'doctrine:migrations:migrate',
//            '--no-interaction'     => '',
//            '--no-debug'           => '',
//            //'--allow-no-migration' => '',
//            //'-all-or-nothing'      => ''
//        ];

        $output = new BufferedOutput();
        $commandInput = new ArrayInput($arguments);
        $application->run($commandInput, $output);
        $contentOut = $output->fetch();
        unset($application);
        unset($kernel);

        $logger->notice("updateSchema: create-custom-db: done");

        $msg = "updateSchema: DB updated ".$connectionParams['dbname']."; output=".$contentOut;
        $logger->notice($msg);
        return $msg;
    }

    public function syncMigrationsDb($request, $connectionParams, $kernel) {
        $logger = $this->container->get('logger');

        //$connectionParams['wrapperClass'] = DoctrineMultidatabaseConnection::class;
        $doctrineConnection = $this->doctrine->getConnection();
        $doctrineConnection->changeDatabase($connectionParams);

        $contentOut = array();
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $output = new BufferedOutput();

        //php bin/console doctrine:migration:sync-metadata-storage --em=systemdb
        $arguments = [
            'command'              => 'doctrine:migration:sync-metadata-storage',
            '--no-interaction'     => null,
            '--no-debug'           => null
        ];
        $commandInput = new ArrayInput($arguments);
        $application->run($commandInput, $output);
        $contentOut[] = $output->fetch();

        //php bin/console doctrine:migration:version --em=systemdb --add --all
        $arguments = [
            'command'               => 'doctrine:migration:version',
            '--add'                 => null,
            '--all'                 => null,
            '--no-interaction'      => null,
            '--no-debug'            => null,
        ];
        $commandInput = new ArrayInput($arguments);
        $application->run($commandInput, $output);
        $contentOut[] = $output->fetch();

        unset($application);
        unset($kernel);

        $msg = "syncMigrationsDb: DB sync migrations ".$connectionParams['dbname']."; output=".implode("<br>",$contentOut);
        $logger->notice($msg);
        return $msg;
    }


    public function createNewDB_WITH_SQL( $request, $connectionParams, $kernel ) {
        $logger = $this->container->get('logger');

        if(1) {
            $config = new \Doctrine\DBAL\Configuration();
            $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
            $valid = $this->isConnectionValid($conn);
            echo $connectionParams['dbname'] . " valid=$valid <br>";

            if ($valid) {
                $msg = "Database " . $connectionParams['dbname'] . " already exists.";
                $logger->notice($msg);
                return $msg;
            }

            $conn = $this->getConnectionByLocale('system');
            $sql = "CREATE DATABASE " . $connectionParams['dbname'];
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery()->fetchAll(\PDO::FETCH_COLUMN);
        }

        //dump($results);
        //exit('111');
        $msg = "Database ".$connectionParams['dbname']." has been created.";
        return $msg;

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
            "ResidencyApplication"      => "show",
            "SiteParameters"            => "settings/settings-id", //"settings",
            "VacReqUserCarryOver"       => "show",
            "Project"                   => "project/show",
            "TransResRequest"           => "work-request/show",
            "DefaultReviewer"           => "default-reviewers/show",
            "Invoice"                   => "invoice/show",
        );

        if (array_key_exists($className,$mapArr))
        {
            $url = $mapArr[$className];
        } else {
            $url = null;
        }

        return $url;
    }

    public function getSiteNameByAbbreviation($abbreviation) {
        $siteObject = $this->em->getRepository(SiteList::class)->findOneByAbbreviation($abbreviation);
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
        //$logger = $this->container->get('logger');

        if (file_exists($dest)) {
            //echo "The file $dest exists <br>";
            //$logger = $this->container->get('logger');
            //$logger->notice("$desired_width thumbnail already exists. dest=" . $dest);
            return null;
        }
        else {
            //echo "The file $dest does not exists <br>";
        }

        if (file_exists($src)) {
            //echo "The file $src exists <br>";
            //$logger->notice("src file does not exists src=$src");
            return null;
        }
        else {
            //$logger->notice("src file exists src=$src");
        }

        if( strpos((string)$src, '.jpg') !== false || strpos((string)$src, '.jpeg') !== false ) {
            //ok, file is jpeg
        } else {
            return null;
        }

        //TODO: check for possible error: Failed to open stream: No such file or directory at
        /* read the source image */
        //$source_image = imagecreatefromjpeg($src);
        $source_image = NULL;
        try
        {
            $source_image = imagecreatefromjpeg($src);
        }
        catch (\Exception $ignored)
        {
            // do nothing... php will ignore and continue
            // but maybe use "ignored" as name to silence IDE warnings.
            $logger = $this->container->get('logger');
            $logger->error("Error in imagecreatefromjpeg: ".$ignored);
        }

        if( !$source_image ) {
            return null;
        }

        $width = imagesx($source_image); //imagesx(): Argument #1 ($image) must be of type GdImage, bool given with code0
        $height = imagesy($source_image);

        /* find the "desired height" of this thumbnail, relative to the desired width  */
        $desired_height = floor($height * ($desired_width / $width));

        $desired_width = floor($desired_width);

        /* create a new, "virtual" image */
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

        if( !$virtual_image ) {
            return null;
        }

        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

        if( !$virtual_image ) {
            return null;
        }

        /* create the physical thumbnail image to its destination */
        imagejpeg($virtual_image, $dest);

        return $dest;
    }

    public function createCrons() {
        if( $this->isWindows() ) {
            //Windows
            $this->createCronsWindows();
        } else {
            //Linux
            $this->createCronsLinux();
        }
    }
    public function createCronsWindows() {

        $projectDir = $this->container->get('kernel')->getProjectDir();
        $console = $projectDir.DIRECTORY_SEPARATOR."bin".DIRECTORY_SEPARATOR."console";

        ////////////////////// 1) swiftMailer (implemented on email util (EmailUtil->createEmailCronJob)) //////////////////////
        //$emailUtil = $this->container->get('user_mailer_utility');
        //$emailUtil->createEmailCronJobWindows();

        $cronJobName = "swift";
        if( $this->getCronStatusWindows($cronJobName,true) === false ) {

            $frequencyMinutes = 15;

            $cronJobCommand = 'php \"' . $console . '\" cron:swift --env=prod';
            $cronJobCommand = '"' . $cronJobCommand . '"';

            $command = 'SchTasks /Create /SC MINUTE /MO ' . $frequencyMinutes .
                ' /IT ' .
                //' /RU system'.
                ' /TN ' . $cronJobName .
                ' /TR ' . $cronJobCommand . '';
            //echo "SchTasks add: ".$command."<br>";
            //$logger->notice("SchTasks:".$command);
            $resEmail = exec($command);
        }
        ////////////////////// EOF 1) swiftMailer (implemented on email util (EmailUtil->createEmailCronJob)) //////////////////////

        ////////////////////// 2) importFellowshipApplications (every hour) //////////////////////
        //command:    php
        //arguments(working): "E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\bin\console" cron:importfellapp --env=prod
        $cronJobName = "importfellapp";
        if( $this->getCronStatusWindows($cronJobName,true) === false ) {
            $frequencyMinutes = 60;

            $cronJobCommand = 'php \"' . $console . '\" cron:importfellapp --env=prod';
            $cronJobCommand = '"' . $cronJobCommand . '"';

            $command = 'SchTasks /Create /SC MINUTE /MO ' . $frequencyMinutes .
                ' /IT ' .
                //' /RU system'.
                ' /TN ' . $cronJobName .
                ' /TR ' . $cronJobCommand . '';
            //echo "SchTasks add: ".$command."<br>";
            //$logger->notice("SchTasks:".$command);
            $resFellapp = exec($command);
        }
        ////////////////////// EOF 2) importFellowshipApplications (every hour) //////////////////////

        ////////////////////// 3) UnpaidInvoiceReminder (at 6 am every Monday) //////////////////////
        //cron:invoice-reminder-emails --env=prod
        $cronJobName = "invoice-reminder-emails";
        if( $this->getCronStatusWindows($cronJobName,true) === false ) {

            $cronJobCommand = 'php \"' . $console . '\" cron:invoice-reminder-emails --env=prod';
            $cronJobCommand = '"' . $cronJobCommand . '"';

            $command = 'SchTasks /Create /SC WEEKLY /D MON /MO 1 /ST 6:00' .
                ' /IT ' .
                //' /RU system'.
                ' /TN ' . $cronJobName .
                ' /TR ' . $cronJobCommand . '';
            //echo "SchTasks add: ".$command."<br>";
            //$logger->notice("SchTasks:".$command);
            $resFellapp = exec($command);
        }
        ////////////////////// EOF 3) UnpaidInvoiceReminder (at 6 am every Monday) //////////////////////

//        ////////////////////// 3b) Expiration Reminder (at 5 am every Monday) //////////////////////
//        $cronJobName = "expiration-reminder-emails";
//        if( $this->getCronStatusWindows($cronJobName,true) === false ) {
//
//            $cronJobCommand = 'php \"' . $console . '\" cron:expiration-reminder-emails --env=prod';
//            $cronJobCommand = '"' . $cronJobCommand . '"';
//
//            $command = 'SchTasks /Create /SC WEEKLY /D MON /MO 1 /ST 5:00' .
//                ' /IT ' .
//                //' /RU system'.
//                ' /TN ' . $cronJobName .
//                ' /TR ' . $cronJobCommand . '';
//            //echo "SchTasks add: ".$command."<br>";
//            //$logger->notice("SchTasks:".$command);
//            $resFellapp = exec($command);
//        }
//        ////////////////////// EOF 3b) Expiration Reminder (at 5 am every Monday) //////////////////////

    }

    //Can use package: https://packagist.org/packages/hellogerard/jobby
    //Show for specific user: crontab -u apache -l
    //Remove for specific user: crontab -u apache -r
    //Create cron jobs:
    //1) swiftMailer (implemented on email util (EmailUtil->createEmailCronJob))
    //2) importFellowshipApplications (every hour)
    //3) UnpaidInvoiceReminder (at 6 am every Monday)
    //TODO: auto generation adds ^M at the end of new line
    public function createCronsLinux() {
        $logger = $this->container->get('logger');
        $logger->notice("Creating cron jobs for Linux");
        $projectDir = $this->container->get('kernel')->getProjectDir();

        //////////////////// 1) swiftMailer (implemented on email util (EmailUtil->createEmailCronJob)) ////////////////////
        //$this->createEmailCronLinux();
        //////////////////// EOF 1) swiftMailer (implemented on email util (EmailUtil->createEmailCronJob)) ////////////////////

        //////////////////// 2) ImportFellowshipApplications (every hour) ////////////////////

        //first delete existing cron job
        //$this->removeCronJob($crontab,$fellappCronJobCommand);
        $commandName = "cron:importfellapp";
        $cronJobName = $commandName." --env=prod";

        $phpPath = $this->getPhpPath();
        $fellappCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";

        //$fellappCronJob = "*/2 * * * *" . " " . $fellappCronJobCommand; //at every 2nd minutes
        $fellappCronJob = "00 * * * *" . " " . $fellappCronJobCommand; //0 minutes - every hour

        if( $this->getCronJobFullNameLinux($commandName) === false ) {

            $res = $this->addCronJobLinux($fellappCronJob);

            $res = "Created $cronJobName cron job";
        } else {
            $res = "$cronJobName already exists";
        }

        $logger->notice($res);
        //////////////////// EOF ImportFellowshipApplications ////////////////////

        //////////////////// 2a) Verify Import Fellowship Applications (every 6 hours) ////////////////////
        $commandName = "cron:verifyimport";
        $cronJobName = $commandName." --env=prod";

        $phpPath = $this->getPhpPath();
        $fellappVerifyImportCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";

        $fellappVerifyImportCronJob = "0 */6 * * *" . " " . $fellappVerifyImportCronJobCommand; //every 6 hours

        if( $this->getCronJobFullNameLinux($commandName) === false ) {

            $res = $this->addCronJobLinux($fellappVerifyImportCronJob);

            $res = "Created $cronJobName cron job: $fellappVerifyImportCronJob";
        } else {
            $res = "$cronJobName already exists";
        }

        $logger->notice($res);
        //////////////////// EOF ImportFellowshipApplications ////////////////////

        //////////////////// 3) UnpaidInvoiceReminder (at 6 am every Monday) ////////////////////
        $commandName = "cron:invoice-reminder-emails";
        $cronJobName = $commandName." --env=prod";

        $phpPath = $this->getPhpPath();
        $trpCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";

        $trpCronJob = "00 06 * * Mon" . " " . $trpCronJobCommand; //every monday (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
        //$trpCronJob = "0 6 * * 1" . " " . $trpCronJobCommand; //run every monday at 6am (https://stackoverflow.com/questions/25676475/run-every-monday-at-5am?rq=1)
        //$trpCronJob = "41 16 * * 2" . " " . $trpCronJobCommand; //testing: run every tuesday at 17:32
        //$trpCronJob = "*/10 * * * *" . " " . $trpCronJobCommand; //testing: At minute 10

        if( $this->getCronJobFullNameLinux($commandName) === false ) {
            $this->addCronJobLinux($trpCronJob);
            $res = "Created $cronJobName cron job";
        } else {
            $res = "$cronJobName already exists";
        }

        $logger->notice($res);
        //////////////////// EOF 3) UnpaidInvoiceReminder (at 6 am every Monday) ////////////////////

        //////////////////// 3b) Expiration Reminder (at 5 am every Monday) ////////////////////
        $commandName = "cron:expiration-reminder-emails";
        $cronJobName = $commandName." --env=prod";

        $phpPath = $this->getPhpPath();
        $trpCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";

        $trpCronJob = "00 05 * * Mon" . " " . $trpCronJobCommand; //every monday (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat

        if( $this->getCronJobFullNameLinux($commandName) === false ) {
            $this->addCronJobLinux($trpCronJob);
            $res = "Created $cronJobName cron job";
        } else {
            $res = "$cronJobName already exists";
        }

        $logger->notice($res);
        //////////////////// EOF 3b) Expiration Reminder (at 5 am every Monday) ////////////////////

        //////////////////// 3c) Project sync (every 10 min) ////////////////////
        $commandName = "cron:project-sync";
        $cronJobName = $commandName." --env=prod";

        $phpPath = $this->getPhpPath();
        $trpCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";

        $trpCronJob = "*/10 * * * *" . " " . $trpCronJobCommand; //At every 10th minute

        if( $this->getCronJobFullNameLinux($commandName) === false ) {
            $this->addCronJobLinux($trpCronJob);
            $res = "Created $cronJobName cron job";
        } else {
            $res = "$cronJobName already exists";
        }

        $logger->notice($res);
        //////////////////// EOF 3b) Expiration Reminder (at 5 am every Monday) ////////////////////

        //////////////////// 4) Status (every 30 minutes) ////////////////////
//        $cronJobName = "cron:status --env=prod";
//
//        $phpPath = $this->getPhpPath();
//        $statusCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";
//
//        $statusFrequency = 30;
//        $statusFrequency = 5;
//        $statusCronJob = "*/$statusFrequency * * * *" . " " . $statusCronJobCommand;
//
//        if( $this->getCronJobFullNameLinux($cronJobName) === false ) {
//            $this->addCronJobLinux($statusCronJob);
//            $res = "Created $cronJobName cron job";
//        } else {
//            $res = "$cronJobName already exists";
//        }
//
//        $logger->notice($res);
        $res = $this->createStatusCronLinux();
        //$logger->notice($res);

        $res = $this->createCertificateCronLinux();
        //////////////////// EOF 4) Status ////////////////////

        return $res;
    }
    public function createStatusCronLinux( $statusFrequency = 30 ) {

        if( $this->isWindows() ) {
            //Windows
            return "Windows is not supported";
        }
        
        $logger = $this->container->get('logger');
        $logger->notice("Creating status cron job for Linux");
        $projectDir = $this->container->get('kernel')->getProjectDir();

        $commandName = "cron:status";
        $cronJobName = $commandName." --env=prod";

        $phpPath = $this->getPhpPath();
        $statusCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";

        $statusFrequency = 30;
        //$statusFrequency = 2; //testing
        $statusCronJob = "*/$statusFrequency * * * *" . " " . $statusCronJobCommand;

        if( $this->getCronJobFullNameLinux($commandName) === false ) {
            $this->addCronJobLinux($statusCronJob);
            $res = "Created $cronJobName cron job";
        } else {
            $res = "$cronJobName already exists";
        }

        $logger->notice($res);
        
        return $res;
    }
    public function createCertificateCronLinux() {

        if( $this->isWindows() ) {
            //Windows
            return "Windows is not supported";
        }

        $logger = $this->container->get('logger');
        $logger->notice("Creating status cron job for Linux");
        $projectDir = $this->container->get('kernel')->getProjectDir();

        //$domain = 'view.online';
        $commandName = "cron:certificate";
        $cronJobName = $commandName." --env=prod"; //." ".$domain;

        $phpPath = $this->getPhpPath();
        $statusCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";

        //Every 2 minutes: */2 * * * *
        //Every day at 3 AM: 0 3 * * *
        //EDT to UTC 3am -> +4 -> 7am -> 0 7 * * *
        //Test, Live servers have EDT time
        $statusCronJob = "0 2 * * *" . " " . $statusCronJobCommand;

        if( $this->getCronJobFullNameLinux($commandName) === false ) {
            $this->addCronJobLinux($statusCronJob);
            $res = "Created $cronJobName cron job";
        } else {
            $res = "$cronJobName already exists";
        }

        $logger->notice($res);

        return $res;
    }
    //Dummy test cron job to check new line for multiple jobs
    public function createTestStatusCronLinux( $statusFrequency = 30 ) {

        if( $this->isWindows() ) {
            //Windows
            return "Windows is not supported";
        }
        
        $logger = $this->container->get('logger');
        $logger->notice("Creating statustest cron job for Linux");
        $projectDir = $this->container->get('kernel')->getProjectDir();

        $commandName = "cron:statustest";
        $cronJobName = $commandName." --env=prod";

        $phpPath = $this->getPhpPath();
        $statusCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";

        //$statusFrequency = 30; //minutes
        $statusFrequency = 2; //testing, in minutes
        $statusCronJob = "*/$statusFrequency * * * *" . " " . $statusCronJobCommand;

        if( $this->getCronJobFullNameLinux($commandName) === false ) {
            $this->addCronJobLinux($statusCronJob);
            $res = "Created $cronJobName cron job";
        } else {
            $res = "$cronJobName already exists";
        }

        $logger->notice($res);
        
        return $res;
    }
    public function createEmailCronLinux( $mailerFlushQueueFrequency = null ) {
        $userSecUtil = $this->container->get('user_security_utility');

        $useSpool = $userSecUtil->getSiteSettingParameter('mailerSpool');

        if( !$mailerFlushQueueFrequency ) {
            $mailerFlushQueueFrequency = $userSecUtil->getSiteSettingParameter('mailerFlushQueueFrequency');
        }

        if( $useSpool && $mailerFlushQueueFrequency ) {
            //OK create email cron
        } else {
            return false;
        }

        $logger = $this->container->get('logger');
        $logger->notice("Creating cron jobs for Linux");
        $projectDir = $this->container->get('kernel')->getProjectDir();

        $phpPath = $this->getPhpPath();
        $emailCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console cron:swift --env=prod";

        $emailCronJob = "*/$mailerFlushQueueFrequency * * * *" . " " . $emailCronJobCommand;

        $commandName = "cron:swift";
        if( $this->getCronJobFullNameLinux($commandName) === false ) {
            $this->addCronJobLinux($emailCronJob);
            $res = "Created $commandName cron job";
        } else {
            $res = "$commandName already exists";
        }

        $logger->notice($res);

        return $res;
    }
    //external url monitor: monitor status of the external url (this server monitors the system's health on another server)
    public function createExternalUrlMonitorCronLinux() {

        if( $this->isWindows() ) {
            //Windows
            return "Windows is not supported";
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');
        $logger->notice("Creating External Url Monitor cron job for Linux");
        $projectDir = $this->container->get('kernel')->getProjectDir();

        $commandName = "cron:externalurlmonitor"; //call checkExternalUrlMonitor
        $cronJobName = $commandName." --env=prod";

        $phpPath = $this->getPhpPath();
        $statusCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";

        $externalUrlMonitorFrequency = $userSecUtil->getSiteSettingParameter('monitorCheckInterval'); //in min
        if( !$externalUrlMonitorFrequency ) {
            $externalUrlMonitorFrequency = 15;
        }
        //$externalUrlMonitorFrequency = 15;

        $statusCronJob = "*/$externalUrlMonitorFrequency * * * *" . " " . $statusCronJobCommand;

        if( $this->getCronJobFullNameLinux($commandName) === false ) {
            $this->addCronJobLinux($statusCronJob);
            $res = "Created $cronJobName cron job";
        } else {
            $res = "$cronJobName already exists";
        }

        $logger->notice($res);

        return $res;
    }
    //Independent monitor script (i.e. python is independent from php, postgresql)
    public function createIndependentMonitorCronLinux() {

        if( $this->isWindows() ) {
            //Windows
            //return "Windows is not supported";
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');
        $logger->notice("Creating Independent Monitor cron job for Linux");
        $projectDir = $this->container->get('kernel')->getProjectDir();

        $commandName = "webmonitor.py"; //"cron:independentmonitor";
        $cronJobName = $commandName." --env=prod";

        $externalUrlMonitorFrequency = $userSecUtil->getSiteSettingParameter('monitorCheckInterval'); //in min
        if( !$externalUrlMonitorFrequency ) {
            $externalUrlMonitorFrequency = 15;
        }

        //get $statusCronJobCommand from $monitorScript
        $statusCronJobCommand = $userSecUtil->getSiteSettingParameter('monitorScript');
        if( !$statusCronJobCommand ) {
            return null; //do not setup monitor cron if monitorScript is empty

            //$phpPath = $this->getPhpPath();
            //$statusCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";
            $path = $projectDir . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "App" . DIRECTORY_SEPARATOR . "UserdirectoryBundle" . DIRECTORY_SEPARATOR . "Util";

            //smtpServerAddress
            $smtpHost = $userSecUtil->getSiteSettingParameter('smtpServerAddress');
            if (!$smtpHost) {
                return null;
            }

            //siteEmail
            $sender = $userSecUtil->getSiteSettingParameter('siteEmail');
            if (!$sender) {
                return null;
            }

            //$sitename,$userRole,$roles=null
//        $receivers = $userSecUtil->getUserEmailsByRole(null,"Platform Administrator"); //array of emails
//        if( $receivers ) {
//            $receivers[] = $sender;
//        } else {
//            $receivers = array($sender);
//        }
//        if( count($receivers) > 0 ) {
//            $receivers = array_unique($receivers);
//            $receivers = "'".implode(",",$receivers)."'";
//        } else {
//            $receivers = $sender;
//        }
            $receivers = $sender;

            $environment = $userSecUtil->getSiteSettingParameter('environment');

            //$smtpHost = "smtp.med.cornell.edu";
            //$sender = "oli2002@med.cornell.edu";
            //$receivers = "oli2002@med.cornell.edu";

            //python webmonitor.py --urls "http://view.med.cornell.edu, http://view-test.med.cornell.edu" -h "smtp.med.cornell.edu" -u "" -p "" -s "oli2002@med.cornell.edu" -r "oli2002@med.cornell.edu"
            //python 'C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex\src\App\UserdirectoryBundle\Util\webmonitor.py'
            // -l "http://view.med.cornell.edu, http://view-test.med.cornell.edu" -h "smtp.med.cornell.edu"
            // -s [[siteEmail]] -r [[ROLE_PLATFORM_DEPUTY_ADMIN]] -e dev
            $statusCronJobCommand = "python3 " . "'" . $path . DIRECTORY_SEPARATOR . "webmonitor.py" . "'" .
                " -l 'http://view.med.cornell.edu, http://view-test.med.cornell.edu'" .
                " -h $smtpHost -s $sender -r $receivers -e $environment";

            //webmonitor.py cron job status: */2 * * * * python '/opt/order-lab/orderflexsrc/App/UserdirectoryBundle/Util/webmonitor.py' -l 'http://view.med.cornell.edu, http://view-test.med.cornell.edu' -h smtp.med.cornell.edu -s oli2002@med.cornell.edu -r 'cinava@yahoo.com,glimpera@med.cornell.edu,vib9020@med.cornell.edu,oli2002@med.cornell.edu,bih2004@med.cornell.edu' -e test.
            //test: python '/opt/order-lab/orderflex/src/App/UserdirectoryBundle/Util/webmonitor.py' -l 'http://view.med.cornell.edu, http://view-test.med.cornell.edu' -h smtp.med.cornell.edu -s oli2002@med.cornell.edu -r 'cinava@yahoo.com,glimpera@med.cornell.edu,vib9020@med.cornell.edu,oli2002@med.cornell.edu,bih2004@med.cornell.edu' -e test.
            //live: python '/srv/order-lab/orderflex/src/App/UserdirectoryBundle/Util/webmonitor.py'
        }

        //$externalUrlMonitorFrequency = 2;
        $statusCronJob = "*/$externalUrlMonitorFrequency * * * *" . " " . $statusCronJobCommand;

        if( $this->getCronJobFullNameLinux($commandName) === false ) {
            $this->addCronJobLinux($statusCronJob);
            $res = "Created $cronJobName cron job";
        } else {
            $res = "$cronJobName already exists";
        }

        $logger->notice($res);

        return $res;
    }

    //Since HAProxy serves the cert directly, you can use openssl from the command line
    public function getSslCertificateRemainingDays( $domain=NULL ) {
        //echo | openssl s_client -connect view.online:443 2>/dev/null | openssl x509 -noout -dates

        //$domain = 'view.online';
        $port = 443;

        if( !$domain ) {
            $domain = 'view.online';
            echo "use the default domain=$domain <br>";
        }

        // Build the shell command
        $cmd = "echo | openssl s_client -connect {$domain}:{$port} 2>/dev/null | openssl x509 -noout -dates -issuer -subject";
        //echo "cmd=$cmd <br>";

        // Execute and capture output
        $output = shell_exec($cmd);
        //dump($output);
        //exit('111');

        // Parse the output
        $validFrom = $validTo = $organization = null;
        if (preg_match('/notBefore=(.+)/', $output, $matches)) {
            $validFrom = trim($matches[1]);
        }
        if (preg_match('/notAfter=(.+)/', $output, $matches)) {
            $validTo = trim($matches[1]);
        }

//        if (preg_match('/Organization=(.+)/', $output, $matches)) {
//            $organization = trim($matches[1]);
//        }
//        $issuerCN = $output['issuer']['CN'] ?? 'N/A';
//        $issuerOrg = $output['issuer']['O'] ?? 'N/A';
        preg_match('/issuer=.*O=([^,]+)/', $output, $matches);
        $issuerOrg = trim($matches[1] ?? 'N/A');

        // Optional: Convert to timestamp or calculate days remaining
        $validToTimestamp = strtotime($validTo);
        $daysRemaining = floor(($validToTimestamp - time()) / 86400);

        // Output or use variables
//        echo "Organization (Certificate issuer): [{$issuerOrg}]\n<br>";
//        echo "Certificate for {$domain}\n<br>";
//        echo "Valid From: {$validFrom}\n<br>";
//        echo "Valid To:   {$validTo}\n<br>";
//        echo "Days Remaining: {$daysRemaining}\n<br>";

        if( $daysRemaining ) {
            $resArr = array(
                'Organization' => $issuerOrg,
                'Domain' => $domain,
                'ValidFrom' => $validFrom,
                'ValidTo' => $validTo,
                'DaysRemaining' => $daysRemaining,
            );
            return $resArr;
            //return (int)$daysRemaining;
        }

        return NULL;
    }
    public function checkSslCertificate( $domain=NULL, $sendEmail=true ) {
        //echo | openssl s_client -connect view.online:443 2>/dev/null | openssl x509 -noout -dates

        $resArr = $this->getSslCertificateRemainingDays($domain);
        $daysRemaining = NULL;
        $organization = NULL;
        if( $resArr ) {
            $daysRemaining = $resArr['DaysRemaining'];
            $organization = $resArr['Organization'];
        }

        //Use two weeks (14 days) in advance notification
        if( $sendEmail && ($daysRemaining === NULL || $daysRemaining < 14) ) {
            //send email
            $userSecUtil = $this->container->get('user_security_utility');
            $emailUtil = $this->container->get('user_mailer_utility');
            //$environment = $userSecUtil->getSiteSettingParameter('environment');
            $emails = $userSecUtil->getUserEmailsByRole(null,"Platform Administrator");
            //$emails = array("oli2002@med.cornell.edu"); //testing

            //siteEmail
            $sender = $userSecUtil->getSiteSettingParameter('siteEmail'); //might be adminemail@example.com
            if( $sender ) {
                if( $emails ) {
                    $emails[] = $sender;
                } else {
                    $emails = array($sender);
                }
            }
            $emails = array_values(array_diff($emails, ["adminemail@example.com"]));
            //echo "emails: <br>";
            //dump($emails);
            //exit('111');

            $subject = "Warning: SSL certificate expiration for $domain issued by $organization";
            $msg = "The SSL certificate for server $domain issued by $organization will expire in $daysRemaining days.";
            //insert steps

            $emailUtil->sendEmail($emails,$subject,$msg);

            //Event Log
            $eventType = "SSL Certificate Warning";
            $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'), $msg, null, null, null, $eventType);
        }

        return $resArr;
    }

    //Run as root
    //Update let's encrypt certificate
    public function updateSslCertificate( $domain, $daysRemaining, $organization ) {

        if( !$domain ) {
            return "Domain name is not provided.";
        }

        //echo "organization=$organization <br>";
        if( !$organization || !$organization == "Let's Encrypt" ) {
            return "Invalid organization $organization. Only Let's Encrypt is supported.";
        }

        if( !$daysRemaining ) {
            $daysRemaining = 0;
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');
        $emails = $userSecUtil->getUserEmailsByRole(null,"Platform Administrator");

        //Run bash packer/update-certbot.sh
        //Do this only for view.online (DigitalOcean server) when certificate has Issuer Name "Organization Let's Encrypt"
        //run as root, otherwise 'Permission denied'
        $projectDir = $this->container->get('kernel')->getProjectDir();
        $bashScript = $projectDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'packer' . DIRECTORY_SEPARATOR . 'update-certbot.sh';

        $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');

        //Add parameter $domainname and $email
        $bashScript = $bashScript . " " . $domain . " " . $systemEmail;
        
        //Interactive command asking to enter domain name
        $command = 'bash ' . $bashScript;
        //echo "updateSslCertificate command={$command} <br>";

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(120); //sec; 120 sec => 2 min
        $process->run();
        
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $stdout = $process->getOutput();              // stdout
        $errorOutput = $process->getErrorOutput();  // stderr
        //echo "STDOUT=[" . $stdout . "]\n<br>";
        //echo "STDERR=[" . $errorOutput . "]\n<br>";
        $info = "STDOUT=[".$stdout . "], STDERR=[" . $errorOutput . "]";
        //echo "update-certbot.sh => info=$info \n<br>";

        if( str_contains($info,'Certificate not yet due for renewal') ) {
            //echo "update-certbot.sh: Nothing to do \n<br>";
            return $info;
        }

        //siteEmail
        //echo "update-certbot.sh: Send email \n<br>";
        $sender = $userSecUtil->getSiteSettingParameter('siteEmail'); //might be adminemail@example.com
        //echo "update-certbot.sh: Send email to sender=[$sender] \n<br>";
        if( $sender ) {
            if( $emails ) {
                $emails[] = $sender;
            } else {
                $emails = array($sender);
            }
        }
        $emails = array_values(array_diff($emails, ["adminemail@example.com"]));
        //echo "emails: <br>";
        //dump($emails);
        //exit('111');

        //TODO: check the return message 'If not expired => Certificate not yet due for renewal; no action taken'
        $subject = "SSL certificate has been updated for $domain";
        $msg = "The SSL certificate for server $domain has been updated. The previous certificate had $daysRemaining remaining days";
        $msg = $msg . "<br><br>" . $info;
        //insert steps

        $emailUtil->sendEmail($emails,$subject,$msg);

        //Event Log
        $eventType = "SSL Certificate Warning";
        $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'), $msg, null, null, null, $eventType);

        return $info;
    }

//    //when HAProxy is terminating SSL with bind :443 ssl crt ...,
//    // the PHP script I shared earlier won't retrieve the certificate from HAProxy itself.
//    // That script connects to the domain and inspects the certificate
//    // presented during the SSL handshake, which in this case is HAProxy’s certificate, not the backend’s.
//    public function checkSslCertificate0( $domain ) {
//        echo "domain=$domain <br>";
//        if( !$domain ) {
//            $domain = 'view.online';
//            echo "use the default domain=$domain <br>";
//        }
//
//        $url = "https://".$domain; // Replace with your target URL
//        echo "url=$url <br>";
//
//
//        $host = parse_url($url, PHP_URL_HOST);
//        echo "host=$host <br>";
//
//        // Create SSL context to capture the peer certificate
//        $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
//        $sslUrl = "ssl://$host:443";
//        echo "sslUrl=$sslUrl <br>";
//        $client = stream_socket_client($sslUrl, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
//
//        if ($client) {
//            $params = stream_context_get_params($client);
//            $cert = $params["options"]["ssl"]["peer_certificate"];
//            $certInfo = openssl_x509_parse($cert);
//
//            $validFrom = date(DATE_RFC2822, $certInfo['validFrom_time_t']);
//            $validTo = date(DATE_RFC2822, $certInfo['validTo_time_t']);
//
//            echo "Certificate for $host\n<br>";
//            echo "Valid From: $validFrom\n<br>";
//            echo "Valid To: $validTo\n<br>";
//        } else {
//            echo "Failed to connect to $host: $errstr ($errno)\n";
//        }
//    }


    public function createUserADStatusCron( $frequency = '6h' ) {

        if( $this->isWindows() ) {
            //Windows
            return "Windows is not supported";
        }

        $logger = $this->container->get('logger');
        $logger->notice("Creating useradstatus cron job for Linux");
        $projectDir = $this->container->get('kernel')->getProjectDir();

        $commandName = "cron:useradstatus";
        $cronJobName = $commandName." --env=prod";

        $phpPath = $this->getPhpPath();
        $statusCronJobCommand = $phpPath." ".$projectDir.DIRECTORY_SEPARATOR."bin/console $cronJobName";


        $statusCronJob = "";
        if( str_contains($frequency, 'm') ) {
            $frequency = str_replace('m','',$frequency);
            $statusCronJob = "*/$frequency * * * *" . " " . $statusCronJobCommand;
        }
        if( str_contains($frequency, 'h') ) {
            $frequency = str_replace('h','',$frequency);
            $statusCronJob = "0 */$frequency * * *" . " " . $statusCronJobCommand;
        }
        //$statusFrequency = 30;
        //$statusFrequency = 5; //testing
        //$statusCronJob = "*/$statusFrequency * * * *" . " " . $statusCronJobCommand;

        if( !$statusCronJob ) {
            $res = "Cron useradstatus is not created: invalid parameter $frequency";
            $logger->notice($res);
            return $res;
        }

        if( $this->getCronJobFullNameLinux($commandName) === false ) {
            $this->addCronJobLinux($statusCronJob);
            $res = "Created $cronJobName cron job";
        } else {
            $res = "$cronJobName already exists";
        }

        $logger->notice($res);

        return $res;
    }

    //NOT USED
    public function createFilesBackupCronLinux() {
        if( $this->isWindows() ) {
            //Windows
            return "Windows is not supported";
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');
        $logger->notice("Creating Independent Monitor cron job for Linux");
        //$projectDir = $this->container->get('kernel')->getProjectDir();

        $commandName = "filesbackup"; //"cron:independentmonitor";
        $cronJobName = $commandName." --env=prod";

        $filesBackupConfig = $userSecUtil->getSiteSettingParameter('filesBackupConfig'); //in min
        if( !$filesBackupConfig ) {
            return "filesBackupConfig is not provided";
        }

        $filesBackupConfigPrepared = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $filesBackupConfig);
        //echo "$filesBackupConfigPrepared=[$filesBackupConfigPrepared]<br>";
        $jsonObject = json_decode($filesBackupConfigPrepared,true);

        if( !$jsonObject ) {
            $filesBackupConfigPrepared = str_replace(array('/', '\\'), '/', $filesBackupConfig);
            //echo "$filesBackupConfigPrepared=[$filesBackupConfigPrepared]<br>";
            $jsonObject = json_decode($filesBackupConfigPrepared,true);
        }

        if( !$jsonObject ) {
            return "Cannot decode JSON configuration file: ".$filesBackupConfig;
        }

        //dump($jsonObject);
        //exit('after json_decode');

        //parse $filesBackupConfig
        $cronJobCommand = NULL;
        $cronIntervals = NULL;

        if( array_key_exists('command', $jsonObject) ) {
            $cronJobCommand = $jsonObject['command'];
        }

        if( array_key_exists('cronintervals', $jsonObject) ) {
            $cronIntervals = $jsonObject['cronintervals'];
        }

        //$cronIntervalsArr = explode(",",$cronIntervals);
        if( !is_array($cronIntervals) ) {
            $cronIntervals = array($cronIntervals);
        }

        $resArr = array();

        foreach($cronIntervals as $cronInterval) {

            $cronJob = NULL;

            if( $cronJob == NULL && str_contains($cronInterval, 'm') ) {
                //exit("Hourly");
                $cronMin = str_replace('m','',$cronInterval);
                $cronJob = "*/$cronMin * * * * " . " " . $cronJobCommand;
            }

            if( $cronJob == NULL && str_contains($cronInterval, 'h') ) {
                //exit("Hourly");
                $cronHour = str_replace('h','',$cronInterval);
                $cronJob = "0 */$cronHour * * * " . " " . $cronJobCommand;
            }

//            //$cronInterval = "1d";
            if( $cronJob == NULL && str_contains($cronInterval, 'd') ) {
                exit("Daily");
//                $cronDay = str_replace('d','',$cronInterval);
//                $cronJob = "0 0 */$cronDay * * * " . " " . $statusCronJobCommand;
            }

            if( $cronJob ) {
                if( $this->getCronJobFullNameLinux($commandName) === false ) {
                    $this->addCronJobLinux($cronJob);
                    $resArr[] = "Created $cronJobName cron job";
                } else {
                    $resArr[] = "$cronJobName already exists";
                }
            }

            return implode(", ",$resArr);
        }

        return NULL;
    }
    //NOT USED
    public function createDbBackupCronLinux() {
        //$cronJobName = "dbbackup-hourly"; //or "dbbackup-daily"
        return $this->createBackupCronLinux("dbbackup-hourly","dbBackupConfig");
    }

    //$cronJobName="uploadsarchive-DAILY"
    //$configFieldName="dbBackupConfig"
    public function createBackupCronLinux( $cronJobName, $configFieldName ) {
        if( $this->isWindows() ) {
            //Windows
            return "Windows is not supported";
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');
        $logger->notice("Creating Independent Monitor cron job for Linux");
        //$projectDir = $this->container->get('kernel')->getProjectDir();

        //$cronJobName = "filesbackup"; //"cron:independentmonitor";
        //$cronJobName = $cronJobName." --env=prod";

        $backupJsonConfig = $userSecUtil->getSiteSettingParameter($configFieldName); //in min
        if( !$backupJsonConfig ) {
            return $configFieldName." is not provided";
        }

        $backupJsonConfigPrepared = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $backupJsonConfig);

        //$backupJsonConfigPrepared = '{"command":[{"s":"sss","t":"ttt"},{"d":"ddd","n":"nnn"}]}';

        //echo "backupConfigPrepared=$backupJsonConfigPrepared<br>";
        $jsonObject = json_decode($backupJsonConfigPrepared,true);

        if( !$jsonObject ) {
            $backupJsonConfigPrepared = str_replace(array('/', '\\'), '/', $backupJsonConfig);
            //echo "$backupJsonConfigPrepared=[$backupJsonConfigPrepared]<br>";
            $jsonObject = json_decode($backupJsonConfigPrepared,true);
        }

        //dump($jsonObject);
        //exit('after json_decode');

        $helpStr = $this->getJsonHelpStr();

        if( !$jsonObject ) {
            return "Cannot decode JSON configuration file: ".$backupJsonConfig. " <br>".$helpStr;
        }

        $resArr = array();

        $sets = NULL;
        if( array_key_exists('sets', $jsonObject) ) {
            $sets = $jsonObject['sets'];
        }

        foreach($sets as $set) {

            //parse set
            $idName = NULL;
            $cronJobCommand = NULL;
            $cronInterval = NULL;

            if( array_key_exists('idname', $set) ) {
                $idName = $set['idname'];
            }

            if( $idName != $cronJobName ) {
                continue; //skip
            }

            if( array_key_exists('command', $set) ) {
                $cronJobCommand = $set['command'];
            }
            if( array_key_exists('croninterval', $set) ) {
                $cronInterval = $set['croninterval'];
            }

            //echo "parsed: [$cronJobCommand] [$cronInterval]<br>";

            $cronJob = NULL;

            //$cronInterval = "5m"; //every 5 minutes
            if( $cronJob == NULL && $cronInterval && str_contains($cronInterval, 'm') ) {
                //exit("Minutes");
                $cronMin = str_replace('m','',$cronInterval);
                $cronJob = "*/$cronMin * * * * " . " " . $cronJobCommand;
            }

            //$cronInterval = "1h";
            if( $cronJob == NULL && $cronInterval && str_contains($cronInterval, 'h') ) {
                //exit("Hourly");

                $cronHour = str_replace('h','',$cronInterval);
                $cronJob = "0 */$cronHour * * * " . " " . $cronJobCommand;
            }

            //$cronInterval = "1d";
            if( $cronJob == NULL && $cronInterval && str_contains($cronInterval, 'd') ) {
                //exit("Daily");

                $cronDay = str_replace('d','',$cronInterval);
                $cronJob = "0 0 */$cronDay * * * " . " " . $cronJobCommand;
            }

            if( $cronJob == NULL && $cronInterval && str_contains($cronInterval, 'e') ) {
                //exit("Every day (e) at X hours"); //'0 3 * * *' - every day at 3 AM, '0 15 * * *' - every day at 3 PM

                $cronTime = str_replace('e','',$cronInterval);
                $cronJob = "0 $cronTime * * * " . " " . $cronJobCommand;
            }

            //Create cron job
            if( $cronJob ) {
                //echo "Create cronJobName=".$cronJobName."<br>";
                if( $this->getCronJobFullNameLinux($cronJobName) === false ) {
                    $this->addCronJobLinux($cronJob);
                    $resArr[] = "Created $cronJobName cron job";
                } else {
                    $resArr[] = "$cronJobName already exists";
                }
            }

            if( !$cronJob ) {
                $resArr[] = "Cron job interval is not provided and cron job can not be created";
            }

        } //foreach sets

        if( count($resArr) == 0 ) {
            $resArr[] = "Cron failed. Probably invalid JSON file. Please make sure 'sets' keyword is exists. <br>".$helpStr;
        }

        $logger->notice("createBackupCronLinux: ".implode(", ",$resArr));
        //dump($resArr);
        //exit('EOF createBackupCronLinux');

        return implode(", ",$resArr);
    }
    public function getBackupManageCronLinux( $backupJsonConfig ) {
        //exit("backupJsonConfig=$backupJsonConfig");
        $sets = $this->getBackupConfigSets($backupJsonConfig);
        //dump($sets);

        $setCronManageArr = array();

        if( !is_array($sets) ) {
            $helpStr = $this->getJsonHelpStr();
            return 'Error: invalid json file. <br><br>'.$helpStr;
        }

        //exit('after getBackupConfigSets');

        foreach($sets as $set) {
            //parse set
            $idName = NULL;

            if( array_key_exists('idname', $set) ) {
                $idName = $set['idname'];
            }
            $setCronManageArr[] = $idName;
        }

        //dump($setCronManageArr);
        //exit('after setCronManageArr');
        return $setCronManageArr;
    }
    public function getBackupConfigSets( $backupJsonConfig ) {

        if( !$backupJsonConfig ) {
            return "Config JSON file is not provided";
        }

        $backupJsonConfigPrepared = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $backupJsonConfig);

        //$backupJsonConfigPrepared = '{"command":[{"s":"sss","t":"ttt"},{"d":"ddd","n":"nnn"}]}';

        //echo "backupConfigPrepared=$backupJsonConfigPrepared<br>";
        $jsonObject = json_decode($backupJsonConfigPrepared,true);

        if( !$jsonObject ) {
            $backupJsonConfigPrepared = str_replace(array('/', '\\'), '/', $backupJsonConfig);
            //echo "$backupJsonConfigPrepared=[$backupJsonConfigPrepared]<br>";
            $jsonObject = json_decode($backupJsonConfigPrepared,true);
        }

        //dump($jsonObject);
        //exit('after json_decode');

        if( !$jsonObject ) {
            return "Cannot decode JSON configuration file: ".$backupJsonConfig;
        }

        $sets = NULL;
        if( array_key_exists('sets', $jsonObject) ) {
            $sets = $jsonObject['sets'];
        }

        return $sets;
    }

    //Used by DataBackupManagemenetController.php
    //create-backup: result file: 'backupdb-...'
    //dbManagePython is a wraper for a python's script order-lab\utils\db-manage\postgres-manage-python\manage_postgres_db.py
    //$networkDrivePath = my/path/
    public function dbManagePython( $networkDrivePath, $action, $sync=true, $backupFileName=null ) {

//        if ( false == $this->security->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//            $res = array(
//                'status' => "NOTOK",
//                'message' => "No permission"
//            );
//            return $res;
//        }

        $logger = $this->container->get('logger');
        $logger->notice("dbManagePython: start: networkDrivePath=$networkDrivePath, action=$action, sync=$sync, backupFileName=$backupFileName");

        $userServiceUtil = $this->container->get('user_service_utility');
        if( $userServiceUtil->isWindows() ){
            $res = array(
                'status' => "NOTOK",
                'message' => "DB management is not implemented for Windows"
            );
            return $res;
        }

        //manage_postgres_db.py is using sample.config file with a local storage as a destination path=/tmp/backups/
        //$filepath is provided by site settings networkDrivePath => manage_postgres_db.py should accept --path

        $dbName = $this->container->getParameter('database_name');
        if( !$dbName ) {
            $res = array(
                'status' => "NOTOK",
                'message' => "Logical error: database_name is not defined in the parameters.yml"
            );
            return $res;
        }

        //ini_set('memory_limit', 0);
//        ini_set('max_execution_time', 0);
//        ini_set('max_input_time', 0);
//        ini_set("default_socket_timeout", 6000); //sec

        $projectRoot = $this->container->get('kernel')->getProjectDir();
        //echo "projectRoot=".$projectRoot."<br>";

        //For multitenancy is not 'order-lab' anymore, but 'order-lab-tenantapp1'
        //$projectRoot = str_replace('order-lab', '', $projectRoot);
        $parentRoot = str_replace('orderflex', '', $projectRoot);
        $parentRoot = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, '', $parentRoot);
        //echo "parentRoot=".$parentRoot."<br>";
        //exit('111');


        $managePackagePath = $parentRoot .
            //DIRECTORY_SEPARATOR . 'order-lab' .
            //DIRECTORY_SEPARATOR . "utils" .
            "utils" .
            DIRECTORY_SEPARATOR . "db-manage" .
            DIRECTORY_SEPARATOR . "postgres-manage-python";
        //echo '\n dbManagePython: managePackagePath='.$managePackagePath."\n<br>";

        //Instead of db.config, use config/paramaters.yml
        //config file
        //$configFilePath = $managePackagePath . DIRECTORY_SEPARATOR . "db.config";

        //TODO: check/create d.config file
        //[setup]
        //storage_engine=LOCAL
        //[local_storage]
        //path=C:\Users\ch3\Documents\MyDocs\WCMC\Backup\db_backup_manag\
        //[postgresql]
        //host=127.0.0.1
        //port=5432
        //db=ScanOrder
        //user=username
        //password=userpassword

        $pythonScriptPath = $managePackagePath . DIRECTORY_SEPARATOR . "manage_postgres_db.py";
        //exit('pythonScriptPath='.$pythonScriptPath);

        //python in virtualenv'ed scripts: /path/to/venv/bin/python3
        if( $userServiceUtil->isWindows() ){
            $pythonEnvPath = $managePackagePath .
                DIRECTORY_SEPARATOR . "venv" .
                DIRECTORY_SEPARATOR . "Scripts" . //Windows
                DIRECTORY_SEPARATOR . "python";
        } else {
            $pythonEnvPath = $managePackagePath .
                DIRECTORY_SEPARATOR . "venv" .
                DIRECTORY_SEPARATOR . "bin" . //Linux
                DIRECTORY_SEPARATOR . "python";
        }
        //echo "pythonEnvPath=".$pythonEnvPath."<br>";
        if( file_exists($pythonEnvPath) ) {
            //echo "The file $filename exists";
        } else {
            $msg = "Error in DB management (action $action): The file $pythonEnvPath does not exist.".
                " Make sure pytnon's environment venv has been installed";
            $res = array(
                'status' => "NOTOK",
                'message' => $msg
            );
            return $res;
        }

        //$command = "$pythonEnvPath $pythonScriptPath --configfile $configFilePath --action list --verbose true --path $networkDrivePath";
        //$command = "$pythonEnvPath $pythonScriptPath --configfile $configFilePath --action list_dbs --verbose true --path $networkDrivePath";

        $dbUsername = $this->container->getParameter('database_user');
        $dbPassword = $this->container->getParameter('database_password');

        $command = "$pythonEnvPath $pythonScriptPath".
            //" --configfile $configFilePath".
            //" --verbose true".
            " --path $networkDrivePath".
            " --source-db $dbName".
            " --user $dbUsername".
            " --password $dbPassword"
            //" --email $email".
            //" --emailUser $emailUser".
            //" --emailPassword $emailPassword"
        ;

        $commandArr = [];
        //$commandArr[] = 'nohup';  //Prevents the process from being terminated when the session ends.
        $commandArr[] = $pythonEnvPath;           // e.g. '/usr/bin/python3'
        $commandArr[] = $pythonScriptPath;        // e.g. '/path/to/script.py'
        $commandArr[] = '--path';
        $commandArr[] = $networkDrivePath;
        $commandArr[] = '--source-db';
        $commandArr[] = $dbName;
        $commandArr[] = '--user';
        $commandArr[] = $dbUsername;
        $commandArr[] = '--password';
        $commandArr[] = $dbPassword;


//        "-h, --mailerhost       mailer host\n" \
//        "-o, --mailerport       mailer port\n" \
//        "-u, --maileruser       mailer username\n" \
//        "-p, --mailerpassword   mailer password\n" \
//        "-r, --receivers        comma separated receiver's emails\n" \
//        "-s, --sender           sender's emails\n" \

        if( $action == 'backup' ) {
            //backup

            //prefix - string to add to the backup filename "backup-prefix-..."
            $userSecUtil = $this->container->get('user_security_utility');
            $environment = $userSecUtil->getSiteSettingParameter('environment');
            if( !$environment ) {
                $environment = "unknownenv";
            }
            //better to use instanceId
            $instanceId = $userSecUtil->getSiteSettingParameter('instanceId');
            if( !$instanceId ) {
                $instanceId = "unknowinstanceId";
            }

            //TODO: check error
            // /usr/local/bin/order-lab-tenantapp1/utils/db-manage/postgres-manage-python/venv/bin/python /usr/local/bin/order-lab-tenantapp1/utils/db-manage/postgres-manage-python/manage_postgres_db.py --configfile /usr/local/bin/order-lab-tenantapp1/utils/db-manage/postgres-manage-python/db.config --verbose true --path /usr/local/bin/order-lab-tenantapp1/orderflex/var/backups/ --source-db tenantapp1 --user symfony --password symfony --action backup --prefix live
            //Maybe provide server name: gethostname() -> xits-po-order-prd2

            $command = $command . " --action backup --prefix ".$environment."-".$instanceId."-".gethostname();

            $commandArr[] = "--action";
            $commandArr[] = "backup";
            $commandArr[] = "--prefix";
            $commandArr[] = $environment."-".$instanceId."-".gethostname();
            
        } elseif( $action == 'restore' ) {
            //exit('DB restore is disabled');
            //restore
            if( $backupFileName ) {
                $command = $command . " --action restore --dump_file $backupFileName";
                $commandArr[] = '--action';
                $commandArr[] = 'restore';
                $commandArr[] = '--dump_file';
                $commandArr[] = $backupFileName;

            } else {
                $msg = "Error in DB management (action $action): backup file is not provided";
                $res = array(
                    'status' => "NOTOK",
                    'message' => $msg
                );
                return $res;
            }
        } else {
            //Invalid action
            $msg = "Error in DB management (action $action): invalid action ".$action;
            $res = array(
                'status' => "NOTOK",
                'message' => $msg
            );
            return $res;
        }

        $logger->notice("dbManagePython: sync=$sync, command=[".$command."]");
        $commandArrStr = implode(" ",$commandArr);
        $logger->notice("dbManagePython: sync=$sync, commandArr=[".$commandArrStr."]");

        //$sync = true; //asynchronous process
        //$sync = false; //synchronous process
        if( $sync ) {
            $logger->notice("dbManagePython: sync=False => run synchronous");
            $res = $this->runProcess($command);
        } else {
            $logger->notice("dbManagePython: sync=True => run asynchronous");
//            $res = $this->runAsyncProcess($commandArr);
            //$res = $this->runAsyncProcessWithEmail($commandArr);
            $res = $this->runAsyncExecProcess($command);
        }

        //echo "python res=".$res."<br>";
        //exit('111');

        $res = array(
            'status' => "OK",
            'message' => $res
        );
        return $res;
    }

    //Use tar command directly 'tar -zcf ...'.
    //Alternatively, backup/filesbackup.py can be used
    //create-backup-upload: result file: 'backupfiles-...'
    public function createBackupUpload( $backupPath=null ) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        if( !$backupPath ) {
            $backupPath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        }

        //echo "backupPath=".$backupPath."<br>";
        if( !$backupPath ) {
//            //exit("No networkDrivePath is defined");
//            $this->addFlash(
//                'pnotify-error',
//                //'notice',
//                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
//            );
//            return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
            //return "Cannot continue with Backup: Backup location path is not provided and/or not defined in the Site Settings";
            $logger->warning("Cannot continue with uploaded folder backup: Backup location path is not provided and/or not defined in the Site Settings");
            $res = array(
                'status' => "NOTOK",
                'message' => "Cannot continue with uploaded folder backup: Backup location path is not provided and/or not defined in the Site Settings"
            );
            return $res;
        }

        $res = NULL;

        if( $backupPath ) {

            set_time_limit(7200); //3600 seconds => 1 hours, 7200 sec => 2 hours
            //set_time_limit(900); //900 sec => 15 min

            //create backup tar -zcvf archive.tar.gz directory/
            $backupPath = realpath($backupPath); //C:\Users\ch3\Documents\MyDocs\WCMC\Backup\db_backup_manag
            //exit($backupPath);

            $environment = $userSecUtil->getSiteSettingParameter('environment');
            if (!$environment) {
                $environment = "unknownenv";
            }

            //better to use instanceId
            $instanceId = $userSecUtil->getSiteSettingParameter('instanceId');
            if( !$instanceId ) {
                $instanceId = "unknowinstanceId";
            }

            $instanceId = $instanceId . "_" . gethostname();

            $date = date('Y-m-d-H-i-s');
            $archiveFile = "backupfiles-" . $environment . "_" . $instanceId . "_" . $date . ".tar.gz";
            $archiveFile = $backupPath . DIRECTORY_SEPARATOR . $archiveFile;
            //echo "archiveFile=".$archiveFile."<br>";

            $projectRoot = $this->container->get('kernel')->getProjectDir();
            //echo "projectRoot=".$projectRoot."<br>";
            $folder = $projectRoot . DIRECTORY_SEPARATOR . "public";//.DIRECTORY_SEPARATOR."Uploaded";
            //$folder = $projectRoot.DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."Uploaded".DIRECTORY_SEPARATOR."calllog";
            //echo "folder=".$folder."<br>";
            //exit('111');

            //Error: The command "tar -zcvf /opt/order-lab/orderflex/var/backups/backupfiles-test_2023-09-12-20-28-20.tar.gz
            // /opt/order-lab/orderflex/public/Uploaded" failed. Exit Code: 2(Misuse of shell builtins)

            $targetFolder = "Uploaded";
            //$targetFolder = "UploadedTest"; //testing

            //use tar.gz archive
            $command = "tar -zcf $archiveFile -C $folder $targetFolder"; //create backup
            //echo "command=".$command."<br>";

            $logger->notice("createUploadBackupAction. before command=" . $command);

            #$resUploadFolder = $this->runProcess($command);
            //$resUploadFolder = $this->runAsyncExecProcess($command);
//            if (!$resUploadFolder) {
//                $resStr = "Backup filename: $archiveFile";
//                $res = array(
//                    'status' => "OK",
//                    'message' => $resStr
//                );
//            } else {
//                $res = array(
//                    'status' => "NOTOK",
//                    'message' => "Error in createBackupUpload: ".$resUploadFolder
//                );
//            }

            $resStr = $this->runAsyncProcessWithEmail('folder-backup',$command);
            $logger->notice("createUploadBackupAction. after resStr=" . $resStr);
            $resStr = "Folder backup started. Archive filename: $archiveFile" . "; " . $resStr;
            $res = array(
                'status' => "OK",
                'message' => $resStr
            );

//            //Event Log
//            $user = $this->security->getUser();
//            $sitename = $this->container->getParameter('employees.sitename');
//            $userSecUtil->createUserEditEvent($sitename, $res, $user, null, $request, 'Create Backup Upload Files');
        }

        return $res;
    }

    public function removeOldBackupFiles( $networkDrivePath=NULL ) {
        $keepNumber = 5;

        if( !$networkDrivePath ) {
            $userSecUtil = $this->container->get('user_security_utility');
            $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
            //echo "networkDrivePath=".$networkDrivePath."<br>";
        }
        if( !$networkDrivePath ) {
            return "removeOldBackupFiles: Cannot proceed: backup path and/or networkDrivePath is not specified";
        }

        //get files from $networkDrivePath
        $files = $this->getBackupFiles($networkDrivePath);
        //echo "Total files ".count($files)."<br>";

        $removedDbCount = $this->processOldBackupFiles($networkDrivePath,$files,$keepNumber,'backupdb-');
        $removedFilesCount = $this->processOldBackupFiles($networkDrivePath,$files,$keepNumber,'backupfiles-');
        return "Removed $removedDbCount DB backup files and $removedFilesCount Upload files";
    }
    public function processOldBackupFiles($networkDrivePath,$files,$keepNumber,$filePartialName) {
        $removedCount = 0;
        $count = 0;
        foreach( $files as $file ) {
            //echo "file id=".$file['id'].", name=".$file['name']."<br>";
            if( str_contains($file['name'],$filePartialName) ) {
                //Keep only the first $keepnumber
                //echo "backupdb file id=".$file['id'].", name=".$file['name']."<br>";
                $filePath = $networkDrivePath.$file['id'];
                //echo "filePath=".$filePath."<br>";
                if (is_file($filePath)) {
                    if( $count >= $keepNumber ) {
                        unlink($filePath);
                        //echo "Removed: $filePath <br>";
                        $removedCount++;
                    }
                }
                $count++;
            }
        }
        return $removedCount;
    }
    public function getBackupFiles( $networkDrivePath ) {
        if( !$networkDrivePath ) {
            return null;
        }

        if (file_exists($networkDrivePath)) {
            //echo "The path=$networkDrivePath";
        } else {
            //echo "The file $networkDrivePath does not exist";
            return null;
        }

        //echo "networkDrivePath=$networkDrivePath <br>";

        //$files = scandir($networkDrivePath); //with dots
        $files = $this->better_scandir($networkDrivePath,SCANDIR_SORT_DESCENDING);
        //dump($files);
        //exit('111');

        $backupFiles = array();
        if( $files && is_array($files) ) {
            $files = array_diff($files, array('..', '.'));
            foreach( $files as $file ) {
                //echo "file=$file <br>";
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if( $ext && $ext != 'log' && $ext != 'txt' ) {
                    $filePath = $networkDrivePath . DIRECTORY_SEPARATOR . $file;
                    $fileName = $file .
                        " (" .
                        date("F d Y H:i:s", filemtime($filePath)) .
                        ", " . $this->formatSizeUnits(filesize($filePath)) .
                        ")";
                    $fileOption = array("id" => $file, "name" => $fileName);
                    $backupFiles[] = $fileOption;
                }
            }
        }
        //dump($files);
        //exit('111');
        return $backupFiles;
    }
    public function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
    //https://stackoverflow.com/questions/11923235/scandir-to-sort-by-date-modified
    public function better_scandir($dir, $sorting_order = SCANDIR_SORT_ASCENDING) {

        /****************************************************************************/
        // Roll through the scandir values.
        //$files = array();
        $files = $this->getFiles( $dir, $sorting_order );
        //dump($files);
        //exit('111');

        /****************************************************************************/
        // Sort the files array.
        if ($sorting_order == SCANDIR_SORT_ASCENDING) {
            asort($files, SORT_NUMERIC);
        }
        else {
            arsort($files, SORT_NUMERIC);
        }

        /****************************************************************************/
        // Set the final return value.
        $ret = array_keys($files);

        /****************************************************************************/
        // Return the final value.
        return $ret;

    } // better_scandir
    public function getFiles( $dir, $sorting_order ) {
        /****************************************************************************/
        // Roll through the scandir values.
        $files = array();
        foreach (scandir($dir, $sorting_order) as $file) {
            if ($file[0] === '.') {
                continue;
            }
            $files[$file] = filemtime($dir . '/' . $file);
        } // foreach

        return $files;
    }

    public function getJsonHelpStr() {
        $helpStr = '
                {"sets" : [
                    {"idname" : "unique-cronjob-name1","command" : "command1 with unique-cronjob-name1 string","croninterval" : "15m"},
                    {"idname" : "unique-cronjob-name2","command" : "command2 with unique-cronjob-name2 string","croninterval" : "3h"},
                    {"idname" : "unique-cronjob-name3","command" : "command3 with unique-cronjob-name3 string","croninterval" : "1d"}
                ]}
            ';
        $helpStr = "Example of valid JSON file: <br>" . $helpStr;
        return $helpStr;
    }

    public function addCronJobLinux( $fullCommand ) {
        $crontab = new Crontab();
        $res = $crontab->addJob($fullCommand);
        return $res;
    }

    //$commandName: 'cron:statustest'
    public function removeCronJobLinuxByCommandName( $commandName ) {
        $res = false;
        $cronJobFullName = $this->getCronJobFullNameLinux($commandName);
        if( $cronJobFullName ) {
            //echo "remove CronJobLinux By CommandName: cronJobFullName=[$cronJobFullName] <br>";
            $crontab = new Crontab();
            $res = $crontab->removeJob($cronJobFullName);
        }

        return $res;
    }

    public function getCronStatus( $cronJobName, $asBoolean=true ) {
        if( $this->isWindows() ){
            return $this->getCronStatusWindows($cronJobName);
        } else {
            return $this->getCronStatusLinux($cronJobName,$asBoolean);
        }
    }

    public function listAllCronJobsLinux($user=null) {
        $crontab = new Crontab();
        $jobs = $crontab->getJobs($user);
        //$jobs = $crontab->getJobs('postgres');
        
        if( isset($jobs) && is_array($jobs) ) {
            $res = "";
            foreach ($jobs as $job) {
                $res = $res . "<p>".$job."</p>";
                //echo "job=[".$job."]<br>";
                //$job = job=*/2 * * * * /opt/remi/php81/root/usr/bin/php /opt/order-lab/orderflex/bin/console cron:statustest --env=prod
            }
            return $res;
        }

        return false;
    }

    //Need permission to run it as apache user
    public function listAllCronJobsAllUsersLinux() {
        $command = 'tail -n 1000 /var/spool/cron/*';
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(120); //sec; 120 sec => 2 min
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $info = $process->getOutput();
        return $info;
    }
    
    //$commandName - 'cron:statustest'
    public function getCronJobFullNameLinux( $commandName, $asBoolean=true ) {
        $existingJobs = array();

        //make ' cron:statustest '
        //$commandName = " "."cron:".$cronJobName." ";
        //echo "commandName=$commandName <br>";
        //exit('111');

        $commandName = trim((string)$commandName);

        //make ' cron:statustest '
        //$commandName = " ".$commandName." ";

        $crontab = new Crontab();

        //$jobs = $crontab->getJobsAsSimpleArray();
        $jobs = $crontab->getJobs();

        if( isset($jobs) && is_array($jobs) ) {

            foreach ($jobs as $job) {
                //echo "[".$commandName."]: job=[".$job."]<br>";
                //$job = job=*/2 * * * * /opt/remi/php81/root/usr/bin/php /opt/order-lab/orderflex/bin/console cron:statustest --env=prod

                //if (strpos((string)$job, $commandName) !== false) {
                if( str_contains($job,$commandName) ) {

                    if( $asBoolean ) {
                        return $job."";
                        break;
                    } else {
                        $existingJobs[] = $job."";
                    }

                }
            }
        }

        if( count($existingJobs) > 0 ) {
            return implode("<br>",$existingJobs);
        }

        return false;
    }
    public function getCronStatusLinux($cronJobName, $asBoolean=false) {

        //$commandName = "cron:".$cronJobName;
        $commandName = $cronJobName;
        //echo "commandName=$commandName <br>";
        //exit('111');

        $cronJobFullName = $this->getCronJobFullNameLinux($commandName,$asBoolean);

        if( $asBoolean ) {
            return $cronJobFullName;
        } else {
            if( $cronJobFullName ) {
                $resStr = '<font color="green">'.$commandName.' cron job status: '.$cronJobFullName.'</font>';
            } else {
                $resStr = '<font color="red">'.$commandName.' cron job status: not found</font>';
            }
            return $resStr;
        }

        return false;
    }

    public function getCronStatusWindows($cronJobName, $asBoolean=false) {
        //$cronJobName = "Swiftmailer";
        $command = 'SchTasks | FINDSTR "'.$cronJobName.'"';
        $res = exec($command);

        if( $res ) {
            if( $asBoolean ) {
                $res = true;
            } else {
                //$res = "Cron job status: " . $crontab->render();
                $res = '<font color="green">Cron job status: '.$res.'.</font>';
            }
        } else {
            if( $asBoolean ) {
                $res = false;
            } else {
                //$res = "Cron job status: " . $crontab->render();
                $res = '<font color="red">Cron job status: not found.</font>';
            }
        }
        //exit($res);
        return $res;
    }

    //https://www.php.net/manual/en/function.realpath.php
    //Will convert /path/to/test/.././..//..///..///../one/two/../three/filename to ../../one/three/filename
    function normalizePath($path)
    {
        $parts = array();// Array to build a new path from the good parts
        $path = str_replace('\\', '/', $path);// Replace backslashes with forwardslashes
        $path = preg_replace('/\/+/', '/', $path);// Combine multiple slashes into a single slash
        $segments = explode('/', $path);// Collect path segments
        $test = '';// Initialize testing variable
        foreach($segments as $segment)
        {
            if($segment != '.')
            {
                $test = array_pop($parts);
                if(is_null($test))
                    $parts[] = $segment;
                else if($segment == '..')
                {
                    if($test == '..')
                        $parts[] = $test;

                    if($test == '..' || $test == '')
                        $parts[] = $segment;
                }
                else
                {
                    $parts[] = $test;
                    $parts[] = $segment;
                }
            }
        }
        return implode('/', $parts);
    }

    function execInBackground($cmd) {
        if( $this->isWinOs() ){
            //pclose(popen("start /B ". $cmd, "r"));
            $oExec = pclose(popen("start /B ". $cmd, "r"));
        }
        else {
            //$phppath = "/opt/remi/php74/root/usr/bin/php";
            $phppath = $this->getPhpPath();
            $cmd = str_replace("php ", $phppath . " ", $cmd);
            $logger = $this->container->get('logger');
            $logger->notice("execInBackground cmd=" . $cmd);
            //echo exec($cmd, $oExec);
            $oExec = exec($cmd . " > /dev/null &");
        }

        return $oExec;
    }

    public function getPublicFolderName() {
        $projectDir = $this->container->getParameter('kernel.project_dir');
        exit("projectDir=$projectDir");
    }

//    public function getUserEncoder($user=null) {
////        $defaultEncoder = new MessageDigestPasswordEncoder('sha512', true, 5000);
////        $encoders = [
////            User::class => $defaultEncoder, // Your user class. This line specify you ant sha512 encoder for this user class
////        ];
////
////        $encoderFactory = new EncoderFactory($encoders);
////        $encoder = $encoderFactory->getEncoder($user);
//
//        $encoder = $this->container->get('security.password_encoder');
//        return $encoder;
//    }

    //used by cron:externalurlmonitor
    public function checkExternalUrlMonitor() {
        //check if url is accessible
        //send notification email if not accessible
        $userSecUtil = $this->container->get('user_security_utility');
        
        //$url = 'http://google.com';
        //$url = 'http://view-test.med.cornell.edu';
        //$urls = 'http://view.med.cornell.edu, http://1view-test.med.cornell.edu';
        //$urls = 'http://view.med.cornell.edu/directory/sign-up'
        //externalMonitorUrl
        $urls = $userSecUtil->getSiteSettingParameter('externalMonitorUrl');
        if( !$urls ) {
            return "Urls are not provided";
        }

        //$urls = 'http://view.med.cornell.edu/directory/login';

        $urls = str_replace(" ","",$urls);
        $urlsArr = explode(",",$urls);
        $res = array();
        foreach($urlsArr as $url) {
            $res[] = $this->checkSingleUrlMonitor($url);
        }
        $resStr = "No site to check";
        if( count($res) > 0 ) {
            $resStr = implode("; ",$res);
        }
        return $resStr;
    }
    public function checkSingleUrlMonitor($url) {
        //check if url is accessible
        //sen notification email if not accessible
        list($status) = @get_headers($url);
        //var_dump($status);

        $serverUp = false;

        //if( $status && strpos($status, '200') !== TRUE ) {
        if( $status && str_contains($status,'200') ) {
            // URL is 202ing is up
            //$res = "Site $url is up: status=".$status;
            //$res = "up";
            $serverUp = true;
        }
        elseif( $status && str_contains($status,'500 Internal Server Error') ) {
            $serverUp = false;
        }
        elseif( $status && str_contains($status,'Error') ) {
            $serverUp = false;
        } else {
            $serverUp = false;
        }

        if( $serverUp === true ) {
            $res = "Site $url is up: status=".$status;
        } else {
            $res = "Site $url is down: status=".$status;
            //$res = "down";

            //send email
            $userSecUtil = $this->container->get('user_security_utility');
            $emailUtil = $this->container->get('user_mailer_utility');
            $environment = $userSecUtil->getSiteSettingParameter('environment');
            $emails = $userSecUtil->getUserEmailsByRole(null,"Platform Administrator");
            //$emails = array("oli2002@med.cornell.edu"); //testing

            //siteEmail
            $sender = $userSecUtil->getSiteSettingParameter('siteEmail');
            if( $sender ) {
                if( $emails ) {
                    $emails[] = $sender;
                } else {
                    $emails = array($sender);
                }
            }

            //$subject = "Warning! ".$res . " (sent by the external ORDER system on $environment server)";
            $subject = "Site $url appears inaccessible!";
            
            //body: “Site [URL/link] does not appear to be accessible. Please verify the site is operational.”
            $msg = "Site $url does not appear to be accessible. Please verify the site is operational. <br><br> Sent by the external ORDER system on $environment server";
            
            $emailUtil->sendEmail($emails,$subject,$msg);

            //Event Log
            $eventType = "Site Down";
            $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'), $msg, null, null, null, $eventType);
        }

        return $res;
    }

    //check system status. Used by StatusCronCommand (php bin/console cron:status --env=prod)
    public function checkStatus() {

        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        $msg = "checkStatus";

        $maintenance = $userSecUtil->getSiteSettingParameter('maintenance');
        if( !$maintenance ) {
            return "Maintenance is off";
        }

        //1) check event log for
        // "Site Settings parameter [maintenance] has been updated by" and
        // "updated value: 1"

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");

        $dql->leftJoin('logger.eventType', 'eventType');

        $queryParameters = array();

        //Site Settings Parameter Updated
        $dql->andWhere("eventType.name = :eventTypeName");
        $queryParameters['eventTypeName'] = 'Site Settings Parameter Updated';

        //Site Settings parameter [maintenance] has been updated by
        $eventStr1 = "Site Settings parameter [maintenance] has been updated by";
        $dql->andWhere("logger.event LIKE :eventStr1");
        $queryParameters['eventStr1'] = '%'.$eventStr1.'%';

        //updated value:<br>1
        $eventStr2 = "updated value:<br>1";
        $dql->andWhere("logger.event LIKE :eventStr2");
        $queryParameters['eventStr2'] = '%'.$eventStr2.'%';

        $dql->orderBy("logger.id","DESC");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
        $query->setParameters( $queryParameters );

        $query->setMaxResults(1);
        $log = $query->getOneOrNullResult();

        if( !$log ) {
            return "Maintenance is off";
        }

        //2) get latest date
        //echo "log ID=".$log->getId()."<br>";
        $latestDate = $log->getCreationdate();
        //echo "latestDate=".$latestDate->format('Y-m-d H:i:s')."<br>";

        //3) check if currentDate is more latestDate by 30 min
        $currentDate = new \DateTime();
        //echo "currentDate=".$currentDate->format('Y-m-d H:i:s')."<br>";

        $maxTime = '30';
        //$maxTime = '1'; //testing

        $currentDate = $currentDate->modify("-$maxTime minutes");

        if( $currentDate > $latestDate ) {
            //$msg = "more than $maxTime min";
            //send email to admin
            $emails = $userSecUtil->getUserEmailsByRole(null,"Platform Administrator");

            //except these users
            $exceptionUsers = $userSecUtil->getSiteSettingParameter('emailCriticalErrorExceptionUsers');
            $exceptionUsersEmails = array();
            foreach($exceptionUsers as $exceptionUser) {
                // echo "exceptionUser=".$exceptionUser."<br>";
                $exceptionUsersEmails[] = $exceptionUser->getSingleEmail();
            }

            if( count($exceptionUsersEmails) > 0 ) {
                $emails = array_diff($emails, $exceptionUsersEmails);
            }

            $emails = array_unique($emails);

            //echo "emails: <br>";
            //print_r($emails);
            //exit('111');

            $subject = "Maintenance Mode On Longer than $maxTime minutes";
            $msg = "Maintenance Mode has been turned on for longer than $maxTime minutes. Please turn it off to allow users to log in:";

            //employees_siteparameters_edit
            //@Route("/{id}/edit", name="employees_siteparameters_edit", methods={"GET"})
            //$param = trim((string)$request->get('param') );

            $router = $userSecUtil->getRequestContextRouter();

            $url = $router->generate(
                'employees_siteparameters_edit',
                array(
                    'id' => 1,
                    'param' => 'maintenance'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $msg = $msg . " " . $url;

            $emailUtil->sendEmail($emails,$subject,$msg);

        } else {
            $msg = "Max time is ok";
        }

        //4) send warning email

        return $msg;
    }

    public function checkStatusTest() {

        $emailUtil = $this->container->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        $siteEmail = $userSecUtil->getSiteSettingParameter('siteEmail');

        $subject = "Testing cron job";
        $msg = "Testing cron job";

        $emailUtil->sendEmail($siteEmail,$subject,$msg);

        //4) send warning email

        return $msg;
    }
    
    ///////////////////////////// TELEPHONY ////////////////////////////////////
    public function assignVerificationCode($user,$phoneNumber) {
        //$text = random_int(100000, 999999);
        $code = $this->generateVerificationCode();

        $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);

        if( $userInfo ) {
            $userInfo->setMobilePhoneVerifyCode($code);
            $userInfo->setPreferredMobilePhoneVerified(false); //should it be unchanged?
            $userInfo->setMobilePhoneVerifyCodeDate(new \DateTime());
            $this->em->flush();
        }

        return $code;
    }
    public function generateVerificationCode($counter=0) {
        $code = random_int(100000, 999999);
        //$code = 111;

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserInfo'] by [UserInfo::class]
        $repository = $this->em->getRepository(UserInfo::class);
        $dql =  $repository->createQueryBuilder("userinfo");
        $dql->select('userinfo');

        $dql->where("userinfo.mobilePhoneVerifyCode = :mobilePhoneVerifyCode");
        //$queryParameters = array('mobilePhoneVerifyCode'=>$code);

        $dql->andWhere("userinfo.mobilePhoneVerifyCodeDate >= :expireDate");
        $expireDate = new \DateTime();
        $expireDate->modify("-2 day");

        $queryParameters = array(
            'mobilePhoneVerifyCode'=>$code,
            'expireDate'=>$expireDate->format('Y-m-d')
        );

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
        $query->setParameters( $queryParameters );

        $userinfos = $query->getResult();

        if( count($userinfos) > 0 ) {
            if( $counter > 100 ) {
                throw new \Exception( 'Possible error in generateVerificationCode: counter='.$counter );
            }

            $counter++;
            $code = $this->generateVerificationCode($counter);
        }

        return $code;
    }
    //https://www.twilio.com/docs/sms/tutorials/how-to-send-sms-messages-php
    public function sendText( $phoneNumber, $textToSend ) {
        // Find your Account Sid and Auth Token at twilio.com/console
        // DANGER! This is insecure. See http://twil.io/secure
        //$sid    = "ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
        //$token  = "your_auth_token";
        //$twilio = new Client($sid, $token);
//        $message = $twilio->messages
//            ->create("+1xxx", // to
//                [
//                    "body" => "This is the ship that made the Kessel Run in fourteen parsecs?",
//                    "from" => "+1xxx"
//                ]
//            );

        $userSecUtil = $this->container->get('user_security_utility');

        $phoneNumberVerification = $userSecUtil->getSiteSettingParameter('phoneNumberVerification','Telephony');
        if( !$phoneNumberVerification ) {
            $message = (object) [
                'errorMessage' => "Phone number verification is disabled.",
            ];
            return $message;
        }

        $twilioSid = $userSecUtil->getSiteSettingParameter('twilioSid','Telephony');
        $twilioApiKey = $userSecUtil->getSiteSettingParameter('twilioApiKey','Telephony');
        $fromPhoneNumber = $userSecUtil->getSiteSettingParameter('fromPhoneNumber','Telephony');

        //$twilioSid = "xxxxx";
        //$twilioApiKey = "xxxxx";
        //$fromPhoneNumber = "xxxxx";

        $twilio = new Client($twilioSid, $twilioApiKey);

        $message = $twilio->messages
            ->create($phoneNumber, // to
                [
                    "body" => $textToSend,      //"This is the test telephony message",
                    "from" => $fromPhoneNumber //"+11234567890"
                ]
            );

        //print($message->sid);

        return $message;
    }
    public function userHasPhoneNumber($phoneNumber) {
        $user = $this->security->getUser();

        $userInfo = $user->getUserInfoByPreferredMobilePhone($phoneNumber);
        //$userInfo = $user->getUserInfo();
        
        if( $userInfo ) {
            //exit($userInfo->getId());
            $userPreferredMobilePhone = $userInfo->getPreferredMobilePhone();
            //echo "[$phoneNumber] =? [$userPreferredMobilePhone]<br>";
            //exit();
            //exit("phoneNumber=[$phoneNumber] ?= userPreferredMobilePhone=[$userPreferredMobilePhone]");
            if( $phoneNumber && $userPreferredMobilePhone && $phoneNumber == $userPreferredMobilePhone ) {
                return true;
            }

            //additional canonical check (without '+')
            $phoneNumber = str_replace('+','',$phoneNumber);
            $userPreferredMobilePhone = str_replace('+','',$userPreferredMobilePhone);
            //echo "[$phoneNumber] =? [$userPreferredMobilePhone]<br>";
            //exit();
            if( $phoneNumber && $userPreferredMobilePhone && $phoneNumber == $userPreferredMobilePhone ) {
                return true;
            }
        } else {
            //exit("userInfo not found by phoneNumber=".$phoneNumber);
        }

        return false;
    }
    public function getVerificationUrl( $verificationCode ) {
        //$user = $this->security->getUser();
        //employees_verify_mobile_code
        $url = $this->container->get('router')->generate(
            'employees_verify_mobile_code',
            array(
                'verificationCode' => $verificationCode,
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        //$urlFull = " <a data-toggle='tooltip' title='Verification Link' href=".$url.">Verify Mobile Phone Number</a>";

        return $url;
    }
    public function getUserByVerificationCode( $verificationCode ) {
        if( !$verificationCode ) {
            return null;
        }

        $repository = $this->em->getRepository(User::class);
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin('user.infos','infos');

        $dql->where("infos.mobilePhoneVerifyCode = :mobilePhoneVerifyCode");
        $queryParameters = array('mobilePhoneVerifyCode'=>$verificationCode);

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
        $query->setParameters( $queryParameters );

        $users = $query->getResult();
        //echo "users count=".count($users)."<br>";
        //exit('111');

        if( count($users) > 0 ) {
            return $users[0];
        }

        return null;
    }


    public function assignAccountRequestVerificationCode($userRequest,$objectName,$phoneNumber) {
        //$text = random_int(100000, 999999);
        $code = $this->generateAccountRequestVerificationCode($objectName);

        //$userInfo = $userRequest->getMobilePhone($phoneNumber);

        if( $userRequest ) {
            $userRequest->setMobilePhoneVerifyCode($code);
            $userRequest->setMobilePhoneVerifyCodeDate(new \DateTime());
            $userRequest->setMobilePhoneVerified(false); //should it be unchanged?
            $this->em->flush();
        }

        return $code;
    }
    public function generateAccountRequestVerificationCode($objectName,$counter=0) {
        $code = random_int(100000, 999999);

        $repository = $this->em->getRepository('App\\UserdirectoryBundle\\Entity\\'.$objectName);
        $dql =  $repository->createQueryBuilder("userrequest");
        $dql->select('userrequest');

        $dql->where("userrequest.mobilePhoneVerifyCode = :mobilePhoneVerifyCode");
        //$queryParameters = array('mobilePhoneVerifyCode'=>$code);

        $dql->andWhere("userrequest.mobilePhoneVerifyCodeDate >= :expireDate");
        $expireDate = new \DateTime();
        $expireDate->modify("-2 day");

        $queryParameters = array(
            'mobilePhoneVerifyCode'=>$code,
            'expireDate'=>$expireDate->format('Y-m-d')
        );

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
        $query->setParameters( $queryParameters );

        $userrequests = $query->getResult();

        if( count($userrequests) > 0 ) {
            if( $counter > 100 ) {
                throw new \Exception( 'Possible error in generateVerificationCode: counter='.$counter );
            }

            $counter++;
            $code = $this->generateAccountRequestVerificationCode($objectName,$counter);
        }

        return $code;
    }

    public function setVerificationEventLog($eventType, $event, $testing=false) {
        $user = null;
        if( $this->security ) {
            $user = $this->security->getUser();
        } 
        $userSecUtil = $this->container->get('user_security_utility');
        if( !$testing ) {
            //            createUserEditEvent($sitename,$event,$user,$subjectEntities,$request,$action='Unknown Event')
            $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'), $event, $user, $user, null, $eventType);
        }
    }

//    public function verificationCodeIsNotExpired( $mobilePhoneHolder ) {
//        $expireDate = new \DateTime();
//        $expireDate->modify("-2 day");
//        $verificationCodeCreationDate = $mobilePhoneHolder->getMobilePhoneVerifyCodeDate();
//        if( !$verificationCodeCreationDate ) {
//            return true;
//        }
//
//        if( $mobilePhoneHolder && $expireDate && $verificationCodeCreationDate >= $expireDate ) {
//            return true;
//        }
//
//        return false;
//    }

    ///////////////////////////// EOF TELEPHONY ////////////////////////////////////


    public function findCountryByIsoAlpha3( $alpha3 ) {

        $iso_array = array(
            'ABW'=>'Aruba',
            'AFG'=>'Afghanistan',
            'AGO'=>'Angola',
            'AIA'=>'Anguilla',
            'ALA'=>'Åland Islands',
            'ALB'=>'Albania',
            'AND'=>'Andorra',
            'ARE'=>'United Arab Emirates',
            'ARG'=>'Argentina',
            'ARM'=>'Armenia',
            'ASM'=>'American Samoa',
            'ATA'=>'Antarctica',
            'ATF'=>'French Southern Territories',
            'ATG'=>'Antigua and Barbuda',
            'AUS'=>'Australia',
            'AUT'=>'Austria',
            'AZE'=>'Azerbaijan',
            'BDI'=>'Burundi',
            'BEL'=>'Belgium',
            'BEN'=>'Benin',
            'BES'=>'Bonaire, Sint Eustatius and Saba',
            'BFA'=>'Burkina Faso',
            'BGD'=>'Bangladesh',
            'BGR'=>'Bulgaria',
            'BHR'=>'Bahrain',
            'BHS'=>'Bahamas',
            'BIH'=>'Bosnia and Herzegovina',
            'BLM'=>'Saint Barthélemy',
            'BLR'=>'Belarus',
            'BLZ'=>'Belize',
            'BMU'=>'Bermuda',
            'BOL'=>'Bolivia, Plurinational State of',
            'BRA'=>'Brazil',
            'BRB'=>'Barbados',
            'BRN'=>'Brunei Darussalam',
            'BTN'=>'Bhutan',
            'BVT'=>'Bouvet Island',
            'BWA'=>'Botswana',
            'CAF'=>'Central African Republic',
            'CAN'=>'Canada',
            'CCK'=>'Cocos (Keeling) Islands',
            'CHE'=>'Switzerland',
            'CHL'=>'Chile',
            'CHN'=>'China',
            'CIV'=>'Côte d\'Ivoire',
            'CMR'=>'Cameroon',
            'COD'=>'Congo, the Democratic Republic of the',
            'COG'=>'Congo',
            'COK'=>'Cook Islands',
            'COL'=>'Colombia',
            'COM'=>'Comoros',
            'CPV'=>'Cape Verde',
            'CRI'=>'Costa Rica',
            'CUB'=>'Cuba',
            'CUW'=>'Curaçao',
            'CXR'=>'Christmas Island',
            'CYM'=>'Cayman Islands',
            'CYP'=>'Cyprus',
            'CZE'=>'Czech Republic',
            'DEU'=>'Germany',
            'DJI'=>'Djibouti',
            'DMA'=>'Dominica',
            'DNK'=>'Denmark',
            'DOM'=>'Dominican Republic',
            'DZA'=>'Algeria',
            'ECU'=>'Ecuador',
            'EGY'=>'Egypt',
            'ERI'=>'Eritrea',
            'ESH'=>'Western Sahara',
            'ESP'=>'Spain',
            'EST'=>'Estonia',
            'ETH'=>'Ethiopia',
            'FIN'=>'Finland',
            'FJI'=>'Fiji',
            'FLK'=>'Falkland Islands (Malvinas)',
            'FRA'=>'France',
            'FRO'=>'Faroe Islands',
            'FSM'=>'Micronesia, Federated States of',
            'GAB'=>'Gabon',
            'GBR'=>'United Kingdom',
            'GEO'=>'Georgia',
            'GGY'=>'Guernsey',
            'GHA'=>'Ghana',
            'GIB'=>'Gibraltar',
            'GIN'=>'Guinea',
            'GLP'=>'Guadeloupe',
            'GMB'=>'Gambia',
            'GNB'=>'Guinea-Bissau',
            'GNQ'=>'Equatorial Guinea',
            'GRC'=>'Greece',
            'GRD'=>'Grenada',
            'GRL'=>'Greenland',
            'GTM'=>'Guatemala',
            'GUF'=>'French Guiana',
            'GUM'=>'Guam',
            'GUY'=>'Guyana',
            'HKG'=>'Hong Kong',
            'HMD'=>'Heard Island and McDonald Islands',
            'HND'=>'Honduras',
            'HRV'=>'Croatia',
            'HTI'=>'Haiti',
            'HUN'=>'Hungary',
            'IDN'=>'Indonesia',
            'IMN'=>'Isle of Man',
            'IND'=>'India',
            'IOT'=>'British Indian Ocean Territory',
            'IRL'=>'Ireland',
            'IRN'=>'Iran, Islamic Republic of',
            'IRQ'=>'Iraq',
            'ISL'=>'Iceland',
            'ISR'=>'Israel',
            'ITA'=>'Italy',
            'JAM'=>'Jamaica',
            'JEY'=>'Jersey',
            'JOR'=>'Jordan',
            'JPN'=>'Japan',
            'KAZ'=>'Kazakhstan',
            'KEN'=>'Kenya',
            'KGZ'=>'Kyrgyzstan',
            'KHM'=>'Cambodia',
            'KIR'=>'Kiribati',
            'KNA'=>'Saint Kitts and Nevis',
            'KOR'=>'Korea, Republic of',
            'KWT'=>'Kuwait',
            'LAO'=>'Lao People\'s Democratic Republic',
            'LBN'=>'Lebanon',
            'LBR'=>'Liberia',
            'LBY'=>'Libya',
            'LCA'=>'Saint Lucia',
            'LIE'=>'Liechtenstein',
            'LKA'=>'Sri Lanka',
            'LSO'=>'Lesotho',
            'LTU'=>'Lithuania',
            'LUX'=>'Luxembourg',
            'LVA'=>'Latvia',
            'MAC'=>'Macao',
            'MAF'=>'Saint Martin (French part)',
            'MAR'=>'Morocco',
            'MCO'=>'Monaco',
            'MDA'=>'Moldova, Republic of',
            'MDG'=>'Madagascar',
            'MDV'=>'Maldives',
            'MEX'=>'Mexico',
            'MHL'=>'Marshall Islands',
            'MKD'=>'Macedonia, the former Yugoslav Republic of',
            'MLI'=>'Mali',
            'MLT'=>'Malta',
            'MMR'=>'Myanmar',
            'MNE'=>'Montenegro',
            'MNG'=>'Mongolia',
            'MNP'=>'Northern Mariana Islands',
            'MOZ'=>'Mozambique',
            'MRT'=>'Mauritania',
            'MSR'=>'Montserrat',
            'MTQ'=>'Martinique',
            'MUS'=>'Mauritius',
            'MWI'=>'Malawi',
            'MYS'=>'Malaysia',
            'MYT'=>'Mayotte',
            'NAM'=>'Namibia',
            'NCL'=>'New Caledonia',
            'NER'=>'Niger',
            'NFK'=>'Norfolk Island',
            'NGA'=>'Nigeria',
            'NIC'=>'Nicaragua',
            'NIU'=>'Niue',
            'NLD'=>'Netherlands',
            'NOR'=>'Norway',
            'NPL'=>'Nepal',
            'NRU'=>'Nauru',
            'NZL'=>'New Zealand',
            'OMN'=>'Oman',
            'PAK'=>'Pakistan',
            'PAN'=>'Panama',
            'PCN'=>'Pitcairn',
            'PER'=>'Peru',
            'PHL'=>'Philippines',
            'PLW'=>'Palau',
            'PNG'=>'Papua New Guinea',
            'POL'=>'Poland',
            'PRI'=>'Puerto Rico',
            'PRK'=>'Korea, Democratic People\'s Republic of',
            'PRT'=>'Portugal',
            'PRY'=>'Paraguay',
            'PSE'=>'Palestinian Territory, Occupied',
            'PYF'=>'French Polynesia',
            'QAT'=>'Qatar',
            'REU'=>'Réunion',
            'ROU'=>'Romania',
            'RUS'=>'Russian Federation',
            'RWA'=>'Rwanda',
            'SAU'=>'Saudi Arabia',
            'SDN'=>'Sudan',
            'SEN'=>'Senegal',
            'SGP'=>'Singapore',
            'SGS'=>'South Georgia and the South Sandwich Islands',
            'SHN'=>'Saint Helena, Ascension and Tristan da Cunha',
            'SJM'=>'Svalbard and Jan Mayen',
            'SLB'=>'Solomon Islands',
            'SLE'=>'Sierra Leone',
            'SLV'=>'El Salvador',
            'SMR'=>'San Marino',
            'SOM'=>'Somalia',
            'SPM'=>'Saint Pierre and Miquelon',
            'SRB'=>'Serbia',
            'SSD'=>'South Sudan',
            'STP'=>'Sao Tome and Principe',
            'SUR'=>'Suriname',
            'SVK'=>'Slovakia',
            'SVN'=>'Slovenia',
            'SWE'=>'Sweden',
            'SWZ'=>'Swaziland',
            'SXM'=>'Sint Maarten (Dutch part)',
            'SYC'=>'Seychelles',
            'SYR'=>'Syrian Arab Republic',
            'TCA'=>'Turks and Caicos Islands',
            'TCD'=>'Chad',
            'TGO'=>'Togo',
            'THA'=>'Thailand',
            'TJK'=>'Tajikistan',
            'TKL'=>'Tokelau',
            'TKM'=>'Turkmenistan',
            'TLS'=>'Timor-Leste',
            'TON'=>'Tonga',
            'TTO'=>'Trinidad and Tobago',
            'TUN'=>'Tunisia',
            'TUR'=>'Turkey',
            'TUV'=>'Tuvalu',
            'TWN'=>'Taiwan, Province of China',
            'TZA'=>'Tanzania, United Republic of',
            'UGA'=>'Uganda',
            'UKR'=>'Ukraine',
            'UMI'=>'United States Minor Outlying Islands',
            'URY'=>'Uruguay',
            'USA'=>'United States',
            'UZB'=>'Uzbekistan',
            'VAT'=>'Holy See (Vatican City State)',
            'VCT'=>'Saint Vincent and the Grenadines',
            'VEN'=>'Venezuela, Bolivarian Republic of',
            'VGB'=>'Virgin Islands, British',
            'VIR'=>'Virgin Islands, U.S.',
            'VNM'=>'Viet Nam',
            'VUT'=>'Vanuatu',
            'WLF'=>'Wallis and Futuna',
            'WSM'=>'Samoa',
            'YEM'=>'Yemen',
            'ZAF'=>'South Africa',
            'ZMB'=>'Zambia',
            'ZWE'=>'Zimbabwe'
        );

        if( isset($iso_array[$alpha3]) ) {
            return $iso_array[$alpha3];
        }

        return NULL;
    }

//    //$yearOffset: 0=>current year, -1=>previous year, +1=>next year
//    //return format: Y-m-d
//    public function getAcademicYearStartEndDates_ORIG( $currentYear=null, $asDateTimeObject=false, $yearOffset=null, $sitename=null ) {
//        $userSecUtil = $this->container->get('user_security_utility');
//        //academicYearStart: July 01
//        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart',$sitename);
//        if( !$academicYearStart ) {
//            throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
//            //$startDate = NULL;
//        }
//        //academicYearEnd: June 30
//        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd',$sitename);
//        if( !$academicYearEnd ) {
//            throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
//            //$endDate = NULL;
//        }
//
//        $startDateMD = $academicYearStart->format('m-d');
//        $endDateMD = $academicYearEnd->format('m-d');
//
//        if( !$currentYear ) {
//            $currentYear = $this->getDefaultAcademicStartYear();
//        }
//
//        $nextYear = $currentYear + 1;
//
//        if( $yearOffset ) {
//            $currentYear = $currentYear + $yearOffset;
//            $nextYear = $nextYear + $yearOffset;
//        }
//
//        $startDate = $currentYear."-".$startDateMD;
//        $endDate = $nextYear."-".$endDateMD;
//        //exit('<br> exit: startDate='.$startDate.'; endDate='.$endDate); //testing
//
//        if( $asDateTimeObject ) {
//            $startDate = \DateTime::createFromFormat('Y-m-d', $startDate);
//            $endDate = \DateTime::createFromFormat('Y-m-d', $endDate);
//        }
//
//        return array(
//            //'currentYear' => $currentYear,
//            'startDate'=> $startDate,
//            'endDate'=> $endDate,
//        );
//    }
    //$yearOffset: 0=>current year, -1=>previous year, +1=>next year
    //return format: Y-m-d
    public function getAcademicYearStartEndDates(
        $currentYear=null,
        $asDateTimeObject=false,
        $yearOffset=null,
        $sitename=null,
        $startfieldname='academicYearStart',
        $endfieldname='academicYearEnd'
    ) {
        $userSecUtil = $this->container->get('user_security_utility');

        if( !$currentYear ) {
            $currentYear = $this->getDefaultAcademicStartYear();
        }
        $nextYear = $currentYear + 1;

        //academicYearStart: July 01
        $academicYearStart = $userSecUtil->getSiteSettingParameter($startfieldname,$sitename);
        if( $academicYearStart ) {
            $startDateMD = $academicYearStart->format('m-d');

            if( $yearOffset ) {
                if( $currentYear ) {
                    $currentYear = $currentYear + $yearOffset;
                }
            }

            $startDate = $currentYear."-".$startDateMD;

            if( $asDateTimeObject ) {
                $startDate = \DateTime::createFromFormat('Y-m-d', $startDate);
            }
        } else {
            $startDate = NULL;
        }

        //academicYearEnd: June 30
        $academicYearEnd = $userSecUtil->getSiteSettingParameter($endfieldname,$sitename);
        if( $academicYearEnd ) {
            $endDateMD = $academicYearEnd->format('m-d');

            if( $yearOffset ) {
                if( $nextYear ) {
                    $nextYear = $nextYear + $yearOffset;
                }
            }

            $endDate = $nextYear."-".$endDateMD;

            if( $asDateTimeObject ) {
                $endDate = \DateTime::createFromFormat('Y-m-d', $endDate);
            }
        } else {
            $endDate = NULL;
        }

        //exit('<br> exit: startDate='.$startDate.'; endDate='.$endDate); //testing

        return array(
            //'currentYear' => $currentYear,
            'startDate' => $startDate,
            'endDate' => $endDate,
        );
    }
    //Get default academic year (if 2021 it means 2021-2022 academic year) according to the academicYearStart in the site settings
    public function getDefaultAcademicStartYear( $sitename=null, $startfieldname='academicYearStart' ) {

        $userSecUtil = $this->container->get('user_security_utility');
        $academicYearStart = $userSecUtil->getSiteSettingParameter($startfieldname,$sitename);
        //echo "getDefaultAcademicStartYear: academicYearStart=$academicYearStart <br>";
        $currentYear = $this->getAcademicStartYear( $academicYearStart );
        //echo "getDefaultAcademicStartYear: currentYear=$currentYear <br>";
        return $currentYear;

        //Moved to getAcademicStartYear
        $currentYear = intval(date("Y"));
        $currentDate = new \DateTime();

        //2011-03-26 (year-month-day)
        $january1 = new \DateTime($currentYear."-01-01");
        //$june30 = new \DateTime($currentYear."-06-30");

        //start date of the academic year
        //$july1 = new \DateTime($currentYear."-07-01"); //get from site setting

        //start date
        $academicYearStart = $userSecUtil->getSiteSettingParameter($startfieldname,$sitename);
        if( $academicYearStart ) {
            $startDateMD = $academicYearStart->format('m-d');
            $july1 = new \DateTime($currentYear."-".$startDateMD);
        } else {
            //throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
            //assume start date July 1st
            $july1 = new \DateTime($currentYear."-07-01");
        }
        //echo "july1=".$july1->format("d-m-Y")."<br>";

        //end date of the year, always December 31
        $december31 = new \DateTime($currentYear."-12-31");

        //Application Season Start Year (applicationSeasonStartDates) set to:
        //current year if current date is between July 1st and December 31st (inclusive) or
        //previous year (current year-1) if current date is between January 1st and June 30th (inclusive)
        // 1January---(current year-1)---1July---(current year)---31December---

        //Residency Start Year (startDates)
        //next year (current year+1) if current date is between July 1st and December 31st (inclusive) or
        //current year if current date is between January 1st and June 30th (inclusive)
        // 1July---(current year+1)---31December---(current year)---30June---

        //set "Application Season Start Year" to current year and "Residency Start Year" to next year if
        // current date is between July 1st and December 31st (inclusive) or
        if( $currentDate >= $july1 && $currentDate <= $december31 ) {
            //$applicationSeasonStartDate = $currentYear;
            //$startDate = $currentYear + 1;
        }

        //set "Application Season Start Year" to previous year and and "Residency Start Year" to current year if
        // current date is between January 1st and June 30th (inclusive)
        if( $currentDate >= $january1 && $currentDate < $july1 ) {
            $currentYear = $currentYear - 1;
            //$startDate = $currentYear;
        }

        //echo "getDefaultAcademicStartYear: currentYear=$currentYear <br>";

        return $currentYear;
    }
    //Get academic year (if 2021 it means 2021-2022 academic year) according to the $stardate and end of year (december 31st)
    public function getAcademicStartYear( $stardate ) {

        //$userSecUtil = $this->container->get('user_security_utility');

        $currentYear = intval(date("Y"));
        //echo "getAcademicStartYear: currentYear=$currentYear <br>"; //testing
        $currentDate = new \DateTime();

        //2011-03-26 (year-month-day)
        $january1 = new \DateTime($currentYear."-01-01");
        //$june30 = new \DateTime($currentYear."-06-30");

        //start date of the academic year
        //$july1 = new \DateTime($currentYear."-07-01"); //get from site setting

        //start date
        //$academicYearStart = $userSecUtil->getSiteSettingParameter($startfieldname,$sitename);
        if( $stardate ) {
            $startDateMD = $stardate->format('m-d');
            $july1 = new \DateTime($currentYear."-".$startDateMD);
        } else {
            //throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
            //assume start date July 1st
            $july1 = new \DateTime($currentYear."-07-01");
        }
        //echo "july1=".$july1->format("d-m-Y")."<br>";

        //end date of the year, always December 31
        $december31 = new \DateTime($currentYear."-12-31");

        //Application Season Start Year (applicationSeasonStartDates) set to:
        //current year if current date is between July 1st and December 31st (inclusive) or
        //previous year (current year-1) if current date is between January 1st and June 30th (inclusive)
        // 1January---(current year-1)---1July---(current year)---31December---

        //Residency Start Year (startDates)
        //next year (current year+1) if current date is between July 1st and December 31st (inclusive) or
        //current year if current date is between January 1st and June 30th (inclusive)
        // 1July---(current year+1)---31December---(current year)---30June---

        //set "Application Season Start Year" to current year and "Residency Start Year" to next year if
        // current date is between July 1st and December 31st (inclusive) or
        if( $currentDate >= $july1 && $currentDate <= $december31 ) {
            //$applicationSeasonStartDate = $currentYear;
            //$startDate = $currentYear + 1;
        }

        //set "Application Season Start Year" to previous year and and "Residency Start Year" to current year if
        // current date is between January 1st and June 30th (inclusive)
        if( $currentDate >= $january1 && $currentDate < $july1 ) {
            $currentYear = $currentYear - 1;
            //$startDate = $currentYear;
        }

        //echo "currentYear=$currentYear <br>";

        return $currentYear;
    }

    public function getAcademicStartEndDayMonth( $formatStr="m-d" )
    {
        $userSecUtil = $this->container->get('user_security_utility');
        //academicYearStart: July 01
        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart');
        if (!$academicYearStart) {
            $academicYearStart = \DateTime::createFromFormat('Y-m-d', date("Y")."-07-01");
            //throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
        }
        //academicYearEnd: June 30
        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd');
        if (!$academicYearEnd) {
            $academicYearEnd = \DateTime::createFromFormat('Y-m-d', date("Y")."-06-30");
            //throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
        }

        $startDayMonth = $academicYearStart->format($formatStr);
        $endDayMonth = $academicYearEnd->format($formatStr);

        return array(
            'startDayMonth'=> $startDayMonth,
            'endDayMonth'=> $endDayMonth,
        );
    }
    
    public function getLinkToListIdByClassName($listName) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:PlatformListManagerRootList'] by [PlatformListManagerRootList::class]
        $listEntity = $this->em->getRepository(PlatformListManagerRootList::class)->findOneByListName($listName);
        if( !$listEntity ) {
            return NULL;
        }

        $linkToListId = $listEntity->getLinkToListId();

        if( !$linkToListId ) {
            return NULL;
        }

        return $linkToListId;

        //platformlistmanager_edit
        $url = $this->container->get('router')->generate(
            'platformlistmanager_edit',
            array(
                'id' => $linkToListId,
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $url;
    }
    public function getSiteParamListUrl( $listName ) {
        //PlatformListManagerRootList find by ListObjectName and get LinkToListID
        $listEntity = $this->em->getRepository(PlatformListManagerRootList::class)->findOneByListName($listName);
        if( !$listEntity ) {
            return NULL;
        }

        $linkToListId = $listEntity->getLinkToListId();

        if( !$linkToListId ) {
            return NULL;
        }

        $url = $this->container->get('router')->generate(
            //'platformlistmanager_edit',
            'platform_list_manager',
            array(
                'listId' => $linkToListId,
            )
            //,
            //UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $url;
    }

    //Used for list Excel generation 
    public function createtListExcelSpout( $repository, $entityClass, $search, $fileName ) {
        //echo "userIds=".count($userIds)."<br>";
        //exit('1');

        $testing = true;
        $testing = false;

        //$author = $this->container->get('security.token_storage')->getToken()->getUser();
        //$transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');

        $newline =  "\n"; //"<br>\n";

        $dql =  $repository->createQueryBuilder("ent");
        $dql->select('ent');

        if( method_exists($entityClass,'getSites') ) {
            $dql->leftJoin("ent.sites", "sites");
            //$dql->addGroupBy('sites.name');
        }

        $searchStr =
            "
                LOWER(ent.name) LIKE LOWER(:search) 
                OR LOWER(ent.abbreviation) LIKE LOWER(:search) 
                OR LOWER(ent.shortname) LIKE LOWER(:search) 
                OR LOWER(ent.description) LIKE LOWER(:search)
            ";

        $dql->andWhere($searchStr);
        $dqlParameters['search'] = '%'.$search.'%';

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters( $dqlParameters );
        }

        $entities = $query->getResult();
        
        $columns = array(
            'ID',
            'Name',
            'Short Name',
            'Abbreviation',
            'Alias',
            'Description',
            //'Site',
            'Type',
            //'Level',
        );

        if( method_exists($entityClass, 'getRoles') ) {
            $columns = array(
                'ID',
                'Name',
                'Short Name',
                'Abbreviation',
                'Alias',
                'Description',
                //'Site',
                'Type',
                //'Level',
            );
        }

        //exit( "Person col=".array_search('Person', $columns) );

        if( $testing == false ) {
            //$writer = WriterFactory::create(Type::XLSX);
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser($fileName);

            $headerStyle = (new StyleBuilder())
                ->setFontBold()
                //->setFontItalic()
                ->setFontSize(12)
                ->setFontColor(Color::BLACK)
                ->setShouldWrapText()
                ->setBackgroundColor(Color::toARGB("E0E0E0"))
                ->build();

            $requestStyle = (new StyleBuilder())
                ->setFontSize(10)
                //->setShouldWrapText()
                ->build();

            $border = (new BorderBuilder())
                ->setBorderBottom(Color::GREEN, Border::WIDTH_THIN, Border::STYLE_DASHED)
                ->build();
            $footerStyle = (new StyleBuilder())
                ->setFontBold()
                //->setFontItalic()
                ->setFontSize(12)
                ->setFontColor(Color::BLACK)
                ->setShouldWrapText()
                ->setBackgroundColor(Color::toARGB("EBF1DE"))
                ->setBorder($border)
                ->build();

            $spoutRow = WriterEntityFactory::createRowFromArray(
                $columns,
                $headerStyle
            );
            $writer->addRow($spoutRow);
        }

        $totalNumberBusinessDays = 0;
        $totalNumberVacationDays = 0;
        $totalNumberPendingVacationDays = 0;
        $totalRequests = 0;
        $totalCarryoverApprovedRequests = 0;
        $totalApprovedFloatingDays = 0;

        $row = 2;
        foreach( $entities as $entity ) {
            $data = array();

            $data[array_search('ID', $columns)] = $entity->getId();
            $data[array_search('Name', $columns)] = $entity->getName();
            $data[array_search('Short Name', $columns)] = $entity->getShortName();
            $data[array_search('Abbreviation', $columns)] = $entity->getAbbreviation();

            //getAlias
            if( $entity instanceof Roles ) {
                $data[array_search('Alias', $columns)] = $entity->getAlias();
            }

            $data[array_search('Description', $columns)] = $entity->getDescription();
            //$data[array_search('Site', $columns)] = $entity->getSite();
            $data[array_search('Type', $columns)] = $entity->getType();
            //$data[array_search('Level', $columns)] = $entity->getLevel();

            if( $testing == false ) {
                //$writer->addRowWithStyle($data,$requestStyle);
                $spoutRow = WriterEntityFactory::createRowFromArray($data, $requestStyle);
                $writer->addRow($spoutRow);
            }
        }//foreach

        if( $testing == false ) {
            //$spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
            //$writer->addRow($spoutRow);

            //set color light green to the last Total row
            //$ews->getStyle('A'.$row.':'.'L'.$row)->applyFromArray($styleLastRow);

            //exit("ids=".$fellappids);

            $writer->close();
        } else {
            print_r($data);
            exit('111');
        }
    }

    /**
     * @return int
     */
    public function getPendingAdminReview(): int
    {
        $pendingCount = 0;

        if( false === $this->security->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $pendingCount;
        }

//        //Test
//        if(0) {
//            $query = $this->em->createQueryBuilder()
//                ->from(User::class, 'user')
//                ->select("user")
//                ->leftJoin("user.infos", "infos")
//                ->where("infos.email = 'cinava@yahoo.com' OR infos.emailCanonical = 'cinava@yahoo.com'")
//                ->orderBy("user.id", "ASC");
//            $pendings = $query->getQuery()->getResult();
//            $pendingCount = count($pendings);
//            echo "pendingCount=$pendingCount<br>";
//            exit("111");
//            //return $pendingCount;
//        }
//        if(0) {
//            //$user = $this->em->getRepository(User::class)->find(1);
//            //exit("111");
//            $repository = $this->em->getRepository(User::class);
//            $dql = $repository->createQueryBuilder('user');
//            $dql->select('user');
//            $dql->where("user.id = 1");
//            //$query = $this->em->createQuery($dql);
//            $query = $dql->getQuery();
//            $pendings = $query->getResult();
//            exit("111");
//            $pendingCount = count($pendings);
//            echo "pendingCount=$pendingCount<br>";
//            exit("111");
//        }
        //$pendings = $this->em->getRepository(User::class)->getPendingAdminReview();
        //$user = $this->em->getRepository(User::class)->find(1);
        //echo "pendings=".count($pendings)."<br>";
        //return count($pendings);
        //$users = $this->em->getRepository(User::class)->findUserByUserInfoEmail('cinava@yahoo.com');
        //echo "users=".count($users)."<br>";
        //return count($users);

        $totalcriteriastr = $this->getPendingReviewCriteria();

        //$em = $this->em; //getDoctrine()->getManager();
        $repository = $this->em->getRepository(User::class);
        $dql = $repository->createQueryBuilder('user');

        $dql->select('COUNT(DISTINCT user.id) as count');
        //$dql->select('user');
        //$dql->select('COUNT(user.id) as count');

        $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
        $dql->leftJoin("user.appointmentTitles", "appointmentTitles");
        $dql->leftJoin("user.medicalTitles", "medicalTitles");
        //$dql->leftJoin("user.locations", "locations");
        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->leftJoin("employmentStatus.employmentType", "employmentType");

        $dql->where($totalcriteriastr);

        //$query = $this->em->createQuery($dql); //Symfony Exception: Doctrine\ORM\Query::getDQL(): Return value must be of type ?string, Doctrine\ORM\QueryBuilder returned
        $query = $dql->getQuery();

        $pendings = $query->getSingleResult();
        //dump($pendings);
        //exit('111');
        return $pendings['count'];

        //$pendings = $query->getResult();
        //$pendingCount = count($pendings);
        //return $pendingCount;
    }
    public function getPendingReviewCriteria() {
        $pendingStatus = BaseUserAttributes::STATUS_UNVERIFIED;
        $criteriastr = "(".
            "administrativeTitles.status = ".$pendingStatus.
            " OR appointmentTitles.status = ".$pendingStatus.
            " OR medicalTitles.status = ".$pendingStatus.
            //" OR locations.status = ".$pendingStatus.
            ")";

        //current_only
        $curdate = date("Y-m-d", time());
        $criteriastr .= " AND (";
        $criteriastr .= "employmentStatus.id IS NULL";
        $criteriastr .= " OR ";
        $criteriastr .= "employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."'";
        $criteriastr .= ")";

        //filter out system user
        $totalcriteriastr = "user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'";

        //filter out Pathology Fellowship Applicants
        $totalcriteriastr = $totalcriteriastr . " AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)";

        //activeAD
        $totalcriteriastr = $totalcriteriastr . " AND (user.activeAD = TRUE AND user.enabled = TRUE)";

        if( $criteriastr ) {
            $totalcriteriastr = $totalcriteriastr . " AND (".$criteriastr.")";
        }

        return $totalcriteriastr;
    }

    /**
     * Working
     *
     * @return int
     */
    public function getPendingAdminReview_WORKING(): int
    {
        //$entityManager = $this->getEntityManager();

        $pendingStatus = BaseUserAttributes::STATUS_UNVERIFIED;

        $pendingStatus = BaseUserAttributes::STATUS_UNVERIFIED;
        $criteriastr = "(".
            "administrativeTitles.status = ".$pendingStatus.
            " OR appointmentTitles.status = ".$pendingStatus.
            " OR medicalTitles.status = ".$pendingStatus.
            //" OR locations.status = ".$pendingStatus.
            ")";

        //current_only
        $curdate = date("Y-m-d", time());
        $criteriastr .= " AND (";
        $criteriastr .= "employmentStatus.id IS NULL";
        $criteriastr .= " OR ";
        $criteriastr .= "employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."'";
        $criteriastr .= ")";

        //filter out system user
        $totalcriteriastr = "u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system'";

        //filter out Pathology Fellowship Applicants
        $totalcriteriastr = $totalcriteriastr . " AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)";

        if( $criteriastr ) {
            $totalcriteriastr = $totalcriteriastr . " AND (".$criteriastr.")";
        }

        //WHERE administrativeTitles.status = '.$pendingStatus.' OR appointmentTitles.status = '.$pendingStatus.' OR medicalTitles.status = '.$pendingStatus.'

        //$dql->leftJoin("user.employmentStatus", "employmentStatus");
        //$dql->leftJoin("employmentStatus.employmentType", "employmentType");

        //FROM AppUserdirectoryBundle:User u
        $query = $this->em->createQuery(
            'SELECT u 
            FROM App\\UserdirectoryBundle\\Entity\\User u
            JOIN u.administrativeTitles administrativeTitles
            JOIN u.appointmentTitles appointmentTitles
            JOIN u.medicalTitles medicalTitles
            JOIN u.employmentStatus employmentStatus
            JOIN employmentStatus.employmentType employmentType
            WHERE '.$totalcriteriastr.'
            ORDER BY u.id ASC'
        );

        $pendings = $query->getResult();

        $pendingCount = count($pendings);

        return $pendingCount;
    }

    public function getEnvironments() {
        return array('live'=>'live', 'test'=>'test', 'dev'=>'dev', 'demo'=>'demo');
    }

    //Similar to Document->getAbsoluteUploadFullPath
    //Document's getAbsoluteUploadFullPath is limited for multitenancy HAproxy when url has 'c/wcm/pathology' prefix:
    //http://view.online/c/wcm/pathology/Uploaded/directory/avatars/avatar/20240708194741.jpeg
    //Here in service, we can get the full url prefix 'http://view.online/c/wcm/pathology' using router
    public function getDocumentAbsoluteUrl( $document, $size=null, $onlyResize=false ) {
        $baseUrl = $this->container->get('router')->generate(
            'main_common_home',
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        ); //https://view.online/c/wcm/pathology/
        //exit('$baseUrl='.$baseUrl);

        $uniquename = $document->getUniquename();
        if( !$uniquename ) {
            return null;
        }

        if ($size) {
            $uniquename = $size . "-" . $uniquename;
        }

        $baseUrl = rtrim($baseUrl,'/'); //trim '/' at the end

        $url = $baseUrl . '/' . $document->getUploadDirectory() . '/' . $uniquename;

        if( $onlyResize == false ) {
            if ($size) {
                $src = $document->getServerPath($size);
                if (file_exists($src)) {
                    //echo "The file $path exists <br>";
                } else {
                    //echo "The file $path does not exists <br>";
                    //exit("The file $path does not exists");
                    $url = $this->getDocumentAbsoluteUrl($document);
                }
            } else {
                //echo "Size is null <br>";
            }
        }

        //exit('$url='.$url);
        return $url;
    }

    public function isDbInitialized( $locale=null ) {
        //return false;//testing
        //echo "isDbInitialized: locale=".$locale."<br>";
        if( !$locale ) {
            //$userSecUtil = $this->container->get('user_security_utility');
            //$param = $userSecUtil->getSingleSiteSettingsParam();
            $param = $this->getSingleSiteSettingParameter();
            if( $param ) {
                $initialConfigurationCompleted = $param->getInitialConfigurationCompleted();
                //echo '$initialConfigurationCompleted='.$initialConfigurationCompleted."<br>";
                if( $initialConfigurationCompleted ) {
                    return true;
                }
            }
            return false;
        }

        //Connect to DB by tenancy name $locale
        $config = new \Doctrine\DBAL\Configuration();
        $config->setSchemaManagerFactory(new \Doctrine\DBAL\Schema\DefaultSchemaManagerFactory());
        //exit('locale='.$locale);
        $connectionParams = $this->getConnectionParams($locale);
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

        $dbname = null;
        if( $conn ) {
            try {
                $dbname = $conn->getDatabase();
            } catch( \Exception $e ) {
                //exit('NO');
                //echo "<br>*** siteparameters.php: Failed to connect to system DB. Use the default DB ***\n\r" . $e->getMessage() . "<br>";
                $conn = null;
            }
        }

        if( $conn ) {
            $table = 'user_siteparameters';
            $schemaManager = $conn->createSchemaManager();
            if( $schemaManager->tablesExist(array($table)) == true ) {
                //SiteParameters entity exists => Do nothing
                //echo "<br>### SiteParameters entity exists => Check 'initialConfigurationCompleted' ### <br>";
                $enableSystem = false;
                $siteparameters = "SELECT * FROM " . $table;
                $hostedGroupHolders = $conn->executeQuery($siteparameters);
                $siteparametersRows = $hostedGroupHolders->fetchAllAssociative(); //fetch();

                if( count($siteparametersRows) > 0 ) {
                    $siteparametersRow = $siteparametersRows[0];
                    if( isset($siteparametersRow['initialconfigurationcompleted']) ) {
                        $initialConfigurationCompleted = $siteparametersRow['initialconfigurationcompleted'];
                        //echo "<br>### initialConfigurationCompleted=$initialConfigurationCompleted ### <br>";
                        if( $initialConfigurationCompleted ) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
    public function getDbNameByLocale( $locale=null ) {
        if( !$locale ) {
            return null;
        }

        $conn = $this->getConnectionByLocale($locale);

        $dbname = null;
        if( $conn ) {
            try {
                $dbname = $conn->getDatabase();
            } catch( \Exception $e ) {
                //exit('NO');
                //echo "<br>*** siteparameters.php: Failed to connect to system DB. Use the default DB ***\n\r" . $e->getMessage() . "<br>";
                $conn = null;
            }
        }
        return $dbname;
    }
    public function getConnectionByLocale( $locale=null ) {
        if( !$locale ) {
            return null;
        }
        //Connect to DB by tenancy name $locale
        $config = new \Doctrine\DBAL\Configuration();
        $config->setSchemaManagerFactory(new \Doctrine\DBAL\Schema\DefaultSchemaManagerFactory());
        //exit('locale='.$locale);
        $connectionParams = $this->getConnectionParams($locale);
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        return $conn;
    }
    public function isConnectionValid( $conn ) {
        if( $conn ) {
            //$dbname = null;
            try {
                $dbname = $conn->getDatabase();
            } catch( \Exception $e ) {
                //exit('NO');
                //echo "<br>*** siteparameters.php: Failed to connect to system DB. Use the default DB ***\n\r" . $e->getMessage() . "<br>";
                $conn = null;
            }
        }
        if( $conn ) {
            return true;
        }
        return false;
    }
    public function isLocalValid( $locale=null ) {
        if( !$locale ) {
            return false;
        }
        $conn = $this->getConnectionByLocale($locale);
        return $this->isConnectionValid($conn);
        
    }
    public function getConnectionParams( $urlSlug ) {
        //echo "urlSlug=$urlSlug <br>";
        //exit('111');
        $params = array();
        $params['driver'] = $this->container->getParameter('database_driver');
        $params['host'] = $this->container->getParameter($urlSlug.'-databaseHost');
        $params['port'] = $this->container->getParameter($urlSlug.'-databasePort');
        $params['dbname'] = $this->container->getParameter($urlSlug.'-databaseName');
        $params['user'] = $this->container->getParameter($urlSlug.'-databaseUser');
        $params['password'] = $this->container->getParameter($urlSlug.'-databasePassword');
        $params['wrapperClass'] = DoctrineMultidatabaseConnection::class;
        //echo "dBName=".$params['dbname']."<br>";
        return $params;
    }

    //replace accented chars (diacritics)
    public function replaceAccentedChars( $str ) {
        $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        $replacedStr = strtr( $str, $unwanted_array );
        if( !$replacedStr ) {
            $replacedStr = $str;
        }
        return $replacedStr;
    }

    public function getInstallationManualUrl() {
        $filename = "InstallationInstructions.pdf";
        $installationManualUrl = "orderassets\\AppUserdirectoryBundle\\form\\docs\\";
        $mailerManualUrl = $installationManualUrl.$filename;
        return $mailerManualUrl;
    }

    //Exact same Last name AND Exact same First name as the profile being shown OR (B) exact same email address as the profile being shown
    public function getSimilarUsers( $user ) {
        //$users = array();

        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
        $email = $user->getSingleEmail();

        $repository = $this->em->getRepository(User::class);
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin('user.infos','infos');

        $dql->where("LOWER(infos.firstName) = LOWER(:firstName) AND LOWER(infos.lastName) = LOWER(:lastName)");

        $dql->orWhere("LOWER(infos.email) = LOWER(:email)");
        
        $queryParameters = array(
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
        );

        $query = $dql->getQuery();
        $query->setParameters( $queryParameters );

        $users = $query->getResult();

        return $users;
    }

//    public function updateSiteSettingParametersAfterRestore( $env, $exceptionUsers, $siteEmail ) {
//        $logger = $this->container->get('logger');
//        $userSecUtil = $this->container->get('user_security_utility');
//
//        $logger->notice("updateSiteSettingParametersAfterRestore: set site settings parameters");
//
//        //restart postgresql server? sudo systemctl restart httpd.service
//        //$command = "systemctl restart httpd.service";
////        $command = "sudo systemctl restart postgresql-14";
////        $logger->notice("command=[".$command."]");
////        $res = $this->runProcess($command);
////        $logger->notice("systemctl restart postgresql-14: res=".$res);
//
//        $projectRoot = $this->container->get('kernel')->getProjectDir();
//        //$projectRoot = C:\Users\ch3\Documents\MyDocs\WCMC\ORDER\order-lab\orderflex
//        $this->runProcess("bash ".$projectRoot.DIRECTORY_SEPARATOR."deploy.sh");
//
//        //$em = $this->getDoctrine()->getManager();
//        //https://stackoverflow.com/questions/42116749/restore-doctrine-connection-after-failed-flush
//        //$em = $this->getDoctrine()->resetManager();
//        //$em = $this->getDoctrine()->getManager();
//
//        $param = $userSecUtil->getSingleSiteSettingsParam();
//        $logger->notice("After get settings parameters. paramId=" . $param->getId());
//
//        if(0) {
//            /////// set original parameters //////////
//            //mailerDeliveryAddresses to admin
//            //environment
//            //liveSiteRootUrl
//            //networkDrivePath
//            //connectionChannel
//            $param->setMailerDeliveryAddresses($mailerDeliveryAddresses);
//            $param->setEnvironment($environment);
//            $param->setLiveSiteRootUrl($liveSiteRootUrl);
//            $param->setNetworkDrivePath($networkDrivePath);
//            $param->setConnectionChannel($connectionChannel);
//            /////// EOF set original parameters //////////
//        }
//
//        /////// set parameters //////////
//        //set environment
//        $param->setEnvironment($env);
//
//        if( $env != 'live' ) {
//            //prevent sending emails to real users for not live environment
//            $param->setMailerDeliveryAddresses($siteEmail);
//        }
//
//        //prevent sending critical emails
//        foreach ($exceptionUsers as $exceptionUser) {
//            $param->addEmailCriticalErrorExceptionUser($exceptionUser);
//        }
//        /////// EOF set parameters //////////
//
//        $logger->notice("After set settings parameters. Before flush");
//        $this->em->flush();
//
//        $logger->notice("updateSiteSettingParametersAfterRestore: After flush");
//    }
    

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
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:PatientLastName'] by [PatientLastName::class]
            $repository = $em->getRepository(PatientLastName::class);
            $dql = $repository->createQueryBuilder("list");
            $dql->select("list.id as id, LEVENSHTEIN(list.field, '".$search."') AS d");
            $dql->orderBy("d","ASC");
            $query = $dql->getQuery(); //$query = $em->createQuery($dql);

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

//            $repository = $this->em->getRepository('AppUserdirectoryBundle:PermissionObjectList');
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