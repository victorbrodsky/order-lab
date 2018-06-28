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

use Doctrine\Common\Collections\ArrayCollection;

use Oleg\UserdirectoryBundle\Entity\Credentials;
use Oleg\UserdirectoryBundle\Entity\GeoLocation;
use Oleg\UserdirectoryBundle\Entity\PerSiteSettings;
use Oleg\UserdirectoryBundle\Entity\Location;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Entity\AdministrativeTitle;
use Oleg\UserdirectoryBundle\Entity\Logger;
use Oleg\UserdirectoryBundle\Entity\UsernameType;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Oleg\UserdirectoryBundle\Security\Util\UserSecurityUtil;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class UserUtil {



    //private $usernamePrefix = 'wcmc-cwid';


    public function setLoginAttempt( $request, $secTokenStorage, $em, $options ) {

        //return;

        $user = null;
        $username = null;
        $roles = null;

        if( !array_key_exists('serverresponse', $options) ) {
            //$options['serverresponse'] = null;
            $options['serverresponse'] = http_response_code();
        }

        //find site object by sitename
        $site = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation($options['sitename']);
        if( !$site ) {
            //throw new NotFoundHttpException('Unable to find SiteList entity by abbreviation='.$options['sitename']);
        }

        $logger = new Logger($site);

        $token = $secTokenStorage->getToken();

        if( $token ) {

            $user = $secTokenStorage->getToken()->getUser();
            $username = $token->getUsername();

            if( $user && is_object($user) ) {
                $roles = $user->getRoles();
            } else {
                $user = null;
            }

            $logger->setUser($user);

        } else {

            $username = $request->get('_username');

            $userDb = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername($username);
            $user = $userDb;

            $logger->setUser($userDb);

        }

        $logger->setRoles($roles);
        $logger->setUsername($username);
        $logger->setIp($request->getClientIp());
        $logger->setWidth($request->get('display_width'));
        $logger->setHeight($request->get('display_height'));
        $logger->setEvent($options['event']);
        $logger->setServerresponse($options['serverresponse']);

        ////////////// browser info //////////////
        //$browser = BrowserInfo::Instance();
        //$name = $browser->getBrowser();
        //$version = $browser->getVersion();
        //$platform = $browser->getPlatform();
        $browser = new Browser();
        $name = $browser->getName();
        $version = $browser->getVersion();

        $os = new Os();
        $platform = $os->getName();

        $browserInfo = $name . " " . $version . " on " . $platform;
        //echo "Your browser: " . $browserInfo . "<br>";
        ////////////// EOF browser info //////////////

        $userAgent = $browserInfo . "; User Agent: " . $_SERVER['HTTP_USER_AGENT'];
        $logger->setUseragent($userAgent);

        //set Event Type
        $eventtype = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName($options['eventtype']);
        $logger->setEventType($eventtype);

        //set eventEntity
        $eventEntity = null;

        if( array_key_exists('eventEntity', $options) && $options['eventEntity'] ) {

            $eventEntity = $options['eventEntity'];

        } elseif( $user && $user instanceof User && $user->getId() ) {

            $eventEntity = $user;
        }

        if( $eventEntity ) {
            //get classname, entity name and id of subject entity
            $class = new \ReflectionClass($eventEntity);
            $className = $class->getShortName();
            $classNamespace = $class->getNamespaceName();

            //set classname, entity name and id of subject entity
            $logger->setEntityNamespace($classNamespace);
            $logger->setEntityName($className);
            $logger->setEntityId($eventEntity->getId());

            //create EventObjectTypeList if not exists
            $userSecUtil = new UserSecurityUtil($em,null,null,null);
            $eventObjectType = $userSecUtil->getObjectByNameTransformer($user,$className,'UserdirectoryBundle','EventObjectTypeList');
            if( $eventObjectType ) {
                $logger->setObjectType($eventObjectType);
            }
        }

        $em->persist($logger);
        $em->flush($logger);
    }

    public function getMaxIdleTime($em) {

        $params = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( !$params ) {
            //new DB does not have SiteParameters object
            return 1800; //30 min
            //throw new \Exception( 'Parameter object is not found' );
        }

        if( count($params) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        $param = $params[0];
        $maxIdleTime = $param->getMaxIdleTime();

        //return time in seconds
        $maxIdleTime = $maxIdleTime * 60;

        return $maxIdleTime;
    }

    public function getMaxIdleTimeAndMaintenance($em, $secAuth, $container) {

        $params = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( !$params ) {
            //new DB does not have SiteParameters object
            $res = array(
                'maxIdleTime' => 1800,
                'maintenance' => false
            );
            return $res; //30 min
            //throw new \Exception( 'Parameter object is not found' );
        }

        if( count($params) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        $param = $params[0];
        $maxIdleTime = $param->getMaxIdleTime();
        $maintenance = $param->getMaintenance();

        //do not use maintenance for admin
        if( $secAuth->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $maintenance = false;
        }

        $debug = in_array( $container->get('kernel')->getEnvironment(), array('test', 'dev') );
        if( $debug ) {
            $maintenance = false;
        }

        //return time in seconds
        $maxIdleTime = $maxIdleTime * 60;

        $res = array(
            'maxIdleTime' => $maxIdleTime,
            'maintenance' => $maintenance
        );

        return $res;
    }

    //return parameter specified by $setting. If the first time login when site parameter does not exist yet, return -1.
    public function getSiteSetting($em,$setting) {

        $params = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

//        if( !$params ) {
//            //throw new \Exception( 'Parameter object is not found' );
//        }

        //echo "params count=".count($params)."<br>";

        if( count($params) == 0 ) {
            return null;
            //return -1;
        }

        if( count($params) > 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
        }

        $param = $params[0];

        if( $setting == null ) {
            return $param;
        }

        $getSettingMethod = "get".$setting;
        $res = $param->$getSettingMethod();

        return $res;
    }

    public function generateUsernameTypes($em,$user=null,$createSystemUser=true) {

        if( $user == null && $createSystemUser ) {
            $user = $this->createSystemUser($em,null,null);
        }

        $entities = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'ldap-user' => 'Active Directory (LDAP)',
            //'wcmc-cwid'=>'WCM CWID',
			'external'=>'External Authentication',
            //'autogenerated'=>'Autogenerated',
            'local-user'=>'Local User'
        );

        $count = 1;
        foreach( $elements as $key=>$value ) {

            $entity = new UsernameType();
            $this->setDefaultList($entity,$count,$user,null);
            $entity->setName( trim($value) );
            $entity->setAbbreviation( trim($key) );

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }
    public function setDefaultList( $entity, $count, $user, $name=null ) {
        $entity->setOrderinlist( $count );
        $entity->setCreator( $user );
        $entity->setCreatedate( new \DateTime() );
        $entity->setType('default');
        if( $name ) {
            $entity->setName( trim($name) );
        }
        return $entity;
    }

    public function createSystemUser( $em, $userkeytype, $default_time_zone ) {

        $userSecUtil = new UserSecurityUtil($em,null,null,null);

        $found_user = $userSecUtil->findSystemUser();

        if( !$found_user ) {

            //echo "creating system user <br>";
            //echo "userkeytype=".$userkeytype."; ID=".$userkeytype->getId()."<br>";

            $adminemail = $this->getSiteSetting($em,'siteEmail');
            if( !$adminemail ) {
                $adminemail = "email@example.com";
            }

            $systemuser = new User();
            $systemuser->setKeytype($userkeytype);
            $systemuser->setPrimaryPublicUserId('system');
            $systemuser->setUsername('system');
            $systemuser->setUsernameCanonical('system');
            $systemuser->setEmail($adminemail);
            $systemuser->setEmailCanonical($adminemail);
            $systemuser->setPassword("");
            $systemuser->setCreatedby('system');
            $systemuser->addRole('ROLE_PLATFORM_DEPUTY_ADMIN');
            $systemuser->getPreferences()->setTimezone($default_time_zone);
            $systemuser->setEnabled(false);
            //$systemuser->setLocked(true); //system is locked, so no one can logged in with this account
            //$systemuser->setExpired(false);
            $em->persist($systemuser);
            $em->flush();

        } else {

            //echo "system user exists ".$found_user."<br>";
            $systemuser = $found_user;

        }

        return $systemuser;
    }

    public function getDefaultUsernameType($em) {
        $userkeytype = null;
        $userkeytypes = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findBy(array(),array('orderinlist' => 'ASC'),1);   //limit result by 1
        //echo "userkeytypes=".$userkeytypes."<br>";
        //print_r($userkeytypes);
        if( $userkeytypes && count($userkeytypes) > 0 ) {
            $userkeytype = $userkeytypes[0];
        }
        return $userkeytype;
    }



    //academic titles, administrative titles, sevices, and divisions: if a object has a non-empty end date that is older than today's date, it is a "past" object.
    //$time: 'current_only' - search only current, 'past_only' - search only past, 'all' - search current and past (no filter)
    public function getCriteriaStrByTime( $dql, $time, $searchField, $inputCriteriastr) {

        //echo "time filter: time=".$time."<br>";

        $criteriastr = "";
        $curdate = date("Y-m-d", time());

        switch( $time ) {
            case "current_only":
                //with an empty or future 'end date'
                //echo "current_only<br>";

                //titles: endDate
//                if( $searchField == null || $searchField == 'administrativeTitles' ) {
//                    $criteriastr .= "(administrativeTitles.endDate IS NULL OR administrativeTitles.endDate > '".$curdate."')";
//                    $criteriastr .= " OR ";
//                }
//                if( $searchField && $searchField == 'appointmentTitles' ) {
//                    $criteriastr .= "(appointmentTitles.endDate IS NULL OR appointmentTitles.endDate > '".$curdate."')";
//                    $criteriastr .= " OR ";
//                }
//                if( $searchField && $searchField == 'medicalTitles' ) {
//                    $criteriastr .= "(medicalTitles.endDate IS NULL OR medicalTitles.endDate > '".$curdate."')";
//                    $criteriastr .= " OR ";
//                }
//
//                //research lab: dissolvedDate
//                if( $searchField == null || $searchField == 'researchLabs' ) {
//                    $criteriastr .= "(researchLabs.dissolvedDate IS NULL OR researchLabs.dissolvedDate > '".$curdate."')";
//                    $criteriastr .= " OR ";
//                }

                //Employment Status should have at least one group where Date of Termination is empty
                if( $searchField == null || $searchField == 'employmentStatus' ) {
                    $criteriastr .= "(";
                    $criteriastr .= "(employmentStatus.id IS NULL)";
                    $criteriastr .= " OR ";
                    //$criteriastr .= "(employmentStatus.terminationDate IS NULL)";
                    //$criteriastr .= " OR ";
                    //$criteriastr .= "(employmentStatus.hireDate IS NOT NULL AND (employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."') )";
                    $criteriastr .= "employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."'";
                    $criteriastr .= ")";
                }

                break;
                
            case "past_only":
                //past or empty or future 'end date'
                //echo "past_only<br>";                
                
                //titles: endDate
//                if( $searchField == null || $searchField == 'administrativeTitles' ) {
//                    $criteriastr .= "(administrativeTitles.endDate IS NOT NULL AND administrativeTitles.endDate < '".$curdate."')";
//                    $criteriastr .= " OR ";
//                }
//                if( $searchField && $searchField == 'appointmentTitles' ) {
//                    $criteriastr .= "(appointmentTitles.endDate IS NOT NULL AND appointmentTitles.endDate < '".$curdate."')";
//                    $criteriastr .= " OR ";
//                }
//                if( $searchField && $searchField == 'medicalTitles' ) {
//                    $criteriastr .= "(medicalTitles.endDate IS NOT NULL AND medicalTitles.endDate < '".$curdate."')";
//                    $criteriastr .= " OR ";
//                }
//
//                //research lab: dissolvedDate
//                if( $searchField == null || $searchField == 'researchLabs' ) {
//                    $criteriastr .= "(researchLabs.dissolvedDate IS NOT NULL AND researchLabs.dissolvedDate < '".$curdate."')";
//                    $criteriastr .= " OR ";
//                }

                //Each group of fields in the employment status should have a non-empty Date of Termination.
                if( $searchField == null || $searchField == 'employmentStatus' ) {
                    //TODO: should the search result display only users with all employment status have a non-empty Date of Termination?
                    $criteriastr .= "(";
                    $criteriastr .= "employmentStatus.id IS NOT NULL";
                    $criteriastr .= " AND ";
                    //$criteriastr .= "(employmentStatus.hireDate IS NOT NULL AND employmentStatus.terminationDate IS NOT NULL AND employmentStatus.terminationDate < '".$curdate."')";
                    $criteriastr .= "(employmentStatus.terminationDate IS NOT NULL AND employmentStatus.terminationDate < '".$curdate."')";
                    $criteriastr .= ")";
                }
                
                break;
            default:
                //do nothing
        }

        if( $inputCriteriastr && $inputCriteriastr != "" ) {
            if( $criteriastr != "" ) {
                $inputCriteriastr = "(" . $inputCriteriastr . ") AND (" . $criteriastr . ")";
            }
        } else {
            $inputCriteriastr = $criteriastr;
        }

        return $inputCriteriastr;
    }


    public function indexLocation( $search, $request, $container, $doctrine ) {

        $repository = $doctrine->getRepository('OlegUserdirectoryBundle:Location');
        $dql =  $repository->createQueryBuilder("location");
        $dql->addSelect('location');
        //$dql->addSelect('COUNT(administrativeTitles) as administrativeTitlesCount');
        //$dql->GroupBy('location');
        $dql->leftJoin("location.user", "locationuser");

        //TODO: show supervisers of this location: get institution => get administrativeTitles joined for institutions => get users and positions
        $dql->leftJoin("location.institution", "institution");
        $dql->leftJoin("institution.administrativeTitles", "administrativeTitles");
        //$dql->leftJoin("administrativeTitles.name", "administrativeTitleName");
        $dql->leftJoin("administrativeTitles.user", "administrativeTitleUser");
        $dql->leftJoin("administrativeTitleUser.infos", "administrativeTitleUserInfos");

        //$dql->leftJoin("location.service", "service");
        //$dql->leftJoin("service.heads", "heads");
        //$dql->leftJoin("heads.infos", "headsinfos");

        $postData = $request->query->all();

        $sort = null;
        if( isset($postData['sort']) ) {
            //check for location sort
            if(
                strpos($postData['sort'],'location.') !== false ||
                //strpos($postData['sort'],'heads.') !== false
                strpos($postData['sort'],'administrativeTitle') !== false
            ) {
                $sort = $postData['sort'];
            }
        }

        if( $sort == null ) {
            $dql->orderBy("location.name","ASC");
        }

        //search
        $criteriastr = "";

        //Show ONLY orphaned locations
        $criteriastr .= "locationuser.id IS NULL";

        switch( $search ) {
            case "Common Locations":
                $criteriastr .= "";
                break;
            case "Pathology Common Locations":
                //filter by Department=Pathology and Laboratory Medicine
                //$dql->leftJoin("location.department", "department");
                //$dql->leftJoin("location.institution", "institution");
                $criteriastr .= " AND ";
                $criteriastr .= "institution.name LIKE '%Pathology%'";
                break;
            case "WCMC & NYP Pathology Common Locations":
                //filter by Institution=Weill Cornell Medical College & NYP and Department=Pathology
                //$dql->leftJoin("location.department", "department");
                //$dql->leftJoin("location.institution", "institution");
                $criteriastr .= " AND ";
                $criteriastr .= "institution.name LIKE '%Pathology%'";
                $criteriastr .= " AND (";
                $criteriastr .= "institution.name LIKE 'Weill Cornell Medical College'";
                $criteriastr .= " OR ";
                $criteriastr .= "institution.name LIKE 'New York Hospital'";
                $criteriastr .= ")";

                break;
            case "WCMC Pathology Common Locations":
                //filter by Institution=Weill Cornell Medical College and Department=Pathology and Laboratory Medicine
                //$dql->leftJoin("location.department", "department");
                //$dql->leftJoin("location.institution", "institution");
                $criteriastr .= " AND ";
                $criteriastr .= "institution.name LIKE 'Pathology and Laboratory Medicine'";
                $criteriastr .= " AND ";
                $criteriastr .= "institution.name LIKE 'Weill Cornell Medical College'";
                break;
            case "NYP Pathology Common Locations":
                //filter by Institution=New York Hospital and Department=Pathology and Laboratory Medicine
                //$dql->leftJoin("location.department", "department");
                //$dql->leftJoin("location.institution", "institution");
                $criteriastr .= " AND ";
                $criteriastr .= "institution.name LIKE 'Pathology'";
                $criteriastr .= " AND ";
                $criteriastr .= "institution.name LIKE 'New York Hospital'";
                break;
            case "WCM Pathology Department Common Location For Phone Directory":
                $dql->leftJoin("location.locationTypes", "locationTypes");
                $criteriastr .= " AND ";
                $criteriastr .= "locationTypes.name = 'WCM Pathology Department Common Location For Phone Directory'";
                break;
            default:
                //search by name
                $criteriastr .= " AND location.name LIKE '%".$search."%'";
        }

        //The "Supervisor" column for the orphaned Location should be the person who belongs to the same "Service" as the orphan location according
        //to their Administrative or Academic Title, and who has the "Head of this Service" checkmarked checked for this service.
        //Since multiple people can check this checkmark for a given service, list all of them, separated by commas.


        $dql->where($criteriastr);

        //pass sorting parameters directly to query; Somehow, knp_paginator stoped correctly create pagination according to sorting parameters
//        if( $sort ) {
//            $dql = $dql . " ORDER BY $postData[sort] $postData[direction]";
//        }

        //echo "Location dql=".$dql."<br>";

        $em = $doctrine->getManager();
        $query = $em->createQuery($dql);    //->setParameter('now', date("Y-m-d", time()));

        $limitFlag = true;
        if( $limitFlag ) {
            $limit = 10;
            $paginator  = $container->get('knp_paginator');
            $pagination = $paginator->paginate(
                $query,
                $request->query->get('page', 1), /*page number*/
                $limit,/*limit per page*/
                array('wrap-queries'=>true)
            );
        } else {
            $pagination = $query->getResult();
        }

        return $pagination;

    }



    public function processResidencySpecialtyTree( $treeholder, $em, $secTokenStorage ) {

        $residencySpecialty = $treeholder->getResidencySpecialty();
        $fellowshipSubspecialty = $treeholder->getFellowshipSubspecialty();
        //echo "fellowshipSubspecialty: name=".$fellowshipSubspecialty->getName().", id=".$fellowshipSubspecialty->getId()."<br>";
        //exit();

        $user = $secTokenStorage->getToken()->getUser();

        //use Institution tree set parent method for residency specialty-subspecialty because it's the same logic
        $fellowshipSubspecialty = $em->getRepository('OlegUserdirectoryBundle:Institution')->checkAndSetParent($user,$treeholder,$residencySpecialty,$fellowshipSubspecialty);

        //set author if not set
        $this->setUpdateInfo($treeholder,$em,$secTokenStorage);

    }

    //re-set node by id
    public function processInstTree( $treeholder, $em, $secTokenStorage, $subjectUser=null ) {

        echo "///////////////title=".$treeholder.", id=".$treeholder->getId()."<br>";

        //reset tree node by id
        $institution = $treeholder->getInstitution();

        if( !$institution ) {
            //set author if not set
            $this->setUpdateInfo($treeholder,$em,$secTokenStorage);
            return;
        }

        //print_r($institution);
        if( $institution ) {
            echo "echo orig=".$institution."<br>";
            echo "echo orig parent=".$institution->getParent()."<br>";
            //$institutionDb = $em->getReference('OlegUserdirectoryBundle:Institution', $institution->getId());
            $institutionDb = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($institution->getId());
            echo "echo id=".$institutionDb->getId()."<br>";
            echo "echo parent=".$institutionDb->getParent()."<br>";

            $treeholder->setInstitution($institutionDb);
        } else {
            echo "institution <br>";
            print_r($institution);
            echo "<br>";
            echo 'Inst does not exists for treeholder id='.$treeholder->getId()."<br>";
        }
        //exit('exit processInstTree');

        if( !$subjectUser ) {
            //set author if not set
            $this->setUpdateInfo($treeholder,$em,$secTokenStorage);
            return;
        }

        //set user positions
        //echo "form <br>";
        //print_r($form);
        //echo "<br>";

        //$form["patient"][0]["procedure"][0]["accession"][0]->getData(); administrativetitles
        //$positiontypes = $form->get('institutionspositiontypes')->getData();
        $positiontypes = $institution->getInstitutionspositiontypes();
        echo "positiontypes <br>";
        print_r($positiontypes);
        echo "<br>";

        if( !$positiontypes ) {
            return;
        }

        foreach( $positiontypes as $institutionspositiontypes ) {
            echo "institutionspositiontypes=".$institutionspositiontypes."<br>";
            //Division-149-2
            $arr = explode("-",$institutionspositiontypes);
            $instId = $arr[1];
            $posId = $arr[2];
            if( $instId && $posId ) {
                $instArr[$instId][] = $posId;
            }
        }

        echo "newPositions <br>";
        print_r($instArr);
        echo "<br>";

        foreach( $instArr as $instId => $newPositions ) {

            $nodeUserPositions = $em->getRepository('OlegUserdirectoryBundle:UserPosition')->findBy(
                array(
                    'user' => $subjectUser->getId(),
                    'institution' => $instId
                )
            );

            if( count($nodeUserPositions) > 1 ) {
                $error = 'Logical Error: More than one UserPosition found for user ' . $subjectUser . ' and institution ID ' . $instId . '. Found ' . count($nodeUserPositions) . ' UserPositions';
                throw new LogicException($error);
            }

            $nodeUserPosition = null;
            if( count($nodeUserPositions) > 0 ) {
                $nodeUserPosition = $nodeUserPositions[0];
            }

            if( !$nodeUserPosition ) {
                //echo 'create new UserPosition<br>';
                $nodeUserPosition = new UserPosition();
                $nodeUserPosition->setUser($subjectUser);
                $instRef = $em->getReference('OlegUserdirectoryBundle:Institution', $instId);
                $nodeUserPosition->setInstitution($instRef);
            }

            $nodeUserPosition->clearPositionTypes();

            foreach( $newPositions as $positionId ) {
                $positionRef = $em->getReference('OlegUserdirectoryBundle:PositionTypeList', $positionId);
                $nodeUserPosition->addPositionType($positionRef);
            }

            $em->persist($nodeUserPosition);
        }

        //remove old userPosition from institution node
        $newIdBreadcrumbs = $institutionDb->getIdBreadcrumbs();

        $originalInstitutionId = $treeholder->getInstitution()->getId();
        $originalInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($originalInstitutionId);
        $originalIdBreadcrumbs = $originalInstitution->getIdBreadcrumbs();

        $this->removeUserPositionFromInstitution($subjectUser->getId(),$originalIdBreadcrumbs,$newIdBreadcrumbs,$em);

        //echo "PRE_SUBMIT set newInst=".$institutionDb."<br>";
        $treeholder->setInstitution($institutionDb);


        //set author if not set
        $this->setUpdateInfo($treeholder,$em,$secTokenStorage);
    }
    public function removeUserPositionFromInstitution( $userid, $originalIdBreadcrumbs, $newIdBreadcrumbs, $em ) {

//        echo "originalIdBreadcrumbs:<br>";
//        print_r($originalIdBreadcrumbs);
//        echo "<br>";
//        echo "newIdBreadcrumbs:<br>";
//        print_r($newIdBreadcrumbs);
//        echo "<br>";

        $mergedArr = array_merge( $originalIdBreadcrumbs, $newIdBreadcrumbs );
        $diffIds = array_diff($mergedArr, $newIdBreadcrumbs);

//        echo "diffIds:<br>";
//        print_r($diffIds);
//        echo "<br>";

        foreach( $diffIds as $instId ) {

            if( !in_array($instId, $newIdBreadcrumbs) ) {
                $this->removeUserPositionFromSingleInstitution($userid,$instId,$em);
            }

        }
    }
    public function removeUserPositionFromSingleInstitution( $userid, $instid, $em ) {

        $originalInstitution = $em->getRepository('OlegUserdirectoryBundle:Institution')->find($instid);

        $originalUserPositions = $em->getRepository('OlegUserdirectoryBundle:UserPosition')->findBy(
            array(
                'user' => $userid,
                'institution' => $instid
            )
        );

        if( !$originalInstitution && (!$originalUserPositions || count($originalUserPositions) == 0) ) {
            return;
        }

        foreach( $originalUserPositions as $originalUserPosition ) {
            //echo "!!!PRE_SUBMIT remove userPosition=".$originalUserPosition." from inst=".$originalInstitution."<br>";
            $originalInstitution->removeUserPosition($originalUserPosition);

            $em->remove($originalUserPosition);
            $em->flush($originalUserPosition);

            $em->persist($originalInstitution);
        }

    }


    public function setUpdateInfo( $entity, $em, $secTokenStorage ) {

        if( !$entity ) {
            return;
        }

        $user = $secTokenStorage->getToken()->getUser();

        $author = $em->getRepository('OlegUserdirectoryBundle:User')->find($user->getId());

        //set author and roles if not set
        if( !$entity->getAuthor() ) {
            $entity->setAuthor($author);
        }

        if( $entity->getId() ) {
            if( $entity->getUpdateAuthor() == null ) {  //update author can be set to any user, not a current user
                $entity->setUpdateAuthor($author);
            }
            $entity->setUpdateAuthorRoles($entity->getUpdateAuthor()->getRoles());
        }
    }

//    //add two default locations: Home and Main Office
//    public function addDefaultLocations($entity,$creator,$em,$container) {
//
//        if( $creator == null ) {
//            $userSecUtil = $container->get('user_security_utility');
//            $creator = $userSecUtil->findSystemUser();
//
//            if( !$creator ) {
//                $creator = $entity;
//            }
//        }
//
//        //echo "creator=".$creator.", id=".$creator->getId()."<br>";
//
//        //Main Office Location
//        $mainLocation = new Location($creator);
//        $mainLocation->setName('Main Office');
//        $mainLocation->setRemovable(false);
//        $mainLocType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Employee Office");
//        $mainLocation->addLocationType($mainLocType);
//        $entity->addLocation($mainLocation);
//
//        //Home Location
//        $homeLocation = new Location($creator);
//        $homeLocation->setName('Home');
//        $homeLocation->setRemovable(false);
//        $homeLocType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Employee Home");
//        $homeLocation->addLocationType($homeLocType);
//        $entity->addLocation($homeLocation);
//
//        return $entity;
//    }


    public function replaceAdminTitleByObject($entity,$creator,$em,$container) {

        if( $creator == null ) {
            $userSecUtil = $container->get('user_security_utility');
            $creator = $userSecUtil->findSystemUser();

            if( !$creator ) {
                $creator = $entity;
            }
        }

        $adminTitle = $entity->getAdministrativeTitles()->first();
        if( $adminTitle ) {
            $adminTitleName = $adminTitle->getName();
        } else {
            $adminTitleName = null;
        }

        if( $adminTitleName == null ) {
            return;
        }

//        $adminTitleNameObject = $em->getRepository('OlegUserdirectoryBundle:AdminTitleList')->findOneByName($adminTitleName);
//
//        if( !$adminTitleNameObject ) {
//
//            //generate admin Title Name
//            $treeTransf = new GenericTreeTransformer($em,$creator);
//            $adminTitleNameObject = $treeTransf->createNewEntity($adminTitleName,"AdminTitleList",$creator);
//
//            $em->persist($adminTitleNameObject);
//        }

        $adminTitleNameObject = $this->getObjectByNameTransformer( $adminTitleName, $creator, "AdminTitleList", $em );

        $adminTitle->setName($adminTitleNameObject);

    }

    //get string to object using transformer
    public function getObjectByNameTransformer( $name, $creator, $className, $em ) {

        if( $name == null || $name == "" ) {
            return null;
        }

        $nameObject = $em->getRepository('OlegUserdirectoryBundle:'.$className)->findOneByName($name);

        if( !$nameObject ) {

            //generate admin Title Name
            $treeTransf = new GenericTreeTransformer($em,$creator,$className);
            $nameObject = $treeTransf->createNewEntity($name,$className,$creator);

            $em->persist($nameObject);
        }

        return $nameObject;
    }


    //clone user according to issue #392
    public function makeUserClone( $suser, $duser ) {

        //Time Zone: America / New York
        $duser->setPreferences( clone $suser->getPreferences() );

        //Administrative Title Type
        foreach( $suser->getAdministrativeTitles() as $object ) {
            $clone = clone $object;
            $duser->addAdministrativeTitle( $clone );
        }

        //Academic Titles
        foreach( $suser->getAppointmentTitles() as $object ) {
            $clone = clone $object;
            $duser->addAppointmentTitle( $clone );
        }

        //Medical Titles
        foreach( $suser->getMedicalTitles() as $object ) {
            $clone = clone $object;
            $duser->addMedicalTitle( $clone );
        }

        //Locations
        //1) remove all locations
        $homeLocations = new ArrayCollection();
        foreach( $duser->getLocations() as $object ) {
            if( $object->hasLocationTypeName("Employee Home") ) {
                $homeLocations->add($object);
            }
            $duser->removeLocation($object);
        }
        //2) add cloned locations
        foreach( $suser->getLocations() as $object ) {
            if( $object->hasLocationTypeName("Employee Home") ) {
                $clone = clone $object;
                $duser->addLocation( $clone );
            }
        }
        //3) set home as the last location
        foreach( $homeLocations as $object ) {
            $duser->addLocation( $object );
        }


        //Medical License: Country, State
        $sMedicalLicense = $suser->getCredentials()->getStateLicense()->first();
        $duser->getCredentials()->getStateLicense()->first()->setCountry($sMedicalLicense->getCountry());
        $duser->getCredentials()->getStateLicense()->first()->setState($sMedicalLicense->getState());

        return $duser;
    }


    //populate user according to issue https://bitbucket.org/weillcornellpathology/scanorder/issues/503/default-values-for-new-users
    public function populateDefaultUserFields( $suser, $duser, $em ) {

        //check match for "Organizational Group for new user's default values in Employee Directory"
        //organizationalGroupDefault (PerSiteSettings) vs institution (OrganizationalGroupDefault)
        $perSiteSettings = $suser->getPerSiteSettings();
        if( !$perSiteSettings ) {
            //exit('no perSiteSettings');
            return $duser;
        }

        $userOrganizationalGroupDefaultInst = $perSiteSettings->getOrganizationalGroupDefault();
        if( !$userOrganizationalGroupDefaultInst ) {
            //exit('no userOrganizationalGroupDefaultInst');
            return $duser;
        }

        $siteParameters = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( count($siteParameters) != 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($siteParameters).'object(s)' );
        }

        $siteParameter = $siteParameters[0];

        $organizationalGroupDefaults = $siteParameter->getOrganizationalGroupDefaults();

        $sourceDefault = null;
        foreach( $organizationalGroupDefaults as $organizationalGroupDefault) {
            if( $organizationalGroupDefault->getInstitution()->getId() == $userOrganizationalGroupDefaultInst->getId() ) {
                $sourceDefault = $organizationalGroupDefault;
                break;
            }
        }

        if( !$sourceDefault ) {
            //echo 'no match <br>';
            return $duser;
        }

        //echo "match inst=".$sourceDefault->getInstitution()."<br>";
        //exit('1');

        if( $sourceDefault->getPrimaryPublicUserIdType() ) {
            $duser->setKeyType($sourceDefault->getPrimaryPublicUserIdType());
        }

        if( $sourceDefault->getEmail() ) {
            $duser->setEmail($sourceDefault->getEmail());
        }

        if( $sourceDefault->getRoles() ) {
            $duser->setRoles($sourceDefault->getRoles());
        }

        if( $sourceDefault->getTimezone() ) {
            $duser->getPreferences()->setTimezone($sourceDefault->getTimezone());
        }

        //check PerSiteSettings
        if( !$duser->getPerSiteSettings() ) {
            $duser->setPerSiteSettings( new PerSiteSettings() );
        }

        if( $sourceDefault->getTooltip() ) {
            $duser->getPerSiteSettings()->setTooltip($sourceDefault->getTooltip());
        }

        foreach( $duser->getPreferences()->getShowToInstitutions() as $showToInstitution ) {
            $duser->getPreferences()->removeShowToInstitution($showToInstitution);
        }
        foreach( $sourceDefault->getShowToInstitutions() as $showToInstitution ) {
            $duser->getPreferences()->addShowToInstitution($showToInstitution);
        }

        if( $sourceDefault->getDefaultInstitution() ) {
            $duser->getPerSiteSettings()->setDefaultInstitution($sourceDefault->getDefaultInstitution());
        }

        //permittedInstitutionalPHIScope
        foreach( $duser->getPerSiteSettings()->getPermittedInstitutionalPHIScope() as $inst ) {
            $duser->getPerSiteSettings()->removePermittedInstitutionalPHIScope($inst);
        }
        foreach( $sourceDefault->getPermittedInstitutionalPHIScope() as $inst ) {
            $duser->getPerSiteSettings()->addPermittedInstitutionalPHIScope($inst);
        }

        //employmentType
        if( $sourceDefault->getEmploymentType() ) {
            $employmentStatus = $duser->getEmploymentStatus()->first();
            $employmentStatus->setEmploymentType($sourceDefault->getEmploymentType());
        }

        //locale
        if( $sourceDefault->getLocale() ) {
            $duser->getPreferences()->setLocale($sourceDefault->getLocale());
        }

        //languages
        foreach( $duser->getPreferences()->getLanguages() as $language ) {
            $duser->getPreferences()->removeLanguage($language);
        }
        foreach( $sourceDefault->getLanguages() as $language ) {
            $duser->getPreferences()->addLanguage($language);
        }

        //administrativeTitleInstitution
        if( $sourceDefault->getAdministrativeTitleInstitution() ) {
            $title = $duser->getAdministrativeTitles()->first();
            $title->setInstitution($sourceDefault->getAdministrativeTitleInstitution());
        }

        //academicTitleInstitution
        if( $sourceDefault->getAcademicTitleInstitution() ) {
            $title = $duser->getAppointmentTitles()->first();
            $title->setInstitution($sourceDefault->getAcademicTitleInstitution());
        }

        //medicalTitleInstitution
        if( $sourceDefault->getMedicalTitleInstitution() ) {
            $title = $duser->getMedicalTitles()->first();
            $title->setInstitution($sourceDefault->getMedicalTitleInstitution());
        }

        //locationTypes
        $mainLocation = $duser->getMainLocation();
        if( $mainLocation ) {
            foreach ($mainLocation->getLocationTypes() as $locationType) {
                $mainLocation->removeLocationType($locationType);
            }
            foreach ($sourceDefault->getLocationTypes() as $locationType) {
                $mainLocation->addLocationType($locationType);
            }

            //locationInstitution
            if( $sourceDefault->getLocationInstitution() ) {
                $mainLocation->setInstitution($sourceDefault->getLocationInstitution());
            }

            $geoLocation = $mainLocation->getGeoLocation();
            if( !$geoLocation ) {
                $geoLocation = new GeoLocation();
                $mainLocation->setGeoLocation( $geoLocation );
            }

            //city
            if( $sourceDefault->getCity() ) {
                $geoLocation->setCity($sourceDefault->getCity());
            }

            //state
            if( $sourceDefault->getState() ) {
                $geoLocation->setState($sourceDefault->getState());
            }

            //zip
            if( $sourceDefault->getZip() ) {
                $geoLocation->setZip($sourceDefault->getZip());
            }

            //country
            if( $sourceDefault->getCountry() ) {
                $geoLocation->setCountry($sourceDefault->getCountry());
            }
        }

        //medicalLicenseCountry and medicalLicenseState
        $credentials = $duser->getCredentials();
        if( ! $credentials ) {
            $credentials = new Credentials();
            $duser->setCredentials($credentials);
        }
        //medicalLicenseCountry
        if( $sourceDefault->getMedicalLicenseCountry() ) {
            $stateLicense = $credentials->getStateLicense()->first();
            $stateLicense->setCountry($sourceDefault->getCountry());
        }
        //medicalLicenseState
        if( $sourceDefault->getMedicalLicenseState() ) {
            $stateLicense = $credentials->getStateLicense()->first();
            $stateLicense->setState($sourceDefault->getMedicalLicenseState());
        }

        return $duser;
    }
    

}