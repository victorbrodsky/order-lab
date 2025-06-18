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



use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\UserdirectoryBundle\Entity\PositionTypeList;
use App\UserdirectoryBundle\Entity\SiteParameters;
use App\UserdirectoryBundle\Entity\UserPosition;
use Doctrine\Common\Collections\ArrayCollection;

use App\UserdirectoryBundle\Entity\Credentials;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\PerSiteSettings;
use App\UserdirectoryBundle\Entity\Location;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Entity\AdministrativeTitle;
use App\UserdirectoryBundle\Entity\Logger;
use App\UserdirectoryBundle\Entity\UsernameType;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\SecurityBundle\Security;


class UserUtil {

    protected $em;
    protected $container;
    protected $security;
    protected $requestStack;
    protected $session;

    public function __construct(
        EntityManagerInterface $em,
        ContainerInterface $container,
        Security $security,
        RequestStack $requestStack,
        Session $session
    ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->session = $session;
    }

    //Should not be used; replaced by getRealScheme
    public function getScheme() {
        return $this->getRealScheme();
//        if( $this->requestStack && $this->requestStack->getCurrentRequest() && $this->requestStack->getCurrentRequest()->getScheme() ) {
//            return $this->requestStack->getCurrentRequest()->getScheme();
//        }
//        return NULL;
    }
    //get scheme: $request->getScheme() will give http if using HaProxy.
    //Therefore, use urlConnectionChannel from SiteSettings
    public function getRealScheme( $request=NULL ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $scheme = $userSecUtil->getSiteSettingParameter('urlConnectionChannel');
        //echo 'settings urlConnectionChannel $scheme='.$scheme.'<br>';
        if( !$scheme ) {
            if( !$request ) {
//                if( $this->requestStack && $this->requestStack->getCurrentRequest() ) {
//                    $request = $this->requestStack->getCurrentRequest();
//                }
                $request = $this->getRequest();
            }
            if( $request && $request->getScheme() ) {
                $scheme = $request->getScheme();
            }
        }
        if( !$scheme ) {
            $scheme = 'http';
        }

        ////// testing //////
        //$scheme = 'https';
        //$this->testSchemeAndHost();
//        $tenant = $this->container->getParameter('tenant_role');
//        echo 'tenant='.$tenant.'<br>';
//        if( !$request ) {
//            $request = $this->getRequest();
//        }
//        $urlTest = $request->getSchemeAndHttpHost(); //with HaProxy give: http://view-test.med.cornell.edu
//        echo 'urlTest='.$urlTest.'<br>';
        //exit('$scheme='.$scheme);
        ////// eof testing //////

        return $scheme;
    }
    public function getRealSchemeAndHttpHost( $request=NULL ) {
        //should give https://view-test.med.cornell.edu
        $baseUrl = NULL;

        if( !$request ) {
            $request = $this->getRequest();
        }
        if( !$request ) {
            return $baseUrl;
        }

        $scheme = $this->getRealScheme($request);
        if( $scheme ) {
            $baseUrl = $scheme . '://' . $request->getHttpHost();
        }
        if( !$baseUrl ) {
//            if( !$request ) {
//                if( $this->requestStack && $this->requestStack->getCurrentRequest() ) {
//                    $request = $this->requestStack->getCurrentRequest();
//                }
//            }
            $baseUrl = $request->getSchemeAndHttpHost();
        }
        return $baseUrl;
    }
    public function getRequest() {
        if( $this->requestStack && $this->requestStack->getCurrentRequest() ) {
            return $this->requestStack->getCurrentRequest();
        }
        return NULL;
    }
    public function testSchemeAndHost( $request=NULL ) {
        if( !$request ) {
            $request = $this->getRequest();
        }
        $userUtil = $this->container->get('user_utility');
        $scheme = $userUtil->getRealScheme($request);
        echo 'scheme='.$scheme.'<br>';
        $urlTest = $request->getSchemeAndHttpHost(); //with HaProxy give: http://view-test.med.cornell.edu
        echo 'urlTest='.$urlTest.'<br>';
        $realUrlTest = $userUtil->getRealSchemeAndHttpHost($request); //with HaProxy should give: https://view-test.med.cornell.edu
        echo 'realUrlTest='.$realUrlTest.'<br>';
    }

    public function getUser() {
        return $this->security->getUser();
    }
    public function getLoggedinUser() {
        return $this->getUser();
    }

    public function isGranted( $roleStr ) {
        return $this->security->isGranted($roleStr);
    }
    public function isLoggedinUserGranted( $roleStr ) {
        return $this->isGranted($roleStr);
    }

    public function getSession() {
        //$logger = $this->container->get('logger');
        //$logger->notice("before getSession");

        $session = $this->session;
        if( $session ) {
            //$logger->notice("UserUtil: session ok");
            return $session;
        }
        //$logger->notice("UserUtil: session NULL");
        return NULL;

//        if( $this->requestStack ) {
//            $logger->notice("before requestStack->getCurrentRequest()->getSession()");
//            $session = $this->requestStack->getCurrentRequest()->getSession();
//            $logger->notice("after requestStack->getCurrentRequest()->getSession()");
//            if( $session ) {
//                $logger->notice("getSession yes!");
//                return $session;
//            }
//        }
//        $logger->notice("getSession exit");
//        return NULL;
    }

//    public function getWorkflowByString($workflowStr)
//    {
//        return $this->container->get($workflowStr);
//    }

    //done
    public function generateUsernameTypes($user=null,$createSystemUser=true) {

        $userSecUtil = $this->container->get('user_security_utility');

        if( $user == null && $createSystemUser ) {
            $user = $this->createSystemUser(null,null);
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
        $entities = $this->em->getRepository(UsernameType::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'local-user'=>'Local User',
            'ldap-user' => 'Active Directory (LDAP)',
            'ldap2-user' => 'Active Directory (LDAP) 2',
            //'ldap-user'=>'WCM CWID',
			'external'=>'External Authentication',
            //'autogenerated'=>'Autogenerated',
            //'local-user'=>'Local User',
            'saml-sso' => 'SAML/SSO'
        );

        $count = 1;
        foreach( $elements as $key=>$value ) {

            $entity = new UsernameType();
            $userSecUtil->setDefaultList($entity,$count,$user,null);
            $entity->setName( trim((string)$value) );
            $entity->setAbbreviation( trim((string)$key) );

            $this->em->persist($entity);
            $this->em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }
//    public function setDefaultList( $entity, $count, $user, $name=null ) {
//        $entity->setOrderinlist( $count );
//        $entity->setCreator( $user );
//        $entity->setCreatedate( new \DateTime() );
//        $entity->setType('default');
//        if( $name ) {
//            $entity->setName( trim((string)$name) );
//        }
//        return $entity;
//    }

    //done
    public function createSystemUser( $userkeytype, $default_time_zone ) {

        $userSecUtil = $this->container->get('user_security_utility');

        $found_user = $userSecUtil->findSystemUser();

        if( !$found_user ) {

            //echo "creating system user <br>";
            //echo "userkeytype=".$userkeytype."; ID=".$userkeytype->getId()."<br>";

            $adminemail = $userSecUtil->getSiteSettingParameter('siteEmail');
            if( !$adminemail ) {
                $adminemail = "adminemail@example.com";
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
            $this->em->persist($systemuser);
            $this->em->flush();

        } else {

            //echo "system user exists ".$found_user."<br>";
            $systemuser = $found_user;

        }

        return $systemuser;
    }

//    public function getDefaultUsernameType() {
//        $userkeytype = null;
//        $userkeytypes = $this->em->getRepository('AppUserdirectoryBundle:UsernameType')->findBy(array(),array('orderinlist' => 'ASC'),1);   //limit result by 1
//        //echo "userkeytypes=".$userkeytypes."<br>";
//        //print_r($userkeytypes);
//        if( $userkeytypes && count($userkeytypes) > 0 ) {
//            $userkeytype = $userkeytypes[0];
//        }
//        return $userkeytype;
//    }


    //done
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

    //done
    public function indexLocation( $search, $request ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Location'] by [Location::class]
        $repository = $this->em->getRepository(Location::class);
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
                strpos((string)$postData['sort'],'location.') !== false ||
                //strpos((string)$postData['sort'],'heads.') !== false
                strpos((string)$postData['sort'],'administrativeTitle') !== false
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
            case "WCM & NYP Pathology Common Locations":
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
            case "WCM Pathology Common Locations":
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
                //$criteriastr .= " AND location.name LIKE '%".$search."%'";
                $criteriastr .= " AND location.name LIKE :search";
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

        //$em = $doctrine->getManager();
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);    //->setParameter('now', date("Y-m-d", time()));

        if( str_contains($criteriastr,':search') ) {
            $query->setParameters(
                array(
                    ':search' => '%'.$search.'%',
                )
            );
        }

        $limitFlag = true;
        if( $limitFlag ) {
            $limit = 10;
            $paginator  = $this->container->get('knp_paginator');
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


    //done
    public function processResidencySpecialtyTree( $treeholder ) {

        $residencySpecialty = $treeholder->getResidencySpecialty();
        $fellowshipSubspecialty = $treeholder->getFellowshipSubspecialty();
        //echo "fellowshipSubspecialty: name=".$fellowshipSubspecialty->getName().", id=".$fellowshipSubspecialty->getId()."<br>";
        //exit();

        $user = $this->security->getUser();

        //use Institution tree set parent method for residency specialty-subspecialty because it's the same logic
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $fellowshipSubspecialty = $this->em->getRepository(Institution::class)->checkAndSetParent($user,$treeholder,$residencySpecialty,$fellowshipSubspecialty);

        //set author if not set
        $userUtil = $this->container->get('user_utility');
        $this->setUpdateInfo($treeholder);

    }

    //done
    //re-set node by id
    public function processInstTree( $treeholder, $subjectUser=null ) {

        echo "///////////////title=".$treeholder.", id=".$treeholder->getId()."<br>";

        //reset tree node by id
        $institution = $treeholder->getInstitution();

        if( !$institution ) {
            //set author if not set
            $this->setUpdateInfo($treeholder);
            return;
        }

        //print_r($institution);
        if( $institution ) {
            echo "echo orig=".$institution."<br>";
            echo "echo orig parent=".$institution->getParent()."<br>";
            //$institutionDb = $this->em->getReference('AppUserdirectoryBundle:Institution', $institution->getId());
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $institutionDb = $this->em->getRepository(Institution::class)->find($institution->getId());
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
            $this->setUpdateInfo($treeholder);
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

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserPosition'] by [UserPosition::class]
            $nodeUserPositions = $this->em->getRepository(UserPosition::class)->findBy(
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
                //$instRef = $this->em->getReference('AppUserdirectoryBundle:Institution', $instId);
                $instRef = $this->em->getReference(Institution::class, $instId);
                $nodeUserPosition->setInstitution($instRef);
            }

            $nodeUserPosition->clearPositionTypes();

            foreach( $newPositions as $positionId ) {
                //$positionRef = $this->em->getReference('AppUserdirectoryBundle:PositionTypeList', $positionId);
                $positionRef = $this->em->getReference(PositionTypeList::class, $positionId);
                $nodeUserPosition->addPositionType($positionRef);
            }

            $this->em->persist($nodeUserPosition);
        }

        //remove old userPosition from institution node
        $newIdBreadcrumbs = $institutionDb->getIdBreadcrumbs();

        $originalInstitutionId = $treeholder->getInstitution()->getId();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $originalInstitution = $this->em->getRepository(Institution::class)->find($originalInstitutionId);
        $originalIdBreadcrumbs = $originalInstitution->getIdBreadcrumbs();

        $this->removeUserPositionFromInstitution($subjectUser->getId(),$originalIdBreadcrumbs,$newIdBreadcrumbs);

        //echo "PRE_SUBMIT set newInst=".$institutionDb."<br>";
        $treeholder->setInstitution($institutionDb);


        //set author if not set
        $this->setUpdateInfo($treeholder);
    }
    //done
    public function removeUserPositionFromInstitution( $userid, $originalIdBreadcrumbs, $newIdBreadcrumbs ) {

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
                $this->removeUserPositionFromSingleInstitution($userid,$instId);
            }

        }
    }
    //done
    public function removeUserPositionFromSingleInstitution( $userid, $instid ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $originalInstitution = $this->em->getRepository(Institution::class)->find($instid);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UserPosition'] by [UserPosition::class]
        $originalUserPositions = $this->em->getRepository(UserPosition::class)->findBy(
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

            $this->em->remove($originalUserPosition);
            //$this->em->flush($originalUserPosition);
            $this->em->flush();

            $this->em->persist($originalInstitution);
        }

    }

    //done
    public function setUpdateInfo( $entity ) {

        if( !$entity ) {
            return;
        }

        $user = $this->security->getUser();

        $author = $this->em->getRepository(User::class)->find($user->getId());

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
//        $mainLocType = $em->getRepository('AppUserdirectoryBundle:LocationTypeList')->findOneByName("Employee Office");
//        $mainLocation->addLocationType($mainLocType);
//        $entity->addLocation($mainLocation);
//
//        //Home Location
//        $homeLocation = new Location($creator);
//        $homeLocation->setName('Home');
//        $homeLocation->setRemovable(false);
//        $homeLocType = $em->getRepository('AppUserdirectoryBundle:LocationTypeList')->findOneByName("Employee Home");
//        $homeLocation->addLocationType($homeLocType);
//        $entity->addLocation($homeLocation);
//
//        return $entity;
//    }


//    public function replaceAdminTitleByObject($entity,$creator,$em,$container) {
//
//        if( $creator == null ) {
//            //$userSecUtil = $container->get('user_security_utility');
//            $userSecUtil = $this->container->get('user_security_utility');
//            $creator = $userSecUtil->findSystemUser();
//
//            if( !$creator ) {
//                $creator = $entity;
//            }
//        }
//
//        $adminTitle = $entity->getAdministrativeTitles()->first();
//        if( $adminTitle ) {
//            $adminTitleName = $adminTitle->getName();
//        } else {
//            $adminTitleName = null;
//        }
//
//        if( $adminTitleName == null ) {
//            return;
//        }
//
//        $adminTitleNameObject = $this->getObjectByNameTransformer( $adminTitleName, $creator, "AdminTitleList", $em );
//
//        $adminTitle->setName($adminTitleNameObject);
//    }

//    //done
//    //get string to object using transformer
//    public function getObjectByNameTransformer( $name, $creator, $className ) {
//
//        if( $name == null || $name == "" ) {
//            return null;
//        }
//
//        $nameObject = $this->em->getRepository('AppUserdirectoryBundle:'.$className)->findOneByName($name);
//
//        if( !$nameObject ) {
//
//            //generate admin Title Name
//            $treeTransf = new GenericTreeTransformer($this->em,$creator,$className);
//            $nameObject = $treeTransf->createNewEntity($name,$className,$creator);
//
//            $this->em->persist($nameObject);
//        }
//
//        return $nameObject;
//    }

    //done
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

    //done
    //populate user according to issue https://bitbucket.org/weillcornellpathology/scanorder/issues/503/default-values-for-new-users
    public function populateDefaultUserFields( $suser, $duser ) {

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

//        $siteParameters = $this->em->getRepository(SiteParameters::class)->findAll();
//        if( count($siteParameters) != 1 ) {
//            throw new \Exception( 'populateDefaultUserFields: Must have only one parameter object. Found '.count($siteParameters).'object(s)' );
//        }
//        $siteParameter = $siteParameters[0];
        $userServiceUtil = $this->container->get('user_service_utility');
        $siteParameter = $userServiceUtil->getSingleSiteSettingParameter();

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

        //Organizational Group for new user's default values in Employee Directory: oleg_userdirectorybundle_user_perSiteSettings_organizationalGroupDefault
        if( $sourceDefault->getInstitution() ) {
            $duser->getPerSiteSettings()->setOrganizationalGroupDefault($sourceDefault->getInstitution());
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

        //employmentInstitution
        if( $sourceDefault->getEmploymentInstitution() ) {
            $employmentStatus = $duser->getEmploymentStatus()->first();
            $employmentStatus->setInstitution($sourceDefault->getEmploymentInstitution());
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
            //echo "Admin title inst=".$title->getInstitution()."<br>";
            $title->setInstitution($sourceDefault->getAdministrativeTitleInstitution());
            //echo "Admin title inst=".$title->getInstitution()."<br>";
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