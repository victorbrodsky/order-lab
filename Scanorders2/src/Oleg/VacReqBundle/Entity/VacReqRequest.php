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
     * @ORM\Column(name="exportId", type="integer")
     */
    private $exportId;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $user;

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
     * @ORM\ManyToMany(targetEntity="VacReqAvailabilityList", inversedBy="requests")
     * @ORM\JoinTable(name="vacreq_request_availability")
     **/
    private $availabilities;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $emergencyCellPhone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $emergencyComment;

//    /**
//     * status: pending, approved, rejected
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $status;

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


    public function __construct($user=null) {
        $this->setUser($user);
        //$this->setStatus('pending');
        $this->setCreateDate(new \DateTime());

        $this->availabilities = new ArrayCollection();
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


    public function getAvailabilities()
    {
        return $this->availabilities;
    }
    public function addAvailability($item)
    {
        if( !$this->availabilities->contains($item) ) {
            $this->availabilities->add($item);
        }
        return $this;
    }
    public function removeAvailability($item)
    {
        $this->availabilities->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getEmergencyComment()
    {
        return $this->emergencyComment;
    }

    /**
     * @param mixed $emergencyComment
     */
    public function setEmergencyComment($emergencyComment)
    {
        $this->emergencyComment = $emergencyComment;
    }
    public function addEmergencyComment($emergencyComment) {
        if( $emergencyComment ) {
            $comment = $this->getEmergencyComment() . "\r\n"."\r\n" . $emergencyComment;
            $this->setEmergencyComment($comment);
        }
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



    public function getOverallStatus()
    {
        if(
            $this->getRequestBusiness() && $this->getRequestBusiness()->getStatus() == 'approved' ||
            $this->getRequestVacation() && $this->getRequestVacation()->getStatus() == 'approved'
        ) {
            return 'approved';
        }

        if(
            $this->getRequestBusiness() && $this->getRequestBusiness()->getStatus() == 'rejected' ||
            $this->getRequestVacation() && $this->getRequestVacation()->getStatus() == 'rejected'
        ) {
            return 'rejected';
        }

        if(
            $this->getRequestBusiness() && $this->getRequestBusiness()->getStatus() == 'pending' ||
            $this->getRequestVacation() && $this->getRequestVacation()->getStatus() == 'pending'
        ) {
            return 'pending';
        }

        return null;
    }

    public function hasBusinessRequest() {
        if( $this->getRequestBusiness() && $this->getRequestBusiness()->getStartDate() ) {
            return true;
        }
        return false;
    }

    public function hasVacationRequest() {
        if( $this->getRequestVacation() && $this->getRequestVacation()->getStartDate() ) {
            return true;
        }
        return false;
    }


    public function __toString()
    {
        return "VacReqRequest: id=".$this->getId()."<br>";
    }
}