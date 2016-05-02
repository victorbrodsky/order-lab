<?php

namespace Oleg\VacReqBundle\Util;
use Doctrine\Common\Collections\ArrayCollection;
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
            $message .= "Your ".$requestName." request";
        } else {
            $message .= "Your request";
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

        $repository = $this->em->getRepository('OlegVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");

        $dql->select('SUM(requestType.numberOfDays) as numberOfDays');

        $dql->leftJoin("request.user", "user");

        if( $requestTypeStr == 'business' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
        }

        if( $requestTypeStr == 'vacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
        }

        $dql->where("requestType.id IS NOT NULL AND user.id = :userId AND requestType.status = :statusApproved");

        if( $academicYearStart && $academicYearEnd ) {
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
            $dql->andWhere("requestType.startDate > '" . $academicYearStartStr . "'" . " AND requestType.endDate < " . "'" . $academicYearEndStr . "'");
        }

        $query = $this->em->createQuery($dql);

        $query->setParameters( array(
            'userId' => $user->getId(),
            'statusApproved' => 'approved'
        ));

        $numberOfDaysRes = $query->getSingleResult();

        $numberOfDays = $numberOfDaysRes['numberOfDays'];

        //echo "numberOfDays=".$numberOfDays."<br>";

        return $numberOfDays;
    }


    public function getSubmitterPhone($user) {

        //(a) prepopulate the phone number with the phone number from the user's profile
        $phones = $user->getAllPhones();
        if( count($phones) > 0 ) {
            return $phones[0];
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
}