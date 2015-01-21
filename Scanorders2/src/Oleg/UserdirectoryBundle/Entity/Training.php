<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_training")
 */
class Training extends BaseUserAttributes
{

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="trainings")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $completionDate;

    /**
     * @ORM\ManyToOne(targetEntity="CompletionReasonList",cascade={"persist"})
     */
    private $completionReason;

    /**
     * @ORM\ManyToOne(targetEntity="TrainingDegreeList",cascade={"persist"})
     */
    private $degree;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $appendDegreeToName;

    /**
     * Contains children - FellowshipSubspecialtyList
     * @ORM\ManyToOne(targetEntity="ResidencySpecialtyList",cascade={"persist"})
     */
    private $residencySpecialty;

//    /**
//     * @ORM\ManyToOne(targetEntity="FellowshipSubspecialtyList",cascade={"persist"})
//     */
//    private $fellowshipSubspecialty;

    /**
     * @ORM\ManyToOne(targetEntity="MajorTrainingList",cascade={"persist"})
     */
    private $major;

    /**
     * @ORM\ManyToOne(targetEntity="MinorTrainingList",cascade={"persist"})
     */
    private $minor;

    /**
     * @ORM\ManyToMany(targetEntity="HonorTrainingList")
     * @ORM\JoinTable(name="user_trainings_honors")
     **/
    private $honors;

    /**
     * @ORM\ManyToOne(targetEntity="FellowshipTitleList",cascade={"persist"})
     */
    private $fellowshipTitle;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $appendFellowshipTitleToName;

//    /**
//     * @ORM\ManyToOne(targetEntity="EducationalInstituteList",cascade={"persist"})
//     */
//    private $educationalInstitute;


    public function __construct($author=null) {

        $this->honors = new ArrayCollection();

        parent::__construct($author);
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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $completionDate
     */
    public function setCompletionDate($completionDate)
    {
        $this->completionDate = $completionDate;
    }

    /**
     * @return mixed
     */
    public function getCompletionDate()
    {
        return $this->completionDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $completionReason
     */
    public function setCompletionReason($completionReason)
    {
        $this->completionReason = $completionReason;
    }

    /**
     * @return mixed
     */
    public function getCompletionReason()
    {
        return $this->completionReason;
    }

    /**
     * @param mixed $degree
     */
    public function setDegree($degree)
    {
        $this->degree = $degree;
    }

    /**
     * @return mixed
     */
    public function getDegree()
    {
        return $this->degree;
    }

    /**
     * @param mixed $appendDegreeToName
     */
    public function setAppendDegreeToName($appendDegreeToName)
    {
        $this->appendDegreeToName = $appendDegreeToName;
    }

    /**
     * @return mixed
     */
    public function getAppendDegreeToName()
    {
        return $this->appendDegreeToName;
    }

    /**
     * @param mixed $residencySpecialty
     */
    public function setResidencySpecialty($residencySpecialty)
    {
        $this->residencySpecialty = $residencySpecialty;
    }

    /**
     * @return mixed
     */
    public function getResidencySpecialty()
    {
        return $this->residencySpecialty;
    }

    /**
     * @param mixed $appendFellowshipTitleToName
     */
    public function setAppendFellowshipTitleToName($appendFellowshipTitleToName)
    {
        $this->appendFellowshipTitleToName = $appendFellowshipTitleToName;
    }

    /**
     * @return mixed
     */
    public function getAppendFellowshipTitleToName()
    {
        return $this->appendFellowshipTitleToName;
    }

    /**
     * @param mixed $fellowshipTitle
     */
    public function setFellowshipTitle($fellowshipTitle)
    {
        $this->fellowshipTitle = $fellowshipTitle;
    }

    /**
     * @return mixed
     */
    public function getFellowshipTitle()
    {
        return $this->fellowshipTitle;
    }

    /**
     * @param mixed $major
     */
    public function setMajor($major)
    {
        $this->major = $major;
    }

    /**
     * @return mixed
     */
    public function getMajor()
    {
        return $this->major;
    }

    /**
     * @param mixed $minor
     */
    public function setMinor($minor)
    {
        $this->minor = $minor;
    }

    /**
     * @return mixed
     */
    public function getMinor()
    {
        return $this->minor;
    }


    public function addHonor($honor)
    {
        if( $honor && !$this->honors->contains($honor) ) {
            $this->honors->add($honor);
        }

        return $this;
    }
    public function removeHonor($honor)
    {
        $this->honors->removeElement($honor);
    }
    public function getHonors()
    {
        return $this->honors;
    }



    public function __toString() {
        return "Training";
    }

}