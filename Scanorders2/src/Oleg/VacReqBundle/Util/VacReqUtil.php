<?php

namespace Oleg\VacReqBundle\Util;
use Doctrine\Common\Collections\ArrayCollection;
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
        $numberOfDays = $this->getApprovedTotalDays($user,$requestTypeStr);
        if( $numberOfDays == 0 ) {
            $result .= "no approved ".$requestTypeStr." days";
        }
        if( $numberOfDays == 1 ) {
            $result .= $numberOfDays." approved ".$requestTypeStr." day";
        }
        if( $numberOfDays > 1 ) {
            $result .= $numberOfDays." approved ".$requestTypeStr." days in total";
        }

        $result .= " and ";

        $requestTypeStr = 'vacation';
        $numberOfDays = $this->getApprovedTotalDays($user,$requestTypeStr);
        if( $numberOfDays == 0 ) {
            $result .= "no approved ".$requestTypeStr." days.";
        }
        if( $numberOfDays == 1 ) {
            $result .= $numberOfDays." approved ".$requestTypeStr." day.";
        }
        if( $numberOfDays > 1 ) {
            $result .= $numberOfDays." approved ".$requestTypeStr." days in total.";
        }

        return $result;
    }


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

        //step2: get requests with start date earlier than academic Year Start
        $numberOfDaysBefore = $this->getApprovedBeforeAcademicYearDays($user,$requestTypeStr,$academicYearStartStr,$academicYearEndStr);

        //step2: get requests with start date later than academic Year End
        $numberOfDaysAfter = $this->getApprovedAfterAcademicYearDays($user,$requestTypeStr,$academicYearStartStr,$academicYearEndStr);

        return $numberOfDaysBefore+$numberOfDaysInside+$numberOfDaysAfter;
    }

    public function getApprovedBeforeAcademicYearDays( $user, $requestTypeStr, $startStr=null, $endStr=null ) {
        $days = 0;
        $subRequestGetMethod = "getRequest".$requestTypeStr;
        $requests = $this->getApprovedYearDays($user,$requestTypeStr,$startStr,$endStr,"before",true);
        foreach( $requests as $request ) {
            $subRequest = $request->$subRequestGetMethod();
            //echo $request->getId().": before: request days=".$subRequest->getNumberOfDays()."<br>";
            $days = $days + $this->getNumberOfWorkingDaysBetweenDates( $subRequest->getStartDate(), new \DateTime($endStr) );
        }
        return $days;
    }

    public function getApprovedAfterAcademicYearDays( $user, $requestTypeStr, $startStr=null, $endStr=null ) {
        $days = 0;
        $subRequestGetMethod = "getRequest".$requestTypeStr;
        $requests = $this->getApprovedYearDays($user,$requestTypeStr,$startStr,$endStr,"after",true);
        foreach( $requests as $request ) {
            $subRequest = $request->$subRequestGetMethod();
            //echo $request->getId().": after: request days=".$subRequest->getNumberOfDays()."<br>";
            $days = $days + $this->getNumberOfWorkingDaysBetweenDates( new \DateTime($startStr), $subRequest->getEndDate() );
        }
        return $days;
    }

    //get prior approved days for the request's academic year
    public function getPriorApprovedDays( $request, $requestTypeStr ) {

        $userSecUtil = $this->container->get('user_security_utility');

        //academicYearStart
        $academicYearStart = $userSecUtil->getSiteSettingParameter('academicYearStart');
        if( !$academicYearStart ) {
            throw new \InvalidArgumentException('academicYearStart is not defined in Site Parameters.');
        }
//        //academicYearEnd
//        $academicYearEnd = $userSecUtil->getSiteSettingParameter('academicYearEnd');
//        if( !$academicYearEnd ) {
//            throw new \InvalidArgumentException('academicYearEnd is not defined in Site Parameters.');
//        }

        //constract start and end date for DB select "Y-m-d"
        //academicYearStart
        $academicYearStartStr = $academicYearStart->format('m-d');
//        $previousYear = date("Y") - 1;
//        $academicYearStartStr = $previousYear."-".$academicYearStartStr;
        //echo "academicYearStartStr=".$academicYearStartStr."<br>";
//        //academicYearEnd
//        $academicYearEndStr = $academicYearEnd->format('m-d');
//        $currentYear = date("Y");
//        $academicYearEndStr = $currentYear."-".$academicYearEndStr;
//        //echo "academicYearEndStr=".$academicYearEndStr."<br>";

        $academicYearArr = $this->getRequestAcademicYears($request);
        if( count($academicYearArr) > 0 ) {
            $yearsArr = explode("-",$academicYearArr[0]);
            $academicYearStartStr = $yearsArr[0]."-".$academicYearStartStr;
        } else {
            throw new \InvalidArgumentException("Request's academic start year is not defined.");
        }

        $user = $request->getUser();

        $requestCreateDate = $request->getCreateDate();
        $requestCreateDateStr = $requestCreateDate->format('Y-m-d');

        $days = $this->getApprovedYearDays($user,$requestTypeStr,$academicYearStartStr,$requestCreateDateStr,"inside",false);

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

        // |----|--s--e--|----|
        if( $type == "inside" && $startStr && $endStr ) {
            $dql->andWhere("requestType.startDate > '" . $startStr . "'" . " AND requestType.endDate < " . "'" . $endStr . "'");
        }

        // |--s--|--e--|----|
        if( $type == "before" && $startStr ) {
            $dql->andWhere("requestType.startDate < '" . $startStr . "'"); // . " AND requestType.endDate > " . "'" . $startStr . "'");
        }

        // |----|--s--|--e--|
        if( $type == "after" && $endStr ) {
            $dql->andWhere("requestType.endDate > '" . $endStr . "'");  // . " AND requestType.endDate < " . "'" . $endStr . "'");
        }

        $query = $this->em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";

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

        $em = $this->em;

        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener

        $eventArr = array();

        //log simple fields
        $changeset = $uow->getEntityChangeSet($request);
        $eventArr = $this->addChangesToEventLog( $eventArr, $changeset );

        //log Business Request
        if( $requestParticular = $request->hasBusinessRequest() ) {
            //$requestParticular = $request->getRequestBusiness();
            $changeset = $uow->getEntityChangeSet($requestParticular);
            $text = "("."Business Travel Request ID ".$request->getId().")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        //log Vacation Request
        if( $requestParticular = $request->hasVacationRequest() ) {
            //$requestParticular = $request->getRequestVacation();
            $changeset = $uow->getEntityChangeSet($requestParticular);
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

    public function convertDateTimeToStr($datetime) {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        $dateStr = $transformer->transform($datetime);
        return $dateStr;
    }

}