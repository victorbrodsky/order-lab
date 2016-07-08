<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/11/2016
 * Time: 11:35 AM
 */

namespace Oleg\VacReqBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;


/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="vacreq_request")
 */
class VacReqRequest
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="exportId", type="integer", nullable=true)
     */
    private $exportId;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $submitter;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updateAuthor", referencedColumnName="id", nullable=true)
     */
    private $updateUser;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;


    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     */
    private $institution;


    /**
     * @ORM\OneToOne(targetEntity="VacReqRequestBusiness", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="requestBusiness_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     **/
    private $requestBusiness;


    /**
     * @ORM\OneToOne(targetEntity="VacReqRequestVacation", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="requestVacation_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     **/
    private $requestVacation;


    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $approver;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $approvedRejectDate;


    //availability
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $availableViaEmail;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $availableEmail;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $availableViaCellPhone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $availableCellPhone;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $availableViaOther;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $availableOther;

    /**
     * Not Available
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $availableNone;

//    /**
//     * Other
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $emergencyComment;
//
//    /**
//     * Cell Phone
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $emergencyPhone;
//
//    /**
//     * E-Mail
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $emergencyEmail;


    /**
     * extraStatus: cancellation-request, "Cancellation Requested", "Cancellation Approved (Canceled)", "Cancellation Denied (Approved)"
     * @ORM\Column(type="string", nullable=true)
     */
    private $extraStatus;

    /**
     * REQUEST_STATUS_ID
     * status: pending, approved, rejected
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;

    /**
     * FINAL_FIRST_DAY_AWAY
     * @ORM\Column(type="date", nullable=true)
     */
    private $firstDayAway;

    /**
     * FINAL_FIRST_DAY_BACK
     * @ORM\Column(type="date", nullable=true)
     */
    private $firstDayBackInOffice;

    /**
     * COMMENTS
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * UPDATE_COMMENTS
     * @ORM\Column(type="text", nullable=true)
     */
    private $updateComment;


    //Carry Over Request fields: source year, destination year, number of carry over days
    /**
     * @ORM\ManyToOne(targetEntity="VacReqRequestTypeList")
     */
    private $requestType;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $sourceYear;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $destinationYear;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $carryOverDays;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     */
    private $tentativeInstitution;

    /**
     * status: pending, approved, rejected
     * @ORM\Column(type="string", nullable=true)
     */
    private $tentativeStatus;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $tentativeApprover;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tentativeApprovedRejectDate;



    public function __construct($user=null) {
        $this->setUser($user);
        $this->setSubmitter($user);
        $this->setStatus('pending');
        $this->setCreateDate(new \DateTime());
    }




    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getExportId()
    {
        return $this->exportId;
    }

    /**
     * @param mixed $exportId
     */
    public function setExportId($exportId)
    {
        $this->exportId = $exportId;
    }



    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getSubmitter()
    {
        return $this->submitter;
    }

    /**
     * @param mixed $submitter
     */
    public function setSubmitter($submitter)
    {
        $this->submitter = $submitter;
    }





    /**
     * @return mixed
     */
    public function getUpdateUser()
    {
        return $this->updateUser;
    }

    /**
     * @param mixed $updateUser
     */
    public function setUpdateUser($updateUser)
    {
        $this->updateUser = $updateUser;
    }

    /**
     * @return DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param DateTime $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdateDate()
    {
        $this->updateDate = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getRequestBusiness()
    {
        return $this->requestBusiness;
    }

    /**
     * @param mixed $requestBusiness
     */
    public function setRequestBusiness($requestBusiness)
    {
        $this->requestBusiness = $requestBusiness;
    }

    /**
     * @return mixed
     */
    public function getRequestVacation()
    {
        return $this->requestVacation;
    }

    /**
     * @param mixed $requestVacation
     */
    public function setRequestVacation($requestVacation)
    {
        $this->requestVacation = $requestVacation;
    }

    /**
     * @return mixed
     */
    public function getAvailableViaEmail()
    {
        return $this->availableViaEmail;
    }

    /**
     * @param mixed $availableViaEmail
     */
    public function setAvailableViaEmail($availableViaEmail)
    {
        $this->availableViaEmail = $availableViaEmail;
    }

    /**
     * @return mixed
     */
    public function getAvailableEmail()
    {
        return $this->availableEmail;
    }

    /**
     * @param mixed $availableEmail
     */
    public function setAvailableEmail($availableEmail)
    {
        $this->availableEmail = $availableEmail;
    }

    /**
     * @return mixed
     */
    public function getAvailableViaCellPhone()
    {
        return $this->availableViaCellPhone;
    }

    /**
     * @param mixed $availableViaCellPhone
     */
    public function setAvailableViaCellPhone($availableViaCellPhone)
    {
        $this->availableViaCellPhone = $availableViaCellPhone;
    }

    /**
     * @return mixed
     */
    public function getAvailableCellPhone()
    {
        return $this->availableCellPhone;
    }

    /**
     * @param mixed $availableCellPhone
     */
    public function setAvailableCellPhone($availableCellPhone)
    {
        $this->availableCellPhone = $availableCellPhone;
    }

    /**
     * @return mixed
     */
    public function getAvailableViaOther()
    {
        return $this->availableViaOther;
    }

    /**
     * @param mixed $availableViaOther
     */
    public function setAvailableViaOther($availableViaOther)
    {
        $this->availableViaOther = $availableViaOther;
    }

    /**
     * @return mixed
     */
    public function getAvailableOther()
    {
        return $this->availableOther;
    }

    /**
     * @param mixed $availableOther
     */
    public function setAvailableOther($availableOther)
    {
        $this->availableOther = $availableOther;
    }

    /**
     * @return mixed
     */
    public function getAvailableNone()
    {
        return $this->availableNone;
    }

    /**
     * @param mixed $availableNone
     */
    public function setAvailableNone($availableNone)
    {
        $this->availableNone = $availableNone;
    }


    /**
     * @return mixed
     */
    public function getApprover()
    {
        return $this->approver;
    }

    /**
     * @param mixed $approver
     */
    public function setApprover($approver)
    {
        $this->approver = $approver;
        $this->setApprovedRejectDate(new \DateTime());
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getTentativeInstitution()
    {
        return $this->tentativeInstitution;
    }

    /**
     * @param mixed $tentativeInstitution
     */
    public function setTentativeInstitution($tentativeInstitution)
    {
        $this->tentativeInstitution = $tentativeInstitution;
    }

    /**
     * @return mixed
     */
    public function getTentativeStatus()
    {
        return $this->tentativeStatus;
    }

    /**
     * @param mixed $tentativeStatus
     */
    public function setTentativeStatus($tentativeStatus)
    {
        $this->tentativeStatus = $tentativeStatus;
    }

    /**
     * @return mixed
     */
    public function getTentativeApprover()
    {
        return $this->tentativeApprover;
    }

    /**
     * @param mixed $tentativeApprover
     */
    public function setTentativeApprover($tentativeApprover)
    {
        $this->tentativeApprover = $tentativeApprover;
    }

    /**
     * @return \DateTime
     */
    public function getTentativeApprovedRejectDate()
    {
        return $this->tentativeApprovedRejectDate;
    }

    /**
     * @param \DateTime $tentativeApprovedRejectDate
     */
    public function setTentativeApprovedRejectDate($tentativeApprovedRejectDate)
    {
        $this->tentativeApprovedRejectDate = $tentativeApprovedRejectDate;
    }



    /**
     * @return \DateTime
     */
    public function getApprovedRejectDate()
    {
        return $this->approvedRejectDate;
    }

    /**
     * @param \DateTime $approvedRejectDate
     */
    public function setApprovedRejectDate($approvedRejectDate)
    {
        $this->approvedRejectDate = $approvedRejectDate;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getFirstDayAway()
    {
        return $this->firstDayAway;
    }

    /**
     * @param mixed $firstDayAway
     */
    public function setFirstDayAway($firstDayAway)
    {
        $this->firstDayAway = $firstDayAway;
    }

    /**
     * @return mixed
     */
    public function getFirstDayBackInOffice()
    {
        return $this->firstDayBackInOffice;
    }

    /**
     * @param mixed $firstDayBackInOffice
     */
    public function setFirstDayBackInOffice($firstDayBackInOffice)
    {
        $this->firstDayBackInOffice = $firstDayBackInOffice;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getUpdateComment()
    {
        return $this->updateComment;
    }

    /**
     * @param mixed $updateComment
     */
    public function setUpdateComment($updateComment)
    {
        $this->updateComment = $updateComment;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * @param mixed $requestType
     */
    public function setRequestType($requestType)
    {
        $this->requestType = $requestType;
    }

    /**
     * @return mixed
     */
    public function getSourceYear()
    {
        return $this->sourceYear;
    }

    /**
     * @param mixed $sourceYear
     */
    public function setSourceYear($sourceYear)
    {
        //$sourceYear = $this->convertYearRangeToYear($sourceYear);
        $this->sourceYear = $sourceYear;
    }

    /**
     * @return mixed
     */
    public function getDestinationYear()
    {
        return $this->destinationYear;
    }

    /**
     * @param mixed $destinationYear
     */
    public function setDestinationYear($destinationYear)
    {
        //$destinationYear = $this->convertYearRangeToYear($destinationYear);
        $this->destinationYear = $destinationYear;
    }

    /**
     * @return mixed
     */
    public function getCarryOverDays()
    {
        return $this->carryOverDays;
    }

    /**
     * @param mixed $carryOverDays
     */
    public function setCarryOverDays($carryOverDays)
    {
        $this->carryOverDays = $carryOverDays;
    }

    /**
     * @return mixed
     */
    public function getExtraStatus()
    {
        return $this->extraStatus;
    }

    /**
     * @param mixed $extraStatus
     */
    public function setExtraStatus($extraStatus)
    {
        $this->extraStatus = $extraStatus;
    }




    public function convertYearRangeToYear($yearRangeStr) {
        if( strpos($yearRangeStr, '-') === false ) {
            return $yearRangeStr;
        }
        $yearRangeArr = explode("-",$yearRangeStr);
        $year = $yearRangeArr[0];
        return $year;
    }


    public function setFinalStatus() {
        $requestType = $this->getRequestType();
        if( $requestType && $requestType->getAbbreviation() == "business-vacation" ) {
            //set overall status
            $overallStatus = $this->getOverallStatus();
            $this->setStatus($overallStatus);
            //$this->setEntireStatus($overallStatus);
        }
    }

    public function setBusinessVacationEntireStatus( $status ) {
        $requestType = $this->getRequestType();
        if( $requestType && $requestType->getAbbreviation() == "business-vacation" ) {
            $this->setStatus($status);
            $this->setEntireStatus($status);
        }
    }

    public function setFinalFirstDayAway() {
        $requestType = $this->getRequestType();
        if( $requestType && $requestType->getAbbreviation() == "business-vacation" ) {
            //set first day away
            $firstDateAway = $this->getFirstDateAway(null);
            $this->setFirstDayAway($firstDateAway);
        }
    }



//    public function getOverallStatus_OLD()
//    {
//        $status = null;
//
//        if(
//            $this->getRequestBusiness() && $this->getRequestBusiness()->getStatus() == 'approved' ||
//            $this->getRequestVacation() && $this->getRequestVacation()->getStatus() == 'approved'
//        ) {
//            $status = 'approved';
//        }
//
//        if(
//            $this->getRequestBusiness() && $this->getRequestBusiness()->getStatus() == 'rejected' ||
//            $this->getRequestVacation() && $this->getRequestVacation()->getStatus() == 'rejected'
//        ) {
//            $status = 'rejected';
//        }
//
//        if(
//            $this->getRequestBusiness() && $this->getRequestBusiness()->getStatus() == 'pending' ||
//            $this->getRequestVacation() && $this->getRequestVacation()->getStatus() == 'pending'
//        ) {
//            $status = 'pending';
//        }
//
//        return $status;
//    }


    public function isOverallStatus( $status ) {
        $resB = true;
        $resV = true;

        if( $this->hasBusinessRequest() ) {
            $resB = false;
            if( $this->getRequestBusiness()->getStatus() == $status ) {
                $resB = true;
            }
        }

        if( $this->hasVacationRequest() ) {
            $resV = false;
            if( $this->getRequestVacation()->getStatus() == $status ) {
                $resV = true;
            }
        }

        if( $resB && $resV ) {
            return true;
        }

        return false;
    }

    //return pending or complete
    public function getOverallStatus() {
        $statusB = null;
        $statusV = null;

        if( $this->hasBusinessRequest() ) {
            $statusB = $this->getRequestBusiness()->getStatus();
        }

        if( $this->hasVacationRequest() ) {
            $statusV = $this->getRequestVacation()->getStatus();
        }

        if( $statusB == null && $statusV == null ) {
            return 'pending';
        }
        if( $statusB == 'pending' && $statusV == 'pending' ) {
            return 'pending';
        }
        if( $statusB == null && $statusV == 'pending' ) {
            return 'pending';
        }
        if( $statusB == 'pending' && $statusV == null ) {
            return 'pending';
        }

        return 'completed';
    }

    public function getDetailedStatus() {
        $statusArr = array();

        if( $this->hasBusinessRequest() ) {
            $statusB = $this->getRequestBusiness()->getStatus();
            $statusArr[] = "Business Travel Request ".$statusB;
        }

        if( $this->hasVacationRequest() ) {
            $statusV = $this->getRequestVacation()->getStatus();
            $statusArr[] =  "Vacation Request ".$statusV;
        }

        if( count($statusArr) > 0 ) {
            return implode(", ",$statusArr);
        }

        return null;
    }

    //status - "Cancellation Requested", "Canceled (Approved)"-"Cancellation Approved (Canceled)", "Cancellation Denied (Approved)"
    public function getStatusStr() {
        $status = $this->getStatus();
        $extraStatus = $this->getExtraStatus();
        if( $extraStatus ) {
            $status = $extraStatus;
        }
        return $status;
    }

    public function hasBusinessRequest() {
        if( $this->getRequestBusiness() && $this->getRequestBusiness()->getStartDate() && $this->getRequestBusiness()->getEndDate() ) {
            return true;
        }
        return false;
    }

    public function hasVacationRequest() {
        if( $this->getRequestVacation() && $this->getRequestVacation()->getStartDate() && $this->getRequestVacation()->getEndDate() ) {
            return true;
        }
        return false;
    }

    public function setEntireStatus( $status ) {
        if( $this->hasBusinessRequest() ) {
            $this->getRequestBusiness()->setStatus($status);
        }

        if( $this->hasVacationRequest() ) {
            $this->getRequestVacation()->setStatus($status);
        }
    }

    public function getTotalDays( $status='approved', $requestTypeStr=null ) {
        $days = 0;
        $daysB = 0;
        $daysV = 0;
        if( $this->hasBusinessRequest() && $this->getRequestBusiness()->getStatus() == $status ) {
            $daysB = $days + $this->getRequestBusiness()->getNumberOfDays();
        }
        if( $this->hasVacationRequest() && $this->getRequestVacation()->getStatus() == $status ) {
            $daysV = $days + $this->getRequestVacation()->getNumberOfDays();
        }

        if( $requestTypeStr == null ) {
            $days = $daysB + $daysV;
        } else {
            if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
                $days = $daysB;
            }

            if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
                $days = $daysV;
            }
        }

        return $days;
    }

    public function getTotalDaysByType( $requestTypeStr ) {

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $days = 0;
            if( $this->hasBusinessRequest() ) {
                $days = $days + $this->getRequestBusiness()->getNumberOfDays();
            }
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $days = 0;
            if( $this->hasVacationRequest() ) {
                $days = $days + $this->getRequestVacation()->getNumberOfDays();
            }
        }

        return $days;
    }

    public function getFirstDateAway($status,$requestTypeStr=null) {
        //echo "status=".$status."; requestTypeStr=".$requestTypeStr."<br>";
        $dateB = null;
        $dateV = null;

        if( $requestTypeStr ) {
            $processB = false;
            $processV = false;
            if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
                $processB = true;
            }
            if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
                $processV = true;
            }
        } else {
            $processB = true;
            $processV = true;
        }

        if( $this->hasBusinessRequest() && $processB ) {
            if( $status ) {
                if( $this->getRequestBusiness()->getStatus() == $status ) {
                    $dateB = $this->getRequestBusiness()->getStartDate();
                }
            } else {
                $dateB = $this->getRequestBusiness()->getStartDate();
            }
        }
        if( $this->hasVacationRequest() && $processV ) {
            if( $status ) {
                if( $this->getRequestVacation()->getStatus() == $status ) {
                    $dateV = $this->getRequestVacation()->getStartDate();
                }
            } else {
                $dateV = $this->getRequestVacation()->getStartDate();
            }
        }

        if( $dateB && $dateV ) {
            //echo "date=".$dateB->format('Y-m-d')."<br>";
            if ($dateB < $dateV) {
                return $dateB;
            } else {
                return $dateV;
            }
        }

        if( $dateB ) {
            //echo "dateB=".$dateB->format('Y-m-d')."<br>";
            return $dateB;
        }

        if( $dateV ) {
            //echo "dateV=".$dateV->format('Y-m-d')."<br>";
            return $dateV;
        }

        return null;
    }


    public function getFinalStartEndDates( $requestTypeStr=null ) {

        $startDate = null;
        $endDate = null;
        $res = array();

        if( $requestTypeStr ) {
            $processB = false;
            $processV = false;
            if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
                $processB = true;
            }
            if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
                $processV = true;
            }
        } else {
            $processB = true;
            $processV = true;
        }

        if( $processB && $processV && $this->hasBusinessRequest() && $this->hasVacationRequest() ) {
            $subRequestB = $this->getRequestBusiness();
            $subRequestV = $this->getRequestVacation();

            //get earliest startDate
            if( $subRequestB->getStartDate() < $subRequestV->getStartDate() ) {
                $startDate = $subRequestB->getStartDate();
            } else {
                $startDate = $subRequestV->getStartDate();
            }
            //get latest endDate
            if( $subRequestB->getEndDate() > $subRequestV->getEndDate() ) {
                $endDate = $subRequestB->getEndDate();
            } else {
                $endDate = $subRequestV->getEndDate();
            }

            $res['startDate'] = $startDate;
            $res['endDate'] = $endDate;
            return $res;
        }

        if( $processB && $this->hasBusinessRequest() ) {
            $subRequest = $this->getRequestBusiness();
            $startDate = $subRequest->getStartDate();
            $endDate = $subRequest->getEndDate();
            $res['startDate'] = $startDate;
            $res['endDate'] = $endDate;
            return $res;
        }

        if( $processV && $this->hasVacationRequest() ) {
            $subRequest = $this->getRequestVacation();
            $startDate = $subRequest->getStartDate();
            $endDate = $subRequest->getEndDate();
            $res['startDate'] = $startDate;
            $res['endDate'] = $endDate;
            return $res;
        }

        return null;
    }

    public function getRequestName() {

        $requestType = $this->getRequestType();

        if( $requestType && $requestType->getAbbreviation() == "carryover" ) {
            $name = $this->getRequestType() . "";
            return $name;
        }

        if( $requestType && $requestType->getAbbreviation() == "business-vacation" ) {
            $name = "";
            if ($this->hasBusinessRequest()) {
                $name = "Business Travel";
            }

            if ($this->hasVacationRequest()) {
                if ($name) {
                    $name = $name . " and ";
                }
                $name = $name . "Vacation";
            }

            if ($name) {
                $name = $name . " ";
            }
            $name = $name . "Request";

            return $name;
        }

        return "Request";
    }

    public function getEmergencyConatcsArr() {
        $resArr = array();

        $cellPhone = $this->getAvailableCellPhone();
        if( $cellPhone ) {
            $resArr[] = "Phone - " . $cellPhone;
        }

        $email = $this->getAvailableEmail();
        if( $email ) {
            $resArr[] = "Email - " . $email;
        }

        $other = $this->getAvailableOther();
        if( $other ) {
            $resArr[] = "Other - " . $other;
        }

        return $resArr;
    }
    public function getEmergencyConatcs() {
        $resArr = $this->getEmergencyConatcsArr();
        return implode('<br>',$resArr);
    }

    public function getSourceYearRange() {
        $endYear = (int)$this->getSourceYear() + 1;
        //echo "endYear=".$endYear."<br>";
        $yearRange = $this->getSourceYear() . "-" . $endYear;
        return $yearRange;
    }
    public function getDestinationYearRange() {
        $endYear = (int)$this->getDestinationYear() + 1;
        //echo "endYear=".$endYear."<br>";
        $yearRange = $this->getDestinationYear() . "-" . $endYear;
        return $yearRange;
    }

    public function getEmailSubject() {

        $requestType = $this->getRequestType();

        $subject = $this->getUser()->getUsernameOptimal() . " has submitted the " . $this->getRequestName() . " #" . $this->getId();

        if( $requestType && $requestType->getAbbreviation() == "carryover" ) {
            //FirstName LastName requested carry over of X vacation days from [Source Academic Year] to [Destination Academic Year]
            $subject = $this->getUser()->getUsernameOptimal(). " requested carry over of " .
                $this->getCarryOverDays() . " vacation days from " .
                $this->getSourceYearRange() . " to " . $this->getDestinationYearRange();
        }

        if( $requestType && $requestType->getAbbreviation() == "business-vacation" ) {
            $subject = $this->getUser()->getUsernameOptimal(). " has submitted the " . $this->getRequestName() . " #" . $this->getId();
        }

        return $subject;
    }

    public function getArrayFields() {
        $fieldsArr = array(
            'phone','institution',
            'status','firstDayAway','firstDayBackInOffice','comment','updateComment',
            'availableViaEmail','availableEmail',
            'availableViaCellPhone','availableCellPhone',
            'availableViaOther','availableOther',
            'requestType','sourceYear','destinationYear','carryOverDays'
        );
        return $fieldsArr;
    }


    public function __toString()
    {
        $break = "\r\n";
        //$transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');

        $res = "Request ID: ".$this->getId().$break;
        $res .= "Person Away: ".$this->getUser().$break;
        $res .= "Organizational Group: ".$this->getInstitution().$break;
        $res .= "Phone Number for the person away: ".$this->getPhone().$break;

        $res .= "Emergency Contact Info:".$break.implode($break,$this->getEmergencyConatcsArr()).$break.$break;

        if( $this->hasBusinessRequest() ) {
            $subRequest = $this->getRequestBusiness();
            $res .= $subRequest."".$break;
        }

        if( $this->hasVacationRequest() ) {
            $subRequest = $this->getRequestVacation();
            $res .= $subRequest."".$break;
        }

        return $res;
    }
}