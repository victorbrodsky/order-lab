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

namespace App\VacReqBundle\Util;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use App\VacReqBundle\Entity\VacReqCarryOver;
use App\VacReqBundle\Entity\VacReqUserCarryOver;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Date;

//use Box\Spout\Common\Type;
//use Box\Spout\Writer\WriterFactory;
//use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
//use Box\Spout\Common\Entity\Style\Border;
//use Box\Spout\Common\Entity\Style\Color;
//use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/25/2016
 * Time: 11:16 AM
 */
class VacReqUtil
{

    protected $em;
    protected $security;
    //protected $secAuth;
    protected $container;


    public function __construct( EntityManagerInterface $em, Security $security, ContainerInterface $container ) {

        $this->em = $em;
        $this->security = $security;
        //$this->secAuth = $secAuth;
        $this->container = $container;

    }


    public function getSettingsByInstitution($instid) {
        $setting = $this->em->getRepository('AppVacReqBundle:VacReqSettings')->findOneByInstitution($instid);
        return $setting;
    }


    public function getInstitutionSettingArray() {
        $settings = $this->em->getRepository('AppVacReqBundle:VacReqSettings')->findAll();

        $arraySettings = array();

        foreach( $settings as $setting ) {
            if( $setting->getInstitution() ) {
                $instid = $setting->getInstitution()->getId();
                $arraySettings[$instid] = $setting;
            }
        }

        return $arraySettings;
    }



    public function settingsAddRemoveUsers( $settings, $userIds ) {
        $originalUsers = $settings->getEmailUsers();

        $newUsers = new ArrayCollection();
        foreach( explode(",",$userIds) as $userId ) {
            //echo "userId=" . $userId . "<br>";
            $emailUser = $this->em->getRepository('AppUserdirectoryBundle:User')->find($userId);
            if( $emailUser ) {
                $newUsers->add($emailUser);
            }
        }

        if( $originalUsers == $newUsers ) {
            return null;
        }

        $originalUsersNames = array();
        foreach( $originalUsers as $originalUser ) {
            $originalUsersNames[] = $originalUser;
            $settings->removeEmailUser($originalUser);
        }

        $newUsersNames = array();
        foreach( $newUsers as $newUser ) {
            $newUsersNames[] = $newUser;
            $settings->addEmailUser($newUser);
        }

        //$arrayDiff = array_diff($originalUserSiteRoles, $newUserSiteRoles);
        $res = array(
            'originalUsers' => $originalUsersNames,
            'newUsers' => $newUsersNames
        );

        return $res;
    }


    //find role approvers by institution
    public function getRequestApprovers( $entity, $institutionType="institution" ) {

        $institution = $entity->getInstitution();
//        if( $institutionType == "institution" ) {
//            $institution = $entity->getInstitution();
//            //echo "institution <br>";
//        }
//        if( $institutionType == "tentativeInstitution" ) {
//            $institution = $entity->getTentativeInstitution();
//            //echo "tentativeInstitution <br>";
//        }

        if( !$institution ) {
            return null;
        }

        //echo "<br>institution=".$institution."<br>";
        //echo "tentative institution=".$entity->getTentativeInstitution()."<br>";

        if( $entity->getRequestType()->getAbbreviation() == "carryover" ) {

            //echo "getTentativeStatus=".$entity->getTentativeStatus()."<br>";
            if( $entity->getTentativeInstitution() && $entity->getTentativeStatus() == 'pending' ) {
                $approverRole = "ROLE_VACREQ_APPROVER";
                $institution = $entity->getTentativeInstitution();
            } else {
                $approverRole = "ROLE_VACREQ_SUPERVISOR";
            }

            //specifically asked for tentative approvers
            if( $entity->getTentativeInstitution() && $institutionType == "tentativeInstitution" ) {
                $approverRole = "ROLE_VACREQ_APPROVER";
                $institution = $entity->getTentativeInstitution();
            }

        } else {
            $approverRole = "ROLE_VACREQ_APPROVER";
        }

        //echo "approverRole=".$approverRole."<br>";
        //echo "institution=".$institution."<br>";

        $approvers = array();
        $roleApprovers = $this->em->getRepository('AppUserdirectoryBundle:User')->
            findRolesBySiteAndPartialRoleName( "vacreq", $approverRole, $institution->getId());
        //echo "roleApprovers count=".count($roleApprovers)."<br>";

        $roleApprover = $roleApprovers[0];

        if( $roleApprover ) {
            $approvers = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleApprover->getName(),"infos.lastName",true);
        }

        return $approvers;
    }

    //$groupId - group (institution) ID
    //$rolePartialName - "ROLE_VACREQ_SUBMITTER", "ROLE_VACREQ_APPROVER", "ROLE_VACREQ_SUPERVISOR"
    public function getUsersByGroupId( $groupId, $rolePartialName="ROLE_VACREQ_SUBMITTER" ) {
        $users = array();

        $roles = $this->em->getRepository('AppUserdirectoryBundle:User')->
                            findRolesBySiteAndPartialRoleName( "vacreq", $rolePartialName, $groupId);

        $role = $roles[0];

        //echo "role=".$role."<br>";
        if( $role ) {
            $users = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($role->getName(),"infos.lastName",true);
        }

        return $users;
    }


    //set confirmation email to approver and email users
    public function sendConfirmationEmailToApprovers( $entity, $sendCopy=true ) {
        $subject = $entity->getEmailSubject();
        $message = $this->createEmailBody($entity);
        return $this->sendGeneralEmailToApproversAndEmailUsers($entity,$subject,$message,$sendCopy);
    }
    public function createEmailBody( $entity, $emailToUser=null, $addText=null, $withLinks=true ) {

        //$break = "\r\n";
        $break = "<br>";

        $submitter = $entity->getUser();

        //$message = "Dear " . $emailToUser->getUsernameOptimal() . "," . $break.$break;
        $message = "Dear ###emailuser###," . $break.$break;

        $requestName = $entity->getRequestName();

        $message .= $submitter->getUsernameOptimal()." has submitted the ".$requestName." ID #".$entity->getId()." and it is ready for review.";
        $message .= $break.$break.$entity->printRequest($this->container)."";

        $reviewRequestUrl = $this->container->get('router')->generate(
            'vacreq_review',
            array(
                'id' => $entity->getId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $message .= $break . $break . "Please follow the link below to review ".$requestName." ID #".$entity->getId().":" . $break;
        $message .= $reviewRequestUrl . $break . $break;

        //$message .= $break . "Please click on the URLs below for quick actions to approve or reject ".$requestName." ID #".$entity->getId().".";

        if( $entity->hasBusinessRequest() ) {
            //href="{{ path(vacreq_sitename~'_status_email_change', { 'id': entity.id,  'requestName':requestName, 'status': 'approved' }) }}
            //approved
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_status_email_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'business',
                    'status' => 'approved'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please follow the link below to Approve the ".$requestName." ID #".$entity->getId().":" . $break;
            $message .= $actionRequestUrl;

            //rejected
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_status_email_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'business',
                    'status' => 'rejected'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please follow the link below to Reject the ".$requestName." ID #".$entity->getId().":" . $break;
            $message .= $actionRequestUrl;
        }

        if( $entity->hasVacationRequest() ) {
            //href="{{ path(vacreq_sitename~'_status_email_change', { 'id': entity.id,  'requestName':requestName, 'status': 'approved' }) }}
            //approved
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_status_email_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'vacation',
                    'status' => 'approved'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please follow the link below to Approve the ".$requestName." ID #".$entity->getId().":" . $break;
            $message .= $actionRequestUrl;

            //rejected
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_status_email_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'vacation',
                    'status' => 'rejected'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please follow the link below to Reject the ".$requestName." ID #".$entity->getId().":" . $break;
            $message .= $actionRequestUrl;
        }

        //CARRYOVER body
        if( $entity->getRequestType()->getAbbreviation() == "carryover" ) {

            $yearRange = $this->getCurrentAcademicYearRange();
            //$accruedDays = $this->getAccruedDaysUpToThisMonth();
            //$carriedOverDays = $this->getUserCarryOverDays($entity->getUser(),$entity->getSourceYear());
            //if( !$carriedOverDays ) {
            //    $carriedOverDays = 0;
            //}

            //vacation
            $resVacationDays = $this->getApprovedTotalDays($entity->getUser(),"vacation");
            $approvedVacationDays = $resVacationDays['numberOfDays'];
            if( !$resVacationDays['accurate'] ) {
                $approvedVacationDays .= " (".$this->getInaccuracyMessage().")";
            }

            //business
//            $resBusinessDays = $this->getApprovedTotalDays($entity->getUser(),"business");
//            $approvedBusinessDays = $resBusinessDays['numberOfDays'];
//            $accurateBusiness = $resBusinessDays['accurate'];
//            if( !$accurateBusiness ) {
//                $approvedBusinessDays .= " (".$this->getInaccuracyMessage().")";
//            }

            //FirstName LastName requested carry over of X vacation days from [Source Academic Year] to [Destination Academic Year].
            //As of [date of request submission], FirstName LastName has accrued Y days in the current [current academic year as 2015-2016] academic year,
            // had Z days carried over from [current academic year -1] to [current academic year],
            // and has been approved for M vacation days and N business travel days during [current academic year as 2015-2016] so far.

            //Dear ...
            $message = "Dear ###emailuser###," . $break.$break;

            //FirstName LastName requested carry over of X vacation days from [Source Academic Year] to [Destination Academic Year].
            $message .= $entity->getEmailSubject().".";

            //comment
            if( $entity->getComment() ) {
                $message .= $break . "Comment: " . $entity->getComment();
            }

            $message .= $break.$break;

//            //As of [date of request submission], FirstName LastName has accrued Y days in the current [current academic year as 2015-2016] academic year,
//            $message .= "As of ".$entity->getCreateDate()->format("F jS Y").", ".$entity->getUser()->getUsernameOptimal()." has accrued ".
//                $accruedDays." days in the current ".$yearRange." academic year,";
//            //had Z days carried over from [current academic year -1] to [current academic year],
//            $message .= " had ".$carriedOverDays." days carried over from ".$previousYear." to ".$currentYear.",";
//            //and has been approved for M vacation days and N business travel days during [current academic year as 2015-2016] so far.
//            $message .= " and has been approved for ".$approvedVacationDays." days and ".$approvedBusinessDays.
//                " business travel days during ".$yearRange." so far.";

            //subject + SubmitterFirstName SubmitterLastName has M approved vacation days during [CURRENT 20XX-20YY] year.
            $message .= $entity->getUser()->getUsernameOptimal()." has ".$approvedVacationDays." approved vacation days during ".$yearRange." year.";

            if( $withLinks ) {
                $prefix = " ";
                if ($entity->getTentativeStatus() == 'pending') {
                    $prefix = " tentatively ";
                }

                if ($entity->getTentativeStatus() == 'approved' && $entity->getTentativeApprovedRejectDate() && $entity->getTentativeApprover()) {
                    //This request has been tentatively approved by [VacationApproverFirstName, VacationApproverLastName] on
                    // DateOfStatusChange at TimeOfStatusChange.
                    $tentativeApprovedRejectDate = $entity->getTentativeApprovedRejectDate()->setTimezone(new \DateTimeZone('America/New_York'));
                    $message .= $break . $break . "This request has been tentatively approved by " . $entity->getTentativeApprover() .
                        " on " . $tentativeApprovedRejectDate->format("M d Y h:i A T") . ".";
                    $prefix = " final ";
                }

                $actionRequestApproveUrl = $this->container->get('router')->generate(
                    'vacreq_status_email_change_carryover',
                    array(
                        'id' => $entity->getId(),
                        'requestName' => 'entire',
                        'status' => 'approved'
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $message .= $break . $break . "To" . $prefix . "approve this request, please follow this link:" . $break;
                $message .= $actionRequestApproveUrl;

                //rejected
                $actionRequestRejectUrl = $this->container->get('router')->generate(
                    'vacreq_status_email_change_carryover',
                    array(
                        'id' => $entity->getId(),
                        'requestName' => 'entire',
                        'status' => 'rejected'
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $message .= $break . $break . "To deny this request, please follow this link:" . $break;
                $message .= $actionRequestRejectUrl;

                //To review SubmitterFirstName SubmitterLastName's past requests, please follow this link:
                //[link to incoming requests filtered by person away = submitter]
                $reviewUrl = $this->container->get('router')->generate(
                    'vacreq_incomingrequests',
                    array(
                        'filter[requestType]' => $entity->getRequestType()->getId(),
                        'filter[user]' => $submitter->getId()
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $message .= $break . $break . "To review " . $submitter->getUsernameShortest() . "'s past requests, please follow this link:" . $break;
                $message .= $reviewUrl;
            }//$withLinks
        }

        $message .= $break.$break."To approve or reject requests, Division Approvers must be on site or using vpn when off site.";
        $message .= $break.$break."**** PLEASE DO NOT REPLY TO THIS EMAIL ****";

        if( $addText ) {
            $message = $addText.$break.$break.$message;
        }

        return $message;
    }

    //set respond confirmation email to a submitter and email users
    public function sendSingleRespondEmailToSubmitter( $entity, $approver, $status, $message=null ) {

        $logger = $this->container->get('logger');
        $emailUtil = $this->container->get('user_mailer_utility');
        //$break = "\r\n";
        $break = "<br>";

        $institution = $entity->getInstitution();
        if( !$institution ) {
            return null;
        }

        $requestName = $entity->getRequestName();

        //$subject = "Respond Confirmation for ".$requestName." ID #".$entity->getId();
        $subject = $entity->getRequestSubject();

        $submitter = $entity->getUser();

        if( !$message ) {
            $message = "Dear " . $submitter->getUsernameOptimal() . "," . $break . $break;

//            $message .= "Your " . $requestName . " ID #" . $entity->getId();
//            if ($status == 'pending') {
//                $status = 'set to Pending';
//            }
//            $message .= " has been " . $status . " by " . $approver->getUsernameOptimal() . ":" . $break;
//            $message .= $entity->getDetailedStatus().".".$break.$break;

            $message .= "Your " . $entity->getRequestMessageHeader() . " by " . $approver->getUsernameOptimal() . ":" . $break.$break;

            $message .= $entity->printRequest($this->container)."".$break.$break;

            $message .= "**** PLEASE DO NOT REPLY TO THIS EMAIL ****";
        }

        $emailUtil->sendEmail( $submitter->getSingleEmail(), $subject, $message, null, null );
        $logger->notice("sendSingleRespondEmailToSubmitter: sent confirmation email to submitter ".$submitter->getSingleEmail());

        //css to email users
        //$approversNameArr = array();
        $cssArr = array();
        $settings = $this->getSettingsByInstitution($institution->getId());
        if( $settings ) {
            foreach ($settings->getEmailUsers() as $emailUser) {
                $emailUserEmail = $emailUser->getSingleEmail();
                if( $emailUserEmail ) {
                    $cssArr[] = $emailUserEmail;
                    //$approversNameArr[] = $emailUser."";
                }
            }
        }

        //add approvers to css
        if( 0 ) { //don't send a copy of the confirmation email to approvers
            $approvers = $this->getRequestApprovers($entity);
            foreach ($approvers as $approver) {
                $approverSingleEmail = $approver->getSingleEmail();
                if ($approverSingleEmail) {
                    $cssArr[] = $approverSingleEmail;
                    //$approversNameArr[] = $approver . "";
                }
            } //foreach approver
        }

        //$emailUtil->sendEmail( $submitter->getSingleEmail(), $subject, $message, $cssArr, null );

        if( count($cssArr) > 0 ) {
            $subject = "Copy of the email: " . $subject;
            $addText = "### This is a copy of the email sent to the submitter " . $submitter . "###";
            $message = $addText . $break . $break . $message;
            $emailUtil->sendEmail($cssArr, $subject, $message, null, null);

            $logger->notice("sendSingleRespondEmailToSubmitter: sent confirmation email to all related users " . implode("; ", $cssArr));
        }
    }

    //totalAllocatedDays - vacationDays + carryOverDays for given $yearRange
    //carryOver days are from getUserCarryOverDays
    //TODO: might add "date of hire" and "end of employment date" to calculate the total vacation days
    public function totalVacationRemainingDays( $user, $totalAllocatedDays=null, $vacationDays=null, $carryOverDaysToNextYear=null, $carryOverDaysFromPreviousYear=null, $yearRange=null ) {

        if( !$totalAllocatedDays ) {
            $totalAllocatedDays = $this->getTotalAccruedDays();
        }

        if( !$yearRange ) {
            $yearRange = $this->getCurrentAcademicYearRange();
        }

        $vacationAccurate = true;
        if( !$vacationDays ) {
            $vacationDaysRes = $this->getApprovedTotalDaysAcademicYear($user,'vacation',$yearRange);
            $vacationDays = $vacationDaysRes['numberOfDays'];
            $vacationAccurate = $vacationDaysRes['accurate'];
        }

        if( !$carryOverDaysFromPreviousYear ) {
            //carried over days from previous year
            $carryOverDaysFromPreviousYear = $this->getUserCarryOverDays($user,$yearRange);
        }

        if( !$carryOverDaysToNextYear ) {
            //subtract carried over days from the current year to the next year.
            $nextYearRange = $this->getNextAcademicYearRange();
            //echo "nextYearRange=".$nextYearRange."<br>";
            $carryOverDaysToNextYear = $this->getUserCarryOverDays($user, $nextYearRange);
            //echo "carryOverDaysToNextYear=".$carryOverDaysToNextYear."<br>";
        }

//        echo "yearRange=".$yearRange."<br>";
//        echo "totalAllocatedDays=".$totalAllocatedDays."<br>";
//        echo "vacationDays=".$vacationDays."<br>";
//        echo "carryOverDays=".$carryOverDays."<br>";

        $res = array(
            'numberOfDays' => ( (int)$totalAllocatedDays - (int)$vacationDays + (int)$carryOverDaysFromPreviousYear ) - (int)$carryOverDaysToNextYear,
            'accurate' => $vacationAccurate
        );

        return $res;
    }

    //"During the current academic year, you have received X approved vacation days in total."
    // (if X = 1, show "During the current academic year, you have received X approved vacation day."
    // if X = 0, show "During the current academic year, you have received no approved vacation days."
    public function getApprovedDaysString( $user, $bruteForce=false ) {

        $requestType = $this->em->getRepository('AppVacReqBundle:VacReqRequestTypeList')->findOneByAbbreviation("business-vacation");

        $yearRange = $this->getCurrentAcademicYearRange();

        //testing
//        $testingDates = $this->getCurrentAcademicYearStartEndDates();
//        echo "<pre>";
//        print_r($testingDates);
//        echo "</pre>";
//        echo "yearRange=".$yearRange."<br>";
//        exit('1');

        $result = "During the current ".$yearRange." academic year, you have received ";

        $yearRangeArr = explode("-",$yearRange);
        $academicYear = $yearRangeArr[0];

        //////////////////////// Business /////////////////////
        $requestTypeStr = 'business';
        $res = $this->getApprovedTotalDays($user,$requestTypeStr,$bruteForce);
        $numberOfDays = $res['numberOfDays'];
        $accurate = $res['accurate'];

        if( !$numberOfDays || $numberOfDays == 0 ) {
            $businessDaysUrlText = "no approved ".$requestTypeStr." travel days";
        }
        if( $numberOfDays == 1 ) {
            $businessDaysUrlText = $numberOfDays." approved ".$requestTypeStr." travel day";
        }
        if( $numberOfDays > 1 ) {
            $businessDaysUrlText = $numberOfDays." approved ".$requestTypeStr." travel days";
        }

        //convert "X approved business travel days" to link
        $businessDaysUrl = $this->container->get('router')->generate(
            'vacreq_myrequests',
            array(
                'filter[requestType]' => $requestType->getId(),
                'filter[businessRequest]' => 1,
                'filter[approved]' => 1,
                'filter[academicYear]' => $academicYear
            )
        );
        $result .= '<a href="'.$businessDaysUrl.'">'.$businessDaysUrlText.'</a>';

        if( $numberOfDays > 1 ) {
            $result .= " in total";
        }
        if( !$accurate ) {
            $result .= " (".$this->getInaccuracyMessage().")";
        }
        //////////////////////// Eof Business /////////////////////

        $result .= " and ";

        //////////////////////// Vacation /////////////////////
        $requestTypeStr = 'vacation';
        $res = $this->getApprovedTotalDays($user,$requestTypeStr,$bruteForce);
        $numberOfDays = $res['numberOfDays'];
        $accurate = $res['accurate'];
        if( !$numberOfDays || $numberOfDays == 0 ) {
            $vacationDaysUrlText = "no approved ".$requestTypeStr." days";
        }
        if( $numberOfDays == 1 ) {
            $vacationDaysUrlText = $numberOfDays." approved ".$requestTypeStr." day";
        }
        if( $numberOfDays > 1 ) {
            $vacationDaysUrlText = $numberOfDays." approved ".$requestTypeStr." days";
        }

        //convert "X approved vacation days" to link
        $vacationDaysUrl = $this->container->get('router')->generate(
            'vacreq_myrequests',
            array(
                'filter[requestType]' => $requestType->getId(),
                'filter[vacationRequest]' => 1,
                'filter[approved]' => 1,
                'filter[academicYear]' => $academicYear
            )
        );
        $result .= '<a href="'.$vacationDaysUrl.'">'.$vacationDaysUrlText.'</a>';

        if( $numberOfDays > 1 ) {
            $result .= " in total";
        }
        if( !$accurate ) {
            $result .= " (".$this->getInaccuracyMessage().")";
        }
        //////////////////////// EOF Vacation /////////////////////

        $result .= ".";

        //if your requests included holidays, they are not automatically removed from these counts
        //If the number of days in the current academic year for both vacation and business travel = 0, this sentence should not be shown.
        if( $numberOfDays > 0 ) {

            $userSecUtil = $this->container->get('user_security_utility');
            $holidaysUrl = $userSecUtil->getSiteSettingParameter('holidaysUrl');
            if (!$holidaysUrl) {
                throw new \InvalidArgumentException('holidaysUrl is not defined in Site Parameters.');
            }
            $holidayLink = '<a href="' . $holidaysUrl . '" target="_blank">holidays</a>';

            $result .= "<br>If your requests included " . $holidayLink . ", they are not automatically removed from these counts.";
        }

        return $result;
    }

    //$yearRange: '2015-2016' or '2015'
    public function getUserCarryOverDays( $user, $yearRange, $asObject=false ) {

        $startYearArr = $this->getYearsFromYearRangeStr($yearRange);
        $startYear = $startYearArr[0];

        $repository = $this->em->getRepository('AppVacReqBundle:VacReqCarryOver');
        $dql = $repository->createQueryBuilder('carryOver');

        $dql->leftJoin("carryOver.userCarryOver", "userCarryOver");

        $dql->where("userCarryOver.user = :user");
        $dql->andWhere("carryOver.year = :year");

        $query = $this->em->createQuery($dql);

        $query->setParameter('user', $user->getId());
        $query->setParameter('year', $startYear);

        //echo "dql=".$dql."<br>";

        $carryOvers = $query->getResult();
        //echo "carryOvers=".count($carryOvers)."<br>";

        if( count($carryOvers) > 0 ) {
            if( $asObject ) {
                return $carryOvers[0];
            }
            $days = $carryOvers[0]->getDays();
            //echo "days=".$days."<br>";
            return $days;
        }
        //echo "days=null<br>";

        return null;
    }

    public function processVacReqCarryOverRequest( $entity, $onlyCheck=false ) {

        $logger = $this->container->get('logger');
        $requestType = $entity->getRequestType();

        if( !$requestType || ($requestType && $requestType->getAbbreviation() != "carryover") ) {
            return;
        }

        $subjectUser = $entity->getUser();

        //get userCarryOver. TODO: This does not distinguish between approved, rejected or pending requests.
        $userCarryOver = $this->em->getRepository('AppVacReqBundle:VacReqUserCarryOver')->findOneByUser($subjectUser->getId());
        //echo "found userCarryOverID=".$userCarryOver->getId()."<br>";

        if( !$userCarryOver ) {
            $userCarryOver = new VacReqUserCarryOver($subjectUser);
        }

        //get VacReqCarryOver for request's destination year
        $carryOverYear = $entity->getDestinationYear();

        $carryOver = null;
        foreach( $userCarryOver->getCarryOvers() as $carryOverThis ) {
            //echo "carryOverThis ID=".$carryOverThis->getId().": year=".$carryOverYear." ?= ".$carryOverThis->getYear()."<br>";
            if( $carryOverThis->getYear() == $carryOverYear ) {
                $carryOver = $carryOverThis;
                break;
            }
        }
        //$logger->notice("carryOver=".$carryOver);

        $carryOverDays = null;

        if( !$carryOver ) {
            if( $onlyCheck == false ) {
                $carryOver = new VacReqCarryOver();
                $carryOver->setYear($carryOverYear);
                $userCarryOver->addCarryOver($carryOver);
            }
        } else {
            $carryOverDays = $carryOver->getDays();
        }

        //echo "carryOverDays=".$carryOverDays."<br>";
        $res = array('userCarryOver'=>$userCarryOver);

        if( $carryOverDays ) {

            //"FirstName LastName already has X days carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year on file.
            $carryOverWarningMessageLog = $entity->getUser()->getUsernameOptimal()." already has ".$carryOverDays." days carried over from ".
                $entity->getSourceYearRange()." academic year to the ".$entity->getDestinationYearRange()." academic year on file.<br>";
            // This carry over request asks for N days to be carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year.
            $carryOverWarningMessageLog .= "This carry over request asks for ".$entity->getCarryOverDays()." days to be carried over from ".
                $entity->getSourceYearRange()." academic year to the ".$entity->getDestinationYearRange()." academic year on file.<br>";
            // Please enter the total amount of days that should be carried over 20YY-20ZZ academic year to the 20ZZ-20MM academic year: [ ]"
            $carryOverWarningMessage = $carryOverWarningMessageLog . "Please enter the total amount of days that should be carried over ".
                $entity->getSourceYearRange()." academic year to the ".$entity->getDestinationYearRange()." academic year on file.";

            $res['exists'] = true;
            $res['days'] = $carryOverDays;
            $res['carryOverWarningMessage'] = $carryOverWarningMessage;
            $res['carryOverWarningMessageLog'] = $carryOverWarningMessageLog;

        } else {

            $carryOverDays = $entity->getCarryOverDays();
            if( $carryOver ) {
                //$logger->notice("exists carryover=".$carryOver);
                if( $onlyCheck == false ) {
                    //$logger->notice("set carryover days=".$carryOverDays);
                    $carryOver->setDays($carryOverDays);
                    //$em = $this->getDoctrine()->getManager();
                    //$em->persist($carryOver);
                    //$em->flush($carryOver);
                }
            }
            $res['exists'] = false;
            $res['days'] = $carryOverDays;
            $res['carryOverWarningMessage'] = null;
            $res['carryOverWarningMessageLog'] = null;
        }

        if( $carryOver ) {
            //$logger->notice("exists carryover=".$carryOver);
            if( $onlyCheck == false ) {
                $carryOverDays = $entity->getCarryOverDays();
                //$logger->notice("final set carryover days=".$carryOverDays);
                $carryOver->setDays($carryOverDays);
                //$em = $this->getDoctrine()->getManager();
                //$em->persist($carryOver);
                //$em->flush($carryOver);
            }
        }

        return $res;
    }

    //TODO: if multiple carry over requests are existed, then the VacReqUserCarryOver should be changed according to them.
    //TODO: we might have one carry over request approved and one denied for the same year.
    //TODO: Currently, VacReqUserCarryOver is synchronised to the latest approved request.
    public function deleteCanceledVacReqCarryOverRequest( $entity )
    {

        //echo "start deleteCanceledVacReqCarryOverRequest <br>";

        $logger = $this->container->get('logger');
        $requestType = $entity->getRequestType();

        if (!$requestType || ($requestType && $requestType->getAbbreviation() != "carryover")) {
            return;
        }

        $subjectUser = $entity->getUser();

        //get userCarryOver
        $userCarryOver = $this->em->getRepository('AppVacReqBundle:VacReqUserCarryOver')->findOneByUser($subjectUser->getId());

        if (!$userCarryOver) {
            //$logger->notice("VacReqUserCarryOver not found by userid=".$subjectUser->getId());
            return;
        }

        //get VacReqCarryOver for request's destination year
        $carryOverYear = $entity->getDestinationYear();
        $carryOverDays = $entity->getCarryOverDays();

        $carryOver = null;
        foreach ($userCarryOver->getCarryOvers() as $carryOverThis) {
            //$logger->notice("carryOverThis->getYear()=".$carryOverThis->getYear());
            if( $carryOverThis->getYear() == $carryOverYear && $carryOverThis->getDays() == $carryOverDays ) {
                $carryOver = $carryOverThis;
                break;
            }
        }

        $removeCarryoverStr = "";

        if( $carryOver ) {

            $removeCarryoverStr = "Removed CarryOver data (".$carryOver->getDays()." day from destination ".$carryOverYear." year for ".$subjectUser;

            $userCarryOver->removeCarryOver($carryOver);

            $this->em->persist($carryOver);
            $this->em->remove($carryOver);

            $this->em->persist($userCarryOver);
            $this->em->flush();

            //$logger->notice($removeCarryoverStr);
        } else {
            //$logger->notice("VacReqUserCarryOver does not carryOver object with destination year=".$carryOverYear);
        }

        return $removeCarryoverStr;
    }

    public function getYearsFromYearRangeStr($yearRangeStr) {
        if( !$yearRangeStr ) {
            throw new \InvalidArgumentException('Year Range of the Academic year is not defined: yearRangeStr='.$yearRangeStr);
        }
        if( strpos($yearRangeStr, '-') === false ) {
            //echo "no '-' in ".$yearRangeStr."<br>";
            $yearRangeArr = array($yearRangeStr);
            return $yearRangeArr;
        }
        $yearRangeArr = explode("-",$yearRangeStr);
        if( count($yearRangeArr) != 2 ) {
            throw new \InvalidArgumentException('Start or End Academic years are not defined: yearRangeStr='.$yearRangeStr);
        }
        return $yearRangeArr;
    }

    public function getPendingTotalDaysAcademicYear( $user, $yearRange ) {

        $requestTypeStr = "business";
        $resB = $this->getApprovedTotalDaysAcademicYear( $user, $requestTypeStr, $yearRange, "pending" );
        $numberOfDaysB = $resB['numberOfDays'];

        $requestTypeStr = "vacation";
        $resV = $this->getApprovedTotalDaysAcademicYear( $user, $requestTypeStr, $yearRange, "pending" );
        $numberOfDaysV = $resV['numberOfDays'];

        $totalPendingDays = $numberOfDaysB + $numberOfDaysV;

        return $totalPendingDays;
    }

    public function getCurrentAcademicYearRange() {
        $dates = $this->getCurrentAcademicYearStartEndDates();
        $startDate = $dates['startDate']; //Y-m-d
        //echo "startDate=".$startDate."<br>";
        $currentYearStartDateArr = explode("-",$startDate);
        $startYear = $currentYearStartDateArr[0];

        $endDate = $dates['endDate']; //Y-m-d
        //echo "endDate=".$endDate."<br>";
        $currentYearEndDateArr = explode("-",$endDate);
        $endYear = $currentYearEndDateArr[0];

        $yearRange = $startYear."-".$endYear;

        return $yearRange;
    }

    //$offset = 0 => previous year
    //$offset = 1 => previous previous year
    public function getPreviousAcademicYearRange( $offset = null ) {
        $dates = $this->getCurrentAcademicYearStartEndDates();
        $startDate = $dates['startDate']; //Y-m-d
        //echo "startDate=".$startDate."<br>";

        $currentYearStartDateArr = explode("-",$startDate);
        $year = $currentYearStartDateArr[0];

        $endYear = ((int)$year);     //previous year end
        $startYear = $endYear - 1;   //previous year start

        if( $offset ) {
            $endYear = $endYear - 1;
            $startYear = $startYear - 1;
        }

        //echo "previous year=".$year."<br>";

        $yearRange = $startYear."-".$endYear;

        return $yearRange;
    }

    public function getNextAcademicYearRange() {
        $dates = $this->getCurrentAcademicYearStartEndDates();
        $startDate = $dates['startDate']; //Y-m-d
        //echo "startDate=".$startDate."<br>";

        $currentYearStartDateArr = explode("-",$startDate);
        $year = $currentYearStartDateArr[0];

        $startYear = ((int)$year) + 1;   //next year start
        $endYear = $startYear + 1; //next year end

        //echo "next year=".$year."<br>";

        $yearRange = $startYear."-".$endYear;

        return $yearRange;
    }

    //calculate approved total days for current academical year
    public function getApprovedTotalDays( $user, $requestTypeStr, $bruteForce=false ) {
        $yearRange = $this->getCurrentAcademicYearRange();
        //echo "getApprovedTotalDays yearRange=".$yearRange."<br>";
        $res = $this->getApprovedTotalDaysAcademicYear( $user, $requestTypeStr, $yearRange, "approved", $bruteForce );
        return $res;
    }

    //calculate approved total days for current academical year
    public function getPreviousYearApprovedTotalDays( $user, $requestTypeStr, $bruteForce=false ) {
        $yearRange = $this->getPreviousAcademicYearRange();
        //echo "getPreviousYearApprovedTotalDays yearRange=".$yearRange."<br>";
        $res = $this->getApprovedTotalDaysAcademicYear( $user, $requestTypeStr, $yearRange, "approved", $bruteForce );
        return $res;
    }

    public function getInaccuracyMessage() {
        return "the count may be imprecise due to included holidays";
    }

    //calculate approved total days for the academical year specified by $yearRange (2015-2016 - current academic year)
    public function getApprovedTotalDaysAcademicYear( $user, $requestTypeStr, $yearRange, $status="approved", $bruteForce=false ) {

        $userSecUtil = $this->container->get('user_security_utility');
        //echo "yearRange=".$yearRange."<br>";

        //academicYearStart
        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart');
        if( !$academicYearStart ) {
            throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
        }
        //academicYearEnd
        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd');
        if( !$academicYearEnd ) {
            throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
        }

        //constract start and end date for DB select "Y-m-d"
        //academicYearStart
        $academicYearStartStr = $academicYearStart->format('m-d');

        //years
        $yearRangeArr = $this->getYearsFromYearRangeStr($yearRange);
        $previousYear = $yearRangeArr[0];
        $currentYear = $yearRangeArr[1];

        $academicYearStartStr = $previousYear."-".$academicYearStartStr;
        //echo "current academicYearStartStr=".$academicYearStartStr."<br>";
        //academicYearEnd
        $academicYearEndStr = $academicYearEnd->format('m-d');

        $academicYearEndStr = $currentYear."-".$academicYearEndStr;
        //echo "current academicYearEndStr=".$academicYearEndStr."<br>";

        //step1: get requests within current academic Year (2015-07-01 - 2016-06-30)
        $numberOfDaysInside = $this->getApprovedYearDays($user,$requestTypeStr,$academicYearStartStr,$academicYearEndStr,"inside",false,$status,$bruteForce);
        //echo "numberOfDaysInside=".$numberOfDaysInside."<br>";

        //step2: get requests with start date earlier than academic Year Start
        $numberOfDaysBeforeRes = $this->getApprovedBeforeAcademicYearDays($user,$requestTypeStr,$academicYearStartStr,$academicYearEndStr,$status,$bruteForce);
        $numberOfDaysBefore = $numberOfDaysBeforeRes['numberOfDays'];
        $accurateBefore = $numberOfDaysBeforeRes['accurate'];
        //echo "numberOfDaysBefore=".$numberOfDaysBefore."<br>";

        //step3: get requests with start date later than academic Year End
        $numberOfDaysAfterRes = $this->getApprovedAfterAcademicYearDays($user,$requestTypeStr,$academicYearStartStr,$academicYearEndStr,$status,$bruteForce);
        $numberOfDaysAfter = $numberOfDaysAfterRes['numberOfDays'];
        $accurateAfter = $numberOfDaysAfterRes['accurate'];
        //echo "numberOfDaysAfter=".$numberOfDaysAfter."<br>";

        $res = array();

        $numberOfDays = $numberOfDaysBefore+$numberOfDaysInside+$numberOfDaysAfter;
        //echo "sum numberOfDays=".$numberOfDays."<br>";

        $res['numberOfDays'] = $numberOfDays;
        $res['accurate'] = true;

        if( !$accurateBefore || !$accurateAfter ) {
            $res['accurate'] = false;
        }

        return $res;
    }

    // |-----start-----|year|-----end-----|year+1|----|
    // |-----start-----|2015-07-01|-----end-----|2016-06-30|----|
    public function getApprovedBeforeAcademicYearDays( $user, $requestTypeStr, $startStr=null, $endStr=null, $status="approved" ) {
        //$logger = $this->container->get('logger');
        $days = 0;
        $accurate = true;
        $subRequestGetMethod = "getRequest".$requestTypeStr;

        //echo "before startStr=".$startStr."<br>";
        //echo "before endStr=".$endStr."<br>";
        $requests = $this->getApprovedYearDays($user,$requestTypeStr,$startStr,$endStr,"before",true,$status);
        //echo "before requests count=".count($requests)."<br>";

        foreach( $requests as $request ) {
            $subRequest = $request->$subRequestGetMethod();
            $requestEndAcademicYearStr = $this->getAcademicYearEdgeDateBetweenRequestStartEnd($request,"first");
            if( !$requestEndAcademicYearStr ) {
                //echo $request->getId()." continue <br>";
                continue;
            }
            //echo "requestStartDate=".$subRequest->getStartDate()->format('Y-m-d')."<br>";
            //echo "requestEndAcademicYearStr=".$requestEndAcademicYearStr."<br>";
            //echo $request->getId().": before: request days=".$subRequest->getNumberOfDays()."<br>";
            //$workingDays = $this->getNumberOfWorkingDaysBetweenDates( $subRequest->getStartDate(), new \DateTime($requestEndAcademicYearStr) );
            $workingDays = $this->getNumberOfWorkingDaysBetweenDates( new \DateTime($requestEndAcademicYearStr), $subRequest->getEndDate() );
            //echo "workingDays=".$workingDays."<br>";
            if( $workingDays > $subRequest->getNumberOfDays() ) {
                //$logger->warning("Logical error getApprovedBeforeAcademicYearDays: number of calculated working days (".$workingDays.") are more than number of days in request (".$subRequest->getNumberOfDays().")");
                $workingDays = $subRequest->getNumberOfDays();
            }

            if( $workingDays != $subRequest->getNumberOfDays() ) {
                //inaccuracy
                //echo "user=".$request->getUser()."<br>";
                //echo "Before: ID# ".$request->getId()." inaccurate: workingDays=".$workingDays." enteredDays=".$subRequest->getNumberOfDays()."<br>";
                $accurate = false;
            }

            //echo $request->getId().": before: request days=".$workingDays."<br>";
            $days = $days + $workingDays;
        }

        $res = array(
            'numberOfDays' => $days,
            'accurate' => $accurate
        );

        return $res;
    }

    // |----|year|-----start-----|year+1|-----end-----|
    // |----|2015-07-01|-----start-----|2016-06-30|-----end-----|
    public function getApprovedAfterAcademicYearDays( $user, $requestTypeStr, $startStr=null, $endStr=null, $status="approved" ) {
        //$logger = $this->container->get('logger');
        $days = 0;
        $accurate = true;
        $subRequestGetMethod = "getRequest".$requestTypeStr;

        //echo "after startStr=".$startStr."<br>";
        //echo "after endStr=".$endStr."<br>";
        $requests = $this->getApprovedYearDays($user,$requestTypeStr,$startStr,$endStr,"after",true,$status);
        //echo "after requests count=".count($requests)."<br>";

        foreach( $requests as $request ) {
            $subRequest = $request->$subRequestGetMethod();
            $requestEndAcademicYearStr = $this->getAcademicYearEdgeDateBetweenRequestStartEnd($request,"last"); //get the academic year edge date after the request's start
            //echo $request->getId().": after: request days=".$subRequest->getNumberOfDays()."<br>";
            if( !$requestEndAcademicYearStr ) {
                continue;
            }
            //echo "requestEndAcademicYearStr=".$requestEndAcademicYearStr."<br>";
            //echo "requestStartAcademicYearStr=".$subRequest->getStartDate()->format('Y-m-d')."<br>";

            //$workingDays = $this->getNumberOfWorkingDaysBetweenDates( new \DateTime($requestStartAcademicYearStr), $subRequest->getEndDate() );
            $workingDays = $this->getNumberOfWorkingDaysBetweenDates( $subRequest->getStartDate(), new \DateTime($requestEndAcademicYearStr) );
            //echo "calculated workingDays=".$workingDays."<br>";
            if( $workingDays > $subRequest->getNumberOfDays() ) {
                //$logger->warning("Logical error getApprovedAfterAcademicYearDays: number of calculated working days (".$workingDays.") are more than number of days in request (".$subRequest->getNumberOfDays().")");
                $workingDays = $subRequest->getNumberOfDays();
            }

            if( $workingDays != $subRequest->getNumberOfDays() ) {
                //inaccuracy
                //echo "After: ID# ".$request->getId()." inaccurate: workingDays=".$workingDays." enteredDays=".$subRequest->getNumberOfDays()."<br>";
                $accurate = false;
            }

            //echo $request->getId().": after: request days=".$workingDays."<br>";
            $days = $days + $workingDays;
        }

        $res = array(
            'numberOfDays' => $days,
            'accurate' => $accurate
        );

        return $res;
    }

    //get prior approved days for the request's academic year:
    // SUM numberOfDays from this request's academic start date and this request's first day away
    public function getPriorApprovedDays( $request, $requestTypeStr ) {

        $userSecUtil = $this->container->get('user_security_utility');

        //academicYearStart
        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart');
        if( !$academicYearStart ) {
            throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
        }

        //academicYearStart
        $academicYearStartStr = $academicYearStart->format('m-d');

        //get request's academic year
        $academicYearArr = $this->getRequestAcademicYears($request);
        if( count($academicYearArr) > 0 ) {
            $yearsArr = $this->getYearsFromYearRangeStr($academicYearArr[0]);
            $startYear = $yearsArr[0];
            $academicYearStartStr = $startYear."-".$academicYearStartStr;
        } else {
            return null;
            //throw new \InvalidArgumentException("Request's academic start year is not defined in request ID#".$request->getId());
        }
        //echo "academicYearStartStr=".$academicYearStartStr."<br>";

        $user = $request->getUser();

        //get the first day away
        $requestFirstDateAway = $request->getFirstDateAway('approved',$requestTypeStr);
        if( $requestFirstDateAway == null ) {
            //exit("Request's first day away is not defined.");
            //throw new \InvalidArgumentException("Request's first day away is not defined.");
            return null;
        }
        $requestFirstDateAwayStr = $requestFirstDateAway->format('Y-m-d');
        //$requestFirstDateAwayStr = date("Y-m-d", strtotime('+1 days', strtotime($requestFirstDateAwayStr)) );
        //echo "requestFirstDateAwayStr=".$requestFirstDateAwayStr."<br>";

        //use the last day, otherwise duplicates requests are not counted
//        $finalStartEndDates = $request->getFinalStartEndDates($requestTypeStr);
//        $requestFirstDateAwayStr = $finalStartEndDates['endDate']->format('Y-m-d');
//        $requestFirstDateAwayStr = date("Y-m-d", strtotime('+1 days', strtotime($requestFirstDateAwayStr)) );

        $days = $this->getApprovedYearDays($user,$requestTypeStr,$academicYearStartStr,$requestFirstDateAwayStr,"inside",false);

        return $days;
    }

    //TODO: select distinct start, end dates
    //http://stackoverflow.com/questions/7224792/sql-to-find-time-elapsed-from-multiple-overlapping-intervals
    public function getApprovedYearDays_SingleQuery( $user, $requestTypeStr, $startStr=null, $endStr=null, $type=null, $asObject=false, $status='approved' ) {

        echo $user.": ".$type.": requestTypeStr=".$requestTypeStr."; status=".$status."<br>";
        echo "date range=".$startStr."<=>".$endStr."<br>";
        $numberOfDays = 0;

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $joinStr = " LEFT JOIN request2.requestBusiness requestType2 ";
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $joinStr = " LEFT JOIN request2.requestVacation requestType2 ";
        }

        //WITH  request.id <> request2.id AND request.user = request2.user AND user.id = ".$user->getId()." AND requestType.status='".$status."'
        //AND requestType.startDate > '" . $startStr . "'" . " AND requestType.endDate < " . "'" . $endStr . "'
        $query = $this->em->createQuery(
            "SELECT
              SUM(requestType.numberOfDays) as numberOfDays, COUNT(request) as totalCount
            FROM AppVacReqBundle:VacReqRequest request
            INNER JOIN request.user user
            INNER JOIN request.requestVacation requestType
            INNER JOIN AppVacReqBundle:VacReqRequest request2
              WITH  request.id <> request2.id AND request.user = request2.user AND user.id = ".$user->getId()." AND requestType.status='$status'
                    AND request.firstDayAway < request2.firstDayAway AND request.firstDayBackInOffice < request2.firstDayBackInOffice
            WHERE requestType.startDate > '$startStr' AND requestType.endDate < '$endStr'
            HAVING COUNT(request2.id) = 0
            "
        );

        $query = $this->em->createQuery(
            "SELECT request1
            FROM AppVacReqBundle:VacReqRequest request1
            INNER JOIN request1.user user
            INNER JOIN request1.requestVacation requestType
            INNER JOIN AppVacReqBundle:VacReqRequest request2
              WITH
              (request1.id <> request2.id)
              AND ( request1.firstDayAway <> request2.firstDayAway )
            WHERE
            requestType.startDate > '$startStr'
            AND requestType.endDate < '$endStr'
            AND request1.user = request2.user
            AND user.id = ".$user->getId()."
            AND requestType.status='$status'
            "
        );

        $requests = $query->getResult();
        echo "requests count=".count($requests)."<br>";

        foreach( $requests as $request ) {
            //echo $request->getId()." days=".$request->getTotalDays($status,$requestTypeStr);
            echo $request->getId()." days=".$request->getRequestVacation()->getNumberOfDays()."<br>";
        }
        exit();


        //INNER JOIN request2.requestVacation requestType2
        //WHERE user.id = ".$user->getId()." AND requestType.status='".$status."'"
        //." AND request.firstDayAway > request2.firstDayAway AND request.firstDayAway > request2.firstDayBackInOffice "
        //."GROUP BY request.user,requestType.startDate,requestType.endDate"
        //.""

//        $requests = $query->getResult();
//        foreach( $requests as $request ) {
//            $thisNumberOfDays = $request->getTotalDays($status,$requestTypeStr);
//            $finalStartEndDatesArr = $request->getFinalStartEndDates();
//            $startendStr = $finalStartEndDatesArr['startDate']->format('Y/m/d')."-".$finalStartEndDatesArr['endDate']->format('Y/m/d');
//            echo "request = ".$request->getId()." ".$startendStr.": days=".$thisNumberOfDays."<br>";
//            $numberOfDays = $numberOfDays + (int)$thisNumberOfDays;
//        }
//        echo "### get numberOfDays = ".$numberOfDays."<br><br>";

        $numberOfDaysItems = $query->getResult();
        echo "numberOfDaysItems count=".count($numberOfDaysItems)."<br>";

        if( $numberOfDaysItems ) {
            //$numberOfDaysItems = $numberOfDaysRes['numberOfDays'];
            if( count($numberOfDaysItems) > 1 ) {
                //$logger = $this->container->get('logger');
                //$logger->warning('Logical error: found more than one SUM: count='.count($numberOfDaysItems));
            }
            foreach( $numberOfDaysItems as $numberOfDaysItem ) {
                echo "+numberOfDays = ".$numberOfDaysItem['numberOfDays']."; count=".$numberOfDaysItem['totalCount']."<br>";
                $numberOfDays = $numberOfDays + $numberOfDaysItem['numberOfDays'];
            }
            echo "### get numberOfDays = ".$numberOfDays."<br><br>";
        }

        return $numberOfDays;


        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");

        if( $asObject ) {
            $dql->select('request');
        } else {
            //$dql->select('request');
            $dql->select('SUM(requestType.numberOfDays) as numberOfDays, COUNT(request) as totalCount');
            //$dql->select('DISTINCT request.id, user.id, SUM(requestType.numberOfDays) as numberOfDays');
        }

//        $dql->innerJoin(
//            "AppVacReqBundle:VacReqRequest",
//            "request2",
//            "WITH",
//            "request2.id <> request.id AND request2.user = request.user"
//        );

        $dql->leftJoin("request.user", "user");

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
            //$dql->leftJoin("request2.requestBusiness", "requestType2");
            //$joinStr = " LEFT JOIN request2.requestBusiness requestType2";
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
            //$dql->leftJoin("request2.requestVacation", "requestType2");
            //$joinStr = " LEFT JOIN request2.requestVacation requestType2";
        }

        $dql->where("requestType.id IS NOT NULL AND user.id = :userId AND requestType.status = :status");

        // |----|year|-----start-----end-----|year+1|----|
        // |----|2015-07-01|-----start-----end-----|2016-06-30|----|
        if( $type == "inside" && $startStr && $endStr ) {
            $dql->andWhere("requestType.startDate > '" . $startStr . "'" . " AND requestType.endDate < " . "'" . $endStr . "'");
        }

        // |-----start-----|year|-----end-----|year+1|----|
        // |-----rstart-----|2015-07-01|-----rend-----|2016-06-30|----|
        if( $type == "before" && $startStr ) {
            //echo "startStr=".$startStr."<br>";
            $dql->andWhere("requestType.startDate < '" . $startStr . "'" . " AND requestType.endDate > '".$startStr."'"); // . " AND requestType.endDate > " . "'" . $startStr . "'");
        }

        // |----|year|-----start-----|year+1|-----end-----|
        // |----|2015-07-01|-----start-----|2016-06-30|-----end-----|
        if( $type == "after" && $startStr && $endStr ) {
            //echo "sql endStr=".$endStr."<br>";
            //$dql->andWhere("requestType.endDate > '" . $endStr . "'" . " AND requestType.startDate < '".$endStr."'");  // . " AND requestType.endDate < " . "'" . $endStr . "'");
            //$dql->andWhere("requestType.startDate > '" . $startStr . "'" . " AND requestType.endDate > " . "'" . $endStr . "'");
            $dql->andWhere("requestType.startDate < '" . $endStr . "'" . " AND requestType.endDate > '".$endStr."'");
        }


        //if( !$asObject ) {
//                $dql->innerJoin(
//                    "AppVacReqBundle:VacReqRequest",
//                    "request2",
//                    "WITH",
//                    //"request2.firstDayAway > request.firstDayAway AND request2.firstDayBackInOffice > request.firstDayBackInOffice"
//                    "request2.firstDayAway > request.firstDayAway".
//                    " AND request2.firstDayBackInOffice > request.firstDayBackInOffice".
//                    " AND request2.user = request.user".
//                    " AND request2.id <> request.id"
//                );

//            $dql->innerJoin(
//                "AppVacReqBundle:VacReqRequest",
//                "request2",
//                "WITH",
//                "request2.id <> request.id AND request2.user = request.user"
//            );
//            $dql->andWhere(
//                "request2.firstDayAway > request.firstDayAway"
//                ." AND request2.firstDayBackInOffice > request.firstDayBackInOffice"
//                ." AND request2.firstDayAway > request.firstDayBackInOffice"
//                //." AND request2.user = request.user"
//                //." AND request2.id <> request.id"
//            );

//        $dql->andWhere(
//            "requestType2.startDate > requestType.startDate"
//            ." AND requestType2.endDate > requestType.endDate"
//            ." AND requestType2.startDate > requestType.endDate"
//            ." AND request2.user = request.user"
//            ." AND request2.id <> request.id"
//        );

//        $dql->andWhere(
//            "requestType.startDate > requestType2.startDate"
//            ." AND requestType.endDate > requestType2.endDate"
//            ." AND requestType.startDate > requestType2.endDate"
//            ." AND request2.user = request.user"
//            ." AND request2.id <> request.id"
//        );

//        $dql->andWhere( "EXISTS (SELECT 1".
//            " FROM AppVacReqBundle:VacReqRequest as request2 ".$joinStr
//            ." WHERE request2.user = request.user AND request2.id <> request.id"
//            //." AND NOT (requestType.startDate >= requestType2.endDate OR requestType.endDate <= requestType2.startDate)"  //detect overlap
//            ." AND (requestType.startDate < requestType2.startDate AND requestType.endDate < requestType2.startDate)"     //no overlap
//            .")"
//        );

        //TODO: select user, distinct start, end dates
        //$dql->addSelect("DISTINCT (requestBusiness.startDate) as startDate ");
        //$dql->groupBy('requestBusiness.startDate','requestBusiness.endDate','requestVacation.startDate','requestVacation.endDate');
        //$dql->groupBy('request.user,requestBusiness.startDate,requestBusiness.endDate,requestVacation.startDate,requestVacation.endDate');
        //$dql->distinct('requestBusiness.startDate','requestBusiness.endDate','requestVacation.startDate','requestVacation.endDate');
        //$dql->groupBy('request.user,requestType.startDate,requestType.endDate');
        //$dql->groupBy('request.id');
        //}

        $query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";
        //echo "dql=".$dql."<br>";

        $query->setParameters( array(
            'userId' => $user->getId(),
            'status' => $status
        ));

        if( $asObject ) {
            $requests = $query->getResult();
            return $requests;
        } else {

            //testing
            $requests = $query->getResult();
            foreach( $requests as $request ) {
                $thisNumberOfDays = $request->getTotalDays($status,$requestTypeStr);
                $finalStartEndDatesArr = $request->getFinalStartEndDates();
                $startendStr = $finalStartEndDatesArr['startDate']->format('Y/m/d')."-".$finalStartEndDatesArr['endDate']->format('Y/m/d');
                echo "request = ".$request->getId()." ".$startendStr.": days=".$thisNumberOfDays."<br>";
                $numberOfDays = $numberOfDays + (int)$thisNumberOfDays;
            }
            echo "### get numberOfDays = ".$numberOfDays."<br><br>";
            return $numberOfDays;
            //EOF testing

            if(0) {
                $numberOfDaysRes = $query->getSingleResult();
                $numberOfDays = $numberOfDaysRes['numberOfDays'];
            } else {
                //$numberOfDaysRes = $query->getOneOrNullResult();
                $numberOfDaysItems = $query->getResult();
                if( $numberOfDaysItems ) {
                    echo "numberOfDaysItems count=".count($numberOfDaysItems)."<br>";
                    //$numberOfDaysItems = $numberOfDaysRes['numberOfDays'];
                    if( count($numberOfDaysItems) > 1 ) {
                        //$logger = $this->container->get('logger');
                        //$logger->warning('Logical error: found more than one SUM: count='.count($numberOfDaysItems));
                    }
                    foreach( $numberOfDaysItems as $numberOfDaysItem ) {
                        echo "+numberOfDays = ".$numberOfDaysItem['numberOfDays']."; count=".$numberOfDaysItem['totalCount']."<br>";
                        $numberOfDays = $numberOfDays + $numberOfDaysItem['numberOfDays'];
                    }
                    echo "### get numberOfDays = ".$numberOfDays."<br><br>";
                }
            }
        }

        return $numberOfDays;
    }
    public function getApprovedYearDays( $user, $requestTypeStr, $startStr=null, $endStr=null, $type=null, $asObject=false, $status='approved', $bruteForce=false ) {

        //echo $type.": requestTypeStr=".$requestTypeStr."<br>";
        $numberOfDays = 0;

        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");

        if( $bruteForce == true ) {
            $asObject = true;
        }

        if( $asObject ) {
            $dql->select('request');
        } else {
            $dql->select('DISTINCT user.id, requestType.startDate, requestType.endDate, requestType.numberOfDays as numberOfDays');
            //$dql->select('SUM(requestType.numberOfDays) as numberOfDays');
        }

//        $dql->innerJoin(
//            "AppVacReqBundle:VacReqRequest",
//            "request2",
//            "WITH",
//            "request2.id <> request.id AND request2.user = request.user"
//        );

        $dql->leftJoin("request.user", "user");

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
            //$dql->leftJoin("request2.requestBusiness", "requestType2");
            //$joinStr = " LEFT JOIN request2.requestBusiness requestType2";
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
            //$dql->leftJoin("request2.requestVacation", "requestType2");
            //$joinStr = " LEFT JOIN request2.requestVacation requestType2";
        }

        $dql->where("requestType.id IS NOT NULL AND user.id = :userId AND requestType.status = :status");

        // |----|year|-----start-----end-----|year+1|----|
        // |----|2015-07-01|-----start-----end-----|2016-06-30|----|
        if( $type == "inside" && $startStr && $endStr ) {
            //echo "range=".$startStr." > ".$endStr."<br>";
            $dql->andWhere("requestType.startDate >= '" . $startStr . "'" . " AND requestType.endDate < " . "'" . $endStr . "'");
        }

        // |-----start-----|year|-----end-----|year+1|----|
        // |-----rstart-----|2015-07-01|-----rend-----|2016-06-30|----|
        if( $type == "before" && $startStr ) {
            //echo "startStr=".$startStr."<br>";
            $dql->andWhere("requestType.startDate < '" . $startStr . "'" . " AND requestType.endDate > '".$startStr."'"); // . " AND requestType.endDate > " . "'" . $startStr . "'");
        }

        // |----|year|-----start-----|year+1|-----end-----|
        // |----|2015-07-01|-----start-----|2016-06-30|-----end-----|
        if( $type == "after" && $startStr && $endStr ) {
            //echo "sql endStr=".$endStr."<br>";
            //$dql->andWhere("requestType.endDate > '" . $endStr . "'" . " AND requestType.startDate < '".$endStr."'");  // . " AND requestType.endDate < " . "'" . $endStr . "'");
            //$dql->andWhere("requestType.startDate > '" . $startStr . "'" . " AND requestType.endDate > " . "'" . $endStr . "'");
            $dql->andWhere("requestType.startDate < '" . $endStr . "'" . " AND requestType.endDate > '".$endStr."'");
        }

        $query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";
        //echo "dql=".$dql."<br>";

        $query->setParameters( array(
            'userId' => $user->getId(),
            'status' => $status
        ));

        //bruteForce not used!!! Instead, we prevent to submit and approve overlap requests
        if( $bruteForce == true ) {
            $requests = $query->getResult();
            $numberOfDays = $this->getNotOverlapNumberOfWorkingDays($requests,$requestTypeStr);
            //echo "bruteForce days=".$numberOfDays."<br>";
            return $numberOfDays;
        }

        if( $asObject ) {
            $requests = $query->getResult();
            return $requests;
        } else {

//            //sum the number of days
//            $requests = $query->getResult();
//            foreach( $requests as $request ) {
//                $thisNumberOfDays = $request->getTotalDays($status,$requestTypeStr);
//                $finalStartEndDatesArr = $request->getFinalStartEndDates();
//                $startendStr = $finalStartEndDatesArr['startDate']->format('Y/m/d')."-".$finalStartEndDatesArr['endDate']->format('Y/m/d');
//                echo "request = ".$request->getId()." ".$startendStr.": days=".$thisNumberOfDays."<br>";
//                $numberOfDays = $numberOfDays + (int)$thisNumberOfDays;
//            }
//            echo "### get numberOfDays = ".$numberOfDays."<br><br>";
//            return $numberOfDays;
//            //EOF sum the number of days
//            //////////////////////////////////////////////////////////////

            if(0) {
                $numberOfDaysRes = $query->getSingleResult();
                $numberOfDays = $numberOfDaysRes['numberOfDays'];
            } else {
                //$numberOfDaysRes = $query->getOneOrNullResult();
                $numberOfDaysItems = $query->getResult();
                if( $numberOfDaysItems ) {
                    //echo "numberOfDaysItems count=".count($numberOfDaysItems)."<br>";
                    //$numberOfDaysItems = $numberOfDaysRes['numberOfDays'];
                    if( count($numberOfDaysItems) > 1 ) {
                        //$logger = $this->container->get('logger');
                        //$logger->warning('Logical error: found more than one SUM: count='.count($numberOfDaysItems));
                    }
                    foreach( $numberOfDaysItems as $numberOfDaysItem ) {
                        //echo "+numberOfDays = ".$numberOfDaysItem['numberOfDays']."; count=".$numberOfDaysItem['totalCount']."<br>";
                        //echo "+numberOfDays = ".$numberOfDaysItem['numberOfDays']."<br>";
                        $numberOfDays = $numberOfDays + $numberOfDaysItem['numberOfDays'];
                    }
                    //echo "### get numberOfDays = ".$numberOfDays."<br><br>";
                }
            }
        }

        return $numberOfDays;
    }

//    //check for overlapped requests
//    public function getNotOverlapNumberOfWorkingDays( $requests, $requestTypeStr ) {
//        $logger = $this->container->get('logger');
//        $overlap = false;
//        $numberOfDays = 0;
//        $overlapRequests = array();
//        $dateRanges = array();
//        foreach( $requests as $request ) {
//            echo "request=".$request->getId()."<br>";
//            $thisDateRange = $request->getFinalStartEndDates($requestTypeStr);
//
//            foreach( $dateRanges as $requestId=>$dateRange ) {
//                $msg = "";
//                //overlap condition: (StartA <= EndB) and (EndA >= StartB)
//                if( ($thisDateRange['startDate'] <= $dateRange['endDate']) && ($thisDateRange['endDate'] >= $dateRange['startDate']) ) {
//                    //overlap!
//                    if( $thisDateRange['startDate'] == $dateRange['startDate'] && $thisDateRange['endDate'] == $dateRange['endDate'] ) {
//                        $msg = "Exact ";
//                    } else {
//                        $startDateStr = $thisDateRange['startDate']->format('Y/m/d');
//                        $endDateStr = $thisDateRange['endDate']->format('Y/m/d');
//                        $msg .= $requestId . ": overlap dates ID=" . $request->getId() .
//                            "; status=" . $request->getStatus() . "; dates=" . $startDateStr . "-" . $endDateStr.
//                            "; created=".$request->getCreateDate()->format('Y/m/d');
//                        echo $msg . "<br>";
//                        $logger->error($msg);
//                        $overlap = true;
//                        $overlapRequests[] = $request;
//                    }
//                    //TODO:
//                } else {
//                    //not overlap
//                    $dateRanges[$request->getId()] = $thisDateRange;
//                    $days = $request->getTotalDaysByType($requestTypeStr);
//                    $numberOfDays = $numberOfDays + $days;
//                    echo "not overlap dates; days=".$days."<br>";
//                }
//            }
//
//            if( count($dateRanges) == 0 ) {
//                $dateRanges[$request->getId()] = $thisDateRange;
//            }
//        }
//
//        return $overlapRequests;
//        //return $overlap;
//        //echo "unique days=".$numberOfDays."<br>";
//        return $numberOfDays;
//    }

    public function getHeaderOverlappedMessage($user) {
        //check for overlapped requests
        $overlappedMessage = null;
        $overlapRequests = $this->getOverlappedUserRequests($user);
        if( count($overlapRequests) > 0 ) {
            $overlappedRequestHrefs = array();
            foreach( $overlapRequests as $overlapRequest ) {
                $overlapRequestLink = $this->container->get('router')->generate(
                    'vacreq_show',
                    array(
                        'id' => $overlapRequest->getId(),
                    )
                //UrlGeneratorInterface::ABSOLUTE_URL
                );
                $thisDateRange = $overlapRequest->getFinalStartEndDates('requestVacation');
                //$startDateStr = $thisDateRange['startDate']->format('Y/m/d');
                //$endDateStr = $thisDateRange['endDate']->format('Y/m/d');
                $thisDateRange = "(".$thisDateRange['startDate']->format('Y/m/d')."-".$thisDateRange['endDate']->format('Y/m/d').")";
                $overlapRequestHref = '<a href="'.$overlapRequestLink.'">ID #'.$overlapRequest->getId().' '.$thisDateRange.'</a>';
                $overlappedRequestHrefs[] = $overlapRequestHref;
            }
            $overlappedMessage = "You have ".count($overlapRequests)." overlapping approved vacation request(s) for the current academic year: <br>".implode("<br>",$overlappedRequestHrefs);
            $overlappedMessage .= "<br>This will affect the accuracy of the calculations of the total approved and carry over days.";
            $overlappedMessage .= "<br>You can fix these overlapped vacation requests by canceling them (click a 'Request Cancellation' action link in 'My Requests' page).";
        }
        return $overlappedMessage;
    }

    //get all user's overlapped requests
    public function getOverlappedUserRequests( $user, $currentYear=true, $log=false ) {

        //1) get all user approved vacation requests
        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');

        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("request.requestType", "requestType");
        $dql->leftJoin("request.requestVacation", "requestVacation");

        $dql->where("requestType.abbreviation = 'business-vacation'");
        $dql->andWhere("requestVacation.status = 'approved'");
        $dql->andWhere("user.id = ".$user->getId());

        if( $currentYear ) {
            $dates = $this->getCurrentAcademicYearStartEndDates();
            //echo "dates=".$dates['startDate']." == ".$dates['endDate']."<br>";
            $currentYearStartDate = $dates['startDate'];
            $dql->andWhere("requestVacation.startDate > '".$currentYearStartDate."'");
        }

        $dql->orderBy('request.createDate', 'ASC');

        $query = $this->em->createQuery($dql);

        $requests = $query->getResult();
        //echo "requests to analyze=".count($requests)."<br>";

        $overlapRequests = $this->checkOverlapRequests($requests,'requestVacation',$log);
        //echo "overlapRequests=".count($overlapRequests)."<br>";

        return $overlapRequests;
    }
    //check for overlapped requests
    public function checkOverlapRequests( $requests, $requestTypeStr, $log=false ) {
        $logger = $this->container->get('logger');
        //$overlap = false;
        $overlapRequests = new ArrayCollection();
        $dateRanges = array();
        foreach( $requests as $request ) {

            $thisDateRange = $request->getFinalStartEndDates($requestTypeStr);
            //echo "check ID=" .$request->getId(). "<br>";

            foreach( $dateRanges as $requestId=>$requestInfo ) {

                $dateRange = $requestInfo['dateRange'];
                $currentRequest = $requestInfo['request'];

                //overlap condition: (StartA <= EndB) and (EndA >= StartB)
                if( ($thisDateRange['startDate'] <= $dateRange['endDate']) && ($thisDateRange['endDate'] >= $dateRange['startDate']) ) {
                    //overlap!
                    //echo "overlap ID=" .$requestId. "<br>";

                    //$overlapRequests[] = $request;
                    if( !$overlapRequests->contains($request) ) {
                        $overlapRequests->add($request);
                    }
                    if( !$overlapRequests->contains($currentRequest) ) {
                        $overlapRequests->add($currentRequest);
                    }

                    if( $thisDateRange['startDate'] == $dateRange['startDate'] && $thisDateRange['endDate'] == $dateRange['endDate'] ) {
                        $msgPrefix = "Exact";
                    } else {
                        $msgPrefix = "";
                        //$overlap = true;
                        //$overlapRequests[] = $request;
                    }

                    if( $log ) {
                        $firstRequest = "#".$requestId."(".$dateRange['startDate']->format('Y/m/d')."-".$dateRange['endDate']->format('Y/m/d').")";

                        $startDateStr = $thisDateRange['startDate']->format('Y/m/d');
                        $endDateStr = $thisDateRange['endDate']->format('Y/m/d');
                        $secondRequest = "#".$request->getId()."(".$startDateStr."-".$endDateStr.")";

                        $msg = $msgPrefix. " Overlap: ".$firstRequest." and " . $secondRequest;
                        //"; created=".$request->getCreateDate()->format('Y/m/d');

                        //echo $msg . "<br>";
                        $logger->error($msg);
                    }

                } else {
                    //not overlap
                    //echo "not overlap ID=" .$requestId. "<br>";
                    //$dateRanges[$request->getId()] = $thisDateRange;
                    $dateRanges[$request->getId()] = array('dateRange'=>$thisDateRange,'request'=>$request);
                }

            }//foreach $dateRanges

            if( count($dateRanges) == 0 ) {
                $dateRanges[$request->getId()] = array('dateRange'=>$thisDateRange,'request'=>$request);
            }

        }//foreach $requests

        //return $overlap;
        return $overlapRequests;
    }
    public function checkRequestForOverlapDates( $user, $subjectRequest ) {
        //$logger = $this->container->get('logger');
        //get all user approved vacation requests
        $requestTypeStr = "requestVacation";

        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');

        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("request.requestType", "requestType");
        $dql->leftJoin("request.requestVacation", "requestVacation");

        $dql->where("requestType.abbreviation = 'business-vacation'");
        $dql->andWhere("requestVacation.status='approved'");
        $dql->andWhere("user.id=".$user->getId());

        $dql->orderBy('request.id');

        $query = $this->em->createQuery($dql);

        $requests = $query->getResult();
        //EOF get all user approved vacation requests

        $overlappedRequests = array();

        $subjectDateRange = $subjectRequest->getFinalStartEndDates($requestTypeStr);
        //dump($subjectDateRange);
        if( !$subjectDateRange ) {
            return $overlappedRequests;
        }
        if( is_array($subjectDateRange) && count($subjectDateRange) == 0 ) {
            return $overlappedRequests;
        }

        //$overlappedIds = array();
        foreach( $requests as $request ) {
            //echo 'check reqid='.$request->getId()."<br>";
            $thisDateRange = $request->getFinalStartEndDates($requestTypeStr);

            $thisStartDate = $thisDateRange['startDate'];
            $subjectStartDate = $subjectDateRange['startDate'];
            //echo "subjectStartDate=$subjectStartDate <br>";
            $thisEndDate = $thisDateRange['endDate'];
            $subjectEndDate = $subjectDateRange['endDate'];
            if(
                ($thisStartDate <= $subjectStartDate)
                &&
                ($thisEndDate >= $subjectEndDate)
            )
            {
                $overlappedRequests[] = $request;
            }

            //$msg = "";
            //overlap condition: (StartA <= EndB) and (EndA >= StartB)
//            if( ($thisDateRange['startDate'] <= $subjectDateRange['endDate']) && ($thisDateRange['endDate'] >= $subjectDateRange['startDate']) ) {
//                $overlappedRequests[] = $request;
//            }

        }//foreach requests
        return $overlappedRequests;
    }
    public function hasOverlappedExactly( $subjectRequest, $overlappedRequests ) {
        $requestTypeStr = "requestVacation";
        $subjectDateRange = $subjectRequest->getFinalStartEndDates($requestTypeStr);
        foreach( $overlappedRequests as $overlappedRequest ) {
            $thisDateRange = $overlappedRequest->getFinalStartEndDates($requestTypeStr);
            $thisStartDate = $thisDateRange['startDate'];
            $subjectStartDate = $subjectDateRange['startDate'];
            $thisEndDate = $thisDateRange['endDate'];
            $subjectEndDate = $subjectDateRange['endDate'];
            if(
                ($thisStartDate == $subjectStartDate)
                &&
                ($thisEndDate == $subjectEndDate)
            )
            {
                return true;
            }
//            if(
//                ($thisDateRange['startDate'] == $subjectDateRange['startDate'])
//                &&
//                ($thisDateRange['endDate'] == $subjectDateRange['endDate']) )
//            {
//                return true;
//            }

        }
        return false;
    }
    public function getOverlappedMessage( $subjectRequest, $overlappedRequests, $absolute=null, $short=false ) {
        //$errorMsg = 'This request ID #'.$entity->getId().' has overlapped vacation date range with a previous approved vacation request(s) with ID #' . implode(',', $overlappedRequestIds);
        $errorMsg = null;
        //Your request includes dates (MM/DD/YYYY, MM/DD/YYYY, MM/DD/YYYY) already covered by your previous request(s) (Request ID LINK #1, Request ID LINK #2, Request ID LINK #3). Please exclude these dates from this request before re-submitting.
        if( count($overlappedRequests) > 0 ) {
            $dates = array();
            $hrefs = array();
            foreach( $overlappedRequests as $overlappedRequest ) {
                //echo "overlapped re id=".$overlappedRequest->getId()."<br>";

                $finalDates = $overlappedRequest->getFinalStartEndDates('requestVacation');
                $dates[] = $finalDates['startDate']->format('m/d/Y')."-".$finalDates['endDate']->format('m/d/Y');

                if( $absolute ) {
                    $absoluteFlag = UrlGeneratorInterface::ABSOLUTE_URL;
                } else {
                    $absoluteFlag = null;
                }
                $link = $this->container->get('router')->generate(
                    'vacreq_show',
                    array(
                        'id' => $overlappedRequest->getId(),
                    ),
                    $absoluteFlag
                    //UrlGeneratorInterface::ABSOLUTE_URL
                );
                if( $absolute ) {
                    $href = 'Request ID '.$overlappedRequest->getId()." ".$link;
                } else {
                    $href = '<a href="'.$link.'">Request ID '.$overlappedRequest->getId().'</a>';
                }

                $hrefs[] = $href;
            }

            $errorMsg = "This request includes dates ".implode(", ",$dates)." already covered by your previous request(s) (".implode(", ",$hrefs).").";

            if( !$short ) {
                $errorMsg .= " Please exclude these dates from this request before re-submitting.";
            }
        }

        return $errorMsg;
    }

    public function getNumberOfWorkingDaysBetweenDates( $starDate, $endDate ) {
        $starDateStr = $starDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');
        //echo $starDateStr . " --- " . $endDateStr ."<br>";
        $holidays = array();
        //$holidays = ['*-12-25', '*-01-01', '*-07-04']; # variable and fixed holidays
        //return $this->getWorkingDays($starDateStr,$endDateStr,$holidays);
        return $this->number_of_working_days($starDateStr,$endDateStr,$holidays);
    }
    //http://stackoverflow.com/questions/336127/calculate-business-days
    //The function returns the no. of business days between two dates and it skips the holidays
    function getWorkingDays($startDate,$endDate,$holidays){
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);


        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
        $days = ($endDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);

        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        }
        else {
            // (edit by Tokes to fix an edge case where the start day was a Sunday
            // and the end day was NOT a Saturday)

            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            }
            else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }

        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
        //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0 )
        {
            $workingDays += $no_remaining_days;
        }

        //We subtract the holidays
        foreach($holidays as $holiday){
            $time_stamp=strtotime($holiday);
            //If the holiday doesn't fall in weekend
            if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
                $workingDays--;
        }

        return $workingDays;
    }
    function number_of_working_days($from, $to, $holidayDays) {
        $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Monday, ...)
        //$holidayDays = ['*-12-25', '*-01-01', '2013-12-23']; # variable and fixed holidays

        $from = new \DateTime($from);
        $to = new \DateTime($to);
        $to->modify('+1 day');
        $interval = new \DateInterval('P1D');
        $periods = new \DatePeriod($from, $interval, $to);

        $days = 0;
        foreach ($periods as $period) {
            if (!in_array($period->format('N'), $workingDays)) continue;
            if (in_array($period->format('Y-m-d'), $holidayDays)) continue;
            if (in_array($period->format('*-m-d'), $holidayDays)) continue;
            $days++;
        }
        return $days;
    }

    //construct the academic year edge date between request's start and end dates, if request is completely inside then return null
    //$position describes which academic year edge to construct. In case if the request spans over couple years (probably impossible case).
    // rstart--|y|--|y+1|--|y+2|--rend => position==first=>return y; position==last=>return y+2
    public function getAcademicYearEdgeDateBetweenRequestStartEnd( $request, $position="first" ) {
        $userSecUtil = $this->container->get('user_security_utility');

        $finalStartEndDates = $request->getFinalStartEndDates();
        $finalStartDate = $finalStartEndDates['startDate'];
        $finalEndDate = $finalStartEndDates['endDate'];
        $startDateMD = $finalStartDate->format('m-d');
        $endDateMD = $finalEndDate->format('m-d');

        //academicYearStart
        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart');
        if( !$academicYearStart ) {
            throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
        }
        //academicYearStart String
        $academicYearStartMD = $academicYearStart->format('m-d');

        //academicYearEnd: June 30
        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd');
        if( !$academicYearEnd ) {
            throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
        }
        //academicYearEnd String
        $academicYearEndMD = $academicYearEnd->format('m-d');

        //check if request is completely inside then return null
        if( $academicYearStart < $startDateMD && $academicYearEndMD > $endDateMD ) {
            //echo "request is completely inside";
            return null;
        }

        // rstart--|y|--|y+1|--|y+2|--rend => position==first=>return y; position==last=>return y+2
        //return y-$academicYearStartMD
        if( $position == "first" ) {
            $year = $finalStartDate->format('Y');
            $academicYearEdgeStr = $year . "-" . $academicYearStartMD;
        }

        //return (y+2)-$academicYearEndMD
        if( $position == "last" ) {
            $year = $finalEndDate->format('Y');
            $academicYearEdgeStr = $year . "-" . $academicYearEndMD;
        }

        return $academicYearEdgeStr;
    }
    //construct date string of the request's academical year edge - start (2016-06-30) or end (2016-07-01)
    public function getRequestEdgeAcademicYearDate( $request, $edge ) {
        $userSecUtil = $this->container->get('user_security_utility');

        //academicYearEdge
        $academicYearEdge = $userSecUtil->getSiteSettingParameter('academicYear'.$edge);
        if( !$academicYearEdge ) {
            throw new \InvalidArgumentException('academicYear'.$edge.' is not defined in Site Parameters.');
        }

        //academicYearEdge
        $academicYearEdgeStr = $academicYearEdge->format('m-d');

        //get request's academic year
        $academicYearArr = $this->getRequestAcademicYears($request);
        if( count($academicYearArr) > 0 ) {
            $yearsArr = $this->getYearsFromYearRangeStr($academicYearArr[0]);
            $edgeYear = $yearsArr[0];
            $academicYearEdgeStr = $edgeYear."-".$academicYearEdgeStr;
        } else {
            throw new \InvalidArgumentException("Request's academic ".$edge." year is not defined.");
        }
        //echo "academicYearEdgeStr=".$academicYearEdgeStr."<br>";

        return $academicYearEdgeStr;
    }

    //construct date string of the academical year edge - start (2016-06-30) or end (2016-07-01)
    public function getEdgeAcademicYearDate( $year, $edge ) {
        $userSecUtil = $this->container->get('user_security_utility');

        //academicYearEdge
        $academicYearEdge = $userSecUtil->getSiteSettingParameter('academicYear'.$edge);
        if( !$academicYearEdge ) {
            throw new \InvalidArgumentException('academicYear'.$edge.' is not defined in Site Parameters.');
        }

        //academicYearEdge
        $academicYearEdgeStr = $academicYearEdge->format('m-d');

        if( $edge == "Start" || $edge == "start" ) {
            $year = $int = (int)$year - 1;
        }

        $academicYearEdgeStr = $year."-".$academicYearEdgeStr;
        //echo "academicYearEdgeStr=".$academicYearEdgeStr."<br>";

        return $academicYearEdgeStr;
    }


    public function getRequestAcademicYears( $request ) {

        $academicYearArr = array();

        if( $request->getRequestType() && $request->getRequestType()->getAbbreviation() == "carryover" ) {
            if( $request->getSourceYear() && $request->getDestinationYear() ) {
                $sourceYear = $request->getSourceYear();
                $academicYearArr[] = $sourceYear."-".((int)$sourceYear+1);
                $destinationYear = $request->getDestinationYear();
                $academicYearArr[] = $destinationYear."-".((int)$destinationYear+1);
            }
            return $academicYearArr;
        }

        //return "2014-2015, 2015-2016";
        $academicYearStr = null;
        $userSecUtil = $this->container->get('user_security_utility');

        //academicYearStart: July 01
        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart');
        if( !$academicYearStart ) {
            throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
        }
        //academicYearEnd: June 30
        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd');
        if( !$academicYearEnd ) {
            throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
        }

        //$res['academicYearStart'] = $academicYearStart;
        //$res['academicYearEnd'] = $academicYearEnd;

        $dates = $request->getFinalStartEndDates();
        $startDate = $dates['startDate'];
        $endDate = $dates['endDate'];

        //echo "startDate= ".$startDate->format('Y-m-d')."<br>";
        //echo "endDate= ".$endDate->format('Y-m-d')."<br>";

        $startDateMD = $startDate->format('m-d');
        $endDateMD = $endDate->format('m-d');

        $startYear = $startDate->format('Y');
        $endYear = $endDate->format('Y');

        //calculate year difference (span)
        //$yearDiff = $endYear - $startYear;
        //$yearDiff = $yearDiff + 1;

        $academicYearStartMD = $academicYearStart->format('m-d');
        //$academicStartDateStr = $startYear."-".$academicYearStartMD;
        //echo "academicStartDateStr= ".$academicStartDateStr."<br>";
        //$academicStartDate = new \DateTime($academicStartDateStr);

        //$endYear = $endYear + $yearDiff;
        $academicYearEndMD = $academicYearEnd->format('m-d');
        //$academicEndDateStr = $endYear."-".$academicYearEndMD;
        //echo "academicEndDateStr= ".$academicEndDateStr."<br>";
        //$academicEndDate = new \DateTime($academicEndDateStr);

        //case 1: start and end dates are inside of academic year
        //if( $startDateMD >= $academicYearStartMD && $endDateMD <= $academicYearEndMD ) {
            //echo "case 1: start and end dates are inside of academic year <br>";
        //}

        //case 2: start date is before start of academic year
        if( $startDateMD < $academicYearStartMD ) {
            //echo "case 2: start date is before start of academic year <br>";
            $startYear = $startYear - 1;
        }

        //case 3: end date is after end of academic year
        if( $endDateMD > $academicYearEndMD ) {
            //echo "case 3: end date is after end of academic year <br>";
            $endYear = $endYear + 1;
        }

        //$academicYearStr = "2014-2015, 2015-2016";
        //$academicYearStr = $startYear . "-" . $endYear;

        for( $year=$startYear; $year < $endYear; $year++ ) {
            //$academicYearStr = $startYear . "-" . $endYear;
            $endtyear = $year + 1;
            $academicYearArr[] = $year."-".$endtyear;
        }

        //$academicYearStr = implode(", ",$academicYearArr);

        return $academicYearArr;
    }

    //return format: Y-m-d
    public function getCurrentAcademicYearStartEndDates($asDateTimeObject=false, $yearOffset=null) {
        $userSecUtil = $this->container->get('user_security_utility');
        //academicYearStart: July 01
        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart');
        if( !$academicYearStart ) {
            throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
        }
        //academicYearEnd: June 30
        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd');
        if( !$academicYearEnd ) {
            throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
        }

        $startDateMD = $academicYearStart->format('m-d');
        $endDateMD = $academicYearEnd->format('m-d');

        //$currentYear = new \DateTime();
        $nowDate = new \DateTime(); //2016-07-15
        //testing
        if( 0 ) {
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2015-08-30"); //testing: expected 2015-2016
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2016-06-30"); //testing: expected 2015-2016
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2016-08-30"); //testing: expected 2016-2017
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2017-01-30"); //testing: expected 2016-2017
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2017-08-30"); //testing: expected 2017-2018
            $nowDate = \DateTime::createFromFormat('Y-m-d', "2018-08-26");
            //$nowDate = \DateTime::createFromFormat('Y-m-d', "2018-12-26");
        }

        $currentYear = $nowDate->format('Y'); //endDate

        //check if current date < academicYearStart date
        $academicYearStartDateStr = $currentYear."-".$startDateMD;
        $academicYearStartDate = \DateTime::createFromFormat('Y-m-d', $academicYearStartDateStr);
        //echo "compare: current date ".$nowDate->format('Y-M-d')." < ".$academicYearStartDate->format('Y-M-d')."<br>";
        if( $nowDate < $academicYearStartDate ) {
            $currentYear = $currentYear - 1; //testing
            //echo "adjust currentYear: $currentYear - 1<br>";
        }
        //echo "currentYear=".$currentYear."<br>";

        $previousYear = $currentYear - 1; //startDate

        $startDate = $previousYear."-".$startDateMD;
        $currentYearStartDate = \DateTime::createFromFormat('Y-m-d', $startDate);

        //echo "nowDate=".$nowDate->format('Y-M-d')."<br>";
        //echo "currentYearStartDate=".$currentYearStartDate->format('Y-M-d')."<br>";
        if( $nowDate > $currentYearStartDate ) {
            $previousYear = $currentYear;
            $currentYear = $currentYear + 1;
            //echo "nowDate>currentYearStartDate: "."previousYear=$previousYear"."; currentYear=$currentYear <br>";
        } else {
            //echo "else: previousYear=$previousYear"."; currentYear=$currentYear <br>";
        }

        if( $yearOffset ) {
            $previousYear = $previousYear + $yearOffset;
            $currentYear = $currentYear + $yearOffset;
        }

        $startDate = $previousYear."-".$startDateMD;
        $endDate = $currentYear."-".$endDateMD;
        //exit('<br> exit: startDate='.$startDate.'; endDate='.$endDate); //testing

        if( $asDateTimeObject ) {
            $startDate = \DateTime::createFromFormat('Y-m-d', $startDate);
            $endDate = \DateTime::createFromFormat('Y-m-d', $endDate);
        }

        return array(
            'startDate'=> $startDate,
            'endDate'=> $endDate,
        );
    }

    public function getApprovedRequestStartedBetweenDates( $requestTypeStr, $startDate, $endDate ) {

        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql = $repository->createQueryBuilder('request');

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
        }

        $dql->where("requestType.id IS NOT NULL AND requestType.status = :statusApproved");
        $dql->andWhere('(requestType.startDate BETWEEN :startDate and :endDate)');

        $query = $this->em->createQuery($dql);

        $query->setParameter('statusApproved', 'approved');
        $query->setParameter('startDate', $startDate->format('Y-m-d H:i:s'));
        $query->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));

        //echo "dql=".$dql."<br>";

        $requests = $query->getResult();

        //echo "count=".count($requests)."<br>";

        return $requests;
    }

    //IF the person is away on the date the page is being loaded (approved request), show the heading "Away" and under it show one of three lines:
    //Vacation: StartDate - EndDate, Back on BackDate
    //Business Travel: StartDate - EndDate, Back on BackDate
    //Vacation + Business Travel: StartDate - EndDate, Back on BackDate
    //Followed by (if the phone number, email, or "other" field was filled in):
    //Emergency Contact Via: Phone - [field-from-phone]; Email - [field form email]; Other - [field form other]__
    public function getUserAwayInfo( $user ) {

        $dateformat = 'M d Y';
        $res = "";
        $resB = "";
        $resV = "";

        $requestsB = $this->getApprovedRequestToday( $user, 'requestBusiness' );
        $requestsV = $this->getApprovedRequestToday( $user, 'requestVacation' );

//        if( $requestB && $requestV ) {
//            //Vacation + Business Travel: StartDate - EndDate, Back on BackDate
//            $res = "Vacation + Business Travel:" . ;
//        }

        if( count($requestsB) > 0 ) {
            //Business Travel: StartDate - EndDate, Back on BackDate
            foreach( $requestsB as $request ) {
                $subRequest = $request->getRequestBusiness();
                $resB = "Business Travel: " . $subRequest->getStartDate()->format($dateformat) . " - " . $subRequest->getEndDate()->format($dateformat);
                $resB .= "<br>";

                $emergencyConatcs = $request->getEmergencyConatcs();
                if( $emergencyConatcs ) {
                    $resB .= "Emergency Contact Via: <br>" . "<strong>" . $emergencyConatcs . "</strong>";
                    $resB .= "<br>";
                }
            }
        }

        if( count($requestsV) > 0 ) {
            foreach( $requestsV as $request ) {
                $subRequest = $request->getRequestVacation();
                //Vacation: StartDate - EndDate, Back on BackDate
                $resV = "Vacation: " . $subRequest->getStartDate()->format($dateformat) . " - " . $subRequest->getEndDate()->format($dateformat);
                $resV .= "<br>";

                $emergencyConatcs = $request->getEmergencyConatcs();
                if( $emergencyConatcs ) {
                    $resV .= "Emergency Contact Via: <br>" . "<strong>" . $emergencyConatcs . "</strong>";
                    $resV .= "<br>";
                }
            }
        }

        if( $resB || $resV ) {
            $res = "<h4>Away:</h4>";
            $res .= '<div style="padding-left: 1em;">';
            $res .= $resB . $resV;
            $res .= '</div>';
        }

        $res = $res . "<br>";

        return $res;
    }
    public function getApprovedRequestToday( $user, $requestTypeStr ) {

        $today = new \DateTime();
        $todayStr = $today->format('Y-m-d');

        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql = $repository->createQueryBuilder('request');
        $dql->leftJoin("request.user", "user");

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
        }

        $dql->where("user.id = :userId AND requestType.id IS NOT NULL AND requestType.status = :statusApproved");

        $dql->andWhere('(:today BETWEEN requestType.startDate and requestType.endDate)');

        $query = $this->em->createQuery($dql);

        $query->setParameter('userId', $user->getId());
        $query->setParameter('statusApproved', 'approved');
        $query->setParameter('today', $todayStr);

        //echo "dql=".$dql."<br>";

        $requests = $query->getResult();

        //echo "count=".count($requests)."<br>";

        return $requests;
    }


    //find the first upper supervisor of this user's group
    public function getClosestSupervisor( $user ) {

        //1) get the submitter group Id of this user
        $groupParams = array('asObjectRole'=>true);
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
        $supervisorRoles = $this->getGroupsByPermission($user,$groupParams);
        //echo "supervisorRoles=".count($supervisorRoles)."<br>";

        //2) get a user with this role
        $supervisorsArr = array();
        foreach( $supervisorRoles as $supervisorRole ) {
            //echo "supervisorRole=".$supervisorRole."<br>";
//            //find users with this role
            $supervisors = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($supervisorRole->getName(),"infos.lastName",true);
            foreach( $supervisors as $supervisor ) {
                $supervisorsArr[] = $supervisor;
            }

        }

        //we can see which user to pick (user with the lowest role's institution) in case of multiple supervisors
        $supervisorUser = $supervisorsArr[0];
        //echo "supervisorUser=".$supervisorUser."<br>";

        return $supervisorUser;
    }

    //get all groups for this user children groups
    public function getAllGroupsByUser( $user ) {

        $groupParams = array();

        //1) get user groups
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'view-away-calendar');
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
        $userOrganizationalInstitutions = $this->getGroupsByPermission($user,$groupParams);
//        echo "user group:<br><pre>";
//        print_r($userOrganizationalInstitutions);
//        echo "</pre>";
//        foreach($userOrganizationalInstitutions as $organizationalInstitution) {
//            echo "user group=".$organizationalInstitution."<br>";
//        }

        //2) get user's supervisor groups
        //to get the select filter with all groups under the supervisor group, find the first upper supervisor of this group.
        if( $this->security->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
            $subjectUser = $user;
        } else {
            $groupParams['asSupervisor'] = true;
            $subjectUser = $this->getClosestSupervisor( $user );
        }
        //echo "subjectUser=".$subjectUser."<br>";
        if( !$subjectUser ) {
            $subjectUser = $user;
        }

        $supervisorOrganizationalInstitutions = $this->getGroupsByPermission($subjectUser,$groupParams);
//        echo "supervisor group:<br><pre>";
//        print_r($supervisorOrganizationalInstitutions);
//        echo "</pre>";

        //3) merge user and supervisor groups keeping original indexes
        $organizationalInstitutions = $userOrganizationalInstitutions + $supervisorOrganizationalInstitutions;
        $organizationalInstitutions = array_unique($organizationalInstitutions);

//        echo "res group:<br><pre>";
//        print_r($organizationalInstitutions);
//        echo "</pre>";

        return $organizationalInstitutions;
    }

    //get all user's organizational groups and children specified to permission
    //get only institutions from the same institutional tree:
    //if submitter has CYTOPATHOLOGY submitter role, then the each resulting institution should be equal or be a parent of CYTOPATHOLOGY
    public function getGroupsByPermission( $user, $params=array() ) {

        $asObject = ( array_key_exists('asObject', $params) ? $params['asObject'] : false);
        $asObjectRole = ( array_key_exists('asObjectRole', $params) ? $params['asObjectRole'] : false);
        $permissions = ( array_key_exists('permissions', $params) ? $params['permissions'] : null);
        $exceptPermissions = ( array_key_exists('exceptPermissions', $params) ? $params['exceptPermissions'] : null);
        $asSupervisor = ( array_key_exists('asSupervisor', $params) ? $params['asSupervisor'] : false);
        $asUser = ( array_key_exists('asUser', $params) ? $params['asUser'] : false);
        $statusArr = ( array_key_exists('statusArr', $params) ? $params['statusArr'] : array());

        $institutions = array();
        $addedArr = array();

        foreach( $permissions as $permission ) {

            $objectStr = $permission['objectStr'];
            $actionStr = $permission['actionStr'];
            //echo "### objectStr=".$objectStr.", actionStr=".$actionStr."### <br>";

            $roles = new ArrayCollection();

            if( count($roles)==0 && $this->security->isGranted('ROLE_VACREQ_ADMIN') ) {
                //echo "roles try 1<br>";
                if( !$asUser ) {
                    $roles = $this->em->getRepository('AppUserdirectoryBundle:User')->
                    findRolesByObjectActionInstitutionSite($objectStr, $actionStr, null, 'vacreq', null);
                }
            }
            if( count($roles)==0 && ($this->security->isGranted('ROLE_VACREQ_SUPERVISOR') || $asSupervisor) ) {
                //echo "roles for ROLE_VACREQ_SUPERVISOR<br>";
                //echo "roles try 2<br>";
                if( !$asUser ) {
                    $roles = $this->em->getRepository('AppUserdirectoryBundle:User')->
                    findUserChildRolesBySitePermissionObjectAction($user, 'vacreq', $objectStr, $actionStr);
                }
            }
            if( count($roles)==0 ) {
                //echo "roles try 3<br>";
                $roles = $this->em->getRepository('AppUserdirectoryBundle:User')->
                    findUserRolesBySitePermissionObjectAction($user,'vacreq',$objectStr,$actionStr);
            }

            //second try to get group. This is the case for changestatus-carryover action
            if( count($roles)==0 && $actionStr == "changestatus-carryover" ) {
                //echo "second try 4<br>";
                //get all changestatus-carryover roles: changestatus-carryover and create
                $childObjectStr = $objectStr;
                $childActionStr = "create";
                $roles = $this->em->getRepository('AppUserdirectoryBundle:User')->
                    findUserParentRolesBySitePermissionObjectAction($user,'vacreq',$objectStr,$actionStr,$childObjectStr,$childActionStr);
                //echo "1 role count=".count($roles)."<br>"; //testing this role count is 1 for wcmc pathology

                if( count($roles)==0 ) {
                    //echo "another try 5 for view-away-calendar action for role ROLE_VACREQ_OBSERVER_WCM_PATHOLOGY<br>";
                    $childActionStr = "view-away-calendar";
                    $roles = $this->em->getRepository('AppUserdirectoryBundle:User')->
                    findUserParentRolesBySitePermissionObjectAction($user,'vacreq',$objectStr,$actionStr,$childObjectStr,$childActionStr);
                }
                //echo "2 role count=".count($roles)."<br>";
            }

            //echo "### EOF ".$actionStr.": final role count=".count($roles)."### <br>";

//            $adminRole = false;
//            if( $this->security->isGranted('ROLE_VACREQ_ADMIN') ) {
//                //echo "admin<br>";
//                $adminRole = true;
//            }
            //echo "<br><br>";

            foreach( $roles as $role ) {

                //echo "###role=".$role."<br>";
                $include = true;
                $institution = $role->getInstitution();

                if( $institution ) {

                    //avoid duplication
                    if( !in_array($institution->getId(), $addedArr) ) {
                        $addedArr[] = $institution->getId();
                    } else {
                        continue;
                    }

                    //$statusArr: include only statuses provided by $statusArr
                    if( $statusArr && count($statusArr)>0 ) {
                        $statusOk = false;
                        foreach( $statusArr as $thisStatus ) {
                            $roleStatus = $role->getType();
                            if( $roleStatus == $thisStatus ) {
                                $statusOk = true;
                                continue;
                            }
                        }

                        if( !$statusOk ) {
                            continue;
                        }
                    }

                    if( $exceptPermissions ) {
                        foreach( $exceptPermissions as $exceptPermission ) {
                            //echo "exceptPermission: ".$exceptPermission['objectStr']."=>".$exceptPermission['actionStr']."<br>";
                            foreach( $role->getPermissions() as $permission ) {
                                $roleObjectStr = $permission->getPermission()->getPermissionObjectList()->getName();
                                $roleActionStr = $permission->getPermission()->getPermissionActionList()->getName();
                                //echo "except: ".$roleObjectStr."=>".$roleActionStr."<br>";
                                if( $roleObjectStr == $exceptPermission['objectStr'] && $roleActionStr == $exceptPermission['actionStr'] ) {
                                    //echo "!!!!!!!!except role=".$role."<br>";
                                    $include = false;
                                    continue;
                                }
                            }
                        }
                    }

                    if( $include == false ) {
                        //echo "exclude role=".$role."<br>";
                        continue;
                    }

                    if( $asObjectRole ) {
                        $institutions[] = $role;
                        continue;
                    }

                    if( $asObject ) {
                        $institutions[] = $institution;
                        continue;
                    }

                    //Clinical Pathology (for review by Firstname Lastname)
                    //find approvers with the same institution
                    $approverStr = $this->getApproversBySubmitterRole($role);
                    if( $approverStr ) {
                        $orgName = $institution . " (for review by " . $approverStr . ")";
                    } else {
                        $orgName = $institution;
                    }
                    //echo "orgName=".$orgName."<br>";

                    $institutions[$institution->getId()] = $orgName;

                }

            }//foreach roles

        }//foreach permissions

//        foreach( $institutions as $key=>$value) {
//            echo $key."=>".$value."<br>";
//        }

        return $institutions;
    }
    //TODO: replace by getGroupsByPermission
    //get user's organizational group
    //get only institutions from the same institutional tree:
    //if submitter has CYTOPATHOLOGY submitter role, then the each resulting institution should be equal or be a parent of CYTOPATHOLOGY
    public function getVacReqOrganizationalInstitutions( $user, $params=array() ) {

        $asObject = ( array_key_exists('asObject', $params) ? $params['asObject'] : false);
        //$requestTypeAbbreviation = ( array_key_exists('requestTypeAbbreviation', $params) ? $params['requestTypeAbbreviation'] : null);
        //$routeName = ( array_key_exists('routeName', $params) ? $params['routeName'] : null);
        $roleSubStrArr = ( array_key_exists('roleSubStrArr', $params) ? $params['roleSubStrArr'] : array("ROLE_VACREQ_"));

        $institutions = array();

        //get this user institutions associated with this site
        $partialRoleName = "ROLE_VACREQ_";
        $userRoles = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserRolesBySiteAndPartialRoleName($user, "vacreq", $partialRoleName, null, false);
        $userInsts = new ArrayCollection();
        foreach( $userRoles as $userRole ) {
            $roleInst = $userRole->getInstitution();
            if( $roleInst && !$userInsts->contains($roleInst) ) {
                $userInsts->add($roleInst);
                //echo "add to userInsts=".$roleInst."<br>";
            }
        }

        //get user's roles by site and role name array
        $submitterRoles = new ArrayCollection();
        foreach( $roleSubStrArr as $roleSubStr ) {
            if( $this->security->isGranted('ROLE_VACREQ_ADMIN') || $this->security->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
                //find all submitter role's institution
                $submitterSubRoles = $this->em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName("vacreq",$roleSubStr);
            } else {
                //echo "roleSubStr=".$roleSubStr."<br>";
                $submitterSubRoles = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserRolesBySiteAndPartialRoleName($user, "vacreq", $roleSubStr, null, false);
            }

            foreach( $submitterSubRoles as $submitterSubRole ) {
                if( $submitterSubRole && !$submitterRoles->contains($submitterSubRole) ) {
                    $submitterRoles->add($submitterSubRole);
                }
            }

        }

//        if( count($submitterRoles) == 0 ) {
//            //find all submitter role's institution
//            $submitterRoles = $this->em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName("vacreq",$requestRoleSubStr);
//        }
//        echo "roles count=".count($submitterRoles)."<br>";

        //get only SUBMITTER roles: filter roles by SUBMITTER string
//        foreach( $submitterRoles as $role ) {
//            echo "role=".$role."<br>";
//        }

        foreach( $submitterRoles as $submitterRole ) {

            //echo "submitterRole=".$submitterRole."<br>";
            $institution = $submitterRole->getInstitution();

            if( $institution ) {

                //get only institutions from the same institutional tree:
                //  if submitter has CYTOPATHOLOGY submitter role, then the each resulting institution should be equal or be a parent of CYTOPATHOLOGY
                //check if this institution is equal or under user's site institution
                if( $this->security->isGranted('ROLE_VACREQ_ADMIN') == false ) {
                    if( $this->em->getRepository('AppUserdirectoryBundle:Institution')->isNodeUnderParentnodes($userInsts, $institution) == false ) {
                        //echo "remove institution=".$institution."<br>";
                        continue;
                    }
                }
                //echo "add submitterRole=".$submitterRole."<br>";

                if( $asObject ) {
                    $institutions[] = $institution;
                    continue;
                }

                //Clinical Pathology (for review by Firstname Lastname)
                //find approvers with the same institution
                $approverStr = $this->getApproversBySubmitterRole($submitterRole);
                if( $approverStr ) {
                    $orgName = $institution . " (for review by " . $approverStr . ")";
                } else {
                    $orgName = $institution;
                }
                //echo "orgName=".$orgName."<br>";

                $institutions[$institution->getId()] = $orgName;
            }
        }

        //add request institution
//        if( $entity->getInstitution() ) {
//            $orgName = $institution . " (for review by " . $approverStr . ")";
//            $institutions[$entity->getInstitution()->getId()] = $orgName;
//        }
        //exit('1');

        return $institutions;
    }
    //$role - string; for example "ROLE_VACREQ_APPROVER_CYTOPATHOLOGY"
    public function getApproversBySubmitterRole( $role ) {
        $roleApprover = str_replace("SUBMITTER","APPROVER",$role);
        $approvers = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserByRole($roleApprover,"infos.lastName",true);

        $approversArr = array();
        foreach( $approvers as $approver ) {
            $approversArr[] = $approver->getUsernameShortest();
        }

        return implode(", ",$approversArr);
    }

    public function getSubmittersFromSubmittedRequestsByGroup( $groupId ) {

        //TODO: this might optimized to get user objects in one query. groupBy does not work in MSSQL.
        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");
        //$dql->select('request');
        $dql->select('DISTINCT (user) as submitter');
        $dql->addSelect('infos.lastName');
        //$dql->select('user');
        //$dql->addSelect('request');
        //$dql->groupBy("user");
        //$dql->addGroupBy("request");
        //$dql->addGroupBy("infos");
        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("user.infos", "infos");

        $dql->where("request.institution = :groupId");

        //Add a filter to the vacation request site for the "My Group" page: show stats only for current employees,
        // meaning check if the listed users have a non-empty "end" date in the Employment Period section of their profile.
        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->andWhere("employmentStatus.terminationDate IS NULL");

        $dql->orderBy('infos.lastName', 'ASC');

        $query = $this->em->createQuery($dql);

        $query->setParameters( array(
            'groupId' => $groupId
        ));

        $results = $query->getResult();
        //echo "count results=".count($results)."<br>";

        $submitters = array();
        foreach( $results as $result ) {
            //$submitters[] = $result->getUser();
            $user = $this->em->getRepository('AppUserdirectoryBundle:User')->find($result['submitter']);
            if( $user ) {
                $submitters[] = $user;
            } else {
                //exit('no user found');
            }
            //echo "user=".$result['submitter']."<br>";
            //echo "user=".$user."<br>";
            //print_r($result);
            //echo "res=".$result['id']."<br>";

        }
        //exit('1');

        return $submitters;
    }

    //$rolePartialNameArr - array of roles partial names. For example, array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR')
    public function hasRoleNameAndGroup( $rolePartialNameArr, $institutionId=null ) {
        if( $this->security->isGranted('ROLE_VACREQ_ADMIN') ) {
            return true;
        }

        $user = $this->security->getUser();
        //$sitename = "vacreq";

        //TODO: fix this by permission

        //get user allowed groups
        $groupParams = array(
            'roleSubStrArr' => $rolePartialNameArr, //array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR'),
            'asObject' => true
        );
        $groupInstitutions = $this->getVacReqOrganizationalInstitutions($user,$groupParams);
        //echo "inst count=".count($groupInstitutions)."<br>";

        //check if subject has at least one of the $groupInstitutions
        foreach( $groupInstitutions as $inst ) {
            //echo "inst=".$inst."<br>";
            if( $inst->getId() == $institutionId ) {
                return true;
            }
        }
        //echo "return false<br>";

        return false;
    }

    public function getSubmitterPhone($user) {

        //(a) prepopulate the phone number with the phone number from the user's profile
        $phones = $user->getAllPhones();
        if( count($phones) > 0 ) {
            return $phones[0]['phone'];
        }

        //(b) prepopulate from previous approved request (if there is one) for this user (person away)
        //$requests = $this->em->getRepository('AppVacReqBundle:VacReqRequest')->findByUser($user,array('ORDER'=>'approvedRejectDate'));
        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("request.requestBusiness", "requestBusiness");
        $dql->leftJoin("request.requestVacation", "requestVacation");

        $dql->where("user.id = :userId");
        $dql->andWhere("requestBusiness.status = :statusApproved OR requestVacation.status = :statusApproved");
        $dql->andWhere("request.phone IS NOT NULL");

        $dql->orderBy('request.createDate', 'DESC');

        $query = $this->em->createQuery($dql);

        $query->setParameters( array(
            'userId' => $user->getId(),
            'statusApproved' => 'approved'
        ));

        $requests = $query->getResult();

        if( count($requests) > 0 ) {
            $request = $requests[0];
            if ($request->getPhone()) {
                return $request->getPhone();
            }
        }

        //(c) prepopulate from the approved request before the last one

        return null;
    }

    public function setEmergencyInfo( $user, $request ) {
        $emergencyInfoArr = $this->getSubmitterEmergencyInfo($user);
        if( $emergencyInfoArr['mobile'] ) {
            $request->setAvailableViaCellPhone(true);
            $request->setAvailableCellPhone($emergencyInfoArr['mobile']);
        }
        if( $emergencyInfoArr['email'] ) {
            $request->setAvailableViaEmail(true);
            $request->setAvailableEmail($emergencyInfoArr['email']);
        }
    }
    public function getSubmitterEmergencyInfo($user) {

        $res = array(
            'mobile' => null,
            'email' => null
        );

        //(1a) prepopulate the cell phone number from the user's profile
        if ($res['mobile'] == null) {
            $homeLocation = $user->getHomeLocation();
            if ($homeLocation && $homeLocation->getMobile()) {
                $res['mobile'] = $homeLocation->getMobile();
            }
        }
        if ($res['mobile'] == null) {
            $mainLocation = $user->getMainLocation();
            if ($mainLocation && $mainLocation->getMobile()) {
                $res['mobile'] = $mainLocation->getMobile();
            }
        }
        if ($res['mobile'] == null) {
            $locations = $user->getLocations();
            foreach ($locations as $location) {
                if ($res['mobile'] == null) {
                    if ($location && $location->getMobile()) {
                        $res['mobile'] = $location->getMobile();
                    }
                } else {
                    break;
                }
            }
        }
        //(1b) prepopulate the email from the user's profile
        $email = $user->getEmail();
        if ($email) {
            $res['email'] = $email;
        }

        //(2a) prepopulate mobile from previous approved request (if there is one) for this user (person away)
        if( $res['mobile'] == null ) {
            $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
            $dql = $repository->createQueryBuilder("request");
            $dql->select('request');
            $dql->leftJoin("request.user", "user");
            $dql->where("user.id = :userId");
            $dql->andWhere("request.availableCellPhone IS NOT NULL");
            $dql->orderBy('request.createDate', 'DESC');

            $query = $this->em->createQuery($dql);

            $query->setParameters(array(
                'userId' => $user->getId(),
            ));

            $requests = $query->getResult();

            foreach( $requests as $request ) {
                if( $request->getAvailableCellPhone() ) {
                    $res['mobile'] = $request->getAvailableCellPhone();
                    break;
                }
            }
        }
        //(2b) prepopulate email from previous approved request (if there is one) for this user (person away)
        if( $res['email'] == null ) {
            $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
            $dql = $repository->createQueryBuilder("request");
            $dql->select('request');
            $dql->leftJoin("request.user", "user");
            $dql->where("user.id = :userId");
            $dql->andWhere("request.availableEmail IS NOT NULL");
            $dql->orderBy('request.createDate', 'DESC');

            $query = $this->em->createQuery($dql);

            $query->setParameters(array(
                'userId' => $user->getId(),
            ));

            $requests = $query->getResult();

            foreach( $requests as $request ) {
                if( $request->getAvailableEmail() ) {
                    $res['email'] = $request->getAvailableEmail();
                    break;
                }
            }
        }

        //(c) prepopulate from the approved request before the last one

        return $res;
    }


    //set cancel email to approver and email users
    public function sendCancelEmailToApprovers( $entity, $user, $status, $sendCopy=true ) {
        $subject = $entity->getRequestName()." ID #" . $entity->getId() . " " . ucwords($status);
        $message = $this->createCancelEmailBody($entity);
        return $this->sendGeneralEmailToApproversAndEmailUsers($entity,$subject,$message,$sendCopy);
    }
    public function createCancelEmailBody( $entity, $emailUser=null, $addText=null ) {
        //$break = "\r\n";
        $break = "<br>";

        //$message = "Dear " . $emailUser->getUsernameOptimal() . "," . $break.$break;
        $message = "Dear ###emailuser###," . $break.$break;

        if( $addText ) {
            $message .= $addText.$break.$break;
        }

        $requestName = $entity->getRequestName();

        $message .= $entity->getUser()." canceled/withdrew the ".$requestName." ID #".$entity->getId()." described below:".$break.$break;

        $message .= $entity->printRequest($this->container)."";

        $message .= $break.$break."**** PLEASE DO NOT REPLY TO THIS EMAIL ****";

        return $message;
    }

    //send the general emails to approver and email users with given subject and message body
    public function sendGeneralEmailToApproversAndEmailUsers( $entity, $subject, $originalMessage, $sendCopy=true ) {

        $logger = $this->container->get('logger');

        $institution = $entity->getInstitution();
        if( !$institution ) {
            //$logger->error("sendGeneralEmailToApproversAndEmailUsers: Request ".$entity->getId()." does not have institution");
            return null;
        }

        $emailUtil = $this->container->get('user_mailer_utility');
        //$break = "\r\n";
        $break = "<br>";

        //$requestName = $entity->getRequestName();

        $approvers = $this->getRequestApprovers($entity);
        //echo "approvers=".count($approvers)."<br>";

        //$subject = $requestName." #" . $entity->getId() . " " . ucwords($status);
        $approversNameArr = array();
        $approverEmailArr = array();
        $approversShortNameArr = array();

        foreach( $approvers as $approver ) {

            //echo "approver=".$approver."<br>";
            $approverSingleEmail = $approver->getSingleEmail();

            if( $approverSingleEmail ) {
                $approverEmailArr[] = $approverSingleEmail;
                $approversNameArr[] = $approver." (".$approverSingleEmail.")";
                $approversShortNameArr[] = $approver->getUsernameOptimal();
            }

            //$message = $this->createCancelEmailBody($entity,$approver);
            //$message = str_replace("###emailuser###",$approver->getUsernameOptimal(),$originalMessage);
            //$emailUtil->sendEmail($approverSingleEmail, $subject, $message, null, null);

        } //foreach approver

        $message = str_replace("###emailuser###",implode("; ",$approversShortNameArr),$originalMessage);

        if( count($approverEmailArr) > 0 ) {
            $logger->notice("sendGeneralEmailToApproversAndEmailUsers: send confirmation emails to approvers=".implode("; ",$approverEmailArr)."; subject=".$subject."; message=".$message);
            $emailUtil->sendEmail($approverEmailArr, $subject, $message, null, null);
        }

        //send email to email users
        //echo "sendCopy=".$sendCopy."<br>";
        if( $sendCopy ) {
            $emailUserEmailArr = array();
            $subject = "Copy of the email: " . $subject;
            $addText = "### This is a copy of the email sent to the approvers " . implode("; ", $approversNameArr) . "###";
            $message = $addText . $break . $break . $message;
            //echo "settings for institution=".$institution."<br>";
            $settings = $this->getSettingsByInstitution($institution->getId());
            if ($settings) {
                //echo "settings OK<br>";
                foreach ($settings->getEmailUsers() as $emailUser) {
                    //echo "emailUser=".$emailUser."<br>";
                    $emailUserEmail = $emailUser->getSingleEmail();
                    if ($emailUserEmail) {
                        //$message = $this->createCancelEmailBody($entity, $emailUser, $addText);
                        //$emailUtil->sendEmail($emailUserEmail, $subject, $message, null, null);
                        $emailUserEmailArr[] = $emailUserEmail;
                    }
                }

            }

            //Add approvers for a notification email for carry over request (if copy user is not in $approverEmailArr)
            if( $entity->getRequestType()->getAbbreviation() == "carryover" ) {
                //echo "getting carry over request approvers<br>";
                $supervisors = $this->getUsersByGroupId($institution,"ROLE_VACREQ_SUPERVISOR");
                foreach( $supervisors as $supervisor ) {
                    $supervisorEmail = $supervisor->getSingleEmail();
                    //echo "supervisor=".$supervisor.", email=".$supervisorEmail."<br>";
                    if( $supervisorEmail && !in_array($supervisorEmail, $approverEmailArr) ) {
                        $emailUserEmailArr[] = $supervisorEmail;
                    }
                }

                //overwrite message without links
                //$entity, $emailToUser=null, $addText=null, $withLinks=true
                $message = $this->createEmailBody($entity,null,null,false);
                $message = str_replace("###emailuser###",implode("; ",$approversShortNameArr),$message);
            }


            //$logger->notice("sendGeneralEmailToApproversAndEmailUsers: emailUserEmailArr count=".count($emailUserEmailArr));
            if (count($emailUserEmailArr) > 0) {
                //print_r($emailUserEmailArr);
                $logger->notice("sendGeneralEmailToApproversAndEmailUsers: send a copy of the confirmation emails to email users and supervisors=" . implode("; ", $emailUserEmailArr) . "; subject=" . $subject . "; message=" . $message);
                $emailUtil->sendEmail($emailUserEmailArr, $subject, $message, null, null);
            }
        }

        //if( count($approverEmailArr) > 0 ) {
            //$emailUtil->sendEmail($approverEmailArr, $subject, $message, $emailUserEmailArr, null);
        //}

        return implode(", ",$approversNameArr);
    }



    //User log should record all changes in user: subjectUser, Author, field, old value, new value.
    public function setEventLogChanges($request) {

        //$diffArr = $this->diffDoctrineObject($request);
        //echo "diffArr=".$diffArr."<br>";
        //print_r($diffArr);
        //exit('1');

        //$em = $this->em;

        //$uow = $em->getUnitOfWork();
        //$uow->computeChangeSets(); // do not compute changes if inside a listener

        $eventArr = array();

        //log simple fields
        //$changeset = $uow->getEntityChangeSet($request);
        $changeset = $this->diffDoctrineObject($request);
        $eventArr = $this->addChangesToEventLog( $eventArr, $changeset );

        //log Business Request
        if( $request->hasBusinessRequest() ) {
            $requestParticular = $request->getRequestBusiness();
            //$changeset = $uow->getEntityChangeSet($requestParticular);
            $changeset = $this->diffDoctrineObject($requestParticular);
            $text = "("."Business Travel Request ID ".$request->getId().")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Vacation Request
        if( $request->hasVacationRequest() ) {
            $requestParticular = $request->getRequestVacation();
            //$changeset = $uow->getEntityChangeSet($requestParticular);
            $changeset = $this->diffDoctrineObject($requestParticular);
            $text = "("."Vacation Request ID ".$request->getId().")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //exit('1');
        return $eventArr;

    }
    public function addChangesToEventLog( $eventArr, $changeset, $text="" ) {

        $changeArr = array();

        //echo "count changeset=".count($changeset)."<br>";

        //process $changeset: author, subjectuser, oldvalue, newvalue
        foreach( $changeset as $key => $value ) {
            if( $value[0] != $value[1] ) {

                if( is_object($key) ) {
                    //if $key is object then skip it, because we don't want to have non-informative record such as: credentials(stateLicense New): old value=, new value=Credentials
                    continue;
                }

                $field = $key;

                $oldValue = $value[0];
                $newValue = $value[1];

                if( $oldValue instanceof \DateTime ) {
                    $oldValue = $this->convertDateTimeToStr($value[0]);
                }
                if( $newValue instanceof \DateTime ) {
                    $newValue = $this->convertDateTimeToStr($value[1]);
                }

                if( is_array($oldValue) ) {
                    $oldValue = implode(", ",$oldValue);
                }
                if( is_array($newValue) ) {
                    $newValue = implode(", ",$newValue);
                }

                $event = "<strong>".$field.$text."</strong>".": "."old value=".$oldValue.", new value=".$newValue;

                //echo "event=".$event."<br>";
                //exit();

                $changeArr[] = $event;
            }
        }

        if( count($changeArr) > 0 ) {
            $eventArr[] = implode("<br>", $changeArr);
        }

        return $eventArr;

    }
    public function diffDoctrineObject($entity) {
        $uow = $this->em->getUnitOfWork();
        $originalEntityData = $uow->getOriginalEntityData($entity);
        //echo "originalEntityData=".$originalEntityData."<br>";
        //print_r($originalEntityData);

//        if( $originalEntityData['phone'] != $entity->getPhone() ) {
//            echo "phone changed:".$originalEntityData['phone'] ."=>". $entity->getPhone()."<br>";
//        }

        $changeSet = array();

        foreach( $entity->getArrayFields() as $field ) {
            $getMethod = "get".$field;
            $oldValue = $originalEntityData[$field];
            $newValue = $entity->$getMethod();
            if( $oldValue != $newValue ) {
                //echo "phone changed:".$originalEntityData['phone'] ."=>". $entity->getPhone()."<br>";
                $changeSet[$field] = array($oldValue,$newValue);
            }
        }

        //print_r($changeSet);
        return $changeSet;
    }


    public function getAccruedDaysUpToThisMonth() {
        //accrued days up to this month calculated by vacationAccruedDaysPerMonth
        $userSecUtil = $this->container->get('user_security_utility');
        $vacationAccruedDaysPerMonth = $userSecUtil->getSiteSettingParameter('vacationAccruedDaysPerMonth');
        if( !$vacationAccruedDaysPerMonth ) {
            throw new \InvalidArgumentException('vacationAccruedDaysPerMonth is not defined in Site Parameters.');
        }

        //get start academic date
        $dates = $this->getCurrentAcademicYearStartEndDates(true);
        $startAcademicYearDate = $dates['startDate'];

        //get month difference between now and $startAcademicYearDate
        $nowDate = new \DateTime();
        $monthCount = $this->diffInMonths($startAcademicYearDate, $nowDate);
        //$monthCount = $monthCount - 1;

        //echo "monthCount=".$monthCount."<br>";
        $accruedDays = (int)$monthCount * (int)$vacationAccruedDaysPerMonth;
        return $accruedDays;
    }

//    /**
//     * Calculate the difference in months between two dates (v1 / 18.11.2013)
//     *
//     * @param \DateTime $date1
//     * @param \DateTime $date2
//     * @return int
//     */
//    public static function diffInMonths_Old(\DateTime $date1, \DateTime $date2)
//    {
//        $diff =  $date1->diff($date2);
//
//        $months = $diff->y * 12 + $diff->m + $diff->d / 30;
//        $months = (int) round($months);
//        $months = $months - 1;
//        echo "months=".$months."<br>";
//
//        return $months;
//    }
    //http://www.tricksofit.com/2013/12/calculate-the-difference-between-two-dates-in-php#.V1GMSL69GgM
    public static function diffInMonths(\DateTime $date1, \DateTime $date2)
    {
        $months = $date1->diff($date2)->m + ($date1->diff($date2)->y*12);
        //echo "months=".$months."<br>";
        return (int)$months;
    }

    public function getTotalAccruedDays() {
        //accrued days up to this month calculated by vacationAccruedDaysPerMonth
        $userSecUtil = $this->container->get('user_security_utility');
        $vacationAccruedDaysPerMonth = $userSecUtil->getSiteSettingParameter('vacationAccruedDaysPerMonth');
        if( !$vacationAccruedDaysPerMonth ) {
            throw new \InvalidArgumentException('vacationAccruedDaysPerMonth is not defined in Site Parameters.');
        }
        //echo "monthCount=".$monthCount."<br>";
        $totalAccruedDays = 12 * $vacationAccruedDaysPerMonth;
        return $totalAccruedDays;
    }

    public function getPendingCarryOverRequests($user) {

        if( $this->security->isGranted('ROLE_VACREQ_SUPERVISOR') == false ) {
           return null;
        }

        //1) get user's supervisor group
        //$userRoles = $this->em->getRepository('AppUserdirectoryBundle:User')->
            //findUserChildRolesBySitePermissionObjectAction($user,'vacreq',"vacReqRequest","changestatus-carryover");

        $groupParams = array('asObject'=>true);
        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
        $groups = $this->getGroupsByPermission($user,$groupParams);

        $idArr = array();
        foreach( $groups as $group ) {
            $idArr[] = $group->getId();
        }

        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");
        $dql->leftJoin("request.institution", "institution");
        $dql->leftJoin("request.requestType", "requestType");

        $dql->where("request.status = 'pending' AND requestType.abbreviation = 'carryover'");

        $idsStr = implode(",", $idArr);
        if( $idsStr ) {
            $dql->andWhere("institution.id IN (" . $idsStr . ") ");
        }

        $query = $this->em->createQuery($dql);

//        $query->setParameters(array(
//            'groupIds' => implode(",",$idArr),
//        ));

        $requests = $query->getResult();

        return $requests;
    }

    //used in navbar. return HTML
    public function getTotalPendingCarryoverRequests($user) {

        //{{ path('vacreq_incomingrequests',{'filter[pending]':1}) }}
        $html = '
                <a id="incoming-orders-menu-badge"
                      class="element-with-tooltip-always"
                      title="Pending Approval" data-toggle="tooltip"
                      data-placement="bottom"
                      href="www.google.com"
                    ><span class="badge">33</span></a>';

        $html = "test";

        return "";
    }

    public function getHeaderInfoMessages( $user ) {

        $userSecUtil = $this->container->get('user_security_utility');


        //{{ yearRange }} Accrued Vacation Days as of today: {{ accruedDays }}
        //"You have accrued X vacation days this academic year (and will accrue X*12 by [date of academic year start from site settings, show as July 1st, 20XX]."
        //"You have accrued 10 vacation days this academic year (and will accrue 24 by July 1st, 2016."
        //accrued days up to this month calculated by vacationAccruedDaysPerMonth
        $accruedDays = $this->getAccruedDaysUpToThisMonth();
        $totalAccruedDays = $this->getTotalAccruedDays();


        //$currentStartYear
        $yearRange = $this->getCurrentAcademicYearRange();
        $yearRangeArr = explode("-",$yearRange);
        $currentStartYear = $yearRangeArr[1];

        $startAcademicYearStr = $this->getEdgeAcademicYearDate( $currentStartYear, "End" );
        $startAcademicYearDate = new \DateTime($startAcademicYearStr);
        $startAcademicYearDateStr = $startAcademicYearDate->format("F jS, Y");

//        $accruedDaysString =    "You have accrued ".$accruedDays." vacation days this academic year".
//                                " (and will accrue ".$totalAccruedDays." by ".$startAcademicYearDateStr.").";

        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart');
        if( !$academicYearStart ) {
            throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
        }
        $academicYearStartString = $academicYearStart->format("F jS");

        $vacationAccruedDaysPerMonth = $userSecUtil->getSiteSettingParameter('vacationAccruedDaysPerMonth');
        if( !$vacationAccruedDaysPerMonth ) {
            throw new \InvalidArgumentException('vacationAccruedDaysPerMonth is not defined in Site Parameters.');
        }
        //If you have worked here since [July 1st] or before,
        // You have so far accrued [22] vacation days this academic year (and will accrue [24] by [July 1st], [2016]).
        $accruedDaysString = "If you have worked here since $academicYearStartString or before, you have so far accrued ".
            $accruedDays." vacation days this academic year (and will accrue ".$totalAccruedDays." by ".$startAcademicYearDateStr.").";
        //Alternatively, if you started after July 1st, you can calculate the amount of vacation days
        // you have accrued by multiplying the number of months since your start date by 2.
        $accruedDaysString .= "<br>Alternatively, if you started after $academicYearStartString, you can calculate the amount of vacation days".
            " you have accrued by multiplying the number of months since your start date by $vacationAccruedDaysPerMonth.";


        //If for the current academic year the value of carried over vacation days is not empty and not zero for the logged in user,
        // append a third sentence stating "You have Y additional vacation days carried over from [Current Academic Year -1, show as 2014-2015]."
        $currentYearRange = $this->getCurrentAcademicYearRange();
        $carriedOverDays = $this->getUserCarryOverDays($user, $currentYearRange);
        //echo "carriedOverDays=".$carriedOverDays."<br>";
        $carriedOverDaysString = null;
        if( $carriedOverDays ) {
            $lastYearRange = $this->getPreviousAcademicYearRange();
            $carriedOverDaysString = "You have ".$carriedOverDays." additional vacation days carried over from ".$lastYearRange;
        }

        //Carry over days to the next academic year
        $nextYearRange = $this->getNextAcademicYearRange();
        $carriedOverDaysNextYear = $this->getUserCarryOverDays($user,$nextYearRange);
        //echo "carriedOverDaysNextYear=".$carriedOverDaysNextYear."<br>";
        //$carriedOverDaysNextYearString = null;
        if( $carriedOverDaysNextYear ) {
            if( $carriedOverDaysString ) {
                $carriedOverDaysString = $carriedOverDaysString . " and ".$carriedOverDaysNextYear." subtracted vacation days carried over to the next year ".$nextYearRange;
            } else {
                $carriedOverDaysString = "You have ".$carriedOverDaysNextYear." subtracted vacation days carried over to the next year ".$nextYearRange;
            }
        }

        if( $carriedOverDaysString ) {
            $carriedOverDaysString = $carriedOverDaysString . ".";
        }

        //totalAllocatedDays - vacationDays + carryOverDays
        $remainingDaysRes = $this->totalVacationRemainingDays($user);
        //$remainingDaysString = "You have ".$remainingDaysRes['numberOfDays']." remaining vacation days during the current academic year";
        ////Based on the assumed [24] accrued days per year and on approved carry over requests documented in this system,
        // You have [17] remaining vacation days during the current academic year.
        $remainingDaysString = "Based on the assumed ".$totalAccruedDays." accrued days per year and on approved carry over ".
            "requests documented in this system,".
            " you have ".$remainingDaysRes['numberOfDays']." remaining vacation days during the current academic year";
        if( !$remainingDaysRes['accurate'] ) {
            $remainingDaysString .= " (".$this->getInaccuracyMessage().")";
        }
        $remainingDaysString .= ".";


        $messages = array();
        $messages['accruedDaysString'] = $accruedDaysString;
        //$messages['accruedDays'] = $accruedDays;
        $messages['totalAccruedDays'] = $totalAccruedDays;
        $messages['carriedOverDaysString'] = $carriedOverDaysString;
        //$messages['carriedOverDaysNextYearString'] = $carriedOverDaysNextYearString;
        $messages['remainingDaysString'] = $remainingDaysString;


        return $messages;
    }

    //(number of days accrued per month from site settings x 12) + days carried over from previous academic year
    // - approved vacation days for this academic year based on the requests
    // with the status of "Approved" or "Cancelation denied (Approved)"
    //If the current month is July or August, AND the logged in user has the number of remaining vacation days > 0 IN THE PREVIOUS ACADEMIC YEAR
    public function getNewCarryOverRequestString( $user ) {

        //$res = $this->getAvailableCurrentYearCarryOverRequestString($user);
        //exit('current res='.$res);

        //If the logged in user has the number of remaining vacation days > 0 IN THE PREVIOUS ACADEMIC YEAR
        //TODO: test it in july (2 months after start academic year)
        $currentMonth = date('n');

        //$currentMonth = "8"; //testing
        //echo "currentMonth=".$currentMonth."<br>";

        //get first month of the academical year
        $dates = $this->getCurrentAcademicYearStartEndDates(true);
        $startAcademicYearDate = $dates['startDate'];
        $startMonth = $startAcademicYearDate->format('n');
        //echo "startMonth=".$startMonth."<br>";
        $nextStartMonth = $startMonth + 1;
        $nextNextStartMonth = $startMonth + 2;
        //echo "nextStartMonth=".$nextStartMonth."<br>";
        //echo "nextNextStartMonth=".$nextNextStartMonth."<br>";

        $previousYearUnusedDaysMessage = null;

        //if( $currentMonth == '07' || $currentMonth == '08' ) {
        if( $currentMonth == $nextStartMonth || $currentMonth == $nextNextStartMonth ) {
            $previousYearUnusedDaysMessage = $this->getPreviousYearUnusedDays($user);
            //echo "previousYearUnusedDaysMessage=".$previousYearUnusedDaysMessage."<br>";
        }

        if( $previousYearUnusedDaysMessage ) {

            return $previousYearUnusedDaysMessage;

        } else {

            $currentYearUnusedDaysMessage = $this->getCurrentYearUnusedDays($user);
            if( $currentYearUnusedDaysMessage ) {
                return $currentYearUnusedDaysMessage;
            }

        }

        return null;
    }

    public function getCurrentYearUnusedDays( $user, $asString=true ) {
        $totalAccruedDays = $this->getTotalAccruedDays();
        $requestTypeStr = 'vacation';

        $currentYearRange = $this->getCurrentAcademicYearRange();
        $carryOverDaysPreviousYear = $this->getUserCarryOverDays($user,$currentYearRange);

        //carried over days from the current year to the next year.
        $nextYearRange = $this->getNextAcademicYearRange();
        $carryOverDaysToNextYear = $this->getUserCarryOverDays($user,$nextYearRange);

        $res = $this->getApprovedTotalDays($user,$requestTypeStr);
        $approvedVacationDays = $res['numberOfDays'];
        //$accurate = $res['accurate'];

        //echo "current $totalAccruedDays + $carryOverDaysPreviousYear - $approvedVacationDays <br>";

        //                      12*2             carryover days from PREVIOUS year   approved days for CURRENT year
        $unusedDays = (int)$totalAccruedDays + (int)$carryOverDaysPreviousYear - (int)$approvedVacationDays - (int)$carryOverDaysToNextYear;

        if( $unusedDays == 0 ) {
            return $unusedDays;
        }

        if( $asString && $unusedDays > 0 ) {
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_carryoverrequest',
                array(
                    'days' => $unusedDays,
                )
            //UrlGeneratorInterface::ABSOLUTE_URL
            );

            $link = '<a href="' . $actionRequestUrl . '">Request to carry over the remaining ' . $unusedDays . ' vacation days</a>';
            return $link;
        }

        return $unusedDays;
    }

    public function getPreviousYearUnusedDays( $user, $asString=true ) {
        $totalAccruedDays = $this->getTotalAccruedDays();
        $requestTypeStr = 'vacation';
        //$break = "\r\n";

        $yearRange = $this->getPreviousAcademicYearRange();
        $carryOverDaysPreviousYear = $this->getUserCarryOverDays($user,$yearRange);
        //echo "carryOverDaysPreviousYear=$carryOverDaysPreviousYear<br>";

        //TODO: test it: carried over days from the current year to THIS year (from prospective of the previous year).
        //For previous year. Use this year carry over days
        $thisYearRange = $this->getCurrentAcademicYearRange();
        $carryOverDaysToThisYear = $this->getUserCarryOverDays($user,$thisYearRange);

        $res = $this->getPreviousYearApprovedTotalDays($user,$requestTypeStr);
        $approvedVacationDays = $res['numberOfDays'];
        //echo "previous: $totalAccruedDays + $carryOverDaysPreviousYear - $approvedVacationDays <br>";
        //                      12*2             carryover days from PREVIOUS year   approved days for CURRENT year
        $unusedDays = (int)$totalAccruedDays + (int)$carryOverDaysPreviousYear - (int)$approvedVacationDays - (int)$carryOverDaysToThisYear;
        //echo "unusedDays=$unusedDays<br>";

        if( $unusedDays == 0 ) {
            return $unusedDays;
        }

        // if the logged in user has a carry over request from the previous academic year to the current academic year
        if( $asString && $unusedDays > 0 ) {

            $yearRangeArr = explode("-", $yearRange);
            $sourceYear = $yearRangeArr[0];
            $destinationYear = $yearRangeArr[1];

            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_carryoverrequest',
                array(
                    'days' => $unusedDays,
                    'sourceYear' => $sourceYear,
                    'destinationYear' => $destinationYear,
                )
            //UrlGeneratorInterface::ABSOLUTE_URL
            );

            $link = "You have " . $unusedDays . " unused vacation days in the previous " . $yearRange . " academic year.";
            $link .= ' <a href="' . $actionRequestUrl . '" target="_blank">Request to carry over the remaining ' . $unusedDays . ' vacation days</a>';

            // If the carry over request has a status of "Approved" (final),
            // show the following statement in the second well INSTEAD of the one that is shown now (link the word "request" to the request page):
            //"Your request to carry over X vacation days from the 20XX-20YY academic year to the 20YY-20ZZ academic year
            // has been approved by FirstName LastName."
            $finalApprovedRequests = $this->getCarryOverRequests( $user, -1, "approved" );
            $countFinalApprovedRequests = count($finalApprovedRequests);
            foreach( $finalApprovedRequests as $thisRequest ) {
                $reqId = "";
                if( $countFinalApprovedRequests > 1 ) {
                    $reqId = " (ID #" . $thisRequest->getId() . ") ";
                }
                $link .= "<br> Your request $reqId to carry over ".$thisRequest->getCarryOverDays().
                    " vacation days from the ".$thisRequest->getSourceYearRange().
                    " academic year to the ".$thisRequest->getDestinationYearRange()." academic year".
                    " has been approved by ".$thisRequest->getApprover().".";
            }

            //If the carry over request has a status of "Tentatively Approved", show the following statement
            // in the second well INSTEAD of the one that is shown now:
            //"Your request to carry over X vacation days from the 20XX-20YY academic year to the 20YY-20ZZ academic year
            // has been tentatively approved by FirstName LastName.
            // Your request still has to be approved by FirstName LastName in order for the X vacation days to carried over to 20YY-20ZZ."
            $tentativelyApprovedRequests = $this->getCarryOverRequests( $user, -1, "pending", "approved" );
            $countTentativelyApprovedRequests = count($tentativelyApprovedRequests);
            foreach( $tentativelyApprovedRequests as $thisRequest ) {
                $reqId = "";
                if( $countTentativelyApprovedRequests > 1 ) {
                    $reqId = " (ID #" . $thisRequest->getId() . ") ";
                }
                $link .= "<br> Your request $reqId to carry over ".$thisRequest->getCarryOverDays().
                    " vacation days from the ".$thisRequest->getSourceYearRange().
                    " academic year to the ".$thisRequest->getDestinationYearRange()." academic year".
                    " has been tentatively approved by ".$thisRequest->getTentativeApprover().".";

                $approvers = $this->getRequestApprovers($thisRequest);
                $approverNamesArr = array();
                foreach( $approvers as $approver ) {
                    $approverNamesArr[] = $approver;
                }
                $finalApproversStr = implode(", ",$approverNamesArr);

                $link .= "<br>Your request still has to be approved by ".$finalApproversStr.
                    " in order for the ".$thisRequest->getCarryOverDays()." vacation days to carried over to ".
                    $thisRequest->getDestinationYearRange().".";
            }

            //If the carry over request has a status of "Submitted" (before "tentatively approved"), show the following statement in
            // the second well INSTEAD of the one that is shown now:
            //"Your request to carry over X vacation days from the 20XX-20YY academic year to the 20YY-20ZZ academic year
            // is awaiting tentative approval of FirstName LastName."
            $pendingRequests = $this->getCarryOverRequests( $user, -1, "pending", "pending" );
            $countPendingRequests = count($pendingRequests);
            foreach( $pendingRequests as $thisRequest ) {
                $reqId = "";
                if( $countPendingRequests > 1 ) {
                    $reqId = " (ID #" . $thisRequest->getId() . ") ";
                }

                $approvers = $this->getRequestApprovers($thisRequest);
                $approverNamesArr = array();
                foreach( $approvers as $approver ) {
                    $approverNamesArr[] = $approver;
                }
                $approversStr = implode(", ",$approverNamesArr);

                $link .= "<br> Your request $reqId to carry over ".$thisRequest->getCarryOverDays().
                    " vacation days from the ".$thisRequest->getSourceYearRange().
                    " academic year to the ".$thisRequest->getDestinationYearRange()." academic year".
                    " is awaiting tentative approval of ".$approversStr.".";
            }

            return $link;
        }

        return $unusedDays;
    }

    //$yearOffset: 0=>current year, -1=>previous year
    public function getCarryOverRequests( $user, $yearOffset=null, $finalStatus=null, $tentativeStatus=null ) {

        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql = $repository->createQueryBuilder('request');

        $dql->leftJoin("request.user", "user");
        $dql->leftJoin("request.requestType", "requestType");

        $dql->where("user.id = :userId AND requestType.abbreviation = 'carryover'");

        if( $finalStatus ) {
            $dql->andWhere('request.status = :finalStatus');
        }
        if( $tentativeStatus ) {
            $dql->andWhere('request.tentativeStatus = :tentativeStatus');
        }

        if( $yearOffset != null ) {
            //previous year: $yearOffset = -1;
            $dates = $this->getCurrentAcademicYearStartEndDates(false,$yearOffset); //in July 12: 2016-2017
            $startAcademicYearDate = $dates['startDate'];
            //$endAcademicYearDate = $dates['startDate'];
            $startAcademicYearDateArr = explode("-",$startAcademicYearDate);
            $startAcademicYearStr = $startAcademicYearDateArr[0];

            $dql->andWhere("request.destinationYear = '".$startAcademicYearStr."'");
        }

        $query = $this->em->createQuery($dql);

        $query->setParameter('userId', $user->getId());

        if( $finalStatus ) {
            $query->setParameter('finalStatus', $finalStatus);
        }
        if( $tentativeStatus ) {
            $query->setParameter('tentativeStatus', $tentativeStatus);
        }

        $requests = $query->getResult();

        return $requests;
    }

    //UNUSED function
    //Get available carry over days for the CURRENT academical year.
    //Case 1, today is May 10: current academical year is 2015-2016 or "July 1st 2015 - 2016 June 30"
    //Case 2, today is July 12: current academical year is 2016-2017 or "July 1st 2016 - 2017 June 30"
    //(number of days accrued per month from site settings x 12) + days carried over from previous academic year
    // - approved vacation days for this academic year based on the requests
    // with the status of "Approved" or "Cancelation denied (Approved)"
    public function getAvailableCurrentYearCarryOverRequestString( $user ) {

        $dates = $this->getCurrentAcademicYearStartEndDates();
        //echo "dates=".$dates['startDate']." == ".$dates['endDate']."<br>";
        $currentYearStart = $dates['startDate']; //Y-m-d
        //echo "currentYearStart=".$currentYearStart."<br>";
        $endDate = $dates['endDate']; //Y-m-d
        //echo "endDate=".$endDate."<br>";

        $currentYearStartDateArr = explode("-",$currentYearStart);
        $year = $currentYearStartDateArr[0];
        $year = ((int)$year) - 1;   //previous year
        //echo "year=".$year."<br>";

        $totalAccruedDays = $this->getTotalAccruedDays();
        $carryOverDaysPreviousYear = $this->getUserCarryOverDays($user,$year); //2015

        //carried over days from the current year to the next year.
        $nextYearRange = $this->getNextAcademicYearRange();
        $carryOverDaysToNextYear = $this->getUserCarryOverDays($user,$nextYearRange);

        $requestTypeStr = 'vacation';
        $res = $this->getApprovedTotalDays($user,$requestTypeStr);
        $approvedVacationDays = $res['numberOfDays'];
        //$accurate = $res['accurate'];

        //echo "$totalAccruedDays + $carryOverDaysPreviousYear - $approvedVacationDays <br>";

        //                      12*2             carryover days from PREVIOUS year   approved days for CURRENT year
        $daysToRequest = (int)$totalAccruedDays + (int)$carryOverDaysPreviousYear - (int)$approvedVacationDays - (int)$carryOverDaysToNextYear;

        if( $daysToRequest && $daysToRequest > 0 ) {
            $actionRequestUrl = $this->container->get('router')->generate(
                'vacreq_carryoverrequest',
                array(
                    'days' => $daysToRequest,
                )
            //UrlGeneratorInterface::ABSOLUTE_URL
            );

            $link = '<a href="'.$actionRequestUrl.'">Request to carry over the remaining '.$daysToRequest.' vacation days</a>';
            return $link;
        }

        return null;
    }

    //Get pending (non-approved, non-rejected) requests for the logged in approver
    public function getTotalPendingRequests( $approver, $groupId=null ) {
        $requestsB = $this->getTotalStatusTypeRequests($approver,"business",$groupId);
        $requestsV = $this->getTotalStatusTypeRequests($approver,"vacation",$groupId);

        return count($requestsB) + count($requestsV);
    }
    public function getTotalStatusTypeRequests( $approver, $requestTypeStr, $groupId=null, $asObject=true, $status = "pending" ) {

        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");

        if( $asObject ) {
            $dql->select('request');
        } else {
            $dql->select('SUM(requestType.numberOfDays) as numberOfDays');
        }

        $dql->leftJoin("request.user", "user");

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
        }

        $dql->where("requestType.status = :status");

        $queryParameters = array(
            'status' => $status
        );

        if( $groupId ) {
            $dql->andWhere("request.institution = :groupId");
            $queryParameters['groupId'] = $groupId;
        } else {
            //get approver groups
            $groupParams = array('asObject' => true);
            $groupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus');
            $groupRoles = $this->getGroupsByPermission($approver, $groupParams);
            $groupIds = array();
            foreach ($groupRoles as $role) {
                $groupIds[] = $role->getId();
            }
            if ($groupIds and count($groupIds) > 0) {
                $dql->andWhere("request.institution IN (" . implode(",", $groupIds) . ")");
            }
        }

        $query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";
        //echo "dql=".$dql."<br>";

        $query->setParameters($queryParameters);

        if( $asObject ) {
            $requests = $query->getResult();
            return $requests;
        } else {
            $numberOfDaysRes = $query->getSingleResult();
            $numberOfDays = $numberOfDaysRes['numberOfDays'];
            //echo "numberOfDays=".$numberOfDays."<br>";
            return $numberOfDays;
        }

        return null;
    }

    public function convertDateTimeToStr($datetime) {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        $dateStr = $transformer->transform($datetime);
        return $dateStr;
    }

    //$status - pre-approval or final status (the one has been changed)
    public function processChangeStatusCarryOverRequest( $entity, $status, $user, $request, $withRedirect=true, $update=true ) {

        //echo "<br><br>Testing: processChangeStatusCarryOverRequest: request ID=".$entity->getId()."<br>";
        //echo "Tentative inst=".$entity->getTentativeInstitution()."<br>";

        $logger = $this->container->get('logger');
        $session = $this->container->get('session');

        //check permissions
//        if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') || $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
//            if( false == $this->get('security.authorization_checker')->isGranted("changestatus", $entity) ) {
//                return $this->redirect($this->generateUrl('vacreq-nopermission'));
//            }
//        } elseif( $this->get('security.authorization_checker')->isGranted("update", $entity) ) {
//            if( $status != 'canceled' && $status != 'pending' && $status != 'cancellation-request' ) {
//                return $this->redirect($this->generateUrl('vacreq-nopermission'));
//            }
//        } else {
//            return $this->redirect($this->generateUrl('vacreq-nopermission'));
//        }
        /////////////// check permission: if user is in approvers => ok ///////////////
        if( false == $this->container->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
            $permitted = false;
            $approvers = $this->getRequestApprovers($entity);
            $approversName = array();
            foreach ($approvers as $approver) {
                if ($user->getId() == $approver->getId()) {
                    //ok
                    $permitted = true;
                }
                $approversName[] = $approver . "";
            }
            if ($permitted == false) {
                //Flash
                $session->getFlashBag()->add(
                    'notice',
                    "You can not review this request. This request can be approved or rejected by " . implode("; ", $approversName)
                );
                //exit("testing: no permission to approve this request.");
                //return $this->redirect($this->generateUrl('vacreq-nopermission'));
                return 'vacreq-nopermission';
            }
        }
        /////////////// EOF check permission: if user is in approvers => ok ///////////////


        $em = $this->em;
        $emailUtil = $this->container->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        //$break = "\r\n";
        $break = "<br>";
        $action = null;

        /////////////////// TWO CASES: pre-approval and final approval ///////////////////
        if( $entity->getTentativeInstitution() && $entity->getTentativeStatus() == 'pending' ) {
            ////////////// FIRST STEP: group pre-approver ///////////////////

            //setTentativeInstitution to approved or rejected

            $action = "Tentatively ".$status;
            $logger->notice("process ChangeStatusCarryOverRequest: action=".$action);

            $entity->setTentativeStatus($status);

            if( $status == "pending" ) { //set tentative status back to pending
                $entity->setTentativeApprover(null);
                $entity->setApprover(null);
                $entity->setStatus('pending');
            } else {
                $entity->setTentativeApprover($user);
                $entity->setTentativeApprovedRejectDate(new \DateTime());
            }

            //send email to supervisor for a final approval
            if( $status == 'approved' ) {

                $entity->setApprover(null);
                $entity->setStatus('pending');

                $approversNameStr = $this->sendConfirmationEmailToApprovers($entity);

                //Event Log
                $requestName = $entity->getRequestName();
                $eventType = 'Carry Over Request Updated';
                $event = $requestName . " ID #".$entity->getId()." for ".$entity->getUser()." has been tentatively approved by ".$entity->getTentativeApprover().". ".
                    "Email for a final approval has been sent to ".$approversNameStr;
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

                //Flash
                $session->getFlashBag()->add(
                    'notice',
                    $event
                );
            }

            //send email to submitter
            if( $status == 'rejected' ) {

                //since it's rejected then set status to rejected
                $entity->setApprover($user);
                $entity->setStatus($status);

                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was rejected.
                $subjectRejected = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange()." was rejected.";

                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
                $bodyRejected = $entity->getTentativeApprover(). " has rejected your request ID #".$entity->getId()." to carry over ".
                    $entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();

                //request info
                $bodyRejected .= $break.$break.$entity->printRequest($this->container);

                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectRejected, $bodyRejected, null, null );

                //Event Log
                $requestName = $entity->getRequestName();
                $eventType = 'Carry Over Request Updated';
                $event = $requestName . " ID #".$entity->getId()." for ".$entity->getUser()." has been tentatively rejected by ".
                    $entity->getTentativeApprover().". ".
                    "Confirmation email has been sent to the submitter ".$entity->getUser().".";
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

                //Flash
                $session->getFlashBag()->add(
                    'notice',
                    $event
                );
            }


        } else {
            ////////////// SECOND STEP: supervisor //////////////

            $action = "Final ".$status;
            $logger->notice("process ChangeStatusCarryOverRequest: action=".$action);

            $entity->setStatus($status);

            if( $status == "pending" ) {
                $entity->setApprover(null);
            } else {
                $entity->setApprover($user);
            }

            if( $status == "approved" ) {

                //process carry over request days if request is approved
                $res = $this->processVacReqCarryOverRequest($entity);
                if( $res && $res['exists'] == true && $withRedirect ) {
                    //warning for overwrite:
                    //"FirstName LastName already has X days carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year on file.
                    // This carry over request asks for N days to be carried over from 20YY-20ZZ academic year to the 20ZZ-20MM academic year.
                    // Please enter the total amount of days that should be carried over 20YY-20ZZ academic year to the 20ZZ-20MM academic year: [ ]"
                    //exit('exists days='.$res['days']);
                    //return $this->redirectToRoute('vacreq_review',array('id'=>$entity->getId()));
                    return "vacreq_review";
                }
                if( $res && ($res['exists'] == false || $update == true) ) {
                    //save
                    $userCarryOver = $res['userCarryOver'];
                    //$logger->notice("process ChangeStatusCarryOverRequest: update userCarryOver=".$userCarryOver);
                    $em->persist($userCarryOver);
                    $em->flush($userCarryOver);
                }

                //send a confirmation email to submitter
                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was approved.
                $subjectApproved = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange() . " was approved.";

                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
                $bodyApproved = $entity->getApprover(). " has approved your request in the final phase to carry over ".
                    $entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();

                //request info
                $bodyApproved .= $break.$break.$entity->printRequest($this->container);

                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectApproved, $bodyApproved, null, null );

                //Event Log
                $requestName = $entity->getRequestName();
                $eventType = 'Carry Over Request Updated';
                $event = $requestName . " for ".$entity->getUser()." has been approved in the final phase by ".$entity->getApprover().
                    ". Confirmation email has been sent to the submitter ".$entity->getUser()->getSingleEmail();
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

                //Flash
                $session->getFlashBag()->add(
                    'notice',
                    $event
                );
            }


            //send email to submitter
            if( $status == 'rejected' ) {
                //Subject: Your request to carry over X vacation days from 20XX-20YY to 20YY-20ZZ was rejected.
                $subjectRejected = "Your request ID #".$entity->getId()." to carry over ".$entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange() . " was rejected.";

                //Message: FirstNameOfTentativeApprover LastNameOfTentativeApprover has rejected your request to
                // carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
                $bodyRejected = $entity->getApprover(). " has rejected your request in the final phase to carry over ".
                    $entity->getCarryOverDays()." vacation days from ".
                    $entity->getSourceYearRange() . " to " . $entity->getDestinationYearRange();

                //request info
                $bodyRejected .= $break.$break.$entity->printRequest($this->container);

                $emailUtil->sendEmail( $entity->getUser()->getSingleEmail(), $subjectRejected, $bodyRejected, null, null );

                //Event Log
                $requestName = $entity->getRequestName();
                $eventType = 'Carry Over Request Updated';
                $event = $requestName . " for ".$entity->getUser()." has been rejected in the final phase by ".$entity->getApprover().
                    ". Confirmation email has been sent to the submitter ".$entity->getUser()->getSingleEmail();
                $userSecUtil->createUserEditEvent($this->container->getParameter('vacreq.sitename'),$event,$user,$entity,$request,$eventType);

                //Flash
                $session->getFlashBag()->add(
                    'notice',
                    $event
                );
            }
        }
        /////////////////// EOF TWO CASES: pre-approval and final approval ///////////////////

        return $action;
    }


    public function addRequestInstitutionToOrgGroup( $entity, $organizationalInstitutions, $institutionType="institution" ) {
        //echo "entity group=".$entity->getInstitution()."<br>";

        if( $institutionType == "institution" ) {
            $institution = $entity->getInstitution();
            //echo "institution <br>";
        }
        if( $institutionType == "tentativeInstitution" ) {
            $institution = $entity->getTentativeInstitution();
            //echo "tentativeInstitution <br>";
        }

        //if( $organizationalInstitutions && $institution ) { //$organizationalInstitutions &&
        if( $institution ) { //$organizationalInstitutions &&
            //echo "add to organizationalInstitutions; count=".count($organizationalInstitutions)."<br>";
            if( !array_key_exists($institution->getId(), $organizationalInstitutions) ) {
                $thisApprovers = $this->getRequestApprovers( $entity, $institutionType );
                $approversArr = array();
                foreach( $thisApprovers as $thisApprover ) {
                    $approversArr[] = $thisApprover->getUsernameShortest();
                }
                if( count($approversArr) > 0 ) {
                    $orgName = $institution . " (for review by " . implode(", ",$approversArr) . ")";
                } else {
                    $orgName = $institution."";
                }
                $organizationalInstitutions[$institution->getId()] = $orgName;
            }
        }

        return $organizationalInstitutions;
    }

    public function getVacReqIdsArrByDqlParameters($dql,$dqlParameters) {
        $dql->select('request.id');

        $query = $dql->getQuery();

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $result = $query->getScalarResult();
        $ids = array_map('current', $result);
        $ids = array_unique($ids);

        return $ids;
    }

    public function createtListExcel( $ids ) {

        $author = $this->container->get('security.token_storage')->getToken()->getUser();
        //$transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');

        $ea = new Spreadsheet(); // ea is short for Excel Application

        $ea->getProperties()
            ->setCreator($author."")
            ->setTitle('Business/Vacation Requests')
            ->setLastModifiedBy($author."")
            ->setDescription('Business/Vacation Requests list in Excel format')
            ->setSubject('PHP Excel manipulation')
            ->setKeywords('excel php office phpexcel lakers')
            ->setCategory('programming')
        ;

        $ews = $ea->getSheet(0);
        $ews->setTitle('Business and Vacation Requests');

        //align all cells to left
        $style = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            )
        );
        $ews->getParent()->getDefaultStyle()->applyFromArray($style);
        //$ews->getPageSetup()->setHorizontalCentered(true);

        $ews->setCellValue('A1', 'ID');
        $ews->setCellValue('B1', 'Person');
        $ews->setCellValue('C1', 'Academic Year');
        $ews->setCellValue('D1', 'Group');

        $ews->setCellValue('E1', 'Business Days');
        $ews->setCellValue('F1', 'Start Date');
        $ews->setCellValue('G1', 'End Date');
        $ews->setCellValue('H1', 'Status');

        $ews->setCellValue('I1', 'Vacation Days');
        $ews->setCellValue('J1', 'Start Date');
        $ews->setCellValue('K1', 'End Date');
        $ews->setCellValue('L1', 'Status');


        $totalNumberBusinessDays = 0;
        $totalNumberVacationDays = 0;

        $row = 2;
        foreach( explode("-",$ids) as $vacreqId ) {

            $vacreq = $this->em->getRepository('AppVacReqBundle:VacReqRequest')->find($vacreqId);
            if( !$vacreq ) {
                continue;
            }

            //check if author can have access to view this request
            if( false == $this->container->get('security.authorization_checker')->isGranted("read", $vacreq) ) {
                continue; //skip this applicant because the current user does not permission to view this applicant
            }

            $ews->setCellValue('A'.$row, $vacreq->getId());

            $academicYearArr = $this->getRequestAcademicYears($vacreq);
            if( count($academicYearArr) > 0 ) {
                $academicYear = $academicYearArr[0];
            } else {
                $academicYear = null;
            }

            $ews->setCellValue('B'.$row, $vacreq->getUser());
            $ews->setCellValue('C'.$row, $academicYear);

            //Group
            $ews->setCellValue('D'.$row, $vacreq->getInstitution()."");

            $businessRequest = $vacreq->getRequestBusiness();
            if( $businessRequest ) {
                $numberBusinessDays = $this->specificRequestExcelInfo($ews,$row,$vacreq,$businessRequest,array('E','F','G','H'));
                if( $numberBusinessDays ) {
                    $totalNumberBusinessDays = $totalNumberBusinessDays + intval($numberBusinessDays);
                }
            }

            $vacationRequest = $vacreq->getRequestVacation();
            if( $vacationRequest ) {
                $numberVacationDays = $this->specificRequestExcelInfo($ews,$row,$vacreq,$vacationRequest,array('I','J','K','L'));
                if( $numberVacationDays ) {
                    $totalNumberVacationDays = $totalNumberVacationDays + intval($numberVacationDays);
                }
            }

            $row = $row + 1;
        }//foreach

        $ews->setCellValue('B'.$row, "Total");
        $ews->setCellValue('E'.$row, $totalNumberBusinessDays);
        $ews->setCellValue('I'.$row, $totalNumberVacationDays);

        $styleLastRow = [
            'font' => [
                'bold' => true,
            ],
//            'alignment' => [
//                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
//            ],
//            'borders' => [
//                'bottom' => [
//                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
//                ],
//            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'ebf1de',
                ],
                'endColor' => [
                    'argb' => 'ebf1de',
                ],
            ],
        ];

        //set color light green to the last Total row
        $ews->getStyle('A'.$row.':'.'L'.$row)->applyFromArray($styleLastRow);

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
    public function specificRequestExcelInfo( $ews, $row, $vacreq, $request, $columnArr ) {
        if( $request ) {
            $numberDays = $request->getNumberOfDays();
            //Business Days
            $ews->setCellValue($columnArr[0].$row, $numberDays."");

            //Start Date
            $startDate = $request->getStartDate();
            if( $startDate ) {
                $startDate->setTimezone(new \DateTimeZone("UTC"));
                $ews->setCellValue($columnArr[1].$row, $startDate->format('m/d/Y'));
            }

            //End Date
            $endDate = $request->getEndDate();
            if( $endDate ) {
                $endDate->setTimezone(new \DateTimeZone("UTC"));
                $ews->setCellValue($columnArr[2].$row, $endDate->format('m/d/Y'));
            }

            //Status
            $status = null;
            if( $request && $request->getStatus() ) {
                if( $vacreq->getExtraStatus() ) {
                    $extraStatus = $vacreq->getExtraStatus();
                    $extraStatus = str_replace('(Approved)','',$extraStatus);
                    $extraStatus = str_replace('(Canceled)','',$extraStatus);
                    $status = $request->getStatus()." (".$extraStatus.")";
                } else {
                    $status = $request->getStatus();
                }
            }
            if( $status ) {
                $ews->setCellValue($columnArr[3].$row, ucfirst($status));
            }

            return $numberDays;
        }

        return 0;
    }

    public function createtListExcelSpout( $ids, $fileName ) {

        $author = $this->container->get('security.token_storage')->getToken()->getUser();
        //$transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');

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

        //setTitle('Business/Vacation Requests')
        //$ews = $ea->getSheet(0);
        //$ews->setTitle('Business and Vacation Requests');


//        $ews->setCellValue('A1', 'ID');
//        $ews->setCellValue('B1', 'Person');
//        $ews->setCellValue('C1', 'Academic Year');
//        $ews->setCellValue('D1', 'Group');
//
//        $ews->setCellValue('E1', 'Business Days');
//        $ews->setCellValue('F1', 'Start Date');
//        $ews->setCellValue('G1', 'End Date');
//        $ews->setCellValue('H1', 'Status');
//
//        $ews->setCellValue('I1', 'Vacation Days');
//        $ews->setCellValue('J1', 'Start Date');
//        $ews->setCellValue('K1', 'End Date');
//        $ews->setCellValue('L1', 'Status');

//        $writer->addRowWithStyle(
//            [
//                'ID',                  //0 - A
//                'Person',              //1 - B
//                'Academic Year',       //2 - C
//                'Group',               //3 - D
//
//                'Business Days',       //4 - E
//                'Start Date',          //5 - F
//                'End Date',            //6 - G
//                'Status',              //7 - H
//
//                'Vacation Days',       //8 - I
//                'Start Date',          //9 - J
//                'End Date',            //10 - K
//                'Status',              //11 - L
//
//            ],
//            $headerStyle
//        );
        $spoutRow = WriterEntityFactory::createRowFromArray(
            [
                'ID',                  //0 - A
                'Person',              //1 - B
                'Academic Year',       //2 - C
                'Group',               //3 - D

                'Business Days',       //4 - E
                'Start Date',          //5 - F
                'End Date',            //6 - G
                'Status',              //7 - H

                'Vacation Days',       //8 - I
                'Start Date',          //9 - J
                'End Date',            //10 - K
                'Status',              //11 - L

            ],
            $headerStyle
        );
        $writer->addRow($spoutRow);


        $totalNumberBusinessDays = 0;
        $totalNumberVacationDays = 0;

        $row = 2;
        foreach( explode("-",$ids) as $vacreqId ) {

            $vacreq = $this->em->getRepository('AppVacReqBundle:VacReqRequest')->find($vacreqId);
            if( !$vacreq ) {
                continue;
            }

            //check if author can have access to view this request
            if( false == $this->container->get('security.authorization_checker')->isGranted("read", $vacreq) ) {
                continue; //skip this applicant because the current user does not permission to view this applicant
            }

            $data = array();

            //$ews->setCellValue('A'.$row, $vacreq->getId());
            $data[0] = $vacreq->getId();

            $academicYearArr = $this->getRequestAcademicYears($vacreq);
            if( count($academicYearArr) > 0 ) {
                $academicYear = $academicYearArr[0];
            } else {
                $academicYear = null;
            }

            //$ews->setCellValue('B'.$row, $vacreq->getUser());
            $data[1] = $vacreq->getUser()."";
            //$ews->setCellValue('C'.$row, $academicYear);
            $data[2] = $academicYear;

            //Group
            //$ews->setCellValue('D'.$row, $vacreq->getInstitution()."");
            $data[3] = $vacreq->getInstitution()."";

            $businessRequest = $vacreq->getRequestBusiness();
            if( $businessRequest ) {
                //$numberBusinessDays = $this->specificRequestExcelSpoutInfo($writer,$vacreq,$businessRequest,array('E','F','G','H'));
                $numberBusinessDays = $this->specificRequestExcelSpoutInfo($data,$vacreq,$businessRequest,array(4,5,6,7));
                if( $numberBusinessDays ) {
                    $totalNumberBusinessDays = $totalNumberBusinessDays + intval($numberBusinessDays);
                }
            } else {
                $data[4] = NULL;
                $data[5] = NULL;
                $data[6] = NULL;
                $data[7] = NULL;
            }
            //print_r($data);

            $vacationRequest = $vacreq->getRequestVacation();
            if( $vacationRequest ) {
                //$numberVacationDays = $this->specificRequestExcelSpoutInfo($writer,$vacreq,$vacationRequest,array('I','J','K','L'));
                $numberVacationDays = $this->specificRequestExcelSpoutInfo($data,$vacreq,$vacationRequest,array(8,9,10,11));
                if( $numberVacationDays ) {
                    $totalNumberVacationDays = $totalNumberVacationDays + intval($numberVacationDays);
                }
            } else {
                $data[8] = NULL;
                $data[9] = NULL;
                $data[10] = NULL;
                $data[11] = NULL;
            }

            //print_r($data);
            //exit('111');

            //$writer->addRowWithStyle($data,$requestStyle);
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $requestStyle);
            $writer->addRow($spoutRow);
            //$row = $row + 1;
        }//foreach

        $data = array();
        $data[0] = NULL;
        $data[2] = NULL;
        $data[3] = NULL;
        $data[5] = NULL;
        $data[6] = NULL;
        $data[7] = NULL;

        //$ews->setCellValue('B'.$row, "Total"); //1
        $data[1] = "Total";
        //$ews->setCellValue('E'.$row, $totalNumberBusinessDays); //4
        $data[4] = $totalNumberBusinessDays;
        //$ews->setCellValue('I'.$row, $totalNumberVacationDays); //8
        $data[8] = $totalNumberVacationDays;
        //$writer->addRowWithStyle($data,$footerStyle);
        $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
        $writer->addRow($spoutRow);

        //set color light green to the last Total row
        //$ews->getStyle('A'.$row.':'.'L'.$row)->applyFromArray($styleLastRow);

        //exit("ids=".$fellappids);

        $writer->close();
    }
    public function specificRequestExcelSpoutInfo( &$data, $vacreq, $request, $columnArr ) {
        if( $request ) {
            $numberDays = $request->getNumberOfDays();
            //Business Days
            //$ews->setCellValue($columnArr[0].$row, $numberDays."");
            $data[$columnArr[0]] = $numberDays."";

            //Start Date
            $startDate = $request->getStartDate();
            if( $startDate ) {
                $startDate->setTimezone(new \DateTimeZone("UTC"));
                //$ews->setCellValue($columnArr[1].$row, $startDate->format('m/d/Y'));
                $data[$columnArr[1]] = $startDate->format('m/d/Y');
            } else {
                $data[$columnArr[1]] = NULL;
            }

            //End Date
            $endDate = $request->getEndDate();
            if( $endDate ) {
                $endDate->setTimezone(new \DateTimeZone("UTC"));
                //$ews->setCellValue($columnArr[2].$row, $endDate->format('m/d/Y'));
                $data[$columnArr[2]] = $endDate->format('m/d/Y');
            } else {
                $data[$columnArr[2]] = NULL;
            }

            //Status
            $status = null;
            if( $request && $request->getStatus() ) {
                if( $vacreq->getExtraStatus() ) {
                    $extraStatus = $vacreq->getExtraStatus();
                    $extraStatus = str_replace('(Approved)','',$extraStatus);
                    $extraStatus = str_replace('(Canceled)','',$extraStatus);
                    $status = $request->getStatus()." (".$extraStatus.")";
                } else {
                    $status = $request->getStatus();
                }
            }
            if( $status ) {
                //$ews->setCellValue($columnArr[3].$row, ucfirst($status));
                $data[$columnArr[3]] = ucfirst($status);
            } else {
                $data[$columnArr[3]] = NULL;
            }

            return $numberDays;
        }

        return 0;
    }

}