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
 * Created by PhpStorm.
 * User: DevServer
 * Date: 8/20/15
 * Time: 4:21 PM
 */

namespace App\ResAppBundle\Util;



use App\ResAppBundle\Entity\VisaStatus; //process.py script: replaced namespace by ::class: added use line for classname=VisaStatus


use App\UserdirectoryBundle\Entity\Logger; //process.py script: replaced namespace by ::class: added use line for classname=Logger


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution


use App\UserdirectoryBundle\Entity\EmploymentType; //process.py script: replaced namespace by ::class: added use line for classname=EmploymentType


use App\UserdirectoryBundle\Entity\LocationTypeList; //process.py script: replaced namespace by ::class: added use line for classname=LocationTypeList


use App\UserdirectoryBundle\Entity\TrainingTypeList; //process.py script: replaced namespace by ::class: added use line for classname=TrainingTypeList


use App\UserdirectoryBundle\Entity\SiteList; //process.py script: replaced namespace by ::class: added use line for classname=SiteList


use App\UserdirectoryBundle\Entity\ResidencyTrackList; //process.py script: replaced namespace by ::class: added use line for classname=ResidencyTrackList
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use App\ResAppBundle\Entity\DataFile;
use App\ResAppBundle\Entity\Interview;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Entity\BoardCertification;
use App\UserdirectoryBundle\Entity\Citizenship;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\EmploymentStatus;
use App\UserdirectoryBundle\Entity\Examination;
use App\ResAppBundle\Entity\ResidencyApplication;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\JobTitleList;
use App\UserdirectoryBundle\Entity\Location;
use App\ResAppBundle\Entity\Reference;
use App\UserdirectoryBundle\Entity\Roles;
use App\UserdirectoryBundle\Entity\StateLicense;
use App\UserdirectoryBundle\Entity\Training;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use App\UserdirectoryBundle\Util\EmailUtil;
use App\UserdirectoryBundle\Util\UserUtil;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Symfony\Bundle\SecurityBundle\Security;


class ResAppUtil {

    protected $em;
    protected $container;
    protected $security;

    protected $systemEmail;


    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
    }



    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->security->isGranted('ROLE_RESAPP_COORDINATOR') ) {
            //exit('not granted ROLE_RESAPP_COORDINATOR ???!!!'); //testing
            return null;
        } else {
            //exit('granted ROLE_RESAPP_COORDINATOR !!!'); //testing
        }
        $userSecUtil = $this->container->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->container->getParameter('resapp.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
    }


    //$resSubspecArg: single residencyTrack id or array of residencyTrack ids
    //$year - application Season Start Date (ResidencyApplication->$applicationSeasonStartDate)
    //$year can be multiple dates "2019,2020,2021..."
    public function getResAppByStatusAndYear($status,$resSubspecArg,$year=null,$interviewer=null) {

        $userServiceUtil = $this->container->get('user_service_utility');
        $resappUtil = $this->container->get('resapp_util');

        //echo "year=$year<br>";
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResidencyApplication'] by [ResidencyApplication::class]
        $repository = $this->em->getRepository(ResidencyApplication::class);
        $dql =  $repository->createQueryBuilder("resapp");
        $dql->select('resapp');
        $dql->leftJoin("resapp.appStatus", "appStatus");

        if( $status ) {
            if (strpos((string)$status, "-") !== false) {
                $statusArr = explode("-", $status);
                $statusStr = $statusArr[0];
                $statusNot = $statusArr[1];
                if ($statusNot && $statusNot == 'not') {
                    //'interviewee-not' is dummy status which is all statuses but not
                    $dql->where("appStatus.name != '" . $statusStr . "'");
                }
            } else {
                $dql->where("appStatus.name = '" . $status . "'");
            }
        }

        if( $resSubspecArg ) {
            $dql->leftJoin("resapp.residencyTrack","residencyTrack");
            if( is_array($resSubspecArg) ) {
                $restypeArr = array();
                foreach( $resSubspecArg as $residencyTypeID => $residencyTypeName ) {
                    $restypeArr[] = "residencyTrack.id = ".$residencyTypeID;
                }
                $dql->andWhere( implode(" OR ", $restypeArr) );
            } else {
                $dql->andWhere("residencyTrack.id=".$resSubspecArg);
            }
        }

        //application Season Start Year ($applicationSeasonStartDate)
        if( $year ) {
            //echo "year=$year<br>";
            if( strpos((string)$year, "," ) !== false) {
                //multiple years
                $yearArr = explode(",",$year);
                $criterions = array();
                foreach($yearArr as $singleYear) {
                    //$bottomDate = $singleYear."-01-01";
                    //$topDate = $singleYear."-12-31";
                    //$startEndDates = $userServiceUtil->getAcademicYearStartEndDates($singleYear);
                    //$startEndDates = $resappUtil->getAcademicYearStartEndDates($singleYear);
                    //$bottomDate = $startEndDates['startDate'];
                    //$topDate = $startEndDates['endDate'];
                    ////startDate - Residency Start Year
                    //$criterions[] = "("."resapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'".")";

                    $startEndDates = $resappUtil->getResAppAcademicYearStartEndDates($singleYear);
                    $bottomDate = $startEndDates['Season Start Date'];
                    $topDate = $startEndDates['Season End Date'];
                    //echo "bottomDate=$bottomDate, topDate=$topDate <br>";
                    ////applicationSeasonStartDate - Application Season Start Year
                    $criterions[] = "("."resapp.applicationSeasonStartDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'".")";
                }
                $criterionStr = implode(" OR ",$criterions);
                $dql->andWhere($criterionStr);
            } else {
                //single year
                //$bottomDate = $year."-01-01";
                //$topDate = $year."-12-31";
                //$startEndDates = $resappUtil->getAcademicYearStartEndDates($year);
                //$bottomDate = $startEndDates['startDate'];
                //$topDate = $startEndDates['endDate'];
                //echo "get AcademicYearStartEndDates: single year: bottomDate=$bottomDate, topDate=$topDate <br>";
                ////startDate - Residency Start Year
                //$dql->andWhere("resapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'");

                $startEndDates = $resappUtil->getResAppAcademicYearStartEndDates($year);
                $bottomDate = $startEndDates['Season Start Date'];
                $topDate = $startEndDates['Season End Date'];
                //echo "get ResAppAcademicYearStartEndDates: single year: bottomDate=$bottomDate, topDate=$topDate <br>";
                ////applicationSeasonStartDate - Application Season Start Year
                $dql->andWhere("resapp.applicationSeasonStartDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'");
            }
        }

        if( $interviewer ) {
            $dql->leftJoin("resapp.interviews", "interviews");
            $dql->leftJoin("interviews.interviewer", "interviewer");
            $dql->andWhere("interviewer.id=".$interviewer->getId());
        }

        $dql->orderBy("resapp.id","ASC");
        
        //echo "dql=".$dql."<br>";

        $query = $dql->getQuery();
        $applicants = $query->getResult();
        
//        echo "applicants=".count($applicants)."<br>";
//        if( $status == 'active' ) {
//            foreach ($applicants as $resapp) {
//                echo "ID " . $resapp->getId() .
//                    "; startDate=" . $resapp->getStartDate()->format('Y-m-d') .
//                    "; status=" . $resapp->getAppStatus()->getName() .
//                    "; type=" . $resapp->getResidencyTrack()->getName() .
//                    "<br>";
//            }
//        }

        return $applicants;
    }

//    public function getResAppByUserAndStatusAndYear($subjectUser, $status,$resSubspecId,$year=null) {
//
//        $repository = $this->em->getRepository('AppResAppBundle:ResidencyApplication');
//        $dql =  $repository->createQueryBuilder("resapp");
//        $dql->select('resapp');
//        $dql->leftJoin("resapp.appStatus", "appStatus");
//        $dql->where("appStatus.name = '" . $status . "'");
//
//        if( $resSubspecId ) {
//            $dql->leftJoin("resapp.residencyTrack","residencyTrack");
//            $dql->andWhere("residencyTrack.id=".$resSubspecId);
//        }
//
//        if( $year ) {
//            $bottomDate = "01-01-".$year;
//            $topDate = "12-31-".$year;
//            $dql->andWhere("resapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'" );
//        }
//
//        if( $subjectUser ) {
//            $dql->leftJoin("resapp.interviews", "interviews");
//            $dql->andWhere("interviews.interviewer=".$subjectUser);
//        }
//
//        $query = $this->em->createQuery($dql);
//        $applicants = $query->getResult();
//
//        return $applicants;
//    }

    //get residency types based on the user roles
    public function getResidencyTypesByUser( $user ) {
        $em = $this->em;
        $userSecUtil = $this->container->get('user_security_utility');

        if( $userSecUtil->hasGlobalUserRole( "ROLE_RESAPP_ADMIN", $user ) ) {
            return $this->getResidencyTypesByInstitution(false);
        }

        $filterTypes = array();
        //$filterTypeIds = array();

        foreach( $user->getRoles() as $rolename ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
            $roleObject = $em->getRepository(Roles::class)->findOneByName($rolename);
            if( $roleObject ) {
                $residencyTrack = $roleObject->getResidencyTrack();
                if( $residencyTrack ) {
                    $filterTypes[$residencyTrack->getId()] = $residencyTrack->getName();
                    //$filterTypeIds[] = $residencyTrack->getId();
                }
            }
        }

//        if( count($filterTypes) > 1 ) {
//            $filterTypes[implode(";",$filterTypeIds)] = "ALL";
//        }

        //$filterTypes = array_reverse($filterTypes);

        return $filterTypes;
    }

    //get all residency application types (with WCMC Pathology) using role
    //NOT USED. Replaced by getResidencyTypes
    public function getResidencyTypesByInstitutionSimple( $asEntities=false ) {
      
        $em = $this->em;

        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution',
            'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
            'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
        );

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
        //exit("wcm=".$wcmc);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $pathology = $em->getRepository(Institution::class)->findByChildnameAndParent(
            "Pathology and Laboratory Medicine",
            $wcmc,
            $mapper
        );

        //get list of residency type with extra "ALL"
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
        $repository = $em->getRepository(ResidencyTrackList::class);
        $dql = $repository->createQueryBuilder('list');
        $dql->leftJoin("list.institution","institution");
        $dql->where("institution.id = ".$pathology->getId());
        $dql->orderBy("list.orderinlist","ASC");

        $query = $dql->getQuery();

        $resTypes = $query->getResult();
        //echo "resTypes count=".count($resTypes)."<br>";

        if( $asEntities ) {
            return $resTypes;
        }

        //add statuses
        $filterType = array();
        foreach( $resTypes as $type ) {
            //echo "type: id=".$type->getId().", name=".$type->getName()."<br>";
            $filterType[$type->getId()] = $type->getName();
        }

        return $filterType;
    }
    //get all residency application types.
    //We use all ResidencyTrackList entries, therefore Institution is optional.
    public function getResidencyTypesByInstitution( $asEntities=false, $withInst=false ) {

        if( $withInst ) {
            $mapper = array(
                'prefix' => 'App',
                'bundleName' => 'UserdirectoryBundle',
                'className' => 'Institution',
                'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
                'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
            );

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $wcmc = $this->em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
            //exit("wcm=".$wcmc);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $pathology = $this->em->getRepository(Institution::class)->findByChildnameAndParent(
                "Pathology and Laboratory Medicine",
                $wcmc,
                $mapper
            );
        }

        //get list of residency type with extra "ALL"
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
        $repository = $this->em->getRepository(ResidencyTrackList::class);
        $dql = $repository->createQueryBuilder('list');
        $dql->where("list.type = :typedef OR list.type = :typeadd");

        if( $withInst ) {
            $dql->leftJoin("list.institution", "institution");
            $dql->where("institution.id = " . $pathology->getId());
        }

        $dql->orderBy("list.orderinlist","ASC");

        $query = $dql->getQuery();

        $query->setParameters(
            array(
                'typedef' => 'default',
                'typeadd' => 'user-added',
            )
        );

        $resTypes = $query->getResult();
        //echo "resTypes count=".count($resTypes)."<br>";

        if( $asEntities ) {
            return $resTypes;
        }

        //add statuses
        $filterType = array();
        foreach( $resTypes as $type ) {
            //echo "type: id=".$type->getId().", name=".$type->getName()."<br>";
            $filterType[$type->getId()] = $type->getName();
        }

        return $filterType;
    }

    //get all residency visa status
    public function getResidencyVisaStatuses( $asEntities=false, $idName = true ) {
        $em = $this->em;

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:VisaStatus'] by [VisaStatus::class]
        $repository = $em->getRepository(VisaStatus::class);
        $dql = $repository->createQueryBuilder('list');

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->orderBy("list.orderinlist","ASC");

        $query = $dql->getQuery();

        $query->setParameters( array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
        ));

        $resTypes = $query->getResult();
        //echo "resTypes count=".count($resTypes)."<br>";

        if( $asEntities ) {
            return $resTypes;
        }

        $filterTypeArr = array();

        //add statuses
        foreach( $resTypes as $type ) {
            //echo "type: id=".$type->getId().", name=".$type->getName()."<br>";
            //$filterType[$type->getId()] = $type->getName();
            if( $idName ) {
                $filterTypeArr[$type->getId()] = $type->getName();
            } else {
                $filterTypeArr[$type->getName()] = $type->getName();
            }
        }

        return $filterTypeArr;
    }

//    public function getResidencyTypesWithSpecials_OLD() {
//        $em = $this->em;
//
//        //get list of residency type with extra "ALL"
//        $repository = $em->getRepository('AppUserdirectoryBundle:ResidencySpecialty');
//        $dql = $repository->createQueryBuilder('list');
//        //$dql->select("list.id as id, list.name as text")
//        $dql->leftJoin("list.parent","parent");
//        $dql->where("list.type = :typedef OR list.type = :typeadd");
//        $dql->andWhere("parent.name LIKE '%Pathology%' OR parent.name LIKE '%Clinical Molecular Genetics%' OR parent IS NULL");
//        //$dql->andWhere("parent.name LIKE '%Pathology%'");
//        $dql->orderBy("list.orderinlist","ASC");
//
//        $query = $em->createQuery($dql);
//
//        $query->setParameters( array(
//            'typedef' => 'default',
//            'typeadd' => 'user-added',
//            //'parentName' => 'Pathology'
//        ));
//
//        $resTypes = $query->getResult();
//
//        //add special cases
////        $specials = array(
////            "ALL" => "ALL",
////        );
//
////        $filterType = array();
////        foreach( $specials as $key => $value ) {
////            $filterType[$key] = $value;
////        }
//
//        //add statuses
//        foreach( $resTypes as $type ) {
//            //echo "type: id=".$status->getId().", name=".$status->getName()."<br>";
//            $filterType[$type->getId()] = $type->getName();
//        }
//
//        return $filterType;
//    }


    //check if the user can view this resapp application: user is Observers/Interviewers or hasSameResidencyTypeId
    public function hasResappPermission( $user, $resapp ) {

        //$res = false;

        $userSecUtil = $this->container->get('user_security_utility');
        if( $userSecUtil->hasGlobalUserRole( "ROLE_RESAPP_ADMIN", $user ) ) {
            return true;
        }

        //if user is observer of this resapp
        if( $resapp->getObservers()->contains($user) ) {
            return true;
        }

        //if user is interviewer of this resapp
        //if( $resapp->getInterviews()->contains($user) ) {
        if( $resapp->getInterviewByUser($user) ) {
            return true;
        }

        //echo "res=".$res."<br>";

        //if user has the same resapp type as this resapp
        if( $resapp->getResidencyTrack() && $this->hasSameResidencyTypeId($user, $resapp->getResidencyTrack()->getId()) ) {
            return true;
        }

        //echo "res=".$res."<br>";
        //exit('1');

        return false;
    }

    //check residency types based on the user roles
    public function hasSameResidencyTypeId( $user, $restypeid ) {
        $em = $this->em;
        $userSecUtil = $this->container->get('user_security_utility');

        if( $userSecUtil->hasGlobalUserRole( "ROLE_RESAPP_ADMIN", $user ) ) {
            return true;
        }

        //echo "restypeid=".$restypeid."<br>";

        $userRoles = $user->getRoles();
        //$userRoles = $this->findUserRolesBySiteAndPartialRoleName($user, 'resapp', 'ROLE_RESAPP_');

        foreach( $userRoles as $rolename ) {

            if (strpos((string)$rolename, 'ROLE_RESAPP_') === false) {
                //Skip other, not related to resapp roles
                continue;
            }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
            $roleObject = $em->getRepository(Roles::class)->findOneByName($rolename);
            //echo "roleObject=".$roleObject."<br>";
            if( $roleObject ) {
                $residencyTrack = $roleObject->getResidencyTrack();
                //echo "roleObject ResidencyTrack=".$residencyTrack.", ID=".$residencyTrack->getId()."<br>";
                if( $residencyTrack ) {
                    if( $restypeid == $residencyTrack->getId() ) {
                        return true;
                        //Residency track does not required institution
                        //it is safer to check also for residencyTrack's institution is under roleObject's institution
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                        if( $em->getRepository(Institution::class)->isNodeUnderParentnode( $roleObject->getInstitution(), $residencyTrack->getInstitution() ) ) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    //get based on roles
    public function getCoordinatorsOfResAppEmails($resapp) {
        return $this->getEmailsOfResApp( $resapp, "_COORDINATOR_" );
    }
    //get based on roles
    public function getDirectorsOfResAppEmails($resapp) {
        return $this->getEmailsOfResApp( $resapp, "_DIRECTOR_" );
    }
    //get based on roles
    public function getCoordinatorsOfResApp( $resapp ) {
        return $this->getUsersOfResAppByRole( $resapp, "_COORDINATOR_" );
    }
    //get based on roles
    public function getDirectorsOfResApp( $resapp ) {
        return $this->getUsersOfResAppByRole( $resapp, "_DIRECTOR_" );
    }

    //get coordinator of given resapp
    public function getUsersOfResAppByRole( $resapp, $roleName ) {

        if( !$resapp ) {
            return null;
        }

        //$em = $this->em;

        $residencyTrack = $resapp->getResidencyTrack();
        //echo "residencyTrack=".$residencyTrack."<br>";

        if( !$residencyTrack ) {
            return null;
        }

        return $this->getUsersOfResidencyTrackByRole($residencyTrack,$roleName);

//        $coordinatorResTypeRole = null;
//
//        $roles = $em->getRepository('AppUserdirectoryBundle:Roles')->findByResidencyTrack($residencyTrack);
//        foreach( $roles as $role ) {
//            if( strpos((string)$role,$roleName) !== false ) {
//                $coordinatorResTypeRole = $role;
//                break;
//            }
//        }
//
//        $users = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($coordinatorResTypeRole);
//
//        return $users;
    }
    public function getUsersOfResidencyTrackByRole( $residencyTrack, $roleName ) {

        if( !$residencyTrack ) {
            return null;
        }

//        $coordinatorResTypeRole = null;
//        $roles = $this->em->getRepository('AppUserdirectoryBundle:Roles')->findByResidencyTrack($residencyTrack);
//        foreach( $roles as $role ) {
//            if( strpos((string)$role,$roleName) !== false ) {
//                $coordinatorResTypeRole = $role;
//                break;
//            }
//        }
        $coordinatorResTypeRole = $this->getRoleByResidencyTrackAndRolename($residencyTrack,$roleName );

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $users = $this->em->getRepository(User::class)->findUserByRole($coordinatorResTypeRole);

        return $users;
    }
    public function getRoleByResidencyTrackAndRolename( $residencyTrack, $roleName ) {
        //$roles = $this->em->getRepository('AppUserdirectoryBundle:Roles')->findByResidencyTrack($residencyTrack);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $roles = $this->em->getRepository(Roles::class)->findByResidencyTrack($residencyTrack);
        foreach( $roles as $role ) {
            if( strpos((string)$role,$roleName) !== false ) {
                return $role;
                break;
            }
        }

        return null;
    }

    public function getEmailsOfResApp( $resapp, $roleName ) {

        $users = $this->getUsersOfResAppByRole( $resapp, $roleName );

        $emails = array();
        if( $users && count($users) > 0 ) {
            foreach( $users as $user ) {
                $emails[] = $user->getEmail();
            }
        }

        //echo "coordinator emails<br>";
        //print_r($emails);
        //exit('1');

        return $emails;
    }

    //send confirmation email to the corresponding Residency director and coordinator
    public function sendConfirmationEmailsOnApplicationPopulation( $residencyApplication, $applicant ) {
        $resappUtil = $this->container->get('resapp_util');
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        $directorEmails = $resappUtil->getDirectorsOfResAppEmails($residencyApplication);
        $coordinatorEmails = $resappUtil->getCoordinatorsOfResAppEmails($residencyApplication);
        $responsibleEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));
        $logger->notice("Send confirmation email (residency application ".$residencyApplication->getId()." populated in DB) to the directors and coordinators emails " . implode(", ",$responsibleEmails));

        //[ResidencyType Residency] FirstNameOfApplicant LastNameOfApplicant's application received
        $populatedSubjectResApp = "[".$residencyApplication->getResidencyTrack()." Residency] ".$applicant->getUsernameShortest()."'s application received";

        /////////////// Configuring the Request Context per Command ///////////////
        // http://symfony.com/doc/current/cookbook/console/request_context.html
        //replace by $router = $userSecUtil->getRequestContextRouter();
        $liveSiteRootUrl = $userSecUtil->getSiteSettingParameter('liveSiteRootUrl');    //http://c.med.cornell.edu/
        $liveSiteHost = parse_url($liveSiteRootUrl, PHP_URL_HOST); //c.med.cornell.edu
        //echo "liveSiteHost=".$liveSiteHost."; ";

        $connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
        if( !$connectionChannel ) {
            $connectionChannel = 'http';
        }

        $context = $this->container->get('router')->getContext();
        $context->setHost($liveSiteHost);
        $context->setScheme($connectionChannel);
        //$context->setBaseUrl('/order');
        /////////////// EOF Configuring the Request Context per Command ///////////////

        //FirstNameOfApplicant LastNameOfApplicant has submitted a new application to your ResidencyType StartDate'sYear(suchAs2018) residency
        // on SubmissionDate and you can access it here: LinkToGeneratedApplicantPDF.
        //To mark this application as priority, please click the following link and log in if prompted:
        //LinkToChangeStatusOfApplicationToPriority
        $linkToGeneratedApplicantPDF = $this->container->get('router')->generate(
            'resapp_view_pdf',
            array(
                'id' => $residencyApplication->getId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $linkToGeneratedApplicantPDF = $this->convertToHref($linkToGeneratedApplicantPDF);
        //echo "linkToGeneratedApplicantPDF=".$linkToGeneratedApplicantPDF."; ";

        $linkToChangeStatusOfApplicationToPriority = $this->container->get('router')->generate(
            'resapp_status_email',
            array(
                'id' => $residencyApplication->getId(),
                'status' => 'priority'
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $linkToChangeStatusOfApplicationToPriority = $this->convertToHref($linkToChangeStatusOfApplicationToPriority);

        $linkToList = $this->container->get('router')->generate(
            'resapp_home',
            array(
                'filter[startDate]' => $residencyApplication->getStartDate()->format('Y'), //2018
                'filter[filter]' => $residencyApplication->getResidencyTrack()->getId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $linkToList = $this->convertToHref($linkToList);

        $creationDate = $residencyApplication->getCreatedate();
        $creationDate->setTimezone(new \DateTimeZone('America/New_York'));
        $creationDateStr = $creationDate->format('m/d/Y h:i A T');

        //$break = "\r\n";
        $break = "<br>";
        $populatedBodyResApp = $applicant->getUsernameShortest()." has submitted a new application to your ".$residencyApplication->getResidencyTrack().
            " ".$residencyApplication->getStartDate()->format('Y')."'s residency on ".$creationDateStr.
            " and you can access it here: ".$break.$linkToGeneratedApplicantPDF;
        $populatedBodyResApp .= $break.$break."To mark this application as priority, please click the following link and log in if prompted:".
            $break.$linkToChangeStatusOfApplicationToPriority;

        //To view the list of all received ResidencyType ResidencyYear applications, please follow this link:
        $populatedBodyResApp .= $break.$break."To view the list of all received ".
            $residencyApplication->getResidencyTrack()." ".$residencyApplication->getStartDate()->format('Y')." applications, please follow this link:".$break;
        $populatedBodyResApp .= $linkToList;

        //If you are off site, please connect via VPN first ( https://its.weill.cornell.edu/services/wifi-networks/vpn ) and then follow the links above.
        $remoteAccessUrl = $userSecUtil->getSiteSettingParameter('remoteAccessUrl');
        if( $remoteAccessUrl ) {
            $remoteAccessUrl = "(".$remoteAccessUrl.")";
        }
        $populatedBodyResApp .= $break.$break."If you are off site, please connect via VPN first $remoteAccessUrl and then follow the links above.";

        $emailUtil = $this->container->get('user_mailer_utility');
        $emailUtil->sendEmail( $responsibleEmails, $populatedSubjectResApp, $populatedBodyResApp );
    }

    public function convertToHref($url) {
        return '<a href="'.$url.'">'.$url.'</a>';
    }
    
    //add based on interviewers in ResidencyTrack object
    //TODO: rewrite and test add default interviewers based on roles and discard interviewers, coordinator, directors in ResidencyTrack object?
    public function addDefaultInterviewers( $resapp ) {

        $residencyTrack = $resapp->getResidencyTrack();

        foreach( $residencyTrack->getInterviewers() as $interviewer ) {

            if( $this->isInterviewerExist($resapp,$interviewer) == false ) {
                $interview = new Interview();
                $interview->setInterviewer($interviewer);
                $interview->setInterviewDate($resapp->getInterviewDate());
                //don't populate/show heavy locations (25 secs (with locations) vs 12 secs (no locations) for 48 interviews)
                //$interview->setLocation($interviewer->getMainLocation());
                $resapp->addInterview($interview);
            }

        }

    }

    public function isInterviewerExist( $resapp, $interviewer ) {
        foreach( $resapp->getInterviews() as $interview ) {
            if( $interview->getInterviewer()->getId() == $interviewer->getId() ) {
                return true;
            }
        }
        return false;
    }

    


    public function addEmptyResAppFields($residencyApplication) {
        //echo "add EmptyResAppFields <br>";
        $em = $this->em;
        $user = $residencyApplication->getUser();
        $author = $this->security->getUser();

        //Pathology Residency Applicant in EmploymentStatus
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentType'] by [EmploymentType::class]
        $employmentType = $em->getRepository(EmploymentType::class)->findOneByName("Pathology Residency Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Residency Applicant");
        }
        if( count($user->getEmploymentStatus()) == 0 ) {
            $employmentStatus = new EmploymentStatus($author);
            $employmentStatus->setEmploymentType($employmentType);
            $user->addEmploymentStatus($employmentStatus);
        }

        //citizenships
        $this->addEmptyCitizenships($residencyApplication);
        
        //Education
        $this->addEmptyTrainings($residencyApplication);

        //National Boards (examination): oleg_resappbundle_residencyapplication_examinations_0_USMLEStep1DatePassed
        $this->addEmptyNationalBoards($residencyApplication);

        //Medical Licensure: oleg_resappbundle_residencyapplication[stateLicenses][0][licenseNumber]
        //$this->addEmptyStateLicenses($residencyApplication);

        //Board Certification
        //$this->addEmptyBoardCertifications($residencyApplication);

        //References
        //$this->addEmptyReferences($residencyApplication);

    }
    //Not Used. Remnant from fellowship
    public function addAllEmptyResAppFields($residencyApplication) {
        //echo "add AllEmptyResAppFields <br>";
        $em = $this->em;
        //$userSecUtil = $this->container->get('user_security_utility');
        //$systemUser = $userSecUtil->findSystemUser();
        $user = $residencyApplication->getUser();
        $author = $this->security->getUser();

        //Pathology Residency Applicant in EmploymentStatus
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentType'] by [EmploymentType::class]
        $employmentType = $em->getRepository(EmploymentType::class)->findOneByName("Pathology Residency Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Residency Applicant");
        }
        if( count($user->getEmploymentStatus()) == 0 ) {
            $employmentStatus = new EmploymentStatus($author);
            $employmentStatus->setEmploymentType($employmentType);
            $user->addEmploymentStatus($employmentStatus);
        }

        //citizenships
        $this->addEmptyCitizenships($residencyApplication);

        //locations
        $this->addEmptyLocations($residencyApplication);

        //Education
        $this->addEmptyTrainings($residencyApplication);

        //National Boards: oleg_resappbundle_residencyapplication_examinations_0_USMLEStep1DatePassed
        $this->addEmptyNationalBoards($residencyApplication);

        //Medical Licensure: oleg_resappbundle_residencyapplication[stateLicenses][0][licenseNumber]
        $this->addEmptyStateLicenses($residencyApplication);

        //Board Certification
        $this->addEmptyBoardCertifications($residencyApplication);

        //References
        $this->addEmptyReferences($residencyApplication);

    }

    //app_resappbundle_residencyapplication_references_0_name
    public function addEmptyReferences($residencyApplication) {

        $author = $this->security->getUser();
        $references = $residencyApplication->getReferences();
        $count = count($references);

        //must be 4
        //Remove the fourth letter of recommendation from the front end application form => 3 references
        for( $count; $count < 3; $count++  ) {

            $reference = new Reference($author);
            $residencyApplication->addReference($reference);

        }

    }

    public function addEmptyBoardCertifications($residencyApplication) {

        $author = $this->security->getUser();
        $boardCertifications = $residencyApplication->getBoardCertifications();
        $count = count($boardCertifications);

        //must be 3
        for( $count; $count < 3; $count++  ) {

            $boardCertification = new BoardCertification($author);
            $residencyApplication->addBoardCertification($boardCertification);
            $residencyApplication->getUser()->getCredentials()->addBoardCertification($boardCertification);

        }

    }

    //app_resappbundle_residencyapplication[stateLicenses][0][licenseNumber]
    public function addEmptyStateLicenses($residencyApplication) {

        $author = $this->security->getUser();

        $stateLicenses = $residencyApplication->getStateLicenses();

        $count = count($stateLicenses);

        //must be 2
        for( $count; $count < 2; $count++  ) {

            $license = new StateLicense($author);
            $residencyApplication->addStateLicense($license);
            $residencyApplication->getUser()->getCredentials()->addStateLicense($license);

        }

    }

    public function addEmptyNationalBoards($residencyApplication) {

        $author = $this->security->getUser();

        $examinations = $residencyApplication->getExaminations();

        if( count($examinations) == 0 ) {
            $examination = new Examination($author);
            $residencyApplication->addExamination($examination);
        } else {
            //$examination = $examinations[0];
        }

    }

    public function addEmptyCitizenships($residencyApplication) {
        $author = $this->security->getUser();

        $citizenships = $residencyApplication->getCitizenships();

        if( count($citizenships) == 0 ) {
            $citizenship = new Citizenship($author);
            $residencyApplication->addCitizenship($citizenship);
        } else {
            //
        }
    }

    public function addEmptyLocations($residencyApplication) {

        $this->addLocationByType($residencyApplication,"Present Address");
        $this->addLocationByType($residencyApplication,"Permanent Address");
        $this->addLocationByType($residencyApplication,"Work Address");

    }
    public function addLocationByType($residencyApplication,$typeName) {

        $user = $residencyApplication->getUser();

        $specificLocation = null;

        foreach( $user->getLocations() as $location ) {
            if( $location->hasLocationTypeName($typeName) ) {
                $specificLocation = $location;
                break;
            }
        }

        if( !$specificLocation ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:LocationTypeList'] by [LocationTypeList::class]
            $locationType = $this->em->getRepository(LocationTypeList::class)->findOneByName($typeName);
            if( !$locationType ) {
                throw new EntityNotFoundException('Unable to find entity by name='.$typeName);
            }

            $specificLocation = new Location();
            $specificLocation->setName('Residency Applicant '.$typeName);
            $specificLocation->addLocationType($locationType);
            $user->addLocation($specificLocation);
            $residencyApplication->addLocation($specificLocation);
        }

    }

    public function addEmptyTrainings($residencyApplication) {
        //echo "add Empty Trainings <br>";
        $this->addTrainingByType($residencyApplication,"Medical",1);
        $this->addTrainingByType($residencyApplication,"Residency",4); //Previous Residency
    }
    public function addTrainingByType($residencyApplication,$typeName,$orderinlist,$maxNumber=1) {
        //echo "<br><br>####### add TrainingByType: $typeName #######<br>";
        $user = $residencyApplication->getUser();

        $trainings = $user->getTrainings();
        //echo "training count=".count($trainings)."<br>";

        $count = 0;

        foreach( $trainings as $training ) {
            //echo "training=".$training->getTrainingType()."<br>";
            if( $training->getTrainingType() && $training->getTrainingType()->getName()."" == $typeName ) {
                //echo $typeName.": count++=[".$training->getTrainingType()->getName()."]<br>";
                $count++;
            }
        }
        //echo $typeName.": maxNumber=".$maxNumber.", count=".$count."<br>";

        //add up to maxNumber
        for( $count; $count < $maxNumber; $count++ ) {
            //echo "maxNumber=".$maxNumber.", count=".$count."<br>";
            $this->addSingleTraining($residencyApplication,$typeName,$orderinlist);
        }

    }
    public function addSingleTraining($residencyApplication,$typeName,$orderinlist) {
        //echo "!!!!!!!!!! add single training with type=".$typeName."<br>";
        $author = $this->security->getUser();
        $training = new Training($author);
        $training->setOrderinlist($orderinlist);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingTypeList'] by [TrainingTypeList::class]
        $trainingType = $this->em->getRepository(TrainingTypeList::class)->findOneByName($typeName);
        $training->setTrainingType($trainingType);

        //s2id_oleg_resappbundle_residencyapplication_trainings_1_jobTitle
        if( $typeName == 'Other' ) {
            //otherExperience1Name => jobTitle
            //if( !$training->getJobTitle() ) {
                $jobTitleEntity = new JobTitleList();
                $training->setJobTitle($jobTitleEntity);
            //}
        }

        $residencyApplication->addTraining($training);
        $residencyApplication->getUser()->addTraining($training);
    }


    public function createApplicantListExcel( $resappids ) {
        
        $author = $this->security->getUser();
        $transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');
        
        $ea = new Spreadsheet(); // ea is short for Excel Application
               
        $ea->getProperties()
            ->setCreator($author."")
            ->setTitle('Residency Applicants')
            ->setLastModifiedBy($author."")
            ->setDescription('Residency Applicants list in spreadsheet format')
            ->setSubject('PHP spreadsheet manipulation')
            ->setKeywords('spreadsheet php office')
            ->setCategory('programming')
            ;
        
        $ews = $ea->getSheet(0);
        $ews->setTitle('Residency Applicants');
        
        //align all cells to left
        $style = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            )
        );
        $ews->getParent()->getDefaultStyle()->applyFromArray($style);
        
        $ews->setCellValue('A1', 'ID'); // Sets cell 'a1' to value 'ID 
        $ews->setCellValue('B1', 'First Name');
        $ews->setCellValue('C1', 'Last Name');
        $ews->setCellValue('D1', 'Medical Degree');
        $ews->setCellValue('E1', 'Medical School');
        $ews->setCellValue('F1', 'Residency Institution');
        $ews->setCellValue('G1', 'References');
        $ews->setCellValue('H1', 'Interview Score');
        $ews->setCellValue('I1', 'Interview Date');
        
        $ews->setCellValue('J1', 'Interviewer');
        $ews->setCellValue('K1', 'Date');
        $ews->setCellValue('L1', 'Academic Score');
        $ews->setCellValue('M1', 'Personality Score');
        $ews->setCellValue('N1', 'Overall Potential Score');
        $ews->setCellValue('O1', 'Total Score');
        $ews->setCellValue('P1', 'Language Proficiency');
        $ews->setCellValue('Q1', 'Fit for Program');
        $ews->setCellValue('R1', 'Comments');
        

        
        $row = 2;
        foreach( explode("-",$resappids) as $resappId ) {
        
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResidencyApplication'] by [ResidencyApplication::class]
            $resapp = $this->em->getRepository(ResidencyApplication::class)->find($resappId);
            if( !$resapp ) {
                continue;
            }
            
            //check if author can have access to view this applicant
            //user who has the same res type can view or edit
            if( $this->hasResappPermission($author,$resapp) == false ) {
                continue; //skip this applicant because the current user does not permission to view this applicant
            }
            
            $ews->setCellValue('A'.$row, $resapp->getId());  
            $ews->setCellValue('B'.$row, $resapp->getUser()->getFirstNameUppercase());
            $ews->setCellValue('C'.$row, $resapp->getUser()->getLastNameUppercase());
            
            //Medical Degree
            $ews->setCellValue('D'.$row, $resapp->getDegreeByTrainingTypeName('Medical'));
            
            //Medical School
            $ews->setCellValue('E'.$row, $resapp->getSchoolByTrainingTypeName('Medical'));
            
            //Residency Institution
            $ews->setCellValue('F'.$row, $resapp->getSchoolByTrainingTypeName('Residency'));
            
            //References
            $ews->setCellValue('G'.$row, $resapp->getAllReferences());
            
            //Interview Score
            $totalScore = "";
            if( $resapp->getInterviewScore() ) {
                $totalScore = $resapp->getInterviewScore();
            }
            $ews->setCellValue('H'.$row, $totalScore );
	       
            //Interview Date                   
            $ews->setCellValue('I'.$row, $transformer->transform($resapp->getInterviewDate()));
            
            $allTotalRanks = 0;
            
            foreach( $resapp->getInterviews() as $interview ) {
            
                //Interviewer
                if( $interview->getInterviewer() ) {
                    $ews->setCellValue('J'.$row, $interview->getInterviewer()->getUsernameOptimal());
                }
                
                //Date
                $ews->setCellValue('K'.$row, $transformer->transform($interview->getInterviewDate()));
                
                //Academic Rank
                if( $interview->getAcademicRank() ) {
                    $ews->setCellValue('L'.$row, $interview->getAcademicRank()->getValue());
                }
                
                //Personality Rank
                if( $interview->getPersonalityRank() ) {
                    $ews->setCellValue('M'.$row, $interview->getPersonalityRank()->getValue());
                }
                
                //Potential Rank
                if( $interview->getPotentialRank() ) {
                    $ews->setCellValue('N'.$row, $interview->getPotentialRank()->getValue());
                }
                
                //Total Rank
                $ews->setCellValue('O'.$row, $interview->getTotalRank());
                $allTotalRanks = $allTotalRanks + $interview->getTotalRank();
                
                //Language Proficiency
                if( $interview->getLanguageProficiency() ) {
                    $ews->setCellValue('P'.$row, $interview->getLanguageProficiency()->getName());
                }

                //fitForProgram
                if( $interview->getFitForProgram() ) {
                    $ews->setCellValue('Q'.$row, $interview->getFitForProgram()->getName());
                }

                //Comments
                $ews->setCellValue('R'.$row, $interview->getComment());
                
                $row++;
            
            } //for each interview
            
            //space in case if there is no interviewers 
            if( count($resapp->getInterviews()) == 0 ) {
                $row++;
            }
            
            //All Total Ranks:           
            $ews->setCellValue('A'.$row, "All Total Scores:");
            $ews->setCellValue('B'.$row, $allTotalRanks);
            
            //Avg Rank:
            $row++;
            $ews->setCellValue('A'.$row, "Avg Score:");
            $ews->setCellValue('B'.$row, $totalScore);
            
            $row = $row + 2;
        }
        
        //exit("ids=".$resappids);
        
        
        // Auto size columns for each worksheet
        //\PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        foreach ($ea->getWorksheetIterator() as $worksheet) {

            $ea->setActiveSheetIndex($ea->getIndex($worksheet));

            $sheet = $ea->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }
               
        
        return $ea;
    }
    public function createApplicantListExcelSpout( $resappids, $fileName ) {

        $author = $this->security->getUser();
        $transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');

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

//        $ews->setCellValue('A1', 'ID'); // Sets cell 'a1' to value 'ID
//        $ews->setCellValue('B1', 'First Name');
//        $ews->setCellValue('C1', 'Last Name');
//        $ews->setCellValue('D1', 'Medical Degree');
//        $ews->setCellValue('E1', 'Medical School');
//        $ews->setCellValue('F1', 'Residency Institution');
//        $ews->setCellValue('G1', 'References');
//        $ews->setCellValue('H1', 'Interview Score');
//        $ews->setCellValue('I1', 'Interview Date');
//
//        $ews->setCellValue('J1', 'Interviewer');
//        $ews->setCellValue('K1', 'Date');
//        $ews->setCellValue('L1', 'Academic Rank');
//        $ews->setCellValue('M1', 'Personality Rank');
//        $ews->setCellValue('N1', 'Potential Rank');
//        $ews->setCellValue('O1', 'Total Rank');
//        $ews->setCellValue('P1', 'Language Proficiency');
//        $ews->setCellValue('Q1', 'Comments');
//        $writer->addRowWithStyle(
//            [
//                'ID',                           //0 - A
//                'First Name',                   //1 - B
//                'Last Name',                    //2 - C
//                'Start Year',                   //3 - D
//                'Medical Degree',               //4 - E
//                'Medical School',               //5 - F
//                'Residency Institution',        //6 - G
//                'References',                   //7 - H
//                'Interview Score',              //8 - I
//                'Interview Date',               //9 - J
//                'Interviewer',                  //10 - K
//                'Date',                         //11 - L
//                'Academic Rank',                //12 - M
//                'Personality Rank',             //13 - N
//                'Potential Rank',               //14 - O
//                'Total Rank',                   //15 - P
//                'Language Proficiency',         //16 - Q
//                'Comments',                     //17 - R
//            ],
//            $headerStyle
//        );
        $spoutRow = WriterEntityFactory::createRowFromArray(
            [
                'ID',                           //0 - A
                'First Name',                   //1 - B
                'Last Name',                    //2 - C
                'Start Year',                   //3 - D
                'Medical Degree',               //4 - E
                'Medical School',               //5 - F
                'Residency Institution',        //6 - G
                'References',                   //7 - H
                'Interview Score',              //8 - I
                'Interview Date',               //9 - J
                'Interviewer',                  //10 - K
                'Date',                         //11 - L
                'Academic Score',                //12 - M
                'Personality Score',             //13 - N
                'Overall Potential Score',       //14 - O
                'Total Score',                  //15 - P
                'Language Proficiency',         //16 - Q
                'Fit for Program',             //17 - R
                'Comments',                     //18 - S
            ],
            $headerStyle
        );
        $writer->addRow($spoutRow);

        //$row = 2;

        foreach( explode("-",$resappids) as $resappId ) {

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResidencyApplication'] by [ResidencyApplication::class]
            $resapp = $this->em->getRepository(ResidencyApplication::class)->find($resappId);
            if( !$resapp ) {
                continue;
            }

            //check if author can have access to view this applicant
            //user who has the same res type can view or edit
            if( $this->hasResappPermission($author,$resapp) == false ) {
                continue; //skip this applicant because the current user does not permission to view this applicant
            }

            $data = array();

            //$ews->setCellValue('A'.$row, $resapp->getId());
            $data[0] = $resapp->getId();

            //$ews->setCellValue('B'.$row, $resapp->getUser()->getFirstNameUppercase());
            $data[1] = $resapp->getUser()->getFirstNameUppercase();

            //$ews->setCellValue('C'.$row, $resapp->getUser()->getLastNameUppercase());
            $data[2] = $resapp->getUser()->getLastNameUppercase();

            $startDate = $resapp->getStartDate();
            if( $startDate ) {
                $data[3] = $startDate->format('Y');
            }

            //Medical Degree
            //$ews->setCellValue('D'.$row, $resapp->getDegreeByTrainingTypeName('Medical'));
            $data[4] = $resapp->getDegreeByTrainingTypeName('Medical');

            //Medical School
            //$ews->setCellValue('E'.$row, $resapp->getSchoolByTrainingTypeName('Medical'));
            $data[5] = $resapp->getSchoolByTrainingTypeName('Medical');

            //Residency Institution
            //$ews->setCellValue('F'.$row, $resapp->getSchoolByTrainingTypeName('Residency'));
            $data[6] = $resapp->getSchoolByTrainingTypeName('Residency');

            //References
            //$ews->setCellValue('G'.$row, $resapp->getAllReferences());
            $data[7] = $resapp->getAllReferences();

                //Interview Score
            $totalScore = "";
            if( $resapp->getInterviewScore() ) {
                $totalScore = $resapp->getInterviewScore();
            }
            //$ews->setCellValue('H'.$row, $totalScore );
            $data[8] = $totalScore;

            //Interview Date
            //$ews->setCellValue('I'.$row, $transformer->transform($resapp->getInterviewDate()));
            $data[9] = $transformer->transform($resapp->getInterviewDate());

            //$writer->addRowWithStyle($data,$requestStyle);
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $requestStyle);
            $writer->addRow($spoutRow);

            $allTotalRanks = 0;
            $interviewers = $resapp->getInterviews();

            foreach( $interviewers as $interview ) {

                $data = array();
                $data[0] = null;
                $data[1] = null;
                $data[2] = null;
                $data[3] = null;
                $data[4] = null;
                $data[5] = null;
                $data[6] = null;
                $data[7] = null;
                $data[8] = null;
                $data[9] = null;

                //Interviewer
                if( $interview->getInterviewer() ) {
                    //$ews->setCellValue('J'.$row, $interview->getInterviewer()->getUsernameOptimal());
                    $data[10] = $interview->getInterviewer()->getUsernameOptimal();
                } else {
                    $data[10] = null;
                }

                //Date
                //$ews->setCellValue('K'.$row, $transformer->transform($interview->getInterviewDate()));
                $data[11] = $transformer->transform($interview->getInterviewDate());

                //Academic Rank
                if( $interview->getAcademicRank() ) {
                    //$ews->setCellValue('L'.$row, $interview->getAcademicRank()->getValue());
                    $data[12] = $interview->getAcademicRank()->getValue();
                } else {
                    $data[12] = null;
                }

                //Personality Rank
                if( $interview->getPersonalityRank() ) {
                    //$ews->setCellValue('M'.$row, $interview->getPersonalityRank()->getValue());
                    $data[13] = $interview->getPersonalityRank()->getValue();
                } else {
                    $data[13] = null;
                }

                //Potential Rank
                if( $interview->getPotentialRank() ) {
                    //$ews->setCellValue('N'.$row, $interview->getPotentialRank()->getValue());
                    $data[14] = $interview->getPotentialRank()->getValue();
                } else {
                    $data[14] = null;
                }

                //Total Rank
                //$ews->setCellValue('O'.$row, $interview->getTotalRank());
                $data[15] = $interview->getTotalRank();
                $allTotalRanks = $allTotalRanks + $interview->getTotalRank();

                //Language Proficiency
                if( $interview->getLanguageProficiency() ) {
                    //$ews->setCellValue('P'.$row, $interview->getLanguageProficiency()->getName());
                    $data[16] = $interview->getLanguageProficiency()->getName();
                } else {
                    $data[16] = null;
                }

                //fitForProgram
                if( $interview->getFitForProgram() ) {
                    $data[17] = $interview->getFitForProgram()->getName();
                } else {
                    $data[17] = null;
                }

                //Comments
                //$ews->setCellValue('Q'.$row, $interview->getComment());
                $data[18] = $interview->getComment();

                //$writer->addRowWithStyle($data,$requestStyle);
                $spoutRow = WriterEntityFactory::createRowFromArray($data, $requestStyle);
                $writer->addRow($spoutRow);

            } //for each interview

            //space in case if there is no interviewers
            if( count($resapp->getInterviews()) == 0 ) {
                //$row++;
            }

            if( count($interviewers) == 0 ) {
                $allTotalRanks = "N/A";
                $totalScore = "N/A";
            }

            $data = array();

            //All Total Ranks:
            //$ews->setCellValue('A'.$row, "All Total Ranks:");
            $data[0] = "All Total Scores:";

            //$ews->setCellValue('B'.$row, $allTotalRanks);
            $data[1] = $allTotalRanks;

            //$writer->addRowWithStyle($data, $footerStyle);
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
            $writer->addRow($spoutRow);

            //Avg Rank:
            $data = array();
            //$row++;
            //$ews->setCellValue('A'.$row, "Avg Rank:");
            $data[0] = "Avg Score:";
            //$ews->setCellValue('B'.$row, $totalScore);
            $data[1] = $totalScore;
            //$writer->addRowWithStyle($data, $footerStyle);
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
            $writer->addRow($spoutRow);


            //$row = $row + 2;
        }

        $writer->close();
    }


    public function createInterviewApplicantList( $resappids ) {

        $author = $this->security->getUser();

        $resapps = array();

        foreach( explode("-",$resappids) as $resappId ) {

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResidencyApplication'] by [ResidencyApplication::class]
            $resapp = $this->em->getRepository(ResidencyApplication::class)->find($resappId);
            if( !$resapp ) {
                continue;
            }

            //check if author can have access to view this applicant
            //user who has the same res type can view or edit
            if( $this->hasResappPermission($author,$resapp) == false ) {
                continue; //skip this applicant because the current user does not permission to view this applicant
            }

            //only include the people who have an interview date (not the status of the interviewer)
            if( !$resapp->getInterviewDate() ) {
                continue;
            }

            $resapps[] = $resapp;
        }

        //exit("ids=".$resappids);
        return $resapps;
    }


    //$roleType: string (INTERVIEWER, COORDINATOR, DIRECTOR)
    //name: ROLE_RESAPP_DIRECTOR_WCM_BREASTPATHOLOGY
    //alias: Residency Program Interviewer WCMC Breast Pathology
    //Description: Access to specific Residency Application type as Interviewer
    //site: resapp
    //Institution: WCMC
    //ResidencyTrack: Breast Pathology
    //Permissions: Create a New Residency Application, Modify a Residency Application, Submit an interview evaluation
    public function createOrEnableResAppRole( $subspecialtyType, $roleType, $institution, $testing=false ) {
        $em = $this->em;
        $user = $this->security->getUser();
        $userSecUtil = $this->container->get('user_security_utility');
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:SiteList'] by [SiteList::class]
        $site = $em->getRepository(SiteList::class)->findOneByAbbreviation('resapp');

        $count = 0;

        //1) name: ROLE_RESAPP_DIRECTOR_WCM_BREASTPATHOLOGY
        //get ROLE NAME: Pathology Informatics => PATHOLOGYINFORMATCS
        $roleNameBase = str_replace(" ","",$subspecialtyType->getName());
        $roleNameBase = strtoupper($roleNameBase);
        //echo "roleNameBase=$roleNameBase<br>";

        //create Director role
        $roleName = "ROLE_RESAPP_".$roleType."_WCM_".$roleNameBase;
        //echo "roleName=$roleName<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $role = $em->getRepository(Roles::class)->findOneByName($roleName);

        if( !$role ) {
            $roleTypeStr = ucfirst(strtolower($roleType));
            //exit('1: '.$roleTypeStr);

            $role = new Roles();
            $role = $userSecUtil->setDefaultList($role, null, $user, $roleName);
            $role->setAlias('Residency Program '.$roleTypeStr.' WCM ' . $subspecialtyType->getName());
            $role->setDescription('Access to specific residency track as '.$roleTypeStr);
            $role->addSite($site);
            $role->setInstitution($institution);
            $role->setResidencyTrack($subspecialtyType);

            if( $roleType == "INTERVIEWER" ) {
                $role->setLevel(30);
                $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit an interview evaluation","Interview","create");
            }

            if( $roleType == "COORDINATOR" ) {
                $role->setLevel(40);
                $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Create a New Residency Application","ResidencyApplication","create");
                $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Modify a Residency Application","ResidencyApplication","update");
            }

            if( $roleType == "DIRECTOR" ) {
                $role->setLevel(50);
                $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Create a New Residency Application","ResidencyApplication","create");
                $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Modify a Residency Application","ResidencyApplication","update");
            }

            if( $count > 0 && !$testing ) {
                $em->persist($role);
                $em->flush($role);
            }

        } else {
            $roleType = $role->getType();
            //exit('2: '.$roleType);
            if( $roleType != 'default' && $roleType != 'user-added' ) {
                $role->setType('default');
                if( !$testing ) {
                    $em->persist($role);
                    $em->flush($role);
                }
                $count++;
            }
        }

        return $count;
    }

    //TODO: add this function to user's profile create/update. Maybe, find a more efficient way to sync (if user's role with resapp changed).
    //When the role (i.e. coordinator) is added by editing the user's profile directly, this ResidencyTrack object is not updated.
    //Synchronise the ResidencyTrack's $coordinators, $directors, $interviewers with the user profiles based on the specific roles:
    //get all users with specific coordinator role and add them (if not added) to the $coordinators in the ResidencyTrack object
    public function synchroniseResidencyTrackAndProfileRoles( $residencyTypes ) {
        //return null; //testing
        //echo "sync ResidencyTrack count=".count($residencyTypes)."<br>";
        //iterate over all ResidencyTrack objects
        foreach( $residencyTypes as $residencyTrack ) {
            //$residencyType - Pain Medicine => ROLE_RESAPP_DIRECTOR_WCM_PAINMEDICINE
//            $this->synchroniseSingleResidencyTrackAndProfileRoles($residencyTrack,"_COORDINATOR_");
//            $this->synchroniseSingleResidencyTrackAndProfileRoles($residencyTrack,"_DIRECTOR_");
//            $this->synchroniseSingleResidencyTrackAndProfileRoles($residencyTrack,"_INTERVIEWER_");

            $this->synchroniseSingleResidencyTrackAndProfileRoles($residencyTrack,"_COORDINATOR_");
            $this->synchroniseSingleResidencyTrackAndProfileRoles($residencyTrack,"_DIRECTOR_");
            $this->synchroniseSingleResidencyTrackAndProfileRoles($residencyTrack,"_INTERVIEWER_");
        }
    }
    //NOT USED. Replaced by synchroniseSingleResidencyTrackAndProfileRoles
    public function synchroniseSingleResidencyTrackAndProfileRoles( $residencyTrack, $roleName ) {
        //1) get all users with role ROLE_RESAPP_DIRECTOR_WCM_PAINMEDICINE
        $users = $this->getUsersOfResidencyTrackByRole($residencyTrack,$roleName); //"_COORDINATOR_"

        //2) for each $coordinators in the ResidencyTrack - check if this user exists in the coordinators, add if not.
        if( $roleName == "_COORDINATOR_" ) {
            $attachedUsers = $residencyTrack->getCoordinators();
        }
        if( $roleName == "_DIRECTOR_" ) {
            $attachedUsers = $residencyTrack->getDirectors();
        }
        if( $roleName == "_INTERVIEWER_" ) {
            $attachedUsers = $residencyTrack->getInterviewers();
        }

        $modified = false;

        foreach( $users as $user ) {

            //Add user to ResidencyTrack if user is not attached yet
            if( $user && !$attachedUsers->contains($user) ) {
                if( $roleName == "_COORDINATOR_" ) {
                    $residencyTrack->addCoordinator($user);
                }
                if( $roleName == "_DIRECTOR_" ) {
                    $residencyTrack->addDirector($user);
                }
                if( $roleName == "_INTERVIEWER_" ) {
                    $residencyTrack->addInterviewer($user);
                }
                $modified = true;
            }

        }

        //Removing the role manually => remove user from $residencyTrack: remove user from ResidencyTrack if user does not have role
        //get coordinators => check if each coordinator has role => if not => remove this user from ResidencyTrack
        $role = $this->getRoleByResidencyTrackAndRolename($residencyTrack,$roleName );
        //echo $roleName.": role=".$role."<br>";

        foreach( $attachedUsers as $user ) {
            if( !$user->hasRole($role) ) {
                //echo $roleName.": remove user=".$user."!!!!!!!!!!!!<br>";
                if ($roleName == "_COORDINATOR_") {
                    $residencyTrack->removeCoordinator($user);
                }
                if ($roleName == "_DIRECTOR_") {
                    $residencyTrack->removeDirector($user);
                }
                if ($roleName == "_INTERVIEWER_") {
                    $residencyTrack->removeInterviewer($user);
                }
                $modified = true;
            }
        }


        if( $modified ) {
            //$this->em->persist($residencyTrack);
            $this->em->flush();
        }
    }
//    public function synchroniseSingleResidencyTrackAndProfileRoles( $residencyTrack, $roleName ) {
//        //1) get all users with role ROLE_RESAPP_DIRECTOR_WCM_PAINMEDICINE
//        $users = $this->getUsersOfResidencyTrackByRole($residencyTrack,$roleName); //"_COORDINATOR_"
//
//        //2) for each $coordinators in the ResidencyTrack - check if this user exists in the coordinators, add if not.
//        if( $roleName == "_COORDINATOR_" ) {
//            $attachedUsers = $residencyTrack->getCoordinators();
//        }
//        if( $roleName == "_DIRECTOR_" ) {
//            $attachedUsers = $residencyTrack->getDirectors();
//        }
//        if( $roleName == "_INTERVIEWER_" ) {
//            $attachedUsers = $residencyTrack->getInterviewers();
//        }
//
//        $modified = false;
//
//        foreach( $users as $user ) {
//
//            //Add user to ResidencyTrack if user is not attached yet
//            if( $user && !$attachedUsers->contains($user) ) {
//                if( $roleName == "_COORDINATOR_" ) {
//                    $residencyTrack->addCoordinator($user);
//                }
//                if( $roleName == "_DIRECTOR_" ) {
//                    $residencyTrack->addDirector($user);
//                }
//                if( $roleName == "_INTERVIEWER_" ) {
//                    $residencyTrack->addInterviewer($user);
//                }
//                $modified = true;
//            }
//
//        }
//
//        //Removing the role manually => remove user from $residencyTrack: remove user from ResidencyTrack if user does not have role
//        //get coordinators => check if each coordinator has role => if not => remove this user from ResidencyTrack
//        //$role = $this->getRoleByResidencyTrackAndRolename($residencyTrack,$roleName );
//        $role = $this->getRoleByResidencyTrackAndRolename($residencyTrack,$roleName );
//        //echo $roleName.": role=".$role."<br>";
//
//        foreach( $attachedUsers as $user ) {
//            if( !$user->hasRole($role) ) {
//                //echo $roleName.": remove user=".$user."!!!!!!!!!!!!<br>";
//                if ($roleName == "_COORDINATOR_") {
//                    $residencyTrack->removeCoordinator($user);
//                }
//                if ($roleName == "_DIRECTOR_") {
//                    $residencyTrack->removeDirector($user);
//                }
//                if ($roleName == "_INTERVIEWER_") {
//                    $residencyTrack->removeInterviewer($user);
//                }
//                $modified = true;
//            }
//        }
//
//
//        if( $modified ) {
//            //$this->em->persist($residencyTrack);
//            //$this->em->flush($resAppTypeConfig);
//            $this->em->flush();
//        }
//    }

    //compare original and final users => get removed users => for each removed user, remove the role
    public function processRemovedUsersByResidencySetting( $residencyTrack, $newUsers, $origUsers, $roleName ) {
        //if( count($newUsers) > 0 && count($origUsers) > 0 ) {
            //$this->printUsers($origUsers,"orig");
            //$this->printUsers($newUsers,"new");

            //get diff
            $diffUsers = $this->array_diff_assoc_true($newUsers->toArray(), $origUsers->toArray());
            //$diffUsers = array_diff($newUsers->toArray(),$origUsers->toArray());
            //$diffUsers = array_diff($origUsers->toArray(),$newUsers->toArray());

            //echo $roleName.": diffUsers count=".count($diffUsers)."<br>";
            //$this->printUsers($diffUsers,"diff");

            $this->removeRoleFromUsers($diffUsers,$residencyTrack,$roleName);
        //}
    }
    public function removeRoleFromUsers( $users, $residencyTrack, $roleName ) {
        $role = $this->getRoleByResidencyTrackAndRolename($residencyTrack,$roleName );
        if( !$role ) {
            return null;
        }
        //echo $roleName.": role=".$role."<br>";
        foreach( $users as $user ) {
            //echo $roleName.": removeRole from user=".$user."<br>";
            $user->removeRole($role);
            //$this->em->flush($user);
            $this->em->flush();
        }
    }
    public function array_diff_assoc_true($array1, $array2)
    {
        //$diff1 = array_diff_assoc($array1,$array2);
        //$diff2 = array_diff_assoc($array2,$array1);
        $diff1 = array_diff($array1,$array2);
        $diff2 = array_diff($array2,$array1);

        //echo "diff1:<br>";
        //print_r($diff1);
        //echo "<br>diff2:<br>";
        //print_r($diff2);
        //echo "<br><br>";

        $res = array_merge( $diff1, $diff2 );
        $res = array_unique($res);

        //echo "res:<br>";
        //print_r($res);
        //echo "<br><br>";

        return $res;
    }
    public function printUsers( $users, $prefix=null ) {
        echo "###########$prefix############<br>";
        foreach( $users as $user ) {
            echo "$user <br>";
        }
        echo "######################<br><br>";
    }

    public function findInterviewByResappAndUser( $resapp, $user ) {
        $interviews = array();
        foreach($resapp->getInterviews() as $interview) {
            $interviewer = $interview->getInterviewer();
            if( $interviewer && $user && $interviewer->getId() == $user->getId() ) {
                $interviews[] = $interview;
            }
        }
        return $interviews;
    }

    public function sendAcceptedNotificationEmail($resapp) {
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        $user = $this->security->getUser();

        if( $user && $user instanceof User) {
            //User OK - do nothing
        } else {
            $user = $userSecUtil->findSystemUser();
        }
        if( !$user ) {
            $user = $userSecUtil->findSystemUser();
        }

        $applicant = $resapp->getUser();
        if( $applicant ) {
            $applicantEmail = $applicant->getSingleEmail();
        } else {
            return false;
        }

        $applicantFullName = $resapp->getApplicantFullName();
        $resappType = $resapp->getResidencyTrack()."";
        $startDate = $resapp->getStartDate();
        if( $startDate ) {
            $startDateStr = $resapp->getStartDate()->format('Y');
        } else {
            $startDateStr = NULL;
        }

        $acceptedEmailSubject = $userSecUtil->getSiteSettingParameter('acceptedEmailSubject',$this->container->getParameter('resapp.sitename'));
        if( !$acceptedEmailSubject ) {
            //Congratulations on your acceptance to the [Subspecialty] [Year] residency at [Institution].
            //Institution should be a variable pre-set to "Weill Cornell Medicine" - if it does not exist, add this field to its Settings.
            $inst = $resapp->getInstitution()."";
            $acceptedEmailSubject = "Congratulations on your acceptance to the "
                .$resappType
                ." ".$startDateStr
                ." residency at ".$inst
            ;
        } else {
            $acceptedEmailSubject = $this->siteSettingsConstantReplace($acceptedEmailSubject,$resapp);
        }

        $acceptedEmailBody = $userSecUtil->getSiteSettingParameter('acceptedEmailBody',$this->container->getParameter('resapp.sitename'));
        if( !$acceptedEmailBody ) {
            //Dear FirstName LastName,
            //We are looking forward to having you join us as a [specialty] resident in [year]!
            //Weill Cornell Medicine
            $acceptedEmailBody = "Dear $applicantFullName,"
                ."<br><br>"."We are looking forward to having you join us as a $resappType resident in $startDateStr!"
                ."<br><br>".$inst
            ;
        } else {
            $acceptedEmailBody = $this->siteSettingsConstantReplace($acceptedEmailBody,$resapp);
        }

        //get CCs: coordinators and directors
        $directorEmails = $this->getDirectorsOfResAppEmails($resapp);
        $coordinatorEmails = $this->getCoordinatorsOfResAppEmails($resapp);
        $ccResponsibleEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));

        $emailUtil->sendEmail( $applicantEmail, $acceptedEmailSubject, $acceptedEmailBody, $ccResponsibleEmails );

        $msg = "Acceptance notification email has been sent to " . $applicantFullName . " (".$applicantEmail.")" . "; CC: ".implode(", ",$ccResponsibleEmails);
        $eventMsg = $msg . "<br><br> Subject:<br>". $acceptedEmailSubject . "<br><br>Body:<br>" . $acceptedEmailBody;

        $userSecUtil->createUserEditEvent(
            $this->container->getParameter('resapp.sitename'), //$sitename
            $eventMsg,                                          //$event message
            $user,                                              //user
            $resapp,                                           //$subjectEntities
            null,                                               //$request
            "ResApp Accepted Notification Email Sent"          //$action
        );

        return true;
    }

    public function sendRejectedNotificationEmail($resapp) {
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        $user = $this->security->getUser();

        if( $user && $user instanceof User) {
            //User OK - do nothing
        } else {
            $user = $userSecUtil->findSystemUser();
        }
        if( !$user ) {
            $user = $userSecUtil->findSystemUser();
        }

        $applicant = $resapp->getUser();
        if( $applicant ) {
            $applicantEmail = $applicant->getSingleEmail();
        } else {
            return false;
        }

        $applicantFullName = $resapp->getApplicantFullName();
        $resappType = $resapp->getResidencyTrack()."";
        $startDate = $resapp->getStartDate();
        if( $startDate ) {
            $startDateStr = $resapp->getStartDate()->format('Y');
        } else {
            $startDateStr = NULL;
        }

        $rejectedEmailSubject = $userSecUtil->getSiteSettingParameter('rejectedEmailSubject',$this->container->getParameter('resapp.sitename'));
        if( !$rejectedEmailSubject ) {
            //Thank you for applying to the [Subspecialty] [Year] residency at [Institution]
            $inst = $resapp->getInstitution()."";
            $rejectedEmailSubject = "Thank you for applying to the "
                .$resappType
                ." ".$startDateStr
                ." residency at ".$inst
            ;
        } else {
            $rejectedEmailSubject = $this->siteSettingsConstantReplace($rejectedEmailSubject,$resapp);
        }

        $rejectedEmailBody = $userSecUtil->getSiteSettingParameter('rejectedEmailBody',$this->container->getParameter('resapp.sitename'));
        if( !$rejectedEmailBody ) {
            //Dear FirstName LastName,
            //We have reviewed your application to the [specialty] residency for [year],
            // and we regret to inform you that we are unable to offer you a position at this time.
            // Please contact us if you have any questions.
            //Weill Cornell Medicine
            $rejectedEmailBody = "Dear $applicantFullName,"
                ."<br><br>"."We have reviewed your application to the $resappType resident for $startDateStr"
                ." and we regret to inform you that we are unable to offer you a position at this time."
                ."<br>Please contact us if you have any questions."
                ."<br><br>".$inst
            ;
        } else {
            $rejectedEmailBody = $this->siteSettingsConstantReplace($rejectedEmailBody,$resapp);
        }

        //get CCs: coordinators and directors
        $directorEmails = $this->getDirectorsOfResAppEmails($resapp);
        $coordinatorEmails = $this->getCoordinatorsOfResAppEmails($resapp);
        $ccResponsibleEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));

        $emailUtil->sendEmail( $applicantEmail, $rejectedEmailSubject, $rejectedEmailBody, $ccResponsibleEmails );

        $msg = "Rejection notification email has been sent to " . $applicantFullName . " (".$applicantEmail.")" . "; CC: ".implode(", ",$ccResponsibleEmails);
        $eventMsg = $msg . "<br><br> Subject:<br>". $rejectedEmailSubject . "<br><br>Body:<br>" . $rejectedEmailBody;

        $userSecUtil->createUserEditEvent(
            $this->container->getParameter('resapp.sitename'), //$sitename
            $eventMsg,                                          //$event message
            $user,                                              //user
            $resapp,                                           //$subjectEntities
            null,                                               //$request
            "ResApp Rejected Notification Email Sent"          //$action
        );

        return true;
    }

    public function getRejectionEmailSent($resapp) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");

        //$resappIdInteger = $resapp->getId()."";
        //echo "resappIdInteger=".$resappIdInteger."<br>";

        $dql->innerJoin('logger.eventType', 'eventType');
        $dql->where("logger.entityName = 'ResidencyApplication' AND logger.entityId = '".$resapp->getId()."'");

        //$dql->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");
        $dql->andWhere("eventType.name = :eventTypeStr");

        $dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery();

        //The status of the work request APCP668-REQ16553 has been changed from 'Pending Histology' to 'Completed and Notified' by Susanna Mirabelli - sum2029 (WCM CWID)

        $query->setParameters(
            array(
                'eventTypeStr' => "ResApp Rejected Notification Email Sent"
            )
        );

        $loggers = $query->getResult();

        $sentDatesArr = array();
        foreach($loggers as $logger) {
            $creationDate = $logger->getCreationdate();
            if( $creationDate ) {
                $sentDatesArr[] = $creationDate->format('m/d/Y');
            }
        }

        if( count($sentDatesArr) > 0 ) {
            $sentDates = implode("<br>",$sentDatesArr);
        } else {
            $sentDates = null;
        }

        return $sentDates;
    }

    public function getResappAcceptanceRejectionEmailSent( $resapp, $fullNonHtmlInfo=false ) {
        $userServiceUtil = $this->container->get('user_service_utility');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");

        $dql->innerJoin('logger.eventType', 'eventType');
        $dql->where("logger.entityName = 'ResidencyApplication' AND logger.entityId = '".$resapp->getId()."'");

        //$dql->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");
        $dql->andWhere("eventType.name = :eventTypeRejectionStr OR eventType.name = :eventTypeAcceptanceStr");

        $dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery();

        //The status of the work request APCP668-REQ16553 has been changed from 'Pending Histology' to 'Completed and Notified' by Susanna Mirabelli - sum2029 (WCM CWID)

        $rejectionEventType = "ResApp Rejected Notification Email Sent";
        $acceptanceEventType = "ResApp Accepted Notification Email Sent";
        $query->setParameters(
            array(
                'eventTypeRejectionStr' => $rejectionEventType,
                'eventTypeAcceptanceStr' => $acceptanceEventType
            )
        );

        $loggers = $query->getResult();

        $sentRejectionDatesArr = array();
        $fullRejectionNonHtmlInfoArr = array();
        $sentAcceptanceDatesArr = array();
        $fullAcceptanceNonHtmlInfoArr = array();
        foreach($loggers as $logger) {
            $creationDate = $logger->getCreationdate();
            if( $creationDate ) {
                $creationDate = $userServiceUtil->convertFromUtcToUserTimezone($creationDate);
                if( $logger->getEventType() ) {
                    $eventTypeName = $logger->getEventType()->getName();
                    if( $eventTypeName == $rejectionEventType ) {
                        $sentRejectionDatesArr[] = "<p style='color:red'>".$creationDate->format('m/d/Y'); // . "-rejected"."</p>";
                        if( $fullNonHtmlInfo ) {
                            //on MM/DD/YYYY at HH:MM by FirstNameOfSender LastNameOfSender
                            $fullRejectionNonHtmlInfoArr[] = $creationDate->format('m/d/Y \a\t H:i:s')." by ".$logger->getUsernameOptimal();
                        }
                    } elseif( $eventTypeName == $acceptanceEventType ) {
                        $sentAcceptanceDatesArr[] = "<p style='color:darkgreen'>".$creationDate->format('m/d/Y'); // . "-accepted"."</p>";
                        if( $fullNonHtmlInfo ) {
                            $fullAcceptanceNonHtmlInfoArr[] = $creationDate->format('m/d/Y \a\t H:i:s')." by ".$logger->getUsernameOptimal();
                        }
                    } else {
                        //This case is not possible: if not acceptance or rejection => use rejection array
                        $sentRejectionDatesArr[] = "<p style='color:grey'>".$creationDate->format('m/d/Y H:i:s'); // . "-unknown"."</p>";
                        //if( $fullNonHtmlInfo ) {
                        //    $fullRejectionNonHtmlInfoArr[] = $creationDate->format('m/d/Y')." by ".$logger->getUser()." (Unknown notification email)";
                        //}
                    }
                }
            }
        }

        //$delimiter = "<br>";
        $delimiter = "";

        if( count($sentRejectionDatesArr) > 0 ) {
            $sentRejectionDates = implode($delimiter,$sentRejectionDatesArr);
            //$sentRejectionDates = $this->natural_language_join($sentRejectionDatesArr,'and');
        } else {
            $sentRejectionDates = null;
        }

        if( count($sentAcceptanceDatesArr) > 0 ) {
            $sentAcceptanceDates = implode($delimiter,$sentAcceptanceDatesArr);
            //$sentAcceptanceDates = $this->natural_language_join($sentAcceptanceDatesArr,'and');
        } else {
            $sentAcceptanceDates = null;
        }

//        if( $sentRejectionDates && $sentAcceptanceDates ) {
//            $sentAcceptanceDates = "<br>".$sentAcceptanceDates;
//        }

        $res = array(
            'rejection' => $sentRejectionDates,
            'acceptance' => $sentAcceptanceDates
        );

        if( $fullNonHtmlInfo ) {
            $delimiter = ", ";
            if( count($fullRejectionNonHtmlInfoArr) > 0 ) {
                //$fullRejectionNonHtmlInfo = implode($delimiter,$fullRejectionNonHtmlInfoArr);
                $fullRejectionNonHtmlInfo = $this->natural_language_join($fullRejectionNonHtmlInfoArr,'and');
            } else {
                $fullRejectionNonHtmlInfo = null;
            }

            if( count($fullAcceptanceNonHtmlInfoArr) > 0 ) {
                //$fullAcceptanceNonHtmlInfo = implode($delimiter,$fullAcceptanceNonHtmlInfoArr);
                $fullAcceptanceNonHtmlInfo = $this->natural_language_join($fullAcceptanceNonHtmlInfoArr,'and');
            } else {
                $fullAcceptanceNonHtmlInfo = null;
            }

            $res['fullRejectionNonHtmlInfo'] = $fullRejectionNonHtmlInfo;
            $res['fullAcceptanceNonHtmlInfo'] = $fullAcceptanceNonHtmlInfo;
        }

        return $res;
    }
    public function getRejectionAcceptanceEmailWarning($resapp,$html=true) {
        //$warningStr = "Warning";
        $warningStr = "";

        $rejectionAcceptanceEmailStr = $this->getResappAcceptanceRejectionEmailSent($resapp,true);

        $fullRejectionNonHtmlInfo = $rejectionAcceptanceEmailStr['fullRejectionNonHtmlInfo'];
        $fullAcceptanceNonHtmlInfo = $rejectionAcceptanceEmailStr['fullAcceptanceNonHtmlInfo'];

        $warningArr = array();


        if( $fullRejectionNonHtmlInfo || $fullAcceptanceNonHtmlInfo ) {
            $applicantFullName = $resapp->getApplicantFullName();
            $resappType = $resapp->getResidencyTrack() . "";
            $startDate = $resapp->getStartDate();
            if ($startDate) {
                $startDateStr = $resapp->getStartDate()->format('Y');
            } else {
                $startDateStr = NULL;
            }


            // If one or more rejection notification email has been sent to the same applicant
            // for the same residency and the same year, show:
            // “A rejection email has already been sent to this applicant (FirstName LastName)
            // for the ResidencyType ResidencyYear on MM/DD/YYYY at HH:MM by FirstNameOfSender LastNameOfSender.”
            //  (show the timestamps for the latest rejection email if there is more than one)
            if ($fullRejectionNonHtmlInfo && !$fullAcceptanceNonHtmlInfo) {
                $warningArr[] = "A rejection email has already been sent to this applicant $applicantFullName 
                                for the $resappType $startDateStr on $fullRejectionNonHtmlInfo.";
            }

            // An acceptance email has already been sent to this applicant (FirstName LastName)
            // for the ResidencyType ResidencyYear on MM/DD/YYYY at HH:MM by FirstNameOfSender LastNameOfSender.
            if ($fullAcceptanceNonHtmlInfo && !$fullRejectionNonHtmlInfo) {
                $warningArr[] = "An acceptance email has already been sent to this applicant $applicantFullName 
                                for the $resappType $startDateStr on $fullAcceptanceNonHtmlInfo.";
            }

            // A rejection email has already been sent to this applicant (FirstName LastName)
            // for the ResidencyType ResidencyYear on MM/DD/YYYY at HH:MM by FirstNameOfSender LastNameOfSender
            // and an acceptance email has already been sent to this applicant (FirstName Lastname)
            // for the ResidencyType ResidencyYear on MM/DD/YYYY at HH:MM by FirstNameOfSender LastNameOfSender.
            if( $fullRejectionNonHtmlInfo && $fullAcceptanceNonHtmlInfo ) {
                $warningArr[] = "A rejection email has already been sent to this applicant $applicantFullName 
                for the $resappType $startDateStr on $fullRejectionNonHtmlInfo 
                and an acceptance email has already been sent to this applicant $applicantFullName 
                for the $resappType $startDateStr on $fullAcceptanceNonHtmlInfo.";
            }

            if( count($warningArr) > 0 ) {
                $warningStr = implode("<br>",$warningArr);
            }

            if ($html) {
                $warningStr = "<p style='color:orange'>" . $warningStr . "</p>";
            }
            //exit($warningStr); //testing
        }

        return $warningStr;
    }
    
    public function siteSettingsConstantReplace($str,$resapp) {

        $applicantFullName = $resapp->getApplicantFullName();
        $resappType = $resapp->getResidencyTrack()."";
        $inst = $resapp->getInstitution()."";
        $startDate = $resapp->getStartDate();
        if( $startDate ) {
            $startDateStr = $resapp->getStartDate()->format('Y');
        } else {
            $startDateStr = NULL;
        }

        $directorsStr = $this->getProgramDirectorStr($resapp->getResidencyTrack(),$str);

        $str = str_replace("[[APPLICANT NAME]]",$applicantFullName,$str);
        $str = str_replace("[[START YEAR]]",$startDateStr,$str);
        $str = str_replace("[[RESIDENCY TYPE]]",$resappType,$str);
        $str = str_replace("[[INSTITUTION]]",$inst,$str);
        $str = str_replace("[[DIRECTOR]]",$directorsStr,$str);

        return $str;
    }

    public function getProgramDirectorStr( $residencyTrack, $str=NULL ) {
        $directorsStr = "Program Director";

        if( $str && strpos((string)$str, "[[DIRECTOR]]") === false ) {
            return $directorsStr;
        }

        if( $residencyTrack ) {
            $directors = $residencyTrack->getDirectors();
            $usernameArr = array();
            foreach( $directors as $director ) {
                //check if account is not inactivated/banned (ROLE_RESAPP_BANNED, ROLE_RESAPP_UNAPPROVED, ROLE_USERDIRECTORY_BANNED, ROLE_USERDIRECTORY_UNAPPROVED)
                if (
                    !$director->isEnabled() ||
                    $this->security->isGranted('ROLE_RESAPP_BANNED') ||
                    $this->security->isGranted('ROLE_RESAPP_UNAPPROVED')
                ) {
                    //user is locked, banned or unapproved
                } else {
                    //user is ok
                    $usernameArr[] = $director->getUsernameOptimal();
                }
            }

            if( count($usernameArr) > 0 ) {

                //for two FirstName1 LastName1, Degree(s) and FirstName2 LastName2, Degree(s)
                //for three or more/: FirstName1 LastName1, Degree(s), FirstName2 LastName2, Degree(s), and FirstName3 LastName3, Degree(s)
                $directorsStr = $this->natural_language_join($usernameArr,'and');

            }
        }

        return $directorsStr;
    }
    /**
     * Join a string with a natural language conjunction at the end.
     * https://gist.github.com/angry-dan/e01b8712d6538510dd9c
     */
    public function natural_language_join(array $list, $conjunction = 'and') {
        $last = array_pop($list);
        if ($list) {
            return implode(', ', $list) . ' ' . $conjunction . ' ' . $last;
        }
        return $last;
    }
    public function getResappByResidencyTrack($residencyTypeId) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
        return $this->em->getRepository(ResidencyTrackList::class)->find($residencyTypeId);
    }

    public function getDefaultResidencyTrack() {
        $userSecUtil = $this->container->get('user_security_utility');
        $defaultResidencyTrack = $userSecUtil->getSiteSettingParameter('defaultResidencyTrack',$this->container->getParameter('resapp.sitename'));
        //echo "1defaultResidencyTrack=$defaultResidencyTrack <br>";
        if( !$defaultResidencyTrack ) {
            //$residencyTracks = $this->em->getRepository("AppUserdirectoryBundle:ResidencyTrackList")->findBy(array(), array('orderinlist' => 'ASC'));
            $residencyTracks = $this->em->getRepository(ResidencyTrackList::class)->findBy(array(), array('orderinlist' => 'ASC'));
            if( count($residencyTracks) > 0 ) {
                $defaultResidencyTrack = $residencyTracks[0];
            }
        }
        if( $defaultResidencyTrack ) {
            //echo "2defaultResidencyTrack=$defaultResidencyTrack <br>";
            return $defaultResidencyTrack->getName();
        }
        return NULL;
    }

    public function getEthnicities( $entity=NULL, $cycle=NULL ) {
//        $ethnicities = array(
//            "Black or African American" => "Black or African American",
//            "Hispanic or Latino" => "Hispanic or Latino",
//            "American Indian or Alaska Native" => "American Indian or Alaska Native",
//            "Native Hawaiian and other Pacific Islander" => "Native Hawaiian and other Pacific Islander",
//            "Unknown" => "Unknown",
//            "None" => "None"
//        );
        $ethnicities = array();
        foreach( $this->getDefaultEthnicitiesArray() as $ethnicity ) {
            $ethnicities[] = array($ethnicity => $ethnicity);
        }

        //add from $entity
        $ethnicityStr = $entity->getEthnicity();
        if( $ethnicityStr ) {
            $ethnicities[] = array($ethnicityStr => $ethnicityStr);
        }

        return $ethnicities;
    }
    public function getDefaultEthnicitiesArray() {
        $ethnicities = array(
            "Black or African American",
            "Hispanic or Latino",
            "American Indian or Alaska Native",
            "Native Hawaiian and other Pacific Islander",
            "Unknown",
            "None"
        );

        return $ethnicities;
    }

//    //Assume start year is 01-01 (unlike fellowship application, usually July 1st, academicYearStart in the site settings)
//    public function getDefaultStartDatesOld() {
//
//        $currentYear = intval(date("Y"));
//        $currentDate = new \DateTime();
//
//        //2011-03-26 (year-month-day)
//        $january1 = new \DateTime($currentYear."-01-01");
//        //$june30 = new \DateTime($currentYear."-06-30");
//        $july1 = new \DateTime($currentYear."-07-01");
//        $december31 = new \DateTime($currentYear."-12-31");
//
//        //default dates
//        $applicationSeasonStartDate = $currentYear;
//        $startDate = $currentYear + 1;
//
//        //Application Season Start Year (applicationSeasonStartDates) set to:
//        //current year if current date is between July 1st and December 31st (inclusive) or
//        //previous year (current year-1) if current date is between January 1st and June 30th (inclusive)
//        // 1January---(current year-1)---1July---(current year)---31December---
//
//        //Residency Start Year (startDates)
//        //next year (current year+1) if current date is between July 1st and December 31st (inclusive) or
//        //current year if current date is between January 1st and June 30th (inclusive)
//        // 1July---(current year+1)---31December---(current year)---30June---
//
//        //set "Application Season Start Year" to current year and "Residency Start Year" to next year if
//        // current date is between July 1st and December 31st (inclusive) or
//        if( $currentDate >= $july1 && $currentDate <= $december31 ) {
//            $applicationSeasonStartDate = $currentYear;
//            $startDate = $currentYear + 1;
//        }
//
//        //set "Application Season Start Year" to previous year and and "Residency Start Year" to current year if
//        // current date is between January 1st and June 30th (inclusive)
//        if( $currentDate >= $january1 && $currentDate < $july1 ) {
//            $applicationSeasonStartDate = $currentYear - 1;
//            $startDate = $currentYear;
//        }
//
//        $resArr['Application Season Start Year'] = $applicationSeasonStartDate;
//        $resArr['Application Season End Year'] = $applicationSeasonStartDate+1;
//
//        $resArr['Residency Start Year'] = $startDate;
//        $resArr['Residency End Year'] = $startDate+1;
//
//        return $resArr;
//    }
//    public function getDefaultStartDates_ORIG() {
//        $userServiceUtil = $this->container->get('user_service_utility');
//
//        $academicStartYear = $userServiceUtil->getDefaultAcademicStartYear();
//
//        $resArr['Application Season Start Year'] = $academicStartYear;
//        $resArr['Application Season End Year'] = $academicStartYear+1;
//
//        $resArr['Residency Start Year'] = $academicStartYear+1; //Application Season Start Year + 1 year
//        $resArr['Residency End Year'] = $academicStartYear+2;
//
//        return $resArr;
//    }
//    public function getResAppAcademicYearStartEndDates_ORIG( $currentYear=null, $formatStr="m/d/Y" ) {
//        //$userServiceUtil = $this->container->get('user_service_utility');
//        $resappUtil = $this->container->get('resapp_util');
//        //$startEndDates = $userServiceUtil->getAcademicYearStartEndDates($currentYear,true); //return dates as Date object
//        $startEndDates = $resappUtil->getResAppAcademicYearStartEndDates($currentYear,true); //return dates as Date object
//        $startDateObject = $startEndDates['startDate'];
//        $endDateObject = $startEndDates['endDate'];
//
//        $startDate = $startDateObject->format($formatStr);
//        $endDate = $endDateObject->format($formatStr);
//
//        $resArr['Season Start Date'] = $startDate;
//        $resArr['Season End Date'] = $endDate;
//
//        $startDateObject->add(new \DateInterval('P1Y')); //P1Y = +1 year
//        $endDateObject->add(new \DateInterval('P1Y'));
//        $residencyStartDate = $startDateObject->format($formatStr);
//        $residencyEndDate = $endDateObject->format($formatStr);
//
////        $startDateObjectPlusOne = clone $startDateObject;
////        $endDateObjectPlusOne = clone $endDateObject;
////        $startDateObjectPlusOne->modify('+1 year');
////        $endDateObjectPlusOne->modify('+1 year');
////        $residencyStartDate = $startDateObjectPlusOne->format($formatStr);
////        $residencyEndDate = $endDateObjectPlusOne->format($formatStr);
//
//        $resArr['Residency Start Date'] = $residencyStartDate; //Application Season Start Year + 1 year
//        $resArr['Residency End Date'] = $residencyEndDate;
//
//        return $resArr;
//    }

    //Renamed getDefaultStartDates to getDefaultStartYears
    public function getDefaultStartYear() {
        $userServiceUtil = $this->container->get('user_service_utility');
        $currentYear = NULL;

        //1) get current year based on site settings start date parameter
        $currentYear = $userServiceUtil->getDefaultAcademicStartYear('resapp','resappAcademicYearStart');

        if( !$currentYear ) {
            //2) get current year based on default start date
            $currentYear = $userServiceUtil->getDefaultAcademicStartYear();
        }

        if( !$currentYear ) {
            //2) get current year
            $currentYear = intval(date("Y"));
        }
        
        return $currentYear;

        //return $this->getStartYearsByYear($currentYear);

//        $resArr['Current Year'] = $currentYear;
//
//        $resArr['Application Season Start Year'] = $currentYear;
//        $resArr['Application Season End Year'] = $currentYear+1;
//
//        $resArr['Residency Start Year'] = $currentYear+1; //Application Season Start Year + 1 year
//        $resArr['Residency End Year'] = $currentYear+2;
//
//        return $resArr;
    }
    //$currentYears - might be comma separated multiple years
    //$clearResidencyYears - clear residency start/end years in case of multiple season years
    public function getStartYearsByYears( $currentYearsStr, $clearResidencyYears=true ) {
        $resArr = array();

        $yearsArr = explode(",",$currentYearsStr);

        $currentYearArr = array();
        $seasonStartYearArr = array();
        $seasonEndYearArr = array();
        $residencyStartYearArr = array();
        $residencyEndYearArr = array();

        foreach($yearsArr as $currentYear ) {
            $currentYearArr[] = $currentYear;

            $seasonStartYearArr[] = $currentYear;
            $seasonEndYearArr[] = $currentYear+1;

            $residencyStartYearArr[] = $currentYear+1; //Application Season Start Year + 1 year
            $residencyEndYearArr[] = $currentYear+2;
        }

//        $resArr['Current Year'] = $currentYear;
//        $resArr['Application Season Start Year'] = $currentYear;
//        $resArr['Application Season End Year'] = $currentYear+1;
//        $resArr['Residency Start Year'] = $currentYear+1; //Application Season Start Year + 1 year
//        $resArr['Residency End Year'] = $currentYear+2;

        $resArr['Current Year'] = implode(",",$currentYearArr);

        $resArr['Application Season Start Year'] = implode(",",$seasonStartYearArr);
        $resArr['Application Season End Year'] = implode(",",$seasonEndYearArr);

        if( $clearResidencyYears ) {
            if (count($currentYearArr) <= 1) {
                $resArr['Residency Start Year'] = implode(",", $residencyStartYearArr);
                $resArr['Residency End Year'] = implode(",", $residencyEndYearArr);
            } else {
                $resArr['Residency Start Year'] = NULL;
                $resArr['Residency End Year'] = NULL;
            }
        } else {
            $resArr['Residency Start Year'] = implode(",", $residencyStartYearArr);
            $resArr['Residency End Year'] = implode(",", $residencyEndYearArr);
        }

        return $resArr;
    }
    //$residencyTypes[id] = name;
    public function getStartYearsByResidencyTracks( $residencyTypes=NULL ) {
        $userServiceUtil = $this->container->get('user_service_utility');
        $currentYear = $this->getDefaultStartYear();
        $startDates = array();
        foreach($residencyTypes as $residencyId=>$residencyName) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $residencyType = $this->em->getRepository(ResidencyTrackList::class)->find($residencyId);
            $startDate = $residencyType->getSeasonYearStart();
            if( $startDate ) {
                //echo $residencyName.": startDate=".$startDate->format('d-m-Y')."<br>";
                $startYear = $userServiceUtil->getAcademicStartYear($startDate);
                $startDates[] = $startYear;
            } else {
                //echo $residencyName.": startDate=NULL"."<br>";
                $startDates[] = $currentYear;
            }
        }

        $startDates = array_unique($startDates);

        return $startDates;
    }
    //TODO: update as in fellapp getAcademicYearStartEndDates.
    //TODO: Replace all getAcademicYearStartEndDates in resapp by this getResAppAcademicYearStartEndDates
    //Get application season and residency start/end dates
    //$currentYear is Application Season Start Year (applicationSeasonStartDate) ()
    //Usually: $startDate = $applicationSeasonStartDate + 1 year:
    //2021 - Application Season Start Year (applicationSeasonStartDate), 20222 - Residency Start Year (startDate)
    public function getResAppAcademicYearStartEndDates( $currentYear=null, $formatStr="Y-m-d", $asDateTimeObject=false ) {
        //$userServiceUtil = $this->container->get('user_service_utility');

        //$startEndDates = $userServiceUtil->getAcademicYearStartEndDates($currentYear,true); //return dates as Date object
        $startEndDates = $this->getAcademicYearStartEndDates($currentYear,true); //return dates as Date object

        $startDateObject = $startEndDates['startDate'];
        $endDateObject = $startEndDates['endDate'];

        if( $asDateTimeObject ) {
            $resArr['Season Start Date'] = $startDateObject;
            $resArr['Season End Date'] = $endDateObject;

            //duplicate with legacy key from getAcademicYearStartEndDates 'startDate'
            $resArr['startDate'] = $startDateObject;
            $resArr['endDate'] = $endDateObject;
        } else {
            $startDate = $startDateObject->format($formatStr);
            $endDate = $endDateObject->format($formatStr);
            //echo "startDate=".$startDate."<br>";
            //echo "endDate=".$endDate."<br>";
            //exit('111');

            $resArr['Season Start Date'] = $startDate;
            $resArr['Season End Date'] = $endDate;

            //duplicate with legacy key from getAcademicYearStartEndDates 'startDate'
            $resArr['startDate'] = $startDate;
            $resArr['endDate'] = $endDate;
        }

        $startDateObject->add(new \DateInterval('P1Y')); //P1Y = +1 year
        $endDateObject->add(new \DateInterval('P1Y'));

        if( $asDateTimeObject ) {
            $resArr['Residency Start Date'] = $startDateObject; //Application Season Start Year + 1 year
            $resArr['Residency End Date'] = $endDateObject;
        } else {
            $residencyStartDate = $startDateObject->format($formatStr);
            $residencyEndDate = $endDateObject->format($formatStr);

            $resArr['Residency Start Date'] = $residencyStartDate; //Application Season Start Year + 1 year
            $resArr['Residency End Date'] = $residencyEndDate;
        }

        return $resArr;
    }
    //Get application start/end dates from resapp site setting or default site settings
    //$currentYear is Application Season Start Year (applicationSeasonStartDate)
    //$yearOffset: 0=>current year, -1=>previous year, +1=>next year
    //return format: Y-m-d
    public function getAcademicYearStartEndDates( $currentYear=NULL, $asDateTimeObject=false, $yearOffset=NULL ) {

        $userServiceUtil = $this->container->get('user_service_utility');

        //1) get start/end dates from resapp site settings
        $startEndDates = $userServiceUtil->getAcademicYearStartEndDates($currentYear,$asDateTimeObject,$yearOffset,'resapp','resappAcademicYearStart','resappAcademicYearEnd');

        $startDate = $startEndDates['startDate'];
        $endDate = $startEndDates['endDate'];

        //echo "startDate=".$startDate."<br>";
        //echo "endDate=".$endDate."<br>";

        if( $startDate == NULL || $endDate == NULL ) {
            //2) get start/end dates from default site settings
            $startEndDates = $userServiceUtil->getAcademicYearStartEndDates($currentYear,$asDateTimeObject,$yearOffset);

            if( $startDate == NULL ) {
                $startDate = $startEndDates['startDate'];
            }

            if( $endDate == NULL ) {
                $endDate = $startEndDates['endDate'];
            }

            if( $startDate == NULL || $endDate == NULL ) {
                $currentYear = intval(date("Y"));

                //3) If still missing, set to the default value to July 1st
                if( $startDate == NULL ) {
                    //$startDate = new \DateTime($currentYear."-07-01");
                    if( $asDateTimeObject ) {
                        $startDate = new \DateTime($currentYear."-07-01");
                    } else {
                        $startDate = $currentYear."-07-01";
                    }
                }

                //3) If still missing, set to the default value to June 30
                if( $endDate == NULL ) {
                    //$endDate = new \DateTime($currentYear."-06-30");
                    if( $asDateTimeObject ) {
                        $endDate = new \DateTime($currentYear . "-06-30");
                    } else {
                        $endDate = $currentYear . "-06-30";
                    }
                }
            }
        }

        return array(
            'startDate'=> $startDate,
            'endDate'=> $endDate,
        );
    }

    public function isResAppInterviewed( $resapp ) {
        //definition of the not interviewed applications
        //interviewed means she sets the interview date and then sends interview evaluation emails.
        //The simplest answer if "not interviewed" would be any applicant that if all those are true:
        // (a) was never set to the “Interviewee” status AND
        // (b) does not have any interview feedback AND
        // (c) does not have an interview date field value AND
        // (d) never had any interviewer evaluation emails sent to interviewers

        if( !$resapp ) {
            return false;
        }
        
        // (a) was never set to the “Interviewee” status AND
        // (b) does not have any interview feedback AND
        // (c) does not have an interview date field value AND
        if( $resapp->isInterviewed() ) {
            return true;
        }

        //(d) never had any interviewer evaluation emails sent to interviewers
        if( $this->isInterviewInvitationEmailSent($resapp) ) {
            return true;
        }

        return false;
    }

    public function isInterviewInvitationEmailSent($resapp) {
        //get the date from event log
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
        $dql = $repository->createQueryBuilder("logger");

        $dql->innerJoin('logger.eventType', 'eventType');
        $dql->where("logger.entityName = 'ResidencyApplication' AND logger.entityId = '".$resapp->getId()."'");

        //$dql->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");
        $dql->andWhere("eventType.name = :eventType OR logger.event LIKE :eventStr");

        //$dql->andWhere("logger.event LIKE :eventStr");

        $dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery();

        //$search = "Please review the FELLOWSHIP INTERVIEW SCHEDULE for the candidate";
        $search = "Invited interviewers to rate residency application ID";
        $eventType = "Residency Application Rating Invitation Emails Resent";
        $query->setParameters(
            array(
                'eventType' => $eventType,
                'eventStr' => '%'.$search.'%',
            )
        );

        $loggers = $query->getResult();

        if( count($loggers) > 0 ) {
            return true;
        }

        return false;
    }
    
} 