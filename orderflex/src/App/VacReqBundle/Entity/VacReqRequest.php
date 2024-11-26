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
 * User: ch3
 * Date: 4/11/2016
 * Time: 11:35 AM
 */

namespace App\VacReqBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


#[ORM\Table(name: 'vacreq_request')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class VacReqRequest
{

    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var integer
     */
    #[ORM\Column(name: 'exportId', type: 'integer', nullable: true)]
    private $exportId;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $user;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $submitter;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'updateAuthor', referencedColumnName: 'id', nullable: true)]
    private $updateUser;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $createDate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updateDate;

    #[ORM\Column(type: 'string', nullable: true)]
    private $phone;


    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\Institution')]
    private $institution;


    #[ORM\OneToOne(targetEntity: 'VacReqRequestBusiness', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'requestBusiness_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $requestBusiness;


    #[ORM\OneToOne(targetEntity: 'VacReqRequestVacation', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'requestVacation_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $requestVacation;


    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $approver;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $approvedRejectDate;


    //availability
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $availableViaEmail;

    #[ORM\Column(type: 'string', nullable: true)]
    private $availableEmail;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $availableViaCellPhone;

    #[ORM\Column(type: 'string', nullable: true)]
    private $availableCellPhone;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $availableViaOther;

    #[ORM\Column(type: 'string', nullable: true)]
    private $availableOther;

    /**
     * Not Available
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
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
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $extraStatus;

    /**
     * REQUEST_STATUS_ID
     * status: pending, approved, rejected
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $status;

    /**
     * FINAL_FIRST_DAY_AWAY
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private $firstDayAway;

    /**
     * FINAL_FIRST_DAY_BACK
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private $firstDayBackInOffice;

    /**
     * COMMENTS
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    /**
     * UPDATE_COMMENTS
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $updateComment;


    //Carry Over Request fields: source year, destination year, number of carry over days
    #[ORM\ManyToOne(targetEntity: 'VacReqRequestTypeList')]
    private $requestType;

    #[ORM\Column(type: 'string', nullable: true)]
    private $sourceYear;

    #[ORM\Column(type: 'string', nullable: true)]
    private $destinationYear;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $carryOverDays;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\Institution')]
    private $tentativeInstitution;

    /**
     * status: pending, approved, rejected
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $tentativeStatus;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $tentativeApprover;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $tentativeApprovedRejectDate;

    //TODO: informUsers - users to inform about status of this request
    /**
     * //Send a notification to the following individuals on service (for Fellows)
     * //https://stackoverflow.com/questions/7490488/convert-flat-array-to-a-delimited-string-to-be-saved-in-the-database
     * //https://stackoverflow.com/questions/49324327/how-not-to-allow-delete-options-in-select2
     * //On the group setting page, admin setup a list of default users (bosses, peers of this fellows institutional group).
     * //On the new request page fellows can add any users in the system to this list, but can not remove the default bosses.
     **/
    #[ORM\JoinTable(name: 'vacreq_request_informuser')]
    #[ORM\JoinColumn(name: 'request_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'informuser_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\User', cascade: ['persist'])]
    private $informUsers;


    public function __construct($user=null) {
        $this->setUser($user);
        $this->setSubmitter($user);
        $this->setStatus('pending');
        $this->setCreateDate(new \DateTime());
        $this->informUsers = new ArrayCollection();
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

    #[ORM\PreUpdate]
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

    public function getInformUsers()
    {
        return $this->informUsers;
    }
    public function addInformUser($item)
    {
        if( $item && !$this->informUsers->contains($item) ) {
            $this->informUsers->add($item);
        }
        return $this;
    }
    public function removeInformUser($item)
    {
        $this->informUsers->removeElement($item);
    }




    public function convertYearRangeToYear($yearRangeStr) {
        if( strpos((string)$yearRangeStr, '-') === false ) {
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

        $requestType = $this->getRequestType();
        if( $requestType && $requestType->getAbbreviation() == "carryover" ) {
            if( $this->getStatus() == $status ) {
                return true;
            } else {
                return false;
            }
        }

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

    //return pending, canceled or complete
    public function getOverallStatus() {

        $requestType = $this->getRequestType();
        if( $requestType && $requestType->getAbbreviation() == "carryover" ) {
            return $this->getStatus();
        }

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

        //canceled
        if( $statusB == 'canceled' && $statusV == 'canceled' ) {
            return 'canceled';
        }
        if( $statusB == null && $statusV == 'canceled' ) {
            return 'canceled';
        }
        if( $statusB == 'canceled' && $statusV == null ) {
            return 'canceled';
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

        $requestType = $this->getRequestType();
        if( $requestType && $requestType->getAbbreviation() == "carryover" ) {
            $res =  "Carry Vacation Request: ";
            $tentativeStatus = $this->getTentativeStatus();
            if( $tentativeStatus ) {
                $res .= "Tentative Status: ".$tentativeStatus."; ";
            }
            $res .= "Final Status: ".$this->getStatus();
            $statusArr[] = $res;
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
        //echo "setEntireStatus status=".$status."<br>";
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
//            $subject = $this->getUser()->getUsernameOptimal(). " requested carry over of " .
//                $this->getCarryOverDays() . " vacation days from " .
//                $this->getSourceYearRange() . " to " . $this->getDestinationYearRange().
//                " via request ID #".$this->getId();

            //SubmitterFirstName SubmitterLastName requests your approval to carry over X vacation days from 20XX-20YY to 20YY-20ZZ.
            $subject = $this->getUser()->getUsernameOptimal()." requests your approval to carry over ".$this->getCarryOverDays().
                " vacation days from ".$this->getSourceYearRange() . " to " . $this->getDestinationYearRange().
                " via request ID #".$this->getId();

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
        return $this->printRequest();
    }

    public function printRequest( $container=null )
    {
        //$break = "\r\n";
        $break = "<br>";
        //$transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');

        $res = "";

//        if( $this->getDetailedStatus() ) {
//            $res .= $this->getDetailedStatus() . $break;
//        }

        $res .= "Request ID: ".$this->getId().$break;
        $res .= "Submitted on: ".$this->getCreateDate()->format('m-d-Y').$break;

//            $res .= "Submitter: " . $this->getSubmitter() . $break;
//            $res .= "Person Away: " . $this->getUser() . $break;
//            $res .= "Approver: " . $this->getApprover() . $break;
        $res .= $this->createUseStrUrl($this->getSubmitter(),"Submitter:",$container).$break;
        $res .= $this->createUseStrUrl($this->getUser(),"Person Away:",$container).$break;

        if( $this->getTentativeApprover() ) {
            $res .= $this->createUseStrUrl($this->getTentativeApprover(), "Tentative Approver:", $container) . $break;
        }

        if( $this->getApprover() ) {
            $res .= $this->createUseStrUrl($this->getApprover(), "Approver:", $container) . $break;
        }

        if( $this->getApprovedRejectDate() ) {
            $res .= "Approved/Rejected on: " . $this->getApprovedRejectDate()->format('m-d-Y') . $break;
        }
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

        $requestType = $this->getRequestType();
        if( $requestType && $requestType->getAbbreviation() == "carryover" ) {
            $res = "";
            if( $this->getDetailedStatus() ) {
                $res .= $this->getDetailedStatus() . $break;
            }
            $res .= "Request ID: ".$this->getId().$break;
            $res .= "Submitted on: ".$this->getCreateDate()->format('m-d-Y').$break;

            //$res .= "Submitter: ".$this->getSubmitter().$break;
            //$res .= "Person Away: ".$this->getUser().$break;
            //$res .= "Approver: ".$this->getApprover().$break;
            $res .= $this->createUseStrUrl($this->getSubmitter(),"Submitter:",$container).$break;
            $res .= $this->createUseStrUrl($this->getUser(),"Person Away:",$container).$break;

            //getTentativeApprover
            if( $this->getTentativeApprover() ) {
                $res .= $this->createUseStrUrl($this->getTentativeApprover(), "Tentative Approver:", $container) . $break;
            }

            if( $this->getApprover() ) {
                $res .= $this->createUseStrUrl($this->getApprover(), "Approver:", $container) . $break;
            }

            if( $this->getApprovedRejectDate() ) {
                $res .= "Approved/Rejected on: " . $this->getApprovedRejectDate()->format('m-d-Y') . $break;
            }
            $res .= "Organizational Group: ".$this->getInstitution().$break;

            $res .= $break;
            $res .= "### Carry Over Request ###".$break;
            $res .= "Tentative Organizational Group: ".$this->getTentativeInstitution().$break;
            $res .= "Carry Over Days: ".$this->getCarryOverDays().$break;
            $res .= "from: ".$this->getSourceYearRange().$break;
            $res .= "to: " . $this->getDestinationYearRange().$break;
        }

        return $res;
    }

    //Vacation request #3024 approved
    public function getRequestSubject() {
        if( $this->getDetailedStatus() ) {
            $subject = $this->getDetailedStatus() . " [Request ID #" . $this->getId() ."]";
        } else {
            $subject = "Request ID #" . $this->getId();
        }

        return $subject;
    }

    //"Vacation request #3024 has been approved" or "Vacation request #3024 has been denied"
    public function getRequestMessageHeader() {

        $header = "ID #".$this->getId();

        $requestType = $this->getRequestType();
        if( $requestType && $requestType->getAbbreviation() == "carryover" ) {

            $header =  "Carry Vacation Request: ";
            $tentativeStatus = $this->getTentativeStatus();
            if( $tentativeStatus ) {
                $header .= "Tentative Status: ".$tentativeStatus."; ";
            }
            $header .= "Final Status: ".$this->getStatus();

        } else {

            $statusB = null;
            if( $this->hasBusinessRequest() ) {
                $statusB = $this->getRequestBusiness()->getStatus();
            }
            if( $statusB == 'pending' ) {
                $statusB = 'set to Pending';
            }

            $statusV = null;
            if( $this->hasVacationRequest() ) {
                $statusV = $this->getRequestVacation()->getStatus();
            }
            if( $statusV == 'pending' ) {
                $statusV = 'set to Pending';
            }

            if( $statusB && $statusV ) {
                if( $statusB == $statusV ) {
                    $header = "Business Travel Request and Vacation Request have been ".$statusB;
                } else {
                    $header = "Business Travel Request has been ".$statusB . " and " . "Vacation Request has been ".$statusV;
                }
            }
            elseif( $statusB && !$statusV ) {
                $header = "Business Travel Request has been ".$statusB;
            }
            elseif( !$statusB && $statusV ) {
                $header = "Vacation Request has been ".$statusV;
            }

        }

        return $header;
    }

    //"Submitter: " . $this->getSubmitter() . (url)
    public function createUseStrUrl( $user, $label, $container ) {
        if( !$user ) {
            return "";
        }
        if( !$container ) {
            return $label . " " . $user . "";
        }

        $userUrl = $container->get('router')->generate(
            'vacreq_showuser',
            array(
                'id' => $user->getId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $userStrUrl = $label . " " . $user . " (" . $userUrl . ")";

        return $userStrUrl;
    }

    public function getRequestTypeAbbreviation() {
        if( $this->getRequestType() ) {
            return $this->getRequestType()->getAbbreviation();
        }
        return NULL;
    }
}