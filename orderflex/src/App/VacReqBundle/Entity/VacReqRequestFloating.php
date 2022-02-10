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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * @ORM\Entity
 * @ORM\Table(name="vacreq_floating")
 */
class VacReqRequestFloating
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
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $submitter;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
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
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
     */
    private $institution;

    /**
     * status: pending, approved, rejected
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $approver;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $approvedRejectDate;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $approverComment;
    
    /**
     * floating day type [Juneteenth]
     *
     * @ORM\ManyToOne(targetEntity="VacReqFloatingTypeList")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $floatingType;

    /**
     * I have worked or plan to work on [Juneteenth]
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $work;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $floatingDay;

//    /**
//     * @ORM\ManyToOne(targetEntity="VacReqRequestTypeList")
//     */
//    private $requestType;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $academicYear;



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
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param \DateTime $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
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
    }

    /**
     * @return mixed
     */
    public function getApprovedRejectDate()
    {
        return $this->approvedRejectDate;
    }

    /**
     * @param mixed $approvedRejectDate
     */
    public function setApprovedRejectDate($approvedRejectDate)
    {
        $this->approvedRejectDate = $approvedRejectDate;
    }

    /**
     * @return mixed
     */
    public function getApproverComment()
    {
        return $this->approverComment;
    }

    /**
     * @param mixed $approverComment
     */
    public function setApproverComment($approverComment)
    {
        $this->approverComment = $approverComment;
    }

//    /**
//     * @return mixed
//     */
//    public function getRequestType()
//    {
//        return $this->requestType;
//    }
//
//    /**
//     * @param mixed $requestType
//     */
//    public function setRequestType($requestType)
//    {
//        $this->requestType = $requestType;
//    }

    /**
     * @return mixed
     */
    public function getFloatingType()
    {
        return $this->floatingType;
    }

    /**
     * @param mixed $floatingType
     */
    public function setFloatingType($floatingType)
    {
        $this->floatingType = $floatingType;
    }

    /**
     * @return mixed
     */
    public function getWork()
    {
        return $this->work;
    }

    /**
     * @param mixed $work
     */
    public function setWork($work)
    {
        $this->work = $work;
    }

    /**
     * @return \DateTime
     */
    public function getFloatingDay()
    {
        return $this->floatingDay;
    }

    /**
     * @param \DateTime $floatingDay
     */
    public function setFloatingDay($floatingDay)
    {
        $this->floatingDay = $floatingDay;
    }

//    /**
//     * @return mixed
//     */
//    public function getAcademicYear()
//    {
//        return $this->academicYear;
//    }
//
//    /**
//     * @param mixed $academicYear
//     */
//    public function setAcademicYear($academicYear)
//    {
//        $this->academicYear = $academicYear;
//    }

//    public function getFinalStartEndDates() {
//        $dates['startDate'] = $this->getAcademicYear();
//        $dates['endDate'];
//
//        return $dates;
//    }
    public function getFinalStartEndDates( $requestTypeStr=null ) {
        $floatingDay = $this->getFloatingDay();
        $res = array();
        $res['startDate'] = $floatingDay;
        $res['endDate'] = $floatingDay;
        return $res;
    }



    public function printRequest( $container=null )
    {
        //$break = "\r\n";
        $break = "<br>";

        $res = "";

        $res .= "Floating Day Request ID: ".$this->getId().$break;
        $res .= "Submitted on: ".$this->getCreateDate()->format('m-d-Y').$break;

        $res .= $this->createUseStrUrl($this->getSubmitter(),"Submitter:",$container).$break;
        $res .= $this->createUseStrUrl($this->getUser(),"Person Away:",$container).$break;

        if( $this->getApprover() ) {
            $res .= $this->createUseStrUrl($this->getApprover(), "Approver:", $container) . $break;
        }

        if( $this->getApprovedRejectDate() ) {
            $res .= "Approved/Rejected on: " . $this->getApprovedRejectDate()->format('m-d-Y') . $break;
        }
        $res .= "Organizational Group: ".$this->getInstitution().$break;

        $res .= "Phone Number for the person away: ".$this->getPhone().$break;
        //$res .= "Emergency Contact Info:".$break.implode($break,$this->getEmergencyConatcsArr()).$break.$break;

        $worked = $this->getWorkStr();

        $res .= "### Floating Day Request ###".$break;
        $res .= "Status: ".$this->getStatus().$break;
        $res .= "Floating Day Type: ".$this->getFloatingType().$break;
        $res .= "I have worked or plan to work: ".$worked.$break;

        if( $this->getApproverComment() ) {
            $res .= "Approver Comment: ".$this->getApproverComment().$break;
        }

        //$requestType = $this->getRequestType();
//        if( $requestType && $requestType->getAbbreviation() == "carryover" ) {
//            $res = "";
//            if( $this->getDetailedStatus() ) {
//                $res .= $this->getDetailedStatus() . $break;
//            }
//            $res .= "Request ID: ".$this->getId().$break;
//            $res .= "Submitted on: ".$this->getCreateDate()->format('m-d-Y').$break;
//
//            //$res .= "Submitter: ".$this->getSubmitter().$break;
//            //$res .= "Person Away: ".$this->getUser().$break;
//            //$res .= "Approver: ".$this->getApprover().$break;
//            $res .= $this->createUseStrUrl($this->getSubmitter(),"Submitter:",$container).$break;
//            $res .= $this->createUseStrUrl($this->getUser(),"Person Away:",$container).$break;
//
//            //getTentativeApprover
//            if( $this->getTentativeApprover() ) {
//                $res .= $this->createUseStrUrl($this->getTentativeApprover(), "Tentative Approver:", $container) . $break;
//            }
//
//            if( $this->getApprover() ) {
//                $res .= $this->createUseStrUrl($this->getApprover(), "Approver:", $container) . $break;
//            }
//
//            if( $this->getApprovedRejectDate() ) {
//                $res .= "Approved/Rejected on: " . $this->getApprovedRejectDate()->format('m-d-Y') . $break;
//            }
//            $res .= "Organizational Group: ".$this->getInstitution().$break;
//
//            $res .= $break;
//            $res .= "### Carry Over Request ###".$break;
//            $res .= "Tentative Organizational Group: ".$this->getTentativeInstitution().$break;
//            $res .= "Carry Over Days: ".$this->getCarryOverDays().$break;
//            $res .= "from: ".$this->getSourceYearRange().$break;
//            $res .= "to: " . $this->getDestinationYearRange().$break;
//        }

        return $res;
    }
    
    public function getWorkStr() {
        $worked = "N/A";
        if( $this->getWork() === true ) {
            $worked = "Yes";
        }
        if( $this->getWork() === false ) {
            $worked = "No";
        }
        return $worked;
    }

    public function isOverallStatus( $status ) {
        if( $this->getStatus() == $status ) {
            return true;
        }
        return false;
    }

    //return pending or complete
    public function getOverallStatus() {
        $status = $this->getStatus();
        return $status;
    }
    public function getDetailedStatus() {
        return "Floating Day Request ".$this->getStatus();
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

    public function getEmailSubject() {
        $subject = $this->getUser()->getUsernameOptimal() . " has submitted the " . $this->getRequestName() . " #" . $this->getId();
        return $subject;
    }

    public function getRequestName() {
        return "Floating Day Request";
    }

    public function getRequestTypeAbbreviation() {
        return "floatingday";
    }

    public function __toString()
    {
        //$break = "\r\n";
        $break = "<br>";
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');

        $worked = "N/A";
        if( $this->getWork() === true ) {
            $worked = "Yes";
        }
        if( $this->getWork() === false ) {
            $worked = "No";
        }

        $res = "### Floating Day Request ###".$break;
        $res .= "Status: ".$this->getStatus().$break;
        $res .= "Floating Day Type: ".$this->getFloatingType().$break;
        $res .= "I have worked or plan to work: ".$worked.$break;

        if( $this->getApproverComment() ) {
            $res .= "Approver Comment: ".$this->getApproverComment().$break;
        }

        return $res;
    }

}