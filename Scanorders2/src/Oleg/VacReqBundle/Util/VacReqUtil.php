<?php

namespace Oleg\VacReqBundle\Util;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/25/2016
 * Time: 11:16 AM
 */
class VacReqUtil
{

    protected $em;
    protected $sc;
    protected $container;


    public function __construct( $em, $sc, $container ) {

        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;

    }


    public function getSettingsByInstitution($instid) {
        $setting = $this->em->getRepository('OlegVacReqBundle:VacReqSettings')->findOneByInstitution($instid);
        return $setting;
    }


    public function getInstitutionSettingArray() {
        $settings = $this->em->getRepository('OlegVacReqBundle:VacReqSettings')->findAll();

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
            $emailUser = $this->em->getRepository('OlegUserdirectoryBundle:User')->find($userId);
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
    public function getRequestApprovers( $entity ) {

        $institution = $entity->getInstitution();
        if( !$institution ) {
            return null;
        }

        $approvers = array();
        $roleApprovers = $this->em->getRepository('OlegUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName( "vacreq", 'ROLE_VACREQ_APPROVER', $institution->getId());
        $roleApprover = $roleApprovers[0];
        //echo "roleApprover=".$roleApprover."<br>";
        if( $roleApprover ) {
            $approvers = $this->em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole($roleApprover->getName());
        }

        return $approvers;
    }

    //set confirmation email to approver and email users
    public function sendConfirmationEmailToApprovers( $entity ) {

        $institution = $entity->getInstitution();
        if( !$institution ) {
            return null;
        }

        $emailUtil = $this->container->get('user_mailer_utility');
        //$break = "\r\n";

        $approvers = $this->getRequestApprovers($entity);

        $approversNameArr = array();

        $subject = "Review Faculty Vacation/Business Request #" . $entity->getId() . " Confirmation";

        foreach( $approvers as $approver ) {

            if( !$approver->getSingleEmail() ) {
                continue;
            }

            $approversNameArr[] = $approver;

            $message = $this->createEmailBody($entity,$approver);
            $emailUtil->sendEmail($approver->getSingleEmail(), $subject, $message, null, null);

        } //foreach approver

        //send email to email users
        $subject = "Copy of the Review Faculty Vacation/Business Request #" . $entity->getId() . " Confirmation";
        $addText = "### This is a copy of a confirmation email sent to the approvers ".implode(", ",$approversNameArr)."###";
        $settings = $this->getSettingsByInstitution($institution->getId());
        if( $settings ) {
            foreach ($settings->getEmailUsers() as $emailUser) {
                $emailUserEmail = $emailUser->getSingleEmail();
                if ($emailUserEmail) {
                    $message = $this->createEmailBody($entity, $emailUser, $addText);
                    $emailUtil->sendEmail($emailUserEmail, $subject, $message, null, null);
                }
            }
        }

    }
    public function createEmailBody($entity,$emailToUser,$addText=null) {

        $break = "\r\n";

        $submitter = $entity->getUser();

        $message = "Dear " . $emailToUser->getUsernameOptimal() . "," . $break.$break;

        if( $addText ) {
            $message .= $addText.$break.$break;
        }

        $message .= $submitter->getUsernameOptimal()." has submitted the pathology faculty vacation/business travel request and it is ready for review.";

        $reviewRequestUrl = $url = $this->container->get('router')->generate(
            'vacreq_review',
            array(
                'id' => $entity->getId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $message .= $break . $break . "Please click on the below URL to review the vacation/business travel request:" . $break;
        $message .= $reviewRequestUrl . $break . $break;

        $message .= $break . "Please click on the URLs below for quick actions to approve or reject the vacation/business travel request.";

        if( $entity->hasBusinessRequest() ) {
            //href="{{ path(vacreq_sitename~'_status_change', { 'id': entity.id,  'requestName':requestName, 'status': 'approved' }) }}
            //approved
            $actionRequestUrl = $url = $this->container->get('router')->generate(
                'vacreq_status_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'business',
                    'status' => 'approved'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please click on the below URL to Approve the business request:" . $break;
            $message .= $actionRequestUrl;

            //rejected
            $actionRequestUrl = $url = $this->container->get('router')->generate(
                'vacreq_status_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'business',
                    'status' => 'rejected'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please click on the below URL to Reject the business request:" . $break;
            $message .= $actionRequestUrl;
        }

        if( $entity->hasVacationRequest() ) {
            //href="{{ path(vacreq_sitename~'_status_change', { 'id': entity.id,  'requestName':requestName, 'status': 'approved' }) }}
            //approved
            $actionRequestUrl = $url = $this->container->get('router')->generate(
                'vacreq_status_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'vacation',
                    'status' => 'approved'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please click on the below URL to Approve the vacation request:" . $break;
            $message .= $actionRequestUrl;

            //rejected
            $actionRequestUrl = $url = $this->container->get('router')->generate(
                'vacreq_status_change',
                array(
                    'id' => $entity->getId(),
                    'requestName' => 'vacation',
                    'status' => 'rejected'
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $message .= $break . $break . "Please click on the below URL to Reject the vacation request:" . $break;
            $message .= $actionRequestUrl;
        }

        $message .= $break.$break."To approve or reject requests, Division Approvers must be on site or using vpn when off site";
        $message .= $break.$break."**** PLEASE DON'T REPLY TO THIS EMAIL ****";

        return $message;
    }

    //set respond confirmation email to a submitter and email users
    public function sendSingleRespondEmailToSubmitter( $entity, $approver, $requestName=null, $status ) {

        $emailUtil = $this->container->get('user_mailer_utility');
        $break = "\r\n";

        $institution = $entity->getInstitution();
        if( !$institution ) {
            return null;
        }

        $subject = "Respond Confirmation for Faculty Vacation/Business Request #".$entity->getId();

        $submitter = $entity->getUser();

        $message = "Dear " . $submitter->getUsernameOptimal() . "," . $break.$break;

        if( $requestName ) {
            $message .= "Your ".$requestName." Request";
        } else {
            $message .= "Your ".$entity->getRequestName()." Request";   //"Your request";
        }

        if ($status == 'pending') {
            $status = 'set to Pending';
        }

        $message .= " has been " . $status . " by " . $approver->getUsernameOptimal() . $break.$break;
        $message .= "**** PLEASE DON'T REPLY TO THIS EMAIL ****";

        //css
        $cssArr = array();
        $settings = $this->getSettingsByInstitution($institution->getId());
        if( $settings ) {
            foreach ($settings->getEmailUsers() as $emailUser) {
                $emailUserEmail = $emailUser->getSingleEmail();
                if ($emailUserEmail) {
                    $cssArr[] = $emailUserEmail;
                }
            }
        }

        $emailUtil->sendEmail( $submitter->getSingleEmail(), $subject, $message, $cssArr, null );
    }


    //"During the current academic year, you have received X approved vacation days in total."
    // (if X = 1, show "During the current academic year, you have received X approved vacation day."
    // if X = 0, show "During the current academic year, you have received no approved vacation days."
    public function getApprovedDaysString( $user ) {

        $result = "During the current academic year, you have received ";

        $requestTypeStr = 'business';
        $res = $this->getApprovedTotalDays($user,$requestTypeStr);
        $numberOfDays = $res['numberOfDays'];
        $accurate = $res['accurate'];
        if( $numberOfDays == 0 ) {
            $result .= "no approved ".$requestTypeStr." days";
        }
        if( $numberOfDays == 1 ) {
            $result .= $numberOfDays." approved ".$requestTypeStr." day";
        }
        if( $numberOfDays > 1 ) {
            $result .= $numberOfDays." approved ".$requestTypeStr." days in total";
        }
        if( !$accurate ) {
            $result .= " (the result might be inaccurate due to academic year overlap)";
        }

        $result .= " and ";

        $requestTypeStr = 'vacation';
        $res = $this->getApprovedTotalDays($user,$requestTypeStr);
        $numberOfDays = $res['numberOfDays'];
        $accurate = $res['accurate'];
        if( $numberOfDays == 0 ) {
            $result .= "no approved ".$requestTypeStr." days";
        }
        if( $numberOfDays == 1 ) {
            $result .= $numberOfDays." approved ".$requestTypeStr." day";
        }
        if( $numberOfDays > 1 ) {
            $result .= $numberOfDays." approved ".$requestTypeStr." days in total";
        }
        if( !$accurate ) {
            $result .= " (the result might be inaccurate due to academic year overlap)";
        }

        $result .= ".";

        return $result;
    }


    //calculate approved total days for current academical year
    public function getApprovedTotalDays( $user, $requestTypeStr ) {

        $userSecUtil = $this->container->get('user_security_utility');

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
        $previousYear = date("Y") - 1;
        $academicYearStartStr = $previousYear."-".$academicYearStartStr;
        //echo "academicYearStartStr=".$academicYearStartStr."<br>";
        //academicYearEnd
        $academicYearEndStr = $academicYearEnd->format('m-d');
        $currentYear = date("Y");
        $academicYearEndStr = $currentYear."-".$academicYearEndStr;
        //echo "academicYearEndStr=".$academicYearEndStr."<br>";

        //step1: get requests within academic Year
        $numberOfDaysInside = $this->getApprovedYearDays($user,$requestTypeStr,$academicYearStartStr,$academicYearEndStr,"inside",false);
        //echo "numberOfDaysInside=".$numberOfDaysInside."<br>";

        //step2: get requests with start date earlier than academic Year Start
        $numberOfDaysBefore = $this->getApprovedBeforeAcademicYearDays($user,$requestTypeStr,$academicYearStartStr,$academicYearEndStr);
        //echo "numberOfDaysBefore=".$numberOfDaysBefore."<br>";

        //step3: get requests with start date later than academic Year End
        $currentYear = date("Y");
        $academicYearStartStr = $currentYear."-".$academicYearStart->format('m-d');
        $nextYear = date("Y") + 1;
        $academicYearEndStr = $nextYear."-".$academicYearStart->format('m-d');
        $numberOfDaysAfter = $this->getApprovedAfterAcademicYearDays($user,$requestTypeStr,$academicYearStartStr,$academicYearEndStr);
        //echo "numberOfDaysAfter=".$numberOfDaysAfter."<br>";

        $res = array();

        $numberOfDays = $numberOfDaysBefore+$numberOfDaysInside+$numberOfDaysAfter;
        $res['numberOfDays'] = $numberOfDays;
        $res['accurate'] = true;

        if( $numberOfDaysBefore > 0 || $numberOfDaysAfter > 0 ) {
            $res['accurate'] = false;
        }

        return $res;
    }

    public function getApprovedBeforeAcademicYearDays( $user, $requestTypeStr, $startStr=null, $endStr=null ) {
        $logger = $this->container->get('logger');
        $days = 0;
        $subRequestGetMethod = "getRequest".$requestTypeStr;

        $requests = $this->getApprovedYearDays($user,$requestTypeStr,$startStr,$endStr,"before",true);

        foreach( $requests as $request ) {
            $subRequest = $request->$subRequestGetMethod();
            $requestEndAcademicYearStr = $this->getRequestEdgeAcademicYearDate( $request, "End" );
            //echo "requestStartDate=".$subRequest->getStartDate()->format('Y-m-d')."<br>";
            //echo "requestEndAcademicYearStr=".$requestEndAcademicYearStr."<br>";
            //echo $request->getId().": before: request days=".$subRequest->getNumberOfDays()."<br>";
            $workingDays = $this->getNumberOfWorkingDaysBetweenDates( $subRequest->getStartDate(), new \DateTime($requestEndAcademicYearStr) );
            //echo "workingDays=".$workingDays."<br>";
            if( $workingDays > $subRequest->getNumberOfDays() ) {
                $logger->warning("Logical error getApprovedBeforeAcademicYearDays: number of calculated working days (".$workingDays.") are more than number of days in request (".$subRequest->getNumberOfDays().")");
                $workingDays = $subRequest->getNumberOfDays();
            }
            $days = $days + $workingDays;
        }

        return $days;
    }

    public function getApprovedAfterAcademicYearDays( $user, $requestTypeStr, $startStr=null, $endStr=null ) {
        $logger = $this->container->get('logger');
        $days = 0;
        $subRequestGetMethod = "getRequest".$requestTypeStr;

        //echo "startStr=".$startStr."<br>";
        //echo "endStr=".$endStr."<br>";
        $requests = $this->getApprovedYearDays($user,$requestTypeStr,$startStr,$endStr,"after",true);

        foreach( $requests as $request ) {
            $subRequest = $request->$subRequestGetMethod();
            $requestStartAcademicYearStr = $this->getRequestEdgeAcademicYearDate( $request, "Start" );
            //echo $request->getId().": after: request days=".$subRequest->getNumberOfDays()."<br>";

            //echo "requestStartAcademicYearStr=".$requestStartAcademicYearStr."<br>";
            //echo "requestEndAcademicYearStr=".$subRequest->getEndDate()->format('Y-m-d')."<br>";

            $workingDays = $this->getNumberOfWorkingDaysBetweenDates( new \DateTime($requestStartAcademicYearStr), $subRequest->getEndDate() );
            if( $workingDays > $subRequest->getNumberOfDays() ) {
                $logger->warning("Logical error getApprovedAfterAcademicYearDays: number of calculated working days (".$workingDays.") are more than number of days in request (".$subRequest->getNumberOfDays().")");
                $workingDays = $subRequest->getNumberOfDays();
            }
            $days = $days + $workingDays;
        }

        return $days;
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
            $yearsArr = explode("-",$academicYearArr[0]);
            $startYear = $yearsArr[0];
            $academicYearStartStr = $startYear."-".$academicYearStartStr;
        } else {
            throw new \InvalidArgumentException("Request's academic start year is not defined.");
        }
        //echo "academicYearStartStr=".$academicYearStartStr."<br>";

        $user = $request->getUser();

        //get the first day away
        $requestFirstDateAway = $request->getFirstDateAway('approved');
        if( $requestFirstDateAway == null ) {
            //exit("Request's first day away is not defined.");
            //throw new \InvalidArgumentException("Request's first day away is not defined.");
            return null;
        }

        $requestFirstDateAwayStr = $requestFirstDateAway->format('Y-m-d');
        //echo "requestFirstDateAwayStr=".$requestFirstDateAwayStr."<br>";

        $days = $this->getApprovedYearDays($user,$requestTypeStr,$academicYearStartStr,$requestFirstDateAwayStr,"inside",false);

        return $days;
    }

    public function getApprovedYearDays( $user, $requestTypeStr, $startStr=null, $endStr=null, $type=null, $asObject=false ) {

        $repository = $this->em->getRepository('OlegVacReqBundle:VacReqRequest');
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

        $dql->where("requestType.id IS NOT NULL AND user.id = :userId AND requestType.status = :statusApproved");

        // |----||--s--e--||----|
        if( $type == "inside" && $startStr && $endStr ) {
            $dql->andWhere("requestType.startDate > '" . $startStr . "'" . " AND requestType.endDate < " . "'" . $endStr . "'");
        }

        // |--s--||--e--||----|
        if( $type == "before" && $startStr ) {
            $dql->andWhere("requestType.startDate < '" . $startStr . "'"); // . " AND requestType.endDate > " . "'" . $startStr . "'");
        }

        // |----||--s--||--e--|
        if( $type == "after" && $startStr && $endStr ) {
            //echo "sql endStr=".$endStr."<br>";
            $dql->andWhere("requestType.endDate > '" . $endStr . "'");  // . " AND requestType.endDate < " . "'" . $endStr . "'");
            //$dql->andWhere("requestType.startDate > '" . $startStr . "'" . " AND requestType.endDate > " . "'" . $endStr . "'");
        }

        $query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";
        //echo "dql=".$dql."<br>";

        $query->setParameters( array(
            'userId' => $user->getId(),
            'statusApproved' => 'approved'
        ));

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

    public function getNumberOfWorkingDaysBetweenDates( $starDate, $endDate ) {
        $starDateStr = $starDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');
        //$holidays = array();
        $holidays = ['*-12-25', '*-01-01', '2013-12-23']; # variable and fixed holidays
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
            $yearsArr = explode("-",$academicYearArr[0]);
            $edgeYear = $yearsArr[0];
            $academicYearEdgeStr = $edgeYear."-".$academicYearEdgeStr;
        } else {
            throw new \InvalidArgumentException("Request's academic ".$edge." year is not defined.");
        }
        //echo "academicYearEdgeStr=".$academicYearEdgeStr."<br>";

        return $academicYearEdgeStr;
    }


    public function getRequestAcademicYears( $request ) {
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
        $yearDiff = $endYear - $startYear;
        $yearDiff = $yearDiff + 1;

        $academicYearStartMD = $academicYearStart->format('m-d');
        $academicStartDateStr = $startYear."-".$academicYearStartMD;
        //echo "academicStartDateStr= ".$academicStartDateStr."<br>";
        //$academicStartDate = new \DateTime($academicStartDateStr);

        //$endYear = $endYear + $yearDiff;
        $academicYearEndMD = $academicYearEnd->format('m-d');
        $academicEndDateStr = $endYear."-".$academicYearEndMD;
        //echo "academicEndDateStr= ".$academicEndDateStr."<br>";
        //$academicEndDate = new \DateTime($academicEndDateStr);

        $academicYearArr = array();

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

    public function getApprovedRequestStartedBetweenDates( $requestTypeStr, $startDate, $endDate ) {

        $repository = $this->em->getRepository('OlegVacReqBundle:VacReqRequest');
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
        //$res = "";
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

        $repository = $this->em->getRepository('OlegVacReqBundle:VacReqRequest');
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




    public function getSubmitterPhone($user) {

        //(a) prepopulate the phone number with the phone number from the user's profile
        $phones = $user->getAllPhones();
        if( count($phones) > 0 ) {
            return $phones[0]['phone'];
        }

        //(b) prepopulate from previous approved request (if there is one) for this user (person away)
        //$requests = $this->em->getRepository('OlegVacReqBundle:VacReqRequest')->findByUser($user,array('ORDER'=>'approvedRejectDate'));
        $repository = $this->em->getRepository('OlegVacReqBundle:VacReqRequest');
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
            $repository = $this->em->getRepository('OlegVacReqBundle:VacReqRequest');
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
            $repository = $this->em->getRepository('OlegVacReqBundle:VacReqRequest');
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
    public function sendCancelEmailToApprovers( $entity, $user, $status ) {

        $institution = $entity->getInstitution();
        if( !$institution ) {
            return null;
        }

        $emailUtil = $this->container->get('user_mailer_utility');
        //$break = "\r\n";

        $approvers = $this->getRequestApprovers($entity);

        $approversNameArr = array();

        $subject = "Faculty Vacation/Business Request #" . $entity->getId() . " " . ucwords($status);

        foreach( $approvers as $approver ) {

            if( !$approver->getSingleEmail() ) {
                continue;
            }

            $approversNameArr[] = $approver;

            $message = $this->createCancelEmailBody($entity,$approver);
            $emailUtil->sendEmail($approver->getSingleEmail(), $subject, $message, null, null);

        } //foreach approver

        //send email to email users
        $subject = "Copy of the Faculty Vacation/Business Request #" . $entity->getId() . " " . ucwords($status);
        $addText = "### This is a copy of a confirmation email sent to the approvers ".implode(", ",$approversNameArr)."###";
        $settings = $this->getSettingsByInstitution($institution->getId());
        if( $settings ) {
            foreach ($settings->getEmailUsers() as $emailUser) {
                $emailUserEmail = $emailUser->getSingleEmail();
                if ($emailUserEmail) {
                    $message = $this->createCancelEmailBody($entity, $emailUser, $addText);
                    $emailUtil->sendEmail($emailUserEmail, $subject, $message, null, null);
                }
            }
        }

    }
    public function createCancelEmailBody( $entity, $emailUser, $addText=null ) {
        $break = "\r\n";

        $message = "Dear " . $emailUser->getUsernameOptimal() . "," . $break.$break;

        if( $addText ) {
            $message .= $addText.$break.$break;
        }

        $message .= $entity->getUser()." canceled/withdrew their business travel / vacation request described below:".$break.$break;

        $message .= $entity."";

        $message .= $break.$break."**** PLEASE DON'T REPLY TO THIS EMAIL ****";

        return $message;
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

        print_r($changeSet);
        return $changeSet;
    }
    /**
     * Try to get an Entity changeSet without changing the UnitOfWork
     *
     * @param EntityManager $em
     * @param $entity
     * @return null|array
     */
    public static function diffDoctrineObject_ORIG(EntityManager $em, $entity) {
        $uow = $em->getUnitOfWork();

        /*****************************************/
        /* Equivalent of $uow->computeChangeSet($this->em->getClassMetadata(get_class($entity)), $entity);
        /*****************************************/
        $class = $em->getClassMetadata(get_class($entity));
        $oid = spl_object_hash($entity);
        $entityChangeSets = array();

        if ($uow->isReadOnly($entity)) {
            return null;
        }

        if ( ! $class->isInheritanceTypeNone()) {
            $class = $em->getClassMetadata(get_class($entity));
        }

        // These parts are not needed for the changeSet?
        // $invoke = $uow->listenersInvoker->getSubscribedSystems($class, Events::preFlush) & ~ListenersInvoker::INVOKE_MANAGER;
        //
        // if ($invoke !== ListenersInvoker::INVOKE_NONE) {
        //     $uow->listenersInvoker->invoke($class, Events::preFlush, $entity, new PreFlushEventArgs($em), $invoke);
        // }

        $actualData = array();

        foreach ($class->reflFields as $name => $refProp) {
            $value = $refProp->getValue($entity);

            if ($class->isCollectionValuedAssociation($name) && $value !== null) {
                if ($value instanceof PersistentCollection) {
                    if ($value->getOwner() === $entity) {
                        continue;
                    }

                    $value = new ArrayCollection($value->getValues());
                }

                // If $value is not a Collection then use an ArrayCollection.
                if ( ! $value instanceof Collection) {
                    $value = new ArrayCollection($value);
                }

                $assoc = $class->associationMappings[$name];

                // Inject PersistentCollection
                $value = new PersistentCollection(
                    $em, $em->getClassMetadata($assoc['targetEntity']), $value
                );
                $value->setOwner($entity, $assoc);
                $value->setDirty( ! $value->isEmpty());

                $class->reflFields[$name]->setValue($entity, $value);

                $actualData[$name] = $value;

                continue;
            }

            if (( ! $class->isIdentifier($name) || ! $class->isIdGeneratorIdentity()) && ($name !== $class->versionField)) {
                $actualData[$name] = $value;
            }
        }

        $originalEntityData = $uow->getOriginalEntityData($entity);
        if (empty($originalEntityData)) {
            // Entity is either NEW or MANAGED but not yet fully persisted (only has an id).
            // These result in an INSERT.
            $originalEntityData = $actualData;
            $changeSet = array();

            foreach ($actualData as $propName => $actualValue) {
                if ( ! isset($class->associationMappings[$propName])) {
                    $changeSet[$propName] = array(null, $actualValue);

                    continue;
                }

                $assoc = $class->associationMappings[$propName];

                if ($assoc['isOwningSide'] && $assoc['type'] & ClassMetadata::TO_ONE) {
                    $changeSet[$propName] = array(null, $actualValue);
                }
            }

            $entityChangeSets[$oid] = $changeSet; // @todo - remove this?
        } else {
            // Entity is "fully" MANAGED: it was already fully persisted before
            // and we have a copy of the original data
            $originalData           = $originalEntityData;
            $isChangeTrackingNotify = $class->isChangeTrackingNotify();
            $changeSet              = $isChangeTrackingNotify ? $uow->getEntityChangeSet($entity) : array();

            foreach ($actualData as $propName => $actualValue) {
                // skip field, its a partially omitted one!
                if ( ! (isset($originalData[$propName]) || array_key_exists($propName, $originalData))) {
                    continue;
                }

                $orgValue = $originalData[$propName];

                // skip if value haven't changed
                if ($orgValue === $actualValue) {
                    continue;
                }

                // if regular field
                if ( ! isset($class->associationMappings[$propName])) {
                    if ($isChangeTrackingNotify) {
                        continue;
                    }

                    $changeSet[$propName] = array($orgValue, $actualValue);

                    continue;
                }

                $assoc = $class->associationMappings[$propName];

                // Persistent collection was exchanged with the "originally"
                // created one. This can only mean it was cloned and replaced
                // on another entity.
                if ($actualValue instanceof PersistentCollection) {
                    $owner = $actualValue->getOwner();
                    if ($owner === null) { // cloned
                        $actualValue->setOwner($entity, $assoc);
                    } else if ($owner !== $entity) { // no clone, we have to fix
                        // @todo - what does this do... can it be removed?
                        if (!$actualValue->isInitialized()) {
                            $actualValue->initialize(); // we have to do this otherwise the cols share state
                        }
                        $newValue = clone $actualValue;
                        $newValue->setOwner($entity, $assoc);
                        $class->reflFields[$propName]->setValue($entity, $newValue);
                    }
                }

                if ($orgValue instanceof PersistentCollection) {
                    // A PersistentCollection was de-referenced, so delete it.
                    // These parts are not needed for the changeSet?
                    //            $coid = spl_object_hash($orgValue);
                    //
                    //            if (isset($uow->collectionDeletions[$coid])) {
                    //                continue;
                    //            }
                    //
                    //            $uow->collectionDeletions[$coid] = $orgValue;
                    $changeSet[$propName] = $orgValue; // Signal changeset, to-many assocs will be ignored.

                    continue;
                }

                if ($assoc['type'] & ClassMetadata::TO_ONE) {
                    if ($assoc['isOwningSide']) {
                        $changeSet[$propName] = array($orgValue, $actualValue);
                    }

                    // These parts are not needed for the changeSet?
                    //            if ($orgValue !== null && $assoc['orphanRemoval']) {
                    //                $uow->scheduleOrphanRemoval($orgValue);
                    //            }
                }
            }

            if ($changeSet) {
                $entityChangeSets[$oid]     = $changeSet;
                // These parts are not needed for the changeSet?
                //        $originalEntityData         = $actualData;
                //        $uow->entityUpdates[$oid]   = $entity;
            }
        }

        // These parts are not needed for the changeSet?
        //// Look for changes in associations of the entity
        //foreach ($class->associationMappings as $field => $assoc) {
        //    if (($val = $class->reflFields[$field]->getValue($entity)) !== null) {
        //        $uow->computeAssociationChanges($assoc, $val);
        //        if (!isset($entityChangeSets[$oid]) &&
        //            $assoc['isOwningSide'] &&
        //            $assoc['type'] == ClassMetadata::MANY_TO_MANY &&
        //            $val instanceof PersistentCollection &&
        //            $val->isDirty()) {
        //            $entityChangeSets[$oid]   = array();
        //            $originalEntityData = $actualData;
        //            $uow->entityUpdates[$oid]      = $entity;
        //        }
        //    }
        //}
        /*********************/

        return $entityChangeSets[$oid];
    }

    public function convertDateTimeToStr($datetime) {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        $dateStr = $transformer->transform($datetime);
        return $dateStr;
    }

}