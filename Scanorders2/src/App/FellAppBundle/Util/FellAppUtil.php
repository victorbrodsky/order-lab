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

namespace Oleg\FellAppBundle\Util;


use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Oleg\FellAppBundle\Entity\DataFile;
use Oleg\FellAppBundle\Entity\Interview;
use Oleg\UserdirectoryBundle\Entity\AccessRequest;
use Oleg\UserdirectoryBundle\Entity\BoardCertification;
use Oleg\UserdirectoryBundle\Entity\Citizenship;
use Oleg\UserdirectoryBundle\Entity\Document;
use Oleg\UserdirectoryBundle\Entity\EmploymentStatus;
use Oleg\UserdirectoryBundle\Entity\Examination;
use Oleg\FellAppBundle\Entity\FellowshipApplication;
use Oleg\UserdirectoryBundle\Entity\GeoLocation;
use Oleg\UserdirectoryBundle\Entity\JobTitleList;
use Oleg\UserdirectoryBundle\Entity\Location;
use Oleg\FellAppBundle\Entity\Reference;
use Oleg\UserdirectoryBundle\Entity\Roles;
use Oleg\UserdirectoryBundle\Entity\StateLicense;
use Oleg\UserdirectoryBundle\Entity\Training;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Oleg\UserdirectoryBundle\Util\EmailUtil;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class FellAppUtil {

    protected $em;
    protected $container;

    protected $systemEmail;


    public function __construct( $em, $container ) {
        $this->em = $em;
        $this->container = $container;
    }



    //check for active access requests
    public function getActiveAccessReq() {
        if( !$this->container->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_COORDINATOR') ) {
            //exit('not granted ROLE_FELLAPP_COORDINATOR ???!!!'); //testing
            return null;
        } else {
            //exit('granted ROLE_FELLAPP_COORDINATOR !!!'); //testing
        }
        $userSecUtil = $this->container->get('user_security_utility');
        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->container->getParameter('fellapp.sitename'),AccessRequest::STATUS_ACTIVE);
        return $accessreqs;
    }


    //$fellSubspecArg: single fellowshipSubspecialty id or array of fellowshipSubspecialty ids
    //$year can be multiple dates "2019,2020,2021..."
    public function getFellAppByStatusAndYear($status,$fellSubspecArg,$year=null,$interviewer=null) {

        //echo "year=$year<br>";
        $repository = $this->em->getRepository('OlegFellAppBundle:FellowshipApplication');
        $dql =  $repository->createQueryBuilder("fellapp");
        $dql->select('fellapp');
        $dql->leftJoin("fellapp.appStatus", "appStatus");

        if( strpos($status, "-") !== false ) {
            $statusArr = explode("-", $status);
            $statusStr = $statusArr[0];
            $statusNot = $statusArr[1];
            if( $statusNot && $statusNot == 'not' ) {
                //'interviewee-not' is dummy status which is all statuses but not
                $dql->where("appStatus.name != '" . $statusStr . "'");
            }
        } else {
            $dql->where("appStatus.name = '" . $status . "'");
        }

        if( $fellSubspecArg ) {
            $dql->leftJoin("fellapp.fellowshipSubspecialty","fellowshipSubspecialty");
            if( is_array($fellSubspecArg) ) {
                $felltypeArr = array();
                foreach( $fellSubspecArg as $fellowshipTypeID => $fellowshipTypeName ) {
                    $felltypeArr[] = "fellowshipSubspecialty.id = ".$fellowshipTypeID;
                }
                $dql->andWhere( implode(" OR ", $felltypeArr) );
            } else {
                $dql->andWhere("fellowshipSubspecialty.id=".$fellSubspecArg);
            }
        }

        if( $year ) {
            if( strpos( $year, "," ) !== false) {
                //multiple years
                $yearArr = explode(",",$year);
                $criterions = array();
                foreach($yearArr as $singleYear) {
                    $bottomDate = $singleYear."-01-01";
                    $topDate = $singleYear."-12-31";
                    //echo "bottomDate=$bottomDate, topDate=$topDate <br>";
                    $criterions[] = "("."fellapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'".")";
                }
                $criterionStr = implode(" OR ",$criterions);
                $dql->andWhere($criterionStr);
            } else {
                //seingle year
                $bottomDate = $year."-01-01";
                $topDate = $year."-12-31";
                $dql->andWhere("fellapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'");
            }
        }

        if( $interviewer ) {
            $dql->leftJoin("fellapp.interviews", "interviews");
            $dql->leftJoin("interviews.interviewer", "interviewer");
            $dql->andWhere("interviewer.id=".$interviewer->getId());
        }

        //echo "dql=".$dql."<br>";

        $query = $this->em->createQuery($dql);
        $applicants = $query->getResult();
        
//        echo "applicants=".count($applicants)."<br>";
//        if( $status == 'active' ) {
//            foreach ($applicants as $fellapp) {
//                echo "ID " . $fellapp->getId() .
//                    "; startDate=" . $fellapp->getStartDate()->format('Y-m-d') .
//                    "; status=" . $fellapp->getAppStatus()->getName() .
//                    "; type=" . $fellapp->getFellowshipSubspecialty()->getName() .
//                    "<br>";
//            }
//        }

        return $applicants;
    }

//    public function getFellAppByUserAndStatusAndYear($subjectUser, $status,$fellSubspecId,$year=null) {
//
//        $repository = $this->em->getRepository('OlegFellAppBundle:FellowshipApplication');
//        $dql =  $repository->createQueryBuilder("fellapp");
//        $dql->select('fellapp');
//        $dql->leftJoin("fellapp.appStatus", "appStatus");
//        $dql->where("appStatus.name = '" . $status . "'");
//
//        if( $fellSubspecId ) {
//            $dql->leftJoin("fellapp.fellowshipSubspecialty","fellowshipSubspecialty");
//            $dql->andWhere("fellowshipSubspecialty.id=".$fellSubspecId);
//        }
//
//        if( $year ) {
//            $bottomDate = "01-01-".$year;
//            $topDate = "12-31-".$year;
//            $dql->andWhere("fellapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'" );
//        }
//
//        if( $subjectUser ) {
//            $dql->leftJoin("fellapp.interviews", "interviews");
//            $dql->andWhere("interviews.interviewer=".$subjectUser);
//        }
//
//        $query = $this->em->createQuery($dql);
//        $applicants = $query->getResult();
//
//        return $applicants;
//    }

    //get fellowship types based on the user roles
    public function getFellowshipTypesByUser( $user ) {
        $em = $this->em;
        $userSecUtil = $this->container->get('user_security_utility');

        if( $userSecUtil->hasGlobalUserRole( "ROLE_FELLAPP_ADMIN", $user ) ) {
            return $this->getFellowshipTypesByInstitution(false);
        }

        $filterTypes = array();
        //$filterTypeIds = array();

        foreach( $user->getRoles() as $rolename ) {
            $roleObject = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($rolename);
            if( $roleObject ) {
                $fellowshipSubspecialty = $roleObject->getFellowshipSubspecialty();
                if( $fellowshipSubspecialty ) {
                    $filterTypes[$fellowshipSubspecialty->getId()] = $fellowshipSubspecialty->getName();
                    //$filterTypeIds[] = $fellowshipSubspecialty->getId();
                }
            }
        }

//        if( count($filterTypes) > 1 ) {
//            $filterTypes[implode(";",$filterTypeIds)] = "ALL";
//        }

        //$filterTypes = array_reverse($filterTypes);

        return $filterTypes;
    }

    //get all fellowship application types (with WCMC Pathology) using role
    public function getFellowshipTypesByInstitution( $asEntities=false ) {
        $em = $this->em;

        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution'
        );

        $wcmc = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
        //exit("wcm=".$wcmc);
        $pathology = $em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
            "Pathology and Laboratory Medicine",
            $wcmc,
            $mapper
        );

        //get list of fellowship type with extra "ALL"
        $repository = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty');
        $dql = $repository->createQueryBuilder('list');
        $dql->leftJoin("list.institution","institution");
        $dql->where("institution.id = ".$pathology->getId());
        $dql->orderBy("list.orderinlist","ASC");

        $query = $em->createQuery($dql);

        $fellTypes = $query->getResult();
        //echo "fellTypes count=".count($fellTypes)."<br>";

        if( $asEntities ) {
            return $fellTypes;
        }

        //add statuses
        foreach( $fellTypes as $type ) {
            //echo "type: id=".$type->getId().", name=".$type->getName()."<br>";
            $filterType[$type->getId()] = $type->getName();
        }

        return $filterType;
    }

    //get all fellowship visa status
    public function getFellowshipVisaStatuses( $asEntities=false ) {
        $em = $this->em;

        $repository = $em->getRepository('OlegFellAppBundle:VisaStatus');
        $dql = $repository->createQueryBuilder('list');

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->orderBy("list.orderinlist","ASC");

        $query = $em->createQuery($dql);

        $query->setParameters( array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
        ));

        $fellTypes = $query->getResult();
        //echo "fellTypes count=".count($fellTypes)."<br>";

        if( $asEntities ) {
            return $fellTypes;
        }

        //add statuses
        foreach( $fellTypes as $type ) {
            //echo "type: id=".$type->getId().", name=".$type->getName()."<br>";
            $filterType[$type->getId()] = $type->getName();
        }

        return $filterType;
    }

//    public function getFellowshipTypesWithSpecials_OLD() {
//        $em = $this->em;
//
//        //get list of fellowship type with extra "ALL"
//        $repository = $em->getRepository('OlegUserdirectoryBundle:FellowshipSubspecialty');
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
//        $fellTypes = $query->getResult();
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
//        foreach( $fellTypes as $type ) {
//            //echo "type: id=".$status->getId().", name=".$status->getName()."<br>";
//            $filterType[$type->getId()] = $type->getName();
//        }
//
//        return $filterType;
//    }


    //check if the user can view this fellapp application: user is Observers/Interviewers or hasSameFellowshipTypeId
    public function hasFellappPermission( $user, $fellapp ) {

        //$res = false;

        $userSecUtil = $this->container->get('user_security_utility');
        if( $userSecUtil->hasGlobalUserRole( "ROLE_FELLAPP_ADMIN", $user ) ) {
            return true;
        }

        //if user is observer of this fellapp
        if( $fellapp->getObservers()->contains($user) ) {
            return true;
        }

        //if user is interviewer of this fellapp
        //if( $fellapp->getInterviews()->contains($user) ) {
        if( $fellapp->getInterviewByUser($user) ) {
            return true;
        }

        //echo "res=".$res."<br>";

        //if user has the same fellapp type as this fellapp
        if( $fellapp->getFellowshipSubspecialty() && $this->hasSameFellowshipTypeId($user, $fellapp->getFellowshipSubspecialty()->getId()) ) {
            return true;
        }

        //echo "res=".$res."<br>";
        //exit('1');

        return false;
    }

    //check fellowship types based on the user roles
    public function hasSameFellowshipTypeId( $user, $felltypeid ) {
        $em = $this->em;
        $userSecUtil = $this->container->get('user_security_utility');

        if( $userSecUtil->hasGlobalUserRole( "ROLE_FELLAPP_ADMIN", $user ) ) {
            return true;
        }

        //echo "felltypeid=".$felltypeid."<br>";

        foreach( $user->getRoles() as $rolename ) {
            $roleObject = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($rolename);
            if( $roleObject ) {
                $fellowshipSubspecialty = $roleObject->getFellowshipSubspecialty();
                if( $fellowshipSubspecialty ) {
                    if( $felltypeid == $fellowshipSubspecialty->getId() ) {
                        //it is safer to check also for fellowshipSubspecialty's institution is under roleObject's institution
                        if( $em->getRepository('OlegUserdirectoryBundle:Institution')->isNodeUnderParentnode( $roleObject->getInstitution(), $fellowshipSubspecialty->getInstitution() ) ) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    //get based on roles
    public function getCoordinatorsOfFellAppEmails($fellapp) {
        return $this->getEmailsOfFellApp( $fellapp, "_COORDINATOR_" );
    }
    //get based on roles
    public function getDirectorsOfFellAppEmails($fellapp) {
        return $this->getEmailsOfFellApp( $fellapp, "_DIRECTOR_" );
    }
    //get based on roles
    public function getCoordinatorsOfFellApp( $fellapp ) {
        return $this->getUsersOfFellAppByRole( $fellapp, "_COORDINATOR_" );
    }
    //get based on roles
    public function getDirectorsOfFellApp( $fellapp ) {
        return $this->getUsersOfFellAppByRole( $fellapp, "_DIRECTOR_" );
    }

    //get coordinator of given fellapp
    public function getUsersOfFellAppByRole( $fellapp, $roleName ) {

        if( !$fellapp ) {
            return null;
        }

        //$em = $this->em;

        $fellowshipSubspecialty = $fellapp->getFellowshipSubspecialty();
        //echo "fellowshipSubspecialty=".$fellowshipSubspecialty."<br>";

        if( !$fellowshipSubspecialty ) {
            return null;
        }

        return $this->getUsersOfFellowshipSubspecialtyByRole($fellowshipSubspecialty,$roleName);

//        $coordinatorFellTypeRole = null;
//
//        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findByFellowshipSubspecialty($fellowshipSubspecialty);
//        foreach( $roles as $role ) {
//            if( strpos($role,$roleName) !== false ) {
//                $coordinatorFellTypeRole = $role;
//                break;
//            }
//        }
//
//        $users = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($coordinatorFellTypeRole);
//
//        return $users;
    }
    public function getUsersOfFellowshipSubspecialtyByRole( $fellowshipSubspecialty, $roleName ) {

        if( !$fellowshipSubspecialty ) {
            return null;
        }

//        $coordinatorFellTypeRole = null;
//        $roles = $this->em->getRepository('OlegUserdirectoryBundle:Roles')->findByFellowshipSubspecialty($fellowshipSubspecialty);
//        foreach( $roles as $role ) {
//            if( strpos($role,$roleName) !== false ) {
//                $coordinatorFellTypeRole = $role;
//                break;
//            }
//        }
        $coordinatorFellTypeRole = $this->getRoleByFellowshipSubspecialtyAndRolename($fellowshipSubspecialty,$roleName );

        $users = $this->em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($coordinatorFellTypeRole);

        return $users;
    }
    public function getRoleByFellowshipSubspecialtyAndRolename( $fellowshipSubspecialty, $roleName ) {
        $roles = $this->em->getRepository('OlegUserdirectoryBundle:Roles')->findByFellowshipSubspecialty($fellowshipSubspecialty);
        foreach( $roles as $role ) {
            if( strpos($role,$roleName) !== false ) {
                return $role;
                break;
            }
        }

        return null;
    }

    public function getEmailsOfFellApp( $fellapp, $roleName ) {

        $users = $this->getUsersOfFellAppByRole( $fellapp, $roleName );

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

    //send confirmation email to the corresponding Fellowship director and coordinator
    public function sendConfirmationEmailsOnApplicationPopulation( $fellowshipApplication, $applicant ) {
        $fellappUtil = $this->container->get('fellapp_util');
        $logger = $this->container->get('logger');

        $directorEmails = $fellappUtil->getDirectorsOfFellAppEmails($fellowshipApplication);
        $coordinatorEmails = $fellappUtil->getCoordinatorsOfFellAppEmails($fellowshipApplication);
        $responsibleEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));
        $logger->notice("Send confirmation email (fellowship application ".$fellowshipApplication->getId()." populated in DB) to the directors and coordinators emails " . implode(", ",$responsibleEmails));

        //[FellowshipType Fellowship] FirstNameOfApplicant LastNameOfApplicant's application received
        $populatedSubjectFellApp = "[".$fellowshipApplication->getFellowshipSubspecialty()." Fellowship] ".$applicant->getUsernameShortest()."'s application received";

        /////////////// Configuring the Request Context per Command ///////////////
        // http://symfony.com/doc/current/cookbook/console/request_context.html
        //replace by $router = $userSecUtil->getRequestContextRouter();
        $userSecUtil = $this->container->get('user_security_utility');
        $liveSiteRootUrl = $userSecUtil->getSiteSettingParameter('liveSiteRootUrl');    //http://c.med.cornell.edu/order/
        $liveSiteHost = parse_url($liveSiteRootUrl, PHP_URL_HOST); //c.med.cornell.edu
        //echo "liveSiteHost=".$liveSiteHost."; ";

        $connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
        if( !$connectionChannel ) {
            $connectionChannel = 'http';
        }

        $context = $this->container->get('router')->getContext();
        $context->setHost($liveSiteHost);
        $context->setScheme($connectionChannel);
        $context->setBaseUrl('/order');
        /////////////// EOF Configuring the Request Context per Command ///////////////

        //FirstNameOfApplicant LastNameOfApplicant has submitted a new application to your FellowshipType StartDate'sYear(suchAs2018) fellowship
        // on SubmissionDate and you can access it here: LinkToGeneratedApplicantPDF.
        //To mark this application as priority, please click the following link and log in if prompted:
        //LinkToChangeStatusOfApplicationToPriority
        $linkToGeneratedApplicantPDF = $this->container->get('router')->generate(
            'fellapp_view_pdf',
            array(
                'id' => $fellowshipApplication->getId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $linkToGeneratedApplicantPDF = $this->convertToHref($linkToGeneratedApplicantPDF);
        //echo "linkToGeneratedApplicantPDF=".$linkToGeneratedApplicantPDF."; ";

        $linkToChangeStatusOfApplicationToPriority = $this->container->get('router')->generate(
            'fellapp_status_email',
            array(
                'id' => $fellowshipApplication->getId(),
                'status' => 'priority'
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $linkToChangeStatusOfApplicationToPriority = $this->convertToHref($linkToChangeStatusOfApplicationToPriority);

        $linkToList = $this->container->get('router')->generate(
            'fellapp_home',
            array(
                'filter[startDate]' => $fellowshipApplication->getStartDate()->format('Y'), //2018
                'filter[filter]' => $fellowshipApplication->getFellowshipSubspecialty()->getId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $linkToList = $this->convertToHref($linkToList);

        $creationDate = $fellowshipApplication->getCreatedate();
        $creationDate->setTimezone(new \DateTimeZone('America/New_York'));
        $creationDateStr = $creationDate->format('m/d/Y h:i A T');

        //$break = "\r\n";
        $break = "<br>";
        $populatedBodyFellApp = $applicant->getUsernameShortest()." has submitted a new application to your ".$fellowshipApplication->getFellowshipSubspecialty().
            " ".$fellowshipApplication->getStartDate()->format('Y')."'s fellowship on ".$creationDateStr.
            " and you can access it here: ".$break.$linkToGeneratedApplicantPDF;
        $populatedBodyFellApp .= $break.$break."To mark this application as priority, please click the following link and log in if prompted:".
            $break.$linkToChangeStatusOfApplicationToPriority;

        //To view the list of all received FellowshipType FellowshipYear applications, please follow this link:
        $populatedBodyFellApp .= $break.$break."To view the list of all received ".
            $fellowshipApplication->getFellowshipSubspecialty()." ".$fellowshipApplication->getStartDate()->format('Y')." applications, please follow this link:".$break;
        $populatedBodyFellApp .= $linkToList;

        //If you are off site, please connect via VPN first ( https://its.weill.cornell.edu/services/wifi-networks/vpn ) and then follow the links above.
        $populatedBodyFellApp .= $break.$break."If you are off site, please connect via VPN first (https://webvpn.med.cornell.edu/) and then follow the links above.";

        $emailUtil = $this->container->get('user_mailer_utility');
        $emailUtil->sendEmail( $responsibleEmails, $populatedSubjectFellApp, $populatedBodyFellApp );
    }

    public function convertToHref($url) {
        return '<a href="'.$url.'">'.$url.'</a>';
    }
    
    //add based on interviewers in FellowshipSubspecialty object
    //TODO: rewrite and test add default interviewers based on roles and discard interviewers, coordinator, directors in FellowshipSubspecialty object?
    public function addDefaultInterviewers( $fellapp ) {

        $fellowshipSubspecialty = $fellapp->getFellowshipSubspecialty();

        foreach( $fellowshipSubspecialty->getInterviewers() as $interviewer ) {

            if( $this->isInterviewerExist($fellapp,$interviewer) == false ) {
                $interview = new Interview();
                $interview->setInterviewer($interviewer);
                $interview->setLocation($interviewer->getMainLocation());
                $interview->setInterviewDate($fellapp->getInterviewDate());
                $fellapp->addInterview($interview);
            }

        }

    }

    public function isInterviewerExist( $fellapp, $interviewer ) {
        foreach( $fellapp->getInterviews() as $interview ) {
            if( $interview->getInterviewer()->getId() == $interviewer->getId() ) {
                return true;
            }
        }
        return false;
    }

    





    public function addEmptyFellAppFields($fellowshipApplication) {

        $em = $this->em;
        //$userSecUtil = $this->container->get('user_security_utility');
        //$systemUser = $userSecUtil->findSystemUser();
        $user = $fellowshipApplication->getUser();
        $author = $this->container->get('security.token_storage')->getToken()->getUser();

        //Pathology Fellowship Applicant in EmploymentStatus
        $employmentType = $em->getRepository('OlegUserdirectoryBundle:EmploymentType')->findOneByName("Pathology Fellowship Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Fellowship Applicant");
        }
        if( count($user->getEmploymentStatus()) == 0 ) {
            $employmentStatus = new EmploymentStatus($author);
            $employmentStatus->setEmploymentType($employmentType);
            $user->addEmploymentStatus($employmentStatus);
        }

        //locations
        $this->addEmptyLocations($fellowshipApplication);

        //Education
        $this->addEmptyTrainings($fellowshipApplication);

        //National Boards: oleg_fellappbundle_fellowshipapplication_examinations_0_USMLEStep1DatePassed
        $this->addEmptyNationalBoards($fellowshipApplication);

        //Medical Licensure: oleg_fellappbundle_fellowshipapplication[stateLicenses][0][licenseNumber]
        $this->addEmptyStateLicenses($fellowshipApplication);

        //Board Certification
        $this->addEmptyBoardCertifications($fellowshipApplication);

        //References
        $this->addEmptyReferences($fellowshipApplication);

    }


    //oleg_fellappbundle_fellowshipapplication_references_0_name
    public function addEmptyReferences($fellowshipApplication) {

        $author = $this->container->get('security.token_storage')->getToken()->getUser();
        $references = $fellowshipApplication->getReferences();
        $count = count($references);

        //must be 4
        //Remove the fourth letter of recommendation from the front end application form => 3 references
        for( $count; $count < 3; $count++  ) {

            $reference = new Reference($author);
            $fellowshipApplication->addReference($reference);

        }

    }

    public function addEmptyBoardCertifications($fellowshipApplication) {

        $author = $this->container->get('security.token_storage')->getToken()->getUser();
        $boardCertifications = $fellowshipApplication->getBoardCertifications();
        $count = count($boardCertifications);

        //must be 3
        for( $count; $count < 3; $count++  ) {

            $boardCertification = new BoardCertification($author);
            $fellowshipApplication->addBoardCertification($boardCertification);
            $fellowshipApplication->getUser()->getCredentials()->addBoardCertification($boardCertification);

        }

    }

    //oleg_fellappbundle_fellowshipapplication[stateLicenses][0][licenseNumber]
    public function addEmptyStateLicenses($fellowshipApplication) {

        $author = $this->container->get('security.token_storage')->getToken()->getUser();

        $stateLicenses = $fellowshipApplication->getStateLicenses();

        $count = count($stateLicenses);

        //must be 2
        for( $count; $count < 2; $count++  ) {

            $license = new StateLicense($author);
            $fellowshipApplication->addStateLicense($license);
            $fellowshipApplication->getUser()->getCredentials()->addStateLicense($license);

        }

    }

    public function addEmptyNationalBoards($fellowshipApplication) {

        $author = $this->container->get('security.token_storage')->getToken()->getUser();

        $examinations = $fellowshipApplication->getExaminations();

        if( count($examinations) == 0 ) {
            $examination = new Examination($author);
            $fellowshipApplication->addExamination($examination);
        } else {
            //$examination = $examinations[0];
        }

    }


    public function addEmptyLocations($fellowshipApplication) {

        $this->addLocationByType($fellowshipApplication,"Present Address");
        $this->addLocationByType($fellowshipApplication,"Permanent Address");
        $this->addLocationByType($fellowshipApplication,"Work Address");

    }
    public function addLocationByType($fellowshipApplication,$typeName) {

        $user = $fellowshipApplication->getUser();

        $specificLocation = null;

        foreach( $user->getLocations() as $location ) {
            if( $location->hasLocationTypeName($typeName) ) {
                $specificLocation = $location;
                break;
            }
        }

        if( !$specificLocation ) {

            $locationType = $this->em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName($typeName);
            if( !$locationType ) {
                throw new EntityNotFoundException('Unable to find entity by name='.$typeName);
            }

            $specificLocation = new Location();
            $specificLocation->setName('Fellowship Applicant '.$typeName);
            $specificLocation->addLocationType($locationType);
            $user->addLocation($specificLocation);
            $fellowshipApplication->addLocation($specificLocation);
        }

    }

    public function addEmptyTrainings($fellowshipApplication) {

        //set TrainingType
        $this->addTrainingByType($fellowshipApplication,"Undergraduate",1);
        $this->addTrainingByType($fellowshipApplication,"Graduate",2);
        $this->addTrainingByType($fellowshipApplication,"Medical",3);
        $this->addTrainingByType($fellowshipApplication,"Residency",4);
        $this->addTrainingByType($fellowshipApplication,"Post-Residency Fellowship",5);

        $maxNumber = 1;
        $this->addTrainingByType($fellowshipApplication,"GME",6,$maxNumber);
        //$this->addTrainingByType($fellowshipApplication,"GME",6,$maxNumber);

        $maxNumber = 3;
        $this->addTrainingByType($fellowshipApplication,"Other",7,$maxNumber);
        //$this->addTrainingByType($fellowshipApplication,"Other",8,$maxNumber);
        //$this->addTrainingByType($fellowshipApplication,"Other",9,$maxNumber);

    }
    public function addTrainingByType($fellowshipApplication,$typeName,$orderinlist,$maxNumber=1) {

        $user = $fellowshipApplication->getUser();

        $specificTraining = null;

        $trainings = $user->getTrainings();

        $count = 0;

        foreach( $trainings as $training ) {
            if( $training->getTrainingType()->getName()."" == $typeName ) {
                $count++;
            }
        }

        //add up to maxNumber
        for( $count; $count < $maxNumber; $count++ ) {
            //echo "maxNumber=".$maxNumber.", count=".$count."<br>";
            $this->addSingleTraining($fellowshipApplication,$typeName,$orderinlist);
        }

    }
    public function addSingleTraining($fellowshipApplication,$typeName,$orderinlist) {

        //echo "!!!!!!!!!! add single training with type=".$typeName."<br>";

        $author = $this->container->get('security.token_storage')->getToken()->getUser();
        $training = new Training($author);
        $training->setOrderinlist($orderinlist);

        $trainingType = $this->em->getRepository('OlegUserdirectoryBundle:TrainingTypeList')->findOneByName($typeName);
        $training->setTrainingType($trainingType);

        //s2id_oleg_fellappbundle_fellowshipapplication_trainings_1_jobTitle
        if( $typeName == 'Other' ) {
            //otherExperience1Name => jobTitle
            //if( !$training->getJobTitle() ) {
                $jobTitleEntity = new JobTitleList();
                $training->setJobTitle($jobTitleEntity);
            //}
        }

        $fellowshipApplication->addTraining($training);
        $fellowshipApplication->getUser()->addTraining($training);

    }


    public function createApplicantListExcel( $fellappids ) {
        
        $author = $this->container->get('security.token_storage')->getToken()->getUser();
        $transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');
        
        $ea = new Spreadsheet(); // ea is short for Excel Application
               
        $ea->getProperties()
            ->setCreator($author."")
            ->setTitle('Fellowship Applicants')
            ->setLastModifiedBy($author."")
            ->setDescription('Fellowship Applicants list in Excel format')
            ->setSubject('PHP Excel manipulation')
            ->setKeywords('excel php office phpexcel lakers')
            ->setCategory('programming')
            ;
        
        $ews = $ea->getSheet(0);
        $ews->setTitle('Fellowship Applicants');
        
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
        $ews->setCellValue('L1', 'Academic Rank');
        $ews->setCellValue('M1', 'Personality Rank');
        $ews->setCellValue('N1', 'Potential Rank');
        $ews->setCellValue('O1', 'Total Rank');
        $ews->setCellValue('P1', 'Language Proficiency');
        $ews->setCellValue('Q1', 'Comments');
        

        
        $row = 2;
        foreach( explode("-",$fellappids) as $fellappId ) {
        
            $fellapp = $this->em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($fellappId);
            if( !$fellapp ) {
                continue;
            }
            
            //check if author can have access to view this applicant
            //user who has the same fell type can view or edit
            if( $this->hasFellappPermission($author,$fellapp) == false ) {
                continue; //skip this applicant because the current user does not permission to view this applicant
            }
            
            $ews->setCellValue('A'.$row, $fellapp->getId());  
            $ews->setCellValue('B'.$row, $fellapp->getUser()->getFirstNameUppercase());
            $ews->setCellValue('C'.$row, $fellapp->getUser()->getLastNameUppercase());
            
            //Medical Degree
            $ews->setCellValue('D'.$row, $fellapp->getDegreeByTrainingTypeName('Medical'));
            
            //Medical School
            $ews->setCellValue('E'.$row, $fellapp->getSchoolByTrainingTypeName('Medical'));
            
            //Residency Institution
            $ews->setCellValue('F'.$row, $fellapp->getSchoolByTrainingTypeName('Residency'));
            
            //References
            $ews->setCellValue('G'.$row, $fellapp->getAllReferences());
            
            //Interview Score
            $totalScore = "";
            if( $fellapp->getInterviewScore() ) {
                $totalScore = $fellapp->getInterviewScore();
            }
            $ews->setCellValue('H'.$row, $totalScore );
	       
            //Interview Date                   
            $ews->setCellValue('I'.$row, $transformer->transform($fellapp->getInterviewDate()));
            
            $allTotalRanks = 0;
            
            foreach( $fellapp->getInterviews() as $interview ) {
            
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
                
                //Comments
                $ews->setCellValue('Q'.$row, $interview->getComment());   
                
                $row++;
            
            } //for each interview
            
            //space in case if there is no interviewers 
            if( count($fellapp->getInterviews()) == 0 ) {
                $row++;
            }
            
            //All Total Ranks:           
            $ews->setCellValue('A'.$row, "All Total Ranks:");
            $ews->setCellValue('B'.$row, $allTotalRanks);
            
            //Avg Rank:
            $row++;
            $ews->setCellValue('A'.$row, "Avg Rank:");
            $ews->setCellValue('B'.$row, $totalScore);
            
            $row = $row + 2;
        }
        
        //exit("ids=".$fellappids);
        
        
        // Auto size columns for each worksheet
        //\PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        foreach ($ea->getWorksheetIterator() as $worksheet) {

            $ea->setActiveSheetIndex($ea->getIndex($worksheet));

            $sheet = $ea->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            /** @var PHPExcel_Cell $cell */
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }
               
        
        return $ea;
    }
    public function createApplicantListExcelSpout( $fellappids, $fileName ) {

        $author = $this->container->get('security.token_storage')->getToken()->getUser();
        $transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');

        $writer = WriterFactory::create(Type::XLSX);
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
        $writer->addRowWithStyle(
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
                'Academic Rank',                //12 - M
                'Personality Rank',             //13 - N
                'Potential Rank',               //14 - O
                'Total Rank',                   //15 - P
                'Language Proficiency',         //16 - Q
                'Comments',                     //17 - R
            ],
            $headerStyle
        );

        //$row = 2;

        foreach( explode("-",$fellappids) as $fellappId ) {

            $fellapp = $this->em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($fellappId);
            if( !$fellapp ) {
                continue;
            }

            //check if author can have access to view this applicant
            //user who has the same fell type can view or edit
            if( $this->hasFellappPermission($author,$fellapp) == false ) {
                continue; //skip this applicant because the current user does not permission to view this applicant
            }

            $data = array();

            //$ews->setCellValue('A'.$row, $fellapp->getId());
            $data[0] = $fellapp->getId();

            //$ews->setCellValue('B'.$row, $fellapp->getUser()->getFirstNameUppercase());
            $data[1] = $fellapp->getUser()->getFirstNameUppercase();

            //$ews->setCellValue('C'.$row, $fellapp->getUser()->getLastNameUppercase());
            $data[2] = $fellapp->getUser()->getLastNameUppercase();

            $startDate = $fellapp->getStartDate();
            if( $startDate ) {
                $data[3] = $startDate->format('Y');
            }

            //Medical Degree
            //$ews->setCellValue('D'.$row, $fellapp->getDegreeByTrainingTypeName('Medical'));
            $data[4] = $fellapp->getDegreeByTrainingTypeName('Medical');

            //Medical School
            //$ews->setCellValue('E'.$row, $fellapp->getSchoolByTrainingTypeName('Medical'));
            $data[5] = $fellapp->getSchoolByTrainingTypeName('Medical');

            //Residency Institution
            //$ews->setCellValue('F'.$row, $fellapp->getSchoolByTrainingTypeName('Residency'));
            $data[6] = $fellapp->getSchoolByTrainingTypeName('Residency');

            //References
            //$ews->setCellValue('G'.$row, $fellapp->getAllReferences());
            $data[7] = $fellapp->getAllReferences();

                //Interview Score
            $totalScore = "";
            if( $fellapp->getInterviewScore() ) {
                $totalScore = $fellapp->getInterviewScore();
            }
            //$ews->setCellValue('H'.$row, $totalScore );
            $data[8] = $totalScore;

            //Interview Date
            //$ews->setCellValue('I'.$row, $transformer->transform($fellapp->getInterviewDate()));
            $data[9] = $transformer->transform($fellapp->getInterviewDate());

            $writer->addRowWithStyle($data,$requestStyle);

            $allTotalRanks = 0;
            $interviewers = $fellapp->getInterviews();

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

                //Comments
                //$ews->setCellValue('Q'.$row, $interview->getComment());
                $data[17] = $interview->getComment();

                $writer->addRowWithStyle($data,$requestStyle);

            } //for each interview

            //space in case if there is no interviewers
            if( count($fellapp->getInterviews()) == 0 ) {
                //$row++;
            }

            if( count($interviewers) == 0 ) {
                $allTotalRanks = "N/A";
                $totalScore = "N/A";
            }

            $data = array();

            //All Total Ranks:
            //$ews->setCellValue('A'.$row, "All Total Ranks:");
            $data[0] = "All Total Ranks:";

            //$ews->setCellValue('B'.$row, $allTotalRanks);
            $data[1] = $allTotalRanks;

            $writer->addRowWithStyle($data, $footerStyle);

            //Avg Rank:
            $data = array();
            //$row++;
            //$ews->setCellValue('A'.$row, "Avg Rank:");
            $data[0] = "Avg Rank:";
            //$ews->setCellValue('B'.$row, $totalScore);
            $data[1] = $totalScore;
            $writer->addRowWithStyle($data, $footerStyle);


            //$row = $row + 2;
        }

        $writer->close();
    }


    public function createInterviewApplicantList( $fellappids ) {

        $author = $this->container->get('security.token_storage')->getToken()->getUser();

        $fellapps = array();

        foreach( explode("-",$fellappids) as $fellappId ) {

            $fellapp = $this->em->getRepository('OlegFellAppBundle:FellowshipApplication')->find($fellappId);
            if( !$fellapp ) {
                continue;
            }

            //check if author can have access to view this applicant
            //user who has the same fell type can view or edit
            if( $this->hasFellappPermission($author,$fellapp) == false ) {
                continue; //skip this applicant because the current user does not permission to view this applicant
            }

            //only include the people who have an interview date (not the status of the interviewer)
            if( !$fellapp->getInterviewDate() ) {
                continue;
            }

            $fellapps[] = $fellapp;
        }

        //exit("ids=".$fellappids);
        return $fellapps;
    }


    //$roleType: string (INTERVIEWER, COORDINATOR, DIRECTOR)
    //name: ROLE_FELLAPP_DIRECTOR_WCM_BREASTPATHOLOGY
    //alias: Fellowship Program Interviewer WCMC Breast Pathology
    //Description: Access to specific Fellowship Application type as Interviewer
    //site: fellapp
    //Institution: WCMC
    //FellowshipSubspecialty: Breast Pathology
    //Permissions: Create a New Fellowship Application, Modify a Fellowship Application, Submit an interview evaluation
    public function createOrEnableFellAppRole( $subspecialtyType, $roleType, $institution, $testing=false ) {
        $em = $this->em;
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $userSecUtil = $this->container->get('user_security_utility');
        $site = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation('fellapp');

        $count = 0;

        //1) name: ROLE_FELLAPP_DIRECTOR_WCM_BREASTPATHOLOGY
        //get ROLE NAME: Pathology Informatics => PATHOLOGYINFORMATCS
        $roleNameBase = str_replace(" ","",$subspecialtyType->getName());
        $roleNameBase = strtoupper($roleNameBase);
        //echo "roleNameBase=$roleNameBase<br>";

        //create Director role
        $roleName = "ROLE_FELLAPP_".$roleType."_WCM_".$roleNameBase;
        //echo "roleName=$roleName<br>";
        $role = $em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($roleName);

        if( !$role ) {
            $roleTypeStr = ucfirst(strtolower($roleType));
            //exit('1: '.$roleTypeStr);

            $role = new Roles();
            $role = $userSecUtil->setDefaultList($role, null, $user, $roleName);
            $role->setAlias('Fellowship Program '.$roleTypeStr.' WCM ' . $subspecialtyType->getName());
            $role->setDescription('Access to specific Fellowship Application type as '.$roleTypeStr);
            $role->addSite($site);
            $role->setInstitution($institution);
            $role->setFellowshipSubspecialty($subspecialtyType);

            if( $roleType == "INTERVIEWER" ) {
                $role->setLevel(30);
                $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Submit an interview evaluation","Interview","create");
            }

            if( $roleType == "COORDINATOR" ) {
                $role->setLevel(40);
                $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Create a New Fellowship Application","FellowshipApplication","create");
                $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Modify a Fellowship Application","FellowshipApplication","update");
            }

            if( $roleType == "DIRECTOR" ) {
                $role->setLevel(50);
                $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Create a New Fellowship Application","FellowshipApplication","create");
                $count = $count + $userSecUtil->checkAndAddPermissionToRole($role,"Modify a Fellowship Application","FellowshipApplication","update");
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

    //TODO: add this function to user's profile create/update. Maybe, find a more efficient way to sync (if user's role with fellapp changed).
    //When the role (i.e. coordinator) is added by editing the user's profile directly, this FellowshipSubspecialty object is not updated.
    //Synchronise the FellowshipSubspecialty's $coordinators, $directors, $interviewers with the user profiles based on the specific roles:
    //get all users with specific coordinator role and add them (if not added) to the $coordinators in the FellowshipSubspecialty object
    public function synchroniseFellowshipSubspecialtyAndProfileRoles( $fellowshipTypes ) {
        //return null; //testing
        //echo "sync FellowshipSubspecialty count=".count($fellowshipTypes)."<br>";
        //iterate over all FellowshipSubspecialty objects
        foreach( $fellowshipTypes as $fellowshipSubspecialty ) {
            //$fellowshipType - Pain Medicine => ROLE_FELLAPP_DIRECTOR_WCM_PAINMEDICINE
            $this->synchroniseSingleFellowshipSubspecialtyAndProfileRoles($fellowshipSubspecialty,"_COORDINATOR_");
            $this->synchroniseSingleFellowshipSubspecialtyAndProfileRoles($fellowshipSubspecialty,"_DIRECTOR_");
            $this->synchroniseSingleFellowshipSubspecialtyAndProfileRoles($fellowshipSubspecialty,"_INTERVIEWER_");
        }
    }
    public function synchroniseSingleFellowshipSubspecialtyAndProfileRoles( $fellowshipSubspecialty, $roleName ) {
        //1) get all users with role ROLE_FELLAPP_DIRECTOR_WCM_PAINMEDICINE
        $users = $this->getUsersOfFellowshipSubspecialtyByRole($fellowshipSubspecialty,$roleName); //"_COORDINATOR_"

        //2) for each $coordinators in the FellowshipSubspecialty - check if this user exists in the coordinators, add if not.
        if( $roleName == "_COORDINATOR_" ) {
            $attachedUsers = $fellowshipSubspecialty->getCoordinators();
        }
        if( $roleName == "_DIRECTOR_" ) {
            $attachedUsers = $fellowshipSubspecialty->getDirectors();
        }
        if( $roleName == "_INTERVIEWER_" ) {
            $attachedUsers = $fellowshipSubspecialty->getInterviewers();
        }

        $modified = false;

        foreach( $users as $user ) {

            //Add user to FellowshipSubspecialty if user is not attached yet
            if( $user && !$attachedUsers->contains($user) ) {
                if( $roleName == "_COORDINATOR_" ) {
                    $fellowshipSubspecialty->addCoordinator($user);
                }
                if( $roleName == "_DIRECTOR_" ) {
                    $fellowshipSubspecialty->addDirector($user);
                }
                if( $roleName == "_INTERVIEWER_" ) {
                    $fellowshipSubspecialty->addInterviewer($user);
                }
                $modified = true;
            }

        }

        //Removing the role manually => remove user from $fellowshipSubspecialty: remove user from FellowshipSubspecialty if user does not have role
        //get coordinators => check if each coordinator has role => if not => remove this user from FellowshipSubspecialty
        $role = $this->getRoleByFellowshipSubspecialtyAndRolename($fellowshipSubspecialty,$roleName );
        //echo $roleName.": role=".$role."<br>";

        foreach( $attachedUsers as $user ) {
            if( !$user->hasRole($role) ) {
                //echo $roleName.": remove user=".$user."!!!!!!!!!!!!<br>";
                if ($roleName == "_COORDINATOR_") {
                    $fellowshipSubspecialty->removeCoordinator($user);
                }
                if ($roleName == "_DIRECTOR_") {
                    $fellowshipSubspecialty->removeDirector($user);
                }
                if ($roleName == "_INTERVIEWER_") {
                    $fellowshipSubspecialty->removeInterviewer($user);
                }
                $modified = true;
            }
        }


        if( $modified ) {
            //$this->em->persist($fellowshipSubspecialty);
            $this->em->flush($fellowshipSubspecialty);
        }
    }

    //compare original and final users => get removed users => for each removed user, remove the role
    public function processRemovedUsersByFellowshipSetting( $fellowshipSubspecialty, $newUsers, $origUsers, $roleName ) {
        if( count($newUsers) > 0 && count($origUsers) > 0 ) {
            //$this->printUsers($origUsers,"orig");
            //$this->printUsers($newUsers,"new");

            //get diff
            $diffUsers = $this->array_diff_assoc_true($newUsers->toArray(), $origUsers->toArray());
            //$diffUsers = array_diff($newUsers->toArray(),$origUsers->toArray());
            //$diffUsers = array_diff($origUsers->toArray(),$newUsers->toArray());

            //echo $roleName.": diffUsers count=".count($diffUsers)."<br>";
            //$this->printUsers($diffUsers,"diff");

            $this->removeRoleFromUsers($diffUsers,$fellowshipSubspecialty,$roleName);
        }
    }
    public function removeRoleFromUsers( $users, $fellowshipSubspecialty, $roleName ) {
        $role = $this->getRoleByFellowshipSubspecialtyAndRolename($fellowshipSubspecialty,$roleName );
        if( !$role ) {
            return null;
        }
        //echo $roleName.": role=".$role."<br>";
        foreach( $users as $user ) {
            //echo $roleName.": removeRole from user=".$user."<br>";
            $user->removeRole($role);
            $this->em->flush($user);
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

    public function findInterviewByFellappAndUser( $fellapp, $user ) {
        $interviews = array();
        foreach($fellapp->getInterviews() as $interview) {
            $interviewer = $interview->getInterviewer();
            if( $interviewer && $user && $interviewer->getId() == $user->getId() ) {
                $interviews[] = $interview;
            }
        }
        return $interviews;
    }

    public function sendAcceptedNotificationEmail($fellapp) {
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        $user = NULL;
        if( $this->container->get('security.token_storage')->getToken() ) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
        }
        if( $user instanceof User) {
            //User OK - do nothing
        } else {
            $user = $userSecUtil->findSystemUser();
        }
        if( !$user ) {
            $user = $userSecUtil->findSystemUser();
        }

        $applicant = $fellapp->getUser();
        if( $applicant ) {
            $applicantEmail = $applicant->getSingleEmail();
        } else {
            return false;
        }

        $applicantFullName = $fellapp->getApplicantFullName();
        $fellappType = $fellapp->getFellowshipSubspecialty()."";
        $startDate = $fellapp->getStartDate();
        if( $startDate ) {
            $startDateStr = $fellapp->getStartDate()->format('Y');
        } else {
            $startDateStr = NULL;
        }

        $acceptedEmailSubject = $userSecUtil->getSiteSettingParameter('acceptedEmailSubject',$this->container->getParameter('fellapp.sitename'));
        if( !$acceptedEmailSubject ) {
            //Congratulations on your acceptance to the [Subspecialty] [Year] fellowship at [Institution].
            //Institution should be a variable pre-set to "Weill Cornell Medicine" - if it does not exist, add this field to its Settings.
            $inst = $fellapp->getInstitution()."";
            $acceptedEmailSubject = "Congratulations on your acceptance to the "
                .$fellappType
                ." ".$startDateStr
                ." fellowship at ".$inst
            ;
        } else {
            $acceptedEmailSubject = $this->siteSettingsConstantReplace($acceptedEmailSubject,$fellapp);
        }

        $acceptedEmailBody = $userSecUtil->getSiteSettingParameter('acceptedEmailBody',$this->container->getParameter('fellapp.sitename'));
        if( !$acceptedEmailBody ) {
            //Dear FirstName LastName,
            //We are looking forward to having you join us as a [specialty] fellow in [year]!
            //Weill Cornell Medicine
            $acceptedEmailBody = "Dear $applicantFullName,"
                ."<br><br>"."We are looking forward to having you join us as a $fellappType fellow in $startDateStr!"
                ."<br><br>".$inst
            ;
        } else {
            $acceptedEmailBody = $this->siteSettingsConstantReplace($acceptedEmailBody,$fellapp);
        }

        //get CCs: coordinators and directors
        $directorEmails = $this->getDirectorsOfFellAppEmails($fellapp);
        $coordinatorEmails = $this->getCoordinatorsOfFellAppEmails($fellapp);
        $ccResponsibleEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));

        $emailUtil->sendEmail( $applicantEmail, $acceptedEmailSubject, $acceptedEmailBody, $ccResponsibleEmails );

        $msg = "Acceptance notification email has been sent to " . $applicantFullName . " (".$applicantEmail.")" . "; CC: ".implode(", ",$ccResponsibleEmails);
        $eventMsg = $msg . "<br><br> Subject:<br>". $acceptedEmailSubject . "<br><br>Body:<br>" . $acceptedEmailBody;

        $userSecUtil->createUserEditEvent(
            $this->container->getParameter('fellapp.sitename'), //$sitename
            $eventMsg,                                          //$event message
            $user,                                              //user
            $fellapp,                                           //$subjectEntities
            null,                                               //$request
            "FellApp Accepted Notification Email Sent"          //$action
        );

        return true;
    }

    public function sendRejectedNotificationEmail($fellapp) {
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        $user = NULL;
        if( $this->container->get('security.token_storage')->getToken() ) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
        }
        if( $user instanceof User) {
            //User OK - do nothing
        } else {
            $user = $userSecUtil->findSystemUser();
        }
        if( !$user ) {
            $user = $userSecUtil->findSystemUser();
        }

        $applicant = $fellapp->getUser();
        if( $applicant ) {
            $applicantEmail = $applicant->getSingleEmail();
        } else {
            return false;
        }

        $applicantFullName = $fellapp->getApplicantFullName();
        $fellappType = $fellapp->getFellowshipSubspecialty()."";
        $startDate = $fellapp->getStartDate();
        if( $startDate ) {
            $startDateStr = $fellapp->getStartDate()->format('Y');
        } else {
            $startDateStr = NULL;
        }

        $rejectedEmailSubject = $userSecUtil->getSiteSettingParameter('rejectedEmailSubject',$this->container->getParameter('fellapp.sitename'));
        if( !$rejectedEmailSubject ) {
            //Thank you for applying to the [Subspecialty] [Year] fellowship at [Institution]
            $inst = $fellapp->getInstitution()."";
            $rejectedEmailSubject = "Thank you for applying to the "
                .$fellappType
                ." ".$startDateStr
                ." fellowship at ".$inst
            ;
        } else {
            $rejectedEmailSubject = $this->siteSettingsConstantReplace($rejectedEmailSubject,$fellapp);
        }

        $rejectedEmailBody = $userSecUtil->getSiteSettingParameter('rejectedEmailBody',$this->container->getParameter('fellapp.sitename'));
        if( !$rejectedEmailBody ) {
            //Dear FirstName LastName,
            //We have reviewed your application to the [specialty] fellowship for [year],
            // and we regret to inform you that we are unable to offer you a position at this time.
            // Please contact us if you have any questions.
            //Weill Cornell Medicine
            $rejectedEmailBody = "Dear $applicantFullName,"
                ."<br><br>"."We have reviewed your application to the $fellappType fellow for $startDateStr"
                ." and we regret to inform you that we are unable to offer you a position at this time."
                ."<br>Please contact us if you have any questions."
                ."<br><br>".$inst
            ;
        } else {
            $rejectedEmailBody = $this->siteSettingsConstantReplace($rejectedEmailBody,$fellapp);
        }

        //get CCs: coordinators and directors
        $directorEmails = $this->getDirectorsOfFellAppEmails($fellapp);
        $coordinatorEmails = $this->getCoordinatorsOfFellAppEmails($fellapp);
        $ccResponsibleEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));

        $emailUtil->sendEmail( $applicantEmail, $rejectedEmailSubject, $rejectedEmailBody, $ccResponsibleEmails );

        $msg = "Rejection notification email has been sent to " . $applicantFullName . " (".$applicantEmail.")" . "; CC: ".implode(", ",$ccResponsibleEmails);
        $eventMsg = $msg . "<br><br> Subject:<br>". $rejectedEmailSubject . "<br><br>Body:<br>" . $rejectedEmailBody;

        $userSecUtil->createUserEditEvent(
            $this->container->getParameter('fellapp.sitename'), //$sitename
            $eventMsg,                                          //$event message
            $user,                                              //user
            $fellapp,                                           //$subjectEntities
            null,                                               //$request
            "FellApp Rejected Notification Email Sent"          //$action
        );

        return true;
    }

    public function getRejectionEmailSent($fellapp) {
        $repository = $this->em->getRepository('OlegUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");

        //$fellappIdInteger = $fellapp->getId()."";
        //echo "fellappIdInteger=".$fellappIdInteger."<br>";

        $dql->innerJoin('logger.eventType', 'eventType');
        $dql->where("logger.entityName = 'FellowshipApplication' AND logger.entityId = '".$fellapp->getId()."'");

        //$dql->andWhere("logger.event LIKE :eventStr AND logger.event LIKE :eventStr2");
        $dql->andWhere("eventType.name = :eventTypeStr");

        $dql->orderBy("logger.id","DESC");
        $query = $this->em->createQuery($dql);

        //The status of the work request APCP668-REQ16553 has been changed from 'Pending Histology' to 'Completed and Notified' by Susanna Mirabelli - sum2029 (WCM CWID)

        $query->setParameters(
            array(
                'eventTypeStr' => "FellApp Rejected Notification Email Sent"
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

    public function siteSettingsConstantReplace($str,$fellapp) {

        $applicantFullName = $fellapp->getApplicantFullName();
        $fellappType = $fellapp->getFellowshipSubspecialty()."";
        $inst = $fellapp->getInstitution()."";
        $startDate = $fellapp->getStartDate();
        if( $startDate ) {
            $startDateStr = $fellapp->getStartDate()->format('Y');
        } else {
            $startDateStr = NULL;
        }

        $str = str_replace("[[APPLICANT NAME]]",$applicantFullName,$str);
        $str = str_replace("[[START YEAR]]",$startDateStr,$str);
        $str = str_replace("[[FELLOWSHIP TYPE]]",$fellappType,$str);
        $str = str_replace("[[INSTITUTION]]",$inst,$str);

        return $str;
    }
} 